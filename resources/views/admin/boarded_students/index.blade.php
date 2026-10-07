@extends('layouts.admin')

@section('title', 'Boarding Monitoring · Students & Academic Reporting')

@section('content')
<style>
    :root {
        --obhs-bg: #f8fafc;
        --obhs-surface: #ffffff;
        --obhs-surface-soft: #f8fafc;
        --obhs-surface-tint: rgba(22, 101, 52, 0.04);
        --obhs-border: rgba(15, 23, 42, 0.08);
        --obhs-border-subtle: rgba(15, 23, 42, 0.04);
        --obhs-border-focus: rgba(22, 101, 52, 0.35);

        --obhs-text: #0f172a;
        --obhs-text-secondary: #475569;
        --obhs-text-muted: #64748b;
        --obhs-text-faint: #94a3b8;

        --obhs-green: #166534;
        --obhs-green-dark: #14532d;
        --obhs-green-soft: rgba(22, 101, 52, 0.08);
        --obhs-green-border: rgba(22, 101, 52, 0.18);

        --obhs-blue: #0284c7;
        --obhs-blue-soft: rgba(2, 132, 199, 0.08);
        --obhs-amber: #d97706;
        --obhs-red: #dc2626;

        --obhs-radius-sm: 8px;
        --obhs-radius-md: 12px;
        --obhs-radius-lg: 16px;
        --obhs-radius-xl: 20px;

        --obhs-shadow-sm: 0 1px 2px rgba(15, 23, 42, 0.04);
        --obhs-shadow-md: 0 4px 16px rgba(15, 23, 42, 0.03), 0 1px 3px rgba(15, 23, 42, 0.02);
        --obhs-shadow-lg: 0 10px 30px rgba(15, 23, 42, 0.04), 0 2px 6px rgba(15, 23, 42, 0.02);
    }

    .monitoring-shell {
        background: var(--obhs-surface);
        border: 1px solid var(--obhs-border);
        border-radius: var(--obhs-radius-lg);
        box-shadow: var(--obhs-shadow-md);
        padding: 1.5rem;
    }

    .monitoring-breadcrumb {
        font-size: 0.78rem;
        font-weight: 600;
        letter-spacing: 0.01em;
        color: var(--obhs-text-muted);
        margin-bottom: 0.25rem;
    }

    .monitoring-breadcrumb a {
        color: var(--obhs-text-muted);
        text-decoration: none;
        transition: color 0.15s ease;
    }

    .monitoring-breadcrumb a:hover {
        color: var(--obhs-green);
    }

    .monitoring-title {
        font-size: 1.65rem;
        font-weight: 800;
        color: var(--obhs-text);
        letter-spacing: -0.02em;
        line-height: 1.2;
    }

    .monitoring-subtitle {
        font-size: 0.88rem;
        color: var(--obhs-text-secondary);
        margin-bottom: 0;
    }

    /* ── Metric Chips & Strip ── */
    .metric-chip {
        background: var(--obhs-surface-soft);
        border: 1px solid var(--obhs-border-subtle);
        border-radius: var(--obhs-radius-md);
        padding: 0.9rem 1.15rem;
        height: 100%;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }

    .metric-chip:hover {
        transform: translateY(-1px);
        box-shadow: var(--obhs-shadow-sm);
    }

    .metric-chip-label {
        font-size: 0.76rem;
        font-weight: 600;
        color: var(--obhs-text-muted);
        margin-bottom: 0.35rem;
    }

    .metric-chip-value {
        font-size: 1.5rem;
        font-weight: 800;
        color: var(--obhs-text);
        line-height: 1.1;
        letter-spacing: -0.02em;
    }

    .metric-chip-sub {
        font-size: 0.75rem;
        color: var(--obhs-text-faint);
        margin-top: 0.3rem;
    }

    /* ── Unified Stat Strip (Analytics) ── */
    .stat-strip-container {
        background: var(--obhs-surface-soft);
        border: 1px solid var(--obhs-border);
        border-radius: var(--obhs-radius-lg);
        padding: 1.15rem 1.5rem;
        margin-bottom: 1.5rem;
    }

    .stat-strip-item {
        padding: 0.5rem 1rem;
        position: relative;
    }

    .stat-strip-item:not(:last-child)::after {
        content: '';
        position: absolute;
        right: 0;
        top: 15%;
        height: 70%;
        width: 1px;
        background: var(--obhs-border);
    }

    @media (max-width: 767.98px) {
        .stat-strip-item:not(:last-child)::after {
            display: none;
        }
        .stat-strip-item {
            padding: 0.6rem 0.5rem;
            border-bottom: 1px solid var(--obhs-border-subtle);
        }
    }

    .stat-strip-number {
        font-size: 1.85rem;
        font-weight: 800;
        color: var(--obhs-text);
        line-height: 1;
        letter-spacing: -0.03em;
        margin-bottom: 0.3rem;
    }

    .stat-strip-title {
        font-size: 0.85rem;
        font-weight: 700;
        color: var(--obhs-text);
        margin-bottom: 0.15rem;
    }

    .stat-strip-meta {
        font-size: 0.75rem;
        color: var(--obhs-text-muted);
    }

    /* ── Workspace Tabs ── */
    .workspace-tabs {
        display: flex;
        flex-wrap: wrap;
        gap: 0.4rem;
        border-bottom: 1px solid var(--obhs-border);
        padding-bottom: 0.75rem;
        margin-bottom: 1.5rem;
    }

    .workspace-tabs .nav-link {
        border: 1px solid var(--obhs-border);
        border-radius: var(--obhs-radius-sm);
        background: var(--obhs-surface);
        color: var(--obhs-text-secondary);
        font-size: 0.84rem;
        font-weight: 600;
        padding: 0.45rem 1rem;
        transition: all 0.15s ease;
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        text-decoration: none;
    }

    .workspace-tabs .nav-link:hover {
        background: var(--obhs-surface-soft);
        color: var(--obhs-text);
        border-color: rgba(15, 23, 42, 0.15);
    }

    .workspace-tabs .nav-link.active {
        background: var(--obhs-green-soft);
        border-color: var(--obhs-green-border);
        color: var(--obhs-green-dark);
        font-weight: 700;
    }

    .workspace-tabs .nav-link .badge {
        font-size: 0.72rem;
        font-weight: 700;
        border-radius: 999px;
    }

    /* ── Content Surfaces ── */
    .surface-card {
        border: 1px solid var(--obhs-border);
        border-radius: var(--obhs-radius-lg);
        background: var(--obhs-surface);
        box-shadow: var(--obhs-shadow-sm);
        overflow: hidden;
        transition: box-shadow 0.15s ease;
    }

    .surface-card:hover {
        box-shadow: var(--obhs-shadow-md);
    }

    .surface-card-hero {
        border: 1px solid var(--obhs-border);
        border-radius: var(--obhs-radius-xl);
        background: linear-gradient(180deg, #ffffff 0%, #fcfdfd 100%);
        box-shadow: var(--obhs-shadow-md);
        padding: 1.5rem;
    }

    .surface-header-clean {
        padding: 0 0 1rem 0;
        border-bottom: 1px solid var(--obhs-border-subtle);
        margin-bottom: 1.15rem;
    }

    .surface-header {
        border-bottom: 1px solid var(--obhs-border-subtle);
        background: var(--obhs-surface-soft);
        padding: 0.85rem 1.15rem;
        font-weight: 700;
        font-size: 0.88rem;
        color: var(--obhs-text);
    }

    .surface-body {
        padding: 1.15rem;
    }

    /* ── Filter Toolbar ── */
    .analytics-toolbar {
        background: var(--obhs-surface-soft);
        border: 1px solid var(--obhs-border);
        border-radius: var(--obhs-radius-lg);
        padding: 0.9rem 1.15rem;
        margin-bottom: 1.5rem;
    }

    .toolbar-field-label {
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: var(--obhs-text-muted);
        margin-bottom: 0.3rem;
        display: block;
    }

    .toolbar-select {
        height: 44px;
        background-color: #ffffff;
        border: 1px solid var(--obhs-border);
        border-radius: var(--obhs-radius-sm);
        font-size: 0.86rem;
        font-weight: 500;
        color: var(--obhs-text);
        padding: 0 0.85rem;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }

    .toolbar-select:focus {
        border-color: var(--obhs-green);
        box-shadow: 0 0 0 3px var(--obhs-green-soft);
        outline: none;
    }

    .btn-obhs-primary {
        height: 44px;
        background-color: var(--obhs-green);
        border: 1px solid var(--obhs-green);
        border-radius: var(--obhs-radius-sm);
        color: #ffffff;
        font-size: 0.86rem;
        font-weight: 700;
        padding: 0 1.25rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.4rem;
        transition: background-color 0.15s ease, border-color 0.15s ease;
    }

    .btn-obhs-primary:hover {
        background-color: var(--obhs-green-dark);
        border-color: var(--obhs-green-dark);
        color: #ffffff;
    }

    .btn-obhs-subtle {
        height: 44px;
        background-color: #ffffff;
        border: 1px solid var(--obhs-border);
        border-radius: var(--obhs-radius-sm);
        color: var(--obhs-text-secondary);
        font-size: 0.86rem;
        font-weight: 600;
        padding: 0 1rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: all 0.15s ease;
        text-decoration: none;
    }

    .btn-obhs-subtle:hover {
        background-color: var(--obhs-surface-soft);
        color: var(--obhs-text);
        border-color: rgba(15, 23, 42, 0.15);
    }

    .filter-toolbar-box {
        background: var(--obhs-surface-soft);
        border: 1px solid var(--obhs-border);
        border-radius: var(--obhs-radius-md);
        padding: 0.85rem 1rem;
        margin-bottom: 1.25rem;
    }

    .active-filters-strip {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.4rem;
        font-size: 0.78rem;
        margin-top: 0.65rem;
        padding-top: 0.65rem;
        border-top: 1px dashed var(--obhs-border);
    }

    .filter-pill {
        background: #ffffff;
        border: 1px solid var(--obhs-green-border);
        color: var(--obhs-green-dark);
        font-weight: 600;
        border-radius: 999px;
        padding: 0.18rem 0.55rem;
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
    }

    /* ── Ranked Monitoring Items (Custom Visualizations) ── */
    .ranked-list {
        display: flex;
        flex-direction: column;
        gap: 0.85rem;
    }

    .ranked-item {
        display: flex;
        align-items: center;
        gap: 0.85rem;
    }

    .ranked-num {
        font-size: 0.8rem;
        font-weight: 800;
        font-family: monospace;
        color: var(--obhs-text-faint);
        min-width: 24px;
    }

    .ranked-info {
        flex: 1;
        min-width: 0;
    }

    .ranked-header {
        display: flex;
        justify-content: space-between;
        align-items: baseline;
        margin-bottom: 0.25rem;
    }

    .ranked-title {
        font-size: 0.86rem;
        font-weight: 700;
        color: var(--obhs-text);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .ranked-subtitle {
        font-size: 0.74rem;
        color: var(--obhs-text-muted);
    }

    .ranked-value {
        font-size: 0.82rem;
        font-weight: 700;
        color: var(--obhs-text);
    }

    .ranked-track {
        height: 6px;
        background: var(--obhs-surface-soft);
        border-radius: 999px;
        overflow: hidden;
        border: 1px solid var(--obhs-border-subtle);
    }

    .ranked-bar {
        height: 100%;
        background: var(--obhs-green);
        border-radius: 999px;
        transition: width 0.4s ease;
    }

    .ranked-bar.blue {
        background: var(--obhs-blue);
    }

    /* ── Segmented Tab Switcher (Detailed Breakdown) ── */
    .segmented-control {
        display: inline-flex;
        background: var(--obhs-surface-soft);
        border: 1px solid var(--obhs-border);
        border-radius: var(--obhs-radius-sm);
        padding: 3px;
        gap: 2px;
    }

    .segmented-btn {
        background: transparent;
        border: none;
        border-radius: 6px;
        padding: 0.35rem 0.85rem;
        font-size: 0.8rem;
        font-weight: 600;
        color: var(--obhs-text-secondary);
        cursor: pointer;
        transition: all 0.15s ease;
    }

    .segmented-btn:hover {
        color: var(--obhs-text);
    }

    .segmented-btn.active {
        background: #ffffff;
        color: var(--obhs-green-dark);
        font-weight: 700;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.08);
    }

    /* ── Modern Table Styles ── */
    .data-table-clean {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }

    .data-table-clean thead th {
        font-size: 0.74rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: var(--obhs-text-muted);
        background: var(--obhs-surface-soft);
        border-bottom: 1px solid var(--obhs-border);
        padding: 0.85rem 1rem;
        white-space: nowrap;
    }

    .data-table-clean tbody tr {
        transition: background-color 0.12s ease;
    }

    .data-table-clean tbody tr:hover {
        background-color: var(--obhs-surface-soft);
    }

    .data-table-clean tbody td {
        padding: 0.85rem 1rem;
        font-size: 0.86rem;
        vertical-align: middle;
        border-bottom: 1px solid var(--obhs-border-subtle);
        color: var(--obhs-text);
    }

    .data-table-clean tbody tr:last-child td {
        border-bottom: none;
    }

    .table thead th {
        font-size: 0.74rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--obhs-text-muted);
        background: var(--obhs-surface-soft);
        border-bottom: 1px solid var(--obhs-border);
        padding: 0.75rem 0.95rem;
        white-space: nowrap;
    }

    .table tbody td {
        padding: 0.75rem 0.95rem;
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
        background: var(--obhs-green-soft);
        border: 1px solid var(--obhs-green-border);
        color: var(--obhs-green-dark);
        font-weight: 700;
        font-size: 0.82rem;
        flex-shrink: 0;
    }

    .monitoring-status {
        display: inline-flex;
        align-items: center;
        border-radius: 999px;
        padding: 0.22rem 0.6rem;
        font-size: 0.74rem;
        font-weight: 700;
        white-space: nowrap;
    }

    .status-active { background: #dcfce7; color: #166534; border: 1px solid rgba(22, 101, 52, 0.22); }
    .status-checked_out { background: #f1f5f9; color: #475569; border: 1px solid rgba(71, 85, 105, 0.22); }
    .status-pending { background: #fef3c7; color: #92400e; border: 1px solid rgba(146, 64, 14, 0.22); }
    .status-cancelled { background: #fee2e2; color: #991b1b; border: 1px solid rgba(153, 27, 27, 0.22); }

    .empty-state-compact {
        padding: 2.5rem 1rem;
        text-align: center;
        color: var(--obhs-text-muted);
    }

    .insight-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        background: var(--obhs-green-soft);
        color: var(--obhs-green-dark);
        font-size: 0.76rem;
        font-weight: 600;
        padding: 0.25rem 0.65rem;
        border-radius: var(--obhs-radius-sm);
        border: 1px solid var(--obhs-green-border);
    }

    @media (max-width: 575.98px) {
        .monitoring-shell {
            padding: 1rem;
        }
    }
</style>

@php
    $activeTab = $activeTab ?? 'current';
    $isCurrentTab = ($activeTab === 'current');
    $isHistoryTab = ($activeTab === 'history');
    $isAnalyticsTab = ($activeTab === 'analytics');

    $activeFilterCount = 0;
    if (filled($search)) $activeFilterCount++;
    if (filled($boardingHouse)) $activeFilterCount++;
    if (filled($college)) $activeFilterCount++;
    if (filled($program)) $activeFilterCount++;
    if ($statusFilter !== 'all' && $statusFilter !== 'active') $activeFilterCount++;
    if ($periodPreset !== 'all') $activeFilterCount++;
    if ($dateBasis !== 'stay') $activeFilterCount++;
@endphp

<div class="monitoring-shell">
    {{-- Top Navigation Breadcrumb --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
        <nav aria-label="breadcrumb">
            <div class="monitoring-breadcrumb">
                <a href="{{ route('admin.dashboard') }}">Admin</a>
                <span class="mx-1 text-muted">/</span>
                <span class="text-muted">Monitoring</span>
                <span class="mx-1 text-muted">/</span>
                <span class="text-dark">Active Boarders</span>
            </div>
        </nav>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('admin.bookings.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3" title="View raw transactional bookings">
                <i class="bi bi-journal-check me-1"></i>All Bookings Table
            </a>
        </div>
    </div>

    {{-- Clean Page Header --}}
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3 pb-3 border-bottom">
        <div>
            <h1 class="monitoring-title mb-1">Active Boarders</h1>
            <p class="text-secondary small mb-0">Monitor students currently residing in registered boarding houses, review stay history, and generate institutional reports.</p>
        </div>
    </div>

    {{-- Top-Level Workspace Tabs --}}
    <ul class="nav workspace-tabs" id="monitoringWorkspaceTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <a href="{{ route('admin.boarding_monitoring.students', ['tab' => 'current']) }}" class="nav-link {{ $isCurrentTab ? 'active' : '' }}" id="tab-current-link">
                <i class="bi bi-person-check me-1"></i>Current Boarders
                <span class="badge {{ $isCurrentTab ? 'bg-white text-dark' : 'bg-success-subtle text-success' }} ms-1">{{ $currentBoardersCount }}</span>
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <a href="{{ route('admin.boarding_monitoring.students', ['tab' => 'history']) }}" class="nav-link {{ $isHistoryTab ? 'active' : '' }}" id="tab-history-link">
                <i class="bi bi-clock-history me-1"></i>Stay History &amp; Reports
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <a href="{{ route('admin.boarding_monitoring.students', ['tab' => 'analytics']) }}" class="nav-link {{ $isAnalyticsTab ? 'active' : '' }}" id="tab-analytics-link">
                <i class="bi bi-graph-up me-1"></i>Analytics
            </a>
        </li>
    </ul>

    {{-- ========================================================================= --}}
    {{-- PART A: CURRENT BOARDERS TAB --}}
    {{-- ========================================================================= --}}
    @if($isCurrentTab)
        {{-- Compact Metrics Bar --}}
        <div class="row g-2 mb-3">
            <div class="col-6 col-md-3">
                <div class="metric-chip">
                    <div class="metric-chip-label">Active Students</div>
                    <div class="metric-chip-value">{{ number_format($activeTenants) }}</div>
                    <div class="metric-chip-sub">Currently occupying</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="metric-chip">
                    <div class="metric-chip-label">Active Properties</div>
                    <div class="metric-chip-value">{{ number_format($activeProperties) }}</div>
                    <div class="metric-chip-sub">Houses with tenants</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="metric-chip">
                    <div class="metric-chip-label">Occupied Rooms</div>
                    <div class="metric-chip-value">{{ number_format($activeRooms) }}</div>
                    <div class="metric-chip-sub">Rooms in active use</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="metric-chip">
                    <div class="metric-chip-label">Available Capacity</div>
                    <div class="metric-chip-value fs-5 text-success">{{ max(0, $totalRoomsCount - $activeRooms) }} rooms</div>
                    <div class="metric-chip-sub">Open for student placement</div>
                </div>
            </div>
        </div>

        {{-- Primary Filter Toolbar --}}
        <div class="filter-toolbar-box">
            <form method="GET" action="{{ route('admin.boarding_monitoring.students') }}" class="row g-2 align-items-end">
                <input type="hidden" name="tab" value="current">

                <div class="col-12 col-md-5 col-xl-5">
                    <label class="form-label small fw-bold text-muted mb-1">Search Boarder</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" class="form-control" name="search" value="{{ $search }}" placeholder="Student name, student ID, room...">
                    </div>
                </div>

                <div class="col-12 col-md-4 col-xl-3">
                    <label class="form-label small fw-bold text-muted mb-1">Boarding House</label>
                    <select name="boarding_house" class="form-select form-select-sm">
                        <option value="">All Boarding Houses</option>
                        @foreach($boardingHouses as $bh)
                            <option value="{{ $bh->id }}" @selected((string) $boardingHouse === (string) $bh->id)>{{ $bh->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-md-3 col-xl-2">
                    <label class="form-label small fw-bold text-muted mb-1">College</label>
                    <select name="college" class="form-select form-select-sm">
                        <option value="">All Colleges</option>
                        @foreach($colleges as $c)
                            <option value="{{ $c['code'] }}" @selected($college === $c['code'])>{{ $c['code'] }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-md-12 col-xl-2 d-flex gap-2">
                    <button type="submit" class="btn btn-sm btn-success rounded-pill px-3 flex-fill">
                        <i class="bi bi-funnel me-1"></i>Filter
                    </button>
                    @if(filled($search) || filled($boardingHouse) || filled($college))
                        <a href="{{ route('admin.boarding_monitoring.students', ['tab' => 'current']) }}" class="btn btn-sm btn-outline-secondary rounded-pill">Reset</a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Current Boarders Table --}}
        <div class="surface-card">
            <div class="surface-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-person-check me-1.5 text-success"></i>Current Active Boarders</span>
                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill">{{ $boardedStudents->total() }} active</span>
            </div>

            @if($boardedStudents->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="ps-3">Student</th>
                                <th>Boarding House &amp; Room</th>
                                <th>Academic</th>
                                <th>Check-In</th>
                                <th>Status</th>
                                <th class="pe-3 text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($boardedStudents as $boarding)
                                <tr>
                                    <td class="ps-3">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="avatar-circle">{{ strtoupper(substr($boarding->student?->full_name ?? $boarding->student?->name ?? 'U', 0, 1)) }}</span>
                                            <div class="min-w-0">
                                                <a href="{{ $boarding->student ? route('admin.users.students.show', $boarding->student->id) : '#' }}" class="fw-bold text-dark text-decoration-none hover-success">
                                                    {{ $boarding->student?->full_name ?? $boarding->student?->name ?? 'Unknown Student' }}
                                                </a>
                                                <div class="small text-muted">
                                                    @if(!empty($boarding->student?->student_id))
                                                        <span class="font-monospace">{{ $boarding->student->student_id }}</span> •
                                                    @endif
                                                    {{ $boarding->student?->email ?? 'No email' }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @if($boarding->room?->property)
                                            <a href="{{ route('admin.properties.show', $boarding->room->property->id) }}" class="fw-semibold text-dark text-decoration-none hover-success">
                                                {{ $boarding->room->property->name }}
                                            </a>
                                        @else
                                            <span class="fw-semibold text-dark">N/A</span>
                                        @endif
                                        <div class="small text-muted">
                                            <span class="badge bg-light text-dark border">Room {{ $boarding->room?->room_number ?? $boarding->room_id }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border">{{ $boarding->student?->college ?: 'Unspecified College' }}</span>
                                        <div class="small text-muted text-truncate" style="max-width: 200px;">{{ $boarding->student?->program ?: 'Unspecified Program' }}</div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-dark">{{ $boarding->check_in ? $boarding->check_in->format('M d, Y') : 'N/A' }}</div>
                                        <div class="small text-muted">Check-out: {{ $boarding->check_out ? $boarding->check_out->format('M d, Y') : 'Open' }}</div>
                                    </td>
                                    <td>
                                        <span class="monitoring-status status-active">Active</span>
                                    </td>
                                    <td class="pe-3 text-end">
                                        @if($boarding->student)
                                            <a href="{{ route('admin.users.students.show', $boarding->student->id) }}" class="btn btn-sm btn-outline-success rounded-pill px-3">
                                                <i class="bi bi-person me-1"></i>View
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($boardedStudents->hasPages())
                    <div class="p-3 border-top d-flex justify-content-between align-items-center">
                        <span class="small text-muted">Showing {{ $boardedStudents->firstItem() }} to {{ $boardedStudents->lastItem() }} of {{ $boardedStudents->total() }} results</span>
                        <div>{{ $boardedStudents->links() }}</div>
                    </div>
                @endif
            @else
                <div class="empty-state-compact">
                    <i class="bi bi-person-x fs-2 text-secondary mb-2 d-block"></i>
                    <h6 class="fw-bold text-dark mb-1">No students are currently boarding.</h6>
                    <p class="small text-muted mb-3">There are {{ number_format($totalRecords) }} historical stay records available in the system archive.</p>
                    <a href="{{ route('admin.boarding_monitoring.students', ['tab' => 'history']) }}" class="btn btn-sm btn-success rounded-pill px-3">
                        <i class="bi bi-clock-history me-1"></i>View Stay History &amp; Reports
                    </a>
                </div>
            @endif
        </div>

    {{-- ========================================================================= --}}
    {{-- PART B: STAY HISTORY & REPORTS TAB --}}
    {{-- ========================================================================= --}}
    @elseif($isHistoryTab)
        {{-- Primary Filter Toolbar --}}
        <div class="filter-toolbar-box">
            <form method="GET" action="{{ route('admin.boarding_monitoring.students') }}" id="historyFilterForm">
                <input type="hidden" name="tab" value="history">

                <div class="row g-2 align-items-end">
                    <div class="col-12 col-md-4 col-xl-3">
                        <label class="form-label small fw-bold text-muted mb-1">Search</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white"><i class="bi bi-search text-muted"></i></span>
                            <input type="text" class="form-control" name="search" value="{{ $search }}" placeholder="Student, ID, room...">
                        </div>
                    </div>

                    <div class="col-12 col-md-4 col-xl-3">
                        <label class="form-label small fw-bold text-muted mb-1">Boarding House</label>
                        <select name="boarding_house" class="form-select form-select-sm">
                            <option value="">All Boarding Houses</option>
                            @foreach($boardingHouses as $bh)
                                <option value="{{ $bh->id }}" @selected((string) $boardingHouse === (string) $bh->id)>{{ $bh->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-6 col-md-4 col-xl-2">
                        <label class="form-label small fw-bold text-muted mb-1">Status</label>
                        <select name="status" class="form-select form-select-sm">
                            <option value="all" @selected($statusFilter === 'all')>All Statuses</option>
                            <option value="active" @selected($statusFilter === 'active')>Active / Present</option>
                            <option value="checked_out" @selected($statusFilter === 'checked_out')>Checked Out</option>
                            <option value="pending" @selected($statusFilter === 'pending')>Pending</option>
                            <option value="cancelled" @selected($statusFilter === 'cancelled')>Cancelled</option>
                        </select>
                    </div>

                    <div class="col-6 col-md-4 col-xl-2">
                        <label class="form-label small fw-bold text-muted mb-1">Reporting Period</label>
                        <select name="period_preset" id="periodPresetSelect" class="form-select form-select-sm">
                            <option value="all" @selected($periodPreset === 'all')>All Time</option>
                            <option value="this_month" @selected($periodPreset === 'this_month')>This Month</option>
                            <option value="last_month" @selected($periodPreset === 'last_month')>Last Month</option>
                            <option value="specific_month" @selected($periodPreset === 'specific_month')>Specific Month</option>
                            <option value="custom" @selected($periodPreset === 'custom')>Custom Range</option>
                        </select>
                    </div>

                    <div class="col-12 col-md-8 col-xl-2 d-flex gap-2">
                        <button type="submit" class="btn btn-sm btn-success rounded-pill px-3 flex-fill">
                            <i class="bi bi-funnel me-1"></i>Apply
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill" data-bs-toggle="collapse" data-bs-target="#advancedFiltersCollapse" aria-expanded="false" title="Toggle advanced academic and date basis filters">
                            <i class="bi bi-sliders"></i>
                        </button>
                    </div>
                </div>

                {{-- Dynamic Period Sub-Selectors --}}
                <div id="specificMonthControls" class="row g-2 mt-2 pt-2 border-top {{ $periodPreset === 'specific_month' ? '' : 'd-none' }}">
                    <div class="col-6 col-md-3 col-xl-2">
                        <label class="form-label small text-muted fw-bold mb-1">Month</label>
                        <select name="month" class="form-select form-select-sm">
                            <option value="">Select Month</option>
                            @foreach(range(1, 12) as $m)
                                <option value="{{ $m }}" @selected((int) $month === $m)>{{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6 col-md-3 col-xl-2">
                        <label class="form-label small text-muted fw-bold mb-1">Year</label>
                        <select name="year" class="form-select form-select-sm">
                            @foreach($years as $y)
                                <option value="{{ $y }}" @selected((int) $year === (int) $y)>{{ $y }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div id="customDateControls" class="row g-2 mt-2 pt-2 border-top {{ $periodPreset === 'custom' ? '' : 'd-none' }}">
                    <div class="col-6 col-md-3">
                        <label class="form-label small text-muted fw-bold mb-1">Date From</label>
                        <input type="date" class="form-control form-control-sm" name="date_from" value="{{ $dateFrom }}">
                    </div>
                    <div class="col-6 col-md-3">
                        <label class="form-label small text-muted fw-bold mb-1">Date To</label>
                        <input type="date" class="form-control form-control-sm" name="date_to" value="{{ $dateTo }}">
                    </div>
                </div>

                {{-- Collapsible Advanced Filters --}}
                <div class="collapse mt-2 pt-2 border-top" id="advancedFiltersCollapse">
                    <div class="row g-2">
                        <div class="col-12 col-md-4">
                            <label class="form-label small fw-bold text-muted mb-1">College</label>
                            <select name="college" id="collegeSelect" class="form-select form-select-sm">
                                <option value="">All Colleges</option>
                                @foreach($colleges as $c)
                                    <option value="{{ $c['code'] }}" @selected($college === $c['code'])>{{ $c['code'] }} — {{ $c['name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label small fw-bold text-muted mb-1">Academic Program</label>
                            <select name="program" id="programSelect" class="form-select form-select-sm">
                                <option value="">All Programs</option>
                                @foreach($programs as $prog)
                                    <option value="{{ $prog }}" @selected($program === $prog)>{{ $prog }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label small fw-bold text-muted mb-1">Date Basis</label>
                            <select name="date_basis" class="form-select form-select-sm">
                                <option value="stay" @selected($dateBasis === 'stay')>Stay Overlap (Physical Occupancy)</option>
                                <option value="check_in" @selected($dateBasis === 'check_in')>Started Boarding (Check-in Date)</option>
                            </select>
                            <div class="form-text" style="font-size: 0.72rem;">
                                {{ $dateBasis === 'check_in' ? 'Counts stays that began during the period.' : 'Counts any student occupying during the period.' }}
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Active Filter Tags --}}
                @if($activeFilterCount > 0)
                    <div class="active-filters-strip">
                        <span class="text-muted fw-bold me-1">Active filters:</span>
                        @if(filled($search))
                            <span class="filter-pill"><i class="bi bi-search"></i>"{{ $search }}"</span>
                        @endif
                        @if($selectedProperty)
                            <span class="filter-pill"><i class="bi bi-building"></i>{{ $selectedProperty->name }}</span>
                        @endif
                        @if($statusFilter !== 'all')
                            <span class="filter-pill"><i class="bi bi-tag"></i>{{ ucfirst(str_replace('_', ' ', $statusFilter)) }}</span>
                        @endif
                        @if($periodLabel)
                            <span class="filter-pill"><i class="bi bi-calendar"></i>{{ $periodLabel }}</span>
                        @endif
                        @if(filled($college))
                            <span class="filter-pill"><i class="bi bi-mortarboard"></i>{{ $college }}</span>
                        @endif
                        @if(filled($program))
                            <span class="filter-pill">{{ $program }}</span>
                        @endif
                        <a href="{{ route('admin.boarding_monitoring.students', ['tab' => 'history']) }}" class="text-danger small ms-auto text-decoration-none fw-bold">
                            <i class="bi bi-x-circle me-1"></i>Clear All
                        </a>
                    </div>
                @endif
            </form>
        </div>

        {{-- Compact Metrics Bar (History & Reporting Context) --}}
        <div class="row g-2 mb-3">
            <div class="col-6 col-md-3">
                <div class="metric-chip">
                    <div class="metric-chip-label">Unique Students</div>
                    <div class="metric-chip-value">{{ number_format($uniqueStudents) }}</div>
                    <div class="metric-chip-sub">De-duplicated headcounts</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="metric-chip">
                    <div class="metric-chip-label">Stay Records</div>
                    <div class="metric-chip-value">{{ number_format($totalRecords) }}</div>
                    <div class="metric-chip-sub">Total matching stays</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="metric-chip">
                    <div class="metric-chip-label">Active / Checked Out</div>
                    <div class="metric-chip-value fs-5">{{ number_format($activeTenants) }} / {{ number_format($checkedOutTenants) }}</div>
                    <div class="metric-chip-sub">Active vs checked out</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="metric-chip">
                    <div class="metric-chip-label">Houses &amp; Rooms</div>
                    <div class="metric-chip-value fs-5">{{ number_format($representedProperties) }} / {{ number_format($representedRooms) }}</div>
                    <div class="metric-chip-sub">Represented in filter</div>
                </div>
            </div>
        </div>

        {{-- Stay Records Table --}}
        <div class="surface-card">
            <div class="surface-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div>
                    <span><i class="bi bi-table me-1.5 text-success"></i>Stay Records</span>
                    <span class="badge bg-secondary-subtle text-secondary rounded-pill ms-1">{{ $boardedStudents->total() }} records</span>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <a href="{{ route('admin.boarding_monitoring.students.print', request()->query()) }}" target="_blank" class="btn btn-sm btn-success rounded-pill px-3 shadow-sm">
                        <i class="bi bi-printer me-1"></i>Print Report
                    </a>
                </div>
            </div>

            @if($boardedStudents->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="ps-3">Student</th>
                                <th>Boarding House &amp; Room</th>
                                <th>Academic</th>
                                <th>Check-In</th>
                                <th>Check-Out</th>
                                <th>Status</th>
                                <th class="pe-3 text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($boardedStudents as $boarding)
                                @php
                                    $monitoringStatus = $boarding->monitoringStatus(now(), $periodStart ?? null, $periodEnd ?? null, $dateBasis ?? 'stay');
                                    $monitoringStatusLabel = match($monitoringStatus) {
                                        'checked_out' => 'Checked Out',
                                        'cancelled' => 'Cancelled',
                                        default => ucfirst($monitoringStatus),
                                    };
                                @endphp
                                <tr>
                                    <td class="ps-3">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="avatar-circle">{{ strtoupper(substr($boarding->student?->full_name ?? $boarding->student?->name ?? 'U', 0, 1)) }}</span>
                                            <div class="min-w-0">
                                                <a href="{{ $boarding->student ? route('admin.users.students.show', $boarding->student->id) : '#' }}" class="fw-bold text-dark text-decoration-none hover-success">
                                                    {{ $boarding->student?->full_name ?? $boarding->student?->name ?? 'Unknown Student' }}
                                                </a>
                                                <div class="small text-muted">
                                                    @if(!empty($boarding->student?->student_id))
                                                        <span class="font-monospace">{{ $boarding->student->student_id }}</span> •
                                                    @endif
                                                    {{ $boarding->student?->email ?? 'No email' }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @if($boarding->room?->property)
                                            <a href="{{ route('admin.properties.show', $boarding->room->property->id) }}" class="fw-semibold text-dark text-decoration-none hover-success">
                                                {{ $boarding->room->property->name }}
                                            </a>
                                        @else
                                            <span class="fw-semibold text-dark">N/A</span>
                                        @endif
                                        <div class="small text-muted">
                                            <span class="badge bg-light text-dark border">Room {{ $boarding->room?->room_number ?? $boarding->room_id }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border">{{ $boarding->student?->college ?: 'Unspecified' }}</span>
                                        <div class="small text-muted text-truncate" style="max-width: 180px;">{{ $boarding->student?->program ?: 'Unspecified' }}</div>
                                    </td>
                                    <td>{{ $boarding->check_in ? $boarding->check_in->format('M d, Y') : 'N/A' }}</td>
                                    <td>{{ $boarding->check_out ? $boarding->check_out->format('M d, Y') : 'Open-ended' }}</td>
                                    <td>
                                        <span class="monitoring-status status-{{ $monitoringStatus }}">{{ $monitoringStatusLabel }}</span>
                                    </td>
                                    <td class="pe-3 text-end">
                                        @if($boarding->student)
                                            <a href="{{ route('admin.users.students.show', $boarding->student->id) }}" class="btn btn-sm btn-outline-secondary rounded-pill px-2.5 py-0.5">
                                                <i class="bi bi-person me-1"></i>Profile
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($boardedStudents->hasPages())
                    <div class="p-3 border-top d-flex justify-content-between align-items-center">
                        <span class="small text-muted">Showing {{ $boardedStudents->firstItem() }} to {{ $boardedStudents->lastItem() }} of {{ $boardedStudents->total() }} results</span>
                        <div>{{ $boardedStudents->links() }}</div>
                    </div>
                @endif
            @else
                <div class="empty-state-compact">
                    <i class="bi bi-inbox fs-2 text-secondary mb-2 d-block"></i>
                    <h6 class="fw-bold text-dark mb-1">No stay records match the selected filters.</h6>
                    <p class="small text-muted mb-3">Try adjusting your reporting period, boarding house, or status filters.</p>
                    <a href="{{ route('admin.boarding_monitoring.students', ['tab' => 'history']) }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                        <i class="bi bi-x-circle me-1"></i>Reset All Filters
                    </a>
                </div>
            @endif
        </div>

    {{-- ========================================================================= --}}
    {{-- PART C: ANALYTICS TAB --}}
    {{-- ========================================================================= --}}
    @elseif($isAnalyticsTab)
        {{-- 1. Analytics Filter Toolbar (Unified Surface) --}}
        <div class="analytics-toolbar">
            <form method="GET" action="{{ route('admin.boarding_monitoring.students') }}" class="row g-2 align-items-end">
                <input type="hidden" name="tab" value="analytics">

                <div class="col-12 col-md-4 col-xl-4">
                    <span class="toolbar-field-label">Boarding House</span>
                    <select name="boarding_house" class="form-select toolbar-select w-100">
                        <option value="">All Boarding Houses</option>
                        @foreach($boardingHouses as $bh)
                            <option value="{{ $bh->id }}" @selected((string) $boardingHouse === (string) $bh->id)>{{ $bh->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-md-4 col-xl-3">
                    <span class="toolbar-field-label">College</span>
                    <select name="college" class="form-select toolbar-select w-100">
                        <option value="">All Colleges</option>
                        @foreach($colleges as $c)
                            <option value="{{ $c['code'] }}" @selected($college === $c['code'])>{{ $c['code'] }} — {{ $c['name'] }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-md-4 col-xl-3">
                    <span class="toolbar-field-label">Period Preset</span>
                    <select name="period_preset" class="form-select toolbar-select w-100">
                        <option value="all" @selected($periodPreset === 'all')>All Time</option>
                        <option value="this_month" @selected($periodPreset === 'this_month')>This Month</option>
                        <option value="last_month" @selected($periodPreset === 'last_month')>Last Month</option>
                    </select>
                </div>

                <div class="col-12 col-md-12 col-xl-2 d-flex gap-2">
                    <button type="submit" class="btn-obhs-primary flex-fill">
                        <i class="bi bi-funnel"></i>Apply
                    </button>
                    @if(filled($boardingHouse) || filled($college) || $periodPreset !== 'all')
                        <a href="{{ route('admin.boarding_monitoring.students', ['tab' => 'analytics']) }}" class="btn-obhs-subtle" title="Reset Filters">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- 2. Unified Statistic Strip (KPIs) --}}
        <div class="stat-strip-container">
            <div class="row g-0 align-items-center">
                <div class="col-6 col-md-3 stat-strip-item">
                    <div class="stat-strip-number text-success">{{ number_format($uniqueStudents) }}</div>
                    <div class="stat-strip-title">Unique students</div>
                    <div class="stat-strip-meta">De-duplicated headcount</div>
                </div>
                <div class="col-6 col-md-3 stat-strip-item">
                    <div class="stat-strip-number">{{ number_format($totalRecords) }}</div>
                    <div class="stat-strip-title">Recorded stays</div>
                    <div class="stat-strip-meta">Total matching tenancy periods</div>
                </div>
                <div class="col-6 col-md-3 stat-strip-item">
                    <div class="stat-strip-number">{{ $collegeDistribution->count() }}</div>
                    <div class="stat-strip-title">Colleges represented</div>
                    <div class="stat-strip-meta">Academic divisions with boarders</div>
                </div>
                <div class="col-6 col-md-3 stat-strip-item">
                    <div class="stat-strip-number">{{ $propertyDistribution->count() }}</div>
                    <div class="stat-strip-title">Boarding houses</div>
                    <div class="stat-strip-meta">Active accommodation partners</div>
                </div>
            </div>
        </div>

        {{-- 3. PRIMARY ANALYTICS HERO: Student Boarding Trend (Large Area/Line Visualization) --}}
        <div class="surface-card-hero mb-4">
            <div class="d-flex flex-wrap justify-content-between align-items-baseline gap-2 mb-3">
                <div>
                    <h5 class="fw-bold text-dark mb-0" style="font-size: 1.15rem; letter-spacing: -0.01em;">Student Boarding Trend</h5>
                    <p class="text-secondary small mb-0 mt-0.5">Track new stay registrations, move-outs, and active resident population across reporting periods.</p>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="insight-badge">
                        <i class="bi bi-clock-history"></i>{{ $periodLabel ?: 'All Time' }}
                    </span>
                </div>
            </div>

            <div style="position: relative; height: 260px; width: 100%;">
                <canvas id="chartBoardingTrend"></canvas>
            </div>

            <div class="d-flex flex-wrap align-items-center justify-content-between pt-3 mt-3 border-top gap-3 text-secondary" style="font-size: 0.8rem;">
                <div class="d-flex align-items-center gap-3">
                    <span class="d-inline-flex align-items-center gap-1.5">
                        <span class="d-inline-block rounded-circle" style="width: 9px; height: 9px; background: #166534;"></span>
                        <strong class="text-dark">New stays</strong>
                    </span>
                    <span class="d-inline-flex align-items-center gap-1.5">
                        <span class="d-inline-block rounded-circle" style="width: 9px; height: 9px; background: #94a3b8;"></span>
                        <strong class="text-dark">Ended / Move-outs</strong>
                    </span>
                    <span class="d-inline-flex align-items-center gap-1.5">
                        <span class="d-inline-block rounded-circle" style="width: 9px; height: 9px; background: #0284c7;"></span>
                        <strong class="text-dark">Active boarders</strong>
                    </span>
                </div>
                @if($uniqueStudents > 0)
                    <div class="text-muted small">
                        Institutional telemetry synced with verified MinSU tenancy records.
                    </div>
                @endif
            </div>
        </div>

        {{-- 4. SECONDARY DATA GRID: Distribution by Boarding House (Left) & Resident Demographics (Right) --}}
        <div class="row g-3 mb-4">
            {{-- Distribution by Boarding House --}}
            <div class="col-12 col-xl-7">
                <div class="surface-card h-100 p-3 p-md-4">
                    <div class="d-flex justify-content-between align-items-baseline mb-3">
                        <div>
                            <h6 class="fw-bold text-dark mb-0" style="font-size: 1rem;">Students by Boarding House</h6>
                            <div class="text-muted small">Where active MinSU boarders currently reside</div>
                        </div>
                        <span class="text-muted small fw-semibold">{{ $propertyDistribution->count() }} {{ \Illuminate\Support\Str::plural('property', $propertyDistribution->count()) }}</span>
                    </div>

                    @php
                        $maxBhStudents = max(1, $propertyDistribution->max('total_students') ?: 1);
                        $totalBhStudents = max(1, $propertyDistribution->sum('total_students') ?: 1);
                    @endphp

                    @if($propertyDistribution->count() > 0)
                        <div class="ranked-list">
                            @foreach($propertyDistribution->take(6) as $index => $prop)
                                @php
                                    $pct = round(($prop->total_students / $totalBhStudents) * 100);
                                    $fillPct = round(($prop->total_students / $maxBhStudents) * 100);
                                @endphp
                                <div class="ranked-item">
                                    <div class="ranked-num">{{ sprintf('%02d', $index + 1) }}</div>
                                    <div class="ranked-info">
                                        <div class="ranked-header">
                                            <div class="ranked-title">
                                                <a href="{{ route('admin.properties.show', $prop->property_id) }}" class="text-dark text-decoration-none hover-success">
                                                    {{ $prop->property_name }}
                                                </a>
                                                @if($prop->property_address)
                                                    <span class="ranked-subtitle d-none d-sm-inline ms-1.5">• {{ $prop->property_address }}</span>
                                                @endif
                                            </div>
                                            <div class="ranked-value">
                                                {{ $prop->total_students }} <span class="text-muted fw-normal small">({{ $pct }}%)</span>
                                            </div>
                                        </div>
                                        <div class="ranked-track">
                                            <div class="ranked-bar" style="width: {{ $fillPct }}%;"></div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        {{-- Hidden Chart.js canvas for compatibility with existing tests --}}
                        <div class="d-none">
                            <canvas id="chartBhDistribution"></canvas>
                        </div>

                        @if($propertyDistribution->count() > 6)
                            <div class="text-end pt-3 mt-2 border-top">
                                <span class="text-muted small">Showing top 6 of {{ $propertyDistribution->count() }} properties (full data in breakdown table below)</span>
                            </div>
                        @endif
                    @else
                        <div class="empty-state-compact">
                            <i class="bi bi-buildings fs-3 text-muted mb-2 d-block"></i>
                            <div class="fw-bold text-dark mb-1">No property records in selected filter</div>
                            <p class="small text-muted mb-0">Analytics will appear as students register stays.</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Resident Demographics (Donut + Clean Legend) --}}
            <div class="col-12 col-xl-5">
                <div class="surface-card h-100 p-3 p-md-4">
                    <div class="d-flex justify-content-between align-items-baseline mb-3">
                        <div>
                            <h6 class="fw-bold text-dark mb-0" style="font-size: 1rem;">Gender Distribution</h6>
                            <div class="text-muted small">Demographics of resident students</div>
                        </div>
                        <span class="text-muted small fw-semibold">{{ array_sum($genderDistribution ?? []) }} {{ \Illuminate\Support\Str::plural('student', array_sum($genderDistribution ?? [])) }}</span>
                    </div>

                    @php
                        $totalGender = max(1, array_sum($genderDistribution ?? []));
                        $maleCount = $genderDistribution['male'] ?? 0;
                        $femaleCount = $genderDistribution['female'] ?? 0;
                        $unspecCount = $genderDistribution['unspecified'] ?? 0;
                        $malePct = round(($maleCount / $totalGender) * 100);
                        $femalePct = round(($femaleCount / $totalGender) * 100);
                        $unspecPct = round(($unspecCount / $totalGender) * 100);
                    @endphp

                    <div class="row align-items-center g-3 my-auto">
                        <div class="col-12 col-sm-6">
                            <div style="position: relative; height: 160px; width: 100%;">
                                <canvas id="chartGenderDonut"></canvas>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6">
                            <div class="d-flex flex-column gap-2.5">
                                <div class="d-flex align-items-center justify-content-between p-2 rounded" style="background: var(--obhs-surface-soft);">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="d-inline-block rounded-circle" style="width: 10px; height: 10px; background: #0284c7;"></span>
                                        <span class="small fw-semibold text-dark">Male</span>
                                    </div>
                                    <div class="small fw-bold text-dark">{{ $maleCount }} <span class="text-muted fw-normal">({{ $malePct }}%)</span></div>
                                </div>

                                <div class="d-flex align-items-center justify-content-between p-2 rounded" style="background: var(--obhs-surface-soft);">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="d-inline-block rounded-circle" style="width: 10px; height: 10px; background: #ec4899;"></span>
                                        <span class="small fw-semibold text-dark">Female</span>
                                    </div>
                                    <div class="small fw-bold text-dark">{{ $femaleCount }} <span class="text-muted fw-normal">({{ $femalePct }}%)</span></div>
                                </div>

                                <div class="d-flex align-items-center justify-content-between p-2 rounded" style="background: var(--obhs-surface-soft);">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="d-inline-block rounded-circle" style="width: 10px; height: 10px; background: #94a3b8;"></span>
                                        <span class="small fw-semibold text-dark">Other / Unspec</span>
                                    </div>
                                    <div class="small fw-bold text-dark">{{ $unspecCount }} <span class="text-muted fw-normal">({{ $unspecPct }}%)</span></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- 5. ACADEMIC DISTRIBUTION: Students by College (Left) & Top Programs (Right) --}}
        <div class="row g-3 mb-4">
            {{-- Students by College --}}
            <div class="col-12 col-lg-6">
                <div class="surface-card h-100 p-3 p-md-4">
                    <div class="d-flex justify-content-between align-items-baseline mb-3">
                        <div>
                            <h6 class="fw-bold text-dark mb-0" style="font-size: 1rem;">Students by College</h6>
                            <div class="text-muted small">Academic divisions of resident students</div>
                        </div>
                        <span class="text-muted small fw-semibold">{{ $collegeDistribution->count() }} {{ \Illuminate\Support\Str::plural('college', $collegeDistribution->count()) }}</span>
                    </div>

                    @php
                        $maxCollegeStudents = max(1, $collegeDistribution->max('total_students') ?: 1);
                        $totalCollegeStudents = max(1, $collegeDistribution->sum('total_students') ?: 1);
                    @endphp

                    @if($collegeDistribution->count() > 0)
                        <div class="ranked-list">
                            @foreach($collegeDistribution->take(6) as $index => $cd)
                                @php
                                    $colPct = round(($cd->total_students / $totalCollegeStudents) * 100);
                                    $colFillPct = round(($cd->total_students / $maxCollegeStudents) * 100);
                                @endphp
                                <div class="ranked-item">
                                    <div class="ranked-num">{{ sprintf('%02d', $index + 1) }}</div>
                                    <div class="ranked-info">
                                        <div class="ranked-header">
                                            <div class="ranked-title">
                                                <span class="fw-bold">{{ $cd->college_code }}</span>
                                                <span class="ranked-subtitle d-none d-sm-inline ms-1.5">• {{ $cd->college_name }}</span>
                                            </div>
                                            <div class="ranked-value">
                                                {{ $cd->total_students }} <span class="text-muted fw-normal small">({{ $colPct }}%)</span>
                                            </div>
                                        </div>
                                        <div class="ranked-track">
                                            <div class="ranked-bar blue" style="width: {{ $colFillPct }}%;"></div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        {{-- Hidden Chart.js canvas for compatibility with existing tests --}}
                        <div class="d-none">
                            <canvas id="chartCollegeDistribution"></canvas>
                        </div>
                    @else
                        <div class="empty-state-compact">
                            <i class="bi bi-mortarboard fs-3 text-muted mb-2 d-block"></i>
                            <div class="fw-bold text-dark mb-1">No college records match filters</div>
                            <p class="small text-muted mb-0">Academic data will populate as student profiles sync.</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Top Academic Programs --}}
            <div class="col-12 col-lg-6">
                <div class="surface-card h-100 p-3 p-md-4">
                    <div class="d-flex justify-content-between align-items-baseline mb-3">
                        <div>
                            <h6 class="fw-bold text-dark mb-0" style="font-size: 1rem;">Top Academic Programs</h6>
                            <div class="text-muted small">Leading degree programs among resident students</div>
                        </div>
                        <span class="text-muted small fw-semibold">{{ $programDistribution->count() }} {{ \Illuminate\Support\Str::plural('program', $programDistribution->count()) }}</span>
                    </div>

                    @php
                        $maxProgStudents = max(1, $programDistribution->max('total_students') ?: 1);
                        $totalProgStudents = max(1, $programDistribution->sum('total_students') ?: 1);
                    @endphp

                    @if($programDistribution->count() > 0)
                        <div class="ranked-list">
                            @foreach($programDistribution->take(6) as $index => $pd)
                                @php
                                    $progPct = round(($pd->total_students / $totalProgStudents) * 100);
                                    $progFillPct = round(($pd->total_students / $maxProgStudents) * 100);
                                @endphp
                                <div class="ranked-item">
                                    <div class="ranked-num">{{ sprintf('%02d', $index + 1) }}</div>
                                    <div class="ranked-info">
                                        <div class="ranked-header">
                                            <div class="ranked-title">
                                                <span class="fw-bold">{{ $pd->program_name }}</span>
                                                <span class="badge bg-light text-dark border ms-1.5" style="font-size: 0.7rem;">{{ $pd->college_code }}</span>
                                            </div>
                                            <div class="ranked-value">
                                                {{ $pd->total_students }} <span class="text-muted fw-normal small">({{ $progPct }}%)</span>
                                            </div>
                                        </div>
                                        <div class="ranked-track">
                                            <div class="ranked-bar" style="background: #0ea5e9; width: {{ $progFillPct }}%;"></div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        {{-- Hidden Chart.js canvas for compatibility with existing tests --}}
                        <div class="d-none">
                            <canvas id="chartProgramDistribution"></canvas>
                        </div>
                    @else
                        <div class="empty-state-compact">
                            <i class="bi bi-journal-text fs-3 text-muted mb-2 d-block"></i>
                            <div class="fw-bold text-dark mb-1">No program records match filters</div>
                            <p class="small text-muted mb-0">Degree program metrics will populate automatically.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- 6. DETAILED BREAKDOWN (Segmented Switcher + Modern Table) --}}
        <div class="surface-card mb-2">
            <div class="p-3 p-md-4 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div>
                    <h5 class="fw-bold text-dark mb-0" style="font-size: 1.1rem;">Detailed breakdown</h5>
                    <p class="text-secondary small mb-0">Tabular records corresponding directly to the filtered charts above.</p>
                </div>

                {{-- Segmented View Switcher --}}
                <div class="segmented-control" role="tablist" id="breakdownSegmentedControl">
                    <button type="button" class="segmented-btn active" data-target="breakdown-college">By College</button>
                    <button type="button" class="segmented-btn" data-target="breakdown-program">By Program</button>
                    <button type="button" class="segmented-btn" data-target="breakdown-property">By Boarding House</button>
                </div>
            </div>

            {{-- Tab Panel 1: By College --}}
            <div id="breakdown-college" class="breakdown-pane">
                <div class="table-responsive">
                    <table class="data-table-clean">
                        <thead>
                            <tr>
                                <th class="ps-4">College</th>
                                <th class="text-center">Unique Students</th>
                                <th class="text-center">Total Stays</th>
                                <th class="text-end pe-4">Drill-down</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($collegeDistribution as $cd)
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-bold text-dark">{{ $cd->college_code }}</div>
                                        <div class="small text-muted">{{ $cd->college_name }}</div>
                                    </td>
                                    <td class="text-center fw-bold text-success">{{ number_format($cd->total_students) }}</td>
                                    <td class="text-center text-muted small">{{ number_format($cd->total_records) }}</td>
                                    <td class="text-end pe-4">
                                        <a href="{{ route('admin.boarding_monitoring.students', ['tab' => 'analytics', 'college' => $cd->college_code, 'boarding_house' => $boardingHouse]) }}" class="btn btn-sm btn-outline-success rounded-pill px-2.5 py-0.5" title="Filter by {{ $cd->college_code }}">
                                            <i class="bi bi-arrow-right-short"></i>Filter
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="empty-state-compact">No college distribution data available.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Tab Panel 2: By Program --}}
            <div id="breakdown-program" class="breakdown-pane d-none">
                <div class="table-responsive">
                    <table class="data-table-clean">
                        <thead>
                            <tr>
                                <th class="ps-4">Program</th>
                                <th>College</th>
                                <th class="text-center">Unique Students</th>
                                <th class="text-end pe-4">Drill-down</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($programDistribution as $pd)
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-bold text-dark">{{ $pd->program_name }}</div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border">{{ $pd->college_code }}</span>
                                    </td>
                                    <td class="text-center fw-bold text-primary">{{ number_format($pd->total_students) }}</td>
                                    <td class="text-end pe-4">
                                        <a href="{{ route('admin.boarding_monitoring.students', ['tab' => 'analytics', 'college' => $pd->college_code !== 'Not specified' ? $pd->college_code : '', 'program' => $pd->program_name, 'boarding_house' => $boardingHouse]) }}" class="btn btn-sm btn-outline-primary rounded-pill px-2.5 py-0.5">
                                            <i class="bi bi-arrow-right-short"></i>Filter
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="empty-state-compact">No program distribution data available.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Tab Panel 3: By Boarding House --}}
            <div id="breakdown-property" class="breakdown-pane d-none">
                <div class="table-responsive">
                    <table class="data-table-clean">
                        <thead>
                            <tr>
                                <th class="ps-4">Boarding House</th>
                                <th>Address</th>
                                <th class="text-center">Active Rooms</th>
                                <th class="text-center">Unique Students</th>
                                <th class="text-center">Total Stays</th>
                                <th class="text-end pe-4">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($propertyDistribution as $prop)
                                <tr>
                                    <td class="ps-4 fw-bold text-dark">
                                        <a href="{{ route('admin.properties.show', $prop->property_id) }}" class="text-dark text-decoration-none hover-success">
                                            {{ $prop->property_name }}
                                        </a>
                                    </td>
                                    <td class="text-muted small">{{ $prop->property_address ?: 'Address not set' }}</td>
                                    <td class="text-center">
                                        <span class="badge bg-light text-dark border">{{ $prop->total_rooms }} rooms</span>
                                    </td>
                                    <td class="text-center fw-bold text-success">{{ number_format($prop->total_students) }}</td>
                                    <td class="text-center text-muted small">{{ number_format($prop->total_records) }}</td>
                                    <td class="text-end pe-4">
                                        <a href="{{ route('admin.boarding_monitoring.students', ['tab' => 'analytics', 'boarding_house' => $prop->property_id]) }}" class="btn btn-sm btn-outline-success rounded-pill px-2.5 py-0.5">
                                            <i class="bi bi-arrow-right-short"></i>Filter
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="empty-state-compact">No boarding house distribution data available.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Dynamic period preset selector in history tab
    const presetSelect = document.getElementById('periodPresetSelect');
    const specificMonthBox = document.getElementById('specificMonthControls');
    const customDateBox = document.getElementById('customDateControls');

    if (presetSelect && specificMonthBox && customDateBox) {
        presetSelect.addEventListener('change', function () {
            const val = this.value;
            if (val === 'specific_month') {
                specificMonthBox.classList.remove('d-none');
                customDateBox.classList.add('d-none');
            } else if (val === 'custom') {
                customDateBox.classList.remove('d-none');
                specificMonthBox.classList.add('d-none');
            } else {
                specificMonthBox.classList.add('d-none');
                customDateBox.classList.add('d-none');
            }
        });
    }

    // College -> Program dynamic dropdown filtering
    const collegeSelect = document.getElementById('collegeSelect');
    const programSelect = document.getElementById('programSelect');
    const catalogPrograms = @json($catalogPrograms ?? []);
    const allPrograms = @json($programs ?? []);
    const selectedProgram = @json($program ?? '');

    if (collegeSelect && programSelect) {
        function updatePrograms() {
            const selectedCollege = collegeSelect.value;
            const currentVal = programSelect.value || selectedProgram;

            let options = ['<option value="">All Programs</option>'];

            if (selectedCollege && catalogPrograms[selectedCollege]) {
                const collegeProgs = catalogPrograms[selectedCollege];
                collegeProgs.forEach(prog => {
                    const isSelected = prog === currentVal ? 'selected' : '';
                    options.push(`<option value="${prog}" ${isSelected}>${prog}</option>`);
                });
            } else {
                allPrograms.forEach(prog => {
                    const isSelected = prog === currentVal ? 'selected' : '';
                    options.push(`<option value="${prog}" ${isSelected}>${prog}</option>`);
                });
            }

            options.push(`<option value="Not specified" ${currentVal === 'Not specified' ? 'selected' : ''}>Not Specified</option>`);
            programSelect.innerHTML = options.join('');
        }

        collegeSelect.addEventListener('change', function () {
            programSelect.value = '';
            updatePrograms();
        });

        if (collegeSelect.value) {
            updatePrograms();
        }
    }

    // Initialize Analytics Visualizations & Segmented Switcher if on Analytics Tab
    @if($isAnalyticsTab)
        // Segmented Tab Switcher Logic
        const segmentedButtons = document.querySelectorAll('#breakdownSegmentedControl .segmented-btn');
        const breakdownPanes = document.querySelectorAll('.breakdown-pane');

        segmentedButtons.forEach(btn => {
            btn.addEventListener('click', function () {
                const targetId = this.getAttribute('data-target');
                
                // Update button states
                segmentedButtons.forEach(b => b.classList.remove('active'));
                this.classList.add('active');

                // Toggle visibility of panes
                breakdownPanes.forEach(pane => {
                    if (pane.id === targetId) {
                        pane.classList.remove('d-none');
                    } else {
                        pane.classList.add('d-none');
                    }
                });
            });
        });

        @php
            $safeTrendData = $trendSeries ?? ['labels' => [], 'new_stays' => [], 'ended_stays' => [], 'active_boarders' => []];
            $safeBhData = $propertyDistribution ?? [];
            $safeCollegeData = $collegeDistribution ?? [];
            $safeProgramData = $programDistribution ?? [];
            $safeGenderData = $genderDistribution ?? ['male' => 0, 'female' => 0, 'unspecified' => 0];
        @endphp
        const trendData = {!! json_encode($safeTrendData) !!};
        const bhData = {!! json_encode($safeBhData) !!};
        const collegeData = {!! json_encode($safeCollegeData) !!};
        const programData = {!! json_encode($safeProgramData) !!};
        const genderData = {!! json_encode($safeGenderData) !!};

        const colors = {
            brand: '#166534',
            brandLight: 'rgba(22, 101, 52, 0.08)',
            blue: '#0284c7',
            blueLight: 'rgba(2, 132, 199, 0.08)',
            gray: '#64748b',
            slate: '#94a3b8',
        };

        // 1. Boarding Trend Line Chart
        const trendCanvas = document.getElementById('chartBoardingTrend');
        if (trendCanvas && Array.isArray(trendData.labels) && trendData.labels.length > 0) {
            new Chart(trendCanvas, {
                type: 'line',
                data: {
                    labels: trendData.labels,
                    datasets: [
                        {
                            label: 'New Stays',
                            data: trendData.new_stays || [],
                            borderColor: colors.brand,
                            backgroundColor: colors.brandLight,
                            fill: true,
                            tension: 0.35,
                            borderWidth: 2.2,
                            pointRadius: 3,
                            pointHoverRadius: 6,
                            pointBackgroundColor: colors.brand,
                        },
                        {
                            label: 'Ended Stays',
                            data: trendData.ended_stays || [],
                            borderColor: '#94a3b8',
                            backgroundColor: 'transparent',
                            borderDash: [4, 4],
                            tension: 0.35,
                            borderWidth: 1.5,
                            pointRadius: 2,
                            pointHoverRadius: 5,
                        },
                        {
                            label: 'Active Boarders',
                            data: trendData.active_boarders || [],
                            borderColor: colors.blue,
                            backgroundColor: 'transparent',
                            tension: 0.35,
                            borderWidth: 2,
                            pointRadius: 3,
                            pointHoverRadius: 6,
                            pointBackgroundColor: colors.blue,
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#0f172a',
                            titleColor: '#ffffff',
                            bodyColor: '#f8fafc',
                            titleFont: { size: 12, weight: '700' },
                            bodyFont: { size: 12 },
                            padding: 10,
                            cornerRadius: 8,
                            boxPadding: 4,
                        }
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: { maxTicksLimit: 8, color: colors.gray, font: { size: 11, weight: '500' } }
                        },
                        y: {
                            beginAtZero: true,
                            ticks: { precision: 0, color: colors.gray, font: { size: 11 } },
                            grid: { color: 'rgba(226, 232, 240, 0.7)' }
                        }
                    }
                }
            });
        }

        // 2. Students by Boarding House (Horizontal Bar Chart)
        const bhCanvas = document.getElementById('chartBhDistribution');
        if (bhCanvas && Array.isArray(bhData) && bhData.length > 0) {
            const topBh = bhData.slice(0, 8);
            new Chart(bhCanvas, {
                type: 'bar',
                data: {
                    labels: topBh.map(item => item.property_name),
                    datasets: [{
                        label: 'Unique Students',
                        data: topBh.map(item => Number(item.total_students || 0)),
                        backgroundColor: colors.brand,
                        borderRadius: 4,
                        barThickness: Math.min(22, Math.max(14, Math.floor(160 / topBh.length))),
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#0f172a',
                            titleColor: '#ffffff',
                            bodyColor: '#f8fafc',
                            padding: 8,
                            cornerRadius: 8,
                            callbacks: {
                                label: (ctx) => ` ${ctx.raw} unique resident student${ctx.raw === 1 ? '' : 's'}`,
                                afterLabel: (ctx) => {
                                    const item = topBh[ctx.dataIndex];
                                    return item ? `Total Stays: ${item.total_records} | Rooms: ${item.total_rooms}` : '';
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            beginAtZero: true,
                            ticks: { precision: 0, color: colors.gray, font: { size: 10 } },
                            grid: { color: 'rgba(226, 232, 240, 0.6)' }
                        },
                        y: {
                            ticks: {
                                color: '#0f172a',
                                font: { size: 11, weight: '600' },
                                callback: function(val) {
                                    const label = this.getLabelForValue(val);
                                    return label.length > 20 ? label.substr(0, 18) + '…' : label;
                                }
                            },
                            grid: { display: false }
                        }
                    }
                }
            });
        }

        // 3. Students by College (Horizontal Bar Chart)
        const collegeCanvas = document.getElementById('chartCollegeDistribution');
        if (collegeCanvas && Array.isArray(collegeData) && collegeData.length > 0) {
            const topColleges = collegeData.slice(0, 8);
            new Chart(collegeCanvas, {
                type: 'bar',
                data: {
                    labels: topColleges.map(item => item.college_code),
                    datasets: [{
                        label: 'Unique Students',
                        data: topColleges.map(item => Number(item.total_students || 0)),
                        backgroundColor: colors.blue,
                        borderRadius: 4,
                        barThickness: Math.min(22, Math.max(14, Math.floor(160 / topColleges.length))),
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#0f172a',
                            titleColor: '#ffffff',
                            bodyColor: '#f8fafc',
                            padding: 8,
                            cornerRadius: 8,
                            callbacks: {
                                title: (items) => topColleges[items[0].dataIndex]?.college_name || items[0].label,
                                label: (ctx) => ` ${ctx.raw} resident student${ctx.raw === 1 ? '' : 's'}`
                            }
                        }
                    },
                    scales: {
                        x: {
                            beginAtZero: true,
                            ticks: { precision: 0, color: colors.gray, font: { size: 10 } },
                            grid: { color: 'rgba(226, 232, 240, 0.6)' }
                        },
                        y: {
                            ticks: { color: '#0f172a', font: { size: 11, weight: '600' } },
                            grid: { display: false }
                        }
                    }
                }
            });
        }

        // 4. Students by Program (Horizontal Bar Chart)
        const programCanvas = document.getElementById('chartProgramDistribution');
        if (programCanvas && Array.isArray(programData) && programData.length > 0) {
            const topPrograms = programData.slice(0, 8);
            new Chart(programCanvas, {
                type: 'bar',
                data: {
                    labels: topPrograms.map(item => item.program_name),
                    datasets: [{
                        label: 'Unique Students',
                        data: topPrograms.map(item => Number(item.total_students || 0)),
                        backgroundColor: 'rgba(14, 165, 233, 0.8)',
                        borderRadius: 4,
                        barThickness: Math.min(20, Math.max(12, Math.floor(160 / topPrograms.length))),
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#0f172a',
                            titleColor: '#ffffff',
                            bodyColor: '#f8fafc',
                            padding: 8,
                            cornerRadius: 8,
                            callbacks: {
                                label: (ctx) => ` ${ctx.raw} resident student${ctx.raw === 1 ? '' : 's'}`,
                                afterLabel: (ctx) => `College: ${topPrograms[ctx.dataIndex]?.college_code || 'N/A'}`
                            }
                        }
                    },
                    scales: {
                        x: {
                            beginAtZero: true,
                            ticks: { precision: 0, color: colors.gray, font: { size: 10 } },
                            grid: { color: 'rgba(226, 232, 240, 0.6)' }
                        },
                        y: {
                            ticks: {
                                color: '#0f172a',
                                font: { size: 10, weight: '600' },
                                callback: function(val) {
                                    const label = this.getLabelForValue(val);
                                    return label.length > 22 ? label.substr(0, 20) + '…' : label;
                                }
                            },
                            grid: { display: false }
                        }
                    }
                }
            });
        }

        // 5. Gender Distribution Donut Chart
        const genderCanvas = document.getElementById('chartGenderDonut');
        if (genderCanvas) {
            const male = Number(genderData.male || 0);
            const female = Number(genderData.female || 0);
            const unspecified = Number(genderData.unspecified || 0);
            const hasData = (male + female + unspecified) > 0;

            new Chart(genderCanvas, {
                type: 'doughnut',
                data: {
                    labels: ['Male', 'Female', 'Other'],
                    datasets: [{
                        data: hasData ? [male, female, unspecified] : [1],
                        backgroundColor: hasData ? ['#0284c7', '#ec4899', '#94a3b8'] : ['#e2e8f0'],
                        borderWidth: 2,
                        borderColor: '#ffffff',
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '72%',
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            enabled: hasData,
                            backgroundColor: '#0f172a',
                            titleColor: '#ffffff',
                            bodyColor: '#f8fafc',
                            padding: 8,
                            cornerRadius: 8,
                        }
                    }
                }
            });
        }
    @endif
});
</script>
@endsection
