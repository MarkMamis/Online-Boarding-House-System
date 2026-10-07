@extends('layouts.admin')

@section('title', 'Landlord Permit Approvals')

@section('content')
<div class="admin-page-shell">
    <x-admin.page-header
        title="Landlord Permit Approvals"
        subtitle="Review uploaded business permits and approve or reject landlord verification."
        icon="bi bi-file-earmark-check"
        :breadcrumbs="[
            ['label' => 'Compliance', 'url' => route('admin.documents.monitoring')],
            ['label' => 'Permit Approvals']
        ]"
    >
        <x-slot:actions>
            <a href="{{ route('admin.users.landlords') }}" class="btn btn-outline-secondary rounded-pill px-3">
                <i class="bi bi-people me-1"></i>Landlords
            </a>
        </x-slot:actions>
    </x-admin.page-header>

    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm mb-4">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger border-0 shadow-sm mb-4">{{ session('error') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger border-0 shadow-sm mb-4">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <x-admin.metric-card
                label="Pending"
                :value="number_format($counts['pending'] ?? 0)"
                icon="bi bi-clock-history"
                variant="warning"
                subtext="Awaiting review"
            />
        </div>
        <div class="col-6 col-lg-3">
            <x-admin.metric-card
                label="Approved"
                :value="number_format($counts['approved'] ?? 0)"
                icon="bi bi-check2-circle"
                variant="success"
                subtext="Permits verified"
            />
        </div>
        <div class="col-6 col-lg-3">
            <x-admin.metric-card
                label="Rejected"
                :value="number_format($counts['rejected'] ?? 0)"
                icon="bi bi-x-circle"
                variant="danger"
                subtext="Returned to landlord"
            />
        </div>
        <div class="col-6 col-lg-3">
            <x-admin.metric-card
                label="Visible Records"
                :value="number_format($landlords->total())"
                icon="bi bi-collection"
                variant="brand"
                subtext="Filtered queue count"
            />
        </div>
    </div>

    <div class="admin-toolbar mb-4">
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('admin.permits.index', ['status' => 'pending']) }}" class="filter-chip {{ $statusFilter === 'pending' ? 'active' : '' }}">Pending</a>
            <a href="{{ route('admin.permits.index', ['status' => 'approved']) }}" class="filter-chip {{ $statusFilter === 'approved' ? 'active' : '' }}">Approved</a>
            <a href="{{ route('admin.permits.index', ['status' => 'rejected']) }}" class="filter-chip {{ $statusFilter === 'rejected' ? 'active' : '' }}">Rejected</a>
            <a href="{{ route('admin.permits.index', ['status' => 'all']) }}" class="filter-chip {{ $statusFilter === 'all' ? 'active' : '' }}">All</a>
        </div>
    </div>

    <div class="admin-table-card">
        <div class="d-flex justify-content-between align-items-center p-3 border-bottom bg-light">
            <div class="fw-semibold text-dark"><i class="bi bi-list-check me-1 text-success"></i>Permit Review Queue</div>
            <span class="badge bg-secondary rounded-pill px-3">{{ $landlords->total() }} records</span>
        </div>

        <div class="table-responsive">
            <table class="table admin-table mb-0">
                <thead>
                    <tr>
                        <th class="ps-3">Landlord</th>
                        <th>Boarding House</th>
                        <th>Permit</th>
                        <th>Status</th>
                        <th>Last Review</th>
                        <th class="pe-3 text-end" style="min-width: 150px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($landlords as $landlord)
                        @php
                            $profile = $landlord->landlordProfile;
                            $status = $profile->business_permit_status ?? 'not_submitted';
                        @endphp
                        <tr>
                            <td class="ps-3">
                                <div class="fw-semibold text-dark">{{ $landlord->full_name }}</div>
                                <div class="small text-muted">{{ $landlord->email }}</div>
                            </td>
                            <td>{{ $landlord->boarding_house_name ?: 'Not provided' }}</td>
                            <td>
                                @if(!empty($profile->business_permit_path))
                                    <button type="button" onclick="openDocumentPreview('{{ file_download_url($profile->business_permit_path) }}', 'Business Permit - {{ addslashes($landlord->full_name) }}')" class="btn btn-sm btn-outline-secondary rounded-pill">
                                        <i class="bi bi-file-earmark-pdf me-1"></i>View Permit
                                    </button>
                                @else
                                    <span class="small text-muted">No file</span>
                                @endif
                            </td>
                            <td>
                                <x-admin.status-badge :status="$status" />
                                @if($status === 'rejected' && filled($profile->business_permit_rejection_reason))
                                    <div class="small text-danger mt-1">{{ $profile->business_permit_rejection_reason }}</div>
                                @endif
                            </td>
                            <td>
                                @if(!empty($profile->business_permit_reviewed_at))
                                    <div>{{ $profile->business_permit_reviewed_at->format('M d, Y h:i A') }}</div>
                                @else
                                    <span class="small text-muted">Not reviewed</span>
                                @endif
                            </td>
                            <td class="pe-3 text-end">
                                <div class="d-inline-flex gap-1">
                                    @if($status === 'approved')
                                        <span class="btn btn-sm btn-action-icon btn-action-icon--success disabled" aria-disabled="true" title="Already approved">
                                            <i class="bi bi-check2-circle"></i>
                                            <span class="visually-hidden">Already Approved</span>
                                        </span>
                                    @else
                                        <button type="button" class="btn btn-sm btn-action-icon btn-action-icon--success" data-bs-toggle="modal" data-bs-target="#approvePermitModal{{ $landlord->id }}" title="Approve Permit" aria-label="Approve Permit">
                                            <i class="bi bi-check2"></i>
                                        </button>
                                    @endif

                                    <button type="button" class="btn btn-sm btn-action-icon btn-action-icon--danger" data-bs-toggle="modal" data-bs-target="#rejectPermitModal{{ $landlord->id }}" title="Reject Permit" aria-label="Reject Permit">
                                        <i class="bi bi-x-circle"></i>
                                    </button>

                                    <a href="{{ route('admin.users.landlords.show', $landlord) }}" class="btn btn-sm btn-action-icon" title="View Landlord Profile" aria-label="View Landlord Profile">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </div>

                                @if($status !== 'approved')
                                    <div class="modal fade text-start" id="approvePermitModal{{ $landlord->id }}" tabindex="-1" aria-labelledby="approvePermitModalLabel{{ $landlord->id }}" aria-hidden="true">
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
                                                        <div class="small text-muted mb-3">Confirm approval to unlock landlord operations and notify the account owner.</div>

                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" value="1" id="approve_confirm_{{ $landlord->id }}" required>
                                                            <label class="form-check-label small" for="approve_confirm_{{ $landlord->id }}">
                                                                I confirm this permit is valid and should be approved.
                                                            </label>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer border-0 pt-0">
                                                        <button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-brand rounded-pill px-3">
                                                            <i class="bi bi-check2 me-1"></i>Confirm Approval
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                <div class="modal fade text-start" id="rejectPermitModal{{ $landlord->id }}" tabindex="-1" aria-labelledby="rejectPermitModalLabel{{ $landlord->id }}" aria-hidden="true">
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
                                                    <div class="small text-muted mb-3">Provide a clear reason so the landlord can upload a corrected permit.</div>

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
                            <td colspan="6" class="p-0">
                                <x-admin.empty-state
                                    title="No permits found"
                                    description="Try switching filters or wait for new permit submissions."
                                    icon="bi bi-check2-circle"
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($landlords->hasPages())
            <div class="p-3 border-top">
                {{ $landlords->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
