@extends('layouts.landlord')

@section('content')
<div class="property-create-shell">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <div class="text-uppercase small text-muted fw-semibold">Property Portfolio</div>
            <h1 class="h3 mb-1">Add Property</h1>
            <div class="text-muted small">Create your property profile and pin its exact location.</div>
        </div>
        <a href="{{ route('landlord.properties.index') }}" class="btn btn-outline-secondary rounded-pill px-3">Back to Properties</a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

    <div class="card shadow-sm border-0 rounded-4 overflow-hidden property-form-card">
        <form id="createPropertyForm" method="POST" enctype="multipart/form-data" action="{{ route('landlord.properties.store') }}">
            @csrf
            <div class="card-body">
                <div class="row g-4 modern-create-grid">
                    <div class="col-xl-7">
                        <div class="edit-panel h-100">
                            <div class="section-kicker mb-3">Basic Details</div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Name</label>
                                    <input type="text" name="name" value="{{ old('name') }}" class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Address</label>
                                    <input type="text" name="address" value="{{ old('address') }}" class="form-control" required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Property Photo (Optional)</label>
                                    <input type="file" id="propertyImageInput" name="image" class="form-control" accept="image/*">
                                    <div class="form-text">JPG/PNG/WebP up to 5MB.</div>
                                    <div id="propertyImagePreviewWrap" class="mt-3" style="display:none;">
                                        <img id="propertyImagePreview" alt="Property preview" class="img-fluid rounded-3 border" style="max-height: 260px; object-fit: cover;">
                                    </div>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Description</label>
                                    <textarea name="description" rows="4" class="form-control" placeholder="Optional">{{ old('description') }}</textarea>
                                </div>
                            </div>

                            <div class="alert alert-light border mb-0 mt-3">
                                <strong>Price Range</strong>
                                <div class="small text-muted">This is automatically generated from room prices after you add rooms.</div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-5">
                        <div class="edit-panel location-panel h-100">
                            <div class="d-flex justify-content-between align-items-center gap-2 mb-2">
                                <h6 class="fw-semibold mb-0 section-kicker d-inline-flex align-items-center gap-2">
                                    <i class="fas fa-map-marker-alt text-primary"></i>Location & Map
                                </h6>
                                <span class="badge text-bg-light border">Interactive</span>
                            </div>
                            <p class="text-muted small mb-3">Enter coordinates manually or click directly on the map to place the pin.</p>

                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label">Latitude</label>
                                    <input type="number" step="0.000001" name="latitude" id="latitude" value="{{ old('latitude') }}" class="form-control" placeholder="e.g. 14.599512">
                                    <div class="form-text">Map click will auto-fill this field.</div>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Longitude</label>
                                    <input type="number" step="0.000001" name="longitude" id="longitude" value="{{ old('longitude') }}" class="form-control" placeholder="e.g. 120.984222">
                                    <div class="form-text">Map click will auto-fill this field.</div>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Property Location Map</label>
                                    <div id="property-map" class="map-box" style="height: 360px; width: 100%;"></div>
                                    <div class="form-text">Click anywhere on the map to set your property location.</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-footer bg-light action-bar">
                <div class="first-room-panel">
                    <h5 class="fw-semibold mb-1">Add First Room (Optional)</h5>
                    <p class="text-muted small mb-3">You can seed the property with its first room now, or leave these blank and add rooms later.</p>
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Room Number</label>
                            <input type="text" name="initial_room_number" value="{{ old('initial_room_number') }}" class="form-control" placeholder="e.g. A-101">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Capacity</label>
                            <input type="number" min="1" name="initial_capacity" value="{{ old('initial_capacity', 1) }}" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Price (P)</label>
                            <input type="number" step="0.01" min="0" name="initial_price" value="{{ old('initial_price') }}" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Status</label>
                            <select name="initial_status" class="form-select">
                                <option value="available" @selected(old('initial_status')==='available')>Available</option>
                                <option value="occupied" @selected(old('initial_status')==='occupied')>Occupied</option>
                                <option value="maintenance" @selected(old('initial_status')==='maintenance')>Maintenance</option>
                            </select>
                        </div>
                    </div>
                    <div class="d-flex flex-wrap gap-2 mt-3 justify-content-end">
                        <a href="{{ route('landlord.properties.index') }}" class="btn btn-outline-secondary rounded-pill px-3">Cancel</a>
                        <button type="submit" class="btn btn-brand rounded-pill px-4">Save Property</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('styles')
