@extends('layouts.landlord')

@section('title', 'Dashboard')

@section('content')
    @php
        $occupiedRooms = max(0, (int) $totalRooms - (int) $vacantRooms);
        $occupancyPct = ((int) $totalRooms) > 0 ? (int) round(($occupiedRooms / (int) $totalRooms) * 100) : 0;
        $unreadMessagesSafe = (int) ($unreadMessages ?? 0);
    @endphp

    <div class="glass-card rounded-4 p-4 p-md-5 mb-4">
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
            <div class="min-w-0">
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <h1 class="h2 mb-0 fw-semibold">Welcome, <span class="highlight">{{ Auth::user()->full_name }}</span></h1>
                    <span class="badge rounded-pill text-bg-light border">Landlord Dashboard</span>
                </div>
                <p class="mb-0 text-secondary mt-2">Track occupancy, requests, and messages across your properties.</p>
            </div>
            <div class="d-flex flex-wrap gap-2">
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
                <a href="{{ route('landlord.bookings.index') }}" class="btn btn-outline-secondary rounded-pill px-4">
                    <i class="bi bi-journal-check me-1"></i> Requests
                    @if($pendingRequests > 0)
                        <span class="badge rounded-pill text-bg-warning ms-1">{{ $pendingRequests }}</span>
                    @endif
                </a>
            </div>
        </div>

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
                        <div class="metric-ic"><i class="bi bi-check2-circle"></i></div>
                        <div>
                            <div class="h4 mb-0">{{ $vacantRooms }}</div>
                            <div class="small metric-label">Vacant Rooms</div>
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
                            <a href="{{ route('landlord.rooms.index') }}" class="text-decoration-none d-block metric-tile h-100">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="metric-ic"><i class="bi bi-door-open"></i></div>
                                    <div class="min-w-0">
                                        <div class="fw-semibold text-dark">Rooms</div>
                                        <div class="small metric-label">Manage pricing and availability</div>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col-12 col-md-6">
                            <a href="{{ route('landlord.bookings.index') }}" class="text-decoration-none d-block metric-tile h-100">
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
                            </a>
                        </div>
                        <div class="col-12 col-md-6">
                            <a href="{{ route('landlord.messages.index') }}" class="text-decoration-none d-block metric-tile h-100">
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
                            </a>
                        </div>
                        <div class="col-12 col-md-6">
                            <a href="{{ route('landlord.tenants.index') }}" class="text-decoration-none d-block metric-tile h-100">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="metric-ic"><i class="bi bi-people"></i></div>
                                    <div class="min-w-0">
                                        <div class="fw-semibold text-dark">Tenants</div>
                                        <div class="small metric-label">View current tenants</div>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col-12 col-md-6">
                            <a href="{{ route('landlord.onboarding.index') }}" class="text-decoration-none d-block metric-tile h-100">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="metric-ic"><i class="bi bi-clipboard-check"></i></div>
                                    <div class="min-w-0">
                                        <div class="fw-semibold text-dark">Onboarding</div>
                                        <div class="small metric-label">Review tenant documents</div>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col-12 col-md-6">
                            <a href="{{ route('landlord.maintenance.index') }}" class="text-decoration-none d-block metric-tile h-100">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="metric-ic"><i class="bi bi-tools"></i></div>
                                    <div class="min-w-0">
                                        <div class="fw-semibold text-dark">Maintenance</div>
                                        <div class="small metric-label">Track issues and repairs</div>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col-12 col-md-6">
                            <a href="{{ route('landlord.payments.index') }}" class="text-decoration-none d-block metric-tile h-100">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="metric-ic"><i class="bi bi-cash-coin"></i></div>
                                    <div class="min-w-0">
                                        <div class="fw-semibold text-dark">Payments</div>
                                        <div class="small metric-label">Review billing and dues</div>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col-12 col-md-6">
                            <a href="{{ route('landlord.analytics.index') }}" class="text-decoration-none d-block metric-tile h-100">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="metric-ic"><i class="bi bi-graph-up"></i></div>
                                    <div class="min-w-0">
                                        <div class="fw-semibold text-dark">Analytics</div>
                                        <div class="small metric-label">View performance insights</div>
                                    </div>
                                </div>
                            </a>
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
                        <a href="{{ route('landlord.bookings.index') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <span><i class="bi bi-journal-check me-2"></i>Review booking requests</span>
                            <span class="badge rounded-pill {{ $pendingRequests > 0 ? 'text-bg-warning' : 'text-bg-secondary' }}">{{ $pendingRequests }}</span>
                        </a>
                        <a href="{{ route('landlord.messages.index') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <span><i class="bi bi-chat-dots me-2"></i>Reply to messages</span>
                            <span class="badge rounded-pill {{ $unreadMessagesSafe > 0 ? 'text-bg-danger' : 'text-bg-secondary' }}">{{ $unreadMessagesSafe }}</span>
                        </a>
                        <a href="{{ route('landlord.rooms.index') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <span><i class="bi bi-door-open me-2"></i>Update room availability</span>
                            <span class="badge rounded-pill {{ $vacantRooms > 0 ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $vacantRooms }}</span>
                        </a>
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
                                        <div class="fw-semibold">Room {{ $room->room_number }}</div>
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

@push('modals')
    @if(!empty($needsPaymentSetup))
        <div class="modal fade" id="paymentSetupReminderModal" tabindex="-1" aria-labelledby="paymentSetupReminderModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content rounded-4">
                    <div class="modal-header border-0 pb-0">
                        <h5 class="modal-title fw-semibold" id="paymentSetupReminderModalLabel">
                            <i class="bi bi-exclamation-circle text-warning me-2"></i>Set Up Your Payment Method
                        </h5>
                    </div>
                    <div class="modal-body pt-2">
                        <p class="mb-3">Please select your preferred payment method(s) and complete required payment details before receiving tenant payments.</p>
                        <div class="small text-muted mb-2">Required setup options:</div>
                        <ul class="small mb-0">
                            <li>Bank: <strong>Bank Name</strong> and <strong>Account Name</strong></li>
                            <li>GCash: <strong>GCash Name</strong>, <strong>GCash Number</strong>, and <strong>GCash QR Code</strong></li>
                            <li>Cash: select <strong>Cash</strong> as a preferred method</li>
                        </ul>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">Later</button>
                        <a href="{{ route('landlord.profile.edit') }}" class="btn btn-brand rounded-pill px-4">
                            <i class="bi bi-gear me-1"></i>Set Up Now
                        </a>
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
                                <label class="form-label small">Price *</label>
                                <input type="number" min="0" step="0.01" name="price" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small">Status *</label>
                                <select name="status" class="form-select" required>
                                    <option value="available">Available</option>
                                    <option value="occupied">Occupied</option>
                                    <option value="maintenance">Maintenance</option>
                                </select>
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
<script>
    document.addEventListener('DOMContentLoaded', () => {
        @if(!empty($needsPaymentSetup))
            const paymentSetupModalEl = document.getElementById('paymentSetupReminderModal');
            if (paymentSetupModalEl && window.bootstrap?.Modal) {
                const paymentSetupModal = new bootstrap.Modal(paymentSetupModalEl);
                paymentSetupModal.show();
            }
        @endif

        const form = document.getElementById('quickRoomForm');
        if(form){
            form.addEventListener('submit', function(){
                const pid = this.querySelector('select[name="property_id"]').value;
                this.action = `/landlord/dashboard/properties/${pid}/rooms`;
            });
        }
    });
</script>
@endpush

@endsection