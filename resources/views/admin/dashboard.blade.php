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
            --brand-soft: #f0fdf4;
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
            font-size: 1.55rem;
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
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.15rem;
            flex-shrink: 0;
        }

        .kpi-val {
            font-size: 1.75rem;
            font-weight: 800;
            line-height: 1.1;
            letter-spacing: -0.02em;
            color: #0f172a;
        }

        .kpi-title {
            font-size: 0.82rem;
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
            padding: 1rem 1.25rem;
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

        .bh-bar-item {
            padding: 0.75rem 0.85rem;
            border-radius: 10px;
            border: 1px solid #f1f5f9;
            background: #fafafa;
            margin-bottom: 0.65rem;
            transition: background 0.15s ease;
        }

        .bh-bar-item:hover {
            background: #f8fafc;
            border-color: #e2e8f0;
        }

        .bh-progress-bg {
            height: 7px;
            border-radius: 999px;
            background: #e2e8f0;
            overflow: hidden;
            margin-top: 0.5rem;
        }

        .bh-progress-fill {
            height: 100%;
            border-radius: 999px;
            background: linear-gradient(90deg, #166534, #22c55e);
        }

        .attention-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.75rem 0.85rem;
            border-radius: 10px;
            border: 1px solid #f1f5f9;
            background: #ffffff;
            margin-bottom: 0.6rem;
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
            padding: 0.65rem 0;
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

        .academic-metric-box {
            padding: 0.75rem;
            border-radius: 10px;
            background: #f8fafc;
            border: 1px solid #f1f5f9;
            text-align: center;
        }

        .map-container {
            height: 320px;
            width: 100%;
            border-radius: 10px;
            overflow: hidden;
            border: 1px solid #e2e8f0;
        }

        .chart-trend-box {
            position: relative;
            height: 200px;
            width: 100%;
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

        {{-- ROW 1: Greeting & Calm Monitoring Intro --}}
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-2 mb-4">
            <div>
                @php
                    $hour = (int) now()->format('H');
                    $greetingPrefix = $hour < 12 ? 'Good morning' : ($hour < 18 ? 'Good afternoon' : 'Good evening');
                @endphp
                <h1 class="page-greeting mb-1">{{ $greetingPrefix }}, {{ Auth::user()->full_name ?? 'Administrator' }}</h1>
                <p class="page-subtext mb-0">Here is the current MinSU boarding-house monitoring and occupancy overview.</p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge badge-soft-green px-3 py-2 rounded-pill small">
                    <i class="bi bi-shield-check me-1"></i> System Healthy
                </span>
                <a href="{{ route('admin.boarding_monitoring.students') }}" class="btn btn-sm btn-outline-success rounded-pill px-3 fw-semibold">
                    <i class="bi bi-door-open me-1"></i> Boarding Monitor
                </a>
            </div>
        </div>

        {{-- ROW 2: Primary Summary Cards (Exactly 4 High-Value Monitoring Metrics) --}}
        @php
            $totalPendingApprovals = ($pendingApprovals ?? 0) + ($pendingPermitApprovals ?? 0);
            $totalNeedsAttention = $totalPendingApprovals + ($pendingReports ?? 0);
        @endphp
        <div class="row g-3 mb-4">
            {{-- Card 1: Active Boarded Students --}}
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
                            <span class="text-success fw-semibold"><i class="bi bi-check-circle me-1"></i>Currently boarded</span> students
                        </div>
                    </div>
                </a>
            </div>

            {{-- Card 2: Boarding Houses --}}
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
                            <span class="text-muted">{{ $activeProperties ?? 0 }} with active rooms</span>
                        </div>
                    </div>
                </a>
            </div>

            {{-- Card 3: Pending Approvals --}}
            <div class="col-12 col-sm-6 col-xl-3">
                <a href="{{ route('admin.approvals.landlords') }}" class="text-decoration-none">
                    <div class="kpi-card">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <div class="kpi-title">Pending Approvals</div>
                                <div class="kpi-val {{ $totalPendingApprovals > 0 ? 'text-warning' : 'text-dark' }}">{{ $totalPendingApprovals }}</div>
                            </div>
                            <div class="kpi-icon-wrap" style="background: #fef3c7; color: #b45309;">
                                <i class="bi bi-hourglass-split"></i>
                            </div>
                        </div>
                        <div class="kpi-sub">
                            @if($totalPendingApprovals > 0)
                                <span class="text-warning fw-semibold">{{ $totalPendingApprovals }} item(s) awaiting review</span>
                            @else
                                <span class="text-success"><i class="bi bi-check2"></i> All reviews completed</span>
                            @endif
                        </div>
                    </div>
                </a>
            </div>

            {{-- Card 4: Reports / Open Issues --}}
            <div class="col-12 col-sm-6 col-xl-3">
                <a href="{{ route('admin.reports.index') }}" class="text-decoration-none">
                    <div class="kpi-card">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <div class="kpi-title">Open Reports</div>
                                <div class="kpi-val {{ ($pendingReports ?? 0) > 0 ? 'text-danger' : 'text-dark' }}">{{ $pendingReports ?? 0 }}</div>
                            </div>
                            <div class="kpi-icon-wrap" style="background: #fee2e2; color: #b91c1c;">
                                <i class="bi bi-flag"></i>
                            </div>
                        </div>
                        <div class="kpi-sub">
                            @if(($pendingReports ?? 0) > 0)
                                <span class="text-danger fw-semibold">{{ $pendingReports }} unresolved issue(s)</span>
                            @else
                                <span class="text-success"><i class="bi bi-check2"></i> No unresolved reports</span>
                            @endif
                        </div>
                    </div>
                </a>
            </div>
        </div>

        {{-- ROW 3: Primary Monitoring (Student Distribution + Needs Attention) --}}
        <div class="row g-3 mb-4">
            {{-- Primary Left: Boarded Student Distribution (Dominant Section) --}}
            <div class="col-12 col-lg-8">
                <div class="dash-card h-100">
                    <div class="dash-card-header">
                        <h2 class="dash-card-title">
                            <i class="bi bi-bar-chart-steps text-success"></i>
                            Boarded Student Distribution
                        </h2>
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge badge-soft-green rounded-pill px-2.5 py-1 text-xs">
                                Total: {{ $activeBoardedStudents ?? 0 }} Active
                            </span>
                            <a href="{{ route('admin.boarding_monitoring.students') }}" class="text-xs text-decoration-none fw-semibold text-success ms-1">
                                View Full List <i class="bi bi-chevron-right"></i>
                            </a>
                        </div>
                    </div>
                    <div class="dash-card-body">
                        @php
                            $maxStudents = $boardedByBoardingHouse->max('total_students') ?: 1;
                        @endphp

                        @if($boardedByBoardingHouse->count() > 0)
                            <div class="text-xs text-muted mb-3 d-flex justify-content-between align-items-center">
                                <span>Showing top boarding houses by verified resident count:</span>
                                <span>{{ $boardedByBoardingHouse->count() }} active propert{{ $boardedByBoardingHouse->count() === 1 ? 'y' : 'ies' }}</span>
                            </div>

                            @foreach($boardedByBoardingHouse as $bh)
                                @php
                                    $pct = round(($bh->total_students / $maxStudents) * 100);
                                @endphp
                                <div class="bh-bar-item">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div class="min-w-0 pe-2">
                                            <div class="fw-bold text-dark text-truncate" style="font-size: 0.88rem;">{{ $bh->name }}</div>
                                            <div class="text-muted text-truncate" style="font-size: 0.75rem;">
                                                <i class="bi bi-geo-alt me-1"></i>{{ $bh->address ?: 'Calapan City' }}
                                            </div>
                                        </div>
                                        <div class="text-end flex-shrink-0">
                                            <span class="badge badge-soft-green rounded-pill px-2 py-1" style="font-size: 0.78rem;">
                                                {{ $bh->total_students }} student{{ (int)$bh->total_students === 1 ? '' : 's' }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="bh-progress-bg" title="{{ $bh->total_students }} students">
                                        <div class="bh-progress-fill" style="width: {{ max(6, $pct) }}%;"></div>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="text-center py-5 text-muted">
                                <div class="mb-2" style="font-size: 2rem; color: #cbd5e1;">
                                    <i class="bi bi-house-door"></i>
                                </div>
                                <div class="fw-semibold text-dark small">No Active Boarded Students Yet</div>
                                <p class="text-xs text-muted mb-0">Student distribution will display here as approved boarding records become active.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Primary Right: Needs Attention Module --}}
            <div class="col-12 col-lg-4">
                <div class="dash-card h-100">
                    <div class="dash-card-header">
                        <h2 class="dash-card-title">
                            <i class="bi bi-exclamation-circle text-warning"></i>
                            Needs Attention
                        </h2>
                        @if($totalNeedsAttention > 0)
                            <span class="badge badge-soft-amber rounded-pill px-2 py-1 text-xs">
                                {{ $totalNeedsAttention }} Pending
                            </span>
                        @else
                            <span class="badge badge-soft-green rounded-pill px-2 py-1 text-xs">
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
                                <div class="text-muted text-xs mt-1">No pending approvals or open reports requiring immediate administrative review.</div>
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
                                        <div class="text-muted" style="font-size: 0.72rem;">Pending listing reviews</div>
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
                                        <div class="fw-semibold text-dark text-xs">Permit Verifications</div>
                                        <div class="text-muted" style="font-size: 0.72rem;">Business permit documents</div>
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
                                        <div class="text-muted" style="font-size: 0.72rem;">Enrollment proof reviews</div>
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
        </div>

        {{-- ROW 4: Secondary Monitoring (Map Locations & Recent Activity) --}}
        <div class="row g-3 mb-4">
            {{-- Left: Boarding House Locations Map --}}
            <div class="col-12 col-lg-7">
                <div class="dash-card h-100">
                    <div class="dash-card-header">
                        <h2 class="dash-card-title">
                            <i class="bi bi-geo-alt text-success"></i>
                            Boarding House Locations
                        </h2>
                        <span class="badge text-bg-light border text-xs text-muted">
                            <i class="bi bi-pin-map-fill text-success me-1"></i>{{ ($landlordMapPoints ?? collect())->count() }} Mapped
                        </span>
                    </div>
                    <div class="dash-card-body">
                        @if(($landlordMapPoints ?? collect())->count() > 0)
                            <div id="adminLandlordMap" class="map-container"></div>
                            <div class="text-xs text-muted mt-2 d-flex align-items-center justify-content-between">
                                <span>Approved properties registered with geographical coordinates.</span>
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

            {{-- Right: Recent Activity / Registrations Feed --}}
            <div class="col-12 col-lg-5">
                <div class="dash-card h-100">
                    <div class="dash-card-header">
                        <h2 class="dash-card-title">
                            <i class="bi bi-clock-history text-success"></i>
                            Recent System Activity
                        </h2>
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
                                            <span>Registered new account</span>
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

        {{-- ROW 5: Secondary Monitoring (Academic Snapshot & Registrations Trend) --}}
        <div class="row g-3">
            {{-- Academic Snapshot --}}
            <div class="col-12 col-lg-6">
                <div class="dash-card h-100">
                    <div class="dash-card-header">
                        <h2 class="dash-card-title">
                            <i class="bi bi-mortarboard text-success"></i>
                            Academic Snapshot
                        </h2>
                        <span class="badge text-bg-light border text-xs text-muted">
                            Active Boarders
                        </span>
                    </div>
                    <div class="dash-card-body">
                        @php
                            $academicColleges = collect($boardedByAcademic ?? []);
                            $academicCollegeCount = $academicColleges->count();
                            $programTotals = [];

                            foreach ($academicColleges as $college) {
                                foreach ((array) ($college->programs ?? []) as $program) {
                                    $pName = trim((string) ($program['name'] ?? 'Not specified'));
                                    if ($pName === '') $pName = 'Not specified';
                                    $programTotals[$pName] = ($programTotals[$pName] ?? 0) + (int) ($program['total_students'] ?? 0);
                                }
                            }
                            arsort($programTotals);
                            $academicProgramCount = count($programTotals);
                            $topCollegeObj = $academicColleges->sortByDesc(fn ($c) => (int) ($c->total_students ?? 0))->first();
                            $topCollegeName = $topCollegeObj ? ($topCollegeObj->college_name ?? 'Not specified') : 'None';
                            $topProgramName = array_key_first($programTotals) ?: 'None';
                        @endphp

                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <div class="academic-metric-box">
                                    <div class="text-xs text-muted text-uppercase fw-semibold">Colleges Represented</div>
                                    <div class="fw-bold text-dark fs-5 mt-1">{{ $academicCollegeCount }}</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="academic-metric-box">
                                    <div class="text-xs text-muted text-uppercase fw-semibold">Academic Programs</div>
                                    <div class="fw-bold text-dark fs-5 mt-1">{{ $academicProgramCount }}</div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="p-2 rounded-2 bg-light border text-xs">
                                    <div class="d-flex justify-content-between mb-1">
                                        <span class="text-muted fw-semibold">Top College:</span>
                                        <span class="text-dark fw-bold text-truncate text-end ms-2" style="max-width: 70%;">{{ $topCollegeName }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span class="text-muted fw-semibold">Top Program:</span>
                                        <span class="text-dark fw-bold text-truncate text-end ms-2" style="max-width: 70%;">{{ $topProgramName }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Gender Distribution Pills --}}
                        <div class="text-xs text-muted fw-semibold mb-2">Gender Distribution (Active Boarders)</div>
                        <div class="row g-2">
                            <div class="col-4">
                                <div class="academic-metric-box py-1.5">
                                    <div class="text-xs text-muted">Male</div>
                                    <div class="fw-bold text-dark text-sm">{{ $genderCounts['male'] ?? 0 }}</div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="academic-metric-box py-1.5">
                                    <div class="text-xs text-muted">Female</div>
                                    <div class="fw-bold text-dark text-sm">{{ $genderCounts['female'] ?? 0 }}</div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="academic-metric-box py-1.5">
                                    <div class="text-xs text-muted">Unspecified</div>
                                    <div class="fw-bold text-dark text-sm">{{ $genderCounts['unspecified'] ?? 0 }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Compact 30-Day Registrations Trend --}}
            <div class="col-12 col-lg-6">
                <div class="dash-card h-100">
                    <div class="dash-card-header">
                        <h2 class="dash-card-title">
                            <i class="bi bi-graph-up text-success"></i>
                            Registrations Trend (30 Days)
                        </h2>
                        <span class="badge text-bg-light border text-xs text-muted" id="rangeLabel">
                            Loading trend...
                        </span>
                    </div>
                    <div class="dash-card-body d-flex flex-column justify-content-between">
                        <div class="chart-trend-box">
                            <canvas id="chartRegistrations"></canvas>
                        </div>
                        <div class="d-flex align-items-center justify-content-between pt-2 border-top text-xs text-muted">
                            <span>Total Users: <strong class="text-dark">{{ $totalUsers ?? 0 }}</strong></span>
                            <span>7-Day Growth: <strong class="text-success">{{ $growthPct ?? 0 }}%</strong></span>
                            <a href="{{ \Illuminate\Support\Facades\Route::has('admin.analytics.index') ? route('admin.analytics.index') : url('/admin/analytics') }}" class="text-success text-decoration-none fw-semibold">
                                Full Analytics <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                    </div>
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
                brand: '#166534',
                brandLight: 'rgba(22, 101, 52, 0.12)',
                gray: '#64748b',
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
                    map.setView([13.4115, 121.1803], 13); // Default Calapan City
                }
            };

            const buildTrendChart = (ctx, labels, data) => {
                const canvasCtx = ctx.getContext('2d');
                const gradient = canvasCtx.createLinearGradient(0, 0, 0, 180);
                gradient.addColorStop(0, 'rgba(22, 101, 52, 0.22)');
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
                            pointRadius: 2,
                            pointHoverRadius: 4,
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
                initLandlordMap();
                initStats();
            });
        })();
    </script>
@endsection