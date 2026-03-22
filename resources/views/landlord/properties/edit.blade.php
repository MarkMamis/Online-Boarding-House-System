@extends('layouts.landlord')

@section('content')
<div class="property-edit-shell">
<div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
  <div>
    <div class="text-uppercase small text-muted fw-semibold">Property Settings</div>
    <h1 class="h3 mb-1">Edit Property</h1>
    <div class="text-muted small">Update property details, image, and map location.</div>
  </div>
  <a href="{{ route('landlord.properties.show', $property->id) }}" class="btn btn-outline-secondary rounded-pill px-3">Back to Overview</a>
</div>

<div class="edit-summary mb-4">
  <div class="edit-summary-item">
    <div class="edit-summary-label">Property</div>
    <div class="edit-summary-value">{{ $property->name }}</div>
  </div>
  <div class="edit-summary-item">
    <div class="edit-summary-label">Address</div>
    <div class="edit-summary-value text-truncate">{{ $property->address }}</div>
  </div>
  <div class="edit-summary-item">
    <div class="edit-summary-label">Price Range</div>
    <div class="edit-summary-value">
      @if($property->price_min !== null || $property->price_max !== null)
        ₱{{ number_format($property->price_min, 2) }} - ₱{{ number_format($property->price_max, 2) }}
      @else
        Not available yet
      @endif
    </div>
  </div>
</div>

@if($errors->any())
  <div class="alert alert-danger">
    <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
  </div>
@endif

<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

<div class="card shadow-sm border-0 rounded-4 overflow-hidden property-form-card">
  <form id="updatePropertyForm" method="POST" enctype="multipart/form-data" action="{{ route('landlord.properties.update', $property->id) }}">
    @csrf
    @method('PUT')
    <div class="card-body">
      <div class="row g-4 modern-edit-grid">
        <div class="col-xl-7">
          <div class="edit-panel h-100">
            <div class="section-kicker mb-3">Basic Details</div>

            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label">Name</label>
                <input type="text" name="name" value="{{ old('name', $property->name) }}" class="form-control" required>
              </div>
              <div class="col-md-6">
                <label class="form-label">Address</label>
                <input type="text" name="address" value="{{ old('address', $property->address) }}" class="form-control" required>
              </div>
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
              <div class="col-12">
                <label class="form-label">Description</label>
                <textarea name="description" rows="4" class="form-control">{{ old('description', $property->description) }}</textarea>
              </div>
            </div>

            <div class="alert alert-light border mb-0 mt-3">
              <strong>Price Range</strong>
              <div class="small text-muted">Auto-generated from room prices. Current range:
                @if($property->price_min !== null || $property->price_max !== null)
                  <span class="fw-semibold">₱{{ number_format($property->price_min, 2) }} - ₱{{ number_format($property->price_max, 2) }}</span>
                @else
                  <span class="fw-semibold">Not available yet</span>
                @endif
              </div>
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
            <p class="text-muted small mb-3">Set an accurate pin by clicking the map or typing exact coordinates.</p>

            <div class="row g-3">
              <div class="col-12">
                <label class="form-label">Latitude</label>
                <input type="number" step="0.000001" name="latitude" id="latitude" value="{{ old('latitude', $property->latitude) }}" class="form-control" placeholder="e.g. 14.599512">
                <div class="form-text">Map click will auto-fill this field.</div>
              </div>
              <div class="col-12">
                <label class="form-label">Longitude</label>
                <input type="number" step="0.000001" name="longitude" id="longitude" value="{{ old('longitude', $property->longitude) }}" class="form-control" placeholder="e.g. 120.984222">
                <div class="form-text">Map click will auto-fill this field.</div>
              </div>
              <div class="col-12">
                <label class="form-label">Property Location Map</label>
                <div id="property-map" class="map-box" style="height: 360px; width: 100%;"></div>
                <div class="form-text">Click anywhere on the map to update your property location.</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </form>

  <div class="card-footer bg-light d-flex justify-content-between align-items-center action-bar">
    <form id="deletePropertyForm" method="POST" action="{{ route('landlord.properties.destroy', $property->id) }}" onsubmit="return confirm('Delete this property? All rooms and bookings under it will be removed.');">
      @csrf
      @method('DELETE')
      <button class="btn btn-outline-danger rounded-pill px-3">Delete</button>
    </form>
    <div>
      <button type="submit" form="updatePropertyForm" class="btn btn-brand rounded-pill px-4">Save Changes</button>
    </div>
  </div>
</div>
</div>
@endsection

@push('styles')
<style>
  .property-edit-shell {
    background: linear-gradient(180deg, #ffffff 0%, #fbfdfc 100%);
    border: 1px solid rgba(2,8,20,.08);
    border-radius: 1.25rem;
    box-shadow: 0 10px 26px rgba(2,8,20,.06);
    padding: 1.25rem;
  }
  .edit-summary {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: .75rem;
  }
  .edit-summary-item {
    border: 1px solid rgba(20,83,45,.16);
    background: linear-gradient(180deg, rgba(167,243,208,.18), rgba(255,255,255,.85));
    border-radius: .9rem;
    padding: .7rem .8rem;
    min-width: 0;
  }
  .edit-summary-label {
    font-size: .72rem;
    text-transform: uppercase;
    letter-spacing: .05em;
    color: rgba(2,8,20,.55);
    font-weight: 700;
    margin-bottom: .2rem;
  }
  .edit-summary-value {
    font-size: .94rem;
    font-weight: 700;
    color: #14532d;
  }
  .property-form-card {
    border: 1px solid rgba(2,8,20,.08) !important;
    box-shadow: 0 14px 30px rgba(2,8,20,.08) !important;
  }
  .property-form-card .card-body {
    padding: 1.35rem;
  }
  .modern-edit-grid {
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
  .section-kicker {
    font-size: .78rem;
    letter-spacing: .05em;
    text-transform: uppercase;
    color: rgba(2,8,20,.6);
    font-weight: 700;
  }
  .section-divider {
    border-color: rgba(2,8,20,.09);
  }
  .map-box {
    border: 1px solid #dee2e6;
    border-radius: .8rem;
    overflow: hidden;
    box-shadow: inset 0 0 0 1px rgba(255,255,255,.6), 0 8px 20px rgba(2,8,20,.06);
  }
  .action-bar {
    position: sticky;
    bottom: 0;
    z-index: 5;
    border-top: 1px solid rgba(2,8,20,.08);
  }
  @media (max-width: 991.98px) {
    .edit-summary {
      grid-template-columns: 1fr;
    }
  }
</style>
@endpush

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
