@extends('layouts.landlord')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h4 mb-0">Booking Requests</h1>
    <a href="{{ route('landlord.dashboard') }}" class="btn btn-outline-secondary">Back</a>
</div>

@if(session('success'))
  <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="card shadow-sm">
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead class="table-light">
        <tr>
          <th>Property</th>
          <th>Room</th>
          <th>Student</th>
          <th>Dates</th>
          <th>Status</th>
          <th class="text-end">Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse($bookings as $b)
          <tr>
            <td>{{ $b->room->property->name }}</td>
            <td>{{ $b->room->room_number }}</td>
            <td>{{ $b->student->full_name }}</td>
            <td>
              <div>{{ $b->check_in->format('M d, Y') }} → {{ $b->check_out->format('M d, Y') }}</div>
              @if($b->notes)
                <div class="small text-muted">{{ $b->notes }}</div>
              @endif
            </td>
            <td>
              @switch($b->status)
                @case('pending') <span class="badge text-bg-warning">Pending</span> @break
                @case('approved') <span class="badge text-bg-success">Approved</span> @break
                @case('rejected') <span class="badge text-bg-danger">Rejected</span> @break
                @default <span class="badge text-bg-secondary">{{ ucfirst($b->status) }}</span>
              @endswitch
            </td>
            <td class="text-end">
              @if($b->status==='pending')
                <form action="{{ route('landlord.bookings.approve', $b->id) }}" method="POST" class="d-inline">@csrf<button class="btn btn-sm btn-success">Approve</button></form>
                <form action="{{ route('landlord.bookings.reject', $b->id) }}" method="POST" class="d-inline">@csrf<button class="btn btn-sm btn-outline-danger">Reject</button></form>
              @else
                <span class="text-muted small">No actions</span>
              @endif
            </td>
          </tr>
        @empty
          <tr><td colspan="6" class="text-center text-muted py-4">No booking requests yet.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection
