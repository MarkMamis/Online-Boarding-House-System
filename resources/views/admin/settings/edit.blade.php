@extends('layouts.admin')

@section('title', 'Admin Settings')

@section('content')
<div class="settings-shell">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <div class="text-uppercase small text-muted fw-semibold">Account Settings</div>
            <h1 class="h3 mb-1"><i class="bi bi-gear me-2"></i>Admin Settings</h1>
            <p class="text-muted mb-0">Manage your admin account details and security settings.</p>
        </div>
        <!-- <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary rounded-pill px-3">
            <i class="bi bi-arrow-left me-1"></i>Back to Dashboard
        </a> -->
    </div>

    <div class="settings-summary mb-4">
        <div class="settings-summary-item">
            <div class="settings-summary-label">Account Type</div>
            <div class="settings-summary-value">Administrator</div>
        </div>
        <div class="settings-summary-item">
            <div class="settings-summary-label">Member Since</div>
            <div class="settings-summary-value">{{ $user->created_at->format('F d, Y') }}</div>
        </div>
        <div class="settings-summary-item">
            <div class="settings-summary-label">Status</div>
            <div class="settings-summary-value">Active</div>
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

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card settings-card">
                <div class="card-header bg-transparent border-0 pt-3 pb-0">
                    <h5 class="mb-0"><i class="bi bi-person me-2"></i>Profile Information</h5>
                </div>
                <div class="card-body">
                    <div class="profile-form-wrap">
                    <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Profile Photo</label>
                                <div class="d-flex align-items-center gap-3 photo-panel">
                                    @if(!empty($user->profile_image_path))
                                        <img id="profile_image_preview" src="{{ asset('storage/' . $user->profile_image_path) }}" alt="Profile photo" class="rounded-circle border" style="width:84px;height:84px;object-fit:cover;">
                                    @else
                                        <img id="profile_image_preview" src="" alt="Profile photo" class="rounded-circle border d-none" style="width:84px;height:84px;object-fit:cover;">
                                        <div id="profile_image_placeholder" class="rounded-circle border bg-light d-flex align-items-center justify-content-center" style="width:84px;height:84px;">
                                            <i class="bi bi-person text-muted"></i>
                                        </div>
                                    @endif
                                    <div class="w-100">
                                        <input id="profile_image_input" type="file" name="profile_image" class="d-none @error('profile_image') is-invalid @enderror" accept="image/*">
                                        <div class="upload-control">
                                            <label for="profile_image_input" class="btn btn-outline-brand upload-trigger mb-0">
                                                <i class="bi bi-upload me-1"></i>Choose Photo
                                            </label>
                                            <span id="profile_file_name" class="upload-file-name">{{ !empty($user->profile_image_path) ? 'Current photo selected' : 'No file selected' }}</span>
                                        </div>
                                        @error('profile_image')
                                            <div class="small text-danger mt-2">{{ $message }}</div>
                                        @enderror
                                        <div class="form-text">JPG, PNG, WEBP, GIF (max 2MB)</div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Full Name <span class="text-danger">*</span></label>
                                <input type="text" name="full_name" value="{{ old('full_name', $user->full_name) }}" class="form-control @error('full_name') is-invalid @enderror" required>
                                @error('full_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Email Address <span class="text-danger">*</span></label>
                                <input type="email" name="email" value="{{ old('email', $user->email) }}" class="form-control @error('email') is-invalid @enderror" required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Contact Number</label>
                                <input type="text" name="contact_number" value="{{ old('contact_number', $user->contact_number) }}" class="form-control @error('contact_number') is-invalid @enderror" placeholder="e.g. +63 912 345 6789">
                                @error('contact_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <hr class="my-2">
                                <h6 class="fw-semibold mb-2"><i class="bi bi-shield-lock me-2"></i>Change Password (Optional)</h6>
                                <p class="text-muted small mb-0">Leave blank if you do not want to change your password.</p>
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
                                <input type="password" name="new_password" class="form-control @error('new_password') is-invalid @enderror" minlength="8">
                                @error('new_password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Confirm New Password</label>
                                <input type="password" name="new_password_confirmation" class="form-control">
                            </div>
                        </div>

                        <div class="d-flex justify-content-end mt-4">
                            <button type="submit" class="btn btn-brand rounded-pill px-4">
                                <i class="bi bi-save me-2"></i>Update Settings
                            </button>
                        </div>
                    </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card settings-card">
                <div class="card-header bg-transparent border-0 pb-0">
                    <h5 class="mb-0"><i class="bi bi-shield-check me-2"></i>Security Tips</h5>
                </div>
                <div class="card-body d-grid gap-2">
                    <div class="tip-item"><i class="bi bi-key"></i><span>Use a strong password with at least 8 characters.</span></div>
                    <div class="tip-item"><i class="bi bi-envelope-check"></i><span>Keep your email active and secure for account recovery.</span></div>
                    <div class="tip-item"><i class="bi bi-bell"></i><span>Review notifications frequently for system updates.</span></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .settings-shell {
        background: linear-gradient(180deg, #ffffff 0%, #fbfdfc 100%);
        border: 1px solid rgba(2,8,20,.08);
        border-radius: 1.25rem;
        box-shadow: 0 10px 26px rgba(2,8,20,.06);
        padding: 1.25rem;
    }
    .settings-summary {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: .75rem;
    }
    .settings-summary-item {
        border: 1px solid rgba(20,83,45,.16);
        background: linear-gradient(180deg, rgba(167,243,208,.18), rgba(255,255,255,.85));
        border-radius: .9rem;
        padding: .7rem .8rem;
    }
    .settings-summary-label {
        font-size: .72rem;
        text-transform: uppercase;
        letter-spacing: .05em;
        color: rgba(2,8,20,.55);
        font-weight: 700;
        margin-bottom: .2rem;
    }
    .settings-summary-value {
        font-size: .94rem;
        font-weight: 700;
        color: #14532d;
    }
    .settings-card {
        border: 1px solid rgba(2,8,20,.08);
        border-radius: 1rem;
        box-shadow: 0 14px 30px rgba(2,8,20,.07);
    }
    .profile-form-wrap {
        max-width: 760px;
    }
    .photo-panel {
        border: 1px solid rgba(2,8,20,.08);
        border-radius: .85rem;
        background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
        padding: .7rem;
    }
    .upload-control {
        display: flex;
        align-items: center;
        gap: .55rem;
        flex-wrap: wrap;
    }
    .upload-trigger {
        border-radius: 999px;
        padding: .35rem .8rem;
    }
    .upload-file-name {
        display: inline-flex;
        align-items: center;
        min-height: 36px;
        border: 1px solid rgba(2,8,20,.1);
        border-radius: .7rem;
        padding: .35rem .6rem;
        font-size: .84rem;
        color: #475569;
        background: rgba(255,255,255,.85);
    }
    .tip-item {
        display: grid;
        grid-template-columns: 24px 1fr;
        gap: .55rem;
        border: 1px solid rgba(22,101,52,.14);
        border-radius: .75rem;
        background: linear-gradient(180deg, rgba(167,243,208,.16), #ffffff);
        padding: .55rem .6rem;
        font-size: .88rem;
        color: #0f172a;
    }
    .tip-item i {
        color: #166534;
        margin-top: .1rem;
    }
    @media (max-width: 991.98px) {
        .settings-summary {
            grid-template-columns: 1fr;
        }
        .settings-shell {
            padding: .95rem;
        }
        .profile-form-wrap {
            max-width: 100%;
        }
    }
    @media (max-width: 575.98px) {
        .photo-panel {
            flex-direction: column;
            align-items: flex-start !important;
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
        const fileName = document.getElementById('profile_file_name');
        const initialFileLabel = fileName ? fileName.textContent : 'No file selected';
        if (!input || !preview) return;

        input.addEventListener('change', function () {
            const file = this.files && this.files[0];
            if (!file) {
                if (fileName) fileName.textContent = initialFileLabel;
                return;
            }

            if (fileName) fileName.textContent = file.name;
            const url = URL.createObjectURL(file);
            preview.src = url;
            preview.classList.remove('d-none');
            if (placeholder) placeholder.classList.add('d-none');
        });
    })();
</script>
@endpush
