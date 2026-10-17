@extends('layouts.student_dashboard')

@section('title', 'Complete Your Move-in')

@push('styles')
<style>
  :root {
    --brand: #14532d;
    --brand-dark: #166534;
    --premium-emerald: #10b981;
    --premium-emerald-deep: #047857;
    --ink: #0f172a;
    --muted: #64748b;
    --line: #e2e8f0;
    --shell: #f8fafc;
  }

  .movein-wrap {
    width: min(840px, 100%);
    margin: 0 auto;
    padding: 0.5rem 0.25rem 2.5rem;
  }

  /* Accommodation Context Header */
  .accommodation-banner {
    background: #ffffff;
    border: 1px solid var(--line);
    border-radius: 1.25rem;
    box-shadow: 0 4px 16px rgba(15, 23, 42, 0.04);
    padding: 1.25rem 1.5rem;
    margin-bottom: 1.5rem;
  }

  .accommodation-badge {
    font-size: 0.72rem;
    font-weight: 800;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: var(--premium-emerald-deep);
    background: rgba(16, 185, 129, 0.12);
    padding: 0.25rem 0.65rem;
    border-radius: 999px;
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
  }

  .accommodation-title {
    font-size: 1.25rem;
    font-weight: 800;
    color: var(--ink);
    margin-bottom: 0.25rem;
    letter-spacing: -0.02em;
  }

  .accommodation-meta {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 0.75rem 1.25rem;
    color: var(--muted);
    font-size: 0.86rem;
  }

  .accommodation-meta-item {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
  }

  /* Setup Shell & Question Panel */
  .setup-shell {
    border: 1px solid rgba(226, 232, 240, 0.9);
    border-radius: 1.5rem;
    background: #ffffff;
    box-shadow: 0 20px 45px -12px rgba(15, 23, 42, 0.08);
    padding: 2.25rem 2rem;
    margin-bottom: 1.5rem;
  }

  .question-kicker {
    font-size: 0.75rem;
    font-weight: 800;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    color: var(--premium-emerald-deep);
    margin-bottom: 0.45rem;
  }

  .question-title {
    font-family: 'Bricolage Grotesque', 'Manrope', system-ui, sans-serif;
    font-size: clamp(1.4rem, 2.6vw, 1.8rem);
    font-weight: 800;
    letter-spacing: -0.025em;
    line-height: 1.25;
    color: var(--ink);
    margin-bottom: 0.35rem;
  }

  .question-subtitle {
    font-size: 0.92rem;
    color: var(--muted);
    margin-bottom: 1.75rem;
    line-height: 1.5;
  }

  /* Form Controls */
  .form-label {
    font-size: 0.86rem;
    font-weight: 700;
    color: #334155;
    margin-bottom: 0.4rem;
  }

  .form-control, .form-select {
    border: 1.5px solid var(--line);
    border-radius: 0.75rem;
    padding: 0.65rem 0.95rem;
    font-size: 0.95rem;
    color: var(--ink);
    background-color: #ffffff;
    transition: border-color 0.18s ease, box-shadow 0.18s ease;
  }

  .form-control:focus, .form-select:focus {
    border-color: var(--premium-emerald);
    box-shadow: 0 0 0 3.5px rgba(16, 185, 129, 0.15);
    outline: 0;
  }

  .field-hint {
    font-size: 0.78rem;
    color: var(--muted);
    margin-top: 0.35rem;
    line-height: 1.4;
  }

  /* Document Upload Field */
  .upload-dropzone {
    border: 2px dashed #cbd5e1;
    border-radius: 1rem;
    padding: 1.5rem;
    text-align: center;
    background: #f8fafc;
    transition: border-color 0.2s ease, background 0.2s ease;
    cursor: pointer;
    position: relative;
  }

  .upload-dropzone:hover, .upload-dropzone:focus-within {
    border-color: var(--premium-emerald);
    background: #f0fdf4;
  }

  .upload-dropzone-icon {
    font-size: 2rem;
    color: var(--premium-emerald-deep);
    margin-bottom: 0.5rem;
  }

  .upload-file-tag {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    background: #ffffff;
    border: 1px solid #cbd5e1;
    border-radius: 0.65rem;
    padding: 0.45rem 0.75rem;
    font-size: 0.84rem;
    font-weight: 600;
    color: var(--ink);
    margin-top: 0.75rem;
    max-width: 100%;
  }

  .upload-file-tag .bi-x-circle {
    cursor: pointer;
    color: #94a3b8;
    transition: color 0.15s;
  }

  .upload-file-tag .bi-x-circle:hover {
    color: #ef4444;
  }

  /* Payment Method Options */
  .payment-method-card {
    border: 1.5px solid var(--line);
    border-radius: 1rem;
    padding: 1rem 1.25rem;
    background: #ffffff;
    cursor: pointer;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    gap: 0.75rem;
  }

  .payment-method-card:hover {
    border-color: #94a3b8;
    background: #f8fafc;
  }

  .payment-method-card.active {
    border-color: var(--premium-emerald);
    background: #f0fdf4;
    box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.12);
  }

  /* Buttons */
  .btn-brand {
    padding: 0.75rem 1.6rem;
    font-weight: 700;
    font-size: 0.95rem;
    border-radius: 999px;
    border: 1px solid rgba(16, 185, 129, 0.9);
    background: linear-gradient(125deg, var(--premium-emerald) 0%, var(--premium-emerald-deep) 100%);
    color: #ecfdf5;
    box-shadow: 0 10px 24px rgba(4, 120, 87, 0.25);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    cursor: pointer;
    text-decoration: none;
    transition: transform 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
  }

  .btn-brand:hover:not(:disabled) {
    transform: translateY(-2px);
    background: linear-gradient(125deg, #0ea770 0%, #046246 100%);
    color: #ffffff;
    box-shadow: 0 14px 28px rgba(4, 120, 87, 0.35);
  }

  .btn-brand:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none !important;
    box-shadow: none !important;
  }

  .btn-prev {
    padding: 0.75rem 1.4rem;
    font-weight: 600;
    font-size: 0.92rem;
    border-radius: 999px;
    border: 1.5px solid #cbd5e1;
    background: #ffffff;
    color: #475569;
    box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.4rem;
    cursor: pointer;
    text-decoration: none;
  }

  .btn-prev:hover {
    background: #f8fafc;
    border-color: #94a3b8;
    color: var(--ink);
  }

  .signature-canvas-container {
    border: 1.5px dashed #cbd5e1;
    border-radius: 0.85rem;
    background: #ffffff;
    position: relative;
    overflow: hidden;
  }

  .signature-canvas-container canvas {
    display: block;
    width: 100%;
    height: 170px;
    cursor: crosshair;
    touch-action: none;
  }

  .digital-id-card {
    background: linear-gradient(135deg, #14532d 0%, #047857 100%);
    border-radius: 1.25rem;
    color: #ffffff;
    padding: 1.75rem;
    box-shadow: 0 15px 35px -8px rgba(4, 120, 87, 0.35);
    position: relative;
    overflow: hidden;
  }

  .digital-id-card::after {
    content: '';
    position: absolute;
    right: -20px;
    bottom: -20px;
    width: 180px;
    height: 180px;
    background: radial-gradient(circle, rgba(255,255,255,0.15) 0%, transparent 70%);
    border-radius: 50%;
    pointer-events: none;
  }

  @media (max-width: 575.98px) {
    .setup-shell {
      padding: 1.5rem 1.15rem;
      border-radius: 1.15rem;
    }
    .accommodation-banner {
      padding: 1rem 1.15rem;
      border-radius: 1rem;
    }
    .btn-brand, .btn-prev {
      width: 100%;
    }
    .question-actions {
      flex-direction: column-reverse;
      gap: 0.75rem;
    }
  }
</style>
@endpush

@section('content')
@php
  $status = (string) ($onboarding->status ?? 'pending');
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
  $checkInLabel = optional($booking->check_in)->format('M d, Y') ?: 'Pending';
  $checkOutLabel = optional($booking->check_out)->format('M d, Y') ?: 'Open Ended';
  $landlordProfile = optional($property->landlord)->landlordProfile;

  $preferredPaymentMethods = collect((array) ($landlordProfile?->preferred_payment_methods ?? []))
    ->map(fn ($m) => strtolower(trim((string) $m)))
    ->filter(fn ($m) => in_array($m, ['bank', 'gcash', 'cash'], true))
    ->unique()
    ->values();

  $hasBankDetails = filled($landlordProfile?->payment_bank_name)
    && filled($landlordProfile?->payment_account_name)
    && filled($landlordProfile?->payment_account_number);
  $hasGcashDetails = filled($landlordProfile?->payment_gcash_name)
    && (filled($landlordProfile?->payment_gcash_number) || filled($landlordProfile?->contact_number));

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

  $selectedPaymentMethod = strtolower((string) old('payment_method', $availablePaymentMethods->first() ?? 'gcash'));

  $statusDisplay = match($status) {
    'pending' => 'Requirements Pending',
    'documents_uploaded' => 'Contract Ready to Sign',
    'contract_signed' => 'Payment Required',
    'deposit_paid' => 'Payment Under Review',
    'completed' => 'Move-in Complete',
    default => ucfirst(str_replace('_', ' ', $status)),
  };

  $statusBadgeClass = match($status) {
    'completed' => 'bg-success text-white',
    'deposit_paid' => 'bg-warning-subtle text-warning-emphasis border border-warning-subtle',
    'contract_signed' => 'bg-primary-subtle text-primary border border-primary-subtle',
    'documents_uploaded' => 'bg-info-subtle text-info-emphasis border border-info-subtle',
    default => 'bg-secondary-subtle text-secondary border border-secondary-subtle',
  };
@endphp

<div class="movein-wrap">
  {{-- Accommodation Context Header --}}
  <div class="accommodation-banner">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
      <div class="accommodation-badge">
        <i class="bi bi-door-open"></i> Room {{ $room->room_number }} · {{ ucfirst((string) ($booking->occupancy_mode ?? 'Solo')) }}
      </div>
      <span class="badge rounded-pill {{ $statusBadgeClass }} px-3 py-1">
        {{ $statusDisplay }}
      </span>
    </div>

    <h2 class="accommodation-title">{{ $property->name }}</h2>

    <div class="accommodation-meta">
      <span class="accommodation-meta-item">
        <i class="bi bi-geo-alt"></i> {{ $property->address }}
      </span>
      <span class="accommodation-meta-item">
        <i class="bi bi-calendar-check"></i> {{ $checkInLabel }} - {{ $checkOutLabel }}
      </span>
      <span class="accommodation-meta-item">
        <i class="bi bi-cash-stack"></i> Total Due: <strong>₱{{ number_format($effectiveDepositAmount, 2) }}</strong>
      </span>
    </div>
  </div>

  {{-- Session Alerts --}}
  @if(session('success'))
    <div class="alert alert-success rounded-4 d-flex align-items-center gap-2 mb-4" role="alert">
      <i class="bi bi-check-circle-fill text-success fs-5"></i>
      <div>{{ session('success') }}</div>
    </div>
  @endif

  @if(session('error'))
    <div class="alert alert-danger rounded-4 d-flex align-items-center gap-2 mb-4" role="alert">
      <i class="bi bi-exclamation-triangle-fill text-danger fs-5"></i>
      <div>{{ session('error') }}</div>
    </div>
  @endif

  @if($errors->any())
    <div class="alert alert-danger rounded-4 mb-4">
      <div class="fw-bold mb-1"><i class="bi bi-exclamation-circle me-1"></i>Please check the following errors:</div>
      <ul class="mb-0 ps-3 small">
        @foreach($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  {{-- STAGE 1: DOCUMENTS UPLOAD (Status: pending) --}}
  @if($status === 'pending')
    <div class="setup-shell">
      <div class="question-kicker">Stage 1 of 3 · Move-in Requirements</div>
      <h1 class="question-title">Submit your move-in requirements</h1>
      <p class="question-subtitle">
        Please upload the required verification documents requested by your landlord. These documents will be reviewed before your rental contract is issued.
      </p>

      <form action="{{ route('student.onboarding.upload_documents', $onboarding) }}" method="POST" enctype="multipart/form-data" id="studentDocsForm">
        @csrf

        <div class="mb-4">
          <label class="form-label" for="documents">
            Verification Documents <span class="text-danger">*</span>
          </label>
          <div class="upload-dropzone" id="dropzoneBox" onclick="document.getElementById('documentsInput').click()">
            <div class="upload-dropzone-icon">
              <i class="bi bi-cloud-arrow-up"></i>
            </div>
            <div class="fw-bold text-dark fs-6 mb-1">Click or drag & drop files here to upload</div>
            <div class="small text-muted mb-2">Accepted formats: PDF, JPG, JPEG, PNG (Max 5MB per file)</div>
            <input type="file" name="documents[]" id="documentsInput" class="d-none" multiple required accept=".pdf,.jpg,.jpeg,.png">
            <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 mt-1">
              <i class="bi bi-folder2-open me-1"></i>Browse Files
            </button>
            <div id="fileListContainer" class="d-flex flex-wrap gap-2 justify-content-center mt-3"></div>
          </div>
          <div class="field-hint">
            <i class="bi bi-info-circle me-1"></i>Ensure your documents are clear and legible (e.g. Student ID, Proof of Enrollment / Registration).
          </div>
        </div>

        <div class="d-flex justify-content-end gap-2 pt-3 border-top">
          <button type="submit" class="btn-brand" id="submitDocsBtn">
            <span>Submit Requirements</span>
            <i class="bi bi-arrow-right"></i>
          </button>
        </div>
      </form>
    </div>
  @endif

  {{-- STAGE 2: CONTRACT SIGNING (Status: documents_uploaded) --}}
  @if($status === 'documents_uploaded')
    <div class="setup-shell">
      <div class="question-kicker">Stage 2 of 3 · Rental Agreement</div>
      <h1 class="question-title">Review &amp; sign your lease contract</h1>
      <p class="question-subtitle">
        Carefully read through the lease agreement terms and house rules. Provide your e-signature below to confirm your move-in agreement.
      </p>

      {{-- Legal Contract Document Component --}}
      <div class="mb-4">
        <x-onboarding.contract-document :onboarding="$onboarding" />
      </div>

      <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4 p-3 rounded-3 bg-light border">
        <div class="small text-muted">
          <i class="bi bi-shield-lock me-1 text-success"></i>This contract is legally binding upon signing by both student and landlord.
        </div>
        <a href="{{ route('student.onboarding.contract_pdf', ['onboarding' => $onboarding, 'download' => 1]) }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
          <i class="bi bi-download me-1"></i>Download PDF Copy
        </a>
      </div>

      <form action="{{ route('student.onboarding.sign_contract', $onboarding) }}" method="POST" id="studentContractForm">
        @csrf
        <input type="hidden" name="signature_data" id="signatureDataInput">

        <div class="mb-4">
          <label class="form-label">
            Your E-Signature <span class="text-danger">*</span>
          </label>
          <div class="field-hint mb-2">Draw your signature inside the box using your mouse, trackpad, or finger.</div>

          <div class="signature-canvas-container mb-2">
            <canvas id="signCanvas"></canvas>
            <div id="sigPlaceholder" class="position-absolute top-50 start-50 translate-middle text-muted pe-none small">
              <i class="bi bi-pen me-1"></i>Sign here
            </div>
          </div>

          <div class="d-flex align-items-center justify-content-between gap-2">
            <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3" id="clearSigBtn">
              <i class="bi bi-eraser me-1"></i>Clear & Redraw
            </button>
            <span class="small text-muted" id="sigStatusText">No signature captured yet</span>
          </div>
        </div>

        <div class="mb-4">
          <label class="form-label" for="signatureName">
            Printed Full Name <span class="text-danger">*</span>
          </label>
          <input type="text" name="signature_name" id="signatureName" class="form-control" value="{{ old('signature_name', $booking->student->full_name) }}" required>
          <div class="field-hint">Must match your official registered name.</div>
        </div>

        <div class="mb-4 p-3 rounded-3 border bg-light">
          <div class="form-check">
            <input class="form-check-input" type="checkbox" id="acceptContractTerms" required>
            <label class="form-check-label small fw-semibold text-dark" for="acceptContractTerms">
              I have read, understood, and accept all the terms, policies, and house rules stated in this lease agreement.
            </label>
          </div>
        </div>

        <div class="d-flex justify-content-between align-items-center gap-2 pt-3 border-top question-actions">
          <a href="{{ route('student.onboarding.index') }}" class="btn-prev">
            <i class="bi bi-arrow-left"></i>
            <span>Cancel</span>
          </a>
          <button type="submit" class="btn-brand" id="signContractBtn">
            <span>Sign Contract &amp; Continue</span>
            <i class="bi bi-check2-circle"></i>
          </button>
        </div>
      </form>
    </div>
  @endif

  {{-- STAGE 3: MOVE-IN PAYMENT (Status: contract_signed) --}}
  @if($status === 'contract_signed')
    <div class="setup-shell">
      <div class="question-kicker">Stage 3 of 3 · Move-in Payment</div>
      <h1 class="question-title">Submit your move-in payment</h1>
      <p class="question-subtitle">
        Your lease agreement has been signed. To finalize your move-in, please complete the initial payment below.
      </p>

      {{-- Payment Breakdown Card --}}
      <div class="p-3 rounded-3 border bg-light mb-4">
        <div class="d-flex justify-content-between align-items-center py-1 border-bottom">
          <span class="text-muted small">Monthly Security Deposit</span>
          <span class="fw-semibold text-dark">₱{{ number_format($depositBaseAmount, 2) }}</span>
        </div>
        @if($selectedAdvanceAmount > 0)
          <div class="d-flex justify-content-between align-items-center py-1 border-bottom">
            <span class="text-muted small">Advance Rent (1 Month)</span>
            <span class="fw-semibold text-dark">₱{{ number_format($selectedAdvanceAmount, 2) }}</span>
          </div>
        @endif
        <div class="d-flex justify-content-between align-items-center pt-2">
          <span class="fw-bold text-dark">Total Amount Due</span>
          <span class="fw-bold text-success fs-5">₱{{ number_format($effectiveDepositAmount, 2) }}</span>
        </div>
      </div>

      <form action="{{ route('student.onboarding.pay_deposit', $onboarding) }}" method="POST" enctype="multipart/form-data" id="studentPaymentForm">
        @csrf

        {{-- Payment Methods --}}
        <div class="mb-4">
          <label class="form-label">
            Select Payment Method <span class="text-danger">*</span>
          </label>
          <div class="row g-2">
            @if($availablePaymentMethods->contains('gcash'))
              <div class="col-12 col-sm-4">
                <label class="payment-method-card w-100 {{ $selectedPaymentMethod === 'gcash' ? 'active' : '' }}" onclick="selectMethod('gcash')">
                  <input type="radio" name="payment_method" value="gcash" class="d-none" {{ $selectedPaymentMethod === 'gcash' ? 'checked' : '' }}>
                  <i class="bi bi-wallet2 fs-4 text-primary"></i>
                  <div>
                    <div class="fw-bold text-dark small">GCash</div>
                    <div class="text-muted" style="font-size: 0.72rem;">E-Wallet transfer</div>
                  </div>
                </label>
              </div>
            @endif

            @if($availablePaymentMethods->contains('bank'))
              <div class="col-12 col-sm-4">
                <label class="payment-method-card w-100 {{ $selectedPaymentMethod === 'bank' ? 'active' : '' }}" onclick="selectMethod('bank')">
                  <input type="radio" name="payment_method" value="bank" class="d-none" {{ $selectedPaymentMethod === 'bank' ? 'checked' : '' }}>
                  <i class="bi bi-bank fs-4 text-success"></i>
                  <div>
                    <div class="fw-bold text-dark small">Bank Transfer</div>
                    <div class="text-muted" style="font-size: 0.72rem;">Online deposit</div>
                  </div>
                </label>
              </div>
            @endif

            @if($availablePaymentMethods->contains('cash'))
              <div class="col-12 col-sm-4">
                <label class="payment-method-card w-100 {{ $selectedPaymentMethod === 'cash' ? 'active' : '' }}" onclick="selectMethod('cash')">
                  <input type="radio" name="payment_method" value="cash" class="d-none" {{ $selectedPaymentMethod === 'cash' ? 'checked' : '' }}>
                  <i class="bi bi-cash-stack fs-4 text-secondary"></i>
                  <div>
                    <div class="fw-bold text-dark small">Cash on Arrival</div>
                    <div class="text-muted" style="font-size: 0.72rem;">Pay at house</div>
                  </div>
                </label>
              </div>
            @endif
          </div>
        </div>

        {{-- Landlord Payment Details Panes --}}
        @if($hasGcashDetails)
          <div class="payment-details-pane p-3 rounded-3 border bg-light mb-4 {{ $selectedPaymentMethod === 'gcash' ? '' : 'd-none' }}" id="gcashPane">
            <div class="fw-bold text-dark small mb-2"><i class="bi bi-info-circle me-1 text-primary"></i>Landlord GCash Details</div>
            <div class="d-flex justify-content-between py-1 border-bottom">
              <span class="text-muted small">Account Name</span>
              <span class="fw-semibold text-dark">{{ $landlordProfile->payment_gcash_name }}</span>
            </div>
            <div class="d-flex justify-content-between py-1">
              <span class="text-muted small">GCash Number</span>
              <span class="fw-bold text-primary">{{ $landlordProfile->payment_gcash_number }}</span>
            </div>
            @if(!empty($landlordProfile->payment_gcash_qr_path) && file_exists_any($landlordProfile->payment_gcash_qr_path))
              <div class="mt-2 text-center pt-2 border-top">
                <div class="small text-muted mb-1">Scan QR Code:</div>
                <img src="{{ file_download_url($landlordProfile->payment_gcash_qr_path) }}" alt="GCash QR Code" class="img-fluid rounded border" style="max-height: 180px;">
              </div>
            @endif
          </div>
        @endif

        @if($hasBankDetails)
          <div class="payment-details-pane p-3 rounded-3 border bg-light mb-4 {{ $selectedPaymentMethod === 'bank' ? '' : 'd-none' }}" id="bankPane">
            <div class="fw-bold text-dark small mb-2"><i class="bi bi-info-circle me-1 text-success"></i>Landlord Bank Account Details</div>
            <div class="d-flex justify-content-between py-1 border-bottom">
              <span class="text-muted small">Bank Name</span>
              <span class="fw-semibold text-dark">{{ $landlordProfile->payment_bank_name }}</span>
            </div>
            <div class="d-flex justify-content-between py-1 border-bottom">
              <span class="text-muted small">Account Name</span>
              <span class="fw-semibold text-dark">{{ $landlordProfile->payment_account_name }}</span>
            </div>
            <div class="d-flex justify-content-between py-1">
              <span class="text-muted small">Account Number</span>
              <span class="fw-bold text-dark">{{ $landlordProfile->payment_account_number }}</span>
            </div>
          </div>
        @endif

        <div class="payment-details-pane p-3 rounded-3 border bg-light mb-4 {{ $selectedPaymentMethod === 'cash' ? '' : 'd-none' }}" id="cashPane">
          <div class="d-flex align-items-center gap-2">
            <i class="bi bi-info-circle-fill text-warning fs-5"></i>
            <div class="small text-muted">
              You selected <strong>Cash on Arrival</strong>. Please ensure you prepare the exact move-in amount of <strong>₱{{ number_format($effectiveDepositAmount, 2) }}</strong> to pay the landlord upon physical check-in.
            </div>
          </div>
        </div>

        {{-- Online Reference & Proof (shown for Bank & GCash) --}}
        <div id="onlineProofFields" class="{{ $selectedPaymentMethod === 'cash' ? 'd-none' : '' }}">
          <div class="mb-3">
            <label class="form-label" for="paymentReference">
              Transaction Reference Number <span class="text-danger">*</span>
            </label>
            <input type="text" name="payment_reference" id="paymentReference" class="form-control" placeholder="e.g. 1029384756" value="{{ old('payment_reference') }}">
            <div class="field-hint">Enter the reference or confirmation number from your receipt.</div>
          </div>

          <div class="mb-4">
            <label class="form-label" for="paymentProof">
              Proof of Payment Screenshot / Receipt <span class="text-danger">*</span>
            </label>
            <input type="file" name="payment_proof" id="paymentProof" class="form-control" accept=".jpg,.jpeg,.png,.pdf">
            <div class="field-hint">Upload a screenshot or photo of your payment transaction (Max 5MB).</div>
          </div>
        </div>

        <div class="mb-4">
          <label class="form-label" for="paymentNotes">
            Additional Payment Notes (Optional)
          </label>
          <textarea name="payment_notes" id="paymentNotes" rows="2" class="form-control" placeholder="Any message or details for the landlord...">{{ old('payment_notes') }}</textarea>
        </div>

        <div class="d-flex justify-content-between align-items-center gap-2 pt-3 border-top question-actions">
          <a href="{{ route('student.onboarding.index') }}" class="btn-prev">
            <i class="bi bi-arrow-left"></i>
            <span>Back</span>
          </a>
          <button type="submit" class="btn-brand" id="submitPaymentBtn">
            <span>Submit Move-in Payment</span>
            <i class="bi bi-arrow-right"></i>
          </button>
        </div>
      </form>
    </div>
  @endif

  {{-- STAGE 4: PAYMENT UNDER REVIEW (Status: deposit_paid) --}}
  @if($status === 'deposit_paid')
    <div class="setup-shell text-center">
      <div class="mb-3">
        <div class="rounded-circle d-inline-flex align-items-center justify-content-center bg-warning-subtle text-warning-emphasis" style="width: 64px; height: 64px; font-size: 1.75rem;">
          <i class="bi bi-hourglass-split"></i>
        </div>
      </div>

      <div class="question-kicker">Move-in Status</div>
      <h1 class="question-title">Payment Submitted · Awaiting Landlord Confirmation</h1>
      <p class="question-subtitle mx-auto" style="max-width: 580px;">
        Thank you! Your move-in payment has been submitted. The landlord will verify your payment details and finalize your active tenancy.
      </p>

      <div class="p-3 rounded-3 border bg-light text-start mx-auto mb-4" style="max-width: 520px;">
        <div class="d-flex justify-content-between py-1 border-bottom">
          <span class="text-muted small">Payment Method</span>
          <span class="fw-semibold text-dark">{{ ucfirst((string) $onboarding->payment_method) }}</span>
        </div>
        @if($onboarding->payment_reference)
          <div class="d-flex justify-content-between py-1 border-bottom">
            <span class="text-muted small">Reference No.</span>
            <span class="fw-medium text-dark">{{ $onboarding->payment_reference }}</span>
          </div>
        @endif
        <div class="d-flex justify-content-between py-1 border-bottom">
          <span class="text-muted small">Amount Submitted</span>
          <span class="fw-bold text-success">₱{{ number_format($effectiveDepositAmount, 2) }}</span>
        </div>
        <div class="d-flex justify-content-between py-1">
          <span class="text-muted small">Status</span>
          <span class="badge bg-warning-subtle text-warning-emphasis rounded-pill px-2">Pending Landlord Review</span>
        </div>
      </div>

      <div class="d-flex justify-content-center gap-2">
        <a href="{{ route('student.onboarding.index') }}" class="btn btn-outline-secondary rounded-pill px-4">
          <i class="bi bi-arrow-left me-1"></i>Back to My Stays
        </a>
        <a href="{{ route('student.onboarding.contract_pdf', ['onboarding' => $onboarding, 'download' => 1]) }}" class="btn btn-outline-secondary rounded-pill px-4">
          <i class="bi bi-filetype-pdf me-1"></i>Signed Contract PDF
        </a>
      </div>
    </div>
  @endif

  {{-- STAGE 5: COMPLETED (Status: completed) --}}
  @if($status === 'completed')
    <div class="setup-shell">
      <div class="text-center mb-4">
        <div class="rounded-circle d-inline-flex align-items-center justify-content-center bg-success-subtle text-success mb-2" style="width: 64px; height: 64px; font-size: 1.75rem;">
          <i class="bi bi-check-circle-fill"></i>
        </div>
        <div class="question-kicker">Move-in Complete</div>
        <h1 class="question-title">Welcome to Your Boarding House!</h1>
        <p class="question-subtitle mx-auto" style="max-width: 540px;">
          All requirements, contract signatures, and payments have been verified. Your stay is now active.
        </p>
      </div>

      {{-- Digital Tenant ID Card --}}
      <div class="digital-id-card mb-4">
        <div class="d-flex justify-content-between align-items-start mb-3">
          <div>
            <div class="text-uppercase small" style="opacity: 0.8; letter-spacing: 0.08em; font-weight: 700;">Digital Tenant Pass</div>
            <div class="fs-4 fw-bold">{{ $booking->student->full_name }}</div>
          </div>
          <span class="badge bg-white text-success rounded-pill px-3 py-1 fw-bold">Active Resident</span>
        </div>

        <div class="row g-2 pt-2 border-top border-white border-opacity-25 small">
          <div class="col-6">
            <span style="opacity: 0.75;">Boarding House:</span>
            <div class="fw-semibold">{{ $property->name }}</div>
          </div>
          <div class="col-6">
            <span style="opacity: 0.75;">Room:</span>
            <div class="fw-semibold">Room {{ $room->room_number }}</div>
          </div>
          <div class="col-6 mt-2">
            <span style="opacity: 0.75;">Check-in Date:</span>
            <div class="fw-semibold">{{ $checkInLabel }}</div>
          </div>
          <div class="col-6 mt-2">
            <span style="opacity: 0.75;">Digital Tenant ID:</span>
            <div class="fw-bold font-monospace">{{ $onboarding->digital_id ?? ('TID-' . str_pad($onboarding->id, 6, '0', STR_PAD_LEFT)) }}</div>
          </div>
        </div>
      </div>

      <div class="d-flex flex-wrap justify-content-center gap-2">
        <a href="{{ route('student.onboarding.contract_pdf', ['onboarding' => $onboarding, 'download' => 1]) }}" class="btn-brand">
          <i class="bi bi-download"></i>
          <span>Download Signed Contract (PDF)</span>
        </a>
        <a href="{{ route('student.onboarding.index') }}" class="btn-prev">
          <i class="bi bi-door-open"></i>
          <span>My Stays</span>
        </a>
      </div>
    </div>
  @endif
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
  // 1. Dropzone file selection preview
  const dropzoneBox = document.getElementById('dropzoneBox');
  const documentsInput = document.getElementById('documentsInput');
  const fileListContainer = document.getElementById('fileListContainer');

  if (dropzoneBox && documentsInput && fileListContainer) {
    documentsInput.addEventListener('change', function () {
      fileListContainer.innerHTML = '';
      if (!this.files || !this.files.length) return;

      Array.from(this.files).forEach(function (file, index) {
        const tag = document.createElement('div');
        tag.className = 'upload-file-tag';
        const sizeMb = (file.size / (1024 * 1024)).toFixed(2);
        tag.innerHTML = '<i class="bi bi-file-earmark-check text-success"></i> ' +
          '<span>' + file.name + ' (' + sizeMb + ' MB)</span>';
        fileListContainer.appendChild(tag);
      });
    });

    ['dragenter', 'dragover'].forEach(eventName => {
      dropzoneBox.addEventListener(eventName, e => {
        e.preventDefault();
        dropzoneBox.style.borderColor = '#10b981';
        dropzoneBox.style.background = '#f0fdf4';
      });
    });

    ['dragleave', 'drop'].forEach(eventName => {
      dropzoneBox.addEventListener(eventName, e => {
        e.preventDefault();
        dropzoneBox.style.borderColor = '#cbd5e1';
        dropzoneBox.style.background = '#f8fafc';
      });
    });

    dropzoneBox.addEventListener('drop', e => {
      if (e.dataTransfer && e.dataTransfer.files) {
        documentsInput.files = e.dataTransfer.files;
        documentsInput.dispatchEvent(new Event('change'));
      }
    });
  }

  // 2. Signature Canvas for Contract
  const canvas = document.getElementById('signCanvas');
  const sigPlaceholder = document.getElementById('sigPlaceholder');
  const clearSigBtn = document.getElementById('clearSigBtn');
  const sigStatusText = document.getElementById('sigStatusText');
  const signatureDataInput = document.getElementById('signatureDataInput');
  const studentContractForm = document.getElementById('studentContractForm');

  if (canvas) {
    const ctx = canvas.getContext('2d');
    let isDrawing = false;
    let hasDrawn = false;

    function resizeCanvas() {
      const rect = canvas.getBoundingClientRect();
      if (rect.width === 0) return;
      const dpr = window.devicePixelRatio || 1;
      canvas.width = rect.width * dpr;
      canvas.height = rect.height * dpr;
      ctx.scale(dpr, dpr);
      ctx.lineWidth = 2.2;
      ctx.lineCap = 'round';
      ctx.lineJoin = 'round';
      ctx.strokeStyle = '#0f172a';
    }

    resizeCanvas();
    window.addEventListener('resize', resizeCanvas);

    function getCoords(e) {
      const rect = canvas.getBoundingClientRect();
      const clientX = e.touches ? e.touches[0].clientX : e.clientX;
      const clientY = e.touches ? e.touches[0].clientY : e.clientY;
      return {
        x: clientX - rect.left,
        y: clientY - rect.top
      };
    }

    function startDraw(e) {
      e.preventDefault();
      isDrawing = true;
      const p = getCoords(e);
      ctx.beginPath();
      ctx.moveTo(p.x, p.y);
      if (sigPlaceholder) sigPlaceholder.style.display = 'none';
    }

    function draw(e) {
      if (!isDrawing) return;
      e.preventDefault();
      const p = getCoords(e);
      ctx.lineTo(p.x, p.y);
      ctx.stroke();
      hasDrawn = true;
      if (sigStatusText) {
        sigStatusText.textContent = 'Signature captured';
        sigStatusText.className = 'small text-success fw-semibold';
      }
    }

    function stopDraw(e) {
      if (!isDrawing) return;
      isDrawing = false;
      if (hasDrawn && signatureDataInput) {
        signatureDataInput.value = canvas.toDataURL('image/png');
      }
    }

    canvas.addEventListener('mousedown', startDraw);
    canvas.addEventListener('mousemove', draw);
    canvas.addEventListener('mouseup', stopDraw);
    canvas.addEventListener('mouseleave', stopDraw);

    canvas.addEventListener('touchstart', startDraw, { passive: false });
    canvas.addEventListener('touchmove', draw, { passive: false });
    canvas.addEventListener('touchend', stopDraw);

    if (clearSigBtn) {
      clearSigBtn.addEventListener('click', function () {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        hasDrawn = false;
        if (signatureDataInput) signatureDataInput.value = '';
        if (sigPlaceholder) sigPlaceholder.style.display = 'block';
        if (sigStatusText) {
          sigStatusText.textContent = 'No signature captured yet';
          sigStatusText.className = 'small text-muted';
        }
      });
    }

    if (studentContractForm) {
      studentContractForm.addEventListener('submit', function (e) {
        if (!hasDrawn || !signatureDataInput || !signatureDataInput.value) {
          e.preventDefault();
          alert('Please provide your signature before signing the contract.');
          return false;
        }
        const termsCheck = document.getElementById('acceptContractTerms');
        if (termsCheck && !termsCheck.checked) {
          e.preventDefault();
          alert('Please accept the contract terms and house rules checkbox.');
          return false;
        }
      });
    }
  }

  // 3. Payment Method Switcher
  window.selectMethod = function (method) {
    document.querySelectorAll('.payment-method-card').forEach(function (card) {
      card.classList.remove('active');
    });

    const radio = document.querySelector('input[name="payment_method"][value="' + method + '"]');
    if (radio) {
      radio.checked = true;
      const card = radio.closest('.payment-method-card');
      if (card) card.classList.add('active');
    }

    // Toggle info panes
    ['gcash', 'bank', 'cash'].forEach(function (m) {
      const pane = document.getElementById(m + 'Pane');
      if (pane) {
        pane.classList.toggle('d-none', m !== method);
      }
    });

    // Toggle online proof fields
    const onlineFields = document.getElementById('onlineProofFields');
    const paymentRef = document.getElementById('paymentReference');
    const paymentProof = document.getElementById('paymentProof');

    if (onlineFields) {
      const isOnline = (method === 'gcash' || method === 'bank');
      onlineFields.classList.toggle('d-none', !isOnline);
      if (paymentRef) paymentRef.required = isOnline;
      if (paymentProof) paymentProof.required = isOnline;
    }
  };
});
</script>
@endpush
