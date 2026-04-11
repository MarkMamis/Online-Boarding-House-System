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
            --brand-rgb: 34,197,94;
            --mint: #a7f3d0;
            --gold: #f59e0b;
            --premium-emerald: #10b981;
            --premium-emerald-deep: #047857;
            --premium-gold: #f4b740;
            --premium-gold-deep: #d18a00;
            --premium-ink: #052e16;
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
        .navbar-green .navbar-toggler {
            border: 0;
            outline: 0;
            box-shadow: none !important;
        }
        .navbar-green .navbar-toggler:focus,
        .navbar-green .navbar-toggler:active {
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
            background: rgba(255,255,255,.95);
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
            letter-spacing: .01em;
            text-transform: none;
            color: #ffffff;
            line-height: 1;
        }
        .navbar-brand-text .brand-line-bottom {
            font-size: .86rem;
            font-weight: 700;
            letter-spacing: .03em;
            text-transform: none;
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
            filter: drop-shadow(0 5px 10px rgba(0,0,0,.45));
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
            #landingNav .nav-item {
                width: 100%;
            }
            #landingNav .btn {
                margin-left: auto;
            }
            .nav-logo-under {
                top: -9px;
            }
            .nav-logo-under img {
                height: 68px;
                width: 68px;
                margin-bottom: -12px;
            }
        }

        .hero {
            position: relative;
            overflow: hidden;
            min-height: 100svh;
            display: flex;
            align-items: center;
            padding-top: 6.5rem;
            padding-bottom: 4rem;
            background: #0c3d20;
            color: #fff;
        }
        .hero .container {
            width: 100%;
        }
        .hero::before {
            content: "";
            position: absolute;
            inset: 0;
            background: url("{{ asset('images/MinSU-Calapan.jpg') }}") center/cover no-repeat;
            filter: saturate(1.08) contrast(1.08);
            transform: scale(1.01);
        }
        .hero::after {
            content: "";
            position: absolute;
            inset: 0;
            background:
                radial-gradient(900px circle at 10% 0%, rgba(var(--brand-rgb), .30), transparent 55%),
                radial-gradient(800px circle at 100% 18%, rgba(var(--brand-rgb), .18), transparent 50%),
                linear-gradient(120deg, rgba(2,8,20,.60), rgba(2,8,20,.26));
        }
        .hero > * { position: relative; z-index: 1; }
        .hero-accreditation {
            display: inline-flex;
            align-items: center;
            gap: .72rem;
            margin-bottom: .95rem;
            max-width: min(100%, 760px);
        }
        .hero-accreditation-logo {
            width: 52px;
            height: 52px;
            object-fit: contain;
            flex: 0 0 auto;
            filter: drop-shadow(0 6px 12px rgba(2,8,20,.32));
        }
        .hero-accreditation-copy {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: .2rem;
            line-height: 1.1;
        }
        .hero-accreditation-label {
            font-size: .7rem;
            font-weight: 800;
            letter-spacing: .13em;
            text-transform: uppercase;
            color: rgba(236, 253, 245, .88);
            text-shadow: 0 2px 8px rgba(2,8,20,.28);
        }
        .hero-accreditation-title {
            font-size: 1.08rem;
            font-weight: 800;
            letter-spacing: .02em;
            color: #ffffff;
            text-shadow: 0 4px 12px rgba(2,8,20,.36);
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
        .hero-cta .btn {
            position: relative;
            overflow: hidden;
            isolation: isolate;
            padding: .8rem 1.4rem;
            font-weight: 700;
            border-radius: 999px;
            transform: translateY(0) scale(1);
            transition: transform .22s ease, box-shadow .24s ease, background .22s ease, border-color .22s ease, color .22s ease;
        }
        .hero-cta .btn::after {
            content: "";
            position: absolute;
            top: -130%;
            bottom: -130%;
            left: -45%;
            width: 38%;
            background: linear-gradient(120deg, transparent 10%, rgba(255,255,255,.55) 50%, transparent 90%);
            transform: translateX(-190%) rotate(18deg);
            transition: transform .55s ease;
            pointer-events: none;
            z-index: 1;
        }
        .hero-cta .btn:hover {
            transform: translateY(-2px) scale(1.02);
        }
        .hero-cta .btn:hover::after {
            transform: translateX(430%) rotate(18deg);
        }
        .hero-cta .btn:active {
            transform: translateY(0) scale(.985);
        }
        .hero-cta .btn:focus-visible {
            outline: 2px solid rgba(255,255,255,.72);
            outline-offset: 2px;
        }
        .btn-brand {
            background: linear-gradient(125deg, var(--premium-emerald) 0%, var(--premium-emerald-deep) 100%);
            border-color: rgba(16, 185, 129, .9);
            color: #ecfdf5;
            box-shadow: 0 10px 24px rgba(4, 120, 87, .34);
        }
        .btn-brand:hover {
            background: linear-gradient(125deg, #0ea770 0%, #046246 100%);
            border-color: #0ea770;
            color: #fff;
            box-shadow: 0 16px 34px rgba(4, 120, 87, .45);
        }
        .btn-ghost {
            border: 1px solid rgba(244, 183, 64, .55);
            color: #fff8e8;
            background: linear-gradient(125deg, rgba(244, 183, 64, .2), rgba(244, 183, 64, .08));
            backdrop-filter: blur(1.5px);
            -webkit-backdrop-filter: blur(1.5px);
            box-shadow: 0 8px 20px rgba(2,8,20,.24);
        }
        .btn-ghost:hover {
            background: linear-gradient(125deg, rgba(244, 183, 64, .32), rgba(244, 183, 64, .18));
            border-color: rgba(255,255,255,.74);
            color: #fff;
            box-shadow: 0 14px 30px rgba(2,8,20,.32);
        }
        .btn-outline-success {
            border-color: rgba(4, 120, 87, .42);
            color: #0f5132;
            transition: transform .2s ease, box-shadow .22s ease, background .22s ease, color .22s ease, border-color .22s ease;
        }
        .btn-outline-success:hover,
        .btn-outline-success:focus-visible {
            background: linear-gradient(125deg, var(--premium-emerald) 0%, var(--premium-emerald-deep) 100%);
            border-color: rgba(4, 120, 87, .85);
            color: #ecfdf5;
            transform: translateY(-1px);
            box-shadow: 0 10px 24px rgba(4, 120, 87, .24);
        }

        .hero-card {
            background: rgba(255,255,255,.12);
            border-radius: 1.5rem;
            border: 1px solid rgba(255,255,255,.22);
            box-shadow: 0 22px 55px rgba(2,8,20,.22);
            backdrop-filter: blur(12px) saturate(1.10);
            -webkit-backdrop-filter: blur(12px) saturate(1.10);
            padding: 1.3rem;
            animation: floatUp 900ms ease both;
            color: #fff;
        }
        .hero-card .text-muted { color: rgba(255,255,255,.74) !important; }
        .hero-card .preview-card { color: #fff; }
        .hero-card + .hero-card { margin-top: 1rem; }
        .stat-chip {
            display: inline-flex; align-items: center; gap: .4rem;
            background: rgba(255,255,255,.14);
            color: #f0fdf4;
            border: 1px solid rgba(255,255,255,.24);
            font-weight: 700;
            padding: .35rem .65rem; border-radius: 999px; font-size: .75rem;
        }
        .trusted-card {
            cursor: pointer;
            transition: transform .26s ease, box-shadow .26s ease, border-color .26s ease, background .26s ease;
        }
        .trusted-card .stat-chip,
        .trusted-card .stat-chip i,
        .trusted-card-hint,
        .trusted-card-hint i {
            transition: transform .22s ease, box-shadow .22s ease, opacity .22s ease, color .22s ease;
        }
        .trusted-card:hover,
        .trusted-card:focus-visible,
        .trusted-card:focus-within {
            transform: translateY(-4px);
            border-color: rgba(167,243,208,.44);
            background: rgba(255,255,255,.16);
            box-shadow: 0 20px 34px rgba(2,8,20,.28);
        }
        .trusted-card:hover .stat-chip,
        .trusted-card:focus-visible .stat-chip,
        .trusted-card:focus-within .stat-chip {
            box-shadow: 0 8px 18px rgba(2,8,20,.24);
            transform: translateY(-1px);
        }
        .trusted-card:hover .stat-chip i,
        .trusted-card:focus-visible .stat-chip i,
        .trusted-card:focus-within .stat-chip i {
            transform: rotate(-10deg) scale(1.08);
        }
        .trusted-card-hint {
            margin-top: .72rem;
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            color: #d1fae5;
            font-size: .78rem;
            font-weight: 700;
            letter-spacing: .02em;
            opacity: .9;
        }
        .trusted-card-hint i {
            line-height: 1;
        }
        .trusted-card:hover .trusted-card-hint,
        .trusted-card:focus-visible .trusted-card-hint,
        .trusted-card:focus-within .trusted-card-hint {
            color: #ecfdf5;
            opacity: 1;
        }
        .trusted-card:hover .trusted-card-hint i,
        .trusted-card:focus-visible .trusted-card-hint i,
        .trusted-card:focus-within .trusted-card-hint i {
            transform: translateX(4px);
        }
        .trusted-card-disabled {
            cursor: default;
            opacity: .88;
        }
        .trusted-card-disabled:hover,
        .trusted-card-disabled:focus-visible,
        .trusted-card-disabled:focus-within {
            transform: none;
            border-color: rgba(255,255,255,.22);
            box-shadow: 0 22px 55px rgba(2,8,20,.22);
            background: rgba(255,255,255,.12);
        }
        .feature-sort {
            display: inline-flex;
            align-items: center;
            gap: .2rem;
            padding: .22rem;
            border-radius: 999px;
            border: 1px solid rgba(255,255,255,.24);
            background: rgba(255,255,255,.10);
        }
        .feature-sort-btn {
            border: 0;
            background: transparent;
            color: rgba(236,253,245,.9);
            border-radius: 999px;
            font-size: .72rem;
            font-weight: 700;
            letter-spacing: .01em;
            padding: .33rem .68rem;
            line-height: 1;
            transition: transform .18s ease, background .18s ease, color .18s ease, box-shadow .18s ease;
        }
        .feature-sort-btn:hover,
        .feature-sort-btn:focus-visible {
            background: rgba(255,255,255,.18);
            color: #fff;
            outline: none;
            transform: translateY(-1px);
        }
        .feature-sort-btn.active {
            background: linear-gradient(120deg, rgba(16, 185, 129, .42), rgba(4, 120, 87, .46));
            color: #ecfdf5;
            box-shadow: inset 0 0 0 1px rgba(167, 243, 208, .35), 0 6px 14px rgba(3, 105, 74, .24);
        }
        .preview-card {
            border: 1px solid rgba(255,255,255,.2);
            border-radius: 1rem;
            padding: .85rem;
            background: rgba(255,255,255,.10);
        }
        .preview-card-actionable {
            position: relative;
            overflow: hidden;
            min-height: 120px;
            isolation: isolate;
            transition: transform .24s ease, border-color .24s ease, box-shadow .24s ease, background .24s ease;
        }
        .preview-card-actionable::before {
            content: "";
            position: absolute;
            inset: 0;
            pointer-events: none;
            opacity: 0;
            z-index: 0;
            background: radial-gradient(80% 120% at 0% 50%, rgba(16,185,129,.18), transparent 65%), radial-gradient(90% 120% at 100% 50%, rgba(244,183,64,.18), transparent 70%);
            transition: opacity .24s ease;
        }
        .preview-card-content {
            position: relative;
            z-index: 1;
            transition: opacity .22s ease, filter .22s ease;
        }
        .preview-card-actions-panel {
            position: absolute;
            top: 0;
            right: 0;
            bottom: 0;
            width: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transform: translateX(16px);
            pointer-events: none;
            transition: opacity .2s ease, transform .2s ease;
            border-left: 1px solid rgba(255,255,255,.2);
            background: linear-gradient(145deg, rgba(15,23,42,.34), rgba(15,23,42,.22));
            backdrop-filter: blur(2px);
            -webkit-backdrop-filter: blur(2px);
            z-index: 5;
        }
        .preview-split-actions {
            display: grid;
            grid-template-columns: 1fr 1fr;
            width: 100%;
            height: 100%;
            border-radius: 0;
            overflow: hidden;
            border: 0;
            box-shadow: none;
        }
        .preview-action-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            gap: .55rem;
            text-decoration: none;
            text-transform: uppercase;
            font-weight: 800;
            letter-spacing: .03em;
            font-size: .8rem;
            line-height: 1;
            padding: .95rem .75rem;
            height: 100%;
            position: relative;
            overflow: hidden;
            transition: transform .2s ease, filter .2s ease, box-shadow .2s ease;
            white-space: nowrap;
        }
        .preview-action-btn::after {
            content: "";
            position: absolute;
            top: -140%;
            bottom: -140%;
            left: -42%;
            width: 40%;
            background: linear-gradient(120deg, transparent 10%, rgba(255,255,255,.45) 50%, transparent 90%);
            transform: translateX(-220%) rotate(15deg);
            transition: transform .55s ease;
            pointer-events: none;
        }
        .preview-action-btn i {
            font-size: 1.9rem;
            line-height: 1;
        }
        .preview-action-btn span {
            font-size: 1.06rem;
            letter-spacing: .04em;
        }
        .preview-action-book {
            background: linear-gradient(140deg, var(--premium-emerald), var(--premium-emerald-deep));
            color: #ecfdf5;
        }
        .preview-action-view {
            background: linear-gradient(140deg, var(--premium-gold), var(--premium-gold-deep));
            color: #fff9eb;
        }
        .preview-card-actionable:hover,
        .preview-card-actionable:focus-within {
            transform: translateY(-3px);
            border-color: rgba(255,255,255,.34);
            box-shadow: 0 16px 28px rgba(2,8,20,.26);
            background: rgba(255,255,255,.13);
        }
        .preview-card-actionable:hover::before,
        .preview-card-actionable:focus-within::before {
            opacity: 1;
        }
        .preview-card-actionable:hover .preview-card-content,
        .preview-card-actionable:focus-within .preview-card-content {
            opacity: .92;
            filter: saturate(.92);
        }
        .preview-card-actionable:hover .preview-card-actions-panel,
        .preview-card-actionable:focus-within .preview-card-actions-panel {
            opacity: 1;
            transform: translateX(0);
            pointer-events: auto;
        }
        .preview-action-btn:hover,
        .preview-action-btn:focus-visible {
            transform: translateY(-2px);
            filter: brightness(1.06);
            box-shadow: inset 0 0 0 1px rgba(255,255,255,.2);
        }
        .preview-action-btn:hover::after,
        .preview-action-btn:focus-visible::after {
            transform: translateX(420%) rotate(15deg);
        }
        .preview-link {
            text-decoration: none;
            color: inherit;
            display: block;
        }
        .preview-link:hover .preview-card {
            border-color: rgba(255,255,255,.45);
            box-shadow: 0 10px 24px rgba(2,8,20,.20);
        }
        .preview-thumb {
            width: 88px;
            height: 88px;
            border-radius: .75rem;
            object-fit: cover;
            border: 1px solid rgba(255,255,255,.2);
            background: rgba(255,255,255,.20);
        }
        .preview-thumb.placeholder {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: rgba(255,255,255,.92);
            background: linear-gradient(120deg, rgba(var(--brand-rgb), .24), rgba(255,255,255,.18));
        }
        .preview-price { font-weight: 800; color: #dcfce7; }

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
        .map-discovery-link {
            text-decoration: none;
            transition: transform .18s ease, box-shadow .18s ease, background .18s ease;
        }
        .map-discovery-link:hover {
            background: #dcfce7;
            border-color: rgba(22, 101, 52, .35);
            color: #14532d;
            transform: translateY(-2px);
            box-shadow: 0 8px 18px rgba(2,8,20,.12);
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

        .trusted-modal .modal-dialog {
            max-width: 980px;
            position: relative;
            margin-bottom: 1.65rem;
            --outside-control-gap: 50px;
            --outside-nav-size: 46px;
            --outside-indicator-size: 6px;
            --outside-indicator-extra-gap: 10px;
        }
        .trusted-modal .modal-content {
            border: 1px solid rgba(15,23,42,.08);
            border-radius: 1.25rem;
            overflow: visible;
            position: relative;
            background: linear-gradient(150deg, #f8fafc, #eef2f7);
            box-shadow: 0 30px 60px rgba(2,8,20,.26);
        }
        .trusted-modal .modal-header {
            border-bottom: 1px solid rgba(2,8,20,.08);
            background: linear-gradient(140deg, rgba(16,185,129,.12), rgba(244,183,64,.14));
        }
        .trusted-modal .modal-title {
            color: #0f172a;
            letter-spacing: .01em;
        }
        .trusted-modal .modal-body {
            padding: 0;
            background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%);
        }
        #trustedLandlordCarousel .carousel-inner {
            overflow: hidden;
        }
        .landlord-carousel-indicators {
            position: static;
            margin: 0;
            gap: .35rem;
            justify-content: center;
        }
        .landlord-carousel-indicators [data-bs-target] {
            width: 30px;
            height: var(--outside-indicator-size);
            border: 1px solid rgba(255,255,255,.65);
            border-radius: 999px;
            background: rgba(15,23,42,.42);
            opacity: 1;
        }
        .landlord-carousel-indicators .active {
            background: linear-gradient(125deg, var(--premium-emerald), var(--premium-gold));
            border-color: rgba(167,243,208,.88);
            box-shadow: 0 0 0 1px rgba(2,8,20,.12), 0 3px 10px rgba(16,185,129,.35);
        }
        .landlord-slide-card {
            border-radius: 0;
            border: 0;
            background: linear-gradient(155deg, #ffffff, #f8fafc);
            box-shadow: none;
            overflow: hidden;
        }
        .landlord-layout {
            display: grid;
            grid-template-columns: 1fr 2fr;
            min-height: 440px;
        }
        .landlord-media {
            position: relative;
            min-height: 100%;
            border-right: 1px solid rgba(2,8,20,.1);
            background: linear-gradient(140deg, #065f46, #10b981);
        }
        .landlord-photo {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .landlord-media-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(180deg, rgba(2,8,20,.08), rgba(2,8,20,.7));
            z-index: 1;
        }
        .landlord-avatar-large {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            color: rgba(236,253,245,.95);
            font-size: clamp(2.8rem, 4vw, 3.6rem);
            font-weight: 900;
            letter-spacing: .04em;
            z-index: 1;
        }
        .landlord-media-caption {
            position: absolute;
            left: 1rem;
            right: 1rem;
            bottom: 1rem;
            z-index: 2;
            color: #f8fafc;
            text-shadow: 0 3px 14px rgba(2,8,20,.52);
        }
        .landlord-media-name {
            font-size: 1.05rem;
            font-weight: 800;
            line-height: 1.2;
        }
        .landlord-media-sub {
            margin-top: .22rem;
            font-size: .8rem;
            color: rgba(241,245,249,.95);
        }
        .landlord-content {
            padding: 1rem 1.1rem;
            max-height: 440px;
            overflow: auto;
        }
        .landlord-rating-row {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: .55rem;
        }
        .landlord-stars {
            color: var(--premium-gold-deep);
            letter-spacing: .04em;
            font-size: .92rem;
        }
        .landlord-metrics {
            display: flex;
            flex-wrap: wrap;
            gap: .45rem;
            margin-top: .78rem;
        }
        .landlord-chip {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            border-radius: 999px;
            padding: .3rem .62rem;
            background: #ecfdf5;
            border: 1px solid rgba(16,185,129,.24);
            color: #065f46;
            font-size: .73rem;
            font-weight: 700;
        }
        .landlord-about {
            margin-top: .76rem;
            margin-bottom: 0;
            color: #334155;
            font-size: .88rem;
            line-height: 1.52;
        }
        .landlord-property-title {
            margin-top: .92rem;
            margin-bottom: .52rem;
            font-size: .75rem;
            font-weight: 800;
            letter-spacing: .08em;
            text-transform: uppercase;
            color: #475569;
        }
        .landlord-property-list {
            display: grid;
            gap: .5rem;
        }
        .landlord-property-item {
            border: 1px solid rgba(2,8,20,.08);
            border-radius: .78rem;
            background: #fff;
            padding: .58rem .66rem;
        }
        .landlord-contact {
            margin-top: .74rem;
            font-size: .82rem;
            color: #0f5132;
            display: inline-flex;
            align-items: center;
            gap: .38rem;
            font-weight: 700;
        }
        .trusted-carousel-controls {
            position: absolute;
            inset: 0;
            z-index: 1200;
            pointer-events: none;
        }
        .trusted-outside-nav {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            width: var(--outside-nav-size);
            height: var(--outside-nav-size);
            border-radius: 999px;
            border: 1px solid rgba(16,185,129,.55);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #ffffff, #ecfdf5);
            color: #065f46;
            box-shadow: 0 12px 22px rgba(2,8,20,.24);
            transition: transform .2s ease, box-shadow .2s ease, background .2s ease;
            pointer-events: auto;
        }
        .trusted-outside-nav-prev {
            left: calc(-1 * var(--outside-control-gap));
        }
        .trusted-outside-nav-next {
            right: calc(-1 * var(--outside-control-gap));
        }
        .trusted-outside-nav i {
            font-size: 1.15rem;
            line-height: 1;
        }
        .landlord-carousel-indicators-outside {
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
            bottom: calc(-1 * (var(--outside-control-gap) - var(--outside-nav-size) + var(--outside-indicator-size) + 2px + var(--outside-indicator-extra-gap)));
            width: auto;
            pointer-events: auto;
        }
        .trusted-modal-empty {
            border: 1px dashed rgba(15,23,42,.16);
            border-radius: .9rem;
            padding: 1rem;
            text-align: center;
            color: #475569;
            background: #fff;
        }

        .footer {
            background: #0b1220;
            color: rgba(255,255,255,.8);
            padding: 3rem 0 2rem;
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
            border: 1px solid rgba(255,255,255,.38);
            object-fit: cover;
            background: rgba(255,255,255,.08);
        }
        .footer-brand-copy {
            min-width: 0;
            line-height: 1.06;
        }
        .footer-brand-title {
            color: #ffffff;
            font-size: 2rem;
            font-weight: 800;
            letter-spacing: .01em;
        }
        .footer-brand-subtitle {
            color: rgba(236,253,245,.92);
            font-size: 1.2rem;
            font-weight: 700;
        }
        .footer-support {
            margin-top: .58rem;
            display: grid;
            gap: .44rem;
            max-width: 740px;
        }
        .footer-support-main {
            font-size: .875rem;
            font-weight: 400;
            color: rgba(255,255,255,.8);
            line-height: 1.45;
        }
        .footer-support-credit {
            display: inline-flex;
            align-items: center;
            gap: .42rem;
            width: fit-content;
            border-radius: 999px;
            border: 1px solid rgba(245, 158, 11, .55);
            background: rgba(245, 158, 11, .15);
            color: #fef3c7;
            font-size: .76rem;
            font-weight: 800;
            letter-spacing: .04em;
            text-transform: uppercase;
            padding: .35rem .7rem;
        }
        .footer a { color: rgba(255,255,255,.8); text-decoration: none; }
        .footer a:hover { color: #fff; }

        @keyframes floatUp {
            from { transform: translateY(12px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        .reveal { animation: floatUp 800ms ease both; }

        @media (max-width: 991.98px) {
            .hero {
                min-height: 100svh;
                padding-top: 5.5rem;
            }
            .hero-card { margin-top: 1.25rem; }
            .landlord-layout {
                grid-template-columns: 1fr;
                min-height: 0;
            }
            .landlord-media {
                min-height: 220px;
                border-right: 0;
                border-bottom: 1px solid rgba(2,8,20,.1);
            }
            .landlord-content {
                max-height: none;
            }
            .trusted-outside-nav-prev {
                left: -16px;
            }
            .trusted-outside-nav-next {
                right: -16px;
            }

            .footer-brand-title {
                font-size: 1.5rem;
            }

            .footer-brand-subtitle {
                font-size: .98rem;
            }
        }
        @media (max-width: 575.98px) {
            .hero {
                min-height: 100svh;
                padding-top: 5.1rem;
                padding-bottom: 2.2rem;
            }
            .hero-title {
                font-size: clamp(2rem, 10vw, 2.45rem);
                line-height: 1.06;
            }
            .hero-sub {
                font-size: .98rem;
                line-height: 1.55;
            }
            .hero-accreditation {
                gap: .56rem;
                margin-bottom: .75rem;
            }
            .hero-accreditation-logo {
                width: 40px;
                height: 40px;
            }
            .hero-accreditation-label {
                font-size: .56rem;
                letter-spacing: .09em;
            }
            .hero-accreditation-title {
                font-size: .9rem;
            }
            .hero-cta {
                gap: .55rem !important;
            }
            .hero-cta .btn {
                width: 100%;
                justify-content: center;
                text-align: center;
            }
            .hero-card {
                border-radius: 1.05rem;
                padding: .9rem;
            }
            .hero-featured-rooms {
                background: rgba(4, 25, 18, .42);
                border-color: rgba(255,255,255,.28);
            }
            .hero-featured-rooms > .d-flex.justify-content-between.align-items-center.mb-3 {
                flex-direction: column;
                align-items: stretch !important;
                gap: .45rem;
                margin-bottom: .65rem !important;
            }
            .hero-featured-rooms [data-featured-room-list] {
                --bs-gutter-y: .62rem;
            }
            .stat-chip {
                font-size: .7rem;
                padding: .28rem .58rem;
            }
            .feature-sort {
                width: 100%;
                display: grid;
                grid-template-columns: repeat(3, minmax(0, 1fr));
                gap: .2rem;
                margin-top: .1rem;
            }
            .feature-sort-btn {
                width: 100%;
                font-size: .66rem;
                padding: .36rem .3rem;
            }
            .section {
                padding: 2.8rem 0;
            }
            .glass-card,
            .role-strip {
                border-radius: 1rem;
                padding: 1rem;
            }
            .role-strip .btn {
                width: 100%;
                margin-top: .45rem;
            }
            .preview-thumb {
                width: 74px;
                height: 74px;
            }
            .preview-card {
                padding: .72rem;
            }
            .preview-room-main {
                display: grid !important;
                grid-template-columns: 74px minmax(0, 1fr);
                align-items: start;
                gap: .62rem !important;
            }
            .preview-room-info {
                min-width: 0;
            }
            .preview-room-info .fw-semibold {
                font-size: 1rem;
                line-height: 1.2;
            }
            .preview-room-info .text-muted.small {
                display: -webkit-box;
                -webkit-line-clamp: 2;
                -webkit-box-orient: vertical;
                overflow: hidden;
            }
            .preview-room-price {
                margin-top: .15rem;
                text-align: left !important;
                display: flex;
                align-items: baseline;
                gap: .42rem;
            }
            .preview-room-price .preview-price {
                font-size: 1.95rem;
                line-height: .95;
            }
            .preview-room-price .small {
                font-size: .76rem;
            }
            .preview-card-actionable {
                min-height: 0;
            }
            .preview-card-content {
                opacity: 1 !important;
                transform: none !important;
                filter: none !important;
            }
            .preview-card-actions-panel {
                position: static;
                inset: auto;
                width: 100%;
                opacity: 1;
                transform: none;
                pointer-events: auto;
                margin-top: .68rem;
                border-left: 0;
                border-radius: .85rem;
            }
            .preview-split-actions {
                grid-template-columns: 1fr 1fr;
                grid-template-rows: none;
                height: auto;
                border-radius: .85rem;
                border: 1px solid rgba(255,255,255,.32);
                box-shadow: 0 8px 16px rgba(2,8,20,.18);
            }
            .preview-action-btn {
                font-size: .7rem;
                padding: .8rem .52rem;
                white-space: nowrap;
                gap: .36rem;
            }
            .preview-action-btn i {
                font-size: 1.1rem;
            }
            .preview-action-btn span {
                font-size: .74rem;
            }
            .preview-card > .d-flex {
                flex-direction: column;
                gap: .65rem !important;
            }
            .preview-card-content > .d-flex {
                flex-direction: column;
                gap: .65rem !important;
            }
            .preview-card > .d-flex > .d-flex,
            .preview-card-content > .d-flex > .d-flex {
                gap: .6rem !important;
            }
            .preview-card .text-end {
                text-align: left !important;
            }
            .preview-card .text-muted.small {
                line-height: 1.35;
            }
            .trusted-card-hint {
                font-size: .72rem;
            }
            .landlord-property-item {
                padding: .52rem .55rem;
            }
            .landlord-media {
                min-height: 188px;
            }
            .landlord-content {
                padding: .86rem .84rem;
            }
            .trusted-modal .modal-dialog {
                margin-bottom: 1.45rem;
                --outside-control-gap: 44px;
                --outside-nav-size: 40px;
                --outside-indicator-extra-gap: 8px;
            }
            .trusted-modal .modal-content {
                height: min(82vh, 680px);
                display: flex;
                flex-direction: column;
            }
            .trusted-modal .modal-header {
                flex: 0 0 auto;
            }
            .trusted-modal .modal-body {
                flex: 1 1 auto;
                min-height: 0;
                overflow: hidden;
            }
            #trustedLandlordCarousel,
            #trustedLandlordCarousel .carousel-inner,
            #trustedLandlordCarousel .carousel-item,
            .landlord-slide-card,
            .landlord-layout {
                height: 100%;
            }
            .landlord-layout {
                grid-template-columns: 1fr;
                grid-template-rows: 188px minmax(0, 1fr);
                min-height: 0;
            }
            .trusted-outside-nav {
                width: var(--outside-nav-size);
                height: var(--outside-nav-size);
                display: none !important;
                visibility: hidden;
            }
            #trustedLandlordCarousel .carousel-inner,
            #trustedLandlordCarousel .carousel-item,
            .landlord-slide-card {
                touch-action: pan-y;
            }
            .landlord-media {
                min-height: 188px;
                max-height: 188px;
            }
            .landlord-content {
                padding: .86rem .84rem;
                max-height: none;
                overflow-y: auto;
                -webkit-overflow-scrolling: touch;
            }
        }
        @media (max-width: 420px) {
            .container {
                padding-left: .72rem;
                padding-right: .72rem;
            }
            .navbar-green .navbar-brand {
                padding-left: 78px;
            }
            .navbar-brand-text {
                max-width: calc(100vw - 188px);
                overflow: hidden;
            }
            .navbar-brand-text .brand-line-top,
            .navbar-brand-text .brand-line-bottom {
                display: block;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }
            .navbar-brand-text .brand-line-top {
                font-size: .88rem;
                letter-spacing: .01em;
            }
            .navbar-brand-text .brand-line-bottom {
                font-size: .6rem;
                letter-spacing: .02em;
            }
            .nav-logo-under {
                top: -8px;
            }
            .nav-logo-under img {
                height: 62px;
                width: 62px;
                margin-bottom: -10px;
            }
            .navbar-green .navbar-toggler {
                padding: .28rem .5rem;
                border-radius: .7rem;
            }
            .hero-title {
                font-size: clamp(1.8rem, 9.2vw, 2.2rem);
            }
            .hero-sub {
                font-size: .95rem;
            }
            .pill {
                font-size: .7rem;
                padding: .3rem .58rem;
            }
            .hero-card .fw-semibold {
                font-size: .95rem;
            }
            .preview-price {
                font-size: 1.2rem;
            }
            .preview-thumb {
                width: 68px;
                height: 68px;
            }
            .preview-room-main {
                grid-template-columns: 68px minmax(0, 1fr);
            }
            .preview-room-price .preview-price {
                font-size: 1.75rem;
            }
            .feature-sort-btn {
                font-size: .61rem;
                padding: .34rem .2rem;
            }

            .footer-brand-logo {
                width: 36px;
                height: 36px;
            }

            .footer-brand-title {
                font-size: 1.15rem;
            }

            .footer-brand-subtitle {
                font-size: .82rem;
            }

            .footer-support-main {
                font-size: .875rem;
            }

            .footer-support-credit {
                font-size: .65rem;
                padding: .28rem .56rem;
            }
        }
    </style>
</head>
<body>
<x-public-topnav />

<header class="hero">
    <div class="container">
        <div class="row align-items-center g-4">
            <div class="col-12 col-lg-6">
                <div class="hero-accreditation" role="note" aria-label="Accredited by Office for Student Support and Engagement">
                    <img src="{{ asset('images/OSSE-main.png') }}" alt="OSSE" class="hero-accreditation-logo">
                    <span class="hero-accreditation-copy">
                        <span class="hero-accreditation-label">Accredited by</span>
                        <span class="hero-accreditation-title">Office for Student Support and Engagement</span>
                    </span>
                </div>
                <!-- <div class="hero-credit"><i class="bi bi-award"></i> Credited by OFFICE FOR STUDENT SUPPORT AND ENGAGEMENT</div> -->
                <h1 class="hero-title display-font">Find trusted boarding houses. Book in Seconds.</h1>
                <p class="hero-sub mt-3">A modern booking system for students and landlords. Browse verified properties, request rooms, and move in with a guided onboarding flow.</p>
                <div class="hero-cta d-flex flex-wrap gap-2 mt-4">
                    <a href="{{ route('register.student') }}" class="btn btn-brand">Get started</a>
                    <a href="{{ route('public.properties.map') }}" class="btn btn-ghost">Browse Map</a>
                </div>
            </div>
            <div class="col-12 col-lg-6">
                <div class="hero-card hero-featured-rooms">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="fw-semibold">Featured Rooms</div>
                        <div class="feature-sort" role="group" aria-label="Sort featured rooms">
                            <button type="button" class="feature-sort-btn active" data-featured-sort="cheapest" aria-pressed="true">Cheapest</button>
                            <button type="button" class="feature-sort-btn" data-featured-sort="nearest" aria-pressed="false">Nearest</button>
                            <button type="button" class="feature-sort-btn" data-featured-sort="top-rated" aria-pressed="false">Top rated</button>
                        </div>
                    </div>
                    <div class="row g-2" data-featured-room-list>
                        @forelse($availableRooms->take(3) as $room)
                            @php
                                $availableSlots = $room->getAvailableSlots();
                                $roomImage = $room->image_path;
                                $propertyImage = $room->property->image_path ?? null;
                                $roomRating = (float) ($room->average_rating ?? $room->property->average_rating ?? 0);
                                $roomRatingCount = (int) ($room->ratings_count ?? 0);
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
                            <div class="col-12" data-featured-room-card data-room-price="{{ (float) $room->price }}" data-room-rating="{{ number_format($roomRating, 2, '.', '') }}" data-room-rating-count="{{ $roomRatingCount }}" data-room-lat="{{ $room->property->latitude ?? '' }}" data-room-lng="{{ $room->property->longitude ?? '' }}" data-room-order="{{ $loop->index }}">
                                <div class="preview-card preview-card-actionable">
                                    <div class="preview-card-content">
                                        <div class="d-flex justify-content-between gap-3">
                                            <div class="d-flex gap-3 preview-room-main">
                                                @if($previewImage)
                                                    <img src="{{ asset('storage/'.$previewImage) }}" alt="Room {{ $room->room_number }} preview" class="preview-thumb" loading="lazy">
                                                @else
                                                    <div class="preview-thumb placeholder"><i class="bi bi-house-door fs-5"></i></div>
                                                @endif
                                                <div class="preview-room-info">
                                                    <div class="fw-semibold">Room {{ $room->room_number }}</div>
                                                    <div class="text-muted small">{{ $room->property->name ?? 'Boarding House' }}{{ !empty($room->property?->address) ? ', ' . $room->property->address : '' }}</div>
                                                    <div class="small mt-1"><i class="bi bi-people"></i> Good for {{ $room->capacity }} • {{ $availableSlots > 0 ? $availableSlots . ' slot' . ($availableSlots > 1 ? 's' : '') . ' left' : 'Available' }}</div>
                                                </div>
                                            </div>
                                            <div class="text-end preview-room-price">
                                                <div class="preview-price">₱{{ number_format((float) $room->price, 0) }}</div>
                                                <div class="small text-muted">per month</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="preview-card-actions-panel">
                                        <div class="preview-split-actions" role="group" aria-label="Room actions for room {{ $room->room_number }}">
                                            <a href="{{ route('login', ['redirect' => route('rooms.public.show', $room)]) }}" class="preview-action-btn preview-action-book" aria-label="Log in to book room {{ $room->room_number }}">
                                                <i class="bi bi-calendar2-check"></i>
                                                <span>Book now</span>
                                            </a>
                                            <a href="{{ route('rooms.public.show', $room) }}" class="preview-action-btn preview-action-view" aria-label="View room {{ $room->room_number }} details">
                                                <i class="bi bi-eye"></i>
                                                <span>View room</span>
                                            </a>
                                        </div>
                                    </div>
                                </div>
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
                @php
                    $landlordShowcase = collect($trustedLandlords ?? [])
                        ->map(function ($landlord) {
                            $profile = $landlord->landlordProfile;
                            $properties = collect($landlord->properties ?? []);

                            if ($properties->isEmpty()) {
                                return null;
                            }

                            $profileImage = $landlord->profile_image_path;
                            $profileImageExists = !empty($profileImage) && (
                                \Illuminate\Support\Facades\Storage::disk('public')->exists($profileImage) ||
                                file_exists(public_path('storage/' . ltrim($profileImage, '/')))
                            );

                            $totalRatings = (int) $properties->sum(fn ($property) => (int) ($property->ratings_count ?? 0));
                            $weightedRating = (float) $properties->sum(function ($property) {
                                return (float) ($property->average_rating ?? 0) * (int) ($property->ratings_count ?? 0);
                            });

                            $averageRating = $totalRatings > 0
                                ? $weightedRating / $totalRatings
                                : (float) $properties->avg(fn ($property) => (float) ($property->average_rating ?? 0));

                            return [
                                'id' => $landlord->id,
                                'name' => $landlord->full_name ?: 'Landlord',
                                'contact' => $landlord->contact_number ?: optional($profile)->contact_number,
                                'boarding_house_name' => optional($profile)->boarding_house_name ?: $landlord->boarding_house_name,
                                'about' => optional($profile)->about,
                                'permit_status' => optional($profile)->business_permit_status,
                                'profile_image' => $profileImageExists ? $profileImage : null,
                                'average_rating' => $averageRating,
                                'ratings_count' => $totalRatings,
                                'properties_count' => (int) $properties->count(),
                                'rooms_count' => (int) $properties->sum(fn ($property) => (int) ($property->rooms_count ?? 0)),
                                'available_rooms_count' => (int) $properties->sum(fn ($property) => (int) ($property->available_rooms_count ?? 0)),
                                'properties' => $properties->take(6)->map(function ($property) {
                                    return [
                                        'name' => $property->name,
                                        'rating' => (float) ($property->average_rating ?? 0),
                                        'ratings_count' => (int) ($property->ratings_count ?? 0),
                                        'rooms_count' => (int) ($property->rooms_count ?? 0),
                                    ];
                                })->values(),
                            ];
                        })
                        ->filter()
                        ->values();
                @endphp
                <div
                    class="hero-card mt-3 trusted-card {{ $landlordShowcase->isEmpty() ? 'trusted-card-disabled' : '' }}"
                    data-trusted-landlords-trigger
                    role="button"
                    tabindex="{{ $landlordShowcase->isEmpty() ? '-1' : '0' }}"
                    @if($landlordShowcase->isNotEmpty()) data-bs-toggle="modal" data-bs-target="#trustedLandlordsModal" @endif
                    aria-label="View trusted landlord list"
                >
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="fw-semibold">Trusted by MINSU community</div>
                            <div class="text-muted small">Secure onboarding and verified landlords.</div>
                        </div>
                        <span class="stat-chip"><i class="bi bi-patch-check"></i> Safe stay</span>
                    </div>
                    <div class="trusted-card-hint">
                        @if($landlordShowcase->isNotEmpty())
                            <span>Click to explore trusted landlords</span>
                            <i class="bi bi-arrow-right-circle"></i>
                        @else
                            <span>Landlord showcase will appear here soon</span>
                        @endif
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
                                <a href="{{ url('/browse-map/properties/17/rooms') }}" class="btn btn-sm btn-outline-success rounded-pill w-100">View property</a>
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

<div class="modal fade trusted-modal" id="trustedLandlordsModal" tabindex="-1" aria-labelledby="trustedLandlordsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title display-font" id="trustedLandlordsModalLabel">Trusted landlord showcase</h5>
                    <div class="small text-muted">Ratings are calculated from each landlord's visible properties and room feedback.</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                @if($landlordShowcase->isNotEmpty())
                    <div id="trustedLandlordCarousel" class="carousel slide" data-bs-ride="false" data-bs-interval="false" data-bs-touch="true" data-bs-wrap="true">
                        <div class="carousel-inner">
                            @foreach($landlordShowcase as $landlordEntry)
                                @php
                                    $roundedRating = round(((float) $landlordEntry['average_rating']) * 2) / 2;
                                    $fullStars = (int) floor($roundedRating);
                                    $hasHalfStar = ($roundedRating - $fullStars) >= 0.5;
                                    $emptyStars = max(0, 5 - $fullStars - ($hasHalfStar ? 1 : 0));
                                @endphp
                                <div class="carousel-item {{ $loop->first ? 'active' : '' }}">
                                    <article class="landlord-slide-card">
                                        <div class="landlord-layout">
                                            <div class="landlord-media">
                                                @if(!empty($landlordEntry['profile_image']))
                                                    <img src="{{ asset('storage/' . ltrim($landlordEntry['profile_image'], '/')) }}" alt="{{ $landlordEntry['name'] }} profile" class="landlord-photo" loading="lazy">
                                                @else
                                                    <span class="landlord-avatar-large">{{ strtoupper(substr($landlordEntry['name'], 0, 1)) }}</span>
                                                @endif
                                                <span class="landlord-media-overlay" aria-hidden="true"></span>
                                                <div class="landlord-media-caption">
                                                    <div class="landlord-media-name">{{ $landlordEntry['name'] }}</div>
                                                    <div class="landlord-media-sub">{{ $landlordEntry['boarding_house_name'] ?: 'Independent landlord' }}</div>
                                                </div>
                                            </div>
                                            <div class="landlord-content">
                                                <div class="landlord-rating-row">
                                                    <span class="landlord-stars" aria-hidden="true">
                                                        @for($star = 0; $star < $fullStars; $star++)
                                                            <i class="bi bi-star-fill"></i>
                                                        @endfor
                                                        @if($hasHalfStar)
                                                            <i class="bi bi-star-half"></i>
                                                        @endif
                                                        @for($star = 0; $star < $emptyStars; $star++)
                                                            <i class="bi bi-star"></i>
                                                        @endfor
                                                    </span>
                                                    <span class="fw-semibold">{{ number_format((float) $landlordEntry['average_rating'], 1) }} / 5</span>
                                                    <span class="small text-muted">({{ number_format((int) $landlordEntry['ratings_count']) }} ratings)</span>
                                                </div>
                                                <div class="landlord-metrics">
                                                    <span class="landlord-chip"><i class="bi bi-buildings"></i> {{ $landlordEntry['properties_count'] }} properties</span>
                                                    <span class="landlord-chip"><i class="bi bi-door-open"></i> {{ $landlordEntry['rooms_count'] }} rooms</span>
                                                    <span class="landlord-chip"><i class="bi bi-check2-circle"></i> {{ $landlordEntry['available_rooms_count'] }} available</span>
                                                    @if(($landlordEntry['permit_status'] ?? null) === 'approved')
                                                        <span class="landlord-chip"><i class="bi bi-shield-check"></i> Permit verified</span>
                                                    @endif
                                                </div>
                                                @if(!empty($landlordEntry['about']))
                                                    <p class="landlord-about">{{ \Illuminate\Support\Str::limit($landlordEntry['about'], 170) }}</p>
                                                @endif
                                                <div class="landlord-property-title">Property rating breakdown</div>
                                                <div class="landlord-property-list">
                                                    @foreach($landlordEntry['properties'] as $propertySummary)
                                                        <div class="landlord-property-item d-flex justify-content-between align-items-center gap-2">
                                                            <div>
                                                                <div class="fw-semibold small">{{ $propertySummary['name'] }}</div>
                                                                <div class="small text-muted">{{ $propertySummary['rooms_count'] }} rooms listed</div>
                                                            </div>
                                                            <div class="text-end small">
                                                                <div class="fw-semibold text-success">{{ number_format((float) $propertySummary['rating'], 1) }} ★</div>
                                                                <div class="text-muted">{{ number_format((int) $propertySummary['ratings_count']) }} reviews</div>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                                @if(!empty($landlordEntry['contact']))
                                                    <div class="landlord-contact"><i class="bi bi-telephone"></i> Contact: {{ $landlordEntry['contact'] }}</div>
                                                @endif
                                            </div>
                                        </div>
                                    </article>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @else
                    <div class="trusted-modal-empty">
                        Landlord profiles are not available yet. Please check back soon.
                    </div>
                @endif
            </div>
            @if($landlordShowcase->count() > 1)
                <div class="trusted-carousel-controls" aria-label="Landlord carousel controls">
                    <button class="trusted-outside-nav trusted-outside-nav-prev" type="button" data-bs-target="#trustedLandlordCarousel" data-bs-slide="prev" aria-label="Previous landlord">
                        <i class="bi bi-chevron-left" aria-hidden="true"></i>
                    </button>
                    <div class="carousel-indicators landlord-carousel-indicators landlord-carousel-indicators-outside">
                        @php($indicatorCount = min(10, $landlordShowcase->count()))
                        @for($i = 0; $i < $indicatorCount; $i++)
                            <button type="button" data-bs-target="#trustedLandlordCarousel" data-bs-slide-to="{{ $i }}" data-indicator-slot="{{ $i }}" class="{{ $i === 0 ? 'active' : '' }}" aria-current="{{ $i === 0 ? 'true' : 'false' }}" aria-label="Slide {{ $i + 1 }}"></button>
                        @endfor
                    </div>
                    <button class="trusted-outside-nav trusted-outside-nav-next" type="button" data-bs-target="#trustedLandlordCarousel" data-bs-slide="next" aria-label="Next landlord">
                        <i class="bi bi-chevron-right" aria-hidden="true"></i>
                    </button>
                </div>
            @endif
        </div>
    </div>
</div>

<footer class="footer">
    <div class="container">
        <div class="row g-3">
            <div class="col-12 col-lg-6">
                <div class="footer-brand mb-2">
                    <div class="footer-brand-logos" aria-hidden="true">
                        <img src="{{ asset('images/MinSU_logo.png') }}" alt="MINSU logo" class="footer-brand-logo" loading="lazy">
                        <img src="{{ asset('images/OSSE-main.png') }}" alt="OSSE logo" class="footer-brand-logo" loading="lazy">
                    </div>
                    <div class="footer-brand-copy">
                        <div class="footer-brand-title">Mindoro State University</div>
                        <div class="footer-brand-subtitle">Online Boarding House System</div>
                    </div>
                </div>
                <div class="footer-support">
                    <div class="footer-support-main">An institution-aligned digital housing ecosystem that advances student welfare, accountable landlord participation, and academically informed accommodation decision-making across the MINSU community.</div>
                    <div class="footer-support-credit"><i class="bi bi-patch-check-fill" aria-hidden="true"></i>Accredited by: Office for Student Support and Engagement</div>
                </div>
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
<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (element) {
            bootstrap.Tooltip.getOrCreateInstance(element);
        });

        const featuredList = document.querySelector('[data-featured-room-list]');
        const sortButtons = Array.from(document.querySelectorAll('[data-featured-sort]'));

        if (featuredList && sortButtons.length) {
            const cards = Array.from(featuredList.querySelectorAll('[data-featured-room-card]'));
            let userPosition = null;

            const toNumber = function (value, fallback) {
                const parsed = Number.parseFloat(value);
                return Number.isFinite(parsed) ? parsed : fallback;
            };

            const toRadians = function (deg) {
                return deg * (Math.PI / 180);
            };

            const distanceKm = function (originLat, originLng, targetLat, targetLng) {
                const earthRadiusKm = 6371;
                const dLat = toRadians(targetLat - originLat);
                const dLng = toRadians(targetLng - originLng);
                const a = Math.sin(dLat / 2) ** 2
                    + Math.cos(toRadians(originLat)) * Math.cos(toRadians(targetLat)) * Math.sin(dLng / 2) ** 2;
                return earthRadiusKm * (2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a)));
            };

            const setActiveSort = function (mode) {
                sortButtons.forEach(function (button) {
                    const isActive = button.dataset.featuredSort === mode;
                    button.classList.toggle('active', isActive);
                    button.setAttribute('aria-pressed', isActive ? 'true' : 'false');
                });
            };

            const nearestScore = function (card) {
                if (!userPosition) {
                    return Number.POSITIVE_INFINITY;
                }

                const lat = toNumber(card.dataset.roomLat, Number.NaN);
                const lng = toNumber(card.dataset.roomLng, Number.NaN);
                if (!Number.isFinite(lat) || !Number.isFinite(lng)) {
                    return Number.POSITIVE_INFINITY;
                }

                return distanceKm(userPosition.lat, userPosition.lng, lat, lng);
            };

            const originalOrder = function (card) {
                return toNumber(card.dataset.roomOrder, Number.MAX_SAFE_INTEGER);
            };

            const sortCards = function (mode) {
                const sorted = [...cards];

                if (mode === 'cheapest') {
                    sorted.sort(function (a, b) {
                        return toNumber(a.dataset.roomPrice, Number.POSITIVE_INFINITY)
                            - toNumber(b.dataset.roomPrice, Number.POSITIVE_INFINITY)
                            || originalOrder(a) - originalOrder(b);
                    });
                }

                if (mode === 'top-rated') {
                    sorted.sort(function (a, b) {
                        return toNumber(b.dataset.roomRating, -1) - toNumber(a.dataset.roomRating, -1)
                            || toNumber(b.dataset.roomRatingCount, -1) - toNumber(a.dataset.roomRatingCount, -1)
                            || originalOrder(a) - originalOrder(b);
                    });
                }

                if (mode === 'nearest') {
                    sorted.sort(function (a, b) {
                        return nearestScore(a) - nearestScore(b)
                            || toNumber(a.dataset.roomPrice, Number.POSITIVE_INFINITY) - toNumber(b.dataset.roomPrice, Number.POSITIVE_INFINITY)
                            || originalOrder(a) - originalOrder(b);
                    });
                }

                sorted.forEach(function (card) {
                    featuredList.appendChild(card);
                });
            };

            const requestLocation = function () {
                return new Promise(function (resolve) {
                    if (userPosition) {
                        resolve(true);
                        return;
                    }

                    if (!navigator.geolocation) {
                        resolve(false);
                        return;
                    }

                    navigator.geolocation.getCurrentPosition(function (position) {
                        userPosition = {
                            lat: position.coords.latitude,
                            lng: position.coords.longitude,
                        };
                        resolve(true);
                    }, function () {
                        resolve(false);
                    }, {
                        enableHighAccuracy: false,
                        timeout: 6000,
                        maximumAge: 120000,
                    });
                });
            };

            sortButtons.forEach(function (button) {
                button.addEventListener('click', async function () {
                    const mode = button.dataset.featuredSort;

                    if (mode === 'nearest') {
                        const defaultLabel = button.textContent;
                        button.textContent = 'Locating...';
                        button.disabled = true;
                        const granted = await requestLocation();
                        button.disabled = false;
                        button.textContent = defaultLabel;

                        if (!granted) {
                            return;
                        }
                    }

                    setActiveSort(mode);
                    sortCards(mode);
                });
            });

            setActiveSort('cheapest');
            sortCards('cheapest');
        }

        const trustedCardTrigger = document.querySelector('[data-trusted-landlords-trigger]:not(.trusted-card-disabled)');
        if (trustedCardTrigger) {
            trustedCardTrigger.addEventListener('keydown', function (event) {
                if (event.key === 'Enter' || event.key === ' ') {
                    event.preventDefault();
                    trustedCardTrigger.click();
                }
            });
        }

        const trustedCarousel = document.getElementById('trustedLandlordCarousel');
        const externalIndicators = Array.from(document.querySelectorAll('.landlord-carousel-indicators-outside [data-bs-slide-to]'));

        let trustedCarouselInstance = null;

        if (trustedCarousel && typeof bootstrap !== 'undefined' && bootstrap.Carousel) {
            const existingCarouselInstance = bootstrap.Carousel.getInstance(trustedCarousel);
            if (existingCarouselInstance) {
                existingCarouselInstance.dispose();
            }

            trustedCarouselInstance = new bootstrap.Carousel(trustedCarousel, {
                interval: false,
                touch: false,
                wrap: true,
                ride: false,
            });

            const isMobileViewport = window.matchMedia('(max-width: 575.98px)').matches;
            if (isMobileViewport) {
                let touchStartX = 0;
                let touchStartY = 0;
                let touchTracking = false;
                const minSwipeDistance = 44;
                const maxVerticalDrift = 72;

                trustedCarousel.addEventListener('touchstart', function (event) {
                    if (!event.touches || event.touches.length !== 1) {
                        touchTracking = false;
                        return;
                    }

                    touchStartX = event.touches[0].clientX;
                    touchStartY = event.touches[0].clientY;
                    touchTracking = true;
                }, { passive: true });

                trustedCarousel.addEventListener('touchend', function (event) {
                    if (!touchTracking || !event.changedTouches || event.changedTouches.length !== 1) {
                        return;
                    }

                    const deltaX = event.changedTouches[0].clientX - touchStartX;
                    const deltaY = event.changedTouches[0].clientY - touchStartY;
                    touchTracking = false;

                    if (Math.abs(deltaX) < minSwipeDistance) {
                        return;
                    }

                    if (Math.abs(deltaY) > maxVerticalDrift || Math.abs(deltaX) <= Math.abs(deltaY)) {
                        return;
                    }

                    if (deltaX < 0) {
                        trustedCarouselInstance.next();
                    } else {
                        trustedCarouselInstance.prev();
                    }
                }, { passive: true });
            }
        }

        if (trustedCarousel && externalIndicators.length) {
            const slides = Array.from(trustedCarousel.querySelectorAll('.carousel-item'));
            const totalSlides = slides.length;

            const normalizeIndex = function (index) {
                if (totalSlides <= 0) {
                    return 0;
                }
                return ((index % totalSlides) + totalSlides) % totalSlides;
            };

            const setExternalIndicator = function (index) {
                const activeIndex = normalizeIndex(index);
                const slotCount = externalIndicators.length;
                const useWindow = totalSlides > slotCount;
                const centerSlot = Math.floor(slotCount / 2);
                const windowStart = useWindow
                    ? normalizeIndex(activeIndex - centerSlot)
                    : 0;

                externalIndicators.forEach(function (indicator, slotIndex) {
                    const slideIndex = useWindow
                        ? normalizeIndex(windowStart + slotIndex)
                        : slotIndex;
                    const isActive = slideIndex === activeIndex;

                    indicator.dataset.bsSlideTo = String(slideIndex);
                    indicator.setAttribute('aria-label', 'Slide ' + (slideIndex + 1));
                    indicator.classList.toggle('active', isActive);
                    indicator.setAttribute('aria-current', isActive ? 'true' : 'false');
                });
            };

            const activeSlide = trustedCarousel.querySelector('.carousel-item.active');
            if (activeSlide) {
                setExternalIndicator(Math.max(0, slides.indexOf(activeSlide)));
            }

            trustedCarousel.addEventListener('slide.bs.carousel', function (event) {
                if (typeof event.to === 'number') {
                    setExternalIndicator(event.to);
                }
            });
        }
    });
</script>
</body>
</html>
