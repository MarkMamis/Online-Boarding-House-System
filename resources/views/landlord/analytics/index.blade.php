@extends('layouts.landlord')

@section('content')
<div class="glass-card rounded-4 p-4 p-md-5">
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="h4 mb-0">Analytics & Insights</h2>
    <small class="text-muted">Last updated: {{ now()->format('M j, Y g:i A') }}</small>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<!-- Occupancy Overview -->
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card shadow-sm">
            <div class="card-body text-center">
                <h4 class="mb-1">{{ $totalRooms }}</h4>
                <small class="text-muted">Total Rooms</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm border-success">
            <div class="card-body text-center">
                <h4 class="text-success mb-1">{{ $occupiedRooms }}</h4>
                <small class="text-muted">Occupied</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm border-primary">
            <div class="card-body text-center">
                <h4 class="text-primary mb-1">{{ $availableRooms }}</h4>
                <small class="text-muted">Available</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm border-warning">
            <div class="card-body text-center">
                <h4 class="text-warning mb-1">{{ $maintenanceRooms }}</h4>
                <small class="text-muted">Maintenance</small>
            </div>
        </div>
    </div>
</div>

<!-- Occupancy Rate -->
<div class="card shadow-sm mb-4">
    <div class="card-header">
        <h5 class="mb-0">Occupancy Rate</h5>
    </div>
    <div class="card-body">
        <div class="d-flex align-items-center">
            <div class="progress flex-grow-1 me-3" style="height: 20px;">
                <div class="progress-bar bg-success" role="progressbar" style="width: {{ $occupancyRate }}%" aria-valuenow="{{ $occupancyRate }}" aria-valuemin="0" aria-valuemax="100"></div>
            </div>
            <span class="fw-bold">{{ $occupancyRate }}%</span>
        </div>
    </div>
</div>

<!-- Revenue Overview -->
<div class="row g-4 mb-4">
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="mb-0">Monthly Revenue (Last 30 Days)</h5>
            </div>
            <div class="card-body">
                <h3 class="text-success mb-0">₱{{ number_format($monthlyRevenue, 2) }}</h3>
                <small class="text-muted">Based on approved bookings</small>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="mb-0">Booking Trends (Last 7 Days)</h5>
            </div>
            <div class="card-body">
                <canvas id="bookingChart" width="400" height="200"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Top Performing Properties -->
<div class="card shadow-sm mb-4">
    <div class="card-header">
        <h5 class="mb-0">Top Performing Properties</h5>
    </div>
    <div class="card-body">
        @if($topProperties->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="small text-uppercase">
                        <tr>
                            <th>Property Name</th>
                            <th>Address</th>
                            <th>Total Bookings</th>
                            <th>Occupancy Rate</th>
                        </tr>
                    </thead>
                    <tbody class="small">
                        @foreach($topProperties as $property)
                            <tr>
                                <td>{{ $property->name }}</td>
                                <td>{{ $property->address }}</td>
                                <td>{{ $property->booking_count }}</td>
                                <td>
                                    @php
                                        $occupancy = $property->rooms_total_live > 0 ? round(($property->booking_count / $property->rooms_total_live) * 100, 1) : 0;
                                    @endphp
                                    {{ $occupancy }}%
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-muted mb-0">No booking data available yet.</p>
        @endif
    </div>
</div>

<!-- Property Performance Details -->
<div class="card shadow-sm">
    <div class="card-header">
        <h5 class="mb-0">Property Performance Details</h5>
    </div>
    <div class="card-body">
        @if($properties->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="small text-uppercase">
                        <tr>
                            <th>Property</th>
                            <th>Total Rooms</th>
                            <th>Occupied</th>
                            <th>Available</th>
                            <th>Maintenance</th>
                            <th>Occupancy %</th>
                        </tr>
                    </thead>
                    <tbody class="small">
                        @foreach($properties as $property)
                            <tr>
                                <td>{{ $property->name }}</td>
                                <td>{{ $property->total_rooms }}</td>
                                <td>{{ $property->occupied_rooms }}</td>
                                <td>{{ $property->available_rooms }}</td>
                                <td>{{ $property->maintenance_rooms }}</td>
                                <td>
                                    @php
                                        $rate = $property->total_rooms > 0 ? round(($property->occupied_rooms / $property->total_rooms) * 100, 1) : 0;
                                    @endphp
                                    <span class="badge bg-{{ $rate > 70 ? 'success' : ($rate > 30 ? 'warning' : 'danger') }}">{{ $rate }}%</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-muted mb-0">No properties found.</p>
        @endif
    </div>
</div>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('bookingChart').getContext('2d');
    const bookingData = @json($weeklyBookings);

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: bookingData.map(item => new Date(item.date).toLocaleDateString()),
            datasets: [{
                label: 'Bookings',
                data: bookingData.map(item => item.count),
                borderColor: '#0ea5a3',
                backgroundColor: 'rgba(14, 165, 163, 0.1)',
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });
});
</script>
@endsection