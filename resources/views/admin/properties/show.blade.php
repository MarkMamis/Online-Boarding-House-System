@extends('layouts.admin')

@section('title', 'Property Details - ' . $property->name)

@section('content')
<style>
    .admin-property-shell {
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

    /* ── Cover Image ── */
    .property-cover-box {
        position: relative;
        border-radius: 0.75rem;
        overflow: hidden;
        background: #0f172a;
        max-height: 220px;
    }

    .property-cover {
        width: 100%;
        height: 220px;
        object-fit: cover;
        display: block;
        transition: transform 0.2s ease;
    }

    .property-cover-trigger {
        display: block;
        border: 0;
        padding: 0;
        width: 100%;
        background: transparent;
        cursor: zoom-in;
        border-radius: 0.75rem;
        overflow: hidden;
        text-align: left;
    }

    .property-cover-trigger:hover .property-cover {
        transform: scale(1.02);
    }

    .property-cover-hint {
        position: absolute;
        bottom: 8px;
        right: 8px;
        background: rgba(15, 23, 42, 0.75);
        color: #ffffff;
        font-size: 0.72rem;
        font-weight: 600;
        padding: 0.2rem 0.55rem;
        border-radius: 999px;
        backdrop-filter: blur(4px);
    }

    .property-cover-placeholder {
        height: 180px;
        border: 1px dashed rgba(2, 8, 20, 0.14);
        border-radius: 0.75rem;
        background: #f8fafc;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        color: #94a3b8;
        font-size: 0.85rem;
    }

    /* ── Quick Check List ── */
    .quick-check-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.5rem;
        padding: 0.45rem 0.65rem;
        border: 1px solid rgba(2, 8, 20, 0.06);
        border-radius: 0.6rem;
        background: #f8fafc;
        margin-bottom: 0.45rem;
    }

    .quick-check-label {
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

    .status-pill.ok {
        background: rgba(22, 163, 74, 0.12);
        color: #166534;
        border: 1px solid rgba(22, 163, 74, 0.25);
    }

    .status-pill.missing {
        background: rgba(245, 158, 11, 0.14);
        color: #92400e;
        border: 1px solid rgba(245, 158, 11, 0.28);
    }

    /* ── Inclusions Chips ── */
    .inclusion-chip {
        border: 1px solid rgba(22, 101, 52, 0.2);
        border-radius: 999px;
        background: rgba(22, 101, 52, 0.06);
        color: #14532d;
        display: inline-flex;
        align-items: center;
        font-size: 0.76rem;
        font-weight: 600;
        gap: 0.3rem;
        padding: 0.25rem 0.6rem;
    }

    /* ── Room Table Styles ── */
    .room-thumb-mini {
        width: 38px;
        height: 38px;
        border-radius: 0.5rem;
        border: 1px solid rgba(2, 8, 20, 0.1);
        object-fit: cover;
        background: #f8fafc;
    }

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

    .empty-state-compact {
        padding: 2rem 1rem;
        text-align: center;
        color: #64748b;
    }

    .map-wrap {
        height: 320px;
        width: 100%;
        border-radius: 0.75rem;
        border: 1px solid rgba(2, 8, 20, 0.08);
    }

    .modal-image-full {
        max-height: 78vh;
        width: 100%;
        object-fit: contain;
        background: #0f172a;
        border-radius: 0.5rem;
    }

    .room-gallery-link {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        height: 280px;
        background: #0f172a;
        border-radius: 0.75rem;
        overflow: hidden;
    }

    .room-gallery-thumb {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
    }

    .room-detail-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
        gap: 0.6rem;
        margin-bottom: 1rem;
    }

    .room-detail-card {
        border: 1px solid rgba(2, 8, 20, 0.08);
        border-radius: 0.65rem;
        background: #f8fafc;
        padding: 0.5rem 0.65rem;
    }

    .room-detail-label {
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #64748b;
        font-weight: 700;
        margin-bottom: 0.1rem;
    }

    .room-detail-value {
        font-size: 0.92rem;
        font-weight: 700;
        color: #0f172a;
    }

    @media (max-width: 575.98px) {
        .admin-property-shell {
            padding: 1rem;
        }
        .map-wrap {
            height: 240px;
        }
    }
</style>

@php
    $approvalStatus = strtolower((string) ($property->approval_status ?? 'pending'));
    $isPendingApproval = $approvalStatus === 'pending';
    $approvalBadgeClass = match($approvalStatus) {
        'approved' => 'bg-success-subtle text-success border border-success-subtle',
        'rejected' => 'bg-danger-subtle text-danger border border-danger-subtle',
        default => 'bg-warning-subtle text-warning border border-warning-subtle',
    };

    $activeTab = request('tab', ($activeTab ?? 'overview'));

    $backStatus = request('status', ($isPendingApproval ? 'pending' : null));
    $backUrl = ($backStatus === 'pending' || request('from') === 'approvals')
        ? route('admin.properties.index', ['status' => 'pending'])
        : route('admin.properties.index');
    $backLabel = ($backStatus === 'pending' || request('from') === 'approvals')
        ? 'Back to Pending Reviews'
        : 'Back to Properties';
@endphp

<div class="admin-property-shell">
    {{-- Top Navigation Breadcrumb --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
        <nav aria-label="breadcrumb">
            <div class="detail-breadcrumb">
                <a href="{{ route('admin.dashboard') }}">Admin</a>
                <span class="mx-1 text-muted">/</span>
                <a href="{{ route('admin.properties.index') }}">Properties</a>
                <span class="mx-1 text-muted">/</span>
                <span class="text-dark">{{ $property->name }}</span>
            </div>
        </nav>
        <a href="{{ $backUrl }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
            <i class="bi bi-arrow-left me-1"></i>{{ $backLabel }}
        </a>
    </div>

    {{-- Compact Property Header --}}
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3 pb-3 border-bottom">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                <h1 class="detail-title mb-0">{{ $property->name }}</h1>
                <span class="badge {{ $approvalBadgeClass }} rounded-pill px-2.5 py-1 fw-semibold">
                    {{ ucfirst(str_replace('_', ' ', $approvalStatus)) }}
                </span>
            </div>
            <div class="text-secondary small d-flex flex-wrap align-items-center gap-2">
                <span><i class="bi bi-geo-alt me-1"></i>{{ $property->address ?: 'Address not provided' }}</span>
                <span class="text-muted">•</span>
                <span>
                    Landlord:
                    <a href="{{ route('admin.users.landlords.show', $property->landlord_id) }}" class="fw-semibold text-success text-decoration-none">
                        {{ optional($property->landlord)->full_name ?: 'Not provided' }}
                    </a>
                </span>
                <a href="{{ route('admin.users.landlords.show', $property->landlord_id) }}" class="btn btn-sm btn-outline-secondary rounded-pill py-0 px-2" style="font-size: 0.72rem;">
                    View Landlord
                </a>
            </div>
        </div>

        {{-- Decision / Status Actions --}}
        <div class="d-flex align-items-center gap-2">
            @if($isPendingApproval)
                <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3 fw-semibold" data-bs-toggle="modal" data-bs-target="#showRejectModal">
                    <i class="bi bi-x-circle me-1"></i>Reject
                </button>
                <button type="button" class="btn btn-sm btn-success rounded-pill px-3 fw-semibold" data-bs-toggle="modal" data-bs-target="#showApproveModal">
                    <i class="bi bi-check2-circle me-1"></i>Approve
                </button>
            @else
                <span class="badge {{ $approvalBadgeClass }} rounded-pill px-3 py-1.5 fs-7">
                    Decision: {{ ucfirst($approvalStatus) }}
                </span>
            @endif
        </div>
    </div>

    {{-- Compact 4-Metric Summary Bar --}}
    <div class="row g-2 mb-3">
        <div class="col-6 col-md-3">
            <div class="metric-chip">
                <div class="metric-chip-label">Approval Status</div>
                <div class="metric-chip-value fs-5">
                    <span class="badge {{ $approvalBadgeClass }} rounded-pill px-2 py-0.5">
                        {{ ucfirst(str_replace('_', ' ', $approvalStatus)) }}
                    </span>
                </div>
                <div class="metric-chip-sub">Review outcome</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="metric-chip">
                <div class="metric-chip-label">Configured Rooms</div>
                <div class="metric-chip-value">{{ $property->occupied_rooms }}/{{ $property->total_rooms }}</div>
                <div class="metric-chip-sub">{{ $property->total_rooms - $property->occupied_rooms }} available rooms</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="metric-chip">
                <div class="metric-chip-label">Occupancy Rate</div>
                <div class="metric-chip-value">{{ $occupancyRate }}%</div>
                <div class="metric-chip-sub">Active tenant occupancy</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="metric-chip">
                <div class="metric-chip-label">Price Range</div>
                <div class="metric-chip-value fs-6">
                    @if(!is_null($minPrice) && !is_null($maxPrice))
                        PHP {{ number_format($minPrice, 0) }} - {{ number_format($maxPrice, 0) }}
                    @else
                        Not Set
                    @endif
                </div>
                <div class="metric-chip-sub">Monthly room rates</div>
            </div>
        </div>
    </div>

    {{-- Primary Detail Tabs --}}
    <ul class="nav detail-nav-tabs" id="propertyTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link {{ $activeTab === 'overview' ? 'active' : '' }}" id="tab-overview-btn" data-bs-toggle="pill" data-bs-target="#tab-overview" type="button" role="tab" aria-controls="tab-overview" aria-selected="{{ $activeTab === 'overview' ? 'true' : 'false' }}" onclick="updatePropertyTabUrl('overview')">
                <i class="bi bi-info-circle"></i>Overview
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link {{ $activeTab === 'rooms' ? 'active' : '' }}" id="tab-rooms-btn" data-bs-toggle="pill" data-bs-target="#tab-rooms" type="button" role="tab" aria-controls="tab-rooms" aria-selected="{{ $activeTab === 'rooms' ? 'true' : 'false' }}" onclick="updatePropertyTabUrl('rooms')">
                <i class="bi bi-door-open"></i>Rooms
                <span class="badge {{ $activeTab === 'rooms' ? 'bg-white text-dark' : 'bg-success-subtle text-success' }} ms-1">{{ $property->rooms->count() }}</span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link {{ $activeTab === 'compliance' ? 'active' : '' }}" id="tab-compliance-btn" data-bs-toggle="pill" data-bs-target="#tab-compliance" type="button" role="tab" aria-controls="tab-compliance" aria-selected="{{ $activeTab === 'compliance' ? 'true' : 'false' }}" onclick="updatePropertyTabUrl('compliance')">
                <i class="bi bi-shield-check"></i>Compliance
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link {{ $activeTab === 'location' ? 'active' : '' }}" id="tab-location-btn" data-bs-toggle="pill" data-bs-target="#tab-location" type="button" role="tab" aria-controls="tab-location" aria-selected="{{ $activeTab === 'location' ? 'true' : 'false' }}" onclick="updatePropertyTabUrl('location'); onLocationTabShown();">
                <i class="bi bi-geo-alt"></i>Location
            </button>
        </li>
    </ul>

    {{-- Tab Panes --}}
    <div class="tab-content" id="propertyTabsContent">
        {{-- ==================== TAB 1: OVERVIEW ==================== --}}
        <div class="tab-pane fade {{ $activeTab === 'overview' ? 'show active' : '' }}" id="tab-overview" role="tabpanel" aria-labelledby="tab-overview-btn" tabindex="0">
            <div class="row g-3">
                {{-- Left: Cover Image, Address, Description, Inclusions --}}
                <div class="col-lg-7">
                    <div class="surface-card mb-3">
                        <div class="surface-header d-flex justify-content-between align-items-center">
                            <span><i class="bi bi-building me-1.5 text-success"></i>Property Preview</span>
                            <span class="badge bg-light text-secondary border rounded-pill">{{ $property->address }}</span>
                        </div>
                        <div class="surface-body">
                            @if(!empty($property->image_path))
                                <div class="property-cover-box mb-3">
                                    <button type="button" class="property-cover-trigger" data-bs-toggle="modal" data-bs-target="#propertyImageModal" aria-label="Expand cover image">
                                        <img src="{{ file_url($property->image_path) }}" alt="{{ $property->name }}" class="property-cover">
                                        <span class="property-cover-hint"><i class="bi bi-arrows-fullscreen me-1"></i>Expand Image</span>
                                    </button>
                                </div>
                            @else
                                <div class="property-cover-placeholder mb-3">
                                    <i class="bi bi-image fs-3 mb-1"></i>
                                    <span>No property image uploaded</span>
                                </div>
                            @endif

                            <div class="mb-3">
                                <div class="small fw-bold text-dark mb-1">Description</div>
                                <p class="small text-secondary mb-0">
                                    {{ $property->description ?: 'No description provided by landlord.' }}
                                </p>
                            </div>

                            <div class="pt-2 border-top">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="small fw-bold text-dark"><i class="bi bi-stars me-1 text-success"></i>Building Inclusions</span>
                                    @if($servicesOffered->isNotEmpty())
                                        <span class="badge bg-light text-secondary border rounded-pill">{{ $servicesOffered->count() }} configured</span>
                                    @endif
                                </div>
                                @if($servicesOffered->isNotEmpty())
                                    <div class="d-flex flex-wrap gap-1.5">
                                        @foreach($servicesOffered as $service)
                                            <span class="inclusion-chip"><i class="bi bi-check2"></i>{{ $service }}</span>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="small text-muted">No building inclusions configured for this property.</div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Right: Property Quick Check & Landlord Compliance Summary --}}
                <div class="col-lg-5">
                    {{-- Property Quick Check --}}
                    <div class="surface-card mb-3">
                        <div class="surface-header">
                            <span><i class="bi bi-clipboard2-check me-1.5 text-success"></i>Property Readiness</span>
                        </div>
                        <div class="surface-body">
                            <div class="quick-check-row">
                                <span class="quick-check-label">Cover Image</span>
                                <span class="status-pill {{ !empty($property->image_path) ? 'ok' : 'missing' }}">
                                    {{ !empty($property->image_path) ? 'Uploaded' : 'Missing' }}
                                </span>
                            </div>
                            <div class="quick-check-row">
                                <span class="quick-check-label">Description</span>
                                <span class="status-pill {{ filled($property->description) ? 'ok' : 'missing' }}">
                                    {{ filled($property->description) ? 'Provided' : 'Missing' }}
                                </span>
                            </div>
                            <div class="quick-check-row">
                                <span class="quick-check-label">Map Coordinates</span>
                                <span class="status-pill {{ !empty($property->latitude) && !empty($property->longitude) ? 'ok' : 'missing' }}">
                                    {{ !empty($property->latitude) && !empty($property->longitude) ? 'Pinned' : 'Missing' }}
                                </span>
                            </div>
                            <div class="quick-check-row">
                                <span class="quick-check-label">Configured Rooms</span>
                                <span class="status-pill {{ $property->rooms->count() > 0 ? 'ok' : 'missing' }}">
                                    {{ $property->rooms->count() > 0 ? $property->rooms->count() . ' Room(s)' : 'None' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- Landlord Compliance Quick Summary --}}
                    <div class="surface-card">
                        <div class="surface-header d-flex justify-content-between align-items-center">
                            <span><i class="bi bi-shield-check me-1.5 text-success"></i>Landlord Compliance</span>
                            <span class="badge {{ ($compliance['is_compliant'] ?? false) ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-warning-subtle text-warning border border-warning-subtle' }} rounded-pill">
                                {{ ($compliance['is_compliant'] ?? false) ? 'Compliant' : 'Incomplete' }}
                            </span>
                        </div>
                        <div class="surface-body">
                            @php
                                $bp = $compliance['business_permit'] ?? 'missing';
                                $sc = $compliance['safety_certificate'] ?? 'missing';
                            @endphp
                            <div class="quick-check-row">
                                <span class="quick-check-label">Business Permit</span>
                                <span class="status-pill {{ $bp === 'approved' ? 'ok' : 'missing' }}">{{ ucfirst($bp) }}</span>
                            </div>
                            <div class="quick-check-row">
                                <span class="quick-check-label">Safety Certificate</span>
                                <span class="status-pill {{ $sc === 'approved' ? 'ok' : 'missing' }}">{{ ucfirst($sc) }}</span>
                            </div>

                            <div class="d-flex justify-content-between align-items-center pt-2 border-top mt-2">
                                <span class="small text-muted">Owner context</span>
                                <a href="{{ route('admin.users.landlords.show', $property->landlord_id) }}" class="small text-success fw-bold text-decoration-none">
                                    <i class="bi bi-box-arrow-up-right me-1"></i>Review Landlord Profile &rarr;
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ==================== TAB 2: ROOMS ==================== --}}
        <div class="tab-pane fade {{ $activeTab === 'rooms' ? 'show active' : '' }}" id="tab-rooms" role="tabpanel" aria-labelledby="tab-rooms-btn" tabindex="0">
            {{-- Room Summary Metrics --}}
            <div class="row g-2 mb-3">
                <div class="col-6 col-md-3">
                    <div class="metric-chip">
                        <div class="metric-chip-label">Total Rooms</div>
                        <div class="metric-chip-value">{{ $property->total_rooms }}</div>
                        <div class="metric-chip-sub">Inventory items</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="metric-chip">
                        <div class="metric-chip-label">Total Capacity</div>
                        <div class="metric-chip-value">{{ (int) $property->rooms->sum('capacity') }} pax</div>
                        <div class="metric-chip-sub">Total student slots</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="metric-chip">
                        <div class="metric-chip-label">Occupied / Available</div>
                        <div class="metric-chip-value">{{ $property->occupied_rooms }} / {{ $property->available_rooms ?? ($property->total_rooms - $property->occupied_rooms) }}</div>
                        <div class="metric-chip-sub">Room allocation</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="metric-chip">
                        <div class="metric-chip-label">Rate Spectrum</div>
                        <div class="metric-chip-value fs-6">
                            @if(!is_null($minPrice) && !is_null($maxPrice))
                                PHP {{ number_format($minPrice, 0) }} - {{ number_format($maxPrice, 0) }}
                            @else
                                Not Set
                            @endif
                        </div>
                        <div class="metric-chip-sub">Per room/month</div>
                    </div>
                </div>
            </div>

            {{-- Room Inventory Table --}}
            <div class="surface-card">
                <div class="surface-header d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-door-open me-1.5 text-success"></i>Room Inventory &amp; Pricing</span>
                    <span class="badge bg-secondary-subtle text-secondary rounded-pill">{{ $property->rooms->count() }} rooms</span>
                </div>

                @if($property->rooms->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="ps-3">Room</th>
                                    <th>Status</th>
                                    <th>Capacity</th>
                                    <th>Monthly Price</th>
                                    <th>Inclusions</th>
                                    <th class="pe-3 text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($property->rooms as $room)
                                    @php
                                        $roomStatus = $room->status ?? 'available';
                                        $roomStatusBadge = match($roomStatus) {
                                            'available' => 'bg-success-subtle text-success border border-success-subtle',
                                            'occupied' => 'bg-secondary-subtle text-secondary border border-secondary-subtle',
                                            default => 'bg-warning-subtle text-warning border border-warning-subtle',
                                        };
                                        $roomImagePath = $room->image_path ?: optional($room->roomImages->first())->image_path;
                                        $roomInclusions = collect(preg_split('/[,\n;]+/', (string) $room->inclusions))->map(fn($s) => trim($s))->filter();
                                    @endphp
                                    <tr>
                                        <td class="ps-3">
                                            <div class="d-flex align-items-center gap-2">
                                                @if(!empty($roomImagePath))
                                                    <img src="{{ file_url($roomImagePath) }}" alt="Room {{ $room->room_number }}" class="room-thumb-mini">
                                                @else
                                                    <div class="room-thumb-mini d-flex align-items-center justify-content-center text-muted">
                                                        <i class="bi bi-door-closed"></i>
                                                    </div>
                                                @endif
                                                <div>
                                                    <div class="fw-bold text-dark">{{ $room->room_number }}</div>
                                                    <div class="small text-muted">{{ (int) ($room->active_bookings_count ?? 0) }} active tenant(s)</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge {{ $roomStatusBadge }} rounded-pill">
                                                {{ ucfirst($roomStatus) }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="fw-semibold text-dark">{{ $room->getOccupancyDisplay() }}</div>
                                            <div class="small text-muted">{{ $room->getAvailableSlots() }} slot(s) available</div>
                                        </td>
                                        <td class="fw-bold text-dark">
                                            PHP {{ number_format((float) $room->price, 2) }}
                                        </td>
                                        <td>
                                            @if($roomInclusions->isNotEmpty())
                                                <div class="d-flex flex-wrap gap-1">
                                                    @foreach($roomInclusions->take(2) as $inc)
                                                        <span class="badge bg-light text-secondary border rounded-pill">{{ $inc }}</span>
                                                    @endforeach
                                                    @if($roomInclusions->count() > 2)
                                                        <span class="badge bg-light text-secondary border rounded-pill">+{{ $roomInclusions->count() - 2 }}</span>
                                                    @endif
                                                </div>
                                            @else
                                                <span class="small text-muted">None</span>
                                            @endif
                                        </td>
                                        <td class="pe-3 text-end">
                                            <button type="button" class="btn btn-sm btn-outline-success rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#roomDetailModal{{ $room->id }}">
                                                <i class="bi bi-eye me-1"></i>View Details
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="empty-state-compact">
                        <i class="bi bi-door-closed fs-2 text-secondary mb-2 d-block"></i>
                        <h6 class="fw-bold text-dark mb-1">No Rooms Configured</h6>
                        <p class="small text-muted mb-0">No rooms have been configured for this property. This property cannot currently provide room inventory information.</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- ==================== TAB 3: COMPLIANCE ==================== --}}
        <div class="tab-pane fade {{ $activeTab === 'compliance' ? 'show active' : '' }}" id="tab-compliance" role="tabpanel" aria-labelledby="tab-compliance-btn" tabindex="0">
            <div class="row g-3">
                {{-- Landlord Compliance --}}
                <div class="col-lg-6">
                    <div class="surface-card h-100">
                        <div class="surface-header d-flex justify-content-between align-items-center">
                            <span><i class="bi bi-file-earmark-check me-1.5 text-success"></i>Landlord Regulatory Compliance</span>
                            <span class="badge {{ ($compliance['is_compliant'] ?? false) ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-warning-subtle text-warning border border-warning-subtle' }} rounded-pill">
                                {{ ($compliance['is_compliant'] ?? false) ? 'Fully Compliant' : 'Incomplete' }}
                            </span>
                        </div>
                        <div class="surface-body">
                            <div class="quick-check-row">
                                <span class="quick-check-label">Business Permit</span>
                                <span class="status-pill {{ $bp === 'approved' ? 'ok' : 'missing' }}">{{ ucfirst($bp) }}</span>
                            </div>
                            <div class="quick-check-row">
                                <span class="quick-check-label">Safety Certificate</span>
                                <span class="status-pill {{ $sc === 'approved' ? 'ok' : 'missing' }}">{{ ucfirst($sc) }}</span>
                            </div>

                            <p class="small text-muted mt-3 mb-3">
                                Documents submitted by landlord <strong>{{ optional($property->landlord)->full_name ?: 'Landlord' }}</strong> determine municipal compliance and legitimate operating authorization.
                            </p>

                            <a href="{{ route('admin.users.landlords.show', ['user' => $property->landlord_id, 'tab' => 'documents']) }}" class="btn btn-sm btn-outline-success rounded-pill px-3">
                                <i class="bi bi-folder-check me-1"></i>Review Landlord Documents
                            </a>
                        </div>
                    </div>
                </div>

                {{-- Property Listing Readiness --}}
                <div class="col-lg-6">
                    <div class="surface-card h-100">
                        <div class="surface-header">
                            <span><i class="bi bi-card-checklist me-1.5 text-success"></i>Property Listing Readiness</span>
                        </div>
                        <div class="surface-body">
                            <div class="quick-check-row">
                                <span class="quick-check-label">Property Cover Photo</span>
                                <span class="status-pill {{ !empty($property->image_path) ? 'ok' : 'missing' }}">
                                    {{ !empty($property->image_path) ? 'Uploaded' : 'Missing' }}
                                </span>
                            </div>
                            <div class="quick-check-row">
                                <span class="quick-check-label">Listing Description</span>
                                <span class="status-pill {{ filled($property->description) ? 'ok' : 'missing' }}">
                                    {{ filled($property->description) ? 'Provided' : 'Missing' }}
                                </span>
                            </div>
                            <div class="quick-check-row">
                                <span class="quick-check-label">Pinned Map Location</span>
                                <span class="status-pill {{ !empty($property->latitude) && !empty($property->longitude) ? 'ok' : 'missing' }}">
                                    {{ !empty($property->latitude) && !empty($property->longitude) ? 'Pinned' : 'Missing' }}
                                </span>
                            </div>
                            <div class="quick-check-row">
                                <span class="quick-check-label">Configured Room Inventory</span>
                                <span class="status-pill {{ $property->rooms->count() > 0 ? 'ok' : 'missing' }}">
                                    {{ $property->rooms->count() > 0 ? $property->rooms->count() . ' Room(s)' : 'None' }}
                                </span>
                            </div>

                            <div class="alert alert-light border rounded-3 small mt-3 mb-0 text-secondary">
                                <i class="bi bi-info-circle text-primary me-1"></i>
                                <strong>Decision Guidance:</strong> Readiness checks inform administrative review. Absence of optional details does not automatically prevent approval unless mandated by local policy.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ==================== TAB 4: LOCATION ==================== --}}
        <div class="tab-pane fade {{ $activeTab === 'location' ? 'show active' : '' }}" id="tab-location" role="tabpanel" aria-labelledby="tab-location-btn" tabindex="0">
            <div class="surface-card">
                <div class="surface-header d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-geo-alt me-1.5 text-success"></i>Property Mapped Location</span>
                    <span class="small text-muted">{{ $property->address }}</span>
                </div>
                <div class="surface-body">
                    @if(!empty($property->latitude) && !empty($property->longitude))
                        <div id="propertyMap" class="map-wrap mb-2"></div>
                        <div class="d-flex justify-content-between align-items-center small text-muted">
                            <span><i class="bi bi-pin-map me-1"></i>{{ $property->address }}</span>
                            <span class="font-monospace">Coordinates: {{ number_format($property->latitude, 6) }}, {{ number_format($property->longitude, 6) }}</span>
                        </div>
                    @else
                        <div class="property-cover-placeholder mb-2">
                            <i class="bi bi-geo-alt fs-2 mb-1"></i>
                            <span>No map coordinates available for this property</span>
                        </div>
                        <div class="small text-muted">The landlord has not set latitude and longitude pins for this listing.</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Full Cover Image Modal --}}
@if(!empty($property->image_path))
    <div class="modal fade" id="propertyImageModal" tabindex="-1" aria-labelledby="propertyImageModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow rounded-4 overflow-hidden">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold" id="propertyImageModalLabel">{{ $property->name }} - Cover Image</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body bg-dark p-3 text-center">
                    <img src="{{ file_url($property->image_path) }}" alt="{{ $property->name }}" class="modal-image-full">
                </div>
            </div>
        </div>
    </div>
@endif

{{-- Room Detail Modals --}}
@foreach($property->rooms as $room)
    @php
        $roomImages = collect([]);
        if (!empty($room->image_path)) {
            $roomImages->push($room->image_path);
        }
        $roomImages = $roomImages
            ->merge($room->roomImages->pluck('image_path'))
            ->filter()
            ->unique()
            ->values();
        $roomServices = collect(preg_split('/[,\n;]+/', (string) $room->inclusions))->map(fn ($item) => trim($item))->filter();
        $roomStatus = $room->status ?? 'available';
        $roomStatusClass = $roomStatus === 'available'
            ? 'bg-success-subtle text-success border border-success-subtle'
            : ($roomStatus === 'occupied' ? 'bg-secondary-subtle text-secondary border border-secondary-subtle' : 'bg-warning-subtle text-warning border border-warning-subtle');
        $activeTenants = (int) ($room->active_bookings_count ?? 0);
        $availableSlots = (int) $room->getAvailableSlots();
        $occupancyDisplay = $room->getOccupancyDisplay();
        $carouselId = 'roomCarousel' . $room->id;
    @endphp
    <div class="modal fade" id="roomDetailModal{{ $room->id }}" tabindex="-1" aria-labelledby="roomDetailModalLabel{{ $room->id }}" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow rounded-4">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold" id="roomDetailModalLabel{{ $room->id }}">Room {{ $room->room_number }} Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body py-3">
                    <div class="d-flex flex-wrap gap-2 mb-3">
                        <span class="badge {{ $roomStatusClass }} rounded-pill">{{ ucfirst($roomStatus) }}</span>
                        <span class="badge bg-light text-dark border rounded-pill">{{ $occupancyDisplay }}</span>
                        <span class="badge bg-light text-dark border rounded-pill">PHP {{ number_format((float) $room->price, 2) }}</span>
                        <span class="badge bg-light text-dark border rounded-pill">{{ $availableSlots }} slots available</span>
                    </div>

                    <div class="room-detail-grid">
                        <div class="room-detail-card">
                            <div class="room-detail-label">Room Number</div>
                            <div class="room-detail-value">{{ $room->room_number }}</div>
                        </div>
                        <div class="room-detail-card">
                            <div class="room-detail-label">Capacity</div>
                            <div class="room-detail-value">{{ (int) $room->capacity }} pax</div>
                        </div>
                        <div class="room-detail-card">
                            <div class="room-detail-label">Occupancy</div>
                            <div class="room-detail-value">{{ $occupancyDisplay }}</div>
                        </div>
                        <div class="room-detail-card">
                            <div class="room-detail-label">Available Slots</div>
                            <div class="room-detail-value">{{ $availableSlots }}</div>
                        </div>
                        <div class="room-detail-card">
                            <div class="room-detail-label">Active Tenants</div>
                            <div class="room-detail-value">{{ $activeTenants }}</div>
                        </div>
                        <div class="room-detail-card">
                            <div class="room-detail-label">Monthly Price</div>
                            <div class="room-detail-value">PHP {{ number_format((float) $room->price, 2) }}</div>
                        </div>
                    </div>

                    @if($roomImages->isNotEmpty())
                        <div id="{{ $carouselId }}" class="carousel slide mb-3" data-bs-ride="false">
                            <div class="carousel-inner rounded-3 overflow-hidden">
                                @foreach($roomImages as $imagePath)
                                    <div class="carousel-item {{ $loop->first ? 'active' : '' }}">
                                        <a href="{{ file_download_url($imagePath) }}" target="_blank" rel="noopener" class="room-gallery-link" title="Open full image">
                                            <img src="{{ file_url($imagePath) }}" class="room-gallery-thumb" alt="{{ $room->room_number }} image {{ $loop->iteration }}">
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                            @if($roomImages->count() > 1)
                                <button class="carousel-control-prev" type="button" data-bs-target="#{{ $carouselId }}" data-bs-slide="prev">
                                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                    <span class="visually-hidden">Previous</span>
                                </button>
                                <button class="carousel-control-next" type="button" data-bs-target="#{{ $carouselId }}" data-bs-slide="next">
                                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                    <span class="visually-hidden">Next</span>
                                </button>
                            @endif
                        </div>
                    @endif

                    <div>
                        <div class="fw-bold small text-dark mb-1">Room Inclusions & Services</div>
                        @if($roomServices->isNotEmpty())
                            <div class="d-flex flex-wrap gap-1.5">
                                @foreach($roomServices as $service)
                                    <span class="badge bg-light text-secondary border rounded-pill">{{ $service }}</span>
                                @endforeach
                            </div>
                        @else
                            <span class="small text-muted">No specific inclusions listed for this room.</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endforeach

