@extends('layouts.admin')

@section('title', 'Student Academic Verifications')

@section('content')
<style>
    .verification-shell {
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
        grid-template-columns: repeat(3, 42px);
        gap: .45rem;
        justify-content: start;
    }
    .action-grid form {
        margin: 0;
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
    .reject-modal-note {
        font-size: .82rem;
        color: rgba(2, 8, 20, .6);
    }
    .verification-docs-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(120px, max-content));
        gap: .5rem;
        justify-content: start;
    }
    .verification-docs-grid .doc-btn {
        width: auto;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: .6rem;
        padding: .34rem .65rem;
    }
    .verification-docs-grid .doc-empty {
        grid-column: 1 / -1;
    }
    @media (max-width: 767.98px) {
        .verification-shell { padding: .95rem; }
        .actions-col { min-width: 190px; }
    }
</style>

<div class="verification-shell">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <div class="text-uppercase small muted fw-semibold">Compliance</div>
            <h1 class="h4 mb-1"><i class="bi bi-person-vcard me-2"></i>Student Academic Verifications</h1>
            <div class="muted small">Review School ID and COR/COE submissions, then approve or reject booking eligibility.</div>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('admin.users.students') }}" class="btn btn-outline-secondary rounded-pill px-3">Students</a>
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

    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="metric-tile">
                <div class="metric-label">Pending</div>
                <div class="metric-value">{{ number_format($counts['pending'] ?? 0) }}</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="metric-tile">
                <div class="metric-label">Approved</div>
                <div class="metric-value">{{ number_format($counts['approved'] ?? 0) }}</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="metric-tile">
                <div class="metric-label">Rejected</div>
                <div class="metric-value">{{ number_format($counts['rejected'] ?? 0) }}</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="metric-tile">
                <div class="metric-label">Visible Records</div>
                <div class="metric-value">{{ number_format($students->total()) }}</div>
            </div>
        </div>
    </div>

    <div class="d-flex flex-wrap gap-2 mb-3">
        <a href="{{ route('admin.student_verifications.index', ['status' => 'pending']) }}" class="btn rounded-pill {{ $statusFilter === 'pending' ? 'btn-success' : 'btn-outline-secondary' }}">Pending</a>
        <a href="{{ route('admin.student_verifications.index', ['status' => 'approved']) }}" class="btn rounded-pill {{ $statusFilter === 'approved' ? 'btn-success' : 'btn-outline-secondary' }}">Approved</a>
        <a href="{{ route('admin.student_verifications.index', ['status' => 'rejected']) }}" class="btn rounded-pill {{ $statusFilter === 'rejected' ? 'btn-success' : 'btn-outline-secondary' }}">Rejected</a>
        <a href="{{ route('admin.student_verifications.index', ['status' => 'all']) }}" class="btn rounded-pill {{ $statusFilter === 'all' ? 'btn-success' : 'btn-outline-secondary' }}">All</a>
    </div>

    <div class="section-card">
        <div class="section-header d-flex justify-content-between align-items-center gap-2">
            <div class="fw-semibold"><i class="bi bi-list-check me-1"></i>Academic Verification Review Queue</div>
            <span class="badge text-bg-secondary">{{ $students->total() }} records</span>
        </div>

        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th class="ps-3">Student</th>
                        <th>Academic Info</th>
                        <th>Verification Documents</th>
                        <th>Status</th>
                        <th>Last Review</th>
                        <th class="pe-3 actions-col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($students as $student)
                        @php
                            $status = $student->school_id_verification_status ?: 'pending';
                            $statusClass = $status === 'approved'
                                ? 'text-bg-success'
                                : ($status === 'rejected' ? 'text-bg-danger' : 'text-bg-warning');
                        @endphp
                        <tr>
                            <td class="ps-3">
                                <div class="fw-semibold">{{ $student->full_name }}</div>
                                <div class="small muted">{{ $student->email }}</div>
                            </td>
                            <td>
                                <div class="small">{{ $student->student_id ?: 'No student ID' }}</div>
                                <div class="small muted">{{ $student->program ?: 'No program provided' }}</div>
                            </td>
                            <td>
                                <div class="verification-docs-grid">
                                    @if(!empty($student->school_id_path))
                                        <a href="{{ asset('storage/' . $student->school_id_path) }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary doc-btn">
                                            <i class="bi bi-file-earmark-image me-1"></i>View School ID
                                        </a>
                                    @endif

                                    @if(!empty($student->enrollment_proof_path))
                                        <a href="{{ asset('storage/' . $student->enrollment_proof_path) }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary doc-btn">
                                            <i class="bi bi-file-earmark-text me-1"></i>View {{ strtoupper((string) ($student->enrollment_proof_type ?? 'COR/COE')) }}
                                        </a>
                                    @endif

                                    @if(empty($student->school_id_path) && empty($student->enrollment_proof_path))
                                        <span class="small muted doc-empty">No file</span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <span class="badge {{ $statusClass }} status-chip">{{ str_replace('_', ' ', $status) }}</span>
                                @if($status === 'rejected' && filled($student->school_id_rejection_reason))
                                    <div class="small text-danger mt-1">{{ $student->school_id_rejection_reason }}</div>
                                @endif
                            </td>
                            <td>
                                @if(!empty($student->school_id_verified_at))
                                    <div>{{ $student->school_id_verified_at->format('M d, Y h:i A') }}</div>
                                @else
                                    <span class="small muted">Not reviewed</span>
                                @endif
                            </td>
                            <td class="pe-3 actions-col">
                                <div class="action-grid">
                                    @if($status === 'approved')
                                        <span class="btn btn-sm btn-success action-btn disabled" aria-disabled="true" title="Already approved">
                                            <i class="bi bi-check2-circle"></i>
                                            <span class="visually-hidden">Already Approved</span>
                                        </span>
                                    @else
                                        <button type="button" class="btn btn-sm btn-success action-btn" data-bs-toggle="modal" data-bs-target="#approveStudentModal{{ $student->id }}" title="Approve verification" aria-label="Approve verification">
                                            <i class="bi bi-check2"></i>
                                        </button>
                                    @endif

                                    <button type="button" class="btn btn-sm btn-outline-danger action-btn" data-bs-toggle="modal" data-bs-target="#rejectStudentModal{{ $student->id }}" title="Reject verification" aria-label="Reject verification">
                                        <i class="bi bi-x-circle"></i>
                                    </button>

                                    <a href="{{ route('admin.users.students.show', $student) }}" class="btn btn-sm btn-outline-secondary action-btn" title="View Student Profile" aria-label="View Student Profile">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </div>

                                @if($status !== 'approved')
                                    <div class="modal fade" id="approveStudentModal{{ $student->id }}" tabindex="-1" aria-labelledby="approveStudentModalLabel{{ $student->id }}" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content rounded-4 border-0 shadow">
                                                <form method="POST" action="{{ route('admin.student_verifications.approve', $student) }}">
                                                    @csrf
                                                    <div class="modal-header border-0 pb-0">
                                                        <h5 class="modal-title" id="approveStudentModalLabel{{ $student->id }}">
                                                            <i class="bi bi-check2-circle text-success me-2"></i>Approve Verification
                                                        </h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body pt-2">
                                                        <div class="mb-2 fw-semibold">{{ $student->full_name }}</div>
                                                        <div class="reject-modal-note mb-3">Confirm approval to unlock booking requests for this student.</div>

                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" value="1" id="approve_confirm_{{ $student->id }}" required>
                                                            <label class="form-check-label small" for="approve_confirm_{{ $student->id }}">
                                                                I confirm this student's submitted verification document(s) are valid and should be approved.
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

                                <div class="modal fade" id="rejectStudentModal{{ $student->id }}" tabindex="-1" aria-labelledby="rejectStudentModalLabel{{ $student->id }}" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content rounded-4 border-0 shadow">
                                            <form method="POST" action="{{ route('admin.student_verifications.reject', $student) }}">
                                                @csrf
                                                <div class="modal-header border-0 pb-0">
                                                    <h5 class="modal-title" id="rejectStudentModalLabel{{ $student->id }}">
                                                        <i class="bi bi-x-octagon text-danger me-2"></i>Reject Verification
                                                    </h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body pt-2">
                                                    <div class="mb-2 fw-semibold">{{ $student->full_name }}</div>
                                                    <div class="reject-modal-note mb-3">Provide a clear reason so the student can upload a corrected School ID or COR/COE.</div>

                                                    <div class="mb-3">
                                                        <label for="rejection_reason_{{ $student->id }}" class="form-label small">Rejection reason <span class="text-danger">*</span></label>
                                                        <textarea id="rejection_reason_{{ $student->id }}" name="rejection_reason" class="form-control" rows="3" maxlength="500" placeholder="State what needs to be corrected in the submitted verification document." required></textarea>
                                                    </div>

                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" value="1" id="reject_confirm_{{ $student->id }}" required>
                                                        <label class="form-check-label small" for="reject_confirm_{{ $student->id }}">
                                                            I confirm this verification submission should be rejected and the student will be notified.
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
                            <td colspan="6" class="text-center py-5 muted">
                                <div class="h6 mb-1"><i class="bi bi-check2-circle me-1"></i>No verification submissions found for this filter.</div>
                                <div>Try switching filters or wait for new student submissions.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($students->hasPages())
            <div class="section-header border-top">
                {{ $students->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
