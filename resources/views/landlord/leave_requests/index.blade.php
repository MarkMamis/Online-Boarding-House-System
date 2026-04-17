@extends('layouts.landlord')

@section('title', 'Leave Requests')

@section('content')
    <div class="leave-shell">
        @php
            $items = collect($leaveRequests ?? collect());
            $pendingCount = $items->where('status', 'pending')->count();
            $approvedCount = $items->where('status', 'approved')->count();
            $rejectedCount = $items->where('status', 'rejected')->count();
            $cancelledCount = $items->where('status', 'cancelled')->count();
        @endphp

        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
            <div>
                <div class="text-uppercase small text-muted fw-semibold">Operations</div>
                <h1 class="h3 mb-1">Leave Requests</h1>
                <div class="text-muted small">Review tenant leave requests and respond with clear decisions.</div>
            </div>
            <a href="{{ route('landlord.tenants.index') }}" class="btn btn-outline-secondary rounded-pill px-3">View Tenants</a>
        </div>

        <div class="leave-summary mb-4">
            <div class="leave-summary-item">
                <div class="leave-summary-label">Pending</div>
                <div class="leave-summary-value text-warning-emphasis">{{ $pendingCount }}</div>
            </div>
            <div class="leave-summary-item">
                <div class="leave-summary-label">Approved</div>
                <div class="leave-summary-value text-success-emphasis">{{ $approvedCount }}</div>
            </div>
            <div class="leave-summary-item">
                <div class="leave-summary-label">Rejected</div>
                <div class="leave-summary-value text-danger-emphasis">{{ $rejectedCount }}</div>
            </div>
            <div class="leave-summary-item">
                <div class="leave-summary-label">Cancelled</div>
                <div class="leave-summary-value text-secondary-emphasis">{{ $cancelledCount }}</div>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success rounded-4">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger rounded-4">{{ session('error') }}</div>
        @endif

        <div class="leave-list-card">
            @forelse($items as $lr)
                @php
                    $status = strtolower((string) ($lr->status ?? ''));
                    $statusClass = match($status) {
                        'pending' => 'status-pending',
                        'approved' => 'status-approved',
                        'rejected' => 'status-rejected',
                        'cancelled' => 'status-cancelled',
                        default => 'status-default',
                    };
                    $statusIcon = match($status) {
                        'pending' => 'bi-hourglass-split',
                        'approved' => 'bi-check-circle',
                        'rejected' => 'bi-x-circle',
                        'cancelled' => 'bi-slash-circle',
                        default => 'bi-info-circle',
                    };
                    $statusLabel = $status !== '' ? ucfirst($status) : 'Unknown';
                    $statusNote = match($status) {
                        'pending' => 'Waiting for your decision',
                        'approved' => 'Request has been approved',
                        'rejected' => 'Request has been rejected',
                        'cancelled' => 'Request was cancelled',
                        default => 'Status unavailable',
                    };
                @endphp

                <article class="leave-item">
                    <div class="leave-main">
                        <div class="leave-title-row">
                            <div class="d-flex align-items-center gap-2 min-w-0">
                                <div class="student-avatar">
                                    {{ strtoupper(substr($lr->student?->full_name ?? 'S', 0, 1)) }}
                                </div>
                                <div class="min-w-0">
                                    <div class="student-name text-truncate">{{ $lr->student?->full_name ?? 'Student' }}</div>
                                    <div class="student-email text-truncate">{{ $lr->student?->email ?? 'No email available' }}</div>
                                </div>
                            </div>
                            <span class="updated-chip"><i class="bi bi-clock-history"></i>{{ optional($lr->updated_at)->diffForHumans() }}</span>
                        </div>

                        <div class="leave-meta-row">
                            <span class="meta-chip"><i class="bi bi-building"></i>{{ $lr->booking?->room?->property?->name ?? 'Property' }}</span>
                            <span class="meta-chip"><i class="bi bi-door-open"></i>{{ $lr->booking?->room?->room_number ?? '—' }}</span>
                            <span class="meta-chip"><i class="bi bi-calendar-event"></i>Leave {{ optional($lr->leave_date)->format('M d, Y') ?? '—' }}</span>
                        </div>

                        <div class="reason-box">
                            <div class="reason-label">Reason</div>
                            <div class="reason-text">{{ $lr->reason ?: 'No reason provided.' }}</div>
                        </div>

                        @if(!empty($lr->landlord_response))
                            <div class="response-box">
                                <div class="response-label">Your Response</div>
                                <div class="response-text">{{ $lr->landlord_response }}</div>
                            </div>
                        @endif
                    </div>

                    <div class="leave-side">
                        <div class="status-panel">
                            <span class="status-pill {{ $statusClass }}"><i class="bi {{ $statusIcon }}"></i>{{ $statusLabel }}</span>
                            <div class="status-note">{{ $statusNote }}</div>
                        </div>

                        <div class="leave-actions {{ $status !== 'pending' ? 'leave-actions-disabled' : '' }}">
                            @if($status === 'pending')
                                <form method="POST" action="{{ route('landlord.leave_requests.approve', $lr->id) }}" class="action-form">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success rounded-pill px-3"><i class="bi bi-check2 me-1"></i>Approve</button>
                                </form>
                                <form method="POST" action="{{ route('landlord.leave_requests.reject', $lr->id) }}" class="action-form">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-3"><i class="bi bi-x me-1"></i>Reject</button>
                                </form>
                            @else
                                <span class="action-muted"><i class="bi bi-lock me-1"></i>No actions available</span>
                            @endif
                        </div>
                    </div>
                </article>
            @empty
                <div class="empty-state">
                    <i class="bi bi-box-arrow-right fs-1 mb-2"></i>
                    <div class="empty-title">No Leave Requests Yet</div>
                    <div class="empty-copy">When students submit leave requests, they will appear here for review.</div>
                </div>
            @endforelse
        </div>
    </div>
@endsection

@push('styles')
<style>
    .leave-shell {
        background: linear-gradient(180deg, #ffffff 0%, #fbfdfc 100%);
        border: 1px solid rgba(2,8,20,.08);
        border-radius: 1.25rem;
        box-shadow: 0 10px 26px rgba(2,8,20,.06);
        padding: 1.25rem;
    }
    .leave-summary {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: .75rem;
    }
    .leave-summary-item {
        border: 1px solid rgba(20,83,45,.16);
        background: linear-gradient(180deg, rgba(167,243,208,.18), rgba(255,255,255,.85));
        border-radius: .9rem;
        padding: .7rem .8rem;
    }
    .leave-summary-label {
        font-size: .72rem;
        text-transform: uppercase;
        letter-spacing: .05em;
        color: rgba(2,8,20,.55);
        font-weight: 700;
        margin-bottom: .2rem;
    }
    .leave-summary-value {
        font-size: 1rem;
        font-weight: 700;
        color: #0f172a;
    }
    .leave-list-card {
        border: 1px solid rgba(2,8,20,.09);
        border-radius: 1rem;
        background: #fff;
        overflow: hidden;
    }
    .leave-item {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        gap: .9rem;
        align-items: start;
        padding: 1rem;
        border-bottom: 1px solid rgba(2,8,20,.08);
    }
    .leave-item:last-child {
        border-bottom: none;
    }
    .leave-title-row {
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
    .updated-chip {
        display: inline-flex;
        align-items: center;
        gap: .3rem;
        border: 1px solid rgba(2,8,20,.12);
        border-radius: 999px;
        background: #f8fafc;
        color: #334155;
        padding: .16rem .5rem;
        font-size: .72rem;
        font-weight: 600;
        white-space: nowrap;
    }
    .leave-meta-row {
        display: flex;
        flex-wrap: wrap;
        gap: .45rem;
        align-items: center;
        margin-bottom: .65rem;
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
    .reason-box,
    .response-box {
        border: 1px solid rgba(2,8,20,.08);
        border-radius: .75rem;
        padding: .6rem .7rem;
        background: #fcfefe;
        margin-bottom: .5rem;
    }
    .reason-label,
    .response-label {
        font-size: .69rem;
        text-transform: uppercase;
        letter-spacing: .05em;
        color: #64748b;
        font-weight: 700;
        margin-bottom: .15rem;
    }
    .reason-text,
    .response-text {
        font-size: .84rem;
        color: #0f172a;
    }
    .leave-side {
        display: grid;
        gap: .45rem;
        min-width: 230px;
        justify-items: end;
    }
    .status-panel {
        text-align: right;
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
    .status-rejected {
        color: #7f1d1d;
        background: #fee2e2;
        border-color: #fca5a5;
    }
    .status-cancelled {
        color: #334155;
        background: #e2e8f0;
        border-color: #cbd5e1;
    }
    .status-default {
        color: #1f2937;
        background: #f3f4f6;
        border-color: #d1d5db;
    }
    .leave-actions {
        display: inline-flex;
        gap: .45rem;
        align-items: center;
        flex-wrap: wrap;
        justify-content: flex-end;
    }
    .leave-actions .btn {
        min-width: 98px;
    }
    .leave-actions-disabled {
        border: 1px dashed rgba(100,116,139,.35);
        border-radius: .7rem;
        padding: .25rem .55rem;
        background: rgba(248,250,252,.9);
    }
    .action-muted {
        font-size: .78rem;
        color: #64748b;
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

    @media (max-width: 991.98px) {
        .leave-shell {
            padding: .95rem;
        }
        .leave-summary {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
        .leave-item {
            grid-template-columns: 1fr;
        }
        .leave-side {
            justify-items: start;
            min-width: 0;
        }
        .status-panel {
            text-align: left;
        }
        .leave-actions {
            justify-content: flex-start;
        }
    }

    @media (max-width: 575.98px) {
        .leave-summary {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

