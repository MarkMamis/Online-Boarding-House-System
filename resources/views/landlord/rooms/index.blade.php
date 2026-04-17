@extends('layouts.landlord')

@section('content')
@php
    $roomCollection = $rooms instanceof \Illuminate\Contracts\Pagination\Paginator
        ? collect($rooms->items())
        : collect($rooms);

    $availableCount = $roomCollection->where('status', 'available')->count();
    $occupiedCount = $roomCollection->where('status', 'occupied')->count();
    $maintenanceCount = $roomCollection->where('status', 'maintenance')->count();
@endphp

<div class="glass-card rounded-4 p-4 p-md-5">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <div class="small text-uppercase fw-semibold text-muted">Property Rooms</div>
            <h1 class="h3 mb-1">{{ $property->name }}</h1>
            <div class="text-muted">{{ $property->address }}</div>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('landlord.properties.show', $property->id) }}" class="btn btn-outline-secondary rounded-pill px-3">Back to Properties</a>
            <a href="{{ route('landlord.properties.rooms.create', $property->id) }}" class="btn btn-brand rounded-pill px-3">Add Room</a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="room-metric">
                <div class="room-metric-label">Total Rooms</div>
                <div class="room-metric-value">{{ $roomCollection->count() }}</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="room-metric">
                <div class="room-metric-label">Available</div>
                <div class="room-metric-value text-success">{{ $availableCount }}</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="room-metric">
                <div class="room-metric-label">Occupied</div>
                <div class="room-metric-value text-secondary">{{ $occupiedCount }}</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="room-metric">
                <div class="room-metric-label">Maintenance</div>
                <div class="room-metric-value text-warning">{{ $maintenanceCount }}</div>
            </div>
        </div>
    </div>

    @if($roomCollection->isEmpty())
        <div class="text-center rounded-4 border-2 border-dashed py-5 px-3 bg-white">
            <div class="h5 mb-2">No rooms yet</div>
            <p class="text-muted mb-3">Add your first room for this property to start accepting bookings.</p>
            <a href="{{ route('landlord.properties.rooms.create', $property->id) }}" class="btn btn-brand rounded-pill px-4">Add First Room</a>
        </div>
    @else
        <div class="vstack gap-3">
            @foreach($roomCollection as $room)
                <article class="room-card rounded-4 p-3 p-md-4">
                    <div class="row g-3 align-items-start">
                        <div class="col-12 col-md-auto">
                            @if(!empty($room->image_path))
                                <img src="{{ asset('storage/'.$room->image_path) }}" alt="Room photo" class="room-photo rounded-3 border">
                            @else
                                <div class="room-photo rounded-3 border d-flex align-items-center justify-content-center text-muted small bg-light">No Photo</div>
                            @endif
                        </div>
                        <div class="col">
                            <div class="d-flex flex-wrap justify-content-between gap-2 mb-2">
                                <div>
                                    <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                        <h2 class="h5 mb-0">{{ $room->room_number }}</h2>
                                        <span class="room-chip room-chip-sm">Capacity: <strong>{{ $room->capacity }}</strong></span>
                                        <span class="room-chip room-chip-sm">Price: <strong>₱{{ number_format($room->price, 2) }}</strong></span>
                                    </div>
                                    <div class="text-muted small">Added {{ $room->created_at->diffForHumans() }}</div>
                                </div>
                                <div class="d-flex flex-column align-items-end gap-2 room-actions-top">
                                    <div>
                                        @if($room->status === 'available')
                                            <span class="badge text-bg-success">Available</span>
                                        @elseif($room->status === 'occupied')
                                            <span class="badge text-bg-secondary">Occupied</span>
                                        @else
                                            <span class="badge text-bg-warning">Maintenance</span>
                                        @endif
                                    </div>
                                    <div class="d-flex flex-wrap justify-content-end gap-2">
                                        <a href="{{ route('landlord.properties.rooms.edit', [$property->id, $room->id]) }}" class="btn btn-sm btn-outline-brand">Edit Room</a>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-2 small">
                                <div class="text-muted mb-1">Inclusions</div>
                                @php
                                    $inclusionItems = collect(preg_split('/[,\n;]+/', (string) $room->inclusions))
                                        ->map(fn ($item) => trim($item))
                                        ->filter()
                                        ->values();
                                @endphp
                                @if($inclusionItems->isNotEmpty())
                                    <div class="d-flex flex-wrap gap-2">
                                        @foreach($inclusionItems as $item)
                                            <span class="room-chip room-chip-xs text-uppercase">{{ $item }}</span>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="text-muted">No inclusions listed.</div>
                                @endif
                            </div>

                            <div class="mb-3 small">
                                <div class="text-muted mb-1">Current Tenant</div>
                                @if($room->current_tenant)
                                    @php
                                        $tenantName = trim((string) ($room->current_tenant->full_name ?? $room->current_tenant->name ?? 'Unknown Tenant'));
                                        $tenantInitial = strtoupper(substr($tenantName !== '' ? $tenantName : 'U', 0, 1));
                                        $tenantContact = trim((string) ($room->current_tenant->contact_number ?? ''));
                                    @endphp
                                    <div class="tenant-box">
                                        <div class="tenant-avatar">
                                            @if(!empty($room->current_tenant->profile_image_path))
                                                <img
                                                    src="{{ asset('storage/' . ltrim($room->current_tenant->profile_image_path, '/')) }}"
                                                    alt="{{ $tenantName }}"
                                                    class="tenant-avatar-img"
                                                >
                                            @else
                                                <span class="tenant-avatar-initial">{{ $tenantInitial }}</span>
                                            @endif
                                        </div>
                                        <div>
                                            <div class="fw-semibold">{{ $tenantName }}</div>
                                            <div class="text-muted">{{ $tenantContact !== '' ? $tenantContact : 'No contact number provided' }}</div>
                                        </div>
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
    .room-metric {
        background: #fff;
        border: 1px solid rgba(2,8,20,.08);
        border-radius: 1rem;
        padding: .9rem 1rem;
        height: 100%;
    }
    .room-metric-label {
        font-size: .78rem;
        letter-spacing: .04em;
        text-transform: uppercase;
        color: rgba(2,8,20,.55);
    }
    .room-metric-value {
        font-size: 1.4rem;
        font-weight: 700;
        color: #0f172a;
    }
    .room-card {
        background: #fff;
        border: 1px solid rgba(2,8,20,.08);
        box-shadow: 0 8px 20px rgba(2,8,20,.05);
    }
    .room-photo {
        width: 124px;
        height: 124px;
        object-fit: cover;
    }
    .room-chip {
        display: inline-flex;
        align-items: center;
        gap: .25rem;
        border: 1px solid rgba(2,8,20,.12);
        background: rgba(248,250,252,.9);
        border-radius: 999px;
        padding: .35rem .7rem;
    }
    .room-chip-sm {
        font-size: .84rem;
        padding: .24rem .56rem;
    }
    .room-chip-xs {
        font-size: .74rem;
        padding: .2rem .52rem;
        letter-spacing: .02em;
    }
    .room-actions-top .btn {
        white-space: nowrap;
    }
    .tenant-box {
        display: inline-flex;
        align-items: center;
        gap: .55rem;
    }
    .tenant-avatar {
        width: 32px;
        height: 32px;
        border-radius: 999px;
        background: #2563eb;
        color: #fff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: .7rem;
        font-weight: 700;
        overflow: hidden;
        border: 1px solid rgba(255,255,255,.5);
        box-shadow: 0 4px 10px rgba(2,8,20,.16);
    }
    .tenant-avatar-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }
    .tenant-avatar-initial {
        line-height: 1;
    }
    @media (max-width: 767.98px) {
        .room-photo {
            width: 100%;
            height: 180px;
        }
    }
</style>
@endpush

