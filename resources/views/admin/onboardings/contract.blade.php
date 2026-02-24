@extends('layouts.admin')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-1">Tenancy Agreement</h5>
                            <small class="text-muted">Onboarding ID: {{ $onboarding->id }}</small>
                        </div>
                        <div>
                            <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-primary btn-sm me-2">
                                <i class="fas fa-home me-1"></i>Dashboard
                            </a>
                            <a href="{{ route('admin.onboardings.show', $onboarding) }}" class="btn btn-outline-secondary me-2">
                                <i class="fas fa-arrow-left me-2"></i>Back to Details
                            </a>
                            <button onclick="window.print()" class="btn btn-outline-primary">
                                <i class="fas fa-print me-2"></i>Print Contract
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Contract Header -->
                    <div class="text-center mb-4">
                        <h3 class="mb-2">TENANCY AGREEMENT</h3>
                        <p class="text-muted mb-0">Official Rental Contract</p>
                        <hr>
                    </div>

                    <!-- Contract Content -->
                    <div class="contract-content" style="font-family: 'Times New Roman', serif; line-height: 1.6;">
                        <div class="mb-4">
                            <p class="mb-4">
                                This Tenancy Agreement is made on <strong>{{ now()->format('F d, Y') }}</strong>
                            </p>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h5 class="text-primary mb-3">LANDLORD</h5>
                                <div class="border p-3 rounded">
                                    <p class="mb-1"><strong>Name:</strong> {{ $onboarding->booking->room->property->landlord->full_name }}</p>
                                    <p class="mb-1"><strong>Email:</strong> {{ $onboarding->booking->room->property->landlord->email }}</p>
                                    <p class="mb-0"><strong>Phone:</strong> {{ $onboarding->booking->room->property->landlord->phone ?? 'Not provided' }}</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h5 class="text-success mb-3">TENANT</h5>
                                <div class="border p-3 rounded">
                                    <p class="mb-1"><strong>Name:</strong> {{ $onboarding->booking->student->full_name }}</p>
                                    <p class="mb-1"><strong>Student ID:</strong> {{ $onboarding->booking->student->student_id }}</p>
                                    <p class="mb-1"><strong>Email:</strong> {{ $onboarding->booking->student->email }}</p>
                                    <p class="mb-0"><strong>Phone:</strong> {{ $onboarding->booking->student->phone ?? 'Not provided' }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <h5 class="text-info mb-3">PROPERTY DETAILS</h5>
                            <div class="border p-3 rounded">
                                <p class="mb-1"><strong>Property Name:</strong> {{ $onboarding->booking->room->property->name }}</p>
                                <p class="mb-1"><strong>Address:</strong> {{ $onboarding->booking->room->property->address }}</p>
                                <p class="mb-1"><strong>Room Number:</strong> {{ $onboarding->booking->room->room_number }}</p>
                                <p class="mb-1"><strong>Monthly Rent:</strong> ₱{{ number_format($onboarding->booking->room->price, 2) }}</p>
                                <p class="mb-0"><strong>Lease Period:</strong> {{ $onboarding->booking->check_in->format('M d, Y') }} to {{ $onboarding->booking->check_out->format('M d, Y') }}</p>
                            </div>
                        </div>

                        <div class="mb-4">
                            <h5 class="text-warning mb-3">TERMS AND CONDITIONS</h5>
                            <div class="border p-3 rounded">
                                <ol class="mb-0">
                                    <li>The Tenant agrees to pay rent on time as specified in this agreement.</li>
                                    <li>The Tenant agrees to maintain the property in good condition and report any damages promptly.</li>
                                    <li>The Landlord agrees to maintain habitable conditions and address maintenance issues in a timely manner.</li>
                                    <li>The Tenant shall not sublet the room without written permission from the Landlord.</li>
                                    <li>This agreement may be terminated by either party with appropriate notice as per local regulations.</li>
                                </ol>
                            </div>
                        </div>

                        <div class="mb-4">
                            <h5 class="text-danger mb-3">SIGNATURES</h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="border p-3 rounded text-center">
                                        <p class="mb-2"><strong>Landlord Signature</strong></p>
                                        <div style="border-bottom: 1px solid #000; height: 40px; margin-bottom: 10px;"></div>
                                        <p class="mb-0 small text-muted">{{ $onboarding->booking->room->property->landlord->full_name }}</p>
                                        <p class="mb-0 small text-muted">Date: {{ now()->format('M d, Y') }}</p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="border p-3 rounded text-center">
                                        <p class="mb-2"><strong>Tenant Signature</strong></p>
                                        <div style="border-bottom: 1px solid #000; height: 40px; margin-bottom: 10px;"></div>
                                        <p class="mb-0 small text-muted">
                                            @if($onboarding->contract_signed)
                                                {{ $onboarding->booking->student->full_name }}
                                            @else
                                                <span class="text-danger">Pending Signature</span>
                                            @endif
                                        </p>
                                        <p class="mb-0 small text-muted">
                                            Date:
                                            @if($onboarding->contract_signed_at)
                                                {{ $onboarding->contract_signed_at->format('M d, Y') }}
                                            @else
                                                Pending
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Contract Status -->
                        <div class="alert alert-info">
                            <h6 class="alert-heading mb-2"><i class="fas fa-info-circle me-2"></i>Contract Status</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <p class="mb-1"><strong>Contract Signed:</strong>
                                        @if($onboarding->contract_signed)
                                            <span class="badge bg-success">Yes</span>
                                        @else
                                            <span class="badge bg-warning">No</span>
                                        @endif
                                    </p>
                                </div>
                                <div class="col-md-6">
                                    <p class="mb-1"><strong>Deposit Paid:</strong>
                                        @if($onboarding->deposit_paid)
                                            <span class="badge bg-success">Yes</span>
                                        @else
                                            <span class="badge bg-warning">No</span>
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    .card-header, .btn, .alert {
        display: none !important;
    }
    .contract-content {
        font-size: 12pt;
    }
    .container-fluid {
        padding: 0 !important;
    }
}
</style>
@endsection