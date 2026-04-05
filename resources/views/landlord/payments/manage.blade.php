@extends('layouts.landlord')

@section('content')
@php
    $statusFilter = $statusFilter ?? 'all';
    $dueDate = $booking->effective_due_date ?? $booking->resolvePaymentDueDate();
    $monthlyRent = (float) ($booking->monthly_rent_amount ?? $booking->room->price);
    $advanceAmount = !empty($booking->include_advance_payment) ? $monthlyRent : 0.0;
    $moveInTotal = $monthlyRent + $advanceAmount;

    $records = $booking->tenantPayments ?? collect();
    $totalRecordCount = 1 + $records->count(); // include initial billing entry

    $defaultBillingMonth = optional($dueDate)->format('Y-m') ?: now()->format('Y-m');
    $manualStatusOld = old('status', 'approved');
    $manualMethodOld = old('payment_method', 'cash');

    $onboarding = $booking->tenantOnboarding;
    $initialBillingStatus = 'recorded';
    if (!empty($onboarding) && !empty($onboarding->deposit_paid)) {
        $initialBillingStatus = 'approved';
    } elseif (!empty($onboarding) && (string) $onboarding->status === 'deposit_paid') {
        $initialBillingStatus = 'submitted';
    }
@endphp

<div class="records-shell">
    <section class="records-section records-intro-section mb-4">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
            <div>
                <div class="text-uppercase small text-muted fw-semibold">Finance</div>
                <h1 class="h3 mb-1">Manage Monthly Payments</h1>
                <div class="text-muted small">Payment records only view for this tenant, including onboarding and monthly records.</div>
            </div>
            <a href="{{ route('landlord.payments.index', ['status' => $statusFilter]) }}" class="btn btn-outline-secondary rounded-pill px-3">
                <i class="bi bi-arrow-left me-1"></i>Back to Payments
            </a>
        </div>
    </section>

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

    <section class="records-section records-tenant-section mb-3">
        <div class="records-header">
            <div class="records-header-top">
                <div class="d-flex align-items-center gap-2 min-w-0">
                    <div class="student-avatar">{{ strtoupper(substr($booking->student->full_name ?? 'S', 0, 1)) }}</div>
                    <div class="min-w-0">
                        <div class="student-name text-truncate">{{ $booking->student->full_name }}</div>
                        <div class="student-email text-truncate">{{ $booking->student->email }}</div>
                    </div>
                </div>

                <button type="button" class="btn btn-success rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#createPaymentRecordModal">
                    <i class="bi bi-plus-circle me-1"></i>Create Payment
                </button>
            </div>

            <div class="records-header-meta">
                <span class="meta-chip"><i class="bi bi-building"></i>{{ $booking->room->property->name }}</span>
                <span class="meta-chip"><i class="bi bi-door-open"></i>Room {{ $booking->room->room_number }}</span>
                <span class="meta-chip"><i class="bi bi-calendar-check"></i>Due {{ $dueDate ? $dueDate->format('M d, Y') : 'N/A' }}</span>
            </div>
        </div>
    </section>

    <section class="records-section records-list-section">
        <section class="records-card">
            <div class="records-card-head">
                <h5 class="mb-0">Payment Records</h5>
                <span class="small text-muted">{{ $totalRecordCount }} record{{ $totalRecordCount === 1 ? '' : 's' }}</span>
            </div>

            <div class="records-list">
                <article class="record-item record-item-initial">
                    <div class="record-item-top">
                        <div>
                            <div class="record-title">Initial Billing (Onboarding)</div>
                            <div class="record-subtitle">One-time onboarding payment basis</div>
                        </div>
                        <span class="record-status status-{{ $initialBillingStatus }}">{{ ucfirst($initialBillingStatus) }}</span>
                    </div>

                    <div class="record-metrics">
                        <div class="record-metric">
                            <div class="k">Monthly Rent</div>
                            <div class="v">PHP {{ number_format($monthlyRent, 2) }}</div>
                        </div>
                        <div class="record-metric">
                            <div class="k">Advance</div>
                            <div class="v">PHP {{ number_format($advanceAmount, 2) }}</div>
                        </div>
                        <div class="record-metric">
                            <div class="k">Move-in Total</div>
                            <div class="v">PHP {{ number_format($moveInTotal, 2) }}</div>
                        </div>
                        <div class="record-metric">
                            <div class="k">Date Basis</div>
                            <div class="v">{{ optional($booking->check_in)->format('M d, Y') ?: '—' }}</div>
                        </div>
                    </div>
                </article>

                @forelse($records as $record)
                    @php
                        $recordStatus = strtolower((string) ($record->status ?? 'submitted'));
                    @endphp
                    <article class="record-item">
                        <div class="record-item-top">
                            <div>
                                <div class="record-title">{{ optional($record->billing_for_date)->format('F Y') ?: 'Billing Record' }}</div>
                                <div class="record-subtitle">Monthly payment record</div>
                            </div>
                            <span class="record-status status-{{ $recordStatus }}">{{ ucfirst($recordStatus) }}</span>
                        </div>

                        <div class="record-item-body">
                            <div class="record-item-content">
                                <div class="record-metrics">
                                    <div class="record-metric">
                                        <div class="k">Amount</div>
                                        <div class="v">PHP {{ number_format((float) ($record->amount_due ?? 0), 2) }}</div>
                                    </div>
                                    <div class="record-metric record-metric-optional">
                                        <div class="k">Method</div>
                                        <div class="v">{{ ucfirst((string) ($record->payment_method ?? '—')) }}</div>
                                    </div>
                                    <div class="record-metric">
                                        <div class="k">Due Date</div>
                                        <div class="v">{{ optional($record->due_date)->format('M d, Y') ?: '—' }}</div>
                                    </div>
                                    <div class="record-metric record-metric-optional">
                                        <div class="k">Submitted</div>
                                        <div class="v">{{ optional($record->submitted_at)->format('M d, Y h:i A') ?: optional($record->created_at)->format('M d, Y h:i A') }}</div>
                                    </div>
                                </div>

                                <div class="record-extras">
                                    <span class="record-extra"><strong>Reference:</strong> {{ $record->payment_reference ?: '—' }}</span>
                                </div>

                                @if(!empty($record->payment_notes))
                                    <div class="record-note">{{ $record->payment_notes }}</div>
                                @endif
                            </div>

                            <aside class="record-actions-card">
                                <div class="record-actions-title">Actions</div>

                                @if($recordStatus !== 'approved')
                                    <form method="POST" action="{{ route('landlord.payments.records.status', ['booking' => $booking->id, 'record' => $record->id]) }}" class="m-0">
                                        @csrf
                                        <input type="hidden" name="status" value="approved">
                                        <input type="hidden" name="status_context" value="{{ $statusFilter }}">
                                        <button type="submit" class="btn btn-sm btn-success rounded-pill px-2">
                                            <i class="bi bi-check2 me-1"></i>Approve
                                        </button>
                                    </form>
                                @endif

                                @if($recordStatus !== 'submitted')
                                    <form method="POST" action="{{ route('landlord.payments.records.status', ['booking' => $booking->id, 'record' => $record->id]) }}" class="m-0">
                                        @csrf
                                        <input type="hidden" name="status" value="submitted">
                                        <input type="hidden" name="status_context" value="{{ $statusFilter }}">
                                        <button type="submit" class="btn btn-sm btn-outline-warning rounded-pill px-2">
                                            <i class="bi bi-arrow-counterclockwise me-1"></i>Set Submitted
                                        </button>
                                    </form>
                                @endif

                                @if($recordStatus !== 'rejected')
                                    <form method="POST" action="{{ route('landlord.payments.records.status', ['booking' => $booking->id, 'record' => $record->id]) }}" class="m-0">
                                        @csrf
                                        <input type="hidden" name="status" value="rejected">
                                        <input type="hidden" name="status_context" value="{{ $statusFilter }}">
                                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-2">
                                            <i class="bi bi-x-circle me-1"></i>Reject
                                        </button>
                                    </form>
                                @endif

                                @if(!empty($record->payment_proof_path))
                                    <a href="{{ asset('storage/' . $record->payment_proof_path) }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary rounded-pill px-2 py-1">
                                        <i class="bi bi-file-earmark-arrow-down me-1"></i>View Proof
                                    </a>
                                @endif

                                <form action="{{ route('landlord.payments.remind', $booking->id) }}" method="POST" class="m-0">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-secondary rounded-pill px-2" onclick="return confirm('Send a payment reminder to this tenant?')">
                                        <i class="bi bi-bell me-1"></i>Send Reminder
                                    </button>
                                </form>
                            </aside>
                        </div>

                        <div class="record-mobile-actions-wrap d-sm-none">
                            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill w-100" data-bs-toggle="modal" data-bs-target="#recordActionsModal{{ $record->id }}">
                                <i class="bi bi-three-dots me-1"></i>View Actions
                            </button>
                        </div>
                    </article>

                    <div class="modal fade record-actions-modal" id="recordActionsModal{{ $record->id }}" tabindex="-1" aria-labelledby="recordActionsModalLabel{{ $record->id }}" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="recordActionsModalLabel{{ $record->id }}">{{ optional($record->billing_for_date)->format('F Y') ?: 'Billing Record' }} Actions</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="record-actions-card modal-record-actions-card">
                                        <div class="record-actions-title">Actions</div>

                                        @if($recordStatus !== 'approved')
                                            <form method="POST" action="{{ route('landlord.payments.records.status', ['booking' => $booking->id, 'record' => $record->id]) }}" class="m-0">
                                                @csrf
                                                <input type="hidden" name="status" value="approved">
                                                <input type="hidden" name="status_context" value="{{ $statusFilter }}">
                                                <button type="submit" class="btn btn-sm btn-success rounded-pill px-2">
                                                    <i class="bi bi-check2 me-1"></i>Approve
                                                </button>
                                            </form>
                                        @endif

                                        @if($recordStatus !== 'submitted')
                                            <form method="POST" action="{{ route('landlord.payments.records.status', ['booking' => $booking->id, 'record' => $record->id]) }}" class="m-0">
                                                @csrf
                                                <input type="hidden" name="status" value="submitted">
                                                <input type="hidden" name="status_context" value="{{ $statusFilter }}">
                                                <button type="submit" class="btn btn-sm btn-outline-warning rounded-pill px-2">
                                                    <i class="bi bi-arrow-counterclockwise me-1"></i>Set Submitted
                                                </button>
                                            </form>
                                        @endif

                                        @if($recordStatus !== 'rejected')
                                            <form method="POST" action="{{ route('landlord.payments.records.status', ['booking' => $booking->id, 'record' => $record->id]) }}" class="m-0">
                                                @csrf
                                                <input type="hidden" name="status" value="rejected">
                                                <input type="hidden" name="status_context" value="{{ $statusFilter }}">
                                                <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-2">
                                                    <i class="bi bi-x-circle me-1"></i>Reject
                                                </button>
                                            </form>
                                        @endif

                                        @if(!empty($record->payment_proof_path))
                                            <a href="{{ asset('storage/' . $record->payment_proof_path) }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary rounded-pill px-2 py-1">
                                                <i class="bi bi-file-earmark-arrow-down me-1"></i>View Proof
                                            </a>
                                        @endif

                                        <form action="{{ route('landlord.payments.remind', $booking->id) }}" method="POST" class="m-0">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-secondary rounded-pill px-2" onclick="return confirm('Send a payment reminder to this tenant?')">
                                                <i class="bi bi-bell me-1"></i>Send Reminder
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-muted small">No monthly payment records yet. Click Create Payment to add one.</div>
                @endforelse
            </div>
        </section>
    </section>
