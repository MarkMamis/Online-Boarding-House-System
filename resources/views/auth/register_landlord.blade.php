<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register as Landlord - Online Boarding House System</title>
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
                        <h1 class="display-5 fw-bold mt-4">Manage your properties with ease.</h1>
                        <p class="lead opacity-75">Create your landlord account to add properties, manage rooms, and handle bookings efficiently.</p>

                        <ul class="list-unstyled hero-list mb-0 opacity-90">
                            <li>
                                <span class="hero-ic"><i class="bi bi-building"></i></span>
                                <div>
                                    <div class="fw-semibold">List rooms and amenities</div>
                                    <div class="small opacity-75">Show your property details clearly to students.</div>
                                </div>
                            </li>
                            <li>
                                <span class="hero-ic"><i class="bi bi-journal-check"></i></span>
                                <div>
                                    <div class="fw-semibold">Handle booking requests</div>
                                    <div class="small opacity-75">Approve, decline, and keep records organized.</div>
                                </div>
                            </li>
                            <li>
                                <span class="hero-ic"><i class="bi bi-graph-up"></i></span>
                                <div>
                                    <div class="fw-semibold">Track activity</div>
                                    <div class="small opacity-75">Monitor inquiries and occupancy at a glance.</div>
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
                                <div class="auth-logo mb-3"><i class="bi bi-building"></i></div>
                                <h2 class="fw-bold mb-1">Register as Landlord</h2>
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

                            <form method="POST" action="{{ route('register') }}" id="landlordRegisterForm" enctype="multipart/form-data" novalidate>
                                @csrf
                                <input type="hidden" name="role" value="landlord">

                                <div class="row g-3">
                                    <div class="col-12">
                                        <label for="full_name" class="form-label">Full Name</label>
                                        <div class="input-group">
                                            <span class="input-group-text field-icon"><i class="bi bi-person"></i></span>
                                            <input type="text" class="form-control" id="full_name" name="full_name" value="{{ old('full_name') }}" placeholder="Full-name" required>
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

                                    <div class="col-12 col-md-6">
                                        <label for="contact_number" class="form-label">Contact Number</label>
                                        <div class="input-group">
                                            <span class="input-group-text field-icon"><i class="bi bi-telephone"></i></span>
                                            <input type="text" class="form-control" id="contact_number" name="contact_number" value="{{ old('contact_number') }}" placeholder="09XX-XXX-XXXX" required>
                                        </div>
                                    </div>

                                    <div class="col-12 col-md-6">
                                        <label for="boarding_house_name" class="form-label">Boarding House Name</label>
                                        <div class="input-group">
                                            <span class="input-group-text field-icon"><i class="bi bi-building"></i></span>
                                            <input type="text" class="form-control" id="boarding_house_name" name="boarding_house_name" value="{{ old('boarding_house_name') }}" placeholder="Green Dorms" required>
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <label for="business_permit" class="form-label">Business Permit <span class="text-danger">*</span></label>
                                        <input type="file" class="form-control" id="business_permit" name="business_permit" accept=".pdf,.jpg,.jpeg,.png" required>
                                        <div class="form-text">Upload a clear PDF/JPG/PNG copy (max 2MB). This will be reviewed by admin.</div>
                                    </div>

                                    <input type="hidden" id="business_permit_acknowledged" name="business_permit_acknowledged" value="{{ old('business_permit_acknowledged') ? '1' : '0' }}">

                                    <div class="col-12">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="terms_accepted" name="terms_accepted" value="1" {{ old('terms_accepted') ? 'checked' : '' }} required>
                                            <label class="form-check-label" for="terms_accepted">
                                                I have read and agree to the Terms and Data Privacy Notice.
                                            </label>
                                            <button type="button" class="btn btn-link btn-sm p-0 ms-1 align-baseline text-decoration-underline" data-bs-toggle="modal" data-bs-target="#termsPrivacyModal" style="color: rgba(255,255,255,.92);">
                                                View terms
                                            </button>
                                        </div>
                                        @error('terms_accepted')
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-12 mt-2">
                                        <button type="submit" class="btn btn-brand btn-lg w-100">Create Landlord Account</button>
                                    </div>
                                </div>
                            </form>
                        </div>
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
                        <li><strong>Legitimate Purpose:</strong> Data is used for account setup, permit verification, booking operations, and communication.</li>
                        <li><strong>Proportionality:</strong> We collect only data needed to provide landlord platform services.</li>
                    </ul>
                    <p class="mb-2 small">By registering, you allow the system to process your information (e.g., name, email, contact details, credentials, property details, and permit files) to manage your account and deliver platform functions.</p>
                    <p class="mb-0 small">You may request access, correction, or deletion of your data, subject to legal and operational requirements, and we apply reasonable safeguards to protect your information.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="landlordPermitConfirmModal" tabindex="-1" aria-labelledby="landlordPermitConfirmModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title" id="landlordPermitConfirmModalLabel">
                        <i class="bi bi-shield-exclamation text-warning me-2"></i>Landlord Permit Notice
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body pt-2">
                    <div class="small text-muted mb-3">
                        Please upload a legal and correct business permit document. Admin will verify your file thoroughly, and incorrect or invalid submissions may be rejected.
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="1" id="permit_modal_confirm_checkbox">
                        <label class="form-check-label small" for="permit_modal_confirm_checkbox">
                            I confirm that the uploaded business permit is legal, valid, and belongs to my boarding house.
                        </label>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-success rounded-pill px-3" id="permit_modal_confirm_btn" disabled>
                        <i class="bi bi-check2 me-1"></i>Confirm Notice
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        (function () {
            const form = document.getElementById('landlordRegisterForm');
            const ackInput = document.getElementById('business_permit_acknowledged');
            const permitModalEl = document.getElementById('landlordPermitConfirmModal');
            const permitModalCheckbox = document.getElementById('permit_modal_confirm_checkbox');
            const permitModalConfirmBtn = document.getElementById('permit_modal_confirm_btn');
            const permitModal = permitModalEl ? new bootstrap.Modal(permitModalEl) : null;

            permitModalEl?.addEventListener('show.bs.modal', () => {
                if (permitModalCheckbox && ackInput) {
                    permitModalCheckbox.checked = ackInput.value === '1';
                    if (permitModalConfirmBtn) {
                        permitModalConfirmBtn.disabled = !permitModalCheckbox.checked;
                    }
                }
            });

            permitModalCheckbox?.addEventListener('change', () => {
                if (permitModalConfirmBtn) {
                    permitModalConfirmBtn.disabled = !permitModalCheckbox.checked;
                }
            });

            permitModalConfirmBtn?.addEventListener('click', () => {
                if (!permitModalCheckbox?.checked || !ackInput) return;
                ackInput.value = '1';
                permitModal?.hide();
            });

            form?.addEventListener('submit', (event) => {
                if (!ackInput || ackInput.value === '1') return;
                event.preventDefault();
                permitModal?.show();
            });
        })();
    </script>
</body>
</html>