@extends('layouts.student_dashboard')

@section('title', 'Booking Requests')

@push('styles')
<style>
    .booking-card {
        background: #fff;
        border: 1px solid rgba(2,8,20,.08);
        border-radius: 1rem;
        padding: 1.5rem;
        margin-bottom: 1rem;
        box-shadow: 0 2px 8px rgba(2,8,20,.06);
        transition: all 0.2s ease;
    }
    .booking-card:hover {
        box-shadow: 0 4px 16px rgba(2,8,20,.1);
    }
    .booking-card-header {
        display: flex;
        align-items: start;
        justify-content: space-between;
        margin-bottom: 1rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid rgba(2,8,20,.08);
    }
    .booking-card-title {
        font-weight: 700;
        font-size: 1rem;
        color: #0f172a;
        margin-bottom: 0.25rem;
    }
    .booking-card-subtitle {
        font-size: 0.85rem;
        color: rgba(2,8,20,.55);
    }
    .booking-card-status {
        padding: 0.4rem 0.8rem;
        border-radius: 0.5rem;
        font-size: 0.75rem;
        font-weight: 600;
    }
    .booking-details {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 1rem;
        margin-bottom: 1rem;
    }
    .booking-detail-item {
        padding: 0.75rem;
        background: #f8fafc;
        border-radius: 0.5rem;
        border: 1px solid rgba(2,8,20,.06);
    }
    .detail-label {
        font-size: 0.68rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: rgba(2,8,20,.45);
        margin-bottom: 0.35rem;
    }
    .detail-value {
        font-size: 0.95rem;
        font-weight: 600;
        color: #0f172a;
    }
    .booking-actions {
        display: flex;
        gap: 0.75rem;
        align-items: center;
    }
    .cancel-form {
        display: flex;
        gap: 0.5rem;
        align-items: center;
        flex-wrap: wrap;
    }
    .empty-state {
        text-align: center;
        padding: 3rem 1rem;
        color: rgba(2,8,20,.45);
    }
    .empty-state i {
        font-size: 3rem;
        margin-bottom: 1rem;
        opacity: 0.3;
    }
</style>
@endpush

@section('content')
<div class="glass-card rounded-4 p-4 p-md-5 mb-4">
    <h3 class="fw-bold mb-1">Booking Requests</h3>
    <p class="text-muted small mb-0">Track and manage your room booking requests</p>
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
    <div class="booking-card">
        <div class="booking-card-header">
            <div>
                <div class="booking-card-title">{{ $booking->room->property->name }}</div>
                <div class="booking-card-subtitle">
                    <i class="bi bi-building me-1"></i>Room {{ $booking->room->room_number }}
                </div>
            </div>
            <span class="booking-card-status @switch($booking->status)
                @case('pending') bg-warning text-dark @break
                @case('approved') bg-success text-white @break
                @case('rejected') bg-danger text-white @break
                @case('cancelled') bg-secondary text-white @break
                @default bg-secondary text-white
            @endswitch">
                {{ ucfirst($booking->status) }}
            </span>
        </div>

        <div class="booking-details">
            <div class="booking-detail-item">
                <div class="detail-label"><i class="bi bi-calendar-event me-1"></i>Check-in</div>
                <div class="detail-value">{{ $booking->check_in->format('M d, Y') }}</div>
            </div>
            <div class="booking-detail-item">
                <div class="detail-label"><i class="bi bi-calendar-x me-1"></i>Check-out</div>
                <div class="detail-value">{{ $booking->check_out->format('M d, Y') }}</div>
            </div>
            <div class="booking-detail-item">
                <div class="detail-label"><i class="bi bi-hourglass me-1"></i>Duration</div>
                <div class="detail-value">{{ $booking->getDurationInDays() }} days</div>
            </div>
            <div class="booking-detail-item">
                <div class="detail-label"><i class="bi bi-clock me-1"></i>Requested</div>
                <div class="detail-value">{{ $booking->created_at->diffForHumans() }}</div>
            </div>
        </div>

        @if($booking->notes)
            <div style="padding: 0.75rem; background: #f1f5f9; border-radius: 0.5rem; border-left: 3px solid var(--brand); margin-bottom: 1rem;">
                <div class="detail-label mb-2">Notes</div>
                <div style="font-size: 0.9rem; color: #0f172a;">{{ $booking->notes }}</div>
            </div>
        @endif

        <div class="booking-actions">
            @if($booking->status === 'pending')
                <form action="{{ route('student.bookings.cancel', $booking->id) }}" method="POST" class="cancel-form" onsubmit="return confirm('Cancel this booking request?');">
                    @csrf
                    <input type="text" name="cancel_reason" class="form-control form-control-sm rounded-pill" placeholder="Reason (optional)" style="flex: 1; min-width: 200px; max-width: 300px;">
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
    </div>
@empty
    <div class="booking-card">
        <div class="empty-state">
            <i class="bi bi-inbox"></i>
            <h5 class="fw-semibold mb-2">No Booking Requests Yet</h5>
            <p class="text-muted mb-3">You haven't made any room booking requests yet.</p>
            <a href="{{ route('student.rooms.index') }}" class="btn btn-brand btn-sm rounded-pill">
                <i class="bi bi-search me-1"></i>Browse Available Rooms
            </a>
        </div>
    </div>
@endforelse

@endsection
