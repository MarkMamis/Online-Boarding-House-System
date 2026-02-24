@extends('layouts.landlord')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h4 mb-1">Manage Rooms</h1>
        <div class="text-muted small">Update pricing, capacity & availability across all your properties</div>
    </div>
    <a href="{{ route('landlord.properties.index') }}" class="btn btn-outline-brand me-2">Manage Properties</a>
    <a href="{{ route('landlord.properties.index') }}" class="btn btn-brand me-2">Add Room</a>
    <a href="{{ route('landlord.properties.create') }}" class="btn btn-brand">Add Property</a>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Photo</th>
                        <th>Room #</th>
                        <th>Property</th>
                        <th>Capacity</th>
                        <th>Price</th>
                        <th>Includes</th>
                        <th>Current Tenant</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rooms as $room)
                        <tr>
                            <td style="width: 156px;">
                                @if(!empty($room->image_path))
                                    <img src="{{ asset('storage/'.$room->image_path) }}" alt="Room photo" class="img-thumbnail" style="width: 140px; height: 140px; object-fit: cover;">
                                @else
                                    <span class="text-muted small">—</span>
                                @endif
                            </td>
                            <td class="fw-semibold">{{ $room->room_number }}</td>
                            <td>
                                <div class="fw-medium">{{ $room->property->name }}</div>
                                <div class="text-muted small">{{ $room->property->address }}</div>
                            </td>
                            <td>{{ $room->capacity }}</td>
                            <td>₱ {{ number_format($room->price, 2) }}</td>
                            <td class="text-muted small">{{ $room->inclusions ? Str::limit($room->inclusions, 40) : '—' }}</td>
                            <td>
                                @if($room->current_tenant)
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-sm rounded-circle me-2 bg-primary text-white d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                            <i class="fas fa-user fa-xs"></i>
                                        </div>
                                        <div>
                                            <div class="fw-medium">{{ $room->current_tenant->full_name }}</div>
                                            <div class="text-muted small">{{ $room->current_tenant->student_id }}</div>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-muted small">No tenant</span>
                                @endif
                            </td>
                            <td>
                                @if($room->status==='available')
                                    <span class="badge text-bg-success">Available</span>
                                @elseif($room->status==='occupied')
                                    <span class="badge text-bg-secondary">Occupied</span>
                                @else
                                    <span class="badge text-bg-warning">Maintenance</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('landlord.properties.rooms.edit', [$room->property_id, $room->id]) }}" class="btn btn-outline-brand">Edit</a>
                                    <a href="{{ route('landlord.properties.show', $room->property_id) }}" class="btn btn-outline-secondary">View Property</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">
                                <i class="fas fa-home fa-3x mb-3 text-muted"></i>
                                <p class="mb-2">No rooms found.</p>
                                <a href="{{ route('landlord.properties.create') }}" class="btn btn-brand btn-sm">Create Your First Property</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@if($rooms->count() > 0)
<div class="row mt-4">
    <div class="col-md-4">
        <div class="card bg-success text-white">
            <div class="card-body text-center">
                <h5 class="card-title">{{ $rooms->where('status', 'available')->count() }}</h5>
                <p class="card-text mb-0">Available Rooms</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-secondary text-white">
            <div class="card-body text-center">
                <h5 class="card-title">{{ $rooms->where('status', 'occupied')->count() }}</h5>
                <p class="card-text mb-0">Occupied Rooms</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-warning text-white">
            <div class="card-body text-center">
                <h5 class="card-title">{{ $rooms->where('status', 'maintenance')->count() }}</h5>
                <p class="card-text mb-0">Under Maintenance</p>
            </div>
        </div>
    </div>
</div>
@endif
@endsection