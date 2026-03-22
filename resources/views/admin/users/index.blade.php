@extends('layouts.admin')

@section('content')
<style>
    .users-shell {
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
        padding: 1.5rem 1.25rem;
        height: 100%;
        transition: all .2s ease;
    }
    .metric-tile:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 28px rgba(2, 8, 20, .08);
        border-color: rgba(2, 8, 20, .12);
    }
    .metric-tile a { text-decoration: none; color: inherit; display: block; }
    .metric-label {
        font-size: .8rem;
        text-transform: uppercase;
        letter-spacing: .06em;
        color: rgba(2, 8, 20, .48);
        margin-bottom: .75rem;
        font-weight: 600;
    }
    .metric-value {
        font-size: 2.5rem;
        font-weight: 800;
        color: #166534;
        line-height: 1;
        margin-bottom: .5rem;
    }
    .metric-subtitle {
        font-size: .85rem;
        color: rgba(2, 8, 20, .62);
        line-height: 1.4;
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
        font-size: .78rem;
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
        padding: .85rem 1rem;
    }
    .table tbody tr {
        transition: background-color .15s ease;
    }
    .table tbody tr:hover {
        background-color: rgba(248, 250, 252, .5);
    }
    .avatar-item {
        width: 40px;
        height: 40px;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid rgba(22, 101, 52, .22);
        background: rgba(22, 101, 52, .12);
        color: #166534;
        font-weight: 700;
        font-size: .9rem;
        flex-shrink: 0;
        margin-right: .75rem;
    }
    @media (max-width: 767.98px) {
        .users-shell { padding: .95rem; }
        .metric-value { font-size: 1.75rem; }
        .metric-tile { padding: 1rem; }
    }
</style>

<div class="users-shell">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <div class="text-uppercase small muted fw-semibold">System Administration</div>
            <h1 class="h4 mb-1">User Management</h1>
            <div class="muted small">Manage all system users and their roles</div>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary rounded-pill px-3">
            Back to Dashboard
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- User Statistics -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-lg-6">
            <div class="metric-tile">
                <div class="metric-label"><i class="bi bi-people me-2"></i>Total Users</div>
                <div class="metric-value">{{ $users->whereIn('role', ['student', 'landlord'])->count() }}</div>
                <div class="metric-subtitle">All registered system accounts</div>
            </div>
        </div>
        <div class="col-12 col-lg-6">
            <div class="row g-3">
                <div class="col-6">
                    <a href="{{ route('admin.users.students') }}" style="text-decoration: none; color: inherit;">
                        <div class="metric-tile" style="text-align: center;">
                            <div class="metric-label"><i class="bi bi-mortarboard"></i></div>
                            <div class="metric-value text-success">{{ $users->where('role', 'student')->count() }}</div>
                            <div class="metric-subtitle">Students</div>
                        </div>
                    </a>
                </div>
                <div class="col-6">
                    <a href="{{ route('admin.users.landlords') }}" style="text-decoration: none; color: inherit;">
                        <div class="metric-tile" style="text-align: center;">
                            <div class="metric-label"><i class="bi bi-building"></i></div>
                            <div class="metric-value" style="color: #FFC107;">{{ $users->where('role', 'landlord')->count() }}</div>
                            <div class="metric-subtitle">Landlords</div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Users Table -->
    <div class="section-card">
        <div class="section-header d-flex justify-content-between align-items-center">
            <div class="fw-semibold"><i class="bi bi-person-lines-fill me-2"></i> All Users</div>
            <span class="badge text-bg-secondary">{{ $users->whereIn('role', ['student', 'landlord'])->count() }} users</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th class="ps-3">Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Contact</th>
                        <th>Registered</th>
                        <th>Status</th>
                        <th class="pe-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users->where('role', '!=', 'admin') as $user)
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
                            <td>
                                <span class="badge text-bg-{{ $user->role === 'landlord' ? 'warning' : 'success' }}">
                                    {{ ucfirst($user->role) }}
                                </span>
                            </td>
                            <td class="small muted">{{ $user->contact_number ?: '—' }}</td>
                            <td class="small muted">{{ $user->created_at->format('M d, Y') }}</td>
                            <td>
                                <span class="badge text-bg-{{ ($user->is_active ?? true) ? 'success' : 'secondary' }}">
                                    {{ ($user->is_active ?? true) ? '● Active' : '○ Inactive' }}
                                </span>
                            </td>
                            <td class="pe-3">
                                @if($user->role === 'student')
                                    <a href="{{ route('admin.users.students.show', $user) }}" class="btn btn-sm btn-outline-secondary" title="View student details">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                @elseif($user->role === 'landlord')
                                    <a href="{{ route('admin.users.landlords.show', $user) }}" class="btn btn-sm btn-outline-secondary" title="View landlord details">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-5">
                                <div><i class="bi bi-inbox" style="font-size: 2.5rem; opacity: 0.3;"></i></div>
                                <p class="mb-0 mt-2">No users found.</p>
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