@extends('layouts.admin')

@section('content')
<div class="admin-page-shell">
    <x-admin.page-header
        title="Onboarding Pipeline"
        subtitle="Track students progressing from approved booking to completed move-in."
        icon="bi-clipboard-check"
        :breadcrumbs="[
            ['label' => 'Monitoring', 'url' => route('admin.dashboard')],
            ['label' => 'Onboarding Pipeline']
        ]"
    >
        <x-slot name="actions">
            <span class="text-secondary small fw-medium">
                <i class="bi bi-info-circle me-1"></i>{{ $onboardings->total() }} total entries
            </span>
        </x-slot>
    </x-admin.page-header>

    <!-- Metrics -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-md-3">
            <x-admin.metric-card
                label="Total Onboardings"
                :value="number_format($onboardings->total())"
                icon="bi-clipboard-data"
                variant="brand"
                subtext="Registered in pipeline"
            />
        </div>
        <div class="col-sm-6 col-md-3">
            <x-admin.metric-card
                label="Showing on Page"
                :value="number_format($onboardings->count())"
                icon="bi-file-earmark-text"
                variant="neutral"
                subtext="Page {{ $onboardings->currentPage() }} of {{ $onboardings->lastPage() ?: 1 }}"
            />
        </div>
    </div>

    <!-- Filter Toolbar -->
    <div class="admin-toolbar mb-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <div class="fw-semibold text-dark small text-uppercase tracking-wider">
                <i class="bi bi-funnel me-1 text-primary"></i> Filter Pipeline
            </div>
            <!-- Segmented Status Filter -->
            <div class="d-flex flex-wrap gap-1">
                <a href="{{ route('admin.onboardings.index', ['search' => $search ?? null, 'date_from' => $dateFrom ?? null, 'date_to' => $dateTo ?? null]) }}"
                    class="filter-chip {{ ($status ?? 'all') === 'all' ? 'active' : '' }}">All</a>
                <a href="{{ route('admin.onboardings.index', ['status' => 'active', 'search' => $search ?? null, 'date_from' => $dateFrom ?? null, 'date_to' => $dateTo ?? null]) }}"
                    class="filter-chip {{ ($status ?? 'all') === 'active' ? 'active' : '' }}">Active</a>
                <a href="{{ route('admin.onboardings.index', ['status' => 'pending', 'search' => $search ?? null, 'date_from' => $dateFrom ?? null, 'date_to' => $dateTo ?? null]) }}"
                    class="filter-chip {{ ($status ?? 'all') === 'pending' ? 'active' : '' }}">Pending</a>
                <a href="{{ route('admin.onboardings.index', ['status' => 'completed', 'search' => $search ?? null, 'date_from' => $dateFrom ?? null, 'date_to' => $dateTo ?? null]) }}"
                    class="filter-chip {{ ($status ?? 'all') === 'completed' ? 'active' : '' }}">Completed</a>
            </div>
        </div>

        <form method="GET" action="{{ route('admin.onboardings.index') }}" id="onboardingFilterForm">
            <input type="hidden" name="status" value="{{ $status ?? 'all' }}">
            <div class="row g-2 align-items-center">
                <div class="col-12 col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-white text-muted border-end-0"><i class="bi bi-search"></i></span>
                        <input
                            type="text"
                            class="form-control border-start-0 ps-0"
                            name="search"
                            value="{{ $search ?? '' }}"
                            placeholder="Search student, landlord, property, room, ID..."
                        >
                    </div>
                </div>
                <div class="col-6 col-md-2">
                    <input type="date" class="form-control" name="date_from" value="{{ $dateFrom ?? '' }}" title="Created From">
                </div>
                <div class="col-6 col-md-2">
                    <input type="date" class="form-control" name="date_to" value="{{ $dateTo ?? '' }}" title="Created To">
                </div>
                <div class="col-12 col-md-3 d-flex gap-2">
                    <button class="btn btn-brand flex-fill" type="submit">
                        <i class="bi bi-funnel me-1"></i>Apply
                    </button>
                    <a href="{{ route('admin.onboardings.index') }}" class="btn btn-action-icon" title="Reset Filters">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- Table Card -->
    <div class="admin-table-card">
        <div class="admin-card-header d-flex justify-content-between align-items-center">
            <span class="fw-semibold text-dark"><i class="bi bi-list-ul me-1 text-primary"></i> Onboarding Records</span>
            <span class="badge bg-light text-secondary border fw-normal">{{ $onboardings->total() }} total</span>
        </div>
        <div class="table-responsive">
            <table class="table admin-table mb-0">
                <thead>
                    <tr>
                        <th class="ps-3">Student</th>
                        <th>Property</th>
                        <th>Landlord</th>
                        <th>Room</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th class="pe-3 text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($onboardings as $onboarding)
                        <tr>
                            <td class="ps-3">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-circle d-inline-flex align-items-center justify-content-center bg-primary-subtle text-primary fw-bold" style="width: 34px; height: 34px; font-size: 0.82rem;">
                                        <i class="bi bi-person"></i>
                                    </div>
                                    <div>
                                        <div class="fw-semibold text-dark">{{ $onboarding->booking->student->full_name }}</div>
                                        <div class="text-secondary small">{{ $onboarding->booking->student->student_id ?: 'Not provided' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark">{{ $onboarding->booking->room->property->name }}</div>
                                <div class="text-secondary small">{{ Str::limit($onboarding->booking->room->property->address, 30) }}</div>
                            </td>
                            <td>
                                <div>{{ $onboarding->booking->room->property->landlord->full_name }}</div>
                                <div class="text-secondary small">{{ $onboarding->booking->room->property->landlord->email }}</div>
                            </td>
                            <td>
                                <div><span class="badge bg-light text-dark border">{{ $onboarding->booking->room->room_number }}</span></div>
                                <div class="text-secondary small mt-1">PHP {{ number_format($onboarding->booking->room->price, 2) }}/mo</div>
                            </td>
                            <td>
                                @switch($onboarding->status)
                                    @case('pending')
                                        <x-admin.status-badge status="pending" label="Pending Documents" />
                                        @break
                                    @case('documents_uploaded')
                                        <x-admin.status-badge status="info" label="Docs Uploaded" />
                                        @break
                                    @case('documents_approved')
                                        <x-admin.status-badge status="approved" label="Docs Approved" />
                                        @break
                                    @case('contract_signed')
                                        <x-admin.status-badge status="neutral" label="Contract Signed" />
                                        @break
                                    @case('deposit_paid')
                                        <x-admin.status-badge status="active" label="Deposit Paid" />
                                        @break
                                    @case('completed')
                                        <x-admin.status-badge status="completed" label="Completed" />
                                        @break
                                    @default
                                        <x-admin.status-badge :status="$onboarding->status" />
                                @endswitch
                            </td>
                            <td>
                                <div class="small fw-medium text-dark">{{ $onboarding->created_at->format('M d, Y') }}</div>
                                <div class="text-secondary small">{{ $onboarding->created_at->diffForHumans() }}</div>
                            </td>
                            <td class="pe-3 text-end">
                                <a href="{{ route('admin.onboardings.show', $onboarding) }}" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-eye me-1"></i>View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <x-admin.empty-state
                                    title="No onboardings found"
                                    description="No student onboarding records match the selected status or date filters."
                                    icon="bi-clipboard-x"
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($onboardings->hasPages())
        <div class="d-flex justify-content-center mt-4">
            {{ $onboardings->links() }}
        </div>
    @endif
</div>
@endsection
