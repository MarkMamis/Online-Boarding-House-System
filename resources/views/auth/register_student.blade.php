<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register as Student - Online Boarding House System</title>
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
            background-image: url("{{ asset('images/minsu.png') }}");
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
        .auth-top {
            position: absolute;
            top: 1rem;
            right: 1rem;
            z-index: 2;
        }
        .auth-top a {
            color: rgba(255,255,255,.88);
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            padding: .4rem .75rem;
            border-radius: 999px;
            background: rgba(255,255,255,.10);
            border: 1px solid rgba(255,255,255,.16);
        }
        .auth-top a:hover { color: #fff; text-decoration: underline; text-underline-offset: 3px; }
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
        .form-control:focus, .form-select:focus { border-color: var(--brand); box-shadow: 0 0 0 .25rem rgba(var(--brand-rgb), .18); }
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
        .btn-brand { background: var(--brand); border-color: var(--brand); }
        .btn-brand:hover { background: var(--brand-dark); border-color: var(--brand-dark); }
        .card a { color: var(--brand-dark); }
        .card a:hover { color: var(--brand); }

        @media (min-width: 992px) {
            .hero-pane { min-height: 100vh; }
        }
    </style>
    <noscript>
        <style>
            .auth-wrapper:before { background-image: url("{{ asset('images/minsu.png') }}"); }
        </style>
    </noscript>
    <!-- If the image is missing, show a subtle gradient background on the left -->
    <script>
        window.addEventListener('load', function(){
            const img = new Image();
            img.onerror = () => document.querySelector('.auth-wrapper')?.classList.add('bg-gradient');
            img.src = "{{ asset('images/minsu.png') }}";
        });
    </script>
</head>
<body class="bg-dark">
    <div class="container-fluid auth-wrapper">
        <div class="auth-top">
            <a href="{{ route('landing') }}" aria-label="Go to home">
                <i class="bi bi-arrow-left"></i> Home
            </a>
        </div>
        <div class="row g-0 h-100">
            <!-- Left hero pane with building image -->
            <div class="col-12 col-lg-6 hero-pane">
                <div class="d-flex flex-column justify-content-between h-100 p-4 p-lg-5 hero-content">
                    <div>
                        <span class="badge brand-badge text-white rounded-pill px-3 py-2">Online Boarding House</span>
                        <h1 class="display-5 fw-bold mt-4">Find your next home away from home.</h1>
                        <p class="lead opacity-75">Create your student account to browse properties, view rooms, and book accommodations easily.</p>

                        <ul class="list-unstyled hero-list mb-0 opacity-90">
                            <li>
                                <span class="hero-ic"><i class="bi bi-search"></i></span>
                                <div>
                                    <div class="fw-semibold">Search & compare rooms</div>
                                    <div class="small opacity-75">See prices, availability, and details quickly.</div>
                                </div>
                            </li>
                            <li>
                                <span class="hero-ic"><i class="bi bi-journal-check"></i></span>
                                <div>
                                    <div class="fw-semibold">Send booking requests</div>
                                    <div class="small opacity-75">Request a slot and track your status.</div>
                                </div>
                            </li>
                            <li>
                                <span class="hero-ic"><i class="bi bi-chat-dots"></i></span>
                                <div>
                                    <div class="fw-semibold">Chat with landlords</div>
                                    <div class="small opacity-75">Ask questions before you book.</div>
                                </div>
                            </li>
                        </ul>
                    </div>
                    <div class="opacity-75 small d-none d-lg-block">Online Boarding House System</div>
                </div>
            </div>

            <!-- Right form pane -->
            <div class="col-lg-6 d-flex align-items-center justify-content-center p-4 p-lg-5">
                <div class="w-100" style="max-width: 520px;">
                    <div class="card rounded-4">
                        <div class="card-body p-4 p-lg-5">
                            <div class="mb-4 text-center">
                                <div class="auth-logo mb-3"><i class="bi bi-mortarboard"></i></div>
                                <h2 class="fw-bold mb-1">Register as Student</h2>
                                <p class="text-muted mb-0">Already have one? <a href="{{ route('login') }}">Sign in</a></p>
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
                                <input type="hidden" name="role" value="student">

                                <div class="row g-3">
                                    <div class="col-12">
                                        <label for="full_name" class="form-label">Full Name</label>
                                        <div class="input-group">
                                            <span class="input-group-text field-icon"><i class="bi bi-person"></i></span>
                                            <input type="text" class="form-control" id="full_name" name="full_name" value="{{ old('full_name') }}" placeholder="Full Name" required>
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <label for="email" class="form-label">Email</label>
                                        <div class="input-group">
                                            <span class="input-group-text field-icon"><i class="bi bi-envelope"></i></span>
                                            <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" placeholder="you@example.com" required>
                                        </div>
                                    </div>

                                    <div class="col-12 col-md-6">
                                        <label for="password" class="form-label">Password</label>
                                        <div class="input-group">
                                            <span class="input-group-text field-icon"><i class="bi bi-lock"></i></span>
                                            <input type="password" class="form-control" id="password" name="password" minlength="8" required>
                                        </div>
                                        <div class="form-text">Minimum of 8 characters.</div>
                                    </div>

                                    <div class="col-12 col-md-6">
                                        <label for="password_confirmation" class="form-label">Confirm Password</label>
                                        <div class="input-group">
                                            <span class="input-group-text field-icon"><i class="bi bi-shield-lock"></i></span>
                                            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" minlength="8" required>
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <label for="contact_number" class="form-label">Contact Number</label>
                                        <div class="input-group">
                                            <span class="input-group-text field-icon"><i class="bi bi-telephone"></i></span>
                                            <input type="text" class="form-control" id="contact_number" name="contact_number" value="{{ old('contact_number') }}" placeholder="09XX-XXX-XXXX" required>
                                        </div>
                                    </div>

                                    <div class="col-12 col-md-6">
                                        <label for="course" class="form-label">Course</label>
                                        <div class="input-group">
                                            <span class="input-group-text field-icon"><i class="bi bi-mortarboard"></i></span>
                                            <input type="text" class="form-control" id="course" name="course" value="{{ old('course') }}" placeholder="e.g., BSIT" required>
                                        </div>
                                    </div>

                                    <div class="col-12 col-md-6">
                                        <label for="year_level" class="form-label">Year Level</label>
                                        <div class="input-group">
                                            <span class="input-group-text field-icon"><i class="bi bi-123"></i></span>
                                            <select class="form-select" id="year_level" name="year_level" required>
                                                <option value="" @selected(old('year_level') === null || old('year_level') === '')>Select year level</option>
                                                <option value="1" @selected(old('year_level') == '1')>1st Year</option>
                                                <option value="2" @selected(old('year_level') == '2')>2nd Year</option>
                                                <option value="3" @selected(old('year_level') == '3')>3rd Year</option>
                                                <option value="4" @selected(old('year_level') == '4')>4th Year</option>
                                                <option value="5" @selected(old('year_level') == '5')>5th Year</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-12 mt-2">
                                        <button type="submit" class="btn btn-brand btn-lg w-100">Create Student Account</button>
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
</body>
</html>