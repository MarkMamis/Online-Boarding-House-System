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
    @media (max-width: 991.98px) {
        .onb-summary {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
        .onb-shell {
            padding: .95rem;
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
        <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
            <div>
                <div class="onb-kicker">Onboarding Records</div>
                <div class="fw-semibold">All my onboardings</div>
            </div>
        </div>
        <div class="small text-muted mb-3">Your onboarding records for each approved booking.</div>

        @if($dashOnboardings->isEmpty())
            <div class="alert alert-secondary mb-0">No onboarding records yet.</div>
        @else
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Property</th>
                            <th>Room</th>
                            <th>Status</th>
                            <th>Lease</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($dashOnboardings as $obRow)
                            <tr>
                                <td class="fw-semibold">{{ $obRow->booking?->room?->property?->name ?? 'Property' }}</td>
                                <td>Room {{ $obRow->booking?->room?->room_number ?? '—' }}</td>
                                <td>
                                    <span class="badge rounded-pill {{ ($obRow->status ?? '') === 'completed' ? 'text-bg-success' : 'text-bg-light border' }}">{{ $obRow->status ?? '—' }}</span>
                                </td>
                                <td class="small text-muted">
                                    {{ optional($obRow->booking?->check_in)->format('M d, Y') }}
                                    –
                                    {{ optional($obRow->booking?->check_out)->format('M d, Y') }}
                                </td>
                                <td class="text-end">
                                    <a class="btn btn-sm btn-brand rounded-pill px-3" href="{{ route('student.onboarding.show', $obRow->id) }}">Open</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
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

    @if(!empty($latestOnboarding))
        @php
            $ob = $latestOnboarding;
            $obStatus = (string) ($ob->status ?? '');
            $docsDone = (!empty($ob->uploaded_documents) || in_array($obStatus, ['documents_uploaded', 'contract_signed', 'deposit_paid', 'completed'], true));
            $contractDone = (!empty($ob->contract_signed) || in_array($obStatus, ['contract_signed', 'deposit_paid', 'completed'], true));
            $depositDone = (!empty($ob->deposit_paid) || in_array($obStatus, ['deposit_paid', 'completed'], true));
            $completeDone = ($obStatus === 'completed');
            $stepsTotal = 4;
            $stepsDone = (int) ($docsDone ? 1 : 0) + (int) ($contractDone ? 1 : 0) + (int) ($depositDone ? 1 : 0) + (int) ($completeDone ? 1 : 0);
            $progressPct = $stepsTotal > 0 ? (int) round(($stepsDone / $stepsTotal) * 100) : 0;
            $requiredDocs = collect($ob->required_documents ?? []);
            $uploadedDocs = collect($ob->uploaded_documents ?? []);
        @endphp

        <div class="onb-block">
            <div class="d-flex flex-column flex-md-row justify-content-between gap-3">
                <div>
                    <div class="onb-kicker mb-1">Current Workflow</div>
                    <div class="fw-semibold mb-1">Onboarding for</div>
                    <div class="small text-muted">
                        {{ $latestOnboarding->booking?->room?->property?->name ?? 'Property' }}
                        • Room {{ $latestOnboarding->booking?->room?->room_number ?? '—' }}
                        • {{ optional($latestOnboarding->booking?->check_in)->format('M d, Y') }} to {{ optional($latestOnboarding->booking?->check_out)->format('M d, Y') }}
                    </div>
                </div>
                <div class="text-md-end">
                    <span class="badge rounded-pill {{ ($latestOnboarding->status ?? '') === 'completed' ? 'text-bg-success' : 'text-bg-light border' }}">Status: {{ $latestOnboarding->status ?? '—' }}</span>
                    <div class="small text-muted mt-1">{{ $progressPct }}% complete</div>
                </div>
            </div>

            <div class="progress mt-3" role="progressbar" aria-label="Onboarding progress" aria-valuenow="{{ $progressPct }}" aria-valuemin="0" aria-valuemax="100" style="height: 10px;">
                <div class="progress-bar bg-success" style="width: {{ $progressPct }}%"></div>
            </div>

            <div class="row g-2 mt-3">
                <div class="col-12 col-md-6 col-lg-3">
                    <div class="onb-step h-100">
                        <div class="label">Step 1</div>
                        <div class="value">Upload documents</div>
                        <div class="small mt-1">@if($docsDone)<span class="text-success">Done</span>@else Pending @endif</div>
                    </div>
                </div>
                <div class="col-12 col-md-6 col-lg-3">
                    <div class="onb-step h-100">
                        <div class="label">Step 2</div>
                        <div class="value">Sign contract</div>
                        <div class="small mt-1">@if($contractDone)<span class="text-success">Done</span>@else Pending @endif</div>
                    </div>
                </div>
                <div class="col-12 col-md-6 col-lg-3">
                    <div class="onb-step h-100">
                        <div class="label">Step 3</div>
                        <div class="value">Pay deposit</div>
                        <div class="small mt-1">@if($depositDone)<span class="text-success">Done</span>@else Pending @endif</div>
                    </div>
                </div>
                <div class="col-12 col-md-6 col-lg-3">
                    <div class="onb-step h-100">
                        <div class="label">Step 4</div>
                        <div class="value">Complete</div>
                        <div class="small mt-1">@if($completeDone)<span class="text-success">Done</span>@else Pending @endif</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="onb-block">
            <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                <div class="fw-semibold">Required documents</div>
                <a href="{{ route('student.onboarding.show', $latestOnboarding->id) }}" class="btn btn-brand btn-sm rounded-pill px-3">Continue</a>
            </div>
            <div class="small text-muted mb-3">Upload and complete your requirements to finalize your stay.</div>

            @if($requiredDocs->isEmpty())
                <div class="alert alert-secondary mb-0">No required documents listed.</div>
            @else
                <div class="row g-2">
                    @foreach($requiredDocs as $docKey)
                        @php
                            $label = ucfirst(str_replace('_', ' ', (string) $docKey));
                            $hasAnyUpload = $uploadedDocs->isNotEmpty();
                        @endphp
                        <div class="col-12 col-md-6">
                            <div class="onb-doc-row d-flex align-items-center justify-content-between">
                                <div>
                                    <div class="fw-semibold">{{ $label }}</div>
                                    <div class="small text-muted">@if($hasAnyUpload) Uploaded @else Not uploaded @endif</div>
                                </div>
                                @if($hasAnyUpload)
                                    <span class="badge text-bg-success">OK</span>
                                @else
                                    <span class="badge text-bg-warning">Missing</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    @else
        @if($dashOnboardings->isNotEmpty())
            <div class="onb-block">
                <div class="alert alert-secondary mb-0">No active onboarding workflow right now.</div>
            </div>
        @endif
    @endif
</div>
@endsection
