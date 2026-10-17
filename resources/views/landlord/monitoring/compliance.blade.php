@extends('layouts.landlord')

@section('title', 'Compliance Status')

@section('content')
<div class="landlord-page-shell">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
        <div>
            <div class="landlord-header-eyebrow">Monitoring Hub</div>
            <h1 class="landlord-header-title mb-1">Compliance &amp; Accreditation Status</h1>
            <div class="landlord-header-subtitle">Monitor institutional accreditation, document verification statuses, and permit expirations.</div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="landlord-summary-strip">
                <span class="landlord-summary-dot"></span>
                @if($accreditationStatus === 'approved')
                    <span class="text-success fw-bold">Accredited</span>
                @elseif($accreditationStatus === 'rejected')
                    <span class="text-danger fw-bold">Accreditation Rejected</span>
                @else
                    <span class="text-warning-emphasis fw-bold">Pending Review</span>
                @endif
                <span class="text-muted">·</span>
                <span>{{ $approvedCount }} of {{ count($documents) }} Verified</span>
                @if($expiringSoonCount > 0)
                    <span class="text-muted">·</span>
                    <span class="text-warning-emphasis fw-bold">{{ $expiringSoonCount }} expiring soon</span>
                @endif
                @if($expiredCount > 0)
                    <span class="text-muted">·</span>
                    <span class="text-danger fw-bold">{{ $expiredCount }} expired</span>
                @endif
            </span>
        </div>
    </div>

    <!-- Accreditation Summary Banner -->
    <div class="landlord-card mb-4">
        <div class="card-body p-3 p-md-4 d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-3 d-flex align-items-center justify-content-center bg-light text-brand" style="width: 48px; height: 48px;">
                    <i class="bi bi-patch-check fs-3"></i>
                </div>
                <div>
                    <div class="small text-muted text-uppercase fw-bold">Institutional Accreditation Status</div>
                    <div class="d-flex align-items-center gap-2 mt-1">
                        @if($accreditationStatus === 'approved')
                            <span class="fs-5 fw-bold text-success"><i class="bi bi-check-circle-fill me-1"></i> Accredited Boarding House</span>
                        @elseif($accreditationStatus === 'rejected')
                            <span class="fs-5 fw-bold text-danger"><i class="bi bi-x-circle-fill me-1"></i> Accreditation Rejected</span>
                        @else
                            <span class="fs-5 fw-bold text-warning-emphasis"><i class="bi bi-hourglass-split me-1"></i> Pending Verification</span>
                        @endif
                    </div>
                    <div class="text-muted small mt-1">
                        Boarding house operations unlock upon approved business permit verification by the MinSU administration.
                    </div>
                </div>
            </div>
            <div>
                <a href="{{ route('landlord.documents.index') }}" class="btn btn-sm btn-outline-brand rounded-pill px-3">
                    Manage Documents <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Required Documents Checklist -->
    <div class="landlord-card mb-4">
        <div class="card-header bg-white border-bottom p-3 p-md-4">
            <h2 class="h5 mb-0 fw-bold text-dark">Required Documents Checklist</h2>
        </div>

        <!-- Desktop Table View -->
        <div class="d-none d-md-block table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light small text-muted">
                    <tr>
                        <th class="ps-4">Document Type</th>
                        <th>File Uploaded</th>
                        <th>Verification Status</th>
                        <th>Expiration Condition</th>
                        <th>Issue / Expiry Date</th>
                        <th>Admin Notes</th>
                        <th class="text-end pe-4">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($documents as $doc)
                        @php
                            $status = $doc['verification_status'];
                            $statusBadge = match($status) {
                                'approved' => 'bg-success-subtle text-success border-success-subtle',
                                'rejected' => 'bg-danger-subtle text-danger border-danger-subtle',
                                'pending' => 'bg-warning-subtle text-warning-emphasis border-warning-subtle',
                                default => 'bg-secondary-subtle text-secondary border-secondary-subtle'
                            };
                            $statusLabel = match($status) {
                                'approved' => 'Approved',
                                'rejected' => 'Rejected',
                                'pending' => 'Pending Review',
                                default => 'Not Submitted'
                            };

                            $exp = $doc['expiration'];
                        @endphp
                        <tr>
                            <td class="ps-4">
                                <div class="fw-semibold text-dark">{{ $doc['label'] }}</div>
                                <div class="small text-muted">{{ $doc['type'] }}</div>
                            </td>
                            <td>
                                @if($doc['has_file'])
                                    <span class="badge bg-light text-success border border-success-subtle rounded-pill">
                                        <i class="bi bi-file-earmark-check me-1"></i> File Attached
                                    </span>
                                @else
                                    <span class="badge bg-light text-muted border rounded-pill">
                                        <i class="bi bi-file-earmark-x me-1"></i> No File
                                    </span>
                                @endif
                            </td>
                            <td>
                                <span class="badge rounded-pill border {{ $statusBadge }}">
                                    {{ $statusLabel }}
                                </span>
                            </td>
                            <td>
                                @if($exp)
                                    @if($exp['status'] === 'expired')
                                        <span class="badge rounded-pill bg-danger-subtle text-danger border border-danger-subtle">
                                            <i class="bi bi-exclamation-triangle me-1"></i> {{ $exp['label'] }}
                                        </span>
                                    @elseif($exp['status'] === 'expiring_soon')
                                        <span class="badge rounded-pill bg-warning-subtle text-warning-emphasis border border-warning-subtle">
                                            <i class="bi bi-clock-history me-1"></i> {{ $exp['label'] }}
                                        </span>
                                    @else
                                        <span class="badge rounded-pill bg-success-subtle text-success border border-success-subtle">
                                            <i class="bi bi-shield-check me-1"></i> {{ $exp['label'] }}
                                        </span>
                                    @endif
                                @else
                                    <span class="text-muted small">—</span>
                                @endif
                            </td>
                            <td>
                                @if($doc['record'] && $doc['record']->expiration_date)
                                    <div class="small text-dark fw-medium">Exp: {{ $doc['record']->expiration_date->format('M d, Y') }}</div>
                                    @if($doc['record']->issued_date)
                                        <div class="small text-muted">Issued: {{ $doc['record']->issued_date->format('M d, Y') }}</div>
                                    @endif
                                @else
                                    <span class="text-muted small">—</span>
                                @endif
                            </td>
                            <td class="small text-muted" style="max-width: 200px;">
                                {{ $doc['notes'] ?: 'No notes' }}
                            </td>
                            <td class="text-end pe-4">
                                <a href="{{ route('landlord.documents.index') }}" class="btn btn-sm btn-outline-brand rounded-pill px-3">
                                    Manage
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Mobile Card/Row View -->
        <div class="d-md-none divide-y">
            @foreach($documents as $doc)
                @php
                    $status = $doc['verification_status'];
                    $statusBadge = match($status) {
                        'approved' => 'bg-success-subtle text-success border-success-subtle',
                        'rejected' => 'bg-danger-subtle text-danger border-danger-subtle',
                        'pending' => 'bg-warning-subtle text-warning-emphasis border-warning-subtle',
                        default => 'bg-secondary-subtle text-secondary border-secondary-subtle'
                    };
                    $statusLabel = match($status) {
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                        'pending' => 'Pending Review',
                        default => 'Not Submitted'
                    };
                    $exp = $doc['expiration'];
                @endphp
                <div class="p-3 border-bottom">
                    <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                        <div>
                            <div class="fw-bold text-dark">{{ $doc['label'] }}</div>
                            <div class="small text-muted">{{ $doc['type'] }}</div>
                        </div>
                        <span class="badge rounded-pill border {{ $statusBadge }}">
                            {{ $statusLabel }}
                        </span>
                    </div>

                    <div class="d-flex flex-wrap gap-2 align-items-center mb-2">
                        @if($doc['has_file'])
                            <span class="badge bg-light text-success border border-success-subtle rounded-pill small">
                                <i class="bi bi-file-earmark-check me-1"></i> File Attached
                            </span>
                        @else
                            <span class="badge bg-light text-muted border rounded-pill small">
                                <i class="bi bi-file-earmark-x me-1"></i> No File
                            </span>
                        @endif

                        @if($exp)
                            @if($exp['status'] === 'expired')
                                <span class="badge rounded-pill bg-danger-subtle text-danger border border-danger-subtle small">
                                    <i class="bi bi-exclamation-triangle me-1"></i> {{ $exp['label'] }}
                                </span>
                            @elseif($exp['status'] === 'expiring_soon')
                                <span class="badge rounded-pill bg-warning-subtle text-warning-emphasis border border-warning-subtle small">
                                    <i class="bi bi-clock-history me-1"></i> {{ $exp['label'] }}
                                </span>
                            @endif
                        @endif
                    </div>

                    @if($doc['record'] && $doc['record']->expiration_date)
                        <div class="small text-muted mb-2">
                            Expires: <strong class="text-dark">{{ $doc['record']->expiration_date->format('M d, Y') }}</strong>
                        </div>
                    @endif

                    @if($doc['notes'])
                        <div class="small text-muted fst-italic mb-2">
                            Admin note: {{ $doc['notes'] }}
                        </div>
                    @endif

                    <div class="text-end pt-1">
                        <a href="{{ route('landlord.documents.index') }}" class="btn btn-sm btn-outline-brand rounded-pill px-3">
                            Manage Document
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
