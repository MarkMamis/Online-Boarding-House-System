@extends('layouts.landlord')

@section('title', 'Compliance Status')

@section('content')
<div class="container-fluid px-2 px-md-3 py-2">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <div class="text-uppercase small text-muted fw-semibold">Monitoring Hub</div>
            <h1 class="h3 mb-1">Compliance &amp; Accreditation Status</h1>
            <div class="text-muted small">Monitor institutional accreditation, document verification statuses, and permit expirations.</div>
        </div>
        <a href="{{ route('landlord.documents.index') }}" class="btn btn-brand rounded-pill px-3">
            <i class="bi bi-file-earmark-arrow-up me-1"></i> Document Renewals &amp; Uploads
        </a>
    </div>

    <!-- Accreditation Banner -->
    <div class="card shadow-sm border-0 rounded-4 mb-4 overflow-hidden bg-white">
        <div class="card-body p-4 d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-3 d-flex align-items-center justify-content-center bg-light text-brand" style="width: 56px; height: 56px;">
                    <i class="bi bi-patch-check fs-2"></i>
                </div>
                <div>
                    <div class="small text-muted text-uppercase fw-bold">Institutional Accreditation Status</div>
                    <div class="d-flex align-items-center gap-2 mt-1">
                        @if($accreditationStatus === 'approved')
                            <span class="fs-5 fw-bold text-success"><i class="bi bi-check-circle-fill me-1"></i> Accredited Boarding House</span>
                        @elseif($accreditationStatus === 'rejected')
                            <span class="fs-5 fw-bold text-danger"><i class="bi bi-x-circle-fill me-1"></i> Accreditation Rejected</span>
                        @else
                            <span class="fs-5 fw-bold text-warning"><i class="bi bi-hourglass-split me-1"></i> Pending Verification</span>
                        @endif
                    </div>
                    <div class="text-muted small mt-1">
                        Boarding house operations unlock upon approved business permit verification by the MinSU administration.
                    </div>
                </div>
            </div>
            <div>
                <a href="{{ route('landlord.documents.index') }}" class="btn btn-outline-brand rounded-pill px-3">
                    View Submission Queue <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Compliance Breakdown Metrics -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card shadow-sm border-0 rounded-4 p-3 h-100 bg-white">
                <div class="small text-muted mb-1">Required Types</div>
                <div class="fs-4 fw-bold text-dark">{{ count($documents) }}</div>
                <div class="small text-muted">Permits &amp; certs</div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card shadow-sm border-0 rounded-4 p-3 h-100 bg-white">
                <div class="small text-muted mb-1">Approved &amp; Valid</div>
                <div class="fs-4 fw-bold text-success">{{ max(0, $approvedCount - $expiredCount) }}</div>
                <div class="small text-muted">In compliance</div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card shadow-sm border-0 rounded-4 p-3 h-100 bg-white">
                <div class="small text-muted mb-1">Expiring Soon</div>
                <div class="fs-4 fw-bold text-warning">{{ $expiringSoonCount }}</div>
                <div class="small text-muted">Needs renewal soon</div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card shadow-sm border-0 rounded-4 p-3 h-100 bg-white">
                <div class="small text-muted mb-1">Expired</div>
                <div class="fs-4 fw-bold text-danger">{{ $expiredCount }}</div>
                <div class="small text-muted">Requires immediate update</div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card shadow-sm border-0 rounded-4 p-3 h-100 bg-white">
                <div class="small text-muted mb-1">Under Review</div>
                <div class="fs-4 fw-bold text-primary">{{ $pendingCount }}</div>
                <div class="small text-muted">Submitted to Admin</div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card shadow-sm border-0 rounded-4 p-3 h-100 bg-white">
                <div class="small text-muted mb-1">Not Submitted</div>
                <div class="fs-4 fw-bold text-secondary">{{ $missingCount }}</div>
                <div class="small text-muted">Pending upload</div>
            </div>
        </div>
    </div>

    <!-- Detailed Compliance Documents Table -->
    <div class="card shadow-sm border-0 rounded-4 overflow-hidden bg-white mb-4">
        <div class="card-header bg-white border-bottom p-3 p-md-4">
            <h2 class="h5 mb-0 fw-bold">Required Documents Status</h2>
        </div>
        <div class="table-responsive">
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
                                'pending' => 'bg-warning-subtle text-warning border-warning-subtle',
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
                                        <span class="badge rounded-pill bg-warning-subtle text-warning border border-warning-subtle">
                                            <i class="bi bi-clock-history me-1"></i> {{ $exp['label'] }}
                                        </span>
                                    @else
                                        <span class="badge rounded-pill bg-success-subtle text-success border border-success-subtle">
                                            <i class="bi bi-shield-check me-1"></i> {{ $exp['label'] }}
                                        </span>
                                    @endif
                                @else
                                    <span class="text-muted small">No expiration set</span>
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
                                    Manage <i class="bi bi-arrow-right ms-1"></i>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
