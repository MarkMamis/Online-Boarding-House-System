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
    background: #f8fafc;
    border: 1px solid rgba(2,8,20,.08);
    border-radius: 1rem;
    padding: 1.5rem;
  }

  .contract-header {
    border-bottom: 1px dashed rgba(15,23,42,.2);
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
    background: #fff;
    border: 1px solid rgba(2,8,20,.08);
    border-radius: .85rem;
    padding: 1rem 1.25rem;
    margin-bottom: 1rem;
  }

  .contract-section h6 {
    font-weight: 700;
    color: #0f172a;
    margin-bottom: .65rem;
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

  .document-card {
    background: #fff;
    border: 1px solid rgba(2,8,20,.08);
    border-radius: 1rem;
    padding: 1.5rem;
    text-align: center;
    transition: all .2s;
  }

  .document-card:hover {
    border-color: rgba(22,101,52,.3);
    box-shadow: 0 4px 12px rgba(22,101,52,.1);
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
</style>
@endpush

@section('content')
<div>
  <div class="glass-card rounded-4 p-4 p-md-5 mb-4">
    <div class="mb-4">
      <h4 class="fw-bold mb-1">Onboarding Process</h4>
      <p class="text-muted mb-0">{{ $onboarding->booking->room->property->name }} — Room {{ $onboarding->booking->room->room_number }}</p>
    </div>

    @if(session('success'))
      <div class="alert alert-info rounded-3 mb-4" role="alert">
        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
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
      
      <div class="mb-4">
        <div class="list-group list-group-flush">
          @foreach($onboarding->required_documents ?? [] as $doc)
            <div class="list-group-item border-0 ps-0 py-2 d-flex align-items-center">
              <i class="bi bi-check-circle-fill text-success me-2"></i>
              <span>{{ ucfirst(str_replace('_', ' ', $doc)) }}</span>
            </div>
          @endforeach
        </div>
      </div>

      <form method="POST" action="{{ route('student.onboarding.upload_documents', $onboarding) }}" enctype="multipart/form-data">
        @csrf
        <div class="mb-3">
          <label for="documents" class="form-label fw-600">Select Documents</label>
          <div class="input-group">
            <input type="file" class="form-control rounded-pill" id="documents" name="documents[]" multiple accept=".pdf,.jpg,.jpeg,.png" required style="padding: 0.65rem 1rem;">
          </div>
          <small class="text-muted d-block mt-2">
            <i class="bi bi-info-circle me-1"></i>
            PDF, JPG, PNG allowed • Max 5MB each • Select multiple files at once
          </small>
        </div>
        <button type="submit" class="btn btn-brand rounded-pill px-4">
          <i class="bi bi-upload me-1"></i>Upload Documents
        </button>
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

      <div class="contract-shell mb-4">
        <div class="contract-header">
          <div class="d-flex align-items-start justify-content-between flex-wrap gap-3">
            <div>
              <div class="text-uppercase small text-muted">Residential Lodging Agreement</div>
              <h5 class="fw-bold mb-1">Tenant Contract Preview</h5>
              <div class="text-muted small">Generated for onboarding #{{ $onboarding->id }}</div>
            </div>
            <div class="contract-highlight">
              Term: {{ $onboarding->booking->check_in_date }} to {{ $onboarding->booking->check_out_date ?? 'Open Ended' }}
            </div>
          </div>
          <div class="contract-meta mt-3">
            <div><strong>Property:</strong> {{ $onboarding->booking->room->property->name }}</div>
            <div><strong>Room:</strong> {{ $onboarding->booking->room->room_number }}</div>
            <div><strong>Monthly Rent:</strong> ₱{{ number_format($onboarding->booking->room->price, 2) }}</div>
            <div><strong>Security Deposit:</strong> ₱{{ number_format($onboarding->deposit_amount, 2) }}</div>
          </div>
        </div>

        <div class="contract-section">
          <h6>Parties</h6>
          <p class="mb-0">
            This agreement is between <strong>{{ $onboarding->booking->room->property->landlord->name ?? 'The Landlord' }}</strong>
            and <strong>{{ auth()->user()->name }}</strong> ("Tenant"). The Tenant agrees to occupy the room listed above
            and to follow all community policies.
          </p>
        </div>

        <div class="contract-section">
          <h6>Key Terms</h6>
          <ul class="contract-list">
            <li>Rent is due on the 1st of each month and payable through the platform.</li>
            <li>Security deposit will be held for damages and may be deducted after checkout.</li>
            <li>Quiet hours are 10:00 PM to 7:00 AM. Guests must be registered with the landlord.</li>
            <li>Maintenance requests should be submitted within 24 hours of noticing an issue.</li>
          </ul>
        </div>

        <div class="contract-section">
          <h6>House Rules Summary</h6>
          <div class="row g-3">
            <div class="col-12 col-md-4">
              <div class="contract-highlight">No smoking inside rooms</div>
            </div>
            <div class="col-12 col-md-4">
              <div class="contract-highlight">No unregistered overnight guests</div>
            </div>
            <div class="col-12 col-md-4">
              <div class="contract-highlight">Maintain cleanliness of shared areas</div>
            </div>
          </div>
        </div>

        <div class="contract-section mb-0">
          <h6>Important Notes</h6>
          <p class="mb-0 text-muted">
            This is a mock contract preview for onboarding. The final signed agreement will be stored in your profile once submitted.
          </p>
        </div>
      </div>

      <div class="mb-4">
        <label class="form-label fw-600 mb-2">E-signature</label>
        <div class="signature-grid">
          <div>
            <div class="signature-pad">Draw signature here</div>
            <div class="signature-actions mt-2">
              <button type="button" class="btn btn-outline-secondary btn-sm">Clear</button>
              <button type="button" class="btn btn-outline-secondary btn-sm">Upload</button>
            </div>
            <div class="signature-hint mt-2">Mockup only. Signature capture will be enabled in production.</div>
          </div>
          <div>
            <label class="form-label fw-600">Typed Signature</label>
            <input type="text" class="form-control" placeholder="Type your full name" value="{{ auth()->user()->name }}" />
            <label class="form-label fw-600 mt-3">Date Signed</label>
            <input type="text" class="form-control" value="{{ now()->format('F j, Y') }}" readonly />
          </div>
        </div>
      </div>

      <form method="POST" action="{{ route('student.onboarding.sign_contract', $onboarding) }}">
        @csrf
        <div class="form-check mb-4">
          <input class="form-check-input" type="checkbox" id="agree" required style="width: 1.25rem; height: 1.25rem; border-color: rgba(22,101,52,.3);">
          <label class="form-check-label" for="agree" style="margin-left: 0.5rem;">
            <span class="fw-500">I agree to the terms and conditions</span> outlined in this lease contract
          </label>
        </div>
        <button type="submit" class="btn btn-brand rounded-pill px-4">
          <i class="bi bi-pen me-1"></i>Sign Contract
        </button>
      </form>
    </div>
  @endif

  <!-- Step 3: Deposit Payment -->
  @if($onboarding->status === 'contract_signed')
    <div class="content-card">
      <h4>
        <i class="bi bi-credit-card me-2" style="color: var(--brand);"></i>
        Step 3: Pay Security Deposit
      </h4>
      
      <div class="alert alert-info rounded-3 mb-4">
        <div class="d-flex align-items-center">
          <div>
            <strong class="d-block">Deposit Amount: ₱{{ number_format($onboarding->deposit_amount, 2) }}</strong>
            <small>This is a 50% deposit of your monthly rent (₱{{ number_format($onboarding->booking->room->price, 2) }}/month)</small>
          </div>
        </div>
      </div>

      <div class="bg-light rounded-3 p-3 mb-4">
        <p class="text-muted mb-0">
          <i class="bi bi-info-circle me-2"></i>
          In a real application, you would be redirected to a payment gateway. For this demo, click simulate to mark deposit as paid.
        </p>
      </div>

      <form method="POST" action="{{ route('student.onboarding.pay_deposit', $onboarding) }}">
        @csrf
        <button type="submit" class="btn btn-brand rounded-pill px-4">
          <i class="bi bi-check-circle me-1"></i>Simulate Deposit Payment
        </button>
      </form>
    </div>
  @endif

  <!-- Completion Status -->
  @if($onboarding->status === 'completed')
    <div class="completion-banner">
      <i class="bi bi-check-circle-fill" style="font-size: 2.5rem;"></i>
      <h3>Onboarding Completed!</h3>
      <p class="mb-0">You're all set to move into your new home.</p>
    </div>

    <div class="content-card">
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

      <div class="row g-3 mt-2">
        <div class="col-md-6">
          <div class="border-start border-3 ps-3" style="border-color: var(--brand);">
            <small class="text-muted d-block">Property</small>
            <strong>{{ $onboarding->booking->room->property->name }}</strong>
          </div>
        </div>
        <div class="col-md-6">
          <div class="border-start border-3 ps-3" style="border-color: var(--brand);">
            <small class="text-muted d-block">Room</small>
            <strong>{{ $onboarding->booking->room->room_number }}</strong>
          </div>
        </div>
        <div class="col-md-6">
          <div class="border-start border-3 ps-3" style="border-color: var(--brand);">
            <small class="text-muted d-block">Lease Period</small>
            <strong>{{ $onboarding->booking->check_in->format('M d, Y') }} — {{ $onboarding->booking->check_out->format('M d, Y') }}</strong>
          </div>
        </div>
        <div class="col-md-6">
          <div class="border-start border-3 ps-3" style="border-color: var(--brand);">
            <small class="text-muted d-block">Deposit Paid</small>
            <strong>₱{{ number_format($onboarding->deposit_amount, 2) }}</strong>
          </div>
        </div>
      </div>
    </div>
  @endif


  <!-- Uploaded Documents (if any) -->
  @if($onboarding->uploaded_documents && count($onboarding->uploaded_documents) > 0)
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

              <h6 class="fw-600 mb-1">{{ $fileName }}</h6>
              <p class="text-muted small mb-3">{{ $fileSizeFormatted }}</p>

              <div class="d-flex gap-2 justify-content-center">
                <a href="{{ route('documents.view', ['onboarding' => $onboarding->id, 'filename' => $fileName]) }}" target="_blank" class="btn btn-sm btn-brand rounded-pill px-3">
                  <i class="bi bi-eye me-1"></i>View
                </a>
                <a href="{{ route('documents.view', ['onboarding' => $onboarding->id, 'filename' => $fileName]) }}?download=1" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                  <i class="bi bi-download me-1"></i>Download
                </a>
              </div>

              @if(in_array($fileExtension, ['jpg', 'jpeg', 'png', 'gif']))
                <div class="mt-3">
                  <img src="{{ route('documents.view', ['onboarding' => $onboarding->id, 'filename' => $fileName]) }}" alt="Document Preview" class="img-thumbnail rounded" style="max-height: 120px; max-width: 100%; object-fit: cover;">
                </div>
              @endif
            </div>
          </div>
        @endforeach
      </div>
    </div>
  @endif
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
@endpush

@endsection