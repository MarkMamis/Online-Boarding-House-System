@extends('layouts.student_dashboard')

@section('title', 'My Bookings & Stays')

@section('content')
@php
    $today = now()->toDateString();
    $bookingItems = $bookings instanceof \Illuminate\Pagination\AbstractPaginator
        ? $bookings->getCollection()
        : collect($bookings);

    $activeOrUpcoming = $bookingItems->filter(function ($b) use ($today) {
        if ($b->status === 'pending') return true;
        if ($b->status === 'approved' && ($b->check_out === null || $b->check_out->toDateString() >= $today)) return true;
        return false;
    })->values();

    $pastStays = $bookingItems->filter(function ($b) use ($today) {
        if (in_array($b->status, ['rejected', 'cancelled'], true)) return true;
        if ($b->status === 'approved' && $b->check_out !== null && $b->check_out->toDateString() < $today) return true;
        return false;
    })->values();

    $totalCount = $bookingItems->count();
    $activeTab = request('tab', ($activeOrUpcoming->isNotEmpty() ? 'active' : ($pastStays->isNotEmpty() ? 'past' : 'active')));
@endphp

<div class="container-fluid px-3 px-md-4 py-3">

    {{-- ── 1. Page Header ── --}}
    <div class="mb-4">
        <h1 class="h3 fw-bold text-dark mb-1">My Bookings & Stays</h1>
        <p class="text-muted small mb-0">Track your room reservation requests, move-in progress, and stay history.</p>
    </div>

    {{-- Session Alerts --}}
    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4 d-flex align-items-center gap-2" role="alert">
            <i class="bi bi-check-circle-fill text-success fs-5"></i>
            <div>{{ session('success') }}</div>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger border-0 shadow-sm rounded-4 mb-4 d-flex align-items-center gap-2" role="alert">
            <i class="bi bi-exclamation-triangle-fill text-danger fs-5"></i>
            <div>{{ session('error') }}</div>
        </div>
    @endif

    {{-- ── 2. Navigation Tabs ── --}}
    <ul class="nav nav-pills mb-4 gap-2" id="staysTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button 
                class="nav-link rounded-pill px-4 py-2 fw-semibold {{ $activeTab === 'active' ? 'active bg-success' : 'text-dark bg-white border' }}" 
                id="active-tab" 
                data-bs-toggle="pill" 
                data-bs-target="#activeStaysPane" 
                type="button" 
                role="tab"
            >
                <i class="bi bi-calendar-check me-1.5"></i>
                <span>Active & Upcoming</span>
                @if($activeOrUpcoming->isNotEmpty())
                    <span class="badge {{ $activeTab === 'active' ? 'bg-white text-success' : 'bg-success text-white' }} rounded-pill ms-1.5">{{ $activeOrUpcoming->count() }}</span>
                @endif
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button 
                class="nav-link rounded-pill px-4 py-2 fw-semibold {{ $activeTab === 'past' ? 'active bg-success' : 'text-dark bg-white border' }}" 
                id="past-tab" 
                data-bs-toggle="pill" 
                data-bs-target="#pastStaysPane" 
                type="button" 
                role="tab"
            >
                <i class="bi bi-clock-history me-1.5"></i>
                <span>Past Stays & History</span>
                @if($pastStays->isNotEmpty())
                    <span class="badge {{ $activeTab === 'past' ? 'bg-white text-success' : 'bg-secondary text-white' }} rounded-pill ms-1.5">{{ $pastStays->count() }}</span>
                @endif
            </button>
        </li>
    </ul>

    {{-- ── 3. Tab Content ── --}}
    <div class="tab-content" id="staysTabContent">
        
        {{-- TAB 1: Active & Upcoming --}}
        <div class="tab-pane fade {{ $activeTab === 'active' ? 'show active' : '' }}" id="activeStaysPane" role="tabpanel">
            @if($activeOrUpcoming->isNotEmpty())
                <div class="d-flex flex-column gap-3 mb-5">
                    @foreach($activeOrUpcoming as $booking)
                        @include('student.bookings.partials.booking-card', ['booking' => $booking, 'today' => $today])
                    @endforeach
                </div>
            @else
                <div class="card border-0 shadow-sm rounded-4 p-5 text-center my-4 bg-white">
                    <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center mx-auto mb-3" style="width: 64px; height: 64px;">
                        <i class="bi bi-calendar-x text-muted fs-3"></i>
                    </div>
                    <h3 class="h5 fw-bold text-dark mb-1">No Active Reservations</h3>
                    <p class="text-muted small mb-4 mx-auto" style="max-width: 400px;">
                        You don't have any ongoing stays or pending booking requests right now. Start exploring accredited MinSU accommodations.
                    </p>
                    <div>
                        <a href="{{ route('student.rooms.index') }}" class="btn btn-success rounded-pill px-4 py-2 fw-semibold">
                            Explore Accommodations
                        </a>
                    </div>
                </div>
            @endif
        </div>

        {{-- TAB 2: Past Stays & History --}}
        <div class="tab-pane fade {{ $activeTab === 'past' ? 'show active' : '' }}" id="pastStaysPane" role="tabpanel">
            @if($pastStays->isNotEmpty())
                <div class="d-flex flex-column gap-3 mb-5">
                    @foreach($pastStays as $booking)
                        @include('student.bookings.partials.booking-card', ['booking' => $booking, 'today' => $today])
                    @endforeach
                </div>
            @else
                <div class="card border-0 shadow-sm rounded-4 p-5 text-center my-4 bg-white">
                    <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center mx-auto mb-3" style="width: 64px; height: 64px;">
                        <i class="bi bi-clock-history text-muted fs-3"></i>
                    </div>
                    <h3 class="h5 fw-bold text-dark mb-1">No Past Stays Recorded</h3>
                    <p class="text-muted small mb-0">Your completed stays and past reservation decisions will appear here.</p>
                </div>
            @endif
        </div>

    </div>

</div>
@endsection
