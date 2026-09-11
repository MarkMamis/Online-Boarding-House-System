@extends('layouts.admin')

@section('title', 'Registered Properties - Admin Panel')

@section('content')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="" />

<style>
    .properties-shell {
        background: #ffffff;
        border: 1px solid rgba(2, 8, 20, 0.08);
        border-radius: 1.1rem;
        box-shadow: 0 4px 18px rgba(2, 8, 20, 0.03);
        padding: 1.25rem;
    }

    .properties-breadcrumb {
        font-size: 0.8rem;
        font-weight: 600;
        letter-spacing: 0.02em;
        color: #64748b;
        margin-bottom: 0.35rem;
    }

    .properties-breadcrumb a {
        color: #64748b;
        text-decoration: none;
    }

    .properties-breadcrumb a:hover {
        color: #166534;
    }

    .page-title {
        font-size: 1.55rem;
        font-weight: 800;
        color: #0f172a;
        line-height: 1.2;
        margin-bottom: 0.25rem;
    }

    .page-subtitle {
        font-size: 0.88rem;
        color: #64748b;
        margin-bottom: 0;
    }

    /* ── Compact Metric Chips ── */
    .metric-chip {
        background: #f8fafc;
        border: 1px solid rgba(2, 8, 20, 0.07);
        border-radius: 0.85rem;
        padding: 0.85rem 1rem;
        height: 100%;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }

    .metric-chip:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(2, 8, 20, 0.04);
    }

    .metric-chip.attention {
        background: #fffbeb;
        border-color: #fde68a;
    }

    .metric-chip-label {
        font-size: 0.74rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #64748b;
        margin-bottom: 0.2rem;
    }

    .metric-chip.attention .metric-chip-label {
        color: #92400e;
    }

    .metric-chip-value {
        font-size: 1.45rem;
        font-weight: 800;
        color: #0f172a;
        line-height: 1.1;
    }

    .metric-chip.attention .metric-chip-value {
        color: #b45309;
    }

    .metric-chip-sub {
        font-size: 0.74rem;
        color: #94a3b8;
        margin-top: 0.25rem;
    }

    /* ── Toolbar & Filters ── */
    .filter-toolbar {
        background: #f8fafc;
        border: 1px solid rgba(2, 8, 20, 0.07);
        border-radius: 0.95rem;
        padding: 0.85rem 1rem;
        margin-bottom: 1.25rem;
    }

    .view-segmented {
        background: #e2e8f0;
        padding: 0.2rem;
        border-radius: 999px;
        display: inline-flex;
        gap: 0.2rem;
    }

    .view-segmented .btn-view {
        border: none;
        background: transparent;
        color: #475569;
        font-size: 0.82rem;
        font-weight: 700;
        padding: 0.35rem 0.9rem;
        border-radius: 999px;
        transition: all 0.15s ease;
    }

    .view-segmented .btn-view.active {
        background: #ffffff;
        color: #166534;
        box-shadow: 0 2px 6px rgba(2, 8, 20, 0.08);
    }

    .form-control-filter,
    .form-select-filter {
        font-size: 0.84rem;
        border-radius: 0.65rem;
        border: 1px solid #cbd5e1;
        background-color: #ffffff;
        padding: 0.45rem 0.75rem;
    }

    .form-control-filter:focus,
    .form-select-filter:focus {
        border-color: #166534;
        box-shadow: 0 0 0 3px rgba(22, 101, 52, 0.12);
    }

    /* ── Data Table ── */
    .table-container {
        border: 1px solid rgba(2, 8, 20, 0.08);
        border-radius: 0.95rem;
        background: #ffffff;
        overflow: hidden;
    }

    .properties-table {
        margin-bottom: 0;
    }

    .properties-table thead th {
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: #64748b;
        background: #f8fafc;
        border-bottom: 1px solid rgba(2, 8, 20, 0.08);
        padding: 0.85rem 1rem;
        white-space: nowrap;
    }

    .properties-table tbody td {
        padding: 0.95rem 1rem;
        vertical-align: middle;
        border-bottom: 1px solid rgba(2, 8, 20, 0.06);
        color: #1e293b;
        font-size: 0.88rem;
    }

    .properties-table tbody tr:last-child td {
        border-bottom: none;
    }

    .properties-table tbody tr:hover td {
        background-color: #fbfdfc;
    }

    /* ── Property Avatar ── */
    .property-thumb {
        width: 44px;
        height: 44px;
        border-radius: 0.65rem;
        object-fit: cover;
        flex-shrink: 0;
        background: #f1f5f9;
        border: 1px solid rgba(2, 8, 20, 0.08);
    }

    .property-thumb-fallback {
        width: 44px;
        height: 44px;
        border-radius: 0.65rem;
        background: rgba(22, 101, 52, 0.08);
        color: #166534;
        border: 1px solid rgba(22, 101, 52, 0.18);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.15rem;
        flex-shrink: 0;
    }

    /* ── Occupancy Progress Bar ── */
    .occupancy-track-sm {
        width: 72px;
        height: 6px;
        background: #e2e8f0;
        border-radius: 999px;
        overflow: hidden;
    }

    .occupancy-fill-sm {
        height: 100%;
        border-radius: 999px;
    }

    /* ── Map Canvas Card ── */
    .map-canvas-container {
        border: 1px solid rgba(2, 8, 20, 0.08);
        border-radius: 0.95rem;
        overflow: hidden;
        background: #ffffff;
    }

    .admin-map-area {
        width: 100%;
        height: 420px;
    }

    .price-marker-pill {
        background: #14532d;
        color: #ffffff;
        border-radius: 999px;
        border: 2px solid #ffffff;
        font-size: 0.72rem;
        font-weight: 700;
        padding: 0.2rem 0.55rem;
        white-space: nowrap;
        line-height: 1;
        box-shadow: 0 4px 12px rgba(2, 8, 20, 0.25);
    }

    .price-marker-pill.pending {
        background: #d97706;
    }

    /* ── Mobile Property Card ── */
    .property-card-mobile {
        border: 1px solid rgba(2, 8, 20, 0.08);
        border-radius: 0.85rem;
        padding: 1rem;
        background: #ffffff;
        margin-bottom: 0.85rem;
    }

    .property-card-mobile:last-child {
        margin-bottom: 0;
    }

    /* ── Empty State ── */
    .empty-state-box {
        padding: 3.5rem 1.5rem;
        text-align: center;
        color: #64748b;
    }

    /* ── Property & Landlord Contextual Links ── */
    .property-title-link {
        color: #0f172a;
        font-weight: 700;
        text-decoration: none;
        transition: color 0.15s ease;
    }

    .property-title-link:hover {
        color: #166534;
        text-decoration: underline;
    }

    .property-title-link:focus-visible {
        outline: 2px solid #166534;
        outline-offset: 2px;
        border-radius: 2px;
    }

    .landlord-name-link {
        color: #0f172a;
        font-weight: 600;
        text-decoration: none;
        transition: color 0.15s ease;
    }

    .landlord-name-link:hover {
        color: #166534;
        text-decoration: underline;
    }

    .landlord-name-link:focus-visible {
        outline: 2px solid #166534;
        outline-offset: 2px;
        border-radius: 2px;
    }

    /* ── Compact Single Primary Action ── */
    .btn-row-action {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.8125rem;
        font-weight: 600;
        line-height: 1.25;
        padding: 0.32rem 0.85rem;
        border-radius: 0.5rem;
        min-width: 72px;
        white-space: nowrap;
        text-decoration: none;
        transition: all 0.15s ease-in-out;
    }

    .btn-row-action:focus-visible {
        outline: 2px solid #166534;
        outline-offset: 2px;
    }

    .btn-row-action-review {
        color: #92400e;
        background-color: #fef3c7;
        border: 1px solid #fcd34d;
    }

    .btn-row-action-review:hover {
        background-color: #fde68a;
        color: #78350f;
        border-color: #f59e0b;
    }

    .btn-row-action-view {
        color: #334155;
        background-color: #ffffff;
        border: 1px solid #cbd5e1;
    }

    .btn-row-action-view:hover {
        background-color: #f8fafc;
        color: #0f172a;
        border-color: #94a3b8;
    }

    /* ── Workspace Status Tabs ── */
    .workspace-status-tabs-wrapper {
        border-bottom: 1px solid rgba(2, 8, 20, 0.08);
        padding-bottom: 0.85rem;
        margin-bottom: 1.15rem;
    }

    .workspace-status-tabs {
        display: inline-flex;
        flex-wrap: wrap;
        gap: 0.5rem;
    }

    .workspace-tab-link {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.45rem 0.95rem;
        border-radius: 999px;
        font-size: 0.84rem;
        font-weight: 600;
        text-decoration: none;
        color: #475569;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        transition: all 0.15s ease;
    }

    .workspace-tab-link:hover {
        background: #f1f5f9;
        color: #0f172a;
        border-color: #cbd5e1;
    }

    .workspace-tab-link.active {
        background: #166534;
        color: #ffffff;
        border-color: #166534;
        box-shadow: 0 2px 8px rgba(22, 101, 52, 0.25);
    }

    .workspace-tab-link.active.attention {
        background: #d97706;
        border-color: #d97706;
        color: #ffffff;
        box-shadow: 0 2px 8px rgba(217, 119, 6, 0.25);
    }

    .workspace-tab-badge {
        font-size: 0.74rem;
        font-weight: 700;
        padding: 0.12rem 0.5rem;
        border-radius: 999px;
        background: #e2e8f0;
        color: #334155;
    }

    .workspace-tab-link.active .workspace-tab-badge {
        background: rgba(255, 255, 255, 0.25);
        color: #ffffff;
    }

    .workspace-tab-badge.attention {
        background: #fee2e2;
        color: #991b1b;
    }

    .workspace-tab-link.active.attention .workspace-tab-badge.attention {
        background: rgba(255, 255, 255, 0.25);
        color: #ffffff;
    }

    /* ── Property Inspector Slide-Over Drawer ── */
    .property-inspector-backdrop {
        position: fixed;
        inset: 0;
        background-color: rgba(15, 23, 42, 0.45);
        backdrop-filter: blur(2px);
        z-index: 1055;
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.25s ease;
    }

    .property-inspector-backdrop.show {
        opacity: 1;
        pointer-events: auto;
    }

    .property-inspector-drawer {
        position: fixed;
        top: 0;
        right: 0;
        bottom: 0;
        width: 100%;
        max-width: 540px;
        background: #ffffff;
        z-index: 1060;
        box-shadow: -10px 0 35px rgba(2, 8, 20, 0.15);
        display: flex;
        flex-direction: column;
        transform: translateX(100%);
        transition: transform 0.28s cubic-bezier(0.16, 1, 0.3, 1);
    }

    .property-inspector-drawer.open {
        transform: translateX(0);
    }

    @media (max-width: 575.98px) {
        .property-inspector-drawer {
            max-width: 100%;
        }
    }

    .inspector-header {
        padding: 1.1rem 1.25rem;
        background: #ffffff;
        border-bottom: 1px solid rgba(2, 8, 20, 0.08);
        flex-shrink: 0;
    }

    .inspector-title {
        font-size: 1.25rem;
        font-weight: 800;
        color: #0f172a;
        line-height: 1.25;
    }

    .inspector-landlord-meta {
        font-size: 0.8rem;
        color: #64748b;
        font-weight: 600;
    }

    .inspector-tabs-bar {
        background: #f8fafc;
        border-bottom: 1px solid rgba(2, 8, 20, 0.08);
        padding: 0.5rem 1.25rem;
        flex-shrink: 0;
    }

    .inspector-tabs {
        display: flex;
        gap: 0.35rem;
        overflow-x: auto;
    }

    .inspector-tab {
        border: none;
        background: transparent;
        color: #64748b;
        font-size: 0.8rem;
        font-weight: 700;
        padding: 0.4rem 0.75rem;
        border-radius: 999px;
        transition: all 0.15s ease;
        white-space: nowrap;
        display: inline-flex;
        align-items: center;
    }

    .inspector-tab:hover {
        background: #e2e8f0;
        color: #0f172a;
    }

    .inspector-tab.active {
        background: #166534;
        color: #ffffff;
    }

    .inspector-tab.active .badge {
        background-color: rgba(255, 255, 255, 0.25) !important;
        color: #ffffff !important;
    }

    .inspector-body {
        flex: 1 1 auto;
        overflow-y: auto;
        padding: 1.15rem 1.25rem;
        background: #fafbfc;
    }

    .inspector-footer {
        padding: 0.9rem 1.25rem;
        background: #ffffff;
        border-top: 1px solid rgba(2, 8, 20, 0.08);
        flex-shrink: 0;
    }

    .inspector-card {
        background: #ffffff;
        border: 1px solid rgba(2, 8, 20, 0.08);
        border-radius: 0.85rem;
        overflow: hidden;
    }

    .inspector-card-header {
        background: #ffffff;
        border-bottom: 1px solid rgba(2, 8, 20, 0.06);
        padding: 0.75rem 1rem;
        font-size: 0.84rem;
        font-weight: 700;
        color: #0f172a;
    }

    .inspector-cover-wrap {
        border-radius: 0.85rem;
        overflow: hidden;
        background: #0f172a;
        max-height: 220px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .inspector-cover-img {
        width: 100%;
        height: 200px;
        object-fit: cover;
    }

    .inspector-cover-empty {
        height: 140px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        background: #f1f5f9;
        color: #64748b;
        border-radius: 0.85rem;
        border: 1px dashed #cbd5e1;
    }

    .quick-check-pill {
        display: flex;
        align-items: center;
        gap: 0.45rem;
        padding: 0.5rem 0.65rem;
        border-radius: 0.65rem;
        border: 1px solid #e2e8f0;
        background: #f8fafc;
        font-size: 0.78rem;
        font-weight: 600;
    }

    .quick-check-pill.ok {
        background: #f0fdf4;
        border-color: #bbf7d0;
        color: #166534;
    }

    .quick-check-pill.missing {
        background: #fffbeb;
        border-color: #fde68a;
        color: #92400e;
    }

    .inspector-stat-chip {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 0.75rem;
        padding: 0.65rem 0.85rem;
        text-align: center;
    }

    .inspector-stat-chip .stat-label {
        font-size: 0.7rem;
        font-weight: 700;
        text-transform: uppercase;
        color: #64748b;
    }

    .inspector-stat-chip .stat-val {
        font-size: 1.15rem;
        font-weight: 800;
        color: #0f172a;
    }

    .inspector-stat-chip .stat-sub {
        font-size: 0.7rem;
        color: #94a3b8;
    }

    .inspector-map-wrap {
        height: 220px;
        width: 100%;
    }

    .room-card-mini {
        background: #ffffff;
        border: 1px solid rgba(2, 8, 20, 0.08);
        border-radius: 0.75rem;
        padding: 0.75rem 0.9rem;
    }
</style>

<div class="properties-shell">
    {{-- 1. PAGE HEADER --}}
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <div class="properties-breadcrumb">
                <a href="{{ route('admin.dashboard') }}">Admin</a> / <span>Properties</span>
            </div>
            <h1 class="page-title">Registered Properties</h1>
            <p class="page-subtitle">Monitor registered boarding houses, occupancy, availability, and approval status.</p>
        </div>

        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('admin.properties.index', ['status' => 'pending']) }}" class="btn btn-sm btn-outline-success fw-semibold rounded-pill px-3 py-2 d-inline-flex align-items-center gap-2">
                <i class="bi bi-clipboard-check"></i>
                <span>Review Approvals</span>
                @if(($summary['pending_properties'] ?? 0) > 0)
                    <span class="badge rounded-pill bg-danger text-white" id="headerPendingBadge">{{ $summary['pending_properties'] }}</span>
                @endif
            </a>
        </div>
    </div>

    {{-- 2. COMPACT SUMMARY KPI ROW --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="metric-chip">
                <div class="metric-chip-label">Total Properties</div>
                <div class="metric-chip-value" id="kpiTotalProperties">{{ number_format($summary['total_properties']) }}</div>
                <div class="metric-chip-sub">Registered across OBHS</div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="metric-chip">
                <div class="metric-chip-label">Approved</div>
                <div class="metric-chip-value text-success" id="kpiApprovedProperties">{{ number_format($summary['approved_properties']) }}</div>
                <div class="metric-chip-sub">Active & compliant listings</div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="metric-chip {{ ($summary['pending_properties'] ?? 0) > 0 ? 'attention' : '' }}" id="kpiPendingCard">
                <div class="metric-chip-label">Pending Review</div>
                <div class="metric-chip-value" id="kpiPendingProperties">{{ number_format($summary['pending_properties']) }}</div>
                <div class="metric-chip-sub" id="kpiPendingSub">
                    @if(($summary['pending_properties'] ?? 0) > 0)
                        <span class="fw-bold text-amber-700">Requires attention</span>
                    @else
                        <span>All submissions reviewed</span>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="metric-chip">
                <div class="metric-chip-label">Available Capacity</div>
                <div class="metric-chip-value">{{ number_format($summary['available_rooms']) }} <span class="fs-6 fw-normal text-muted">rooms</span></div>
                <div class="metric-chip-sub">{{ number_format($summary['available_slots']) }} total slots open</div>
            </div>
        </div>
    </div>

    {{-- 2.5 WORKSPACE STATUS NAVIGATION TABS --}}
    <div class="workspace-status-tabs-wrapper">
        <div class="workspace-status-tabs" role="tablist" aria-label="Filter properties by approval status">
            <a href="{{ route('admin.properties.index', array_merge(request()->except(['page']), ['status' => 'all'])) }}"
               class="workspace-tab-link {{ $statusFilter === 'all' ? 'active' : '' }}">
                <span>All Properties</span>
                <span class="workspace-tab-badge" id="tabCountAll">{{ number_format($summary['total_properties'] ?? 0) }}</span>
            </a>
            <a href="{{ route('admin.properties.index', array_merge(request()->except(['page']), ['status' => 'pending'])) }}"
               class="workspace-tab-link {{ $statusFilter === 'pending' ? 'active attention' : '' }}">
                <span>Pending Review</span>
                <span class="workspace-tab-badge {{ ($summary['pending_properties'] ?? 0) > 0 ? 'attention' : '' }}" id="tabCountPending">{{ number_format($summary['pending_properties'] ?? 0) }}</span>
            </a>
            <a href="{{ route('admin.properties.index', array_merge(request()->except(['page']), ['status' => 'approved'])) }}"
               class="workspace-tab-link {{ $statusFilter === 'approved' ? 'active' : '' }}">
                <span>Approved</span>
                <span class="workspace-tab-badge" id="tabCountApproved">{{ number_format($summary['approved_properties'] ?? 0) }}</span>
            </a>
            <a href="{{ route('admin.properties.index', array_merge(request()->except(['page']), ['status' => 'rejected'])) }}"
               class="workspace-tab-link {{ $statusFilter === 'rejected' ? 'active' : '' }}">
                <span>Rejected</span>
                <span class="workspace-tab-badge" id="tabCountRejected">{{ number_format($summary['rejected_properties'] ?? 0) }}</span>
            </a>
        </div>
    </div>

    {{-- 3. VIEW MODE & FILTER TOOLBAR --}}
    <div class="filter-toolbar">
        <form method="GET" action="{{ route('admin.properties.index') }}" id="propertiesFilterForm">
            <input type="hidden" name="view" id="activeViewInput" value="{{ $activeView }}">

            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
                {{-- Segmented View Switcher --}}
                <div class="view-segmented" role="tablist" aria-label="Properties display mode">
                    <button type="button" class="btn-view {{ $activeView === 'list' ? 'active' : '' }}" id="tabBtnList" onclick="switchView('list')">
                        <i class="bi bi-list-ul me-1"></i> List View
                    </button>
                    <button type="button" class="btn-view {{ $activeView === 'map' ? 'active' : '' }}" id="tabBtnMap" onclick="switchView('map')">
                        <i class="bi bi-map me-1"></i> Map View ({{ count($mapProperties) }})
                    </button>
                </div>

                {{-- Reset Link --}}
                @if($search !== '' || $statusFilter !== 'all' || $availabilityFilter !== 'all' || $locationFilter !== 'all')
                    <a href="{{ route('admin.properties.index', ['view' => $activeView]) }}" class="btn btn-sm btn-link text-decoration-none text-muted p-0">
                        <i class="bi bi-x-circle me-1"></i> Reset Filters
                    </a>
                @endif
            </div>

            <div class="row g-2 align-items-center">
                {{-- Search Box --}}
                <div class="col-12 col-md-4 col-lg-5">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-muted" id="search-addon">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text"
                               name="search"
                               value="{{ $search }}"
                               class="form-control form-control-filter border-start-0 ps-0"
                               placeholder="Search properties, landlords, addresses..."
                               aria-label="Search properties"
                               aria-describedby="search-addon">
                    </div>
                </div>

                {{-- Status Filter --}}
                <div class="col-6 col-md-2 col-lg-2">
                    <select name="status" class="form-select form-select-filter" aria-label="Filter by approval status" onchange="this.form.submit()">
                        <option value="all" {{ $statusFilter === 'all' ? 'selected' : '' }}>All Statuses</option>
                        <option value="approved" {{ $statusFilter === 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="pending" {{ $statusFilter === 'pending' ? 'selected' : '' }}>Pending Review</option>
                        <option value="rejected" {{ $statusFilter === 'rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                </div>

                {{-- Availability Filter --}}
                <div class="col-6 col-md-3 col-lg-2">
                    <select name="availability" class="form-select form-select-filter" aria-label="Filter by room availability" onchange="this.form.submit()">
                        <option value="all" {{ $availabilityFilter === 'all' ? 'selected' : '' }}>All Availability</option>
                        <option value="available" {{ $availabilityFilter === 'available' ? 'selected' : '' }}>Available Slots</option>
                        <option value="occupied" {{ $availabilityFilter === 'occupied' ? 'selected' : '' }}>Fully Occupied</option>
                        <option value="no_rooms" {{ $availabilityFilter === 'no_rooms' ? 'selected' : '' }}>No Rooms Yet</option>
                    </select>
                </div>

                {{-- Location / Barangay Filter --}}
                <div class="col-12 col-md-3 col-lg-3">
                    <div class="d-flex gap-2">
                        <select name="location" class="form-select form-select-filter" aria-label="Filter by location" onchange="this.form.submit()">
                            <option value="all" {{ $locationFilter === 'all' ? 'selected' : '' }}>All Locations</option>
                            @foreach($distinctLocations as $loc)
                                <option value="{{ $loc }}" {{ $locationFilter === $loc ? 'selected' : '' }}>{{ $loc }}</option>
                            @endforeach
                        </select>

                        <button type="submit" class="btn btn-sm btn-outline-secondary px-3" title="Apply filter">
                            Filter
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    {{-- 4. MAIN CONTENT AREA: LIST VIEW VS MAP VIEW --}}

    {{-- A. LIST VIEW CONTAINER --}}
    <div id="propertiesListContainer" style="{{ $activeView === 'list' ? '' : 'display: none;' }}">
        @if($properties->count() > 0)
            {{-- Desktop Table (md and up) --}}
            <div class="table-container d-none d-md-block mb-3">
                <div class="table-responsive">
                    <table class="table properties-table align-middle">
                        <thead>
                            <tr>
                                <th scope="col" style="width: 28%;">Property</th>
                                <th scope="col" style="width: 22%;">Landlord</th>
                                <th scope="col" style="width: 22%;">Location</th>
                                <th scope="col" style="width: 16%;">Rooms & Occupancy</th>
                                <th scope="col" style="width: 12%;">Status</th>
                                <th scope="col" class="text-end" style="width: 85px;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($properties as $property)
                                <tr>
                                    {{-- Property Identity --}}
                                    <td>
                                        <div class="d-flex align-items-center gap-3">
                                            @if($property->image_path)
                                                <img src="{{ file_url($property->image_path) }}" alt="{{ $property->name }}" class="property-thumb">
                                            @else
                                                <div class="property-thumb-fallback">
                                                    <i class="bi bi-buildings"></i>
                                                </div>
                                            @endif
                                            <div>
                                                <a href="{{ route('admin.properties.show', $property) }}" class="property-title-link d-block lh-sm mb-1">
                                                    {{ $property->name }}
                                                </a>
                                                <span class="small text-muted">Added {{ $property->created_at?->format('M d, Y') ?? 'N/A' }}</span>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Landlord --}}
                                    <td>
                                        <div>
                                            <a href="{{ route('admin.users.landlords.show', $property->landlord_id) }}" class="landlord-name-link">
                                                {{ $property->landlord->full_name ?: $property->landlord->name }}
                                            </a>
                                            <div class="small text-muted">{{ $property->landlord->email }}</div>
                                        </div>
                                    </td>

                                    {{-- Location --}}
                                    <td>
                                        <div class="fw-medium text-truncate" style="max-width: 210px;" title="{{ $property->address }}">
                                            {{ $property->address ?: 'Address not set' }}
                                        </div>
                                        @if($property->latitude && $property->longitude)
                                            <button type="button"
                                                    class="btn btn-link btn-sm p-0 text-decoration-none small text-success fw-semibold mt-1"
                                                    onclick="focusProperty({{ $property->id }}, {{ $property->latitude }}, {{ $property->longitude }})">
                                                <i class="bi bi-geo-alt-fill me-1"></i>Locate on map
                                            </button>
                                        @else
                                            <span class="small text-muted"><i class="bi bi-dash me-1"></i>No coordinates</span>
                                        @endif
                                    </td>

                                    {{-- Rooms & Occupancy --}}
                                    <td>
                                        <div class="fw-semibold text-dark">
                                            {{ $property->occupied_rooms }} / {{ $property->total_rooms }} occupied
                                        </div>
                                        <div class="small text-muted mb-1">
                                            @if($property->total_rooms > 0)
                                                {{ $property->available_rooms }} available
                                            @else
                                                No rooms registered
                                            @endif
                                        </div>
                                        @if($property->total_rooms > 0)
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="occupancy-track-sm">
                                                    <div class="occupancy-fill-sm {{ $property->occupancy_rate >= 85 ? 'bg-danger' : ($property->occupancy_rate >= 60 ? 'bg-warning' : 'bg-success') }}"
                                                         style="width: {{ min($property->occupancy_rate, 100) }}%;"></div>
                                                </div>
                                                <span class="small text-muted fw-semibold">{{ $property->occupancy_rate }}%</span>
                                            </div>
                                        @endif
                                    </td>

                                    {{-- Approval Status --}}
                                    <td id="propertyStatusCell-{{ $property->id }}">
                                        @if(($property->approval_status ?? 'pending') === 'approved')
                                            <span class="badge rounded-pill bg-success-subtle text-success-emphasis border border-success-subtle px-2 py-1">
                                                <i class="bi bi-check-circle-fill me-1"></i>Approved
                                            </span>
                                        @elseif(($property->approval_status ?? 'pending') === 'rejected')
                                            <span class="badge rounded-pill bg-danger-subtle text-danger-emphasis border border-danger-subtle px-2 py-1"
                                                  title="{{ $property->rejection_reason ?? 'Rejected' }}">
                                                <i class="bi bi-x-circle-fill me-1"></i>Rejected
                                            </span>
                                        @else
                                            <span class="badge rounded-pill bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-1">
                                                <i class="bi bi-clock-fill me-1"></i>Pending Review
                                            </span>
                                        @endif
                                    </td>

                                    {{-- Action --}}
                                    <td class="text-end" id="propertyActionCell-{{ $property->id }}">
                                        @if(($property->approval_status ?? 'pending') === 'pending')
                                            <button type="button"
                                               class="btn-row-action btn-row-action-review"
                                               onclick="openPropertyInspector({{ $property->id }}, 'review')"
                                               title="Review property in inspector drawer">
                                                Review
                                            </button>
                                        @else
                                            <button type="button"
                                               class="btn-row-action btn-row-action-view"
                                               onclick="openPropertyInspector({{ $property->id }}, 'view')"
                                               title="View property details in inspector drawer">
                                                View
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Mobile Stacked Cards (below md) --}}
            <div class="d-md-none mb-3">
                @foreach($properties as $property)
                    <div class="property-card-mobile">
                        <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
                            <div class="d-flex align-items-center gap-2">
                                @if($property->image_path)
                                    <img src="{{ file_url($property->image_path) }}" alt="{{ $property->name }}" class="property-thumb">
                                @else
                                    <div class="property-thumb-fallback">
                                        <i class="bi bi-buildings"></i>
                                    </div>
                                @endif
                                <div>
                                    <a href="{{ route('admin.properties.show', $property) }}" class="property-title-link d-block lh-sm">
                                        {{ $property->name }}
                                    </a>
                                    <span class="small text-muted">{{ $property->address ?: 'Address not set' }}</span>
                                </div>
                            </div>

                            {{-- Status Badge --}}
                            <div id="propertyStatusMobile-{{ $property->id }}">
                                @if(($property->approval_status ?? 'pending') === 'approved')
                                    <span class="badge rounded-pill bg-success-subtle text-success-emphasis border border-success-subtle px-2 py-1">
                                        Approved
                                    </span>
                                @elseif(($property->approval_status ?? 'pending') === 'rejected')
                                    <span class="badge rounded-pill bg-danger-subtle text-danger-emphasis border border-danger-subtle px-2 py-1">
                                        Rejected
                                    </span>
                                @else
                                    <span class="badge rounded-pill bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-1">
                                        Pending
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="small bg-light rounded p-2 mb-2">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted">Landlord:</span>
                                <a href="{{ route('admin.users.landlords.show', $property->landlord_id) }}" class="landlord-name-link">
                                    {{ $property->landlord->full_name ?: $property->landlord->name }}
                                </a>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">Rooms & Capacity:</span>
                                <span class="fw-semibold text-dark">{{ $property->occupied_rooms }}/{{ $property->total_rooms }} occupied ({{ $property->available_rooms }} avail)</span>
                            </div>
                        </div>

                        <div class="d-flex align-items-center justify-content-between gap-2 pt-1">
                            @if($property->latitude && $property->longitude)
                                <button type="button" class="btn btn-sm btn-link text-decoration-none text-success p-0 small fw-semibold" onclick="focusProperty({{ $property->id }}, {{ $property->latitude }}, {{ $property->longitude }})">
                                    <i class="bi bi-geo-alt-fill me-1"></i>Locate on map
                                </button>
                            @else
                                <span></span>
                            @endif

                            <div id="propertyActionMobile-{{ $property->id }}">
                                @if(($property->approval_status ?? 'pending') === 'pending')
                                    <button type="button" class="btn-row-action btn-row-action-review" onclick="openPropertyInspector({{ $property->id }}, 'review')">
                                        Review
                                    </button>
                                @else
                                    <button type="button" class="btn-row-action btn-row-action-view" onclick="openPropertyInspector({{ $property->id }}, 'view')">
                                        View
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Pagination & Count Info --}}
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 pt-2">
                <div class="small text-muted">
                    Showing <span class="fw-semibold">{{ $properties->firstItem() ?? 0 }}</span> to <span class="fw-semibold">{{ $properties->lastItem() ?? 0 }}</span> of <span class="fw-semibold">{{ $properties->total() }}</span> properties
                </div>
                <div>
                    {{ $properties->links() }}
                </div>
            </div>
        @else
            {{-- Empty State --}}
            <div class="table-container empty-state-box">
                <div class="mb-3">
                    <i class="bi bi-buildings text-muted" style="font-size: 2.8rem;"></i>
                </div>
                @if($search !== '' || $statusFilter !== 'all' || $availabilityFilter !== 'all' || $locationFilter !== 'all')
                    <h5 class="fw-bold text-dark mb-1">No properties match your filters</h5>
                    <p class="small text-muted mb-3">Try modifying your search keywords or clearing the active status/location filters.</p>
                    <a href="{{ route('admin.properties.index', ['view' => $activeView]) }}" class="btn btn-sm btn-success rounded-pill px-3">
                        <i class="bi bi-arrow-counterclockwise me-1"></i> Reset Filters
                    </a>
                @else
                    <h5 class="fw-bold text-dark mb-1">No registered properties yet</h5>
                    <p class="small text-muted mb-0">Boarding house properties submitted by landlords will appear here for administrative monitoring.</p>
                @endif
            </div>
        @endif
    </div>

    {{-- B. MAP VIEW CONTAINER --}}
    <div id="propertiesMapContainer" style="{{ $activeView === 'map' ? '' : 'display: none;' }}">
        <div class="map-canvas-container">
            <div class="d-flex align-items-center justify-content-between p-3 border-bottom bg-light">
                <div class="fw-semibold text-dark small">
                    <i class="bi bi-geo-alt me-1 text-success"></i> Properties Location Map
                    <span class="badge rounded-pill bg-light text-muted border ms-2">{{ count($mapProperties) }} Mapped Locations</span>
                </div>
                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1" onclick="switchView('list')">
                    <i class="bi bi-list-ul me-1"></i> Back to List View
                </button>
            </div>
            <div id="properties-map" class="admin-map-area"></div>
        </div>
