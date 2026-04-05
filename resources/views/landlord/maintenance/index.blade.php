@extends('layouts.landlord')

@section('content')
<div class="maintenance-shell">
    @php
        $maintenanceCount = $maintenanceRooms->count();
        $totalRooms = $allRooms->count();
        $availableCount = $allRooms->where('status', 'available')->count();
        $occupiedCount = $allRooms->where('status', 'occupied')->count();
    @endphp

    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <div class="text-uppercase small text-muted fw-semibold">Operations</div>
            <h1 class="h3 mb-1">Maintenance Management</h1>
            <div class="text-muted small">Track rooms under maintenance and restore room availability once work is complete.</div>
        </div>
        <a href="{{ route('landlord.rooms.index') }}" class="btn btn-outline-secondary rounded-pill px-3">View Rooms</a>
    </div>

    <div class="maintenance-summary mb-4">
        <div class="maintenance-summary-item">
            <div class="maintenance-summary-label">Under Maintenance</div>
            <div class="maintenance-summary-value text-warning-emphasis">{{ $maintenanceCount }}</div>
        </div>
        <div class="maintenance-summary-item">
            <div class="maintenance-summary-label">Available</div>
            <div class="maintenance-summary-value text-success-emphasis">{{ $availableCount }}</div>
        </div>
        <div class="maintenance-summary-item">
            <div class="maintenance-summary-label">Occupied</div>
            <div class="maintenance-summary-value text-primary-emphasis">{{ $occupiedCount }}</div>
        </div>
        <div class="maintenance-summary-item">
            <div class="maintenance-summary-label">Total Rooms</div>
            <div class="maintenance-summary-value">{{ $totalRooms }}</div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success rounded-4">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger rounded-4">{{ session('error') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger rounded-4">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="maintenance-list-card mb-4">
        <div class="list-head">Rooms Under Maintenance</div>

        @if($maintenanceRooms->isNotEmpty())
            @foreach($maintenanceRooms as $room)
                <article class="maintenance-item">
                    <div class="maintenance-main">
                        <div class="maintenance-title-row">
                            <div class="maintenance-title">{{ $room->property->name }}</div>
                            <span class="updated-chip"><i class="bi bi-clock-history"></i>{{ $room->maintenance_date ? $room->maintenance_date->diffForHumans() : 'N/A' }}</span>
                        </div>

                        <div class="maintenance-meta-row">
                            <span class="meta-chip"><i class="bi bi-door-open"></i>Room {{ $room->room_number }}</span>
                            <span class="meta-chip"><i class="bi bi-tools"></i>Since {{ $room->maintenance_date ? $room->maintenance_date->format('M d, Y') : 'N/A' }}</span>
                        </div>

                        <div class="reason-box">
                            <div class="reason-label">Maintenance Reason</div>
                            <div class="reason-text">{{ $room->maintenance_reason ?: 'No reason specified' }}</div>
                        </div>
                    </div>

                    <div class="maintenance-side">
                        <div class="status-panel">
                            <span class="status-pill status-pending"><i class="bi bi-tools"></i>Under Maintenance</span>
                            <div class="status-note">Room is temporarily unavailable</div>
                        </div>

                        <div class="maintenance-actions">
                            <form action="{{ route('landlord.maintenance.complete', $room->id) }}" method="POST" class="action-form">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-success rounded-pill px-3" onclick="return confirm('Mark this room as maintenance complete?')">
                                    <i class="bi bi-check2 me-1"></i>Complete
                                </button>
                            </form>
                        </div>
                    </div>
                </article>
            @endforeach
        @else
            <div class="empty-state">
                <i class="bi bi-house-check fs-1 mb-2"></i>
                <div class="empty-title">No Rooms Under Maintenance</div>
                <div class="empty-copy">All rooms are currently active and available for regular operation.</div>
            </div>
        @endif
    </div>

    <div class="maintenance-form-card">
        <div class="list-head">Set Room to Maintenance</div>
        <div class="p-3 p-lg-4">
            <form method="POST" action="{{ route('landlord.maintenance.set') }}" class="row g-3">
                @csrf
                <div class="col-12 col-lg-4">
                    <label class="form-label">Select Room</label>
                    <select name="room_id" class="form-select" required>
                        <option value="">Choose room...</option>
                        @foreach($allRooms as $room)
                            <option value="{{ $room->id }}" @selected((string) old('room_id') === (string) $room->id)>
                                {{ $room->property->name }} - Room {{ $room->room_number }} ({{ ucfirst($room->status) }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-lg-6">
                    <label class="form-label">Reason (Optional)</label>
                    <input
                        type="text"
                        name="reason"
                        class="form-control"
                        value="{{ old('reason') }}"
                        placeholder="e.g., Plumbing issue, Electrical repair"
                    >
                </div>

                <div class="col-12 col-lg-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-warning w-100 rounded-pill">
                        <i class="bi bi-wrench-adjustable me-1"></i>Set
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .maintenance-shell {
        background: linear-gradient(180deg, #ffffff 0%, #fbfdfc 100%);
        border: 1px solid rgba(2,8,20,.08);
        border-radius: 1.25rem;
        box-shadow: 0 10px 26px rgba(2,8,20,.06);
        padding: 1.25rem;
    }
    .maintenance-summary {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: .75rem;
    }
    .maintenance-summary-item {
        border: 1px solid rgba(20,83,45,.16);
        background: linear-gradient(180deg, rgba(167,243,208,.18), rgba(255,255,255,.85));
        border-radius: .9rem;
        padding: .7rem .8rem;
    }
    .maintenance-summary-label {
        font-size: .72rem;
        text-transform: uppercase;
        letter-spacing: .05em;
        color: rgba(2,8,20,.55);
        font-weight: 700;
        margin-bottom: .2rem;
    }
    .maintenance-summary-value {
        font-size: 1rem;
        font-weight: 700;
        color: #0f172a;
    }
    .maintenance-list-card,
    .maintenance-form-card {
        border: 1px solid rgba(2,8,20,.09);
        border-radius: 1rem;
        background: #fff;
        overflow: hidden;
    }
    .list-head {
        border-bottom: 1px solid rgba(2,8,20,.08);
        padding: .85rem 1rem;
        font-size: .86rem;
        font-weight: 800;
        letter-spacing: .04em;
        text-transform: uppercase;
        color: rgba(2,8,20,.62);
        background: rgba(248,250,252,.78);
    }
    .maintenance-item {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        gap: .9rem;
        align-items: start;
        padding: 1rem;
        border-bottom: 1px solid rgba(2,8,20,.08);
    }
    .maintenance-item:last-child {
        border-bottom: none;
    }
    .maintenance-title-row {
        display: flex;
        justify-content: space-between;
        gap: .6rem;
        align-items: start;
        margin-bottom: .45rem;
    }
    .maintenance-title {
        font-weight: 700;
        color: #14532d;
        line-height: 1.2;
    }
    .updated-chip {
        display: inline-flex;
        align-items: center;
        gap: .3rem;
        border: 1px solid rgba(2,8,20,.12);
        border-radius: 999px;
        background: #f8fafc;
        color: #334155;
        padding: .16rem .5rem;
        font-size: .72rem;
        font-weight: 600;
        white-space: nowrap;
    }
    .maintenance-meta-row {
        display: flex;
        flex-wrap: wrap;
        gap: .45rem;
        align-items: center;
        margin-bottom: .6rem;
    }
    .meta-chip {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        border: 1px solid rgba(2,8,20,.12);
        border-radius: 999px;
        background: #f8fafc;
        color: #0f172a;
        padding: .18rem .55rem;
        font-size: .78rem;
        font-weight: 600;
    }
    .reason-box {
        border: 1px solid rgba(2,8,20,.08);
        border-radius: .75rem;
        padding: .6rem .7rem;
        background: #fcfefe;
    }
    .reason-label {
        font-size: .69rem;
        text-transform: uppercase;
        letter-spacing: .05em;
        color: #64748b;
        font-weight: 700;
        margin-bottom: .15rem;
    }
    .reason-text {
        font-size: .84rem;
        color: #0f172a;
    }
    .maintenance-side {
        display: grid;
        gap: .45rem;
        min-width: 230px;
        justify-items: end;
    }
    .status-panel {
        text-align: right;
    }
    .status-pill {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        border-radius: 999px;
        padding: .22rem .62rem;
        font-size: .76rem;
        font-weight: 700;
        border: 1px solid transparent;
    }
    .status-note {
        margin-top: .24rem;
        font-size: .74rem;
        color: rgba(2,8,20,.56);
    }
    .status-pending {
        color: #7c2d12;
        background: #ffedd5;
        border-color: #fdba74;
    }
    .maintenance-actions {
        border: 1px dashed rgba(100,116,139,.35);
        border-radius: .7rem;
        padding: .35rem .5rem;
        background: rgba(248,250,252,.9);
    }
    .empty-state {
        text-align: center;
        color: #64748b;
        padding: 2.4rem 1rem;
    }
    .empty-state i {
        color: rgba(2,8,20,.2);
    }
    .empty-title {
        color: #0f172a;
        font-weight: 700;
        margin-bottom: .35rem;
    }
    .empty-copy {
        max-width: 520px;
        margin: 0 auto;
        font-size: .9rem;
    }

    @media (max-width: 991.98px) {
        .maintenance-shell {
            padding: .95rem;
        }
        .maintenance-summary {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
        .maintenance-item {
            grid-template-columns: 1fr;
        }
        .maintenance-side {
            justify-items: start;
            min-width: 0;
        }
        .status-panel {
            text-align: left;
        }
    }

    @media (max-width: 575.98px) {
        .maintenance-summary {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush