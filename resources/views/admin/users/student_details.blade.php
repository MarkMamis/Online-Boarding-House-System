@extends('layouts.admin')

@section('title', 'Student Details - ' . $user->full_name)

@section('content')
<style>
    .student-detail-shell {
        background: linear-gradient(180deg, #ffffff 0%, #fbfdfc 100%);
        border: 1px solid rgba(2, 8, 20, .08);
        border-radius: 1.25rem;
        box-shadow: 0 10px 26px rgba(2, 8, 20, .06);
        padding: 1.25rem;
    }
    .muted { color: rgba(2, 8, 20, .58); }
    .metric-tile {
        border: 1px solid rgba(2, 8, 20, .08);
        border-radius: 1rem;
        background: #fff;
        box-shadow: 0 8px 18px rgba(2, 8, 20, .05);
        padding: .95rem 1rem;
        height: 100%;
    }
    .metric-label {
        font-size: .78rem;
        text-transform: uppercase;
        letter-spacing: .05em;
        color: rgba(2, 8, 20, .55);
    }
    .metric-value {
        font-size: 1.45rem;
        font-weight: 700;
        color: #166534;
    }
    .section-card {
        border: 1px solid rgba(2, 8, 20, .08);
        border-radius: 1rem;
        background: #fff;
        box-shadow: 0 8px 20px rgba(2, 8, 20, .05);
        overflow: hidden;
    }
    .section-header {
        border-bottom: 1px solid rgba(2, 8, 20, .08);
        background: #fff;
        padding: .85rem 1rem;
    }
    .avatar-pill {
        width: 42px;
        height: 42px;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid rgba(22, 101, 52, .22);
        background: rgba(22, 101, 52, .12);
        color: #166534;
        font-weight: 700;
        flex-shrink: 0;
    }
    .table thead th {
        font-size: .78rem;
        text-transform: uppercase;
        letter-spacing: .05em;
        color: rgba(2, 8, 20, .62);
        background: rgba(248, 250, 252, .96);
    }
    .info-grid p { margin-bottom: .5rem; }
    @media (max-width: 767.98px) {
        .student-detail-shell { padding: .95rem; }
    }
</style>

<div class="student-detail-shell">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <div class="text-uppercase small muted fw-semibold">Student Account</div>
            <h1 class="h4 mb-1">{{ $user->full_name }}</h1>
            <div class="muted small">Student profile, accommodation, activity, and booking records.</div>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('admin.users.students.edit', $user) }}" class="btn btn-outline-primary rounded-pill px-3">
                <i class="bi bi-pencil me-1"></i> Edit Student
            </a>
            <a href="{{ route('admin.users.students') }}" class="btn btn-outline-secondary rounded-pill px-3">
                Back to Students
            </a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="metric-tile">
                <div class="metric-label">Total Bookings</div>
                <div class="metric-value">{{ number_format($bookingHistory->count()) }}</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="metric-tile">
                <div class="metric-label">Messages Sent</div>
                <div class="metric-value">{{ number_format($messagesSent) }}</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="metric-tile">
                <div class="metric-label">Messages Received</div>
                <div class="metric-value">{{ number_format($messagesReceived) }}</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="metric-tile">
                <div class="metric-label">Email Status</div>
                <div class="metric-value">
                    <span class="badge text-bg-{{ $user->email_verified_at ? 'success' : 'warning' }}" style="font-size: .75rem;">
                        {{ $user->email_verified_at ? 'Verified' : 'Unverified' }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-lg-8">
            <div class="section-card h-100">
                <div class="section-header fw-semibold"><i class="bi bi-person-vcard me-1"></i> Student Information</div>
                <div class="p-3 info-grid">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Full Name:</strong> {{ $user->full_name ?: 'Not provided' }}</p>
                            <p><strong>Email:</strong> {{ $user->email }}</p>
                            <p><strong>Gender:</strong> {{ $user->gender ?: 'Not provided' }}</p>
                            <p><strong>Contact:</strong> {{ $user->contact_number ?: 'Not provided' }}</p>
                            <p><strong>Student ID:</strong> {{ $user->student_id ?: 'Not provided' }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Course:</strong> {{ $user->course ?: 'Not provided' }}</p>
                            <p><strong>Year Level:</strong> {{ $user->year_level ?: 'Not provided' }}</p>
                            <p><strong>Birth Date:</strong> {{ $user->birth_date ? $user->birth_date->format('M d, Y') : 'Not provided' }}</p>
                            <p><strong>Address:</strong> {{ $user->address ?: 'Not provided' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="section-card h-100">
                <div class="section-header fw-semibold"><i class="bi bi-info-circle me-1"></i> Account Status</div>
                <div class="p-3">
                    <div class="mb-3">
                        <div class="small text-uppercase muted fw-semibold">Registered</div>
                        <div>{{ $user->created_at->format('M d, Y') }}</div>
                    </div>
                    <div class="mb-3">
                        <div class="small text-uppercase muted fw-semibold">Last Updated</div>
                        <div>{{ $user->updated_at->format('M d, Y') }}</div>
                    </div>
                    <div>
                        <div class="small text-uppercase muted fw-semibold mb-2">Email Verified</div>
                        <span class="badge text-bg-{{ $user->email_verified_at ? 'success' : 'warning' }}">
                            {{ $user->email_verified_at ? '✓ Verified' : '⚠ Unverified' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-lg-8">
            <div class="section-card">
                <div class="section-header fw-semibold"><i class="bi bi-phone me-1"></i> Emergency Contact Information</div>
                <div class="p-3 info-grid">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Contact Name:</strong> {{ $user->emergency_contact_name ?: 'Not provided' }}</p>
                            <p><strong>Contact Number:</strong> {{ $user->emergency_contact_number ?: 'Not provided' }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Relationship:</strong> {{ $user->emergency_contact_relationship ?: 'Not provided' }}</p>
                            <p><strong>Guardian Name:</strong> {{ $user->guardian_name ?: 'Not provided' }}</p>
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-md-6">
                            <p><strong>Guardian Contact:</strong> {{ $user->guardian_contact ?: 'Not provided' }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Parent Contact Name:</strong> {{ $user->parent_contact_name ?: 'Not provided' }}</p>
                            <p><strong>Parent Contact Number:</strong> {{ $user->parent_contact_number ?: 'Not provided' }}</p>
                            <p><strong>Parent Address:</strong> {{ $user->parent_contact_address ?: 'Not provided' }}</p>
                        </div>
                    </div>
                    @if(!empty($user->parent_contact_photo_path))
                        <div class="mt-2">
                            <div class="small text-uppercase muted fw-semibold mb-2">Parent/Guardian ID or Photo</div>
                            <a href="{{ asset('storage/' . $user->parent_contact_photo_path) }}" target="_blank" rel="noopener">
                                <img src="{{ asset('storage/' . $user->parent_contact_photo_path) }}" alt="Parent or guardian photo" style="width:110px;height:110px;object-fit:cover;border-radius:.75rem;border:1px solid rgba(2,8,20,.12);">
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="section-card">
                <div class="section-header fw-semibold"><i class="bi bi-hospital me-1"></i> Medical Information</div>
                <div class="p-3 info-grid">
                    <p><strong>Blood Type:</strong> {{ $user->blood_type ?: 'Not provided' }}</p>
                    <p><strong>Allergies:</strong> {{ $user->allergies ?: 'None specified' }}</p>
                    <p><strong>Medical Conditions:</strong> {{ $user->medical_conditions ?: 'None specified' }}</p>
                    <p class="mb-0"><strong>Medications:</strong> {{ $user->medications ?: 'None specified' }}</p>
                </div>
            </div>
        </div>
    </div>

    @if($currentBooking)
    <div class="section-card mb-4">
        <div class="section-header fw-semibold"><i class="bi bi-house me-1"></i> Current Accommodation</div>
        <div class="p-3">
            <div class="row">
                <div class="col-md-3">
                    <div style="border-right: 1px solid rgba(2,8,20,.08); padding-right: 1rem;">
                        <div class="small text-uppercase muted fw-semibold">Property</div>
                        <div class="fw-semibold">{{ $currentBooking->room->property->name }}</div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div style="border-right: 1px solid rgba(2,8,20,.08); padding: 0 1rem;">
                        <div class="small text-uppercase muted fw-semibold">Room</div>
                        <div class="fw-semibold">{{ $currentBooking->room->room_number }}</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div style="border-right: 1px solid rgba(2,8,20,.08); padding: 0 1rem;">
                        <div class="small text-uppercase muted fw-semibold">Check-in</div>
                        <div class="small">{{ \Carbon\Carbon::parse($currentBooking->check_in)->format('M d, Y') }}</div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div style="border-right: 1px solid rgba(2,8,20,.08); padding: 0 1rem;">
                        <div class="small text-uppercase muted fw-semibold">Check-out</div>
                        <div class="small">{{ \Carbon\Carbon::parse($currentBooking->check_out)->format('M d, Y') }}</div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div style="padding: 0 1rem;">
                        <div class="small text-uppercase muted fw-semibold">Monthly Rent</div>
                        <div class="fw-semibold text-success">₱{{ number_format($currentBooking->room->monthly_rent, 2) }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    @if($bookingHistory->count() > 0)
    <div class="section-card">
        <div class="section-header d-flex justify-content-between align-items-center">
            <div class="fw-semibold"><i class="bi bi-history me-1"></i> Booking History</div>
            <span class="badge text-bg-secondary">{{ $bookingHistory->count() }} bookings</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th class="ps-3">Property & Room</th>
                        <th>Check-in</th>
                        <th>Check-out</th>
                        <th>Status</th>
                        <th class="pe-3">Booking Date</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($bookingHistory as $booking)
                    <tr>
                        <td class="ps-3">
                            <div class="fw-semibold">{{ $booking->room->property->name }}</div>
                            <div class="small muted">Room {{ $booking->room->room_number }}</div>
                        </td>
                        <td>{{ \Carbon\Carbon::parse($booking->check_in)->format('M d, Y') }}</td>
                        <td>{{ \Carbon\Carbon::parse($booking->check_out)->format('M d, Y') }}</td>
                        <td>
                            <span class="badge text-bg-{{ $booking->status === 'approved' ? 'success' : ($booking->status === 'pending' ? 'warning' : 'danger') }}">
                                {{ ucfirst($booking->status) }}
                            </span>
                        </td>
                        <td class="pe-3 small muted">{{ $booking->created_at->format('M d, Y') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>
@endsection