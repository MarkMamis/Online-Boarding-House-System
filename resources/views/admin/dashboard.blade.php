@extends('layouts.admin')

@section('content')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="" />
    <style>
        .admin-page {
            --ink: #0f172a;
            --muted: #64748b;
            --line: #e2e8f0;
            --card: #ffffff;
            --brand: #166534;
            --brand-light: rgba(22, 101, 52, 0.08);
            --brand-border: #bbf7d0;
            --amber-soft: #fffbeb;
            --amber-border: #fef3c7;
            --danger-soft: #fef2f2;
            --danger-border: #fee2e2;
            --blue-soft: #f0f9ff;
            --blue-border: #e0f2fe;
            color: var(--ink);
        }

        .page-greeting {
            font-size: 1.5rem;
            font-weight: 800;
            letter-spacing: -0.025em;
            color: #0f172a;
        }

        .page-subtext {
            color: var(--muted);
            font-size: 0.88rem;
        }

        .kpi-card {
            background: var(--card);
            border: 1px solid var(--line);
            border-radius: 14px;
            padding: 1.15rem 1.25rem;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
            transition: transform 0.15s ease, box-shadow 0.15s ease, border-color 0.15s ease;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .kpi-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(15, 23, 42, 0.06);
            border-color: #cbd5e1;
        }

        .kpi-icon-wrap {
            width: 42px;
            height: 42px;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.15rem;
            flex-shrink: 0;
        }

        .kpi-val {
            font-size: 1.8rem;
            font-weight: 800;
            line-height: 1.1;
            letter-spacing: -0.02em;
            color: #0f172a;
        }

        .kpi-title {
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: var(--muted);
            margin-bottom: 0.25rem;
        }

        .kpi-sub {
            font-size: 0.78rem;
            color: #64748b;
            margin-top: 0.4rem;
        }

        .dash-card {
            background: var(--card);
            border: 1px solid var(--line);
            border-radius: 14px;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .dash-card-header {
            padding: 0.9rem 1.25rem;
            border-bottom: 1px solid #f1f5f9;
            background: #ffffff;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .dash-card-title {
            font-size: 0.95rem;
            font-weight: 700;
            color: #0f172a;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .dash-card-body {
            padding: 1.25rem;
            flex: 1 1 auto;
        }

        .chart-container-distribution {
            position: relative;
            width: 100%;
            min-height: 220px;
            max-height: 320px;
        }

        .chart-container-trend {
            position: relative;
            width: 100%;
            height: 220px;
        }

        .chart-container-donut {
            position: relative;
            width: 100%;
            height: 200px;
        }

        .chart-container-college {
            position: relative;
            width: 100%;
            height: 230px;
        }

        .occupancy-prop-row {
            padding: 0.6rem 0.75rem;
            border-radius: 8px;
            border: 1px solid #f1f5f9;
            background: #f8fafc;
            margin-bottom: 0.5rem;
        }

        .occupancy-prop-row:last-child {
            margin-bottom: 0;
        }

        .occupancy-stacked-bar {
            height: 8px;
            border-radius: 999px;
            background: #e2e8f0;
            display: flex;
            overflow: hidden;
            margin-top: 0.45rem;
        }

        .stacked-occupied {
            background: #166534;
            height: 100%;
        }

        .stacked-available {
            background: #86efac;
            height: 100%;
        }

        .attention-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.7rem 0.85rem;
            border-radius: 10px;
            border: 1px solid #f1f5f9;
            background: #ffffff;
            margin-bottom: 0.55rem;
            text-decoration: none;
            color: inherit;
            transition: all 0.15s ease;
        }

        .attention-item:hover {
            background: #f8fafc;
            border-color: #cbd5e1;
            color: inherit;
            transform: translateX(2px);
        }

        .activity-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.6rem 0;
            border-bottom: 1px dashed #f1f5f9;
        }

        .activity-item:last-child {
            border-bottom: 0;
            padding-bottom: 0;
        }

        .user-avatar-sm {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            font-weight: 700;
            color: #14532d;
            background: #dcfce7;
            border: 1px solid #bbf7d0;
            flex-shrink: 0;
        }

        .stat-badge {
            font-size: 0.75rem;
            font-weight: 700;
            padding: 0.25rem 0.6rem;
            border-radius: 999px;
        }

        .map-container {
            height: 280px;
            width: 100%;
            border-radius: 10px;
            overflow: hidden;
            border: 1px solid #e2e8f0;
        }

        .badge-soft-green {
            background: #dcfce7;
            color: #166534;
            border: 1px solid #bbf7d0;
            font-weight: 700;
        }

        .badge-soft-amber {
            background: #fef3c7;
            color: #92400e;
            border: 1px solid #fde68a;
            font-weight: 700;
        }

        .badge-soft-red {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
            font-weight: 700;
        }

        .badge-soft-blue {
            background: #e0f2fe;
            color: #075985;
            border: 1px solid #bae6fd;
            font-weight: 700;
        }
    </style>

    <div class="container-fluid px-0 admin-page pb-4">
        {{-- Alerts and Migration Notices --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm mb-3 py-2 px-3 small" role="alert">
                <i class="bi bi-check-circle-fill me-1"></i> {!! session('success') !!}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close" style="padding: 0.75rem;"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm mb-3 py-2 px-3 small" role="alert">
                <i class="bi bi-exclamation-octagon-fill me-1"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close" style="padding: 0.75rem;"></button>
            </div>
        @endif

        @if(!empty($hasPendingMigrations))
            <div class="alert alert-warning d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3 p-3 mb-3 rounded-3 border border-warning shadow-sm">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-warning bg-opacity-25 p-2 text-warning-emphasis d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                        <i class="bi bi-database-fill-exclamation fs-5"></i>
                    </div>
                    <div>
                        <div class="fw-bold text-dark small">Database Migrations Pending / Incomplete</div>
                        <div class="text-muted" style="font-size: 0.78rem;">Your database schema needs synchronization for full feature compatibility.</div>
                    </div>
                </div>
                <form method="POST" action="{{ Route::has('admin.system.migrate') ? route('admin.system.migrate') : url('/admin/system/migrate') }}" class="m-0 flex-shrink-0">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-warning fw-semibold rounded-pill px-3 shadow-sm" onclick="return confirm('Run database migrations now?')">
                        <i class="bi bi-play-circle-fill me-1"></i> Run Migrations Now
                    </button>
                </form>
            </div>
        @endif

        {{-- GREETING & CONSOLE HEADER --}}
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-2 mb-4">
            <div>
                @php
                    $hour = (int) now()->format('H');
                    $greetingPrefix = $hour < 12 ? 'Good morning' : ($hour < 18 ? 'Good afternoon' : 'Good evening');
                @endphp
                <h1 class="page-greeting mb-1">{{ $greetingPrefix }}, {{ Auth::user()->full_name ?? 'Administrator' }}</h1>
                <p class="page-subtext mb-0">Current MinSU student boarding monitoring, capacity distribution, and system activity.</p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge badge-soft-green px-3 py-2 rounded-pill small">
                    <i class="bi bi-shield-check me-1"></i> Live Monitoring Console
                </span>
            </div>
        </div>

        {{-- SECTION 1: 4 KPI STATISTICS --}}
        @php
            $totalPendingApprovals = ($pendingApprovals ?? 0) + ($pendingPermitApprovals ?? 0);
            $totalNeedsAttention = $totalPendingApprovals + ($pendingReports ?? 0);
        @endphp
        <div class="row g-3 mb-4">
            {{-- KPI 1: Active Boarders --}}
            <div class="col-12 col-sm-6 col-xl-3">
                <a href="{{ route('admin.boarding_monitoring.students') }}" class="text-decoration-none">
                    <div class="kpi-card">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <div class="kpi-title">Active Boarders</div>
                                <div class="kpi-val">{{ $activeBoardedStudents ?? 0 }}</div>
                            </div>
                            <div class="kpi-icon-wrap" style="background: #dcfce7; color: #166534;">
                                <i class="bi bi-people-fill"></i>
                            </div>
                        </div>
                        <div class="kpi-sub">
                            <span class="text-success fw-semibold"><i class="bi bi-check-circle me-1"></i>Currently verified</span> boarding
                        </div>
                    </div>
                </a>
            </div>

            {{-- KPI 2: Boarding Houses --}}
            <div class="col-12 col-sm-6 col-xl-3">
                <a href="{{ route('admin.properties.index') }}" class="text-decoration-none">
                    <div class="kpi-card">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <div class="kpi-title">Boarding Houses</div>
                                <div class="kpi-val">{{ $totalProperties ?? 0 }}</div>
                            </div>
                            <div class="kpi-icon-wrap" style="background: #e0f2fe; color: #0369a1;">
                                <i class="bi bi-buildings"></i>
                            </div>
                        </div>
                        <div class="kpi-sub">
                            <span class="text-muted">{{ $activeProperties ?? 0 }} active properties with rooms</span>
                        </div>
                    </div>
                </a>
            </div>

            {{-- KPI 3: Available Slots / Capacity --}}
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="kpi-card">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="kpi-title">Available Slots</div>
                            <div class="kpi-val text-success">{{ $slotsAvailable ?? 0 }}</div>
                        </div>
                        <div class="kpi-icon-wrap" style="background: #f0fdf4; color: #15803d; border: 1px solid #bbf7d0;">
                            <i class="bi bi-door-open-fill"></i>
                        </div>
                    </div>
                    <div class="kpi-sub">
                        <span class="text-muted">Total capacity: <strong>{{ $totalCapacity ?? 0 }}</strong> slots ({{ $bedOccupancyRate ?? 0 }}% full)</span>
                    </div>
                </div>
            </div>

            {{-- KPI 4: Needs Attention --}}
            <div class="col-12 col-sm-6 col-xl-3">
                <a href="{{ route('admin.approvals.landlords') }}" class="text-decoration-none">
                    <div class="kpi-card">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <div class="kpi-title">Needs Attention</div>
                                <div class="kpi-val {{ $totalNeedsAttention > 0 ? 'text-warning' : 'text-dark' }}">{{ $totalNeedsAttention }}</div>
                            </div>
                            <div class="kpi-icon-wrap" style="background: #fef3c7; color: #b45309;">
                                <i class="bi bi-exclamation-triangle-fill"></i>
                            </div>
                        </div>
                        <div class="kpi-sub">
                            @if($totalNeedsAttention > 0)
                                <span class="text-warning fw-semibold">{{ $totalNeedsAttention }} pending item(s) to resolve</span>
                            @else
                                <span class="text-success"><i class="bi bi-check2"></i> All queues clear</span>
                            @endif
                        </div>
                    </div>
                </a>
            </div>
        </div>

        {{-- PRIMARY ANALYTICS ROW: Boarded Student Distribution (Left 65%) & Overall Occupancy (Right 35%) --}}
        <div class="row g-3 mb-4">
            {{-- Dominant Graph: Boarded Student Distribution (Horizontal Bar Chart) --}}
            <div class="col-12 col-xl-8">
                <div class="dash-card h-100">
                    <div class="dash-card-header">
                        <div>
                            <h2 class="dash-card-title">
                                <i class="bi bi-bar-chart-steps text-success"></i>
                                Boarded Student Distribution
                            </h2>
                            <div class="text-xs text-muted">Active boarded students per registered boarding house</div>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge badge-soft-green rounded-pill px-2.5 py-1 text-xs">
                                {{ $activeBoardedStudents ?? 0 }} Total Boarders
                            </span>
                            <a href="{{ route('admin.boarding_monitoring.students') }}" class="text-xs text-decoration-none fw-semibold text-success ms-1">
                                Full Roster <i class="bi bi-chevron-right"></i>
                            </a>
                        </div>
                    </div>
                    <div class="dash-card-body d-flex flex-column justify-content-center">
                        @if($boardedByBoardingHouse->count() > 0)
                            <div class="chart-container-distribution" style="height: {{ max(220, min(360, $boardedByBoardingHouse->count() * 42 + 60)) }}px;">
                                <canvas id="chartBhDistribution"></canvas>
                            </div>
                            <div class="d-flex align-items-center justify-content-between pt-2 mt-2 border-top text-xs text-muted">
                                <span><i class="bi bi-info-circle me-1"></i>Sorted by verified resident student population</span>
                                <span>Top {{ $boardedByBoardingHouse->count() }} properties shown</span>
                            </div>
                        @else
                            <div class="text-center py-5 text-muted">
                                <i class="bi bi-house-door fs-2 text-secondary"></i>
                                <div class="fw-semibold text-dark small mt-2">No Active Boarded Students Yet</div>
                                <p class="text-xs text-muted mb-0">Student distribution bar graph will populate once student check-in records are active.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Occupancy / Capacity Monitoring (Right 35%) --}}
            <div class="col-12 col-xl-4">
                <div class="dash-card h-100">
                    <div class="dash-card-header">
                        <div>
                            <h2 class="dash-card-title">
                                <i class="bi bi-pie-chart text-success"></i>
                                Occupancy &amp; Capacity
                            </h2>
                            <div class="text-xs text-muted">Slots utilized across monitored properties</div>
                        </div>
                        <span class="badge text-bg-light border text-xs text-muted">
                            {{ $bedOccupancyRate ?? 0 }}% Full
                        </span>
                    </div>
                    <div class="dash-card-body d-flex flex-column justify-content-between">
                        {{-- Overall Progress Meter --}}
                        @php
                            $safeTotalCap = (int) ($totalCapacity ?? 0);
                            $safeOccupied = (int) ($occupiedBeds ?? 0);
                            $safeSlotsAvail = (int) ($slotsAvailable ?? 0);
                            $safeBedRate = (float) ($bedOccupancyRate ?? 0);
                            $occPct = $safeTotalCap > 0 ? min(100, round(($safeOccupied / $safeTotalCap) * 100)) : 0;
                            $availPct = 100 - $occPct;
                        @endphp
                        <div class="p-3 rounded-3 bg-light border mb-3">
                            <div class="d-flex align-items-center justify-content-between text-xs mb-1">
                                <span class="fw-bold text-dark">Overall Bed Capacity</span>
                                <span><strong class="text-success">{{ $safeOccupied }}</strong> occupied / <strong>{{ $safeTotalCap }}</strong> total</span>
                            </div>
                            <div class="occupancy-stacked-bar" style="height: 10px;">
                                <div class="stacked-occupied" style="width: {{ $occPct }}%;" title="{{ $safeOccupied }} occupied"></div>
                                <div class="stacked-available" style="width: {{ $availPct }}%;" title="{{ $safeSlotsAvail }} available"></div>
                            </div>
                            <div class="d-flex align-items-center justify-content-between text-xs text-muted mt-2">
                                <span><span class="d-inline-block rounded-circle me-1" style="width: 8px; height: 8px; background: #166534;"></span> Occupied: {{ $safeOccupied }}</span>
                                <span><span class="d-inline-block rounded-circle me-1" style="width: 8px; height: 8px; background: #86efac;"></span> Available: {{ $safeSlotsAvail }}</span>
                            </div>
                        </div>

                        {{-- Per Property Compact Stacked List --}}
                        <div class="text-xs fw-bold text-dark text-uppercase tracking-wider mb-2">Property Capacity Status</div>
                        <div class="d-flex flex-column gap-2 overflow-auto" style="max-height: 200px;">
                            @forelse(($propertyOccupancies ?? collect())->take(4) as $pOcc)
                                @php
                                    $pCap = (int) ($pOcc['capacity'] ?? 0);
                                    $pOccVal = (int) ($pOcc['occupied'] ?? 0);
                                    $pRate = (float) ($pOcc['occupancy_rate'] ?? 0);
                                    $pOccPct = $pCap > 0 ? min(100, round(($pOccVal / $pCap) * 100)) : 0;
                                    $pAvailPct = 100 - $pOccPct;
                                @endphp
                                <div class="occupancy-prop-row">
                                    <div class="d-flex align-items-center justify-content-between text-xs">
                                        <span class="fw-bold text-dark text-truncate pe-2" style="max-width: 60%;">{{ $pOcc['name'] ?? 'Property' }}</span>
                                        <span class="text-muted" style="font-size: 0.72rem;">{{ $pOccVal }}/{{ $pCap }} slots ({{ $pRate }}%)</span>
                                    </div>
                                    <div class="occupancy-stacked-bar">
                                        <div class="stacked-occupied" style="width: {{ $pOccPct }}%;"></div>
                                        <div class="stacked-available" style="width: {{ $pAvailPct }}%;"></div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-3 text-muted text-xs">No property capacity data recorded.</div>
                            @endforelse
                        </div>

                        <div class="pt-2 mt-2 border-top text-end">
                            <a href="{{ route('admin.properties.index') }}" class="text-xs text-decoration-none fw-semibold text-success">
                                View all properties <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- SECOND ANALYTICS ROW: Boarding Trend (Left 65%) & Student Gender (Right 35%) --}}
        <div class="row g-3 mb-4">
            {{-- Boarding Trend (Line Graph) --}}
            <div class="col-12 col-xl-8">
                <div class="dash-card h-100">
                    <div class="dash-card-header">
                        <div>
                            <h2 class="dash-card-title">
                                <i class="bi bi-graph-up text-success"></i>
                                Boarding &amp; Registration Trend
                            </h2>
                            <div class="text-xs text-muted">Daily account registrations and activity trajectory (Last 30 Days)</div>
                        </div>
                        <span class="badge text-bg-light border text-xs text-muted" id="rangeLabel">
                            Loading 30-day window...
                        </span>
                    </div>
                    <div class="dash-card-body d-flex flex-column justify-content-between">
                        <div class="chart-container-trend">
                            <canvas id="chartRegistrations"></canvas>
                        </div>
                        <div class="d-flex align-items-center justify-content-between pt-2 border-top text-xs text-muted">
                            <span>Total Users: <strong class="text-dark">{{ $totalUsers ?? 0 }}</strong></span>
                            <span>7-Day Growth: <strong class="text-success">{{ $growthPct ?? 0 }}%</strong></span>
                            <a href="{{ \Illuminate\Support\Facades\Route::has('admin.analytics.index') ? route('admin.analytics.index') : url('/admin/analytics') }}" class="text-success text-decoration-none fw-semibold">
                                Full Analytics Engine <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Student Gender Distribution (Donut Chart + Metrics) --}}
            <div class="col-12 col-xl-4">
                <div class="dash-card h-100">
                    <div class="dash-card-header">
                        <div>
                            <h2 class="dash-card-title">
                                <i class="bi bi-gender-ambiguous text-success"></i>
                                Student Gender
                            </h2>
                            <div class="text-xs text-muted">Demographic breakdown of {{ ($activeBoardedStudents ?? 0) > 0 ? 'active boarders' : 'registered students' }}</div>
                        </div>
                        <span class="badge text-bg-light border text-xs text-muted">
                            Total: {{ array_sum($genderCounts ?? []) }}
                        </span>
                    </div>
                    <div class="dash-card-body d-flex flex-column justify-content-between">
                        <div class="chart-container-donut">
                            <canvas id="chartGenderDonut"></canvas>
                        </div>
                        <div class="row g-2 pt-2 border-top text-center">
                            <div class="col-4">
                                <div class="p-1.5 rounded-2 bg-light border">
                                    <div class="text-muted" style="font-size: 0.7rem;">Male</div>
                                    <div class="fw-bold text-dark text-xs">{{ $genderCounts['male'] ?? 0 }}</div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="p-1.5 rounded-2 bg-light border">
                                    <div class="text-muted" style="font-size: 0.7rem;">Female</div>
                                    <div class="fw-bold text-dark text-xs">{{ $genderCounts['female'] ?? 0 }}</div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="p-1.5 rounded-2 bg-light border">
                                    <div class="text-muted" style="font-size: 0.7rem;">Unspecified</div>
                                    <div class="fw-bold text-dark text-xs">{{ $genderCounts['unspecified'] ?? 0 }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- THIRD ANALYTICS ROW: Academic Distribution (Left 50%) & Boarding Locations Map (Right 50%) --}}
        <div class="row g-3 mb-4">
            {{-- Students by College (Horizontal Bar Chart) --}}
            <div class="col-12 col-lg-6">
                <div class="dash-card h-100">
                    <div class="dash-card-header">
                        <div>
                            <h2 class="dash-card-title">
                                <i class="bi bi-mortarboard text-success"></i>
                                Boarded Students by College
                            </h2>
                            <div class="text-xs text-muted">Distribution of active students across MinSU colleges</div>
                        </div>
                        @php
                            $academicColleges = collect($boardedByAcademic ?? []);
                            $academicCollegeCount = $academicColleges->count();
                        @endphp
                        <span class="badge text-bg-light border text-xs text-muted">
                            {{ $academicCollegeCount }} College{{ $academicCollegeCount === 1 ? '' : 's' }}
                        </span>
                    </div>
                    <div class="dash-card-body d-flex flex-column justify-content-between">
                        @if($academicCollegeCount > 0)
                            <div class="chart-container-college">
                                <canvas id="chartCollegeDistribution"></canvas>
                            </div>
                        @else
                            <div class="text-center py-5 text-muted">
                                <i class="bi bi-mortarboard fs-3 text-secondary"></i>
                                <div class="fw-semibold text-dark small mt-2">No College Data Available</div>
                                <div class="text-xs text-muted">College affiliations will display as verified students board.</div>
                            </div>
                        @endif
                        <div class="d-flex align-items-center justify-content-between pt-2 border-top text-xs text-muted">
                            <span>Top College: <strong class="text-dark">{{ $academicColleges->first()?->college_name ?? 'None' }}</strong></span>
                            <a href="{{ \Illuminate\Support\Facades\Route::has('admin.analytics.index') ? route('admin.analytics.index') : url('/admin/analytics') }}" class="text-success text-decoration-none fw-semibold">
                                View Academic Breakdown <i class="bi bi-chevron-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Boarding House Locations (Leaflet Map) --}}
            <div class="col-12 col-lg-6">
                <div class="dash-card h-100">
                    <div class="dash-card-header">
                        <div>
                            <h2 class="dash-card-title">
                                <i class="bi bi-geo-alt text-success"></i>
                                Boarding House Locations
                            </h2>
                            <div class="text-xs text-muted">Geographical distribution of approved properties</div>
                        </div>
                        <span class="badge text-bg-light border text-xs text-muted">
                            <i class="bi bi-pin-map-fill text-success me-1"></i>{{ ($landlordMapPoints ?? collect())->count() }} Mapped
                        </span>
                    </div>
                    <div class="dash-card-body d-flex flex-column justify-content-between">
                        @if(($landlordMapPoints ?? collect())->count() > 0)
                            <div id="adminLandlordMap" class="map-container"></div>
                            <div class="text-xs text-muted mt-2 d-flex align-items-center justify-content-between">
                                <span>Approved properties with GPS coordinates.</span>
                                <span>Click pin to view landlord &amp; address.</span>
                            </div>
                        @else
                            <div class="text-center py-5 text-muted bg-light rounded-3">
                                <i class="bi bi-map fs-3 text-secondary"></i>
                                <div class="fw-semibold text-dark small mt-2">No Properties Mapped Yet</div>
                                <div class="text-xs text-muted">Coordinates will appear once landlords set property map locations.</div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- BOTTOM ROW: Needs Attention (Left 50%) & Recent Activity (Right 50%) --}}
        <div class="row g-3">
            {{-- Needs Attention Summary --}}
            <div class="col-12 col-lg-6">
                <div class="dash-card h-100">
                    <div class="dash-card-header">
                        <div>
                            <h2 class="dash-card-title">
                                <i class="bi bi-exclamation-circle text-warning"></i>
                                Needs Attention
                            </h2>
                            <div class="text-xs text-muted">Pending administrative approvals and open reports</div>
                        </div>
                        @if($totalNeedsAttention > 0)
                            <span class="badge badge-soft-amber rounded-pill px-2.5 py-1 text-xs">
                                {{ $totalNeedsAttention }} Items Pending
                            </span>
                        @else
                            <span class="badge badge-soft-green rounded-pill px-2.5 py-1 text-xs">
                                All Clear
                            </span>
                        @endif
                    </div>
                    <div class="dash-card-body">
                        @if($totalNeedsAttention === 0)
                            <div class="text-center py-4">
                                <div class="rounded-circle bg-success bg-opacity-10 text-success d-inline-flex align-items-center justify-content-center mb-2" style="width: 44px; height: 44px;">
                                    <i class="bi bi-check2-all fs-4"></i>
                                </div>
                                <div class="fw-bold text-dark small">Everything Looks Good</div>
                                <div class="text-muted text-xs mt-1">No pending property approvals, permit reviews, or open complaints.</div>
                            </div>
                        @else
                            <div class="text-xs text-muted mb-2">Items requiring administrative action:</div>
                        @endif

                        <div class="d-flex flex-column">
                            {{-- Landlord Property Approvals --}}
                            <a href="{{ route('admin.approvals.landlords') }}" class="attention-item">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="user-avatar-sm" style="background: #fef3c7; color: #b45309; border-color: #fde68a;">
                                        <i class="bi bi-buildings"></i>
                                    </div>
                                    <div>
                                        <div class="fw-semibold text-dark text-xs">Landlord Properties</div>
                                        <div class="text-muted" style="font-size: 0.72rem;">Pending property listing reviews</div>
                                    </div>
                                </div>
                                <div>
                                    @if(($pendingApprovals ?? 0) > 0)
                                        <span class="badge badge-soft-amber rounded-pill px-2 py-0.5 text-xs">{{ $pendingApprovals }}</span>
                                    @else
                                        <span class="badge text-bg-light rounded-pill px-2 py-0.5 text-xs text-muted">0</span>
                                    @endif
                                </div>
                            </a>

                            {{-- Business Permit Reviews --}}
                            <a href="{{ route('admin.documents.verification') }}" class="attention-item">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="user-avatar-sm" style="background: #e0f2fe; color: #0369a1; border-color: #bae6fd;">
                                        <i class="bi bi-file-earmark-check"></i>
                                    </div>
                                    <div>
                                        <div class="fw-semibold text-dark text-xs">Business Permits</div>
                                        <div class="text-muted" style="font-size: 0.72rem;">Landlord compliance verifications</div>
                                    </div>
                                </div>
                                <div>
                                    @if(($pendingPermitApprovals ?? 0) > 0)
                                        <span class="badge badge-soft-amber rounded-pill px-2 py-0.5 text-xs">{{ $pendingPermitApprovals }}</span>
                                    @else
                                        <span class="badge text-bg-light rounded-pill px-2 py-0.5 text-xs text-muted">0</span>
                                    @endif
                                </div>
                            </a>

                            {{-- Student ID Verifications --}}
                            <a href="{{ route('admin.approvals.students') }}" class="attention-item">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="user-avatar-sm" style="background: #f3e8ff; color: #7e22ce; border-color: #e9d5ff;">
                                        <i class="bi bi-person-vcard"></i>
                                    </div>
                                    <div>
                                        <div class="fw-semibold text-dark text-xs">Student ID Approvals</div>
                                        <div class="text-muted" style="font-size: 0.72rem;">Enrollment identity proof reviews</div>
                                    </div>
                                </div>
                                <div>
                                    <span class="badge text-bg-light rounded-pill px-2 py-0.5 text-xs text-muted">Queue</span>
                                </div>
                            </a>

                            {{-- Open Reports --}}
                            <a href="{{ route('admin.reports.index') }}" class="attention-item mb-0">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="user-avatar-sm" style="background: #fee2e2; color: #b91c1c; border-color: #fecaca;">
                                        <i class="bi bi-flag"></i>
                                    </div>
                                    <div>
                                        <div class="fw-semibold text-dark text-xs">Unresolved Reports</div>
                                        <div class="text-muted" style="font-size: 0.72rem;">Student/landlord complaints</div>
                                    </div>
                                </div>
                                <div>
                                    @if(($pendingReports ?? 0) > 0)
                                        <span class="badge badge-soft-red rounded-pill px-2 py-0.5 text-xs">{{ $pendingReports }}</span>
                                    @else
                                        <span class="badge text-bg-light rounded-pill px-2 py-0.5 text-xs text-muted">0</span>
                                    @endif
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Recent Activity Feed --}}
            <div class="col-12 col-lg-6">
                <div class="dash-card h-100">
                    <div class="dash-card-header">
                        <div>
                            <h2 class="dash-card-title">
                                <i class="bi bi-clock-history text-success"></i>
                                Recent Activity
                            </h2>
                            <div class="text-xs text-muted">Latest account and onboarding events</div>
                        </div>
                        <a href="{{ route('admin.users.index') }}" class="text-xs text-decoration-none fw-semibold text-success">
                            All Users <i class="bi bi-chevron-right"></i>
                        </a>
                    </div>
                    <div class="dash-card-body">
                        <div class="d-flex flex-column">
                            @forelse($recentUsers->take(5) as $u)
                                <div class="activity-item">
                                    <div class="user-avatar-sm">
                                        {{ strtoupper(substr($u->full_name ?? 'U', 0, 1)) }}
                                    </div>
                                    <div class="min-w-0 flex-grow-1">
                                        <div class="d-flex align-items-center justify-content-between gap-1">
                                            <div class="fw-bold text-dark text-truncate text-xs">{{ $u->full_name }}</div>
                                            <span class="badge @if($u->role==='admin') badge-soft-blue @elseif($u->role==='landlord') badge-soft-amber @else badge-soft-green @endif rounded-pill px-2 py-0.5 text-xs">
                                                {{ ucfirst($u->role) }}
                                            </span>
                                        </div>
                                        <div class="d-flex align-items-center justify-content-between text-muted" style="font-size: 0.73rem;">
                                            <span>Registered account</span>
                                            <span>{{ $u->created_at->diffForHumans(null, true, true) }} ago</span>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-4 text-muted small">
                                    No recent account activity recorded.
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- CHART.JS & LEAFLET SCRIPTS --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
    <script>
        (function () {
            const bhDistributionData = @json($boardedByBoardingHouse ?? []);
            const genderData = @json($genderCounts ?? []);
            const academicCollegeData = @json($boardedByAcademic ?? []);
            const landlordPoints = @json($landlordMapPoints ?? []);

            const colors = {
                brand: '#166534',
                brandLight: 'rgba(22, 101, 52, 0.14)',
                brandHover: '#15803d',
                mint: '#86efac',
                amber: '#f59e0b',
                amberLight: 'rgba(245, 158, 11, 0.14)',
                blue: '#0ea5e9',
                blueLight: 'rgba(14, 165, 233, 0.14)',
                gray: '#64748b',
                grayLight: '#e2e8f0',
            };

            const fmt = (iso) => {
                if (!iso) return '';
                const parts = iso.split('-');
                if (parts.length === 3) {
                    const months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
                    return `${months[parseInt(parts[1], 10) - 1]} ${parseInt(parts[2], 10)}`;
                }
                return iso;
            };

            // 1. Primary Graph: Horizontal Bar Chart for Boarded Students per BH
            const initBhDistributionChart = () => {
                const ctx = document.getElementById('chartBhDistribution');
                if (!ctx || !Array.isArray(bhDistributionData) || bhDistributionData.length === 0) return;

                const labels = bhDistributionData.map(item => item.name);
                const values = bhDistributionData.map(item => Number(item.total_students || 0));

                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Active Boarders',
                            data: values,
                            backgroundColor: colors.brand,
                            borderRadius: 6,
                            borderSkipped: false,
                            barThickness: Math.min(26, Math.max(16, Math.floor(180 / Math.max(1, labels.length)))),
                        }]
                    },
                    options: {
                        indexAxis: 'y',
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                backgroundColor: 'rgba(15, 23, 42, 0.92)',
                                titleColor: '#ffffff',
                                bodyColor: '#f8fafc',
                                padding: 10,
                                cornerRadius: 8,
                                callbacks: {
                                    label: function(context) {
                                        const count = context.raw;
                                        return ` ${count} active student${count === 1 ? '' : 's'}`;
                                    },
                                    afterLabel: function(context) {
                                        const idx = context.dataIndex;
                                        const item = bhDistributionData[idx];
                                        return item?.address ? `Location: ${item.address}` : '';
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                beginAtZero: true,
                                ticks: { precision: 0, color: colors.gray, font: { size: 11 } },
                                grid: { color: 'rgba(226, 232, 240, 0.6)' }
                            },
                            y: {
                                ticks: {
                                    color: '#0f172a',
                                    font: { size: 12, weight: '600' },
                                    callback: function(val) {
                                        const label = this.getLabelForValue(val);
                                        return label.length > 24 ? label.substr(0, 22) + '…' : label;
                                    }
                                },
                                grid: { display: false }
                            }
                        }
                    }
                });
            };

            // 2. Gender Distribution Donut Chart
            const initGenderDonutChart = () => {
                const ctx = document.getElementById('chartGenderDonut');
                if (!ctx) return;

                const male = Number(genderData.male || 0);
                const female = Number(genderData.female || 0);
                const unspecified = Number(genderData.unspecified || 0);
                const hasData = (male + female + unspecified) > 0;

                new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Male', 'Female', 'Unspecified'],
                        datasets: [{
                            data: hasData ? [male, female, unspecified] : [1],
                            backgroundColor: hasData 
                                ? ['#0284c7', '#ec4899', '#94a3b8'] 
                                : ['#e2e8f0'],
                            borderWidth: 2,
                            borderColor: '#ffffff',
                            hoverOffset: 4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '72%',
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    boxWidth: 10,
                                    boxHeight: 10,
                                    usePointStyle: true,
                                    pointStyle: 'circle',
                                    padding: 12,
                                    font: { size: 11, weight: '600' },
                                    color: colors.gray
                                }
                            },
                            tooltip: {
                                enabled: hasData,
                                backgroundColor: 'rgba(15, 23, 42, 0.92)',
                                titleColor: '#ffffff',
                                bodyColor: '#f8fafc',
                                padding: 8,
                                cornerRadius: 8
                            }
                        }
                    }
                });
            };

            // 3. Academic College Distribution (Horizontal Bar Chart)
            const initCollegeChart = () => {
                const ctx = document.getElementById('chartCollegeDistribution');
                if (!ctx || !Array.isArray(academicCollegeData) || academicCollegeData.length === 0) return;

                const labels = academicCollegeData.map(c => c.college_code || c.college_name || 'Not specified');
                const values = academicCollegeData.map(c => Number(c.total_students || 0));

                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Students',
                            data: values,
                            backgroundColor: 'rgba(14, 165, 233, 0.85)',
                            borderRadius: 6,
                            barThickness: Math.min(24, Math.max(16, Math.floor(160 / Math.max(1, labels.length)))),
                        }]
                    },
                    options: {
                        indexAxis: 'y',
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                backgroundColor: 'rgba(15, 23, 42, 0.92)',
                                titleColor: '#ffffff',
                                bodyColor: '#f8fafc',
                                padding: 8,
                                cornerRadius: 8,
                                callbacks: {
                                    title: function(items) {
                                        const idx = items[0].dataIndex;
                                        return academicCollegeData[idx]?.college_name || items[0].label;
                                    },
                                    label: function(context) {
                                        return ` ${context.raw} resident student${context.raw === 1 ? '' : 's'}`;
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
                                ticks: { color: '#0f172a', font: { size: 11, weight: '600' } },
                                grid: { display: false }
                            }
                        }
                    }
                });
            };

            // 4. Boarding & Registration Trend (Line Graph)
            const buildTrendChart = (ctx, labels, data) => {
                const canvasCtx = ctx.getContext('2d');
                const gradient = canvasCtx.createLinearGradient(0, 0, 0, 180);
                gradient.addColorStop(0, 'rgba(22, 101, 52, 0.24)');
                gradient.addColorStop(1, 'rgba(22, 101, 52, 0.00)');

                return new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels,
                        datasets: [{
                            label: 'New Registrations',
                            data,
                            borderColor: colors.brand,
                            backgroundColor: gradient,
                            fill: true,
                            tension: 0.35,
                            borderWidth: 2,
                            pointRadius: 2.5,
                            pointHoverRadius: 4.5,
                            pointBackgroundColor: '#ffffff',
                            pointBorderColor: colors.brand,
                            pointBorderWidth: 1.5,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                backgroundColor: 'rgba(15, 23, 42, 0.90)',
                                titleColor: '#ffffff',
                                bodyColor: '#f8fafc',
                                padding: 8,
                                cornerRadius: 8,
                                displayColors: false
                            }
                        },
                        scales: {
                            x: {
                                grid: { display: false },
                                ticks: { maxTicksLimit: 6, color: colors.gray, font: { size: 10 } }
                            },
                            y: {
                                beginAtZero: true,
                                ticks: { precision: 0, color: colors.gray, font: { size: 10 } },
                                grid: { color: 'rgba(226, 232, 240, 0.6)' }
                            }
                        }
                    }
                });
            };

            // 5. Leaflet Property Locations Map
            const initLandlordMap = () => {
                const mapEl = document.getElementById('adminLandlordMap');
                if (!mapEl || typeof L === 'undefined' || !Array.isArray(landlordPoints) || landlordPoints.length === 0) {
                    return;
                }

                const map = L.map(mapEl, { zoomControl: true, attributionControl: true });
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxNativeZoom: 19,
                    maxZoom: 22,
                    attribution: '&copy; OpenStreetMap'
                }).addTo(map);

                const bounds = [];
                landlordPoints.forEach((point) => {
                    if (typeof point.latitude !== 'number' || typeof point.longitude !== 'number') return;
                    const marker = L.marker([point.latitude, point.longitude]).addTo(map);
                    const popupHtml = `
                        <div style="font-family: inherit; min-width: 180px; padding: 2px;">
                            <div style="font-weight: 700; color: #0f172a; font-size: 13px;">${point.name || 'Property'}</div>
                            <div style="font-size: 11px; color: #64748b; margin-top: 2px;">${point.address || 'Address not set'}</div>
                            <div style="font-size: 11px; color: #166534; margin-top: 6px; font-weight: 600;">Landlord: ${point.landlord_name || 'N/A'}</div>
                        </div>
                    `;
                    marker.bindPopup(popupHtml);
                    bounds.push([point.latitude, point.longitude]);
                });

                if (bounds.length === 1) {
                    map.setView(bounds[0], 14);
                } else if (bounds.length > 1) {
                    map.fitBounds(bounds, { padding: [20, 20] });
                } else {
                    map.setView([13.4115, 121.1803], 13);
                }
            };

            // Async Stats Fetching for 30-Day Trend
            async function initStats() {
                try {
                    const res = await fetch("{{ route('admin.dashboard.stats') }}?days=30", {
                        headers: { 'Accept': 'application/json' },
                        credentials: 'same-origin'
                    });
                    if (!res.ok) throw new Error('Failed to load stats');
                    const stats = await res.json();

                    const labels = (stats.labels || []).map(fmt);
                    const rangeEl = document.getElementById('rangeLabel');
                    if (rangeEl && stats.range) {
                        rangeEl.textContent = `${fmt(stats.range.start)} – ${fmt(stats.range.end)}`;
                    }

                    const regCanvas = document.getElementById('chartRegistrations');
                    if (regCanvas) {
                        buildTrendChart(regCanvas, labels, stats.registrations || []);
                    }
                } catch (e) {
                    const rangeEl = document.getElementById('rangeLabel');
                    if (rangeEl) rangeEl.textContent = '30-day summary';
                }
            }

            document.addEventListener('DOMContentLoaded', () => {
                initBhDistributionChart();
                initGenderDonutChart();
                initCollegeChart();
                initLandlordMap();
                initStats();
            });
        })();
    </script>
@endsection