<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Student Panel') - Online Boarding House System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    @stack('styles')
    <style>
        :root {
            --brand: #166534;      /* MINSU green */
            --brand-dark: #15803d; /* hover green */
            --gold: #f59e0b;       /* MINSU gold */
            --ink:#0f172a;
            --nav-h:72px;
            --sidebar-w:300px;
        }
        body { font-family:'Inter', system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif; min-height:100vh; overflow-x:hidden; background:#fff; }

        .navbar-glass { background:#fff!important; border-bottom:1px solid rgba(2,8,20,.08); }
        .dash-shell { padding-top:5rem; padding-bottom:4rem; }
        .glass-card { background:#fff; border:1px solid rgba(2,8,20,.08); box-shadow:0 10px 26px rgba(2,8,20,.08); }

        .sidepanel.glass-card {
            background: rgba(255,255,255,1);
            backdrop-filter: none;
        }

        .main-col .card { background:#fff; border: 1px solid rgba(2,8,20,.08); }
        .main-col .card-header { background: rgba(2,8,20,.02); border-bottom-color: rgba(2,8,20,.08); }

        .main-col .bg-white { background-color: #fff !important; }

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
        .sidepanel .list-group-item i { color: rgba(2,8,20,.65); }
        .sidepanel .list-group-item.active {
            background: rgba(22,101,52,.10);
            border-color: rgba(22,101,52,.22);
            color: rgba(2,8,20,.90);
            font-weight: 700;
        }
        .sidepanel .list-group-item.active i { color: var(--brand-dark); }
        .sidepanel .panel-title { color: rgba(2,8,20,.70); }

        .sidepanel .form-control:focus {
            border-color: rgba(22,101,52,.35);
            box-shadow: 0 0 0 .2rem rgba(22,101,52,.10);
        }

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
        $notificationsCount = \Illuminate\Support\Facades\Schema::hasTable('notifications')
            ? Auth::user()->unreadNotifications()->count()
            : 0;
    @endphp

    <nav class="navbar navbar-expand-lg navbar-light navbar-glass fixed-top">
        <div class="container-fluid px-3 px-lg-4">
            <a class="navbar-brand fw-bold" href="{{ route('student.dashboard') }}">Student Panel</a>
            <div class="d-flex align-items-center gap-3 ms-auto">
                <div class="small text-muted d-none d-lg-block">Signed in as</div>
                <div class="small text-dark d-none d-lg-block">{{ Auth::user()->full_name }}</div>

                <a href="{{ route('notifications.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill position-relative" title="Notifications">
                    <i class="bi bi-bell"></i>
                    @if($notificationsCount > 0)
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill text-bg-danger">{{ $notificationsCount }}</span>
                    @endif
                </a>
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
                <x-student-sidebar />
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
