@extends('layouts.admin')

@section('title', 'Landlord Approval')

@section('content')
<style>
    /* ── Shared utilities ── */
    .la-muted  { color: rgba(2,8,20,.52); }
    .la-label  { font-size:.72rem; text-transform:uppercase; letter-spacing:.05em; color:rgba(2,8,20,.45); }

    /* ── Tab row ── */
    .la-tabs   { display:flex; flex-wrap:wrap; gap:.5rem; margin-bottom:1.1rem; }
    .la-tab    { display:inline-flex; align-items:center; gap:.4rem; border-radius:999px;
                 padding:.55rem .95rem; border:1px solid rgba(2,8,20,.12); text-decoration:none;
                 color:#334155; font-weight:600; font-size:.88rem; background:#fff; }
    .la-tab.active { background:#166534; color:#fff; border-color:#166534; }

    /* ── Filter pills ── */
    .la-filters { display:flex; flex-wrap:wrap; gap:.4rem; margin-bottom:1rem; }
    .la-filter  { border-radius:999px; padding:.38rem .85rem; font-size:.82rem; font-weight:600;
                  border:1px solid rgba(2,8,20,.14); text-decoration:none; color:#475569; background:#fff; }
    .la-filter.active { background:#166534; color:#fff; border-color:#166534; }

    /* ── Status badges ── */
    .sbadge     { display:inline-flex; align-items:center; border-radius:999px;
                  padding:.25rem .62rem; font-size:.73rem; font-weight:700; white-space:nowrap; }
    .sb-missing { background:#e2e8f0; color:#475569; }
    .sb-pending { background:#fef3c7; color:#92400e; }
    .sb-approved{ background:#dcfce7; color:#166534; }
    .sb-rejected{ background:#fee2e2; color:#991b1b; }

    /* ── Landlord card ── */
    .ll-card    { background:#fff; border:1px solid rgba(2,8,20,.08); border-radius:.85rem;
                  margin-bottom:.85rem; overflow:hidden; }
    .ll-head    { padding:.75rem 1rem; border-bottom:1px solid rgba(2,8,20,.07);
                  background:rgba(248,250,252,.8); display:flex; flex-wrap:wrap;
                  align-items:center; justify-content:space-between; gap:.6rem; }
    .ll-name    { font-weight:700; font-size:.97rem; color:#0f172a; }
    .ll-meta    { font-size:.82rem; color:rgba(2,8,20,.55); }

    /* ── Property table ── */
    .pt-wrap    { overflow-x:auto; }
    .pt-table   { width:100%; border-collapse:collapse; font-size:.85rem; }
    .pt-table th{ padding:.55rem .85rem; font-size:.7rem; text-transform:uppercase;
                  letter-spacing:.05em; color:rgba(2,8,20,.45); font-weight:600;
                  border-bottom:1px solid rgba(2,8,20,.08); white-space:nowrap; }
    .pt-table td{ padding:.6rem .85rem; border-bottom:1px solid rgba(2,8,20,.06);
                  vertical-align:middle; }
    .pt-table tr:last-child td { border-bottom:none; }
    .pt-table tr:hover td { background:rgba(22,101,52,.025); }
    .pt-prop-name  { font-weight:600; color:#0f172a; }
    .pt-prop-addr  { color:rgba(2,8,20,.52); font-size:.8rem; }
    .pt-prop-date  { white-space:nowrap; color:rgba(2,8,20,.52); }
    .pt-actions    { white-space:nowrap; }
    .pt-actions .btn { font-size:.78rem; padding:.28rem .65rem; }

    /* ── Document rows (permit tab) ── */
    .doc-table      { width:100%; border-collapse:collapse; font-size:.85rem; }
    .doc-table td   { padding:.65rem .9rem; border-bottom:1px solid rgba(2,8,20,.06);
                      vertical-align:middle; }
    .doc-table tr:last-child td { border-bottom:none; }
    .doc-table .doc-name { font-weight:600; color:#0f172a; width:160px; }
    .doc-table .doc-status{ width:110px; }
    .doc-table .doc-file  { color:rgba(2,8,20,.52); font-size:.8rem; }
    .doc-table .doc-acts  { white-space:nowrap; text-align:right; }
    .doc-table .doc-acts .btn { font-size:.78rem; padding:.28rem .65rem; }
    .doc-last-review { font-size:.76rem; color:rgba(2,8,20,.45); }
    .doc-reject-reason { font-size:.76rem; color:#b91c1c; }

    /* ── Empty states ── */
    .la-empty-page { background:#fff; border:1px solid rgba(2,8,20,.08); border-radius:.85rem;
                     padding:2.5rem 1rem; text-align:center; color:rgba(2,8,20,.52); }
    .la-empty-row  { padding:.65rem .85rem; color:rgba(2,8,20,.45); font-size:.83rem;
                     font-style:italic; }

    /* ── Summary chip ── */
    .ll-prop-summary { font-size:.78rem; color:rgba(2,8,20,.5); }

    /* ── Pagination wrapper ── */
    .la-pagination { background:#fff; border:1px solid rgba(2,8,20,.08);
                     border-radius:.85rem; padding:.75rem 1rem; margin-top:.75rem; }

    @media(max-width:767.98px){
        .ll-head { flex-direction:column; align-items:flex-start; }
        .pt-table th, .pt-table td { padding:.5rem .6rem; }
        .doc-table .doc-name { width:auto; }
        .doc-table td { padding:.5rem .6rem; }
    }
</style>

@php
    $permitStatusOptions   = ['all'=>'All','missing'=>'Missing','pending'=>'Pending','approved'=>'Approved','rejected'=>'Rejected'];
    $propertyStatusOptions = ['pending'=>'Pending','approved'=>'Approved','rejected'=>'Rejected','all'=>'All'];

    $sbClass = function($status) {
        return match($status) {
            'approved'                    => 'sbadge sb-approved',
            'rejected'                    => 'sbadge sb-rejected',
            'pending'                     => 'sbadge sb-pending',
            'missing','not_submitted',''  => 'sbadge sb-missing',
            default                       => 'sbadge sb-missing',
        };
    };
@endphp

<div class="mb-4 d-flex flex-wrap justify-content-between align-items-start gap-2">
    <div>
        <div class="text-uppercase small la-muted fw-semibold mb-1">Approvals</div>
        <h1 class="h5 mb-0 fw-bold"><i class="bi bi-buildings me-1"></i> Landlord Approval</h1>
    </div>
    <a href="{{ route('admin.users.landlords') }}" class="btn btn-sm btn-outline-secondary rounded-pill">
        <i class="bi bi-people me-1"></i>Landlords
    </a>
</div>

{{-- Flash messages --}}
@if(session('success'))
    <div class="alert alert-success border-0 shadow-sm py-2 mb-3">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-danger border-0 shadow-sm py-2 mb-3">{{ session('error') }}</div>
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

{{-- Tabs --}}
<div class="la-tabs">
    <a href="{{ route('admin.approvals.landlords', ['tab' => 'properties']) }}"
       class="la-tab {{ $activeTab === 'properties' ? 'active' : '' }}">
        <i class="bi bi-building"></i> Property Approval
    </a>
    <a href="{{ route('admin.approvals.landlords', ['tab' => 'permits']) }}"
       class="la-tab {{ $activeTab === 'permits' ? 'active' : '' }}">
        <i class="bi bi-file-earmark-check"></i> Permit Approval
    </a>
</div>

{{-- Status filters --}}
<div class="la-filters">
    @foreach(($activeTab === 'permits' ? $permitStatusOptions : $propertyStatusOptions) as $value => $label)
        <a href="{{ route('admin.approvals.landlords', ['tab' => $activeTab, 'status' => $value]) }}"
           class="la-filter {{ $statusFilter === $value ? 'active' : '' }}">{{ $label }}</a>
    @endforeach
</div>

{{-- ══════════════════════════════════════════════════════
     LANDLORD LIST
     ═════════════════════════════════════════════════════= --}}
@forelse($landlords as $landlord)
    @php
        $profile = $landlord->landlordProfile;

        // Compute document statuses (used on both tabs)
        $businessStatus = !filled(optional($profile)->business_permit_path)
            ? 'missing'
            : (optional($profile)->business_permit_status ?: 'pending');

        $safetyStatus = !filled(optional($profile)->safety_certificate_path)
            ? 'missing'
            : ((property_exists($profile ?? new stdClass(), 'safety_certificate_status') && filled(optional($profile)->safety_certificate_status))
                ? $profile->safety_certificate_status
                : 'pending');

        $boardingHouseName = $landlord->boarding_house_name
            ?: optional($profile)->boarding_house_name
            ?: null;

        // For property tab: skip landlords with 0 matching properties when filter != 'all'
        $propertyList = $landlord->properties ?? collect();
        $skipLandlord = ($activeTab === 'properties' && $statusFilter !== 'all' && $propertyList->count() === 0);
    @endphp

    @if($skipLandlord)
        @continue
    @endif

    <div class="ll-card">
        {{-- ── Landlord header ── --}}
        <div class="ll-head">
            <div>
                <div class="ll-name">{{ $landlord->full_name }}</div>
                <div class="ll-meta">
                    {{ $landlord->email }}
                    @if($landlord->contact_number)
                        &nbsp;·&nbsp;{{ $landlord->contact_number }}
                    @endif
                    @if($boardingHouseName)
                        &nbsp;·&nbsp;{{ $boardingHouseName }}
                    @endif
                </div>
            </div>

            @if($activeTab === 'properties')
                <div class="ll-prop-summary">
                    <span class="sbadge sb-pending me-1">{{ $landlord->pending_properties_count ?? 0 }} Pending</span>
                    <span class="sbadge sb-approved me-1">{{ $landlord->approved_properties_count ?? 0 }} Approved</span>
                    <span class="sbadge sb-rejected">{{ $landlord->rejected_properties_count ?? 0 }} Rejected</span>
                </div>
            @else
                {{-- Permit tab: show quick doc status badges in header --}}
                <div class="d-flex gap-2 flex-wrap">
                    <span class="la-label me-1 align-self-center d-none d-sm-inline">Permit</span>
                    <span class="{{ $sbClass($businessStatus) }}">{{ ucfirst($businessStatus) }}</span>
                    <span class="la-label me-1 align-self-center d-none d-sm-inline">Safety</span>
                    <span class="{{ $sbClass($safetyStatus) }}">{{ ucfirst($safetyStatus) }}</span>
                </div>
            @endif
        </div>

        {{-- ══ PROPERTY TAB BODY ══ --}}
        @if($activeTab === 'properties')
            @if($propertyList->count() > 0)
                <div class="pt-wrap">
                    <table class="pt-table">
                        <thead>
                            <tr>
                                <th>Property</th>
                                <th>Status</th>
                                <th>Submitted</th>
                                <th>Rejection Reason</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($propertyList as $property)
                                @php
                                    $propertyStatus = (string)($property->approval_status ?? 'pending');
                                @endphp
                                <tr>
                                    <td>
                                        <div class="pt-prop-name">{{ $property->name }}</div>
                                        <div class="pt-prop-addr">{{ $property->address }}</div>
                                    </td>
                                    <td>
                                        <span class="{{ $sbClass($propertyStatus) }}">{{ ucfirst(str_replace('_',' ',$propertyStatus)) }}</span>
                                    </td>
                                    <td class="pt-prop-date">
                                        {{ $property->created_at->format('M d, Y') }}
                                    </td>
                                    <td class="la-muted" style="font-size:.8rem;max-width:200px;">
                                        {{ filled($property->rejection_reason) ? $property->rejection_reason : '—' }}
                                    </td>
                                    <td class="pt-actions text-end">
                                        <a href="{{ route('admin.properties.show', $property) }}"
                                           class="btn btn-sm btn-outline-secondary rounded-pill">View</a>
                                        @if($propertyStatus !== 'approved')
                                            <button type="button" class="btn btn-sm btn-success rounded-pill"
                                                data-bs-toggle="modal"
                                                data-bs-target="#approvePropertyModal{{ $property->id }}">Approve</button>
                                        @endif
                                        <button type="button" class="btn btn-sm btn-outline-danger rounded-pill"
                                            data-bs-toggle="modal"
                                            data-bs-target="#rejectPropertyModal{{ $property->id }}">Reject</button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Property modals --}}
                @foreach($propertyList as $property)
                    @php $propertyStatus = (string)($property->approval_status ?? 'pending'); @endphp

                    {{-- Approve modal --}}
                    <div class="modal fade" id="approvePropertyModal{{ $property->id }}" tabindex="-1"
                         aria-labelledby="approvePropertyModalLabel{{ $property->id }}" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content rounded-4 border-0 shadow">
                                <form method="POST" action="{{ route('admin.properties.approve', $property) }}">
                                    @csrf
                                    <div class="modal-header border-0 pb-0">
                                        <h5 class="modal-title" id="approvePropertyModalLabel{{ $property->id }}">Approve Property</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body pt-2">
                                        <div class="mb-1 fw-semibold">{{ $property->name }}</div>
                                        <div class="small la-muted mb-3">Landlord: {{ $landlord->full_name }}</div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" value="1"
                                                   id="approve_property_confirm_{{ $property->id }}" required>
                                            <label class="form-check-label small"
                                                   for="approve_property_confirm_{{ $property->id }}">
                                                I confirm this property is valid and ready to be published.
                                            </label>
                                        </div>
                                    </div>
                                    <div class="modal-footer border-0 pt-0">
                                        <button type="button" class="btn btn-outline-secondary rounded-pill"
                                                data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-success rounded-pill px-3">Confirm Approval</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    {{-- Reject modal --}}
                    <div class="modal fade" id="rejectPropertyModal{{ $property->id }}" tabindex="-1"
                         aria-labelledby="rejectPropertyModalLabel{{ $property->id }}" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content rounded-4 border-0 shadow">
                                <form method="POST" action="{{ route('admin.properties.reject', $property) }}">
                                    @csrf
                                    <div class="modal-header border-0 pb-0">
                                        <h5 class="modal-title" id="rejectPropertyModalLabel{{ $property->id }}">Reject Property</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body pt-2">
                                        <div class="mb-1 fw-semibold">{{ $property->name }}</div>
                                        <div class="mb-3">
                                            <label for="rejection_reason_{{ $property->id }}" class="form-label small">
                                                Rejection reason <span class="text-muted">(Optional)</span>
                                            </label>
                                            <textarea id="rejection_reason_{{ $property->id }}" name="rejection_reason"
                                                      class="form-control" rows="3" maxlength="500"
                                                      placeholder="Explain what needs to be corrected."></textarea>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" value="1"
                                                   id="reject_property_confirm_{{ $property->id }}" required>
                                            <label class="form-check-label small"
                                                   for="reject_property_confirm_{{ $property->id }}">
                                                I confirm this property should be rejected.
                                            </label>
                                        </div>
                                    </div>
                                    <div class="modal-footer border-0 pt-0">
                                        <button type="button" class="btn btn-outline-secondary rounded-pill"
                                                data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-danger rounded-pill px-3">Confirm Rejection</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach

            @else
                <div class="la-empty-row">No properties submitted.</div>
            @endif

        {{-- ══ PERMIT TAB BODY ══ --}}
        @else
            <table class="doc-table">
                <tbody>
                    {{-- ── Business Permit row ── --}}
                    <tr>
                        <td class="doc-name"><i class="bi bi-file-earmark-text me-1 la-muted"></i>Business Permit</td>
                        <td class="doc-status">
                            <span class="{{ $sbClass($businessStatus) }}">{{ ucfirst($businessStatus) }}</span>
                        </td>
                        <td class="doc-file">
                            @if(filled(optional($profile)->business_permit_path))
                                <a href="{{ asset('storage/' . $profile->business_permit_path) }}"
                                   target="_blank" rel="noopener"
                                   class="btn btn-sm btn-outline-secondary rounded-pill me-1">
                                    <i class="bi bi-file-earmark-pdf me-1"></i>View
                                </a>
                            @else
                                <span class="la-muted">No file uploaded</span>
                            @endif
                            @if(!empty(optional($profile)->business_permit_reviewed_at))
                                <div class="doc-last-review mt-1">
                                    Reviewed {{ $profile->business_permit_reviewed_at->format('M d, Y') }}
                                    @if(!empty(optional(optional($profile)->businessPermitReviewer)->full_name))
                                        · {{ $profile->businessPermitReviewer->full_name }}
                                    @endif
                                </div>
                            @endif
                            @if($businessStatus === 'rejected' && filled(optional($profile)->business_permit_rejection_reason))
                                <div class="doc-reject-reason">{{ $profile->business_permit_rejection_reason }}</div>
                            @endif
                        </td>
                        <td class="doc-acts">
                            @if(filled(optional($profile)->business_permit_path) && $businessStatus !== 'approved')
                                <button type="button" class="btn btn-sm btn-success rounded-pill"
                                    data-bs-toggle="modal" data-bs-target="#approvePermitModal{{ $landlord->id }}">Approve</button>
                                <button type="button" class="btn btn-sm btn-outline-danger rounded-pill"
                                    data-bs-toggle="modal" data-bs-target="#rejectPermitModal{{ $landlord->id }}">Reject</button>
                            @elseif($businessStatus === 'approved')
                                <button type="button" class="btn btn-sm btn-outline-danger rounded-pill"
                                    data-bs-toggle="modal" data-bs-target="#rejectPermitModal{{ $landlord->id }}">Reject</button>
                            @endif
                        </td>
                    </tr>

                    {{-- ── Safety Certificate row ── --}}
                    <tr>
                        <td class="doc-name"><i class="bi bi-shield-check me-1 la-muted"></i>Safety Certificate</td>
                        <td class="doc-status">
                            <span class="{{ $sbClass($safetyStatus) }}">{{ ucfirst($safetyStatus) }}</span>
                        </td>
                        <td class="doc-file">
                            @if(filled(optional($profile)->safety_certificate_path))
                                <a href="{{ asset('storage/' . $profile->safety_certificate_path) }}"
                                   target="_blank" rel="noopener"
                                   class="btn btn-sm btn-outline-secondary rounded-pill me-1">
                                    <i class="bi bi-file-earmark-pdf me-1"></i>View
                                </a>
                            @else
                                <span class="la-muted">No file uploaded</span>
                            @endif
                            @if(!empty(optional($profile)->safety_certificate_reviewed_at))
                                <div class="doc-last-review mt-1">
                                    Reviewed {{ $profile->safety_certificate_reviewed_at->format('M d, Y') }}
                                    @if(!empty(optional(optional($profile)->safetyCertificateReviewer)->full_name))
                                        · {{ $profile->safetyCertificateReviewer->full_name }}
                                    @endif
                                </div>
                            @endif
                            @if($safetyStatus === 'rejected' && filled(optional($profile)->safety_certificate_rejection_reason))
                                <div class="doc-reject-reason">{{ $profile->safety_certificate_rejection_reason }}</div>
                            @endif
                        </td>
                        <td class="doc-acts">
                            @if(filled(optional($profile)->safety_certificate_path) && $safetyStatus !== 'approved')
                                <button type="button" class="btn btn-sm btn-success rounded-pill"
                                    data-bs-toggle="modal" data-bs-target="#approveSafetyModal{{ $landlord->id }}">Approve</button>
                                <button type="button" class="btn btn-sm btn-outline-danger rounded-pill"
                                    data-bs-toggle="modal" data-bs-target="#rejectSafetyModal{{ $landlord->id }}">Reject</button>
                            @elseif($safetyStatus === 'approved')
                                <button type="button" class="btn btn-sm btn-outline-danger rounded-pill"
                                    data-bs-toggle="modal" data-bs-target="#rejectSafetyModal{{ $landlord->id }}">Reject</button>
                            @endif
                        </td>
                    </tr>
                </tbody>
            </table>

            {{-- ── Permit modals ── --}}
            {{-- Approve business permit --}}
            <div class="modal fade" id="approvePermitModal{{ $landlord->id }}" tabindex="-1"
                 aria-labelledby="approvePermitModalLabel{{ $landlord->id }}" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content rounded-4 border-0 shadow">
                        <form method="POST" action="{{ route('admin.permits.approve', $landlord) }}">
                            @csrf
                            <div class="modal-header border-0 pb-0">
                                <h5 class="modal-title" id="approvePermitModalLabel{{ $landlord->id }}">Approve Business Permit</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body pt-2">
                                <div class="mb-1 fw-semibold">{{ $landlord->full_name }}</div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="1"
                                           id="approve_permit_confirm_{{ $landlord->id }}" required>
                                    <label class="form-check-label small" for="approve_permit_confirm_{{ $landlord->id }}">
                                        I confirm this business permit is valid and should be approved.
                                    </label>
                                </div>
                            </div>
                            <div class="modal-footer border-0 pt-0">
                                <button type="button" class="btn btn-outline-secondary rounded-pill"
                                        data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-success rounded-pill px-3">Confirm Approval</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Reject business permit --}}
            <div class="modal fade" id="rejectPermitModal{{ $landlord->id }}" tabindex="-1"
                 aria-labelledby="rejectPermitModalLabel{{ $landlord->id }}" aria-hidden="true">
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
                                <div class="mb-3">
                                    <label for="permit_rejection_reason_{{ $landlord->id }}" class="form-label small">
                                        Rejection reason <span class="text-danger">*</span>
                                    </label>
                                    <textarea id="permit_rejection_reason_{{ $landlord->id }}"
                                              name="rejection_reason" class="form-control"
                                              rows="3" maxlength="500" required></textarea>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="1"
                                           id="reject_permit_confirm_{{ $landlord->id }}" required>
                                    <label class="form-check-label small" for="reject_permit_confirm_{{ $landlord->id }}">
                                        I confirm this business permit should be rejected.
                                    </label>
                                </div>
                            </div>
                            <div class="modal-footer border-0 pt-0">
                                <button type="button" class="btn btn-outline-secondary rounded-pill"
                                        data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-danger rounded-pill px-3">Confirm Rejection</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Approve safety certificate --}}
            <div class="modal fade" id="approveSafetyModal{{ $landlord->id }}" tabindex="-1"
                 aria-labelledby="approveSafetyModalLabel{{ $landlord->id }}" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content rounded-4 border-0 shadow">
                        <form method="POST" action="{{ route('admin.permits.safety.approve', $landlord) }}">
                            @csrf
                            <div class="modal-header border-0 pb-0">
                                <h5 class="modal-title" id="approveSafetyModalLabel{{ $landlord->id }}">Approve Safety Certificate</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body pt-2">
                                <div class="mb-1 fw-semibold">{{ $landlord->full_name }}</div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="1"
                                           id="approve_safety_confirm_{{ $landlord->id }}" required>
                                    <label class="form-check-label small" for="approve_safety_confirm_{{ $landlord->id }}">
                                        I confirm this safety certificate is valid and should be approved.
                                    </label>
                                </div>
                            </div>
                            <div class="modal-footer border-0 pt-0">
                                <button type="button" class="btn btn-outline-secondary rounded-pill"
                                        data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-success rounded-pill px-3">Confirm Approval</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Reject safety certificate --}}
            <div class="modal fade" id="rejectSafetyModal{{ $landlord->id }}" tabindex="-1"
                 aria-labelledby="rejectSafetyModalLabel{{ $landlord->id }}" aria-hidden="true">
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
                                <div class="mb-3">
                                    <label for="safety_rejection_reason_{{ $landlord->id }}" class="form-label small">
                                        Rejection reason <span class="text-danger">*</span>
                                    </label>
                                    <textarea id="safety_rejection_reason_{{ $landlord->id }}"
                                              name="rejection_reason" class="form-control"
                                              rows="3" maxlength="500" required></textarea>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="1"
                                           id="reject_safety_confirm_{{ $landlord->id }}" required>
                                    <label class="form-check-label small" for="reject_safety_confirm_{{ $landlord->id }}">
                                        I confirm this safety certificate should be rejected.
                                    </label>
                                </div>
                            </div>
                            <div class="modal-footer border-0 pt-0">
                                <button type="button" class="btn btn-outline-secondary rounded-pill"
                                        data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-danger rounded-pill px-3">Confirm Rejection</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif
    </div>{{-- /.ll-card --}}

@empty
    <div class="la-empty-page">
        <i class="bi bi-check2-circle fs-3 mb-2 d-block"></i>
        <div class="fw-semibold mb-1">No landlords found for this filter.</div>
        <div class="small">Try a different filter or wait for new records.</div>
    </div>
@endforelse

{{-- Pagination --}}
@if(($landlords ?? null) && $landlords->hasPages())
    <div class="la-pagination">
        {{ $landlords->links() }}
    </div>
@endif

@endsection
