@extends('layouts.student_dashboard')

@section('title', 'Room ' . $room->room_number . ' — ' . ($room->property->name ?? 'Details'))

@push('styles')
<style>
    /* ── Breadcrumb ── */
    .sr-back-bar { display:flex; align-items:center; gap:.5rem; margin-bottom:1.25rem; }
    .sr-back-bar a { color:var(--brand); font-weight:600; font-size:.85rem; text-decoration:none; }
    .sr-back-bar a:hover { text-decoration:underline; }
    .sr-back-bar .sep { color:rgba(2,8,20,.3); font-size:.75rem; }
    .sr-back-bar .cur { font-size:.85rem; color:rgba(2,8,20,.55); }

    /* ── Hero cover ── */
    .sr-hero {
        width: 100%; aspect-ratio: 16/6; object-fit: cover;
        border-radius: 1rem; display: block;
        border: 1px solid rgba(2,8,20,.08);
        box-shadow: 0 4px 18px rgba(2,8,20,.10);
    }
    .sr-hero-placeholder {
        aspect-ratio: 16/6; background: #f1f5f9; border-radius: 1rem;
        border: 1px solid rgba(2,8,20,.08);
        display:flex; align-items:center; justify-content:center; color:#94a3b8;
    }

    /* ── Cards ── */
    .sr-card { background:#fff; border:1px solid rgba(2,8,20,.08); border-radius:1rem; box-shadow:0 4px 18px rgba(2,8,20,.07); }
    .sr-card-header {
        padding: 1rem 1.25rem .75rem; border-bottom: 1px solid rgba(2,8,20,.07);
        font-weight:700; font-size:.95rem; color:#0f172a;
        display:flex; align-items:center; gap:.5rem;
    }
    .sr-card-header i { color:var(--brand); font-size:1rem; }
    .sr-card-body { padding: 1rem 1.25rem; }

    .sr-stat {
        border:1px solid rgba(2,8,20,.08); border-radius:.8rem;
        background:#f8fafc; padding:.7rem .8rem;
    }
    .sr-stat .lbl {
        font-size:.67rem; letter-spacing:.05em; text-transform:uppercase;
        color:rgba(2,8,20,.5); font-weight:700;
    }
    .sr-stat .val {
        font-size:.96rem; font-weight:700; color:#0f172a; margin-top:.15rem;
    }

    .inc-chip {
        display:inline-flex; align-items:center; gap:.35rem;
        border:1px solid rgba(2,8,20,.12); border-radius:999px;
        padding:.35rem .65rem; font-size:.77rem; color:rgba(2,8,20,.75);
        background:#fff;
    }
    .inc-chip i { color:var(--brand); }

    .tenant-banner {
        border:1px solid rgba(22,101,52,.2);
        border-radius:.9rem;
        background:linear-gradient(180deg, rgba(22,101,52,.07) 0%, rgba(22,101,52,.03) 100%);
        padding:.9rem 1rem;
    }

    .roommate-card {
        display:flex; align-items:center; gap:.7rem;
        border:1px solid rgba(2,8,20,.08); border-radius:.8rem;
        background:#fff; padding:.65rem .75rem;
    }
    .roommate-avatar {
        width:34px; height:34px; border-radius:999px;
        display:flex; align-items:center; justify-content:center;
        font-weight:700; font-size:.8rem;
        color:#166534; background:rgba(22,101,52,.1);
        border:1px solid rgba(22,101,52,.2);
    }

    .detail-grid {
        display:grid; grid-template-columns:repeat(auto-fill,minmax(130px,1fr));
        gap:.6rem;
    }
    .detail-thumb {
        position:relative; border-radius:.75rem; overflow:hidden;
        border:1px solid rgba(2,8,20,.08); cursor:pointer;
    }
    .detail-thumb img { width:100%; height:100%; object-fit:cover; aspect-ratio:4/3; display:block; }
    .thumb-label {
        position:absolute; left:.35rem; right:.35rem; bottom:.35rem;
        background:rgba(2,8,20,.65); color:#fff; font-size:.67rem;
        border-radius:.45rem; padding:.2rem .4rem;
    }

    .sr-chat-box {
        display:flex; flex-direction:column; gap:.6rem;
        max-height:280px; overflow:auto;
        border:1px solid rgba(2,8,20,.08); border-radius:.8rem;
        background:#f8fafc; padding:.8rem;
    }
    .bubble-body {
        max-width:540px; border-radius:.9rem; padding:.5rem .72rem;
        font-size:.84rem; line-height:1.45;
    }
    .bubble-body.mine { background:#166534; color:#fff; }
    .bubble-body.theirs { background:#fff; border:1px solid rgba(2,8,20,.1); color:#0f172a; }
    .msg-meta { margin-top:.18rem; font-size:.68rem; color:rgba(2,8,20,.55); }

    .sr-book-price {
        font-size:1.5rem; line-height:1; font-weight:800; color:#0f172a;
    }
    .sr-book-price span {
        font-size:.8rem; font-weight:500; color:rgba(2,8,20,.6); margin-left:.15rem;
    }

    .btn-outline-brand {
        border:1px solid color-mix(in srgb, var(--brand) 45%, #ffffff);
        color:var(--brand);
        background:#fff;
    }
    .btn-outline-brand:hover {
        background:color-mix(in srgb, var(--brand) 8%, #ffffff);
        color:var(--brand);
    }

    .sr-status-available {
        background: rgba(22,101,52,.12);
        border: 1px solid rgba(22,101,52,.24);
        color: #166534;
    }

    #lbOverlay {
        position:fixed; inset:0; background:rgba(2,8,20,.88);
        display:none; align-items:center; justify-content:center;
        z-index:2000; padding:1rem;
    }
    #lbOverlay.active { display:flex; }
    #lbImg { max-width:92vw; max-height:84vh; border-radius:.65rem; }
    .lb-close, .lb-nav {
        position:absolute; border:0; border-radius:999px;
        width:38px; height:38px; display:flex; align-items:center; justify-content:center;
        background:rgba(255,255,255,.16); color:#fff;
    }
    .lb-close { top:1rem; right:1rem; }
    .lb-prev { left:1rem; }
    .lb-next { right:1rem; }
    .lb-label {
        position:absolute; left:50%; transform:translateX(-50%); bottom:1rem;
        background:rgba(2,8,20,.68); color:#fff;
        border-radius:.5rem; font-size:.76rem; padding:.35rem .6rem;
    }

    @media (max-width: 991.98px) {
        .sr-hero, .sr-hero-placeholder { aspect-ratio: 16/8; }
    }
</style>
@endpush

@section('content')
@php
    $img = $room->image_path ?: ($room->property->image_path ?? null);
    $rawNum = trim((string) ($room->room_number ?? ''));
    $displayNum = preg_replace('/^room\s*[:#-]?\s*/i', '', $rawNum) ?: $rawNum;

    $inclusions = collect(preg_split('/[,\n;]+/', $room->inclusions ?? ''))->map('trim')->filter()->values();
    $propertyInclusionLabels = (array) config('property_amenities.flat', []);
    $propertyInclusions = collect((array) ($room->property->building_inclusions ?? []))
        ->map(fn ($key) => $propertyInclusionLabels[$key] ?? null)
        ->filter()
        ->values();
    $detailPhotos = $room->roomImages ?? collect();

    $availableSlots = $room->getAvailableSlots();
    $occupancy = $room->getOccupancyDisplay();
    $isFullCapacity = $availableSlots === 0;
    $isInMaintenance = $room->status === 'maintenance';

    $statusMap = [
        'available' => ['label' => 'Available', 'css' => 'sr-status-available', 'icon' => 'bi-check-circle-fill'],
        'occupied' => ['label' => 'Occupied', 'css' => 'sr-status-occupied', 'icon' => 'bi-x-circle-fill'],
        'maintenance' => ['label' => 'Maintenance', 'css' => 'sr-status-maintenance', 'icon' => 'bi-tools'],
    ];

    if ($isInMaintenance) {
        $sc = $statusMap['maintenance'];
    } elseif ($isFullCapacity) {
        $sc = ['label' => 'Full (' . $occupancy . ')', 'css' => 'sr-status-occupied', 'icon' => 'bi-x-circle-fill'];
    } else {
        $sc = ['label' => 'Available (' . $availableSlots . ' slot' . ($availableSlots > 1 ? 's' : '') . ')', 'css' => 'sr-status-available', 'icon' => 'bi-check-circle-fill'];
    }

    $inclusionIcons = [
        'wifi' => 'bi-wifi', 'aircon' => 'bi-thermometer-snow',
        'electric' => 'bi-lightning-charge', 'fan' => 'bi-wind',
        'water' => 'bi-droplet', 'parking' => 'bi-p-circle',
        'cable' => 'bi-tv', 'laundry' => 'bi-bag',
        'kitchen' => 'bi-egg-fried', 'ref' => 'bi-snow2', 'refrigerator' => 'bi-snow2',
    ];

    $landlordId = $room->property->landlord_id ?? null;
    $landlordName = $room->property->landlord->full_name ?? 'Landlord';
    $tenantMode = $tenantMode ?? false;

    $student = auth()->user();
    $schoolIdVerificationStatus = (string) ($student->school_id_verification_status ?? '');
    if ($schoolIdVerificationStatus === '') {
        $hasVerificationDocument = filled($student->school_id_path) || filled($student->enrollment_proof_path);
        $schoolIdVerificationStatus = $hasVerificationDocument ? 'pending' : 'not_submitted';
    }
    $bookingLockedBySchoolId = $schoolIdVerificationStatus !== 'approved';

    if ($schoolIdVerificationStatus === 'rejected') {
        $bookingLockMessage = 'Verification rejected. Upload a corrected School ID or COR/COE in Student Setup to unlock booking.';
    } elseif ($schoolIdVerificationStatus === 'not_submitted') {
        $bookingLockMessage = 'Upload your School ID or COR/COE in Student Setup to unlock booking.';
    } else {
        $bookingLockMessage = 'Booking is locked while your academic verification is pending admin approval.';
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
                'rules' => $rules,
            ];
        })
        ->filter(fn ($section) => $section['rules']->isNotEmpty())
        ->values();
@endphp

@if($tenantMode)
    <div class="sr-back-bar">
        <a href="{{ route('student.dashboard') }}"><i class="bi bi-arrow-left me-1"></i>Dashboard</a>
        <span class="sep"><i class="bi bi-chevron-right"></i></span>
        <span class="cur">My Room</span>
    </div>

    <div class="tenant-banner mb-3">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div>
                <div class="fw-semibold">You are a current tenant</div>
                <div class="text-muted small">Explore is locked to your assigned room.</div>
            </div>
            <span class="badge rounded-pill sr-status-available px-3">Active Stay</span>
        </div>
    </div>

    <div class="mb-3">
        @if(!empty($img))
            <img src="{{ asset('storage/'.$img) }}" alt="Room cover" class="sr-hero">
        @else
            <div class="sr-hero-placeholder"><span class="text-center"><i class="bi bi-image fs-2 d-block mb-1"></i><small>No cover photo</small></span></div>
        @endif
    </div>

    <div class="row g-3">
        <div class="col-12 col-lg-8">
            <div class="sr-card mb-3">
                <div class="sr-card-header">
                    <i class="bi bi-door-open"></i> My Room Summary
                </div>
                <div class="sr-card-body">
                    <div class="mb-3">
                        <h4 class="fw-bold mb-1">Room {{ $displayNum }}</h4>
                        <div class="text-muted small"><i class="bi bi-building me-1"></i>{{ $room->property->name ?? '—' }}</div>
                        <div class="text-muted small mt-1"><i class="bi bi-geo-alt me-1"></i>{{ $room->property->address ?? '—' }}</div>
                    </div>

                    <div class="row g-2">
                        <div class="col-6 col-sm-4">
                            <div class="sr-stat">
                                <div class="lbl">Monthly Rent</div>
                                <div class="val" style="color:var(--brand);">₱{{ number_format((float)$room->price, 0) }}</div>
                            </div>
                        </div>
                        <div class="col-6 col-sm-4">
                            <div class="sr-stat">
                                <div class="lbl">Occupancy</div>
                                <div class="val">{{ $occupancy }}</div>
                            </div>
                        </div>
                        <div class="col-12 col-sm-4">
                            <div class="sr-stat">
                                <div class="lbl">Landlord</div>
                                <div class="val" style="font-size:.88rem;">{{ $landlordName }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-2 mt-2">
                        <div class="col-12 col-sm-6">
                            <div class="sr-stat">
                                <div class="lbl">Check-in</div>
                                <div class="val">{{ $tenantBooking?->check_in?->format('M j, Y') ?? '—' }}</div>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6">
                            <div class="sr-stat">
                                <div class="lbl">Check-out</div>
                                <div class="val">{{ $tenantBooking?->check_out?->format('M j, Y') ?? '—' }}</div>
                            </div>
                        </div>
                    </div>

                    @if($propertyInclusions->isNotEmpty())
                    <div class="mt-3">
                        <div class="mb-2" style="font-size:.68rem;font-weight:700;letter-spacing:.07em;text-transform:uppercase;color:rgba(2,8,20,.45);">Boarding House Inclusions</div>
                        <div class="d-flex flex-wrap gap-2">
                            @foreach($propertyInclusions as $inclusion)
                            <span class="inc-chip"><i class="bi bi-shield-check"></i>{{ $inclusion }}</span>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <div class="sr-card">
                <div class="sr-card-header"><i class="bi bi-people"></i> Roommates</div>
                <div class="sr-card-body">
                    @if($roommates->isNotEmpty())
                        <div class="d-grid gap-2">
                            @foreach($roommates as $mate)
                                @php $name = $mate->student->full_name ?? ($mate->student->name ?? 'Tenant'); @endphp
                                <div class="roommate-card">
                                    <div class="roommate-avatar">{{ strtoupper(substr($name, 0, 1)) }}</div>
                                    <div>
                                        <div class="fw-semibold">{{ $name }}</div>
                                        <div class="text-muted small">Checked in {{ $mate->check_in?->format('M j, Y') }}</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-muted small">No roommates listed for this room yet.</div>
                    @endif
                </div>
            </div>

            <div class="sr-card mt-3">
                <div class="sr-card-header"><i class="bi bi-file-text"></i> House Rules</div>
                <div class="sr-card-body">
                    @forelse($houseRuleSections as $section)
                        <div class="mb-3">
                            <div class="small text-uppercase text-muted fw-semibold mb-2">{{ $section['label'] }}</div>
                            <ul class="mb-0 ps-3" style="font-size:.86rem;color:rgba(2,8,20,.75);line-height:1.55;">
                                @foreach($section['rules'] as $rule)
                                    <li>{{ $rule }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @empty
                        <div class="text-muted small">No house rules configured for this property yet.</div>
                    @endforelse
                </div>
            </div>

            <div class="sr-card mt-3">
                <div class="sr-card-header">
                    <i class="bi bi-star-half"></i> Tenant Feedback
                    @if($avgRating)
                    <span class="ms-auto d-flex align-items-center gap-1" style="font-size:.82rem;">
                        @for($s=1;$s<=5;$s++)
                            <i class="bi {{ $s <= round($avgRating) ? 'bi-star-fill' : ($s - $avgRating < 1 && $s - $avgRating > 0 ? 'bi-star-half' : 'bi-star') }}" style="color:#f59e0b;font-size:.8rem;"></i>
                        @endfor
                        <span class="fw-bold" style="color:#0f172a;">{{ number_format($avgRating,1) }}</span>
                        <span class="text-muted" style="font-size:.75rem;">({{ $feedbacks->count() }} {{ $feedbacks->count() === 1 ? 'review' : 'reviews' }})</span>
                    </span>
                    @endif
                </div>
                <div class="sr-card-body d-flex flex-column flex-sm-row justify-content-between align-items-start gap-2">
                    <div class="text-muted small">Feedback is now managed on a dedicated page for better review and submission flow.</div>
                    <a href="{{ route('student.rooms.feedback_page', $room->id) }}" class="btn btn-outline-brand rounded-pill px-3">
                        <i class="bi bi-box-arrow-up-right me-1"></i>Open Feedback Page
                    </a>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-4">
            <div class="sr-card">
                <div class="sr-card-header"><i class="bi bi-shield-check"></i> Tenant Actions</div>
                <div class="sr-card-body">
                    <div class="d-grid gap-2">
                        <a class="btn btn-outline-secondary" href="{{ route('student.onboarding.index') }}">View onboarding</a>
                        <a class="btn btn-outline-secondary" href="{{ route('student.bookings.index') }}">View requests</a>
                        <a class="btn btn-outline-secondary" href="{{ route('messages.index') }}">Message landlord</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@else
{{-- Breadcrumb --}}
<div class="sr-back-bar">
    <a href="{{ route('student.dashboard') }}#browse-rooms"><i class="bi bi-arrow-left me-1"></i>Browse Rooms</a>
    <span class="sep"><i class="bi bi-chevron-right"></i></span>
    <span class="cur">Room {{ $displayNum }}</span>
</div>

{{-- Hero --}}
<div class="mb-3">
    @if(!empty($img))
        <img src="{{ asset('storage/'.$img) }}" alt="Room cover" class="sr-hero">
    @else
        <div class="sr-hero-placeholder"><span class="text-center"><i class="bi bi-image fs-2 d-block mb-1"></i><small>No cover photo</small></span></div>
    @endif
</div>

<div class="row g-3">

    {{-- ════ Left ════ --}}
    <div class="col-12 col-lg-8">

        {{-- Room info --}}
        <div class="sr-card mb-3">
            <div class="sr-card-header">
                <i class="bi bi-door-open"></i> Room Details
                <span class="badge rounded-pill {{ $sc['css'] }} ms-auto d-flex align-items-center gap-1 px-3" style="font-size:.74rem;">
                    <i class="bi {{ $sc['icon'] }}"></i> {{ $sc['label'] }}
                </span>
            </div>
            <div class="sr-card-body">
                <div class="mb-3">
                    <h4 class="fw-bold mb-1">Room {{ $displayNum }}</h4>
                    <div class="text-muted small"><i class="bi bi-building me-1"></i>{{ $room->property->name ?? '—' }}</div>
                    <div class="text-muted small mt-1"><i class="bi bi-geo-alt me-1"></i>{{ $room->property->address ?? '—' }}</div>
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-6 col-sm-4">
                        <div class="sr-stat">
                            <div class="lbl">Monthly Rent</div>
                            <div class="val" style="color:var(--brand);">₱{{ number_format((float)$room->price, 0) }}</div>
                        </div>
                    </div>
                    <div class="col-6 col-sm-4">
                        <div class="sr-stat">
                            <div class="lbl">Occupancy</div>
                            <div class="val">{{ $occupancy }}</div>
                            <div style="font-size:.65rem;color:rgba(2,8,20,.45);margin-top:.2rem;">{{ $availableSlots }} slot{{ $availableSlots > 1 ? 's' : '' }} left</div>
                        </div>
                    </div>
                    <div class="col-12 col-sm-4">
                        <div class="sr-stat">
                            <div class="lbl">Landlord</div>
                            <div class="val" style="font-size:.88rem;">{{ $landlordName }}</div>
                        </div>
                    </div>
                </div>

                @if($inclusions->isNotEmpty())
                <div>
                    <div class="mb-2" style="font-size:.68rem;font-weight:700;letter-spacing:.07em;text-transform:uppercase;color:rgba(2,8,20,.45);">Inclusions</div>
                    <div class="d-flex flex-wrap gap-2">
                        @foreach($inclusions as $inc)
                        @php $ik=strtolower($inc); $icon=collect($inclusionIcons)->first(fn($v,$k)=>str_contains($ik,$k))?? 'bi-check2-circle'; @endphp
                        <span class="inc-chip"><i class="bi {{ $icon }}"></i>{{ $inc }}</span>
                        @endforeach
                    </div>
                </div>
                @endif

                @if($propertyInclusions->isNotEmpty())
                <div class="mt-3">
                    <div class="mb-2" style="font-size:.68rem;font-weight:700;letter-spacing:.07em;text-transform:uppercase;color:rgba(2,8,20,.45);">Boarding House Inclusions</div>
                    <div class="d-flex flex-wrap gap-2">
                        @foreach($propertyInclusions as $inclusion)
                        <span class="inc-chip"><i class="bi bi-shield-check"></i>{{ $inclusion }}</span>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        </div>

        {{-- Detail photos --}}
        @if($detailPhotos->isNotEmpty())
        <div class="sr-card mb-3">
            <div class="sr-card-header"><i class="bi bi-images"></i> Room Photos</div>
            <div class="sr-card-body">
                <div class="detail-grid">
                    @foreach($detailPhotos as $idx => $photo)
                    <div class="detail-thumb" onclick="openLb({{ $idx }})"
                         data-src="{{ asset('storage/'.$photo->image_path) }}"
                         data-label="{{ $photo->label ?? '' }}">
                        <img src="{{ asset('storage/'.$photo->image_path) }}" alt="{{ $photo->label ?? 'Room photo' }}" loading="lazy">
                        @if(!empty($photo->label))<div class="thumb-label">{{ $photo->label }}</div>@endif
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        {{-- Chat / Inquiry --}}
        <div class="sr-card">
            <div class="sr-card-header"><i class="bi bi-chat-dots"></i> Ask the Landlord</div>
            <div class="sr-card-body">
                <p class="text-muted small mb-3">Have questions? Message <strong>{{ $landlordName }}</strong> directly — no booking required.</p>

                @if($thread->isNotEmpty())
                <div class="sr-chat-box mb-3" id="chatBox">
                    @foreach($thread as $msg)
                    @php $mine=(int)$msg->sender_id===Auth::id(); @endphp
                    <div class="msg-bubble d-flex flex-column {{ $mine ? 'align-self-end align-items-end' : 'align-self-start align-items-start' }}">
                        <div class="bubble-body {{ $mine ? 'mine' : 'theirs' }}">{{ $msg->body }}</div>
                        <div class="msg-meta">
                            {{ $mine ? 'You' : $landlordName }} · {{ $msg->created_at->diffForHumans() }}
                            @if($mine && $msg->read_at)<i class="bi bi-check2-all ms-1" style="color:var(--brand);"></i>@endif
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="rounded-3 mb-3 p-3 text-center" style="background:#f8fafc;border:1px solid rgba(2,8,20,.06);">
                    <i class="bi bi-chat-square-text fs-4 d-block mb-1" style="color:rgba(2,8,20,.25);"></i>
                    <span class="text-muted small">No messages yet. Be the first to ask!</span>
                </div>
                @endif

                @if($landlordId)
                <form method="POST" action="{{ route('student.rooms.inquire', $room->id) }}">
                    @csrf
                    @error('body')<div class="alert alert-danger py-2 small mb-2">{{ $message }}</div>@enderror
                    <textarea name="body"
                              class="form-control rounded-3 @error('body') is-invalid @enderror"
                              rows="2"
                              placeholder="e.g. Is this room still available? When can I visit?"
                              style="resize:none;font-size:.88rem;border-color:rgba(2,8,20,.12);">{{ old('body') }}</textarea>
                    <button type="submit" class="btn btn-brand w-100 mt-2 rounded-pill fw-semibold" style="font-size:.88rem;">
                        <i class="bi bi-send me-1"></i> Send Message
                    </button>
                </form>
                @endif
            </div>
        </div>

        {{-- ── Tenant Feedback ── --}}
        <div class="sr-card mt-3">
            <div class="sr-card-header">
                <i class="bi bi-star-half"></i> Tenant Feedback
                @if($avgRating)
                <span class="ms-auto d-flex align-items-center gap-1" style="font-size:.82rem;">
                    @for($s=1;$s<=5;$s++)
                        <i class="bi {{ $s <= round($avgRating) ? 'bi-star-fill' : ($s - $avgRating < 1 && $s - $avgRating > 0 ? 'bi-star-half' : 'bi-star') }}" style="color:#f59e0b;font-size:.8rem;"></i>
                    @endfor
                    <span class="fw-bold" style="color:#0f172a;">{{ number_format($avgRating,1) }}</span>
                    <span class="text-muted" style="font-size:.75rem;">({{ $feedbacks->count() }} {{ $feedbacks->count() === 1 ? 'review' : 'reviews' }})</span>
                </span>
                @endif
            </div>
            <div class="sr-card-body d-flex flex-column flex-sm-row justify-content-between align-items-start gap-2">
                <div class="text-muted small">Feedback has its own page so ratings, comments, and submission are all in one place.</div>
                <a href="{{ route('student.rooms.feedback_page', $room->id) }}" class="btn btn-outline-brand rounded-pill px-3">
                    <i class="bi bi-box-arrow-up-right me-1"></i>Open Feedback Page
                </a>
            </div>
        </div>

    </div>

    {{-- ════ Right sidebar ════ --}}
    <div class="col-12 col-lg-4">

        {{-- Book card --}}
        <div class="sr-card mb-3" style="position:sticky;top:5.5rem;">
            <div class="sr-card-body">
                <div class="sr-book-price mb-1">₱{{ number_format((float)$room->price, 0) }}<span>/month</span></div>
                <div class="text-muted small mb-1"><i class="bi bi-people me-1"></i>{{ $occupancy }} pax • {{ $availableSlots }} slot{{ $availableSlots > 1 ? 's' : '' }} available</div>
                @if($avgRating)
                <div class="d-flex align-items-center gap-1 mb-3">
                    @for($s=1;$s<=5;$s++)
                    <i class="bi {{ $s <= round($avgRating) ? 'bi-star-fill' : 'bi-star' }}" style="color:#f59e0b;font-size:.8rem;"></i>
                    @endfor
                    <span style="font-size:.8rem;font-weight:700;color:#0f172a;">{{ number_format($avgRating,1) }}</span>
                    <span class="text-muted" style="font-size:.75rem;">({{ $feedbacks->count() }})</span>
                </div>
                @else
                <div class="mb-3"></div>
                @endif

                @if($isInMaintenance)
                    <button class="btn btn-secondary w-100 rounded-pill fw-semibold mb-2" disabled>
                        <i class="bi bi-tools me-1"></i> In Maintenance
                    </button>
                    <div class="text-center text-muted small">This room is currently under maintenance</div>
                @elseif($bookingLockedBySchoolId)
                    <button type="button" class="btn btn-secondary w-100 rounded-pill fw-semibold mb-2" disabled title="{{ $bookingLockMessage }}">
                        <i class="bi bi-shield-lock me-1"></i> Booking Locked
                    </button>
                    <div class="text-center text-muted" style="font-size:.72rem;">{{ $bookingLockMessage }}</div>
                @elseif($hasExistingBooking && !$existingBookingIsThisRoom)
                    {{-- Student has a booking but not for this room --}}
                    <button type="button" class="btn btn-secondary w-100 rounded-pill fw-semibold mb-2" disabled title="You already have an active booking. Complete or cancel it to book another room.">
                        <i class="bi bi-lock me-1"></i> Booking Not Available
                    </button>
                    <div class="text-center text-muted" style="font-size:.72rem;">You already have an active booking. Complete or cancel it to book another room.</div>
                @elseif($existingBookingIsThisRoom)
                    {{-- Student already has a booking for this room --}}
                    <button type="button" class="btn btn-success w-100 rounded-pill fw-semibold mb-2" disabled>
                        <i class="bi bi-check-circle me-1"></i> Already Booked
                    </button>
                    <div class="text-center text-muted" style="font-size:.72rem;">You have an active booking for this room</div>
                @elseif($isFullCapacity)
                    {{-- Room is at full capacity --}}
                    <button type="button" class="btn btn-secondary w-100 rounded-pill fw-semibold mb-2" disabled title="This room is at full capacity.">
                        <i class="bi bi-exclamation-circle me-1"></i> Room Full
                    </button>
                    <div class="text-center text-muted" style="font-size:.72rem;">All {{ $room->capacity }} slots are occupied</div>
                @else
                    {{-- Student can book this room --}}
                    <button type="button" class="btn btn-brand w-100 rounded-pill fw-semibold mb-2"
                            data-bs-toggle="modal" data-bs-target="#bookConfirmModal">
                        <i class="bi bi-calendar-check me-1"></i> Book This Room
                    </button>
                    <div class="text-center text-muted" style="font-size:.72rem;">You won't be charged yet</div>
                @endif

                <hr class="my-3" style="border-color:rgba(2,8,20,.08);">
                <div class="d-flex flex-column gap-2" style="font-size:.8rem;color:rgba(2,8,20,.6);">
                    <div><i class="bi bi-shield-check me-2" style="color:var(--brand);"></i>Verified boarding house</div>
                    <div><i class="bi bi-chat-dots me-2" style="color:#3b82f6;"></i>Message the landlord above</div>
                    <div><i class="bi bi-arrow-counterclockwise me-2"></i>Cancel anytime while pending</div>
                </div>
            </div>
        </div>

        {{-- Property card --}}
        <div class="sr-card">
            <div class="sr-card-header"><i class="bi bi-building"></i> Property</div>
            <div class="p-0 overflow-hidden" style="border-radius:0 0 1rem 1rem;">
                @if($room->property->image_path)
                    <img src="{{ asset('storage/'.$room->property->image_path) }}"
                         alt="{{ $room->property->name }}"
                         style="width:100%;aspect-ratio:16/9;object-fit:cover;display:block;">
                @endif
                <div class="p-3">
                    <div class="fw-semibold">{{ $room->property->name ?? '—' }}</div>
                    <div class="text-muted small mt-1"><i class="bi bi-geo-alt me-1"></i>{{ $room->property->address ?? '—' }}</div>
                    <div class="text-muted small mt-1"><i class="bi bi-person me-1"></i>{{ $landlordName }}</div>
                </div>
            </div>
        </div>

    </div>
</div>
@endif

{{-- Lightbox --}}
<div id="lbOverlay" onclick="closeLbOutside(event)">
    <button class="lb-close" onclick="closeLb()"><i class="bi bi-x-lg"></i></button>
    <button class="lb-nav lb-prev" onclick="lbNav(-1,event)"><i class="bi bi-chevron-left"></i></button>
    <img id="lbImg" src="" alt="">
    <div id="lbLbl" class="lb-label" style="display:none;"></div>
    <button class="lb-nav lb-next" onclick="lbNav(1,event)"><i class="bi bi-chevron-right"></i></button>
</div>
@endsection

@push('scripts')
<script>
    // ── Sidebar panel buttons: redirect to dashboard with the correct hash ──
    (function() {
        const dashUrl = '{{ route('student.dashboard') }}';
        document.addEventListener('click', function(e) {
            const btn = e.target.closest('[data-panel-target],[data-panel-jump]');
            if (!btn) return;
            const panel = btn.dataset.panelTarget || btn.dataset.panelJump;
            if (panel) {
                e.preventDefault();
                window.location.href = dashUrl + '#' + panel;
            }
        });
    })();

    // Scroll chat to bottom
    const chatBox = document.getElementById('chatBox');
    if (chatBox) chatBox.scrollTop = chatBox.scrollHeight;

    // Lightbox
    const thumbs = [...document.querySelectorAll('.detail-thumb')];
    let cur = 0;
    function openLb(idx) {
        cur = idx; renderLb();
        document.getElementById('lbOverlay').classList.add('active');
        document.body.style.overflow = 'hidden';
    }
    function renderLb() {
        const t = thumbs[cur];
        document.getElementById('lbImg').src = t.dataset.src;
        const lbl = document.getElementById('lbLbl');
        if (t.dataset.label) { lbl.textContent = t.dataset.label; lbl.style.display = ''; }
        else { lbl.style.display = 'none'; }
    }
    function closeLb() {
        document.getElementById('lbOverlay').classList.remove('active');
        document.body.style.overflow = '';
    }
    function closeLbOutside(e) { if (e.target === document.getElementById('lbOverlay')) closeLb(); }
    function lbNav(dir, e) {
        e.stopPropagation();
        cur = (cur + dir + thumbs.length) % thumbs.length;
        renderLb();
    }
    document.addEventListener('keydown', e => {
        if (!document.getElementById('lbOverlay').classList.contains('active')) return;
        if (e.key === 'Escape') closeLb();
        if (e.key === 'ArrowLeft')  lbNav(-1, { stopPropagation: () => {} });
        if (e.key === 'ArrowRight') lbNav(1,  { stopPropagation: () => {} });
    });

</script>

{{-- Book Confirmation Modal --}}
<div class="modal fade" id="bookConfirmModal" tabindex="-1" aria-labelledby="bookConfirmLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content rounded-4 border-0 shadow">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title fw-bold" id="bookConfirmLabel">
          <i class="bi bi-calendar-check me-2" style="color:var(--brand);"></i>Confirm Booking
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body pt-2">
        <div class="d-flex align-items-start gap-3 mb-3">
          @php
            $coverImg = $room->roomImages->firstWhere('is_cover', true) ?? $room->roomImages->first();
          @endphp
          @if($coverImg)
          <img src="{{ asset('storage/' . $coverImg->image_path) }}"
               class="rounded-3 shrink-0"
               style="width:72px;height:56px;object-fit:cover;"
               alt="Room photo">
          @endif
          <div>
            <div class="fw-semibold">{{ $room->label ?? 'Room ' . $room->room_number }}</div>
            <div class="text-muted small">{{ $room->property->name ?? '' }}</div>
            <div class="fw-bold mt-1" style="color:var(--brand);">₱{{ number_format((float)$room->price, 0) }}<span class="text-muted fw-normal" style="font-size:.85rem;">/month</span></div>
          </div>
        </div>
        <div class="alert alert-light border rounded-3 mb-0" style="font-size:.85rem;">
          <i class="bi bi-info-circle me-1 text-primary"></i>
          You'll fill in your move-in details on the next step. <strong>You won't be charged yet.</strong>
        </div>
      </div>
      <div class="modal-footer border-0 pt-0">
        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
        <a href="{{ route('bookings.create', $room->id) }}" class="btn btn-brand rounded-pill px-4 fw-semibold">
          <i class="bi bi-arrow-right me-1"></i>Continue
        </a>
      </div>
    </div>
  </div>
</div>
@endpush
