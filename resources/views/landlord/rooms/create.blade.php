@extends('layouts.landlord')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h4 mb-1">Add Room — {{ $property->name }}</h1>
        <div class="text-muted small">{{ $property->address }}</div>
    </div>
    <a href="{{ route('landlord.properties.rooms.index', $property->id) }}" class="btn btn-outline-secondary">Back</a>
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

<form method="POST" enctype="multipart/form-data" action="{{ route('landlord.properties.rooms.store', $property->id) }}" class="card shadow-sm">
  @csrf
  <div class="card-body">
    <div class="row g-3">
      <div class="col-md-4">
        <label class="form-label">Room Number</label>
        <input type="text" name="room_number" class="form-control" value="{{ old('room_number') }}" required>
      </div>
      <div class="col-md-4">
        <label class="form-label">Capacity</label>
        <input type="number" min="1" name="capacity" class="form-control" value="{{ old('capacity', 1) }}" required>
      </div>
      <div class="col-md-4">
        <label class="form-label">Price (₱)</label>
        <input type="number" step="0.01" min="0" name="price" class="form-control" value="{{ old('price') }}" required>
      </div>
      <div class="col-md-4">
        <label class="form-label">Status</label>
        <select name="status" class="form-select" required>
          <option value="available" @selected(old('status')==='available')>Available</option>
          <option value="occupied" @selected(old('status')==='occupied')>Occupied</option>
          <option value="maintenance" @selected(old('status')==='maintenance')>Maintenance</option>
        </select>
      </div>

      <div class="col-md-8">
        <label class="form-label">Room Photo (optional)</label>
        <input type="file" id="roomImageInput" name="image" class="form-control" accept="image/*">
        <div class="form-text">JPG/PNG/WebP/GIF, up to 2MB.</div>
        <div class="mt-2" id="roomImagePreviewWrap" style="display:none;">
          <img id="roomImagePreview" alt="Room photo preview" class="img-thumbnail" style="max-height: 140px;">
        </div>
      </div>

      <div class="col-12">
        <label class="form-label">Included in Rent (optional)</label>
        <textarea name="inclusions" class="form-control" rows="3" placeholder="e.g., Electric fan, WiFi, Water, Bed frame">{{ old('inclusions') }}</textarea>
        <div class="form-text">List what’s included (comma-separated is fine).</div>
      </div>
    </div>
  </div>
  <div class="card-footer bg-light d-flex justify-content-end">
    <button type="submit" class="btn btn-brand">Save Room</button>
  </div>
</form>

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', () => {
    const input = document.getElementById('roomImageInput');
    const wrap = document.getElementById('roomImagePreviewWrap');
    const img = document.getElementById('roomImagePreview');
    if (!input || !wrap || !img) return;

    input.addEventListener('change', () => {
      const file = input.files && input.files[0];
      if (!file) {
        wrap.style.display = 'none';
        img.removeAttribute('src');
        return;
      }
      const url = URL.createObjectURL(file);
      img.src = url;
      wrap.style.display = '';
      img.onload = () => URL.revokeObjectURL(url);
    });
  });
</script>
@endpush
@endsection
