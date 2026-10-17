@extends('layouts.admin')

@section('title', 'Document Review Queue - Admin Panel')

@section('content')
<div class="admin-page-shell">
    <x-admin.page-header
        title="Document Review Queue"
        subtitle="Review submitted documents awaiting administrative verification. Historical versions remain available for auditing."
        icon="bi-file-earmark-check"
        :breadcrumb="[
            ['label' => 'Admin', 'url' => route('admin.dashboard')],
            ['label' => 'Management'],
            ['label' => 'Document Review Queue']
        ]"
    >
        @if($landlordId > 0)
            <x-slot:actions>
                <a href="{{ route('admin.users.landlords.show', $landlordId) }}" class="btn btn-sm btn-light border px-3">
                    <i class="bi bi-arrow-left me-1"></i>Landlord Details
                </a>
            </x-slot:actions>
        @endif
    </x-admin.page-header>

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

    @php
        $verificationQuery = [
            'landlord_id' => $landlordId,
            'verification_status' => $statusFilter,
            'document_type' => $documentType,
        ];
        if ($includeHistory) {
            $verificationQuery['history'] = 1;
        }
    @endphp

    {{-- Filters --}}
    <div class="d-flex flex-wrap gap-2 mb-3 align-items-center justify-content-between">
        <div class="d-flex flex-wrap gap-1 align-items-center">
            @foreach(['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected', 'all' => 'All'] as $value => $label)
                <a href="{{ route('admin.documents.verification', array_merge($verificationQuery, ['verification_status' => $value])) }}"
                   class="filter-chip {{ $statusFilter === $value ? 'active' : '' }}">{{ $label }}</a>
            @endforeach
        </div>

        <div>
            <select class="form-select form-select-sm rounded-pill" style="min-width: 220px;" onchange="window.location = this.value;">
                <option value="{{ route('admin.documents.verification', array_merge($verificationQuery, ['document_type' => ''])) }}">All document types</option>
                @foreach(\App\Models\LandlordDocument::types() as $type => $label)
                    <option value="{{ route('admin.documents.verification', array_merge($verificationQuery, ['document_type' => $type])) }}"
                            @selected($documentType === $type)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="admin-table-card">
        <div class="admin-card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div class="fw-bold d-flex align-items-center gap-2">
                <i class="bi bi-inbox text-success"></i>
                <span>Document Queue Items</span>
            </div>
            <span class="badge rounded-pill bg-light text-secondary border">{{ $documents->total() }} records</span>
        </div>

        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th class="ps-3">Landlord</th>
                        <th>Document</th>
                        <th>Number</th>
                        <th>Issued</th>
                        <th>Expires</th>
                        <th>Expiration Status</th>
                        <th>Submitted</th>
                        <th>Status</th>
                        <th class="pe-3 text-end" style="min-width: 170px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($documents as $document)
                        @php
                            $expiration = $document->expirationInfo();
                        @endphp
                        <tr>
                            <td class="ps-3">
                                <div class="fw-bold text-dark">{{ $document->landlord?->full_name ?: 'Unknown' }}</div>
                                <div class="small text-muted">{{ $document->landlord?->email }}</div>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark">
                                    <i class="bi bi-file-earmark-text me-1 text-primary"></i>{{ $document->typeLabel($document->document_type) }}
                                </div>
                            </td>
                            <td class="text-secondary small">{{ $document->document_number ?: '—' }}</td>
                            <td>{{ $document->date_issued?->format('M d, Y') ?: '—' }}</td>
                            <td>{{ $document->expiration_date?->format('M d, Y') ?: '—' }}</td>
                            <td>
                                <x-admin.status-badge :status="$expiration['status']" />
                            </td>
                            <td class="text-secondary small">{{ $document->submitted_at?->format('M d, Y') ?: '—' }}</td>
                            <td>
                                <x-admin.status-badge :status="$document->verification_status" />
                                @if($document->verification_status === 'rejected' && filled($document->rejection_reason))
                                    <div class="small text-danger mt-1" title="{{ $document->rejection_reason }}" style="max-width: 180px;">Reason: {{ \Illuminate\Support\Str::limit($document->rejection_reason, 45) }}</div>
                                @endif
                                @if(!$document->is_current)
                                    <div class="small text-muted mt-1"><i class="bi bi-clock-history me-1"></i>Historical version</div>
                                @endif
                            </td>
                            <td class="pe-3 text-end">
                                <div class="d-inline-flex align-items-center gap-1">
                                    <button type="button" onclick="openDocumentPreview('{{ file_download_url($document->file_path) }}', '{{ addslashes($document->typeLabel($document->document_type)) }} - {{ addslashes(optional($document->landlord)->full_name ?? 'Landlord') }}')" class="btn btn-sm btn-light border btn-action-icon text-secondary" title="View Document">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <a href="{{ file_download_url($document->file_path, true) }}" download class="btn btn-sm btn-light border btn-action-icon text-secondary" title="Download">
                                        <i class="bi bi-download"></i>
                                    </a>
                                    @if($document->is_current && $document->verification_status !== 'approved')
                                        <button type="button" class="btn btn-sm btn-brand btn-action-icon" data-bs-toggle="modal" data-bs-target="#approveModal{{ $document->id }}" title="Approve">
                                            <i class="bi bi-check2"></i>
                                        </button>
                                    @endif
                                    @if($document->is_current)
                                        <button type="button" class="btn btn-sm btn-outline-danger btn-action-icon" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $document->id }}" title="Reject">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9">
                                <x-admin.empty-state
                                    icon="bi-file-earmark-check"
                                    title="No documents awaiting review"
                                    message="The verification queue is clear for the selected filters."
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($documents->hasPages())
            <div class="admin-card-header border-top d-flex justify-content-center">
                {{ $documents->links() }}
            </div>
        @endif
    </div>
