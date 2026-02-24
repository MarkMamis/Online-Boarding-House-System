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
        .map-mini { height: 220px; border-radius: 1rem; overflow: hidden; border: 1px solid rgba(2,8,20,.08); background: rgba(2,8,20,.02); }
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

                @if($hasCurrentApprovedBooking && !empty($currentApprovedBooking))
                    <div class="alert alert-success rounded-4 mb-4 d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                        <div>
                            <div class="fw-semibold">Approved booking active</div>
                            <div class="small">
                                {{ $currentApprovedBooking->room?->property?->name ?? 'Property' }}
                                • Room {{ $currentApprovedBooking->room?->room_number ?? '—' }}
                                • {{ optional($currentApprovedBooking->check_in)->format('M d, Y') }} to {{ optional($currentApprovedBooking->check_out)->format('M d, Y') }}
                            </div>
                            <div class="small">Booking is disabled while you have an approved stay.</div>
                        </div>
                        <button type="button" class="btn btn-brand rounded-pill px-4" data-panel-jump="onboarding">View onboarding</button>
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

                <div class="section-card p-3 p-lg-4 mb-4">
                    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                        <div class="d-flex align-items-start gap-3">
                            <div class="activity-ic"><i class="bi {{ $nextAction['icon'] }}"></i></div>
                            <div>
                                <div class="fw-semibold">Next action: {{ $nextAction['title'] }}</div>
                                <div class="small text-muted">{{ $nextAction['desc'] }}</div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-brand rounded-pill px-4" data-panel-jump="{{ $nextAction['panel'] }}">{{ $nextAction['cta'] }}</button>
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
                                    <button type="button" class="w-100 text-start border-0 bg-transparent p-0" data-panel-jump="{{ $e['panel'] }}">
                                        <div class="d-flex align-items-start gap-3">
                                            <div class="activity-ic"><i class="bi {{ $e['icon'] }}"></i></div>
                                            <div class="min-w-0">
                                                <div class="fw-semibold text-truncate">{{ $e['title'] }}</div>
                                                <div class="small text-muted">{{ $e['meta'] }} • {{ optional($e['ts'])->diffForHumans() }}</div>
                                            </div>
                                        </div>
                                    </button>
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
                                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill" data-panel-jump="browse-rooms">Browse</button>
                                </div>

                                <div class="row g-3">
                                    @forelse($recommendedRooms->take(3) as $room)
                                        <div class="col-12">
                                            <div class="border rounded-4 bg-white shadow-sm p-3">
                                                <div class="small text-muted">{{ $room->property->name }}</div>
                                                <div class="fw-semibold">Room {{ $room->room_number }}</div>
                                                <div class="small text-muted">Capacity: {{ $room->capacity }} • ₱ {{ number_format($room->price,2) }}</div>
                                                <div class="mt-2">
                                                    <button
                                                        type="button"
                                                        class="btn btn-sm btn-brand rounded-pill px-3"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#bookingRequestModal"
                                                        data-booking-modal
                                                        data-book-url="{{ route('bookings.store', $room->id) }}"
                                                        data-room-label="{{ $room->property->name }} — Room {{ $room->room_number }}"
                                                    >Request booking</button>
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
                                        • Room {{ $latestOnboarding->booking?->room?->room_number ?? '—' }}
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
                                        <button type="button" class="btn btn-sm btn-brand rounded-pill px-3" data-panel-jump="onboarding">Go to onboarding</button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3" data-panel-jump="messages">Message landlord</button>
                                    </div>
                                @else
                                    <div class="alert alert-secondary mb-0">No onboarding record yet.</div>
                                @endif
                            </div>
                        @endif

                        <div class="section-card p-3 p-lg-4">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div class="fw-semibold"><i class="bi bi-map me-2"></i> Map Preview</div>
                                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill" data-panel-jump="property-map">Open map</button>
                            </div>
                            <div id="propertiesMapMini" class="map-mini" data-map-url="{{ route('student.properties.map') }}"></div>
                            <div class="small text-muted mt-2">Shows approved boarding houses with available rooms.</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ROOMS PANEL -->
        @unless($hasCurrentApprovedBooking)
        <div class="student-panel" data-student-panel="browse-rooms">
            <div class="glass-card rounded-4 p-4 p-md-5 mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="fw-semibold mb-0">Browse Rooms</h4>
                </div>

                <div id="propertyRoomFilterNotice" class="alert alert-light border rounded-4 d-none">
                    Showing rooms for <strong id="propertyRoomFilterName"></strong>.
                    <button type="button" id="clearPropertyRoomFilter" class="btn btn-sm btn-outline-secondary ms-2">Clear</button>
                </div>

                <div class="mb-5">
                    <h5 class="fw-semibold mb-3">Recommended Rooms For You</h5>
                    <div class="row g-3">
                        @forelse($recommendedRooms as $room)
                            <div class="col-12 col-md-6 col-lg-4" data-room-property-id="{{ $room->property_id }}">
                                <div class="border rounded-4 bg-white shadow-sm p-3 h-100 d-flex flex-column">
                                    <div class="small text-muted mb-1">{{ $room->property->name }}</div>
                                    <div class="small text-muted mb-1">
                                        <i class="fas fa-map-marker-alt text-danger me-1"></i>
                                        {{ Str::limit($room->property->address, 40) }}
                                    </div>
                                    <div class="fw-semibold">Room {{ $room->room_number }}</div>
                                    <div class="small">Capacity: {{ $room->capacity }}</div>
                                    <div class="small mb-2">Price: ₱ {{ number_format($room->price,2) }}</div>
                                    @if($room->property->latitude && $room->property->longitude)
                                        <div class="small mb-2">
                                            <span class="badge text-bg-light" id="distance-room-{{ $room->id }}">
                                                <i class="fas fa-route text-primary me-1"></i>Calculating distance...
                                            </span>
                                        </div>
                                    @endif
                                    <div class="mt-auto">
                                        <button
                                            type="button"
                                            class="btn btn-sm btn-brand w-100"
                                            data-bs-toggle="modal"
                                            data-bs-target="#bookingRequestModal"
                                            data-booking-modal
                                            data-book-url="{{ route('bookings.store', $room->id) }}"
                                            data-room-label="{{ $room->property->name }} — Room {{ $room->room_number }}"
                                        >Request Booking</button>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12"><div class="alert alert-secondary mb-0">No recommendations right now. Check back later.</div></div>
                        @endforelse
                    </div>
                </div>

                <div class="mb-4">
                    <div class="d-flex flex-column flex-md-row align-items-md-end justify-content-between mb-2 gap-2">
                        <h5 class="fw-semibold mb-0">All Rooms</h5>
                        <form method="GET" action="{{ route('student.dashboard') }}" class="row g-2 align-items-end">
                            <div class="col-6 col-md-auto">
                                <label class="form-label small mb-0">Min Price</label>
                                <input type="number" step="0.01" min="0" name="min_price" value="{{ old('min_price', $minPrice) }}" class="form-control form-control-sm" placeholder="e.g., 1000">
                            </div>
                            <div class="col-6 col-md-auto">
                                <label class="form-label small mb-0">Max Price</label>
                                <input type="number" step="0.01" min="0" name="max_price" value="{{ old('max_price', $maxPrice) }}" class="form-control form-control-sm" placeholder="e.g., 5000">
                            </div>
                            <div class="col-6 col-md-auto">
                                <label class="form-label small mb-0">Min Capacity</label>
                                <input type="number" min="1" name="capacity" value="{{ old('capacity', $minCapacity) }}" class="form-control form-control-sm" placeholder="e.g., {{ $preferredCapacity }}">
                            </div>
                            <div class="col-6 col-md-auto d-flex gap-2">
                                <button class="btn btn-sm btn-brand" type="submit">Apply</button>
                                <a href="{{ route('student.dashboard') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                            </div>
                        </form>
                    </div>
                    <div class="table-responsive rounded-4 shadow-sm bg-white">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Property</th>
                                    <th>Location</th>
                                    <th>Room</th>
                                    <th>Capacity</th>
                                    <th>Price</th>
                                    <th>Status</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($allRooms as $r)
                                    <tr data-room-property-id="{{ $r->property_id }}">
                                        <td>{{ $r->property->name }}</td>
                                        <td>
                                            <small class="text-muted">
                                                <i class="fas fa-map-marker-alt text-danger me-1"></i>
                                                {{ Str::limit($r->property->address, 30) }}
                                            </small>
                                        </td>
                                        <td>{{ $r->room_number }}</td>
                                        <td>{{ $r->capacity }}</td>
                                        <td>₱ {{ number_format($r->price,2) }}</td>
                                        <td>
                                            @if($r->status==='available')
                                                <span class="badge text-bg-success">Available</span>
                                                @if($r->updated_at && $r->updated_at->gte($newThreshold))
                                                    <span class="badge text-bg-primary ms-1">New</span>
                                                @endif
                                            @elseif($r->status==='occupied')
                                                <span class="badge text-bg-secondary">Occupied</span>
                                            @else
                                                <span class="badge text-bg-warning">Maintenance</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            @if($r->status==='available')
                                                <button
                                                    type="button"
                                                    class="btn btn-sm btn-brand"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#bookingRequestModal"
                                                    data-booking-modal
                                                    data-book-url="{{ route('bookings.store', $r->id) }}"
                                                    data-room-label="{{ $r->property->name }} — Room {{ $r->room_number }}"
                                                >Book</button>
                                            @else
                                                <span class="text-muted small">—</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="7" class="text-center text-muted py-4">No rooms found.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="alert alert-info rounded-4 mb-0">
                    <strong>Tip:</strong> Recommendations are based on lowest-priced available rooms. More personalization coming soon.
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
                                    <td>Room {{ $b->room->room_number ?? '—' }}</td>
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
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="fw-semibold mb-0">Messages</h4>
                </div>

                @php
                    $messageErrors = $errors->getBag('messages_dashboard');
                @endphp

                @if($messageErrors->any())
                    <div class="alert alert-danger rounded-4">
                        <div class="fw-semibold mb-1">Please fix the following:</div>
                        <ul class="mb-0">
                            @foreach($messageErrors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="row g-4">
                    <div class="col-12 col-lg-5">
                        <div class="border rounded-4 bg-white shadow-sm p-3">
                            <div class="fw-semibold mb-1">Send a message</div>
                            <div class="small text-muted mb-3">Choose a landlord and send your question.</div>

                            <form method="POST" action="{{ route('messages.store') }}">
                                @csrf
                                <input type="hidden" name="error_bag" value="messages_dashboard">
                                <input type="hidden" name="panel" value="messages">

                                @php
                                    $propertyList = ($messageProperties ?? collect());
                                    $hasProperties = $propertyList->count() > 0;
                                @endphp

                                <div class="mb-3">
                                    <label class="form-label small text-muted">Property</label>
                                    <select name="property_id" class="form-select" required @disabled(!$hasProperties)>
                                        @if(!$hasProperties)
                                            <option value="" selected>No booked properties yet</option>
                                        @else
                                            <option value="" disabled selected>Select booked property</option>
                                            @foreach($propertyList as $prop)
                                                <option value="{{ $prop->id }}" @selected((string)old('property_id') === (string)$prop->id)>{{ $prop->name }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                    @if(!$hasProperties)
                                        <div class="form-text">You can message only the owner of a property you booked.</div>
                                    @endif
                                </div>

                                <div class="mb-3">
                                    <label class="form-label small text-muted">Message</label>
                                    <textarea name="body" class="form-control" rows="4" required placeholder="Ask about availability, pricing, or rules.">{{ old('body') }}</textarea>
                                </div>

                                <div class="d-flex flex-wrap gap-2">
                                    <button class="btn btn-brand btn-sm rounded-pill px-3" type="submit" @disabled(!$hasProperties)>Send</button>
                                    <a class="btn btn-sm btn-outline-secondary rounded-pill px-3" href="{{ route('messages.index') }}">Open inbox</a>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="col-12 col-lg-7">
                        <div class="row g-3">
                            @forelse(($recentMessages ?? collect()) as $m)
                                <div class="col-12">
                                    <div class="border rounded-4 bg-white shadow-sm p-3">
                                        <div class="d-flex justify-content-between align-items-start gap-2">
                                            <div>
                                                <div class="fw-semibold">
                                                    {{ $m->sender->full_name ?? 'Sender' }}
                                                    @if(empty($m->read_at))
                                                        <span class="badge rounded-pill text-bg-danger ms-1">Unread</span>
                                                    @endif
                                                </div>
                                                <div class="small text-muted">{{ $m->created_at?->diffForHumans() ?? '' }}</div>
                                            </div>
                                            @if(!empty($m->property_id))
                                                <span class="badge text-bg-light">Property #{{ $m->property_id }}</span>
                                            @endif
                                        </div>
                                        <div class="small mt-2">{{ \Illuminate\Support\Str::limit($m->body, 180) }}</div>
                                    </div>
                                </div>
                            @empty
                                <div class="col-12"><div class="alert alert-secondary mb-0">No messages yet.</div></div>
                            @endforelse
                        </div>
                    </div>
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
                                            <td>Room {{ $obRow->booking?->room?->room_number ?? '—' }}</td>
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
                                    • Room {{ $latestOnboarding->booking?->room?->room_number ?? '—' }}
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
                                    <div class="fw-semibold">Pay deposit</div>
                                    <div class="small">@if($depositDone)<span class="text-success">Done</span>@else Pending @endif</div>
                                </div>
                            </div>
                            <div class="col-12 col-md-6 col-lg-3">
                                <div class="border rounded-4 p-3 h-100">
                                    <div class="small text-muted">Step 4</div>
                                    <div class="fw-semibold">Complete</div>
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
                            {{ $currentApprovedBooking->room?->property?->name ?? 'Property' }} • Room {{ $currentApprovedBooking->room?->room_number ?? '—' }}
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
                                            <div class="small text-muted">Course/Department: {{ $rb->student?->course ?? '—' }}</div>
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
                        <button type="button" class="btn btn-sm btn-brand rounded-pill px-3" data-open-report-form="1">New report</button>
                    </div>
                </div>

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
                            <div class="col-12 col-lg-8">
                                <label class="form-label small text-muted">Title</label>
                                <input type="text" name="title" class="form-control" value="{{ old('title') }}" placeholder="e.g., Broken lock / Noise complaint" required>
                            </div>
                            <div class="col-12 col-lg-4">
                                <label class="form-label small text-muted">Priority</label>
                                <select name="priority" class="form-select" required>
                                    <option value="low" @selected(old('priority', 'medium')==='low')>Low</option>
                                    <option value="medium" @selected(old('priority', 'medium')==='medium')>Medium</option>
                                    <option value="high" @selected(old('priority', 'medium')==='high')>High</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label small text-muted">Description</label>
                                <textarea name="description" class="form-control" rows="4" placeholder="Describe the issue clearly…" required>{{ old('description') }}</textarea>
                            </div>
                        </div>
                        <div class="mt-3">
                            <button type="submit" class="btn btn-brand rounded-pill px-4">Submit report</button>
                        </div>
                    </form>
                </div>

                <div class="row g-3">
                    @forelse(($recentReports ?? collect()) as $r)
                        <div class="col-12">
                            <div class="border rounded-4 bg-white shadow-sm p-3">
                                <div class="d-flex justify-content-between align-items-start gap-2">
                                    <div class="fw-semibold">{{ $r->title }}</div>
                                    <div>
                                        <span class="badge text-bg-light">{{ $r->status }}</span>
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
                            <div class="small text-muted">Course</div>
                            <div class="fw-semibold">{{ Auth::user()->course ?: '—' }}</div>
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
                }
                history.replaceState(null, '', '#' + panelId);
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
                showPanel('browse-rooms');
                setTimeout(() => {
                    applyPropertyRoomFilter(propertyId, propertyName);
                    if (browseRoomsPanel && browseRoomsPanel.scrollIntoView) {
                        browseRoomsPanel.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }
                }, 50);
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
                        const map = L.map('propertiesMapMini', {
                            zoomControl: false,
                            attributionControl: false,
                            scrollWheelZoom: false,
                            doubleClickZoom: false,
                            boxZoom: false,
                            keyboard: false,
                        });
                        miniMapInstance = map;
                        miniMapInitialized = true;

                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            attribution: '&copy; OpenStreetMap contributors'
                        }).addTo(map);

                        const bounds = [];

                        props.slice(0, 12).forEach(p => {
                            const marker = L.circleMarker([p.lat, p.lng], {
                                radius: 6,
                                color: BRAND_GREEN,
                                fillColor: BRAND_GREEN,
                                fillOpacity: 0.7,
                                weight: 1,
                            }).addTo(map);
                            marker.bindTooltip(p.name, { direction: 'top', offset: [0, -6] });
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
