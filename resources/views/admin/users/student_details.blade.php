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
    .management-tabs {
        gap: .45rem;
    }
    .management-tabs .nav-link {
        border: 1px solid rgba(20, 83, 45, .22);
        border-radius: 999px;
        background: rgba(240, 253, 244, .72);
        color: #14532d;
        font-size: .8rem;
        font-weight: 700;
        padding: .35rem .78rem;
    }
    .management-tabs .nav-link.active {
        background: #166534;
        border-color: #166534;
        color: #fff;
    }
    .overview-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: .65rem;
    }
    .overview-tile {
        border: 1px solid rgba(2, 8, 20, .08);
        border-radius: .8rem;
        background: #fff;
        padding: .68rem .75rem;
        min-width: 0;
    }
    .overview-label {
        font-size: .72rem;
        text-transform: uppercase;
        letter-spacing: .05em;
        color: rgba(2, 8, 20, .58);
        font-weight: 700;
        margin-bottom: .2rem;
    }
    .overview-value {
        font-size: 1.2rem;
        font-weight: 700;
        color: #0f172a;
    }
    .next-step-bar {
        border: 1px solid rgba(20, 83, 45, .2);
        border-radius: .8rem;
        background: rgba(240, 253, 244, .72);
        padding: .62rem .72rem;
        margin-top: .75rem;
    }
    .status-grid {
        display: grid;
        gap: .6rem;
    }
    .status-item {
        border: 1px solid rgba(2, 8, 20, .08);
        border-radius: .75rem;
        background: #fff;
        padding: .55rem .65rem;
    }
    .status-label {
        font-size: .72rem;
        text-transform: uppercase;
        letter-spacing: .05em;
        color: rgba(2, 8, 20, .58);
        font-weight: 700;
        margin-bottom: .12rem;
    }
    .status-value {
        font-size: .95rem;
        font-weight: 600;
        color: #0f172a;
    }
    .stay-grid {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: .65rem;
    }
    .stay-item {
        border: 1px solid rgba(2, 8, 20, .08);
        border-radius: .75rem;
        background: #fff;
        padding: .55rem .65rem;
    }
    .profile-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: .75rem 1rem;
    }
    .profile-item {
        border-bottom: 1px dashed rgba(2, 8, 20, .12);
        padding-bottom: .45rem;
    }
    .profile-label {
        font-size: .72rem;
        text-transform: uppercase;
        letter-spacing: .05em;
        color: rgba(2, 8, 20, .55);
        font-weight: 700;
        margin-bottom: .12rem;
    }
    .profile-value {
        font-size: .96rem;
        color: #0f172a;
        font-weight: 600;
    }
    .empty-note {
        padding: 1.35rem .9rem;
    }
    .tab-pane > .section-card:last-child,
    .tab-pane > .row:last-child {
        margin-bottom: 0 !important;
    }
    @media (max-width: 1199.98px) {
        .overview-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
        .stay-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
        .profile-grid {
            grid-template-columns: 1fr;
        }
    }
    @media (max-width: 767.98px) {
        .student-detail-shell { padding: .95rem; }
        .overview-grid,
        .stay-grid {
            grid-template-columns: 1fr;
        }
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

    @php
        $emailVerified = (bool) $user->email_verified_at;
        $hasEmergencyProfile = filled($user->emergency_contact_name)
            || filled($user->emergency_contact_number)
            || filled($user->parent_contact_name)
            || filled($user->guardian_name);
        $hasMedicalProfile = filled($user->blood_type)
            || filled($user->allergies)
            || filled($user->medical_conditions)
            || filled($user->medications);

        $recommendedAction = 'Review latest booking activity and keep profile updated.';
        if (!$emailVerified) {
            $recommendedAction = 'Follow up email verification to complete account setup.';
        } elseif (!$currentBooking && $bookingHistory->isEmpty()) {
            $recommendedAction = 'Student has no booking yet; verify profile readiness.';
        } elseif (!$currentBooking) {
            $recommendedAction = 'No active stay; review booking history for next action.';
        }
    @endphp

    <ul class="nav nav-pills management-tabs mb-3" id="studentManageTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="tab-overview-btn" data-bs-toggle="pill" data-bs-target="#tab-overview" type="button" role="tab" aria-controls="tab-overview" aria-selected="true">Overview</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-profile-btn" data-bs-toggle="pill" data-bs-target="#tab-profile" type="button" role="tab" aria-controls="tab-profile" aria-selected="false">Profile and Welfare</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-activity-btn" data-bs-toggle="pill" data-bs-target="#tab-activity" type="button" role="tab" aria-controls="tab-activity" aria-selected="false">Booking Activity</button>
        </li>
    </ul>

    <div class="tab-content" id="studentManageTabsContent">
        <div class="tab-pane fade show active" id="tab-overview" role="tabpanel" aria-labelledby="tab-overview-btn" tabindex="0">
            <div class="row g-3 mb-4">
                <div class="col-lg-8">
                    <div class="section-card h-100">
                        <div class="section-header d-flex justify-content-between align-items-center gap-2">
                            <div>
                                <div class="fw-semibold"><i class="bi bi-speedometer2 me-1"></i> Management Snapshot</div>
                                <div class="section-subtitle">Account activity and accommodation status at a glance</div>
                            </div>
                            <span class="badge text-bg-{{ $emailVerified ? 'success' : 'warning' }}">{{ $emailVerified ? 'Email Verified' : 'Email Unverified' }}</span>
                        </div>
                        <div class="p-3">
                            <div class="overview-grid">
                                <div class="overview-tile">
                                    <div class="overview-label">Total Bookings</div>
                                    <div class="overview-value">{{ number_format($bookingHistory->count()) }}</div>
                                </div>
                                <div class="overview-tile">
                                    <div class="overview-label">Messages Sent</div>
                                    <div class="overview-value">{{ number_format($messagesSent) }}</div>
                                </div>
                                <div class="overview-tile">
                                    <div class="overview-label">Messages Received</div>
                                    <div class="overview-value">{{ number_format($messagesReceived) }}</div>
                                </div>
                                <div class="overview-tile">
                                    <div class="overview-label">Active Stay</div>
                                    <div class="overview-value">{{ $currentBooking ? 'Yes' : 'No' }}</div>
                                </div>
                            </div>

                            <div class="next-step-bar small">
                                <strong>Recommended next:</strong>
                                <span>{{ $recommendedAction }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="section-card h-100">
                        <div class="section-header fw-semibold"><i class="bi bi-info-circle me-1"></i> Account Health</div>
                        <div class="p-3 status-grid">
                            <div class="status-item">
                                <div class="status-label">Registered</div>
                                <div class="status-value">{{ $user->created_at->format('M d, Y') }}</div>
                            </div>
                            <div class="status-item">
                                <div class="status-label">Last Updated</div>
                                <div class="status-value">{{ $user->updated_at->format('M d, Y') }}</div>
                            </div>
                            <div class="status-item">
                                <div class="status-label">Emergency Profile</div>
                                <div class="status-value">{{ $hasEmergencyProfile ? 'Has records' : 'Missing details' }}</div>
                            </div>
                            <div class="status-item">
                                <div class="status-label">Medical Profile</div>
                                <div class="status-value">{{ $hasMedicalProfile ? 'Has records' : 'No details yet' }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="section-card mb-4">
                <div class="section-header fw-semibold"><i class="bi bi-house me-1"></i> Current Accommodation</div>
                <div class="p-3">
                    @if($currentBooking)
                        <div class="stay-grid">
                            <div class="stay-item">
                                <div class="status-label">Property</div>
                                <div class="status-value">{{ $currentBooking->room->property->name }}</div>
                            </div>
                            <div class="stay-item">
                                <div class="status-label">Room</div>
                                <div class="status-value">{{ $currentBooking->room->room_number }}</div>
                            </div>
                            <div class="stay-item">
                                <div class="status-label">Check-in</div>
                                <div class="status-value">{{ \Carbon\Carbon::parse($currentBooking->check_in)->format('M d, Y') }}</div>
                            </div>
                            <div class="stay-item">
                                <div class="status-label">Check-out</div>
                                <div class="status-value">{{ \Carbon\Carbon::parse($currentBooking->check_out)->format('M d, Y') }}</div>
                            </div>
                            <div class="stay-item">
                                <div class="status-label">Monthly Rent</div>
                                <div class="status-value text-success">₱{{ number_format((float) ($currentBooking->room->monthly_rent ?? $currentBooking->room->price ?? 0), 2) }}</div>
                            </div>
                        </div>
                    @else
                        <div class="text-center muted empty-note">No active accommodation for this student right now.</div>
                    @endif
                </div>
            </div>
        </div>

        <div class="tab-pane fade" id="tab-profile" role="tabpanel" aria-labelledby="tab-profile-btn" tabindex="0">
            <div class="section-card mb-4">
                <div class="section-header fw-semibold"><i class="bi bi-person-vcard me-1"></i> Student Information</div>
                <div class="p-3">
                    <div class="profile-grid">
                        <div class="profile-item">
                            <div class="profile-label">Full Name</div>
                            <div class="profile-value">{{ $user->full_name ?: 'Not provided' }}</div>
                        </div>
                        <div class="profile-item">
                            <div class="profile-label">Student ID</div>
                            <div class="profile-value">{{ $user->student_id ?: 'Not provided' }}</div>
                        </div>
                        <div class="profile-item">
                            <div class="profile-label">Email</div>
                            <div class="profile-value">{{ $user->email }}</div>
                        </div>
                        <div class="profile-item">
                            <div class="profile-label">Contact</div>
                            <div class="profile-value">{{ $user->contact_number ?: 'Not provided' }}</div>
                        </div>
                        <div class="profile-item">
                            <div class="profile-label">Gender</div>
                            <div class="profile-value">{{ $user->gender ?: 'Not provided' }}</div>
                        </div>
                        <div class="profile-item">
                            <div class="profile-label">Birth Date</div>
                            <div class="profile-value">{{ $user->birth_date ? $user->birth_date->format('M d, Y') : 'Not provided' }}</div>
                        </div>
                        <div class="profile-item">
                            <div class="profile-label">Program</div>
                            <div class="profile-value">{{ $user->program ?: 'Not provided' }}</div>
                        </div>
                        <div class="profile-item">
                            <div class="profile-label">Year Level</div>
                            <div class="profile-value">{{ $user->year_level ?: 'Not provided' }}</div>
                        </div>
                        <div class="profile-item">
                            <div class="profile-label">Address</div>
                            <div class="profile-value">{{ $user->address ?: 'Not provided' }}</div>
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
                                    <p><strong>Relationship:</strong> {{ $user->emergency_contact_relationship ?: 'Not provided' }}</p>
                                    <p><strong>Guardian Name:</strong> {{ $user->guardian_name ?: 'Not provided' }}</p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Guardian Contact:</strong> {{ $user->guardian_contact ?: 'Not provided' }}</p>
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
        </div>

        <div class="tab-pane fade" id="tab-activity" role="tabpanel" aria-labelledby="tab-activity-btn" tabindex="0">
            <div class="section-card mb-4">
                <div class="section-header d-flex justify-content-between align-items-center">
                    <div class="fw-semibold"><i class="bi bi-chat-dots me-1"></i> Communication Activity</div>
                </div>
                <div class="p-3">
                    <div class="overview-grid">
                        <div class="overview-tile">
                            <div class="overview-label">Messages Sent</div>
                            <div class="overview-value">{{ number_format($messagesSent) }}</div>
                        </div>
                        <div class="overview-tile">
                            <div class="overview-label">Messages Received</div>
                            <div class="overview-value">{{ number_format($messagesReceived) }}</div>
                        </div>
                        <div class="overview-tile">
                            <div class="overview-label">Total Conversations</div>
                            <div class="overview-value">{{ number_format($messagesSent + $messagesReceived) }}</div>
                        </div>
                        <div class="overview-tile">
                            <div class="overview-label">Booking Records</div>
                            <div class="overview-value">{{ number_format($bookingHistory->count()) }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="section-card">
                <div class="section-header d-flex justify-content-between align-items-center">
                    <div class="fw-semibold"><i class="bi bi-history me-1"></i> Booking History</div>
                    <span class="badge text-bg-secondary">{{ $bookingHistory->count() }} bookings</span>
                </div>
                @if($bookingHistory->count() > 0)
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
                @else
                    <div class="text-center muted empty-note">No booking history found for this student.</div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection