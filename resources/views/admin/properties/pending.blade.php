@extends('layouts.admin')

@section('title', 'Property Reviews - Admin Panel')

@section('content')
<div class="admin-page-shell">
    <x-admin.page-header
        title="Property Reviews"
        subtitle="Review boarding house submissions requiring administrative approval."
        icon="bi-shield-check"
        :breadcrumb="[
            ['label' => 'Admin', 'url' => route('admin.dashboard')],
            ['label' => 'Management'],
            ['label' => 'Property Reviews']
        ]"
    />

    {{-- Metric Strip --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <x-admin.metric-card
                label="Pending Review"
                :value="number_format($counts['pending'] ?? 0)"
                variant="{{ ($counts['pending'] ?? 0) > 0 ? 'warning' : 'success' }}"
                icon="bi-hourglass-split"
            />
        </div>
        <div class="col-6 col-lg-3">
            <x-admin.metric-card
                label="Approved"
                :value="number_format($counts['approved'] ?? 0)"
                variant="success"
                icon="bi-check2-circle"
            />
        </div>
        <div class="col-6 col-lg-3">
            <x-admin.metric-card
                label="Rejected"
                :value="number_format($counts['rejected'] ?? 0)"
                variant="danger"
                icon="bi-x-octagon"
            />
        </div>
        <div class="col-6 col-lg-3">
            <x-admin.metric-card
                label="Total Submissions"
                :value="number_format($counts['all'] ?? $properties->total())"
                variant="neutral"
                icon="bi-collection"
            />
        </div>
    </div>

    {{-- Filter Segmented Tabs --}}
    <div class="d-flex flex-wrap gap-2 mb-3 align-items-center">
        <div class="d-flex flex-wrap gap-1">
            <a href="{{ route('admin.property_reviews.index', ['status' => 'pending']) }}" class="filter-chip {{ $statusFilter === 'pending' ? 'active' : '' }}">
                <i class="bi bi-hourglass-split"></i> Pending Review
                @if(($counts['pending'] ?? 0) > 0)
                    <span class="badge rounded-pill bg-warning text-dark ms-1">{{ $counts['pending'] }}</span>
                @endif
            </a>
            <a href="{{ route('admin.property_reviews.index', ['status' => 'approved']) }}" class="filter-chip {{ $statusFilter === 'approved' ? 'active' : '' }}">
                <i class="bi bi-check2-circle"></i> Approved
            </a>
            <a href="{{ route('admin.property_reviews.index', ['status' => 'rejected']) }}" class="filter-chip {{ $statusFilter === 'rejected' ? 'active' : '' }}">
                <i class="bi bi-x-octagon"></i> Rejected
            </a>
            <a href="{{ route('admin.property_reviews.index', ['status' => 'all']) }}" class="filter-chip {{ $statusFilter === 'all' ? 'active' : '' }}">
                <i class="bi bi-list-ul"></i> All Submissions
            </a>
            <a href="{{ route('admin.property_reviews.index', ['status' => 'draft']) }}" class="filter-chip {{ $statusFilter === 'draft' ? 'active' : '' }}">
                <i class="bi bi-file-earmark"></i> Drafts
                @if(($counts['draft'] ?? 0) > 0)
                    <span class="badge rounded-pill bg-light text-secondary border ms-1">{{ $counts['draft'] }}</span>
                @endif
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm py-2 mb-3 d-flex align-items-center gap-2">
            <i class="bi bi-check-circle-fill"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger border-0 shadow-sm py-2 mb-3">
            <ul class="mb-0 ps-3">
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
            'draft' => 'Draft Properties (Not Submitted)',
            'all' => 'All Property Submissions',
            default => 'Pending Review Queue',
        };
    @endphp

    <div class="admin-table-card">
        <div class="admin-card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div class="fw-bold d-flex align-items-center gap-2">
                <i class="bi bi-inbox text-success"></i>
                <span>{{ $queueTitle }}</span>
            </div>
            <span class="badge rounded-pill bg-light text-secondary border">{{ $properties->total() }} records</span>
        </div>

        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th class="ps-3">Property</th>
                        <th>Landlord</th>
                        <th>Address</th>
                        <th>{{ $statusFilter === 'draft' ? 'Last Saved' : 'Submitted' }}</th>
                        <th>Status</th>
                        <th>Reviewed</th>
                        <th class="pe-3 text-end" style="min-width: 180px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($properties as $property)
                        @php
                            $status = (string) ($property->approval_status ?? 'pending');
                            $reviewedAt = $property->approved_at ?? $property->rejected_at;
                            $reviewedAtValue = !empty($reviewedAt)
                                ? \Illuminate\Support\Carbon::parse($reviewedAt)
                                : null;
                        @endphp
                        <tr>
                            <td class="ps-3">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="admin-avatar">
                                        <i class="bi bi-building"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark">{{ $property->name }}</div>
                                        @if($status === 'draft')
                                            <div class="small text-muted">Not yet submitted</div>
                                        @else
                                            <div class="small text-muted">Submitted for review</div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark">{{ $property->landlord?->full_name ?? $property->landlord?->name ?? '—' }}</div>
                                <div class="small text-muted">{{ $property->landlord?->email ?? '—' }}</div>
                            </td>
                            <td>
                                <div class="text-secondary small">{{ $property->address ?: 'Address not set' }}</div>
                            </td>
                            <td>
                                @if($status === 'draft')
                                    <div class="fw-semibold">{{ ($property->updated_at ?? $property->created_at)->format('M d, Y') }}</div>
                                    <div class="small text-muted">Saved {{ ($property->updated_at ?? $property->created_at)->format('h:i A') }}</div>
                                @elseif($property->submitted_at)
                                    <div class="fw-semibold">{{ $property->submitted_at->format('M d, Y') }}</div>
                                    <div class="small text-muted">{{ $property->submitted_at->format('h:i A') }}</div>
                                @else
                                    <div class="fw-semibold">{{ $property->created_at->format('M d, Y') }}</div>
                                    <div class="small text-muted">{{ $property->created_at->format('h:i A') }}</div>
                                @endif
                            </td>
                            <td>
                                <x-admin.status-badge :status="$status" />
                                @if($status === 'rejected' && !empty($property->rejection_reason))
                                    <div class="small text-danger mt-1" style="max-width: 200px;">{{ $property->rejection_reason }}</div>
                                @endif
                            </td>
                            <td>
                                @if($status === 'draft')
                                    <span class="small text-muted">—</span>
                                @elseif($reviewedAtValue)
                                    <div>{{ $reviewedAtValue->format('M d, Y') }}</div>
                                    <div class="small text-muted">{{ $reviewedAtValue->format('h:i A') }}</div>
                                @else
                                    <span class="small text-muted">Not reviewed</span>
                                @endif
                            </td>
                            <td class="pe-3 text-end">
                                <div class="d-inline-flex align-items-center gap-1">
                                    @if($status === 'draft')
                                        <span class="badge rounded-pill bg-light text-secondary border px-2 py-1 small me-1">
                                            Draft Only
                                        </span>
                                    @elseif($status === 'approved')
                                        <span class="btn btn-sm btn-light border btn-action-icon text-muted disabled" aria-disabled="true" title="Already approved">
                                            <i class="bi bi-check2-circle"></i>
                                        </span>
                                    @else
                                        <button type="button" class="btn btn-sm btn-brand btn-action-icon" data-bs-toggle="modal" data-bs-target="#approvePropertyModal{{ $property->id }}" title="Approve Property" aria-label="Approve Property">
                                            <i class="bi bi-check2"></i>
                                        </button>
                                    @endif

                                    @if($status !== 'draft')
                                        <button type="button" class="btn btn-sm btn-outline-danger btn-action-icon" data-bs-toggle="modal" data-bs-target="#rejectPropertyModal{{ $property->id }}" title="Reject Property" aria-label="Reject Property">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                    @endif

                                    <a href="{{ route('admin.properties.show', $property) }}" class="btn btn-sm btn-light border btn-action-icon text-secondary" title="View Property Details" aria-label="View Property Details">
                                        <i class="bi bi-eye"></i>
                                    </a>

                                    @if($property->landlord)
                                        <a href="{{ route('admin.users.landlords.show', $property->landlord) }}" class="btn btn-sm btn-light border btn-action-icon text-secondary" title="View Landlord" aria-label="View Landlord">
                                            <i class="bi bi-person"></i>
                                        </a>
                                    @endif
                                </div>

                                @if($status !== 'draft')
                                    {{-- Approve Modal --}}
                                    <div class="modal fade" id="approvePropertyModal{{ $property->id }}" tabindex="-1" aria-labelledby="approvePropertyModalLabel{{ $property->id }}" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content rounded-4 border-0 shadow">
                                                <form method="POST" action="{{ route('admin.properties.approve', $property) }}">
                                                    @csrf
                                                    <div class="modal-header border-0 pb-0">
                                                        <h5 class="modal-title fw-bold" id="approvePropertyModalLabel{{ $property->id }}">
                                                            <i class="bi bi-check2-circle text-success me-2"></i>Approve Property
                                                        </h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body pt-2 text-start">
                                                        <div class="mb-2 fw-semibold">{{ $property->name }}</div>
                                                        <div class="small text-muted mb-3">Landlord: {{ $property->landlord?->full_name ?? $property->landlord?->name ?? '—' }}</div>

                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" value="1" id="approve_property_confirm_{{ $property->id }}" required>
                                                            <label class="form-check-label small" for="approve_property_confirm_{{ $property->id }}">
                                                                I confirm this property is valid and ready to be published on OBHS.
                                                            </label>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer border-0 pt-0">
                                                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-sm btn-brand px-3">
                                                            <i class="bi bi-check2 me-1"></i>Confirm Approval
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Reject Modal --}}
                                    <div class="modal fade" id="rejectPropertyModal{{ $property->id }}" tabindex="-1" aria-labelledby="rejectPropertyModalLabel{{ $property->id }}" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content rounded-4 border-0 shadow">
                                                <form method="POST" action="{{ route('admin.properties.reject', $property) }}">
                                                    @csrf
                                                    <div class="modal-header border-0 pb-0">
                                                        <h5 class="modal-title fw-bold" id="rejectPropertyModalLabel{{ $property->id }}">
                                                            <i class="bi bi-x-octagon text-danger me-2"></i>Reject Property
                                                        </h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body pt-2 text-start">
                                                        <div class="mb-2 fw-semibold">{{ $property->name }}</div>
                                                        <div class="small text-muted mb-3">Provide a reason to help the landlord correct the listing (optional).</div>

                                                        <div class="mb-3">
                                                            <label for="rejection_reason_{{ $property->id }}" class="form-label small fw-semibold">Rejection reason <span class="text-muted">(Optional)</span></label>
                                                            <textarea id="rejection_reason_{{ $property->id }}" name="rejection_reason" class="form-control" rows="3" maxlength="500" placeholder="Explain what needs to be corrected."></textarea>
                                                        </div>

                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" value="1" id="reject_property_confirm_{{ $property->id }}" required>
                                                            <label class="form-check-label small" for="reject_property_confirm_{{ $property->id }}">
                                                                I confirm this property listing should be rejected.
                                                            </label>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer border-0 pt-0">
                                                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-sm btn-danger px-3">
                                                            <i class="bi bi-x-lg me-1"></i>Confirm Rejection
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <x-admin.empty-state
                                    icon="bi-shield-check"
                                    title="No properties found for this queue"
                                    message="There are no property listings currently matching the selected review status filter."
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($properties->hasPages())
            <div class="admin-card-header border-top d-flex justify-content-center">
                {{ $properties->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

