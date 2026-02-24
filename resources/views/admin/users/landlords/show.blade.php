@extends('layouts.admin')

@section('title', 'Landlord Details - ' . $user->full_name)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h4 mb-1">{{ $user->full_name }}</h1>
                    <div class="text-muted small">Landlord Account Details & Tenant Information</div>
                </div>
                <div>
                    <a href="{{ route('admin.users.landlords') }}" class="btn btn-outline-secondary me-2">
                        <i class="fas fa-arrow-left me-1"></i>Back to Landlords
                    </a>
                    <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-primary">
                        Dashboard
                    </a>
                </div>
            </div>

            <!-- Landlord Overview -->
            <div class="row g-4 mb-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body text-center">
                            <h4 class="card-title mb-0">{{ $totalTenants }}</h4>
                            <p class="card-text mb-0">Current Tenants</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body text-center">
                            <h4 class="card-title mb-0">{{ $totalProperties }}</h4>
                            <p class="card-text mb-0">Properties</p>
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
                            <h4 class="card-title mb-0">{{ $occupancyRate }}%</h4>
                            <p class="card-text mb-0">Occupancy Rate</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Onboarding Statistics -->
            <div class="row g-4 mb-4">
                <div class="col-12">
                    <h5 class="mb-3">Tenant Onboarding Statistics</h5>
                </div>
                <div class="col-md-2">
                    <div class="card bg-secondary text-white">
                        <div class="card-body text-center">
                            <h4 class="card-title mb-0">{{ $totalOnboarding }}</h4>
                            <p class="card-text mb-0 small">Total Onboarding</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card bg-warning text-white">
                        <div class="card-body text-center">
                            <h4 class="card-title mb-0">{{ $pendingOnboarding }}</h4>
                            <p class="card-text mb-0 small">Pending Documents</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card bg-info text-white">
                        <div class="card-body text-center">
                            <h4 class="card-title mb-0">{{ $documentsUploaded }}</h4>
                            <p class="card-text mb-0 small">Documents Uploaded</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card bg-primary text-white">
                        <div class="card-body text-center">
                            <h4 class="card-title mb-0">{{ $contractSigned }}</h4>
                            <p class="card-text mb-0 small">Contract Signed</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card bg-success text-white">
                        <div class="card-body text-center">
                            <h4 class="card-title mb-0">{{ $depositPaid }}</h4>
                            <p class="card-text mb-0 small">Deposit Paid</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card bg-dark text-white">
                        <div class="card-body text-center">
                            <h4 class="card-title mb-0">{{ $completedOnboarding }}</h4>
                            <p class="card-text mb-0 small">Completed</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Landlord Information -->
            <div class="row g-4 mb-4">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Landlord Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Full Name:</strong> {{ $user->full_name }}</p>
                                    <p><strong>Email:</strong> {{ $user->email }}</p>
                                    <p><strong>Contact:</strong> {{ $user->contact_number ?: 'Not provided' }}</p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Boarding House:</strong> {{ $user->boarding_house_name ?: 'Not specified' }}</p>
                                    <p><strong>Registered:</strong> {{ $user->created_at->format('F d, Y') }}</p>
                                    <p><strong>Last Updated:</strong> {{ $user->updated_at->format('F d, Y') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Quick Actions</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <button class="btn btn-outline-primary" disabled>
                                    <i class="fas fa-envelope me-1"></i>Send Message
                                </button>
                                <button class="btn btn-outline-warning" disabled>
                                    <i class="fas fa-edit me-1"></i>Edit Profile
                                </button>
                                <button class="btn btn-outline-danger" disabled>
                                    <i class="fas fa-ban me-1"></i>Deactivate Account
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Current Tenants Section -->
            @if($currentTenants->count() > 0)
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-users me-2"></i>Current Tenants ({{ $currentTenants->count() }})
                    </h5>
                    <span class="badge bg-primary">{{ $currentTenants->count() }} active</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Tenant Name</th>
                                    <th>Contact Info</th>
                                    <th>Property & Room</th>
                                    <th>Course/Year</th>
                                    <th>Check-in Date</th>
                                    <th>Check-out Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($currentTenants as $tenant)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-circle bg-primary text-white me-3" style="width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold;">
                                                {{ strtoupper(substr($tenant['name'], 0, 1)) }}
                                            </div>
                                            <div>
                                                <div class="fw-bold">{{ $tenant['name'] }}</div>
                                                <div class="text-muted small">{{ $tenant['email'] }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <i class="fas fa-phone text-muted me-1"></i>{{ $tenant['contact'] ?: 'Not provided' }}
                                    </td>
                                    <td>
                                        <div class="fw-bold">{{ $tenant['property_name'] }}</div>
                                        <div class="text-muted small">Room {{ $tenant['room_number'] }}</div>
                                    </td>
                                    <td>
                                        <div>{{ $tenant['course'] ?: 'Not specified' }}</div>
                                        <div class="text-muted small">{{ $tenant['year_level'] ? 'Year ' . $tenant['year_level'] : '' }}</div>
                                    </td>
                                    <td>
                                        <i class="fas fa-calendar-check text-success me-1"></i>
                                        {{ $tenant['check_in'] }}
                                    </td>
                                    <td>
                                        <i class="fas fa-calendar-times text-warning me-1"></i>
                                        {{ $tenant['check_out'] }}
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('admin.users.students.show', $tenant['id']) }}" class="btn btn-sm btn-outline-primary" title="View Tenant Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <button class="btn btn-sm btn-outline-secondary" disabled title="Send Message">
                                                <i class="fas fa-envelope"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif

            <!-- Properties & Tenants -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Properties & Current Tenants</h5>
                </div>
                <div class="card-body p-0">
                    @forelse($properties as $property)
                        <div class="border-bottom p-4">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h6 class="mb-1">{{ $property->name }}</h6>
                                    <p class="text-muted small mb-0">{{ $property->address }}</p>
                                </div>
                                <div class="text-end">
                                    <span class="badge bg-primary">{{ $property->current_tenants }} tenants</span>
                                    <div class="text-muted small mt-1">{{ $property->occupied_rooms }}/{{ $property->total_rooms }} rooms occupied</div>
                                </div>
                            </div>

                            @if($property->current_tenants > 0)
                                <div class="table-responsive">
                                    <table class="table table-sm table-borderless">
                                        <thead>
                                            <tr class="table-light">
                                                <th>Room</th>
                                                <th>Tenant</th>
                                                <th>Contact</th>
                                                <th>Check-in</th>
                                                <th>Check-out</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($property->rooms as $room)
                                                @foreach($room->bookings as $booking)
                                                    <tr>
                                                        <td>
                                                            <strong>{{ $room->room_number }}</strong>
                                                            <div class="text-muted small">₱{{ number_format($room->price, 2) }}/month</div>
                                                        </td>
                                                        <td>
                                                            <div>{{ $booking->student->full_name }}</div>
                                                            <div class="text-muted small">{{ $booking->student->email }}</div>
                                                        </td>
                                                        <td>{{ $booking->student->contact_number ?: 'N/A' }}</td>
                                                        <td>{{ $booking->check_in->format('M d, Y') }}</td>
                                                        <td>{{ $booking->check_out->format('M d, Y') }}</td>
                                                        <td>
                                                            <span class="badge bg-success">Active</span>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center text-muted py-3">
                                    <i class="fas fa-users fa-2x mb-2"></i>
                                    <p class="mb-0">No current tenants in this property</p>
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="text-center text-muted py-5">
                            <i class="fas fa-building fa-3x mb-3"></i>
                            <h6>No Properties Found</h6>
                            <p class="mb-0">This landlord hasn't added any properties yet.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection