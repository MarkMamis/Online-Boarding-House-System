@extends('layouts.admin')

@section('title', 'Reports Management')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">Reports Management</h4>
                    <div>
                        <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-primary btn-sm me-2">
                            <i class="fas fa-home me-1"></i>Dashboard
                        </a>
                        <span class="badge bg-info">{{ $reports->total() }} Total Reports</span>
                    </div>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Student</th>
                                    <th>Title</th>
                                    <th>Priority</th>
                                    <th>Status</th>
                                    <th>Submitted</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($reports as $report)
                                    <tr>
                                        <td>{{ $report->id }}</td>
                                        <td>
                                            <strong>{{ $report->user->name }}</strong><br>
                                            <small class="text-muted">{{ $report->user->email }}</small>
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.reports.show', $report) }}" class="text-decoration-none">
                                                {{ Str::limit($report->title, 50) }}
                                            </a>
                                        </td>
                                        <td>
                                            @if($report->priority === 'high')
                                                <span class="badge bg-danger">High</span>
                                            @elseif($report->priority === 'medium')
                                                <span class="badge bg-warning">Medium</span>
                                            @else
                                                <span class="badge bg-success">Low</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($report->status === 'pending')
                                                <span class="badge bg-secondary">Pending</span>
                                            @elseif($report->status === 'in_progress')
                                                <span class="badge bg-primary">In Progress</span>
                                            @else
                                                <span class="badge bg-success">Resolved</span>
                                            @endif
                                        </td>
                                        <td>{{ $report->created_at->format('M d, Y') }}</td>
                                        <td>
                                            <a href="{{ route('admin.reports.show', $report) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye"></i> View
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4">
                                            <div class="text-muted">
                                                <i class="fas fa-inbox fa-2x mb-2"></i>
                                                <p>No reports found.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($reports->hasPages())
                        <div class="d-flex justify-content-center mt-4">
                            {{ $reports->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection