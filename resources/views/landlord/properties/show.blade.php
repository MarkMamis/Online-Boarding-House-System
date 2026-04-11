@extends('layouts.landlord')

@section('content')
<div class="property-overview-shell">
  <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
    <div>
      <div class="text-uppercase small text-muted fw-semibold">Property Overview</div>
      <h1 class="h3 mb-1">{{ $property->name }}</h1>
      <div class="text-muted">{{ $property->address }}</div>
    </div>
    <div class="d-flex flex-wrap gap-2">
      <a href="{{ route('landlord.properties.index') }}" class="btn btn-outline-secondary">Back to List</a>
      <a href="{{ route('landlord.properties.edit', $property->id) }}" class="btn btn-outline-brand">Edit Property</a>
      <a href="{{ route('landlord.properties.rooms.create', $property->id) }}" class="btn btn-brand">Add Room</a>
    </div>
  </div>

@if(session('info'))
  <div class="alert alert-info">{{ session('info') }}</div>
@endif

<div class="row g-4 mb-4">
  <div class="col-6 col-md-3">
    <div class="metric-card">
      <div class="small text-muted text-uppercase">Total Rooms</div>
      <div class="h3 mb-0">{{ $property->rooms_total_live }}</div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="metric-card">
      <div class="small text-muted text-uppercase">Vacant Rooms</div>
      <div class="h3 mb-0">{{ $property->rooms_vacant_live }}</div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="metric-card">
      <div class="small text-muted text-uppercase">Active Today</div>
      <div class="h3 mb-0">{{ $activeBookings->count() }}</div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="metric-card">
      <div class="small text-muted text-uppercase">Pending Requests</div>
      <div class="h3 mb-0">{{ $pendingBookings->count() }}</div>
    </div>
  </div>
</div>

<!-- Property Location Map -->
@if($property->latitude && $property->longitude)
<div class="card shadow-sm border-0 rounded-4 mb-4 overflow-hidden">
  <div class="card-header bg-white border-0 py-3">
    <strong>Property Location</strong>
  </div>
  <div class="card-body p-0">
    <div id="propertyMap" style="height:300px;"></div>
  </div>
</div>
@endif

@php
  $amenityLabels = (array) config('property_amenities.flat', []);
  $buildingInclusions = collect((array) ($property->building_inclusions ?? []))
    ->map(fn ($key) => $amenityLabels[$key] ?? trim((string) $key))
    ->filter()
    ->values();
@endphp
<div class="card shadow-sm border-0 rounded-4 mb-4 overflow-hidden">
  <div class="card-header bg-white border-0 py-3">
    <strong>Building Inclusions</strong>
  </div>
  <div class="card-body pt-2">
    @if($buildingInclusions->isNotEmpty())
      <div class="d-flex flex-wrap gap-2">
        @foreach($buildingInclusions as $inclusion)
          <span class="item-chip">{{ $inclusion }}</span>
        @endforeach
      </div>
    @else
      <div class="text-muted small">No building inclusions selected yet.</div>
    @endif
  </div>
</div>

