<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin Panel - Boarding House System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --brand:#14532d;
            --brand-dark:#166534;
            --mint:#a7f3d0;
            --gold:#f59e0b;
            --ink:#0f172a;
            --nav-h:68px;
            --sidebar-w:264px;
            --surface:#ffffff;
            --shell:#f8fafc;
            --line:#e5e7eb;
            --muted:#6b7280;
        }
        body {
            font-family:'Manrope', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height:100vh;
            overflow-x:hidden;
            background: var(--shell);
        }

        .navbar-glass {
            background: #ffffff !important;
            border-bottom: 1px solid var(--line);
            z-index: 1040;
        }
        .dash-shell { padding-top:5rem; padding-bottom:3rem; }

        .navbar-brand {
            color: #111827 !important;
            letter-spacing: .01em;
            font-weight: 700;
        }
        .brand-mark {
            width: 34px;
            height: 34px;
            object-fit: contain;
            display: inline-block;
        }
        .top-meta {
            color: var(--muted);
            font-size: .8rem;
            line-height: 1.35;
        }
        .user-chip {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            border-radius: 999px;
            padding: .32rem .7rem;
            background: #f8fafc;
            border: 1px solid var(--line);
            color: #334155;
            font-size: .85rem;
        }
        .icon-btn {
            width: 34px;
            height: 34px;
            border-radius: 999px;
            border: 1px solid var(--line);
            background: #fff;
            color: #475569;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
        }
        .icon-btn:hover {
            background: rgba(167,243,208,.22);
            color: #14532d;
        }
        .icon-btn-wrap {
            position: relative;
            display: inline-flex;
        }
        .icon-btn-badge {
            position: absolute;
            top: -6px;
            right: -6px;
            min-width: 18px;
            height: 18px;
            border-radius: 999px;
            padding: 0 5px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: .66rem;
            font-weight: 700;
            color: #fff;
            background: #dc2626;
            border: 2px solid #fff;
            line-height: 1;
        }
        .btn-logout {
            color: #dc2626;
            border-color: #fecaca;
            background: #fff5f5;
        }
        .btn-logout:hover {
            color: #b91c1c;
            border-color: #fca5a5;
            background: #ffe4e6;
        }

        .btn-brand { background:var(--brand); border-color:var(--brand); color:#fff; }
        .btn-brand:hover { background:var(--brand-dark); border-color:var(--brand-dark); color:#fff; }
        .badge-brand { background: rgba(22,101,52,.10); border:1px solid rgba(22,101,52,.18); color: var(--brand); }

        .sidepanel {
            background: #fff;
            border-right: 1px solid var(--line);
            border-radius: 0;
            display: flex;
            flex-direction: column;
            min-height: 100%;
        }
        .sidebar-head {
            border: 1px solid var(--line);
            background: #fbfdff;
            border-radius: .8rem;
        }

        .main-col .card {
            background: #fff;
            border: 1px solid rgba(2,8,20,.08);
        }
        .main-col .card-header {
            background: rgba(2,8,20,.02);
            border-bottom-color: rgba(2,8,20,.08);
        }

        .main-col .bg-white {
            background-color: #fff !important;
        }

        .sidepanel .list-group-item {
            background: transparent;
            border: 1px solid transparent;
            color: #334155;
            display: flex;
            align-items: center;
            gap: .6rem;
            padding: .74rem .9rem;
            width:100%;
            text-align: left;
            border-radius: .7rem;
            transition: all .18s ease;
        }
        .sidepanel .list-group-item:hover {
            background: rgba(167,243,208,.22);
            border-color: rgba(20,83,45,.20);
        }
        .sidepanel .nav-section {
            padding: .9rem .9rem .35rem;
            font-size: .68rem;
            letter-spacing: .08em;
            text-transform: uppercase;
            color: #94a3b8;
        }
        .sidepanel .nav-toggle { cursor: pointer; }
        .sidepanel .nav-chevron { margin-left: auto; transition: transform .18s ease; opacity: .75; }
        .sidepanel .nav-toggle[aria-expanded="true"] .nav-chevron { transform: rotate(180deg); }
        .sidepanel .list-group-item.sub-item {
            padding: .6rem .9rem .6rem 2.2rem;
            font-size: .88rem;
        }
        .sidepanel .list-group-item i { color: #64748b; }
        .sidepanel .list-group-item.active {
            background: rgba(167,243,208,.32);
            border-color: rgba(20,83,45,.28);
            color: #14532d;
            font-weight: 700;
        }
        .sidepanel .list-group-item.active i { color: #166534; }
        .sidepanel .panel-title { color: #64748b; }
        .sidepanel .list-group {
            display: flex;
            flex-direction: column;
            flex: 1 1 auto;
            min-height: 0;
        }
        .sidebar-footer {
            border-top: 1px solid var(--line);
            margin-top: auto;
            padding-top: .9rem;
        }
        .sidebar-logout {
            width: 100%;
            justify-content: flex-start;
        }

        @media (max-width: 992px){
            .dash-shell { padding-top:4.2rem; }
            .main-col { padding: .9rem; }
        }
        @media (min-width: 992px) {
            .navbar-glass {
                left: var(--sidebar-w);
                width: calc(100% - var(--sidebar-w));
            }
            .dash-shell {
                padding-top: var(--nav-h);
                padding-left: 0;
            }
            .sidepanel-col {
                position: fixed;
                top: 0;
                bottom: 0;
                left: 0;
                width: var(--sidebar-w);
                height: auto;
                z-index: 3;
                padding-left: 0;
                padding-right: 0;
                overflow-y: auto;
                overflow-x: hidden;
                scrollbar-width: thin;
            }
            .sidepanel {
                position: static;
                min-height: 100%;
                height: auto;
                overflow: visible;
                border-left: 0;
                border-bottom: 0;
                box-shadow: none;
                padding-top: .75rem;
            }
            .main-col {
                margin-left: var(--sidebar-w);
                width: calc(100% - var(--sidebar-w));
                max-width: calc(100% - var(--sidebar-w));
                flex: 0 0 auto;
                padding: 1.25rem;
            }
        }
    </style>
</head>
<body>
    @php
        $routeName = \Illuminate\Support\Facades\Route::currentRouteName();
        $usersOpen = is_string($routeName) && str_starts_with($routeName, 'admin.users.');

        $pendingApprovalsCount = \App\Models\Property::where('approval_status', 'pending')->count();
        $notificationsCount = \Illuminate\Support\Facades\Schema::hasTable('notifications')
            ? \Illuminate\Notifications\DatabaseNotification::query()
                ->where('notifiable_type', get_class(Auth::user()))
                ->where('notifiable_id', Auth::id())
                ->whereNull('read_at')
                ->count()
            : 0;
    @endphp

    <nav class="navbar navbar-expand-lg navbar-light navbar-glass fixed-top">
        <div class="container-fluid px-3 px-lg-4">
            <a class="navbar-brand d-flex align-items-center gap-2" href="{{ route('admin.dashboard') }}">
                <img src="{{ asset('images/minsu3.png') }}" alt="MINSU" class="brand-mark" />
                <span>Admin Portal</span>
            </a>
            <div class="d-flex align-items-center gap-3 ms-auto">
                <div class="top-meta d-none d-lg-block text-end">
                    <div>Workspace</div>
                    <div class="fw-semibold">System Administration</div>
                </div>
                <span class="icon-btn-wrap d-none d-md-inline-flex">
                    <a class="icon-btn" href="{{ route('notifications.index') }}" title="Notifications">
                        <i class="bi bi-bell"></i>
                    </a>
                    @if($notificationsCount > 0)
                        <span class="icon-btn-badge">{{ $notificationsCount > 99 ? '99+' : $notificationsCount }}</span>
                    @endif
                </span>
                <span class="user-chip d-none d-lg-inline-flex">
                    <i class="bi bi-person-circle"></i>
                    <span>{{ Auth::user()->full_name }}</span>
                </span>
            </div>
        </div>
    </nav>

    <main class="dash-shell container-fluid ps-0 pe-0">
        <div class="row g-0">
            <div class="col-12 col-lg-3 col-xl-2 ps-0 sidepanel-col">
                <div class="p-3 sidepanel">
                    <div class="d-flex align-items-center gap-2 px-2 py-2 mb-2 sidebar-head">
                        @if(!empty(Auth::user()->profile_image_path))
                            <img src="{{ asset('storage/' . Auth::user()->profile_image_path) }}" alt="Profile photo" class="rounded-3 border" style="width:40px;height:40px;object-fit:cover;border-color:rgba(22,101,52,.22)!important;">
                        @else
                            <div class="rounded-3 d-inline-flex align-items-center justify-content-center" style="width:40px;height:40px;background:rgba(22,101,52,.10);border:1px solid rgba(22,101,52,.20);">
                                <i class="bi bi-person-badge" style="color:#14532d;"></i>
                            </div>
                        @endif
                        <div class="min-w-0">
                            <div class="fw-semibold text-truncate">{{ Auth::user()->full_name }}</div>
                            <div class="small panel-title">Admin Account</div>
                        </div>
                        <span class="ms-auto badge rounded-pill badge-brand">Admin</span>
                    </div>

                    <div class="list-group list-group-flush rounded-3 overflow-hidden">
                        <div class="nav-section">Main</div>
                        <a @class(['list-group-item', 'active' => $routeName === 'admin.dashboard']) href="{{ route('admin.dashboard') }}">
                            <i class="bi bi-speedometer2"></i>
                            <span>Dashboard</span>
                        </a>

                        <div class="nav-section">Management</div>
                        <button class="list-group-item nav-toggle" type="button" data-bs-toggle="collapse" data-bs-target="#navUsers" aria-expanded="{{ $usersOpen ? 'true' : 'false' }}" aria-controls="navUsers">
                            <i class="bi bi-people"></i>
                            <span>Users</span>
                            <i class="bi bi-chevron-down nav-chevron" style="font-size:.9rem;"></i>
                        </button>
                        <div class="collapse {{ $usersOpen ? 'show' : '' }}" id="navUsers">
                            <a @class(['list-group-item sub-item', 'active' => $routeName === 'admin.users.index']) href="{{ route('admin.users.index') }}">
                                <i class="bi bi-people"></i> All Users
                            </a>
                            <a @class(['list-group-item sub-item', 'active' => $routeName === 'admin.users.students']) href="{{ route('admin.users.students') }}">
                                <i class="bi bi-mortarboard"></i> Students
                            </a>
                            <a @class(['list-group-item sub-item', 'active' => $routeName === 'admin.users.landlords']) href="{{ route('admin.users.landlords') }}">
                                <i class="bi bi-building"></i> Landlords
                            </a>
                        </div>
                        <a @class(['list-group-item', 'active' => is_string($routeName)
                            && str_starts_with($routeName, 'admin.properties.')
                            && !str_starts_with($routeName, 'admin.properties.pending')
                            && !str_starts_with($routeName, 'admin.properties.approve')
                            && !str_starts_with($routeName, 'admin.properties.reject')
                        ]) href="{{ route('admin.properties.index') }}">
                            <i class="bi bi-buildings"></i>
                            <span>Properties</span>
                        </a>
                        <a @class(['list-group-item', 'active' => is_string($routeName)
                            && (
                                str_starts_with($routeName, 'admin.properties.pending')
                                || str_starts_with($routeName, 'admin.properties.approve')
                                || str_starts_with($routeName, 'admin.properties.reject')
                            )
                        ]) href="{{ route('admin.properties.pending') }}">
                            <i class="bi bi-check2-circle"></i>
                            <span>Approvals</span>
                            @if($pendingApprovalsCount > 0)
                                <span class="badge rounded-pill text-bg-danger ms-auto">{{ $pendingApprovalsCount }}</span>
                            @endif
                        </a>
                        <a @class(['list-group-item', 'active' => $routeName === 'admin.bookings.index']) href="{{ route('admin.bookings.index') }}">
                            <i class="bi bi-journal-check"></i>
                            <span>Bookings</span>
                        </a>
                        <a @class(['list-group-item', 'active' => is_string($routeName) && str_starts_with($routeName, 'admin.onboardings.')]) href="{{ route('admin.onboardings.index') }}">
                            <i class="bi bi-clipboard-check"></i>
                            <span>Onboardings</span>
                        </a>
                        <a @class(['list-group-item', 'active' => is_string($routeName) && str_starts_with($routeName, 'admin.reports.')]) href="{{ route('admin.reports.index') }}">
                            <i class="bi bi-flag"></i>
                            <span>Reports</span>
                        </a>

                        <div class="nav-section">Activity</div>
                        <a @class(['list-group-item', 'active' => $routeName === 'notifications.index']) href="{{ route('notifications.index') }}">
                            <i class="bi bi-bell"></i>
                            <span>Notifications</span>
                            @if($notificationsCount > 0)
                                <span class="badge rounded-pill text-bg-danger ms-auto">{{ $notificationsCount }}</span>
                            @endif
                        </a>
                    </div>

                    <div class="sidebar-footer px-1 mt-3">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-logout rounded-pill sidebar-logout">
                                <i class="bi bi-box-arrow-right me-1"></i> Logout
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-9 col-xl-10 main-col">
                @yield('content')
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <x-toast />
    <x-chatbot />
    @stack('scripts')
</body>
</html>