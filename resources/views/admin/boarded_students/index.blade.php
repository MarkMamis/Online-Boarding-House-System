@extends('layouts.admin')

@section('title', 'Boarding Monitoring · Students & Academic Reporting')

@section('content')
<style>
    .monitoring-shell {
        background: #ffffff;
        border: 1px solid rgba(2, 8, 20, 0.08);
        border-radius: 1.1rem;
        box-shadow: 0 4px 18px rgba(2, 8, 20, 0.03);
        padding: 1.25rem;
    }

    .monitoring-breadcrumb {
        font-size: 0.8rem;
        font-weight: 600;
        letter-spacing: 0.02em;
        color: #64748b;
        margin-bottom: 0.35rem;
    }

    .monitoring-breadcrumb a {
        color: #64748b;
        text-decoration: none;
    }

    .monitoring-breadcrumb a:hover {
        color: #166534;
    }

    .monitoring-title {
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

    /* ── Workspace Tabs ── */
    .workspace-tabs {
        display: flex;
        flex-wrap: wrap;
        gap: 0.45rem;
        border-bottom: 1px solid rgba(2, 8, 20, 0.08);
        padding-bottom: 0.75rem;
        margin-bottom: 1.25rem;
    }

    .workspace-tabs .nav-link {
        border: 1px solid rgba(20, 83, 45, 0.18);
        border-radius: 999px;
        background: rgba(240, 253, 244, 0.65);
        color: #14532d;
        font-size: 0.82rem;
        font-weight: 700;
        padding: 0.4rem 0.95rem;
        transition: all 0.15s ease;
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        text-decoration: none;
    }

    .workspace-tabs .nav-link:hover {
        background: rgba(22, 101, 52, 0.12);
        border-color: rgba(20, 83, 45, 0.3);
    }

    .workspace-tabs .nav-link.active {
        background: #166534;
        border-color: #166534;
        color: #ffffff;
        box-shadow: 0 2px 8px rgba(22, 101, 52, 0.25);
    }

    .workspace-tabs .nav-link .badge {
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

    /* ── Filter Toolbar ── */
    .filter-toolbar-box {
        background: #f8fafc;
        border: 1px solid rgba(2, 8, 20, 0.06);
        border-radius: 0.85rem;
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
        border-top: 1px dashed rgba(2, 8, 20, 0.08);
    }

    .filter-pill {
        background: #ffffff;
        border: 1px solid rgba(22, 101, 52, 0.2);
        color: #166534;
        font-weight: 600;
        border-radius: 999px;
        padding: 0.18rem 0.55rem;
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
    }

    /* ── Table Styles ── */
    .table thead th {
        font-size: 0.74rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #64748b;
        background: #f8fafc;
        border-bottom: 1px solid rgba(2, 8, 20, 0.08);
        padding: 0.65rem 0.85rem;
        white-space: nowrap;
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
        color: #64748b;
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
                <span class="text-dark">Boarding Monitoring</span>
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
            <h1 class="monitoring-title mb-1">Boarding Monitoring</h1>
            <p class="text-secondary small mb-0">Track current student occupancy, review stay history, and generate institutional reports.</p>
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
        {{-- Analytics Filter Toolbar --}}
        <div class="filter-toolbar-box">
            <form method="GET" action="{{ route('admin.boarding_monitoring.students') }}" class="row g-2 align-items-end">
                <input type="hidden" name="tab" value="analytics">

                <div class="col-12 col-md-4 col-xl-4">
                    <label class="form-label small fw-bold text-muted mb-1">Boarding House</label>
                    <select name="boarding_house" class="form-select form-select-sm">
                        <option value="">All Boarding Houses</option>
                        @foreach($boardingHouses as $bh)
                            <option value="{{ $bh->id }}" @selected((string) $boardingHouse === (string) $bh->id)>{{ $bh->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-md-4 col-xl-3">
                    <label class="form-label small fw-bold text-muted mb-1">College</label>
                    <select name="college" class="form-select form-select-sm">
                        <option value="">All Colleges</option>
                        @foreach($colleges as $c)
                            <option value="{{ $c['code'] }}" @selected($college === $c['code'])>{{ $c['code'] }} — {{ $c['name'] }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-md-4 col-xl-3">
                    <label class="form-label small fw-bold text-muted mb-1">Period Preset</label>
                    <select name="period_preset" class="form-select form-select-sm">
                        <option value="all" @selected($periodPreset === 'all')>All Time</option>
                        <option value="this_month" @selected($periodPreset === 'this_month')>This Month</option>
                        <option value="last_month" @selected($periodPreset === 'last_month')>Last Month</option>
                    </select>
                </div>

                <div class="col-12 col-md-12 col-xl-2 d-flex gap-2">
                    <button type="submit" class="btn btn-sm btn-success rounded-pill px-3 flex-fill">
                        <i class="bi bi-funnel me-1"></i>Apply
                    </button>
                    @if(filled($boardingHouse) || filled($college) || $periodPreset !== 'all')
                        <a href="{{ route('admin.boarding_monitoring.students', ['tab' => 'analytics']) }}" class="btn btn-sm btn-outline-secondary rounded-pill">Reset</a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Analytics KPI Summary --}}
        <div class="row g-2 mb-3">
            <div class="col-6 col-md-3">
                <div class="metric-chip">
                    <div class="metric-chip-label">Unique Students</div>
                    <div class="metric-chip-value">{{ number_format($uniqueStudents) }}</div>
                    <div class="metric-chip-sub">Total represented</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="metric-chip">
                    <div class="metric-chip-label">Total Stays</div>
                    <div class="metric-chip-value">{{ number_format($totalRecords) }}</div>
                    <div class="metric-chip-sub">Booking records</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="metric-chip">
                    <div class="metric-chip-label">Colleges</div>
                    <div class="metric-chip-value fs-5">{{ $collegeDistribution->count() }} represented</div>
                    <div class="metric-chip-sub">Academic units</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="metric-chip">
                    <div class="metric-chip-label">Properties</div>
                    <div class="metric-chip-value fs-5">{{ $propertyDistribution->count() }} boarding houses</div>
                    <div class="metric-chip-sub">Housing providers</div>
                </div>
            </div>
        </div>

        {{-- Distribution Tables --}}
        <div class="row g-3 mb-3">
            {{-- College Distribution --}}
            <div class="col-12 col-xl-5">
                <div class="surface-card h-100">
                    <div class="surface-header d-flex justify-content-between align-items-center">
                        <span><i class="bi bi-mortarboard-fill text-success me-1.5"></i>Students by College</span>
                        <span class="badge bg-secondary-subtle text-secondary rounded-pill">{{ $collegeDistribution->count() }}</span>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>College</th>
                                    <th class="text-center">Unique Students</th>
                                    <th class="text-center">Stays</th>
                                    <th class="text-end pe-3">Drill-down</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($collegeDistribution as $cd)
                                    <tr>
                                        <td>
                                            <div class="fw-bold text-dark">{{ $cd->college_code }}</div>
                                            <div class="small text-muted">{{ $cd->college_name }}</div>
                                        </td>
                                        <td class="text-center fw-bold text-success">{{ number_format($cd->total_students) }}</td>
                                        <td class="text-center text-muted small">{{ number_format($cd->total_records) }}</td>
                                        <td class="text-end pe-3">
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
            </div>

            {{-- Program Distribution --}}
            <div class="col-12 col-xl-7">
                <div class="surface-card h-100">
                    <div class="surface-header d-flex justify-content-between align-items-center">
                        <span><i class="bi bi-journal-text text-primary me-1.5"></i>Students by Program</span>
                        <span class="badge bg-secondary-subtle text-secondary rounded-pill">{{ $programDistribution->count() }}</span>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Program</th>
                                    <th>College</th>
                                    <th class="text-center">Unique Students</th>
                                    <th class="text-end pe-3">Drill-down</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($programDistribution as $pd)
                                    <tr>
                                        <td>
                                            <div class="fw-bold text-dark">{{ $pd->program_name }}</div>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark border">{{ $pd->college_code }}</span>
                                        </td>
                                        <td class="text-center fw-bold text-primary">{{ number_format($pd->total_students) }}</td>
                                        <td class="text-end pe-3">
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
            </div>
        </div>

        {{-- Boarding House Distribution --}}
        <div class="surface-card">
            <div class="surface-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-buildings-fill text-success me-1.5"></i>Students by Boarding House</span>
                <span class="badge bg-secondary-subtle text-secondary rounded-pill">{{ $propertyDistribution->count() }} houses</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="ps-3">Boarding House</th>
                            <th>Address</th>
                            <th class="text-center">Active Rooms</th>
                            <th class="text-center">Unique Students</th>
                            <th class="text-center">Total Stays</th>
                            <th class="text-end pe-3">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($propertyDistribution as $prop)
                            <tr>
                                <td class="ps-3 fw-bold text-dark">
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
                                <td class="text-end pe-3">
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
    @endif
</div>

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
});
</script>
@endsection
