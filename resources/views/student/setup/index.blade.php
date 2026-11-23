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

        /* Save & Return to Review Button */
        .btn-save-return-review {
            padding: .75rem 1.4rem;
            font-weight: 700;
            font-size: .92rem;
            border-radius: 999px;
            border: 1.5px solid var(--premium-emerald-deep);
            background: rgba(16, 185, 129, .08);
            color: var(--premium-emerald-deep);
            box-shadow: 0 1px 2px rgba(15, 23, 42, .04);
            transition: all .2s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: .4rem;
            cursor: pointer;
            text-decoration: none;
        }

        .btn-save-return-review:hover {
            background: var(--premium-emerald-deep);
            color: #ffffff;
            border-color: var(--premium-emerald-deep);
            transform: translateY(-1px);
        }

        /* Review Screen Cards */
        .review-sections-wrap {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .review-card {
            border: 1px solid var(--line);
            border-radius: 1rem;
            background: #ffffff;
            padding: 1.15rem 1.35rem;
            transition: border-color .15s ease, box-shadow .15s ease;
        }

        .review-card:hover {
            border-color: #cbd5e1;
            box-shadow: 0 4px 12px rgba(15, 23, 42, .04);
        }

        .review-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-bottom: .6rem;
            margin-bottom: .75rem;
            border-bottom: 1px solid #f1f5f9;
        }

        .review-section-title {
            font-size: .8rem;
            font-weight: 800;
            letter-spacing: .08em;
            text-transform: uppercase;
            color: var(--premium-emerald-deep);
            margin: 0;
            display: flex;
            align-items: center;
            gap: .4rem;
        }

        .btn-review-edit {
            font-size: .82rem;
            font-weight: 700;
            color: var(--brand-dark);
            text-decoration: none;
            border-radius: .5rem;
            padding: .25rem .6rem;
            background: rgba(20, 83, 45, .06);
            border: 0;
            display: inline-flex;
            align-items: center;
            gap: .3rem;
            transition: all .15s ease;
            cursor: pointer;
        }

        .btn-review-edit:hover {
            background: rgba(20, 83, 45, .14);
            color: var(--brand);
        }

        .btn-review-edit:focus-visible {
            outline: 2px solid var(--premium-emerald);
            outline-offset: 2px;
        }

        .review-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: .75rem 1rem;
        }

        .review-item {
            display: flex;
            flex-direction: column;
            gap: .15rem;
        }

        .review-label {
            font-size: .75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .04em;
            color: var(--muted);
        }

        .review-val {
            font-size: .92rem;
            font-weight: 600;
            color: var(--ink);
            word-break: break-word;
        }

        .review-val.is-empty {
            color: #94a3b8;
            font-style: italic;
            font-weight: 500;
        }

        .review-thumb {
            width: 48px;
            height: 48px;
            border-radius: .5rem;
            object-fit: cover;
            border: 1px solid #e2e8f0;
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
            .question-actions .btn-save-return-review,
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

                <!-- PANEL 0: Early Student ID Question & Identity Protection -->
                <section class="question-panel active" data-question-panel="0">
                    <div class="question-kicker">Student identity</div>
                    <h1 class="question-title">What is your Student ID?</h1>
                    <p class="question-subtitle">Enter your MinSU Student ID as shown on your university records.</p>

                    <div class="row g-3">
                        <div class="col-12" id="studentIdInputContainer">
                            <label class="form-label" for="student_id" id="studentIdLabel">Student ID</label>
                            <input type="text" id="student_id" name="student_id" class="form-control @error('student_id') is-invalid @enderror" value="{{ old('student_id', $user->student_id) }}" placeholder="Enter your Student ID, if available" autocomplete="off" spellcheck="false">
                            <div class="field-hint" id="student_id_help">If you haven't received your Student ID yet, you may leave this blank. Format example: MCC2026-00000.</div>
                            <div id="student_id_feedback" class="mt-2 small" role="status" aria-live="polite"></div>
                            @error('student_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>

                        <!-- RECOVERY GUIDANCE CARD (Displayed on exact duplicate/conflict) -->
                        <div class="col-12 d-none" id="studentIdConflictCard">
                            <div class="p-3 p-md-4 rounded-4 border border-warning-subtle" style="background: #fffbeb; box-shadow: 0 4px 14px rgba(245, 158, 11, .10);">
                                <div class="d-flex align-items-start gap-3">
                                    <div class="rounded-circle p-2 text-warning-emphasis bg-warning bg-opacity-25 flex-shrink-0" style="width: 42px; height: 42px; display: flex; align-items: center; justify-content: center;">
                                        <i class="bi bi-shield-exclamation fs-5"></i>
                                    </div>
                                    <div>
                                        <h5 class="fw-bold text-dark mb-1" style="font-size: 1.05rem;">We couldn't continue with this Student ID.</h5>
                                        <p class="text-muted small mb-3 leading-relaxed">
                                            If you've used OBHS before, you can recover your account. If you believe this is a mistake, request assistance.
                                        </p>

                                        <div class="d-flex flex-wrap gap-2 mb-2">
                                            <a href="{{ route('password.request') }}" class="btn btn-sm btn-brand">
                                                <i class="bi bi-key me-1"></i>Recover Account
                                            </a>
                                            <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3" id="toggleAssistanceFormBtn">
                                                <i class="bi bi-question-circle me-1"></i>Request Assistance
                                            </button>
                                            <button type="button" class="btn btn-sm btn-link text-muted text-decoration-none" id="dismissConflictBtn">
                                                <i class="bi bi-arrow-left me-1"></i>Back
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Inline Identity Assistance Request Form -->
                                <div class="mt-3 pt-3 border-top border-warning-subtle d-none" id="assistanceRequestFormWrap">
                                    <h6 class="fw-bold text-dark mb-1" style="font-size: .95rem;">
                                        <i class="bi bi-envelope-paper me-1 text-success"></i>Request Account Assistance
                                    </h6>
                                    <p class="text-muted small mb-3">
                                        Submit a recovery request for administrative identity review. Our campus administrators will verify your university records.
                                    </p>

                                    <div id="assistanceAlert" class="alert d-none py-2 small" role="alert"></div>

                                    <div class="row g-2">
                                        <div class="col-12 col-md-6">
                                            <label class="form-label small mb-1">Claimed Student ID</label>
                                            <input type="text" id="assist_student_id" class="form-control form-control-sm bg-light" readonly>
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <label class="form-label small mb-1">Contact Email *</label>
                                            <input type="email" id="assist_contact_email" class="form-control form-control-sm" value="{{ $user->email }}" required>
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <label class="form-label small mb-1">Contact Phone (Optional)</label>
                                            <input type="text" id="assist_contact_phone" class="form-control form-control-sm" placeholder="09XXXXXXXXX" value="{{ $user->contact_number }}">
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <label class="form-label small mb-1">Supporting Document (Optional)</label>
                                            <input type="file" id="assist_document" class="form-control form-control-sm" accept=".pdf,image/*">
                                            <div class="field-hint" style="font-size: .72rem;">MinSU ID or COR/COE photo.</div>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label small mb-1">Reason for Recovery *</label>
                                            <textarea id="assist_reason" class="form-control form-control-sm" rows="2" placeholder="e.g., I lost access to my registered university email and need to recover my existing student identity." required></textarea>
                                        </div>
                                        <div class="col-12 pt-2 d-flex gap-2">
                                            <button type="button" class="btn btn-sm btn-brand" id="submitAssistanceBtn">
                                                <span>Submit Assistance Request</span>
                                                <i class="bi bi-send ms-1"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3" id="cancelAssistanceBtn">
                                                Cancel
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="question-actions first-question">
                        <button type="button" class="btn btn-save-return-review d-none">
                            <i class="bi bi-arrow-return-left"></i>
                            <span>Save & Return to Review</span>
                        </button>
                        <button type="button" class="btn btn-brand btn-next-question" data-target="1" id="btnContinueFromStudentId">
                            <span>Continue</span>
                            <i class="bi bi-arrow-right"></i>
                        </button>
                    </div>
                </section>

                <!-- PANEL 1: Basic Identity & Contact Number -->
                <section class="question-panel" data-question-panel="1">
                    <div class="question-kicker">Personal details</div>
                    <h2 class="question-title">What should we call you and how can we reach you?</h2>
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
                                    <div class="field-hint">JPG, PNG, WEBP, or GIF.</div>
                                    @error('profile_image')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="question-actions">
                        <button type="button" class="btn btn-prev btn-prev-question" data-target="0">
                            <i class="bi bi-arrow-left"></i>
                            <span>Previous</span>
                        </button>
                        <button type="button" class="btn btn-save-return-review d-none">
                            <i class="bi bi-arrow-return-left"></i>
                            <span>Save & Return to Review</span>
                        </button>
                        <button type="button" class="btn btn-brand btn-next-question" data-target="2">
                            <span>Continue</span>
                            <i class="bi bi-arrow-right"></i>
                        </button>
                    </div>
                </section>

                <!-- PANEL 2: Demographics & Permanent Location -->
                <section class="question-panel" data-question-panel="2">
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
                        <button type="button" class="btn btn-prev btn-prev-question" data-target="1">
                            <i class="bi bi-arrow-left"></i>
                            <span>Previous</span>
                        </button>
                        <button type="button" class="btn btn-save-return-review d-none">
                            <i class="bi bi-arrow-return-left"></i>
                            <span>Save & Return to Review</span>
                        </button>
                        <button type="button" class="btn btn-brand btn-next-question" data-target="3">
                            <span>Continue</span>
                            <i class="bi bi-arrow-right"></i>
                        </button>
                    </div>
                </section>

                <!-- PANEL 3: Academic Standing at MinSU -->
                <section class="question-panel" data-question-panel="3">
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

                        <div class="col-12">
                            <label class="form-label">Year Level *</label>
                            <select id="year_level" name="year_level" class="form-select @error('year_level') is-invalid @enderror" required>
                                <option value="" disabled {{ $selectedYearLevel ? '' : 'selected' }}>Select year</option>
                                @foreach(['1st Year','2nd Year','3rd Year','4th Year'] as $level)
                                    <option value="{{ $level }}" {{ $selectedYearLevel === $level ? 'selected' : '' }}>{{ $level }}</option>
                                @endforeach
                            </select>
                            @error('year_level')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="question-actions">
                        <button type="button" class="btn btn-prev btn-prev-question" data-target="2">
                            <i class="bi bi-arrow-left"></i>
                            <span>Previous</span>
                        </button>
                        <button type="button" class="btn btn-save-return-review d-none">
                            <i class="bi bi-arrow-return-left"></i>
                            <span>Save & Return to Review</span>
                        </button>
                        <button type="button" class="btn btn-brand btn-next-question" data-target="4">
                            <span>Continue</span>
                            <i class="bi bi-arrow-right"></i>
                        </button>
                    </div>
                </section>

                <!-- PANEL 4: Student Verification Documents -->
                <section class="question-panel" data-question-panel="4">
                    <div class="question-kicker">Academic verification</div>
                    <h2 class="question-title">Verify your student enrollment</h2>
                    <p class="question-subtitle">Upload your school ID and proof of enrollment (COR or COE) to verify your account.</p>

                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label" id="schoolIdLabel">School ID Upload</label>
                            <div class="d-flex align-items-center gap-3">
                                @if(!empty($user->school_id_path))
                                    <img id="school_id_preview" src="{{ file_download_url($user->school_id_path) }}" alt="School ID preview" class="upload-preview">
                                @else
                                    <img id="school_id_preview" src="" alt="School ID preview" class="upload-preview d-none">
                                @endif
                                <div class="w-100">
                                    <input type="file" name="school_id_photo" id="school_id_photo" class="form-control @error('school_id_photo') is-invalid @enderror" accept="image/*" data-has-existing="{{ empty($user->school_id_path) ? '0' : '1' }}">
                                    <div id="school_id_help" class="field-hint">
                                        Upload a clear photo of your school ID if available.
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
                                Accepted: PDF, JPG, PNG, WEBP.
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
                        <button type="button" class="btn btn-prev btn-prev-question" data-target="3">
                            <i class="bi bi-arrow-left"></i>
                            <span>Previous</span>
                        </button>
                        <button type="button" class="btn btn-save-return-review d-none">
                            <i class="bi bi-arrow-return-left"></i>
                            <span>Save & Return to Review</span>
                        </button>
                        <button type="button" class="btn btn-brand btn-next-question" data-target="5">
                            <span>Continue</span>
                            <i class="bi bi-arrow-right"></i>
                        </button>
                    </div>
                </section>

                <!-- PANEL 5: Emergency & Parent Contact -->
                <section class="question-panel" data-question-panel="5">
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
                        <button type="button" class="btn btn-prev btn-prev-question" data-target="4">
                            <i class="bi bi-arrow-left"></i>
                            <span>Previous</span>
                        </button>
                        <button type="button" class="btn btn-save-return-review d-none">
                            <i class="bi bi-arrow-return-left"></i>
                            <span>Save & Return to Review</span>
                        </button>
                        <button type="button" class="btn btn-brand btn-next-question" data-target="6">
                            <span>Continue</span>
                            <i class="bi bi-arrow-right"></i>
                        </button>
                    </div>
                </section>

                <!-- PANEL 6: Review Your Information -->
                <section class="question-panel" data-question-panel="6">
                    <div class="question-kicker">Review your information</div>
                    <h2 class="question-title">Review your information</h2>
                    <p class="question-subtitle">Make sure your details are correct before submitting.</p>

                    <div class="review-sections-wrap">
                        <!-- 1. Student Identity -->
                        <div class="review-card">
                            <div class="review-header">
                                <h3 class="review-section-title"><i class="bi bi-person-badge me-1"></i>Student Identity</h3>
                                <button type="button" class="btn-review-edit" data-edit-panel="0">
                                    <i class="bi bi-pencil-square"></i>Edit
                                </button>
                            </div>
                            <div class="review-grid">
                                <div class="review-item">
                                    <span class="review-label">Student ID</span>
                                    <span class="review-val" id="review_student_id">Not provided</span>
                                </div>
                            </div>
                        </div>

                        <!-- 2. Personal Details -->
                        <div class="review-card">
                            <div class="review-header">
                                <h3 class="review-section-title"><i class="bi bi-person me-1"></i>Personal Details</h3>
                                <button type="button" class="btn-review-edit" data-edit-panel="1">
                                    <i class="bi bi-pencil-square"></i>Edit
                                </button>
                            </div>
                            <div class="review-grid">
                                <div class="review-item">
                                    <span class="review-label">Full Name</span>
                                    <span class="review-val" id="review_full_name">—</span>
                                </div>
                                <div class="review-item">
                                    <span class="review-label">Contact Number</span>
                                    <span class="review-val" id="review_contact_number">—</span>
                                </div>
                                <div class="review-item">
                                    <span class="review-label">Profile Photo</span>
                                    <div id="review_profile_photo_wrap" class="d-flex align-items-center gap-2 mt-1">
                                        <span class="review-val" id="review_profile_photo">Not provided</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 3. Demographics & Location -->
                        <div class="review-card">
                            <div class="review-header">
                                <h3 class="review-section-title"><i class="bi bi-geo-alt me-1"></i>Demographics & Address</h3>
                                <button type="button" class="btn-review-edit" data-edit-panel="2">
                                    <i class="bi bi-pencil-square"></i>Edit
                                </button>
                            </div>
                            <div class="review-grid">
                                <div class="review-item">
                                    <span class="review-label">Birthdate</span>
                                    <span class="review-val" id="review_birth_date">—</span>
                                </div>
                                <div class="review-item">
                                    <span class="review-label">Gender</span>
                                    <span class="review-val" id="review_gender">—</span>
                                </div>
                                <div class="review-item" style="grid-column: 1 / -1;">
                                    <span class="review-label">Permanent Home Address</span>
                                    <span class="review-val" id="review_street_address">—</span>
                                </div>
                            </div>
                        </div>

                        <!-- 4. Academic Standing -->
                        <div class="review-card">
                            <div class="review-header">
                                <h3 class="review-section-title"><i class="bi bi-mortarboard me-1"></i>Academic Information</h3>
                                <button type="button" class="btn-review-edit" data-edit-panel="3">
                                    <i class="bi bi-pencil-square"></i>Edit
                                </button>
                            </div>
                            <div class="review-grid">
                                <div class="review-item">
                                    <span class="review-label">College</span>
                                    <span class="review-val" id="review_college">—</span>
                                </div>
                                <div class="review-item">
                                    <span class="review-label">Program</span>
                                    <span class="review-val" id="review_program">—</span>
                                </div>
                                <div class="review-item">
                                    <span class="review-label">Year Level</span>
                                    <span class="review-val" id="review_year_level">—</span>
                                </div>
                            </div>
                        </div>

                        <!-- 5. Academic Verification Documents -->
                        <div class="review-card">
                            <div class="review-header">
                                <h3 class="review-section-title"><i class="bi bi-file-earmark-check me-1"></i>Academic Verification</h3>
                                <button type="button" class="btn-review-edit" data-edit-panel="4">
                                    <i class="bi bi-pencil-square"></i>Edit
                                </button>
                            </div>
                            <div class="review-grid">
                                <div class="review-item">
                                    <span class="review-label">School ID</span>
                                    <div id="review_school_id_wrap" class="d-flex align-items-center gap-2 mt-1">
                                        <span class="review-val" id="review_school_id">Not provided</span>
                                    </div>
                                </div>
                                <div class="review-item">
                                    <span class="review-label">Enrollment Proof Type</span>
                                    <span class="review-val" id="review_enrollment_proof_type">—</span>
                                </div>
                                <div class="review-item" style="grid-column: 1 / -1;">
                                    <span class="review-label">COR / COE Document</span>
                                    <div id="review_enrollment_proof_wrap" class="d-flex align-items-center gap-2 mt-1">
                                        <span class="review-val" id="review_enrollment_proof_file">—</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 6. Safety & Emergency Contacts -->
                        <div class="review-card">
                            <div class="review-header">
                                <h3 class="review-section-title"><i class="bi bi-shield-plus me-1"></i>Emergency Contact</h3>
                                <button type="button" class="btn-review-edit" data-edit-panel="5">
                                    <i class="bi bi-pencil-square"></i>Edit
                                </button>
                            </div>
                            <div class="review-grid">
                                <div class="review-item">
                                    <span class="review-label">Parent / Guardian Name</span>
                                    <span class="review-val" id="review_parent_name">—</span>
                                </div>
                                <div class="review-item">
                                    <span class="review-label">Parent Contact Number</span>
                                    <span class="review-val" id="review_parent_number">—</span>
                                </div>
                                <div class="review-item" style="grid-column: 1 / -1;">
                                    <span class="review-label">Parent Permanent Address</span>
                                    <span class="review-val" id="review_parent_address">—</span>
                                </div>
                                <div class="review-item">
                                    <span class="review-label">Emergency Contact Name</span>
                                    <span class="review-val" id="review_emergency_name">—</span>
                                </div>
                                <div class="review-item">
                                    <span class="review-label">Emergency Mobile Number</span>
                                    <span class="review-val" id="review_emergency_number">—</span>
                                </div>
                                <div class="review-item">
                                    <span class="review-label">Relationship to Student</span>
                                    <span class="review-val" id="review_emergency_relationship">—</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="question-actions">
                        <button type="button" class="btn btn-prev btn-prev-question" data-target="5">
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
            const studentIdFeedback = document.getElementById('student_id_feedback');
            const checkStudentIdUrl = @json(route('student.setup.check_student_id'));
            const initialStudentId = @json(trim((string) ($user->student_id ?? '')));
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

            // Identity Protection & Recovery DOM References
            const conflictCard = document.getElementById('studentIdConflictCard');
            const assistFormWrap = document.getElementById('assistanceRequestFormWrap');
            const assistStudentIdInput = document.getElementById('assist_student_id');
            const confirmedStudentIdDisplay = document.getElementById('confirmedStudentIdDisplay');
            const toggleAssistanceBtn = document.getElementById('toggleAssistanceFormBtn');
            const dismissConflictBtn = document.getElementById('dismissConflictBtn');
            const cancelAssistanceBtn = document.getElementById('cancelAssistanceBtn');
            const submitAssistanceBtn = document.getElementById('submitAssistanceBtn');
            const assistAlert = document.getElementById('assistanceAlert');
            const btnEditStudentId = document.getElementById('btnEditStudentId');
            const recoveryRequestUrl = @json(route('student.setup.recovery_request'));

            const showConflictCard = function (val) {
                if (conflictCard) {
                    conflictCard.classList.remove('d-none');
                }
                if (assistStudentIdInput) {
                    assistStudentIdInput.value = val;
                }
            };

            const hideConflictCard = function () {
                if (conflictCard) {
                    conflictCard.classList.add('d-none');
                }
                if (assistFormWrap) {
                    assistFormWrap.classList.add('d-none');
                }
            };

            // --- Student ID Exact-Match Availability State ---
            let currentStudentIdCheckState = initialStudentId ? 'available' : 'idle';
            let lastCheckedStudentId = initialStudentId;
            let studentIdAbortController = null;
            let studentIdRequestId = 0;
            let activeCheckPromise = null;

            const renderStudentIdFeedback = function (state, message, showRetry = false) {
                if (!studentIdFeedback || !studentIdInput) return;
                studentIdInput.classList.remove('is-valid', 'is-invalid');

                if (state === 'checking') {
                    studentIdFeedback.innerHTML = `<span class="spinner-border spinner-border-sm me-1 text-muted" role="status" aria-hidden="true"></span><span class="text-muted">${message}</span>`;
                } else if (state === 'available') {
                    studentIdInput.classList.add('is-valid');
                    studentIdFeedback.innerHTML = `<i class="bi bi-check-circle-fill text-success me-1"></i><span class="text-success fw-semibold">${message}</span>`;
                    hideConflictCard();
                } else if (state === 'taken') {
                    studentIdInput.classList.add('is-invalid');
                    studentIdFeedback.innerHTML = `<i class="bi bi-x-circle-fill text-danger me-1"></i><span class="text-danger fw-semibold">${message}</span>`;
                    showConflictCard(lastCheckedStudentId || (studentIdInput.value || '').trim());
                } else if (state === 'invalid_format') {
                    studentIdInput.classList.add('is-invalid');
                    studentIdFeedback.innerHTML = `<i class="bi bi-exclamation-circle-fill text-danger me-1"></i><span class="text-danger">${message}</span>`;
                } else if (state === 'failed') {
                    studentIdInput.classList.add('is-invalid');
                    let html = `<i class="bi bi-exclamation-triangle-fill text-danger me-1"></i><span class="text-danger">${message}</span>`;
                    if (showRetry) {
                        html += `<button type="button" class="btn btn-link btn-sm p-0 ms-1 text-danger fw-bold text-decoration-underline" id="retryStudentIdCheckBtn">Retry</button>`;
                    }
                    studentIdFeedback.innerHTML = html;
                    const retryBtn = document.getElementById('retryStudentIdCheckBtn');
                    if (retryBtn) {
                        retryBtn.addEventListener('click', function (e) {
                            e.preventDefault();
                            performStudentIdCheck((studentIdInput.value || '').trim());
                        });
                    }
                } else {
                    studentIdFeedback.innerHTML = '';
                }
            };

            const performStudentIdCheck = function (rawVal) {
                const val = (rawVal || '').trim();
                if (val === '') {
                    currentStudentIdCheckState = 'idle';
                    lastCheckedStudentId = '';
                    renderStudentIdFeedback('idle', '');
                    return Promise.resolve({ available: false, state: 'idle' });
                }

                // Format validation: do NOT query database if format is incomplete/invalid
                if (!/^[A-Za-z0-9\-]{3,50}$/.test(val)) {
                    currentStudentIdCheckState = 'invalid_format';
                    renderStudentIdFeedback('invalid_format', 'Please enter a valid Student ID format (e.g., MCC2026-00000).');
                    return Promise.resolve({ available: false, state: 'invalid_format' });
                }

                // If this is the student's own existing ID already assigned to them
                if (initialStudentId && val.toLowerCase() === initialStudentId.toLowerCase()) {
                    currentStudentIdCheckState = 'available';
                    lastCheckedStudentId = val;
                    renderStudentIdFeedback('available', 'Student ID is available.');
                    return Promise.resolve({ available: true, state: 'available' });
                }

                if (studentIdAbortController) {
                    studentIdAbortController.abort();
                }
                studentIdAbortController = new AbortController();
                const reqId = ++studentIdRequestId;

                currentStudentIdCheckState = 'checking';
                renderStudentIdFeedback('checking', 'Checking Student ID...');

                const token = document.querySelector('input[name="_token"]')?.value || '';

                activeCheckPromise = fetch(checkStudentIdUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': token,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ student_id: val }),
                    signal: studentIdAbortController.signal
                })
                .then(async function (response) {
                    if (reqId !== studentIdRequestId) return { available: false, state: 'stale' };

                    const data = await response.json().catch(() => ({}));

                    if (response.status === 422) {
                        currentStudentIdCheckState = data.status === 'taken' ? 'taken' : 'invalid_format';
                        renderStudentIdFeedback(currentStudentIdCheckState, data.message || 'Please enter a valid Student ID format (e.g., MCC2026-00000).');
                        return { available: false, state: currentStudentIdCheckState };
                    }

                    if (response.status === 429) {
                        currentStudentIdCheckState = 'failed';
                        renderStudentIdFeedback('failed', 'Too many check attempts. Please wait a moment and try again.', true);
                        return { available: false, state: 'failed' };
                    }

                    if (!response.ok) {
                        throw new Error('HTTP ' + response.status);
                    }

                    if (reqId !== studentIdRequestId) return { available: false, state: 'stale' };

                    if (data.available) {
                        currentStudentIdCheckState = 'available';
                        lastCheckedStudentId = val;
                        renderStudentIdFeedback('available', data.message || 'Student ID is available.');
                        return { available: true, state: 'available' };
                    } else {
                        currentStudentIdCheckState = data.status === 'invalid_format' ? 'invalid_format' : 'taken';
                        renderStudentIdFeedback(currentStudentIdCheckState, data.message || 'This Student ID is already registered.');
                        return { available: false, state: currentStudentIdCheckState };
                    }
                })
                .catch(function (err) {
                    if (err.name === 'AbortError') {
                        return { available: false, state: 'aborted' };
                    }
                    if (reqId !== studentIdRequestId) return { available: false, state: 'stale' };

                    currentStudentIdCheckState = 'failed';
                    renderStudentIdFeedback('failed', 'Unable to check Student ID right now. ', true);
                    return { available: false, state: 'failed' };
                })
                .finally(function () {
                    if (reqId === studentIdRequestId) {
                        activeCheckPromise = null;
                    }
                });

                return activeCheckPromise;
            };

            if (studentIdInput) {
                // While typing: normal uninterrupted typing, no network calls, invalidate previous check
                studentIdInput.addEventListener('input', function () {
                    if (studentIdAbortController) {
                        studentIdAbortController.abort();
                    }
                    activeCheckPromise = null;
                    currentStudentIdCheckState = 'idle';
                    lastCheckedStudentId = '';
                    studentIdInput.classList.remove('is-valid', 'is-invalid');
                    if (studentIdFeedback) {
                        studentIdFeedback.innerHTML = '';
                    }
                    hideConflictCard();
                });

                // Validate ONLY on blur
                studentIdInput.addEventListener('blur', function () {
                    const val = (studentIdInput.value || '').trim();

                    if (val === '') {
                        if (studentIdAbortController) studentIdAbortController.abort();
                        activeCheckPromise = null;
                        currentStudentIdCheckState = 'idle';
                        lastCheckedStudentId = '';
                        studentIdInput.classList.remove('is-valid', 'is-invalid');
                        if (studentIdFeedback) studentIdFeedback.innerHTML = '';
                        return;
                    }

                    // Complete format check before database lookup
                    if (!/^[A-Za-z0-9\-]{3,50}$/.test(val)) {
                        if (studentIdAbortController) studentIdAbortController.abort();
                        activeCheckPromise = null;
                        currentStudentIdCheckState = 'invalid_format';
                        renderStudentIdFeedback('invalid_format', 'Please enter a valid Student ID format (e.g., MCC2026-00000).');
                        return;
                    }

                    // If already verified for this exact value, do not query again
                    if (val.toLowerCase() === lastCheckedStudentId.toLowerCase() && (currentStudentIdCheckState === 'available' || currentStudentIdCheckState === 'taken')) {
                        return;
                    }

                    performStudentIdCheck(val);
                });
            }

            // Assistance Form & Conflict Card Listeners
            if (dismissConflictBtn) {
                dismissConflictBtn.addEventListener('click', function () {
                    hideConflictCard();
                    if (studentIdInput) {
                        studentIdInput.focus();
                        studentIdInput.select();
                    }
                });
            }

            if (toggleAssistanceBtn && assistFormWrap) {
                toggleAssistanceBtn.addEventListener('click', function () {
                    assistFormWrap.classList.toggle('d-none');
                });
            }

            if (cancelAssistanceBtn && assistFormWrap) {
                cancelAssistanceBtn.addEventListener('click', function () {
                    assistFormWrap.classList.add('d-none');
                });
            }

            if (btnEditStudentId) {
                btnEditStudentId.addEventListener('click', function () {
                    moveToPanel(0);
                    if (studentIdInput) {
                        studentIdInput.focus();
                    }
                });
            }

            if (submitAssistanceBtn) {
                submitAssistanceBtn.addEventListener('click', async function () {
                    const email = (document.getElementById('assist_contact_email')?.value || '').trim();
                    const reason = (document.getElementById('assist_reason')?.value || '').trim();
                    const studentId = (document.getElementById('assist_student_id')?.value || '').trim();
                    const phone = (document.getElementById('assist_contact_phone')?.value || '').trim();
                    const docFile = document.getElementById('assist_document')?.files?.[0];

                    if (!email || !reason) {
                        if (assistAlert) {
                            assistAlert.className = 'alert alert-danger py-2 small';
                            assistAlert.textContent = 'Please provide your contact email and a reason for recovery.';
                            assistAlert.classList.remove('d-none');
                        }
                        return;
                    }

                    submitAssistanceBtn.disabled = true;
                    submitAssistanceBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>Submitting...';

                    const token = document.querySelector('input[name="_token"]')?.value || '';
                    const formData = new FormData();
                    formData.append('claimed_student_id', studentId);
                    formData.append('contact_email', email);
                    formData.append('contact_phone', phone);
                    formData.append('reason', reason);
                    if (docFile) {
                        formData.append('supporting_document', docFile);
                    }

                    try {
                        const resp = await fetch(recoveryRequestUrl, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': token,
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: formData
                        });

                        const data = await resp.json().catch(() => ({}));
                        if (resp.ok && data.success) {
                            if (assistAlert) {
                                assistAlert.className = 'alert alert-success py-2 small';
                                assistAlert.innerHTML = `<strong>Request Submitted!</strong> Reference <code>${data.tracking_number || ''}</code>. Campus administrators will review your student identity.`;
                                assistAlert.classList.remove('d-none');
                            }
                            const reasonEl = document.getElementById('assist_reason');
                            if (reasonEl) reasonEl.value = '';
                            submitAssistanceBtn.innerHTML = '<i class="bi bi-check-circle me-1"></i>Submitted';
                        } else {
                            if (assistAlert) {
                                assistAlert.className = 'alert alert-danger py-2 small';
                                assistAlert.textContent = data.message || 'Unable to submit request. Please try again.';
                                assistAlert.classList.remove('d-none');
                            }
                            submitAssistanceBtn.disabled = false;
                            submitAssistanceBtn.innerHTML = '<span>Submit Assistance Request</span><i class="bi bi-send ms-1"></i>';
                        }
                    } catch (e) {
                        if (assistAlert) {
                            assistAlert.className = 'alert alert-danger py-2 small';
                            assistAlert.textContent = 'Network error. Please try again.';
                            assistAlert.classList.remove('d-none');
                        }
                        submitAssistanceBtn.disabled = false;
                        submitAssistanceBtn.innerHTML = '<span>Submit Assistance Request</span><i class="bi bi-send ms-1"></i>';
                    }
                });
            }

            // --- Panel Navigation & Visibility ---
            let editingFromReview = false;

            const populateStudentReviewSummary = function () {
                // 1. Student Identity
                const revStudentId = document.getElementById('review_student_id');
                if (revStudentId && studentIdInput) {
                    const sidVal = (studentIdInput.value || '').trim();
                    if (sidVal) {
                        revStudentId.textContent = sidVal;
                        revStudentId.classList.remove('is-empty');
                    } else {
                        revStudentId.textContent = 'Not provided';
                        revStudentId.classList.add('is-empty');
                    }
                }

                // 2. Personal Details
                const revFullName = document.getElementById('review_full_name');
                const fullNameInput = form.querySelector('[name="full_name"]');
                if (revFullName && fullNameInput) {
                    const fnVal = (fullNameInput.value || '').trim();
                    revFullName.textContent = fnVal || '—';
                }

                const revContact = document.getElementById('review_contact_number');
                const contactInput = form.querySelector('[name="contact_number"]');
                if (revContact && contactInput) {
                    const cVal = (contactInput.value || '').trim();
                    revContact.textContent = cVal || '—';
                }

                const revPhotoWrap = document.getElementById('review_profile_photo_wrap');
                if (revPhotoWrap) {
                    if (profileInput && profileInput.files && profileInput.files[0]) {
                        const file = profileInput.files[0];
                        const url = URL.createObjectURL(file);
                        revPhotoWrap.innerHTML = `<img src="${url}" class="review-thumb" alt="Profile photo preview"><span class="review-val">${file.name}</span>`;
                    } else if (profilePreview && !profilePreview.classList.contains('d-none') && profilePreview.src) {
                        revPhotoWrap.innerHTML = `<img src="${profilePreview.src}" class="review-thumb" alt="Profile photo"><span class="review-val text-muted">Current photo on record</span>`;
                    } else {
                        revPhotoWrap.innerHTML = `<span class="review-val is-empty">Not provided</span>`;
                    }
                }

                // 3. Demographics & Address
                const revBirthDate = document.getElementById('review_birth_date');
                const birthInput = form.querySelector('[name="birth_date"]');
                if (revBirthDate && birthInput) {
                    const bVal = (birthInput.value || '').trim();
                    if (bVal) {
                        try {
                            const d = new Date(bVal + 'T00:00:00');
                            revBirthDate.textContent = d.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
                            revBirthDate.classList.remove('is-empty');
                        } catch (e) {
                            revBirthDate.textContent = bVal;
                        }
                    } else {
                        revBirthDate.textContent = 'Not provided';
                        revBirthDate.classList.add('is-empty');
                    }
                }

                const revGender = document.getElementById('review_gender');
                if (revGender && genderSelect) {
                    let gVal = genderSelect.value;
                    if (gVal === 'Other' && genderCustomInput && genderCustomInput.value.trim()) {
                        gVal = genderCustomInput.value.trim();
                    }
                    revGender.textContent = gVal || '—';
                }

                const revAddress = document.getElementById('review_street_address');
                if (revAddress && homeAddressInput) {
                    revAddress.textContent = (homeAddressInput.value || '').trim() || '—';
                }

                // 4. Academic Standing
                const revCollege = document.getElementById('review_college');
                if (revCollege && collegeSelect) {
                    const selOpt = collegeSelect.selectedOptions[0];
                    revCollege.textContent = selOpt && selOpt.value ? selOpt.textContent.trim() : (collegeSelect.value || '—');
                }

                const revProgram = document.getElementById('review_program');
                if (revProgram && programSelect) {
                    revProgram.textContent = programSelect.value || '—';
                }

                const revYearLevel = document.getElementById('review_year_level');
                if (revYearLevel && yearLevelInput) {
                    revYearLevel.textContent = yearLevelInput.value || '—';
                }

                // 5. Academic Verification
                const revSchoolIdWrap = document.getElementById('review_school_id_wrap');
                if (revSchoolIdWrap) {
                    if (schoolIdInput && schoolIdInput.files && schoolIdInput.files[0]) {
                        const file = schoolIdInput.files[0];
                        const url = URL.createObjectURL(file);
                        revSchoolIdWrap.innerHTML = `<img src="${url}" class="review-thumb" alt="School ID preview"><span class="review-val">${file.name}</span>`;
                    } else if (schoolIdPreview && !schoolIdPreview.classList.contains('d-none') && schoolIdPreview.src) {
                        revSchoolIdWrap.innerHTML = `<img src="${schoolIdPreview.src}" class="review-thumb" alt="School ID"><span class="review-val text-muted">Current file on record</span>`;
                    } else {
                        revSchoolIdWrap.innerHTML = `<span class="review-val is-empty">Not provided</span>`;
                    }
                }

                const revProofType = document.getElementById('review_enrollment_proof_type');
                if (revProofType && enrollmentProofType) {
                    const sel = enrollmentProofType.selectedOptions[0];
                    revProofType.textContent = sel && sel.value ? sel.textContent.trim() : (enrollmentProofType.value ? enrollmentProofType.value.toUpperCase() : '—');
                }

                const revProofWrap = document.getElementById('review_enrollment_proof_wrap');
                if (revProofWrap) {
                    if (enrollmentProofFile && enrollmentProofFile.files && enrollmentProofFile.files[0]) {
                        const file = enrollmentProofFile.files[0];
                        revProofWrap.innerHTML = `<i class="bi bi-file-earmark-check text-success fs-5"></i><span class="review-val">${file.name}</span>`;
                    } else if (enrollmentProofFile && enrollmentProofFile.dataset.hasExisting === '1') {
                        revProofWrap.innerHTML = `<i class="bi bi-file-earmark-check text-success fs-5"></i><span class="review-val text-muted">Current proof document on record</span>`;
                    } else {
                        revProofWrap.innerHTML = `<span class="review-val is-empty">Not provided</span>`;
                    }
                }

                // 6. Safety & Emergency Contacts
                const revParentName = document.getElementById('review_parent_name');
                const parentNameInput = form.querySelector('[name="parent_contact_name"]');
                if (revParentName && parentNameInput) revParentName.textContent = (parentNameInput.value || '').trim() || '—';

                const revParentNum = document.getElementById('review_parent_number');
                const parentNumInput = form.querySelector('[name="parent_contact_number"]');
                if (revParentNum && parentNumInput) revParentNum.textContent = (parentNumInput.value || '').trim() || '—';

                const revParentAddr = document.getElementById('review_parent_address');
                const parentAddrInput = form.querySelector('[name="parent_contact_address"]');
                if (revParentAddr && parentAddrInput) revParentAddr.textContent = (parentAddrInput.value || '').trim() || '—';

                const revEmergName = document.getElementById('review_emergency_name');
                const emergNameInput = form.querySelector('[name="emergency_contact_name"]');
                if (revEmergName && emergNameInput) revEmergName.textContent = (emergNameInput.value || '').trim() || '—';

                const revEmergNum = document.getElementById('review_emergency_number');
                const emergNumInput = form.querySelector('[name="emergency_contact_number"]');
                if (revEmergNum && emergNumInput) revEmergNum.textContent = (emergNumInput.value || '').trim() || '—';

                const revEmergRel = document.getElementById('review_emergency_relationship');
                const emergRelInput = form.querySelector('[name="emergency_contact_relationship"]');
                if (revEmergRel && emergRelInput) revEmergRel.textContent = (emergRelInput.value || '').trim() || '—';
            };

            const updateActivePanel = function () {
                questionPanels.forEach((panel, index) => {
                    panel.classList.toggle('active', index === currentPanel);
                });
                window.scrollTo({ top: 0, behavior: 'smooth' });
            };

            const moveToPanel = function (targetIndex) {
                currentPanel = Math.max(0, Math.min(questionPanels.length - 1, targetIndex));
                if (currentPanel === 6) {
                    populateStudentReviewSummary();
                    editingFromReview = false;
                    document.querySelectorAll('.btn-save-return-review').forEach((b) => b.classList.add('d-none'));
                }
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

                // 2. Student ID availability check state validation
                if (field === studentIdInput && value !== '') {
                    if (!/^[A-Za-z0-9\-]{3,50}$/.test(value)) {
                        renderStudentIdFeedback('invalid_format', 'Please enter a valid Student ID format (e.g., MCC2026-00000).');
                        return false;
                    }
                    if (currentStudentIdCheckState === 'taken') {
                        renderStudentIdFeedback('taken', 'This Student ID is already registered.');
                        return false;
                    }
                    if (currentStudentIdCheckState === 'invalid_format') {
                        renderStudentIdFeedback('invalid_format', 'Please enter a valid Student ID format (e.g., MCC2026-00000).');
                        return false;
                    }
                    if (currentStudentIdCheckState === 'failed') {
                        renderStudentIdFeedback('failed', 'Unable to check Student ID right now. ', true);
                        return false;
                    }
                    if (currentStudentIdCheckState === 'checking') {
                        renderStudentIdFeedback('checking', 'Checking Student ID availability, please wait...');
                        return false;
                    }
                }

                // 3. Format checks if file is selected
                if (field.type === 'file' && field.files && field.files.length > 0) {
                    const file = field.files[0];
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

                // 4. Philippine mobile phone validation
                if (field.classList.contains('ph-contact-number')) {
                    if (value !== '' && !/^09\d{9}$/.test(value)) {
                        showFieldError(field, 'Use 11-digit Philippine mobile format: 09XXXXXXXXX.');
                        return false;
                    }
                }

                // 5. HTML5 Constraint Validation
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
                button.addEventListener('click', async function (e) {
                    e.preventDefault();
                    if (button.disabled) return;

                    // If advancing from Student ID Verification panel (Panel 0)
                    if (currentPanel === 0 && studentIdInput) {
                        const val = (studentIdInput.value || '').trim();
                        if (val !== '') {
                            // Validate COMPLETE input format first
                            if (!/^[A-Za-z0-9\-]{3,50}$/.test(val)) {
                                currentStudentIdCheckState = 'invalid_format';
                                renderStudentIdFeedback('invalid_format', 'Please enter a valid Student ID format (e.g., MCC2026-00000).');
                                studentIdInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
                                try { studentIdInput.focus({ preventScroll: true }); } catch (err) {}
                                return;
                            }

                            // If checking is currently in flight, wait for it
                            if (activeCheckPromise) {
                                await activeCheckPromise;
                            } else if (currentStudentIdCheckState !== 'available' || val.toLowerCase() !== lastCheckedStudentId.toLowerCase()) {
                                await performStudentIdCheck(val);
                            }

                            if (currentStudentIdCheckState !== 'available') {
                                studentIdInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
                                try { studentIdInput.focus({ preventScroll: true }); } catch (err) {}
                                return;
                            }
                        } else {
                            currentStudentIdCheckState = 'idle';
                            lastCheckedStudentId = '';
                            studentIdInput.classList.remove('is-valid', 'is-invalid');
                            if (studentIdFeedback) studentIdFeedback.innerHTML = '';
                            hideConflictCard();
                        }
                    }

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

            // --- Edit Section & Save-Return Listeners ---
            document.querySelectorAll('.btn-review-edit').forEach((button) => {
                button.addEventListener('click', function (e) {
                    e.preventDefault();
                    const targetIndex = Number(button.getAttribute('data-edit-panel'));
                    editingFromReview = true;
                    document.querySelectorAll('.btn-save-return-review').forEach((b) => b.classList.remove('d-none'));
                    moveToPanel(targetIndex);
                });
            });

            document.querySelectorAll('.btn-save-return-review').forEach((button) => {
                button.addEventListener('click', async function (e) {
                    e.preventDefault();

                    // If saving from Student ID panel (Panel 0)
                    if (currentPanel === 0 && studentIdInput) {
                        const val = (studentIdInput.value || '').trim();
                        if (val !== '') {
                            if (!/^[A-Za-z0-9\-]{3,50}$/.test(val)) {
                                currentStudentIdCheckState = 'invalid_format';
                                renderStudentIdFeedback('invalid_format', 'Please enter a valid Student ID format (e.g., MCC2026-00000).');
                                studentIdInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
                                try { studentIdInput.focus({ preventScroll: true }); } catch (err) {}
                                return;
                            }

                            if (activeCheckPromise) {
                                await activeCheckPromise;
                            } else if (currentStudentIdCheckState !== 'available' || val.toLowerCase() !== lastCheckedStudentId.toLowerCase()) {
                                await performStudentIdCheck(val);
                            }

                            if (currentStudentIdCheckState !== 'available') {
                                studentIdInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
                                try { studentIdInput.focus({ preventScroll: true }); } catch (err) {}
                                return;
                            }
                        } else {
                            currentStudentIdCheckState = 'idle';
                            lastCheckedStudentId = '';
                            studentIdInput.classList.remove('is-valid', 'is-invalid');
                            if (studentIdFeedback) studentIdFeedback.innerHTML = '';
                            hideConflictCard();
                        }
                    }

                    if (!validatePanel(currentPanel)) {
                        return;
                    }

                    moveToPanel(6);
                });
            });

            // Automatically clear client validation on user edit
            if (form) {
                ['input', 'change'].forEach((eventName) => {
                    form.addEventListener(eventName, function (e) {
                        if (e.target && e.target.matches('input, select, textarea')) {
                            if (e.target !== studentIdInput) {
                                clearFieldError(e.target);
                            }
                        }
                    });
                });

                form.addEventListener('submit', async function (event) {
                    if (studentIdInput) {
                        const val = (studentIdInput.value || '').trim();
                        if (val !== '') {
                            if (!/^[A-Za-z0-9\-]{3,50}$/.test(val)) {
                                currentStudentIdCheckState = 'invalid_format';
                                renderStudentIdFeedback('invalid_format', 'Please enter a valid Student ID format (e.g., MCC2026-00000).');
                                moveToPanel(0);
                                studentIdInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
                                try { studentIdInput.focus({ preventScroll: true }); } catch (err) {}
                                event.preventDefault();
                                return;
                            }

                            if (activeCheckPromise) {
                                await activeCheckPromise;
                            } else if (currentStudentIdCheckState !== 'available' || val.toLowerCase() !== lastCheckedStudentId.toLowerCase()) {
                                await performStudentIdCheck(val);
                            }

                            if (currentStudentIdCheckState !== 'available') {
                                moveToPanel(0);
                                studentIdInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
                                try { studentIdInput.focus({ preventScroll: true }); } catch (err) {}
                                event.preventDefault();
                                return;
                            }
                        }
                    }

                    for (let i = 0; i < 6; i++) {
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

            // --- Academic Proof Sync ---
            const syncAcademicProofRequirements = function () {
                // Student ID is optional across all year levels at initial onboarding
                if (studentIdInput) {
                    studentIdInput.required = false;
                }
                if (studentIdLabel) {
                    studentIdLabel.textContent = 'Student ID';
                }

                if (schoolIdLabel) {
                    schoolIdLabel.textContent = 'School ID Upload';
                }
                if (schoolIdInput) {
                    schoolIdInput.required = false;
                }
                if (schoolIdHelp) {
                    schoolIdHelp.textContent = 'Upload a clear photo of your school ID if available.';
                }

                if (enrollmentProofFile) {
                    const hasExistingEnrollmentProof = enrollmentProofFile.dataset.hasExisting === '1';
                    enrollmentProofFile.required = !hasExistingEnrollmentProof;
                }

                if (enrollmentProofType) {
                    enrollmentProofType.required = true;
                }

                if (enrollmentProofHelp) {
                    enrollmentProofHelp.textContent = 'Accepted: PDF, JPG, PNG, WEBP.';
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
