@extends('layouts.student_dashboard')

@section('title', 'Explore Rooms')

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
        ? 'Your verification document was not approved yet. Boarding requests stay locked until your School ID or COR/COE is validated by admin.'
        : 'Your academic verification is still in process. Boarding requests stay locked until admin validation is completed.';

    $hasActiveFilters = filled(request('q')) 
        || filled(request('search'))
        || filled(request('property_id'))
        || request('rating', 'any') !== 'any' 
        || request('occupancy', 'all') !== 'all' 
        || ($selectedAmenities ?? collect())->isNotEmpty()
        || request()->filled('min_price')
        || request()->filled('max_price');
@endphp

<div class="container-fluid px-3 px-md-4 py-3">

    {{-- ── 1. Page Header & Search Shell ── --}}
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
        <div>
            <h1 class="h3 fw-bold text-dark mb-1">Explore Rooms</h1>
            <p class="text-muted small mb-0">Discover and compare accredited accommodations near MinSU campus.</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button 
                type="button" 
                class="btn {{ $hasActiveFilters ? 'btn-success' : 'btn-outline-secondary' }} rounded-pill px-3.5 py-2 fw-semibold d-inline-flex align-items-center gap-2"
                data-bs-toggle="collapse" 
                data-bs-target="#marketplaceFilters" 
                aria-expanded="{{ $hasActiveFilters ? 'true' : 'false' }}"
                aria-controls="marketplaceFilters"
            >
                <i class="bi bi-sliders"></i>
                <span>Filters</span>
                @if($hasActiveFilters)
                    <span class="badge bg-white text-success rounded-pill px-1.5" style="font-size: 0.7rem;">Active</span>
                @endif
            </button>
        </div>
    </div>

    {{-- ── 2. Expandable Filter Form ── --}}
    <form method="GET" action="{{ route('student.rooms.index') }}" id="roomsFilterForm">
        {{-- Search Input Row --}}
        <div class="market-search-bar d-flex align-items-center gap-2 mb-3">
            <i class="bi bi-search text-muted ms-2 fs-5"></i>
            <input 
                type="text" 
                name="q" 
                class="form-control border-0 shadow-none px-2 py-2" 
                placeholder="Search by boarding house name, address, or barangay..."
                value="{{ request('q', request('search')) }}"
                aria-label="Search accommodations"
            >
            @if(request('q') || request('search'))
                <a href="{{ route('student.rooms.index', request()->except(['q', 'search'])) }}" class="btn btn-link text-muted p-0 me-2" title="Clear keyword">
                    <i class="bi bi-x-circle-fill"></i>
                </a>
            @endif
            <button type="submit" class="btn btn-success rounded-pill px-4 py-2 fw-semibold">
                Search
            </button>
        </div>

        {{-- Collapsible Filter Drawer --}}
        <div class="collapse {{ $hasActiveFilters ? 'show' : '' }} mb-4" id="marketplaceFilters">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                <div class="row g-4">
                    {{-- Price Bounds --}}
                    <div class="col-md-6 col-lg-3">
                        <label class="form-label fw-bold text-dark small mb-2 d-flex align-items-center gap-1.5">
                            <i class="bi bi-cash-stack text-success"></i> Monthly Budget
                        </label>
                        <div class="d-flex align-items-center gap-2">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-light">₱</span>
                                <input 
                                    type="number" 
                                    name="min_price" 
                                    class="form-control" 
                                    placeholder="Min ({{ (int) ($priceBoundsMin ?? 0) }})" 
                                    min="0" 
                                    value="{{ request()->filled('min_price') ? request('min_price') : '' }}"
                                >
                            </div>
                            <span class="text-muted">–</span>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-light">₱</span>
                                <input 
                                    type="number" 
                                    name="max_price" 
                                    class="form-control" 
                                    placeholder="Max ({{ (int) ($priceBoundsMax ?? 10000) }})" 
                                    min="0" 
                                    value="{{ request()->filled('max_price') ? request('max_price') : '' }}"
                                >
                            </div>
                        </div>
                    </div>

                    {{-- Occupancy Mode --}}
                    <div class="col-md-6 col-lg-3">
                        <label class="form-label fw-bold text-dark small mb-2 d-flex align-items-center gap-1.5">
                            <i class="bi bi-door-open text-primary"></i> Occupancy Mode
                        </label>
                        <div class="d-flex flex-column gap-1.5">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="occupancy" id="occAll" value="all" @checked(request('occupancy', 'all') === 'all')>
                                <label class="form-check-label small" for="occAll">All types</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="occupancy" id="occRoom" value="per_room" @checked(request('occupancy') === 'per_room')>
                                <label class="form-check-label small" for="occRoom">Solo room (Entire)</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="occupancy" id="occBed" value="per_bed" @checked(request('occupancy') === 'per_bed')>
                                <label class="form-check-label small" for="occBed">Bed-spacer (Shared)</label>
                            </div>
                        </div>
                    </div>

                    {{-- Ratings --}}
                    <div class="col-md-6 col-lg-3">
                        <label class="form-label fw-bold text-dark small mb-2 d-flex align-items-center gap-1.5">
                            <i class="bi bi-star text-warning"></i> Student Ratings
                        </label>
                        <div class="d-flex flex-column gap-1.5">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="rating" id="rateAny" value="any" @checked(request('rating', 'any') === 'any')>
                                <label class="form-check-label small" for="rateAny">Any rating</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="rating" id="rate4" value="4_up" @checked(request('rating') === '4_up')>
                                <label class="form-check-label small" for="rate4">4.0 stars & above</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="rating" id="rate3" value="3_up" @checked(request('rating') === '3_up')>
                                <label class="form-check-label small" for="rate3">3.0 stars & above</label>
                            </div>
                        </div>
                    </div>

                    {{-- Key Amenities --}}
                    <div class="col-md-6 col-lg-3">
                        <label class="form-label fw-bold text-dark small mb-2 d-flex align-items-center gap-1.5">
                            <i class="bi bi-patch-check text-info"></i> Inclusions & Amenities
                        </label>
                        <div class="row row-cols-2 g-1" style="max-height: 120px; overflow-y: auto;">
                            @foreach(($amenityOptions ?? []) as $key => $label)
                                <div class="col">
                                    <div class="form-check">
                                        <input 
                                            class="form-check-input" 
                                            type="checkbox" 
                                            name="amenities[]" 
                                            id="amenity_{{ $key }}" 
                                            value="{{ $key }}"
                                            @checked(($selectedAmenities ?? collect())->contains($key))
                                        >
                                        <label class="form-check-label small text-truncate" for="amenity_{{ $key }}" title="{{ $label }}">
                                            {{ $label }}
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Action Row --}}
                <div class="d-flex align-items-center justify-content-between pt-3 border-top mt-3">
                    <a href="{{ route('student.rooms.index') }}" class="btn btn-sm btn-link text-secondary text-decoration-none p-0">
                        <i class="bi bi-arrow-counterclockwise me-1"></i>Reset all filters
                    </a>
                    <button type="submit" class="btn btn-sm btn-success rounded-pill px-4 py-1.5 fw-semibold">
                        Apply Filters
                    </button>
                </div>
            </div>
        </div>
    </form>

    {{-- ── 3. Results Header & Count ── --}}
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h2 class="h5 fw-bold text-dark mb-0">
            {{ $allRooms->count() }} {{ Str::plural('accommodation', $allRooms->count()) }} available
        </h2>
        @if($hasActiveFilters)
            <a href="{{ route('student.rooms.index') }}" class="small text-danger text-decoration-none fw-semibold">
                <i class="bi bi-x-circle me-1"></i>Clear all filters
            </a>
        @endif
    </div>

    {{-- ── 4. Accommodation Marketplace Grid ── --}}
    @if($allRooms->isNotEmpty())
        <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 row-cols-xxl-4 g-4 mb-5">
            @foreach($allRooms as $room)
                <x-room-card :room="$room" />
            @endforeach
        </div>
    @else
        <div class="card border-0 shadow-sm rounded-4 p-5 text-center my-4 bg-white">
            <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center mx-auto mb-3" style="width: 68px; height: 68px;">
                <i class="bi bi-search text-muted fs-3"></i>
            </div>
            <h3 class="h5 fw-bold text-dark mb-1">No Accommodations Found</h3>
            <p class="text-muted small mb-4 mx-auto" style="max-width: 420px;">
                We couldn't find any accredited rooms matching your exact search criteria. Try clearing some filters or expanding your budget range.
            </p>
            <div>
                <a href="{{ route('student.rooms.index') }}" class="btn btn-success rounded-pill px-4 py-2 fw-semibold">
                    <i class="bi bi-arrow-counterclockwise me-1"></i> View All Available Rooms
                </a>
            </div>
        </div>
    @endif

</div>

{{-- Academic Verification Modal (Informational, doesn't block browsing) --}}
@if($showVerificationModal)
<div class="modal fade" id="studentVerificationPendingModal" tabindex="-1" aria-labelledby="studentVerificationPendingModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow p-3">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="studentVerificationPendingModalLabel">
                    <i class="bi bi-shield-exclamation text-warning me-2"></i>{{ $verificationModalTitle }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-2">
                <p class="mb-2 text-dark">{{ $verificationModalMessage }}</p>
                <p class="small text-muted mb-0">You can freely browse accredited rooms and boarding houses while your student status is validated.</p>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Got it</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const modalEl = document.getElementById('studentVerificationPendingModal');
        if (modalEl && window.bootstrap && !sessionStorage.getItem('dismissed_verification_notice')) {
            const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
            modal.show();
            sessionStorage.setItem('dismissed_verification_notice', 'true');
        }
    });
</script>
@endpush
@endif

@endsection
