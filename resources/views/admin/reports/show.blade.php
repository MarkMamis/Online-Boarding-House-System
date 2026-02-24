@extends('layouts.admin')

@section('title', 'View Report')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">Report #{{ $report->id }}</h4>
                    <div>
                        <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-primary btn-sm me-2">
                            <i class="fas fa-home me-1"></i>Dashboard
                        </a>
                        <a href="{{ route('admin.reports.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left me-1"></i> Back to Reports
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <div class="row">
                        <div class="col-md-8">
                            <!-- Report Details -->
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="mb-0">Report Details</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row mb-3">
                                        <div class="col-sm-3"><strong>Title:</strong></div>
                                        <div class="col-sm-9">{{ $report->title }}</div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-sm-3"><strong>Student:</strong></div>
                                        <div class="col-sm-9">
                                            {{ $report->user->name }} ({{ $report->user->email }})
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-sm-3"><strong>Priority:</strong></div>
                                        <div class="col-sm-9">
                                            @if($report->priority === 'high')
                                                <span class="badge bg-danger">High</span>
                                            @elseif($report->priority === 'medium')
                                                <span class="badge bg-warning">Medium</span>
                                            @else
                                                <span class="badge bg-success">Low</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-sm-3"><strong>Status:</strong></div>
                                        <div class="col-sm-9">
                                            @if($report->status === 'pending')
                                                <span class="badge bg-secondary">Pending</span>
                                            @elseif($report->status === 'in_progress')
                                                <span class="badge bg-primary">In Progress</span>
                                            @else
                                                <span class="badge bg-success">Resolved</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-sm-3"><strong>Submitted:</strong></div>
                                        <div class="col-sm-9">{{ $report->created_at->format('F d, Y \a\t g:i A') }}</div>
                                    </div>
                                    @if($report->resolved_at)
                                        <div class="row mb-3">
                                            <div class="col-sm-3"><strong>Resolved:</strong></div>
                                            <div class="col-sm-9">{{ $report->resolved_at->format('F d, Y \a\t g:i A') }}</div>
                                        </div>
                                    @endif
                                    <div class="row">
                                        <div class="col-sm-3"><strong>Description:</strong></div>
                                        <div class="col-sm-9">
                                            <div class="bg-light p-3 rounded">
                                                {!! nl2br(e($report->description)) !!}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <!-- Update Status -->
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">Update Report</h5>
                                </div>
                                <div class="card-body">
                                    <form action="{{ route('admin.reports.update', $report) }}" method="POST">
                                        @csrf
                                        @method('PUT')

                                        <div class="mb-3">
                                            <label for="status" class="form-label">Status</label>
                                            <select class="form-select" id="status" name="status" required>
                                                <option value="pending" {{ $report->status === 'pending' ? 'selected' : '' }}>Pending</option>
                                                <option value="in_progress" {{ $report->status === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                                <option value="resolved" {{ $report->status === 'resolved' ? 'selected' : '' }}>Resolved</option>
                                            </select>
                                        </div>

                                        <div class="mb-3">
                                            <label for="admin_response" class="form-label">Admin Response</label>
                                            <textarea class="form-control" id="admin_response" name="admin_response"
                                                      rows="4" placeholder="Add your response...">{{ $report->admin_response }}</textarea>
                                        </div>

                                        <button type="submit" class="btn btn-primary w-100">
                                            <i class="fas fa-save me-2"></i>Update Report
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection