@extends('layouts.admin')

@section('title', 'Student Details - ' . $user->full_name)

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">
                            <i class="fas fa-user-graduate me-2"></i>
                            Student Details: {{ $user->full_name }}
                        </h4>
                        <div>
                            <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-light btn-sm me-2">
                                <i class="fas fa-home me-1"></i>Dashboard
                            </a>
                            <a href="{{ route('admin.users.students') }}" class="btn btn-light btn-sm">
                                <i class="fas fa-arrow-left me-1"></i>Back to Students
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Student Information -->
                        <div class="col-lg-8">
                            <div class="card border-0 shadow-sm mb-4">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0">
                                        <i class="fas fa-id-card me-2"></i>Personal Information
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Full Name</label>
                                                <p class="mb-0">{{ $user->full_name ?: 'Not provided' }}</p>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Username</label>
                                                <p class="mb-0">{{ $user->name }}</p>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Email</label>
                                                <p class="mb-0">{{ $user->email }}</p>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Contact Number</label>
                                                <p class="mb-0">{{ $user->contact_number ?: 'Not provided' }}</p>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Student ID</label>
                                                <p class="mb-0">{{ $user->student_id ?: 'Not provided' }}</p>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Course/Program</label>
                                                <p class="mb-0">{{ $user->course ?: 'Not provided' }}</p>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Year Level</label>
                                                <p class="mb-0">{{ $user->year_level ?: 'Not provided' }}</p>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Birth Date</label>
                                                <p class="mb-0">{{ $user->birth_date ? $user->birth_date->format('M j, Y') : 'Not provided' }}</p>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Address</label>
                                                <p class="mb-0">{{ $user->address ?: 'Not provided' }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Emergency Contact Information -->
                            <div class="card border-0 shadow-sm mb-4">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0">
                                        <i class="fas fa-phone-alt me-2"></i>Emergency Contact
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Emergency Contact Name</label>
                                                <p class="mb-0">{{ $user->emergency_contact_name ?: 'Not provided' }}</p>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Emergency Contact Number</label>
                                                <p class="mb-0">{{ $user->emergency_contact_number ?: 'Not provided' }}</p>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Emergency Contact Relationship</label>
                                                <p class="mb-0">{{ $user->emergency_contact_relationship ?: 'Not provided' }}</p>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Guardian Name</label>
                                                <p class="mb-0">{{ $user->guardian_name ?: 'Not provided' }}</p>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Guardian Contact</label>
                                                <p class="mb-0">{{ $user->guardian_contact ?: 'Not provided' }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Medical Information -->
                            <div class="card border-0 shadow-sm mb-4">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0">
                                        <i class="fas fa-heartbeat me-2"></i>Medical Information
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Blood Type</label>
                                                <p class="mb-0">{{ $user->blood_type ?: 'Not provided' }}</p>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Allergies</label>
                                                <p class="mb-0">{{ $user->allergies ?: 'None specified' }}</p>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Medical Conditions</label>
                                                <p class="mb-0">{{ $user->medical_conditions ?: 'None specified' }}</p>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Medications</label>
                                                <p class="mb-0">{{ $user->medications ?: 'None specified' }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Sidebar Information -->
                        <div class="col-lg-4">
                            <!-- Account Status -->
                            <div class="card border-0 shadow-sm mb-4">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">
                                        <i class="fas fa-info-circle me-2"></i>Account Status
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Registration Date</label>
                                        <p class="mb-0">{{ $user->created_at->format('M j, Y \a\t g:i A') }}</p>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Last Updated</label>
                                        <p class="mb-0">{{ $user->updated_at->format('M j, Y \a\t g:i A') }}</p>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Email Verified</label>
                                        <p class="mb-0">
                                            <span class="badge bg-{{ $user->email_verified_at ? 'success' : 'warning' }}">
                                                {{ $user->email_verified_at ? 'Verified' : 'Unverified' }}
                                            </span>
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Current Accommodation -->
                            <div class="card border-0 shadow-sm mb-4">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">
                                        <i class="fas fa-home me-2"></i>Current Accommodation
                                    </h6>
                                </div>
                                <div class="card-body">
                                    @if($currentBooking)
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Property</label>
                                            <p class="mb-0">{{ $currentBooking->room->property->name }}</p>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Room</label>
                                            <p class="mb-0">{{ $currentBooking->room->room_number }}</p>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Check-in Date</label>
                                            <p class="mb-0">{{ \Carbon\Carbon::parse($currentBooking->check_in)->format('M j, Y') }}</p>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Check-out Date</label>
                                            <p class="mb-0">{{ \Carbon\Carbon::parse($currentBooking->check_out)->format('M j, Y') }}</p>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Monthly Rent</label>
                                            <p class="mb-0">₱{{ number_format($currentBooking->room->monthly_rent, 2) }}</p>
                                        </div>
                                    @else
                                        <p class="text-muted mb-0">No current accommodation</p>
                                    @endif
                                </div>
                            </div>

                            <!-- Activity Summary -->
                            <div class="card border-0 shadow-sm mb-4">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">
                                        <i class="fas fa-chart-bar me-2"></i>Activity Summary
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Total Bookings</label>
                                        <p class="mb-0">{{ $bookingHistory->count() }}</p>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Messages Sent</label>
                                        <p class="mb-0">{{ $messagesSent }}</p>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Messages Received</label>
                                        <p class="mb-0">{{ $messagesReceived }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Booking History -->
                    @if($bookingHistory->count() > 0)
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">
                                <i class="fas fa-history me-2"></i>Booking History
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Property</th>
                                            <th>Room</th>
                                            <th>Check-in</th>
                                            <th>Check-out</th>
                                            <th>Status</th>
                                            <th>Booking Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($bookingHistory as $booking)
                                        <tr>
                                            <td>
                                                <div class="fw-semibold">{{ $booking->room->property->name }}</div>
                                                <small class="text-muted">{{ $booking->room->property->address }}</small>
                                            </td>
                                            <td>{{ $booking->room->room_number }}</td>
                                            <td>{{ \Carbon\Carbon::parse($booking->check_in)->format('M j, Y') }}</td>
                                            <td>{{ \Carbon\Carbon::parse($booking->check_out)->format('M j, Y') }}</td>
                                            <td>
                                                <span class="badge bg-{{ $booking->status === 'approved' ? 'success' : ($booking->status === 'pending' ? 'warning' : 'danger') }}">
                                                    {{ ucfirst($booking->status) }}
                                                </span>
                                            </td>
                                            <td class="text-muted small">{{ $booking->created_at->format('M j, Y') }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection