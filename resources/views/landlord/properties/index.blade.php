@extends('layouts.landlord')

@section('content')
<div class="glass-card rounded-4 p-4 p-md-5">
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="{{ route('landlord.dashboard') }}" class="btn btn-sm btn-outline-secondary rounded-pill me-2">
            ← Back to Dashboard
        </a>
        <h2 class="h4 mb-0 d-inline">Properties</h2>
    </div>
    <a href="{{ route('landlord.properties.create') }}" class="btn btn-sm btn-brand rounded-pill">Add Property</a>
</div>
@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<!-- Properties Map -->
<div class="card shadow-sm mb-4" id="propertiesMapCard" style="display: none;">
  <div class="card-header bg-white">
    <strong>All Properties Location</strong>
  </div>
  <div class="card-body p-0">
    <div id="propertiesMap" style="height:350px; border-radius:0 0 0.375rem 0.375rem;"></div>
  </div>
</div>

<div class="table-responsive bg-white shadow-sm rounded-4 p-2">
    <table class="table table-hover align-middle mb-0">
        <thead class="small text-uppercase">
            <tr>
                <th>Name</th>
                <th>Address</th>
                <th>Location</th>
                <th>Rooms</th>
                <th>Vacant</th>
                <th>Price Range</th>
                <th>Added</th>
                <th class="text-end">Actions</th>
            </tr>
        </thead>
        <tbody class="small">
            @forelse($properties as $prop)
                <tr>
                    <td>{{ $prop->name }}</td>
                    <td>{{ $prop->address }}</td>
                    <td>
                        @if($prop->latitude && $prop->longitude)
                            <span class="badge bg-success">
                                <i class="fas fa-map-marker-alt me-1"></i>Mapped
                            </span>
                        @else
                            <span class="badge bg-warning text-dark">
                                <i class="fas fa-question-circle me-1"></i>Not Set
                            </span>
                        @endif
                    </td>
                    <td>{{ $prop->rooms_total_live }}</td>
                    <td>{{ $prop->rooms_vacant_live }}</td>
                    <td>
                        @if($prop->price_min !== null || $prop->price_max !== null)
                            ₱{{ number_format($prop->price_min,0) }} - ₱{{ number_format($prop->price_max,0) }}
                        @else
                            <span class="text-muted">N/A</span>
                        @endif
                    </td>
                    <td>{{ $prop->created_at->diffForHumans() }}</td>
                    <td class="text-end">
                        <a class="btn btn-sm btn-outline-secondary me-1" href="{{ route('landlord.properties.show', $prop->id) }}">Overview</a>
                        <a class="btn btn-sm btn-outline-brand me-1" href="{{ route('landlord.properties.rooms.index', $prop->id) }}">Manage Rooms</a>
                        <a class="btn btn-sm btn-brand me-1" href="{{ route('landlord.properties.rooms.create', $prop->id) }}">Add Room</a>
                        <a class="btn btn-sm btn-outline-warning me-1" href="{{ route('landlord.properties.edit', $prop->id) }}">Edit</a>
                        <form action="{{ route('landlord.properties.destroy', $prop->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this property? All rooms and bookings under it will be removed.');">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="8" class="text-center text-muted">No properties yet.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin=""/>
@endpush

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    // Check if any properties have coordinates
    const propertiesWithCoords = @json($properties->filter(fn($p) => $p->latitude && $p->longitude));
    
    if (propertiesWithCoords.length > 0) {
        document.getElementById('propertiesMapCard').style.display = 'block';
        
        const map = L.map('propertiesMap');
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);
        
        const bounds = [];
        propertiesWithCoords.forEach(property => {
            const latLng = [property.latitude, property.longitude];
            const marker = L.marker(latLng).addTo(map);
            marker.bindPopup(`
                <strong>${property.name}</strong><br>
                ${property.address}<br>
                <span class="badge bg-primary">${property.rooms_vacant_live} vacant / ${property.rooms_total_live} total rooms</span><br>
                <a href="/landlord/properties/${property.id}" class="btn btn-sm btn-brand mt-2">View Details</a>
            `);
            bounds.push(latLng);
        });
        
        if (bounds.length > 0) {
            map.fitBounds(bounds, { padding: [20, 20] });
        }
    }
});
</script>
@endpush