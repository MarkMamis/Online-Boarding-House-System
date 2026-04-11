<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\Room;
use App\Notifications\SystemNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class PropertyController extends Controller
{
    protected function ensureLandlord()
    {
        if (!Auth::check() || Auth::user()->role !== 'landlord') {
            abort(403, 'Unauthorized');
        }
    }

    protected function ensureAdmin()
    {
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            abort(403, 'Unauthorized');
        }
    }

    public function adminPending()
    {
        $this->ensureAdmin();

        $statusFilter = strtolower((string) request('status', 'pending'));
        if (!in_array($statusFilter, ['pending', 'approved', 'rejected', 'all'], true)) {
            $statusFilter = 'pending';
        }

        $propertiesQuery = Property::with(['landlord']);
        if ($statusFilter !== 'all') {
            $propertiesQuery->where('approval_status', $statusFilter);
        }

        $properties = $propertiesQuery
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        $counts = [
            'pending' => Property::where('approval_status', 'pending')->count(),
            'approved' => Property::where('approval_status', 'approved')->count(),
            'rejected' => Property::where('approval_status', 'rejected')->count(),
            'all' => Property::count(),
        ];

        return view('admin.properties.pending', compact('properties', 'statusFilter', 'counts'));
    }

    public function adminShow(Property $property)
    {
        $this->ensureAdmin();

        $today = now()->toDateString();

        $property->load([
            'landlord.landlordProfile',
            'rooms' => function ($query) use ($today) {
                $query->with(['roomImages'])
                    ->withCount([
                        'bookings as active_bookings_count' => function ($bookingQuery) use ($today) {
                            $bookingQuery->where('status', 'approved')
                                ->where('check_in', '<=', $today)
                                ->where('check_out', '>', $today);
                        },
                    ])
                    ->orderBy('room_number');
            },
        ])->loadCount([
            'rooms as total_rooms',
            'rooms as available_rooms' => function ($query) {
                $query->where('status', 'available')->where('slots_available', '>', 0);
            },
            'rooms as occupied_rooms' => function ($query) use ($today) {
                $query->whereHas('bookings', function ($bookingQuery) use ($today) {
                    $bookingQuery->where('status', 'approved')
                        ->where('check_in', '<=', $today)
                        ->where('check_out', '>', $today);
                });
            },
        ]);

        $priceValues = $property->rooms
            ->pluck('price')
            ->filter(fn ($price) => is_numeric($price) && (float) $price > 0)
            ->map(fn ($price) => (float) $price)
            ->values();

        $minPrice = $priceValues->isNotEmpty() ? $priceValues->min() : null;
        $maxPrice = $priceValues->isNotEmpty() ? $priceValues->max() : null;

        $amenityLabelMap = collect((array) config('property_amenities.flat', []))
            ->mapWithKeys(fn ($label, $key) => [strtolower((string) $key) => (string) $label]);

        $buildingServices = collect((array) ($property->building_inclusions ?? []))
            ->map(fn ($item) => trim((string) $item))
            ->filter()
            ->map(function (string $item) use ($amenityLabelMap): string {
                $normalized = strtolower($item);

                if ($amenityLabelMap->has($normalized)) {
                    return (string) $amenityLabelMap->get($normalized);
                }

                return preg_match('/[A-Z]/', $item)
                    ? $item
                    : ucwords(str_replace(['_', '-'], ' ', $item));
            });

        $roomServices = $property->rooms
            ->flatMap(function ($room) {
                return collect(preg_split('/[,\n;]+/', (string) $room->inclusions))
                    ->map(fn ($item) => trim($item))
                    ->filter();
            })
            ->map(fn ($item) => strtolower($item))
            ->unique()
            ->values()
            ->map(fn ($item) => ucwords($item));

        $servicesOffered = $buildingServices
            ->concat($roomServices)
            ->filter()
            ->values()
            ->unique(fn ($item) => strtolower((string) $item))
            ->values();

        $occupancyRate = (int) ($property->total_rooms > 0
            ? round((($property->occupied_rooms ?? 0) / $property->total_rooms) * 100)
            : 0);

        return view('admin.properties.show', compact(
            'property',
            'minPrice',
            'maxPrice',
            'servicesOffered',
            'occupancyRate'
        ));
    }

    public function adminApprove(Property $property)
    {
        $this->ensureAdmin();

        $property->update([
            'approval_status' => 'approved',
            'approved_at' => now(),
            'approved_by' => Auth::id(),
            'rejected_at' => null,
            'rejected_by' => null,
            'rejection_reason' => null,
        ]);

        $property->loadMissing('landlord');
        if ($property->landlord) {
            try {
                $property->landlord->notify(new SystemNotification(
                    'Property approved',
                    sprintf('Your property "%s" has been approved and is now visible to students.', $property->name),
                    route('landlord.properties.index'),
                    ['property_id' => $property->id, 'status' => 'approved']
                ));
            } catch (\Throwable $e) {
                // ignore
            }

            try {
                Mail::raw(
                    sprintf(
                        "Good news! Your property '%s' has been approved and is now visible to students.",
                        $property->name
                    ),
                    function ($message) use ($property) {
                        $message->to($property->landlord->email)->subject('Property Approved');
                    }
                );
            } catch (\Throwable $e) {
                // ignore email transport errors
            }
        }

        return back()->with('success', 'Property approved and published to students.');
    }

    public function adminReject(Request $request, Property $property)
    {
        $this->ensureAdmin();

        $request->validate([
            'rejection_reason' => 'nullable|string|max:500',
        ]);

        $property->update([
            'approval_status' => 'rejected',
            'rejected_at' => now(),
            'rejected_by' => Auth::id(),
            'rejection_reason' => $request->input('rejection_reason'),
            'approved_at' => null,
            'approved_by' => null,
        ]);

        $property->loadMissing('landlord');
        if ($property->landlord) {
            $reason = (string) $property->rejection_reason;
            try {
                $property->landlord->notify(new SystemNotification(
                    'Property rejected',
                    $reason !== ''
                        ? sprintf('Your property "%s" was rejected: %s', $property->name, $reason)
                        : sprintf('Your property "%s" was rejected.', $property->name),
                    route('landlord.properties.index'),
                    ['property_id' => $property->id, 'status' => 'rejected']
                ));
            } catch (\Throwable $e) {
                // ignore
            }

            try {
                Mail::raw(
                    $reason !== ''
                        ? sprintf("Your property '%s' was rejected. Reason: %s", $property->name, $reason)
                        : sprintf("Your property '%s' was rejected.", $property->name),
                    function ($message) use ($property) {
                        $message->to($property->landlord->email)->subject('Property Rejected');
                    }
                );
            } catch (\Throwable $e) {
                // ignore email transport errors
            }
        }

        return back()->with('success', 'Property rejected.');
    }

    public function index()
    {
        $this->ensureLandlord();
        $properties = Property::where('landlord_id', Auth::id())
            ->withCount([
                'rooms as rooms_total_live',
                'rooms as rooms_vacant_live' => function ($q) {
                    $q->where('status', 'available')->where('slots_available', '>', 0);
                },
            ])
            ->orderBy('created_at','desc')
            ->get();
        return view('landlord.properties.index', compact('properties'));
    }

    public function create()
    {
        $this->ensureLandlord();
        $this->authorize('create', Property::class);
        Log::info('Property create route visited', ['user' => Auth::id()]);
        return view('landlord.properties.create');
    }

    public function store(Request $request)
    {
        $this->ensureLandlord();
        $this->authorize('create', Property::class);
        Log::info('Property store called', ['user' => Auth::id(), 'data' => $request->all()]);
        $supportsBuildingInclusions = Schema::hasColumn('properties', 'building_inclusions');
        $supportsHouseRules = Schema::hasColumn('properties', 'house_rules');
        $allowedAmenities = array_keys((array) config('property_amenities.flat', []));
        $houseRuleCategories = (array) config('property_house_rules.categories', []);
        $isDashboardQuickCreate = $request->boolean('from_dashboard');
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'description' => 'nullable|string',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:5120',
            'building_inclusions' => $supportsBuildingInclusions ? 'nullable|array' : 'nullable',
            'building_inclusions.*' => $supportsBuildingInclusions ? ['string', Rule::in($allowedAmenities)] : 'nullable',
            'building_inclusion_custom' => $supportsBuildingInclusions ? 'nullable|array' : 'nullable',
            'building_inclusion_custom.*' => $supportsBuildingInclusions ? 'nullable|array' : 'nullable',
            'building_inclusion_custom.*.*' => $supportsBuildingInclusions ? 'nullable|string|max:100' : 'nullable',
            'house_rules' => $supportsHouseRules ? 'nullable|array' : 'nullable',
            // First room is required on full create flow, optional on dashboard quick create
            'initial_room_number' => [Rule::requiredIf(!$isDashboardQuickCreate), 'string', 'max:50'],
            'initial_capacity' => [Rule::requiredIf(!$isDashboardQuickCreate), 'integer', 'min:1'],
            'initial_price' => [Rule::requiredIf(!$isDashboardQuickCreate), 'numeric', 'min:0'],
            'initial_status' => [Rule::requiredIf(!$isDashboardQuickCreate), 'in:available,occupied,maintenance'],
        ]);

        foreach (array_keys($houseRuleCategories) as $categoryKey) {
            $validator->addRules([
                'house_rules.' . $categoryKey => $supportsHouseRules ? 'nullable|string|max:4000' : 'nullable',
            ]);
        }

        try {
            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }
        } catch (\Symfony\Component\Mime\Exception\LogicException $e) {
            return back()->withInput()->with('error', 'File upload validation failed. Please ensure the PHP "fileinfo" extension is enabled and try a smaller image if needed (PHP upload limit).');
        }

        $imagePath = null;
        if ($request->hasFile('image')) {
            try {
                $storedPath = $request->file('image')->store('properties', 'public');
                $imagePath = str_replace('\\', '/', $storedPath);
            } catch (\Symfony\Component\Mime\Exception\LogicException $e) {
                return back()->withInput()->with('error', 'Unable to process the uploaded image. Please ensure the PHP "fileinfo" extension is enabled and try a smaller image if needed (PHP upload limit).');
            }
        }

        $selectedAmenities = collect($request->input('building_inclusions', []))
            ->map(fn ($item) => (string) $item)
            ->filter(fn ($item) => in_array($item, $allowedAmenities, true))
            ->unique()
            ->values()
            ->all();

        $customAmenities = collect((array) $request->input('building_inclusion_custom', []))
            ->flatMap(fn ($items) => collect((array) $items))
            ->map(fn ($item) => trim((string) $item))
            ->filter()
            ->unique(fn ($item) => strtolower((string) $item))
            ->values()
            ->all();

        $submittedCustomInclusionPayload = $request->boolean('building_inclusion_custom_present') || $request->has('building_inclusion_custom');
        if (!$submittedCustomInclusionPayload) {
            $existingCustomAmenities = collect((array) ($property->building_inclusions ?? []))
                ->map(fn ($item) => trim((string) $item))
                ->filter(fn ($item) => $item !== '' && !in_array($item, $allowedAmenities, true))
                ->values()
                ->all();

            $customAmenities = collect($customAmenities)
                ->concat($existingCustomAmenities)
                ->unique(fn ($item) => strtolower((string) $item))
                ->values()
                ->all();
        }

        $selectedAmenities = collect($selectedAmenities)
            ->concat($customAmenities)
            ->unique(fn ($item) => strtolower((string) $item))
            ->values()
            ->all();

        $houseRulesPayload = [];
        $hasHouseRuleInput = false;
        foreach (array_keys($houseRuleCategories) as $categoryKey) {
            $rawValue = (string) $request->input('house_rules.' . $categoryKey, '');
            if (trim($rawValue) !== '') {
                $hasHouseRuleInput = true;
            }

            $rawLines = preg_split('/\r\n|\r|\n/', $rawValue) ?: [];
            $cleanedLines = collect($rawLines)
                ->map(fn ($line) => trim((string) $line))
                ->filter()
                ->unique()
                ->values()
                ->all();

            if (!empty($cleanedLines)) {
                $houseRulesPayload[$categoryKey] = $cleanedLines;
            }
        }

        $propertyData = [
            'landlord_id' => Auth::id(),
            'image_path' => $imagePath,
            'approval_status' => 'pending',
            'name' => $request->name,
            'address' => $request->address,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'description' => $request->description,
        ];
        if ($supportsBuildingInclusions) {
            $propertyData['building_inclusions'] = $selectedAmenities;
        }
        if ($supportsHouseRules) {
            $propertyData['house_rules'] = empty($houseRulesPayload) ? null : $houseRulesPayload;
        }

        $property = Property::create($propertyData);

        // Geocode address only if coordinates not provided
        if (!$request->filled('latitude') || !$request->filled('longitude')) {
            $geo = app(\App\Services\GeocodingService::class)->geocodeAddress($property->address);
            if ($geo) {
                $property->latitude = $geo['lat'];
                $property->longitude = $geo['lng'];
                $property->save();
            }
        }
        // If room fields provided, create first room
        if ($request->filled('initial_room_number')) {
            $initialCapacity = (int) ($request->initial_capacity ?: 1);
            $initialStatus = (string) ($request->initial_status ?: 'available');

            Room::create([
                'property_id' => $property->id,
                'room_number' => $request->initial_room_number,
                'capacity' => $initialCapacity,
                'slots_available' => $initialStatus === 'available' ? $initialCapacity : 0,
                'price' => $request->initial_price ?: 0,
                'status' => $initialStatus,
            ]);

            $property->refreshPriceRange();
        }

        $successMsg = 'Property created successfully.' . ($request->filled('initial_room_number') ? ' First room added.' : '');
        if ($request->boolean('from_dashboard')) {
            return redirect()->route('landlord.dashboard')->with('success', $successMsg);
        }
        return redirect()->route('landlord.properties.show', $property->id)->with('success', $successMsg);       
    }

    public function edit(Property $property)
    {
        $this->ensureLandlord();
        $this->authorize('update', $property);
        return view('landlord.properties.edit', compact('property'));
    }

    public function update(Request $request, Property $property)
    {
        $this->ensureLandlord();
        $this->authorize('update', $property);
        $supportsBuildingInclusions = Schema::hasColumn('properties', 'building_inclusions');
        $supportsHouseRules = Schema::hasColumn('properties', 'house_rules');
        $allowedAmenities = array_keys((array) config('property_amenities.flat', []));
        $houseRuleCategories = (array) config('property_house_rules.categories', []);
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'description' => 'nullable|string',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:5120',
            'building_inclusions' => $supportsBuildingInclusions ? 'nullable|array' : 'nullable',
            'building_inclusions.*' => $supportsBuildingInclusions ? ['string', Rule::in($allowedAmenities)] : 'nullable',
            'building_inclusion_custom' => $supportsBuildingInclusions ? 'nullable|array' : 'nullable',
            'building_inclusion_custom.*' => $supportsBuildingInclusions ? 'nullable|array' : 'nullable',
            'building_inclusion_custom.*.*' => $supportsBuildingInclusions ? 'nullable|string|max:100' : 'nullable',
            'house_rules' => $supportsHouseRules ? 'nullable|array' : 'nullable',
        ]);

        foreach (array_keys($houseRuleCategories) as $categoryKey) {
            $validator->addRules([
                'house_rules.' . $categoryKey => $supportsHouseRules ? 'nullable|string|max:4000' : 'nullable',
            ]);
        }

        try {
            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }
        } catch (\Symfony\Component\Mime\Exception\LogicException $e) {
            return back()->withInput()->with('error', 'File upload validation failed. Please ensure the PHP "fileinfo" extension is enabled and try a smaller image if needed (PHP upload limit).');
        }

        $selectedAmenities = collect($request->input('building_inclusions', []))
            ->map(fn ($item) => (string) $item)
            ->filter(fn ($item) => in_array($item, $allowedAmenities, true))
            ->unique()
            ->values()
            ->all();

        $customAmenities = collect((array) $request->input('building_inclusion_custom', []))
            ->flatMap(fn ($items) => collect((array) $items))
            ->map(fn ($item) => trim((string) $item))
            ->filter()
            ->unique(fn ($item) => strtolower((string) $item))
            ->values()
            ->all();

        $selectedAmenities = collect($selectedAmenities)
            ->concat($customAmenities)
            ->unique(fn ($item) => strtolower((string) $item))
            ->values()
            ->all();

        $houseRulesPayload = [];
        $hasHouseRuleInput = false;
        foreach (array_keys($houseRuleCategories) as $categoryKey) {
            $rawValue = (string) $request->input('house_rules.' . $categoryKey, '');
            if (trim($rawValue) !== '') {
                $hasHouseRuleInput = true;
            }

            $rawLines = preg_split('/\r\n|\r|\n/', $rawValue) ?: [];
            $cleanedLines = collect($rawLines)
                ->map(fn ($line) => trim((string) $line))
                ->filter()
                ->unique()
                ->values()
                ->all();

            if (!empty($cleanedLines)) {
                $houseRulesPayload[$categoryKey] = $cleanedLines;
            }
        }

        if ($request->hasFile('image')) {
            try {
                $storedPath = $request->file('image')->store('properties', 'public');
                $newImagePath = str_replace('\\', '/', $storedPath);
            } catch (\Symfony\Component\Mime\Exception\LogicException $e) {
                return back()->withInput()->with('error', 'Unable to process the uploaded image. Please ensure the PHP "fileinfo" extension is enabled and try a smaller image if needed (PHP upload limit).');
            }

            $oldImagePath = $property->image_path;
            $property->image_path = $newImagePath;

            if (!empty($oldImagePath) && $oldImagePath !== $newImagePath && Storage::disk('public')->exists($oldImagePath)) {
                Storage::disk('public')->delete($oldImagePath);
            }
        }

        $originalAddress = $property->address;
        $validated = $validator->validated();
        unset($validated['image']);
        if ($supportsBuildingInclusions) {
            $validated['building_inclusions'] = $selectedAmenities;
        }
        if ($supportsHouseRules) {
            $validated['house_rules'] = $hasHouseRuleInput
                ? (empty($houseRulesPayload) ? null : $houseRulesPayload)
                : $property->house_rules;
        }
        $property->fill($validated);
        $property->save();

        // Update coordinates if provided, otherwise geocode if address changed
        if ($request->filled('latitude') && $request->filled('longitude')) {
            $property->latitude = $request->latitude;
            $property->longitude = $request->longitude;
            $property->save();
        } elseif ($property->address !== $originalAddress) {
            $geo = app(\App\Services\GeocodingService::class)->geocodeAddress($property->address);
            if ($geo) {
                $property->latitude = $geo['lat'];
                $property->longitude = $geo['lng'];
                $property->save();
            }
        }

        $property->refreshPriceRange();

        return redirect()->route('landlord.properties.index')->with('success', 'Property updated.');
    }

    public function destroy(Property $property)
    {
        $this->ensureLandlord();
        $this->authorize('delete', $property);
        $property->delete();
        return redirect()->route('landlord.properties.index')->with('success', 'Property deleted.');
    }

    public function show(Property $property)
    {
        $this->ensureLandlord();
        $this->authorize('view', $property);

        $property->loadCount([
            'rooms as rooms_total_live',
            'rooms as rooms_vacant_live' => function ($q) {
                $q->where('status', 'available')->where('slots_available', '>', 0);
            },
        ])->load(['rooms' => function($q){
            $q->with(['bookings' => function($bookingQ) {
                $bookingQ->where('status', 'approved')
                  ->where('check_in', '<=', now()->toDateString())
                  ->where('check_out', '>', now()->toDateString())
                  ->with('student');
            }])
            ->orderBy('room_number');
        }]);

        // Add current tenant info to each room
        $property->rooms->each(function($room) {
            $currentBooking = $room->bookings->first();
            $room->current_tenant = $currentBooking ? $currentBooking->student : null;
            $room->current_booking = $currentBooking;
        });

        $today = now()->toDateString();
        $activeBookings = \App\Models\Booking::with(['room','student'])
            ->whereHas('room', function ($q) use ($property) { $q->where('property_id', $property->id); })
            ->where('status', 'approved')
            ->where('check_in', '<=', $today)
            ->where('check_out', '>', $today)
            ->orderByDesc('created_at')
            ->get();

        $pendingBookings = \App\Models\Booking::with(['room','student'])
            ->whereHas('room', function ($q) use ($property) { $q->where('property_id', $property->id); })
            ->where('status', 'pending')
            ->orderByDesc('created_at')
            ->get();

        return view('landlord.properties.show', compact('property','activeBookings','pendingBookings'));
    }
}

