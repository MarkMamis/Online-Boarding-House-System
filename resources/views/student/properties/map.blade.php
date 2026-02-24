<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Property Map - Student Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin=""/>
    <style>
        body { font-family: 'Inter', system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif; background:#f1f5f9; }
        #propertiesMap { height: calc(100vh - 76px); border-radius: 0; }
        .map-controls { position: absolute; top: 20px; right: 20px; z-index: 1000; }
        .property-card { background: white; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .leaflet-popup-content-wrapper { border-radius: 8px; }
        .leaflet-popup-content { margin: 0; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="{{ route('student.dashboard') }}">Student Portal</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav" aria-controls="nav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="nav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a href="{{ route('student.dashboard') }}" class="nav-link">Dashboard</a></li>
                    <li class="nav-item"><a href="{{ route('student.rooms.index') }}" class="nav-link">Browse Rooms</a></li>
                    <li class="nav-item"><a href="{{ route('student.properties.map_view') }}" class="nav-link active">Map View</a></li>
                    <li class="nav-item"><a href="{{ route('messages.index') }}" class="btn btn-sm btn-outline-light ms-2">Messages</a></li>
                    <li class="nav-item ms-2">
                        <form method="POST" action="{{ route('logout') }}" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-light">Logout</button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div id="propertiesMap"></div>

    <!-- Rooms Modal (stay on page) -->
    <div class="modal fade" id="propertyRoomsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="propertyRoomsModalTitle">Rooms</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="propertyRoomsModalMeta" class="small text-muted mb-2"></div>
                    <div id="propertyRoomsModalLoading" class="text-muted">Loading rooms…</div>
                    <div id="propertyRoomsModalError" class="alert alert-danger d-none"></div>
                    <div id="propertyRoomsModalEmpty" class="alert alert-light border d-none mb-0">No rooms found for this property.</div>
                    <div id="propertyRoomsModalList" class="list-group d-none"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="map-controls">
        <div class="btn-group-vertical" role="group">
            <button id="locateBtn" class="btn btn-primary btn-sm" title="Find my location">
                <i class="fas fa-crosshairs"></i>
            </button>
            <button id="refreshBtn" class="btn btn-secondary btn-sm" title="Refresh properties">
                <i class="fas fa-sync-alt"></i>
            </button>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const map = L.map('propertiesMap');
            let userMarker = null;
            let userLatLng = null;
            let markers = [];

            const escapeHtml = (value) => {
                return String(value ?? '')
                    .replaceAll('&', '&amp;')
                    .replaceAll('<', '&lt;')
                    .replaceAll('>', '&gt;')
                    .replaceAll('"', '&quot;')
                    .replaceAll("'", '&#039;');
            };

            const roomsDataBaseUrl = @json(url('/student/properties'));

            const roomsModalEl = document.getElementById('propertyRoomsModal');
            const roomsModalTitleEl = document.getElementById('propertyRoomsModalTitle');
            const roomsModalMetaEl = document.getElementById('propertyRoomsModalMeta');
            const roomsModalLoadingEl = document.getElementById('propertyRoomsModalLoading');
            const roomsModalErrorEl = document.getElementById('propertyRoomsModalError');
            const roomsModalEmptyEl = document.getElementById('propertyRoomsModalEmpty');
            const roomsModalListEl = document.getElementById('propertyRoomsModalList');

            const showRoomsModal = async (propertyId) => {
                if (!roomsModalEl) return;

                if (roomsModalTitleEl) roomsModalTitleEl.textContent = 'Rooms';
                if (roomsModalMetaEl) roomsModalMetaEl.textContent = '';
                if (roomsModalErrorEl) {
                    roomsModalErrorEl.classList.add('d-none');
                    roomsModalErrorEl.textContent = '';
                }
                if (roomsModalEmptyEl) roomsModalEmptyEl.classList.add('d-none');
                if (roomsModalListEl) {
                    roomsModalListEl.classList.add('d-none');
                    roomsModalListEl.innerHTML = '';
                }
                if (roomsModalLoadingEl) roomsModalLoadingEl.classList.remove('d-none');

                bootstrap.Modal.getOrCreateInstance(roomsModalEl).show();

                try {
                    const url = `${roomsDataBaseUrl}/${encodeURIComponent(propertyId)}/rooms-data`;
                    const resp = await fetch(url, { headers: { 'Accept': 'application/json' } });
                    if (!resp.ok) throw new Error('Failed to load rooms');

                    const data = await resp.json();
                    const prop = data.property || {};
                    const rooms = Array.isArray(data.rooms) ? data.rooms : [];

                    if (roomsModalTitleEl) roomsModalTitleEl.textContent = prop.name ? `Rooms — ${prop.name}` : 'Rooms';
                    if (roomsModalMetaEl) roomsModalMetaEl.textContent = prop.address ? prop.address : '';

                    if (roomsModalLoadingEl) roomsModalLoadingEl.classList.add('d-none');

                    if (!rooms.length) {
                        if (roomsModalEmptyEl) roomsModalEmptyEl.classList.remove('d-none');
                        return;
                    }

                    if (roomsModalListEl) {
                        roomsModalListEl.classList.remove('d-none');
                        roomsModalListEl.innerHTML = rooms.map(r => {
                            const status = (r.status || '').toString();
                            const badgeClass = status === 'available' ? 'bg-success' : (status === 'occupied' ? 'bg-secondary' : 'bg-warning');
                            const price = typeof r.price === 'number' ? r.price : Number(r.price || 0);
                            const capacity = typeof r.capacity === 'number' ? r.capacity : Number(r.capacity || 0);
                            return `
                                <div class="list-group-item d-flex justify-content-between align-items-start gap-3">
                                    <div>
                                        <div class="fw-semibold">Room ${escapeHtml(r.room_number)}</div>
                                        <div class="small text-muted">Capacity: ${escapeHtml(capacity)} • ₱ ${escapeHtml(price.toFixed(2))}</div>
                                    </div>
                                    <span class="badge ${badgeClass}">${escapeHtml(status || '—')}</span>
                                </div>
                            `;
                        }).join('');
                    }
                } catch (e) {
                    if (roomsModalLoadingEl) roomsModalLoadingEl.classList.add('d-none');
                    if (roomsModalErrorEl) {
                        roomsModalErrorEl.textContent = 'Unable to load rooms right now. Please try again.';
                        roomsModalErrorEl.classList.remove('d-none');
                    }
                }
            };

            document.addEventListener('click', (ev) => {
                const trigger = ev.target && ev.target.closest ? ev.target.closest('[data-view-rooms-for-property]') : null;
                if (!trigger) return;
                ev.preventDefault();
                const propertyId = trigger.getAttribute('data-view-rooms-for-property');
                if (propertyId) showRoomsModal(propertyId);
            });

            // Initialize map with default view
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(map);

            // Set default view to Philippines
            map.setView([12.8797, 121.7740], 6);

            // Load properties
            function loadProperties() {
                fetch('{{ route("student.properties.map") }}')
                    .then(r => r.json())
                    .then(data => {
                        const props = data.properties || [];

                        // Clear existing markers
                        markers.forEach(marker => map.removeLayer(marker));
                        markers = [];

                        if (props.length === 0) {
                            alert('No properties with location data available.');
                            return;
                        }

                        const bounds = [];

                        props.forEach(p => {
                            const marker = L.marker([p.lat, p.lng]).addTo(map);
                            marker._propData = p;

                            let popupContent = `
                                <div class="property-card p-2">
                                    <h6 class="mb-1">${p.name}</h6>
                                    <p class="mb-2 small text-muted">
                                        <i class="fas fa-map-marker-alt me-1"></i>${p.address}
                                    </p>
                                    <p class="mb-2 small">
                                        <span class="badge bg-success">${p.available_rooms} rooms available</span>
                                    </p>
                                    <div class="distance-info mb-2"></div>
                                    <button type="button" class="btn btn-primary btn-sm w-100" data-view-rooms-for-property="${p.id}">
                                        <i class="fas fa-eye me-1"></i>View Rooms
                                    </button>
                                </div>
                            `;

                            marker.bindPopup(popupContent);
                            bounds.push([p.lat, p.lng]);
                            markers.push(marker);
                        });

                        // Fit map to show all properties
                        if (bounds.length > 0) {
                            map.fitBounds(bounds, { padding: [20, 20] });
                        }

                        // Update distances if user location is available
                        if (userLatLng) {
                            updateDistances();
                        }
                    })
                    .catch(err => {
                        console.error('Failed to load properties:', err);
                        alert('Failed to load property locations. Please try again.');
                    });
            }

            // Update distance information in popups
            function updateDistances() {
                if (!userLatLng) return;

                markers.forEach(marker => {
                    const p = marker._propData;
                    const propertyLatLng = [p.lat, p.lng];
                    const distMeters = map.distance(userLatLng, propertyLatLng);
                    const km = (distMeters / 1000).toFixed(2);

                    const popupEl = marker.getPopup();
                    if (popupEl) {
                        const content = popupEl.getContent();
                        const container = (typeof content === 'string') ? (() => {
                            const el = document.createElement('div');
                            el.innerHTML = content;
                            return el;
                        })() : content;

                        const distanceDiv = container && container.querySelector ? container.querySelector('.distance-info') : null;
                        if (distanceDiv) {
                            distanceDiv.innerHTML = `<small class="text-muted"><i class="fas fa-route me-1"></i>${km} km away</small>`;
                            marker.setPopupContent(container);
                        }
                    }
                });
            }

            // Locate user
            function locateUser() {
                if (!navigator.geolocation) {
                    alert('Geolocation is not supported by your browser.');
                    return;
                }

                navigator.geolocation.getCurrentPosition(
                    pos => {
                        const { latitude, longitude } = pos.coords;
                        userLatLng = [latitude, longitude];

                        // Remove existing user marker
                        if (userMarker) {
                            map.removeLayer(userMarker);
                        }

                        // Add user marker
                        userMarker = L.circleMarker(userLatLng, {
                            radius: 8,
                            color: '#0ea5a3',
                            fillColor: '#0ea5a3',
                            fillOpacity: 0.85
                        }).addTo(map);

                        userMarker.bindPopup('<strong><i class="fas fa-location-dot me-1"></i>You are here</strong>');

                        // Center map on user location
                        map.setView(userLatLng, 13);

                        // Update distances
                        updateDistances();
                    },
                    err => {
                        console.warn('Geolocation error:', err);
                        alert('Unable to get your location. Please check your browser permissions.');
                    },
                    { enableHighAccuracy: true, timeout: 10000 }
                );
            }

            // Event listeners
            document.getElementById('locateBtn').addEventListener('click', locateUser);
            document.getElementById('refreshBtn').addEventListener('click', loadProperties);

            // Load properties on page load
            loadProperties();
        });
    </script>
</body>
</html>