@extends('layouts.landlord')

@section('title', 'Room Availability & Maintenance Mode')

@section('content')
<div class="landlord-page-shell">
    @php
        $maintenanceCount = $maintenanceRooms->count();
        $totalRooms = $allRooms->count();
        $availableCount = $allRooms->where('status', 'available')->count();
        $occupiedCount = $allRooms->where('status', 'occupied')->count();
    @endphp

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
        <div>
            <div class="landlord-header-eyebrow">Operations</div>
            <h1 class="landlord-header-title mb-1">Room Availability &amp; Maintenance Mode</h1>
            <div class="landlord-header-subtitle">Temporarily take rooms offline for cleaning or repairs to hide them from the student marketplace.</div>
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <span class="landlord-summary-strip">
                <span class="landlord-summary-dot"></span>
                <span><strong>{{ $totalRooms }}</strong> Total Rooms</span>
                <span class="text-muted">·</span>
                @if($maintenanceCount > 0)
                    <span class="text-warning-emphasis fw-bold">{{ $maintenanceCount }} Under Maintenance</span>
                @else
                    <span class="text-muted">0 Maintenance</span>
                @endif
                <span class="text-muted">·</span>
                <span class="text-success fw-bold">{{ $availableCount }}</span> Available
            </span>
        </div>
    </div>

    <!-- Collapsible Availability Guidance -->
    <div class="mb-3">
        <button class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1" type="button" data-bs-toggle="collapse" data-bs-target="#maintenancePolicyCollapse" aria-expanded="false" aria-controls="maintenancePolicyCollapse">
            <i class="bi bi-info-circle me-1"></i> How maintenance mode works
        </button>
        <div class="collapse mt-2" id="maintenancePolicyCollapse">
            <div class="card card-body bg-light border-0 rounded-4 p-3 small text-secondary">
                <strong>Room Availability Notice:</strong> Placing a room under maintenance temporarily removes its vacant bed slots from the student discovery marketplace. Once repairs are completed, clicking <strong>Complete</strong> immediately restores the room to available status. Current occupant contracts remain unaffected.
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success rounded-4 mb-3">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger rounded-4 mb-3">{{ session('error') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger rounded-4 mb-3">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Rooms Currently Under Maintenance -->
    <div class="landlord-card mb-4">
        <div class="card-header bg-white border-bottom p-3 p-md-4 d-flex justify-content-between align-items-center">
            <h2 class="h5 mb-0 fw-bold text-dark">Rooms Currently Under Maintenance</h2>
            @if($maintenanceCount > 0)
                <span class="badge rounded-pill bg-warning-subtle text-warning-emphasis border border-warning-subtle">
                    {{ $maintenanceCount }} {{ Str::plural('Room', $maintenanceCount) }}
                </span>
            @endif
        </div>

        @if($maintenanceRooms->isNotEmpty())
            @foreach($maintenanceRooms as $room)
                <article class="maintenance-item">
                    <div class="maintenance-main">
                        <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                            <span class="fw-bold text-dark fs-6">{{ $room->property->name }}</span>
                            <span class="text-muted">·</span>
                            <span class="fw-semibold text-dark">Room {{ $room->room_number }}</span>
                            <span class="badge rounded-pill bg-warning-subtle text-warning-emphasis border border-warning-subtle small ms-auto d-md-none">
                                Maintenance
                            </span>
                        </div>

                        <div class="maintenance-meta-row mb-2">
                            <span class="meta-chip">
                                <i class="bi bi-tools"></i>
                                Offline since {{ $room->maintenance_date ? $room->maintenance_date->format('M d, Y') : 'N/A' }}
                            </span>
                            @if($room->maintenance_date)
                                <span class="text-muted small ms-1">({{ $room->maintenance_date->diffForHumans() }})</span>
                            @endif
                        </div>

                        <div class="reason-box mb-1">
                            <div class="reason-label">Maintenance Reason</div>
                            <div class="reason-text">{{ $room->maintenance_reason ?: 'No specific reason provided.' }}</div>
                        </div>
                    </div>

                    <div class="maintenance-side">
                        <div class="status-panel d-none d-md-block">
                            <span class="badge rounded-pill bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-1">
                                <i class="bi bi-wrench me-1"></i>Under Maintenance
                            </span>
                            <div class="status-note">Room is hidden from student discovery</div>
                        </div>

                        <div class="maintenance-actions">
                            <form action="{{ route('landlord.maintenance.complete', $room->id) }}" method="POST" class="action-form">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-success rounded-pill px-3" onclick="return confirm('Mark maintenance as complete and restore room to available status?')">
                                    <i class="bi bi-check2 me-1"></i>Complete Maintenance
                                </button>
                            </form>
                        </div>
                    </div>
                </article>
            @endforeach
        @else
            <div class="landlord-empty-state border-0">
                <div class="landlord-empty-icon">
                    <i class="bi bi-house-check"></i>
                </div>
                <div class="landlord-empty-title">No Rooms Under Maintenance</div>
                <div class="landlord-empty-text">All rooms across your properties are active and in service. Use the form below if a room requires repairs or cleaning.</div>
            </div>
        @endif
    </div>

    <!-- Quick Form: Set Room to Maintenance -->
    <div class="landlord-card">
        <div class="card-header bg-white border-bottom p-3 p-md-4">
            <h2 class="h5 mb-0 fw-bold text-dark">Place a Room into Maintenance Mode</h2>
            <div class="text-muted small mt-1">Select a room to temporarily withdraw it from the public boarding marketplace.</div>
        </div>
        <div class="card-body p-3 p-md-4">
            <form method="POST" action="{{ route('landlord.maintenance.set') }}" class="row g-3 align-items-end">
                @csrf
                <div class="col-12 col-md-5">
                    <label class="form-label small fw-semibold text-muted">Select Room</label>
                    <select name="room_id" class="form-select rounded-3" required style="height: 40px; font-size: .88rem;">
                        <option value="">Choose room to take offline...</option>
                        @foreach($allRooms as $room)
                            <option value="{{ $room->id }}" @selected((string) old('room_id') === (string) $room->id)>
                                {{ $room->property->name }} — Room {{ $room->room_number }} ({{ ucfirst($room->status) }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-md-5">
                    <label class="form-label small fw-semibold text-muted">Reason (Optional)</label>
                    <input
                        type="text"
                        name="reason"
                        class="form-control rounded-3"
                        value="{{ old('reason') }}"
                        placeholder="e.g., Plumbing repair, repaint, cleaning..."
                        style="height: 40px; font-size: .88rem;"
                    >
                </div>

                <div class="col-12 col-md-2">
                    <button type="submit" class="btn btn-warning w-100 rounded-pill px-3" style="height: 40px; font-size: .88rem;">
                        <i class="bi bi-wrench me-1"></i>Set Mode
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .maintenance-item {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        gap: 1rem;
        align-items: start;
        padding: 1.1rem 1.25rem;
        border-bottom: 1px solid rgba(2,8,20,.08);
        transition: background .2s ease;
    }
    .maintenance-item:hover {
        background: #fbfcfd;
    }
    .maintenance-item:last-child {
        border-bottom: none;
    }
    .maintenance-meta-row {
        display: flex;
        flex-wrap: wrap;
        gap: .45rem;
        align-items: center;
    }
    .meta-chip {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        border: 1px solid rgba(2,8,20,.1);
        border-radius: 999px;
        background: #f8fafc;
        color: #334155;
        padding: .15rem .55rem;
        font-size: .76rem;
        font-weight: 600;
    }
    .reason-box {
        background: #f8fafc;
        border: 1px solid rgba(2,8,20,.06);
        border-radius: .75rem;
        padding: .65rem .85rem;
        max-width: 580px;
    }
    .reason-label {
        font-size: .68rem;
        letter-spacing: .04em;
        text-transform: uppercase;
        color: #64748b;
        font-weight: 700;
        margin-bottom: .2rem;
    }
    .reason-text {
        font-size: .83rem;
        color: #1e293b;
    }
    .maintenance-side {
        display: grid;
        gap: .6rem;
        min-width: 200px;
        justify-items: end;
    }
    .status-panel {
        text-align: right;
    }
    .status-note {
        margin-top: .25rem;
        font-size: .73rem;
        color: #64748b;
    }
    .maintenance-actions {
        display: inline-flex;
        gap: .45rem;
        align-items: center;
        justify-content: flex-end;
    }

    @media (max-width: 767.98px) {
        .maintenance-item {
            grid-template-columns: 1fr;
        }
        .maintenance-side {
            justify-items: start;
            min-width: 0;
            width: 100%;
            border-top: 1px dashed rgba(2,8,20,.08);
            padding-top: .75rem;
        }
        .maintenance-actions {
            width: 100%;
            justify-content: flex-start;
        }
        .maintenance-actions .btn {
            width: 100%;
        }
    }
</style>
@endpush
