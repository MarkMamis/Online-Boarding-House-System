@extends('layouts.admin')

@section('content')
    <style>
        .boarded-shell {
            background: linear-gradient(180deg, #ffffff 0%, #fbfdfc 100%);
            border: 1px solid rgba(2, 8, 20, .08);
            border-radius: 1.25rem;
            box-shadow: 0 10px 26px rgba(2, 8, 20, .06);
            padding: 1.25rem;
        }

        .boarded-muted {
            color: rgba(2, 8, 20, .58);
        }

        .boarded-metric {
            border: 1px solid rgba(2, 8, 20, .08);
            border-radius: 1rem;
            background: #fff;
            box-shadow: 0 6px 16px rgba(2, 8, 20, .04);
            padding: .95rem 1rem;
            height: 100%;
        }

        .boarded-metric-label {
            font-size: .78rem;
            text-transform: uppercase;
            letter-spacing: .04em;
            color: rgba(2, 8, 20, .55);
        }

        .boarded-metric-value {
            font-size: 1.45rem;
            font-weight: 700;
            color: #166534;
        }

        .boarded-card {
            border: 1px solid rgba(2, 8, 20, .08);
            border-radius: 1rem;
            background: #fff;
            box-shadow: 0 8px 20px rgba(2, 8, 20, .05);
            overflow: hidden;
        }

        .boarded-card-header {
            padding: .85rem 1rem;
            border-bottom: 1px solid rgba(2, 8, 20, .08);
            background: #fff;
        }

        .tenant-avatar {
            width: 40px;
            height: 40px;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(22, 101, 52, .12);
            color: #166534;
            border: 1px solid rgba(22, 101, 52, .22);
            flex-shrink: 0;
            font-weight: 700;
            text-transform: uppercase;
        }

        .table thead th {
            font-size: .78rem;
            text-transform: uppercase;
            letter-spacing: .05em;
            color: rgba(2, 8, 20, .62);
            background: rgba(248, 250, 252, .96);
            border-bottom: 1px solid rgba(2, 8, 20, .08);
            white-space: nowrap;
        }

        .table tbody td {
            vertical-align: middle;
        }

        .table-empty {
            padding: 3rem 1rem;
            text-align: center;
            color: rgba(2, 8, 20, .58);
        }
    </style>

    <div class="boarded-shell container-fluid py-2">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
            <div>
                <div class="text-uppercase small boarded-muted fw-semibold">Monitoring</div>
                <h1 class="h3 mb-1">Boarded Students</h1>
                <p class="boarded-muted mb-0">Current active tenants with room, property, and stay start date.</p>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-6 col-lg-3">
                <div class="boarded-metric">
                    <div class="boarded-metric-label">Active Boardings</div>
                    <div class="boarded-metric-value">{{ number_format($activeBoardings) }}</div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="boarded-metric">
                    <div class="boarded-metric-label">Active Tenants</div>
                    <div class="boarded-metric-value">{{ number_format($activeTenants) }}</div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="boarded-metric">
                    <div class="boarded-metric-label">Occupied Rooms</div>
                    <div class="boarded-metric-value">{{ number_format($activeRooms) }}</div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="boarded-metric">
                    <div class="boarded-metric-label">Active Properties</div>
                    <div class="boarded-metric-value">{{ number_format($activeProperties) }}</div>
                </div>
            </div>
        </div>

        <div class="boarded-card mb-3">
            <div class="boarded-card-header fw-semibold"><i class="bi bi-funnel me-1"></i> Search</div>
            <div class="p-3">
                <form class="row g-2 align-items-end" method="GET" action="{{ route('admin.boarded_students.index') }}">
                    <div class="col-12 col-md-9">
                        <label class="form-label small text-uppercase text-muted mb-1">Find tenant / room / property</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                            <input
                                type="text"
                                class="form-control"
                                name="search"
                                value="{{ $search ?? '' }}"
                                placeholder="Tenant name, email, room number, property"
                            >
                        </div>
                    </div>
                    <div class="col-12 col-md-3 d-flex gap-2">
                        <button class="btn btn-success flex-fill" type="submit">
                            <i class="bi bi-check2-circle me-1"></i>Apply
                        </button>
                        <a href="{{ route('admin.boarded_students.index') }}" class="btn btn-outline-secondary">Reset</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="boarded-card">
            <div class="boarded-card-header fw-semibold"><i class="bi bi-people me-1"></i> Current Tenant List</div>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="ps-3">Tenant</th>
                            <th>Room</th>
                            <th>Property</th>
                            <th>Start Date</th>
                            <th>Booked Length</th>
                            <th class="pe-3">Expected Move-out</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($boardedStudents as $boarding)
                            <tr>
                                <td class="ps-3">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="tenant-avatar">{{ strtoupper(substr($boarding->student->full_name ?? 'U', 0, 1)) }}</span>
                                        <div class="min-w-0">
                                            <div class="fw-semibold text-truncate">{{ $boarding->student->full_name ?? 'Unknown tenant' }}</div>
                                            <div class="small text-muted text-truncate">{{ $boarding->student->email ?? 'No email' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-semibold">{{ $boarding->room?->room_number ? 'Room ' . $boarding->room->room_number : 'N/A' }}</div>
                                </td>
                                <td>
                                    <div class="fw-semibold">{{ $boarding->room?->property?->name ?? 'N/A' }}</div>
                                    <div class="small text-muted text-truncate">{{ $boarding->room?->property?->address ?? 'Address not set' }}</div>
                                </td>
                                <td>{{ $boarding->check_in ? $boarding->check_in->format('M d, Y') : 'N/A' }}</td>
                                <td>
                                    @if($boarding->check_in)
                                        @php
                                            $stayEndDate = $boarding->check_out
                                                ? $boarding->check_out->copy()->startOfDay()
                                                : \Illuminate\Support\Carbon::today();
                                            $stayDays = max(0, $boarding->check_in->copy()->startOfDay()->diffInDays($stayEndDate, false));
                                        @endphp
                                        {{ $stayDays }} day{{ $stayDays === 1 ? '' : 's' }}
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td class="pe-3">
                                    @if($boarding->check_out)
                                        @php
                                            $expectedMoveOut = $boarding->check_out->copy()->startOfDay();
                                            $today = \Illuminate\Support\Carbon::today();
                                            $daysLeft = $today->diffInDays($expectedMoveOut, false);
                                        @endphp
                                        <div>{{ $expectedMoveOut->format('M d, Y') }}</div>
                                        @if($daysLeft > 0)
                                            <div class="small text-success fw-semibold">{{ $daysLeft }} day{{ $daysLeft === 1 ? '' : 's' }} left</div>
                                        @elseif($daysLeft === 0)
                                            <div class="small text-warning fw-semibold">Moves out today</div>
                                        @else
                                            <div class="small text-danger fw-semibold">Overdue by {{ abs($daysLeft) }} day{{ abs($daysLeft) === 1 ? '' : 's' }}</div>
                                        @endif
                                    @else
                                        <div>Open-ended</div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="table-empty">No active boarded students found for the current filters.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($boardedStudents->hasPages())
                <div class="p-3 border-top">{{ $boardedStudents->links() }}</div>
            @endif
        </div>
    </div>
@endsection
