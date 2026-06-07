@extends('layouts.admin')

@section('title', 'Landlord Approval')

@section('content')
<style>
    .approval-shell {
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
        font-size: .75rem;
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
    .table thead th {
        font-size: .78rem;
        text-transform: uppercase;
        letter-spacing: .05em;
        color: rgba(2, 8, 20, .62);
        background: rgba(248, 250, 252, .96);
        border-bottom: 1px solid rgba(2, 8, 20, .08);
    }
    .table tbody td {
        vertical-align: middle;
    }
    .tab-row {
        display: flex;
        flex-wrap: wrap;
        gap: .6rem;
        margin-bottom: 1rem;
    }
    .tab-link {
        display: inline-flex;
        align-items: center;
        gap: .45rem;
        border-radius: 999px;
        padding: .68rem 1rem;
        border: 1px solid rgba(2, 8, 20, .1);
        text-decoration: none;
        color: #334155;
        font-weight: 700;
        background: #fff;
    }
    .tab-link.active {
        background: #166534;
        color: #fff;
        border-color: #166534;
        box-shadow: 0 10px 22px rgba(22, 101, 52, .18);
    }
    .status-chip {
        font-size: .75rem;
        text-transform: capitalize;
        border-radius: 999px;
        padding: .3rem .6rem;
    }
    .actions-col {
        min-width: 170px;
    }
    .action-grid {
        display: grid;
        gap: .45rem;
        justify-content: start;
    }
    .action-grid.properties-grid {
        grid-template-columns: repeat(4, 42px);
    }
    .action-grid.permits-grid {
        grid-template-columns: repeat(3, 42px);
    }
    .action-btn {
        width: 42px;
        height: 42px;
        padding: 0;
        border-radius: .7rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
    }
    .property-avatar {
        width: 42px;
        height: 42px;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(22, 101, 52, .12);
        color: #166534;
        border: 1px solid rgba(22, 101, 52, .22);
        flex-shrink: 0;
    }
    .reject-modal-note,
    .table-empty {
        color: rgba(2, 8, 20, .58);
    }
    @media (max-width: 767.98px) {
        .approval-shell { padding: .95rem; }
        .actions-col { min-width: 190px; }
    }
</style>

<div class="approval-shell">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <div class="text-uppercase small muted fw-semibold">Approvals</div>
            <h1 class="h4 mb-1"><i class="bi bi-buildings me-2"></i>Landlord Approval</h1>
            <div class="muted small">Review landlord-facing approvals in one place and switch between property and permit queues.</div>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('admin.users.landlords') }}" class="btn btn-outline-secondary rounded-pill px-3">Landlords</a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger border-0 shadow-sm">{{ session('error') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger border-0 shadow-sm">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="tab-row">
        <a href="{{ route('admin.approvals.landlords', ['tab' => 'properties', 'status' => $activeTab === 'properties' ? $statusFilter : null]) }}" class="tab-link {{ $activeTab === 'properties' ? 'active' : '' }}">
            <i class="bi bi-building"></i>
            <span>Property Approval</span>
        </a>
        <a href="{{ route('admin.approvals.landlords', ['tab' => 'permits', 'status' => $activeTab === 'permits' ? $statusFilter : null]) }}" class="tab-link {{ $activeTab === 'permits' ? 'active' : '' }}">
            <i class="bi bi-file-earmark-check"></i>
            <span>Permit Approval</span>
        </a>
    </div>

    @php
        $currentCounts = $activeTab === 'properties' ? ($propertyCounts ?? []) : ($permitCounts ?? []);
        $currentTotal = $activeTab === 'properties'
            ? (($properties ?? null)?->total() ?? 0)
            : (($landlords ?? null)?->total() ?? 0);
    @endphp

    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="metric-tile">
                <div class="metric-label">Pending</div>
                <div class="metric-value">{{ number_format($currentCounts['pending'] ?? 0) }}</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="metric-tile">
                <div class="metric-label">Approved</div>
                <div class="metric-value">{{ number_format($currentCounts['approved'] ?? 0) }}</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="metric-tile">
                <div class="metric-label">Rejected</div>
                <div class="metric-value">{{ number_format($currentCounts['rejected'] ?? 0) }}</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="metric-tile">
                <div class="metric-label">Visible Records</div>
                <div class="metric-value">{{ number_format($currentTotal) }}</div>
            </div>
        </div>
    </div>

    <div class="d-flex flex-wrap gap-2 mb-3">
        <a href="{{ route('admin.approvals.landlords', ['tab' => $activeTab, 'status' => 'pending']) }}" class="btn rounded-pill {{ $statusFilter === 'pending' ? 'btn-success' : 'btn-outline-secondary' }}">Pending</a>
        <a href="{{ route('admin.approvals.landlords', ['tab' => $activeTab, 'status' => 'approved']) }}" class="btn rounded-pill {{ $statusFilter === 'approved' ? 'btn-success' : 'btn-outline-secondary' }}">Approved</a>
        <a href="{{ route('admin.approvals.landlords', ['tab' => $activeTab, 'status' => 'rejected']) }}" class="btn rounded-pill {{ $statusFilter === 'rejected' ? 'btn-success' : 'btn-outline-secondary' }}">Rejected</a>
        <a href="{{ route('admin.approvals.landlords', ['tab' => $activeTab, 'status' => 'all']) }}" class="btn rounded-pill {{ $statusFilter === 'all' ? 'btn-success' : 'btn-outline-secondary' }}">All</a>
    </div>

    @if($activeTab === 'properties')
        @php
            $queueTitle = match($statusFilter) {
                'approved' => 'Approved Property Queue',
                'rejected' => 'Rejected Property Queue',
                'all' => 'All Property Submissions',
                default => 'Pending Property Queue',
            };
        @endphp
        <div class="section-card">
            <div class="section-header d-flex justify-content-between align-items-center gap-2">
                <div class="fw-semibold"><i class="bi bi-hourglass-split me-1"></i>{{ $queueTitle }}</div>
                <span class="badge {{ $statusFilter === 'approved' ? 'text-bg-success' : ($statusFilter === 'rejected' ? 'text-bg-danger' : ($statusFilter === 'all' ? 'text-bg-secondary' : 'text-bg-warning')) }}">{{ $properties->total() }} records</span>
            </div>

            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th class="ps-3">Property</th>
                            <th>Landlord</th>
                            <th>Address</th>
                            <th>Submitted</th>
                            <th>Status</th>
                            <th>Reviewed</th>
                            <th class="pe-3 actions-col">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($properties as $property)
                            @php
                                $status = (string) ($property->approval_status ?? 'pending');
                                $statusClass = $status === 'approved'
                                    ? 'text-bg-success'
                                    : ($status === 'rejected' ? 'text-bg-danger' : 'text-bg-warning');
                                $reviewedAt = $property->approved_at ?? $property->rejected_at;
                                $reviewedAtValue = !empty($reviewedAt) ? \Illuminate\Support\Carbon::parse($reviewedAt) : null;
                            @endphp
                            <tr>
                                <td class="ps-3">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="property-avatar">
                                            <i class="bi bi-building"></i>
                                        </div>
                                        <div>
                                            <div class="fw-semibold">{{ $property->name }}</div>
                                            <div class="small muted">Submitted for review</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div>{{ $property->landlord->full_name }}</div>
                                    <div class="small muted">{{ $property->landlord->email }}</div>
                                </td>
                                <td><div class="fw-semibold">{{ $property->address }}</div></td>
                                <td>
                                    <div class="fw-semibold">{{ $property->created_at->format('M d, Y') }}</div>
                                    <div class="small muted">{{ $property->created_at->format('h:i A') }}</div>
                                </td>
                                <td>
                                    <span class="badge {{ $statusClass }}">{{ str_replace('_', ' ', $status) }}</span>
                                    @if($status === 'rejected' && !empty($property->rejection_reason))
                                        <div class="small text-danger mt-1">{{ $property->rejection_reason }}</div>
                                    @endif
                                </td>
                                <td>
                                    @if($reviewedAtValue)
                                        <div>{{ $reviewedAtValue->format('M d, Y') }}</div>
                                        <div class="small muted">{{ $reviewedAtValue->format('h:i A') }}</div>
                                    @else
                                        <span class="small muted">Not reviewed</span>
                                    @endif
                                </td>
                                <td class="pe-3 actions-col">
                                    <div class="action-grid properties-grid">
                                        @if($status === 'approved')
                                            <span class="btn btn-sm btn-success action-btn disabled" aria-disabled="true" title="Already approved">
                                                <i class="bi bi-check2-circle"></i>
                                            </span>
                                        @else
                                            <button type="button" class="btn btn-sm btn-success action-btn" data-bs-toggle="modal" data-bs-target="#approvePropertyModal{{ $property->id }}" title="Approve Property" aria-label="Approve Property">
                                                <i class="bi bi-check2"></i>
                                            </button>
                                        @endif

                                        <button type="button" class="btn btn-sm btn-outline-danger action-btn" data-bs-toggle="modal" data-bs-target="#rejectPropertyModal{{ $property->id }}" title="Reject Property" aria-label="Reject Property">
                                            <i class="bi bi-x-lg"></i>
                                        </button>

                                        <a href="{{ route('admin.properties.show', $property) }}" class="btn btn-sm btn-outline-secondary action-btn" title="View Property Details" aria-label="View Property Details">
                                            <i class="bi bi-eye"></i>
                                        </a>

                                        <a href="{{ route('admin.users.landlords.show', $property->landlord) }}" class="btn btn-sm btn-outline-secondary action-btn" title="View Landlord" aria-label="View Landlord">
                                            <i class="bi bi-person"></i>
                                        </a>
                                    </div>

                                    <div class="modal fade" id="approvePropertyModal{{ $property->id }}" tabindex="-1" aria-labelledby="approvePropertyModalLabel{{ $property->id }}" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content rounded-4 border-0 shadow">
                                                <form method="POST" action="{{ route('admin.properties.approve', $property) }}">
                                                    @csrf
                                                    <div class="modal-header border-0 pb-0">
                                                        <h5 class="modal-title" id="approvePropertyModalLabel{{ $property->id }}">
                                                            <i class="bi bi-check2-circle text-success me-2"></i>Approve Property
                                                        </h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body pt-2">
                                                        <div class="mb-2 fw-semibold">{{ $property->name }}</div>
                                                        <div class="small muted mb-3">Landlord: {{ $property->landlord->full_name }}</div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" value="1" id="approve_property_confirm_{{ $property->id }}" required>
                                                            <label class="form-check-label small" for="approve_property_confirm_{{ $property->id }}">
                                                                I confirm this property is valid and ready to be published.
                                                            </label>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer border-0 pt-0">
                                                        <button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-success rounded-pill px-3">
                                                            <i class="bi bi-check2 me-1"></i>Confirm Approval
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="modal fade" id="rejectPropertyModal{{ $property->id }}" tabindex="-1" aria-labelledby="rejectPropertyModalLabel{{ $property->id }}" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content rounded-4 border-0 shadow">
                                                <form method="POST" action="{{ route('admin.properties.reject', $property) }}">
                                                    @csrf
                                                    <div class="modal-header border-0 pb-0">
                                                        <h5 class="modal-title" id="rejectPropertyModalLabel{{ $property->id }}">
                                                            <i class="bi bi-x-octagon text-danger me-2"></i>Reject Property
                                                        </h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body pt-2">
                                                        <div class="mb-2 fw-semibold">{{ $property->name }}</div>
                                                        <div class="small muted mb-3">Provide a reason to help the landlord correct the listing (optional).</div>
                                                        <div class="mb-3">
                                                            <label for="rejection_reason_{{ $property->id }}" class="form-label small">Rejection reason <span class="text-muted">(Optional)</span></label>
                                                            <textarea id="rejection_reason_{{ $property->id }}" name="rejection_reason" class="form-control" rows="3" maxlength="500" placeholder="Explain what needs to be corrected."></textarea>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" value="1" id="reject_property_confirm_{{ $property->id }}" required>
                                                            <label class="form-check-label small" for="reject_property_confirm_{{ $property->id }}">
                                                                I confirm this property should be rejected.
                                                            </label>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer border-0 pt-0">
                                                        <button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-danger rounded-pill px-3">
                                                            <i class="bi bi-x-lg me-1"></i>Confirm Rejection
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 table-empty">
                                    <div class="h6 mb-1"><i class="bi bi-check2-circle me-1"></i>No properties found for this filter.</div>
                                    <div>Try switching filters or wait for new submissions.</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($properties->hasPages())
                <div class="section-header border-top">
                    {{ $properties->links() }}
                </div>
            @endif
        </div>
    @else
        <div class="section-card">
            <div class="section-header d-flex justify-content-between align-items-center gap-2">
                <div class="fw-semibold"><i class="bi bi-list-check me-1"></i>Permit Review Queue</div>
                <span class="badge text-bg-secondary">{{ $landlords->total() }} records</span>
            </div>

            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th class="ps-3">Landlord</th>
                            <th>Boarding House</th>
                            <th>Permit</th>
                            <th>Status</th>
                            <th>Last Review</th>
                            <th class="pe-3 actions-col">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($landlords as $landlord)
                            @php
                                $profile = $landlord->landlordProfile;
                                $status = $profile->business_permit_status ?? 'not_submitted';
                                $statusClass = $status === 'approved'
                                    ? 'text-bg-success'
                                    : ($status === 'rejected' ? 'text-bg-danger' : 'text-bg-warning');
                            @endphp
                            <tr>
                                <td class="ps-3">
                                    <div class="fw-semibold">{{ $landlord->full_name }}</div>
                                    <div class="small muted">{{ $landlord->email }}</div>
                                </td>
                                <td>{{ $landlord->boarding_house_name ?: 'Not provided' }}</td>
                                <td>
                                    @if(!empty($profile->business_permit_path))
                                        <a href="{{ asset('storage/' . $profile->business_permit_path) }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary rounded-pill">
                                            <i class="bi bi-file-earmark-pdf me-1"></i>View Permit
                                        </a>
                                    @else
                                        <span class="small muted">No file</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge {{ $statusClass }} status-chip">{{ str_replace('_', ' ', $status) }}</span>
                                    @if($status === 'rejected' && filled($profile->business_permit_rejection_reason))
                                        <div class="small text-danger mt-1">{{ $profile->business_permit_rejection_reason }}</div>
                                    @endif
                                </td>
                                <td>
                                    @if(!empty($profile->business_permit_reviewed_at))
                                        <div>{{ $profile->business_permit_reviewed_at->format('M d, Y h:i A') }}</div>
                                    @else
                                        <span class="small muted">Not reviewed</span>
                                    @endif
                                </td>
                                <td class="pe-3 actions-col">
                                    <div class="action-grid permits-grid">
                                        @if($status === 'approved')
                                            <span class="btn btn-sm btn-success action-btn disabled" aria-disabled="true" title="Already approved">
                                                <i class="bi bi-check2-circle"></i>
                                            </span>
                                        @else
                                            <button type="button" class="btn btn-sm btn-success action-btn" data-bs-toggle="modal" data-bs-target="#approvePermitModal{{ $landlord->id }}" title="Approve Permit" aria-label="Approve Permit">
                                                <i class="bi bi-check2"></i>
                                            </button>
                                        @endif

                                        <button type="button" class="btn btn-sm btn-outline-danger action-btn" data-bs-toggle="modal" data-bs-target="#rejectPermitModal{{ $landlord->id }}" title="Reject Permit" aria-label="Reject Permit">
                                            <i class="bi bi-x-circle"></i>
                                        </button>

                                        <a href="{{ route('admin.users.landlords.show', $landlord) }}" class="btn btn-sm btn-outline-secondary action-btn" title="View Landlord Profile" aria-label="View Landlord Profile">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </div>

                                    @if($status !== 'approved')
                                        <div class="modal fade" id="approvePermitModal{{ $landlord->id }}" tabindex="-1" aria-labelledby="approvePermitModalLabel{{ $landlord->id }}" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content rounded-4 border-0 shadow">
                                                    <form method="POST" action="{{ route('admin.permits.approve', $landlord) }}">
                                                        @csrf
                                                        <div class="modal-header border-0 pb-0">
                                                            <h5 class="modal-title" id="approvePermitModalLabel{{ $landlord->id }}">
                                                                <i class="bi bi-check2-circle text-success me-2"></i>Approve Permit Submission
                                                            </h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body pt-2">
                                                            <div class="mb-2 fw-semibold">{{ $landlord->full_name }}</div>
                                                            <div class="reject-modal-note mb-3">Confirm approval to unlock landlord operations and notify the account owner.</div>
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox" value="1" id="approve_confirm_{{ $landlord->id }}" required>
                                                                <label class="form-check-label small" for="approve_confirm_{{ $landlord->id }}">
                                                                    I confirm this permit is valid and should be approved.
                                                                </label>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer border-0 pt-0">
                                                            <button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">Cancel</button>
                                                            <button type="submit" class="btn btn-success rounded-pill px-3">
                                                                <i class="bi bi-check2 me-1"></i>Confirm Approval
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    <div class="modal fade" id="rejectPermitModal{{ $landlord->id }}" tabindex="-1" aria-labelledby="rejectPermitModalLabel{{ $landlord->id }}" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content rounded-4 border-0 shadow">
                                                <form method="POST" action="{{ route('admin.permits.reject', $landlord) }}">
                                                    @csrf
                                                    <div class="modal-header border-0 pb-0">
                                                        <h5 class="modal-title" id="rejectPermitModalLabel{{ $landlord->id }}">
                                                            <i class="bi bi-x-octagon text-danger me-2"></i>Reject Permit Submission
                                                        </h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body pt-2">
                                                        <div class="mb-2 fw-semibold">{{ $landlord->full_name }}</div>
                                                        <div class="reject-modal-note mb-3">Provide a clear reason so the landlord can upload a corrected permit.</div>
                                                        <div class="mb-3">
                                                            <label for="rejection_reason_{{ $landlord->id }}" class="form-label small">Rejection reason <span class="text-danger">*</span></label>
                                                            <textarea id="rejection_reason_{{ $landlord->id }}" name="rejection_reason" class="form-control" rows="3" maxlength="500" placeholder="State what needs to be corrected in the permit." required></textarea>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" value="1" id="reject_confirm_{{ $landlord->id }}" required>
                                                            <label class="form-check-label small" for="reject_confirm_{{ $landlord->id }}">
                                                                I confirm this permit should be rejected and the landlord will be notified.
                                                            </label>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer border-0 pt-0">
                                                        <button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-danger rounded-pill px-3">
                                                            <i class="bi bi-send me-1"></i>Confirm Rejection
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 table-empty">
                                    <div class="h6 mb-1"><i class="bi bi-check2-circle me-1"></i>No permits found for this filter.</div>
                                    <div>Try switching filters or wait for new permit submissions.</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($landlords->hasPages())
                <div class="section-header border-top">
                    {{ $landlords->links() }}
                </div>
            @endif
        </div>
    @endif
</div>
@endsection
