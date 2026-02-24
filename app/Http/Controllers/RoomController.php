<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\Room;
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
        return view('landlord.rooms.edit', compact('property','room'));
    }

    public function update(Request $request, $propertyId, Room $room)
    {
        $property = $this->getOwnedProperty($propertyId);
        if ($room->property_id !== $property->id) {
            abort(404);
        }
        $this->authorize('update', $room);
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
        return redirect()->route('landlord.properties.show', $property->id)
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
        ]);

        if (($room->property->approval_status ?? 'pending') !== 'approved') {
            abort(404);
        }

        return view('rooms.show', compact('room'));
    }
}
