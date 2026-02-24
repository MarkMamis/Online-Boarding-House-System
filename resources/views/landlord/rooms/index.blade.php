@extends('layouts.landlord')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h4 mb-1">Rooms — {{ $property->name }}</h1>
        <div class="text-muted small">{{ $property->address }}</div>
    </div>
    <a href="{{ route('landlord.properties.rooms.create', $property->id) }}" class="btn btn-brand">Add Room</a>
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
                        <th>Capacity</th>
                        <th>Price</th>
                        <th>Includes</th>
                        <th>Current Tenant</th>
                        <th>Status</th>
                        <th>Created</th>
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
                            <td class="text-muted small">{{ $room->created_at->diffForHumans() }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-muted py-4">No rooms yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
