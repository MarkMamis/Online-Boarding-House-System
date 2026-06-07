@extends('layouts.admin')

@section('title', 'Landlord Approval')

@section('content')
<style>
    .approval-shell {
        background: linear-gradient(180deg, #ffffff 0%, #fbfdfc 100%);
        border: 1px solid rgba(2, 8, 20, .08);
        border-radius: 1.25rem;
        box-shadow: 0 10px 26px rgba(2, 8, 20, .06);
        padding: 1.25rem;
    }
    .muted { color: rgba(2, 8, 20, .58); }
    .metric-tile {
        border: 1px solid rgba(2, 8, 20, .08);
        border-radius: 1rem;
        background: #fff;
        box-shadow: 0 8px 18px rgba(2, 8, 20, .05);
        padding: .95rem 1rem;
        height: 100%;
    }
    .metric-label {
        font-size: .75rem;
        text-transform: uppercase;
        letter-spacing: .05em;
        color: rgba(2, 8, 20, .55);
    }
    .metric-value {
        font-size: 1.45rem;
        font-weight: 700;
        color: #166534;
    }
    .tab-row {
        display: flex;
        flex-wrap: wrap;
        gap: .6rem;
        margin-bottom: 1rem;
    }
    .tab-link {
        display: inline-flex;
        align-items: center;
        gap: .45rem;
        border-radius: 999px;
        padding: .68rem 1rem;
        border: 1px solid rgba(2, 8, 20, .1);
        text-decoration: none;
        color: #334155;
        font-weight: 700;
        background: #fff;
    }
    .tab-link.active {
        background: #166534;
        color: #fff;
        border-color: #166534;
        box-shadow: 0 10px 22px rgba(22, 101, 52, .18);
    }
    .section-card {
        border: 1px solid rgba(2, 8, 20, .08);
        border-radius: 1rem;
        background: #fff;
        box-shadow: 0 8px 20px rgba(2, 8, 20, .05);
        overflow: hidden;
    }
    .filter-row {
        display: flex;
        flex-wrap: wrap;
        gap: .5rem;
        margin-bottom: 1rem;
    }
    .landlord-stack {
        display: grid;
        gap: 1rem;
    }
    .landlord-card {
        border: 1px solid rgba(2, 8, 20, .08);
        border-radius: 1rem;
        background: #fff;
        box-shadow: 0 8px 20px rgba(2, 8, 20, .05);
        overflow: hidden;
    }
    .landlord-header {
        padding: 1rem 1rem .9rem;
        border-bottom: 1px solid rgba(2, 8, 20, .08);
        background: linear-gradient(180deg, rgba(248, 250, 252, .96), #fff);
    }
    .landlord-meta {
        display: grid;
        gap: .8rem;
        grid-template-columns: minmax(0, 1.2fr) repeat(4, minmax(110px, 1fr));
        align-items: start;
    }
    .landlord-name {
        font-size: 1.02rem;
        font-weight: 700;
        color: #0f172a;
    }
    .meta-block-label {
        font-size: .74rem;
        text-transform: uppercase;
        letter-spacing: .05em;
        color: rgba(2, 8, 20, .55);
        margin-bottom: .2rem;
    }
    .meta-block-value {
        font-size: .93rem;
        color: #0f172a;
        font-weight: 600;
    }
    .meta-block-copy {
        font-size: .83rem;
        color: rgba(2, 8, 20, .62);
    }
    .status-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 999px;
        padding: .34rem .7rem;
        font-size: .76rem;
        font-weight: 700;
        text-transform: capitalize;
    }
    .status-missing {
        background: #e2e8f0;
        color: #475569;
    }
    .status-pending {
        background: #fef3c7;
        color: #92400e;
    }
    .status-approved {
        background: #dcfce7;
        color: #166534;
    }
    .status-rejected {
        background: #fee2e2;
        color: #991b1b;
    }
    .document-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 1rem;
        padding: 1rem;
    }
    .document-card {
        border: 1px solid rgba(2, 8, 20, .08);
        border-radius: .95rem;
        padding: .95rem;
        background: #fff;
    }
    .document-card-head {
        display: flex;
        justify-content: space-between;
        gap: .8rem;
        align-items: flex-start;
        margin-bottom: .8rem;
    }
    .document-title {
        font-weight: 700;
        color: #0f172a;
    }
    .document-actions {
        display: flex;
        flex-wrap: wrap;
        gap: .5rem;
        margin-top: .85rem;
    }
    .property-list {
        padding: 1rem;
        display: grid;
        gap: .85rem;
    }
    .property-card {
        border: 1px solid rgba(2, 8, 20, .08);
        border-radius: .95rem;
        padding: .95rem;
        background: #fff;
    }
    .property-card-top {
        display: flex;
        justify-content: space-between;
        gap: .8rem;
        align-items: flex-start;
        margin-bottom: .65rem;
    }
    .property-name {
        font-size: 1rem;
        font-weight: 700;
        color: #0f172a;
    }
    .property-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: .8rem;
        margin-bottom: .8rem;
    }
    .property-actions,
    .document-actions form {
        margin: 0;
    }
    .property-actions {
        display: flex;
        flex-wrap: wrap;
        gap: .5rem;
    }
    .empty-state {
        padding: 2rem 1rem;
        text-align: center;
        color: rgba(2, 8, 20, .58);
    }
    .rejection-copy {
        font-size: .82rem;
        color: #b91c1c;
        margin-top: .45rem;
    }
    @media (max-width: 991.98px) {
        .landlord-meta {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .document-grid,
        .property-grid {
            grid-template-columns: 1fr;
        }
    }
    @media (max-width: 767.98px) {
        .approval-shell {
            padding: .95rem;
        }

        .landlord-header,
        .document-grid,
        .property-list {
            padding: .85rem;
        }

        .landlord-meta {
            grid-template-columns: 1fr;
        }

        .property-card-top {
            flex-direction: column;
        }
    }
</style>

@php
    $currentCounts = $activeTab === 'properties' ? ($propertyCounts ?? []) : ($permitCounts ?? []);
    $currentTotal = ($landlords ?? null)?->total() ?? 0;
    $permitStatusOptions = ['all' => 'All', 'missing' => 'Missing', 'pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected'];
    $propertyStatusOptions = ['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected', 'all' => 'All'];

    $statusClass = function ($status) {
        return match ($status) {
            'approved' => 'status-badge status-approved',
            'rejected' => 'status-badge status-rejected',
            'pending' => 'status-badge status-pending',
            'missing', 'not_submitted' => 'status-badge status-missing',
            default => 'status-badge status-missing',
        };
    };
@endphp

<div class="approval-shell">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <div class="text-uppercase small muted fw-semibold">Approvals</div>
            <h1 class="h4 mb-1"><i class="bi bi-buildings me-2"></i>Landlord Approval</h1>
            <div class="muted small">Review landlord permits and property submissions from one grouped approval workspace.</div>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('admin.users.landlords') }}" class="btn btn-outline-secondary rounded-pill px-3">Landlords</a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger border-0 shadow-sm">{{ session('error') }}</div>
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

    <div class="tab-row">
        <a href="{{ route('admin.approvals.landlords', ['tab' => 'properties', 'status' => $activeTab === 'properties' ? $statusFilter : null]) }}" class="tab-link {{ $activeTab === 'properties' ? 'active' : '' }}">
            <i class="bi bi-building"></i>
            <span>Property Approval</span>
        </a>
        <a href="{{ route('admin.approvals.landlords', ['tab' => 'permits', 'status' => $activeTab === 'permits' ? $statusFilter : null]) }}" class="tab-link {{ $activeTab === 'permits' ? 'active' : '' }}">
            <i class="bi bi-file-earmark-check"></i>
            <span>Permit Approval</span>
        </a>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="metric-tile">
                <div class="metric-label">{{ $activeTab === 'permits' ? 'All Landlords' : 'Pending Properties' }}</div>
                <div class="metric-value">{{ number_format($currentCounts[$activeTab === 'permits' ? 'all' : 'pending'] ?? 0) }}</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="metric-tile">
                <div class="metric-label">{{ $activeTab === 'permits' ? 'Missing Docs' : 'Approved Properties' }}</div>
                <div class="metric-value">{{ number_format($currentCounts[$activeTab === 'permits' ? 'missing' : 'approved'] ?? 0) }}</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="metric-tile">
                <div class="metric-label">{{ $activeTab === 'permits' ? 'Pending Docs' : 'Rejected Properties' }}</div>
                <div class="metric-value">{{ number_format($currentCounts['pending'] ?? 0) }}</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="metric-tile">
                <div class="metric-label">Visible Landlords</div>
                <div class="metric-value">{{ number_format($currentTotal) }}</div>
            </div>
        </div>
    </div>

    <div class="filter-row">
        @foreach(($activeTab === 'permits' ? $permitStatusOptions : $propertyStatusOptions) as $value => $label)
            <a href="{{ route('admin.approvals.landlords', ['tab' => $activeTab, 'status' => $value]) }}" class="btn rounded-pill {{ $statusFilter === $value ? 'btn-success' : 'btn-outline-secondary' }}">{{ $label }}</a>
        @endforeach
    </div>

    <div class="landlord-stack">
        @forelse($landlords as $landlord)
            @php
                $profile = $landlord->landlordProfile;
                $businessStatus = !filled(optional($profile)->business_permit_path)
                    ? 'missing'
                    : (optional($profile)->business_permit_status ?: 'pending');
                $safetyStatus = !filled(optional($profile)->safety_certificate_path)
                    ? 'missing'
                    : ((property_exists($profile ?? new stdClass(), 'safety_certificate_status') && filled(optional($profile)->safety_certificate_status))
                        ? $profile->safety_certificate_status
                        : 'pending');
            @endphp

            <article class="landlord-card">
                <div class="landlord-header">
                    <div class="landlord-meta">
                        <div>
                            <div class="landlord-name">{{ $landlord->full_name }}</div>
                            <div class="meta-block-copy">{{ $landlord->email }}</div>
                            <div class="meta-block-copy">{{ $landlord->contact_number ?: 'No contact number provided' }}</div>
                        </div>
                        <div>
                            <div class="meta-block-label">Boarding House</div>
                            <div class="meta-block-value">{{ $landlord->boarding_house_name ?: optional($profile)->boarding_house_name ?: 'Not provided' }}</div>
                        </div>
                        <div>
                            <div class="meta-block-label">Properties</div>
                            <div class="meta-block-value">{{ number_format($landlord->properties_count ?? 0) }}</div>
                            @if($activeTab === 'properties')
                                <div class="meta-block-copy">Pending {{ number_format($landlord->pending_properties_count ?? 0) }} · Approved {{ number_format($landlord->approved_properties_count ?? 0) }} · Rejected {{ number_format($landlord->rejected_properties_count ?? 0) }}</div>
                            @endif
                        </div>
                        <div>
                            <div class="meta-block-label">Business Permit</div>
                            <span class="{{ $statusClass($businessStatus) }}">{{ str_replace('_', ' ', $businessStatus) }}</span>
                        </div>
                        <div>
                            <div class="meta-block-label">Safety Certificate</div>
                            <span class="{{ $statusClass($safetyStatus) }}">{{ str_replace('_', ' ', $safetyStatus) }}</span>
                        </div>
                    </div>
                </div>

                @if($activeTab === 'permits')
                    <div class="document-grid">
                        <section class="document-card">
                            <div class="document-card-head">
                                <div>
                                    <div class="document-title">Business Permit</div>
                                    <div class="meta-block-copy">Status and review actions for the uploaded business permit.</div>
                                </div>
                                <span class="{{ $statusClass($businessStatus) }}">{{ str_replace('_', ' ', $businessStatus) }}</span>
                            </div>

                            @if(filled(optional($profile)->business_permit_path))
                                <a href="{{ asset('storage/' . $profile->business_permit_path) }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary rounded-pill">
                                    <i class="bi bi-file-earmark-pdf me-1"></i>View File
                                </a>
                            @else
                                <div class="meta-block-copy">Missing business permit upload.</div>
                            @endif

                            <div class="mt-3">
                                <div class="meta-block-label">Last Review</div>
                                <div class="meta-block-copy">
                                    @if(!empty(optional($profile)->business_permit_reviewed_at))
                                        {{ $profile->business_permit_reviewed_at->format('M d, Y h:i A') }}
                                        @if(!empty(optional(optional($profile)->businessPermitReviewer)->full_name))
                                            · {{ $profile->businessPermitReviewer->full_name }}
                                        @endif
                                    @else
                                        Not reviewed
                                    @endif
                                </div>
                                @if($businessStatus === 'rejected' && filled(optional($profile)->business_permit_rejection_reason))
                                    <div class="rejection-copy">{{ $profile->business_permit_rejection_reason }}</div>
                                @endif
                            </div>

                            @if(filled(optional($profile)->business_permit_path) && $businessStatus !== 'approved')
                                <div class="document-actions">
                                    <button type="button" class="btn btn-success rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#approvePermitModal{{ $landlord->id }}">
                                        Approve
                                    </button>
                                    <button type="button" class="btn btn-outline-danger rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#rejectPermitModal{{ $landlord->id }}">
                                        Reject
                                    </button>
                                </div>
                            @endif
                        </section>

                        <section class="document-card">
                            <div class="document-card-head">
                                <div>
                                    <div class="document-title">Safety Certificate</div>
                                    <div class="meta-block-copy">Status and review actions for the uploaded safety certificate.</div>
                                </div>
                                <span class="{{ $statusClass($safetyStatus) }}">{{ str_replace('_', ' ', $safetyStatus) }}</span>
                            </div>

                            @if(filled(optional($profile)->safety_certificate_path))
                                <a href="{{ asset('storage/' . $profile->safety_certificate_path) }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary rounded-pill">
                                    <i class="bi bi-file-earmark-pdf me-1"></i>View File
                                </a>
                            @else
                                <div class="meta-block-copy">Missing safety certificate upload.</div>
                            @endif

                            <div class="mt-3">
                                <div class="meta-block-label">Last Review</div>
                                <div class="meta-block-copy">
                                    @if(!empty(optional($profile)->safety_certificate_reviewed_at))
                                        {{ $profile->safety_certificate_reviewed_at->format('M d, Y h:i A') }}
                                        @if(!empty(optional(optional($profile)->safetyCertificateReviewer)->full_name))
                                            · {{ $profile->safetyCertificateReviewer->full_name }}
                                        @endif
                                    @else
                                        Not reviewed
                                    @endif
                                </div>
                                @if($safetyStatus === 'rejected' && filled(optional($profile)->safety_certificate_rejection_reason))
                                    <div class="rejection-copy">{{ $profile->safety_certificate_rejection_reason }}</div>
                                @endif
                            </div>

                            @if(filled(optional($profile)->safety_certificate_path) && $safetyStatus !== 'approved')
                                <div class="document-actions">
                                    <button type="button" class="btn btn-success rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#approveSafetyModal{{ $landlord->id }}">
                                        Approve
                                    </button>
                                    <button type="button" class="btn btn-outline-danger rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#rejectSafetyModal{{ $landlord->id }}">
                                        Reject
                                    </button>
                                </div>
                            @endif
                        </section>
                    </div>
                @else
                    <div class="property-list">
                        @forelse($landlord->properties as $property)
                            @php
                                $propertyStatus = (string) ($property->approval_status ?? 'pending');
                                $reviewedAt = $property->approved_at ?? $property->rejected_at;
                                $reviewedAtValue = !empty($reviewedAt) ? \Illuminate\Support\Carbon::parse($reviewedAt) : null;
                            @endphp
                            <section class="property-card">
                                <div class="property-card-top">
                                    <div>
                                        <div class="property-name">{{ $property->name }}</div>
                                        <div class="meta-block-copy">{{ $property->address }}</div>
                                    </div>
                                    <span class="{{ $statusClass($propertyStatus) }}">{{ str_replace('_', ' ', $propertyStatus) }}</span>
                                </div>

                                <div class="property-grid">
                                    <div>
                                        <div class="meta-block-label">Submitted</div>
                                        <div class="meta-block-copy">{{ $property->created_at->format('M d, Y h:i A') }}</div>
                                    </div>
                                    <div>
                                        <div class="meta-block-label">Reviewed</div>
                                        <div class="meta-block-copy">{{ $reviewedAtValue ? $reviewedAtValue->format('M d, Y h:i A') : 'Not reviewed' }}</div>
                                    </div>
                                    <div>
                                        <div class="meta-block-label">Rejection Reason</div>
                                        <div class="meta-block-copy">{{ filled($property->rejection_reason) ? $property->rejection_reason : 'None' }}</div>
                                    </div>
                                </div>

                                <div class="property-actions">
                                    <a href="{{ route('admin.properties.show', $property) }}" class="btn btn-outline-secondary rounded-pill px-3">View</a>
                                    @if($propertyStatus !== 'approved')
                                        <button type="button" class="btn btn-success rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#approvePropertyModal{{ $property->id }}">Approve</button>
                                    @endif
                                    <button type="button" class="btn btn-outline-danger rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#rejectPropertyModal{{ $property->id }}">Reject</button>
                                </div>
                            </section>

                            <div class="modal fade" id="approvePropertyModal{{ $property->id }}" tabindex="-1" aria-labelledby="approvePropertyModalLabel{{ $property->id }}" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content rounded-4 border-0 shadow">
                                        <form method="POST" action="{{ route('admin.properties.approve', $property) }}">
                                            @csrf
                                            <div class="modal-header border-0 pb-0">
                                                <h5 class="modal-title" id="approvePropertyModalLabel{{ $property->id }}">Approve Property</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body pt-2">
                                                <div class="mb-2 fw-semibold">{{ $property->name }}</div>
                                                <div class="small muted mb-3">Landlord: {{ $landlord->full_name }}</div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" value="1" id="approve_property_confirm_{{ $property->id }}" required>
                                                    <label class="form-check-label small" for="approve_property_confirm_{{ $property->id }}">
                                                        I confirm this property is valid and ready to be published.
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="modal-footer border-0 pt-0">
                                                <button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-success rounded-pill px-3">Confirm Approval</button>
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
                                                <h5 class="modal-title" id="rejectPropertyModalLabel{{ $property->id }}">Reject Property</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body pt-2">
                                                <div class="mb-2 fw-semibold">{{ $property->name }}</div>
                                                <div class="small muted mb-3">Provide a reason to help the landlord correct the listing.</div>
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
                                                <button type="submit" class="btn btn-danger rounded-pill px-3">Confirm Rejection</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="empty-state">
                                <div class="fw-semibold mb-1">No properties match this filter for {{ $landlord->full_name }}.</div>
                                <div>Try switching status filters or wait for new submissions.</div>
                            </div>
                        @endforelse
                    </div>
                @endif
            </article>

            @if($activeTab === 'permits')
                <div class="modal fade" id="approvePermitModal{{ $landlord->id }}" tabindex="-1" aria-labelledby="approvePermitModalLabel{{ $landlord->id }}" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content rounded-4 border-0 shadow">
                            <form method="POST" action="{{ route('admin.permits.approve', $landlord) }}">
                                @csrf
                                <div class="modal-header border-0 pb-0">
                                    <h5 class="modal-title" id="approvePermitModalLabel{{ $landlord->id }}">Approve Business Permit</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body pt-2">
                                    <div class="mb-2 fw-semibold">{{ $landlord->full_name }}</div>
                                    <div class="small muted mb-3">Confirm approval for the uploaded business permit.</div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" value="1" id="approve_permit_confirm_{{ $landlord->id }}" required>
                                        <label class="form-check-label small" for="approve_permit_confirm_{{ $landlord->id }}">
                                            I confirm this business permit is valid and should be approved.
                                        </label>
                                    </div>
                                </div>
                                <div class="modal-footer border-0 pt-0">
                                    <button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-success rounded-pill px-3">Confirm Approval</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="modal fade" id="rejectPermitModal{{ $landlord->id }}" tabindex="-1" aria-labelledby="rejectPermitModalLabel{{ $landlord->id }}" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content rounded-4 border-0 shadow">
                            <form method="POST" action="{{ route('admin.permits.reject', $landlord) }}">
                                @csrf
                                <div class="modal-header border-0 pb-0">
                                    <h5 class="modal-title" id="rejectPermitModalLabel{{ $landlord->id }}">Reject Business Permit</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body pt-2">
                                    <div class="mb-2 fw-semibold">{{ $landlord->full_name }}</div>
                                    <div class="small muted mb-3">Provide a clear reason so the landlord can upload a corrected permit.</div>
                                    <div class="mb-3">
                                        <label for="permit_rejection_reason_{{ $landlord->id }}" class="form-label small">Rejection reason <span class="text-danger">*</span></label>
                                        <textarea id="permit_rejection_reason_{{ $landlord->id }}" name="rejection_reason" class="form-control" rows="3" maxlength="500" required></textarea>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" value="1" id="reject_permit_confirm_{{ $landlord->id }}" required>
                                        <label class="form-check-label small" for="reject_permit_confirm_{{ $landlord->id }}">
                                            I confirm this business permit should be rejected.
                                        </label>
                                    </div>
                                </div>
                                <div class="modal-footer border-0 pt-0">
                                    <button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-danger rounded-pill px-3">Confirm Rejection</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="modal fade" id="approveSafetyModal{{ $landlord->id }}" tabindex="-1" aria-labelledby="approveSafetyModalLabel{{ $landlord->id }}" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content rounded-4 border-0 shadow">
                            <form method="POST" action="{{ route('admin.permits.safety.approve', $landlord) }}">
                                @csrf
                                <div class="modal-header border-0 pb-0">
                                    <h5 class="modal-title" id="approveSafetyModalLabel{{ $landlord->id }}">Approve Safety Certificate</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body pt-2">
                                    <div class="mb-2 fw-semibold">{{ $landlord->full_name }}</div>
                                    <div class="small muted mb-3">Confirm approval for the uploaded safety certificate.</div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" value="1" id="approve_safety_confirm_{{ $landlord->id }}" required>
                                        <label class="form-check-label small" for="approve_safety_confirm_{{ $landlord->id }}">
                                            I confirm this safety certificate is valid and should be approved.
                                        </label>
                                    </div>
                                </div>
                                <div class="modal-footer border-0 pt-0">
                                    <button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-success rounded-pill px-3">Confirm Approval</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="modal fade" id="rejectSafetyModal{{ $landlord->id }}" tabindex="-1" aria-labelledby="rejectSafetyModalLabel{{ $landlord->id }}" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content rounded-4 border-0 shadow">
                            <form method="POST" action="{{ route('admin.permits.safety.reject', $landlord) }}">
                                @csrf
                                <div class="modal-header border-0 pb-0">
                                    <h5 class="modal-title" id="rejectSafetyModalLabel{{ $landlord->id }}">Reject Safety Certificate</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body pt-2">
                                    <div class="mb-2 fw-semibold">{{ $landlord->full_name }}</div>
                                    <div class="small muted mb-3">Provide a clear reason so the landlord can upload a corrected safety certificate.</div>
                                    <div class="mb-3">
                                        <label for="safety_rejection_reason_{{ $landlord->id }}" class="form-label small">Rejection reason <span class="text-danger">*</span></label>
                                        <textarea id="safety_rejection_reason_{{ $landlord->id }}" name="rejection_reason" class="form-control" rows="3" maxlength="500" required></textarea>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" value="1" id="reject_safety_confirm_{{ $landlord->id }}" required>
                                        <label class="form-check-label small" for="reject_safety_confirm_{{ $landlord->id }}">
                                            I confirm this safety certificate should be rejected.
                                        </label>
                                    </div>
                                </div>
                                <div class="modal-footer border-0 pt-0">
                                    <button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-danger rounded-pill px-3">Confirm Rejection</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @endif
        @empty
            <div class="section-card">
                <div class="empty-state">
                    <div class="h6 mb-1"><i class="bi bi-check2-circle me-1"></i>No landlords found for this filter.</div>
                    <div>Try changing the current filter or wait for new landlord records.</div>
                </div>
            </div>
        @endforelse
    </div>

    @if(($landlords ?? null) && $landlords->hasPages())
        <div class="section-card mt-3">
            <div class="p-3">
                {{ $landlords->links() }}
            </div>
        </div>
    @endif
</div>
@endsection
