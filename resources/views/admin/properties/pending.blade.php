@extends('layouts.admin')

@section('title', 'Property Approvals - Admin Panel')

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

    .actions-col {
        min-width: 220px;
    }

    .action-grid {
        display: grid;
        grid-template-columns: repeat(4, 42px);
        gap: .45rem;
        justify-content: start;
    }

    .action-btn {
        width: 42px;
        height: 42px;
        padding: 0;
        border-radius: .7rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
    }

    @media (max-width: 767.98px) {
        .admin-properties-shell {
            padding: .95rem;
        }

        .actions-col {
            min-width: 220px;
        }
    }
</style>

<div class="admin-properties-shell">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <div class="text-uppercase small section-muted fw-semibold">Management</div>
            <h1 class="h3 mb-1"><i class="bi bi-clock-history me-2"></i>Property Approval Queue</h1>
            <p class="section-muted mb-0">Review and manage pending, approved, and rejected property submissions.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('admin.properties.index') }}" class="btn btn-outline-secondary rounded-pill px-3">
                <i class="bi bi-list-ul me-1"></i>All Properties
            </a>
            <!-- <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary rounded-pill px-3">
                <i class="bi bi-arrow-left me-1"></i>Dashboard
            </a> -->
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="admin-metric">
                <div class="admin-metric-label">Pending</div>
                <div class="admin-metric-value">{{ number_format($counts['pending'] ?? 0) }}</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="admin-metric">
                <div class="admin-metric-label">Approved</div>
                <div class="admin-metric-value">{{ number_format($counts['approved'] ?? 0) }}</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="admin-metric">
                <div class="admin-metric-label">Rejected</div>
                <div class="admin-metric-value">{{ number_format($counts['rejected'] ?? 0) }}</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="admin-metric">
                <div class="admin-metric-label">Visible Records</div>
                <div class="admin-metric-value">{{ number_format($properties->total()) }}</div>
            </div>
        </div>
    </div>

    <div class="d-flex flex-wrap gap-2 mb-3">
        <a href="{{ route('admin.properties.pending', ['status' => 'pending']) }}" class="btn rounded-pill {{ $statusFilter === 'pending' ? 'btn-success' : 'btn-outline-secondary' }}">Pending</a>
        <a href="{{ route('admin.properties.pending', ['status' => 'approved']) }}" class="btn rounded-pill {{ $statusFilter === 'approved' ? 'btn-success' : 'btn-outline-secondary' }}">Approved</a>
        <a href="{{ route('admin.properties.pending', ['status' => 'rejected']) }}" class="btn rounded-pill {{ $statusFilter === 'rejected' ? 'btn-success' : 'btn-outline-secondary' }}">Rejected</a>
        <a href="{{ route('admin.properties.pending', ['status' => 'all']) }}" class="btn rounded-pill {{ $statusFilter === 'all' ? 'btn-success' : 'btn-outline-secondary' }}">All</a>
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

    @php
        $queueTitle = match($statusFilter) {
            'approved' => 'Approved Queue',
            'rejected' => 'Rejected Queue',
            'all' => 'All Property Submissions',
            default => 'Pending Queue',
        };
    @endphp

    <div class="admin-table-card">
        <div class="admin-card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div class="fw-semibold"><i class="bi bi-hourglass-split me-1"></i> {{ $queueTitle }}</div>
            <span class="badge {{ $statusFilter === 'approved' ? 'text-bg-success' : ($statusFilter === 'rejected' ? 'text-bg-danger' : ($statusFilter === 'all' ? 'text-bg-secondary' : 'text-bg-warning')) }}">{{ $properties->total() }} records</span>
        </div>

        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th class="ps-3">Property</th>
                        <th>Landlord</th>
                        <th>Address</th>
                        <th>Submitted</th>
                        <th>Status</th>
                        <th>Reviewed</th>
                        <th class="pe-3 actions-col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($properties as $property)
                        @php
                            $status = (string) ($property->approval_status ?? 'pending');
                            $statusClass = $status === 'approved'
                                ? 'text-bg-success'
                                : ($status === 'rejected' ? 'text-bg-danger' : 'text-bg-warning');
                            $reviewedAt = $property->approved_at ?? $property->rejected_at;
                            $reviewedAtValue = !empty($reviewedAt)
                                ? \Illuminate\Support\Carbon::parse($reviewedAt)
                                : null;
                        @endphp
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
                            <td>
                                <span class="badge {{ $statusClass }}">{{ str_replace('_', ' ', $status) }}</span>
                                @if($status === 'rejected' && !empty($property->rejection_reason))
                                    <div class="small text-danger mt-1">{{ $property->rejection_reason }}</div>
                                @endif
                            </td>
                            <td>
                                @if($reviewedAtValue)
                                    <div>{{ $reviewedAtValue->format('M d, Y') }}</div>
                                    <div class="small section-muted">{{ $reviewedAtValue->format('h:i A') }}</div>
                                @else
                                    <span class="small section-muted">Not reviewed</span>
                                @endif
                            </td>
                            <td class="pe-3 actions-col">
                                <div class="action-grid">
                                    @if($status === 'approved')
                                        <span class="btn btn-sm btn-success action-btn disabled" aria-disabled="true" title="Already approved">
                                            <i class="bi bi-check2-circle"></i>
                                            <span class="visually-hidden">Already approved</span>
                                        </span>
                                    @else
                                        <button type="button" class="btn btn-sm btn-success action-btn" data-bs-toggle="modal" data-bs-target="#approvePropertyModal{{ $property->id }}" title="Approve Property" aria-label="Approve Property">
                                            <i class="bi bi-check2"></i>
                                        </button>
                                    @endif

                                    <button type="button" class="btn btn-sm btn-outline-danger action-btn" data-bs-toggle="modal" data-bs-target="#rejectPropertyModal{{ $property->id }}" title="Reject Property" aria-label="Reject Property">
                                        <i class="bi bi-x-lg"></i>
                                    </button>

                                    <a href="{{ route('admin.properties.show', $property) }}" class="btn btn-sm btn-outline-secondary action-btn" title="View Property Details" aria-label="View Property Details">
                                        <i class="bi bi-eye"></i>
                                    </a>

                                    <a href="{{ route('admin.users.landlords.show', $property->landlord) }}" class="btn btn-sm btn-outline-secondary action-btn" title="View Landlord" aria-label="View Landlord">
                                        <i class="bi bi-person"></i>
                                    </a>
                                </div>

                                <div class="modal fade" id="approvePropertyModal{{ $property->id }}" tabindex="-1" aria-labelledby="approvePropertyModalLabel{{ $property->id }}" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content rounded-4 border-0 shadow">
                                            <form method="POST" action="{{ route('admin.properties.approve', $property) }}">
                                                @csrf
                                                <div class="modal-header border-0 pb-0">
                                                    <h5 class="modal-title" id="approvePropertyModalLabel{{ $property->id }}">
                                                        <i class="bi bi-check2-circle text-success me-2"></i>Approve Property
                                                    </h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body pt-2">
                                                    <div class="mb-2 fw-semibold">{{ $property->name }}</div>
                                                    <div class="small section-muted mb-3">Landlord: {{ $property->landlord->full_name }}</div>

                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" value="1" id="approve_property_confirm_{{ $property->id }}" required>
                                                        <label class="form-check-label small" for="approve_property_confirm_{{ $property->id }}">
                                                            I confirm this property is valid and ready to be published.
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="modal-footer border-0 pt-0">
                                                    <button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-success rounded-pill px-3">
                                                        <i class="bi bi-check2 me-1"></i>Confirm Approval
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                <div class="modal fade" id="rejectPropertyModal{{ $property->id }}" tabindex="-1" aria-labelledby="rejectPropertyModalLabel{{ $property->id }}" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content rounded-4 border-0 shadow">
                                            <form method="POST" action="{{ route('admin.properties.reject', $property) }}">
                                                @csrf
                                                <div class="modal-header border-0 pb-0">
                                                    <h5 class="modal-title" id="rejectPropertyModalLabel{{ $property->id }}">
                                                        <i class="bi bi-x-octagon text-danger me-2"></i>Reject Property
                                                    </h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body pt-2">
                                                    <div class="mb-2 fw-semibold">{{ $property->name }}</div>
                                                    <div class="small section-muted mb-3">Provide a reason to help the landlord correct the listing (optional).</div>

                                                    <div class="mb-3">
                                                        <label for="rejection_reason_{{ $property->id }}" class="form-label small">Rejection reason <span class="text-muted">(Optional)</span></label>
                                                        <textarea id="rejection_reason_{{ $property->id }}" name="rejection_reason" class="form-control" rows="3" maxlength="500" placeholder="Explain what needs to be corrected."></textarea>
                                                    </div>

                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" value="1" id="reject_property_confirm_{{ $property->id }}" required>
                                                        <label class="form-check-label small" for="reject_property_confirm_{{ $property->id }}">
                                                            I confirm this property should be rejected.
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="modal-footer border-0 pt-0">
                                                    <button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-danger rounded-pill px-3">
                                                        <i class="bi bi-x-lg me-1"></i>Confirm Rejection
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="table-empty">
                                <div class="h6 mb-1"><i class="bi bi-check2-circle me-1"></i>No properties found for this filter.</div>
                                <div>Try switching filters or wait for new submissions.</div>
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
