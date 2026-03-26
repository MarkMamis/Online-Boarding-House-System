<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Online Boarding House System</title>
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
        .form-control {
            background: rgba(255,255,255,.10);
            border-color: rgba(255,255,255,.22);
            color: #fff;
        }
        .form-control::placeholder { color: rgba(255,255,255,.55); }
        .form-check-label { color: rgba(255,255,255,.82); }
        .card a { color: rgba(255,255,255,.92); }
        .card a:hover { color: rgba(255,255,255,1); }

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
        .form-control:focus { border-color: var(--brand); box-shadow: 0 0 0 .25rem rgba(var(--brand-rgb), .18); }
        .btn-brand { background: var(--brand); border-color: var(--brand); }
        .btn-brand:hover { background: var(--brand-dark); border-color: var(--brand-dark); }
        .card a { color: var(--brand-dark); }
        .card a:hover { color: var(--brand); }
        .field-icon { width: 40px; height: 40px; display:inline-flex; align-items:center; justify-content:center; border-radius: .5rem; }

        @media (min-width: 992px) {
            .hero-pane { min-height: 100vh; }
        }
    </style>
    <noscript>
        <style>
            .auth-wrapper:before { background-image: url("{{ asset('images/MinSU-Calapan.jpg') }}"); }
        </style>
    </noscript>
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
                        <h1 class="display-6 fw-bold mt-4 mb-2">Welcome back</h1>
                        <p class="mb-0 opacity-75">Sign in to manage rooms, tenants, and bookings.</p>

                        <ul class="list-unstyled hero-list mb-0 opacity-90">
                            <li>
                                <span class="hero-ic"><i class="bi bi-house-door"></i></span>
                                <div>
                                    <div class="fw-semibold">Browse listings faster</div>
                                    <div class="small opacity-75">Find available rooms and boarding houses in minutes.</div>
                                </div>
                            </li>
                            <li>
                                <span class="hero-ic"><i class="bi bi-chat-dots"></i></span>
                                <div>
                                    <div class="fw-semibold">Message in one place</div>
                                    <div class="small opacity-75">Keep conversations organized with built‑in messaging.</div>
                                </div>
                            </li>
                            <li>
                                <span class="hero-ic"><i class="bi bi-shield-check"></i></span>
                                <div>
                                    <div class="fw-semibold">Secure access</div>
                                    <div class="small opacity-75">Your account and requests are protected.</div>
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
                                <h2 class="fw-bold mb-1">Sign in</h2>
                                <p class="text-muted mb-0">New here? <a href="{{ route('register') }}" class="text-white text-decoration-none fw-semibold mb-0">Create an account</a></p>
                            </div>

                            @if(session('success'))
                                <div class="alert alert-success">{{ session('success') }}</div>
                            @endif
                            @if($errors->any())
                                <div class="alert alert-danger">
                                    <ul class="mb-0">
                                        @foreach($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <form method="POST" action="{{ route('login') }}" novalidate>
                                @csrf
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label for="email" class="form-label">Email</label>
                                        <div class="input-group">
                                            <span class="input-group-text field-icon"><i class="bi bi-envelope"></i></span>
                                            <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" placeholder="you@example.com" required>
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <label for="password" class="form-label">Password</label>
                                        <div class="input-group">
                                            <span class="input-group-text field-icon"><i class="bi bi-lock"></i></span>
                                            <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password">
                                            <span class="input-group-text field-icon" id="toggle_password" role="button" tabindex="0" aria-label="Show password" aria-controls="password">
                                                <i class="bi bi-eye" aria-hidden="true"></i>
                                            </span>
                                        </div>
                                    </div>

                                    <div class="col-12 d-flex justify-content-between align-items-center">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="remember" name="remember">
                                            <label class="form-check-label" for="remember">Remember me</label>
                                        </div>
                                        <a class="small text-white text-decoration-none" href="#" onclick="return false; ">Forgot password?</a>
                                    </div>

                                    <div class="col-12 mt-2">
                                        <button type="submit" class="btn btn-brand btn-lg w-100">Sign in</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <p class="text-center text-white mt-3 mb-0 small">By signing in, you agree to our Terms and Privacy Policy.</p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        (function () {
            const passwordInput = document.getElementById('password');
            const toggle = document.getElementById('toggle_password');
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
        })();
    </script>
</body>
</html>