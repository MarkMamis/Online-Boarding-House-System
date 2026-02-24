@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h4 mb-1">User Management</h1>
        <div class="text-muted small">Manage all system users and their roles</div>
    </div>
    <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-primary">Back to Dashboard</a>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<!-- User Statistics -->
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body text-center">
                <h4 class="card-title mb-0">{{ $users->total() }}</h4>
                <p class="card-text mb-0">Total Users</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <a href="{{ route('admin.users.students') }}" class="text-decoration-none">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <h4 class="card-title mb-0">{{ $users->where('role', 'student')->count() }}</h4>
                    <p class="card-text mb-0">Students</p>
                    <small>Click to view details</small>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-3">
        <a href="{{ route('admin.users.landlords') }}" class="text-decoration-none">
            <div class="card bg-warning text-white">
                <div class="card-body text-center">
                    <h4 class="card-title mb-0">{{ $users->where('role', 'landlord')->count() }}</h4>
                    <p class="card-text mb-0">Landlords</p>
                    <small>Click to view details</small>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-3">
        <a href="{{ route('admin.users.admins') }}" class="text-decoration-none">
            <div class="card bg-danger text-white">
                <div class="card-body text-center">
                    <h4 class="card-title mb-0">{{ $users->where('role', 'admin')->count() }}</h4>
                    <p class="card-text mb-0">Admins</p>
                    <small>Click to view details</small>
                </div>
            </div>
        </a>
    </div>
</div>

<!-- Users Table -->
<div class="card shadow-sm">
    <div class="card-header">
        <h5 class="mb-0">All Users</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Contact</th>
                        <th>Registered</th>
                        <th>Status</th>
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
                            <td>
                                <span class="badge bg-{{ $user->role === 'admin' ? 'danger' : ($user->role === 'landlord' ? 'warning' : 'success') }}">
                                    {{ ucfirst($user->role) }}
                                </span>
                            </td>
                            <td>{{ $user->contact_number ?: 'N/A' }}</td>
                            <td class="text-muted small">{{ $user->created_at->format('M j, Y') }}</td>
                            <td>
                                <span class="badge bg-success">Active</span>
                            </td>
                            <td>
                                @if($user->role === 'student')
                                    <a href="{{ route('admin.users.students.show', $user) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye me-1"></i>View Details
                                    </a>
                                @elseif($user->role === 'landlord')
                                    <a href="{{ route('admin.users.landlords.show', $user) }}" class="btn btn-sm btn-outline-warning">
                                        <i class="fas fa-eye me-1"></i>View Details
                                    </a>
                                @else
                                    <span class="text-muted small">N/A</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                <i class="fas fa-users fa-3x mb-3 text-muted"></i>
                                <p class="mb-0">No users found.</p>
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