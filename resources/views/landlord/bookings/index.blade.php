@extends('layouts.landlord')

@section('title', 'Boarding Requests')

@section('content')
<div class="landlord-page-shell">
    @php
        $pendingCount = $bookings->where('status', 'pending')->count();
        $approvedCount = $bookings->where('status', 'approved')->count();
        $rejectedCount = $bookings->where('status', 'rejected')->count();
        $cancelledCount = $bookings->where('status', 'cancelled')->count();
    @endphp

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
        <div>
            <div class="landlord-header-eyebrow">Boarding Request Pipeline</div>
            <h1 class="landlord-header-title mb-1">Boarding Requests</h1>
            <div class="landlord-header-subtitle">Review incoming boarding requests and process student booking applications.</div>
        </div>
        @if($bookings->isNotEmpty())
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <span class="landlord-summary-strip">
                    <span class="landlord-summary-dot"></span>
                    <span><strong>{{ $bookings->count() }}</strong> {{ Str::plural('Request', $bookings->count()) }}</span>
                    @if($pendingCount > 0)
                        <span class="text-muted">·</span>
                        <span class="text-warning-emphasis fw-bold">{{ $pendingCount }} Action Required</span>
                    @endif
                    <span class="text-muted">·</span>
                    <span class="text-success fw-bold">{{ $approvedCount }}</span> Approved
                </span>
            </div>
        @endif
    </div>

    @if(session('success'))
        <div class="alert alert-success rounded-4 mb-3">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger rounded-4 mb-3">{{ session('error') }}</div>
    @endif

    @if($bookings->isNotEmpty())
        <div class="landlord-filter-bar mb-3">
            <div class="landlord-filter-search">
                <i class="bi bi-search"></i>
                <input id="bookingSearchInput" type="text" class="form-control" placeholder="Search student name, property, or room...">
            </div>
            <div class="landlord-filter-select">
                <select id="bookingStatusFilter" class="form-select">
                    <option value="all">All Requests</option>
                    <option value="pending" @selected($pendingCount > 0)>Pending ({{ $pendingCount }})</option>
                    <option value="approved">Approved ({{ $approvedCount }})</option>
                    <option value="rejected">Rejected ({{ $rejectedCount }})</option>
                    <option value="cancelled">Cancelled ({{ $cancelledCount }})</option>
                </select>
            </div>
        </div>
    @endif

    <div class="landlord-card">
        @forelse($bookings as $b)
            @php
                $status = strtolower((string) $b->status);
                $statusBadgeClass = match($status) {
                    'pending' => 'bg-warning-subtle text-warning-emphasis border border-warning-subtle',
                    'approved' => 'bg-success-subtle text-success border border-success-subtle',
                    'rejected' => 'bg-danger-subtle text-danger border border-danger-subtle',
                    'cancelled' => 'bg-secondary-subtle text-secondary border border-secondary-subtle',
                    default => 'bg-light text-dark'
                };
                $statusNote = match($status) {
                    'pending' => 'Waiting for your review decision',
                    'approved' => 'Approved · Tenant proceeding with onboarding',
                    'rejected' => 'Request was declined',
                    'cancelled' => 'Cancelled by student',
                    default => 'Status updated'
                };

                $searchBlob = strtolower(trim(implode(' ', [
                    $b->student->full_name ?? '',
                    $b->room->property->name ?? '',
                    $b->room->room_number ?? '',
                    $b->notes ?? '',
                ])));
            @endphp

            <article class="booking-item" data-search="{{ $searchBlob }}" data-status="{{ $status }}">
                <div class="booking-main">
                    <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                        <span class="fw-bold text-dark fs-6">{{ $b->room->property->name }}</span>
                        <span class="text-muted">·</span>
                        <span class="text-muted small">Room {{ $b->room->room_number }}</span>
                        <span class="badge rounded-pill {{ $statusBadgeClass }} small ms-auto d-md-none">
                            {{ ucfirst($status) }}
                        </span>
                    </div>

                    <div class="d-flex align-items-center gap-2 mb-2">
                        <div class="student-avatar-sm">
                            {{ strtoupper(substr($b->student->full_name ?? 'S', 0, 1)) }}
                        </div>
                        <div class="fw-semibold text-dark">{{ $b->student->full_name }}</div>
                        @if($b->student->year_level)
                            <span class="text-muted small">({{ $b->student->year_level }})</span>
                        @endif
                    </div>

                    <div class="booking-meta-row mb-2">
                        <span class="meta-chip">
                            <i class="bi bi-calendar-range"></i>
                            {{ $b->check_in->format('M d, Y') }} - {{ $b->check_out->format('M d, Y') }}
                        </span>
                        @if($b->notes)
                            <span class="meta-note"><i class="bi bi-chat-left-text me-1"></i>{{ $b->notes }}</span>
                        @endif
                    </div>
                </div>

                <div class="booking-side">
                    <div class="status-panel d-none d-md-block">
                        <span class="badge rounded-pill {{ $statusBadgeClass }} px-2 py-1">
                            {{ ucfirst($status) }}
                        </span>
                        <div class="status-note">{{ $statusNote }}</div>
                    </div>

                    <div class="booking-actions">
                        @if($status === 'pending')
                            <form action="{{ route('landlord.bookings.approve', $b->id) }}" method="POST" class="action-form" onsubmit="return confirm('Approve this request? Approving will allow the student to continue to move-in requirements (documents, contract, deposit).');">
                                @csrf
                                <button class="btn btn-sm btn-success rounded-pill px-3">
                                    <i class="bi bi-check2 me-1"></i>Approve
                                </button>
                            </form>
                            <form action="{{ route('landlord.bookings.reject', $b->id) }}" method="POST" class="action-form" onsubmit="return confirm('Decline this boarding request?');">
                                @csrf
                                <button class="btn btn-sm btn-outline-danger rounded-pill px-3">
                                    <i class="bi bi-x me-1"></i>Decline
                                </button>
                            </form>
                        @elseif($status === 'approved' && $b->tenantOnboarding)
                            <a href="{{ route('landlord.onboarding.review', $b->tenantOnboarding->id) }}" class="btn btn-sm btn-outline-brand rounded-pill px-3">
                                <i class="bi bi-clipboard-check me-1"></i>View Move-In Step
                            </a>
                        @else
                            <span class="text-muted small"><i class="bi bi-check-circle me-1"></i>Completed</span>
                        @endif
                    </div>
                </div>
            </article>
        @empty
            <div class="landlord-empty-state border-0">
                <div class="landlord-empty-icon">
                    <i class="bi bi-inbox"></i>
                </div>
                <div class="landlord-empty-title">No Boarding Requests Yet</div>
                <div class="landlord-empty-text">When students submit boarding requests for your rooms, they will appear here for your review and approval.</div>
            </div>
        @endforelse

        @if($bookings->isNotEmpty())
            <div id="bookingNoMatch" class="landlord-empty-state border-0 d-none">
                <div class="landlord-empty-icon">
                    <i class="bi bi-search"></i>
                </div>
                <div class="landlord-empty-title">No matching requests</div>
                <div class="landlord-empty-text">Try adjusting your search query or selected status filter.</div>
            </div>
        @endif
    </div>
