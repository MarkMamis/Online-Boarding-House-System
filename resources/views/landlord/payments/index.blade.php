@extends('layouts.landlord')

@section('content')
<div class="payments-shell">
    @php
        $onboardingPaidTotal = (float) ($onboardingPaidTotal ?? 0);
        $onboardingPendingTotal = (float) ($onboardingPendingTotal ?? 0);
        $monthlyPaidTotal = (float) ($monthlyPaidTotal ?? 0);
        $monthlyPendingTotal = (float) ($monthlyPendingTotal ?? 0);
        $statusFilter = $statusFilter ?? 'all';
        $activeTab = strtolower((string) request('tab', 'monthly'));
        if (!in_array($activeTab, ['monthly', 'onboarding'], true)) {
            $activeTab = 'monthly';
        }

        $allBookingsCollection = collect($allBookings ?? $bookings);
        $onboardingBookings = $allBookingsCollection;
        if ($statusFilter === 'pending') {
            $onboardingBookings = $onboardingBookings->filter(function ($b) {
                $o = $b->tenantOnboarding;
                return $o && (string) $o->status === 'deposit_paid' && !(bool) $o->deposit_paid;
            });
        } elseif ($statusFilter === 'paid') {
            $onboardingBookings = $onboardingBookings->filter(function ($b) {
                $o = $b->tenantOnboarding;
                return $o && ((bool) $o->deposit_paid || (string) $o->status === 'completed');
            });
        }

        $totalPendingReviewCount = (int) ($totalPendingReviewCount ?? 0);
        $onboardingPendingReviewCount = (int) ($onboardingPendingReviewCount ?? 0);
        $monthlyPendingReviewCount = (int) ($monthlyPendingReviewCount ?? 0);

        $monthlyTabUrl = route('landlord.payments.index', ['tab' => 'monthly', 'status' => $statusFilter]);
        $onboardingTabUrl = route('landlord.payments.index', ['tab' => 'onboarding', 'status' => $statusFilter]);
    @endphp

    {{-- Page Header --}}
    <header class="payments-page-header mb-4">
        <div class="text-uppercase small text-muted fw-semibold">Finance</div>
        <h1 class="h3 mb-1 fw-bold text-dark">Payments</h1>
        <p class="text-muted small mb-0">Review and track payments from your tenants.</p>
    </header>

    @if(session('success'))
        <div class="alert alert-success rounded-4 mb-4 d-flex align-items-center gap-2">
            <i class="bi bi-check-circle-fill text-success fs-5"></i>
            <div>{{ session('success') }}</div>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger rounded-4 mb-4 d-flex align-items-center gap-2">
            <i class="bi bi-exclamation-triangle-fill text-danger fs-5"></i>
            <div>{{ session('error') }}</div>
        </div>
    @endif

    <!-- 2. Priority Summary: Payments Awaiting Review -->
    @if($totalPendingReviewCount > 0)
        <div class="priority-review-banner mb-4">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="priority-icon-circle">
                        <i class="bi bi-hourglass-split"></i>
                    </div>
                    <div>
                        <div class="fw-bold text-dark">
                            {{ $totalPendingReviewCount }} {{ Str::plural('payment', $totalPendingReviewCount) }} awaiting your review
                        </div>
                        <div class="small text-muted">
                            @if($activeTab === 'monthly')
                                {{ $monthlyPendingReviewCount }} monthly rent submission(s) pending verification.
                            @else
                                {{ $onboardingPendingReviewCount }} move-in deposit submission(s) pending verification.
                            @endif
                        </div>
                    </div>
                </div>
                @if($statusFilter !== 'pending')
                    <a href="{{ route('landlord.payments.index', ['tab' => $activeTab, 'status' => 'pending']) }}" class="btn btn-sm btn-brand rounded-pill px-3">
                        <i class="bi bi-funnel me-1"></i> View Pending
                    </a>
                @endif
            </div>
        </div>
    @endif

    <!-- 3. Payment Type Selector (Segmented Control) -->
    <div class="payment-type-selector mb-4" role="tablist" aria-label="Payment Types">
        <a href="{{ $monthlyTabUrl }}"
           class="type-pill {{ $activeTab === 'monthly' ? 'active' : '' }}"
           aria-current="{{ $activeTab === 'monthly' ? 'page' : 'false' }}">
            <i class="bi bi-calendar2-month me-1"></i> Monthly Rent
            @if($monthlyPendingReviewCount > 0)
                <span class="badge bg-danger rounded-pill ms-1">{{ $monthlyPendingReviewCount }}</span>
            @endif
        </a>
        <a href="{{ $onboardingTabUrl }}"
           class="type-pill {{ $activeTab === 'onboarding' ? 'active' : '' }}"
           aria-current="{{ $activeTab === 'onboarding' ? 'page' : 'false' }}">
            <i class="bi bi-key me-1"></i> Move-in Payments
            @if($onboardingPendingReviewCount > 0)
                <span class="badge bg-danger rounded-pill ms-1">{{ $onboardingPendingReviewCount }}</span>
            @endif
        </a>
    </div>

    <!-- 4. Type-Specific Sub-Header with Collections Summary & Filter -->
    <div class="type-header-bar d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
        <div class="type-summary-text">
            @if($activeTab === 'monthly')
                <span class="text-muted small">Confirmed monthly collections:</span>
                <strong class="text-success-emphasis ms-1">₱{{ number_format($monthlyPaidTotal, 2) }}</strong>
                <span class="badge bg-light text-secondary border ms-2">{{ $paidCount }} confirmed</span>
            @else
                <span class="text-muted small">Confirmed move-in dues:</span>
                <strong class="text-success-emphasis ms-1">₱{{ number_format($onboardingPaidTotal, 2) }}</strong>
                <span class="badge bg-light text-secondary border ms-2">{{ $allBookingsCollection->filter(fn($b) => $b->tenantOnboarding && ((bool)$b->tenantOnboarding->deposit_paid || (string)$b->tenantOnboarding->status === 'completed'))->count() }} confirmed</span>
            @endif
        </div>

        <form method="GET" action="{{ route('landlord.payments.index') }}" class="payments-filter-form d-flex align-items-center gap-2">
            <input type="hidden" name="tab" value="{{ $activeTab }}">
            <label for="statusFilterSelect" class="visually-hidden">Filter Status</label>
            <select id="statusFilterSelect" name="status" class="form-select form-select-sm rounded-pill" onchange="this.form.submit()">
                <option value="all" @selected($statusFilter === 'all')>All Statuses</option>
                <option value="pending" @selected($statusFilter === 'pending')>Pending Review</option>
                <option value="paid" @selected($statusFilter === 'paid')>Paid</option>
                @if($activeTab === 'monthly')
                    <option value="overdue" @selected($statusFilter === 'overdue')>Overdue</option>
                @endif
            </select>
            @if($statusFilter !== 'all')
                <a href="{{ route('landlord.payments.index', ['tab' => $activeTab]) }}" class="btn btn-sm btn-outline-secondary rounded-pill px-2" title="Clear filter">
                    <i class="bi bi-x-circle"></i>
                </a>
            @endif
        </form>
    </div>

    <!-- 5. Content Panes -->
    @if($activeTab === 'monthly')
        <!-- MONTHLY RENT VIEW -->
        <div class="payments-records-wrap">
            @if($bookings->isNotEmpty())
                <div class="vstack gap-3">
                    @foreach($bookings as $booking)
                        @php
                            $status = strtolower((string) ($booking->effective_payment_status ?? $booking->payment_status ?? 'pending'));
                            $dueDate = $booking->effective_due_date;
                            $monthlyRent = (float) ($booking->monthly_rent_amount ?? $booking->room->price);
                            $currentDueAmount = $monthlyRent;
                            $effectivePaidAt = $booking->effective_payment_date ?? null;
                            $lastPaidDate = optional($effectivePaidAt)->format('M d, Y');

                            $latestTenantPayment = $booking->latest_tenant_payment ?? null;
                            $latestTenantPaymentStatus = strtolower((string) ($latestTenantPayment->status ?? ''));
                            $latestTenantPaymentTime = $latestTenantPayment
                                ? (optional($latestTenantPayment->submitted_at)->format('M d, Y') ?: optional($latestTenantPayment->created_at)->format('M d, Y'))
                                : null;

                            $onboarding = $booking->tenantOnboarding;
                            $onboardingStatus = strtolower((string) ($onboarding->status ?? ''));
                            $monthlyActionsLocked = $onboarding && !((bool) ($onboarding->deposit_paid ?? false) || $onboardingStatus === 'completed');

                            $statusBadgeClass = match ($status) {
                                'paid' => 'status-pill-paid',
                                'overdue' => 'status-pill-overdue',
                                'no_record' => 'status-pill-neutral',
                                default => 'status-pill-pending',
                            };
                            $statusLabel = match ($status) {
                                'paid' => 'Paid',
                                'overdue' => 'Overdue',
                                'no_record' => 'No Record',
                                default => 'Pending',
                            };
                            $tenantName = (string) ($booking->student->full_name ?? $booking->student->name ?? 'Student');
                        @endphp

                        <article class="payment-card">
                            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start gap-3">
                                <!-- Tenant & Context -->
                                <div class="d-flex align-items-start gap-3 min-w-0">
                                    <div class="tenant-avatar">
                                        {{ strtoupper(substr($tenantName, 0, 1)) }}
                                    </div>
                                    <div class="min-w-0">
                                        <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                                            <h2 class="h6 mb-0 fw-bold text-dark text-truncate">{{ $tenantName }}</h2>
                                            <span class="status-pill {{ $statusBadgeClass }}">{{ $statusLabel }}</span>
                                        </div>
                                        <div class="text-muted small mb-2 d-flex align-items-center gap-2 flex-wrap">
                                            <span><i class="bi bi-building me-1"></i>{{ $booking->room->property->name }}</span>
                                            <span>&bull;</span>
                                            <span><i class="bi bi-door-open me-1"></i>{{ $booking->room->room_number }}</span>
                                        </div>

                                        <div class="payment-meta-pills d-flex flex-wrap gap-2 small">
                                            <span class="meta-pill">
                                                <i class="bi bi-calendar-event me-1"></i>Due: {{ $dueDate ? $dueDate->format('M d, Y') : 'N/A' }}
                                            </span>
                                            @if($status === 'paid' && $lastPaidDate)
                                                <span class="meta-pill text-success">
                                                    <i class="bi bi-check-circle me-1"></i>Paid: {{ $lastPaidDate }}
                                                </span>
                                            @endif
                                            @if($latestTenantPayment && !empty($latestTenantPayment->payment_method))
                                                <span class="meta-pill">
                                                    <i class="bi bi-credit-card me-1"></i>{{ ucfirst($latestTenantPayment->payment_method) }}
                                                    @if(!empty($latestTenantPayment->payment_reference))
                                                        #{{ Str::limit($latestTenantPayment->payment_reference, 12) }}
                                                    @endif
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <!-- Amount & Actions -->
                                <div class="text-md-end w-100 w-md-auto d-flex flex-md-column justify-content-between align-items-end gap-2 pt-2 pt-md-0 border-top border-md-0">
                                    <div>
                                        <div class="payment-amount text-dark fw-bold fs-5">
                                            ₱{{ number_format($currentDueAmount, 2) }}
                                        </div>
                                        <div class="small text-muted">Monthly Rent</div>
                                    </div>

                                    <div class="payment-card-actions d-flex flex-wrap gap-2 mt-2 justify-content-end">
                                        @if($latestTenantPayment && !empty($latestTenantPayment->payment_proof_path))
                                            <a href="{{ file_download_url($latestTenantPayment->payment_proof_path) }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                                                <i class="bi bi-file-earmark-arrow-down me-1"></i> Proof
                                            </a>
                                        @endif

                                        @if($monthlyActionsLocked)
                                            <a href="{{ route('landlord.payments.index', ['tab' => 'onboarding']) }}" class="btn btn-sm btn-outline-warning rounded-pill px-3">
                                                <i class="bi bi-lock me-1"></i> Under Move-in
                                            </a>
                                        @else
                                            @if($status === 'paid')
                                                <form id="monthlyUndoPaid{{ $booking->id }}" action="{{ route('landlord.payments.mark_pending', $booking->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button
                                                        type="button"
                                                        class="btn btn-sm btn-outline-warning rounded-pill px-3 js-payment-confirm"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#paymentConfirmModal"
                                                        data-confirm-title="Undo Paid Status"
                                                        data-confirm-message="Undo paid status for this monthly payment?"
                                                        data-confirm-submit-label="Undo Paid"
                                                        data-confirm-tone="warning"
                                                        data-form-id="monthlyUndoPaid{{ $booking->id }}"
                                                    >
                                                        <i class="bi bi-arrow-counterclockwise me-1"></i> Undo
                                                    </button>
                                                </form>
                                            @elseif($status === 'no_record')
                                                <a href="{{ route('landlord.payments.manage', ['booking' => $booking->id, 'status' => $statusFilter]) }}" class="btn btn-sm btn-outline-brand rounded-pill px-3">
                                                    <i class="bi bi-journal-text me-1"></i> Manage
                                                </a>
                                                <form id="monthlyRemind{{ $booking->id }}" action="{{ route('landlord.payments.remind', $booking->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button
                                                        type="button"
                                                        class="btn btn-sm btn-outline-danger rounded-pill px-3 js-payment-confirm"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#paymentConfirmModal"
                                                        data-confirm-title="Send Payment Reminder"
                                                        data-confirm-message="Send a monthly rent reminder to this tenant?"
                                                        data-confirm-submit-label="Send Reminder"
                                                        data-confirm-tone="danger"
                                                        data-form-id="monthlyRemind{{ $booking->id }}"
                                                    >
                                                        <i class="bi bi-bell me-1"></i> Remind
                                                    </button>
                                                </form>
                                            @else
                                                <form id="monthlyMarkPaid{{ $booking->id }}" action="{{ route('landlord.payments.mark_paid', $booking->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button
                                                        type="button"
                                                        class="btn btn-sm btn-success rounded-pill px-3 js-payment-confirm"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#paymentConfirmModal"
                                                        data-confirm-title="Mark Payment as Received"
                                                        data-confirm-message="Confirm that monthly rent of ₱{{ number_format($currentDueAmount, 2) }} was received?"
                                                        data-confirm-submit-label="Mark Paid"
                                                        data-confirm-tone="success"
                                                        data-form-id="monthlyMarkPaid{{ $booking->id }}"
                                                    >
                                                        <i class="bi bi-check2 me-1"></i> Mark Paid
                                                    </button>
                                                </form>
                                                <a href="{{ route('landlord.payments.manage', ['booking' => $booking->id, 'status' => $statusFilter]) }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                                                    <i class="bi bi-journal-text me-1"></i> Manage
                                                </a>
                                            @endif
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            @else
                <div class="empty-payments-card text-center py-5 px-3 bg-white rounded-4 border">
                    <i class="bi bi-calendar-check text-muted fs-1 mb-2"></i>
                    <h3 class="h5 mb-1 fw-bold text-dark">
                        {{ $statusFilter !== 'all' ? 'No Matching Monthly Payments' : 'No Monthly Payments Recorded' }}
                    </h3>
                    <p class="text-muted small mb-0">
                        {{ $statusFilter !== 'all' ? 'Try changing or resetting your filter option.' : 'Monthly billing records will appear here as your active tenants enter billing cycles.' }}
                    </p>
                </div>
            @endif
        </div>
    @else
        <!-- MOVE-IN PAYMENTS VIEW -->
        <div class="payments-records-wrap">
            @if($onboardingBookings->isNotEmpty())
                <div class="vstack gap-3">
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
                            $hasSubmittedDeposit = $onboarding && $onboardingStatus === 'deposit_paid' && !(bool) $onboarding->deposit_paid;
                            $hasSignedContract = $onboarding && (bool) $onboarding->contract_signed;

                            if ($hasOnboardingPaid) {
                                $onboardingStateLabel = 'Paid';
                                $onboardingStateClass = 'status-pill-paid';
                            } elseif ($hasSubmittedDeposit) {
                                $onboardingStateLabel = 'For Review';
                                $onboardingStateClass = 'status-pill-pending';
                            } elseif ($hasSignedContract) {
                                $onboardingStateLabel = 'Contract Signed';
                                $onboardingStateClass = 'status-pill-neutral';
                            } elseif ($onboarding) {
                                $onboardingStateLabel = 'In Progress';
                                $onboardingStateClass = 'status-pill-neutral';
                            } else {
                                $onboardingStateLabel = 'Not Started';
                                $onboardingStateClass = 'status-pill-neutral';
                            }

                            $onboardingProofPath = $onboarding?->payment_proof_path;
                            $onboardingSubmittedAt = optional($onboarding?->payment_submitted_at)->format('M d, Y');
                            $tenantName = (string) ($booking->student->full_name ?? $booking->student->name ?? 'Student');
                        @endphp

                        <article class="payment-card">
                            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start gap-3">
                                <!-- Tenant Info -->
                                <div class="d-flex align-items-start gap-3 min-w-0">
                                    <div class="tenant-avatar">
                                        {{ strtoupper(substr($tenantName, 0, 1)) }}
                                    </div>
                                    <div class="min-w-0">
                                        <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                                            <h2 class="h6 mb-0 fw-bold text-dark text-truncate">{{ $tenantName }}</h2>
                                            <span class="status-pill {{ $onboardingStateClass }}">{{ $onboardingStateLabel }}</span>
                                        </div>
                                        <div class="text-muted small mb-2 d-flex align-items-center gap-2 flex-wrap">
                                            <span><i class="bi bi-building me-1"></i>{{ $booking->room->property->name }}</span>
                                            <span>&bull;</span>
                                            <span><i class="bi bi-door-open me-1"></i>{{ $booking->room->room_number }}</span>
                                        </div>

                                        <div class="payment-meta-pills d-flex flex-wrap gap-2 small">
                                            <span class="meta-pill">
                                                <i class="bi bi-box-arrow-in-right me-1"></i>Check-in: {{ $booking->check_in->format('M d, Y') }}
                                            </span>
                                            @if($advanceIncluded)
                                                <span class="meta-pill">
                                                    <i class="bi bi-info-circle me-1"></i>Deposit + Advance Rent
                                                </span>
                                            @else
                                                <span class="meta-pill">
                                                    <i class="bi bi-info-circle me-1"></i>Initial Rent Deposit
                                                </span>
                                            @endif
                                            @if($onboarding && !empty($onboarding->payment_method))
                                                <span class="meta-pill">
                                                    <i class="bi bi-credit-card me-1"></i>{{ ucfirst($onboarding->payment_method) }}
                                                    @if(!empty($onboarding->payment_reference))
                                                        #{{ Str::limit($onboarding->payment_reference, 12) }}
                                                    @endif
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <!-- Amount & Actions -->
                                <div class="text-md-end w-100 w-md-auto d-flex flex-md-column justify-content-between align-items-end gap-2 pt-2 pt-md-0 border-top border-md-0">
                                    <div>
                                        <div class="payment-amount text-dark fw-bold fs-5">
                                            ₱{{ number_format($onboardingAmount, 2) }}
                                        </div>
                                        <div class="small text-muted">Move-in Obligation</div>
                                    </div>

                                    <div class="payment-card-actions d-flex flex-wrap gap-2 mt-2 justify-content-end">
                                        @if($onboardingProofPath)
                                            <a href="{{ file_download_url($onboardingProofPath) }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                                                <i class="bi bi-file-earmark-arrow-down me-1"></i> Proof
                                            </a>
                                        @endif

                                        @if($hasSubmittedDeposit)
                                            <form action="{{ route('landlord.onboarding.approve_documents', $onboarding) }}" method="POST" class="d-inline">
                                                @csrf
                                                <input type="hidden" name="action" value="approve_payment">
                                                <button type="submit" class="btn btn-sm btn-success rounded-pill px-3" onclick="return confirm('Approve move-in payment of ₱{{ number_format($onboardingAmount, 2) }} and mark as paid?')">
                                                    <i class="bi bi-check2 me-1"></i> Mark Paid
                                                </button>
                                            </form>
                                        @endif

                                        @if($onboarding)
                                            <a href="{{ route('landlord.onboarding.review', $onboarding) }}" class="btn btn-sm btn-outline-brand rounded-pill px-3">
                                                <i class="bi bi-list-check me-1"></i> View Details
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            @else
                <div class="empty-payments-card text-center py-5 px-3 bg-white rounded-4 border">
                    <i class="bi bi-key text-muted fs-1 mb-2"></i>
                    <h3 class="h5 mb-1 fw-bold text-dark">
                        {{ $statusFilter !== 'all' ? 'No Matching Move-in Payments' : 'No Move-in Payments Found' }}
                    </h3>
                    <p class="text-muted small mb-0">
                        {{ $statusFilter !== 'all' ? 'Try resetting your filter option.' : 'Move-in dues and initial deposits appear here when students begin the check-in onboarding process.' }}
                    </p>
                </div>
            @endif
        </div>
    @endif
