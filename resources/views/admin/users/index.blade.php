@extends('layouts.admin')

@section('content')
<style>
    .users-shell {
        background: linear-gradient(180deg, #ffffff 0%, #fbfdfc 100%);
        border: 1px solid rgba(2, 8, 20, .08);
        border-radius: 1.25rem;
        box-shadow: 0 10px 26px rgba(2, 8, 20, .06);
        padding: 1.25rem;
    }
    .muted { color: rgba(2, 8, 20, .58); }
    .metric-tile {
        border: 1px solid rgba(2, 8, 20, .08);
        border-radius: 1rem;
        background: #fff;
        box-shadow: 0 8px 18px rgba(2, 8, 20, .05);
        padding: 1.5rem 1.25rem;
        height: 100%;
        transition: all .2s ease;
    }
    .metric-tile:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 28px rgba(2, 8, 20, .08);
        border-color: rgba(2, 8, 20, .12);
    }
    .metric-tile a { text-decoration: none; color: inherit; display: block; }
    .metric-label {
        font-size: .8rem;
        text-transform: uppercase;
        letter-spacing: .06em;
        color: rgba(2, 8, 20, .48);
        margin-bottom: .75rem;
        font-weight: 600;
    }
    .metric-value {
        font-size: 2.5rem;
        font-weight: 800;
        color: #166534;
        line-height: 1;
        margin-bottom: .5rem;
    }
    .metric-subtitle {
        font-size: .85rem;
        color: rgba(2, 8, 20, .62);
        line-height: 1.4;
    }
    .section-card {
        border: 1px solid rgba(2, 8, 20, .08);
        border-radius: 1rem;
        background: #fff;
        box-shadow: 0 8px 20px rgba(2, 8, 20, .05);
        overflow: hidden;
    }
    .filters-card {
        overflow: visible;
        position: relative;
        z-index: 30;
    }
    .section-header {
        border-bottom: 1px solid rgba(2, 8, 20, .08);
        background: #fff;
        padding: 1rem 1.25rem;
    }
    .table thead th {
        font-size: .78rem;
        text-transform: uppercase;
        letter-spacing: .05em;
        color: rgba(2, 8, 20, .62);
        background: rgba(248, 250, 252, .96);
        border: none;
        vertical-align: middle;
        font-weight: 600;
    }
    .table tbody td {
        vertical-align: middle;
        border-color: rgba(2, 8, 20, .05);
        padding: .85rem 1rem;
    }
    .table tbody tr {
        transition: background-color .15s ease;
    }
    .table tbody tr:hover {
        background-color: rgba(248, 250, 252, .5);
    }
    .avatar-item {
        width: 40px;
        height: 40px;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid rgba(22, 101, 52, .22);
        background: rgba(22, 101, 52, .12);
        color: #166534;
        font-weight: 700;
        font-size: .9rem;
        flex-shrink: 0;
        margin-right: .75rem;
    }
    .avatar-photo {
        width: 40px;
        height: 40px;
        border-radius: 999px;
        object-fit: cover;
        border: 1px solid rgba(22, 101, 52, .22);
        margin-right: .75rem;
        flex-shrink: 0;
        background: #f8fafc;
    }
    .custom-filter-select {
        position: relative;
        width: 100%;
    }
    .custom-filter-toggle {
        width: 100%;
        height: calc(1.5em + .75rem + 2px);
        border: 1px solid #ced4da;
        border-radius: .375rem;
        background: #fff;
        color: #212529;
        padding: .375rem .75rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        font-size: .95rem;
        text-align: left;
    }
    .custom-filter-toggle-label {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        padding-right: .5rem;
    }
    .custom-filter-menu {
        position: absolute;
        top: calc(100% + .35rem);
        left: 0;
        right: 0;
        z-index: 25;
        border: 1px solid rgba(2, 8, 20, .12);
        border-radius: .6rem;
        background: #fff;
        box-shadow: 0 12px 28px rgba(2, 8, 20, .12);
        max-height: 230px;
        overflow-y: auto;
        padding: .35rem;
        display: none;
    }
    .custom-filter-menu.open {
        display: block;
    }
    .custom-filter-option {
        width: 100%;
        border: 0;
        background: transparent;
        text-align: left;
        border-radius: .45rem;
        padding: .45rem .5rem;
        color: #212529;
        font-size: .9rem;
    }
    .custom-filter-option:hover {
        background: rgba(22, 101, 52, .08);
    }
    .custom-filter-option.active {
        background: rgba(22, 101, 52, .14);
        color: #14532d;
        font-weight: 600;
    }
    .records-loading {
        opacity: .55;
        pointer-events: none;
        transition: opacity .15s ease;
    }

    .filter-desktop-label-row {
        display: grid;
        grid-template-columns: 5fr 2fr 3fr;
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
        grid-template-columns: 5fr 2fr 3fr;
        gap: .5rem;
        align-items: center;
    }

    @media (max-width: 767.98px) {
        .users-shell { padding: .95rem; }
        .metric-value { font-size: 1.75rem; }
        .metric-tile { padding: 1rem; }

        .filter-desktop-label-row,
        .filter-desktop-controls {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="users-shell">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <div class="text-uppercase small muted fw-semibold">System Administration</div>
            <h1 class="h4 mb-1">User Management</h1>
            <div class="muted small">Manage all system users and their roles</div>
        </div>
        <!-- <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary rounded-pill px-3">
            Back to Dashboard
        </a> -->
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- User Statistics -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-lg-6">
            <div class="metric-tile">
                <div class="metric-label"><i class="bi bi-people me-2"></i>Total Users</div>
                <div class="metric-value">{{ $users->whereIn('role', ['student', 'landlord'])->count() }}</div>
                <div class="metric-subtitle">All registered system accounts</div>
            </div>
        </div>
        <div class="col-12 col-lg-6">
            <div class="row g-3">
                <div class="col-6">
                    <a href="{{ route('admin.users.students') }}" style="text-decoration: none; color: inherit;">
                        <div class="metric-tile" style="text-align: center;">
                            <div class="metric-label"><i class="bi bi-mortarboard"></i></div>
                            <div class="metric-value text-success">{{ $users->where('role', 'student')->count() }}</div>
                            <div class="metric-subtitle">Click here to view only students</div>
                        </div>
                    </a>
                </div>
                <div class="col-6">
                    <a href="{{ route('admin.users.landlords') }}" style="text-decoration: none; color: inherit;">
                        <div class="metric-tile" style="text-align: center;">
                            <div class="metric-label"><i class="bi bi-building"></i></div>
                            <div class="metric-value" style="color: #FFC107;">{{ $users->where('role', 'landlord')->count() }}</div>
                            <div class="metric-subtitle">Click here to view only landlords</div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Users Table -->
    <div class="section-card filters-card mb-3">
        <div class="section-header fw-semibold"><i class="bi bi-funnel me-2"></i> Filters</div>
        <div class="p-3">
            <form method="GET" action="{{ route('admin.users.index') }}" id="usersFilterForm">
                <div class="filter-desktop-label-row">
                    <span>Name Search</span>
                    <span>Role</span>
                    <span>Reset</span>
                </div>
                <div class="filter-desktop-controls">
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                        <input
                            type="text"
                            class="form-control"
                            name="search"
                            value="{{ $search ?? '' }}"
                            placeholder="Search name"
                        >
                    </div>
                    <div>
                        <input type="hidden" id="usersRoleFilter" name="role" value="{{ $roleFilter ?? 'all' }}">
                        <div class="custom-filter-select">
                            <button type="button" class="custom-filter-toggle" id="usersRoleToggle" aria-haspopup="listbox" aria-expanded="false">
                                <span class="custom-filter-toggle-label">All</span>
                                <i class="bi bi-chevron-down"></i>
                            </button>
                            <div class="custom-filter-menu" id="usersRoleMenu" role="listbox" aria-label="Filter by role"></div>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary" id="resetUsersFilters">Reset</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="section-card" id="usersRecordsSection">
        <div class="section-header d-flex justify-content-between align-items-center">
            <div class="fw-semibold"><i class="bi bi-person-lines-fill me-2"></i> All Users</div>
            <span class="badge text-bg-secondary">{{ $users->total() }} users</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th class="ps-3">Name</th>
                        <th>Role</th>
                        <th>Contact</th>
                        <th>Registered</th>
                        <th>Status</th>
                        <th class="pe-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td class="ps-3">
                                <div class="d-flex align-items-center">
                                    @if(!empty($user->profile_image_path))
                                        <img src="{{ asset('storage/' . $user->profile_image_path) }}" alt="{{ $user->full_name }}" class="avatar-photo">
                                    @else
                                        <div class="avatar-item">{{ strtoupper(substr($user->full_name, 0, 1)) }}</div>
                                    @endif
                                    <div>
                                        <div class="fw-semibold small">{{ $user->full_name }}</div>
                                        <div class="text-muted small" style="font-size: .75rem;">{{ $user->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge text-bg-{{ $user->role === 'landlord' ? 'warning' : 'success' }}">
                                    {{ ucfirst($user->role) }}
                                </span>
                            </td>
                            <td class="small muted">{{ $user->contact_number ?: '—' }}</td>
                            <td class="small muted">{{ $user->created_at->format('M d, Y') }}</td>
                            <td>
                                <span class="badge text-bg-{{ ($user->is_active ?? true) ? 'success' : 'secondary' }}">
                                    {{ ($user->is_active ?? true) ? '● Active' : '○ Inactive' }}
                                </span>
                            </td>
                            <td class="pe-3">
                                @if($user->role === 'student')
                                    <a href="{{ route('admin.users.students.show', $user) }}" class="btn btn-sm btn-outline-secondary" title="View student details">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                @elseif($user->role === 'landlord')
                                    <a href="{{ route('admin.users.landlords.show', $user) }}" class="btn btn-sm btn-outline-secondary" title="View landlord details">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-5">
                                <div><i class="bi bi-inbox" style="font-size: 2.5rem; opacity: 0.3;"></i></div>
                                <p class="mb-0 mt-2">No users found.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($users->hasPages())
        <div style="padding: 1rem; border-top: 1px solid rgba(2, 8, 20, .08); background: #f8fafc;">
            {{ $users->links() }}
        </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var filterForm = document.getElementById('usersFilterForm');
        var searchInput = filterForm ? filterForm.querySelector('input[name="search"]') : null;
        var roleInput = document.getElementById('usersRoleFilter');
        var roleToggle = document.getElementById('usersRoleToggle');
        var roleMenu = document.getElementById('usersRoleMenu');
        var recordsSection = document.getElementById('usersRecordsSection');
        var resetButton = document.getElementById('resetUsersFilters');

        if (!filterForm || !searchInput || !roleInput || !roleToggle || !roleMenu || !recordsSection || !resetButton) {
            return;
        }

        var roleOptions = [
            { value: 'all', label: 'All' },
            { value: 'student', label: 'Student' },
            { value: 'landlord', label: 'Landlord' }
        ];

        var activeRequestController = null;

        var setRoleLabel = function (value) {
            var selected = roleOptions.find(function (opt) { return opt.value === value; }) || roleOptions[0];
            var labelEl = roleToggle.querySelector('.custom-filter-toggle-label');
            if (labelEl) {
                labelEl.textContent = selected.label;
            }
        };

        var closeRoleMenu = function () {
            roleMenu.classList.remove('open');
            roleToggle.setAttribute('aria-expanded', 'false');
        };

        var renderRoleMenu = function () {
            roleMenu.innerHTML = '';
            roleOptions.forEach(function (item) {
                var button = document.createElement('button');
                button.type = 'button';
                button.className = 'custom-filter-option' + (roleInput.value === item.value ? ' active' : '');
                button.textContent = item.label;
                button.addEventListener('click', function () {
                    roleInput.value = item.value;
                    setRoleLabel(item.value);
                    renderRoleMenu();
                    closeRoleMenu();
                    updateRecords();
                });
                roleMenu.appendChild(button);
            });
        };

        var buildFilterUrl = function () {
            var params = new URLSearchParams(new FormData(filterForm));
            if (!params.get('search')) {
                params.delete('search');
            }
            if (!params.get('role') || params.get('role') === 'all') {
                params.delete('role');
            }

            var queryString = params.toString();
            return queryString ? (filterForm.action + '?' + queryString) : filterForm.action;
        };

        var bindPagination = function () {
            recordsSection.querySelectorAll('.pagination a').forEach(function (linkEl) {
                linkEl.addEventListener('click', function (event) {
                    event.preventDefault();
                    var nextUrl = linkEl.getAttribute('href');
                    if (!nextUrl) {
                        return;
                    }
                    updateRecords(nextUrl);
                });
            });
        };

        var updateRecords = async function (targetUrl) {
            var fetchUrl = targetUrl || buildFilterUrl();

            if (activeRequestController) {
                activeRequestController.abort();
            }
            activeRequestController = new AbortController();

            recordsSection.classList.add('records-loading');

            try {
                var response = await fetch(fetchUrl, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    signal: activeRequestController.signal
                });

                if (!response.ok) {
                    throw new Error('HTTP ' + response.status);
                }

                var html = await response.text();
                var parser = new DOMParser();
                var doc = parser.parseFromString(html, 'text/html');
                var nextRecords = doc.getElementById('usersRecordsSection');

                if (!nextRecords) {
                    window.location.href = fetchUrl;
                    return;
                }

                recordsSection.innerHTML = nextRecords.innerHTML;
                bindPagination();
                window.history.replaceState({}, '', fetchUrl);
            } catch (error) {
                if (error.name !== 'AbortError') {
                    window.location.href = fetchUrl;
                }
            } finally {
                recordsSection.classList.remove('records-loading');
            }
        };

        filterForm.addEventListener('submit', function (event) {
            event.preventDefault();
            updateRecords();
        });

        roleToggle.addEventListener('click', function (event) {
            event.stopPropagation();
            var willOpen = !roleMenu.classList.contains('open');
            closeRoleMenu();
            if (willOpen) {
                roleMenu.classList.add('open');
                roleToggle.setAttribute('aria-expanded', 'true');
            }
        });

        document.addEventListener('click', function (event) {
            if (!event.target.closest('.custom-filter-select')) {
                closeRoleMenu();
            }
        });

        var searchDebounce;
        var lastSearchValue = (searchInput.value || '').trim();

        searchInput.addEventListener('input', function () {
            clearTimeout(searchDebounce);
            searchDebounce = setTimeout(function () {
                var currentValue = (searchInput.value || '').trim();
                if (currentValue === lastSearchValue) {
                    return;
                }
                lastSearchValue = currentValue;
                updateRecords();
            }, 320);
        });

        resetButton.addEventListener('click', function (event) {
            event.preventDefault();
            searchInput.value = '';
            lastSearchValue = '';
            roleInput.value = 'all';
            setRoleLabel('all');
            renderRoleMenu();
            updateRecords(filterForm.action);
        });

        setRoleLabel(roleInput.value || 'all');
        renderRoleMenu();
        bindPagination();
    });
</script>
@endpush
@endsection