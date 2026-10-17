@extends('layouts.admin')

@section('title', 'Identity Recovery Requests - Admin Panel')

@section('content')
<div class="admin-page-shell">
    <x-admin.page-header
        title="Identity Recovery Requests"
        subtitle="Review student account recovery claims and verify university credential ownership."
        icon="bi-shield-lock"
        :breadcrumb="[
            ['label' => 'Admin', 'url' => route('admin.dashboard')],
            ['label' => 'Management'],
            ['label' => 'Student Verification', 'url' => route('admin.approvals.students')],
            ['label' => 'Identity Recoveries']
        ]"
    />

    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm py-2 mb-3 d-flex align-items-center gap-2">
            <i class="bi bi-check-circle-fill"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger border-0 shadow-sm py-2 mb-3 d-flex align-items-center gap-2">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger border-0 shadow-sm py-2 mb-3">
            <ul class="mb-0 ps-3">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Sub-navigation Tabs --}}
    <div class="d-flex flex-wrap gap-2 mb-3 border-bottom pb-2">
        <a href="{{ route('admin.approvals.students') }}" class="btn btn-sm btn-light border text-muted">
            <i class="bi bi-person-vcard me-1"></i> Student Enrollment Verification
        </a>
        <a href="{{ route('admin.identity_recoveries.index') }}" class="btn btn-sm btn-brand">
            <i class="bi bi-shield-lock me-1"></i> Identity Recovery Requests
            @if(($counts['pending'] ?? 0) > 0)
                <span class="badge bg-danger ms-1">{{ $counts['pending'] }}</span>
            @endif
        </a>
    </div>

    {{-- Metric Strip --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <x-admin.metric-card
                label="Pending Review"
                :value="number_format($counts['pending'] ?? 0)"
                variant="{{ ($counts['pending'] ?? 0) > 0 ? 'warning' : 'success' }}"
                icon="bi-hourglass-split"
            />
        </div>
        <div class="col-6 col-lg-3">
            <x-admin.metric-card
                label="Approved for Recovery"
                :value="number_format($counts['approved'] ?? 0)"
                variant="success"
                icon="bi-check2-circle"
            />
        </div>
        <div class="col-6 col-lg-3">
            <x-admin.metric-card
                label="Rejected / Closed"
                :value="number_format($counts['rejected'] ?? 0)"
                variant="danger"
                icon="bi-x-octagon"
            />
        </div>
        <div class="col-6 col-lg-3">
            <x-admin.metric-card
                label="Total in View"
                :value="number_format($requests->total())"
                variant="neutral"
                icon="bi-collection"
            />
        </div>
    </div>

    {{-- Filter Segmented Tabs --}}
    <div class="d-flex flex-wrap gap-2 mb-3 align-items-center">
        <div class="d-flex flex-wrap gap-1">
            <a href="{{ route('admin.identity_recoveries.index', ['status' => 'pending']) }}" class="filter-chip {{ $statusFilter === 'pending' ? 'active' : '' }}">
                <i class="bi bi-hourglass-split"></i> Pending
            </a>
            <a href="{{ route('admin.identity_recoveries.index', ['status' => 'approved']) }}" class="filter-chip {{ $statusFilter === 'approved' ? 'active' : '' }}">
                <i class="bi bi-check2-circle"></i> Approved
            </a>
            <a href="{{ route('admin.identity_recoveries.index', ['status' => 'rejected']) }}" class="filter-chip {{ $statusFilter === 'rejected' ? 'active' : '' }}">
                <i class="bi bi-x-octagon"></i> Rejected
            </a>
            <a href="{{ route('admin.identity_recoveries.index', ['status' => 'all']) }}" class="filter-chip {{ $statusFilter === 'all' ? 'active' : '' }}">
                <i class="bi bi-list-ul"></i> All
            </a>
        </div>
    </div>

    <div class="admin-table-card">
        <div class="admin-card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div class="fw-bold d-flex align-items-center gap-2">
                <i class="bi bi-shield-lock text-success"></i>
                <span>Identity Recovery Queue</span>
            </div>
            <span class="badge rounded-pill bg-light text-secondary border">{{ $requests->total() }} records</span>
        </div>

        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th class="ps-3">Claimant Details</th>
                        <th>Claimed Student ID</th>
                        <th>Contact Method</th>
                        <th>Reason / Evidence</th>
                        <th>Existing Account Info</th>
                        <th>Status</th>
                        <th>Review Details</th>
                        <th class="pe-3 text-end" style="min-width: 150px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($requests as $req)
                        @php
                            $status = $req->status ?: 'pending';
                        @endphp
                        <tr>
                            <td class="ps-3">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="admin-avatar">
                                        <i class="bi bi-person"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark">{{ $req->user->full_name ?? 'Claimant' }}</div>
                                        <div class="small text-muted">{{ $req->user->email ?? 'No email' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border fw-bold px-2 py-1" style="font-size: 0.88rem;">
                                    {{ $req->student_id }}
                                </span>
                            </td>
                            <td>
                                <div class="small fw-semibold text-dark"><i class="bi bi-envelope me-1 text-muted"></i>{{ $req->contact_email }}</div>
                                @if(filled($req->contact_phone))
                                    <div class="small text-muted"><i class="bi bi-telephone me-1 text-muted"></i>{{ $req->contact_phone }}</div>
                                @endif
                            </td>
                            <td>
                                <div class="small text-dark mb-1" style="max-width: 240px; word-break: break-word;">{{ $req->reason }}</div>
                                @if(!empty($req->document_path))
                                    <button type="button" onclick="openDocumentPreview('{{ file_download_url($req->document_path) }}', 'Identity Evidence - {{ addslashes($req->student_id) }}')" class="btn btn-sm btn-light border py-1 px-2 text-primary" style="font-size: 0.78rem;">
                                        <i class="bi bi-file-earmark-arrow-down me-1"></i>View Uploaded ID
                                    </button>
                                @else
                                    <span class="small text-muted">No document attached</span>
                                @endif
                            </td>
                            <td>
                                @if($req->existing_account)
                                    @php
                                        $emailParts = explode('@', $req->existing_account->email);
                                        $maskedUser = substr($emailParts[0], 0, 1) . '***' . (strlen($emailParts[0]) > 2 ? substr($emailParts[0], -1) : '');
                                        $maskedEmail = $maskedUser . '@' . ($emailParts[1] ?? 'minsu.edu.ph');
                                    @endphp
                                    <div class="small fw-bold text-dark">{{ $req->existing_account->full_name }}</div>
                                    <div class="small text-muted">{{ $maskedEmail }}</div>
                                    <span class="badge bg-secondary-subtle text-secondary border mt-1" style="font-size: 0.72rem;">
                                        {{ ucfirst($req->existing_account->school_id_verification_status ?: 'unverified') }}
                                    </span>
                                @else
                                    <span class="small text-muted">No conflicting account</span>
                                @endif
                            </td>
                            <td>
                                <x-admin.status-badge :status="$status" />
                                @if($status === 'rejected' && filled($req->admin_notes))
                                    <div class="small text-danger mt-1" style="max-width: 180px;">{{ $req->admin_notes }}</div>
                                @endif
                            </td>
                            <td>
                                @if(!empty($req->reviewed_at))
                                    <div class="small fw-semibold text-dark">{{ $req->reviewer->full_name ?? 'Admin' }}</div>
                                    <div class="small text-muted">{{ $req->reviewed_at->format('M d, Y h:i A') }}</div>
                                    @if(filled($req->admin_notes) && $status !== 'rejected')
                                        <div class="small text-muted fst-italic mt-1" style="max-width: 180px;">"{{ $req->admin_notes }}"</div>
                                    @endif
                                @else
                                    <span class="small text-muted">Pending review</span>
                                @endif
                            </td>
                            <td class="pe-3 text-end">
                                <div class="d-inline-flex align-items-center gap-1">
                                    @if($status === 'pending')
                                        <button type="button" class="btn btn-sm btn-brand btn-action-icon" data-bs-toggle="modal" data-bs-target="#approveModal{{ $req->id }}" title="Approve recovery request" aria-label="Approve recovery">
                                            <i class="bi bi-check2"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-danger btn-action-icon" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $req->id }}" title="Reject recovery request" aria-label="Reject recovery">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                    @else
                                        <span class="small text-muted">Completed</span>
                                    @endif
                                </div>

                                {{-- Approve Modal --}}
                                @if($status === 'pending')
                                    <div class="modal fade" id="approveModal{{ $req->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content text-start">
                                                <form method="POST" action="{{ route('admin.identity_recoveries.approve', $req) }}">
                                                    @csrf
                                                    <div class="modal-header">
                                                        <h5 class="modal-title fs-6 fw-bold">Approve Identity Recovery</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <p class="small text-muted mb-3">
                                                            You are confirming that the claimant (<strong>{{ $req->user->full_name ?? 'Claimant' }}</strong>) has verified rightful ownership of Student ID <strong>{{ $req->student_id }}</strong>.
                                                        </p>
                                                        <div class="mb-3">
                                                            <label class="form-label small fw-semibold">Review / Resolution Notes (Optional)</label>
                                                            <textarea name="admin_notes" rows="3" class="form-control form-control-sm" placeholder="Enter notes or instructions provided to the student..."></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-sm btn-brand">Confirm Approval</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Reject Modal --}}
                                    <div class="modal fade" id="rejectModal{{ $req->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content text-start">
                                                <form method="POST" action="{{ route('admin.identity_recoveries.reject', $req) }}">
                                                    @csrf
                                                    <div class="modal-header">
                                                        <h5 class="modal-title fs-6 fw-bold text-danger">Reject Identity Recovery</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <p class="small text-muted mb-3">
                                                            Provide a reason for rejecting this account recovery claim for Student ID <strong>{{ $req->student_id }}</strong>.
                                                        </p>
                                                        <div class="mb-3">
                                                            <label class="form-label small fw-semibold">Rejection Reason *</label>
                                                            <textarea name="admin_notes" rows="3" class="form-control form-control-sm" placeholder="State reason for rejection..." required></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-sm btn-danger">Confirm Rejection</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-2 d-block mb-2 text-secondary"></i>
                                No identity recovery requests found for the selected status.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($requests->hasPages())
            <div class="p-3 border-top">
                {{ $requests->links() }}
            </div>
        @endif
    </div>
</div>

{{-- Document Preview Modal --}}
<div class="modal fade" id="documentPreviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fs-6 fw-bold" id="documentPreviewTitle">Document Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center p-0" id="documentPreviewBody" style="min-height: 300px; max-height: 80vh; overflow: auto;">
                <div class="p-5 text-muted">Loading preview...</div>
            </div>
            <div class="modal-footer">
                <a id="documentDownloadBtn" href="#" class="btn btn-sm btn-outline-secondary" target="_blank" download>
                    <i class="bi bi-download me-1"></i> Download File
                </a>
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
function openDocumentPreview(url, title) {
    const modalTitle = document.getElementById('documentPreviewTitle');
    const modalBody = document.getElementById('documentPreviewBody');
    const downloadBtn = document.getElementById('documentDownloadBtn');

    if (modalTitle) modalTitle.textContent = title;
    if (downloadBtn) downloadBtn.href = url;

    const ext = url.split('.').pop().toLowerCase();
    if (['jpg', 'jpeg', 'png', 'webp', 'gif'].includes(ext)) {
        modalBody.innerHTML = `<img src="${url}" class="img-fluid p-3" alt="Document Preview">`;
    } else if (ext === 'pdf') {
        modalBody.innerHTML = `<iframe src="${url}" style="width: 100%; height: 600px; border: none;"></iframe>`;
    } else {
        modalBody.innerHTML = `<div class="p-5"><p class="text-muted">Preview not supported for this file type.</p><a href="${url}" class="btn btn-sm btn-primary" download>Download to view</a></div>`;
    }

    const modal = new bootstrap.Modal(document.getElementById('documentPreviewModal'));
    modal.show();
}
</script>
@endsection
