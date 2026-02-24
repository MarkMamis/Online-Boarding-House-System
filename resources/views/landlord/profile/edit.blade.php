@extends('layouts.landlord')

@section('content')
<div class="glass-card rounded-4 p-4 p-md-5">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-1">
                        <i class="fas fa-user-edit me-2"></i>Edit Profile
                    </h1>
                    <p class="text-muted mb-0">Update your account information and preferences</p>
                </div>
                <div>
                    <a href="{{ route('landlord.dashboard') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i>Back to Dashboard
                    </a>
                </div>
            </div>

            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if(session('success'))
                <div class="alert alert-success">
                    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                </div>
            @endif

            <div class="row">
                <div class="col-lg-8">
                    <div class="card shadow-sm">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-user me-2"></i>Personal Information
                            </h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('landlord.profile.update') }}" enctype="multipart/form-data">
                                @csrf
                                @method('PUT')

                                <div class="row g-3">
                                    <div class="col-12">
                                        <label class="form-label">Profile Photo</label>
                                        <div class="d-flex align-items-center gap-3">
                                            @if(!empty($user->profile_image_path))
                                                <img id="profile_image_preview" src="{{ asset('storage/' . $user->profile_image_path) }}" alt="Profile photo" class="rounded-circle border" style="width: 84px; height: 84px; object-fit: cover;">
                                            @else
                                                <img id="profile_image_preview" src="" alt="Profile photo" class="rounded-circle border d-none" style="width: 84px; height: 84px; object-fit: cover;">
                                                <div id="profile_image_placeholder" class="rounded-circle border bg-light d-flex align-items-center justify-content-center" style="width: 84px; height: 84px;">
                                                    <i class="fas fa-user text-muted"></i>
                                                </div>
                                            @endif
                                            <div class="flex-grow-1">
                                                <input id="profile_image_input" type="file" name="profile_image" class="form-control @error('profile_image') is-invalid @enderror" accept="image/*">
                                                @error('profile_image')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                                <div class="form-text">JPG, PNG, WEBP, GIF (max 2MB)</div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Full Name <span class="text-danger">*</span></label>
                                        <input type="text" name="full_name" value="{{ old('full_name', $user->full_name) }}"
                                               class="form-control @error('full_name') is-invalid @enderror" required>
                                        @error('full_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Email Address <span class="text-danger">*</span></label>
                                        <input type="email" name="email" value="{{ old('email', $user->email) }}"
                                               class="form-control @error('email') is-invalid @enderror" required>
                                        @error('email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Contact Number</label>
                                        <input type="text" name="contact_number" value="{{ old('contact_number', $user->contact_number) }}"
                                               class="form-control @error('contact_number') is-invalid @enderror"
                                               placeholder="e.g. +63 912 345 6789">
                                        @error('contact_number')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Boarding House Name</label>
                                        <input type="text" name="boarding_house_name" value="{{ old('boarding_house_name', $user->boarding_house_name) }}"
                                               class="form-control @error('boarding_house_name') is-invalid @enderror"
                                               placeholder="e.g. Mindoro Way Boarding House">
                                        @error('boarding_house_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-12">
                                        <hr class="my-4">
                                        <h6 class="fw-semibold mb-3">
                                            <i class="fas fa-lock me-2"></i>Change Password (Optional)
                                        </h6>
                                        <p class="text-muted small mb-3">Leave blank if you don't want to change your password</p>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Current Password</label>
                                        <input type="password" name="current_password" class="form-control @error('current_password') is-invalid @enderror">
                                        @error('current_password')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">New Password</label>
                                        <input type="password" name="new_password" class="form-control @error('new_password') is-invalid @enderror"
                                               minlength="8">
                                        @error('new_password')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div class="form-text">Minimum 8 characters</div>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Confirm New Password</label>
                                        <input type="password" name="new_password_confirmation" class="form-control">
                                        <div class="form-text">Re-enter your new password</div>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-end mt-4">
                                    <button type="submit" class="btn btn-brand">
                                        <i class="fas fa-save me-2"></i>Update Profile
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card shadow-sm">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-info-circle me-2"></i>Account Information
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <strong>Account Type:</strong>
                                <span class="badge bg-warning text-dark ms-2">Landlord</span>
                            </div>

                            <div class="mb-3">
                                <strong>Member Since:</strong>
                                <div class="text-muted">{{ $user->created_at->format('F d, Y') }}</div>
                            </div>

                            <div class="mb-3">
                                <strong>Last Updated:</strong>
                                <div class="text-muted">{{ $user->updated_at->format('F d, Y \a\t g:i A') }}</div>
                            </div>

                            <div class="mb-0">
                                <strong>Account Status:</strong>
                                <span class="badge bg-success ms-2">Active</span>
                            </div>
                        </div>
                    </div>

                    <div class="card shadow-sm mt-3">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-shield-alt me-2"></i>Security Tips
                            </h5>
                        </div>
                        <div class="card-body">
                            <ul class="list-unstyled small mb-0">
                                <li class="mb-2">
                                    <i class="fas fa-check-circle text-success me-2"></i>
                                    Use a strong password with at least 8 characters
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check-circle text-success me-2"></i>
                                    Keep your contact information up to date
                                </li>
                                <li class="mb-0">
                                    <i class="fas fa-check-circle text-success me-2"></i>
                                    Regularly update your boarding house information
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    (function () {
        const input = document.getElementById('profile_image_input');
        const preview = document.getElementById('profile_image_preview');
        const placeholder = document.getElementById('profile_image_placeholder');
        if (!input || !preview) return;

        input.addEventListener('change', function () {
            const file = input.files && input.files[0];
            if (!file) return;
            if (!file.type || !file.type.startsWith('image/')) return;

            const url = URL.createObjectURL(file);
            preview.src = url;
            preview.classList.remove('d-none');
            if (placeholder) placeholder.classList.add('d-none');
        });
    })();
</script>
@endpush