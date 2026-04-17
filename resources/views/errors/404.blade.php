<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>404 Page Not Found | OBHS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700&family=Bricolage+Grotesque:opsz,wght@12..96,600;12..96,700&display=swap" rel="stylesheet">
    <style>
        :root {
            --brand: #14532d;
            --brand-2: #166534;
            --brand-rgb: 34, 197, 94;
            --premium-emerald: #10b981;
            --premium-emerald-deep: #047857;
            --ink: #0f172a;
            --paper: #f8fafc;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            font-family: 'Manrope', system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
            color: var(--ink);
            background: var(--paper);
        }

        h1, h2, h3, .display-font {
            font-family: 'Bricolage Grotesque', 'Manrope', system-ui, sans-serif;
        }

        .navbar-green {
            background: linear-gradient(180deg, #1a5c2e 0%, #2d8a4e 60%, #3aaf65 100%);
            box-shadow: 0 2px 16px rgba(0, 0, 0, .3);
            overflow: visible !important;
        }

        .navbar-green .nav-link {
            color: rgba(255, 255, 255, .92);
            font-weight: 600;
            letter-spacing: .01em;
        }

        .navbar-green .nav-link:hover {
            color: #fff;
            text-decoration: underline;
            text-underline-offset: 3px;
        }

        .navbar-green .btn-link {
            color: rgba(255, 255, 255, .92) !important;
            font-weight: 600;
        }

        .navbar-green .btn-link:hover {
            color: #fff !important;
        }

        .navbar-green .navbar-toggler {
            border: 0;
            outline: 0;
            box-shadow: none !important;
        }

        .navbar-green .navbar-toggler .hamburger-icon {
            width: 30px;
            height: 22px;
            display: inline-flex;
            flex-direction: column;
            justify-content: center;
            gap: 5px;
        }

        .navbar-green .navbar-toggler .hamburger-icon span {
            display: block;
            height: 2.5px;
            border-radius: 999px;
            background: rgba(255, 255, 255, .95);
            transform-origin: center;
            transition: transform .28s ease, opacity .2s ease, width .22s ease;
            align-self: flex-end;
        }

        .navbar-green .navbar-toggler .hamburger-icon span:nth-child(1) {
            width: 100%;
        }

        .navbar-green .navbar-toggler .hamburger-icon span:nth-child(2) {
            width: 66.6667%;
        }

        .navbar-green .navbar-toggler .hamburger-icon span:nth-child(3) {
            width: 33.3333%;
        }

        .navbar-green .navbar-toggler[aria-expanded="true"] .hamburger-icon span:nth-child(1) {
            width: 100%;
            align-self: center;
            transform: translateY(7.5px) rotate(45deg);
        }

        .navbar-green .navbar-toggler[aria-expanded="true"] .hamburger-icon span:nth-child(2) {
            opacity: 0;
            width: 0;
        }

        .navbar-green .navbar-toggler[aria-expanded="true"] .hamburger-icon span:nth-child(3) {
            width: 100%;
            align-self: center;
            transform: translateY(-7.5px) rotate(-45deg);
        }

        .navbar-green .navbar-brand {
            position: relative;
            padding-left: 100px;
            margin-left: 0;
        }

        .navbar-brand-text {
            display: inline-flex;
            flex-direction: column;
            justify-content: center;
            align-items: flex-start;
            gap: .12rem;
            line-height: 1;
            vertical-align: middle;
        }

        .navbar-brand-text .brand-line-top {
            font-size: 1.35rem;
            font-weight: 900;
            color: #ffffff;
            line-height: 1;
        }

        .navbar-brand-text .brand-line-bottom {
            font-size: .86rem;
            font-weight: 700;
            color: rgba(236, 253, 245, .86);
            line-height: 1;
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
            filter: drop-shadow(0 5px 10px rgba(0, 0, 0, .45));
        }

        .not-found-main {
            flex: 1;
            padding-top: 7.1rem;
            background: linear-gradient(160deg, #f8fafc 0%, #eef2f7 100%);
        }

        .not-found-shell {
            max-width: 1080px;
        }

        .not-found-panel {
            border: 1px solid rgba(2, 8, 20, .08);
            border-radius: 1.45rem;
            background: #ffffff;
            box-shadow: 0 24px 50px rgba(2, 8, 20, .12);
            padding: clamp(1rem, 2.5vw, 2rem);
            overflow: hidden;
        }

        .not-found-row {
            --bs-gutter-x: clamp(.45rem, 1.2vw, .95rem);
        }

        .error-kicker {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            border-radius: 999px;
            background: #dcfce7;
            border: 1px solid rgba(22, 163, 74, .24);
            color: #166534;
            padding: .32rem .68rem;
            font-size: .74rem;
            font-weight: 700;
            letter-spacing: .03em;
            text-transform: uppercase;
        }

        .error-title {
            margin-top: .95rem;
            margin-bottom: .7rem;
            font-size: clamp(2rem, 4.2vw, 3.4rem);
            line-height: 1.02;
        }

        .error-copy {
            color: #475569;
            font-size: 1rem;
            line-height: 1.62;
            max-width: 32ch;
        }

        .error-actions {
            margin-top: 1.1rem;
            display: flex;
            flex-wrap: wrap;
            gap: .62rem;
        }

        .btn-pill {
            border-radius: 999px;
            padding: .62rem 1.18rem;
            font-weight: 700;
            border: 1px solid transparent;
            transition: transform .2s ease, box-shadow .2s ease, background .2s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: .4rem;
            font-size: .92rem;
        }

        .btn-go-home {
            color: #ecfdf5;
            background: linear-gradient(125deg, var(--premium-emerald) 0%, var(--premium-emerald-deep) 100%);
            box-shadow: 0 12px 24px rgba(4, 120, 87, .28);
        }

        .btn-go-home:hover {
            color: #ffffff;
            transform: translateY(-1px);
            box-shadow: 0 18px 30px rgba(4, 120, 87, .36);
        }

        .btn-back {
            color: #14532d;
            background: #f0fdf4;
            border-color: rgba(22, 101, 52, .24);
        }

        .btn-back:hover {
            color: #14532d;
            background: #dcfce7;
            transform: translateY(-1px);
        }

        .error-illustration {
            min-height: 340px;
            display: flex;
            justify-content: flex-start;
            align-items: center;
            padding: 0;
        }

        .error-illustration-img {
            width: 100%;
            max-width: 100%;
            height: auto;
            object-fit: contain;
            display: block;
            margin-left: 0;
            background: transparent;
            border: 0;
            border-radius: 0;
            padding: 0;
            box-shadow: none;
            filter: none;
            transform: none;
        }

        .footer {
            background: #0b1220;
            color: rgba(255, 255, 255, .8);
            padding: 2.6rem 0 1.6rem;
            margin-top: auto;
        }

        .footer a {
            color: rgba(255, 255, 255, .82);
            text-decoration: none;
        }

        .footer a:hover {
            color: #ffffff;
        }

        .footer-brand {
            display: flex;
            align-items: center;
            gap: .72rem;
        }

        .footer-brand-logos {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            flex: 0 0 auto;
        }

        .footer-brand-logo {
            width: 44px;
            height: 44px;
            border-radius: 999px;
            border: 1px solid rgba(255, 255, 255, .38);
            object-fit: cover;
            background: rgba(255, 255, 255, .08);
        }

        .footer-brand-title {
            color: #ffffff;
            font-size: 1.35rem;
            font-weight: 800;
            letter-spacing: .01em;
            line-height: 1.05;
        }

        .footer-brand-subtitle {
            color: rgba(236, 253, 245, .92);
            font-size: .9rem;
            font-weight: 700;
        }

        .newsletter-card {
            border-radius: 1rem;
            border: 1px solid rgba(255, 255, 255, .12);
            background: rgba(255, 255, 255, .04);
            padding: .8rem;
        }

        .newsletter-input {
            border: 1px solid rgba(255, 255, 255, .2);
            background: rgba(15, 23, 42, .58);
            color: #ffffff;
            border-radius: 999px;
            padding: .52rem .85rem;
            width: 100%;
            outline: none;
        }

        .newsletter-input::placeholder {
            color: rgba(255, 255, 255, .55);
        }

        .newsletter-btn {
            border: 1px solid rgba(16, 185, 129, .65);
            background: linear-gradient(125deg, var(--premium-emerald), var(--premium-emerald-deep));
            color: #ecfdf5;
            border-radius: 999px;
            font-weight: 700;
            padding: .52rem .92rem;
            white-space: nowrap;
        }

        @media (max-width: 991.98px) {
            .navbar-green .navbar-brand {
                padding-left: 84px;
            }

            .navbar-brand-text .brand-line-top {
                font-size: 1.08rem;
            }

            .navbar-brand-text .brand-line-bottom {
                font-size: .7rem;
            }

            #landingNav .navbar-nav {
                margin-left: auto;
                width: fit-content;
                align-items: flex-end;
                text-align: right;
                padding-top: .5rem;
            }

            #landingNav .btn {
                margin-left: auto;
            }

            .nav-logo-under img {
                height: 68px;
                width: 68px;
                margin-bottom: -12px;
            }

            .error-illustration {
                min-height: 290px;
                justify-content: center;
            }

            .error-illustration-img {
                margin-left: 0;
            }
        }

        @media (max-width: 575.98px) {
            .not-found-main {
                padding-top: 5.7rem;
            }

            .not-found-panel {
                border-radius: 1rem;
                padding: .9rem;
            }

            .error-copy {
                max-width: 100%;
                font-size: .95rem;
            }

            .error-actions {
                flex-direction: column;
            }

            .btn-pill {
                width: 100%;
            }

            .error-illustration {
                min-height: 240px;
            }

            .footer-brand-logo {
                width: 38px;
                height: 38px;
            }
        }
    </style>
