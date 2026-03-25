@extends('layouts.admin')

@section('title', 'Pending Properties - Admin Panel')

@section('content')
<style>
    .admin-properties-shell {
        background: linear-gradient(180deg, #ffffff 0%, #fbfdfc 100%);
        border: 1px solid rgba(2, 8, 20, .08);
        border-radius: 1.25rem;
        box-shadow: 0 10px 26px rgba(2, 8, 20, .06);
        padding: 1.25rem;
    }

    .section-muted {
        color: rgba(2, 8, 20, .58);
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

    .property-avatar {
        width: 42px;
        height: 42px;
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
    }

    .table tbody td {
        vertical-align: middle;
    }

    .table-empty {
        padding: 3rem 1rem;
        text-align: center;
        color: rgba(2, 8, 20, .58);
    }

    .action-stack {
        min-width: 330px;
    }

    @media (max-width: 767.98px) {
        .admin-properties-shell {
            padding: .95rem;
        }

        .action-stack {
            min-width: 260px;
        }
    }
</style>

<div class="admin-properties-shell">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <div class="text-uppercase small section-muted fw-semibold">Management</div>
            <h1 class="h3 mb-1"><i class="bi bi-clock-history me-2"></i>Pending Property Approvals</h1>
            <p class="section-muted mb-0">Review newly added properties before they become visible to students.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('admin.properties.index') }}" class="btn btn-outline-secondary rounded-pill px-3">
                <i class="bi bi-list-ul me-1"></i>All Properties
            </a>
            <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary rounded-pill px-3">
                <i class="bi bi-arrow-left me-1"></i>Dashboard
            </a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="admin-metric">
                <div class="admin-metric-label">Pending Properties</div>
                <div class="admin-metric-value">{{ number_format($properties->total()) }}</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="admin-metric">
                <div class="admin-metric-label">Current Page</div>
                <div class="admin-metric-value">{{ number_format($properties->count()) }}</div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger border-0 shadow-sm">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="admin-table-card">
        <div class="admin-card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div class="fw-semibold"><i class="bi bi-hourglass-split me-1"></i> Pending Queue</div>
            <span class="badge text-bg-warning">{{ $properties->total() }} awaiting review</span>
        </div>

        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th class="ps-3">Property</th>
                        <th>Landlord</th>
                        <th>Address</th>
                        <th>Submitted</th>
                        <th class="pe-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($properties as $property)
                        <tr>
                            <td class="ps-3">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="property-avatar">
                                        <i class="bi bi-building"></i>
                                    </div>
                                    <div>
                                        <div class="fw-semibold">{{ $property->name }}</div>
                                        <div class="small section-muted">Submitted for review</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div>{{ $property->landlord->full_name }}</div>
                                <div class="small section-muted">{{ $property->landlord->email }}</div>
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $property->address }}</div>
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $property->created_at->format('M d, Y') }}</div>
                                <div class="small section-muted">{{ $property->created_at->format('h:i A') }}</div>
                            </td>
                            <td class="pe-3">
                                <div class="action-stack d-flex flex-column gap-2">
                                    <form method="POST" action="{{ route('admin.properties.approve', $property) }}" class="d-flex">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-success w-100">
                                            <i class="bi bi-check2 me-1"></i>Approve Property
                                        </button>
                                    </form>

                                    <form method="POST" action="{{ route('admin.properties.reject', $property) }}" class="d-flex gap-2">
                                        @csrf
                                        <input type="text" name="rejection_reason" class="form-control form-control-sm" placeholder="Reason (optional)" maxlength="500">
                                        <button type="submit" class="btn btn-sm btn-outline-danger text-nowrap">
                                            <i class="bi bi-x-lg me-1"></i>Reject
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="table-empty">
                                <div class="h6 mb-1"><i class="bi bi-check2-circle me-1"></i>No pending properties.</div>
                                <div>All submitted properties are already reviewed.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($properties->hasPages())
            <div class="admin-card-header border-top">
                {{ $properties->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
