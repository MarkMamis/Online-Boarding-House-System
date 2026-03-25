@extends('layouts.admin')

@section('content')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="" />
    <style>
        .admin-page {
            --ink: #0f172a;
            --muted: rgba(15, 23, 42, .64);
            --line: rgba(15, 23, 42, .10);
            --card: #ffffff;
            --brand: #166534;
            --brand-2: #15803d;
            --amber: #f59e0b;
            --sky: #0ea5e9;
            color: var(--ink);
        }

        .page-title {
            letter-spacing: -.03em;
            font-weight: 800;
        }

        .muted { color: var(--muted); }

        .hero {
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(21, 128, 61, .20);
            border-radius: 20px;
            background:
                radial-gradient(900px 260px at 96% -25%, rgba(245, 158, 11, .22), transparent 58%),
                radial-gradient(700px 220px at -2% 124%, rgba(14, 165, 233, .16), transparent 60%),
                linear-gradient(135deg, #f3fbf5 0%, #f9fcfb 100%);
            box-shadow: 0 20px 40px rgba(15, 23, 42, .08);
        }

        .hero::after {
            content: "";
            position: absolute;
            inset: auto -80px -120px auto;
            width: 320px;
            height: 320px;
            border-radius: 50%;
            background: radial-gradient(circle at center, rgba(21, 128, 61, .20), transparent 70%);
            pointer-events: none;
        }

        .hero-chip {
            display: inline-flex;
            align-items: center;
            gap: .45rem;
            border-radius: 999px;
            border: 1px solid rgba(21, 128, 61, .24);
            background: rgba(255, 255, 255, .72);
            color: rgba(15, 23, 42, .76);
            padding: .42rem .8rem;
            font-size: .8rem;
            font-weight: 600;
        }

        .hero-actions .btn {
            border-radius: 999px;
            padding: .5rem .95rem;
            font-weight: 600;
        }

        .insights-row {
            border-top: 1px dashed rgba(15, 23, 42, .14);
        }

        .insight-pill {
            display: inline-flex;
            align-items: center;
            gap: .45rem;
            font-size: .85rem;
            color: var(--muted);
            white-space: nowrap;
        }

        .kpi {
            position: relative;
            border: 1px solid var(--line);
            border-radius: 18px;
            background: var(--card);
            box-shadow: 0 14px 30px rgba(15, 23, 42, .06);
            transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
            overflow: hidden;
        }

        .kpi::before {
            content: "";
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, rgba(22, 101, 52, .95), rgba(21, 128, 61, .45));
        }

        .kpi:hover {
            transform: translateY(-2px);
            border-color: rgba(22, 101, 52, .24);
            box-shadow: 0 20px 34px rgba(15, 23, 42, .09);
        }

        .kpi .kpi-icon {
            width: 46px;
            height: 46px;
            border-radius: 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(21, 128, 61, .10);
            border: 1px solid rgba(21, 128, 61, .20);
            color: var(--brand);
            flex: 0 0 auto;
            font-size: 1.15rem;
        }

        .kpi .kpi-value {
            font-weight: 800;
            letter-spacing: -.03em;
            line-height: 1;
        }

        .kpi .kpi-label {
            font-size: .84rem;
            color: var(--muted);
            font-weight: 600;
        }

        .kpi .kpi-meta {
            font-size: .82rem;
            color: var(--muted);
            border-top: 1px dashed rgba(15, 23, 42, .10);
            margin-top: .8rem;
            padding-top: .7rem;
        }

        .kpi a { color: inherit; }

        .section-card {
            position: relative;
            border: 1px solid var(--line);
            border-radius: 18px;
            background: var(--card);
            box-shadow: 0 14px 30px rgba(15, 23, 42, .06);
            overflow: hidden;
        }

        .section-card .card-header {
            border-bottom: 1px solid rgba(15, 23, 42, .07);
            border-top-left-radius: 18px;
            border-top-right-radius: 18px;
            background:
                linear-gradient(180deg, rgba(22, 101, 52, .03), rgba(22, 101, 52, .01));
        }

        .header-title {
            font-weight: 700;
            letter-spacing: -.01em;
        }

        .status-pill {
            font-size: .75rem;
            border-radius: 999px;
        }

        .chart-wrap {
            position: relative;
            height: 300px;
            border: 1px solid rgba(15, 23, 42, .08);
            border-radius: 16px;
            padding: .65rem;
            background:
                radial-gradient(560px 180px at 100% 0%, rgba(14, 165, 233, .10), transparent 58%),
                radial-gradient(420px 160px at 0% 100%, rgba(22, 101, 52, .09), transparent 60%),
                linear-gradient(180deg, rgba(255, 255, 255, .94), rgba(248, 250, 252, .88));
        }

        .chart-wrap canvas { width: 100% !important; height: 100% !important; }

        .status-chip {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            border-radius: 999px;
            padding: .32rem .62rem;
            font-size: .76rem;
            border: 1px solid rgba(15, 23, 42, .12);
            background: rgba(15, 23, 42, .03);
            color: rgba(15, 23, 42, .72);
        }

        .table thead th {
            background: rgba(15, 23, 42, .04);
            border-bottom: 0;
            color: rgba(15, 23, 42, .62);
            font-size: .75rem;
            letter-spacing: .06em;
        }

        .user-avatar {
            width: 34px;
            height: 34px;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, rgba(22, 101, 52, .18), rgba(14, 165, 233, .18));
            border: 1px solid rgba(21, 128, 61, .24);
            color: rgba(15, 23, 42, .78);
            font-size: .75rem;
            font-weight: 800;
        }

        .analytics-list {
            display: grid;
            gap: .55rem;
        }

        .analytics-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .8rem;
            border: 1px solid rgba(15, 23, 42, .10);
            border-radius: .75rem;
            padding: .55rem .7rem;
            background: rgba(255, 255, 255, .76);
        }

        .analytics-item .name {
            font-size: .85rem;
            font-weight: 600;
            color: rgba(15, 23, 42, .84);
        }

        .analytics-item .meta {
            font-size: .76rem;
            color: var(--muted);
            margin-top: .1rem;
        }

        .analytics-item .count {
            font-size: .82rem;
            font-weight: 700;
            color: #14532d;
            border: 1px solid rgba(22, 101, 52, .22);
            background: rgba(167, 243, 208, .24);
            border-radius: 999px;
            padding: .15rem .52rem;
            white-space: nowrap;
        }

        .gender-pill {
            border: 1px solid rgba(15, 23, 42, .10);
            border-radius: .75rem;
            background: rgba(248, 250, 252, .9);
            padding: .6rem .7rem;
        }

        .map-wrap {
            border: 1px solid rgba(15, 23, 42, .10);
            border-radius: 12px;
            overflow: hidden;
            background: #eef2f7;
        }

        .map-canvas {
            width: 100%;
            height: 380px;
        }

        .map-legend {
            font-size: .8rem;
            color: var(--muted);
            margin-top: .55rem;
        }

        @media (max-width: 575.98px) {
            .hero-actions { width: 100%; }
            .hero-actions .btn { flex: 1 1 auto; }
            .chart-wrap { height: 250px; padding: .45rem; }
        }
    </style>

    <div class="container-fluid px-0 admin-page">
        <div class="hero p-3 p-lg-4 mb-4">
            <div class="d-flex flex-column flex-xl-row align-items-xl-start justify-content-between gap-3 gap-lg-4">
                <div>
                    <div class="small muted fw-semibold text-uppercase" style="letter-spacing:.08em;">Admin Dashboard</div>
                    <h1 class="h2 mb-2 page-title">Control Center Overview</h1>
                    <div class="small muted">Real-time snapshot of users, approvals, bookings, onboarding, and issue reports.</div>

                    <div class="d-flex flex-wrap gap-2 mt-3">
                        <span class="hero-chip"><i class="bi bi-people"></i> {{ $totalUsers }} users</span>
                        <span class="hero-chip"><i class="bi bi-check2-circle"></i> {{ $pendingApprovals }} approvals pending</span>
                        <span class="hero-chip"><i class="bi bi-journal-check"></i> {{ $totalBookings ?? 0 }} bookings tracked</span>
                    </div>
                </div>

                <div class="d-flex flex-wrap gap-2 hero-actions">
                    <a class="btn btn-outline-success" href="{{ route('admin.properties.pending') }}">
                        <i class="bi bi-check2-circle me-1"></i> Review Approvals
                        @if(($pendingApprovals ?? 0) > 0)
                            <span class="badge text-bg-danger ms-1">{{ $pendingApprovals }}</span>
                        @endif
                    </a>
                    <a class="btn btn-outline-secondary" href="{{ route('admin.bookings.index') }}">
                        <i class="bi bi-journal-check me-1"></i> Monitor Bookings
                    </a>
                    <button class="btn btn-brand" onclick="location.reload()">
                        <i class="bi bi-arrow-clockwise me-1"></i> Refresh
                    </button>
                </div>
            </div>

            <div class="d-flex flex-wrap gap-3 gap-lg-4 pt-3 mt-3 insights-row">
                <span class="insight-pill"><i class="bi bi-calendar2-plus"></i> New today: <strong class="text-dark">{{ $todayNew }}</strong></span>
                <span class="insight-pill"><i class="bi bi-graph-up-arrow"></i> 7-day growth: <strong class="text-dark">{{ $growthPct }}%</strong></span>
                <span class="insight-pill"><i class="bi bi-flag"></i> Reports pending: <strong class="text-dark">{{ $pendingReports }}</strong></span>
            </div>
        </div>

        <div class="row g-3 g-lg-4 mb-4">
            <div class="col-12 col-md-6 col-xl-3">
                <div class="kpi p-3 p-lg-4 h-100">
                    <a class="text-decoration-none" href="{{ route('admin.users.index') }}">
                        <div class="d-flex align-items-start gap-3">
                            <div class="kpi-icon"><i class="bi bi-people"></i></div>
                            <div class="min-w-0">
                                <div class="kpi-value h3 mb-0">{{ $totalUsers }}</div>
                                <div class="kpi-label">Total Users</div>
                                <div class="kpi-meta">Students: {{ $roleCounts['student'] ?? 0 }} • Landlords: {{ $roleCounts['landlord'] ?? 0 }}</div>
                            </div>
                        </div>
                    </a>
                </div>
            </div>

            <div class="col-12 col-md-6 col-xl-3">
                <div class="kpi p-3 p-lg-4 h-100">
                    <a class="text-decoration-none" href="{{ route('admin.properties.index') }}">
                        <div class="d-flex align-items-start gap-3">
                            <div class="kpi-icon"><i class="bi bi-buildings"></i></div>
                            <div class="min-w-0">
                                <div class="kpi-value h3 mb-0">{{ $totalProperties }}</div>
                                <div class="kpi-label">Properties</div>
                                <div class="kpi-meta">
                                    Pending approvals:
                                    @if(($pendingApprovals ?? 0) > 0)
                                        <span class="badge text-bg-danger status-pill">{{ $pendingApprovals }}</span>
                                    @else
                                        <span class="badge text-bg-success status-pill">0</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            </div>

            <div class="col-12 col-md-6 col-xl-3">
                <div class="kpi p-3 p-lg-4 h-100">
                    <a class="text-decoration-none" href="{{ route('admin.bookings.index') }}">
                        <div class="d-flex align-items-start gap-3">
                            <div class="kpi-icon"><i class="bi bi-journal-check"></i></div>
                            <div class="min-w-0">
                                <div class="kpi-value h3 mb-0">{{ $totalBookings ?? 0 }}</div>
                                <div class="kpi-label">Bookings</div>
                                <div class="kpi-meta">
                                    Pending:
                                    @if(($pendingBookings ?? 0) > 0)
                                        <span class="badge text-bg-warning status-pill">{{ $pendingBookings }}</span>
                                    @else
                                        <span class="badge text-bg-success status-pill">0</span>
                                    @endif
                                    <span class="ms-2">Approved: <span class="badge text-bg-secondary status-pill">{{ $approvedBookings ?? 0 }}</span></span>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            </div>

            <div class="col-12 col-md-6 col-xl-3">
                <div class="kpi p-3 p-lg-4 h-100">
                    <a class="text-decoration-none" href="{{ route('admin.onboardings.index') }}">
                        <div class="d-flex align-items-start gap-3">
                            <div class="kpi-icon"><i class="bi bi-clipboard-check"></i></div>
                            <div class="min-w-0">
                                <div class="kpi-value h3 mb-0">{{ $totalOnboardings }}</div>
                                <div class="kpi-label">Onboardings</div>
                                <div class="kpi-meta">Active: {{ $activeOnboardings }} • Completed: {{ $completedOnboardings }}</div>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </div>

        <div class="row g-3 g-lg-4 mb-4">
            <div class="col-12 col-xl-5">
                <div class="section-card h-100">
                    <div class="card-header d-flex align-items-center justify-content-between px-3 px-lg-4 py-3">
                        <div class="header-title"><i class="bi bi-house-check me-2"></i>Boarded Students Per BH</div>
                        <div class="status-chip">Active boarders</div>
                    </div>
                    <div class="card-body px-3 px-lg-4 py-4">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div class="small muted">Total active boarded students</div>
                            <span class="badge text-bg-success">{{ $activeBoardedStudents }}</span>
                        </div>

                        <div class="analytics-list">
                            @forelse($boardedByBoardingHouse as $bh)
                                <div class="analytics-item">
                                    <div class="min-w-0">
                                        <div class="name text-truncate">{{ $bh->name }}</div>
                                        <div class="meta text-truncate">{{ $bh->address ?: 'Address not set' }}</div>
                                    </div>
                                    <span class="count">{{ $bh->total_students }} student{{ (int) $bh->total_students === 1 ? '' : 's' }}</span>
                                </div>
                            @empty
                                <div class="text-muted small">No active boarded students found.</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-xl-7">
                <div class="section-card h-100">
                    <div class="card-header d-flex align-items-center justify-content-between px-3 px-lg-4 py-3">
                        <div class="header-title"><i class="bi bi-mortarboard me-2"></i>Boarded Students Per Program</div>
                        <div class="status-chip">Academic profile</div>
                    </div>
                    <div class="card-body px-3 px-lg-4 py-4">
                        <div class="analytics-list mb-3">
                            @forelse($boardedByProgram as $program)
                                <div class="analytics-item">
                                    <div class="name">{{ $program->program }}</div>
                                    <span class="count">{{ $program->total_students }}</span>
                                </div>
                            @empty
                                <div class="text-muted small">No program data yet.</div>
                            @endforelse
                        </div>

                        <div class="small muted">Male/Female distribution (active boarders)</div>
                        <div class="row g-2 mt-1">
                            <div class="col-4">
                                <div class="gender-pill text-center">
                                    <div class="small muted">Male</div>
                                    <div class="fw-bold text-dark">{{ $genderCounts['male'] ?? 0 }}</div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="gender-pill text-center">
                                    <div class="small muted">Female</div>
                                    <div class="fw-bold text-dark">{{ $genderCounts['female'] ?? 0 }}</div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="gender-pill text-center">
                                    <div class="small muted">Unspecified</div>
                                    <div class="fw-bold text-dark">{{ $genderCounts['unspecified'] ?? 0 }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="section-card mb-4">
            <div class="card-header d-flex align-items-center justify-content-between px-3 px-lg-4 py-3">
                <div class="header-title"><i class="bi bi-geo-alt me-2"></i>Landlord Property Locations</div>
                <div class="status-chip">Approved properties with coordinates</div>
            </div>
            <div class="card-body px-3 px-lg-4 py-4">
                @if(($landlordMapPoints ?? collect())->count() > 0)
                    <div class="map-wrap">
                        <div id="adminLandlordMap" class="map-canvas"></div>
                    </div>
                    <div class="map-legend">
                        <i class="bi bi-pin-map me-1"></i>{{ ($landlordMapPoints ?? collect())->count() }} mapped landlord propert{{ ($landlordMapPoints ?? collect())->count() === 1 ? 'y' : 'ies' }}
                    </div>
                @else
                    <div class="alert alert-warning mb-0 small">
                        No landlord properties with coordinates yet. Add latitude and longitude on approved properties to display map markers.
                    </div>
                @endif
            </div>
        </div>

        <div class="row g-3 g-lg-4 mb-4">
            <div class="col-12 col-xl-6">
                <div class="section-card h-100">
                    <div class="card-header d-flex align-items-center justify-content-between px-3 px-lg-4 py-3">
                        <div class="header-title"><i class="bi bi-graph-up me-2"></i> Registrations (Last 30 Days)</div>
                        <div class="status-chip" id="rangeLabel">Loading...</div>
                    </div>
                    <div class="card-body px-3 px-lg-4 py-4">
                        <div class="chart-wrap">
                            <canvas id="chartRegistrations"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-xl-6">
                <div class="section-card h-100">
                    <div class="card-header d-flex align-items-center justify-content-between px-3 px-lg-4 py-3">
                        <div class="header-title"><i class="bi bi-journal-check me-2"></i> Booking Status</div>
                        <div class="status-chip">All-time</div>
                    </div>
                    <div class="card-body px-3 px-lg-4 py-4">
                        <div class="chart-wrap">
                            <canvas id="chartBookings"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 g-lg-4 mb-4">
            <div class="col-12 col-xl-6">
                <div class="section-card h-100">
                    <div class="card-header d-flex align-items-center justify-content-between px-3 px-lg-4 py-3">
                        <div class="header-title"><i class="bi bi-check2-circle me-2"></i> Approval Activity</div>
                        <div class="status-chip">Approved vs Rejected</div>
                    </div>
                    <div class="card-body px-3 px-lg-4 py-4">
                        <div class="chart-wrap">
                            <canvas id="chartApprovals"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-xl-6">
                <div class="section-card h-100">
                    <div class="card-header d-flex align-items-center justify-content-between px-3 px-lg-4 py-3">
                        <div class="header-title"><i class="bi bi-door-open me-2"></i> Room Status</div>
                        <div class="status-chip">All-time</div>
                    </div>
                    <div class="card-body px-3 px-lg-4 py-4">
                        <div class="chart-wrap">
                            <canvas id="chartRooms"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 g-lg-4 mb-4">
            <div class="col-12 col-xl-7">
                <div class="section-card">
                    <div class="card-header d-flex align-items-center justify-content-between px-3 px-lg-4 py-3">
                        <div class="header-title">
                            <i class="bi bi-activity me-2"></i> Activity Snapshot
                        </div>
                        <div class="status-chip">Last 7 days + today</div>
                    </div>
                    <div class="card-body px-3 px-lg-4 py-4">
                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <div class="p-3 rounded-4" style="background: rgba(22,101,52,.07); border: 1px solid rgba(22,101,52,.16);">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div>
                                            <div class="small muted">Growth (7d)</div>
                                            <div class="h2 fw-bold mb-0">{{ $growthPct }}%</div>
                                        </div>
                                        <div class="kpi-icon" style="background: rgba(245,158,11,.16); border-color: rgba(245,158,11,.26); color: rgba(180,83,9,1);"><i class="bi bi-graph-up"></i></div>
                                    </div>
                                    <div class="small muted mt-2">{{ $last7DaysNew }} new users in the last 7 days.</div>
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="p-3 rounded-4" style="background: rgba(2,8,20,.03); border: 1px solid rgba(2,8,20,.06);">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div>
                                            <div class="small muted">New Today</div>
                                            <div class="h2 fw-bold mb-0">{{ $todayNew }}</div>
                                        </div>
                                        <div class="kpi-icon" style="background: rgba(2,8,20,.06); border-color: rgba(2,8,20,.10); color: rgba(2,8,20,.70);"><i class="bi bi-calendar2-plus"></i></div>
                                    </div>
                                    <div class="small muted mt-2">Registrations within the last 24 hours.</div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4">
                            <div class="header-title mb-2"><i class="bi bi-gear me-2"></i> System Status</div>
                            <div class="d-flex flex-wrap gap-2">
                                <span class="status-chip"><i class="bi bi-collection-play"></i> Queue: {{ $systemStatus['queue'] }}</span>
                                <span class="status-chip"><i class="bi bi-hdd-rack"></i> Cache: {{ $systemStatus['cache'] }}</span>
                                <span class="status-chip"><i class="bi bi-envelope-paper"></i> Mail: {{ $systemStatus['mail'] }}</span>
                                <span class="status-chip"><i class="bi bi-layers"></i> Laravel: {{ $systemStatus['version'] }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-xl-5">
                <div class="section-card h-100">
                    <div class="card-header d-flex align-items-center justify-content-between px-3 px-lg-4 py-3">
                        <div class="header-title"><i class="bi bi-flag me-2"></i> Reports</div>
                        <a class="small text-decoration-none fw-semibold" href="{{ route('admin.reports.index') }}">View all</a>
                    </div>
                    <div class="card-body px-3 px-lg-4 py-4">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <div class="small muted">Total reports</div>
                                <div class="h2 fw-bold mb-0">{{ $totalReports }}</div>
                            </div>
                            <div class="kpi-icon" style="background: rgba(220,38,38,.08); border-color: rgba(220,38,38,.16); color: rgba(220,38,38,1);"><i class="bi bi-exclamation-triangle"></i></div>
                        </div>

                        <hr class="my-3">

                        <div class="d-flex align-items-center justify-content-between">
                            <div class="small muted">Pending</div>
                            @if($pendingReports > 0)
                                <span class="badge text-bg-danger">{{ $pendingReports }} pending</span>
                            @else
                                <span class="badge text-bg-success">All resolved</span>
                            @endif
                        </div>

                        <div class="small muted mt-3">
                            Use this section to monitor student/landlord issues and keep operations clean.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="section-card">
            <div class="card-header d-flex align-items-center justify-content-between px-3 px-lg-4 py-3">
                <div class="header-title"><i class="bi bi-person-plus me-2"></i> Recent Registrations</div>
                <a class="small text-decoration-none fw-semibold" href="{{ route('admin.users.index') }}">Manage users</a>
            </div>
            <div class="card-body px-0">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="small text-uppercase">
                            <tr>
                                <th class="ps-4">Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th class="pe-4">Registered</th>
                            </tr>
                        </thead>
                        <tbody class="small">
                            @forelse($recentUsers as $u)
                                <tr>
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="user-avatar">{{ strtoupper(substr($u->full_name, 0, 1)) }}</span>
                                            <div>
                                                <div class="fw-semibold">{{ $u->full_name }}</div>
                                                <div class="small muted">{{ $u->created_at->format('M d, Y') }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $u->email }}</td>
                                    <td>
                                        <span class="badge @if($u->role==='admin') text-bg-primary @elseif($u->role==='landlord') text-bg-warning @else text-bg-success @endif">
                                            {{ ucfirst($u->role) }}
                                        </span>
                                    </td>
                                    <td class="pe-4">{{ $u->created_at->diffForHumans() }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">No users yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
    <script>
        (function () {
            const landlordPoints = @json($landlordMapPoints ?? []);

            const colors = {
                brand: 'rgba(22,101,52,1)',
                brandSoft: 'rgba(22,101,52,.18)',
                teal: 'rgba(21,128,61,1)',
                tealSoft: 'rgba(21,128,61,.18)',
                gold: 'rgba(245,158,11,1)',
                danger: 'rgba(220,38,38,1)',
                dangerSoft: 'rgba(220,38,38,.18)',
                gray: 'rgba(2,8,20,.55)',
            };

            const fmt = (iso) => {
                const d = new Date(iso + 'T00:00:00');
                return d.toLocaleDateString(undefined, { month: 'short', day: 'numeric' });
            };

            const setRangeLabel = (text) => {
                const el = document.getElementById('rangeLabel');
                if (el) el.textContent = text;
            };

            const buildLine = (ctx, labels, data) => {
                const gradient = ctx.getContext('2d').createLinearGradient(0, 0, 0, 280);
                gradient.addColorStop(0, 'rgba(21,128,61,.34)');
                gradient.addColorStop(1, 'rgba(21,128,61,.03)');

                return new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels,
                        datasets: [{
                            label: 'New users',
                            data,
                            borderColor: colors.brand,
                            backgroundColor: gradient,
                            fill: true,
                            tension: 0.42,
                            borderWidth: 2.75,
                            pointRadius: 2.5,
                            pointHoverRadius: 5,
                            pointBackgroundColor: '#ffffff',
                            pointBorderColor: colors.brand,
                            pointBorderWidth: 2,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        animation: { duration: 900, easing: 'easeOutQuart' },
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                intersect: false,
                                mode: 'index',
                                backgroundColor: 'rgba(2,8,20,.86)',
                                titleColor: '#e2e8f0',
                                bodyColor: '#f8fafc',
                                padding: 10,
                                cornerRadius: 10,
                                displayColors: false
                            },
                        },
                        scales: {
                            x: {
                                grid: { display: false },
                                ticks: { maxTicksLimit: 10, color: colors.gray, padding: 8, font: { size: 11 } }
                            },
                            y: {
                                beginAtZero: true,
                                ticks: { precision: 0, color: colors.gray, padding: 8, font: { size: 11 } },
                                grid: { color: 'rgba(148,163,184,0.22)', drawBorder: false }
                            }
                        }
                    }
                });
            };

            const initLandlordMap = () => {
                const mapEl = document.getElementById('adminLandlordMap');
                if (!mapEl || typeof L === 'undefined' || !Array.isArray(landlordPoints) || landlordPoints.length === 0) {
                    return;
                }

                const map = L.map(mapEl, {
                    zoomControl: true,
                    attributionControl: true,
                });

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '&copy; OpenStreetMap contributors'
                }).addTo(map);

                const bounds = [];

                landlordPoints.forEach((point) => {
                    if (typeof point.latitude !== 'number' || typeof point.longitude !== 'number') return;

                    const marker = L.marker([point.latitude, point.longitude]).addTo(map);
                    const popupHtml = `
                        <div style="min-width:220px;">
                            <div style="font-weight:700;color:#0f172a;">${point.name || 'Property'}</div>
                            <div style="font-size:12px;color:#475569;margin-top:2px;">${point.address || 'Address not set'}</div>
                            <div style="font-size:12px;color:#334155;margin-top:8px;"><strong>Landlord:</strong> ${point.landlord_name || 'N/A'}</div>
                            <div style="font-size:12px;color:#64748b;">${point.landlord_email || ''}</div>
                        </div>
                    `;
                    marker.bindPopup(popupHtml);
                    bounds.push([point.latitude, point.longitude]);
                });

                if (bounds.length === 1) {
                    map.setView(bounds[0], 14);
                } else if (bounds.length > 1) {
                    map.fitBounds(bounds, { padding: [24, 24] });
                } else {
                    map.setView([12.8797, 121.7740], 5);
                }
            };

            const buildDoughnut = (ctx, labels, data, palette) => new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels,
                    datasets: [{
                        data,
                        backgroundColor: palette,
                        borderWidth: 2,
                        borderColor: 'rgba(255,255,255,0.95)',
                        hoverOffset: 10,
                        spacing: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: { duration: 900, easing: 'easeOutQuart' },
                    cutout: '70%',
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                boxWidth: 10,
                                boxHeight: 10,
                                usePointStyle: true,
                                pointStyle: 'circle',
                                color: colors.gray,
                                padding: 12,
                                font: { size: 11, weight: '600' }
                            }
                        },
                        tooltip: {
                            callbacks: { label: (ctx) => `${ctx.label}: ${ctx.raw}` },
                            backgroundColor: 'rgba(2,8,20,.86)',
                            titleColor: '#e2e8f0',
                            bodyColor: '#f8fafc',
                            padding: 10,
                            cornerRadius: 10
                        }
                    }
                }
            });

            const buildPolar = (ctx, labels, data, palette) => new Chart(ctx, {
                type: 'polarArea',
                data: {
                    labels,
                    datasets: [{
                        data,
                        backgroundColor: palette,
                        borderWidth: 2,
                        borderColor: 'rgba(255,255,255,0.95)',
                        hoverOffset: 8
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: { duration: 950, easing: 'easeOutQuart' },
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                boxWidth: 10,
                                boxHeight: 10,
                                usePointStyle: true,
                                pointStyle: 'circle',
                                color: colors.gray,
                                padding: 12,
                                font: { size: 11, weight: '600' }
                            }
                        },
                        tooltip: {
                            callbacks: { label: (ctx) => `${ctx.label}: ${ctx.raw}` },
                            backgroundColor: 'rgba(2,8,20,.86)',
                            titleColor: '#e2e8f0',
                            bodyColor: '#f8fafc',
                            padding: 10,
                            cornerRadius: 10
                        }
                    },
                    scales: {
                        r: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0,
                                color: colors.gray,
                                backdropColor: 'rgba(255,255,255,.72)',
                                font: { size: 10 }
                            },
                            grid: { color: 'rgba(148,163,184,0.22)' },
                            angleLines: { color: 'rgba(148,163,184,0.16)' }
                        }
                    }
                }
            });

            const buildBar = (ctx, labels, approved, rejected) => new Chart(ctx, {
                type: 'bar',
                data: {
                    labels,
                    datasets: [
                        {
                            label: 'Approved',
                            data: approved,
                            backgroundColor: 'rgba(21,128,61,.32)',
                            borderColor: colors.teal,
                            borderWidth: 1,
                            borderRadius: 8,
                            maxBarThickness: 18
                        },
                        {
                            label: 'Rejected',
                            data: rejected,
                            backgroundColor: 'rgba(220,38,38,.24)',
                            borderColor: colors.danger,
                            borderWidth: 1,
                            borderRadius: 8,
                            maxBarThickness: 18
                        },
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: { duration: 850, easing: 'easeOutQuart' },
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                boxWidth: 10,
                                boxHeight: 10,
                                usePointStyle: true,
                                pointStyle: 'circle',
                                color: colors.gray,
                                padding: 12,
                                font: { size: 11, weight: '600' }
                            }
                        },
                        tooltip: {
                            intersect: false,
                            mode: 'index',
                            backgroundColor: 'rgba(2,8,20,.86)',
                            titleColor: '#e2e8f0',
                            bodyColor: '#f8fafc',
                            padding: 10,
                            cornerRadius: 10
                        }
                    },
                    scales: {
                        x: {
                            stacked: false,
                            grid: { display: false },
                            ticks: { maxTicksLimit: 10, color: colors.gray, padding: 8, font: { size: 11 } }
                        },
                        y: {
                            beginAtZero: true,
                            ticks: { precision: 0, color: colors.gray, padding: 8, font: { size: 11 } },
                            grid: { color: 'rgba(148,163,184,0.22)', drawBorder: false }
                        }
                    }
                }
            });

            async function init() {
                try {
                    const res = await fetch("{{ route('admin.dashboard.stats') }}?days=30", {
                        headers: { 'Accept': 'application/json' },
                        credentials: 'same-origin'
                    });
                    if (!res.ok) throw new Error('Failed to load dashboard stats');
                    const stats = await res.json();

                    const labels = (stats.labels || []).map(fmt);
                    setRangeLabel(`${fmt(stats.range.start)} – ${fmt(stats.range.end)}`);

                    const regCtx = document.getElementById('chartRegistrations');
                    const bookCtx = document.getElementById('chartBookings');
                    const apprCtx = document.getElementById('chartApprovals');
                    const roomCtx = document.getElementById('chartRooms');

                    if (regCtx) buildLine(regCtx, labels, stats.registrations || []);

                    if (bookCtx) {
                        const bs = stats.bookingStatus || {};
                        buildPolar(bookCtx,
                            ['Pending', 'Approved', 'Rejected', 'Cancelled'],
                            [bs.pending || 0, bs.approved || 0, bs.rejected || 0, bs.cancelled || 0],
                            [
                                'rgba(245,158,11,.85)',
                                'rgba(22,101,52,.85)',
                                'rgba(239,68,68,.85)',
                                'rgba(107,114,128,.85)'
                            ]
                        );
                    }

                    if (apprCtx) {
                        const a = stats.approvals || {};
                        buildBar(apprCtx, labels, a.approved || [], a.rejected || []);
                    }

                    if (roomCtx) {
                        const rs = stats.roomStatus || {};
                        buildDoughnut(roomCtx,
                            ['Available', 'Occupied', 'Maintenance'],
                            [rs.available || 0, rs.occupied || 0, rs.maintenance || 0],
                            [
                                'rgba(22,101,52,.85)',
                                'rgba(245,158,11,.85)',
                                'rgba(239,68,68,.85)'
                            ]
                        );
                    }
                } catch (e) {
                    setRangeLabel('Stats unavailable');
                    console.error(e);
                }
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', () => {
                    init();
                    initLandlordMap();
                });
            } else {
                init();
                initLandlordMap();
            }
        })();
    </script>
@endsection