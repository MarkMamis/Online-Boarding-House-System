<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Browse Rooms</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container">
    <a class="navbar-brand" href="{{ route('student.dashboard') }}">Student</a>
    <div class="ms-auto">
      <a href="{{ route('messages.index') }}" class="btn btn-sm btn-outline-light me-2">Messages</a>
      <form class="d-inline" method="POST" action="{{ route('logout') }}">@csrf<button class="btn btn-sm btn-outline-light">Logout</button></form>
    </div>
  </div>
</nav>
<main class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4">Available Rooms</h1>
    <div>
      <a href="{{ route('student.properties.map_view') }}" class="btn btn-primary me-2">
        <i class="fas fa-map-marked-alt me-1"></i>Map View
      </a>
      <a href="{{ route('student.dashboard') }}" class="btn btn-outline-secondary">Back to Dashboard</a>
    </div>
  </div>

  @if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
  @endif
  @if(session('booking_success'))
    <div class="alert alert-success">{{ session('booking_success') }}</div>
  @endif

  <div id="propertyRoomFilterNotice" class="alert alert-light border d-none">
    Showing rooms for <strong id="propertyRoomFilterName"></strong>.
    <button type="button" id="clearPropertyRoomFilter" class="btn btn-sm btn-outline-secondary ms-2">Clear</button>
  </div>

  <!-- Property Locations Map -->
  <div class="mb-4" id="roomsMap" style="height:360px; border-radius:1rem; overflow:hidden; display:none;"></div>

  <div class="row g-3">
    @forelse($rooms as $room)
      <div class="col-md-4" data-room-property-id="{{ $room->property_id }}">
        <div class="card h-100 shadow-sm">
          @if(!empty($room->image_path))
            <img src="{{ asset('storage/'.$room->image_path) }}" class="card-img-top" alt="Room photo" style="height: 180px; object-fit: cover;">
          @endif
          <div class="card-body">
            <h5 class="card-title mb-1">{{ $room->property->name }}</h5>
            <div class="text-muted small mb-2">
              <i class="fas fa-map-marker-alt text-danger me-1"></i>
              {{ $room->property->address }}
            </div>
            @if($room->property->latitude && $room->property->longitude)
              <div class="small mb-2">
                <span class="badge text-bg-light" id="distance-room-{{ $room->id }}">
                  <i class="fas fa-route text-primary me-1"></i>Calculating distance...
                </span>
              </div>
            @endif
            <div class="mb-2"><strong>Room:</strong> {{ $room->room_number }}</div>
            <div class="mb-2"><strong>Capacity:</strong> {{ $room->capacity }}</div>
            <div class="mb-3"><strong>Price:</strong> ₱ {{ number_format($room->price, 2) }}</div>
            @if(!empty($room->inclusions))
              <div class="small text-muted mb-3"><strong>Includes:</strong> {{ Str::limit($room->inclusions, 80) }}</div>
            @endif
            <button
              type="button"
              class="btn btn-primary w-100"
              data-bs-toggle="modal"
              data-bs-target="#bookingRequestModal"
              data-booking-modal
              data-book-url="{{ route('bookings.store', $room->id) }}"
              data-room-label="{{ $room->property->name }} — Room {{ $room->room_number }}"
            >Request Booking</button>
          </div>
        </div>
      </div>
    @empty
      <div class="col-12"><div class="alert alert-info">No available rooms right now.</div></div>
    @endforelse
  </div>
