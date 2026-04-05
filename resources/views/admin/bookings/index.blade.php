@extends('layouts.admin')

@section('content')
    <style>
        .admin-bookings-shell {
            background: linear-gradient(180deg, #ffffff 0%, #fbfdfc 100%);
            border: 1px solid rgba(2, 8, 20, .08);
            border-radius: 1.25rem;
            box-shadow: 0 10px 26px rgba(2, 8, 20, .06);
            padding: 1.25rem;
        }

        .section-muted {
            color: rgba(2, 8, 20, .58);
        }

        .admin-metric {
            border: 1px solid rgba(2, 8, 20, .08);
            border-radius: 1rem;
            background: #fff;
            box-shadow: 0 6px 16px rgba(2, 8, 20, .04);
            padding: .95rem 1rem;
            height: 100%;
        }

        .admin-metric-label {
            font-size: .78rem;
            text-transform: uppercase;
            letter-spacing: .04em;
            color: rgba(2, 8, 20, .55);
        }

        .admin-metric-value {
            font-size: 1.45rem;
            font-weight: 700;
            color: #166534;
        }

        .admin-filter-card,
        .admin-table-card {
            border: 1px solid rgba(2, 8, 20, .08);
            border-radius: 1rem;
            background: #fff;
            box-shadow: 0 8px 20px rgba(2, 8, 20, .05);
            overflow: hidden;
        }

        .admin-card-header {
            padding: .85rem 1rem;
            border-bottom: 1px solid rgba(2, 8, 20, .08);
            background: #fff;
        }

        .booking-avatar {
            width: 40px;
            height: 40px;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(22, 101, 52, .12);
            color: #166534;
            border: 1px solid rgba(22, 101, 52, .22);
            flex-shrink: 0;
        }

        .table thead th {
            font-size: .78rem;
            text-transform: uppercase;
            letter-spacing: .05em;
            color: rgba(2, 8, 20, .62);
            background: rgba(248, 250, 252, .96);
            border-bottom: 1px solid rgba(2, 8, 20, .08);
            white-space: nowrap;
        }

        .table tbody td {
            vertical-align: middle;
        }

        .status-badge {
            font-size: .72rem;
            border-radius: 999px;
            padding: .38rem .62rem;
            text-transform: uppercase;
            letter-spacing: .04em;
        }

        .table-empty {
            padding: 3rem 1rem;
            text-align: center;
            color: rgba(2, 8, 20, .58);
        }

        .mobile-filter-toolbar {
            display: flex;
            gap: .5rem;
            align-items: center;
        }

        .mobile-filter-toolbar .input-group {
            flex: 1 1 auto;
        }

        .mobile-settings-btn {
            width: 42px;
            height: 42px;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex: 0 0 auto;
        }

        .mobile-filter-sheet {
            border-top-left-radius: 1.2rem;
            border-top-right-radius: 1.2rem;
            --bs-offcanvas-height: min(540px, calc(100vh - 4.5rem));
            max-height: none;
            z-index: 11020;
        }

        .offcanvas-backdrop.show {
            z-index: 11010;
        }

        body.booking-filter-open #chatbotWidget {
            display: none !important;
        }

        .mobile-filter-sheet .offcanvas-header {
            border-bottom: 1px solid rgba(2, 8, 20, .08);
        }

        .mobile-filter-sheet .offcanvas-body {
            overflow-y: visible;
        }

        .mobile-filter-handle {
            width: 42px;
            height: 4px;
            background: rgba(148, 163, 184, .6);
            border-radius: 999px;
            margin: .2rem auto .7rem;
        }

        .filter-sheet-label {
            font-size: .72rem;
            text-transform: uppercase;
            letter-spacing: .06em;
            color: rgba(2, 8, 20, .56);
            margin-bottom: .35rem;
            font-weight: 700;
        }

        .filter-desktop-label-row {
            display: grid;
            grid-template-columns: 4fr 2fr 2fr 2fr 2fr;
            gap: .5rem;
            margin-bottom: .35rem;
            font-size: .72rem;
            text-transform: uppercase;
            letter-spacing: .05em;
            color: rgba(2, 8, 20, .55);
            font-weight: 700;
        }

        .filter-desktop-controls {
            display: grid;
            grid-template-columns: 4fr 2fr 2fr 2fr 2fr;
            gap: .5rem;
            align-items: center;
        }

        @media (max-width: 767.98px) {
            .admin-bookings-shell {
                padding: .95rem;
            }

            .admin-filter-card .admin-card-header {
                display: none;
            }

            .mobile-settings-btn {
                display: inline-flex;
            }

            .mobile-filter-sheet {
                bottom: 0;
                --bs-offcanvas-height: min(560px, calc(100vh - 4.25rem));
                max-height: none;
            }

            .mobile-filter-sheet .offcanvas-body {
                padding-bottom: calc(1rem + env(safe-area-inset-bottom));
            }
        }

        @media (min-width: 768px) {
            .mobile-settings-btn {
                display: none;
            }

            .mobile-filter-toolbar {
                display: block;
            }
        }
    </style>

    <div class="admin-bookings-shell container-fluid py-2">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
            <div>
                <div class="text-uppercase small section-muted fw-semibold">Monitoring</div>
                <h1 class="h3 mb-1">Bookings Monitoring</h1>
                <p class="section-muted mb-0">System-wide booking activity and occupancy requests.</p>
            </div>
            <!-- <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary rounded-pill px-3">
                <i class="bi bi-arrow-left me-1"></i>Dashboard
            </a> -->
        </div>

        <div class="row g-3 mb-4">
            <div class="col-6 col-lg-3">
                <div class="admin-metric">
                    <div class="admin-metric-label">Total Bookings</div>
                    <div class="admin-metric-value">{{ number_format($totalBookings) }}</div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="admin-metric">
                    <div class="admin-metric-label">Pending</div>
                    <div class="admin-metric-value">{{ number_format($pendingBookings) }}</div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="admin-metric">
                    <div class="admin-metric-label">Approved</div>
                    <div class="admin-metric-value">{{ number_format($approvedBookings) }}</div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="admin-metric">
                    <div class="admin-metric-label">Active Tenants</div>
                    <div class="admin-metric-value">{{ number_format($activeTenants) }}</div>
                </div>
            </div>
        </div>

        <div class="admin-filter-card mb-3">
            <div class="admin-card-header fw-semibold"><i class="bi bi-funnel me-1"></i> Filters</div>
            <div class="p-3">
                <form class="row g-2 align-items-end" method="GET" action="{{ route('admin.bookings.index') }}" id="desktopBookingFilterForm">
                    <div class="col-12 d-none d-md-block">
                        <div class="filter-desktop-label-row">
                            <span>Search</span>
                            <span>Status</span>
                            <span>Requested From</span>
                            <span>Requested To</span>
                            <span>Actions</span>
                        </div>
                        <div class="filter-desktop-controls">
                            <div>
                                <div class="input-group">
                                    <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                                    <input
                                        type="text"
                                        class="form-control"
                                        name="search"
                                        value="{{ $search ?? '' }}"
                                        placeholder="Booking ID, student, property, landlord"
                                    >
                                </div>
                            </div>
                            <div>
                                <select class="form-select" name="status">
                                    <option value="" @selected(!$status)>All</option>
                                    <option value="pending" @selected($status==='pending')>Pending</option>
                                    <option value="approved" @selected($status==='approved')>Approved</option>
                                    <option value="rejected" @selected($status==='rejected')>Rejected</option>
                                    <option value="cancelled" @selected($status==='cancelled')>Cancelled</option>
                                </select>
                            </div>
                            <div>
                                <input type="date" class="form-control" name="date_from" value="{{ $dateFrom ?? '' }}">
                            </div>
                            <div>
                                <input type="date" class="form-control" name="date_to" value="{{ $dateTo ?? '' }}">
                            </div>
                            <div class="d-flex gap-2">
                                <button class="btn btn-success flex-fill">
                                    <i class="bi bi-check2-circle me-1"></i>Apply
                                </button>
                                <a href="{{ route('admin.bookings.index') }}" class="btn btn-outline-secondary">Reset</a>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 d-md-none mobile-filter-toolbar">
                        <label class="form-label small mb-1 d-none">Search</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                            <input
                                type="text"
                                class="form-control"
                                id="bookingSearchInput"
                                name="search_preview"
                                value="{{ $search ?? '' }}"
                                placeholder="Booking ID, student, property, landlord"
                            >
                        </div>
                        <button
                            class="btn btn-outline-secondary mobile-settings-btn"
                            type="button"
                            data-bs-toggle="offcanvas"
                            data-bs-target="#mobileBookingFilterSheet"
                            aria-expanded="false"
                            aria-controls="mobileBookingFilterSheet"
                            title="Filter settings"
                        >
                            <i class="bi bi-sliders"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <form method="GET" action="{{ route('admin.bookings.index') }}" id="mobileBookingFilterForm" class="d-md-none">
            <div class="offcanvas offcanvas-bottom mobile-filter-sheet" tabindex="-1" id="mobileBookingFilterSheet" aria-labelledby="mobileBookingFilterSheetLabel">
                <div class="offcanvas-header pb-2">
                    <div class="w-100">
                        <div class="mobile-filter-handle"></div>
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="offcanvas-title mb-0" id="mobileBookingFilterSheetLabel">Filter by</h5>
                            <a href="{{ route('admin.bookings.index') }}" class="btn btn-link text-decoration-none p-0">Reset</a>
                        </div>
                    </div>
                    <button type="button" class="btn-close ms-2" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                </div>
                <div class="offcanvas-body pt-3">
                    <input type="hidden" name="search" id="mobileFilterSearchInput" value="{{ $search ?? '' }}">

                    <div class="mb-3">
                        <label class="filter-sheet-label">Status</label>
                        <select class="form-select" name="status">
                            <option value="" @selected(!$status)>All</option>
                            <option value="pending" @selected($status==='pending')>Pending</option>
                            <option value="approved" @selected($status==='approved')>Approved</option>
                            <option value="rejected" @selected($status==='rejected')>Rejected</option>
                            <option value="cancelled" @selected($status==='cancelled')>Cancelled</option>
                        </select>
                    </div>

                    <div class="row g-2">
                        <div class="col-6">
                            <label class="filter-sheet-label">Requested From</label>
                            <input type="date" class="form-control" name="date_from" value="{{ $dateFrom ?? '' }}">
                        </div>
                        <div class="col-6">
                            <label class="filter-sheet-label">Requested To</label>
                            <input type="date" class="form-control" name="date_to" value="{{ $dateTo ?? '' }}">
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-4">
                        <a href="{{ route('admin.bookings.index') }}" class="btn btn-outline-secondary flex-fill">Reset All</a>
                        <button type="submit" class="btn btn-success flex-fill">Apply Filters</button>
                    </div>
                </div>
            </div>
        </form>

        <div class="admin-table-card">
            <div class="admin-card-header fw-semibold d-flex justify-content-between align-items-center gap-2">
                <span><i class="bi bi-list-ul me-1"></i> Booking Records</span>
                <span class="badge text-bg-light border">{{ $bookings->total() }} total entries</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="ps-3">Student</th>
                            <th>Property / Room</th>
                            <th>Landlord</th>
                            <th>Status</th>
                            <th>Stay Dates</th>
                            <th class="pe-3">Requested</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($bookings as $b)
                            <tr>
                                <td class="ps-3">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="booking-avatar">
                                            <i class="bi bi-person"></i>
                                        </div>
                                        <div>
                                            <div class="fw-semibold">{{ $b->student->full_name ?? '—' }}</div>
                                            <div class="section-muted small">{{ $b->student->email ?? '' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-semibold">{{ $b->room->property->name ?? '—' }}</div>
                                    <div class="section-muted small">Room {{ $b->room->room_number ?? '—' }} • {{ $b->room->property->address ?? '' }}</div>
                                </td>
                                <td>
                                    <div>{{ $b->room->property->landlord->full_name ?? '—' }}</div>
                                    <div class="section-muted small">{{ $b->room->property->landlord->email ?? '' }}</div>
                                </td>
                                <td>
                                    @php $st = $b->status; @endphp
                                    <span class="badge status-badge
                                        @if($st==='pending') text-bg-warning
                                        @elseif($st==='approved') text-bg-success
                                        @elseif($st==='rejected') text-bg-danger
                                        @else text-bg-secondary @endif
                                    ">{{ ucfirst($st) }}</span>
                                </td>
                                <td class="small">
                                    <div>{{ optional($b->check_in)->format('M d, Y') }}</div>
                                    <div class="section-muted">to {{ optional($b->check_out)->format('M d, Y') }}</div>
                                </td>
                                <td class="pe-3 section-muted small">{{ $b->created_at->format('M d, Y h:i A') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="table-empty">
                                    <div class="h6 mb-1"><i class="bi bi-inbox me-1"></i>No bookings found.</div>
                                    <div>No records match your selected filters.</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-3">
            {{ $bookings->links() }}
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                var searchInput = document.getElementById('bookingSearchInput');
                var mobileSearchInput = document.getElementById('mobileFilterSearchInput');
                var mobileFilterSheet = document.getElementById('mobileBookingFilterSheet');
                if (!searchInput || !mobileSearchInput) return;

                var syncSearch = function () {
                    mobileSearchInput.value = searchInput.value || '';
                };

                syncSearch();
                searchInput.addEventListener('input', syncSearch);

                if (mobileFilterSheet) {
                    mobileFilterSheet.addEventListener('show.bs.offcanvas', function () {
                        document.body.classList.add('booking-filter-open');
                    });
                    mobileFilterSheet.addEventListener('hidden.bs.offcanvas', function () {
                        document.body.classList.remove('booking-filter-open');
                    });
                }
            });
        </script>
    @endpush
@endsection
