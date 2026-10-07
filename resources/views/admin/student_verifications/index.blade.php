@extends('layouts.admin')

@section('title', 'Student Academic Verifications')

@section('content')
<div class="admin-page-shell">
    <x-admin.page-header
        title="Student Academic Verifications"
        subtitle="Review School ID and COR/COE submissions, then approve or reject boarding request eligibility."
        icon="bi bi-person-vcard"
        :breadcrumbs="[
            ['label' => 'Compliance', 'url' => route('admin.documents.monitoring')],
            ['label' => 'Academic Verifications']
        ]"
    >
        <x-slot:actions>
            <a href="{{ route('admin.users.students') }}" class="btn btn-outline-secondary rounded-pill px-3">
                <i class="bi bi-people me-1"></i>Students
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
                subtext="Verified students"
            />
        </div>
        <div class="col-6 col-lg-3">
            <x-admin.metric-card
                label="Rejected"
                :value="number_format($counts['rejected'] ?? 0)"
                icon="bi bi-x-circle"
                variant="danger"
                subtext="Action required"
            />
        </div>
        <div class="col-6 col-lg-3">
            <x-admin.metric-card
                label="Visible Records"
                :value="number_format($students->total())"
                icon="bi bi-collection"
                variant="brand"
                subtext="Filtered queue count"
            />
        </div>
    </div>

    <div class="admin-toolbar mb-4">
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('admin.student_verifications.index', ['status' => 'pending']) }}" class="filter-chip {{ $statusFilter === 'pending' ? 'active' : '' }}">Pending</a>
            <a href="{{ route('admin.student_verifications.index', ['status' => 'approved']) }}" class="filter-chip {{ $statusFilter === 'approved' ? 'active' : '' }}">Approved</a>
            <a href="{{ route('admin.student_verifications.index', ['status' => 'rejected']) }}" class="filter-chip {{ $statusFilter === 'rejected' ? 'active' : '' }}">Rejected</a>
            <a href="{{ route('admin.student_verifications.index', ['status' => 'all']) }}" class="filter-chip {{ $statusFilter === 'all' ? 'active' : '' }}">All</a>
        </div>
    </div>

    <div class="admin-table-card">
        <div class="d-flex justify-content-between align-items-center p-3 border-bottom bg-light">
            <div class="fw-semibold text-dark"><i class="bi bi-list-check me-1 text-success"></i>Academic Verification Review Queue</div>
            <span class="badge bg-secondary rounded-pill px-3">{{ $students->total() }} records</span>
        </div>

        <div class="table-responsive">
            <table class="table admin-table mb-0">
                <thead>
                    <tr>
                        <th class="ps-3">Student</th>
                        <th>Academic Info</th>
                        <th>Verification Documents</th>
                        <th>Status</th>
                        <th>Last Review</th>
                        <th class="pe-3 text-end" style="min-width: 150px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($students as $student)
                        @php
                            $status = $student->school_id_verification_status ?: 'pending';
                        @endphp
                        <tr>
                            <td class="ps-3">
                                <div class="fw-semibold text-dark">{{ $student->full_name }}</div>
                                <div class="small text-muted">{{ $student->email }}</div>
                            </td>
                            <td>
                                <div class="fw-medium text-dark">{{ $student->student_id ?: 'No student ID' }}</div>
                                <div class="small text-muted">{{ $student->program ?: 'No program provided' }}</div>
                            </td>
                            <td>
                                <div class="d-flex flex-wrap gap-1">
                                    @if(!empty($student->school_id_path))
                                        <button type="button" onclick="openDocumentPreview('{{ file_download_url($student->school_id_path) }}', 'School ID - {{ addslashes($student->full_name) }}')" class="btn btn-sm btn-outline-secondary rounded-pill">
                                            <i class="bi bi-file-earmark-image me-1"></i>School ID
                                        </button>
                                    @endif

                                    @if(!empty($student->enrollment_proof_path))
                                        <button type="button" onclick="openDocumentPreview('{{ file_download_url($student->enrollment_proof_path) }}', '{{ strtoupper((string) ($student->enrollment_proof_type ?? 'COR/COE')) }} - {{ addslashes($student->full_name) }}')" class="btn btn-sm btn-outline-secondary rounded-pill">
                                            <i class="bi bi-file-earmark-text me-1"></i>{{ strtoupper((string) ($student->enrollment_proof_type ?? 'COR/COE')) }}
                                        </button>
                                    @endif

                                    @if(empty($student->school_id_path) && empty($student->enrollment_proof_path))
                                        <span class="small text-muted">No file</span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <x-admin.status-badge :status="$status" />
                                @if($status === 'rejected' && filled($student->school_id_rejection_reason))
                                    <div class="small text-danger mt-1">{{ $student->school_id_rejection_reason }}</div>
                                @endif
                            </td>
                            <td>
                                @if(!empty($student->school_id_verified_at))
                                    <div>{{ $student->school_id_verified_at->format('M d, Y h:i A') }}</div>
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
                                        <button type="button" class="btn btn-sm btn-action-icon btn-action-icon--success" data-bs-toggle="modal" data-bs-target="#approveStudentModal{{ $student->id }}" title="Approve verification" aria-label="Approve verification">
                                            <i class="bi bi-check2"></i>
                                        </button>
                                    @endif

                                    <button type="button" class="btn btn-sm btn-action-icon btn-action-icon--danger" data-bs-toggle="modal" data-bs-target="#rejectStudentModal{{ $student->id }}" title="Reject verification" aria-label="Reject verification">
                                        <i class="bi bi-x-circle"></i>
                                    </button>

                                    <a href="{{ route('admin.users.students.show', $student) }}" class="btn btn-sm btn-action-icon" title="View Student Profile" aria-label="View Student Profile">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </div>

                                @if($status !== 'approved')
                                    <div class="modal fade text-start" id="approveStudentModal{{ $student->id }}" tabindex="-1" aria-labelledby="approveStudentModalLabel{{ $student->id }}" aria-hidden="true">
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
                                                        <div class="small text-muted mb-3">Confirm approval to unlock boarding requests for this student.</div>

                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" value="1" id="approve_confirm_{{ $student->id }}" required>
                                                            <label class="form-check-label small" for="approve_confirm_{{ $student->id }}">
                                                                I confirm this student's submitted verification document(s) are valid and should be approved.
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

                                <div class="modal fade text-start" id="rejectStudentModal{{ $student->id }}" tabindex="-1" aria-labelledby="rejectStudentModalLabel{{ $student->id }}" aria-hidden="true">
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
                                                    <div class="small text-muted mb-3">Provide a clear reason so the student can upload a corrected School ID or COR/COE.</div>

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
                            <td colspan="6" class="p-0">
                                <x-admin.empty-state
                                    title="No verification submissions found"
                                    description="Try switching filters or wait for new student submissions."
                                    icon="bi bi-check2-circle"
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($students->hasPages())
            <div class="p-3 border-top">
                {{ $students->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
