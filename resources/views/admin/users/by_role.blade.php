@extends('layouts.admin')

@section('content')
<style>
    .role-shell {
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
        padding: 1.2rem 1.1rem;
        height: 100%;
    }
    .metric-label {
        font-size: .75rem;
        text-transform: uppercase;
        letter-spacing: .05em;
        color: rgba(2, 8, 20, .55);
        margin-bottom: .5rem;
    }
    .metric-value {
        font-size: 2rem;
        font-weight: 800;
        color: #166534;
        line-height: 1;
    }
    .metric-subtitle {
        font-size: .8rem;
        color: rgba(2, 8, 20, .65);
        margin-top: .25rem;
    }
    .section-card {
        border: 1px solid rgba(2, 8, 20, .08);
        border-radius: 1rem;
        background: #fff;
        box-shadow: 0 8px 20px rgba(2, 8, 20, .05);
        overflow: hidden;
    }
    .section-header {
        border-bottom: 1px solid rgba(2, 8, 20, .08);
        background: #fff;
        padding: 1rem 1.25rem;
    }
    .table thead th {
        font-size: .75rem;
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
        padding: .8rem 1rem;
    }
    .table tbody tr {
        transition: background-color .15s ease;
    }
    .table tbody tr:hover {
        background-color: rgba(248, 250, 252, .5);
    }
    .avatar-item {
        width: 38px;
        height: 38px;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid rgba(22, 101, 52, .22);
        background: rgba(22, 101, 52, .12);
        color: #166534;
        font-weight: 700;
        font-size: .85rem;
        flex-shrink: 0;
        margin-right: .75rem;
    }
    .avatar-photo {
        width: 38px;
        height: 38px;
        border-radius: 999px;
        object-fit: cover;
        border: 1px solid rgba(22, 101, 52, .22);
        margin-right: .75rem;
        flex-shrink: 0;
        background: #f8fafc;
    }
    .admin-filter-card {
        border: 1px solid rgba(2, 8, 20, .08);
        border-radius: 1rem;
        background: #fff;
        box-shadow: 0 8px 20px rgba(2, 8, 20, .05);
    }
    .admin-card-header {
        padding: .85rem 1rem;
        border-bottom: 1px solid rgba(2, 8, 20, .08);
        background: #fff;
    }
    .filter-desktop-label-row {
        display: grid;
        grid-template-columns: 3fr 2fr 3fr 2fr 2fr;
        gap: .5rem;
        font-size: .72rem;
        text-transform: uppercase;
        letter-spacing: .05em;
        color: rgba(2, 8, 20, .55);
        font-weight: 700;
        margin-bottom: .35rem;
    }
    .filter-desktop-controls {
        display: grid;
        grid-template-columns: 3fr 2fr 3fr 2fr 2fr;
        gap: .5rem;
        align-items: end;
    }
    .filter-desktop-label-row.no-major,
    .filter-desktop-controls.no-major {
        grid-template-columns: 3fr 2fr 3fr 2fr;
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
    .custom-filter-toggle:disabled {
        background: #f8f9fa;
        color: #6c757d;
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
    @media (max-width: 767.98px) {
        .role-shell { padding: .95rem; }
        .metric-value { font-size: 1.4rem; }
        .metric-tile { padding: 1rem; }
        .filter-desktop-label-row,
        .filter-desktop-controls {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="role-shell">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <div class="text-uppercase small muted fw-semibold">User Management</div>
            <h1 class="h4 mb-1">{{ $roleTitle }}</h1>
            <div class="muted small">Manage {{ strtolower($roleTitle) }} and their information</div>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary rounded-pill px-3">
                All Users
            </a>
            @if($role === 'landlord')
                <a href="{{ route('admin.permits.index') }}" class="btn btn-outline-secondary rounded-pill px-3">
                    Permit Approvals
                </a>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($role === 'student')
        <div class="admin-filter-card mb-4">
            <div class="admin-card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div class="fw-semibold"><i class="bi bi-funnel me-1"></i> Filters</div>
                <div class="small text-muted">Academic catalog mapping</div>
            </div>

            <div class="p-3">
            <form method="GET" action="{{ route('admin.users.students') }}" id="academicFilterForm">
                @php
                    $showMajorFilter = ($selectedProgram !== '' && count($majorOptions) > 0);
                @endphp
                <div class="filter-desktop-label-row{{ $showMajorFilter ? '' : ' no-major' }}" id="filterLabelRow">
                    <span>Name Search</span>
                    <span>College</span>
                    <span>Program</span>
                    <span id="majorFilterLabel" style="{{ $showMajorFilter ? '' : 'display:none;' }}">Major (Optional)</span>
                    <span>Reset</span>
                </div>

                <div class="filter-desktop-controls{{ $showMajorFilter ? '' : ' no-major' }}" id="filterControlRow">
                    <div>
                        <div class="input-group">
                            <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                            <input
                                type="text"
                                class="form-control"
                                name="search"
                                value="{{ $selectedNameSearch ?? '' }}"
                                placeholder="Student name"
                            >
                        </div>
                    </div>

                    <div>
                        <input type="hidden" id="collegeFilter" name="college" value="{{ $selectedCollege }}">
                        <div class="custom-filter-select">
                            <button type="button" class="custom-filter-toggle" id="collegeToggle" aria-haspopup="listbox" aria-expanded="false">
                                <span class="custom-filter-toggle-label">All Colleges</span>
                                <i class="bi bi-chevron-down"></i>
                            </button>
                            <div class="custom-filter-menu" id="collegeMenu" role="listbox" aria-label="Filter by college"></div>
                        </div>
                    </div>

                    <div>
                        <input type="hidden" id="programFilter" name="program" value="{{ $selectedProgram }}">
                        <div class="custom-filter-select">
                            <button type="button" class="custom-filter-toggle" id="programToggle" aria-haspopup="listbox" aria-expanded="false">
                                <span class="custom-filter-toggle-label">Select college first</span>
                                <i class="bi bi-chevron-down"></i>
                            </button>
                            <div class="custom-filter-menu" id="programMenu" role="listbox" aria-label="Filter by program"></div>
                        </div>
                    </div>

                    <div id="majorFilterWrap" style="{{ ($selectedProgram !== '' && count($majorOptions) > 0) ? '' : 'display:none;' }}">
                        <input type="hidden" id="majorFilter" name="major" value="{{ $selectedMajor }}">
                        <div class="custom-filter-select">
                            <button type="button" class="custom-filter-toggle" id="majorToggle" aria-haspopup="listbox" aria-expanded="false">
                                <span class="custom-filter-toggle-label">All Majors</span>
                                <i class="bi bi-chevron-down"></i>
                            </button>
                            <div class="custom-filter-menu" id="majorMenu" role="listbox" aria-label="Filter by major"></div>
                        </div>
                    </div>

                    <div class="d-flex gap-2 flex-wrap">
                        <a href="{{ route('admin.users.students') }}" class="btn btn-outline-secondary" id="resetFiltersBtn">Reset</a>
                    </div>
                </div>
            </form>
            </div>
        </div>
    @endif

    <!-- Statistics -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-md-6 col-lg-4">
            <div class="metric-tile">
                <div class="metric-label"><i class="bi bi-{{ $role === 'landlord' ? 'building' : 'mortarboard' }} me-1"></i>Total</div>
                <div class="metric-value">{{ $users->total() }}</div>
                <div class="metric-subtitle">{{ $roleTitle }} registered</div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-lg-4">
            <div class="metric-tile">
                <div class="metric-label"><i class="bi bi-calendar me-1"></i>This Week</div>
                <div class="metric-value">{{ $users->where('created_at', '>=', now()->startOfWeek())->count() }}</div>
                <div class="metric-subtitle">New registrations</div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-lg-4">
            <div class="metric-tile">
                <div class="metric-label"><i class="bi bi-calendar2 me-1"></i>This Month</div>
                <div class="metric-value">{{ $users->where('created_at', '>=', now()->startOfMonth())->count() }}</div>
                <div class="metric-subtitle">Monthly registrations</div>
            </div>
        </div>
    </div>

    <!-- Users Table -->
    <div class="section-card" id="recordsSection">
        <div class="section-header d-flex justify-content-between align-items-center">
            <div class="fw-semibold"><i class="bi bi-{{ $role === 'landlord' ? 'building' : 'person-lines-fill' }} me-2"></i>{{ $roleTitle }} List</div>
            <span class="badge text-bg-secondary">{{ $users->total() }} {{ strtolower($roleTitle) }}</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
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
                                        @if($role === 'student')
                                            <div class="text-muted small" style="font-size: .75rem;">{{ $user->email }}</div>
                                        @else
                                            <div class="text-muted small" style="font-size: .75rem;">{{ $user->name }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            @if($role !== 'student')
                                <td class="small">{{ $user->email }}</td>
                            @endif
                            <td class="small muted">{{ $user->contact_number ?: '—' }}</td>
                            @if($role === 'student')
                                <td class="small muted">
                                    @if(filled($user->college) && isset($collegeOptions[$user->college]))
                                        {{ $collegeOptions[$user->college] }} ({{ $user->college }})
                                    @else
                                        {{ $user->college ?: '—' }}
                                    @endif
                                </td>
                                <td>
                                    <div class="small fw-semibold">{{ $user->program ?: '—' }}</div>
                                    @if(filled($user->major))
                                        <div class="text-muted small" style="font-size: .75rem;">{{ $user->major }}</div>
                                    @endif
                                </td>
                            @endif
                            @if($role === 'landlord')
                                <td class="small">{{ $user->boarding_house_name ?: '—' }}</td>
                                <td>
                                    <span class="badge text-bg-success">{{ $user->current_tenants ?? 0 }}</span>
                                </td>
                                <td>
                                    <span class="badge text-bg-warning">{{ $user->onboarding_tenants ?? 0 }}</span>
                                </td>
                                <td class="small muted">{{ $user->occupied_rooms ?? 0 }}/{{ $user->total_rooms ?? 0 }}</td>
                                <td>
                                    @if(isset($user->total_rooms) && $user->total_rooms > 0)
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="progress grow" style="height: 5px; width: 60px;">
                                                <div class="progress-bar bg-{{ ($user->occupied_rooms / $user->total_rooms) > 0.8 ? 'danger' : (($user->occupied_rooms / $user->total_rooms) > 0.5 ? 'warning' : 'success') }}"
                                                     style="width: {{ ($user->occupied_rooms / $user->total_rooms) * 100 }}%"></div>
                                            </div>
                                            <small class="text-muted" style="min-width: 38px;">{{ round(($user->occupied_rooms / $user->total_rooms) * 100) }}%</small>
                                        </div>
                                    @else
                                        <small class="text-muted">—</small>
                                    @endif
                                </td>
                            @endif
                            <td class="small muted">{{ $user->created_at->format('M d, Y') }}</td>
                            <td class="pe-3">
                                @if($role === 'student')
                                    <a href="{{ route('admin.users.students.show', $user) }}" class="btn btn-sm btn-outline-secondary" title="View details">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                @elseif($role === 'landlord')
                                    <a href="{{ route('admin.users.landlords.show', $user) }}" class="btn btn-sm btn-outline-secondary" title="View details">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $role === 'landlord' ? '10' : ($role === 'student' ? '6' : '5') }}" class="text-center text-muted py-5">
                                <div><i class="bi bi-inbox" style="font-size: 2.5rem; opacity: 0.3;"></i></div>
                                <p class="mb-0 mt-2">No {{ strtolower($roleTitle) }} found.</p>
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
    const majorLabel = document.getElementById('majorFilterLabel');
    const filterLabelRow = document.getElementById('filterLabelRow');
    const filterControlRow = document.getElementById('filterControlRow');

    if (!filterForm || !searchInput || !recordsSection || !resetFiltersBtn || !collegeInput || !programInput || !majorInput || !collegeToggle || !programToggle || !majorToggle || !collegeMenu || !programMenu || !majorMenu || !majorWrap || !majorLabel || !filterLabelRow || !filterControlRow) {
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
        majorLabel.style.display = showMajor ? '' : 'none';
        majorWrap.style.display = showMajor ? '' : 'none';
        filterLabelRow.classList.toggle('no-major', !showMajor);
        filterControlRow.classList.toggle('no-major', !showMajor);
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