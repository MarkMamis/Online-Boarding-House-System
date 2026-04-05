@extends('layouts.student_dashboard')

@section('title', 'Profile')

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
    .profile-main-card .form-label {
        font-weight: 600;
        color: #0f172a;
    }
    .profile-main-card .form-control,
    .profile-main-card .form-select {
        border-color: rgba(2,8,20,.14);
    }
    .profile-photo-panel {
        border: 1px solid rgba(2,8,20,.08);
        border-radius: .85rem;
        background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
        padding: .7rem;
    }
    .profile-form-section {
        border: 1px solid rgba(2,8,20,.1);
        border-radius: .9rem;
        background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
        padding: .9rem;
    }
    .password-wrap {
        border-top: 1px dashed rgba(2,8,20,.15);
        margin-top: 1rem;
        padding-top: 1rem;
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
    .security-tip-item {
        display: grid;
        grid-template-columns: 28px minmax(0, 1fr);
        align-items: start;
        gap: .55rem;
        border: 1px solid rgba(22,101,52,.14);
        border-radius: .75rem;
        background: linear-gradient(180deg, rgba(167,243,208,.16), #ffffff);
        padding: .55rem .6rem;
        margin-bottom: .55rem;
    }
    .security-tip-item:last-child {
        margin-bottom: 0;
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
    .academic-info-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: .55rem;
    }
    .academic-info-item {
        border: 1px solid rgba(2,8,20,.08);
        border-radius: .75rem;
        background: #fff;
        padding: .55rem .6rem;
    }
    .academic-info-label {
        font-size: .68rem;
        text-transform: uppercase;
        letter-spacing: .05em;
        color: #64748b;
        font-weight: 700;
    }
    .academic-info-value {
        font-size: .85rem;
        font-weight: 700;
        color: #0f172a;
        word-break: break-word;
    }
    .academic-doc-list {
        display: flex;
        flex-wrap: wrap;
        gap: .45rem;
    }
    @media (max-width: 991.98px) {
        .profile-summary {
            grid-template-columns: 1fr;
        }
        .profile-edit-shell {
            padding: .95rem;
        }
        .academic-info-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')
<div class="profile-edit-shell">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <div class="text-uppercase small text-muted fw-semibold">Account Settings</div>
            <h1 class="h3 mb-1">Profile Settings</h1>
            <p class="text-muted mb-0">Update your profile information, photo, and account security.</p>
        </div>
    </div>

    <div class="profile-summary mb-4">
        <div class="profile-summary-item">
            <div class="profile-summary-label">Account Type</div>
            <div class="profile-summary-value">Student</div>
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
        <div class="alert alert-success rounded-4">
            <i class="bi bi-check-circle me-1"></i>{{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger rounded-4">
            <i class="bi bi-exclamation-circle me-1"></i>{{ session('error') }}
        </div>
    @endif

    @php
        $academicVerificationStatus = (string) ($user->school_id_verification_status ?? '');
        if ($academicVerificationStatus === '') {
            $hasAcademicDocuments = filled($user->school_id_path) || filled($user->enrollment_proof_path);
            $academicVerificationStatus = $hasAcademicDocuments ? 'pending' : 'not_submitted';
        }

        $academicStatusBadgeClass = match ($academicVerificationStatus) {
            'approved' => 'text-bg-success',
            'rejected' => 'text-bg-danger',
            'pending' => 'text-bg-warning',
            default => 'text-bg-secondary',
        };

        $academicStatusLabel = ucwords(str_replace('_', ' ', $academicVerificationStatus));
        $enrollmentProofLabel = strtoupper((string) ($user->enrollment_proof_type ?? 'COR/COE'));
    @endphp

    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card bg-white profile-main-card mb-3">
                <div class="card-header bg-transparent border-0 pt-3 pb-0 px-3">
                    <h5 class="mb-0"><i class="bi bi-sliders me-2"></i>Profile Details</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('student.profile.update') }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="row g-4">
                            <div class="col-12">
                                <div class="profile-form-section">
                                    <h6 class="fw-semibold mb-3"><i class="bi bi-person me-2"></i>Personal Information</h6>

                                    <div class="row g-3">
                                        <div class="col-12">
                                            <label class="form-label">Profile Photo</label>
                                            <div class="d-flex align-items-center gap-3 profile-photo-panel">
                                                @if(!empty($user->profile_image_path))
                                                    <img id="profile_image_preview" src="{{ asset('storage/' . $user->profile_image_path) }}" alt="Profile photo" class="rounded-circle border" style="width:84px;height:84px;object-fit:cover;">
                                                @else
                                                    <img id="profile_image_preview" src="" alt="Profile photo" class="rounded-circle border d-none" style="width:84px;height:84px;object-fit:cover;">
                                                    <div id="profile_image_placeholder" class="rounded-circle border bg-light d-flex align-items-center justify-content-center" style="width:84px;height:84px;">
                                                        <i class="bi bi-person text-muted"></i>
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
                                            <input type="text" name="full_name" value="{{ old('full_name', $user->full_name) }}" class="form-control @error('full_name') is-invalid @enderror" required>
                                            @error('full_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label">Email <span class="text-danger">*</span></label>
                                            <input type="email" name="email" value="{{ old('email', $user->email) }}" class="form-control @error('email') is-invalid @enderror" required>
                                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label">Contact Number</label>
                                            <input type="text" name="contact_number" value="{{ old('contact_number', $user->contact_number) }}" class="form-control @error('contact_number') is-invalid @enderror">
                                            @error('contact_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label">Birth Date</label>
                                            <input type="date" name="birth_date" value="{{ old('birth_date', optional($user->birth_date)->format('Y-m-d')) }}" class="form-control @error('birth_date') is-invalid @enderror">
                                            @error('birth_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>

                                        <div class="col-12">
                                            <label class="form-label">Address</label>
                                            <textarea name="address" rows="2" class="form-control @error('address') is-invalid @enderror">{{ old('address', $user->address) }}</textarea>
                                            @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="profile-form-section">
                                    <h6 class="fw-semibold mb-3"><i class="bi bi-mortarboard me-2"></i>Academic Information</h6>

                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Student ID</label>
                                            <input type="text" name="student_id" value="{{ old('student_id', $user->student_id) }}" class="form-control @error('student_id') is-invalid @enderror">
                                            @error('student_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label">Course</label>
                                            <input type="text" name="course" value="{{ old('course', $user->course) }}" class="form-control @error('course') is-invalid @enderror">
                                            @error('course')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label">Year Level</label>
                                            <input type="text" name="year_level" value="{{ old('year_level', $user->year_level) }}" class="form-control @error('year_level') is-invalid @enderror">
                                            @error('year_level')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label">Gender</label>
                                            @php
                                                $storedGender = trim((string) ($user->gender ?? ''));
                                                $normalizedStoredGender = strtolower($storedGender);
                                                $allowedGenderValues = ['male', 'female', 'other', 'rather not say'];

                                                $selectedGender = old(
                                                    'gender',
                                                    in_array($normalizedStoredGender, $allowedGenderValues, true)
                                                        ? match ($normalizedStoredGender) {
                                                            'male' => 'Male',
                                                            'female' => 'Female',
                                                            'other' => 'Other',
                                                            default => 'Rather not say',
                                                        }
                                                        : (filled($storedGender) ? 'Other' : '')
                                                );

                                                $defaultCustomGender = old(
                                                    'gender_custom',
                                                    (!in_array($normalizedStoredGender, ['male', 'female', 'rather not say'], true) && filled($storedGender))
                                                        ? $storedGender
                                                        : ''
                                                );
                                            @endphp

                                            <select id="gender" name="gender" class="form-select @error('gender') is-invalid @enderror">
                                                <option value="">Select gender</option>
                                                <option value="Male" @selected($selectedGender === 'Male')>Male</option>
                                                <option value="Female" @selected($selectedGender === 'Female')>Female</option>
                                                <option value="Other" @selected($selectedGender === 'Other')>Other</option>
                                                <option value="Rather not say" @selected($selectedGender === 'Rather not say')>Rather not say</option>
                                            </select>
                                            @error('gender')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>

                                        <div class="col-md-6" id="gender_custom_wrap" style="display: none;">
                                            <label class="form-label">Specify Gender</label>
                                            <input type="text" id="gender_custom" name="gender_custom" value="{{ $defaultCustomGender }}" class="form-control @error('gender_custom') is-invalid @enderror" maxlength="100" placeholder="Please specify">
                                            @error('gender_custom')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="profile-form-section">
                                    <h6 class="fw-semibold mb-3"><i class="bi bi-telephone me-2"></i>Emergency and Parent Contacts</h6>

                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Emergency Contact Name</label>
                                            <input type="text" name="emergency_contact_name" value="{{ old('emergency_contact_name', $user->emergency_contact_name) }}" class="form-control @error('emergency_contact_name') is-invalid @enderror">
                                            @error('emergency_contact_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label">Emergency Contact Number</label>
                                            <input type="text" name="emergency_contact_number" value="{{ old('emergency_contact_number', $user->emergency_contact_number) }}" class="form-control @error('emergency_contact_number') is-invalid @enderror">
                                            @error('emergency_contact_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label">Emergency Contact Relationship</label>
                                            <input type="text" name="emergency_contact_relationship" value="{{ old('emergency_contact_relationship', $user->emergency_contact_relationship) }}" class="form-control @error('emergency_contact_relationship') is-invalid @enderror" placeholder="e.g. Mother, Father, Guardian">
                                            @error('emergency_contact_relationship')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label">Parent Contact Name</label>
                                            <input type="text" name="parent_contact_name" value="{{ old('parent_contact_name', $user->parent_contact_name) }}" class="form-control @error('parent_contact_name') is-invalid @enderror" placeholder="e.g. Maria Santos">
                                            @error('parent_contact_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label">Parent Contact Number</label>
                                            <input type="text" name="parent_contact_number" value="{{ old('parent_contact_number', $user->parent_contact_number) }}" class="form-control @error('parent_contact_number') is-invalid @enderror" placeholder="e.g. +63 9xx xxx xxxx">
                                            @error('parent_contact_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>

                                        <div class="col-12">
                                            <label class="form-label">Parent/Guardian Address</label>
                                            <textarea name="parent_contact_address" rows="2" class="form-control @error('parent_contact_address') is-invalid @enderror" placeholder="Complete home address of parent/guardian for emergency response">{{ old('parent_contact_address', $user->parent_contact_address) }}</textarea>
                                            @error('parent_contact_address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>

                                        <div class="col-12">
                                            <label class="form-label">Parent/Guardian ID or Photo (Optional)</label>
                                            <div class="d-flex align-items-center gap-3 profile-photo-panel">
                                                @if(!empty($user->parent_contact_photo_path))
                                                    <img id="parent_contact_photo_preview" src="{{ asset('storage/' . $user->parent_contact_photo_path) }}" alt="Parent or guardian photo" class="rounded border" style="width:84px;height:84px;object-fit:cover;">
                                                @else
                                                    <img id="parent_contact_photo_preview" src="" alt="Parent or guardian photo" class="rounded border d-none" style="width:84px;height:84px;object-fit:cover;">
                                                    <div id="parent_contact_photo_placeholder" class="rounded border bg-light d-flex align-items-center justify-content-center" style="width:84px;height:84px;">
                                                        <i class="bi bi-person-vcard text-muted"></i>
                                                    </div>
                                                @endif
                                                <div class="grow">
                                                    <input id="parent_contact_photo_input" type="file" name="parent_contact_photo" class="form-control @error('parent_contact_photo') is-invalid @enderror" accept="image/*">
                                                    @error('parent_contact_photo')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                    <div class="form-text">JPG, PNG, WEBP, GIF (max 3MB)</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="profile-form-section">
                                    <h6 class="fw-semibold mb-3"><i class="bi bi-heart-pulse me-2"></i>Medical Information</h6>

                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Blood Type</label>
                                            <input type="text" name="blood_type" value="{{ old('blood_type', $user->blood_type) }}" class="form-control @error('blood_type') is-invalid @enderror" placeholder="e.g. O+">
                                            @error('blood_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end mt-4 action-bar">
                            <button type="submit" class="btn btn-brand rounded-pill px-4">
                                <i class="bi bi-save me-1"></i>Update Profile
                            </button>
                        </div>
                    </form>

                    <div class="password-wrap">
                        <h6 class="fw-semibold mb-2"><i class="bi bi-shield-lock me-2"></i>Change Password</h6>
                        <p class="text-muted small mb-3">Update your password to keep your account secure.</p>

                        <form method="POST" action="{{ route('student.profile.update-password') }}" class="row g-3">
                            @csrf
                            @method('PUT')

                            <div class="col-md-4">
                                <label class="form-label">Current Password</label>
                                <input type="password" name="current_password" class="form-control @error('current_password') is-invalid @enderror" required>
                                @error('current_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">New Password</label>
                                <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" minlength="8" required>
                                @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Confirm New Password</label>
                                <input type="password" name="password_confirmation" class="form-control" minlength="8" required>
                            </div>

                            <div class="col-12 d-flex justify-content-end">
                                <button type="submit" class="btn btn-outline-secondary rounded-pill px-4">
                                    <i class="bi bi-key me-1"></i>Change Password
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card bg-white profile-side-card">
                <div class="card-header bg-transparent border-0 pb-0">
                    <h5 class="mb-0"><i class="bi bi-mortarboard me-2"></i>Academic Information</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between gap-2 mb-3">
                        <span class="small text-muted">Verification Status</span>
                        <span class="badge rounded-pill {{ $academicStatusBadgeClass }}">{{ $academicStatusLabel }}</span>
                    </div>

                    @if($academicVerificationStatus === 'rejected' && !empty($user->school_id_rejection_reason))
                        <div class="alert alert-danger py-2 px-3 small mb-3">{{ $user->school_id_rejection_reason }}</div>
                    @endif

                    <div class="academic-info-grid mb-3">
                        <div class="academic-info-item">
                            <div class="academic-info-label">Student ID</div>
                            <div class="academic-info-value">{{ $user->student_id ?: 'Not provided' }}</div>
                        </div>
                        <div class="academic-info-item">
                            <div class="academic-info-label">Year Level</div>
                            <div class="academic-info-value">{{ $user->year_level ?: 'Not provided' }}</div>
                        </div>
                        <div class="academic-info-item">
                            <div class="academic-info-label">Course</div>
                            <div class="academic-info-value">{{ $user->course ?: 'Not provided' }}</div>
                        </div>
                        <div class="academic-info-item">
                            <div class="academic-info-label">Gender</div>
                            <div class="academic-info-value">{{ $user->gender ?: 'Not specified' }}</div>
                        </div>
                    </div>

                    <div class="small text-muted fw-semibold mb-2">Uploaded Academic Documents</div>
                    <div class="academic-doc-list">
                        @if(!empty($user->school_id_path))
                            <a href="{{ asset('storage/' . $user->school_id_path) }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary rounded-pill">View School ID</a>
                        @endif

                        @if(!empty($user->enrollment_proof_path))
                            <a href="{{ asset('storage/' . $user->enrollment_proof_path) }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary rounded-pill">View {{ $enrollmentProofLabel }}</a>
                        @endif

                        @if(empty($user->school_id_path) && empty($user->enrollment_proof_path))
                            <span class="small text-muted">No academic documents uploaded yet.</span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="card bg-white profile-side-card mt-3">
                <div class="card-header bg-transparent border-0 pb-0">
                    <h5 class="mb-0"><i class="bi bi-shield-check me-2"></i>Security Tips</h5>
                </div>
                <div class="card-body">
                    <div class="security-tip-item">
                        <span class="security-tip-icon"><i class="bi bi-key"></i></span>
                        <span class="security-tip-text">Use a strong password with at least 8 characters.</span>
                    </div>
                    <div class="security-tip-item">
                        <span class="security-tip-icon"><i class="bi bi-telephone"></i></span>
                        <span class="security-tip-text">Keep contact and emergency details updated.</span>
                    </div>
                    <div class="security-tip-item">
                        <span class="security-tip-icon"><i class="bi bi-person-check"></i></span>
                        <span class="security-tip-text">Use your real identity details for onboarding verification.</span>
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
            if (!file || !file.type || !file.type.startsWith('image/')) return;
            const url = URL.createObjectURL(file);
            preview.src = url;
            preview.classList.remove('d-none');
            if (placeholder) placeholder.classList.add('d-none');
        });
    })();

    (function () {
        const input = document.getElementById('parent_contact_photo_input');
        const preview = document.getElementById('parent_contact_photo_preview');
        const placeholder = document.getElementById('parent_contact_photo_placeholder');
        if (!input || !preview) return;

        input.addEventListener('change', function () {
            const file = input.files && input.files[0];
            if (!file || !file.type || !file.type.startsWith('image/')) return;
            const url = URL.createObjectURL(file);
            preview.src = url;
            preview.classList.remove('d-none');
            if (placeholder) placeholder.classList.add('d-none');
        });
    })();

    (function () {
        const genderSelect = document.getElementById('gender');
        const customWrap = document.getElementById('gender_custom_wrap');
        const customInput = document.getElementById('gender_custom');
        if (!genderSelect || !customWrap || !customInput) return;

        const syncGenderInput = function () {
            const isOther = genderSelect.value === 'Other';
            customWrap.style.display = isOther ? '' : 'none';
            customInput.required = isOther;
            if (!isOther) {
                customInput.value = '';
            }
        };

        genderSelect.addEventListener('change', syncGenderInput);
        syncGenderInput();
    })();
</script>
@endpush
