@extends('layouts.student_dashboard')

@section('title', 'My Move-ins & Stays')

@push('styles')
<style>
    :root {
        --brand: #14532d;
        --brand-dark: #166534;
        --premium-emerald: #10b981;
        --premium-emerald-deep: #047857;
        --ink: #0f172a;
        --muted: #64748b;
        --line: #e2e8f0;
        --shell: #f8fafc;
    }

    .onb-index-shell {
        max-width: 960px;
        margin: 0 auto;
        padding: 0.5rem 0.25rem 2.5rem;
    }

    .onb-summary-strip {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        background: #ffffff;
        border: 1px solid var(--line);
        padding: 0.35rem 0.85rem;
        border-radius: 999px;
        font-size: 0.82rem;
        color: var(--muted);
    }

    .onb-summary-dot {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: var(--premium-emerald);
    }

    .onb-card {
        background: #ffffff;
        border: 1px solid var(--line);
        border-radius: 1.25rem;
        padding: 1.5rem;
        box-shadow: 0 4px 16px rgba(15, 23, 42, 0.04);
        margin-bottom: 1.5rem;
    }

    .onb-item {
        border: 1px solid var(--line);
        border-radius: 1rem;
        background: #ffffff;
        padding: 1.25rem;
        transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        gap: 1rem;
    }

    .onb-item:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.06);
        border-color: #cbd5e1;
    }

    .btn-brand {
        padding: 0.55rem 1.25rem;
        font-weight: 700;
        font-size: 0.88rem;
        border-radius: 999px;
        border: 1px solid rgba(16, 185, 129, 0.9);
        background: linear-gradient(125deg, var(--premium-emerald) 0%, var(--premium-emerald-deep) 100%);
        color: #ecfdf5;
        box-shadow: 0 6px 16px rgba(4, 120, 87, 0.22);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.4rem;
        cursor: pointer;
        text-decoration: none;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .btn-brand:hover {
        transform: translateY(-1px);
        background: linear-gradient(125deg, #0ea770 0%, #046246 100%);
        color: #ffffff;
        box-shadow: 0 10px 22px rgba(4, 120, 87, 0.32);
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
@endphp

<div class="onb-index-shell">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <div class="text-uppercase small text-muted fw-semibold" style="letter-spacing: 0.08em;">Student Operations</div>
            <h1 class="h3 mb-1 fw-bold text-dark">My Move-ins & Stays</h1>
            <div class="text-muted small">Track your boarding house onboarding process from requirement submission to active residency.</div>
        </div>

        @if($onbTotal > 0)
            <div class="onb-summary-strip">
                <span class="onb-summary-dot"></span>
                <span><strong>{{ $onbTotal }}</strong> {{ Str::plural('Record', $onbTotal) }}</span>
                @if($onbInProgress > 0)
                    <span class="text-muted">·</span>
                    <span class="text-warning-emphasis fw-bold">{{ $onbInProgress }} In Progress</span>
                @endif
                <span class="text-muted">·</span>
                <span class="text-success fw-bold">{{ $onbCompleted }} Completed</span>
            </div>
        @endif
    </div>

    @if(session('success'))
        <div class="alert alert-success rounded-4 d-flex align-items-center gap-2 mb-4">
            <i class="bi bi-check-circle-fill text-success fs-5"></i>
            <div>{{ session('success') }}</div>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger rounded-4 d-flex align-items-center gap-2 mb-4">
            <i class="bi bi-exclamation-triangle-fill text-danger fs-5"></i>
            <div>{{ session('error') }}</div>
        </div>
    @endif

    {{-- Move-in Records List --}}
    @if($dashOnboardings->isEmpty())
        <div class="onb-card text-center py-5">
            <div class="rounded-circle d-inline-flex align-items-center justify-content-center bg-light text-muted mb-3" style="width: 60px; height: 60px; font-size: 1.6rem;">
                <i class="bi bi-door-closed"></i>
            </div>
            <h5 class="fw-bold text-dark mb-1">No Move-in Records Yet</h5>
            <p class="text-muted small mx-auto mb-3" style="max-width: 440px;">
                When a landlord approves your boarding request, your move-in requirements and lease contract will appear here.
            </p>
            <a href="{{ route('student.rooms.index') }}" class="btn btn-outline-secondary rounded-pill px-4">
                <i class="bi bi-search me-1"></i>Find Boarding Houses
            </a>
        </div>
    @else
        <div class="row g-3 mb-4">
            @foreach($dashOnboardings as $obRow)
                @php
                    $status = (string) ($obRow->status ?? 'pending');
                    $isCompleted = ($status === 'completed');
                    $statusBadgeClass = match($status) {
                        'completed' => 'bg-success text-white',
                        'deposit_paid' => 'bg-warning-subtle text-warning-emphasis border border-warning-subtle',
                        'contract_signed' => 'bg-primary-subtle text-primary border border-primary-subtle',
                        'documents_uploaded' => 'bg-info-subtle text-info-emphasis border border-info-subtle',
                        default => 'bg-secondary-subtle text-secondary border border-secondary-subtle',
                    };
                    $statusLabel = match($status) {
                        'pending' => 'Requirements Pending',
                        'documents_uploaded' => 'Sign Contract',
                        'contract_signed' => 'Payment Due',
                        'deposit_paid' => 'Payment Under Review',
                        'completed' => 'Active Stay',
                        default => ucfirst(str_replace('_', ' ', $status)),
                    };
                    $room = $obRow->booking?->room;
                    $property = $room?->property;
                @endphp
                <div class="col-12 col-md-6">
                    <div class="onb-item h-100">
                        <div>
                            <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                <span class="badge rounded-pill {{ $statusBadgeClass }} px-2 py-1 small">
                                    {{ $statusLabel }}
                                </span>
                                @if($obRow->digital_id)
                                    <span class="badge bg-light text-dark border font-monospace">{{ $obRow->digital_id }}</span>
                                @endif
                            </div>

                            <h5 class="fw-bold text-dark mb-1">{{ $property?->name ?? 'Boarding House' }}</h5>
                            <div class="small text-muted mb-3">
                                <i class="bi bi-door-open me-1"></i>Room {{ $room?->room_number ?? '—' }}
                                @if($property?->address)
                                    · <span class="text-truncate">{{ $property->address }}</span>
                                @endif
                            </div>

                            <div class="p-2 rounded bg-light border small text-muted mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <span>Stay Duration:</span>
                                    <span class="fw-medium text-dark">{{ optional($obRow->booking?->check_in)->format('M d, Y') }} - {{ optional($obRow->booking?->check_out)->format('M d, Y') }}</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span>Monthly Rent:</span>
                                    <span class="fw-bold text-success">₱{{ number_format((float) ($room?->price ?? 0), 2) }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 pt-2 border-top">
                            <a href="{{ route('student.onboarding.show', $obRow->id) }}" class="{{ $isCompleted ? 'btn btn-sm btn-outline-secondary rounded-pill px-3' : 'btn-brand' }}">
                                <span>{{ $isCompleted ? 'View Stay Pass' : 'Continue Move-in' }}</span>
                                <i class="bi {{ $isCompleted ? 'bi-arrow-right' : 'bi-arrow-right-circle' }}"></i>
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Stay Management & Leave Requests (if active booking) --}}
    @if(!empty($hasCurrentApprovedBooking))
        @php
            $leaveErrors = $errors->getBag('leave_request');
            $leaveItems = ($currentBookingLeaveRequests ?? collect());
        @endphp

        <div class="onb-card">
            <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                <div>
                    <div class="text-uppercase small text-muted fw-semibold" style="letter-spacing: 0.06em;">Stay Management</div>
                    <h5 class="fw-bold text-dark mb-0">Request Scheduled Departure or Temporary Leave</h5>
                </div>
            </div>
            <div class="small text-muted mb-3">Submit a leave date and reason for your landlord's approval.</div>

            <form method="POST" action="{{ route('student.leave_requests.store') }}" class="mb-4">
                @csrf
                <div class="row g-2 align-items-end">
                    <div class="col-12 col-md-4">
                        <label class="form-label small fw-bold">Leave Date <span class="text-danger">*</span></label>
                        <input type="date" name="leave_date" value="{{ old('leave_date') }}" class="form-control form-control-sm @if($leaveErrors->has('leave_date')) is-invalid @endif" required min="{{ now()->toDateString() }}">
                        @if($leaveErrors->has('leave_date'))
                            <div class="invalid-feedback">{{ $leaveErrors->first('leave_date') }}</div>
                        @endif
                    </div>
                    <div class="col-12 col-md-5">
                        <label class="form-label small fw-bold">Reason (Optional)</label>
                        <input type="text" name="reason" value="{{ old('reason') }}" class="form-control form-control-sm @if($leaveErrors->has('reason')) is-invalid @endif" maxlength="1000" placeholder="e.g. End of semester, family emergency, break...">
                        @if($leaveErrors->has('reason'))
                            <div class="invalid-feedback">{{ $leaveErrors->first('reason') }}</div>
                        @endif
                    </div>
                    <div class="col-12 col-md-3">
                        <button type="submit" class="btn btn-sm btn-brand w-100">
                            <i class="bi bi-send me-1"></i>Submit Leave
                        </button>
                    </div>
                </div>
            </form>

            <div class="fw-semibold text-dark small mb-2"><i class="bi bi-clock-history me-1 text-primary"></i>My Leave History</div>
            @if($leaveItems->isEmpty())
                <div class="alert alert-light border small text-muted mb-0">No leave requests submitted for this stay.</div>
            @else
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead class="table-light small">
                            <tr>
                                <th>Leave Date</th>
                                <th>Status</th>
                                <th>Reason</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody class="small">
                            @foreach($leaveItems as $lr)
                                <tr>
                                    <td class="fw-semibold">{{ optional($lr->leave_date)->format('M d, Y') }}</td>
                                    <td>
                                        <span class="badge rounded-pill {{ ($lr->status ?? '') === 'approved' ? 'bg-success' : (($lr->status ?? '') === 'rejected' ? 'bg-danger' : 'bg-warning-subtle text-warning-emphasis border border-warning-subtle') }}">
                                            {{ ucfirst($lr->status) }}
                                        </span>
                                    </td>
                                    <td class="text-muted">{{ Str::limit((string)($lr->reason ?? '—'), 50) }}</td>
                                    <td class="text-end">
                                        @if(($lr->status ?? '') === 'pending')
                                            <form method="POST" action="{{ route('student.leave_requests.cancel', $lr->id) }}" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-secondary rounded-pill py-0 px-2">Cancel</button>
                                            </form>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    @endif
</div>
@endsection
