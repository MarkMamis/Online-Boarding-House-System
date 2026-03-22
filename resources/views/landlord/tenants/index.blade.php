@extends('layouts.landlord')

@section('content')
<div class="glass-card rounded-4 p-4 p-md-5">
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h4 mb-1">Current Tenants</h1>
        <div class="text-muted small">Manage your active tenants and their bookings</div>
    </div>
    <a href="{{ route('landlord.bookings.index') }}" class="btn btn-outline-brand">View All Bookings</a>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Tenant</th>
                        <th>Room</th>
                        <th>Property</th>
                        <th>Check-in</th>
                        <th>Check-out</th>
                        <th>Payment Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tenants as $tenant)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-circle bg-primary text-white me-3" style="width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold;">
                                        {{ strtoupper(substr($tenant->student->full_name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="fw-semibold">{{ $tenant->student->full_name }}</div>
                                        <div class="text-muted small">{{ $tenant->student->email }}</div>
                                        <div class="text-muted small">Emergency: {{ $tenant->student->parent_contact_name ?: ($tenant->student->emergency_contact_name ?: 'Not provided') }}</div>
                                        <div class="text-muted small">Contact: {{ $tenant->student->parent_contact_number ?: ($tenant->student->emergency_contact_number ?: 'Not provided') }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-info">{{ $tenant->room->room_number }}</span>
                            </td>
                            <td>
                                <div class="fw-medium">{{ $tenant->room->property->name }}</div>
                                <div class="text-muted small">{{ $tenant->room->property->address }}</div>
                            </td>
                            <td>{{ $tenant->check_in->format('M j, Y') }}</td>
                            <td>{{ $tenant->check_out->format('M j, Y') }}</td>
                            <td>
                                @if($tenant->payment_status === 'paid')
                                    <span class="badge bg-success">Paid</span>
                                @elseif($tenant->payment_status === 'pending')
                                    <span class="badge bg-warning">Pending</span>
                                @else
                                    <span class="badge bg-secondary">Not Paid</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="mailto:{{ $tenant->student->email }}" class="btn btn-outline-brand" title="Contact Tenant">
                                        <i class="fas fa-envelope"></i>
                                    </a>
                                    <a href="{{ route('landlord.messages.index') }}?to={{ $tenant->student->id }}" class="btn btn-outline-brand" title="Send Message">
                                        <i class="fas fa-comment"></i>
                                    </a>
                                    <a href="{{ route('landlord.properties.show', $tenant->room->property_id) }}" class="btn btn-outline-secondary" title="View Property">
                                        <i class="fas fa-building"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-5">
                                <i class="fas fa-users fa-3x mb-3 text-muted"></i>
                                <h5 class="mb-2">No Current Tenants</h5>
                                <p class="mb-3">You don't have any approved bookings yet.</p>
                                <a href="{{ route('landlord.bookings.index') }}" class="btn btn-brand">Check Booking Requests</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@if($tenants->count() > 0)
<div class="row mt-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body text-center">
                <h4 class="card-title mb-0">{{ $tenants->count() }}</h4>
                <p class="card-text mb-0">Total Tenants</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body text-center">
                <h4 class="card-title mb-0">{{ $tenants->where('payment_status', 'paid')->count() }}</h4>
                <p class="card-text mb-0">Paid Tenants</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body text-center">
                <h4 class="card-title mb-0">{{ $tenants->where('payment_status', 'pending')->count() }}</h4>
                <p class="card-text mb-0">Pending Payments</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body text-center">
                <h4 class="card-title mb-0">{{ $tenants->where('check_out', '>', now())->count() }}</h4>
                <p class="card-text mb-0">Active Leases</p>
            </div>
        </div>
    </div>
</div>
@endif
</div>
@endsection