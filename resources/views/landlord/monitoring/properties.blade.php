@extends('layouts.landlord')

@section('title', 'Property & Room Status')

@section('content')
<div class="landlord-page-shell">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
        <div>
            <div class="landlord-header-eyebrow">Monitoring Hub</div>
            <h1 class="landlord-header-title mb-1">Property &amp; Room Status</h1>
            <div class="landlord-header-subtitle">Read-only operational status across your boarding house portfolio and room occupancy.</div>
        </div>
        @if($totalProperties > 0)
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <span class="landlord-summary-strip">
                    <span class="landlord-summary-dot"></span>
                    <span><strong>{{ $occupancyRate }}%</strong> Occupancy</span>
                    <span class="text-muted">·</span>
                    <span class="text-success fw-bold">{{ $totalAvailableSlots }}</span> slots available
                    @if($maintenanceRooms > 0)
                        <span class="text-muted">·</span>
                        <span class="text-warning-emphasis fw-bold">{{ $maintenanceRooms }}</span> maintenance
                    @endif
                    <span class="text-muted">·</span>
                    <span class="text-muted">{{ $totalRooms }} rooms</span>
                </span>
            </div>
        @endif
    </div>

    <!-- Property Breakdown -->
    <div class="d-flex flex-column gap-3">
        @forelse($properties as $property)
            @php
                $propOccupancy = $property->total_rooms > 0
                    ? round(($property->occupied_rooms / $property->total_rooms) * 100, 1)
                    : 0;
            @endphp
            <div class="landlord-card">
                <div class="card-header bg-white border-bottom p-3 p-md-4 d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-3 d-flex align-items-center justify-content-center bg-light text-brand" style="width: 44px; height: 44px;">
                            <i class="bi bi-buildings fs-5"></i>
                        </div>
                        <div>
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <h2 class="h5 mb-0 fw-bold text-dark">{{ $property->name }}</h2>
                                @if(($property->approval_status ?? $property->status) === 'approved')
                                    <span class="badge rounded-pill bg-success-subtle text-success border border-success-subtle">Approved</span>
                                @elseif(($property->approval_status ?? $property->status) === 'draft')
                                    <span class="badge rounded-pill bg-secondary-subtle text-secondary border border-secondary-subtle">Draft</span>
                                @elseif(($property->approval_status ?? $property->status) === 'rejected')
                                    <span class="badge rounded-pill bg-danger-subtle text-danger border border-danger-subtle">Rejected</span>
                                @else
                                    <span class="badge rounded-pill bg-warning-subtle text-warning border border-warning-subtle">Pending Review</span>
                                @endif
                            </div>
                            <div class="text-muted small mt-1">
                                <i class="bi bi-geo-alt me-1"></i> {{ $property->address }}
                            </div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <span class="badge bg-light text-dark border px-2 py-1">
                            <i class="bi bi-door-open me-1"></i> {{ $property->rooms->count() }} Rooms
                        </span>
                        <span class="badge bg-light text-dark border px-2 py-1">
                            <i class="bi bi-bar-chart me-1"></i> {{ $propOccupancy }}% Occupied
                        </span>
                        <a href="{{ route('landlord.properties.show', $property->id) }}" class="btn btn-sm btn-outline-brand rounded-pill px-3">
                            Manage Property <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>

                <div class="card-body p-0">
                    @if($property->rooms->isEmpty())
                        <div class="p-4 text-center text-muted">
                            <i class="bi bi-door-closed fs-3 d-block mb-2 text-muted"></i>
                            No rooms registered for this property yet.
                            <div class="mt-2">
                                <a href="{{ route('landlord.properties.rooms.create', $property->id) }}" class="btn btn-sm btn-outline-brand rounded-pill">
                                    <i class="bi bi-plus-circle me-1"></i> Add Room
                                </a>
                            </div>
                        </div>
                    @else
                        <!-- Desktop Table View -->
                        <div class="d-none d-md-block table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light small text-muted">
                                    <tr>
                                        <th class="ps-4">Room #</th>
                                        <th>Capacity &amp; Slots</th>
                                        <th>Monthly Rate</th>
                                        <th>Status</th>
                                        <th>Current Occupants</th>
                                        <th class="text-end pe-4">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($property->rooms as $room)
                                        @php
                                            $roomStatusClass = match($room->status) {
                                                'available' => 'bg-success-subtle text-success border-success-subtle',
                                                'occupied' => 'bg-primary-subtle text-primary border-primary-subtle',
                                                'maintenance' => 'bg-warning-subtle text-warning border-warning-subtle',
                                                default => 'bg-secondary-subtle text-secondary border-secondary-subtle'
                                            };
                                        @endphp
                                        <tr>
                                            <td class="ps-4 fw-semibold text-dark">
                                                <i class="bi bi-door-closed me-1 text-muted"></i>
                                                {{ $room->room_number }}
                                            </td>
                                            <td>
                                                <div class="small fw-semibold">{{ $room->slots_available }} / {{ $room->capacity }} slots available</div>
                                                <div class="progress progress-sm mt-1" style="height: 5px; width: 100px;">
                                                    @php
                                                        $usedSlots = max(0, $room->capacity - $room->slots_available);
                                                        $slotPercent = $room->capacity > 0 ? ($usedSlots / $room->capacity) * 100 : 0;
                                                    @endphp
                                                    <div class="progress-bar bg-brand" role="progressbar" style="width: {{ $slotPercent }}%"></div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="fw-semibold text-dark">₱{{ number_format($room->price, 2) }}</div>
                                                <div class="small text-muted">/ month</div>
                                            </td>
                                            <td>
                                                <span class="badge rounded-pill border {{ $roomStatusClass }}">
                                                    {{ ucfirst($room->status) }}
                                                </span>
                                            </td>
                                            <td>
                                                @if(isset($room->active_tenants) && $room->active_tenants->isNotEmpty())
                                                    <div class="d-flex flex-wrap gap-1">
                                                        @foreach($room->active_tenants as $tenant)
                                                            <span class="badge bg-light text-dark border rounded-pill px-2 py-1 small" title="{{ $tenant->full_name }}">
                                                                <i class="bi bi-person me-1"></i> {{ $tenant->full_name }}
                                                            </span>
                                                        @endforeach
                                                    </div>
                                                @elseif($room->current_tenant)
                                                    <span class="badge bg-light text-dark border rounded-pill px-2 py-1 small">
                                                        <i class="bi bi-person me-1"></i> {{ $room->current_tenant->full_name }}
                                                    </span>
                                                @else
                                                    <span class="text-muted small">None</span>
                                                @endif
                                            </td>
                                            <td class="text-end pe-4">
                                                <a href="{{ route('landlord.properties.rooms.edit', [$property->id, $room->id]) }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                                                    Manage
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Mobile Card/List View -->
                        <div class="d-md-none divide-y">
                            @foreach($property->rooms as $room)
                                @php
                                    $roomStatusClass = match($room->status) {
                                        'available' => 'bg-success-subtle text-success border-success-subtle',
                                        'occupied' => 'bg-primary-subtle text-primary border-primary-subtle',
                                        'maintenance' => 'bg-warning-subtle text-warning border-warning-subtle',
                                        default => 'bg-secondary-subtle text-secondary border-secondary-subtle'
                                    };
                                    $usedSlots = max(0, $room->capacity - $room->slots_available);
                                @endphp
                                <div class="p-3 border-bottom">
                                    <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                        <div>
                                            <div class="fw-bold text-dark">
                                                <i class="bi bi-door-closed me-1 text-muted"></i>
                                                Room {{ $room->room_number }}
                                            </div>
                                            <div class="small text-muted">₱{{ number_format($room->price, 2) }} / month</div>
                                        </div>
                                        <span class="badge rounded-pill border {{ $roomStatusClass }}">
                                            {{ ucfirst($room->status) }}
                                        </span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center gap-2 mb-2">
                                        <span class="small text-muted">
                                            {{ $room->slots_available }} of {{ $room->capacity }} slots available ({{ $usedSlots }} occupied)
                                        </span>
                                    </div>
                                    @if(isset($room->active_tenants) && $room->active_tenants->isNotEmpty())
                                        <div class="d-flex flex-wrap gap-1 mb-2">
                                            @foreach($room->active_tenants as $tenant)
                                                <span class="badge bg-light text-dark border rounded-pill px-2 py-1 small">
                                                    <i class="bi bi-person me-1"></i> {{ $tenant->full_name }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @elseif($room->current_tenant)
                                        <div class="mb-2">
                                            <span class="badge bg-light text-dark border rounded-pill px-2 py-1 small">
                                                <i class="bi bi-person me-1"></i> {{ $room->current_tenant->full_name }}
                                            </span>
                                        </div>
                                    @endif
                                    <div class="text-end pt-1">
                                        <a href="{{ route('landlord.properties.rooms.edit', [$property->id, $room->id]) }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                                            Manage Room
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <div class="landlord-empty-state">
                <div class="landlord-empty-icon">
                    <i class="bi bi-buildings"></i>
                </div>
                <div class="landlord-empty-title">No Properties Found</div>
                <div class="landlord-empty-text mb-3">You haven't registered any boarding house properties yet. Add a property to start monitoring occupancy.</div>
                <a href="{{ route('landlord.properties.create') }}" class="btn btn-brand rounded-pill px-4">
                    <i class="bi bi-plus-circle me-1"></i> Add Your First Property
                </a>
            </div>
        @endforelse
    </div>
</div>
@endsection
