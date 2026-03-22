@extends('layouts.landlord')

@section('content')
<div class="room-directory-shell">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <div class="text-uppercase small text-muted fw-semibold">Portfolio Overview</div>
            <h1 class="h3 mb-1">Room Directory</h1>
            <div class="text-muted small">Review rooms across all properties with a cleaner, focused view.</div>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('landlord.properties.index') }}" class="btn btn-outline-secondary rounded-pill px-3">Manage Properties</a>
            <button type="button" class="btn btn-brand rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#addRoomPropertyModal">Add Room</button>
        </div>
    </div>

    @if($rooms->count() > 0)
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="stat-tile stat-available">
                <div class="stat-count">{{ $rooms->where('status', 'available')->count() }}</div>
                <div class="stat-label">Available Rooms</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-tile stat-occupied">
                <div class="stat-count">{{ $rooms->where('status', 'occupied')->count() }}</div>
                <div class="stat-label">Occupied Rooms</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-tile stat-maintenance">
                <div class="stat-count">{{ $rooms->where('status', 'maintenance')->count() }}</div>
                <div class="stat-label">Under Maintenance</div>
            </div>
        </div>
    </div>
    @endif

    <div class="room-list-card">
        @forelse($rooms as $room)
            <article class="room-item">
                <div class="room-thumb-wrap">
                    @if(!empty($room->image_path))
                        <img src="{{ asset('storage/'.$room->image_path) }}" alt="Room photo" class="room-thumb">
                    @else
                        <div class="room-thumb room-thumb-empty">
                            <i class="bi bi-image"></i>
                        </div>
                    @endif
                </div>

                <div class="room-main">
                    <div class="room-head">
                        <div>
                            <div class="room-title">Room {{ $room->room_number }}</div>
                            <div class="room-subtitle">{{ $room->property->name }}</div>
                            <div class="text-muted small">{{ $room->property->address }}</div>
                        </div>
                        <div class="d-flex align-items-center gap-2 flex-wrap justify-content-end">
                            @if($room->status==='available')
                                <span class="badge rounded-pill text-bg-success">Available</span>
                            @elseif($room->status==='occupied')
                                <span class="badge rounded-pill text-bg-secondary">Occupied</span>
                            @else
                                <span class="badge rounded-pill text-bg-warning">Maintenance</span>
                            @endif
                            <span class="meta-chip"><i class="bi bi-people"></i>{{ $room->capacity }} slots</span>
                            <span class="meta-chip"><i class="bi bi-cash"></i>₱{{ number_format($room->price, 2) }}</span>
                        </div>
                    </div>

                    <div class="room-body-grid">
                        <div>
                            <div class="meta-label mb-2">Inclusions</div>
                            @if($room->inclusions)
                                @php
                                    $incAll = collect(preg_split('/[,\n;]+/', $room->inclusions))->map('trim')->filter();
                                    $incItems = $incAll->take(4);
                                @endphp
                                @if($incItems->isNotEmpty())
                                    <div class="d-flex flex-wrap gap-2">
                                        @foreach($incItems as $item)
                                            <span class="inc-pill">{{ strtoupper($item) }}</span>
                                        @endforeach
                                        @if($incAll->count() > 4)
                                            <span class="inc-more">+{{ $incAll->count() - 4 }} more</span>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-muted small">No inclusions</span>
                                @endif
                            @else
                                <span class="text-muted small">No inclusions</span>
                            @endif
                        </div>

                        <div>
                            <div class="meta-label mb-2">Current Tenant</div>
                            @if($room->current_tenant)
                                <div class="tenant-box">
                                    <div class="tenant-avatar"><i class="fas fa-user fa-xs"></i></div>
                                    <div>
                                        <div class="fw-semibold">{{ $room->current_tenant->full_name }}</div>
                                        <div class="text-muted small">{{ $room->current_tenant->student_id }}</div>
                                    </div>
                                </div>
                            @else
                                <span class="text-muted small">No tenant assigned</span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="room-actions">
                    <a href="{{ route('landlord.properties.rooms.edit', [$room->property_id, $room->id]) }}" class="btn btn-sm btn-outline-brand rounded-pill px-3">Edit Room</a>
                    <a href="{{ route('landlord.properties.show', $room->property_id) }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">View Property</a>
                </div>
            </article>
        @empty
            <div class="text-center text-muted py-5">
                <i class="fas fa-home fa-3x mb-3 text-muted"></i>
                <p class="mb-2">No rooms found.</p>
                <a href="{{ route('landlord.properties.create') }}" class="btn btn-brand rounded-pill px-3">Create Your First Property</a>
            </div>
        @endforelse
    </div>
</div>

