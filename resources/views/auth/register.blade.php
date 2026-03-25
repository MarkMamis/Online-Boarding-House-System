<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Online Boarding House System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --brand: #0ea5a3;
            --brand-dark: #0b7f7e;
            --brand-rgb: 14,165,163;
            --brand-dark-rgb: 11,127,126;
        }
        body { font-family: 'Inter', system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif; }
        .auth-wrapper {
            min-height: 100vh;
            position: relative;
            overflow: hidden;
        }
        .auth-wrapper:before {
            content: "";
            position: absolute;
            inset: 0;
            background-image: url("{{ asset('images/MinSU-Calapan.jpg') }}");
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            filter: saturate(1.08) contrast(1.08);
            transform: scale(1.01);
        }
        .auth-wrapper:after {
            content: "";
            position: absolute;
            inset: 0;
            background:
                radial-gradient(900px circle at 10% 0%, rgba(var(--brand-rgb), .30), transparent 55%),
                radial-gradient(800px circle at 100% 18%, rgba(var(--brand-rgb), .18), transparent 50%),
                linear-gradient(120deg, rgba(2,8,20,.60), rgba(2,8,20,.26));
        }
        .auth-wrapper > .row { position: relative; z-index: 1; }
        .hero-home-link {
            color: rgba(255,255,255,.88);
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            padding: .35rem .7rem;
            border-radius: 999px;
            background: rgba(255,255,255,.10);
            border: 1px solid rgba(255,255,255,.16);
            line-height: 1;
        }
        .hero-home-link:hover { color: #fff; text-decoration: underline; text-underline-offset: 3px; }
        .hero-pane {
            background: transparent;
            position: relative;
            min-height: 240px;
        }
        .hero-content { position: relative; z-index: 1; color: #fff; }
        .hero-content h1 { text-shadow: 0 12px 30px rgba(2,8,20,.35); }
        .hero-content p { text-shadow: 0 10px 24px rgba(2,8,20,.28); }
        .hero-list { margin-top: 1.25rem; }
        .hero-list li { display: flex; align-items: flex-start; gap: .7rem; margin-bottom: .65rem; }
        .hero-list .hero-ic {
            width: 34px;
            height: 34px;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(255,255,255,.12);
            border: 1px solid rgba(255,255,255,.18);
            color: rgba(255,255,255,.92);
            flex: 0 0 auto;
            margin-top: .05rem;
        }
        .brand-badge { background: rgba(255,255,255,.12); border: 1px solid rgba(255,255,255,.20); }
        .card {
            border: 1px solid rgba(255,255,255,.22);
            border-top: 4px solid rgba(var(--brand-rgb), .55);
            background: rgba(255,255,255,.12);
            box-shadow: 0 22px 55px rgba(2,8,20,.18);
            overflow: hidden;
            backdrop-filter: blur(12px) saturate(1.10);
            -webkit-backdrop-filter: blur(12px) saturate(1.10);
            color: #fff;
        }

        .card .text-muted { color: rgba(255,255,255,.72) !important; }
        .form-label { font-weight: 600; color: rgba(255,255,255,.88); }
        .field-icon { background: rgba(255,255,255,.12); color: rgba(255,255,255,.88); }
        .input-group-text.field-icon { border-color: rgba(255,255,255,.18); }
        .form-control, .form-select {
            background: rgba(255,255,255,.10);
            border-color: rgba(255,255,255,.22);
            color: #fff;
            transition: all .18s ease;
        }
        /* Make native select dropdown options readable */
        .form-select option {
            color: #0f172a;
            background: #fff;
        }
        .form-control::placeholder { color: rgba(255,255,255,.55); }
        .form-text { color: rgba(255,255,255,.65); }
        .form-check-label { color: rgba(255,255,255,.82); }
        .card a { color: rgba(255,255,255,.92); }
        .card a:hover { color: rgba(255,255,255,1); }

        .card .card-header { background: transparent; border-bottom: 0; }
        .form-control:focus, .form-select:focus { 
            border-color: var(--brand); 
            box-shadow: 0 0 0 .25rem rgba(var(--brand-rgb), .18);
            background: rgba(255,255,255,.14);
        }
        .field-icon { width: 40px; height: 40px; display:inline-flex; align-items:center; justify-content:center; border-radius: .5rem; }
        .auth-logo {
            width: 56px;
            height: 56px;
            border-radius: 18px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(var(--brand-rgb), .12);
            border: 1px solid rgba(var(--brand-rgb), .20);
            color: var(--brand-dark);
            box-shadow: 0 16px 34px rgba(2,8,20,.10);
        }
        .btn-brand { 
            background: var(--brand); 
            border-color: var(--brand);
            font-weight: 600;
            letter-spacing: .02em;
            transition: all .2s ease;
            padding: .65rem 1.5rem;
        }
        .btn-brand:hover { 
            background: var(--brand-dark); 
            border-color: var(--brand-dark);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(var(--brand-rgb), .25);
        }
        .card a { color: var(--brand-dark); }
        .card a:hover { color: var(--brand); }

        /* Form sections styling */
        .form-section-label {
            font-size: .75rem;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: rgba(255,255,255,.55);
            font-weight: 700;
            display: block;
            margin-bottom: .5rem;
            margin-top: .75rem;
            padding-bottom: .35rem;
            border-bottom: 1px solid rgba(255,255,255,.10);
        }
        .form-section-label:first-child { margin-top: 0; }

        /* Gender radio buttons styling */
        .gender-options {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: .6rem;
            margin-bottom: .5rem;
        }
        .gender-option {
            min-width: 0;
        }
        .gender-option input[type="radio"] {
            display: none;
        }
        .gender-option label {
            display: block;
            padding: .5rem .75rem;
            border: 1px solid rgba(255,255,255,.22);
            border-radius: .5rem;
            background: rgba(255,255,255,.08);
            cursor: pointer;
            transition: all .18s ease;
            color: rgba(255,255,255,.88);
            margin: 0;
            text-align: center;
            font-weight: 500;
            white-space: nowrap;
            font-size: .9rem;
        }
        .gender-option input[type="radio"]:checked + label {
            border-color: var(--brand);
            background: rgba(var(--brand-rgb), .20);
            color: #fff;
            box-shadow: 0 0 0 2px rgba(var(--brand-rgb), .30);
        }
        .gender-option label:hover {
            border-color: var(--brand);
            background: rgba(var(--brand-rgb), .12);
        }
        .gender-custom-field {
            display: none;
            margin-top: .5rem;
        }
        .gender-custom-field.show {
            display: block;
        }
        .gender-custom-field .form-control {
            padding: .55rem .75rem;
            font-size: .9rem;
        }

        @media (min-width: 768px) {
            .gender-options { grid-template-columns: repeat(4, minmax(0, 1fr)); }
        }

        @media (min-width: 992px) {
            .hero-pane { min-height: 100vh; }
        }
    </style>
    <noscript>
        <style>
            .auth-wrapper:before { background-image: url("{{ asset('images/MinSU-Calapan.jpg') }}"); }
        </style>
    </noscript>
    <!-- If the image is missing, show a subtle gradient background on the left -->
    <script>
        window.addEventListener('load', function(){
            const img = new Image();
            img.onerror = () => document.querySelector('.auth-wrapper')?.classList.add('bg-gradient');
            img.src = "{{ asset('images/MinSU-Calapan.jpg') }}";
        });
    </script>
</head>
<body class="bg-dark">
    <div class="container-fluid auth-wrapper">
        <div class="row g-0 h-100">
            <!-- Left hero pane with building image -->
            <div class="col-12 col-lg-6 hero-pane">
                <div class="d-flex flex-column justify-content-between h-100 p-4 p-lg-5 hero-content">
                    <div>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <a href="{{ route('landing') }}" class="hero-home-link" aria-label="Go to home">
                                <i class="bi bi-arrow-left"></i> Home
                            </a>
                            <span class="badge brand-badge text-white rounded-pill px-3 py-2">Online Boarding House</span>
                        </div>
                        <h1 class="display-5 fw-bold mt-4">Find your next home away from home.</h1>
                        <p class="lead opacity-75">Create your account to manage rooms, tenants, and bookings with a modern, simple experience.</p>

                        <ul class="list-unstyled hero-list mb-0 opacity-90">
                            <li>
                                <span class="hero-ic"><i class="bi bi-person-check"></i></span>
                                <div>
                                    <div class="fw-semibold">Student & Landlord ready</div>
                                    <div class="small opacity-75">Choose your role and get the right tools.</div>
                                </div>
                            </li>
                            <li>
                                <span class="hero-ic"><i class="bi bi-journal-check"></i></span>
                                <div>
                                    <div class="fw-semibold">Simple booking requests</div>
                                    <div class="small opacity-75">Send requests and track updates easily.</div>
                                </div>
                            </li>
                            <li>
                                <span class="hero-ic"><i class="bi bi-geo-alt"></i></span>
                                <div>
                                    <div class="fw-semibold">Location‑aware browsing</div>
                                    <div class="small opacity-75">Find places near campus with map support.</div>
                                </div>
                            </li>
                        </ul>
                    </div>
                    <div class="opacity-75 small d-none d-lg-block">Online Boarding House System</div>
                </div>
            </div>

            <!-- Right form pane -->
            <div class="col-lg-6 d-flex align-items-center justify-content-center p-4 p-lg-5">
                <div class="w-100" style="max-width: 740px;">
                    <div class="card rounded-4">
                        <div class="card-body p-4 p-lg-5">
                            <div class="mb-4 text-center">
                                <h2 class="fw-bold mb-1">Create your account</h2>
                                <p class="text-muted mb-0">Already have one? <a href="{{ route('login') }}" class="text-white text-decoration-none fw-semibold mb-0">Sign in</a></p>
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

                            <form method="POST" action="{{ route('register') }}" novalidate>
                                @csrf

                                <div class="row g-2">
                                    <!-- Account Information Section -->
                                    <div class="col-12">
                                        <span class="form-section-label"><i class="bi bi-person-fill"></i> Account Information</span>
                                    </div>

                                    <div class="col-12">
                                        <label for="full_name" class="form-label">Full Name</label>
                                        <div class="input-group">
                                            <span class="input-group-text field-icon"><i class="bi bi-person"></i></span>
                                            <input type="text" class="form-control" id="full_name" name="full_name" value="{{ old('full_name') }}" placeholder="full name" required>
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <label for="email" class="form-label">Email</label>
                                        <div class="input-group">
                                            <span class="input-group-text field-icon"><i class="bi bi-envelope"></i></span>
                                            <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" placeholder="you@example.com" required>
                                        </div>
                                    </div>

                                    <!-- Security Section -->
                                    <div class="col-12">
                                        <span class="form-section-label"><i class="bi bi-shield-lock"></i> Security</span>
                                    </div>

                                    <div class="col-12 col-md-6">
                                        <label for="password" class="form-label">Password</label>
                                        <div class="input-group">
                                            <span class="input-group-text field-icon"><i class="bi bi-lock"></i></span>
                                            <input type="password" class="form-control" id="password" name="password" minlength="8" required>
                                            <span class="input-group-text field-icon" id="toggle_password" role="button" tabindex="0" aria-label="Show password" aria-controls="password">
                                                <i class="bi bi-eye" aria-hidden="true"></i>
                                            </span>
                                        </div>
                                        <div class="form-text">Minimum of 8 characters.</div>
                                    </div>

                                    <div class="col-12 col-md-6">
                                        <label for="password_confirmation" class="form-label">Confirm Password</label>
                                        <div class="input-group">
                                            <span class="input-group-text field-icon"><i class="bi bi-shield-lock"></i></span>
                                            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" minlength="8" required>
                                            <span class="input-group-text field-icon" id="toggle_password_confirmation" role="button" tabindex="0" aria-label="Show password" aria-controls="password_confirmation">
                                                <i class="bi bi-eye" aria-hidden="true"></i>
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Contact Information Section -->
                                    <div class="col-12">
                                        <span class="form-section-label"><i class="bi bi-telephone"></i> Contact Information</span>
                                    </div>

                                    <div class="col-12 col-md-6">
                                        <label for="contact_number" class="form-label">Contact Number</label>
                                        <div class="input-group">
                                            <span class="input-group-text field-icon"><i class="bi bi-telephone"></i></span>
                                            <input type="text" class="form-control" id="contact_number" name="contact_number" value="{{ old('contact_number') }}" placeholder="09XX-XXX-XXXX" required>
                                        </div>
                                    </div>

                                    <div class="col-12 d-none" id="gender_group">
                                        <label for="gender" class="form-label">Gender</label>
                                        <div class="gender-options">
                                            <div class="gender-option">
                                                <input type="radio" id="gender_male" name="gender" value="Male" {{ old('gender') == 'Male' ? 'checked' : '' }}>
                                                <label for="gender_male">Male</label>
                                            </div>
                                            <div class="gender-option">
                                                <input type="radio" id="gender_female" name="gender" value="Female" {{ old('gender') == 'Female' ? 'checked' : '' }}>
                                                <label for="gender_female">Female</label>
                                            </div>
                                            <div class="gender-option">
                                                <input type="radio" id="gender_other" name="gender" value="Other" {{ old('gender') == 'Other' ? 'checked' : '' }}>
                                                <label for="gender_other">Other</label>
                                            </div>
                                            <div class="gender-option">
                                                <input type="radio" id="gender_rather_not" name="gender" value="Rather not say" {{ old('gender') == 'Rather not say' ? 'checked' : '' }}>
                                                <label for="gender_rather_not">Rather not say</label>
                                            </div>
                                        </div>
                                        <div class="gender-custom-field {{ old('gender') == 'Other' ? 'show' : '' }}" id="genderCustomField">
                                            <input type="text" class="form-control" id="gender_custom" name="gender_custom" placeholder="Please specify" value="{{ old('gender_custom') }}">
                                        </div>
                                    </div>

                                    <!-- Student Fields Section -->
                                    <div class="col-12" id="student_section_label">
                                        <span class="form-section-label"><i class="bi bi-mortarboard"></i> Academic Information</span>
                                    </div>

                                    <div class="col-12 col-md-6" id="course_group">
                                        <label for="course" class="form-label">Course</label>
                                        <div class="input-group">
                                            <span class="input-group-text field-icon"><i class="bi bi-mortarboard"></i></span>
                                            <input type="text" class="form-control" id="course" name="course" value="{{ old('course') }}" placeholder="e.g., BSIT">
                                        </div>
                                    </div>

                                    <div class="col-12 col-md-6" id="year_level_group">
                                        <label for="year_level" class="form-label">Year Level</label>
                                        <div class="input-group">
                                            <span class="input-group-text field-icon"><i class="bi bi-123"></i></span>
                                            <select class="form-select" id="year_level" name="year_level">
                                                <option value="" @selected(old('year_level') === null || old('year_level') === '')>Select year level</option>
                                                <option value="1st Year" @selected(old('year_level') == '1st Year')>1st Year</option>
                                                <option value="2nd Year" @selected(old('year_level') == '2nd Year')>2nd Year</option>
                                                <option value="3rd Year" @selected(old('year_level') == '3rd Year')>3rd Year</option>
                                                <option value="4th Year" @selected(old('year_level') == '4th Year')>4th Year</option>
                                            </select>
                                        </div>
                                    </div>

                                    <!-- Landlord Fields Section -->
                                    <div class="col-12" id="landlord_section_label">
                                        <span class="form-section-label"><i class="bi bi-building"></i> Property Information</span>
                                    </div>

                                    <div class="col-12 col-md-6" id="boarding_house_group">
                                        <label for="boarding_house_name" class="form-label">Boarding House Name</label>
                                        <div class="input-group">
                                            <span class="input-group-text field-icon"><i class="bi bi-building"></i></span>
                                            <input type="text" class="form-control" id="boarding_house_name" name="boarding_house_name" value="{{ old('boarding_house_name') }}" placeholder="Green Dorms">
                                        </div>
                                    </div>

                                    <!-- Role Selection -->
                                    <div class="col-12">
                                        <label for="role" class="form-label">Role</label>
                                        <div class="row g-2">
                                            <div class="col-12 col-sm-4">
                                                <div class="form-check border rounded-3 p-3 h-100">
                                                    <input class="form-check-input" type="radio" name="role" id="role_student" value="student" {{ old('role', 'student') == 'student' ? 'checked' : '' }} required>
                                                    <label class="form-check-label d-block mt-1" for="role_student">
                                                        <strong>Student</strong>
                                                        <div class="text-muted small">Browse and book</div>
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-12 col-sm-4">
                                                <div class="form-check border rounded-3 p-3 h-100">
                                                    <input class="form-check-input" type="radio" name="role" id="role_landlord" value="landlord" {{ old('role') == 'landlord' ? 'checked' : '' }}>
                                                    <label class="form-check-label d-block mt-1" for="role_landlord">
                                                        <strong>Landlord</strong>
                                                        <div class="text-muted small">Manage properties</div>
                                                    </label>
                                                </div>
                                            </div>
                                            <!-- Admin signup is disabled to ensure only one admin and prevent elevation via self-register. -->
                                        </div>
                                    </div>

                                    <div class="col-12 mt-2">
                                        <button type="submit" class="btn btn-brand btn-lg w-100">Create account</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <p class="text-center text-white mt-3 mb-0 small">By creating an account, you agree to our Terms and Privacy Policy.</p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Password visibility toggle
        (function () {
            const passwords = [
                { input: 'password', toggle: 'toggle_password' },
                { input: 'password_confirmation', toggle: 'toggle_password_confirmation' }
            ];

            passwords.forEach(config => {
                const passwordInput = document.getElementById(config.input);
                const toggle = document.getElementById(config.toggle);
                if (!passwordInput || !toggle) return;

                const icon = toggle.querySelector('i');

                const applyState = (isVisible) => {
                    passwordInput.type = isVisible ? 'text' : 'password';
                    if (icon) {
                        icon.classList.toggle('bi-eye', !isVisible);
                        icon.classList.toggle('bi-eye-slash', isVisible);
                    }
                    toggle.setAttribute('aria-label', isVisible ? 'Hide password' : 'Show password');
                };

                const toggleVisibility = () => {
                    applyState(passwordInput.type === 'password');
                };

                toggle.addEventListener('click', toggleVisibility);
                toggle.addEventListener('keydown', (e) => {
                    if (e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        toggleVisibility();
                    }
                });
            });
        })();

        // Form role and field management
        (function () {
            const boardingGroup = document.getElementById('boarding_house_group');
            const boardingInput = document.getElementById('boarding_house_name');
            const landlordRadio = document.getElementById('role_landlord');
            const studentRadio = document.getElementById('role_student');

            const studentSectionLabel = document.getElementById('student_section_label');
            const landlordSectionLabel = document.getElementById('landlord_section_label');
            const courseGroup = document.getElementById('course_group');
            const courseInput = document.getElementById('course');
            const yearLevelGroup = document.getElementById('year_level_group');
            const yearLevelSelect = document.getElementById('year_level');
            const genderGroup = document.getElementById('gender_group');

            const genderRadios = document.querySelectorAll('input[name="gender"]');
            const genderOtherRadio = document.getElementById('gender_other');
            const genderCustomField = document.getElementById('genderCustomField');
            const genderCustomInput = document.getElementById('gender_custom');

            function syncRoleSections() {
                const isStudent = !!studentRadio?.checked;
                const isLandlord = !!landlordRadio?.checked;

                if (studentSectionLabel) studentSectionLabel.classList.toggle('d-none', !isStudent);
                if (courseGroup) courseGroup.classList.toggle('d-none', !isStudent);
                if (yearLevelGroup) yearLevelGroup.classList.toggle('d-none', !isStudent);
                if (genderGroup) genderGroup.classList.toggle('d-none', !isStudent);

                if (courseInput) {
                    courseInput.disabled = !isStudent;
                    courseInput.required = isStudent;
                }
                if (yearLevelSelect) {
                    yearLevelSelect.disabled = !isStudent;
                    yearLevelSelect.required = isStudent;
                }

                genderRadios.forEach((radio) => {
                    radio.disabled = !isStudent;
                    radio.required = isStudent;
                });

                if (landlordSectionLabel) landlordSectionLabel.classList.toggle('d-none', !isLandlord);
                if (boardingGroup) boardingGroup.classList.toggle('d-none', !isLandlord);
                if (boardingInput) {
                    boardingInput.disabled = !isLandlord;
                    boardingInput.required = isLandlord;
                }

                toggleGenderCustomField();
            }

            function toggleGenderCustomField() {
                const isStudent = !!studentRadio?.checked;
                const isOtherSelected = !!genderOtherRadio?.checked;
                const shouldShow = isStudent && isOtherSelected;

                if (genderCustomField) {
                    genderCustomField.classList.toggle('show', shouldShow);
                }

                if (genderCustomInput) {
                    genderCustomInput.disabled = !shouldShow;
                    genderCustomInput.required = shouldShow;
                    if (!shouldShow) {
                        genderCustomInput.value = '';
                    }
                }
            }

            document.querySelectorAll('input[name="role"]').forEach((el) => {
                el.addEventListener('change', syncRoleSections);
            });

            genderRadios.forEach((el) => {
                el.addEventListener('change', toggleGenderCustomField);
            });

            syncRoleSections();
        })();
    </script>
</body>
</html>