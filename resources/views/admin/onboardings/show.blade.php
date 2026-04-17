@extends('layouts.admin')

@section('title', 'Onboarding Details')

@section('content')
<style>
    .onboarding-show-shell {
        background: linear-gradient(180deg, #ffffff 0%, #fbfdfc 100%);
        border: 1px solid rgba(2, 8, 20, .08);
        border-radius: 1.25rem;
        box-shadow: 0 10px 26px rgba(2, 8, 20, .06);
        padding: 1.25rem;
    }

    .section-muted {
        color: rgba(2, 8, 20, .58);
    }

    .onboarding-hero {
        border: 1px solid rgba(20, 83, 45, .14);
        border-radius: 1rem;
        background: radial-gradient(560px 240px at 100% 0%, rgba(167, 243, 208, .34), transparent 62%), linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
        padding: 1rem;
    }

    .id-chip {
        display: inline-flex;
        align-items: center;
        gap: .4rem;
        border-radius: 999px;
        border: 1px solid rgba(20, 83, 45, .24);
        background: rgba(167, 243, 208, .22);
        color: #14532d;
        font-size: .78rem;
        font-weight: 700;
        padding: .32rem .72rem;
    }

    .surface-card {
        border: 1px solid rgba(2, 8, 20, .08);
        border-radius: 1rem;
        background: #fff;
        box-shadow: 0 8px 20px rgba(2, 8, 20, .05);
        overflow: hidden;
        height: 100%;
    }

    .surface-head {
        padding: .85rem 1rem;
        border-bottom: 1px solid rgba(2, 8, 20, .08);
        background: #fff;
        font-weight: 600;
    }

    .detail-item {
        padding: .6rem 0;
        border-bottom: 1px dashed rgba(2, 8, 20, .08);
    }

    .detail-item:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }

    .detail-label {
        font-size: .75rem;
        text-transform: uppercase;
        letter-spacing: .05em;
        color: rgba(2, 8, 20, .54);
        margin-bottom: .2rem;
        font-weight: 700;
    }

    .status-badge {
        font-size: .72rem;
        border-radius: 999px;
        padding: .38rem .62rem;
        text-transform: uppercase;
        letter-spacing: .04em;
    }

    .onboarding-progress {
        border: 1px solid rgba(2, 8, 20, .08);
        border-radius: 1rem;
        background: #fff;
        box-shadow: 0 8px 20px rgba(2, 8, 20, .05);
        padding: 1rem;
    }

    .onboarding-progress .progress {
        height: 14px;
        border-radius: 999px;
        background: rgba(148, 163, 184, .2);
    }

    .onboarding-progress .progress-bar {
        background: linear-gradient(90deg, #166534, #22c55e);
        font-size: .72rem;
        font-weight: 600;
    }

    .doc-card {
        border: 1px solid rgba(2, 8, 20, .08);
        border-radius: .9rem;
        background: #fff;
        height: 100%;
    }

    .doc-icon {
        width: 48px;
        height: 48px;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(239, 68, 68, .12);
        color: #dc2626;
        border: 1px solid rgba(239, 68, 68, .2);
    }

    .contract-callout {
        border: 1px dashed rgba(217, 119, 6, .4);
        background: rgba(254, 243, 199, .38);
        border-radius: .8rem;
        padding: .75rem .85rem;
        color: #92400e;
    }

    @media (max-width: 767.98px) {
        .onboarding-show-shell {
            padding: .95rem;
        }
    }
</style>

@php
    $progress = match($onboarding->status) {
        'pending' => 20,
        'documents_uploaded' => 40,
        'documents_approved' => 60,
        'contract_signed' => 80,
        'deposit_paid' => 100,
        'completed' => 100,
        default => 0
    };
@endphp

<div class="onboarding-show-shell container-fluid py-2">
    <div class="onboarding-hero mb-4">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
            <div>
                <div class="text-uppercase small section-muted fw-semibold">Onboarding Management</div>
                <h1 class="h3 mb-1 text-success">Onboarding Details</h1>
                <p class="section-muted mb-0">Monitor tenant completion milestones, documents, and contract status.</p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <span class="id-chip"><i class="bi bi-hash"></i>{{ $onboarding->id }}</span>
                <!-- <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary rounded-pill px-3">
                    <i class="bi bi-house-door me-1"></i>Dashboard
                </a> -->
                <a href="{{ route('admin.onboardings.index') }}" class="btn btn-outline-secondary rounded-pill px-3">
                    <i class="bi bi-arrow-left me-1"></i>Back to List
                </a>
            </div>
        </div>
    </div>

    <div class="onboarding-progress mb-4">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h6 class="mb-0 fw-semibold">Onboarding Progress</h6>
            <span class="small section-muted">{{ $progress }}% complete</span>
        </div>
        <div class="progress mb-2">
            <div class="progress-bar" role="progressbar" style="width: {{ $progress }}%" aria-valuenow="{{ $progress }}" aria-valuemin="0" aria-valuemax="100">
                {{ $progress }}%
            </div>
        </div>
        <div class="d-flex justify-content-between small section-muted">
            <span>Documents</span>
            <span>Approval</span>
            <span>Contract</span>
            <span>Deposit</span>
            <span>Complete</span>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-12 col-lg-6">
            <div class="surface-card">
                <div class="surface-head"><i class="bi bi-person me-2"></i>Student Information</div>
                <div class="p-3">
                    <div class="detail-item">
                        <div class="detail-label">Full Name</div>
                        <div class="fw-semibold">{{ $onboarding->booking->student->full_name }}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Student ID</div>
                        <div>{{ $onboarding->booking->student->student_id }}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Email</div>
                        <div>{{ $onboarding->booking->student->email }}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Phone</div>
                        <div>{{ $onboarding->booking->student->phone ?? 'Not provided' }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-6">
            <div class="surface-card">
                <div class="surface-head"><i class="bi bi-building me-2"></i>Property and Room</div>
                <div class="p-3">
                    <div class="detail-item">
                        <div class="detail-label">Property</div>
                        <div class="fw-semibold">{{ $onboarding->booking->room->property->name }}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Address</div>
                        <div>{{ $onboarding->booking->room->property->address }}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Room</div>
                        <div>{{ $onboarding->booking->room->room_number }}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Monthly Rent</div>
                        <div>PHP {{ number_format($onboarding->booking->room->price, 2) }}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Lease Period</div>
                        <div>{{ $onboarding->booking->check_in->format('M d, Y') }} - {{ $onboarding->booking->check_out->format('M d, Y') }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-6">
            <div class="surface-card">
                <div class="surface-head"><i class="bi bi-person-badge me-2"></i>Landlord Information</div>
                <div class="p-3">
                    <div class="detail-item">
                        <div class="detail-label">Name</div>
                        <div class="fw-semibold">{{ $onboarding->booking->room->property->landlord->full_name }}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Email</div>
                        <div>{{ $onboarding->booking->room->property->landlord->email }}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Phone</div>
                        <div>{{ $onboarding->booking->room->property->landlord->phone ?? 'Not provided' }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-6">
            <div class="surface-card">
                <div class="surface-head"><i class="bi bi-clipboard2-check me-2"></i>Onboarding Status</div>
                <div class="p-3">
                    <div class="detail-item">
                        <div class="detail-label">Current Status</div>
                        <div>
                            @switch($onboarding->status)
                                @case('pending')
                                    <span class="badge status-badge text-bg-warning">Pending Documents</span>
                                    @break
                                @case('documents_uploaded')
                                    <span class="badge status-badge text-bg-info">Documents Uploaded</span>
                                    @break
                                @case('documents_approved')
                                    <span class="badge status-badge text-bg-primary">Documents Approved</span>
                                    @break
                                @case('contract_signed')
                                    <span class="badge status-badge text-bg-secondary">Contract Signed</span>
                                    @break
                                @case('deposit_paid')
                                    <span class="badge status-badge text-bg-success">Deposit Paid</span>
                                    @break
                                @case('completed')
                                    <span class="badge status-badge text-bg-success">Completed</span>
                                    @break
                                @default
                                    <span class="badge status-badge text-bg-light border">{{ ucfirst($onboarding->status) }}</span>
                            @endswitch
                        </div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Created</div>
                        <div>{{ $onboarding->created_at->format('M d, Y h:i A') }}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Last Updated</div>
                        <div>{{ $onboarding->updated_at->format('M d, Y h:i A') }}</div>
                    </div>
                    @if($onboarding->digital_id)
                        <div class="detail-item">
                            <div class="detail-label">Digital ID</div>
                            <div><code>{{ $onboarding->digital_id }}</code></div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="mt-4">
        <div class="d-flex align-items-center gap-2 mb-3">
            <h6 class="mb-0 fw-semibold"><i class="bi bi-file-earmark-text me-1"></i>Uploaded Documents</h6>
        </div>
        @if($onboarding->uploaded_documents && count($onboarding->uploaded_documents) > 0)
            <div class="row g-3">
                @foreach($onboarding->uploaded_documents as $document)
                    <div class="col-12 col-md-6 col-xl-4">
                        <div class="doc-card p-3 text-center">
                            <span class="doc-icon mb-2"><i class="bi bi-file-earmark-pdf"></i></span>
                            <div class="small text-muted mb-3 text-break">{{ basename($document) }}</div>
                            <div class="d-flex justify-content-center gap-2">
                                <a href="{{ route('documents.view', [$onboarding->id, basename($document)]) }}" target="_blank" class="btn btn-sm btn-outline-success rounded-pill px-3">
                                    <i class="bi bi-eye me-1"></i>View
                                </a>
                                <a href="{{ route('documents.view', [$onboarding->id, basename($document)]) }}?download=1" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                                    <i class="bi bi-download me-1"></i>Download
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="alert alert-warning border-0 shadow-sm mb-0">
                <i class="bi bi-exclamation-triangle me-2"></i>No documents have been uploaded yet.
            </div>
        @endif
    </div>

    <div class="mt-4">
        <div class="surface-card">
            <div class="surface-head"><i class="bi bi-file-earmark-check me-2"></i>Contract Information</div>
            <div class="p-3">
                @if($onboarding->contract_signed)
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <div class="detail-label">Contract Signed</div>
                            <div class="fw-semibold">{{ $onboarding->contract_signed_at ? $onboarding->contract_signed_at->format('M d, Y h:i A') : 'N/A' }}</div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="detail-label">Deposit Paid</div>
                            <div>
                                @if($onboarding->deposit_paid)
                                    <span class="text-success fw-semibold">Yes</span>
                                    <span class="section-muted">({{ $onboarding->deposit_paid_at ? $onboarding->deposit_paid_at->format('M d, Y h:i A') : 'N/A' }})</span>
                                @else
                                    <span class="text-danger fw-semibold">No</span>
                                @endif
                            </div>
                        </div>
                    </div>
                @else
                    <div class="contract-callout mb-3">
                        <i class="bi bi-exclamation-circle me-1"></i>
                        Contract has not been signed yet. The tenant must complete document upload and approval first.
                    </div>
                @endif

                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('admin.onboardings.contract', $onboarding) }}" target="_blank" class="btn btn-success rounded-pill px-3">
                        <i class="bi bi-eye me-1"></i>View Contract
                    </a>
                    <a href="{{ route('admin.onboardings.contract_pdf', ['onboarding' => $onboarding, 'download' => 1]) }}" class="btn btn-outline-secondary rounded-pill px-3">
                        <i class="bi bi-filetype-pdf me-1"></i>Download PDF
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
