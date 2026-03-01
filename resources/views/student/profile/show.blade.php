@extends('layouts.student_dashboard')

@section('title', 'Profile')

@section('content')
<div class="glass-card rounded-4 p-4 p-md-5 mb-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="fw-semibold mb-0">Profile</h4>
    </div>

    <div class="border rounded-4 bg-white shadow-sm p-4">
        @if(session('success'))
            <div class="alert alert-success rounded-4">{{ session('success') }}</div>
        @endif

        <div class="row g-3">
            <div class="col-12 col-md-6">
                <div class="small text-muted">Full name</div>
                <div class="fw-semibold">{{ $user->full_name }}</div>
            </div>
            <div class="col-12 col-md-6">
                <div class="small text-muted">Email</div>
                <div class="fw-semibold">{{ $user->email }}</div>
            </div>
            <div class="col-12 col-md-6">
                <div class="small text-muted">Student ID</div>
                <div class="fw-semibold">{{ $user->student_id ?: '—' }}</div>
            </div>
            <div class="col-12 col-md-6">
                <div class="small text-muted">Contact</div>
                <div class="fw-semibold">{{ $user->contact_number ?: '—' }}</div>
            </div>
            <div class="col-12 col-md-6">
                <div class="small text-muted">Course</div>
                <div class="fw-semibold">{{ $user->course ?: '—' }}</div>
            </div>
            <div class="col-12 col-md-6">
                <div class="small text-muted">Year level</div>
                <div class="fw-semibold">{{ $user->year_level ?: '—' }}</div>
            </div>
            <div class="col-12">
                <div class="small text-muted">Address</div>
                <div class="fw-semibold">{{ $user->address ?: '—' }}</div>
            </div>
        </div>
        <div class="mt-3">
            <a href="{{ route('student.profile.edit') }}" class="btn btn-brand btn-sm rounded-pill px-3">Edit profile</a>
            <a href="{{ route('student.profile.change-password') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">Change password</a>
        </div>
    </div>
</div>
@endsection
