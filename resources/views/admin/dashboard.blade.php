@extends('layouts.admin')

@section('content')
    <style>
        .page-title { letter-spacing: -.02em; }
        .muted { color: rgba(2,8,20,.62); }

        .kpi {
            border: 1px solid rgba(2,8,20,.08);
            border-radius: 1rem;
            background: #fff;
            box-shadow: 0 10px 26px rgba(2,8,20,.06);
        }
        .kpi .kpi-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(99,102,241,.10);
            border: 1px solid rgba(99,102,241,.18);
            color: rgba(79,70,229,1);
            flex: 0 0 auto;
        }
        .kpi .kpi-value { font-weight: 800; letter-spacing: -.02em; }
        .kpi .kpi-label { font-size: .85rem; color: rgba(2,8,20,.62); }
        .kpi a { color: inherit; }
        .kpi:hover { border-color: rgba(99,102,241,.22); box-shadow: 0 14px 34px rgba(2,8,20,.08); }

        .section-card { border: 1px solid rgba(2,8,20,.08); border-radius: 1rem; background: #fff; box-shadow: 0 10px 26px rgba(2,8,20,.06); }
        .section-card .card-header { background: #fff; border-bottom: 1px solid rgba(2,8,20,.06); border-top-left-radius: 1rem; border-top-right-radius: 1rem; }
        .table thead th { background: rgba(2,8,20,.03); border-bottom: 0; }
        .status-pill { font-size: .75rem; }

        .chart-wrap { height: 260px; }
        .chart-wrap canvas { width: 100% !important; height: 100% !important; }
    </style>

    <div class="container-fluid px-0">
        <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 mb-4">
            <div>
                <div class="small muted">Admin Dashboard</div>
                <h1 class="h3 mb-1 page-title">Overview</h1>
                <div class="small muted">Quick view of users, approvals, bookings, onboarding, and reports.</div>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a class="btn btn-outline-secondary rounded-pill" href="{{ route('admin.properties.pending') }}">
                    <i class="bi bi-check2-circle me-1"></i> Review Approvals
                    @if(($pendingApprovals ?? 0) > 0)
                        <span class="badge text-bg-danger ms-1">{{ $pendingApprovals }}</span>
                    @endif
                </a>
                <a class="btn btn-outline-secondary rounded-pill" href="{{ route('admin.bookings.index') }}">
                    <i class="bi bi-journal-check me-1"></i> Monitor Bookings
                </a>
                <button class="btn btn-brand rounded-pill" onclick="location.reload()">
                    <i class="bi bi-arrow-clockwise me-1"></i> Refresh
                </button>
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
                                <div class="small muted mt-2">Students: {{ $roleCounts['student'] ?? 0 }} • Landlords: {{ $roleCounts['landlord'] ?? 0 }}</div>
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
                                <div class="small muted mt-2">
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
                                <div class="small muted mt-2">
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
                                <div class="small muted mt-2">Active: {{ $activeOnboardings }} • Completed: {{ $completedOnboardings }}</div>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </div>

        <div class="row g-3 g-lg-4 mb-4">
            <div class="col-12 col-xl-6">
                <div class="section-card h-100">
                    <div class="card-header d-flex align-items-center justify-content-between px-3 px-lg-4 py-3">
                        <div class="fw-semibold"><i class="bi bi-graph-up me-2"></i> Registrations (Last 30 Days)</div>
                        <div class="small muted" id="rangeLabel">Loading…</div>
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
                        <div class="fw-semibold"><i class="bi bi-journal-check me-2"></i> Booking Status</div>
                        <div class="small muted">All-time</div>
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
                        <div class="fw-semibold"><i class="bi bi-check2-circle me-2"></i> Approval Activity</div>
                        <div class="small muted">Approved vs Rejected</div>
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
                        <div class="fw-semibold"><i class="bi bi-door-open me-2"></i> Room Status</div>
                        <div class="small muted">All-time</div>
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
                        <div class="fw-semibold">
                            <i class="bi bi-activity me-2"></i> Activity Snapshot
                        </div>
                        <div class="small muted">Last 7 days + today</div>
                    </div>
                    <div class="card-body px-3 px-lg-4 py-4">
                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <div class="p-3 rounded-4" style="background: rgba(22,101,52,.06); border: 1px solid rgba(22,101,52,.14);">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div>
                                            <div class="small muted">Growth (7d)</div>
                                            <div class="h2 fw-bold mb-0">{{ $growthPct }}%</div>
                                        </div>
                                        <div class="kpi-icon" style="background: rgba(245,158,11,.12); border-color: rgba(245,158,11,.22); color: rgba(180,83,9,1);"><i class="bi bi-graph-up"></i></div>
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
                            <div class="fw-semibold mb-2"><i class="bi bi-gear me-2"></i> System Status</div>
                            <div class="d-flex flex-wrap gap-2">
                                <span class="badge text-bg-info status-pill">Queue: {{ $systemStatus['queue'] }}</span>
                                <span class="badge text-bg-success status-pill">Cache: {{ $systemStatus['cache'] }}</span>
                                <span class="badge text-bg-secondary status-pill">Mail: {{ $systemStatus['mail'] }}</span>
                                <span class="badge text-bg-dark status-pill">Laravel: {{ $systemStatus['version'] }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-xl-5">
                <div class="section-card h-100">
                    <div class="card-header d-flex align-items-center justify-content-between px-3 px-lg-4 py-3">
                        <div class="fw-semibold"><i class="bi bi-flag me-2"></i> Reports</div>
                        <a class="small text-decoration-none" href="{{ route('admin.reports.index') }}">View all</a>
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
                <div class="fw-semibold"><i class="bi bi-person-plus me-2"></i> Recent Registrations</div>
                <a class="small text-decoration-none" href="{{ route('admin.users.index') }}">Manage users</a>
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
                                        <div class="fw-semibold">{{ $u->full_name }}</div>
                                        <div class="small muted">ID: {{ $u->id }}</div>
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
    <script>
        (function () {
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

            const buildLine = (ctx, labels, data) => new Chart(ctx, {
                type: 'line',
                data: {
                    labels,
                    datasets: [{
                        label: 'New users',
                        data,
                        borderColor: colors.brand,
                        backgroundColor: colors.brandSoft,
                        fill: true,
                        tension: 0.35,
                        pointRadius: 2,
                        pointHoverRadius: 4,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: { intersect: false, mode: 'index' },
                    },
                    scales: {
                        x: { grid: { display: false }, ticks: { maxTicksLimit: 10, color: colors.gray } },
                        y: { beginAtZero: true, ticks: { precision: 0, color: colors.gray } }
                    }
                }
            });

            const buildDoughnut = (ctx, labels, data, palette) => new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels,
                    datasets: [{
                        data,
                        backgroundColor: palette,
                        borderWidth: 1,
                        borderColor: 'rgba(255,255,255,1)'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '68%',
                    plugins: {
                        legend: { position: 'bottom', labels: { boxWidth: 12, color: colors.gray } },
                        tooltip: { callbacks: { label: (ctx) => `${ctx.label}: ${ctx.raw}` } }
                    }
                }
            });

            const buildBar = (ctx, labels, approved, rejected) => new Chart(ctx, {
                type: 'bar',
                data: {
                    labels,
                    datasets: [
                        { label: 'Approved', data: approved, backgroundColor: colors.tealSoft, borderColor: colors.teal, borderWidth: 1 },
                        { label: 'Rejected', data: rejected, backgroundColor: colors.dangerSoft, borderColor: colors.danger, borderWidth: 1 },
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom', labels: { boxWidth: 12, color: colors.gray } },
                        tooltip: { intersect: false, mode: 'index' }
                    },
                    scales: {
                        x: { stacked: false, grid: { display: false }, ticks: { maxTicksLimit: 10, color: colors.gray } },
                        y: { beginAtZero: true, ticks: { precision: 0, color: colors.gray } }
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
                        buildDoughnut(bookCtx,
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
                document.addEventListener('DOMContentLoaded', init);
            } else {
                init();
            }
        })();
    </script>
@endsection