</div>

<div class="modal fade" id="createPaymentRecordModal" tabindex="-1" aria-labelledby="createPaymentRecordModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createPaymentRecordModalLabel">Create Payment Record</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="POST" action="{{ route('landlord.payments.records.store', $booking->id) }}" class="row g-2">
                    @csrf
                    <input type="hidden" name="manual_booking_id" value="{{ $booking->id }}">
                    <input type="hidden" name="status_context" value="{{ $statusFilter }}">

                    <div class="col-sm-6">
                        <label class="form-label small fw-semibold mb-1">Billing Month</label>
                        <input type="month" name="billing_month" class="form-control form-control-sm" required value="{{ old('billing_month', $defaultBillingMonth) }}">
                    </div>
                    <div class="col-sm-6">
                        <label class="form-label small fw-semibold mb-1">Due Date</label>
                        <input type="date" name="due_date" class="form-control form-control-sm" value="{{ old('due_date', optional($dueDate)->toDateString()) }}">
                    </div>

                    <div class="col-sm-6">
                        <label class="form-label small fw-semibold mb-1">Amount Due (PHP)</label>
                        <input type="number" step="0.01" min="0.01" name="amount_due" class="form-control form-control-sm" required value="{{ old('amount_due', number_format((float) $monthlyRent, 2, '.', '')) }}">
                    </div>
                    <div class="col-sm-6">
                        <label class="form-label small fw-semibold mb-1">Status</label>
                        <select name="status" class="form-select form-select-sm" required>
                            <option value="submitted" @selected($manualStatusOld === 'submitted')>Submitted</option>
                            <option value="approved" @selected($manualStatusOld === 'approved')>Approved</option>
                            <option value="rejected" @selected($manualStatusOld === 'rejected')>Rejected</option>
                        </select>
                    </div>

                    <div class="col-sm-6">
                        <label class="form-label small fw-semibold mb-1">Method</label>
                        <select name="payment_method" class="form-select form-select-sm">
                            <option value="cash" @selected($manualMethodOld === 'cash')>Cash</option>
                            <option value="bank" @selected($manualMethodOld === 'bank')>Bank</option>
                            <option value="gcash" @selected($manualMethodOld === 'gcash')>GCash</option>
                        </select>
                    </div>
                    <div class="col-sm-6">
                        <label class="form-label small fw-semibold mb-1">Reference</label>
                        <input type="text" name="payment_reference" class="form-control form-control-sm" maxlength="120" value="{{ old('payment_reference') }}" placeholder="Optional reference no.">
                    </div>

                    <div class="col-12">
                        <label class="form-label small fw-semibold mb-1">Notes</label>
                        <textarea name="payment_notes" class="form-control form-control-sm" rows="3" maxlength="1000" placeholder="Optional notes for this record">{{ old('payment_notes') }}</textarea>
                    </div>

                    <div class="col-12 d-flex justify-content-end gap-2 mt-2">
                        <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success btn-sm rounded-pill px-3">
                            <i class="bi bi-plus-circle me-1"></i>Create Record
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .records-shell {
        background: linear-gradient(180deg, #ffffff 0%, #fbfdfc 100%);
        border: 1px solid rgba(2,8,20,.08);
        border-radius: 1.25rem;
        box-shadow: 0 10px 26px rgba(2,8,20,.06);
        padding: 1.25rem;
    }
    .records-section {
        min-width: 0;
    }
    .records-header {
        border: 1px solid rgba(2,8,20,.09);
        border-radius: 1rem;
        background: #fff;
        padding: .9rem;
        display: grid;
        gap: .55rem;
    }
    .records-header-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .7rem;
        flex-wrap: wrap;
    }
    .records-header-top .btn {
        flex: 0 0 auto;
    }
    .records-header-meta {
        display: flex;
        flex-wrap: wrap;
        gap: .45rem;
        align-items: center;
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

    .records-card {
        border: 1px solid rgba(2,8,20,.1);
        border-radius: .95rem;
        background: #fff;
        overflow: hidden;
    }
    .records-card-head {
        border-bottom: 1px solid rgba(2,8,20,.08);
        padding: .78rem .9rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: .6rem;
        background: rgba(248,250,252,.78);
    }
    .records-list {
        padding: .85rem;
        display: grid;
        gap: .65rem;
    }
    .record-item {
        border: 1px solid rgba(2,8,20,.11);
        border-radius: .82rem;
        background: #f8fafc;
        padding: .72rem .78rem;
        display: grid;
        gap: .45rem;
    }
    .record-item-initial {
        border-color: rgba(22,101,52,.26);
        background: linear-gradient(180deg, rgba(240,253,244,.74), rgba(255,255,255,.98));
    }
    .record-item-top {
        display: flex;
        justify-content: space-between;
        align-items: start;
        gap: .55rem;
    }
    .record-title {
        font-size: .98rem;
        font-weight: 700;
        color: #0f172a;
    }
    .record-subtitle {
        font-size: .76rem;
        color: #64748b;
    }
    .record-status {
        display: inline-flex;
        align-items: center;
        border-radius: 999px;
        padding: .2rem .62rem;
        font-size: .72rem;
        font-weight: 700;
        border: 1px solid transparent;
        white-space: nowrap;
    }
    .status-submitted { color: #7c2d12; background: #ffedd5; border-color: #fdba74; }
    .status-approved { color: #14532d; background: #dcfce7; border-color: #86efac; }
    .status-rejected { color: #7f1d1d; background: #fee2e2; border-color: #fca5a5; }
    .status-recorded { color: #334155; background: #f1f5f9; border-color: #cbd5e1; }

    .record-metrics {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: .48rem;
    }
    .record-item-body {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 200px;
        gap: .55rem;
        align-items: start;
    }
    .record-item-content {
        display: grid;
        gap: .45rem;
    }
    .record-metric {
        border: 1px solid rgba(2,8,20,.09);
        border-radius: .65rem;
        background: #fff;
        padding: .45rem .5rem;
    }
    .record-metric .k {
        font-size: .66rem;
        text-transform: uppercase;
        letter-spacing: .05em;
        color: rgba(2,8,20,.5);
        font-weight: 700;
        margin-bottom: .15rem;
    }
    .record-metric .v {
        font-size: .82rem;
        font-weight: 700;
        color: #0f172a;
    }
    .record-extras {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: .45rem;
    }
    .record-extra {
        font-size: .76rem;
        color: #334155;
    }
    .record-note {
        border: 1px solid rgba(2,8,20,.1);
        border-left: 3px solid rgba(22,101,52,.45);
        border-radius: .55rem;
        background: #fff;
        padding: .4rem .5rem;
        font-size: .76rem;
        color: #0f172a;
    }
    .record-actions-card {
        border: 1px solid rgba(37,99,235,.22);
        border-radius: .7rem;
        background: linear-gradient(180deg, rgba(239,246,255,.78), rgba(255,255,255,.98));
        padding: .45rem;
        display: grid;
        gap: .32rem;
    }
    .record-actions-title {
        font-size: .66rem;
        text-transform: uppercase;
        letter-spacing: .06em;
        color: rgba(30,64,175,.78);
        font-weight: 700;
        margin-bottom: .08rem;
    }
    .record-actions-card .btn {
        display: inline-flex;
        justify-content: center;
        align-items: center;
        width: 100%;
    }
    .record-mobile-actions-wrap {
        display: none;
    }
    .record-actions-modal .modal-content {
        border: 1px solid rgba(2,8,20,.12);
        border-radius: 1rem;
    }
    .record-actions-modal .modal-body {
        padding: .85rem;
    }
    .modal-record-actions-card {
        display: grid;
    }

    @media (max-width: 991.98px) {
        .records-shell {
            padding: .95rem;
        }
        .records-header-top {
            align-items: stretch;
        }
        .records-header-top .btn {
            width: 100%;
        }
        .record-item-body {
            grid-template-columns: 1fr;
        }
        .record-metrics {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 575.98px) {
        .records-shell {
            background: transparent;
            border: none;
            box-shadow: none;
            padding: 0;
        }
        .records-section {
            background: linear-gradient(180deg, #ffffff 0%, #fbfdfc 100%);
            border: 1px solid rgba(2,8,20,.09);
            border-radius: 1rem;
            box-shadow: 0 8px 20px rgba(2,8,20,.05);
            padding: .85rem;
            margin-bottom: .75rem !important;
        }
        .records-section:last-child {
            margin-bottom: 0 !important;
        }
        .records-list-section {
            background: transparent;
            border: none;
            border-radius: 0;
            box-shadow: none;
            padding: 0;
        }
        .records-header {
            border: none;
            border-radius: 0;
            background: transparent;
            padding: 0;
        }
        .records-card {
            border-radius: 1rem;
        }
        .records-card-head {
            padding: .72rem .78rem;
        }
        .records-list {
            padding: .72rem;
        }
        .student-email {
            display: none;
        }
        .record-metrics {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
        .record-metric-optional {
            display: none;
        }
        .record-item {
            padding: .65rem;
        }
        .record-item-top {
            gap: .4rem;
        }
        .record-item-body {
            gap: .45rem;
        }
        .record-item .record-actions-card {
            display: none;
        }
        .record-actions-modal .record-actions-card {
            display: grid;
        }
        .record-mobile-actions-wrap {
            display: block;
        }
        .records-header-top .btn {
            width: 100%;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        @if($errors->any() && (int) old('manual_booking_id') === (int) $booking->id)
            if (window.bootstrap) {
                const modalElement = document.getElementById('createPaymentRecordModal');
                if (modalElement) {
                    const modalInstance = bootstrap.Modal.getInstance(modalElement) || new bootstrap.Modal(modalElement);
                    modalInstance.show();
                }
            }
        @endif
    });
</script>
@endpush
