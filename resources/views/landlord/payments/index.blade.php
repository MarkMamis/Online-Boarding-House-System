@extends('layouts.landlord')

@section('content')
<div class="glass-card rounded-4 p-4 p-md-5">
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="h4 mb-0">Payments & Billing</h2>
</div>

@if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<!-- Payment Statistics -->
<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="card shadow-sm border-success">
            <div class="card-body text-center">
                <h4 class="text-success mb-1">₱{{ number_format($totalPaid, 2) }}</h4>
                <small class="text-muted">Total Paid</small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm border-warning">
            <div class="card-body text-center">
                <h4 class="text-warning mb-1">₱{{ number_format($totalPending, 2) }}</h4>
                <small class="text-muted">Pending Payments</small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm border-info">
            <div class="card-body text-center">
                <h4 class="text-info mb-1">₱{{ number_format($totalExpected, 2) }}</h4>
                <small class="text-muted">Expected Revenue</small>
            </div>
        </div>
    </div>
</div>

<!-- Bookings with Payment Status -->
<div class="card shadow-sm">
    <div class="card-header">
        <h5 class="mb-0">Booking Payments</h5>
    </div>
    <div class="card-body">
        @if($bookings->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="small text-uppercase">
                        <tr>
                            <th>Student</th>
                            <th>Property</th>
                            <th>Room</th>
                            <th>Check-in</th>
                            <th>Check-out</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="small">
                        @foreach($bookings as $booking)
                            <tr>
                                <td>{{ $booking->student->full_name }}</td>
                                <td>{{ $booking->room->property->name }}</td>
                                <td>{{ $booking->room->room_number }}</td>
                                <td>{{ $booking->check_in->format('M d, Y') }}</td>
                                <td>{{ $booking->check_out->format('M d, Y') }}</td>
                                <td>₱{{ number_format($booking->room->price * $booking->getDurationInDays(), 2) }}</td>
                                <td>
                                    @if($booking->payment_status === 'paid')
                                        <span class="badge bg-success">Paid</span>
                                    @else
                                        <span class="badge bg-warning">Pending</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    @if($booking->payment_status === 'paid')
                                        <form action="{{ route('landlord.payments.mark_pending', $booking->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-warning" onclick="return confirm('Mark this payment as pending?')">Mark Pending</button>
                                        </form>
                                    @else
                                        <form action="{{ route('landlord.payments.mark_paid', $booking->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Mark this payment as received?')">Mark Paid</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-muted mb-0">No approved bookings found.</p>
        @endif
    </div>
</div>
</div>
@endsection