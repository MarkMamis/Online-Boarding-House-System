<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Room Map - Online Boarding House System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Bricolage+Grotesque:opsz,wght@12..96,600;12..96,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin=""/>
    <style>
        :root {
            --brand: #14532d;
            --brand-2: #166534;
            --mint: #a7f3d0;
            --gold: #f59e0b;
            --ink: #0f172a;
            --paper: #f8fafc;
            --line: rgba(2, 8, 20, .1);
        }

        body {
            font-family: 'Manrope', system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
            color: var(--ink);
            background: radial-gradient(1500px circle at -20% -10%, rgba(34, 197, 94, .18), transparent 45%),
                        radial-gradient(1200px circle at 110% 10%, rgba(20, 83, 45, .12), transparent 50%),
                        var(--paper);
            min-height: 100vh;
        }

        h1,
        h2,
        h3,
        .display-font {
            font-family: 'Bricolage Grotesque', 'Manrope', system-ui, sans-serif;
        }

        .navbar-green {
            background: linear-gradient(180deg, #1a5c2e 0%, #2d8a4e 60%, #3aaf65 100%);
            box-shadow: 0 2px 16px rgba(0,0,0,.30);
            overflow: visible !important;
        }

        .navbar-green .nav-link {
            color: rgba(255,255,255,.92) !important;
            font-weight: 600;
            letter-spacing: .01em;
        }

        .navbar-green .nav-link:hover {
            color: #fff !important;
            text-decoration: underline;
            text-underline-offset: 3px;
        }

        .navbar-green .btn-link {
            color: rgba(255,255,255,.92) !important;
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
            padding-left: 86px;
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

        .browse-shell {
            padding-top: 6.15rem;
            padding-bottom: 2.5rem;
        }

        .hero-panel {
            border: 0;
            background: transparent;
            color: var(--ink);
            box-shadow: none;
            padding: 0;
            position: relative;
            overflow: visible;
        }

        .hero-panel-head {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: flex-start;
            gap: .35rem;
        }

        .hero-title-block {
            max-width: 720px;
        }

        .hero-title-block .display-font {
            margin: 0;
            color: #0f172a;
            font-size: clamp(1.85rem, 3vw, 2.35rem);
            line-height: 1.04;
        }

        .hero-search-band {
            margin-top: .38rem;
            padding: .18rem 0 .62rem;
            border: 0;
            background: transparent;
            box-shadow: none;
            position: relative;
        }

        .hero-search-form {
            display: block;
        }

        .hero-search-row {
            display: grid;
            grid-template-columns: auto 1fr auto auto;
            align-items: end;
            gap: .48rem;
            border-bottom: 2px solid rgba(2, 8, 20, .14);
            padding: 0 0 .48rem;
        }

        .hero-search-icon {
            font-size: 2rem;
            color: rgba(15, 23, 42, .62);
            margin-left: 0;
        }

        .hero-search-input {
            height: auto;
            border: 0;
            border-radius: 0;
            background: transparent;
            color: #0f172a;
            font-size: clamp(1.8rem, 4.2vw, 2.9rem);
            font-weight: 700;
            letter-spacing: -.01em;
            line-height: 1.02;
            padding: 0;
        }

        .hero-search-input::placeholder {
            color: rgba(30, 41, 59, .7);
            font-weight: 600;
        }

        .hero-search-input:focus {
            border: 0;
            box-shadow: none;
            outline: none;
        }

        .hero-search-submit,
        .hero-search-reset {
            width: 2.45rem;
            height: 2.45rem;
            border-radius: 999px;
            padding: 0;
            white-space: nowrap;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .hero-search-submit {
            background: linear-gradient(125deg, #16a34a, #166534);
            border: 1px solid rgba(22, 101, 52, .6);
            color: #ecfdf5;
            box-shadow: 0 8px 18px rgba(2, 8, 20, .15);
        }

        .hero-search-submit:hover {
            filter: brightness(1.04);
            color: #fff;
        }

        .hero-search-reset {
            border: 1px solid rgba(2,8,20,.18);
            background: #ffffff;
            color: #334155;
        }

        .hero-search-reset:hover {
            background: #f8fafc;
            color: #0f172a;
        }

        .hero-search-suggestion-menu {
            position: absolute;
            top: calc(100% - .14rem);
            left: 0;
            right: 0;
            z-index: 2500;
            margin-top: 0;
            border: 1px solid rgba(2, 8, 20, .12);
            border-radius: .9rem;
            background: #ffffff;
            box-shadow: 0 14px 28px rgba(2, 8, 20, .12);
            padding: .35rem;
            max-height: 260px;
            overflow-y: auto;
            display: none;
        }

        .hero-search-suggestion-menu.is-open {
            display: block;
        }

        .hero-panel::after {
            content: none;
        }

        .kicker {
            display: inline-flex;
            align-items: center;
            gap: .45rem;
            border-radius: 999px;
            border: 1px solid rgba(255, 255, 255, .4);
            background: rgba(255, 255, 255, .14);
            color: #dcfce7;
            font-size: .75rem;
            font-weight: 700;
            padding: .35rem .72rem;
            letter-spacing: .04em;
            text-transform: uppercase;
        }

        .hero-meta {
            display: inline-flex;
            align-items: center;
            gap: .45rem;
            border-radius: 999px;
            background: rgba(167, 243, 208, .25);
            border: 1px solid rgba(167, 243, 208, .45);
            padding: .35rem .72rem;
            font-size: .8rem;
            font-weight: 600;
            color: #ecfdf5;
        }

        .map-wrap {
            margin-top: 1rem;
            border-radius: 1.1rem;
            border: 1px solid var(--line);
            background: #f1f5f9;
            box-shadow: 0 14px 30px rgba(2, 8, 20, .12);
            overflow: hidden;
            position: relative;
        }

        #publicPropertiesMap {
            height: 480px;
        }

        .map-empty {
            height: 480px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: .5rem;
            color: rgba(15, 23, 42, .62);
            background: linear-gradient(145deg, rgba(167, 243, 208, .26), rgba(255, 255, 255, .95));
        }

        .map-overlay {
            position: absolute;
            top: .95rem;
            left: .95rem;
            z-index: 500;
            background: rgba(15, 23, 42, .8);
            color: #f8fafc;
            border-radius: .85rem;
            padding: .5rem .7rem;
            font-size: .78rem;
            font-weight: 600;
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, .2);
        }

        .price-pill-marker {
            border-radius: 999px;
            border: 2px solid #fff;
            color: #fff;
            background: linear-gradient(180deg, #166534 0%, #14532d 100%);
            box-shadow: 0 10px 20px rgba(2, 8, 20, .35);
            font-size: .72rem;
            font-weight: 700;
            line-height: 1;
            padding: .25rem .58rem;
            white-space: nowrap;
            letter-spacing: .01em;
        }

        .price-pill-marker.is-empty {
            background: linear-gradient(180deg, #64748b 0%, #475569 100%);
        }

        .leaflet-popup-content-wrapper {
            border-radius: 1rem;
        }

        .map-popup {
            min-width: 236px;
            max-width: 276px;
        }

        .map-popup-photo {
            width: 100%;
            height: 110px;
            border-radius: .7rem;
            overflow: hidden;
            margin-bottom: .52rem;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, rgba(167, 243, 208, .45), rgba(240, 253, 250, .9));
            color: #0f766e;
        }

        .map-popup-photo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .map-popup-title {
            font-size: .95rem;
            font-weight: 700;
            line-height: 1.25;
            margin-bottom: .2rem;
        }

        .map-popup-address {
            font-size: .76rem;
            color: rgba(2, 8, 20, .65);
            margin-bottom: .4rem;
        }

        .map-popup-meta {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .55rem;
            margin-bottom: .45rem;
        }

        .map-popup-price {
            font-size: .84rem;
            color: #14532d;
            font-weight: 800;
        }

        .info-block {
            margin-top: 1rem;
        }

        .list-panel,
        .filter-panel {
            border-radius: 1.05rem;
            border: 1px solid var(--line);
            background: #fff;
            box-shadow: 0 10px 24px rgba(2, 8, 20, .07);
        }

        .list-panel {
            padding: 1rem;
        }

        .filter-panel {
            padding: 1rem;
        }

        .mobile-filter-trigger {
            display: none;
            width: 2.1rem;
            height: 2.1rem;
            padding: 0;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
        }

        .room-list {
            display: grid;
            gap: .75rem;
        }

        .room-item {
            border: 1px solid rgba(2, 8, 20, .1);
            border-radius: 1rem;
            background: #fff;
            padding: .85rem;
            display: grid;
            grid-template-columns: 108px minmax(0, 1fr);
            gap: .82rem;
            transition: border-color .2s ease, box-shadow .2s ease, transform .2s ease;
        }

        .room-item:hover {
            border-color: rgba(20, 83, 45, .35);
            transform: translateY(-2px);
            box-shadow: 0 12px 20px rgba(2, 8, 20, .08);
        }

        .room-thumb {
            width: 100%;
            height: 108px;
            border-radius: .75rem;
            border: 1px solid rgba(2, 8, 20, .08);
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(145deg, rgba(167, 243, 208, .35), #f8fafc);
            color: #0f766e;
        }

        .room-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .room-name {
            font-size: 1rem;
            font-weight: 700;
            margin-bottom: .16rem;
            color: #0f172a;
        }

        .property-name {
            font-size: .88rem;
            color: #14532d;
            font-weight: 700;
            margin-bottom: .14rem;
        }

        .room-address {
            font-size: .78rem;
            color: rgba(2, 8, 20, .62);
            margin-bottom: .32rem;
        }

        .chip-row {
            display: flex;
            flex-wrap: wrap;
            gap: .34rem;
            margin-bottom: .4rem;
        }

        .meta-chip,
        .amenity-chip {
            display: inline-flex;
            align-items: center;
            gap: .28rem;
            border-radius: 999px;
            padding: .2rem .55rem;
            font-size: .71rem;
            font-weight: 600;
        }

        .meta-chip {
            border: 1px solid rgba(2, 8, 20, .12);
            color: #0f172a;
            background: #f8fafc;
        }

        .amenity-chip {
            border: 1px solid rgba(20, 83, 45, .22);
            color: #14532d;
            background: rgba(167, 243, 208, .22);
        }

        .helper-note {
            font-size: .78rem;
            color: rgba(2, 8, 20, .58);
        }

        .empty-list {
            border-radius: 1rem;
            border: 1px dashed rgba(2, 8, 20, .18);
            background: linear-gradient(145deg, #ffffff, #f8fafc);
            min-height: 170px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            gap: .35rem;
            color: rgba(2, 8, 20, .62);
        }

        .filter-group {
            margin-bottom: .9rem;
        }

        .filter-group .form-label {
            font-size: .75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .04em;
            color: rgba(2, 8, 20, .58);
            margin-bottom: .32rem;
        }

        .filter-panel .form-control,
        .filter-panel .form-select {
            border-radius: .8rem;
            border-color: rgba(2, 8, 20, .14);
            font-size: .9rem;
        }

        .filter-panel .form-control:focus,
        .filter-panel .form-select:focus {
            border-color: rgba(22, 101, 52, .45);
            box-shadow: 0 0 0 .2rem rgba(22, 101, 52, .12);
        }

        .search-wrap {
            position: relative;
        }

        .search-suggestion-menu {
            position: absolute;
            top: calc(100% + 6px);
            left: 0;
            right: 0;
            z-index: 2500;
            border: 1px solid rgba(2, 8, 20, .14);
            border-radius: .8rem;
            background: #fff;
            box-shadow: 0 12px 24px rgba(2, 8, 20, .14);
            padding: .3rem;
            max-height: 240px;
            overflow-y: auto;
            display: none;
        }

        .search-suggestion-menu.is-open {
            display: block;
        }

        .search-suggestion-item {
            width: 100%;
            border: 0;
            background: transparent;
            text-align: left;
            border-radius: .55rem;
            padding: .4rem .55rem;
            font-size: .86rem;
            color: #0f172a;
        }

        .search-suggestion-item:hover,
        .search-suggestion-item:focus {
            background: rgba(167, 243, 208, .28);
            outline: none;
        }

        .search-help {
            margin-top: .25rem;
            font-size: .72rem;
            color: rgba(2, 8, 20, .56);
        }

        .filter-check-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: .42rem .65rem;
        }

        .filter-option {
            display: flex;
            align-items: center;
            gap: .4rem;
            font-size: .82rem;
            color: #334155;
            margin: 0;
        }

        .filter-option input {
            margin-top: 0;
            flex-shrink: 0;
        }

        .price-slider-wrap {
            position: relative;
            height: 34px;
            display: flex;
            align-items: center;
        }

        .price-slider-track,
        .price-slider-range {
            position: absolute;
            left: 0;
            right: 0;
            height: 6px;
            border-radius: 999px;
        }

        .price-slider-track {
            background: #e2e8f0;
        }

        .price-slider-range {
            background: linear-gradient(90deg, #22c55e 0%, #166534 100%);
        }

        .price-slider-wrap input[type="range"] {
            position: absolute;
            left: 0;
            right: 0;
            width: 100%;
            appearance: none;
            -webkit-appearance: none;
            pointer-events: none;
            background: transparent;
            margin: 0;
        }

        .price-slider-wrap input[type="range"]::-webkit-slider-thumb {
            -webkit-appearance: none;
            appearance: none;
            pointer-events: auto;
            width: 18px;
            height: 18px;
            border-radius: 999px;
            border: 2px solid #ffffff;
            background: #14532d;
            box-shadow: 0 2px 8px rgba(2, 8, 20, .3);
            cursor: pointer;
        }

        .price-slider-wrap input[type="range"]::-moz-range-thumb {
            pointer-events: auto;
            width: 18px;
            height: 18px;
            border-radius: 999px;
            border: 2px solid #ffffff;
            background: #14532d;
            box-shadow: 0 2px 8px rgba(2, 8, 20, .3);
            cursor: pointer;
        }

        .btn-brand {
            border-radius: 999px;
            font-weight: 700;
            background: var(--brand);
            border-color: var(--brand);
            color: #fff;
        }

        .btn-brand:hover {
            background: var(--brand-2);
            border-color: var(--brand-2);
            color: #fff;
        }

        @media (max-width: 991.98px) {
            .browse-shell {
                padding-top: 5.4rem;
            }

            .navbar-green .navbar-brand {
                padding-left: 74px;
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

            #publicPropertiesMap,
            .map-empty {
                height: 360px;
            }

            .hero-search-row {
                grid-template-columns: auto 1fr auto auto;
            }

            .hero-search-submit,
            .hero-search-reset,
            .hero-search-input {
                width: auto;
            }

        }

        @media (min-width: 1200px) {
            .filter-panel.offcanvas-xl {
                position: sticky;
                top: 5.4rem;
                visibility: visible;
                transform: none;
                background: #fff;
            }

            .filter-panel.offcanvas-xl .offcanvas-body {
                display: block;
                flex-grow: 1;
                width: 100%;
                overflow: visible;
                padding: 0;
            }

            .filter-panel.offcanvas-xl #publicMapFilterForm {
                width: 100%;
            }
        }

        @media (max-width: 1199.98px) {
            .mobile-filter-trigger {
                display: inline-flex;
            }

            .filter-panel.offcanvas-xl {
                height: 100dvh;
                max-height: 100dvh;
                border-radius: 0;
                border: 0;
                padding: .95rem .95rem 0;
                background: linear-gradient(160deg, #f8fbf9 0%, #ffffff 52%, #f2f8f4 100%);
                transform: none !important;
                --reveal-x: 28px;
                --reveal-y: 28px;
                clip-path: circle(0 at var(--reveal-x, calc(100% - 36px)) var(--reveal-y, 34px));
                transition: clip-path 1.05s cubic-bezier(.18, .82, .2, 1), background-color .52s ease;
                will-change: clip-path;
            }

            .filter-panel.offcanvas-xl.show,
            .filter-panel.offcanvas-xl.showing {
                clip-path: circle(160vmax at var(--reveal-x, calc(100% - 36px)) var(--reveal-y, 34px));
            }

            .filter-panel.offcanvas-xl.hiding {
                clip-path: circle(0 at var(--reveal-x, calc(100% - 36px)) var(--reveal-y, 34px));
            }

            .filter-panel.offcanvas-xl .filter-sheet-header {
                position: sticky;
                top: 0;
                z-index: 2;
                padding-bottom: .6rem;
                background: #fff;
            }

            .filter-panel.offcanvas-xl .offcanvas-body {
                flex: 1 1 auto;
                overflow-y: auto;
                padding-bottom: 1rem;
            }

            .filter-panel.offcanvas-xl #publicMapFilterForm {
                min-height: 100%;
                display: flex;
                flex-direction: column;
            }

            .filter-panel.offcanvas-xl #publicMapFilterLabel {
                font-size: 1.75rem;
                font-weight: 800;
                letter-spacing: .01em;
            }

            .filter-panel.offcanvas-xl .form-label {
                font-size: .92rem;
                letter-spacing: .03em;
            }

            .filter-panel.offcanvas-xl .filter-option {
                font-size: 1.08rem;
                line-height: 1.2;
            }

            .filter-panel.offcanvas-xl .helper-note,
            .filter-panel.offcanvas-xl .search-help {
                font-size: .92rem;
            }

            .filter-panel.offcanvas-xl .form-control,
            .filter-panel.offcanvas-xl .form-select,
            .filter-panel.offcanvas-xl .btn {
                font-size: 1.08rem;
            }

            .filter-panel.offcanvas-xl .btn-close {
                transform: scale(1.2);
            }

            .filter-panel.offcanvas-xl .filter-check-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: .52rem .8rem;
            }

            .filter-panel.offcanvas-xl .filter-actions {
                margin-top: auto !important;
                padding-top: .9rem;
                position: sticky;
                bottom: 0;
                background: linear-gradient(180deg, rgba(248, 251, 249, 0), #f8fbf9 28%, #f8fbf9 100%);
            }

            .offcanvas-backdrop {
                transition: opacity .52s ease;
            }

            .offcanvas-backdrop.show {
                opacity: .38;
            }
        }

        @media (max-width: 767.98px) {
            .hero-title-block .display-font {
                font-size: clamp(1.46rem, 7.8vw, 2rem);
            }

            .hero-search-icon {
                font-size: 1.52rem;
            }

            .hero-search-row {
                grid-template-columns: auto minmax(0, 1fr) auto auto;
                gap: .36rem;
            }

            .hero-search-input {
                font-size: clamp(1.24rem, 7vw, 1.7rem);
            }

            .hero-search-submit,
            .hero-search-reset {
                width: 2.18rem;
                height: 2.18rem;
            }

            .room-item {
                grid-template-columns: 1fr;
            }

            .room-thumb {
                height: 160px;
            }
        }
    </style>
</head>
<body>
<x-public-topnav />

<main class="browse-shell">
    <div class="container">
        @php
            $totalProperties = (int) $properties->count();
            $totalRooms = (int) ($publicRooms->count() ?? 0);
            $availableRooms = (int) $publicRooms->filter(fn ($room) => $room->status === 'available' && $room->hasAvailableSlots())->count();
            $selectedAmenities = $selectedAmenities ?? [];
        @endphp

        <section class="hero-panel mb-4">
            <div class="hero-panel-head">
                <div class="hero-title-block">
                    <h1 class="display-font h3 mt-2 mb-1">Browse Rooms by Map</h1>
                </div>
            </div>

            <div class="hero-search-band">
                <form method="GET" action="{{ route('public.properties.map') }}" class="hero-search-form" id="heroMapSearchForm" aria-label="Map search">
                    <div class="hero-search-row">
                        <i class="bi bi-search hero-search-icon" aria-hidden="true"></i>
                    <input
                        id="heroSearchInput"
                        type="text"
                        name="q"
                        class="form-control hero-search-input"
                        value="{{ $search }}"
                        placeholder="Search"
                        autocomplete="off"
                        data-suggest-url="{{ route('public.properties.suggestions') }}">

                        <button type="submit" class="btn hero-search-submit" aria-label="Search">
                            <i class="bi bi-arrow-right"></i>
                        </button>
                        <a href="{{ route('public.properties.map') }}" class="btn hero-search-reset" id="heroSearchReset" aria-label="Reset search">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    </div>

                    @foreach($selectedAmenities as $amenity)
                        <input type="hidden" name="amenities[]" value="{{ $amenity }}">
                    @endforeach
                    @if($minRating !== null)
                        <input type="hidden" name="min_rating" value="{{ $minRating }}">
                    @endif
                    @if($minPrice !== null)
                        <input type="hidden" name="min_price" value="{{ (int) $minPrice }}">
                    @endif
                    @if($maxPrice !== null)
                        <input type="hidden" name="max_price" value="{{ (int) $maxPrice }}">
                    @endif

                    <div id="heroSearchSuggestionMenu" class="hero-search-suggestion-menu" role="listbox" aria-label="Hero search suggestions"></div>
                </form>
            </div>

            <div class="map-wrap">
                <div class="map-overlay" id="mapHoverHint"><i class="bi bi-cursor-fill"></i> Click a price pin to open property details</div>
                <div id="publicPropertiesMap" data-map-url="{{ route('public.properties.map_data') }}"></div>
                <div id="publicPropertiesMapEmpty" class="map-empty" style="display: none;">
                    <i class="bi bi-map fs-3"></i>
                    <div>No map locations available for your selected filters.</div>
                </div>
            </div>
        </section>

        <section class="info-block">
            <div class="row g-3 align-items-start">
                <div class="col-12 col-xl-8">
                    <div class="list-panel">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h2 class="h5 mb-0">Room List</h2>
                            <button
                                type="button"
                                id="publicMapFilterOpen"
                                class="btn btn-sm btn-outline-success mobile-filter-trigger"
                                data-bs-toggle="offcanvas"
                                data-bs-target="#publicMapFilterSheet"
                                aria-controls="publicMapFilterSheet"
                                aria-label="Open filters">
                                <i class="bi bi-sliders2"></i>
                            </button>
                        </div>

                        <div class="room-list" id="publicRoomList">
                            @forelse($publicRooms as $room)
                                @php
                                    $roomImage = ltrim((string) ($room->image_path ?? ''), '/');
                                    $propertyImage = ltrim((string) ($room->property->image_path ?? ''), '/');

                                    $roomImageExists = $roomImage !== '' && (
                                        \Illuminate\Support\Facades\Storage::disk('public')->exists($roomImage)
                                        || file_exists(public_path('storage/' . $roomImage))
                                    );
                                    $propertyImageExists = $propertyImage !== '' && (
                                        \Illuminate\Support\Facades\Storage::disk('public')->exists($propertyImage)
                                        || file_exists(public_path('storage/' . $propertyImage))
                                    );
                                    $displayImage = $roomImageExists ? $roomImage : ($propertyImageExists ? $propertyImage : null);

                                    $propertyInclusions = collect((array) ($room->property->building_inclusions ?? []))
                                        ->map(fn ($key) => ($amenityOptions ?? [])[$key] ?? trim((string) $key))
                                        ->filter()
                                        ->take(3)
                                        ->values();

                                    $availableSlots = $room->getAvailableSlots();
                                    $occupancy = $room->getOccupancyDisplay();
                                    $isAvailable = $room->status === 'available' && $availableSlots > 0;

                                    $ratingValue = $room->feedbacks_avg_rating !== null
                                        ? (float) $room->feedbacks_avg_rating
                                        : (($room->property->average_rating ?? null) !== null ? (float) $room->property->average_rating : null);

                                    $ratingCount = (int) ($room->feedbacks_count ?? 0);
                                    if ($ratingCount === 0) {
                                        $ratingCount = (int) ($room->property->ratings_count ?? 0);
                                    }
                                @endphp
                                <article class="room-item" data-property-id="{{ $room->property_id }}">
                                    <div class="room-thumb">
                                        @if($displayImage)
                                            <img src="{{ asset('storage/' . $displayImage) }}" alt="Room photo" loading="lazy">
                                        @else
                                            <i class="bi bi-building fs-4"></i>
                                        @endif
                                    </div>

                                    <div>
                                        <div class="d-flex flex-wrap justify-content-between align-items-start gap-2">
                                            <div>
                                                <div class="room-name">{{ $room->room_number }}</div>
                                                <div class="property-name">{{ $room->property->name }}</div>
                                                <div class="room-address"><i class="bi bi-geo-alt"></i> {{ $room->property->address ?: 'Address not available' }}</div>
                                            </div>
                                            <span class="meta-chip" title="Current occupancy">
                                                <i class="bi bi-people"></i>{{ $occupancy }} / {{ (int) $room->capacity }} pax
                                            </span>
                                        </div>

                                        <div class="chip-row">
                                            <span class="meta-chip"><i class="bi bi-cash"></i>₱{{ number_format((float) $room->price, 0) }}/mo</span>
                                            <span class="meta-chip"><i class="bi bi-door-open"></i>{{ $availableSlots }} slot{{ $availableSlots === 1 ? '' : 's' }}</span>
                                            <span class="meta-chip"><i class="bi bi-star-fill text-warning"></i>{{ $ratingValue !== null ? number_format($ratingValue, 1) : 'No rating' }} ({{ $ratingCount }})</span>
                                            <span class="meta-chip"><i class="bi bi-circle-fill {{ $isAvailable ? 'text-success' : 'text-secondary' }}" style="font-size:.5rem;"></i>{{ $isAvailable ? 'Available' : ucfirst((string) $room->status) }}</span>
                                        </div>

                                        @if($propertyInclusions->isNotEmpty())
                                            <div class="chip-row">
                                                @foreach($propertyInclusions as $inclusion)
                                                    <span class="amenity-chip"><i class="bi bi-check-circle"></i>{{ $inclusion }}</span>
                                                @endforeach
                                            </div>
                                        @endif

                                        <div class="d-flex flex-wrap gap-2 mt-2">
                                            <button
                                                type="button"
                                                class="btn btn-sm btn-outline-success rounded-pill js-focus-map"
                                                data-property-id="{{ $room->property_id }}"
                                                @disabled($room->property->latitude === null || $room->property->longitude === null)
                                                data-bs-toggle="tooltip"
                                                data-bs-title="Show this room's property on map">
                                                <i class="bi bi-crosshair2 me-1"></i>Locate on map
                                            </button>
                                            <a class="btn btn-sm btn-outline-success rounded-pill" href="{{ route('public.properties.rooms', $room->property_id) }}">View Property Rooms</a>
                                            <a class="btn btn-sm btn-brand rounded-pill" href="{{ route('rooms.public.show', $room->id) }}">View Room</a>
                                        </div>
                                    </div>
                                </article>
                            @empty
                                <div class="empty-list">
                                    <i class="bi bi-search fs-3"></i>
                                    <div>No rooms matched your filters.</div>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="col-12 col-xl-4">
                    <aside class="filter-panel offcanvas-xl offcanvas-bottom" id="publicMapFilterSheet" tabindex="-1" aria-labelledby="publicMapFilterLabel">
                        <div class="d-flex align-items-center justify-content-between mb-2 filter-sheet-header">
                            <h2 class="h6 mb-0" id="publicMapFilterLabel">Filters</h2>
                            <div class="d-flex align-items-center gap-2">
                                <a href="{{ route('public.properties.map') }}" class="btn btn-sm btn-outline-secondary d-none d-xl-inline-flex" data-filter-reset>Reset</a>
                                <button type="button" id="publicMapFilterClose" class="btn-close d-xl-none" aria-label="Close"></button>
                            </div>
                        </div>

                        <div class="offcanvas-body p-0">

                        <form method="GET" action="{{ route('public.properties.map') }}" id="publicMapFilterForm">
                            <div class="filter-group">
                                <label class="form-label" for="filterSearch">Search</label>
                                <div class="search-wrap">
                                    <input
                                        id="filterSearch"
                                        type="text"
                                        name="q"
                                        class="form-control"
                                        value="{{ $search }}"
                                        placeholder="Property, address, or room"
                                        autocomplete="off"
                                        data-suggest-url="{{ route('public.properties.suggestions') }}">
                                    <div id="searchSuggestionMenu" class="search-suggestion-menu" role="listbox" aria-label="Search suggestions"></div>
                                </div>
                            </div>

                            <div class="filter-group">
                                <label class="form-label">Inclusions</label>
                                <div class="filter-check-grid">
                                    @foreach(($amenityOptions ?? []) as $amenityKey => $amenityLabel)
                                        <label class="filter-option" for="amenity_{{ $amenityKey }}">
                                            <input
                                                id="amenity_{{ $amenityKey }}"
                                                type="checkbox"
                                                name="amenities[]"
                                                value="{{ $amenityKey }}"
                                                class="form-check-input"
                                                @checked(in_array($amenityKey, $selectedAmenities, true))>
                                            <span>{{ $amenityLabel }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>

                            <div class="filter-group mb-1">
                                <label class="form-label">Minimum Rating</label>
                                <div class="filter-check-grid">
                                    <label class="filter-option" for="rating_any">
                                        <input id="rating_any" type="radio" class="form-check-input" name="min_rating" value="" @checked($minRating === null)>
                                        <span>Any rating</span>
                                    </label>
                                    <label class="filter-option" for="rating_4">
                                        <input id="rating_4" type="radio" class="form-check-input" name="min_rating" value="4" @checked((float) ($minRating ?? -1) === 4.0)>
                                        <span>4.0 and above</span>
                                    </label>
                                    <label class="filter-option" for="rating_3">
                                        <input id="rating_3" type="radio" class="form-check-input" name="min_rating" value="3" @checked((float) ($minRating ?? -1) === 3.0)>
                                        <span>3.0 and above</span>
                                    </label>
                                    <label class="filter-option" for="rating_2">
                                        <input id="rating_2" type="radio" class="form-check-input" name="min_rating" value="2" @checked((float) ($minRating ?? -1) === 2.0)>
                                        <span>2.0 and above</span>
                                    </label>
                                </div>
                            </div>

                            <div class="filter-group mt-2">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <label class="form-label mb-0" for="priceMinSlider">Price Range</label>
                                    <span class="helper-note" id="priceRangeLabel">₱{{ number_format((int) ($sliderMinValue ?? 0)) }} - ₱{{ number_format((int) ($sliderMaxValue ?? 0)) }}</span>
                                </div>

                                <div class="price-slider-wrap">
                                    <div class="price-slider-track"></div>
                                    <div class="price-slider-range" id="priceSliderRange"></div>
                                    <input
                                        id="priceMinSlider"
                                        type="range"
                                        min="{{ (int) (($priceBounds['min'] ?? 0)) }}"
                                        max="{{ (int) (($priceBounds['max'] ?? 5000)) }}"
                                        step="100"
                                        value="{{ (int) ($sliderMinValue ?? ($priceBounds['min'] ?? 0)) }}">
                                    <input
                                        id="priceMaxSlider"
                                        type="range"
                                        min="{{ (int) (($priceBounds['min'] ?? 0)) }}"
                                        max="{{ (int) (($priceBounds['max'] ?? 5000)) }}"
                                        step="100"
                                        value="{{ (int) ($sliderMaxValue ?? ($priceBounds['max'] ?? 5000)) }}">
                                </div>

                                <div class="d-flex justify-content-between small text-muted mt-1">
                                    <span id="priceBoundMin">₱{{ number_format((int) (($priceBounds['min'] ?? 0))) }}</span>
                                    <span id="priceBoundMax">₱{{ number_format((int) (($priceBounds['max'] ?? 5000))) }}</span>
                                </div>

                                <input id="filterMinPrice" type="hidden" name="min_price" value="{{ $minPrice !== null ? (int) $minPrice : '' }}">
                                <input id="filterMaxPrice" type="hidden" name="max_price" value="{{ $maxPrice !== null ? (int) $maxPrice : '' }}">
                            </div>

                            <div class="d-flex gap-2 mt-3 filter-actions d-xl-none">
                                <button type="button" class="btn btn-brand flex-fill" id="publicMapFilterApplyMobile">Apply Filters</button>
                                <a href="{{ route('public.properties.map') }}" class="btn btn-outline-secondary" data-filter-reset>Reset</a>
                            </div>

                        </form>

                        </div>
                    </aside>
                </div>
            </div>
        </section>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const tooltipElements = document.querySelectorAll('[data-bs-toggle="tooltip"]');
        tooltipElements.forEach((element) => bootstrap.Tooltip.getOrCreateInstance(element));

        const filterSheetElement = document.getElementById('publicMapFilterSheet');
        const filterSheetOpenButton = document.getElementById('publicMapFilterOpen');
        const filterSheetCloseButton = document.getElementById('publicMapFilterClose');

        const setFilterSheetRevealPoint = (clientX, clientY) => {
            if (!filterSheetElement || !Number.isFinite(clientX) || !Number.isFinite(clientY)) {
                return;
            }

            filterSheetElement.style.setProperty('--reveal-x', `${Math.round(clientX)}px`);
            filterSheetElement.style.setProperty('--reveal-y', `${Math.round(clientY)}px`);
        };

        const setFilterSheetRevealOrigin = (sourceElement) => {
            if (!filterSheetElement || !sourceElement) {
                return;
            }

            const preferredSource = sourceElement.querySelector('i') || sourceElement;
            const sourceRect = preferredSource.getBoundingClientRect();
            const originX = sourceRect.left + (sourceRect.width / 2);
            const originY = sourceRect.top + (sourceRect.height / 2);
            setFilterSheetRevealPoint(originX, originY);
        };

        if (filterSheetElement && filterSheetCloseButton && typeof bootstrap !== 'undefined' && bootstrap.Offcanvas) {
            if (filterSheetOpenButton) {
                filterSheetOpenButton.addEventListener('pointerdown', (event) => {
                    if (Number.isFinite(event.clientX) && Number.isFinite(event.clientY)) {
                        setFilterSheetRevealPoint(event.clientX, event.clientY);
                        return;
                    }

                    setFilterSheetRevealOrigin(filterSheetOpenButton);
                });

                filterSheetOpenButton.addEventListener('click', () => {
                    setFilterSheetRevealOrigin(filterSheetOpenButton);
                });
            }

            filterSheetCloseButton.addEventListener('click', (event) => {
                event.preventDefault();
                setFilterSheetRevealOrigin(filterSheetCloseButton);
                bootstrap.Offcanvas.getOrCreateInstance(filterSheetElement).hide();
            });

            filterSheetElement.addEventListener('hide.bs.offcanvas', () => {
                if (!filterSheetElement.style.getPropertyValue('--reveal-x')) {
                    setFilterSheetRevealOrigin(filterSheetCloseButton);
                }
            });
        }

        const searchSuggestors = [];

        const bindSuggestionInput = (inputId, menuId) => {
            const input = document.getElementById(inputId);
            const menu = document.getElementById(menuId);
            const suggestUrl = input ? input.dataset.suggestUrl : null;

            if (!input || !menu || !suggestUrl) {
                return;
            }

            let timer = null;
            let controller = null;

            const close = () => {
                menu.classList.remove('is-open');
                menu.innerHTML = '';
            };

            const render = (items) => {
                menu.innerHTML = '';

                const cleanItems = (items || [])
                    .map((item) => String(item || '').trim())
                    .filter((item) => item.length > 0)
                    .slice(0, 8);

                if (!cleanItems.length) {
                    close();
                    return;
                }

                cleanItems.forEach((item) => {
                    const button = document.createElement('button');
                    button.type = 'button';
                    button.className = 'search-suggestion-item';
                    button.textContent = item;
                    button.addEventListener('click', () => {
                        input.value = item;
                        close();
                        if (input.form) {
                            if (typeof input.form.requestSubmit === 'function') {
                                input.form.requestSubmit();
                            } else {
                                input.form.dispatchEvent(new Event('submit', { bubbles: true, cancelable: true }));
                            }
                        }
                    });
                    menu.appendChild(button);
                });

                menu.classList.add('is-open');
            };

            const fetchSuggestions = (term = '') => {
                if (controller) {
                    controller.abort();
                }

                controller = new AbortController();
                const requestUrl = `${suggestUrl}?q=${encodeURIComponent(term)}`;

                fetch(requestUrl, { signal: controller.signal })
                    .then((response) => response.json())
                    .then((payload) => {
                        render(payload.suggestions || []);
                    })
                    .catch(() => {});
            };

            input.addEventListener('focus', () => {
                fetchSuggestions(input.value || '');
            });

            input.addEventListener('input', () => {
                clearTimeout(timer);
                timer = setTimeout(() => {
                    fetchSuggestions(input.value || '');
                }, 220);
            });

            input.addEventListener('keydown', (event) => {
                if (event.key === 'Escape') {
                    close();
                }
            });

            searchSuggestors.push({ input, menu, close });
        };

        bindSuggestionInput('heroSearchInput', 'heroSearchSuggestionMenu');
        bindSuggestionInput('filterSearch', 'searchSuggestionMenu');

        document.addEventListener('click', (event) => {
            const target = event.target;

            searchSuggestors.forEach(({ input, menu, close }) => {
                if (menu.contains(target) || target === input) {
                    return;
                }
                close();
            });
        });

        const minSlider = document.getElementById('priceMinSlider');
        const maxSlider = document.getElementById('priceMaxSlider');
        const minPriceInput = document.getElementById('filterMinPrice');
        const maxPriceInput = document.getElementById('filterMaxPrice');
        const rangeLabel = document.getElementById('priceRangeLabel');
        const rangeFill = document.getElementById('priceSliderRange');

        const formatPeso = (value) => `₱${Math.round(Number(value || 0)).toLocaleString()}`;

        const updatePriceFilter = () => {
            if (!minSlider || !maxSlider || !minPriceInput || !maxPriceInput || !rangeLabel || !rangeFill) return;

            const boundsMin = Number(minSlider.min || 0);
            const boundsMax = Number(minSlider.max || 0);
            const span = Math.max(1, boundsMax - boundsMin);

            const minValue = Number(minSlider.value || boundsMin);
            const maxValue = Number(maxSlider.value || boundsMax);

            if (minValue <= boundsMin && maxValue >= boundsMax) {
                minPriceInput.value = '';
                maxPriceInput.value = '';
            } else {
                minPriceInput.value = String(Math.round(minValue));
                maxPriceInput.value = String(Math.round(maxValue));
            }

            rangeLabel.textContent = `${formatPeso(minValue)} - ${formatPeso(maxValue)}`;

            const leftPercent = ((minValue - boundsMin) / span) * 100;
            const widthPercent = ((maxValue - minValue) / span) * 100;
            rangeFill.style.left = `${Math.max(0, leftPercent)}%`;
            rangeFill.style.right = `${Math.max(0, 100 - leftPercent - widthPercent)}%`;
        };

        if (minSlider && maxSlider) {
            updatePriceFilter();
        }

        const mapElement = document.getElementById('publicPropertiesMap');
        const emptyElement = document.getElementById('publicPropertiesMapEmpty');
        const roomListElement = document.getElementById('publicRoomList');
        const heroForm = document.getElementById('heroMapSearchForm');
        const heroSearchInput = document.getElementById('heroSearchInput');
        const heroSearchReset = document.getElementById('heroSearchReset');
        const filterForm = document.getElementById('publicMapFilterForm');
        const filterSearchInput = document.getElementById('filterSearch');
        const filterResetButtons = Array.from(document.querySelectorAll('[data-filter-reset]'));
        const filterApplyMobileButton = document.getElementById('publicMapFilterApplyMobile');

        if (!mapElement || !filterForm || !heroSearchInput || !filterSearchInput) {
            return;
        }

        const mapDataBaseUrl = mapElement.dataset.mapUrl;
        const pageBaseUrl = filterForm.getAttribute('action') || window.location.pathname;

        let map = null;
        let markerLayer = null;
        let activeFetchController = null;
        let mapFetchToken = 0;
        const markerByPropertyId = new Map();
        const mapWrap = document.querySelector('.map-wrap');

        const escapeHtml = (value) => {
            return String(value ?? '')
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;')
                .replaceAll("'", '&#039;');
        };

        const scrollToMap = () => {
            if (!mapWrap) return;
            const topOffset = Math.max(0, Math.round((mapWrap.getBoundingClientRect().top + window.scrollY) - 94));
            window.scrollTo({ top: topOffset, behavior: 'smooth' });
        };

        const closeFilterSheet = () => {
            if (!filterSheetElement || !filterSheetElement.classList.contains('show') || typeof bootstrap === 'undefined' || !bootstrap.Offcanvas) {
                return;
            }

            bootstrap.Offcanvas.getOrCreateInstance(filterSheetElement).hide();
        };

        const syncSearchInputs = (source) => {
            const sourceValue = String(source?.value || '');
            heroSearchInput.value = sourceValue;
            filterSearchInput.value = sourceValue;
        };

        const collectFilterParams = () => {
            const params = new URLSearchParams();
            const queryText = String(filterSearchInput.value || '').trim();
            if (queryText !== '') {
                params.set('q', queryText);
            }

            filterForm.querySelectorAll('input[name="amenities[]"]:checked').forEach((input) => {
                if (input.value) {
                    params.append('amenities[]', input.value);
                }
            });

            const minRatingInput = filterForm.querySelector('input[name="min_rating"]:checked');
            if (minRatingInput && String(minRatingInput.value || '').trim() !== '') {
                params.set('min_rating', String(minRatingInput.value).trim());
            }

            const minPriceValue = String(minPriceInput?.value || '').trim();
            const maxPriceValue = String(maxPriceInput?.value || '').trim();
            if (minPriceValue !== '') {
                params.set('min_price', minPriceValue);
            }
            if (maxPriceValue !== '') {
                params.set('max_price', maxPriceValue);
            }

            return params;
        };

        const formatPriceLabel = (minPrice, maxPrice) => {
            if (minPrice === null && maxPrice === null) return 'Price TBD';
            if (minPrice !== null && maxPrice !== null && Number(minPrice) !== Number(maxPrice)) {
                return `₱${Number(minPrice).toLocaleString()}-₱${Number(maxPrice).toLocaleString()}`;
            }
            const single = minPrice !== null ? minPrice : maxPrice;
            return `₱${Number(single).toLocaleString()}`;
        };

        const buildPopupHtml = (property) => {
            const imageHtml = property.image_url
                ? `<img src="${escapeHtml(property.image_url)}" alt="${escapeHtml(property.name)} preview">`
                : '<i class="bi bi-building fs-3"></i>';

            const inclusions = Array.isArray(property.inclusions) ? property.inclusions.slice(0, 4) : [];
            const inclusionHtml = inclusions.length
                ? `<div class="d-flex flex-wrap gap-1 mt-1">${inclusions.map((label) => `<span class="badge text-bg-light border">${escapeHtml(label)}</span>`).join('')}</div>`
                : '';

            const ratingLabel = property.rating === null
                ? 'No rating yet'
                : `${Number(property.rating).toFixed(1)} (${Number(property.ratings_count || 0)})`;

            const roomsUrl = property.rooms_url ? escapeHtml(property.rooms_url) : '#';

            return `
                <div class="map-popup">
                    <div class="map-popup-photo">${imageHtml}</div>
                    <div class="map-popup-title">${escapeHtml(property.name)}</div>
                    <div class="map-popup-address">${escapeHtml(property.address || 'Address not available')}</div>
                    <div class="map-popup-meta">
                        <span class="badge text-bg-light">${escapeHtml(String(property.available_rooms_count || 0))} room(s)</span>
                        <span class="map-popup-price">${escapeHtml(formatPriceLabel(property.price_min, property.price_max))}</span>
                    </div>
                    <div class="small text-muted mb-1"><i class="bi bi-star-fill text-warning"></i> ${escapeHtml(ratingLabel)}</div>
                    ${inclusionHtml}
                    <a class="btn btn-sm btn-brand w-100 mt-2" href="${roomsUrl}">View Rooms</a>
                </div>
            `;
        };

        const ensureMap = () => {
            if (map) return map;

            map = L.map('publicPropertiesMap', {
                zoomControl: true,
            }).setView([13.4115, 121.1806], 11);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxNativeZoom: 19,
                maxZoom: 22,
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(map);

            markerLayer = L.layerGroup().addTo(map);
            return map;
        };

        const renderMap = (properties) => {
            const mapItems = Array.isArray(properties) ? properties : [];

            if (!mapItems.length) {
                mapElement.style.display = 'none';
                if (emptyElement) {
                    emptyElement.style.display = 'flex';
                    emptyElement.innerHTML = '<i class="bi bi-map fs-3"></i><div>No map locations available for your selected filters.</div>';
                }
                markerByPropertyId.clear();
                if (markerLayer) {
                    markerLayer.clearLayers();
                }
                return;
            }

            mapElement.style.display = 'block';
            if (emptyElement) {
                emptyElement.style.display = 'none';
            }

            const mapInstance = ensureMap();
            markerLayer.clearLayers();
            markerByPropertyId.clear();

            const bounds = [];

            mapItems.forEach((property) => {
                const markerLabel = formatPriceLabel(property.price_min, property.price_max);
                const markerWidth = Math.max(74, Math.min(170, Math.round(markerLabel.length * 7.2 + 24)));

                const markerIcon = L.divIcon({
                    className: 'price-pill-wrap',
                    html: `<div class="price-pill-marker ${property.price_min === null && property.price_max === null ? 'is-empty' : ''}">${escapeHtml(markerLabel)}</div>`,
                    iconSize: [markerWidth, 30],
                    iconAnchor: [Math.round(markerWidth / 2), 15],
                    popupAnchor: [0, -12],
                });

                const marker = L.marker([property.lat, property.lng], { icon: markerIcon })
                    .addTo(markerLayer)
                    .bindPopup(buildPopupHtml(property));

                markerByPropertyId.set(Number(property.id), marker);
                bounds.push([property.lat, property.lng]);
            });

            if (bounds.length > 1) {
                mapInstance.fitBounds(bounds, { padding: [26, 26] });
            } else if (bounds.length === 1) {
                mapInstance.setView(bounds[0], 15);
            }

            setTimeout(() => {
                mapInstance.invalidateSize();
            }, 60);
        };

        const buildRoomCardHtml = (room) => {
            const imageHtml = room.display_image_url
                ? `<img src="${escapeHtml(room.display_image_url)}" alt="Room photo" loading="lazy">`
                : '<i class="bi bi-building fs-4"></i>';

            const inclusions = Array.isArray(room.inclusions) ? room.inclusions : [];
            const inclusionHtml = inclusions.length
                ? `<div class="chip-row">${inclusions.map((label) => `<span class="amenity-chip"><i class="bi bi-check-circle"></i>${escapeHtml(label)}</span>`).join('')}</div>`
                : '';

            const ratingText = room.rating === null
                ? 'No rating'
                : `${Number(room.rating).toFixed(1)}`;

            const statusClass = room.is_available ? 'text-success' : 'text-secondary';
            const statusLabel = room.is_available ? 'Available' : escapeHtml(room.status_label || room.status || 'Unavailable');
            const locateDisabled = room.can_focus_map ? '' : 'disabled';
            const propertyAddress = room.property_address && String(room.property_address).trim() !== ''
                ? room.property_address
                : 'Address not available';

            return `
                <article class="room-item" data-property-id="${Number(room.property_id || 0)}">
                    <div class="room-thumb">${imageHtml}</div>
                    <div>
                        <div class="d-flex flex-wrap justify-content-between align-items-start gap-2">
                            <div>
                                <div class="room-name">${escapeHtml(room.room_number || '-')}</div>
                                <div class="property-name">${escapeHtml(room.property_name || '')}</div>
                                <div class="room-address"><i class="bi bi-geo-alt"></i> ${escapeHtml(propertyAddress)}</div>
                            </div>
                            <span class="meta-chip" title="Current occupancy">
                                <i class="bi bi-people"></i>${escapeHtml(room.occupancy || '0')} / ${Number(room.capacity || 0)} pax
                            </span>
                        </div>

                        <div class="chip-row">
                            <span class="meta-chip"><i class="bi bi-cash"></i>₱${Math.round(Number(room.price || 0)).toLocaleString()}/mo</span>
                            <span class="meta-chip"><i class="bi bi-door-open"></i>${Number(room.available_slots || 0)} slot${Number(room.available_slots || 0) === 1 ? '' : 's'}</span>
                            <span class="meta-chip"><i class="bi bi-star-fill text-warning"></i>${escapeHtml(ratingText)} (${Number(room.ratings_count || 0)})</span>
                            <span class="meta-chip"><i class="bi bi-circle-fill ${statusClass}" style="font-size:.5rem;"></i>${statusLabel}</span>
                        </div>

                        ${inclusionHtml}

                        <div class="d-flex flex-wrap gap-2 mt-2">
                            <button type="button" class="btn btn-sm btn-outline-success rounded-pill js-focus-map" data-property-id="${Number(room.property_id || 0)}" ${locateDisabled}>
                                <i class="bi bi-crosshair2 me-1"></i>Locate on map
                            </button>
                            <a class="btn btn-sm btn-outline-success rounded-pill" href="${escapeHtml(room.property_rooms_url || '#')}">View Property Rooms</a>
                            <a class="btn btn-sm btn-brand rounded-pill" href="${escapeHtml(room.room_url || '#')}">View Room</a>
                        </div>
                    </div>
                </article>
            `;
        };

        const renderRoomList = (rooms) => {
            if (!roomListElement) return;

            const roomItems = Array.isArray(rooms) ? rooms : [];
            if (!roomItems.length) {
                roomListElement.innerHTML = `
                    <div class="empty-list">
                        <i class="bi bi-search fs-3"></i>
                        <div>No rooms matched your filters.</div>
                    </div>
                `;
                return;
            }

            roomListElement.innerHTML = roomItems.map((room) => buildRoomCardHtml(room)).join('');
        };

        const focusPropertyOnMap = (propertyId) => {
            if (!map) return;
            const marker = markerByPropertyId.get(Number(propertyId || 0));
            if (!marker) return;

            scrollToMap();
            const markerLatLng = marker.getLatLng();
            map.flyTo(markerLatLng, 15, { duration: 0.6 });
            marker.openPopup();
            setTimeout(() => {
                map.invalidateSize();
            }, 260);
        };

        const applyFiltersAjax = ({ pushHistory = true } = {}) => {
            const params = collectFilterParams();
            const queryString = params.toString();
            const requestUrl = mapDataBaseUrl + (queryString ? `?${queryString}` : '');
            const requestToken = ++mapFetchToken;

            if (activeFetchController) {
                activeFetchController.abort();
            }

            activeFetchController = new AbortController();

            if (roomListElement) {
                roomListElement.innerHTML = '<div class="empty-list"><i class="bi bi-hourglass-split fs-3"></i><div>Updating results...</div></div>';
            }

            fetch(requestUrl, { signal: activeFetchController.signal })
                .then((response) => response.json())
                .then((payload) => {
                    if (requestToken !== mapFetchToken) {
                        return;
                    }

                    renderMap(payload.properties || []);
                    renderRoomList(payload.rooms || []);

                    if (pushHistory) {
                        const nextUrl = pageBaseUrl + (queryString ? `?${queryString}` : '');
                        window.history.replaceState({}, '', nextUrl);
                    }
                })
                .catch((error) => {
                    if (error && error.name === 'AbortError') {
                        return;
                    }

                    mapElement.style.display = 'none';
                    if (emptyElement) {
                        emptyElement.style.display = 'flex';
                        emptyElement.innerHTML = '<i class="bi bi-exclamation-circle fs-3"></i><div>Unable to load map data right now.</div>';
                    }
                    if (roomListElement) {
                        roomListElement.innerHTML = '<div class="empty-list"><i class="bi bi-exclamation-circle fs-3"></i><div>Unable to load room list right now.</div></div>';
                    }
                });
        };

        const debounce = (fn, delay = 260) => {
            let timer = null;
            return (...args) => {
                clearTimeout(timer);
                timer = setTimeout(() => fn(...args), delay);
            };
        };

        const debouncedApplyFilters = debounce(() => {
            applyFiltersAjax({ pushHistory: true });
        }, 260);

        const resetAllFilters = ({ closeSheet = false } = {}) => {
            syncSearchInputs({ value: '' });

            filterForm.querySelectorAll('input[name="amenities[]"]').forEach((checkbox) => {
                checkbox.checked = false;
            });

            const anyRatingRadio = filterForm.querySelector('input[name="min_rating"][value=""]');
            if (anyRatingRadio) {
                anyRatingRadio.checked = true;
            }

            if (minSlider && maxSlider) {
                minSlider.value = minSlider.min;
                maxSlider.value = maxSlider.max;
                updatePriceFilter();
            }

            applyFiltersAjax({ pushHistory: true });

            if (closeSheet) {
                closeFilterSheet();
            }
        };

        if (roomListElement) {
            roomListElement.addEventListener('click', (event) => {
                const button = event.target.closest('.js-focus-map');
                if (!button || button.disabled) return;
                event.preventDefault();
                focusPropertyOnMap(button.dataset.propertyId);
            });
        }

        if (heroForm) {
            heroForm.addEventListener('submit', (event) => {
                event.preventDefault();
                syncSearchInputs(heroSearchInput);
                applyFiltersAjax({ pushHistory: true });
            });
        }

        filterForm.addEventListener('submit', (event) => {
            event.preventDefault();
            syncSearchInputs(filterSearchInput);
            applyFiltersAjax({ pushHistory: true });
            closeFilterSheet();
        });

        filterSearchInput.addEventListener('input', () => {
            syncSearchInputs(filterSearchInput);
            debouncedApplyFilters();
        });

        filterForm.querySelectorAll('input[name="amenities[]"], input[name="min_rating"]').forEach((input) => {
            input.addEventListener('change', () => {
                debouncedApplyFilters();
            });
        });

        if (heroSearchInput) {
            heroSearchInput.addEventListener('input', () => {
                filterSearchInput.value = heroSearchInput.value;
            });
        }

        if (heroSearchReset) {
            heroSearchReset.addEventListener('click', (event) => {
                event.preventDefault();
                resetAllFilters();
            });
        }

        filterResetButtons.forEach((button) => {
            button.addEventListener('click', (event) => {
                event.preventDefault();
                resetAllFilters({ closeSheet: true });
            });
        });

        if (filterApplyMobileButton) {
            filterApplyMobileButton.addEventListener('click', () => {
                syncSearchInputs(filterSearchInput);
                applyFiltersAjax({ pushHistory: true });
                closeFilterSheet();
            });
        }

        if (minSlider && maxSlider) {
            minSlider.addEventListener('input', () => {
                if (Number(minSlider.value) > Number(maxSlider.value)) {
                    maxSlider.value = minSlider.value;
                }
                updatePriceFilter();
                debouncedApplyFilters();
            });

            maxSlider.addEventListener('input', () => {
                if (Number(maxSlider.value) < Number(minSlider.value)) {
                    minSlider.value = maxSlider.value;
                }
                updatePriceFilter();
                debouncedApplyFilters();
            });
        }

        applyFiltersAjax({ pushHistory: false });
    });
</script>
</body>
</html>

