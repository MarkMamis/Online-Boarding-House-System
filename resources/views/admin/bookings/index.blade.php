@extends('layouts.admin')

@section('title', 'All Bookings - Monitoring')

@section('content')
<div class="admin-page-shell">
    <x-admin.page-header
        title="All Bookings"
        subtitle="System-wide boarding activity and transactional occupancy requests."
        icon="bi-journal-check"
        :breadcrumbs="[
            ['label' => 'Monitoring', 'url' => route('admin.dashboard')],
            ['label' => 'Active Boarders', 'url' => route('admin.boarding_monitoring.students')],
            ['label' => 'All Bookings']
        ]"
    >
        <x-slot name="actions">
            <a href="{{ route('admin.boarding_monitoring.students') }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-door-open me-1"></i>Active Boarders
            </a>
        </x-slot>
    </x-admin.page-header>

    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <x-admin.metric-card
                label="Total Requests"
                :value="number_format($totalBookings)"
                icon="bi-journal-text"
                variant="brand"
                subtext="All-time booking submissions"
            />
        </div>
        <div class="col-6 col-lg-3">
            <x-admin.metric-card
                label="Pending Requests"
                :value="number_format($pendingBookings)"
                icon="bi-clock-history"
                variant="warning"
                subtext="Awaiting landlord approval"
            />
        </div>
        <div class="col-6 col-lg-3">
            <x-admin.metric-card
                label="Approved"
                :value="number_format($approvedBookings)"
                icon="bi-check-circle"
                variant="brand"
                subtext="Confirmed by landlords"
            />
        </div>
        <div class="col-6 col-lg-3">
            <x-admin.metric-card
                label="Active Tenants"
                :value="number_format($activeTenants)"
                icon="bi-people"
                variant="neutral"
                subtext="Currently residing students"
            />
        </div>
    </div>

    <!-- Filter Toolbar -->
    <div class="admin-toolbar mb-4">
        <form method="GET" action="{{ route('admin.bookings.index') }}" id="desktopBookingFilterForm">
            <div class="row g-2 align-items-center">
                <div class="col-12 col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-white text-muted border-end-0"><i class="bi bi-search"></i></span>
                        <input
                            type="text"
                            class="form-control border-start-0 ps-0"
                            name="search"
                            value="{{ $search ?? '' }}"
                            placeholder="Request ID, student, property, landlord..."
                        >
                    </div>
                </div>
                <div class="col-6 col-md-2">
                    <select class="form-select" name="status" title="Booking Status">
                        <option value="" @selected(!$status)>All Statuses</option>
                        <option value="pending" @selected($status==='pending')>Pending</option>
                        <option value="approved" @selected($status==='approved')>Approved</option>
                        <option value="rejected" @selected($status==='rejected')>Rejected</option>
                        <option value="cancelled" @selected($status==='cancelled')>Cancelled</option>
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <input type="date" class="form-control" name="date_from" value="{{ $dateFrom ?? '' }}" title="Requested From">
                </div>
                <div class="col-6 col-md-2">
                    <input type="date" class="form-control" name="date_to" value="{{ $dateTo ?? '' }}" title="Requested To">
                </div>
                <div class="col-6 col-md-2 d-flex gap-2">
                    <button class="btn btn-brand flex-fill" type="submit">
                        <i class="bi bi-funnel me-1"></i>Apply
                    </button>
                    <a href="{{ route('admin.bookings.index') }}" class="btn btn-action-icon" title="Reset Filters">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- Table Card -->
    <div class="admin-table-card">
        <div class="admin-card-header d-flex justify-content-between align-items-center">
            <span class="fw-semibold text-dark"><i class="bi bi-list-ul me-1 text-primary"></i> Boarding Request Records</span>
            <span class="badge bg-light text-secondary border fw-normal">{{ $bookings->total() }} total entries</span>
        </div>
        <div class="table-responsive">
            <table class="table admin-table mb-0">
                <thead>
                    <tr>
                        <th class="ps-3">Student</th>
                        <th>Property / Room</th>
                        <th>Landlord</th>
                        <th>Status</th>
                        <th>Stay Dates</th>
                        <th class="pe-3 text-end">Requested</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($bookings as $b)
                        <tr>
                            <td class="ps-3">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-circle d-inline-flex align-items-center justify-content-center bg-primary-subtle text-primary fw-bold" style="width: 34px; height: 34px; font-size: 0.82rem;">
                                        <i class="bi bi-person"></i>
                                    </div>
                                    <div>
                                        <div class="fw-semibold text-dark">{{ $b->student->full_name ?? '—' }}</div>
                                        <div class="text-secondary small">{{ $b->student->email ?? '' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark">{{ $b->room->property->name ?? '—' }}</div>
                                <div class="text-secondary small">Room {{ $b->room->room_number ?? '—' }} • {{ Str::limit($b->room->property->address ?? '', 28) }}</div>
                            </td>
                            <td>
                                <div>{{ $b->room->property->landlord->full_name ?? '—' }}</div>
                                <div class="text-secondary small">{{ $b->room->property->landlord->email ?? '' }}</div>
                            </td>
                            <td>
                                <x-admin.status-badge :status="$b->status" />
                            </td>
                            <td class="small">
                                <div class="fw-medium text-dark">{{ optional($b->check_in)->format('M d, Y') }}</div>
                                <div class="text-secondary small">to {{ optional($b->check_out)->format('M d, Y') }}</div>
                            </td>
                            <td class="pe-3 text-end text-secondary small">{{ $b->created_at->format('M d, Y h:i A') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <x-admin.empty-state
                                    title="No boarding requests found"
                                    description="No transactional booking records match your selected filters."
                                    icon="bi-journal-x"
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($bookings->hasPages())
        <div class="d-flex justify-content-center mt-4">
            {{ $bookings->links() }}
        </div>
    @endif
</div>
@endsection
