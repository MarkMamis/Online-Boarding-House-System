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
        $activeTab = strtolower((string) request('tab', 'onboarding'));
        if (!in_array($activeTab, ['onboarding', 'monthly'], true)) {
            $activeTab = 'onboarding';
        }
        $onboardingBookings = collect($allBookings ?? $bookings);
        $onboardingTabUrl = route('landlord.payments.index', ['tab' => 'onboarding', 'status' => $statusFilter]);
        $monthlyTabUrl = route('landlord.payments.index', ['tab' => 'monthly', 'status' => $statusFilter]);
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
        <div class="billing-tabs mb-3" role="tablist" aria-label="Payment views">
            <a href="{{ $onboardingTabUrl }}" class="billing-tab {{ $activeTab === 'onboarding' ? 'active' : '' }}" aria-current="{{ $activeTab === 'onboarding' ? 'page' : 'false' }}">
                <i class="bi bi-file-earmark-check me-1"></i>Onboarding
            </a>
            <a href="{{ $monthlyTabUrl }}" class="billing-tab {{ $activeTab === 'monthly' ? 'active' : '' }}" aria-current="{{ $activeTab === 'monthly' ? 'page' : 'false' }}">
                <i class="bi bi-calendar2-week me-1"></i>Monthly Billing
            </a>
        </div>

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

        <div class="billing-pane onboarding-pane {{ $activeTab === 'onboarding' ? '' : 'd-none' }}">
            <div class="onboarding-summary mb-3">
                <div class="onboarding-summary-item">
                    <div class="onboarding-summary-label">Onboarding Ledger</div>
                    <div class="onboarding-summary-value">PHP {{ number_format($onboardingLedgerTotal, 2) }}</div>
                </div>
                <div class="onboarding-summary-item paid">
                    <div class="onboarding-summary-label">Onboarding Paid</div>
                    <div class="onboarding-summary-value">PHP {{ number_format($onboardingPaidTotal, 2) }}</div>
                </div>
                <div class="onboarding-summary-item pending">
                    <div class="onboarding-summary-label">Onboarding Pending</div>
                    <div class="onboarding-summary-value">PHP {{ number_format($onboardingPendingTotal, 2) }}</div>
                </div>
            </div>

            <div class="onboarding-list-card">
                @if($onboardingBookings->isNotEmpty())
                    @foreach($onboardingBookings as $booking)
                        @php
                            $monthlyRent = (float) ($booking->monthly_rent_amount ?? $booking->room->price);
                            $advanceIncluded = (bool) ($booking->include_advance_payment ?? false);
                            $advanceAmount = $advanceIncluded ? $monthlyRent : 0.0;
                            $moveInTotal = $monthlyRent + $advanceAmount;
                            $onboarding = $booking->tenantOnboarding;
                            $onboardingAmount = $onboarding && is_numeric($onboarding->deposit_amount) && (float) $onboarding->deposit_amount > 0
                                ? (float) $onboarding->deposit_amount
                                : $moveInTotal;
                            $onboardingStatus = strtolower((string) ($onboarding->status ?? 'not_started'));
                            $hasOnboardingPaid = $onboarding && ((bool) $onboarding->deposit_paid || $onboardingStatus === 'completed');
                            $hasSubmittedDeposit = $onboarding && $onboardingStatus === 'deposit_paid';
                            $hasSignedContract = $onboarding && (bool) $onboarding->contract_signed;
                            $bookingPaymentStatus = strtolower((string) ($booking->effective_payment_status ?? $booking->payment_status ?? 'pending'));
                            $canSetOnboardingReminder = $onboarding
                                && in_array($onboardingStatus, ['pending', 'documents_uploaded', 'contract_signed'], true)
                                && empty($onboarding->payment_submitted_at);

                            if ($hasOnboardingPaid) {
                                $onboardingStateLabel = 'Paid';
                                $onboardingStateClass = 'onboarding-state-paid';
                                $onboardingStateNote = 'Move-in billing is completed and confirmed.';
                            } elseif ($hasSubmittedDeposit) {
                                $onboardingStateLabel = 'For Review';
                                $onboardingStateClass = 'onboarding-state-pending';
                                $onboardingStateNote = 'Tenant submitted onboarding payment for review.';
                            } elseif ($hasSignedContract) {
                                $onboardingStateLabel = 'Contract Signed';
                                $onboardingStateClass = 'onboarding-state-progress';
                                $onboardingStateNote = 'Contract is signed. Waiting for deposit submission.';
                            } elseif ($onboarding) {
                                $onboardingStateLabel = 'In Progress';
                                $onboardingStateClass = 'onboarding-state-progress';
                                $onboardingStateNote = 'Onboarding is ongoing.';
                            } else {
                                $onboardingStateLabel = 'Not Started';
                                $onboardingStateClass = 'onboarding-state-neutral';
                                $onboardingStateNote = 'No onboarding record yet for this booking.';
                            }

                            $onboardingProofPath = $onboarding?->payment_proof_path;
                            $onboardingSubmittedAt = optional($onboarding?->payment_submitted_at)->format('M d, Y h:i A');
                            $tenantName = (string) ($booking->student->full_name ?? 'Student');
                        @endphp

                        <article class="onboarding-item">
                            <div class="onboarding-head">
                                <div class="d-flex align-items-center gap-2 min-w-0">
                                    <div class="student-avatar">{{ strtoupper(substr($tenantName, 0, 1)) }}</div>
                                    <div class="min-w-0">
                                        <div class="student-name text-truncate">{{ $tenantName }}</div>
                                        <div class="student-email text-truncate">{{ $booking->student->email }}</div>
                                    </div>
                                </div>
                                <span class="onboarding-state {{ $onboardingStateClass }}">{{ $onboardingStateLabel }}</span>
                            </div>

                            <div class="payment-meta-row mt-2">
                                <span class="meta-chip"><i class="bi bi-building"></i>{{ $booking->room->property->name }}</span>
                                <span class="meta-chip"><i class="bi bi-door-open"></i>{{ $booking->room->room_number }}</span>
                                <span class="meta-chip"><i class="bi bi-calendar-check"></i>Check-in {{ $booking->check_in->format('M d, Y') }}</span>
                            </div>

                            <div class="onboarding-ledger-grid mt-3">
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
                                        <strong>PHP {{ number_format($onboardingAmount, 2) }}</strong>
                                    </div>
                                </div>

                                <div class="invoice-lines">
                                    <div class="invoice-line invoice-line-heading">
                                        <span>Submission</span>
                                        <strong>{{ $onboardingSubmittedAt ? 'Submitted' : 'No Record' }}</strong>
                                    </div>
                                    <div class="invoice-line">
                                        <span>Submitted At</span>
                                        <strong>{{ $onboardingSubmittedAt ?: 'No submission yet' }}</strong>
                                    </div>
                                    <div class="invoice-line">
                                        <span>Payment Method</span>
                                        <strong>{{ $onboarding ? ucfirst((string) ($onboarding->payment_method ?? '—')) : '—' }}</strong>
                                    </div>
                                    <div class="invoice-line">
                                        <span>Reference</span>
                                        <strong>{{ $onboarding && !empty($onboarding->payment_reference) ? $onboarding->payment_reference : '—' }}</strong>
                                    </div>
                                </div>
                            </div>

                            <div class="onboarding-foot mt-3">
                                <div class="onboarding-note">{{ $onboardingStateNote }}</div>
                                <div class="d-flex flex-wrap gap-2 justify-content-end">
                                    @if($onboarding)
                                        <a href="{{ route('landlord.onboarding.review', $onboarding) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                            <i class="bi bi-list-check me-1"></i>View Onboarding Status
                                        </a>
                                    @endif

                                    @if($hasSubmittedDeposit)
                                        <form action="{{ route('landlord.onboarding.approve_documents', $onboarding) }}" method="POST" class="d-inline">
                                            @csrf
                                            <input type="hidden" name="action" value="approve_payment">
                                            <button type="submit" class="btn btn-sm btn-success rounded-pill px-3" onclick="return confirm('Approve onboarding payment and mark as paid?')">
                                                <i class="bi bi-check2 me-1"></i>Mark Paid
                                            </button>
                                        </form>
                                    @elseif($hasOnboardingPaid && $bookingPaymentStatus === 'paid')
                                        <form action="{{ route('landlord.payments.mark_pending', $booking->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-warning rounded-pill px-3" onclick="return confirm('Undo paid status for this onboarding tenant?')">
                                                <i class="bi bi-arrow-counterclockwise me-1"></i>Undo Paid
                                            </button>
                                        </form>
                                    @endif

                                    @if($canSetOnboardingReminder)
                                        <form id="onboardingReminder{{ $booking->id }}" action="{{ route('landlord.payments.remind', $booking->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button
                                                type="button"
                                                class="btn btn-sm btn-outline-danger rounded-pill px-3 js-payment-confirm"
                                                data-bs-toggle="modal"
                                                data-bs-target="#paymentConfirmModal"
                                                data-confirm-title="Set Onboarding Reminder"
                                                data-confirm-message="Set onboarding payment reminder for this tenant?"
                                                data-confirm-submit-label="Set Reminder"
                                                data-confirm-tone="danger"
                                                data-form-id="onboardingReminder{{ $booking->id }}"
                                            >
                                                <i class="bi bi-bell me-1"></i>Set Reminder
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </article>
                    @endforeach
                @else
                    <div class="empty-state">
                        <i class="bi bi-file-earmark-text fs-1 mb-2"></i>
                        <div class="empty-title">No Onboarding Records</div>
                        <div class="empty-copy">Onboarding billing entries will appear here once tenants start the contract and deposit flow.</div>
                    </div>
                @endif
            </div>
        </div>

        <div class="billing-pane monthly-pane {{ $activeTab === 'monthly' ? '' : 'd-none' }}">
            <div class="monthly-summary mb-3">
                <span class="monthly-summary-chip paid"><i class="bi bi-check-circle"></i>{{ $paidCount }} paid</span>
                <span class="monthly-summary-chip pending"><i class="bi bi-hourglass-split"></i>{{ $pendingCount }} pending</span>
                <span class="monthly-summary-chip overdue"><i class="bi bi-exclamation-octagon"></i>{{ $overdueCount }} overdue</span>
            </div>

            <form method="GET" action="{{ route('landlord.payments.index') }}" class="payments-filter-toolbar mb-3">
                <input type="hidden" name="tab" value="monthly">
                <label for="payment_status_filter" class="small text-muted fw-semibold mb-0">Filter status</label>
                <select id="payment_status_filter" name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="all" @selected($statusFilter === 'all')>All</option>
                    <option value="paid" @selected($statusFilter === 'paid')>Paid</option>
                    <option value="pending" @selected($statusFilter === 'pending')>Pending</option>
                    <option value="overdue" @selected($statusFilter === 'overdue')>Overdue</option>
                </select>
            </form>

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
                            $currentDueAmount = $monthlyRent;
                            $monthlyCycleAmount = $monthlyRent;
                            $effectivePaidAt = $booking->effective_payment_date ?? null;
                            $lastPaidDate = optional($effectivePaidAt)->format('M d, Y');
                            $onboardingCycleStart = optional($booking->check_in)?->copy()->startOfDay();
                            $onboardingCycleEnd = $onboardingCycleStart
                                ? $onboardingCycleStart->copy()->addMonthNoOverflow()->subDay()
                                : null;
                            $expectedCycleStart = $onboardingCycleEnd
                                ? $onboardingCycleEnd->copy()->addDay()
                                : null;
                            $expectedCycleEnd = $expectedCycleStart
                                ? $expectedCycleStart->copy()->addMonthNoOverflow()->subDay()
                                : null;
                            $statusClass = match ($status) {
                                'paid' => 'status-approved',
                                'overdue' => 'status-overdue',
                                'no_record' => 'status-neutral',
                                default => 'status-pending',
                            };
                            $statusIcon = match ($status) {
                                'paid' => 'bi-check-circle',
                                'overdue' => 'bi-exclamation-octagon',
                                'no_record' => 'bi-dash-circle',
                                default => 'bi-hourglass-split',
                            };
                            $statusLabel = match ($status) {
                                'paid' => 'Paid',
                                'overdue' => 'Overdue',
                                'no_record' => 'No Record',
                                default => 'Pending',
                            };
                            $statusNote = match ($status) {
                                'paid' => 'Payment received' . ($effectivePaidAt ? ' on ' . $effectivePaidAt->format('M d, Y') : ''),
                                'overdue' => 'Overdue since ' . ($dueDate ? $dueDate->format('M d, Y') : 'previous due date'),
                                'no_record' => 'No monthly payment submission yet' . ($dueDate ? ' for due date ' . $dueDate->format('M d, Y') : ''),
                                default => 'Awaiting payment receipt' . ($dueDate ? ' by ' . $dueDate->format('M d, Y') : ''),
                            };

                            $latestTenantPayment = $booking->latest_tenant_payment ?? null;
                            $latestTenantPaymentStatus = strtolower((string) ($latestTenantPayment->status ?? ''));
                            $latestTenantPaymentStatusLabel = $latestTenantPayment
                                ? ucfirst($latestTenantPaymentStatus ?: 'submitted')
                                : null;
                            $onboarding = $booking->tenantOnboarding;
                            $onboardingStatus = strtolower((string) ($onboarding->status ?? ''));
                            $monthlyActionsLocked = $onboarding
                                && !((bool) ($onboarding->deposit_paid ?? false) || $onboardingStatus === 'completed');
                            $isOnboardingMonthlyHold = $monthlyActionsLocked || $status === 'onboarding';
                            if ($isOnboardingMonthlyHold && $expectedCycleStart) {
                                $dueDate = $expectedCycleStart->copy();
                            }
                            $cycleStartDate = $isOnboardingMonthlyHold
                                ? ($expectedCycleStart ? $expectedCycleStart->copy() : null)
                                : ($dueDate ? $dueDate->copy() : optional($booking->check_in)?->copy());
                            $cycleEndDate = $isOnboardingMonthlyHold
                                ? ($expectedCycleEnd ? $expectedCycleEnd->copy() : null)
                                : ($cycleStartDate ? $cycleStartDate->copy()->addMonthNoOverflow()->subDay() : null);
                            $cycleContextLabel = $isOnboardingMonthlyHold
                                ? 'Expected First Cycle'
                                : ($status === 'paid' ? 'Next Cycle' : 'Current Cycle');
                            $latestSubmissionToneClass = match ($latestTenantPaymentStatus) {
                                'approved' => 'submission-tone-approved',
                                'rejected' => 'submission-tone-rejected',
                                'submitted' => 'submission-tone-submitted',
                                default => 'submission-tone-empty',
                            };
                            $latestTenantPaymentTime = $latestTenantPayment
                                ? (optional($latestTenantPayment->submitted_at)->format('M d, Y h:i A') ?: optional($latestTenantPayment->created_at)->format('M d, Y h:i A'))
                                : null;
                            $latestBillingForDate = !empty($latestTenantPayment?->billing_for_date)
                                ? \Illuminate\Support\Carbon::parse((string) $latestTenantPayment->billing_for_date)->startOfDay()
                                : null;
                            $latestBillingForEndDate = $latestBillingForDate
                                ? $latestBillingForDate->copy()->addMonthNoOverflow()->subDay()
                                : null;
                            $latestBillingForLabel = $latestBillingForDate
                                ? $latestBillingForDate->format('M d, Y') . ' - ' . $latestBillingForEndDate->format('M d, Y')
                                : 'Not specified';
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
                                            <span class="amount-chip-label">Cycle Amount</span>
                                            <span class="amount-chip-value">PHP {{ number_format($monthlyCycleAmount, 2) }}</span>
                                        </span>
                                        <span class="amount-chip amount-chip-due-date">
                                            <span class="amount-chip-label">Due Date</span>
                                            <span class="amount-chip-value">{{ $dueDate ? $dueDate->format('M d, Y') : 'N/A' }}</span>
                                        </span>
                                    </div>
                                </div>

                                <div class="payment-meta-row">
                                    <span class="meta-chip"><i class="bi bi-building"></i>{{ $booking->room->property->name }}</span>
                                    <span class="meta-chip"><i class="bi bi-door-open"></i>{{ $booking->room->room_number }}</span>
                                    <span class="meta-chip meta-chip-optional"><i class="bi bi-calendar-range"></i>{{ $booking->check_in->format('M d, Y') }} - {{ $booking->check_out->format('M d, Y') }}</span>
                                    <span class="meta-chip meta-chip-optional"><i class="bi bi-clock"></i>{{ $booking->getDurationInDays() }} day{{ $booking->getDurationInDays() === 1 ? '' : 's' }}</span>
                                    <span class="meta-chip"><i class="bi bi-calendar-check"></i>Due {{ $dueDate ? $dueDate->format('M d, Y') : 'N/A' }}</span>
                                </div>

                                <div class="payment-ledger-grid mt-3">
                                    <div class="invoice-lines">
                                        <div class="invoice-line invoice-line-heading">
                                            <span>Monthly Billing Snapshot</span>
                                            <strong>{{ $cycleContextLabel }}</strong>
                                        </div>
                                        <div class="invoice-line">
                                            <span>Cycle Amount</span>
                                            <strong>PHP {{ number_format($currentDueAmount, 2) }}</strong>
                                        </div>
                                        <div class="invoice-line">
                                            <span>{{ $isOnboardingMonthlyHold ? 'Expected Due Date' : 'Due Date' }}</span>
                                            <strong>{{ $dueDate ? $dueDate->format('M d, Y') : 'N/A' }}</strong>
                                        </div>
                                        @if($isOnboardingMonthlyHold)
                                            <div class="invoice-line">
                                                <span>Onboarding Window</span>
                                                <strong>{{ $onboardingCycleStart ? $onboardingCycleStart->format('M d, Y') : 'N/A' }} - {{ $onboardingCycleEnd ? $onboardingCycleEnd->format('M d, Y') : 'N/A' }}</strong>
                                            </div>
                                        @else
                                            <div class="invoice-line">
                                                <span>Last Confirmed Payment</span>
                                                <strong>{{ $lastPaidDate ?: 'Not yet marked paid' }}</strong>
                                            </div>
                                        @endif
                                        <div class="invoice-line invoice-line-total">
                                            <span>Billing Cycle</span>
                                            <strong>{{ $cycleStartDate ? $cycleStartDate->format('M d, Y') : 'N/A' }} - {{ $cycleEndDate ? $cycleEndDate->format('M d, Y') : 'N/A' }}</strong>
                                        </div>
                                    </div>

                                    <div class="invoice-lines submission-lines {{ $latestSubmissionToneClass }}">
                                        <div class="invoice-line invoice-line-heading">
                                            <span>Latest Monthly Submission</span>
                                            <strong>{{ $latestTenantPaymentStatusLabel ?: 'No Record' }}</strong>
                                        </div>
                                        <div class="invoice-line">
                                            <span>Submitted At</span>
                                            <strong>{{ $latestTenantPaymentTime ?: 'No submission yet' }}</strong>
                                        </div>
                                        <div class="invoice-line invoice-line-detail">
                                            <span>Method / Ref</span>
                                            <strong>{{ $latestMethodAndRef }}</strong>
                                        </div>
                                        <div class="invoice-line">
                                            <span>Payment For</span>
                                            <strong>{{ $latestBillingForLabel }}</strong>
                                        </div>
                                        <div class="invoice-line invoice-line-hint">
                                            <span><i class="bi bi-arrow-right-circle me-1"></i>Use Manage Monthly for complete month-by-month history.</span>
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
                                @if($isOnboardingMonthlyHold)
                                    <div class="status-panel">
                                        <div class="status-panel-title">Monthly Activation</div>
                                        <div class="status-note">Monthly status is hidden until onboarding is completed.</div>
                                        <div class="status-note">Expected first cycle: {{ $cycleStartDate ? $cycleStartDate->format('M d, Y') : 'N/A' }} - {{ $cycleEndDate ? $cycleEndDate->format('M d, Y') : 'N/A' }}</div>
                                    </div>
                                @else
                                    <div class="status-panel">
                                        <div class="status-panel-title">Monthly Status</div>
                                        <span class="status-pill {{ $statusClass }}"><i class="bi {{ $statusIcon }}"></i>{{ $statusLabel }}</span>
                                        <div class="status-note">{{ $statusNote }}</div>
                                        <div class="status-note">Cycle Amount: PHP {{ number_format($currentDueAmount, 2) }}</div>
                                    </div>
                                @endif

                                <div class="payment-actions">
                                    <div class="action-panel-title">Actions</div>
                                    @unless($monthlyActionsLocked)
                                        <a href="{{ route('landlord.payments.manage', ['booking' => $booking->id, 'status' => $statusFilter]) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                            <i class="bi bi-journal-text me-1"></i>Manage Monthly
                                        </a>
                                    @endunless

                                    @if($latestTenantPayment && !empty($latestTenantPayment->payment_proof_path))
                                        <a href="{{ asset('storage/' . $latestTenantPayment->payment_proof_path) }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                                            <i class="bi bi-file-earmark-arrow-down me-1"></i>Latest Proof
                                        </a>
                                    @endif

                                    @if($monthlyActionsLocked)
                                        <div class="action-lock-note">
                                            <i class="bi bi-info-circle me-1"></i>This payment is still under onboarding.
                                        </div>
                                        <a href="{{ route('landlord.payments.index', ['tab' => 'onboarding', 'status' => 'all']) }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                                            <i class="bi bi-list-check me-1"></i>Go To Onboarding
                                        </a>
                                    @else
                                        @if($status === 'paid')
                                            <form id="monthlyUndoPaidDesktop{{ $booking->id }}" action="{{ route('landlord.payments.mark_pending', $booking->id) }}" method="POST" class="action-form">
                                                @csrf
                                                <button
                                                    type="button"
                                                    class="btn btn-sm btn-outline-warning rounded-pill px-3 js-payment-confirm"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#paymentConfirmModal"
                                                    data-confirm-title="Undo Paid Status"
                                                    data-confirm-message="Undo paid status for this booking?"
                                                    data-confirm-submit-label="Undo Paid"
                                                    data-confirm-tone="warning"
                                                    data-form-id="monthlyUndoPaidDesktop{{ $booking->id }}"
                                                >
                                                    <i class="bi bi-arrow-counterclockwise me-1"></i>Undo Paid
                                                </button>
                                            </form>
                                        @elseif($status === 'no_record')
                                            <div class="action-helper-note">
                                                <i class="bi bi-journal-plus me-1"></i>No monthly payment record yet. Use Manage Monthly, then click Create Payment.
                                            </div>
                                            <form id="monthlyReminderDesktop{{ $booking->id }}" action="{{ route('landlord.payments.remind', $booking->id) }}" method="POST" class="action-form">
                                                @csrf
                                                <button
                                                    type="button"
                                                    class="btn btn-sm btn-outline-danger rounded-pill px-3 js-payment-confirm"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#paymentConfirmModal"
                                                    data-confirm-title="Send Payment Reminder"
                                                    data-confirm-message="Send a payment reminder to this tenant?"
                                                    data-confirm-submit-label="Send Reminder"
                                                    data-confirm-tone="danger"
                                                    data-form-id="monthlyReminderDesktop{{ $booking->id }}"
                                                >
                                                    <i class="bi bi-bell me-1"></i>Send Reminder
                                                </button>
                                            </form>
                                        @else
                                            <form id="monthlyMarkPaidDesktop{{ $booking->id }}" action="{{ route('landlord.payments.mark_paid', $booking->id) }}" method="POST" class="action-form">
                                                @csrf
                                                <button
                                                    type="button"
                                                    class="btn btn-sm btn-success rounded-pill px-3 js-payment-confirm"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#paymentConfirmModal"
                                                    data-confirm-title="Mark Payment as Paid"
                                                    data-confirm-message="Mark this payment as received?"
                                                    data-confirm-submit-label="Mark Paid"
                                                    data-confirm-tone="success"
                                                    data-form-id="monthlyMarkPaidDesktop{{ $booking->id }}"
                                                >
                                                    <i class="bi bi-check2 me-1"></i>Mark Paid
                                                </button>
                                            </form>
                                            <form id="monthlyReminderDesktop{{ $booking->id }}" action="{{ route('landlord.payments.remind', $booking->id) }}" method="POST" class="action-form">
                                                @csrf
                                                <button
                                                    type="button"
                                                    class="btn btn-sm btn-outline-danger rounded-pill px-3 js-payment-confirm"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#paymentConfirmModal"
                                                    data-confirm-title="Send Payment Reminder"
                                                    data-confirm-message="Send a payment reminder to this tenant?"
                                                    data-confirm-submit-label="Send Reminder"
                                                    data-confirm-tone="danger"
                                                    data-form-id="monthlyReminderDesktop{{ $booking->id }}"
                                                >
                                                    <i class="bi bi-bell me-1"></i>Send Reminder
                                                </button>
                                            </form>
                                        @endif
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
                                        @if($isOnboardingMonthlyHold)
                                            <div class="status-panel mb-3">
                                                <div class="status-panel-title">Monthly Activation</div>
                                                <div class="status-note">Monthly status is hidden until onboarding is completed.</div>
                                                <div class="status-note">Expected first cycle: {{ $cycleStartDate ? $cycleStartDate->format('M d, Y') : 'N/A' }} - {{ $cycleEndDate ? $cycleEndDate->format('M d, Y') : 'N/A' }}</div>
                                            </div>
                                        @else
                                            <div class="status-panel mb-3">
                                                <div class="status-panel-title">Monthly Status</div>
                                                <span class="status-pill {{ $statusClass }}"><i class="bi {{ $statusIcon }}"></i>{{ $statusLabel }}</span>
                                                <div class="status-note">{{ $statusNote }}</div>
                                                <div class="status-note">Cycle Amount: PHP {{ number_format($currentDueAmount, 2) }}</div>
                                            </div>
                                        @endif

                                        <div class="payment-actions">
                                            <div class="action-panel-title">Actions</div>
                                            @unless($monthlyActionsLocked)
                                                <a href="{{ route('landlord.payments.manage', ['booking' => $booking->id, 'status' => $statusFilter]) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                                    <i class="bi bi-journal-text me-1"></i>Manage Monthly
                                                </a>
                                            @endunless

                                            @if($latestTenantPayment && !empty($latestTenantPayment->payment_proof_path))
                                                <a href="{{ asset('storage/' . $latestTenantPayment->payment_proof_path) }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                                                    <i class="bi bi-file-earmark-arrow-down me-1"></i>Latest Proof
                                                </a>
                                            @endif

                                            @if($monthlyActionsLocked)
                                                <div class="action-lock-note">
                                                    <i class="bi bi-info-circle me-1"></i>This payment is still under onboarding.
                                                </div>
                                                <a href="{{ route('landlord.payments.index', ['tab' => 'onboarding', 'status' => 'all']) }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                                                    <i class="bi bi-list-check me-1"></i>Go To Onboarding
                                                </a>
                                            @else
                                                @if($status === 'paid')
                                                    <form id="monthlyUndoPaidMobile{{ $booking->id }}" action="{{ route('landlord.payments.mark_pending', $booking->id) }}" method="POST" class="action-form">
                                                        @csrf
                                                        <button
                                                            type="button"
                                                            class="btn btn-sm btn-outline-warning rounded-pill px-3 js-payment-confirm"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#paymentConfirmModal"
                                                            data-confirm-title="Undo Paid Status"
                                                            data-confirm-message="Undo paid status for this booking?"
                                                            data-confirm-submit-label="Undo Paid"
                                                            data-confirm-tone="warning"
                                                            data-form-id="monthlyUndoPaidMobile{{ $booking->id }}"
                                                        >
                                                            <i class="bi bi-arrow-counterclockwise me-1"></i>Undo Paid
                                                        </button>
                                                    </form>
                                                @elseif($status === 'no_record')
                                                    <div class="action-helper-note">
                                                        <i class="bi bi-journal-plus me-1"></i>No monthly payment record yet. Use Manage Monthly, then click Create Payment.
                                                    </div>
                                                    <form id="monthlyReminderMobile{{ $booking->id }}" action="{{ route('landlord.payments.remind', $booking->id) }}" method="POST" class="action-form">
                                                        @csrf
                                                        <button
                                                            type="button"
                                                            class="btn btn-sm btn-outline-danger rounded-pill px-3 js-payment-confirm"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#paymentConfirmModal"
                                                            data-confirm-title="Send Payment Reminder"
                                                            data-confirm-message="Send a payment reminder to this tenant?"
                                                            data-confirm-submit-label="Send Reminder"
                                                            data-confirm-tone="danger"
                                                            data-form-id="monthlyReminderMobile{{ $booking->id }}"
                                                        >
                                                            <i class="bi bi-bell me-1"></i>Send Reminder
                                                        </button>
                                                    </form>
                                                @else
                                                    <form id="monthlyMarkPaidMobile{{ $booking->id }}" action="{{ route('landlord.payments.mark_paid', $booking->id) }}" method="POST" class="action-form">
                                                        @csrf
                                                        <button
                                                            type="button"
                                                            class="btn btn-sm btn-success rounded-pill px-3 js-payment-confirm"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#paymentConfirmModal"
                                                            data-confirm-title="Mark Payment as Paid"
                                                            data-confirm-message="Mark this payment as received?"
                                                            data-confirm-submit-label="Mark Paid"
                                                            data-confirm-tone="success"
                                                            data-form-id="monthlyMarkPaidMobile{{ $booking->id }}"
                                                        >
                                                            <i class="bi bi-check2 me-1"></i>Mark Paid
                                                        </button>
                                                    </form>
                                                    <form id="monthlyReminderMobile{{ $booking->id }}" action="{{ route('landlord.payments.remind', $booking->id) }}" method="POST" class="action-form">
                                                        @csrf
                                                        <button
                                                            type="button"
                                                            class="btn btn-sm btn-outline-danger rounded-pill px-3 js-payment-confirm"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#paymentConfirmModal"
                                                            data-confirm-title="Send Payment Reminder"
                                                            data-confirm-message="Send a payment reminder to this tenant?"
                                                            data-confirm-submit-label="Send Reminder"
                                                            data-confirm-tone="danger"
                                                            data-form-id="monthlyReminderMobile{{ $booking->id }}"
                                                        >
                                                            <i class="bi bi-bell me-1"></i>Send Reminder
                                                        </button>
                                                    </form>
                                                @endif
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
        </div>
    </section>