<style>
    .property-create-shell {
        background: linear-gradient(180deg, #ffffff 0%, #fbfdfc 100%);
        border: 1px solid rgba(2,8,20,.08);
        border-radius: 1.25rem;
        box-shadow: 0 10px 26px rgba(2,8,20,.06);
        padding: 1.25rem;
    }
    .property-form-card {
        border: 1px solid rgba(2,8,20,.08) !important;
        box-shadow: 0 14px 30px rgba(2,8,20,.08) !important;
    }
    .property-form-card .card-body {
        padding: 1.35rem;
    }
    .modern-create-grid {
        align-items: stretch;
    }
    .edit-panel {
        border: 1px solid rgba(2,8,20,.09);
        border-radius: 1rem;
        background: linear-gradient(180deg, #ffffff 0%, #fcfffd 100%);
        padding: 1rem;
        box-shadow: 0 8px 20px rgba(2,8,20,.05);
    }
    .location-panel {
        background: linear-gradient(180deg, #f8fbff 0%, #ffffff 100%);
    }
    .section-kicker {
        font-size: .78rem;
        letter-spacing: .05em;
        text-transform: uppercase;
        color: rgba(2,8,20,.6);
        font-weight: 700;
    }
    .property-form-card .form-label {
        font-weight: 600;
        color: #0f172a;
    }
    .property-form-card .form-control,
    .property-form-card .form-select,
    .property-form-card textarea {
        border-color: rgba(2,8,20,.14);
        background: #ffffff;
    }
    .map-box {
        border: 1px solid #dee2e6;
        border-radius: .8rem;
        overflow: hidden;
        box-shadow: inset 0 0 0 1px rgba(255,255,255,.6), 0 8px 20px rgba(2,8,20,.06);
    }
    .action-bar {
        border-top: 1px solid rgba(2,8,20,.08);
        background: #f8fafc;
        padding: 1rem 1.25rem 1.15rem;
    }
    .first-room-panel {
        border: 1px solid rgba(2,8,20,.08);
        border-radius: .95rem;
        background: #ffffff;
        padding: 1rem;
    }
</style>
@endpush

<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize map centered on Manila, Philippines
    const map = L.map('property-map').setView([14.5995, 120.9842], 10);

    // Add OpenStreetMap tiles
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    let marker = null;

    // Function to update marker position
    function updateMarker(lat, lng) {
        if (marker) {
            map.removeLayer(marker);
        }
        marker = L.marker([lat, lng]).addTo(map);
        document.getElementById('latitude').value = lat.toFixed(6);
        document.getElementById('longitude').value = lng.toFixed(6);
    }

    // Handle map clicks
    map.on('click', function(e) {
        const lat = e.latlng.lat;
        const lng = e.latlng.lng;
        updateMarker(lat, lng);
    });

    // Handle coordinate input changes
    document.getElementById('latitude').addEventListener('input', function() {
        const lat = parseFloat(this.value);
        const lng = parseFloat(document.getElementById('longitude').value);
        if (!isNaN(lat) && !isNaN(lng) && lat >= -90 && lat <= 90 && lng >= -180 && lng <= 180) {
            updateMarker(lat, lng);
            map.setView([lat, lng], 15);
        }
    });

    document.getElementById('longitude').addEventListener('input', function() {
        const lat = parseFloat(document.getElementById('latitude').value);
        const lng = parseFloat(this.value);
        if (!isNaN(lat) && !isNaN(lng) && lat >= -90 && lat <= 90 && lng >= -180 && lng <= 180) {
            updateMarker(lat, lng);
            map.setView([lat, lng], 15);
        }
    });

    // Try to get user's current location for initial map position
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(position) {
            const lat = position.coords.latitude;
            const lng = position.coords.longitude;
            map.setView([lat, lng], 13);
        }, function(error) {
            console.log('Geolocation error:', error);
        });
    }
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const input = document.getElementById('propertyImageInput');
    const wrap = document.getElementById('propertyImagePreviewWrap');
    const img = document.getElementById('propertyImagePreview');
    if (!input || !wrap || !img) return;

    input.addEventListener('change', function () {
        const file = input.files && input.files[0];
        if (!file) {
            wrap.style.display = 'none';
            img.src = '';
            return;
        }
        img.src = URL.createObjectURL(file);
        wrap.style.display = 'block';
    });
});
</script>
