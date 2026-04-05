@extends('layouts.student_dashboard')

@section('title', 'Tenant Dashboard')

@section('content')
<div class="container-fluid px-0">
    @php
        $leaveErrors = $errors->getBag('leave_request');
        $stayStatusLabel = match((string) ($paymentStatus ?? 'pending')) {
            'paid' => 'Payment up to date',
            'overdue' => 'Payment overdue',
            default => 'Payment pending',
        };
        $stayStatusBadge = match((string) ($paymentStatus ?? 'pending')) {
            'paid' => 'text-bg-success',
            'overdue' => 'text-bg-danger',
            default => 'text-bg-warning',
        };
        $houseRuleList = collect($houseRules ?? []);
        $inclusionList = collect($buildingInclusions ?? []);
        $feedbackItems = collect($recentFeedbackItems ?? []);
        $reportItems = collect($recentUserReports ?? []);
        $feedbackAvgLabel = !is_null($feedbackAverageRating ?? null)
            ? number_format((float) $feedbackAverageRating, 1) . '/5'
            : '—';
    @endphp

    <div class="glass-card rounded-4 p-4 p-md-5 mb-4" style="background: linear-gradient(135deg, rgba(22,101,52,.10), rgba(240,253,244,.9)); border-color: rgba(22,101,52,.18);">
        <div class="d-flex flex-column flex-lg-row justify-content-between gap-3">
            <div>
                <div class="small text-uppercase fw-semibold" style="letter-spacing:.08em;color:#14532d;">Tenant Hub</div>
                <h3 class="fw-bold mb-2" style="color:#14532d;">Tenant Dashboard</h3>
                <div class="text-muted">
                    {{ $tenantProperty->name ?? 'Property' }} • Room {{ $tenantRoom->room_number ?? '—' }}
                </div>
                <div class="small text-muted mt-1">
                    {{ optional($tenantBooking->check_in)->format('M d, Y') }} to {{ optional($tenantBooking->check_out)->format('M d, Y') }}
                </div>
            </div>
            <div class="d-flex flex-column align-items-lg-end gap-2">
                <span class="badge rounded-pill {{ $stayStatusBadge }}">{{ $stayStatusLabel }}</span>
                <div class="small text-muted">Landlord: {{ $landlord->full_name ?? '—' }}</div>
                @if(!empty($landlord->contact_number))
                    <div class="small text-muted">Contact: {{ $landlord->contact_number }}</div>
                @endif
            </div>
        </div>

        <div class="row g-3 mt-2">
            <div class="col-12 col-md-6 col-xl-3">
                <div class="metric-tile h-100">
                    <div class="small text-muted">Monthly rent</div>
                    <div class="h5 fw-bold mb-0">PHP {{ number_format((float) ($monthlyRent ?? 0), 2) }}</div>
                </div>
            </div>
            <div class="col-12 col-md-6 col-xl-3">
                <div class="metric-tile h-100">
                    <div class="small text-muted">Next due date</div>
                    <div class="h5 fw-bold mb-0">{{ !empty($paymentDueDate) ? $paymentDueDate->format('M d, Y') : '—' }}</div>
                </div>
            </div>
            <div class="col-12 col-md-6 col-xl-3">
                <div class="metric-tile h-100">
                    <div class="small text-muted">Room occupancy</div>
                    <div class="h5 fw-bold mb-0">{{ (int) ($roommatesCount ?? 0) }}@if(!empty($roomCapacity)) / {{ (int) $roomCapacity }}@endif</div>
                </div>
            </div>
            <div class="col-12 col-md-6 col-xl-3">
                <div class="metric-tile h-100">
                    <div class="small text-muted">Unread updates</div>
                    <div class="h5 fw-bold mb-0">{{ (int) ($unreadMessagesCount ?? 0) + (int) ($unreadResponsesCount ?? 0) }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-12 col-xl-8">
            <div class="glass-card rounded-4 p-3 p-md-4 h-100">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h5 class="fw-semibold mb-0">Your Boarding House Location</h5>
                    <a href="{{ $mapOpenUrl }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary rounded-pill">Open in Google Maps</a>
                </div>
                <div class="ratio ratio-16x9 rounded-4 overflow-hidden border">
                    <iframe src="{{ $mapEmbedUrl }}" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen title="Property map"></iframe>
                </div>
                <div class="small text-muted mt-2">{{ $tenantProperty->address ?? 'No address available.' }}</div>
            </div>
        </div>
        <div class="col-12 col-xl-4">
            <div class="glass-card rounded-4 p-3 p-md-4 h-100">
                <h5 class="fw-semibold mb-3">Tenant Quick Actions</h5>
                <div class="d-grid gap-2">
                    <a href="{{ route('student.rooms.show', $tenantRoom->id) }}" class="btn btn-outline-brand rounded-pill text-start"><i class="bi bi-house-door me-2"></i>Open My Room</a>
                    <a href="{{ route('messages.index') }}" class="btn btn-outline-brand rounded-pill text-start"><i class="bi bi-chat-dots me-2"></i>Messages</a>
                    <a href="{{ route('student.reports.index') }}" class="btn btn-outline-brand rounded-pill text-start"><i class="bi bi-question-circle me-2"></i>Help Center</a>
                    <a href="{{ route('student.rooms.feedback_page', $tenantRoom->id) }}" class="btn btn-outline-brand rounded-pill text-start"><i class="bi bi-star me-2"></i>Room Feedback</a>
                    <a href="{{ route('student.onboarding.show', $tenantOnboarding->id) }}" class="btn btn-brand rounded-pill text-start"><i class="bi bi-clipboard-check me-2"></i>View Onboarding Record</a>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-12 col-lg-6">
            <div class="glass-card rounded-4 p-3 p-md-4 h-100">
                <h5 class="fw-semibold mb-3">Roommates</h5>
                <div class="small text-muted mb-3">Students with active approved stays in your room.</div>
                @if(($roommates ?? collect())->isEmpty())
                    <div class="alert alert-secondary mb-0">No roommates found yet.</div>
                @else
                    <div class="list-group list-group-flush">
                        @foreach($roommates as $rb)
                            @php $isMe = (int) ($rb->student_id ?? 0) === (int) Auth::id(); @endphp
                            <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <div>
                                    <div class="fw-semibold">
                                        {{ $rb->student?->full_name ?? 'Student' }}
                                        @if($isMe)
                                            <span class="badge rounded-pill text-bg-success ms-1">You</span>
                                        @endif
                                    </div>
                                    <div class="small text-muted">{{ $rb->student?->course ?? 'Course not set' }}</div>
                                </div>
                                <div class="small text-muted">Since {{ optional($rb->check_in)->format('M d, Y') }}</div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <div class="col-12 col-lg-6">
            <div class="glass-card rounded-4 p-3 p-md-4 h-100">
                <h5 class="fw-semibold mb-3">House Rules And Inclusions</h5>

                <div class="mb-3">
                    <div class="fw-semibold small text-uppercase text-muted mb-2">House Rules</div>
                    @if($houseRuleList->isEmpty())
                        <div class="small text-muted">No rules listed yet by landlord.</div>
                    @else
                        <ul class="mb-0 ps-3">
                            @foreach($houseRuleList as $rule)
                                <li>{{ $rule }}</li>
                            @endforeach
                        </ul>
                    @endif
                </div>

                <div>
                    <div class="fw-semibold small text-uppercase text-muted mb-2">Building Inclusions</div>
                    @if($inclusionList->isEmpty())
                        <div class="small text-muted">No inclusions listed yet.</div>
                    @else
                        <ul class="mb-0 ps-3">
                            @foreach($inclusionList as $item)
                                <li>{{ $item }}</li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-12 col-xl-6">
            <div class="glass-card rounded-4 p-3 p-md-4 h-100">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h5 class="fw-semibold mb-0">Feedback Overview</h5>
                    <a href="{{ route('student.rooms.feedback_page', $tenantRoom->id) }}" class="btn btn-sm btn-outline-secondary rounded-pill">Open feedback</a>
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-12 col-md-4">
                        <div class="border rounded-4 p-3 h-100">
                            <div class="small text-muted">Your submissions</div>
                            <div class="fw-bold fs-5">{{ (int) ($feedbackTotalCount ?? 0) }}</div>
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="border rounded-4 p-3 h-100">
                            <div class="small text-muted">Average rating</div>
                            <div class="fw-bold fs-5">{{ $feedbackAvgLabel }}</div>
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="border rounded-4 p-3 h-100">
                            <div class="small text-muted">Positive sentiment</div>
                            <div class="fw-bold fs-5">{{ (int) ($feedbackPositiveCount ?? 0) }}</div>
                        </div>
                    </div>
                </div>

                @if($feedbackItems->isEmpty())
                    <div class="alert alert-secondary mb-0">No feedback submitted yet for your current room.</div>
                @else
                    <div class="list-group list-group-flush">
                        @foreach($feedbackItems as $fb)
                            <div class="list-group-item px-0">
                                <div class="d-flex justify-content-between align-items-start gap-2">
                                    <div class="min-w-0">
                                        <div class="fw-semibold text-truncate">{{ $fb->comment ?: 'No comment provided.' }}</div>
                                        <div class="small text-muted">{{ optional($fb->created_at)->format('M d, Y h:i A') }}</div>
                                    </div>
                                    <span class="badge rounded-pill text-bg-warning">{{ number_format((float) ($fb->rating ?? 0), 1) }}/5</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <div class="col-12 col-xl-6">
            <div class="glass-card rounded-4 p-3 p-md-4 h-100">
                <div class="d-flex align-items-center justify-content-between mb-1">
                    <h5 class="fw-semibold mb-0">Reports Overview</h5>
                    <a href="{{ route('student.reports.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill">Open Help Center</a>
                </div>
                <div class="small text-muted mb-3">Only reports submitted by your account are shown here.</div>

                <div class="row g-2 mb-3">
                    <div class="col-6 col-md-3">
                        <div class="border rounded-4 p-3 h-100">
                            <div class="small text-muted">Total</div>
                            <div class="fw-bold fs-5">{{ (int) ($reportTotalCount ?? 0) }}</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="border rounded-4 p-3 h-100">
                            <div class="small text-muted">Pending</div>
                            <div class="fw-bold fs-5">{{ (int) ($reportPendingCount ?? 0) }}</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="border rounded-4 p-3 h-100">
                            <div class="small text-muted">In progress</div>
                            <div class="fw-bold fs-5">{{ (int) ($reportInProgressCount ?? 0) }}</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="border rounded-4 p-3 h-100">
                            <div class="small text-muted">Resolved</div>
                            <div class="fw-bold fs-5">{{ (int) ($reportResolvedCount ?? 0) }}</div>
                        </div>
                    </div>
                </div>

                @if($reportItems->isEmpty())
                    <div class="alert alert-secondary mb-0">No reports submitted yet.</div>
                @else
                    <div class="list-group list-group-flush">
                        @foreach($reportItems as $rp)
                            @php
                                $rpStatus = (string) ($rp->status ?? 'pending');
                                $rpStatusBadge = $rpStatus === 'resolved'
                                    ? 'text-bg-success'
                                    : ($rpStatus === 'in_progress' ? 'text-bg-warning' : 'text-bg-secondary');
                                $rpPriority = (string) ($rp->priority ?? 'medium');
                                $rpPriorityBadge = $rpPriority === 'high'
                                    ? 'text-bg-danger'
                                    : ($rpPriority === 'medium' ? 'text-bg-warning' : 'text-bg-secondary');
                            @endphp
                            <div class="list-group-item px-0">
                                <div class="d-flex justify-content-between align-items-start gap-2">
                                    <div class="min-w-0">
                                        <div class="fw-semibold text-truncate">{{ $rp->title ?? 'Report' }}</div>
                                        <div class="small text-muted">{{ optional($rp->created_at)->format('M d, Y h:i A') }}</div>
                                        @if(!empty($rp->admin_response) && empty($rp->response_read))
                                            <div class="small text-danger">New admin response</div>
                                        @endif
                                    </div>
                                    <div class="d-flex align-items-center gap-1 flex-wrap justify-content-end">
                                        <span class="badge rounded-pill {{ $rpPriorityBadge }}">{{ ucfirst($rpPriority) }}</span>
                                        <span class="badge rounded-pill {{ $rpStatusBadge }}">{{ ucwords(str_replace('_', ' ', $rpStatus)) }}</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                @if((int) ($reportUnreadResponseCount ?? 0) > 0)
                    <div class="small text-danger mt-2">You have {{ (int) $reportUnreadResponseCount }} unread admin response(s).</div>
                @endif
            </div>
        </div>
    </div>

    <div id="tenant-dashboard-leave" class="glass-card rounded-4 p-3 p-md-4">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-3">
            <h5 class="fw-semibold mb-0">Leave Requests</h5>
            <div class="small text-muted">Request temporary leave from your landlord.</div>
        </div>

        <form method="POST" action="{{ route('student.leave_requests.store') }}" class="mb-3">
            @csrf
            <input type="hidden" name="panel" value="tenant-dashboard-leave">
            <div class="row g-2 align-items-end">
                <div class="col-12 col-md-4">
                    <label class="form-label small text-muted">Leave date</label>
                    <input type="date" name="leave_date" value="{{ old('leave_date') }}" class="form-control @if($leaveErrors->has('leave_date')) is-invalid @endif" min="{{ now()->toDateString() }}" required>
                    @if($leaveErrors->has('leave_date'))
                        <div class="invalid-feedback">{{ $leaveErrors->first('leave_date') }}</div>
                    @endif
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label small text-muted">Reason (optional)</label>
                    <input type="text" name="reason" value="{{ old('reason') }}" class="form-control @if($leaveErrors->has('reason')) is-invalid @endif" maxlength="1000" placeholder="Brief reason for leave request">
                    @if($leaveErrors->has('reason'))
                        <div class="invalid-feedback">{{ $leaveErrors->first('reason') }}</div>
                    @endif
                </div>
                <div class="col-12 col-md-2 d-grid">
                    <button class="btn btn-brand rounded-pill" type="submit">Submit</button>
                </div>
            </div>
        </form>

        @if(($tenantLeaveRequests ?? collect())->isEmpty())
            <div class="alert alert-secondary mb-0">No leave requests submitted yet.</div>
        @else
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Reason</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($tenantLeaveRequests as $lr)
                            <tr>
                                <td>{{ optional($lr->leave_date)->format('M d, Y') }}</td>
                                <td>
                                    @php
                                        $lrStatus = (string) ($lr->status ?? 'pending');
                                        $lrBadge = $lrStatus === 'approved' ? 'text-bg-success' : ($lrStatus === 'rejected' ? 'text-bg-danger' : ($lrStatus === 'cancelled' ? 'text-bg-secondary' : 'text-bg-warning'));
                                    @endphp
                                    <span class="badge {{ $lrBadge }}">{{ ucfirst($lrStatus) }}</span>
                                </td>
                                <td class="text-muted">{{ $lr->reason ?: '—' }}</td>
                                <td class="text-end">
                                    @if(($lr->status ?? '') === 'pending')
                                        <form method="POST" action="{{ route('student.leave_requests.cancel', $lr->id) }}" class="d-inline">
                                            @csrf
                                            <input type="hidden" name="panel" value="tenant-dashboard-leave">
                                            <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill">Cancel</button>
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
