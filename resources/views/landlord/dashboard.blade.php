@extends('layouts.landlord')

@section('title', 'Dashboard')

@section('content')
    @php
        $occupiedRooms = max(0, (int) $totalRooms - (int) $vacantRooms);
        $occupancyPct = ((int) $totalRooms) > 0 ? (int) round(($occupiedRooms / (int) $totalRooms) * 100) : 0;
        $unreadMessagesSafe = (int) ($unreadMessages ?? 0);
        $setupSnapshot = $setupSnapshot ?? [];
        $landlordProfile = Auth::user()->landlordProfile;
        $hasPermitFile = filled(optional($landlordProfile)->business_permit_path);
        $permitStatus = $setupSnapshot['permit_status'] ?? (optional($landlordProfile)->business_permit_status ?: ($hasPermitFile ? 'pending' : 'not_submitted'));
        $profileComplete = (bool) ($setupSnapshot['profile_complete'] ?? false);
        $permitApproved = (bool) ($setupSnapshot['permit_approved'] ?? ($hasPermitFile && $permitStatus === 'approved'));
        $billingComplete = (bool) ($setupSnapshot['billing_complete'] ?? false);
        $landlordOperationsLocked = !$profileComplete || !$permitApproved;

        $permitBadgeClass = match ($permitStatus) {
            'approved' => 'text-bg-success',
            'rejected' => 'text-bg-danger',
            'pending' => 'text-bg-warning',
            default => 'text-bg-secondary',
        };

        $permitStatusLabel = str_replace('_', ' ', $permitStatus);
        $permitPending = $permitStatus === 'pending';
        $setupFullyComplete = $profileComplete && $permitApproved && $billingComplete;
        $tenantTrendCollection = collect($tenantTrend ?? []);
    @endphp

    <div class="glass-card rounded-4 p-4 p-md-5 mb-4">
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
            <div class="min-w-0">
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <h1 class="h2 mb-0 fw-semibold">Welcome, <span class="highlight">{{ Auth::user()->full_name }}</span></h1>
                    <span class="badge rounded-pill text-bg-light border">Landlord Dashboard</span>
                </div>
                <p class="mb-0 text-secondary mt-2">Track occupancy, requests, and messages across your properties.</p>
                @if(!$setupFullyComplete)
                    <div class="d-flex flex-wrap gap-2 mt-3">
                        @if(!$profileComplete)
                            <span class="badge rounded-pill text-bg-secondary">Profile Incomplete</span>
                        @endif
                        @if(!$permitApproved)
                            <span class="badge rounded-pill {{ $permitBadgeClass }}">Permit {{ $permitStatusLabel }}</span>
                        @endif
                        @if(!$billingComplete)
                            <span class="badge rounded-pill text-bg-warning">Billing Needs Setup</span>
                        @endif
                    </div>
                @endif
            </div>
            <div class="d-flex flex-wrap gap-2">
                @if($landlordOperationsLocked)
                    <button type="button" class="btn btn-brand rounded-pill px-4" disabled title="Complete profile, permit, and billing setup first">
                        <i class="bi bi-plus-circle me-1"></i> Add Property
                    </button>
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" disabled title="Complete profile, permit, and billing setup first">
                        <i class="bi bi-door-open me-1"></i> Add Room
                    </button>
                @else
                    <button type="button" class="btn btn-brand rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#addPropertyModal">
                        <i class="bi bi-plus-circle me-1"></i> Add Property
                    </button>
                    @if($propertiesCount > 0)
                        <button type="button" class="btn btn-outline-brand rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#addRoomModal">
                            <i class="bi bi-door-open me-1"></i> Add Room
                        </button>
                    @else
                        <button type="button" class="btn btn-outline-secondary rounded-pill px-4" disabled>
                            <i class="bi bi-door-open me-1"></i> Add Room
                        </button>
                    @endif
                @endif
                @if($landlordOperationsLocked)
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" disabled title="Complete setup first">
                        <i class="bi bi-journal-check me-1"></i> Requests
                    </button>
                @else
                    <a href="{{ route('landlord.bookings.index') }}" class="btn btn-outline-secondary rounded-pill px-4">
                        <i class="bi bi-journal-check me-1"></i> Requests
                        @if($pendingRequests > 0)
                            <span class="badge rounded-pill text-bg-warning ms-1">{{ $pendingRequests }}</span>
                        @endif
                    </a>
                @endif
            </div>
        </div>

        @if(($overduePaymentsCount ?? 0) > 0)
            <div class="alert alert-danger rounded-4 mt-3 mb-0 d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div>
                    <strong>{{ $overduePaymentsCount }}</strong> tenant payment{{ $overduePaymentsCount === 1 ? '' : 's' }} marked overdue.
                </div>
                <a href="{{ route('landlord.payments.index', ['status' => 'overdue']) }}" class="btn btn-sm btn-outline-danger rounded-pill">Review Overdue Payments</a>
            </div>
        @endif

        <div class="row g-4 mt-3">
            <div class="col-6 col-lg-3">
                <div class="metric-tile h-100">
                    <div class="d-flex align-items-center gap-3">
                        <div class="metric-ic"><i class="bi bi-buildings"></i></div>
                        <div>
                            <div class="h4 mb-0">{{ $propertiesCount }}</div>
                            <div class="small metric-label">Properties</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="metric-tile h-100">
                    <div class="d-flex align-items-center gap-3">
                        <div class="metric-ic"><i class="bi bi-door-open"></i></div>
                        <div>
                            <div class="h4 mb-0">{{ $totalRooms }}</div>
                            <div class="small metric-label">Total Rooms</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="metric-tile h-100">
                    <div class="d-flex align-items-center gap-3">
                        <div class="metric-ic"><i class="bi bi-hourglass-split"></i></div>
                        <div>
                            <div class="h4 mb-0">{{ $pendingRequests }}</div>
                            <div class="small metric-label">Pending Requests</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="metric-tile h-100">
                    <div class="d-flex align-items-center gap-3">
                        <div class="metric-ic"><i class="bi bi-exclamation-triangle"></i></div>
                        <div>
                            <div class="h4 mb-0">{{ (int) ($overduePaymentsCount ?? 0) }}</div>
                            <div class="small metric-label">Overdue Payments</div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <div class="row g-4 mt-1">
            <div class="col-12 col-xl-7">
                <div class="border rounded-4 bg-white p-4 h-100">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="fw-semibold mb-0">Quick Modules</h5>
                        <span class="small text-muted">Shortcuts to your daily tasks</span>
                    </div>

                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            @if($landlordOperationsLocked)
                                <span class="text-decoration-none d-block metric-tile h-100 opacity-75" aria-disabled="true" title="Complete setup first" style="cursor:not-allowed;">
                            @else
                                <a href="{{ route('landlord.rooms.index') }}" class="text-decoration-none d-block metric-tile h-100">
                            @endif
                                <div class="d-flex align-items-center gap-3">
                                    <div class="metric-ic"><i class="bi bi-door-open"></i></div>
                                    <div class="min-w-0">
                                        <div class="fw-semibold text-dark">Rooms</div>
                                        <div class="small metric-label">Manage pricing and availability</div>
                                    </div>
                                </div>
                            @if($landlordOperationsLocked)</span>@else</a>@endif
                        </div>
                        <div class="col-12 col-md-6">
                            @if($landlordOperationsLocked)
                                <span class="text-decoration-none d-block metric-tile h-100 opacity-75" aria-disabled="true" title="Complete setup first" style="cursor:not-allowed;">
                            @else
                                <a href="{{ route('landlord.bookings.index') }}" class="text-decoration-none d-block metric-tile h-100">
                            @endif
                                <div class="d-flex align-items-center gap-3">
                                    <div class="metric-ic"><i class="bi bi-journal-check"></i></div>
                                    <div class="min-w-0">
                                        <div class="fw-semibold text-dark">Booking Requests</div>
                                        <div class="small metric-label">Approve or decline requests</div>
                                    </div>
                                    @if($pendingRequests > 0)
                                        <span class="badge rounded-pill text-bg-warning ms-auto">{{ $pendingRequests }}</span>
                                    @endif
                                </div>
                            @if($landlordOperationsLocked)</span>@else</a>@endif
                        </div>
                        <div class="col-12 col-md-6">
                            @if($landlordOperationsLocked)
                                <span class="text-decoration-none d-block metric-tile h-100 opacity-75" aria-disabled="true" title="Complete setup first" style="cursor:not-allowed;">
                            @else
                                <a href="{{ route('landlord.messages.index') }}" class="text-decoration-none d-block metric-tile h-100">
                            @endif
                                <div class="d-flex align-items-center gap-3">
                                    <div class="metric-ic"><i class="bi bi-chat-dots"></i></div>
                                    <div class="min-w-0">
                                        <div class="fw-semibold text-dark">Messages</div>
                                        <div class="small metric-label">Respond to tenant questions</div>
                                    </div>
                                    @if(isset($unreadMessages) && $unreadMessages > 0)
                                        <span class="badge rounded-pill text-bg-danger ms-auto">{{ $unreadMessages }}</span>
                                    @endif
                                </div>
                            @if($landlordOperationsLocked)</span>@else</a>@endif
                        </div>
                        <div class="col-12 col-md-6">
                            @if($landlordOperationsLocked)
                                <span class="text-decoration-none d-block metric-tile h-100 opacity-75" aria-disabled="true" title="Complete setup first" style="cursor:not-allowed;">
                            @else
                                <a href="{{ route('landlord.tenants.index') }}" class="text-decoration-none d-block metric-tile h-100">
                            @endif
                                <div class="d-flex align-items-center gap-3">
                                    <div class="metric-ic"><i class="bi bi-people"></i></div>
                                    <div class="min-w-0">
                                        <div class="fw-semibold text-dark">Tenants</div>
                                        <div class="small metric-label">View current tenants</div>
                                    </div>
                                </div>
                            @if($landlordOperationsLocked)</span>@else</a>@endif
                        </div>
                        <div class="col-12 col-md-6">
                            @if($landlordOperationsLocked)
                                <span class="text-decoration-none d-block metric-tile h-100 opacity-75" aria-disabled="true" title="Complete setup first" style="cursor:not-allowed;">
                            @else
                                <a href="{{ route('landlord.onboarding.index') }}" class="text-decoration-none d-block metric-tile h-100">
                            @endif
                                <div class="d-flex align-items-center gap-3">
                                    <div class="metric-ic"><i class="bi bi-clipboard-check"></i></div>
                                    <div class="min-w-0">
                                        <div class="fw-semibold text-dark">Onboarding</div>
                                        <div class="small metric-label">Review tenant documents</div>
                                    </div>
                                </div>
                            @if($landlordOperationsLocked)</span>@else</a>@endif
                        </div>
                        <div class="col-12 col-md-6">
                            @if($landlordOperationsLocked)
                                <span class="text-decoration-none d-block metric-tile h-100 opacity-75" aria-disabled="true" title="Complete setup first" style="cursor:not-allowed;">
                            @else
                                <a href="{{ route('landlord.maintenance.index') }}" class="text-decoration-none d-block metric-tile h-100">
                            @endif
                                <div class="d-flex align-items-center gap-3">
                                    <div class="metric-ic"><i class="bi bi-tools"></i></div>
                                    <div class="min-w-0">
                                        <div class="fw-semibold text-dark">Maintenance</div>
                                        <div class="small metric-label">Track issues and repairs</div>
                                    </div>
                                </div>
                            @if($landlordOperationsLocked)</span>@else</a>@endif
                        </div>
                        <div class="col-12 col-md-6">
                            @if($landlordOperationsLocked)
                                <span class="text-decoration-none d-block metric-tile h-100 opacity-75" aria-disabled="true" title="Complete setup first" style="cursor:not-allowed;">
                            @else
                                <a href="{{ route('landlord.payments.index') }}" class="text-decoration-none d-block metric-tile h-100">
                            @endif
                                <div class="d-flex align-items-center gap-3">
                                    <div class="metric-ic"><i class="bi bi-cash-coin"></i></div>
                                    <div class="min-w-0">
                                        <div class="fw-semibold text-dark">Payments</div>
                                        <div class="small metric-label">Review billing and dues</div>
                                    </div>
                                </div>
                            @if($landlordOperationsLocked)</span>@else</a>@endif
                        </div>
                        <div class="col-12 col-md-6">
                            @if($landlordOperationsLocked)
                                <span class="text-decoration-none d-block metric-tile h-100 opacity-75" aria-disabled="true" title="Complete setup first" style="cursor:not-allowed;">
                            @else
                                <a href="{{ route('landlord.analytics.index') }}" class="text-decoration-none d-block metric-tile h-100">
                            @endif
                                <div class="d-flex align-items-center gap-3">
                                    <div class="metric-ic"><i class="bi bi-graph-up"></i></div>
                                    <div class="min-w-0">
                                        <div class="fw-semibold text-dark">Analytics</div>
                                        <div class="small metric-label">View performance insights</div>
                                    </div>
                                </div>
                            @if($landlordOperationsLocked)</span>@else</a>@endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-xl-5">
                <div class="border rounded-4 bg-white p-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h5 class="fw-semibold mb-0">Next Actions</h5>
                        <span class="small text-muted">Today</span>
                    </div>

                    <div class="list-group list-group-flush rounded-3 overflow-hidden">
                        @if($landlordOperationsLocked)
                            <span class="list-group-item list-group-item-action d-flex justify-content-between align-items-center disabled opacity-75" aria-disabled="true" title="Complete setup first" style="cursor:not-allowed;">
                        @else
                            <a href="{{ route('landlord.bookings.index') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                        @endif
                            <span><i class="bi bi-journal-check me-2"></i>Review booking requests</span>
                            <span class="badge rounded-pill {{ $pendingRequests > 0 ? 'text-bg-warning' : 'text-bg-secondary' }}">{{ $pendingRequests }}</span>
                        @if($landlordOperationsLocked)</span>@else</a>@endif
                        @if($landlordOperationsLocked)
                            <span class="list-group-item list-group-item-action d-flex justify-content-between align-items-center disabled opacity-75" aria-disabled="true" title="Complete setup first" style="cursor:not-allowed;">
                        @else
                            <a href="{{ route('landlord.payments.index', ['status' => 'overdue']) }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                        @endif
                            <span><i class="bi bi-exclamation-triangle me-2"></i>Review overdue payments</span>
                            <span class="badge rounded-pill {{ (int) ($overduePaymentsCount ?? 0) > 0 ? 'text-bg-danger' : 'text-bg-secondary' }}">{{ (int) ($overduePaymentsCount ?? 0) }}</span>
                        @if($landlordOperationsLocked)</span>@else</a>@endif
                        @if($permitPending)
                            <span class="list-group-item list-group-item-action d-flex justify-content-between align-items-center disabled opacity-75" aria-disabled="true" title="Messages unlock after permit approval" style="cursor:not-allowed;">
                                <span><i class="bi bi-chat-dots me-2"></i>Reply to messages</span>
                                <span class="badge rounded-pill text-bg-secondary">Locked</span>
                            </span>
                        @else
                            <a href="{{ route('landlord.messages.index') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                <span><i class="bi bi-chat-dots me-2"></i>Reply to messages</span>
                                <span class="badge rounded-pill {{ $unreadMessagesSafe > 0 ? 'text-bg-danger' : 'text-bg-secondary' }}">{{ $unreadMessagesSafe }}</span>
                            </a>
                        @endif
                        @if($landlordOperationsLocked)
                            <span class="list-group-item list-group-item-action d-flex justify-content-between align-items-center disabled opacity-75" aria-disabled="true" title="Complete setup first" style="cursor:not-allowed;">
                        @else
                            <a href="{{ route('landlord.rooms.index') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                        @endif
                            <span><i class="bi bi-door-open me-2"></i>Update room availability</span>
                            <span class="badge rounded-pill {{ $vacantRooms > 0 ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $vacantRooms }}</span>
                        @if($landlordOperationsLocked)</span>@else</a>@endif
                    </div>

                    <div class="mt-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="fw-semibold">Occupancy</div>
                            <div class="small text-muted">{{ $occupancyPct }}%</div>
                        </div>
                        <div class="progress mt-2" style="height:10px;">
                            <div class="progress-bar" role="progressbar" style="width: {{ $occupancyPct }}%; background: var(--brand);"></div>
                        </div>
                        <div class="small text-muted mt-2">{{ $occupiedRooms }} occupied • {{ $vacantRooms }} vacant • {{ $totalRooms }} rooms</div>
                    </div>

                    @if($propertiesCount === 0)
                        <div class="alert alert-secondary rounded-4 mt-4 mb-0 small">
                            <strong>Getting started:</strong> Add your first property to start creating rooms and receiving booking requests.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="glass-card rounded-4 p-4 p-md-5 mb-4">
        <div class="row g-4">
            <div class="col-12 col-xl-5">
                <h5 class="fw-semibold mb-2">Monthly Tenant Trend</h5>
                <p class="small text-muted mb-3">Active tenant count by month-end (last 6 months).</p>
                <div class="border rounded-4 bg-white p-3" style="height: 340px;">
                    <canvas id="chartLandlordTenantTrend"></canvas>
                </div>
            </div>

            <div class="col-12 col-xl-7">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h5 class="fw-semibold mb-0">Real-time Occupancy by Property</h5>
                    <span class="small text-muted">Occupied vs vacant beds</span>
                </div>
                <p class="small text-muted mb-3">Stacked occupancy view based on current room capacities and active tenants.</p>
                <div class="border rounded-4 bg-white p-3" style="height: 340px;">
                    <canvas id="chartLandlordPropertyOccupancy"></canvas>
                </div>
            </div>
        </div>
    </div>

    @if(!$permitPending)
    <div class="row g-4 mb-4">
        <div class="col-12 col-xl-8">
            <div class="glass-card rounded-4 p-4 p-md-5 h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-semibold mb-0">Your Properties</h5>
                    <a href="{{ route('landlord.properties.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill">Manage All</a>
                </div>

                <div class="table-responsive rounded-4 border">
                    <table class="table align-middle mb-0">
                        <thead class="small text-uppercase">
                            <tr>
                                <th>Name</th>
                                <th>Address</th>
                                <th>Rooms</th>
                                <th>Price Range</th>
                                <th>Added</th>
                            </tr>
                        </thead>
                        <tbody class="small">
                            @forelse($properties as $prop)
                                <tr>
                                    <td>
                                        <a class="text-decoration-none" href="{{ route('landlord.properties.show', $prop) }}">{{ $prop->name }}</a>
                                    </td>
                                    <td class="text-muted">{{ $prop->address }}</td>
                                    <td>
                                        <span class="fw-semibold">{{ $prop->rooms_vacant_live }}</span>
                                        <span class="text-muted">/ {{ $prop->rooms_total_live }}</span>
                                    </td>
                                    <td>
                                        @if($prop->price_min !== null || $prop->price_max !== null)
                                            ₱{{ number_format($prop->price_min,0) }} - ₱{{ number_format($prop->price_max,0) }}
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td class="text-muted">{{ $prop->created_at->diffForHumans() }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">
                                        No properties yet.
                                        <a href="{{ route('landlord.properties.create') }}">Add one</a>.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-4">
            <div class="glass-card rounded-4 p-4 p-md-5 h-100">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h5 class="fw-semibold mb-0">Recommended Rooms</h5>
                    <a href="{{ route('landlord.rooms.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill">View Rooms</a>
                </div>
                <p class="small text-muted mb-3">Cheapest available rooms from your inventory.</p>

                <div class="row g-3">
                    @forelse($recommendedRooms as $room)
                        <div class="col-12">
                            <div class="border rounded-4 bg-white p-3">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="min-w-0">
                                        <div class="small text-muted text-truncate">{{ $room->property->name }}</div>
                                        <div class="fw-semibold">{{ $room->room_number }}</div>
                                        <div class="small text-muted">Capacity: {{ $room->capacity }}</div>
                                    </div>
                                    <div class="text-end">
                                        <div class="fw-semibold">₱{{ number_format($room->price,2) }}</div>
                                        <span class="badge text-bg-success">Available</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12">
                            <div class="alert alert-secondary mb-0">No available rooms to recommend right now.</div>
                        </div>
                    @endforelse
                </div>

                <div class="alert alert-secondary rounded-4 mt-4 mb-0 small">
                    <strong>Tip:</strong> Recommended rooms are picked from your vacant inventory by lowest price.
                </div>
            </div>
        </div>
    </div>

    <div class="glass-card rounded-4 p-4 p-md-5 mb-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="fw-semibold mb-0">Messages Overview</h5>
            <a href="{{ route('landlord.messages.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill">Open Inbox</a>
        </div>

        <div class="row g-4">
            <div class="col-md-6">
                <h6 class="fw-semibold mb-2">Unread ({{ (int) ($unreadMessages ?? 0) }})</h6>
                @forelse($unreadMessagesList as $m)
                    <div class="border rounded-4 p-3 mb-2 bg-white small">
                        <div class="d-flex justify-content-between">
                            <strong>{{ $m->sender->full_name }}</strong>
                            <span class="text-muted">{{ $m->created_at->diffForHumans() }}</span>
                        </div>
                        <div class="mt-1">{{ Str::limit($m->body, 120) }}</div>

                        <form method="POST" action="{{ route('messages.read', $m->id) }}" class="mt-2 d-flex gap-2 flex-wrap">
                            @csrf
                            <button class="btn btn-sm btn-success">Mark Read</button>
                            <button type="button" class="btn btn-sm btn-brand" data-bs-toggle="collapse" data-bs-target="#reply{{$m->id}}">Reply</button>
                        </form>

                        <div class="collapse mt-2" id="reply{{$m->id}}">
                            <form method="POST" action="{{ route('messages.store') }}" class="small">
                                @csrf
                                <input type="hidden" name="receiver_id" value="{{ $m->sender->id }}">
                                <input type="hidden" name="property_id" value="{{ $m->property_id }}">
                                <textarea name="body" rows="2" class="form-control mb-2" required placeholder="Your reply..."></textarea>
                                <div class="d-flex justify-content-end">
                                    <button class="btn btn-sm btn-brand">Send</button>
                                </div>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="small text-muted">No unread messages.</div>
                @endforelse
            </div>

            <div class="col-md-6">
                <h6 class="fw-semibold mb-2">Recently Read</h6>
                @forelse($recentReceivedMessages as $m)
                    <div class="border rounded-4 p-3 mb-2 bg-white small">
                        <div class="d-flex justify-content-between">
                            <strong>{{ $m->sender->full_name }}</strong>
                            <span class="text-muted">{{ $m->created_at->diffForHumans() }}</span>
                        </div>
                        <div class="mt-1">{{ Str::limit($m->body, 120) }}</div>
                        <button type="button" class="btn btn-sm btn-outline-brand mt-2" data-bs-toggle="collapse" data-bs-target="#replyRead{{$m->id}}">Reply</button>
                        <div class="collapse mt-2" id="replyRead{{$m->id}}">
                            <form method="POST" action="{{ route('messages.store') }}" class="small">
                                @csrf
                                <input type="hidden" name="receiver_id" value="{{ $m->sender->id }}">
                                <input type="hidden" name="property_id" value="{{ $m->property_id }}">
                                <textarea name="body" rows="2" class="form-control mb-2" required placeholder="Your reply..."></textarea>
                                <div class="d-flex justify-content-end">
                                    <button class="btn btn-sm btn-brand">Send</button>
                                </div>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="small text-muted">No recent messages.</div>
                @endforelse
            </div>
        </div>

        <div class="small text-muted mt-3">Only the latest 5 messages per section are shown here. Open inbox to view full history.</div>
    </div>
    @endif

@push('modals')
    @if(!empty($needsLandlordSetup))
        @php
            $setupProgress = (int) round((($setupCompletedCount ?? 0) / max(1, (int) ($setupTotalCount ?? 1))) * 100);
        @endphp
        <div class="modal fade" id="landlordSetupReminderModal" tabindex="-1" aria-labelledby="landlordSetupReminderModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content rounded-4">
                    <div class="modal-header border-0 pb-0">
                        <h5 class="modal-title fw-semibold" id="landlordSetupReminderModalLabel">
                            <i class="bi bi-clipboard-check text-warning me-2"></i>Complete Landlord Setup
                        </h5>
                    </div>
                    <div class="modal-body pt-2">
                        <p class="mb-3">Setup onboarding is now handled in three steps: Profile, Permit, and Billing. Property and room actions unlock after permit approval.</p>

                        <div class="d-flex justify-content-between align-items-center small mb-2">
                            <span class="text-muted">Setup progress</span>
                            <span class="fw-semibold">{{ $setupCompletedCount ?? 0 }}/{{ $setupTotalCount ?? 0 }}</span>
                        </div>
                        <div class="progress mb-3" style="height: 8px;">
                            <div class="progress-bar" role="progressbar" style="width: {{ $setupProgress }}%; background: var(--brand);"></div>
                        </div>

                        @php
                            $setupItems = $setupChecklist ?? [];
                            $permitGateActive = !$permitApproved;
                        @endphp
                        <div class="d-grid gap-2">
                            @foreach($setupItems as $item)
                                @php
                                    $title = $item['title'] ?? '';
                                    $isCompleted = !empty($item['completed']);
                                    $lockedItem = $permitGateActive && in_array($title, ['Add property location', 'Set room availability'], true);
                                    $isPermitItem = $title === 'Upload business permit';
                                    $permitBadgeClass = $permitStatus === 'approved'
                                        ? 'text-bg-success'
                                        : ($permitStatus === 'rejected' ? 'text-bg-danger' : ($permitStatus === 'pending' ? 'text-bg-warning' : 'text-bg-secondary'));
                                @endphp

                                <div class="border rounded-3 p-2 d-flex justify-content-between align-items-start gap-2 {{ $lockedItem ? 'bg-light opacity-75 border-secondary-subtle' : 'bg-white' }}" @if($lockedItem) style="pointer-events:none; filter:grayscale(15%);" @endif>
                                    <div>
                                        <div class="fw-semibold small mb-1 {{ $lockedItem ? 'text-muted' : '' }}">
                                            @if($isPermitItem && $permitStatus === 'pending')
                                                <i class="bi bi-hourglass-split text-warning me-1"></i>
                                            @elseif($isPermitItem && $permitStatus === 'rejected')
                                                <i class="bi bi-x-octagon-fill text-danger me-1"></i>
                                            @elseif($isCompleted)
                                                <i class="bi bi-check-circle-fill text-success me-1"></i>
                                            @elseif($lockedItem)
                                                <i class="bi bi-lock-fill text-muted me-1"></i>
                                            @else
                                                <i class="bi bi-dot text-warning me-1"></i>
                                            @endif
                                            {{ $item['title'] ?? 'Setup Item' }}
                                        </div>
                                        <div class="small {{ $lockedItem ? 'text-muted' : 'text-muted' }}">{{ $item['description'] ?? '' }}</div>
                                    </div>
                                    @if($lockedItem)
                                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill" disabled>Pending permit</button>
                                    @elseif($isPermitItem)
                                        <span class="badge rounded-pill {{ $permitBadgeClass }}">
                                            {{ $permitStatus === 'approved' ? 'Approved' : ($permitStatus === 'rejected' ? 'Rejected' : ($permitStatus === 'pending' ? 'Pending' : 'Not submitted')) }}
                                        </span>
                                    @elseif(!$isCompleted)
                                        <a href="{{ $item['action_url'] ?? '#' }}" class="btn btn-sm btn-outline-brand rounded-pill">{{ $item['action_label'] ?? 'Open' }}</a>
                                    @else
                                        <span class="badge text-bg-success rounded-pill">Done</span>
                                    @endif
                                </div>
                            @endforeach
                        </div>

                    </div>
                    <div class="modal-footer border-0 pt-0">
                        @php
                            $setupSubmitted = (bool) ($setupSnapshot['setup_submitted'] ?? false);
                            $waitingPermitApproval = $setupSubmitted && ($permitStatus === 'pending');
                        @endphp
                        <button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">Later</button>
                        @if($waitingPermitApproval)
                            <span class="small text-muted fw-semibold px-2">Waiting for permit approval</span>
                        @else
                            <a href="{{ route('landlord.setup.show') }}" class="btn btn-brand rounded-pill px-4">
                                <i class="bi bi-gear me-1"></i>Open Setup Form
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="modal fade" id="addPropertyModal" tabindex="-1" aria-labelledby="addPropertyModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content rounded-4">
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-semibold" id="addPropertyModalLabel">Add Property</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="{{ route('landlord.properties.store') }}">
                    @csrf
                    <input type="hidden" name="from_dashboard" value="1">
                    <div class="modal-body">
                        @if(session('success'))
                            <div class="alert alert-success small rounded-3">{{ session('success') }}</div>
                        @endif
                        @if($errors->any())
                            <div class="alert alert-danger small rounded-3">
                                <ul class="mb-0">
                                    @foreach($errors->all() as $err)
                                        <li>{{ $err }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small">Name *</label>
                                <input type="text" name="name" class="form-control" required value="{{ old('name') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small">Address *</label>
                                <input type="text" name="address" class="form-control" required value="{{ old('address') }}">
                            </div>
                            <div class="col-12">
                                <label class="form-label small">Description</label>
                                <textarea name="description" class="form-control" rows="3">{{ old('description') }}</textarea>
                            </div>
                        </div>
                        <hr class="my-4">
                        <h6 class="fw-semibold mb-2">Optional: Create First Room</h6>
                        <p class="small text-muted mb-3">Fill these to automatically create the first room for this property.</p>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label small">Room Number</label>
                                <input type="text" name="initial_room_number" class="form-control" value="{{ old('initial_room_number') }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small">Capacity</label>
                                <input type="number" min="1" name="initial_capacity" class="form-control" value="{{ old('initial_capacity') }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small">Price</label>
                                <input type="number" min="0" step="0.01" name="initial_price" class="form-control" value="{{ old('initial_price') }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small">Status</label>
                                <select name="initial_status" class="form-select">
                                    <option value="" selected>-- choose --</option>
                                    <option value="available" @selected(old('initial_status')==='available')>Available</option>
                                    <option value="occupied" @selected(old('initial_status')==='occupied')>Occupied</option>
                                    <option value="maintenance" @selected(old('initial_status')==='maintenance')>Maintenance</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-brand rounded-pill px-4">Save Property</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add Room Modal -->
    <div class="modal fade" id="addRoomModal" tabindex="-1" aria-labelledby="addRoomModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-md modal-dialog-centered">
            <div class="modal-content rounded-4">
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-semibold" id="addRoomModalLabel">Add Room</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" enctype="multipart/form-data" id="quickRoomForm">
                    @csrf
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label small">Property *</label>
                                <select name="property_id" class="form-select" required>
                                    @foreach($properties as $p)
                                        <option value="{{ $p->id }}">{{ $p->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small">Room Number *</label>
                                <input type="text" name="room_number" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small">Capacity *</label>
                                <input type="number" min="1" name="capacity" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small">Status *</label>
                                <select name="status" class="form-select" required>
                                    <option value="available">Available</option>
                                    <option value="occupied">Occupied</option>
                                    <option value="maintenance">Maintenance</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small">Pricing Model *</label>
                                <select name="pricing_model" id="quickPricingModel" class="form-select" required>
                                    <option value="per_room">Per room</option>
                                    <option value="per_bed">Per bed</option>
                                    <option value="hybrid" selected>Hybrid</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small">Per-room Price *</label>
                                <input type="number" min="0" step="0.01" name="price_per_room" id="quickPricePerRoom" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small">Per-bed Price *</label>
                                <input type="number" min="0" step="0.01" name="price_per_bed" id="quickPricePerBed" class="form-control" required>
                            </div>

                            <div class="col-12">
                                <label class="form-label small">Room Photo (optional)</label>
                                <input type="file" name="image" class="form-control" accept="image/*">
                            </div>

                            <div class="col-12">
                                <label class="form-label small">Included in Rent (optional)</label>
                                <textarea name="inclusions" class="form-control" rows="2" placeholder="e.g., Electric fan, WiFi, Water"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-brand rounded-pill px-4">Save Room</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        @if(!empty($needsLandlordSetup))
            const landlordSetupModalEl = document.getElementById('landlordSetupReminderModal');
            if (landlordSetupModalEl && window.bootstrap?.Modal) {
                const landlordSetupModal = new bootstrap.Modal(landlordSetupModalEl);
                landlordSetupModal.show();
            }
        @endif

        const form = document.getElementById('quickRoomForm');
        if(form){
            form.addEventListener('submit', function(){
                const pid = this.querySelector('select[name="property_id"]').value;
                this.action = `/landlord/dashboard/properties/${pid}/rooms`;
            });

            const pricingModel = document.getElementById('quickPricingModel');
            const perRoomInput = document.getElementById('quickPricePerRoom');
            const perBedInput = document.getElementById('quickPricePerBed');

            const syncQuickPricingInputs = () => {
                if (!pricingModel || !perRoomInput || !perBedInput) return;
                const model = pricingModel.value || 'hybrid';
                const needsPerRoom = model === 'per_room' || model === 'hybrid';
                const needsPerBed = model === 'per_bed' || model === 'hybrid';
                perRoomInput.disabled = !needsPerRoom;
                perRoomInput.required = needsPerRoom;
                perBedInput.disabled = !needsPerBed;
                perBedInput.required = needsPerBed;
            };

            if (pricingModel) {
                pricingModel.addEventListener('change', syncQuickPricingInputs);
                pricingModel.addEventListener('input', syncQuickPricingInputs);
                syncQuickPricingInputs();
            }
        }

        if (typeof Chart !== 'undefined') {
            const trendCanvas = document.getElementById('chartLandlordTenantTrend');
            const occupancyCanvas = document.getElementById('chartLandlordPropertyOccupancy');

            const trendRows = @json($tenantTrendCollection->values());
            const trendLabels = trendRows.map((row) => String(row?.label || '-'));
            const trendData = trendRows.map((row) => Number(row?.count || 0));

            if (trendCanvas) {
                const trendGradient = trendCanvas.getContext('2d').createLinearGradient(0, 0, 0, 280);
                trendGradient.addColorStop(0, 'rgba(20,83,45,0.30)');
                trendGradient.addColorStop(1, 'rgba(20,83,45,0.04)');

                new Chart(trendCanvas, {
                    type: 'line',
                    data: {
                        labels: trendLabels.length ? trendLabels : ['No data'],
                        datasets: [{
                            label: 'Active tenants',
                            data: trendData.length ? trendData : [0],
                            borderColor: 'rgba(20,83,45,0.95)',
                            backgroundColor: trendGradient,
                            fill: true,
                            tension: 0.35,
                            borderWidth: 2.5,
                            pointRadius: 3,
                            pointHoverRadius: 5,
                            pointBackgroundColor: '#ffffff',
                            pointBorderColor: 'rgba(20,83,45,0.95)',
                            pointBorderWidth: 2,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                callbacks: {
                                    label: (ctx) => `Active tenants: ${ctx.raw}`
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: { precision: 0 },
                                grid: { color: 'rgba(148,163,184,0.18)' }
                            },
                            x: {
                                grid: { display: false },
                                ticks: {
                                    maxRotation: 0,
                                    minRotation: 0,
                                }
                            }
                        }
                    }
                });
            }

            if (occupancyCanvas) {
                const occupancyRowsRaw = @json($propertyOccupancyBreakdown ?? []);
                const occupancyRows = (Array.isArray(occupancyRowsRaw) ? occupancyRowsRaw : [])
                    .map((property) => {
                        const rooms = Array.isArray(property?.rooms) ? property.rooms : [];
                        const occupiedBeds = rooms.reduce((sum, room) => sum + Math.max(0, Number(room?.occupied || 0)), 0);
                        const capacityBeds = rooms.reduce((sum, room) => sum + Math.max(1, Number(room?.capacity || 1)), 0);
                        const vacantBeds = Math.max(0, capacityBeds - occupiedBeds);

                        return {
                            name: String(property?.property_name || 'Property'),
                            occupiedBeds,
                            vacantBeds,
                        };
                    })
                    .filter((row) => row.occupiedBeds > 0 || row.vacantBeds > 0);

                new Chart(occupancyCanvas, {
                    type: 'bar',
                    data: {
                        labels: occupancyRows.length ? occupancyRows.map((row) => row.name) : ['No data'],
                        datasets: [
                            {
                                label: 'Occupied beds',
                                data: occupancyRows.length ? occupancyRows.map((row) => row.occupiedBeds) : [0],
                                backgroundColor: 'rgba(20,83,45,0.78)',
                                borderColor: 'rgba(20,83,45,1)',
                                borderWidth: 1,
                                borderRadius: 7,
                            },
                            {
                                label: 'Vacant beds',
                                data: occupancyRows.length ? occupancyRows.map((row) => row.vacantBeds) : [0],
                                backgroundColor: 'rgba(148,163,184,0.62)',
                                borderColor: 'rgba(100,116,139,0.95)',
                                borderWidth: 1,
                                borderRadius: 7,
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        indexAxis: 'y',
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    boxWidth: 10,
                                    boxHeight: 10,
                                    usePointStyle: true,
                                    pointStyle: 'circle',
                                }
                            },
                            tooltip: {
                                mode: 'nearest',
                                intersect: true,
                            }
                        },
                        scales: {
                            x: {
                                stacked: true,
                                beginAtZero: true,
                                ticks: { precision: 0 },
                                grid: { color: 'rgba(148,163,184,0.18)' }
                            },
                            y: {
                                stacked: true,
                                grid: { display: false },
                            }
                        }
                    }
                });
            }
        }
    });
</script>
@endpush

@endsection
