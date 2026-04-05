<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Registration - Online Boarding House System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --brand: #14532d;
            --brand-dark: #166534;
            --ink: #0f172a;
            --muted: #6b7280;
            --line: #e5e7eb;
            --shell: #f8fafc;
            --surface: #ffffff;
        }

        body {
            font-family: 'Manrope', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            background: var(--shell);
            color: var(--ink);
        }

        .top-nav {
            border-bottom: 1px solid var(--line);
            background: #fff;
        }

        .top-nav-brand {
            color: var(--ink);
            font-weight: 800;
            text-decoration: none;
            letter-spacing: .01em;
        }

        .auth-card {
            border: 1px solid var(--line);
            border-top: 4px solid var(--brand);
            background: var(--surface);
            box-shadow: 0 14px 30px rgba(15, 23, 42, 0.06);
        }

        .form-label {
            color: #334155;
            font-weight: 600;
        }

        .input-group-text,
        .form-control {
            border-color: var(--line);
            color: var(--ink);
            background: #fff;
        }

        .form-control::placeholder {
            color: #94a3b8;
        }

        .form-control:focus {
            border-color: var(--brand);
            box-shadow: 0 0 0 .25rem rgba(20, 83, 45, 0.14);
            color: var(--ink);
        }

        .btn-brand {
            background: var(--brand);
            border-color: var(--brand);
            font-weight: 600;
        }

        .btn-brand:hover {
            background: var(--brand-dark);
            border-color: var(--brand-dark);
        }

        .page-wrap {
            min-height: calc(100vh - 62px);
        }

        .admin-chip {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            border: 1px solid rgba(20, 83, 45, .2);
            background: rgba(167, 243, 208, .2);
            color: var(--brand);
            border-radius: 999px;
            padding: .26rem .62rem;
            font-size: .74rem;
            font-weight: 700;
        }

        .muted-copy {
            color: var(--muted);
        }
    </style>
</head>
<body>
    <nav class="top-nav py-2">
        <div class="container d-flex justify-content-between align-items-center">
            <a href="{{ route('landing') }}" class="top-nav-brand d-inline-flex align-items-center gap-2">
                <i class="bi bi-grid"></i>
                Admin Panel
            </a>
            <a href="{{ route('login') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">Sign in</a>
        </div>
    </nav>

    <div class="container page-wrap py-4 py-lg-5">
        <div class="row justify-content-center align-items-center" style="min-height: calc(100vh - 130px);">
            <div class="col-12 col-md-8 col-lg-6 col-xl-5">
                <div class="mb-3 text-center">
                    <span class="admin-chip"><i class="bi bi-shield-check"></i> Admin Setup</span>
                </div>

                <div class="card auth-card rounded-4">
                    <div class="card-body p-4 p-lg-5">
                        <div class="text-center mb-4">
                            <h1 class="h3 mb-1">Register Admin</h1>
                            <p class="mb-0 muted-copy">Simple and minimal admin registration</p>
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
                                    </div>
                                </div>

                                <div class="col-12 col-md-6">
                                    <label for="password_confirmation" class="form-label">Confirm Password</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-shield-lock"></i></span>
                                        <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" minlength="8" required>
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
                                        <button type="button" class="btn btn-link btn-sm p-0 ms-1 align-baseline text-decoration-underline" data-bs-toggle="modal" data-bs-target="#termsPrivacyModal">
                                            View terms
                                        </button>
                                    </div>
                                    @error('terms_accepted')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12 pt-2">
                                    <button type="submit" class="btn btn-brand btn-lg w-100 text-white">Create Admin Account</button>
                                </div>
                            </div>
                        </form>

                        <p class="text-center mt-4 mb-0 muted-copy">
                            Already have an account? <a href="{{ route('login') }}" class="text-decoration-none fw-semibold">Sign in</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="termsPrivacyModal" tabindex="-1" aria-labelledby="termsPrivacyModalLabel" aria-hidden="true">
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
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
