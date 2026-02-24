@extends('layouts.admin')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-1">Onboarding Details</h5>
                            <small class="text-muted">ID: {{ $onboarding->id }}</small>
                        </div>
                        <div>
                            <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-primary btn-sm me-2">
                                <i class="fas fa-home me-1"></i>Dashboard
                            </a>
                            <a href="{{ route('admin.onboardings.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Back to List
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Progress Bar -->
                    <div class="mb-4">
                        <h6>Onboarding Progress</h6>
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
                        <div class="progress mb-2">
                            <div class="progress-bar" role="progressbar" style="width: {{ $progress }}%" aria-valuenow="{{ $progress }}" aria-valuemin="0" aria-valuemax="100">
                                {{ $progress }}% Complete
                            </div>
                        </div>
                        <div class="d-flex justify-content-between small text-muted">
                            <span>Documents</span>
                            <span>Approval</span>
                            <span>Contract</span>
                            <span>Deposit</span>
                            <span>Complete</span>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Student Information -->
                        <div class="col-md-6">
                            <div class="card border">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0"><i class="fas fa-user me-2"></i>Student Information</h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-2">
                                        <strong>Name:</strong> {{ $onboarding->booking->student->full_name }}
                                    </div>
                                    <div class="mb-2">
                                        <strong>Student ID:</strong> {{ $onboarding->booking->student->student_id }}
                                    </div>
                                    <div class="mb-2">
                                        <strong>Email:</strong> {{ $onboarding->booking->student->email }}
                                    </div>
                                    <div class="mb-0">
                                        <strong>Phone:</strong> {{ $onboarding->booking->student->phone ?? 'Not provided' }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Property Information -->
                        <div class="col-md-6">
                            <div class="card border">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0"><i class="fas fa-building me-2"></i>Property & Room</h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-2">
                                        <strong>Property:</strong> {{ $onboarding->booking->room->property->name }}
                                    </div>
                                    <div class="mb-2">
                                        <strong>Address:</strong> {{ $onboarding->booking->room->property->address }}
                                    </div>
                                    <div class="mb-2">
                                        <strong>Room:</strong> {{ $onboarding->booking->room->room_number }}
                                    </div>
                                    <div class="mb-2">
                                        <strong>Monthly Rent:</strong> ₱{{ number_format($onboarding->booking->room->price, 2) }}
                                    </div>
                                    <div class="mb-0">
                                        <strong>Lease Period:</strong>
                                        {{ $onboarding->booking->check_in->format('M d, Y') }} - {{ $onboarding->booking->check_out->format('M d, Y') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <!-- Landlord Information -->
                        <div class="col-md-6">
                            <div class="card border">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0"><i class="fas fa-user-tie me-2"></i>Landlord Information</h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-2">
                                        <strong>Name:</strong> {{ $onboarding->booking->room->property->landlord->full_name }}
                                    </div>
                                    <div class="mb-2">
                                        <strong>Email:</strong> {{ $onboarding->booking->room->property->landlord->email }}
                                    </div>
                                    <div class="mb-0">
                                        <strong>Phone:</strong> {{ $onboarding->booking->room->property->landlord->phone ?? 'Not provided' }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Onboarding Status -->
                        <div class="col-md-6">
                            <div class="card border">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0"><i class="fas fa-tasks me-2"></i>Onboarding Status</h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-2">
                                        <strong>Status:</strong>
                                        @switch($onboarding->status)
                                            @case('pending')
                                                <span class="badge bg-warning">Pending Documents</span>
                                                @break
                                            @case('documents_uploaded')
                                                <span class="badge bg-info">Documents Uploaded</span>
                                                @break
                                            @case('documents_approved')
                                                <span class="badge bg-primary">Documents Approved</span>
                                                @break
                                            @case('contract_signed')
                                                <span class="badge bg-secondary">Contract Signed</span>
                                                @break
                                            @case('deposit_paid')
                                                <span class="badge bg-success">Deposit Paid</span>
                                                @break
                                            @case('completed')
                                                <span class="badge bg-success">Completed</span>
                                                @break
                                            @default
                                                <span class="badge bg-light">{{ ucfirst($onboarding->status) }}</span>
                                        @endswitch
                                    </div>
                                    <div class="mb-2">
                                        <strong>Created:</strong> {{ $onboarding->created_at->format('M d, Y H:i') }}
                                    </div>
                                    <div class="mb-2">
                                        <strong>Last Updated:</strong> {{ $onboarding->updated_at->format('M d, Y H:i') }}
                                    </div>
                                    @if($onboarding->digital_id)
                                    <div class="mb-0">
                                        <strong>Digital ID:</strong> <code>{{ $onboarding->digital_id }}</code>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Documents Section -->
                    @if($onboarding->uploaded_documents && count($onboarding->uploaded_documents) > 0)
                    <div class="mt-4">
                        <h6><i class="fas fa-file-alt me-2"></i>Uploaded Documents</h6>
                        <div class="row">
                            @foreach($onboarding->uploaded_documents as $document)
                            <div class="col-md-4 mb-3">
                                <div class="card border">
                                    <div class="card-body text-center">
                                        <i class="fas fa-file-pdf fa-2x text-danger mb-2"></i>
                                        <div class="small text-muted mb-2">{{ basename($document) }}</div>
                                        <a href="{{ route('documents.view', [$onboarding->id, basename($document)]) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye me-1"></i>View
                                        </a>
                                        <a href="{{ route('documents.view', [$onboarding->id, basename($document)]) }}?download=1" class="btn btn-sm btn-outline-secondary ms-1">
                                            <i class="fas fa-download me-1"></i>Download
                                        </a>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @else
                    <div class="mt-4">
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            No documents have been uploaded yet.
                        </div>
                    </div>
                    @endif

                    <!-- Contract Section -->
                    @if($onboarding->contract_signed)
                    <div class="mt-4">
                        <h6><i class="fas fa-file-contract me-2"></i>Contract Information</h6>
                        <div class="card border">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <strong>Contract Signed:</strong> {{ $onboarding->contract_signed_at ? $onboarding->contract_signed_at->format('M d, Y H:i') : 'N/A' }}
                                    </div>
                                    <div class="col-md-6">
                                        <strong>Deposit Paid:</strong>
                                        @if($onboarding->deposit_paid)
                                            <span class="text-success">Yes</span> ({{ $onboarding->deposit_paid_at ? $onboarding->deposit_paid_at->format('M d, Y H:i') : 'N/A' }})
                                        @else
                                            <span class="text-danger">No</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <a href="{{ route('admin.onboardings.contract', $onboarding) }}" target="_blank" class="btn btn-primary me-2">
                                        <i class="fas fa-eye me-2"></i>View Contract
                                    </a>
                                    <a href="{{ route('student.onboarding.show', $onboarding) }}" target="_blank" class="btn btn-outline-primary">
                                        <i class="fas fa-external-link-alt me-2"></i>Student View
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    @else
                    <div class="mt-4">
                        <h6><i class="fas fa-file-contract me-2"></i>Contract Information</h6>
                        <div class="card border">
                            <div class="card-body">
                                <div class="alert alert-warning mb-0">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    Contract has not been signed yet. The tenant needs to complete the document upload and approval process first.
                                </div>
                                <div class="mt-3">
                                    <a href="{{ route('admin.onboardings.contract', $onboarding) }}" target="_blank" class="btn btn-primary me-2">
                                        <i class="fas fa-eye me-2"></i>View Contract
                                    </a>
                                    <a href="{{ route('student.onboarding.show', $onboarding) }}" target="_blank" class="btn btn-outline-primary">
                                        <i class="fas fa-external-link-alt me-2"></i>Student View
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection