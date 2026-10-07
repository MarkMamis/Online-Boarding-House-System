@extends('layouts.admin')

@section('title', $roleTitle . ' - OBHS Admin')

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
    .custom-filter-toggle:disabled {
        background: var(--admin-surface-subtle);
        color: var(--admin-text-muted);
        cursor: not-allowed;
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
        :title="$roleTitle"
        :subtitle="'Manage registered ' . strtolower($roleTitle) . ' profiles, academic affiliations, and status.'"
        :icon="$role === 'landlord' ? 'bi-building' : 'bi-mortarboard'"
        :breadcrumbs="[
            ['label' => 'System', 'url' => route('admin.dashboard')],
            ['label' => 'User Accounts', 'url' => route('admin.users.index')],
            ['label' => $roleTitle]
        ]"
    >
        <x-slot name="actions">
            <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-people me-1"></i>All Users
            </a>
            @if($role === 'landlord')
                <a href="{{ route('admin.permits.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-shield-check me-1"></i>Landlord Accreditation
                </a>
            @endif
        </x-slot>
    </x-admin.page-header>

    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm alert-dismissible fade show mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($role === 'student')
        <!-- Academic Filter Toolbar -->
        <div class="admin-toolbar mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="fw-semibold text-dark small text-uppercase tracking-wider">
                    <i class="bi bi-funnel me-1 text-primary"></i> Academic Catalog Filter
                </div>
                <div class="small text-secondary">MinSU Academic Mapping</div>
            </div>

            <form method="GET" action="{{ route('admin.users.students') }}" id="academicFilterForm">
                @php
                    $showMajorFilter = ($selectedProgram !== '' && count($majorOptions) > 0);
                @endphp
                <div class="row g-2 align-items-center" id="filterControlRow">
                    <div class="col-12 col-md-3">
                        <div class="input-group">
                            <span class="input-group-text bg-white text-muted border-end-0"><i class="bi bi-search"></i></span>
                            <input
                                type="text"
                                class="form-control border-start-0 ps-0"
                                name="search"
                                value="{{ $selectedNameSearch ?? '' }}"
                                placeholder="Student name..."
                            >
                        </div>
                    </div>

                    <div class="col-6 col-md-3">
                        <input type="hidden" id="collegeFilter" name="college" value="{{ $selectedCollege }}">
                        <div class="custom-filter-select">
                            <button type="button" class="custom-filter-toggle" id="collegeToggle" aria-haspopup="listbox" aria-expanded="false">
                                <span class="custom-filter-toggle-label">All Colleges</span>
                                <i class="bi bi-chevron-down text-muted"></i>
                            </button>
                            <div class="custom-filter-menu" id="collegeMenu" role="listbox" aria-label="Filter by college"></div>
                        </div>
                    </div>

                    <div class="col-6 col-md-3">
                        <input type="hidden" id="programFilter" name="program" value="{{ $selectedProgram }}">
                        <div class="custom-filter-select">
                            <button type="button" class="custom-filter-toggle" id="programToggle" aria-haspopup="listbox" aria-expanded="false">
                                <span class="custom-filter-toggle-label">Select college first</span>
                                <i class="bi bi-chevron-down text-muted"></i>
                            </button>
                            <div class="custom-filter-menu" id="programMenu" role="listbox" aria-label="Filter by program"></div>
                        </div>
                    </div>

                    <div class="col-6 col-md-2" id="majorFilterWrap" style="{{ ($selectedProgram !== '' && count($majorOptions) > 0) ? '' : 'display:none;' }}">
                        <input type="hidden" id="majorFilter" name="major" value="{{ $selectedMajor }}">
                        <div class="custom-filter-select">
                            <button type="button" class="custom-filter-toggle" id="majorToggle" aria-haspopup="listbox" aria-expanded="false">
                                <span class="custom-filter-toggle-label">All Majors</span>
                                <i class="bi bi-chevron-down text-muted"></i>
                            </button>
                            <div class="custom-filter-menu" id="majorMenu" role="listbox" aria-label="Filter by major"></div>
                        </div>
                    </div>

                    <div class="col-auto ms-auto d-flex gap-2">
                        <a href="{{ route('admin.users.students') }}" class="btn btn-action-icon" id="resetFiltersBtn" title="Reset Filters">
                            <i class="bi bi-arrow-counterclockwise"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    @endif

    <!-- Statistics -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-md-4">
            <x-admin.metric-card
                label="Total {{ $roleTitle }}"
                :value="$users->total()"
                :icon="$role === 'landlord' ? 'bi-building' : 'bi-mortarboard'"
                variant="brand"
                :subtext="$roleTitle . ' registered across OBHS'"
            />
        </div>
        <div class="col-6 col-md-4">
            <x-admin.metric-card
                label="New This Week"
                :value="$users->where('created_at', '>=', now()->startOfWeek())->count()"
                icon="bi-calendar-event"
                variant="neutral"
                subtext="Registered this calendar week"
            />
        </div>
        <div class="col-6 col-md-4">
            <x-admin.metric-card
                label="New This Month"
                :value="$users->where('created_at', '>=', now()->startOfMonth())->count()"
                icon="bi-calendar-month"
                variant="neutral"
                subtext="Registered this calendar month"
            />
        </div>
    </div>

    <!-- Users Table -->
    <div class="admin-table-card" id="recordsSection">
        <div class="admin-card-header d-flex justify-content-between align-items-center">
            <div class="fw-semibold text-dark">
                <i class="bi bi-{{ $role === 'landlord' ? 'building' : 'person-lines-fill' }} me-2 text-primary"></i>
                {{ $roleTitle }} Directory
            </div>
            <span class="badge bg-light text-secondary border fw-normal">{{ $users->total() }} {{ strtolower($roleTitle) }}</span>
        </div>
        <div class="table-responsive">
            <table class="table admin-table mb-0">
                <thead>
                    <tr>
                        <th class="ps-3">Name</th>
                        @if($role !== 'student')
                            <th>Email</th>
                        @endif
                        <th>Contact</th>
                        @if($role === 'student')
                            <th>College</th>
                            <th>Program</th>
                        @endif
                        @if($role === 'landlord')
                            <th>Boarding House</th>
                            <th>Tenants</th>
                            <th>Onboarding</th>
                            <th>Rooms</th>
                            <th>Occupancy</th>
                        @endif
                        <th>Registered</th>
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
                                        @if($role === 'student')
                                            <div class="text-secondary small">{{ $user->email }}</div>
                                        @else
                                            <div class="text-secondary small">{{ $user->name }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            @if($role !== 'student')
                                <td class="small text-secondary">{{ $user->email }}</td>
                            @endif
                            <td class="small text-secondary">{{ $user->contact_number ?: '—' }}</td>
                            @if($role === 'student')
                                <td class="small text-secondary">
                                    @if(filled($user->college) && isset($collegeOptions[$user->college]))
                                        {{ $collegeOptions[$user->college] }} ({{ $user->college }})
                                    @else
                                        {{ $user->college ?: '—' }}
                                    @endif
                                </td>
                                <td>
                                    <div class="small fw-semibold text-dark">{{ $user->program ?: '—' }}</div>
                                    @if(filled($user->major))
                                        <div class="text-secondary small">{{ $user->major }}</div>
                                    @endif
                                </td>
                            @endif
                            @if($role === 'landlord')
                                <td class="small fw-semibold text-dark">{{ $user->boarding_house_name ?: '—' }}</td>
                                <td>
                                    <span class="badge bg-success-subtle text-success border">{{ $user->current_tenants ?? 0 }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-warning-subtle text-warning-emphasis border">{{ $user->onboarding_tenants ?? 0 }}</span>
                                </td>
                                <td class="small text-secondary">{{ $user->occupied_rooms ?? 0 }}/{{ $user->total_rooms ?? 0 }}</td>
                                <td>
                                    @if(isset($user->total_rooms) && $user->total_rooms > 0)
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="progress grow" style="height: 6px; width: 60px; border-radius: 999px;">
                                                <div class="progress-bar bg-{{ ($user->occupied_rooms / $user->total_rooms) > 0.8 ? 'danger' : (($user->occupied_rooms / $user->total_rooms) > 0.5 ? 'warning' : 'success') }}"
                                                     style="width: {{ ($user->occupied_rooms / $user->total_rooms) * 100 }}%"></div>
                                            </div>
                                            <small class="text-secondary" style="min-width: 38px;">{{ round(($user->occupied_rooms / $user->total_rooms) * 100) }}%</small>
                                        </div>
                                    @else
                                        <small class="text-muted">—</small>
                                    @endif
                                </td>
                            @endif
                            <td class="small text-secondary">{{ $user->created_at->format('M d, Y') }}</td>
                            <td class="pe-3 text-end">
                                @if($role === 'student')
                                    <a href="{{ route('admin.users.students.show', $user) }}" class="btn btn-sm btn-outline-secondary" title="View details">
                                        <i class="bi bi-eye me-1"></i>View
                                    </a>
                                @elseif($role === 'landlord')
                                    <a href="{{ route('admin.users.landlords.show', $user) }}" class="btn btn-sm btn-outline-secondary" title="View details">
                                        <i class="bi bi-eye me-1"></i>View
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $role === 'landlord' ? '10' : ($role === 'student' ? '6' : '5') }}">
                                <x-admin.empty-state
                                    :title="'No ' . strtolower($roleTitle) . ' found'"
                                    :description="'No registered ' . strtolower($roleTitle) . ' records match the current filter criteria.'"
                                    :icon="$role === 'landlord' ? 'bi-building' : 'bi-mortarboard'"
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

@if($role === 'student')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const filterForm = document.getElementById('academicFilterForm');
    const searchInput = filterForm ? filterForm.querySelector('input[name="search"]') : null;
    const recordsSection = document.getElementById('recordsSection');
    const resetFiltersBtn = document.getElementById('resetFiltersBtn');

    const collegeInput = document.getElementById('collegeFilter');
    const programInput = document.getElementById('programFilter');
    const majorInput = document.getElementById('majorFilter');

    const collegeToggle = document.getElementById('collegeToggle');
    const programToggle = document.getElementById('programToggle');
    const majorToggle = document.getElementById('majorToggle');

    const collegeMenu = document.getElementById('collegeMenu');
    const programMenu = document.getElementById('programMenu');
    const majorMenu = document.getElementById('majorMenu');

    const majorWrap = document.getElementById('majorFilterWrap');
    const filterControlRow = document.getElementById('filterControlRow');

    if (!filterForm || !searchInput || !recordsSection || !resetFiltersBtn || !collegeInput || !programInput || !majorInput || !collegeToggle || !programToggle || !majorToggle || !collegeMenu || !programMenu || !majorMenu || !majorWrap || !filterControlRow) {
        return;
    }

    const colleges = @json($collegeOptions);
    const programsByCollege = @json($catalogProgramsByCollege);
    const majorsByProgram = @json($catalogMajorsByProgram);

    const setToggleLabel = (toggleEl, labelText) => {
        const labelEl = toggleEl.querySelector('.custom-filter-toggle-label');
        if (labelEl) {
            labelEl.textContent = labelText;
        }
    };

    const closeAllMenus = (exceptMenu = null) => {
        [collegeMenu, programMenu, majorMenu].forEach((menuEl) => {
            if (menuEl !== exceptMenu) {
                menuEl.classList.remove('open');
            }
        });
        [collegeToggle, programToggle, majorToggle].forEach((toggleEl) => {
            if (!(exceptMenu && toggleEl.nextElementSibling === exceptMenu)) {
                toggleEl.setAttribute('aria-expanded', 'false');
            }
        });
    };

    const toggleMenu = (toggleEl, menuEl) => {
        const willOpen = !menuEl.classList.contains('open');
        closeAllMenus();
        if (willOpen && !toggleEl.disabled) {
            menuEl.classList.add('open');
            toggleEl.setAttribute('aria-expanded', 'true');
        }
    };

    const renderMenu = (menuEl, items, selectedValue, onSelect) => {
        menuEl.innerHTML = '';
        items.forEach((item) => {
            const optionBtn = document.createElement('button');
            optionBtn.type = 'button';
            optionBtn.className = 'custom-filter-option' + (selectedValue === item.value ? ' active' : '');
            optionBtn.textContent = item.label;
            optionBtn.dataset.value = item.value;
            optionBtn.addEventListener('click', () => {
                onSelect(item.value);
                closeAllMenus();
            });
            menuEl.appendChild(optionBtn);
        });
    };

    const setMajorLayout = (showMajor) => {
        majorWrap.style.display = showMajor ? '' : 'none';
    };

    let activeRequestController = null;

    const buildFilterUrl = () => {
        const params = new URLSearchParams(new FormData(filterForm));
        Array.from(params.keys()).forEach((key) => {
            if (!params.get(key)) {
                params.delete(key);
            }
        });

        const queryString = params.toString();
        return queryString ? `${filterForm.action}?${queryString}` : filterForm.action;
    };

    const bindPaginationLinks = () => {
        recordsSection.querySelectorAll('.pagination a').forEach((linkEl) => {
            linkEl.addEventListener('click', (event) => {
                event.preventDefault();
                const nextUrl = linkEl.getAttribute('href');
                if (!nextUrl) {
                    return;
                }
                updateRecords(nextUrl);
            });
        });
    };

    const updateRecords = async (targetUrl = null) => {
        const fetchUrl = targetUrl || buildFilterUrl();

        if (activeRequestController) {
            activeRequestController.abort();
        }
        activeRequestController = new AbortController();

        recordsSection.classList.add('records-loading');

        try {
            const response = await fetch(fetchUrl, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                },
                signal: activeRequestController.signal,
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }

            const html = await response.text();
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const nextRecordsSection = doc.getElementById('recordsSection');

            if (!nextRecordsSection) {
                window.location.href = fetchUrl;
                return;
            }

            recordsSection.innerHTML = nextRecordsSection.innerHTML;
            bindPaginationLinks();
            window.history.replaceState({}, '', fetchUrl);
        } catch (error) {
            if (error.name !== 'AbortError') {
                window.location.href = fetchUrl;
            }
        } finally {
            recordsSection.classList.remove('records-loading');
        }
    };

    const getCollegeLabel = (collegeCode) => {
        if (!collegeCode) {
            return 'All Colleges';
        }
        const collegeName = colleges[collegeCode] || collegeCode;
        return `${collegeName} (${collegeCode})`;
    };

    const getProgramOptions = () => {
        const collegeCode = collegeInput.value;
        if (!collegeCode) {
            return [];
        }
        return Array.isArray(programsByCollege[collegeCode]) ? programsByCollege[collegeCode] : [];
    };

    const getMajorOptions = () => {
        const programName = programInput.value;
        if (!programName) {
            return [];
        }
        return Array.isArray(majorsByProgram[programName]) ? majorsByProgram[programName] : [];
    };

    const renderCollegeControl = () => {
        const collegeItems = [{ value: '', label: 'All Colleges' }];
        Object.entries(colleges).forEach(([code, name]) => {
            collegeItems.push({ value: code, label: `${name} (${code})` });
        });

        setToggleLabel(collegeToggle, getCollegeLabel(collegeInput.value));
        renderMenu(collegeMenu, collegeItems, collegeInput.value, (value) => {
            collegeInput.value = value;
            programInput.value = '';
            majorInput.value = '';
            renderCollegeControl();
            renderProgramControl();
            updateRecords();
        });
    };

    const renderProgramControl = () => {
        const programs = getProgramOptions();

        if (!collegeInput.value) {
            programToggle.disabled = true;
            setToggleLabel(programToggle, 'Select college first');
            programMenu.innerHTML = '';
            majorInput.value = '';
            renderMajorControl();
            return;
        }

        if (!programs.includes(programInput.value)) {
            programInput.value = '';
        }

        if (programs.length === 1) {
            programInput.value = programs[0];
            programToggle.disabled = true;
            setToggleLabel(programToggle, programs[0]);
            programMenu.innerHTML = '';
            renderMajorControl();
            return;
        }

        programToggle.disabled = false;
        setToggleLabel(programToggle, programInput.value || 'All Programs');

        const programItems = [{ value: '', label: 'All Programs' }, ...programs.map((programName) => ({
            value: programName,
            label: programName,
        }))];

        renderMenu(programMenu, programItems, programInput.value, (value) => {
            programInput.value = value;
            majorInput.value = '';
            renderProgramControl();
            updateRecords();
        });

        renderMajorControl();
    };

    const renderMajorControl = () => {
        const majors = getMajorOptions();

        if (!programInput.value || majors.length === 0) {
            majorInput.value = '';
            majorToggle.disabled = true;
            setToggleLabel(majorToggle, 'All Majors');
            majorMenu.innerHTML = '';
            setMajorLayout(false);
            return;
        }

        if (!majors.includes(majorInput.value)) {
            majorInput.value = '';
        }

        setMajorLayout(true);

        if (majors.length === 1) {
            majorInput.value = majors[0];
            majorToggle.disabled = true;
            setToggleLabel(majorToggle, majors[0]);
            majorMenu.innerHTML = '';
            return;
        }

        majorToggle.disabled = false;
        setToggleLabel(majorToggle, majorInput.value || 'All Majors');

        const majorItems = [{ value: '', label: 'All Majors' }, ...majors.map((majorName) => ({
            value: majorName,
            label: majorName,
        }))];

        renderMenu(majorMenu, majorItems, majorInput.value, (value) => {
            majorInput.value = value;
            renderMajorControl();
            updateRecords();
        });
    };

    filterForm.addEventListener('submit', (event) => {
        event.preventDefault();
        updateRecords();
    });

    collegeToggle.addEventListener('click', (event) => {
        event.stopPropagation();
        toggleMenu(collegeToggle, collegeMenu);
    });

    programToggle.addEventListener('click', (event) => {
        event.stopPropagation();
        toggleMenu(programToggle, programMenu);
    });

    majorToggle.addEventListener('click', (event) => {
        event.stopPropagation();
        toggleMenu(majorToggle, majorMenu);
    });

    document.addEventListener('click', (event) => {
        if (!event.target.closest('.custom-filter-select')) {
            closeAllMenus();
        }
    });

    let searchDebounceTimer;
    let lastSearchValue = searchInput.value.trim();

    searchInput.addEventListener('input', () => {
        clearTimeout(searchDebounceTimer);
        searchDebounceTimer = setTimeout(() => {
            const currentValue = searchInput.value.trim();
            if (currentValue === lastSearchValue) {
                return;
            }
            lastSearchValue = currentValue;
            updateRecords();
        }, 320);
    });

    resetFiltersBtn.addEventListener('click', (event) => {
        event.preventDefault();
        searchInput.value = '';
        lastSearchValue = '';
        collegeInput.value = '';
        programInput.value = '';
        majorInput.value = '';
        renderCollegeControl();
        renderProgramControl();
        updateRecords(filterForm.action);
    });

    renderCollegeControl();
    renderProgramControl();
    bindPaginationLinks();
});
</script>
@endif
@endsection