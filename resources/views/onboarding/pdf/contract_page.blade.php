<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Onboarding Contract #{{ $onboarding->id }}</title>
    <style>
        @page {
            size: legal portrait;
            margin: 34mm 15mm 20mm;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: DejaVu Sans, Arial, sans-serif;
            color: #0f1720;
            font-size: 11.15px;
            line-height: 1.5;
            background: #ffffff;
        }

        .page {
            width: 100%;
        }

        .pdf-header {
            position: fixed;
            top: -24mm;
            left: 0;
            right: 0;
            height: 23mm;
            font-family: Helvetica, Arial, sans-serif;
        }

        .pdf-footer {
            position: fixed;
            bottom: -13mm;
            left: 0;
            right: 0;
            height: 12mm;
            font-size: 10px;
            color: #334155;
        }

        .footer-rule {
            border-top: 1px solid #d0d7e2;
            margin-bottom: 3px;
        }

        .footer-table {
            width: 100%;
            border-collapse: collapse;
        }

        .footer-table td {
            vertical-align: top;
        }

        .footer-left {
            text-align: left;
        }

        .footer-right {
            text-align: right;
        }

        .brand-table,
        .signature-table {
            width: 100%;
            border-collapse: collapse;
        }

        .brand-table td {
            vertical-align: middle;
        }

        .brand-left {
            width: 118px;
            white-space: nowrap;
        }

        .brand-logo {
            display: inline-block;
            width: 52px;
            height: 52px;
            object-fit: contain;
            margin-right: 4px;
            vertical-align: middle;
            border-radius: 999px;
            border: 1px solid #d1d5db;
            background: #f8fafc;
        }

        .brand-left .brand-logo:last-child {
            margin-right: 0;
        }

        .brand-logo-fallback {
            display: inline-block;
            width: 52px;
            height: 52px;
            margin-right: 4px;
            border: 1px solid #d1d5db;
            border-radius: 50%;
            background: #f8fafc;
        }

        .brand-left .brand-logo-fallback:last-child {
            margin-right: 0;
        }

        .brand-right {
            color: #0f172a;
            padding-left: 5px;
            font-family: Helvetica, Arial, sans-serif;
        }

        .brand-title {
            font-family: Helvetica, Arial, sans-serif;
            font-size: 29px;
            font-weight: 800;
            line-height: 1;
            letter-spacing: 0.01em;
            margin: 0;
        }

        .brand-subtitle {
            font-family: Helvetica, Arial, sans-serif;
            font-size: 21px;
            margin-top: 0;
            line-height: 1;
            color: #14532d;
            font-weight: 800;
            letter-spacing: 0.01em;
            margin-bottom: 0;
        }

        .divider {
            border-top: 2px solid #0f1720;
            margin: 6px 0 3px;
        }

        .doc-title {
            text-align: center;
            font-family: DejaVu Serif, Georgia, serif;
            font-size: 34px;
            font-weight: 700;
            letter-spacing: 0.15px;
            margin: 0 0 12px;
        }

        p {
            margin: 0 0 8px;
        }

        .line-entry {
            margin-bottom: 4px;
        }

        .fill-line {
            display: inline-block;
            min-width: 205px;
            border-bottom: 1px solid #a8afb8;
            padding: 0 4px 2px;
            text-align: center;
        }

        .line-value {
            display: inline-block;
            width: 84%;
            border-bottom: 1px solid #b8c0c9;
            padding: 0 4px 2px;
        }

        .section {
            margin-top: 8px;
        }

        .section-title {
            font-size: 12px;
            font-weight: 700;
            margin: 0 0 6px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .sub-title {
            font-size: 11.8px;
            font-weight: 700;
            margin: 8px 0 4px;
        }

        .record-list,
        .plain-list {
            margin: 0 0 4px;
            padding-left: 18px;
        }

        .record-list li,
        .plain-list li {
            margin-bottom: 3px;
        }

        .detail-line {
            margin-bottom: 3px;
        }

        .section-signature {
            margin-top: 14px;
            page-break-inside: avoid;
        }

        .signature-table td {
            width: 50%;
            vertical-align: top;
            padding: 0 18px;
            text-align: center;
        }

        .sig-date {
            font-size: 13px;
            margin-bottom: 6px;
        }

        .sig-box {
            height: 78px;
            margin: 4px 0 2px;
            display: block;
        }

        .sig-image {
            max-width: 190px;
            max-height: 74px;
            object-fit: contain;
        }

        .sig-placeholder {
            display: inline-block;
            margin-top: 30px;
            font-size: 11px;
            color: #667085;
        }

        .sig-line {
            border-top: 1px solid #c2c7cf;
            margin: 0 auto 2px;
            width: 88%;
        }

        .sig-name {
            font-size: 13px;
            line-height: 1.2;
        }

        .sig-role {
            font-size: 13px;
            font-weight: 700;
            line-height: 1.1;
            text-transform: uppercase;
            letter-spacing: 0;
        }

        .mt-2 {
            margin-top: 6px;
        }

        .mt-3 {
            margin-top: 10px;
        }
    </style>
</head>
<body>
@php
    $booking = $onboarding->booking;
    $room = $booking->room;
    $property = $room->property;
    $landlord = $property->landlord;
    $student = $booking->student;

    $tenantName = trim((string) ($student->full_name ?? $student->name ?? 'Tenant'));
    $landlordName = trim((string) ($landlord->full_name ?? $landlord->name ?? 'Landlord'));

    $agreementDateValue = $agreementDate instanceof \DateTimeInterface
        ? $agreementDate
        : \Illuminate\Support\Carbon::parse($agreementDate ?: now());

    $agreementDateLabel = $agreementDateValue->format('F j, Y');
    $checkInLabel = optional($booking->check_in)->format('F j, Y') ?: 'Pending';
    $checkOutLabel = optional($booking->check_out)->format('F j, Y') ?: 'Open Ended';
    $durationDays = method_exists($booking, 'getDurationInDays') ? (int) $booking->getDurationInDays() : 0;
    $durationLabel = $durationDays . ' day' . ($durationDays === 1 ? '' : 's');
    $roomModeLabel = ucfirst((string) ($booking->occupancy_mode ?? 'Solo'));

    $monthlyRentAmount = is_numeric($booking->monthly_rent_amount)
        ? (float) $booking->monthly_rent_amount
        : (float) ($room->price ?? 0);
    $selectedAdvanceAmount = !empty($booking->include_advance_payment) ? $monthlyRentAmount : 0.0;
    $expectedOnboardingAmount = $monthlyRentAmount + $selectedAdvanceAmount;
    $submittedOnboardingAmount = is_numeric($onboarding->deposit_amount) ? (float) $onboarding->deposit_amount : 0.0;
    $effectiveOnboardingAmount = max($expectedOnboardingAmount, $submittedOnboardingAmount);

    $bookingStatusLabel = ucfirst((string) ($booking->status ?? 'pending'));
    $requestedLabel = optional($booking->created_at)->diffForHumans() ?: 'Recently';
    $advancePaymentLabel = !empty($booking->include_advance_payment) ? 'Yes' : 'No';
    $paymentStatusLabel = ucfirst((string) $booking->derivedPaymentStatus());
    $contractStatusLabel = $onboarding->contract_signed
        ? (!empty($onboarding->landlord_contract_signed) ? 'Fully Signed' : 'Tenant Signed')
        : 'Pending';

    $defaultRuleCategories = (array) config('property_house_rules.categories', []);
    $propertyHouseRules = (array) ($property->house_rules ?? []);

    $resolveRules = function (string $key) use ($defaultRuleCategories, $propertyHouseRules): array {
        $fallback = (array) (($defaultRuleCategories[$key]['rules'] ?? []));
        $raw = (array) ($propertyHouseRules[$key] ?? $fallback);

        return array_values(array_filter(array_map(static function ($line) {
            return trim((string) $line);
        }, $raw)));
    };

    $occupancyRules = $resolveRules('occupancy');
    $maintenanceRules = $resolveRules('maintenance_safety');
    $prohibitedRules = $resolveRules('prohibited_activities');

    $toDataUri = static function (?string $filePath): ?string {
        if (!$filePath || !is_file($filePath)) {
            return null;
        }

        $extension = strtolower((string) pathinfo($filePath, PATHINFO_EXTENSION));
        $mime = match ($extension) {
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            default => 'application/octet-stream',
        };

        $binary = @file_get_contents($filePath);
        if ($binary === false || $binary === '') {
            return null;
        }

        return 'data:' . $mime . ';base64,' . base64_encode($binary);
    };

    $tenantSignaturePath = null;
    if (!empty($onboarding->contract_signature_path)) {
        $signatureRelativePath = ltrim((string) $onboarding->contract_signature_path, '/');
        $candidateSignaturePaths = [
            public_path('storage/' . $signatureRelativePath),
            storage_path('app/public/' . $signatureRelativePath),
        ];

        foreach ($candidateSignaturePaths as $candidateSignaturePath) {
            if (is_file($candidateSignaturePath)) {
                $tenantSignaturePath = $candidateSignaturePath;
                break;
            }
        }
    }

    $tenantSignatureSrc = $toDataUri($tenantSignaturePath);

    $landlordSignaturePath = null;
    if (!empty($onboarding->landlord_contract_signature_path)) {
        $signatureRelativePath = ltrim((string) $onboarding->landlord_contract_signature_path, '/');
        $candidateSignaturePaths = [
            public_path('storage/' . $signatureRelativePath),
            storage_path('app/public/' . $signatureRelativePath),
        ];

        foreach ($candidateSignaturePaths as $candidateSignaturePath) {
            if (is_file($candidateSignaturePath)) {
                $landlordSignaturePath = $candidateSignaturePath;
                break;
            }
        }
    }

    $landlordSignatureSrc = $toDataUri($landlordSignaturePath);
    $landlordHasSigned = !empty($onboarding->landlord_contract_signed);

    $tenantSignedDateLabel = optional($onboarding->contract_signed_at)->format('F j, Y') ?: $agreementDateLabel;
    $landlordSignedDateLabel = $landlordHasSigned
        ? (optional($onboarding->landlord_contract_signed_at)->format('F j, Y')
            ?: optional($onboarding->updated_at)->format('F j, Y')
            ?: '')
        : '';

    $logoCandidates = [
        public_path('images/MinSU_logo.png'),
        public_path('images/minsu3.png'),
    ];
    $systemLogoCandidates = [
        public_path('images/OSSE-main.png'),
        public_path('images/OSSE Logo.png'),
        public_path('images/minsu1.png'),
    ];

    $primaryLogoPath = null;
    foreach ($logoCandidates as $logoCandidate) {
        if (is_file($logoCandidate)) {
            $primaryLogoPath = $logoCandidate;
            break;
        }
    }

    $secondaryLogoPath = null;
    foreach ($systemLogoCandidates as $logoCandidate) {
        if (is_file($logoCandidate)) {
            $secondaryLogoPath = $logoCandidate;
            break;
        }
    }

    $primaryLogoSrc = $toDataUri($primaryLogoPath);
    $secondaryLogoSrc = $toDataUri($secondaryLogoPath);
    $digitalTenantId = trim((string) ($onboarding->digital_id ?? ''));
@endphp

<div class="pdf-header">
    <table class="brand-table">
        <tr>
            <td class="brand-left">
                @if($primaryLogoSrc)
                    <img src="{{ $primaryLogoSrc }}" alt="" class="brand-logo">
                @else
                    <span class="brand-logo-fallback"></span>
                @endif
                @if($secondaryLogoSrc)
                    <img src="{{ $secondaryLogoSrc }}" alt="" class="brand-logo">
                @else
                    <span class="brand-logo-fallback"></span>
                @endif
            </td>
            <td class="brand-right">
                <p class="brand-title">Mindoro State University</p>
                <p class="brand-subtitle">Online Boarding House System</p>
            </td>
        </tr>
    </table>

    <div class="divider"></div>
</div>

<div class="pdf-footer">
    <div class="footer-rule"></div>
    <table class="footer-table">
        <tr>
            <td class="footer-left">
                <strong>Digital Tenant ID:</strong> {{ $digitalTenantId !== '' ? $digitalTenantId : 'Not assigned' }}
            </td>
            <td class="footer-right">
                <span class="page-number-placeholder"></span>
            </td>
        </tr>
    </table>
</div>

<div class="page">

    <p class="doc-title">Residential Lease Agreement</p>

    <p><strong>This Agreement</strong> is made on <span class="fill-line">{{ $agreementDateLabel }}</span> <strong>[Date]</strong>, between:</p>
    <p class="line-entry"><strong>Landlord:</strong> <span class="line-value">{{ $landlordName }}</span></p>
    <p class="line-entry"><strong>Tenant:</strong> <span class="line-value">{{ $tenantName }}</span></p>

    <p class="mt-3">For the rental of the premises described below, under the following terms and conditions:</p>

    <div class="section">
        <p class="section-title">1. Premises</p>
        <p>The Landlord hereby leases to the Tenant, and the Tenant hereby rents from the Landlord, the residential property located at:</p>
        <p class="detail-line">Property Name: {{ $property->name }}</p>
        <p class="detail-line">Assigned Unit: {{ $room->room_number }}</p>
        <p>Address: {{ $property->address ?: 'Not specified' }}</p>
        <p>Hereinafter referred to as the "Premises."</p>
    </div>

    <div class="section">
        <p class="section-title">2. Term of Lease</p>
        <p>The tenancy shall commence on {{ $checkInLabel }} ("Check-in Date") and shall continue subject to the approved booking and tenancy terms of the Landlord, unless earlier terminated in accordance with this Agreement or applicable law.</p>
        <p class="mt-2">The booking record reflects the following:</p>
        <ul class="record-list">
            <li>Check-in Date: {{ $checkInLabel }}</li>
            <li>Check-out Date: {{ $checkOutLabel }}</li>
            <li>Duration: {{ $durationLabel }}</li>
            <li>Room Mode: {{ $roomModeLabel }}</li>
        </ul>
        <p class="mt-2">The Tenant agrees to occupy the Premises only for lawful residential purposes and in accordance with the approved booking and community rules.</p>
    </div>

    <div class="section">
        <p class="section-title">3. Rent, Advance Payment, and Payment Terms</p>
        <p>The Tenant agrees to pay the following amounts:</p>
        <ul class="record-list">
            <li>Monthly Rent: P{{ number_format($monthlyRentAmount, 2) }}</li>
            <li>Advance Payment: P{{ number_format($selectedAdvanceAmount, 2) }}</li>
            <li>Total Onboarding / Initial Payment Due: P{{ number_format($effectiveOnboardingAmount, 2) }}</li>
        </ul>

        <p class="sub-title">3.1 Due Date</p>
        <p>Rent shall be due on the 1st day of each month, unless otherwise agreed in writing by the parties.</p>

        <p class="sub-title">3.2 Method of Payment</p>
        <p>All rental payments shall be made exclusively through the designated Platform approved by the Landlord.</p>

        <p class="sub-title">3.3 Onboarding Payment</p>
        <p>The move-in payment shall consist of the monthly rent plus any required or selected advance payment. Full payment is required prior to move-in and completion of onboarding.</p>
    </div>

    <div class="section">
        <p class="section-title">4. Occupancy and Use</p>
        <ol class="plain-list">
            <li>The Tenant shall personally occupy the Premises and shall not assign, sublease, or transfer possession of the room to any other person without the prior written consent of the Landlord.</li>
            <li>Overnight guests are strictly prohibited without prior written approval from the Landlord.</li>
            <li>The Tenant shall comply with the approved room occupancy type, which for this Agreement is: {{ $roomModeLabel }} Occupancy.</li>
        </ol>
    </div>

    <div class="section">
        <p class="section-title">5. House Rules and Community Policies</p>
        <p>The following house rules form an integral part of this Agreement and are binding upon the Tenant:</p>

        <p class="sub-title">5.1 Occupancy Rules</p>
        <ol class="plain-list">
            @forelse($occupancyRules as $rule)
                <li>{{ $rule }}</li>
            @empty
                <li>Overnight guests are prohibited without prior written consent of the Landlord.</li>
                <li>The Tenant shall observe the curfew from 10:00 PM to 5:00 AM at all times.</li>
                <li>The Tenant shall not sublet or transfer the room to any other person.</li>
            @endforelse
        </ol>

        <p class="sub-title">5.2 Maintenance and Safety</p>
        <ol class="plain-list">
            @forelse($maintenanceRules as $rule)
                <li>{{ $rule }}</li>
            @empty
                <li>The Tenant shall keep the room and common areas clean and orderly at all times.</li>
                <li>Any damage, defect, or maintenance issue must be reported to the Landlord within 24 hours of discovery.</li>
                <li>The Tenant shall not alter, paint, or modify any part of the room without prior written consent of the Landlord.</li>
            @endforelse
        </ol>

        <p class="sub-title">5.3 Prohibited Acts</p>
        <ol class="plain-list">
            @forelse($prohibitedRules as $rule)
                <li>{{ $rule }}</li>
            @empty
                <li>Noise disturbance after 10:00 PM, including parties, loud music, or similar disruptive acts, is strictly prohibited.</li>
                <li>Cooking inside the room is not allowed unless a designated cooking area has been expressly provided by the Landlord.</li>
            @endforelse
        </ol>

        <p class="mt-2">Failure to comply with these rules may constitute grounds for termination of tenancy, removal from the Premises, or other remedies available to the Landlord under applicable law.</p>
    </div>

    <div class="section">
        <p class="section-title">6. Execution and Move-in Conditions</p>

        <p class="sub-title">6.1 Binding Effect</p>
        <p>This Agreement shall become legally binding once all required identity documents have been verified and the electronic signatures of both parties have been duly submitted.</p>

        <p class="sub-title">6.2 Payment Verification</p>
        <p>Advance payment and payment status shall be reviewed and verified by the Landlord prior to handover of the Premises.</p>

        <p class="sub-title">6.3 Possession</p>
        <p>Move-in shall be allowed only upon confirmation of the approved booking status and the confirmed check-in date.</p>
    </div>

    <div class="section">
        <p class="section-title">7. Booking and Status Record</p>
        <p>For reference, the onboarding and booking details associated with this Agreement are as follows:</p>
        <ul class="record-list">
            <li>Generated for: Onboarding #{{ $onboarding->id }}</li>
            <li>Agreement Date: {{ $agreementDateLabel }}</li>
            <li>Approved Status: {{ $bookingStatusLabel }}</li>
            <li>Requested: {{ $requestedLabel }}</li>
            <li>Advance Payment: {{ $advancePaymentLabel }}</li>
            <li>Payment Status: {{ $paymentStatusLabel }}</li>
            <li>Contract Status: {{ $contractStatusLabel }}</li>
        </ul>
    </div>

    <div class="section section-signature">
        <p class="section-title">8. Signature and Execution</p>
        <p>IN WITNESS WHEREOF, the parties have executed this Residential Lease Agreement on the date first above written.</p>

        <table class="signature-table mt-3">
            <tr>
                <td>
                    <div class="sig-date">{{ $tenantSignedDateLabel }}</div>
                    <div class="sig-box">
                        @if($tenantSignatureSrc)
                            <img src="{{ $tenantSignatureSrc }}" alt="" class="sig-image">
                        @else
                            <span class="sig-placeholder">Signature</span>
                        @endif
                    </div>
                    <div class="sig-line"></div>
                    <div class="sig-name">{{ $tenantName }}</div>
                    <div class="sig-role">Tenant</div>
                </td>
                <td>
                    <div class="sig-date">{!! $landlordSignedDateLabel !== '' ? e($landlordSignedDateLabel) : '&nbsp;' !!}</div>
                    <div class="sig-box">
                        @if($landlordSignatureSrc)
                            <img src="{{ $landlordSignatureSrc }}" alt="" class="sig-image">
                        @else
                            <span class="sig-placeholder">Signature</span>
                        @endif
                    </div>
                    <div class="sig-line"></div>
                    <div class="sig-name">{{ $landlordName }}</div>
                    <div class="sig-role">Landlord</div>
                </td>
            </tr>
        </table>
    </div>
</div>
</body>
</html>
