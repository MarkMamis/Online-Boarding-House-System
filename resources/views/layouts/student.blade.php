<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Student Panel - Online Boarding House System')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --brand:#0ea5a3; --brand-dark:#0b7f7e; }
        body { font-family:'Inter',system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif; min-height:100vh; }
        .bg-dorm {
            position:fixed; inset:0; background:
                linear-gradient(rgba(0,0,0,.55), rgba(0,0,0,.35)),
                url('{{ asset('images/mindoro-way-pic.jpg') }}') center/cover no-repeat;
            z-index:-1;
        }
        .navbar-trans { background:rgba(0,0,0,.55)!important; backdrop-filter: blur(6px); }
        .content-wrapper { padding-top:5rem; padding-bottom:4rem; }
        .dash-card { background:rgba(255,255,255,.9); backdrop-filter: blur(4px); box-shadow:0 10px 30px rgba(0,0,0,.35); border:0; }
        .dash-card h1 { font-weight:600; }
        .stat-tile { background:#ffffff; border-radius:1rem; box-shadow:0 4px 14px rgba(0,0,0,.08); padding:1.1rem 1.25rem; }
        .stat-tile small { color:#5b6b7f; }
        .highlight { color:var(--brand); font-weight:600; }
        .btn-brand { background:var(--brand); border-color:var(--brand); }
        .btn-brand:hover { background:var(--brand-dark); border-color:var(--brand-dark); }
        .quick-actions a { text-decoration:none; }
        @media (max-width: 992px){ .content-wrapper { padding-top:4rem; } }

        @media (max-width: 420px) {
            .container,
            .container-fluid {
                padding-left: .75rem !important;
                padding-right: .75rem !important;
            }

            .content-wrapper {
                padding-top: 3.7rem;
                padding-bottom: 2.2rem;
            }

            h1, .h1 { font-size: 1.35rem; }
            h2, .h2 { font-size: 1.2rem; }
            h3, .h3 { font-size: 1.08rem; }
            h4, .h4 { font-size: 1rem; }

            .dash-card,
            .stat-tile {
                border-radius: .85rem;
            }

            .btn {
                font-size: .86rem;
                padding: .45rem .8rem;
            }

            .form-control,
            .form-select {
                font-size: .95rem;
                padding: .5rem .68rem;
            }
        }
    </style>
    <noscript><style>.bg-dorm{background:linear-gradient(rgba(0,0,0,.55), rgba(0,0,0,.35)), url('{{ asset('images/mindoro-way-pic.jpg') }}') center/cover no-repeat;}</style></noscript>
    <script>
        window.addEventListener('load', () => {
            const img = new Image();
            img.onerror = () => document.querySelector('.bg-dorm').style.background = 'linear-gradient(135deg,#0ea5a3,#064e4e)';
            img.src = '{{ asset('images/mindoro-way-pic.jpg') }}';
        });
    </script>
    @stack('styles')
</head>
<body>
    <div class="bg-dorm"></div>
    @php
        $notificationsCount = \Illuminate\Support\Facades\Schema::hasTable('notifications')
            ? Auth::user()->unreadNotifications()->count()
            : 0;
    @endphp
    <nav class="navbar navbar-expand-lg navbar-dark navbar-trans fixed-top">
        <div class="container">
            <a class="navbar-brand fw-bold" href="{{ route('student.dashboard') }}">Student Panel</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu" aria-controls="navMenu" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navMenu">
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-lg-center">
                    <li class="nav-item me-lg-2">
                        <a href="{{ route('notifications.index') }}" class="btn btn-sm btn-outline-light rounded-pill position-relative" title="Notifications">
                            <span style="font-size: 14px; line-height: 1;">🔔</span>
                            @if($notificationsCount > 0)
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill text-bg-danger">{{ $notificationsCount }}</span>
                            @endif
                        </a>
                    </li>
                    <li class="nav-item me-lg-3 d-flex align-items-center gap-2 text-white small">
                        @if(!empty(Auth::user()->profile_image_path))
                            <img src="{{ asset('storage/' . Auth::user()->profile_image_path) }}" alt="Profile photo" class="rounded-circle border" style="width:28px;height:28px;object-fit:cover;border-color:rgba(255,255,255,.55)!important;">
                        @else
                            <span class="rounded-circle border d-inline-flex align-items-center justify-content-center" style="width:28px;height:28px;border-color:rgba(255,255,255,.55)!important;background:rgba(255,255,255,.12);">
                                <span style="font-size:12px;line-height:1;">👤</span>
                            </span>
                        @endif
                        <span>Hi, {{ Auth::user()->full_name }}</span>
                    </li>
                    <li class="nav-item">
                        <form method="POST" action="{{ route('logout') }}" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-light rounded-pill px-3">Logout</button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <main class="content-wrapper container">
        @yield('content')
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <x-toast />
    <x-chatbot />
    @stack('scripts')
</body>
</html>