</div>

<!-- Generic Confirmation Modal -->
<div class="modal fade" id="paymentConfirmModal" tabindex="-1" aria-labelledby="paymentConfirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="paymentConfirmModalLabel">Confirm Action</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-4 text-center">
                <div class="confirm-icon-box mb-3 mx-auto">
                    <i class="bi bi-question-circle fs-1 text-primary"></i>
                </div>
                <p id="paymentConfirmMessage" class="mb-0 text-muted fs-6">Are you sure you want to proceed?</p>
            </div>
            <div class="modal-footer border-0 pt-0 justify-content-center">
                <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                <button type="button" id="paymentConfirmSubmitBtn" class="btn btn-primary rounded-pill px-4">Confirm</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .payments-shell {
        max-width: 1040px;
        margin: 0 auto;
        padding-bottom: 3rem;
    }

    /* Header */
    .payments-page-header {
        border-bottom: 1px solid #f1f5f9;
        padding-bottom: 1rem;
    }

    /* Priority Banner */
    .priority-review-banner {
        background: #fefce8;
        border: 1px solid #fef08a;
        border-radius: 1rem;
        padding: 1rem 1.25rem;
    }

    .priority-icon-circle {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: #fde047;
        color: #854d0e;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.15rem;
        flex-shrink: 0;
    }

    /* Type Selector (Segmented Control) */
    .payment-type-selector {
        display: inline-flex;
        background: #f1f5f9;
        border-radius: 999px;
        padding: 0.3rem;
        gap: 0.35rem;
    }

    .type-pill {
        display: inline-flex;
        align-items: center;
        padding: 0.45rem 1.15rem;
        border-radius: 999px;
        font-size: 0.88rem;
        font-weight: 600;
        color: #64748b;
        text-decoration: none;
        transition: all 0.15s ease;
    }

    .type-pill:hover {
        color: #0f172a;
    }

    .type-pill.active {
        background: #ffffff;
        color: #14532d;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.06);
    }

    /* Cards */
    .payment-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 1.15rem;
        padding: 1.25rem 1.4rem;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }

    .payment-card:hover {
        border-color: #cbd5e1;
        box-shadow: 0 4px 14px rgba(15, 23, 42, 0.04);
    }

    .tenant-avatar {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        background: #ecfdf5;
        color: #047857;
        font-weight: 700;
        font-size: 1.1rem;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    /* Status Pills */
    .status-pill {
        display: inline-flex;
        align-items: center;
        padding: 0.2rem 0.65rem;
        border-radius: 999px;
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }

    .status-pill-paid {
        background: #dcfce7;
        color: #15803d;
    }

    .status-pill-pending {
        background: #fef9c3;
        color: #854d0e;
    }

    .status-pill-overdue {
        background: #fee2e2;
        color: #b91c1c;
    }

    .status-pill-neutral {
        background: #f1f5f9;
        color: #64748b;
    }

    .meta-pill {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 0.4rem;
        padding: 0.2rem 0.5rem;
        font-size: 0.76rem;
        color: #475569;
    }

    .confirm-icon-box {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        background: #f0fdf4;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    /* Responsive */
    @media (max-width: 576px) {
        .payment-card {
            padding: 1rem;
        }

        .payment-type-selector {
            width: 100%;
            display: flex;
        }

        .type-pill {
            flex: 1;
            justify-content: center;
            padding: 0.45rem 0.5rem;
            font-size: 0.8rem;
        }

        .payment-card-actions {
            width: 100%;
            justify-content: stretch !important;
        }

        .payment-card-actions .btn {
            flex: 1;
            text-align: center;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    (function () {
        'use strict';

        let targetFormToSubmit = null;
        const confirmModalEl = document.getElementById('paymentConfirmModal');
        const confirmMessageEl = document.getElementById('paymentConfirmMessage');
        const confirmTitleEl = document.getElementById('paymentConfirmModalLabel');
        const confirmSubmitBtn = document.getElementById('paymentConfirmSubmitBtn');

        document.querySelectorAll('.js-payment-confirm').forEach((btn) => {
            btn.addEventListener('click', function () {
                const title = btn.getAttribute('data-confirm-title') || 'Confirm Action';
                const message = btn.getAttribute('data-confirm-message') || 'Are you sure you want to proceed?';
                const submitLabel = btn.getAttribute('data-confirm-submit-label') || 'Confirm';
                const tone = btn.getAttribute('data-confirm-tone') || 'primary';
                const formId = btn.getAttribute('data-form-id');

                if (confirmTitleEl) confirmTitleEl.textContent = title;
                if (confirmMessageEl) confirmMessageEl.textContent = message;
                if (confirmSubmitBtn) {
                    confirmSubmitBtn.textContent = submitLabel;
                    confirmSubmitBtn.className = 'btn rounded-pill px-4 btn-' + tone;
                }

                targetFormToSubmit = formId ? document.getElementById(formId) : null;
            });
        });

        if (confirmSubmitBtn) {
            confirmSubmitBtn.addEventListener('click', function () {
                if (targetFormToSubmit) {
                    targetFormToSubmit.submit();
                }
            });
        }
    })();
</script>
@endpush
