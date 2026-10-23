@extends('layouts.landlord')

@section('title', $property->name . ' - Property Workspace')

@section('content')
@php
    $roomsCollection = $property->rooms ?? collect();
    $totalRooms = $roomsCollection->count();
    $totalBedCapacity = (int) $roomsCollection->sum('capacity');

    // Available rooms: status === 'available' and has at least 1 slot available
    $vacantRoomsCount = $roomsCollection->filter(fn($r) => $r->status === 'available' && $r->getAvailableSlots() > 0)->count();
    $availableBedSlots = (int) $roomsCollection->sum(fn($r) => $r->getAvailableSlots());

    // Occupied rooms & slots
    $occupiedRoomsCount = $roomsCollection->filter(fn($r) => (isset($r->active_tenants) && $r->active_tenants->isNotEmpty()) || $r->status === 'occupied')->count();
    $occupiedBedSlots = max(0, $totalBedCapacity - $availableBedSlots);

    // Maintenance rooms
    $maintenanceRoomsCount = $roomsCollection->where('status', 'maintenance')->count();

    // Canonical approval status
    $approvalStatus = strtolower((string) ($property->approval_status ?? $property->status ?? 'pending'));

    // Building inclusions
    $amenityLabels = (array) config('property_amenities.flat', []);
    $buildingInclusions = collect((array) ($property->building_inclusions ?? []))
        ->map(fn ($key) => $amenityLabels[$key] ?? trim((string) $key))
        ->filter()
        ->values();

    // House rules
    $rawRules = $property->house_rules;
    $groupedRules = [];
    $ruleCategoryMeta = (array) config('property_house_rules.categories', []);
    if (is_array($rawRules) || is_object($rawRules)) {
        foreach ($rawRules as $catKey => $val) {
            $catLabel = $ruleCategoryMeta[$catKey]['label'] ?? ucwords(str_replace('_', ' ', (string) $catKey));
            $rulesList = is_array($val) ? $val : preg_split('/\r\n|\r|\n/', (string) $val);
            $cleanList = collect($rulesList)->map(fn($r) => trim((string)$r))->filter()->values()->all();
            if (!empty($cleanList)) {
                $groupedRules[$catLabel] = $cleanList;
            }
        }
    }

    // Coordinates check
    $lat = $property->latitude ? (float) $property->latitude : null;
    $lng = $property->longitude ? (float) $property->longitude : null;
    $hasCoordinates = !empty($lat) && !empty($lng) && $lat != 0.0 && $lng != 0.0;
@endphp

