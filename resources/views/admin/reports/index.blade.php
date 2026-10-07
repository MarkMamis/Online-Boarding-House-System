@extends('layouts.admin')

@section('title', 'Reports Management')

@section('content')
<div class="admin-page-shell">
    <x-admin.page-header
        title="Reports Management"
        subtitle="Track student issues, complaints, priorities, and resolution progress."
        icon="bi-flag"
        :breadcrumbs="[
            ['label' => 'Insights', 'url' => route('admin.dashboard')],
            ['label' => 'Reports']
        ]"
    >
        <x-slot name="actions">
            <span class="text-secondary small fw-medium">
                <i class="bi bi-info-circle me-1"></i>{{ $reports->total() }} total entries
            </span>
        </x-slot>
    </x-admin.page-header>

    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-md-3">
            <x-admin.metric-card
                label="Total Reports"
                :value="number_format($reports->total())"
                icon="bi-flag"
                variant="brand"
                subtext="Submitted across OBHS"
            />
        </div>
        <div class="col-sm-6 col-md-3">
            <x-admin.metric-card
                label="Showing on Page"
                :value="number_format($reports->count())"
                icon="bi-file-earmark-text"
                variant="neutral"
                subtext="Page {{ $reports->currentPage() }} of {{ $reports->lastPage() ?: 1 }}"
            />
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm alert-dismissible fade show mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="admin-table-card">
        <div class="admin-card-header d-flex justify-content-between align-items-center">
            <span class="fw-semibold text-dark"><i class="bi bi-list-ul me-1 text-primary"></i> Report Records</span>
            <span class="badge bg-light text-secondary border fw-normal">{{ $reports->total() }} total entries</span>
        </div>
        <div class="table-responsive">
            <table class="table admin-table mb-0">
                <thead>
                    <tr>
                        <th class="ps-3" style="width: 80px;">ID</th>
                        <th>Student</th>
                        <th>Title</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Submitted</th>
                        <th class="pe-3 text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reports as $report)
                        <tr>
                            <td class="ps-3 fw-semibold text-muted">#{{ $report->id }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-circle d-inline-flex align-items-center justify-content-center bg-primary-subtle text-primary fw-bold" style="width: 34px; height: 34px; font-size: 0.82rem;">
                                        <i class="bi bi-person"></i>
                                    </div>
                                    <div>
                                        <div class="fw-semibold text-dark">{{ $report->user->name }}</div>
                                        <div class="text-secondary small">{{ $report->user->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <a href="{{ route('admin.reports.show', $report) }}" class="text-decoration-none fw-semibold text-dark hover-underline">
                                    {{ Str::limit($report->title, 60) }}
                                </a>
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
                                    <x-admin.status-badge status="pending" label="Submitted" />
                                @elseif($report->status === 'in_progress')
                                    <x-admin.status-badge status="info" label="Reviewed" />
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
                                    <i class="bi bi-eye me-1"></i>View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <x-admin.empty-state
                                    title="No reports found"
                                    description="There are no student reports or complaints filed at this time."
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