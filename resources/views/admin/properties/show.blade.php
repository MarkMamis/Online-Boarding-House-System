@extends('layouts.admin')

@section('title', 'Property Details - ' . $property->name)

@section('content')
<style>
    .admin-property-shell {
        background: linear-gradient(180deg, #ffffff 0%, #fbfdfc 100%);
        border: 1px solid rgba(2, 8, 20, .08);
        border-radius: 1.25rem;
        box-shadow: 0 10px 26px rgba(2, 8, 20, .06);
        padding: 1.25rem;
    }
    .section-muted { color: rgba(2, 8, 20, .58); }
    .metric-tile {
        border: 1px solid rgba(2, 8, 20, .08);
        border-radius: 1rem;
        background: #fff;
        box-shadow: 0 8px 18px rgba(2, 8, 20, .05);
        padding: .95rem 1rem;
        height: 100%;
    }
    .metric-label {
        font-size: .78rem;
        text-transform: uppercase;
        letter-spacing: .05em;
        color: rgba(2, 8, 20, .55);
    }
    .metric-value {
        font-size: 1.35rem;
        font-weight: 700;
        color: #166534;
    }
    .section-card {
        border: 1px solid rgba(2, 8, 20, .08);
        border-radius: 1rem;
        background: #fff;
        box-shadow: 0 8px 20px rgba(2, 8, 20, .05);
        overflow: hidden;
    }
    .section-header {
        border-bottom: 1px solid rgba(2, 8, 20, .08);
        background: #fff;
        padding: .85rem 1rem;
    }
    .property-cover {
        width: 100%;
        max-height: 280px;
        object-fit: cover;
        border-radius: .85rem;
        border: 1px solid rgba(2, 8, 20, .08);
        transition: transform .2s ease, box-shadow .2s ease;
    }
    .property-cover-trigger {
        display: block;
        border: 0;
        padding: 0;
        width: 100%;
        background: transparent;
        text-align: left;
        border-radius: .85rem;
        overflow: hidden;
        cursor: zoom-in;
    }
    .property-cover-trigger:hover .property-cover,
    .property-cover-trigger:focus-visible .property-cover {
        transform: scale(1.01);
        box-shadow: 0 12px 26px rgba(2, 8, 20, .14);
    }
    .property-cover-hint {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        font-size: .78rem;
        color: rgba(2, 8, 20, .62);
        margin-top: .4rem;
    }
    .property-cover-placeholder {
        height: 220px;
        border: 1px dashed rgba(2, 8, 20, .16);
        border-radius: .85rem;
        background: rgba(248, 250, 252, .9);
        display: flex;
        align-items: center;
        justify-content: center;
        color: rgba(2, 8, 20, .5);
    }
    .info-chip {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        border: 1px solid rgba(2, 8, 20, .12);
        border-radius: 999px;
        background: rgba(248, 250, 252, .9);
        padding: .35rem .65rem;
        font-size: .82rem;
    }
    .service-chip {
        border: 1px solid rgba(22, 101, 52, .2);
        border-radius: 999px;
        background: rgba(22, 101, 52, .08);
        color: #14532d;
        display: inline-flex;
        align-items: center;
        font-size: .78rem;
        padding: .28rem .6rem;
    }
    .room-thumb {
        width: 52px;
        height: 52px;
        border-radius: .6rem;
        border: 1px solid rgba(2, 8, 20, .1);
        object-fit: cover;
        background: #f8fafc;
    }
    .room-view-btn {
        border-radius: 999px;
        font-size: .78rem;
        font-weight: 600;
    }
    .room-gallery-thumb {
        max-width: 100%;
        max-height: 100%;
        width: auto;
        height: auto;
        object-fit: contain;
        border-radius: .75rem;
        border: 1px solid rgba(255, 255, 255, .16);
    }
    .room-gallery-inner {
        border-radius: .75rem;
        border: 1px solid rgba(2, 8, 20, .12);
        background: #0f172a;
    }
    .room-gallery-link {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        height: 320px;
        padding: .65rem;
        background: #0f172a;
        text-decoration: none;
        cursor: zoom-in;
    }
    .room-gallery-hint {
        font-size: .75rem;
        color: rgba(2, 8, 20, .58);
        margin-top: .45rem;
    }
    .room-detail-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: .65rem;
        margin-bottom: 1rem;
    }
    .room-detail-card {
        border: 1px solid rgba(2, 8, 20, .1);
        border-radius: .75rem;
        background: #f8fafc;
        padding: .6rem .7rem;
    }
    .room-detail-label {
        font-size: .72rem;
        text-transform: uppercase;
        letter-spacing: .05em;
        color: rgba(2, 8, 20, .58);
    }
    .room-detail-value {
        font-size: .95rem;
        font-weight: 600;
        color: #0f172a;
    }
    .modal-image-full {
        max-height: 78vh;
        width: 100%;
        object-fit: contain;
        background: #0f172a;
        border-radius: .5rem;
    }
    .table thead th {
        font-size: .78rem;
        text-transform: uppercase;
        letter-spacing: .05em;
        color: rgba(2, 8, 20, .62);
        background: rgba(248, 250, 252, .96);
    }
    .map-wrap {
        height: 300px;
    }
    @media (max-width: 767.98px) {
        .admin-property-shell { padding: .95rem; }
        .map-wrap { height: 240px; }
    }
</style>

@php
    $permitStatus = optional(optional($property->landlord)->landlordProfile)->business_permit_status ?? 'not_submitted';
    $permitBadgeClass = $permitStatus === 'approved'
        ? 'text-bg-success'
        : ($permitStatus === 'rejected' ? 'text-bg-danger' : ($permitStatus === 'pending' ? 'text-bg-warning' : 'text-bg-secondary'));

    $approvalStatus = $property->approval_status ?? 'pending';
    $approvalBadgeClass = $approvalStatus === 'approved'
        ? 'text-bg-success'
        : ($approvalStatus === 'rejected' ? 'text-bg-danger' : 'text-bg-warning');
@endphp

<div class="admin-property-shell">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <div class="text-uppercase small section-muted fw-semibold">Management</div>
            <h1 class="h4 mb-1">{{ $property->name }}</h1>
            <div class="section-muted small">{{ $property->address }}</div>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('admin.properties.pending') }}" class="btn btn-outline-secondary rounded-pill px-3">Pending Queue</a>
            <a href="{{ route('admin.properties.index') }}" class="btn btn-outline-secondary rounded-pill px-3">All Properties</a>
            <a href="#roomsSection" class="btn btn-outline-success rounded-pill px-3">View Rooms</a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="metric-tile">
                <div class="metric-label">Approval</div>
                <div class="metric-value"><span class="badge {{ $approvalBadgeClass }}">{{ str_replace('_', ' ', $approvalStatus) }}</span></div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="metric-tile">
                <div class="metric-label">Rooms</div>
                <div class="metric-value">{{ $property->occupied_rooms }}/{{ $property->total_rooms }}</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="metric-tile">
                <div class="metric-label">Occupancy</div>
                <div class="metric-value">{{ $occupancyRate }}%</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="metric-tile">
                <div class="metric-label">Price Range</div>
                <div class="metric-value">
                    @if(!is_null($minPrice) && !is_null($maxPrice))
                        PHP {{ number_format($minPrice, 0) }} - {{ number_format($maxPrice, 0) }}
                    @else
                        Not set
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-lg-8">
            <div class="section-card h-100">
                <div class="section-header fw-semibold"><i class="bi bi-building me-1"></i> Property Overview</div>
                <div class="p-3">
                    @if(!empty($property->image_path))
                        <button
                            type="button"
                            class="property-cover-trigger mb-1"
                            data-bs-toggle="modal"
                            data-bs-target="#propertyImageModal"
                            aria-label="View property image full screen"
                        >
                            <img src="{{ asset('storage/' . $property->image_path) }}" alt="{{ $property->name }}" class="property-cover">
                        </button>
                        <div class="property-cover-hint"><i class="bi bi-arrows-fullscreen"></i>Click image to view full screen</div>
                    @else
                        <div class="property-cover-placeholder mb-3">
                            <span><i class="bi bi-image me-1"></i>No property image uploaded</span>
                        </div>
                    @endif

                    <div class="d-flex flex-wrap gap-2 mb-3">
                        <span class="info-chip"><i class="bi bi-geo-alt"></i>{{ $property->address }}</span>
                        @if(!empty($property->latitude) && !empty($property->longitude))
                            <span class="info-chip"><i class="bi bi-crosshair"></i>{{ number_format($property->latitude, 6) }}, {{ number_format($property->longitude, 6) }}</span>
                        @endif
                    </div>

                    <div>
                        <div class="fw-semibold mb-1">Description</div>
                        <p class="mb-0 section-muted">{{ $property->description ?: 'No description provided by landlord.' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="section-card h-100">
                <div class="section-header fw-semibold"><i class="bi bi-person-vcard me-1"></i> Landlord & Compliance</div>
                <div class="p-3">
                    <div class="mb-2 fw-semibold">{{ optional($property->landlord)->full_name }}</div>
                    <div class="small section-muted mb-2">{{ optional($property->landlord)->email }}</div>
                    <div class="small mb-2"><strong>Contact:</strong> {{ optional($property->landlord)->contact_number ?: 'Not provided' }}</div>
                    <div class="small mb-3"><strong>Boarding House:</strong> {{ optional($property->landlord)->boarding_house_name ?: 'Not provided' }}</div>

                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="small fw-semibold">Permit Status</span>
                        <span class="badge {{ $permitBadgeClass }}">{{ str_replace('_', ' ', $permitStatus) }}</span>
                    </div>

                    <a href="{{ route('admin.users.landlords.show', $property->landlord_id) }}" class="btn btn-sm btn-outline-secondary rounded-pill w-100">
                        <i class="bi bi-eye me-1"></i>View Landlord Details
                    </a>
                </div>
            </div>
        </div>
    </div>

    @if(!empty($property->latitude) && !empty($property->longitude))
        <div class="section-card mb-4">
            <div class="section-header fw-semibold"><i class="bi bi-map me-1"></i> Property Location</div>
            <div id="propertyMap" class="map-wrap"></div>
        </div>
    @endif

    <div class="section-card mb-4">
        <div class="section-header fw-semibold"><i class="bi bi-stars me-1"></i> Services Offered</div>
        <div class="p-3">
            @if($servicesOffered->isNotEmpty())
                <div class="d-flex flex-wrap gap-2">
                    @foreach($servicesOffered as $service)
                        <span class="service-chip">{{ $service }}</span>
                    @endforeach
                </div>
            @else
                <div class="section-muted">No room services/inclusions listed yet.</div>
            @endif
        </div>
    </div>

    <div class="section-card" id="roomsSection">
        <div class="section-header d-flex justify-content-between align-items-center gap-2">
            <div class="fw-semibold"><i class="bi bi-door-open me-1"></i> Room Pricing & Services</div>
            <span class="badge text-bg-secondary">{{ $property->rooms->count() }} rooms</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th class="ps-3">Room</th>
                        <th>Status</th>
                        <th>Capacity</th>
                        <th>Price</th>
                        <th>Services</th>
                        <th>Photo</th>
                        <th class="pe-3">View</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($property->rooms as $room)
                        @php
                            $status = $room->status ?? 'available';
                            $statusClass = $status === 'available'
                                ? 'text-bg-success'
                                : ($status === 'occupied' ? 'text-bg-secondary' : 'text-bg-warning');

                            $roomImagePath = $room->image_path ?: optional($room->roomImages->first())->image_path;
                            $roomImages = collect([]);
                            if (!empty($room->image_path)) {
                                $roomImages->push($room->image_path);
                            }
                            $roomImages = $roomImages
                                ->merge($room->roomImages->pluck('image_path'))
                                ->filter()
                                ->unique()
                                ->values();
                            $services = collect(preg_split('/[,\n;]+/', (string) $room->inclusions))
                                ->map(fn ($item) => trim($item))
                                ->filter();
                        @endphp
                        <tr>
                            <td class="ps-3">
                                <div class="fw-semibold">Room {{ $room->room_number }}</div>
                                <div class="small section-muted">Active tenants: {{ (int) ($room->active_bookings_count ?? 0) }}</div>
                            </td>
                            <td><span class="badge {{ $statusClass }}">{{ ucfirst($status) }}</span></td>
                            <td>
                                <div>{{ $room->getOccupancyDisplay() }}</div>
                                <div class="small section-muted">{{ $room->getAvailableSlots() }} slots available</div>
                            </td>
                            <td class="fw-semibold">PHP {{ number_format((float) $room->price, 2) }}</td>
                            <td>
                                @if($services->isNotEmpty())
                                    <div class="d-flex flex-wrap gap-1">
                                        @foreach($services->take(3) as $service)
                                            <span class="badge text-bg-light border">{{ $service }}</span>
                                        @endforeach
                                        @if($services->count() > 3)
                                            <span class="badge text-bg-light border">+{{ $services->count() - 3 }} more</span>
                                        @endif
                                    </div>
                                @else
                                    <span class="small section-muted">None listed</span>
                                @endif
                            </td>
                            <td class="pe-3">
                                @if(!empty($roomImagePath))
                                    <a href="{{ asset('storage/' . $roomImagePath) }}" target="_blank" rel="noopener" title="View room photo">
                                        <img src="{{ asset('storage/' . $roomImagePath) }}" alt="Room photo" class="room-thumb">
                                    </a>
                                @else
                                    <div class="small section-muted">No photo</div>
                                @endif
                            </td>
                            <td class="pe-3">
                                <button
                                    type="button"
                                    class="btn btn-sm btn-outline-success room-view-btn"
                                    data-bs-toggle="modal"
                                    data-bs-target="#roomDetailModal{{ $room->id }}"
                                >
                                    <i class="bi bi-eye me-1"></i>View room
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 section-muted">No rooms added for this property yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if(!empty($property->image_path))
        <div class="modal fade" id="propertyImageModal" tabindex="-1" aria-labelledby="propertyImageModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content border-0 shadow">
                    <div class="modal-header">
                        <h2 class="modal-title fs-6" id="propertyImageModalLabel">{{ $property->name }} - Property Image</h2>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body bg-dark">
                        <img src="{{ asset('storage/' . $property->image_path) }}" alt="{{ $property->name }} full image" class="modal-image-full">
                    </div>
                </div>
            </div>
        </div>
    @endif

    @foreach($property->rooms as $room)
        @php
            $roomImages = collect([]);
            if (!empty($room->image_path)) {
                $roomImages->push($room->image_path);
            }
            $roomImages = $roomImages
                ->merge($room->roomImages->pluck('image_path'))
                ->filter()
                ->unique()
                ->values();
            $roomServices = collect(preg_split('/[,\n;]+/', (string) $room->inclusions))
                ->map(fn ($item) => trim($item))
                ->filter();
            $roomStatus = $room->status ?? 'available';
            $roomStatusClass = $roomStatus === 'available'
                ? 'text-bg-success'
                : ($roomStatus === 'occupied' ? 'text-bg-secondary' : 'text-bg-warning');
            $activeTenants = (int) ($room->active_bookings_count ?? 0);
            $availableSlots = (int) $room->getAvailableSlots();
            $occupancyDisplay = $room->getOccupancyDisplay();
            $carouselId = 'roomCarousel' . $room->id;
        @endphp
        <div class="modal fade" id="roomDetailModal{{ $room->id }}" tabindex="-1" aria-labelledby="roomDetailModalLabel{{ $room->id }}" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content border-0 shadow">
                    <div class="modal-header">
                        <h2 class="modal-title fs-6" id="roomDetailModalLabel{{ $room->id }}">Room {{ $room->room_number }} Details</h2>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="d-flex flex-wrap gap-2 mb-3">
                            <span class="badge {{ $roomStatusClass }}">{{ ucfirst($roomStatus) }}</span>
                            <span class="badge text-bg-light border">{{ $occupancyDisplay }}</span>
                            <span class="badge text-bg-light border">PHP {{ number_format((float) $room->price, 2) }}</span>
                            <span class="badge text-bg-light border">{{ $availableSlots }} slots available</span>
                        </div>

                        <div class="room-detail-grid">
                            <div class="room-detail-card">
                                <div class="room-detail-label">Room Number</div>
                                <div class="room-detail-value">{{ $room->room_number }}</div>
                            </div>
                            <div class="room-detail-card">
                                <div class="room-detail-label">Capacity</div>
                                <div class="room-detail-value">{{ (int) $room->capacity }} pax</div>
                            </div>
                            <div class="room-detail-card">
                                <div class="room-detail-label">Occupancy</div>
                                <div class="room-detail-value">{{ $occupancyDisplay }}</div>
                            </div>
                            <div class="room-detail-card">
                                <div class="room-detail-label">Available Slots</div>
                                <div class="room-detail-value">{{ $availableSlots }}</div>
                            </div>
                            <div class="room-detail-card">
                                <div class="room-detail-label">Active Tenants</div>
                                <div class="room-detail-value">{{ $activeTenants }}</div>
                            </div>
                            <div class="room-detail-card">
                                <div class="room-detail-label">Monthly Price</div>
                                <div class="room-detail-value">PHP {{ number_format((float) $room->price, 2) }}</div>
                            </div>
                            @if($roomStatus === 'maintenance' && !empty($room->maintenance_reason))
                                <div class="room-detail-card">
                                    <div class="room-detail-label">Maintenance Reason</div>
                                    <div class="room-detail-value">{{ $room->maintenance_reason }}</div>
                                </div>
                            @endif
                            @if(!empty($room->maintenance_date))
                                <div class="room-detail-card">
                                    <div class="room-detail-label">Maintenance Date</div>
                                    <div class="room-detail-value">{{ \Illuminate\Support\Carbon::parse($room->maintenance_date)->format('M d, Y') }}</div>
                                </div>
                            @endif
                            <div class="room-detail-card">
                                <div class="room-detail-label">Last Updated</div>
                                <div class="room-detail-value">{{ \Illuminate\Support\Carbon::parse($room->updated_at)->format('M d, Y h:i A') }}</div>
                            </div>
                        </div>

                        @if($roomImages->isNotEmpty())
                            <div id="{{ $carouselId }}" class="carousel slide mb-3" data-bs-ride="false">
                                <div class="carousel-inner room-gallery-inner">
                                    @foreach($roomImages as $imagePath)
                                        <div class="carousel-item {{ $loop->first ? 'active' : '' }}">
                                            <a href="{{ asset('storage/' . $imagePath) }}" target="_blank" rel="noopener" class="room-gallery-link" title="Open full image">
                                                <img src="{{ asset('storage/' . $imagePath) }}" class="room-gallery-thumb" alt="Room {{ $room->room_number }} image {{ $loop->iteration }}">
                                            </a>
                                        </div>
                                    @endforeach
                                </div>
                                @if($roomImages->count() > 1)
                                    <button class="carousel-control-prev" type="button" data-bs-target="#{{ $carouselId }}" data-bs-slide="prev">
                                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                        <span class="visually-hidden">Previous</span>
                                    </button>
                                    <button class="carousel-control-next" type="button" data-bs-target="#{{ $carouselId }}" data-bs-slide="next">
                                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                        <span class="visually-hidden">Next</span>
                                    </button>
                                @endif
                            </div>
                            <div class="room-gallery-hint"><i class="bi bi-arrows-fullscreen me-1"></i>Click image to open full size.</div>
                        @else
                            <div class="property-cover-placeholder mb-3" style="height: 320px;">
                                <span><i class="bi bi-image me-1"></i>No room image uploaded</span>
                            </div>
                        @endif

                        <div>
                            <div class="fw-semibold mb-2">Room Services</div>
                            @if($roomServices->isNotEmpty())
                                <div class="d-flex flex-wrap gap-2">
                                    @foreach($roomServices as $service)
                                        <span class="badge text-bg-light border">{{ $service }}</span>
                                    @endforeach
                                </div>
                            @else
                                <span class="section-muted small">No services listed for this room.</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>
@endsection

@push('styles')
@if(!empty($property->latitude) && !empty($property->longitude))
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin=""/>
@endif
@endpush

@push('scripts')
@if(!empty($property->latitude) && !empty($property->longitude))
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const mapElement = document.getElementById('propertyMap');
    if (!mapElement) return;

    const lat = {{ $property->latitude }};
    const lng = {{ $property->longitude }};

    const map = L.map('propertyMap').setView([lat, lng], 16);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxNativeZoom: 19,
        maxZoom: 22,
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    const marker = L.marker([lat, lng]).addTo(map);
    marker.bindPopup('<strong>{{ addslashes($property->name) }}</strong><br>{{ addslashes($property->address) }}').openPopup();
});
</script>
@endif
@endpush
