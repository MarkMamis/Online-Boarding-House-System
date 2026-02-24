@extends('layouts.student')

@section('title', 'Submit Report')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">Submit a Report</h4>
                    <a href="{{ route('student.dashboard') }}" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-home me-1"></i>Dashboard
                    </a>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-4">
                        Use this form to report any issues, bugs, or unexpected events you've encountered in the system.
                        Our admin team will review your report and get back to you as soon as possible.
                    </p>

                    <form action="{{ route('student.reports.store') }}" method="POST">
                        @csrf

                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="title" class="form-label">Report Title <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('title') is-invalid @enderror"
                                       id="title" name="title" value="{{ old('title') }}" required>
                                @error('title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="priority" class="form-label">Priority Level <span class="text-danger">*</span></label>
                                <select class="form-select @error('priority') is-invalid @enderror"
                                        id="priority" name="priority" required>
                                    <option value="">Select Priority</option>
                                    <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>Low - Minor issue, not urgent</option>
                                    <option value="medium" {{ old('priority') == 'medium' ? 'selected' : '' }}>Medium - Moderate issue affecting functionality</option>
                                    <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>High - Critical issue requiring immediate attention</option>
                                </select>
                                @error('priority')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="description" class="form-label">Description <span class="text-danger">*</span></label>
                                <textarea class="form-control @error('description') is-invalid @enderror"
                                          id="description" name="description" rows="6"
                                          placeholder="Please provide detailed information about the issue you're experiencing..." required>{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">
                                    Include as much detail as possible: what you were doing, what you expected to happen, what actually happened, error messages, etc.
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-paper-plane me-2"></i>Submit Report
                                </button>
                                <a href="{{ route('student.dashboard') }}" class="btn btn-secondary ms-2">
                                    <i class="fas fa-times me-2"></i>Cancel
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection