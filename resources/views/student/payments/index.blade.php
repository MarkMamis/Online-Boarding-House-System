@extends('layouts.student_dashboard')

@section('title', 'My Payments')

@push('styles')
<style>
  .tenant-pay-shell {
    background: linear-gradient(180deg, #ffffff 0%, #fbfdfc 100%);
    border: 1px solid rgba(2,8,20,.08);
    border-radius: 1.25rem;
    box-shadow: 0 10px 26px rgba(2,8,20,.06);
    padding: 1.1rem;
  }
  .tenant-pay-summary {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: .75rem;
  }
  .tenant-pay-summary-item {
    border: 1px solid rgba(20,83,45,.16);
    background: linear-gradient(180deg, rgba(167,243,208,.18), rgba(255,255,255,.85));
    border-radius: .9rem;
    padding: .72rem .82rem;
  }
  .tenant-pay-summary-label {
    font-size: .72rem;
    text-transform: uppercase;
    letter-spacing: .05em;
    color: rgba(2,8,20,.55);
    font-weight: 700;
    margin-bottom: .2rem;
  }
  .tenant-pay-summary-value {
    font-size: .98rem;
    font-weight: 700;
    color: #14532d;
  }
  .tenant-pay-card {
    border: 1px solid rgba(2,8,20,.09);
    border-radius: 1rem;
    background: #fff;
    overflow: hidden;
  }
  .tenant-pay-card-header {
    border-bottom: 1px solid rgba(2,8,20,.08);
    padding: .85rem 1rem;
    background: rgba(248,250,252,.72);
  }
  .tenant-pay-card-body {
    padding: 1rem;
  }
  .pay-method-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: .75rem;
  }
  .pay-method-card {
    position: relative;
    border: 1px solid rgba(2,8,20,.14);
    border-radius: .85rem;
    background: #fff;
    padding: .85rem .95rem;
    cursor: pointer;
    transition: border-color .18s ease, box-shadow .18s ease, transform .18s ease;
  }
  .pay-method-card:hover {
    border-color: rgba(22,101,52,.45);
    transform: translateY(-1px);
  }
  .pay-method-card.selected {
    border-color: rgba(22,101,52,.7);
    box-shadow: 0 0 0 3px rgba(22,101,52,.12);
    background: linear-gradient(180deg, rgba(167,243,208,.26), rgba(255,255,255,.95));
  }
  .pay-method-card input[type='radio'] {
    position: absolute;
    opacity: 0;
    pointer-events: none;
  }
  .pay-method-title {
    font-weight: 700;
    color: #0f172a;
  }
  .pay-method-sub {
    font-size: .78rem;
    color: #64748b;
  }
  .pay-channel-detail {
    border: 1px solid rgba(2,8,20,.08);
    border-radius: .85rem;
    background: #fff;
    padding: .9rem 1rem;
  }
  .pay-detail-label {
    font-size: .72rem;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: .05em;
    font-weight: 700;
  }
  .pay-detail-value {
    font-weight: 700;
    color: #0f172a;
  }
  .pay-status-pill {
    display: inline-flex;
    align-items: center;
    gap: .35rem;
    border-radius: 999px;
    padding: .22rem .62rem;
    font-size: .76rem;
    font-weight: 700;
    border: 1px solid transparent;
  }
  .pay-status-submitted {
    color: #7c2d12;
    background: #ffedd5;
    border-color: #fdba74;
  }
  .pay-status-approved {
    color: #14532d;
    background: #dcfce7;
    border-color: #86efac;
  }
  .pay-status-rejected {
    color: #7f1d1d;
    background: #fee2e2;
    border-color: #fca5a5;
  }
  .record-row {
    border: 1px solid rgba(2,8,20,.09);
    border-radius: .9rem;
    padding: .8rem .9rem;
    display: grid;
    gap: .45rem;
    background: #fff;
  }
  .record-title {
    font-size: 1rem;
    font-weight: 700;
    color: #0f172a;
  }
  .record-metrics {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: .55rem;
  }
  .record-metric {
    border: 1px solid rgba(2,8,20,.08);
    border-radius: .72rem;
    background: #f8fafc;
    padding: .55rem .62rem;
  }
  .record-metric.k-wide {
    grid-column: 1 / -1;
  }
  .record-metric .k {
    font-size: .66rem;
    text-transform: uppercase;
    letter-spacing: .05em;
    color: rgba(2,8,20,.48);
    font-weight: 700;
    margin-bottom: .16rem;
    line-height: 1.2;
  }
  .record-metric .v {
    font-size: .88rem;
    font-weight: 700;
    color: #0f172a;
    line-height: 1.25;
    word-break: break-word;
  }
  .record-proof-link {
    display: inline-flex;
    align-items: center;
    gap: .35rem;
    border: 1px solid rgba(22,101,52,.28);
    border-radius: 999px;
    background: rgba(240,253,244,.78);
    color: #166534;
    text-decoration: none;
    padding: .22rem .62rem;
    font-size: .75rem;
    font-weight: 700;
    width: fit-content;
  }
  .record-proof-link:hover {
    color: #14532d;
    border-color: rgba(22,101,52,.42);
  }
  .record-notes {
    border: 1px solid rgba(2,8,20,.08);
    border-left: 3px solid rgba(22,101,52,.44);
    border-radius: .7rem;
    padding: .56rem .64rem;
    background: rgba(248,250,252,.72);
    font-size: .82rem;
    color: #334155;
  }
  .tenant-pay-actions {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: .75rem;
    border: 1px dashed rgba(20,83,45,.25);
    border-radius: .9rem;
    padding: .8rem .9rem;
    background: linear-gradient(180deg, rgba(240,253,244,.6), rgba(255,255,255,.95));
  }
  .tenant-pay-actions-copy {
    font-size: .84rem;
    color: #475569;
  }
  .tenant-submit-modal .modal-content {
    border: 1px solid rgba(2,8,20,.12);
    border-radius: 1rem;
  }
  .tenant-submit-modal .modal-header {
    border-bottom: 1px solid rgba(2,8,20,.08);
    background: rgba(248,250,252,.72);
  }
  .tenant-submit-modal .modal-body {
    padding: 1rem;
    background: #f8fafc;
  }
  @media (max-width: 991.98px) {
    .tenant-pay-shell {
      padding: .95rem;
    }
    .tenant-pay-summary {
      grid-template-columns: repeat(2, minmax(0, 1fr));
    }
    .tenant-pay-actions {
      flex-direction: column;
      align-items: stretch;
    }
    .tenant-pay-actions .btn {
      width: 100%;
      justify-content: center;
    }
  }
  @media (max-width: 575.98px) {
    .tenant-pay-summary {
      grid-template-columns: repeat(2, minmax(0, 1fr));
      gap: .6rem;
    }
    .tenant-pay-card-header {
      padding: .72rem .78rem;
    }
    .tenant-pay-card-body {
      padding: .78rem;
    }
    .record-row {
      padding: .72rem;
      gap: .5rem;
    }
    .record-title {
      font-size: .98rem;
    }
    .record-metric {
      padding: .52rem .56rem;
    }
    .record-metric .v {
      font-size: .82rem;
    }
  }
  @media (max-width: 389.98px) {
    .tenant-pay-summary {
      grid-template-columns: 1fr;
    }
    .record-metrics {
      grid-template-columns: 1fr;
    }
    .record-metric.k-wide {
      grid-column: auto;
    }
  }
