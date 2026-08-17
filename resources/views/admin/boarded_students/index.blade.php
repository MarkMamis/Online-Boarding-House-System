@extends('layouts.admin')

@section('title', 'Boarding Monitoring · Students & Academic Reporting')

@section('content')
    <style>
        .boarded-shell {
            background: linear-gradient(180deg, #ffffff 0%, #fbfdfc 100%);
            border: 1px solid rgba(2, 8, 20, .08);
            border-radius: 1.25rem;
            box-shadow: 0 10px 26px rgba(2, 8, 20, .06);
            padding: 1.25rem;
        }

        .boarded-muted { color: rgba(2, 8, 20, .58); }

        .boarded-metric {
            border: 1px solid rgba(2, 8, 20, .08);
            border-radius: 1rem;
            background: #fff;
            box-shadow: 0 6px 16px rgba(2, 8, 20, .04);
            padding: .95rem 1rem;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .boarded-metric-label {
            font-size: .72rem;
            text-transform: uppercase;
            letter-spacing: .04em;
            color: rgba(2, 8, 20, .55);
            font-weight: 700;
        }

        .boarded-metric-value {
            font-size: 1.45rem;
            font-weight: 800;
            color: #166534;
            line-height: 1.2;
        }

        .boarded-card {
            border: 1px solid rgba(2, 8, 20, .08);
            border-radius: 1rem;
            background: #fff;
            box-shadow: 0 8px 20px rgba(2, 8, 20, .05);
            overflow: hidden;
        }

        .boarded-card-header {
            padding: .85rem 1.15rem;
            border-bottom: 1px solid rgba(2, 8, 20, .08);
            background: #fff;
        }

        .tenant-avatar {
            width: 38px;
            height: 38px;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(22, 101, 52, .12);
            color: #166534;
            border: 1px solid rgba(22, 101, 52, .22);
            flex-shrink: 0;
            font-weight: 700;
            text-transform: uppercase;
            font-size: .85rem;
        }

        .monitoring-status {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            padding: .28rem .65rem;
            font-size: .74rem;
            font-weight: 700;
            white-space: nowrap;
        }

        .status-active { background: #dcfce7; color: #166534; border: 1px solid rgba(22, 101, 52, .20); }
        .status-checked-out { background: #f1f5f9; color: #475569; border: 1px solid rgba(71, 85, 105, .20); }
        .status-pending { background: #fef3c7; color: #92400e; border: 1px solid rgba(146, 64, 14, .20); }
        .status-cancelled { background: #fee2e2; color: #991b1b; border: 1px solid rgba(153, 27, 27, .20); }

        .table thead th {
            font-size: .72rem;
            text-transform: uppercase;
            letter-spacing: .05em;
            color: rgba(2, 8, 20, .65);
            background: rgba(248, 250, 252, .98);
            border-bottom: 1px solid rgba(2, 8, 20, .08);
            white-space: nowrap;
            padding: .75rem .85rem;
        }

        .table tbody td {
            vertical-align: middle;
            padding: .75rem .85rem;
            font-size: .88rem;
        }

        .table-empty {
            padding: 3.5rem 1rem !important;
            text-align: center;
            color: rgba(2, 8, 20, .58);
        }

        .period-banner {
            background: linear-gradient(90deg, rgba(22, 101, 52, .08) 0%, rgba(22, 101, 52, .03) 100%);
            border: 1px solid rgba(22, 101, 52, .20);
            border-radius: .75rem;
            padding: .75rem 1.15rem;
        }
    </style>

    <div class="boarded-shell container-fluid py-2">
        {{-- Header Section --}}
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3">
            <div>
                <div class="text-uppercase small boarded-muted fw-bold">Boarding House Management</div>
                <h1 class="h3 mb-1 fw-bold text-dark">Boarding Monitoring</h1>
                <p class="boarded-muted mb-0">Student occupancy tracking, check-in history, and printable institutional reports.</p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('admin.boarding_monitoring.students.print', request()->query()) }}" target="_blank" class="btn btn-sm btn-success rounded-pill px-3 shadow-sm">
                    <i class="bi bi-printer me-1"></i>Print Report
                </a>
                <a href="{{ route('admin.bookings.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill">
                    <i class="bi bi-journal-check me-1"></i>All Bookings Table
                </a>
            </div>
        </div>

        {{-- Active Report Context Banner --}}
        <div class="period-banner d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
            <div>
                <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                    <span class="badge bg-success text-white px-2 py-1 fs-6">
                        <i class="bi bi-building me-1"></i>{{ $selectedProperty ? $selectedProperty->name : 'All Boarding Houses' }}
                    </span>
                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                        <i class="bi bi-funnel me-1"></i>Basis: {{ $dateBasis === 'check_in' ? 'Started Boarding (Check-in)' : 'Stayed During Period (Occupancy)' }}
                    </span>
                    @if($periodLabel)
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1">
                            <i class="bi bi-calendar3 me-1"></i>Period: {{ $periodLabel }}
                        </span>
                    @else
                        <span class="badge bg-light text-dark border px-2 py-1">
                            <i class="bi bi-clock-history me-1"></i>All Recorded Stays (Current)
                        </span>
                    @endif
                </div>
                <div class="small boarded-muted">
                    @if($dateBasis === 'check_in')
                        Filtering strictly by student boarding start date (check-in) within the selected period.
                    @else
                        Filtering by physical stay overlap (active or occupying during any part of the selected period).
                    @endif
                </div>
            </div>
            @if($search || $boardingHouse || $college || $program || $month || $year || $dateFrom || $dateTo || $statusFilter !== 'all' || $dateBasis !== 'stay')
                <a href="{{ route('admin.boarding_monitoring.students') }}" class="btn btn-sm btn-outline-success rounded-pill">
                    <i class="bi bi-x-circle me-1"></i>Reset All Filters
                </a>
            @endif
        </div>

        {{-- Summary Cards Section --}}
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-4 col-xl-2">
                <div class="boarded-metric">
                    <div class="boarded-metric-label"><i class="bi bi-people-fill me-1 text-success"></i>Unique Students</div>
                    <div class="boarded-metric-value">{{ number_format($uniqueStudents) }}</div>
                    <div class="small boarded-muted mt-1">{{ number_format($totalRecords) }} stay records</div>
                </div>
            </div>
            <div class="col-6 col-md-4 col-xl-2">
                <div class="boarded-metric">
                    <div class="boarded-metric-label"><i class="bi bi-check-circle-fill me-1 text-success"></i>Active / Present</div>
                    <div class="boarded-metric-value text-success">{{ number_format($activeBoardings) }}</div>
                    <div class="small text-success mt-1">{{ number_format($activeTenants) }} distinct students</div>
                </div>
            </div>
            <div class="col-6 col-md-4 col-xl-2">
                <div class="boarded-metric">
                    <div class="boarded-metric-label"><i class="bi bi-door-closed-fill me-1 text-secondary"></i>Checked Out</div>
                    <div class="boarded-metric-value text-secondary">{{ number_format($checkedOutBoardings) }}</div>
                    <div class="small boarded-muted mt-1">{{ number_format($checkedOutTenants) }} distinct students</div>
                </div>
            </div>
            <div class="col-6 col-md-4 col-xl-2">
                <div class="boarded-metric">
                    <div class="boarded-metric-label"><i class="bi bi-clock-history me-1 text-warning"></i>Pending</div>
                    <div class="boarded-metric-value text-warning">{{ number_format($pendingBoardings) }}</div>
                    <div class="small boarded-muted mt-1">Awaiting confirmation</div>
                </div>
            </div>
            <div class="col-6 col-md-4 col-xl-2">
                <div class="boarded-metric">
                    <div class="boarded-metric-label"><i class="bi bi-x-octagon-fill me-1 text-danger"></i>Cancelled</div>
                    <div class="boarded-metric-value text-danger">{{ number_format($cancelledBoardings) }}</div>
                    <div class="small boarded-muted mt-1">Pure cancellations</div>
                </div>
            </div>
            <div class="col-6 col-md-4 col-xl-2">
                <div class="boarded-metric">
                    <div class="boarded-metric-label"><i class="bi bi-building-check me-1 text-primary"></i>Houses &amp; Rooms</div>
                    <div class="boarded-metric-value text-primary">{{ number_format($activeProperties) }} <span class="fs-6 fw-normal text-muted">/ {{ number_format($activeRooms) }} rooms</span></div>
                    <div class="small boarded-muted mt-1">Represented in filter</div>
                </div>
            </div>
        </div>

        {{-- Filter Bar Card --}}
        <div class="boarded-card mb-4">
            <div class="boarded-card-header d-flex align-items-center justify-content-between">
                <div class="fw-bold text-dark"><i class="bi bi-funnel-fill text-success me-1"></i> Multi-Dimensional Monitoring &amp; Reporting Filters</div>
                <div class="d-flex align-items-center gap-2">
                    <a href="{{ route('admin.boarding_monitoring.students.print', request()->query()) }}" target="_blank" class="btn btn-sm btn-outline-success">
                        <i class="bi bi-printer me-1"></i>Print This Filter
                    </a>
                </div>
            </div>
            <div class="p-3">
                <form id="monitoringFilterForm" class="row g-3 align-items-end" method="GET" action="{{ route('admin.boarding_monitoring.students') }}">
                    <div class="col-12 col-md-6 col-xl-3">
                        <label class="form-label small text-uppercase text-muted fw-bold mb-1">Boarding House</label>
                        <select name="boarding_house" class="form-select form-select-sm">
                            <option value="">All Boarding Houses</option>
                            @foreach($boardingHouses as $bh)
                                <option value="{{ $bh->id }}" @selected((string) $boardingHouse === (string) $bh->id)>{{ $bh->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12 col-md-6 col-xl-3">
                        <label class="form-label small text-uppercase text-muted fw-bold mb-1">Report Basis</label>
                        <select name="date_basis" class="form-select form-select-sm">
                            <option value="stay" @selected($dateBasis === 'stay')>Stayed During Period (Occupancy)</option>
                            <option value="check_in" @selected($dateBasis === 'check_in')>Started Boarding During Period (Check-in Date)</option>
                        </select>
                    </div>

                    <div class="col-6 col-md-3 col-xl-1">
                        <label class="form-label small text-uppercase text-muted fw-bold mb-1">Month</label>
                        <select name="month" class="form-select form-select-sm">
                            <option value="">Any</option>
                            @foreach(range(1, 12) as $m)
                                <option value="{{ $m }}" @selected((int) $month === $m)>{{ date('M', mktime(0, 0, 0, $m, 1)) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-6 col-md-3 col-xl-1">
                        <label class="form-label small text-uppercase text-muted fw-bold mb-1">Year</label>
                        <select name="year" class="form-select form-select-sm">
                            <option value="">Any</option>
                            @foreach($years as $y)
                                <option value="{{ $y }}" @selected((int) $year === (int) $y)>{{ $y }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-6 col-md-3 col-xl-2">
                        <label class="form-label small text-uppercase text-muted fw-bold mb-1">Date From</label>
                        <input type="date" class="form-control form-control-sm" name="date_from" value="{{ $dateFrom }}">
                    </div>

                    <div class="col-6 col-md-3 col-xl-2">
                        <label class="form-label small text-uppercase text-muted fw-bold mb-1">Date To</label>
                        <input type="date" class="form-control form-control-sm" name="date_to" value="{{ $dateTo }}">
                    </div>

                    <div class="col-12 col-md-4 col-xl-2">
                        <label class="form-label small text-uppercase text-muted fw-bold mb-1">College</label>
                        <select name="college" id="collegeSelect" class="form-select form-select-sm">
                            <option value="">All Colleges</option>
                            @foreach($colleges as $c)
                                <option value="{{ $c['code'] }}" @selected($college === $c['code'])>{{ $c['code'] }} — {{ $c['name'] }}</option>
                            @endforeach
                            <option value="Not specified" @selected($college === 'Not specified')>Not Specified</option>
                        </select>
                    </div>

                    <div class="col-12 col-md-4 col-xl-3">
                        <label class="form-label small text-uppercase text-muted fw-bold mb-1">Program</label>
                        <select name="program" id="programSelect" class="form-select form-select-sm">
                            <option value="">All Programs</option>
                            @foreach($programs as $prog)
                                <option value="{{ $prog }}" @selected($program === $prog)>{{ $prog }}</option>
                            @endforeach
                            <option value="Not specified" @selected($program === 'Not specified')>Not Specified</option>
                        </select>
                    </div>

                    <div class="col-6 col-md-4 col-xl-2">
                        <label class="form-label small text-uppercase text-muted fw-bold mb-1">Status</label>
                        <select name="status" class="form-select form-select-sm">
                            <option value="all" @selected($statusFilter === 'all')>All Statuses</option>
                            <option value="active" @selected($statusFilter === 'active')>Active / Present</option>
                            <option value="checked_out" @selected($statusFilter === 'checked_out')>Checked Out</option>
                            <option value="pending" @selected($statusFilter === 'pending')>Pending</option>
                            <option value="cancelled" @selected($statusFilter === 'cancelled')>Cancelled</option>
                        </select>
                    </div>

                    <div class="col-12 col-md-8 col-xl-3">
                        <label class="form-label small text-uppercase text-muted fw-bold mb-1">Search</label>
                        <input type="text" class="form-control form-control-sm" name="search" value="{{ $search }}" placeholder="Student name, student ID, room...">
                    </div>

                    <div class="col-12 col-md-4 col-xl-2 d-flex gap-2">
                        <button class="btn btn-sm btn-success flex-fill" type="submit"><i class="bi bi-funnel me-1"></i>Apply</button>
                        <a href="{{ route('admin.boarding_monitoring.students') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                    </div>
                </form>
            </div>
        </div>

        {{-- Main Content Tabs: Student Records & Academic Distribution --}}
        <ul class="nav nav-tabs mb-3" id="monitoringTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active fw-bold" id="students-tab" data-bs-toggle="tab" data-bs-target="#students-panel" type="button" role="tab">
                    <i class="bi bi-people me-1"></i> Boarded Students ({{ number_format($totalRecords) }})
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-bold" id="academic-tab" data-bs-toggle="tab" data-bs-target="#academic-panel" type="button" role="tab">
                    <i class="bi bi-mortarboard me-1"></i> College &amp; Program Distribution
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-bold" id="properties-tab" data-bs-toggle="tab" data-bs-target="#properties-panel" type="button" role="tab">
                    <i class="bi bi-buildings me-1"></i> Boarding House Distribution
                </button>
            </li>
        </ul>

        <div class="tab-content" id="monitoringTabsContent">
            {{-- Panel 1: Student Monitoring Table --}}
            <div class="tab-pane fade show active" id="students-panel" role="tabpanel">
                <div class="boarded-card">
                    <div class="boarded-card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <div class="fw-bold text-dark"><i class="bi bi-table me-1 text-success"></i> Student Records ({{ $selectedProperty ? $selectedProperty->name : 'All Houses' }})</div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="small boarded-muted">Showing {{ $boardedStudents->firstItem() ?? 0 }}–{{ $boardedStudents->lastItem() ?? 0 }} of {{ number_format($boardedStudents->total()) }} records</span>
                            <a href="{{ route('admin.boarding_monitoring.students.print', request()->query()) }}" target="_blank" class="btn btn-sm btn-outline-success rounded-pill px-2 py-0">
                                <i class="bi bi-printer me-1"></i>Print
                            </a>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="ps-3">Student</th>
                                    <th>Student ID</th>
                                    <th>College</th>
                                    <th>Program</th>
                                    <th>Boarding House</th>
                                    <th>Room</th>
                                    <th>Check-in</th>
                                    <th>Check-out</th>
                                    <th class="pe-3 text-end">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($boardedStudents as $boarding)
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
                                                <span class="tenant-avatar">{{ strtoupper(substr($boarding->student?->full_name ?? $boarding->student?->name ?? 'U', 0, 1)) }}</span>
                                                <div class="min-w-0">
                                                    <div class="fw-bold text-dark text-truncate">{{ $boarding->student?->full_name ?? $boarding->student?->name ?? 'Unknown Student' }}</div>
                                                    <div class="small text-muted text-truncate">{{ $boarding->student?->email ?? 'No email' }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            @if(!empty($boarding->student?->student_id))
                                                <span class="font-monospace fw-semibold text-secondary small">{{ $boarding->student->student_id }}</span>
                                            @else
                                                <span class="text-muted small">N/A</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark border">{{ $boarding->student?->college ?: 'Not specified' }}</span>
                                        </td>
                                        <td>
                                            <div class="text-truncate" style="max-width: 220px;" title="{{ $boarding->student?->program ?? 'Not specified' }}">
                                                {{ $boarding->student?->program ?: 'Not specified' }}
                                            </div>
                                            @if(!empty($boarding->student?->major))
                                                <div class="small text-muted text-truncate" style="max-width: 220px;">Major: {{ $boarding->student->major }}</div>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="fw-semibold text-dark">{{ $boarding->room?->property?->name ?? 'N/A' }}</div>
                                            <div class="small text-muted text-truncate" style="max-width: 180px;">{{ $boarding->room?->property?->address ?? '' }}</div>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark border">Room {{ $boarding->room?->room_number ?? $boarding->room_id }}</span>
                                        </td>
                                        <td>{{ $boarding->check_in ? $boarding->check_in->format('M d, Y') : 'N/A' }}</td>
                                        <td>{{ $boarding->check_out ? $boarding->check_out->format('M d, Y') : 'Open-ended' }}</td>
                                        <td class="pe-3 text-end">
                                            <span class="monitoring-status status-{{ $monitoringStatus }}">{{ $monitoringStatusLabel }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="table-empty">
                                            <i class="bi bi-inbox fs-2 text-muted d-block mb-2"></i>
                                            No student records match the selected monitoring filters.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($boardedStudents->hasPages())
                        <div class="p-3 border-top d-flex justify-content-between align-items-center">
                            <span class="small text-muted">Showing {{ $boardedStudents->firstItem() }} to {{ $boardedStudents->lastItem() }} of {{ $boardedStudents->total() }} results</span>
                            <div>{{ $boardedStudents->links() }}</div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Panel 2: Academic Distribution (College & Program Reporting) --}}
            <div class="tab-pane fade" id="academic-panel" role="tabpanel">
                <div class="row g-3">
                    {{-- College Summary --}}
                    <div class="col-12 col-xl-5">
                        <div class="boarded-card h-100">
                            <div class="boarded-card-header d-flex justify-content-between align-items-center">
                                <div class="fw-bold text-dark"><i class="bi bi-mortarboard-fill text-success me-1"></i> Students by College</div>
                                <span class="small text-muted">De-duplicated unique headcounts</span>
                            </div>
                            <div class="table-responsive">
                                <table class="table align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>College</th>
                                            <th class="text-center">Unique Students</th>
                                            <th class="text-center">Total Stays</th>
                                            <th class="text-end">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($collegeDistribution as $cd)
                                            <tr>
                                                <td>
                                                    <div class="fw-bold text-dark">{{ $cd->college_code }}</div>
                                                    <div class="small text-muted">{{ $cd->college_name }}</div>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge bg-success-subtle text-success border border-success-subtle fs-6 px-2 py-1">
                                                        {{ number_format($cd->total_students) }}
                                                    </span>
                                                </td>
                                                <td class="text-center text-muted small">{{ number_format($cd->total_records) }}</td>
                                                <td class="text-end">
                                                    <a href="{{ route('admin.boarding_monitoring.students', array_merge(request()->query(), ['college' => $cd->college_code, 'program' => ''])) }}" class="btn btn-sm btn-outline-success rounded-pill px-2 py-0" title="Drill into {{ $cd->college_code }} students">
                                                        <i class="bi bi-arrow-right-short"></i> Filter
                                                    </a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="table-empty">No academic distribution data available.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    {{-- Program Summary --}}
                    <div class="col-12 col-xl-7">
                        <div class="boarded-card h-100">
                            <div class="boarded-card-header d-flex justify-content-between align-items-center">
                                <div class="fw-bold text-dark"><i class="bi bi-journal-text text-primary me-1"></i> Students by Academic Program</div>
                                <span class="small text-muted">De-duplicated unique headcounts</span>
                            </div>
                            <div class="table-responsive">
                                <table class="table align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>Program</th>
                                            <th>College</th>
                                            <th class="text-center">Unique Students</th>
                                            <th class="text-end">Action</th>
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
                                                <td class="text-center">
                                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle fs-6 px-2 py-1">
                                                        {{ number_format($pd->total_students) }}
                                                    </span>
                                                </td>
                                                <td class="text-end">
                                                    <a href="{{ route('admin.boarding_monitoring.students', array_merge(request()->query(), ['college' => $pd->college_code !== 'Not specified' ? $pd->college_code : '', 'program' => $pd->program_name])) }}" class="btn btn-sm btn-outline-primary rounded-pill px-2 py-0" title="Drill into {{ $pd->program_name }}">
                                                        <i class="bi bi-arrow-right-short"></i> Filter
                                                    </a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="table-empty">No program distribution data available.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Panel 3: Property Distribution --}}
            <div class="tab-pane fade" id="properties-panel" role="tabpanel">
                <div class="boarded-card">
                    <div class="boarded-card-header d-flex justify-content-between align-items-center">
                        <div class="fw-bold text-dark"><i class="bi bi-buildings-fill text-success me-1"></i> Students by Boarding House</div>
                        <span class="small text-muted">Click a boarding house to filter records</span>
                    </div>
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="ps-3">Boarding House</th>
                                    <th>Address</th>
                                    <th class="text-center">Active Rooms</th>
                                    <th class="text-center">Unique Students</th>
                                    <th class="text-center">Total Stays</th>
                                    <th class="pe-3 text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($propertyDistribution as $prop)
                                    <tr>
                                        <td class="ps-3 fw-bold text-dark">{{ $prop->property_name }}</td>
                                        <td class="text-muted small">{{ $prop->property_address ?: 'Address not set' }}</td>
                                        <td class="text-center">
                                            <span class="badge bg-light text-dark border">{{ $prop->total_rooms }} rooms</span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-success-subtle text-success border border-success-subtle fs-6 px-2 py-1">
                                                {{ number_format($prop->total_students) }}
                                            </span>
                                        </td>
                                        <td class="text-center text-muted small">{{ number_format($prop->total_records) }}</td>
                                        <td class="pe-3 text-end">
                                            <a href="{{ route('admin.boarding_monitoring.students', array_merge(request()->query(), ['boarding_house' => $prop->property_id])) }}" class="btn btn-sm btn-outline-success rounded-pill px-2 py-0">
                                                <i class="bi bi-arrow-right-short"></i> Filter
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="table-empty">No boarding house distribution data available.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Dependent Program Dropdown Client Script --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const collegeSelect = document.getElementById('collegeSelect');
            const programSelect = document.getElementById('programSelect');
            const catalogPrograms = @json($catalogPrograms ?? []);
            const allPrograms = @json($programs ?? []);
            const selectedProgram = @json($program ?? '');

            if (!collegeSelect || !programSelect) return;

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
        });
    </script>
@endsection