</head>
<body>
<x-public-topnav />

<main class="not-found-main">
    <section class="container pb-5 not-found-shell">
        <div class="not-found-panel">
            <div class="row g-4 align-items-center not-found-row">
                <div class="col-12 col-lg-5">
                    <span class="error-kicker"><i class="bi bi-compass"></i>404 Error</span>
                    <h1 class="error-title">Page not found</h1>
                    <p class="error-copy">Not all who wander are lost, but it looks like this link took you to an unknown route. You can head back to a known OBHS page below.</p>

                    <div class="error-actions">
                        <a class="btn-pill btn-go-home" href="{{ route('landing') }}">
                            <i class="bi bi-house-door-fill"></i>
                            Go Home
                        </a>
                        <a class="btn-pill btn-back" href="{{ url()->previous() }}">
                            <i class="bi bi-arrow-left"></i>
                            Go Back
                        </a>
                    </div>
                </div>
                <div class="col-12 col-lg-7">
                    <figure class="error-illustration mb-0">
                        <img
                            class="error-illustration-img"
                            src="{{ asset('images/ERROR 404.gif') }}"
                            alt="Error 404 illustration"
                            loading="lazy"
                            decoding="async"
                        >
                    </figure>
                </div>
            </div>
        </div>
    </section>
</main>

