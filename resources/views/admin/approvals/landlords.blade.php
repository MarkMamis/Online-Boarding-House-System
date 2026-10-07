@extends('layouts.admin')

@section('title', 'Landlord Accreditation - Admin Panel')

@section('content')
<div class="admin-page-shell">
    <x-admin.page-header
        title="Landlord Accreditation"
        subtitle="Review landlord eligibility, business permits, and initial compliance requirements."
        icon="bi-check2-circle"
        :breadcrumb="[
            ['label' => 'Admin', 'url' => route('admin.dashboard')],
            ['label' => 'Management'],
            ['label' => 'Landlord Accreditation']
        ]"
    >
        <x-slot:actions>
            <a href="{{ route('admin.users.landlords') }}" class="btn btn-sm btn-outline-brand px-3">
                <i class="bi bi-people me-1"></i>Landlords Directory
            </a>
        </x-slot:actions>
    </x-admin.page-header>

    {{-- Flash messages --}}
    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm py-2 mb-3 d-flex align-items-center gap-2">
            <i class="bi bi-check-circle-fill"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger border-0 shadow-sm py-2 mb-3 d-flex align-items-center gap-2">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <span>{{ session('error') }}</span>
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
        $permitStatusOptions   = ['all'=>'All','missing'=>'Missing','pending'=>'Pending','approved'=>'Approved','rejected'=>'Rejected'];
        $propertyStatusOptions = ['pending'=>'Pending','approved'=>'Approved','rejected'=>'Rejected','all'=>'All'];
    @endphp

    {{-- Tabs & Status Filters --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
        <div class="admin-segmented">
            <a href="{{ route('admin.approvals.landlords', ['tab' => 'permits']) }}"
               class="btn-seg {{ $activeTab === 'permits' ? 'active' : '' }}">
                <i class="bi bi-file-earmark-check"></i> Permit Accreditation
            </a>
            <a href="{{ route('admin.approvals.landlords', ['tab' => 'properties']) }}"
               class="btn-seg {{ $activeTab === 'properties' ? 'active' : '' }}">
                <i class="bi bi-building"></i> Property Reviews
            </a>
        </div>

        <div class="d-flex flex-wrap gap-1">
            @foreach(($activeTab === 'permits' ? $permitStatusOptions : $propertyStatusOptions) as $value => $label)
                <a href="{{ route('admin.approvals.landlords', ['tab' => $activeTab, 'status' => $value]) }}"
                   class="filter-chip {{ $statusFilter === $value ? 'active' : '' }}">{{ $label }}</a>
            @endforeach
        </div>
    </div>

    {{-- Landlord List --}}
    @forelse($landlords as $landlord)
        @php
            $profile = $landlord->landlordProfile;

            // Authoritatively resolve document statuses via centralized service
            $docStatusService = app(\App\Services\LandlordDocumentStatusService::class);
            $businessStatus = $docStatusService->resolveDocumentStatus($landlord, \App\Services\LandlordDocumentStatusService::DOC_BUSINESS_PERMIT);
            $safetyStatus = $docStatusService->resolveDocumentStatus($landlord, \App\Services\LandlordDocumentStatusService::DOC_SAFETY_CERTIFICATE);

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

        <div class="admin-card mb-3">
            {{-- Landlord Header --}}
            <div class="admin-card-header d-flex flex-wrap align-items-center justify-content-between gap-2 py-3">
                <div class="d-flex align-items-center gap-2">
                    <div class="admin-avatar">
                        <i class="bi bi-person"></i>
                    </div>
                    <div>
                        <div class="fw-bold text-dark">{{ $landlord->full_name }}</div>
                        <div class="small text-muted">
                            {{ $landlord->email }}
                            @if($landlord->contact_number)
                                &nbsp;·&nbsp;{{ $landlord->contact_number }}
                            @endif
                            @if($boardingHouseName)
                                &nbsp;·&nbsp;<span class="text-dark fw-semibold">{{ $boardingHouseName }}</span>
                            @endif
                        </div>
                    </div>
                </div>

                @if($activeTab === 'properties')
                    <div class="d-flex gap-2">
                        <span class="badge rounded-pill bg-warning-subtle text-warning-emphasis border border-warning-subtle">{{ $landlord->pending_properties_count ?? 0 }} Pending</span>
                        <span class="badge rounded-pill bg-success-subtle text-success border border-success-subtle">{{ $landlord->approved_properties_count ?? 0 }} Approved</span>
                        <span class="badge rounded-pill bg-danger-subtle text-danger border border-danger-subtle">{{ $landlord->rejected_properties_count ?? 0 }} Rejected</span>
                    </div>
                @else
                    {{-- Permit tab: show quick doc status badges in header --}}
                    <div class="d-flex gap-2 align-items-center flex-wrap">
                        <span class="small text-muted me-1 d-none d-sm-inline">Business Permit:</span>
                        <x-admin.status-badge :status="$businessStatus" />
                        <span class="small text-muted ms-2 me-1 d-none d-sm-inline">Safety Cert:</span>
                        <x-admin.status-badge :status="$safetyStatus" />
                    </div>
                @endif
            </div>

            {{-- PROPERTY TAB BODY --}}
            @if($activeTab === 'properties')
                @if($propertyList->count() > 0)
                    <div class="admin-table-wrap">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th class="ps-3">Property</th>
                                    <th>Status</th>
                                    <th>Submitted</th>
                                    <th>Rejection Reason</th>
                                    <th class="pe-3 text-end" style="min-width: 160px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($propertyList as $property)
                                    @php
                                        $propertyStatus = (string)($property->approval_status ?? 'pending');
                                    @endphp
                                    <tr>
                                        <td class="ps-3">
                                            <div class="fw-bold text-dark">{{ $property->name }}</div>
                                            <div class="small text-muted">{{ $property->address }}</div>
                                        </td>
                                        <td>
                                            <x-admin.status-badge :status="$propertyStatus" />
                                        </td>
                                        <td>
                                            <div class="fw-semibold">{{ $property->created_at->format('M d, Y') }}</div>
                                            <div class="small text-muted">{{ $property->created_at->format('h:i A') }}</div>
                                        </td>
                                        <td class="small text-muted" style="max-width: 220px;">
                                            {{ filled($property->rejection_reason) ? $property->rejection_reason : '—' }}
                                        </td>
                                        <td class="pe-3 text-end">
                                            <div class="d-inline-flex align-items-center gap-1">
                                                <a href="{{ route('admin.properties.show', $property) }}"
                                                   class="btn btn-sm btn-light border btn-action-icon text-secondary" title="View Property">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                @if($propertyStatus !== 'approved')
                                                    <button type="button" class="btn btn-sm btn-brand btn-action-icon"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#approvePropertyModal{{ $property->id }}" title="Approve">
                                                        <i class="bi bi-check2"></i>
                                                    </button>
                                                @endif
                                                <button type="button" class="btn btn-sm btn-outline-danger btn-action-icon"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#rejectPropertyModal{{ $property->id }}" title="Reject">
                                                    <i class="bi bi-x-lg"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Property Modals --}}
                    @foreach($propertyList as $property)
                        {{-- Approve modal --}}
                        <div class="modal fade" id="approvePropertyModal{{ $property->id }}" tabindex="-1"
                             aria-labelledby="approvePropertyModalLabel{{ $property->id }}" aria-hidden="true">
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
                                            <div class="mb-1 fw-semibold">{{ $property->name }}</div>
                                            <div class="small text-muted mb-3">Landlord: {{ $landlord->full_name }}</div>
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
                                            <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill"
                                                    data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-sm btn-brand px-3">Confirm Approval</button>
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
                                            <h5 class="modal-title fw-bold" id="rejectPropertyModalLabel{{ $property->id }}">
                                                <i class="bi bi-x-octagon text-danger me-2"></i>Reject Property
                                            </h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body pt-2 text-start">
                                            <div class="mb-1 fw-semibold">{{ $property->name }}</div>
                                            <div class="mb-3">
                                                <label for="rejection_reason_{{ $property->id }}" class="form-label small fw-semibold">
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
                                            <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill"
                                                    data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-sm btn-danger px-3">Confirm Rejection</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="p-3 text-center text-muted small">No properties submitted by this landlord.</div>
                @endif

            {{-- PERMIT TAB BODY --}}
            @else
                <div class="admin-table-wrap">
                    <table class="admin-table">
                        <tbody>
                            {{-- Business Permit row --}}
                            <tr>
                                <td class="ps-3" style="width: 220px;">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="bi bi-file-earmark-text text-primary"></i>
                                        <span class="fw-bold">Business Permit</span>
                                    </div>
                                </td>
                                <td style="width: 140px;">
                                    <x-admin.status-badge :status="$businessStatus" />
                                </td>
                                <td>
                                    @if(filled(optional($profile)->business_permit_path))
                                        <button type="button" onclick="openDocumentPreview('{{ file_download_url($profile->business_permit_path) }}', 'Business Permit - {{ addslashes($landlord->full_name) }}')"
                                           class="btn btn-sm btn-light border py-1 px-2 text-secondary" style="font-size: 0.8rem;">
                                            <i class="bi bi-file-earmark-pdf me-1 text-primary"></i>View Document
                                        </button>
                                    @else
                                        <span class="small text-muted">No document uploaded</span>
                                    @endif
                                    @if(!empty(optional($profile)->business_permit_reviewed_at))
                                        <div class="small text-muted mt-1">
                                            Reviewed {{ $profile->business_permit_reviewed_at->format('M d, Y') }}
                                            @if(!empty(optional(optional($profile)->businessPermitReviewer)->full_name))
                                                · {{ $profile->businessPermitReviewer->full_name }}
                                            @endif
                                        </div>
                                    @endif
                                    @if($businessStatus === 'rejected' && filled(optional($profile)->business_permit_rejection_reason))
                                        <div class="small text-danger mt-1">{{ $profile->business_permit_rejection_reason }}</div>
                                    @endif
                                </td>
                                <td class="pe-3 text-end" style="width: 160px;">
                                    <div class="d-inline-flex align-items-center gap-1">
                                        @if(filled(optional($profile)->business_permit_path) && $businessStatus !== 'approved')
                                            <button type="button" class="btn btn-sm btn-brand btn-action-icon"
                                                data-bs-toggle="modal" data-bs-target="#approvePermitModal{{ $landlord->id }}" title="Approve">
                                                <i class="bi bi-check2"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-danger btn-action-icon"
                                                data-bs-toggle="modal" data-bs-target="#rejectPermitModal{{ $landlord->id }}" title="Reject">
                                                <i class="bi bi-x-lg"></i>
                                            </button>
                                        @elseif($businessStatus === 'approved')
                                            <button type="button" class="btn btn-sm btn-outline-danger btn-action-icon"
                                                data-bs-toggle="modal" data-bs-target="#rejectPermitModal{{ $landlord->id }}" title="Revoke/Reject">
                                                <i class="bi bi-x-lg"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>

                            {{-- Safety Certificate row --}}
                            <tr>
                                <td class="ps-3" style="width: 220px;">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="bi bi-shield-check text-success"></i>
                                        <span class="fw-bold">Safety Certificate</span>
                                    </div>
                                </td>
                                <td style="width: 140px;">
                                    <x-admin.status-badge :status="$safetyStatus" />
                                </td>
                                <td>
                                    @if(filled(optional($profile)->safety_certificate_path))
                                        <button type="button" onclick="openDocumentPreview('{{ file_download_url($profile->safety_certificate_path) }}', 'Safety Certificate - {{ addslashes($landlord->full_name) }}')"
                                           class="btn btn-sm btn-light border py-1 px-2 text-secondary" style="font-size: 0.8rem;">
                                            <i class="bi bi-file-earmark-pdf me-1 text-success"></i>View Document
                                        </button>
                                    @else
                                        <span class="small text-muted">No document uploaded</span>
                                    @endif
                                    @if(!empty(optional($profile)->safety_certificate_reviewed_at))
                                        <div class="small text-muted mt-1">
                                            Reviewed {{ $profile->safety_certificate_reviewed_at->format('M d, Y') }}
                                            @if(!empty(optional(optional($profile)->safetyCertificateReviewer)->full_name))
                                                · {{ $profile->safetyCertificateReviewer->full_name }}
                                            @endif
                                        </div>
                                    @endif
                                    @if($safetyStatus === 'rejected' && filled(optional($profile)->safety_certificate_rejection_reason))
                                        <div class="small text-danger mt-1">{{ $profile->safety_certificate_rejection_reason }}</div>
                                    @endif
                                </td>
                                <td class="pe-3 text-end" style="width: 160px;">
                                    <div class="d-inline-flex align-items-center gap-1">
                                        @if(filled(optional($profile)->safety_certificate_path) && $safetyStatus !== 'approved')
                                            <button type="button" class="btn btn-sm btn-brand btn-action-icon"
                                                data-bs-toggle="modal" data-bs-target="#approveSafetyModal{{ $landlord->id }}" title="Approve">
                                                <i class="bi bi-check2"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-danger btn-action-icon"
                                                data-bs-toggle="modal" data-bs-target="#rejectSafetyModal{{ $landlord->id }}" title="Reject">
                                                <i class="bi bi-x-lg"></i>
                                            </button>
                                        @elseif($safetyStatus === 'approved')
                                            <button type="button" class="btn btn-sm btn-outline-danger btn-action-icon"
                                                data-bs-toggle="modal" data-bs-target="#rejectSafetyModal{{ $landlord->id }}" title="Revoke/Reject">
                                                <i class="bi bi-x-lg"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                {{-- Permit Modals --}}
                {{-- Approve business permit --}}
                <div class="modal fade" id="approvePermitModal{{ $landlord->id }}" tabindex="-1"
                     aria-labelledby="approvePermitModalLabel{{ $landlord->id }}" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content rounded-4 border-0 shadow">
                            <form method="POST" action="{{ route('admin.permits.approve', $landlord) }}">
                                @csrf
                                <div class="modal-header border-0 pb-0">
                                    <h5 class="modal-title fw-bold" id="approvePermitModalLabel{{ $landlord->id }}">
                                        <i class="bi bi-check2-circle text-success me-2"></i>Approve Business Permit
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body pt-2 text-start">
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
                                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill"
                                            data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-sm btn-brand px-3">Confirm Approval</button>
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
                                    <h5 class="modal-title fw-bold" id="rejectPermitModalLabel{{ $landlord->id }}">
                                        <i class="bi bi-x-octagon text-danger me-2"></i>Reject Business Permit
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body pt-2 text-start">
                                    <div class="mb-2 fw-semibold">{{ $landlord->full_name }}</div>
                                    <div class="mb-3">
                                        <label for="permit_rejection_reason_{{ $landlord->id }}" class="form-label small fw-semibold">
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
                                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill"
                                            data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-sm btn-danger px-3">Confirm Rejection</button>
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
                                    <h5 class="modal-title fw-bold" id="approveSafetyModalLabel{{ $landlord->id }}">
                                        <i class="bi bi-check2-circle text-success me-2"></i>Approve Safety Certificate
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body pt-2 text-start">
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
                                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill"
                                            data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-sm btn-brand px-3">Confirm Approval</button>
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
                                    <h5 class="modal-title fw-bold" id="rejectSafetyModalLabel{{ $landlord->id }}">
                                        <i class="bi bi-x-octagon text-danger me-2"></i>Reject Safety Certificate
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body pt-2 text-start">
                                    <div class="mb-2 fw-semibold">{{ $landlord->full_name }}</div>
                                    <div class="mb-3">
                                        <label for="safety_rejection_reason_{{ $landlord->id }}" class="form-label small fw-semibold">
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
                                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill"
                                            data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-sm btn-danger px-3">Confirm Rejection</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    @empty
        <div class="admin-card">
            <x-admin.empty-state
                icon="bi-check2-circle"
                title="No landlords found for this filter"
                message="There are no landlord records matching the selected status filter."
            />
        </div>
    @endforelse

    {{-- Pagination --}}
    @if(($landlords ?? null) && $landlords->hasPages())
        <div class="admin-card-header border-top d-flex justify-content-center mt-3">
            {{ $landlords->links() }}
        </div>
    @endif
</div>
@endsection

