@extends('layouts.admin')

@section('title', 'User Accounts - Admin Portal')

@section('content')
<style>
    .custom-filter-select {
        position: relative;
        width: 100%;
    }
    .custom-filter-toggle {
        width: 100%;
        height: 42px;
        border: 1px solid var(--admin-border);
        border-radius: var(--admin-radius-md);
        background: #fff;
        color: var(--admin-text);
        padding: .375rem .75rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        font-size: .875rem;
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
        border: 1px solid var(--admin-border);
        border-radius: var(--admin-radius-md);
        background: #fff;
        box-shadow: var(--admin-shadow-md);
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
        border-radius: var(--admin-radius-sm);
        padding: .45rem .5rem;
        color: var(--admin-text);
        font-size: .875rem;
    }
    .custom-filter-option:hover {
        background: var(--admin-primary-soft);
    }
    .custom-filter-option.active {
        background: var(--admin-primary-soft);
        color: var(--admin-primary);
        font-weight: 600;
    }
    .records-loading {
        opacity: .55;
        pointer-events: none;
        transition: opacity .15s ease;
    }
</style>

<div class="admin-page-shell">
    <x-admin.page-header
        title="User Accounts"
        subtitle="Manage all registered system user accounts, roles, and status."
        icon="bi-people"
        :breadcrumbs="[
            ['label' => 'System', 'url' => route('admin.dashboard')],
            ['label' => 'User Accounts']
        ]"
    >
        <x-slot name="actions">
            <span class="text-secondary small fw-medium">
                <i class="bi bi-info-circle me-1"></i>{{ $users->total() }} total users
            </span>
        </x-slot>
    </x-admin.page-header>

    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm alert-dismissible fade show mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- User Statistics -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-md-4">
            <x-admin.metric-card
                label="Total Users"
                :value="$users->whereIn('role', ['student', 'landlord'])->count()"
                icon="bi-people"
                variant="brand"
                subtext="Registered across OBHS"
            />
        </div>
        <div class="col-6 col-md-4">
            <x-admin.metric-card
                label="Students"
                :value="$users->where('role', 'student')->count()"
                icon="bi-mortarboard"
                variant="neutral"
                subtext="Click to view only students"
                :url="route('admin.users.students')"
            />
        </div>
        <div class="col-6 col-md-4">
            <x-admin.metric-card
                label="Landlords"
                :value="$users->where('role', 'landlord')->count()"
                icon="bi-building"
                variant="warning"
                subtext="Click to view only landlords"
                :url="route('admin.users.landlords')"
            />
        </div>
    </div>

    <!-- Filter Toolbar -->
    <div class="admin-toolbar mb-4">
        <form method="GET" action="{{ route('admin.users.index') }}" id="usersFilterForm">
            <div class="row g-2 align-items-center">
                <div class="col-12 col-md-6">
                    <div class="input-group">
                        <span class="input-group-text bg-white text-muted border-end-0"><i class="bi bi-search"></i></span>
                        <input
                            type="text"
                            class="form-control border-start-0 ps-0"
                            name="search"
                            value="{{ $search ?? '' }}"
                            placeholder="Search by user name or email..."
                        >
                    </div>
                </div>
                <div class="col-8 col-md-4">
                    <input type="hidden" id="usersRoleFilter" name="role" value="{{ $roleFilter ?? 'all' }}">
                    <div class="custom-filter-select">
                        <button type="button" class="custom-filter-toggle" id="usersRoleToggle" aria-haspopup="listbox" aria-expanded="false">
                            <span class="custom-filter-toggle-label">All Roles</span>
                            <i class="bi bi-chevron-down text-muted"></i>
                        </button>
                        <div class="custom-filter-menu" id="usersRoleMenu" role="listbox" aria-label="Filter by role"></div>
                    </div>
                </div>
                <div class="col-4 col-md-2 d-flex">
                    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary w-100" id="resetUsersFilters">Reset</a>
                </div>
            </div>
        </form>
    </div>

    <!-- Users Table Card -->
    <div class="admin-table-card" id="usersRecordsSection">
        <div class="admin-card-header d-flex justify-content-between align-items-center">
            <div class="fw-semibold text-dark"><i class="bi bi-person-lines-fill me-2 text-primary"></i> All Users</div>
            <span class="badge bg-light text-secondary border fw-normal">{{ $users->total() }} users</span>
        </div>
        <div class="table-responsive">
            <table class="table admin-table mb-0">
                <thead>
                    <tr>
                        <th class="ps-3">Name</th>
                        <th>Role</th>
                        <th>Contact</th>
                        <th>Registered</th>
                        <th>Status</th>
                        <th class="pe-3 text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td class="ps-3">
                                <div class="d-flex align-items-center">
                                    @if(!empty($user->profile_image_path))
                                        <img src="{{ file_url($user->profile_image_path) }}" alt="{{ $user->full_name }}" class="rounded-circle me-2 object-fit-cover" style="width: 36px; height: 36px;">
                                    @else
                                        <div class="rounded-circle d-inline-flex align-items-center justify-content-center bg-primary-subtle text-primary fw-bold me-2" style="width: 36px; height: 36px; font-size: 0.85rem;">
                                            {{ strtoupper(substr($user->full_name, 0, 1)) }}
                                        </div>
                                    @endif
                                    <div>
                                        <div class="fw-semibold text-dark">{{ $user->full_name }}</div>
                                        <div class="text-secondary small">{{ $user->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($user->role === 'landlord')
                                    <x-admin.status-badge status="pending" label="Landlord" />
                                @else
                                    <x-admin.status-badge status="active" label="Student" />
                                @endif
                            </td>
                            <td class="small text-secondary">{{ $user->contact_number ?: '—' }}</td>
                            <td class="small text-secondary">{{ $user->created_at->format('M d, Y') }}</td>
                            <td>
                                @if($user->is_active ?? true)
                                    <x-admin.status-badge status="active" label="Active" />
                                @else
                                    <x-admin.status-badge status="neutral" label="Inactive" />
                                @endif
                            </td>
                            <td class="pe-3 text-end">
                                @if($user->role === 'student')
                                    <a href="{{ route('admin.users.students.show', $user) }}" class="btn btn-sm btn-outline-secondary" title="View student details">
                                        <i class="bi bi-eye me-1"></i>View
                                    </a>
                                @elseif($user->role === 'landlord')
                                    <a href="{{ route('admin.users.landlords.show', $user) }}" class="btn btn-sm btn-outline-secondary" title="View landlord details">
                                        <i class="bi bi-eye me-1"></i>View
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <x-admin.empty-state
                                    title="No users found"
                                    description="No registered user records match the specified search keywords or role filters."
                                    icon="bi-people"
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($users->hasPages())
        <div class="p-3 border-top bg-light d-flex justify-content-center">
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
            { value: 'all', label: 'All Roles' },
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