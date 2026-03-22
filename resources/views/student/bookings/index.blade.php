@extends('layouts.student_dashboard')

@section('title', 'Booking Requests')

@push('styles')
<style>
    .requests-hero {
        border: 1px solid rgba(2,8,20,.08);
        border-radius: 1rem;
        background: linear-gradient(180deg, #fff 0%, #fbfdfc 100%);
        box-shadow: 0 10px 24px rgba(2,8,20,.06);
        padding: 1.25rem;
    }
    .requests-stats {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: .7rem;
        margin-top: 1rem;
    }
    .requests-stat {
        border: 1px solid rgba(2,8,20,.08);
        border-radius: .8rem;
        background: #fff;
        padding: .65rem .75rem;
    }
    .requests-stat-label {
        font-size: .7rem;
        text-transform: uppercase;
        letter-spacing: .06em;
        color: rgba(2,8,20,.5);
        font-weight: 700;
        margin-bottom: .2rem;
    }
    .requests-stat-value {
        font-size: 1rem;
        font-weight: 700;
        color: #0f172a;
    }
    .request-card {
        border: 1px solid rgba(2,8,20,.08);
        border-radius: 1rem;
        background: #fff;
        box-shadow: 0 8px 18px rgba(2,8,20,.05);
        padding: 1rem;
        margin-top: .9rem;
    }
    .request-card-head {
        display: flex;
        justify-content: space-between;
        align-items: start;
        gap: .7rem;
        margin-bottom: .75rem;
    }
    .request-title {
        font-size: 1rem;
        font-weight: 700;
        color: #0f172a;
    }
    .request-subtitle {
        font-size: .82rem;
        color: rgba(2,8,20,.55);
    }
    .request-status {
        border-radius: 999px;
        padding: .22rem .7rem;
        font-size: .72rem;
        font-weight: 700;
        border: 1px solid transparent;
        white-space: nowrap;
    }
    .request-status.pending { background: #fef3c7; color: #92400e; border-color: #fcd34d; }
    .request-status.approved { background: #dcfce7; color: #166534; border-color: #86efac; }
    .request-status.rejected { background: #fee2e2; color: #991b1b; border-color: #fca5a5; }
    .request-status.cancelled { background: #e2e8f0; color: #334155; border-color: #cbd5e1; }
    .request-metrics {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: .6rem;
        margin-bottom: .75rem;
    }
    .request-metric {
        border: 1px solid rgba(2,8,20,.06);
        border-radius: .75rem;
        background: #f8fafc;
        padding: .6rem;
    }
    .request-metric .k {
        font-size: .68rem;
        text-transform: uppercase;
        letter-spacing: .05em;
        color: rgba(2,8,20,.48);
        font-weight: 700;
        margin-bottom: .2rem;
    }
    .request-metric .v {
        font-size: .88rem;
        font-weight: 700;
        color: #0f172a;
    }
    .request-note {
        border: 1px solid rgba(22,101,52,.18);
        border-left: 3px solid var(--brand);
        background: rgba(22,101,52,.05);
        border-radius: .7rem;
        padding: .65rem .75rem;
        margin-bottom: .75rem;
    }
    .request-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: .6rem;
        flex-wrap: wrap;
    }
    .cancel-form {
        display: flex;
        gap: .45rem;
        align-items: center;
        flex-wrap: wrap;
    }
    .empty-state {
        border: 1px dashed rgba(2,8,20,.16);
        border-radius: 1rem;
        text-align: center;
        padding: 2rem 1rem;
        color: rgba(2,8,20,.56);
        min-height: 260px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }
    .empty-state i {
        font-size: 2.25rem;
        margin-bottom: .7rem;
        opacity: .45;
    }
    .empty-actions .btn {
        padding: .38rem .82rem;
        font-size: .8rem;
        line-height: 1.15;
    }
    .empty-actions .btn i {
        font-size: .82rem;
    }
    @media (max-width: 991.98px) {
        .requests-stats,
        .request-metrics {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }
</style>
@endpush

@section('content')
@php
    $bookingItems = $bookings instanceof \Illuminate\Pagination\AbstractPaginator
        ? $bookings->getCollection()
        : collect($bookings);
    $pendingCount = (int) $bookingItems->where('status', 'pending')->count();
    $approvedCount = (int) $bookingItems->where('status', 'approved')->count();
    $rejectedCount = (int) $bookingItems->where('status', 'rejected')->count();
    $totalCount = (int) $bookingItems->count();
@endphp

<div class="requests-hero mb-4">
    <h3 class="fw-bold mb-1">Booking Requests</h3>
    <p class="text-muted small mb-0">Track status updates, review booking timelines, and manage pending requests.</p>

    <div class="requests-stats">
        <div class="requests-stat">
            <div class="requests-stat-label">Total</div>
            <div class="requests-stat-value">{{ $totalCount }}</div>
        </div>
        <div class="requests-stat">
            <div class="requests-stat-label">Pending</div>
            <div class="requests-stat-value">{{ $pendingCount }}</div>
        </div>
        <div class="requests-stat">
            <div class="requests-stat-label">Approved</div>
            <div class="requests-stat-value">{{ $approvedCount }}</div>
        </div>
        <div class="requests-stat">
            <div class="requests-stat-label">Rejected</div>
            <div class="requests-stat-value">{{ $rejectedCount }}</div>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success rounded-4 mb-4" role="alert">
        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger rounded-4 mb-4" role="alert">
        <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
    </div>
@endif

@forelse($bookings as $booking)
    <article class="request-card">
        <div class="request-card-head">
            <div>
                <div class="request-title">{{ $booking->room->property->name }}</div>
                <div class="request-subtitle">
                    <i class="bi bi-building me-1"></i>Room {{ $booking->room->room_number }}
                </div>
            </div>
            <span class="request-status {{ $booking->status }}">
                {{ ucfirst($booking->status) }}
            </span>
        </div>

        <div class="request-metrics">
            <div class="request-metric">
                <div class="k"><i class="bi bi-calendar-event me-1"></i>Check-in</div>
                <div class="v">{{ $booking->check_in->format('M d, Y') }}</div>
            </div>
            <div class="request-metric">
                <div class="k"><i class="bi bi-calendar-x me-1"></i>Check-out</div>
                <div class="v">{{ $booking->check_out->format('M d, Y') }}</div>
            </div>
            <div class="request-metric">
                <div class="k"><i class="bi bi-hourglass me-1"></i>Duration</div>
                <div class="v">{{ $booking->getDurationInDays() }} days</div>
            </div>
            <div class="request-metric">
                <div class="k"><i class="bi bi-clock me-1"></i>Requested</div>
                <div class="v">{{ $booking->created_at->diffForHumans() }}</div>
            </div>
        </div>

        @if($booking->notes)
            <div class="request-note">
                <div class="small fw-semibold mb-1" style="letter-spacing:.02em;">Notes</div>
                <div class="small" style="color:#0f172a;">{{ $booking->notes }}</div>
            </div>
        @endif

        <div class="request-actions">
            <a href="{{ route('student.rooms.show', $booking->room_id) }}" class="btn btn-sm btn-outline-secondary rounded-pill">
                <i class="bi bi-eye me-1"></i>View room
            </a>

            @if($booking->status === 'pending')
                <form action="{{ route('student.bookings.cancel', $booking->id) }}" method="POST" class="cancel-form" onsubmit="return confirm('Cancel this booking request?');">
                    @csrf
                    <input type="text" name="cancel_reason" class="form-control form-control-sm rounded-pill" placeholder="Reason (optional)" style="min-width: 200px;">
                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill">
                        <i class="bi bi-trash me-1"></i>Cancel Request
                    </button>
                </form>
            @elseif($booking->status === 'approved')
                <div class="text-muted small">
                    <i class="bi bi-check-circle text-success me-2"></i>Your booking is confirmed. Check-in on {{ $booking->check_in->format('M d, Y') }}
                </div>
            @elseif($booking->status === 'rejected')
                <div class="text-muted small">
                    <i class="bi bi-x-circle text-danger me-2"></i>Your booking request was rejected by the landlord
                </div>
            @elseif($booking->status === 'cancelled')
                <div class="text-muted small">
                    <i class="bi bi-dash-circle text-secondary me-2"></i>This booking request was cancelled
                </div>
            @endif
        </div>
    </article>
@empty
    <div class="request-card">
        <div class="empty-state">
            <i class="bi bi-inbox"></i>
            <h5 class="fw-semibold mb-2">No Booking Requests Yet</h5>
            <p class="text-muted mb-3">You have not made any room booking requests. Start by exploring available rooms.</p>
            <div class="d-flex gap-2 flex-wrap justify-content-center empty-actions">
                <a href="{{ route('student.rooms.index') }}" class="btn btn-brand btn-sm rounded-pill">
                    <i class="bi bi-search me-1"></i>Browse Available Rooms
                </a>
                <a href="{{ route('student.properties.map_view') }}" class="btn btn-outline-secondary btn-sm rounded-pill">
                    <i class="bi bi-map me-1"></i>Open Property Map
                </a>
            </div>
        </div>
    </div>
@endforelse

@if($bookings instanceof \Illuminate\Pagination\AbstractPaginator && $bookings->hasPages())
    <div class="mt-3">
        {{ $bookings->links() }}
    </div>
@endif

@endsection
