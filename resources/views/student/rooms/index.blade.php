@extends('layouts.student_dashboard')

@section('title', 'Browse Rooms')

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin=""/>
    <style>
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
    </style>
@endpush

@section('content')
<div class="glass-card rounded-4 p-4 p-md-5 mb-4">

    {{-- Header + Search/Filter --}}
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-0">Browse Rooms</h4>
            <div class="text-muted small mt-1">Find and explore your perfect boarding house</div>
        </div>
        <form method="GET" action="{{ route('student.rooms.index') }}" class="d-flex flex-wrap gap-2 align-items-center">
            <input type="text" name="q" class="form-control form-control-sm rounded-pill" style="min-width:170px;" placeholder="Search rooms or properties..." value="{{ request('q') }}">
            <input type="number" step="0.01" min="0" name="min_price" value="{{ old('min_price', $minPrice) }}" class="form-control form-control-sm rounded-pill" style="width:100px;" placeholder="Min ₱">
            <input type="number" step="0.01" min="0" name="max_price" value="{{ old('max_price', $maxPrice) }}" class="form-control form-control-sm rounded-pill" style="width:100px;" placeholder="Max ₱">
            <input type="number" min="1" name="capacity" value="{{ old('capacity', $minCapacity) }}" class="form-control form-control-sm rounded-pill" style="width:90px;" placeholder="Pax">
            <button class="btn btn-sm btn-brand rounded-pill px-3" type="submit"><i class="bi bi-search me-1"></i>Filter</button>
            <a href="{{ route('student.rooms.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill">Reset</a>
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
                                    <span class="badge text-bg-primary flex-shrink-0" style="font-size:.62rem;">New</span>
                                @endif
                            </div>
                            <div class="text-muted mt-1" style="font-size:.76rem;">
                                <i class="bi bi-geo-alt-fill text-danger me-1"></i>{{ Str::limit($room->property->address, 38) }}
                            </div>
                            <div class="mt-2" style="font-size:.75rem;">
                                @if(($room->feedbacks_count ?? 0) > 0)
                                    @php $avg = (float) $room->feedbacks_avg_rating; @endphp
                                    <span class="me-1" style="color:#f59e0b;">
                                        @for($s=1;$s<=5;$s++)
                                            <i class="bi {{ $s <= round($avg) ? 'bi-star-fill' : 'bi-star' }}"></i>
                                        @endfor
                                    </span>
                                    <span class="text-muted">{{ number_format($avg, 1) }}</span>
                                    <span class="text-muted">({{ $room->feedbacks_count }})</span>
                                @else
                                    <span class="text-muted">No ratings yet</span>
                                @endif
                            </div>
                            <div class="d-flex justify-content-between align-items-center mt-2">
                                <div>
                                    <span class="fw-bold text-dark" style="font-size:.88rem;">Room {{ $room->room_number }}</span>
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
                            <div class="mt-2" style="font-size:.75rem;">
                                @if(($r->feedbacks_count ?? 0) > 0)
                                    @php $avg = (float) $r->feedbacks_avg_rating; @endphp
                                    <span class="me-1" style="color:#f59e0b;">
                                        @for($s=1;$s<=5;$s++)
                                            <i class="bi {{ $s <= round($avg) ? 'bi-star-fill' : 'bi-star' }}"></i>
                                        @endfor
                                    </span>
                                    <span class="text-muted">{{ number_format($avg, 1) }}</span>
                                    <span class="text-muted">({{ $r->feedbacks_count }})</span>
                                @else
                                    <span class="text-muted">No ratings yet</span>
                                @endif
                            </div>
                            <div class="d-flex justify-content-between align-items-center mt-2">
                                <div>
                                    <span class="fw-bold text-dark" style="font-size:.88rem;">Room {{ $r->room_number }}</span>
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

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const filterNoticeEl = document.getElementById('propertyRoomFilterNotice');
        const filterNameEl = document.getElementById('propertyRoomFilterName');
        const clearFilterBtn = document.getElementById('clearPropertyRoomFilter');
        
        const applyPropertyRoomFilter = (propertyId, propertyName) => {
            const filterId = propertyId ? String(propertyId) : '';
            const hasFilter = !!filterId;

            if (filterNoticeEl && filterNameEl) {
                filterNameEl.textContent = propertyName || '';
                filterNoticeEl.classList.toggle('d-none', !hasFilter);
            }

            document.querySelectorAll('[data-room-property-id]').forEach(el => {
                const isMatch = !hasFilter || String(el.getAttribute('data-room-property-id')) === filterId;
                el.style.display = isMatch ? '' : 'none';
            });
        };

        if (clearFilterBtn) {
            clearFilterBtn.addEventListener('click', () => applyPropertyRoomFilter('', ''));
        }

        const params = new URLSearchParams(window.location.search || '');
        const initialPropertyId = params.get('property_id');
        const initialPropertyName = params.get('property_name');
        if (initialPropertyId) {
            applyPropertyRoomFilter(initialPropertyId, initialPropertyName || '');
        }

        // Distance calculation for rooms with location
        @foreach($recommendedRooms->concat($allRooms) as $room)
            @if($room->property->latitude && $room->property->longitude)
                if(navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(pos => {
                        const { latitude, longitude } = pos.coords;
                        const userLatLng = [latitude, longitude];
                        const propertyLatLng = [{{ $room->property->latitude }}, {{ $room->property->longitude }}];
                        
                        const R = 6371;
                        const dLat = (propertyLatLng[0] - userLatLng[0]) * Math.PI / 180;
                        const dLon = (propertyLatLng[1] - userLatLng[1]) * Math.PI / 180;
                        const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
                                 Math.cos(userLatLng[0] * Math.PI / 180) * Math.cos(propertyLatLng[0] * Math.PI / 180) *
                                 Math.sin(dLon/2) * Math.sin(dLon/2);
                        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
                        const distance = R * c;
                        
                        const distanceBadge = document.getElementById('distance-room-{{ $room->id }}');
                        if(distanceBadge) {
                            distanceBadge.innerHTML = '<i class="bi bi-signpost-2 text-primary me-1"></i>' + distance.toFixed(1) + ' km away';
                        }
                    }, err => {
                        const distanceBadge = document.getElementById('distance-room-{{ $room->id }}');
                        if(distanceBadge) {
                            distanceBadge.innerHTML = '<i class="bi bi-geo-alt text-muted me-1"></i>Location available';
                        }
                    });
                } else {
                    const distanceBadge = document.getElementById('distance-room-{{ $room->id }}');
                    if(distanceBadge) {
                        distanceBadge.innerHTML = '<i class="bi bi-geo-alt text-muted me-1"></i>Location available';
                    }
                }
            @endif
        @endforeach
    });
</script>
@endpush

@endsection
