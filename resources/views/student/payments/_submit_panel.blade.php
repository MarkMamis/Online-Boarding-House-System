@php
  $renderMode = $renderMode ?? 'modal';
  $isMobileSubmitView = $renderMode === 'mobile-page';
  $queryView = $isMobileSubmitView ? 'submit' : 'list';
@endphp

@if($isMobileSubmitView)
  <div class="d-flex align-items-center justify-content-between mb-3">
    <div>
      <div class="text-uppercase small text-muted fw-semibold">Payment Entry</div>
      <h5 class="fw-bold mb-0">Submit Monthly Payment</h5>
    </div>
    <a href="{{ route('student.payments.index', ['booking_id' => optional($selectedBooking)->id]) }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
      <i class="bi bi-arrow-left me-1"></i>Back
    </a>
  </div>
@endif

<div class="tenant-pay-card {{ $isMobileSubmitView ? '' : 'mb-0' }}">
  <div class="tenant-pay-card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
    <h5 class="mb-0">Submit Next Monthly Payment</h5>
    @if($selectedBooking)
      <span class="small text-muted">{{ $selectedBooking->room->property->name }} • Room {{ $selectedBooking->room->room_number }}</span>
    @endif
  </div>
  <div class="tenant-pay-card-body">
    <form method="GET" action="{{ route('student.payments.index') }}" class="row g-2 align-items-end mb-3">
      <input type="hidden" name="view" value="{{ $queryView }}">
      <div class="col-12 col-md-8">
        <label class="form-label small text-muted fw-semibold mb-1">Active Booking</label>
        <select class="form-select" name="booking_id" onchange="this.form.submit()">
          @foreach($tenantBookings as $bookingOption)
            <option value="{{ $bookingOption->id }}" @selected(!empty($selectedBooking) && (int) $selectedBooking->id === (int) $bookingOption->id)>
              {{ $bookingOption->room->property->name }} - Room {{ $bookingOption->room->room_number }} (Due {{ optional($bookingOption->resolvePaymentDueDate())->format('M d, Y') ?? 'N/A' }})
            </option>
          @endforeach
        </select>
      </div>
    </form>

    @if($selectedBooking)
      <div class="alert alert-info rounded-3 mb-3">
        <strong class="d-block">Amount Due: PHP {{ number_format((float) $nextDueAmount, 2) }}</strong>
        <small>Billing Month: {{ optional($nextDueDate)->format('F Y') ?? 'N/A' }}</small>
      </div>

      @if($hasSubmittedForDue)
        <div class="alert alert-warning rounded-3 mb-0">
          <i class="bi bi-hourglass-split me-2"></i>
          A payment submission already exists for this billing month. Please wait for landlord review.
        </div>
      @elseif($availablePaymentMethods->isEmpty())
        <div class="alert alert-warning rounded-3 mb-0">
          <i class="bi bi-exclamation-circle me-2"></i>
          The landlord has not configured a valid payment method yet. Please contact your landlord.
        </div>
      @else
        <form method="POST" action="{{ route('student.payments.store') }}" enctype="multipart/form-data" id="monthlyPaymentForm">
          @csrf
          <input type="hidden" name="booking_id" value="{{ $selectedBooking->id }}">

          <div class="mb-3">
            <label class="form-label fw-semibold">Select Payment Method</label>
            <div class="pay-method-grid">
              @if($availablePaymentMethods->contains('bank'))
                <label class="pay-method-card {{ old('payment_method') === 'bank' ? 'selected' : '' }}">
                  <input type="radio" name="payment_method" value="bank" {{ old('payment_method') === 'bank' ? 'checked' : '' }} required>
                  <div class="pay-method-title"><i class="bi bi-bank me-1"></i>Bank Transfer</div>
                  <div class="pay-method-sub">Send via bank account details</div>
                </label>
              @endif
              @if($availablePaymentMethods->contains('gcash'))
                <label class="pay-method-card {{ old('payment_method') === 'gcash' ? 'selected' : '' }}">
                  <input type="radio" name="payment_method" value="gcash" {{ old('payment_method') === 'gcash' ? 'checked' : '' }} required>
                  <div class="pay-method-title"><i class="bi bi-phone me-1"></i>GCash</div>
                  <div class="pay-method-sub">Use wallet number or QR</div>
                </label>
              @endif
              @if($availablePaymentMethods->contains('cash'))
                <label class="pay-method-card {{ old('payment_method') === 'cash' ? 'selected' : '' }}">
                  <input type="radio" name="payment_method" value="cash" {{ old('payment_method') === 'cash' ? 'checked' : '' }} required>
                  <div class="pay-method-title"><i class="bi bi-cash-stack me-1"></i>Cash</div>
                  <div class="pay-method-sub">Pay directly to landlord</div>
                </label>
              @endif
            </div>
          </div>

          @if($availablePaymentMethods->contains('bank'))
            <div class="pay-channel-detail mb-3 {{ old('payment_method') === 'bank' ? '' : 'd-none' }}" data-payment-panel="bank">
              <div class="row g-2">
                <div class="col-md-4">
                  <div class="pay-detail-label">Bank</div>
                  <div class="pay-detail-value">{{ $landlordProfile->payment_bank_name }}</div>
                </div>
                <div class="col-md-4">
                  <div class="pay-detail-label">Account Name</div>
                  <div class="pay-detail-value">{{ $landlordProfile->payment_account_name }}</div>
                </div>
                <div class="col-md-4">
                  <div class="pay-detail-label">Account Number</div>
                  <div class="pay-detail-value">{{ $landlordProfile->payment_account_number }}</div>
                </div>
              </div>
            </div>
          @endif

          @if($availablePaymentMethods->contains('gcash'))
            <div class="pay-channel-detail mb-3 {{ old('payment_method') === 'gcash' ? '' : 'd-none' }}" data-payment-panel="gcash">
              <div class="row g-2">
                <div class="col-md-6">
                  <div class="pay-detail-label">GCash Name</div>
                  <div class="pay-detail-value">{{ $landlordProfile->payment_gcash_name }}</div>
                </div>
                <div class="col-md-6">
                  <div class="pay-detail-label">GCash Number</div>
                  <div class="pay-detail-value">{{ $landlordProfile->payment_gcash_number }}</div>
                </div>
              </div>
            </div>
          @endif

          @if($availablePaymentMethods->contains('cash'))
            <div class="pay-channel-detail mb-3 {{ old('payment_method') === 'cash' ? '' : 'd-none' }}" data-payment-panel="cash">
              <div class="pay-detail-value">Cash payment selected</div>
              <div class="small text-muted mt-1">Coordinate with your landlord for handover and official receipt.</div>
            </div>
          @endif

          <div id="onlinePaymentFields" class="row g-3 mb-3 {{ in_array(old('payment_method'), ['bank','gcash'], true) ? '' : 'd-none' }}">
            <div class="col-12 col-md-6">
              <label for="paymentReferenceInput" class="form-label fw-semibold">Reference Number</label>
              <input type="text" id="paymentReferenceInput" name="payment_reference" value="{{ old('payment_reference') }}" class="form-control" maxlength="120" placeholder="Transaction/reference number">
            </div>
            <div class="col-12 col-md-6">
              <label for="paymentProofInput" class="form-label fw-semibold">Upload Payment Proof</label>
              <input type="file" id="paymentProofInput" name="payment_proof" class="form-control" accept=".jpg,.jpeg,.png,.pdf">
              <div class="small text-muted mt-1">JPG, PNG, or PDF up to 5MB.</div>
            </div>
          </div>

          <div class="mb-3">
            <label for="paymentNotesInput" class="form-label fw-semibold">Notes (optional)</label>
            <textarea id="paymentNotesInput" name="payment_notes" class="form-control" rows="3" maxlength="1000" placeholder="Additional payment details for landlord review">{{ old('payment_notes') }}</textarea>
          </div>

          <button type="submit" class="btn btn-success rounded-pill px-4">
            <i class="bi bi-send-check me-1"></i>Submit Monthly Payment
          </button>
        </form>
      @endif
    @endif
  </div>
</div>
