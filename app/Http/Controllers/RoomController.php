<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Property;
use App\Models\Room;
use App\Models\RoomImage;
use App\Models\TenantOnboarding;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class RoomController extends Controller
{
    protected function ensureLandlord()
    {
        if (!Auth::check() || Auth::user()->role !== 'landlord') {
            abort(403, 'Unauthorized');
        }
    }

    protected function getOwnedProperty($propertyId): Property
    {
        $this->ensureLandlord();
        $property = Property::where('id', $propertyId)
            ->where('landlord_id', Auth::id())
            ->firstOrFail();
        return $property;
    }

    public function landlordIndex()
    {
        $this->ensureLandlord();
        $rooms = Room::with(['property', 'bookings' => function($q) {
                $q->where('status', 'approved')
                  ->where('check_in', '<=', now()->toDateString())
                  ->where('check_out', '>', now()->toDateString())
                  ->with('student');
            }])
            ->whereHas('property', function ($q) {
                $q->where('landlord_id', Auth::id());
            })
            ->orderBy('created_at', 'desc')
            ->get();

        // Add current tenant info to each room
        $rooms->each(function($room) {
            $currentBooking = $room->bookings->first();
            $room->current_tenant = $currentBooking ? $currentBooking->student : null;
            $room->current_booking = $currentBooking;
        });

        return view('landlord.rooms.landlord_index', compact('rooms'));
    }

    public function index($propertyId)
    {
        $property = $this->getOwnedProperty($propertyId);
        $rooms = $property->rooms()
            ->with(['bookings' => function($q) {
                $q->where('status', 'approved')
                  ->where('check_in', '<=', now()->toDateString())
                  ->where('check_out', '>', now()->toDateString())
                  ->with('student');
            }])
            ->orderBy('room_number')
            ->get();

        // Add current tenant info to each room
        $rooms->each(function($room) {
            $currentBooking = $room->bookings->first();
            $room->current_tenant = $currentBooking ? $currentBooking->student : null;
            $room->current_booking = $currentBooking;
        });

        return view('landlord.rooms.index', compact('property', 'rooms'));
    }

    public function create($propertyId)
    {
        $property = $this->getOwnedProperty($propertyId);
        return view('landlord.rooms.create', compact('property'));
    }

    public function store(Request $request, $propertyId)
    {
        $property = $this->getOwnedProperty($propertyId);
        $validator = Validator::make($request->all(), [
            'room_number' => 'required|string|max:50',
            'capacity' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'status' => 'required|in:available,occupied,maintenance',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp,gif|max:2048',
            'inclusions' => 'nullable|string|max:2000',
        ]);

        try {
            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }
        } catch (\Symfony\Component\Mime\Exception\LogicException $e) {
            return back()->withInput()->with('error', 'File upload validation failed. Please ensure the PHP "fileinfo" extension is enabled and try a smaller image if needed (PHP upload limit).');
        }

        $validated = $validator->validated();
        unset($validated['image']);
        if ($request->hasFile('image')) {
            try {
                $validated['image_path'] = str_replace('\\', '/', $request->file('image')->store('rooms', 'public'));
            } catch (\Symfony\Component\Mime\Exception\LogicException $e) {
                return back()->withInput()->with('error', 'Unable to process the uploaded image. Please ensure the PHP "fileinfo" extension is enabled and try a smaller image if needed (PHP upload limit).');
            }
        }

        $property->rooms()->create(array_merge($validated, [
            'landlord_id' => Auth::id(),
        ]));

        return redirect()
            ->route('landlord.properties.rooms.index', $property->id)
            ->with('success', 'Room added successfully.');
    }

    public function edit($propertyId, Room $room)
    {
        $property = $this->getOwnedProperty($propertyId);
        if ($room->property_id !== $property->id) {
            abort(404);
        }
        $this->authorize('update', $room);
        $roomImages = $room->roomImages;
        return view('landlord.rooms.edit', compact('property', 'room', 'roomImages'));
    }

    public function update(Request $request, $propertyId, Room $room)
    {
        $property = $this->getOwnedProperty($propertyId);
        if ($room->property_id !== $property->id) {
            abort(404);
        }
        $this->authorize('update', $room);
        $validator = Validator::make($request->all(), [
            'room_number'            => 'required|string|max:50',
            'capacity'               => 'required|integer|min:1',
            'price'                  => 'required|numeric|min:0',
            'status'                 => 'required|in:available,occupied,maintenance',
            'image'                  => 'nullable|image|mimes:jpg,jpeg,png,webp,gif|max:4096',
            'inclusions'             => 'nullable|string|max:2000',
            'detail_images.*'        => 'nullable|image|mimes:jpg,jpeg,png,webp,gif|max:4096',
            'detail_labels.*'        => 'nullable|string|max:100',
            'delete_detail_images'   => 'nullable|array',
            'delete_detail_images.*' => 'integer',
        ]);

        try {
            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }
        } catch (\Symfony\Component\Mime\Exception\LogicException $e) {
            return back()->withInput()->with('error', 'File upload validation failed. Please ensure the PHP "fileinfo" extension is enabled and try a smaller image if needed (PHP upload limit).');
        }

        $validated = $validator->validated();
        unset($validated['image']);

        // --- Cover photo ---
        if ($request->hasFile('image')) {
            if (!empty($room->image_path)) {
                Storage::disk('public')->delete($room->image_path);
            }
            try {
                $validated['image_path'] = str_replace('\\', '/', $request->file('image')->store('rooms', 'public'));
            } catch (\Symfony\Component\Mime\Exception\LogicException $e) {
                return back()->withInput()->with('error', 'Unable to process the uploaded image. Please ensure the PHP "fileinfo" extension is enabled and try a smaller image if needed (PHP upload limit).');
            }
        }

        $room->update($validated);

        // --- Delete removed detail images ---
        if ($request->filled('delete_detail_images')) {
            foreach ($request->input('delete_detail_images') as $imgId) {
                $img = RoomImage::where('id', $imgId)->where('room_id', $room->id)->first();
                if ($img) {
                    Storage::disk('public')->delete($img->image_path);
                    $img->delete();
                }
            }
        }

        // --- Update labels of existing detail images ---
        if ($request->filled('existing_labels')) {
            foreach ($request->input('existing_labels') as $imgId => $label) {
                RoomImage::where('id', $imgId)->where('room_id', $room->id)->update(['label' => $label]);
            }
        }

        // --- Upload new detail images ---
        if ($request->hasFile('detail_images')) {
            $existingCount = $room->roomImages()->count();
            $labels = $request->input('detail_labels', []);
            foreach ($request->file('detail_images') as $i => $file) {
                if (!$file || !$file->isValid()) continue;
                try {
                    $path = str_replace('\\', '/', $file->store('rooms', 'public'));
                    RoomImage::create([
                        'room_id'    => $room->id,
                        'image_path' => $path,
                        'label'      => $labels[$i] ?? null,
                        'sort_order' => $existingCount + $i,
                    ]);
                } catch (\Exception $e) {
                    // skip failed uploads silently
                }
            }
        }

        return redirect()->route('landlord.properties.rooms.edit', [$property->id, $room->id])
            ->with('success', 'Room updated successfully.');
    }

    public function destroy($propertyId, Room $room)
    {
        $property = $this->getOwnedProperty($propertyId);
        if ($room->property_id !== $property->id) {
            abort(404);
        }
        $this->authorize('delete', $room);
        $room->delete();
        return redirect()->route('landlord.properties.show', $property->id)
            ->with('success', 'Room deleted.');
    }

    // Quick store endpoint from dashboard modal (property id already known)
    public function quickStore(Request $request, $propertyId)
    {
        $property = $this->getOwnedProperty($propertyId);
        $this->authorize('update', $property);
        $validator = Validator::make($request->all(), [
            'room_number' => 'required|string|max:50',
            'capacity' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'status' => 'required|in:available,occupied,maintenance',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp,gif|max:2048',
            'inclusions' => 'nullable|string|max:2000',
        ]);

        try {
            if ($validator->fails()) {
                return redirect()->route('landlord.dashboard')
                    ->withErrors($validator)
                    ->withInput();
            }
        } catch (\Symfony\Component\Mime\Exception\LogicException $e) {
            return redirect()->route('landlord.dashboard')
                ->withInput()
                ->with('error', 'File upload validation failed. Please ensure the PHP "fileinfo" extension is enabled and try a smaller image if needed (PHP upload limit).');
        }
        $validated = $validator->validated();
        unset($validated['image']);
        if ($request->hasFile('image')) {
            try {
                $validated['image_path'] = str_replace('\\', '/', $request->file('image')->store('rooms', 'public'));
            } catch (\Symfony\Component\Mime\Exception\LogicException $e) {
                return redirect()->route('landlord.dashboard')
                    ->withInput()
                    ->with('error', 'Unable to process the uploaded image. Please ensure the PHP "fileinfo" extension is enabled and try a smaller image if needed (PHP upload limit).');
            }
        }
        $property->rooms()->create(array_merge($validated, [
            'landlord_id' => Auth::id(),
        ]));
        return redirect()->route('landlord.dashboard')
            ->with('success', 'Room added to property "'.$property->name.'"');
    }

    // Public room details page (guest accessible)
    public function publicShow(Room $room)
    {
        $room->load([
            'property:id,name,address,landlord_id,image_path,approval_status',
            'property.landlord:id,full_name',
            'roomImages',
        ]);

        if (($room->property->approval_status ?? 'pending') !== 'approved') {
            abort(404);
        }

        return view('rooms.show', compact('room'));
    }

    // Student: browse available rooms
    public function studentIndex(Request $request)
    {
        if (!Auth::check() || Auth::user()->role !== 'student') {
            abort(403, 'Unauthorized');
        }

        $student = Auth::user();
        $today = now()->toDateString();

        // Check if student has current approved booking
        $currentApprovedBooking = $student->bookings()
            ->where('status', 'approved')
            ->where('check_out', '>', $today)
            ->with(['room.property.landlord'])
            ->orderByDesc('check_in')
            ->first();
        $hasCurrentApprovedBooking = !empty($currentApprovedBooking);

        // Filters from query string
        $minPrice = $request->query('min_price');
        $maxPrice = $request->query('max_price');
        $minCapacity = $request->query('capacity');
        $search = $request->query('q');

        // Recommended rooms (rooms with available slots; apply user filters if provided)
        $recommendedRooms = Room::with('property.landlord')
            ->withAvg('feedbacks', 'rating')
            ->withCount('feedbacks')
            ->where('status', '!=', 'maintenance')
            ->whereHas('property', function ($q) {
                $q->where('approval_status', 'approved');
            })
            ->when($minCapacity !== null && $minCapacity !== '', function ($q) use ($minCapacity) {
                $q->where('capacity', '>=', (int) $minCapacity);
            })
            ->when($minPrice !== null && $minPrice !== '', function ($q) use ($minPrice) {
                $q->where('price', '>=', (float) $minPrice);
            })
            ->when($maxPrice !== null && $maxPrice !== '', function ($q) use ($maxPrice) {
                $q->where('price', '<=', (float) $maxPrice);
            })
            ->when($search && $search !== '', function ($q) use ($search) {
                $q->where(function ($qq) use ($search) {
                    $qq->where('room_number', 'like', "%$search%")
                       ->orWhereHas('property', fn($pq) => $pq->where('name', 'like', "%$search%")->orWhere('address', 'like', "%$search%"));
                });
            })
            ->orderBy('price')
            ->get()
            ->filter(fn($room) => $room->hasAvailableSlots())
            ->values()
            ->take(6);

        // All rooms with occupancy info (exclude maintenance)
        $allRooms = Room::with('property.landlord')
            ->withAvg('feedbacks', 'rating')
            ->withCount('feedbacks')
            ->where('status', '!=', 'maintenance')
            ->whereHas('property', function ($q) {
                $q->where('approval_status', 'approved');
            })
            ->when($minCapacity !== null && $minCapacity !== '', function ($q) use ($minCapacity) {
                $q->where('capacity', '>=', (int) $minCapacity);
            })
            ->when($minPrice !== null && $minPrice !== '', function ($q) use ($minPrice) {
                $q->where('price', '>=', (float) $minPrice);
            })
            ->when($maxPrice !== null && $maxPrice !== '', function ($q) use ($maxPrice) {
                $q->where('price', '<=', (float) $maxPrice);
            })
            ->when($search && $search !== '', function ($q) use ($search) {
                $q->where(function ($qq) use ($search) {
                    $qq->where('room_number', 'like', "%$search%")
                       ->orWhereHas('property', fn($pq) => $pq->where('name', 'like', "%$search%")->orWhere('address', 'like', "%$search%"));
                });
            })
            ->orderBy('property_id')
            ->orderBy('room_number')
            ->get();

        $newThreshold = now()->subDays(3);

        return view('student.rooms.index', compact(
            'recommendedRooms',
            'allRooms',
            'minPrice',
            'maxPrice',
            'minCapacity',
            'newThreshold',
            'hasCurrentApprovedBooking',
            'currentApprovedBooking'
        ));
    }

    // Student-authenticated room detail page with inquiry form
    public function studentShow(Room $room)
    {
        $today = now()->toDateString();

        $tenantOnboarding = TenantOnboarding::where('status', 'completed')
            ->whereHas('booking', function ($q) use ($today) {
                $q->where('student_id', Auth::id())
                  ->where('status', 'approved')
                  ->where('check_in', '<=', $today)
                  ->where('check_out', '>', $today);
            })
            ->with('booking.room.property.landlord')
            ->orderByDesc('updated_at')
            ->first();

        if ($tenantOnboarding && $tenantOnboarding->booking?->room_id && $tenantOnboarding->booking->room_id !== $room->id) {
            return redirect()->route('student.rooms.show', $tenantOnboarding->booking->room_id);
        }

        $tenantMode = !empty($tenantOnboarding);
        $tenantBooking = $tenantOnboarding?->booking;

        $room->load([
            'property:id,name,address,landlord_id,image_path,approval_status',
            'property.landlord:id,full_name',
            'roomImages',
            'feedbacks.user:id,full_name',
        ]);

        if (($room->property->approval_status ?? 'pending') !== 'approved') {
            abort(404);
        }

        // Check if student has an existing booking (pending or approved)
        $existingBooking = Booking::where('student_id', Auth::id())
            ->whereIn('status', ['pending', 'approved'])
            ->where('check_out', '>', $today)
            ->first();
        
        $hasExistingBooking = !empty($existingBooking);
        $existingBookingRoomId = $hasExistingBooking ? $existingBooking->room_id : null;
        $existingBookingIsThisRoom = $hasExistingBooking && $existingBookingRoomId === $room->id;

        // Thread: messages between this student and the property landlord about this property
        $thread = collect();
        if ($room->property->landlord_id) {
            $thread = \App\Models\Message::with(['sender', 'receiver'])
                ->where('property_id', $room->property_id)
                ->where(function ($q) use ($room) {
                    $q->where(function ($inner) use ($room) {
                        $inner->where('sender_id', Auth::id())
                              ->where('receiver_id', $room->property->landlord_id);
                    })->orWhere(function ($inner) use ($room) {
                        $inner->where('sender_id', $room->property->landlord_id)
                              ->where('receiver_id', Auth::id());
                    });
                })
                ->orderBy('created_at')
                ->get();
        }

        // Mark any unread messages from landlord as read
        \App\Models\Message::where('receiver_id', Auth::id())
            ->where('sender_id', $room->property->landlord_id)
            ->where('property_id', $room->property_id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        // Feedback eligibility: must have at least one approved booking for this room
        $canFeedback = $room->bookings()
            ->where('student_id', Auth::id())
            ->where('status', 'approved')
            ->exists();

        $alreadyFeedback = \App\Models\RoomFeedback::where('room_id', $room->id)
            ->where('user_id', Auth::id())
            ->first();

        $feedbacks = $room->feedbacks;
        $avgRating = $feedbacks->isNotEmpty() ? round($feedbacks->avg('rating'), 1) : null;

        $roommates = collect();
        if ($tenantMode) {
            $roommates = Booking::with('student:id,full_name')
                ->where('room_id', $room->id)
                ->where('status', 'approved')
                ->where('check_in', '<=', $today)
                ->where('check_out', '>', $today)
                ->where('student_id', '!=', Auth::id())
                ->orderBy('check_in')
                ->get();
        }

        return view('student.rooms.show', compact(
            'room',
            'thread',
            'feedbacks',
            'avgRating',
            'canFeedback',
            'alreadyFeedback',
            'hasExistingBooking',
            'existingBookingRoomId',
            'existingBookingIsThisRoom',
            'tenantMode',
            'tenantBooking',
            'roommates'
        ));
    }
}
