<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\Room;
use App\Notifications\SystemNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class PropertyController extends Controller
{
    protected function ensureLandlord()
    {
        if (! Auth::check() || Auth::user()->role !== 'landlord') {
            abort(403, 'Unauthorized');
        }
    }

    protected function ensureAdmin()
    {
        if (! Auth::check() || Auth::user()->role !== 'admin') {
            abort(403, 'Unauthorized');
        }
    }

    public function adminPending()
    {
        $this->ensureAdmin();

        $statusFilter = strtolower((string) request('status', 'pending'));
        if (! in_array($statusFilter, ['pending', 'approved', 'rejected', 'all'], true)) {
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

        // Landlord compliance summary for deep details page
        $complianceService = app(\App\Services\LandlordDocumentStatusService::class);
        $profile = $property->landlord?->landlordProfile;
        $compliance = [
            'business_permit' => $complianceService->resolveDocumentStatus($profile, \App\Services\LandlordDocumentStatusService::DOC_BUSINESS_PERMIT),
            'safety_certificate' => $complianceService->resolveDocumentStatus($profile, \App\Services\LandlordDocumentStatusService::DOC_SAFETY_CERTIFICATE),
        ];
        $compliance['is_compliant'] = ($compliance['business_permit'] === 'approved' && $compliance['safety_certificate'] === 'approved');
        $activeTab = request('tab', 'overview');

        return view('admin.properties.show', compact(
            'property',
            'minPrice',
            'maxPrice',
            'servicesOffered',
            'occupancyRate',
            'compliance',
            'activeTab'
        ));
    }

    public function adminInspect(Property $property)
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

        $landlord = $property->landlord;
        $profile = $landlord?->landlordProfile;

        // Landlord compliance using LandlordDocumentStatusService
        $complianceService = app(\App\Services\LandlordDocumentStatusService::class);
        $bpStatus = $complianceService->resolveDocumentStatus($profile, \App\Services\LandlordDocumentStatusService::DOC_BUSINESS_PERMIT);
        $scStatus = $complianceService->resolveDocumentStatus($profile, \App\Services\LandlordDocumentStatusService::DOC_SAFETY_CERTIFICATE);

        $isCompliant = ($bpStatus === 'approved' && $scStatus === 'approved');

        // Quick check
        $hasImage = ! empty($property->image_path);
        $hasDescription = filled($property->description);
        $hasCoordinates = ! empty($property->latitude) && ! empty($property->longitude);
        $hasRooms = $property->total_rooms > 0;

        // Room pricing
        $priceValues = $property->rooms
            ->pluck('price')
            ->filter(fn ($price) => is_numeric($price) && (float) $price > 0)
            ->map(fn ($price) => (float) $price)
            ->values();

        $minPrice = $priceValues->isNotEmpty() ? $priceValues->min() : null;
        $maxPrice = $priceValues->isNotEmpty() ? $priceValues->max() : null;
        $totalCapacity = (int) $property->rooms->sum('capacity');
        $occupancyRate = (float) ($property->total_rooms > 0
            ? round((($property->occupied_rooms ?? 0) / $property->total_rooms) * 100, 1)
            : 0);

        // Building inclusions / amenities
        $amenityLabelMap = collect((array) config('property_amenities.flat', []))
            ->mapWithKeys(fn ($label, $key) => [strtolower((string) $key) => (string) $label]);

        $buildingServices = collect((array) ($property->building_inclusions ?? []))
            ->map(fn ($item) => trim((string) $item))
            ->filter()
            ->map(function (string $item) use ($amenityLabelMap): string {
                $normalized = strtolower($item);

                return $amenityLabelMap->has($normalized)
                    ? (string) $amenityLabelMap->get($normalized)
                    : ucwords(str_replace(['_', '-'], ' ', $item));
            })->values()->all();

        // Rooms detailed list
        $roomsData = $property->rooms->map(function ($room) {
            $imagePath = $room->image_path ?: optional($room->roomImages->first())->image_path;
            $inclusions = collect(preg_split('/[,\n;]+/', (string) $room->inclusions))
                ->map(fn ($item) => trim($item))
                ->filter()
                ->values()
                ->all();

            return [
                'id' => $room->id,
                'room_number' => $room->room_number,
                'capacity' => (int) $room->capacity,
                'occupied' => (int) ($room->active_bookings_count ?? 0),
                'available' => (int) $room->getAvailableSlots(),
                'price' => (float) $room->price,
                'status' => $room->status ?? 'available',
                'inclusions' => $inclusions,
                'photo_url' => $imagePath ? file_url($imagePath) : null,
            ];
        })->values()->all();

        return response()->json([
            'id' => $property->id,
            'name' => $property->name,
            'approval_status' => $property->approval_status ?? 'pending',
            'created_at_formatted' => $property->created_at?->format('M d, Y') ?? 'N/A',
            'address' => $property->address ?: 'Address not set',
            'latitude' => $property->latitude ? (float) $property->latitude : null,
            'longitude' => $property->longitude ? (float) $property->longitude : null,
            'image_url' => $property->image_path ? file_url($property->image_path) : null,
            'description' => $property->description ?: null,
            'rejection_reason' => $property->rejection_reason ?: null,
            'landlord' => [
                'id' => $landlord?->id,
                'name' => $landlord?->name ?? 'N/A',
                'full_name' => $landlord?->full_name ?: ($landlord?->name ?? 'N/A'),
                'email' => $landlord?->email ?? 'N/A',
                'contact_number' => $landlord?->contact_number,
                'profile_url' => $landlord ? route('admin.users.landlords.show', $landlord->id) : null,
            ],
            'compliance' => [
                'business_permit' => $bpStatus,
                'safety_certificate' => $scStatus,
                'is_complete' => $isCompliant,
                'summary_label' => $isCompliant ? 'Compliant' : 'Documents incomplete',
            ],
            'quick_check' => [
                'image_uploaded' => $hasImage,
                'description_added' => $hasDescription,
                'coordinates_pinned' => $hasCoordinates,
                'rooms_added' => $hasRooms,
                'room_count' => (int) $property->total_rooms,
            ],
            'rooms_summary' => [
                'total_rooms' => (int) $property->total_rooms,
                'occupied_rooms' => (int) ($property->occupied_rooms ?? 0),
                'available_rooms' => (int) ($property->available_rooms ?? 0),
                'total_capacity' => $totalCapacity,
                'occupancy_rate' => $occupancyRate,
                'min_price' => $minPrice,
                'max_price' => $maxPrice,
            ],
            'building_inclusions' => $buildingServices,
            'rooms' => $roomsData,
            'full_details_url' => route('admin.properties.show', $property),
            'approve_url' => route('admin.properties.approve', $property),
            'reject_url' => route('admin.properties.reject', $property),
        ]);
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
                    ['property_id' => $property->id, 'status' => 'approved'],
                    sendEmail: true,
                    actionText: 'View Properties'
                ));
            } catch (\Throwable $e) {
                // ignore notification/email transport errors
            }
        }

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Property \"{$property->name}\" approved and published to students.",
                'approval_status' => 'approved',
                'property_id' => $property->id,
            ]);
        }

        return back()->with('success', 'Property approved and published to students.');
    }

    public function adminReject(Request $request, Property $property)
    {
        $this->ensureAdmin();

        $request->validate([
            'rejection_reason' => 'required|string|max:500',
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
                    [
                        'property_id' => $property->id,
                        'status' => 'rejected',
                        'reason' => $reason,
                    ],
                    sendEmail: true,
                    actionText: 'View Property Listings'
                ));
            } catch (\Throwable $e) {
                // ignore notification/email transport errors
            }
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Property \"{$property->name}\" was rejected.",
                'approval_status' => 'rejected',
                'property_id' => $property->id,
                'rejection_reason' => $property->rejection_reason,
            ]);
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
            ->orderBy('created_at', 'desc')
            ->get();

        return view('landlord.properties.index', compact('properties'));
    }

    public function create(Request $request)
    {
        $this->ensureLandlord();
        $this->authorize('create', Property::class);
        Log::info('Property create route visited', ['user' => Auth::id()]);

        $draftProperty = null;
        if ($request->filled('draft_id')) {
            $draftProperty = Property::where('id', $request->input('draft_id'))
                ->where('landlord_id', Auth::id())
                ->where('approval_status', 'draft')
                ->first();
        } elseif (! $request->boolean('new')) {
            $draftProperty = Property::where('landlord_id', Auth::id())
                ->where('approval_status', 'draft')
                ->latest('updated_at')
                ->first();
        }

        return view('landlord.properties.create', compact('draftProperty'));
    }

    public function autoSave(Request $request)
    {
        $this->ensureLandlord();
        $this->authorize('create', Property::class);

        $customMessages = [
            'image.max' => 'Your photo is too large. Please choose an image under 5 MB.',
            'image.image' => 'Please select a valid image file (JPG, PNG, or WebP).',
            'image.mimes' => 'Please select a valid image file (JPG, PNG, or WebP).',
        ];

        $validator = Validator::make($request->all(), [
            'property_id' => 'nullable|integer',
            'draft_version' => 'nullable|integer',
            'name' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:5000',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:5120',
            'building_inclusions' => 'nullable|array',
            'building_inclusions.*' => 'string',
            'house_rules' => 'nullable|array',
        ], $customMessages);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        $property = null;
        if ($request->filled('property_id')) {
            $propertyId = (int) $request->input('property_id');
            $property = Property::where('id', $propertyId)
                ->where('landlord_id', Auth::id())
                ->first();

            if (! $property) {
                return response()->json([
                    'success' => false,
                    'message' => 'Draft property not found or unauthorized.',
                ], 404);
            }

            if ($property->approval_status !== 'draft') {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot auto-save a property that has already been submitted for review.',
                ], 422);
            }
        } else {
            // Check meaningful input before creating first draft
            $hasName = filled(trim((string) $request->input('name', '')));
            $hasAddress = filled(trim((string) $request->input('address', '')));
            $hasDescription = filled(trim((string) $request->input('description', '')));
            $hasImage = $request->hasFile('image');

            if (! $hasName && ! $hasAddress && ! $hasDescription && ! $hasImage) {
                return response()->json([
                    'success' => true,
                    'saved' => false,
                    'message' => 'No meaningful changes to save yet.',
                ]);
            }
        }

        $supportsBuildingInclusions = Schema::hasColumn('properties', 'building_inclusions');
        $supportsHouseRules = Schema::hasColumn('properties', 'house_rules');
        $allowedAmenities = array_keys((array) config('property_amenities.flat', []));
        $houseRuleCategories = (array) config('property_house_rules.categories', []);

        $selectedAmenities = collect($request->input('building_inclusions', []))
            ->map(fn ($item) => (string) $item)
            ->filter(fn ($item) => in_array($item, $allowedAmenities, true))
            ->unique()
            ->values()
            ->all();

        $houseRulesPayload = [];
        if ($request->has('house_rules') && is_array($request->input('house_rules'))) {
            foreach (array_keys($houseRuleCategories) as $categoryKey) {
                $raw = $request->input('house_rules.'.$categoryKey);
                if (is_array($raw)) {
                    $cleaned = collect($raw)->map(fn ($l) => trim((string) $l))->filter()->values()->all();
                } else {
                    $rawLines = preg_split('/\r\n|\r|\n/', (string) $raw) ?: [];
                    $cleaned = collect($rawLines)->map(fn ($l) => trim((string) $l))->filter()->values()->all();
                }
                if (! empty($cleaned)) {
                    $houseRulesPayload[$categoryKey] = $cleaned;
                }
            }
        }

        $dataToSave = [
            'name' => $request->filled('name') ? trim($request->input('name')) : ($property->name ?? 'Untitled Property'),
            'address' => $request->input('address', $property->address ?? ''),
            'description' => $request->input('description', $property->description ?? null),
            'latitude' => $request->filled('latitude') ? $request->input('latitude') : ($property->latitude ?? null),
            'longitude' => $request->filled('longitude') ? $request->input('longitude') : ($property->longitude ?? null),
        ];

        if ($supportsBuildingInclusions && $request->has('building_inclusions')) {
            $dataToSave['building_inclusions'] = $selectedAmenities;
        }

        if ($supportsHouseRules && $request->has('house_rules')) {
            $dataToSave['house_rules'] = empty($houseRulesPayload) ? null : $houseRulesPayload;
        }

        if ($property) {
            $property->update($dataToSave);
        } else {
            $dataToSave['landlord_id'] = Auth::id();
            $dataToSave['approval_status'] = 'draft';
            $property = Property::create($dataToSave);
        }

        if ($request->hasFile('image')) {
            try {
                $imagePath = app(\App\Services\FileStorageService::class)->upload(
                    $request->file('image'),
                    'properties/'.$property->id.'/images'
                );
                $property->update(['image_path' => $imagePath]);
            } catch (\Throwable $e) {
                Log::warning('Autosave image upload failed: '.$e->getMessage());
            }
        }

        return response()->json([
            'success' => true,
            'saved' => true,
            'property_id' => $property->id,
            'draft_version' => $property->fresh()->updated_at->timestamp,
            'image_url' => $property->image_path ? file_url($property->image_path) : null,
            'image_name' => basename($property->image_path ?? ''),
            'message' => 'All changes saved',
        ]);
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
        $isDraft = in_array($request->input('action'), ['draft', 'save_draft'], true) || $request->boolean('save_draft');
        $customMessages = [
            'image.max' => 'Your photo is too large. Please choose an image under 5 MB.',
            'image.image' => 'Please select a valid image file (JPG, PNG, or WebP).',
            'image.mimes' => 'Please select a valid image file (JPG, PNG, or WebP).',
        ];

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
            // Room fields are optional when saving draft or adding rooms later inside property
            'initial_room_number' => ['nullable', 'string', 'max:50'],
            'initial_capacity' => ['nullable', 'integer', 'min:1'],
            'initial_price' => ['nullable', 'numeric', 'min:0'],
            'initial_status' => ['nullable', 'in:available,occupied,maintenance'],
        ], $customMessages);

        foreach (array_keys($houseRuleCategories) as $categoryKey) {
            $validator->addRules([
                'house_rules.'.$categoryKey => $supportsHouseRules ? 'nullable|string|max:4000' : 'nullable',
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
        $coverImagePending = $request->hasFile('image');

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
        if (! $submittedCustomInclusionPayload) {
            $existingCustomAmenities = collect((array) ($property->building_inclusions ?? []))
                ->map(fn ($item) => trim((string) $item))
                ->filter(fn ($item) => $item !== '' && ! in_array($item, $allowedAmenities, true))
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
            $rawValue = (string) $request->input('house_rules.'.$categoryKey, '');
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

            if (! empty($cleanedLines)) {
                $houseRulesPayload[$categoryKey] = $cleanedLines;
            }
        }

        $existingDraft = null;
        if ($request->filled('property_id')) {
            $existingDraft = Property::where('id', $request->input('property_id'))
                ->where('landlord_id', Auth::id())
                ->where('approval_status', 'draft')
                ->first();
        }

        $propertyData = [
            'landlord_id' => Auth::id(),
            'image_path' => $existingDraft && ! $coverImagePending ? $existingDraft->image_path : $imagePath,
            'approval_status' => $isDraft ? 'draft' : 'pending',
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

        if ($existingDraft) {
            $existingDraft->update($propertyData);
            $property = $existingDraft;
        } else {
            $property = Property::create($propertyData);
        }

        if ($coverImagePending) {
            try {
                $imagePath = app(\App\Services\FileStorageService::class)->upload(
                    $request->file('image'),
                    'properties/'.$property->id.'/images'
                );
                $property->update(['image_path' => $imagePath]);
            } catch (\Throwable $e) {
                // Property stays created without a cover photo; upload failure is already logged.
            }
        }

        // Geocode address only if coordinates not provided
        if (! $request->filled('latitude') || ! $request->filled('longitude')) {
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

        $successMsg = $isDraft
            ? 'Boarding house saved as draft. You can continue setup and add rooms inside.'
            : ('Property created successfully.'.($request->filled('initial_room_number') ? ' First room added.' : ''));
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
        $customMessages = [
            'image.max' => 'Your photo is too large. Please choose an image under 5 MB.',
            'image.image' => 'Please select a valid image file (JPG, PNG, or WebP).',
            'image.mimes' => 'Please select a valid image file (JPG, PNG, or WebP).',
        ];

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
        ], $customMessages);

        foreach (array_keys($houseRuleCategories) as $categoryKey) {
            $validator->addRules([
                'house_rules.'.$categoryKey => $supportsHouseRules ? 'nullable|string|max:4000' : 'nullable',
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
            $rawValue = (string) $request->input('house_rules.'.$categoryKey, '');
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

            if (! empty($cleanedLines)) {
                $houseRulesPayload[$categoryKey] = $cleanedLines;
            }
        }

        if ($request->hasFile('image')) {
            try {
                $newImagePath = app(\App\Services\FileStorageService::class)->replace(
                    $property->image_path,
                    $request->file('image'),
                    'properties/'.$property->id.'/images'
                );
            } catch (\RuntimeException $e) {
                return back()->withInput()->with('error', 'File upload failed. Please try again.');
            } catch (\Symfony\Component\Mime\Exception\LogicException $e) {
                return back()->withInput()->with('error', 'Unable to process the uploaded image. Please ensure the PHP "fileinfo" extension is enabled and try a smaller image if needed (PHP upload limit).');
            }

            $property->image_path = $newImagePath;
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

        $today = now()->toDateString();
        $hasActiveBookings = \App\Models\Booking::whereHas('room', function ($q) use ($property) {
            $q->where('property_id', $property->id);
        })
            ->where(function ($q) use ($today) {
                $q->where(function ($sq) use ($today) {
                    $sq->where('status', 'approved')
                        ->where('check_out', '>', $today);
                })->orWhere('status', 'pending');
            })
            ->exists();

        if ($hasActiveBookings) {
            return back()->with('error', 'Cannot delete a property that has active tenants or pending booking requests.');
        }

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
        ])->load(['rooms' => function ($q) {
            $q->with(['roomImages', 'bookings' => function ($bookingQ) {
                $bookingQ->where('status', 'approved')
                    ->where('check_in', '<=', now()->toDateString())
                    ->where('check_out', '>', now()->toDateString())
                    ->with('student');
            }])
                ->orderBy('room_number');
        }]);

        // Add current tenant info and full active tenants list to each room
        $property->rooms->each(function ($room) {
            $activeStudents = $room->bookings
                ->filter(fn ($b) => $b->status === 'approved' && $b->student !== null)
                ->map(fn ($b) => $b->student)
                ->values();

            $room->active_tenants = $activeStudents;
            $currentBooking = $room->bookings->first();
            $room->current_tenant = $activeStudents->first() ?? ($currentBooking ? $currentBooking->student : null);
            $room->current_booking = $currentBooking;
        });

        $today = now()->toDateString();
        $activeBookings = \App\Models\Booking::with(['room', 'student'])
            ->whereHas('room', function ($q) use ($property) {
                $q->where('property_id', $property->id);
            })
            ->where('status', 'approved')
            ->where('check_in', '<=', $today)
            ->where('check_out', '>', $today)
            ->orderByDesc('created_at')
            ->get();

        $pendingBookings = \App\Models\Booking::with(['room', 'student'])
            ->whereHas('room', function ($q) use ($property) {
                $q->where('property_id', $property->id);
            })
            ->where('status', 'pending')
            ->orderByDesc('created_at')
            ->get();

        return view('landlord.properties.show', compact('property', 'activeBookings', 'pendingBookings'));
    }
}