</main>

  <!-- Booking Request Modal (stay on page) -->
  @php($bookingErrors = $errors->getBag('booking'))
  <div class="modal fade" id="bookingRequestModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <form id="bookingRequestForm" method="POST" action="{{ session('booking_form_action', '') }}">
          @csrf
          <input type="hidden" name="stay" value="1">
          <div class="modal-header">
            <h5 class="modal-title">Request Booking</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div id="bookingRoomLabel" class="small text-muted mb-2">{{ session('booking_room_label') }}</div>

            <div class="row g-2">
              <div class="col-12 col-md-6">
                <label class="form-label">Check-in</label>
                <input type="date" name="check_in" value="{{ old('check_in') }}" class="form-control @if($bookingErrors->has('check_in')) is-invalid @endif" required>
                @if($bookingErrors->has('check_in'))
                  <div class="invalid-feedback">{{ $bookingErrors->first('check_in') }}</div>
                @endif
              </div>
              <div class="col-12 col-md-6">
                <label class="form-label">Check-out</label>
                <input type="date" name="check_out" value="{{ old('check_out') }}" class="form-control @if($bookingErrors->has('check_out')) is-invalid @endif" required>
                @if($bookingErrors->has('check_out'))
                  <div class="invalid-feedback">{{ $bookingErrors->first('check_out') }}</div>
                @endif
              </div>
            </div>

            <div class="mt-2">
              <label class="form-label">Notes (optional)</label>
              <textarea name="notes" rows="3" class="form-control @if($bookingErrors->has('notes')) is-invalid @endif" placeholder="Anything the landlord should know?">{{ old('notes') }}</textarea>
              @if($bookingErrors->has('notes'))
                <div class="invalid-feedback">{{ $bookingErrors->first('notes') }}</div>
              @endif
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary">Submit Request</button>
          </div>
        </form>
      </div>
    </div>
  </div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin=""/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
    const bookingModalEl = document.getElementById('bookingRequestModal');
    const bookingForm = document.getElementById('bookingRequestForm');
    const bookingRoomLabel = document.getElementById('bookingRoomLabel');

    document.querySelectorAll('[data-booking-modal]').forEach(btn => {
      btn.addEventListener('click', () => {
        const url = btn.getAttribute('data-book-url');
        const label = btn.getAttribute('data-room-label') || '';
        if (bookingForm && url) bookingForm.setAttribute('action', url);
        if (bookingRoomLabel) bookingRoomLabel.textContent = label;
      });
    });

    const bookingShouldOpen = @json($bookingErrors->any() || session()->has('booking_form_action'));
    if (bookingShouldOpen && bookingModalEl) {
      const modal = bootstrap.Modal.getOrCreateInstance(bookingModalEl);
      modal.show();
    }

        const escapeHtml = (value) => {
            return String(value ?? '')
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;')
                .replaceAll("'", '&#039;');
        };

        const filterNoticeEl = document.getElementById('propertyRoomFilterNotice');
        const filterNameEl = document.getElementById('propertyRoomFilterName');
        const clearFilterBtn = document.getElementById('clearPropertyRoomFilter');
        const applyPropertyRoomFilter = (propertyId, propertyName) => {
            const filterId = propertyId ? String(propertyId) : '';
            const hasFilter = !!filterId;

            if (filterNoticeEl && filterNameEl) {
                filterNameEl.textContent = propertyName || '';
                filterNoticeEl.classList.toggle('d-none', !hasFilter);
            }

            document.querySelectorAll('[data-room-property-id]').forEach(el => {
                const isMatch = !hasFilter || String(el.getAttribute('data-room-property-id')) === filterId;
                el.style.display = isMatch ? '' : 'none';
            });
        };

        if (clearFilterBtn) {
            clearFilterBtn.addEventListener('click', () => applyPropertyRoomFilter('', ''));
        }

        document.addEventListener('click', (ev) => {
            const trigger = ev.target && ev.target.closest ? ev.target.closest('[data-view-rooms-for-property]') : null;
            if (!trigger) return;
            ev.preventDefault();

            const propertyId = trigger.getAttribute('data-view-rooms-for-property') || '';
            const propertyName = trigger.getAttribute('data-property-name') || '';
            applyPropertyRoomFilter(propertyId, propertyName);

            const mapEl = document.getElementById('roomsMap');
            if (mapEl && mapEl.scrollIntoView) {
                mapEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });

        fetch('{{ route('student.properties.map') }}')
            .then(r => r.json())
            .then(data => {
                const props = data.properties || [];
                // Filter to only show properties that have available rooms
                const availablePropertyIds = @json($rooms->pluck('property_id')->unique()->values());
                const filteredProps = props.filter(p => availablePropertyIds.includes(p.id));
                
                if(!filteredProps.length){ return; }
                const mapEl = document.getElementById('roomsMap');
                mapEl.style.display = 'block';
                const map = L.map('roomsMap');
                const bounds = [];
                const markers = [];
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; OpenStreetMap contributors'
                }).addTo(map);
                filteredProps.forEach(p => {
                    const marker = L.marker([p.lat, p.lng]).addTo(map);
                    marker._propData = p; // store for distance update later
                  marker.bindPopup(
                    `<strong>${p.name}</strong><br>${p.address}<br>Available Rooms: ${p.available_rooms}` +
                    `<div class='distance-info'></div>` +
                    `<button type='button' class='btn btn-sm btn-primary mt-2' data-view-rooms-for-property='${p.id}' data-property-name='${escapeHtml(p.name)}'>View Rooms</button>`
                  );
                    bounds.push([p.lat, p.lng]);
                    markers.push(marker);
                });
                if(bounds.length){ map.fitBounds(bounds, { padding:[24,24] }); }

                // HTML5 Geolocation: center map on user & show distances
                if(navigator.geolocation){
                    navigator.geolocation.getCurrentPosition(pos => {
                        const { latitude, longitude } = pos.coords;
                        const userLatLng = [latitude, longitude];
                        const userMarker = L.circleMarker(userLatLng, { radius:8, color:'#0ea5a3', fillColor:'#0ea5a3', fillOpacity:0.85 }).addTo(map);
                        userMarker.bindPopup('<strong>You are here</strong>');
                        bounds.push(userLatLng);
                        map.fitBounds(bounds, { padding:[24,24] });
                        // Update each marker popup with distance
                        markers.forEach(m => {
                            const p = m._propData;
                            const distMeters = map.distance(userLatLng, [p.lat, p.lng]);
                            const km = (distMeters/1000).toFixed(2);
                            const popupEl = m.getPopup().getContent();
                            // Inject distance info if placeholder exists
                            m.setPopupContent(popupEl.replace('<div class=\'distance-info\'></div>', `<div class='distance-info mt-1'><span class='badge text-bg-light'>${km} km away</span></div>`));
                        });
                    }, err => {
                        console.warn('Geolocation denied/unavailable', err);
                    }, { enableHighAccuracy:true, timeout:10000 });
                }

                // Calculate distances for room cards
                @foreach($rooms as $room)
                    @if($room->property->latitude && $room->property->longitude)
                        if(navigator.geolocation) {
                            navigator.geolocation.getCurrentPosition(pos => {
                                const { latitude, longitude } = pos.coords;
                                const userLatLng = [latitude, longitude];
                                const propertyLatLng = [{{ $room->property->latitude }}, {{ $room->property->longitude }}];
                                
                                // Calculate distance using Haversine formula
                                const R = 6371; // Earth's radius in km
                                const dLat = (propertyLatLng[0] - userLatLng[0]) * Math.PI / 180;
                                const dLon = (propertyLatLng[1] - userLatLng[1]) * Math.PI / 180;
                                const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
                                         Math.cos(userLatLng[0] * Math.PI / 180) * Math.cos(propertyLatLng[0] * Math.PI / 180) *
                                         Math.sin(dLon/2) * Math.sin(dLon/2);
                                const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
                                const distance = R * c;
                                
                                const distanceBadge = document.getElementById('distance-room-{{ $room->id }}');
                                if(distanceBadge) {
                                    distanceBadge.innerHTML = '<i class="fas fa-route text-primary me-1"></i>' + distance.toFixed(1) + ' km away';
                                }
                            }, err => {
                                const distanceBadge = document.getElementById('distance-room-{{ $room->id }}');
                                if(distanceBadge) {
                                    distanceBadge.innerHTML = '<i class="fas fa-map-marker-alt text-muted me-1"></i>Location available';
                                }
                            });
                        } else {
                            const distanceBadge = document.getElementById('distance-room-{{ $room->id }}');
                            if(distanceBadge) {
                                distanceBadge.innerHTML = '<i class="fas fa-map-marker-alt text-muted me-1"></i>Location available';
                            }
                        }
                    @endif
                @endforeach
            })
            .catch(err => console.warn('Map load failed', err));
    });
</script>
</body>
</html>
