@extends('layouts.landlord')

@section('content')
<div class="bookings-shell">
  <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
      <div>
          <div class="text-uppercase small text-muted fw-semibold">Booking Pipeline</div>
          <h1 class="h3 mb-1">Booking Requests</h1>
          <div class="text-muted small">Review incoming bookings and approve qualified requests quickly.</div>
      </div>
      <a href="{{ route('landlord.dashboard') }}" class="btn btn-outline-secondary rounded-pill px-3">Back</a>
  </div>

  <div class="booking-summary mb-4">
      <div class="booking-summary-item">
          <div class="booking-summary-label">Pending</div>
          <div class="booking-summary-value text-warning-emphasis">{{ $bookings->where('status', 'pending')->count() }}</div>
      </div>
      <div class="booking-summary-item">
          <div class="booking-summary-label">Approved</div>
          <div class="booking-summary-value text-success-emphasis">{{ $bookings->where('status', 'approved')->count() }}</div>
      </div>
      <div class="booking-summary-item">
          <div class="booking-summary-label">Rejected</div>
          <div class="booking-summary-value text-danger-emphasis">{{ $bookings->where('status', 'rejected')->count() }}</div>
      </div>
  </div>

  @if(session('success'))
    <div class="alert alert-success rounded-4">{{ session('success') }}</div>
  @endif

  <div class="booking-list-card">
    @forelse($bookings as $b)
      <article class="booking-item">
        <div class="booking-main">
          <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-2">
            <div>
              <div class="booking-title">{{ $b->room->property->name }}</div>
              <div class="text-muted small">Room {{ $b->room->room_number }} · {{ $b->student->full_name }}</div>
            </div>
            <div>
              @switch($b->status)
                @case('pending') <span class="badge rounded-pill text-bg-warning">Pending</span> @break
                @case('approved') <span class="badge rounded-pill text-bg-success">Approved</span> @break
                @case('rejected') <span class="badge rounded-pill text-bg-danger">Rejected</span> @break
                @default <span class="badge rounded-pill text-bg-secondary">{{ ucfirst($b->status) }}</span>
              @endswitch
            </div>
          </div>

          <div class="booking-meta-row">
            <span class="meta-chip"><i class="bi bi-calendar-range"></i>{{ $b->check_in->format('M d, Y') }} - {{ $b->check_out->format('M d, Y') }}</span>
            @if($b->notes)
              <span class="meta-note">{{ $b->notes }}</span>
            @endif
          </div>
        </div>

        <div class="booking-actions">
          @if($b->status === 'pending')
            <form action="{{ route('landlord.bookings.approve', $b->id) }}" method="POST" class="d-inline">@csrf<button class="btn btn-sm btn-success rounded-pill px-3">Approve</button></form>
            <form action="{{ route('landlord.bookings.reject', $b->id) }}" method="POST" class="d-inline">@csrf<button class="btn btn-sm btn-outline-danger rounded-pill px-3">Reject</button></form>
          @else
            <span class="text-muted small">No actions</span>
          @endif
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
    grid-template-columns: repeat(3, minmax(0, 1fr));
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
    align-items: center;
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
  .booking-actions {
    display: inline-flex;
    gap: .45rem;
    align-items: center;
  }
  @media (max-width: 991.98px) {
    .booking-summary {
      grid-template-columns: 1fr;
    }
    .bookings-shell {
      padding: .95rem;
    }
    .booking-item {
      grid-template-columns: 1fr;
      align-items: start;
    }
  }
</style>
@endpush
