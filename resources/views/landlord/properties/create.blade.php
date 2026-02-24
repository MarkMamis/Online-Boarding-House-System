@extends('layouts.landlord')

@section('content')
<div class="glass-card rounded-4 p-4 p-md-5">
<h2 class="h4 mb-4">Add Property</h2>
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

<form method="POST" enctype="multipart/form-data" action="{{ route('landlord.properties.store') }}" class="row g-4">
    @csrf
    <div class="col-12">
        <label class="form-label">Property Photo (Optional)</label>
        <input type="file" id="propertyImageInput" name="image" class="form-control" accept="image/*">
        <div class="form-text">JPG/PNG/WebP up to 5MB.</div>
        <div id="propertyImagePreviewWrap" class="mt-3" style="display:none;">
            <img id="propertyImagePreview" alt="Property preview" class="img-fluid rounded-3 border" style="max-height: 260px; object-fit: cover;">
        </div>
    </div>
    <div class="col-md-6">
        <label class="form-label">Name</label>
        <input type="text" name="name" value="{{ old('name') }}" class="form-control" required>
    </div>
    <div class="col-md-6">
        <label class="form-label">Address</label>
        <input type="text" name="address" value="{{ old('address') }}" class="form-control" required>
    </div>
    <div class="col-12">
        <label class="form-label">Description</label>
        <textarea name="description" rows="3" class="form-control" placeholder="Optional">{{ old('description') }}</textarea>
    </div>
    <div class="col-md-6">
        <label class="form-label">Price Range (Min)</label>
        <input type="number" step="0.01" min="0" name="price_min" value="{{ old('price_min') }}" class="form-control">
    </div>
    <div class="col-md-6">
        <label class="form-label">Price Range (Max)</label>
        <input type="number" step="0.01" min="0" name="price_max" value="{{ old('price_max') }}" class="form-control">
    </div>

    <!-- Location/Map Section -->
    <div class="col-12">
        <hr class="my-4">
        <h5 class="fw-semibold mb-3">
            <i class="fas fa-map-marker-alt text-primary me-2"></i>Location & Map
        </h5>
        <p class="text-muted small mb-3">Set the exact location of your property. You can either enter coordinates manually or click on the map to set the location.</p>
    </div>

    <div class="col-md-6">
        <label class="form-label">Latitude</label>
        <input type="number" step="0.000001" name="latitude" id="latitude" value="{{ old('latitude') }}" class="form-control" placeholder="e.g. 14.599512">
        <div class="form-text">Click on the map to set automatically</div>
    </div>
    <div class="col-md-6">
        <label class="form-label">Longitude</label>
        <input type="number" step="0.000001" name="longitude" id="longitude" value="{{ old('longitude') }}" class="form-control" placeholder="e.g. 120.984222">
        <div class="form-text">Click on the map to set automatically</div>
    </div>

    <div class="col-12">
        <label class="form-label">Property Location Map</label>
        <div id="property-map" style="height: 300px; width: 100%; border: 1px solid #dee2e6; border-radius: 0.375rem;"></div>
        <div class="form-text">Click anywhere on the map to set your property location</div>
    </div>

    <hr class="mt-4 mb-2">
    <div class="col-12">
        <h5 class="fw-semibold">Add First Room (Optional)</h5>
        <p class="text-muted small mb-3">You can seed the property with its first room now to make recommendations appear for students sooner. Leave blank if you want to add rooms later.</p>
    </div>
    <div class="col-md-3">
        <label class="form-label">Room Number</label>
        <input type="text" name="initial_room_number" value="{{ old('initial_room_number') }}" class="form-control" placeholder="e.g. A-101">
    </div>
    <div class="col-md-3">
        <label class="form-label">Capacity</label>
        <input type="number" min="1" name="initial_capacity" value="{{ old('initial_capacity', 1) }}" class="form-control">
    </div>
    <div class="col-md-3">
        <label class="form-label">Price (₱)</label>
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
    <div class="col-12">
        <button class="btn btn-brand rounded-pill px-4">Save Property</button>
        <a href="{{ route('landlord.properties.index') }}" class="btn btn-outline-secondary rounded-pill">Cancel</a>
    </div>
</form>
</div>
@endsection

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