<div class="row g-4">
  <div class="col-lg-7">
    <div class="card shadow-sm border-0 rounded-4 h-100 panel-card">
      <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
        <strong>Rooms</strong>
        <span class="badge text-bg-light border">{{ $property->rooms->count() }}</span>
      </div>
      <div class="card-body p-3 pt-0">
        @forelse($property->rooms as $room)
          @php
            $slotsAvailable = $room->getAvailableSlots();
          @endphp
          <div class="item-row">
            <div class="d-flex flex-wrap justify-content-between gap-2 mb-2">
              <div class="fw-semibold">Room {{ $room->room_number }}</div>
              <div>
                @if($room->status==='available')
                  <span class="badge text-bg-success">Available</span>
                @elseif($room->status==='occupied')
                  <span class="badge text-bg-dark">Occupied</span>
                @else
                  <span class="badge text-bg-warning">Maintenance</span>
                @endif
              </div>
            </div>

            <div class="d-flex flex-wrap gap-2 small mb-2">
              <span class="item-chip">Capacity: <strong>{{ $room->capacity }}</strong></span>
              <span class="item-chip">Slots: <strong>{{ $slotsAvailable }}/{{ $room->capacity }}</strong></span>
              <span class="item-chip">Price: <strong>₱{{ number_format($room->price, 2) }}</strong></span>
            </div>

            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-2">
              <div class="small mb-0">
                <span class="text-muted me-1">Tenant:</span>
                @if($room->current_tenant)
                  <span class="fw-medium">{{ $room->current_tenant->full_name }}</span>
                  <span class="text-muted">(ID hidden)</span>
                @else
                  <span class="text-muted">No tenant</span>
                @endif
              </div>

              <div class="d-flex flex-wrap gap-2 justify-content-end">
                <a href="{{ route('landlord.properties.rooms.edit', [$property->id, $room->id]) }}" class="btn btn-sm btn-outline-brand">Edit</a>
                <form action="{{ route('landlord.properties.rooms.destroy', [$property->id, $room->id]) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this room?');">
                  @csrf
                  @method('DELETE')
                  <button class="btn btn-sm btn-outline-danger">Delete</button>
                </form>
              </div>
            </div>
          </div>
        @empty
          <div class="text-center text-muted py-4">No rooms yet. <a href="{{ route('landlord.properties.rooms.create', $property->id) }}">Add one</a>.</div>
        @endforelse
      </div>
    </div>
  </div>
  <div class="col-lg-5">
    <div class="card shadow-sm border-0 rounded-4 mb-4 panel-card">
      <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
        <strong>Active Bookings (Today)</strong>
        <span class="badge text-bg-light border">{{ $activeBookings->count() }}</span>
      </div>
      <div class="card-body p-3 pt-0">
        @forelse($activeBookings as $b)
          <div class="item-row">
            <div class="fw-semibold mb-1">Room {{ $b->room->room_number }}</div>
            <div class="small mb-1"><span class="text-muted">Student:</span> {{ $b->student->full_name }}</div>
            <div class="small text-muted">{{ $b->check_in->format('M d, Y') }} -> {{ $b->check_out->format('M d, Y') }}</div>
          </div>
        @empty
          <div class="text-center text-muted py-4">No active bookings today.</div>
        @endforelse
      </div>
    </div>

    <div class="card shadow-sm border-0 rounded-4 panel-card">
      <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
        <strong>Pending Requests</strong>
        <span class="badge text-bg-light border">{{ $pendingBookings->count() }}</span>
      </div>
      <div class="card-body p-3 pt-0">
        @forelse($pendingBookings as $b)
          <div class="item-row">
            <div class="d-flex flex-wrap justify-content-between gap-2 mb-2">
              <div class="fw-semibold">Room {{ $b->room->room_number }}</div>
              <div class="small text-muted">{{ $b->check_in->format('M d, Y') }} -> {{ $b->check_out->format('M d, Y') }}</div>
            </div>
            <div class="small mb-3"><span class="text-muted">Student:</span> {{ $b->student->full_name }}</div>
            <div class="d-flex flex-wrap gap-2">
              <form action="{{ route('landlord.bookings.approve', $b->id) }}" method="POST" class="d-inline">@csrf<button class="btn btn-sm btn-success">Approve</button></form>
              <form action="{{ route('landlord.bookings.reject', $b->id) }}" method="POST" class="d-inline">@csrf<button class="btn btn-sm btn-outline-danger">Reject</button></form>
            </div>
          </div>
        @empty
          <div class="text-center text-muted py-4">No pending requests.</div>
        @endforelse
      </div>
    </div>
  </div>
</div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin=""/>
<style>
  .property-overview-shell {
    background: linear-gradient(180deg, #ffffff 0%, #fbfdfc 100%);
    border: 1px solid rgba(2,8,20,.08);
    border-radius: 1.25rem;
    box-shadow: 0 10px 26px rgba(2,8,20,.06);
    padding: 1.25rem;
  }
  .metric-card {
    border: 1px solid rgba(2,8,20,.08);
    border-radius: 1rem;
    padding: .95rem 1rem;
    background: #fff;
    box-shadow: 0 6px 16px rgba(2,8,20,.04);
    text-align: center;
  }
  .panel-card .card-body {
    max-height: 520px;
    overflow: auto;
  }
  .item-row {
    border: 1px solid rgba(2,8,20,.08);
    border-radius: .9rem;
    background: #fff;
    padding: .8rem;
  }
  .item-row + .item-row {
    margin-top: .65rem;
  }
  .item-chip {
    display: inline-flex;
    align-items: center;
    gap: .25rem;
    border: 1px solid rgba(2,8,20,.12);
    background: rgba(248,250,252,.9);
    border-radius: 999px;
    padding: .3rem .6rem;
  }
</style>
@endpush

@push('scripts')
@if($property->latitude && $property->longitude)
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