</div>

{{-- 5. PROPERTY INSPECTION DRAWER & ACTION MODALS PARTIAL --}}
@include('admin.properties.partials.property-inspector')

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    let map = null;
    let markers = [];
    const mapContainer = document.getElementById('properties-map');
    const propertiesData = @json($mapProperties);

    // Fallback Center: Calapan City / MCC Operating Area
    const DEFAULT_LAT = 13.4043;
    const DEFAULT_LNG = 121.1746;
    const DEFAULT_ZOOM = 13;

    function escapeHtml(val) {
        return String(val ?? '')
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }

    function formatPriceText(minPrice, maxPrice) {
        const nMin = minPrice !== null && minPrice !== undefined && minPrice !== '' ? Number(minPrice) : null;
        const nMax = maxPrice !== null && maxPrice !== undefined && maxPrice !== '' ? Number(maxPrice) : null;

        if (nMin === null && nMax === null) return 'Price TBD';
        if (nMin !== null && nMax !== null && nMin !== nMax) {
            return `₱${nMin.toLocaleString()} - ₱${nMax.toLocaleString()}`;
        }
        return `₱${(nMin !== null ? nMin : nMax).toLocaleString()}`;
    }

    function initMap() {
        if (map !== null || !mapContainer) return;

        map = L.map('properties-map').setView([DEFAULT_LAT, DEFAULT_LNG], DEFAULT_ZOOM);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxNativeZoom: 19,
            maxZoom: 22,
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        markers = [];

        propertiesData.forEach(function(property) {
            if (property.latitude && property.longitude) {
                const isPending = property.approval_status === 'pending';
                const markerClass = isPending ? 'price-marker-pill pending' : 'price-marker-pill';
                const markerLabel = isPending ? 'Pending Review' : formatPriceText(property.price_min, property.price_max);

                const icon = L.divIcon({
                    className: 'map-custom-div-icon',
                    html: `<div class="${markerClass}">${escapeHtml(markerLabel)}</div>`,
                    iconSize: [Math.max(76, Math.min(150, markerLabel.length * 8 + 18)), 26],
                    iconAnchor: [Math.round(Math.max(76, Math.min(150, markerLabel.length * 8 + 18)) / 2), 13],
                    popupAnchor: [0, -10]
                });

                const popupHtml = `
                    <div style="min-width: 210px; font-family: inherit;">
                        ${property.image_url ? `<div style="height: 100px; border-radius: 6px; overflow: hidden; margin-bottom: 6px;"><img src="${escapeHtml(property.image_url)}" style="width: 100%; height: 100%; object-fit: cover;"></div>` : ''}
                        <h6 style="font-weight: 700; margin-bottom: 2px; color: #0f172a;">${escapeHtml(property.name)}</h6>
                        <div style="font-size: 0.78rem; color: #64748b; margin-bottom: 6px;">${escapeHtml(property.address || 'Address not set')}</div>
                        <div style="font-size: 0.8rem; margin-bottom: 4px;">
                            <strong>${escapeHtml(property.occupied_rooms)}/${escapeHtml(property.total_rooms)}</strong> occupied • <strong>${escapeHtml(property.available_rooms)}</strong> available
                        </div>
                        <div style="font-size: 0.76rem; color: #64748b; margin-bottom: 8px;">
                            Landlord: ${escapeHtml(property.landlord_name)}
                        </div>
                        <a href="${escapeHtml(property.show_url)}" class="btn btn-sm btn-success w-100" style="font-size: 0.78rem; padding: 4px 8px; border-radius: 6px;">
                            View Property Details
                        </a>
                    </div>
                `;

                const marker = L.marker([property.latitude, property.longitude], { icon: icon })
                    .addTo(map)
                    .bindPopup(popupHtml);

                markers.push({
                    id: property.id,
                    marker: marker,
                    lat: property.latitude,
                    lng: property.longitude
                });
            }
        });

        if (markers.length > 0) {
            const group = new L.featureGroup(markers.map(m => m.marker));
            map.fitBounds(group.getBounds().pad(0.12));
        }
    }

    // View Switching logic
    window.switchView = function(viewMode) {
        const listCont = document.getElementById('propertiesListContainer');
        const mapCont = document.getElementById('propertiesMapContainer');
        const tabList = document.getElementById('tabBtnList');
        const tabMap = document.getElementById('tabBtnMap');
        const viewInput = document.getElementById('activeViewInput');

        if (viewMode === 'map') {
            if (listCont) listCont.style.display = 'none';
            if (mapCont) mapCont.style.display = 'block';
            if (tabList) tabList.classList.remove('active');
            if (tabMap) tabMap.classList.add('active');
            if (viewInput) viewInput.value = 'map';

            if (map === null) {
                initMap();
            } else {
                setTimeout(() => map.invalidateSize(), 150);
            }
        } else {
            if (mapCont) mapCont.style.display = 'none';
            if (listCont) listCont.style.display = 'block';
            if (tabMap) tabMap.classList.remove('active');
            if (tabList) tabList.classList.add('active');
            if (viewInput) viewInput.value = 'list';
        }
    };

    // Focus on property in Map View
    window.focusProperty = function(propertyId, lat, lng) {
        window.switchView('map');
        if (map && lat && lng) {
            setTimeout(function() {
                map.setView([lat, lng], 16);
                const item = markers.find(m => m.id === propertyId);
                if (item) {
                    item.marker.openPopup();
                }
            }, 200);
        }
    };

    // If initial view is map, initialize map immediately
    @if($activeView === 'map')
        initMap();
    @endif

    // ════════════════════════════════════════════════════════════════
    // PROPERTY INSPECTOR DRAWER & QUEUE WORKSPACE LOGIC
    // ════════════════════════════════════════════════════════════════
    let currentInspectorPropertyId = null;
    let pendingQueue = @json($pendingPropertyIds ?? []);
    let selectedDeepLinkProperty = {{ (int) ($selectedPropertyId ?? 0) }};
    let inspectorMiniMap = null;
    let inspectorMiniMarker = null;
    let inspectorLoadedData = null;

    // Internal Tabs Switcher
    window.switchInspectorTab = function(tabName) {
        document.querySelectorAll('.inspector-tab').forEach(btn => {
            btn.classList.toggle('active', btn.dataset.tab === tabName);
        });
        document.querySelectorAll('.inspector-tab-pane').forEach(pane => {
            pane.classList.add('d-none');
        });
        const activePane = document.getElementById('tabContent' + tabName.charAt(0).toUpperCase() + tabName.slice(1));
        if (activePane) {
            activePane.classList.remove('d-none');
        }

        if (tabName === 'location' && inspectorMiniMap) {
            setTimeout(() => inspectorMiniMap.invalidateSize(), 150);
        }
    };

    // Open Inspector Drawer
    window.openPropertyInspector = function(propertyId, mode = 'review') {
        currentInspectorPropertyId = Number(propertyId);

        const drawer = document.getElementById('propertyInspectorDrawer');
        const backdrop = document.getElementById('propertyInspectorBackdrop');
        if (!drawer || !backdrop) return;

        drawer.classList.add('open');
        drawer.setAttribute('aria-hidden', 'false');
        backdrop.classList.add('show');
        document.body.classList.add('overflow-hidden');

        // Update URL query param without reload to preserve all active filter/search/pagination states
        const url = new URL(window.location);
        url.searchParams.set('property', propertyId);
        window.history.replaceState({}, '', url);

        // Reset UI states to skeleton
        document.getElementById('inspectorLoadingState').classList.remove('d-none');
        document.getElementById('inspectorErrorState').classList.add('d-none');
        document.getElementById('inspectorContent').classList.add('d-none');
        window.switchInspectorTab('overview');

        // Queue navigation update
        updateQueueNavUI(currentInspectorPropertyId);

        // Fetch property details
        fetchInspectorData(currentInspectorPropertyId);
    };

    // Close Inspector Drawer
    window.closePropertyInspector = function() {
        const drawer = document.getElementById('propertyInspectorDrawer');
        const backdrop = document.getElementById('propertyInspectorBackdrop');
        if (drawer) {
            drawer.classList.remove('open');
            drawer.setAttribute('aria-hidden', 'true');
        }
        if (backdrop) backdrop.classList.remove('show');
        document.body.classList.remove('overflow-hidden');

        // Remove ?property param without reload
        const url = new URL(window.location);
        url.searchParams.delete('property');
        window.history.replaceState({}, '', url);

        currentInspectorPropertyId = null;
        inspectorLoadedData = null;
    };

    // Keyboard accessibility: Escape to close drawer
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const approveModal = document.getElementById('inspectorApproveModal');
            const rejectModal = document.getElementById('inspectorRejectModal');
            const isApproveOpen = approveModal && approveModal.classList.contains('show');
            const isRejectOpen = rejectModal && rejectModal.classList.contains('show');
            if (!isApproveOpen && !isRejectOpen) {
                closePropertyInspector();
            }
        }
    });

    // Update Queue Navigation UI
    function updateQueueNavUI(propertyId) {
        const queueNav = document.getElementById('inspectorQueueNav');
        const queuePos = document.getElementById('inspectorQueuePos');
        const prevBtn = document.getElementById('inspectorPrevBtn');
        const nextBtn = document.getElementById('inspectorNextBtn');

        const index = pendingQueue.indexOf(propertyId);
        if (index !== -1 && pendingQueue.length > 0) {
            queueNav.classList.remove('d-none');
            queuePos.textContent = `${index + 1} of ${pendingQueue.length}`;
            prevBtn.disabled = (index === 0);
            nextBtn.disabled = (index === pendingQueue.length - 1);
        } else {
            queueNav.classList.add('d-none');
        }
    }

    // Queue Navigation: previous or next pending property
    window.navigateInspectorQueue = function(delta) {
        const currentIndex = pendingQueue.indexOf(currentInspectorPropertyId);
        if (currentIndex === -1) return;

        const targetIndex = currentIndex + delta;
        if (targetIndex >= 0 && targetIndex < pendingQueue.length) {
            const nextId = pendingQueue[targetIndex];
            window.openPropertyInspector(nextId, 'review');
        }
    };

    // Fetch Property Inspection Data
    function fetchInspectorData(propertyId) {
        fetch(`/admin/properties/${propertyId}/inspect`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (!response.ok) throw new Error('Failed to load property');
            return response.json();
        })
        .then(data => {
            inspectorLoadedData = data;
            renderInspectorData(data);
        })
        .catch(err => {
            document.getElementById('inspectorLoadingState').classList.add('d-none');
            document.getElementById('inspectorErrorState').classList.remove('d-none');
            document.getElementById('inspectorErrorMessage').textContent = 'Unable to load property details. Please try again.';
        });
    }

    window.retryInspectorLoad = function() {
        if (currentInspectorPropertyId) {
            document.getElementById('inspectorLoadingState').classList.remove('d-none');
            document.getElementById('inspectorErrorState').classList.add('d-none');
            fetchInspectorData(currentInspectorPropertyId);
        }
    };

    // Render Property Inspection Data into Drawer
    function renderInspectorData(data) {
        document.getElementById('inspectorLoadingState').classList.add('d-none');
        document.getElementById('inspectorContent').classList.remove('d-none');

        // Header info
        document.getElementById('inspectorPropertyTitle').textContent = data.name || 'Property';
        document.getElementById('inspectorLandlordName').textContent = data.landlord?.name || 'Landlord';
        document.getElementById('inspectorLandlordContact').textContent = data.landlord?.email ? `• ${data.landlord.email}` : '';
        document.getElementById('inspectorFullDetailsLink').href = data.full_details_url || `/admin/properties/${data.id}`;

        // Status Badge in Header
        const badgeWrap = document.getElementById('inspectorStatusBadgeWrap');
        if (data.approval_status === 'approved') {
            badgeWrap.innerHTML = '<span class="badge rounded-pill bg-success-subtle text-success-emphasis border border-success-subtle px-2 py-1"><i class="bi bi-check-circle-fill me-1"></i>Approved</span>';
        } else if (data.approval_status === 'rejected') {
            badgeWrap.innerHTML = `<span class="badge rounded-pill bg-danger-subtle text-danger-emphasis border border-danger-subtle px-2 py-1"><i class="bi bi-x-circle-fill me-1"></i>Rejected</span>`;
        } else {
            badgeWrap.innerHTML = '<span class="badge rounded-pill bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-1"><i class="bi bi-clock-fill me-1"></i>Pending Review</span>';
        }

        // Tab counts & indicators
        document.getElementById('inspectorTabRoomsCount').textContent = data.rooms_summary?.total_rooms || 0;
        const complianceAlert = document.getElementById('inspectorComplianceAlert');
        if (complianceAlert) {
            complianceAlert.classList.toggle('d-none', !!data.compliance?.is_complete);
        }

        // Overview: Cover Image
        const coverImg = document.getElementById('inspectorCoverImg');
        const coverEmpty = document.getElementById('inspectorCoverEmpty');
        if (data.image_url) {
            coverImg.src = data.image_url;
            coverImg.classList.remove('d-none');
            coverEmpty.classList.add('d-none');
        } else {
            coverImg.classList.add('d-none');
            coverEmpty.classList.remove('d-none');
        }

        // Overview: Quick Check
        setQuickCheckPill('qcImage', data.quick_check?.image_uploaded, 'Facade Image', 'Missing');
        setQuickCheckPill('qcDescription', data.quick_check?.description_added, 'Description', 'Missing');
        setQuickCheckPill('qcCoordinates', data.quick_check?.coordinates_pinned, 'Coordinates', 'Missing');
        setQuickCheckPill('qcRooms', data.quick_check?.rooms_added, `${data.quick_check?.room_count || 0} Rooms`, 'No Rooms');

        // Overview: Address & Description
        document.getElementById('inspectorAddress').textContent = data.address || 'Address not set';
        document.getElementById('inspectorDescription').textContent = data.description || 'No description provided by landlord.';
        document.getElementById('inspectorSubmittedAt').textContent = data.created_at || 'N/A';

        // Overview: Building Inclusions
        const incWrap = document.getElementById('inspectorInclusionsWrap');
        const incCount = document.getElementById('inspectorInclusionsCount');
        if (data.building_inclusions && data.building_inclusions.length > 0) {
            incCount.textContent = data.building_inclusions.length;
            incWrap.innerHTML = `<div class="d-flex flex-wrap gap-1">${data.building_inclusions.map(inc => `<span class="badge text-bg-light border">${escapeHtml(inc)}</span>`).join('')}</div>`;
        } else {
            incCount.textContent = '0';
            incWrap.innerHTML = '<div class="text-muted small">No inclusions listed yet.</div>';
        }

        // Rooms Tab
        document.getElementById('inspectorTotalRooms').textContent = data.rooms_summary?.total_rooms || 0;
        document.getElementById('inspectorOccupancyRate').textContent = `${data.rooms_summary?.occupancy_rate || 0}%`;
        document.getElementById('inspectorOccupiedRooms').textContent = `${data.rooms_summary?.occupied_rooms || 0} occupied (${data.rooms_summary?.available_rooms || 0} avail)`;
        document.getElementById('inspectorPriceRange').textContent = formatPriceText(data.rooms_summary?.min_price, data.rooms_summary?.max_price);

        const roomsList = document.getElementById('inspectorRoomsList');
        const roomsEmpty = document.getElementById('inspectorRoomsEmpty');
        if (data.rooms && data.rooms.length > 0) {
            roomsEmpty.classList.add('d-none');
            roomsList.innerHTML = data.rooms.map(room => `
                <div class="room-card-mini">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <strong class="text-dark">Room ${escapeHtml(room.room_number)}</strong>
                        <span class="badge ${room.status === 'available' ? 'bg-success-subtle text-success' : 'bg-secondary text-white'}">${escapeHtml(room.status)}</span>
                    </div>
                    <div class="d-flex justify-content-between small text-muted">
                        <span>₱${Number(room.price || 0).toLocaleString()} / mo</span>
                        <span>Capacity: ${room.capacity} • Avail: ${room.available_slots}</span>
                    </div>
                    ${room.inclusions && room.inclusions.length > 0 ? `
                        <div class="d-flex flex-wrap gap-1 mt-2">
                            ${room.inclusions.map(inc => `<span class="badge text-bg-light border" style="font-size: 0.68rem;">${escapeHtml(inc)}</span>`).join('')}
                        </div>
                    ` : ''}
                </div>
            `).join('');
        } else {
            roomsList.innerHTML = '';
            roomsEmpty.classList.remove('d-none');
        }

        // Compliance Tab
        const bpBadge = document.getElementById('inspectorBpStatusBadge');
        const scBadge = document.getElementById('inspectorScStatusBadge');
        const compOverallBadge = document.getElementById('inspectorComplianceOverallBadge');

        setComplianceBadge(bpBadge, data.compliance?.business_permit);
        setComplianceBadge(scBadge, data.compliance?.safety_certificate);

        if (data.compliance?.is_complete) {
            compOverallBadge.className = 'badge rounded-pill bg-success-subtle text-success-emphasis border border-success-subtle';
            compOverallBadge.textContent = 'Compliant';
        } else {
            compOverallBadge.className = 'badge rounded-pill bg-warning-subtle text-warning-emphasis border border-warning-subtle';
            compOverallBadge.textContent = 'Incomplete';
        }

        if (data.landlord?.id) {
            document.getElementById('inspectorReviewPermitsLink').href = `/admin/users/landlords/${data.landlord.id}`;
        }

        // Location Tab
        const mapContainer = document.getElementById('inspectorMapContainer');
        const mapEmpty = document.getElementById('inspectorMapEmpty');
        document.getElementById('inspectorLocationFooter').textContent = data.address || 'Address not set';

        if (data.latitude && data.longitude) {
            mapEmpty.classList.add('d-none');
            mapContainer.classList.remove('d-none');
            initOrUpdateMiniMap(Number(data.latitude), Number(data.longitude), data.name);
        } else {
            mapContainer.classList.add('d-none');
            mapEmpty.classList.remove('d-none');
        }

        // Footer Decision Buttons vs Monitoring Mode
        const footerPending = document.getElementById('inspectorFooterPending');
        const footerGeneral = document.getElementById('inspectorFooterGeneral');
        if (data.approval_status === 'pending') {
            footerPending.classList.remove('d-none');
            footerGeneral.classList.add('d-none');
        } else {
            footerPending.classList.add('d-none');
            footerGeneral.classList.remove('d-none');
            document.getElementById('inspectorGeneralStatusMsg').textContent = `Property is currently ${data.approval_status}.`;
            document.getElementById('inspectorGeneralFullDetailsBtn').href = data.full_details_url || `/admin/properties/${data.id}`;
        }
    }

    function setQuickCheckPill(elementId, isOk, okText, missingText) {
        const pill = document.getElementById(elementId);
        if (!pill) return;
        if (isOk) {
            pill.className = 'quick-check-pill ok';
            pill.querySelector('.qc-indicator').innerHTML = '<i class="bi bi-check-circle-fill"></i>';
            pill.querySelector('.qc-title').textContent = okText;
        } else {
            pill.className = 'quick-check-pill missing';
            pill.querySelector('.qc-indicator').innerHTML = '<i class="bi bi-exclamation-triangle-fill"></i>';
            pill.querySelector('.qc-title').textContent = missingText;
        }
    }

    function setComplianceBadge(badgeEl, status) {
        if (!badgeEl) return;
        const norm = (status || 'missing').toLowerCase();
        if (norm === 'approved') {
            badgeEl.className = 'badge rounded-pill bg-success-subtle text-success-emphasis border border-success-subtle';
            badgeEl.textContent = 'Approved';
        } else if (norm === 'pending') {
            badgeEl.className = 'badge rounded-pill bg-warning-subtle text-warning-emphasis border border-warning-subtle';
            badgeEl.textContent = 'Pending Review';
        } else if (norm === 'rejected') {
            badgeEl.className = 'badge rounded-pill bg-danger-subtle text-danger-emphasis border border-danger-subtle';
            badgeEl.textContent = 'Rejected';
        } else {
            badgeEl.className = 'badge rounded-pill bg-light text-muted border';
            badgeEl.textContent = 'Missing';
        }
    }

    function initOrUpdateMiniMap(lat, lng, name) {
        const container = document.getElementById('inspectorMapContainer');
        if (!container) return;

        if (inspectorMiniMap === null) {
            inspectorMiniMap = L.map('inspectorMapContainer').setView([lat, lng], 16);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap'
            }).addTo(inspectorMiniMap);
            inspectorMiniMarker = L.marker([lat, lng]).addTo(inspectorMiniMap);
        } else {
            inspectorMiniMap.setView([lat, lng], 16);
            if (inspectorMiniMarker) {
                inspectorMiniMarker.setLatLng([lat, lng]);
            } else {
                inspectorMiniMarker = L.marker([lat, lng]).addTo(inspectorMiniMap);
            }
            setTimeout(() => inspectorMiniMap.invalidateSize(), 150);
        }
    }

    // Modal Triggers
    window.openInspectorApproveModal = function() {
        if (!inspectorLoadedData) return;
        document.getElementById('approveModalPropName').textContent = inspectorLoadedData.name;
        document.getElementById('approveModalLandlordName').textContent = inspectorLoadedData.landlord?.name || 'Landlord';
        const modal = new bootstrap.Modal(document.getElementById('inspectorApproveModal'));
        modal.show();
    };

    window.openInspectorRejectModal = function() {
        if (!inspectorLoadedData) return;
        document.getElementById('rejectModalPropName').textContent = inspectorLoadedData.name;
        document.getElementById('rejectReasonInput').value = '';
        document.getElementById('rejectReasonError').classList.add('d-none');
        const modal = new bootstrap.Modal(document.getElementById('inspectorRejectModal'));
        modal.show();
    };

    // Show in-page Toast
    function showInspectorToast(message, isSuccess = true) {
        const toastEl = document.getElementById('inspectorToast');
        const toastMsg = document.getElementById('inspectorToastMsg');
        if (!toastEl || !toastMsg) return;

        toastEl.className = `toast align-items-center text-white border-0 shadow-lg rounded-3 ${isSuccess ? 'bg-success' : 'bg-danger'}`;
        toastMsg.textContent = message;
        const toast = new bootstrap.Toast(toastEl, { delay: 4000 });
        toast.show();
    }

    // Submit Approval via AJAX
    window.submitInspectorApproval = function() {
        if (!currentInspectorPropertyId) return;

        const btn = document.getElementById('confirmApproveBtn');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Approving...';

        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';

        fetch(`/admin/properties/${currentInspectorPropertyId}/approve`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (!response.ok) throw new Error('Server error');
            return response.json();
        })
        .then(data => {
            btn.disabled = false;
            btn.textContent = 'Confirm Approval';

            const modalEl = document.getElementById('inspectorApproveModal');
            const modal = bootstrap.Modal.getInstance(modalEl);
            if (modal) modal.hide();

            handleDecisionSuccess(currentInspectorPropertyId, 'approved', data.message || 'Property approved successfully.');
        })
        .catch(err => {
            btn.disabled = false;
            btn.textContent = 'Confirm Approval';
            alert('Approval failed. Please try again.');
        });
    };

    // Submit Rejection via AJAX
    window.submitInspectorRejection = function() {
        if (!currentInspectorPropertyId) return;

        const reason = document.getElementById('rejectReasonInput').value.trim();
        const errorEl = document.getElementById('rejectReasonError');
        if (!reason) {
            errorEl.classList.remove('d-none');
            return;
        }
        errorEl.classList.add('d-none');

        const btn = document.getElementById('confirmRejectBtn');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Rejecting...';

        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';

        fetch(`/admin/properties/${currentInspectorPropertyId}/reject`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ rejection_reason: reason })
        })
        .then(response => {
            if (!response.ok) throw new Error('Server error');
            return response.json();
        })
        .then(data => {
            btn.disabled = false;
            btn.textContent = 'Confirm Rejection';

            const modalEl = document.getElementById('inspectorRejectModal');
            const modal = bootstrap.Modal.getInstance(modalEl);
            if (modal) modal.hide();

            handleDecisionSuccess(currentInspectorPropertyId, 'rejected', data.message || 'Property rejected successfully.');
        })
        .catch(err => {
            btn.disabled = false;
            btn.textContent = 'Confirm Rejection';
            alert('Rejection failed. Please try again.');
        });
    };

    // Post-decision State & Queue Progression
    function handleDecisionSuccess(resolvedId, newStatus, message) {
        // 1. Update row badges in desktop table and mobile card
        updateLocalRowStatus(resolvedId, newStatus);

        // 2. Decrement pending queue and KPI counts
        const index = pendingQueue.indexOf(resolvedId);
        if (index !== -1) {
            pendingQueue.splice(index, 1);
        }

        updateLocalKPICounts(newStatus);

        // 3. Show feedback toast
        showInspectorToast(message, true);

        // 4. Auto-advance if reviewing pending queue
        if (pendingQueue.length > 0) {
            const nextIndex = Math.min(index, pendingQueue.length - 1);
            const nextId = pendingQueue[nextIndex];
            setTimeout(() => {
                window.openPropertyInspector(nextId, 'review');
            }, 600);
        } else {
            // Queue is now empty!
            setTimeout(() => {
                closePropertyInspector();
                showInspectorToast('All pending property reviews completed!', true);
            }, 800);
        }
    }

    function updateLocalRowStatus(id, newStatus) {
        const statusCell = document.getElementById(`propertyStatusCell-${id}`);
        const statusMobile = document.getElementById(`propertyStatusMobile-${id}`);
        const actionCell = document.getElementById(`propertyActionCell-${id}`);
        const actionMobile = document.getElementById(`propertyActionMobile-${id}`);

        let badgeHtml = '';
        if (newStatus === 'approved') {
            badgeHtml = '<span class="badge rounded-pill bg-success-subtle text-success-emphasis border border-success-subtle px-2 py-1"><i class="bi bi-check-circle-fill me-1"></i>Approved</span>';
        } else {
            badgeHtml = '<span class="badge rounded-pill bg-danger-subtle text-danger-emphasis border border-danger-subtle px-2 py-1"><i class="bi bi-x-circle-fill me-1"></i>Rejected</span>';
        }

        if (statusCell) statusCell.innerHTML = badgeHtml;
        if (statusMobile) statusMobile.innerHTML = badgeHtml;

        const viewBtnHtml = `<button type="button" class="btn-row-action btn-row-action-view" onclick="openPropertyInspector(${id}, 'view')">View</button>`;
        if (actionCell) actionCell.innerHTML = viewBtnHtml;
        if (actionMobile) actionMobile.innerHTML = viewBtnHtml;
    }

    function updateLocalKPICounts(newStatus) {
        const pendingCountEl = document.getElementById('tabCountPending');
        const kpiPendingEl = document.getElementById('kpiPendingProperties');
        const headerBadgeEl = document.getElementById('headerPendingBadge');

        const currentPending = pendingQueue.length;
        if (pendingCountEl) pendingCountEl.textContent = currentPending;
        if (kpiPendingEl) kpiPendingEl.textContent = currentPending;
        if (headerBadgeEl) {
            if (currentPending > 0) {
                headerBadgeEl.textContent = currentPending;
            } else {
                headerBadgeEl.remove();
            }
        }

        if (newStatus === 'approved') {
            const approvedCountEl = document.getElementById('tabCountApproved');
            const kpiApprovedEl = document.getElementById('kpiApprovedProperties');
            if (approvedCountEl) approvedCountEl.textContent = Number(approvedCountEl.textContent.replace(/,/g, '') || 0) + 1;
            if (kpiApprovedEl) kpiApprovedEl.textContent = Number(kpiApprovedEl.textContent.replace(/,/g, '') || 0) + 1;
        } else if (newStatus === 'rejected') {
            const rejectedCountEl = document.getElementById('tabCountRejected');
            if (rejectedCountEl) rejectedCountEl.textContent = Number(rejectedCountEl.textContent.replace(/,/g, '') || 0) + 1;
        }
    }

    // Auto-open if deep-link ?property={id} was present
    if (selectedDeepLinkProperty > 0) {
        setTimeout(() => {
            window.openPropertyInspector(selectedDeepLinkProperty, 'review');
        }, 120);
    }
});
</script>
@endsection