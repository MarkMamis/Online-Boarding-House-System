@extends('layouts.landlord')

@section('title', 'Rooms - ' . $property->name)

@section('content')
@php
    $roomCollection = $rooms instanceof \Illuminate\Contracts\Pagination\Paginator
        ? collect($rooms->items())
        : collect($rooms);

    $availableCount = $roomCollection->where('status', 'available')->count();
    $occupiedCount = $roomCollection->where('status', 'occupied')->count();
    $maintenanceCount = $roomCollection->where('status', 'maintenance')->count();
@endphp

<div class="landlord-page-shell">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
        <div>
            <div class="landlord-header-eyebrow">Property Inventory</div>
            <h1 class="landlord-header-title mb-1">{{ $property->name }}</h1>
            <div class="landlord-header-subtitle"><i class="bi bi-geo-alt me-1"></i>{{ $property->address }}</div>
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            @if($roomCollection->isNotEmpty())
                <span class="landlord-summary-strip">
                    <span class="landlord-summary-dot"></span>
                    <span><strong>{{ $roomCollection->count() }}</strong> {{ Str::plural('Room', $roomCollection->count()) }}</span>
                    <span class="text-muted">·</span>
                    <span class="text-success fw-bold">{{ $availableCount }}</span> Available
                    <span class="text-muted">·</span>
                    <span class="text-secondary fw-bold">{{ $occupiedCount }}</span> Occupied
                </span>
            @endif
            <a href="{{ route('landlord.properties.show', $property->id) }}" class="btn btn-outline-secondary rounded-pill px-3">
                <i class="bi bi-arrow-left me-1"></i> Property Overview
            </a>
            <a href="{{ route('landlord.properties.rooms.create', $property->id) }}" class="btn btn-brand rounded-pill px-3">
                <i class="bi bi-plus-circle me-1"></i> Add Room
            </a>
        </div>
    </div>

    @if($roomCollection->isEmpty())
        <div class="landlord-empty-state">
            <div class="landlord-empty-icon">
                <i class="bi bi-door-open"></i>
            </div>
            <div class="landlord-empty-title">No rooms yet</div>
            <div class="landlord-empty-text mb-3">Add your first room for this property to start accepting student boarding requests.</div>
            <a href="{{ route('landlord.properties.rooms.create', $property->id) }}" class="btn btn-brand rounded-pill px-4">
                <i class="bi bi-plus-circle me-1"></i> Add First Room
            </a>
        </div>
    @else
        <div class="vstack gap-3">
            @foreach($roomCollection as $room)
                @php
                    $activeOccupants = isset($room->active_tenants) && $room->active_tenants->isNotEmpty()
                        ? $room->active_tenants
                        : ($room->current_tenant ? collect([$room->current_tenant]) : collect());
                    $occupiedBeds = $activeOccupants->count();
                    $totalCapacity = (int) ($room->capacity ?: 1);

                    $statusBadgeClass = match($room->status) {
                        'available' => 'bg-success-subtle text-success border-success-subtle',
                        'occupied' => 'bg-secondary-subtle text-secondary border-secondary-subtle',
                        'maintenance' => 'bg-warning-subtle text-warning-emphasis border-warning-subtle',
                        default => 'bg-light text-dark'
                    };
                @endphp
                <article class="landlord-card p-3 p-md-4">
                    <div class="row g-3 align-items-start">
                        <div class="col-12 col-md-auto">
                            @if(!empty($room->image_path))
                                <img src="{{ file_url($room->image_path) }}" alt="Room {{ $room->room_number }}" class="room-photo rounded-3 border">
                            @else
                                <div class="room-photo rounded-3 border d-flex align-items-center justify-content-center text-muted small bg-light">
                                    <i class="bi bi-door-closed fs-3"></i>
                                </div>
                            @endif
                        </div>
                        <div class="col">
                            <div class="d-flex flex-wrap justify-content-between gap-2 mb-2">
                                <div>
                                    <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                        <h2 class="h5 mb-0 fw-bold text-dark">Room {{ $room->room_number }}</h2>
                                        <span class="badge rounded-pill border {{ $statusBadgeClass }} small">
                                            {{ ucfirst($room->status) }}
                                        </span>
                                        <span class="chip">
                                            @if($occupiedBeds > 0)
                                                <strong>{{ $occupiedBeds }} of {{ $totalCapacity }} beds occupied</strong>
                                            @else
                                                Capacity: <strong>{{ $totalCapacity }}</strong>
                                            @endif
                                        </span>
                                        <span class="chip">Rate: <strong>₱{{ number_format($room->price, 2) }} / mo</strong></span>
                                    </div>
                                    <div class="text-muted small">Added {{ $room->created_at->diffForHumans() }}</div>
                                </div>
                                <div class="d-flex flex-wrap align-items-center gap-2">
                                    <a href="{{ route('landlord.properties.rooms.edit', [$property->id, $room->id]) }}" class="btn btn-sm btn-outline-brand rounded-pill px-3">
                                        <i class="bi bi-pencil me-1"></i> Edit Room
                                    </a>
                                    <form action="{{ route('landlord.properties.rooms.duplicate', [$property->id, $room->id]) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-secondary rounded-pill px-3" title="Duplicate Room Configuration">
                                            <i class="bi bi-copy me-1"></i> Duplicate
                                        </button>
                                    </form>
                                </div>
                            </div>

                            <div class="mb-2 small">
                                <div class="text-muted mb-1 fw-bold" style="font-size: .72rem; text-transform: uppercase;">Inclusions</div>
                                @php
                                    $inclusionItems = collect(preg_split('/[,\n;]+/', (string) $room->inclusions))
                                        ->map(fn ($item) => trim($item))
                                        ->filter()
                                        ->values();
                                @endphp
                                @if($inclusionItems->isNotEmpty())
                                    <div class="d-flex flex-wrap gap-1">
                                        @foreach($inclusionItems->take(4) as $item)
                                            <span class="chip" style="font-size: .72rem;">{{ strtoupper($item) }}</span>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="text-muted">No inclusions listed.</div>
                                @endif
                            </div>

                            <div class="small">
                                <div class="text-muted mb-1 fw-bold" style="font-size: .72rem; text-transform: uppercase;">
                                    {{ $activeOccupants->count() > 1 ? 'Current Tenants (' . $activeOccupants->count() . ')' : 'Current Tenant' }}
                                </div>
                                @if($activeOccupants->isNotEmpty())
                                    <div class="d-flex flex-column gap-1">
                                        @foreach($activeOccupants as $occupant)
                                            @php
                                                $tenantName = trim((string) ($occupant->full_name ?? $occupant->name ?? 'Unknown Tenant'));
                                                $tenantInitial = strtoupper(substr($tenantName !== '' ? $tenantName : 'U', 0, 1));
                                                $tenantContact = trim((string) ($occupant->contact_number ?? ''));
                                            @endphp
                                            <div class="d-inline-flex align-items-center gap-2">
                                                <div class="tenant-avatar">
                                                    @if(!empty($occupant->profile_image_path))
                                                        <img src="{{ file_url($occupant->profile_image_path) }}" alt="{{ $tenantName }}" class="tenant-avatar-img">
                                                    @else
                                                        <span class="tenant-avatar-initial">{{ $tenantInitial }}</span>
                                                    @endif
                                                </div>
                                                <div class="fw-semibold text-dark">{{ $tenantName }}</div>
                                                @if($tenantContact !== '')
                                                    <span class="text-muted">({{ $tenantContact }})</span>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="text-muted">No tenant assigned</div>
                                @endif
                            </div>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    @endif
</div>
@endsection

@push('styles')
<style>
    .room-photo {
        width: 140px;
        height: 105px;
        object-fit: cover;
    }
    .chip {
        display: inline-flex;
        align-items: center;
        gap: .25rem;
        border: 1px solid rgba(2,8,20,.1);
        background: #f8fafc;
        border-radius: 999px;
        padding: .15rem .55rem;
        font-size: .76rem;
        color: #334155;
    }
    .tenant-avatar {
        width: 24px;
        height: 24px;
        border-radius: 50%;
        background: #2563eb;
        color: #fff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: .65rem;
        font-weight: 700;
        overflow: hidden;
    }
    .tenant-avatar-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    @media (max-width: 575.98px) {
        .room-photo {
            width: 100%;
            height: 160px;
        }
    }
</style>
@endpush
