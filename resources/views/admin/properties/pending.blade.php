@extends('layouts.admin')

@section('title', 'Pending Properties - Admin Panel')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1"><i class="fas fa-clock me-2"></i>Pending Property Approvals</h1>
            <p class="text-muted mb-0">Review newly added properties before they become visible to students.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.properties.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-list me-1"></i>All Properties
            </a>
            <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>Dashboard
            </a>
        </div>
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

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Pending Properties ({{ $properties->total() }})</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Property</th>
                            <th>Landlord</th>
                            <th>Address</th>
                            <th>Submitted</th>
                            <th style="width: 320px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($properties as $property)
                            <tr>
                                <td class="fw-bold">{{ $property->name }}</td>
                                <td>
                                    <div>{{ $property->landlord->full_name }}</div>
                                    <div class="text-muted small">{{ $property->landlord->email }}</div>
                                </td>
                                <td>{{ $property->address }}</td>
                                <td>{{ $property->created_at->format('M d, Y h:i A') }}</td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <form method="POST" action="{{ route('admin.properties.approve', $property) }}">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success">
                                                <i class="fas fa-check me-1"></i>Approve
                                            </button>
                                        </form>

                                        <form method="POST" action="{{ route('admin.properties.reject', $property) }}" class="d-flex gap-2">
                                            @csrf
                                            <input type="text" name="rejection_reason" class="form-control form-control-sm" placeholder="Reason (optional)" maxlength="500">
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="fas fa-times me-1"></i>Reject
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-5">
                                    <i class="fas fa-check-circle fa-2x mb-2"></i>
                                    <div>No pending properties.</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($properties->hasPages())
            <div class="card-footer">
                {{ $properties->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
