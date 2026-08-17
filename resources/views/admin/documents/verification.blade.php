@extends('layouts.admin')

@section('title', 'Document Verification')

@section('content')
<style>
    .doc-table-wrap { overflow-x:auto; }
    .doc-table { width:100%; border-collapse:collapse; font-size:.86rem; }
    .doc-table th { padding:.55rem .8rem; font-size:.7rem; text-transform:uppercase; letter-spacing:.05em; color:rgba(2,8,20,.45); font-weight:600; border-bottom:1px solid rgba(2,8,20,.08); white-space:nowrap; text-align:left; }
    .doc-table td { padding:.65rem .8rem; border-bottom:1px solid rgba(2,8,20,.06); vertical-align:middle; }
    .doc-table tr:last-child td { border-bottom:none; }
    .doc-table tr:hover td { background:rgba(22,101,52,.025); }
    .sbadge { display:inline-flex; align-items:center; gap:.35rem; border-radius:999px; padding:.25rem .62rem; font-size:.73rem; font-weight:700; white-space:nowrap; }
    .sb-pending { background:#e0f2fe; color:#075985; }
    .sb-approved{ background:#dcfce7; color:#166534; }
    .sb-rejected{ background:#fee2e2; color:#991b1b; }
    .sb-valid   { background:#dcfce7; color:#166534; }
    .sb-soon    { background:#fef3c7; color:#92400e; }
    .sb-expired { background:#fee2e2; color:#991b1b; }
    .filter-pill { display:inline-flex; align-items:center; gap:.35rem; border-radius:999px; padding:.38rem .85rem; font-size:.82rem; font-weight:600; border:1px solid rgba(2,8,20,.14); text-decoration:none; color:#475569; background:#fff; }
    .filter-pill.active { background:#166534; color:#fff; border-color:#166534; }
</style>

<div class="mb-4 d-flex flex-wrap justify-content-between align-items-start gap-2">
    <div>
        <div class="text-uppercase small text-secondary fw-semibold mb-1">Approvals</div>
        <h1 class="h4 mb-0 fw-bold"><i class="bi bi-file-earmark-check me-1" style="color:#166534;"></i> Document Verification</h1>
        <p class="text-secondary small mb-0 mt-1">Review and approve or reject current landlord documents. Historical versions remain available for auditing.</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        @if($landlordId > 0)
            <a href="{{ route('admin.users.landlords.show', $landlordId) }}" class="btn btn-sm btn-outline-secondary rounded-pill">
                <i class="bi bi-arrow-left me-1"></i>Landlord Details
            </a>
        @endif
        <a href="{{ route('admin.documents.monitoring') }}" class="btn btn-sm btn-outline-secondary rounded-pill">
            <i class="bi bi-activity me-1"></i>Document Monitoring
        </a>
        @if($includeHistory)
            <a href="{{ route('admin.documents.verification', ['landlord_id' => $landlordId, 'verification_status' => $statusFilter, 'document_type' => $documentType]) }}" class="btn btn-sm btn-outline-success rounded-pill">
                <i class="bi bi-clock-history me-1"></i>Current Versions
            </a>
        @else
            <a href="{{ route('admin.documents.verification', ['landlord_id' => $landlordId, 'verification_status' => 'all', 'document_type' => $documentType, 'history' => 1]) }}" class="btn btn-sm btn-outline-secondary rounded-pill">
                <i class="bi bi-clock-history me-1"></i>View History
            </a>
        @endif
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success border-0 shadow-sm py-2 mb-3">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-danger border-0 shadow-sm py-2 mb-3">{{ session('error') }}</div>
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
<div class="d-flex flex-wrap gap-2 mb-3 align-items-center">
    <div class="d-flex flex-wrap gap-1">
        @foreach(['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected', 'all' => 'All'] as $value => $label)
            <a href="{{ route('admin.documents.verification', array_merge($verificationQuery, ['verification_status' => $value])) }}"
               class="filter-pill {{ $statusFilter === $value ? 'active' : '' }}">{{ $label }}</a>
        @endforeach
    </div>
    <span class="text-secondary small mx-1">|</span>
    <select class="form-select form-select-sm rounded-pill" style="width:auto;" onchange="window.location = this.value;">
        <option value="{{ route('admin.documents.verification', array_merge($verificationQuery, ['document_type' => ''])) }}">All document types</option>
        @foreach(\App\Models\LandlordDocument::types() as $type => $label)
            <option value="{{ route('admin.documents.verification', array_merge($verificationQuery, ['document_type' => $type])) }}"
                    @selected($documentType === $type)>{{ $label }}</option>
        @endforeach
    </select>
</div>

<div class="bg-white border rounded-4 shadow-sm overflow-hidden">
    <div class="table-responsive">
        <table class="doc-table">
            <thead>
                <tr>
                    <th>Landlord</th>
                    <th>Document</th>
                    <th>Number</th>
                    <th>Issued</th>
                    <th>Expires</th>
                    <th>Expiration Status</th>
                    <th>Submitted</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($documents as $document)
                    @php
                        $expiration = $document->expirationInfo();
                        $eBadge = match($expiration['status']) {
                            'valid' => 'sb-valid',
                            'expiring_soon' => 'sb-soon',
                            'expired' => 'sb-expired',
                            default => 'sb-pending',
                        };
                        $vBadge = match($document->verification_status) {
                            'approved' => 'sb-approved',
                            'rejected' => 'sb-rejected',
                            default => 'sb-pending',
                        };
                    @endphp
                    <tr>
                        <td>
                            <div class="fw-semibold">{{ $document->landlord?->full_name ?: 'Unknown' }}</div>
                            <div class="small text-secondary">{{ $document->landlord?->email }}</div>
                        </td>
                        <td class="fw-semibold"><i class="bi bi-file-earmark-text me-1 text-secondary"></i>{{ $document->typeLabel($document->document_type) }}</td>
                        <td class="text-secondary">{{ $document->document_number ?: '—' }}</td>
                        <td>{{ $document->date_issued?->format('M d, Y') ?: '—' }}</td>
                        <td>{{ $document->expiration_date?->format('M d, Y') ?: '—' }}</td>
                        <td><span class="sbadge {{ $eBadge }}">{{ ucwords(str_replace('_', ' ', $expiration['status'])) }}</span></td>
                        <td class="text-secondary">{{ $document->submitted_at?->format('M d, Y') ?: '—' }}</td>
                        <td>
                            <span class="sbadge {{ $vBadge }}">{{ ucfirst($document->verification_status) }}</span>
                            @if($document->verification_status === 'rejected' && filled($document->rejection_reason))
                                <div class="small text-danger mt-1" title="{{ $document->rejection_reason }}">Reason: {{ \Illuminate\Support\Str::limit($document->rejection_reason, 55) }}</div>
                            @endif
                            @if(!$document->is_current)
                                <div class="small text-secondary mt-1"><i class="bi bi-clock-history me-1"></i>Historical version</div>
                            @endif
                        </td>
                        <td class="text-end">
                            <div class="d-flex flex-wrap justify-content-end gap-1">
                                <a href="{{ file_download_url($document->file_path) }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary rounded-pill">
                                    <i class="bi bi-eye me-1"></i>View
                                </a>
                                <a href="{{ file_download_url($document->file_path, true) }}" class="btn btn-sm btn-outline-secondary rounded-pill">
                                    <i class="bi bi-download me-1"></i>Download
                                </a>
                                @if($document->is_current && $document->verification_status !== 'approved')
                                    <button type="button" class="btn btn-sm btn-success rounded-pill" data-bs-toggle="modal" data-bs-target="#approveModal{{ $document->id }}">Approve</button>
                                @endif
                                @if($document->is_current)
                                    <button type="button" class="btn btn-sm btn-outline-danger rounded-pill" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $document->id }}">Reject</button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center text-secondary py-4">No documents match the selected filters.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if($documents->hasPages())
    <div class="bg-white border rounded-4 shadow-sm px-3 py-3 mt-3">{{ $documents->links() }}</div>
@endif

@foreach($documents as $document)
    @if($document->is_current)
    {{-- Approve modal --}}
    <div class="modal fade" id="approveModal{{ $document->id }}" tabindex="-1" aria-labelledby="approveModalLabel{{ $document->id }}" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow">
                <form method="POST" action="{{ route('admin.documents.approve', $document) }}">
                    @csrf
                    <div class="modal-header border-0 pb-0">
                        <h5 class="modal-title fw-semibold" id="approveModalLabel{{ $document->id }}">Approve {{ $document->typeLabel($document->document_type) }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body pt-2">
                        <div class="mb-1 fw-semibold">{{ $document->landlord?->full_name }}</div>
                        <div class="small text-secondary mb-3">
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
                        <button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success rounded-pill px-3">Confirm Approval</button>
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
                        <h5 class="modal-title fw-semibold" id="rejectModalLabel{{ $document->id }}">Reject {{ $document->typeLabel($document->document_type) }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body pt-2">
                        <div class="mb-2 fw-semibold">{{ $document->landlord?->full_name }}</div>
                        <div class="mb-3">
                            <label for="rejection_reason_{{ $document->id }}" class="form-label small">Rejection reason <span class="text-danger">*</span></label>
                            <textarea id="rejection_reason_{{ $document->id }}" name="rejection_reason" class="form-control" rows="3" maxlength="500" required></textarea>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="1" id="reject_confirm_{{ $document->id }}" required>
                            <label class="form-check-label small" for="reject_confirm_{{ $document->id }}">
                                I confirm this document should be rejected.
                            </label>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger rounded-pill px-3">Confirm Rejection</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
@endforeach
@endsection
