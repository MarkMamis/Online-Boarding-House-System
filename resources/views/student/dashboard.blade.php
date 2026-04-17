@extends('layouts.student_dashboard')

@section('title', 'Dashboard')

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin=""/>
    <style>
        .student-panel { display: none; }
        .student-panel.active { display: block; }

        .section-card { border: 1px solid rgba(2,8,20,.08); border-radius: 1rem; background: #fff; box-shadow: 0 10px 26px rgba(2,8,20,.06); }
        .section-card .card-header { background: #fff; border-bottom: 1px solid rgba(2,8,20,.06); border-top-left-radius: 1rem; border-top-right-radius: 1rem; }
        .activity-item { border-bottom: 1px solid rgba(2,8,20,.06); padding: .85rem 0; }
        .activity-item:last-child { border-bottom: 0; padding-bottom: 0; }
        .activity-ic {
            width: 36px;
            height: 36px;
            border-radius: .9rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(22,101,52,.10);
            border: 1px solid rgba(22,101,52,.18);
            color: var(--brand);
            flex: 0 0 auto;
        }
        .map-mini { height: 260px; border-radius: 1rem; overflow: hidden; border: 1px solid rgba(2,8,20,.08); background: rgba(2,8,20,.02); }
        .map-mini-pill {
            background: #0f5132;
            color: #fff;
            border-radius: 999px;
            border: 2px solid #fff;
            font-size: .68rem;
            font-weight: 700;
            line-height: 1;
            padding: .18rem .5rem;
            white-space: nowrap;
            box-shadow: 0 8px 16px rgba(2,8,20,.22);
        }
        .map-mini-pill.map-mini-pill-empty { background: #334155; }
        .map-preview-meta {
            border: 1px solid rgba(2,8,20,.08);
            border-radius: .9rem;
            background: #fff;
            padding: .65rem .75rem;
        }
        .map-mini-popup-photo {
            width: 100%;
            height: 94px;
            border-radius: .65rem;
            overflow: hidden;
            background: linear-gradient(135deg, #dcfce7, #ecfeff);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #0f766e;
            margin-bottom: .45rem;
        }
        .map-mini-popup-photo img { width: 100%; height: 100%; object-fit: cover; display: block; }

        /* ── Airbnb-style room cards ── */
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
        .room-badge-warn { background: rgba(217,119,6,.85) !important; }

        .room-browse-body { padding: .85rem 1rem .5rem; flex: 1; }
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

        .dash-rec-card-row {
            display: flex;
            gap: .75rem;
            align-items: flex-start;
        }
        .dash-rec-card-media {
            width: 92px;
            height: 92px;
            flex: 0 0 92px;
            border-radius: .75rem;
            overflow: hidden;
            border: 1px solid rgba(2,8,20,.1);
            background: #f8fafc;
        }
        .dash-rec-card-action {
            display: flex;
            align-items: center;
        }
        .dash-rec-card-btn {
            white-space: nowrap;
            min-width: 96px;
        }

        @media (max-width: 575.98px) {
            .dash-rec-card-row {
                flex-wrap: wrap;
                gap: .65rem;
            }
            .dash-rec-card-media {
                width: 84px;
                height: 84px;
                flex: 0 0 84px;
            }
            .dash-rec-card-body {
                flex: 1 1 calc(100% - 96px);
                min-width: 0;
            }
            .dash-rec-card-action {
                width: 100%;
            }
            .dash-rec-card-btn {
                width: 100%;
                min-width: 0;
            }
        }
    </style>
@endpush

@section('content')
    @php
        $recommendedRoomsForJs = $recommendedRooms->map(function ($room) {
            return [
                'id' => $room->id,
                'lat' => $room->property->latitude,
                'lng' => $room->property->longitude,
            ];
        })->values();
    @endphp

    <script type="application/json" id="recommendedRoomsJson">{!! $recommendedRoomsForJs->toJson() !!}</script>
    <script type="application/json" id="pageFlagsJson">{!! json_encode([
        'hasReportErrors' => $errors->any(),
        'reportSubmitted' => session()->has('success'),
    ]) !!}</script>

    <div id="studentPanels">
        @if(session('booking_success'))
            <div class="alert alert-success rounded-4 mb-3">{{ session('booking_success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger rounded-4 mb-3">{{ session('error') }}</div>
        @endif

        <!-- DASHBOARD PANEL -->
        <div class="student-panel active" data-student-panel="dashboard">
            <div class="glass-card rounded-4 p-4 p-md-5 mb-4">
                @php
                    $availableRoomsCount = isset($allRooms) ? (int) $allRooms->where('status', 'available')->count() : 0;
                    $hasCurrentApprovedBooking = (bool) ($hasCurrentApprovedBooking ?? false);

                    $nextAction = [
                        'title' => 'Browse rooms and request a booking',
                        'desc' => 'Start by checking available rooms and sending a booking request to a landlord.',
                        'panel' => 'browse-rooms',
                        'icon' => 'bi-search',
                        'cta' => 'Browse rooms',
                    ];

                    if ($hasCurrentApprovedBooking) {
                        $nextAction = [
                            'title' => 'View your approved booking',
                            'desc' => 'You already have an approved booking. Booking is disabled while this stay is active.',
                            'panel' => 'onboarding',
                            'icon' => 'bi-house-check',
                            'cta' => 'Open onboarding',
                        ];
                    }

                    if (($unreadResponsesCount ?? 0) > 0) {
                        $nextAction = [
                            'title' => 'Read admin response',
                            'desc' => 'You have new response(s) on your report. Open reports to read updates.',
                            'panel' => 'reports',
                            'icon' => 'bi-flag',
                            'cta' => 'Open reports',
                        ];
                    } elseif (($unreadMessagesCount ?? 0) > 0) {
                        $nextAction = [
                            'title' => 'Reply to your messages',
                            'desc' => 'You have unread message(s). Open messages to view and reply.',
                            'panel' => 'messages',
                            'icon' => 'bi-chat-dots',
                            'cta' => 'Open messages',
                        ];
                    } elseif ($hasCurrentApprovedBooking && !empty($latestOnboarding) && ($latestOnboarding->status ?? '') !== 'completed') {
                        $nextAction = [
                            'title' => 'Continue tenant onboarding',
                            'desc' => 'Your booking is approved. Complete onboarding steps to finalize your stay.',
                            'panel' => 'onboarding',
                            'icon' => 'bi-clipboard-check',
                            'cta' => 'Continue onboarding',
                        ];
                    } elseif (($pendingBookingsCount ?? 0) > 0) {
                        $nextAction = [
                            'title' => 'Track your booking requests',
                            'desc' => 'You have pending booking request(s). Check status updates from landlords.',
                            'panel' => 'requests',
                            'icon' => 'bi-hourglass-split',
                            'cta' => 'View requests',
                        ];
                    } elseif (!empty($latestOnboarding) && ($latestOnboarding->status ?? '') !== 'completed') {
                        $nextAction = [
                            'title' => 'Continue tenant onboarding',
                            'desc' => 'Your onboarding is still in progress. Complete required steps and documents.',
                            'panel' => 'onboarding',
                            'icon' => 'bi-clipboard-check',
                            'cta' => 'Continue onboarding',
                        ];
                    }

                    $events = collect();
                    foreach (($recentBookings ?? collect())->take(6) as $b) {
                        $events->push([
                            'ts' => $b->created_at,
                            'icon' => 'bi-journal-check',
                            'title' => 'Booking request: ' . ($b->room?->property?->name ?? 'Property'),
                            'meta' => 'Status: ' . ($b->status ?? '—'),
                            'panel' => 'requests',
                        ]);
                    }
                    foreach (($recentMessages ?? collect())->take(6) as $m) {
                        $events->push([
                            'ts' => $m->created_at,
                            'icon' => 'bi-chat-dots',
                            'title' => 'Message from ' . ($m->sender?->full_name ?? 'Sender'),
                            'meta' => empty($m->read_at) ? 'Unread' : 'Read',
                            'panel' => 'messages',
                        ]);
                    }
                    foreach (($recentReports ?? collect())->take(6) as $r) {
                        $events->push([
                            'ts' => $r->created_at,
                            'icon' => 'bi-flag',
                            'title' => 'Report: ' . ($r->title ?? 'Report'),
                            'meta' => (!empty($r->admin_response) && empty($r->response_read)) ? 'New response' : ('Status: ' . ($r->status ?? '—')),
                            'panel' => 'reports',
                        ]);
                    }

                    // Keep Recent Activity informative for first-time users.

                    $nextActionUrl = match ((string) ($nextAction['panel'] ?? '')) {
                        'requests' => route('student.bookings.index'),
                        'messages' => route('messages.index'),
                        'reports' => route('student.reports.index'),
                        'onboarding' => route('student.onboarding.index'),
                        'profile' => route('student.profile.show'),
                        'property-map' => route('student.properties.map_view'),
                        default => route('student.rooms.index'),
                    };
                    if ($events->isEmpty()) {
                        $student = Auth::user();
                        $events->push([
                            'ts' => $student?->created_at,
                            'icon' => 'bi-person-check',
                            'title' => 'Account created',
                            'meta' => 'Your student account was created successfully.',
                            'panel' => 'browse-rooms',
                        ]);

                        if (!empty($student?->email_verified_at)) {
                            $events->push([
                                'ts' => $student->email_verified_at,
                                'icon' => 'bi-envelope-check',
                                'title' => 'Email verified',
                                'meta' => 'Verification complete. Your student portal access is now active.',
                                'panel' => 'profile',
                            ]);
                        } else {
                            $events->push([
                                'ts' => $student?->created_at,
                                'icon' => 'bi-envelope-exclamation',
                                'title' => 'Email verification pending',
                                'meta' => 'Verify your email to secure your account and receive important updates.',
                                'panel' => 'profile',
                            ]);
                        }
                    }

                    $events = $events->sortByDesc('ts')->take(8)->values();
                @endphp

                <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center mb-4 gap-3">
                    <div>
                        <div class="small text-muted">Student Dashboard</div>
                        <h1 class="h2 mb-1 fw-semibold">Welcome, <span class="highlight">{{ Auth::user()->full_name }}</span></h1>
                        <div class="small text-muted">Quick summary and next steps for your boarding house stay.</div>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        @if(($unreadMessagesCount ?? 0) > 0)
                            <span class="badge rounded-pill text-bg-danger">{{ $unreadMessagesCount }} unread messages</span>
                        @endif
                        @if(($pendingBookingsCount ?? 0) > 0)
                            <span class="badge rounded-pill text-bg-warning">{{ $pendingBookingsCount }} pending requests</span>
                        @endif
                        @if(($unreadResponsesCount ?? 0) > 0)
                            <span class="badge rounded-pill text-bg-danger">{{ $unreadResponsesCount }} report response(s)</span>
                        @endif
                        @if(($notificationsCount ?? 0) > 0)
                            <span class="badge rounded-pill text-bg-secondary">{{ $notificationsCount }} notifications</span>
                        @endif
                    </div>
                </div>

                @if(($ratingReminders ?? collect())->isNotEmpty())
                    <div class="alert alert-warning rounded-4 mb-4">
                        <div class="fw-semibold mb-1"><i class="bi bi-star-fill me-1"></i>Rate your completed stay</div>
                        <div class="small mb-2">You have completed booking(s) that still need a star rating.</div>
                        <div class="d-flex flex-wrap gap-2">
                            @foreach(($ratingReminders ?? collect())->take(3) as $reminder)
                                <a href="{{ route('student.rooms.show', $reminder->room_id) }}" class="btn btn-sm btn-outline-dark rounded-pill">
                                    {{ $reminder->room?->property?->name ?? 'Property' }} - {{ $reminder->room?->room_number ?? $reminder->room_id }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="row g-3 g-lg-4 mb-4">
                    <div class="col-6 col-md-3">
                        <div class="metric-tile h-100">
                            <div class="d-flex align-items-center gap-3">
                                <div class="metric-ic"><i class="bi bi-door-open"></i></div>
                                <div>
                                    <div class="metric-kpi h4 mb-0">{{ $availableRoomsCount }}</div>
                                    <div class="small metric-label">Available Rooms</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="metric-tile h-100">
                            <div class="d-flex align-items-center gap-3">
                                <div class="metric-ic"><i class="bi bi-hourglass-split"></i></div>
                                <div>
                                    <div class="metric-kpi h4 mb-0">{{ $pendingBookingsCount }}</div>
                                    <div class="small metric-label">Pending Requests</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="metric-tile h-100">
                            <div class="d-flex align-items-center gap-3">
                                <div class="metric-ic"><i class="bi bi-check2-circle"></i></div>
                                <div>
                                    <div class="metric-kpi h4 mb-0">{{ $activeBookingsCount }}</div>
                                    <div class="small metric-label">Active Booking(s)</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="metric-tile h-100">
                            <div class="d-flex align-items-center gap-3">
                                <div class="metric-ic"><i class="bi bi-chat-dots"></i></div>
                                <div>
                                    <div class="metric-kpi h4 mb-0">{{ $messagesCount }}</div>
                                    <div class="small metric-label">Messages</div>
                                    @if(($unreadMessagesCount ?? 0) > 0)
                                        <div class="small"><span class="badge rounded-pill text-bg-danger">{{ $unreadMessagesCount }} unread</span></div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                @php
                    $mapPreviewPropertyCount = (int) collect($recommendedRooms ?? collect())->pluck('property_id')->filter()->unique()->count();
                    $mapPreviewPropertyCount = max($mapPreviewPropertyCount, (int) $availableRoomsCount > 0 ? 1 : 0);
                @endphp

                <div class="section-card p-3 p-lg-4 mb-4">
                    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-2 mb-3">
                        <div class="fw-semibold"><i class="bi bi-map me-2"></i>Map Preview</div>
                        <div class="d-flex gap-2 flex-wrap">
                            <a href="{{ route('student.properties.map_view') }}" class="btn btn-sm btn-outline-secondary rounded-pill">Open full map</a>
                            <!-- <a href="{{ route('student.rooms.index') }}" class="btn btn-sm btn-brand rounded-pill px-3">Browse rooms</a> -->
                        </div>
                    </div>
                    <div class="row g-3 align-items-stretch">
                        <div class="col-12 col-lg-8">
                            <div id="propertiesMapMini" class="map-mini" data-map-url="{{ route('student.properties.map') }}"></div>
                        </div>
                        <div class="col-12 col-lg-4 d-grid gap-2">
                            <div class="map-preview-meta">
                                <div class="small text-muted">Visible Listings</div>
                                <div class="fw-semibold">Approved boarding houses nearby</div>
                            </div>
                            <div class="map-preview-meta">
                                <div class="small text-muted">Coverage</div>
                                <div class="fw-semibold">{{ $mapPreviewPropertyCount }}+ active properties</div>
                            </div>
                            <div class="map-preview-meta">
                                <div class="small text-muted">Tip</div>
                                <div class="small">Tap a price pin to preview the property, then jump straight to matching rooms.</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="section-card p-3 p-lg-4 mb-4">
                    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                        <div class="d-flex align-items-start gap-3">
                            <div class="activity-ic"><i class="bi {{ $nextAction['icon'] }}"></i></div>
                            <div>
                                <div class="fw-semibold">Next action: {{ $nextAction['title'] }}</div>
                                <div class="small text-muted">{{ $nextAction['desc'] }}</div>
                            </div>
                        </div>
                        <a href="{{ $nextActionUrl }}" class="btn btn-brand rounded-pill px-4">{{ $nextAction['cta'] }}</a>
                    </div>
                </div>

                <div class="row g-3 g-lg-4">
                    <div class="col-12 col-lg-6">
                        <div class="section-card p-3 p-lg-4 h-100">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div class="fw-semibold"><i class="bi bi-activity me-2"></i> Recent Activity</div>
                                <div class="small text-muted">Latest updates</div>
                            </div>

                            @forelse($events as $e)
                                <div class="activity-item">
                                    @if(($e['panel'] ?? '') === 'requests')
                                        <a href="{{ route('student.bookings.index') }}" class="w-100 text-start border-0 bg-transparent p-0 text-decoration-none d-block">
                                            <div class="d-flex align-items-start gap-3">
                                                <div class="activity-ic"><i class="bi {{ $e['icon'] }}"></i></div>
                                                <div class="min-w-0">
                                                    <div class="fw-semibold text-truncate text-body">{{ $e['title'] }}</div>
                                                    <div class="small text-muted">{{ $e['meta'] }} • {{ optional($e['ts'])->diffForHumans() }}</div>
                                                </div>
                                            </div>
                                        </a>
                                    @else
                                        <button type="button" class="w-100 text-start border-0 bg-transparent p-0" data-panel-jump="{{ $e['panel'] }}">
                                            <div class="d-flex align-items-start gap-3">
                                                <div class="activity-ic"><i class="bi {{ $e['icon'] }}"></i></div>
                                                <div class="min-w-0">
                                                    <div class="fw-semibold text-truncate">{{ $e['title'] }}</div>
                                                    <div class="small text-muted">{{ $e['meta'] }} • {{ optional($e['ts'])->diffForHumans() }}</div>
                                                </div>
                                            </div>
                                        </button>
                                    @endif
                                </div>
                            @empty
                                <div class="text-muted">No recent activity yet.</div>
                            @endforelse
                        </div>
                    </div>

                    <div class="col-12 col-lg-6">
                        @unless($hasCurrentApprovedBooking)
                            <div class="section-card p-3 p-lg-4 mb-3">
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <div class="fw-semibold"><i class="bi bi-stars me-2"></i> Recommended Rooms</div>
                                </div>

                                <div class="row g-3">
                                    @forelse($recommendedRooms->take(3) as $room)
                                        @php
                                            $recImg = $room->image_path ?: ($room->property->image_path ?? null);
                                            $recInclusions = collect(preg_split('/[,\n;]+/', $room->inclusions ?? ''))
                                                ->map('trim')
                                                ->filter()
                                                ->take(4);
                                        @endphp
                                        <div class="col-12">
                                            <div class="border rounded-4 bg-white shadow-sm p-3">
                                                <div class="dash-rec-card-row">
                                                    <div class="dash-rec-card-media">
                                                        @if($recImg)
                                                            <img src="{{ asset('storage/'.$recImg) }}" alt="Room image" style="width:100%;height:100%;object-fit:cover;display:block;">
                                                        @else
                                                            <div class="d-flex align-items-center justify-content-center h-100 text-muted small">
                                                                <i class="bi bi-image me-1"></i>No photo
                                                            </div>
                                                        @endif
                                                    </div>

                                                    <div class="dash-rec-card-body flex-fill min-w-0">
                                                        <div class="small text-muted text-truncate">{{ $room->property->name }}</div>
                                                        <div class="fw-semibold">{{ $room->room_number }}</div>
                                                        <div class="small text-muted">Capacity: {{ $room->capacity }} • ₱ {{ number_format($room->price,2) }}</div>

                                                        <div class="d-flex flex-wrap gap-1 mt-2">
                                                            @forelse($recInclusions as $inc)
                                                                <span class="badge text-bg-light border">{{ $inc }}</span>
                                                            @empty
                                                                <span class="small text-muted">No inclusions listed.</span>
                                                            @endforelse
                                                        </div>
                                                    </div>

                                                    <div class="dash-rec-card-action">
                                                        <a href="{{ route('student.rooms.show', $room->id) }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3 dash-rec-card-btn">View more</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="col-12"><div class="text-muted">No recommendations right now.</div></div>
                                    @endforelse
                                </div>
                            </div>
                        @endunless

                        @if($hasCurrentApprovedBooking)
                            <div class="section-card p-3 p-lg-4 mb-3">
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <div class="fw-semibold"><i class="bi bi-clipboard-check me-2"></i> Onboarding Progress</div>
                                    @if(!empty($latestOnboarding))
                                        <a class="btn btn-sm btn-outline-secondary rounded-pill" href="{{ route('student.onboarding.show', $latestOnboarding->id) }}">Open</a>
                                    @endif
                                </div>

                                @php
                                    $ob = $latestOnboarding ?? null;
                                    $obStatus = (string) ($ob->status ?? '');
                                    $docsDone = $ob && (!empty($ob->uploaded_documents) || in_array($obStatus, ['documents_uploaded', 'contract_signed', 'deposit_paid', 'completed'], true));
                                    $contractDone = $ob && (!empty($ob->contract_signed) || in_array($obStatus, ['contract_signed', 'deposit_paid', 'completed'], true));
                                    $depositDone = $ob && (!empty($ob->deposit_paid) || in_array($obStatus, ['deposit_paid', 'completed'], true));
                                    $completeDone = $ob && ($obStatus === 'completed');
                                    $stepsTotal = 4;
                                    $stepsDone = (int) ($docsDone ? 1 : 0) + (int) ($contractDone ? 1 : 0) + (int) ($depositDone ? 1 : 0) + (int) ($completeDone ? 1 : 0);
                                    $progressPct = $stepsTotal > 0 ? (int) round(($stepsDone / $stepsTotal) * 100) : 0;
                                @endphp

                                @if(!empty($latestOnboarding))
                                    <div class="small text-muted mb-2">
                                        {{ $latestOnboarding->booking?->room?->property?->name ?? 'Property' }}
                                        • {{ $latestOnboarding->booking?->room?->room_number ?? '—' }}
                                    </div>

                                    <div class="progress" role="progressbar" aria-label="Onboarding progress" aria-valuenow="{{ $progressPct }}" aria-valuemin="0" aria-valuemax="100" style="height: 10px;">
                                        <div class="progress-bar bg-success" style="width: {{ $progressPct }}%"></div>
                                    </div>
                                    <div class="d-flex justify-content-between small text-muted mt-1">
                                        <span>{{ $progressPct }}% complete</span>
                                        <span>Status: {{ $latestOnboarding->status ?? '—' }}</span>
                                    </div>

                                    <div class="row g-2 mt-3">
                                        <div class="col-6">
                                            <div class="border rounded-4 p-2 bg-white">
                                                <div class="small text-muted">1. Documents</div>
                                                @if($docsDone)
                                                    <div class="fw-semibold text-success">Done</div>
                                                @else
                                                    <div class="fw-semibold">Pending</div>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="border rounded-4 p-2 bg-white">
                                                <div class="small text-muted">2. Contract</div>
                                                @if($contractDone)
                                                    <div class="fw-semibold text-success">Done</div>
                                                @else
                                                    <div class="fw-semibold">Pending</div>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="border rounded-4 p-2 bg-white">
                                                <div class="small text-muted">3. Deposit</div>
                                                @if($depositDone)
                                                    <div class="fw-semibold text-success">Done</div>
                                                @else
                                                    <div class="fw-semibold">Pending</div>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="border rounded-4 p-2 bg-white">
                                                <div class="small text-muted">4. Complete</div>
                                                @if($completeDone)
                                                    <div class="fw-semibold text-success">Done</div>
                                                @else
                                                    <div class="fw-semibold">Pending</div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mt-3 d-flex gap-2 flex-wrap">
                                        <a href="{{ route('student.onboarding.index') }}" class="btn btn-sm btn-brand rounded-pill px-3">Go to onboarding</a>
                                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3" data-panel-jump="messages">Message landlord</button>
                                    </div>
                                @else
                                    <div class="alert alert-secondary mb-0">No onboarding record yet.</div>
                                @endif
                            </div>
                        @endif

                        
                    </div>
                </div>
            </div>
        </div>

        <!-- ROOMS PANEL -->
        @unless($hasCurrentApprovedBooking)
        <div class="student-panel" data-student-panel="browse-rooms">
            <div class="glass-card rounded-4 p-4 p-md-5 mb-4">

                {{-- Header + Search/Filter --}}
                <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
                    <div>
                        <h4 class="fw-bold mb-0">Browse Rooms</h4>
                        <div class="text-muted small mt-1">Find and explore your perfect boarding house</div>
                    </div>
                    <form method="GET" action="{{ route('student.dashboard') }}" class="d-flex flex-wrap gap-2 align-items-center">
                        <input type="text" name="q" class="form-control form-control-sm rounded-pill" style="min-width:170px;" placeholder="Search rooms or properties..." value="{{ request('q') }}">
                        <input type="number" step="0.01" min="0" name="min_price" value="{{ old('min_price', $minPrice) }}" class="form-control form-control-sm rounded-pill" style="width:100px;" placeholder="Min ₱">
                        <input type="number" step="0.01" min="0" name="max_price" value="{{ old('max_price', $maxPrice) }}" class="form-control form-control-sm rounded-pill" style="width:100px;" placeholder="Max ₱">
                        <input type="number" min="1" name="capacity" value="{{ old('capacity', $minCapacity) }}" class="form-control form-control-sm rounded-pill" style="width:90px;" placeholder="Pax">
                        <button class="btn btn-sm btn-brand rounded-pill px-3" type="submit"><i class="bi bi-search me-1"></i>Filter</button>
                        <a href="{{ route('student.dashboard') }}#browse-rooms" class="btn btn-sm btn-outline-secondary rounded-pill">Reset</a>
                    </form>
                </div>

                {{-- Property filter notice --}}
                <div id="propertyRoomFilterNotice" class="alert alert-light border rounded-4 d-none mb-4 py-2">
                    Showing rooms for <strong id="propertyRoomFilterName"></strong>.
                    <button type="button" id="clearPropertyRoomFilter" class="btn btn-sm btn-outline-secondary ms-2">Clear</button>
                </div>

                {{-- ✨ Recommended Rooms --}}
                @if($recommendedRooms->isNotEmpty())
                <div class="mb-5">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <h5 class="fw-bold mb-0">✨ Recommended For You</h5>
                        <span class="badge rounded-pill text-bg-success">{{ $recommendedRooms->count() }}</span>
                    </div>
                    <div class="row g-3">
                        @foreach($recommendedRooms as $room)
                        @php
                            $rImg = $room->image_path ?: ($room->property->image_path ?? null);
                            $rInclusions = collect(preg_split('/[,\n;]+/', $room->inclusions ?? ''))->map('trim')->filter()->take(3);
                            $availableSlots = $room->getAvailableSlots();
                            $occupancy = $room->getOccupancyDisplay();
                            $isFullCapacity = $availableSlots === 0;
                        @endphp
                        <div class="col-12 col-sm-6 col-xl-4" data-room-property-id="{{ $room->property_id }}">
                            <a href="{{ route('student.rooms.show', $room->id) }}" class="text-decoration-none">
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
                                        @else
                                            <span class="room-browse-badge-status" style="background: rgba(34,197,94,.9); border-color: rgb(34,197,94);">{{ $availableSlots }} slot{{ $availableSlots > 1 ? 's' : '' }}</span>
                                        @endif
                                    </div>
                                    <div class="room-browse-body">
                                        <div class="d-flex justify-content-between align-items-start gap-1">
                                            <div class="fw-semibold text-dark" style="font-size:.9rem;">{{ $room->property->name }}</div>
                                            @if($room->updated_at && $room->updated_at->gte($newThreshold))
                                                <span class="badge text-bg-primary shrink-0" style="font-size:.62rem;">New</span>
                                            @endif
                                        </div>
                                        <div class="text-muted mt-1" style="font-size:.76rem;">
                                            <i class="bi bi-geo-alt-fill text-danger me-1"></i>{{ Str::limit($room->property->address, 38) }}
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center mt-2">
                                            <div>
                                                <span class="fw-bold text-dark" style="font-size:.88rem;">{{ $room->room_number }}</span>
                                                <span class="text-muted ms-2" style="font-size:.75rem;"><i class="bi bi-people me-1"></i>{{ $occupancy }} / {{ $room->capacity }} pax</span>
                                            </div>
                                            <div class="fw-bold {{ $isFullCapacity ? 'text-muted' : 'text-success' }}" style="font-size:.92rem;">₱{{ number_format($room->price, 0) }}<span class="text-muted fw-normal" style="font-size:.68rem;">/mo</span></div>
                                        </div>
                                        @if($rInclusions->isNotEmpty())
                                            <div class="d-flex flex-wrap gap-1 mt-2">
                                                @foreach($rInclusions as $inc)<span class="room-inc-chip">{{ $inc }}</span>@endforeach
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
                </div>
                @endif

                {{-- All Rooms --}}
                <div>
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <h5 class="fw-bold mb-0">All Rooms</h5>
                        <span class="badge rounded-pill text-bg-light border">{{ $allRooms->filter(fn($r) => $r->hasAvailableSlots())->count() }} available</span>
                    </div>
                    <div class="row g-3">
                        @forelse($allRooms as $r)
                        @php
                            $rImg2 = $r->image_path ?: ($r->property->image_path ?? null);
                            $rInc2 = collect(preg_split('/[,\n;]+/', $r->inclusions ?? ''))->map('trim')->filter()->take(3);
                            $availableSlots2 = $r->getAvailableSlots();
                            $occupancy2 = $r->getOccupancyDisplay();
                            $isFullCapacity2 = $availableSlots2 === 0;
                        @endphp
                        <div class="col-12 col-sm-6 col-xl-4" data-room-property-id="{{ $r->property_id }}">
                            <a href="{{ route('student.rooms.show', $r->id) }}" class="text-decoration-none">
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
                                        @else
                                            @if($r->updated_at && $r->updated_at->gte($newThreshold))
                                                <span class="room-browse-badge-top"><i class="bi bi-lightning-fill me-1" style="font-size:.65rem;"></i>New</span>
                                            @endif
                                            <span class="room-browse-badge-status" style="background: rgba(34,197,94,.9); border-color: rgb(34,197,94);">{{ $availableSlots2 }} slot{{ $availableSlots2 > 1 ? 's' : '' }}</span>
                                        @endif
                                    </div>
                                    <div class="room-browse-body">
                                        <div class="fw-semibold text-dark" style="font-size:.9rem;">{{ $r->property->name }}</div>
                                        <div class="text-muted mt-1" style="font-size:.76rem;">
                                            <i class="bi bi-geo-alt-fill text-danger me-1"></i>{{ Str::limit($r->property->address, 38) }}
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center mt-2">
                                            <div>
                                                <span class="fw-bold text-dark" style="font-size:.88rem;">{{ $r->room_number }}</span>
                                                <span class="text-muted ms-2" style="font-size:.75rem;"><i class="bi bi-people me-1"></i>{{ $occupancy2 }} / {{ $r->capacity }} pax</span>
                                            </div>
                                            <div class="fw-bold {{ $isFullCapacity2 || $r->status === 'maintenance' ? 'text-muted' : 'text-success' }}" style="font-size:.92rem;">
                                                ₱{{ number_format($r->price, 0) }}<span class="text-muted fw-normal" style="font-size:.68rem;">/mo</span>
                                            </div>
                                        </div>
                                        @if($rInc2->isNotEmpty())
                                            <div class="d-flex flex-wrap gap-1 mt-2">
                                                @foreach($rInc2 as $inc)<span class="room-inc-chip">{{ $inc }}</span>@endforeach
                                            </div>
                                        @endif
                                    </div>
                                    <div class="room-browse-footer">
                                        <span class="btn btn-sm w-100 rounded-pill {{ $isFullCapacity2 || $r->status === 'maintenance' ? 'btn-outline-secondary' : 'btn-brand' }}">View Details <i class="bi bi-arrow-right ms-1"></i></span>
                                    </div>
                                </div>
                            </a>
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
        </div>
        @endunless

        <!-- MAP PANEL -->
        <div class="student-panel" data-student-panel="property-map">
            <div class="glass-card rounded-4 p-4 p-md-5 mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="fw-semibold mb-0">Property Map</h4>
                </div>

                <div class="mb-4">
                    <div id="propertiesMap" data-map-url="{{ route('student.properties.map') }}" style="height:360px; border-radius:1rem; overflow:hidden; display:none;"></div>
                </div>

                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end mb-3 gap-3">
                    <h5 class="fw-semibold mb-0">Boarding Houses</h5>
                    <form method="GET" action="{{ route('student.dashboard') }}" class="d-flex flex-wrap gap-2">
                        <input type="hidden" name="min_price" value="{{ $minPrice }}">
                        <input type="hidden" name="max_price" value="{{ $maxPrice }}">
                        <input type="hidden" name="capacity" value="{{ $minCapacity }}">
                        <div style="flex: 1 1 240px; min-width: 200px;">
                            <input type="text" name="q" class="form-control form-control-sm" placeholder="Search name or address" value="{{ request('q') }}">
                        </div>
                        <button class="btn btn-sm btn-brand" type="submit">Search</button>
                        <a href="{{ route('student.dashboard') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                    </form>
                </div>
                <div class="row g-3">
                    @forelse($allProperties as $prop)
                        <div class="col-12 col-md-6 col-lg-4">
                            <div class="border rounded-4 bg-white shadow-sm p-3 h-100 d-flex flex-column">
                                <div class="d-flex justify-content-between align-items-start mb-1">
                                    <div class="fw-semibold">{{ $prop->name }}</div>
                                    <span class="badge text-bg-light">{{ $prop->rooms_available_live }} / {{ $prop->rooms_total_live }} available</span>
                                </div>
                                <div class="small text-muted mb-1">By: {{ $prop->landlord->full_name ?? 'Landlord' }}</div>
                                <div class="small mb-2">{{ Str::limit($prop->description, 90) ?: 'No description provided.' }}</div>
                                @if($prop->price_min !== null || $prop->price_max !== null)
                                    <div class="small mb-2">Price Range: ₱{{ number_format($prop->price_min,0) }} - ₱{{ number_format($prop->price_max,0) }}</div>
                                @endif
                                <div class="mt-auto d-flex justify-content-between align-items-center">
                                    <div class="small text-muted">Added {{ $prop->created_at->diffForHumans() }}</div>
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-brand"
                                        data-view-rooms-for-property="{{ $prop->id }}"
                                        data-property-name="{{ $prop->name }}"
                                    >View Rooms</button>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12"><div class="alert alert-secondary mb-0">No boarding houses found yet.</div></div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- REQUESTS PANEL -->
        <div class="student-panel" data-student-panel="requests">
            <div class="glass-card rounded-4 p-4 p-md-5 mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="fw-semibold mb-0">Requests</h4>
                </div>

                <div class="table-responsive rounded-4 shadow-sm bg-white">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Property</th>
                                <th>Room</th>
                                <th>Status</th>
                                <th>Dates</th>
                                <th>Requested</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse(($recentBookings ?? collect()) as $b)
                                <tr>
                                    <td class="fw-semibold">{{ $b->room->property->name ?? '—' }}</td>
                                    <td>{{ $b->room->room_number ?? '—' }}</td>
                                    <td>
                                        @php
                                            $st = (string) ($b->status ?? '');
                                        @endphp
                                        @if($st === 'approved')
                                            <span class="badge text-bg-success">Approved</span>
                                        @elseif($st === 'pending')
                                            <span class="badge text-bg-warning">Pending</span>
                                        @elseif($st === 'rejected')
                                            <span class="badge text-bg-danger">Rejected</span>
                                        @elseif($st === 'cancelled')
                                            <span class="badge text-bg-secondary">Cancelled</span>
                                        @else
                                            <span class="badge text-bg-light">{{ $st ?: '—' }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="small">
                                            {{ $b->check_in?->format('M d, Y') ?? '—' }} → {{ $b->check_out?->format('M d, Y') ?? '—' }}
                                        </div>
                                    </td>
                                    <td><span class="small text-muted">{{ $b->created_at?->diffForHumans() ?? '—' }}</span></td>
                                    <td class="text-end">
                                        @if(($b->status ?? '') === 'pending')
                                            <form method="POST" action="{{ route('student.bookings.cancel', $b->id) }}" class="d-inline">
                                                @csrf
                                                <input type="hidden" name="stay" value="1">
                                                <input type="hidden" name="panel" value="requests">
                                                <button type="submit" class="btn btn-sm btn-outline-danger">Cancel</button>
                                            </form>
                                        @else
                                            <span class="text-muted small">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center text-muted py-4">No requests yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- MESSAGES PANEL -->
        <div class="student-panel" data-student-panel="messages">
            <div class="glass-card rounded-4 p-4 p-md-5 mb-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="fw-semibold mb-0">Messages</h4>
                    <a href="{{ route('messages.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                        <i class="bi bi-inbox me-1"></i> Full Inbox
                    </a>
                </div>

                @php
                    $messageErrors = $errors->getBag('messages_dashboard');
                    $threads = $messageThreads ?? collect();
                    $activeThread = null;
                    $activeThreadKey = old('_thread_key');
                @endphp

                @if($messageErrors->any())
                    <div class="alert alert-danger rounded-4 mb-3">
                        <div class="fw-semibold mb-1">Please fix the following:</div>
                        <ul class="mb-0">
                            @foreach($messageErrors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="row g-3" style="min-height:420px;">

                    {{-- ── Thread list (left) ── --}}
                    <div class="col-12 col-lg-4">
                        <div class="border rounded-4 bg-white shadow-sm overflow-hidden h-100" style="min-height:420px;">
                            <div class="px-3 py-2 border-bottom bg-light" style="font-size:.76rem;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:rgba(2,8,20,.45);">Conversations</div>
                            @if($threads->isEmpty())
                                <div class="p-4 text-center text-muted small">
                                    <i class="bi bi-chat-square-text fs-3 d-block mb-2" style="color:rgba(2,8,20,.2);"></i>
                                    No conversations yet.<br>
                                    Message a landlord from a room page.
                                </div>
                            @else
                                <div class="list-group list-group-flush rounded-0" id="threadList">
                                    @foreach($threads as $tidx => $t)
                                    @php
                                        $tKey = $tidx;
                                        $isActive = $activeThreadKey !== null ? (string)$activeThreadKey === (string)$tKey : $tidx === 0;
                                        $latest = $t['latest'];
                                        $isMine = (int)$latest->sender_id === Auth::id();
                                        $preview = \Illuminate\Support\Str::limit($latest->body, 60);
                                    @endphp
                                    <button type="button"
                                            class="list-group-item list-group-item-action px-3 py-2 text-start thread-btn {{ $isActive ? 'active' : '' }}"
                                            data-thread="{{ $tKey }}">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="rounded-circle d-flex align-items-center justify-content-center shrink-0"
                                                 style="width:36px;height:36px;background:rgba(22,101,52,.10);border:1px solid rgba(22,101,52,.2);">
                                                <i class="bi bi-person" style="color:var(--brand);font-size:.85rem;"></i>
                                            </div>
                                            <div class="flex-fill min-w-0">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <span class="fw-semibold text-truncate" style="font-size:.86rem;max-width:120px;">{{ $t['other']->full_name ?? 'Landlord' }}</span>
                                                    @if($t['unread'] > 0)
                                                        <span class="badge rounded-pill text-bg-danger ms-1" style="font-size:.65rem;">{{ $t['unread'] }}</span>
                                                    @endif
                                                </div>
                                                @if($t['property'])
                                                    <div class="text-muted text-truncate" style="font-size:.72rem;">{{ $t['property']->name }}</div>
                                                @endif
                                                <div class="text-muted text-truncate" style="font-size:.73rem;">
                                                    {{ $isMine ? 'You: ' : '' }}{{ $preview }}
                                                </div>
                                            </div>
                                        </div>
                                    </button>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- ── Conversation view (right) ── --}}
                    <div class="col-12 col-lg-8">
                        <div class="border rounded-4 bg-white shadow-sm d-flex flex-column h-100" style="min-height:420px;">

                            {{-- Thread content areas --}}
                            <div class="flex-fill position-relative" style="overflow:hidden;">
                                @if($threads->isEmpty())
                                    <div class="d-flex flex-column align-items-center justify-content-center h-100 p-4 text-center text-muted">
                                        <i class="bi bi-chat-dots fs-1 mb-2" style="color:rgba(2,8,20,.15);"></i>
                                        <div class="small">Select a conversation or start a new inquiry from a room page.</div>
                                    </div>
                                @else
                                    @foreach($threads as $tidx => $t)
                                    @php $isActive = $activeThreadKey !== null ? (string)$activeThreadKey === (string)$tidx : $tidx === 0; @endphp
                                    <div class="thread-view d-flex flex-column {{ $isActive ? '' : 'd-none' }}" data-thread-view="{{ $tidx }}" style="height:100%;">
                                        {{-- header --}}
                                        <div class="px-3 py-2 border-bottom d-flex align-items-center gap-2" style="background:#fafafa;">
                                            <div class="rounded-circle d-flex align-items-center justify-content-center shrink-0"
                                                 style="width:32px;height:32px;background:rgba(22,101,52,.10);border:1px solid rgba(22,101,52,.18);">
                                                <i class="bi bi-person" style="color:var(--brand);font-size:.8rem;"></i>
                                            </div>
                                            <div>
                                                <div class="fw-semibold" style="font-size:.88rem;">{{ $t['other']->full_name ?? 'Landlord' }}</div>
                                                @if($t['property'])
                                                <div class="text-muted" style="font-size:.72rem;"><i class="bi bi-building me-1"></i>{{ $t['property']->name }}</div>
                                                @endif
                                            </div>
                                        </div>
                                        {{-- messages --}}
                                        <div class="flex-fill overflow-auto p-3 d-flex flex-column gap-2 thread-msg-list" style="max-height:280px;">
                                            @foreach(array_reverse($t['messages']) as $msg)
                                            @php $mine = (int)$msg->sender_id === Auth::id(); @endphp
                                            <div class="d-flex {{ $mine ? 'justify-content-end' : 'justify-content-start' }}">
                                                <div style="max-width:78%;">
                                                    <div class="px-3 py-2 rounded-3" style="font-size:.85rem;line-height:1.5;{{ $mine ? 'background:var(--brand);color:#fff;border-radius:1rem 1rem 0 1rem!important;' : 'background:#f1f5f9;color:#0f172a;border-radius:1rem 1rem 1rem 0!important;' }}">{{ $msg->body }}</div>
                                                    <div class="mt-1" style="font-size:.67rem;color:rgba(2,8,20,.4);text-align:{{ $mine ? 'right' : 'left' }};">
                                                        {{ $mine ? 'You' : ($t['other']->full_name ?? 'Landlord') }} · {{ $msg->created_at->diffForHumans() }}
                                                        @if($mine && $msg->read_at)<i class="bi bi-check2-all ms-1" style="color:var(--brand);"></i>@endif
                                                    </div>
                                                </div>
                                            </div>
                                            @endforeach
                                        </div>
                                        {{-- reply form --}}
                                        <div class="border-top p-3" style="background:#fafafa;">
                                            <form method="POST" action="{{ route('messages.store') }}" class="d-flex gap-2 align-items-end">
                                                @csrf
                                                <input type="hidden" name="error_bag" value="messages_dashboard">
                                                <input type="hidden" name="panel" value="messages">
                                                <input type="hidden" name="_thread_key" value="{{ $tidx }}">
                                                <input type="hidden" name="receiver_id" value="{{ $t['other']->id ?? '' }}">
                                                <input type="hidden" name="property_id" value="{{ $t['property_id'] ?? '' }}">
                                                <textarea name="body" rows="2" required
                                                          class="form-control rounded-3 flex-fill"
                                                          placeholder="Type a reply…"
                                                          style="resize:none;font-size:.86rem;border-color:rgba(2,8,20,.12);"></textarea>
                                                <button type="submit" class="btn btn-brand rounded-pill px-3 shrink-0" style="font-size:.85rem;">
                                                    <i class="bi bi-send"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                    @endforeach
                                @endif
                            </div>

                        </div>
                    </div>
                </div>

                {{-- Quick new inquiry tip --}}
                <div class="mt-3 rounded-4 p-3 d-flex align-items-center gap-2" style="background:rgba(22,101,52,.05);border:1px solid rgba(22,101,52,.15);">
                    <i class="bi bi-info-circle" style="color:var(--brand);font-size:1rem;"></i>
                    <span class="small text-muted">To start a new conversation, open a room from <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none fw-semibold" data-panel-target="browse-rooms" style="color:var(--brand);">Browse Rooms</button> and use the inquiry form.</span>
                </div>
            </div>
        </div>

        <!-- ONBOARDING PANEL -->
        <div class="student-panel" data-student-panel="onboarding">
            <div class="glass-card rounded-4 p-4 p-md-5 mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="fw-semibold mb-0">Tenant Onboarding</h4>
                </div>

                <div class="border rounded-4 bg-white shadow-sm p-4 mb-3">
                    <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                        <div class="fw-semibold">All my onboardings</div>
                        <a href="{{ route('student.onboarding.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill">View all</a>
                    </div>
                    <div class="small text-muted mb-3">Your onboarding records for each approved booking.</div>

                    @php
                        $dashOnboardings = ($allOnboardings ?? collect());
                    @endphp

                    @if($dashOnboardings->isEmpty())
                        <div class="alert alert-secondary mb-0">No onboarding records yet.</div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-sm align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Property</th>
                                        <th>Room</th>
                                        <th>Status</th>
                                        <th>Lease</th>
                                        <th class="text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($dashOnboardings as $obRow)
                                        <tr>
                                            <td class="fw-semibold">{{ $obRow->booking?->room?->property?->name ?? 'Property' }}</td>
                                            <td>{{ $obRow->booking?->room?->room_number ?? '—' }}</td>
                                            <td>
                                                <span class="badge text-bg-light">{{ $obRow->status ?? '—' }}</span>
                                            </td>
                                            <td class="small text-muted">
                                                {{ optional($obRow->booking?->check_in)->format('M d, Y') }}
                                                –
                                                {{ optional($obRow->booking?->check_out)->format('M d, Y') }}
                                            </td>
                                            <td class="text-end">
                                                <a class="btn btn-sm btn-brand rounded-pill px-3" href="{{ route('student.onboarding.show', $obRow->id) }}">Open</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>

                @if(!empty($hasCurrentApprovedBooking))
                    @php
                        $leaveErrors = $errors->getBag('leave_request');
                        $leaveItems = ($currentBookingLeaveRequests ?? collect());
                    @endphp

                    <div class="border rounded-4 bg-white shadow-sm p-4 mb-3">
                        <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                            <div class="fw-semibold">Request for leave</div>
                            <div class="small text-muted">For your current stay</div>
                        </div>
                        <div class="small text-muted mb-3">Submit a leave date and reason. Your landlord will review it.</div>

                        <form method="POST" action="{{ route('student.leave_requests.store') }}" class="mb-3">
                            @csrf
                            <input type="hidden" name="panel" value="onboarding">

                            <div class="row g-2">
                                <div class="col-12 col-md-4">
                                    <label class="form-label">Leave date</label>
                                    <input type="date" name="leave_date" value="{{ old('leave_date') }}" class="form-control @if($leaveErrors->has('leave_date')) is-invalid @endif" required>
                                    @if($leaveErrors->has('leave_date'))
                                        <div class="invalid-feedback">{{ $leaveErrors->first('leave_date') }}</div>
                                    @endif
                                </div>
                                <div class="col-12 col-md-8">
                                    <label class="form-label">Reason (optional)</label>
                                    <input type="text" name="reason" value="{{ old('reason') }}" class="form-control @if($leaveErrors->has('reason')) is-invalid @endif" maxlength="1000" placeholder="e.g., internship, family emergency, transfer...">
                                    @if($leaveErrors->has('reason'))
                                        <div class="invalid-feedback">{{ $leaveErrors->first('reason') }}</div>
                                    @endif
                                </div>
                            </div>

                            <div class="mt-3">
                                <button type="submit" class="btn btn-brand btn-sm rounded-pill px-3">Submit request</button>
                            </div>
                        </form>

                        <div class="fw-semibold mb-2">My leave requests</div>
                        @if($leaveItems->isEmpty())
                            <div class="alert alert-secondary mb-0">No leave requests yet.</div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-sm align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Leave date</th>
                                            <th>Status</th>
                                            <th>Reason</th>
                                            <th class="text-end">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($leaveItems as $lr)
                                            <tr>
                                                <td class="fw-semibold">{{ optional($lr->leave_date)->format('M d, Y') }}</td>
                                                <td><span class="badge text-bg-light">{{ $lr->status }}</span></td>
                                                <td class="small text-muted">{{ \Illuminate\Support\Str::limit((string)($lr->reason ?? ''), 60) }}</td>
                                                <td class="text-end">
                                                    @if(($lr->status ?? '') === 'pending')
                                                        <form method="POST" action="{{ route('student.leave_requests.cancel', $lr->id) }}" class="d-inline">
                                                            @csrf
                                                            <input type="hidden" name="panel" value="onboarding">
                                                            <button type="submit" class="btn btn-sm btn-outline-secondary rounded-pill">Cancel</button>
                                                        </form>
                                                    @else
                                                        <span class="text-muted small">—</span>
                                                    @endif
                                                </td>
                                            </tr>
                                            @if(!empty($lr->landlord_response))
                                                <tr>
                                                    <td colspan="4" class="small">
                                                        <span class="text-muted">Landlord response:</span> {{ $lr->landlord_response }}
                                                    </td>
                                                </tr>
                                            @endif
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                @endif

                @if(!empty($latestOnboarding))
                    @php
                        $ob = $latestOnboarding;
                        $obStatus = (string) ($ob->status ?? '');
                        $docsDone = (!empty($ob->uploaded_documents) || in_array($obStatus, ['documents_uploaded', 'contract_signed', 'deposit_paid', 'completed'], true));
                        $contractDone = (!empty($ob->contract_signed) || in_array($obStatus, ['contract_signed', 'deposit_paid', 'completed'], true));
                        $depositDone = (!empty($ob->deposit_paid) || in_array($obStatus, ['deposit_paid', 'completed'], true));
                        $completeDone = ($obStatus === 'completed');
                        $stepsTotal = 4;
                        $stepsDone = (int) ($docsDone ? 1 : 0) + (int) ($contractDone ? 1 : 0) + (int) ($depositDone ? 1 : 0) + (int) ($completeDone ? 1 : 0);
                        $progressPct = $stepsTotal > 0 ? (int) round(($stepsDone / $stepsTotal) * 100) : 0;
                        $requiredDocs = collect($ob->required_documents ?? []);
                        $uploadedDocs = collect($ob->uploaded_documents ?? []);
                    @endphp

                    <div class="border rounded-4 bg-white shadow-sm p-4 mb-3">
                        <div class="d-flex flex-column flex-md-row justify-content-between gap-3">
                            <div>
                                <div class="fw-semibold mb-1">Onboarding for</div>
                                <div class="small text-muted">
                                    {{ $latestOnboarding->booking?->room?->property?->name ?? 'Property' }}
                                    • {{ $latestOnboarding->booking?->room?->room_number ?? '—' }}
                                    • {{ optional($latestOnboarding->booking?->check_in)->format('M d, Y') }} to {{ optional($latestOnboarding->booking?->check_out)->format('M d, Y') }}
                                </div>
                            </div>
                            <div class="text-md-end">
                                <span class="badge text-bg-light">Status: {{ $latestOnboarding->status ?? '—' }}</span>
                                <div class="small text-muted mt-1">{{ $progressPct }}% complete</div>
                            </div>
                        </div>

                        <div class="progress mt-3" role="progressbar" aria-label="Onboarding progress" aria-valuenow="{{ $progressPct }}" aria-valuemin="0" aria-valuemax="100" style="height: 10px;">
                            <div class="progress-bar bg-success" style="width: {{ $progressPct }}%"></div>
                        </div>

                        <div class="row g-2 mt-3">
                            <div class="col-12 col-md-6 col-lg-3">
                                <div class="border rounded-4 p-3 h-100">
                                    <div class="small text-muted">Step 1</div>
                                    <div class="fw-semibold">Upload documents</div>
                                    <div class="small">@if($docsDone)<span class="text-success">Done</span>@else Pending @endif</div>
                                </div>
                            </div>
                            <div class="col-12 col-md-6 col-lg-3">
                                <div class="border rounded-4 p-3 h-100">
                                    <div class="small text-muted">Step 2</div>
                                    <div class="fw-semibold">Sign contract</div>
                                    <div class="small">@if($contractDone)<span class="text-success">Done</span>@else Pending @endif</div>
                                </div>
                            </div>
                            <div class="col-12 col-md-6 col-lg-3">
                                <div class="border rounded-4 p-3 h-100">
                                    <div class="small text-muted">Step 3</div>
                                    <div class="fw-semibold">Submit payment</div>
                                    <div class="small">@if($depositDone)<span class="text-success">Done</span>@else Pending @endif</div>
                                </div>
                            </div>
                            <div class="col-12 col-md-6 col-lg-3">
                                <div class="border rounded-4 p-3 h-100">
                                    <div class="small text-muted">Step 4</div>
                                    <div class="fw-semibold">Landlord approval</div>
                                    <div class="small">@if($completeDone)<span class="text-success">Done</span>@else Pending @endif</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="border rounded-4 bg-white shadow-sm p-4">
                        <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                            <div class="fw-semibold">Required documents</div>
                            <a href="{{ route('student.onboarding.show', $latestOnboarding->id) }}" class="btn btn-brand btn-sm rounded-pill px-3">Continue</a>
                        </div>
                        <div class="small text-muted mb-3">Upload and complete your requirements to finalize your stay.</div>

                        @if($requiredDocs->isEmpty())
                            <div class="alert alert-secondary mb-0">No required documents listed.</div>
                        @else
                            <div class="row g-2">
                                @foreach($requiredDocs as $docKey)
                                    @php
                                        $label = ucfirst(str_replace('_', ' ', (string) $docKey));
                                        $hasAnyUpload = $uploadedDocs->isNotEmpty();
                                    @endphp
                                    <div class="col-12 col-md-6">
                                        <div class="border rounded-4 p-3 d-flex align-items-center justify-content-between">
                                            <div>
                                                <div class="fw-semibold">{{ $label }}</div>
                                                <div class="small text-muted">@if($hasAnyUpload) Uploaded @else Not uploaded @endif</div>
                                            </div>
                                            @if($hasAnyUpload)
                                                <span class="badge text-bg-success">OK</span>
                                            @else
                                                <span class="badge text-bg-warning">Missing</span>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @else
                    <div class="alert alert-secondary mb-0">No onboarding record yet.</div>
                @endif
            </div>
        </div>

        @php
            $showRoommatesPanel = !empty($hasCurrentApprovedBooking)
                && !empty($currentApprovedBooking)
                && !empty($latestOnboarding)
                && (int) ($latestOnboarding->booking_id ?? 0) === (int) ($currentApprovedBooking->id ?? 0);
            $roommateItems = ($roommates ?? collect());
        @endphp

        @if($showRoommatesPanel)
            <!-- ROOMMATES PANEL -->
            <div class="student-panel" data-student-panel="roommates">
                <div class="glass-card rounded-4 p-4 p-md-5 mb-4">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-3">
                        <h4 class="fw-semibold mb-0">Roommates</h4>
                        <div class="small text-muted">
                            {{ $currentApprovedBooking->room?->property?->name ?? 'Property' }} • {{ $currentApprovedBooking->room?->room_number ?? '—' }}
                        </div>
                    </div>

                    <div class="section-card p-3 p-lg-4">
                        <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                            <div class="fw-semibold">Students in the same room</div>
                            <span class="badge text-bg-light">
                                {{ (int) ($roommatesCount ?? 0) }}@if(!empty($roomCapacity)) / {{ (int) $roomCapacity }}@endif
                            </span>
                        </div>
                        <div class="small text-muted mb-3">This list shows students who have onboarding records in your room.</div>

                        @if($roommateItems->isEmpty())
                            <div class="alert alert-secondary mb-0">No onboarded roommates found yet.</div>
                        @else
                            <div class="list-group list-group-flush">
                                @foreach($roommateItems as $rb)
                                    @php $isMe = (int) ($rb->student_id ?? 0) === (int) Auth::id(); @endphp
                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                        <div class="min-w-0">
                                            <div class="fw-semibold text-truncate">
                                                {{ $rb->student?->full_name ?? 'Student' }}
                                                @if($isMe)
                                                    <span class="badge rounded-pill text-bg-success ms-1">You</span>
                                                @endif
                                            </div>
                                            <div class="small text-muted">Program/Department: {{ $rb->student?->program ?? '—' }}</div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        <!-- REPORTS PANEL -->
        <div class="student-panel" data-student-panel="reports">
            <div class="glass-card rounded-4 p-4 p-md-5 mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="fw-semibold mb-0">My Reports</h4>
                    <div class="d-flex gap-2">
                        @if($canSubmitReport ?? false)
                            <button type="button" class="btn btn-sm btn-brand rounded-pill px-3" data-open-report-form="1">New report</button>
                        @endif
                    </div>
                </div>

                @if(!($canSubmitReport ?? false))
                    <div class="alert alert-info rounded-4">
                        Only verified tenants can submit reports. Reports and feedback are restricted to students with approved stays.
                    </div>
                @endif

                @if($canSubmitReport ?? false)
                <div id="newReportFormWrap" class="border rounded-4 bg-white shadow-sm p-4 mb-3" style="display:none;">
                    <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                        <div class="fw-semibold">Submit a new report</div>
                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill" data-close-report-form="1">Close</button>
                    </div>
                    <div class="small text-muted mb-3">Use this to report issues/concerns. An admin will respond.</div>

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <div class="fw-semibold mb-1">Please fix the following:</div>
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('student.reports.store') }}">
                        @csrf
                        <input type="hidden" name="from_dashboard" value="1">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label small text-muted">Title</label>
                                <input type="text" name="title" class="form-control" value="{{ old('title') }}" placeholder="e.g., Broken lock / Noise complaint" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label small text-muted">Description</label>
                                <textarea name="description" class="form-control" rows="4" placeholder="Describe the issue clearly…" required>{{ old('description') }}</textarea>
                                <div class="small text-muted mt-2">Priority is auto-detected by our AI triage model (Low, Medium, High).</div>
                            </div>
                        </div>
                        <div class="mt-3">
                            <button type="submit" class="btn btn-brand rounded-pill px-4">Submit report</button>
                        </div>
                    </form>
                </div>
                @endif

                <div class="row g-3">
                    @forelse(($recentReports ?? collect()) as $r)
                        <div class="col-12">
                            <div class="border rounded-4 bg-white shadow-sm p-3">
                                <div class="d-flex justify-content-between align-items-start gap-2">
                                    <div class="fw-semibold">{{ $r->title }}</div>
                                    <div>
                                        @php
                                            $statusLabel = match ((string) $r->status) {
                                                'pending' => 'Submitted',
                                                'in_progress' => 'Reviewed',
                                                'resolved' => 'Resolved',
                                                default => ucfirst((string) $r->status),
                                            };
                                            $statusClass = match ((string) $r->status) {
                                                'pending' => 'text-bg-secondary',
                                                'in_progress' => 'text-bg-primary',
                                                'resolved' => 'text-bg-success',
                                                default => 'text-bg-light',
                                            };
                                        @endphp
                                        <span class="badge {{ $statusClass }}">{{ $statusLabel }}</span>
                                        @if(!empty($r->admin_response) && empty($r->response_read))
                                            <span class="badge text-bg-danger ms-1">New response</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="small text-muted">{{ $r->created_at?->diffForHumans() ?? '' }}</div>
                                @if(!empty($r->admin_response))
                                    <div class="small mt-2"><strong>Response:</strong> {{ \Illuminate\Support\Str::limit($r->admin_response, 160) }}</div>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="col-12"><div class="alert alert-secondary mb-0">No reports yet.</div></div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- NOTIFICATIONS PANEL -->
        <div class="student-panel" data-student-panel="notifications">
            <div class="glass-card rounded-4 p-4 p-md-5 mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="fw-semibold mb-0">Notifications</h4>
                    <div class="d-flex gap-2">
                        <form method="POST" action="{{ route('notifications.read_all') }}">
                            @csrf
                            <input type="hidden" name="stay" value="1">
                            <input type="hidden" name="panel" value="notifications">
                            <button type="submit" class="btn btn-sm btn-outline-secondary rounded-pill">Mark all read</button>
                        </form>
                    </div>
                </div>

                @php
                    $notifItems = collect();
                    if (\Illuminate\Support\Facades\Schema::hasTable('notifications')) {
                        $u = Auth::user();
                        $notifItems = \Illuminate\Notifications\DatabaseNotification::query()
                            ->where('notifiable_type', get_class($u))
                            ->where('notifiable_id', $u->id)
                            ->orderByDesc('created_at')
                            ->limit(20)
                            ->get();
                    }
                @endphp

                <div class="row g-3">
                    @forelse(($notifItems ?? collect()) as $n)
                        <div class="col-12">
                            <div class="border rounded-4 bg-white shadow-sm p-3">
                                <div class="d-flex justify-content-between align-items-start gap-2">
                                    <div class="min-w-0">
                                        <div class="fw-semibold text-truncate">{{ data_get($n->data, 'title', 'Notification') }}</div>
                                        <div class="small text-muted">{{ $n->created_at?->diffForHumans() ?? '' }}</div>
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        @if(is_null($n->read_at))
                                            <span class="badge rounded-pill text-bg-danger">Unread</span>
                                            <form method="POST" action="{{ route('notifications.read', $n->id) }}">
                                                @csrf
                                                <input type="hidden" name="stay" value="1">
                                                <input type="hidden" name="panel" value="notifications">
                                                <button type="submit" class="btn btn-sm btn-outline-secondary rounded-pill">Mark read</button>
                                            </form>
                                        @else
                                            <span class="badge rounded-pill text-bg-light">Read</span>
                                        @endif
                                    </div>
                                </div>

                                <div class="small mt-2">{{ data_get($n->data, 'message', '') }}</div>

                                @php $url = data_get($n->data, 'url'); @endphp
                                @if(is_string($url) && $url !== '')
                                    <div class="mt-2">
                                        <a href="{{ $url }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-brand rounded-pill px-3">Open details</a>
                                        <span class="small text-muted ms-2">Opens in a new tab</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="col-12"><div class="alert alert-secondary mb-0">No notifications yet.</div></div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- PROFILE PANEL -->
        <div class="student-panel" data-student-panel="profile">
            <div class="glass-card rounded-4 p-4 p-md-5 mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="fw-semibold mb-0">Profile</h4>
                </div>

                <div class="border rounded-4 bg-white shadow-sm p-4">
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <div class="small text-muted">Full name</div>
                            <div class="fw-semibold">{{ Auth::user()->full_name }}</div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="small text-muted">Email</div>
                            <div class="fw-semibold">{{ Auth::user()->email }}</div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="small text-muted">Student ID</div>
                            <div class="fw-semibold">{{ Auth::user()->student_id ?: '—' }}</div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="small text-muted">Contact</div>
                            <div class="fw-semibold">{{ Auth::user()->contact_number ?: '—' }}</div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="small text-muted">Program</div>
                            <div class="fw-semibold">{{ Auth::user()->program ?: '—' }}</div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="small text-muted">Year level</div>
                            <div class="fw-semibold">{{ Auth::user()->year_level ?: '—' }}</div>
                        </div>
                        <div class="col-12">
                            <div class="small text-muted">Address</div>
                            <div class="fw-semibold">{{ Auth::user()->address ?: '—' }}</div>
                        </div>
                    </div>
                    <div class="mt-3">
                        <a href="{{ route('student.profile.edit') }}" class="btn btn-brand btn-sm rounded-pill px-3">Edit profile</a>
                        <a href="{{ route('student.profile.change-password') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">Change password</a>
                    </div>
                </div>
            </div>
        </div>

        @if(!empty($showApprovedBookingModal) && !empty($currentApprovedBooking))
            <div class="modal fade" id="approvedBookingModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                        <div class="p-4" style="background: linear-gradient(140deg, rgba(22,101,52,.14), rgba(167,243,208,.2)); border-bottom: 1px solid rgba(22,101,52,.14);">
                            <div class="d-flex align-items-center gap-3">
                                <div class="d-inline-flex align-items-center justify-content-center rounded-circle" style="width:46px;height:46px;background:rgba(22,101,52,.16);color:#166534;">
                                    <i class="bi bi-house-check-fill" style="font-size:1.2rem;"></i>
                                </div>
                                <div>
                                    <div class="fw-bold" style="color:#14532d;">Approved booking active</div>
                                    <div class="small text-muted">Finish onboarding to complete your move-in flow.</div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-body p-4">
                            <div class="small mb-2">
                                <span class="fw-semibold">{{ $currentApprovedBooking->room?->property?->name ?? 'Property' }}</span>
                                • {{ $currentApprovedBooking->room?->room_number ?? '—' }}
                            </div>
                            <div class="small text-muted mb-3">
                                {{ optional($currentApprovedBooking->check_in)->format('M d, Y') }} to {{ optional($currentApprovedBooking->check_out)->format('M d, Y') }}
                            </div>
                            <div class="alert alert-info rounded-3 mb-0" style="background: rgba(22,101,52,.08); border-color: rgba(22,101,52,.18); color:#14532d;">
                                Booking is disabled while you have an approved stay. Continue your onboarding steps to proceed.
                            </div>
                        </div>
                        <div class="modal-footer border-0 pt-0 px-4 pb-4">
                            <button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">Later</button>
                            <a href="{{ route('student.onboarding.index') }}" class="btn btn-brand rounded-pill px-4">View onboarding</a>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Booking Request Modal (stay on dashboard) -->
        @unless($hasCurrentApprovedBooking)
        @php
            $bookingErrors = $errors->getBag('booking');
        @endphp
        <div class="modal fade" id="bookingRequestModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form id="bookingRequestForm" method="POST" action="{{ session('booking_form_action', '') }}">
                        @csrf
                        <input type="hidden" name="stay" value="1">
                        <div class="modal-header">
                            <h5 class="modal-title">Request Booking</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div id="bookingRoomLabel" class="small text-muted mb-2">{{ session('booking_room_label') }}</div>

                            <div class="row g-2">
                                <div class="col-12 col-md-6">
                                    <label class="form-label">Check-in</label>
                                    <input type="date" name="check_in" value="{{ old('check_in') }}" class="form-control @if($bookingErrors->has('check_in')) is-invalid @endif" required>
                                    @if($bookingErrors->has('check_in'))
                                        <div class="invalid-feedback">{{ $bookingErrors->first('check_in') }}</div>
                                    @endif
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label">Check-out</label>
                                    <input type="date" name="check_out" value="{{ old('check_out') }}" class="form-control @if($bookingErrors->has('check_out')) is-invalid @endif" required>
                                    @if($bookingErrors->has('check_out'))
                                        <div class="invalid-feedback">{{ $bookingErrors->first('check_out') }}</div>
                                    @endif
                                </div>
                            </div>

                            <div class="mt-2">
                                <label class="form-label">Notes (optional)</label>
                                <textarea name="notes" rows="3" class="form-control @if($bookingErrors->has('notes')) is-invalid @endif" placeholder="Anything the landlord should know?">{{ old('notes') }}</textarea>
                                @if($bookingErrors->has('notes'))
                                    <div class="invalid-feedback">{{ $bookingErrors->first('notes') }}</div>
                                @endif
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-brand">Submit Request</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endunless
    </div>
@endsection

@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const bookingModalEl = document.getElementById('bookingRequestModal');
            const bookingForm = document.getElementById('bookingRequestForm');
            const bookingRoomLabel = document.getElementById('bookingRoomLabel');
            const approvedBookingModalEl = document.getElementById('approvedBookingModal');

            document.querySelectorAll('[data-booking-modal]').forEach(btn => {
                btn.addEventListener('click', () => {
                    const url = btn.getAttribute('data-book-url');
                    const label = btn.getAttribute('data-room-label') || '';
                    if (bookingForm && url) bookingForm.setAttribute('action', url);
                    if (bookingRoomLabel) bookingRoomLabel.textContent = label;
                });
            });

            const bookingShouldOpen = @json($errors->getBag('booking')->any() || session()->has('booking_form_action'));
            if (bookingShouldOpen && bookingModalEl) {
                const modal = bootstrap.Modal.getOrCreateInstance(bookingModalEl);
                modal.show();
            }

            const shouldShowApprovedBookingModal = @json(!empty($showApprovedBookingModal) && !empty($currentApprovedBooking));
            const openApprovedBookingModal = () => {
                if (!shouldShowApprovedBookingModal || !approvedBookingModalEl) return;
                const approvedModal = bootstrap.Modal.getOrCreateInstance(approvedBookingModalEl);
                approvedModal.show();
            };

            const sidebar = document.querySelector('.sidepanel');
            const sidebarButtons = sidebar ? sidebar.querySelectorAll('[data-panel-target]') : [];
            const panels = document.querySelectorAll('[data-student-panel]');

            const BRAND_GREEN = getComputedStyle(document.documentElement).getPropertyValue('--brand').trim() || '#166534';

            const sidebarSearch = document.getElementById('sidepanelSearch');
            if (sidebarSearch && sidebar) {
                const searchableItems = Array.from(sidebar.querySelectorAll('.list-group .list-group-item'));
                sidebarSearch.addEventListener('input', () => {
                    const q = (sidebarSearch.value || '').trim().toLowerCase();
                    searchableItems.forEach(el => {
                        const text = (el.textContent || '').trim().toLowerCase();
                        el.style.display = (!q || text.includes(q)) ? '' : 'none';
                    });
                });
            }

            const setActiveSidebar = (panelId) => {
                sidebarButtons.forEach(btn => {
                    const isActive = btn.dataset.panelTarget === panelId;
                    btn.classList.toggle('active', isActive);
                });
            };

            const showPanel = (panelId) => {
                panels.forEach(p => p.classList.toggle('active', p.dataset.studentPanel === panelId));
                setActiveSidebar(panelId);
                if (panelId === 'property-map') {
                    initPropertyMapOnce();
                }
                if (panelId === 'dashboard') {
                    initMiniMapOnce();
                    openApprovedBookingModal();
                }
                if (panelId === 'dashboard') {
                    history.replaceState(null, '', window.location.pathname + window.location.search);
                } else {
                    history.replaceState(null, '', '#' + panelId);
                }
                window.scrollTo({ top: 0, behavior: 'auto' });
            };

            const escapeHtml = (value) => {
                return String(value ?? '')
                    .replaceAll('&', '&amp;')
                    .replaceAll('<', '&lt;')
                    .replaceAll('>', '&gt;')
                    .replaceAll('"', '&quot;')
                    .replaceAll("'", '&#039;');
            };

            const browseRoomsPanel = document.querySelector('[data-student-panel="browse-rooms"]');
            const filterNoticeEl = document.getElementById('propertyRoomFilterNotice');
            const filterNameEl = document.getElementById('propertyRoomFilterName');
            const clearFilterBtn = document.getElementById('clearPropertyRoomFilter');
            const studentRoomsIndexUrl = @json(route('student.rooms.index'));

            const applyPropertyRoomFilter = (propertyId, propertyName) => {
                if (!browseRoomsPanel) return;
                const filterId = propertyId ? String(propertyId) : '';
                const hasFilter = !!filterId;

                if (filterNoticeEl && filterNameEl) {
                    filterNameEl.textContent = propertyName || '';
                    filterNoticeEl.classList.toggle('d-none', !hasFilter);
                }

                browseRoomsPanel.querySelectorAll('[data-room-property-id]').forEach(el => {
                    const isMatch = !hasFilter || String(el.getAttribute('data-room-property-id')) === filterId;
                    el.style.display = isMatch ? '' : 'none';
                });
            };

            if (clearFilterBtn) {
                clearFilterBtn.addEventListener('click', () => applyPropertyRoomFilter('', ''));
            }

            document.addEventListener('click', (ev) => {
                const trigger = ev.target && ev.target.closest ? ev.target.closest('[data-view-rooms-for-property]') : null;
                if (!trigger) return;
                ev.preventDefault();

                const propertyId = trigger.getAttribute('data-view-rooms-for-property') || '';
                const propertyName = trigger.getAttribute('data-property-name') || '';

                const query = new URLSearchParams();
                if (propertyId) query.set('property_id', propertyId);
                if (propertyName) query.set('property_name', propertyName);

                const nextUrl = query.toString()
                    ? `${studentRoomsIndexUrl}?${query.toString()}`
                    : studentRoomsIndexUrl;

                window.location.href = nextUrl;
            });

            const reportFormWrap = document.getElementById('newReportFormWrap');
            const openReportForm = () => {
                if (!reportFormWrap) return;
                reportFormWrap.style.display = '';
                const titleInput = reportFormWrap.querySelector('input[name="title"]');
                if (titleInput) {
                    setTimeout(() => titleInput.focus(), 50);
                }
            };
            const closeReportForm = () => {
                if (!reportFormWrap) return;
                reportFormWrap.style.display = 'none';
            };

            document.querySelectorAll('[data-open-report-form]').forEach(btn => {
                btn.addEventListener('click', () => {
                    showPanel('reports');
                    openReportForm();
                });
            });
            document.querySelectorAll('[data-close-report-form]').forEach(btn => {
                btn.addEventListener('click', () => closeReportForm());
            });

            sidebarButtons.forEach(btn => {
                btn.addEventListener('click', () => showPanel(btn.dataset.panelTarget));
            });

            document.querySelectorAll('[data-panel-jump]').forEach(btn => {
                btn.addEventListener('click', () => showPanel(btn.dataset.panelJump));
            });

            // ── Messages panel: thread list switching ──
            document.addEventListener('click', function(e) {
                const threadBtn = e.target.closest('.thread-btn');
                if (!threadBtn) return;
                const threadId = threadBtn.dataset.thread;
                document.querySelectorAll('.thread-btn').forEach(b => b.classList.remove('active'));
                threadBtn.classList.add('active');
                document.querySelectorAll('.thread-view').forEach(v => {
                    v.classList.toggle('d-none', v.dataset.threadView !== threadId);
                });
                const msgList = document.querySelector(`.thread-view[data-thread-view="${threadId}"] .thread-msg-list`);
                if (msgList) msgList.scrollTop = msgList.scrollHeight;
            });
            // Auto-scroll all thread message lists on load
            document.querySelectorAll('.thread-msg-list').forEach(el => { el.scrollTop = el.scrollHeight; });

            const recommendedRoomsJsonEl = document.getElementById('recommendedRoomsJson');
            const recommendedRooms = JSON.parse(recommendedRoomsJsonEl ? (recommendedRoomsJsonEl.textContent || '[]') : '[]');

            const setRecommendedDistanceFallback = () => {
                for (const r of recommendedRooms) {
                    if (!r || !r.lat || !r.lng) continue;
                    const el = document.getElementById(`distance-room-${r.id}`);
                    if (el) {
                        el.innerHTML = '<i class="fas fa-map-marker-alt text-muted me-1"></i>Location available';
                    }
                }
            };

            const haversineKm = (lat1, lon1, lat2, lon2) => {
                const R = 6371;
                const toRad = (x) => x * Math.PI / 180;
                const dLat = toRad(lat2 - lat1);
                const dLon = toRad(lon2 - lon1);
                const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
                    Math.cos(toRad(lat1)) * Math.cos(toRad(lat2)) *
                    Math.sin(dLon/2) * Math.sin(dLon/2);
                const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
                return R * c;
            };

            const updateRecommendedDistances = (userLat, userLng) => {
                for (const r of recommendedRooms) {
                    if (!r || !r.lat || !r.lng) continue;
                    const el = document.getElementById(`distance-room-${r.id}`);
                    if (!el) continue;
                    const km = haversineKm(userLat, userLng, Number(r.lat), Number(r.lng));
                    el.innerHTML = '<i class="fas fa-route text-primary me-1"></i>' + km.toFixed(1) + ' km away';
                }
            };


            let propertyMapInitialized = false;
            let propertyMapInstance = null;

            let miniMapInitialized = false;
            let miniMapInstance = null;

            const initMiniMapOnce = () => {
                if (miniMapInitialized) {
                    if (miniMapInstance) {
                        setTimeout(() => miniMapInstance.invalidateSize(), 60);
                    }
                    return;
                }

                const mapEl = document.getElementById('propertiesMapMini');
                const mapUrl = mapEl ? mapEl.dataset.mapUrl : null;
                if (!mapEl || !mapUrl) {
                    return;
                }

                fetch(mapUrl)
                    .then(r => r.json())
                    .then(data => {
                        const props = data.properties || [];

                        const formatPriceLabel = (minPrice, maxPrice) => {
                            if (minPrice === null && maxPrice === null) return 'Price TBD';
                            if (minPrice !== null && maxPrice !== null && Number(minPrice) !== Number(maxPrice)) {
                                return `₱${Number(minPrice).toLocaleString()}-₱${Number(maxPrice).toLocaleString()}`;
                            }
                            const single = minPrice !== null ? minPrice : maxPrice;
                            return `₱${Number(single).toLocaleString()}`;
                        };

                        const compactPriceLabel = (minPrice, maxPrice) => {
                            const shortPeso = (value) => {
                                const n = Number(value || 0);
                                if (!Number.isFinite(n)) return '0';
                                if (n >= 1000) {
                                    const k = n / 1000;
                                    return `${Number.isInteger(k) ? k.toFixed(0) : k.toFixed(1)}k`;
                                }
                                return n.toFixed(0);
                            };
                            if (minPrice === null && maxPrice === null) return 'TBD';
                            if (minPrice !== null && maxPrice !== null && Number(minPrice) !== Number(maxPrice)) {
                                return `₱${shortPeso(minPrice)}-₱${shortPeso(maxPrice)}`;
                            }
                            const single = minPrice !== null ? minPrice : maxPrice;
                            return `₱${shortPeso(single)}`;
                        };

                        const popupHtml = (p) => {
                            const imageHtml = p.image_url
                                ? `<img src="${escapeHtml(p.image_url)}" alt="${escapeHtml(p.name)} preview">`
                                : `<i class="bi bi-building fs-4"></i>`;

                            return `
                                <div style="min-width:220px;max-width:250px;">
                                    <div class="map-mini-popup-photo">${imageHtml}</div>
                                    <div class="fw-semibold">${escapeHtml(p.name)}</div>
                                    <div class="small text-muted mb-1">${escapeHtml(p.address || 'Address not available')}</div>
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="badge text-bg-light">${escapeHtml(String(p.available_rooms || 0))} available</span>
                                        <span class="fw-semibold" style="color:${BRAND_GREEN};">${escapeHtml(formatPriceLabel(p.price_min, p.price_max))}</span>
                                    </div>
                                    <button type='button' class='btn btn-sm btn-brand w-100' data-view-rooms-for-property='${p.id}' data-property-name='${escapeHtml(p.name)}'>View Rooms</button>
                                </div>
                            `;
                        };

                        const map = L.map('propertiesMapMini', {
                            zoomControl: true,
                            attributionControl: false,
                            scrollWheelZoom: true,
                            doubleClickZoom: true,
                            boxZoom: true,
                            keyboard: true,
                        });
                        miniMapInstance = map;
                        miniMapInitialized = true;

                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            maxNativeZoom: 19,
                            maxZoom: 22,
                            attribution: '&copy; OpenStreetMap contributors'
                        }).addTo(map);

                        const bounds = [];

                        props.slice(0, 12).forEach(p => {
                            const markerLabel = compactPriceLabel(p.price_min, p.price_max);
                            const markerWidth = Math.max(60, Math.min(132, Math.round(markerLabel.length * 6.8 + 22)));
                            const icon = L.divIcon({
                                className: 'map-mini-pill-wrap',
                                html: `<div class="map-mini-pill ${p.price_min === null && p.price_max === null ? 'map-mini-pill-empty' : ''}">${escapeHtml(markerLabel)}</div>`,
                                iconSize: [markerWidth, 26],
                                iconAnchor: [Math.round(markerWidth / 2), 13],
                                popupAnchor: [0, -10],
                            });
                            const marker = L.marker([p.lat, p.lng], { icon }).addTo(map);
                            marker.bindPopup(popupHtml(p));
                            bounds.push([p.lat, p.lng]);
                        });

                        const fit = () => {
                            if (bounds.length) {
                                map.fitBounds(bounds, { padding: [16, 16] });
                            } else {
                                map.setView([12.5, 122.0], 6);
                            }
                        };

                        fit();

                        if (navigator.geolocation) {
                            navigator.geolocation.getCurrentPosition(
                                pos => {
                                    const userLatLng = [pos.coords.latitude, pos.coords.longitude];
                                    L.circleMarker(userLatLng, {
                                        radius: 7,
                                        color: BRAND_GREEN,
                                        fillColor: BRAND_GREEN,
                                        fillOpacity: 0.9,
                                        weight: 2,
                                    }).addTo(map).bindTooltip('You are here', { direction: 'top', offset: [0, -6] });

                                    bounds.push(userLatLng);
                                    fit();
                                },
                                () => {},
                                { enableHighAccuracy: true, timeout: 6000 }
                            );
                        }

                        setTimeout(() => map.invalidateSize(), 60);
                    })
                    .catch(() => {
                        miniMapInitialized = false;
                        miniMapInstance = null;
                    });
            };

            const initPropertyMapOnce = () => {
                if (propertyMapInitialized) {
                    if (propertyMapInstance) {
                        setTimeout(() => propertyMapInstance.invalidateSize(), 60);
                    }
                    return;
                }

                const mapEl = document.getElementById('propertiesMap');
                const mapUrl = mapEl ? mapEl.dataset.mapUrl : null;
                if (!mapEl || !mapUrl) {
                    setRecommendedDistanceFallback();
                    return;
                }

                propertyMapInitialized = true;

                fetch(mapUrl)
                    .then(r => r.json())
                    .then(data => {
                        const props = data.properties || [];
                        if (!props.length) {
                            return;
                        }

                        mapEl.style.display = 'block';

                        const map = L.map('propertiesMap');
                        propertyMapInstance = map;

                        const bounds = [];
                        const markers = [];

                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            maxNativeZoom: 19,
                            maxZoom: 22,
                            attribution: '&copy; OpenStreetMap contributors'
                        }).addTo(map);

                        props.forEach(p => {
                            const marker = L.marker([p.lat, p.lng]).addTo(map);
                            marker._propData = p;
                            marker.bindPopup(
                                `<strong>${p.name}</strong><br>${p.address}<br>Available Rooms: ${p.available_rooms}` +
                                `<div class='distance-info'></div>` +
                                `<button type='button' class='btn btn-sm btn-brand mt-2' data-view-rooms-for-property='${p.id}' data-property-name='${escapeHtml(p.name)}'>View Rooms</button>`
                            );
                            bounds.push([p.lat, p.lng]);
                            markers.push(marker);
                        });

                        if (bounds.length) {
                            map.fitBounds(bounds, { padding: [24, 24] });
                        }

                        const onUserLocation = (latitude, longitude) => {
                            const userLatLng = [latitude, longitude];
                            const userMarker = L.circleMarker(userLatLng, { radius: 8, color: BRAND_GREEN, fillColor: BRAND_GREEN, fillOpacity: 0.85 }).addTo(map);
                            userMarker.bindPopup('<strong>You are here</strong>');

                            bounds.push(userLatLng);
                            map.fitBounds(bounds, { padding: [24, 24] });

                            markers.forEach(m => {
                                const p = m._propData;
                                const distMeters = map.distance(userLatLng, [p.lat, p.lng]);
                                const km = (distMeters / 1000).toFixed(2);
                                const popupEl = m.getPopup().getContent();
                                m.setPopupContent(
                                    popupEl.replace(
                                        '<div class=\'distance-info\'></div>',
                                        `<div class='distance-info mt-1'><span class='badge text-bg-light'>${km} km away</span></div>`
                                    )
                                );
                            });

                            updateRecommendedDistances(latitude, longitude);
                        };

                        if (navigator.geolocation) {
                            navigator.geolocation.getCurrentPosition(
                                pos => onUserLocation(pos.coords.latitude, pos.coords.longitude),
                                () => setRecommendedDistanceFallback(),
                                { enableHighAccuracy: true, timeout: 10000 }
                            );
                        } else {
                            setRecommendedDistanceFallback();
                        }

                        setTimeout(() => map.invalidateSize(), 60);
                    })
                    .catch(() => setRecommendedDistanceFallback());
            };

            const initial = (location.hash || '').replace('#', '') || 'dashboard';
            if (document.querySelector(`[data-student-panel="${initial}"]`)) {
                showPanel(initial);
            } else {
                showPanel('dashboard');
            }

            const pageFlagsEl = document.getElementById('pageFlagsJson');
            const pageFlags = JSON.parse(pageFlagsEl ? (pageFlagsEl.textContent || '{}') : '{}');
            const hasReportErrors = !!pageFlags.hasReportErrors;
            const reportSubmitted = !!pageFlags.reportSubmitted;
            if (hasReportErrors) {
                showPanel('reports');
                openReportForm();
            } else if (reportSubmitted && (location.hash || '') === '#reports') {
                showPanel('reports');
            }
        });
    </script>
@endpush

