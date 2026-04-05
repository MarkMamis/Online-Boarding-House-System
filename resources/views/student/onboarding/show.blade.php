@extends('layouts.student_dashboard')

@section('title', 'Onboarding Process')

@push('styles')
<style>
  :root {
    --brand: #166534;
    --brand-dark: #15803d;
  }

  .glass-card {
    background: #fff;
    border: 1px solid rgba(2,8,20,.08);
    border-radius: 1.25rem;
    box-shadow: 0 10px 26px rgba(2,8,20,.06);
  }

  .onb-shell {
    background: linear-gradient(180deg, #ffffff 0%, #fbfdfc 100%);
    border: 1px solid rgba(2,8,20,.08);
    border-radius: 1.25rem;
    box-shadow: 0 10px 26px rgba(2,8,20,.06);
    padding: 1.1rem;
  }

  .onb-summary {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: .7rem;
    margin-top: .8rem;
  }

  .onb-summary-item {
    border: 1px solid rgba(20,83,45,.16);
    background: linear-gradient(180deg, rgba(167,243,208,.18), rgba(255,255,255,.85));
    border-radius: .85rem;
    padding: .65rem .75rem;
  }

  .onb-summary-label {
    font-size: .7rem;
    text-transform: uppercase;
    letter-spacing: .06em;
    color: rgba(2,8,20,.5);
    font-weight: 700;
    margin-bottom: .18rem;
  }

  .onb-summary-value {
    font-size: .96rem;
    font-weight: 700;
    color: #14532d;
  }

  .onb-block {
    border: 1px solid rgba(2,8,20,.08);
    border-radius: 1rem;
    background: #fff;
    box-shadow: 0 8px 18px rgba(2,8,20,.05);
    padding: 1.1rem;
  }

  .progress-steps {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 2rem 0;
  }

  .progress-step {
    display: flex;
    flex-direction: column;
    align-items: center;
    flex: 1;
    position: relative;
  }

  .progress-step:not(:last-child)::after {
    content: '';
    position: absolute;
    top: 20px;
    left: 50%;
    width: 100%;
    height: 2px;
    background: #e5e7eb;
    z-index: 0;
  }

  .progress-step.completed:not(:last-child)::after {
    background: var(--brand);
  }

  .step-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    color: #fff;
    background: #d1d5db;
    margin-bottom: .75rem;
    z-index: 1;
    position: relative;
  }

  .progress-step.active .step-icon {
    background: var(--brand);
    box-shadow: 0 0 0 4px rgba(22,101,52,.1);
  }

  .progress-step.completed .step-icon {
    background: var(--brand);
  }

  .step-label {
    font-size: .875rem;
    font-weight: 500;
    color: #6b7280;
    text-align: center;
  }

  .progress-step.active .step-label {
    color: var(--brand);
    font-weight: 600;
  }

  .progress-step.completed .step-label {
    color: var(--brand);
  }

  .content-card {
    background: #fff;
    border: 1px solid rgba(2,8,20,.08);
    border-radius: 1rem;
    padding: 2rem;
    margin-bottom: 1.5rem;
  }

  .content-card h4 {
    color: #0f172a;
    font-weight: 700;
    margin-bottom: 1rem;
  }

  .btn-brand {
    background: var(--brand);
    border-color: var(--brand);
    color: #fff;
  }

  .btn-brand:hover {
    background: var(--brand-dark);
    border-color: var(--brand-dark);
    color: #fff;
  }

  .alert-info {
    background: rgba(22,101,52,.08);
    border: 1px solid rgba(22,101,52,.2);
    color: #1e3a2f;
  }

  .contract-box {
    background: #f8f9fa;
    border: 1px solid rgba(2,8,20,.08);
    border-radius: .75rem;
    padding: 1.5rem;
    font-family: 'Courier New', monospace;
    font-size: .875rem;
    line-height: 1.6;
    white-space: pre-line;
    max-height: 400px;
    overflow-y: auto;
  }

  .contract-shell {
    background: linear-gradient(180deg, #fcfbf6 0%, #f7f3e8 100%);
    border: 1px solid #d8cfb6;
    border-radius: 1rem;
    padding: 1.5rem;
    box-shadow: inset 0 0 0 1px rgba(130, 108, 66, .08), 0 10px 22px rgba(44, 33, 12, .08);
  }

  .contract-shell.legal-document {
    font-family: Georgia, 'Times New Roman', serif;
    color: #1f2937;
  }

  .contract-header {
    border-bottom: 1px dashed rgba(92, 72, 39, .35);
    padding-bottom: 1rem;
    margin-bottom: 1.25rem;
  }

  .contract-meta {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: .75rem;
    font-size: .9rem;
    color: #475569;
  }

  .contract-section {
    background: rgba(255,255,255,.9);
    border: 1px solid rgba(130, 108, 66, .24);
    border-left: 4px solid rgba(130, 108, 66, .38);
    border-radius: .85rem;
    padding: 1rem 1.25rem;
    margin-bottom: 1rem;
  }

  .contract-section h6 {
    font-weight: 700;
    color: #1f2937;
    margin-bottom: .65rem;
    text-transform: uppercase;
    letter-spacing: .04em;
    font-size: .8rem;
  }

  .snapshot-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }

  .rules-toggle {
    overflow: hidden;
  }

  .rules-toggle summary {
    list-style: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: .75rem;
    font-weight: 700;
    color: #1f2937;
    font-family: 'Manrope', system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
  }

  .rules-toggle summary::-webkit-details-marker {
    display: none;
  }

  .rules-toggle-label {
    display: inline-flex;
    align-items: center;
    gap: .45rem;
  }

  .rules-toggle-hint {
    color: #64748b;
    font-size: .78rem;
    font-weight: 600;
  }

  .rules-toggle summary .bi-chevron-down {
    transition: transform .2s ease;
  }

  .rules-toggle[open] summary .bi-chevron-down {
    transform: rotate(180deg);
  }

  .contract-list {
    padding-left: 1.1rem;
    margin-bottom: 0;
  }

  .contract-highlight {
    background: rgba(22,101,52,.08);
    border-radius: .6rem;
    padding: .75rem 1rem;
    font-weight: 600;
    color: #14532d;
  }

  .payment-method-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: .75rem;
  }

  .payment-method-card {
    position: relative;
    border: 1px solid rgba(2,8,20,.14);
    border-radius: .85rem;
    background: #fff;
    padding: .85rem .95rem;
    cursor: pointer;
    transition: border-color .18s ease, box-shadow .18s ease, transform .18s ease;
  }

  .payment-method-card:hover {
    border-color: rgba(22,101,52,.4);
    transform: translateY(-1px);
  }

  .payment-method-card.selected {
    border-color: rgba(22,101,52,.7);
    box-shadow: 0 0 0 3px rgba(22,101,52,.12);
    background: linear-gradient(180deg, rgba(167,243,208,.26), rgba(255,255,255,.95));
  }

  .payment-method-card input[type="radio"] {
    position: absolute;
    opacity: 0;
    pointer-events: none;
  }

  .payment-method-title {
    font-weight: 700;
    color: #0f172a;
  }

  .payment-method-sub {
    font-size: .78rem;
    color: #64748b;
  }

  .payment-channel-detail {
    border: 1px solid rgba(2,8,20,.08);
    border-radius: .85rem;
    background: #fff;
    padding: .9rem 1rem;
  }

  .payment-detail-label {
    font-size: .72rem;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: .05em;
    font-weight: 700;
  }

  .payment-detail-value {
    font-weight: 700;
    color: #0f172a;
  }

  .gcash-qr-preview {
    display: inline-block;
    border: 1px solid rgba(2,8,20,.1);
    border-radius: .75rem;
    padding: .35rem;
    background: #fff;
    max-width: 160px;
  }

  .gcash-qr-preview img {
    width: 100%;
    height: auto;
    border-radius: .45rem;
    display: block;
  }

  .signature-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 1rem;
  }

  .signature-pad {
    border: 1px dashed rgba(15,23,42,.3);
    border-radius: .75rem;
    height: 120px;
    background: repeating-linear-gradient(
      0deg,
      #fff,
      #fff 28px,
      #f1f5f9 28px,
      #f1f5f9 29px
    );
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #94a3b8;
    font-size: .85rem;
    text-transform: uppercase;
    letter-spacing: .08em;
  }

  .signature-actions {
    display: flex;
    flex-wrap: wrap;
    gap: .5rem;
  }

  .signature-hint {
    font-size: .85rem;
    color: #64748b;
  }

  .signature-card {
    border: 1px dashed rgba(15,23,42,.28);
    border-radius: .85rem;
    background: #f8fafc;
    padding: 1rem;
  }

  .signature-preview {
    width: 100%;
    height: 120px;
    border: 1px solid rgba(2,8,20,.12);
    border-radius: .75rem;
    background: #fff;
    object-fit: contain;
    padding: .35rem;
  }

  .signature-preview-placeholder {
    width: 100%;
    height: 120px;
    border: 1px dashed rgba(15,23,42,.24);
    border-radius: .75rem;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: .82rem;
    color: #64748b;
    background: #fff;
    text-transform: uppercase;
    letter-spacing: .04em;
  }

  .signature-canvas-wrap {
    width: 100%;
    height: calc(100vh - 220px);
    border: 2px dashed rgba(15,23,42,.28);
    border-radius: 1rem;
    background: #fff;
    overflow: hidden;
    position: relative;
  }

  .signature-orientation-hint {
    display: none;
    margin-top: .5rem;
    color: #92400e;
    background: #fffbeb;
    border: 1px solid #fcd34d;
    border-radius: .65rem;
    padding: .5rem .75rem;
    font-size: .8rem;
  }

  .signature-orientation-hint.show {
    display: block;
  }

  #signatureCanvas {
    width: 100%;
    height: 100%;
    touch-action: none;
    cursor: crosshair;
    display: block;
  }

  .document-card {
    background: #fff;
    border: 1px solid rgba(2,8,20,.08);
    border-radius: 1rem;
    padding: 1.5rem;
    text-align: center;
    transition: all .2s;
    overflow: hidden;
  }

  .document-card:hover {
    border-color: rgba(22,101,52,.3);
    box-shadow: 0 4px 12px rgba(22,101,52,.1);
  }

  .document-file-name {
    font-size: .95rem;
    line-height: 1.35;
    margin-bottom: .25rem;
    overflow-wrap: anywhere;
    word-break: break-word;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    min-height: 2.6em;
  }

  .document-card .btn {
    white-space: nowrap;
  }

  .onb-upload-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: .9rem;
  }

  .onb-upload-card {
    border: 1px solid rgba(2,8,20,.1);
    border-radius: .85rem;
    background: #fff;
    padding: .9rem;
    box-shadow: 0 4px 12px rgba(2,8,20,.04);
  }

  .onb-file-name {
    display: inline-block;
    max-width: 100%;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    padding: .2rem .5rem;
    border-radius: .55rem;
    background: rgba(2,8,20,.04);
    border: 1px solid rgba(2,8,20,.08);
  }

  .bk-rules {
    background: linear-gradient(135deg, #f0fdf4 0%, #f8fafc 100%);
    border: 1.5px solid #bbf7d0;
    border-radius: 16px;
    padding: 1.75rem;
    margin-bottom: 1rem;
    font-size: .85rem;
    color: #374151;
    line-height: 1.7;
  }

  .bk-rules-header {
    display: flex;
    align-items: flex-start;
    gap: .75rem;
    margin-bottom: 1.25rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid #bbf7d0;
  }

  .bk-rules-icon {
    font-size: 1.4rem;
    color: var(--brand);
    flex-shrink: 0;
    margin-top: .05rem;
  }

  .bk-rules-title {
    font-size: 1rem;
    font-weight: 800;
    color: #0f172a;
    margin: 0;
  }

  .bk-rules-subtitle {
    font-size: .7rem;
    color: #6b7280;
    margin-top: .25rem;
  }

  .bk-rules-section {
    margin-top: 1.25rem;
  }

  .bk-rules-section-header {
    font-size: .78rem;
    font-weight: 700;
    color: var(--brand);
    text-transform: uppercase;
    letter-spacing: .05em;
    margin-bottom: .75rem;
    display: flex;
    align-items: center;
    gap: .5rem;
  }

  .bk-rules-list {
    list-style: none;
    margin: 0;
    padding: 0;
    display: flex;
    flex-direction: column;
    gap: .6rem;
  }

  .bk-rules-list li {
    display: flex;
    gap: .75rem;
    align-items: flex-start;
  }

  .bk-rules-list-icon {
    color: var(--brand);
    font-weight: 700;
    font-size: .75rem;
    flex-shrink: 0;
    width: 20px;
    text-align: center;
    margin-top: .1rem;
  }

  .bk-rules-list-text {
    color: #374151;
    font-size: .8rem;
  }

  .completion-banner {
    background: linear-gradient(135deg, var(--brand) 0%, var(--brand-dark) 100%);
    color: white;
    border-radius: 1.25rem;
    padding: 2rem;
    text-align: center;
    margin-bottom: 1.5rem;
  }

  .completion-banner h3 {
    margin-bottom: 0.5rem;
  }

  .digital-id-box {
    background: #f1f5f9;
    border: 2px solid var(--brand);
    border-radius: .75rem;
    padding: 1rem;
    font-family: 'Courier New', monospace;
    font-weight: 600;
    color: var(--brand);
    margin: 1rem 0;
  }

  .completion-card {
    border: 1px solid rgba(22,101,52,.16);
    background: linear-gradient(180deg, rgba(255,255,255,.96), rgba(240,253,244,.55));
  }

  .completion-status-row {
    display: flex;
    flex-wrap: wrap;
    gap: .5rem;
    margin-bottom: 1rem;
  }

  .completion-pill {
    display: inline-flex;
    align-items: center;
    gap: .35rem;
    border: 1px solid rgba(22,101,52,.2);
    background: rgba(255,255,255,.85);
    border-radius: 999px;
    padding: .35rem .7rem;
    font-size: .76rem;
    color: #14532d;
    font-weight: 700;
  }

  .completion-kpi-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: .75rem;
    margin-top: .5rem;
  }

  .completion-kpi {
    border: 1px solid rgba(15,23,42,.1);
    background: #fff;
    border-radius: .85rem;
    padding: .75rem .85rem;
  }

  .completion-kpi-label {
    font-size: .68rem;
    text-transform: uppercase;
    letter-spacing: .06em;
    color: #64748b;
    margin-bottom: .2rem;
    font-weight: 700;
  }

  .completion-kpi-value {
    font-weight: 700;
    color: #0f172a;
  }

  .completion-proof-link {
    margin-top: .9rem;
  }

  .completion-map-card {
    margin-top: 1rem;
    border: 1px solid rgba(15,23,42,.1);
    background: #fff;
    border-radius: .9rem;
    padding: .8rem;
  }

  .completion-map-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: .6rem;
    margin-bottom: .55rem;
  }

  .completion-map-title {
    font-weight: 700;
    color: #0f172a;
  }

  .completion-map-frame {
    border: 1px solid rgba(15,23,42,.12);
    border-radius: .75rem;
    overflow: hidden;
    background: #e2e8f0;
  }

  .completion-map-frame iframe {
    border: 0;
    width: 100%;
    height: 100%;
  }

  @media (max-width: 991.98px) {
    .onb-summary {
      grid-template-columns: repeat(2, minmax(0, 1fr));
    }
    .onb-shell {
      padding: .95rem;
    }
  }

  @media (max-width: 575.98px) {
    .onb-shell {
      padding: .7rem;
      border-radius: .9rem;
    }

    .onb-block {
      padding: .8rem;
      border-radius: .85rem;
    }

    .onb-summary {
      gap: .55rem;
    }

    .onb-summary-item {
      padding: .55rem .6rem;
      border-radius: .7rem;
    }

    .onb-summary-label {
      font-size: .63rem;
    }

    .onb-summary-value {
      font-size: .9rem;
    }

    .progress-steps {
      padding: 1rem 0 .65rem;
    }

    .step-icon {
      width: 34px;
      height: 34px;
      margin-bottom: .45rem;
      font-size: .85rem;
    }

    .progress-step:not(:last-child)::after {
      top: 17px;
    }

    .step-label {
      font-size: .78rem;
      line-height: 1.2;
    }

    .content-card {
      padding: 1rem;
      border-radius: .9rem;
      margin-bottom: 1rem;
    }

    .document-file-name {
      font-size: .86rem;
      -webkit-line-clamp: 3;
      min-height: 3.5em;
    }

    .completion-kpi-grid {
      grid-template-columns: 1fr;
    }

    .content-card h4 {
      font-size: 1.08rem;
    }

    .contract-shell {
      padding: .85rem;
      border-radius: .85rem;
    }

    .payment-method-grid {
      grid-template-columns: 1fr;
    }

    .contract-header {
      padding-bottom: .75rem;
      margin-bottom: .85rem;
    }

    .contract-meta {
      grid-template-columns: 1fr;
      gap: .45rem;
      font-size: .82rem;
    }

    .snapshot-grid {
      grid-template-columns: 1fr;
    }

    .contract-section {
      padding: .8rem .9rem;
      margin-bottom: .7rem;
      border-radius: .75rem;
    }

    .contract-list {
      padding-left: 1rem;
    }

    .contract-highlight {
      padding: .6rem .75rem;
      font-size: .84rem;
    }

    .bk-rules {
      padding: .95rem;
      border-radius: .9rem;
    }

    .bk-rules-header {
      padding-bottom: .75rem;
      margin-bottom: .85rem;
      gap: .55rem;
    }

    .bk-rules-title {
      font-size: .96rem;
    }

    .bk-rules-subtitle {
      font-size: .7rem;
    }

    .bk-rules-section {
      margin-top: .9rem;
    }

    .bk-rules-section-header {
      font-size: .74rem;
      margin-bottom: .5rem;
    }

    .bk-rules-list {
      gap: .45rem;
    }

    .bk-rules-list li {
      gap: .55rem;
    }

    .bk-rules-list-icon {
      width: 18px;
      font-size: .7rem;
    }

    .bk-rules-list-text {
      font-size: .8rem;
      line-height: 1.45;
    }

    .signature-grid {
      grid-template-columns: 1fr;
      gap: .75rem;
    }

    .signature-actions {
      gap: .4rem;
    }

    .signature-actions .btn {
      font-size: .76rem;
      padding: .35rem .65rem;
    }

    .signature-preview,
    .signature-preview-placeholder,
    .signature-pad {
      height: 100px;
    }

    .signature-canvas-wrap {
      height: calc(100vh - 245px);
    }
  }

  @media (max-width: 420px) {
    .onb-shell {
      padding: .6rem;
    }

    .onb-summary {
      grid-template-columns: 1fr 1fr;
      gap: .45rem;
    }

    .progress-steps {
      overflow-x: auto;
      padding-bottom: .3rem;
      gap: .35rem;
    }

    .progress-step {
      min-width: 70px;
    }

    .step-label {
      font-size: .72rem;
    }

    .onb-upload-grid {
      grid-template-columns: 1fr;
      gap: .65rem;
    }

    .onb-upload-card {
      padding: .75rem;
    }

    .document-card {
      padding: .95rem;
      border-radius: .85rem;
    }

    .document-card .d-flex.gap-2 {
      display: grid;
      grid-template-columns: 1fr 1fr;
      align-items: center;
      gap: .45rem !important;
    }

    .document-card .btn {
      width: 100%;
      padding-left: .35rem;
      padding-right: .35rem;
    }

    .content-card {
      padding: .85rem;
    }

    .contract-section h6 {
      font-size: .92rem;
      margin-bottom: .45rem;
    }

    .bk-rules {
      padding: .8rem;
    }

    .bk-rules-list-text {
      font-size: .78rem;
    }

    .signature-actions {
      display: grid;
      grid-template-columns: 1fr 1fr;
    }

    .signature-actions .btn {
      width: 100%;
    }
  }
