@extends('layouts.landlord')

@section('title', 'Documents & Requirements')

@section('content')
<style>
    .doc-card { background:#fff; border:1px solid rgba(2,8,20,.08); border-radius:1rem; box-shadow:0 10px 26px rgba(2,8,20,.06); }
    .doc-label { font-size:.72rem; text-transform:uppercase; letter-spacing:.05em; color:rgba(2,8,20,.45); }
    .doc-value { color:#0f172a; }
    .doc-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(180px,1fr)); gap:.85rem; }
    .stat-badge { display:inline-flex; align-items:center; gap:.4rem; border-radius:999px; padding:.28rem .7rem; font-size:.76rem; font-weight:700; white-space:nowrap; }
    .sb-valid   { background:#dcfce7; color:#166534; }
    .sb-soon    { background:#fef3c7; color:#92400e; }
    .sb-expired { background:#fee2e2; color:#991b1b; }
    .sb-pending { background:#e0f2fe; color:#075985; }
    .sb-approved{ background:#dcfce7; color:#166534; }
    .sb-rejected{ background:#fee2e2; color:#991b1b; }
    .sb-none    { background:#e2e8f0; color:#475569; }
</style>

<div class="mb-4 d-flex flex-wrap justify-content-between align-items-start gap-2">
    <div>
        <div class="text-uppercase small text-secondary fw-semibold mb-1">Compliance</div>
        <h1 class="h4 mb-0 fw-bold"><i class="bi bi-folder-check me-1" style="color:#14532d;"></i> Documents &amp; Requirements</h1>
        <p class="text-secondary small mb-0 mt-1">Upload the required documents for your boarding house. Submitted documents are reviewed by the admin.</p>
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

<div class="row g-4">
    @foreach($documents as $doc)
        @php
            $record = $doc['record'];
            $hasFile = (bool) $doc['has_file'];
            $vStatus = $doc['verification_status'];
            $expiration = $doc['expiration'];

            $vBadge = match($vStatus) {
                'approved' => 'sb-approved',
                'rejected' => 'sb-rejected',
                'pending'  => 'sb-pending',
                default    => 'sb-none',
            };
            $vLabel = match($vStatus) {
                'approved' => 'Approved',
                'rejected' => 'Rejected',
                'pending'  => 'Pending verification',
                default    => 'Not submitted',
            };

            $eBadge = match($expiration['status'] ?? '') {
                'valid' => 'sb-valid',
                'expiring_soon' => 'sb-soon',
                'expired' => 'sb-expired',
                default => 'sb-none',
            };
            $eLabel = match($expiration['status'] ?? '') {
                'valid' => 'Valid',
                'expiring_soon' => 'Expiring soon',
                'expired' => 'Expired',
                default => 'No expiration date',
            };

            $canReplace = $hasFile;
            $filePath = $record ? $record->file_path : $doc['legacy_path'];
        @endphp

        <div class="col-12 col-lg-6">
            <div class="doc-card p-4 h-100">
                <div class="d-flex justify-content-between align-items-start gap-2 mb-3">
                    <div>
                        <h5 class="fw-semibold mb-1"><i class="bi bi-file-earmark-text me-1" style="color:#14532d;"></i>{{ $doc['label'] }}</h5>
                        @if($record && filled($record->document_number))
                            <div class="small text-secondary">Document #: <strong class="doc-value">{{ $record->document_number }}</strong></div>
                        @elseif($record || $doc['legacy_path'])
                            <div class="small text-secondary">Document #: <span class="text-muted">—</span></div>
                        @else
                            <div class="small text-secondary">Not yet submitted</div>
                        @endif
                    </div>
                    <span class="stat-badge {{ $vBadge }}"><i class="bi bi-shield-check"></i>{{ $vLabel }}</span>
                </div>

                <div class="doc-grid mb-3">
                    <div>
                        <div class="doc-label">Date Issued</div>
                        <div class="doc-value small fw-semibold">{{ $record?->date_issued?->format('M d, Y') ?: ($doc['legacy_path'] ? '—' : '—') }}</div>
                    </div>
                    <div>
                        <div class="doc-label">Expiration Date</div>
                        <div class="doc-value small fw-semibold">{{ $record?->expiration_date?->format('M d, Y') ?: ($doc['legacy_path'] ? '—' : '—') }}</div>
                    </div>
                </div>

                <div class="d-flex flex-wrap gap-2 mb-3">
                    <span class="stat-badge {{ $eBadge }}"><i class="bi bi-calendar2-check"></i>{{ $eLabel }}</span>
                    @if($expiration && $expiration['label'] && $expiration['label'] !== 'No expiration date')
                        <span class="stat-badge sb-none">{{ $expiration['label'] }}</span>
                    @endif
                    @if($vStatus === 'rejected' && filled($record?->rejection_reason))
                        <span class="stat-badge sb-rejected"><i class="bi bi-x-octagon"></i>{{ Str::limit($record->rejection_reason, 42) }}</span>
                    @endif
                </div>

                @if($record)
                    <div class="small text-secondary mb-3">
                        Submitted: <strong>{{ $record->submitted_at?->format('M d, Y') ?: '—' }}</strong>
                        @if($record->approved_at)
                            &nbsp;·&nbsp; Approved: <strong>{{ $record->approved_at->format('M d, Y') }}</strong>
                        @endif
                    </div>
                @endif

                <div class="d-flex flex-wrap gap-2">
                    @if($hasFile)
                        <a href="{{ file_download_url($filePath) }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary rounded-pill">
                            <i class="bi bi-eye me-1"></i>View submitted file
                        </a>
                    @endif

                    @if($vStatus === 'approved')
                        <button type="button" class="btn btn-sm btn-outline-brand rounded-pill" data-bs-toggle="modal" data-bs-target="#replaceModal{{ $doc['type'] }}">
                            <i class="bi bi-arrow-repeat me-1"></i>Renew / Replace
                        </button>
                    @elseif($vStatus === 'rejected')
                        <button type="button" class="btn btn-sm btn-brand rounded-pill" data-bs-toggle="modal" data-bs-target="#replaceModal{{ $doc['type'] }}">
                            <i class="bi bi-arrow-repeat me-1"></i>Resubmit
                        </button>
                    @elseif($vStatus === 'pending')
                        @if($hasFile)
                            <span class="btn btn-sm btn-outline-secondary rounded-pill disabled">Awaiting admin review</span>
                        @else
                            <button type="button" class="btn btn-sm btn-brand rounded-pill" data-bs-toggle="modal" data-bs-target="#replaceModal{{ $doc['type'] }}">
                                <i class="bi bi-upload me-1"></i>Upload document
                            </button>
                        @endif
                    @else
                        <button type="button" class="btn btn-sm btn-brand rounded-pill" data-bs-toggle="modal" data-bs-target="#replaceModal{{ $doc['type'] }}">
                            <i class="bi bi-upload me-1"></i>Upload document
                        </button>
                    @endif
                </div>
            </div>
        </div>

        {{-- Upload / Replace modal --}}
        <div class="modal fade" id="replaceModal{{ $doc['type'] }}" tabindex="-1" aria-labelledby="replaceModalLabel{{ $doc['type'] }}" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content rounded-4 border-0 shadow">
                    <form method="POST" action="{{ $record ? route('landlord.documents.resubmit', $record) : route('landlord.documents.store') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="modal-header border-0 pb-0">
                            <h5 class="modal-title fw-semibold" id="replaceModalLabel{{ $doc['type'] }}">
                                {{ $record ? 'Replace / Resubmit' : 'Upload' }} {{ $doc['label'] }}
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body pt-2">
                            <input type="hidden" name="document_type" value="{{ $doc['type'] }}">

                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Document file *</label>
                                <input type="file" name="file" class="form-control" accept=".pdf,.jpg,.jpeg,.png" required>
                                <div class="form-text">PDF, JPG, JPEG, or PNG. Maximum 2 MB.</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Document number</label>
                                <input type="text" name="document_number" class="form-control" maxlength="100"
                                       value="{{ old('document_number', $record?->document_number) }}"
                                       placeholder="e.g. BP-2026-00123">
                            </div>
                            <div class="row g-3">
                                <div class="col-6">
                                    <label class="form-label small fw-semibold">Date issued</label>
                                    <input type="date" name="date_issued" class="form-control"
                                           value="{{ old('date_issued', $record?->date_issued?->format('Y-m-d')) }}">
                                </div>
                                <div class="col-6">
                                    <label class="form-label small fw-semibold">Expiration date</label>
                                    <input type="date" name="expiration_date" class="form-control"
                                           value="{{ old('expiration_date', $record?->expiration_date?->format('Y-m-d')) }}">
                                </div>
                            </div>
                            <div class="small text-secondary mt-3">
                                <i class="bi bi-info-circle me-1"></i>
                                Replacing an approved or rejected document returns it to <strong>pending</strong> for a new admin review.
                            </div>
                        </div>
                        <div class="modal-footer border-0 pt-0">
                            <button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-brand rounded-pill px-4">Submit for review</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endforeach
</div>

<div class="alert alert-light border rounded-4 mt-4 small text-secondary">
    <i class="bi bi-shield-lock me-1"></i>
    Uploaded documents are private and only visible to you and system administrators. Verification status and expiration status are tracked separately: an approved document may still expire and require renewal.
</div>
@endsection
