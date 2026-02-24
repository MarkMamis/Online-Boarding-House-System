@extends('layouts.admin')

@section('content')
    <div class="container py-4">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-3">
            <div>
                <h1 class="h4 mb-1">Bookings Monitoring</h1>
                <div class="text-muted small">System-wide booking activity</div>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="card"><div class="card-body text-center">
                    <div class="h4 mb-0">{{ $totalBookings }}</div>
                    <div class="small text-muted">Total</div>
                </div></div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card"><div class="card-body text-center">
                    <div class="h4 mb-0">{{ $pendingBookings }}</div>
                    <div class="small text-muted">Pending</div>
                </div></div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card"><div class="card-body text-center">
                    <div class="h4 mb-0">{{ $approvedBookings }}</div>
                    <div class="small text-muted">Approved</div>
                </div></div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card"><div class="card-body text-center">
                    <div class="h4 mb-0">{{ $activeTenants }}</div>
                    <div class="small text-muted">Active Tenants</div>
                </div></div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-body">
                <form class="row g-2 align-items-end" method="GET" action="{{ route('admin.bookings.index') }}">
                    <div class="col-12 col-md-4">
                        <label class="form-label small mb-1">Status</label>
                        <select class="form-select" name="status">
                            <option value="" @selected(!$status)>All</option>
                            <option value="pending" @selected($status==='pending')>Pending</option>
                            <option value="approved" @selected($status==='approved')>Approved</option>
                            <option value="rejected" @selected($status==='rejected')>Rejected</option>
                            <option value="cancelled" @selected($status==='cancelled')>Cancelled</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-auto d-flex gap-2">
                        <button class="btn btn-primary">Apply</button>
                        <a href="{{ route('admin.bookings.index') }}" class="btn btn-outline-secondary">Reset</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Student</th>
                                <th>Property / Room</th>
                                <th>Landlord</th>
                                <th>Status</th>
                                <th>Dates</th>
                                <th>Requested</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($bookings as $b)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $b->student->full_name ?? '—' }}</div>
                                        <div class="text-muted small">{{ $b->student->email ?? '' }}</div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold">{{ $b->room->property->name ?? '—' }}</div>
                                        <div class="text-muted small">Room {{ $b->room->room_number ?? '—' }} • {{ $b->room->property->address ?? '' }}</div>
                                    </td>
                                    <td>
                                        <div>{{ $b->room->property->landlord->full_name ?? '—' }}</div>
                                        <div class="text-muted small">{{ $b->room->property->landlord->email ?? '' }}</div>
                                    </td>
                                    <td>
                                        @php $st = $b->status; @endphp
                                        <span class="badge
                                            @if($st==='pending') text-bg-warning
                                            @elseif($st==='approved') text-bg-success
                                            @elseif($st==='rejected') text-bg-danger
                                            @else text-bg-secondary @endif
                                        ">{{ ucfirst($st) }}</span>
                                    </td>
                                    <td class="small">
                                        {{ optional($b->check_in)->format('M d, Y') }}
                                        <span class="text-muted">to</span>
                                        {{ optional($b->check_out)->format('M d, Y') }}
                                    </td>
                                    <td class="text-muted small">{{ $b->created_at->format('M d, Y h:i A') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-5">No bookings found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="mt-3">
            {{ $bookings->links() }}
        </div>
    </div>
@endsection
