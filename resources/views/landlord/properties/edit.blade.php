@extends('layouts.landlord')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h1 class="h4 mb-1">Edit Property</h1>
    <div class="text-muted small">Update details and pricing</div>
  </div>
  <a href="{{ route('landlord.properties.index') }}" class="btn btn-outline-secondary">Back</a>
</div>

@if($errors->any())
  <div class="alert alert-danger">
    <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
  </div>
@endif

<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

<div class="card shadow-sm">
  <form id="updatePropertyForm" method="POST" enctype="multipart/form-data" action="{{ route('landlord.properties.update', $property->id) }}">
    @csrf
    @method('PUT')
    <div class="card-body">
      <div class="row g-3">
      <div class="col-12">
        <label class="form-label">Property Photo (Optional)</label>
        <input type="file" id="propertyImageInput" name="image" class="form-control" accept="image/*">
        <div class="form-text">JPG/PNG/WebP up to 5MB.</div>

        @if(!empty($property->image_path))
          <div class="mt-3">
            <div class="text-muted small mb-1">Current photo</div>
            <img src="{{ asset('storage/' . $property->image_path) }}" alt="Property photo" class="img-fluid rounded-3 border" style="max-height: 260px; object-fit: cover;">
          </div>
        @endif

        <div id="propertyImagePreviewWrap" class="mt-3" style="display:none;">
          <div class="text-muted small mb-1">New photo preview</div>
          <img id="propertyImagePreview" alt="Property preview" class="img-fluid rounded-3 border" style="max-height: 260px; object-fit: cover;">
        </div>
      </div>
      <div class="col-md-6">
        <label class="form-label">Name</label>
        <input type="text" name="name" value="{{ old('name', $property->name) }}" class="form-control" required>
      </div>
      <div class="col-md-6">
        <label class="form-label">Address</label>
        <input type="text" name="address" value="{{ old('address', $property->address) }}" class="form-control" required>
      </div>
      <div class="col-12">
        <label class="form-label">Description</label>
        <textarea name="description" rows="3" class="form-control">{{ old('description', $property->description) }}</textarea>
      </div>
      <div class="col-md-6">
        <label class="form-label">Price Min (₱)</label>
        <input type="number" step="0.01" min="0" name="price_min" value="{{ old('price_min', $property->price_min) }}" class="form-control">
      </div>
      <div class="col-md-6">
        <label class="form-label">Price Max (₱)</label>
        <input type="number" step="0.01" min="0" name="price_max" value="{{ old('price_max', $property->price_max) }}" class="form-control">
      </div>

      <!-- Location/Map Section -->
      <div class="col-12">
        <hr class="my-4">
        <h6 class="fw-semibold mb-3">
          <i class="fas fa-map-marker-alt text-primary me-2"></i>Location & Map
        </h6>
        <p class="text-muted small mb-3">Update the exact location of your property. Click on the map to set the location.</p>
      </div>

      <div class="col-md-6">
        <label class="form-label">Latitude</label>
        <input type="number" step="0.000001" name="latitude" id="latitude" value="{{ old('latitude', $property->latitude) }}" class="form-control" placeholder="e.g. 14.599512">
        <div class="form-text">Click on the map to set automatically</div>
      </div>
      <div class="col-md-6">
        <label class="form-label">Longitude</label>
        <input type="number" step="0.000001" name="longitude" id="longitude" value="{{ old('longitude', $property->longitude) }}" class="form-control" placeholder="e.g. 120.984222">
        <div class="form-text">Click on the map to set automatically</div>
      </div>

      <div class="col-12">
        <label class="form-label">Property Location Map</label>
        <div id="property-map" style="height: 300px; width: 100%; border: 1px solid #dee2e6; border-radius: 0.375rem;"></div>
        <div class="form-text">Click anywhere on the map to update your property location</div>
      </div>
      </div>
    </div>
  </form>

  <div class="card-footer bg-light d-flex justify-content-between">
    <form id="deletePropertyForm" method="POST" action="{{ route('landlord.properties.destroy', $property->id) }}" onsubmit="return confirm('Delete this property? All rooms and bookings under it will be removed.');">
      @csrf
      @method('DELETE')
      <button class="btn btn-outline-danger">Delete</button>
    </form>
    <div>
      <button type="submit" form="updatePropertyForm" class="btn btn-brand">Save Changes</button>
    </div>
  </div>
</div>
@endsection

<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize map centered on Manila, Philippines or property location
    const defaultLat = {{ $property->latitude ?? 14.5995 }};
    const defaultLng = {{ $property->longitude ?? 120.9842 }};
    const map = L.map('property-map').setView([defaultLat, defaultLng], 15);

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

    // Set initial marker if coordinates exist
    if ({{ $property->latitude ? 'true' : 'false' }} && {{ $property->longitude ? 'true' : 'false' }}) {
        updateMarker({{ $property->latitude }}, {{ $property->longitude }});
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
