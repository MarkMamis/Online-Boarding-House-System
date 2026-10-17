@extends('layouts.admin')

@section('title', 'Active Boarders · Student Accommodation Monitoring')

@section('content')
<style>
    /* ── Ranked Monitoring Items (Analytics Visualizations) ── */
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
        color: var(--admin-text-faint, #94a3b8);
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
        color: var(--admin-text, #0f172a);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .ranked-subtitle {
        font-size: 0.74rem;
        color: var(--admin-text-muted, #64748b);
    }

    .ranked-value {
        font-size: 0.82rem;
        font-weight: 700;
        color: var(--admin-text, #0f172a);
    }

    .ranked-track {
        height: 6px;
        background: #f1f5f9;
        border-radius: 999px;
        overflow: hidden;
        border: 1px solid rgba(15, 23, 42, 0.04);
    }

    .ranked-bar {
        height: 100%;
        background: #166534;
        border-radius: 999px;
        transition: width 0.4s ease;
    }

    .ranked-bar.blue {
        background: #0284c7;
    }

    /* ── Segmented Tab Switcher (Detailed Breakdown) ── */
    .segmented-control {
        display: inline-flex;
        background: #f1f5f9;
        border: 1px solid rgba(15, 23, 42, 0.08);
        border-radius: 8px;
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
        color: #475569;
        cursor: pointer;
        transition: all 0.15s ease;
    }

    .segmented-btn:hover {
        color: #0f172a;
    }

    .segmented-btn.active {
        background: #ffffff;
        color: #166534;
        font-weight: 700;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.08);
    }

    /* ── Stat Strip Item ── */
    .stat-strip-item {
        padding: 0.75rem 1rem;
        position: relative;
    }

    .stat-strip-item:not(:last-child)::after {
        content: '';
        position: absolute;
        right: 0;
        top: 15%;
        height: 70%;
        width: 1px;
        background: rgba(15, 23, 42, 0.08);
    }

    @media (max-width: 767.98px) {
        .stat-strip-item:not(:last-child)::after {
            display: none;
        }
    }

    .stat-strip-number {
        font-size: 1.75rem;
        font-weight: 800;
        color: #0f172a;
        line-height: 1;
        margin-bottom: 0.25rem;
    }

    .stat-strip-title {
        font-size: 0.84rem;
        font-weight: 700;
        color: #0f172a;
        margin-bottom: 0.1rem;
    }

    .stat-strip-meta {
        font-size: 0.74rem;
        color: #64748b;
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

<div class="admin-page-shell">
    {{-- Page Header --}}
    <x-admin.page-header
        title="Active Boarders"
        subtitle="Monitor students currently residing in registered boarding houses, review stay history, and generate institutional reports."
        icon="bi-people"
        :breadcrumbs="[
            ['label' => 'Monitoring', 'url' => route('admin.dashboard')],
            ['label' => 'Active Boarders']
        ]"
    />

    {{-- ========================================================================= --}}
    {{-- PART A: CURRENT BOARDERS TAB --}}
    {{-- ========================================================================= --}}
    @if($isCurrentTab)
        {{-- KPI Metrics Row (Aligned with Boarding Houses design) --}}
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <x-admin.metric-card
                    label="Active Students"
                    :value="number_format($activeTenants)"
                    icon="bi-people-fill"
                    variant="brand"
                    subtext="Currently occupying"
                />
            </div>
            <div class="col-6 col-md-3">
                <x-admin.metric-card
                    label="Active Properties"
                    :value="number_format($activeProperties)"
                    icon="bi-buildings"
                    variant="neutral"
                    subtext="Houses with tenants"
                />
            </div>
            <div class="col-6 col-md-3">
                <x-admin.metric-card
                    label="Occupied Rooms"
                    :value="number_format($activeRooms)"
                    icon="bi-door-closed-fill"
                    variant="neutral"
                    subtext="Rooms in active use"
                />
            </div>
            <div class="col-6 col-md-3">
                <x-admin.metric-card
                    label="Available Capacity"
                    :value="number_format(max(0, $totalRoomsCount - $activeRooms)) . ' rooms'"
                    icon="bi-door-open"
                    variant="neutral"
                    subtext="Open for student placement"
                />
            </div>
        </div>

        {{-- Primary Filter Toolbar --}}
        <div class="admin-toolbar mb-4">
            <form method="GET" action="{{ route('admin.boarding_monitoring.students') }}" class="row g-2 align-items-center">
                <input type="hidden" name="tab" value="current">

                <div class="col-12 col-md-5 col-lg-5">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                        <input type="text" class="form-control border-start-0 ps-0" name="search" value="{{ $search }}" placeholder="Search by student name, ID, room...">
                    </div>
                </div>

                <div class="col-12 col-md-4 col-lg-3">
                    <select name="boarding_house" class="form-select" onchange="this.form.submit()">
                        <option value="">All Boarding Houses</option>
                        @foreach($boardingHouses as $bh)
                            <option value="{{ $bh->id }}" @selected((string) $boardingHouse === (string) $bh->id)>{{ $bh->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-md-3 col-lg-2">
                    <select name="college" class="form-select" onchange="this.form.submit()">
                        <option value="">All Colleges</option>
                        @foreach($colleges as $c)
                            <option value="{{ $c['code'] }}" @selected($college === $c['code'])>{{ $c['code'] }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-md-12 col-lg-2 d-flex gap-2">
                    <button type="submit" class="btn btn-brand flex-fill">
                        <i class="bi bi-funnel me-1"></i>Filter
                    </button>
                    @if(filled($search) || filled($boardingHouse) || filled($college))
                        <a href="{{ route('admin.boarding_monitoring.students', ['tab' => 'current']) }}" class="btn btn-action-icon" title="Reset Filters">
                            <i class="bi bi-arrow-counterclockwise"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Current Boarders Table --}}
        <div class="admin-table-card">
            <div class="d-flex justify-content-between align-items-center p-3 border-bottom bg-light">
                <div class="fw-semibold text-dark d-flex align-items-center gap-2">
                    <i class="bi bi-person-check text-success fs-5"></i>
                    <span>Current Boarders</span>
                </div>
                <span class="badge bg-secondary rounded-pill px-3">{{ $boardedStudents->total() }} active</span>
            </div>

            @if($boardedStudents->count() > 0)
                <div class="table-responsive">
                    <table class="table admin-table mb-0">
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
                                            <div class="rounded-circle d-inline-flex align-items-center justify-content-center bg-primary-subtle text-primary fw-bold" style="width: 36px; height: 36px; font-size: 0.84rem;">
                                                {{ strtoupper(substr($boarding->student?->full_name ?? $boarding->student?->name ?? 'U', 0, 1)) }}
                                            </div>
                                            <div>
                                                <a href="{{ $boarding->student ? route('admin.users.students.show', $boarding->student->id) : '#' }}" class="fw-semibold text-dark text-decoration-none">
                                                    {{ $boarding->student?->full_name ?? $boarding->student?->name ?? 'Unknown Student' }}
                                                </a>
                                                <div class="small text-muted">
                                                    {{ $boarding->student?->student_id ?: 'No Student ID' }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @if($boarding->room?->property)
                                            <a href="{{ route('admin.properties.show', $boarding->room->property->id) }}" class="fw-semibold text-dark text-decoration-none">
                                                {{ $boarding->room->property->name }}
                                            </a>
                                        @else
                                            <span class="fw-semibold text-dark">N/A</span>
                                        @endif
                                        <div class="small text-muted mt-0.5">
                                            <span class="badge bg-light text-dark border">Room {{ $boarding->room?->room_number ?? $boarding->room_id }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div><span class="badge bg-light text-dark border">{{ $boarding->student?->college ?: 'Unspecified College' }}</span></div>
                                        <div class="small text-muted mt-0.5" title="{{ $boarding->student?->program }}">{{ $boarding->student?->program ?: 'Unspecified Program' }}</div>
                                    </td>
                                    <td>
                                        <div class="fw-medium text-dark">{{ $boarding->check_in ? $boarding->check_in->format('M d, Y') : 'N/A' }}</div>
                                        <div class="small text-muted">{{ $boarding->check_out ? 'Exp: ' . $boarding->check_out->format('M d, Y') : 'Open stay' }}</div>
                                    </td>
                                    <td>
                                        <x-admin.status-badge status="active" label="Active" />
                                    </td>
                                    <td class="pe-3 text-end">
                                        @if($boarding->student)
                                            <a href="{{ route('admin.users.students.show', $boarding->student->id) }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                                                <i class="bi bi-eye me-1"></i>View
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($boardedStudents->hasPages())
                    <div class="p-3 border-top d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <span class="small text-muted">Showing {{ $boardedStudents->firstItem() }} to {{ $boardedStudents->lastItem() }} of {{ $boardedStudents->total() }} records</span>
                        <div>{{ $boardedStudents->links() }}</div>
                    </div>
                @endif
            @else
                <div class="p-0">
                    <x-admin.empty-state
                        title="No students are currently boarding."
                        description="There are {{ number_format($totalRecords) }} historical stay records available in the system archive."
                        icon="bi-person-x"
                        action-text="View Stay History"
                        :action-url="route('admin.boarding_monitoring.students', ['tab' => 'history'])"
                        action-icon="bi-clock-history"
                    />
                </div>
            @endif
        </div>

    {{-- ========================================================================= --}}
    {{-- PART B: STAY HISTORY & REPORTS TAB --}}
    {{-- ========================================================================= --}}
    @elseif($isHistoryTab)
        {{-- KPI Metrics Row --}}
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <x-admin.metric-card
                    label="Unique Students"
                    :value="number_format($uniqueStudents)"
                    icon="bi-people"
                    variant="brand"
                    subtext="De-duplicated headcounts"
                />
            </div>
            <div class="col-6 col-md-3">
                <x-admin.metric-card
                    label="Stay Records"
                    :value="number_format($totalRecords)"
                    icon="bi-collection"
                    variant="neutral"
                    subtext="Total matching stays"
                />
            </div>
            <div class="col-6 col-md-3">
                <x-admin.metric-card
                    label="Active / Checked Out"
                    :value="number_format($activeTenants) . ' / ' . number_format($checkedOutTenants)"
                    icon="bi-arrow-left-right"
                    variant="neutral"
                    subtext="Active vs checked out"
                />
            </div>
            <div class="col-6 col-md-3">
                <x-admin.metric-card
                    label="Houses &amp; Rooms"
                    :value="number_format($representedProperties) . ' / ' . number_format($representedRooms)"
                    icon="bi-building"
                    variant="neutral"
                    subtext="Represented in filter"
                />
            </div>
        </div>

        {{-- Primary Filter Toolbar --}}
        <div class="admin-toolbar mb-4">
            <form method="GET" action="{{ route('admin.boarding_monitoring.students') }}" id="historyFilterForm">
                <input type="hidden" name="tab" value="history">

                <div class="row g-2 align-items-center">
                    <div class="col-12 col-md-4 col-xl-3">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                            <input type="text" class="form-control border-start-0 ps-0" name="search" value="{{ $search }}" placeholder="Student, ID, room...">
                        </div>
                    </div>

                    <div class="col-12 col-md-4 col-xl-3">
                        <select name="boarding_house" class="form-select" onchange="this.form.submit()">
                            <option value="">All Boarding Houses</option>
                            @foreach($boardingHouses as $bh)
                                <option value="{{ $bh->id }}" @selected((string) $boardingHouse === (string) $bh->id)>{{ $bh->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-6 col-md-2 col-xl-2">
                        <select name="status" class="form-select" onchange="this.form.submit()">
                            <option value="all" @selected($statusFilter === 'all')>All Statuses</option>
                            <option value="active" @selected($statusFilter === 'active')>Active / Present</option>
                            <option value="checked_out" @selected($statusFilter === 'checked_out')>Checked Out</option>
                            <option value="pending" @selected($statusFilter === 'pending')>Pending</option>
                            <option value="cancelled" @selected($statusFilter === 'cancelled')>Cancelled</option>
                        </select>
                    </div>

                    <div class="col-6 col-md-2 col-xl-2">
                        <select name="period_preset" id="periodPresetSelect" class="form-select">
                            <option value="all" @selected($periodPreset === 'all')>All Time</option>
                            <option value="this_month" @selected($periodPreset === 'this_month')>This Month</option>
                            <option value="last_month" @selected($periodPreset === 'last_month')>Last Month</option>
                            <option value="specific_month" @selected($periodPreset === 'specific_month')>Specific Month</option>
                            <option value="custom" @selected($periodPreset === 'custom')>Custom Range</option>
                        </select>
                    </div>

                    <div class="col-12 col-md-12 col-xl-2 d-flex gap-2">
                        <button type="submit" class="btn btn-brand flex-fill">
                            <i class="bi bi-funnel me-1"></i>Apply
                        </button>
                        <button type="button" class="btn btn-action-icon" data-bs-toggle="collapse" data-bs-target="#advancedFiltersCollapse" aria-expanded="false" title="Toggle advanced academic and date basis filters">
                            <i class="bi bi-sliders"></i>
                        </button>
                        @if($activeFilterCount > 0)
                            <a href="{{ route('admin.boarding_monitoring.students', ['tab' => 'history']) }}" class="btn btn-action-icon" title="Reset Filters">
                                <i class="bi bi-arrow-counterclockwise"></i>
                            </a>
                        @endif
                    </div>
                </div>

                {{-- Dynamic Period Sub-Selectors --}}
                <div id="specificMonthControls" class="row g-2 mt-2 pt-2 border-top {{ $periodPreset === 'specific_month' ? '' : 'd-none' }}">
                    <div class="col-6 col-md-3 col-xl-2">
                        <label class="form-label small text-muted fw-bold mb-1">Month</label>
                        <select name="month" class="form-select">
                            <option value="">Select Month</option>
                            @foreach(range(1, 12) as $m)
                                <option value="{{ $m }}" @selected((int) $month === $m)>{{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6 col-md-3 col-xl-2">
                        <label class="form-label small text-muted fw-bold mb-1">Year</label>
                        <select name="year" class="form-select">
                            @foreach($years as $y)
                                <option value="{{ $y }}" @selected((int) $year === (int) $y)>{{ $y }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div id="customDateControls" class="row g-2 mt-2 pt-2 border-top {{ $periodPreset === 'custom' ? '' : 'd-none' }}">
                    <div class="col-6 col-md-3">
                        <label class="form-label small text-muted fw-bold mb-1">Date From</label>
                        <input type="date" class="form-control" name="date_from" value="{{ $dateFrom }}">
                    </div>
                    <div class="col-6 col-md-3">
                        <label class="form-label small text-muted fw-bold mb-1">Date To</label>
                        <input type="date" class="form-control" name="date_to" value="{{ $dateTo }}">
                    </div>
                </div>

                {{-- Collapsible Advanced Filters --}}
                <div class="collapse mt-2 pt-2 border-top" id="advancedFiltersCollapse">
                    <div class="row g-2">
                        <div class="col-12 col-md-4">
                            <label class="form-label small fw-bold text-muted mb-1">College</label>
                            <select name="college" id="collegeSelect" class="form-select">
                                <option value="">All Colleges</option>
                                @foreach($colleges as $c)
                                    <option value="{{ $c['code'] }}" @selected($college === $c['code'])>{{ $c['code'] }} — {{ $c['name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label small fw-bold text-muted mb-1">Academic Program</label>
                            <select name="program" id="programSelect" class="form-select">
                                <option value="">All Programs</option>
                                @foreach($programs as $prog)
                                    <option value="{{ $prog }}" @selected($program === $prog)>{{ $prog }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label small fw-bold text-muted mb-1">Date Basis</label>
                            <select name="date_basis" class="form-select">
                                <option value="stay" @selected($dateBasis === 'stay')>Stay Overlap (Physical Occupancy)</option>
                                <option value="check_in" @selected($dateBasis === 'check_in')>Started Boarding (Check-in Date)</option>
                            </select>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        {{-- Stay Records Table --}}
        <div class="admin-table-card">
            <div class="d-flex flex-wrap justify-content-between align-items-center p-3 border-bottom bg-light gap-2">
                <div class="fw-semibold text-dark d-flex align-items-center gap-2">
                    <i class="bi bi-clock-history text-success fs-5"></i>
                    <span>Stay Records</span>
                    <span class="badge bg-secondary rounded-pill px-3">{{ $boardedStudents->total() }} records</span>
                </div>
                <a href="{{ route('admin.boarding_monitoring.students.print', request()->query()) }}" target="_blank" class="btn btn-sm btn-brand rounded-pill px-3">
                    <i class="bi bi-printer me-1"></i>Print Report
                </a>
            </div>

            @if($boardedStudents->count() > 0)
                <div class="table-responsive">
                    <table class="table admin-table mb-0">
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
                                            <div class="rounded-circle d-inline-flex align-items-center justify-content-center bg-primary-subtle text-primary fw-bold" style="width: 36px; height: 36px; font-size: 0.84rem;">
                                                {{ strtoupper(substr($boarding->student?->full_name ?? $boarding->student?->name ?? 'U', 0, 1)) }}
                                            </div>
                                            <div>
                                                <a href="{{ $boarding->student ? route('admin.users.students.show', $boarding->student->id) : '#' }}" class="fw-semibold text-dark text-decoration-none">
                                                    {{ $boarding->student?->full_name ?? $boarding->student?->name ?? 'Unknown Student' }}
                                                </a>
                                                <div class="small text-muted">
                                                    {{ $boarding->student?->student_id ?: 'No Student ID' }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @if($boarding->room?->property)
                                            <a href="{{ route('admin.properties.show', $boarding->room->property->id) }}" class="fw-semibold text-dark text-decoration-none">
                                                {{ $boarding->room->property->name }}
                                            </a>
                                        @else
                                            <span class="fw-semibold text-dark">N/A</span>
                                        @endif
                                        <div class="small text-muted mt-0.5">
                                            <span class="badge bg-light text-dark border">Room {{ $boarding->room?->room_number ?? $boarding->room_id }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div><span class="badge bg-light text-dark border">{{ $boarding->student?->college ?: 'Unspecified' }}</span></div>
                                        <div class="small text-muted mt-0.5" title="{{ $boarding->student?->program }}">{{ $boarding->student?->program ?: 'Unspecified' }}</div>
                                    </td>
                                    <td><div class="fw-medium text-dark">{{ $boarding->check_in ? $boarding->check_in->format('M d, Y') : 'N/A' }}</div></td>
                                    <td><div class="small text-muted">{{ $boarding->check_out ? $boarding->check_out->format('M d, Y') : 'Open-ended' }}</div></td>
                                    <td>
                                        <x-admin.status-badge :status="$monitoringStatus" :label="$monitoringStatusLabel" />
                                    </td>
                                    <td class="pe-3 text-end">
                                        @if($boarding->student)
                                            <a href="{{ route('admin.users.students.show', $boarding->student->id) }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                                                <i class="bi bi-eye me-1"></i>View
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($boardedStudents->hasPages())
                    <div class="p-3 border-top d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <span class="small text-muted">Showing {{ $boardedStudents->firstItem() }} to {{ $boardedStudents->lastItem() }} of {{ $boardedStudents->total() }} records</span>
                        <div>{{ $boardedStudents->links() }}</div>
                    </div>
                @endif
            @else
                <div class="p-0">
                    <x-admin.empty-state
                        title="No stay records found"
                        description="No stay records match the selected reporting filters. Try adjusting your filter settings."
                        icon="bi-inbox"
                    />
                </div>
            @endif
        </div>

    {{-- ========================================================================= --}}
    {{-- PART C: CENTRALIZED ANALYTICS POINTER --}}
    {{-- ========================================================================= --}}
    @elseif($isAnalyticsTab)
        <div class="admin-table-card p-5 text-center mb-4">
            <i class="bi bi-graph-up-arrow fs-1 text-primary d-block mb-3"></i>
            <h5 class="fw-bold text-dark mb-2">Centralized Institutional Analytics</h5>
            <p class="text-secondary max-w-md mx-auto mb-4" style="max-width: 500px;">
                Detailed boarding activity trends, academic distributions, and occupancy insights have been consolidated into the unified Analytics and Institutional Reports workspaces.
            </p>
            <div class="d-flex justify-content-center gap-2">
                <a href="{{ route('admin.analytics.index', array_filter(['property_id' => $boardingHouse ?: null, 'college' => $college ?: null, 'program' => $program ?: null])) }}" class="btn btn-brand rounded-pill px-4">
                    <i class="bi bi-graph-up me-1"></i>Open Analytics Dashboard
                </a>
                <a href="{{ route('admin.institutional_reports.index') }}" class="btn btn-outline-secondary rounded-pill px-4">
                    <i class="bi bi-journal-text me-1"></i>Institutional Reports
                </a>
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
