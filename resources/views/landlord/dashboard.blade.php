@extends('layouts.landlord')

@section('title', 'Landlord Home')

@section('content')
@php
    $landlordProfile = Auth::user()->landlordProfile;
    $hasPermitFile = filled(optional($landlordProfile)->business_permit_path);
    $permitStatus = $setupSnapshot['permit_status'] ?? (optional($landlordProfile)->business_permit_status ?: ($hasPermitFile ? 'pending' : 'not_submitted'));
    $profileComplete = (bool) ($setupSnapshot['profile_complete'] ?? false);
    $billingComplete = (bool) ($setupSnapshot['billing_complete'] ?? false);
    $landlordOperationsLocked = !$profileComplete || !$billingComplete;

    $totalActionCount = (int) ($totalActionCount ?? ($pendingRequests + ($pendingOnboardingsCount ?? 0) + ($pendingMonthlyPaymentsCount ?? 0) + ($overduePaymentsCount ?? 0)));
@endphp

<div class="landlord-home-container mb-5">
    <!-- 1. HEADER & GREETING -->
    <div class="glass-card rounded-4 p-4 p-md-5 mb-4 border">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <div>
                <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                    <h1 class="h2 mb-0 fw-bold text-dark">Good day, <span class="text-success" style="color:#14532d!important;">{{ Auth::user()->name }}</span>!</h1>
                    <span class="badge rounded-pill bg-success-subtle text-success border border-success-subtle px-3 py-1">Owner Workspace</span>
                </div>
                <p class="text-secondary mb-0">
                    @if($propertiesCount > 0)
                        Portfolio: <strong>{{ $propertiesCount }}</strong> {{ Str::plural('Boarding House', $propertiesCount) }}
                        @if(($totalBeds ?? 0) > 0)
                            &bull; <strong>{{ $occupiedBeds ?? 0 }}</strong> of <strong>{{ $totalBeds }}</strong> Beds Occupied ({{ $bedOccupancyRate ?? 0 }}% Occupancy)
                        @endif
                    @else
                        Welcome to your OBHS property management workspace.
                    @endif
                </p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                @if(!$landlordOperationsLocked)
                    <a href="{{ route('landlord.properties.create') }}" class="btn btn-brand rounded-pill px-4">
                        <i class="bi bi-plus-circle me-1"></i> Add Boarding House
                    </a>
                @else
                    <a href="{{ route('landlord.setup.show') }}" class="btn btn-warning rounded-pill px-4">
                        <i class="bi bi-exclamation-circle me-1"></i> Complete Profile Setup
                    </a>
                @endif
            </div>
        </div>

        {{-- Setup alert if incomplete --}}
        @if($landlordOperationsLocked)
            <div class="alert alert-warning rounded-4 mt-4 mb-0 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="fs-3 text-warning"><i class="bi bi-shield-exclamation"></i></div>
                    <div>
                        <strong>Account Setup Required</strong>
                        <div class="small text-secondary">Complete your profile details, contact information, and business permit to accept student bookings.</div>
                    </div>
                </div>
                <a href="{{ route('landlord.setup.show') }}" class="btn btn-sm btn-dark rounded-pill px-3">Finish Setup</a>
            </div>
        @endif
    </div>

    <!-- 2. MAIN SECTION: NEEDS YOUR ATTENTION TODAY -->
    <div class="glass-card rounded-4 p-4 mb-4 border">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="d-flex align-items-center gap-2">
                <div class="p-2 rounded-3 bg-danger-subtle text-danger d-inline-flex align-items-center justify-content-center" style="width:36px;height:36px;">
                    <i class="bi bi-lightning-charge-fill"></i>
                </div>
                <h2 class="h5 mb-0 fw-bold text-dark">Needs Your Attention Today</h2>
            </div>
            @if($totalActionCount > 0)
                <span class="badge rounded-pill bg-danger px-3 py-1">{{ $totalActionCount }} {{ Str::plural('Task', $totalActionCount) }} Pending</span>
            @endif
        </div>

        @if($totalActionCount > 0)
            <div class="d-flex flex-column gap-3">
                {{-- A. Monthly Rent Confirmations --}}
                @if(($pendingMonthlyPaymentsCount ?? 0) > 0)
                    <div class="border rounded-4 p-3 bg-white shadow-sm">
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-2">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge rounded-pill bg-danger-subtle text-danger border border-danger-subtle">Payment Verification</span>
                                <strong class="text-dark">{{ $pendingMonthlyPaymentsCount }} {{ Str::plural('Monthly Payment', $pendingMonthlyPaymentsCount) }} Submitted</strong>
                            </div>
                            <a href="{{ route('landlord.payments.index') }}" class="btn btn-sm btn-outline-danger rounded-pill px-3">
                                Review Payments <i class="bi bi-arrow-right ms-1"></i>
                            </a>
                        </div>
                        <div class="small text-secondary mb-2">Students have uploaded proof of rental payment awaiting your confirmation:</div>
                        <div class="row g-2">
                            @foreach($pendingMonthlyPaymentsList ?? [] as $payItem)
                                <div class="col-12 col-md-6">
                                    <div class="p-2 rounded-3 bg-light border d-flex justify-content-between align-items-center">
                                        <div class="min-w-0 me-2">
                                            <div class="fw-semibold text-truncate text-dark">{{ $payItem->student->name ?? 'Student' }}</div>
                                            <div class="small text-muted text-truncate">{{ $payItem->booking->room->property->name ?? 'Property' }} &bull; {{ $payItem->booking->room->room_number ?? 'Room' }}</div>
                                        </div>
                                        <div class="text-end">
                                            <div class="fw-bold text-success">₱{{ number_format((float) ($payItem->amount ?? 0), 2) }}</div>
                                            <a href="{{ route('landlord.payments.index') }}" class="btn btn-xs btn-outline-success rounded-pill px-2 py-0" style="font-size:0.75rem;">Confirm</a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- B. Move-In Onboarding Steps --}}
                @if(($pendingOnboardingsCount ?? 0) > 0)
                    <div class="border rounded-4 p-3 bg-white shadow-sm">
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-2">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge rounded-pill bg-warning-subtle text-warning-emphasis border border-warning-subtle">Move-In Progress</span>
                                <strong class="text-dark">{{ $pendingOnboardingsCount }} {{ Str::plural('Student Move-In', $pendingOnboardingsCount) }} Awaiting Review</strong>
                            </div>
                            <a href="{{ route('landlord.onboarding.index') }}" class="btn btn-sm btn-outline-warning text-dark rounded-pill px-3">
                                View Move-In Queue <i class="bi bi-arrow-right ms-1"></i>
                            </a>
                        </div>
                        <div class="small text-secondary mb-2">Approved applicants have submitted verification documents or advance deposits:</div>
                        <div class="row g-2">
                            @foreach($pendingOnboardingsList ?? [] as $onbItem)
                                <div class="col-12 col-md-6">
                                    <div class="p-2 rounded-3 bg-light border d-flex justify-content-between align-items-center">
                                        <div class="min-w-0 me-2">
                                            <div class="fw-semibold text-truncate text-dark">{{ $onbItem->booking->student->name ?? 'Student' }}</div>
                                            <div class="small text-muted text-truncate">{{ $onbItem->booking->room->property->name ?? 'Property' }} &bull; {{ $onbItem->booking->room->room_number ?? 'Room' }}</div>
                                        </div>
                                        <div>
                                            <a href="{{ route('landlord.onboarding.review', $onbItem->id) }}" class="btn btn-xs btn-outline-dark rounded-pill px-2 py-1" style="font-size:0.75rem;">
                                                Review Step
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- C. Pending Boarding Requests --}}
                @if(($pendingRequests ?? 0) > 0)
                    <div class="border rounded-4 p-3 bg-white shadow-sm">
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-2">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge rounded-pill bg-info-subtle text-info-emphasis border border-info-subtle">Booking Inquiries</span>
                                <strong class="text-dark">{{ $pendingRequests }} New Boarding {{ Str::plural('Request', $pendingRequests) }}</strong>
                            </div>
                            <a href="{{ route('landlord.bookings.index') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                Review Requests <i class="bi bi-arrow-right ms-1"></i>
                            </a>
                        </div>
                        <div class="small text-secondary mb-2">Students have requested accommodation in your rooms:</div>
                        <div class="row g-2">
                            @foreach($pendingBookingsList ?? [] as $bookItem)
                                <div class="col-12 col-md-6">
                                    <div class="p-2 rounded-3 bg-light border d-flex justify-content-between align-items-center">
                                        <div class="min-w-0 me-2">
                                            <div class="fw-semibold text-truncate text-dark">{{ $bookItem->student->name ?? 'Applicant' }}</div>
                                            <div class="small text-muted text-truncate">{{ $bookItem->room->property->name ?? 'Property' }} &bull; Room {{ $bookItem->room->room_number ?? '-' }}</div>
                                        </div>
                                        <div>
                                            <a href="{{ route('landlord.bookings.index') }}" class="btn btn-xs btn-outline-success rounded-pill px-2 py-1" style="font-size:0.75rem;">
                                                Decide
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- D. Overdue Rent Alert --}}
                @if(($overduePaymentsCount ?? 0) > 0)
                    <div class="border border-danger-subtle rounded-4 p-3 bg-danger-subtle d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
                        <div class="d-flex align-items-center gap-2 text-danger">
                            <i class="bi bi-exclamation-octagon-fill fs-5"></i>
                            <div>
                                <strong>{{ $overduePaymentsCount }} tenant {{ Str::plural('payment', $overduePaymentsCount) }} past due date.</strong>
                                <div class="small text-danger-emphasis">Remind tenants or follow up on pending monthly rent balances.</div>
                            </div>
                        </div>
                        <a href="{{ route('landlord.payments.index', ['status' => 'overdue']) }}" class="btn btn-sm btn-danger rounded-pill px-3">
                            View Overdue Roster
                        </a>
                    </div>
                @endif
            </div>
        @else
            {{-- Calm Empty State --}}
            <div class="text-center py-4 py-md-5">
                <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-success-subtle text-success mb-3" style="width:64px;height:64px;">
                    <i class="bi bi-check-circle-fill fs-2"></i>
                </div>
                <h3 class="h5 fw-bold text-dark mb-1">You're all caught up!</h3>
                <p class="text-secondary small mb-3">No pending booking requests, unverified payments, or documents require your action right now.</p>
                <div class="d-flex justify-content-center gap-2 flex-wrap">
                    <a href="{{ route('landlord.tenants.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                        <i class="bi bi-people me-1"></i> View Current Residents
                    </a>
                    <a href="{{ route('landlord.properties.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                        <i class="bi bi-buildings me-1"></i> Manage Boarding Houses
                    </a>
                </div>
            </div>
        @endif
    </div>

    <!-- 3. YOUR BOARDING HOUSES (Compact & Actionable) -->
    <div class="glass-card rounded-4 p-4 border">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h2 class="h5 mb-0 fw-bold text-dark">Your Boarding Houses</h2>
                <div class="small text-secondary">Summary of your properties and real-time room capacity.</div>
            </div>
            @if($propertiesCount > 0)
                <a href="{{ route('landlord.properties.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                    View All ({{ $propertiesCount }})
                </a>
            @endif
        </div>

        @if($properties->isNotEmpty())
            <div class="row g-3">
                @foreach($properties as $property)
                    @php
                        $propTotalBeds = (int) $property->rooms->sum('capacity');
                        $propOccupiedBeds = (int) $property->rooms->sum(function($r) {
                            return max(0, $r->capacity - $r->slots_available);
                        });
                        $propAvailableBeds = max(0, $propTotalBeds - $propOccupiedBeds);
                        $isFull = $propTotalBeds > 0 && $propAvailableBeds === 0;

                        $propStatusBadge = match ($property->status) {
                            'approved' => 'bg-success-subtle text-success border border-success-subtle',
                            'pending' => 'bg-warning-subtle text-warning-emphasis border border-warning-subtle',
                            'rejected' => 'bg-danger-subtle text-danger border border-danger-subtle',
                            default => 'bg-secondary-subtle text-secondary border border-secondary-subtle',
                        };
                    @endphp
                    <div class="col-12 col-md-6 col-lg-4">
                        <div class="card h-100 rounded-4 border shadow-sm overflow-hidden d-flex flex-column">
                            @if(!empty($property->cover_image))
                                <div style="height:140px;background:#f8fafc;position:relative;overflow:hidden;">
                                    <img src="{{ file_url($property->cover_image) }}" alt="{{ $property->name }}" class="w-100 h-100" style="object-fit:cover;">
                                    <span class="badge rounded-pill {{ $propStatusBadge }} position-absolute top-0 end-0 m-2">
                                        {{ ucfirst($property->status) }}
                                    </span>
                                </div>
                            @else
                                <div style="height:100px;background:rgba(20,83,45,0.06);position:relative;" class="d-flex align-items-center justify-content-center text-secondary">
                                    <i class="bi bi-buildings fs-1 opacity-50"></i>
                                    <span class="badge rounded-pill {{ $propStatusBadge }} position-absolute top-0 end-0 m-2">
                                        {{ ucfirst($property->status) }}
                                    </span>
                                </div>
                            @endif
                            <div class="p-3 d-flex flex-column flex-grow-1">
                                <h3 class="h6 fw-bold text-dark mb-1 text-truncate" title="{{ $property->name }}">{{ $property->name }}</h3>
                                <div class="small text-secondary mb-2 text-truncate">
                                    <i class="bi bi-geo-alt me-1"></i>{{ $property->address }}
                                </div>
                                <div class="mt-auto pt-2 border-top">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="small text-muted">{{ $property->rooms->count() }} {{ Str::plural('Room', $property->rooms->count()) }}</span>
                                        @if($isFull)
                                            <span class="badge rounded-pill bg-danger-subtle text-danger">Full ({{ $propTotalBeds }} Beds)</span>
                                        @elseif($propAvailableBeds > 0)
                                            <span class="badge rounded-pill bg-success-subtle text-success">{{ $propAvailableBeds }} {{ Str::plural('Bed', $propAvailableBeds) }} Available</span>
                                        @else
                                            <span class="badge rounded-pill bg-secondary-subtle text-secondary">No Beds Listed</span>
                                        @endif
                                    </div>
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('landlord.properties.show', $property->id) }}" class="btn btn-sm btn-outline-brand rounded-pill w-100">
                                            Manage Property
                                        </a>
                                        <a href="{{ route('landlord.properties.rooms.create', $property->id) }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3" title="Add Room">
                                            <i class="bi bi-plus-lg"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            {{-- Empty properties state --}}
            <div class="text-center py-4 py-md-5 border rounded-4 bg-light">
                <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-white text-secondary shadow-sm mb-3" style="width:56px;height:56px;">
                    <i class="bi bi-buildings fs-2 text-success"></i>
                </div>
                <h3 class="h6 fw-bold text-dark mb-1">Add your first boarding house</h3>
                <p class="text-secondary small mb-3">You haven't listed any boarding houses yet. Create your property to start listing rooms.</p>
                <a href="{{ route('landlord.properties.create') }}" class="btn btn-brand rounded-pill px-4">
                    <i class="bi bi-plus-circle me-1"></i> Add Boarding House
                </a>
            </div>
        @endif
    </div>
</div>
@endsection
