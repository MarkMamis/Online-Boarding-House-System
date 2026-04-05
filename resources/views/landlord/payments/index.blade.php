@extends('layouts.landlord')

@section('content')
<div class="payments-shell">
    @php
        $onboardingPaidTotal = (float) ($onboardingPaidTotal ?? 0);
        $onboardingPendingTotal = (float) ($onboardingPendingTotal ?? 0);
        $monthlyPaidTotal = (float) ($monthlyPaidTotal ?? 0);
        $monthlyPendingTotal = (float) ($monthlyPendingTotal ?? 0);
        $onboardingLedgerTotal = (float) ($onboardingLedgerTotal ?? ($onboardingPaidTotal + $onboardingPendingTotal));
        $monthlyLedgerTotal = (float) ($monthlyLedgerTotal ?? ($monthlyPaidTotal + $monthlyPendingTotal));
        $statusFilter = $statusFilter ?? 'all';
        $today = now()->toDateString();
    @endphp

    <section class="payments-section payments-intro-section mb-4">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
            <div>
                <div class="text-uppercase small text-muted fw-semibold">Finance</div>
                <h1 class="h3 mb-1">Payments & Billing</h1>
                <div class="text-muted small">Track rental collections, outstanding balances, and payment status updates.</div>
            </div>
            <a href="{{ route('landlord.tenants.index') }}" class="btn btn-outline-secondary rounded-pill px-3">View Tenants</a>
        </div>
    </section>

    <section class="payments-section payments-summary-section mb-4">
        <div class="payments-summary">
            <div class="payments-summary-item">
                <div class="payments-summary-label">Total Paid (Shared)</div>
                <div class="payments-summary-value text-success-emphasis">PHP {{ number_format($totalPaid, 2) }}</div>
                <div class="payments-summary-sub">Total Paid = onboarding paid + monthly approved</div>
            </div>
            <div class="payments-summary-item">
                <div class="payments-summary-label">Pending (Shared)</div>
                <div class="payments-summary-value text-warning-emphasis">PHP {{ number_format($totalPending, 2) }}</div>
                <div class="payments-summary-sub">Pending = onboarding pending + monthly submitted</div>
            </div>
            <div class="payments-summary-item">
                <div class="payments-summary-label">Total Ledger</div>
                <div class="payments-summary-value text-primary-emphasis">PHP {{ number_format($totalExpected, 2) }}</div>
                <div class="payments-summary-sub">Total Ledger = Total Paid + Pending</div>
            </div>
        </div>
    </section>

    <section class="payments-section payments-records-section">
        <form method="GET" action="{{ route('landlord.payments.index') }}" class="payments-filter-toolbar mb-3">
            <label for="payment_status_filter" class="small text-muted fw-semibold mb-0">Filter status</label>
            <select id="payment_status_filter" name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="all" @selected($statusFilter === 'all')>All</option>
                <option value="paid" @selected($statusFilter === 'paid')>Paid</option>
                <option value="pending" @selected($statusFilter === 'pending')>Pending</option>
                <option value="overdue" @selected($statusFilter === 'overdue')>Overdue</option>
            </select>
        </form>

    @if(session('success'))
        <div class="alert alert-success rounded-4">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger rounded-4">{{ session('error') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger rounded-4">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="payments-list-card">
        @if($bookings->isNotEmpty())
            @if($bookings->count() > 1)
                <div class="swipe-hint d-sm-none" aria-hidden="true">
                    <i class="bi bi-arrow-left-right"></i>
                    <span>Swipe tenant cards</span>
                </div>
            @endif

            <div class="payments-mobile-track">
            @foreach($bookings as $booking)
                @php
                    $status = strtolower((string) ($booking->effective_payment_status ?? $booking->payment_status ?? 'pending'));
                    $dueDate = $booking->effective_due_date;
                    $monthlyRent = (float) ($booking->monthly_rent_amount ?? $booking->room->price);
                    $amount = (float) ($booking->billing_amount ?? $monthlyRent);
                    $statusClass = match ($status) {
                        'paid' => 'status-approved',
                        'overdue' => 'status-overdue',
                        default => 'status-pending',
                    };
                    $statusIcon = match ($status) {
                        'paid' => 'bi-check-circle',
                        'overdue' => 'bi-exclamation-octagon',
                        default => 'bi-hourglass-split',
                    };
                    $statusLabel = match ($status) {
                        'paid' => 'Paid',
                        'overdue' => 'Overdue',
                        default => 'Pending',
                    };
                    $statusNote = match ($status) {
                        'paid' => 'Payment received' . ($booking->payment_date ? ' on ' . $booking->payment_date->format('M d, Y') : ''),
                        'overdue' => 'Overdue since ' . ($dueDate ? $dueDate->format('M d, Y') : 'previous due date'),
                        default => 'Awaiting payment receipt' . ($dueDate ? ' by ' . $dueDate->format('M d, Y') : ''),
                    };

                    $advanceIncluded = (bool) ($booking->include_advance_payment ?? false);
                    $roomRequiresAdvance = (bool) ($booking->room->requires_advance_payment ?? false);
                    $advanceAmount = $advanceIncluded ? $monthlyRent : 0.0;
                    $moveInTotal = $monthlyRent + $advanceAmount;
                    $monthlyCycleAmount = $monthlyRent;
                    $onboardingAmount = $moveInTotal;

                    $isOverdueAdvance = $advanceIncluded
                        && in_array($status, ['pending', 'overdue'], true)
                        && optional($booking->check_in)->toDateString() < $today;

                    if (!$advanceIncluded) {
                        if ($roomRequiresAdvance) {
                            $advanceStatusClass = 'advance-overdue';
                            $advanceStatusLabel = 'Required';
                            $advanceStatusNote = 'Room requires 1-month advance, but this booking record has no included advance.';
                        } else {
                            $advanceStatusClass = 'advance-neutral';
                            $advanceStatusLabel = 'Not Included';
                            $advanceStatusNote = 'Optional advance was not included for this booking.';
                        }
                    } elseif ($status === 'paid') {
                        $advanceStatusClass = 'advance-paid';
                        $advanceStatusLabel = 'Paid';
                        $advanceStatusNote = 'Advance marked paid' . ($booking->payment_date ? ' on ' . $booking->payment_date->format('M d, Y') : '.') ;
                    } elseif ($isOverdueAdvance) {
                        $advanceStatusClass = 'advance-overdue';
                        $advanceStatusLabel = 'Overdue';
                        $advanceStatusNote = 'Check-in date has passed and advance is still unpaid.';
                    } else {
                        $advanceStatusClass = 'advance-pending';
                        $advanceStatusLabel = 'Pending';
                        $advanceStatusNote = 'Advance is expected before check-in.';
                    }

                    $latestTenantPayment = $booking->latest_tenant_payment ?? null;
                    $latestTenantPaymentStatus = strtolower((string) ($latestTenantPayment->status ?? ''));
                    $latestTenantPaymentStatusLabel = $latestTenantPayment
                        ? ucfirst($latestTenantPaymentStatus ?: 'submitted')
                        : null;
                    $latestSubmissionToneClass = match ($latestTenantPaymentStatus) {
                        'approved' => 'submission-tone-approved',
                        'rejected' => 'submission-tone-rejected',
                        'submitted' => 'submission-tone-submitted',
                        default => 'submission-tone-empty',
                    };
                    $latestTenantPaymentTime = $latestTenantPayment
                        ? (optional($latestTenantPayment->submitted_at)->format('M d, Y h:i A') ?: optional($latestTenantPayment->created_at)->format('M d, Y h:i A'))
                        : null;
                    $latestMethodAndRef = '—';
                    if ($latestTenantPayment) {
                        $latestMethodAndRef = ucfirst((string) ($latestTenantPayment->payment_method ?? '—'));
                        if (!empty($latestTenantPayment->payment_reference)) {
                            $latestMethodAndRef .= ' • ' . $latestTenantPayment->payment_reference;
                        }
                    }
                @endphp

                <article class="payment-item">
                    <div class="payment-main">
                        <div class="payment-title-row">
                            <div class="d-flex align-items-center gap-2 min-w-0">
                                <div class="student-avatar">{{ strtoupper(substr($booking->student->full_name ?? 'S', 0, 1)) }}</div>
                                <div class="min-w-0">
                                    <div class="student-name text-truncate">{{ $booking->student->full_name }}</div>
                                    <div class="student-email text-truncate">{{ $booking->student->email }}</div>
                                </div>
                            </div>
                            <div class="amount-chip-group">
                                <span class="amount-chip amount-chip-monthly">
                                    <span class="amount-chip-label">Monthly Cycle</span>
                                    <span class="amount-chip-value">PHP {{ number_format($monthlyCycleAmount, 2) }}</span>
                                </span>
                                <span class="amount-chip amount-chip-onboarding">
                                    <span class="amount-chip-label">Onboarding Total</span>
                                    <span class="amount-chip-value">PHP {{ number_format($onboardingAmount, 2) }}</span>
                                </span>
                            </div>
                        </div>

                        <div class="payment-meta-row">
                            <span class="meta-chip"><i class="bi bi-building"></i>{{ $booking->room->property->name }}</span>
                            <span class="meta-chip"><i class="bi bi-door-open"></i>Room {{ $booking->room->room_number }}</span>
                            <span class="meta-chip meta-chip-optional"><i class="bi bi-calendar-range"></i>{{ $booking->check_in->format('M d, Y') }} - {{ $booking->check_out->format('M d, Y') }}</span>
                            <span class="meta-chip meta-chip-optional"><i class="bi bi-clock"></i>{{ $booking->getDurationInDays() }} day{{ $booking->getDurationInDays() === 1 ? '' : 's' }}</span>
                            <span class="meta-chip"><i class="bi bi-calendar-check"></i>Due {{ $dueDate ? $dueDate->format('M d, Y') : 'N/A' }}</span>
                        </div>

                        <div class="payment-ledger-grid mt-3">
                            <div class="invoice-lines">
                                <div class="invoice-line invoice-line-heading">
                                    <span>Onboarding Billing</span>
                                    <strong>One-time</strong>
                                </div>
                                <div class="invoice-line">
                                    <span>Monthly Rent</span>
                                    <strong>PHP {{ number_format($monthlyRent, 2) }}</strong>
                                </div>
                                <div class="invoice-line invoice-line-detail">
                                    <span>Advance</span>
                                    <strong>PHP {{ number_format($advanceAmount, 2) }}</strong>
                                </div>
                                <div class="invoice-line invoice-line-total">
                                    <span>Move-in Total</span>
                                    <strong>PHP {{ number_format($moveInTotal, 2) }}</strong>
                                </div>
                                <div class="invoice-line">
                                    <span>Next Monthly Cycle</span>
                                    <strong>PHP {{ number_format($monthlyCycleAmount, 2) }}</strong>
                                </div>
                            </div>

                            <div class="invoice-lines submission-lines {{ $latestSubmissionToneClass }}">
                                <div class="invoice-line invoice-line-heading">
                                    <span>Latest Submission</span>
                                    <strong>{{ $latestTenantPaymentStatusLabel ?: 'No Record' }}</strong>
                                </div>
                                <div class="invoice-line">
                                    <span>Latest Tenant Submission</span>
                                    <strong>{{ $latestTenantPaymentTime ?: 'No submission yet' }}</strong>
                                </div>
                                <div class="invoice-line invoice-line-detail">
                                    <span>Method / Ref</span>
                                    <strong>{{ $latestMethodAndRef }}</strong>
                                </div>
                                <div class="invoice-line">
                                    <span>Record Status</span>
                                    <strong>{{ $latestTenantPaymentStatusLabel ?: '—' }}</strong>
                                </div>
                                <div class="invoice-line invoice-line-hint">
                                    <span><i class="bi bi-arrow-right-circle me-1"></i>Use Manage Monthly to review full records.</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mobile-more-wrap d-sm-none">
                        <button type="button" class="btn btn-sm btn-outline-primary rounded-pill w-100 mobile-more-btn" data-bs-toggle="modal" data-bs-target="#mobilePaymentDetailModal{{ $booking->id }}">
                            <i class="bi bi-three-dots me-1"></i>View More
                        </button>
                    </div>

                    <div class="payment-side-grid">
                        <div class="status-panel">
                            <div class="status-panel-title">Current Status</div>
                            <span class="status-pill {{ $statusClass }}"><i class="bi {{ $statusIcon }}"></i>{{ $statusLabel }}</span>
                            <div class="status-note">{{ $statusNote }}</div>

                            <div class="mt-2">
                                <span class="advance-badge {{ $advanceStatusClass }}">Advance: {{ $advanceStatusLabel }}</span>
                                <div class="status-note">{{ $advanceStatusNote }}</div>
                            </div>
                        </div>

                        <div class="payment-actions">
                            <div class="action-panel-title">Actions</div>
                            <a href="{{ route('landlord.payments.manage', ['booking' => $booking->id, 'status' => $statusFilter]) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                <i class="bi bi-journal-text me-1"></i>Manage Monthly
                            </a>

                            @if($latestTenantPayment && !empty($latestTenantPayment->payment_proof_path))
                                <a href="{{ asset('storage/' . $latestTenantPayment->payment_proof_path) }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                                    <i class="bi bi-file-earmark-arrow-down me-1"></i>Latest Proof
                                </a>
                            @endif

                            @if($status === 'paid')
                                <form action="{{ route('landlord.payments.mark_pending', $booking->id) }}" method="POST" class="action-form">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-warning rounded-pill px-3" onclick="return confirm('Undo paid status for this booking?')">
                                        <i class="bi bi-arrow-counterclockwise me-1"></i>Undo Paid
                                    </button>
                                </form>
                            @else
                                <form action="{{ route('landlord.payments.mark_paid', $booking->id) }}" method="POST" class="action-form">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success rounded-pill px-3" onclick="return confirm('Mark this payment as received?')">
                                        <i class="bi bi-check2 me-1"></i>Mark Paid
                                    </button>
                                </form>
                                <form action="{{ route('landlord.payments.remind', $booking->id) }}" method="POST" class="action-form">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-3" onclick="return confirm('Send a payment reminder to this tenant?')">
                                        <i class="bi bi-bell me-1"></i>Send Reminder
                                    </button>
                                </form>
                            @endif

                            @if(!empty($booking->reminder_qr_url) && in_array($status, ['pending', 'overdue'], true))
                                <a href="{{ $booking->reminder_qr_url }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                                    <i class="bi bi-qr-code me-1"></i>GCash QR
                                </a>
                            @endif
                        </div>
                    </div>
                </article>

                <div class="modal fade payment-detail-modal" id="mobilePaymentDetailModal{{ $booking->id }}" tabindex="-1" aria-labelledby="mobilePaymentDetailLabel{{ $booking->id }}" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h2 class="modal-title fs-6" id="mobilePaymentDetailLabel{{ $booking->id }}">{{ $booking->student->full_name }} - Status & Actions</h2>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="status-panel mb-3">
                                    <div class="status-panel-title">Current Status</div>
                                    <span class="status-pill {{ $statusClass }}"><i class="bi {{ $statusIcon }}"></i>{{ $statusLabel }}</span>
                                    <div class="status-note">{{ $statusNote }}</div>

                                    <div class="mt-2">
                                        <span class="advance-badge {{ $advanceStatusClass }}">Advance: {{ $advanceStatusLabel }}</span>
                                        <div class="status-note">{{ $advanceStatusNote }}</div>
                                    </div>
                                </div>

                                <div class="payment-actions">
                                    <div class="action-panel-title">Actions</div>
                                    <a href="{{ route('landlord.payments.manage', ['booking' => $booking->id, 'status' => $statusFilter]) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                        <i class="bi bi-journal-text me-1"></i>Manage Monthly
                                    </a>

                                    @if($latestTenantPayment && !empty($latestTenantPayment->payment_proof_path))
                                        <a href="{{ asset('storage/' . $latestTenantPayment->payment_proof_path) }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                                            <i class="bi bi-file-earmark-arrow-down me-1"></i>Latest Proof
                                        </a>
                                    @endif

                                    @if($status === 'paid')
                                        <form action="{{ route('landlord.payments.mark_pending', $booking->id) }}" method="POST" class="action-form">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-warning rounded-pill px-3" onclick="return confirm('Undo paid status for this booking?')">
                                                <i class="bi bi-arrow-counterclockwise me-1"></i>Undo Paid
                                            </button>
                                        </form>
                                    @else
                                        <form action="{{ route('landlord.payments.mark_paid', $booking->id) }}" method="POST" class="action-form">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success rounded-pill px-3" onclick="return confirm('Mark this payment as received?')">
                                                <i class="bi bi-check2 me-1"></i>Mark Paid
                                            </button>
                                        </form>
                                        <form action="{{ route('landlord.payments.remind', $booking->id) }}" method="POST" class="action-form">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-3" onclick="return confirm('Send a payment reminder to this tenant?')">
                                                <i class="bi bi-bell me-1"></i>Send Reminder
                                            </button>
                                        </form>
                                    @endif

                                    @if(!empty($booking->reminder_qr_url) && in_array($status, ['pending', 'overdue'], true))
                                        <a href="{{ $booking->reminder_qr_url }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                                            <i class="bi bi-qr-code me-1"></i>GCash QR
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            @endforeach
            </div>
        @else
            <div class="empty-state">
                <i class="bi bi-cash-stack fs-1 mb-2"></i>
                <div class="empty-title">No Matching Payments</div>
                <div class="empty-copy">Try changing the filter or wait for records with active billing cycles.</div>
            </div>
        @endif
    </div>
    </section>
</div>
@endsection

@push('styles')
<style>
    .payments-shell {
        background: linear-gradient(180deg, #ffffff 0%, #fbfdfc 100%);
        border: 1px solid rgba(2,8,20,.08);
        border-radius: 1.25rem;
        box-shadow: 0 10px 26px rgba(2,8,20,.06);
        padding: 1.25rem;
    }
    .payments-section {
        min-width: 0;
    }
    .payments-summary {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: .75rem;
    }
    .payments-filter-toolbar {
        display: inline-flex;
        align-items: center;
        gap: .6rem;
        border: 1px solid rgba(2,8,20,.09);
        border-radius: 999px;
        background: #fff;
        padding: .35rem .7rem;
    }
    .payments-filter-toolbar .form-select {
        min-width: 160px;
        border: 1px solid rgba(2,8,20,.13);
        border-radius: 999px;
    }
    .payments-summary-item {
        border: 1px solid rgba(20,83,45,.16);
        background: linear-gradient(180deg, rgba(167,243,208,.18), rgba(255,255,255,.85));
        border-radius: .9rem;
        padding: .7rem .8rem;
    }
    .payments-summary-label {
        font-size: .72rem;
        text-transform: uppercase;
        letter-spacing: .05em;
        color: rgba(2,8,20,.55);
        font-weight: 700;
        margin-bottom: .2rem;
    }
    .payments-summary-value {
        font-size: 1rem;
        font-weight: 700;
        color: #0f172a;
    }
    .payments-summary-sub {
        margin-top: .2rem;
        font-size: .68rem;
        color: rgba(2,8,20,.56);
        line-height: 1.3;
    }
    .payments-list-card {
        border: 1px solid rgba(2,8,20,.09);
        border-radius: 1rem;
        background: #fff;
        overflow: hidden;
    }
    .payments-mobile-track {
        display: block;
    }
    .swipe-hint {
        display: none;
    }
    .mobile-more-wrap {
        display: none;
    }
    .mobile-more-btn {
        font-weight: 700;
    }
    .payment-detail-modal .modal-content {
        border: 1px solid rgba(2,8,20,.12);
        border-radius: 1rem;
    }
    .payment-detail-modal .modal-header {
        border-bottom: 1px solid rgba(2,8,20,.1);
    }
    .payment-detail-modal .modal-body {
        padding: .85rem;
    }
    .payment-detail-modal .status-panel {
        text-align: left;
    }
    .payment-detail-modal .payment-actions {
        border-color: rgba(37,99,235,.24);
        background: linear-gradient(180deg, rgba(239,246,255,.7), rgba(255,255,255,.98));
    }
    .payment-item {
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(330px, 420px);
        gap: .9rem;
        align-items: start;
        padding: 1rem;
        border-bottom: 1px solid rgba(2,8,20,.08);
    }
    .payment-item:last-child {
        border-bottom: none;
    }
    .payment-title-row {
        display: flex;
        justify-content: space-between;
        gap: .6rem;
        align-items: start;
        margin-bottom: .45rem;
    }
    .student-avatar {
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
    .student-name {
        font-weight: 700;
        color: #14532d;
        line-height: 1.2;
    }
    .student-email {
        font-size: .78rem;
        color: #64748b;
    }
    .amount-chip {
        display: inline-flex;
        align-items: center;
        border: 1px solid rgba(20,83,45,.2);
        border-radius: 999px;
        background: rgba(167,243,208,.18);
        color: #14532d;
        padding: .18rem .6rem;
        font-size: .8rem;
        font-weight: 800;
        white-space: nowrap;
    }
    .amount-chip-group {
        display: flex;
        gap: .32rem;
        justify-content: flex-end;
        align-items: stretch;
        flex-wrap: nowrap;
    }
    .amount-chip {
        border-radius: .7rem;
        padding: .22rem .52rem;
        display: grid;
        gap: .02rem;
        text-align: right;
        white-space: normal;
    }
    .amount-chip-label {
        font-size: .62rem;
        letter-spacing: .05em;
        text-transform: uppercase;
        font-weight: 700;
        opacity: .8;
    }
    .amount-chip-value {
        font-size: .78rem;
        font-weight: 800;
    }
    .amount-chip-monthly {
        border-color: rgba(21,128,61,.3);
        background: linear-gradient(180deg, rgba(167,243,208,.36), rgba(255,255,255,.96));
        color: #14532d;
    }
    .amount-chip-onboarding {
        border-color: rgba(100,116,139,.28);
        background: linear-gradient(180deg, rgba(226,232,240,.6), rgba(255,255,255,.98));
        color: #334155;
    }
    .payment-meta-row {
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
    .payment-ledger-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: .55rem;
        max-width: 760px;
    }
    .payment-side-grid {
        display: grid;
        grid-template-columns: minmax(140px, 1fr) minmax(170px, 1fr);
        gap: .45rem;
        align-items: start;
        min-width: 330px;
    }
    .payment-side-grid > div + div {
        border-left: 1px dashed rgba(100,116,139,.35);
        padding-left: .6rem;
    }
    .status-panel {
        text-align: right;
        padding: .45rem .55rem;
        border: 1px solid rgba(2,8,20,.1);
        border-radius: .7rem;
        background: linear-gradient(180deg, rgba(248,250,252,.92), rgba(255,255,255,.98));
    }
    .status-panel-title {
        font-size: .68rem;
        text-transform: uppercase;
        letter-spacing: .06em;
        color: rgba(2,8,20,.5);
        font-weight: 700;
        margin-bottom: .24rem;
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
    .status-note {
        margin-top: .24rem;
        font-size: .74rem;
        color: rgba(2,8,20,.56);
    }
    .status-pending {
        color: #7c2d12;
        background: #ffedd5;
        border-color: #fdba74;
    }
    .status-approved {
        color: #14532d;
        background: #dcfce7;
        border-color: #86efac;
    }
    .status-overdue {
        color: #7f1d1d;
        background: #fee2e2;
        border-color: #fca5a5;
    }
    .payment-actions {
        border: 1px solid rgba(37,99,235,.2);
        border-radius: .7rem;
        padding: .42rem .52rem;
        background: linear-gradient(180deg, rgba(239,246,255,.78), rgba(255,255,255,.98));
        display: grid;
        gap: .35rem;
        align-content: start;
    }
    .action-panel-title {
        font-size: .68rem;
        text-transform: uppercase;
        letter-spacing: .06em;
        color: rgba(30,64,175,.78);
        font-weight: 700;
        margin-bottom: .12rem;
    }
    .payment-actions .btn {
        display: inline-flex;
        justify-content: center;
        align-items: center;
        width: 100%;
    }
    .invoice-lines {
        border: 1px solid rgba(2,8,20,.1);
        border-radius: .75rem;
        background: #fcfefc;
        padding: .5rem .65rem;
        display: grid;
        gap: .35rem;
    }
    .submission-lines {
        border-width: 1px;
        border-style: solid;
        position: relative;
        overflow: hidden;
    }
    .submission-lines::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 4px;
        background: rgba(16,185,129,.6);
    }
    .submission-tone-submitted {
        border-color: rgba(251,146,60,.5);
        background: linear-gradient(180deg, rgba(255,237,213,.72), rgba(255,255,255,.98));
        box-shadow: 0 0 0 1px rgba(251,146,60,.12);
    }
    .submission-tone-approved {
        border-color: rgba(52,211,153,.45);
        background: linear-gradient(180deg, rgba(220,252,231,.74), rgba(255,255,255,.98));
        box-shadow: 0 0 0 1px rgba(52,211,153,.12);
    }
    .submission-tone-rejected {
        border-color: rgba(248,113,113,.46);
        background: linear-gradient(180deg, rgba(254,226,226,.74), rgba(255,255,255,.98));
        box-shadow: 0 0 0 1px rgba(248,113,113,.12);
    }
    .submission-tone-empty {
        border-color: rgba(148,163,184,.34);
        background: linear-gradient(180deg, rgba(241,245,249,.82), rgba(255,255,255,.98));
    }
    .submission-tone-submitted::before { background: #f97316; }
    .submission-tone-approved::before { background: #16a34a; }
    .submission-tone-rejected::before { background: #dc2626; }
    .submission-tone-empty::before { background: #94a3b8; }
    .invoice-line-hint {
        justify-content: flex-start;
        border-top: 1px dashed rgba(2,8,20,.16);
        padding-top: .35rem;
        margin-top: .08rem;
        font-size: .72rem;
        color: #334155;
    }
    .invoice-line {
        display: flex;
        align-items: center;
        justify-content: space-between;
        font-size: .78rem;
        color: #0f172a;
    }
    .invoice-line-heading {
        border-bottom: 1px dashed rgba(2,8,20,.16);
        padding-bottom: .34rem;
        margin-bottom: .12rem;
    }
    .invoice-line-heading span {
        font-weight: 700;
        color: #14532d;
    }
    .invoice-line strong {
        font-size: .8rem;
    }
    .invoice-line-total {
        border-top: 1px dashed rgba(2,8,20,.18);
        padding-top: .35rem;
        font-weight: 700;
    }
    .advance-badge {
        display: inline-flex;
        align-items: center;
        gap: .25rem;
        border-radius: 999px;
        padding: .2rem .58rem;
        font-size: .74rem;
        font-weight: 700;
        border: 1px solid transparent;
    }
    .advance-paid {
        color: #14532d;
        background: #dcfce7;
        border-color: #86efac;
    }
    .advance-pending {
        color: #7c2d12;
        background: #ffedd5;
        border-color: #fdba74;
    }
    .advance-overdue {
        color: #7f1d1d;
        background: #fee2e2;
        border-color: #fca5a5;
    }
    .advance-neutral {
        color: #334155;
        background: #f1f5f9;
        border-color: #cbd5e1;
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

    @media (max-width: 1199.98px) {
        .payment-item {
            grid-template-columns: 1fr;
        }
        .payment-side-grid {
            min-width: 0;
        }
        .status-panel {
            text-align: left;
        }
        .amount-chip-group {
            justify-content: flex-start;
        }
        .amount-chip {
            text-align: left;
        }
    }

    @media (max-width: 991.98px) {
        .payments-shell {
            padding: .95rem;
        }
        .payments-summary {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
        .payment-ledger-grid {
            grid-template-columns: 1fr;
        }
        .payment-side-grid {
            grid-template-columns: 1fr;
        }
        .payment-side-grid > div + div {
            border-left: none;
            border-top: 1px dashed rgba(100,116,139,.35);
            padding-left: 0;
            padding-top: .55rem;
        }
    }

    @media (max-width: 575.98px) {
        .payments-shell {
            background: transparent;
            border: none;
            box-shadow: none;
            padding: 0;
        }
        .payments-section {
            background: linear-gradient(180deg, #ffffff 0%, #fbfdfc 100%);
            border: 1px solid rgba(2,8,20,.09);
            border-radius: 1rem;
            box-shadow: 0 8px 20px rgba(2,8,20,.05);
            padding: .85rem;
            margin-bottom: .75rem !important;
        }
        .payments-records-section {
            background: transparent;
            border: none;
            border-radius: 0;
            box-shadow: none;
            padding: 0;
        }
        .payments-section:last-child {
            margin-bottom: 0 !important;
        }
        .payments-intro-section .h3 {
            font-size: 1.75rem;
        }
        .payments-summary {
            grid-template-columns: 1fr;
        }
        .payments-list-card {
            border: none;
            border-radius: 0;
            background: transparent;
            padding: .55rem 0 .2rem;
        }
        .swipe-hint {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            margin: 0 .8rem .45rem;
            font-size: .74rem;
            color: #475569;
            font-weight: 600;
            letter-spacing: .01em;
        }
        .swipe-hint i {
            color: #2563eb;
            font-size: .9rem;
        }
        .payments-mobile-track {
            display: flex;
            gap: .65rem;
            overflow-x: auto;
            overscroll-behavior-x: contain;
            scroll-snap-type: x mandatory;
            -webkit-overflow-scrolling: touch;
            padding: 0 0 .35rem;
            scrollbar-width: none;
        }
        .payments-mobile-track::-webkit-scrollbar {
            display: none;
        }
        .payments-mobile-track .payment-item {
            flex: 0 0 100%;
            width: 100%;
            max-width: none;
            scroll-snap-align: start;
            border: 1px solid rgba(2,8,20,.1);
            border-radius: .95rem;
            background: #fff;
            margin: 0;
        }
        .mobile-more-wrap {
            display: block;
            margin-top: .55rem;
        }
        .payment-side-grid {
            display: none;
        }
        .payment-item {
            padding: .75rem;
            gap: .65rem;
        }
        .payment-title-row {
            flex-direction: column;
            align-items: stretch;
            gap: .5rem;
        }
        .student-email {
            display: none;
        }
        .amount-chip-group {
            width: 100%;
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: .35rem;
        }
        .amount-chip {
            text-align: left;
        }
        .meta-chip-optional,
        .invoice-line-detail,
        .invoice-line-hint {
            display: none;
        }
        .payments-filter-toolbar {
            display: flex;
            width: 100%;
            border-radius: .8rem;
        }
        .payments-filter-toolbar .form-select {
            min-width: 0;
            width: 100%;
        }
        .invoice-line {
            font-size: .74rem;
        }
        .invoice-line strong {
            font-size: .76rem;
        }
        .payment-actions .btn {
            font-size: .82rem;
        }
    }
</style>
@endpush