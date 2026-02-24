@extends('layouts.landlord')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h1 class="h4 mb-1">Property Overview — {{ $property->name }}</h1>
    <div class="text-muted small">{{ $property->address }}</div>
  </div>
  <div class="d-flex gap-2">
    <a href="{{ route('landlord.properties.edit', $property->id) }}" class="btn btn-outline-brand">Edit</a>
    <a href="{{ route('landlord.properties.rooms.create', $property->id) }}" class="btn btn-brand">Add Room</a>
  </div>
</div>

@if(session('success'))
  <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if(session('info'))
  <div class="alert alert-info">{{ session('info') }}</div>
@endif

<div class="row g-4 mb-4">
  <div class="col-6 col-md-3">
    <div class="border rounded-4 p-3 bg-white shadow-sm text-center">
      <div class="small text-muted">Total Rooms</div>
      <div class="h4 mb-0">{{ $property->rooms_total_live }}</div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="border rounded-4 p-3 bg-white shadow-sm text-center">
      <div class="small text-muted">Vacant Rooms</div>
      <div class="h4 mb-0">{{ $property->rooms_vacant_live }}</div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="border rounded-4 p-3 bg-white shadow-sm text-center">
      <div class="small text-muted">Active Bookings (Today)</div>
      <div class="h4 mb-0">{{ $activeBookings->count() }}</div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="border rounded-4 p-3 bg-white shadow-sm text-center">
      <div class="small text-muted">Pending Requests</div>
      <div class="h4 mb-0">{{ $pendingBookings->count() }}</div>
    </div>
  </div>
</div>

<!-- Property Location Map -->
@if($property->latitude && $property->longitude)
<div class="card shadow-sm mb-4">
  <div class="card-header bg-white">
    <strong>Property Location</strong>
  </div>
  <div class="card-body p-0">
    <div id="propertyMap" style="height:300px; border-radius:0 0 0.375rem 0.375rem;"></div>
  </div>
</div>
@endif

<div class="row g-4">
  <div class="col-lg-6">
    <div class="card shadow-sm">
      <div class="card-header bg-white">
        <strong>Rooms</strong>
      </div>
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>#</th>
              <th>Capacity</th>
              <th>Price</th>
              <th>Current Tenant</th>
              <th>Status</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse($property->rooms as $room)
              <tr>
                <td class="fw-semibold">{{ $room->room_number }}</td>
                <td>{{ $room->capacity }}</td>
                <td>₱ {{ number_format($room->price, 2) }}</td>
                <td>
                  @if($room->current_tenant)
                    <div class="d-flex align-items-center">
                      <div class="avatar avatar-sm rounded-circle me-2 bg-primary text-white d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                        <i class="fas fa-user fa-xs"></i>
                      </div>
                      <div>
                        <div class="fw-medium">{{ $room->current_tenant->full_name }}</div>
                        <div class="text-muted small">{{ $room->current_tenant->student_id }}</div>
                      </div>
                    </div>
                  @else
                    <span class="text-muted small">No tenant</span>
                  @endif
                </td>
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
                  <a href="{{ route('landlord.properties.rooms.edit', [$property->id, $room->id]) }}" class="btn btn-sm btn-outline-brand">Edit</a>
                  <form action="{{ route('landlord.properties.rooms.destroy', [$property->id, $room->id]) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this room?');">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-sm btn-outline-danger">Delete</button>
                  </form>
                </td>
              </tr>
            @empty
              <tr><td colspan="5" class="text-center text-muted py-4">No rooms yet. <a href="{{ route('landlord.properties.rooms.create', $property->id) }}">Add one</a>.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
  <div class="col-lg-6">
    <div class="card shadow-sm mb-4">
      <div class="card-header bg-white"><strong>Active Bookings (Today)</strong></div>
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>Room</th>
              <th>Student</th>
              <th>Dates</th>
            </tr>
          </thead>
          <tbody>
            @forelse($activeBookings as $b)
              <tr>
                <td>{{ $b->room->room_number }}</td>
                <td>{{ $b->student->full_name }}</td>
                <td>{{ $b->check_in->format('M d, Y') }} → {{ $b->check_out->format('M d, Y') }}</td>
              </tr>
            @empty
              <tr><td colspan="3" class="text-center text-muted py-4">No active bookings today.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>

    <div class="card shadow-sm">
      <div class="card-header bg-white"><strong>Pending Requests</strong></div>
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>Room</th>
              <th>Student</th>
              <th>Dates</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse($pendingBookings as $b)
              <tr>
                <td>{{ $b->room->room_number }}</td>
                <td>{{ $b->student->full_name }}</td>
                <td>{{ $b->check_in->format('M d, Y') }} → {{ $b->check_out->format('M d, Y') }}</td>
                <td class="text-end">
                  <form action="{{ route('landlord.bookings.approve', $b->id) }}" method="POST" class="d-inline">@csrf<button class="btn btn-sm btn-success">Approve</button></form>
                  <form action="{{ route('landlord.bookings.reject', $b->id) }}" method="POST" class="d-inline">@csrf<button class="btn btn-sm btn-outline-danger">Reject</button></form>
                </td>
              </tr>
            @empty
              <tr><td colspan="4" class="text-center text-muted py-4">No pending requests.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin=""/>
@endpush

@push('scripts')
@if($property->latitude && $property->longitude)
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const map = L.map('propertyMap');
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    const propertyLatLng = [{{ $property->latitude }}, {{ $property->longitude }}];
    const marker = L.marker(propertyLatLng).addTo(map);
    marker.bindPopup(`<strong>{{ addslashes($property->name) }}</strong><br>{{ addslashes($property->address) }}<br>Total Rooms: {{ $property->rooms_total_live }}<br>Vacant Rooms: {{ $property->rooms_vacant_live }}`);

    // Set view to property location with appropriate zoom
    map.setView(propertyLatLng, 16);

    // Add a circle to show approximate area
    L.circle(propertyLatLng, {
        color: '#0ea5a3',
        fillColor: '#0ea5a3',
        fillOpacity: 0.1,
        radius: 100
    }).addTo(map);
});
</script>
@endif
@endpush
