@extends('layouts.admin')

@section('title', 'Onboarding Contract')

@section('content')
<style>
    .contract-shell {
        background: linear-gradient(180deg, #ffffff 0%, #fbfdfc 100%);
        border: 1px solid rgba(2, 8, 20, .08);
        border-radius: 1.25rem;
        box-shadow: 0 10px 26px rgba(2, 8, 20, .06);
        padding: 1.25rem;
    }

    .section-muted {
        color: rgba(2, 8, 20, .58);
    }

    .contract-hero {
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

    .agreement-card {
        border: 1px solid rgba(2, 8, 20, .08);
        border-radius: 1rem;
        background: #fff;
        box-shadow: 0 8px 20px rgba(2, 8, 20, .05);
        overflow: hidden;
    }

    .agreement-head {
        text-align: center;
        border-bottom: 1px solid rgba(2, 8, 20, .08);
        padding: 1.1rem 1rem .95rem;
        background: #fff;
    }

    .agreement-title {
        letter-spacing: .08em;
        margin-bottom: .3rem;
        color: #14532d;
        font-weight: 800;
    }

    .contract-content {
        font-family: 'Manrope', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        line-height: 1.65;
        color: #0f172a;
    }

    .party-card,
    .clause-card,
    .signature-card,
    .status-card {
        border: 1px solid rgba(2, 8, 20, .1);
        border-radius: .9rem;
        background: #fff;
        padding: .95rem;
    }

    .section-tag {
        font-size: .72rem;
        text-transform: uppercase;
        letter-spacing: .06em;
        color: rgba(2, 8, 20, .58);
        font-weight: 700;
        margin-bottom: .45rem;
    }

    .party-heading {
        font-size: .95rem;
        color: #14532d;
        font-weight: 700;
        margin-bottom: .6rem;
    }

    .signature-line {
        border-bottom: 1px solid #0f172a;
        height: 42px;
        margin-bottom: .55rem;
    }

    .status-pill {
        font-size: .72rem;
        border-radius: 999px;
        padding: .35rem .62rem;
        text-transform: uppercase;
        letter-spacing: .04em;
    }

    .print-only {
        display: none;
    }

    @media (max-width: 767.98px) {
        .contract-shell {
            padding: .95rem;
        }
    }

    @media print {
        .contract-shell {
            border: none !important;
            box-shadow: none !important;
            padding: 0 !important;
            background: #fff !important;
        }
        .no-print {
            display: none !important;
        }
        .agreement-card,
        .party-card,
        .clause-card,
        .signature-card,
        .status-card {
            border-color: #d1d5db !important;
            box-shadow: none !important;
        }
        .print-only {
            display: block;
        }
    }
</style>

<div class="contract-shell container-fluid py-2">
    <div class="contract-hero mb-4 no-print">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
            <div>
                <div class="text-uppercase small section-muted fw-semibold">Onboarding Contract</div>
                <h1 class="h3 mb-1 text-success">Tenancy Agreement</h1>
                <p class="section-muted mb-0">Printable rental contract generated from onboarding and booking records.</p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <span class="id-chip"><i class="bi bi-hash"></i>{{ $onboarding->id }}</span>
                <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary rounded-pill px-3">
                    <i class="bi bi-house-door me-1"></i>Dashboard
                </a>
                <a href="{{ route('admin.onboardings.show', $onboarding) }}" class="btn btn-outline-secondary rounded-pill px-3">
                    <i class="bi bi-arrow-left me-1"></i>Back to Details
                </a>
                <button onclick="window.print()" class="btn btn-success rounded-pill px-3">
                    <i class="bi bi-printer me-1"></i>Print Contract
                </button>
            </div>
        </div>
    </div>

    <div class="agreement-card">
        <div class="agreement-head">
            <div class="agreement-title h4">TENANCY AGREEMENT</div>
            <div class="text-muted">Official Rental Contract</div>
        </div>

        <div class="p-3 p-lg-4 contract-content">
            <div class="print-only mb-3 small text-muted">Generated on {{ now()->format('M d, Y h:i A') }}</div>

            <p class="mb-4">
                This Tenancy Agreement is made on <strong>{{ now()->format('F d, Y') }}</strong> between the Landlord and the Tenant identified below.
            </p>

            <div class="row g-3 mb-4">
                <div class="col-12 col-md-6">
                    <div class="party-card">
                        <div class="section-tag">Party</div>
                        <div class="party-heading">Landlord</div>
                        <div><strong>Name:</strong> {{ $onboarding->booking->room->property->landlord->full_name }}</div>
                        <div><strong>Email:</strong> {{ $onboarding->booking->room->property->landlord->email }}</div>
                        <div><strong>Phone:</strong> {{ $onboarding->booking->room->property->landlord->phone ?? 'Not provided' }}</div>
                    </div>
                </div>
                <div class="col-12 col-md-6">
                    <div class="party-card">
                        <div class="section-tag">Party</div>
                        <div class="party-heading">Tenant</div>
                        <div><strong>Name:</strong> {{ $onboarding->booking->student->full_name }}</div>
                        <div><strong>Student ID:</strong> {{ $onboarding->booking->student->student_id }}</div>
                        <div><strong>Email:</strong> {{ $onboarding->booking->student->email }}</div>
                        <div><strong>Phone:</strong> {{ $onboarding->booking->student->phone ?? 'Not provided' }}</div>
                    </div>
                </div>
            </div>

            <div class="clause-card mb-4">
                <div class="section-tag">Property Details</div>
                <div><strong>Property Name:</strong> {{ $onboarding->booking->room->property->name }}</div>
                <div><strong>Address:</strong> {{ $onboarding->booking->room->property->address }}</div>
                <div><strong>Room Number:</strong> {{ $onboarding->booking->room->room_number }}</div>
                <div><strong>Monthly Rent:</strong> PHP {{ number_format($onboarding->booking->room->price, 2) }}</div>
                <div><strong>Lease Period:</strong> {{ $onboarding->booking->check_in->format('M d, Y') }} to {{ $onboarding->booking->check_out->format('M d, Y') }}</div>
            </div>

            <div class="clause-card mb-4">
                <div class="section-tag">Terms and Conditions</div>
                <ol class="mb-0 ps-3">
                    <li>The Tenant agrees to pay rent on time as specified in this agreement.</li>
                    <li>The Tenant agrees to maintain the property in good condition and report any damages promptly.</li>
                    <li>The Landlord agrees to maintain habitable conditions and address maintenance issues in a timely manner.</li>
                    <li>The Tenant shall not sublet the room without written permission from the Landlord.</li>
                    <li>This agreement may be terminated by either party with appropriate notice as per local regulations.</li>
                </ol>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-12 col-md-6">
                    <div class="signature-card text-center">
                        <div class="section-tag">Signature</div>
                        <div class="fw-semibold mb-2">Landlord Signature</div>
                        <div class="signature-line"></div>
                        <div class="small text-muted">{{ $onboarding->booking->room->property->landlord->full_name }}</div>
                        <div class="small text-muted">Date: {{ now()->format('M d, Y') }}</div>
                    </div>
                </div>
                <div class="col-12 col-md-6">
                    <div class="signature-card text-center">
                        <div class="section-tag">Signature</div>
                        <div class="fw-semibold mb-2">Tenant Signature</div>
                        <div class="signature-line"></div>
                        <div class="small text-muted">
                            @if($onboarding->contract_signed)
                                {{ $onboarding->booking->student->full_name }}
                            @else
                                <span class="text-danger">Pending Signature</span>
                            @endif
                        </div>
                        <div class="small text-muted">
                            Date:
                            @if($onboarding->contract_signed_at)
                                {{ $onboarding->contract_signed_at->format('M d, Y') }}
                            @else
                                Pending
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="status-card no-print">
                <div class="section-tag mb-2">Contract Status</div>
                <div class="row g-2">
                    <div class="col-12 col-md-6">
                        <div><strong>Contract Signed:</strong>
                            @if($onboarding->contract_signed)
                                <span class="badge status-pill text-bg-success">Yes</span>
                            @else
                                <span class="badge status-pill text-bg-warning">No</span>
                            @endif
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div><strong>Deposit Paid:</strong>
                            @if($onboarding->deposit_paid)
                                <span class="badge status-pill text-bg-success">Yes</span>
                            @else
                                <span class="badge status-pill text-bg-warning">No</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection