@extends('layouts.admin')

@section('title', 'Student Verification - Admin Panel')

@section('content')
<div class="admin-page-shell">
    <x-admin.page-header
        title="Student Verification"
        subtitle="Verify MinSU student eligibility and submitted academic credentials."
        icon="bi-person-vcard"
        :breadcrumb="[
            ['label' => 'Admin', 'url' => route('admin.dashboard')],
            ['label' => 'Management'],
            ['label' => 'Student Verification']
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

    {{-- Metric Strip --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <x-admin.metric-card
                label="Pending Verification"
                :value="number_format($counts['pending'] ?? 0)"
                variant="{{ ($counts['pending'] ?? 0) > 0 ? 'warning' : 'success' }}"
                icon="bi-hourglass-split"
            />
        </div>
        <div class="col-6 col-lg-3">
            <x-admin.metric-card
                label="Verified Students"
                :value="number_format($counts['approved'] ?? 0)"
                variant="success"
                icon="bi-check2-circle"
            />
        </div>
        <div class="col-6 col-lg-3">
            <x-admin.metric-card
                label="Rejected"
                :value="number_format($counts['rejected'] ?? 0)"
                variant="danger"
                icon="bi-x-octagon"
            />
        </div>
        <div class="col-6 col-lg-3">
            <x-admin.metric-card
                label="Total in View"
                :value="number_format($students->total())"
                variant="neutral"
                icon="bi-collection"
            />
        </div>
    </div>

    {{-- Sub-navigation Tabs --}}
    <div class="d-flex flex-wrap gap-2 mb-3 border-bottom pb-2">
        <a href="{{ route('admin.approvals.students') }}" class="btn btn-sm btn-brand">
            <i class="bi bi-person-vcard me-1"></i> Student Enrollment Verification
        </a>
        <a href="{{ route('admin.identity_recoveries.index') }}" class="btn btn-sm btn-light border text-muted">
            <i class="bi bi-shield-lock me-1"></i> Identity Recovery Requests
        </a>
    </div>

    {{-- Filter Segmented Tabs --}}
    <div class="d-flex flex-wrap gap-2 mb-3 align-items-center">
        <div class="d-flex flex-wrap gap-1">
            <a href="{{ route('admin.approvals.students', ['status' => 'pending']) }}" class="filter-chip {{ $statusFilter === 'pending' ? 'active' : '' }}">
                <i class="bi bi-hourglass-split"></i> Pending
            </a>
            <a href="{{ route('admin.approvals.students', ['status' => 'approved']) }}" class="filter-chip {{ $statusFilter === 'approved' ? 'active' : '' }}">
                <i class="bi bi-check2-circle"></i> Verified
            </a>
            <a href="{{ route('admin.approvals.students', ['status' => 'rejected']) }}" class="filter-chip {{ $statusFilter === 'rejected' ? 'active' : '' }}">
                <i class="bi bi-x-octagon"></i> Rejected
            </a>
            <a href="{{ route('admin.approvals.students', ['status' => 'all']) }}" class="filter-chip {{ $statusFilter === 'all' ? 'active' : '' }}">
                <i class="bi bi-list-ul"></i> All
            </a>
        </div>
    </div>

    <div class="admin-table-card">
        <div class="admin-card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div class="fw-bold d-flex align-items-center gap-2">
                <i class="bi bi-person-check text-success"></i>
                <span>Student Verification Queue</span>
            </div>
            <span class="badge rounded-pill bg-light text-secondary border">{{ $students->total() }} records</span>
        </div>

        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th class="ps-3">Student</th>
                        <th>Academic Information</th>
                        <th>Credentials Submitted</th>
                        <th>Status</th>
                        <th>Last Review</th>
                        <th class="pe-3 text-end" style="min-width: 160px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($students as $student)
                        @php
                            $status = $student->school_id_verification_status ?: 'pending';
                        @endphp
                        <tr>
                            <td class="ps-3">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="admin-avatar">
                                        <i class="bi bi-person"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark">{{ $student->full_name }}</div>
                                        <div class="small text-muted">{{ $student->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark">{{ $student->student_id ?: 'No student ID' }}</div>
                                <div class="small text-muted">{{ $student->program ?: 'No program provided' }}</div>
                            </td>
                            <td>
                                <div class="d-flex flex-wrap gap-1">
                                    @if(!empty($student->school_id_path))
                                        <button type="button" onclick="openDocumentPreview('{{ file_download_url($student->school_id_path) }}', 'School ID - {{ addslashes($student->full_name) }}')" class="btn btn-sm btn-light border py-1 px-2 text-secondary" style="font-size: 0.8rem;">
                                            <i class="bi bi-file-earmark-image me-1 text-primary"></i>School ID
                                        </button>
                                    @endif

                                    @if(!empty($student->enrollment_proof_path))
                                        <button type="button" onclick="openDocumentPreview('{{ file_download_url($student->enrollment_proof_path) }}', '{{ strtoupper((string) ($student->enrollment_proof_type ?? 'COR/COE')) }} - {{ addslashes($student->full_name) }}')" class="btn btn-sm btn-light border py-1 px-2 text-secondary" style="font-size: 0.8rem;">
                                            <i class="bi bi-file-earmark-text me-1 text-success"></i>{{ strtoupper((string) ($student->enrollment_proof_type ?? 'COR/COE')) }}
                                        </button>
                                    @endif

                                    @if(empty($student->school_id_path) && empty($student->enrollment_proof_path))
                                        <span class="small text-muted">No document uploaded</span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <x-admin.status-badge :status="$status" />
                                @if($status === 'rejected' && filled($student->school_id_rejection_reason))
                                    <div class="small text-danger mt-1" style="max-width: 200px;">{{ $student->school_id_rejection_reason }}</div>
                                @endif
                            </td>
                            <td>
                                @if(!empty($student->school_id_verified_at))
                                    <div class="fw-semibold">{{ $student->school_id_verified_at->format('M d, Y') }}</div>
                                    <div class="small text-muted">{{ $student->school_id_verified_at->format('h:i A') }}</div>
                                @else
                                    <span class="small text-muted">Not reviewed</span>
                                @endif
                            </td>
                            <td class="pe-3 text-end">
                                <div class="d-inline-flex align-items-center gap-1">
                                    @if($status === 'approved')
                                        <span class="btn btn-sm btn-light border btn-action-icon text-muted disabled" aria-disabled="true" title="Already approved">
                                            <i class="bi bi-check2-circle"></i>
                                        </span>
                                    @else
                                        <button type="button" class="btn btn-sm btn-brand btn-action-icon" data-bs-toggle="modal" data-bs-target="#approveStudentModal{{ $student->id }}" title="Approve verification" aria-label="Approve verification">
                                            <i class="bi bi-check2"></i>
                                        </button>
                                    @endif

                                    <button type="button" class="btn btn-sm btn-outline-danger btn-action-icon" data-bs-toggle="modal" data-bs-target="#rejectStudentModal{{ $student->id }}" title="Reject verification" aria-label="Reject verification">
                                        <i class="bi bi-x-circle"></i>
                                    </button>

                                    <a href="{{ route('admin.users.students.show', $student) }}" class="btn btn-sm btn-light border btn-action-icon text-secondary" title="View Student Profile" aria-label="View Student Profile">
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
                                                        <h5 class="modal-title fw-bold" id="approveStudentModalLabel{{ $student->id }}">
                                                            <i class="bi bi-check2-circle text-success me-2"></i>Approve Verification
                                                        </h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body pt-2 text-start">
                                                        <div class="mb-2 fw-semibold">{{ $student->full_name }}</div>
                                                        <div class="small text-muted mb-3">Confirm approval to unlock verified booking privileges for this student.</div>

                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" value="1" id="approve_confirm_{{ $student->id }}" required>
                                                            <label class="form-check-label small" for="approve_confirm_{{ $student->id }}">
                                                                I confirm this student's submitted credentials are valid MinSU records.
                                                            </label>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer border-0 pt-0">
                                                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-sm btn-brand px-3">
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
                                                    <h5 class="modal-title fw-bold" id="rejectStudentModalLabel{{ $student->id }}">
                                                        <i class="bi bi-x-octagon text-danger me-2"></i>Reject Verification
                                                    </h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body pt-2 text-start">
                                                    <div class="mb-2 fw-semibold">{{ $student->full_name }}</div>
                                                    <div class="small text-muted mb-3">Provide a clear reason so the student can re-upload corrected credentials.</div>

                                                    <div class="mb-3">
                                                        <label for="rejection_reason_{{ $student->id }}" class="form-label small fw-semibold">Rejection reason <span class="text-danger">*</span></label>
                                                        <textarea id="rejection_reason_{{ $student->id }}" name="rejection_reason" class="form-control" rows="3" maxlength="500" placeholder="State what needs to be corrected in the submitted document." required></textarea>
                                                    </div>

                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" value="1" id="reject_confirm_{{ $student->id }}" required>
                                                        <label class="form-check-label small" for="reject_confirm_{{ $student->id }}">
                                                            I confirm this verification submission should be rejected.
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="modal-footer border-0 pt-0">
                                                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-sm btn-danger px-3">
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
                            <td colspan="6">
                                <x-admin.empty-state
                                    icon="bi-person-check"
                                    title="No students found for this verification queue"
                                    message="There are no student credential submissions currently matching this status."
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($students->hasPages())
            <div class="admin-card-header border-top d-flex justify-content-center">
                {{ $students->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

