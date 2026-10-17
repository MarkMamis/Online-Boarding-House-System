@extends('layouts.admin')

@section('title', 'Onboarding Details')

@section('content')
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

<div class="admin-page-shell">
    <x-admin.page-header
        title="Onboarding #{{ $onboarding->id }}"
        subtitle="Monitor tenant completion milestones, verification documents, and lease contract status."
        icon="bi bi-person-check"
        :breadcrumbs="[
            ['label' => 'Monitoring', 'url' => route('admin.onboardings.index')],
            ['label' => 'Onboarding Pipeline', 'url' => route('admin.onboardings.index')],
            ['label' => '#' . $onboarding->id]
        ]"
    >
        <x-slot:actions>
            <a href="{{ route('admin.onboardings.index') }}" class="btn btn-outline-secondary rounded-pill px-3">
                <i class="bi bi-arrow-left me-1"></i>Back to Pipeline
            </a>
        </x-slot:actions>
    </x-admin.page-header>

    <div class="admin-table-card p-4 mb-4">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-bar-chart-steps me-2 text-success"></i>Onboarding Progress</h6>
            <span class="badge bg-success-subtle text-success rounded-pill px-3 fw-bold">{{ $progress }}% Complete</span>
        </div>
        <div class="progress mb-2" style="height: 10px; border-radius: 999px;">
            <div class="progress-bar bg-success" role="progressbar" style="width: {{ $progress }}%" aria-valuenow="{{ $progress }}" aria-valuemin="0" aria-valuemax="100"></div>
        </div>
        <div class="d-flex justify-content-between small text-muted">
            <span>Documents</span>
            <span>Approval</span>
            <span>Contract</span>
            <span>Deposit</span>
            <span>Complete</span>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-12 col-lg-6">
            <div class="admin-table-card h-100">
                <div class="p-3 border-bottom bg-light">
                    <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-person me-2 text-success"></i>Student Information</h6>
                </div>
                <div class="p-3">
                    <div class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted small">Full Name</span>
                        <span class="fw-semibold text-dark">{{ $onboarding->booking->student->full_name }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted small">Student ID</span>
                        <span class="fw-medium text-dark">{{ $onboarding->booking->student->student_id ?: 'Not provided' }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted small">Email</span>
                        <span class="text-dark">{{ $onboarding->booking->student->email }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-2">
                        <span class="text-muted small">Phone</span>
                        <span class="text-dark">{{ $onboarding->booking->student->phone ?? 'Not provided' }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-6">
            <div class="admin-table-card h-100">
                <div class="p-3 border-bottom bg-light">
                    <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-building me-2 text-success"></i>Property and Room</h6>
                </div>
                <div class="p-3">
                    <div class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted small">Property</span>
                        <span class="fw-semibold text-dark">{{ $onboarding->booking->room->property->name }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted small">Address</span>
                        <span class="text-dark text-end ms-2">{{ $onboarding->booking->room->property->address }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted small">Room</span>
                        <span class="fw-medium text-dark">{{ $onboarding->booking->room->room_number }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted small">Monthly Rent</span>
                        <span class="fw-bold text-success">PHP {{ number_format($onboarding->booking->room->price, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-2">
                        <span class="text-muted small">Lease Period</span>
                        <span class="text-dark">{{ $onboarding->booking->check_in->format('M d, Y') }} - {{ $onboarding->booking->check_out->format('M d, Y') }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-6">
            <div class="admin-table-card h-100">
                <div class="p-3 border-bottom bg-light">
                    <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-person-badge me-2 text-success"></i>Landlord Information</h6>
                </div>
                <div class="p-3">
                    <div class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted small">Name</span>
                        <span class="fw-semibold text-dark">{{ $onboarding->booking->room->property->landlord->full_name }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted small">Email</span>
                        <span class="text-dark">{{ $onboarding->booking->room->property->landlord->email }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-2">
                        <span class="text-muted small">Phone</span>
                        <span class="text-dark">{{ $onboarding->booking->room->property->landlord->phone ?? 'Not provided' }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-6">
            <div class="admin-table-card h-100">
                <div class="p-3 border-bottom bg-light">
                    <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-clipboard2-check me-2 text-success"></i>Onboarding Status</h6>
                </div>
                <div class="p-3">
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                        <span class="text-muted small">Current Status</span>
                        <x-admin.status-badge :status="$onboarding->status" />
                    </div>
                    <div class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted small">Created</span>
                        <span class="text-dark">{{ $onboarding->created_at->format('M d, Y h:i A') }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted small">Last Updated</span>
                        <span class="text-dark">{{ $onboarding->updated_at->format('M d, Y h:i A') }}</span>
                    </div>
                    @if($onboarding->digital_id)
                        <div class="d-flex justify-content-between align-items-center py-2">
                            <span class="text-muted small">Digital ID</span>
                            <code>{{ $onboarding->digital_id }}</code>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="mb-4">
        <h6 class="fw-bold mb-3 text-dark"><i class="bi bi-file-earmark-text me-2 text-success"></i>Uploaded Documents</h6>
        @if($onboarding->uploaded_documents && count($onboarding->uploaded_documents) > 0)
            <div class="row g-3">
                @foreach($onboarding->uploaded_documents as $document)
                    <div class="col-12 col-md-6 col-xl-4">
                        <div class="admin-table-card p-3 text-center h-100 d-flex flex-column justify-content-between">
                            <div>
                                <div class="mb-2 text-danger fs-1"><i class="bi bi-file-earmark-pdf"></i></div>
                                <div class="small fw-semibold text-dark mb-3 text-break">{{ basename($document) }}</div>
                            </div>
                            <div class="d-flex justify-content-center gap-2">
                                <a href="{{ route('documents.view', [$onboarding->id, basename($document)]) }}" target="_blank" class="btn btn-sm btn-outline-brand rounded-pill px-3">
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

    <div class="admin-table-card p-4">
        <h6 class="fw-bold mb-3 text-dark"><i class="bi bi-file-earmark-check me-2 text-success"></i>Contract &amp; Payment Information</h6>
        @if($onboarding->contract_signed)
            <div class="row g-3 mb-3">
                <div class="col-12 col-md-4">
                    <span class="text-muted small d-block mb-1">Contract Signed</span>
                    <span class="fw-semibold text-dark">{{ $onboarding->contract_signed_at ? $onboarding->contract_signed_at->format('M d, Y h:i A') : 'N/A' }}</span>
                </div>
                <div class="col-12 col-md-4">
                    <span class="text-muted small d-block mb-1">Payment Status</span>
                    <div>
                        @if($onboarding->deposit_paid)
                            <span class="badge bg-success-subtle text-success rounded-pill px-3 fw-bold">Confirmed</span>
                            <span class="text-muted small ms-1">({{ $onboarding->deposit_paid_at ? $onboarding->deposit_paid_at->format('M d, Y') : 'N/A' }})</span>
                        @elseif($onboarding->status === 'deposit_paid')
                            <span class="badge bg-warning-subtle text-warning-emphasis rounded-pill px-3 fw-bold">Under Review</span>
                        @else
                            <span class="badge bg-secondary-subtle text-secondary rounded-pill px-3 fw-bold">Pending Payment</span>
                        @endif
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <span class="text-muted small d-block mb-1">Payment Details</span>
                    <div class="small">
                        @if($onboarding->payment_method)
                            <span class="fw-medium text-dark">{{ ucfirst($onboarding->payment_method) }}</span>
                            @if($onboarding->payment_reference)
                                · <code class="text-primary">{{ $onboarding->payment_reference }}</code>
                            @endif
                        @else
                            <span class="text-muted">No payment recorded</span>
                        @endif
                    </div>
                </div>
            </div>
        @else
            <div class="alert alert-info border-0 shadow-sm mb-3">
                <i class="bi bi-info-circle me-2"></i>Contract has not been signed yet. The tenant must complete document upload and approval first.
            </div>
        @endif

        <div class="d-flex flex-wrap gap-2 pt-2 border-top">
            <a href="{{ route('admin.onboardings.contract', $onboarding) }}" target="_blank" class="btn btn-brand rounded-pill px-3">
                <i class="bi bi-eye me-1"></i>View Contract
            </a>
            <a href="{{ route('admin.onboardings.contract_pdf', ['onboarding' => $onboarding, 'download' => 1]) }}" class="btn btn-outline-secondary rounded-pill px-3">
                <i class="bi bi-filetype-pdf me-1"></i>Download PDF
            </a>
            @if(!empty($onboarding->payment_proof_path) && file_exists_any($onboarding->payment_proof_path))
                <a href="{{ file_download_url($onboarding->payment_proof_path) }}" target="_blank" rel="noopener" class="btn btn-outline-secondary rounded-pill px-3">
                    <i class="bi bi-receipt me-1"></i>View Payment Proof
                </a>
            @endif
        </div>
    </div>
</div>
@endsection
