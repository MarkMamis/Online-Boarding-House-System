@extends('layouts.admin')

@section('title', 'Incident Reports - Admin Management')

@section('content')
<div class="admin-page-shell">
    <x-admin.page-header
        title="Incident Reports"
        subtitle="Manage student incident reports, complaints, urgent issues, and resolution workflows."
        icon="bi-flag"
        :breadcrumbs="[
            ['label' => 'Management', 'url' => route('admin.dashboard')],
            ['label' => 'Incident Reports']
        ]"
    >
        <x-slot name="actions">
            <span class="text-secondary small fw-medium">
                <i class="bi bi-info-circle me-1"></i>{{ $reports->total() }} total entries
            </span>
        </x-slot>
    </x-admin.page-header>

    {{-- Status Quick Tabs --}}
    <ul class="admin-tabs">
        <li>
            <a href="{{ route('admin.reports.index', array_filter(['priority' => $priorityFilter !== 'all' ? $priorityFilter : null, 'search' => $search ?: null])) }}"
               @class(['admin-tab-link', 'active' => $statusFilter === 'all'])>
                <i class="bi bi-collection me-1"></i>All Reports
                <span class="badge bg-secondary-subtle text-secondary ms-1">{{ $counts['all'] ?? 0 }}</span>
            </a>
        </li>
        <li>
            <a href="{{ route('admin.reports.index', array_filter(['status' => 'pending', 'priority' => $priorityFilter !== 'all' ? $priorityFilter : null, 'search' => $search ?: null])) }}"
               @class(['admin-tab-link', 'active' => $statusFilter === 'pending'])>
                <i class="bi bi-clock-history me-1"></i>Pending Review
                <span class="badge bg-warning-subtle text-warning-emphasis ms-1">{{ $counts['pending'] ?? 0 }}</span>
            </a>
        </li>
        <li>
            <a href="{{ route('admin.reports.index', array_filter(['status' => 'in_progress', 'priority' => $priorityFilter !== 'all' ? $priorityFilter : null, 'search' => $search ?: null])) }}"
               @class(['admin-tab-link', 'active' => $statusFilter === 'in_progress'])>
                <i class="bi bi-arrow-repeat me-1"></i>In Progress
                <span class="badge bg-info-subtle text-info-emphasis ms-1">{{ $counts['in_progress'] ?? 0 }}</span>
            </a>
        </li>
        <li>
            <a href="{{ route('admin.reports.index', array_filter(['status' => 'resolved', 'priority' => $priorityFilter !== 'all' ? $priorityFilter : null, 'search' => $search ?: null])) }}"
               @class(['admin-tab-link', 'active' => $statusFilter === 'resolved'])>
                <i class="bi bi-check-circle me-1"></i>Resolved
                <span class="badge bg-success-subtle text-success ms-1">{{ $counts['resolved'] ?? 0 }}</span>
            </a>
        </li>
    </ul>

    {{-- KPI Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <x-admin.metric-card
                label="Total Incidents"
                :value="number_format($counts['all'] ?? 0)"
                icon="bi-flag"
                variant="brand"
                subtext="All reported cases"
            />
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <x-admin.metric-card
                label="Pending Action"
                :value="number_format($counts['pending'] ?? 0)"
                icon="bi-exclamation-circle"
                variant="warning"
                subtext="Awaiting initial review"
            />
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <x-admin.metric-card
                label="Under Investigation"
                :value="number_format($counts['in_progress'] ?? 0)"
                icon="bi-arrow-repeat"
                variant="neutral"
                subtext="Currently in progress"
            />
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <x-admin.metric-card
                label="High Priority Open"
                :value="number_format($counts['high_priority'] ?? 0)"
                icon="bi-shield-exclamation"
                variant="danger"
                subtext="Urgent unresolved reports"
            />
        </div>
    </div>

    {{-- Filter Toolbar --}}
    <div class="admin-toolbar mb-4">
        <form method="GET" action="{{ route('admin.reports.index') }}">
            @if($statusFilter !== 'all')
                <input type="hidden" name="status" value="{{ $statusFilter }}">
            @endif
            <div class="row g-2 align-items-center">
                <div class="col-12 col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-white text-muted border-end-0"><i class="bi bi-search"></i></span>
                        <input
                            type="text"
                            class="form-control border-start-0 ps-0"
                            name="search"
                            value="{{ $search ?? '' }}"
                            placeholder="Search by student, title, or keywords..."
                        >
                    </div>
                </div>
                <div class="col-6 col-md-4">
                    <select name="priority" class="form-select" onchange="this.form.submit()">
                        <option value="all" @selected($priorityFilter === 'all')>All Priorities</option>
                        <option value="high" @selected($priorityFilter === 'high')>Critical / High Priority</option>
                        <option value="medium" @selected($priorityFilter === 'medium')>Moderate / Medium Priority</option>
                        <option value="low" @selected($priorityFilter === 'low')>Minor / Low Priority</option>
                    </select>
                </div>
                <div class="col-6 col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-brand flex-fill">
                        <i class="bi bi-funnel me-1"></i>Filter
                    </button>
                    <a href="{{ route('admin.reports.index') }}" class="btn btn-action-icon" title="Reset Filters">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                </div>
            </div>
        </form>
    </div>

    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm alert-dismissible fade show mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="admin-table-card">
        <div class="admin-card-header d-flex justify-content-between align-items-center">
            <span class="fw-semibold text-dark"><i class="bi bi-list-ul me-1 text-primary"></i> Incident Records</span>
            <span class="badge bg-light text-secondary border fw-normal">{{ $reports->total() }} records</span>
        </div>
        <div class="table-responsive">
            <table class="table admin-table mb-0">
                <thead>
                    <tr>
                        <th class="ps-3" style="width: 75px;">ID</th>
                        <th>Student / Reporter</th>
                        <th>Incident / Subject</th>
                        <th>Associated Property</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Submitted</th>
                        <th class="pe-3 text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reports as $report)
                        @php
                            $userBooking = $report->user?->bookings?->firstWhere('status', 'approved');
                            $propName = $userBooking?->room?->property?->name ?? 'General / Not specified';
                        @endphp
                        <tr>
                            <td class="ps-3 fw-semibold text-muted">#{{ $report->id }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-circle d-inline-flex align-items-center justify-content-center bg-primary-subtle text-primary fw-bold" style="width: 34px; height: 34px; font-size: 0.82rem;">
                                        <i class="bi bi-person"></i>
                                    </div>
                                    <div>
                                        <div class="fw-semibold text-dark">{{ $report->user->full_name ?: $report->user->name }}</div>
                                        <div class="text-secondary small">{{ $report->user->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <a href="{{ route('admin.reports.show', $report) }}" class="text-decoration-none fw-semibold text-dark hover-underline">
                                    {{ Str::limit($report->title, 55) }}
                                </a>
                            </td>
                            <td>
                                <span class="small fw-medium text-dark">
                                    <i class="bi bi-building me-1 text-muted"></i>{{ $propName }}
                                </span>
                            </td>
                            <td>
                                @if($report->priority === 'high')
                                    <x-admin.status-badge status="rejected" label="Critical" />
                                @elseif($report->priority === 'medium')
                                    <x-admin.status-badge status="pending" label="Moderate" />
                                @else
                                    <x-admin.status-badge status="approved" label="Minor" />
                                @endif
                            </td>
                            <td>
                                @if($report->status === 'pending')
                                    <x-admin.status-badge status="pending" label="Pending" />
                                @elseif($report->status === 'in_progress')
                                    <x-admin.status-badge status="info" label="In Progress" />
                                @else
                                    <x-admin.status-badge status="completed" label="Resolved" />
                                @endif
                            </td>
                            <td>
                                <div class="small fw-medium text-dark">{{ $report->created_at->format('M d, Y') }}</div>
                                <div class="text-secondary small">{{ $report->created_at->diffForHumans() }}</div>
                            </td>
                            <td class="pe-3 text-end">
                                <a href="{{ route('admin.reports.show', $report) }}" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-eye me-1"></i>Review
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">
                                <x-admin.empty-state
                                    title="No incident reports found"
                                    description="No student incident or complaint records match the current filter criteria."
                                    icon="bi-flag"
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($reports->hasPages())
        <div class="d-flex justify-content-center mt-4">
            {{ $reports->links() }}
        </div>
    @endif
</div>
@endsection