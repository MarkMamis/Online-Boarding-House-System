<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Boarding House System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --brand: #166534;      /* deep green */
            --brand-2: #15803d;    /* medium green */
            --brand-alt: #f59e0b;  /* gold */
            --nav-h:72px;
            --sidebar-w:300px;
        }
        body { font-family:'Inter', system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif; min-height:100vh; overflow-x:hidden; background:#fff; }

        .navbar-glass { background:#fff!important; border-bottom:1px solid rgba(2,8,20,.08); }
        .dash-shell { padding-top:5rem; padding-bottom:4rem; }
        .glass-card { background:#fff; border:1px solid rgba(2,8,20,.08); box-shadow:0 10px 26px rgba(2,8,20,.08); }

        .text-brand { color: var(--brand) !important; }
        .btn-brand { background: var(--brand); border-color: var(--brand); color: #fff; }
        .btn-brand:hover { background: var(--brand-2); border-color: var(--brand-2); color: #fff; }
        .badge-brand { background: rgba(22,101,52,.10); border:1px solid rgba(22,101,52,.18); color: var(--brand); }

        .sidepanel.glass-card { background: rgba(255,255,255,1); backdrop-filter:none; }
        .sidepanel .list-group-item {
            background: rgba(255,255,255,1);
            border-color: rgba(2,8,20,.08);
            color: rgba(2,8,20,.82);
            display:flex;
            align-items:center;
            gap:.6rem;
            padding: .85rem 1rem;
            width:100%;
        }
        .sidepanel .nav-section {
            padding: .65rem 1rem .35rem;
            font-size: .72rem;
            letter-spacing: .08em;
            text-transform: uppercase;
            color: rgba(2,8,20,.55);
        }
        .sidepanel .list-group-item i { color: rgba(2,8,20,.65); }
        .sidepanel .list-group-item.active {
            background: rgba(22,101,52,.10);
            border-color: rgba(22,101,52,.22);
            color: rgba(2,8,20,.90);
            font-weight: 700;
        }
        .sidepanel .list-group-item.active i { color: var(--brand); }
        .sidepanel .panel-title { color: rgba(2,8,20,.70); }

        @media (max-width: 992px){ .dash-shell { padding-top:4.2rem; } }
        @media (min-width: 992px) {
            .dash-shell { padding-top: calc(var(--nav-h) + 1.25rem); }
            .sidepanel-col {
                position: fixed;
                top: calc(var(--nav-h) + 1.25rem);
                left: 0;
                width: var(--sidebar-w);
                height: calc(100vh - var(--nav-h) - 2.5rem);
                z-index: 3;
            }
            .sidepanel { position: static; height: 100%; overflow:auto; direction: rtl; scrollbar-width: thin; }
            .sidepanel > * { direction: ltr; }
            .main-col {
                margin-left: calc(var(--sidebar-w) + 1.5rem);
                width: calc(100% - var(--sidebar-w) - 1.5rem);
                max-width: calc(100% - var(--sidebar-w) - 1.5rem);
                flex: 0 0 auto;
            }
        }
    </style>
</head>
<body>
    @php
        $routeName = \Illuminate\Support\Facades\Route::currentRouteName();

        $pendingApprovalsCount = \App\Models\Property::where('approval_status', 'pending')->count();
        $notificationsCount = \Illuminate\Support\Facades\Schema::hasTable('notifications')
            ? \Illuminate\Notifications\DatabaseNotification::query()
                ->where('notifiable_type', get_class(Auth::user()))
                ->where('notifiable_id', Auth::id())
                ->whereNull('read_at')
                ->count()
            : 0;
    @endphp

    <nav class="navbar navbar-expand-lg navbar-light navbar-glass fixed-top" style="min-height: var(--nav-h);">
        <div class="container-fluid px-3 px-lg-4">
            <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="{{ route('admin.dashboard') }}">
                <img src="{{ asset('images/minsu3.png') }}" alt="MINSU" style="width:32px;height:32px;object-fit:contain;" />
                <span>Admin Panel</span>
            </a>
            <div class="d-flex align-items-center gap-3 ms-auto">
                <a href="{{ route('notifications.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill position-relative" title="Notifications">
                    <i class="bi bi-bell"></i>
                    @if($notificationsCount > 0)
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill text-bg-danger">{{ $notificationsCount }}</span>
                    @endif
                </a>

                <div class="small text-muted d-none d-lg-block">Signed in as</div>
                <div class="small text-dark d-none d-lg-block">{{ Auth::user()->full_name }}</div>
                <form method="POST" action="{{ route('logout') }}" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-dark rounded-pill px-3">Logout</button>
                </form>
            </div>
        </div>
    </nav>

    <main class="dash-shell container-fluid ps-0 pe-3 pe-lg-4">
        <div class="row g-4">
            <div class="col-12 col-lg-3 col-xl-2 ps-0 sidepanel-col">
                <div class="glass-card rounded-4 p-3 sidepanel">
                    <div class="d-flex align-items-center gap-2 px-2 pt-2 pb-2">
                        <img src="{{ asset('images/minsu3.png') }}" alt="MINSU" style="width:28px;height:28px;object-fit:contain;" />
                        <div class="fw-bold text-brand">MINSU</div>
                        <span class="ms-auto badge rounded-pill badge-brand">Admin</span>
                    </div>
                    <div class="d-flex align-items-center gap-2 px-2 pt-2 pb-3">
                        @if(!empty(Auth::user()->profile_image_path))
                            <img src="{{ asset('storage/' . Auth::user()->profile_image_path) }}" alt="Profile photo" class="rounded-3 border" style="width:40px;height:40px;object-fit:cover;border-color:rgba(22,101,52,.22)!important;">
                        @else
                            <div class="rounded-3 d-inline-flex align-items-center justify-content-center" style="width:40px;height:40px;background:rgba(22,101,52,.10);border:1px solid rgba(22,101,52,.20);">
                                <i class="bi bi-person-badge" style="color: var(--brand);"></i>
                            </div>
                        @endif
                        <div class="min-w-0">
                            <div class="fw-semibold text-truncate">{{ Auth::user()->full_name }}</div>
                            <div class="small panel-title">Admin</div>
                        </div>
                    </div>

                    <div class="list-group list-group-flush rounded-3 overflow-hidden">
                        <div class="nav-section">Overview</div>
                        <a @class(['list-group-item', 'active' => $routeName === 'admin.dashboard']) href="{{ route('admin.dashboard') }}">
                            <i class="bi bi-speedometer2"></i>
                            <span>Dashboard</span>
                        </a>

                        <div class="nav-section">Management</div>
                        <a @class(['list-group-item', 'active' => is_string($routeName) && str_starts_with($routeName, 'admin.users.')]) href="{{ route('admin.users.index') }}">
                            <i class="bi bi-people"></i>
                            <span>Users</span>
                        </a>
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
                </div>
            </div>

            <div class="col-12 col-lg-9 col-xl-10 main-col">
                @yield('content')
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>