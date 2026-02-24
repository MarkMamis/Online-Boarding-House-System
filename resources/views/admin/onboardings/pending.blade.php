@extends('layouts.admin')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Pending Document Onboardings</h5>
                        <div>
                            <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-primary btn-sm me-2">
                                <i class="fas fa-home me-1"></i>Dashboard
                            </a>
                            <div class="btn-group" role="group">
                                <a href="{{ route('admin.onboardings.index') }}" class="btn btn-outline-primary btn-sm">All</a>
                                <a href="{{ route('admin.onboardings.active') }}" class="btn btn-outline-primary btn-sm">Active</a>
                                <a href="{{ route('admin.onboardings.pending') }}" class="btn btn-outline-primary btn-sm active">Pending</a>
                                <a href="{{ route('admin.onboardings.completed') }}" class="btn btn-outline-primary btn-sm">Completed</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        These students are waiting for document uploads to proceed with onboarding.
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Student</th>
                                    <th>Property</th>
                                    <th>Landlord</th>
                                    <th>Room</th>
                                    <th>Waiting Since</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($onboardings as $onboarding)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-sm rounded-circle me-3">
                                                <i class="fas fa-user"></i>
                                            </div>
                                            <div>
                                                <div class="fw-bold">{{ $onboarding->booking->student->full_name }}</div>
                                                <small class="text-muted">{{ $onboarding->booking->student->student_id }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <div class="fw-bold">{{ $onboarding->booking->room->property->name }}</div>
                                            <small class="text-muted">{{ $onboarding->booking->room->property->address }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-sm rounded-circle me-3">
                                                <i class="fas fa-building"></i>
                                            </div>
                                            <div>
                                                <div class="fw-bold">{{ $onboarding->booking->room->property->landlord->full_name }}</div>
                                                <small class="text-muted">{{ $onboarding->booking->room->property->landlord->email }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">{{ $onboarding->booking->room->room_number }}</span>
                                        <br>
                                        <small class="text-muted">₱{{ number_format($onboarding->booking->room->price, 2) }}/month</small>
                                    </td>
                                    <td>
                                        <small>{{ $onboarding->created_at->format('M d, Y') }}</small>
                                        <br>
                                        <small class="text-muted">{{ $onboarding->created_at->diffForHumans() }}</small>
                                        @if($onboarding->created_at->diffInDays() > 7)
                                            <br>
                                            <span class="badge bg-danger">Overdue</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.onboardings.show', $onboarding) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4">
                                        <div class="text-muted">
                                            <i class="fas fa-check-circle fa-2x mb-2"></i>
                                            <p>No pending document onboardings</p>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($onboardings->hasPages())
                    <div class="d-flex justify-content-center mt-4">
                        {{ $onboardings->links() }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection