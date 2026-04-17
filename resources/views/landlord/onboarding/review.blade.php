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
      $landlordUser = optional(optional(optional($onboarding->booking)->room)->property)->landlord;
      $landlordContractSignaturePath = (string) ($onboarding->landlord_contract_signature_path ?? '');
      $hasLandlordContractSignature = $landlordContractSignaturePath !== '' && Storage::disk('public')->exists($landlordContractSignaturePath);
      $landlordContractSignatureUrl = $hasLandlordContractSignature ? asset('storage/' . $landlordContractSignaturePath) : null;
      $landlordSignerName = trim((string) ($onboarding->landlord_contract_signature_name ?? ($landlordUser->full_name ?? $landlordUser->name ?? 'Landlord')));
      $landlordProfileSignaturePath = (string) (optional(optional($landlordUser)->landlordProfile)->contract_signature_path ?? '');
      $hasLandlordProfileSignature = $landlordProfileSignaturePath !== '' && Storage::disk('public')->exists($landlordProfileSignaturePath);
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

    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4 review-hero">
      <div class="review-hero-copy">
        <div class="text-uppercase small text-muted fw-semibold">Onboarding Review</div>
        <h1 class="h3 mb-1">{{ $onboarding->booking->student->full_name }}</h1>
        <div class="text-muted small">Review onboarding status, submitted documents, and approval actions.</div>
      </div>
      <div class="review-top-actions">
        @if($status === 'completed')
          <a href="{{ route('landlord.onboarding.contract', $onboarding) }}" class="btn btn-outline-secondary review-top-action-btn" title="View Contract Sign" aria-label="View Contract Sign">
            <i class="bi bi-file-earmark-text review-action-icon" aria-hidden="true"></i>
            <span class="review-action-text">View Contract Sign</span>
          </a>
          <a href="{{ route('landlord.onboarding.contract_pdf', ['onboarding' => $onboarding, 'download' => 1]) }}" class="btn btn-outline-secondary review-top-action-btn" title="Download PDF" aria-label="Download PDF">
            <i class="bi bi-download review-action-icon" aria-hidden="true"></i>
            <span class="review-action-text">Download PDF</span>
          </a>
          <a href="{{ route('landlord.onboarding.index') }}" class="btn btn-outline-secondary review-top-action-btn" title="Back" aria-label="Back">
            <i class="bi bi-arrow-left review-action-icon" aria-hidden="true"></i>
            <span class="review-action-text">Back</span>
          </a>
        @else
          <a href="{{ route('landlord.onboarding.index') }}" class="btn btn-outline-secondary review-top-action-btn" title="Back" aria-label="Back">
            <i class="bi bi-arrow-left review-action-icon" aria-hidden="true"></i>
            <span class="review-action-text">Back</span>
          </a>
        @endif
      </div>
    </div>

    @if(session('success'))
      <div class="alert alert-success rounded-4 mb-3">{{ session('success') }}</div>
    @endif

    @if(session('error'))
      <div class="alert alert-danger rounded-4 mb-3">{{ session('error') }}</div>
    @endif

    <div class="review-mobile-tabs mb-3" id="reviewMobileTabs">
      <button type="button" class="review-mobile-tab active" data-mobile-tab="overview">Overview</button>
      <button type="button" class="review-mobile-tab" data-mobile-tab="documents">Documents</button>
      <button type="button" class="review-mobile-tab" data-mobile-tab="contract">Contract</button>
      <button type="button" class="review-mobile-tab" data-mobile-tab="deposit">Deposit</button>
    </div>

    <div class="mobile-tab-panel mobile-tab-active" data-mobile-panel="overview">

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
              <span class="meta-chip"><i class="bi bi-door-open"></i>{{ $onboarding->booking->room->room_number }}</span>
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
              <div class="status-note-item {{ $onboarding->landlord_contract_signed ? 'done' : '' }}">
                <i class="bi {{ $onboarding->landlord_contract_signed ? 'bi-check-circle-fill' : 'bi-circle' }}"></i>
                <span>Landlord Signed {{ $onboarding->landlord_contract_signed_at ? 'on ' . $onboarding->landlord_contract_signed_at->format('M d, Y H:i') : '' }}</span>
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
    </div>

    @php
      $activeStep = match ($status) {
        'pending' => 'documents',
        'documents_uploaded', 'contract_signed' => 'contract',
        'deposit_paid', 'completed' => 'deposit',
        default => 'documents',
      };
      $documentsStepDone = $uploadedDocs->isNotEmpty();
      $contractStepDone = $onboarding->contract_signed && $onboarding->landlord_contract_signed;
      $depositStepDone = !empty($onboarding->deposit_paid_at);
      $requiredDocuments = collect((array) ($onboarding->required_documents ?? []))->values();
      $pdfPreviewUrl = route('landlord.onboarding.contract_pdf', ['onboarding' => $onboarding]);
      $pdfDownloadUrl = route('landlord.onboarding.contract_pdf', ['onboarding' => $onboarding, 'download' => 1]);
      $viewerNonce = now()->format('Uu');
      $pdfPreviewViewerBase = $pdfPreviewUrl . (str_contains($pdfPreviewUrl, '?') ? '&' : '?') . 'viewer=fit-width-' . $viewerNonce;
      $pdfViewerUrl = $pdfPreviewViewerBase . '#page=1&view=FitH&zoom=FitH,0,0&pagemode=none&navpanes=0&toolbar=1';
      $pdfOpenTabUrl = $pdfPreviewUrl . '#page=1&view=Fit&zoom=Fit,0,0&toolbar=1';
    @endphp

    <div class="review-stepper-shell mb-4" id="onboardingReviewStepper">
      <button type="button" class="review-step {{ $documentsStepDone ? 'done' : '' }} {{ $activeStep === 'documents' ? 'active' : '' }}" data-review-step="documents">
        <span class="review-step-badge">1</span>
        <span class="review-step-label">Documents</span>
      </button>
      <button type="button" class="review-step {{ $contractStepDone ? 'done' : '' }} {{ $activeStep === 'contract' ? 'active' : '' }}" data-review-step="contract">
        <span class="review-step-badge">2</span>
        <span class="review-step-label">Contract</span>
      </button>
      <button type="button" class="review-step {{ $depositStepDone ? 'done' : '' }} {{ $activeStep === 'deposit' ? 'active' : '' }}" data-review-step="deposit">
        <span class="review-step-badge">3</span>
        <span class="review-step-label">Deposit</span>
      </button>
    </div>

    <div class="review-step-panel mobile-tab-panel {{ $activeStep === 'documents' ? 'active' : '' }}" data-review-panel="documents" data-mobile-panel="documents">
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
                  $requiredDocKey = (string) ($requiredDocuments->get($loop->index) ?? '');
                  $docTypeLabel = match ($requiredDocKey) {
                    'student_id' => 'Student ID',
                    'proof_of_income' => 'Proof Of Income',
                    'emergency_contact' => 'Emergency Contact',
                    default => 'Document ' . ($loop->index + 1),
                  };
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
                    <div class="doc-head">
                      <div class="doc-icon {{ $fileExtension === 'pdf' ? 'doc-icon-pdf' : ($isImage ? 'doc-icon-image' : 'doc-icon-file') }}">
                        @if($isImage)
                          <i class="bi bi-image"></i>
                        @elseif($fileExtension === 'pdf')
                          <i class="bi bi-filetype-pdf"></i>
                        @else
                          <i class="bi bi-file-earmark-text"></i>
                        @endif
                      </div>
                      <div class="doc-type-label">{{ $docTypeLabel }}</div>
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

    <div class="review-step-panel mobile-tab-panel {{ $activeStep === 'contract' ? 'active' : '' }}" data-review-panel="contract" data-mobile-panel="contract">
      <div class="review-card mb-4">
        <div class="review-card-header d-flex justify-content-between align-items-center contract-section-header">
          <h5 class="mb-0">Contract Section</h5>
          <div class="contract-actions">
            <a href="{{ $pdfOpenTabUrl }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary rounded-pill">Open PDF</a>
            <a href="{{ $pdfDownloadUrl }}" class="btn btn-sm btn-outline-secondary rounded-pill">Download PDF</a>
          </div>
        </div>
        <div class="review-card-body">
          <div class="contract-preview-inline-shell mb-3">
            <div class="pdf-frame-shell-inline">
              <iframe
                src="{{ $pdfViewerUrl }}"
                class="pdf-frame-inline"
                title="Onboarding Contract PDF"
              ></iframe>

              <div class="preview-fallback small text-muted">
                If the preview does not load, open it directly:
                <a href="{{ $pdfOpenTabUrl }}" target="_blank" rel="noopener">View PDF</a>
              </div>
            </div>
          </div>

          <div class="row g-3 mt-1">
            <div class="col-12 col-lg-6">
              <div class="small text-muted mb-1">Tenant Signature</div>
              @if($contractSignerName !== '')
                <div class="small mb-2"><strong>Signed as:</strong> {{ $contractSignerName }}</div>
              @endif
              @if($hasContractSignature && $contractSignatureUrl)
                <div class="small text-muted mb-1">Tenant e-signature</div>
                <div class="border rounded-4 p-3 text-center" style="background:#fff;">
                  <img src="{{ $contractSignatureUrl }}" alt="Tenant e-signature" style="max-width:100%;max-height:180px;object-fit:contain;">
                </div>
              @else
                <div class="alert alert-warning rounded-3 mb-0">No tenant e-signature image is available for this onboarding record.</div>
              @endif
            </div>

            <div class="col-12 col-lg-6">
              <div class="small text-muted mb-1">Landlord Signature</div>
              @if($landlordSignerName !== '')
                <div class="small mb-2"><strong>Signed as:</strong> {{ $landlordSignerName }}</div>
              @endif
              @if($hasLandlordContractSignature && $landlordContractSignatureUrl)
                <div class="small text-muted mb-1">Landlord e-signature</div>
                <div class="border rounded-4 p-3 text-center" style="background:#fff;">
                  <img src="{{ $landlordContractSignatureUrl }}" alt="Landlord e-signature" style="max-width:100%;max-height:180px;object-fit:contain;">
                </div>
              @else
                <div class="alert alert-warning rounded-3 mb-0">No landlord e-signature is embedded yet.</div>
              @endif
            </div>
          </div>

          <div class="mt-3 pt-3 border-top">
            @if(!$onboarding->landlord_contract_signed)
              @if($hasLandlordProfileSignature)
                <form method="POST" action="{{ route('landlord.onboarding.sign_contract', $onboarding) }}">
                  @csrf
                  <button type="submit" class="btn btn-brand rounded-pill px-3">Sign this contract</button>
                </form>
                <div class="small text-muted mt-2">Uses your saved e-signature from Landlord Profile.</div>
              @else
                <div class="alert alert-warning rounded-3 mb-2">
                  <i class="bi bi-info-circle me-2"></i>
                  Upload your e-signature in profile before signing this contract.
                </div>
                <a href="{{ route('landlord.profile.edit') }}" class="btn btn-sm btn-outline-secondary rounded-pill">Open Profile to Upload E-signature</a>
              @endif
            @else
              <div class="alert alert-success rounded-3 mb-0">
                <i class="bi bi-check-circle me-2"></i>
                Contract signed by landlord on {{ optional($onboarding->landlord_contract_signed_at)->format('M d, Y h:i A') ?: 'recorded time' }}.
              </div>
            @endif
          </div>
        </div>
      </div>
    </div>

    <div class="review-step-panel mobile-tab-panel {{ $activeStep === 'deposit' ? 'active' : '' }}" data-review-panel="deposit" data-mobile-panel="deposit">
      <div class="review-card mb-4">
        <div class="review-card-header">
          <h5 class="mb-0">Deposit Section</h5>
        </div>
        <div class="review-card-body">
          @if($paymentReviewPending)
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
          @else
            <div class="alert {{ $onboarding->deposit_paid ? 'alert-success' : 'alert-warning' }} rounded-3 mb-3">
              @if($onboarding->deposit_paid)
                <i class="bi bi-check-circle me-2"></i>
                Deposit already approved on {{ optional($onboarding->deposit_paid_at)->format('M d, Y h:i A') ?: 'recorded time' }}.
              @else
                <i class="bi bi-info-circle me-2"></i>
                No payment review is pending for this onboarding yet.
              @endif
            </div>

            <div class="payment-review-banner mb-3">
              <div class="payment-review-title">Payment Details</div>
              <div class="payment-review-copy">Submitted payment details are shown below for record tracking.</div>
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

            <div class="row g-3">
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

            <div class="small text-muted mt-3">
              Formula: Total Submitted = Deposit Amount + Advance Amount (PHP {{ number_format($submittedDepositAmount, 2) }} + PHP {{ number_format($submittedAdvanceAmount, 2) }})
            </div>

            @if(!empty($onboarding->payment_notes))
              <div class="alert alert-light border rounded-3 mt-3 mb-0">
                <div class="small text-muted mb-1">Student Payment Notes</div>
                <div>{{ $onboarding->payment_notes }}</div>
              </div>
            @endif

            @if(!empty($onboarding->payment_proof_path))
              <div class="mt-3">
                <a href="{{ asset('storage/' . $onboarding->payment_proof_path) }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary rounded-pill">View Payment Proof</a>
              </div>
            @endif
          @endif
        </div>
      </div>
    </div>
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
  .review-top-actions {
    display: flex;
    flex-wrap: wrap;
    gap: .5rem;
  }
  .review-action-icon {
    display: none;
  }
  .contract-actions {
    display: flex;
    flex-wrap: wrap;
    gap: .5rem;
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
  .doc-head {
    display: flex;
    align-items: center;
    gap: .55rem;
    min-height: 38px;
  }
  .doc-name {
    font-weight: 700;
    color: #14532d;
    line-height: 1.2;
  }
  .doc-type-label {
    font-size: .78rem;
    font-weight: 800;
    color: #14532d;
    text-transform: uppercase;
    letter-spacing: .045em;
    line-height: 1.1;
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
  .contract-preview-inline-shell {
    background: #eef2f6;
    border: 1px solid rgba(2, 8, 20, .1);
    border-radius: .9rem;
    padding: .75rem;
  }
  .preview-meta {
    display: flex;
    flex-wrap: wrap;
    gap: .45rem;
  }
  .meta-pill {
    display: inline-flex;
    align-items: center;
    gap: .35rem;
    border: 1px solid rgba(2, 8, 20, .12);
    border-radius: 999px;
    background: #f8fafc;
    color: #0f172a;
    padding: .2rem .6rem;
    font-size: .78rem;
    font-weight: 600;
  }
  .pdf-frame-shell-inline {
    background: #ffffff;
    border: 1px solid #d1d5db;
    border-radius: .8rem;
    box-shadow: 0 10px 24px rgba(2, 8, 20, .1);
    overflow: hidden;
  }
  .pdf-frame-inline {
    display: block;
    width: 100%;
    height: min(72vh, 900px);
    min-height: 540px;
    border: 0;
    background: #e2e8f0;
  }
  .preview-fallback {
    border-top: 1px solid #e2e8f0;
    padding: .55rem .75rem;
    background: #f8fafc;
  }
  .review-stepper-shell {
    border: 1px solid rgba(2,8,20,.1);
    border-radius: .95rem;
    background: linear-gradient(180deg, #ffffff, #f8fafc);
    padding: .85rem;
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: .55rem;
  }
  .review-step {
    border: 1px solid rgba(2,8,20,.12);
    border-radius: .8rem;
    background: #fff;
    padding: .58rem .62rem;
    display: flex;
    align-items: center;
    gap: .5rem;
    text-align: left;
    cursor: pointer;
    transition: border-color .2s ease, background-color .2s ease, box-shadow .2s ease;
  }
  .review-step-badge {
    width: 26px;
    height: 26px;
    border-radius: 50%;
    border: 2px solid #cbd5e1;
    color: #64748b;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: .76rem;
    font-weight: 700;
    flex: 0 0 auto;
    background: #fff;
  }
  .review-step-label {
    font-size: .82rem;
    font-weight: 700;
    color: #0f172a;
  }
  .review-step.active {
    border-color: rgba(20,83,45,.38);
    background: rgba(240,253,244,.8);
    box-shadow: 0 0 0 .16rem rgba(20,83,45,.12);
  }
  .review-step.active .review-step-badge {
    border-color: #14532d;
    color: #14532d;
  }
  .review-step.done {
    border-color: rgba(22,163,74,.32);
    background: rgba(220,252,231,.65);
  }
  .review-step.done .review-step-badge {
    border-color: #16a34a;
    background: #16a34a;
    color: #fff;
  }
  .review-step-panel {
    display: none;
  }
  .review-step-panel.active {
    display: block;
  }
  .review-mobile-tabs {
    display: none;
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
    .review-hero {
      position: relative;
    }
    .review-hero-copy {
      padding-right: 6.7rem;
      width: 100%;
    }
    .review-top-actions {
      position: absolute;
      top: .1rem;
      right: 0;
      display: inline-flex !important;
      align-items: center;
      justify-content: flex-start;
      gap: .28rem;
      width: auto;
      padding: 0;
      border: 0;
      border-radius: 0;
      background: transparent;
      box-shadow: none;
    }
    .review-top-actions .btn {
      width: auto;
      text-align: center;
      min-width: 0;
      line-height: 1;
    }
    .review-top-actions .review-top-action-btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 32px !important;
      min-width: 32px !important;
      height: 32px !important;
      min-height: 32px !important;
      flex: 0 0 32px;
      aspect-ratio: 1 / 1;
      padding: 0 !important;
      border: 1px solid rgba(100,116,139,.45) !important;
      border-radius: 50% !important;
      background: #f8fafc !important;
      box-shadow: none !important;
      color: #475569;
    }
    .review-top-action-btn .review-action-icon {
      display: inline-flex;
      font-size: .88rem;
      line-height: 1;
    }
    .review-top-action-btn:hover,
    .review-top-action-btn:focus {
      color: #0f172a;
      background: #ffffff !important;
      border-color: rgba(15,23,42,.55) !important;
      box-shadow: 0 1px 6px rgba(2,8,20,.12) !important;
    }
    .review-top-action-btn .review-action-text {
      display: none;
    }
    .review-mobile-tabs {
      display: grid;
      grid-template-columns: repeat(4, minmax(0, 1fr));
      gap: .38rem;
      border: 1px solid rgba(2,8,20,.12);
      border-radius: .95rem;
      padding: .42rem;
      background: linear-gradient(180deg, #f8fafc, #eef2f7);
      box-shadow: inset 0 1px 0 rgba(255,255,255,.65);
    }
    .review-mobile-tab {
      border: 1px solid rgba(2,8,20,.14);
      background: #ffffff;
      color: #334155;
      border-radius: .65rem;
      padding: .5rem .3rem;
      font-size: .72rem;
      font-weight: 700;
      text-align: center;
      line-height: 1.15;
      transition: all .2s ease;
    }
    .review-mobile-tab.active {
      border-color: rgba(20,83,45,.42);
      background: linear-gradient(180deg, rgba(220,252,231,.96), rgba(187,247,208,.76));
      color: #14532d;
      box-shadow: 0 2px 8px rgba(20,83,45,.16);
      transform: translateY(-1px);
    }
    .contract-section-header {
      display: block !important;
    }
    .contract-section-header h5 {
      margin-bottom: .6rem !important;
    }
    .contract-actions {
      display: grid !important;
      grid-template-columns: repeat(2, minmax(0, 1fr));
      gap: .5rem;
      width: 100%;
    }
    .contract-actions .btn {
      width: 100%;
      text-align: center;
      padding-left: .4rem;
      padding-right: .4rem;
      min-width: 0;
    }
    .review-summary {
      grid-template-columns: repeat(2, minmax(0, 1fr));
    }
    .payment-breakdown-grid {
      grid-template-columns: 1fr;
    }
    .review-stepper-shell {
      display: none;
    }
    .mobile-tab-panel {
      display: none !important;
    }
    .mobile-tab-panel.mobile-tab-active {
      display: block !important;
    }
    .pdf-frame-inline {
      height: 62vh;
      min-height: 460px;
    }
  }

  @media (max-width: 575.98px) {
    .onboarding-review-shell {
      width: calc(100vw - 1rem);
      margin-left: calc(50% - 50vw + .5rem);
      margin-right: calc(50% - 50vw + .5rem);
      border-radius: 1rem;
    }
    .review-summary {
      grid-template-columns: repeat(2, minmax(0, 1fr));
    }
    .doc-actions {
      display: grid;
      grid-template-columns: 1fr;
    }
    .doc-actions .btn {
      width: 100%;
    }
    .review-mobile-tabs {
      grid-template-columns: repeat(2, minmax(0, 1fr));
    }
  }
</style>
@endpush

@push('scripts')
<script>
  (function () {
    const stepButtons = Array.from(document.querySelectorAll('[data-review-step]'));
    const stepPanels = Array.from(document.querySelectorAll('[data-review-panel]'));
    const mobileTabs = Array.from(document.querySelectorAll('[data-mobile-tab]'));
    const mobilePanels = Array.from(document.querySelectorAll('[data-mobile-panel]'));

    const setActiveStep = function (stepName) {
      stepButtons.forEach(function (button) {
        const isActive = button.getAttribute('data-review-step') === stepName;
        button.classList.toggle('active', isActive);
      });

      stepPanels.forEach(function (panel) {
        const isActive = panel.getAttribute('data-review-panel') === stepName;
        panel.classList.toggle('active', isActive);
      });
    };

    const setMobileTab = function (tabName) {
      mobileTabs.forEach(function (button) {
        const isActive = button.getAttribute('data-mobile-tab') === tabName;
        button.classList.toggle('active', isActive);
      });

      mobilePanels.forEach(function (panel) {
        const isActive = panel.getAttribute('data-mobile-panel') === tabName;
        panel.classList.toggle('mobile-tab-active', isActive);
      });

      if (tabName !== 'overview' && stepButtons.length && stepPanels.length) {
        setActiveStep(tabName);
      }
    };

    if (stepButtons.length && stepPanels.length) {
      stepButtons.forEach(function (button) {
        button.addEventListener('click', function () {
          const stepName = button.getAttribute('data-review-step');
          if (!stepName) return;
          setActiveStep(stepName);
        });
      });
    }

    if (mobileTabs.length && mobilePanels.length) {
      mobileTabs.forEach(function (button) {
        button.addEventListener('click', function () {
          const tabName = button.getAttribute('data-mobile-tab');
          if (!tabName) return;
          setMobileTab(tabName);
        });
      });

      if (window.matchMedia('(max-width: 991.98px)').matches) {
        setMobileTab('overview');
      }
    }
  })();
</script>
@endpush