</style>
@endpush

@section('content')
@php
  $statusLabel = (string) ($onboarding->status ?? 'pending');
  $booking = $onboarding->booking;
  $room = $booking->room;
  $property = $room->property;
  $monthlyRentAmount = is_numeric($booking->monthly_rent_amount)
    ? (float) $booking->monthly_rent_amount
    : (float) ($room->price ?? 0);
  $depositBaseAmount = $monthlyRentAmount;
  $selectedAdvanceAmount = !empty($booking->include_advance_payment) ? $monthlyRentAmount : 0.0;
  $expectedOnboardingAmount = $monthlyRentAmount + $selectedAdvanceAmount;
  $submittedOnboardingAmount = is_numeric($onboarding->deposit_amount) && (float) $onboarding->deposit_amount > 0
    ? (float) $onboarding->deposit_amount
    : 0.0;
  $effectiveDepositAmount = max($expectedOnboardingAmount, $submittedOnboardingAmount);
  $checkInLabel = optional($booking->check_in)->format('M d, Y');
  $checkOutLabel = optional($booking->check_out)->format('M d, Y') ?: 'Open Ended';
  $bookingStatusLabel = ucfirst((string) ($booking->status ?? 'pending'));
  $requestedLabel = optional($booking->created_at)->diffForHumans() ?: 'Recently';
  $durationDays = method_exists($booking, 'getDurationInDays') ? $booking->getDurationInDays() : 0;
  $roomModeLabel = ucfirst((string) ($booking->occupancy_mode ?? 'Solo'));
  $advancePaymentLabel = !empty($booking->include_advance_payment) ? 'Yes' : 'No';
  $paymentStatusLabel = ucfirst((string) $booking->derivedPaymentStatus());
  $propertyAddress = trim((string) ($property->address ?? ''));
  $propertyLatitude = $property->latitude;
  $propertyLongitude = $property->longitude;
  $hasPropertyCoordinates = is_numeric($propertyLatitude) && is_numeric($propertyLongitude);
  $mapQuery = $hasPropertyCoordinates
    ? ((string) $propertyLatitude . ',' . (string) $propertyLongitude)
    : trim($propertyAddress !== '' ? $propertyAddress : (string) ($property->name ?? ''));
  $mapEmbedUrl = $mapQuery !== ''
    ? ('https://maps.google.com/maps?q=' . rawurlencode($mapQuery) . '&z=15&output=embed')
    : null;
  $mapOpenUrl = $mapQuery !== ''
    ? ('https://www.google.com/maps/search/?api=1&query=' . rawurlencode($mapQuery))
    : null;
  $landlordProfile = optional($property->landlord)->landlordProfile;
  $preferredPaymentMethods = collect((array) ($landlordProfile->preferred_payment_methods ?? []))
    ->map(fn ($method) => strtolower(trim((string) $method)))
    ->filter(fn ($method) => in_array($method, ['bank', 'gcash', 'cash'], true))
    ->unique()
    ->values();
  $hasBankDetails = filled($landlordProfile?->payment_bank_name)
    && filled($landlordProfile?->payment_account_name)
    && filled($landlordProfile?->payment_account_number);
  $hasGcashDetails = filled($landlordProfile?->payment_gcash_name)
    && filled($landlordProfile?->payment_gcash_number);
  $availablePaymentMethods = collect();
  if ($preferredPaymentMethods->contains('bank') && $hasBankDetails) {
    $availablePaymentMethods->push('bank');
  }
  if ($preferredPaymentMethods->contains('gcash') && $hasGcashDetails) {
    $availablePaymentMethods->push('gcash');
  }
  if ($preferredPaymentMethods->contains('cash')) {
    $availablePaymentMethods->push('cash');
  }
  if ($availablePaymentMethods->isEmpty()) {
    if ($hasBankDetails) {
      $availablePaymentMethods->push('bank');
    }
    if ($hasGcashDetails) {
      $availablePaymentMethods->push('gcash');
    }
  }
  $selectedPaymentMethod = strtolower((string) old('payment_method', $availablePaymentMethods->first() ?? ''));
  $paymentReviewPending = $statusLabel === 'deposit_paid' && empty($onboarding->deposit_paid_at);
  $statusDisplayLabel = $paymentReviewPending
    ? 'Payment under review'
    : ucfirst(str_replace('_', ' ', $statusLabel));
  $docsDone = in_array($statusLabel, ['documents_uploaded', 'contract_signed', 'deposit_paid', 'completed'], true);
  $contractDone = in_array($statusLabel, ['contract_signed', 'deposit_paid', 'completed'], true);
  $depositDone = in_array($statusLabel, ['deposit_paid', 'completed'], true);
  $completeDone = ($statusLabel === 'completed');
  $stepsDone = (int) $docsDone + (int) $contractDone + (int) $depositDone + (int) $completeDone;
  $progressPct = (int) round(($stepsDone / 4) * 100);

  $defaultHouseRuleCategories = (array) config('property_house_rules.categories', []);
  $propertyHouseRules = (array) ($onboarding->booking->room->property->house_rules ?? []);
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

