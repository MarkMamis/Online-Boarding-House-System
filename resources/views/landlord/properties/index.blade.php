@extends('layouts.landlord')

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

<div class="glass-card rounded-4 p-4 p-md-5 portfolio-shell">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <div class="text-uppercase small text-muted fw-semibold">Portfolio</div>
            <h2 class="h3 mb-1">Properties</h2>
            <p class="text-muted mb-0">Use this page as your property control center. Open rooms from each property card.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">  
            <a href="{{ route('landlord.properties.create') }}" class="btn btn-brand rounded-pill px-3">Add Property</a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="portfolio-metric h-100">
                <div class="portfolio-metric-label">Properties</div>
                <div class="portfolio-metric-value">{{ number_format($totalProperties) }}</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="portfolio-metric h-100">
                <div class="portfolio-metric-label">Total Rooms</div>
                <div class="portfolio-metric-value">{{ number_format($totalRooms) }}</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="portfolio-metric h-100">
                <div class="portfolio-metric-label">Vacant Rooms</div>
                <div class="portfolio-metric-value">{{ number_format($vacantRooms) }}</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="portfolio-metric h-100">
                <div class="portfolio-metric-label">Mapped Properties</div>
                <div class="portfolio-metric-value">{{ number_format($mappedProperties) }}</div>
            </div>
        </div>
    </div>

    @if($totalProperties > 0)
    <div class="property-toolbar rounded-4 mb-4">
        <div class="row g-2 align-items-center">
            <div class="col-12 col-lg-6">
                <label for="propertySearch" class="form-label small text-muted mb-1">Search</label>
                <input id="propertySearch" type="text" class="form-control" placeholder="Search by property name or address">
            </div>
            <div class="col-12 col-md-6 col-lg-3">
                <label for="propertyFilter" class="form-label small text-muted mb-1">Filter</label>
                <select id="propertyFilter" class="form-select">
                    <option value="all">All Properties</option>
                    <option value="vacant">With Vacant Rooms</option>
                    <option value="full">Fully Occupied</option>
                    <option value="unmapped">Map Not Set</option>
                </select>
            </div>
            <div class="col-12 col-md-6 col-lg-3">
                <label for="mapToggleBtn" class="form-label small text-muted mb-1">Property Map</label>
                <button id="mapToggleBtn" class="btn btn-outline-secondary w-100" type="button" data-bs-toggle="collapse" data-bs-target="#propertiesMapWrap" aria-expanded="{{ $mappedProperties > 0 ? 'true' : 'false' }}" aria-controls="propertiesMapWrap">
                    {{ $mappedProperties > 0 ? 'Hide Map' : 'Show Map' }}
                </button>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0 rounded-4 mb-4" id="propertiesMapCard" style="{{ $mappedProperties > 0 ? 'display:block;' : 'display:none;' }}">
        <div id="propertiesMapWrap" class="collapse{{ $mappedProperties > 0 ? ' show' : '' }}">
            <div class="card-body p-0">
                <div class="px-4 py-3 border-bottom small text-muted">Property Locations</div>
                <div id="propertiesMap" style="height:280px;"></div>
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
                    ->map(fn ($key) => $amenityLabels[$key] ?? null)
                    ->filter()
                    ->values();
            @endphp
            <article class="property-card rounded-4 p-3 p-md-4"
                data-name="{{ strtolower($prop->name) }}"
                data-address="{{ strtolower($prop->address) }}"
                data-vacant="{{ $vacant }}"
                data-total="{{ $total }}"
                data-mapped="{{ $isMapped ? '1' : '0' }}">
                <div class="d-flex flex-column flex-xl-row justify-content-between gap-3">
                    <div class="w-100">
                        <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                            <h3 class="h5 mb-0">{{ $prop->name }}</h3>
                            @if($isMapped)
                                <span class="badge text-bg-success">Mapped</span>
                            @else
                                <span class="badge text-bg-warning">Map not set</span>
                            @endif
                        </div>
                        <div class="text-muted small mb-3">{{ $prop->address }}</div>

                        @if($buildingInclusions->isNotEmpty())
                            <div class="d-flex flex-wrap gap-2 mb-3 small">
                                @foreach($buildingInclusions->take(4) as $inclusion)
                                    <span class="chip">{{ $inclusion }}</span>
                                @endforeach
                            </div>
                        @endif

                        <div class="d-flex flex-wrap gap-2 small">
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
                            <span class="chip">Added: <strong>{{ $prop->created_at->diffForHumans() }}</strong></span>
                        </div>

                        <div class="mt-3">
                            <div class="d-flex justify-content-between small text-muted mb-1">
                                <span>Occupancy</span>
                                <span>{{ $occupancyRate }}%</span>
                            </div>
                            <div class="progress" role="progressbar" aria-label="Occupancy" aria-valuenow="{{ $occupancyRate }}" aria-valuemin="0" aria-valuemax="100" style="height: 7px;">
                                <div class="progress-bar bg-success" style="width: {{ $occupancyRate }}%"></div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex flex-nowrap align-items-center gap-2 property-actions justify-content-xl-end align-self-xl-start">
                        <a class="btn btn-sm btn-outline-secondary" href="{{ route('landlord.properties.show', $prop->id) }}">
                            Property Overview
                        </a>
                        <a class="btn btn-sm btn-brand" href="{{ route('landlord.properties.rooms.index', $prop->id) }}">Manage Rooms</a>
                        <a class="btn btn-sm btn-outline-brand" href="{{ route('landlord.properties.rooms.create', $prop->id) }}">Add Room</a>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-secondary btn-icon" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="More actions">
                                <i class="bi bi-three-dots-vertical"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="{{ route('landlord.properties.edit', $prop->id) }}">Edit Property</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form action="{{ route('landlord.properties.destroy', $prop->id) }}" method="POST" onsubmit="return confirm('Delete this property? All rooms and bookings under it will be removed.');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="dropdown-item text-danger" type="submit">Delete Property</button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </article>
        @empty
            <div class="text-center rounded-4 border-2 border-dashed py-5 px-3 bg-white">
                <div class="h5 mb-2">No properties yet</div>
                <p class="text-muted mb-3">Create your first property to start listing rooms.</p>
                <a href="{{ route('landlord.properties.create') }}" class="btn btn-brand rounded-pill px-4">Create Property</a>
            </div>
        @endforelse

        @if($totalProperties > 0)
            <div id="propertyNoMatch" class="text-center rounded-4 border-2 border-dashed py-4 px-3 bg-white d-none">
                <div class="fw-semibold mb-1">No matching property</div>
                <p class="text-muted small mb-0">Try a different search keyword or filter option.</p>
            </div>
        @endif
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin=""/>
<style>
    .portfolio-shell {
        background: linear-gradient(180deg, #ffffff 0%, #fbfdfc 100%);
    }
    .portfolio-metric {
        background: #fff;
        border: 1px solid rgba(2,8,20,.08);
        border-radius: 1rem;
        padding: .95rem 1rem;
    }
    .portfolio-metric-label {
        font-size: .78rem;
        letter-spacing: .04em;
        text-transform: uppercase;
        color: rgba(2,8,20,.55);
    }
    .portfolio-metric-value {
        font-size: 1.45rem;
        font-weight: 700;
        color: #166534;
    }
    .property-card {
        background: #fff;
        border: 1px solid rgba(2,8,20,.08);
        box-shadow: 0 8px 20px rgba(2,8,20,.05);
    }
    .property-toolbar {
        background: #fff;
        border: 1px solid rgba(2,8,20,.08);
        box-shadow: 0 8px 20px rgba(2,8,20,.04);
        padding: .9rem;
    }
    .chip {
        display: inline-flex;
        align-items: center;
        gap: .25rem;
        border: 1px solid rgba(2,8,20,.12);
        background: rgba(248,250,252,.9);
        border-radius: 999px;
        padding: .35rem .7rem;
    }
    .property-actions {
        min-width: 220px;
        white-space: nowrap;
    }
    .btn-icon {
        width: 34px;
        height: 34px;
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
    // Check if any properties have coordinates
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
    
    if (propertiesWithCoords.length > 0) {
        const mapCard = document.getElementById('propertiesMapCard');
        if (!mapCard) return;
        mapCard.style.display = 'block';
        
        const map = L.map('propertiesMap');
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxNativeZoom: 19,
            maxZoom: 22,
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);
        
        const markerLatLngs = [];
        propertiesWithCoords.forEach(property => {
            const lat = Number(property.latitude);
            const lng = Number(property.longitude);
            if (!Number.isFinite(lat) || !Number.isFinite(lng)) {
                return;
            }

            const latLng = [lat, lng];
            const marker = L.marker(latLng).addTo(map);
            marker.bindPopup(`
                <strong>${property.name}</strong><br>
                ${property.address}<br>
                <span class="badge bg-primary">${property.rooms_vacant_live} vacant / ${property.rooms_total_live} total rooms</span><br>
                <a href="/landlord/properties/${property.id}" class="btn btn-sm btn-brand mt-2">View Details</a>
            `);
            markerLatLngs.push(latLng);
        });

        const fitMapToProperties = () => {
            if (markerLatLngs.length === 0) return;

            if (markerLatLngs.length === 1) {
                map.setView(markerLatLngs[0], 16);
                return;
            }

            const bounds = L.latLngBounds(markerLatLngs);
            map.fitBounds(bounds.pad(0.2), { padding: [20, 20] });
        };

        fitMapToProperties();

        if (mapWrap && mapWrap.classList.contains('show')) {
            setTimeout(() => {
                map.invalidateSize();
                fitMapToProperties();
            }, 170);
        }

        if (mapWrap && mapToggleBtn) {
            mapWrap.addEventListener('shown.bs.collapse', () => {
                mapToggleBtn.textContent = 'Hide Map';
                setTimeout(() => {
                    map.invalidateSize();
                    fitMapToProperties();
                }, 170);
            });
            mapWrap.addEventListener('hidden.bs.collapse', () => {
                mapToggleBtn.textContent = 'Show Map';
            });
        }
    } else if (mapToggleBtn) {
        mapToggleBtn.disabled = true;
        mapToggleBtn.textContent = 'Map Unavailable';
    }
});
</script>
@endpush