{{-- Safe Approve Modal --}}
@if($isPendingApproval)
<div class="modal fade" id="showApproveModal" tabindex="-1" aria-labelledby="showApproveModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <form method="POST" action="{{ route('admin.properties.approve', $property) }}">
                @csrf
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold" id="showApproveModalLabel">Approve Property</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body py-3">
                    <p class="text-secondary mb-2">
                        You are about to approve <strong class="text-dark">{{ $property->name }}</strong>, owned by <strong class="text-dark">{{ optional($property->landlord)->full_name ?: optional($property->landlord)->name }}</strong>.
                    </p>
                    <div class="alert alert-success-subtle border border-success-subtle rounded-3 small mb-0">
                        <i class="bi bi-check2-circle me-1"></i>
                        This will approve the property listing and make its rooms available for student bookings.
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success fw-bold rounded-pill px-4">Confirm Approval</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Safe Reject Modal with Required Reason --}}
<div class="modal fade" id="showRejectModal" tabindex="-1" aria-labelledby="showRejectModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <form method="POST" action="{{ route('admin.properties.reject', $property) }}">
                @csrf
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold text-danger" id="showRejectModalLabel">Reject Property</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body py-3">
                    <p class="text-secondary small mb-3">
                        Please provide a reason why <strong class="text-dark">{{ $property->name }}</strong> is being rejected. This feedback will be sent to the landlord.
                    </p>
                    <div class="mb-2">
                        <label for="showRejectionReason" class="form-label small fw-bold">Rejection Reason <span class="text-danger">*</span></label>
                        <textarea id="showRejectionReason" name="rejection_reason" class="form-control" rows="3" placeholder="e.g. Incomplete address, missing safety documentation, photo discrepancies..." required maxlength="500"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger fw-bold rounded-pill px-4">Confirm Rejection</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

