@props([
    'onboarding',
    'agreementDate' => null,
    'collapsibleRules' => true,
])

@php
    $booking = $onboarding->booking;
    $room = $booking->room;
    $property = $room->property;

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

    $agreementDateLabel = now()->format('F j, Y');
    if ($agreementDate instanceof \DateTimeInterface) {
        $agreementDateLabel = $agreementDate->format('F j, Y');
    } elseif (is_string($agreementDate) && trim($agreementDate) !== '') {
        try {
            $agreementDateLabel = \Illuminate\Support\Carbon::parse($agreementDate)->format('F j, Y');
        } catch (\Throwable $error) {
            $agreementDateLabel = now()->format('F j, Y');
        }
    }

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
                'icon' => (string) ($categoryConfig['icon'] ?? 'dot'),
                'rules' => $rules,
            ];
        })
        ->filter(fn ($section) => $section['rules']->isNotEmpty())
        ->values();
@endphp

<div class="contract-shell legal-document mb-4">
    <div class="contract-header">
        <div class="d-flex align-items-start justify-content-between flex-wrap gap-3">
            <div>
                <div class="text-uppercase small text-muted">Residential Lease Agreement</div>
                <h5 class="fw-bold mb-1">Residential Lease Contract</h5>
                <div class="text-muted small">Reference No. ONB-{{ $onboarding->id }}</div>
            </div>
            <div class="contract-highlight">
                Agreement Date: {{ $agreementDateLabel }}
            </div>
        </div>
        <div class="contract-meta mt-3">
            <div><strong>Property:</strong> {{ $property->name }}</div>
            <div><strong>Room:</strong> {{ $room->room_number }}</div>
            <div><strong>Monthly Rent:</strong> P{{ number_format($monthlyRentAmount, 2) }}</div>
            <div><strong>Advance Amount:</strong> P{{ number_format($selectedAdvanceAmount, 2) }}</div>
            <div><strong>Full Payment Due:</strong> P{{ number_format($effectiveDepositAmount, 2) }}</div>
        </div>
    </div>

    <div class="contract-section">
        <h6>Booking Snapshot</h6>
        <div class="contract-meta snapshot-grid">
            <div><strong>Approved:</strong> {{ $bookingStatusLabel }}</div>
            <div><strong>Check-in:</strong> {{ $checkInLabel }}</div>
            <div><strong>Check-out:</strong> {{ $checkOutLabel }}</div>
            <div><strong>Duration:</strong> {{ $durationDays }} days</div>
            <div><strong>Requested:</strong> {{ $requestedLabel }}</div>
            <div><strong>Room Mode:</strong> {{ $roomModeLabel }}</div>
            <div><strong>Advance Payment:</strong> {{ $advancePaymentLabel }}</div>
            <div><strong>Payment Status:</strong> {{ $paymentStatusLabel }}</div>
        </div>
    </div>

    <div class="contract-section">
        <h6>1. Parties and Premises</h6>
        <p class="mb-0">
            This agreement is executed between the Landlord and the Tenant for the lease of
            <strong>{{ $property->name }}</strong>, <strong>{{ $room->room_number }}</strong> (the "Premises"), subject to the terms and obligations stated in this contract.
        </p>
    </div>

    <div class="contract-section">
        <h6>2. Lease Term and Occupancy</h6>
        <p class="mb-0">
            The Tenant agrees to occupy the Premises according to the approved booking period and acknowledged occupancy mode.
            Tenancy rights begin only after onboarding requirements are completed and this contract is deemed active by the platform records.
        </p>
    </div>

    <div class="contract-section">
        <h6>3. Financial Terms</h6>
        <ul class="contract-list mb-0">
            <li><strong>Monthly Rent.</strong> The monthly rental amount is due on or before the due date registered in the platform billing records.</li>
            <li><strong>Payment Channel.</strong> All lease-related payments must be made through approved payment methods configured in the platform.</li>
            <li><strong>Initial Settlement.</strong> Onboarding payment consists of monthly rent plus any declared advance amount and must be verified before move-in release.</li>
        </ul>
    </div>

    @if($collapsibleRules)
        <details class="contract-section rules-toggle">
            <summary>
                <span class="rules-toggle-label">
                    <i class="bi bi-house-check"></i>
                    4. Community Rules and Maintenance
                </span>
                <span class="rules-toggle-hint">Show rules <i class="bi bi-chevron-down"></i></span>
            </summary>

            <div class="bk-rules mt-3 mb-0">
                <div class="bk-rules-header">
                    <i class="bi bi-file-text bk-rules-icon"></i>
                    <div>
                        <h5 class="bk-rules-title">Property House Rules</h5>
                        <div class="bk-rules-subtitle">These rules form part of this legal lease agreement.</div>
                    </div>
                </div>

                @php $ruleNumber = 1; @endphp
                @forelse($houseRuleSections as $section)
                    <div class="bk-rules-section">
                        <div class="bk-rules-section-header"><i class="bi bi-{{ $section['icon'] }}" style="font-size:.85rem;"></i> {{ $section['label'] }}</div>
                        <ul class="bk-rules-list">
                            @foreach($section['rules'] as $rule)
                                <li>
                                    <span class="bk-rules-list-icon">{{ str_pad((string) $ruleNumber, 2, '0', STR_PAD_LEFT) }}</span>
                                    <span class="bk-rules-list-text">{{ $rule }}</span>
                                </li>
                                @php $ruleNumber++; @endphp
                            @endforeach
                        </ul>
                    </div>
                @empty
                    <div class="small text-muted">No house rules configured for this property yet.</div>
                @endforelse
            </div>
        </details>
    @else
        <div class="contract-section">
            <h6>4. Community Rules and Maintenance</h6>
            <div class="bk-rules mt-3 mb-0">
                <div class="bk-rules-header">
                    <i class="bi bi-file-text bk-rules-icon"></i>
                    <div>
                        <h5 class="bk-rules-title">Property House Rules</h5>
                        <div class="bk-rules-subtitle">These rules form part of this legal lease agreement.</div>
                    </div>
                </div>

                @php $ruleNumber = 1; @endphp
                @forelse($houseRuleSections as $section)
                    <div class="bk-rules-section">
                        <div class="bk-rules-section-header"><i class="bi bi-{{ $section['icon'] }}" style="font-size:.85rem;"></i> {{ $section['label'] }}</div>
                        <ul class="bk-rules-list">
                            @foreach($section['rules'] as $rule)
                                <li>
                                    <span class="bk-rules-list-icon">{{ str_pad((string) $ruleNumber, 2, '0', STR_PAD_LEFT) }}</span>
                                    <span class="bk-rules-list-text">{{ $rule }}</span>
                                </li>
                                @php $ruleNumber++; @endphp
                            @endforeach
                        </ul>
                    </div>
                @empty
                    <div class="small text-muted">No house rules configured for this property yet.</div>
                @endforelse
            </div>
        </div>
    @endif

    <div class="contract-section">
        <h6>5. Compliance and Possession</h6>
        <ul class="contract-list mb-0">
            <li><strong>Binding Effect.</strong> This contract becomes enforceable once onboarding documents are approved and e-signature records are captured.</li>
            <li><strong>Verification Requirement.</strong> Payment submissions remain subject to landlord verification before room handover authorization.</li>
            <li><strong>Possession Conditions.</strong> Move-in is strictly aligned with confirmed check-in date and approved booking status.</li>
        </ul>
    </div>

    <div class="contract-section mb-0">
        <h6>6. Execution</h6>
        <p class="mb-0">
            By completion of platform onboarding and submission of electronic signatures, the parties acknowledge and accept all terms in this agreement.
        </p>
    </div>
</div>