</div>

@foreach($documents as $document)
    @if($document->is_current)
    {{-- Approve modal --}}
    <div class="modal fade" id="approveModal{{ $document->id }}" tabindex="-1" aria-labelledby="approveModalLabel{{ $document->id }}" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow">
                <form method="POST" action="{{ route('admin.documents.approve', $document) }}">
                    @csrf
                    <div class="modal-header border-0 pb-0">
                        <h5 class="modal-title fw-bold" id="approveModalLabel{{ $document->id }}">
                            <i class="bi bi-check2-circle text-success me-2"></i>Approve {{ $document->typeLabel($document->document_type) }}
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body pt-2 text-start">
                        <div class="mb-1 fw-semibold">{{ $document->landlord?->full_name }}</div>
                        <div class="small text-muted mb-3">
                            {{ $document->typeLabel($document->document_type) }}
                            @if($document->document_number) · #{{ $document->document_number }} @endif
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="1" id="approve_confirm_{{ $document->id }}" required>
                            <label class="form-check-label small" for="approve_confirm_{{ $document->id }}">
                                I confirm this document is authentic and valid.
                            </label>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-sm btn-brand px-3">Confirm Approval</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Reject modal --}}
    <div class="modal fade" id="rejectModal{{ $document->id }}" tabindex="-1" aria-labelledby="rejectModalLabel{{ $document->id }}" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow">
                <form method="POST" action="{{ route('admin.documents.reject', $document) }}">
                    @csrf
                    <div class="modal-header border-0 pb-0">
                        <h5 class="modal-title fw-bold" id="rejectModalLabel{{ $document->id }}">
                            <i class="bi bi-x-octagon text-danger me-2"></i>Reject {{ $document->typeLabel($document->document_type) }}
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body pt-2 text-start">
                        <div class="mb-2 fw-semibold">{{ $document->landlord?->full_name }}</div>
                        <div class="mb-3">
                            <label for="rejection_reason_{{ $document->id }}" class="form-label small fw-semibold">Rejection reason <span class="text-danger">*</span></label>
                            <textarea id="rejection_reason_{{ $document->id }}" name="rejection_reason" class="form-control" rows="3" maxlength="500" placeholder="State why this document cannot be accepted." required></textarea>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="1" id="reject_confirm_{{ $document->id }}" required>
                            <label class="form-check-label small" for="reject_confirm_{{ $document->id }}">
                                I confirm this document should be rejected.
                            </label>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-sm btn-danger px-3">Confirm Rejection</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
@endforeach
@endsection

