@extends('layouts.landlord')

@section('title', 'Properties')

@section('content')
@php
    $propertiesCollection = $properties instanceof \Illuminate\Contracts\Pagination\Paginator
        ? collect($properties->items())
        : collect($properties);

    $totalProperties = $propertiesCollection->count();
    $totalRooms = (int) $propertiesCollection->sum(fn ($prop) => (int) ($prop->rooms_total_live ?? 0));
    $vacantRooms = (int) $propertiesCollection->sum(fn ($prop) => (int) ($prop->rooms_vacant_live ?? 0));
    $mappedProperties = (int) $propertiesCollection->filter(fn ($prop) => !empty($prop->latitude) && !empty($prop->longitude))->count();
@endphp

<div class="landlord-page-shell">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
        <div>
            <div class="landlord-header-eyebrow">Portfolio</div>
            <h1 class="landlord-header-title mb-1">Properties</h1>
            <div class="landlord-header-subtitle">Manage boarding house details, room inventory, and student capacity.</div>
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            @if($totalProperties > 0)
                <span class="landlord-summary-strip">
                    <span class="landlord-summary-dot"></span>
                    <span><strong>{{ $totalProperties }}</strong> {{ Str::plural('Property', $totalProperties) }}</span>
                    <span class="text-muted">·</span>
                    <span>{{ $totalRooms }} Rooms ({{ $vacantRooms }} Vacant)</span>
                </span>
            @endif
            <a href="{{ route('landlord.properties.create') }}" class="btn btn-brand rounded-pill px-3">
                <i class="bi bi-plus-circle me-1"></i> Add Property
            </a>
        </div>
    </div>

    @if($totalProperties > 0)
        <div class="landlord-filter-bar mb-3">
            <div class="landlord-filter-search">
                <i class="bi bi-search"></i>
                <input id="propertySearch" type="text" class="form-control" placeholder="Search by property name or address...">
            </div>
            <div class="landlord-filter-select">
                <select id="propertyFilter" class="form-select">
                    <option value="all">All Properties</option>
                    <option value="vacant">With Vacant Rooms</option>
                    <option value="full">Fully Occupied</option>
                    <option value="unmapped">Map Not Set</option>
                </select>
            </div>
            <div class="landlord-filter-select">
                <button id="mapToggleBtn" class="btn btn-outline-secondary w-100 rounded-3" type="button" data-bs-toggle="collapse" data-bs-target="#propertiesMapWrap" aria-expanded="false" aria-controls="propertiesMapWrap" style="height: 40px; font-size: .88rem;">
                    <i class="bi bi-geo-alt me-1"></i> <span id="mapToggleText">View Map</span>
                </button>
            </div>
        </div>

        <div class="card shadow-sm border-0 rounded-4 mb-3" id="propertiesMapCard" style="{{ $mappedProperties > 0 ? 'display:block;' : 'display:none;' }}">
            <div id="propertiesMapWrap" class="collapse">
                <div class="card-body p-0">
                    <div class="px-3 py-2 border-bottom small text-muted d-flex justify-content-between align-items-center">
                        <span><i class="bi bi-map me-1"></i> Property Locations</span>
                        <span class="badge bg-light text-dark border">{{ $mappedProperties }} mapped</span>
                    </div>
                    <div id="propertiesMap" style="height:260px;"></div>
                </div>
            </div>
        </div>
    @endif

    <div class="vstack gap-3" id="propertyList">
        @forelse($propertiesCollection as $prop)
            @php
                $isMapped = !empty($prop->latitude) && !empty($prop->longitude);
                $vacant = (int) ($prop->rooms_vacant_live ?? 0);
                $total = (int) ($prop->rooms_total_live ?? 0);
                $occupied = max($total - $vacant, 0);
                $occupancyRate = $total > 0 ? (int) round(($occupied / $total) * 100) : 0;
                $amenityLabels = (array) config('property_amenities.flat', []);
                $buildingInclusions = collect((array) ($prop->building_inclusions ?? []))
                    ->map(fn ($key) => $amenityLabels[$key] ?? trim((string) $key))
                    ->filter()
                    ->values();
            @endphp
            <article class="property-card rounded-4 p-3 p-md-4"
                data-name="{{ strtolower($prop->name) }}"
                data-address="{{ strtolower($prop->address) }}"
                data-vacant="{{ $vacant }}"
                data-total="{{ $total }}"
                data-mapped="{{ $isMapped ? '1' : '0' }}">
                <div class="d-flex flex-column flex-md-row justify-content-between gap-3">
                    <div class="w-100 min-w-0">
                        <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                            <h2 class="h5 mb-0 fw-bold text-dark">{{ $prop->name }}</h2>
                            @if(($prop->approval_status ?? $prop->status) === 'approved')
                                <span class="badge rounded-pill bg-success-subtle text-success border border-success-subtle small">Approved</span>
                            @elseif(($prop->approval_status ?? $prop->status) === 'draft')
                                <span class="badge rounded-pill bg-secondary-subtle text-secondary border border-secondary-subtle small">Draft</span>
                            @elseif(($prop->approval_status ?? $prop->status) === 'rejected')
                                <span class="badge rounded-pill bg-danger-subtle text-danger border border-danger-subtle small">Rejected</span>
                            @else
                                <span class="badge rounded-pill bg-warning-subtle text-warning-emphasis border border-warning-subtle small">Pending Review</span>
                            @endif

                            @if($isMapped)
                                <span class="badge rounded-pill bg-light text-muted border small"><i class="bi bi-geo-alt-fill text-success me-1"></i>Mapped</span>
                            @else
                                <span class="badge rounded-pill bg-light text-muted border small"><i class="bi bi-geo-alt me-1"></i>No Map</span>
                            @endif
                        </div>

                        <div class="text-muted small mb-3">
                            <i class="bi bi-geo-alt me-1"></i> {{ $prop->address }}
                        </div>

                        @if($buildingInclusions->isNotEmpty())
                            <div class="d-flex flex-wrap gap-1 mb-3">
                                @foreach($buildingInclusions->take(3) as $inclusion)
                                    <span class="chip">{{ $inclusion }}</span>
                                @endforeach
                                @if($buildingInclusions->count() > 3)
                                    <span class="chip text-muted">+{{ $buildingInclusions->count() - 3 }} more</span>
                                @endif
                            </div>
                        @endif

                        <div class="d-flex flex-wrap gap-2 small mb-3">
                            <span class="chip">Rooms: <strong>{{ $total }}</strong></span>
                            <span class="chip">Vacant: <strong>{{ $vacant }}</strong></span>
                            <span class="chip">
                                Price:
                                <strong>
                                    @if($prop->price_min !== null || $prop->price_max !== null)
                                        ₱{{ number_format($prop->price_min, 0) }} - ₱{{ number_format($prop->price_max, 0) }}
                                    @else
                                        N/A
                                    @endif
                                </strong>
                            </span>
                            <span class="chip text-muted">Added {{ $prop->created_at->diffForHumans() }}</span>
                        </div>

                        <div class="mt-2" style="max-width: 320px;">
                            <div class="d-flex justify-content-between small text-muted mb-1">
                                <span>Occupancy</span>
                                <span class="fw-bold text-dark">{{ $occupancyRate }}%</span>
                            </div>
                            <div class="progress" role="progressbar" aria-label="Occupancy" aria-valuenow="{{ $occupancyRate }}" aria-valuemin="0" aria-valuemax="100" style="height: 6px;">
                                <div class="progress-bar bg-success" style="width: {{ $occupancyRate }}%"></div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex align-items-center gap-2 property-actions justify-content-md-end align-self-md-start pt-2 pt-md-0">
                        <a class="btn btn-sm btn-brand rounded-pill px-3" href="{{ route('landlord.properties.show', $prop->id) }}">
                            Manage Property
                        </a>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-secondary btn-icon rounded-circle" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="More actions">
                                <i class="bi bi-three-dots-vertical"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                                <li>
                                    <a class="dropdown-item py-2" href="{{ route('landlord.properties.rooms.create', $prop->id) }}">
                                        <i class="bi bi-plus-circle me-2 text-muted"></i> Add Room
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item py-2" href="{{ route('landlord.properties.edit', $prop->id) }}">
                                        <i class="bi bi-pencil me-2 text-muted"></i> Edit Property
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider my-1"></li>
                                <li>
                                    <form action="{{ route('landlord.properties.destroy', $prop->id) }}" method="POST" onsubmit="return confirm('Delete this property? All rooms and boarding requests under it will be removed.');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="dropdown-item py-2 text-danger" type="submit">
                                            <i class="bi bi-trash me-2 text-danger"></i> Delete Property
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </article>
        @empty
            <div class="landlord-empty-state">
                <div class="landlord-empty-icon">
                    <i class="bi bi-buildings"></i>
                </div>
                <div class="landlord-empty-title">No Properties Yet</div>
                <div class="landlord-empty-text mb-3">Create your first boarding house property to start listing rooms and accepting student bookings.</div>
                <a href="{{ route('landlord.properties.create') }}" class="btn btn-brand rounded-pill px-4">
                    <i class="bi bi-plus-circle me-1"></i> Add Property
                </a>
            </div>
        @endforelse

        @if($totalProperties > 0)
            <div id="propertyNoMatch" class="landlord-empty-state d-none">
                <div class="landlord-empty-icon">
                    <i class="bi bi-search"></i>
                </div>
                <div class="landlord-empty-title">No Matching Property</div>
                <div class="landlord-empty-text">Try adjusting your search keyword or selected filter option.</div>
            </div>
        @endif
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin=""/>
<style>
    .property-card {
        background: #fff;
        border: 1px solid rgba(2,8,20,.08);
        box-shadow: 0 4px 14px rgba(2,8,20,.03);
        transition: transform .15s ease, box-shadow .15s ease;
    }
    .property-card:hover {
        box-shadow: 0 8px 22px rgba(2,8,20,.06);
    }
    .chip {
        display: inline-flex;
        align-items: center;
        gap: .25rem;
        border: 1px solid rgba(2,8,20,.1);
        background: #f8fafc;
        border-radius: 999px;
        padding: .2rem .6rem;
        font-size: .78rem;
        color: #334155;
    }
    .property-actions {
        white-space: nowrap;
    }
    .btn-icon {
        width: 32px;
        height: 32px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
</style>
@endpush

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const propertiesWithCoords = @json($propertiesCollection->filter(fn ($p) => $p->latitude && $p->longitude)->values());
    const searchInput = document.getElementById('propertySearch');
    const filterSelect = document.getElementById('propertyFilter');
    const noMatchState = document.getElementById('propertyNoMatch');
    const propertyCards = Array.from(document.querySelectorAll('#propertyList .property-card'));

    const applyFilters = () => {
        if (!searchInput || !filterSelect) return;

        const query = searchInput.value.trim().toLowerCase();
        const filter = filterSelect.value;
        let visible = 0;

        propertyCards.forEach((card) => {
            const name = card.dataset.name || '';
            const address = card.dataset.address || '';
            const vacant = Number(card.dataset.vacant || 0);
            const total = Number(card.dataset.total || 0);
            const mapped = Number(card.dataset.mapped || 0);

            const matchesQuery = !query || name.includes(query) || address.includes(query);

            let matchesFilter = true;
            if (filter === 'vacant') matchesFilter = vacant > 0;
            if (filter === 'full') matchesFilter = total > 0 && vacant === 0;
            if (filter === 'unmapped') matchesFilter = mapped === 0;

            const show = matchesQuery && matchesFilter;
            card.classList.toggle('d-none', !show);
            if (show) visible += 1;
        });

        if (noMatchState) {
            noMatchState.classList.toggle('d-none', visible !== 0);
        }
    };

    if (searchInput && filterSelect) {
        searchInput.addEventListener('input', applyFilters);
        filterSelect.addEventListener('change', applyFilters);
    }

    const mapWrap = document.getElementById('propertiesMapWrap');
    const mapToggleBtn = document.getElementById('mapToggleBtn');
    const mapToggleText = document.getElementById('mapToggleText');
    
    if (propertiesWithCoords.length > 0) {
        const mapCard = document.getElementById('propertiesMapCard');
        if (!mapCard) return;
        mapCard.style.display = 'block';
        
        let mapInstance = null;

        const initMap = () => {
            if (mapInstance) return;
            mapInstance = L.map('propertiesMap');
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxNativeZoom: 19,
                maxZoom: 22,
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(mapInstance);
            
            const markerLatLngs = [];
            propertiesWithCoords.forEach(property => {
                const lat = Number(property.latitude);
                const lng = Number(property.longitude);
                if (!Number.isFinite(lat) || !Number.isFinite(lng)) return;

                const latLng = [lat, lng];
                const marker = L.marker(latLng).addTo(mapInstance);
                marker.bindPopup(`
                    <strong>${property.name}</strong><br>
                    ${property.address}<br>
                    <span class="badge bg-primary">${property.rooms_vacant_live} vacant / ${property.rooms_total_live} total rooms</span><br>
                    <a href="/landlord/properties/${property.id}" class="btn btn-sm btn-brand mt-2">View Details</a>
                `);
                markerLatLngs.push(latLng);
            });

            if (markerLatLngs.length === 1) {
                mapInstance.setView(markerLatLngs[0], 16);
            } else if (markerLatLngs.length > 1) {
                const bounds = L.latLngBounds(markerLatLngs);
                mapInstance.fitBounds(bounds.pad(0.2), { padding: [20, 20] });
            }
        };

        if (mapWrap && mapToggleBtn) {
            mapWrap.addEventListener('shown.bs.collapse', () => {
                if (mapToggleText) mapToggleText.textContent = 'Hide Map';
                initMap();
                setTimeout(() => {
                    if (mapInstance) mapInstance.invalidateSize();
                }, 170);
            });
            mapWrap.addEventListener('hidden.bs.collapse', () => {
                if (mapToggleText) mapToggleText.textContent = 'View Map';
            });
        }
    } else if (mapToggleBtn) {
        mapToggleBtn.disabled = true;
        if (mapToggleText) mapToggleText.textContent = 'Map Unavailable';
    }
});
</script>
@endpush