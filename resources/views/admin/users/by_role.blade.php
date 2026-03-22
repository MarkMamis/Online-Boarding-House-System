@extends('layouts.admin')

@section('content')
<style>
    .role-shell {
        background: linear-gradient(180deg, #ffffff 0%, #fbfdfc 100%);
        border: 1px solid rgba(2, 8, 20, .08);
        border-radius: 1.25rem;
        box-shadow: 0 10px 26px rgba(2, 8, 20, .06);
        padding: 1.25rem;
    }
    .muted { color: rgba(2, 8, 20, .58); }
    .metric-tile {
        border: 1px solid rgba(2, 8, 20, .08);
        border-radius: 1rem;
        background: #fff;
        box-shadow: 0 8px 18px rgba(2, 8, 20, .05);
        padding: 1.2rem 1.1rem;
        height: 100%;
    }
    .metric-label {
        font-size: .75rem;
        text-transform: uppercase;
        letter-spacing: .05em;
        color: rgba(2, 8, 20, .55);
        margin-bottom: .5rem;
    }
    .metric-value {
        font-size: 2rem;
        font-weight: 800;
        color: #166534;
        line-height: 1;
    }
    .metric-subtitle {
        font-size: .8rem;
        color: rgba(2, 8, 20, .65);
        margin-top: .25rem;
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
        padding: 1rem 1.25rem;
    }
    .table thead th {
        font-size: .75rem;
        text-transform: uppercase;
        letter-spacing: .05em;
        color: rgba(2, 8, 20, .62);
        background: rgba(248, 250, 252, .96);
        border: none;
        vertical-align: middle;
        font-weight: 600;
    }
    .table tbody td {
        vertical-align: middle;
        border-color: rgba(2, 8, 20, .05);
        padding: .8rem 1rem;
    }
    .table tbody tr {
        transition: background-color .15s ease;
    }
    .table tbody tr:hover {
        background-color: rgba(248, 250, 252, .5);
    }
    .avatar-item {
        width: 38px;
        height: 38px;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid rgba(22, 101, 52, .22);
        background: rgba(22, 101, 52, .12);
        color: #166534;
        font-weight: 700;
        font-size: .85rem;
        flex-shrink: 0;
        margin-right: .75rem;
    }
    @media (max-width: 767.98px) {
        .role-shell { padding: .95rem; }
        .metric-value { font-size: 1.4rem; }
        .metric-tile { padding: 1rem; }
    }
</style>

<div class="role-shell">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <div class="text-uppercase small muted fw-semibold">User Management</div>
            <h1 class="h4 mb-1">{{ $roleTitle }}</h1>
            <div class="muted small">Manage {{ strtolower($roleTitle) }} and their information</div>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary rounded-pill px-3">
                All Users
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Statistics -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-md-6 col-lg-4">
            <div class="metric-tile">
                <div class="metric-label"><i class="bi bi-{{ $role === 'landlord' ? 'building' : 'mortarboard' }} me-1"></i>Total</div>
                <div class="metric-value">{{ $users->total() }}</div>
                <div class="metric-subtitle">{{ $roleTitle }} registered</div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-lg-4">
            <div class="metric-tile">
                <div class="metric-label"><i class="bi bi-calendar me-1"></i>This Week</div>
                <div class="metric-value">{{ $users->where('created_at', '>=', now()->startOfWeek())->count() }}</div>
                <div class="metric-subtitle">New registrations</div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-lg-4">
            <div class="metric-tile">
                <div class="metric-label"><i class="bi bi-calendar2 me-1"></i>This Month</div>
                <div class="metric-value">{{ $users->where('created_at', '>=', now()->startOfMonth())->count() }}</div>
                <div class="metric-subtitle">Monthly registrations</div>
            </div>
        </div>
    </div>

    <!-- Users Table -->
    <div class="section-card">
        <div class="section-header d-flex justify-content-between align-items-center">
            <div class="fw-semibold"><i class="bi bi-{{ $role === 'landlord' ? 'building' : 'person-lines-fill' }} me-2"></i>{{ $roleTitle }} List</div>
            <span class="badge text-bg-secondary">{{ $users->total() }} {{ strtolower($roleTitle) }}</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th class="ps-3">Name</th>
                        <th>Email</th>
                        <th>Contact</th>
                        @if($role === 'landlord')
                            <th>Boarding House</th>
                            <th>Tenants</th>
                            <th>Onboarding</th>
                            <th>Rooms</th>
                            <th>Occupancy</th>
                        @endif
                        <th>Registered</th>
                        <th class="pe-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td class="ps-3">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-item">{{ strtoupper(substr($user->full_name, 0, 1)) }}</div>
                                    <div>
                                        <div class="fw-semibold small">{{ $user->full_name }}</div>
                                        <div class="text-muted small" style="font-size: .75rem;">{{ $user->name }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="small">{{ $user->email }}</td>
                            <td class="small muted">{{ $user->contact_number ?: '—' }}</td>
                            @if($role === 'landlord')
                                <td class="small">{{ $user->boarding_house_name ?: '—' }}</td>
                                <td>
                                    <span class="badge text-bg-success">{{ $user->current_tenants ?? 0 }}</span>
                                </td>
                                <td>
                                    <span class="badge text-bg-warning">{{ $user->onboarding_tenants ?? 0 }}</span>
                                </td>
                                <td class="small muted">{{ $user->occupied_rooms ?? 0 }}/{{ $user->total_rooms ?? 0 }}</td>
                                <td>
                                    @if(isset($user->total_rooms) && $user->total_rooms > 0)
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="progress grow" style="height: 5px; width: 60px;">
                                                <div class="progress-bar bg-{{ ($user->occupied_rooms / $user->total_rooms) > 0.8 ? 'danger' : (($user->occupied_rooms / $user->total_rooms) > 0.5 ? 'warning' : 'success') }}"
                                                     style="width: {{ ($user->occupied_rooms / $user->total_rooms) * 100 }}%"></div>
                                            </div>
                                            <small class="text-muted" style="min-width: 38px;">{{ round(($user->occupied_rooms / $user->total_rooms) * 100) }}%</small>
                                        </div>
                                    @else
                                        <small class="text-muted">—</small>
                                    @endif
                                </td>
                            @endif
                            <td class="small muted">{{ $user->created_at->format('M d, Y') }}</td>
                            <td class="pe-3">
                                @if($role === 'student')
                                    <a href="{{ route('admin.users.students.show', $user) }}" class="btn btn-sm btn-outline-secondary" title="View details">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                @elseif($role === 'landlord')
                                    <a href="{{ route('admin.users.landlords.show', $user) }}" class="btn btn-sm btn-outline-secondary" title="View details">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $role === 'landlord' ? '10' : '5' }}" class="text-center text-muted py-5">
                                <div><i class="bi bi-inbox" style="font-size: 2.5rem; opacity: 0.3;"></i></div>
                                <p class="mb-0 mt-2">No {{ strtolower($roleTitle) }} found.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($users->hasPages())
        <div style="padding: 1rem; border-top: 1px solid rgba(2, 8, 20, .08); background: #f8fafc;">
            {{ $users->links() }}
        </div>
        @endif
    </div>
</div>
@endsection