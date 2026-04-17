<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Property;
use App\Models\Room;
use App\Models\RoomImage;
use App\Models\TenantOnboarding;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class RoomController extends Controller
{
    protected function syncPropertyPriceRange(Property $property): void
    {
        $property->refreshPriceRange();
    }

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

    protected function pricingRules(): array
    {
        return [
            'pricing_model' => 'nullable|in:per_room,per_bed,hybrid',
            'price' => 'nullable|numeric|min:0',
            'price_per_room' => 'nullable|numeric|min:0',
            'price_per_bed' => 'nullable|numeric|min:0',
        ];
    }

    protected function parseNullableFloat(mixed $value): ?float
    {
        if ($value === null) {
            return null;
        }

        $normalized = trim((string) $value);
        if ($normalized === '') {
            return null;
        }

        return is_numeric($normalized) ? (float) $normalized : null;
    }

    protected function detectPricingModel(Request $request): string
    {
        $rawModel = strtolower(trim((string) $request->input('pricing_model', '')));
        if (in_array($rawModel, [Room::PRICING_MODEL_PER_ROOM, Room::PRICING_MODEL_PER_BED, Room::PRICING_MODEL_HYBRID], true)) {
            return $rawModel;
        }

        $pricePerRoom = $this->parseNullableFloat($request->input('price_per_room'));
        $pricePerBed = $this->parseNullableFloat($request->input('price_per_bed'));

        if (($pricePerRoom ?? 0) > 0 && ($pricePerBed ?? 0) > 0) {
            return Room::PRICING_MODEL_HYBRID;
        }

        if (($pricePerBed ?? 0) > 0) {
            return Room::PRICING_MODEL_PER_BED;
        }

        return Room::PRICING_MODEL_PER_ROOM;
    }

    protected function validatePricingInputs($validator, Request $request): void
    {
        $capacity = max(1, (int) $request->input('capacity', 1));
        $pricingModel = $this->detectPricingModel($request);
        $legacyPrice = (float) $request->input('price', 0);
        $pricePerRoom = $this->parseNullableFloat($request->input('price_per_room'));
        $pricePerBed = $this->parseNullableFloat($request->input('price_per_bed'));

        if (($pricePerRoom === null || $pricePerRoom <= 0) && $legacyPrice > 0) {
            $pricePerRoom = $legacyPrice;
        }

        if (($pricePerBed === null || $pricePerBed <= 0) && $legacyPrice > 0) {
            $pricePerBed = round($legacyPrice / $capacity, 2);
        }

        if ($pricingModel === Room::PRICING_MODEL_PER_ROOM) {
            if (($pricePerRoom ?? 0) <= 0) {
                $validator->errors()->add('price_per_room', 'Per-room monthly price is required for per room pricing.');
            }
            return;
        }

        if ($pricingModel === Room::PRICING_MODEL_PER_BED) {
            if (($pricePerBed ?? 0) <= 0) {
                $validator->errors()->add('price_per_bed', 'Per-bed monthly price is required for per bed pricing.');
            }
            return;
        }

        if (($pricePerRoom ?? 0) <= 0) {
            $validator->errors()->add('price_per_room', 'Per-room monthly price is required for hybrid pricing.');
        }

        if (($pricePerBed ?? 0) <= 0) {
            $validator->errors()->add('price_per_bed', 'Per-bed monthly price is required for hybrid pricing.');
        }
    }

    protected function resolvePricingPayload(Request $request, int $capacity, ?float $fallbackPrice = null): array
    {
        $capacity = max(1, $capacity);
        $pricingModel = $this->detectPricingModel($request);
        $legacyPrice = (float) $request->input('price', $fallbackPrice ?? 0);
        $pricePerRoom = $this->parseNullableFloat($request->input('price_per_room'));
        $pricePerBed = $this->parseNullableFloat($request->input('price_per_bed'));

        if (($pricePerRoom === null || $pricePerRoom <= 0) && $legacyPrice > 0) {
            $pricePerRoom = $legacyPrice;
        }

        if (($pricePerBed === null || $pricePerBed <= 0) && $legacyPrice > 0) {
            $pricePerBed = round($legacyPrice / $capacity, 2);
        }

        if ($pricingModel === Room::PRICING_MODEL_PER_ROOM) {
            $pricePerRoom = max(0.0, (float) $pricePerRoom);
            if (($pricePerBed ?? 0) <= 0) {
                $pricePerBed = round($pricePerRoom / $capacity, 2);
            }
            $price = $pricePerRoom;
        } elseif ($pricingModel === Room::PRICING_MODEL_PER_BED) {
            $pricePerBed = max(0.0, (float) $pricePerBed);
            if (($pricePerRoom ?? 0) <= 0) {
                $pricePerRoom = round($pricePerBed * $capacity, 2);
            }
            $price = $pricePerBed;
        } else {
            $pricePerRoom = max(0.0, (float) $pricePerRoom);
            $pricePerBed = max(0.0, (float) $pricePerBed);
            if ($pricePerRoom <= 0 && $pricePerBed > 0) {
                $pricePerRoom = round($pricePerBed * $capacity, 2);
            }
            if ($pricePerBed <= 0 && $pricePerRoom > 0) {
                $pricePerBed = round($pricePerRoom / $capacity, 2);
            }
            $price = min($pricePerRoom, $pricePerBed);
        }

        return [
            'pricing_model' => $pricingModel,
            'price_per_room' => round((float) $pricePerRoom, 2),
            'price_per_bed' => round((float) $pricePerBed, 2),
            'price' => round((float) $price, 2),
        ];
    }

    public function landlordIndex()
    {
        $this->ensureLandlord();
        $properties = Property::where('landlord_id', Auth::id())
            ->orderBy('name')
            ->get(['id', 'name', 'address']);

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

        return view('landlord.rooms.landlord_index', compact('rooms', 'properties'));
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
        $supportsAdvanceRequirement = Schema::hasColumn('rooms', 'requires_advance_payment');
        return view('landlord.rooms.create', compact('property', 'supportsAdvanceRequirement'));
    }

    public function store(Request $request, $propertyId)
    {
        $property = $this->getOwnedProperty($propertyId);
        $supportsAdvanceRequirement = Schema::hasColumn('rooms', 'requires_advance_payment');
        $validator = Validator::make($request->all(), [
            'room_number' => 'required|string|max:50',
            'capacity' => 'required|integer|min:1',
            'slots_available' => 'nullable|integer|min:0|lte:capacity',
            'status' => 'required|in:available,occupied,maintenance',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp,gif|max:2048',
            'inclusions' => 'nullable|string|max:2000',
            'requires_advance_payment' => $supportsAdvanceRequirement ? 'nullable|boolean' : 'nullable',
            'detail_images.*' => 'nullable|image|mimes:jpg,jpeg,png,webp,gif|max:4096',
            'detail_labels.*' => 'nullable|string|max:100',
        ] + $this->pricingRules());

        $validator->after(function ($validator) use ($request) {
            $this->validatePricingInputs($validator, $request);
        });

        try {
            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }
        } catch (\Symfony\Component\Mime\Exception\LogicException $e) {
            return back()->withInput()->with('error', 'File upload validation failed. Please ensure the PHP "fileinfo" extension is enabled and try a smaller image if needed (PHP upload limit).');
        }

        $validated = $validator->validated();
        unset($validated['image']);
        $validated = array_merge(
            $validated,
            $this->resolvePricingPayload($request, (int) $validated['capacity'])
        );

        if ($supportsAdvanceRequirement) {
            $validated['requires_advance_payment'] = $request->boolean('requires_advance_payment');
        }

        if ($request->hasFile('image')) {
            try {
                $validated['image_path'] = str_replace('\\', '/', $request->file('image')->store('rooms', 'public'));
            } catch (\Symfony\Component\Mime\Exception\LogicException $e) {
                return back()->withInput()->with('error', 'Unable to process the uploaded image. Please ensure the PHP "fileinfo" extension is enabled and try a smaller image if needed (PHP upload limit).');
            }
        }

        $validated['slots_available'] = array_key_exists('slots_available', $validated)
            ? (int) $validated['slots_available']
            : ((string) $validated['status'] === 'available' ? (int) $validated['capacity'] : 0);

        if ((string) $validated['status'] !== 'available') {
            $validated['slots_available'] = 0;
        }

        $room = $property->rooms()->create($validated);
        $room->syncAvailabilitySnapshot();

        if ($request->hasFile('detail_images')) {
            $labels = $request->input('detail_labels', []);
            foreach ($request->file('detail_images') as $i => $file) {
                if (!$file || !$file->isValid()) {
                    continue;
                }
                try {
                    $path = str_replace('\\', '/', $file->store('rooms', 'public'));
                    RoomImage::create([
                        'room_id' => $room->id,
                        'image_path' => $path,
                        'label' => $labels[$i] ?? null,
                        'sort_order' => $i,
                    ]);
                } catch (\Exception $e) {
                    // Skip failed detail image uploads and keep room creation successful.
                }
            }
        }

        $this->syncPropertyPriceRange($property);

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
        $supportsAdvanceRequirement = Schema::hasColumn('rooms', 'requires_advance_payment');
        return view('landlord.rooms.edit', compact('property', 'room', 'roomImages', 'supportsAdvanceRequirement'));
    }

    public function update(Request $request, $propertyId, Room $room)
    {
        $property = $this->getOwnedProperty($propertyId);
        if ($room->property_id !== $property->id) {
            abort(404);
        }
        $this->authorize('update', $room);
        $supportsAdvanceRequirement = Schema::hasColumn('rooms', 'requires_advance_payment');
        $validator = Validator::make($request->all(), [
            'room_number'            => 'required|string|max:50',
            'capacity'               => 'required|integer|min:1',
            'slots_available'        => 'nullable|integer|min:0|lte:capacity',
            'status'                 => 'required|in:available,occupied,maintenance',
            'image'                  => 'nullable|image|mimes:jpg,jpeg,png,webp,gif|max:4096',
            'inclusions'             => 'nullable|string|max:2000',
            'requires_advance_payment' => $supportsAdvanceRequirement ? 'nullable|boolean' : 'nullable',
            'detail_images.*'        => 'nullable|image|mimes:jpg,jpeg,png,webp,gif|max:4096',
            'detail_labels.*'        => 'nullable|string|max:100',
            'delete_detail_images'   => 'nullable|array',
            'delete_detail_images.*' => 'integer',
        ] + $this->pricingRules());

        $validator->after(function ($validator) use ($request) {
            $this->validatePricingInputs($validator, $request);
        });

        try {
            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }
        } catch (\Symfony\Component\Mime\Exception\LogicException $e) {
            return back()->withInput()->with('error', 'File upload validation failed. Please ensure the PHP "fileinfo" extension is enabled and try a smaller image if needed (PHP upload limit).');
        }

        $validated = $validator->validated();
        unset($validated['image']);
        $validated = array_merge(
            $validated,
            $this->resolvePricingPayload($request, (int) $validated['capacity'], (float) ($room->price ?? 0))
        );

        if ($supportsAdvanceRequirement) {
            $validated['requires_advance_payment'] = $request->boolean('requires_advance_payment');
        }

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

        $validated['slots_available'] = array_key_exists('slots_available', $validated)
            ? (int) $validated['slots_available']
            : ($room->slots_available ?? 0);

        if ((string) $validated['status'] !== 'available') {
            $validated['slots_available'] = 0;
        }

        $room->update($validated);
        $room->syncAvailabilitySnapshot();
        $this->syncPropertyPriceRange($property);

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
        $this->syncPropertyPriceRange($property);
        return redirect()->route('landlord.properties.show', $property->id)
            ->with('success', 'Room deleted.');
    }

    // Quick store endpoint from dashboard modal (property id already known)
    public function quickStore(Request $request, $propertyId)
    {
        $property = $this->getOwnedProperty($propertyId);
        $this->authorize('update', $property);
        $supportsAdvanceRequirement = Schema::hasColumn('rooms', 'requires_advance_payment');
        $validator = Validator::make($request->all(), [
            'room_number' => 'required|string|max:50',
            'capacity' => 'required|integer|min:1',
            'slots_available' => 'nullable|integer|min:0|lte:capacity',
            'status' => 'required|in:available,occupied,maintenance',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp,gif|max:2048',
            'inclusions' => 'nullable|string|max:2000',
            'requires_advance_payment' => $supportsAdvanceRequirement ? 'nullable|boolean' : 'nullable',
        ] + $this->pricingRules());

        $validator->after(function ($validator) use ($request) {
            $this->validatePricingInputs($validator, $request);
        });

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
        $validated = array_merge(
            $validated,
            $this->resolvePricingPayload($request, (int) $validated['capacity'])
        );

        if ($supportsAdvanceRequirement) {
            $validated['requires_advance_payment'] = $request->boolean('requires_advance_payment');
        }

        if ($request->hasFile('image')) {
            try {
                $validated['image_path'] = str_replace('\\', '/', $request->file('image')->store('rooms', 'public'));
            } catch (\Symfony\Component\Mime\Exception\LogicException $e) {
                return redirect()->route('landlord.dashboard')
                    ->withInput()
                    ->with('error', 'Unable to process the uploaded image. Please ensure the PHP "fileinfo" extension is enabled and try a smaller image if needed (PHP upload limit).');
            }
        }

        $validated['slots_available'] = array_key_exists('slots_available', $validated)
            ? (int) $validated['slots_available']
            : ((string) $validated['status'] === 'available' ? (int) $validated['capacity'] : 0);

        if ((string) $validated['status'] !== 'available') {
            $validated['slots_available'] = 0;
        }

        $room = $property->rooms()->create($validated);
        $room->syncAvailabilitySnapshot();
        $this->syncPropertyPriceRange($property);
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
            'feedbacks' => function ($query) {
                $query->with('user:id,full_name')
                    ->latest()
                    ->take(8);
            },
        ]);

        $room->loadCount('feedbacks')->loadAvg('feedbacks', 'rating');

        if (!\App\Models\Property::query()->visibleToAudience()->whereKey($room->property_id)->exists()) {
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
        $search = trim((string) $request->query('q', ''));
        $supportsBuildingInclusions = Schema::hasColumn('properties', 'building_inclusions');
        $amenityOptions = (array) config('property_amenities.flat', []);

        $amenitiesInput = $request->query('amenities', []);
        if (!is_array($amenitiesInput)) {
            $amenitiesInput = [$amenitiesInput];
        }

        $selectedAmenities = collect($amenitiesInput)
            ->map(fn ($value) => trim((string) $value))
            ->filter(fn ($value) => $value !== '')
            ->unique()
            ->values();

        if (!$supportsBuildingInclusions) {
            $selectedAmenities = collect();
        } else {
            $selectedAmenities = $selectedAmenities
                ->filter(fn ($value) => array_key_exists($value, $amenityOptions))
                ->values();
        }

        $ratingFilter = strtolower(trim((string) $request->query('rating', 'any')));
        if (!in_array($ratingFilter, ['any', '4_up', '3_up', 'unrated'], true)) {
            $ratingFilter = 'any';
        }

        $occupancyFilter = strtolower(trim((string) $request->query('occupancy', 'all')));
        if (!in_array($occupancyFilter, ['all', Room::PRICING_MODEL_PER_ROOM, Room::PRICING_MODEL_PER_BED], true)) {
            $occupancyFilter = 'all';
        }

        $roomsBaseQuery = Room::with('property.landlord')
            ->withAvg('feedbacks', 'rating')
            ->withCount('feedbacks')
            ->where('status', '!=', 'maintenance')
            ->whereHas('property', function ($q) {
                $q->visibleToAudience();
            })
            ->when($selectedAmenities->isNotEmpty(), function ($q) use ($selectedAmenities) {
                $q->whereHas('property', function ($propertyQuery) use ($selectedAmenities) {
                    $propertyQuery->where(function ($amenityQuery) use ($selectedAmenities) {
                        foreach ($selectedAmenities as $amenityKey) {
                            $amenityQuery->orWhereJsonContains('building_inclusions', $amenityKey);
                        }
                    });
                });
            })
            ->when($search !== '', function ($q) use ($search) {
                $q->whereHas('property', fn ($pq) => $pq->where('address', 'like', "%{$search}%"));
            });

        $allCandidateRooms = $roomsBaseQuery
            ->orderBy('property_id')
            ->orderBy('room_number')
            ->get();

        $resolveComparablePrice = function (Room $room) use ($occupancyFilter): float {
            $pricingModel = method_exists($room, 'resolvePricingModel')
                ? $room->resolvePricingModel()
                : Room::PRICING_MODEL_PER_ROOM;
            $effectivePerRoom = $room->effectivePricePerRoom();
            $effectivePerBed = $room->effectivePricePerBed();

            // Keep hybrid rooms visible in both occupancy modes by comparing against
            // the more permissive (lower) monthly value.
            if ($pricingModel === Room::PRICING_MODEL_HYBRID && $occupancyFilter !== 'all') {
                return min($effectivePerRoom, $effectivePerBed);
            }

            if ($occupancyFilter === Room::PRICING_MODEL_PER_BED) {
                return $effectivePerBed;
            }

            if ($occupancyFilter === Room::PRICING_MODEL_PER_ROOM) {
                return $effectivePerRoom;
            }

            $listingPricingMode = method_exists($room, 'resolveListingPricingMode')
                ? $room->resolveListingPricingMode()
                : Room::PRICING_MODEL_PER_ROOM;

            return match ($listingPricingMode) {
                Room::PRICING_MODEL_PER_BED => $effectivePerBed,
                Room::PRICING_MODEL_PER_ROOM => $effectivePerRoom,
                default => min($effectivePerRoom, $effectivePerBed),
            };
        };

        $priceSamples = $allCandidateRooms
            ->map(fn (Room $room) => $resolveComparablePrice($room))
            ->filter(fn ($value) => is_numeric($value) && (float) $value > 0)
            ->values();

        $priceBoundsMin = $priceSamples->isNotEmpty() ? (int) floor((float) $priceSamples->min()) : 0;
        $priceBoundsMax = $priceSamples->isNotEmpty() ? (int) ceil((float) $priceSamples->max()) : 10000;
        if ($priceBoundsMax <= $priceBoundsMin) {
            $priceBoundsMax = $priceBoundsMin + 1000;
        }

        $minPrice = $request->query('min_price', $priceBoundsMin);
        $maxPrice = $request->query('max_price', $priceBoundsMax);

        $minPrice = is_numeric($minPrice) ? (float) $minPrice : (float) $priceBoundsMin;
        $maxPrice = is_numeric($maxPrice) ? (float) $maxPrice : (float) $priceBoundsMax;
        $minPrice = max((float) $priceBoundsMin, min((float) $priceBoundsMax, $minPrice));
        $maxPrice = max((float) $priceBoundsMin, min((float) $priceBoundsMax, $maxPrice));
        if ($minPrice > $maxPrice) {
            [$minPrice, $maxPrice] = [$maxPrice, $minPrice];
        }

        $matchesOccupancyFilter = function (Room $room) use ($occupancyFilter): bool {
            if ($occupancyFilter === 'all') {
                return true;
            }

            $pricingModel = method_exists($room, 'resolvePricingModel')
                ? $room->resolvePricingModel()
                : Room::PRICING_MODEL_PER_ROOM;

            // Hybrid listings should appear in both per-room and bed-spacer filters.
            if ($pricingModel === Room::PRICING_MODEL_HYBRID) {
                return true;
            }

            $listingPricingMode = method_exists($room, 'resolveListingPricingMode')
                ? $room->resolveListingPricingMode()
                : $room->resolvePricingModel();

            if ($occupancyFilter === Room::PRICING_MODEL_PER_ROOM) {
                return in_array($listingPricingMode, [Room::PRICING_MODEL_PER_ROOM, 'both'], true);
            }

            return in_array($listingPricingMode, [Room::PRICING_MODEL_PER_BED, 'both'], true);
        };

        $matchesRatingFilter = function (Room $room) use ($ratingFilter): bool {
            $feedbackCount = (int) ($room->feedbacks_count ?? 0);
            $avgRating = (float) ($room->feedbacks_avg_rating ?? 0);

            return match ($ratingFilter) {
                '4_up' => $feedbackCount > 0 && $avgRating >= 4.0,
                '3_up' => $feedbackCount > 0 && $avgRating >= 3.0,
                'unrated' => $feedbackCount === 0,
                default => true,
            };
        };

        $filteredRooms = $allCandidateRooms
            ->filter(function (Room $room) use ($resolveComparablePrice, $minPrice, $maxPrice, $matchesOccupancyFilter, $matchesRatingFilter) {
                if (!$matchesOccupancyFilter($room) || !$matchesRatingFilter($room)) {
                    return false;
                }

                $price = $resolveComparablePrice($room);
                return $price >= $minPrice && $price <= $maxPrice;
            })
            ->values();

        // Recommended rooms (available slots only)
        $recommendedRooms = $filteredRooms
            ->filter(fn (Room $room) => $room->hasAvailableSlots())
            ->sortBy(fn (Room $room) => $resolveComparablePrice($room))
            ->values()
            ->take(6);

        // All rooms for grouped rendering
        $allRooms = $filteredRooms
            ->sort(function (Room $a, Room $b) {
                if ((int) $a->property_id !== (int) $b->property_id) {
                    return ((int) $a->property_id) <=> ((int) $b->property_id);
                }

                return strnatcasecmp((string) $a->room_number, (string) $b->room_number);
            })
            ->values();

        $propertyAddressSuggestions = $allCandidateRooms
            ->pluck('property.address')
            ->map(fn ($value) => trim((string) $value))
            ->filter(fn ($value) => $value !== '')
            ->unique()
            ->sort()
            ->values();

        $showAdvancedFilters = $selectedAmenities->isNotEmpty()
            || $ratingFilter !== 'any'
            || $occupancyFilter !== 'all'
            || (int) round($minPrice) !== $priceBoundsMin
            || (int) round($maxPrice) !== $priceBoundsMax;

        $newThreshold = now()->subDays(3);

        return view('student.rooms.index', compact(
            'recommendedRooms',
            'allRooms',
            'propertyAddressSuggestions',
            'minPrice',
            'maxPrice',
            'priceBoundsMin',
            'priceBoundsMax',
            'selectedAmenities',
            'ratingFilter',
            'occupancyFilter',
            'showAdvancedFilters',
            'amenityOptions',
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
            'property:id,name,address,landlord_id,image_path,approval_status,house_rules',
            'property.landlord:id,full_name',
            'roomImages',
            'feedbacks.user:id,full_name',
        ]);

        if (!\App\Models\Property::query()->visibleToAudience()->whereKey($room->property_id)->exists()) {
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
            ->whereDate('check_in', '<=', $today)
            ->exists();

        $alreadyFeedback = \App\Models\RoomFeedback::where('room_id', $room->id)
            ->where('user_id', Auth::id())
            ->first();

        $feedbacks = $room->feedbacks;
        $avgRating = $feedbacks->isNotEmpty() ? round($feedbacks->avg('rating'), 1) : null;
        $feedbackDistribution = collect([5, 4, 3, 2, 1])->map(function (int $stars) use ($feedbacks) {
            $count = $feedbacks->where('rating', $stars)->count();
            $total = $feedbacks->count();

            return [
                'stars' => $stars,
                'count' => $count,
                'percent' => $total > 0 ? (int) round(($count / $total) * 100) : 0,
            ];
        });

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
            'feedbackDistribution',
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

    // Student-authenticated dedicated feedback page
    public function studentFeedback(Room $room)
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
            return redirect()->route('student.rooms.feedback_page', $tenantOnboarding->booking->room_id);
        }

        $tenantMode = !empty($tenantOnboarding);

        $room->load([
            'property:id,name,address,landlord_id,image_path,approval_status',
            'property.landlord:id,full_name',
            'feedbacks.user:id,full_name',
        ]);

        if (!\App\Models\Property::query()->visibleToAudience()->whereKey($room->property_id)->exists()) {
            abort(404);
        }

        $canFeedback = $room->bookings()
            ->where('student_id', Auth::id())
            ->where('status', 'approved')
            ->whereDate('check_in', '<=', $today)
            ->exists();

        $alreadyFeedback = \App\Models\RoomFeedback::where('room_id', $room->id)
            ->where('user_id', Auth::id())
            ->first();

        $feedbacks = $room->feedbacks;
        $avgRating = $feedbacks->isNotEmpty() ? round($feedbacks->avg('rating'), 1) : null;
        $feedbackDistribution = collect([5, 4, 3, 2, 1])->map(function (int $stars) use ($feedbacks) {
            $count = $feedbacks->where('rating', $stars)->count();
            $total = $feedbacks->count();

            return [
                'stars' => $stars,
                'count' => $count,
                'percent' => $total > 0 ? (int) round(($count / $total) * 100) : 0,
            ];
        });

        return view('student.rooms.feedback', compact(
            'room',
            'feedbacks',
            'avgRating',
            'feedbackDistribution',
            'canFeedback',
            'alreadyFeedback',
            'tenantMode'
        ));
    }
}
