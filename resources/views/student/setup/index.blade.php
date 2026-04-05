<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Verification Setup</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <style>
        :root {
            --brand: #14532d;
            --brand-dark: #166534;
            --ink: #0f172a;
            --line: #e2e8f0;
            --shell: #f8fafc;
            --step-idle: #cbd5e1;
            --step-active: #14532d;
            --step-done: #16a34a;
        }

        body {
            background:
                radial-gradient(520px 220px at 10% -10%, rgba(22, 163, 74, .18), transparent 60%),
                radial-gradient(680px 260px at 110% -20%, rgba(14, 116, 144, .12), transparent 62%),
                var(--shell);
            color: var(--ink);
            min-height: 100vh;
        }

        .setup-wrap {
            max-width: 1020px;
            margin: 0 auto;
            padding: 1.1rem;
        }

        .setup-shell {
            border: 1px solid var(--line);
            border-radius: 1.2rem;
            background: rgba(255, 255, 255, .95);
            box-shadow: 0 14px 34px rgba(2, 8, 20, .08);
            overflow: hidden;
        }

        .setup-head {
            border-bottom: 1px solid var(--line);
            padding: 1.05rem 1.1rem;
            background: linear-gradient(180deg, rgba(240, 253, 244, .78), rgba(255, 255, 255, .96));
        }

        .setup-head h1 {
            letter-spacing: .01em;
        }

        .setup-chip {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            border: 1px solid rgba(20, 83, 45, .2);
            border-radius: 999px;
            background: rgba(167, 243, 208, .28);
            color: #14532d;
            padding: .25rem .6rem;
            font-size: .75rem;
            font-weight: 700;
        }

        .setup-grid {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 340px;
            gap: 1rem;
        }

        .setup-main {
            padding: 1rem;
        }

        .setup-side {
            border-left: 1px solid var(--line);
            background: #fbfdfc;
            padding: 1rem;
        }

        .check-item {
            border: 1px solid var(--line);
            border-radius: .85rem;
            background: #fff;
            padding: .7rem .75rem;
        }

        .check-title {
            font-size: .86rem;
            font-weight: 700;
            margin-bottom: .1rem;
        }

        .check-copy {
            font-size: .78rem;
            color: #64748b;
            margin-bottom: 0;
        }

        .stepper-shell {
            border: 1px solid var(--line);
            border-radius: .95rem;
            background: linear-gradient(180deg, #ffffff, #f8fafc);
            padding: .8rem .9rem;
            margin-bottom: .9rem;
        }

        .stepper-track {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: .5rem;
            align-items: center;
        }

        .step-chip {
            position: relative;
            border: 1px solid rgba(2, 8, 20, .1);
            border-radius: .85rem;
            background: #fff;
            padding: .55rem .6rem;
            display: flex;
            align-items: center;
            gap: .5rem;
            transition: border-color .2s ease, background-color .2s ease, box-shadow .2s ease;
        }

        .step-chip::after {
            content: '';
            position: absolute;
            top: 50%;
            right: -.42rem;
            width: .42rem;
            height: 2px;
            background: var(--step-idle);
            transform: translateY(-50%);
        }

        .step-chip:last-child::after {
            display: none;
        }

        .step-badge {
            width: 26px;
            height: 26px;
            border-radius: 50%;
            border: 2px solid var(--step-idle);
            color: #64748b;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: .76rem;
            font-weight: 700;
            background: #fff;
            flex: 0 0 auto;
        }

        .step-copy {
            min-width: 0;
        }

        .step-label {
            font-size: .73rem;
            text-transform: uppercase;
            letter-spacing: .05em;
            color: #64748b;
            margin-bottom: .08rem;
            line-height: 1.2;
            font-weight: 700;
        }

        .step-title {
            font-size: .82rem;
            color: #0f172a;
            line-height: 1.2;
            font-weight: 600;
        }

        .step-chip.active {
            border-color: rgba(20, 83, 45, .3);
            background: rgba(240, 253, 244, .8);
            box-shadow: 0 0 0 .2rem rgba(20, 83, 45, .08);
        }

        .step-chip.active .step-badge {
            border-color: var(--step-active);
            color: var(--step-active);
        }

        .step-chip.active .step-label {
            color: #14532d;
        }

        .step-chip.done {
            border-color: rgba(22, 163, 74, .32);
            background: rgba(220, 252, 231, .68);
        }

        .step-chip.done .step-badge {
            border-color: var(--step-done);
            color: #fff;
            background: var(--step-done);
        }

        .step-chip.done::after {
            background: var(--step-done);
        }

        .step-status-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: .5rem;
            margin-top: .6rem;
        }

        .step-status-text {
            font-size: .78rem;
            color: #64748b;
        }

        .step-panel {
            display: none;
        }

        .step-panel.active {
            display: block;
        }

        .step-panel-enter {
            animation: stepFade .24s ease;
        }

        @keyframes stepFade {
            from {
                opacity: 0;
                transform: translateY(4px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .progress {
            height: .56rem;
            border-radius: 999px;
            background: #e2e8f0;
        }

        .progress-bar {
            background: linear-gradient(90deg, #14532d, #16a34a);
        }

        .section-card {
            border: 1px solid var(--line);
            border-radius: .9rem;
            background: #fff;
            padding: .85rem;
            margin-bottom: .8rem;
        }

        .section-title {
            font-size: .86rem;
            font-weight: 700;
            margin-bottom: .2rem;
        }

        .section-sub {
            font-size: .78rem;
            color: #64748b;
            margin-bottom: .65rem;
        }

        .form-label {
            font-weight: 600;
            font-size: .84rem;
            color: #334155;
            margin-bottom: .35rem;
        }

        .form-control,
        .form-select {
            border-color: var(--line);
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #14532d;
            box-shadow: 0 0 0 .2rem rgba(20, 83, 45, .12);
        }

        .upload-preview {
            width: 78px;
            height: 78px;
            border-radius: .75rem;
            border: 1px solid rgba(2, 8, 20, .1);
            object-fit: cover;
            background: #f8fafc;
        }

        .step-actions {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .6rem;
            margin-top: .25rem;
        }

        .step-actions-right {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
        }

        .step-actions-left {
            min-width: 90px;
        }

        .missing-list {
            margin: 0;
            padding-left: 1.1rem;
            font-size: .8rem;
            color: #b91c1c;
        }

        .btn-brand {
            background: var(--brand);
            border-color: var(--brand);
            color: #fff;
        }

        .btn-brand:hover {
            background: var(--brand-dark);
            border-color: var(--brand-dark);
            color: #fff;
        }

        .btn-next,
        .btn-prev {
            border-radius: 999px;
            min-width: 116px;
        }

        .helper-chip {
            display: inline-flex;
            align-items: center;
            gap: .32rem;
            border-radius: 999px;
            border: 1px solid rgba(2, 8, 20, .12);
            padding: .22rem .56rem;
            font-size: .74rem;
            background: #fff;
            color: #475569;
        }

        @media (max-width: 991.98px) {
            .setup-grid {
                grid-template-columns: 1fr;
            }

            .setup-side {
                border-left: 0;
                border-top: 1px solid var(--line);
            }

            .stepper-track {
                grid-template-columns: 1fr;
            }

            .step-chip::after {
                display: none;
            }
        }

        @media (max-width: 575.98px) {
            .setup-wrap {
                padding: .8rem;
            }

            .setup-main,
            .setup-side {
                padding: .8rem;
            }

            .step-actions {
                flex-wrap: wrap;
            }

            .step-actions-left,
            .step-actions-right {
                width: 100%;
                justify-content: space-between;
            }

            .btn-next,
            .btn-prev {
                min-width: 0;
                width: 49%;
            }
        }
    </style>
</head>
<body>
    <div class="setup-wrap">
        <div class="d-flex align-items-center justify-content-between mb-3 px-1">
            <div class="setup-chip"><i class="bi bi-shield-check"></i>Student Verification Setup</div>
            <form method="POST" action="{{ route('logout') }}" class="m-0">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-secondary rounded-pill px-3">Logout</button>
            </form>
        </div>

        <div class="setup-shell">
            <div class="setup-head">
                <h1 class="h4 mb-1">Complete your student setup</h1>
                <div class="text-muted small">Flow: account creation -> email verification -> student setup -> full portal unlock.</div>
            </div>

            <div class="setup-grid">
                <div class="setup-main">
                    @if(session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger">
                            <div class="fw-semibold mb-1">Please fix the following:</div>
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="stepper-shell">
                        <div class="stepper-track" id="setupStepper">
                            <div class="step-chip active" data-step-nav="0">
                                <span class="step-badge">1</span>
                                <div class="step-copy">
                                    <div class="step-label">Step 1</div>
                                    <div class="step-title">Personal Profile</div>
                                </div>
                            </div>
                            <div class="step-chip" data-step-nav="1">
                                <span class="step-badge">2</span>
                                <div class="step-copy">
                                    <div class="step-label">Step 2</div>
                                    <div class="step-title">Academic Identity</div>
                                </div>
                            </div>
                            <div class="step-chip" data-step-nav="2">
                                <span class="step-badge">3</span>
                                <div class="step-copy">
                                    <div class="step-label">Step 3</div>
                                    <div class="step-title">Emergency Contacts</div>
                                </div>
                            </div>
                        </div>
                        <div class="step-status-row">
                            <span class="step-status-text" id="stepStatusText">Step 1 of 3</span>
                            <span class="helper-chip"><i class="bi bi-info-circle"></i>Complete all required fields to unlock portal access</span>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('student.setup.update') }}" enctype="multipart/form-data" id="studentSetupForm">
                        @csrf
                        @method('PUT')

                        @php
                            $selectedGender = old('gender', in_array($user->gender, ['Male', 'Female', 'Other', 'Rather not say']) ? $user->gender : (filled($user->gender) ? 'Other' : ''));
                            $selectedYearLevel = old('year_level', $user->year_level);
                            $isFirstYearSelected = $selectedYearLevel === '1st Year';
                            $selectedEnrollmentProofType = old('enrollment_proof_type', $user->enrollment_proof_type);
                            $hasEnrollmentProof = !empty($user->enrollment_proof_path);
                        @endphp

                        <section class="step-panel active" data-step="0">
                            <div class="section-card step-panel-enter">
                                <div class="section-title">Personal Information</div>
                                <div class="section-sub">Tell us who you are. This verifies your student identity profile.</div>

                                <div class="row g-3">
                                    <div class="col-12 col-md-6">
                                        <label class="form-label">Full Name</label>
                                        <input type="text" name="full_name" class="form-control @error('full_name') is-invalid @enderror" value="{{ old('full_name', $user->full_name) }}" required>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <label class="form-label">Contact Number</label>
                                        <input type="text" id="contact_number" name="contact_number" class="form-control ph-contact-number @error('contact_number') is-invalid @enderror" value="{{ old('contact_number', $user->contact_number) }}" inputmode="numeric" pattern="^09\d{9}$" maxlength="11" placeholder="09XXXXXXXXX" title="Use 11-digit PH mobile format: 09XXXXXXXXX" required>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <label class="form-label">Birth Date (Optional)</label>
                                        <input type="date" name="birth_date" class="form-control @error('birth_date') is-invalid @enderror" value="{{ old('birth_date', optional($user->birth_date)->format('Y-m-d')) }}">
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <label class="form-label">Gender</label>
                                        <select name="gender" id="gender" class="form-select @error('gender') is-invalid @enderror" required>
                                            <option value="" disabled {{ $selectedGender === '' ? 'selected' : '' }}>Select gender</option>
                                            <option value="Male" {{ $selectedGender === 'Male' ? 'selected' : '' }}>Male</option>
                                            <option value="Female" {{ $selectedGender === 'Female' ? 'selected' : '' }}>Female</option>
                                            <option value="Other" {{ $selectedGender === 'Other' ? 'selected' : '' }}>Other</option>
                                            <option value="Rather not say" {{ $selectedGender === 'Rather not say' ? 'selected' : '' }}>Rather not say</option>
                                        </select>
                                    </div>
                                    <div class="col-12" id="genderCustomWrap" style="display: none;">
                                        <label class="form-label">Specify Gender</label>
                                        <input type="text" name="gender_custom" id="gender_custom" class="form-control @error('gender_custom') is-invalid @enderror" value="{{ old('gender_custom', $selectedGender === 'Other' && !in_array($user->gender, ['Male', 'Female', 'Other', 'Rather not say']) ? $user->gender : '') }}" maxlength="100" placeholder="Please specify">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Home Address</label>
                                        <textarea name="address" rows="2" class="form-control @error('address') is-invalid @enderror" required>{{ old('address', $user->address) }}</textarea>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Profile Photo</label>
                                        <div class="d-flex align-items-center gap-3">
                                            @if(!empty($user->profile_image_path))
                                                <img id="profile_preview" src="{{ asset('storage/' . $user->profile_image_path) }}" alt="Profile" class="upload-preview">
                                            @else
                                                <img id="profile_preview" src="" alt="Profile" class="upload-preview d-none">
                                            @endif
                                            <div class="w-100">
                                                <input type="file" name="profile_image" id="profile_image" class="form-control @error('profile_image') is-invalid @enderror" accept="image/*" {{ empty($user->profile_image_path) ? 'required' : '' }}>
                                                <div class="form-text">Required. JPG, PNG, WEBP, GIF up to 3MB.</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="step-actions">
                                    <div class="step-actions-left"></div>
                                    <div class="step-actions-right">
                                        <button type="button" class="btn btn-brand btn-next" data-next-step="1">Next Step <i class="bi bi-arrow-right ms-1"></i></button>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <section class="step-panel" data-step="1">
                            <div class="section-card step-panel-enter">
                                <div class="section-title">Academic Identity</div>
                                <div class="section-sub">Confirm your academic information so admin and housing records match.</div>
                                <div class="row g-3">
                                    <div class="col-12 col-md-4">
                                        <label class="form-label">Student ID</label>
                                        <input type="text" id="student_id" name="student_id" class="form-control @error('student_id') is-invalid @enderror" value="{{ old('student_id', $user->student_id) }}" placeholder="MCC2026-00000" {{ $isFirstYearSelected ? '' : 'required' }}>
                                        <div id="student_id_help" class="form-text {{ $isFirstYearSelected ? '' : 'd-none' }}">Optional for 1st Year while official ID release is pending.</div>
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <label class="form-label">Course</label>
                                        <input type="text" name="course" class="form-control @error('course') is-invalid @enderror" value="{{ old('course', $user->course) }}" required>
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <label class="form-label">Year Level</label>
                                        <select id="year_level" name="year_level" class="form-select @error('year_level') is-invalid @enderror" required>
                                            <option value="" disabled {{ $selectedYearLevel ? '' : 'selected' }}>Select year</option>
                                            @foreach(['1st Year','2nd Year','3rd Year','4th Year'] as $level)
                                                <option value="{{ $level }}" {{ $selectedYearLevel === $level ? 'selected' : '' }}>{{ $level }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">School ID Upload</label>
                                        <div class="d-flex align-items-center gap-3">
                                            @if(!empty($user->school_id_path))
                                                <img id="school_id_preview" src="{{ asset('storage/' . $user->school_id_path) }}" alt="School ID" class="upload-preview">
                                            @else
                                                <img id="school_id_preview" src="" alt="School ID" class="upload-preview d-none">
                                            @endif
                                            <div class="w-100">
                                                <input type="file" name="school_id_photo" id="school_id_photo" class="form-control @error('school_id_photo') is-invalid @enderror" accept="image/*" data-has-existing="{{ empty($user->school_id_path) ? '0' : '1' }}" {{ ($isFirstYearSelected || !empty($user->school_id_path)) ? '' : 'required' }}>
                                                <div id="school_id_help" class="form-text">
                                                    {{ $isFirstYearSelected ? 'Optional for 1st Year students. If unavailable, upload COR or COE below.' : 'Required for 2nd Year and above. Upload a clear School ID photo (JPG, PNG, WEBP, GIF up to 3MB).' }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <label class="form-label">Enrollment Proof Type (COR or COE)</label>
                                        <select id="enrollment_proof_type" name="enrollment_proof_type" class="form-select @error('enrollment_proof_type') is-invalid @enderror" {{ ($isFirstYearSelected || $hasEnrollmentProof) ? 'required' : '' }}>
                                            <option value="" {{ filled($selectedEnrollmentProofType) ? '' : 'selected' }}>Select proof type</option>
                                            <option value="cor" {{ (string) $selectedEnrollmentProofType === 'cor' ? 'selected' : '' }}>Certificate of Registration (COR)</option>
                                            <option value="coe" {{ (string) $selectedEnrollmentProofType === 'coe' ? 'selected' : '' }}>Certificate of Enrollment (COE)</option>
                                        </select>
                                        <div class="form-text">Choose whichever document you have available.</div>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <label class="form-label">COR / COE Upload</label>
                                        <input type="file" name="enrollment_proof_file" id="enrollment_proof_file" class="form-control @error('enrollment_proof_file') is-invalid @enderror" accept=".pdf,image/*" data-has-existing="{{ $hasEnrollmentProof ? '1' : '0' }}" {{ ($isFirstYearSelected && !$hasEnrollmentProof) ? 'required' : '' }}>
                                        <div id="enrollment_proof_help" class="form-text">
                                            {{ $isFirstYearSelected ? 'Required for 1st Year students. Accepted: PDF, JPG, PNG, WEBP up to 4MB.' : 'Optional for 2nd Year and above. Accepted: PDF, JPG, PNG, WEBP up to 4MB.' }}
                                        </div>
                                        @if($hasEnrollmentProof)
                                            <a href="{{ asset('storage/' . $user->enrollment_proof_path) }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary rounded-pill mt-2">
                                                <i class="bi bi-file-earmark-arrow-down me-1"></i>View current {{ strtoupper((string) ($user->enrollment_proof_type ?? 'proof')) }} file
                                            </a>
                                        @endif
                                    </div>
                                </div>

                                <div class="step-actions">
                                    <div class="step-actions-left">
                                        <button type="button" class="btn btn-outline-secondary btn-prev" data-prev-step="0"><i class="bi bi-arrow-left me-1"></i>Back</button>
                                    </div>
                                    <div class="step-actions-right">
                                        <button type="button" class="btn btn-brand btn-next" data-next-step="2">Next Step <i class="bi bi-arrow-right ms-1"></i></button>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <section class="step-panel" data-step="2">
                            <div class="section-card step-panel-enter">
                                <div class="section-title">Emergency and Parent Contact</div>
                                <div class="section-sub">These details are used for urgent coordination and safety verification.</div>
                                <div class="row g-3">
                                    <div class="col-12">
                                        <div class="fw-semibold small text-uppercase text-muted">Parent / Guardian Details</div>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <label class="form-label">Parent or Guardian Name</label>
                                        <input type="text" name="parent_contact_name" class="form-control @error('parent_contact_name') is-invalid @enderror" value="{{ old('parent_contact_name', $user->parent_contact_name) }}" required>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <label class="form-label">Parent or Guardian Number</label>
                                        <input type="text" name="parent_contact_number" class="form-control ph-contact-number @error('parent_contact_number') is-invalid @enderror" value="{{ old('parent_contact_number', $user->parent_contact_number) }}" inputmode="numeric" pattern="^09\d{9}$" maxlength="11" placeholder="09XXXXXXXXX" title="Use 11-digit PH mobile format: 09XXXXXXXXX" required>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Parent or Guardian Address</label>
                                        <textarea name="parent_contact_address" rows="2" class="form-control @error('parent_contact_address') is-invalid @enderror" required>{{ old('parent_contact_address', $user->parent_contact_address) }}</textarea>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Parent or Guardian Photo (Optional)</label>
                                        <input type="file" name="parent_contact_photo" id="parent_contact_photo" class="form-control @error('parent_contact_photo') is-invalid @enderror" accept="image/*">
                                    </div>

                                    <div class="col-12 pt-1">
                                        <div class="fw-semibold small text-uppercase text-muted">Emergency Contact Details</div>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <label class="form-label">Emergency Contact Name</label>
                                        <input type="text" name="emergency_contact_name" class="form-control @error('emergency_contact_name') is-invalid @enderror" value="{{ old('emergency_contact_name', $user->emergency_contact_name) }}" required>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <label class="form-label">Emergency Contact Number</label>
                                        <input type="text" name="emergency_contact_number" class="form-control ph-contact-number @error('emergency_contact_number') is-invalid @enderror" value="{{ old('emergency_contact_number', $user->emergency_contact_number) }}" inputmode="numeric" pattern="^09\d{9}$" maxlength="11" placeholder="09XXXXXXXXX" title="Use 11-digit PH mobile format: 09XXXXXXXXX" required>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Emergency Contact Relationship</label>
                                        <input type="text" name="emergency_contact_relationship" class="form-control @error('emergency_contact_relationship') is-invalid @enderror" value="{{ old('emergency_contact_relationship', $user->emergency_contact_relationship) }}" required>
                                    </div>
                                </div>

                                <div class="step-actions">
                                    <div class="step-actions-left">
                                        <button type="button" class="btn btn-outline-secondary btn-prev" data-prev-step="1"><i class="bi bi-arrow-left me-1"></i>Back</button>
                                    </div>
                                    <div class="step-actions-right">
                                        <button type="submit" class="btn btn-brand rounded-pill px-4">Complete Setup and Unlock Portal</button>
                                    </div>
                                </div>
                            </div>
                        </section>
                    </form>
                </div>

                <aside class="setup-side">
                    <div class="small text-muted text-uppercase fw-semibold mb-2">Setup Progress</div>
                    @php
                        $progress = (int) round(($completedCount / max(1, $totalCount)) * 100);
                    @endphp
                    <div class="d-flex justify-content-between small mb-1">
                        <span class="text-muted">Completed</span>
                        <span class="fw-semibold">{{ $completedCount }}/{{ $totalCount }}</span>
                    </div>
                    <div class="progress mb-3">
                        <div class="progress-bar" role="progressbar" style="width: {{ $progress }}%;" aria-valuenow="{{ $progress }}" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>

                    <div class="vstack gap-2 mb-3">
                        @foreach($checklist as $item)
                            <div class="check-item">
                                <div class="d-flex align-items-start justify-content-between gap-2">
                                    <div>
                                        <div class="check-title">{{ $item['title'] }}</div>
                                        <p class="check-copy">{{ $item['description'] }}</p>
                                    </div>
                                    <span class="badge {{ $item['completed'] ? 'text-bg-success' : 'text-bg-warning' }}">{{ $item['completed'] ? 'Done' : 'Pending' }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    @if(!empty($missingFields))
                        <div class="alert alert-warning py-2 px-3 mb-0">
                            <div class="small fw-semibold mb-1">Missing required fields</div>
                            <ul class="missing-list">
                                @foreach($missingFields as $field)
                                    <li>{{ $field }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </aside>
            </div>
        </div>
    </div>

    <script>
        (function () {
            const form = document.getElementById('studentSetupForm');
            const stepPanels = Array.from(document.querySelectorAll('[data-step]'));
            const stepNavs = Array.from(document.querySelectorAll('[data-step-nav]'));
            const stepStatusText = document.getElementById('stepStatusText');

            let currentStep = 0;

            const updateStepper = function () {
                stepPanels.forEach((panel, index) => {
                    panel.classList.toggle('active', index === currentStep);
                });

                stepNavs.forEach((nav, index) => {
                    nav.classList.remove('active', 'done');
                    if (index < currentStep) {
                        nav.classList.add('done');
                    } else if (index === currentStep) {
                        nav.classList.add('active');
                    }
                });

                if (stepStatusText) {
                    stepStatusText.textContent = 'Step ' + (currentStep + 1) + ' of ' + stepPanels.length;
                }
            };

            const firstInvalidInStep = function (stepIndex) {
                const panel = stepPanels[stepIndex];
                if (!panel) return null;
                const fields = Array.from(panel.querySelectorAll('input, select, textarea'));

                for (let i = 0; i < fields.length; i++) {
                    const field = fields[i];
                    if (field.disabled) continue;
                    if (!field.checkValidity()) {
                        return field;
                    }
                }

                return null;
            };

            const validateStep = function (stepIndex) {
                const panel = stepPanels[stepIndex];
                if (!panel) return true;

                const invalidField = firstInvalidInStep(stepIndex);
                if (!invalidField) return true;

                invalidField.reportValidity();
                invalidField.focus();
                return false;
            };

            const moveToStep = function (targetStep) {
                currentStep = Math.max(0, Math.min(stepPanels.length - 1, targetStep));
                updateStepper();
            };

            document.querySelectorAll('[data-next-step]').forEach((button) => {
                button.addEventListener('click', function () {
                    if (!validateStep(currentStep)) return;
                    const targetStep = Number(button.getAttribute('data-next-step'));
                    moveToStep(targetStep);
                });
            });

            document.querySelectorAll('[data-prev-step]').forEach((button) => {
                button.addEventListener('click', function () {
                    const targetStep = Number(button.getAttribute('data-prev-step'));
                    moveToStep(targetStep);
                });
            });

            if (form) {
                form.addEventListener('submit', function (event) {
                    for (let i = 0; i < stepPanels.length; i++) {
                        if (!validateStep(i)) {
                            moveToStep(i);
                            event.preventDefault();
                            return;
                        }
                    }
                });
            }

            const profileInput = document.getElementById('profile_image');
            const profilePreview = document.getElementById('profile_preview');
            if (profileInput && profilePreview) {
                profileInput.addEventListener('change', function () {
                    const file = profileInput.files && profileInput.files[0];
                    if (!file || !file.type || !file.type.startsWith('image/')) return;
                    profilePreview.src = URL.createObjectURL(file);
                    profilePreview.classList.remove('d-none');
                });
            }

            const schoolIdInput = document.getElementById('school_id_photo');
            const schoolIdPreview = document.getElementById('school_id_preview');
            if (schoolIdInput && schoolIdPreview) {
                schoolIdInput.addEventListener('change', function () {
                    const file = schoolIdInput.files && schoolIdInput.files[0];
                    if (!file || !file.type || !file.type.startsWith('image/')) return;
                    schoolIdPreview.src = URL.createObjectURL(file);
                    schoolIdPreview.classList.remove('d-none');
                });
            }

            const yearLevelInput = document.getElementById('year_level');
            const studentIdInput = document.getElementById('student_id');
            const studentIdHelp = document.getElementById('student_id_help');
            const schoolIdHelp = document.getElementById('school_id_help');
            const enrollmentProofType = document.getElementById('enrollment_proof_type');
            const enrollmentProofFile = document.getElementById('enrollment_proof_file');
            const enrollmentProofHelp = document.getElementById('enrollment_proof_help');

            const syncAcademicProofRequirements = function () {
                const isFirstYear = yearLevelInput && yearLevelInput.value === '1st Year';

                if (studentIdInput) {
                    studentIdInput.required = !isFirstYear;
                }
                if (studentIdHelp) {
                    studentIdHelp.classList.toggle('d-none', !isFirstYear);
                }

                if (schoolIdInput) {
                    const hasExistingSchoolId = schoolIdInput.dataset.hasExisting === '1';
                    schoolIdInput.required = !isFirstYear && !hasExistingSchoolId;
                }
                if (schoolIdHelp) {
                    schoolIdHelp.textContent = isFirstYear
                        ? 'Optional for 1st Year students. If unavailable, upload COR or COE below.'
                        : 'Required for 2nd Year and above. Upload a clear School ID photo (JPG, PNG, WEBP, GIF up to 3MB).';
                }

                if (enrollmentProofFile) {
                    const hasExistingEnrollmentProof = enrollmentProofFile.dataset.hasExisting === '1';
                    enrollmentProofFile.required = isFirstYear && !hasExistingEnrollmentProof;
                }

                if (enrollmentProofType) {
                    const hasExistingEnrollmentProof = enrollmentProofFile && enrollmentProofFile.dataset.hasExisting === '1';
                    const hasSelectedProofFile = enrollmentProofFile && enrollmentProofFile.files && enrollmentProofFile.files.length > 0;
                    enrollmentProofType.required = isFirstYear || hasExistingEnrollmentProof || hasSelectedProofFile;
                }

                if (enrollmentProofHelp) {
                    enrollmentProofHelp.textContent = isFirstYear
                        ? 'Required for 1st Year students. Accepted: PDF, JPG, PNG, WEBP up to 4MB.'
                        : 'Optional for 2nd Year and above. Accepted: PDF, JPG, PNG, WEBP up to 4MB.';
                }
            };

            if (yearLevelInput) {
                yearLevelInput.addEventListener('change', syncAcademicProofRequirements);
            }
            if (enrollmentProofFile) {
                enrollmentProofFile.addEventListener('change', syncAcademicProofRequirements);
            }
            syncAcademicProofRequirements();

            const phoneInputs = Array.from(document.querySelectorAll('.ph-contact-number'));
            const phonePattern = /^09\d{9}$/;
            const sanitizePhone = function (value) {
                return (value || '').replace(/\D/g, '').slice(0, 11);
            };
            const syncPhoneValidity = function (input) {
                const value = input.value || '';
                if (value === '' || phonePattern.test(value)) {
                    input.setCustomValidity('');
                    return;
                }
                input.setCustomValidity('Use 11-digit PH mobile format: 09XXXXXXXXX.');
            };

            phoneInputs.forEach(function (input) {
                const normalize = function () {
                    input.value = sanitizePhone(input.value);
                    syncPhoneValidity(input);
                };

                input.addEventListener('input', normalize);
                input.addEventListener('blur', normalize);
                input.addEventListener('paste', function () {
                    window.setTimeout(normalize, 0);
                });

                normalize();
            });

            const genderSelect = document.getElementById('gender');
            const genderCustomWrap = document.getElementById('genderCustomWrap');
            const genderCustomInput = document.getElementById('gender_custom');

            const syncGenderCustom = function () {
                if (!genderSelect || !genderCustomWrap) return;
                const isOther = genderSelect.value === 'Other';
                genderCustomWrap.style.display = isOther ? '' : 'none';
                if (genderCustomInput) {
                    genderCustomInput.required = isOther;
                    if (!isOther) {
                        genderCustomInput.value = '';
                    }
                }
            };

            if (genderSelect) {
                genderSelect.addEventListener('change', syncGenderCustom);
                syncGenderCustom();
            }

            const invalidServerField = document.querySelector('#studentSetupForm .is-invalid');
            if (invalidServerField) {
                const invalidStep = stepPanels.findIndex((panel) => panel.contains(invalidServerField));
                if (invalidStep >= 0) {
                    currentStep = invalidStep;
                }
            }

            updateStepper();
        })();
    </script>
</body>
</html>
