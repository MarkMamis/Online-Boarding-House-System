@extends('layouts.student_dashboard')

@section('title', 'Browse Rooms')

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin=""/>
    <style>
        .room-browse-card {
            background: #fff;
            border-radius: 1.1rem;
            overflow: hidden;
            border: 1px solid rgba(2,8,20,.08);
            box-shadow: 0 4px 16px rgba(2,8,20,.06);
            transition: box-shadow .2s, transform .2s;
            display: flex;
            flex-direction: column;
            color: inherit;
        }
        .room-browse-card:hover {
            box-shadow: 0 12px 32px rgba(2,8,20,.13);
            transform: translateY(-3px);
        }
        .room-browse-card-dimmed { opacity: .65; }

        .room-browse-photo {
            position: relative;
            aspect-ratio: 4/3;
            overflow: hidden;
            background: #f1f3f5;
            flex-shrink: 0;
        }
        .room-browse-photo img {
            width: 100%; height: 100%;
            object-fit: cover;
            transition: transform .3s ease;
        }
        .room-browse-card:hover .room-browse-photo img { transform: scale(1.04); }
        .room-browse-nophoto {
            width:100%; height:100%;
            display:flex; align-items:center; justify-content:center;
            background: #f8f9fa;
        }
        .room-browse-badge-top {
            position: absolute; top: .6rem; left: .6rem;
            background: rgba(22,101,52,.9);
            color: #fff; font-size: .7rem; font-weight: 600;
            padding: .2rem .6rem; border-radius: 2rem;
            backdrop-filter: blur(4px);
        }
        .room-browse-badge-status {
            position: absolute; top: .6rem; right: .6rem;
            background: rgba(0,0,0,.55);
            color: #fff; font-size: .7rem; font-weight: 600;
            padding: .2rem .6rem; border-radius: 2rem;
        }

        .room-browse-body {
            padding: .85rem 1rem .5rem;
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: .42rem;
        }
        .room-browse-footer { padding: .5rem 1rem .85rem; }
        .room-inc-chip {
            display: inline-block;
            background: #f1f5f9;
            border: 1px solid #e2e8f0;
            color: #475569;
            font-size: .68rem;
            font-weight: 500;
            padding: .15rem .55rem;
            border-radius: 2rem;
        }

        .pricing-type-pill {
            background: #eef2ff;
            border: 1px solid #c7d2fe;
            color: #312e81;
            font-size: .68rem;
            font-weight: 700;
            border-radius: .55rem;
            padding: .16rem .5rem;
            display: inline-flex;
            align-items: center;
            line-height: 1;
        }

        .room-card-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: .55rem;
        }

        .room-property-title {
            font-size: .96rem;
            font-weight: 800;
            color: #111827;
            line-height: 1.2;
            margin: 0;
        }

        .room-address-line {
            display: inline-flex;
            align-items: center;
            gap: .28rem;
            color: #64748b;
            font-size: .77rem;
            line-height: 1.25;
            margin-top: .18rem;
        }

        .room-tags-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .45rem;
            flex-wrap: wrap;
        }

        .room-rating-chip {
            display: inline-flex;
            align-items: center;
            gap: .26rem;
            border-radius: 999px;
            border: 1px solid #e2e8f0;
            background: #f8fafc;
            color: #475569;
            font-size: .67rem;
            font-weight: 700;
            padding: .17rem .52rem;
            line-height: 1;
        }

        .room-main-grid {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: .55rem;
            align-items: end;
            margin-top: .2rem;
        }

        .room-identity-title {
            font-size: .96rem;
            font-weight: 800;
            color: #111827;
            line-height: 1.2;
        }

        .room-occupancy-line {
            margin-top: .18rem;
            font-size: .76rem;
            color: #475569;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: .28rem;
        }

        .room-slot-summary {
            margin-top: .26rem;
            font-size: .74rem;
            font-weight: 700;
            color: #0f766e;
            line-height: 1.25;
        }

        .room-slot-summary.is-full {
            color: #991b1b;
        }

        .room-slot-summary.is-maintenance {
            color: #334155;
        }

        .room-slot-progress {
            height: 7px;
            border-radius: 999px;
            background: #e2e8f0;
            overflow: hidden;
            margin-top: .26rem;
        }

        .room-slot-progress > span {
            display: block;
            height: 100%;
            border-radius: inherit;
            background: linear-gradient(90deg, #16a34a, #22c55e);
            transition: width .25s ease;
        }

        .room-slot-progress.is-full > span {
            background: linear-gradient(90deg, #dc2626, #ef4444);
        }

        .room-slot-progress.is-maintenance > span {
            background: linear-gradient(90deg, #64748b, #94a3b8);
        }

        .room-chip-wrap {
            display: flex;
            flex-wrap: wrap;
            gap: .35rem;
            margin-top: .08rem;
        }

        .room-pricing-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            gap: .55rem;
            margin-top: .5rem;
        }

        .room-price-emphasis {
            min-width: 168px;
            border-radius: .8rem;
            padding: .45rem .65rem;
            text-align: right;
            background: linear-gradient(135deg, #065f46 0%, #0f766e 100%);
            color: #ffffff;
            box-shadow: 0 8px 18px rgba(15,118,110,.28);
            border: 1px solid rgba(255,255,255,.16);
        }

        .room-price-emphasis.is-muted {
            background: linear-gradient(135deg, #475569 0%, #64748b 100%);
            box-shadow: none;
        }

        .room-price-main {
            font-size: 1.28rem;
            font-weight: 800;
            letter-spacing: -.02em;
            line-height: 1.05;
        }

        .room-price-main .room-price-currency {
            font-size: .82rem;
            font-weight: 700;
            opacity: .92;
            margin-right: .12rem;
        }

        .room-price-main .room-price-suffix {
            font-size: .72rem;
            opacity: .9;
            font-weight: 600;
            margin-left: .08rem;
        }

        .room-price-meta {
            margin-top: .14rem;
            font-size: .64rem;
            font-weight: 700;
            letter-spacing: .06em;
            text-transform: uppercase;
            opacity: .9;
            line-height: 1.25;
        }

        .room-price-subsplit {
            margin-top: .14rem;
            font-size: .66rem;
            font-weight: 600;
            opacity: .92;
            line-height: 1.25;
        }

        .recommended-carousel {
            position: relative;
            padding-inline: 2.15rem;
        }

        .recommended-track {
            --fade-left: 24px;
            --fade-right: 28px;
            display: flex;
            gap: .95rem;
            overflow-x: auto;
            scroll-snap-type: x mandatory;
            scroll-behavior: smooth;
            padding: .1rem 0 .35rem;
            scrollbar-width: none;
            -ms-overflow-style: none;
            -webkit-mask-image: linear-gradient(90deg, transparent 0, #000 var(--fade-left), #000 calc(100% - var(--fade-right)), transparent 100%);
            mask-image: linear-gradient(90deg, transparent 0, #000 var(--fade-left), #000 calc(100% - var(--fade-right)), transparent 100%);
        }

        .recommended-carousel.at-start .recommended-track {
            --fade-left: 0px;
        }

        .recommended-carousel.at-end .recommended-track {
            --fade-right: 0px;
        }

        .recommended-track::-webkit-scrollbar {
            display: none;
        }

        .recommended-item {
            flex: 0 0 calc((100% - (2 * .95rem)) / 3.22);
            min-width: 280px;
            scroll-snap-align: start;
        }

        .recommended-arrow {
            position: absolute;
            top: 42%;
            transform: translateY(-50%);
            width: 2.05rem;
            height: 2.05rem;
            border-radius: 999px;
            border: 1px solid #d1d5db;
            background: #ffffff;
            color: #0f172a;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 6px 16px rgba(15, 23, 42, .12);
            z-index: 3;
            transition: transform .2s ease, box-shadow .2s ease, background .2s ease;
        }

        .recommended-arrow:hover:not(:disabled) {
            transform: translateY(-50%) scale(1.03);
            background: #f8fafc;
            box-shadow: 0 10px 18px rgba(15, 23, 42, .16);
        }

        .recommended-arrow:disabled {
            opacity: .4;
            cursor: not-allowed;
        }

        .recommended-arrow.prev {
            left: .15rem;
        }

        .recommended-arrow.next {
            right: .15rem;
        }

        .recommended-meta {
            margin-top: .5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .6rem;
            color: #64748b;
            font-size: .76rem;
        }

        .recommended-progress {
            font-weight: 700;
            color: #334155;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 999px;
            padding: .12rem .56rem;
        }

        .recommended-hint {
            display: inline-flex;
            align-items: center;
            gap: .28rem;
        }

        .recommended-hint i {
            color: #0f766e;
        }

        .rooms-filter-shell {
            margin-bottom: 1.2rem;
        }

        .rooms-header-grid {
            display: grid;
            grid-template-columns: minmax(260px, 1fr) minmax(360px, 1.35fr);
            gap: 1rem;
            align-items: start;
        }

        .rooms-title {
            margin: 0;
            font-size: clamp(1.55rem, 2.7vw, 2.3rem);
            font-weight: 850;
            color: #0f172a;
            line-height: 1.08;
        }

        .rooms-subtitle {
            margin-top: .4rem;
            color: #475569;
            font-size: .9rem;
            max-width: none;
            line-height: 1.4;
        }

        .rooms-toolbar {
            display: flex;
            align-items: center;
            gap: .55rem;
            justify-content: flex-end;
            flex-wrap: wrap;
        }

        .rooms-search-input {
            flex: 1 1 260px;
            min-width: 220px;
            border-radius: 999px;
            border: 1px solid #cbd5e1;
            background: #ffffff;
            color: #0f172a;
            padding: .62rem .95rem;
            outline: none;
            box-shadow: inset 0 1px 1px rgba(2, 8, 20, .03);
        }

        .rooms-search-input:focus {
            border-color: #0f766e;
            box-shadow: 0 0 0 .16rem rgba(15, 118, 110, .15);
        }

        .btn-filters-toggle {
            border-radius: 999px;
            border: 1px solid #166534;
            background: #166534;
            color: #ecfdf5;
            font-weight: 700;
            padding: .5rem .9rem;
            display: inline-flex;
            align-items: center;
            gap: .38rem;
            line-height: 1;
            box-shadow: 0 8px 20px rgba(22, 101, 52, .22);
            transition: color .2s ease, background .2s ease, border-color .2s ease, box-shadow .2s ease;
        }

        .btn-filters-toggle:hover,
        .btn-filters-toggle:focus-visible,
        .btn-filters-toggle[aria-expanded="true"] {
            color: #166534;
            background: #ffffff;
            border-color: #166534;
            box-shadow: none;
        }

        .glass-card .btn-brand {
            border: 1px solid #166534;
            background: #166534;
            color: #ecfdf5;
            transition: color .2s ease, background .2s ease, border-color .2s ease, box-shadow .2s ease;
        }

        .glass-card .btn-brand:hover,
        .glass-card .btn-brand:focus-visible,
        .room-browse-card:hover .room-browse-footer .btn-brand {
            color: #166534;
            background: #ffffff;
            border-color: #166534;
        }

        .btn-filters-reset {
            border-radius: 999px;
            border: 1px solid #94a3b8;
            color: #475569;
            background: #ffffff;
            padding: .5rem .88rem;
            font-weight: 600;
            line-height: 1;
            text-decoration: none;
        }

        .btn-filters-reset:hover {
            color: #334155;
            background: #f8fafc;
            border-color: #64748b;
        }

        .advanced-filters-panel {
            margin-top: .85rem;
            border: 1px solid #cbd5e1;
            border-radius: .95rem;
            background: #f8fafc;
            padding: .72rem;
            transition: all .2s ease;
        }

        .advanced-filters-panel.is-collapsed {
            display: none;
        }

        .advanced-filters-grid {
            display: grid;
            grid-template-columns: minmax(0, 1.25fr) minmax(0, 1fr) minmax(0, .78fr) minmax(0, .78fr);
            gap: .68rem;
        }

        .filters-card {
            border: 1px solid #cbd5e1;
            border-radius: .85rem;
            background: #ffffff;
            padding: .65rem;
        }

        .filters-card-title {
            font-size: .8rem;
            font-weight: 850;
            letter-spacing: .03em;
            text-transform: uppercase;
            color: #0f172a;
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            margin-bottom: .58rem;
        }

        .price-chip-line {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: .36rem;
            margin-bottom: .46rem;
        }

        .price-chip {
            border-radius: 999px;
            border: 1px solid #cbd5e1;
            background: #ffffff;
            color: #334155;
            font-size: .78rem;
            font-weight: 700;
            padding: .1rem .52rem;
        }

        .price-caption-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: .72rem;
            text-transform: uppercase;
            letter-spacing: .04em;
            color: #64748b;
            font-weight: 800;
            margin-bottom: .3rem;
        }

        .shared-range-slider {
            position: relative;
            padding-top: .85rem;
            height: 3.15rem;
        }

        .shared-range-slider .range-track {
            position: absolute;
            left: 0;
            right: 0;
            top: 1.6rem;
            height: 6px;
            border-radius: 999px;
            background: #cbd5e1;
            overflow: hidden;
        }

        .shared-range-slider .range-fill {
            position: absolute;
            top: 0;
            height: 100%;
            border-radius: inherit;
            background: linear-gradient(90deg, #0f766e, #14b8a6);
        }

        .range-handle {
            -webkit-appearance: none;
            appearance: none;
            position: absolute;
            left: 0;
            right: 0;
            top: 1rem;
            width: 100%;
            height: 1.3rem;
            margin: 0;
            background: none;
            pointer-events: none;
        }

        .range-handle::-webkit-slider-runnable-track {
            height: 6px;
            background: transparent;
            border: none;
        }

        .range-handle::-moz-range-track {
            height: 6px;
            background: transparent;
            border: none;
        }

        .range-handle::-webkit-slider-thumb {
            -webkit-appearance: none;
            appearance: none;
            width: 16px;
            height: 16px;
            border-radius: 999px;
            background: #0f766e;
            border: 2px solid #ffffff;
            box-shadow: 0 0 0 1px rgba(15, 23, 42, .2), 0 4px 12px rgba(15, 118, 110, .3);
            margin-top: -5px;
            pointer-events: auto;
            cursor: pointer;
            position: relative;
            z-index: 2;
        }

        .range-handle::-moz-range-thumb {
            width: 16px;
            height: 16px;
            border-radius: 999px;
            background: #0f766e;
            border: 2px solid #ffffff;
            box-shadow: 0 0 0 1px rgba(15, 23, 42, .2), 0 4px 12px rgba(15, 118, 110, .3);
            pointer-events: auto;
            cursor: pointer;
            position: relative;
            z-index: 2;
        }

        .range-indicator {
            position: absolute;
            top: 0;
            transform: translate(-50%, -95%);
            font-size: .69rem;
            font-weight: 800;
            line-height: 1;
            color: #ecfeff;
            background: #0f766e;
            border: 1px solid rgba(255, 255, 255, .22);
            border-radius: 999px;
            padding: .18rem .42rem;
            white-space: nowrap;
            opacity: 0;
            pointer-events: none;
            transition: opacity .15s ease, transform .15s ease;
            z-index: 4;
            box-shadow: 0 8px 14px rgba(15, 118, 110, .28);
        }

        .shared-range-slider.show-min .range-indicator.min,
        .shared-range-slider.show-max .range-indicator.max {
            opacity: 1;
            transform: translate(-50%, -118%);
        }

        .shared-range-slider.tight-gap .range-indicator.min {
            transform: translate(-86%, -118%);
        }

        .shared-range-slider.tight-gap .range-indicator.max {
            transform: translate(-14%, -118%);
        }

        .amenity-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: .45rem;
            max-height: 166px;
            overflow: auto;
            padding-right: .18rem;
        }

        .choice-item {
            border: 1px solid #cbd5e1;
            border-radius: .68rem;
            background: #ffffff;
            padding: .28rem .45rem;
            display: flex;
            align-items: center;
            gap: .4rem;
            color: #334155;
            font-size: .86rem;
            line-height: 1.25;
        }

        .choice-item input {
            margin: 0;
            accent-color: #0f766e;
        }

        .rating-stack,
        .occupancy-stack {
            display: flex;
            flex-direction: column;
            gap: .4rem;
        }

        .occupancy-card {
            display: flex;
            flex-direction: column;
        }

        .property-room-group {
            border: 1px solid #e2e8f0;
            border-radius: 1rem;
            padding: .8rem;
            background: #ffffff;
            box-shadow: 0 8px 22px rgba(2, 8, 20, .05);
        }

        .property-group-head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: .7rem;
            margin-bottom: .65rem;
            padding-bottom: .62rem;
            border-bottom: 1px solid #edf2f7;
        }

        .property-group-title {
            margin: 0;
            font-size: 1rem;
            font-weight: 800;
            color: #0f172a;
            line-height: 1.25;
        }

        .property-group-address {
            margin-top: .2rem;
            display: inline-flex;
            align-items: center;
            gap: .3rem;
            color: #64748b;
            font-size: .78rem;
            line-height: 1.25;
        }

        .property-group-meta {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            flex-wrap: wrap;
            justify-content: flex-end;
        }

        .property-meta-chip {
            display: inline-flex;
            align-items: center;
            gap: .28rem;
            border-radius: 999px;
            border: 1px solid #e2e8f0;
            background: #f8fafc;
            color: #334155;
            font-size: .69rem;
            font-weight: 700;
            line-height: 1;
            padding: .2rem .56rem;
        }

        .property-meta-chip.available {
            border-color: #bbf7d0;
            background: #f0fdf4;
            color: #166534;
        }

        .property-meta-chip.rating {
            border-color: #fcd34d;
            background: #fffbeb;
            color: #92400e;
        }

        @media (max-width: 1199.98px) {
            .advanced-filters-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .recommended-item {
                flex-basis: calc((100% - .95rem) / 2.2);
                min-width: 300px;
            }
        }

        @media (max-width: 767.98px) {
            .rooms-header-grid {
                grid-template-columns: 1fr;
            }

            .rooms-toolbar {
                justify-content: flex-start;
            }

            .advanced-filters-grid {
                grid-template-columns: 1fr;
            }

            .recommended-carousel {
                padding-inline: 1.7rem;
            }

            .recommended-track {
                --fade-left: 16px;
                --fade-right: 20px;
            }

            .recommended-item {
                flex-basis: calc(100% / 1.08);
                min-width: 250px;
            }
        }

        @media (max-width: 575.98px) {
            .amenity-grid {
                grid-template-columns: 1fr;
                max-height: 190px;
            }

            .property-room-group {
                padding: .62rem;
            }

            .property-group-head {
                flex-direction: column;
                align-items: stretch;
                gap: .45rem;
            }

            .property-group-meta {
                justify-content: flex-start;
            }

            .room-pricing-row {
                flex-direction: column;
                align-items: stretch;
            }

            .room-main-grid {
                grid-template-columns: 1fr;
                align-items: stretch;
            }

            .room-price-emphasis {
                width: 100%;
                text-align: left;
                min-width: 0;
            }

            .room-tags-row {
                align-items: flex-start;
            }
        }
    </style>
@endpush

@section('content')
@php
    $student = auth()->user();
    $schoolIdVerificationStatus = (string) ($student->school_id_verification_status ?? '');
    if ($schoolIdVerificationStatus === '') {
        $hasVerificationDocument = filled($student->school_id_path) || filled($student->enrollment_proof_path);
        $schoolIdVerificationStatus = $hasVerificationDocument ? 'pending' : 'not_submitted';
    }

    $showVerificationModal = $schoolIdVerificationStatus !== 'approved';
    $verificationModalTitle = $schoolIdVerificationStatus === 'rejected'
        ? 'Academic Verification Required'
        : 'Academic Verification In Progress';
    $verificationModalMessage = $schoolIdVerificationStatus === 'rejected'
        ? 'Your verification document was not approved yet. Booking stays locked until your School ID or COR/COE is validated by admin.'
        : 'Your academic verification is still in process. Booking stays locked until admin validation is completed.';
@endphp

<div class="glass-card rounded-4 p-4 p-md-5 mb-4">

    {{-- Header + Search/Filter --}}
    <form method="GET" action="{{ route('student.rooms.index') }}" class="rooms-filter-shell">
        <div class="rooms-header-grid">
            <div>
                <h2 class="rooms-title">Browse Rooms</h2>
                <p class="rooms-subtitle mb-0">Search fast, then open advanced filters for budget, amenities, ratings, and occupancy mode.</p>
            </div>
            <div class="rooms-toolbar">
                <input
                    type="text"
                    name="q"
                    class="rooms-search-input"
                    placeholder="Search boarding house address..."
                    list="propertyAddressSuggestions"
                    autocomplete="off"
                    value="{{ request('q') }}"
                >
                <button
                    type="button"
                    id="toggleAdvancedFilters"
                    class="btn-filters-toggle"
                    aria-controls="advancedFiltersPanel"
                    aria-expanded="{{ ($showAdvancedFilters ?? false) ? 'true' : 'false' }}"
                >
                    <i class="bi bi-sliders"></i>
                    <span>Filters</span>
                </button>
                <a href="{{ route('student.rooms.index') }}" class="btn-filters-reset">Reset</a>
            </div>
        </div>

        <datalist id="propertyAddressSuggestions">
            @foreach(($propertyAddressSuggestions ?? collect()) as $addressSuggestion)
                <option value="{{ $addressSuggestion }}"></option>
            @endforeach
        </datalist>

        <div id="advancedFiltersPanel" class="advanced-filters-panel {{ ($showAdvancedFilters ?? false) ? '' : 'is-collapsed' }}">
            <div class="advanced-filters-grid">
                <section class="filters-card">
                    <div class="filters-card-title">
                        <i class="bi bi-cash-coin text-info"></i>
                        Price Range
                    </div>
                    <div class="price-chip-line">
                        <span class="price-chip">Min: PHP <span id="minPriceDisplay">{{ number_format((float) $minPrice, 0) }}</span></span>
                        <span class="price-chip">Max: PHP <span id="maxPriceDisplay">{{ number_format((float) $maxPrice, 0) }}</span></span>
                    </div>
                    <div class="price-caption-row">
                        <span>Minimum</span>
                        <span>Maximum</span>
                    </div>
                    <div id="sharedPriceSlider" class="shared-range-slider" data-range-slider>
                        <div class="range-track">
                            <div id="priceRangeFill" class="range-fill"></div>
                        </div>
                        <input
                            id="minPriceRange"
                            class="range-handle"
                            type="range"
                            name="min_price"
                            min="{{ (int) $priceBoundsMin }}"
                            max="{{ (int) $priceBoundsMax }}"
                            step="100"
                            value="{{ (int) round((float) $minPrice) }}"
                        >
                        <input
                            id="maxPriceRange"
                            class="range-handle"
                            type="range"
                            name="max_price"
                            min="{{ (int) $priceBoundsMin }}"
                            max="{{ (int) $priceBoundsMax }}"
                            step="100"
                            value="{{ (int) round((float) $maxPrice) }}"
                        >
                        <span id="minPriceIndicator" class="range-indicator min">PHP {{ number_format((float) $minPrice, 0) }}</span>
                        <span id="maxPriceIndicator" class="range-indicator max">PHP {{ number_format((float) $maxPrice, 0) }}</span>
                    </div>
                </section>

                <section class="filters-card">
                    <div class="filters-card-title">
                        <i class="bi bi-stars text-success"></i>
                        Amenities
                    </div>
                    <div class="amenity-grid">
                        @foreach(($amenityOptions ?? []) as $amenityKey => $amenityLabel)
                            <label class="choice-item">
                                <input
                                    type="checkbox"
                                    name="amenities[]"
                                    value="{{ $amenityKey }}"
                                    @checked(($selectedAmenities ?? collect())->contains($amenityKey))
                                >
                                <span>{{ $amenityLabel }}</span>
                            </label>
                        @endforeach
                    </div>
                </section>

                <section class="filters-card">
                    <div class="filters-card-title">
                        <i class="bi bi-star text-warning"></i>
                        Ratings
                    </div>
                    <div class="rating-stack">
                        <label class="choice-item">
                            <input type="radio" name="rating" value="any" @checked(($ratingFilter ?? 'any') === 'any')>
                            <span>Any rating</span>
                        </label>
                        <label class="choice-item">
                            <input type="radio" name="rating" value="4_up" @checked(($ratingFilter ?? 'any') === '4_up')>
                            <span>4.0 and up</span>
                        </label>
                        <label class="choice-item">
                            <input type="radio" name="rating" value="3_up" @checked(($ratingFilter ?? 'any') === '3_up')>
                            <span>3.0 and up</span>
                        </label>
                        <label class="choice-item">
                            <input type="radio" name="rating" value="unrated" @checked(($ratingFilter ?? 'any') === 'unrated')>
                            <span>Unrated only</span>
                        </label>
                    </div>
                </section>

                <section class="filters-card occupancy-card">
                    <div class="filters-card-title">
                        <i class="bi bi-door-open text-primary"></i>
                        Occupancy Mode
                    </div>
                    <div class="occupancy-stack">
                        <label class="choice-item">
                            <input type="radio" name="occupancy" value="all" @checked(($occupancyFilter ?? 'all') === 'all')>
                            <span>All modes</span>
                        </label>
                        <label class="choice-item">
                            <input type="radio" name="occupancy" value="per_room" @checked(($occupancyFilter ?? 'all') === 'per_room')>
                            <span>Per room</span>
                        </label>
                        <label class="choice-item">
                            <input type="radio" name="occupancy" value="per_bed" @checked(($occupancyFilter ?? 'all') === 'per_bed')>
                            <span>Bed spacer</span>
                        </label>
                    </div>
                </section>
            </div>
        </div>
    </form>

    {{-- Property filter notice --}}
    <div id="propertyRoomFilterNotice" class="alert alert-light border rounded-4 d-none mb-4 py-2">
        Showing rooms for <strong id="propertyRoomFilterName"></strong>.
        <button type="button" id="clearPropertyRoomFilter" class="btn btn-sm btn-outline-secondary ms-2">Clear</button>
    </div>

    {{-- ✨ Recommended Rooms --}}
    @if($recommendedRooms->isNotEmpty())
    <div class="mb-5" id="recommendedSection">
        <div class="d-flex align-items-center gap-2 mb-3">
            <h5 class="fw-bold mb-0"><i class="bi bi-stars text-success me-1"></i>Recommended For You</h5>
            <span class="badge rounded-pill text-bg-success" id="recommendedCountBadge">{{ $recommendedRooms->count() }}</span>
        </div>
        <div class="recommended-carousel" data-room-carousel>
            <button type="button" class="recommended-arrow prev" data-carousel-prev aria-label="Previous recommended room">
                <i class="bi bi-chevron-left"></i>
            </button>
            <button type="button" class="recommended-arrow next" data-carousel-next aria-label="Next recommended room">
                <i class="bi bi-chevron-right"></i>
            </button>

            <div class="recommended-track" data-carousel-track>
            @foreach($recommendedRooms as $room)
            @php
                $rImg = $room->image_path ?: ($room->property->image_path ?? null);
                $rInclusions = collect(preg_split('/[,\n;]+/', $room->inclusions ?? ''))->map('trim')->filter();
                $buildingAmenityKeys = collect((array) ($room->property->building_inclusions ?? []))
                    ->map(fn ($key) => trim((string) $key))
                    ->filter()
                    ->unique()
                    ->values();
                $propertyInclusions = collect((array) ($room->property->building_inclusions ?? []))
                    ->map(fn ($key) => ($amenityOptions ?? [])[$key] ?? trim((string) $key))
                    ->filter();
                $availableSlots = $room->getAvailableSlots();
                $occupancy = $room->getOccupancyDisplay();
                $isFullCapacity = $availableSlots === 0;
                $pricingModel = method_exists($room, 'resolvePricingModel') ? $room->resolvePricingModel() : 'hybrid';
                $listingPricingMode = method_exists($room, 'resolveListingPricingMode')
                    ? $room->resolveListingPricingMode()
                    : ($pricingModel === 'hybrid' ? 'both' : $pricingModel);
                $pricingModelLabel = match ($listingPricingMode) {
                    'per_bed' => $pricingModel === 'hybrid' ? 'Hybrid (Bedspacer Active)' : 'For Bedspacing',
                    'per_room' => $pricingModel === 'hybrid' ? 'Hybrid (Solo Occupancy)' : 'Per Room',
                    default => 'Hybrid',
                };
                $effectivePricePerRoom = method_exists($room, 'effectivePricePerRoom')
                    ? (float) $room->effectivePricePerRoom()
                    : (float) $room->price;
                $effectivePricePerBed = method_exists($room, 'effectivePricePerBed')
                    ? (float) $room->effectivePricePerBed()
                    : ((float) $room->price / max(1, (int) $room->capacity));
                $pricingCadenceLabel = match ($listingPricingMode) {
                    'per_bed' => 'Monthly per bed',
                    'per_room' => 'Monthly per room',
                    default => 'Monthly per room and per bed',
                };
                $primaryPrice = match ($listingPricingMode) {
                    'per_bed' => $effectivePricePerBed,
                    default => $effectivePricePerRoom,
                };
                $occupiedCount = max(0, (int) $room->capacity - $availableSlots);
                $isMaintenance = (string) $room->status === 'maintenance';
                $slotSummaryClass = $isMaintenance ? 'is-maintenance' : ($isFullCapacity ? 'is-full' : '');
                $slotProgressClass = $isMaintenance ? 'is-maintenance' : ($isFullCapacity ? 'is-full' : '');
                $slotPercent = $isMaintenance ? 0 : (int) round((max(0, $availableSlots) / max(1, (int) $room->capacity)) * 100);
                $slotSummaryText = $isMaintenance
                    ? 'Maintenance mode: bookings are temporarily unavailable.'
                    : ($isFullCapacity
                        ? 'Fully occupied now. New booking is unavailable.'
                        : $availableSlots . ' of ' . $room->capacity . ' slots are available.');
                $inclusionItems = $propertyInclusions
                    ->merge($rInclusions)
                    ->map(fn ($value) => trim((string) $value))
                    ->filter()
                    ->unique(fn ($value) => strtolower($value))
                    ->values();
                $visibleInclusions = $inclusionItems->take(6);
                $hiddenInclusionCount = max(0, $inclusionItems->count() - $visibleInclusions->count());
            @endphp
            <div
                class="recommended-item"
                data-room-property-id="{{ $room->property_id }}"
                data-room-filter-item
                data-room-address="{{ Str::lower((string) ($room->property->address ?? '')) }}"
                data-room-pricing-model="{{ $pricingModel }}"
                data-room-listing-mode="{{ $listingPricingMode }}"
                data-room-rating="{{ (float) ($room->feedbacks_avg_rating ?? 0) }}"
                data-room-ratings-count="{{ (int) ($room->feedbacks_count ?? 0) }}"
                data-room-price-room="{{ (float) $effectivePricePerRoom }}"
                data-room-price-bed="{{ (float) $effectivePricePerBed }}"
                data-room-amenities="{{ $buildingAmenityKeys->implode('|') }}"
                data-carousel-item
            >
                <a href="{{ route('student.rooms.show', $room->id) }}" class="text-decoration-none d-block h-100">
                    <div class="room-browse-card h-100 {{ $isFullCapacity ? 'room-browse-card-dimmed' : '' }}">
                        <div class="room-browse-photo">
                            @if($rImg)
                                <img src="{{ asset('storage/'.$rImg) }}" alt="Room" loading="lazy">
                            @else
                                <div class="room-browse-nophoto"><i class="bi bi-building fs-2 text-muted"></i></div>
                            @endif
                            <span class="room-browse-badge-top"><i class="bi bi-star-fill me-1" style="font-size:.65rem;"></i>Recommended</span>
                            @if($room->status === 'maintenance')
                                <span class="room-browse-badge-status">Maintenance</span>
                            @elseif($isFullCapacity)
                                <span class="room-browse-badge-status">Full ({{ $occupancy }})</span>
                            @endif
                        </div>
                        <div class="room-browse-body">
                            <div class="room-card-header">
                                <h6 class="room-property-title">{{ $room->property->name }}</h6>
                                @if($room->updated_at && $room->updated_at->gte($newThreshold))
                                    <span class="badge text-bg-primary shrink-0" style="font-size:.62rem;">New</span>
                                @endif
                            </div>
                            <div class="room-address-line">
                                <i class="bi bi-geo-alt-fill text-danger"></i>{{ Str::limit($room->property->address, 46) }}
                            </div>
                            <div class="room-tags-row mt-1">
                                <span class="pricing-type-pill">{{ $pricingModelLabel }}</span>
                                <span class="room-rating-chip">
                                    @if(($room->feedbacks_count ?? 0) > 0)
                                        @php $avg = (float) $room->feedbacks_avg_rating; @endphp
                                        <i class="bi bi-star-fill" style="color:#f59e0b;"></i>
                                        {{ number_format($avg, 1) }} ({{ $room->feedbacks_count }})
                                    @else
                                        <i class="bi bi-star"></i>
                                        No ratings yet
                                    @endif
                                </span>
                            </div>
                            <div class="room-main-grid">
                                <div>
                                    <div class="room-identity-title">{{ $room->room_number }}</div>
                                    <div class="room-occupancy-line">
                                        <i class="bi bi-people"></i>
                                        Occupancy {{ $occupancy }} ({{ $occupiedCount }} of {{ $room->capacity }})
                                    </div>
                                    <div class="room-slot-summary {{ $slotSummaryClass }}">{{ $slotSummaryText }}</div>
                                    <div class="room-slot-progress {{ $slotProgressClass }}">
                                        <span style="width: {{ $slotPercent }}%"></span>
                                    </div>
                                </div>
                                <div class="room-price-emphasis {{ $isFullCapacity ? 'is-muted' : '' }}">
                                    <div class="room-price-main">
                                        <span class="room-price-currency">PHP</span>{{ number_format($primaryPrice, 0) }}<span class="room-price-suffix">/mo</span>
                                    </div>
                                    <div class="room-price-meta">{{ $pricingCadenceLabel }}</div>
                                    @if($listingPricingMode === 'both')
                                        <div class="room-price-subsplit">Room PHP {{ number_format($effectivePricePerRoom, 0) }} - Bed PHP {{ number_format($effectivePricePerBed, 0) }}</div>
                                    @endif
                                </div>
                            </div>
                            @if($visibleInclusions->isNotEmpty())
                                <div class="room-chip-wrap">
                                    @foreach($visibleInclusions as $inc)<span class="room-inc-chip">{{ $inc }}</span>@endforeach
                                    @if($hiddenInclusionCount > 0)
                                        <span class="room-inc-chip">+{{ $hiddenInclusionCount }} more</span>
                                    @endif
                                </div>
                            @endif
                            @if($room->property->latitude && $room->property->longitude)
                                <div class="mt-2">
                                    <span class="badge text-bg-light border" id="distance-room-{{ $room->id }}" style="font-size:.7rem;">
                                        <i class="bi bi-signpost-2 text-primary me-1"></i>Calculating...
                                    </span>
                                </div>
                            @endif
                        </div>
                        <div class="room-browse-footer">
                            <span class="btn btn-sm {{ $isFullCapacity ? 'btn-outline-secondary' : 'btn-brand' }} w-100 rounded-pill">View Details <i class="bi bi-arrow-right ms-1"></i></span>
                        </div>
                    </div>
                </a>
            </div>
            @endforeach

            </div>

            <div class="recommended-meta">
                <span class="recommended-hint" data-carousel-hint><i class="bi bi-arrows-angle-expand"></i>Swipe or use arrows to see more rooms</span>
                <span class="recommended-progress" data-carousel-progress>1 / {{ $recommendedRooms->count() }}</span>
            </div>
        </div>
    </div>
    @endif

    {{-- All Rooms --}}
    <div>
        @php
            $allRoomsByProperty = $allRooms->groupBy('property_id');
        @endphp
        <div class="d-flex align-items-center gap-2 mb-3">
            <h5 class="fw-bold mb-0">All Rooms</h5>
            <span class="badge rounded-pill text-bg-light border">{{ $allRooms->filter(fn($r) => $r->hasAvailableSlots())->count() }} available</span>
        </div>
        <div class="d-flex flex-column gap-3">
            @forelse($allRoomsByProperty as $propertyId => $propertyRooms)
            @php
                $propertyModel = optional($propertyRooms->first())->property;
                $propertyName = $propertyModel?->name ?? ('Property #' . $propertyId);
                $propertyAddress = $propertyModel?->address ?? 'Address not available';
                $propertyAvailableRooms = $propertyRooms->filter(fn ($room) => $room->hasAvailableSlots())->count();
                $propertyTotalRooms = $propertyRooms->count();
                $sortedPropertyRooms = $propertyRooms
                    ->sort(function ($a, $b) {
                        $availabilityRank = ((int) $b->hasAvailableSlots()) <=> ((int) $a->hasAvailableSlots());

                        if ($availabilityRank !== 0) {
                            return $availabilityRank;
                        }

                        return strnatcasecmp((string) $a->room_number, (string) $b->room_number);
                    })
                    ->values();

                $propertyRatingsCount = (int) ($propertyModel?->ratings_count ?? 0);
                $propertyAverageRating = $propertyModel?->average_rating !== null
                    ? (float) $propertyModel->average_rating
                    : null;

                if (($propertyAverageRating === null || $propertyRatingsCount === 0) && $propertyTotalRooms > 0) {
                    $ratingWeightedSum = $propertyRooms->sum(function ($room) {
                        $count = (int) ($room->feedbacks_count ?? 0);
                        $avg = (float) ($room->feedbacks_avg_rating ?? 0);
                        return $count * $avg;
                    });

                    $roomRatingsCount = (int) $propertyRooms->sum(fn ($room) => (int) ($room->feedbacks_count ?? 0));

                    if ($roomRatingsCount > 0) {
                        $propertyRatingsCount = $roomRatingsCount;
                        $propertyAverageRating = $ratingWeightedSum / $roomRatingsCount;
                    }
                }
            @endphp
            <div class="property-room-group" data-room-property-id="{{ $propertyId }}">
                <div class="property-group-head">
                    <div>
                        <h6 class="property-group-title">{{ $propertyName }}</h6>
                        <div class="property-group-address">
                            <i class="bi bi-geo-alt-fill text-danger"></i>{{ Str::limit($propertyAddress, 64) }}
                        </div>
                    </div>
                    <div class="property-group-meta">
                        <span class="property-meta-chip"><i class="bi bi-building"></i>{{ $propertyTotalRooms }} rooms</span>
                        <span class="property-meta-chip available"><i class="bi bi-door-open"></i>{{ $propertyAvailableRooms }} available</span>
                        <span class="property-meta-chip rating">
                            @if(($propertyRatingsCount ?? 0) > 0)
                                <i class="bi bi-star-fill"></i>{{ number_format((float) $propertyAverageRating, 1) }} ({{ $propertyRatingsCount }})
                            @else
                                <i class="bi bi-star"></i>No ratings yet
                            @endif
                        </span>
                    </div>
                </div>

                <div class="recommended-carousel" data-room-carousel>
                    <button type="button" class="recommended-arrow prev" data-carousel-prev aria-label="Previous room in {{ $propertyName }}">
                        <i class="bi bi-chevron-left"></i>
                    </button>
                    <button type="button" class="recommended-arrow next" data-carousel-next aria-label="Next room in {{ $propertyName }}">
                        <i class="bi bi-chevron-right"></i>
                    </button>

                    <div class="recommended-track" data-carousel-track>
                    @foreach($sortedPropertyRooms as $r)
                    @php
                        $rImg2 = $r->image_path ?: ($r->property->image_path ?? null);
                        $rInc2 = collect(preg_split('/[,\n;]+/', $r->inclusions ?? ''))->map('trim')->filter();
                        $buildingAmenityKeys2 = collect((array) ($r->property->building_inclusions ?? []))
                            ->map(fn ($key) => trim((string) $key))
                            ->filter()
                            ->unique()
                            ->values();
                        $propertyInclusions2 = collect((array) ($r->property->building_inclusions ?? []))
                            ->map(fn ($key) => ($amenityOptions ?? [])[$key] ?? trim((string) $key))
                            ->filter();
                        $availableSlots2 = $r->getAvailableSlots();
                        $occupancy2 = $r->getOccupancyDisplay();
                        $isFullCapacity2 = $availableSlots2 === 0;
                        $pricingModel2 = method_exists($r, 'resolvePricingModel') ? $r->resolvePricingModel() : 'hybrid';
                        $listingPricingMode2 = method_exists($r, 'resolveListingPricingMode')
                            ? $r->resolveListingPricingMode()
                            : ($pricingModel2 === 'hybrid' ? 'both' : $pricingModel2);
                        $pricingModelLabel2 = match ($listingPricingMode2) {
                            'per_bed' => $pricingModel2 === 'hybrid' ? 'Hybrid (Bedspacer Active)' : 'For Bedspacing',
                            'per_room' => $pricingModel2 === 'hybrid' ? 'Hybrid (Solo Occupancy)' : 'Per Room',
                            default => 'Hybrid',
                        };
                        $effectivePricePerRoom2 = method_exists($r, 'effectivePricePerRoom')
                            ? (float) $r->effectivePricePerRoom()
                            : (float) $r->price;
                        $effectivePricePerBed2 = method_exists($r, 'effectivePricePerBed')
                            ? (float) $r->effectivePricePerBed()
                            : ((float) $r->price / max(1, (int) $r->capacity));
                        $pricingCadenceLabel2 = match ($listingPricingMode2) {
                            'per_bed' => 'Monthly per bed',
                            'per_room' => 'Monthly per room',
                            default => 'Monthly per room and per bed',
                        };
                        $primaryPrice2 = match ($listingPricingMode2) {
                            'per_bed' => $effectivePricePerBed2,
                            default => $effectivePricePerRoom2,
                        };
                        $occupiedCount2 = max(0, (int) $r->capacity - $availableSlots2);
                        $isMaintenance2 = (string) $r->status === 'maintenance';
                        $slotSummaryClass2 = $isMaintenance2 ? 'is-maintenance' : ($isFullCapacity2 ? 'is-full' : '');
                        $slotProgressClass2 = $isMaintenance2 ? 'is-maintenance' : ($isFullCapacity2 ? 'is-full' : '');
                        $slotPercent2 = $isMaintenance2 ? 0 : (int) round((max(0, $availableSlots2) / max(1, (int) $r->capacity)) * 100);
                        $slotSummaryText2 = $isMaintenance2
                            ? 'Maintenance mode: bookings are temporarily unavailable.'
                            : ($isFullCapacity2
                                ? 'Fully occupied now. New booking is unavailable.'
                                : $availableSlots2 . ' of ' . $r->capacity . ' slots are available.');
                        $inclusionItems2 = $propertyInclusions2
                            ->merge($rInc2)
                            ->map(fn ($value) => trim((string) $value))
                            ->filter()
                            ->unique(fn ($value) => strtolower($value))
                            ->values();
                        $visibleInclusions2 = $inclusionItems2->take(6);
                        $hiddenInclusionCount2 = max(0, $inclusionItems2->count() - $visibleInclusions2->count());
                    @endphp
                    <div
                        class="recommended-item"
                        data-room-filter-item
                        data-room-property-id="{{ $propertyId }}"
                        data-room-address="{{ Str::lower((string) ($r->property->address ?? '')) }}"
                        data-room-pricing-model="{{ $pricingModel2 }}"
                        data-room-listing-mode="{{ $listingPricingMode2 }}"
                        data-room-rating="{{ (float) ($r->feedbacks_avg_rating ?? 0) }}"
                        data-room-ratings-count="{{ (int) ($r->feedbacks_count ?? 0) }}"
                        data-room-price-room="{{ (float) $effectivePricePerRoom2 }}"
                        data-room-price-bed="{{ (float) $effectivePricePerBed2 }}"
                        data-room-amenities="{{ $buildingAmenityKeys2->implode('|') }}"
                        data-carousel-item
                    >
                        <a href="{{ route('student.rooms.show', $r->id) }}" class="text-decoration-none d-block h-100">
                            <div class="room-browse-card h-100 {{ $isFullCapacity2 || $r->status === 'maintenance' ? 'room-browse-card-dimmed' : '' }}">
                                <div class="room-browse-photo">
                                    @if($rImg2)
                                        <img src="{{ asset('storage/'.$rImg2) }}" alt="Room" loading="lazy">
                                    @else
                                        <div class="room-browse-nophoto"><i class="bi bi-building fs-2 text-muted"></i></div>
                                    @endif
                                    @if($r->status === 'maintenance')
                                        <span class="room-browse-badge-status">Maintenance</span>
                                    @elseif($isFullCapacity2)
                                        <span class="room-browse-badge-status">Full ({{ $occupancy2 }})</span>
                                    @endif
                                </div>
                                <div class="room-browse-body">
                                    <div class="room-card-header">
                                        <h6 class="room-property-title">{{ $r->property->name }}</h6>
                                        @if($r->updated_at && $r->updated_at->gte($newThreshold))
                                            <span class="badge text-bg-primary shrink-0" style="font-size:.62rem;">New</span>
                                        @endif
                                    </div>
                                    <div class="room-address-line">
                                        <i class="bi bi-geo-alt-fill text-danger"></i>{{ Str::limit($r->property->address, 46) }}
                                    </div>
                                    <div class="room-tags-row mt-1">
                                        <span class="pricing-type-pill">{{ $pricingModelLabel2 }}</span>
                                        <span class="room-rating-chip">
                                            @if(($r->feedbacks_count ?? 0) > 0)
                                                @php $avg = (float) $r->feedbacks_avg_rating; @endphp
                                                <i class="bi bi-star-fill" style="color:#f59e0b;"></i>
                                                {{ number_format($avg, 1) }} ({{ $r->feedbacks_count }})
                                            @else
                                                <i class="bi bi-star"></i>
                                                No ratings yet
                                            @endif
                                        </span>
                                    </div>
                                    <div class="room-main-grid">
                                        <div>
                                            <div class="room-identity-title">{{ $r->room_number }}</div>
                                            <div class="room-occupancy-line">
                                                <i class="bi bi-people"></i>
                                                Occupancy {{ $occupancy2 }} ({{ $occupiedCount2 }} of {{ $r->capacity }})
                                            </div>
                                            <div class="room-slot-summary {{ $slotSummaryClass2 }}">{{ $slotSummaryText2 }}</div>
                                            <div class="room-slot-progress {{ $slotProgressClass2 }}">
                                                <span style="width: {{ $slotPercent2 }}%"></span>
                                            </div>
                                        </div>
                                        <div class="room-price-emphasis {{ $isFullCapacity2 || $r->status === 'maintenance' ? 'is-muted' : '' }}">
                                            <div class="room-price-main">
                                                <span class="room-price-currency">PHP</span>{{ number_format($primaryPrice2, 0) }}<span class="room-price-suffix">/mo</span>
                                            </div>
                                            <div class="room-price-meta">{{ $pricingCadenceLabel2 }}</div>
                                            @if($listingPricingMode2 === 'both')
                                                <div class="room-price-subsplit">Room PHP {{ number_format($effectivePricePerRoom2, 0) }} - Bed PHP {{ number_format($effectivePricePerBed2, 0) }}</div>
                                            @endif
                                        </div>
                                    </div>
                                    @if($visibleInclusions2->isNotEmpty())
                                        <div class="room-chip-wrap">
                                            @foreach($visibleInclusions2 as $inc)<span class="room-inc-chip">{{ $inc }}</span>@endforeach
                                            @if($hiddenInclusionCount2 > 0)
                                                <span class="room-inc-chip">+{{ $hiddenInclusionCount2 }} more</span>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                                <div class="room-browse-footer">
                                    <span class="btn btn-sm w-100 rounded-pill {{ $isFullCapacity2 || $r->status === 'maintenance' ? 'btn-outline-secondary' : 'btn-brand' }}">View Details <i class="bi bi-arrow-right ms-1"></i></span>
                                </div>
                            </div>
                        </a>
                    </div>
                    @endforeach

                    </div>

                    <div class="recommended-meta">
                        <span class="recommended-hint" data-carousel-hint><i class="bi bi-arrows-angle-expand"></i>Swipe or use arrows for more rooms in this property</span>
                        <span class="recommended-progress" data-carousel-progress>1 / {{ $propertyTotalRooms }}</span>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-12">
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-search fs-1 d-block mb-3"></i>
                    No rooms match your filters. Try adjusting the search.
                </div>
            </div>
            @endforelse
        </div>
    </div>
</div>

@if($showVerificationModal)
<div class="modal fade" id="studentVerificationPendingModal" tabindex="-1" aria-labelledby="studentVerificationPendingModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title" id="studentVerificationPendingModalLabel">
                    <i class="bi bi-shield-exclamation text-warning me-2"></i>{{ $verificationModalTitle }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-2">
                <p class="mb-2">{{ $verificationModalMessage }}</p>
                <p class="small text-muted mb-0">You can continue browsing rooms and properties while waiting for validation.</p>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-outline-secondary rounded-pill px-3" data-bs-dismiss="modal">Got it</button>
            </div>
        </div>
    </div>
</div>
@endif

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const verificationModalEl = document.getElementById('studentVerificationPendingModal');
        if (verificationModalEl && window.bootstrap) {
            const verificationModal = bootstrap.Modal.getOrCreateInstance(verificationModalEl);
            verificationModal.show();
        }

        const filterNoticeEl = document.getElementById('propertyRoomFilterNotice');
        const filterNameEl = document.getElementById('propertyRoomFilterName');
        const clearFilterBtn = document.getElementById('clearPropertyRoomFilter');

        const advancedFiltersPanel = document.getElementById('advancedFiltersPanel');
        const toggleAdvancedFiltersBtn = document.getElementById('toggleAdvancedFilters');
        const sharedPriceSliderEl = document.getElementById('sharedPriceSlider');
        const minPriceRangeEl = document.getElementById('minPriceRange');
        const maxPriceRangeEl = document.getElementById('maxPriceRange');
        const priceRangeFillEl = document.getElementById('priceRangeFill');
        const minPriceDisplayEl = document.getElementById('minPriceDisplay');
        const maxPriceDisplayEl = document.getElementById('maxPriceDisplay');
        const minPriceIndicatorEl = document.getElementById('minPriceIndicator');
        const maxPriceIndicatorEl = document.getElementById('maxPriceIndicator');
        const filterFormEl = document.querySelector('.rooms-filter-shell');
        const searchInputEl = document.querySelector('input[name="q"]');
        const amenityInputs = Array.from(document.querySelectorAll('input[name="amenities[]"]'));
        const ratingInputs = Array.from(document.querySelectorAll('input[name="rating"]'));
        const occupancyInputs = Array.from(document.querySelectorAll('input[name="occupancy"]'));
        const roomFilterItems = Array.from(document.querySelectorAll('[data-room-filter-item]'));
        const propertyGroupEls = Array.from(document.querySelectorAll('.property-room-group'));
        const recommendedSectionEl = document.getElementById('recommendedSection');
        const recommendedCountBadgeEl = document.getElementById('recommendedCountBadge');

        let activePropertyFilterId = '';

        const formatPeso = (value) => Number(value || 0).toLocaleString('en-PH');

        const syncPriceRangeState = (source) => {
            if (!minPriceRangeEl || !maxPriceRangeEl) {
                return;
            }

            let minValue = Number(minPriceRangeEl.value || 0);
            let maxValue = Number(maxPriceRangeEl.value || 0);
            const rangeFloor = Number(minPriceRangeEl.min || 0);
            const rangeCeiling = Number(minPriceRangeEl.max || 0);

            if (minValue > maxValue) {
                if (source === 'min') {
                    maxValue = minValue;
                    maxPriceRangeEl.value = String(maxValue);
                } else {
                    minValue = maxValue;
                    minPriceRangeEl.value = String(minValue);
                }
            }

            if (minPriceDisplayEl) {
                minPriceDisplayEl.textContent = formatPeso(minValue);
            }

            if (maxPriceDisplayEl) {
                maxPriceDisplayEl.textContent = formatPeso(maxValue);
            }

            if (!sharedPriceSliderEl || !Number.isFinite(rangeCeiling - rangeFloor) || rangeCeiling === rangeFloor) {
                return;
            }

            const toPercent = (value) => ((value - rangeFloor) / (rangeCeiling - rangeFloor)) * 100;
            const minPercent = Math.min(100, Math.max(0, toPercent(minValue)));
            const maxPercent = Math.min(100, Math.max(0, toPercent(maxValue)));

            if (priceRangeFillEl) {
                priceRangeFillEl.style.left = `${minPercent}%`;
                priceRangeFillEl.style.width = `${Math.max(0, maxPercent - minPercent)}%`;
            }

            if (minPriceIndicatorEl) {
                minPriceIndicatorEl.textContent = `PHP ${formatPeso(minValue)}`;
                minPriceIndicatorEl.style.left = `${minPercent}%`;
            }

            if (maxPriceIndicatorEl) {
                maxPriceIndicatorEl.textContent = `PHP ${formatPeso(maxValue)}`;
                maxPriceIndicatorEl.style.left = `${maxPercent}%`;
            }

            sharedPriceSliderEl.classList.toggle('tight-gap', Math.abs(maxPercent - minPercent) < 11);
        };

        const setPriceIndicatorVisible = (handle, visible) => {
            if (!sharedPriceSliderEl) {
                return;
            }

            sharedPriceSliderEl.classList.toggle('show-min', handle === 'min' && visible);
            sharedPriceSliderEl.classList.toggle('show-max', handle === 'max' && visible);
        };

        const attachPriceHandleEvents = (inputEl, handle) => {
            if (!inputEl) {
                return;
            }

            inputEl.addEventListener('input', () => {
                setPriceIndicatorVisible(handle, true);
                syncPriceRangeState(handle);
            });

            inputEl.addEventListener('focus', () => setPriceIndicatorVisible(handle, true));
            inputEl.addEventListener('mousedown', () => setPriceIndicatorVisible(handle, true));
            inputEl.addEventListener('touchstart', () => setPriceIndicatorVisible(handle, true), { passive: true });

            inputEl.addEventListener('change', () => setPriceIndicatorVisible(handle, false));
            inputEl.addEventListener('blur', () => setPriceIndicatorVisible(handle, false));
            inputEl.addEventListener('mouseup', () => setPriceIndicatorVisible(handle, false));
            inputEl.addEventListener('touchend', () => setPriceIndicatorVisible(handle, false), { passive: true });
        };

        if (toggleAdvancedFiltersBtn && advancedFiltersPanel) {
            toggleAdvancedFiltersBtn.addEventListener('click', () => {
                const isCollapsed = advancedFiltersPanel.classList.toggle('is-collapsed');
                toggleAdvancedFiltersBtn.setAttribute('aria-expanded', isCollapsed ? 'false' : 'true');
            });
        }

        filterFormEl?.addEventListener('submit', (event) => {
            event.preventDefault();
        });

        attachPriceHandleEvents(minPriceRangeEl, 'min');
        attachPriceHandleEvents(maxPriceRangeEl, 'max');
        syncPriceRangeState('max');

        const initRoomCarousel = (carouselEl) => {
            if (!carouselEl) {
                return null;
            }

            const trackEl = carouselEl.querySelector('[data-carousel-track]');
            const prevBtn = carouselEl.querySelector('[data-carousel-prev]');
            const nextBtn = carouselEl.querySelector('[data-carousel-next]');
            const progressEl = carouselEl.querySelector('[data-carousel-progress]');
            const hintEl = carouselEl.querySelector('[data-carousel-hint]');

            if (!trackEl) {
                return null;
            }

            let scrollDebounce = null;

            const clamp = (value, min, max) => Math.min(max, Math.max(min, value));

            const visibleItems = () => Array.from(trackEl.querySelectorAll('[data-carousel-item], .recommended-item'))
                .filter((item) => item.style.display !== 'none');

            const getStep = () => {
                const first = visibleItems()[0];
                if (!first) {
                    return 0;
                }

                const styles = window.getComputedStyle(trackEl);
                const gap = parseFloat(styles.columnGap || styles.gap || '0') || 0;
                return first.getBoundingClientRect().width + gap;
            };

            const getMaxScrollLeft = () => Math.max(0, trackEl.scrollWidth - trackEl.clientWidth);

            const totalItems = () => visibleItems().length;

            const getCurrentIndex = () => {
                const total = totalItems();
                const maxScroll = getMaxScrollLeft();
                if (!total || total === 1 || maxScroll <= 0) {
                    return 0;
                }

                const ratio = clamp(trackEl.scrollLeft / maxScroll, 0, 1);
                return clamp(Math.round(ratio * (total - 1)), 0, total - 1);
            };

            const renderState = () => {
                const total = totalItems();
                const current = getCurrentIndex();
                const maxScroll = getMaxScrollLeft();
                const hasMore = total > 1 && maxScroll > 2;
                const canGoPrev = trackEl.scrollLeft > 2;
                const canGoNext = trackEl.scrollLeft < (maxScroll - 2);

                carouselEl.classList.toggle('at-start', !hasMore || !canGoPrev);
                carouselEl.classList.toggle('at-end', !hasMore || !canGoNext);

                if (progressEl) {
                    progressEl.textContent = total ? `${current + 1} / ${total}` : '0 / 0';
                }

                if (prevBtn) {
                    prevBtn.disabled = !canGoPrev;
                    prevBtn.classList.toggle('d-none', !hasMore || !canGoPrev);
                }

                if (nextBtn) {
                    nextBtn.disabled = !canGoNext;
                    nextBtn.classList.toggle('d-none', !hasMore || !canGoNext);
                }

                if (hintEl) {
                    hintEl.classList.toggle('d-none', !hasMore);
                }
            };

            const scrollByStep = (direction, behavior = 'smooth') => {
                const step = getStep();
                const maxScroll = getMaxScrollLeft();
                if (!step || maxScroll <= 0) {
                    renderState();
                    return;
                }

                const target = clamp(trackEl.scrollLeft + (step * direction), 0, maxScroll);
                trackEl.scrollTo({
                    left: target,
                    behavior,
                });

                window.setTimeout(renderState, behavior === 'smooth' ? 260 : 0);
            };

            const normalizeScrollPosition = (behavior = 'auto') => {
                const maxScroll = getMaxScrollLeft();
                const safeLeft = clamp(trackEl.scrollLeft, 0, maxScroll);
                trackEl.scrollTo({ left: safeLeft, behavior });
                renderState();
            };

            prevBtn?.addEventListener('click', () => {
                scrollByStep(-1);
            });

            nextBtn?.addEventListener('click', () => {
                scrollByStep(1);
            });

            trackEl.addEventListener('scroll', () => {
                if (scrollDebounce) {
                    window.clearTimeout(scrollDebounce);
                }

                scrollDebounce = window.setTimeout(renderState, 90);
            }, { passive: true });

            // Let vertical mouse wheel naturally pan the horizontal carousel.
            trackEl.addEventListener('wheel', (event) => {
                if (Math.abs(event.deltaY) > Math.abs(event.deltaX)) {
                    event.preventDefault();
                    trackEl.scrollLeft += event.deltaY;
                }
            }, { passive: false });

            window.addEventListener('resize', () => {
                normalizeScrollPosition('auto');
            });

            renderState();

            return {
                refresh: () => {
                    if (carouselEl.offsetParent === null && getComputedStyle(carouselEl).display === 'none') {
                        return;
                    }

                    normalizeScrollPosition('auto');
                },
            };
        };

        const carouselControllers = Array.from(document.querySelectorAll('[data-room-carousel]'))
            .map((carouselEl) => initRoomCarousel(carouselEl))
            .filter(Boolean);

        const normalizeText = (value) => String(value || '')
            .toLowerCase()
            .trim()
            .replace(/\s+/g, ' ');

        const getSelectedRating = () => ratingInputs.find((input) => input.checked)?.value || 'any';
        const getSelectedOccupancy = () => occupancyInputs.find((input) => input.checked)?.value || 'all';

        const getComparablePrice = (itemEl, occupancyFilter) => {
            const pricingModel = String(itemEl.dataset.roomPricingModel || 'per_room');
            const listingMode = String(itemEl.dataset.roomListingMode || 'per_room');
            const roomPrice = Number(itemEl.dataset.roomPriceRoom || 0);
            const bedPrice = Number(itemEl.dataset.roomPriceBed || 0);

            if (pricingModel === 'hybrid' && occupancyFilter !== 'all') {
                return Math.min(roomPrice, bedPrice);
            }

            if (occupancyFilter === 'per_bed') {
                return bedPrice;
            }

            if (occupancyFilter === 'per_room') {
                return roomPrice;
            }

            if (listingMode === 'per_bed') {
                return bedPrice;
            }

            if (listingMode === 'per_room') {
                return roomPrice;
            }

            return Math.min(roomPrice, bedPrice);
        };

        const matchesOccupancy = (itemEl, occupancyFilter) => {
            if (occupancyFilter === 'all') {
                return true;
            }

            const pricingModel = String(itemEl.dataset.roomPricingModel || 'per_room');
            if (pricingModel === 'hybrid') {
                return true;
            }

            const listingMode = String(itemEl.dataset.roomListingMode || 'per_room');

            if (occupancyFilter === 'per_room') {
                return listingMode === 'per_room' || listingMode === 'both';
            }

            return listingMode === 'per_bed' || listingMode === 'both';
        };

        const matchesRating = (itemEl, ratingFilter) => {
            const ratingsCount = Number(itemEl.dataset.roomRatingsCount || 0);
            const rating = Number(itemEl.dataset.roomRating || 0);

            if (ratingFilter === '4_up') {
                return ratingsCount > 0 && rating >= 4;
            }

            if (ratingFilter === '3_up') {
                return ratingsCount > 0 && rating >= 3;
            }

            if (ratingFilter === 'unrated') {
                return ratingsCount === 0;
            }

            return true;
        };

        const applyLiveFilters = () => {
            const query = normalizeText(searchInputEl?.value || '');
            const selectedAmenities = amenityInputs
                .filter((input) => input.checked)
                .map((input) => String(input.value || '').trim())
                .filter((value) => value !== '');
            const minPrice = Number(minPriceRangeEl?.value || 0);
            const maxPrice = Number(maxPriceRangeEl?.value || 0);
            const ratingFilter = getSelectedRating();
            const occupancyFilter = getSelectedOccupancy();

            roomFilterItems.forEach((itemEl) => {
                const propertyId = String(itemEl.getAttribute('data-room-property-id') || '');
                const addressText = normalizeText(itemEl.dataset.roomAddress || '');
                const itemAmenitySet = new Set(
                    String(itemEl.dataset.roomAmenities || '')
                        .split('|')
                        .map((value) => value.trim())
                        .filter((value) => value !== '')
                );

                let isMatch = true;

                if (activePropertyFilterId && propertyId !== activePropertyFilterId) {
                    isMatch = false;
                }

                if (isMatch && query && !addressText.includes(query)) {
                    isMatch = false;
                }

                if (isMatch && selectedAmenities.length > 0) {
                    isMatch = selectedAmenities.every((amenityKey) => itemAmenitySet.has(amenityKey));
                }

                if (isMatch && !matchesRating(itemEl, ratingFilter)) {
                    isMatch = false;
                }

                if (isMatch && !matchesOccupancy(itemEl, occupancyFilter)) {
                    isMatch = false;
                }

                if (isMatch) {
                    const comparablePrice = getComparablePrice(itemEl, occupancyFilter);
                    isMatch = comparablePrice >= minPrice && comparablePrice <= maxPrice;
                }

                itemEl.style.display = isMatch ? '' : 'none';
            });

            propertyGroupEls.forEach((groupEl) => {
                const propertyId = String(groupEl.getAttribute('data-room-property-id') || '');
                const matchesProperty = !activePropertyFilterId || propertyId === activePropertyFilterId;
                const hasVisibleRoom = Array.from(groupEl.querySelectorAll('[data-room-filter-item]'))
                    .some((itemEl) => itemEl.style.display !== 'none');

                groupEl.style.display = matchesProperty && hasVisibleRoom ? '' : 'none';
            });

            if (recommendedSectionEl) {
                const visibleRecommendedCount = Array.from(recommendedSectionEl.querySelectorAll('[data-room-filter-item]'))
                    .filter((itemEl) => itemEl.style.display !== 'none')
                    .length;

                recommendedSectionEl.classList.toggle('d-none', visibleRecommendedCount === 0);

                if (recommendedCountBadgeEl) {
                    recommendedCountBadgeEl.textContent = String(visibleRecommendedCount);
                }
            }

            carouselControllers.forEach((controller) => controller.refresh());
        };

        const applyPropertyRoomFilter = (propertyId, propertyName) => {
            activePropertyFilterId = propertyId ? String(propertyId) : '';
            const hasFilter = !!activePropertyFilterId;

            if (filterNoticeEl && filterNameEl) {
                filterNameEl.textContent = propertyName || '';
                filterNoticeEl.classList.toggle('d-none', !hasFilter);
            }

            applyLiveFilters();
        };

        if (searchInputEl) {
            searchInputEl.addEventListener('input', applyLiveFilters);
        }

        amenityInputs.forEach((inputEl) => inputEl.addEventListener('change', applyLiveFilters));
        ratingInputs.forEach((inputEl) => inputEl.addEventListener('change', applyLiveFilters));
        occupancyInputs.forEach((inputEl) => inputEl.addEventListener('change', applyLiveFilters));
        minPriceRangeEl?.addEventListener('input', applyLiveFilters);
        maxPriceRangeEl?.addEventListener('input', applyLiveFilters);

        if (clearFilterBtn) {
            clearFilterBtn.addEventListener('click', () => applyPropertyRoomFilter('', ''));
        }

        const params = new URLSearchParams(window.location.search || '');
        const initialPropertyId = params.get('property_id');
        const initialPropertyName = params.get('property_name');
        if (initialPropertyId) {
            applyPropertyRoomFilter(initialPropertyId, initialPropertyName || '');
        } else {
            applyLiveFilters();
        }

        // Distance calculation for rooms with location
        @foreach($recommendedRooms->concat($allRooms) as $room)
            @if($room->property->latitude && $room->property->longitude)
                if(navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(pos => {
                        const { latitude, longitude } = pos.coords;
                        const userLatLng = [latitude, longitude];
                        const propertyLatLng = [{{ $room->property->latitude }}, {{ $room->property->longitude }}];
                        
                        const R = 6371;
                        const dLat = (propertyLatLng[0] - userLatLng[0]) * Math.PI / 180;
                        const dLon = (propertyLatLng[1] - userLatLng[1]) * Math.PI / 180;
                        const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
                                 Math.cos(userLatLng[0] * Math.PI / 180) * Math.cos(propertyLatLng[0] * Math.PI / 180) *
                                 Math.sin(dLon/2) * Math.sin(dLon/2);
                        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
                        const distance = R * c;
                        
                        const distanceBadge = document.getElementById('distance-room-{{ $room->id }}');
                        if(distanceBadge) {
                            distanceBadge.innerHTML = '<i class="bi bi-signpost-2 text-primary me-1"></i>' + distance.toFixed(1) + ' km away';
                        }
                    }, err => {
                        const distanceBadge = document.getElementById('distance-room-{{ $room->id }}');
                        if(distanceBadge) {
                            distanceBadge.innerHTML = '<i class="bi bi-geo-alt text-muted me-1"></i>Location available';
                        }
                    });
                } else {
                    const distanceBadge = document.getElementById('distance-room-{{ $room->id }}');
                    if(distanceBadge) {
                        distanceBadge.innerHTML = '<i class="bi bi-geo-alt text-muted me-1"></i>Location available';
                    }
                }
            @endif
        @endforeach
    });
</script>
@endpush

@endsection