<div class="onb-shell mb-4">
  <div class="onb-block mb-3">
    <div class="mb-4">
      <div class="text-uppercase small text-muted fw-semibold">Student Operations</div>
      <h4 class="fw-bold mb-1">Onboarding Process</h4>
      <p class="text-muted mb-0">{{ $onboarding->booking->room->property->name }} — Room {{ $onboarding->booking->room->room_number }}</p>
    </div>

    <div class="onb-summary mb-4">
      <div class="onb-summary-item">
        <div class="onb-summary-label">Status</div>
        <div class="onb-summary-value">{{ $statusDisplayLabel }}</div>
      </div>
      <div class="onb-summary-item">
        <div class="onb-summary-label">Progress</div>
        <div class="onb-summary-value">{{ $progressPct }}%</div>
      </div>
      <div class="onb-summary-item">
        <div class="onb-summary-label">Payment Due</div>
        <div class="onb-summary-value">P{{ number_format($effectiveDepositAmount, 0) }}</div>
        @if($selectedAdvanceAmount > 0)
          <div class="small text-muted fw-semibold mt-1">Deposit P{{ number_format($depositBaseAmount, 0) }} + Advance P{{ number_format($selectedAdvanceAmount, 0) }}</div>
        @else
          <div class="small text-muted fw-semibold mt-1">Deposit P{{ number_format($depositBaseAmount, 0) }}</div>
        @endif
      </div>
      <div class="onb-summary-item">
        <div class="onb-summary-label">Lease</div>
        <div class="onb-summary-value">{{ optional($booking->check_in)->format('M d') }} - {{ optional($booking->check_out)->format('M d') }}</div>
      </div>
    </div>

    @if(session('success'))
      <div class="alert alert-info rounded-3 mb-4" role="alert">
        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
      </div>
    @endif

    @if(session('error'))
      <div class="alert alert-danger rounded-3 mb-4" role="alert">
        <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
      </div>
    @endif

    <!-- Progress Steps -->
    <div class="progress-steps">
      <div class="progress-step @if(in_array($onboarding->status, ['documents_uploaded', 'contract_signed', 'deposit_paid', 'completed'])) completed @endif @if($onboarding->status === 'pending') active @endif">
        <div class="step-icon">
          @if(in_array($onboarding->status, ['documents_uploaded', 'contract_signed', 'deposit_paid', 'completed']))
            <i class="bi bi-check"></i>
          @else
            1
          @endif
        </div>
        <div class="step-label">Documents</div>
      </div>

      <div class="progress-step @if(in_array($onboarding->status, ['contract_signed', 'deposit_paid', 'completed'])) completed @endif @if($onboarding->status === 'documents_uploaded') active @endif">
        <div class="step-icon">
          @if(in_array($onboarding->status, ['contract_signed', 'deposit_paid', 'completed']))
            <i class="bi bi-check"></i>
          @else
            2
          @endif
        </div>
        <div class="step-label">Contract</div>
      </div>

      <div class="progress-step @if(in_array($onboarding->status, ['deposit_paid', 'completed'])) completed @endif @if($onboarding->status === 'contract_signed') active @endif">
        <div class="step-icon">
          @if(in_array($onboarding->status, ['deposit_paid', 'completed']))
            <i class="bi bi-check"></i>
          @else
            3
          @endif
        </div>
        <div class="step-label">Deposit</div>
      </div>

      <div class="progress-step @if($onboarding->status === 'completed') completed @elseif($onboarding->status === 'deposit_paid') active @endif">
        <div class="step-icon">
          @if($onboarding->status === 'completed')
            <i class="bi bi-check"></i>
          @else
            4
          @endif
        </div>
        <div class="step-label">Complete</div>
      </div>
    </div>
  </div>

  <!-- Step 1: Document Upload -->
  @if($onboarding->status === 'pending')
    <div class="content-card">
      <h4>
        <i class="bi bi-file-earmark-arrow-up me-2" style="color: var(--brand);"></i>
        Step 1: Upload Required Documents
      </h4>
      <p class="text-muted mb-3">Please upload the following required documents to proceed:</p>

      @php
        $requiredCount = count((array) ($onboarding->required_documents ?? []));
      @endphp

      <div class="alert rounded-3 mb-4" style="background: linear-gradient(135deg, rgba(22,101,52,.08), rgba(167,243,208,.12)); border: 1px solid rgba(22,101,52,.15); color: #14532d;">
        <div class="d-flex align-items-start gap-3">
          <div style="font-size: 1.4rem; flex-shrink: 0;">
            <i class="bi bi-info-circle-fill"></i>
          </div>
          <div style="flex-grow: 1;">
            <div class="fw-600 mb-1">Complete all required documents to proceed</div>
            <div class="small" style="line-height: 1.5;">
              You need to upload <strong>{{ $requiredCount }} required document{{ $requiredCount > 1 ? 's' : '' }}</strong> before you can move forward to Step 2 (Contract Signing). 
              Each document must be a clear image or PDF file. Once all documents are uploaded, click the <strong>Next</strong> button to continue your onboarding journey.
            </div>
          </div>
        </div>
      </div>

      @if($errors->has('documents') || $errors->has('documents.*'))
        <div class="alert alert-danger rounded-3 mb-3">
          {{ $errors->first('documents') ?: $errors->first('documents.*') }}
        </div>
      @endif

      <form method="POST" action="{{ route('student.onboarding.upload_documents', $onboarding) }}" enctype="multipart/form-data" id="onboardingDocumentsForm">
        @csrf

        @php
          $isFirstYearStudent = (string) ($onboarding->booking->student->year_level ?? '') === '1st Year';
        @endphp

        <div class="onb-upload-grid mb-3">
          @foreach(($onboarding->required_documents ?? []) as $doc)
            @php
              $docKey = (string) $doc;
              $docLabel = $docKey === 'student_id' && $isFirstYearStudent
                ? 'Student ID / COR'
                : ucwords(str_replace('_', ' ', $docKey));
              $docHint = match ($docKey) {
                'student_id' => $isFirstYearStudent
                  ? 'Upload a clear School ID or COR (Certificate of Registration) image or PDF copy.'
                  : 'Upload a clear School ID image or PDF copy.',
                'proof_of_income' => 'Upload proof of income or financial support document.',
                'emergency_contact' => 'Upload emergency contact proof or guardian details document.',
                default => 'Upload a clear file for this requirement.',
              };
              $inputId = 'onb_doc_' . $loop->index;
            @endphp

            <div class="onb-upload-card">
              <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
                <div>
                  <div class="fw-semibold">{{ $docLabel }}</div>
                  <div class="small text-muted">{{ $docHint }}</div>
                </div>
                <span class="badge text-bg-light border">Required</span>
              </div>

              <input
                type="file"
                class="d-none onb-doc-input"
                id="{{ $inputId }}"
                name="documents[]"
                accept=".pdf,.jpg,.jpeg,.png"
                required
              >

              <div class="d-flex align-items-center gap-2 flex-wrap">
                <label for="{{ $inputId }}" class="btn btn-sm btn-outline-secondary rounded-pill mb-0">
                  <i class="bi bi-upload me-1"></i>Choose File
                </label>
                <span class="onb-file-name text-muted small" data-file-name-for="{{ $inputId }}">No file selected</span>
              </div>
            </div>
          @endforeach
        </div>

        <div class="d-flex justify-content-end">
          <button type="submit" class="btn btn-brand rounded-pill px-4" id="onboardingDocsNextBtn" disabled>
            Next <i class="bi bi-arrow-right ms-1"></i>
          </button>
        </div>

      </form>
    </div>
  @endif

  <!-- Step 2: Contract Signing -->
  @if($onboarding->status === 'documents_uploaded')
    <div class="content-card">
      <h4>
        <i class="bi bi-file-check me-2" style="color: var(--brand);"></i>
        Step 2: Review and Sign Contract
      </h4>
      <p class="text-muted mb-3">Please review the lease contract carefully before signing:</p>

      <div class="contract-shell legal-document mb-4">
        <div class="contract-header">
          <div class="d-flex align-items-start justify-content-between flex-wrap gap-3">
            <div>
              <div class="text-uppercase small text-muted">Residential Lease Agreement</div>
              <h5 class="fw-bold mb-1">Legal Contract Preview</h5>
              <div class="text-muted small">Generated for onboarding #{{ $onboarding->id }}</div>
            </div>
            <div class="contract-highlight">
              Agreement Date: {{ now()->format('F j, Y') }}
            </div>
          </div>
          <div class="contract-meta mt-3">
            <div><strong>Property:</strong> {{ $property->name }}</div>
            <div><strong>Room:</strong> {{ $room->room_number }}</div>
            <div><strong>Monthly Rent:</strong> ₱{{ number_format($monthlyRentAmount, 2) }}</div>
            <div><strong>Advance Amount:</strong> ₱{{ number_format($selectedAdvanceAmount, 2) }}</div>
            <div><strong>Full Payment Due:</strong> ₱{{ number_format($effectiveDepositAmount, 2) }}</div>
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
          <h6>1. Premises</h6>
          <p class="mb-0">
            The Landlord agrees to rent to the Tenant, and the Tenant agrees to rent from the Landlord, the property located at
            <strong>{{ $property->name }}</strong>, Room <strong>{{ $room->room_number }}</strong> (the "Premises"), under the terms and conditions set forth in this Agreement.
          </p>
        </div>

        <div class="contract-section">
          <h6>2. Term and Occupancy</h6>
          <p class="mb-0">
            The Tenant agrees to occupy the Premises under the approved booking terms and to strictly adhere to all community policies established by the Landlord.
            This Agreement serves as the binding contract for the tenancy commencing upon the completion of the Onboarding Process.
          </p>
        </div>

        <div class="contract-section">
          <h6>3. Rent and Payment</h6>
          <ul class="contract-list mb-0">
            <li><strong>Due Date.</strong> Rent is due on the 1st day of each month.</li>
            <li><strong>Method of Payment.</strong> All rent payments must be payable exclusively through the designated Platform.</li>
            <li><strong>Onboarding Payment.</strong> The move-in payment equals monthly rent plus any selected/required advance payment and is required to complete onboarding.</li>
          </ul>
        </div>

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

        <div class="contract-section">
          <h6>5. Execution and Move-In Conditions</h6>
          <ul class="contract-list mb-0">
            <li><strong>Binding Effect.</strong> This Contract becomes legally binding once all identity documents are verified and the electronic signature is submitted by both parties.</li>
            <li><strong>Payment Verification.</strong> Advance payment and payment status will be reviewed and verified prior to room handover.</li>
            <li><strong>Possession.</strong> Move-in is strictly subject to the confirmed check-in date and approved booking status.</li>
          </ul>
        </div>

        <div class="contract-section mb-0">
          <h6>IN WITNESS WHEREOF</h6>
          <p class="mb-0">
            The parties have executed this Agreement as of the date first written above.
          </p>
        </div>
      </div>

      <div class="mb-4">
        <label class="form-label fw-600 mb-2">E-signature</label>
        <div class="signature-grid">
          <div>
            <div class="signature-card">
              <div id="signaturePreviewPlaceholder" class="signature-preview-placeholder">No signature yet</div>
              <img id="signaturePreviewImage" class="signature-preview d-none" alt="Signature preview" />
            </div>
            <div class="signature-actions mt-2">
              <button type="button" class="btn btn-brand btn-sm rounded-pill px-3" id="openSignatureModalBtn" data-bs-toggle="modal" data-bs-target="#signatureCanvasModal">
                <i class="bi bi-pen me-1"></i>Sign Here
              </button>
              <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3" id="uploadSignatureBtn">
                <i class="bi bi-upload me-1"></i>Upload Signature
              </button>
              <input type="file" id="uploadSignatureInput" class="d-none" accept="image/png,image/jpeg,image/jpg,image/webp">
              <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3" id="clearSavedSignatureBtn" disabled>
                <i class="bi bi-eraser me-1"></i>Clear
              </button>
            </div>
            <div class="signature-hint mt-2">Click <strong>Sign Here</strong> to draw on full-screen canvas or <strong>Upload Signature</strong> to use a signature image file.</div>
          </div>
          <div>
            <label class="form-label fw-600">Typed Signature</label>
            <input type="text" class="form-control" placeholder="Type your full name" value="{{ auth()->user()->name }}" readonly />
            <label class="form-label fw-600 mt-3">Date Signed</label>
            <input type="text" class="form-control" value="{{ now()->format('F j, Y') }}" readonly />
            <div class="small text-muted mt-2" id="signatureStatusText">Signature required before contract submission.</div>
          </div>
        </div>
      </div>

      <form method="POST" action="{{ route('student.onboarding.sign_contract', $onboarding) }}" id="onboardingContractForm">
        @csrf
        <input type="hidden" name="signature_data" id="contractSignatureData">
        <input type="hidden" name="signature_name" id="contractSignatureName" value="">
        <div class="form-check mb-4">
          <input class="form-check-input" type="checkbox" id="agree" required style="width: 1.25rem; height: 1.25rem; border-color: rgba(22,101,52,.3);">
          <label class="form-check-label" for="agree" style="margin-left: 0.5rem;">
            <span class="fw-500">I agree to the terms and conditions</span> outlined in this lease contract
          </label>
        </div>
        <button type="submit" class="btn btn-brand rounded-pill px-4" id="signContractSubmitBtn" disabled>
          <i class="bi bi-pen me-1"></i>Sign Contract
        </button>
      </form>

      <div class="modal fade" id="signatureCanvasModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-fullscreen">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">Draw Your Signature</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <div class="signature-canvas-wrap">
                <canvas id="signatureCanvas"></canvas>
              </div>
              <div id="signatureOrientationHint" class="signature-orientation-hint">
                Rotate your phone to landscape for a wider signing area.
              </div>
              <div class="small text-muted mt-2">Use mouse or finger/stylus to sign. Your signature will be attached to this contract submission.</div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-outline-secondary rounded-pill" id="clearSignatureCanvasBtn">Clear Canvas</button>
              <button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">Cancel</button>
              <button type="button" class="btn btn-brand rounded-pill px-4" id="saveSignatureBtn">Save Signature</button>
            </div>
          </div>
        </div>
      </div>
    </div>
  @endif

  <!-- Step 3: Deposit Payment -->
  @if($onboarding->status === 'contract_signed')
    <div class="content-card">
      <h4>
        <i class="bi bi-credit-card me-2" style="color: var(--brand);"></i>
        Step 3: Pay Move-in Amount
      </h4>
      
      <div class="alert alert-info rounded-3 mb-4">
        <div class="d-flex align-items-center">
          <div>
            <strong class="d-block">Full Payment Due: ₱{{ number_format($effectiveDepositAmount, 2) }}</strong>
            <small>
              Deposit amount: ₱{{ number_format($depositBaseAmount, 2) }}
              @if($selectedAdvanceAmount > 0)
                + Advance: ₱{{ number_format($selectedAdvanceAmount, 2) }}
              @endif
            </small>
          </div>
        </div>
      </div>

      @if($availablePaymentMethods->isEmpty())
        <div class="alert alert-warning rounded-3 mb-0">
          <i class="bi bi-exclamation-circle me-2"></i>
          The landlord has not completed billing setup yet. Please contact the landlord to enable payment methods.
        </div>
      @else
        <form method="POST" action="{{ route('student.onboarding.pay_deposit', $onboarding) }}" enctype="multipart/form-data" id="onboardingPaymentForm">
          @csrf

          <div class="contract-section mb-3">
            <h6 class="mb-3">Select Payment Method</h6>
            <div class="payment-method-grid">
              @if($availablePaymentMethods->contains('bank'))
                <label class="payment-method-card {{ $selectedPaymentMethod === 'bank' ? 'selected' : '' }}">
                  <input type="radio" name="payment_method" value="bank" {{ $selectedPaymentMethod === 'bank' ? 'checked' : '' }} required>
                  <div class="payment-method-title"><i class="bi bi-bank me-1"></i>Bank Transfer</div>
                  <div class="payment-method-sub">Send via bank account details</div>
                </label>
              @endif

              @if($availablePaymentMethods->contains('gcash'))
                <label class="payment-method-card {{ $selectedPaymentMethod === 'gcash' ? 'selected' : '' }}">
                  <input type="radio" name="payment_method" value="gcash" {{ $selectedPaymentMethod === 'gcash' ? 'checked' : '' }} required>
                  <div class="payment-method-title"><i class="bi bi-phone me-1"></i>GCash</div>
                  <div class="payment-method-sub">Use wallet number or QR code</div>
                </label>
              @endif

              @if($availablePaymentMethods->contains('cash'))
                <label class="payment-method-card {{ $selectedPaymentMethod === 'cash' ? 'selected' : '' }}">
                  <input type="radio" name="payment_method" value="cash" {{ $selectedPaymentMethod === 'cash' ? 'checked' : '' }} required>
                  <div class="payment-method-title"><i class="bi bi-cash-stack me-1"></i>Cash</div>
                  <div class="payment-method-sub">Pay directly to landlord</div>
                </label>
              @endif
            </div>
            @error('payment_method')
              <div class="text-danger small mt-2">{{ $message }}</div>
            @enderror
          </div>

          @if($availablePaymentMethods->contains('bank'))
            <div class="payment-channel-detail mb-3 {{ $selectedPaymentMethod === 'bank' ? '' : 'd-none' }}" data-payment-panel="bank">
              <div class="row g-2">
                <div class="col-md-4">
                  <div class="payment-detail-label">Bank</div>
                  <div class="payment-detail-value">{{ $landlordProfile->payment_bank_name }}</div>
                </div>
                <div class="col-md-4">
                  <div class="payment-detail-label">Account Name</div>
                  <div class="payment-detail-value">{{ $landlordProfile->payment_account_name }}</div>
                </div>
                <div class="col-md-4">
                  <div class="payment-detail-label">Account Number</div>
                  <div class="payment-detail-value">{{ $landlordProfile->payment_account_number }}</div>
                </div>
              </div>
            </div>
          @endif

          @if($availablePaymentMethods->contains('gcash'))
            <div class="payment-channel-detail mb-3 {{ $selectedPaymentMethod === 'gcash' ? '' : 'd-none' }}" data-payment-panel="gcash">
              <div class="row g-2 align-items-start">
                <div class="col-md-8">
                  <div class="payment-detail-label">GCash Name</div>
                  <div class="payment-detail-value">{{ $landlordProfile->payment_gcash_name }}</div>
                  <div class="payment-detail-label mt-2">GCash Number</div>
                  <div class="payment-detail-value">{{ $landlordProfile->payment_gcash_number }}</div>
                </div>
                <div class="col-md-4 text-md-end">
                  @if(!empty($landlordProfile->payment_gcash_qr_path))
                    <a href="{{ asset('storage/' . $landlordProfile->payment_gcash_qr_path) }}" target="_blank" rel="noopener" class="gcash-qr-preview" title="Open QR in new tab">
                      <img src="{{ asset('storage/' . $landlordProfile->payment_gcash_qr_path) }}" alt="GCash QR">
                    </a>
                  @endif
                </div>
              </div>
            </div>
          @endif

          @if($availablePaymentMethods->contains('cash'))
            <div class="payment-channel-detail mb-3 {{ $selectedPaymentMethod === 'cash' ? '' : 'd-none' }}" data-payment-panel="cash">
              <div class="payment-detail-value">Cash payment selected</div>
              <div class="small text-muted mt-1">Coordinate with the landlord for exact handover schedule and official receipt issuance.</div>
            </div>
          @endif

          @if(filled($landlordProfile?->payment_instructions))
            <div class="alert alert-light border rounded-3 mb-3">
              <div class="fw-semibold mb-1"><i class="bi bi-chat-square-text me-1"></i>Landlord Billing Instructions</div>
              <div class="small text-muted mb-0">{{ $landlordProfile->payment_instructions }}</div>
            </div>
          @endif

          <div class="row g-3 mb-3 {{ $selectedPaymentMethod === 'cash' ? 'd-none' : '' }}" id="onlinePaymentFields">
            <div class="col-md-6">
              <label class="form-label fw-600" for="paymentReferenceInput">Reference Number</label>
              <input
                type="text"
                class="form-control @error('payment_reference') is-invalid @enderror"
                id="paymentReferenceInput"
                name="payment_reference"
                value="{{ old('payment_reference') }}"
                maxlength="120"
                placeholder="Enter transaction reference"
              >
              <small class="text-muted" id="paymentReferenceHelp">Required for selected method. Enter the transaction reference number.</small>
              @error('payment_reference')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
            <div class="col-md-6">
              <label class="form-label fw-600" for="paymentProofInput">Payment Proof</label>
              <input
                type="file"
                class="form-control @error('payment_proof') is-invalid @enderror"
                id="paymentProofInput"
                name="payment_proof"
                accept=".jpg,.jpeg,.png,.pdf"
              >
              <small class="text-muted">Required for Bank and GCash. Accepted: JPG, PNG, PDF (max 5MB).</small>
              @error('payment_proof')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>

          <div class="mb-4">
            <label class="form-label fw-600" for="paymentNotesInput">Payment Notes (Optional)</label>
            <textarea
              class="form-control @error('payment_notes') is-invalid @enderror"
              id="paymentNotesInput"
              name="payment_notes"
              rows="3"
              maxlength="1000"
              placeholder="Add notes like transfer time, receipt number, or payment remarks."
            >{{ old('payment_notes') }}</textarea>
            @error('payment_notes')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <button type="submit" class="btn btn-brand rounded-pill px-4">
            <i class="bi bi-check-circle me-1"></i>Submit Payment Transaction
          </button>
        </form>
      @endif
    </div>
  @endif

  @if($paymentReviewPending)
    <div class="content-card">
      <h4>
        <i class="bi bi-hourglass-split me-2" style="color: var(--brand);"></i>
        Step 4: Waiting For Landlord Payment Approval
      </h4>

      <div class="alert alert-warning rounded-3 mb-3">
        <i class="bi bi-info-circle me-2"></i>
        Your payment has been submitted and is now pending landlord verification. Tenant access will be activated only after approval.
      </div>

      <div class="row g-3">
        <div class="col-12 col-md-4">
          <div class="border rounded-3 p-3 h-100">
            <div class="small text-muted">Payment Method</div>
            <div class="fw-semibold">{{ ucfirst((string) ($onboarding->payment_method ?? '—')) }}</div>
          </div>
        </div>
        <div class="col-12 col-md-4">
          <div class="border rounded-3 p-3 h-100">
            <div class="small text-muted">Reference Number</div>
            <div class="fw-semibold">{{ $onboarding->payment_reference ?: '—' }}</div>
          </div>
        </div>
        <div class="col-12 col-md-4">
          <div class="border rounded-3 p-3 h-100">
            <div class="small text-muted">Submitted At</div>
            <div class="fw-semibold">{{ optional($onboarding->payment_submitted_at)->format('M d, Y h:i A') ?: '—' }}</div>
          </div>
        </div>
      </div>

      @if(!empty($onboarding->payment_notes))
        <div class="alert alert-light border rounded-3 mt-3 mb-0">
          <div class="fw-semibold small text-muted mb-1">Your Payment Notes</div>
          <div>{{ $onboarding->payment_notes }}</div>
        </div>
      @endif
    </div>
  @endif

  <!-- Completion Status -->
  @if($onboarding->status === 'completed')
    <div class="completion-banner">
      <i class="bi bi-check-circle-fill" style="font-size: 2.5rem;"></i>
      <h3>Onboarding Completed!</h3>
      <p class="mb-0">You're all set to move into your new home.</p>
    </div>

    <div class="content-card completion-card">
      @if($onboarding->digital_id)
        <div class="mb-4">
          <h5 class="fw-600 mb-2">Your Digital Tenant ID</h5>
          <div class="digital-id-box">
            {{ $onboarding->digital_id }}
          </div>
          <small class="text-muted">
            <i class="bi bi-info-circle me-1"></i>
            Keep this ID safe — you'll need it for check-in and other services
          </small>
        </div>
      @endif

      <div class="completion-status-row">
        <span class="completion-pill"><i class="bi bi-file-earmark-check"></i>Documents Verified</span>
        <span class="completion-pill"><i class="bi bi-pen"></i>Contract Signed</span>
        <span class="completion-pill"><i class="bi bi-wallet2"></i>Payment Settled</span>
        <span class="completion-pill"><i class="bi bi-house-check"></i>Move-in Ready</span>
      </div>

      <div class="completion-kpi-grid">
        <div class="completion-kpi">
          <div class="completion-kpi-label">Property</div>
          <div class="completion-kpi-value">{{ $onboarding->booking->room->property->name }}</div>
        </div>
        <div class="completion-kpi">
          <div class="completion-kpi-label">Room</div>
          <div class="completion-kpi-value">{{ $onboarding->booking->room->room_number }}</div>
        </div>
        <div class="completion-kpi">
          <div class="completion-kpi-label">Lease Period</div>
          <div class="completion-kpi-value">{{ $onboarding->booking->check_in->format('M d, Y') }} — {{ $onboarding->booking->check_out->format('M d, Y') }}</div>
        </div>
        <div class="completion-kpi">
          <div class="completion-kpi-label">Total Paid</div>
          <div class="completion-kpi-value">₱{{ number_format($onboarding->deposit_amount, 2) }}</div>
        </div>
        @if(!empty($onboarding->payment_method))
          <div class="completion-kpi">
            <div class="completion-kpi-label">Payment Method</div>
            <div class="completion-kpi-value">{{ ucfirst((string) $onboarding->payment_method) }}</div>
          </div>
        @endif
        @if(!empty($onboarding->payment_reference))
          <div class="completion-kpi">
            <div class="completion-kpi-label">Transaction Reference</div>
            <div class="completion-kpi-value">{{ $onboarding->payment_reference }}</div>
          </div>
        @endif
      </div>

      @if(!empty($onboarding->payment_proof_path))
        <div class="completion-proof-link">
          <a href="{{ asset('storage/' . $onboarding->payment_proof_path) }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-success rounded-pill">
            <i class="bi bi-receipt me-1"></i>View Submitted Payment Proof
          </a>
        </div>
      @endif
      @if(!empty($onboarding->payment_notes))
        <div class="alert alert-light border rounded-3 mt-3 mb-0">
          <div class="fw-semibold small text-muted mb-1">Payment Notes</div>
          <div>{{ $onboarding->payment_notes }}</div>
        </div>
      @endif

      <div class="completion-map-card">
        <div class="completion-map-header">
          <div class="completion-map-title"><i class="bi bi-geo-alt-fill me-1 text-success"></i>Property Location</div>
          @if($mapOpenUrl)
            <a href="{{ $mapOpenUrl }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-success rounded-pill">
              <i class="bi bi-box-arrow-up-right me-1"></i>Open in Maps
            </a>
          @endif
        </div>
        <div class="small text-muted mb-2">
          {{ $propertyAddress !== '' ? $propertyAddress : ($property->name ?? 'Property location unavailable') }}
        </div>
        @if($mapEmbedUrl)
          <div class="ratio ratio-16x9 completion-map-frame">
            <iframe
              src="{{ $mapEmbedUrl }}"
              loading="lazy"
              referrerpolicy="no-referrer-when-downgrade"
              allowfullscreen
              title="Property location map"
            ></iframe>
          </div>
        @else
          <div class="alert alert-warning rounded-3 mb-0">
            <i class="bi bi-exclamation-circle me-2"></i>Map preview is unavailable because this property has no saved location yet.
          </div>
        @endif
      </div>

      <div class="alert alert-success rounded-3 mt-3 mb-0">
        <i class="bi bi-info-circle me-2"></i>
        Next step: coordinate your exact arrival time with your landlord before check-in.
      </div>
    </div>
  @endif


  <!-- Uploaded Documents (if any) -->
  @if($onboarding->uploaded_documents && count($onboarding->uploaded_documents) > 0 && in_array($onboarding->status, ['pending', 'completed'], true))
    <div class="content-card">
      <h4 class="mb-4">
        <i class="bi bi-file-check me-2" style="color: var(--brand);"></i>
        Uploaded Documents
        @if($onboarding->status === 'pending')
          <span class="badge rounded-pill text-bg-warning ms-2" style="font-size: 0.75rem;">
            <i class="bi bi-hourglass-split me-1"></i>Under Review
          </span>
        @elseif($onboarding->status !== 'pending')
          <span class="badge rounded-pill text-bg-success ms-2" style="font-size: 0.75rem;">
            <i class="bi bi-check-circle me-1"></i>Approved
          </span>
        @endif
      </h4>

      @if($onboarding->status === 'pending')
        <div class="alert alert-info rounded-3 mb-4">
          <i class="bi bi-info-circle me-2"></i>
          Your documents have been uploaded and are currently under review by the landlord. You will be notified once the review is complete.
        </div>
      @endif

      <div class="row g-3">
        @foreach($onboarding->uploaded_documents as $index => $doc)
          <div class="col-12 col-sm-6 col-lg-4">
            <div class="document-card">
              @php
                $fileName = basename($doc);
                $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                $fileSize = Storage::disk('public')->size($doc);
                $fileSizeFormatted = $fileSize < 1024 ? $fileSize . ' B' : ($fileSize < 1048576 ? round($fileSize / 1024, 1) . ' KB' : round($fileSize / 1048576, 1) . ' MB');
              @endphp

              <div class="mb-2">
                @if(in_array($fileExtension, ['jpg', 'jpeg', 'png', 'gif']))
                  <i class="bi bi-image" style="font-size: 2rem; color: var(--brand);"></i>
                @elseif($fileExtension === 'pdf')
                  <i class="bi bi-file-pdf" style="font-size: 2rem; color: #dc2626;"></i>
                @else
                  <i class="bi bi-file-earmark" style="font-size: 2rem; color: #3b82f6;"></i>
                @endif
              </div>

              <h6 class="fw-600 document-file-name" title="{{ $fileName }}">{{ $fileName }}</h6>
              <p class="text-muted small mb-3">{{ $fileSizeFormatted }}</p>

              <div class="d-flex gap-2 justify-content-center">
                <a href="{{ route('documents.view', ['onboarding' => $onboarding->id, 'filename' => $fileName]) }}" target="_blank" class="btn btn-sm btn-brand rounded-pill px-3">
                  <i class="bi bi-eye me-1"></i>View
                </a>
                <a href="{{ route('documents.view', ['onboarding' => $onboarding->id, 'filename' => $fileName]) }}?download=1" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                  <i class="bi bi-download me-1"></i>Download
                </a>
              </div>
            </div>
          </div>
        @endforeach
      </div>
    </div>
  @endif
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
  document.addEventListener('DOMContentLoaded', function () {
    const docsForm = document.getElementById('onboardingDocumentsForm');
    const docsNextBtn = document.getElementById('onboardingDocsNextBtn');
    const contractForm = document.getElementById('onboardingContractForm');
    const signatureInput = document.getElementById('contractSignatureData');
    const signatureNameInput = document.getElementById('contractSignatureName');
    const signaturePreviewImage = document.getElementById('signaturePreviewImage');
    const signaturePreviewPlaceholder = document.getElementById('signaturePreviewPlaceholder');
    const signatureStatusText = document.getElementById('signatureStatusText');
    const signSubmitBtn = document.getElementById('signContractSubmitBtn');
    const agreeCheckbox = document.getElementById('agree');
    const clearSavedSignatureBtn = document.getElementById('clearSavedSignatureBtn');
    const openSignatureModalBtn = document.getElementById('openSignatureModalBtn');
    const uploadSignatureBtn = document.getElementById('uploadSignatureBtn');
    const uploadSignatureInput = document.getElementById('uploadSignatureInput');
    const signatureModalEl = document.getElementById('signatureCanvasModal');
    const saveSignatureBtn = document.getElementById('saveSignatureBtn');
    const clearSignatureCanvasBtn = document.getElementById('clearSignatureCanvasBtn');
    const signatureCanvas = document.getElementById('signatureCanvas');
    const signatureOrientationHint = document.getElementById('signatureOrientationHint');
    const paymentForm = document.getElementById('onboardingPaymentForm');
    const paymentMethodInputs = Array.from(document.querySelectorAll('input[name="payment_method"]'));
    const paymentMethodCards = Array.from(document.querySelectorAll('.payment-method-card'));
    const paymentPanels = Array.from(document.querySelectorAll('[data-payment-panel]'));
    const onlinePaymentFields = document.getElementById('onlinePaymentFields');
    const paymentReferenceInput = document.getElementById('paymentReferenceInput');
    const paymentReferenceHelp = document.getElementById('paymentReferenceHelp');
    const paymentProofInput = document.getElementById('paymentProofInput');
    const signatureModal = signatureModalEl ? bootstrap.Modal.getOrCreateInstance(signatureModalEl) : null;
    let signatureCtx = null;
    let isDrawing = false;
    let hasStroke = false;
    let orientationLockActive = false;
    const studentPrintedName = @json((string) (auth()->user()->name ?? ''));

    const syncNextButtonState = function () {
      if (!docsForm || !docsNextBtn) return;

      const inputs = Array.from(docsForm.querySelectorAll('.onb-doc-input[required]'));
      if (!inputs.length) {
        docsNextBtn.disabled = true;
        return;
      }

      const allSelected = inputs.every(function (input) {
        return input.files && input.files.length > 0;
      });

      docsNextBtn.disabled = !allSelected;
    };

    document.querySelectorAll('.onb-doc-input').forEach(function (input) {
      input.addEventListener('change', function () {
        const target = document.querySelector('[data-file-name-for="' + input.id + '"]');
        if (!target) return;

        const file = input.files && input.files[0] ? input.files[0] : null;
        target.textContent = file ? file.name : 'No file selected';
        target.classList.toggle('text-muted', !file);
        target.classList.toggle('text-success', !!file);

        syncNextButtonState();
      });
    });

    syncNextButtonState();

    const drawSignatureGuide = function (width, height) {
      if (!signatureCtx) return;

      const baselineY = Math.max(44, Math.floor(height - 72));
      const lineStartX = Math.max(20, Math.floor(width * 0.2));
      const lineEndX = Math.min(width - 20, Math.floor(width * 0.8));

      signatureCtx.lineWidth = 1;
      signatureCtx.strokeStyle = 'rgba(71, 85, 105, 0.5)';
      signatureCtx.beginPath();
      signatureCtx.moveTo(lineStartX, baselineY);
      signatureCtx.lineTo(lineEndX, baselineY);
      signatureCtx.stroke();

      if (!studentPrintedName) return;

      signatureCtx.textAlign = 'center';
      signatureCtx.textBaseline = 'top';
      signatureCtx.fillStyle = 'rgba(71, 85, 105, 0.92)';
      signatureCtx.font = '500 20px "Poppins", "Segoe UI", sans-serif';
      signatureCtx.fillText(studentPrintedName, Math.floor(width / 2), baselineY + 8);

      signatureCtx.fillStyle = 'rgba(100, 116, 139, 0.95)';
      signatureCtx.font = '12px "Poppins", "Segoe UI", sans-serif';
      signatureCtx.fillText('Signature over Printed name', Math.floor(width / 2), baselineY + 36);
    };

    const resizeSignatureCanvas = function () {
      if (!signatureCanvas) return;
      const ratio = Math.max(window.devicePixelRatio || 1, 1);
      const rect = signatureCanvas.getBoundingClientRect();
      signatureCanvas.width = Math.floor(rect.width * ratio);
      signatureCanvas.height = Math.floor(rect.height * ratio);
      signatureCtx = signatureCanvas.getContext('2d');
      signatureCtx.setTransform(ratio, 0, 0, ratio, 0, 0);
      signatureCtx.lineWidth = 2;
      signatureCtx.lineCap = 'round';
      signatureCtx.lineJoin = 'round';
      signatureCtx.strokeStyle = '#0f172a';
      signatureCtx.fillStyle = '#ffffff';
      signatureCtx.fillRect(0, 0, rect.width, rect.height);
      drawSignatureGuide(rect.width, rect.height);
      signatureCtx.lineWidth = 2;
      signatureCtx.lineCap = 'round';
      signatureCtx.lineJoin = 'round';
      signatureCtx.strokeStyle = '#0f172a';
      hasStroke = false;
    };

    const getCanvasPoint = function (event) {
      const rect = signatureCanvas.getBoundingClientRect();
      return {
        x: event.clientX - rect.left,
        y: event.clientY - rect.top,
      };
    };

    const syncContractSubmitState = function () {
      if (!signSubmitBtn) return;
      const hasSignature = !!(signatureInput && signatureInput.value);
      const agreed = !!(agreeCheckbox && agreeCheckbox.checked);
      signSubmitBtn.disabled = !(hasSignature && agreed);
    };

    const updateSignaturePreview = function () {
      const hasSignature = !!(signatureInput && signatureInput.value);
      if (signaturePreviewImage && signaturePreviewPlaceholder) {
        signaturePreviewImage.classList.toggle('d-none', !hasSignature);
        signaturePreviewPlaceholder.classList.toggle('d-none', hasSignature);
      }
      if (clearSavedSignatureBtn) {
        clearSavedSignatureBtn.disabled = !hasSignature;
      }
      if (signatureStatusText) {
        signatureStatusText.textContent = hasSignature
          ? 'Signature captured. You can now agree and submit the contract.'
          : 'Signature required before contract submission.';
        signatureStatusText.classList.toggle('text-success', hasSignature);
      }
      syncContractSubmitState();
    };

    const tryLandscapeForSignature = async function () {
      const isMobile = window.matchMedia('(max-width: 991.98px)').matches;
      if (!isMobile) return;

      if (signatureOrientationHint) {
        signatureOrientationHint.classList.remove('show');
      }

      try {
        if (!document.fullscreenElement) {
          if (signatureModalEl && signatureModalEl.requestFullscreen) {
            await signatureModalEl.requestFullscreen();
          } else if (document.documentElement.requestFullscreen) {
            await document.documentElement.requestFullscreen();
          }
        }

        if (screen.orientation && screen.orientation.lock) {
          await screen.orientation.lock('landscape');
          orientationLockActive = true;
        }
      } catch (error) {
        if (signatureOrientationHint) {
          signatureOrientationHint.classList.add('show');
        }
      }
    };

    const resetOrientationAfterSignature = async function () {
      try {
        if (orientationLockActive && screen.orientation && screen.orientation.unlock) {
          screen.orientation.unlock();
        }
      } catch (error) {
        // Ignore unlock failures.
      }
      orientationLockActive = false;

      try {
        if (document.fullscreenElement && document.exitFullscreen) {
          await document.exitFullscreen();
        }
      } catch (error) {
        // Ignore fullscreen exit failures.
      }
    };

    const cleanupSignatureModalBackdrop = function () {
      document.querySelectorAll('.modal-backdrop').forEach(function (backdrop) {
        backdrop.remove();
      });
      document.body.classList.remove('modal-open');
      document.body.style.removeProperty('padding-right');
      document.body.style.removeProperty('overflow');
      document.body.style.removeProperty('touch-action');
    };

    if (signatureModalEl && signatureCanvas) {
      if (openSignatureModalBtn) {
        openSignatureModalBtn.addEventListener('click', function () {
          void tryLandscapeForSignature();
        });
      }

      signatureModalEl.addEventListener('shown.bs.modal', function () {
        resizeSignatureCanvas();
        void tryLandscapeForSignature();
      });

      signatureModalEl.addEventListener('hidden.bs.modal', function () {
        void resetOrientationAfterSignature();
        cleanupSignatureModalBackdrop();
      });

      signatureCanvas.addEventListener('pointerdown', function (event) {
        if (!signatureCtx) return;
        isDrawing = true;
        hasStroke = true;
        const point = getCanvasPoint(event);
        signatureCtx.beginPath();
        signatureCtx.moveTo(point.x, point.y);
      });

      signatureCanvas.addEventListener('pointermove', function (event) {
        if (!isDrawing || !signatureCtx) return;
        const point = getCanvasPoint(event);
        signatureCtx.lineTo(point.x, point.y);
        signatureCtx.stroke();
      });

      const stopDrawing = function () {
        isDrawing = false;
      };

      signatureCanvas.addEventListener('pointerup', stopDrawing);
      signatureCanvas.addEventListener('pointerleave', stopDrawing);

      if (clearSignatureCanvasBtn) {
        clearSignatureCanvasBtn.addEventListener('click', function () {
          resizeSignatureCanvas();
        });
      }

      if (saveSignatureBtn) {
        saveSignatureBtn.addEventListener('click', function () {
          if (!signatureCanvas || !hasStroke) {
            alert('Please draw your signature first.');
            return;
          }
          const data = signatureCanvas.toDataURL('image/png');
          if (signatureInput) signatureInput.value = data;
          if (signatureNameInput) signatureNameInput.value = studentPrintedName;
          if (signaturePreviewImage) signaturePreviewImage.src = data;
          updateSignaturePreview();
          if (signatureModal) signatureModal.hide();

          // Run cleanup after hide call to avoid stuck overlay/scroll lock on mobile.
          setTimeout(function () {
            void resetOrientationAfterSignature();
            cleanupSignatureModalBackdrop();
          }, 120);
        });
      }
    }

    if (clearSavedSignatureBtn) {
      clearSavedSignatureBtn.addEventListener('click', function () {
        if (signatureInput) signatureInput.value = '';
        if (signatureNameInput) signatureNameInput.value = '';
        if (signaturePreviewImage) signaturePreviewImage.removeAttribute('src');
        if (uploadSignatureInput) uploadSignatureInput.value = '';
        updateSignaturePreview();
      });
    }

    if (uploadSignatureBtn && uploadSignatureInput) {
      uploadSignatureBtn.addEventListener('click', function () {
        uploadSignatureInput.click();
      });

      uploadSignatureInput.addEventListener('change', function () {
        const file = uploadSignatureInput.files && uploadSignatureInput.files[0] ? uploadSignatureInput.files[0] : null;
        if (!file) return;

        const isImage = /^image\//.test(file.type);
        if (!isImage) {
          alert('Please upload an image file for your signature.');
          uploadSignatureInput.value = '';
          return;
        }

        if (file.size > 5 * 1024 * 1024) {
          alert('Signature image is too large. Please upload a file smaller than 5MB.');
          uploadSignatureInput.value = '';
          return;
        }

        const reader = new FileReader();
        reader.onload = function (event) {
          const dataUrl = typeof event.target.result === 'string' ? event.target.result : '';
          if (!dataUrl) {
            alert('Unable to read the uploaded signature file.');
            return;
          }
          if (signatureInput) signatureInput.value = dataUrl;
          if (signatureNameInput) signatureNameInput.value = studentPrintedName;
          if (signaturePreviewImage) signaturePreviewImage.src = dataUrl;
          updateSignaturePreview();
        };
        reader.readAsDataURL(file);
      });
    }

    if (agreeCheckbox) {
      agreeCheckbox.addEventListener('change', syncContractSubmitState);
    }

    if (contractForm) {
      contractForm.addEventListener('submit', function (event) {
        if (!signatureInput || !signatureInput.value) {
          event.preventDefault();
          alert('Please capture your signature before signing the contract.');
          return;
        }
        if (signatureNameInput && !signatureNameInput.value) {
          signatureNameInput.value = studentPrintedName;
        }
        if (!agreeCheckbox || !agreeCheckbox.checked) {
          event.preventDefault();
          alert('Please agree to the terms and conditions before submitting.');
        }
      });
    }

    const syncPaymentMethodState = function () {
      if (!paymentMethodInputs.length) return;

      let selectedMethod = '';
      paymentMethodInputs.forEach(function (input) {
        if (input.checked) {
          selectedMethod = input.value;
        }
      });

      paymentMethodCards.forEach(function (card) {
        const input = card.querySelector('input[name="payment_method"]');
        card.classList.toggle('selected', !!(input && input.checked));
      });

      paymentPanels.forEach(function (panel) {
        panel.classList.toggle('d-none', panel.dataset.paymentPanel !== selectedMethod);
      });

      const needsOnlineProof = selectedMethod === 'bank' || selectedMethod === 'gcash';
      if (onlinePaymentFields) {
        onlinePaymentFields.classList.toggle('d-none', !needsOnlineProof);
      }
      if (paymentReferenceInput) {
        paymentReferenceInput.required = needsOnlineProof;
        paymentReferenceInput.disabled = !needsOnlineProof;
        if (!needsOnlineProof) {
          paymentReferenceInput.value = '';
        }
      }
      if (paymentProofInput) {
        paymentProofInput.required = needsOnlineProof;
        paymentProofInput.disabled = !needsOnlineProof;
        if (!needsOnlineProof) {
          paymentProofInput.value = '';
        }
      }
      if (paymentReferenceHelp) {
        paymentReferenceHelp.textContent = needsOnlineProof
          ? 'Required for selected method. Enter the transaction reference number.'
          : 'Not required for Cash payments.';
      }
    };

    paymentMethodInputs.forEach(function (input) {
      input.addEventListener('change', syncPaymentMethodState);
    });

    if (paymentForm) {
      paymentForm.addEventListener('submit', function (event) {
        const selected = paymentMethodInputs.find(function (input) {
          return input.checked;
        });

        if (!selected) {
          event.preventDefault();
          alert('Please select a payment method.');
          return;
        }

        const selectedMethod = selected.value;
        const needsOnlineProof = selectedMethod === 'bank' || selectedMethod === 'gcash';
        const hasReference = paymentReferenceInput && paymentReferenceInput.value.trim().length > 0;
        const hasProofFile = paymentProofInput && paymentProofInput.files && paymentProofInput.files.length > 0;

        if (needsOnlineProof && !hasReference) {
          event.preventDefault();
          alert('Please enter your transaction reference number.');
          return;
        }

        if (needsOnlineProof && !hasProofFile) {
          event.preventDefault();
          alert('Please upload your payment proof for this payment method.');
        }
      });
    }

    syncPaymentMethodState();
    updateSignaturePreview();
  });
</script>
@endpush

@endsection