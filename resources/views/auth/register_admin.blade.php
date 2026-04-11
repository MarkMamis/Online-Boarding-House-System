<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Registration - Online Boarding House System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@500;600;700;800&family=Sora:wght@500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --admin-ink: #f5f7ff;
            --admin-muted: #b7bed7;
            --admin-line: rgba(255, 255, 255, .14);
            --admin-shell-top: #11142a;
            --admin-shell-bottom: #1a1024;
            --admin-panel: rgba(17, 20, 42, .74);
            --admin-panel-soft: rgba(22, 25, 50, .7);
            --admin-cyan: #52d6ff;
            --admin-cyan-deep: #1ba6d2;
            --admin-amber: #ffbe55;
            --admin-amber-deep: #e08f16;
            --admin-success: #29c98f;
            --control-height: 46px;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Manrope', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            color: var(--admin-ink);
            background:
                radial-gradient(1000px circle at 10% -10%, rgba(82,214,255,.18), transparent 48%),
                radial-gradient(900px circle at 100% 0%, rgba(255,190,85,.16), transparent 42%),
                linear-gradient(165deg, var(--admin-shell-top), var(--admin-shell-bottom));
        }

        .admin-stage {
            min-height: 100vh;
            position: relative;
            overflow: hidden;
        }

        .admin-stage::before {
            content: "";
            position: absolute;
            inset: -10% -30% auto auto;
            width: 520px;
            height: 520px;
            border-radius: 999px;
            background: radial-gradient(circle, rgba(82,214,255,.2), rgba(82,214,255,0));
            pointer-events: none;
        }

        .admin-stage::after {
            content: "";
            position: absolute;
            inset: auto auto -24% -18%;
            width: 520px;
            height: 520px;
            border-radius: 999px;
            background: radial-gradient(circle, rgba(255,190,85,.2), rgba(255,190,85,0));
            pointer-events: none;
        }

        .admin-wrap {
            position: relative;
            z-index: 1;
            min-height: 100vh;
            padding: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .admin-surface {
            width: 100%;
            max-width: 920px;
        }

        .admin-hero {
            padding: 1.15rem 1rem .5rem;
            text-align: center;
        }

        .admin-brand-block {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: .75rem;
            margin: 0 auto .75rem;
            text-decoration: none;
            color: inherit;
        }

        .admin-brand-block:hover {
            color: inherit;
        }

        .admin-brand-logos {
            display: inline-flex;
            align-items: center;
            gap: .45rem;
            flex: 0 0 auto;
        }

        .admin-brand-logos img {
            width: 44px;
            height: 44px;
            object-fit: contain;
            filter: drop-shadow(0 6px 12px rgba(7,10,28,.34));
        }

        .admin-brand-copy {
            display: flex;
            flex-direction: column;
            align-items: center;
            line-height: 1.08;
            min-width: 0;
        }

        .admin-brand-top {
            font-family: 'Sora', 'Manrope', sans-serif;
            font-size: 1rem;
            font-weight: 700;
            color: #f7fbff;
        }

        .admin-brand-bottom {
            font-size: .82rem;
            font-weight: 700;
            color: #d6e4ff;
        }

        .admin-top-actions {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: .35rem;
            margin-bottom: .85rem;
        }

        .admin-top-link {
            display: inline-flex;
            align-items: center;
            gap: .32rem;
            border-radius: .6rem;
            border: 1px solid transparent;
            background: transparent;
            color: #d8e5ff;
            text-decoration: none;
            font-size: .8rem;
            font-weight: 700;
            padding: .32rem .58rem;
            transition: transform .2s ease, background .2s ease, color .2s ease, border-color .2s ease;
        }

        .admin-top-link:hover {
            color: #ffffff;
            background: rgba(255,255,255,.07);
            border-color: rgba(255,255,255,.16);
            transform: translateY(-1px);
        }

        .admin-chip {
            display: inline-flex;
            align-items: center;
            gap: .38rem;
            border: 1px solid rgba(82,214,255,.36);
            color: #ddf5ff;
            background: rgba(82,214,255,.14);
            border-radius: 999px;
            padding: .24rem .62rem;
            font-size: .72rem;
            font-weight: 700;
            letter-spacing: .04em;
            text-transform: uppercase;
        }

        .admin-title {
            font-family: 'Sora', 'Manrope', sans-serif;
            font-size: clamp(1.55rem, 3.4vw, 2.2rem);
            line-height: 1.15;
            margin: .82rem 0 .45rem;
            letter-spacing: .01em;
        }

        .admin-subtitle {
            color: var(--admin-muted);
            margin: 0 auto;
            max-width: 560px;
        }

        .admin-hero-list {
            list-style: none;
            margin: 1rem 0 0;
            padding: 0;
            display: grid;
            gap: .55rem;
        }

        .admin-hero-list li {
            display: flex;
            align-items: flex-start;
            gap: .58rem;
        }

        .admin-hero-icon {
            width: 30px;
            height: 30px;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #f4fbff;
            background: rgba(255,255,255,.1);
            border: 1px solid rgba(255,255,255,.18);
            flex: 0 0 auto;
            margin-top: .08rem;
        }

        .admin-hero-text {
            font-size: .88rem;
            color: rgba(245,247,255,.93);
        }

        .admin-hero-text span {
            display: block;
            color: var(--admin-muted);
            font-size: .8rem;
            margin-top: .08rem;
        }

        .admin-form-pane {
            width: 100%;
            max-width: 620px;
            margin: .9rem auto 0;
            padding: 1.05rem;
            border: 1px solid var(--admin-line);
            border-radius: 1.05rem;
            background: linear-gradient(145deg, var(--admin-panel), var(--admin-panel-soft));
            backdrop-filter: blur(14px) saturate(1.08);
            -webkit-backdrop-filter: blur(14px) saturate(1.08);
            box-shadow: 0 24px 54px rgba(7, 10, 28, .42);
        }

        .admin-form-head {
            text-align: left;
            margin-bottom: .95rem;
        }

        .admin-form-head h1 {
            font-family: 'Sora', 'Manrope', sans-serif;
            font-size: 1.32rem;
            margin: 0;
        }

        .admin-form-head p {
            margin: .32rem 0 0;
            color: var(--admin-muted);
            font-size: .87rem;
        }

        .admin-form-head a {
            color: #dbf6ff;
            font-weight: 700;
            text-decoration: none;
        }

        .admin-form-head a:hover {
            color: #ffffff;
        }

        .form-label {
            color: #eaf0ff;
            font-weight: 600;
            font-size: .87rem;
        }

        .input-group-text,
        .form-control {
            border-color: rgba(255,255,255,.2);
            background: rgba(255,255,255,.08);
            color: #f3f7ff;
            min-height: var(--control-height);
        }

        .input-group-text {
            min-width: 46px;
            justify-content: center;
            color: #d8e0f4;
        }

        .form-control::placeholder {
            color: rgba(221,230,252,.58);
        }

        .form-control:focus {
            border-color: rgba(82,214,255,.7);
            box-shadow: 0 0 0 .22rem rgba(82,214,255,.16);
            background: rgba(255,255,255,.12);
            color: #ffffff;
        }

        .form-check-label {
            color: #d6deef;
            font-size: .84rem;
        }

        .form-check-input:checked {
            background-color: var(--admin-success);
            border-color: var(--admin-success);
        }

        .terms-trigger {
            color: #dbf6ff;
            font-weight: 700;
            text-underline-offset: 3px;
        }

        .terms-trigger:hover {
            color: #ffffff;
        }

        .btn-admin {
            border: 1px solid rgba(255,190,85,.7);
            background: linear-gradient(125deg, var(--admin-amber), var(--admin-amber-deep));
            color: #1a1204;
            font-weight: 800;
            letter-spacing: .01em;
            border-radius: 999px;
            min-height: 46px;
            box-shadow: 0 12px 24px rgba(184,119,8,.34);
            transition: transform .2s ease, box-shadow .2s ease;
        }

        .btn-admin:hover {
            color: #120c03;
            transform: translateY(-1px);
            box-shadow: 0 16px 28px rgba(184,119,8,.42);
        }

        .inline-foot {
            color: var(--admin-muted);
            text-align: center;
            margin-top: .92rem;
            margin-bottom: 0;
            font-size: .82rem;
        }

        .inline-foot a {
            color: #dbf6ff;
            text-decoration: none;
            font-weight: 700;
        }

        .inline-foot a:hover {
            color: #ffffff;
        }

        .alert {
            font-size: .86rem;
            border: 0;
        }

        .terms-modal .modal-content {
            border: 1px solid rgba(255,255,255,.22);
            border-radius: 1rem;
            background: linear-gradient(155deg, #0f142b, #171c35);
            color: #f5f7ff;
            box-shadow: 0 22px 50px rgba(7, 10, 28, .5);
        }

        .terms-modal .modal-header,
        .terms-modal .modal-footer {
            border-color: rgba(255,255,255,.12);
        }

        .terms-modal .btn-close {
            filter: invert(1) grayscale(1) brightness(2);
            opacity: .78;
        }

        .terms-modal .modal-body {
            color: #d7deef;
        }

        .terms-modal strong {
            color: #f1f5ff;
        }

        .btn-close-light {
            border-radius: 999px;
            border: 1px solid rgba(255,255,255,.2);
            color: #edf3ff;
            background: rgba(255,255,255,.08);
        }

        .btn-close-light:hover {
            color: #fff;
            background: rgba(255,255,255,.14);
        }

        @media (min-width: 992px) {
            .admin-wrap {
                padding: 1.35rem;
            }

            .admin-surface {
                min-height: auto;
            }

            .admin-hero {
                padding: 1.15rem 1rem .4rem;
            }

            .admin-form-pane {
                max-width: 620px;
                padding: 1.2rem 1.25rem 1.35rem;
            }
        }

        @media (max-width: 575.98px) {
            .admin-wrap {
                padding: .8rem;
            }

            .admin-surface {
                max-width: 100%;
            }

            .admin-hero,
            .admin-form-pane {
                padding: .85rem;
            }

            .admin-form-pane {
                margin-top: .55rem;
                border-radius: .95rem;
            }

            .admin-brand-logos img {
                width: 38px;
                height: 38px;
            }

            .admin-brand-top {
                font-size: .9rem;
            }

            .admin-brand-bottom {
                font-size: .74rem;
            }

            .admin-title {
                font-size: 1.42rem;
            }

            .admin-hero-list {
                gap: .45rem;
            }

            .admin-hero-text {
                font-size: .84rem;
            }

            .admin-hero-text span {
                font-size: .76rem;
            }
        }
    </style>
</head>
<body>
    <div class="admin-stage">
        <div class="admin-wrap">
            <div class="admin-surface">
                <section class="admin-hero" aria-label="Admin registration overview">
                    <a href="{{ route('landing') }}" class="admin-brand-block" aria-label="Mindoro State University Online Boarding House System">
                        <span class="admin-brand-logos" aria-hidden="true">
                            <img src="{{ asset('images/MinSU_logo.png') }}" alt="Mindoro State University logo" loading="lazy">
                            <img src="{{ asset('images/OSSE-main.png') }}" alt="OSSE logo" loading="lazy">
                        </span>
                        <span class="admin-brand-copy">
                            <span class="admin-brand-top">Mindoro State University</span>
                            <span class="admin-brand-bottom">Online Boarding House System</span>
                        </span>
                    </a>

                    <h2 class="admin-title">Admin Registration</h2>
                    <p class="admin-subtitle">Create a secure administrator account for the platform.</p>
                </section>

                <section class="admin-form-pane" aria-label="Admin registration form">
                    <div class="admin-form-head">
                        <center><p>Already have an account? <a href="{{ route('login') }}">Sign in</a></p>
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

                    <form method="POST" action="{{ route('register.admin.submit') }}" novalidate>
                        @csrf

                        <div class="row g-3">
                            <div class="col-12">
                                <label for="full_name" class="form-label">Full Name</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                                    <input type="text" id="full_name" name="full_name" class="form-control" value="{{ old('full_name') }}" placeholder="Admin full name" required>
                                </div>
                            </div>

                            <div class="col-12">
                                <label for="email" class="form-label">Email</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                    <input type="email" id="email" name="email" class="form-control" value="{{ old('email') }}" placeholder="admin@example.com" required>
                                </div>
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="password" class="form-label">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                    <input type="password" id="password" name="password" class="form-control" minlength="8" required>
                                    <span class="input-group-text" id="toggle_password" role="button" tabindex="0" aria-label="Show password" aria-controls="password">
                                        <i class="bi bi-eye" aria-hidden="true"></i>
                                    </span>
                                </div>
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="password_confirmation" class="form-label">Confirm Password</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-shield-lock"></i></span>
                                    <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" minlength="8" required>
                                    <span class="input-group-text" id="toggle_password_confirmation" role="button" tabindex="0" aria-label="Show password" aria-controls="password_confirmation">
                                        <i class="bi bi-eye" aria-hidden="true"></i>
                                    </span>
                                </div>
                            </div>

                            <div class="col-12">
                                <label for="contact_number" class="form-label">Contact Number</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                                    <input type="text" id="contact_number" name="contact_number" class="form-control" value="{{ old('contact_number') }}" placeholder="09XX-XXX-XXXX" required>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="terms_accepted" name="terms_accepted" value="1" {{ old('terms_accepted') ? 'checked' : '' }} required>
                                    <label class="form-check-label" for="terms_accepted">
                                        I have read and agree to the Terms and Data Privacy Notice.
                                    </label>
                                    <button type="button" class="btn btn-link btn-sm p-0 ms-1 align-baseline text-decoration-underline terms-trigger" data-bs-toggle="modal" data-bs-target="#termsPrivacyModal">
                                        View terms
                                    </button>
                                </div>
                                @error('terms_accepted')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 pt-1">
                                <button type="submit" class="btn btn-admin w-100">Create Admin Account</button>
                            </div>
                        </div>
                    </form>

                    <p class="inline-foot">Only authorized personnel should proceed with admin account creation.</p>
                </section>
            </div>
        </div>
    </div>

    <div class="modal fade terms-modal" id="termsPrivacyModal" tabindex="-1" aria-labelledby="termsPrivacyModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="termsPrivacyModalLabel">Terms and Data Privacy Notice</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-2">Under Republic Act No. 10173 (Data Privacy Act of 2012), we process your personal data using these principles:</p>
                    <ul class="small mb-3">
                        <li><strong>Transparency:</strong> You are informed about what data we collect and why.</li>
                        <li><strong>Legitimate Purpose:</strong> Data is used for account creation, role-based access control, records management, and platform security.</li>
                        <li><strong>Proportionality:</strong> We collect only data needed to provide administrative services.</li>
                    </ul>
                    <p class="mb-2 small">By registering, you allow the system to process your information (e.g., name, email, contact details, credentials, and access-related data) to deliver admin functions and comply with legal obligations.</p>
                    <p class="mb-0 small">You may request access, correction, or deletion of your data, subject to legal and operational requirements, and we apply reasonable safeguards to protect your information.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-close-light" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        (function () {
            const passwords = [
                { input: 'password', toggle: 'toggle_password' },
                { input: 'password_confirmation', toggle: 'toggle_password_confirmation' }
            ];

            passwords.forEach((config) => {
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
                toggle.addEventListener('keydown', (event) => {
                    if (event.key === 'Enter' || event.key === ' ') {
                        event.preventDefault();
                        toggleVisibility();
                    }
                });
            });
        })();
    </script>
</body>
</html>
