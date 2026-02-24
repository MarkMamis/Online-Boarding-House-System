@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h4 mb-1">{{ $roleTitle }}</h1>
        <div class="text-muted small">Manage {{ strtolower($roleTitle) }} and their information</div>
    </div>
    <div>
        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary me-2">All Users</a>
        <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-primary">Back to Dashboard</a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<!-- Statistics -->
<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="card bg-{{ $role === 'admin' ? 'danger' : ($role === 'landlord' ? 'warning' : 'success') }} text-white">
            <div class="card-body text-center">
                <h4 class="card-title mb-0">{{ $users->total() }}</h4>
                <p class="card-text mb-0">Total {{ $roleTitle }}</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-info text-white">
            <div class="card-body text-center">
                <h4 class="card-title mb-0">{{ $users->where('created_at', '>=', now()->startOfWeek())->count() }}</h4>
                <p class="card-text mb-0">This Week</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-secondary text-white">
            <div class="card-body text-center">
                <h4 class="card-title mb-0">{{ $users->where('created_at', '>=', now()->startOfMonth())->count() }}</h4>
                <p class="card-text mb-0">This Month</p>
            </div>
        </div>
    </div>
</div>

<!-- Users Table -->
<div class="card shadow-sm">
    <div class="card-header">
        <h5 class="mb-0">{{ $roleTitle }} List</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Name</th>
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
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $user->full_name }}</div>
                                <div class="text-muted small">{{ $user->name }}</div>
                            </td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->contact_number ?: 'N/A' }}</td>
                            @if($role === 'landlord')
                                <td>{{ $user->boarding_house_name ?: 'N/A' }}</td>
                                <td>
                                    <span class="badge bg-success">{{ $user->current_tenants ?? 0 }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-warning">{{ $user->onboarding_tenants ?? 0 }}</span>
                                </td>
                                <td>
                                    <span class="text-muted">{{ $user->occupied_rooms ?? 0 }}/{{ $user->total_rooms ?? 0 }}</span>
                                </td>
                                <td>
                                    @if(isset($user->total_rooms) && $user->total_rooms > 0)
                                        <div class="d-flex align-items-center">
                                            <div class="progress flex-grow-1 me-2" style="height: 6px;">
                                                <div class="progress-bar bg-{{ ($user->occupied_rooms / $user->total_rooms) > 0.8 ? 'danger' : (($user->occupied_rooms / $user->total_rooms) > 0.5 ? 'warning' : 'success') }}"
                                                     style="width: {{ ($user->occupied_rooms / $user->total_rooms) * 100 }}%"></div>
                                            </div>
                                            <small class="text-muted">{{ round(($user->occupied_rooms / $user->total_rooms) * 100) }}%</small>
                                        </div>
                                    @else
                                        <small class="text-muted">No rooms</small>
                                    @endif
                                </td>
                            @endif
                            <td class="text-muted small">{{ $user->created_at->format('M j, Y') }}</td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    @if($role === 'student')
                                        <a href="{{ route('admin.users.students.show', $user) }}" class="btn btn-outline-primary" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    @elseif($role === 'landlord')
                                        <a href="{{ route('admin.users.landlords.show', $user) }}" class="btn btn-outline-primary" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    @endif
                                    <button class="btn btn-outline-warning" title="Edit User">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    @if($role !== 'admin')
                                        <button class="btn btn-outline-danger" title="Deactivate">
                                            <i class="fas fa-ban"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $role === 'landlord' ? '9' : '5' }}" class="text-center text-muted py-4">
                                <i class="fas fa-users fa-3x mb-3 text-muted"></i>
                                <p class="mb-0">No {{ strtolower($roleTitle) }} found.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer">
        {{ $users->links() }}
    </div>
</div>
@endsection