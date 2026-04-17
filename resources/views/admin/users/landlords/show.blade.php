@extends('layouts.admin')

@section('title', 'Landlord Details - ' . $user->full_name)

@section('content')
<style>
    .landlord-detail-shell {
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
    .onboarding-grid .metric-tile { padding: .8rem .85rem; }
    .onboarding-grid .metric-value { font-size: 1.2rem; }
    .table thead th {
        font-size: .78rem;
        text-transform: uppercase;
        letter-spacing: .05em;
        color: rgba(2, 8, 20, .62);
        background: rgba(248, 250, 252, .96);
    }
    .property-block + .property-block {
        border-top: 1px solid rgba(2, 8, 20, .08);
    }
    .info-grid p { margin-bottom: .5rem; }
    .permit-card {
        border: 1px solid rgba(2, 8, 20, .08);
        border-radius: .9rem;
        background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
        padding: .85rem .9rem;
    }
    .permit-label {
        font-size: .75rem;
        text-transform: uppercase;
        letter-spacing: .05em;
        color: rgba(2, 8, 20, .55);
    }
    .permit-value {
        font-weight: 600;
        color: #0f172a;
    }
    .cockpit-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: .65rem;
    }
    .cockpit-tile {
        border: 1px solid rgba(2, 8, 20, .08);
        border-radius: .8rem;
        background: #fff;
        padding: .68rem .75rem;
        min-width: 0;
    }
    .cockpit-label {
        font-size: .72rem;
        text-transform: uppercase;
        letter-spacing: .05em;
        color: rgba(2, 8, 20, .58);
        font-weight: 700;
        margin-bottom: .2rem;
    }
    .cockpit-value {
        font-size: 1.2rem;
        font-weight: 700;
        color: #0f172a;
    }
    .onboarding-strip {
        border: 1px dashed rgba(2, 8, 20, .16);
        border-radius: .8rem;
        background: rgba(248, 250, 252, .85);
        padding: .72rem .8rem;
        margin-top: .8rem;
    }
    .onboarding-track {
        width: 100%;
        height: 8px;
        border-radius: 999px;
        background: rgba(2, 8, 20, .1);
        overflow: hidden;
        margin: .4rem 0 .55rem;
    }
    .onboarding-fill {
        height: 100%;
        border-radius: 999px;
        background: linear-gradient(90deg, #16a34a 0%, #22c55e 100%);
    }
    .stage-pills {
        display: flex;
        flex-wrap: wrap;
        gap: .45rem;
    }
    .stage-pill {
        border: 1px solid rgba(2, 8, 20, .1);
        border-radius: 999px;
        background: #fff;
        padding: .26rem .64rem;
        font-size: .78rem;
        font-weight: 600;
        color: #334155;
    }
    .section-subtitle {
        font-size: .82rem;
        color: rgba(2, 8, 20, .58);
    }
    .next-step-bar {
        border: 1px solid rgba(20, 83, 45, .2);
        border-radius: .8rem;
        background: rgba(240, 253, 244, .72);
        padding: .62rem .72rem;
        margin-top: .75rem;
    }
    .next-step-bar a {
        color: #14532d;
        font-weight: 700;
        text-decoration: none;
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
    .profile-item-label {
        font-size: .72rem;
        text-transform: uppercase;
        letter-spacing: .05em;
        color: rgba(2, 8, 20, .55);
        font-weight: 700;
        margin-bottom: .12rem;
    }
    .profile-item-value {
        font-size: .96rem;
        color: #0f172a;
        font-weight: 600;
    }
    .compliance-box {
        border: 1px solid rgba(2, 8, 20, .08);
        border-radius: .9rem;
        background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
        padding: .85rem;
        height: 100%;
    }
    .compliance-actions {
        display: flex;
        flex-wrap: wrap;
        gap: .45rem;
        margin-top: .65rem;
    }
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
    .tab-pane > .section-card:last-child,
    .tab-pane > .row:last-child {
        margin-bottom: 0 !important;
    }
    .empty-compact {
        padding: 1.25rem .9rem !important;
    }
    @media (max-width: 1199.98px) {
        .cockpit-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
        .profile-grid {
            grid-template-columns: 1fr;
        }
    }
    @media (max-width: 767.98px) {
        .landlord-detail-shell { padding: .95rem; }
        .cockpit-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

@php
    $permitProfile = optional($user->landlordProfile);
    $permitStatus = $permitProfile->business_permit_status ?? 'not_submitted';
    $isAccountActive = (bool) ($user->is_active ?? true);
    $focusNextLabel = 'Review permit status';

    $completionPct = $totalOnboarding > 0
        ? (int) round(($completedOnboarding / $totalOnboarding) * 100)
        : 0;

    $permitBadgeClass = $permitStatus === 'approved'
        ? 'text-bg-success'
        : ($permitStatus === 'rejected' ? 'text-bg-danger' : ($permitStatus === 'pending' ? 'text-bg-warning' : 'text-bg-secondary'));

    if ($totalProperties === 0) {
        $focusNextLabel = 'Guide landlord to add first property';
    } elseif ($totalTenants === 0) {
        $focusNextLabel = 'Check room readiness and occupancy';
    }
@endphp

<div class="landlord-detail-shell">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <div class="text-uppercase small muted fw-semibold">Landlord Account</div>
            <h1 class="h4 mb-1">{{ $user->full_name }}</h1>
            <div class="muted small">Landlord details, occupancy, onboarding, and active tenant records.</div>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('admin.users.landlords') }}" class="btn btn-outline-secondary rounded-pill px-3">
                Back to Landlords
            </a>
        </div>
    </div>

    <ul class="nav nav-pills management-tabs mb-3" id="landlordManageTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="tab-overview-btn" data-bs-toggle="pill" data-bs-target="#tab-overview" type="button" role="tab" aria-controls="tab-overview" aria-selected="true">Overview</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-tenants-btn" data-bs-toggle="pill" data-bs-target="#tab-tenants" type="button" role="tab" aria-controls="tab-tenants" aria-selected="false">Current Tenants</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-portfolio-btn" data-bs-toggle="pill" data-bs-target="#tab-portfolio" type="button" role="tab" aria-controls="tab-portfolio" aria-selected="false">Portfolio</button>
        </li>
    </ul>

    <div class="tab-content" id="landlordManageTabsContent">
        <div class="tab-pane fade show active" id="tab-overview" role="tabpanel" aria-labelledby="tab-overview-btn" tabindex="0">
            <div class="row g-3 mb-4">
                <div class="col-lg-8">
                    <div class="section-card h-100">
                        <div class="section-header d-flex justify-content-between align-items-center gap-2">
                            <div>
                                <div class="fw-semibold"><i class="bi bi-speedometer2 me-1"></i> Management Snapshot</div>
                                <div class="section-subtitle">Core operational signals and onboarding momentum</div>
                            </div>
                            <span class="badge {{ $isAccountActive ? 'text-bg-success' : 'text-bg-danger' }}">{{ $isAccountActive ? 'Active Account' : 'Inactive Account' }}</span>
                        </div>
                        <div class="p-3">
                            <div class="cockpit-grid">
                                <div class="cockpit-tile">
                                    <div class="cockpit-label">Current Tenants</div>
                                    <div class="cockpit-value">{{ number_format($totalTenants) }}</div>
                                </div>
                                <div class="cockpit-tile">
                                    <div class="cockpit-label">Properties</div>
                                    <div class="cockpit-value">{{ number_format($totalProperties) }}</div>
                                </div>
                                <div class="cockpit-tile">
                                    <div class="cockpit-label">Occupied Rooms</div>
                                    <div class="cockpit-value">{{ number_format($occupiedRooms) }}/{{ number_format($totalRooms) }}</div>
                                </div>
                                <div class="cockpit-tile">
                                    <div class="cockpit-label">Occupancy Rate</div>
                                    <div class="cockpit-value">{{ $occupancyRate }}%</div>
                                </div>
                            </div>

                            <div class="onboarding-strip">
                                <div class="d-flex justify-content-between align-items-center gap-2 small">
                                    <strong class="text-dark">Onboarding Completion</strong>
                                    <span>{{ $completedOnboarding }} of {{ $totalOnboarding }} ({{ $completionPct }}%)</span>
                                </div>
                                <div class="onboarding-track">
                                    <div class="onboarding-fill" style="width: {{ $completionPct }}%;"></div>
                                </div>
                                <div class="stage-pills">
                                    <span class="stage-pill">Pending: {{ $pendingOnboarding }}</span>
                                    <span class="stage-pill">Docs: {{ $documentsUploaded }}</span>
                                    <span class="stage-pill">Contract: {{ $contractSigned }}</span>
                                    <span class="stage-pill">Deposit: {{ $depositPaid }}</span>
                                    <span class="stage-pill">Completed: {{ $completedOnboarding }}</span>
                                </div>
                            </div>

                            <div class="next-step-bar small">
                                <strong>Recommended next:</strong>
                                <span>{{ $focusNextLabel }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="section-card h-100">
                        <div class="section-header fw-semibold"><i class="bi bi-lightning me-1"></i> Quick Actions</div>
                        <div class="p-3 d-grid gap-2">
                            <button class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#messageModal" data-receiver-id="{{ $user->id }}" data-receiver-name="{{ $user->full_name }}"><i class="bi bi-envelope me-1"></i> Send Message</button>
                            <button class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#editLandlordModal"><i class="bi bi-pencil-square me-1"></i> Edit Profile</button>
                            <a href="{{ route('admin.permits.index') }}" class="btn btn-outline-secondary"><i class="bi bi-file-earmark-check me-1"></i> Permit Queue</a>
                            <button class="btn {{ ($user->is_active ?? true) ? 'btn-outline-danger' : 'btn-outline-success' }} w-100" data-bs-toggle="modal" data-bs-target="#confirmStatusModal">
                                <i class="bi {{ ($user->is_active ?? true) ? 'bi-slash-circle' : 'bi-check-circle' }} me-1"></i>
                                {{ ($user->is_active ?? true) ? 'Deactivate Account' : 'Activate Account' }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="section-card mb-4">
                <div class="section-header d-flex justify-content-between align-items-center gap-2">
                    <div>
                        <div class="fw-semibold"><i class="bi bi-shield-check me-1"></i> Profile and Compliance</div>
                        <div class="section-subtitle">Landlord identity details and permit review status</div>
                    </div>
                    <span class="badge {{ $permitBadgeClass }}">{{ str_replace('_', ' ', $permitStatus) }}</span>
                </div>
                <div class="p-3">
                    <div class="row g-3">
                        <div class="col-lg-7">
                            <div class="profile-grid">
                                <div class="profile-item">
                                    <div class="profile-item-label">Full Name</div>
                                    <div class="profile-item-value">{{ $user->full_name }}</div>
                                </div>
                                <div class="profile-item">
                                    <div class="profile-item-label">Boarding House</div>
                                    <div class="profile-item-value">{{ $user->boarding_house_name ?: 'Not specified' }}</div>
                                </div>
                                <div class="profile-item">
                                    <div class="profile-item-label">Email</div>
                                    <div class="profile-item-value">{{ $user->email }}</div>
                                </div>
                                <div class="profile-item">
                                    <div class="profile-item-label">Contact</div>
                                    <div class="profile-item-value">{{ $user->contact_number ?: 'Not provided' }}</div>
                                </div>
                                <div class="profile-item">
                                    <div class="profile-item-label">Registered</div>
                                    <div class="profile-item-value">{{ $user->created_at->format('F d, Y') }}</div>
                                </div>
                                <div class="profile-item">
                                    <div class="profile-item-label">Last Updated</div>
                                    <div class="profile-item-value">{{ $user->updated_at->format('F d, Y') }}</div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-5">
                            <div class="compliance-box">
                                <div class="permit-label mb-1">Business Permit</div>
                                @if(filled($permitProfile->business_permit_path))
                                    <div class="permit-value mb-2">Uploaded</div>
                                    <div class="small text-muted mb-2">
                                        <strong>Last Reviewed:</strong>
                                        {{ filled($permitProfile->business_permit_reviewed_at) ? $permitProfile->business_permit_reviewed_at->format('M d, Y h:i A') : 'Not reviewed yet' }}
                                    </div>
                                    <div class="small text-muted">
                                        @if($permitStatus === 'rejected' && filled($permitProfile->business_permit_rejection_reason))
                                            {{ $permitProfile->business_permit_rejection_reason }}
                                        @elseif($permitStatus === 'approved')
                                            Permit has been approved.
                                        @elseif($permitStatus === 'pending')
                                            Pending admin review.
                                        @else
                                            No review note available.
                                        @endif
                                    </div>
                                    <div class="compliance-actions">
                                        <a href="{{ asset('storage/' . $permitProfile->business_permit_path) }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary rounded-pill">
                                            <i class="bi bi-file-earmark-pdf me-1"></i>View Permit
                                        </a>
                                        <a href="{{ route('admin.permits.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill">Open Permit Queue</a>
                                    </div>
                                @else
                                    <div class="permit-value text-warning-emphasis mb-2">Not Submitted</div>
                                    <div class="small text-muted">Landlord has not submitted a permit document yet.</div>
                                    <div class="compliance-actions">
                                        <a href="{{ route('admin.permits.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill">Open Permit Queue</a>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="tab-pane fade" id="tab-tenants" role="tabpanel" aria-labelledby="tab-tenants-btn" tabindex="0">
            @if($currentTenants->count() > 0)
                <div class="section-card mb-4">
                    <div class="section-header d-flex justify-content-between align-items-center">
                        <div class="fw-semibold"><i class="bi bi-people me-1"></i> Current Tenants ({{ $currentTenants->count() }})</div>
                        <span class="badge text-bg-success">{{ $currentTenants->count() }} active</span>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th class="ps-3">Tenant Name</th>
                                    <th>Contact Info</th>
                                    <th>Property & Room</th>
                                    <th>Program/Year</th>
                                    <th>Check-in</th>
                                    <th>Check-out</th>
                                    <th class="pe-3">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($currentTenants as $tenant)
                                    <tr>
                                        <td class="ps-3">
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="avatar-pill">{{ strtoupper(substr($tenant['name'], 0, 1)) }}</div>
                                                <div>
                                                    <div class="fw-semibold">{{ $tenant['name'] }}</div>
                                                    <div class="small muted">{{ $tenant['email'] }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ $tenant['contact'] ?: 'Not provided' }}</td>
                                        <td>
                                            <div class="fw-semibold">{{ $tenant['property_name'] }}</div>
                                            <div class="small muted">{{ $tenant['room_number'] }}</div>
                                        </td>
                                        <td>
                                            <div>{{ $tenant['program'] ?: 'Not specified' }}</div>
                                            <div class="small muted">{{ $tenant['year_level'] ? 'Year ' . $tenant['year_level'] : '' }}</div>
                                        </td>
                                        <td>{{ $tenant['check_in'] }}</td>
                                        <td>{{ $tenant['check_out'] }}</td>
                                        <td class="pe-3">
                                            <div class="d-flex gap-1">
                                                <a href="{{ route('admin.users.students.show', $tenant['id']) }}" class="btn btn-sm btn-outline-secondary" title="View tenant details">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <button class="btn btn-sm btn-outline-secondary" title="Send message" data-bs-toggle="modal" data-bs-target="#messageModal" data-receiver-id="{{ $tenant['id'] }}" data-receiver-name="{{ $tenant['name'] }}">
                                                    <i class="bi bi-envelope"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @else
                <div class="section-card mb-4">
                    <div class="section-header fw-semibold"><i class="bi bi-people me-1"></i> Current Tenants</div>
                    <div class="text-center muted empty-compact">No active tenants under this landlord right now.</div>
                </div>
            @endif
        </div>

        <div class="tab-pane fade" id="tab-portfolio" role="tabpanel" aria-labelledby="tab-portfolio-btn" tabindex="0">
            <div class="section-card" id="portfolioSection">
                <div class="section-header fw-semibold"><i class="bi bi-buildings me-1"></i> Properties & Current Tenants</div>
                <div>
                    @forelse($properties as $property)
                        <div class="property-block p-3 p-lg-4">
                            <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
                                <div>
                                    <h6 class="mb-1">{{ $property->name }}</h6>
                                    <p class="small muted mb-0">{{ $property->address }}</p>
                                </div>
                                <div class="text-lg-end">
                                    <span class="badge text-bg-primary">{{ $property->current_tenants }} tenants</span>
                                    <div class="small muted mt-1">{{ $property->occupied_rooms }}/{{ $property->total_rooms }} rooms occupied</div>
                                </div>
                            </div>

                            @if($property->current_tenants > 0)
                                <div class="table-responsive">
                                    <table class="table table-sm mb-0">
                                        <thead>
                                            <tr>
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
                                                            <div class="small muted">PHP {{ number_format($room->price, 2) }}/month</div>
                                                        </td>
                                                        <td>
                                                            <div>{{ $booking->student->full_name }}</div>
                                                            <div class="small muted">{{ $booking->student->email }}</div>
                                                        </td>
                                                        <td>{{ $booking->student->contact_number ?: 'N/A' }}</td>
                                                        <td>{{ $booking->check_in->format('M d, Y') }}</td>
                                                        <td>{{ $booking->check_out->format('M d, Y') }}</td>
                                                        <td><span class="badge text-bg-success">Active</span></td>
                                                    </tr>
                                                @endforeach
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center muted empty-compact">No current tenants in this property.</div>
                            @endif
                        </div>
                    @empty
                        <div class="text-center muted py-5">
                            <div class="h6 mb-1">No Properties Found</div>
                            <p class="mb-0">This landlord has not added any properties yet.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="editLandlordModal" tabindex="-1" aria-labelledby="editLandlordModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.users.landlords.update', $user) }}">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title" id="editLandlordModalLabel">Edit Landlord Profile</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="full_name" class="form-control" value="{{ $user->full_name }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="{{ $user->email }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Contact Number</label>
                        <input type="text" name="contact_number" class="form-control" value="{{ $user->contact_number }}">
                    </div>
                    <div>
                        <label class="form-label">Boarding House Name</label>
                        <input type="text" name="boarding_house_name" class="form-control" value="{{ $user->boarding_house_name }}">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-brand">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="messageModal" tabindex="-1" aria-labelledby="messageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.messages.store') }}">
                @csrf
                <input type="hidden" name="receiver_id" id="messageReceiverId">
                <div class="modal-header">
                    <h5 class="modal-title" id="messageModalLabel">Send Message</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="small muted mb-2">To: <span id="messageReceiverName" class="fw-semibold text-dark">Recipient</span></div>
                    <label class="form-label">Message</label>
                    <textarea name="body" class="form-control" rows="4" maxlength="2000" required placeholder="Type your message here..."></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-brand">Send Message</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('messageModal');
    if (!modal) return;

    modal.addEventListener('show.bs.modal', (event) => {
        const trigger = event.relatedTarget;
        if (!trigger) return;

        const receiverId = trigger.getAttribute('data-receiver-id') || '';
        const receiverName = trigger.getAttribute('data-receiver-name') || 'Recipient';

        const idInput = document.getElementById('messageReceiverId');
        const nameLabel = document.getElementById('messageReceiverName');

        if (idInput) idInput.value = receiverId;
        if (nameLabel) nameLabel.textContent = receiverName;
    });
});
</script>

<div class="modal fade" id="confirmStatusModal" tabindex="-1" aria-labelledby="confirmStatusModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.users.landlords.status', $user) }}">
                @csrf
                <div class="modal-header border-0">
                    <h5 class="modal-title" id="confirmStatusModalLabel">
                        {{ ($user->is_active ?? true) ? 'Deactivate Account' : 'Activate Account' }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert {{ ($user->is_active ?? true) ? 'alert-warning' : 'alert-info' }} mb-3" role="alert">
                        <i class="bi {{ ($user->is_active ?? true) ? 'bi-exclamation-triangle' : 'bi-info-circle' }} me-2"></i>
                        {{ ($user->is_active ?? true) ? 'You are about to deactivate this landlord account.' : 'You are about to activate this landlord account.' }}
                    </div>
                    <p class="mb-2 text-muted">
                        @if($user->is_active ?? true)
                            Once deactivated, this landlord will not be able to log in or manage their properties and tenants. The account can be reactivated later.
                        @else
                            Once activated, this landlord will be able to log in and manage their properties and tenants normally.
                        @endif
                    </p>
                    <p class="mb-0 fw-semibold">
                        <strong>{{ $user->full_name }}</strong><br>
                        <span class="small text-muted">{{ $user->email }}</span>
                    </p>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn {{ ($user->is_active ?? true) ? 'btn-danger' : 'btn-success' }}">
                        <i class="bi {{ ($user->is_active ?? true) ? 'bi-slash-circle' : 'bi-check-circle' }} me-1"></i>
                        {{ ($user->is_active ?? true) ? 'Yes, Deactivate' : 'Yes, Activate' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection


