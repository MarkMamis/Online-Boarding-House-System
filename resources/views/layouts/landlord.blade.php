<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Landlord Panel') - Online Boarding House System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    @stack('styles')
    <style>
        :root {
            --brand:#166534;
            --brand-dark:#15803d;
            --gold:#f59e0b;
            --ink:#0f172a;
            --nav-h:72px;
            --sidebar-w:300px;
        }
        body { font-family:'Inter', system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif; min-height:100vh; overflow-x:hidden; background:#fff; }

        .navbar-glass { background:#fff!important; border-bottom:1px solid rgba(2,8,20,.08); }
        .dash-shell { padding-top:5rem; padding-bottom:4rem; }
        .glass-card { background:#fff; border:1px solid rgba(2,8,20,.08); box-shadow:0 10px 26px rgba(2,8,20,.08); }

        .navbar-brand { color: var(--brand) !important; }

        .btn-brand { background:var(--brand); border-color:var(--brand); color:#fff; }
        .btn-brand:hover { background:var(--brand-dark); border-color:var(--brand-dark); color:#fff; }
        .btn-outline-brand { border-color: rgba(22,101,52,.35); color: var(--brand); }
        .btn-outline-brand:hover { background: rgba(22,101,52,.08); border-color: rgba(22,101,52,.55); color: var(--brand); }
        .highlight { color:var(--brand); font-weight:700; }

        .metric-tile {
            border-radius: 1rem;
            padding: 1rem 1.1rem;
            background: rgba(255,255,255,.78);
            border: 1px solid rgba(2,8,20,.08);
            box-shadow: 0 10px 26px rgba(2, 8, 20, .06);
        }
        .metric-ic {
            width: 42px;
            height: 42px;
            border-radius: .9rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(22,101,52,.10);
            border: 1px solid rgba(22,101,52,.18);
            color: var(--brand);
            flex: 0 0 auto;
        }
        .metric-label { color: rgba(2,8,20,.55); }

        .sidepanel.glass-card {
            background: rgba(255,255,255,1);
            backdrop-filter: none;
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
            background: rgba(255,255,255,1);
            border-color: rgba(2,8,20,.08);
            color: rgba(2,8,20,.82);
            display: flex;
            align-items: center;
            gap: .6rem;
            padding: .85rem 1rem;
            width: 100%;
            text-align: left;
        }
        .sidepanel .nav-section {
            padding: .65rem 1rem .35rem;
            font-size: .72rem;
            letter-spacing: .08em;
            text-transform: uppercase;
            color: rgba(2,8,20,.55);
        }
        .sidepanel .nav-toggle { cursor: pointer; }
        .sidepanel .nav-chevron { margin-left: auto; transition: transform .18s ease; opacity: .75; }
        .sidepanel .nav-toggle[aria-expanded="true"] .nav-chevron { transform: rotate(180deg); }
        .sidepanel .list-group-item.sub-item {
            padding: .7rem 1rem .7rem 2.55rem;
            background: rgba(255,255,255,1);
            font-size: .92rem;
        }
        .sidepanel .list-group-item.sub-item i { font-size: .95rem; }
        .sidepanel .list-group-item i { color: rgba(2,8,20,.65); }
        .sidepanel .list-group-item.active {
            background: rgba(22,101,52,.10);
            border-color: rgba(22,101,52,.22);
            color: rgba(2,8,20,.90);
            font-weight: 700;
        }
        .sidepanel .list-group-item.active i { color: var(--brand-dark); }
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
            .sidepanel {
                position: static;
                height: 100%;
                overflow: auto;
                direction: rtl;
                scrollbar-width: thin;
            }
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
        $propertiesOpen = is_string($routeName) && str_starts_with($routeName, 'landlord.properties.');
        $roomsOpen = is_string($routeName) && (str_starts_with($routeName, 'landlord.rooms.') || str_starts_with($routeName, 'landlord.properties.rooms.'));
        $notificationsCount = \Illuminate\Support\Facades\Schema::hasTable('notifications')
            ? Auth::user()->unreadNotifications()->count()
            : 0;
    @endphp

    <nav class="navbar navbar-expand-lg navbar-light navbar-glass fixed-top">
        <div class="container-fluid px-3 px-lg-4">
            <a class="navbar-brand fw-bold" href="{{ route('landlord.dashboard') }}">Landlord Panel</a>
            <div class="d-flex align-items-center gap-3 ms-auto">
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
                    <div class="d-flex align-items-center gap-2 px-2 pt-2 pb-3">
                        @if(!empty(Auth::user()->profile_image_path))
                            <img src="{{ asset('storage/' . Auth::user()->profile_image_path) }}" alt="Profile photo" class="rounded-3 border" style="width:40px;height:40px;object-fit:cover;border-color:rgba(22,101,52,.22)!important;">
                        @else
                            <div class="rounded-3 d-inline-flex align-items-center justify-content-center" style="width:40px;height:40px;background:rgba(22,101,52,.10);border:1px solid rgba(22,101,52,.20);">
                                <i class="bi bi-person-badge" style="color:var(--brand);"></i>
                            </div>
                        @endif
                        <div class="min-w-0">
                            <div class="fw-semibold text-truncate">{{ Auth::user()->full_name }}</div>
                            <div class="small panel-title">Landlord</div>
                        </div>
                    </div>

                    <div class="list-group list-group-flush rounded-3 overflow-hidden">
                        <div class="nav-section">Main</div>
                        <a @class(['list-group-item', 'active' => $routeName === 'landlord.dashboard']) href="{{ route('landlord.dashboard') }}">
                            <i class="bi bi-speedometer2"></i>
                            <span>Dashboard</span>
                        </a>

                        <div class="nav-section">Management</div>
                        <button class="list-group-item nav-toggle" type="button" data-bs-toggle="collapse" data-bs-target="#navProperties" aria-expanded="{{ $propertiesOpen ? 'true' : 'false' }}" aria-controls="navProperties">
                            <i class="bi bi-buildings"></i>
                            <span>Properties</span>
                            <i class="bi bi-chevron-down nav-chevron" style="font-size:.9rem;"></i>
                        </button>
                        <div class="collapse {{ $propertiesOpen ? 'show' : '' }}" id="navProperties">
                            <a @class(['list-group-item sub-item', 'active' => $routeName === 'landlord.properties.index']) href="{{ route('landlord.properties.index') }}">
                                <i class="bi bi-list-ul"></i> All Properties
                            </a>
                            <a @class(['list-group-item sub-item', 'active' => $routeName === 'landlord.properties.create']) href="{{ route('landlord.properties.create') }}">
                                <i class="bi bi-plus-circle"></i> Add Property
                            </a>
                        </div>

                        <button class="list-group-item nav-toggle" type="button" data-bs-toggle="collapse" data-bs-target="#navRooms" aria-expanded="{{ $roomsOpen ? 'true' : 'false' }}" aria-controls="navRooms">
                            <i class="bi bi-door-open"></i>
                            <span>Rooms</span>
                            <i class="bi bi-chevron-down nav-chevron" style="font-size:.9rem;"></i>
                        </button>
                        <div class="collapse {{ $roomsOpen ? 'show' : '' }}" id="navRooms">
                            <a @class(['list-group-item sub-item', 'active' => $routeName === 'landlord.rooms.index']) href="{{ route('landlord.rooms.index') }}">
                                <i class="bi bi-list-ul"></i> All Rooms
                            </a>
                        </div>

                        <div class="nav-section">Operations</div>
                        <a @class(['list-group-item', 'active' => $routeName === 'landlord.bookings.index']) href="{{ route('landlord.bookings.index') }}">
                            <i class="bi bi-journal-check"></i> Booking Requests
                        </a>
                        <a @class(['list-group-item', 'active' => $routeName === 'notifications.index']) href="{{ route('notifications.index') }}">
                            <i class="bi bi-bell"></i>
                            <span>Notifications</span>
                            @if($notificationsCount > 0)
                                <span class="badge rounded-pill text-bg-danger ms-auto">{{ $notificationsCount }}</span>
                            @endif
                        </a>
                        <a @class(['list-group-item', 'active' => $routeName === 'landlord.messages.index']) href="{{ route('landlord.messages.index') }}">
                            <i class="bi bi-chat-dots"></i> Messages
                        </a>
                        <a @class(['list-group-item', 'active' => $routeName === 'landlord.feedback.index']) href="{{ route('landlord.feedback.index') }}">
                            <i class="bi bi-star-half"></i> Feedback
                        </a>
                        <a @class(['list-group-item', 'active' => $routeName === 'landlord.tenants.index']) href="{{ route('landlord.tenants.index') }}">
                            <i class="bi bi-people"></i> Tenants
                        </a>
                        <a @class(['list-group-item', 'active' => $routeName === 'landlord.onboarding.index']) href="{{ route('landlord.onboarding.index') }}">
                            <i class="bi bi-clipboard-check"></i> Onboarding
                        </a>
                        <a @class(['list-group-item', 'active' => $routeName === 'landlord.leave_requests.index']) href="{{ route('landlord.leave_requests.index') }}">
                            <i class="bi bi-box-arrow-right"></i> Leave Requests
                        </a>
                        <a @class(['list-group-item', 'active' => $routeName === 'landlord.maintenance.index']) href="{{ route('landlord.maintenance.index') }}">
                            <i class="bi bi-tools"></i> Maintenance
                        </a>

                        <div class="nav-section">Finance</div>
                        <a @class(['list-group-item', 'active' => $routeName === 'landlord.payments.index']) href="{{ route('landlord.payments.index') }}">
                            <i class="bi bi-cash-coin"></i> Payments
                        </a>
                        <a @class(['list-group-item', 'active' => $routeName === 'landlord.analytics.index']) href="{{ route('landlord.analytics.index') }}">
                            <i class="bi bi-graph-up"></i> Analytics
                        </a>

                        <div class="nav-section">Account</div>
                        <a @class(['list-group-item', 'active' => $routeName === 'landlord.profile.edit']) href="{{ route('landlord.profile.edit') }}">
                            <i class="bi bi-gear"></i> Profile
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
    <x-toast />
    <x-chatbot />
    @stack('modals')
    @stack('scripts')
</body>
</html>