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
            font-family: 'Manrope', system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
            background:
                radial-gradient(760px 320px at -4% -10%, rgba(34, 197, 94, .18), transparent 58%),
                radial-gradient(860px 340px at 110% -16%, rgba(20, 83, 45, .10), transparent 60%),
                var(--shell);
            color: var(--ink);
            min-height: 100vh;
        }

        .setup-wrap {
            max-width: 680px;
            margin: 0 auto;
            padding: 1rem .82rem 1.6rem;
        }

        .setup-shell {
            border: 1px solid rgba(255, 255, 255, .78);
            border-radius: 1.75rem;
            background: rgba(255, 255, 255, .95);
            box-shadow: 0 24px 54px rgba(15, 23, 42, .12);
            overflow: hidden;
            backdrop-filter: blur(10px);
        }

        .setup-head {
            display: none;
        }

        .setup-topbar {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 1rem;
            margin-bottom: .9rem;
        }

        .setup-topbar-actions {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: .2rem;
            justify-content: flex-start;
        }

        .setup-topbar-title {
            font-size: .8rem;
            font-weight: 800;
            letter-spacing: .1em;
            text-transform: uppercase;
            color: #166534;
            margin: 0;
        }

        .setup-link {
            font-size: .86rem;
            color: #475569;
            text-decoration: none;
            padding: .1rem 0;
        }

        .setup-link:hover {
            color: #14532d;
            text-decoration: underline;
        }

        .setup-main {
            padding: 1rem 1rem 1.1rem;
        }

        .stepper-shell {
            border: 0;
            border-radius: 0;
            background: transparent;
            padding: .1rem 0 1rem;
            margin-bottom: .15rem;
            box-shadow: none;
        }

        .stepper-track {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: .5rem;
            align-items: center;
        }

        .step-chip {
            position: relative;
            padding-top: .7rem;
            transition: opacity .2s ease;
        }

        .step-chip::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            border-radius: 999px;
            background: var(--step-idle);
        }

        .step-badge {
            display: none;
        }

        .step-copy {
            min-width: 0;
            text-align: left;
        }

        .step-label {
            font-size: .72rem;
            color: #64748b;
            margin-bottom: 0;
            line-height: 1.2;
            font-weight: 600;
        }

        .step-title {
            display: none;
        }

        .step-chip.active {
            opacity: 1;
        }

        .step-chip.active::before {
            background: linear-gradient(90deg, #7cf94c, #52d228);
        }

        .step-chip.active .step-label {
            color: #14532d;
        }

        .step-chip.done {
            opacity: .9;
        }

        .step-chip.done::before {
            background: #16a34a;
        }

        .step-chip.done .step-label {
            color: #166534;
        }

        .step-status-text {
            display: inline-block;
            font-size: .8rem;
            color: #166534;
            font-weight: 700;
            margin-top: .95rem;
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

        .section-card {
            border: 0;
            border-radius: 0;
            background: transparent;
            padding: .25rem 0 0;
            margin-bottom: 0;
            box-shadow: none;
        }

        .section-title {
            display: block;
            font-size: 1.6rem;
            font-weight: 800;
            margin-bottom: .35rem;
            letter-spacing: -.02em;
        }

        .section-sub {
            display: block;
            font-size: .9rem;
            color: #64748b;
            margin-bottom: 1.1rem;
        }

        .form-label {
            font-weight: 600;
            font-size: .86rem;
            color: #334155;
            margin-bottom: .35rem;
        }

        .form-control,
        .form-select {
            border-color: var(--line);
            border-radius: .9rem;
            min-height: 46px;
            padding: .68rem .8rem;
            background: #fff;
        }

        textarea.form-control {
            min-height: unset;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #14532d;
            box-shadow: 0 0 0 .2rem rgba(20, 83, 45, .12);
        }

        .field-hint {
            font-size: .78rem;
            color: #64748b;
            margin-top: .38rem;
        }

        .address-suggest-wrap {
            position: relative;
        }

        .address-suggestion-menu {
            position: absolute;
            top: calc(100% + 4px);
            left: 0;
            right: 0;
            z-index: 40;
            border: 1px solid rgba(2, 8, 20, .14);
            border-radius: .75rem;
            background: #fff;
            box-shadow: 0 12px 24px rgba(2, 8, 20, .12);
            padding: .3rem;
            max-height: 230px;
            overflow-y: auto;
            display: none;
        }

        .address-suggestion-menu.is-open {
            display: block;
        }

        .address-suggestion-item {
            width: 100%;
            border: 0;
            background: transparent;
            text-align: left;
            border-radius: .55rem;
            padding: .42rem .55rem;
            font-size: .86rem;
            color: #0f172a;
            line-height: 1.35;
        }

        .address-suggestion-item:hover,
        .address-suggestion-item:focus {
            background: rgba(167, 243, 208, .28);
            outline: none;
        }

        .upload-preview {
            width: 86px;
            height: 86px;
            border-radius: 1rem;
            border: 1px solid rgba(2, 8, 20, .08);
            object-fit: cover;
            background: #f8fafc;
            box-shadow: 0 10px 24px rgba(15, 23, 42, .08);
        }

        .step-actions {
            display: flex;
            flex-direction: column;
            align-items: stretch;
            justify-content: flex-start;
            gap: .6rem;
            margin-top: .5rem;
            padding-top: .85rem;
            border-top: 1px solid rgba(15, 23, 42, .05);
        }

        .step-actions.has-dual-actions {
            display: grid;
            grid-template-columns: 1fr;
        }

        @media (min-width: 576px) {
            .step-actions.has-desktop-dual-actions {
                grid-template-columns: repeat(2, minmax(0, 1fr));
                align-items: end;
            }
        }

        .step-actions-right {
            display: flex;
            align-items: stretch;
            gap: .5rem;
            width: 100%;
        }

        .step-actions-left {
            width: 100%;
        }

        .btn-brand {
            background: linear-gradient(135deg, var(--brand), var(--brand-dark));
            border-color: var(--brand);
            color: #fff;
            box-shadow: 0 12px 24px rgba(20, 83, 45, .16);
        }

        .btn-brand:hover {
            background: var(--brand-dark);
            border-color: var(--brand-dark);
            color: #fff;
        }

        .btn-next,
        .btn-prev {
            border-radius: 999px;
            width: 100%;
            min-height: 44px;
            font-weight: 700;
        }

        .change-role-link {
            font-size: .84rem;
            color: #14532d;
            text-decoration: none;
        }

        .change-role-link:hover {
            text-decoration: underline;
        }

        @media (max-width: 991.98px) {
            .setup-wrap {
                max-width: 720px;
            }
        }

        @media (max-width: 575.98px) {
            .setup-wrap {
                padding: .8rem .7rem 1.25rem;
            }

            .setup-topbar,
            .setup-topbar-actions {
                align-items: flex-start;
            }

            .setup-topbar,
            .setup-topbar-actions {
                flex-direction: column;
            }

            .setup-topbar-actions {
                gap: .08rem;
            }

            .setup-main,
            .setup-head {
                padding-left: .88rem;
                padding-right: .88rem;
            }

            .setup-main {
                padding: .88rem;
            }

            .setup-head {
                padding: 1rem .95rem .75rem;
            }

            .stepper-shell {
                padding: .1rem 0 .8rem;
            }

            .step-chip {
                padding-top: .58rem;
            }

            .step-label {
                font-size: .66rem;
            }

            .step-actions.has-dual-actions {
                grid-template-columns: repeat(2, minmax(0, 1fr));
                align-items: end;
            }

            .step-actions-right,
            .step-actions-left {
                width: 100%;
            }

            .section-title {
                font-size: 1.35rem;
            }
        }
    </style>
</head>
<body>
    <div class="setup-wrap">
        <div class="setup-topbar px-1">
            <p class="setup-topbar-title">Student Verification Setup</p>
            <div class="setup-topbar-actions">
                @if(!$user->isStudentSetupComplete())
                    <a href="{{ route('onboarding.role.show') }}" class="change-role-link">
                        Selected the wrong role? Change role
                    </a>
                @endif
                <form method="POST" action="{{ route('logout') }}" class="m-0">
                    @csrf
                    <button type="submit" class="btn btn-link setup-link p-0">Logout</button>
                </form>
            </div>
        </div>

        <div class="setup-shell">
            <div class="setup-main">
                    @if(session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    <div class="stepper-shell">
                        <div class="stepper-track" id="setupStepper">
                            <div class="step-chip active" data-step-nav="0">
                                <div class="step-copy">
                                    <div class="step-label">Personal</div>
                                </div>
                            </div>
                            <div class="step-chip" data-step-nav="1">
                                <div class="step-copy">
                                    <div class="step-label">Academic</div>
                                </div>
                            </div>
                            <div class="step-chip" data-step-nav="2">
                                <div class="step-copy">
                                    <div class="step-label">Emergency</div>
                                </div>
                            </div>
                        </div>
                        <span class="step-status-text" id="stepStatusText">Step 1 of 3</span>
                    </div>

                    <form method="POST" action="{{ route('student.setup.update') }}" enctype="multipart/form-data" id="studentSetupForm">
                        @csrf
                        @method('PUT')

                        @php
                            $selectedGender = old('gender', in_array($user->gender, ['Male', 'Female', 'Other', 'Rather not Say', 'Rather not say']) ? $user->gender : (filled($user->gender) ? 'Other' : ''));
                            $selectedYearLevel = old('year_level', $user->year_level);
                            $isFirstYearSelected = $selectedYearLevel === '1st Year';
                            $selectedEnrollmentProofType = old('enrollment_proof_type', $user->enrollment_proof_type);
                            $hasEnrollmentProof = !empty($user->enrollment_proof_path);
                            $collegeMap = $academicCatalog['colleges'] ?? [];
                            $programMap = $academicCatalog['programs'] ?? [];
                            $selectedCollege = old('college', $user->college);
                            $selectedProgram = old('program', $user->program);
                            $programOptions = $programMap[$selectedCollege] ?? [];
                            $selectedFullName = old('full_name', $user->full_name ?: $user->name);
                        @endphp

                        <section class="step-panel active" data-step="0">
                            <div class="section-card step-panel-enter">
                                <div class="section-title">Personal Info</div>
                                <div class="section-sub">Add the basic profile details needed for student verification.</div>

                                <div class="row g-3">
                                    <div class="col-12">
                                        <label class="form-label">Name *</label>
                                        <input type="text" name="full_name" class="form-control @error('full_name') is-invalid @enderror" value="{{ $selectedFullName }}" required>
                                        @error('full_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Contact Number *</label>
                                        <input type="text" id="contact_number" name="contact_number" class="form-control ph-contact-number @error('contact_number') is-invalid @enderror" value="{{ old('contact_number', $user->contact_number) }}" inputmode="numeric" pattern="^09\d{9}$" maxlength="11" placeholder="09XXXXXXXXX" title="Use 11-digit PH mobile format: 09XXXXXXXXX" required>
                                        @error('contact_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Birthdate *</label>
                                        <input type="date" name="birth_date" class="form-control @error('birth_date') is-invalid @enderror" value="{{ old('birth_date', optional($user->birth_date)->format('Y-m-d')) }}" min="1920-01-01" max="{{ now()->toDateString() }}" required>
                                        @error('birth_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Gender *</label>
                                        <select name="gender" id="gender" class="form-select @error('gender') is-invalid @enderror" required>
                                            <option value="" disabled {{ $selectedGender === '' ? 'selected' : '' }}>Select gender</option>
                                            <option value="Male" {{ $selectedGender === 'Male' ? 'selected' : '' }}>Male</option>
                                            <option value="Female" {{ $selectedGender === 'Female' ? 'selected' : '' }}>Female</option>
                                            <option value="Other" {{ $selectedGender === 'Other' ? 'selected' : '' }}>Other</option>
                                            <option value="Rather not Say" {{ in_array($selectedGender, ['Rather not Say', 'Rather not say']) ? 'selected' : '' }}>Rather not Say</option>
                                        </select>
                                        @error('gender')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-12" id="genderCustomWrap" style="display: none;">
                                        <label class="form-label">Specify Gender *</label>
                                        <input type="text" name="gender_custom" id="gender_custom" class="form-control @error('gender_custom') is-invalid @enderror" value="{{ old('gender_custom', $selectedGender === 'Other' && !in_array($user->gender, ['Male', 'Female', 'Other', 'Rather not Say', 'Rather not say']) ? $user->gender : '') }}" maxlength="100" placeholder="Please specify">
                                        @error('gender_custom')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Home Address *</label>
                                        <div class="address-suggest-wrap">
                                            <textarea id="homeAddressInput" name="address" rows="2" class="form-control @error('address') is-invalid @enderror" placeholder="Start typing barangay..." required>{{ old('address', $user->address) }}</textarea>
                                            <div id="homeAddressSuggestMenu" class="address-suggestion-menu" role="listbox" aria-label="Home address suggestions"></div>
                                        </div>
                                        <div class="field-hint">Format: Barangay, City/Municipality, Province (e.g., Masipit, Calapan City, Oriental Mindoro).</div>
                                        <div id="homeAddressSuggestStatus" class="small text-muted mt-1"></div>
                                        @error('address')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Profile Photo *</label>
                                        <div class="d-flex align-items-center gap-3">
                                            @if(!empty($user->profile_image_path))
                                                <img id="profile_preview" src="{{ asset('storage/' . $user->profile_image_path) }}" alt="Profile" class="upload-preview">
                                            @else
                                                <img id="profile_preview" src="" alt="Profile" class="upload-preview d-none">
                                            @endif
                                            <div class="w-100">
                                                <input type="file" name="profile_image" id="profile_image" class="form-control @error('profile_image') is-invalid @enderror" accept="image/*" {{ empty($user->profile_image_path) ? 'required' : '' }}>
                                                <div class="field-hint">Required. JPG, PNG, WEBP, GIF up to 3MB.</div>
                                                @error('profile_image')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="step-actions has-dual-actions">
                                    <div class="step-actions-left">
                                        <span class="d-block" aria-hidden="true"></span>
                                    </div>
                                    <div class="step-actions-right">
                                        <button type="button" class="btn btn-brand btn-next" data-next-step="1">Next Step </button>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <section class="step-panel" data-step="1">
                            <div class="section-card step-panel-enter">
                                <div class="section-title">Academic Verification</div>
                                <div class="section-sub">Submit the academic details and proof documents required to continue.</div>
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label class="form-label">College *</label>
                                        <select id="college" name="college" class="form-select @error('college') is-invalid @enderror" required>
                                            <option value="">Select college</option>
                                            @foreach($collegeMap as $collegeCode => $collegeName)
                                                <option value="{{ $collegeCode }}" @selected($selectedCollege === $collegeCode)>
                                                    {{ $collegeCode }} - {{ $collegeName }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('college')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label">Program *</label>
                                        <select id="program" name="program" class="form-select @error('program') is-invalid @enderror" data-initial-program="{{ $selectedProgram }}" required>
                                            <option value="">Select program</option>
                                            @foreach($programOptions as $programName)
                                                <option value="{{ $programName }}" @selected($selectedProgram === $programName)>{{ $programName }}</option>
                                            @endforeach
                                        </select>
                                        @error('program')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label">Year Level *</label>
                                        <select id="year_level" name="year_level" class="form-select @error('year_level') is-invalid @enderror" required>
                                            <option value="" disabled {{ $selectedYearLevel ? '' : 'selected' }}>Select year</option>
                                            @foreach(['1st Year','2nd Year','3rd Year','4th Year'] as $level)
                                                <option value="{{ $level }}" {{ $selectedYearLevel === $level ? 'selected' : '' }}>{{ $level }}</option>
                                            @endforeach
                                        </select>
                                        @error('year_level')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label" id="studentIdLabel">Student ID{{ $isFirstYearSelected ? '' : ' *' }}</label>
                                        <input type="text" id="student_id" name="student_id" class="form-control @error('student_id') is-invalid @enderror" value="{{ old('student_id', $user->student_id) }}" placeholder="MCC2026-00000" {{ $isFirstYearSelected ? '' : 'required' }}>
                                        <div id="student_id_help" class="field-hint {{ $isFirstYearSelected ? '' : 'd-none' }}">Optional for 1st Year while official ID release is pending.</div>
                                        @error('student_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label" id="schoolIdLabel">School ID Upload{{ $isFirstYearSelected ? '' : ' *' }}</label>
                                        <div class="d-flex align-items-center gap-3">
                                            @if(!empty($user->school_id_path))
                                                <img id="school_id_preview" src="{{ asset('storage/' . $user->school_id_path) }}" alt="School ID" class="upload-preview">
                                            @else
                                                <img id="school_id_preview" src="" alt="School ID" class="upload-preview d-none">
                                            @endif
                                            <div class="w-100">
                                                <input type="file" name="school_id_photo" id="school_id_photo" class="form-control @error('school_id_photo') is-invalid @enderror" accept="image/*" data-has-existing="{{ empty($user->school_id_path) ? '0' : '1' }}" {{ ($isFirstYearSelected || !empty($user->school_id_path)) ? '' : 'required' }}>
                                                <div id="school_id_help" class="field-hint">
                                                    {{ $isFirstYearSelected ? 'Optional for 1st Year students. If unavailable, upload COR or COE below.' : 'Required for 2nd Year and above. Upload a clear School ID photo (JPG, PNG, WEBP, GIF up to 3MB).' }}
                                                </div>
                                                @error('school_id_photo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Enrollment Proof Type (COR or COE) *</label>
                                        <select id="enrollment_proof_type" name="enrollment_proof_type" class="form-select @error('enrollment_proof_type') is-invalid @enderror" required>
                                            <option value="" {{ filled($selectedEnrollmentProofType) ? '' : 'selected' }}>Select proof type</option>
                                            <option value="cor" {{ (string) $selectedEnrollmentProofType === 'cor' ? 'selected' : '' }}>Certificate of Registration (COR)</option>
                                            <option value="coe" {{ (string) $selectedEnrollmentProofType === 'coe' ? 'selected' : '' }}>Certificate of Enrollment (COE)</option>
                                        </select>
                                        <div class="field-hint">Choose whichever document you have available.</div>
                                        @error('enrollment_proof_type')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">COR / COE Upload *</label>
                                        <input type="file" name="enrollment_proof_file" id="enrollment_proof_file" class="form-control @error('enrollment_proof_file') is-invalid @enderror" accept=".pdf,image/*" data-has-existing="{{ $hasEnrollmentProof ? '1' : '0' }}" {{ $hasEnrollmentProof ? '' : 'required' }}>
                                        <div id="enrollment_proof_help" class="field-hint">
                                            Accepted: PDF, JPG, PNG, WEBP up to 4MB.
                                        </div>
                                        @error('enrollment_proof_file')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                        @if($hasEnrollmentProof)
                                            <a href="{{ asset('storage/' . $user->enrollment_proof_path) }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary rounded-pill mt-2">
                                                <i class="bi bi-file-earmark-arrow-down me-1"></i>View current {{ strtoupper((string) ($user->enrollment_proof_type ?? 'proof')) }} file
                                            </a>
                                        @endif
                                    </div>
                                </div>

                                <div class="step-actions has-dual-actions">
                                    <div class="step-actions-left">
                                        <button type="button" class="btn btn-outline-secondary btn-prev" data-prev-step="0">Back</button>
                                    </div>
                                    <div class="step-actions-right">
                                        <button type="button" class="btn btn-brand btn-next" data-next-step="2">Next Step </button>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <section class="step-panel" data-step="2">
                            <div class="section-card step-panel-enter">
                                <div class="section-title">Emergency and Parent Contact</div>
                                <div class="section-sub">Provide the contact details we need for urgent coordination and safety checks.</div>
                                <div class="row g-3">
                                    <div class="col-12">
                                        <div class="fw-semibold small text-uppercase text-muted">Parent / Guardian Details</div>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Parent / Guardian Name *</label>
                                        <input type="text" name="parent_contact_name" class="form-control @error('parent_contact_name') is-invalid @enderror" value="{{ old('parent_contact_name', $user->parent_contact_name) }}" required>
                                        @error('parent_contact_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Parent / Guardian Number *</label>
                                        <input type="text" name="parent_contact_number" class="form-control ph-contact-number @error('parent_contact_number') is-invalid @enderror" value="{{ old('parent_contact_number', $user->parent_contact_number) }}" inputmode="numeric" pattern="^09\d{9}$" maxlength="11" placeholder="09XXXXXXXXX" title="Use 11-digit PH mobile format: 09XXXXXXXXX" required>
                                        @error('parent_contact_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Parent / Guardian Address *</label>
                                        <textarea name="parent_contact_address" rows="2" class="form-control @error('parent_contact_address') is-invalid @enderror" required>{{ old('parent_contact_address', $user->parent_contact_address) }}</textarea>
                                        @error('parent_contact_address')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-12 pt-1">
                                        <div class="fw-semibold small text-uppercase text-muted">Emergency Contact Details</div>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Emergency Contact Name *</label>
                                        <input type="text" name="emergency_contact_name" class="form-control @error('emergency_contact_name') is-invalid @enderror" value="{{ old('emergency_contact_name', $user->emergency_contact_name) }}" required>
                                        @error('emergency_contact_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Emergency Contact Number *</label>
                                        <input type="text" name="emergency_contact_number" class="form-control ph-contact-number @error('emergency_contact_number') is-invalid @enderror" value="{{ old('emergency_contact_number', $user->emergency_contact_number) }}" inputmode="numeric" pattern="^09\d{9}$" maxlength="11" placeholder="09XXXXXXXXX" title="Use 11-digit PH mobile format: 09XXXXXXXXX" required>
                                        @error('emergency_contact_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Emergency Contact Relationship *</label>
                                        <input type="text" name="emergency_contact_relationship" class="form-control @error('emergency_contact_relationship') is-invalid @enderror" value="{{ old('emergency_contact_relationship', $user->emergency_contact_relationship) }}" required>
                                        @error('emergency_contact_relationship')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                </div>

                                <div class="step-actions has-dual-actions has-desktop-dual-actions">
                                    <div class="step-actions-left">
                                        <button type="button" class="btn btn-outline-secondary btn-prev" data-prev-step="1">Back</button>
                                    </div>
                                    <div class="step-actions-right">
                                        <button type="submit" class="btn btn-brand rounded-pill px-4">Complete Setup and Unlock Portal</button>
                                    </div>
                                </div>
                            </div>
                        </section>
                    </form>
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

            const homeAddressInput = document.getElementById('homeAddressInput');
            const homeAddressSuggestMenu = document.getElementById('homeAddressSuggestMenu');
            const homeAddressSuggestStatus = document.getElementById('homeAddressSuggestStatus');

            if (homeAddressInput && homeAddressSuggestMenu && homeAddressSuggestStatus) {
                const psgcBaseUrl = 'https://psgc.gitlab.io/api';
                let indexedBarangays = [];
                let datasetsPromise = null;
                let suggestionTimer = null;

                const normalizeCode = function (value) {
                    return typeof value === 'string' ? value : '';
                };

                const setAddressStatus = function (message, isError) {
                    homeAddressSuggestStatus.textContent = message || '';
                    homeAddressSuggestStatus.classList.toggle('text-danger', Boolean(isError));
                    homeAddressSuggestStatus.classList.toggle('text-muted', !isError);
                };

                const closeSuggestionMenu = function () {
                    homeAddressSuggestMenu.classList.remove('is-open');
                    homeAddressSuggestMenu.innerHTML = '';
                };

                const fetchJson = function (endpoint) {
                    return fetch(psgcBaseUrl + endpoint, {
                        headers: {
                            Accept: 'application/json'
                        }
                    }).then(function (response) {
                        if (!response.ok) {
                            throw new Error('Unable to load PSGC data.');
                        }
                        return response.json();
                    });
                };

                const buildIndexRows = function (payload) {
                    const barangays = Array.isArray(payload[0]) ? payload[0] : [];
                    const cities = Array.isArray(payload[1]) ? payload[1] : [];
                    const provinces = Array.isArray(payload[2]) ? payload[2] : [];

                    const cityByCode = new Map();
                    cities.forEach(function (cityItem) {
                        const code = normalizeCode(cityItem?.code);
                        if (!code) return;
                        cityByCode.set(code, {
                            name: String(cityItem?.name || '').trim(),
                            provinceCode: normalizeCode(cityItem?.provinceCode),
                        });
                    });

                    const provinceByCode = new Map();
                    provinces.forEach(function (provinceItem) {
                        const code = normalizeCode(provinceItem?.code);
                        if (!code) return;
                        provinceByCode.set(code, String(provinceItem?.name || '').trim());
                    });

                    indexedBarangays = barangays
                        .map(function (barangayItem) {
                            const barangayName = String(barangayItem?.name || '').trim();
                            if (!barangayName) {
                                return null;
                            }

                            const cityCode = normalizeCode(barangayItem?.cityCode) || normalizeCode(barangayItem?.municipalityCode) || normalizeCode(barangayItem?.subMunicipalityCode);
                            const cityRecord = cityCode ? cityByCode.get(cityCode) : null;
                            const cityName = String(cityRecord?.name || '').trim();

                            const provinceCode = normalizeCode(barangayItem?.provinceCode) || normalizeCode(cityRecord?.provinceCode);
                            const provinceName = provinceCode ? String(provinceByCode.get(provinceCode) || '').trim() : '';

                            if (!cityName || !provinceName) {
                                return null;
                            }

                            const fullAddress = [barangayName, cityName, provinceName].join(', ');

                            return {
                                address: fullAddress,
                                barangayLower: barangayName.toLowerCase(),
                                addressLower: fullAddress.toLowerCase(),
                            };
                        })
                        .filter(Boolean);
                };

                const loadDatasets = function () {
                    if (indexedBarangays.length > 0) {
                        return Promise.resolve();
                    }

                    if (datasetsPromise) {
                        return datasetsPromise;
                    }

                    setAddressStatus('Loading PH location suggestions...', false);

                    datasetsPromise = Promise.all([
                        fetchJson('/barangays.json'),
                        fetchJson('/cities-municipalities.json'),
                        fetchJson('/provinces.json')
                    ]).then(function (payload) {
                        buildIndexRows(payload);
                        setAddressStatus('', false);
                    }).catch(function () {
                        setAddressStatus('Unable to load PSGC suggestions right now. You can still enter the address manually.', true);
                        throw new Error('PSGC datasets unavailable');
                    });

                    return datasetsPromise;
                };

                const getSuggestions = function (term) {
                    const needle = term.toLowerCase();
                    const suggestions = [];
                    const seen = new Set();

                    for (let i = 0; i < indexedBarangays.length; i += 1) {
                        const item = indexedBarangays[i];
                        if (!item) continue;

                        if (!item.barangayLower.includes(needle) && !item.addressLower.includes(needle)) {
                            continue;
                        }

                        const key = item.addressLower;
                        if (seen.has(key)) {
                            continue;
                        }

                        seen.add(key);
                        suggestions.push(item.address);

                        if (suggestions.length >= 8) {
                            break;
                        }
                    }

                    return suggestions;
                };

                const renderSuggestions = function (items) {
                    homeAddressSuggestMenu.innerHTML = '';

                    if (!items.length) {
                        closeSuggestionMenu();
                        return;
                    }

                    items.forEach(function (item) {
                        const button = document.createElement('button');
                        button.type = 'button';
                        button.className = 'address-suggestion-item';
                        button.textContent = item;
                        button.addEventListener('click', function () {
                            homeAddressInput.value = item;
                            closeSuggestionMenu();
                            setAddressStatus('Suggested address applied.', false);
                        });
                        homeAddressSuggestMenu.appendChild(button);
                    });

                    homeAddressSuggestMenu.classList.add('is-open');
                };

                const runSuggestionSearch = function () {
                    const term = String(homeAddressInput.value || '').trim();

                    if (term.length < 2) {
                        closeSuggestionMenu();
                        setAddressStatus('', false);
                        return;
                    }

                    loadDatasets()
                        .then(function () {
                            const suggestions = getSuggestions(term);
                            renderSuggestions(suggestions);

                            if (suggestions.length === 0) {
                                setAddressStatus('No matching barangay found yet. Try a different spelling.', false);
                            } else {
                                setAddressStatus('', false);
                            }
                        })
                        .catch(function () {
                            closeSuggestionMenu();
                        });
                };

                homeAddressInput.addEventListener('focus', function () {
                    loadDatasets().catch(function () {});
                    runSuggestionSearch();
                });

                homeAddressInput.addEventListener('input', function () {
                    clearTimeout(suggestionTimer);
                    suggestionTimer = setTimeout(runSuggestionSearch, 220);
                });

                homeAddressInput.addEventListener('keydown', function (event) {
                    if (event.key === 'Escape') {
                        closeSuggestionMenu();
                    }
                });

                document.addEventListener('click', function (event) {
                    if (homeAddressSuggestMenu.contains(event.target) || event.target === homeAddressInput) {
                        return;
                    }
                    closeSuggestionMenu();
                });
            }

            const collegeSelect = document.getElementById('college');
            const programSelect = document.getElementById('program');
            const catalog = @json($academicCatalog ?? ['programs' => [], 'majors' => []]);

            if (collegeSelect && programSelect) {
                const initialProgram = programSelect.dataset.initialProgram || programSelect.value;

                const populatePrograms = function (selectedCollege, preferredProgram) {
                    const programs = (catalog.programs && catalog.programs[selectedCollege]) ? catalog.programs[selectedCollege] : [];

                    programSelect.innerHTML = '<option value="">Select program</option>';
                    programs.forEach((programName) => {
                        const option = document.createElement('option');
                        option.value = programName;
                        option.textContent = programName;
                        option.selected = preferredProgram === programName;
                        programSelect.appendChild(option);
                    });

                    if (preferredProgram && !programs.includes(preferredProgram)) {
                        programSelect.value = '';
                    }
                };

                collegeSelect.addEventListener('change', function () {
                    populatePrograms(collegeSelect.value, '');
                });

                populatePrograms(collegeSelect.value, initialProgram);
            }

            const yearLevelInput = document.getElementById('year_level');
            const studentIdInput = document.getElementById('student_id');
            const studentIdLabel = document.getElementById('studentIdLabel');
            const studentIdHelp = document.getElementById('student_id_help');
            const schoolIdLabel = document.getElementById('schoolIdLabel');
            const schoolIdHelp = document.getElementById('school_id_help');
            const enrollmentProofType = document.getElementById('enrollment_proof_type');
            const enrollmentProofFile = document.getElementById('enrollment_proof_file');
            const enrollmentProofHelp = document.getElementById('enrollment_proof_help');

            const syncAcademicProofRequirements = function () {
                const isFirstYear = yearLevelInput && yearLevelInput.value === '1st Year';

                if (studentIdInput) {
                    studentIdInput.required = !isFirstYear;
                }
                if (studentIdLabel) {
                    studentIdLabel.textContent = isFirstYear ? 'Student ID' : 'Student ID *';
                }
                if (studentIdHelp) {
                    studentIdHelp.classList.toggle('d-none', !isFirstYear);
                }

                if (schoolIdLabel) {
                    schoolIdLabel.textContent = isFirstYear ? 'School ID Upload' : 'School ID Upload *';
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
