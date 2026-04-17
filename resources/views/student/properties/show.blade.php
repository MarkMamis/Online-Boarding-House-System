<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $property->name }} - Boarding House Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Inter', system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif; min-height:100vh; background:#f1f5f9; }
        .header-band { background: linear-gradient(135deg,#0ea5a3,#0b7f7e); color:#fff; padding:3rem 0 2.5rem; }
        .rooms-card { background:#fff; border-radius:1.1rem; box-shadow:0 6px 18px rgba(0,0,0,.08); }
        .badge-light { background:#eef2f7; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand fw-semibold" href="{{ route('student.dashboard') }}">Student Portal</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav" aria-controls="nav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="nav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a href="{{ route('student.dashboard') }}" class="nav-link">Back to Dashboard</a></li>
                    <li class="nav-item"><a href="{{ route('student.rooms.index') }}" class="nav-link">Browse Rooms</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <header class="header-band">
        <div class="container">
            <h1 class="h3 fw-semibold mb-2">{{ $property->name }}</h1>
            <p class="mb-1"><strong>Address:</strong> {{ $property->address }}</p>
            <p class="mb-1"><strong>Landlord:</strong> {{ $property->landlord->full_name ?? 'Landlord' }}</p>
            <p class="mb-0"><strong>Availability:</strong> {{ $property->rooms_available_live }} / {{ $property->rooms_total_live }} rooms available</p>
        </div>
    </header>

    <main class="py-4">
        <div class="container">
            @php
                $student = auth()->user();
                $schoolIdVerificationStatus = (string) ($student->school_id_verification_status ?? '');
                if ($schoolIdVerificationStatus === '') {
                    $hasVerificationDocument = filled($student->school_id_path) || filled($student->enrollment_proof_path);
                    $schoolIdVerificationStatus = $hasVerificationDocument ? 'pending' : 'not_submitted';
                }
                $bookingLockedBySchoolId = $schoolIdVerificationStatus !== 'approved';

                if ($schoolIdVerificationStatus === 'rejected') {
                    $bookingLockMessage = 'Your verification document was rejected. Upload a corrected School ID or COR/COE in Student Setup to unlock booking.';
                } elseif ($schoolIdVerificationStatus === 'not_submitted') {
                    $bookingLockMessage = 'Booking is locked until you upload your School ID or COR/COE in Student Setup.';
                } else {
                    $bookingLockMessage = 'Booking is locked while your academic verification is pending admin approval.';
                }
            @endphp

            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif
            @if(session('booking_success'))
                <div class="alert alert-success">{{ session('booking_success') }}</div>
            @endif

            @if($bookingLockedBySchoolId)
                <div class="alert alert-warning">
                    {{ $bookingLockMessage }} You can still browse rooms and properties.
                </div>
            @endif

            @php
                $amenityLabels = (array) config('property_amenities.flat', []);
                $buildingInclusions = collect((array) ($property->building_inclusions ?? []))
                    ->map(fn ($key) => $amenityLabels[$key] ?? trim((string) $key))
                    ->filter()
                    ->values();
            @endphp

            <!-- Property Location Map -->
            @if($property->latitude && $property->longitude)
                <div class="mb-4">
                    <div id="propertyMap" style="height:300px; border-radius:1rem; overflow:hidden;"></div>
                </div>
            @endif

            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="rooms-card p-4">
                        <h5 class="fw-semibold mb-3">Rooms</h5>
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead class="table-light small">
                                    <tr>
                                        <th>Photo</th>
                                        <th>#</th>
                                        <th>Capacity</th>
                                        <th>Price</th>
                                        <th>Includes</th>
                                        <th>Status</th>
                                        <th class="text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="small">
                                    @forelse($property->rooms as $room)
                                        <tr>
                                            <td style="width: 72px;">
                                                @if(!empty($room->image_path))
                                                    <img src="{{ asset('storage/'.$room->image_path) }}" alt="Room photo" class="img-thumbnail" style="width: 56px; height: 56px; object-fit: cover;">
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                            <td>{{ $room->room_number }}</td>
                                            <td>{{ $room->capacity }}</td>
                                            <td>₱ {{ number_format($room->price,2) }}</td>
                                            <td class="text-muted">{{ $room->inclusions ? Str::limit($room->inclusions, 40) : '—' }}</td>
                                            <td>
                                                @if($room->status==='available')
                                                    <span class="badge text-bg-success">Available</span>
                                                @elseif($room->status==='occupied')
                                                    <span class="badge text-bg-secondary">Occupied</span>
                                                @else
                                                    <span class="badge text-bg-warning">Maintenance</span>
                                                @endif
                                            </td>
                                            <td class="text-end">
                                                @if($room->status==='available')
                                                    @if($bookingLockedBySchoolId)
                                                        <button type="button" class="btn btn-sm btn-outline-secondary" disabled title="{{ $bookingLockMessage }}">Request</button>
                                                    @else
                                                        <button
                                                            type="button"
                                                            class="btn btn-sm btn-outline-success"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#bookingRequestModal"
                                                            data-booking-modal
                                                            data-book-url="{{ route('bookings.store', $room->id) }}"
                                                            data-room-label="{{ $property->name }} — {{ $room->room_number }}"
                                                        >Request</button>
                                                    @endif
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="7" class="text-center text-muted py-4">No rooms defined yet.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    @if($buildingInclusions->isNotEmpty())
                        <div class="rooms-card p-4 mb-4">
                            <h6 class="fw-semibold mb-2">Building/Boarding House Inclusions</h6>
                            <div class="d-flex flex-wrap gap-2">
                                @foreach($buildingInclusions as $inclusion)
                                    <span class="badge text-bg-light border">{{ $inclusion }}</span>
                                @endforeach
                            </div>
                        </div>
                    @endif
                    <div class="rooms-card p-4 mb-4">
                        <h6 class="fw-semibold mb-2">Description</h6>
                        <p class="small mb-0">{{ $property->description ?: 'No description provided by landlord.' }}</p>
                    </div>
                    <div class="rooms-card p-4 mb-4">
                        <h6 class="fw-semibold mb-2">Message Landlord</h6>
                        <form method="POST" action="{{ route('messages.store') }}" class="small">
                            @csrf
                            <input type="hidden" name="receiver_id" value="{{ $property->landlord->id }}">
                            <input type="hidden" name="property_id" value="{{ $property->id }}">
                            <div class="mb-2">
                                <label class="form-label">Your Message</label>
                                <textarea name="body" class="form-control" rows="3" required placeholder="Ask about availability, pricing, or rules.">{{ old('body') }}</textarea>
                            </div>
                            <div class="d-flex justify-content-end">
                                <button class="btn btn-sm btn-brand" type="submit">Send</button>
                            </div>
                            <div class="form-text">Landlord will see this in their Messages panel.</div>
                        </form>
                    </div>
                    @if($property->price_min !== null || $property->price_max !== null)
                        <div class="rooms-card p-4 mb-4">
                            <h6 class="fw-semibold mb-2">Price Range</h6>
                            <p class="small mb-0">₱{{ number_format($property->price_min,0) }} - ₱{{ number_format($property->price_max,0) }}</p>
                        </div>
                    @endif
                    <div class="rooms-card p-4">
                        <h6 class="fw-semibold mb-2">Quick Info</h6>
                        <ul class="small mb-0 list-unstyled">
                            <li>Created: {{ $property->created_at->diffForHumans() }}</li>
                            <li>Total Rooms: {{ $property->rooms_total_live }}</li>
                            <li>Available Rooms: {{ $property->rooms_available_live }}</li>
                        </ul>
                    </div>
                </div>
            </div>
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
                        <button type="submit" class="btn btn-outline-success">Submit Request</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
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

            @if($bookingErrors->any() || session()->has('booking_form_action'))
                if (bookingModalEl) {
                    const modal = bootstrap.Modal.getOrCreateInstance(bookingModalEl);
                    modal.show();
                }
            @endif
        });
    </script>
    @if($property->latitude && $property->longitude)
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin=""/>
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const map = L.map('propertyMap');
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxNativeZoom: 19,
                    maxZoom: 22,
                    attribution: '&copy; OpenStreetMap contributors'
                }).addTo(map);
                
                const propertyLatLng = [{{ $property->latitude }}, {{ $property->longitude }}];
                const marker = L.marker(propertyLatLng).addTo(map);
                marker.bindPopup(`<strong>{{ addslashes($property->name) }}</strong><br>{{ addslashes($property->address) }}<br>Available Rooms: {{ $property->rooms_available_live }}`);
                
                // HTML5 Geolocation: show user location and distance
                if(navigator.geolocation){
                    navigator.geolocation.getCurrentPosition(pos => {
                        const { latitude, longitude } = pos.coords;
                        const userLatLng = [latitude, longitude];
                        const userMarker = L.circleMarker(userLatLng, { radius:8, color:'#0ea5a3', fillColor:'#0ea5a3', fillOpacity:0.85 }).addTo(map);
                        userMarker.bindPopup('<strong>You are here</strong>');
                        
                        // Fit bounds to show both property and user
                        const bounds = [propertyLatLng, userLatLng];
                        map.fitBounds(bounds, { padding:[24,24] });
                        
                        // Calculate and show distance
                        const distMeters = map.distance(userLatLng, propertyLatLng);
                        const km = (distMeters/1000).toFixed(2);
                        marker.setPopupContent(`<strong>{{ addslashes($property->name) }}</strong><br>{{ addslashes($property->address) }}<br>Available Rooms: {{ $property->rooms_available_live }}<br><span class='badge text-bg-light mt-1'>${km} km away</span>`);
                    }, err => {
                        console.warn('Geolocation denied/unavailable', err);
                        map.setView(propertyLatLng, 15);
                    }, { enableHighAccuracy:true, timeout:10000 });
                } else {
                    map.setView(propertyLatLng, 15);
                }
            });
        </script>
    @endif
</body>
</html>