</div>

<div class="modal fade" id="paymentConfirmModal" tabindex="-1" aria-labelledby="paymentConfirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content action-confirm-modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="paymentConfirmModalLabel">Confirm Action</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="action-confirm-body">
                    <div id="paymentConfirmIconWrap" class="action-confirm-icon-wrap tone-primary">
                        <i id="paymentConfirmIcon" class="bi bi-question-circle"></i>
                    </div>
                    <p id="paymentConfirmMessage" class="mb-0 text-muted">Are you sure you want to continue?</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                <button type="button" id="paymentConfirmSubmitBtn" class="btn rounded-pill px-3 action-confirm-submit btn-primary">Confirm</button>
            </div>
        </div>
    </div>
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
    .billing-tabs {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: .55rem;
    }
    .billing-tab {
        border: 1px solid rgba(2,8,20,.12);
        border-radius: .85rem;
        background: #ffffff;
        color: #334155;
        font-weight: 700;
        font-size: .9rem;
        text-decoration: none;
        padding: .62rem .8rem;
        display: inline-flex;
        justify-content: center;
        align-items: center;
        transition: all .2s ease;
    }
    .billing-tab:hover {
        border-color: rgba(37,99,235,.35);
        color: #1e40af;
        background: #f8fbff;
    }
    .billing-tab.active {
        background: linear-gradient(135deg, #0f766e, #115e59);
        border-color: #115e59;
        color: #ffffff;
        box-shadow: 0 10px 22px rgba(15,118,110,.25);
    }
    .onboarding-summary {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: .65rem;
    }
    .onboarding-summary-item {
        border: 1px solid rgba(20,83,45,.14);
        border-radius: .85rem;
        background: linear-gradient(180deg, rgba(167,243,208,.2), rgba(255,255,255,.96));
        padding: .62rem .75rem;
    }
    .onboarding-summary-item.paid {
        border-color: rgba(22,163,74,.24);
        background: linear-gradient(180deg, rgba(220,252,231,.9), rgba(255,255,255,.98));
    }
    .onboarding-summary-item.pending {
        border-color: rgba(251,146,60,.3);
        background: linear-gradient(180deg, rgba(255,237,213,.92), rgba(255,255,255,.98));
    }
    .onboarding-summary-label {
        font-size: .7rem;
        text-transform: uppercase;
        letter-spacing: .05em;
        color: rgba(2,8,20,.55);
        font-weight: 700;
        margin-bottom: .15rem;
    }
    .onboarding-summary-value {
        font-size: .95rem;
        font-weight: 800;
        color: #0f172a;
    }
    .onboarding-list-card {
        border: 1px solid rgba(2,8,20,.09);
        border-radius: 1rem;
        background: #fff;
        overflow: hidden;
    }
    .onboarding-item {
        padding: .95rem;
        border-bottom: 1px solid rgba(2,8,20,.08);
    }
    .onboarding-item:last-child {
        border-bottom: none;
    }
    .onboarding-head {
        display: flex;
        justify-content: space-between;
        gap: .6rem;
        align-items: center;
    }
    .onboarding-state {
        display: inline-flex;
        align-items: center;
        border-radius: 999px;
        padding: .2rem .62rem;
        font-size: .76rem;
        font-weight: 700;
        border: 1px solid transparent;
        white-space: nowrap;
    }
    .onboarding-state-paid {
        color: #14532d;
        background: #dcfce7;
        border-color: #86efac;
    }
    .onboarding-state-pending {
        color: #7c2d12;
        background: #ffedd5;
        border-color: #fdba74;
    }
    .onboarding-state-progress {
        color: #1e3a8a;
        background: #dbeafe;
        border-color: #93c5fd;
    }
    .onboarding-state-neutral {
        color: #334155;
        background: #f1f5f9;
        border-color: #cbd5e1;
    }
    .onboarding-ledger-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: .55rem;
    }
    .onboarding-foot {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: .7rem;
    }
    .onboarding-note {
        font-size: .82rem;
        color: #475569;
    }
    .monthly-summary {
        display: flex;
        flex-wrap: wrap;
        gap: .45rem;
    }
    .monthly-summary-chip {
        display: inline-flex;
        align-items: center;
        gap: .3rem;
        border-radius: 999px;
        padding: .22rem .6rem;
        font-size: .76rem;
        font-weight: 700;
        border: 1px solid transparent;
    }
    .monthly-summary-chip.paid {
        color: #14532d;
        background: #dcfce7;
        border-color: #86efac;
    }
    .monthly-summary-chip.pending {
        color: #7c2d12;
        background: #ffedd5;
        border-color: #fdba74;
    }
    .monthly-summary-chip.overdue {
        color: #7f1d1d;
        background: #fee2e2;
        border-color: #fca5a5;
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
    .amount-chip-due-date {
        border-color: rgba(59,130,246,.26);
        background: linear-gradient(180deg, rgba(219,234,254,.85), rgba(255,255,255,.98));
        color: #1e3a8a;
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
    .monthly-pane .payment-side-grid {
        grid-template-columns: 1fr;
        min-width: 250px;
    }
    .monthly-pane .payment-side-grid > div + div {
        border-left: none;
        border-top: 1px dashed rgba(100,116,139,.35);
        padding-left: 0;
        padding-top: .55rem;
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
    .status-neutral {
        color: #334155;
        background: #f1f5f9;
        border-color: #cbd5e1;
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
    .action-lock-note {
        border: 1px dashed rgba(59,130,246,.35);
        border-radius: .6rem;
        background: rgba(239,246,255,.7);
        color: #1e40af;
        font-size: .74rem;
        line-height: 1.35;
        padding: .42rem .5rem;
        text-align: center;
    }
    .action-helper-note {
        border: 1px dashed rgba(16,185,129,.35);
        border-radius: .6rem;
        background: rgba(236,253,245,.72);
        color: #065f46;
        font-size: .74rem;
        line-height: 1.35;
        padding: .42rem .5rem;
        text-align: center;
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
    .action-confirm-modal-content {
        border: 1px solid rgba(15,23,42,.12);
        border-radius: 1rem;
        box-shadow: 0 18px 38px rgba(2,8,20,.14);
        overflow: hidden;
    }
    .action-confirm-modal-content .modal-header {
        border-bottom: 1px solid rgba(148,163,184,.24);
        background: linear-gradient(180deg, rgba(248,250,252,.9), rgba(255,255,255,.98));
    }
    .action-confirm-body {
        display: grid;
        grid-template-columns: 36px minmax(0, 1fr);
        gap: .65rem;
        align-items: center;
    }
    .action-confirm-icon-wrap {
        width: 36px;
        height: 36px;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid transparent;
        font-size: 1rem;
    }
    .action-confirm-icon-wrap.tone-primary {
        background: rgba(37,99,235,.12);
        color: #1d4ed8;
        border-color: rgba(37,99,235,.3);
    }
    .action-confirm-icon-wrap.tone-success {
        background: rgba(22,163,74,.14);
        color: #15803d;
        border-color: rgba(22,163,74,.34);
    }
    .action-confirm-icon-wrap.tone-warning {
        background: rgba(245,158,11,.16);
        color: #b45309;
        border-color: rgba(245,158,11,.36);
    }
    .action-confirm-icon-wrap.tone-danger {
        background: rgba(239,68,68,.14);
        color: #b91c1c;
        border-color: rgba(239,68,68,.34);
    }
    .action-confirm-submit {
        min-width: 120px;
        font-weight: 700;
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
        .onboarding-summary {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
        .onboarding-summary-item:first-child {
            grid-column: 1 / -1;
        }
        .onboarding-ledger-grid {
            grid-template-columns: 1fr;
        }
        .onboarding-foot {
            flex-direction: column;
            align-items: flex-start;
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
        .billing-tabs {
            grid-template-columns: 1fr;
        }
        .billing-tab {
            justify-content: flex-start;
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
        .onboarding-summary {
            grid-template-columns: 1fr;
        }
        .payments-list-card {
            border: none;
            border-radius: 0;
            background: transparent;
            padding: .55rem 0 .2rem;
        }
        .onboarding-list-card {
            border: none;
            border-radius: 0;
            background: transparent;
        }
        .onboarding-item {
            border: 1px solid rgba(2,8,20,.1);
            border-radius: .95rem;
            background: #fff;
            margin-bottom: .6rem;
        }
        .onboarding-item:last-child {
            margin-bottom: 0;
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const confirmModal = document.getElementById('paymentConfirmModal');
    const titleEl = document.getElementById('paymentConfirmModalLabel');
    const messageEl = document.getElementById('paymentConfirmMessage');
    const submitBtn = document.getElementById('paymentConfirmSubmitBtn');
    const iconWrap = document.getElementById('paymentConfirmIconWrap');
    const iconEl = document.getElementById('paymentConfirmIcon');

    if (!confirmModal || !titleEl || !messageEl || !submitBtn || !iconWrap || !iconEl) {
        return;
    }

    let targetForm = null;

    const submitToneClassMap = {
        primary: 'btn-primary',
        success: 'btn-success',
        warning: 'btn-warning',
        danger: 'btn-danger'
    };

    const iconToneClassMap = {
        primary: 'tone-primary',
        success: 'tone-success',
        warning: 'tone-warning',
        danger: 'tone-danger'
    };

    const iconClassMap = {
        primary: 'bi-question-circle',
        success: 'bi-check-circle',
        warning: 'bi-exclamation-circle',
        danger: 'bi-bell'
    };

    const setModalTone = (tone) => {
        const normalizedTone = ['primary', 'success', 'warning', 'danger'].includes(tone) ? tone : 'primary';

        submitBtn.classList.remove('btn-primary', 'btn-success', 'btn-warning', 'btn-danger');
        submitBtn.classList.add(submitToneClassMap[normalizedTone]);

        iconWrap.classList.remove('tone-primary', 'tone-success', 'tone-warning', 'tone-danger');
        iconWrap.classList.add(iconToneClassMap[normalizedTone]);

        iconEl.className = `bi ${iconClassMap[normalizedTone]}`;
    };

    confirmModal.addEventListener('show.bs.modal', function (event) {
        const trigger = event.relatedTarget;
        const formId = trigger ? trigger.getAttribute('data-form-id') : null;
        const tone = trigger?.getAttribute('data-confirm-tone') || 'primary';

        targetForm = formId ? document.getElementById(formId) : null;
        titleEl.textContent = trigger?.getAttribute('data-confirm-title') || 'Confirm Action';
        messageEl.textContent = trigger?.getAttribute('data-confirm-message') || 'Are you sure you want to continue?';
        submitBtn.textContent = trigger?.getAttribute('data-confirm-submit-label') || 'Confirm';
        submitBtn.disabled = !targetForm;
        setModalTone(tone);
    });

    submitBtn.addEventListener('click', function () {
        if (targetForm) {
            targetForm.submit();
        }
    });

    confirmModal.addEventListener('hidden.bs.modal', function () {
        targetForm = null;
        submitBtn.disabled = false;
        titleEl.textContent = 'Confirm Action';
        messageEl.textContent = 'Are you sure you want to continue?';
        submitBtn.textContent = 'Confirm';
        setModalTone('primary');
    });
});
</script>
@endpush
