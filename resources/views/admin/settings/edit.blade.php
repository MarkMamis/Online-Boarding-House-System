@extends('layouts.admin')

@section('title', 'Admin Settings')

@section('content')
<div class="admin-page-shell">
    <x-admin.page-header
        title="Admin Settings"
        subtitle="Manage your administrator account details, security credentials, and profile image."
        icon="bi bi-gear"
        :breadcrumbs="[
            ['label' => 'System', 'url' => route('admin.users.index')],
            ['label' => 'Settings']
        ]"
    />

    <div class="row g-3 mb-4">
        <div class="col-12 col-md-4">
            <x-admin.metric-card
                label="Account Type"
                value="Administrator"
                icon="bi bi-shield-lock"
                variant="brand"
                subtext="Full system privileges"
            />
        </div>
        <div class="col-12 col-md-4">
            <x-admin.metric-card
                label="Member Since"
                :value="$user->created_at->format('M d, Y')"
                icon="bi bi-calendar-check"
                variant="neutral"
                subtext="Account registration date"
            />
        </div>
        <div class="col-12 col-md-4">
            <x-admin.metric-card
                label="Account Status"
                value="Active"
                icon="bi bi-check-circle"
                variant="success"
                subtext="In good standing"
            />
        </div>
    </div>

    @if($errors->any())
        <div class="alert alert-danger border-0 shadow-sm mb-4">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger border-0 shadow-sm mb-4">{{ session('error') }}</div>
    @endif

    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm mb-4">{{ session('success') }}</div>
    @endif

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="admin-table-card p-4">
                <h5 class="fw-bold mb-3 text-dark"><i class="bi bi-person me-2 text-success"></i>Profile Information</h5>
                <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold small text-muted">Profile Photo</label>
                            <div class="d-flex align-items-center gap-3 p-3 bg-light rounded-4 border">
                                @if(!empty($user->profile_image_path))
                                    <img id="profile_image_preview" src="{{ file_url($user->profile_image_path) }}" alt="Profile photo" class="rounded-circle border shadow-sm" style="width:72px;height:72px;object-fit:cover;">
                                @else
                                    <img id="profile_image_preview" src="" alt="Profile photo" class="rounded-circle border d-none shadow-sm" style="width:72px;height:72px;object-fit:cover;">
                                    <div id="profile_image_placeholder" class="rounded-circle border bg-white d-flex align-items-center justify-content-center shadow-sm" style="width:72px;height:72px;">
                                        <i class="bi bi-person fs-3 text-muted"></i>
                                    </div>
                                @endif
                                <div>
                                    <input id="profile_image_input" type="file" name="profile_image" class="d-none @error('profile_image') is-invalid @enderror" accept="image/*">
                                    <div class="d-flex align-items-center gap-2 flex-wrap">
                                        <label for="profile_image_input" class="btn btn-sm btn-outline-brand rounded-pill mb-0">
                                            <i class="bi bi-upload me-1"></i>Choose Photo
                                        </label>
                                        <span id="profile_file_name" class="small text-muted">{{ !empty($user->profile_image_path) ? 'Current photo retained' : 'No file selected' }}</span>
                                    </div>
                                    @error('profile_image')
                                        <div class="small text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text small">JPG, PNG, WEBP, GIF (max 2MB)</div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-muted">Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="full_name" value="{{ old('full_name', $user->full_name) }}" class="form-control rounded-3 @error('full_name') is-invalid @enderror" required>
                            @error('full_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-muted">Email Address <span class="text-danger">*</span></label>
                            <input type="email" name="email" value="{{ old('email', $user->email) }}" class="form-control rounded-3 @error('email') is-invalid @enderror" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-muted">Contact Number</label>
                            <input type="text" name="contact_number" value="{{ old('contact_number', $user->contact_number) }}" class="form-control rounded-3 @error('contact_number') is-invalid @enderror" placeholder="e.g. +63 912 345 6789">
                            @error('contact_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <hr class="my-3">
                            <h6 class="fw-bold mb-1 text-dark"><i class="bi bi-shield-lock me-2 text-success"></i>Change Password (Optional)</h6>
                            <p class="text-muted small mb-3">Leave blank if you do not want to update your password.</p>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-muted">Current Password</label>
                            <input type="password" name="current_password" class="form-control rounded-3 @error('current_password') is-invalid @enderror">
                            @error('current_password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-muted">New Password</label>
                            <input type="password" name="new_password" class="form-control rounded-3 @error('new_password') is-invalid @enderror" minlength="8">
                            @error('new_password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-muted">Confirm New Password</label>
                            <input type="password" name="new_password_confirmation" class="form-control rounded-3">
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

        <div class="col-lg-4">
            <div class="admin-table-card p-4">
                <h5 class="fw-bold mb-3 text-dark"><i class="bi bi-shield-check me-2 text-success"></i>Security Tips</h5>
                <div class="d-flex flex-column gap-3">
                    <div class="p-3 bg-light rounded-3 border">
                        <div class="d-flex align-items-center gap-2 mb-1 fw-semibold text-dark">
                            <i class="bi bi-key text-success"></i> Strong Passwords
                        </div>
                        <div class="small text-muted">Use a combination of uppercase, lowercase, numbers, and special characters with at least 8 characters.</div>
                    </div>
                    <div class="p-3 bg-light rounded-3 border">
                        <div class="d-flex align-items-center gap-2 mb-1 fw-semibold text-dark">
                            <i class="bi bi-envelope-check text-success"></i> Email Security
                        </div>
                        <div class="small text-muted">Keep your registered administrator email account active and secured for recovery verification.</div>
                    </div>
                    <div class="p-3 bg-light rounded-3 border">
                        <div class="d-flex align-items-center gap-2 mb-1 fw-semibold text-dark">
                            <i class="bi bi-bell text-success"></i> Audit Logs & Alerts
                        </div>
                        <div class="small text-muted">Regularly inspect admin notifications and user management logs for unexpected actions.</div>
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
