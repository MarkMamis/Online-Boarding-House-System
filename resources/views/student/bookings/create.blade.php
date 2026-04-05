@extends('layouts.student_dashboard')

@section('title', 'Request Booking')

@push('styles')
<style>
    :root { --brand: #166534; --brand-light: #dcfce7; --brand-mid: #16a34a; }

    /* ── Page shell ── */
    .bk-page { background: #f8faf8; min-height: 100vh; padding: 2rem 0 4rem; }

    /* ── Breadcrumb ── */
    .bk-breadcrumb { font-size: .82rem; color: rgba(2,8,20,.5); margin-bottom: 1.5rem; }
    .bk-breadcrumb a { color: var(--brand); text-decoration: none; }
    .bk-breadcrumb a:hover { text-decoration: underline; }

    /* ── Room hero banner ── */
    .bk-hero {
        width: 100%; height: 220px; border-radius: 16px; overflow: hidden;
        background: #e2e8f0; margin-bottom: 1.75rem; position: relative;
    }
    .bk-hero img { width: 100%; height: 100%; object-fit: cover; }
    .bk-hero-overlay {
        position: absolute; inset: 0;
        background: linear-gradient(to top, rgba(0,0,0,.55) 0%, transparent 60%);
        display: flex; align-items: flex-end; padding: 1.25rem 1.5rem;
    }
    .bk-hero-title { color: #fff; font-weight: 700; font-size: 1.35rem; line-height: 1.2; }
    .bk-hero-sub   { color: rgba(255,255,255,.82); font-size: .85rem; }

    /* ── Section cards ── */
    .bk-card {
        background: #fff; border-radius: 16px;
        border: 1px solid rgba(2,8,20,.08); padding: 1.5rem;
        margin-bottom: 1.25rem;
    }
    .bk-section-title {
        font-size: 1rem; font-weight: 700; color: #0f172a;
        margin-bottom: 1rem; display: flex; align-items: center; gap: .5rem;
    }
    .bk-section-title i { color: var(--brand); font-size: 1.1rem; }

    /* ── Date pickers ── */
    .bk-date-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 0; border: 1.5px solid #d1d5db; border-radius: 12px; overflow: hidden; }
    .bk-date-col { padding: .9rem 1rem; }
    .bk-date-col:first-child { border-right: 1px solid #d1d5db; }
    .bk-date-label { font-size: .7rem; font-weight: 700; color: #6b7280; text-transform: uppercase; letter-spacing: .06em; margin-bottom: .25rem; }
    .bk-date-col input[type=date] {
        border: none; outline: none; padding: 0; width: 100%;
        font-size: .95rem; font-weight: 600; color: #0f172a; background: transparent;
    }
    .bk-date-col input[type=date]::-webkit-calendar-picker-indicator { opacity: .4; cursor: pointer; }

    /* ── Notes ── */
    .bk-notes { border: 1.5px solid #d1d5db; border-radius: 12px; padding: .9rem 1rem; width: 100%; resize: none; font-size: .9rem; color: #0f172a; }
    .bk-notes:focus { outline: none; border-color: var(--brand); box-shadow: 0 0 0 3px rgba(22,101,52,.12); }

    /* ── House Rules ── */
    .bk-rules {
        background: linear-gradient(135deg, #f0fdf4 0%, #f8fafc 100%);
        border: 1.5px solid #bbf7d0; border-radius: 16px;
        padding: 1.75rem; margin-bottom: 1.25rem; font-size: .85rem; color: #374151;
        line-height: 1.7;
    }
    .bk-rules-header {
        display: flex; align-items: flex-start; gap: .75rem; margin-bottom: 1.25rem;
        padding-bottom: 1rem; border-bottom: 2px solid #bbf7d0;
    }
    .bk-rules-icon { font-size: 1.4rem; color: var(--brand); flex-shrink: 0; margin-top: .05rem; }
    .bk-rules-title { font-size: 1rem; font-weight: 800; color: #0f172a; margin: 0; }
    .bk-rules-subtitle { font-size: .7rem; color: #6b7280; margin-top: .25rem; }
    .bk-rules-section { margin-top: 1.25rem; }
    .bk-rules-section-header {
        font-size: .78rem; font-weight: 700; color: var(--brand); text-transform: uppercase;
        letter-spacing: .05em; margin-bottom: .75rem; display: flex; align-items: center; gap: .5rem;
    }
    .bk-rules-list { list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: .6rem; }
    .bk-rules-list li { display: flex; gap: .75rem; align-items: flex-start; }
    .bk-rules-list-icon {
        color: var(--brand); font-weight: 700; font-size: .75rem; flex-shrink: 0;
        width: 20px; text-align: center; margin-top: .1rem;
    }
    .bk-rules-list-text { color: #374151; font-size: .8rem; }

    /* ── ESign pad (in modal) ── */
    .bk-esign-status-btn {
        border: 2px dashed #d1d5db; border-radius: 8px; padding: 1rem;
        background: #fafafa; width: 100%; cursor: pointer;
        transition: all .2s; text-align: center; display: flex; flex-direction: column;
        align-items: center; justify-content: center; gap: .5rem;
    }
    .bk-esign-status-btn:hover { border-color: var(--brand); background: #f0fdf4; }
    .bk-esign-status-btn.signed { background: #f0fdf4; border-color: #86efac; }
    .btn-open-sign { background: var(--brand); color: #fff; border: 1px solid var(--brand); border-radius: 8px; padding: .6rem 1.25rem; font-size: .82rem; font-weight: 600; cursor: pointer; width: 100%; transition: background .2s; }
    .btn-open-sign:hover { background: #14532d; }

    /* ── Full-screen signature modal ── */
    .bk-sign-modal { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.8); z-index: 9999; }
    .bk-sign-modal.active { display: flex; align-items: center; justify-content: center; }
    .bk-sign-modal-content {
        background: #fff; border-radius: 20px; max-width: 90vw; max-height: 90vh;
        display: flex; flex-direction: column; box-shadow: 0 25px 50px rgba(0,0,0,.3);
    }
    .bk-sign-modal-header {
        border-bottom: 1px solid #e5e7eb; padding: 1.5rem; display: flex;
        justify-content: space-between; align-items: center; flex-shrink: 0;
    }
    .bk-sign-modal-title { font-size: 1.1rem; font-weight: 700; color: #0f172a; }
    .bk-sign-modal-close {
        background: none; border: none; font-size: 1.5rem; color: #6b7280;
        cursor: pointer; padding: 0; width: 32px; height: 32px; display: flex;
        align-items: center; justify-content: center;
    }
    .bk-sign-modal-close:hover { color: #0f172a; }
    .bk-sign-modal-body {
        padding: 2rem; flex: 1; display: flex; flex-direction: column;
        align-items: center; justify-content: center; overflow-y: auto;
    }
    #signaturePadModal {
        border: 2px solid #0f172a; border-radius: 8px; background: #fff;
        touch-action: none; cursor: crosshair; width: 100%; max-width: 600px;
        height: 60vh;
    }
    .bk-sign-modal-footer {
        border-top: 1px solid #e5e7eb; padding: 1.5rem; display: flex;
        justify-content: center; gap: 1rem; flex-shrink: 0;
    }
    .btn-sign-modal { background: var(--brand); color: #fff; border: 1px solid var(--brand); border-radius: 8px; padding: .8rem 2rem; font-size: .88rem; font-weight: 600; cursor: pointer; transition: background .2s; }
    .btn-sign-modal:hover { background: #14532d; }
    .btn-cancel-modal { background: #f3f4f6; color: #6b7280; border: 1px solid #d1d5db; border-radius: 8px; padding: .8rem 2rem; font-size: .88rem; font-weight: 600; cursor: pointer; transition: all .2s; }
    .btn-cancel-modal:hover { background: #e5e7eb; }
    .btn-clear-modal { background: #f3f4f6; color: #6b7280; border: 1px solid #d1d5db; border-radius: 8px; padding: .8rem 2rem; font-size: .88rem; font-weight: 600; cursor: pointer; transition: all .2s; }
    .btn-clear-modal:hover { background: #e5e7eb; }

    /* ── Confirmation Modal ── */
    .bk-confirm-modal { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.5); z-index: 9998; }
    .bk-confirm-modal.active { display: flex; align-items: center; justify-content: center; }
    .bk-confirm-content {
        background: #fff;
        border-radius: 18px;
        max-width: 980px;
        width: 94%;
        max-height: 92vh;
        box-shadow: 0 24px 70px rgba(2,8,20,.28);
        overflow: hidden;
        display: flex;
        flex-direction: column;
    }
    .bk-confirm-header {
        background: #fff;
        color: #0f172a;
        padding: 1.15rem 1.5rem;
        flex-shrink: 0;
        border-bottom: 1px solid #e5e7eb;
    }
    .bk-confirm-title {
        font-size: 1.05rem;
        font-weight: 800;
        margin: 0;
        letter-spacing: .01em;
    }
    .bk-confirm-body {
        padding: 1.5rem;
        overflow-y: auto;
        flex: 1;
    }
    .bk-confirm-shell {
        display: grid;
        grid-template-columns: 1.15fr .85fr;
        gap: 1.25rem;
    }
    .bk-confirm-col {
        border: 1px solid rgba(2,8,20,.1);
        border-radius: 20px;
        background: #fff;
        padding: 1.2rem;
        box-shadow: 0 4px 24px rgba(22,101,52,.08);
    }
    .bk-detail-head {
        border: 1px solid rgba(2,8,20,.08);
        border-radius: 14px;
        padding: .8rem .9rem;
        background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
        margin-bottom: .9rem;
    }
    .bk-detail-head-title {
        font-size: .98rem;
        font-weight: 800;
        color: #0f172a;
        line-height: 1.2;
    }
    .bk-detail-head-sub {
        margin-top: .2rem;
        font-size: .78rem;
        color: rgba(2,8,20,.55);
        line-height: 1.35;
    }
    .bk-detail-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: .55rem;
    }
    .bk-detail-item {
        border: 1px solid rgba(2,8,20,.08);
        border-radius: 12px;
        background: #fff;
        padding: .62rem .72rem;
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        align-items: center;
        gap: .6rem;
    }
    .bk-detail-item .bk-confirm-label {
        margin: 0;
        color: rgba(2,8,20,.55);
        font-size: .8rem;
        font-weight: 600;
    }
    .bk-detail-item .bk-confirm-value {
        margin: 0;
        font-size: .92rem;
        font-weight: 700;
        color: #0f172a;
        text-align: right;
    }
    .bk-confirm-panel-title {
        font-size: .95rem;
        color: #0f172a;
        font-weight: 700;
        margin-bottom: .85rem;
        display: flex;
        align-items: center;
        gap: .45rem;
    }
    .bk-confirm-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: .7rem 0;
        border-bottom: 1px solid rgba(2,8,20,.08);
        font-size: .88rem;
    }
    .bk-confirm-row:last-child { border-bottom: none; }
    .bk-confirm-label { color: rgba(2,8,20,.5); font-weight: 500; }
    .bk-confirm-value { color: #0f172a; font-weight: 600; }
    .bk-confirm-price {
        font-size: 1.6rem;
        font-weight: 800;
        color: #0f172a;
        margin-bottom: .85rem;
    }
    .bk-confirm-price span {
        font-size: .9rem;
        font-weight: 400;
        color: rgba(2,8,20,.45);
    }
    .bk-confirm-edit-box {
        margin-top: .9rem;
        padding: .9rem;
        border: 1px solid rgba(2,8,20,.08);
        border-radius: 14px;
        background: #fff;
    }
    .bk-confirm-edit-title {
        font-size: .84rem;
        font-weight: 700;
        color: rgba(2,8,20,.55);
        margin-bottom: .65rem;
    }
    .bk-plan-options {
        display: grid;
        grid-template-columns: 1fr;
        gap: .5rem;
    }
    .bk-plan-option {
        display: flex;
        align-items: center;
        gap: .55rem;
        padding: .55rem .7rem;
        border: 1px solid rgba(2,8,20,.14);
        border-radius: 10px;
        background: #fff;
        font-size: .84rem;
    }
    .bk-plan-option input[type="radio"],
    .bk-plan-option input[type="checkbox"] {
        accent-color: var(--brand);
        cursor: pointer;
        flex-shrink: 0;
    }
    .bk-confirm-info {
        margin-top: 1rem;
        padding-top: .85rem;
        border-top: 1px solid #e5e7eb;
        font-size: .78rem;
        color: #94a3b8;
        display: flex;
        gap: .5rem;
        align-items: flex-start;
    }
    .bk-confirm-footer {
        background: #f9fafb;
        padding: 1.25rem 1.5rem;
        display: flex;
        gap: 1rem;
        justify-content: flex-end;
        flex-shrink: 0;
        border-top: 1px solid #e5e7eb;
    }
    .bk-confirm-cancel { background: #f3f4f6; color: #6b7280; border: 1px solid #d1d5db; border-radius: 8px; padding: .7rem 1.5rem; font-size: .85rem; font-weight: 600; cursor: pointer; transition: all .2s; }
    .bk-confirm-cancel:hover { background: #e5e7eb; }
    .bk-confirm-submit { background: var(--brand); color: #fff; border: 1px solid var(--brand); border-radius: 8px; padding: .7rem 1.5rem; font-size: .85rem; font-weight: 600; cursor: pointer; transition: background .2s; }
    .bk-confirm-submit:hover { background: #14532d; }

    /* ── Agree checkbox ── */
    .bk-agree {
        display: flex; align-items: flex-start; gap: .75rem;
        background: #f0fdf4; border: 1.5px solid #86efac; border-radius: 12px;
        padding: 1rem 1.25rem; margin-bottom: 1.25rem;
    }
    .bk-agree input[type=checkbox] { width: 18px; height: 18px; margin-top: .15rem; accent-color: var(--brand); flex-shrink: 0; cursor: pointer; }
    .bk-agree label { font-size: .88rem; color: #0f172a; cursor: pointer; }
    .bk-agree label strong { color: var(--brand); }

    /* ── Submit button ── */
    .btn-brand-book {
        background: var(--brand); color: #fff; border: none;
        border-radius: 12px; padding: .9rem 2rem; font-weight: 700;
        font-size: 1rem; width: 100%; transition: background .2s, opacity .2s;
    }
    .btn-brand-book:hover:not(:disabled) { background: #14532d; color: #fff; }
    .btn-brand-book:disabled { opacity: .45; cursor: not-allowed; }

    /* ── Sticky summary card ── */
    .bk-summary {
        background: #fff; border-radius: 20px;
        border: 1px solid rgba(2,8,20,.1); padding: 1.5rem;
        position: sticky; top: 5.5rem;
        box-shadow: 0 4px 24px rgba(22,101,52,.08);
    }
    .bk-summary-price { font-size: 1.6rem; font-weight: 800; color: #0f172a; }
    .bk-summary-price span { font-size: .9rem; font-weight: 400; color: rgba(2,8,20,.45); }
    .bk-summary-divider { border: none; border-top: 1px solid rgba(2,8,20,.08); margin: 1rem 0; }
    .bk-summary-row { display: flex; justify-content: space-between; align-items: center; font-size: .88rem; margin-bottom: .5rem; }
    .bk-summary-row .label { color: rgba(2,8,20,.5); }
    .bk-summary-row .val   { font-weight: 600; color: #0f172a; }
    .bk-summary-total { font-size: 1rem; font-weight: 800; color: #0f172a; border-top: 1.5px solid rgba(2,8,20,.08); padding-top: .75rem; margin-top: .25rem; }
    .bk-summary-note { font-size: .75rem; color: rgba(2,8,20,.4); text-align: center; margin-top: .75rem; }
    .bk-summary-landlord { display: flex; align-items: center; gap: .75rem; margin-bottom: 1.25rem; }
    .bk-summary-landlord-avatar {
        width: 40px; height: 40px; border-radius: 50%; background: var(--brand);
        color: #fff; display: flex; align-items: center; justify-content: center;
        font-weight: 700; font-size: 1rem; flex-shrink: 0;
    }
    .bk-summary-landlord-name { font-weight: 600; font-size: .88rem; color: #0f172a; }
    .bk-summary-landlord-label { font-size: .75rem; color: rgba(2,8,20,.45); }

    /* ── Alert ── */
    .bk-alert-danger { background: #fef2f2; border: 1px solid #fca5a5; border-radius: 12px; padding: 1rem 1.25rem; margin-bottom: 1.25rem; color: #991b1b; font-size: .88rem; }

    @media (max-width: 767px) {
        .bk-hero { height: 150px; }
        .bk-date-grid { grid-template-columns: 1fr; }
        .bk-date-col:first-child { border-right: none; border-bottom: 1px solid #d1d5db; }
        .bk-rules { font-size: .8rem; }
        .bk-rules-title { font-size: .88rem; }
        .bk-confirm-modal.active { align-items: flex-start; padding: 1rem 0; }
        .bk-confirm-content { width: 94%; max-height: 94vh; }
        .bk-confirm-shell { grid-template-columns: 1fr; }
        .bk-confirm-body { padding: 1rem; }
        .bk-confirm-footer { padding: 1rem; }
    }
</style>
@endpush

@section('content')
@php
    $inclusions = is_string($room->inclusions)
        ? json_decode($room->inclusions, true)
        : (is_array($room->inclusions) ? $room->inclusions : []);
    $inclusions = is_array($inclusions) ? $inclusions : [];

    $landlordName    = $landlord?->full_name ?? 'Landlord';
    $landlordInitial = strtoupper(substr($landlordName, 0, 1));
    $propertyName    = $room->property->name ?? 'Property';
    $propertyAddress = $room->property->address ?? '';
    $roomLabel       = $room->label ?? ('Room ' . $room->room_number);
    $studentName     = $student->full_name ?? $student->name ?? 'Tenant';
    $today           = now()->format('F d, Y');
    $refNo           = 'OBHS-' . strtoupper(substr(md5($room->id . now()->timestamp), 0, 8));
    $advanceRequiredByLandlord = (bool) ($room->requires_advance_payment ?? false);
    $initialIncludeAdvance = old('include_advance_payment', '0');
    if ($advanceRequiredByLandlord) {
        $initialIncludeAdvance = '1';
    }

    $defaultHouseRuleCategories = (array) config('property_house_rules.categories', []);
    $propertyHouseRules = (array) ($room->property->house_rules ?? []);
    $houseRuleSections = collect($defaultHouseRuleCategories)
        ->map(function ($categoryConfig, $categoryKey) use ($propertyHouseRules) {
            $fallbackRules = (array) ($categoryConfig['rules'] ?? []);
            $rules = collect((array) ($propertyHouseRules[$categoryKey] ?? $fallbackRules))
                ->map(fn ($line) => trim((string) $line))
                ->filter()
                ->values();

            return [
                'label' => (string) ($categoryConfig['label'] ?? $categoryKey),
                'icon' => (string) ($categoryConfig['icon'] ?? 'dot'),
                'rules' => $rules,
            ];
        })
        ->filter(fn ($section) => $section['rules']->isNotEmpty())
        ->values();
@endphp

<div class="bk-page">
    <div class="container" style="max-width:1100px;">

        {{-- Breadcrumb --}}
        <div class="bk-breadcrumb">
            <a href="{{ route('student.dashboard') }}">Dashboard</a> /
            <a href="{{ route('student.rooms.show', $room->id) }}">{{ $propertyName }}</a> /
            <span>Request Booking</span>
        </div>

        @if($errors->any())
        <div class="bk-alert-danger">
            <i class="bi bi-exclamation-triangle me-2"></i>
            <ul class="mb-0 ps-3">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
        @endif

        {{-- Hero banner --}}
        <div class="bk-hero">
            @if($coverImage)
                <img src="{{ asset('storage/' . $coverImage->image_path) }}" alt="{{ $propertyName }}">
            @else
                <div style="width:100%;height:100%;background:linear-gradient(135deg,#166534 0%,#4ade80 100%);"></div>
            @endif
            <div class="bk-hero-overlay">
                <div>
                    <div class="bk-hero-title">{{ $propertyName }}</div>
                    <div class="bk-hero-sub"><i class="bi bi-geo-alt me-1"></i>{{ $propertyAddress }}</div>
                </div>
            </div>
        </div>

        <div class="row g-4">

            {{-- ═══════════════ LEFT COLUMN ═══════════════ --}}
            <div class="col-lg-7">

                {{-- Step 1: Dates --}}
                <div class="bk-card">
                    <div class="bk-section-title">
                        <i class="bi bi-calendar3"></i> Your Stay
                    </div>
                    <div class="bk-date-grid mb-3">
                        <div class="bk-date-col">
                            <div class="bk-date-label">Check-in</div>
                            <input type="date" id="checkInDisplay" value="{{ old('check_in') }}"
                                   min="{{ now()->toDateString() }}" />
                        </div>
                        <div class="bk-date-col">
                            <div class="bk-date-label">Check-out</div>
                            <input type="date" id="checkOutDisplay" value="{{ old('check_out') }}"
                                   min="{{ now()->addDay()->toDateString() }}" />
                        </div>
                    </div>
                    <div>
                        <div class="bk-date-label mb-1" style="font-size:.78rem;">Special Requests / Notes</div>
                        <textarea id="notesDisplay" rows="3" class="bk-notes"
                                  placeholder="Early check-in, specific bed preference, allergies, etc.">{{ old('notes') }}</textarea>
                    </div>
                </div>

                {{-- House Rules --}}
                <div class="bk-rules">
                    <div class="bk-rules-header">
                        <i class="bi bi-file-text bk-rules-icon"></i>
                        <div>
                            <h5 class="bk-rules-title">House Rules</h5>
                            <div class="bk-rules-subtitle">Please review before booking</div>
                        </div>
                    </div>

                    @php $ruleNumber = 1; @endphp
                    @forelse($houseRuleSections as $section)
                        <div class="bk-rules-section">
                            <div class="bk-rules-section-header"><i class="bi bi-{{ $section['icon'] }}" style="font-size:.85rem;"></i> {{ $section['label'] }}</div>
                            <ul class="bk-rules-list">
                                @foreach($section['rules'] as $rule)
                                    <li>
                                        <span class="bk-rules-list-icon">{{ str_pad((string) $ruleNumber, 2, '0', STR_PAD_LEFT) }}</span>
                                        <span class="bk-rules-list-text">{{ $rule }}</span>
                                    </li>
                                    @php $ruleNumber++; @endphp
                                @endforeach
                            </ul>
                        </div>
                    @empty
                        <div class="small text-muted">No house rules configured for this property yet.</div>
                    @endforelse
                </div>

                {{-- Hidden real form --}}
                <form id="bookingForm" method="POST" action="{{ route('bookings.store', $room->id) }}">
                    @csrf
                    <input type="hidden" name="check_in"             id="fCheckIn"  value="{{ old('check_in') }}">
                    <input type="hidden" name="check_out"            id="fCheckOut" value="{{ old('check_out') }}">
                    <input type="hidden" name="notes"                id="fNotes"    value="{{ old('notes') }}">
                    <input type="hidden" name="include_advance_payment" id="fIncludeAdvance" value="{{ $initialIncludeAdvance }}">
                    <input type="hidden" name="occupancy_mode" id="fOccupancyMode" value="{{ old('occupancy_mode', 'solo') }}">
                    <input type="hidden" name="agreed_to_contract"   value="1">
                </form>

                <button type="button" class="btn-brand-book" id="submitBtn" disabled onclick="showConfirmation()">
                    <i class="bi bi-calendar-check me-2"></i>Submit Booking Request
                </button>
                <div class="text-center mt-2" style="font-size:.78rem;color:rgba(2,8,20,.4);">
                    <i class="bi bi-lock me-1"></i>Your request is reviewed by the landlord. You won't be charged yet.
                </div>

            </div>{{-- /left col --}}

            {{-- ═══════════════ RIGHT COLUMN ═══════════════ --}}
            <div class="col-lg-5">
                <div class="bk-summary">

                    <div class="bk-summary-landlord">
                        <div class="bk-summary-landlord-avatar">{{ $landlordInitial }}</div>
                        <div>
                            <div class="bk-summary-landlord-name">{{ $landlordName }}</div>
                            <div class="bk-summary-landlord-label">Boarding House Operator</div>
                        </div>
                    </div>

                    <hr class="bk-summary-divider">

                    <div class="fw-bold mb-1" style="font-size:.95rem;">{{ $roomLabel }}</div>
                    <div class="text-muted" style="font-size:.8rem; line-height:1.5;">
                        {{ $propertyName }}<br>
                        <i class="bi bi-geo-alt me-1"></i>{{ $propertyAddress }}
                    </div>

                    @if(count($inclusions) > 0)
                    <div class="d-flex flex-wrap gap-1 mt-2">
                        @foreach($inclusions as $inc)
                        <span style="background:#f0fdf4;color:#166534;border:1px solid #bbf7d0;border-radius:20px;padding:.2rem .65rem;font-size:.72rem;font-weight:600;">{{ $inc }}</span>
                        @endforeach
                    </div>
                    @endif

                    <hr class="bk-summary-divider">

                    <div class="bk-summary-price mb-3">
                        ₱{{ number_format($room->price, 0) }}<span>/month</span>
                    </div>

                    <div class="mb-3">
                        <div class="fw-semibold mb-2" style="font-size:.84rem;">Occupancy Option</div>
                        <div class="d-flex flex-column gap-2" style="font-size:.82rem;">
                            <label class="d-flex align-items-center gap-2">
                                <input type="radio" name="occupancy_mode_display" value="solo" {{ old('occupancy_mode', 'solo') === 'solo' ? 'checked' : '' }}>
                                <span>Solo occupancy</span>
                            </label>
                            @if((int) $room->capacity > 1)
                                <label class="d-flex align-items-center gap-2">
                                    <input type="radio" name="occupancy_mode_display" value="shared" {{ old('occupancy_mode') === 'shared' ? 'checked' : '' }}>
                                    <span>Open vacancy (shared) - rent split by capacity ({{ (int) $room->capacity }})</span>
                                </label>
                            @endif
                        </div>
                    </div>

                    <div class="bk-summary-row">
                        <span class="label">Monthly Rent</span>
                        <span class="val" id="summaryMonthlyRent">₱{{ number_format($room->price, 0) }}</span>
                    </div>
                    <div class="bk-summary-row">
                        <span class="label">1 Month Advance</span>
                        <span class="val" id="summaryAdvance">₱{{ number_format($room->price, 0) }}</span>
                    </div>
                    <div class="bk-summary-row bk-summary-total">
                        <span>Move-in Total</span>
                        <span id="summaryMoveInTotal">₱{{ number_format($room->price, 0) }}</span>
                    </div>

                    <div class="form-check mt-3">
                        <input
                            class="form-check-input"
                            type="checkbox"
                            id="includeAdvanceDisplay"
                            @checked($initialIncludeAdvance === '1')
                            @disabled($advanceRequiredByLandlord)
                        >
                        <label class="form-check-label" for="includeAdvanceDisplay" style="font-size:.83rem;color:rgba(2,8,20,.72);">
                            Include 1 month advance in move-in payment
                        </label>
                    </div>
                    <div style="font-size:.76rem;color:rgba(2,8,20,.52);margin-top:.25rem;">
                        @if($advanceRequiredByLandlord)
                            This room requires advance payment, based on the landlord's billing rule.
                        @else
                            You can choose to pay advance now or pay it later based on your arrangement with the landlord.
                        @endif
                    </div>

                    <hr class="bk-summary-divider">

                    <div class="bk-summary-row">
                        <span class="label"><i class="bi bi-box-arrow-in-right me-1"></i>Check-in</span>
                        <span class="val" id="summaryCheckin">—</span>
                    </div>
                    <div class="bk-summary-row">
                        <span class="label"><i class="bi bi-box-arrow-right me-1"></i>Check-out</span>
                        <span class="val" id="summaryCheckout">—</span>
                    </div>
                    <div class="bk-summary-row">
                        <span class="label"><i class="bi bi-moon me-1"></i>Duration</span>
                        <span class="val" id="summaryDuration">—</span>
                    </div>

                    <hr class="bk-summary-divider">

                    <div class="d-flex flex-column gap-2" style="font-size:.78rem;color:rgba(2,8,20,.55);">
                        <div><i class="bi bi-shield-check me-2" style="color:var(--brand);"></i>Verified boarding house</div>
                        <div><i class="bi bi-arrow-counterclockwise me-2"></i>Cancel anytime while request is pending</div>
                        <div><i class="bi bi-person-check me-2" style="color:#3b82f6;"></i>Landlord reviews your request before approval</div>
                    </div>

                    <div class="bk-summary-note">Move-in cost is collected offline by the landlord upon approval.</div>
                </div>

                <div class="text-center mt-3">
                    <a href="{{ route('student.rooms.show', $room->id) }}"
                       style="font-size:.82rem;color:rgba(2,8,20,.45);text-decoration:none;">
                        <i class="bi bi-arrow-left me-1"></i>Back to room details
                    </a>
                </div>
            </div>{{-- /right col --}}

        </div>{{-- /row --}}
    </div>{{-- /container --}}
</div>

<!-- Booking Confirmation Modal -->
<div class="bk-confirm-modal" id="confirmModal">
    <div class="bk-confirm-content">
        <div class="bk-confirm-header">
            <h5 class="bk-confirm-title"><i class="bi bi-check2-circle me-2" style="color:#166534;"></i>Confirm Your Booking</h5>
        </div>
        <div class="bk-confirm-body">
            <div class="bk-confirm-shell">
                <div class="bk-confirm-col">
                    <div class="bk-confirm-panel-title"><i class="bi bi-file-earmark-text"></i>Booking Details</div>
                    <div class="bk-detail-head">
                        <div class="bk-detail-head-title" id="confirmRoom">—</div>
                        <div class="bk-detail-head-sub" id="confirmProperty">—</div>
                    </div>
                    <div class="bk-detail-grid">
                        <div class="bk-detail-item">
                            <p class="bk-confirm-label"><i class="bi bi-box-arrow-in-right me-1"></i>Check-in</p>
                            <p class="bk-confirm-value" id="confirmCheckin">—</p>
                        </div>
                        <div class="bk-detail-item">
                            <p class="bk-confirm-label"><i class="bi bi-box-arrow-right me-1"></i>Check-out</p>
                            <p class="bk-confirm-value" id="confirmCheckout">—</p>
                        </div>
                        <div class="bk-detail-item">
                            <p class="bk-confirm-label"><i class="bi bi-moon me-1"></i>Duration</p>
                            <p class="bk-confirm-value" id="confirmDuration">—</p>
                        </div>
                    </div>
                </div>

                <div class="bk-confirm-col">
                    <div class="bk-confirm-panel-title"><i class="bi bi-wallet2"></i>Plan & Payment</div>
                    <div class="bk-confirm-price" id="confirmPriceHero">—<span>/month</span></div>
                    <div class="bk-confirm-row">
                        <span class="bk-confirm-label">Monthly Rent</span>
                        <span class="bk-confirm-value" id="confirmPrice">—</span>
                    </div>
                    <div class="bk-confirm-row">
                        <span class="bk-confirm-label">Occupancy</span>
                        <span class="bk-confirm-value" id="confirmOccupancy">—</span>
                    </div>
                    <div class="bk-confirm-row">
                        <span class="bk-confirm-label">Advance Payment</span>
                        <span class="bk-confirm-value" id="confirmAdvanceOption">—</span>
                    </div>
                    <div class="bk-confirm-row">
                        <span class="bk-confirm-label">Move-in Total</span>
                        <span class="bk-confirm-value" id="confirmMoveInTotal">—</span>
                    </div>

                    <div class="bk-confirm-edit-box">
                        <div class="bk-confirm-edit-title">Booking Options (Editable)</div>
                        <div class="bk-plan-options">
                            <label class="bk-plan-option">
                                <input type="radio" name="confirm_occupancy_mode" value="solo">
                                <span>Solo occupancy plan</span>
                            </label>
                            @if((int) $room->capacity > 1)
                            <label class="bk-plan-option">
                                <input type="radio" name="confirm_occupancy_mode" value="shared">
                                <span>Shared occupancy plan (split by capacity {{ (int) $room->capacity }})</span>
                            </label>
                            @endif
                            <label class="bk-plan-option">
                                <input type="checkbox" id="confirmIncludeAdvance" @disabled($advanceRequiredByLandlord)>
                                <span>Include 1 month advance in move-in total</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bk-confirm-info">
                <i class="bi bi-info-circle" style="flex-shrink: 0; margin-top: .1rem;"></i>
                <span>Your booking request will be reviewed by the landlord. You won't be charged until approved.</span>
            </div>
        </div>
        <div class="bk-confirm-footer">
            <button type="button" class="bk-confirm-cancel" onclick="closeConfirmation()">
                <i class="bi bi-x me-1"></i>Cancel
            </button>
            <button type="button" class="bk-confirm-submit" onclick="submitBooking()">
                <i class="bi bi-check2 me-1"></i>Confirm Booking
            </button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    const baseMonthlyRent = {{ (float) $room->price }};
    const roomCapacity = Math.max(1, {{ (int) $room->capacity }});
    const advanceRequiredByLandlord = @json($advanceRequiredByLandlord);

    function peso(amount) {
        return '₱' + new Intl.NumberFormat('en-PH', {
            minimumFractionDigits: Number.isInteger(amount) ? 0 : 2,
            maximumFractionDigits: 2
        }).format(amount);
    }

    function selectedOccupancyMode() {
        const selected = document.querySelector('input[name="occupancy_mode_display"]:checked');
        return selected ? selected.value : 'solo';
    }

    function computedMonthlyRent() {
        const mode = selectedOccupancyMode();
        if (mode === 'shared') {
            return Math.round((baseMonthlyRent / roomCapacity) * 100) / 100;
        }
        return baseMonthlyRent;
    }

    function syncAdvanceOption() {
        const includeAdvanceDisplay = document.getElementById('includeAdvanceDisplay');
        let includeAdvance = includeAdvanceDisplay ? includeAdvanceDisplay.checked : true;
        if (advanceRequiredByLandlord) {
            includeAdvance = true;
            if (includeAdvanceDisplay) {
                includeAdvanceDisplay.checked = true;
            }
        }
        const monthlyRent = computedMonthlyRent();
        const advanceAmount = includeAdvance ? monthlyRent : 0;
        const moveInTotal = monthlyRent + advanceAmount;

        const fOccupancyMode = document.getElementById('fOccupancyMode');
        if (fOccupancyMode) {
            fOccupancyMode.value = selectedOccupancyMode();
        }

        const fIncludeAdvance = document.getElementById('fIncludeAdvance');
        if (fIncludeAdvance) {
            fIncludeAdvance.value = includeAdvance ? '1' : '0';
        }

        const summaryMonthlyRent = document.getElementById('summaryMonthlyRent');
        const summaryAdvance = document.getElementById('summaryAdvance');
        const summaryMoveInTotal = document.getElementById('summaryMoveInTotal');
        if (summaryMonthlyRent) summaryMonthlyRent.textContent = peso(monthlyRent);
        if (summaryAdvance) summaryAdvance.textContent = peso(advanceAmount);
        if (summaryMoveInTotal) summaryMoveInTotal.textContent = peso(moveInTotal);
    }

    function syncDates() {
        const ci = document.getElementById('checkInDisplay').value;
        const checkOutInput = document.getElementById('checkOutDisplay');
        let co = checkOutInput.value;

        document.getElementById('fCheckIn').value  = ci;

        if (ci) {
            const next = new Date(ci);
            next.setDate(next.getDate() + 1);
            checkOutInput.min = next.toISOString().split('T')[0];

            // Auto-fill checkout to one month after check-in when empty.
            if (!co) {
                const defaultCheckout = new Date(ci + 'T00:00:00');
                defaultCheckout.setMonth(defaultCheckout.getMonth() + 1);
                co = defaultCheckout.toISOString().split('T')[0];
                checkOutInput.value = co;
            }

            // If selected checkout is earlier than min, correct it to min valid date.
            if (co && co < checkOutInput.min) {
                co = checkOutInput.min;
                checkOutInput.value = co;
            }
        }

        document.getElementById('fCheckOut').value = co;

        const fmt = d => d
            ? new Date(d + 'T00:00:00').toLocaleDateString('en-PH', {month:'short', day:'numeric', year:'numeric'})
            : '—';

        document.getElementById('summaryCheckin').textContent  = fmt(ci);
        document.getElementById('summaryCheckout').textContent = fmt(co);

        if (ci && co) {
            const days = Math.round((new Date(co) - new Date(ci)) / 86400000);
            document.getElementById('summaryDuration').textContent = days > 0
                ? days + ' day' + (days !== 1 ? 's' : '') + ' (~' + (days / 30).toFixed(1) + ' mo)'
                : 'Invalid range';
        } else {
            document.getElementById('summaryDuration').textContent = '—';
        }

        toggleSubmit();
    }

    function toggleSubmit() {
        const ci     = document.getElementById('checkInDisplay').value;
        const co     = document.getElementById('checkOutDisplay').value;
        document.getElementById('submitBtn').disabled = !(ci && co);
    }

    function submitBooking() {
        document.getElementById('fNotes').value = document.getElementById('notesDisplay').value;
        document.getElementById('bookingForm').submit();
    }

    function showConfirmation() {
        const ci = document.getElementById('checkInDisplay').value;
        const co = document.getElementById('checkOutDisplay').value;

        if (!ci || !co) {
            alert('Please select both check-in and check-out dates.');
            return;
        }

        // Format dates for display
        const fmt = d => d ? new Date(d + 'T00:00:00').toLocaleDateString('en-PH', {
            month: 'short', day: 'numeric', year: 'numeric'
        }) : '—';

        // Calculate duration
        const days = Math.round((new Date(co) - new Date(ci)) / 86400000);
        const durationText = days > 0
            ? days + ' day' + (days !== 1 ? 's' : '') + ' (~' + (days / 30).toFixed(1) + ' mo)'
            : 'Invalid range';

        // Populate confirmation modal
        document.getElementById('confirmRoom').textContent = document.querySelector('.bk-summary .fw-bold').textContent.trim();
        document.getElementById('confirmProperty').textContent = '{{ $propertyName }}';
        document.getElementById('confirmCheckin').textContent = fmt(ci);
        document.getElementById('confirmCheckout').textContent = fmt(co);
        document.getElementById('confirmDuration').textContent = durationText;
        const monthlyRent = computedMonthlyRent();
        document.getElementById('confirmPrice').textContent = peso(monthlyRent);
        const confirmPriceHero = document.getElementById('confirmPriceHero');
        if (confirmPriceHero) {
            confirmPriceHero.innerHTML = `${peso(monthlyRent)}<span>/month</span>`;
        }
        document.getElementById('confirmOccupancy').textContent = selectedOccupancyMode() === 'shared' ? 'Open vacancy (shared)' : 'Solo occupancy';

        const includeAdvanceDisplay = document.getElementById('includeAdvanceDisplay');
        const includeAdvance = includeAdvanceDisplay ? includeAdvanceDisplay.checked : true;
        const advanceAmount = includeAdvance ? monthlyRent : 0;
        const moveInTotal = monthlyRent + advanceAmount;
        document.getElementById('confirmAdvanceOption').textContent = includeAdvance ? 'Included' : 'Not included';
        document.getElementById('confirmMoveInTotal').textContent = peso(moveInTotal);

        syncConfirmControls();

        // Show modal
        document.getElementById('confirmModal').classList.add('active');
    }

    function syncConfirmControls() {
        const mainOccupancy = selectedOccupancyMode();
        const confirmOccupancy = document.querySelector(`input[name="confirm_occupancy_mode"][value="${mainOccupancy}"]`);
        if (confirmOccupancy) {
            confirmOccupancy.checked = true;
        }

        const includeAdvanceDisplay = document.getElementById('includeAdvanceDisplay');
        const confirmIncludeAdvance = document.getElementById('confirmIncludeAdvance');
        if (confirmIncludeAdvance && includeAdvanceDisplay) {
            confirmIncludeAdvance.checked = !!includeAdvanceDisplay.checked;
            if (advanceRequiredByLandlord) {
                confirmIncludeAdvance.checked = true;
            }
        }
    }

    function applyConfirmationEdits() {
        const confirmSelected = document.querySelector('input[name="confirm_occupancy_mode"]:checked');
        const mainSelected = confirmSelected
            ? document.querySelector(`input[name="occupancy_mode_display"][value="${confirmSelected.value}"]`)
            : null;
        if (mainSelected) {
            mainSelected.checked = true;
        }

        const includeAdvanceDisplay = document.getElementById('includeAdvanceDisplay');
        const confirmIncludeAdvance = document.getElementById('confirmIncludeAdvance');
        if (includeAdvanceDisplay && confirmIncludeAdvance) {
            includeAdvanceDisplay.checked = advanceRequiredByLandlord ? true : !!confirmIncludeAdvance.checked;
        }

        syncAdvanceOption();

        const monthlyRent = computedMonthlyRent();
        const includeAdvance = includeAdvanceDisplay ? includeAdvanceDisplay.checked : true;
        const advanceAmount = includeAdvance ? monthlyRent : 0;
        const moveInTotal = monthlyRent + advanceAmount;
        document.getElementById('confirmPrice').textContent = peso(monthlyRent);
        const confirmPriceHero = document.getElementById('confirmPriceHero');
        if (confirmPriceHero) {
            confirmPriceHero.innerHTML = `${peso(monthlyRent)}<span>/month</span>`;
        }
        document.getElementById('confirmOccupancy').textContent = selectedOccupancyMode() === 'shared' ? 'Open vacancy (shared)' : 'Solo occupancy';
        document.getElementById('confirmAdvanceOption').textContent = includeAdvance ? 'Included' : 'Not included';
        document.getElementById('confirmMoveInTotal').textContent = peso(moveInTotal);
    }

    function closeConfirmation() {
        document.getElementById('confirmModal').classList.remove('active');
    }

    document.getElementById('checkInDisplay').addEventListener('change', syncDates);
    document.getElementById('checkOutDisplay').addEventListener('change', syncDates);
    document.getElementById('includeAdvanceDisplay').addEventListener('change', syncAdvanceOption);
    document.querySelectorAll('input[name="occupancy_mode_display"]').forEach((el) => {
        el.addEventListener('change', syncAdvanceOption);
    });
    document.querySelectorAll('input[name="confirm_occupancy_mode"]').forEach((el) => {
        el.addEventListener('change', applyConfirmationEdits);
    });
    const confirmIncludeAdvance = document.getElementById('confirmIncludeAdvance');
    if (confirmIncludeAdvance) {
        confirmIncludeAdvance.addEventListener('change', applyConfirmationEdits);
    }
    document.getElementById('notesDisplay').addEventListener('input', function() {
        document.getElementById('fNotes').value = this.value;
    });

    // Close modal when clicking outside
    document.getElementById('confirmModal').addEventListener('click', (e) => {
        if (e.target.id === 'confirmModal') closeConfirmation();
    });

    // Restore state if validation failed
    (function() {
        const ci = document.getElementById('checkInDisplay').value;
        const co = document.getElementById('checkOutDisplay').value;
        syncAdvanceOption();
        if (ci || co) syncDates();
    })();

</script>
@endpush
