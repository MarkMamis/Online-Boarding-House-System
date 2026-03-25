@extends('layouts.admin')

@section('title', 'Reports Management')

@section('content')
<style>
    .admin-reports-shell {
        background: linear-gradient(180deg, #ffffff 0%, #fbfdfc 100%);
        border: 1px solid rgba(2, 8, 20, .08);
        border-radius: 1.25rem;
        box-shadow: 0 10px 26px rgba(2, 8, 20, .06);
        padding: 1.25rem;
    }

    .section-muted {
        color: rgba(2, 8, 20, .58);
    }

    .reports-page-title {
        color: #166534;
    }

    .admin-metric {
        border: 1px solid rgba(2, 8, 20, .08);
        border-radius: 1rem;
        background: #fff;
        box-shadow: 0 6px 16px rgba(2, 8, 20, .04);
        padding: .95rem 1rem;
        height: 100%;
    }

    .admin-metric-label {
        font-size: .78rem;
        text-transform: uppercase;
        letter-spacing: .04em;
        color: rgba(2, 8, 20, .55);
    }

    .admin-metric-value {
        font-size: 1.45rem;
        font-weight: 700;
        color: #166534;
    }

    .admin-table-card {
        border: 1px solid rgba(2, 8, 20, .08);
        border-radius: 1rem;
        background: #fff;
        box-shadow: 0 8px 20px rgba(2, 8, 20, .05);
        overflow: hidden;
    }

    .admin-card-header {
        padding: .85rem 1rem;
        border-bottom: 1px solid rgba(2, 8, 20, .08);
        background: #fff;
    }

    .report-avatar {
        width: 40px;
        height: 40px;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(22, 101, 52, .12);
        color: #166534;
        border: 1px solid rgba(22, 101, 52, .22);
        flex-shrink: 0;
    }

    .table thead th {
        font-size: .78rem;
        text-transform: uppercase;
        letter-spacing: .05em;
        color: rgba(2, 8, 20, .62);
        background: rgba(248, 250, 252, .96);
        border-bottom: 1px solid rgba(2, 8, 20, .08);
        white-space: nowrap;
    }

    .table tbody td {
        vertical-align: middle;
    }

    .status-badge,
    .priority-badge {
        font-size: .72rem;
        border-radius: 999px;
        padding: .38rem .62rem;
        text-transform: uppercase;
        letter-spacing: .04em;
    }

    .table-empty {
        padding: 3rem 1rem;
        text-align: center;
        color: rgba(2, 8, 20, .58);
    }

    @media (max-width: 767.98px) {
        .admin-reports-shell {
            padding: .95rem;
        }
    }
</style>

<div class="admin-reports-shell container-fluid py-2">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <div class="text-uppercase small section-muted fw-semibold">Management</div>
            <h1 class="h3 mb-1 reports-page-title">Reports Management</h1>
            <p class="section-muted mb-0">Track student issues, priorities, and resolution progress.</p>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary rounded-pill px-3">
            <i class="bi bi-arrow-left me-1"></i>Dashboard
        </a>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="admin-metric">
                <div class="admin-metric-label">Total Reports</div>
                <div class="admin-metric-value">{{ number_format($reports->total()) }}</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="admin-metric">
                <div class="admin-metric-label">Current Page</div>
                <div class="admin-metric-value">{{ number_format($reports->count()) }}</div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="admin-table-card">
        <div class="admin-card-header fw-semibold d-flex justify-content-between align-items-center gap-2">
            <span><i class="bi bi-list-ul me-1"></i> Report Records</span>
            <span class="badge text-bg-light border">{{ $reports->total() }} total entries</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-3">ID</th>
                        <th>Student</th>
                        <th>Title</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Submitted</th>
                        <th class="pe-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reports as $report)
                        <tr>
                            <td class="ps-3 fw-semibold">#{{ $report->id }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="report-avatar"><i class="bi bi-person"></i></div>
                                    <div>
                                        <div class="fw-semibold">{{ $report->user->name }}</div>
                                        <div class="section-muted small">{{ $report->user->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <a href="{{ route('admin.reports.show', $report) }}" class="text-decoration-none fw-semibold">
                                    {{ Str::limit($report->title, 60) }}
                                </a>
                            </td>
                            <td>
                                @if($report->priority === 'high')
                                    <span class="badge priority-badge text-bg-danger">High</span>
                                @elseif($report->priority === 'medium')
                                    <span class="badge priority-badge text-bg-warning">Medium</span>
                                @else
                                    <span class="badge priority-badge text-bg-success">Low</span>
                                @endif
                            </td>
                            <td>
                                @if($report->status === 'pending')
                                    <span class="badge status-badge text-bg-secondary">Pending</span>
                                @elseif($report->status === 'in_progress')
                                    <span class="badge status-badge text-bg-primary">In Progress</span>
                                @else
                                    <span class="badge status-badge text-bg-success">Resolved</span>
                                @endif
                            </td>
                            <td>
                                <div class="small fw-semibold">{{ $report->created_at->format('M d, Y') }}</div>
                                <div class="section-muted small">{{ $report->created_at->diffForHumans() }}</div>
                            </td>
                            <td class="pe-3">
                                <a href="{{ route('admin.reports.show', $report) }}" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-eye me-1"></i>View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="table-empty">
                                <div class="h6 mb-1"><i class="bi bi-inbox me-1"></i>No reports found.</div>
                                <div>There are no reports to display right now.</div>
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