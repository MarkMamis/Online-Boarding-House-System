@extends('layouts.landlord')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h4 mb-1">Edit Room — {{ $property->name }}</h1>
        <div class="text-muted small">Property address: {{ $property->address }}</div>
    </div>
    <a href="{{ route('landlord.properties.show', $property->id) }}" class="btn btn-outline-secondary">Back to Property</a>
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

@if(session('success'))
  <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="card shadow-sm">
  <form id="updateRoomForm" method="POST" enctype="multipart/form-data" action="{{ route('landlord.properties.rooms.update', [$property->id, $room->id]) }}">
    @csrf
    @method('PUT')
    <div class="card-body">
      <div class="row g-3">
      <div class="col-md-3">
        <label class="form-label">Room Number</label>
        <input type="text" name="room_number" class="form-control" value="{{ old('room_number', $room->room_number) }}" required>
      </div>
      <div class="col-md-3">
        <label class="form-label">Capacity</label>
        <input type="number" min="1" name="capacity" class="form-control" value="{{ old('capacity', $room->capacity) }}" required>
      </div>
      <div class="col-md-3">
        <label class="form-label">Price (₱)</label>
        <input type="number" step="0.01" min="0" name="price" class="form-control" value="{{ old('price', $room->price) }}" required>
      </div>
      <div class="col-md-3">
        <label class="form-label">Status</label>
        <select name="status" class="form-select" required>
          <option value="available" @selected(old('status', $room->status)==='available')>Available</option>
          <option value="occupied" @selected(old('status', $room->status)==='occupied')>Occupied</option>
          <option value="maintenance" @selected(old('status', $room->status)==='maintenance')>Maintenance</option>
        </select>
      </div>

      <div class="col-md-6">
        <label class="form-label">Room Photo (optional)</label>
        <input type="file" name="image" class="form-control" accept="image/*">
        <div class="form-text">Leave blank to keep current photo.</div>
      </div>

      <div class="col-12">
        <label class="form-label">Included in Rent (optional)</label>
        <textarea name="inclusions" class="form-control" rows="3" placeholder="e.g., Electric fan, WiFi, Water, Bed frame">{{ old('inclusions', $room->inclusions) }}</textarea>
      </div>

      @if(!empty($room->image_path))
      <div class="col-md-6">
        <label class="form-label">Current Photo</label>
        <div>
          <img src="{{ asset('storage/'.$room->image_path) }}" alt="Room photo" style="max-height: 120px;" class="img-thumbnail">
        </div>
      </div>
      @endif
    </div>
    </div>
  </form>
  <div class="card-footer bg-light d-flex justify-content-between">
    <form id="deleteRoomForm" method="POST" action="{{ route('landlord.properties.rooms.destroy', [$property->id, $room->id]) }}" onsubmit="return confirm('Delete this room? Bookings linked to it will be orphaned or removed depending on cascade settings.');">
      @csrf
      @method('DELETE')
      <button class="btn btn-outline-danger">Delete Room</button>
    </form>
    <button type="submit" form="updateRoomForm" class="btn btn-brand">Save Changes</button>
  </div>
</div>
@endsection
