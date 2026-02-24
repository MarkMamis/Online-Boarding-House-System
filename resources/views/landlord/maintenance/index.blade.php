@extends('layouts.landlord')

@section('content')
<div class="glass-card rounded-4 p-4 p-md-5">
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="h4 mb-0">Maintenance Management</h2>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<!-- Rooms Under Maintenance -->
<div class="card shadow-sm mb-4">
    <div class="card-header bg-warning text-dark">
        <h5 class="mb-0">Rooms Under Maintenance</h5>
    </div>
    <div class="card-body">
        @if($maintenanceRooms->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="small text-uppercase">
                        <tr>
                            <th>Property</th>
                            <th>Room</th>
                            <th>Reason</th>
                            <th>Since</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="small">
                        @foreach($maintenanceRooms as $room)
                            <tr>
                                <td>{{ $room->property->name }}</td>
                                <td>{{ $room->room_number }}</td>
                                <td>{{ $room->maintenance_reason ?: 'No reason specified' }}</td>
                                <td>{{ $room->maintenance_date ? $room->maintenance_date->diffForHumans() : 'N/A' }}</td>
                                <td class="text-end">
                                    <form action="{{ route('landlord.maintenance.complete', $room->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Mark this room as maintenance complete?')">Complete</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-muted mb-0">No rooms are currently under maintenance.</p>
        @endif
    </div>
</div>

<!-- Set Room to Maintenance -->
<div class="card shadow-sm">
    <div class="card-header">
        <h5 class="mb-0">Set Room to Maintenance</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('landlord.maintenance.set') }}" class="row g-3">
            @csrf
            <div class="col-md-4">
                <label class="form-label">Select Room</label>
                <select name="room_id" class="form-select" required>
                    <option value="">Choose room...</option>
                    @foreach($allRooms as $room)
                        <option value="{{ $room->id }}">{{ $room->property->name }} - Room {{ $room->room_number }} ({{ ucfirst($room->status) }})</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Reason (Optional)</label>
                <input type="text" name="reason" class="form-control" placeholder="e.g., Plumbing issue, Electrical repair">
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-warning w-100">Set to Maintenance</button>
            </div>
        </form>
    </div>
</div>
</div>
@endsection