<div class="landlord-page-shell">
    {{-- Breadcrumb Navigation --}}
    <div class="mb-3">
        <a href="{{ route('landlord.properties.index') }}" class="property-back-link">
            <i class="bi bi-arrow-left me-1"></i> Properties
        </a>
    </div>

    {{-- System / Flash Alerts --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-3 mb-3 py-2 px-3 small" role="alert">
            <i class="bi bi-check-circle me-1"></i> {{ session('success') }}
            <button type="button" class="btn-close py-2" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('info'))
        <div class="alert alert-info alert-dismissible fade show rounded-3 mb-3 py-2 px-3 small" role="alert">
            <i class="bi bi-info-circle me-1"></i> {{ session('info') }}
            <button type="button" class="btn-close py-2" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show rounded-3 mb-3 py-2 px-3 small" role="alert">
            <i class="bi bi-exclamation-triangle me-1"></i> {{ session('error') }}
            <button type="button" class="btn-close py-2" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- 1. Property Header --}}
    <header class="property-header-card p-3 p-md-4 rounded-4 mb-3">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
            <div class="d-flex align-items-start gap-3 min-w-0 w-100">
                {{-- Restrained Cover Photo / Fallback --}}
                <div class="property-cover-wrap flex-shrink-0">
                    @if(!empty($property->image_path))
                        <img src="{{ file_url($property->image_path) }}" alt="{{ $property->name }}" class="property-cover-img rounded-3 border">
                    @else
                        <div class="property-cover-fallback rounded-3 border d-flex align-items-center justify-content-center text-muted">
                            <i class="bi bi-houses fs-3 text-secondary"></i>
                        </div>
                    @endif
                </div>

                {{-- Property Title, Address & Canonical Status --}}
                <div class="min-w-0 flex-grow-1">
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                        <h1 class="h4 mb-0 fw-bold text-dark text-break">{{ $property->name }}</h1>
                        @if($approvalStatus === 'approved')
                            <span class="badge rounded-pill bg-success-subtle text-success border border-success-subtle small">
                                <i class="bi bi-check-circle me-1"></i>Approved
                            </span>
                        @elseif($approvalStatus === 'draft')
                            <span class="badge rounded-pill bg-secondary-subtle text-secondary border border-secondary-subtle small">
                                <i class="bi bi-pencil me-1"></i>Draft
                            </span>
                        @elseif($approvalStatus === 'rejected')
                            <span class="badge rounded-pill bg-danger-subtle text-danger border border-danger-subtle small">
                                <i class="bi bi-x-circle me-1"></i>Changes Requested
                            </span>
                        @else
                            <span class="badge rounded-pill bg-warning-subtle text-warning-emphasis border border-warning-subtle small">
                                <i class="bi bi-clock-history me-1"></i>Under Admin Review
                            </span>
                        @endif
                    </div>
                    <div class="text-muted small d-flex align-items-center gap-1">
                        <i class="bi bi-geo-alt flex-shrink-0"></i>
                        <span class="text-truncate">{{ $property->address ?: 'Address not specified' }}</span>
                    </div>

                    @if($approvalStatus === 'rejected' && filled($property->rejection_reason))
                        <div class="alert alert-danger py-2 px-3 rounded-3 small mt-2 mb-0">
                            <strong>Admin Feedback:</strong> {{ $property->rejection_reason }}
                        </div>
                    @endif
                </div>
            </div>

            {{-- Header Actions --}}
            <div class="d-flex align-items-center gap-2 flex-wrap flex-shrink-0 w-100 w-md-auto justify-content-start justify-content-md-end">
                @if($approvalStatus === 'draft')
                    <a href="{{ route('landlord.properties.edit', ['property' => $property->id, 'panel' => 3]) }}" class="btn btn-brand rounded-pill px-3 py-2 fw-semibold">
                        <i class="bi bi-send-check me-1"></i> Review &amp; Submit
                    </a>
                    <a href="{{ route('landlord.properties.edit', $property->id) }}" class="btn btn-outline-secondary rounded-pill px-3 py-2">
                        <i class="bi bi-pencil me-1"></i> Edit Property
                    </a>
                    <a href="{{ route('landlord.properties.rooms.create', $property->id) }}" class="btn btn-outline-secondary rounded-pill px-3 py-2">
                        <i class="bi bi-plus-lg me-1"></i> Add Room
                    </a>
                @elseif($approvalStatus === 'pending')
                    <span class="badge rounded-pill bg-warning-subtle text-warning-emphasis border border-warning-subtle px-3 py-2 small fw-semibold">
                        <i class="bi bi-clock-history me-1"></i> Waiting for Admin review
                    </span>
                    <a href="{{ route('landlord.properties.rooms.create', $property->id) }}" class="btn btn-brand rounded-pill px-3 py-2">
                        <i class="bi bi-plus-lg me-1"></i> Add Room
                    </a>
                    <a href="{{ route('landlord.properties.edit', $property->id) }}" class="btn btn-outline-secondary rounded-pill px-3 py-2">
                        <i class="bi bi-pencil me-1"></i> Edit Property
                    </a>
                @else
                    <a href="{{ route('landlord.properties.rooms.create', $property->id) }}" class="btn btn-brand rounded-pill px-3 py-2">
                        <i class="bi bi-plus-lg me-1"></i> Add Room
                    </a>
                    <a href="{{ route('landlord.properties.edit', $property->id) }}" class="btn btn-outline-secondary rounded-pill px-3 py-2">
                        <i class="bi bi-pencil me-1"></i> Edit Property
                    </a>
                @endif
            </div>
        </div>
    </header>

    {{-- Contextual Review State Banners --}}
    @if($approvalStatus === 'draft')
        <div class="alert alert-warning border-0 rounded-4 p-3 mb-3 d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 shadow-sm">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-info-circle-fill text-warning fs-5 flex-shrink-0"></i>
                <span class="small text-dark">Your property is saved but hasn't been submitted for approval.</span>
            </div>
            <div class="d-flex align-items-center gap-2 flex-shrink-0">
                <a href="{{ route('landlord.properties.edit', ['property' => $property->id, 'panel' => 3]) }}" class="btn btn-sm btn-brand rounded-pill px-3 py-1 fw-semibold">
                    Review &amp; Submit
                </a>
            </div>
        </div>
    @elseif($approvalStatus === 'pending')
        <div class="alert alert-info border-0 rounded-4 p-3 mb-3 d-flex align-items-center gap-2 shadow-sm">
            <i class="bi bi-hourglass-split text-primary fs-5 flex-shrink-0"></i>
            <span class="small text-dark">Waiting for Admin review. Your property submission is queued for administrative review.</span>
        </div>
    @endif

    {{-- 2. Compact Room Availability Summary Strip --}}
    <div class="property-summary-bar rounded-pill px-3 py-2 mb-4 d-inline-flex flex-wrap align-items-center gap-2">
        <span class="summary-dot bg-success rounded-circle"></span>
        <span class="fw-semibold text-dark">{{ $totalRooms }} {{ Str::plural('room', $totalRooms) }}</span>
        @if($totalBedCapacity > $totalRooms)
            <span class="text-muted small">({{ $totalBedCapacity }} bed slots)</span>
        @endif
        <span class="text-muted">·</span>
        <span class="text-success fw-medium">
            {{ $vacantRoomsCount }} available
            @if($totalBedCapacity > $totalRooms)
                <span class="text-muted fw-normal">({{ $availableBedSlots }} {{ Str::plural('bed', $availableBedSlots) }} open)</span>
            @endif
        </span>
        <span class="text-muted">·</span>
        <span class="text-secondary fw-medium">
            {{ $occupiedRoomsCount }} occupied
            @if($totalBedCapacity > $totalRooms && $occupiedBedSlots > 0)
                <span class="text-muted fw-normal">({{ $occupiedBedSlots }} {{ Str::plural('bed', $occupiedBedSlots) }} filled)</span>
            @endif
        </span>
        @if($maintenanceRoomsCount > 0)
            <span class="text-muted">·</span>
            <span class="text-warning-emphasis fw-medium">{{ $maintenanceRoomsCount }} maintenance</span>
        @endif
    </div>

    {{-- 3. Rooms — Primary Management Content --}}
    <section class="mb-4" aria-labelledby="rooms-heading">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h2 id="rooms-heading" class="h5 fw-bold mb-0 text-dark">Rooms</h2>
                <div class="text-muted small">Manage room pricing, bed capacity, and current tenants.</div>
            </div>
            @if($totalRooms > 0)
                <span class="badge bg-light text-dark border rounded-pill px-3 py-2">
                    {{ $totalRooms }} {{ Str::plural('Room', $totalRooms) }}
                </span>
            @endif
        </div>

        @if($roomsCollection->isEmpty())
            {{-- Clean Empty Room State --}}
            <div class="empty-room-card rounded-4 text-center p-4 p-md-5 mb-4 bg-white border">
                <div class="empty-room-icon mb-3">
                    <i class="bi bi-door-open fs-1 text-muted"></i>
                </div>
                <h3 class="h5 fw-bold text-dark mb-1">No rooms yet</h3>
                <p class="text-muted small mb-3 mx-auto" style="max-width: 440px;">
                    Add your first room to prepare your boarding house for bookings and manage student room occupancy.
                </p>
                <a href="{{ route('landlord.properties.rooms.create', $property->id) }}" class="btn btn-brand rounded-pill px-4 py-2">
                    <i class="bi bi-plus-lg me-1"></i> Add Room
                </a>
            </div>
        @else
            {{-- Room Records List --}}
            <div class="vstack gap-3">
                @foreach($roomsCollection as $room)
                    @php
                        $slotsAvailable = (int) $room->getAvailableSlots();
                        $totalCapacity = (int) ($room->capacity ?: 1);
                        $roomPhoto = $room->image_path ?: optional($room->roomImages->first())->image_path;
                        $pricingModel = method_exists($room, 'resolvePricingModel') ? $room->resolvePricingModel() : 'per_room';

                        $pricingUnit = match($pricingModel) {
                            'per_bed' => '/ bed / mo',
                            'per_room' => '/ room / mo',
                            default => '/ mo',
                        };

                        $statusBadgeClass = match($room->status) {
                            'available' => 'bg-success-subtle text-success border-success-subtle',
                            'occupied' => 'bg-secondary-subtle text-secondary border-secondary-subtle',
                            'maintenance' => 'bg-warning-subtle text-warning-emphasis border-warning-subtle',
                            default => 'bg-light text-dark'
                        };
                    @endphp

                    <article class="room-management-card rounded-4 p-3 p-md-4 border bg-white">
                        <div class="d-flex flex-column flex-sm-row align-items-start gap-3">
                            {{-- Room Photo Thumbnail --}}
                            <div class="room-thumbnail-wrap flex-shrink-0">
                                @if(!empty($roomPhoto))
                                    <img src="{{ file_url($roomPhoto) }}" alt="Room {{ $room->room_number }}" class="room-thumbnail rounded-3 border">
                                @else
                                    <div class="room-thumbnail-fallback rounded-3 border d-flex align-items-center justify-content-center text-muted bg-light">
                                        <i class="bi bi-door-closed fs-3 text-secondary"></i>
                                    </div>
                                @endif
                            </div>

                            {{-- Room Details & Management Metadata --}}
                            <div class="min-w-0 flex-grow-1 w-100">
                                <div class="d-flex flex-column flex-md-row justify-content-between align-items-start gap-2 mb-2">
                                    <div>
                                        <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                            <h3 class="h5 mb-0 fw-bold text-dark">Room {{ $room->room_number }}</h3>
                                            <span class="badge rounded-pill border {{ $statusBadgeClass }} small">
                                                {{ ucfirst($room->status) }}
                                            </span>
                                        </div>
                                        <div class="d-flex flex-wrap align-items-center gap-2 small">
                                            <span class="item-chip">
                                                Rate: <strong>₱{{ number_format($room->price, 2) }} {{ $pricingUnit }}</strong>
                                            </span>
                                            <span class="item-chip">
                                                Capacity: <strong>{{ $totalCapacity }} {{ Str::plural('bed', $totalCapacity) }}</strong>
                                            </span>
                                            <span class="item-chip">
                                                Slots: <strong>{{ $slotsAvailable }}/{{ $totalCapacity }}</strong>
                                            </span>
                                        </div>
                                    </div>

                                    {{-- Room Actions: Edit, Duplicate, Delete --}}
                                    <div class="d-flex flex-wrap align-items-center gap-2 mt-2 mt-md-0">
                                        <a href="{{ route('landlord.properties.rooms.edit', [$property->id, $room->id]) }}" class="btn btn-sm btn-outline-brand rounded-pill px-3">
                                            <i class="bi bi-pencil me-1"></i> Edit
                                        </a>
                                        <form action="{{ route('landlord.properties.rooms.duplicate', [$property->id, $room->id]) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-secondary rounded-pill px-3" title="Duplicate Room Configuration">
                                                <i class="bi bi-copy me-1"></i> Duplicate
                                            </button>
                                        </form>
                                        <form action="{{ route('landlord.properties.rooms.destroy', [$property->id, $room->id]) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this room?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-2" title="Delete Room">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>

                                {{-- Tenant Display (strictly preserves test assertions) --}}
                                <div class="room-tenant-row pt-2 mt-2 border-top small">
                                    @if(isset($room->active_tenants) && $room->active_tenants->count() > 1)
                                        <span class="text-muted me-1">Tenants ({{ $room->active_tenants->count() }}):</span>
                                        <span class="fw-semibold text-dark">{{ $room->active_tenants->pluck('full_name')->filter()->join(', ') }}</span>
                                    @elseif($room->current_tenant)
                                        <span class="text-muted me-1">Tenant:</span>
                                        <span class="fw-semibold text-dark">{{ $room->current_tenant->full_name }}</span>
                                        <span class="text-muted">(ID hidden)</span>
                                    @else
                                        <span class="text-muted me-1">Tenant:</span>
                                        <span class="text-muted">No tenant</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </section>

    {{-- 4. Amenities & House Rules --}}
    <section class="row g-3 mb-4" aria-labelledby="amenities-heading">
        {{-- Building Inclusions --}}
        <div class="col-12 col-lg-6">
            <div class="card shadow-xs border rounded-4 h-100 bg-white">
                <div class="card-header bg-white border-0 pt-3 pb-2 px-3 px-md-4 d-flex justify-content-between align-items-center">
                    <strong id="amenities-heading" class="text-dark small text-uppercase fw-bold letter-spacing-1">
                        <i class="bi bi-stars me-1 text-brand"></i> Building Amenities
                    </strong>
                    <span class="badge bg-light text-muted border">{{ $buildingInclusions->count() }}</span>
                </div>
                <div class="card-body px-3 px-md-4 pt-1 pb-3">
                    @if($buildingInclusions->isNotEmpty())
                        <div class="d-flex flex-wrap gap-2">
                            @foreach($buildingInclusions as $inclusion)
                                <span class="item-chip">{{ $inclusion }}</span>
                            @endforeach
                        </div>
                    @else
                        <div class="text-muted small">No building amenities added yet.</div>
                    @endif
                </div>
            </div>
        </div>

        {{-- House Rules --}}
        <div class="col-12 col-lg-6">
            <div class="card shadow-xs border rounded-4 h-100 bg-white">
                <div class="card-header bg-white border-0 pt-3 pb-2 px-3 px-md-4 d-flex justify-content-between align-items-center">
                    <strong class="text-dark small text-uppercase fw-bold letter-spacing-1">
                        <i class="bi bi-shield-check me-1 text-brand"></i> House Rules
                    </strong>
                    <span class="badge bg-light text-muted border">{{ count($groupedRules) }} {{ Str::plural('category', count($groupedRules)) }}</span>
                </div>
                <div class="card-body px-3 px-md-4 pt-1 pb-3">
                    @if(!empty($groupedRules))
                        <div class="vstack gap-2">
                            @foreach($groupedRules as $catLabel => $rules)
                                <div>
                                    <div class="fw-semibold text-dark small mb-1">{{ $catLabel }}</div>
                                    <ul class="mb-0 ps-3 text-muted small">
                                        @foreach($rules as $ruleText)
                                            <li>{{ $ruleText }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-muted small">No house rules specified yet.</div>
                    @endif
                </div>
            </div>
        </div>
    </section>

    {{-- 5. Property Location (Leaflet Map) --}}
    <section class="card shadow-xs border rounded-4 mb-4 bg-white overflow-hidden" aria-labelledby="location-heading">
        <div class="card-header bg-white border-0 pt-3 pb-2 px-3 px-md-4 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <strong id="location-heading" class="text-dark small text-uppercase fw-bold letter-spacing-1">
                    <i class="bi bi-geo-alt me-1 text-brand"></i> Property Location
                </strong>
                <div class="text-muted small">{{ $property->address ?: 'Address not set' }}</div>
            </div>
            @if($hasCoordinates)
                <a href="https://www.google.com/maps?q={{ $lat }},{{ $lng }}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                    <i class="bi bi-box-arrow-up-right me-1"></i> Open in Maps
                </a>
            @endif
        </div>
        <div class="card-body p-3 px-md-4 pt-0">
            @if($hasCoordinates)
                <div id="propertyMap" class="property-map-container rounded-3 border"></div>
                <div class="d-flex justify-content-between align-items-center mt-2 small text-muted">
                    <span>Coordinates: {{ number_format($lat, 6) }}, {{ number_format($lng, 6) }}</span>
                    <span>100m proximity indicator</span>
                </div>
            @else
                <div class="text-center py-4 px-3 border rounded-3 bg-light">
                    <i class="bi bi-geo fs-2 text-muted mb-2 d-block"></i>
                    <div class="fw-semibold text-dark small mb-1">Location not pinned yet.</div>
                    <div class="text-muted small mb-2">GPS coordinates have not been set for this boarding house. Pin the location in property settings to display the interactive map.</div>
                    <a href="{{ route('landlord.properties.edit', $property->id) }}" class="btn btn-sm btn-outline-brand rounded-pill px-3">
                        <i class="bi bi-geo-alt me-1"></i> Pin Location
                    </a>
                </div>
            @endif
        </div>
    </section>

    {{-- 6. Booking Requests (Active & Pending) --}}
    <section class="card shadow-xs border rounded-4 mb-4 bg-white" aria-labelledby="requests-heading">
        <div class="card-header bg-white border-0 pt-3 pb-2 px-3 px-md-4 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <strong id="requests-heading" class="text-dark small text-uppercase fw-bold letter-spacing-1">
                    <i class="bi bi-calendar2-check me-1 text-brand"></i> Booking Requests for this Property
                </strong>
                <div class="text-muted small">Active stays today and pending student applicant reviews.</div>
            </div>
            <a href="{{ route('landlord.bookings.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                View All Requests
            </a>
        </div>
        <div class="card-body px-3 px-md-4 pt-1 pb-3">
            <div class="row g-3">
                {{-- Active Today --}}
                <div class="col-12 col-md-6">
                    <div class="p-3 rounded-3 border bg-light h-100">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="fw-bold small text-dark">Active Today</span>
                            <span class="badge bg-white text-dark border">{{ $activeBookings->count() }}</span>
                        </div>
                        @forelse($activeBookings as $b)
                            <div class="bg-white p-2 rounded-2 border mb-2 small">
                                <div class="fw-semibold text-dark">{{ $b->room->room_number ?? 'Room' }}</div>
                                <div class="text-muted">Student: <span class="text-dark">{{ $b->student->full_name ?? 'N/A' }}</span></div>
                                <div class="text-muted text-truncate">{{ $b->check_in?->format('M d, Y') }} &rarr; {{ $b->check_out?->format('M d, Y') }}</div>
                            </div>
                        @empty
                            <div class="text-muted small py-2">No active boarding requests today.</div>
                        @endforelse
                    </div>
                </div>

                {{-- Pending Requests --}}
                <div class="col-12 col-md-6">
                    <div class="p-3 rounded-3 border bg-light h-100">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="fw-bold small text-dark">Pending Requests</span>
                            <span class="badge bg-white text-dark border">{{ $pendingBookings->count() }}</span>
                        </div>
                        @forelse($pendingBookings as $b)
                            <div class="bg-white p-2 rounded-2 border mb-2 small">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="fw-semibold text-dark">{{ $b->room->room_number ?? 'Room' }}</span>
                                    <span class="text-muted">{{ $b->check_in?->format('M d') }} - {{ $b->check_out?->format('M d, Y') }}</span>
                                </div>
                                <div class="text-muted mb-2">Student: <span class="text-dark">{{ $b->student->full_name ?? 'N/A' }}</span></div>
                                <div class="d-flex gap-2">
                                    <form action="{{ route('landlord.bookings.approve', $b->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success rounded-pill px-3 py-1">Approve</button>
                                    </form>
                                    <form action="{{ route('landlord.bookings.reject', $b->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-3 py-1">Reject</button>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <div class="text-muted small py-2">No pending requests.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

@push('styles')
@if($hasCoordinates)
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin=""/>
@endif
<style>
    .property-back-link {
        color: #64748b;
        font-size: .875rem;
        font-weight: 500;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        transition: color .15s ease;
    }
    .property-back-link:hover {
        color: var(--brand, #14532d);
    }
    .property-header-card {
        background: #ffffff;
        border: 1px solid rgba(2, 8, 20, .08);
        box-shadow: 0 4px 16px rgba(2, 8, 20, .04);
    }
    .property-cover-wrap {
        width: 72px;
        height: 72px;
    }
    .property-cover-img,
    .property-cover-fallback {
        width: 72px;
        height: 72px;
        object-fit: cover;
    }
    .property-summary-bar {
        background: #ffffff;
        border: 1px solid rgba(2, 8, 20, .08);
        box-shadow: 0 2px 8px rgba(2, 8, 20, .03);
        font-size: .875rem;
    }
    .summary-dot {
        width: 8px;
        height: 8px;
        display: inline-block;
    }
    .room-management-card {
        background: #ffffff;
        border: 1px solid rgba(2, 8, 20, .08) !important;
        box-shadow: 0 2px 8px rgba(2, 8, 20, .03);
        transition: box-shadow .2s ease, border-color .2s ease;
    }
    .room-management-card:hover {
        border-color: rgba(20, 83, 45, .25) !important;
        box-shadow: 0 6px 18px rgba(2, 8, 20, .06);
    }
    .room-thumbnail-wrap {
        width: 100px;
        height: 75px;
    }
    .room-thumbnail,
    .room-thumbnail-fallback {
        width: 100px;
        height: 75px;
        object-fit: cover;
    }
    .item-chip {
        display: inline-flex;
        align-items: center;
        gap: .25rem;
        border: 1px solid rgba(2, 8, 20, .10);
        background: #f8fafc;
        border-radius: 999px;
        padding: .2rem .6rem;
        font-size: .78rem;
        color: #334155;
    }
    .property-map-container {
        height: 260px;
        min-height: 240px;
        width: 100%;
        display: block;
        background: #f1f5f9;
        z-index: 1;
    }
    .empty-room-card {
        border: 1px dashed rgba(2, 8, 20, .15) !important;
    }
    .letter-spacing-1 {
        letter-spacing: .04em;
    }
    .shadow-xs {
        box-shadow: 0 2px 6px rgba(2, 8, 20, .03);
    }

    @media (max-width: 575.98px) {
        .property-cover-wrap {
            width: 56px;
            height: 56px;
        }
        .property-cover-img,
        .property-cover-fallback {
            width: 56px;
            height: 56px;
        }
        .room-thumbnail-wrap {
            width: 100%;
            height: 140px;
        }
        .room-thumbnail,
        .room-thumbnail-fallback {
            width: 100%;
            height: 140px;
        }
    }
</style>
@endpush

@push('scripts')
@if($hasCoordinates)
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const mapEl = document.getElementById('propertyMap');
    if (!mapEl) return;

    const propertyLatLng = [{{ $lat }}, {{ $lng }}];
    const map = L.map('propertyMap', {
        scrollWheelZoom: false
    }).setView(propertyLatLng, 16);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxNativeZoom: 19,
        maxZoom: 22,
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    const marker = L.marker(propertyLatLng).addTo(map);
    marker.bindPopup(`<strong>{{ addslashes($property->name) }}</strong><br>{{ addslashes($property->address) }}`);

    L.circle(propertyLatLng, {
        color: '#14532d',
        fillColor: '#14532d',
        fillOpacity: 0.08,
        radius: 100
    }).addTo(map);

    // Guaranteed tile rendering fix for Leaflet
    setTimeout(() => {
        map.invalidateSize();
    }, 200);

    window.addEventListener('resize', () => {
        map.invalidateSize();
    });
});
</script>
@endif
@endpush
