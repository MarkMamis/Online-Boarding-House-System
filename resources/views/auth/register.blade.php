<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Online Boarding House System</title>
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
            --auth-control-height: 46px;
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
        .hero-top-actions {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: .5rem;
        }
        .hero-home-link {
            color: rgba(255,255,255,.88);
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            padding: .42rem .78rem;
            border-radius: 999px;
            background: linear-gradient(125deg, rgba(244,183,64,.2), rgba(244,183,64,.08));
            border: 1px solid rgba(244,183,64,.52);
            color: #fef3c7;
            line-height: 1;
        }
        .hero-home-link:hover { color: #fff8e8; text-decoration: none; transform: translateY(-1px); }
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
            gap: .85rem;
            margin-top: .8rem;
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
            gap: .5rem;
            flex: 0 0 auto;
        }
        .hero-brand-logos img {
            width: 52px;
            height: 52px;
            object-fit: contain;
            filter: drop-shadow(0 6px 12px rgba(2,8,20,.26));
        }
        .hero-brand-logos img:first-child {
            width: 68px;
            height: 68px;
        }
        .hero-brand-copy {
            display: flex;
            flex-direction: column;
            gap: .12rem;
            min-width: 0;
            line-height: 1.06;
        }
        .hero-brand-top {
            font-size: 1.15rem;
            font-weight: 800;
            color: #ffffff;
            letter-spacing: .01em;
        }
        .hero-brand-bottom {
            font-size: .92rem;
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
            box-shadow: 0 28px 58px rgba(2,8,20,.28);
            overflow: hidden;
            backdrop-filter: blur(12px) saturate(1.10);
            -webkit-backdrop-filter: blur(12px) saturate(1.10);
            color: #fff;
        }

        .auth-card {
            border-radius: 1.25rem;
            border-color: rgba(167,243,208,.26);
        }

        .card .text-muted { color: rgba(255,255,255,.74) !important; }
        .form-label { font-weight: 600; color: rgba(255,255,255,.88); }
        .required-mark {
            color: #f87171;
            margin-left: .2rem;
        }
        .field-icon { background: rgba(255,255,255,.12); color: rgba(255,255,255,.88); }
        .input-group-text.field-icon { border-color: rgba(255,255,255,.18); }
        .form-control, .form-select {
            background: rgba(255,255,255,.10);
            border-color: rgba(255,255,255,.22);
            color: #fff;
            min-height: var(--auth-control-height);
            transition: all .18s ease;
        }
        .input-group > .form-control,
        .input-group > .form-select,
        .input-group > .input-group-text.field-icon,
        .input-group > .custom-select .custom-select-trigger {
            min-height: var(--auth-control-height);
            height: var(--auth-control-height);
        }
        .native-select-hidden {
            position: absolute !important;
            width: 1px !important;
            height: 1px !important;
            padding: 0 !important;
            margin: -1px !important;
            overflow: hidden !important;
            clip: rect(0, 0, 0, 0) !important;
            white-space: nowrap !important;
            border: 0 !important;
            pointer-events: none !important;
            opacity: 0 !important;
        }
        .custom-select {
            position: relative;
            width: 100%;
        }
        .input-group > .custom-select {
            flex: 1 1 auto;
            width: 1%;
            min-width: 0;
        }
        .custom-select-trigger {
            width: 100%;
            min-height: var(--auth-control-height);
            border: 1px solid rgba(255,255,255,.22);
            border-radius: .375rem;
            background: rgba(255,255,255,.10);
            color: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: space-between;
            gap: .5rem;
            text-align: left;
            padding: .42rem .75rem;
            line-height: 1.2;
            transition: border-color .18s ease, box-shadow .18s ease, background .18s ease;
        }
        .input-group .custom-select-trigger {
            border-top-left-radius: 0;
            border-bottom-left-radius: 0;
            border-left: 0;
        }
        .custom-select-trigger i {
            font-size: .85rem;
            transition: transform .18s ease;
            color: rgba(209,250,229,.9);
            flex: 0 0 auto;
        }
        .custom-select.is-open .custom-select-trigger {
            border-color: rgba(16,185,129,.88);
            box-shadow: 0 0 0 .25rem rgba(16,185,129,.2);
            background: rgba(255,255,255,.14);
        }
        .custom-select.is-open .custom-select-trigger i {
            transform: rotate(180deg);
        }
        .custom-select.is-disabled .custom-select-trigger {
            opacity: .55;
            cursor: not-allowed;
        }
        .custom-select-menu {
            position: absolute;
            left: 0;
            right: 0;
            top: calc(100% + 8px);
            border: 1px solid rgba(167,243,208,.35);
            border-radius: .75rem;
            background: linear-gradient(155deg, rgba(10,18,32,.97), rgba(18,32,46,.96));
            box-shadow: 0 18px 34px rgba(2,8,20,.42);
            padding: .3rem;
            max-height: 260px;
            overflow-y: auto;
            z-index: 1200;
            display: none;
        }
        .custom-select.is-open .custom-select-menu {
            display: block;
        }
        .custom-select-option {
            width: 100%;
            text-align: left;
            border: 0;
            border-radius: .55rem;
            background: transparent;
            color: rgba(236,253,245,.92);
            padding: .46rem .55rem;
            font-size: .9rem;
            transition: background .16s ease, color .16s ease, transform .16s ease;
        }
        .custom-select-option:hover {
            background: rgba(16,185,129,.2);
            color: #fff;
            transform: translateX(1px);
        }
        .custom-select-option.is-active {
            background: linear-gradient(145deg, rgba(16,185,129,.32), rgba(4,120,87,.34));
            color: #ecfdf5;
            box-shadow: inset 0 0 0 1px rgba(167,243,208,.35);
        }
        /* Make native select dropdown options readable */
        .form-select option {
            color: #0f172a;
            background: #fff;
        }
        .form-control::placeholder { color: rgba(255,255,255,.55); }
        .form-text { color: rgba(255,255,255,.65); }
        .form-check-label { color: rgba(255,255,255,.82); }
        .card a { color: #d1fae5; }
        .card a:hover { color: #ecfdf5; }

        .card .card-header { background: transparent; border-bottom: 0; }
        .form-control:focus, .form-select:focus { 
            border-color: rgba(16,185,129,.88);
            box-shadow: 0 0 0 .25rem rgba(16,185,129,.2);
            background: rgba(255,255,255,.14);
        }
        .field-icon {
            width: 50px;
            min-width: 50px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            line-height: 1;
            border-radius: .5rem;
        }
        .input-group > .input-group-text.field-icon:first-child {
            border-top-right-radius: 0;
            border-bottom-right-radius: 0;
        }
        .input-group > .input-group-text.field-icon:last-child {
            border-top-left-radius: 0;
            border-bottom-left-radius: 0;
        }
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
            padding: .65rem 1.5rem;
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

        /* Form sections styling */
        .form-section-label {
            font-size: .75rem;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: rgba(255,255,255,.55);
            font-weight: 700;
            display: block;
            margin-bottom: .6rem;
            margin-top: .75rem;
            padding-bottom: .45rem;
            border-bottom: 1px solid rgba(167,243,208,.22);
        }
        .form-section-label:first-child { margin-top: 0; }
        .role-option {
            border: 1px solid rgba(255,255,255,.22) !important;
            border-radius: .9rem !important;
            background: rgba(255,255,255,.08);
            transition: border-color .2s ease, transform .2s ease, box-shadow .2s ease, background .2s ease;
        }
        .role-option:hover {
            transform: translateY(-1px);
            border-color: rgba(167,243,208,.52) !important;
            box-shadow: 0 8px 18px rgba(2,8,20,.18);
        }
        .role-option .form-check-input {
            margin-top: .2rem;
        }
        .role-option .form-check-input:checked {
            background-color: var(--premium-emerald);
            border-color: var(--premium-emerald);
            box-shadow: 0 0 0 .2rem rgba(16,185,129,.2);
        }
        .role-option-label {
            color: #ecfdf5;
        }
        .role-option-sub {
            color: rgba(209,250,229,.78);
        }
        .register-role-gate {
            border: 1px solid rgba(255,255,255,.18);
            border-radius: 1rem;
            background: rgba(255,255,255,.08);
            padding: .95rem;
        }
        .register-mobile-cta-stage {
            border: 1px solid rgba(255,255,255,.18);
            border-radius: 1rem;
            background: rgba(255,255,255,.08);
            padding: 1rem;
        }
        .register-mobile-cta-stage .cta-title {
            margin-bottom: .35rem;
        }
        .register-mobile-cta-stage .cta-sub {
            color: rgba(209,250,229,.8);
            margin-bottom: .9rem;
        }
        .mobile-cta-back-btn {
            border-radius: 999px;
            border: 1px solid rgba(255,255,255,.3);
            color: #ecfdf5;
            background: rgba(255,255,255,.08);
            font-weight: 700;
            padding: .55rem .95rem;
        }
        .mobile-cta-back-btn:hover {
            color: #fff;
            background: rgba(255,255,255,.14);
            border-color: rgba(255,255,255,.4);
        }
        .mobile-role-heading {
            display: none;
            text-align: center;
            margin-bottom: .85rem;
        }
        .mobile-role-heading .mobile-role-sub {
            color: rgba(209,250,229,.8);
            margin-bottom: 0;
        }
        .role-gate-title {
            display: block;
            font-size: .78rem;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: rgba(236,253,245,.78);
            font-weight: 800;
            margin-bottom: .6rem;
        }
        .role-gate-option {
            min-height: 124px;
        }
        .role-gate-option strong {
            font-size: 1rem;
        }
        .role-gate-btn {
            position: relative;
            overflow: hidden;
            isolation: isolate;
            width: 100%;
            min-height: 124px;
            border: 1px solid rgba(255,255,255,.22);
            border-radius: .9rem;
            background: rgba(255,255,255,.08);
            color: #ecfdf5;
            text-align: left;
            padding: .88rem .95rem;
            transition: border-color .2s ease, transform .2s ease, box-shadow .2s ease, background .2s ease;
        }
        .role-gate-btn::after {
            content: "";
            position: absolute;
            top: -120%;
            bottom: -120%;
            left: -42%;
            width: 34%;
            background: linear-gradient(120deg, transparent 10%, rgba(255,255,255,.36) 50%, transparent 90%);
            transform: translateX(-220%) rotate(16deg);
            transition: transform .55s ease;
            pointer-events: none;
            z-index: 0;
        }
        .role-gate-btn > * {
            position: relative;
            z-index: 1;
        }
        .role-gate-btn:hover {
            transform: translateY(-1px);
            border-color: rgba(167,243,208,.52);
            background: linear-gradient(145deg, rgba(16,185,129,.14), rgba(244,183,64,.12));
            box-shadow: 0 8px 18px rgba(2,8,20,.18);
        }
        .role-gate-btn:hover::after {
            transform: translateX(420%) rotate(16deg);
        }
        .role-gate-btn.is-selected {
            border-color: rgba(16,185,129,.88);
            background: linear-gradient(145deg, rgba(16,185,129,.22), rgba(4,120,87,.18));
            box-shadow: 0 0 0 2px rgba(16,185,129,.2), 0 10px 18px rgba(2,8,20,.22);
        }
        .role-gate-btn:focus-visible {
            outline: 2px solid rgba(236,253,245,.78);
            outline-offset: 2px;
        }
        .role-gate-btn strong {
            font-size: 1rem;
        }
        .role-gate-btn .role-option-sub {
            margin-top: .48rem;
            display: block;
        }
        .continue-role-btn:disabled {
            opacity: .6;
            cursor: not-allowed;
        }
        .register-form-stage {
            opacity: 0;
            transform: translateY(8px);
            transition: opacity .24s ease, transform .24s ease;
        }
        .register-form-stage.is-active {
            opacity: 1;
            transform: none;
        }
        .change-role-btn {
            border-radius: 999px;
            border: 1px solid rgba(255,255,255,.24);
            color: #ecfdf5;
            background: rgba(255,255,255,.08);
            font-size: .8rem;
            font-weight: 700;
            padding: .35rem .72rem;
            transition: transform .2s ease, background .2s ease;
        }
        .change-role-btn:hover {
            color: #fff;
            background: rgba(255,255,255,.14);
            transform: translateY(-1px);
        }
        .academic-meta {
            margin-top: .38rem;
            font-size: .74rem;
            color: rgba(209,250,229,.82);
        }

        /* Gender radio buttons styling */
        .gender-options {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: .6rem;
            margin-bottom: .5rem;
        }
        .gender-option {
            min-width: 0;
        }
        .gender-option input[type="radio"] {
            display: none;
        }
        .gender-option label {
            display: block;
            padding: .5rem .75rem;
            border: 1px solid rgba(255,255,255,.22);
            border-radius: .5rem;
            background: rgba(255,255,255,.08);
            cursor: pointer;
            transition: all .18s ease;
            color: rgba(255,255,255,.88);
            margin: 0;
            text-align: center;
            font-weight: 500;
            white-space: nowrap;
            font-size: .9rem;
        }
        .gender-option input[type="radio"]:checked + label {
            border-color: var(--brand);
            background: rgba(var(--brand-rgb), .20);
            color: #fff;
            box-shadow: 0 0 0 2px rgba(var(--brand-rgb), .30);
        }
        .gender-option label:hover {
            border-color: var(--brand);
            background: rgba(var(--brand-rgb), .12);
        }
        .gender-custom-field {
            display: none;
            margin-top: .5rem;
        }
        .gender-custom-field.show {
            display: block;
        }
        .gender-custom-field .form-control {
            padding: .55rem .75rem;
            font-size: .9rem;
        }

        /* Business permit uploader */
        .permit-upload {
            border: 1px solid rgba(255,255,255,.22);
            border-radius: .75rem;
            background: rgba(255,255,255,.07);
            padding: .75rem;
            transition: border-color .18s ease, box-shadow .18s ease, background .18s ease;
        }
        .permit-upload:focus-within {
            border-color: rgba(16,185,129,.72);
            box-shadow: 0 0 0 .25rem rgba(16,185,129,.16);
            background: rgba(255,255,255,.10);
        }
        .permit-upload-top {
            display: flex;
            align-items: center;
            gap: .65rem;
            flex-wrap: wrap;
        }
        .permit-upload-btn {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            border: 1px solid rgba(16,185,129,.55);
            color: #fff;
            background: rgba(16,185,129,.24);
            border-radius: .6rem;
            padding: .45rem .75rem;
            font-weight: 600;
            cursor: pointer;
            transition: transform .18s ease, background .18s ease;
        }
        .permit-upload-btn:hover {
            background: rgba(16,185,129,.34);
            transform: translateY(-1px);
        }
        .permit-upload-filename {
            min-width: 0;
            font-size: .93rem;
            color: rgba(255,255,255,.85);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            flex: 1 1 240px;
        }
        .permit-upload-help {
            margin-top: .45rem;
            color: rgba(255,255,255,.65);
            font-size: .85rem;
        }
        .permit-upload input[type="file"] {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            white-space: nowrap;
            border: 0;
        }

        .terms-trigger-btn {
            color: rgba(236,253,245,.95) !important;
            font-weight: 700;
            text-decoration-color: rgba(244,183,64,.85);
            text-underline-offset: 3px;
            transition: color .2s ease, transform .2s ease;
        }
        .terms-trigger-btn:hover {
            color: #fff !important;
            transform: translateY(-1px);
        }
        .terms-modal .modal-dialog {
            max-width: 760px;
        }
        .terms-modal .modal-content {
            border: 1px solid rgba(15,23,42,.12);
            border-radius: 1rem;
            overflow: hidden;
            background: linear-gradient(160deg, #ffffff, #f8fafc);
            box-shadow: 0 26px 58px rgba(2,8,20,.28);
        }
        .terms-modal .modal-header {
            border-bottom: 1px solid rgba(15,23,42,.08);
            background: linear-gradient(145deg, rgba(16,185,129,.12), rgba(244,183,64,.14));
            padding: 1rem 1.1rem;
        }
        .terms-modal .modal-title-wrap {
            display: flex;
            align-items: flex-start;
            gap: .62rem;
        }
        .terms-modal .modal-title-icon {
            width: 34px;
            height: 34px;
            border-radius: .75rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #065f46;
            background: rgba(236,253,245,.94);
            border: 1px solid rgba(16,185,129,.28);
            box-shadow: 0 6px 14px rgba(2,8,20,.14);
            flex: 0 0 auto;
        }
        .terms-modal .modal-title {
            color: #0f172a;
            font-weight: 800;
            letter-spacing: .01em;
        }
        .terms-modal .modal-subtitle {
            margin-top: .2rem;
            font-size: .79rem;
            color: #475569;
        }
        .terms-modal .modal-body {
            padding: 1.05rem 1.1rem 1.2rem;
            color: #1e293b;
        }
        .terms-lead {
            font-size: .93rem;
            line-height: 1.58;
            color: #334155;
            margin-bottom: .76rem;
        }
        .terms-principles {
            list-style: none;
            margin: 0 0 .82rem;
            padding: 0;
            display: grid;
            gap: .48rem;
        }
        .terms-principles li {
            border: 1px solid rgba(15,23,42,.08);
            border-radius: .72rem;
            background: #fff;
            padding: .58rem .7rem;
            line-height: 1.48;
            font-size: .89rem;
            color: #1f2937;
        }
        .terms-principles li strong {
            color: #065f46;
        }
        .terms-note-card {
            border: 1px solid rgba(16,185,129,.22);
            border-radius: .72rem;
            background: linear-gradient(145deg, #f0fdf4, #ecfeff);
            padding: .66rem .72rem;
            margin-bottom: .56rem;
            line-height: 1.55;
            font-size: .87rem;
            color: #334155;
        }
        .terms-note-card:last-child {
            margin-bottom: 0;
        }
        .terms-modal .modal-footer {
            border-top: 1px solid rgba(15,23,42,.08);
            background: rgba(248,250,252,.92);
            padding: .78rem 1.1rem 1rem;
        }
        .terms-close-btn {
            border-radius: 999px;
            border-color: rgba(15,23,42,.16);
            color: #334155;
            font-weight: 700;
            padding: .42rem .95rem;
            transition: transform .2s ease, box-shadow .2s ease;
        }
        .terms-close-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 8px 16px rgba(15,23,42,.12);
        }

        @media (min-width: 768px) {
            .gender-options { grid-template-columns: repeat(4, minmax(0, 1fr)); }
        }

        @media (min-width: 992px) {
            .hero-pane { min-height: 100vh; }
        }
        @media (max-width: 991.98px) {
            .hero-content {
                max-width: none;
            }
        }
        @media (max-width: 575.98px) {
            .hero-top-actions {
                display: none;
            }
            .hero-pane {
                min-height: 0;
            }
            .hero-content {
                padding: 1rem .95rem !important;
            }
            .auth-wrapper.mobile-hide-hero-cta .hero-mobile-cta {
                display: none !important;
            }
            .auth-wrapper.mobile-hide-hero-cta .hero-content {
                padding-bottom: .3rem !important;
            }
            .hero-brand-block {
                gap: .6rem;
                margin-top: 0;
                margin-bottom: .72rem;
            }
            .hero-brand-logos img {
                width: 42px;
                height: 42px;
            }
            .hero-brand-logos img:first-child {
                width: 54px;
                height: 54px;
            }
            .hero-brand-top {
                font-size: 1rem;
            }
            .hero-brand-bottom {
                font-size: .8rem;
            }
            .hero-content h1 {
                font-size: clamp(1.9rem, 9.8vw, 2.35rem);
                margin-bottom: .52rem;
            }
            .hero-content p {
                font-size: .97rem;
                margin-bottom: .65rem;
            }
            .hero-list {
                margin-top: .52rem;
            }
            .hero-list li {
                gap: .56rem;
                margin-bottom: .42rem;
            }
            .hero-list .hero-ic {
                width: 30px;
                height: 30px;
                border-radius: 10px;
            }
            .hero-stat-chip {
                font-size: .62rem;
                padding: .24rem .36rem;
                gap: .2rem;
                white-space: nowrap;
            }
            .hero-stats {
                margin-top: .65rem;
                gap: .24rem;
                flex-wrap: nowrap;
            }
            .hero-stat-chip i {
                display: none;
            }
            .col-lg-6.d-flex.align-items-center.justify-content-center.p-4.p-lg-5 {
                align-items: flex-start !important;
                padding-top: .4rem !important;
                padding-left: .95rem !important;
                padding-right: .95rem !important;
                padding-bottom: .95rem !important;
            }
            .auth-card .card-body {
                padding: 1rem .9rem !important;
            }
            .register-card-heading {
                display: none;
            }
            .register-role-gate {
                padding: .75rem;
                border-radius: .85rem;
            }
            .register-mobile-cta-stage {
                padding: .8rem;
                border-radius: .85rem;
            }
            .register-mobile-cta-stage .cta-title {
                font-size: 1.55rem;
            }
            .register-mobile-cta-stage .cta-sub {
                font-size: .92rem;
                margin-bottom: .75rem;
            }
            .mobile-role-heading {
                display: block;
            }
            .register-form-stage .d-flex.justify-content-end.mb-2 {
                margin-bottom: .45rem !important;
            }
            .change-role-btn {
                font-size: .76rem;
                padding: .3rem .62rem;
            }
        }
    </style>
    <noscript>
        <style>
            .auth-wrapper:before { background-image: url("{{ asset('images/MinSU-Calapan.jpg') }}"); }
        </style>
    </noscript>
    <!-- If the image is missing, show a subtle gradient background on the left -->
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
                        <div class="hero-top-actions">
                            <a href="{{ route('landing') }}" class="hero-home-link" aria-label="Go to home">
                                <i class="bi bi-arrow-left"></i> Home
                            </a>
                        </div>
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
                        <h1 class="display-font display-5 fw-bold hero-mobile-cta">Find your new home away from home.</h1>
                        <p class="lead opacity-75 hero-mobile-cta">Choose your role and start booking, onboarding, or property management in one secure platform.</p>

                        <ul class="list-unstyled hero-list mb-0 opacity-90 hero-mobile-cta">
                            <li>
                                <span class="hero-ic"><i class="bi bi-person-check"></i></span>
                                <div>
                                    <div class="fw-semibold">Student & Landlord ready</div>
                                    <div class="small opacity-75">Choose your role and get the right tools.</div>
                                </div>
                            </li>
                            <li>
                                <span class="hero-ic"><i class="bi bi-journal-check"></i></span>
                                <div>
                                    <div class="fw-semibold">Simple booking requests</div>
                                    <div class="small opacity-75">Send requests and track updates easily.</div>
                                </div>
                            </li>
                            <li>
                                <span class="hero-ic"><i class="bi bi-geo-alt"></i></span>
                                <div>
                                    <div class="fw-semibold">Location‑aware browsing</div>
                                    <div class="small opacity-75">Find places near campus with map support.</div>
                                </div>
                            </li>
                        </ul>
                        <div class="hero-stats hero-mobile-cta">
                            <span class="hero-stat-chip"><i class="bi bi-patch-check"></i> Verified access</span>
                            <span class="hero-stat-chip"><i class="bi bi-lightning-charge"></i> Fast onboarding</span>
                            <span class="hero-stat-chip"><i class="bi bi-geo-alt"></i> Campus-focused</span>
                        </div>
                    </div>
                    <div class="opacity-75 small d-none d-lg-block">Online Boarding House System</div>
                </div>
            </div>

            <!-- Right form pane -->
            <div class="col-lg-6 d-flex align-items-center justify-content-center p-4 p-lg-5">
                <div class="w-100" style="max-width: 740px;">
                    <div class="card auth-card">
                        <div class="card-body p-4 p-lg-5">
                            <div class="mb-4 text-center register-card-heading">
                                <h2 class="display-font fw-bold mb-1">Create your account</h2>
                                <p class="text-muted mb-0">Already have one? <a href="{{ route('login') }}" class="text-white text-decoration-none fw-semibold mb-0">Sign in</a></p>
                            </div>

                            @if($errors->any())
                                <div class="alert alert-danger">
                                    <ul class="mb-0">
                                        @foreach($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <form method="POST" action="{{ route('register') }}" id="registerForm" novalidate>
                                @csrf

                                <div class="row g-2">
                                    <div class="col-12">
                                        <span class="form-section-label"><i class="bi bi-person-fill"></i> Account Information</span>
                                    </div>

                                    <div class="col-12">
                                        <label for="full_name" class="form-label">Full Name<span class="required-mark">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text field-icon"><i class="bi bi-person"></i></span>
                                            <input type="text" class="form-control" id="full_name" name="full_name" value="{{ old('full_name') }}" placeholder="First Name Middle Name Last Name" required>
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <label for="email" class="form-label">Email<span class="required-mark">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text field-icon"><i class="bi bi-envelope"></i></span>
                                            <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" placeholder="you@example.com" required>
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <span class="form-section-label"><i class="bi bi-shield-lock"></i> Security</span>
                                    </div>

                                    <div class="col-12 col-md-6">
                                        <label for="password" class="form-label">Password<span class="required-mark">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text field-icon"><i class="bi bi-lock"></i></span>
                                            <input type="password" class="form-control" id="password" name="password" minlength="8" required>
                                            <span class="input-group-text field-icon" id="toggle_password" role="button" tabindex="0" aria-label="Show password" aria-controls="password">
                                                <i class="bi bi-eye" aria-hidden="true"></i>
                                            </span>
                                        </div>
                                        <div class="form-text">Use at least 8 characters.</div>
                                    </div>

                                    <div class="col-12 col-md-6">
                                        <label for="password_confirmation" class="form-label">Confirm Password<span class="required-mark">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text field-icon"><i class="bi bi-shield-lock"></i></span>
                                            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" minlength="8" required>
                                            <span class="input-group-text field-icon" id="toggle_password_confirmation" role="button" tabindex="0" aria-label="Show password" aria-controls="password_confirmation">
                                                <i class="bi bi-eye" aria-hidden="true"></i>
                                            </span>
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="terms_accepted" name="terms_accepted" value="1" {{ old('terms_accepted') ? 'checked' : '' }} required>
                                            <label class="form-check-label" for="terms_accepted">
                                                I have read and agree to the Terms and Data Privacy Notice.<span class="required-mark">*</span>
                                            </label>
                                            <button type="button" class="btn btn-link btn-sm p-0 ms-1 align-baseline text-decoration-underline terms-trigger-btn" data-bs-toggle="modal" data-bs-target="#termsPrivacyModal">
                                                View terms
                                            </button>
                                        </div>
                                        @error('terms_accepted')
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-12 mt-2">
                                        <button type="submit" class="btn btn-brand btn-lg w-100">Create account</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade terms-modal" id="termsPrivacyModal" tabindex="-1" aria-labelledby="termsPrivacyModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <div class="modal-title-wrap">
                        <span class="modal-title-icon" aria-hidden="true"><i class="bi bi-shield-lock"></i></span>
                        <div>
                            <h5 class="modal-title display-font mb-0" id="termsPrivacyModalLabel">Terms and Data Privacy Notice</h5>
                            <div class="modal-subtitle">Republic Act No. 10173 (Data Privacy Act of 2012)</div>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="terms-lead">Under Republic Act No. 10173 (Data Privacy Act of 2012), we process your personal data using these principles:</p>
                    <ul class="terms-principles">
                        <li><strong>Transparency:</strong> You are informed about what data we collect and why.</li>
                        <li><strong>Legitimate Purpose:</strong> Data is used only for account creation, booking operations, communication, and security.</li>
                        <li><strong>Proportionality:</strong> We only collect data necessary for system services.</li>
                    </ul>
                    <div class="terms-note-card">By registering, you allow the system to collect and process your information (e.g., name, email, credentials, and the onboarding data you provide later) to provide platform functions and comply with legal obligations.</div>
                    <div class="terms-note-card">You may request access, correction, or deletion of your data, subject to legal and operational requirements, and we apply reasonable organizational, physical, and technical safeguards to protect your information.</div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary terms-close-btn" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Password visibility toggle
        (function () {
            const passwords = [
                { input: 'password', toggle: 'toggle_password' },
                { input: 'password_confirmation', toggle: 'toggle_password_confirmation' }
            ];

            passwords.forEach(config => {
                const passwordInput = document.getElementById(config.input);
                const toggle = document.getElementById(config.toggle);
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
            });
        })();

        // Registration submit guard
        (function () {
            const registerForm = document.getElementById('registerForm');
            if (!registerForm) return;

            registerForm.addEventListener('submit', () => {
                const submitButton = registerForm.querySelector('button[type="submit"]');
                if (!submitButton) return;

                submitButton.disabled = true;
                submitButton.textContent = 'Creating account...';
            });
        })();
    </script>
</body>
</html>
