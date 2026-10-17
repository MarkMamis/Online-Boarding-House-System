@extends('layouts.student_dashboard')

@section('title', 'Home')

@section('content')
<div class="container-fluid px-3 px-md-4 py-3">

    {{-- ── 1. Hero Discovery Header ── --}}
    <div class="row justify-content-center text-center my-4 py-2">
        <div class="col-lg-8 col-xl-7">
            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-3 py-2 mb-3 fw-semibold small">
                <i class="bi bi-patch-check-fill me-1"></i>MinSU Accredited Student Accommodations
            </span>
            <h1 class="display-6 fw-bold text-dark mb-2">
                Find your next place near MinSU.
            </h1>
            <p class="text-muted fs-6 mb-4">
                Explore accredited boarding houses and rooms that fit your budget, lifestyle, and classes.
            </p>

            {{-- Functional Marketplace Search Bar --}}
            <form action="{{ route('student.rooms.index') }}" method="GET" class="market-search-bar d-flex align-items-center gap-2 mx-auto">
                <i class="bi bi-search text-muted ms-2 fs-5"></i>
                <input 
                    type="text" 
                    name="q" 
                    class="form-control border-0 shadow-none px-2 py-2" 
                    placeholder="Search boarding houses, barangays, or landmarks..."
                    value="{{ $search ?? '' }}"
                    aria-label="Search accommodations"
                >
                <button type="submit" class="btn btn-success rounded-pill px-4 py-2 fw-semibold d-inline-flex align-items-center gap-2">
                    <span>Search</span>
                </button>
            </form>
        </div>
    </div>

    {{-- ── 2. Context-Aware Student Lifecycle Banner ── --}}
    @if(($lifecycleState ?? 'searching') === 'move_in_pending' && !empty($currentApprovedBooking))
        {{-- Case C: Move-in Pending --}}
        <div class="card border-0 shadow-sm rounded-4 mb-4 bg-success bg-opacity-10 border-start border-4 border-success p-3 p-md-4">
            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                <div class="d-flex align-items-start gap-3">
                    <div class="rounded-circle bg-success text-white p-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px;">
                        <i class="bi bi-clipboard-check fs-4"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <span class="badge bg-success rounded-pill px-2.5 py-1">Booking Approved</span>
                            <span class="small text-muted">Next step: Complete Onboarding</span>
                        </div>
                        <h2 class="h5 fw-bold text-dark mb-1">
                            Continue your move-in to {{ $currentApprovedBooking->room->property->name ?? 'Boarding House' }}
                        </h2>
                        <p class="text-muted small mb-0">
                            Room {{ $currentApprovedBooking->room->room_number ?? '—' }} &bull; 
                            Check-in scheduled for {{ \Carbon\Carbon::parse($currentApprovedBooking->check_in)->format('M d, Y') }}.
                            Please complete your digital contract and move-in documents.
                        </p>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2 flex-shrink-0">
                    <a href="{{ route('student.onboarding.show', $currentApprovedBooking->tenantOnboarding->id ?? 1) }}" class="btn btn-success rounded-pill px-4 py-2 fw-semibold shadow-sm">
                        <i class="bi bi-arrow-right-circle me-1"></i> Continue Move-in
                    </a>
                </div>
            </div>
        </div>

    @elseif(($lifecycleState ?? 'searching') === 'booking_pending' && !empty($latestPendingBooking))
        {{-- Case B: Booking Pending --}}
        <div class="card border-0 shadow-sm rounded-4 mb-4 bg-warning bg-opacity-10 border-start border-4 border-warning p-3 p-md-4">
            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                <div class="d-flex align-items-start gap-3">
                    <div class="rounded-circle bg-warning text-dark p-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px;">
                        <i class="bi bi-hourglass-split fs-4"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <span class="badge bg-warning text-dark rounded-pill px-2.5 py-1">Under Review</span>
                            <span class="small text-muted">Submitted {{ $latestPendingBooking->created_at->diffForHumans() }}</span>
                        </div>
                        <h2 class="h5 fw-bold text-dark mb-1">
                            Your reservation request for {{ $latestPendingBooking->room->property->name ?? 'Boarding House' }}
                        </h2>
                        <p class="text-muted small mb-0">
                            Room {{ $latestPendingBooking->room->room_number ?? '—' }} &bull;
                            The landlord is reviewing your booking. You will be notified as soon as a decision is made.
                        </p>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2 flex-shrink-0">
                    <a href="{{ route('student.bookings.index') }}" class="btn btn-outline-dark rounded-pill px-4 py-2 fw-semibold">
                        View Booking Details
                    </a>
                </div>
            </div>
        </div>

    @elseif(($lifecycleState ?? 'searching') === 'completed_stay' && !empty($ratingReminders) && $ratingReminders->isNotEmpty())
        {{-- Case E: Completed Stay Review Prompt --}}
        @php $reminder = $ratingReminders->first(); @endphp
        <div class="card border-0 shadow-sm rounded-4 mb-4 bg-primary bg-opacity-10 border-start border-4 border-primary p-3 p-md-4">
            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                <div class="d-flex align-items-start gap-3">
                    <div class="rounded-circle bg-primary text-white p-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px;">
                        <i class="bi bi-star-fill fs-4"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <span class="badge bg-primary rounded-pill px-2.5 py-1">Completed Stay</span>
                        </div>
                        <h2 class="h5 fw-bold text-dark mb-1">
                            How was your stay at {{ $reminder->room->property->name ?? 'Boarding House' }}?
                        </h2>
                        <p class="text-muted small mb-0">
                            Your feedback helps fellow MinSU students find safe, comfortable accommodation.
                        </p>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2 flex-shrink-0">
                    <a href="{{ route('student.rooms.feedback_page', $reminder->room_id) }}" class="btn btn-primary rounded-pill px-4 py-2 fw-semibold shadow-sm">
                        <i class="bi bi-pencil-square me-1"></i> Leave a Review
                    </a>
                </div>
            </div>
        </div>
    @endif

    {{-- ── 3. Quick Discovery Category Chips ── --}}
    <div class="mb-4">
        <div class="chip-scroll-row">
            <a href="{{ route('student.rooms.index') }}" class="market-category-chip active">
                <i class="bi bi-grid"></i>
                <span>All Accommodations</span>
            </a>
            <a href="{{ route('student.rooms.index', ['occupancy' => 'per_room']) }}" class="market-category-chip">
                <i class="bi bi-person"></i>
                <span>Solo Rooms</span>
            </a>
            <a href="{{ route('student.rooms.index', ['occupancy' => 'per_bed']) }}" class="market-category-chip">
                <i class="bi bi-people"></i>
                <span>Bed-Spacer (Shared)</span>
            </a>
            <a href="{{ route('student.rooms.index', ['max_price' => 2000]) }}" class="market-category-chip">
                <i class="bi bi-tag"></i>
                <span>Under ₱2,000</span>
            </a>
        </div>
    </div>

    {{-- ── 4. Featured Accommodations Catalog ── --}}
    <div class="d-flex align-items-baseline justify-content-between mb-3">
        <div>
            <h2 class="h5 fw-bold text-dark mb-1">Available Accommodations</h2>
            <p class="text-muted small mb-0">Accredited student rooms ready for move-in near Mindoro State University.</p>
        </div>
        <a href="{{ route('student.rooms.index') }}" class="text-success fw-semibold small text-decoration-none d-inline-flex align-items-center gap-1">
            <span>Explore all ({{ $totalAvailableRoomsCount ?? count($featuredRooms ?? []) }})</span>
            <i class="bi bi-arrow-right"></i>
        </a>
    </div>

    @if(!empty($featuredRooms) && $featuredRooms->isNotEmpty())
        <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 row-cols-xxl-4 g-4 mb-4">
            @foreach($featuredRooms as $room)
                <x-room-card :room="$room" />
            @endforeach
        </div>
    @else
        <div class="card border-0 shadow-sm rounded-4 p-5 text-center my-4 bg-white">
            <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center mx-auto mb-3" style="width: 64px; height: 64px;">
                <i class="bi bi-house-exclamation text-muted fs-3"></i>
            </div>
            <h3 class="h5 fw-bold text-dark mb-1">No Available Rooms Matching Your Criteria</h3>
            <p class="text-muted small mb-3">We couldn't find any accredited rooms matching your current filter. Try expanding your search.</p>
            <div>
                <a href="{{ route('student.rooms.index') }}" class="btn btn-outline-success rounded-pill px-4 py-2 fw-semibold">
                    Browse All Accommodations
                </a>
            </div>
        </div>
    @endif

</div>
@endsection
