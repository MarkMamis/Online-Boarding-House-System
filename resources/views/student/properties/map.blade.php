@extends('layouts.student_dashboard')

@section('title', 'Property Map')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin=""/>
<style>
    .lookup-shell {
        background: linear-gradient(180deg, #ffffff 0%, #fbfdfc 100%);
        border: 1px solid rgba(2,8,20,.08);
        border-radius: 1.25rem;
        box-shadow: 0 10px 26px rgba(2,8,20,.06);
        padding: 1.25rem;
    }
    .lookup-summary {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: .75rem;
    }
    .lookup-summary-item {
        border: 1px solid rgba(20,83,45,.16);
        background: linear-gradient(180deg, rgba(167,243,208,.18), rgba(255,255,255,.85));
        border-radius: .9rem;
        padding: .7rem .8rem;
    }
    .lookup-summary-label {
        font-size: .72rem;
        text-transform: uppercase;
        letter-spacing: .05em;
        color: rgba(2,8,20,.55);
        font-weight: 700;
        margin-bottom: .2rem;
    }
    .lookup-summary-value {
        font-size: .96rem;
        font-weight: 700;
        color: #14532d;
    }
    .lookup-toolbar {
        border: 1px solid rgba(2,8,20,.09);
        border-radius: .95rem;
        background: #ffffff;
        padding: .85rem;
    }
    .map-stage {
        border: 1px solid rgba(2,8,20,.09);
        border-radius: 1rem;
        overflow: hidden;
        background: #f8fafc;
        box-shadow: 0 10px 24px rgba(2,8,20,.06);
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
        box-shadow: 0 10px 18px rgba(2,8,20,.28);
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
        color: rgba(2,8,20,.65);
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
        color: rgba(2,8,20,.72);
    }
    .map-empty {
        min-height: 320px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        color: rgba(2,8,20,.55);
        font-size: .88rem;
        gap: .3rem;
    }
    .property-grid-card {
        border: 1px solid rgba(2,8,20,.09);
        border-radius: 1rem;
        background: #fff;
        padding: .85rem;
        box-shadow: 0 8px 20px rgba(2,8,20,.05);
        height: 100%;
        display: flex;
        flex-direction: column;
        gap: .45rem;
    }
    .property-grid-card:hover {
        border-color: rgba(20,83,45,.25);
        box-shadow: 0 12px 24px rgba(2,8,20,.08);
    }
    .property-name {
        font-weight: 700;
        color: #0f172a;
        line-height: 1.2;
    }
    .meta-chip {
        display: inline-flex;
        align-items: center;
        gap: .3rem;
        border: 1px solid rgba(2,8,20,.12);
        border-radius: 999px;
        background: #f8fafc;
        color: #0f172a;
        padding: .18rem .55rem;
        font-size: .75rem;
        font-weight: 600;
    }
    @media (max-width: 991.98px) {
        .lookup-summary {
            grid-template-columns: 1fr;
        }
        .lookup-shell {
            padding: .95rem;
        }
    }
</style>
@endpush

@section('content')
<div class="lookup-shell mb-4">
    @php
        $totalProperties = (int) $allProperties->count();
        $totalAvailableRooms = (int) $allProperties->sum('rooms_available_live');
    @endphp

    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <div class="text-uppercase small text-muted fw-semibold">Booking Lookup</div>
            <h1 class="h3 mb-1">Property Map</h1>
            <div class="text-muted small">Find nearby boarding houses and check live room availability.</div>
        </div>
    </div>

    <div class="lookup-summary mb-4">
        <div class="lookup-summary-item">
            <div class="lookup-summary-label">Properties</div>
            <div class="lookup-summary-value">{{ $totalProperties }}</div>
        </div>
        <div class="lookup-summary-item">
            <div class="lookup-summary-label">Available Rooms</div>
            <div class="lookup-summary-value">{{ $totalAvailableRooms }}</div>
        </div>
        <div class="lookup-summary-item">
            <div class="lookup-summary-label">Search Scope</div>
            <div class="lookup-summary-value">Name or Address</div>
        </div>
    </div>

    <form method="GET" action="{{ route('student.properties.map_view') }}" class="lookup-toolbar mb-4">
        <input type="hidden" name="min_price" value="{{ $minPrice }}">
        <input type="hidden" name="max_price" value="{{ $maxPrice }}">
        <input type="hidden" name="capacity" value="{{ $minCapacity }}">
        <div class="row g-2 align-items-center">
            <div class="col-12 col-lg-9">
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                    <input type="text" name="q" class="form-control" placeholder="Search by property name or address" value="{{ request('q') }}">
                </div>
            </div>
            <div class="col-12 col-lg-3 d-flex gap-2">
                <button class="btn btn-brand flex-fill" type="submit">Search</button>
                <a href="{{ route('student.properties.map_view') }}" class="btn btn-outline-secondary">Reset</a>
            </div>
        </div>
    </form>

    <div class="map-stage mb-4">
        <div id="propertiesMap" data-map-url="{{ route('student.properties.map') }}" style="height:360px; display:none;"></div>
        <div id="propertiesMapEmpty" class="map-empty">
            <i class="bi bi-map fs-3"></i>
            <div>Loading property locations...</div>
        </div>
    </div>

    <div class="d-flex align-items-center justify-content-between mb-3">
        <h5 class="fw-semibold mb-0">Boarding Houses</h5>
        <div class="small text-muted">{{ $totalProperties }} result(s)</div>
    </div>

    <div class="row g-3">
        @forelse($allProperties as $prop)
            <div class="col-12 col-md-6 col-xl-4">
                <article class="property-grid-card">
                    <div class="d-flex justify-content-between align-items-start gap-2">
                        <div class="property-name">{{ $prop->name }}</div>
                        <span class="badge rounded-pill text-bg-light border">{{ $prop->rooms_available_live }} / {{ $prop->rooms_total_live }} available</span>
                    </div>

                    <div class="small text-muted">By {{ $prop->landlord->full_name ?? 'Landlord' }}</div>
                    <div class="small text-muted">{{ $prop->address }}</div>

                    <div class="small">{{ \Illuminate\Support\Str::limit($prop->description, 95) ?: 'No description provided.' }}</div>

                    <div class="d-flex flex-wrap gap-2 mt-1">
                        @if($prop->price_min !== null || $prop->price_max !== null)
                            <span class="meta-chip"><i class="bi bi-cash"></i>₱{{ number_format($prop->price_min,0) }} - ₱{{ number_format($prop->price_max,0) }}</span>
                        @endif
                        <span class="meta-chip"><i class="bi bi-clock-history"></i>{{ $prop->created_at->diffForHumans() }}</span>
                    </div>

                    <div class="mt-auto pt-2 d-flex justify-content-end">
                        <a class="btn btn-sm btn-brand rounded-pill px-3" href="{{ route('student.rooms.index') }}?property_id={{ $prop->id }}&property_name={{ urlencode($prop->name) }}">View Rooms</a>
                    </div>
                </article>
            </div>
        @empty
            <div class="col-12"><div class="alert alert-secondary mb-0">No boarding houses found yet.</div></div>
        @endforelse
    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const mapEl = document.getElementById('propertiesMap');
        const baseMapUrl = mapEl ? mapEl.dataset.mapUrl : null;
        const searchParams = new URLSearchParams(window.location.search || '');
        const mapUrl = baseMapUrl ? `${baseMapUrl}${searchParams.toString() ? `?${searchParams.toString()}` : ''}` : null;
        if (!mapEl || !mapUrl) return;

        fetch(mapUrl)
            .then(r => r.json())
            .then(data => {
                const props = data.properties || [];
                if (!props.length) {
                    const emptyEl = document.getElementById('propertiesMapEmpty');
                    if (emptyEl) {
                        emptyEl.innerHTML = `<i class="bi bi-map fs-3"></i><div>No map points available for your current filters.</div>`;
                    }
                    return;
                }

                mapEl.style.display = 'block';
                const emptyEl = document.getElementById('propertiesMapEmpty');
                if (emptyEl) emptyEl.style.display = 'none';
                const map = L.map('propertiesMap');
                const bounds = [];
                const markers = [];
                const markerEntries = [];
                const roomsBaseUrl = @json(route('student.rooms.index'));

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; OpenStreetMap contributors'
                }).addTo(map);

                const escapeHtml = (value) => {
                    return String(value ?? '')
                        .replaceAll('&', '&amp;')
                        .replaceAll('<', '&lt;')
                        .replaceAll('>', '&gt;')
                        .replaceAll('"', '&quot;')
                        .replaceAll("'", '&#039;');
                };

                const formatPriceLabel = (minPrice, maxPrice) => {
                    if (minPrice === null && maxPrice === null) return 'Price TBD';
                    if (minPrice !== null && maxPrice !== null && Number(minPrice) !== Number(maxPrice)) {
                        return `₱${Number(minPrice).toLocaleString()}-₱${Number(maxPrice).toLocaleString()}`;
                    }
                    const single = minPrice !== null ? minPrice : maxPrice;
                    return `₱${Number(single).toLocaleString()}`;
                };

                const buildPopupHtml = (p, distanceText = '') => {
                    const imageHtml = p.image_url
                        ? `<img src="${escapeHtml(p.image_url)}" alt="${escapeHtml(p.name)} preview">`
                        : `<i class="bi bi-building fs-3"></i>`;
                    const distanceHtml = distanceText
                        ? `<div class="map-popup-distance">${escapeHtml(distanceText)} away</div>`
                        : `<div class="map-popup-distance">Distance unavailable</div>`;

                    return `
                        <div class="map-popup">
                            <div class="map-popup-photo">${imageHtml}</div>
                            <div class="map-popup-title">${escapeHtml(p.name)}</div>
                            <div class="map-popup-address">${escapeHtml(p.address || 'Address not available')}</div>
                            <div class="map-popup-row">
                                <span class="badge text-bg-light">${escapeHtml(String(p.available_rooms || 0))} room(s) available</span>
                                <span class="map-popup-price">${escapeHtml(formatPriceLabel(p.price_min, p.price_max))}</span>
                            </div>
                            ${distanceHtml}
                            <a class='btn btn-sm btn-brand mt-2 w-100' href='${roomsBaseUrl}?property_id=${encodeURIComponent(p.id)}&property_name=${encodeURIComponent(p.name || '')}'>View Rooms</a>
                        </div>
                    `;
                };

                // Spread close markers in screen space so price tags remain readable.
                const distributeNearbyMarkers = () => {
                    const placed = [];
                    const yThreshold = 32;
                    const candidateOffsets = [
                        [0, 0],
                        [0, -34], [0, 34],
                        [26, 0], [-26, 0],
                        [26, -34], [-26, -34], [26, 34], [-26, 34],
                        [0, -68], [0, 68],
                        [52, 0], [-52, 0],
                        [52, -34], [-52, -34], [52, 34], [-52, 34],
                    ];

                    markerEntries
                        .map(entry => ({
                            ...entry,
                            basePoint: map.latLngToLayerPoint(entry.baseLatLng),
                        }))
                        .sort((a, b) => (a.basePoint.y - b.basePoint.y) || (a.basePoint.x - b.basePoint.x))
                        .forEach(entry => {
                            let chosen = L.point(0, 0);

                            for (const [ox, oy] of candidateOffsets) {
                                const candidate = entry.basePoint.add(L.point(ox, oy));
                                const collides = placed.some(p => (
                                    Math.abs(p.x - candidate.x) < ((p.w + entry.w) / 2 + 10)
                                    && Math.abs(p.y - candidate.y) < yThreshold
                                ));

                                if (!collides) {
                                    chosen = L.point(ox, oy);
                                    break;
                                }
                            }

                            const adjustedPoint = entry.basePoint.add(chosen);
                            entry.marker.setLatLng(map.layerPointToLatLng(adjustedPoint));
                            placed.push({
                                x: adjustedPoint.x,
                                y: adjustedPoint.y,
                                w: entry.w,
                            });
                        });
                };

                props.forEach(p => {
                    const markerPrice = formatPriceLabel(p.price_min, p.price_max);
                    const markerWidth = Math.max(72, Math.min(168, Math.round(markerPrice.length * 7.2 + 22)));
                    const markerIcon = L.divIcon({
                        className: 'price-marker-wrap',
                        html: `<div class="price-marker ${p.price_min === null && p.price_max === null ? 'price-marker-empty' : ''}">${escapeHtml(markerPrice)}</div>`,
                        iconSize: [markerWidth, 28],
                        iconAnchor: [Math.round(markerWidth / 2), 14],
                        popupAnchor: [0, -10],
                    });

                    const marker = L.marker([p.lat, p.lng], { icon: markerIcon }).addTo(map);
                    marker._propData = p;
                    marker.bindPopup(buildPopupHtml(p));
                    bounds.push([p.lat, p.lng]);
                    markers.push(marker);
                    markerEntries.push({
                        marker,
                        baseLatLng: L.latLng(p.lat, p.lng),
                        w: markerWidth,
                    });
                });

                if (bounds.length) {
                    map.fitBounds(bounds, { padding: [24, 24] });
                }

                distributeNearbyMarkers();
                map.on('zoomend moveend', distributeNearbyMarkers);

                const onUserLocation = (latitude, longitude) => {
                    const userLatLng = [latitude, longitude];
                    const userMarker = L.circleMarker(userLatLng, { radius: 8, color: '#166534', fillColor: '#166534', fillOpacity: 0.85 }).addTo(map);
                    userMarker.bindPopup('<strong>You are here</strong>');

                    bounds.push(userLatLng);
                    map.fitBounds(bounds, { padding: [24, 24] });

                    markers.forEach(m => {
                        const p = m._propData;
                        const distMeters = map.distance(userLatLng, [p.lat, p.lng]);
                        const km = (distMeters / 1000).toFixed(2);
                        m.setPopupContent(buildPopupHtml(p, `${km} km`));
                    });
                };

                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(
                        pos => onUserLocation(pos.coords.latitude, pos.coords.longitude),
                        () => {},
                        { enableHighAccuracy: true, timeout: 10000 }
                    );
                }
            })
            .catch(() => {});
    });
</script>
@endpush
