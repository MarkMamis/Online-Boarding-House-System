@extends('layouts.admin')

@section('title', 'Admin Analytics & Insights - OBHS')

@section('content')
<div class="admin-page-shell">
    {{-- Page Header --}}
    <x-admin.page-header
        title="Admin Reports & Analytics"
        subtitle="Defendable operational insights on boarding trends, institutional demographics, property occupancy, and compliance."
        icon="bi-graph-up-arrow"
        :breadcrumbs="[
            ['label' => 'Insights', 'url' => route('admin.dashboard')],
            ['label' => 'Analytics']
        ]"
    />

    {{-- Filter Toolbar --}}
    <div class="admin-toolbar mb-4">
        <form method="GET" action="{{ route('admin.analytics.index') }}" id="analyticsFilterForm">
            <div class="row g-3 align-items-end">
                {{-- Period Preset --}}
                <div class="col-12 col-sm-6 col-md-3">
                    <label for="period_preset" class="form-label small fw-semibold text-secondary mb-1">Reporting Period</label>
                    <select name="period_preset" id="period_preset" class="form-select" onchange="toggleCustomDates(this.value)">
                        <option value="all" @selected(($periodInfo['periodPreset'] ?? '') === 'all')>All Time (Current Active)</option>
                        <option value="this_month" @selected(($periodInfo['periodPreset'] ?? '') === 'this_month')>This Month ({{ now()->format('M Y') }})</option>
                        <option value="last_month" @selected(($periodInfo['periodPreset'] ?? '') === 'last_month')>Last Month ({{ now()->subMonthNoOverflow()->format('M Y') }})</option>
                        <option value="custom" @selected(($periodInfo['periodPreset'] ?? '') === 'custom')>Custom Date Range</option>
                    </select>
                </div>

                {{-- Custom Date From --}}
                <div class="col-6 col-sm-3 col-md-2 custom-date-col {{ ($periodInfo['periodPreset'] ?? '') === 'custom' ? '' : 'd-none' }}" id="customDateFromCol">
                    <label for="date_from" class="form-label small fw-semibold text-secondary mb-1">From</label>
                    <input type="date" name="date_from" id="date_from" value="{{ $periodInfo['dateFrom'] ?? '' }}" class="form-control">
                </div>

                {{-- Custom Date To --}}
                <div class="col-6 col-sm-3 col-md-2 custom-date-col {{ ($periodInfo['periodPreset'] ?? '') === 'custom' ? '' : 'd-none' }}" id="customDateToCol">
                    <label for="date_to" class="form-label small fw-semibold text-secondary mb-1">To</label>
                    <input type="date" name="date_to" id="date_to" value="{{ $periodInfo['dateTo'] ?? '' }}" class="form-control">
                </div>

                {{-- Property Filter --}}
                <div class="col-12 col-sm-6 col-md-3">
                    <label for="property_id" class="form-label small fw-semibold text-secondary mb-1">Boarding House</label>
                    <select name="property_id" id="property_id" class="form-select">
                        <option value="">All Boarding Houses</option>
                        @foreach($filterOptions['boardingHouses'] as $bh)
                            <option value="{{ $bh->id }}" @selected((int)$propertyId === (int)$bh->id)>{{ $bh->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- College Filter --}}
                <div class="col-12 col-sm-6 col-md-2">
                    <label for="college" class="form-label small fw-semibold text-secondary mb-1">College</label>
                    <select name="college" id="college" class="form-select">
                        <option value="">All Colleges</option>
                        @foreach($filterOptions['colleges'] as $col)
                            <option value="{{ $col['code'] }}" @selected($college === $col['code'])>{{ $col['code'] }} - {{ \Illuminate\Support\Str::limit($col['name'], 20) }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Action Buttons --}}
                <div class="col-12 col-sm-6 col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-brand flex-fill">
                        <i class="bi bi-funnel me-1"></i>Filter
                    </button>
                    <a href="{{ route('admin.analytics.index') }}" class="btn btn-action-icon" title="Reset Filters">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                </div>
            </div>

            @if(!empty($periodInfo['periodLabel']))
                <div class="mt-2 text-muted small d-flex align-items-center gap-1">
                    <i class="bi bi-calendar3 text-primary"></i>
                    Active Period Filter: <strong class="text-dark">{{ $periodInfo['periodLabel'] }}</strong>
                </div>
            @endif
        </form>
    </div>

    {{-- Summary KPI Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <x-admin.metric-card
                label="Active Boarders"
                :value="number_format($kpis['active_boarders'])"
                icon="bi-person-check"
                variant="brand"
                subtext="Unique accommodated students"
            />
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <x-admin.metric-card
                label="Registered Students"
                :value="number_format($kpis['registered_students'])"
                icon="bi-mortarboard"
                variant="neutral"
                subtext="Total student accounts in OBHS"
            />
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <x-admin.metric-card
                label="Room Occupancy"
                :value="$kpis['room_occupancy_rate'] . '%'"
                icon="bi-door-open"
                variant="neutral"
                :subtext="$kpis['occupied_rooms'] . ' of ' . $kpis['total_rooms'] . ' rooms occupied'"
            />
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <x-admin.metric-card
                label="Bed Occupancy"
                :value="$kpis['bed_occupancy_rate'] . '%'"
                icon="bi-grid"
                variant="neutral"
                :subtext="$kpis['occupied_beds'] . ' beds of ' . $kpis['total_capacity'] . ' capacity'"
            />
        </div>
    </div>

    {{-- Visualizations Row 1: Boarding Trend & Booking Status --}}
    <div class="row g-3 mb-4">
        {{-- Trend Line Chart --}}
        <div class="col-12 col-xl-8">
            <div class="admin-table-card h-100">
                <div class="admin-card-header d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-graph-up me-2 text-primary"></i>Monthly Boarding &amp; Booking Activity ({{ $trend['year'] }})</h6>
                        <span class="small text-secondary">Comparison of ongoing boarders vs new booking requests per month</span>
                    </div>
                </div>
                <div class="p-3 p-md-4">
                    <div style="height: 290px; position: relative;">
                        <canvas id="chartBoardingTrend"></canvas>
                    </div>
                </div>
            </div>
        </div>

        {{-- Booking Request Lifecycle Doughnut --}}
        <div class="col-12 col-xl-4">
            <div class="admin-table-card h-100">
                <div class="admin-card-header">
                    <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-pie-chart me-2 text-primary"></i>Booking Request Status</h6>
                    <span class="small text-secondary">Distribution of {{ $bookingLifecycle['total'] }} total requests</span>
                </div>
                <div class="p-3 p-md-4 d-flex flex-column justify-content-center">
                    @if($bookingLifecycle['total'] > 0)
                        <div style="height: 220px; position: relative;">
                            <canvas id="chartBookingStatus"></canvas>
                        </div>
                        <div class="d-flex justify-content-around mt-3 text-center small">
                            <div><span class="badge bg-success-subtle text-success border">Approved</span><div><strong>{{ $bookingLifecycle['approved'] }}</strong></div></div>
                            <div><span class="badge bg-warning-subtle text-warning-emphasis border">Pending</span><div><strong>{{ $bookingLifecycle['pending'] }}</strong></div></div>
                            <div><span class="badge bg-danger-subtle text-danger border">Rejected</span><div><strong>{{ $bookingLifecycle['rejected'] }}</strong></div></div>
                            <div><span class="badge bg-secondary-subtle text-secondary border">Cancelled</span><div><strong>{{ $bookingLifecycle['cancelled'] }}</strong></div></div>
                        </div>
                    @else
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary"></i>
                            No booking records for this period.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Visualizations Row 2: Demographics (Colleges, Programs, Gender) --}}
    <div class="row g-3 mb-4">
        {{-- College Distribution --}}
        <div class="col-12 col-lg-5">
            <div class="admin-table-card h-100">
                <div class="admin-card-header">
                    <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-buildings me-2 text-primary"></i>Boarders by College</h6>
                    <span class="small text-secondary">De-duplicated student counts across academic units</span>
                </div>
                <div class="p-3 p-md-4">
                    @if($demographics['colleges']->isNotEmpty())
                        <div style="height: 260px; position: relative;">
                            <canvas id="chartColleges"></canvas>
                        </div>
                    @else
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-people fs-1 d-block mb-2 text-secondary"></i>
                            No boarders recorded under current filters.
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Top Programs --}}
        <div class="col-12 col-lg-4">
            <div class="admin-table-card h-100">
                <div class="admin-card-header">
                    <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-book me-2 text-primary"></i>Top Programs with Boarders</h6>
                    <span class="small text-secondary">Programs with highest accommodation usage</span>
                </div>
                <div class="p-3 p-md-4">
                    @if($demographics['programs']->isNotEmpty())
                        <div style="height: 260px; position: relative;">
                            <canvas id="chartPrograms"></canvas>
                        </div>
                    @else
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-journal-text fs-1 d-block mb-2 text-secondary"></i>
                            No program records found.
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Gender Distribution --}}
        <div class="col-12 col-lg-3">
            <div class="admin-table-card h-100">
                <div class="admin-card-header">
                    <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-gender-ambiguous me-2 text-primary"></i>Gender Breakdown</h6>
                    <span class="small text-secondary">Active Boarders vs Registered</span>
                </div>
                <div class="p-3 p-md-4 d-flex flex-column justify-content-center">
                    @php
                        $totalGenderBoarders = $demographics['boarder_gender']['male'] + $demographics['boarder_gender']['female'] + $demographics['boarder_gender']['unspecified'];
                    @endphp
                    @if($totalGenderBoarders > 0)
                        <div style="height: 190px; position: relative;">
                            <canvas id="chartGender"></canvas>
                        </div>
                        <div class="d-flex justify-content-around mt-3 text-center small">
                            <div><span class="text-primary fw-bold">Male</span><div>{{ $demographics['boarder_gender']['male'] }}</div></div>
                            <div><span class="text-danger fw-bold">Female</span><div>{{ $demographics['boarder_gender']['female'] }}</div></div>
                            <div><span class="text-secondary fw-bold">Other/N/A</span><div>{{ $demographics['boarder_gender']['unspecified'] }}</div></div>
                        </div>
                    @else
                        <div class="text-center py-4 text-muted">
                            <i class="bi bi-person fs-1 d-block mb-2 text-secondary"></i>
                            No active boarders for gender breakdown.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Boarding House Occupancy & Inventory Breakdown --}}
    <div class="admin-table-card mb-4">
        <div class="admin-card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div>
                <h6 class="fw-bold mb-0 text-dark">
                    <i class="bi bi-buildings me-2 text-primary"></i>Boarding House Capacity &amp; Occupancy Overview
                </h6>
                <span class="small text-secondary">Operational capacity, room inventory, and active occupancy rates</span>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-light text-dark border fw-normal">{{ $occupancy->count() }} Properties</span>
                <a href="{{ route('admin.institutional_reports.index', ['report_preset' => 'occupancy_availability']) }}" class="btn btn-sm btn-outline-brand">
                    <i class="bi bi-journal-text me-1"></i>Generate Full Report
                </a>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table admin-table mb-0">
                <thead>
                    <tr>
                        <th class="ps-4">Boarding House</th>
                        <th>Landlord</th>
                        <th>Total Rooms</th>
                        <th>Occupied</th>
                        <th>Available</th>
                        <th>Bed Capacity</th>
                        <th>Occupied Beds</th>
                        <th>Room Occupancy</th>
                        <th>Bed Occupancy</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($occupancy as $p)
                        <tr>
                            <td class="ps-4 fw-semibold text-dark">
                                <a href="{{ route('admin.properties.inspect', $p->id) }}" class="text-decoration-none text-dark hover-primary">
                                    {{ $p->name }}
                                </a>
                            </td>
                            <td>{{ $p->landlord_name }}</td>
                            <td>{{ $p->total_rooms }}</td>
                            <td><span class="badge bg-success-subtle text-success border">{{ $p->occupied_rooms }}</span></td>
                            <td><span class="badge bg-info-subtle text-info-emphasis border">{{ $p->available_rooms }}</span></td>
                            <td>{{ $p->total_capacity }}</td>
                            <td>{{ $p->occupied_beds }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="progress flex-grow-1" style="height: 6px;">
                                        <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $p->room_occupancy_rate }}%"></div>
                                    </div>
                                    <span class="small fw-bold">{{ $p->room_occupancy_rate }}%</span>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="progress flex-grow-1" style="height: 6px;">
                                        <div class="progress-bar bg-info" role="progressbar" style="width: {{ $p->bed_occupancy_rate }}%"></div>
                                    </div>
                                    <span class="small fw-bold">{{ $p->bed_occupancy_rate }}%</span>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9">
                                <x-admin.empty-state
                                    title="No properties registered"
                                    description="No approved boarding house records available for occupancy analysis."
                                    icon="bi-buildings"
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    {{-- Landlord Document Compliance Summary --}}
    <div class="admin-table-card mb-4">
        <div class="admin-card-header d-flex align-items-center justify-content-between">
            <div>
                <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-shield-check me-2 text-primary"></i>Landlord Document Compliance Overview</h6>
                <span class="small text-secondary">Permit &amp; safety certification verification status</span>
            </div>
            <a href="{{ route('admin.documents.monitoring') }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-activity me-1"></i>Compliance Monitor
            </a>
        </div>
        <div class="p-3 p-md-4">
            <div class="row g-3">
                {{-- Business Permit Status Card --}}
                <div class="col-12 col-md-6">
                    <div class="p-3 rounded-3 border bg-light">
                        <div class="fw-semibold text-dark mb-2 d-flex align-items-center gap-2 small">
                            <i class="bi bi-file-earmark-text text-primary"></i>Business Permits
                        </div>
                        <div class="d-flex flex-wrap gap-2 text-center small">
                            <div class="flex-fill p-2 bg-white rounded-2 border">
                                <span class="text-success fw-bold d-block">{{ $compliance['business_permit']['valid'] }}</span>
                                <span class="text-muted small">Valid</span>
                            </div>
                            <div class="flex-fill p-2 bg-white rounded-2 border">
                                <span class="text-warning fw-bold d-block">{{ $compliance['business_permit']['expiring_soon'] }}</span>
                                <span class="text-muted small">Expiring Soon</span>
                            </div>
                            <div class="flex-fill p-2 bg-white rounded-2 border">
                                <span class="text-danger fw-bold d-block">{{ $compliance['business_permit']['expired'] }}</span>
                                <span class="text-muted small">Expired</span>
                            </div>
                            <div class="flex-fill p-2 bg-white rounded-2 border">
                                <span class="text-primary fw-bold d-block">{{ $compliance['business_permit']['pending'] }}</span>
                                <span class="text-muted small">Pending Review</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Safety Certificate Status Card --}}
                <div class="col-12 col-md-6">
                    <div class="p-3 rounded-3 border bg-light">
                        <div class="fw-semibold text-dark mb-2 d-flex align-items-center gap-2 small">
                            <i class="bi bi-shield-lock text-primary"></i>Safety Certificates
                        </div>
                        <div class="d-flex flex-wrap gap-2 text-center small">
                            <div class="flex-fill p-2 bg-white rounded-2 border">
                                <span class="text-success fw-bold d-block">{{ $compliance['safety_certificate']['valid'] }}</span>
                                <span class="text-muted small">Valid</span>
                            </div>
                            <div class="flex-fill p-2 bg-white rounded-2 border">
                                <span class="text-warning fw-bold d-block">{{ $compliance['safety_certificate']['expiring_soon'] }}</span>
                                <span class="text-muted small">Expiring Soon</span>
                            </div>
                            <div class="flex-fill p-2 bg-white rounded-2 border">
                                <span class="text-danger fw-bold d-block">{{ $compliance['safety_certificate']['expired'] }}</span>
                                <span class="text-muted small">Expired</span>
                            </div>
                            <div class="flex-fill p-2 bg-white rounded-2 border">
                                <span class="text-primary fw-bold d-block">{{ $compliance['safety_certificate']['pending'] }}</span>
                                <span class="text-muted small">Pending Review</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Load Chart.js --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

