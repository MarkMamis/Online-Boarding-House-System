<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Online Boarding House System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --brand: #2d8a4e;
            --brand-dark: #1a5c2e;
        }
        body { font-family: 'Inter', system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif; }
        .text-brand { color: var(--brand-dark); }
        .bg-soft {
            background:
                radial-gradient(900px circle at 15% 0%, rgba(45,138,78,.12), transparent 55%),
                radial-gradient(850px circle at 100% 20%, rgba(13,110,253,.08), transparent 45%),
                linear-gradient(180deg, rgba(248,249,250,1), rgba(248,249,250,1));
        }
        .card { border: 0; box-shadow: 0 10px 30px rgba(2, 8, 20, .08); }
        .btn-brand { background: var(--brand); border-color: var(--brand); }
        .btn-brand:hover { background: var(--brand-dark); border-color: var(--brand-dark); }
        .btn-outline-brand { border-color: rgba(45,138,78,.45); color: #1a5c2e; }
        .btn-outline-brand:hover { border-color: var(--brand-dark); background: rgba(45,138,78,.06); color: var(--brand-dark); }

        .navbar-green {
            background: linear-gradient(180deg, #1a5c2e 0%, #2d8a4e 60%, #3aaf65 100%);
            box-shadow: 0 2px 16px rgba(0,0,0,.30);
            overflow: visible !important;
        }
        .navbar-green .nav-link { color: rgba(255,255,255,.92); font-weight: 500; letter-spacing: .01em; }
        .navbar-green .nav-link:hover { color: #fff; text-decoration: underline; text-underline-offset: 3px; }
        .navbar-green .btn-link { color: rgba(255,255,255,.92) !important; font-weight: 500; }
        .navbar-green .btn-link:hover { color: #fff !important; }
        .navbar-green .navbar-toggler { border-color: rgba(255,255,255,.4); }
        .navbar-green .navbar-toggler-icon { filter: invert(1); }

        .nav-logo-center {
            position: absolute;
            left: .75rem;
            top: .25rem;
            transform: none;
            z-index: 30;
            pointer-events: auto;
        }
        .nav-logo-center img {
            height: 90px;
            width: 90px;
            object-fit: contain;
            margin-bottom: -18px;
            filter: drop-shadow(0 4px 10px rgba(0,0,0,.50));
        }

        @media (min-width: 992px) {
            .navbar-green #landingNav .navbar-nav {
                margin-left: 110px;
            }
        }

        .hero {
            padding-top: 6.5rem;
            padding-bottom: 3.5rem;
        }
        .hero-with-bg {
            position: relative;
            overflow: hidden;
        }
        .hero-with-bg:before {
            content: "";
            position: absolute;
            inset: 0;
            background-image: url("{{ asset('images/minsu.png') }}");
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }
        .hero-with-bg:after {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(180deg, rgba(248,249,250,.28), rgba(248,249,250,.62));
        }
        .hero-with-bg > * {
            position: relative;
            z-index: 1;
        }
        .hero-kicker {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            padding: .35rem .75rem;
            border-radius: 999px;
            background: rgba(45,138,78,.10);
            color: var(--brand-dark);
            font-weight: 600;
            font-size: .9rem;
        }

        /* Lightweight “product preview” illustration (no external assets) */
        .ui-shell {
            background: #f4f6f8;
            border-radius: 1.5rem;
            border: 1px solid rgba(2, 8, 20, .08);
            box-shadow: 0 32px 80px rgba(2, 8, 20, .18);
            overflow: hidden;
        }
        .ui-topbar {
            padding: .75rem 1.1rem;
            background: linear-gradient(90deg, #1a5c2e 0%, #2d8a4e 60%, #3aaf65 100%);
            display: flex;
            align-items: center;
            gap: .65rem;
        }
        .ui-dot { width: 9px; height: 9px; border-radius: 999px; background: rgba(255,255,255,.35); }
        .ui-app-title {
            flex: 1;
            color: rgba(255,255,255,.90);
            font-size: .78rem;
            font-weight: 600;
            letter-spacing: .02em;
        }
        .ui-avatar {
            width: 26px; height: 26px;
            border-radius: 999px;
            background: rgba(255,255,255,.22);
            border: 2px solid rgba(255,255,255,.40);
            display: flex; align-items: center; justify-content: center;
            color: #fff; font-size: .75rem;
        }
        .ui-body { padding: 1rem 1.1rem 1.1rem; }
        .ui-stat {
            background: #fff;
            border-radius: 1rem;
            padding: .85rem 1rem;
            border: 1px solid rgba(2,8,20,.07);
            box-shadow: 0 2px 8px rgba(2,8,20,.05);
        }
        .ui-stat-label { font-size: .72rem; color: rgba(2,8,20,.50); font-weight: 500; text-transform: uppercase; letter-spacing: .06em; }
        .ui-stat-value { font-size: 1.6rem; font-weight: 700; color: #0f172a; line-height: 1.1; }
        .ui-stat-sub { font-size: .78rem; color: rgba(2,8,20,.50); margin-top: .15rem; }
        .ui-stat-icon {
            width: 36px; height: 36px;
            border-radius: .7rem;
            display: flex; align-items: center; justify-content: center;
            font-size: 1rem;
        }
        .ui-progress-track {
            height: 6px;
            border-radius: 999px;
            background: rgba(2,8,20,.07);
            margin-top: .6rem;
            overflow: hidden;
        }
        .ui-progress-bar {
            height: 100%;
            border-radius: 999px;
            background: linear-gradient(90deg, var(--brand-dark), var(--brand));
        }
        .ui-divider { height: 1px; background: rgba(2,8,20,.06); margin: .75rem 0; }
        .ui-activity-row {
            display: flex;
            align-items: center;
            gap: .65rem;
            padding: .45rem 0;
        }
        .ui-activity-dot {
            width: 8px; height: 8px;
            border-radius: 999px;
            flex-shrink: 0;
        }
        .ui-activity-text { font-size: .8rem; color: rgba(2,8,20,.70); flex: 1; }
        .ui-activity-badge {
            font-size: .7rem;
            font-weight: 700;
            padding: .2rem .55rem;
            border-radius: 999px;
        }
        .ui-badge {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            font-weight: 600;
            color: #0f172a;
            background: rgba(2,8,20,.04);
            padding: .35rem .6rem;
            border-radius: 999px;
            border: 1px solid rgba(2,8,20,.06);
            font-size: .85rem;
        }
        .icon-chip {
            width: 42px;
            height: 42px;
            border-radius: .9rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(45,138,78,.10);
            color: var(--brand-dark);
        }
        .feature-card { border: 1px solid rgba(2, 8, 20, .08); }
        .section-pad { padding: 4rem 0; }

        .features-fixed-bg {
            background-image: url("{{ asset('images/minsu.png') }}");
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
            background-color: transparent;
        }
        @media (max-width: 991.98px) {
            .features-fixed-bg { background-attachment: scroll; }
        }

        .footer-dark {
            background:
                radial-gradient(900px circle at 15% 15%, rgba(45,138,78,.16), transparent 55%),
                radial-gradient(800px circle at 90% 0%, rgba(13,110,253,.14), transparent 55%),
                linear-gradient(180deg, #0b1220, #070b12);
        }
        .footer-card {
            background: rgba(255,255,255,.06);
            border: 1px solid rgba(255,255,255,.10);
            border-radius: 1rem;
        }
        .footer-card:hover { border-color: rgba(255,255,255,.16); }
        .footer-link { color: rgba(255,255,255,.82); text-decoration: none; }
        .footer-link:hover { color: #fff; text-decoration: underline; text-underline-offset: 3px; }
        .footer-muted { color: rgba(255,255,255,.70); }
        .footer-pill {
            display: inline-flex;
            align-items: center;
            gap: .45rem;
            background: rgba(255,255,255,.08);
            border: 1px solid rgba(255,255,255,.10);
            padding: .35rem .7rem;
            border-radius: 999px;
            color: rgba(255,255,255,.86);
            font-size: .85rem;
            font-weight: 600;
        }
    </style>
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-green fixed-top">
        <div class="container-xl position-relative" style="padding-top:.6rem;padding-bottom:.6rem;">

            {{-- Left Logo --}}
            <a class="nav-logo-center d-none d-lg-block" href="{{ route('landing') }}">
                <img src="{{ asset('images/minsu3.png') }}" alt="MINSU Logo">
            </a>

            {{-- Mobile brand --}}
            <a class="navbar-brand fw-bold text-white d-lg-none" href="{{ route('landing') }}">OBHS</a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#landingNav" aria-controls="landingNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="landingNav">
                {{-- Left side nav links --}}
                <ul class="navbar-nav me-auto mb-2 mb-lg-0 gap-lg-3">
                    <li class="nav-item"><a class="nav-link" href="#features">Features</a></li>
                    <li class="nav-item"><a class="nav-link" href="#students">For Students</a></li>
                    <li class="nav-item"><a class="nav-link" href="#landlords">For Landlords</a></li>
                </ul>

                {{-- Right side --}}
                <div class="d-flex align-items-center gap-lg-3 gap-2">
                    <a href="{{ route('login') }}" class="btn btn-link text-decoration-none">Log in</a>
                    <a href="{{ route('register') }}" class="btn btn-outline-light rounded-pill px-4">Sign up</a>
                </div>
            </div>
        </div>
    </nav>

    <header class="bg-soft hero hero-with-bg">
        <div class="container-xl">
            <div class="row align-items-center g-4 g-lg-5">
                <div class="col-lg-6">
                    <div class="hero-kicker mb-3">
                        <i class="bi bi-lightning-charge"></i>
                         Online Boarding House System
                    </div>

                    <h1 class="display-4 fw-bold lh-sm mb-3">
                        Online <span class="text-brand">boarding house</span>
                        booking and management
                    </h1>
                    <p class="lead text-dark fw-bold mb-4">
                        Built for students and landlords — browse rooms, send booking requests, manage properties, and communicate in one place.
                    </p>

                    <div class="d-flex flex-column flex-sm-row gap-2">
                        <a href="{{ route('register') }}" class="btn btn-brand btn-lg rounded-pill px-4">Get started</a>
                        <a href="#features" class="btn btn-outline-brand btn-lg rounded-pill px-4">See features</a>
                    </div>

                    <div class="d-flex flex-wrap align-items-center gap-3 mt-4 text-muted small">
                        <span class="ui-badge"><i class="bi bi-person-check"></i> Student & Landlord roles</span>
                        <span class="ui-badge"><i class="bi bi-shield-check"></i> Secure access</span>
                        <span class="ui-badge"><i class="bi bi-chat-dots"></i> Messaging</span>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="card rounded-4">
                        <div class="card-body p-4 p-lg-5">
                            <div class="d-flex align-items-center justify-content-between gap-3 flex-wrap mb-3">
                                <div>
                                    <div class="fw-semibold">Room previews</div>
                                    <div class="text-muted small">Top-rated available rooms (preview).</div>
                                </div>
                                <span class="badge rounded-pill" style="background: rgba(2,8,20,.06); color: rgba(2,8,20,.70);">Preview</span>
                            </div>

                            @php
                                $featuredRooms = ($availableRooms ?? collect())
                                    ->sortByDesc(fn ($room) => (float) ($room->property->average_rating ?? 0))
                                    ->sortByDesc(fn ($room) => (int) ($room->property->ratings_count ?? 0))
                                    ->take(3)
                                    ->values();
                            @endphp

                            <div class="row g-3">
                                @forelse($featuredRooms as $idx => $room)
                                    @php
                                        $img = $room->image_path ?: ($room->property->image_path ?? null);

                                        $rawRoomNumber = trim((string) ($room->room_number ?? ''));
                                        $normalizedRoomNumber = preg_replace('/^room\s*[:#-]?\s*/i', '', $rawRoomNumber);
                                        $displayRoomNumber = $normalizedRoomNumber !== '' ? $normalizedRoomNumber : $rawRoomNumber;

                                        $chips = collect(preg_split('/[\n\r,]+/', (string) ($room->inclusions ?? '')))
                                            ->map(fn ($v) => trim($v))
                                            ->filter()
                                            ->take(3);

                                        if ($chips->isEmpty()) {
                                            $chips = collect(['Wi-Fi', 'Water', 'Electricity']);
                                        }

                                        $avg = (float) ($room->property->average_rating ?? 0);
                                        $count = (int) ($room->property->ratings_count ?? 0);
                                        $ratingLabel = $count > 0 ? number_format($avg, 1) : '—';
                                    @endphp

                                    <div class="{{ $idx < 2 ? 'col-12 col-md-6' : 'col-12' }}">
                                        <div class="card rounded-4 h-100 overflow-hidden">
                                            <div class="bg-light rounded-top-4 position-relative overflow-hidden" style="height: 120px;">
                                                @if(!empty($img))
                                                    <img src="{{ asset('storage/' . $img) }}" alt="Room photo" style="height: 120px; width: 100%; object-fit: cover;">
                                                @else
                                                    <div class="h-100 d-flex align-items-center justify-content-center">
                                                        <i class="bi bi-image" style="font-size: 1.5rem; color: rgba(2,8,20,.35);"></i>
                                                    </div>
                                                @endif
                                                <span class="badge rounded-pill bg-dark" style="position:absolute; top:.6rem; right:.6rem;">Available</span>
                                            </div>

                                            <div class="card-body p-3">
                                                <div class="d-flex align-items-start justify-content-between gap-2">
                                                    <div class="fw-semibold">Room {{ $displayRoomNumber }}</div>
                                                    <div class="fw-bold">₱{{ number_format((float) $room->price, 0) }}</div>
                                                </div>

                                                <div class="d-flex flex-wrap align-items-center gap-2 text-muted small mt-1">
                                                    <span class="d-inline-flex align-items-center gap-1">
                                                        <i class="bi bi-star-fill text-warning"></i>
                                                        <span class="fw-semibold text-body">{{ $ratingLabel }}</span>
                                                        @if($count > 0)
                                                            <span class="text-muted">({{ $count }})</span>
                                                        @endif
                                                    </span>
                                                    <span class="text-muted">•</span>
                                                    <span><i class="bi bi-geo-alt"></i> {{ \Illuminate\Support\Str::limit($room->property->address ?? '—', 26) }}</span>
                                                </div>

                                                <div class="d-flex flex-wrap gap-2 mt-2">
                                                    @foreach($chips as $chip)
                                                        <span class="ui-badge" style="font-size:.75rem;padding:.25rem .5rem;">{{ $chip }}</span>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="col-12">
                                        <div class="alert alert-light border rounded-4 mb-0">
                                            No available rooms yet.
                                        </div>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <section class="section-pad bg-soft">
        <div class="container-xl">
            <div class="d-flex flex-column flex-md-row align-items-md-end justify-content-between gap-2 mb-4">
                <div>
                    <h2 class="fw-bold mb-1">Available <span class="text-brand">Rooms</span></h2>
                    <p class="text-muted mb-0">Preview of rooms currently marked available.</p>
                </div>
                    <div class="text-muted small">Log in as student to book.</div>
            </div>

            <div class="row g-3 g-lg-4">
                @forelse(($availableRooms ?? collect()) as $room)
                    <div class="col-12 col-md-6 col-lg-4">
                            <a href="{{ route('rooms.public.show', $room) }}" class="text-decoration-none text-reset">
                                <div class="card rounded-4 h-100 overflow-hidden">
                            @php
                                $img = $room->image_path ?: ($room->property->image_path ?? null);
                            @endphp
                            @if(!empty($img))
                                <img src="{{ asset('storage/' . $img) }}" alt="Room photo" class="card-img-top bg-light" style="height: 220px; width: 100%; object-fit: cover;">
                            @endif
                            <div class="card-body p-4">
                                <div class="d-flex align-items-start justify-content-between gap-2">
                                    <div>
                                        @php
                                            $rawRoomNumber = trim((string) ($room->room_number ?? ''));
                                            $normalizedRoomNumber = preg_replace('/^room\s*[:#-]?\s*/i', '', $rawRoomNumber);
                                            $displayRoomNumber = $normalizedRoomNumber !== '' ? $normalizedRoomNumber : $rawRoomNumber;
                                        @endphp
                                        <h3 class="h5 fw-bold mb-1">Room {{ $displayRoomNumber }}</h3>
                                        <div class="text-muted small">
                                            <i class="bi bi-building"></i> {{ $room->property->name ?? 'Property' }}
                                        </div>
                                        <div class="text-muted small">
                                            <i class="bi bi-geo-alt"></i> {{ $room->property->address ?? '—' }}
                                        </div>
                                    </div>
                                    <span class="badge rounded-pill" style="background: rgba(45,138,78,.12); color: var(--brand-dark);">Available</span>
                                </div>

                                <div class="d-flex flex-wrap gap-2 mt-3">
                                    <span class="ui-badge"><i class="bi bi-people"></i> Capacity: {{ (int) $room->capacity }}</span>
                                    <span class="ui-badge"><i class="bi bi-cash-coin"></i> ₱{{ number_format((float) $room->price, 2) }}</span>
                                </div>

                                @if(!empty($room->inclusions))
                                    <p class="text-muted mt-3 mb-0">{{ \Illuminate\Support\Str::limit($room->inclusions, 90) }}</p>
                                @endif

                                <div class="text-muted small mt-3">
                                    <i class="bi bi-person"></i> Landlord: {{ $room->property->landlord->full_name ?? 'N/A' }}
                                </div>
                            </div>
                            </div>
                        </a>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="alert alert-light border rounded-4 mb-0">
                            No available rooms yet.
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    <section id="features" class="section-pad features-fixed-bg">
        <div class="container-xl">
            <div class="text-center mb-4">
                <h2 class="fw-bold mb-1">Our <span class="text-brand">Features</span></h2>
                <p class="text-muted mb-0">Everything you need for browsing, booking, and managing boarding houses.</p>
            </div>

            <div class="row g-3 g-lg-4">
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="card feature-card rounded-4 h-100">
                        <div class="card-body p-4">
                            <div class="icon-chip mb-3"><i class="bi bi-search"></i></div>
                            <h3 class="h5 fw-bold">Room browsing</h3>
                            <p class="text-muted mb-0">Explore available rooms with clear details and availability.</p>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-6 col-lg-4">
                    <div class="card feature-card rounded-4 h-100">
                        <div class="card-body p-4">
                            <div class="icon-chip mb-3"><i class="bi bi-calendar2-check"></i></div>
                            <h3 class="h5 fw-bold">Online booking requests</h3>
                            <p class="text-muted mb-0">Send requests and track approval status without hassle.</p>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-6 col-lg-4">
                    <div class="card feature-card rounded-4 h-100">
                        <div class="card-body p-4">
                            <div class="icon-chip mb-3"><i class="bi bi-chat-left-text"></i></div>
                            <h3 class="h5 fw-bold">Messaging</h3>
                            <p class="text-muted mb-0">Students and landlords can communicate inside the platform.</p>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-6 col-lg-4">
                    <div class="card feature-card rounded-4 h-100">
                        <div class="card-body p-4">
                            <div class="icon-chip mb-3"><i class="bi bi-building"></i></div>
                            <h3 class="h5 fw-bold">Property management</h3>
                            <p class="text-muted mb-0">Landlords can add properties, rooms, and keep listings updated.</p>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-6 col-lg-4">
                    <div class="card feature-card rounded-4 h-100">
                        <div class="card-body p-4">
                            <div class="icon-chip mb-3"><i class="bi bi-geo-alt"></i></div>
                            <h3 class="h5 fw-bold">Map view</h3>
                            <p class="text-muted mb-0">View properties on a map to help students compare locations.</p>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-6 col-lg-4">
                    <div class="card feature-card rounded-4 h-100">
                        <div class="card-body p-4">
                            <div class="icon-chip mb-3"><i class="bi bi-flag"></i></div>
                            <h3 class="h5 fw-bold">Reports & responses</h3>
                            <p class="text-muted mb-0">Students can submit reports and monitor responses.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="students" class="section-pad bg-soft">
        <div class="container-xl">
            <div class="row align-items-center g-4">
                <div class="col-lg-6">
                    <h2 class="fw-bold mb-2">For <span class="text-brand">Students</span></h2>
                    <p class="text-muted mb-4">Find the right boarding house faster and manage your requests in one dashboard.</p>
                    <div class="d-flex flex-column gap-2 text-muted">
                        <div><i class="bi bi-check2-circle text-brand me-2"></i>Browse rooms and view details</div>
                        <div><i class="bi bi-check2-circle text-brand me-2"></i>Request bookings and track status</div>
                        <div><i class="bi bi-check2-circle text-brand me-2"></i>Message landlords and submit reports</div>
                    </div>
                    <div class="mt-4 d-flex flex-column flex-sm-row gap-2">
                        <a href="{{ route('register.student') }}" class="btn btn-brand btn-lg rounded-pill px-4">Register as Student</a>
                        <a href="{{ route('login') }}" class="btn btn-outline-brand btn-lg rounded-pill px-4">I already have an account</a>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="card rounded-4">
                        <div class="card-body p-4 p-lg-5">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div class="fw-semibold">Student dashboard highlights</div>
                                <span class="badge rounded-pill" style="background: rgba(45,138,78,.10); color: var(--brand-dark);">Student</span>
                            </div>
                            <div class="row g-3">
                                <div class="col-12 col-sm-6">
                                    <div class="ui-card">
                                        <div class="text-muted small">Saved rooms</div>
                                        <div class="h4 fw-bold mb-0">5</div>
                                    </div>
                                </div>
                                <div class="col-12 col-sm-6">
                                    <div class="ui-card">
                                        <div class="text-muted small">Active requests</div>
                                        <div class="h4 fw-bold mb-0">2</div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="ui-card">
                                        <div class="text-muted small mb-2">Messages</div>
                                        <div class="d-flex align-items-center justify-content-between">
                                            <div class="small text-muted"><i class="bi bi-dot"></i> New reply from landlord</div>
                                            <span class="badge rounded-pill" style="background: rgba(13,110,253,.10); color: #0d6efd;">1</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="landlords" class="section-pad">
        <div class="container-xl">
            <div class="row align-items-center g-4">
                <div class="col-lg-6 order-lg-2">
                    <h2 class="fw-bold mb-2">For <span class="text-brand">Landlords</span></h2>
                    <p class="text-muted mb-4">Manage properties, rooms, and booking requests with a clean workflow.</p>
                    <div class="d-flex flex-column gap-2 text-muted">
                        <div><i class="bi bi-check2-circle text-brand me-2"></i>Create properties and rooms</div>
                        <div><i class="bi bi-check2-circle text-brand me-2"></i>Approve or reject booking requests</div>
                        <div><i class="bi bi-check2-circle text-brand me-2"></i>Track tenants, messages, and reports</div>
                    </div>
                    <div class="mt-4 d-flex flex-column flex-sm-row gap-2">
                        <a href="{{ route('register.landlord') }}" class="btn btn-brand btn-lg rounded-pill px-4">Register as Landlord</a>
                        <a href="{{ route('login') }}" class="btn btn-outline-brand btn-lg rounded-pill px-4">Log in</a>
                    </div>
                </div>
                <div class="col-lg-6 order-lg-1">
                    <div class="card rounded-4">
                        <div class="card-body p-4 p-lg-5">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div class="fw-semibold">Landlord tools</div>
                                <span class="badge rounded-pill" style="background: rgba(45,138,78,.10); color: var(--brand-dark);">Landlord</span>
                            </div>
                            <div class="row g-3">
                                <div class="col-12 col-sm-6">
                                    <div class="ui-card">
                                        <div class="text-muted small">Properties</div>
                                        <div class="h4 fw-bold mb-0">3</div>
                                    </div>
                                </div>
                                <div class="col-12 col-sm-6">
                                    <div class="ui-card">
                                        <div class="text-muted small">Rooms listed</div>
                                        <div class="h4 fw-bold mb-0">24</div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="ui-card">
                                        <div class="text-muted small mb-2">Booking queue</div>
                                        <div class="d-flex align-items-center justify-content-between">
                                            <div class="small text-muted"><i class="bi bi-dot"></i> 2 awaiting approval</div>
                                            <span class="badge rounded-pill" style="background: rgba(13,110,253,.10); color: #0d6efd;">2</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <footer class="footer-dark text-white pt-5">
        <div class="container-xl">
            <div class="row g-4 align-items-stretch">
                <div class="col-lg-4">
                    <div class="footer-card p-4 h-100">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="fw-bold" style="letter-spacing: .3px;">Online Boarding House System</div>
                            <span class="footer-pill"><i class="bi bi-mortarboard"></i> Capstone</span>
                        </div>
                        <p class="footer-muted mt-3 mb-0">
                            A centralized platform for students to browse rooms and send booking requests, and for landlords to manage properties, rooms, and bookings.
                        </p>

                        <div class="d-flex flex-wrap gap-2 mt-4">
                            <span class="footer-pill"><i class="bi bi-shield-check"></i> Secure</span>
                            <span class="footer-pill"><i class="bi bi-chat-dots"></i> Messaging</span>
                            <span class="footer-pill"><i class="bi bi-geo-alt"></i> Map view</span>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3">
                    <div class="footer-card p-4 h-100">
                        <div class="fw-semibold mb-3">Quick Links</div>
                        <div class="d-flex flex-column gap-2">
                            <a class="footer-link" href="#features"><i class="bi bi-stars me-2"></i>Features</a>
                            <a class="footer-link" href="#students"><i class="bi bi-person me-2"></i>For Students</a>
                            <a class="footer-link" href="#landlords"><i class="bi bi-building me-2"></i>For Landlords</a>
                            <a class="footer-link" href="{{ route('login') }}"><i class="bi bi-box-arrow-in-right me-2"></i>Log in</a>
                            <a class="footer-link" href="{{ route('register') }}"><i class="bi bi-person-plus me-2"></i>Sign up</a>
                        </div>
                    </div>
                </div>

                <div class="col-lg-5">
                    <div class="row g-3">
                        <div class="col-12">
                            <div class="footer-card p-4">
                                <div class="d-flex align-items-center justify-content-between gap-3 flex-wrap">
                                    <div>
                                        <div class="text-uppercase footer-muted" style="font-size: .75rem; letter-spacing: .12em;">Recommended</div>
                                        <div class="fw-semibold">Start with a student account</div>
                                        <div class="footer-muted small">Browse rooms and submit booking requests in minutes.</div>
                                    </div>
                                    <a href="{{ route('register.student') }}" class="btn btn-brand rounded-pill px-4">Register as Student</a>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="footer-card p-4 h-100">
                                <div class="d-flex gap-3">
                                    <div class="icon-chip" style="background: rgba(255,255,255,.10); color: #fff;"><i class="bi bi-house-door"></i></div>
                                    <div>
                                        <div class="text-uppercase footer-muted" style="font-size: .75rem; letter-spacing: .12em;">Listings</div>
                                        <div class="fw-semibold">Manage properties & rooms</div>
                                        <div class="footer-muted small">Landlords can create and update listings quickly.</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="footer-card p-4 h-100">
                                <div class="d-flex gap-3">
                                    <div class="icon-chip" style="background: rgba(255,255,255,.10); color: #fff;"><i class="bi bi-calendar2-check"></i></div>
                                    <div>
                                        <div class="text-uppercase footer-muted" style="font-size: .75rem; letter-spacing: .12em;">Bookings</div>
                                        <div class="fw-semibold">Approve requests smoothly</div>
                                        <div class="footer-muted small">Track pending, approved, and rejected bookings.</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="footer-card p-4 h-100">
                                <div class="d-flex gap-3">
                                    <div class="icon-chip" style="background: rgba(255,255,255,.10); color: #fff;"><i class="bi bi-chat-left-text"></i></div>
                                    <div>
                                        <div class="text-uppercase footer-muted" style="font-size: .75rem; letter-spacing: .12em;">Communication</div>
                                        <div class="fw-semibold">Message inside the app</div>
                                        <div class="footer-muted small">Students and landlords can chat for faster coordination.</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="footer-card p-4 h-100">
                                <div class="d-flex gap-3">
                                    <div class="icon-chip" style="background: rgba(255,255,255,.10); color: #fff;"><i class="bi bi-flag"></i></div>
                                    <div>
                                        <div class="text-uppercase footer-muted" style="font-size: .75rem; letter-spacing: .12em;">Reports</div>
                                        <div class="fw-semibold">Submit concerns easily</div>
                                        <div class="footer-muted small">Log issues and monitor responses transparently.</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-between gap-2 mt-4 py-4" style="border-top: 1px solid rgba(255,255,255,.10);">
                <div class="footer-muted small">&copy; {{ date('Y') }} Online Boarding House System</div>
                <div class="d-flex gap-3 small">
                    <a class="footer-link" href="{{ route('login') }}">Log in</a>
                    <a class="footer-link" href="{{ route('register') }}">Sign up</a>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
