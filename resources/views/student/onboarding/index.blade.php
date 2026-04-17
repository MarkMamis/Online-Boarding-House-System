@extends('layouts.student_dashboard')

@section('title', 'Tenant Onboarding')

@push('styles')
<style>
    .onb-shell {
        background: linear-gradient(180deg, #ffffff 0%, #fbfdfc 100%);
        border: 1px solid rgba(2,8,20,.08);
        border-radius: 1.25rem;
        box-shadow: 0 10px 26px rgba(2,8,20,.06);
        padding: 1.25rem;
    }
    .onb-summary {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: .7rem;
    }
    .onb-summary-item {
        border: 1px solid rgba(20,83,45,.16);
        background: linear-gradient(180deg, rgba(167,243,208,.18), rgba(255,255,255,.85));
        border-radius: .85rem;
        padding: .65rem .75rem;
    }
    .onb-summary-label {
        font-size: .7rem;
        text-transform: uppercase;
        letter-spacing: .06em;
        color: rgba(2,8,20,.5);
        font-weight: 700;
        margin-bottom: .18rem;
    }
    .onb-summary-value {
        font-size: 1rem;
        font-weight: 700;
        color: #14532d;
    }
    .onb-block {
        border: 1px solid rgba(2,8,20,.08);
        border-radius: 1rem;
        background: #fff;
        box-shadow: 0 8px 18px rgba(2,8,20,.05);
        padding: 1rem;
        margin-top: .9rem;
    }
    .onb-kicker {
        font-size: .72rem;
        text-transform: uppercase;
        letter-spacing: .06em;
        font-weight: 700;
        color: rgba(2,8,20,.45);
    }
    .onb-step {
        border: 1px solid rgba(2,8,20,.08);
        border-radius: .8rem;
        padding: .75rem;
        background: #fff;
    }
    .onb-step .label {
        font-size: .72rem;
        color: rgba(2,8,20,.52);
        margin-bottom: .15rem;
    }
    .onb-step .value {
        font-weight: 700;
        color: #0f172a;
    }
    .onb-doc-row {
        border: 1px solid rgba(2,8,20,.08);
        border-radius: .85rem;
        padding: .75rem;
        background: #fff;
    }
    
    .onb-records-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 1rem;
    }
    
    .onb-record-card {
        border: 1px solid rgba(2,8,20,.1);
        border-radius: 1rem;
        background: linear-gradient(135deg, #ffffff 0%, #fafbfa 100%);
        padding: 1.5rem;
        transition: all .3s ease;
        display: flex;
        flex-direction: column;
        position: relative;
        overflow: hidden;
    }
    
    .onb-record-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: rgba(22,101,52,.2);
    }
    
    .onb-record-card.pending::before {
        background: linear-gradient(90deg, #f59e0b, #fbbf24);
    }
    
    .onb-record-card.completed::before {
        background: linear-gradient(90deg, #10b981, #34d399);
    }
    
    .onb-record-card:hover {
        box-shadow: 0 12px 24px rgba(22,101,52,.12);
        border-color: rgba(22,101,52,.2);
        transform: translateY(-2px);
    }
    
    .onb-record-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 1rem;
        margin-bottom: 1rem;
    }
    
    .onb-record-title {
        font-weight: 700;
        font-size: 1.1rem;
        color: #0f172a;
        margin-bottom: .35rem;
    }
    
    .onb-record-property {
        font-size: .85rem;
        color: #475569;
    }
    
    .onb-record-status {
        display: inline-block;
        padding: .35rem .75rem;
        border-radius: .5rem;
        font-size: .75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .04em;
    }
    
    .onb-record-status.pending {
        background: linear-gradient(135deg, rgba(245,158,11,.15), rgba(251,191,36,.15));
        color: #92400e;
        border: 1px solid rgba(245,158,11,.3);
    }
    
    .onb-record-status.completed {
        background: linear-gradient(135deg, rgba(16,185,129,.15), rgba(52,211,153,.15));
        color: #065f46;
        border: 1px solid rgba(16,185,129,.3);
    }
    
    .onb-record-meta {
        display: flex;
        flex-direction: column;
        gap: .6rem;
        margin: 1.2rem 0;
        padding: 1rem 0;
        border-top: 1px solid rgba(2,8,20,.06);
        border-bottom: 1px solid rgba(2,8,20,.06);
        flex-grow: 1;
    }
    
    .onb-record-meta-item {
        display: flex;
        align-items: center;
        gap: .6rem;
        font-size: .85rem;
        color: #475569;
    }
    
    .onb-record-meta-item i {
        color: #166534;
        font-size: 1rem;
    }
    
    .onb-record-meta-label {
        font-weight: 600;
        color: #0f172a;
        min-width: 80px;
    }
    
    .onb-record-cta {
        margin-top: 1rem;
        display: flex;
        gap: .6rem;
    }
    
    .onb-record-cta .btn {
        flex: 1;
        font-weight: 600;
        border-radius: .6rem;
    }
    
    @media (max-width: 991.98px) {
        .onb-summary {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
        .onb-shell {
            padding: .95rem;
        }
        .onb-records-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')
@php
    $dashOnboardings = ($allOnboardings ?? collect());
    $onbTotal = (int) $dashOnboardings->count();
    $onbCompleted = (int) $dashOnboardings->where('status', 'completed')->count();
    $onbInProgress = (int) $dashOnboardings->filter(function ($item) {
        return ($item->status ?? '') !== 'completed';
    })->count();
    $onbPendingLeaves = (int) (($currentBookingLeaveRequests ?? collect())->where('status', 'pending')->count());
@endphp

<div class="onb-shell mb-4">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <div class="text-uppercase small text-muted fw-semibold">Student Operations</div>
            <h1 class="h3 mb-1">Tenant Onboarding</h1>
            <div class="text-muted small">Manage required steps, documents, and leave requests for your current stay.</div>
        </div>
    </div>

    <div class="onb-summary mb-3">
        <div class="onb-summary-item">
            <div class="onb-summary-label">Records</div>
            <div class="onb-summary-value">{{ $onbTotal }}</div>
        </div>
        <div class="onb-summary-item">
            <div class="onb-summary-label">In Progress</div>
            <div class="onb-summary-value">{{ $onbInProgress }}</div>
        </div>
        <div class="onb-summary-item">
            <div class="onb-summary-label">Completed</div>
            <div class="onb-summary-value">{{ $onbCompleted }}</div>
        </div>
        <div class="onb-summary-item">
            <div class="onb-summary-label">Pending Leave</div>
            <div class="onb-summary-value">{{ $onbPendingLeaves }}</div>
        </div>
    </div>

    <div class="onb-block">
        <div class="d-flex align-items-center justify-content-between gap-2 mb-3">
            <div>
                <div class="onb-kicker">Onboarding Records</div>
                <div class="fw-semibold">All my onboardings</div>
            </div>
            <span class="badge text-bg-light border">{{ $onbTotal }} record{{ $onbTotal > 1 ? 's' : '' }}</span>
        </div>
        <div class="small text-muted mb-4">Complete your onboarding process step by step. Start with document uploads, then sign the contract, pay your deposit, and you're ready to move in!</div>

        @if($dashOnboardings->isEmpty())
            <div class="alert alert-secondary mb-0">
                <i class="bi bi-info-circle me-2"></i>
                No onboarding records yet. Once you have an approved booking, your onboarding process will appear here.
            </div>
        @else
            <div class="onb-records-grid">
                @foreach($dashOnboardings as $obRow)
                    @php
                        $status = (string) ($obRow->status ?? 'pending');
                        $statusLabel = $status === 'deposit_paid' ? 'Payment Under Review' : ucfirst(str_replace('_', ' ', $status));
                        $isCompleted = ($status === 'completed');
                        $isPending = ($status === 'pending');
                        $progressSteps = [
                            'pending' => 1,
                            'documents_uploaded' => 2,
                            'contract_signed' => 3,
                            'deposit_paid' => 4,
                            'completed' => 4,
                        ];
                        $currentStep = $progressSteps[$status] ?? 1;
                        $progressPct = (int) round(($currentStep / 4) * 100);
                    @endphp
                    <div class="onb-record-card {{ $status }}">
                        <div class="onb-record-header">
                            <div class="flex-grow-1">
                                <div class="onb-record-title">{{ $obRow->booking?->room?->property?->name ?? 'Property' }}</div>
                                <div class="onb-record-property">
                                    <i class="bi bi-door-closed me-1" style="color: #166534;"></i>
                                    {{ $obRow->booking?->room?->room_number ?? '—' }}
                                </div>
                            </div>
                            <span class="onb-record-status {{ $status }}">
                                @if($isCompleted)
                                    <i class="bi bi-check-circle me-1"></i>Complete
                                @elseif($isPending)
                                    <i class="bi bi-clock-history me-1"></i>Pending
                                @else
                                    <i class="bi bi-hourglass-split me-1"></i>{{ $statusLabel }}
                                @endif
                            </span>
                        </div>

                        <div class="onb-record-meta">
                            <div class="onb-record-meta-item">
                                <i class="bi bi-calendar-event"></i>
                                <span>
                                    <span class="onb-record-meta-label">Lease:</span>
                                    {{ optional($obRow->booking?->check_in)->format('M d') }} — {{ optional($obRow->booking?->check_out)->format('M d, Y') }}
                                </span>
                            </div>
                            <div class="onb-record-meta-item">
                                <i class="bi bi-currency-dollar"></i>
                                <span>
                                    <span class="onb-record-meta-label">Deposit:</span>
                                    ₱{{ number_format($obRow->deposit_amount ?? 0, 0) }}
                                </span>
                            </div>
                            <div class="onb-record-meta-item">
                                <i class="bi bi-bar-chart-steps"></i>
                                <span>
                                    <span class="onb-record-meta-label">Progress:</span>
                                    {{ $currentStep }}/4 steps
                                    <span class="ms-2" style="color: #166534; font-weight: 600;">{{ $progressPct }}%</span>
                                </span>
                            </div>
                        </div>

                        <div class="onb-record-cta">
                            <a href="{{ route('student.onboarding.show', $obRow->id) }}" class="btn btn-brand btn-sm">
                                @if($isCompleted)
                                    <i class="bi bi-eye me-1"></i>View Details
                                @elseif($isPending)
                                    <i class="bi bi-play-circle me-1"></i>Start Onboarding
                                @else
                                    <i class="bi bi-arrow-right-circle me-1"></i>Continue Process
                                @endif
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    @if(!empty($hasCurrentApprovedBooking))
        @php
            $leaveErrors = $errors->getBag('leave_request');
            $leaveItems = ($currentBookingLeaveRequests ?? collect());
        @endphp

        <div class="onb-block">
            <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                <div>
                    <div class="onb-kicker">Stay Management</div>
                    <div class="fw-semibold">Request for leave</div>
                </div>
                <div class="small text-muted">For your current stay</div>
            </div>
            <div class="small text-muted mb-3">Submit a leave date and reason. Your landlord will review it.</div>

            <form method="POST" action="{{ route('student.leave_requests.store') }}" class="mb-3">
                @csrf

                <div class="row g-2">
                    <div class="col-12 col-md-4">
                        <label class="form-label">Leave date</label>
                        <input type="date" name="leave_date" value="{{ old('leave_date') }}" class="form-control @if($leaveErrors->has('leave_date')) is-invalid @endif" required>
                        @if($leaveErrors->has('leave_date'))
                            <div class="invalid-feedback">{{ $leaveErrors->first('leave_date') }}</div>
                        @endif
                    </div>
                    <div class="col-12 col-md-8">
                        <label class="form-label">Reason (optional)</label>
                        <input type="text" name="reason" value="{{ old('reason') }}" class="form-control @if($leaveErrors->has('reason')) is-invalid @endif" maxlength="1000" placeholder="e.g., internship, family emergency, transfer...">
                        @if($leaveErrors->has('reason'))
                            <div class="invalid-feedback">{{ $leaveErrors->first('reason') }}</div>
                        @endif
                    </div>
                </div>

                <div class="mt-3">
                    <button type="submit" class="btn btn-brand btn-sm rounded-pill px-3">Submit request</button>
                </div>
            </form>

            <div class="fw-semibold mb-2">My leave requests</div>
            @if($leaveItems->isEmpty())
                <div class="alert alert-secondary mb-0">No leave requests yet.</div>
            @else
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Leave date</th>
                                <th>Status</th>
                                <th>Reason</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($leaveItems as $lr)
                                <tr>
                                    <td class="fw-semibold">{{ optional($lr->leave_date)->format('M d, Y') }}</td>
                                    <td><span class="badge rounded-pill {{ ($lr->status ?? '') === 'approved' ? 'text-bg-success' : (($lr->status ?? '') === 'rejected' ? 'text-bg-danger' : 'text-bg-light border') }}">{{ $lr->status }}</span></td>
                                    <td class="small text-muted">{{ \Illuminate\Support\Str::limit((string)($lr->reason ?? ''), 60) }}</td>
                                    <td class="text-end">
                                        @if(($lr->status ?? '') === 'pending')
                                            <form method="POST" action="{{ route('student.leave_requests.cancel', $lr->id) }}" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-secondary rounded-pill">Cancel</button>
                                            </form>
                                        @else
                                            <span class="text-muted small">—</span>
                                        @endif
                                    </td>
                                </tr>
                                @if(!empty($lr->landlord_response))
                                    <tr>
                                        <td colspan="4" class="small">
                                            <span class="text-muted">Landlord response:</span> {{ $lr->landlord_response }}
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    @endif

</div>
@endsection