<div class="modal fade" id="addRoomPropertyModal" tabindex="-1" aria-labelledby="addRoomPropertyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-sm">
            <div class="modal-header">
                <h5 class="modal-title" id="addRoomPropertyModalLabel">Select Property</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small mb-3">Choose a property where you want to create a new room.</p>
                @if($properties->isNotEmpty())
                    <div class="property-select-list">
                        @foreach($properties as $property)
                            <a href="{{ route('landlord.properties.rooms.create', $property->id) }}" class="property-select-item text-decoration-none">
                                <span class="property-select-name">{{ $property->name }}</span>
                                <span class="property-select-address">{{ $property->address }}</span>
                                <i class="bi bi-chevron-right"></i>
                            </a>
                        @endforeach
                    </div>
                @else
                    <div class="alert alert-light border mb-0">
                        <div class="fw-semibold mb-1">No properties yet</div>
                        <div class="small text-muted mb-2">You need to create a property first before adding rooms.</div>
                        <a href="{{ route('landlord.properties.create') }}" class="btn btn-sm btn-brand rounded-pill px-3">Create Property</a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .room-directory-shell {
        background: linear-gradient(180deg, #ffffff 0%, #fbfdfc 100%);
        border: 1px solid rgba(2,8,20,.08);
        border-radius: 1.25rem;
        box-shadow: 0 10px 26px rgba(2,8,20,.06);
        padding: 1.25rem;
    }
    .room-list-card {
        border: 1px solid rgba(2,8,20,.09);
        border-radius: 1rem;
        background: #ffffff;
        overflow: hidden;
    }
    .room-item {
        display: grid;
        grid-template-columns: 140px minmax(0, 1fr) auto;
        gap: 1rem;
        padding: 1rem;
        border-bottom: 1px solid rgba(2,8,20,.08);
        align-items: start;
    }
    .room-item:last-child {
        border-bottom: none;
    }
    .room-thumb-wrap {
        width: 140px;
    }
    .room-thumb {
        width: 100%;
        height: 112px;
        border-radius: .8rem;
        border: 1px solid rgba(2,8,20,.1);
        object-fit: cover;
    }
    .room-thumb-empty {
        background: #f1f5f9;
        color: #64748b;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
    }
    .room-main {
        min-width: 0;
    }
    .room-head {
        display: flex;
        justify-content: space-between;
        align-items: start;
        gap: .8rem;
        margin-bottom: .75rem;
    }
    .room-title {
        font-size: 1rem;
        font-weight: 700;
        color: #0f172a;
        line-height: 1.2;
    }
    .room-subtitle {
        font-size: .93rem;
        font-weight: 600;
        color: #14532d;
    }
    .room-body-grid {
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(220px, .9fr);
        gap: .9rem;
    }
    .meta-label {
        font-size: .72rem;
        letter-spacing: .05em;
        text-transform: uppercase;
        color: rgba(2,8,20,.58);
        font-weight: 700;
    }
    .meta-chip {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        border: 1px solid rgba(2,8,20,.12);
        border-radius: 999px;
        background: #f8fafc;
        color: #0f172a;
        padding: .18rem .55rem;
        font-size: .78rem;
        font-weight: 600;
    }
    .inc-pill {
        background: rgba(22,101,52,.1);
        color: #14532d;
        border: 1px solid rgba(22,101,52,.15);
        border-radius: 999px;
        padding: .2rem .55rem;
        font-size: .71rem;
        font-weight: 700;
        letter-spacing: .02em;
    }
    .inc-more {
        color: rgba(2,8,20,.55);
        font-size: .76rem;
        font-weight: 600;
        padding-top: .2rem;
    }
    .tenant-box {
        display: inline-flex;
        align-items: center;
        gap: .55rem;
    }
    .tenant-avatar {
        width: 30px;
        height: 30px;
        border-radius: 999px;
        background: #2563eb;
        color: #fff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: .65rem;
    }
    .room-actions {
        display: flex;
        flex-direction: column;
        gap: .45rem;
        justify-content: center;
        min-width: 130px;
    }
    .stat-tile {
        border: 1px solid rgba(2,8,20,.1);
        border-radius: .95rem;
        background: #ffffff;
        padding: .95rem 1rem;
        text-align: center;
        box-shadow: 0 8px 20px rgba(2,8,20,.05);
    }
    .stat-count {
        font-size: 1.3rem;
        font-weight: 700;
        line-height: 1.1;
        margin-bottom: .2rem;
    }
    .stat-label {
        font-size: .82rem;
        color: rgba(2,8,20,.62);
        font-weight: 600;
    }
    .stat-available .stat-count { color: #166534; }
    .stat-occupied .stat-count { color: #334155; }
    .stat-maintenance .stat-count { color: #a16207; }
    .property-select-list {
        display: grid;
        gap: .55rem;
    }
    .property-select-item {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        grid-template-areas:
            "name icon"
            "address icon";
        gap: .1rem .6rem;
        border: 1px solid rgba(2,8,20,.12);
        border-radius: .75rem;
        padding: .65rem .75rem;
        background: #ffffff;
    }
    .property-select-item:hover {
        border-color: rgba(22,101,52,.35);
        background: #f8fffb;
    }
    .property-select-name {
        grid-area: name;
        color: #14532d;
        font-weight: 700;
        font-size: .92rem;
        line-height: 1.2;
    }
    .property-select-address {
        grid-area: address;
        color: rgba(2,8,20,.62);
        font-size: .78rem;
        line-height: 1.25;
    }
    .property-select-item i {
        grid-area: icon;
        color: rgba(2,8,20,.45);
        align-self: center;
    }
    @media (max-width: 1199.98px) {
        .room-item {
            grid-template-columns: 120px minmax(0, 1fr);
        }
        .room-thumb-wrap {
            width: 120px;
        }
        .room-thumb {
            height: 100px;
        }
        .room-actions {
            grid-column: 1 / -1;
            flex-direction: row;
            justify-content: flex-end;
            min-width: 0;
        }
    }
    @media (max-width: 767.98px) {
        .room-item {
            grid-template-columns: 1fr;
            gap: .75rem;
        }
        .room-thumb-wrap {
            width: 100%;
        }
        .room-thumb {
            height: 180px;
        }
        .room-head {
            flex-direction: column;
        }
        .room-body-grid {
            grid-template-columns: 1fr;
        }
        .room-actions {
            justify-content: flex-start;
        }
    }
</style>
@endpush