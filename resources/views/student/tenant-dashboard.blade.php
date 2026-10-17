@extends('layouts.student_dashboard')

@section('title', 'Your Current Stay')

@section('content')
<div class="container-fluid px-3 px-md-4 py-3">
    @php
        $leaveErrors = $errors->getBag('leave_request');
        $stayStatusLabel = match((string) ($paymentStatus ?? 'pending')) {
            'paid' => 'Payment Up to Date',
            'overdue' => 'Payment Overdue',
            default => 'Payment Pending',
        };
        $stayStatusBadge = match((string) ($paymentStatus ?? 'pending')) {
            'paid' => 'bg-success text-white',
            'overdue' => 'bg-danger text-white',
            default => 'bg-warning text-dark',
        };
        $houseRuleList = collect($houseRules ?? []);
        $inclusionList = collect($buildingInclusions ?? []);
    @endphp

    {{-- ── 1. Current Stay Hospitality Hero ── --}}
    <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 mb-4 bg-white">
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-start gap-4">
            <div>
                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-3 py-1.5 mb-2 fw-semibold small">
                    <i class="bi bi-house-check-fill me-1"></i>Active Resident Stay
                </span>
                <h1 class="h3 fw-bold text-dark mb-1">
                    {{ $tenantProperty->name ?? 'Boarding House' }}
                </h1>
                <div class="text-muted fs-6 mb-2">
                    Room {{ $tenantRoom->room_number ?? '—' }} &bull; {{ $tenantProperty->address ?? 'Victoria, Oriental Mindoro' }}
                </div>
                <div class="small text-muted d-flex align-items-center gap-2">
                    <i class="bi bi-calendar-event text-secondary"></i>
                    <span>Stay Duration: <strong>{{ optional($tenantBooking->check_in)->format('M d, Y') }}</strong> to <strong>{{ optional($tenantBooking->check_out)->format('M d, Y') }}</strong></span>
                </div>
            </div>

            <div class="d-flex flex-column align-items-lg-end gap-2 bg-light p-3 rounded-4 border">
                <span class="badge rounded-pill {{ $stayStatusBadge }} px-3 py-1.5 fw-semibold">{{ $stayStatusLabel }}</span>
                <div class="fw-bold text-dark fs-5 mt-1">
                    ₱{{ number_format((float) ($monthlyRent ?? 0), 2) }}
                    <span class="text-muted fs-6 fw-normal">/ month</span>
                </div>
                @if(!empty($paymentDueDate))
                    <div class="small text-muted">Next Due: {{ $paymentDueDate->format('M d, Y') }}</div>
                @endif
                <div class="small text-muted border-top pt-2 mt-1 w-100 text-lg-end">
                    <strong>Landlord:</strong> {{ $landlord->full_name ?? '—' }}
                    @if(!empty($landlord->contact_number))
                        &bull; {{ $landlord->contact_number }}
                    @endif
                </div>
            </div>
        </div>

        {{-- Primary Resident Actions --}}
        <div class="d-flex flex-wrap gap-2 pt-4 border-top mt-4">
            <a href="{{ route('student.rooms.show', $tenantRoom->id) }}" class="btn btn-outline-dark rounded-pill px-3.5 py-2 fw-semibold">
                <i class="bi bi-door-open me-1.5"></i>Room Details
            </a>
            @if(!empty($tenantOnboarding))
                <a href="{{ route('student.onboarding.show', $tenantOnboarding->id) }}" class="btn btn-outline-dark rounded-pill px-3.5 py-2 fw-semibold">
                    <i class="bi bi-file-earmark-text me-1.5"></i>Move-in & Contract
                </a>
            @endif
            <a href="{{ route('messages.index') }}" class="btn btn-success rounded-pill px-4 py-2 fw-semibold">
                <i class="bi bi-chat-dots me-1.5"></i>Message Landlord
            </a>
            <a href="{{ route('student.rooms.feedback_page', $tenantRoom->id) }}" class="btn btn-outline-success rounded-pill px-3.5 py-2 fw-semibold">
                <i class="bi bi-star me-1.5"></i>Review Room
            </a>
        </div>
    </div>

    {{-- ── 2. Roommates & House Guidelines ── --}}
    <div class="row g-4 mb-4">
        {{-- Roommates Card --}}
        <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm rounded-4 p-4 h-100 bg-white">
                <h2 class="h5 fw-bold text-dark mb-1">Roommates</h2>
                <p class="text-muted small mb-3">Other students currently residing in your room.</p>

                @if(($roommates ?? collect())->isEmpty())
                    <div class="alert alert-light border rounded-3 text-muted small mb-0">
                        <i class="bi bi-info-circle me-1"></i>No other roommates currently assigned to this room.
                    </div>
                @else
                    <div class="list-group list-group-flush">
                        @foreach($roommates as $rb)
                            @php $isMe = (int) ($rb->student_id ?? 0) === (int) Auth::id(); @endphp
                            <div class="list-group-item d-flex justify-content-between align-items-center px-0 py-2.5">
                                <div class="d-flex align-items-center gap-2.5">
                                    <div class="rounded-circle bg-success bg-opacity-10 text-success fw-bold d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; font-size: 0.85rem;">
                                        {{ strtoupper(substr($rb->student?->full_name ?? 'S', 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="fw-semibold text-dark">
                                            {{ $rb->student?->full_name ?? 'Student' }}
                                            @if($isMe)
                                                <span class="badge bg-success rounded-pill ms-1" style="font-size: 0.68rem;">You</span>
                                            @endif
                                        </div>
                                        <div class="small text-muted">{{ $rb->student?->program ?? 'Student' }}</div>
                                    </div>
                                </div>
                                <div class="small text-muted">Since {{ optional($rb->check_in)->format('M d, Y') }}</div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        {{-- House Rules & Inclusions Card --}}
        <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm rounded-4 p-4 h-100 bg-white">
                <h2 class="h5 fw-bold text-dark mb-1">House Rules & Inclusions</h2>
                <p class="text-muted small mb-3">Important boarding guidelines and facilities.</p>

                <div class="mb-3">
                    <div class="fw-bold small text-uppercase text-secondary mb-2">Rules & Guidelines</div>
                    @if($houseRuleList->isEmpty())
                        <div class="small text-muted">Standard MinSU student conduct applies.</div>
                    @else
                        <ul class="mb-0 ps-3 small text-muted">
                            @foreach($houseRuleList as $rule)
                                <li class="mb-1">{{ $rule }}</li>
                            @endforeach
                        </ul>
                    @endif
                </div>

                @if($inclusionList->isNotEmpty())
                    <div class="border-top pt-3">
                        <div class="fw-bold small text-uppercase text-secondary mb-2">Included Amenities</div>
                        <div class="d-flex flex-wrap gap-1.5">
                            @foreach($inclusionList as $item)
                                <span class="badge bg-light text-dark border rounded-pill px-2.5 py-1.5 fw-medium small">
                                    <i class="bi bi-check2 text-success me-1"></i>{{ ucwords(str_replace('_', ' ', $item)) }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- ── 3. Leave Requests Management ── --}}
    <div id="tenant-dashboard-leave" class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-3">
            <div>
                <h2 class="h5 fw-bold text-dark mb-1">Temporary Leave Requests</h2>
                <p class="text-muted small mb-0">Inform your landlord before temporarily vacating your room (weekends, holidays, emergencies).</p>
            </div>
        </div>

        <form method="POST" action="{{ route('student.leave_requests.store') }}" class="mb-4 bg-light p-3 p-md-4 rounded-4 border">
            @csrf
            <input type="hidden" name="panel" value="tenant-dashboard-leave">
            <div class="row g-3 align-items-end">
                <div class="col-12 col-md-4">
                    <label class="form-label small fw-semibold text-dark">Leave Date</label>
                    <input type="date" name="leave_date" value="{{ old('leave_date') }}" class="form-control rounded-3 @if($leaveErrors->has('leave_date')) is-invalid @endif" min="{{ now()->toDateString() }}" required>
                    @if($leaveErrors->has('leave_date'))
                        <div class="invalid-feedback">{{ $leaveErrors->first('leave_date') }}</div>
                    @endif
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label small fw-semibold text-dark">Reason (Optional)</label>
                    <input type="text" name="reason" value="{{ old('reason') }}" class="form-control rounded-3 @if($leaveErrors->has('reason')) is-invalid @endif" maxlength="1000" placeholder="e.g. Weekend visit to family, academic field work">
                    @if($leaveErrors->has('reason'))
                        <div class="invalid-feedback">{{ $leaveErrors->first('reason') }}</div>
                    @endif
                </div>
                <div class="col-12 col-md-2 d-grid">
                    <button class="btn btn-success rounded-pill fw-semibold py-2" type="submit">Submit Request</button>
                </div>
            </div>
        </form>

        @if(($tenantLeaveRequests ?? collect())->isEmpty())
            <div class="alert alert-light border rounded-3 text-muted small mb-0 text-center py-4">
                <i class="bi bi-clock-history d-block fs-3 mb-1 text-secondary"></i>
                No leave requests filed yet.
            </div>
        @else
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr class="small text-muted text-uppercase">
                            <th>Date</th>
                            <th>Status</th>
                            <th>Reason</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($tenantLeaveRequests as $lr)
                            <tr>
                                <td class="fw-semibold text-dark">{{ optional($lr->leave_date)->format('M d, Y') }}</td>
                                <td>
                                    @php
                                        $lrStatus = (string) ($lr->status ?? 'pending');
                                        $lrBadge = match($lrStatus) {
                                            'approved' => 'bg-success text-white',
                                            'rejected' => 'bg-danger text-white',
                                            'cancelled' => 'bg-secondary text-white',
                                            default => 'bg-warning text-dark',
                                        };
                                    @endphp
                                    <span class="badge rounded-pill {{ $lrBadge }} px-2.5 py-1">{{ ucfirst($lrStatus) }}</span>
                                </td>
                                <td class="text-muted small">{{ $lr->reason ?: '—' }}</td>
                                <td class="text-end">
                                    @if(($lr->status ?? '') === 'pending')
                                        <form method="POST" action="{{ route('student.leave_requests.cancel', $lr->id) }}" class="d-inline">
                                            @csrf
                                            <input type="hidden" name="panel" value="tenant-dashboard-leave">
                                            <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-3">Cancel</button>
                                        </form>
                                    @else
                                        <span class="text-muted small">No action</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection
