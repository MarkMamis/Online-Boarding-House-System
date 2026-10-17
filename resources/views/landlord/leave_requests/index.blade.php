@extends('layouts.landlord')

@section('title', 'Departure & Leave Notices')

@section('content')
<div class="landlord-page-shell">
    @php
        $items = collect($leaveRequests ?? collect());
        $pendingCount = $items->where('status', 'pending')->count();
        $approvedCount = $items->where('status', 'approved')->count();
        $rejectedCount = $items->where('status', 'rejected')->count();
        $cancelledCount = $items->where('status', 'cancelled')->count();
    @endphp

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
        <div>
            <div class="landlord-header-eyebrow">Operations</div>
            <h1 class="landlord-header-title mb-1">Departure &amp; Leave Notices</h1>
            <div class="landlord-header-subtitle">Review tenant early departure notices and schedule move-out dates.</div>
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            @if($items->isNotEmpty())
                <span class="landlord-summary-strip">
                    <span class="landlord-summary-dot"></span>
                    <span><strong>{{ $items->count() }}</strong> {{ Str::plural('Notice', $items->count()) }}</span>
                    @if($pendingCount > 0)
                        <span class="text-muted">·</span>
                        <span class="text-warning-emphasis fw-bold">{{ $pendingCount }} Action Required</span>
                    @endif
                    <span class="text-muted">·</span>
                    <span class="text-success fw-bold">{{ $approvedCount }}</span> Approved
                </span>
            @endif
        </div>
    </div>

    <!-- Collapsible Policy Helper -->
    <div class="mb-3">
        <button class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1" type="button" data-bs-toggle="collapse" data-bs-target="#leavePolicyCollapse" aria-expanded="false" aria-controls="leavePolicyCollapse">
            <i class="bi bi-info-circle me-1"></i> How scheduled departures work
        </button>
        <div class="collapse mt-2" id="leavePolicyCollapse">
            <div class="card card-body bg-light border-0 rounded-4 p-3 small text-secondary">
                <strong>Scheduled Departures Policy:</strong> Approving a future departure date adjusts the student's booking end-date so they remain an active resident until that date. Their bed slot is automatically made available for new student bookings once the departure date passes. Immediate departures free the room slot right away.
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success rounded-4 mb-3">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger rounded-4 mb-3">{{ session('error') }}</div>
    @endif

    @if($items->isNotEmpty())
        <div class="landlord-filter-bar mb-3">
            <div class="landlord-filter-search">
                <i class="bi bi-search"></i>
                <input id="leaveSearchInput" type="text" class="form-control" placeholder="Search student name, room, property, or reason...">
            </div>
            <div class="landlord-filter-select">
                <select id="leaveStatusFilter" class="form-select">
                    <option value="all">All Notices</option>
                    <option value="pending" @selected($pendingCount > 0)>Pending ({{ $pendingCount }})</option>
                    <option value="approved">Approved ({{ $approvedCount }})</option>
                    <option value="rejected">Rejected ({{ $rejectedCount }})</option>
                    <option value="cancelled">Cancelled ({{ $cancelledCount }})</option>
                </select>
            </div>
        </div>
    @endif

    <div class="landlord-card">
        @forelse($items as $lr)
            @php
                $status = strtolower((string) ($lr->status ?? ''));
                $statusBadgeClass = match($status) {
                    'pending' => 'bg-warning-subtle text-warning-emphasis border border-warning-subtle',
                    'approved' => 'bg-success-subtle text-success border border-success-subtle',
                    'rejected' => 'bg-danger-subtle text-danger border border-danger-subtle',
                    'cancelled' => 'bg-secondary-subtle text-secondary border border-secondary-subtle',
                    default => 'bg-light text-dark',
                };
                $statusLabel = $status !== '' ? ucfirst($status) : 'Unknown';
                $statusNote = match($status) {
                    'pending' => 'Waiting for your decision',
                    'approved' => 'Request approved · Move-out scheduled',
                    'rejected' => 'Departure notice was declined',
                    'cancelled' => 'Cancelled by student',
                    default => 'Status unavailable',
                };

                $searchBlob = strtolower(trim(implode(' ', [
                    $lr->student?->full_name ?? '',
                    $lr->booking?->room?->property?->name ?? '',
                    $lr->booking?->room?->room_number ?? '',
                    $lr->reason ?? '',
                ])));
            @endphp

            <article class="leave-item" data-search="{{ $searchBlob }}" data-status="{{ $status }}">
                <div class="leave-main">
                    <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                        <div class="student-avatar-sm">
                            {{ strtoupper(substr($lr->student?->full_name ?? 'S', 0, 1)) }}
                        </div>
                        <span class="fw-bold text-dark fs-6">{{ $lr->student?->full_name ?? 'Student' }}</span>
                        <span class="text-muted small">({{ $lr->student?->email ?? 'No email' }})</span>
                        <span class="badge rounded-pill {{ $statusBadgeClass }} small ms-auto d-md-none">
                            {{ $statusLabel }}
                        </span>
                    </div>

                    <div class="leave-meta-row mb-2">
                        <span class="meta-chip"><i class="bi bi-building"></i>{{ $lr->booking?->room?->property?->name ?? 'Property' }}</span>
                        <span class="meta-chip"><i class="bi bi-door-open"></i>Room {{ $lr->booking?->room?->room_number ?? '—' }}</span>
                        <span class="meta-chip"><i class="bi bi-calendar-event"></i>Departure: {{ optional($lr->leave_date)->format('M d, Y') ?? '—' }}</span>
                        <span class="text-muted small ms-1"><i class="bi bi-clock-history me-1"></i>{{ optional($lr->updated_at)->diffForHumans() }}</span>
                    </div>

                    <div class="reason-box mb-2">
                        <div class="reason-label">Reason for Departure</div>
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
                    <div class="status-panel d-none d-md-block">
                        <span class="badge rounded-pill {{ $statusBadgeClass }} px-2 py-1">
                            {{ $statusLabel }}
                        </span>
                        <div class="status-note">{{ $statusNote }}</div>
                    </div>

                    <div class="leave-actions">
                        @if($status === 'pending')
                            <form method="POST" action="{{ route('landlord.leave_requests.approve', $lr->id) }}" class="action-form">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-success rounded-pill px-3" onclick="return confirm('Approve this departure notice? If the departure date is in the future, the tenant remains active until that date.')">
                                    <i class="bi bi-check2 me-1"></i>Approve
                                </button>
                            </form>
                            <form method="POST" action="{{ route('landlord.leave_requests.reject', $lr->id) }}" class="action-form">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-3" onclick="return confirm('Reject this departure notice? The student will remain on their current check-out schedule.')">
                                    <i class="bi bi-x me-1"></i>Decline
                                </button>
                            </form>
                        @else
                            <span class="text-muted small"><i class="bi bi-lock me-1"></i>Completed</span>
                        @endif
                    </div>
                </div>
            </article>
        @empty
            <div class="landlord-empty-state border-0">
                <div class="landlord-empty-icon">
                    <i class="bi bi-box-arrow-right"></i>
                </div>
                <div class="landlord-empty-title">No Leave Requests Yet</div>
                <div class="landlord-empty-text">When students submit departure or leave notices, they will appear here for your review and scheduling.</div>
            </div>
        @endforelse

        @if($items->isNotEmpty())
            <div id="leaveNoMatch" class="landlord-empty-state border-0 d-none">
                <div class="landlord-empty-icon">
                    <i class="bi bi-search"></i>
                </div>
                <div class="landlord-empty-title">No matching departure notices</div>
                <div class="landlord-empty-text">Try adjusting your search query or selected status filter.</div>
            </div>
        @endif
    </div>
</div>
@endsection

@push('styles')
<style>
    .leave-item {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        gap: 1rem;
        align-items: start;
        padding: 1.1rem 1.25rem;
        border-bottom: 1px solid rgba(2,8,20,.08);
        transition: background .2s ease;
    }
    .leave-item:hover {
        background: #fbfcfd;
    }
    .leave-item:last-child {
        border-bottom: none;
    }
    .student-avatar-sm {
        width: 26px;
        height: 26px;
        border-radius: 50%;
        background: rgba(20,83,45,.15);
        color: #14532d;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: .72rem;
        font-weight: 700;
        flex-shrink: 0;
    }
    .leave-meta-row {
        display: flex;
        flex-wrap: wrap;
        gap: .45rem;
        align-items: center;
    }
    .meta-chip {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        border: 1px solid rgba(2,8,20,.1);
        border-radius: 999px;
        background: #f8fafc;
        color: #334155;
        padding: .15rem .55rem;
        font-size: .76rem;
        font-weight: 600;
    }
    .reason-box {
        background: #f8fafc;
        border: 1px solid rgba(2,8,20,.06);
        border-radius: .75rem;
        padding: .65rem .85rem;
        max-width: 600px;
    }
    .reason-label, .response-label {
        font-size: .68rem;
        letter-spacing: .04em;
        text-transform: uppercase;
        color: #64748b;
        font-weight: 700;
        margin-bottom: .2rem;
    }
    .reason-text, .response-text {
        font-size: .83rem;
        color: #1e293b;
    }
    .response-box {
        background: rgba(22,101,52,.04);
        border: 1px solid rgba(22,101,52,.12);
        border-radius: .75rem;
        padding: .65rem .85rem;
        max-width: 600px;
    }
    .leave-side {
        display: grid;
        gap: .6rem;
        min-width: 200px;
        justify-items: end;
    }
    .status-panel {
        text-align: right;
    }
    .status-note {
        margin-top: .25rem;
        font-size: .73rem;
        color: #64748b;
    }
    .leave-actions {
        display: inline-flex;
        gap: .45rem;
        align-items: center;
        flex-wrap: wrap;
        justify-content: flex-end;
    }
    .action-form {
        margin: 0;
    }

    @media (max-width: 767.98px) {
        .leave-item {
            grid-template-columns: 1fr;
        }
        .leave-side {
            justify-items: start;
            min-width: 0;
            width: 100%;
            border-top: 1px dashed rgba(2,8,20,.08);
            padding-top: .75rem;
        }
        .leave-actions {
            width: 100%;
            justify-content: flex-start;
        }
        .leave-actions .btn {
            flex: 1 1 auto;
        }
    }
</style>
@endpush

@push('scripts')
<script>
(function() {
    const searchInput = document.getElementById('leaveSearchInput');
    const statusFilter = document.getElementById('leaveStatusFilter');
    const items = Array.from(document.querySelectorAll('.leave-item'));
    const noMatch = document.getElementById('leaveNoMatch');

    if (!items.length) return;

    const applyFilters = () => {
        const query = (searchInput?.value || '').trim().toLowerCase();
        const status = statusFilter?.value || 'all';
        let visibleCount = 0;

        items.forEach(item => {
            const itemSearch = item.dataset.search || '';
            const itemStatus = item.dataset.status || '';

            const matchQuery = !query || itemSearch.includes(query);
            const matchStatus = status === 'all' || itemStatus === status;

            const visible = matchQuery && matchStatus;
            item.classList.toggle('d-none', !visible);
            if (visible) visibleCount++;
        });

        if (noMatch) {
            noMatch.classList.toggle('d-none', visibleCount > 0);
        }
    };

    if (searchInput) searchInput.addEventListener('input', applyFilters);
    if (statusFilter) statusFilter.addEventListener('change', applyFilters);

    applyFilters();
})();
</script>
@endpush
