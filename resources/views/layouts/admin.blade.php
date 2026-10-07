<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin Panel - Boarding House System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @stack('styles')
    <style>
        /* ==========================================================================
           OBHS Admin Unified Design System Tokens & Base
           ========================================================================== */
        :root {
            /* Surfaces & Backgrounds */
            --admin-bg: #f8fafc;
            --admin-surface: #ffffff;
            --admin-surface-subtle: #f8fafc;
            --admin-surface-hover: rgba(241, 245, 249, 0.7);
            --admin-surface-tint: rgba(22, 101, 52, 0.04);

            /* Typography & Neutrals */
            --admin-text: #0f172a;
            --admin-text-secondary: #475569;
            --admin-text-muted: #64748b;
            --admin-text-faint: #94a3b8;

            /* Borders */
            --admin-border: rgba(15, 23, 42, 0.08);
            --admin-border-subtle: rgba(15, 23, 42, 0.04);
            --admin-border-strong: rgba(15, 23, 42, 0.15);
            --admin-border-focus: rgba(22, 101, 52, 0.35);

            /* Brand (MinSU Green) */
            --admin-primary: #166534;
            --admin-primary-dark: #14532d;
            --admin-primary-hover: #15803d;
            --admin-primary-soft: rgba(22, 101, 52, 0.08);
            --admin-primary-border: rgba(22, 101, 52, 0.18);

            /* Semantics */
            --admin-success: #16a34a;
            --admin-success-soft: rgba(22, 163, 74, 0.08);
            --admin-success-border: rgba(22, 163, 74, 0.2);

            --admin-warning: #d97706;
            --admin-warning-soft: #fffbeb;
            --admin-warning-border: #fef3c7;

            --admin-danger: #dc2626;
            --admin-danger-soft: #fef2f2;
            --admin-danger-border: #fee2e2;

            --admin-info: #0284c7;
            --admin-info-soft: rgba(2, 132, 199, 0.08);
            --admin-info-border: rgba(2, 132, 199, 0.2);

            /* Radii */
            --admin-radius-sm: 8px;
            --admin-radius-md: 12px;
            --admin-radius-lg: 16px;
            --admin-radius-xl: 20px;

            /* Shadows */
            --admin-shadow-xs: 0 1px 2px rgba(15, 23, 42, 0.04);
            --admin-shadow-sm: 0 2px 6px rgba(15, 23, 42, 0.04);
            --admin-shadow-md: 0 4px 16px rgba(15, 23, 42, 0.04);

            /* Layout Measurements */
            --nav-h: 68px;
            --sidebar-w: 286px;
            --brand: var(--admin-primary);
            --brand-dark: var(--admin-primary-dark);
            --line: var(--admin-border);
            --surface: var(--admin-surface);
            --shell: var(--admin-bg);
            --muted: var(--admin-text-muted);
        }

        body {
            font-family: 'Manrope', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            min-height: 100vh;
            overflow-x: hidden;
            background: var(--admin-bg);
            color: var(--admin-text);
            -webkit-font-smoothing: antialiased;
        }

        /* ── Topbar / Navbar ── */
        .navbar-glass {
            background: var(--admin-surface) !important;
            border-bottom: 1px solid var(--admin-border);
            z-index: 1040;
            box-shadow: var(--admin-shadow-xs);
        }
        .dash-shell { padding-top: 5rem; padding-bottom: 3rem; }

        .navbar-brand {
            color: var(--admin-text) !important;
            letter-spacing: -0.01em;
            font-weight: 800;
            font-size: 1.15rem;
        }
        .brand-mark {
            width: 34px;
            height: 34px;
            object-fit: contain;
            display: inline-block;
        }
        .top-meta {
            color: var(--admin-text-muted);
            font-size: .8rem;
            line-height: 1.35;
        }
        .user-chip {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            border-radius: 999px;
            padding: .35rem .75rem;
            background: var(--admin-surface-subtle);
            border: 1px solid var(--admin-border);
            color: var(--admin-text-secondary);
            font-size: .85rem;
        }
        .icon-btn {
            width: 36px;
            height: 36px;
            border-radius: 999px;
            border: 1px solid var(--admin-border);
            background: var(--admin-surface);
            color: var(--admin-text-secondary);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            transition: all 0.15s ease;
        }
        .icon-btn:hover {
            background: var(--admin-primary-soft);
            color: var(--admin-primary);
            border-color: var(--admin-primary-border);
        }
        .icon-btn.active {
            background: var(--admin-primary-soft);
            color: var(--admin-primary);
            border-color: var(--admin-primary-border);
        }
        .icon-btn-wrap {
            position: relative;
            display: inline-flex;
        }
        .icon-btn-badge {
            position: absolute;
            top: -5px;
            right: -5px;
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
            background: var(--admin-danger);
            border: 2px solid #fff;
            line-height: 1;
        }

        /* ── Sidebar ── */
        .sidepanel {
            background: var(--admin-surface);
            border-right: 1px solid var(--admin-border);
            border-radius: 0;
            display: flex;
            flex-direction: column;
            min-height: 100%;
        }
        .sidebar-head {
            border: 1px solid var(--admin-border-subtle);
            background: var(--admin-surface-subtle);
            border-radius: var(--admin-radius-md);
        }
        .sidepanel .nav-section {
            padding: .9rem .85rem .35rem;
            font-size: .68rem;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
            color: var(--admin-text-faint);
        }
        .sidepanel .list-group-item {
            background: transparent;
            border: 1px solid transparent;
            color: var(--admin-text-secondary);
            display: flex;
            align-items: center;
            gap: .65rem;
            padding: .65rem .85rem;
            width: 100%;
            min-width: 0;
            text-align: left;
            border-radius: var(--admin-radius-md);
            font-size: .88rem;
            font-weight: 600;
            text-decoration: none;
            transition: all .15s ease;
        }
        .sidepanel .list-group-item > span:not(.badge) {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            min-width: 0;
        }
        .sidepanel .list-group-item > .badge {
            flex-shrink: 0;
            font-size: .7rem;
            padding: .25rem .5rem;
        }
        .sidepanel .list-group-item:hover {
            background: var(--admin-primary-soft);
            color: var(--admin-primary-dark);
            border-color: var(--admin-primary-border);
        }
        .sidepanel .list-group-item i {
            color: var(--admin-text-muted);
            font-size: 1rem;
            flex-shrink: 0;
            transition: color .15s ease;
        }
        .sidepanel .list-group-item:hover i {
            color: var(--admin-primary);
        }
        .sidepanel .list-group-item.active {
            background: var(--admin-primary-soft);
            border-color: var(--admin-primary-border);
            color: var(--admin-primary);
            font-weight: 700;
        }
        .sidepanel .list-group-item.active i {
            color: var(--admin-primary);
        }
        .sidepanel .list-group-item.sub-item {
            padding: .55rem .85rem .55rem 2.2rem;
            font-size: .84rem;
        }
        .sidepanel .nav-toggle { cursor: pointer; }
        .sidepanel .nav-chevron { margin-left: auto; transition: transform .18s ease; opacity: .75; }
        .sidepanel .nav-toggle[aria-expanded="true"] .nav-chevron { transform: rotate(180deg); }
        .sidebar-footer {
            border-top: 1px solid var(--admin-border);
            margin-top: auto;
            padding-top: .85rem;
        }
        .sidebar-logout {
            width: 100%;
            justify-content: flex-start;
        }
        .btn-logout {
            color: var(--admin-danger);
            border: 1px solid var(--admin-danger-border);
            background: var(--admin-danger-soft);
            font-weight: 600;
        }
        .btn-logout:hover {
            color: #991b1b;
            border-color: #fca5a5;
            background: #ffe4e6;
        }

        /* ── Unified Page Shell ── */
        .admin-page-shell {
            max-width: 1560px;
            margin: 0 auto;
            width: 100%;
        }

        /* ── Unified Page Header & Breadcrumb ── */
        .admin-page-header {
            margin-bottom: 1.5rem;
        }
        .admin-breadcrumb {
            font-size: 0.78rem;
            font-weight: 600;
            letter-spacing: 0.01em;
            color: var(--admin-text-muted);
            margin-bottom: 0.25rem;
        }
        .admin-breadcrumb a {
            color: var(--admin-text-muted);
            text-decoration: none;
            transition: color 0.15s ease;
        }
        .admin-breadcrumb a:hover {
            color: var(--admin-primary);
        }
        .admin-page-title {
            font-size: 1.55rem;
            font-weight: 800;
            letter-spacing: -0.02em;
            color: var(--admin-text);
            line-height: 1.2;
            margin-bottom: 0.25rem;
        }
        .admin-page-icon {
            color: var(--admin-primary);
            font-size: 1.4rem;
        }
        .admin-page-subtitle {
            font-size: 0.88rem;
            color: var(--admin-text-secondary);
            margin-bottom: 0;
        }

        /* ── Unified Surface / Card System ── */
        .admin-card {
            background: var(--admin-surface);
            border: 1px solid var(--admin-border);
            border-radius: var(--admin-radius-lg);
            box-shadow: var(--admin-shadow-xs);
            overflow: hidden;
            transition: border-color 0.15s ease, box-shadow 0.15s ease;
        }
        .admin-card-header {
            padding: 0.85rem 1.25rem;
            border-bottom: 1px solid var(--admin-border-subtle);
            background: var(--admin-surface);
            font-weight: 700;
            font-size: 0.95rem;
            color: var(--admin-text);
        }
        .admin-card-body {
            padding: 1.25rem;
        }

        /* ── Unified Metric Cards & Strips ── */
        .admin-metric-card {
            background: var(--admin-surface);
            border: 1px solid var(--admin-border);
            border-radius: var(--admin-radius-md);
            padding: 1rem 1.15rem;
            box-shadow: var(--admin-shadow-xs);
            transition: transform 0.15s ease, box-shadow 0.15s ease, border-color 0.15s ease;
            height: 100%;
        }
        .admin-metric-card:hover {
            transform: translateY(-1px);
            box-shadow: var(--admin-shadow-sm);
            border-color: var(--admin-border-strong);
        }
        .admin-metric-label {
            font-size: 0.76rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--admin-text-muted);
        }
        .admin-metric-value {
            font-size: 1.55rem;
            font-weight: 800;
            letter-spacing: -0.02em;
            line-height: 1.1;
            color: var(--admin-text);
        }
        .admin-metric-sub {
            font-size: 0.76rem;
            color: var(--admin-text-muted);
            margin-top: 0.3rem;
        }
        .admin-metric-icon {
            width: 38px;
            height: 38px;
            border-radius: var(--admin-radius-sm);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            flex-shrink: 0;
        }

        /* Unified Compact Metric Strip for Management Queues */
        .admin-queue-strip {
            background: var(--admin-surface);
            border: 1px solid var(--admin-border);
            border-radius: var(--admin-radius-md);
            padding: 0.75rem 1rem;
            box-shadow: var(--admin-shadow-xs);
            margin-bottom: 1.25rem;
        }
        .admin-queue-item {
            padding: 0.35rem 0.85rem;
        }
        .admin-queue-item:not(:last-child) {
            border-right: 1px solid var(--admin-border-subtle);
        }

        /* ── Unified Toolbar & Filters ── */
        .admin-toolbar {
            background: var(--admin-surface);
            border: 1px solid var(--admin-border);
            border-radius: var(--admin-radius-md);
            padding: 0.85rem 1.15rem;
            box-shadow: var(--admin-shadow-xs);
            margin-bottom: 1.25rem;
        }
        .admin-toolbar .form-control,
        .admin-toolbar .form-select {
            border-color: var(--admin-border);
            font-size: 0.88rem;
            border-radius: var(--admin-radius-sm);
        }
        .admin-toolbar .form-control:focus,
        .admin-toolbar .form-select:focus {
            border-color: var(--admin-border-focus);
            box-shadow: 0 0 0 3px rgba(22, 101, 52, 0.12);
        }

        /* ── Filter Chips & Segmented Controls ── */
        .filter-chip,
        .admin-filter-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            border-radius: 999px;
            padding: 0.38rem 0.85rem;
            font-size: 0.82rem;
            font-weight: 600;
            border: 1px solid var(--admin-border);
            text-decoration: none;
            color: var(--admin-text-secondary);
            background: var(--admin-surface);
            transition: all 0.15s ease;
        }
        .filter-chip:hover,
        .admin-filter-pill:hover {
            background: var(--admin-primary-soft);
            color: var(--admin-primary);
            border-color: var(--admin-primary-border);
        }
        .filter-chip.active,
        .admin-filter-pill.active {
            background: var(--admin-primary);
            color: #fff;
            border-color: var(--admin-primary);
            box-shadow: 0 2px 4px rgba(22, 101, 52, 0.2);
        }

        /* Segmented tab container */
        .admin-segmented {
            background: var(--admin-surface-subtle);
            border: 1px solid var(--admin-border);
            padding: 0.2rem;
            border-radius: 999px;
            display: inline-flex;
            gap: 0.2rem;
        }
        .admin-segmented .btn-seg {
            border: none;
            background: transparent;
            color: var(--admin-text-secondary);
            font-size: 0.82rem;
            font-weight: 600;
            padding: 0.35rem 0.85rem;
            border-radius: 999px;
            text-decoration: none;
            transition: all 0.15s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
        }
        .admin-segmented .btn-seg:hover {
            color: var(--admin-text);
        }
        .admin-segmented .btn-seg.active {
            background: var(--admin-surface);
            color: var(--admin-primary);
            font-weight: 700;
            box-shadow: var(--admin-shadow-xs);
        }

        /* ── Unified Workspace Tabs (.admin-tabs) ── */
        .admin-tabs {
            display: flex;
            flex-wrap: wrap;
            gap: 0.45rem;
            border-bottom: 1px solid var(--admin-border);
            padding-bottom: 0.75rem;
            margin-bottom: 1.25rem;
            list-style: none;
            padding-left: 0;
        }
        .admin-tabs .nav-link,
        .admin-tab-link {
            border: 1px solid var(--admin-border);
            border-radius: 999px;
            background: var(--admin-surface);
            color: var(--admin-text-secondary);
            font-size: 0.84rem;
            font-weight: 600;
            padding: 0.42rem 1rem;
            transition: all 0.15s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            text-decoration: none;
        }
        .admin-tabs .nav-link:hover,
        .admin-tab-link:hover {
            background: var(--admin-surface-hover);
            color: var(--admin-text);
            border-color: rgba(15, 23, 42, 0.18);
        }
        .admin-tabs .nav-link.active,
        .admin-tab-link.active {
            background: var(--admin-primary-soft);
            border-color: var(--admin-primary-border);
            color: var(--admin-primary);
            font-weight: 700;
        }
        .admin-tabs .nav-link .badge,
        .admin-tab-link .badge {
            font-size: 0.72rem;
            font-weight: 700;
            border-radius: 999px;
        }

        /* ── Unified Status Badges ── */
        .admin-status-badge,
        .sbadge {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            border-radius: 999px;
            padding: 0.25rem 0.65rem;
            font-size: 0.74rem;
            font-weight: 700;
            letter-spacing: 0.01em;
            white-space: nowrap;
            line-height: 1.25;
        }
        .admin-status-badge .status-icon,
        .sbadge .status-icon {
            font-size: 0.72rem;
        }
        .status-success,
        .sb-approved,
        .sb-valid {
            background: var(--admin-success-soft);
            color: var(--admin-primary);
            border: 1px solid var(--admin-success-border);
        }
        .status-warning,
        .sb-pending,
        .sb-soon {
            background: var(--admin-warning-soft);
            color: #92400e;
            border: 1px solid var(--admin-warning-border);
        }
        .status-danger,
        .sb-rejected,
        .sb-expired {
            background: var(--admin-danger-soft);
            color: #991b1b;
            border: 1px solid var(--admin-danger-border);
        }
        .status-info {
            background: var(--admin-info-soft);
            color: #075985;
            border: 1px solid var(--admin-info-border);
        }
        .status-neutral,
        .sb-missing {
            background: #f1f5f9;
            color: var(--admin-text-secondary);
            border: 1px solid #e2e8f0;
        }

        /* ── Unified Table System ── */
        .admin-table-card {
            background: var(--admin-surface);
            border: 1px solid var(--admin-border);
            border-radius: var(--admin-radius-lg);
            box-shadow: var(--admin-shadow-xs);
            overflow: hidden;
        }
        .admin-table-wrap {
            overflow-x: auto;
            width: 100%;
        }
        .admin-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.86rem;
            margin-bottom: 0;
        }
        .admin-table thead th,
        .table thead th {
            padding: 0.65rem 1rem;
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--admin-text-muted);
            font-weight: 700;
            background: var(--admin-surface-subtle);
            border-bottom: 1px solid var(--admin-border);
            white-space: nowrap;
            vertical-align: middle;
        }
        .admin-table tbody td,
        .table tbody td {
            padding: 0.8rem 1rem;
            border-bottom: 1px solid var(--admin-border-subtle);
            vertical-align: middle;
            color: var(--admin-text);
        }
        .admin-table tbody tr:hover,
        .table-hover tbody tr:hover {
            background-color: var(--admin-surface-hover);
        }
        .admin-avatar {
            width: 40px;
            height: 40px;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: var(--admin-primary-soft);
            color: var(--admin-primary);
            border: 1px solid var(--admin-primary-border);
            font-weight: 700;
            font-size: 0.9rem;
            flex-shrink: 0;
        }

        /* ── Unified Buttons ── */
        .btn-brand,
        .btn-success-obhs {
            background: var(--admin-primary);
            border-color: var(--admin-primary);
            color: #fff;
            font-weight: 600;
            border-radius: var(--admin-radius-sm);
            transition: all 0.15s ease;
        }
        .btn-brand:hover,
        .btn-success-obhs:hover {
            background: var(--admin-primary-dark);
            border-color: var(--admin-primary-dark);
            color: #fff;
        }
        .btn-outline-brand {
            border-color: var(--admin-primary-border);
            color: var(--admin-primary);
            background: transparent;
            font-weight: 600;
            border-radius: var(--admin-radius-sm);
        }
        .btn-outline-brand:hover {
            background: var(--admin-primary-soft);
            border-color: var(--admin-primary);
            color: var(--admin-primary);
        }
        .btn-action-icon {
            width: 36px;
            height: 36px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: var(--admin-radius-sm);
            font-size: 0.95rem;
        }

        /* ── Unified Empty State ── */
        .admin-empty-state {
            padding: 3rem 1.5rem;
            text-align: center;
        }
        .admin-empty-icon {
            font-size: 2.5rem;
            color: var(--admin-text-faint);
            line-height: 1;
        }
        .admin-empty-title {
            font-size: 1.05rem;
            font-weight: 700;
            color: var(--admin-text);
            margin-bottom: 0.25rem;
        }
        .admin-empty-message {
            font-size: 0.85rem;
            color: var(--admin-text-muted);
        }

        /* ── Responsive Rules ── */
        @media (max-width: 992px) {
            .dash-shell {
                padding-top: 4.2rem;
                padding-bottom: 5.25rem;
            }
            .main-col { padding: .85rem; }
            .sidepanel-col { display: none; }

            .mobile-bottom-nav {
                display: block;
                position: fixed;
                left: 0;
                right: 0;
                bottom: 0;
                z-index: 1070;
                background: rgba(255,255,255,.98);
                border-top: 1px solid var(--admin-border);
                box-shadow: none;
                backdrop-filter: blur(8px);
                -webkit-backdrop-filter: blur(8px);
            }

            .mobile-bottom-nav .nav-grid {
                display: grid;
                grid-template-columns: repeat(5, minmax(0, 1fr));
            }

            .mobile-bottom-nav .nav-link {
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                gap: .2rem;
                color: var(--admin-text-muted);
                width: 100%;
                padding: .5rem .2rem .45rem;
                font-size: .67rem;
                font-weight: 700;
                text-decoration: none;
                line-height: 1.05;
                border: 0;
                background: transparent;
            }

            .mobile-bottom-nav .nav-link i {
                font-size: .98rem;
            }

            .mobile-bottom-nav .nav-link.active {
                color: var(--admin-primary);
            }

            .mobile-bottom-nav .home-link i {
                font-size: 1.15rem;
            }

            .mobile-top-icons {
                display: inline-flex;
                align-items: center;
                gap: .45rem;
            }

            .mobile-top-icons .icon-btn {
                width: 32px;
                height: 32px;
            }

            .chatbot-widget {
                bottom: 5.4rem !important;
            }

            .chatbot-panel {
                bottom: 4.1rem !important;
            }

            .more-list .list-group-item {
                border: 1px solid var(--admin-border);
                border-radius: var(--admin-radius-md);
                margin-bottom: .5rem;
                display: flex;
                align-items: center;
                gap: .55rem;
                color: var(--admin-text-secondary);
                font-weight: 600;
            }

            .more-list .list-group-item i {
                color: var(--admin-text-muted);
            }

            #adminMoreSheet {
                border-top-left-radius: 1rem;
                border-top-right-radius: 1rem;
                --bs-offcanvas-height: clamp(245px, 37vh, 380px);
                height: clamp(245px, 37vh, 380px);
                bottom: 0;
                z-index: 1065;
            }

            #adminMoreSheet .offcanvas-body {
                padding-top: .25rem;
                overflow-y: auto;
            }

            .offcanvas-backdrop.show {
                z-index: 1060;
            }

            .chatbot-widget,
            .chatbot-panel {
                z-index: 1040 !important;
            }
        }

        @media (max-width: 420px) {
            .container,
            .container-fluid {
                padding-left: .75rem !important;
                padding-right: .75rem !important;
            }

            .main-col {
                padding: .65rem !important;
            }

            .dash-shell {
                padding-top: 4rem;
                padding-bottom: 5rem;
            }

            .admin-page-title { font-size: 1.35rem; }

            .admin-card,
            .admin-metric-card {
                border-radius: var(--admin-radius-md) !important;
            }

            .btn {
                font-size: .84rem;
                padding: .4rem .75rem;
            }

            .top-meta {
                display: none !important;
            }
        }

        @media (min-width: 992px) {
            .navbar-glass {
                left: var(--sidebar-w);
                width: calc(100% - var(--sidebar-w));
            }
            .dash-shell {
                padding-top: var(--nav-h);
                padding-left: 0;
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
                overflow-y: auto;
                overflow-x: hidden;
                scrollbar-gutter: stable;
                scrollbar-width: thin;
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
            .main-col {
                margin-left: var(--sidebar-w);
                width: calc(100% - var(--sidebar-w));
                max-width: calc(100% - var(--sidebar-w));
                flex: 0 0 auto;
                padding: 1.5rem 1.75rem;
            }
        }
    </style>
</head>
<body>
    @php
        $routeName = \Illuminate\Support\Facades\Route::currentRouteName();
        $usersOpen = is_string($routeName) && str_starts_with($routeName, 'admin.users.');

        $pendingPropertyReviewsCount = 0;
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('properties') && \Illuminate\Support\Facades\Schema::hasColumn('properties', 'approval_status')) {
                $pendingPropertyReviewsCount = \App\Models\Property::where('approval_status', 'pending')->count();
            }
        } catch (\Throwable $e) {}

        $pendingPermitApprovalsCount = 0;
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('landlord_profiles') && \Illuminate\Support\Facades\Schema::hasColumn('landlord_profiles', 'business_permit_status')) {
                $pendingPermitApprovalsCount = \App\Models\LandlordProfile::where('business_permit_status', 'pending')->count();
            }
        } catch (\Throwable $e) {}

        $pendingLandlordAccreditationCount = $pendingPermitApprovalsCount;

        $pendingStudentVerificationCount = 0;
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('users') && \Illuminate\Support\Facades\Schema::hasColumn('users', 'school_id_verification_status')) {
                $pendingStudentVerificationCount = \App\Models\User::query()
                    ->where('role', 'student')
                    ->where(function ($docQuery) {
                        $docQuery->where(function ($schoolIdQuery) {
                            if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'school_id_path')) {
                                $schoolIdQuery->whereNotNull('school_id_path')
                                    ->where('school_id_path', '!=', '');
                            }
                        });

                        if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'enrollment_proof_path')) {
                            $docQuery->orWhere(function ($proofQuery) {
                                $proofQuery->whereNotNull('enrollment_proof_path')
                                    ->where('enrollment_proof_path', '!=', '');
                            });
                        }
                    })
                    ->where(function ($query) {
                        $query->where('school_id_verification_status', 'pending')
                            ->orWhereNull('school_id_verification_status')
                            ->orWhere('school_id_verification_status', '');
                    })
                    ->count();
            }
        } catch (\Throwable $e) {}

        $pendingDocumentCount = 0;
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('landlord_documents')) {
                $pendingDocumentCount = \App\Models\LandlordDocument::current()->where('verification_status', 'pending')->count();
            }
        } catch (\Throwable $e) {}

        $notificationsCount = 0;
        try {
            if (Auth::check() && \Illuminate\Support\Facades\Schema::hasTable('notifications')) {
                $notificationsCount = \Illuminate\Notifications\DatabaseNotification::query()
                    ->where('notifiable_type', get_class(Auth::user()))
                    ->where('notifiable_id', Auth::id())
                    ->whereNull('read_at')
                    ->count();
            }
        } catch (\Throwable $e) {}
    @endphp

    <nav class="navbar navbar-expand-lg navbar-light navbar-glass fixed-top">
        <div class="container-fluid px-3 px-lg-4">
            <a class="navbar-brand d-flex align-items-center gap-2" href="{{ route('admin.dashboard') }}">
                <img src="{{ asset('images/minsu3.png') }}" alt="MINSU" class="brand-mark" />
                <span>Admin Portal</span>
            </a>
            <div class="d-flex align-items-center gap-3 ms-auto">
                <div class="mobile-top-icons d-lg-none">
                    <a @class(['icon-btn', 'active' => is_string($routeName) && str_starts_with($routeName, 'admin.reports.')]) href="{{ route('admin.reports.index') }}" title="Reports">
                        <i class="bi bi-flag"></i>
                    </a>
                    <span class="icon-btn-wrap">
                        <a @class(['icon-btn', 'active' => $routeName === 'notifications.index']) href="{{ route('notifications.index') }}" title="Notifications">
                            <i class="bi bi-bell"></i>
                        </a>
                        @if($notificationsCount > 0)
                            <span class="icon-btn-badge">{{ $notificationsCount > 99 ? '99+' : $notificationsCount }}</span>
                        @endif
                    </span>
                    <a @class(['icon-btn', 'active' => $routeName === 'admin.settings.edit']) href="{{ route('admin.settings.edit') }}" title="Settings">
                        <i class="bi bi-gear"></i>
                    </a>
                </div>
                <div class="top-meta d-none d-lg-block text-end">
                    <div class="text-xs text-uppercase tracking-wider text-muted fw-semibold">Admin Workspace</div>
                    <div class="fw-bold text-dark small">MinSU Boarding Monitoring &amp; Compliance</div>
                </div>
                <span class="icon-btn-wrap d-none d-md-inline-flex">
                    <a class="icon-btn" href="{{ route('notifications.index') }}" title="Notifications">
                        <i class="bi bi-bell"></i>
                    </a>
                    @if($notificationsCount > 0)
                        <span class="icon-btn-badge">{{ $notificationsCount > 99 ? '99+' : $notificationsCount }}</span>
                    @endif
                </span>
                <span class="user-chip d-none d-lg-inline-flex">
                    <i class="bi bi-person-circle text-success"></i>
                    <span class="fw-semibold">{{ Auth::user()->full_name }}</span>
                </span>
            </div>
        </div>
    </nav>

    <main class="dash-shell container-fluid ps-0 pe-0">
        <div class="row g-0">
            <div class="col-12 col-lg-3 col-xl-2 ps-0 sidepanel-col">
                <div class="p-3 sidepanel">
                    <div class="d-flex align-items-center gap-2 px-2 py-2 mb-2 sidebar-head">
                        @if(!empty(Auth::user()->profile_image_path))
                            <img src="{{ file_url(Auth::user()->profile_image_path) }}" alt="Profile photo" class="rounded-3 border" style="width:38px;height:38px;object-fit:cover;border-color:rgba(22,101,52,.22)!important;">
                        @else
                            <div class="rounded-3 d-inline-flex align-items-center justify-content-center" style="width:38px;height:38px;background:rgba(22,101,52,.10);border:1px solid rgba(22,101,52,.20);">
                                <i class="bi bi-person-badge" style="color:#14532d;"></i>
                            </div>
                        @endif
                        <div class="min-w-0">
                            <div class="fw-bold text-truncate text-dark" style="font-size: 0.88rem;">{{ Auth::user()->full_name }}</div>
                            <div class="small text-muted" style="font-size: 0.75rem;">Administrator</div>
                        </div>
                    </div>

                    <div class="list-group list-group-flush rounded-3 overflow-hidden">
                        <div class="nav-section">Overview</div>
                        <a @class(['list-group-item', 'active' => $routeName === 'admin.dashboard']) href="{{ route('admin.dashboard') }}">
                            <i class="bi bi-speedometer2"></i>
                            <span>Dashboard</span>
                        </a>

                        <div class="nav-section">Monitoring</div>
                        <a @class(['list-group-item', 'active' => is_string($routeName)
                            && str_starts_with($routeName, 'admin.properties.')
                            && !str_starts_with($routeName, 'admin.properties.pending')
                            && !str_starts_with($routeName, 'admin.properties.approve')
                            && !str_starts_with($routeName, 'admin.properties.reject')
                            && $routeName !== 'admin.property_reviews.index'
                        ]) href="{{ route('admin.properties.index') }}">
                            <i class="bi bi-buildings"></i>
                            <span>Boarding Houses</span>
                        </a>
                        <a @class(['list-group-item', 'active' => in_array($routeName, ['admin.boarding_monitoring.students', 'admin.boarded_students.index', 'admin.bookings.index'], true)]) href="{{ route('admin.boarding_monitoring.students') }}">
                            <i class="bi bi-door-open"></i>
                            <span>Active Boarders</span>
                        </a>
                        <a @class(['list-group-item', 'active' => is_string($routeName) && str_starts_with($routeName, 'admin.onboardings.')]) href="{{ route('admin.onboardings.index') }}">
                            <i class="bi bi-clipboard-check"></i>
                            <span>Onboarding Pipeline</span>
                        </a>
                        <a @class(['list-group-item', 'active' => $routeName === 'admin.documents.monitoring']) href="{{ route('admin.documents.monitoring') }}">
                            <i class="bi bi-activity"></i>
                            <span>Compliance Monitor</span>
                        </a>

                        <div class="nav-section">Management</div>
                        <a @class(['list-group-item', 'active' => is_string($routeName)
                            && (
                                $routeName === 'admin.property_reviews.index'
                                || str_starts_with($routeName, 'admin.properties.pending')
                                || str_starts_with($routeName, 'admin.properties.approve')
                                || str_starts_with($routeName, 'admin.properties.reject')
                            )
                        ]) href="{{ route('admin.property_reviews.index') }}">
                            <i class="bi bi-shield-check"></i>
                            <span>Property Reviews</span>
                            @if($pendingPropertyReviewsCount > 0)
                                <span class="badge rounded-pill text-bg-danger ms-auto">{{ $pendingPropertyReviewsCount }}</span>
                            @endif
                        </a>
                        <a @class(['list-group-item', 'active' => is_string($routeName) && ($routeName === 'admin.approvals.students' || str_starts_with($routeName, 'admin.student_verifications.'))]) href="{{ route('admin.approvals.students') }}">
                            <i class="bi bi-person-vcard"></i>
                            <span>Student Verification</span>
                            @if($pendingStudentVerificationCount > 0)
                                <span class="badge rounded-pill text-bg-danger ms-auto">{{ $pendingStudentVerificationCount }}</span>
                            @endif
                        </a>
                        <a @class(['list-group-item', 'active' => is_string($routeName)
                            && (
                                $routeName === 'admin.approvals.landlords'
                                || str_starts_with($routeName, 'admin.permits.')
                                || str_starts_with($routeName, 'admin.permits.approve')
                                || str_starts_with($routeName, 'admin.permits.reject')
                            )
                        ]) href="{{ route('admin.approvals.landlords') }}">
                            <i class="bi bi-check2-circle"></i>
                            <span>Landlord Accreditation</span>
                            @if($pendingLandlordAccreditationCount > 0)
                                <span class="badge rounded-pill text-bg-danger ms-auto">{{ $pendingLandlordAccreditationCount }}</span>
                            @endif
                        </a>
                        <a @class(['list-group-item', 'active' => $routeName === 'admin.documents.verification']) href="{{ route('admin.documents.verification') }}">
                            <i class="bi bi-file-earmark-check"></i>
                            <span>Document Review Queue</span>
                            @if($pendingDocumentCount > 0)
                                <span class="badge rounded-pill text-bg-danger ms-auto">{{ $pendingDocumentCount }}</span>
                            @endif
                        </a>

                        <div class="nav-section">Insights</div>
                        <a @class(['list-group-item', 'active' => is_string($routeName) && str_starts_with($routeName, 'admin.reports.')]) href="{{ route('admin.reports.index') }}">
                            <i class="bi bi-flag"></i>
                            <span>Reports</span>
                        </a>
                        <a @class(['list-group-item', 'active' => is_string($routeName) && str_starts_with($routeName, 'admin.analytics.')]) href="{{ \Illuminate\Support\Facades\Route::has('admin.analytics.index') ? route('admin.analytics.index') : url('/admin/analytics') }}">
                            <i class="bi bi-graph-up-arrow"></i>
                            <span>Analytics</span>
                        </a>

                        <div class="nav-section">System</div>
                        <button class="list-group-item nav-toggle" type="button" data-bs-toggle="collapse" data-bs-target="#navUsers" aria-expanded="{{ $usersOpen ? 'true' : 'false' }}" aria-controls="navUsers">
                            <i class="bi bi-people"></i>
                            <span>User Accounts</span>
                            <i class="bi bi-chevron-down nav-chevron" style="font-size:.9rem;"></i>
                        </button>
                        <div class="collapse {{ $usersOpen ? 'show' : '' }}" id="navUsers">
                            <a @class(['list-group-item sub-item', 'active' => $routeName === 'admin.users.index']) href="{{ route('admin.users.index') }}">
                                <i class="bi bi-people"></i> All Users
                            </a>
                            <a @class(['list-group-item sub-item', 'active' => $routeName === 'admin.users.students']) href="{{ route('admin.users.students') }}">
                                <i class="bi bi-mortarboard"></i> Students
                            </a>
                            <a @class(['list-group-item sub-item', 'active' => $routeName === 'admin.users.landlords']) href="{{ route('admin.users.landlords') }}">
                                <i class="bi bi-building"></i> Landlords
                            </a>
                        </div>
                        <a @class(['list-group-item', 'active' => $routeName === 'notifications.index']) href="{{ route('notifications.index') }}">
                            <i class="bi bi-bell"></i>
                            <span>Notifications</span>
                            @if($notificationsCount > 0)
                                <span class="badge rounded-pill text-bg-danger ms-auto">{{ $notificationsCount }}</span>
                            @endif
                        </a>
                    </div>

                    <div class="sidebar-footer px-1 mt-3">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-logout rounded-pill sidebar-logout">
                                <i class="bi bi-box-arrow-right me-1"></i> Logout
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-9 col-xl-10 main-col">
                @yield('content')
            </div>
        </div>
    </main>

    @php
        $isUsersRoute = is_string($routeName) && str_starts_with($routeName, 'admin.users.');
        $isPropertiesRoute = is_string($routeName)
            && str_starts_with($routeName, 'admin.properties.')
            && !str_starts_with($routeName, 'admin.properties.pending')
            && !str_starts_with($routeName, 'admin.properties.approve')
            && !str_starts_with($routeName, 'admin.properties.reject')
            && $routeName !== 'admin.property_reviews.index';
        $isManagementRoute = is_string($routeName)
            && (
                $routeName === 'admin.property_reviews.index'
                || $routeName === 'admin.approvals.landlords'
                || $routeName === 'admin.approvals.students'
                || $routeName === 'admin.documents.verification'
                || str_starts_with($routeName, 'admin.properties.pending')
                || str_starts_with($routeName, 'admin.permits.')
                || str_starts_with($routeName, 'admin.properties.approve')
                || str_starts_with($routeName, 'admin.properties.reject')
                || str_starts_with($routeName, 'admin.permits.approve')
                || str_starts_with($routeName, 'admin.permits.reject')
                || str_starts_with($routeName, 'admin.student_verifications.')
            );
        $isPropertyReviewsRoute = is_string($routeName) && ($routeName === 'admin.property_reviews.index' || str_starts_with($routeName, 'admin.properties.pending'));
        $isLandlordAccreditationRoute = is_string($routeName) && ($routeName === 'admin.approvals.landlords' || str_starts_with($routeName, 'admin.permits.'));
        $isStudentVerificationsRoute = is_string($routeName) && ($routeName === 'admin.approvals.students' || str_starts_with($routeName, 'admin.student_verifications.'));
        $isDocVerificationRoute = $routeName === 'admin.documents.verification';
        $isDocMonitoringRoute = $routeName === 'admin.documents.monitoring';
        $isBoardedStudentsRoute = in_array($routeName, ['admin.boarded_students.index', 'admin.boarding_monitoring.students'], true);
        $isOnboardingsRoute = is_string($routeName) && str_starts_with($routeName, 'admin.onboardings.');
        $isReportsRoute = is_string($routeName) && str_starts_with($routeName, 'admin.reports.');
    @endphp

    <nav class="mobile-bottom-nav d-lg-none" aria-label="Admin mobile navigation">
        <div class="nav-grid">
            <a @class(['nav-link', 'active' => $isPropertiesRoute]) href="{{ route('admin.properties.index') }}">
                <i class="bi bi-buildings"></i>
                <span>Properties</span>
            </a>
            <a @class(['nav-link', 'active' => $isBoardedStudentsRoute]) href="{{ route('admin.boarding_monitoring.students') }}">
                <i class="bi bi-door-open"></i>
                <span>Boarders</span>
            </a>
            <a @class(['nav-link', 'home-link', 'active' => $routeName === 'admin.dashboard']) href="{{ route('admin.dashboard') }}">
                <i class="bi bi-house-door"></i>
                <span>Home</span>
            </a>
            <a @class(['nav-link', 'active' => $isManagementRoute]) href="{{ route('admin.property_reviews.index') }}">
                <i class="bi bi-shield-check"></i>
                <span>Reviews</span>
            </a>
            <button type="button" @class(['nav-link', 'active' => $isUsersRoute || $isOnboardingsRoute || $isDocMonitoringRoute || $isReportsRoute]) data-bs-toggle="offcanvas" data-bs-target="#adminMoreSheet" aria-controls="adminMoreSheet">
                <i class="bi bi-three-dots"></i>
                <span>More</span>
            </button>
        </div>
    </nav>

    <div class="offcanvas offcanvas-bottom d-lg-none" tabindex="-1" id="adminMoreSheet" aria-labelledby="adminMoreSheetLabel">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title fw-semibold" id="adminMoreSheetLabel">Admin Navigation</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
            <div class="list-group more-list">
                <div class="nav-section px-2 py-1 small text-uppercase text-muted fw-bold">Monitoring</div>
                <a @class(['list-group-item', 'active' => $isPropertiesRoute]) href="{{ route('admin.properties.index') }}">
                    <i class="bi bi-buildings"></i>
                    <span>Boarding Houses</span>
                </a>
                <a @class(['list-group-item', 'active' => $isBoardedStudentsRoute]) href="{{ route('admin.boarding_monitoring.students') }}">
                    <i class="bi bi-door-open"></i>
                    <span>Active Boarders</span>
                </a>
                <a @class(['list-group-item', 'active' => $isOnboardingsRoute]) href="{{ route('admin.onboardings.index') }}">
                    <i class="bi bi-clipboard-check"></i>
                    <span>Onboarding Pipeline</span>
                </a>
                <a @class(['list-group-item', 'active' => $isDocMonitoringRoute]) href="{{ route('admin.documents.monitoring') }}">
                    <i class="bi bi-activity"></i>
                    <span>Compliance Monitor</span>
                </a>

                <div class="nav-section px-2 py-1 mt-2 small text-uppercase text-muted fw-bold">Management</div>
                <a @class(['list-group-item', 'active' => $isPropertyReviewsRoute]) href="{{ route('admin.property_reviews.index') }}">
                    <i class="bi bi-shield-check"></i>
                    <span>Property Reviews</span>
                    @if($pendingPropertyReviewsCount > 0)
                        <span class="badge rounded-pill text-bg-danger ms-auto">{{ $pendingPropertyReviewsCount }}</span>
                    @endif
                </a>
                <a @class(['list-group-item', 'active' => $isStudentVerificationsRoute]) href="{{ route('admin.approvals.students') }}">
                    <i class="bi bi-person-vcard"></i>
                    <span>Student Verification</span>
                    @if($pendingStudentVerificationCount > 0)
                        <span class="badge rounded-pill text-bg-danger ms-auto">{{ $pendingStudentVerificationCount }}</span>
                    @endif
                </a>
                <a @class(['list-group-item', 'active' => $isLandlordAccreditationRoute]) href="{{ route('admin.approvals.landlords') }}">
                    <i class="bi bi-check2-circle"></i>
                    <span>Landlord Accreditation</span>
                    @if($pendingLandlordAccreditationCount > 0)
                        <span class="badge rounded-pill text-bg-danger ms-auto">{{ $pendingLandlordAccreditationCount }}</span>
                    @endif
                </a>
                <a @class(['list-group-item', 'active' => $isDocVerificationRoute]) href="{{ route('admin.documents.verification') }}">
                    <i class="bi bi-file-earmark-check"></i>
                    <span>Document Review Queue</span>
                    @if($pendingDocumentCount > 0)
                        <span class="badge rounded-pill text-bg-danger ms-auto">{{ $pendingDocumentCount }}</span>
                    @endif
                </a>

                <div class="nav-section px-2 py-1 mt-2 small text-uppercase text-muted fw-bold">Insights &amp; System</div>
                <a @class(['list-group-item', 'active' => $isReportsRoute]) href="{{ route('admin.reports.index') }}">
                    <i class="bi bi-flag"></i>
                    <span>Reports</span>
                </a>
                <a @class(['list-group-item', 'active' => is_string($routeName) && str_starts_with($routeName, 'admin.analytics.')]) href="{{ \Illuminate\Support\Facades\Route::has('admin.analytics.index') ? route('admin.analytics.index') : url('/admin/analytics') }}">
                    <i class="bi bi-graph-up-arrow"></i>
                    <span>Analytics</span>
                </a>
                <a @class(['list-group-item', 'active' => $isUsersRoute]) href="{{ route('admin.users.index') }}">
                    <i class="bi bi-people"></i>
                    <span>User Accounts</span>
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="list-group-item text-danger bg-white">
                        <i class="bi bi-box-arrow-right text-danger"></i>
                        <span>Logout</span>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- OBHS Document Preview Modal -->
    <div class="modal fade" id="obhsDocumentPreviewModal" tabindex="-1" aria-labelledby="obhsDocumentPreviewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-light py-2 px-3 border-bottom">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-file-earmark-text text-primary fs-5"></i>
                        <h6 class="modal-title fw-semibold mb-0" id="obhsDocumentPreviewModalLabel">Document Preview</h6>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0 bg-dark position-relative text-center d-flex justify-content-center align-items-center" style="min-height: 520px; max-height: 82vh;">
                    <div id="obhsDocumentPreviewSpinner" class="position-absolute top-50 start-50 translate-middle text-white text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading document...</span>
                        </div>
                        <div class="small mt-2 text-white-50">Loading document...</div>
                    </div>
                    <iframe id="obhsDocumentPreviewIframe" src="" class="w-100 border-0 d-none" style="height: 78vh;" onload="const sp = document.getElementById('obhsDocumentPreviewSpinner'); if(sp) sp.classList.add('d-none');" onerror="const sp = document.getElementById('obhsDocumentPreviewSpinner'); if(sp) sp.classList.add('d-none'); const err = document.getElementById('obhsDocumentPreviewError'); if(err) err.classList.remove('d-none');"></iframe>
                    <img id="obhsDocumentPreviewImage" src="" alt="Document Preview" class="img-fluid d-none" style="max-height: 78vh; object-fit: contain;" onload="const sp = document.getElementById('obhsDocumentPreviewSpinner'); if(sp) sp.classList.add('d-none');" onerror="const sp = document.getElementById('obhsDocumentPreviewSpinner'); if(sp) sp.classList.add('d-none'); const err = document.getElementById('obhsDocumentPreviewError'); if(err) err.classList.remove('d-none');" />
                    <div id="obhsDocumentPreviewError" class="d-none text-white p-4">
                        <i class="bi bi-exclamation-triangle text-warning fs-1"></i>
                        <p class="mt-2 mb-0">Unable to preview this document format directly.</p>
                        <a id="obhsDocumentPreviewFallbackLink" href="#" class="btn btn-sm btn-primary mt-3" download>Download File</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function openDocumentPreview(url, title = 'Document Preview', forcedType = null) {
            if (!url) return;
            const modalEl = document.getElementById('obhsDocumentPreviewModal');
            if (!modalEl) {
                window.location.href = url;
                return;
            }

            const modalTitle = document.getElementById('obhsDocumentPreviewModalLabel');
            const spinner = document.getElementById('obhsDocumentPreviewSpinner');
            const iframe = document.getElementById('obhsDocumentPreviewIframe');
            const img = document.getElementById('obhsDocumentPreviewImage');
            const errorBox = document.getElementById('obhsDocumentPreviewError');
            const fallbackLink = document.getElementById('obhsDocumentPreviewFallbackLink');

            if (modalTitle) modalTitle.textContent = title;
            if (fallbackLink) fallbackLink.href = url;

            if (spinner) spinner.classList.remove('d-none');
            if (iframe) { iframe.classList.add('d-none'); iframe.src = ''; }
            if (img) { img.classList.add('d-none'); img.src = ''; }
            if (errorBox) errorBox.classList.add('d-none');

            const cleanUrl = url.split('?')[0].toLowerCase();
            const isImg = forcedType === 'image' || cleanUrl.match(/\.(jpg|jpeg|png|webp|gif|svg)$/);

            if (isImg && img) {
                img.src = url;
                img.classList.remove('d-none');
            } else if (iframe) {
                iframe.src = url;
                iframe.classList.remove('d-none');
            }

            const modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
            modalInstance.show();
        }

        document.getElementById('obhsDocumentPreviewModal')?.addEventListener('hidden.bs.modal', function () {
            const iframe = document.getElementById('obhsDocumentPreviewIframe');
            const img = document.getElementById('obhsDocumentPreviewImage');
            if (iframe) iframe.src = '';
            if (img) img.src = '';
        });
    </script>
    <x-toast />
    <x-chatbot />
    @stack('scripts')
</body>
</html>
