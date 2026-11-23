@extends('layouts.landlord')

@section('title', 'Move-in Review · ' . ($onboarding->booking->student->full_name ?? 'Tenant'))

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

  .review-workspace {
    max-width: 980px;
    margin: 0 auto;
    padding: 0.5rem 0.25rem 2.5rem;
  }

  .review-card {
    background: #ffffff;
    border: 1px solid var(--line);
    border-radius: 1.25rem;
    box-shadow: 0 4px 16px rgba(15, 23, 42, 0.04);
    padding: 1.5rem;
    margin-bottom: 1.5rem;
  }

  .actionable-card {
    border: 2px solid rgba(16, 185, 129, 0.4);
    box-shadow: 0 8px 24px rgba(4, 120, 87, 0.08);
  }

  .action-banner {
    background: #f0fdf4;
    border: 1px solid #bbf7d0;
    border-radius: 1rem;
    padding: 1rem 1.25rem;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
  }

  .document-preview-box {
    border: 1px solid var(--line);
    border-radius: 0.85rem;
    background: #f8fafc;
    padding: 1rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
  }

  .btn-brand {
    padding: 0.65rem 1.4rem;
    font-weight: 700;
    font-size: 0.92rem;
    border-radius: 999px;
    border: 1px solid rgba(16, 185, 129, 0.9);
    background: linear-gradient(125deg, var(--premium-emerald) 0%, var(--premium-emerald-deep) 100%);
    color: #ecfdf5;
    box-shadow: 0 8px 20px rgba(4, 120, 87, 0.25);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.45rem;
    cursor: pointer;
    text-decoration: none;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
  }

  .btn-brand:hover {
    transform: translateY(-1px);
    background: linear-gradient(125deg, #0ea770 0%, #046246 100%);
    color: #ffffff;
    box-shadow: 0 12px 26px rgba(4, 120, 87, 0.35);
  }

  .signature-preview-thumbnail {
    max-height: 80px;
    max-width: 220px;
    object-fit: contain;
    border: 1px solid #cbd5e1;
    border-radius: 0.5rem;
    background: #ffffff;
    padding: 0.25rem;
  }

  @media (max-width: 575.98px) {
    .review-card {
      padding: 1.15rem;
      border-radius: 1rem;
    }
  }
</style>
@endpush

@section('content')
@php
  $status = strtolower((string) $onboarding->status);
  $booking = $onboarding->booking;
  $student = $booking->student;
  $room = $booking->room;
  $property = $room->property;
  $landlordUser = $property->landlord;
  $landlordProfile = optional($landlordUser)->landlordProfile;

  $monthlyRentAmount = is_numeric($booking->monthly_rent_amount)
    ? (float) $booking->monthly_rent_amount
    : (float) ($room->price ?? 0);
  $submittedAdvanceAmount = is_numeric($onboarding->advance_amount)
    ? (float) $onboarding->advance_amount
    : (!empty($booking->include_advance_payment) ? $monthlyRentAmount : 0.0);
  $submittedPaymentAmount = is_numeric($onboarding->deposit_amount)
    ? (float) $onboarding->deposit_amount
    : (($monthlyRentAmount > 0 || $submittedAdvanceAmount > 0) ? ($monthlyRentAmount + $submittedAdvanceAmount) : null);
  $submittedTotalAmount = $submittedPaymentAmount ?? ($monthlyRentAmount + $submittedAdvanceAmount);

  $uploadedDocs = collect($onboarding->uploaded_documents ?? []);
  $hasStudentSignature = !empty($onboarding->contract_signature_path) && file_exists_any($onboarding->contract_signature_path);
  $hasLandlordSignature = !empty($onboarding->landlord_contract_signature_path) && file_exists_any($onboarding->landlord_contract_signature_path);
  $hasProfileSignature = !empty($landlordProfile?->contract_signature_path) && file_exists_any($landlordProfile?->contract_signature_path);

  $areDocsApproved = method_exists($onboarding, 'areDocumentsApproved') ? $onboarding->areDocumentsApproved() : !empty($onboarding->required_documents['_documents_approved']);
  $needsPaymentApproval = ($status === 'deposit_paid' && empty($onboarding->deposit_paid_at));
  $needsDocumentReview = ($status === 'documents_uploaded' && $uploadedDocs->isNotEmpty() && !$areDocsApproved);
  $needsLandlordSign = ($onboarding->contract_signed && !$onboarding->landlord_contract_signed);

  $statusBadgeClass = match($status) {
    'completed' => 'bg-success text-white',
    'deposit_paid' => 'bg-warning-subtle text-warning-emphasis border border-warning-subtle',
    'contract_signed' => 'bg-primary-subtle text-primary border border-primary-subtle',
    'documents_uploaded' => 'bg-info-subtle text-info-emphasis border border-info-subtle',
    default => 'bg-secondary-subtle text-secondary border border-secondary-subtle',
  };

  $statusLabel = match($status) {
    'pending' => 'Waiting for Student Upload',
    'documents_uploaded' => $areDocsApproved ? 'Documents Approved · Awaiting Student Signature' : 'Documents Ready for Review',
    'contract_signed' => $needsLandlordSign ? 'Landlord Signature Required' : 'Awaiting Student Payment',
    'deposit_paid' => 'Payment Awaiting Confirmation',
    'completed' => 'Move-in Complete',
    default => ucfirst(str_replace('_', ' ', $status)),
  };
@endphp

<div class="review-workspace">
  {{-- Header & Navigation --}}
  <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
      <a href="{{ route('landlord.onboarding.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3 mb-2">
        <i class="bi bi-arrow-left me-1"></i>Back to Move-in Progress
      </a>
      <h1 class="h3 fw-bold text-dark mb-1">Move-in Review · {{ $student->full_name }}</h1>
      <div class="text-muted small">
        <i class="bi bi-door-open me-1"></i>Room {{ $room->room_number }} · {{ $property->name }}
      </div>
    </div>

    <div class="d-flex align-items-center gap-2">
      <span class="badge rounded-pill {{ $statusBadgeClass }} px-3 py-2 fs-6">
        {{ $statusLabel }}
      </span>
      @if($status === 'completed' || $onboarding->contract_signed)
        <a href="{{ route('landlord.onboarding.contract_pdf', ['onboarding' => $onboarding, 'download' => 1]) }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3" title="Download PDF">
          <i class="bi bi-download me-1"></i>Contract PDF
        </a>
      @endif
    </div>
  </div>

  {{-- Session Notifications --}}
  @if(session('success'))
    <div class="alert alert-success rounded-4 d-flex align-items-center gap-2 mb-4">
      <i class="bi bi-check-circle-fill text-success fs-5"></i>
      <div>{{ session('success') }}</div>
    </div>
  @endif

  @if(session('error'))
    <div class="alert alert-danger rounded-4 d-flex align-items-center gap-2 mb-4">
      <i class="bi bi-exclamation-triangle-fill text-danger fs-5"></i>
      <div>{{ session('error') }}</div>
    </div>
  @endif

  {{-- Actionable Guidance Banner --}}
  @if($needsPaymentApproval)
    <div class="action-banner">
      <i class="bi bi-exclamation-circle-fill text-success fs-4"></i>
      <div class="flex-grow-1">
        <strong class="text-dark">Action Required: Verify Move-in Payment</strong>
        <div class="small text-muted">The student submitted payment proof. Review transaction details below and approve to complete move-in.</div>
      </div>
    </div>
  @elseif($needsLandlordSign)
    <div class="action-banner">
      <i class="bi bi-pen-fill text-success fs-4"></i>
      <div class="flex-grow-1">
        <strong class="text-dark">Action Required: Sign Rental Agreement</strong>
        <div class="small text-muted">The student has signed the lease agreement. Please apply your landlord signature below.</div>
      </div>
    </div>
  @elseif($needsDocumentReview)
    <div class="action-banner">
      <i class="bi bi-file-earmark-check-fill text-success fs-4"></i>
      <div class="flex-grow-1">
        <strong class="text-dark">Action Required: Review Uploaded Documents</strong>
        <div class="small text-muted">Inspect the student's verification files below. Approve them to proceed or request re-upload.</div>
      </div>
    </div>
  @elseif($status === 'pending')
    <div class="alert alert-light border rounded-4 d-flex align-items-center gap-2 mb-4 text-muted small">
      <i class="bi bi-clock-history fs-5 text-secondary"></i>
      <div>Waiting for the student to upload move-in documents. No review action is required right now.</div>
    </div>
  @endif

  {{-- SECTION 1: TENANT & STAY INFORMATION --}}
  <div class="review-card">
    <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
      <h5 class="fw-bold text-dark mb-0"><i class="bi bi-person-badge me-2 text-success"></i>Tenant & Lease Summary</h5>
      <span class="badge bg-light text-dark border">Onboarding #{{ $onboarding->id }}</span>
    </div>

    <div class="row g-3">
      <div class="col-12 col-md-6">
        <div class="d-flex align-items-center gap-3 mb-3">
          <div class="rounded-circle d-flex align-items-center justify-content-center bg-primary-subtle text-primary fw-bold fs-5" style="width: 48px; height: 48px;">
            {{ strtoupper(substr($student->full_name ?? 'S', 0, 1)) }}
          </div>
          <div>
            <div class="fw-bold text-dark fs-6">{{ $student->full_name }}</div>
            <div class="text-muted small">{{ $student->email }} · {{ $student->contact_number ?? $student->phone ?? 'No phone' }}</div>
          </div>
        </div>
        <div class="small text-muted">
          <div><strong>Student ID:</strong> {{ $student->student_id ?: 'Not provided' }}</div>
          <div><strong>College / Program:</strong> {{ $student->college ?: 'N/A' }} · {{ $student->program ?: 'N/A' }}</div>
        </div>
      </div>

      <div class="col-12 col-md-6">
        <div class="p-3 rounded-3 bg-light border small">
          <div class="d-flex justify-content-between mb-1">
            <span class="text-muted">Stay Dates:</span>
            <span class="fw-semibold text-dark">{{ optional($booking->check_in)->format('M d, Y') }} - {{ optional($booking->check_out)->format('M d, Y') }}</span>
          </div>
          <div class="d-flex justify-content-between mb-1">
            <span class="text-muted">Monthly Rent:</span>
            <span class="fw-bold text-dark">₱{{ number_format($monthlyRentAmount, 2) }}</span>
          </div>
          <div class="d-flex justify-content-between">
            <span class="text-muted">Total Move-in Due:</span>
            <span class="fw-bold text-success fs-6">₱{{ number_format($submittedTotalAmount, 2) }}</span>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- SECTION 2: MOVE-IN PAYMENT (PROMINENT IF STATUS IS DEPOSIT_PAID) --}}
  @if($status === 'deposit_paid' || $onboarding->deposit_paid)
    <div class="review-card {{ $needsPaymentApproval ? 'actionable-card' : '' }}">
      <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
        <h5 class="fw-bold text-dark mb-0">
          <i class="bi bi-cash-stack me-2 text-success"></i>Move-in Payment Verification
        </h5>
        @if($onboarding->deposit_paid)
          <span class="badge bg-success rounded-pill px-3 py-1">Payment Confirmed</span>
        @else
          <span class="badge bg-warning-subtle text-warning-emphasis rounded-pill px-3 py-1">Awaiting Confirmation</span>
        @endif
      </div>

      <div class="row g-3 mb-3">
        <div class="col-12 col-md-6">
          <div class="p-3 rounded-3 bg-light border small h-100">
            <div class="d-flex justify-content-between mb-2">
              <span class="text-muted">Payment Method:</span>
              <span class="fw-bold text-dark">{{ ucfirst((string) $onboarding->payment_method) }}</span>
            </div>
            @if($onboarding->payment_reference)
              <div class="d-flex justify-content-between mb-2">
                <span class="text-muted">Reference Number:</span>
                <span class="fw-bold text-primary font-monospace">{{ $onboarding->payment_reference }}</span>
              </div>
            @endif
            <div class="d-flex justify-content-between mb-2">
              <span class="text-muted">Total Amount Paid:</span>
              <span class="fw-bold text-success fs-6">₱{{ number_format($submittedTotalAmount, 2) }}</span>
            </div>
            @if($onboarding->payment_notes)
              <div class="mt-2 pt-2 border-top text-muted">
                <strong>Student Note:</strong> {{ $onboarding->payment_notes }}
              </div>
            @endif
          </div>
        </div>

        <div class="col-12 col-md-6">
          @if(!empty($onboarding->payment_proof_path) && file_exists_any($onboarding->payment_proof_path))
            <div class="p-3 rounded-3 bg-light border text-center h-100 d-flex flex-column justify-content-between">
              <div>
                <div class="small fw-bold text-muted mb-2">Submitted Payment Proof</div>
                <img src="{{ file_download_url($onboarding->payment_proof_path) }}" alt="Payment Receipt" class="img-fluid rounded border mb-2" style="max-height: 120px; object-fit: contain;">
              </div>
              <div>
                <a href="{{ file_download_url($onboarding->payment_proof_path) }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                  <i class="bi bi-box-arrow-up-right me-1"></i>View Full Receipt
                </a>
              </div>
            </div>
          @else
            <div class="p-3 rounded-3 bg-light border text-center h-100 d-flex align-items-center justify-content-center text-muted small">
              <div>
                <i class="bi bi-cash-coin fs-3 d-block mb-1"></i>
                Cash payment or physical receipt
              </div>
            </div>
          @endif
        </div>
      </div>

      {{-- Landlord Payment Decision Buttons --}}
      @if($needsPaymentApproval)
        <div class="d-flex flex-wrap justify-content-end gap-2 pt-3 border-top">
          <form action="{{ route('landlord.onboarding.approve_documents', $onboarding) }}" method="POST" class="d-inline" onsubmit="return confirm('Confirm payment receipt? This will finalize onboarding and activate the student tenancy.');">
            @csrf
            <input type="hidden" name="action" value="approve_payment">
            <button type="submit" class="btn-brand">
              <i class="bi bi-check-circle-fill"></i>
              <span>Approve Payment & Finalize Move-in</span>
            </button>
          </form>

          <form action="{{ route('landlord.onboarding.approve_documents', $onboarding) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to reject this payment? The student will be prompted to re-submit proof.');">
            @csrf
            <input type="hidden" name="action" value="reject_payment">
            <button type="submit" class="btn btn-outline-danger rounded-pill px-3">
              <i class="bi bi-x-circle me-1"></i>Reject Payment
            </button>
          </form>
        </div>
      @endif
    </div>
  @endif

  {{-- SECTION 3: RENTAL CONTRACT & SIGNATURES --}}
  <div class="review-card {{ $needsLandlordSign ? 'actionable-card' : '' }}">
    <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
      <h5 class="fw-bold text-dark mb-0">
        <i class="bi bi-pencil-square me-2 text-success"></i>Rental Agreement & Signatures
      </h5>
      <div class="d-flex gap-2">
        <a href="{{ route('landlord.onboarding.contract', $onboarding) }}" target="_blank" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
          <i class="bi bi-eye me-1"></i>View Agreement
        </a>
      </div>
    </div>

    <div class="row g-3 mb-3">
      {{-- Student Signature --}}
      <div class="col-12 col-md-6">
        <div class="p-3 rounded-3 bg-light border small h-100">
          <div class="fw-bold text-dark mb-1">Student E-Signature</div>
          @if($onboarding->contract_signed)
            <div class="text-success fw-semibold mb-2">
              <i class="bi bi-check-circle-fill me-1"></i>Signed on {{ optional($onboarding->contract_signed_at)->format('M d, Y h:i A') }}
            </div>
            <div class="text-muted mb-2">Signer: <strong>{{ $onboarding->contract_signature_name ?? $student->full_name }}</strong></div>
            @if($hasStudentSignature)
              <div>
                <img src="{{ file_download_url($onboarding->contract_signature_path) }}" alt="Student Signature" class="signature-preview-thumbnail">
              </div>
            @endif
          @else
            <div class="text-muted"><i class="bi bi-hourglass me-1"></i>Pending student signature</div>
          @endif
        </div>
      </div>

      {{-- Landlord Signature --}}
      <div class="col-12 col-md-6">
        <div class="p-3 rounded-3 bg-light border small h-100">
          <div class="fw-bold text-dark mb-1">Landlord E-Signature</div>
          @if($onboarding->landlord_contract_signed)
            <div class="text-success fw-semibold mb-2">
              <i class="bi bi-check-circle-fill me-1"></i>Signed on {{ optional($onboarding->landlord_contract_signed_at)->format('M d, Y h:i A') }}
            </div>
            @if($hasLandlordSignature)
              <div>
                <img src="{{ file_download_url($onboarding->landlord_contract_signature_path) }}" alt="Landlord Signature" class="signature-preview-thumbnail">
              </div>
            @endif
          @elseif($needsLandlordSign)
            <div class="text-warning-emphasis fw-semibold mb-2">
              <i class="bi bi-pen me-1"></i>Ready for your signature
            </div>
            @if($hasProfileSignature)
              <form action="{{ route('landlord.onboarding.sign_contract', $onboarding) }}" method="POST" class="mt-2">
                @csrf
                <button type="submit" class="btn btn-sm btn-brand">
                  <i class="bi bi-pen-fill"></i>Apply Saved Profile Signature
                </button>
              </form>
            @else
              <div class="text-danger small mb-2">No saved signature found in your Landlord Profile.</div>
              <a href="{{ route('landlord.profile.edit') }}" class="btn btn-sm btn-outline-secondary rounded-pill">
                <i class="bi bi-gear me-1"></i>Upload Signature in Profile
              </a>
            @endif
          @else
            <div class="text-muted"><i class="bi bi-clock me-1"></i>Awaiting prior onboarding steps</div>
          @endif
        </div>
      </div>
    </div>
  </div>

  {{-- SECTION 4: SUBMITTED DOCUMENTS --}}
  <div class="review-card {{ $needsDocumentReview ? 'actionable-card' : '' }}">
    <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
      <h5 class="fw-bold text-dark mb-0">
        <i class="bi bi-file-earmark-text me-2 text-success"></i>Submitted Documents
      </h5>
      <span class="badge bg-light text-dark border">{{ $uploadedDocs->count() }} file{{ $uploadedDocs->count() > 1 ? 's' : '' }}</span>
    </div>

    @if($uploadedDocs->isEmpty())
      <div class="alert alert-light border small text-muted mb-0">
        <i class="bi bi-info-circle me-1"></i>No documents uploaded by the student yet.
      </div>
    @else
      <div class="row g-2 mb-3">
        @foreach($uploadedDocs as $doc)
          <div class="col-12 col-md-6">
            <div class="document-preview-box">
              <div class="d-flex align-items-center gap-2 overflow-hidden">
                <i class="bi bi-file-earmark-pdf fs-3 text-danger"></i>
                <div class="text-truncate">
                  <div class="fw-semibold text-dark small text-truncate">{{ basename($doc) }}</div>
                  <div class="text-muted" style="font-size: 0.72rem;">Uploaded File</div>
                </div>
              </div>
              <div class="d-flex gap-1 flex-shrink-0">
                <a href="{{ route('documents.view', [$onboarding->id, basename($doc)]) }}" target="_blank" class="btn btn-sm btn-outline-secondary rounded-pill px-2" title="View Document">
                  <i class="bi bi-eye"></i>
                </a>
                <a href="{{ route('documents.view', [$onboarding->id, basename($doc)]) }}?download=1" class="btn btn-sm btn-outline-secondary rounded-pill px-2" title="Download Document">
                  <i class="bi bi-download"></i>
                </a>
              </div>
            </div>
          </div>
        @endforeach
      </div>

      @if($needsDocumentReview)
        <div class="d-flex flex-wrap justify-content-end gap-2 pt-3 border-top">
          <form action="{{ route('landlord.onboarding.approve_documents', $onboarding) }}" method="POST" class="d-inline">
            @csrf
            <input type="hidden" name="action" value="approve">
            <button type="submit" class="btn-brand">
              <i class="bi bi-check2"></i>
              <span>Approve Documents</span>
            </button>
          </form>

          <form action="{{ route('landlord.onboarding.approve_documents', $onboarding) }}" method="POST" class="d-inline" onsubmit="return confirm('Reject documents and request the student to re-upload?');">
            @csrf
            <input type="hidden" name="action" value="reject">
            <button type="submit" class="btn btn-outline-danger rounded-pill px-3">
              <i class="bi bi-arrow-repeat me-1"></i>Request Re-upload
            </button>
          </form>
        </div>
      @elseif($areDocsApproved && $status === 'documents_uploaded')
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 pt-3 border-top">
          <div class="text-success small fw-semibold">
            <i class="bi bi-check-circle-fill me-1"></i>Documents approved. Waiting for student to review and sign the contract.
          </div>
          <form action="{{ route('landlord.onboarding.approve_documents', $onboarding) }}" method="POST" class="d-inline" onsubmit="return confirm('Reopen document review and request student to re-upload?');">
            @csrf
            <input type="hidden" name="action" value="reject">
            <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-3">
              <i class="bi bi-arrow-repeat me-1"></i>Request Re-upload
            </button>
          </form>
        </div>
      @endif
    @endif
  </div>

  {{-- SECTION 5: MOVE-IN COMPLETION (IF COMPLETED) --}}
  @if($status === 'completed')
    <div class="review-card bg-light border-success">
      <div class="d-flex align-items-center gap-3">
        <div class="rounded-circle d-flex align-items-center justify-content-center bg-success text-white" style="width: 48px; height: 48px; font-size: 1.5rem;">
          <i class="bi bi-check-lg"></i>
        </div>
        <div>
          <h5 class="fw-bold text-dark mb-1">Onboarding Successfully Completed</h5>
          <div class="small text-muted">
            Digital Tenant ID: <strong class="font-monospace text-dark">{{ $onboarding->digital_id }}</strong> · Tenancy active in property records.
          </div>
        </div>
      </div>
    </div>
  @endif
</div>
@endsection
