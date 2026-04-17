@extends('layouts.admin')

@section('content')
<style>
    .admin-onboarding-shell {
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

    .onboarding-avatar {
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

    .status-nav .btn {
        border-radius: 999px;
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
        --bs-offcanvas-height: min(560px, calc(100vh - 4.25rem));
        z-index: 11020;
    }

    .offcanvas-backdrop.show {
        z-index: 11010;
    }

    body.onboarding-filter-open #chatbotWidget {
        display: none !important;
    }

    .mobile-filter-sheet .offcanvas-header {
        border-bottom: 1px solid rgba(2, 8, 20, .08);
    }

    .mobile-filter-sheet .offcanvas-body {
        overflow-y: auto;
        padding-bottom: calc(1rem + env(safe-area-inset-bottom));
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
        grid-template-columns: 5fr 2fr 2fr 3fr;
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
        grid-template-columns: 5fr 2fr 2fr 3fr;
        gap: .5rem;
        align-items: center;
    }

    @media (max-width: 767.98px) {
        .admin-onboarding-shell {
            padding: .95rem;
        }

        .admin-filter-card .admin-card-header {
            display: none;
        }

        .desktop-only {
            display: none !important;
        }

        .mobile-settings-btn {
            display: inline-flex;
        }

        .mobile-filter-sheet {
            bottom: 0;
        }
    }

    @media (min-width: 768px) {
        .mobile-settings-btn,
        .mobile-only {
            display: none !important;
        }

        .mobile-filter-toolbar {
            display: block;
        }
    }
</style>

<div class="admin-onboarding-shell container-fluid py-2">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <div class="text-uppercase small section-muted fw-semibold">Management</div>
            <h1 class="h3 mb-1">Tenant Onboardings</h1>
            <p class="section-muted mb-0">Review and track all onboarding progress from request to completion.</p>
        </div>
        <!-- <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary rounded-pill px-3">
            <i class="bi bi-arrow-left me-1"></i>Dashboard
        </a> -->
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="admin-metric">
                <div class="admin-metric-label">Total Onboardings</div>
                <div class="admin-metric-value">{{ number_format($onboardings->total()) }}</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="admin-metric">
                <div class="admin-metric-label">Current Page</div>
                <div class="admin-metric-value">{{ number_format($onboardings->count()) }}</div>
            </div>
        </div>
    </div>

    <div class="admin-filter-card mb-3">
        <div class="admin-card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div class="fw-semibold"><i class="bi bi-funnel me-1"></i> Filters</div>
            <div class="btn-group status-nav desktop-only" role="group" aria-label="Onboarding status views">
                <a href="{{ route('admin.onboardings.index', ['search' => $search ?? null, 'date_from' => $dateFrom ?? null, 'date_to' => $dateTo ?? null]) }}"
                    class="btn btn-outline-success btn-sm {{ ($status ?? 'all') === 'all' ? 'active' : '' }}">All</a>
                <a href="{{ route('admin.onboardings.index', ['status' => 'active', 'search' => $search ?? null, 'date_from' => $dateFrom ?? null, 'date_to' => $dateTo ?? null]) }}"
                    class="btn btn-outline-success btn-sm {{ ($status ?? 'all') === 'active' ? 'active' : '' }}">Active</a>
                <a href="{{ route('admin.onboardings.index', ['status' => 'pending', 'search' => $search ?? null, 'date_from' => $dateFrom ?? null, 'date_to' => $dateTo ?? null]) }}"
                    class="btn btn-outline-success btn-sm {{ ($status ?? 'all') === 'pending' ? 'active' : '' }}">Pending</a>
                <a href="{{ route('admin.onboardings.index', ['status' => 'completed', 'search' => $search ?? null, 'date_from' => $dateFrom ?? null, 'date_to' => $dateTo ?? null]) }}"
                    class="btn btn-outline-success btn-sm {{ ($status ?? 'all') === 'completed' ? 'active' : '' }}">Completed</a>
            </div>
        </div>

        <div class="p-3">
            <form class="row g-2 align-items-end" method="GET" action="{{ route('admin.onboardings.index') }}" id="desktopOnboardingFilterForm">
                <input type="hidden" name="status" value="{{ $status ?? 'all' }}">

                <div class="col-12 d-none d-md-block">
                    <div class="filter-desktop-label-row">
                        <span>Search</span>
                        <span>Created From</span>
                        <span>Created To</span>
                        <span>Actions</span>
                    </div>
                    <div class="filter-desktop-controls">
                        <div class="input-group">
                            <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                            <input
                                type="text"
                                class="form-control"
                                name="search"
                                value="{{ $search ?? '' }}"
                                placeholder="Student, landlord, property, room, ID"
                            >
                        </div>
                        <input type="date" class="form-control" name="date_from" value="{{ $dateFrom ?? '' }}">
                        <input type="date" class="form-control" name="date_to" value="{{ $dateTo ?? '' }}">
                        <div class="d-flex gap-2">
                            <button class="btn btn-success flex-fill">
                                <i class="bi bi-check2-circle me-1"></i>Apply
                            </button>
                            <a href="{{ route('admin.onboardings.index') }}" class="btn btn-outline-secondary">Reset</a>
                        </div>
                    </div>
                </div>

                <div class="col-12 d-md-none mobile-filter-toolbar">
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                        <input
                            type="text"
                            class="form-control"
                            id="onboardingSearchInput"
                            name="search_preview"
                            value="{{ $search ?? '' }}"
                            placeholder="Student, landlord, property, room, ID"
                        >
                    </div>
                    <button
                        class="btn btn-outline-secondary mobile-settings-btn"
                        type="button"
                        data-bs-toggle="offcanvas"
                        data-bs-target="#mobileOnboardingFilterSheet"
                        aria-expanded="false"
                        aria-controls="mobileOnboardingFilterSheet"
                        title="Filter settings"
                    >
                        <i class="bi bi-sliders"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <form method="GET" action="{{ route('admin.onboardings.index') }}" id="mobileOnboardingFilterForm" class="mobile-only">
        <div class="offcanvas offcanvas-bottom mobile-filter-sheet" tabindex="-1" id="mobileOnboardingFilterSheet" aria-labelledby="mobileOnboardingFilterSheetLabel">
            <div class="offcanvas-header pb-2">
                <div class="w-100">
                    <div class="mobile-filter-handle"></div>
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="offcanvas-title mb-0" id="mobileOnboardingFilterSheetLabel">Filter by</h5>
                        <a href="{{ route('admin.onboardings.index') }}" class="btn btn-link text-decoration-none p-0">Reset</a>
                    </div>
                </div>
                <button type="button" class="btn-close ms-2" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>
            <div class="offcanvas-body pt-3">
                <input type="hidden" name="search" id="mobileOnboardingSearchInput" value="{{ $search ?? '' }}">

                <div class="mb-3">
                    <label class="filter-sheet-label">Status</label>
                    <select class="form-select" name="status">
                        <option value="all" @selected(($status ?? 'all') === 'all')>All</option>
                        <option value="active" @selected(($status ?? 'all') === 'active')>Active</option>
                        <option value="pending" @selected(($status ?? 'all') === 'pending')>Pending</option>
                        <option value="completed" @selected(($status ?? 'all') === 'completed')>Completed</option>
                    </select>
                </div>

                <div class="row g-2">
                    <div class="col-6">
                        <label class="filter-sheet-label">Created From</label>
                        <input type="date" class="form-control" name="date_from" value="{{ $dateFrom ?? '' }}">
                    </div>
                    <div class="col-6">
                        <label class="filter-sheet-label">Created To</label>
                        <input type="date" class="form-control" name="date_to" value="{{ $dateTo ?? '' }}">
                    </div>
                </div>

                <div class="d-flex gap-2 mt-4">
                    <a href="{{ route('admin.onboardings.index') }}" class="btn btn-outline-secondary flex-fill">Reset All</a>
                    <button type="submit" class="btn btn-success flex-fill">Apply Filters</button>
                </div>
            </div>
        </div>
    </form>

    <div class="admin-table-card">
        <div class="admin-card-header fw-semibold d-flex justify-content-between align-items-center gap-2">
            <span><i class="bi bi-list-ul me-1"></i> Onboarding Records</span>
            <span class="badge text-bg-light border">{{ $onboardings->total() }} total entries</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-3">Student</th>
                        <th>Property</th>
                        <th>Landlord</th>
                        <th>Room</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th class="pe-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($onboardings as $onboarding)
                        <tr>
                            <td class="ps-3">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="onboarding-avatar">
                                        <i class="bi bi-person"></i>
                                    </div>
                                    <div>
                                        <div class="fw-semibold">{{ $onboarding->booking->student->full_name }}</div>
                                        <div class="section-muted small">{{ $onboarding->booking->student->student_id }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $onboarding->booking->room->property->name }}</div>
                                <div class="section-muted small">{{ $onboarding->booking->room->property->address }}</div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="onboarding-avatar">
                                        <i class="bi bi-building"></i>
                                    </div>
                                    <div>
                                        <div>{{ $onboarding->booking->room->property->landlord->full_name }}</div>
                                        <div class="section-muted small">{{ $onboarding->booking->room->property->landlord->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div><span class="badge text-bg-info">{{ $onboarding->booking->room->room_number }}</span></div>
                                <div class="section-muted small mt-1">PHP {{ number_format($onboarding->booking->room->price, 2) }}/month</div>
                            </td>
                            <td>
                                @switch($onboarding->status)
                                    @case('pending')
                                        <span class="badge status-badge text-bg-warning">Pending Documents</span>
                                        @break
                                    @case('documents_uploaded')
                                        <span class="badge status-badge text-bg-info">Documents Uploaded</span>
                                        @break
                                    @case('documents_approved')
                                        <span class="badge status-badge text-bg-primary">Documents Approved</span>
                                        @break
                                    @case('contract_signed')
                                        <span class="badge status-badge text-bg-secondary">Contract Signed</span>
                                        @break
                                    @case('deposit_paid')
                                        <span class="badge status-badge text-bg-success">Deposit Paid</span>
                                        @break
                                    @case('completed')
                                        <span class="badge status-badge text-bg-success">Completed</span>
                                        @break
                                    @default
                                        <span class="badge status-badge text-bg-light">{{ ucfirst($onboarding->status) }}</span>
                                @endswitch
                            </td>
                            <td>
                                <div class="small fw-semibold">{{ $onboarding->created_at->format('M d, Y') }}</div>
                                <div class="section-muted small">{{ $onboarding->created_at->diffForHumans() }}</div>
                            </td>
                            <td class="pe-3">
                                <a href="{{ route('admin.onboardings.show', $onboarding) }}" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-eye me-1"></i>View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="table-empty">
                                <div class="h6 mb-1"><i class="bi bi-inbox me-1"></i>No onboardings found.</div>
                                <div>No records are available for this view yet.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($onboardings->hasPages())
        <div class="d-flex justify-content-center mt-4">
            {{ $onboardings->links() }}
        </div>
    @endif
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var searchInput = document.getElementById('onboardingSearchInput');
        var mobileSearchInput = document.getElementById('mobileOnboardingSearchInput');
        var mobileFilterSheet = document.getElementById('mobileOnboardingFilterSheet');
        if (!searchInput || !mobileSearchInput) return;

        var syncSearch = function () {
            mobileSearchInput.value = searchInput.value || '';
        };

        syncSearch();
        searchInput.addEventListener('input', syncSearch);

        if (mobileFilterSheet) {
            mobileFilterSheet.addEventListener('show.bs.offcanvas', function () {
                document.body.classList.add('onboarding-filter-open');
            });
            mobileFilterSheet.addEventListener('hidden.bs.offcanvas', function () {
                document.body.classList.remove('onboarding-filter-open');
            });
        }
    });
</script>
@endpush
@endsection
