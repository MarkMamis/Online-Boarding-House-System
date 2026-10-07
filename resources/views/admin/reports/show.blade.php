@extends('layouts.admin')

@section('title', 'View Report')

@section('content')
<div class="admin-page-shell">
    <x-admin.page-header
        title="Report Details"
        subtitle="Review student issue details and update administrative resolution progress."
        icon="bi-flag"
        :breadcrumbs="[
            ['label' => 'Insights', 'url' => route('admin.dashboard')],
            ['label' => 'Reports', 'url' => route('admin.reports.index')],
            ['label' => 'Report #' . $report->id]
        ]"
    >
        <x-slot name="actions">
            <span class="badge bg-light text-dark border px-3 py-2 fw-semibold">
                <i class="bi bi-hash me-1"></i>{{ $report->id }}
            </span>
            <a href="{{ route('admin.reports.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>Back to Reports
            </a>
        </x-slot>
    </x-admin.page-header>

    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm alert-dismissible fade show mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-4">
        <div class="col-12 col-xl-8">
            <div class="admin-table-card">
                <div class="admin-card-header fw-semibold d-flex align-items-center gap-2">
                    <i class="bi bi-file-earmark-text text-primary"></i>
                    <span>Report Information</span>
                </div>
                <div class="p-4">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="text-uppercase text-secondary fw-semibold small tracking-wider mb-1">Title</label>
                            <div class="fw-bold text-dark fs-5">{{ $report->title }}</div>
                        </div>

                        <div class="col-sm-6">
                            <label class="text-uppercase text-secondary fw-semibold small tracking-wider mb-1">Student</label>
                            <div class="d-flex align-items-center gap-2">
                                <div class="rounded-circle d-inline-flex align-items-center justify-content-center bg-primary-subtle text-primary fw-bold" style="width: 34px; height: 34px; font-size: 0.82rem;">
                                    <i class="bi bi-person"></i>
                                </div>
                                <div>
                                    <div class="fw-semibold text-dark">{{ $report->user->name }}</div>
                                    <div class="text-secondary small">{{ $report->user->email }}</div>
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <label class="text-uppercase text-secondary fw-semibold small tracking-wider mb-1">Priority & Status</label>
                            <div class="d-flex align-items-center gap-2">
                                @if($report->priority === 'high')
                                    <x-admin.status-badge status="rejected" label="Critical Priority" />
                                @elseif($report->priority === 'medium')
                                    <x-admin.status-badge status="pending" label="Moderate Priority" />
                                @else
                                    <x-admin.status-badge status="approved" label="Minor Priority" />
                                @endif

                                @if($report->status === 'pending')
                                    <x-admin.status-badge status="pending" label="Submitted" />
                                @elseif($report->status === 'in_progress')
                                    <x-admin.status-badge status="info" label="Reviewed" />
                                @else
                                    <x-admin.status-badge status="completed" label="Resolved" />
                                @endif
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <label class="text-uppercase text-secondary fw-semibold small tracking-wider mb-1">Submitted</label>
                            <div class="fw-medium text-dark">{{ $report->created_at->format('M d, Y h:i A') }}</div>
                            <div class="text-secondary small">{{ $report->created_at->diffForHumans() }}</div>
                        </div>

                        @if($report->resolved_at)
                            <div class="col-sm-6">
                                <label class="text-uppercase text-secondary fw-semibold small tracking-wider mb-1">Resolved</label>
                                <div class="fw-medium text-dark">{{ $report->resolved_at->format('M d, Y h:i A') }}</div>
                                <div class="text-secondary small">{{ $report->resolved_at->diffForHumans() }}</div>
                            </div>
                        @endif

                        <div class="col-12 mt-3">
                            <label class="text-uppercase text-secondary fw-semibold small tracking-wider mb-1">Description</label>
                            <div class="p-3 bg-light rounded-3 border text-dark">
                                {!! nl2br(e($report->description)) !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-4">
            <div class="admin-table-card">
                <div class="admin-card-header fw-semibold d-flex align-items-center gap-2">
                    <i class="bi bi-sliders2 text-primary"></i>
                    <span>Update Report</span>
                </div>
                <div class="p-4">
                    <form action="{{ route('admin.reports.update', $report) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="status" class="form-label fw-semibold text-dark small">Status</label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="pending" {{ $report->status === 'pending' ? 'selected' : '' }}>Submitted</option>
                                <option value="in_progress" {{ $report->status === 'in_progress' ? 'selected' : '' }}>Reviewed</option>
                                <option value="resolved" {{ $report->status === 'resolved' ? 'selected' : '' }}>Resolved</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="admin_response" class="form-label fw-semibold text-dark small">Admin Response</label>
                            <textarea
                                class="form-control"
                                id="admin_response"
                                name="admin_response"
                                rows="5"
                                placeholder="Add your administrative response or resolution notes..."
                            >{{ $report->admin_response }}</textarea>
                        </div>

                        <button type="submit" class="btn btn-brand w-100">
                            <i class="bi bi-check2-circle me-1"></i>Update Report
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection