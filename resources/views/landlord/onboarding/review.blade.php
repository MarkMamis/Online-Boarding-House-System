@extends('layouts.landlord')

@section('title', 'Review Documents')

@section('content')
  <div class="onboarding-review-shell">
    @php
      $status = strtolower((string) $onboarding->status);
      $statusLabel = ucfirst(str_replace('_', ' ', $status));
      $paymentReviewPending = $status === 'deposit_paid' && empty($onboarding->deposit_paid_at);
      if ($paymentReviewPending) {
        $statusLabel = 'Payment Under Review';
      }
      $progress = match($status) {
        'pending' => 25,
        'documents_uploaded' => 50,
        'contract_signed' => 75,
        'deposit_paid' => 90,
        'completed' => 100,
        default => 0,
      };
      $statusClass = match($status) {
        'pending' => 'status-pending',
        'documents_uploaded' => 'status-info',
        'contract_signed' => 'status-primary',
        'deposit_paid' => 'status-primary',
        'completed' => 'status-approved',
        default => 'status-default',
      };
      $statusIcon = match($status) {
        'pending' => 'bi-hourglass-split',
        'documents_uploaded' => 'bi-file-earmark-check',
        'contract_signed' => 'bi-pencil-square',
        'deposit_paid' => 'bi-cash-stack',
        'completed' => 'bi-check-circle',
        default => 'bi-info-circle',
      };
      $uploadedDocs = collect($onboarding->uploaded_documents ?? []);
      $contractSignaturePath = (string) ($onboarding->contract_signature_path ?? '');
      $hasContractSignature = $contractSignaturePath !== '' && Storage::disk('public')->exists($contractSignaturePath);
      $contractSignatureUrl = $hasContractSignature ? asset('storage/' . $contractSignaturePath) : null;
      $contractSignerName = trim((string) ($onboarding->contract_signature_name ?? ($onboarding->booking->student->full_name ?? '')));
      $monthlyRentAmount = is_numeric($onboarding->booking?->monthly_rent_amount)
        ? (float) $onboarding->booking?->monthly_rent_amount
        : (is_numeric($onboarding->booking?->room?->price) ? (float) $onboarding->booking?->room?->price : 0.0);
      $submittedAdvanceAmount = is_numeric($onboarding->advance_amount)
        ? (float) $onboarding->advance_amount
        : (!empty($onboarding->booking?->include_advance_payment) ? $monthlyRentAmount : 0.0);
      $submittedPaymentAmount = is_numeric($onboarding->deposit_amount)
        ? (float) $onboarding->deposit_amount
        : (($monthlyRentAmount > 0 || $submittedAdvanceAmount > 0) ? ($monthlyRentAmount + $submittedAdvanceAmount) : null);
      $submittedTotalAmount = $submittedPaymentAmount ?? ($monthlyRentAmount + $submittedAdvanceAmount);
      $submittedDepositAmount = max(0.0, $submittedTotalAmount - $submittedAdvanceAmount);
      $advanceIncludedLabel = $submittedAdvanceAmount > 0 ? 'Included' : 'Not Included';
    @endphp

    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
      <div>
        <div class="text-uppercase small text-muted fw-semibold">Onboarding Review</div>
        <h1 class="h3 mb-1">{{ $onboarding->booking->student->full_name }}</h1>
        <div class="text-muted small">Review onboarding status, submitted documents, and approval actions.</div>
      </div>
      <a href="{{ route('landlord.onboarding.index') }}" class="btn btn-outline-secondary rounded-pill px-3">Back</a>
    </div>

    @if(session('success'))
      <div class="alert alert-success rounded-4 mb-3">{{ session('success') }}</div>
    @endif

    @if(session('error'))
      <div class="alert alert-danger rounded-4 mb-3">{{ session('error') }}</div>
    @endif

    <div class="review-summary mb-4">
      <div class="review-summary-item">
        <div class="review-summary-label">Current Status</div>
        <div class="review-summary-value"><span class="status-pill {{ $statusClass }}"><i class="bi {{ $statusIcon }}"></i>{{ $statusLabel }}</span></div>
      </div>
      <div class="review-summary-item">
        <div class="review-summary-label">Progress</div>
        <div class="review-summary-value">{{ $progress }}%</div>
      </div>
      <div class="review-summary-item">
        <div class="review-summary-label">Documents</div>
        <div class="review-summary-value">{{ $uploadedDocs->count() }}</div>
      </div>
      <div class="review-summary-item">
        <div class="review-summary-label">Last Updated</div>
        <div class="review-summary-value">{{ $onboarding->updated_at->diffForHumans() }}</div>
      </div>
    </div>

    <div class="row g-3 mb-4">
      <div class="col-12 col-xl-8">
        <div class="review-card h-100">
          <div class="review-card-header">
            <h5 class="mb-0">Student and Lease Information</h5>
          </div>
          <div class="review-card-body">
            <div class="tenant-head mb-3">
              <div class="tenant-avatar">{{ strtoupper(substr($onboarding->booking->student->full_name ?? 'S', 0, 1)) }}</div>
              <div class="min-w-0">
                <div class="tenant-name text-truncate">{{ $onboarding->booking->student->full_name }}</div>
                <div class="tenant-email text-truncate">{{ $onboarding->booking->student->email }}</div>
              </div>
            </div>

            <div class="meta-row mb-3">
              <span class="meta-chip"><i class="bi bi-person-badge"></i>Student ID: Hidden</span>
              <span class="meta-chip"><i class="bi bi-building"></i>{{ $onboarding->booking->room->property->name }}</span>
              <span class="meta-chip"><i class="bi bi-door-open"></i>Room {{ $onboarding->booking->room->room_number }}</span>
              <span class="meta-chip"><i class="bi bi-calendar-range"></i>{{ $onboarding->booking->check_in->format('M d, Y') }} - {{ $onboarding->booking->check_out->format('M d, Y') }}</span>
            </div>

            <div>
              <div class="progress-label-row">
                <span class="progress-label">Onboarding Progress</span>
                <span class="progress-value">{{ $progress }}%</span>
              </div>
              <div class="progress progress-modern" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $progress }}">
                <div class="progress-bar" style="width: {{ $progress }}%;"></div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-12 col-xl-4">
        <div class="review-card h-100">
          <div class="review-card-header">
            <h5 class="mb-0">Status Details</h5>
          </div>
          <div class="review-card-body">
            <div class="status-note-list">
              <div class="status-note-item {{ $onboarding->contract_signed ? 'done' : '' }}">
                <i class="bi {{ $onboarding->contract_signed ? 'bi-check-circle-fill' : 'bi-circle' }}"></i>
                <span>Contract Signed {{ $onboarding->contract_signed_at ? 'on ' . $onboarding->contract_signed_at->format('M d, Y H:i') : '' }}</span>
              </div>
              <div class="status-note-item {{ $onboarding->deposit_paid ? 'done' : '' }}">
                <i class="bi {{ $onboarding->deposit_paid ? 'bi-check-circle-fill' : 'bi-circle' }}"></i>
                <span>
                  @if($paymentReviewPending)
                    Payment submitted @if($onboarding->payment_submitted_at) on {{ $onboarding->payment_submitted_at->format('M d, Y H:i') }} @endif and waiting for landlord approval
                  @else
                    Deposit {{ $onboarding->deposit_paid ? 'Paid' : 'Not Paid' }}
                    @if($onboarding->deposit_paid_at)
                      on {{ $onboarding->deposit_paid_at->format('M d, Y') }}
                    @endif
                  @endif
                  @if($onboarding->deposit_amount)
                    (PHP {{ number_format($onboarding->deposit_amount, 2) }})
                  @endif
                </span>
              </div>
              <div class="status-note-item {{ !empty($onboarding->digital_id) ? 'done' : '' }}">
                <i class="bi {{ !empty($onboarding->digital_id) ? 'bi-check-circle-fill' : 'bi-circle' }}"></i>
                <span>Digital Tenant ID: {{ $onboarding->digital_id ?? 'Not assigned' }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    @if($onboarding->contract_signed)
      <div class="review-card mb-4">
        <div class="review-card-header d-flex justify-content-between align-items-center">
          <h5 class="mb-0">Contract E-signature Proof</h5>
          @if($onboarding->contract_signed_at)
            <span class="small text-muted">Signed {{ $onboarding->contract_signed_at->format('M d, Y h:i A') }}</span>
          @endif
        </div>
        <div class="review-card-body">
          @if($contractSignerName !== '')
            <div class="mb-3">
              <div class="small text-muted">Signed as</div>
              <div class="fw-semibold">{{ $contractSignerName }}</div>
            </div>
          @endif
          @if($hasContractSignature && $contractSignatureUrl)
            <div class="border rounded-4 p-3 text-center" style="background:#fff;">
              <img src="{{ $contractSignatureUrl }}" alt="Tenant e-signature" style="max-width:100%;max-height:180px;object-fit:contain;">
            </div>
            <div class="mt-3">
              <a href="{{ $contractSignatureUrl }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary rounded-pill">Open Signature</a>
            </div>
          @else
            <div class="alert alert-warning rounded-3 mb-0">
              <i class="bi bi-exclamation-triangle me-2"></i>
              No e-signature image is available for this onboarding record.
            </div>
          @endif
        </div>
      </div>
    @endif

    @if($paymentReviewPending)
      <div class="review-card mb-4">
        <div class="review-card-header">
          <h5 class="mb-0">Payment Submission Review</h5>
        </div>
        <div class="review-card-body">
          <div class="payment-review-banner mb-3">
            <div class="payment-review-title">Review Checklist</div>
            <div class="payment-review-copy">Confirm method, total submitted amount, and proof/reference details before approving.</div>
          </div>

          <div class="payment-breakdown-grid mb-3">
            <div class="payment-breakdown-item">
              <div class="payment-breakdown-label">Total Submitted</div>
              <div class="payment-breakdown-value">PHP {{ number_format($submittedTotalAmount, 2) }}</div>
            </div>
            <div class="payment-breakdown-item">
              <div class="payment-breakdown-label">Deposit Amount</div>
              <div class="payment-breakdown-value">PHP {{ number_format($submittedDepositAmount, 2) }}</div>
            </div>
            <div class="payment-breakdown-item">
              <div class="payment-breakdown-label">Advance Amount</div>
              <div class="payment-breakdown-value">PHP {{ number_format($submittedAdvanceAmount, 2) }}</div>
              <div class="small text-muted">{{ $advanceIncludedLabel }}</div>
            </div>
          </div>

          <div class="row g-3 mb-3">
            <div class="col-12 col-md-4">
              <div class="small text-muted">Method</div>
              <div class="fw-semibold">{{ ucfirst((string) ($onboarding->payment_method ?? '—')) }}</div>
            </div>
            <div class="col-12 col-md-4">
              <div class="small text-muted">Reference</div>
              <div class="fw-semibold">{{ $onboarding->payment_reference ?: '—' }}</div>
            </div>
            <div class="col-12 col-md-4">
              <div class="small text-muted">Submitted At</div>
              <div class="fw-semibold">{{ optional($onboarding->payment_submitted_at)->format('M d, Y h:i A') ?: '—' }}</div>
            </div>
          </div>

          <div class="small text-muted mb-3">
            Formula: Total Submitted = Deposit Amount + Advance Amount (PHP {{ number_format($submittedDepositAmount, 2) }} + PHP {{ number_format($submittedAdvanceAmount, 2) }})
          </div>

          @if(!empty($onboarding->payment_notes))
            <div class="alert alert-light border rounded-3 mb-3">
              <div class="small text-muted mb-1">Student Payment Notes</div>
              <div>{{ $onboarding->payment_notes }}</div>
            </div>
          @endif

          @if(!empty($onboarding->payment_proof_path))
            <div class="mb-3">
              <a href="{{ asset('storage/' . $onboarding->payment_proof_path) }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary rounded-pill">View Payment Proof</a>
            </div>
          @endif

          <p class="text-muted mb-3">Approve when the submitted amount and proof are valid. Reject to send the student back to Step 3 for payment resubmission.</p>

          <form method="POST" action="{{ route('landlord.onboarding.approve_documents', $onboarding) }}">
            @csrf
            <div class="decision-grid mb-3">
              <label class="decision-card">
                <input class="form-check-input" type="radio" name="action" value="approve_payment" checked>
                <div>
                  <div class="decision-title">Approve Payment</div>
                  <div class="decision-copy">Verify payment, complete onboarding, and activate tenant access.</div>
                </div>
              </label>

              <label class="decision-card decision-reject">
                <input class="form-check-input" type="radio" name="action" value="reject_payment">
                <div>
                  <div class="decision-title">Reject Payment</div>
                  <div class="decision-copy">Return student to Step 3 to resubmit corrected payment details.</div>
                </div>
              </label>
            </div>

            <button type="submit" class="btn btn-brand rounded-pill px-3">Submit Payment Decision</button>
          </form>
        </div>
      </div>
    @endif

    @if($uploadedDocs->isNotEmpty())
      <div class="review-card mb-4">
        <div class="review-card-header d-flex justify-content-between align-items-center">
          <h5 class="mb-0">Uploaded Documents</h5>
          <span class="small text-muted">{{ $uploadedDocs->count() }} file{{ $uploadedDocs->count() === 1 ? '' : 's' }}</span>
        </div>
        <div class="review-card-body">
          <div class="row g-3">
            @foreach($uploadedDocs as $doc)
              @php
                $fileName = basename($doc);
                $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                $isImage = in_array($fileExtension, ['jpg', 'jpeg', 'png', 'gif'], true);
                $exists = Storage::disk('public')->exists($doc);
                $fileSize = $exists ? Storage::disk('public')->size($doc) : 0;
                $fileSizeFormatted = !$exists
                  ? 'Unavailable'
                  : ($fileSize < 1024
                      ? $fileSize . ' B'
                      : ($fileSize < 1048576
                          ? round($fileSize / 1024, 1) . ' KB'
                          : round($fileSize / 1048576, 1) . ' MB'));
              @endphp

              <div class="col-12 col-md-6 col-xl-4">
                <div class="doc-card h-100">
                  <div class="doc-icon {{ $fileExtension === 'pdf' ? 'doc-icon-pdf' : ($isImage ? 'doc-icon-image' : 'doc-icon-file') }}">
                    @if($isImage)
                      <i class="bi bi-image"></i>
                    @elseif($fileExtension === 'pdf')
                      <i class="bi bi-filetype-pdf"></i>
                    @else
                      <i class="bi bi-file-earmark-text"></i>
                    @endif
                  </div>

                  <div class="doc-name text-truncate" title="{{ $fileName }}">{{ $fileName }}</div>
                  <div class="doc-meta">{{ strtoupper($fileExtension ?: 'FILE') }} · {{ $fileSizeFormatted }}</div>

                  <div class="doc-actions">
                    @if($exists)
                      <a href="{{ route('documents.view', ['onboarding' => $onboarding->id, 'filename' => $fileName]) }}" target="_blank" class="btn btn-sm btn-brand rounded-pill">View</a>
                      <a href="{{ route('documents.view', ['onboarding' => $onboarding->id, 'filename' => $fileName]) }}?download=1" class="btn btn-sm btn-outline-secondary rounded-pill">Download</a>
                    @else
                      <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill" disabled>Unavailable</button>
                    @endif
                  </div>

                  @if($isImage && $exists)
                    <a href="{{ route('documents.view', ['onboarding' => $onboarding->id, 'filename' => $fileName]) }}" target="_blank" class="doc-preview-link">
                      <img src="{{ route('documents.view', ['onboarding' => $onboarding->id, 'filename' => $fileName]) }}" alt="Document Preview" class="doc-preview">
                    </a>
                  @endif
                </div>
              </div>
            @endforeach
          </div>
        </div>
      </div>

      @if($onboarding->status === 'pending')
        <div class="review-card">
          <div class="review-card-header">
            <h5 class="mb-0">Document Review Decision</h5>
          </div>
          <div class="review-card-body">
            <p class="text-muted mb-3">Choose whether to approve submitted documents or reject them for re-upload.</p>

            <form method="POST" action="{{ route('landlord.onboarding.approve_documents', $onboarding) }}">
              @csrf
              <div class="decision-grid mb-3">
                <label class="decision-card">
                  <input class="form-check-input" type="radio" name="action" value="approve" checked>
                  <div>
                    <div class="decision-title">Approve Documents</div>
                    <div class="decision-copy">Allow student to proceed to the next onboarding step.</div>
                  </div>
                </label>

                <label class="decision-card decision-reject">
                  <input class="form-check-input" type="radio" name="action" value="reject">
                  <div>
                    <div class="decision-title">Reject Documents</div>
                    <div class="decision-copy">Require student to upload corrected documents.</div>
                  </div>
                </label>
              </div>

              <button type="submit" class="btn btn-brand rounded-pill px-3">Submit Review</button>
            </form>
          </div>
        </div>
      @endif
    @else
      <div class="review-card">
        <div class="empty-state">
          <i class="bi bi-file-earmark-x fs-1 mb-2"></i>
          <div class="empty-title">No Documents Uploaded</div>
          <div class="empty-copy">The student has not uploaded any required documents yet.</div>
        </div>
      </div>
    @endif
  </div>
@endsection

@push('styles')
<style>
  .onboarding-review-shell {
    background: linear-gradient(180deg, #ffffff 0%, #fbfdfc 100%);
    border: 1px solid rgba(2,8,20,.08);
    border-radius: 1.25rem;
    box-shadow: 0 10px 26px rgba(2,8,20,.06);
    padding: 1.25rem;
  }
  .review-summary {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: .75rem;
  }
  .review-summary-item {
    border: 1px solid rgba(20,83,45,.16);
    background: linear-gradient(180deg, rgba(167,243,208,.18), rgba(255,255,255,.85));
    border-radius: .9rem;
    padding: .7rem .8rem;
  }
  .review-summary-label {
    font-size: .72rem;
    text-transform: uppercase;
    letter-spacing: .05em;
    color: rgba(2,8,20,.55);
    font-weight: 700;
    margin-bottom: .2rem;
  }
  .review-summary-value {
    font-size: .95rem;
    font-weight: 700;
    color: #0f172a;
  }
  .review-card {
    border: 1px solid rgba(2,8,20,.09);
    border-radius: 1rem;
    background: #fff;
    overflow: hidden;
  }
  .review-card-header {
    border-bottom: 1px solid rgba(2,8,20,.08);
    padding: .85rem 1rem;
    background: rgba(248,250,252,.72);
  }
  .review-card-body {
    padding: 1rem;
  }
  .tenant-head {
    display: flex;
    align-items: center;
    gap: .6rem;
  }
  .tenant-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-weight: 800;
    color: #14532d;
    background: rgba(167,243,208,.35);
    border: 1px solid rgba(20,83,45,.2);
    flex: 0 0 auto;
  }
  .tenant-name {
    font-weight: 700;
    color: #14532d;
    line-height: 1.2;
  }
  .tenant-email {
    font-size: .78rem;
    color: #64748b;
  }
  .meta-row {
    display: flex;
    flex-wrap: wrap;
    gap: .45rem;
    align-items: center;
  }
  .meta-chip {
    display: inline-flex;
    align-items: center;
    gap: .35rem;
    border: 1px solid rgba(2,8,20,.12);
    border-radius: 999px;
    background: #f8fafc;
    color: #0f172a;
    padding: .18rem .55rem;
    font-size: .78rem;
    font-weight: 600;
  }
  .progress-label-row {
    display: flex;
    justify-content: space-between;
    gap: .6rem;
    align-items: center;
    margin-bottom: .25rem;
  }
  .progress-label {
    font-size: .73rem;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: .05em;
    font-weight: 700;
  }
  .progress-value {
    font-size: .78rem;
    color: #14532d;
    font-weight: 700;
  }
  .progress-modern {
    height: .52rem;
    border-radius: 999px;
    background: #e2e8f0;
    overflow: hidden;
  }
  .progress-modern .progress-bar {
    background: linear-gradient(90deg, #14532d, #16a34a);
  }
  .status-pill {
    display: inline-flex;
    align-items: center;
    gap: .35rem;
    border-radius: 999px;
    padding: .22rem .62rem;
    font-size: .76rem;
    font-weight: 700;
    border: 1px solid transparent;
  }
  .status-pending {
    color: #7c2d12;
    background: #ffedd5;
    border-color: #fdba74;
  }
  .status-info {
    color: #1e3a8a;
    background: #dbeafe;
    border-color: #93c5fd;
  }
  .status-primary {
    color: #0f766e;
    background: #ccfbf1;
    border-color: #5eead4;
  }
  .status-approved {
    color: #14532d;
    background: #dcfce7;
    border-color: #86efac;
  }
  .status-default {
    color: #1f2937;
    background: #f3f4f6;
    border-color: #d1d5db;
  }
  .status-note-list {
    display: grid;
    gap: .5rem;
  }
  .status-note-item {
    display: flex;
    align-items: flex-start;
    gap: .45rem;
    font-size: .83rem;
    color: #475569;
  }
  .status-note-item i {
    margin-top: .1rem;
    color: #94a3b8;
  }
  .status-note-item.done i {
    color: #16a34a;
  }
  .doc-card {
    border: 1px solid rgba(2,8,20,.08);
    border-radius: .9rem;
    background: #fff;
    padding: .85rem;
    display: grid;
    gap: .45rem;
  }
  .doc-icon {
    width: 38px;
    height: 38px;
    border-radius: .75rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
  }
  .doc-icon-image {
    color: #14532d;
    background: #dcfce7;
    border: 1px solid #86efac;
  }
  .doc-icon-pdf {
    color: #7f1d1d;
    background: #fee2e2;
    border: 1px solid #fca5a5;
  }
  .doc-icon-file {
    color: #1e3a8a;
    background: #dbeafe;
    border: 1px solid #93c5fd;
  }
  .doc-name {
    font-weight: 700;
    color: #14532d;
    line-height: 1.2;
  }
  .doc-meta {
    font-size: .76rem;
    color: #64748b;
  }
  .doc-actions {
    display: inline-flex;
    gap: .4rem;
    flex-wrap: wrap;
  }
  .doc-preview-link {
    display: block;
  }
  .doc-preview {
    width: 100%;
    border-radius: .65rem;
    border: 1px solid rgba(2,8,20,.08);
    max-height: 170px;
    object-fit: cover;
  }
  .decision-grid {
    display: grid;
    gap: .6rem;
    grid-template-columns: 1fr;
  }
  .decision-card {
    display: flex;
    align-items: flex-start;
    gap: .55rem;
    border: 1px solid rgba(20,83,45,.18);
    border-radius: .8rem;
    padding: .75rem;
    background: rgba(167,243,208,.12);
    cursor: pointer;
  }
  .decision-card input {
    margin-top: .18rem;
  }
  .decision-reject {
    border-color: rgba(220,38,38,.2);
    background: rgba(254,226,226,.45);
  }
  .decision-title {
    font-size: .84rem;
    font-weight: 700;
    color: #0f172a;
  }
  .decision-copy {
    font-size: .78rem;
    color: #64748b;
  }
  .payment-review-banner {
    border: 1px solid rgba(2,8,20,.1);
    border-radius: .85rem;
    background: #f8fafc;
    padding: .7rem .85rem;
  }
  .payment-review-title {
    font-size: .78rem;
    text-transform: uppercase;
    letter-spacing: .05em;
    color: rgba(2,8,20,.58);
    font-weight: 700;
    margin-bottom: .1rem;
  }
  .payment-review-copy {
    font-size: .82rem;
    color: #475569;
  }
  .payment-breakdown-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: .6rem;
  }
  .payment-breakdown-item {
    border: 1px solid rgba(2,8,20,.11);
    border-radius: .8rem;
    background: #fff;
    padding: .65rem .75rem;
  }
  .payment-breakdown-label {
    font-size: .72rem;
    text-transform: uppercase;
    letter-spacing: .05em;
    color: rgba(2,8,20,.52);
    font-weight: 700;
    margin-bottom: .12rem;
  }
  .payment-breakdown-value {
    font-size: .94rem;
    color: #0f172a;
    font-weight: 700;
  }

  @media (min-width: 992px) {
    .decision-grid {
      grid-template-columns: repeat(2, minmax(0, 1fr));
    }
  }

  .empty-state {
    text-align: center;
    color: #64748b;
    padding: 2.4rem 1rem;
  }
  .empty-state i {
    color: rgba(2,8,20,.2);
  }
  .empty-title {
    color: #0f172a;
    font-weight: 700;
    margin-bottom: .35rem;
  }
  .empty-copy {
    max-width: 520px;
    margin: 0 auto;
    font-size: .9rem;
  }

  @media (max-width: 991.98px) {
    .onboarding-review-shell {
      padding: .95rem;
    }
    .review-summary {
      grid-template-columns: repeat(2, minmax(0, 1fr));
    }
    .payment-breakdown-grid {
      grid-template-columns: 1fr;
    }
  }

  @media (max-width: 575.98px) {
    .review-summary {
      grid-template-columns: 1fr;
    }
    .doc-actions {
      display: grid;
      grid-template-columns: 1fr;
    }
    .doc-actions .btn {
      width: 100%;
    }
  }
</style>
@endpush