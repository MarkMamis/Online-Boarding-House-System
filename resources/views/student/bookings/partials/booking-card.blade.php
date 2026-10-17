@php
    $room = $booking->room;
    $property = $room?->property;
    $landlord = $property?->landlord;
    $onboarding = $booking->tenantOnboarding;
    $isOnboardingCompleted = ($onboarding?->status ?? '') === 'completed';

    $img = $room?->image_path ?: ($room?->roomImages?->first()?->image_path ?: ($property?->image_path ?? null));
    $monthlyRent = (float) (($booking->monthly_rent_amount ?? 0) > 0 ? $booking->monthly_rent_amount : ($room?->price ?? 0));
    $paymentStatus = method_exists($booking, 'derivedPaymentStatus') ? $booking->derivedPaymentStatus() : 'unpaid';
@endphp

<article class="card border-0 shadow-sm rounded-4 p-3 p-md-4 bg-white">
    <div class="row g-3 align-items-center">
        {{-- Left: Photo Thumbnail --}}
        <div class="col-12 col-sm-4 col-md-3 col-xl-2">
            <div class="rounded-3 overflow-hidden position-relative bg-light" style="aspect-ratio: 4/3;">
                @if(!empty($img))
                    <img 
                        src="{{ file_url($img) }}" 
                        alt="{{ $property->name ?? 'Boarding house' }}" 
                        class="w-100 h-100 object-fit-cover"
                        loading="lazy"
                    >
                @else
                    <div class="w-100 h-100 d-flex flex-column align-items-center justify-content-center text-muted">
                        <i class="bi bi-buildings fs-3 opacity-50 mb-1"></i>
                        <span class="small" style="font-size: 0.7rem;">MinSU Listing</span>
                    </div>
                @endif
                <span class="position-absolute top-0 start-0 m-2 badge bg-dark bg-opacity-75 text-white rounded-pill small" style="font-size: 0.68rem;">
                    Room {{ $room?->room_number ?? '—' }}
                </span>
            </div>
        </div>

        {{-- Middle: Booking Information --}}
        <div class="col-12 col-sm-8 col-md-5 col-xl-6">
            <div class="d-flex align-items-center gap-2 mb-1">
                <h3 class="h6 fw-bold text-dark mb-0 text-truncate">
                    {{ $property->name ?? 'Boarding House' }}
                </h3>
                <span class="badge bg-light text-secondary border rounded-pill small" style="font-size: 0.7rem;">
                    {{ ucfirst((string) ($booking->occupancy_mode ?? 'solo')) }}
                </span>
            </div>
            
            <p class="text-muted small mb-2 text-truncate">
                <i class="bi bi-geo-alt me-1 text-danger"></i>{{ $property->address ?? 'Near MinSU Campus' }}
            </p>

            <div class="d-flex flex-wrap align-items-center gap-3 small text-muted">
                <div>
                    <i class="bi bi-calendar-event text-success me-1"></i>
                    <span>{{ $booking->check_in?->format('M d, Y') ?? '—' }} &rarr; {{ $booking->check_out?->format('M d, Y') ?? '—' }}</span>
                </div>
                <div>
                    <i class="bi bi-cash-stack text-success me-1"></i>
                    <span class="fw-semibold text-dark">₱{{ number_format($monthlyRent, 0) }}</span>
                    <span>/ month</span>
                </div>
            </div>

            @if($booking->notes)
                <div class="mt-2 text-muted small fst-italic" style="font-size: 0.78rem;">
                    <i class="bi bi-chat-left-text me-1"></i>Note: {{ Str::limit($booking->notes, 90) }}
                </div>
            @endif
        </div>

        {{-- Right: Status & Contextual Actions --}}
        <div class="col-12 col-md-4 col-xl-4 text-md-end pt-3 pt-md-0 border-top border-md-0">
            <div class="mb-3">
                @if($booking->status === 'pending')
                    <span class="badge bg-warning text-dark px-3 py-1.5 rounded-pill fw-semibold">
                        <i class="bi bi-hourglass-split me-1"></i>Awaiting Landlord Review
                    </span>
                @elseif($booking->status === 'approved')
                    @if(!$isOnboardingCompleted)
                        <span class="badge bg-success bg-opacity-15 text-success border border-success border-opacity-25 px-3 py-1.5 rounded-pill fw-semibold">
                            <i class="bi bi-check-circle-fill me-1"></i>Approved — Move-in Pending
                        </span>
                    @else
                        <span class="badge bg-success px-3 py-1.5 rounded-pill fw-semibold">
                            <i class="bi bi-house-door-fill me-1"></i>Active Stay
                        </span>
                    @endif
                @elseif($booking->status === 'rejected')
                    <span class="badge bg-danger bg-opacity-15 text-danger border border-danger border-opacity-25 px-3 py-1.5 rounded-pill fw-semibold">
                        <i class="bi bi-x-circle me-1"></i>Request Declined
                    </span>
                @elseif($booking->status === 'cancelled')
                    <span class="badge bg-secondary bg-opacity-15 text-secondary border px-3 py-1.5 rounded-pill fw-semibold">
                        <i class="bi bi-dash-circle me-1"></i>Cancelled
                    </span>
                @endif
            </div>

            <div class="d-flex align-items-center justify-content-md-end gap-2 flex-wrap">
                @if($room)
                    <a href="{{ route('student.rooms.show', $room->id) }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                        View Room
                    </a>
                @endif

                @if($booking->status === 'approved')
                    @if(!$isOnboardingCompleted)
                        <a href="{{ route('student.onboarding.show', $onboarding?->id ?? 1) }}" class="btn btn-sm btn-success rounded-pill px-3.5 py-1.5 fw-semibold shadow-sm">
                            <i class="bi bi-clipboard-check me-1"></i>Complete Move-in
                        </a>
                    @else
                        <a href="{{ route('student.tenant.dashboard') }}" class="btn btn-sm btn-success rounded-pill px-3.5 py-1.5 fw-semibold shadow-sm">
                            <i class="bi bi-house-door me-1"></i>Open Stay Hub
                        </a>
                    @endif
                @elseif($booking->status === 'pending')
                    <button 
                        type="button" 
                        class="btn btn-sm btn-outline-danger rounded-pill px-3"
                        data-bs-toggle="modal" 
                        data-bs-target="#cancelModal_{{ $booking->id }}"
                    >
                        <i class="bi bi-trash me-1"></i>Cancel
                    </button>
                @endif
            </div>
        </div>
    </div>
</article>

{{-- Cancel Modal --}}
@if($booking->status === 'pending')
<div class="modal fade" id="cancelModal_{{ $booking->id }}" tabindex="-1" aria-labelledby="cancelModalLabel_{{ $booking->id }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('student.bookings.cancel', $booking->id) }}" method="POST" class="modal-content rounded-4 border-0 shadow p-3">
            @csrf
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="cancelModalLabel_{{ $booking->id }}">Cancel Boarding Request</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-2">
                <p class="text-muted small mb-3">
                    Are you sure you want to cancel your request for <strong>Room {{ $room?->room_number }}</strong> at <strong>{{ $property?->name }}</strong>?
                </p>
                <div class="mb-2">
                    <label class="form-label small fw-semibold text-dark">Reason for Cancellation (Optional)</label>
                    <input type="text" name="cancel_reason" class="form-control rounded-3" placeholder="e.g. Found another room, change of plans...">
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">Keep Booking</button>
                <button type="submit" class="btn btn-danger rounded-pill px-3.5 fw-semibold">Confirm Cancellation</button>
            </div>
        </form>
    </div>
</div>
@endif