<script>
let propertyMapInstance = null;

function switchPropertyTab(tabName) {
    const btn = document.getElementById(`tab-${tabName}-btn`);
    if (btn) {
        bootstrap.Tab.getOrCreateInstance(btn).show();
        updatePropertyTabUrl(tabName);
        if (tabName === 'location') {
            onLocationTabShown();
        }
    }
}

function updatePropertyTabUrl(tabName) {
    const url = new URL(window.location);
    url.searchParams.set('tab', tabName);
    window.history.replaceState({}, '', url);
}

function onLocationTabShown() {
    setTimeout(() => {
        if (propertyMapInstance) {
            propertyMapInstance.invalidateSize();
        } else {
            initPropertyMap();
        }
    }, 150);
}

function initPropertyMap() {
    const mapElement = document.getElementById('propertyMap');
    if (!mapElement || propertyMapInstance) return;

    @if(!empty($property->latitude) && !empty($property->longitude))
        const lat = {{ $property->latitude }};
        const lng = {{ $property->longitude }};

        propertyMapInstance = L.map('propertyMap').setView([lat, lng], 16);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxNativeZoom: 19,
            maxZoom: 22,
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(propertyMapInstance);

        const marker = L.marker([lat, lng]).addTo(propertyMapInstance);
        marker.bindPopup('<strong>{{ addslashes($property->name) }}</strong><br>{{ addslashes($property->address) }}').openPopup();
    @endif
}

document.addEventListener('DOMContentLoaded', () => {
    // If Location tab is active on initial load, initialize map immediately
    @if($activeTab === 'location' && !empty($property->latitude) && !empty($property->longitude))
        initPropertyMap();
    @endif

    // Listen for tab shown events
    const locationTabBtn = document.getElementById('tab-location-btn');
    if (locationTabBtn) {
        locationTabBtn.addEventListener('shown.bs.tab', () => {
            onLocationTabShown();
        });
    }
});
</script>
@endsection

@push('styles')
@if(!empty($property->latitude) && !empty($property->longitude))
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin=""/>
@endif
@endpush

@push('scripts')
@if(!empty($property->latitude) && !empty($property->longitude))
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
@endif
@endpush
