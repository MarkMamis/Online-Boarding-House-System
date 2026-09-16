@extends('layouts.admin')

@section('title', 'Landlord Details - ' . $user->full_name)

@section('content')
<style>
    .landlord-detail-shell {
        background: #ffffff;
        border: 1px solid rgba(2, 8, 20, 0.08);
        border-radius: 1.1rem;
        box-shadow: 0 4px 18px rgba(2, 8, 20, 0.03);
        padding: 1.25rem;
    }

    .detail-breadcrumb {
        font-size: 0.8rem;
        font-weight: 600;
        letter-spacing: 0.02em;
        color: #64748b;
        margin-bottom: 0.35rem;
    }

    .detail-breadcrumb a {
        color: #64748b;
        text-decoration: none;
    }

    .detail-breadcrumb a:hover {
        color: #166534;
    }

    .detail-title {
        font-size: 1.55rem;
        font-weight: 800;
        color: #0f172a;
        line-height: 1.2;
    }

    /* ── Metric Chips ── */
    .metric-chip {
        background: #f8fafc;
        border: 1px solid rgba(2, 8, 20, 0.07);
        border-radius: 0.85rem;
        padding: 0.75rem 1rem;
        height: 100%;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }

    .metric-chip:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(2, 8, 20, 0.04);
    }

    .metric-chip-label {
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #64748b;
        margin-bottom: 0.2rem;
    }

    .metric-chip-value {
        font-size: 1.35rem;
        font-weight: 800;
        color: #0f172a;
        line-height: 1.1;
    }

    .metric-chip-sub {
        font-size: 0.74rem;
        color: #94a3b8;
        margin-top: 0.25rem;
    }

    /* ── Primary Tabs ── */
    .detail-nav-tabs {
        display: flex;
        flex-wrap: wrap;
        gap: 0.45rem;
        border-bottom: 1px solid rgba(2, 8, 20, 0.08);
        padding-bottom: 0.75rem;
        margin-bottom: 1.25rem;
    }

    .detail-nav-tabs .nav-link {
        border: 1px solid rgba(20, 83, 45, 0.18);
        border-radius: 999px;
        background: rgba(240, 253, 244, 0.65);
        color: #14532d;
        font-size: 0.82rem;
        font-weight: 700;
        padding: 0.4rem 0.9rem;
        transition: all 0.15s ease;
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
    }

    .detail-nav-tabs .nav-link:hover {
        background: rgba(22, 101, 52, 0.12);
        border-color: rgba(20, 83, 45, 0.3);
    }

    .detail-nav-tabs .nav-link.active {
        background: #166534;
        border-color: #166534;
        color: #ffffff;
        box-shadow: 0 2px 8px rgba(22, 101, 52, 0.25);
    }

    .detail-nav-tabs .nav-link .badge {
        font-size: 0.72rem;
        font-weight: 700;
        border-radius: 999px;
    }

    /* ── Content Surfaces ── */
    .surface-card {
        border: 1px solid rgba(2, 8, 20, 0.07);
        border-radius: 0.85rem;
        background: #ffffff;
        box-shadow: 0 2px 10px rgba(2, 8, 20, 0.02);
        overflow: hidden;
    }

    .surface-header {
        border-bottom: 1px solid rgba(2, 8, 20, 0.06);
        background: #f8fafc;
        padding: 0.75rem 1rem;
        font-weight: 700;
        font-size: 0.88rem;
        color: #0f172a;
    }

    .surface-body {
        padding: 1rem;
    }

    /* ── Profile Grid ── */
    .profile-info-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.85rem 1.25rem;
    }

    .profile-info-item {
        border-bottom: 1px dashed rgba(2, 8, 20, 0.08);
        padding-bottom: 0.4rem;
    }

    .profile-info-label {
        font-size: 0.72rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #64748b;
        font-weight: 700;
        margin-bottom: 0.15rem;
    }

    .profile-info-val {
        font-size: 0.92rem;
        font-weight: 600;
        color: #0f172a;
        word-break: break-word;
    }

    /* ── Compact Compliance Card ── */
    .compliance-summary-card {
        border: 1px solid rgba(2, 8, 20, 0.07);
        border-radius: 0.85rem;
        background: #ffffff;
        padding: 1rem;
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .compliance-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.5rem;
        padding: 0.45rem 0.65rem;
        border: 1px solid rgba(2, 8, 20, 0.06);
        border-radius: 0.6rem;
        background: #f8fafc;
        margin-bottom: 0.5rem;
    }

    .compliance-label {
        font-size: 0.82rem;
        font-weight: 600;
        color: #334155;
    }

    .status-pill {
        font-size: 0.72rem;
        font-weight: 700;
        border-radius: 999px;
        padding: 0.18rem 0.55rem;
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
    }

    .status-pill.approved {
        background: rgba(22, 163, 74, 0.12);
        color: #166534;
        border: 1px solid rgba(22, 163, 74, 0.25);
    }

    .status-pill.pending {
        background: rgba(245, 158, 11, 0.14);
        color: #92400e;
        border: 1px solid rgba(245, 158, 11, 0.28);
    }

    .status-pill.rejected {
        background: rgba(239, 68, 68, 0.12);
        color: #b91c1c;
        border: 1px solid rgba(239, 68, 68, 0.25);
    }

    .status-pill.missing {
        background: rgba(100, 116, 139, 0.12);
        color: #475569;
        border: 1px solid rgba(100, 116, 139, 0.22);
    }

    /* ── Operational Strip ── */
    .onboarding-progress-bar {
        width: 100%;
        height: 6px;
        border-radius: 999px;
        background: rgba(2, 8, 20, 0.08);
        overflow: hidden;
        margin: 0.35rem 0 0.5rem;
    }

    .onboarding-progress-fill {
        height: 100%;
        border-radius: 999px;
        background: linear-gradient(90deg, #16a34a 0%, #22c55e 100%);
    }

    .stage-chips {
        display: flex;
        flex-wrap: wrap;
        gap: 0.35rem;
    }

    .stage-chip {
        font-size: 0.74rem;
        font-weight: 600;
        color: #475569;
        background: #f1f5f9;
        border-radius: 999px;
        padding: 0.15rem 0.55rem;
    }

    .notice-box {
        border: 1px solid rgba(20, 83, 45, 0.18);
        border-radius: 0.65rem;
        background: rgba(240, 253, 244, 0.65);
        padding: 0.5rem 0.75rem;
        font-size: 0.8rem;
        color: #14532d;
    }

    /* ── Tables & List items ── */
    .table thead th {
        font-size: 0.74rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #64748b;
        background: #f8fafc;
        border-bottom: 1px solid rgba(2, 8, 20, 0.08);
        padding: 0.65rem 0.85rem;
    }

    .table tbody td {
        padding: 0.65rem 0.85rem;
        font-size: 0.86rem;
        vertical-align: middle;
    }

    .avatar-circle {
        width: 34px;
        height: 34px;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(22, 101, 52, 0.1);
        border: 1px solid rgba(22, 101, 52, 0.2);
        color: #166534;
        font-weight: 700;
        font-size: 0.82rem;
        flex-shrink: 0;
    }

    .empty-state-compact {
        padding: 2rem 1rem;
        text-align: center;
        color: #64748b;
    }

    @media (max-width: 991.98px) {
        .profile-info-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 575.98px) {
        .landlord-detail-shell {
            padding: 1rem;
        }
    }
</style>

@php
    $permitProfile = optional($user->landlordProfile);
    $documentTypes = \App\Models\LandlordDocument::types();
    $currentLandlordDocuments = $currentLandlordDocuments ?? collect();
    $documentHistory = $documentHistory ?? collect();

    $businessPermitRecord = $currentLandlordDocuments->get(\App\Models\LandlordDocument::TYPE_BUSINESS_PERMIT);
    $permitStatus = $compliance['business_permit'] ?? ($businessPermitRecord?->verification_status ?: ($permitProfile->business_permit_status ?? 'not_submitted'));
    $safetyStatus = $compliance['safety_certificate'] ?? 'not_submitted';
    $isAccountActive = (bool) ($user->is_active ?? true);

    $completionPct = $totalOnboarding > 0
        ? (int) round(($completedOnboarding / $totalOnboarding) * 100)
        : 0;

    $focusNotice = null;
    if ($totalProperties === 0) {
        $focusNotice = 'Landlord has not configured any properties yet.';
    } elseif ($totalRooms === 0) {
        $focusNotice = 'Properties have no rooms configured yet.';
    } elseif ($totalTenants === 0) {
        $focusNotice = 'Ready for student bookings and active tenants.';
    }

    $activeTab = request('tab', ($activeTab ?? 'overview'));
@endphp

<div class="landlord-detail-shell">
    {{-- Top Navigation Breadcrumb --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
        <nav aria-label="breadcrumb">
            <div class="detail-breadcrumb">
                <a href="{{ route('admin.dashboard') }}">Admin</a>
                <span class="mx-1 text-muted">/</span>
                <a href="{{ route('admin.users.landlords') }}">Users</a>
                <span class="mx-1 text-muted">/</span>
                <a href="{{ route('admin.users.landlords') }}">Landlords</a>
                <span class="mx-1 text-muted">/</span>
                <span class="text-dark">{{ $user->full_name }}</span>
            </div>
        </nav>
        <a href="{{ route('admin.users.landlords') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
            <i class="bi bi-arrow-left me-1"></i>Back to Landlords
        </a>
    </div>

    {{-- Compact Account Header --}}
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3 pb-3 border-bottom">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                <h1 class="detail-title mb-0">{{ $user->full_name }}</h1>
                <span class="badge {{ $isAccountActive ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-danger-subtle text-danger border border-danger-subtle' }} rounded-pill px-2.5 py-1 fw-semibold">
                    {{ $isAccountActive ? 'Active Account' : 'Inactive Account' }}
                </span>
            </div>
            <div class="text-secondary small d-flex flex-wrap align-items-center gap-2">
                <span><i class="bi bi-person-badge me-1"></i>Landlord Account</span>
                <span class="text-muted">•</span>
                <span><i class="bi bi-envelope me-1"></i>{{ $user->email }}</span>
                <span class="text-muted">•</span>
                <span><i class="bi bi-telephone me-1"></i>{{ $user->contact_number ?: 'No contact number' }}</span>
                @if($user->boarding_house_name)
                    <span class="text-muted">•</span>
                    <span><i class="bi bi-building me-1"></i>{{ $user->boarding_house_name }}</span>
                @endif
            </div>
        </div>

        {{-- Action Buttons --}}
        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-sm btn-success rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#messageModal" data-receiver-id="{{ $user->id }}" data-receiver-name="{{ $user->full_name }}">
                <i class="bi bi-envelope me-1"></i>Send Message
            </button>

            <div class="dropdown">
                <button class="btn btn-sm btn-outline-secondary rounded-pill px-3 dropdown-toggle" type="button" id="landlordActionsDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    More Actions
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm" aria-labelledby="landlordActionsDropdown">
                    <li>
                        <button type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#editLandlordModal">
                            <i class="bi bi-pencil-square me-2 text-secondary"></i>Edit Profile
                        </button>
                    </li>
                    <li>
                        <button type="button" class="dropdown-item" onclick="switchLandlordTab('documents')">
                            <i class="bi bi-folder-check me-2 text-secondary"></i>Review Requirements
                        </button>
                    </li>
                    <li>
                        <a class="dropdown-item" href="{{ route('admin.permits.index') }}">
                            <i class="bi bi-file-earmark-check me-2 text-secondary"></i>Permit Queue
                        </a>
                    </li>
                    <li><hr class="dropdown-divider my-1"></li>
                    <li>
                        <button type="button" class="dropdown-item {{ $isAccountActive ? 'text-danger' : 'text-success' }}" data-bs-toggle="modal" data-bs-target="#confirmStatusModal">
                            <i class="bi {{ $isAccountActive ? 'bi-slash-circle' : 'bi-check-circle' }} me-2"></i>
                            {{ $isAccountActive ? 'Deactivate Account' : 'Activate Account' }}
                        </button>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    {{-- Compact 4-Metric Summary Bar --}}
    <div class="row g-2 mb-3">
        <div class="col-6 col-md-3">
            <div class="metric-chip">
                <div class="metric-chip-label">Current Tenants</div>
                <div class="metric-chip-value">{{ number_format($totalTenants) }}</div>
                <div class="metric-chip-sub">Active bookings</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="metric-chip">
                <div class="metric-chip-label">Properties</div>
                <div class="metric-chip-value">{{ number_format($totalProperties) }}</div>
                <div class="metric-chip-sub">{{ number_format($totalRooms) }} rooms total</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="metric-chip">
                <div class="metric-chip-label">Occupied Rooms</div>
                <div class="metric-chip-value">{{ number_format($occupiedRooms) }}/{{ number_format($totalRooms) }}</div>
                <div class="metric-chip-sub">{{ $totalRooms - $occupiedRooms }} available</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="metric-chip">
                <div class="metric-chip-label">Occupancy Rate</div>
                <div class="metric-chip-value">{{ $occupancyRate }}%</div>
                <div class="metric-chip-sub">Portfolio average</div>
            </div>
        </div>
    </div>

    {{-- Primary Detail Tabs --}}
    <ul class="nav detail-nav-tabs" id="landlordTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link {{ $activeTab === 'overview' ? 'active' : '' }}" id="tab-overview-btn" data-bs-toggle="pill" data-bs-target="#tab-overview" type="button" role="tab" aria-controls="tab-overview" aria-selected="{{ $activeTab === 'overview' ? 'true' : 'false' }}" onclick="updateTabUrl('overview')">
                <i class="bi bi-speedometer2"></i>Overview
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link {{ $activeTab === 'tenants' ? 'active' : '' }}" id="tab-tenants-btn" data-bs-toggle="pill" data-bs-target="#tab-tenants" type="button" role="tab" aria-controls="tab-tenants" aria-selected="{{ $activeTab === 'tenants' ? 'true' : 'false' }}" onclick="updateTabUrl('tenants')">
                <i class="bi bi-people"></i>Current Tenants
                <span class="badge {{ $activeTab === 'tenants' ? 'bg-white text-dark' : 'bg-success-subtle text-success' }} ms-1">{{ $totalTenants }}</span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link {{ $activeTab === 'portfolio' ? 'active' : '' }}" id="tab-portfolio-btn" data-bs-toggle="pill" data-bs-target="#tab-portfolio" type="button" role="tab" aria-controls="tab-portfolio" aria-selected="{{ $activeTab === 'portfolio' ? 'true' : 'false' }}" onclick="updateTabUrl('portfolio')">
                <i class="bi bi-buildings"></i>Portfolio
                <span class="badge {{ $activeTab === 'portfolio' ? 'bg-white text-dark' : 'bg-success-subtle text-success' }} ms-1">{{ $totalProperties }}</span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link {{ $activeTab === 'documents' ? 'active' : '' }}" id="tab-documents-btn" data-bs-toggle="pill" data-bs-target="#tab-documents" type="button" role="tab" aria-controls="tab-documents" aria-selected="{{ $activeTab === 'documents' ? 'true' : 'false' }}" onclick="updateTabUrl('documents')">
                <i class="bi bi-folder-check"></i>Documents &amp; Requirements
            </button>
        </li>
    </ul>

    {{-- Tab Panes --}}
    <div class="tab-content" id="landlordTabsContent">
        {{-- ==================== TAB 1: OVERVIEW ==================== --}}
        <div class="tab-pane fade {{ $activeTab === 'overview' ? 'show active' : '' }}" id="tab-overview" role="tabpanel" aria-labelledby="tab-overview-btn" tabindex="0">
            <div class="row g-3">
                {{-- Left: Operational Status & Landlord Profile --}}
                <div class="col-lg-7">
                    {{-- Operational Status --}}
                    <div class="surface-card mb-3">
                        <div class="surface-header d-flex justify-content-between align-items-center">
                            <span><i class="bi bi-activity me-1.5 text-success"></i>Operational Status</span>
                            <span class="badge {{ ($compliance['is_compliant'] ?? false) ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-warning-subtle text-warning border border-warning-subtle' }} rounded-pill px-2 py-0.5">
                                {{ ($compliance['is_compliant'] ?? false) ? 'Compliant' : 'Incomplete' }}
                            </span>
                        </div>
                        <div class="surface-body">
                            {{-- Onboarding mini track --}}
                            <div class="d-flex justify-content-between align-items-center small mb-1">
                                <span class="fw-semibold text-dark">Tenant Onboarding</span>
                                <span class="text-secondary">{{ $completedOnboarding }} of {{ $totalOnboarding }} completed ({{ $completionPct }}%)</span>
                            </div>
                            <div class="onboarding-progress-bar">
                                <div class="onboarding-progress-fill" style="width: {{ $completionPct }}%;"></div>
                            </div>
                            <div class="stage-chips mb-2.5">
                                <span class="stage-chip">Pending: {{ $pendingOnboarding }}</span>
                                <span class="stage-chip">Docs: {{ $documentsUploaded }}</span>
                                <span class="stage-chip">Contract: {{ $contractSigned }}</span>
                                <span class="stage-chip">Deposit: {{ $depositPaid }}</span>
                                <span class="stage-chip">Done: {{ $completedOnboarding }}</span>
                            </div>

                            <div class="d-flex justify-content-between align-items-center small py-2 border-top">
                                <span class="text-secondary fw-semibold">Property readiness:</span>
                                <span class="text-dark fw-bold">{{ $totalProperties }} {{ Str::plural('property', $totalProperties) }}, {{ $totalRooms }} rooms configured</span>
                            </div>

                            @if($focusNotice)
                                <div class="notice-box mt-2 d-flex align-items-center justify-content-between gap-2">
                                    <div>
                                        <i class="bi bi-info-circle me-1"></i>
                                        <span>{{ $focusNotice }}</span>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-link text-success fw-bold p-0 text-decoration-none text-nowrap" onclick="switchLandlordTab('portfolio')">
                                        View Portfolio &rarr;
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Landlord Profile Details --}}
                    <div class="surface-card">
                        <div class="surface-header d-flex justify-content-between align-items-center">
                            <span><i class="bi bi-person me-1.5 text-secondary"></i>Landlord Profile</span>
                            <button type="button" class="btn btn-sm btn-link text-success p-0 text-decoration-none fw-semibold" data-bs-toggle="modal" data-bs-target="#editLandlordModal">
                                <i class="bi bi-pencil me-1"></i>Edit
                            </button>
                        </div>
                        <div class="surface-body">
                            <div class="profile-info-grid">
                                <div class="profile-info-item">
                                    <div class="profile-info-label">Full Name</div>
                                    <div class="profile-info-val">{{ $user->full_name }}</div>
                                </div>
                                <div class="profile-info-item">
                                    <div class="profile-info-label">Boarding House</div>
                                    <div class="profile-info-val">{{ $user->boarding_house_name ?: 'Not specified' }}</div>
                                </div>
                                <div class="profile-info-item">
                                    <div class="profile-info-label">Email Address</div>
                                    <div class="profile-info-val">{{ $user->email }}</div>
                                </div>
                                <div class="profile-info-item">
                                    <div class="profile-info-label">Contact Number</div>
                                    <div class="profile-info-val">{{ $user->contact_number ?: 'Not provided' }}</div>
                                </div>
                                <div class="profile-info-item">
                                    <div class="profile-info-label">Registered Date</div>
                                    <div class="profile-info-val">{{ $user->created_at->format('M d, Y') }}</div>
                                </div>
                                <div class="profile-info-item">
                                    <div class="profile-info-label">Last Updated</div>
                                    <div class="profile-info-val">{{ $user->updated_at->format('M d, Y') }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Right: Concise Compliance Summary --}}
                <div class="col-lg-5">
                    <div class="compliance-summary-card">
                        <div>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <h6 class="fw-bold text-dark mb-0"><i class="bi bi-shield-check me-1.5 text-success"></i>Compliance Summary</h6>
                                    <div class="small text-muted">Core regulatory requirements</div>
                                </div>
                                <span class="badge {{ ($compliance['is_compliant'] ?? false) ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-warning-subtle text-warning border border-warning-subtle' }} rounded-pill px-2.5 py-1">
                                    {{ ($compliance['is_compliant'] ?? false) ? 'Approved' : 'Action Required' }}
                                </span>
                            </div>

                            <div class="compliance-row">
                                <span class="compliance-label">Business Permit</span>
                                @php
                                    $bpClass = match($permitStatus) {
                                        'approved' => 'approved',
                                        'rejected' => 'rejected',
                                        'pending' => 'pending',
                                        default => 'missing',
                                    };
                                    $bpLabel = match($permitStatus) {
                                        'approved' => 'Approved',
                                        'rejected' => 'Rejected',
                                        'pending' => 'Pending Review',
                                        default => 'Missing',
                                    };
                                @endphp
                                <span class="status-pill {{ $bpClass }}">{{ $bpLabel }}</span>
                            </div>

                            <div class="compliance-row">
                                <span class="compliance-label">Safety Certificate</span>
                                @php
                                    $scClass = match($safetyStatus) {
                                        'approved' => 'approved',
                                        'rejected' => 'rejected',
                                        'pending' => 'pending',
                                        default => 'missing',
                                    };
                                    $scLabel = match($safetyStatus) {
                                        'approved' => 'Approved',
                                        'rejected' => 'Rejected',
                                        'pending' => 'Pending Review',
                                        default => 'Missing',
                                    };
                                @endphp
                                <span class="status-pill {{ $scClass }}">{{ $scLabel }}</span>
                            </div>

                            <div class="small text-muted mt-2 mb-3">
                                Detailed documents, file verification history, and manual approvals are managed in the Documents tab.
                            </div>
                        </div>

                        <div class="d-flex flex-wrap gap-2 pt-2 border-top">
                            <button type="button" class="btn btn-sm btn-outline-success rounded-pill px-3" onclick="switchLandlordTab('documents')">
                                <i class="bi bi-folder-check me-1"></i>View Requirements
                            </button>
                            <a href="{{ route('admin.documents.verification', ['landlord_id' => $user->id, 'verification_status' => 'all']) }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                                <i class="bi bi-shield-check me-1"></i>Verification Desk
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ==================== TAB 2: CURRENT TENANTS ==================== --}}
        <div class="tab-pane fade {{ $activeTab === 'tenants' ? 'show active' : '' }}" id="tab-tenants" role="tabpanel" aria-labelledby="tab-tenants-btn" tabindex="0">
            <div class="surface-card">
                <div class="surface-header d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-people me-1.5 text-success"></i>Current Active Tenants</span>
                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill">{{ $currentTenants->count() }} active</span>
                </div>

                @if($currentTenants->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="ps-3">Tenant Name</th>
                                    <th>Contact</th>
                                    <th>Property & Room</th>
                                    <th>Program / Year</th>
                                    <th>Stay Dates</th>
                                    <th class="pe-3 text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($currentTenants as $tenant)
                                    <tr>
                                        <td class="ps-3">
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="avatar-circle">{{ strtoupper(substr($tenant['name'], 0, 1)) }}</div>
                                                <div>
                                                    <div class="fw-bold text-dark">{{ $tenant['name'] }}</div>
                                                    <div class="small text-muted">{{ $tenant['email'] }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ $tenant['contact'] ?: 'Not provided' }}</td>
                                        <td>
                                            <div class="fw-semibold text-dark">{{ $tenant['property_name'] }}</div>
                                            <div class="small text-muted">{{ $tenant['room_number'] }}</div>
                                        </td>
                                        <td>
                                            <div>{{ $tenant['program'] ?: '—' }}</div>
                                            <div class="small text-muted">{{ $tenant['year_level'] ? 'Year ' . $tenant['year_level'] : '' }}</div>
                                        </td>
                                        <td>
                                            <div class="small text-dark fw-semibold">{{ $tenant['check_in'] }}</div>
                                            <div class="small text-muted">to {{ $tenant['check_out'] }}</div>
                                        </td>
                                        <td class="pe-3 text-end">
                                            <div class="d-flex justify-content-end gap-1">
                                                <a href="{{ route('admin.users.students.show', $tenant['id']) }}" class="btn btn-sm btn-outline-secondary rounded-pill" title="View student profile">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill" title="Send message" data-bs-toggle="modal" data-bs-target="#messageModal" data-receiver-id="{{ $tenant['id'] }}" data-receiver-name="{{ $tenant['name'] }}">
                                                    <i class="bi bi-envelope"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="empty-state-compact">
                        <i class="bi bi-people fs-2 text-secondary mb-2 d-block"></i>
                        <h6 class="fw-bold text-dark mb-1">No Active Tenants</h6>
                        <p class="small text-muted mb-0">No active tenants currently assigned to this landlord.</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- ==================== TAB 3: PORTFOLIO ==================== --}}
        <div class="tab-pane fade {{ $activeTab === 'portfolio' ? 'show active' : '' }}" id="tab-portfolio" role="tabpanel" aria-labelledby="tab-portfolio-btn" tabindex="0">
            <div class="surface-card">
                <div class="surface-header d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-buildings me-1.5 text-success"></i>Landlord Properties</span>
                    <span class="badge bg-secondary-subtle text-secondary rounded-pill">{{ $properties->count() }} registered</span>
                </div>

                @if($properties->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="ps-3">Property</th>
                                    <th>Location</th>
                                    <th>Rooms & Occupancy</th>
                                    <th>Status</th>
                                    <th class="pe-3 text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($properties as $property)
                                    @php
                                        $propApproval = strtolower((string) ($property->approval_status ?? 'pending'));
                                        $propBadge = match($propApproval) {
                                            'approved' => 'bg-success-subtle text-success border border-success-subtle',
                                            'rejected' => 'bg-danger-subtle text-danger border border-danger-subtle',
                                            default => 'bg-warning-subtle text-warning border border-warning-subtle',
                                        };
                                        $propOccupancyPct = $property->total_rooms > 0
                                            ? round(($property->occupied_rooms / $property->total_rooms) * 100)
                                            : 0;
                                    @endphp
                                    <tr>
                                        <td class="ps-3">
                                            <a href="{{ route('admin.properties.show', $property->id) }}" class="fw-bold text-dark text-decoration-none hover-success">
                                                {{ $property->name }}
                                            </a>
                                            <div class="small text-muted">{{ $property->current_tenants ?? 0 }} active tenants</div>
                                        </td>
                                        <td>
                                            <div class="small text-dark">{{ $property->address }}</div>
                                        </td>
                                        <td>
                                            <div class="fw-semibold text-dark">{{ $property->occupied_rooms }}/{{ $property->total_rooms }} rooms</div>
                                            <div class="small text-muted">{{ $propOccupancyPct }}% occupancy</div>
                                        </td>
                                        <td>
                                            <span class="badge {{ $propBadge }} rounded-pill px-2.5 py-1">
                                                {{ ucfirst($propApproval) }}
                                            </span>
                                        </td>
                                        <td class="pe-3 text-end">
                                            <a href="{{ route('admin.properties.show', $property->id) }}" class="btn btn-sm btn-outline-success rounded-pill px-3">
                                                <i class="bi bi-arrow-right me-1"></i>View
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="empty-state-compact">
                        <i class="bi bi-building fs-2 text-secondary mb-2 d-block"></i>
                        <h6 class="fw-bold text-dark mb-1">No Properties Configured</h6>
                        <p class="small text-muted mb-0">This landlord has not registered any boarding house properties yet.</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- ==================== TAB 4: DOCUMENTS & REQUIREMENTS ==================== --}}
        <div class="tab-pane fade {{ $activeTab === 'documents' ? 'show active' : '' }}" id="tab-documents" role="tabpanel" aria-labelledby="tab-documents-btn" tabindex="0">
            <div class="surface-card mb-3">
                <div class="surface-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div>
                        <span><i class="bi bi-folder-check me-1.5 text-success"></i>Documents &amp; Requirements</span>
                        <span class="small text-muted ms-2 fw-normal">Current active version of each regulatory requirement</span>
                    </div>
                    <a href="{{ route('admin.documents.verification', ['landlord_id' => $user->id, 'verification_status' => 'all']) }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                        <i class="bi bi-shield-check me-1"></i>Open Document Verification
                    </a>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="ps-3">Document</th>
                                <th>Number</th>
                                <th>Expiration</th>
                                <th>Status</th>
                                <th class="pe-3 text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($documentTypes as $documentType => $documentLabel)
                                @php
                                    $record = $currentLandlordDocuments->get($documentType);
                                    $legacyPath = $documentType === \App\Models\LandlordDocument::TYPE_BUSINESS_PERMIT
                                        ? $permitProfile->business_permit_path
                                        : $permitProfile->safety_certificate_path;
                                    $legacyStatus = $documentType === \App\Models\LandlordDocument::TYPE_BUSINESS_PERMIT
                                        ? ($permitProfile->business_permit_status ?? null)
                                        : ($permitProfile->safety_certificate_status ?? null);
                                    $filePath = $record?->file_path ?: $legacyPath;
                                    $documentNumber = $record?->document_number;
                                    $expirationDate = $record?->expiration_date;
                                    $status = $record?->verification_status ?: ($legacyStatus ?: 'not_submitted');
                                    $statusLabel = match($status) {
                                        'approved' => 'Approved',
                                        'rejected' => 'Rejected',
                                        'pending' => 'Pending',
                                        default => 'Not submitted',
                                    };
                                    $statusClass = match($status) {
                                        'approved' => 'text-bg-success',
                                        'rejected' => 'text-bg-danger',
                                        'pending' => 'text-bg-warning',
                                        default => 'text-bg-secondary',
                                    };
                                    $expirationLabel = $expirationDate
                                        ? ($expirationDate->lte(\Illuminate\Support\Carbon::today()) ? 'Expired' : 'Valid')
                                        : null;
                                    $historyRows = $documentHistory->get($documentType, collect());
                                    $approveRoute = $record
                                        ? route('admin.documents.approve', $record)
                                        : ($documentType === \App\Models\LandlordDocument::TYPE_BUSINESS_PERMIT
                                            ? route('admin.permits.approve', $user)
                                            : route('admin.permits.safety.approve', $user));
                                    $rejectRoute = $record
                                        ? route('admin.documents.reject', $record)
                                        : ($documentType === \App\Models\LandlordDocument::TYPE_BUSINESS_PERMIT
                                            ? route('admin.permits.reject', $user)
                                            : route('admin.permits.safety.reject', $user));
                                    $rejectModalId = 'landlordDetailReject' . \Illuminate\Support\Str::studly($documentType);
                                @endphp
                                <tr>
                                    <td class="ps-3">
                                        <div class="fw-bold text-dark">{{ $documentLabel }}</div>
                                        @if($record)
                                            <div class="small text-muted">Current submitted version</div>
                                        @elseif(filled($legacyPath))
                                            <div class="small text-muted">Legacy profile submission</div>
                                        @else
                                            <div class="small text-muted">No document submitted</div>
                                        @endif
                                    </td>
                                    <td>{{ $documentNumber ?: '—' }}</td>
                                    <td>
                                        <div>{{ $expirationDate?->format('M d, Y') ?: '—' }}</div>
                                        @if($expirationLabel)
                                            <div class="small {{ $expirationLabel === 'Expired' ? 'text-danger' : 'text-success' }}">{{ $expirationLabel }}</div>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge {{ $statusClass }} rounded-pill">{{ $statusLabel }}</span>
                                        @if($status === 'rejected' && filled($record?->rejection_reason))
                                            <div class="small text-danger mt-1" title="{{ $record->rejection_reason }}">{{ \Illuminate\Support\Str::limit($record->rejection_reason, 45) }}</div>
                                        @endif
                                    </td>
                                    <td class="pe-3 text-end">
                                        <div class="d-flex flex-wrap justify-content-end gap-1">
                                            @if(filled($filePath))
                                                <button type="button" onclick="openDocumentPreview('{{ file_download_url($filePath) }}', '{{ addslashes($documentLabel) }} - {{ addslashes($user->full_name) }}')" class="btn btn-sm btn-outline-secondary rounded-pill">
                                                    <i class="bi bi-eye me-1"></i>View
                                                </button>
                                                <a href="{{ file_download_url($filePath, true) }}" download class="btn btn-sm btn-outline-secondary rounded-pill">
                                                    <i class="bi bi-download me-1"></i>Download
                                                </a>
                                            @endif
                                            @if(filled($filePath) && $status !== 'approved')
                                                <form method="POST" action="{{ $approveRoute }}" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-success rounded-pill" onclick="return confirm('Approve this document?')">Approve</button>
                                                </form>
                                            @endif
                                            @if(filled($filePath) && $status !== 'rejected')
                                                <details class="d-inline-block position-relative text-start">
                                                    <summary class="btn btn-sm btn-outline-danger rounded-pill">Reject</summary>
                                                    <div class="bg-white border rounded-3 shadow-sm p-3 mt-1 position-absolute end-0" style="z-index: 20; min-width: 280px;">
                                                        <form method="POST" action="{{ $rejectRoute }}">
                                                            @csrf
                                                            <label for="{{ $rejectModalId }}Reason" class="form-label small fw-semibold">Rejection reason <span class="text-danger">*</span></label>
                                                            <textarea id="{{ $rejectModalId }}Reason" name="rejection_reason" class="form-control form-control-sm mb-2" rows="3" maxlength="500" required></textarea>
                                                            <button type="submit" class="btn btn-sm btn-danger rounded-pill">Confirm Rejection</button>
                                                        </form>
                                                    </div>
                                                </details>
                                            @endif
                                            @if(!$filePath)
                                                <span class="small text-muted align-self-center">Awaiting upload</span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @if($historyRows->isNotEmpty())
                                    <tr>
                                        <td colspan="5" class="px-3 py-2 bg-light">
                                            <details>
                                                <summary class="small fw-semibold text-success" style="cursor: pointer;">View document history ({{ $historyRows->count() }})</summary>
                                                <div class="table-responsive mt-2">
                                                    <table class="table table-sm align-middle mb-0 bg-white rounded border">
                                                        <thead>
                                                            <tr>
                                                                <th>Version</th>
                                                                <th>Number</th>
                                                                <th>Expiration</th>
                                                                <th>Status</th>
                                                                <th>Actions</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach($historyRows as $historyDocument)
                                                                @php
                                                                    $historyExpired = $historyDocument->expiration_date && $historyDocument->expiration_date->lte(\Illuminate\Support\Carbon::today());
                                                                @endphp
                                                                <tr>
                                                                    <td>{{ $historyDocument->date_issued?->format('Y') ?: ($historyDocument->submitted_at?->format('Y') ?: 'Previous') }}</td>
                                                                    <td>{{ $historyDocument->document_number ?: '—' }}</td>
                                                                    <td>
                                                                        {{ $historyDocument->expiration_date?->format('M d, Y') ?: '—' }}
                                                                        @if($historyExpired)
                                                                            <span class="small text-danger ms-1">(Expired)</span>
                                                                        @endif
                                                                    </td>
                                                                    <td><span class="badge {{ $historyDocument->verification_status === 'approved' ? 'text-bg-success' : ($historyDocument->verification_status === 'rejected' ? 'text-bg-danger' : 'text-bg-warning') }} rounded-pill">{{ ucfirst($historyDocument->verification_status) }}</span></td>
                                                                    <td>
                                                                        @if(filled($historyDocument->file_path))
                                                                            <button type="button" onclick="openDocumentPreview('{{ file_download_url($historyDocument->file_path) }}', '{{ addslashes($documentLabel) }} (History) - {{ addslashes($user->full_name) }}')" class="btn btn-sm btn-outline-secondary rounded-pill">View</button>
                                                                            <a href="{{ file_download_url($historyDocument->file_path, true) }}" download class="btn btn-sm btn-outline-secondary rounded-pill">Download</a>
                                                                        @endif
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </details>
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Edit Landlord Modal --}}
<div class="modal fade" id="editLandlordModal" tabindex="-1" aria-labelledby="editLandlordModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <form method="POST" action="{{ route('admin.users.landlords.update', $user) }}">
                @csrf
                @method('PUT')
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold" id="editLandlordModalLabel">Edit Landlord Profile</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body py-3">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Full Name</label>
                        <input type="text" name="full_name" class="form-control" value="{{ $user->full_name }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Email</label>
                        <input type="email" name="email" class="form-control" value="{{ $user->email }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Contact Number</label>
                        <input type="text" name="contact_number" class="form-control" value="{{ $user->contact_number }}">
                    </div>
                    <div>
                        <label class="form-label small fw-bold">Boarding House Name</label>
                        <input type="text" name="boarding_house_name" class="form-control" value="{{ $user->boarding_house_name }}">
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success fw-bold rounded-pill px-4">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Message Modal --}}
<div class="modal fade" id="messageModal" tabindex="-1" aria-labelledby="messageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <form method="POST" action="{{ route('admin.messages.store') }}">
                @csrf
                <input type="hidden" name="receiver_id" id="messageReceiverId">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold" id="messageModalLabel">Send Message</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body py-3">
                    <div class="small text-muted mb-2">To: <span id="messageReceiverName" class="fw-bold text-dark">Recipient</span></div>
                    <label class="form-label small fw-bold">Message</label>
                    <textarea name="body" class="form-control" rows="4" maxlength="2000" required placeholder="Type your message here..."></textarea>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success fw-bold rounded-pill px-4">Send Message</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Safe Deactivate / Activate Modal --}}
<div class="modal fade" id="confirmStatusModal" tabindex="-1" aria-labelledby="confirmStatusModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <form method="POST" action="{{ route('admin.users.landlords.status', $user) }}">
                @csrf
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold {{ ($user->is_active ?? true) ? 'text-danger' : 'text-success' }}" id="confirmStatusModalLabel">
                        {{ ($user->is_active ?? true) ? 'Deactivate Landlord Account' : 'Activate Landlord Account' }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body py-3">
                    <div class="alert {{ ($user->is_active ?? true) ? 'alert-danger-subtle border border-danger-subtle text-danger' : 'alert-success-subtle border border-success-subtle text-success' }} rounded-3 small mb-3">
                        <i class="bi {{ ($user->is_active ?? true) ? 'bi-exclamation-triangle' : 'bi-info-circle' }} me-1.5"></i>
                        {{ ($user->is_active ?? true) ? 'You are about to deactivate this landlord account.' : 'You are about to activate this landlord account.' }}
                    </div>
                    <p class="small text-secondary mb-2">
                        @if($user->is_active ?? true)
                            Once deactivated, this landlord will not be able to log in or manage their properties and tenants. The account can be reactivated by an admin at any time.
                        @else
                            Once activated, this landlord will regain access to their dashboard, properties, and tenant management tools.
                        @endif
                    </p>
                    <div class="bg-light p-2.5 rounded-3 border small">
                        <strong>{{ $user->full_name }}</strong><br>
                        <span class="text-muted">{{ $user->email }}</span>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn {{ ($user->is_active ?? true) ? 'btn-danger' : 'btn-success' }} fw-bold rounded-pill px-4">
                        <i class="bi {{ ($user->is_active ?? true) ? 'bi-slash-circle' : 'bi-check-circle' }} me-1"></i>
                        {{ ($user->is_active ?? true) ? 'Yes, Deactivate' : 'Yes, Activate' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function switchLandlordTab(tabName) {
    const btn = document.getElementById(`tab-${tabName}-btn`);
    if (btn) {
        bootstrap.Tab.getOrCreateInstance(btn).show();
        updateTabUrl(tabName);
    }
}

function updateTabUrl(tabName) {
    const url = new URL(window.location);
    url.searchParams.set('tab', tabName);
    window.history.replaceState({}, '', url);
}

document.addEventListener('DOMContentLoaded', () => {
    // Message modal receiver data population
    const modal = document.getElementById('messageModal');
    if (modal) {
        modal.addEventListener('show.bs.modal', (event) => {
            const trigger = event.relatedTarget;
            if (!trigger) return;

            const receiverId = trigger.getAttribute('data-receiver-id') || '';
            const receiverName = trigger.getAttribute('data-receiver-name') || 'Recipient';

            const idInput = document.getElementById('messageReceiverId');
            const nameLabel = document.getElementById('messageReceiverName');

            if (idInput) idInput.value = receiverId;
            if (nameLabel) nameLabel.textContent = receiverName;
        });
    }
});
</script>
@endsection
