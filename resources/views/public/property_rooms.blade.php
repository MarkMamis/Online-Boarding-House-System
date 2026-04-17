<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $property->name }} Rooms - Online Boarding House System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Bricolage+Grotesque:opsz,wght@12..96,600;12..96,700&display=swap" rel="stylesheet">
    <style>
        :root {
            --brand: #14532d;
            --brand-2: #166534;
            --ink: #0f172a;
            --paper: #f8fafc;
            --line: rgba(2, 8, 20, .1);
        }

        body {
            font-family: 'Manrope', system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
            color: var(--ink);
            background: radial-gradient(1200px circle at -10% -10%, rgba(34, 197, 94, .18), transparent 45%),
                        radial-gradient(1100px circle at 115% 0%, rgba(20, 83, 45, .1), transparent 55%),
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
            color: rgba(255,255,255,.92);
            font-weight: 600;
            letter-spacing: .01em;
        }

        .navbar-green .nav-link:hover {
            color: #fff;
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

        .shell {
            padding-top: 6rem;
            padding-bottom: 2.2rem;
        }

        .hero {
            position: relative;
            min-height: clamp(390px, 58vh, 560px);
            border-radius: 1.2rem;
            overflow: hidden;
            border: 1px solid rgba(2, 8, 20, .1);
            box-shadow: 0 20px 36px rgba(15, 23, 42, .18);
            background: linear-gradient(125deg, rgba(20, 83, 45, .96), rgba(22, 101, 52, .92));
            color: #ecfdf5;
        }

        .hero-image {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: 50% 50%;
            transform: translate3d(0, 0, 0) scale(1.16);
            will-change: transform;
            animation-play-state: running;
        }

        .hero-image.is-portrait {
            animation: heroPanY 12s ease-in-out infinite alternate;
        }

        .hero-image.is-landscape {
            animation: heroPanX 14s ease-in-out infinite alternate;
        }

        @keyframes heroPanY {
            0% {
                transform: translate3d(0, -12%, 0) scale(1.18);
            }
            100% {
                transform: translate3d(0, 12%, 0) scale(1.18);
            }
        }

        @keyframes heroPanX {
            0% {
                transform: translate3d(-12%, 0, 0) scale(1.18);
            }
            100% {
                transform: translate3d(12%, 0, 0) scale(1.18);
            }
        }

        .hero-overlay {
            position: absolute;
            inset: 0;
            background:
                linear-gradient(180deg, rgba(8, 20, 12, .22) 0%, rgba(8, 20, 12, .68) 78%, rgba(8, 20, 12, .84) 100%),
                radial-gradient(900px circle at -5% 105%, rgba(16, 185, 129, .35), transparent 56%);
        }

        .hero-content {
            position: relative;
            z-index: 1;
            min-height: clamp(390px, 58vh, 560px);
            padding: 1.05rem 1.2rem;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            gap: .8rem;
        }

        .hero-top-row {
            display: flex;
            justify-content: flex-end;
        }

        .hero-bottom-row {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 1rem;
        }

        .hero-left {
            min-width: 0;
        }

        .hero-right {
            text-align: right;
            min-width: 220px;
        }

        .property-name {
            font-size: 3.15rem;
            font-weight: 800;
            margin: 0;
            line-height: 1;
            text-shadow: 0 8px 20px rgba(0, 0, 0, .3);
        }

        .property-address {
            margin: .25rem 0 0;
            color: rgba(236, 253, 245, .95);
            font-size: 1.92rem;
            font-weight: 700;
            line-height: 1.06;
            text-shadow: 0 6px 18px rgba(0, 0, 0, .3);
        }

        .property-landlord-btn {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            margin-top: .72rem;
            border-radius: 999px;
            border: 1px solid rgba(220, 252, 231, .65);
            background: transparent;
            color: #ecfdf5;
            font-size: .83rem;
            font-weight: 700;
            padding: .34rem .74rem;
            transition: transform .16s ease, background .2s ease, border-color .2s ease;
        }

        .property-landlord-btn:focus-visible {
            transform: translateY(-1px);
            background: #ffffff;
            border-color: #ffffff;
            color: #14532d;
            outline: none;
        }

        .hero.is-landlord-cta .property-landlord-btn {
            background: #ffffff;
            border-color: #ffffff;
            color: #14532d;
            box-shadow: 0 10px 22px rgba(2, 8, 20, .24);
            transform: translateY(-1px);
        }

        .rooms-scroll-dock {
            position: fixed;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: 1100;
            display: flex;
            justify-content: center;
            align-items: flex-end;
            pointer-events: none;
            transition: opacity .25s ease, transform .25s ease;
        }

        .rooms-scroll-dock::before {
            content: "";
            position: absolute;
            inset: 0;
            height: 118px;
            background: linear-gradient(180deg, rgba(248, 250, 252, 0) 0%, rgba(15, 23, 42, .35) 100%);
            pointer-events: none;
        }

        .rooms-scroll-dock.is-hidden,
        .rooms-scroll-dock.is-hidden-modal {
            opacity: 0;
            transform: translateY(18px);
        }

        .rooms-scroll-dock-inner {
            position: relative;
            pointer-events: auto;
            margin-bottom: .55rem;
            border: 1px solid rgba(255, 255, 255, .75);
            border-radius: 999px;
            background: rgba(15, 23, 42, .72);
            color: #f8fafc;
            padding: .45rem .95rem;
            display: inline-flex;
            align-items: center;
            gap: .52rem;
            box-shadow: 0 14px 28px rgba(2, 8, 20, .28);
            text-decoration: none;
            font-size: .85rem;
            font-weight: 700;
            animation: roomsDockFloat 2.2s ease-in-out infinite;
        }

        .rooms-scroll-dock-inner:hover {
            color: #fff;
            background: rgba(15, 23, 42, .82);
        }

        .rooms-scroll-dock-inner .icon {
            animation: roomsDockArrow 1.2s ease-in-out infinite;
        }

        @keyframes roomsDockFloat {
            0%, 100% {
                transform: translateY(0);
            }
            50% {
                transform: translateY(-5px);
            }
        }

        @keyframes roomsDockArrow {
            0%, 100% {
                transform: translateY(0);
            }
            50% {
                transform: translateY(3px);
            }
        }

        .hero-price-range {
            font-size: 3rem;
            font-weight: 800;
            line-height: 1;
            color: #f8fafc;
            text-shadow: 0 8px 20px rgba(0, 0, 0, .33);
        }

        .hero-room-count {
            margin-top: .2rem;
            font-size: 1.9rem;
            font-weight: 700;
            line-height: 1.06;
            color: rgba(236, 253, 245, .95);
            text-shadow: 0 6px 16px rgba(0, 0, 0, .3);
        }

        .hero-meta {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            border-radius: 999px;
            border: 1px solid rgba(167, 243, 208, .45);
            background: rgba(167, 243, 208, .24);
            padding: .32rem .7rem;
            font-size: .8rem;
            font-weight: 600;
            color: #ecfdf5;
        }

        .room-list {
            display: flex;
            flex-direction: column;
            gap: .82rem;
            margin-top: 1rem;
        }

        .room-card {
            border: 1px solid var(--line);
            border-radius: 1rem;
            background: #fff;
            box-shadow: 0 10px 24px rgba(2, 8, 20, .07);
            overflow: hidden;
            display: grid;
            grid-template-columns: 240px minmax(0, 1fr);
            transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease;
            position: relative;
        }

        .room-card::before {
            content: "";
            position: absolute;
            inset: 0 auto 0 0;
            width: 4px;
            background: linear-gradient(180deg, #22c55e, #166534);
            opacity: .92;
        }

        .room-card:hover {
            transform: translateY(-2px);
            border-color: rgba(20, 83, 45, .35);
            box-shadow: 0 14px 26px rgba(2, 8, 20, .1);
        }

        .room-photo {
            min-height: 172px;
            border-right: 1px solid var(--line);
            background: linear-gradient(145deg, rgba(167, 243, 208, .35), #f8fafc);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #0f766e;
            overflow: hidden;
        }

        .room-photo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .room-body {
            padding: .92rem 1rem;
            display: grid;
            grid-template-columns: minmax(0, 1fr) 186px;
            gap: .9rem;
            align-items: stretch;
        }

        .room-head {
            display: flex;
            flex-wrap: wrap;
            align-items: flex-start;
            justify-content: space-between;
            gap: .45rem;
        }

        .room-meta-grid {
            display: grid;
            grid-template-columns: repeat(2, max-content);
            gap: .3rem .42rem;
            justify-content: end;
            align-content: start;
        }

        .room-meta-grid .chip {
            white-space: nowrap;
        }

        .room-title {
            font-size: 1.08rem;
            font-weight: 700;
            margin: 0;
            color: #0b1220;
        }

        .room-subtitle {
            font-size: .78rem;
            color: rgba(15, 23, 42, .64);
            margin-top: .12rem;
        }

        .room-submeta {
            margin-top: .14rem;
            font-size: .74rem;
            color: rgba(51, 65, 85, .78);
            font-weight: 600;
            letter-spacing: .01em;
        }

        .chip-row {
            display: flex;
            flex-wrap: wrap;
            gap: .3rem;
        }

        .chip {
            border-radius: 999px;
            border: 1px solid rgba(2, 8, 20, .12);
            background: #f8fafc;
            color: #0f172a;
            font-size: .72rem;
            font-weight: 600;
            padding: .18rem .52rem;
            display: inline-flex;
            align-items: center;
            gap: .24rem;
        }

        .inclusion-chip {
            border-radius: 999px;
            border: 1px solid rgba(22, 101, 52, .24);
            background: rgba(167, 243, 208, .2);
            color: #14532d;
            font-size: .7rem;
            font-weight: 600;
            padding: .16rem .5rem;
            display: inline-flex;
            align-items: center;
            gap: .26rem;
        }

        .inclusion-title {
            font-size: .72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .04em;
            color: rgba(15, 23, 42, .56);
            margin-bottom: .3rem;
        }

        .room-action-row {
            margin-top: .05rem;
            display: grid;
            gap: .45rem;
        }

        .room-main {
            display: flex;
            flex-direction: column;
            gap: .6rem;
        }

        .room-side {
            border-left: 1px dashed rgba(2, 8, 20, .16);
            padding-left: .78rem;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            gap: .65rem;
            min-height: 100%;
        }

        .price-tag {
            border-radius: .85rem;
            border: 1px solid rgba(34, 197, 94, .28);
            background: linear-gradient(145deg, rgba(220, 252, 231, .62), rgba(240, 253, 244, .9));
            padding: .58rem .64rem;
            text-align: right;
        }

        .price-label {
            font-size: .65rem;
            text-transform: uppercase;
            letter-spacing: .05em;
            color: rgba(15, 23, 42, .55);
            font-weight: 700;
        }

        .price-value {
            font-size: 1.65rem;
            line-height: 1.05;
            font-weight: 800;
            color: #14532d;
        }

        .price-period {
            font-size: .75rem;
            color: rgba(15, 23, 42, .58);
            font-weight: 600;
        }

        .review-card {
            border: 1px dashed rgba(148, 163, 184, .42);
            border-radius: .8rem;
            background: #fbfdff;
            padding: .55rem .6rem;
        }

        .review-title {
            font-size: .72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .04em;
            color: rgba(15, 23, 42, .58);
            margin-bottom: .35rem;
        }

        .review-item {
            font-size: .78rem;
            color: #334155;
        }

        .review-item + .review-item {
            margin-top: .34rem;
            padding-top: .34rem;
            border-top: 1px dashed rgba(148, 163, 184, .35);
        }

        .review-head {
            display: flex;
            justify-content: space-between;
            gap: .4rem;
            font-size: .72rem;
            color: #475569;
            margin-bottom: .1rem;
        }

        .review-empty {
            font-size: .76rem;
            color: rgba(15, 23, 42, .52);
        }

        .review-stars {
            color: #f59e0b;
            letter-spacing: .03em;
            white-space: nowrap;
        }

        .modal-soft-label {
            font-size: .7rem;
            text-transform: uppercase;
            letter-spacing: .05em;
            color: #64748b;
            font-weight: 700;
            margin-bottom: .14rem;
        }

        .modal-soft-value {
            font-size: .92rem;
            color: #0f172a;
            margin-bottom: .58rem;
        }

        .landlord-modal-dialog {
            max-width: 920px;
        }

        .landlord-modal-content {
            overflow: hidden;
            border: 0;
            border-radius: 1.1rem;
            box-shadow: 0 26px 52px rgba(2, 8, 20, .34);
        }

        .landlord-modal-layout {
            display: grid;
            grid-template-columns: minmax(260px, 38%) minmax(0, 1fr);
            min-height: 470px;
        }

        .landlord-modal-media {
            position: relative;
            background: linear-gradient(165deg, rgba(20, 83, 45, .95), rgba(22, 101, 52, .9));
            border-right: 1px solid rgba(148, 163, 184, .2);
        }

        .landlord-modal-media::after {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(180deg, rgba(15, 23, 42, .12), rgba(15, 23, 42, .55));
        }

        .landlord-modal-media img {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .landlord-modal-media-fallback {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            color: rgba(236, 253, 245, .95);
            z-index: 1;
        }

        .landlord-modal-media-caption {
            position: absolute;
            left: 1rem;
            right: 1rem;
            bottom: 1rem;
            z-index: 2;
            color: #ecfdf5;
        }

        .landlord-modal-media-caption .title {
            font-size: 1.04rem;
            font-weight: 800;
            line-height: 1.1;
        }

        .landlord-modal-media-caption .sub {
            font-size: .8rem;
            opacity: .92;
            margin-top: .15rem;
        }

        .landlord-modal-panel {
            background: #fff;
            padding: 1rem 1.1rem;
            position: relative;
            overflow-y: auto;
        }

        .landlord-modal-close {
            position: absolute;
            top: .8rem;
            right: .8rem;
            border: 0;
            background: transparent;
            color: #64748b;
            font-size: 1.65rem;
            line-height: 1;
            padding: 0;
            cursor: pointer;
        }

        .landlord-modal-close:hover {
            color: #0f172a;
        }

        .landlord-modal-heading {
            padding-right: 2rem;
            margin-bottom: .68rem;
            border-bottom: 1px solid rgba(148, 163, 184, .25);
            padding-bottom: .55rem;
        }

        .landlord-modal-title {
            font-size: 1.06rem;
            font-weight: 800;
            color: #0f172a;
            line-height: 1.15;
        }

        .landlord-modal-sub {
            font-size: .8rem;
            color: #475569;
            margin-top: .15rem;
        }

        .property-name-list {
            margin: 0;
            padding-left: 1rem;
            color: #0f172a;
            font-size: .9rem;
        }

        .property-name-list li + li {
            margin-top: .18rem;
        }

        .chip-available {
            background: rgba(187, 247, 208, .35);
            border-color: rgba(34, 197, 94, .45);
            color: #166534;
        }

        .chip-unavailable {
            background: #f1f5f9;
            border-color: rgba(148, 163, 184, .45);
            color: #475569;
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

        .empty-state {
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
            margin-top: 1rem;
        }

        @media (max-width: 991.98px) {
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

            .shell {
                padding-top: 5.6rem;
            }

            .property-name {
                font-size: 2.6rem;
            }

            .property-address {
                font-size: 1.5rem;
            }

            .hero-price-range {
                font-size: 2.2rem;
            }

            .hero-room-count {
                font-size: 1.45rem;
            }
        }

        @media (max-width: 767.98px) {
            .hero {
                min-height: 340px;
            }

            .hero-content {
                min-height: 340px;
                padding: .9rem;
            }

            .hero-bottom-row {
                flex-direction: column;
                align-items: flex-start;
            }

            .hero-right {
                text-align: left;
                min-width: 0;
            }

            .property-name {
                font-size: 2.2rem;
            }

            .property-address {
                font-size: 1.32rem;
            }

            .hero-price-range {
                font-size: 1.9rem;
            }

            .hero-room-count {
                font-size: 1.22rem;
            }

            .landlord-modal-layout {
                grid-template-columns: 1fr;
                min-height: 0;
            }

            .landlord-modal-media {
                min-height: 220px;
                border-right: 0;
                border-bottom: 1px solid rgba(148, 163, 184, .2);
            }

            .room-card {
                grid-template-columns: 1fr;
            }

            .room-photo {
                min-height: 180px;
                border-right: 0;
                border-bottom: 1px solid var(--line);
            }

            .room-body {
                grid-template-columns: 1fr;
            }

            .room-side {
                border-left: 0;
                border-top: 1px dashed rgba(2, 8, 20, .16);
                padding-left: 0;
                padding-top: .7rem;
            }

            .room-meta-grid {
                width: 100%;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                justify-content: stretch;
            }

            .room-meta-grid .chip {
                white-space: normal;
            }

            .price-tag {
                text-align: left;
            }

            .rooms-scroll-dock::before {
                height: 96px;
            }

            .rooms-scroll-dock-inner {
                font-size: .8rem;
                margin-bottom: .5rem;
            }
        }

    </style>
</head>
<body>
<x-public-topnav />

<main class="shell">
    <div class="container">
        @php
            $propertyImage = ltrim((string) ($property->image_path ?? ''), '/');
            $propertyImageExists = $propertyImage !== '' && (
                \Illuminate\Support\Facades\Storage::disk('public')->exists($propertyImage)
                || file_exists(public_path('storage/' . $propertyImage))
            );
            $availablePropertyRooms = $rooms->filter(fn ($room) => $room->status === 'available' && $room->getAvailableSlots() > 0)->count();
            $minPropertyPrice = $rooms->count() > 0 ? (float) $rooms->min('price') : null;
            $maxPropertyPrice = $rooms->count() > 0 ? (float) $rooms->max('price') : null;
            $propertyPriceLabel = 'Price N/A';
            if ($minPropertyPrice !== null && $maxPropertyPrice !== null) {
                $propertyPriceLabel = $minPropertyPrice === $maxPropertyPrice
                    ? 'PHP ' . number_format($minPropertyPrice, 0)
                    : 'PHP ' . number_format($minPropertyPrice, 0) . '-' . number_format($maxPropertyPrice, 0);
            }
        @endphp

        <section class="hero">
            @if($propertyImageExists)
                <img class="hero-image is-landscape" src="{{ asset('storage/' . $propertyImage) }}" alt="{{ $property->name }}" loading="lazy">
            @endif
            <div class="hero-overlay"></div>

            <div class="hero-content">
                <div class="hero-top-row">
                    <a href="{{ route('public.properties.map') }}" class="btn btn-outline-light rounded-pill"><i class="bi bi-arrow-left me-1"></i>Back to Map</a>
                </div>

                <div class="hero-bottom-row">
                    <div class="hero-left">
                        <h1 class="property-name display-font">{{ $property->name }}</h1>
                        <p class="property-address"><i class="bi bi-geo-alt me-1"></i>{{ $property->address ?: 'Address not available' }}</p>
                        <button type="button" id="landlordDetailsBtn" class="property-landlord-btn" data-default-text="Landlord: {{ $property->landlord->full_name ?? 'Not available' }}" data-hover-text="View Details" data-bs-toggle="modal" data-bs-target="#landlordDetailsModal">
                            <i class="bi bi-person-circle"></i>
                            <span id="landlordDetailsBtnText">Landlord: {{ $property->landlord->full_name ?? 'Not available' }}</span>
                        </button>
                    </div>

                    <div class="hero-right">
                        <div class="hero-price-range">{{ $propertyPriceLabel }}</div>
                        <div class="hero-room-count">{{ $availablePropertyRooms }} room{{ $availablePropertyRooms === 1 ? '' : 's' }} available</div>
                    </div>
                </div>
            </div>
        </section>

        @if($rooms->isNotEmpty())
            <section class="room-list">
                @foreach($rooms as $room)
                    @php
                        $roomImage = ltrim((string) ($room->image_path ?? ''), '/');
                        $propertyImage = ltrim((string) ($property->image_path ?? ''), '/');

                        $roomImageExists = $roomImage !== '' && (
                            \Illuminate\Support\Facades\Storage::disk('public')->exists($roomImage)
                            || file_exists(public_path('storage/' . $roomImage))
                        );
                        $propertyImageExists = $propertyImage !== '' && (
                            \Illuminate\Support\Facades\Storage::disk('public')->exists($propertyImage)
                            || file_exists(public_path('storage/' . $propertyImage))
                        );
                        $displayImage = $roomImageExists ? $roomImage : ($propertyImageExists ? $propertyImage : null);

                        $availableSlots = $room->getAvailableSlots();
                        $isAvailable = $room->status === 'available' && $availableSlots > 0;
                        $isNewRoom = $room->updated_at && $room->updated_at->gte(now()->subDays(14));
                        $rating = $room->feedbacks_avg_rating !== null ? (float) $room->feedbacks_avg_rating : null;
                        $ratingCount = (int) ($room->feedbacks_count ?? 0);
                        $recentReviews = ($room->feedbacks ?? collect())->take(2);
                        $roomInclusions = collect(preg_split('/[\r\n,;]+/', (string) ($room->inclusions ?? '')))
                            ->map(fn ($item) => trim((string) $item))
                            ->filter()
                            ->unique()
                            ->take(6)
                            ->values();
                        $amenityMap = (array) config('property_amenities.flat', []);
                        $propertyInclusions = collect((array) ($property->building_inclusions ?? []))
                            ->map(fn ($key) => $amenityMap[$key] ?? trim((string) $key))
                            ->filter()
                            ->unique()
                            ->take(4)
                            ->values();
                    @endphp
                    <article class="room-card">
                        <div class="room-photo">
                            @if($displayImage)
                                <img src="{{ asset('storage/' . $displayImage) }}" alt="{{ $room->room_number }}" loading="lazy">
                            @else
                                <i class="bi bi-building fs-3"></i>
                            @endif
                        </div>

                        <div class="room-body">
                            <div class="room-main">
                                <div class="room-head">
                                    <div>
                                        <h2 class="room-title">{{ $room->room_number }}</h2>
                                        <div class="room-subtitle">{{ $property->name }}</div>
                                        <div class="room-submeta">
                                            <span
                                                class="js-room-distance"
                                                data-room-lat="{{ $property->latitude ?? '' }}"
                                                data-room-lng="{{ $property->longitude ?? '' }}"
                                                data-is-new="{{ $isNewRoom ? '1' : '0' }}">Allow location to show distance</span>
                                        </div>
                                    </div>
                                    <div class="room-meta-grid">
                                        <span class="chip"><i class="bi bi-door-open"></i>{{ $availableSlots }} slot{{ $availableSlots === 1 ? '' : 's' }} available</span>
                                        <span class="chip {{ $isAvailable ? 'chip-available' : 'chip-unavailable' }}"><i class="bi bi-circle-fill" style="font-size:.48rem;"></i>{{ $isAvailable ? 'Available' : ucfirst((string) $room->status) }}</span>
                                        <span class="chip"><i class="bi bi-people"></i>Capacity: {{ (int) $room->capacity }} pax</span>
                                        <span class="chip"><i class="bi bi-star-fill text-warning"></i>{{ $rating !== null ? number_format($rating, 1) : 'No rating' }} ({{ $ratingCount }})</span>
                                    </div>
                                </div>

                                @if($roomInclusions->isNotEmpty() || $propertyInclusions->isNotEmpty())
                                    <div>
                                        <div class="inclusion-title">Inclusions</div>
                                        <div class="chip-row">
                                            @foreach($roomInclusions as $inclusion)
                                                <span class="inclusion-chip"><i class="bi bi-check2-circle"></i>{{ $inclusion }}</span>
                                            @endforeach
                                            @foreach($propertyInclusions as $inclusion)
                                                <span class="inclusion-chip"><i class="bi bi-building-check"></i>{{ $inclusion }}</span>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                <div class="review-card">
                                    <div class="review-title">Reviews</div>
                                    @if($recentReviews->isNotEmpty())
                                        @foreach($recentReviews as $review)
                                            @php
                                                $reviewerName = trim((string) ($review->display_name ?: ($review->user->full_name ?? 'Anonymous')));
                                                $reviewComment = trim((string) ($review->comment ?? ''));
                                                $reviewRating = max(1, min(5, (int) round((float) ($review->rating ?? 0))));
                                            @endphp
                                            <div class="review-item">
                                                <div class="review-head">
                                                    <span>{{ $reviewerName !== '' ? $reviewerName : 'Anonymous' }}</span>
                                                    <span class="review-stars">
                                                        @for($star = 1; $star <= 5; $star++)
                                                            <i class="bi {{ $star <= $reviewRating ? 'bi-star-fill' : 'bi-star' }}"></i>
                                                        @endfor
                                                    </span>
                                                </div>
                                                <div>{{ $reviewComment !== '' ? \Illuminate\Support\Str::limit($reviewComment, 100) : 'No written comment.' }}</div>
                                            </div>
                                        @endforeach
                                    @else
                                        <div class="review-empty">No reviews yet for this room.</div>
                                    @endif
                                </div>
                            </div>

                            <aside class="room-side">
                                <div class="price-tag">
                                    <div class="price-label">Monthly Rent</div>
                                    <div class="price-value">₱{{ number_format((float) $room->price, 0) }}</div>
                                    <div class="price-period">per month</div>
                                </div>
                                <div class="room-action-row">
                                    <a href="{{ route('rooms.public.show', $room->id) }}" class="btn btn-brand btn-sm w-100">View Room</a>
                                </div>
                            </aside>
                        </div>
                    </article>
                @endforeach
            </section>
        @else
            <div class="empty-state">
                <i class="bi bi-search fs-3"></i>
                <div>No public rooms are available for this property right now.</div>
            </div>
        @endif
    </div>
</main>

@if($rooms->count() > 1)
    <div id="roomsScrollDock" class="rooms-scroll-dock" aria-hidden="false">
        <button id="roomsScrollDockBtn" type="button" class="rooms-scroll-dock-inner" aria-label="Scroll to see more rooms">
            <span>More rooms below</span>
            <i class="bi bi-chevron-double-down icon" aria-hidden="true"></i>
        </button>
    </div>
@endif

<div class="modal fade" id="landlordDetailsModal" tabindex="-1" aria-labelledby="landlordDetailsModalLabel" aria-hidden="true">
    @php
        $landlord = $property->landlord;
        $landlordProfile = $landlord?->landlordProfile;
        $landlordAddress = trim((string) ($landlord->address ?? ''));
        $landlordAbout = trim((string) ($landlordProfile->about ?? ''));
        $landlordImage = ltrim((string) ($landlord->profile_image_path ?? ''), '/');
        $landlordImageExists = $landlordImage !== '' && (
            \Illuminate\Support\Facades\Storage::disk('public')->exists($landlordImage)
            || file_exists(public_path('storage/' . $landlordImage))
        );
        $ownedProperties = ($landlord?->properties ?? collect())
            ->pluck('name')
            ->map(fn ($value) => trim((string) $value))
            ->filter()
            ->unique()
            ->values();
    @endphp

    <div class="modal-dialog modal-dialog-centered landlord-modal-dialog">
        <div class="modal-content landlord-modal-content">
            <div class="landlord-modal-layout">
                <div class="landlord-modal-media" aria-hidden="true">
                    @if($landlordImageExists)
                        <img src="{{ asset('storage/' . $landlordImage) }}" alt="{{ $landlord->full_name ?? 'Landlord' }}">
                    @else
                        <div class="landlord-modal-media-fallback"><i class="bi bi-person fs-1"></i></div>
                    @endif
                    <div class="landlord-modal-media-caption">
                        <div class="title">{{ $landlord->full_name ?? 'Landlord' }}</div>
                        <div class="sub">Landlord profile</div>
                    </div>
                </div>

                <div class="landlord-modal-panel">
                    <button type="button" class="landlord-modal-close" data-bs-dismiss="modal" aria-label="Close">&times;</button>

                    <div class="landlord-modal-heading">
                        <h5 class="modal-title mb-0" id="landlordDetailsModalLabel">Landlord Details</h5>
                    </div>

                    <div class="modal-soft-label">Name</div>
                    <div class="modal-soft-value">{{ $landlord->full_name ?? 'Not available' }}</div>

                    <div class="modal-soft-label">Address</div>
                    <div class="modal-soft-value">{{ $landlordAddress !== '' ? $landlordAddress : 'Not available' }}</div>

                    <div class="modal-soft-label">Properties Owned</div>
                    @if($ownedProperties->isNotEmpty())
                        <ul class="property-name-list modal-soft-value">
                            @foreach($ownedProperties as $propertyName)
                                <li>{{ $propertyName }}</li>
                            @endforeach
                        </ul>
                    @else
                        <div class="modal-soft-value">Not available</div>
                    @endif

                    <div class="modal-soft-label">About</div>
                    <div class="modal-soft-value mb-0">{{ $landlordAbout !== '' ? \Illuminate\Support\Str::limit($landlordAbout, 220) : 'No additional details provided yet.' }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const heroImage = document.querySelector('.hero-image');
        const hero = document.querySelector('.hero');
        const landlordButton = document.getElementById('landlordDetailsBtn');
        const landlordButtonText = document.getElementById('landlordDetailsBtnText');
        const landlordModal = document.getElementById('landlordDetailsModal');
        const roomsScrollDock = document.getElementById('roomsScrollDock');
        const roomsScrollDockBtn = document.getElementById('roomsScrollDockBtn');
        const roomDistanceElements = Array.from(document.querySelectorAll('.js-room-distance'));

        const toRadians = (degrees) => (degrees * Math.PI) / 180;

        const getDistanceInKm = (fromLat, fromLng, toLat, toLng) => {
            const earthRadiusKm = 6371;
            const latDelta = toRadians(toLat - fromLat);
            const lngDelta = toRadians(toLng - fromLng);

            const a =
                Math.sin(latDelta / 2) * Math.sin(latDelta / 2) +
                Math.cos(toRadians(fromLat)) * Math.cos(toRadians(toLat)) *
                Math.sin(lngDelta / 2) * Math.sin(lngDelta / 2);

            const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
            return earthRadiusKm * c;
        };

        const formatDistanceLabel = (distanceKm) => {
            if (!Number.isFinite(distanceKm)) {
                return 'Distance unavailable';
            }

            if (distanceKm < 1) {
                return `${Math.max(1, Math.round(distanceKm * 1000))} m away`;
            }

            if (distanceKm < 10) {
                return `${distanceKm.toFixed(1)} km away`;
            }

            return `${Math.round(distanceKm)} km away`;
        };

        const applyRoomDistanceText = (baseText, isNew) => {
            if (isNew) {
                return `${baseText}`;
            }

            return baseText;
        };

        const setDistanceFallback = (baseFallback) => {
            roomDistanceElements.forEach((element) => {
                const isNew = element.dataset.isNew === '1';
                element.textContent = applyRoomDistanceText(baseFallback, isNew);
            });
        };

        if (roomDistanceElements.length) {
            if (!navigator.geolocation) {
                setDistanceFallback('Distance unavailable');
            } else {
                navigator.geolocation.getCurrentPosition(
                    (position) => {
                        const userLat = Number(position.coords.latitude || 0);
                        const userLng = Number(position.coords.longitude || 0);

                        roomDistanceElements.forEach((element) => {
                            const roomLat = Number(element.dataset.roomLat || NaN);
                            const roomLng = Number(element.dataset.roomLng || NaN);
                            const isNew = element.dataset.isNew === '1';

                            if (!Number.isFinite(roomLat) || !Number.isFinite(roomLng)) {
                                element.textContent = applyRoomDistanceText('Distance unavailable', isNew);
                                return;
                            }

                            const distanceKm = getDistanceInKm(userLat, userLng, roomLat, roomLng);
                            element.textContent = applyRoomDistanceText(formatDistanceLabel(distanceKm), isNew);
                        });
                    },
                    () => {
                        setDistanceFallback('Location off');
                    },
                    {
                        enableHighAccuracy: true,
                        timeout: 12000,
                        maximumAge: 60000,
                    }
                );
            }
        }

        if (heroImage) {
            const applyHeroAnimation = () => {
                heroImage.classList.remove('is-portrait', 'is-landscape');

                const width = Number(heroImage.naturalWidth || 0);
                const height = Number(heroImage.naturalHeight || 0);
                if (!width || !height) {
                    heroImage.classList.add('is-landscape');
                    return;
                }

                if (height > width) {
                    heroImage.classList.add('is-portrait');
                } else {
                    heroImage.classList.add('is-landscape');
                }
            };

            if (heroImage.complete) {
                applyHeroAnimation();
            } else {
                heroImage.addEventListener('load', applyHeroAnimation, { once: true });
            }

            window.setTimeout(applyHeroAnimation, 240);
        }

        if (landlordButton && landlordButtonText) {
            const defaultText = landlordButton.dataset.defaultText || landlordButtonText.textContent || 'Landlord';
            const hoverText = landlordButton.dataset.hoverText || 'View Details';

            const showDefault = () => {
                landlordButtonText.textContent = defaultText;
            };

            const showHover = () => {
                landlordButtonText.textContent = hoverText;
            };

            if (hero) {
                hero.addEventListener('mouseenter', () => {
                    hero.classList.add('is-landlord-cta');
                    showHover();
                });

                hero.addEventListener('mouseleave', () => {
                    hero.classList.remove('is-landlord-cta');
                    showDefault();
                });

                hero.addEventListener('focusin', () => {
                    hero.classList.add('is-landlord-cta');
                });

                hero.addEventListener('focusout', () => {
                    hero.classList.remove('is-landlord-cta');
                    showDefault();
                });
            }

            if (landlordModal) {
                landlordModal.addEventListener('show.bs.modal', () => {
                    if (hero) {
                        hero.classList.remove('is-landlord-cta');
                    }
                    showDefault();
                });

                landlordModal.addEventListener('hidden.bs.modal', () => {
                    if (hero) {
                        hero.classList.remove('is-landlord-cta');
                    }
                    showDefault();
                });
            }
        }

        if (roomsScrollDock) {
            const updateDockVisibility = () => {
                const documentHeight = Math.max(
                    document.body.scrollHeight,
                    document.documentElement.scrollHeight
                );
                const viewportBottom = window.scrollY + window.innerHeight;
                const atEnd = viewportBottom >= documentHeight - 8;
                const hasOverflow = documentHeight > window.innerHeight + 24;

                if (!hasOverflow || atEnd) {
                    roomsScrollDock.classList.add('is-hidden');
                } else {
                    roomsScrollDock.classList.remove('is-hidden');
                }
            };

            updateDockVisibility();
            window.addEventListener('scroll', updateDockVisibility, { passive: true });
            window.addEventListener('resize', updateDockVisibility);

            if (roomsScrollDockBtn) {
                roomsScrollDockBtn.addEventListener('click', () => {
                    window.scrollBy({
                        top: Math.max(Math.round(window.innerHeight * 0.58), 440),
                        behavior: 'smooth',
                    });
                });
            }

            if (landlordModal) {
                landlordModal.addEventListener('show.bs.modal', () => {
                    roomsScrollDock.classList.add('is-hidden-modal');
                });

                landlordModal.addEventListener('hidden.bs.modal', () => {
                    roomsScrollDock.classList.remove('is-hidden-modal');
                    updateDockVisibility();
                });
            }
        }
    });
</script>
</body>
</html>