</div>
@endsection

@push('styles')
<style>
    .booking-item {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        gap: 1rem;
        align-items: start;
        padding: 1.1rem 1.25rem;
        border-bottom: 1px solid rgba(2,8,20,.08);
        transition: background .2s ease;
    }
    .booking-item:hover {
        background: #fbfcfd;
    }
    .booking-item:last-child {
        border-bottom: none;
    }
    .booking-meta-row {
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
    .meta-note {
        font-size: .78rem;
        color: #64748b;
        background: #f1f5f9;
        border-radius: .5rem;
        padding: .15rem .5rem;
    }
    .student-avatar-sm {
        width: 24px;
        height: 24px;
        border-radius: 50%;
        background: rgba(20,83,45,.15);
        color: #14532d;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: .7rem;
        font-weight: 700;
        flex-shrink: 0;
    }
    .booking-side {
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
    .booking-actions {
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
        .booking-item {
            grid-template-columns: 1fr;
        }
        .booking-side {
            justify-items: start;
            min-width: 0;
            width: 100%;
            border-top: 1px dashed rgba(2,8,20,.08);
            padding-top: .75rem;
        }
        .booking-actions {
            width: 100%;
            justify-content: flex-start;
        }
        .booking-actions .btn {
            flex: 1 1 auto;
        }
    }
</style>
@endpush

@push('scripts')
<script>
(function() {
    const searchInput = document.getElementById('bookingSearchInput');
    const statusFilter = document.getElementById('bookingStatusFilter');
    const items = Array.from(document.querySelectorAll('.booking-item'));
    const noMatch = document.getElementById('bookingNoMatch');

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

    // Initial run if default filter is selected
    applyFilters();
})();
</script>
@endpush
