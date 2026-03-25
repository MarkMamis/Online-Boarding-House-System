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
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700&family=Bricolage+Grotesque:opsz,wght@12..96,600;12..96,700&display=swap" rel="stylesheet">
    <style>
        :root {
            --brand: #14532d;
            --brand-2: #166534;
            --mint: #a7f3d0;
            --gold: #f59e0b;
            --ink: #0f172a;
            --paper: #f8fafc;
        }
        body {
            font-family: 'Manrope', system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
            color: var(--ink);
            background: var(--paper);
        }
        h1, h2, h3, .display-font {
            font-family: 'Bricolage Grotesque', 'Manrope', system-ui, sans-serif;
        }
        .navbar-green {
            background: linear-gradient(180deg, #1a5c2e 0%, #2d8a4e 60%, #3aaf65 100%);
            box-shadow: 0 2px 16px rgba(0,0,0,.30);
            overflow: visible !important;
        }
        .navbar-green .nav-link { color: rgba(255,255,255,.92); font-weight: 600; letter-spacing: .01em; }
        .navbar-green .nav-link:hover { color: #fff; text-decoration: underline; text-underline-offset: 3px; }
        .navbar-green .btn-link { color: rgba(255,255,255,.92) !important; font-weight: 600; }
        .navbar-green .btn-link:hover { color: #fff !important; }
        .navbar-green .navbar-toggler { border-color: rgba(255,255,255,.4); }
        .navbar-green .navbar-toggler-icon { filter: invert(1); }
        .navbar-green .navbar-brand {
            position: relative;
            padding-left: 86px;
            margin-left: 0;
        }
        .nav-logo-under {
            position: absolute;
            left: 0;
            top: -10px;
            z-index: 2;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        .nav-logo-under img {
            height: 84px;
            width: 84px;
            object-fit: contain;
            margin-bottom: -18px;
            filter: drop-shadow(0 5px 10px rgba(0,0,0,.45));
        }
        @media (max-width: 991.98px) {
            .navbar-green .navbar-brand {
                padding-left: 62px;
                font-size: 1rem;
            }
            .nav-logo-under {
                top: -6px;
            }
            .nav-logo-under img {
                height: 56px;
                width: 56px;
                margin-bottom: -8px;
            }
        }

        .hero {
            position: relative;
            overflow: hidden;
            padding-top: 6.5rem;
            padding-bottom: 4rem;
            background: #0c3d20;
            color: #fff;
        }
        .hero::before {
            content: "";
            position: absolute;
            inset: 0;
            background: url("{{ asset('images/MinSU-Calapan.jpg') }}") center/cover no-repeat;
            opacity: .35;
            filter: saturate(1.05) contrast(1.1);
        }
        .hero::after {
            content: "";
            position: absolute;
            inset: 0;
            background:
                radial-gradient(900px circle at 10% 10%, rgba(34,197,94,.35), transparent 60%),
                linear-gradient(180deg, rgba(12,61,32,.4), rgba(12,61,32,.85));
        }
        .hero > * { position: relative; z-index: 1; }
        .hero-kicker {
            display: inline-flex; align-items: center; gap: .5rem;
            padding: .4rem .75rem; border-radius: 999px;
            background: rgba(255,255,255,.12);
            border: 1px solid rgba(255,255,255,.25);
            font-weight: 600; font-size: .85rem;
        }
        .hero-credit {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            margin-top: .65rem;
            padding: .45rem .8rem;
            border-radius: 999px;
            background: rgba(245, 158, 11, .2);
            border: 1px solid rgba(245, 158, 11, .55);
            color: #fef3c7;
            font-size: .78rem;
            font-weight: 700;
            letter-spacing: .01em;
        }
        .hero-title { font-size: clamp(2.3rem, 4.6vw, 4.1rem); line-height: 1.05; }
        .hero-sub { font-size: 1.05rem; color: rgba(255,255,255,.85); }
        .hero-cta .btn { padding: .8rem 1.4rem; font-weight: 700; border-radius: 999px; }
        .btn-brand { background: #22c55e; border-color: #22c55e; color: #0b2f18; }
        .btn-brand:hover { background: #16a34a; border-color: #16a34a; color: #fff; }
        .btn-ghost { border: 1px solid rgba(255,255,255,.4); color: #fff; }
        .btn-ghost:hover { background: rgba(255,255,255,.12); }

        .hero-card {
            background: rgba(255,255,255,.92);
            border-radius: 1.5rem;
            border: 1px solid rgba(15, 23, 42, .08);
            box-shadow: 0 30px 70px rgba(2, 8, 20, .35);
            padding: 1.3rem;
            animation: floatUp 900ms ease both;
            color: var(--ink);
        }
        .hero-card .text-muted { color: rgba(15,23,42,.6) !important; }
        .preview-card { color: var(--ink); }
        .hero-card + .hero-card { margin-top: 1rem; }
        .stat-chip {
            display: inline-flex; align-items: center; gap: .4rem;
            background: rgba(16,185,129,.12);
            color: #065f46; font-weight: 700;
            padding: .35rem .65rem; border-radius: 999px; font-size: .75rem;
        }
        .preview-card {
            border: 1px solid rgba(2,8,20,.08);
            border-radius: 1rem;
            padding: .85rem;
            background: #fff;
        }
        .preview-link {
            text-decoration: none;
            color: inherit;
            display: block;
        }
        .preview-link:hover .preview-card {
            border-color: rgba(22, 163, 74, .45);
            box-shadow: 0 10px 24px rgba(2, 8, 20, .10);
        }
        .preview-thumb {
            width: 88px;
            height: 88px;
            border-radius: .75rem;
            object-fit: cover;
            border: 1px solid rgba(2,8,20,.08);
            background: #e2e8f0;
        }
        .preview-thumb.placeholder {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #64748b;
            background: linear-gradient(120deg, rgba(34,197,94,.18), rgba(14,116,144,.12));
        }
        .preview-price { font-weight: 800; color: #166534; }

        .section { padding: 4.2rem 0; }
        .section-title { font-size: clamp(1.6rem, 3vw, 2.5rem); }
        .section-sub { color: rgba(15,23,42,.65); }

        .glass-card {
            background: #fff;
            border-radius: 1.2rem;
            border: 1px solid rgba(2,8,20,.08);
            box-shadow: 0 16px 40px rgba(2,8,20,.08);
            padding: 1.5rem;
        }
        .feature-icon {
            width: 44px; height: 44px; border-radius: 14px;
            display: inline-flex; align-items: center; justify-content: center;
            background: rgba(22,101,52,.12); color: #14532d;
        }
        .pill {
            display: inline-flex; align-items: center; gap: .4rem;
            border-radius: 999px; padding: .35rem .7rem; font-size: .75rem;
            border: 1px solid rgba(2,8,20,.08); background: #f1f5f9; color: #334155;
        }

        .property-card {
            border-radius: 1.3rem;
            overflow: hidden;
            border: 1px solid rgba(2,8,20,.08);
            background: #fff;
            box-shadow: 0 14px 32px rgba(2,8,20,.08);
            transition: transform .2s ease, box-shadow .2s ease;
        }
        .property-card:hover { transform: translateY(-4px); box-shadow: 0 22px 46px rgba(2,8,20,.12); }
        .property-photo {
            height: 180px;
            background: linear-gradient(120deg, rgba(34,197,94,.15), rgba(14,116,144,.15));
            display: flex; align-items: center; justify-content: center;
            color: rgba(15,23,42,.45);
        }
        .property-photo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .step-card { border-left: 4px solid #16a34a; }

        .role-strip {
            background: linear-gradient(120deg, #f8fafc, #eef2ff);
            border: 1px solid rgba(2,8,20,.08);
            border-radius: 1.5rem;
            padding: 2rem;
        }

        .footer {
            background: #0b1220;
            color: rgba(255,255,255,.8);
            padding: 3rem 0 2rem;
        }
        .footer a { color: rgba(255,255,255,.8); text-decoration: none; }
        .footer a:hover { color: #fff; }

        @keyframes floatUp {
            from { transform: translateY(12px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        .reveal { animation: floatUp 800ms ease both; }

        @media (max-width: 991.98px) {
            .hero { padding-top: 5.5rem; }
            .hero-card { margin-top: 1.25rem; }
        }
        @media (max-width: 575.98px) {
            .preview-thumb {
                width: 74px;
                height: 74px;
            }
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-green fixed-top">
    <div class="container">
        <a class="navbar-brand text-white fw-bold" href="#">
            <span class="nav-logo-under" aria-hidden="true">
                <img src="{{ asset('images/minsu3.png') }}" alt="MINSU">
            </span>
            &nbsp &nbsp Online Boarding House System
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#landingNav" aria-controls="landingNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon" style="filter: invert(1);"></span>
        </button>
        <div class="collapse navbar-collapse" id="landingNav">
            <ul class="navbar-nav ms-auto gap-lg-3 align-items-lg-center">
                <li class="nav-item"><a class="nav-link" href="#features">Features</a></li>
                <li class="nav-item"><a class="nav-link" href="#students">For Students</a></li>
                <li class="nav-item"><a class="nav-link" href="#landlords">For Landlords</a></li>
                <li class="nav-item"><a class="btn btn-link" href="{{ route('login') }}">Log in</a></li>
                <li class="nav-item"><a class="btn btn-outline-light btn-sm rounded-pill px-3" href="{{ route('register') }}">Sign up</a></li>
            </ul>
        </div>
    </div>
</nav>

<header class="hero">
    <div class="container">
        <div class="row align-items-center g-4">
            <div class="col-12 col-lg-6">
                <span class="hero-kicker mb-3"><i class="bi bi-award"></i>Credited by OFFICE FOR STUDENT SUPPORT AND ENGAGEMENT</span>
                <!-- <div class="hero-credit"><i class="bi bi-award"></i> Credited by OFFICE FOR STUDENT SUPPORT AND ENGAGEMENT</div> -->
                <h1 class="hero-title display-font">Find trusted boarding houses. Book in minutes.</h1>
                <p class="hero-sub mt-3">A modern booking system for students and landlords. Browse verified properties, request rooms, and move in with a guided onboarding flow.</p>
                <div class="hero-cta d-flex flex-wrap gap-2 mt-4">
                    <a href="{{ route('register.student') }}" class="btn btn-brand">Get started</a>
                    <a href="#features" class="btn btn-ghost">See features</a>
                </div>
                <div class="d-flex flex-wrap gap-2 mt-4">
                    <span class="pill"><i class="bi bi-shield-check"></i> Verified listings</span>
                    <span class="pill"><i class="bi bi-geo-alt"></i> Map discovery</span>
                    <span class="pill"><i class="bi bi-chat-dots"></i> Direct messaging</span>
                </div>
            </div>
            <div class="col-12 col-lg-6">
                <div class="hero-card">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="fw-semibold">Featured Rooms</div>
                        <span class="stat-chip"><i class="bi bi-door-open-fill"></i> {{ $availableRooms->count() }} available now</span>
                    </div>
                    <div class="row g-2">
                        @forelse($availableRooms->take(3) as $room)
                            @php
                                $availableSlots = $room->getAvailableSlots();
                                $roomImage = $room->image_path;
                                $propertyImage = $room->property->image_path ?? null;
                                $roomImageExists = !empty($roomImage) && (
                                    \Illuminate\Support\Facades\Storage::disk('public')->exists($roomImage) ||
                                    file_exists(public_path('storage/' . ltrim($roomImage, '/')))
                                );
                                $propertyImageExists = !empty($propertyImage) && (
                                    \Illuminate\Support\Facades\Storage::disk('public')->exists($propertyImage) ||
                                    file_exists(public_path('storage/' . ltrim($propertyImage, '/')))
                                );
                                $previewImage = $roomImageExists ? $roomImage : ($propertyImageExists ? $propertyImage : null);
                            @endphp
                            <div class="col-12">
                                <a href="{{ route('rooms.public.show', $room) }}" class="preview-link" aria-label="View room {{ $room->room_number }} details">
                                    <div class="preview-card">
                                        <div class="d-flex justify-content-between gap-3">
                                            <div class="d-flex gap-3">
                                                @if($previewImage)
                                                    <img src="{{ asset('storage/'.$previewImage) }}" alt="Room {{ $room->room_number }} preview" class="preview-thumb" loading="lazy">
                                                @else
                                                    <div class="preview-thumb placeholder"><i class="bi bi-house-door fs-5"></i></div>
                                                @endif
                                                <div>
                                                    <div class="fw-semibold">Room {{ $room->room_number }}</div>
                                                    <div class="text-muted small">{{ $room->property->name ?? 'Boarding House' }}{{ !empty($room->property?->address) ? ', ' . $room->property->address : '' }}</div>
                                                    <div class="small mt-1"><i class="bi bi-people"></i> Good for {{ $room->capacity }} • {{ $availableSlots > 0 ? $availableSlots . ' slot' . ($availableSlots > 1 ? 's' : '') . ' left' : 'Available' }}</div>
                                                </div>
                                            </div>
                                            <div class="text-end">
                                                <div class="preview-price">₱{{ number_format((float) $room->price, 0) }}</div>
                                                <div class="small text-muted">per month</div>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        @empty
                            <div class="col-12">
                                <div class="preview-card text-center text-muted small">
                                    No available rooms yet.
                                </div>
                            </div>
                        @endforelse
                    </div>
                </div>
                <div class="hero-card mt-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="fw-semibold">Trusted by MINSU community</div>
                            <div class="text-muted small">Secure onboarding and verified landlords.</div>
                        </div>
                        <span class="stat-chip"><i class="bi bi-patch-check"></i> Safe stay</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>

<section class="section" id="features">
    <div class="container">
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-end gap-3 mb-4">
            <div>
                <div class="text-uppercase small text-muted">Why OBHS</div>
                <h2 class="section-title">A modern booking flow built for campus life</h2>
                <p class="section-sub">One platform for discovery, booking, and onboarding. Clear, fast, and transparent.</p>
            </div>
            <a href="{{ route('register.student') }}" class="btn btn-outline-success rounded-pill">Browse boarding houses</a>
        </div>
        <div class="row g-3">
            <div class="col-12 col-md-6 col-xl-3">
                <div class="glass-card h-100 reveal">
                    <div class="feature-icon mb-3"><i class="bi bi-search"></i></div>
                    <h5 class="fw-semibold">Property-first discovery</h5>
                    <p class="text-muted">Find verified boarding houses and then choose rooms that fit your budget.</p>
                </div>
            </div>
            <div class="col-12 col-md-6 col-xl-3">
                <div class="glass-card h-100 reveal" style="animation-delay: .1s;">
                    <div class="feature-icon mb-3"><i class="bi bi-journal-check"></i></div>
                    <h5 class="fw-semibold">Online booking requests</h5>
                    <p class="text-muted">Send requests in seconds and track approval in your dashboard.</p>
                </div>
            </div>
            <div class="col-12 col-md-6 col-xl-3">
                <div class="glass-card h-100 reveal" style="animation-delay: .2s;">
                    <div class="feature-icon mb-3"><i class="bi bi-clipboard-check"></i></div>
                    <h5 class="fw-semibold">Guided onboarding</h5>
                    <p class="text-muted">Upload documents, sign contracts, and pay deposits in one flow.</p>
                </div>
            </div>
            <div class="col-12 col-md-6 col-xl-3">
                <div class="glass-card h-100 reveal" style="animation-delay: .3s;">
                    <div class="feature-icon mb-3"><i class="bi bi-chat-dots"></i></div>
                    <h5 class="fw-semibold">Direct messaging</h5>
                    <p class="text-muted">Students and landlords can communicate instantly and securely.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section" id="properties">
    <div class="container">
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
            <div>
                <h2 class="section-title">Featured Boarding Houses</h2>
                <p class="section-sub">Browse top-rated boarding houses, then choose the room that fits your needs.</p>
            </div>
            <span class="pill"><i class="bi bi-geo-alt"></i> Calapan City</span>
        </div>
        <div class="row g-3">
            @forelse($featuredProperties as $property)
                @php
                    $propertyImage = $property->image_path;
                    $propertyImageExists = !empty($propertyImage) && (
                        \Illuminate\Support\Facades\Storage::disk('public')->exists($propertyImage) ||
                        file_exists(public_path('storage/' . ltrim($propertyImage, '/')))
                    );
                    $rating = $property->average_rating ? number_format((float) $property->average_rating, 1) : null;
                    $minPrice = $property->rooms_min_price;
                @endphp
                <div class="col-12 col-md-6 col-xl-4">
                    <div class="property-card h-100">
                        <div class="property-photo">
                            @if($propertyImageExists)
                                <img src="{{ asset('storage/' . $propertyImage) }}" alt="{{ $property->name }} preview" loading="lazy">
                            @else
                                <i class="bi bi-building fs-2"></i>
                            @endif
                        </div>
                        <div class="p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <h6 class="fw-semibold mb-0">{{ $property->name }}</h6>
                                <span class="pill">{{ $rating ?? 'New' }} ★</span>
                            </div>
                            <div class="text-muted small mt-2">{{ $property->address ?: 'Address not available' }}</div>
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <div class="small text-muted">{{ (int) $property->rooms_count }} rooms • {{ (int) $property->available_rooms_count }} available</div>
                                <div class="fw-bold text-success">{{ $minPrice ? 'From ₱' . number_format((float) $minPrice, 0) : 'Price TBD' }}</div>
                            </div>
                            <div class="mt-3">
                                <a href="{{ route('register.student') }}" class="btn btn-sm btn-outline-success rounded-pill w-100">View property</a>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="glass-card text-center text-muted">
                        No featured properties available yet.
                    </div>
                </div>
            @endforelse
        </div>
    </div>
</section>

<section class="section" id="students">
    <div class="container">
        <div class="role-strip">
            <div class="row align-items-center g-3">
                <div class="col-12 col-lg-7">
                    <h2 class="section-title">For Students</h2>
                    <p class="section-sub">Discover verified boarding houses, compare rooms, and request a booking with confidence.</p>
                    <div class="row g-2 mt-3">
                        <div class="col-12 col-md-6">
                            <div class="step-card glass-card h-100">
                                <h6 class="fw-semibold">Search by location</h6>
                                <p class="text-muted small mb-0">Use the property map and filters to match your budget.</p>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="step-card glass-card h-100">
                                <h6 class="fw-semibold">Request & track</h6>
                                <p class="text-muted small mb-0">Monitor booking status, onboarding progress, and messages.</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-lg-5 text-lg-end">
                    <a href="{{ route('register.student') }}" class="btn btn-brand">Register as Student</a>
                    <a href="{{ route('login') }}" class="btn btn-outline-success ms-2">I already have an account</a>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section" id="landlords">
    <div class="container">
        <div class="role-strip" style="background: linear-gradient(120deg, #f0fdf4, #ecfeff);">
            <div class="row align-items-center g-3">
                <div class="col-12 col-lg-7">
                    <h2 class="section-title">For Landlords</h2>
                    <p class="section-sub">Manage properties, rooms, bookings, and onboarding with a clean workflow.</p>
                    <div class="row g-2 mt-3">
                        <div class="col-12 col-md-6">
                            <div class="step-card glass-card h-100">
                                <h6 class="fw-semibold">Publish listings</h6>
                                <p class="text-muted small mb-0">Add properties and rooms in minutes and keep availability updated.</p>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="step-card glass-card h-100">
                                <h6 class="fw-semibold">Approve & onboard</h6>
                                <p class="text-muted small mb-0">Review documents, approve bookings, and track tenants.</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-lg-5 text-lg-end">
                    <a href="{{ route('register.landlord') }}" class="btn btn-brand">Register as Landlord</a>
                    <a href="{{ route('login') }}" class="btn btn-outline-success ms-2">Log in</a>
                </div>
            </div>
        </div>
    </div>
</section>

<footer class="footer">
    <div class="container">
        <div class="row g-3">
            <div class="col-12 col-lg-6">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <img src="{{ asset('images/minsu3.png') }}" alt="MINSU" style="width:30px;height:30px;object-fit:contain;">
                    <div class="fw-semibold text-white">Online Boarding House System</div>
                </div>
                <div class="small">A centralized platform for MINSU students and landlords.</div>
                <div class="small mt-1">Credited by: OFFICE FOR STUDENT SUPPORT AND ENGAGEMENT</div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="fw-semibold text-white mb-2">Quick Links</div>
                <div class="small d-flex flex-column gap-1">
                    <a href="#features">Features</a>
                    <a href="#students">For Students</a>
                    <a href="#landlords">For Landlords</a>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="fw-semibold text-white mb-2">Access</div>
                <div class="small d-flex flex-column gap-1">
                    <a href="{{ route('login') }}">Log in</a>
                    <a href="{{ route('register') }}">Sign up</a>
                </div>
            </div>
        </div>
        <div class="text-center small mt-4">© 2026 Online Boarding House System</div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
