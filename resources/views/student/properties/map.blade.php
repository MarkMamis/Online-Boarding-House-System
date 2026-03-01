@extends('layouts.student_dashboard')

@section('title', 'Property Map')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin=""/>
@endpush

@section('content')
<div class="glass-card rounded-4 p-4 p-md-5 mb-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="fw-semibold mb-0">Property Map</h4>
    </div>

    <div class="mb-4">
        <div id="propertiesMap" data-map-url="{{ route('student.properties.map') }}" style="height:360px; border-radius:1rem; overflow:hidden; display:none;"></div>
    </div>

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end mb-3 gap-3">
        <h5 class="fw-semibold mb-0">Boarding Houses</h5>
        <form method="GET" action="{{ route('student.properties.map_view') }}" class="d-flex flex-wrap gap-2">
            <input type="hidden" name="min_price" value="{{ $minPrice }}">
            <input type="hidden" name="max_price" value="{{ $maxPrice }}">
            <input type="hidden" name="capacity" value="{{ $minCapacity }}">
            <div style="flex: 1 1 240px; min-width: 200px;">
                <input type="text" name="q" class="form-control form-control-sm" placeholder="Search name or address" value="{{ request('q') }}">
            </div>
            <button class="btn btn-sm btn-brand" type="submit">Search</button>
            <a href="{{ route('student.properties.map_view') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
        </form>
    </div>

    <div class="row g-3">
        @forelse($allProperties as $prop)
            <div class="col-12 col-md-6 col-lg-4">
                <div class="border rounded-4 bg-white shadow-sm p-3 h-100 d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-start mb-1">
                        <div class="fw-semibold">{{ $prop->name }}</div>
                        <span class="badge text-bg-light">{{ $prop->rooms_available_live }} / {{ $prop->rooms_total_live }} available</span>
                    </div>
                    <div class="small text-muted mb-1">By: {{ $prop->landlord->full_name ?? 'Landlord' }}</div>
                    <div class="small mb-2">{{ \Illuminate\Support\Str::limit($prop->description, 90) ?: 'No description provided.' }}</div>
                    @if($prop->price_min !== null || $prop->price_max !== null)
                        <div class="small mb-2">Price Range: ₱{{ number_format($prop->price_min,0) }} - ₱{{ number_format($prop->price_max,0) }}</div>
                    @endif
                    <div class="mt-auto d-flex justify-content-between align-items-center">
                        <div class="small text-muted">Added {{ $prop->created_at->diffForHumans() }}</div>
                        <a class="btn btn-sm btn-brand" href="{{ route('student.rooms.index') }}?property_id={{ $prop->id }}&property_name={{ urlencode($prop->name) }}">View Rooms</a>
                    </div>
                </div>
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
        const mapUrl = mapEl ? mapEl.dataset.mapUrl : null;
        if (!mapEl || !mapUrl) return;

        fetch(mapUrl)
            .then(r => r.json())
            .then(data => {
                const props = data.properties || [];
                if (!props.length) {
                    return;
                }

                mapEl.style.display = 'block';
                const map = L.map('propertiesMap');
                const bounds = [];
                const markers = [];
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

                props.forEach(p => {
                    const marker = L.marker([p.lat, p.lng]).addTo(map);
                    marker._propData = p;
                    marker.bindPopup(
                        `<strong>${escapeHtml(p.name)}</strong><br>${escapeHtml(p.address)}<br>Available Rooms: ${escapeHtml(p.available_rooms)}` +
                        `<div class='distance-info'></div>` +
                        `<a class='btn btn-sm btn-brand mt-2' href='${roomsBaseUrl}?property_id=${encodeURIComponent(p.id)}&property_name=${encodeURIComponent(p.name || '')}'>View Rooms</a>`
                    );
                    bounds.push([p.lat, p.lng]);
                    markers.push(marker);
                });

                if (bounds.length) {
                    map.fitBounds(bounds, { padding: [24, 24] });
                }

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
                        const popupEl = m.getPopup().getContent();
                        m.setPopupContent(
                            popupEl.replace(
                                '<div class=\'distance-info\'></div>',
                                `<div class='distance-info mt-1'><span class='badge text-bg-light'>${km} km away</span></div>`
                            )
                        );
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