<script>
    function toggleCustomDates(preset) {
        const fromCol = document.getElementById('customDateFromCol');
        const toCol = document.getElementById('customDateToCol');
        if (preset === 'custom') {
            fromCol.classList.remove('d-none');
            toCol.classList.remove('d-none');
        } else {
            fromCol.classList.add('d-none');
            toCol.classList.add('d-none');
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        if (typeof Chart === 'undefined') return;

        // 1. Boarding Trend Line Chart
        const trendCtx = document.getElementById('chartBoardingTrend');
        if (trendCtx) {
            new Chart(trendCtx, {
                type: 'line',
                data: {
                    labels: @json($trend['labels']),
                    datasets: [
                        {
                            label: 'Active Boarders (Stay Overlap)',
                            data: @json($trend['active_boarders']),
                            borderColor: '#166534',
                            backgroundColor: 'rgba(22, 101, 52, 0.08)',
                            borderWidth: 2.5,
                            tension: 0.35,
                            fill: true,
                            pointRadius: 4,
                            pointBackgroundColor: '#166534'
                        },
                        {
                            label: 'New Booking Requests',
                            data: @json($trend['new_bookings']),
                            borderColor: '#0284c7',
                            backgroundColor: 'rgba(2, 132, 199, 0.04)',
                            borderWidth: 2,
                            borderDash: [5, 5],
                            tension: 0.35,
                            pointRadius: 3,
                            pointBackgroundColor: '#0284c7'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'top', labels: { boxWidth: 14, font: { size: 12 } } },
                        tooltip: { mode: 'index', intersect: false }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { precision: 0, font: { size: 11 } },
                            grid: { color: 'rgba(0,0,0,0.05)' }
                        },
                        x: {
                            grid: { display: false },
                            ticks: { font: { size: 11 } }
                        }
                    }
                }
            });
        }

        // 2. Booking Status Doughnut Chart
        const bookingCtx = document.getElementById('chartBookingStatus');
        if (bookingCtx) {
            new Chart(bookingCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Approved', 'Pending', 'Rejected', 'Cancelled'],
                    datasets: [{
                        data: [
                            {{ $bookingLifecycle['approved'] }},
                            {{ $bookingLifecycle['pending'] }},
                            {{ $bookingLifecycle['rejected'] }},
                            {{ $bookingLifecycle['cancelled'] }}
                        ],
                        backgroundColor: ['#16a34a', '#eab308', '#dc2626', '#94a3b8'],
                        borderWidth: 2,
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const total = {{ $bookingLifecycle['total'] }};
                                    const val = context.parsed;
                                    const pct = total > 0 ? ((val / total) * 100).toFixed(1) : 0;
                                    return ` ${context.label}: ${val} (${pct}%)`;
                                }
                            }
                        }
                    },
                    cutout: '70%'
                }
            });
        }

        // 3. College Distribution Horizontal Bar Chart
        const collegeCtx = document.getElementById('chartColleges');
        if (collegeCtx) {
            @php
                $collegeLabels = $demographics['colleges']->pluck('college_code')->all();
                $collegeData = $demographics['colleges']->pluck('total_students')->all();
            @endphp
            new Chart(collegeCtx, {
                type: 'bar',
                data: {
                    labels: @json($collegeLabels),
                    datasets: [{
                        label: 'Boarders',
                        data: @json($collegeData),
                        backgroundColor: '#16a34a',
                        borderRadius: 6
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        x: {
                            beginAtZero: true,
                            ticks: { precision: 0, font: { size: 10 } },
                            grid: { color: 'rgba(0,0,0,0.05)' }
                        },
                        y: {
                            ticks: { font: { size: 11, weight: 'bold' } },
                            grid: { display: false }
                        }
                    }
                }
            });
        }

        // 4. Top Programs Bar Chart
        const programCtx = document.getElementById('chartPrograms');
        if (programCtx) {
            @php
                $programLabels = $demographics['programs']->pluck('program_name')->all();
                $programData = $demographics['programs']->pluck('total_students')->all();
            @endphp
            new Chart(programCtx, {
                type: 'bar',
                data: {
                    labels: @json($programLabels),
                    datasets: [{
                        label: 'Boarders',
                        data: @json($programData),
                        backgroundColor: '#0284c7',
                        borderRadius: 6
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        x: {
                            beginAtZero: true,
                            ticks: { precision: 0, font: { size: 10 } },
                            grid: { color: 'rgba(0,0,0,0.05)' }
                        },
                        y: {
                            ticks: {
                                font: { size: 10 },
                                callback: function(value) {
                                    const label = this.getLabelForValue(value);
                                    return label.length > 22 ? label.substr(0, 20) + '...' : label;
                                }
                            },
                            grid: { display: false }
                        }
                    }
                }
            });
        }

        // 5. Gender Distribution Doughnut Chart
        const genderCtx = document.getElementById('chartGender');
        if (genderCtx) {
            new Chart(genderCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Male', 'Female', 'Unspecified'],
                    datasets: [{
                        data: [
                            {{ $demographics['boarder_gender']['male'] }},
                            {{ $demographics['boarder_gender']['female'] }},
                            {{ $demographics['boarder_gender']['unspecified'] }}
                        ],
                        backgroundColor: ['#2563eb', '#ec4899', '#94a3b8'],
                        borderWidth: 2,
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    cutout: '68%'
                }
            });
        }
    });
</script>
@endsection
