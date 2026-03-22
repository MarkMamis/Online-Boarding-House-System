<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Student Portal') - Online Boarding House System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @stack('styles')
    <style>
        :root {
            --brand:#14532d;
            --brand-dark:#166534;
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
        .glass-card { background:var(--surface); border:1px solid rgba(2,8,20,.08); box-shadow:0 10px 26px rgba(2,8,20,.08); }

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

        .sidepanel.glass-card {
            background: rgba(255,255,255,1);
            backdrop-filter: none;
        }
        .sidebar-head {
            border: 1px solid var(--line);
            background: #fbfdff;
            border-radius: .8rem;
        }

        .main-col .card { background:#fff; border: 1px solid rgba(2,8,20,.08); }
        .main-col .card-header { background: rgba(2,8,20,.02); border-bottom-color: rgba(2,8,20,.08); }

        .main-col .bg-white { background-color: #fff !important; }

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
            background: transparent;
            border: 1px solid transparent;
            color: #334155;
            display: flex;
            align-items: center;
            gap: .6rem;
            padding: .74rem .9rem;
            width: 100%;
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
        .sidepanel .list-group-item i { color: #64748b; }
        .sidepanel .list-group-item i {
            width: 1rem;
            text-align: center;
            font-size: .93rem;
            flex: 0 0 auto;
        }
        .student-nav-list {
            display: flex;
            flex-direction: column;
            gap: .12rem;
        }
        .sidepanel .list-group-item.active {
            background: rgba(167,243,208,.32);
            border-color: rgba(20,83,45,.28);
            color: #14532d;
            font-weight: 700;
        }
        .sidepanel .list-group-item.active i { color: #166534; }
        .sidepanel .panel-title { color: #64748b; }

        .sidepanel .form-control:focus {
            border-color: rgba(22,101,52,.35);
            box-shadow: 0 0 0 .2rem rgba(22,101,52,.10);
        }

        @media (max-width: 992px){ .dash-shell { padding-top:4.2rem; } }
        @media (min-width: 992px) {
            .navbar-glass {
                left: var(--sidebar-w);
                width: calc(100% - var(--sidebar-w));
            }
            .dash-shell {
                padding-top: var(--nav-h);
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
            .sidepanel-col {
                overflow-y: auto;
                overflow-x: hidden;
                scrollbar-width: thin;
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
        $notificationsCount = \Illuminate\Support\Facades\Schema::hasTable('notifications')
            ? Auth::user()->unreadNotifications()->count()
            : 0;
    @endphp

    <nav class="navbar navbar-expand-lg navbar-light navbar-glass fixed-top">
        <div class="container-fluid px-3 px-lg-4">
            <a class="navbar-brand d-flex align-items-center gap-2" href="{{ route('student.dashboard') }}">
                <img src="{{ asset('images/minsu3.png') }}" alt="MINSU" class="brand-mark">
                <span>Student Portal</span>
            </a>
            <div class="d-flex align-items-center gap-3 ms-auto">
                <div class="top-meta d-none d-lg-block text-end">
                    <div>Workspace</div>
                    <div class="fw-semibold">Student Operations</div>
                </div>
                <span class="icon-btn-wrap d-none d-md-inline-flex">
                    <a class="icon-btn" href="{{ route('notifications.index') }}" title="Notifications">
                        <i class="bi bi-bell"></i>
                    </a>
                    @if($notificationsCount > 0)
                        <span class="icon-btn-badge">{{ $notificationsCount > 99 ? '99+' : $notificationsCount }}</span>
                    @endif
                </span>
                <a class="icon-btn d-none d-md-inline-flex" href="{{ route('student.profile.show') }}" title="Settings" aria-label="Settings">
                    <i class="bi bi-gear"></i>
                </a>
                <form method="POST" action="{{ route('logout') }}" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-sm rounded-pill px-3 btn-logout">Logout</button>
                </form>
            </div>
        </div>
    </nav>

    <main class="dash-shell container-fluid ps-0 pe-0">
        <div class="row g-0">
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
