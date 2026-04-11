<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Online Boarding House System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Bricolage+Grotesque:opsz,wght@12..96,600;12..96,700&display=swap" rel="stylesheet">
    <style>
        :root {
            --brand: #14532d;
            --brand-2: #166534;
            --brand-rgb: 34,197,94;
            --premium-emerald: #10b981;
            --premium-emerald-deep: #047857;
            --premium-gold: #f4b740;
            --premium-gold-deep: #d18a00;
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
        .auth-wrapper {
            min-height: 100vh;
            position: relative;
            overflow: hidden;
        }
        .auth-wrapper:before {
            content: "";
            position: absolute;
            inset: 0;
            background-image: url("{{ asset('images/MinSU-Calapan.jpg') }}");
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            filter: saturate(1.08) contrast(1.08);
            transform: scale(1.01);
        }
        .auth-wrapper:after {
            content: "";
            position: absolute;
            inset: 0;
            background:
                radial-gradient(900px circle at 10% 0%, rgba(var(--brand-rgb), .30), transparent 55%),
                radial-gradient(800px circle at 100% 18%, rgba(var(--brand-rgb), .18), transparent 50%),
                linear-gradient(120deg, rgba(2,8,20,.60), rgba(2,8,20,.26));
        }
        .auth-wrapper > .row { position: relative; z-index: 1; }
        .hero-pane {
            background: transparent;
            position: relative;
            min-height: 240px;
        }
        .hero-content {
            position: relative;
            z-index: 1;
            color: #fff;
            max-width: 620px;
        }
        .hero-content h1 { text-shadow: 0 12px 30px rgba(2,8,20,.35); }
        .hero-content p { text-shadow: 0 10px 24px rgba(2,8,20,.28); }
        .hero-brand-block {
            display: flex;
            align-items: center;
            gap: .92rem;
            margin-top: .5rem;
            margin-bottom: 1rem;
        }
        .hero-brand-link {
            text-decoration: none;
            color: inherit;
        }
        .hero-brand-link:hover {
            text-decoration: none;
            color: inherit;
        }
        .hero-brand-logos {
            display: inline-flex;
            align-items: center;
            gap: .55rem;
            flex: 0 0 auto;
        }
        .hero-brand-logos img {
            width: 58px;
            height: 58px;
            object-fit: contain;
            filter: drop-shadow(0 6px 12px rgba(2,8,20,.26));
        }
        .hero-brand-copy {
            display: flex;
            flex-direction: column;
            gap: .14rem;
            min-width: 0;
            line-height: 1.04;
        }
        .hero-brand-top {
            font-size: 1.32rem;
            font-weight: 800;
            color: #ffffff;
            letter-spacing: .01em;
        }
        .hero-brand-bottom {
            font-size: 1.03rem;
            font-weight: 700;
            color: rgba(236,253,245,.92);
            letter-spacing: .01em;
        }
        .hero-list { margin-top: 1.25rem; }
        .hero-list li { display: flex; align-items: flex-start; gap: .7rem; margin-bottom: .65rem; }
        .hero-list .hero-ic {
            width: 34px;
            height: 34px;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(255,255,255,.12);
            border: 1px solid rgba(255,255,255,.18);
            color: rgba(255,255,255,.92);
            flex: 0 0 auto;
            margin-top: .05rem;
        }
        .hero-stats {
            display: flex;
            flex-wrap: wrap;
            gap: .48rem;
            margin-top: 1rem;
        }
        .hero-stat-chip {
            display: inline-flex;
            align-items: center;
            gap: .38rem;
            font-size: .73rem;
            font-weight: 700;
            color: #ecfdf5;
            border-radius: 999px;
            padding: .32rem .62rem;
            border: 1px solid rgba(255,255,255,.24);
            background: rgba(255,255,255,.14);
        }
        .card {
            border: 1px solid rgba(255,255,255,.22);
            background: rgba(255,255,255,.12);
            box-shadow: 0 22px 55px rgba(2,8,20,.18);
            overflow: hidden;
            backdrop-filter: blur(12px) saturate(1.10);
            -webkit-backdrop-filter: blur(12px) saturate(1.10);
            color: #fff;
        }

        .auth-card {
            border-radius: 1.25rem;
            border-color: rgba(167,243,208,.26);
            box-shadow: 0 28px 58px rgba(2,8,20,.28);
        }
        .card .text-muted { color: rgba(255,255,255,.74) !important; }
        .form-label { font-weight: 600; color: rgba(255,255,255,.88); }
        .field-icon {
            background: rgba(255,255,255,.12);
            color: rgba(255,255,255,.88);
            width: 40px;
            height: 40px;
            display:inline-flex;
            align-items:center;
            justify-content:center;
            border-radius: .5rem;
        }
        .input-group-text.field-icon { border-color: rgba(255,255,255,.18); }
        .form-control {
            background: rgba(255,255,255,.10);
            border-color: rgba(255,255,255,.22);
            color: #fff;
            min-height: 42px;
        }
        .form-control::placeholder { color: rgba(255,255,255,.55); }
        .form-check-label { color: rgba(255,255,255,.82); }
        .card a { color: #d1fae5; }
        .card a:hover { color: #ecfdf5; }

        .auth-logo {
            width: 56px;
            height: 56px;
            border-radius: 18px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(var(--brand-rgb), .12);
            border: 1px solid rgba(var(--brand-rgb), .20);
            color: #ecfdf5;
            box-shadow: 0 16px 34px rgba(2,8,20,.10);
        }
        .form-control:focus {
            border-color: rgba(16,185,129,.88);
            box-shadow: 0 0 0 .25rem rgba(16,185,129,.2);
            background: rgba(255,255,255,.14);
        }
        .btn-brand {
            position: relative;
            overflow: hidden;
            isolation: isolate;
            border: 1px solid rgba(16, 185, 129, .9);
            background: linear-gradient(125deg, var(--premium-emerald) 0%, var(--premium-emerald-deep) 100%);
            color: #ecfdf5;
            font-weight: 700;
            border-radius: 999px;
            box-shadow: 0 10px 24px rgba(4, 120, 87, .34);
            transition: transform .2s ease, box-shadow .24s ease, background .24s ease, border-color .24s ease;
        }
        .btn-brand::after {
            content: "";
            position: absolute;
            top: -130%;
            bottom: -130%;
            left: -45%;
            width: 38%;
            background: linear-gradient(120deg, transparent 10%, rgba(255,255,255,.52) 50%, transparent 90%);
            transform: translateX(-190%) rotate(18deg);
            transition: transform .55s ease;
            pointer-events: none;
        }
        .btn-brand:hover {
            background: linear-gradient(125deg, #0ea770 0%, #046246 100%);
            border-color: #0ea770;
            color: #fff;
            transform: translateY(-1px);
            box-shadow: 0 16px 34px rgba(4, 120, 87, .44);
        }
        .btn-brand:hover::after {
            transform: translateX(430%) rotate(18deg);
        }
        .btn-brand:focus-visible {
            outline: 2px solid rgba(255,255,255,.72);
            outline-offset: 2px;
        }
        .auth-links {
            border-top: 1px solid rgba(255,255,255,.16);
            margin-top: 1rem;
            padding-top: .9rem;
            display: flex;
            justify-content: center;
        }
        .auth-home-link {
            display: inline-flex;
            align-items: center;
            gap: .34rem;
            text-decoration: none;
            font-size: .82rem;
            font-weight: 700;
            color: #ecfdf5;
            opacity: .9;
            transition: opacity .2s ease, transform .2s ease;
        }
        .auth-home-link:hover {
            opacity: 1;
            transform: translateX(-1px);
            color: #fff;
        }

        @media (min-width: 992px) {
            .hero-pane { min-height: 100vh; }
        }
        @media (max-width: 991.98px) {
            .hero-pane {
                min-height: 0;
                padding-bottom: 0 !important;
            }
            .hero-content {
                max-width: none;
            }
            .hero-list {
                margin-top: .95rem;
            }
        }
        @media (max-width: 575.98px) {
            .hero-mobile-cta {
                display: none !important;
            }
            .hero-content h1 {
                font-size: clamp(1.85rem, 9.5vw, 2.25rem);
            }
            .hero-brand-block {
                width: 100%;
                justify-content: flex-start;
                gap: .62rem;
                margin-bottom: .85rem;
            }
            .hero-brand-logos img {
                width: 46px;
                height: 46px;
            }
            .hero-brand-top {
                font-size: 1.06rem;
            }
            .hero-brand-bottom {
                font-size: .85rem;
            }
            .hero-content p {
                font-size: .94rem;
            }
            .hero-content {
                padding-bottom: .35rem !important;
            }
            .hero-list li {
                margin-bottom: .5rem;
                gap: .56rem;
            }
            .hero-list .hero-ic {
                width: 30px;
                height: 30px;
                border-radius: 10px;
            }
            .hero-stat-chip {
                font-size: .68rem;
                padding: .28rem .5rem;
            }
            .card .card-body {
                padding: 1.2rem 1rem !important;
            }
        }
    </style>
    <noscript>
        <style>
            .auth-wrapper:before { background-image: url("{{ asset('images/MinSU-Calapan.jpg') }}"); }
        </style>
    </noscript>
    <script>
        window.addEventListener('load', function(){
            const img = new Image();
            img.onerror = () => document.querySelector('.auth-wrapper')?.classList.add('bg-gradient');
            img.src = "{{ asset('images/MinSU-Calapan.jpg') }}";
        });
    </script>
</head>
<body class="bg-dark">
    <div class="container-fluid auth-wrapper">
        <div class="row g-0 h-100">
            <!-- Left hero pane with building image -->
            <div class="col-12 col-lg-6 hero-pane">
                <div class="d-flex flex-column justify-content-between h-100 p-4 p-lg-5 hero-content">
                    <div>
                        <a href="{{ route('landing') }}" class="hero-brand-block hero-brand-link" role="note" aria-label="Mindoro State University Online Boarding House System">
                            <span class="hero-brand-logos" aria-hidden="true">
                                <img src="{{ asset('images/MinSU_logo.png') }}" alt="Mindoro State University logo" loading="lazy">
                                <img src="{{ asset('images/OSSE-main.png') }}" alt="OSSE logo" loading="lazy">
                            </span>
                            <span class="hero-brand-copy">
                                <span class="hero-brand-top">Mindoro State University</span>
                                <span class="hero-brand-bottom">Online Boarding House System</span>
                            </span>
                        </a>
                        <h1 class="display-font display-6 fw-bold mb-2 hero-mobile-cta">Log in to your account</h1>
                        <p class="mb-0 opacity-75 hero-mobile-cta">Sign in to track bookings, onboarding progress, and room updates in one premium workflow.</p>

                        <ul class="list-unstyled hero-list mb-0 opacity-90 hero-mobile-cta">
                            <li>
                                <span class="hero-ic"><i class="bi bi-house-door"></i></span>
                                <div>
                                    <div class="fw-semibold">Browse listings faster</div>
                                    <div class="small opacity-75">Find available rooms and boarding houses in seconds.</div>
                                </div>
                            </li>
                            <li>
                                <span class="hero-ic"><i class="bi bi-chat-dots"></i></span>
                                <div>
                                    <div class="fw-semibold">Message in one place</div>
                                    <div class="small opacity-75">Keep conversations organized with built-in messaging.</div>
                                </div>
                            </li>
                            <li>
                                <span class="hero-ic"><i class="bi bi-shield-check"></i></span>
                                <div>
                                    <div class="fw-semibold">Secure access</div>
                                    <div class="small opacity-75">Your account and requests are protected.</div>
                                </div>
                            </li>
                        </ul>
                        <div class="hero-stats hero-mobile-cta">
                            <span class="hero-stat-chip"><i class="bi bi-patch-check"></i> Verified properties</span>
                            <span class="hero-stat-chip"><i class="bi bi-lightning-charge"></i> Fast request flow</span>
                            <span class="hero-stat-chip"><i class="bi bi-geo-alt"></i> Campus-ready map</span>
                        </div>
                    </div>
                    <div class="opacity-75 small d-none d-lg-block">Online Boarding House System</div>
                </div>
            </div>

            <!-- Right form pane -->
            <div class="col-lg-6 d-flex align-items-center justify-content-center p-4 p-lg-5">
                <div class="w-100" style="max-width: 520px;">
                    <div class="card auth-card">
                        <div class="card-body p-4 p-lg-5">
                            <div class="mb-4 text-center">
                                <h2 class="display-font fw-bold mb-1">Sign in</h2>
                                <p class="text-muted mb-0">New here? <a href="{{ route('register') }}" class="text-white text-decoration-none fw-semibold mb-0">Create an account</a></p>
                                <!-- <p class="text-muted mb-0 small mt-1">Need admin access? <a href="{{ route('register.admin') }}" class="text-white text-decoration-none fw-semibold">Register admin</a></p> -->
                            </div>

                            @if(session('success'))
                                <div class="alert alert-success">{{ session('success') }}</div>
                            @endif
                            @if($errors->any())
                                <div class="alert alert-danger">
                                    <ul class="mb-0">
                                        @foreach($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <form method="POST" action="{{ route('login') }}" novalidate>
                                @csrf
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label for="email" class="form-label">Email</label>
                                        <div class="input-group">
                                            <span class="input-group-text field-icon"><i class="bi bi-envelope"></i></span>
                                            <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" placeholder="you@example.com" required>
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <label for="password" class="form-label">Password</label>
                                        <div class="input-group">
                                            <span class="input-group-text field-icon"><i class="bi bi-lock"></i></span>
                                            <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" required autocomplete="current-password">
                                            <span class="input-group-text field-icon" id="toggle_password" role="button" tabindex="0" aria-label="Show password" aria-controls="password">
                                                <i class="bi bi-eye" aria-hidden="true"></i>
                                            </span>
                                        </div>
                                    </div>

                                    <div class="col-12 d-flex justify-content-between align-items-center">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="remember" name="remember">
                                            <label class="form-check-label" for="remember">Remember me</label>
                                        </div>
                                        <a class="small text-white text-decoration-none" href="#" onclick="return false; ">Forgot password?</a>
                                    </div>

                                    <div class="col-12 mt-2">
                                        <button type="submit" class="btn btn-brand btn-lg w-100">Sign in</button>
                                    </div>
                                </div>
                            </form>
                            <div class="auth-links">
                                <a href="{{ route('landing') }}" class="auth-home-link" aria-label="Back to home page">
                                    <i class="bi bi-arrow-left"></i> Back to home
                                </a>
                            </div>
                        </div>
                    </div>
                    <p class="text-center text-white mt-3 mb-0 small">By signing in, you agree to our Terms and Privacy Policy.</p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        (function () {
            const passwordInput = document.getElementById('password');
            const toggle = document.getElementById('toggle_password');
            if (!passwordInput || !toggle) return;

            const icon = toggle.querySelector('i');

            const applyState = (isVisible) => {
                passwordInput.type = isVisible ? 'text' : 'password';
                if (icon) {
                    icon.classList.toggle('bi-eye', !isVisible);
                    icon.classList.toggle('bi-eye-slash', isVisible);
                }
                toggle.setAttribute('aria-label', isVisible ? 'Hide password' : 'Show password');
            };

            const toggleVisibility = () => {
                applyState(passwordInput.type === 'password');
            };

            toggle.addEventListener('click', toggleVisibility);
            toggle.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    toggleVisibility();
                }
            });
        })();
    </script>
</body>
</html>