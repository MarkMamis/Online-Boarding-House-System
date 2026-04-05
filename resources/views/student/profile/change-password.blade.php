@extends('layouts.student_dashboard')

@section('content')
<div class="container py-4">
    <div class="glass-card p-4 shadow-sm">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
            <div>
                <h4 class="mb-1">Change Password</h4>
                <div class="text-muted small">Keep your account secure</div>
            </div>
            <a href="{{ route('student.profile.show') }}" class="btn btn-outline-secondary btn-sm">Back to Profile</a>
        </div>

        @if (session('status'))
            <div class="alert alert-success small mb-3">{{ session('status') }}</div>
        @endif

        <form action="{{ route('student.profile.update-password') }}" method="POST" class="row g-3">
            @csrf
            @method('PUT')

            <div class="col-12 col-md-6">
                <label class="form-label">Current Password</label>
                <input
                    type="password"
                    name="current_password"
                    class="form-control @error('current_password') is-invalid @enderror"
                    required
                    autocomplete="current-password"
                />
                @error('current_password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12 col-md-6">
                <label class="form-label">New Password</label>
                <input
                    type="password"
                    name="password"
                    class="form-control @error('password') is-invalid @enderror"
                    required
                    minlength="8"
                    autocomplete="new-password"
                />
                @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12 col-md-6">
                <label class="form-label">Confirm New Password</label>
                <input
                    type="password"
                    name="password_confirmation"
                    class="form-control @error('password_confirmation') is-invalid @enderror"
                    required
                    minlength="8"
                    autocomplete="new-password"
                />
                @error('password_confirmation')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12 d-flex flex-wrap gap-2">
                <button type="submit" class="btn btn-primary">Update Password</button>
                <a href="{{ route('student.profile.show') }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
