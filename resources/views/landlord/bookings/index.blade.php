@extends('layouts.landlord')

@section('content')
<div class="bookings-shell">
  @php
      $pendingCount = $bookings->where('status', 'pending')->count();
      $approvedCount = $bookings->where('status', 'approved')->count();
      $rejectedCount = $bookings->where('status', 'rejected')->count();
      $cancelledCount = $bookings->where('status', 'cancelled')->count();
  @endphp

  <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
      <div>
          <div class="text-uppercase small text-muted fw-semibold">Booking Pipeline</div>
          <h1 class="h3 mb-1">Booking Requests</h1>
          <div class="text-muted small">Review incoming bookings and approve qualified requests quickly.</div>
      </div>
      <!-- <a href="{{ route('landlord.dashboard') }}" class="btn btn-outline-secondary rounded-pill px-3">Back</a> -->
  </div>

  <div class="booking-summary mb-4">
      <div class="booking-summary-item">
          <div class="booking-summary-label">Pending</div>
        <div class="booking-summary-value text-warning-emphasis">{{ $pendingCount }}</div>
      </div>
      <div class="booking-summary-item">
          <div class="booking-summary-label">Approved</div>
        <div class="booking-summary-value text-success-emphasis">{{ $approvedCount }}</div>
      </div>
      <div class="booking-summary-item">
          <div class="booking-summary-label">Rejected</div>
        <div class="booking-summary-value text-danger-emphasis">{{ $rejectedCount }}</div>
      </div>
      <div class="booking-summary-item">
        <div class="booking-summary-label">Cancelled</div>
        <div class="booking-summary-value text-secondary-emphasis">{{ $cancelledCount }}</div>
      </div>
  </div>

  @if(session('success'))
    <div class="alert alert-success rounded-4">{{ session('success') }}</div>
  @endif

  <div class="booking-list-card">
    @forelse($bookings as $b)
      @php
          $status = strtolower((string) $b->status);
      @endphp
      <article class="booking-item">
        <div class="booking-main">
          <div class="mb-2">
            <div class="booking-title">{{ $b->room->property->name }}</div>
            <div class="text-muted small">Room {{ $b->room->room_number }} · {{ $b->student->full_name }}</div>
          </div>

          <div class="booking-meta-row">
            <span class="meta-chip"><i class="bi bi-calendar-range"></i>{{ $b->check_in->format('M d, Y') }} - {{ $b->check_out->format('M d, Y') }}</span>
            @if($b->notes)
              <span class="meta-note">{{ $b->notes }}</span>
            @endif
          </div>
        </div>

        <div class="booking-side">
          <div class="status-panel">
            @switch($status)
              @case('pending')
                <span class="status-pill status-pending"><i class="bi bi-hourglass-split"></i>Pending</span>
                <div class="status-note">Waiting for your decision</div>
                @break
              @case('approved')
                <span class="status-pill status-approved"><i class="bi bi-check-circle"></i>Approved</span>
                <div class="status-note">Tenant can proceed to onboarding</div>
                @break
              @case('rejected')
                <span class="status-pill status-rejected"><i class="bi bi-x-circle"></i>Rejected</span>
                <div class="status-note">Request is closed</div>
                @break
              @case('cancelled')
                <span class="status-pill status-cancelled"><i class="bi bi-slash-circle"></i>Cancelled</span>
                <div class="status-note">Cancelled by student</div>
                @break
              @default
                <span class="status-pill status-default"><i class="bi bi-info-circle"></i>{{ ucfirst($status ?: 'Unknown') }}</span>
                <div class="status-note">No additional actions available</div>
            @endswitch
          </div>

          <div class="booking-actions {{ $status !== 'pending' ? 'booking-actions-disabled' : '' }}">
            @if($status === 'pending')
              <form action="{{ route('landlord.bookings.approve', $b->id) }}" method="POST" class="action-form">@csrf<button class="btn btn-sm btn-success rounded-pill px-3"><i class="bi bi-check2 me-1"></i>Approve</button></form>
              <form action="{{ route('landlord.bookings.reject', $b->id) }}" method="POST" class="action-form">@csrf<button class="btn btn-sm btn-outline-danger rounded-pill px-3"><i class="bi bi-x me-1"></i>Reject</button></form>
            @else
              <span class="action-muted"><i class="bi bi-lock me-1"></i>No actions available</span>
            @endif
          </div>
        </div>
      </article>
    @empty
      <div class="text-center text-muted py-5">No booking requests yet.</div>
    @endforelse
  </div>
</div>
@endsection

@push('styles')
<style>
  .bookings-shell {
    background: linear-gradient(180deg, #ffffff 0%, #fbfdfc 100%);
    border: 1px solid rgba(2,8,20,.08);
    border-radius: 1.25rem;
    box-shadow: 0 10px 26px rgba(2,8,20,.06);
    padding: 1.25rem;
  }
  .booking-summary {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: .75rem;
  }
  .booking-summary-item {
    border: 1px solid rgba(20,83,45,.16);
    background: linear-gradient(180deg, rgba(167,243,208,.18), rgba(255,255,255,.85));
    border-radius: .9rem;
    padding: .7rem .8rem;
  }
  .booking-summary-label {
    font-size: .72rem;
    text-transform: uppercase;
    letter-spacing: .05em;
    color: rgba(2,8,20,.55);
    font-weight: 700;
    margin-bottom: .2rem;
  }
  .booking-summary-value {
    font-size: 1rem;
    font-weight: 700;
    color: #0f172a;
  }
  .booking-list-card {
    border: 1px solid rgba(2,8,20,.09);
    border-radius: 1rem;
    background: #fff;
    overflow: hidden;
  }
  .booking-item {
    display: grid;
    grid-template-columns: minmax(0, 1fr) auto;
    gap: .9rem;
    align-items: start;
    padding: .95rem 1rem;
    border-bottom: 1px solid rgba(2,8,20,.08);
  }
  .booking-item:last-child {
    border-bottom: none;
  }
  .booking-title {
    font-weight: 700;
    color: #14532d;
    line-height: 1.2;
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
    border: 1px solid rgba(2,8,20,.12);
    border-radius: 999px;
    background: #f8fafc;
    color: #0f172a;
    padding: .18rem .55rem;
    font-size: .78rem;
    font-weight: 600;
  }
  .meta-note {
    font-size: .79rem;
    color: rgba(2,8,20,.64);
  }
  .booking-side {
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
  .booking-actions {
    display: inline-flex;
    gap: .45rem;
    align-items: center;
    flex-wrap: wrap;
    justify-content: flex-end;
  }
  .action-form {
    display: inline-flex;
  }
  .booking-actions .btn {
    min-width: 98px;
  }
  .booking-actions-disabled {
    border: 1px dashed rgba(100,116,139,.35);
    border-radius: .7rem;
    padding: .25rem .55rem;
    background: rgba(248,250,252,.9);
  }
  .action-muted {
    font-size: .78rem;
    color: #64748b;
  }
  @media (max-width: 991.98px) {
    .booking-summary {
      grid-template-columns: repeat(2, minmax(0, 1fr));
    }
    .bookings-shell {
      padding: .95rem;
    }
    .booking-item {
      grid-template-columns: 1fr;
      align-items: start;
    }
    .booking-side {
      justify-items: start;
      min-width: 0;
    }
    .status-panel {
      text-align: left;
    }
    .booking-actions {
      justify-content: flex-start;
    }
  }
  @media (max-width: 575.98px) {
    .booking-summary {
      grid-template-columns: 1fr;
    }
  }
</style>
@endpush
