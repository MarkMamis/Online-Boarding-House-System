@extends('layouts.admin')

@section('title', 'Properties Management - Admin Panel')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-1">
                        <i class="fas fa-building me-2"></i>Properties Management
                    </h1>
                    <p class="text-muted mb-0">Overview of all landlord properties and their locations</p>
                </div>
                <div>
                    <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i>Back to Dashboard
                    </a>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row g-4 mb-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body text-center">
                            <h4 class="card-title mb-0">{{ $totalProperties }}</h4>
                            <p class="card-text mb-0">Total Properties</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body text-center">
                            <h4 class="card-title mb-0">{{ $totalLandlords }}</h4>
                            <p class="card-text mb-0">Active Landlords</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body text-center">
                            <h4 class="card-title mb-0">{{ $occupiedRooms }}/{{ $totalRooms }}</h4>
                            <p class="card-text mb-0">Occupied Rooms</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-white">
                        <div class="card-body text-center">
                            <h4 class="card-title mb-0">{{ $availableRooms }}</h4>
                            <p class="card-text mb-0">Available Rooms</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Properties Map -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-map-marked-alt me-2"></i>Properties Location Map
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div id="properties-map" style="height: 400px; width: 100%;"></div>
                </div>
            </div>

            <!-- Properties Table -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-list me-2"></i>All Properties
                    </h5>
                    <div class="input-group" style="width: 300px;">
                        <input type="text" class="form-control" id="searchInput" placeholder="Search properties...">
                        <button class="btn btn-outline-secondary" type="button" id="searchBtn">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0" id="propertiesTable">
                            <thead class="table-light">
                                <tr>
                                    <th>Property Name</th>
                                    <th>Landlord</th>
                                    <th>Location</th>
                                    <th>Rooms</th>
                                    <th>Occupancy</th>
                                    <th>Approval</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($properties as $property)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-circle bg-primary text-white me-3" style="width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold;">
                                                <i class="fas fa-building"></i>
                                            </div>
                                            <div>
                                                <div class="fw-bold">{{ $property->name }}</div>
                                                <div class="text-muted small">Added {{ $property->created_at->format('M d, Y') }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div>{{ $property->landlord->full_name }}</div>
                                        <div class="text-muted small">{{ $property->landlord->email }}</div>
                                    </td>
                                    <td>
                                        <div class="fw-bold">{{ $property->address }}</div>
                                        @if($property->latitude && $property->longitude)
                                            <div class="text-muted small">
                                                <i class="fas fa-map-marker-alt text-danger me-1"></i>
                                                {{ number_format($property->latitude, 6) }}, {{ number_format($property->longitude, 6) }}
                                            </div>
                                        @else
                                            <div class="text-muted small">
                                                <i class="fas fa-exclamation-triangle text-warning me-1"></i>
                                                Location not set
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="fw-bold">{{ $property->occupied_rooms }}/{{ $property->total_rooms }}</div>
                                        <div class="text-muted small">{{ $property->available_rooms }} available</div>
                                    </td>
                                    <td>
                                        <div class="progress" style="width: 80px;">
                                            <div class="progress-bar {{ $property->occupancy_rate >= 80 ? 'bg-danger' : ($property->occupancy_rate >= 60 ? 'bg-warning' : 'bg-success') }}"
                                                 role="progressbar"
                                                 style="width: {{ $property->occupancy_rate }}%"
                                                 aria-valuenow="{{ $property->occupancy_rate }}"
                                                 aria-valuemin="0"
                                                 aria-valuemax="100">
                                            </div>
                                        </div>
                                        <div class="text-muted small mt-1">{{ $property->occupancy_rate }}%</div>
                                    </td>
                                    <td>
                                        @if(($property->approval_status ?? 'pending') === 'approved')
                                            <span class="badge bg-success">Approved</span>
                                        @elseif(($property->approval_status ?? 'pending') === 'rejected')
                                            <span class="badge bg-danger">Rejected</span>
                                            @if(!empty($property->rejection_reason))
                                                <div class="text-muted small mt-1">{{ $property->rejection_reason }}</div>
                                            @endif
                                        @else
                                            <span class="badge bg-warning text-dark">Pending</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <button class="btn btn-sm btn-outline-primary" title="View on Map" onclick="focusProperty({{ $property->id }}, {{ $property->latitude ?? 0 }}, {{ $property->longitude ?? 0 }})">
                                                <i class="fas fa-map-marker-alt"></i>
                                            </button>
                                            <a href="{{ route('admin.users.landlords.show', $property->landlord_id) }}" class="btn btn-sm btn-outline-info" title="View Landlord">
                                                <i class="fas fa-user"></i>
                                            </a>

                                            @if(($property->approval_status ?? 'pending') === 'pending')
                                                <form method="POST" action="{{ route('admin.properties.approve', $property) }}" class="d-inline">
                                                    @csrf
                                                    <button class="btn btn-sm btn-outline-success" title="Approve">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-5">
                                        <i class="fas fa-building fa-3x mb-3"></i>
                                        <h6>No Properties Found</h6>
                                        <p class="mb-0">No properties have been added to the system yet.</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize map
    const map = L.map('properties-map').setView([14.5995, 120.9842], 10); // Default to Manila area

    // Add OpenStreetMap tiles
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    // Property markers
    const markers = [];
    const propertiesData = @json($properties);

    propertiesData.forEach(function(property) {
        if (property.latitude && property.longitude) {
            const marker = L.marker([property.latitude, property.longitude])
                .addTo(map)
                .bindPopup(`
                    <div class="text-center">
                        <h6 class="mb-1">${property.name}</h6>
                        <p class="mb-1"><strong>Landlord:</strong> ${property.landlord.full_name}</p>
                        <p class="mb-1"><strong>Address:</strong> ${property.address}</p>
                        <p class="mb-1"><strong>Rooms:</strong> ${property.occupied_rooms}/${property.total_rooms} occupied</p>
                        <p class="mb-0"><strong>Occupancy:</strong> ${property.occupancy_rate}%</p>
                    </div>
                `);

            markers.push({
                id: property.id,
                marker: marker,
                lat: property.latitude,
                lng: property.longitude
            });
        }
    });

    // Fit map to show all markers
    if (markers.length > 0) {
        const group = new L.featureGroup(markers.map(m => m.marker));
        map.fitBounds(group.getBounds().pad(0.1));
    }

    // Focus on property function
    window.focusProperty = function(propertyId, lat, lng) {
        if (lat && lng) {
            map.setView([lat, lng], 15);
            // Find and open the marker popup
            const markerData = markers.find(m => m.id === propertyId);
            if (markerData) {
                markerData.marker.openPopup();
            }
        }
    };

    // Search functionality
    const searchInput = document.getElementById('searchInput');
    const searchBtn = document.getElementById('searchBtn');
    const table = document.getElementById('propertiesTable');
    const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');

    function filterTable() {
        const filter = searchInput.value.toLowerCase();
        for (let i = 0; i < rows.length; i++) {
            const cells = rows[i].getElementsByTagName('td');
            let found = false;
            for (let j = 0; j < cells.length; j++) {
                if (cells[j].textContent.toLowerCase().indexOf(filter) > -1) {
                    found = true;
                    break;
                }
            }
            rows[i].style.display = found ? '' : 'none';
        }
    }

    searchInput.addEventListener('keyup', filterTable);
    searchBtn.addEventListener('click', filterTable);
});
</script>

<style>
.avatar-circle {
    flex-shrink: 0;
}

.progress {
    height: 6px;
}

.table th {
    font-weight: 600;
    font-size: 0.875rem;
}

@media (max-width: 768px) {
    .d-flex.align-items-center .avatar-circle {
        width: 35px;
        height: 35px;
    }

    .table-responsive {
        font-size: 0.875rem;
    }
}
</style>
@endsection