<footer class="footer">
    <div class="container">
        <div class="row g-4">
            <div class="col-12 col-lg-5">
                <div class="footer-brand">
                    <div class="footer-brand-logos" aria-hidden="true">
                        <img src="{{ asset('images/MinSU_logo.png') }}" alt="MINSU logo" class="footer-brand-logo" loading="lazy">
                        <img src="{{ asset('images/OSSE-main.png') }}" alt="OSSE logo" class="footer-brand-logo" loading="lazy">
                    </div>
                    <div>
                        <div class="footer-brand-title">Mindoro State University</div>
                        <div class="footer-brand-subtitle">Online Boarding House System</div>
                    </div>
                </div>
                <p class="small mt-3 mb-0">An institution-aligned housing platform for students and landlords, built for transparent booking and onboarding.</p>
            </div>
            <div class="col-6 col-lg-2">
                <div class="fw-semibold text-white mb-2">Explore</div>
                <div class="small d-flex flex-column gap-1">
                    <a href="{{ route('landing') }}#features">Features</a>
                    <a href="{{ route('landing') }}#students">Students</a>
                    <a href="{{ route('landing') }}#landlords">Landlords</a>
                </div>
            </div>
            <div class="col-6 col-lg-2">
                <div class="fw-semibold text-white mb-2">Account</div>
                <div class="small d-flex flex-column gap-1">
                    <a href="{{ route('login') }}">Log in</a>
                    <a href="{{ route('register') }}">Sign up</a>
                </div>
            </div>
            <div class="col-12 col-lg-3">
                <div class="fw-semibold text-white mb-2">Stay updated</div>
                <div class="newsletter-card">
                    <div class="d-flex gap-2 flex-column flex-sm-row">
                        <input class="newsletter-input" type="email" placeholder="name@email.com" aria-label="Email">
                        <button type="button" class="newsletter-btn">Subscribe</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="small text-center mt-4">© 2026 Online Boarding House System</div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
