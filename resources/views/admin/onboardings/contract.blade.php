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

    .contract-list {
        padding-left: 1.1rem;
        margin-bottom: 0;
    }

    .rules-section {
        background: linear-gradient(135deg, #f0fdf4 0%, #f8fafc 100%);
        border: 1.5px solid #bbf7d0;
        border-radius: .85rem;
        padding: 1rem;
    }

    .rules-group-title {
        font-size: .76rem;
        font-weight: 700;
        color: #14532d;
        text-transform: uppercase;
        letter-spacing: .05em;
        margin-bottom: .5rem;
    }

    .rules-list {
        margin: 0;
        padding-left: 1rem;
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

@php
    $booking = $onboarding->booking;
    $student = $booking->student;
    $room = $booking->room;
    $property = $room->property;
    $landlord = $property->landlord;

    $monthlyRentAmount = is_numeric($booking->monthly_rent_amount)
        ? (float) $booking->monthly_rent_amount
        : (float) ($room->price ?? 0);
    $selectedAdvanceAmount = !empty($booking->include_advance_payment) ? $monthlyRentAmount : 0.0;
    $expectedOnboardingAmount = $monthlyRentAmount + $selectedAdvanceAmount;
    $submittedOnboardingAmount = is_numeric($onboarding->deposit_amount) && (float) $onboarding->deposit_amount > 0
        ? (float) $onboarding->deposit_amount
        : 0.0;
    $effectiveDepositAmount = max($expectedOnboardingAmount, $submittedOnboardingAmount);

    $checkInLabel = optional($booking->check_in)->format('M d, Y') ?: 'Pending';
    $checkOutLabel = optional($booking->check_out)->format('M d, Y') ?: 'Open Ended';
    $bookingStatusLabel = ucfirst((string) ($booking->status ?? 'pending'));
    $requestedLabel = optional($booking->created_at)->diffForHumans() ?: 'Recently';
    $durationDays = method_exists($booking, 'getDurationInDays') ? $booking->getDurationInDays() : 0;
    $roomModeLabel = ucfirst((string) ($booking->occupancy_mode ?? 'Solo'));
    $advancePaymentLabel = !empty($booking->include_advance_payment) ? 'Yes' : 'No';
    $paymentStatusLabel = ucfirst((string) $booking->derivedPaymentStatus());

    $defaultHouseRuleCategories = (array) config('property_house_rules.categories', []);
    $propertyHouseRules = (array) ($property->house_rules ?? []);
    $houseRuleSections = collect($defaultHouseRuleCategories)
        ->map(function ($categoryConfig, $categoryKey) use ($propertyHouseRules) {
            $fallbackRules = (array) ($categoryConfig['rules'] ?? []);
            $rules = collect((array) ($propertyHouseRules[$categoryKey] ?? $fallbackRules))
                ->map(fn ($line) => trim((string) $line))
                ->filter()
                ->values();

            return [
                'label' => (string) ($categoryConfig['label'] ?? $categoryKey),
                'rules' => $rules,
            ];
        })
        ->filter(fn ($section) => $section['rules']->isNotEmpty())
        ->values();
@endphp

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
            <div class="agreement-title h4">RESIDENTIAL LEASE AGREEMENT</div>
            <div class="text-muted">Legal Contract Preview</div>
        </div>

        <div class="p-3 p-lg-4 contract-content">
            <div class="print-only mb-3 small text-muted">Generated on {{ now()->format('M d, Y h:i A') }}</div>

            <p class="mb-4">
                This Residential Lease Agreement is made on <strong>{{ now()->format('F d, Y') }}</strong> between the Landlord and the Tenant identified below.
            </p>

            <div class="row g-3 mb-4">
                <div class="col-12 col-md-6">
                    <div class="party-card">
                        <div class="section-tag">Party</div>
                        <div class="party-heading">Landlord</div>
                        <div><strong>Name:</strong> {{ $landlord->full_name ?? $landlord->name }}</div>
                        <div><strong>Email:</strong> {{ $landlord->email }}</div>
                    </div>
                </div>
                <div class="col-12 col-md-6">
                    <div class="party-card">
                        <div class="section-tag">Party</div>
                        <div class="party-heading">Tenant</div>
                        <div><strong>Name:</strong> {{ $student->full_name ?? $student->name }}</div>
                        <div><strong>Student ID:</strong> {{ $student->student_id }}</div>
                        <div><strong>Email:</strong> {{ $student->email }}</div>
                    </div>
                </div>
            </div>

            <div class="clause-card mb-4">
                <div class="section-tag">Booking Snapshot</div>
                <div class="row g-2 small">
                    <div class="col-12 col-md-6"><strong>Property:</strong> {{ $property->name }}</div>
                    <div class="col-12 col-md-6"><strong>Room:</strong> {{ $room->room_number }}</div>
                    <div class="col-12 col-md-6"><strong>Approved:</strong> {{ $bookingStatusLabel }}</div>
                    <div class="col-12 col-md-6"><strong>Check-in:</strong> {{ $checkInLabel }}</div>
                    <div class="col-12 col-md-6"><strong>Check-out:</strong> {{ $checkOutLabel }}</div>
                    <div class="col-12 col-md-6"><strong>Duration:</strong> {{ $durationDays }} days</div>
                    <div class="col-12 col-md-6"><strong>Requested:</strong> {{ $requestedLabel }}</div>
                    <div class="col-12 col-md-6"><strong>Room Mode:</strong> {{ $roomModeLabel }}</div>
                    <div class="col-12 col-md-6"><strong>Monthly Rent:</strong> ₱{{ number_format($monthlyRentAmount, 2) }}</div>
                    <div class="col-12 col-md-6"><strong>Advance Payment:</strong> {{ $advancePaymentLabel }}</div>
                    <div class="col-12 col-md-6"><strong>Advance Amount:</strong> ₱{{ number_format($selectedAdvanceAmount, 2) }}</div>
                    <div class="col-12 col-md-6"><strong>Full Payment Due:</strong> ₱{{ number_format($effectiveDepositAmount, 2) }}</div>
                    <div class="col-12 col-md-6"><strong>Payment Status:</strong> {{ $paymentStatusLabel }}</div>
                </div>
            </div>

            <div class="clause-card mb-4">
                <div class="section-tag">1. Premises</div>
                <p class="mb-0">
                    The Landlord agrees to rent to the Tenant, and the Tenant agrees to rent from the Landlord, the property located at
                    <strong>{{ $property->name }}</strong>, <strong>{{ $room->room_number }}</strong> (the "Premises"), under the terms and conditions set forth in this Agreement.
                </p>
            </div>

            <div class="clause-card mb-4">
                <div class="section-tag">2. Term and Occupancy</div>
                <p class="mb-0">
                    The Tenant agrees to occupy the Premises under the approved booking terms and to strictly adhere to all community policies established by the Landlord.
                    This Agreement serves as the binding contract for the tenancy commencing upon the completion of the Onboarding Process.
                </p>
            </div>

            <div class="clause-card mb-4">
                <div class="section-tag">3. Rent and Payment</div>
                <ul class="contract-list">
                    <li><strong>Due Date.</strong> Rent is due on the 1st day of each month.</li>
                    <li><strong>Method of Payment.</strong> All rent payments must be payable exclusively through the designated Platform.</li>
                    <li><strong>Onboarding Payment.</strong> The move-in payment equals monthly rent plus any selected/required advance payment and is required to complete onboarding.</li>
                </ul>
            </div>

            <div class="clause-card mb-4">
                <div class="section-tag">4. Community Rules and Maintenance</div>
                <div class="rules-section">
                    @php $ruleNumber = 1; @endphp
                    @forelse($houseRuleSections as $section)
                        <div class="mb-3">
                            <div class="rules-group-title">{{ $section['label'] }}</div>
                            <ol class="rules-list">
                                @foreach($section['rules'] as $rule)
                                    <li>{{ $rule }}</li>
                                    @php $ruleNumber++; @endphp
                                @endforeach
                            </ol>
                        </div>
                    @empty
                        <div class="small text-muted">No house rules configured for this property yet.</div>
                    @endforelse
                </div>
            </div>

            <div class="clause-card mb-4">
                <div class="section-tag">5. Execution and Move-In Conditions</div>
                <ul class="contract-list">
                    <li><strong>Binding Effect.</strong> This Contract becomes legally binding once all identity documents are verified and the electronic signature is submitted by both parties.</li>
                    <li><strong>Payment Verification.</strong> Advance payment and payment status will be reviewed and verified prior to room handover.</li>
                    <li><strong>Possession.</strong> Move-in is strictly subject to the confirmed check-in date and approved booking status.</li>
                </ul>
            </div>

            <div class="clause-card mb-4">
                <div class="section-tag">IN WITNESS WHEREOF</div>
                <p class="mb-0">The parties have executed this Agreement as of the date first written above.</p>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-12 col-md-6">
                    <div class="signature-card text-center">
                        <div class="section-tag">Signature</div>
                        <div class="fw-semibold mb-2">Landlord Signature</div>
                        <div class="signature-line"></div>
                        <div class="small text-muted">{{ $landlord->full_name ?? $landlord->name }}</div>
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
                                {{ $student->full_name ?? $student->name }}
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