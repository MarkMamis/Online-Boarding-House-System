@extends('layouts.admin')

@section('title', 'View Report')

@section('content')
<style>
    .report-view-shell {
        background: linear-gradient(180deg, #ffffff 0%, #fbfdfc 100%);
        border: 1px solid rgba(2, 8, 20, .08);
        border-radius: 1.25rem;
        box-shadow: 0 10px 26px rgba(2, 8, 20, .06);
        padding: 1.25rem;
    }

    .section-muted {
        color: rgba(2, 8, 20, .58);
    }

    .report-hero {
        border: 1px solid rgba(20, 83, 45, .16);
        border-radius: 1rem;
        background: radial-gradient(560px 260px at 100% 0%, rgba(167, 243, 208, .36), transparent 62%), linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
        padding: 1rem;
    }

    .report-id-chip {
        display: inline-flex;
        align-items: center;
        gap: .4rem;
        border-radius: 999px;
        border: 1px solid rgba(20, 83, 45, .22);
        background: rgba(167, 243, 208, .2);
        color: #14532d;
        font-size: .78rem;
        font-weight: 700;
        padding: .3rem .72rem;
    }

    .admin-surface {
        border: 1px solid rgba(2, 8, 20, .08);
        border-radius: 1rem;
        background: #fff;
        box-shadow: 0 8px 20px rgba(2, 8, 20, .05);
        overflow: hidden;
    }

    .admin-surface-head {
        padding: .9rem 1rem;
        border-bottom: 1px solid rgba(2, 8, 20, .08);
        background: #fff;
    }

    .report-detail-row {
        display: grid;
        grid-template-columns: 170px 1fr;
        gap: .9rem;
        padding: .72rem 0;
        border-bottom: 1px dashed rgba(2, 8, 20, .08);
    }

    .report-detail-row:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }

    .report-label {
        font-size: .76rem;
        text-transform: uppercase;
        letter-spacing: .06em;
        color: rgba(2, 8, 20, .52);
        font-weight: 700;
    }

    .status-badge,
    .priority-badge {
        font-size: .72rem;
        border-radius: 999px;
        padding: .38rem .62rem;
        text-transform: uppercase;
        letter-spacing: .04em;
    }

    .report-description {
        background: linear-gradient(180deg, rgba(248, 250, 252, .9), #ffffff);
        border: 1px solid rgba(2, 8, 20, .08);
        border-radius: .9rem;
        padding: .9rem;
        white-space: normal;
    }

    .form-select,
    .form-control {
        border-color: rgba(2, 8, 20, .14);
    }

    .form-select:focus,
    .form-control:focus {
        border-color: rgba(20, 83, 45, .45);
        box-shadow: 0 0 0 .18rem rgba(22, 101, 52, .14);
    }

    @media (max-width: 991.98px) {
        .report-view-shell {
            padding: .95rem;
        }

        .report-detail-row {
            grid-template-columns: 1fr;
            gap: .35rem;
        }
    }
</style>

<div class="report-view-shell container-fluid py-2">
    <div class="report-hero mb-4">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
            <div>
                <div class="text-uppercase small section-muted fw-semibold">Report Management</div>
                <h1 class="h3 mb-1 text-success">Report Details</h1>
                <p class="section-muted mb-0">Review issue details and update resolution progress.</p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <span class="report-id-chip"><i class="bi bi-hash"></i>{{ $report->id }}</span>
                <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary rounded-pill px-3">
                    <i class="bi bi-house-door me-1"></i>Dashboard
                </a>
                <a href="{{ route('admin.reports.index') }}" class="btn btn-outline-secondary rounded-pill px-3">
                    <i class="bi bi-arrow-left me-1"></i>Back to Reports
                </a>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-3">
        <div class="col-12 col-xl-8">
            <div class="admin-surface h-100">
                <div class="admin-surface-head fw-semibold d-flex align-items-center gap-2">
                    <i class="bi bi-file-earmark-text"></i>
                    <span>Report Information</span>
                </div>
                <div class="p-3 p-lg-4">
                    <div class="report-detail-row">
                        <div class="report-label">Title</div>
                        <div class="fw-semibold">{{ $report->title }}</div>
                    </div>
                    <div class="report-detail-row">
                        <div class="report-label">Student</div>
                        <div>
                            <div class="fw-semibold">{{ $report->user->name }}</div>
                            <div class="section-muted small">{{ $report->user->email }}</div>
                        </div>
                    </div>
                    <div class="report-detail-row">
                        <div class="report-label">Priority</div>
                        <div>
                            @if($report->priority === 'high')
                                <span class="badge priority-badge text-bg-danger">High</span>
                            @elseif($report->priority === 'medium')
                                <span class="badge priority-badge text-bg-warning">Medium</span>
                            @else
                                <span class="badge priority-badge text-bg-success">Low</span>
                            @endif
                        </div>
                    </div>
                    <div class="report-detail-row">
                        <div class="report-label">Status</div>
                        <div>
                            @if($report->status === 'pending')
                                <span class="badge status-badge text-bg-secondary">Pending</span>
                            @elseif($report->status === 'in_progress')
                                <span class="badge status-badge text-bg-primary">In Progress</span>
                            @else
                                <span class="badge status-badge text-bg-success">Resolved</span>
                            @endif
                        </div>
                    </div>
                    <div class="report-detail-row">
                        <div class="report-label">Submitted</div>
                        <div>
                            <div class="fw-semibold">{{ $report->created_at->format('M d, Y h:i A') }}</div>
                            <div class="section-muted small">{{ $report->created_at->diffForHumans() }}</div>
                        </div>
                    </div>
                    @if($report->resolved_at)
                        <div class="report-detail-row">
                            <div class="report-label">Resolved</div>
                            <div>
                                <div class="fw-semibold">{{ $report->resolved_at->format('M d, Y h:i A') }}</div>
                                <div class="section-muted small">{{ $report->resolved_at->diffForHumans() }}</div>
                            </div>
                        </div>
                    @endif
                    <div class="report-detail-row">
                        <div class="report-label">Description</div>
                        <div class="report-description">{!! nl2br(e($report->description)) !!}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-4">
            <div class="admin-surface">
                <div class="admin-surface-head fw-semibold d-flex align-items-center gap-2">
                    <i class="bi bi-sliders2"></i>
                    <span>Update Report</span>
                </div>
                <div class="p-3 p-lg-4">
                    <form action="{{ route('admin.reports.update', $report) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="status" class="form-label fw-semibold">Status</label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="pending" {{ $report->status === 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="in_progress" {{ $report->status === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                <option value="resolved" {{ $report->status === 'resolved' ? 'selected' : '' }}>Resolved</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="admin_response" class="form-label fw-semibold">Admin Response</label>
                            <textarea
                                class="form-control"
                                id="admin_response"
                                name="admin_response"
                                rows="5"
                                placeholder="Add your response..."
                            >{{ $report->admin_response }}</textarea>
                        </div>

                        <button type="submit" class="btn btn-success w-100 rounded-pill">
                            <i class="bi bi-save2 me-1"></i>Update Report
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection