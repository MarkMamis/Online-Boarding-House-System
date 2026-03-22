@extends('layouts.landlord')

@section('content')
<div class="profile-edit-shell">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <div class="text-uppercase small text-muted fw-semibold">Account Settings</div>
                    <h1 class="h3 mb-1">
                        <i class="fas fa-user-edit me-2"></i>Edit Profile
                    </h1>
                    <p class="text-muted mb-0">Update your account information and preferences</p>
                </div>
                <div>
                    <a href="{{ route('landlord.dashboard') }}" class="btn btn-outline-secondary rounded-pill px-3">
                        <i class="fas fa-arrow-left me-1"></i>Back to Dashboard
                    </a>
                </div>
            </div>

            <div class="profile-summary mb-4">
                <div class="profile-summary-item">
                    <div class="profile-summary-label">Account Type</div>
                    <div class="profile-summary-value">Landlord</div>
                </div>
                <div class="profile-summary-item">
                    <div class="profile-summary-label">Member Since</div>
                    <div class="profile-summary-value">{{ $user->created_at->format('F d, Y') }}</div>
                </div>
                <div class="profile-summary-item">
                    <div class="profile-summary-label">Status</div>
                    <div class="profile-summary-value">Active</div>
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
                    <div class="card shadow-sm profile-main-card">
                        <div class="card-header bg-transparent border-0 pt-3 pb-0 px-3">
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
                                        <div class="d-flex align-items-center gap-3 profile-photo-panel">
                                            @if(!empty($user->profile_image_path))
                                                <img id="profile_image_preview" src="{{ asset('storage/' . $user->profile_image_path) }}" alt="Profile photo" class="rounded-circle border" style="width: 84px; height: 84px; object-fit: cover;">
                                            @else
                                                <img id="profile_image_preview" src="" alt="Profile photo" class="rounded-circle border d-none" style="width: 84px; height: 84px; object-fit: cover;">
                                                <div id="profile_image_placeholder" class="rounded-circle border bg-light d-flex align-items-center justify-content-center" style="width: 84px; height: 84px;">
                                                    <i class="fas fa-user text-muted"></i>
                                                </div>
                                            @endif
                                            <div class="grow">
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

                                <div class="d-flex justify-content-end mt-4 action-bar">
                                    <button type="submit" class="btn btn-brand rounded-pill px-4">
                                        <i class="fas fa-save me-2"></i>Update Profile
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card shadow-sm mt-3 profile-side-card">
                        <div class="card-header bg-transparent border-0 pb-0">
                            <h5 class="mb-0">
                                <i class="fas fa-shield-alt me-2"></i>Security Tips
                            </h5>
                        </div>
                        <div class="card-body profile-side-body">
                            <div class="security-tip-item">
                                <span class="security-tip-icon"><i class="fas fa-key"></i></span>
                                <span class="security-tip-text">Use a strong password with at least 8 characters</span>
                            </div>
                            <div class="security-tip-item">
                                <span class="security-tip-icon"><i class="fas fa-phone-alt"></i></span>
                                <span class="security-tip-text">Keep your contact information up to date</span>
                            </div>
                            <div class="security-tip-item mb-0">
                                <span class="security-tip-icon"><i class="fas fa-home"></i></span>
                                <span class="security-tip-text">Regularly update your boarding house information</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .profile-edit-shell {
        background: linear-gradient(180deg, #ffffff 0%, #fbfdfc 100%);
        border: 1px solid rgba(2,8,20,.08);
        border-radius: 1.25rem;
        box-shadow: 0 10px 26px rgba(2,8,20,.06);
        padding: 1.25rem;
    }
    .profile-summary {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: .75rem;
    }
    .profile-summary-item {
        border: 1px solid rgba(20,83,45,.16);
        background: linear-gradient(180deg, rgba(167,243,208,.18), rgba(255,255,255,.85));
        border-radius: .9rem;
        padding: .7rem .8rem;
        min-width: 0;
    }
    .profile-summary-label {
        font-size: .72rem;
        text-transform: uppercase;
        letter-spacing: .05em;
        color: rgba(2,8,20,.55);
        font-weight: 700;
        margin-bottom: .2rem;
    }
    .profile-summary-value {
        font-size: .94rem;
        font-weight: 700;
        color: #14532d;
    }
    .profile-main-card,
    .profile-side-card {
        border: 1px solid rgba(2,8,20,.08);
        border-radius: 1rem;
        box-shadow: 0 14px 30px rgba(2,8,20,.07);
    }
    .profile-main-card .card-body,
    .profile-side-card .card-body {
        padding: 1rem;
    }
    .profile-side-body {
        display: grid;
        gap: .6rem;
    }
    .profile-main-card .form-label {
        font-weight: 600;
        color: #0f172a;
    }
    .profile-main-card .form-control {
        border-color: rgba(2,8,20,.14);
    }
    .profile-photo-panel {
        border: 1px solid rgba(2,8,20,.08);
        border-radius: .85rem;
        background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
        padding: .7rem;
    }
    .action-bar {
        position: sticky;
        bottom: .4rem;
        z-index: 3;
        padding: .55rem;
        background: rgba(248,250,252,.88);
        border: 1px solid rgba(2,8,20,.08);
        border-radius: .85rem;
    }
    .side-info-item {
        border: 1px solid rgba(2,8,20,.1);
        border-radius: .75rem;
        background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
        padding: .55rem .65rem;
    }
    .side-info-label {
        font-size: .76rem;
        text-transform: uppercase;
        letter-spacing: .05em;
        color: rgba(2,8,20,.56);
        font-weight: 700;
        margin-bottom: .15rem;
    }
    .side-info-value {
        font-weight: 600;
        color: #0f172a;
        line-height: 1.3;
    }
    .security-tip-item {
        display: grid;
        grid-template-columns: 28px minmax(0, 1fr);
        align-items: start;
        gap: .55rem;
        border: 1px solid rgba(22,101,52,.14);
        border-radius: .75rem;
        background: linear-gradient(180deg, rgba(167,243,208,.16), #ffffff);
        padding: .55rem .6rem;
    }
    .security-tip-icon {
        width: 28px;
        height: 28px;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #166534;
        background: rgba(22,101,52,.12);
        font-size: .76rem;
    }
    .security-tip-text {
        font-size: .86rem;
        color: #0f172a;
        font-weight: 500;
        line-height: 1.3;
    }
    @media (max-width: 991.98px) {
        .profile-summary {
            grid-template-columns: 1fr;
        }
        .profile-edit-shell {
            padding: .95rem;
        }
    }
</style>
@endpush

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