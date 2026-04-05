@extends('layouts.admin')

@section('title', 'Properties Management - Admin Panel')

@section('content')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="" />

<style>
    .admin-properties-shell {
        background: linear-gradient(180deg, #ffffff 0%, #fbfdfc 100%);
        border: 1px solid rgba(2, 8, 20, .08);
        border-radius: 1.25rem;
        box-shadow: 0 10px 26px rgba(2, 8, 20, .06);
        padding: 1.25rem;
    }

    .section-muted {
        color: rgba(2, 8, 20, .58);
    }

    .admin-metric {
        border: 1px solid rgba(2, 8, 20, .08);
        border-radius: 1rem;
        background: #fff;
        box-shadow: 0 6px 16px rgba(2, 8, 20, .04);
        padding: .95rem 1rem;
        height: 100%;
    }

    .admin-metric-label {
        font-size: .78rem;
        text-transform: uppercase;
        letter-spacing: .04em;
        color: rgba(2, 8, 20, .55);
    }

    .admin-metric-value {
        font-size: 1.45rem;
        font-weight: 700;
        color: #166534;
    }

    .admin-map-card,
    .admin-table-card {
        border: 1px solid rgba(2, 8, 20, .08);
        border-radius: 1rem;
        background: #fff;
        box-shadow: 0 8px 20px rgba(2, 8, 20, .05);
        overflow: hidden;
    }

    .admin-card-header {
        padding: .85rem 1rem;
        border-bottom: 1px solid rgba(2, 8, 20, .08);
        background: #fff;
    }

    .admin-map {
        width: 100%;
        height: 390px;
    }

    .price-marker {
        background: #0f5132;
        color: #fff;
        border-radius: 999px;
        border: 2px solid #fff;
        font-size: .72rem;
        font-weight: 700;
        letter-spacing: .01em;
        padding: .2rem .55rem;
        white-space: nowrap;
        line-height: 1;
        box-shadow: 0 10px 18px rgba(2, 8, 20, .28);
    }

    .price-marker.price-marker-empty {
        background: #334155;
    }

    .leaflet-popup-content-wrapper {
        border-radius: .95rem;
    }

    .map-popup {
        min-width: 240px;
        max-width: 280px;
    }

    .map-popup-photo {
        width: 100%;
        height: 112px;
        border-radius: .7rem;
        background: linear-gradient(135deg, #d1fae5 0%, #ecfeff 100%);
        overflow: hidden;
        margin-bottom: .55rem;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #0f766e;
    }

    .map-popup-photo img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .map-popup-title {
        font-weight: 700;
        line-height: 1.2;
        margin-bottom: .2rem;
    }

    .map-popup-address {
        color: rgba(2, 8, 20, .65);
        font-size: .78rem;
        margin-bottom: .35rem;
    }

    .map-popup-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .6rem;
        margin-bottom: .45rem;
    }

    .map-popup-price {
        font-size: .88rem;
        font-weight: 700;
        color: #0f5132;
    }

    .map-popup-distance {
        font-size: .72rem;
        color: rgba(2, 8, 20, .72);
    }

    .property-avatar {
        width: 42px;
        height: 42px;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(22, 101, 52, .12);
        color: #166534;
        border: 1px solid rgba(22, 101, 52, .22);
        flex-shrink: 0;
    }

    .table thead th {
        font-size: .78rem;
        text-transform: uppercase;
        letter-spacing: .05em;
        color: rgba(2, 8, 20, .62);
        background: rgba(248, 250, 252, .96);
        border-bottom: 1px solid rgba(2, 8, 20, .08);
    }

    .table tbody td {
        vertical-align: middle;
    }

    .occupancy-track {
        height: 7px;
        width: 88px;
        background: rgba(2, 8, 20, .10);
        border-radius: 999px;
        overflow: hidden;
    }

    .occupancy-fill {
        height: 100%;
    }

    .toolbar {
        width: 320px;
        max-width: 100%;
    }

    .table-empty {
        padding: 3rem 1rem;
        text-align: center;
        color: rgba(2, 8, 20, .58);
    }

    @media (max-width: 767.98px) {
        .admin-properties-shell {
            padding: .95rem;
        }
        .admin-map {
            height: 320px;
        }
    }
</style>

<div class="admin-properties-shell">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <div class="text-uppercase small section-muted fw-semibold">Management</div>
            <h1 class="h3 mb-1">Properties Management</h1>
            <p class="section-muted mb-0">Overview of all landlord properties and their locations.</p>
        </div>
        <!-- <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary rounded-pill px-3">
            Back to Dashboard
        </a> -->
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="admin-metric">
                <div class="admin-metric-label">Total Properties</div>
                <div class="admin-metric-value">{{ number_format($totalProperties) }}</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="admin-metric">
                <div class="admin-metric-label">Active Landlords</div>
                <div class="admin-metric-value">{{ number_format($totalLandlords) }}</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="admin-metric">
                <div class="admin-metric-label">Occupied Rooms</div>
                <div class="admin-metric-value">{{ number_format($occupiedRooms) }}/{{ number_format($totalRooms) }}</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="admin-metric">
                <div class="admin-metric-label">Available Rooms</div>
                <div class="admin-metric-value">{{ number_format($availableRooms) }}</div>
            </div>
        </div>
    </div>

    <div class="admin-map-card mb-4">
        <div class="admin-card-header fw-semibold">
            <i class="bi bi-geo-alt me-1"></i> Properties Location Map
        </div>
        <div id="properties-map" class="admin-map"></div>
    </div>

    <div class="admin-table-card">
        <div class="admin-card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div class="fw-semibold"><i class="bi bi-list-ul me-1"></i> All Properties</div>
            <div class="input-group toolbar">
                <input type="text" class="form-control" id="searchInput" placeholder="Search properties...">
                <button class="btn btn-outline-secondary" type="button" id="searchBtn">
                    <i class="bi bi-search"></i>
                </button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover mb-0" id="propertiesTable">
                <thead>
                    <tr>
                        <th class="ps-3">Property Name</th>
                        <th>Landlord</th>
                        <th>Location</th>
                        <th>Rooms</th>
                        <th>Occupancy</th>
                        <th>Approval</th>
                        <th class="pe-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($properties as $property)
                        <tr>
                            <td class="ps-3">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="property-avatar">
                                        <i class="bi bi-building"></i>
                                    </div>
                                    <div>
                                        <div class="fw-semibold">{{ $property->name }}</div>
                                        <div class="small section-muted">Added {{ $property->created_at->format('M d, Y') }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div>{{ $property->landlord->full_name }}</div>
                                <div class="small section-muted">{{ $property->landlord->email }}</div>
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $property->address }}</div>
                                @if($property->latitude && $property->longitude)
                                    <div class="small section-muted">
                                        <i class="bi bi-geo me-1"></i>{{ number_format($property->latitude, 6) }}, {{ number_format($property->longitude, 6) }}
                                    </div>
                                @else
                                    <div class="small section-muted">
                                        <i class="bi bi-exclamation-triangle me-1"></i>Location not set
                                    </div>
                                @endif
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $property->occupied_rooms }}/{{ $property->total_rooms }}</div>
                                <div class="small section-muted">{{ $property->available_rooms }} available</div>
                            </td>
                            <td>
                                <div class="occupancy-track">
                                    <div class="occupancy-fill {{ $property->occupancy_rate >= 80 ? 'bg-danger' : ($property->occupancy_rate >= 60 ? 'bg-warning' : 'bg-success') }}" style="width: {{ $property->occupancy_rate }}%"></div>
                                </div>
                                <div class="small section-muted mt-1">{{ $property->occupancy_rate }}%</div>
                            </td>
                            <td>
                                @if(($property->approval_status ?? 'pending') === 'approved')
                                    <span class="badge text-bg-success">Approved</span>
                                @elseif(($property->approval_status ?? 'pending') === 'rejected')
                                    <span class="badge text-bg-danger">Rejected</span>
                                    @if(!empty($property->rejection_reason))
                                        <div class="small section-muted mt-1">{{ $property->rejection_reason }}</div>
                                    @endif
                                @else
                                    <span class="badge text-bg-warning">Pending</span>
                                @endif
                            </td>
                            <td class="pe-3">
                                <div class="d-flex flex-wrap gap-1">
                                    <a href="{{ route('admin.properties.show', $property) }}" class="btn btn-sm btn-outline-secondary" title="View property details">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <button class="btn btn-sm btn-outline-secondary" title="View on map" onclick="focusProperty({{ $property->id }}, {{ $property->latitude ?? 0 }}, {{ $property->longitude ?? 0 }})">
                                        <i class="bi bi-geo-alt"></i>
                                    </button>
                                    <a href="{{ route('admin.users.landlords.show', $property->landlord_id) }}" class="btn btn-sm btn-outline-secondary" title="View landlord">
                                        <i class="bi bi-person"></i>
                                    </a>
                                    @if(($property->approval_status ?? 'pending') === 'pending')
                                        <form method="POST" action="{{ route('admin.properties.approve', $property) }}" class="d-inline">
                                            @csrf
                                            <button class="btn btn-sm btn-outline-success" title="Approve">
                                                <i class="bi bi-check2"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="table-empty">
                                <div class="h6 mb-1">No Properties Found</div>
                                <div>No properties have been added to the system yet.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const map = L.map('properties-map').setView([14.5995, 120.9842], 10);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxNativeZoom: 19,
        maxZoom: 22,
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    const markers = [];
    const propertiesData = @json($properties);

    const escapeHtml = function(value) {
        return String(value ?? '')
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    };

    const formatPriceLabel = function(minPrice, maxPrice) {
        const normalizedMin = minPrice !== null && minPrice !== undefined && minPrice !== '' ? Number(minPrice) : null;
        const normalizedMax = maxPrice !== null && maxPrice !== undefined && maxPrice !== '' ? Number(maxPrice) : null;

        if (normalizedMin === null && normalizedMax === null) return 'Price TBD';
        if (normalizedMin !== null && normalizedMax !== null && normalizedMin !== normalizedMax) {
            return `PHP ${normalizedMin.toLocaleString()}-${normalizedMax.toLocaleString()}`;
        }
        const single = normalizedMin !== null ? normalizedMin : normalizedMax;
        return `PHP ${single.toLocaleString()}`;
    };

    const buildPopupHtml = function(property) {
        const imageHtml = property.image_path
            ? `<img src="/storage/${escapeHtml(property.image_path)}" alt="${escapeHtml(property.name)} preview">`
            : '<i class="bi bi-building fs-3"></i>';
        const availableRooms = Number(property.available_rooms || 0);
        const priceText = formatPriceLabel(property.price_min, property.price_max);

        return `
            <div class="map-popup">
                <div class="map-popup-photo">${imageHtml}</div>
                <div class="map-popup-title">${escapeHtml(property.name)}</div>
                <div class="map-popup-address">${escapeHtml(property.address || 'Address not available')}</div>
                <div class="map-popup-row">
                    <span class="badge text-bg-light">${escapeHtml(String(availableRooms))} room(s) available</span>
                    <span class="map-popup-price">${escapeHtml(priceText)}</span>
                </div>
                <div class="map-popup-distance">${escapeHtml(property.landlord?.full_name || 'Landlord')} • ${escapeHtml(String(property.occupancy_rate || 0))}% occupancy</div>
                <a href="/admin/properties/${property.id}" class="btn btn-sm btn-success mt-2 w-100">View Details</a>
            </div>
        `;
    };

    propertiesData.forEach(function(property) {
        if (property.latitude && property.longitude) {
            const markerPrice = formatPriceLabel(property.price_min, property.price_max);
            const markerWidth = Math.max(72, Math.min(168, Math.round(markerPrice.length * 7.2 + 22)));
            const markerIcon = L.divIcon({
                className: 'price-marker-wrap',
                html: `<div class="price-marker ${(property.price_min === null || property.price_min === undefined || property.price_min === '') && (property.price_max === null || property.price_max === undefined || property.price_max === '') ? 'price-marker-empty' : ''}">${escapeHtml(markerPrice)}</div>`,
                iconSize: [markerWidth, 28],
                iconAnchor: [Math.round(markerWidth / 2), 14],
                popupAnchor: [0, -10],
            });

            const marker = L.marker([property.latitude, property.longitude], { icon: markerIcon })
                .addTo(map)
                .bindPopup(buildPopupHtml(property));

            markers.push({
                id: property.id,
                marker: marker,
                lat: property.latitude,
                lng: property.longitude
            });
        }
    });

    if (markers.length > 0) {
        const group = new L.featureGroup(markers.map(m => m.marker));
        map.fitBounds(group.getBounds().pad(0.1));
    }

    window.focusProperty = function(propertyId, lat, lng) {
        if (lat && lng) {
            map.setView([lat, lng], 15);
            const markerData = markers.find(m => m.id === propertyId);
            if (markerData) {
                markerData.marker.openPopup();
            }
        }
    };

    const searchInput = document.getElementById('searchInput');
    const searchBtn = document.getElementById('searchBtn');
    const table = document.getElementById('propertiesTable');
    const body = table ? table.getElementsByTagName('tbody')[0] : null;
    const rows = body ? body.getElementsByTagName('tr') : [];

    function filterTable() {
        if (!searchInput) return;
        const filter = searchInput.value.toLowerCase();
        for (let i = 0; i < rows.length; i++) {
            const cells = rows[i].getElementsByTagName('td');
            let found = false;
            for (let j = 0; j < cells.length; j++) {
                if (cells[j].textContent.toLowerCase().indexOf(filter) > -1) {
                    found = true;
                    break;
                }
            }
            rows[i].style.display = found ? '' : 'none';
        }
    }

    if (searchInput) {
        searchInput.addEventListener('keyup', filterTable);
    }
    if (searchBtn) {
        searchBtn.addEventListener('click', filterTable);
    }
});
</script>
@endsection