</style>
@endpush

@section('content')
@php
  $selectedStatusClass = null;
  $isSubmitView = ($viewMode ?? 'list') === 'submit';
@endphp
<div class="tenant-pay-shell mb-4">
  <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
    <div>
      <div class="text-uppercase small text-muted fw-semibold">Tenant Finance</div>
      <h4 class="fw-bold mb-1">Monthly Payments</h4>
      <div class="text-muted small">Submit monthly payment for your active room and track payment records.</div>
    </div>
  </div>

  @if(session('success'))
    <div class="alert alert-success rounded-4 mb-3">{{ session('success') }}</div>
  @endif

  @if(session('error'))
    <div class="alert alert-danger rounded-4 mb-3">{{ session('error') }}</div>
  @endif

  @if($errors->any())
    <div class="alert alert-danger rounded-4 mb-3">
      <ul class="mb-0">
        @foreach($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  @if(!$isSubmitView)
    <div class="tenant-pay-summary mb-4">
      <div class="tenant-pay-summary-item">
        <div class="tenant-pay-summary-label">Total Paid</div>
        <div class="tenant-pay-summary-value">PHP {{ number_format((float) $totalPaid, 2) }}</div>
      </div>
      <div class="tenant-pay-summary-item">
        <div class="tenant-pay-summary-label">Pending Review</div>
        <div class="tenant-pay-summary-value">PHP {{ number_format((float) $totalPending, 2) }}</div>
      </div>
      <div class="tenant-pay-summary-item">
        <div class="tenant-pay-summary-label">Pending Submissions</div>
        <div class="tenant-pay-summary-value">{{ (int) $pendingCount }}</div>
      </div>
      <div class="tenant-pay-summary-item">
        <div class="tenant-pay-summary-label">Next Due</div>
        <div class="tenant-pay-summary-value">
          @if($nextDueDate)
            {{ $nextDueDate->format('M d, Y') }}
          @else
            —
          @endif
        </div>
      </div>
    </div>
  @endif

  @if($tenantBookings->isEmpty())
    <div class="tenant-pay-card">
      <div class="tenant-pay-card-body">
        <div class="alert alert-warning rounded-3 mb-0">
          <i class="bi bi-info-circle me-2"></i>
          No active tenant booking with completed onboarding was found. Payments are available only for active tenants.
        </div>
      </div>
    </div>
  @elseif($isSubmitView)
    @include('student.payments._submit_panel', ['renderMode' => 'mobile-page'])
  @else
    @php
      $mobileSubmitParams = ['view' => 'submit'];
      if (!empty($selectedBooking)) {
        $mobileSubmitParams['booking_id'] = $selectedBooking->id;
      }
    @endphp

    <div class="tenant-pay-actions mb-3">
      <div>
        <div class="fw-semibold">Submit Next Monthly Payment</div>
        <div class="tenant-pay-actions-copy">Desktop opens a quick modal. Mobile switches to a focused submit screen.</div>
      </div>
      <div class="d-flex flex-wrap gap-2">
        <button type="button" class="btn btn-success rounded-pill px-3 d-none d-lg-inline-flex" data-bs-toggle="modal" data-bs-target="#submitPaymentModal">
          <i class="bi bi-send-check me-1"></i>Open Payment Form
        </button>
        <a href="{{ route('student.payments.index', $mobileSubmitParams) }}" class="btn btn-success rounded-pill px-3 d-lg-none">
          <i class="bi bi-arrow-right-circle me-1"></i>Go to Submit Page
        </a>
      </div>
    </div>

    <div class="tenant-pay-card">
      <div class="tenant-pay-card-header">
        <h5 class="mb-0">Payment Records</h5>
      </div>
      <div class="tenant-pay-card-body">
        @php
          $hasMonthlyRecords = count($paymentRecords) > 0;
        @endphp

        @if(!empty($onboardingPaymentRecord))
          @php
            $onboardingStatus = strtolower((string) ($onboardingPaymentRecord->status ?? 'submitted'));
            $onboardingStatusClass = match ($onboardingStatus) {
              'approved' => 'pay-status-approved',
              'rejected' => 'pay-status-rejected',
              default => 'pay-status-submitted',
            };
            $onboardingStatusLabel = ucfirst($onboardingStatus);
            $onboardingSubmittedAt = optional($onboardingPaymentRecord->submitted_at)->format('M d, Y h:i A')
              ?: optional($onboardingPaymentRecord->created_at)->format('M d, Y h:i A')
              ?: '—';
          @endphp
          <div class="record-row mb-3 border-success-subtle" style="background: linear-gradient(180deg, rgba(167,243,208,.14), rgba(255,255,255,.98));">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
              <div class="record-title">{{ $onboardingPaymentRecord->label ?? 'Initial Onboarding Payment' }}</div>
              <span class="pay-status-pill {{ $onboardingStatusClass }}">{{ $onboardingStatusLabel }}</span>
            </div>
            <div class="record-metrics">
              <div class="record-metric">
                <div class="k"><i class="bi bi-cash me-1"></i>Amount</div>
                <div class="v">PHP {{ number_format((float) ($onboardingPaymentRecord->amount_due ?? 0), 2) }}</div>
              </div>
              <div class="record-metric">
                <div class="k"><i class="bi bi-credit-card-2-front me-1"></i>Method</div>
                <div class="v">{{ ucfirst((string) ($onboardingPaymentRecord->payment_method ?? '—')) }}</div>
              </div>
              <div class="record-metric">
                <div class="k"><i class="bi bi-calendar-event me-1"></i>Due Date</div>
                <div class="v">{{ optional($onboardingPaymentRecord->due_date)->format('M d, Y') ?: '—' }}</div>
              </div>
              <div class="record-metric">
                <div class="k"><i class="bi bi-hash me-1"></i>Reference</div>
                <div class="v">{{ $onboardingPaymentRecord->payment_reference ?: '—' }}</div>
              </div>
              <div class="record-metric k-wide">
                <div class="k"><i class="bi bi-clock me-1"></i>Submitted</div>
                <div class="v">{{ $onboardingSubmittedAt }}</div>
              </div>
              @if(!empty($onboardingPaymentRecord->payment_proof_path))
                <div class="record-metric k-wide">
                  <div class="k"><i class="bi bi-file-earmark-arrow-down me-1"></i>Payment Proof</div>
                  <a href="{{ asset('storage/' . $onboardingPaymentRecord->payment_proof_path) }}" target="_blank" rel="noopener" class="record-proof-link">
                    <i class="bi bi-box-arrow-up-right"></i>View Proof
                  </a>
                </div>
              @endif
            </div>
            @if(!empty($onboardingPaymentRecord->payment_notes))
              <div class="record-notes">Notes: {{ $onboardingPaymentRecord->payment_notes }}</div>
            @endif
          </div>
        @endif

        @if($hasMonthlyRecords)
          @foreach($paymentRecords as $record)
            @php
              $status = strtolower((string) ($record->status ?? 'submitted'));
              $statusClass = match ($status) {
                'approved' => 'pay-status-approved',
                'rejected' => 'pay-status-rejected',
                default => 'pay-status-submitted',
              };
              $statusLabel = ucfirst($status);
              $submittedAt = optional($record->submitted_at)->format('M d, Y h:i A') ?: optional($record->created_at)->format('M d, Y h:i A');
            @endphp
            <div class="record-row mb-3">
              <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div class="record-title">{{ optional($record->billing_for_date)->format('F Y') ?: 'Billing Record' }}</div>
                <span class="pay-status-pill {{ $statusClass }}">{{ $statusLabel }}</span>
              </div>
              <div class="record-metrics">
                <div class="record-metric">
                  <div class="k"><i class="bi bi-cash me-1"></i>Amount</div>
                  <div class="v">PHP {{ number_format((float) $record->amount_due, 2) }}</div>
                </div>
                <div class="record-metric">
                  <div class="k"><i class="bi bi-credit-card-2-front me-1"></i>Method</div>
                  <div class="v">{{ ucfirst((string) ($record->payment_method ?? '—')) }}</div>
                </div>
                <div class="record-metric">
                  <div class="k"><i class="bi bi-calendar-event me-1"></i>Due Date</div>
                  <div class="v">{{ optional($record->due_date)->format('M d, Y') ?: '—' }}</div>
                </div>
                <div class="record-metric">
                  <div class="k"><i class="bi bi-hash me-1"></i>Reference</div>
                  <div class="v">{{ $record->payment_reference ?: '—' }}</div>
                </div>
                <div class="record-metric k-wide">
                  <div class="k"><i class="bi bi-clock me-1"></i>Submitted</div>
                  <div class="v">{{ $submittedAt }}</div>
                </div>
                @if(!empty($record->payment_proof_path))
                  <div class="record-metric k-wide">
                    <div class="k"><i class="bi bi-file-earmark-arrow-down me-1"></i>Payment Proof</div>
                    <a href="{{ asset('storage/' . $record->payment_proof_path) }}" target="_blank" rel="noopener" class="record-proof-link">
                      <i class="bi bi-box-arrow-up-right"></i>View Proof
                    </a>
                  </div>
                @endif
              </div>
              @if(!empty($record->payment_notes))
                <div class="record-notes">Notes: {{ $record->payment_notes }}</div>
              @endif
            </div>
          @endforeach
        @elseif(empty($onboardingPaymentRecord))
          <div class="text-muted">No payment records yet.</div>
        @else
          <div class="text-muted small">No monthly payment records yet.</div>
        @endif

        @if(method_exists($paymentRecords, 'links'))
          <div class="mt-2">{{ $paymentRecords->links() }}</div>
        @endif
      </div>
    </div>

    <div class="modal fade tenant-submit-modal" id="submitPaymentModal" tabindex="-1" aria-labelledby="submitPaymentModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="submitPaymentModalLabel">Submit Monthly Payment</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            @include('student.payments._submit_panel', ['renderMode' => 'modal'])
          </div>
        </div>
      </div>
    </div>
  @endif
</div>
@endsection

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function () {
    @if($errors->any() && ($viewMode ?? 'list') === 'list')
      if (window.innerWidth >= 992 && window.bootstrap) {
        const modalElement = document.getElementById('submitPaymentModal');
        if (modalElement) {
          const existingModal = bootstrap.Modal.getInstance(modalElement);
          (existingModal || new bootstrap.Modal(modalElement)).show();
        }
      }
    @endif

    const methodInputs = Array.from(document.querySelectorAll('input[name="payment_method"]'));
    const methodCards = Array.from(document.querySelectorAll('.pay-method-card'));
    const methodPanels = Array.from(document.querySelectorAll('[data-payment-panel]'));
    const onlinePaymentFields = document.getElementById('onlinePaymentFields');
    const paymentReferenceInput = document.getElementById('paymentReferenceInput');
    const paymentProofInput = document.getElementById('paymentProofInput');

    const syncMethodState = function () {
      if (!methodInputs.length) return;

      let selectedMethod = '';
      methodInputs.forEach(function (input) {
        if (input.checked) {
          selectedMethod = input.value;
        }
      });

      methodCards.forEach(function (card) {
        const input = card.querySelector('input[name="payment_method"]');
        card.classList.toggle('selected', !!(input && input.checked));
      });

      methodPanels.forEach(function (panel) {
        panel.classList.toggle('d-none', panel.dataset.paymentPanel !== selectedMethod);
      });

      const needsOnlineProof = selectedMethod === 'bank' || selectedMethod === 'gcash';
      if (onlinePaymentFields) {
        onlinePaymentFields.classList.toggle('d-none', !needsOnlineProof);
      }
      if (paymentReferenceInput) {
        paymentReferenceInput.required = needsOnlineProof;
        if (!needsOnlineProof) {
          paymentReferenceInput.value = '';
        }
      }
      if (paymentProofInput) {
        paymentProofInput.required = needsOnlineProof;
        if (!needsOnlineProof) {
          paymentProofInput.value = '';
        }
      }
    };

    methodInputs.forEach(function (input) {
      input.addEventListener('change', syncMethodState);
    });

    syncMethodState();
  });
</script>
@endpush
