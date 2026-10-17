@extends('layouts.landlord')

@section('title', 'Room Directory')

@section('content')
<div class="landlord-page-shell">
    @php
        $availableCount = $rooms->where('status', 'available')->count();
        $occupiedCount = $rooms->where('status', 'occupied')->count();
        $maintenanceCount = $rooms->where('status', 'maintenance')->count();
    @endphp

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
        <div>
            <div class="landlord-header-eyebrow">Portfolio Overview</div>
            <h1 class="landlord-header-title mb-1">Room Directory</h1>
            <div class="landlord-header-subtitle">Review rooms across all properties with a cleaner, focused view.</div>
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            @if($rooms->isNotEmpty())
                <span class="landlord-summary-strip">
                    <span class="landlord-summary-dot"></span>
                    <span><strong>{{ $rooms->count() }}</strong> {{ Str::plural('Room', $rooms->count()) }}</span>
                    <span class="text-muted">·</span>
                    <span class="text-success fw-bold">{{ $availableCount }}</span> Available
                    <span class="text-muted">·</span>
                    <span class="text-secondary fw-bold">{{ $occupiedCount }}</span> Occupied
                    @if($maintenanceCount > 0)
                        <span class="text-muted">·</span>
                        <span class="text-warning-emphasis fw-bold">{{ $maintenanceCount }}</span> Maintenance
                    @endif
                </span>
            @endif
            <button type="button" class="btn btn-brand rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#addRoomPropertyModal">
                <i class="bi bi-plus-circle me-1"></i> Add Room
            </button>
        </div>
    </div>

    @if($rooms->isNotEmpty())
        <div class="landlord-filter-bar mb-3">
            <div class="landlord-filter-search">
                <i class="bi bi-search"></i>
                <input id="roomSearchInput" type="text" class="form-control" placeholder="Search room number, property, or address...">
            </div>
            @if($properties->count() > 1)
                <div class="landlord-filter-select">
                    <select id="roomPropertyFilter" class="form-select">
                        <option value="all">All Properties</option>
                        @foreach($properties as $property)
                            <option value="{{ $property->id }}">{{ $property->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
            <div class="landlord-filter-select">
                <select id="roomStatusFilter" class="form-select">
                    <option value="all">All Statuses</option>
                    <option value="available">Available</option>
                    <option value="occupied">Occupied</option>
                    <option value="maintenance">Maintenance</option>
                </select>
            </div>
        </div>
    @endif

    <div class="landlord-card" id="roomListWrap">
        @forelse($rooms as $room)
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

                $searchBlob = strtolower(trim(implode(' ', [
                    $room->room_number,
                    $room->property->name ?? '',
                    $room->property->address ?? '',
                    $room->inclusions ?? '',
                    implode(' ', $activeOccupants->map(fn($o) => $o->full_name ?? $o->name ?? '')->toArray())
                ])));
            @endphp

            <article class="room-item"
                data-search="{{ $searchBlob }}"
                data-property="{{ $room->property_id }}"
                data-status="{{ strtolower((string) $room->status) }}">
                
                <div class="room-thumb-wrap">
                    @if(!empty($room->image_path))
                        <img src="{{ file_url($room->image_path) }}" alt="Room {{ $room->room_number }}" class="room-thumb">
                    @else
                        <div class="room-thumb room-thumb-empty">
                            <i class="bi bi-door-closed"></i>
                        </div>
                    @endif
                </div>

                <div class="room-main">
                    <div class="room-head">
                        <div>
                            <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                                <h2 class="room-title mb-0">Room {{ $room->room_number }}</h2>
                                <span class="badge rounded-pill border {{ $statusBadgeClass }} small">
                                    {{ ucfirst($room->status) }}
                                </span>
                            </div>
                            <div class="room-subtitle">{{ $room->property->name }}</div>
                            <div class="text-muted small"><i class="bi bi-geo-alt me-1"></i>{{ $room->property->address }}</div>
                        </div>

                        <div class="d-flex align-items-center gap-2 flex-wrap justify-content-end">
                            <span class="meta-chip">
                                <i class="bi bi-people"></i>
                                @if($occupiedBeds > 0)
                                    <strong>{{ $occupiedBeds }} of {{ $totalCapacity }} beds occupied</strong>
                                @else
                                    {{ $totalCapacity }} {{ Str::plural('slot', $totalCapacity) }}
                                @endif
                            </span>
                            <span class="meta-chip"><i class="bi bi-cash"></i>₱{{ number_format($room->price, 2) }} / mo</span>
                        </div>
                    </div>

                    <div class="room-body-grid">
                        <div>
                            <div class="meta-label mb-1">Inclusions</div>
                            @if($room->inclusions)
                                @php
                                    $incAll = collect(preg_split('/[,\n;]+/', $room->inclusions))->map('trim')->filter();
                                    $incItems = $incAll->take(3);
                                @endphp
                                @if($incItems->isNotEmpty())
                                    <div class="d-flex flex-wrap gap-1">
                                        @foreach($incItems as $item)
                                            <span class="inc-pill">{{ strtoupper($item) }}</span>
                                        @endforeach
                                        @if($incAll->count() > 3)
                                            <span class="inc-more small text-muted">+{{ $incAll->count() - 3 }} more</span>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-muted small">No inclusions listed</span>
                                @endif
                            @else
                                <span class="text-muted small">No inclusions listed</span>
                            @endif
                        </div>

                        <div>
                            <div class="meta-label mb-1">{{ $activeOccupants->count() > 1 ? 'Current Tenants (' . $activeOccupants->count() . ')' : 'Current Tenant' }}</div>
                            @if($activeOccupants->isNotEmpty())
                                <div class="d-flex flex-column gap-1">
                                    @foreach($activeOccupants as $occupant)
                                        @php
                                            $tenantName = trim((string) ($occupant->full_name ?? $occupant->name ?? 'Unknown Tenant'));
                                            $tenantInitial = strtoupper(substr($tenantName !== '' ? $tenantName : 'U', 0, 1));
                                            $tenantContact = trim((string) ($occupant->contact_number ?? ''));
                                        @endphp
                                        <div class="tenant-box">
                                            <div class="tenant-avatar">
                                                @if(!empty($occupant->profile_image_path))
                                                    <img src="{{ file_url($occupant->profile_image_path) }}" alt="{{ $tenantName }}" class="tenant-avatar-img">
                                                @else
                                                    <span class="tenant-avatar-initial">{{ $tenantInitial }}</span>
                                                @endif
                                            </div>
                                            <div class="min-w-0">
                                                <div class="fw-semibold text-dark text-truncate" style="font-size: .85rem;">{{ $tenantName }}</div>
                                                @if($tenantContact !== '')
                                                    <div class="text-muted small">{{ $tenantContact }}</div>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <span class="text-muted small">No residents yet</span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="room-actions">
                    <a href="{{ route('landlord.properties.rooms.edit', [$room->property_id, $room->id]) }}" class="btn btn-sm btn-brand rounded-pill px-3">
                        <i class="bi bi-pencil me-1"></i>Edit Room
                    </a>
                    <form action="{{ route('landlord.properties.rooms.duplicate', [$room->property_id, $room->id]) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-secondary rounded-pill px-3 w-100" title="Duplicate Room Configuration">
                            <i class="bi bi-copy me-1"></i>Duplicate
                        </button>
                    </form>
                </div>
            </article>
        @empty
            <div class="landlord-empty-state border-0">
                <div class="landlord-empty-icon">
                    <i class="bi bi-door-open"></i>
                </div>
                <div class="landlord-empty-title">No Rooms Found</div>
                <div class="landlord-empty-text mb-3">Add rooms to your properties to begin receiving student boarding requests.</div>
                <button type="button" class="btn btn-brand rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#addRoomPropertyModal">
                    <i class="bi bi-plus-circle me-1"></i> Add Your First Room
                </button>
            </div>
        @endforelse

        @if($rooms->isNotEmpty())
            <div id="roomNoMatch" class="landlord-empty-state border-0 d-none">
                <div class="landlord-empty-icon">
                    <i class="bi bi-search"></i>
                </div>
                <div class="landlord-empty-title">No matching rooms</div>
                <div class="landlord-empty-text">Try adjusting your search keywords or selected property/status filter.</div>
            </div>
        @endif
    </div>
</div>

<!-- Modal to select property for Add Room -->
<div class="modal fade" id="addRoomPropertyModal" tabindex="-1" aria-labelledby="addRoomPropertyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-sm rounded-4">
            <div class="modal-header border-bottom">
                <h5 class="modal-title fw-bold" id="addRoomPropertyModalLabel">Select Property for New Room</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <p class="text-muted small mb-3">Choose which boarding house property you want to add a room to:</p>
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
                    <div class="alert alert-light border rounded-3 mb-0">
                        <div class="fw-semibold mb-1">No properties available</div>
                        <div class="small text-muted mb-2">You need to register a property first before creating rooms.</div>
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
    .room-item {
        display: grid;
        grid-template-columns: 130px minmax(0, 1fr) auto;
        gap: 1.1rem;
        padding: 1.1rem 1.25rem;
        border-bottom: 1px solid rgba(2,8,20,.08);
        align-items: start;
        transition: background .2s ease;
    }
    .room-item:hover {
        background: #fbfcfd;
    }
    .room-item:last-child {
        border-bottom: none;
    }
    .room-thumb-wrap {
        width: 130px;
    }
    .room-thumb {
        width: 100%;
        height: 100px;
        border-radius: .75rem;
        border: 1px solid rgba(2,8,20,.1);
        object-fit: cover;
    }
    .room-thumb-empty {
        background: #f1f5f9;
        color: #94a3b8;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
    }
    .room-main {
        min-width: 0;
    }
    .room-head {
        display: flex;
        justify-content: space-between;
        align-items: start;
        gap: .8rem;
        margin-bottom: .6rem;
    }
    .room-title {
        font-size: 1.05rem;
        font-weight: 700;
        color: #0f172a;
        line-height: 1.2;
    }
    .room-subtitle {
        font-size: .88rem;
        font-weight: 600;
        color: #166534;
    }
    .room-body-grid {
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(200px, .9fr);
        gap: .8rem;
    }
    .meta-label {
        font-size: .68rem;
        letter-spacing: .05em;
        text-transform: uppercase;
        color: #64748b;
        font-weight: 700;
    }
    .meta-chip {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        border: 1px solid rgba(2,8,20,.1);
        border-radius: 999px;
        background: #f8fafc;
        color: #334155;
        padding: .15rem .55rem;
        font-size: .76rem;
        font-weight: 600;
    }
    .inc-pill {
        background: rgba(22,101,52,.08);
        color: #14532d;
        border: 1px solid rgba(22,101,52,.15);
        border-radius: 999px;
        padding: .15rem .5rem;
        font-size: .7rem;
        font-weight: 700;
        letter-spacing: .02em;
    }
    .tenant-box {
        display: inline-flex;
        align-items: center;
        gap: .5rem;
    }
    .tenant-avatar {
        width: 26px;
        height: 26px;
        border-radius: 50%;
        background: #2563eb;
        color: #fff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: .65rem;
        font-weight: 700;
        overflow: hidden;
        flex-shrink: 0;
    }
    .tenant-avatar-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .room-actions {
        display: flex;
        flex-direction: column;
        gap: .4rem;
        justify-content: center;
        min-width: 120px;
    }
    .property-select-list {
        display: grid;
        gap: .5rem;
    }
    .property-select-item {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        grid-template-areas:
            "name icon"
            "address icon";
        gap: .1rem .6rem;
        border: 1px solid rgba(2,8,20,.1);
        border-radius: .75rem;
        padding: .65rem .85rem;
        background: #ffffff;
        transition: all .15s ease;
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
    }
    .property-select-address {
        grid-area: address;
        color: #64748b;
        font-size: .78rem;
    }
    .property-select-item i {
        grid-area: icon;
        color: #94a3b8;
        align-self: center;
    }

    @media (max-width: 991.98px) {
        .room-item {
            grid-template-columns: 110px minmax(0, 1fr);
        }
        .room-thumb-wrap {
            width: 110px;
        }
        .room-thumb {
            height: 90px;
        }
        .room-actions {
            grid-column: 1 / -1;
            flex-direction: row;
            justify-content: flex-start;
            min-width: 0;
            width: 100%;
            border-top: 1px dashed rgba(2,8,20,.08);
            padding-top: .75rem;
        }
        .room-actions .btn {
            flex: 1 1 auto;
        }
    }

    @media (max-width: 575.98px) {
        .room-item {
            grid-template-columns: 1fr;
        }
        .room-thumb-wrap {
            width: 100%;
        }
        .room-thumb {
            height: 160px;
        }
        .room-head {
            flex-direction: column;
        }
        .room-body-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@push('scripts')
<script>
(function() {
    const searchInput = document.getElementById('roomSearchInput');
    const propertyFilter = document.getElementById('roomPropertyFilter');
    const statusFilter = document.getElementById('roomStatusFilter');
    const items = Array.from(document.querySelectorAll('.room-item'));
    const noMatch = document.getElementById('roomNoMatch');

    if (!items.length) return;

    const applyFilters = () => {
        const query = (searchInput?.value || '').trim().toLowerCase();
        const propId = propertyFilter?.value || 'all';
        const status = statusFilter?.value || 'all';
        let visibleCount = 0;

        items.forEach(item => {
            const itemSearch = item.dataset.search || '';
            const itemProp = item.dataset.property || '';
            const itemStatus = item.dataset.status || '';

            const matchQuery = !query || itemSearch.includes(query);
            const matchProp = propId === 'all' || itemProp === propId;
            const matchStatus = status === 'all' || itemStatus === status;

            const visible = matchQuery && matchProp && matchStatus;
            item.classList.toggle('d-none', !visible);
            if (visible) visibleCount++;
        });

        if (noMatch) {
            noMatch.classList.toggle('d-none', visibleCount > 0);
        }
    };

    if (searchInput) searchInput.addEventListener('input', applyFilters);
    if (propertyFilter) propertyFilter.addEventListener('change', applyFilters);
    if (statusFilter) statusFilter.addEventListener('change', applyFilters);
})();
</script>
@endpush
