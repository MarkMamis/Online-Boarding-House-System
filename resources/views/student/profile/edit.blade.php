@extends('layouts.student_dashboard')

@section('content')
<div class="container py-4">
    <div class="glass-card p-4 shadow-sm">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
            <div>
                <h4 class="mb-1">Edit Profile</h4>
                <div class="text-muted small">Update your account details</div>
            </div>
            <a href="{{ route('student.profile.show') }}" class="btn btn-outline-secondary btn-sm">Back to Profile</a>
        </div>

        @if (session('status'))
            <div class="alert alert-success small mb-3">{{ session('status') }}</div>
        @endif

        <form action="{{ route('student.profile.update') }}" method="POST" class="row g-3">
            @csrf
            @method('PUT')

            <div class="col-12 col-md-6">
                <label class="form-label">First Name</label>
                <input
                    type="text"
                    name="first_name"
                    value="{{ old('first_name', $user->first_name) }}"
                    class="form-control @error('first_name') is-invalid @enderror"
                />
                @error('first_name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12 col-md-6">
                <label class="form-label">Last Name</label>
                <input
                    type="text"
                    name="last_name"
                    value="{{ old('last_name', $user->last_name) }}"
                    class="form-control @error('last_name') is-invalid @enderror"
                />
                @error('last_name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12 col-md-6">
                <label class="form-label">Email</label>
                <input
                    type="email"
                    name="email"
                    value="{{ old('email', $user->email) }}"
                    class="form-control @error('email') is-invalid @enderror"
                />
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12 col-md-6">
                <label class="form-label">Phone</label>
                <input
                    type="text"
                    name="phone"
                    value="{{ old('phone', $user->phone) }}"
                    class="form-control @error('phone') is-invalid @enderror"
                />
                @error('phone')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12">
                <label class="form-label">Address</label>
                <input
                    type="text"
                    name="address"
                    value="{{ old('address', $user->address) }}"
                    class="form-control @error('address') is-invalid @enderror"
                />
                @error('address')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12 col-md-4">
                <label class="form-label">City</label>
                <input
                    type="text"
                    name="city"
                    value="{{ old('city', $user->city) }}"
                    class="form-control @error('city') is-invalid @enderror"
                />
                @error('city')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12 col-md-4">
                <label class="form-label">Province</label>
                <input
                    type="text"
                    name="province"
                    value="{{ old('province', $user->province) }}"
                    class="form-control @error('province') is-invalid @enderror"
                />
                @error('province')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12 col-md-4">
                <label class="form-label">Zip Code</label>
                <input
                    type="text"
                    name="zip_code"
                    value="{{ old('zip_code', $user->zip_code) }}"
                    class="form-control @error('zip_code') is-invalid @enderror"
                />
                @error('zip_code')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12">
                <label class="form-label">About</label>
                <textarea
                    name="about"
                    rows="4"
                    class="form-control @error('about') is-invalid @enderror"
                >{{ old('about', $user->about) }}</textarea>
                @error('about')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12 d-flex flex-wrap gap-2">
                <button type="submit" class="btn btn-primary">Save Changes</button>
                <a href="{{ route('student.profile.show') }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
