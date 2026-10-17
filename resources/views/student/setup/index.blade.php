<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Verification Setup - Online Boarding House System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Bricolage+Grotesque:opsz,wght@12..96,600;12..96,700;12..96,800&display=swap" rel="stylesheet">
    <style>
        :root {
            --brand: #14532d;
            --brand-dark: #166534;
            --premium-emerald: #10b981;
            --premium-emerald-deep: #047857;
            --ink: #0f172a;
            --muted: #64748b;
            --line: #e2e8f0;
            --shell: #f8fafc;
        }

        *, *::before, *::after {
            box-sizing: border-box;
        }

        body {
            font-family: 'Manrope', system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
            color: var(--ink);
            background:
                radial-gradient(800px 380px at -4% -10%, rgba(16, 185, 129, .10), transparent 58%),
                radial-gradient(860px 400px at 110% -16%, rgba(4, 120, 87, .06), transparent 60%),
                var(--shell);
            min-height: 100vh;
            margin: 0;
            display: flex;
            flex-direction: column;
        }

        .setup-wrap {
            width: min(680px, 100%);
            margin: 0 auto;
            padding: 1.5rem 1rem 2.5rem;
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        /* Topbar */
        .setup-topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.25rem;
            padding: 0 .25rem;
        }

        .setup-brand {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            text-decoration: none;
            color: var(--brand);
            font-weight: 800;
            font-size: 1.05rem;
            letter-spacing: -.02em;
        }

        .setup-brand-badge {
            width: 2rem;
            height: 2rem;
            border-radius: .6rem;
            background: rgba(16, 185, 129, .12);
            color: var(--premium-emerald-deep);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.05rem;
        }

        .setup-topbar-actions {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .change-role-link {
            font-size: .84rem;
            color: var(--premium-emerald-deep);
            font-weight: 600;
            text-decoration: none;
            transition: color .18s ease;
        }

        .change-role-link:hover {
            color: var(--brand);
            text-decoration: underline;
        }

        .setup-logout {
            border: 0;
            background: transparent;
            color: var(--muted);
            font-size: .84rem;
            font-weight: 600;
            padding: .3rem .6rem;
            border-radius: .5rem;
            transition: all .18s ease;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: .3rem;
        }

        .setup-logout:hover {
            color: #b91c1c;
            background: rgba(239, 68, 68, .08);
        }

        /* Main Setup Shell */
        .setup-shell {
            border: 1px solid rgba(226, 232, 240, .9);
            border-radius: 1.5rem;
            background: #ffffff;
            box-shadow: 0 20px 45px -12px rgba(15, 23, 42, .08);
            padding: 2.25rem 2rem;
        }

        /* Question Panel */
        .question-panel {
            display: none;
        }

        .question-panel.active {
            display: block;
            animation: questionFadeIn .22s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes questionFadeIn {
            from {
                opacity: 0;
                transform: translateY(6px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .question-kicker {
            font-size: .75rem;
            font-weight: 800;
            letter-spacing: .12em;
            text-transform: uppercase;
            color: var(--premium-emerald-deep);
            margin-bottom: .45rem;
        }

        .question-title {
            font-family: 'Bricolage Grotesque', 'Manrope', system-ui, sans-serif;
            font-size: clamp(1.45rem, 2.8vw, 1.85rem);
            font-weight: 800;
            letter-spacing: -.025em;
            line-height: 1.2;
            color: var(--ink);
            margin-bottom: .35rem;
        }

        .question-subtitle {
            font-size: .92rem;
            color: var(--muted);
            margin-bottom: 1.75rem;
            line-height: 1.5;
        }

        /* Form Inputs & Controls */
        .form-label {
            font-size: .86rem;
            font-weight: 700;
            color: #334155;
            margin-bottom: .4rem;
        }

        .form-control, .form-select {
            border: 1.5px solid var(--line);
            border-radius: .75rem;
            padding: .65rem .95rem;
            font-size: .95rem;
            color: var(--ink);
            background-color: #ffffff;
            transition: border-color .18s ease, box-shadow .18s ease;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--premium-emerald);
            box-shadow: 0 0 0 3.5px rgba(16, 185, 129, .15);
            outline: 0;
        }

        .field-hint {
            font-size: .78rem;
            color: var(--muted);
            margin-top: .35rem;
            line-height: 1.4;
        }

        /* Upload previews */
        .upload-preview {
            width: 76px;
            height: 76px;
            object-fit: cover;
            border-radius: .85rem;
            border: 2px solid #e2e8f0;
            box-shadow: 0 4px 10px rgba(15, 23, 42, .06);
            flex-shrink: 0;
        }

        /* PSGC Address Autocomplete */
        .address-suggest-wrap {
            position: relative;
        }

        .address-suggestion-menu {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            z-index: 1050;
            display: none;
            max-height: 220px;
            overflow-y: auto;
            margin-top: .35rem;
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: .75rem;
            box-shadow: 0 14px 30px rgba(15, 23, 42, .14);
            padding: .35rem;
        }

        .address-suggestion-menu.is-open {
            display: block;
        }

        .address-suggestion-item {
            width: 100%;
            border: 0;
            background: transparent;
            text-align: left;
            padding: .55rem .75rem;
            border-radius: .5rem;
            font-size: .88rem;
            color: var(--ink);
            transition: background .15s ease;
            cursor: pointer;
        }

        .address-suggestion-item:hover, .address-suggestion-item:focus {
            background: #f1f5f9;
            color: var(--brand);
            outline: none;
        }

        /* Navigation Actions */
        .question-actions {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid var(--line);
        }

        .question-actions.first-question {
            justify-content: flex-end;
        }

        /* Primary Button (.btn-brand) matching landing page */
        .btn-brand {
            position: relative;
            overflow: hidden;
            isolation: isolate;
            padding: .75rem 1.6rem;
            font-weight: 700;
            font-size: .95rem;
            border-radius: 999px;
            border: 1px solid rgba(16, 185, 129, .9);
            background: linear-gradient(125deg, var(--premium-emerald) 0%, var(--premium-emerald-deep) 100%);
            color: #ecfdf5;
            box-shadow: 0 10px 24px rgba(4, 120, 87, .34);
            transform: translateY(0) scale(1);
            transition: transform .22s ease, box-shadow .24s ease, background .22s ease, border-color .22s ease, color .22s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: .5rem;
            cursor: pointer;
            text-decoration: none;
        }

        .btn-brand::after {
            content: "";
            position: absolute;
            top: -130%;
            bottom: -130%;
            left: -45%;
            width: 38%;
            background: linear-gradient(120deg, transparent 10%, rgba(255,255,255,.55) 50%, transparent 90%);
            transform: translateX(-190%) rotate(18deg);
            transition: transform .55s ease;
            pointer-events: none;
            z-index: 1;
        }

        .btn-brand:hover:not(:disabled) {
            transform: translateY(-2px) scale(1.01);
            background: linear-gradient(125deg, #0ea770 0%, #046246 100%);
            border-color: #0ea770;
            color: #fff;
            box-shadow: 0 16px 34px rgba(4, 120, 87, .45);
        }

        .btn-brand:hover:not(:disabled)::after {
            transform: translateX(430%) rotate(18deg);
        }

        .btn-brand:active:not(:disabled) {
            transform: translateY(0) scale(.99);
        }

        .btn-brand:focus-visible {
            outline: 2px solid rgba(16, 185, 129, .72);
            outline-offset: 2px;
        }

        .btn-brand:disabled {
            opacity: .5;
            cursor: not-allowed;
            transform: none !important;
            box-shadow: none !important;
        }

        /* Secondary Outline Button (.btn-prev) */
        .btn-prev {
            padding: .75rem 1.4rem;
            font-weight: 600;
            font-size: .92rem;
            border-radius: 999px;
            border: 1.5px solid #cbd5e1;
            background: #ffffff;
            color: #475569;
            box-shadow: 0 1px 2px rgba(15, 23, 42, .04);
            transition: all .2s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: .4rem;
            cursor: pointer;
            text-decoration: none;
        }

        .btn-prev:hover:not(:disabled) {
            background: #f8fafc;
            color: #0f172a;
            border-color: #94a3b8;
            transform: translateY(-1px);
        }

        .btn-prev:focus-visible {
            outline: 2px solid #94a3b8;
            outline-offset: 2px;
        }

        @media (max-width: 575.98px) {
            .setup-wrap {
                padding: 1rem .75rem 2rem;
            }

            .setup-shell {
                padding: 1.5rem 1.15rem;
                border-radius: 1.25rem;
            }

            .question-actions {
                flex-direction: column-reverse;
                gap: .75rem;
            }

            .question-actions .btn-prev,
            .question-actions .btn-brand {
                width: 100%;
            }

            .setup-topbar-actions {
                gap: .6rem;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .question-panel.active {
                animation: none !important;
            }
            .btn-brand, .btn-brand::after, .btn-prev {
                transition: none !important;
                transform: none !important;
            }
        }
    </style>
</head>
<body>
    <div class="setup-wrap">
        <header class="setup-topbar">
            <a href="{{ url('/') }}" class="setup-brand">
                <span class="setup-brand-badge"><i class="bi bi-mortarboard-fill"></i></span>
                <span>OBHS</span>
            </a>
            <div class="setup-topbar-actions">
                @if(!$user->isStudentSetupComplete())
                    <a href="{{ route('onboarding.role.show') }}" class="change-role-link">
                        <i class="bi bi-arrow-left-right me-1"></i>Change role
                    </a>
                @endif
                <form method="POST" action="{{ route('logout') }}" class="m-0">
                    @csrf
                    <button type="submit" class="setup-logout" title="Sign out of your account">
                        <i class="bi bi-box-arrow-right"></i>
                        <span>Log out</span>
                    </button>
                </form>
            </div>
        </header>

        <main class="setup-shell">
            @if(session('error'))
                <div class="alert alert-danger rounded-3 border-0 py-2 px-3 mb-3 small d-flex align-items-center gap-2">
                    <i class="bi bi-exclamation-circle-fill"></i>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            <form method="POST" action="{{ route('student.setup.update') }}" enctype="multipart/form-data" id="studentSetupForm" novalidate>
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

                <!-- PANEL 1: Basic Identity & Contact Number -->
                <section class="question-panel active" data-question-panel="0">
                    <div class="question-kicker">Personal details</div>
                    <h1 class="question-title">What should we call you and how can we reach you?</h1>
                    <p class="question-subtitle">Enter your full legal name as it appears on university records, active mobile number, and profile picture.</p>

                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Full Name *</label>
                            <input type="text" name="full_name" class="form-control @error('full_name') is-invalid @enderror" value="{{ $selectedFullName }}" placeholder="e.g., Juan Dela Cruz" required>
                            @error('full_name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label">Mobile Number *</label>
                            <input type="text" id="contact_number" name="contact_number" class="form-control ph-contact-number @error('contact_number') is-invalid @enderror" value="{{ old('contact_number', $user->contact_number) }}" inputmode="numeric" pattern="^09\d{9}$" maxlength="11" placeholder="09XXXXXXXXX" title="Use 11-digit PH mobile format: 09XXXXXXXXX" required>
                            <div class="field-hint">Must be an 11-digit Philippine mobile format (09XXXXXXXXX).</div>
                            @error('contact_number')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label">Profile Photo *</label>
                            <div class="d-flex align-items-center gap-3">
                                @if(!empty($user->profile_image_path))
                                    <img id="profile_preview" src="{{ file_url($user->profile_image_path) }}" alt="Profile preview" class="upload-preview">
                                @else
                                    <img id="profile_preview" src="" alt="Profile preview" class="upload-preview d-none">
                                @endif
                                <div class="w-100">
                                    <input type="file" name="profile_image" id="profile_image" class="form-control @error('profile_image') is-invalid @enderror" accept="image/*" {{ empty($user->profile_image_path) ? 'required' : '' }}>
                                    <div class="field-hint">JPG, PNG, WEBP, or GIF up to 3MB.</div>
                                    @error('profile_image')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="question-actions first-question">
                        <button type="button" class="btn btn-brand btn-next-question" data-target="1">
                            <span>Continue</span>
                            <i class="bi bi-arrow-right"></i>
                        </button>
                    </div>
                </section>

                <!-- PANEL 2: Demographics & Permanent Location -->
                <section class="question-panel" data-question-panel="1">
                    <div class="question-kicker">Personal details</div>
                    <h2 class="question-title">Tell us a bit more about yourself</h2>
                    <p class="question-subtitle">Your birthdate, gender, and permanent home address for student verification.</p>

                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label">Birthdate *</label>
                            <input type="date" name="birth_date" class="form-control @error('birth_date') is-invalid @enderror" value="{{ old('birth_date', optional($user->birth_date)->format('Y-m-d')) }}" min="1920-01-01" max="{{ now()->toDateString() }}" required>
                            @error('birth_date')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label">Gender *</label>
                            <select name="gender" id="gender" class="form-select @error('gender') is-invalid @enderror" required>
                                <option value="" disabled {{ $selectedGender === '' ? 'selected' : '' }}>Select gender</option>
                                <option value="Male" {{ $selectedGender === 'Male' ? 'selected' : '' }}>Male</option>
                                <option value="Female" {{ $selectedGender === 'Female' ? 'selected' : '' }}>Female</option>
                                <option value="Other" {{ $selectedGender === 'Other' ? 'selected' : '' }}>Other</option>
                                <option value="Rather not Say" {{ in_array($selectedGender, ['Rather not Say', 'Rather not say']) ? 'selected' : '' }}>Rather not Say</option>
                            </select>
                            @error('gender')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12" id="genderCustomWrap" style="display: none;">
                            <label class="form-label">Specify Gender *</label>
                            <input type="text" name="gender_custom" id="gender_custom" class="form-control @error('gender_custom') is-invalid @enderror" value="{{ old('gender_custom', $selectedGender === 'Other' && !in_array($user->gender, ['Male', 'Female', 'Other', 'Rather not Say', 'Rather not say']) ? $user->gender : '') }}" maxlength="100" placeholder="Please specify your gender">
                            @error('gender_custom')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label">Home Address *</label>
                            <div class="address-suggest-wrap">
                                <textarea id="homeAddressInput" name="address" rows="2" class="form-control @error('address') is-invalid @enderror" placeholder="Start typing barangay or municipality..." required>{{ old('address', $user->address) }}</textarea>
                                <div id="homeAddressSuggestMenu" class="address-suggestion-menu" role="listbox" aria-label="Home address suggestions"></div>
                            </div>
                            <div class="field-hint">Format: Barangay, City/Municipality, Province (e.g., Masipit, Calapan City, Oriental Mindoro).</div>
                            <div id="homeAddressSuggestStatus" class="small text-muted mt-1"></div>
                            @error('address')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="question-actions">
                        <button type="button" class="btn btn-prev btn-prev-question" data-target="0">
                            <i class="bi bi-arrow-left"></i>
                            <span>Previous</span>
                        </button>
                        <button type="button" class="btn btn-brand btn-next-question" data-target="2">
                            <span>Continue</span>
                            <i class="bi bi-arrow-right"></i>
                        </button>
                    </div>
                </section>

                <!-- PANEL 3: Academic Standing at MinSU -->
                <section class="question-panel" data-question-panel="2">
                    <div class="question-kicker">Academic information</div>
                    <h2 class="question-title">What is your academic standing at MinSU?</h2>
                    <p class="question-subtitle">Select your college, degree program, and current year level.</p>

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
                            @error('college')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label">Program *</label>
                            <select id="program" name="program" class="form-select @error('program') is-invalid @enderror" data-initial-program="{{ $selectedProgram }}" required>
                                <option value="">Select program</option>
                                @foreach($programOptions as $programName)
                                    <option value="{{ $programName }}" @selected($selectedProgram === $programName)>{{ $programName }}</option>
                                @endforeach
                            </select>
                            @error('program')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label">Year Level *</label>
                            <select id="year_level" name="year_level" class="form-select @error('year_level') is-invalid @enderror" required>
                                <option value="" disabled {{ $selectedYearLevel ? '' : 'selected' }}>Select year</option>
                                @foreach(['1st Year','2nd Year','3rd Year','4th Year'] as $level)
                                    <option value="{{ $level }}" {{ $selectedYearLevel === $level ? 'selected' : '' }}>{{ $level }}</option>
                                @endforeach
                            </select>
                            @error('year_level')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label" id="studentIdLabel">Student ID{{ $isFirstYearSelected ? '' : ' *' }}</label>
                            <input type="text" id="student_id" name="student_id" class="form-control @error('student_id') is-invalid @enderror" value="{{ old('student_id', $user->student_id) }}" placeholder="MCC2026-00000" {{ $isFirstYearSelected ? '' : 'required' }}>
                            <div id="student_id_help" class="field-hint {{ $isFirstYearSelected ? '' : 'd-none' }}">Optional for 1st Year while official ID is pending.</div>
                            @error('student_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="question-actions">
                        <button type="button" class="btn btn-prev btn-prev-question" data-target="1">
                            <i class="bi bi-arrow-left"></i>
                            <span>Previous</span>
                        </button>
                        <button type="button" class="btn btn-brand btn-next-question" data-target="3">
                            <span>Continue</span>
                            <i class="bi bi-arrow-right"></i>
                        </button>
                    </div>
                </section>

                <!-- PANEL 4: Student Verification Documents -->
                <section class="question-panel" data-question-panel="3">
                    <div class="question-kicker">Academic verification</div>
                    <h2 class="question-title">Verify your student enrollment</h2>
                    <p class="question-subtitle">Upload your school ID and proof of enrollment (COR or COE) to verify your account.</p>

                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label" id="schoolIdLabel">School ID Upload{{ $isFirstYearSelected ? '' : ' *' }}</label>
                            <div class="d-flex align-items-center gap-3">
                                @if(!empty($user->school_id_path))
                                    <img id="school_id_preview" src="{{ file_download_url($user->school_id_path) }}" alt="School ID preview" class="upload-preview">
                                @else
                                    <img id="school_id_preview" src="" alt="School ID preview" class="upload-preview d-none">
                                @endif
                                <div class="w-100">
                                    <input type="file" name="school_id_photo" id="school_id_photo" class="form-control @error('school_id_photo') is-invalid @enderror" accept="image/*" data-has-existing="{{ empty($user->school_id_path) ? '0' : '1' }}" {{ ($isFirstYearSelected || !empty($user->school_id_path)) ? '' : 'required' }}>
                                    <div id="school_id_help" class="field-hint">
                                        {{ $isFirstYearSelected ? 'Optional for 1st Year students. If unavailable, upload COR or COE below.' : 'Required for 2nd Year and above. Clear School ID photo (JPG, PNG, WEBP, GIF up to 3MB).' }}
                                    </div>
                                    @error('school_id_photo')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
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
                            <div class="field-hint">Choose whichever official enrollment document you currently have available.</div>
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
                                <a href="{{ file_download_url($user->enrollment_proof_path) }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary rounded-pill mt-2">
                                    <i class="bi bi-file-earmark-arrow-down me-1"></i>View current {{ strtoupper((string) ($user->enrollment_proof_type ?? 'proof')) }} file
                                </a>
                            @endif
                        </div>
                    </div>

                    <div class="question-actions">
                        <button type="button" class="btn btn-prev btn-prev-question" data-target="2">
                            <i class="bi bi-arrow-left"></i>
                            <span>Previous</span>
                        </button>
                        <button type="button" class="btn btn-brand btn-next-question" data-target="4">
                            <span>Continue</span>
                            <i class="bi bi-arrow-right"></i>
                        </button>
                    </div>
                </section>

                <!-- PANEL 5: Emergency & Parent Contact -->
                <section class="question-panel" data-question-panel="4">
                    <div class="question-kicker">Safety & emergency contacts</div>
                    <h2 class="question-title">Who should we contact in case of emergency?</h2>
                    <p class="question-subtitle">Provide your parent or guardian details and an emergency contact for safety coordination.</p>

                    <div class="row g-3">
                        <div class="col-12">
                            <div class="fw-bold small text-uppercase text-muted border-bottom pb-1">Parent or Guardian Information</div>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Parent / Guardian Full Name *</label>
                            <input type="text" name="parent_contact_name" class="form-control @error('parent_contact_name') is-invalid @enderror" value="{{ old('parent_contact_name', $user->parent_contact_name) }}" placeholder="e.g., Maria Dela Cruz" required>
                            @error('parent_contact_name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label">Parent / Guardian Mobile Number *</label>
                            <input type="text" name="parent_contact_number" class="form-control ph-contact-number @error('parent_contact_number') is-invalid @enderror" value="{{ old('parent_contact_number', $user->parent_contact_number) }}" inputmode="numeric" pattern="^09\d{9}$" maxlength="11" placeholder="09XXXXXXXXX" title="Use 11-digit PH mobile format: 09XXXXXXXXX" required>
                            @error('parent_contact_number')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label">Parent / Guardian Permanent Address *</label>
                            <textarea name="parent_contact_address" rows="2" class="form-control @error('parent_contact_address') is-invalid @enderror" placeholder="Barangay, City/Municipality, Province" required>{{ old('parent_contact_address', $user->parent_contact_address) }}</textarea>
                            @error('parent_contact_address')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12 pt-2">
                            <div class="fw-bold small text-uppercase text-muted border-bottom pb-1">Designated Emergency Contact</div>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Emergency Contact Name *</label>
                            <input type="text" name="emergency_contact_name" class="form-control @error('emergency_contact_name') is-invalid @enderror" value="{{ old('emergency_contact_name', $user->emergency_contact_name) }}" placeholder="e.g., Juan Dela Cruz Sr." required>
                            @error('emergency_contact_name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label">Emergency Mobile Number *</label>
                            <input type="text" name="emergency_contact_number" class="form-control ph-contact-number @error('emergency_contact_number') is-invalid @enderror" value="{{ old('emergency_contact_number', $user->emergency_contact_number) }}" inputmode="numeric" pattern="^09\d{9}$" maxlength="11" placeholder="09XXXXXXXXX" title="Use 11-digit PH mobile format: 09XXXXXXXXX" required>
                            @error('emergency_contact_number')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label">Relationship to Student *</label>
                            <input type="text" name="emergency_contact_relationship" class="form-control @error('emergency_contact_relationship') is-invalid @enderror" value="{{ old('emergency_contact_relationship', $user->emergency_contact_relationship) }}" placeholder="e.g., Father, Mother, Aunt" required>
                            @error('emergency_contact_relationship')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="question-actions">
                        <button type="button" class="btn btn-prev btn-prev-question" data-target="3">
                            <i class="bi bi-arrow-left"></i>
                            <span>Previous</span>
                        </button>
                        <button type="submit" id="submitFormBtn" class="btn btn-brand">
                            <span>Submit for Verification</span>
                            <i class="bi bi-check2-circle"></i>
                        </button>
                    </div>
                </section>
            </form>
        </main>
    </div>

    <script>
        (function () {
            const form = document.getElementById('studentSetupForm');
            const submitBtn = document.getElementById('submitFormBtn');
            const questionPanels = Array.from(document.querySelectorAll('[data-question-panel]'));
            let currentPanel = 0;

            // DOM References
            const profileInput = document.getElementById('profile_image');
            const profilePreview = document.getElementById('profile_preview');
            const schoolIdInput = document.getElementById('school_id_photo');
            const schoolIdPreview = document.getElementById('school_id_preview');
            const yearLevelInput = document.getElementById('year_level');
            const studentIdInput = document.getElementById('student_id');
            const studentIdLabel = document.getElementById('studentIdLabel');
            const studentIdHelp = document.getElementById('student_id_help');
            const schoolIdLabel = document.getElementById('schoolIdLabel');
            const schoolIdHelp = document.getElementById('school_id_help');
            const enrollmentProofType = document.getElementById('enrollment_proof_type');
            const enrollmentProofFile = document.getElementById('enrollment_proof_file');
            const enrollmentProofHelp = document.getElementById('enrollment_proof_help');
            const genderSelect = document.getElementById('gender');
            const genderCustomWrap = document.getElementById('genderCustomWrap');
            const genderCustomInput = document.getElementById('gender_custom');
            const collegeSelect = document.getElementById('college');
            const programSelect = document.getElementById('program');
            const homeAddressInput = document.getElementById('homeAddressInput');
            const homeAddressSuggestMenu = document.getElementById('homeAddressSuggestMenu');
            const homeAddressSuggestStatus = document.getElementById('homeAddressSuggestStatus');

            // --- Panel Navigation & Visibility ---
            const updateActivePanel = function () {
                questionPanels.forEach((panel, index) => {
                    panel.classList.toggle('active', index === currentPanel);
                });
                window.scrollTo({ top: 0, behavior: 'smooth' });
            };

            const moveToPanel = function (targetIndex) {
                currentPanel = Math.max(0, Math.min(questionPanels.length - 1, targetIndex));
                updateActivePanel();
            };

            // --- Inline Validation Engine ---
            const getFieldLabelText = function (field) {
                const parent = field.closest('.col-12, .col-md-6') || field.parentElement;
                const label = parent ? parent.querySelector('.form-label') : null;
                if (label) {
                    return label.textContent.replace(/[*:]/g, '').trim();
                }
                return 'This field';
            };

            const showFieldError = function (field, message) {
                if (!field) return;
                field.classList.add('is-invalid');
                const parent = field.closest('.col-12, .col-md-6') || field.parentElement;
                let feedback = parent.querySelector(`.client-feedback[data-for="${field.name || field.id}"]`);
                if (!feedback) {
                    feedback = document.createElement('div');
                    feedback.className = 'invalid-feedback client-feedback d-block';
                    feedback.setAttribute('data-for', field.name || field.id);
                    parent.appendChild(feedback);
                }
                feedback.textContent = message;
                feedback.style.display = 'block';
            };

            const clearFieldError = function (field) {
                if (!field) return;
                field.classList.remove('is-invalid');
                const parent = field.closest('.col-12, .col-md-6') || field.parentElement;
                if (parent) {
                    const feedback = parent.querySelector(`.client-feedback[data-for="${field.name || field.id}"]`);
                    if (feedback) feedback.remove();
                    const serverFeedback = parent.querySelector('.invalid-feedback:not(.client-feedback)');
                    if (serverFeedback) serverFeedback.style.display = 'none';
                }
            };

            const validateField = function (field) {
                if (!field || field.disabled || field.type === 'hidden') return true;

                // Check if element is conditionally hidden inside its panel
                let parent = field.parentElement;
                while (parent && !parent.classList.contains('question-panel')) {
                    if (parent.style.display === 'none' || parent.classList.contains('d-none')) {
                        return true;
                    }
                    parent = parent.parentElement;
                }

                const value = (field.value || '').trim();

                // 1. Required check
                if (field.required) {
                    if (field.type === 'file') {
                        const hasFiles = field.files && field.files.length > 0;
                        const hasExisting = field.dataset.hasExisting === '1';
                        if (!hasFiles && !hasExisting) {
                            showFieldError(field, `${getFieldLabelText(field)} is required.`);
                            return false;
                        }
                    } else if (value === '') {
                        showFieldError(field, `${getFieldLabelText(field)} is required.`);
                        return false;
                    }
                }

                // 2. File size & format checks if file is selected
                if (field.type === 'file' && field.files && field.files.length > 0) {
                    const file = field.files[0];
                    let maxMb = 3;
                    if (field.name === 'enrollment_proof_file') maxMb = 4;
                    if (file.size > maxMb * 1024 * 1024) {
                        const actualMb = (file.size / (1024 * 1024)).toFixed(1);
                        showFieldError(field, `Selected file (${actualMb}MB) exceeds the maximum limit of ${maxMb}MB.`);
                        return false;
                    }
                    if (field.accept) {
                        const allowed = field.accept.split(',').map((s) => s.trim().toLowerCase());
                        const fileName = file.name.toLowerCase();
                        const fileType = (file.type || '').toLowerCase();
                        const matches = allowed.some((ext) => {
                            if (ext === 'image/*') return fileType.startsWith('image/');
                            if (ext.startsWith('.')) return fileName.endsWith(ext);
                            return false;
                        });
                        if (!matches) {
                            showFieldError(field, `Please upload an accepted format (${field.accept}).`);
                            return false;
                        }
                    }
                }

                // 3. Philippine mobile phone validation
                if (field.classList.contains('ph-contact-number')) {
                    if (value !== '' && !/^09\d{9}$/.test(value)) {
                        showFieldError(field, 'Use 11-digit Philippine mobile format: 09XXXXXXXXX.');
                        return false;
                    }
                }

                // 4. HTML5 Constraint Validation
                if (!field.checkValidity()) {
                    showFieldError(field, field.validationMessage || 'Please provide a valid value.');
                    return false;
                }

                clearFieldError(field);
                return true;
            };

            const validatePanel = function (panelIndex) {
                const panel = questionPanels[panelIndex];
                if (!panel) return true;

                const fields = Array.from(panel.querySelectorAll('input, select, textarea'));
                let firstInvalid = null;
                let isValid = true;

                fields.forEach((field) => {
                    const fieldValid = validateField(field);
                    if (!fieldValid) {
                        isValid = false;
                        if (!firstInvalid) {
                            firstInvalid = field;
                        }
                    }
                });

                if (!isValid && firstInvalid) {
                    firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    try {
                        firstInvalid.focus({ preventScroll: true });
                        firstInvalid.reportValidity();
                    } catch (e) {}
                }

                return isValid;
            };

            // --- Navigation Button Listeners ---
            document.querySelectorAll('.btn-next-question').forEach((button) => {
                button.addEventListener('click', function (e) {
                    e.preventDefault();
                    if (!validatePanel(currentPanel)) {
                        return;
                    }
                    const target = Number(button.getAttribute('data-target'));
                    moveToPanel(target);
                });
            });

            document.querySelectorAll('.btn-prev-question').forEach((button) => {
                button.addEventListener('click', function (e) {
                    e.preventDefault();
                    const target = Number(button.getAttribute('data-target'));
                    moveToPanel(target);
                });
            });

            // Automatically clear client validation on user edit
            if (form) {
                ['input', 'change'].forEach((eventName) => {
                    form.addEventListener(eventName, function (e) {
                        if (e.target && e.target.matches('input, select, textarea')) {
                            clearFieldError(e.target);
                        }
                    });
                });

                form.addEventListener('submit', function (event) {
                    for (let i = 0; i < questionPanels.length; i++) {
                        if (!validatePanel(i)) {
                            moveToPanel(i);
                            event.preventDefault();
                            return;
                        }
                    }

                    if (submitBtn) {
                        submitBtn.disabled = true;
                        submitBtn.innerHTML = '<span>Submitting verification...</span><span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';
                    }
                });
            }

            // --- Image Preview Handlers ---
            if (profileInput && profilePreview) {
                profileInput.addEventListener('change', function () {
                    const file = profileInput.files && profileInput.files[0];
                    if (!file || !file.type || !file.type.startsWith('image/')) return;
                    profilePreview.src = URL.createObjectURL(file);
                    profilePreview.classList.remove('d-none');
                });
            }

            if (schoolIdInput && schoolIdPreview) {
                schoolIdInput.addEventListener('change', function () {
                    const file = schoolIdInput.files && schoolIdInput.files[0];
                    if (!file || !file.type || !file.type.startsWith('image/')) return;
                    schoolIdPreview.src = URL.createObjectURL(file);
                    schoolIdPreview.classList.remove('d-none');
                });
            }

            // --- Phone Normalization & Validation ---
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

            // --- Gender Custom Input Toggle ---
            const syncGenderCustom = function () {
                if (!genderSelect || !genderCustomWrap) return;
                const isOther = genderSelect.value === 'Other';
                genderCustomWrap.style.display = isOther ? '' : 'none';
                if (genderCustomInput) {
                    genderCustomInput.required = isOther;
                    if (!isOther) genderCustomInput.value = '';
                }
            };

            if (genderSelect) {
                genderSelect.addEventListener('change', syncGenderCustom);
                syncGenderCustom();
            }

            // --- Academic Proof Sync based on Year Level ---
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
                        : 'Required for 2nd Year and above. Clear School ID photo (JPG, PNG, WEBP, GIF up to 3MB).';
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

            if (yearLevelInput) yearLevelInput.addEventListener('change', syncAcademicProofRequirements);
            if (enrollmentProofFile) enrollmentProofFile.addEventListener('change', syncAcademicProofRequirements);
            syncAcademicProofRequirements();

            // --- PSGC Location Suggestions ---
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
                        headers: { Accept: 'application/json' }
                    }).then(function (response) {
                        if (!response.ok) throw new Error('Unable to load PSGC data.');
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
                            if (!barangayName) return null;

                            const cityCode = normalizeCode(barangayItem?.cityCode) || normalizeCode(barangayItem?.municipalityCode) || normalizeCode(barangayItem?.subMunicipalityCode);
                            const cityRecord = cityCode ? cityByCode.get(cityCode) : null;
                            const cityName = String(cityRecord?.name || '').trim();

                            const provinceCode = normalizeCode(barangayItem?.provinceCode) || normalizeCode(cityRecord?.provinceCode);
                            const provinceName = provinceCode ? String(provinceByCode.get(provinceCode) || '').trim() : '';

                            if (!cityName || !provinceName) return null;

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
                    if (indexedBarangays.length > 0) return Promise.resolve();
                    if (datasetsPromise) return datasetsPromise;

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
                        if (!item.barangayLower.includes(needle) && !item.addressLower.includes(needle)) continue;

                        const key = item.addressLower;
                        if (seen.has(key)) continue;

                        seen.add(key);
                        suggestions.push(item.address);
                        if (suggestions.length >= 8) break;
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
                            clearFieldError(homeAddressInput);
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
                    if (event.key === 'Escape') closeSuggestionMenu();
                });

                document.addEventListener('click', function (event) {
                    if (homeAddressSuggestMenu.contains(event.target) || event.target === homeAddressInput) return;
                    closeSuggestionMenu();
                });
            }

            // --- Academic Catalog & Program Population ---
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

            // --- Server-side invalid field automatic panel activation ---
            const invalidServerField = document.querySelector('#studentSetupForm .is-invalid');
            if (invalidServerField) {
                const invalidPanelIndex = questionPanels.findIndex((panel) => panel.contains(invalidServerField));
                if (invalidPanelIndex >= 0) {
                    currentPanel = invalidPanelIndex;
                    setTimeout(function () {
                        invalidServerField.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }, 100);
                }
            }

            updateActivePanel();
        })();
    </script>
</body>
</html>
