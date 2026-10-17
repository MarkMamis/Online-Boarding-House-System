@props(['room'])

@php
    $imagePath = $room->image_path 
        ?: ($room->roomImages->first()?->image_path 
            ?? ($room->property->image_path ?? null));
            
    $pricingModel = method_exists($room, 'resolvePricingModel') 
        ? $room->resolvePricingModel() 
        : 'per_room';
        
    $pricePerRoom = method_exists($room, 'effectivePricePerRoom') 
        ? $room->effectivePricePerRoom() 
        : (float) ($room->price ?? 0);
        
    $pricePerBed = method_exists($room, 'effectivePricePerBed') 
        ? $room->effectivePricePerBed() 
        : (float) ($room->price ?? 0);
        
    $capacity = max(1, (int) ($room->capacity ?? 1));
    $slotsAvailable = (int) ($room->slots_available ?? 0);
    $isAvailable = $slotsAvailable > 0 && ($room->status ?? 'available') !== 'maintenance';

    // Real ratings only
    $avgRating = (float) ($room->feedbacks_avg_rating ?? ($room->feedbacks?->avg('rating') ?? 0));
    $feedbackCount = (int) ($room->feedbacks_count ?? ($room->feedbacks?->count() ?? 0));

    // Verified status
    $isAccredited = ($room->property?->approval_status ?? '') === 'approved';
@endphp

<article class="col">
    <a href="{{ route('student.rooms.show', $room->id) }}" class="card room-market-card h-100 text-decoration-none border-0 shadow-sm overflow-hidden position-relative" aria-label="{{ $room->property->name ?? 'Boarding house' }} - Room {{ $room->room_number }}">
        <!-- Photo Container -->
        <div class="room-photo-wrapper position-relative">
            @if(!empty($imagePath))
                <img 
                    src="{{ file_url($imagePath) }}" 
                    alt="{{ $room->property->name ?? 'Boarding house' }} - Room {{ $room->room_number }}" 
                    class="room-photo img-fluid w-100" 
                    loading="lazy"
                >
            @else
                <div class="room-photo-fallback d-flex flex-column align-items-center justify-content-center w-100 h-100 bg-light text-muted">
                    <i class="bi bi-buildings fs-1 text-secondary opacity-50 mb-1"></i>
                    <span class="small fw-semibold text-secondary">No Photo Available</span>
                </div>
            @endif

            <!-- Top Left: Accreditation Badge (Authoritative only) -->
            @if($isAccredited)
                <div class="position-absolute top-0 start-0 m-3">
                    <span class="badge bg-white text-dark shadow-sm px-2.5 py-1.5 rounded-pill fw-semibold small d-inline-flex align-items-center gap-1 border">
                        <i class="bi bi-patch-check-fill text-success"></i>
                        <span>Accredited</span>
                    </span>
                </div>
            @endif

            <!-- Top Right: Availability Badge -->
            <div class="position-absolute top-0 end-0 m-3">
                @if(!$isAvailable)
                    <span class="badge bg-dark bg-opacity-75 text-white px-2.5 py-1.5 rounded-pill small fw-medium">
                        Fully Booked
                    </span>
                @elseif($capacity === 1)
                    <span class="badge bg-success bg-opacity-90 text-white px-2.5 py-1.5 rounded-pill small fw-medium">
                        Solo Room
                    </span>
                @else
                    <span class="badge bg-dark bg-opacity-75 text-white px-2.5 py-1.5 rounded-pill small fw-medium">
                        {{ $slotsAvailable }} {{ Str::plural('slot', $slotsAvailable) }} left
                    </span>
                @endif
            </div>
        </div>

        <!-- Card Body -->
        <div class="card-body p-3 d-flex flex-column justify-content-between">
            <div>
                <!-- Property Title & Optional Rating -->
                <div class="d-flex align-items-start justify-content-between gap-2 mb-1">
                    <h3 class="h6 mb-0 fw-bold text-dark text-truncate flex-grow-1" title="{{ $room->property->name ?? 'Boarding House' }}">
                        {{ $room->property->name ?? 'Boarding House' }}
                    </h3>
                    @if($feedbackCount > 0)
                        <div class="d-flex align-items-center gap-1 text-dark small fw-semibold flex-shrink-0">
                            <i class="bi bi-star-fill text-warning" style="font-size: 0.8rem;"></i>
                            <span>{{ number_format($avgRating, 1) }}</span>
                            <span class="text-muted fw-normal" style="font-size: 0.75rem;">({{ $feedbackCount }})</span>
                        </div>
                    @endif
                </div>

                <!-- Room Number & Location -->
                <div class="d-flex align-items-center gap-2 small text-muted mb-2">
                    <span class="fw-semibold text-dark">Room {{ $room->room_number }}</span>
                    <span>&bull;</span>
                    <span class="text-truncate"><i class="bi bi-geo-alt me-1"></i>{{ $room->property->address ?? 'Victoria, Oriental Mindoro' }}</span>
                </div>
            </div>

            <!-- Price & Unit -->
            <div class="pt-2 border-top mt-auto d-flex align-items-baseline justify-content-between">
                <div>
                    @if($pricingModel === 'per_bed')
                        <span class="fs-5 fw-bold text-dark">₱{{ number_format($pricePerBed, 0) }}</span>
                        <span class="text-muted small fw-normal"> / month per bed</span>
                    @elseif($pricingModel === 'per_room')
                        <span class="fs-5 fw-bold text-dark">₱{{ number_format($pricePerRoom, 0) }}</span>
                        <span class="text-muted small fw-normal"> / month whole room</span>
                    @else
                        <span class="fs-5 fw-bold text-dark">₱{{ number_format($pricePerBed, 0) }}</span>
                        <span class="text-muted small fw-normal"> / bed <span class="text-muted opacity-50">&bull;</span> ₱{{ number_format($pricePerRoom, 0) }} / room</span>
                    @endif
                </div>
            </div>
        </div>
    </a>
</article>
