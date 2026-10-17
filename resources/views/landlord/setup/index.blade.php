<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Landlord Setup - Online Boarding House System</title>
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

        /* Upload preview */
        .upload-preview {
            width: 76px;
            height: 76px;
            object-fit: cover;
            border-radius: .85rem;
            border: 2px solid #e2e8f0;
            box-shadow: 0 4px 10px rgba(15, 23, 42, .06);
            flex-shrink: 0;
        }

        /* Address Suggestions */
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

        /* Payment Methods */
        .method-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: .75rem;
        }

        .method-option {
            display: flex;
            align-items: flex-start;
            gap: .65rem;
            padding: .9rem .75rem;
            border-radius: .85rem;
            border: 1.5px solid var(--line);
            background: #ffffff;
            cursor: pointer;
            transition: all .18s ease;
        }

        .method-option:hover {
            border-color: #cbd5e1;
            background: #f8fafc;
        }

        .method-option:has(input:checked) {
            border-color: var(--premium-emerald-deep);
            background: rgba(16, 185, 129, .04);
            box-shadow: 0 0 0 1px var(--premium-emerald-deep);
        }

        .method-title {
            font-weight: 700;
            font-size: .95rem;
            color: var(--ink);
            line-height: 1.2;
        }

        .method-sub {
            font-size: .78rem;
            color: var(--muted);
            margin: .2rem 0 0;
            line-height: 1.35;
        }

        .conditional-group {
            background: #f8fafc;
            border: 1px solid var(--line);
            border-radius: 1rem;
            padding: 1.25rem;
            margin-top: 1rem;
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

        .inline-note {
            padding: .75rem 1rem;
            border-radius: .75rem;
            background: rgba(16, 185, 129, .08);
            border: 1px solid rgba(16, 185, 129, .2);
            color: var(--brand-dark);
            font-size: .88rem;
            margin-bottom: 1.25rem;
        }

        .inline-note.is-danger {
            background: rgba(239, 68, 68, .08);
            border-color: rgba(239, 68, 68, .2);
            color: #b91c1c;
        }

        @media (max-width: 575.98px) {
            .setup-wrap {
                padding: 1rem .75rem 2rem;
            }

            .setup-shell {
                padding: 1.5rem 1.15rem;
                border-radius: 1.25rem;
            }

            .method-grid {
                grid-template-columns: 1fr;
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
    @php
        $landlordProfile = $user->landlordProfile;
        $selectedMethods = old('preferred_payment_methods', optional($landlordProfile)->preferred_payment_methods ?? []);
        $selectedMethods = is_array($selectedMethods) ? $selectedMethods : [];
        $hasProfilePhoto = !empty($user->profile_image_path);
        $hasContractSignature = !empty(optional($landlordProfile)->contract_signature_path);
        $hasBusinessPermit = !empty(optional($landlordProfile)->business_permit_path);
        $hasSafetyCertificate = !empty(optional($landlordProfile)->safety_certificate_path);
    @endphp

    <div class="setup-wrap">
        <header class="setup-topbar">
            <a href="{{ url('/') }}" class="setup-brand">
                <span class="setup-brand-badge"><i class="bi bi-houses-fill"></i></span>
                <span>OBHS</span>
            </a>
            <div class="setup-topbar-actions">
                @if(!$setupSnapshot['setup_submitted'])
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

        <main class="setup-shell" data-initial-step="{{ (int) ($initialStep ?? 0) }}">
            @if(session('error'))
                <div class="alert alert-danger rounded-3 border-0 py-2 px-3 mb-3 small d-flex align-items-center gap-2">
                    <i class="bi bi-exclamation-circle-fill"></i>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            @if(session('success'))
                <div class="alert alert-success rounded-3 border-0 py-2 px-3 mb-3 small d-flex align-items-center gap-2">
                    <i class="bi bi-check-circle-fill"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            <form method="POST" action="{{ route('landlord.setup.update') }}" enctype="multipart/form-data" id="landlordSetupForm" novalidate>
                @csrf
                @method('PUT')

                <!-- PANEL 1: Identity & Boarding House Basics -->
                <section class="question-panel active" data-question-panel="0">
                    <div class="question-kicker">Landlord details</div>
                    <h1 class="question-title">Tell us about you and your boarding house</h1>
                    <p class="question-subtitle">Enter your legal contact information and official boarding house name.</p>

                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Full Name *</label>
                            <input type="text" name="full_name" class="form-control @error('full_name') is-invalid @enderror" value="{{ old('full_name', $user->full_name ?: $user->name) }}" placeholder="e.g., Maria Santos" required>
                            @error('full_name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label">Email Address *</label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" placeholder="landlord@example.com" required>
                            @error('email')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label">Contact Number *</label>
                            <input type="text" name="contact_number" class="form-control ph-number @error('contact_number') is-invalid @enderror" value="{{ old('contact_number', $user->contact_number) }}" inputmode="numeric" pattern="^09\d{9}$" maxlength="11" placeholder="09XXXXXXXXX" title="Use 11-digit PH mobile format: 09XXXXXXXXX" required>
                            @error('contact_number')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label">Boarding House Name *</label>
                            <input type="text" name="boarding_house_name" class="form-control @error('boarding_house_name') is-invalid @enderror" value="{{ old('boarding_house_name', $user->boarding_house_name ?? optional($landlordProfile)->boarding_house_name) }}" placeholder="e.g., Sunshine Student Residence" required>
                            @error('boarding_house_name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label">Property Address *</label>
                            <div class="address-suggest-wrap">
                                <textarea id="landlordAddressInput" name="address" rows="2" class="form-control @error('address') is-invalid @enderror" placeholder="Start typing barangay or municipality..." required>{{ old('address', $user->address) }}</textarea>
                                <div id="landlordAddressSuggestMenu" class="address-suggestion-menu" role="listbox" aria-label="Landlord address suggestions"></div>
                            </div>
                            <div class="field-hint">Format: Barangay, City/Municipality, Province (e.g., Masipit, Calapan City, Oriental Mindoro).</div>
                            <div id="landlordAddressSuggestStatus" class="small text-muted mt-1"></div>
                            @error('address')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="question-actions first-question">
                        <button type="button" class="btn btn-brand btn-next-question" data-target="1">
                            <span>Continue</span>
                            <i class="bi bi-arrow-right"></i>
                        </button>
                    </div>
                </section>

                <!-- PANEL 2: Property Description & Media -->
                <section class="question-panel" data-question-panel="1">
                    <div class="question-kicker">Property description</div>
                    <h2 class="question-title">Introduce your boarding house to students</h2>
                    <p class="question-subtitle">Provide a welcoming description, your profile photo, and an optional contract e-signature.</p>

                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">About Your Boarding House / Description *</label>
                            <textarea name="about" rows="4" class="form-control @error('about') is-invalid @enderror" placeholder="Describe the amenities, room rules, nearby landmarks, and boarding culture..." required>{{ old('about', optional($landlordProfile)->about) }}</textarea>
                            @error('about')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label">Profile Photo</label>
                            <div class="d-flex align-items-center gap-3">
                                @if($hasProfilePhoto)
                                    <img id="profile_preview" src="{{ file_url($user->profile_image_path) }}" alt="Profile preview" class="upload-preview">
                                @else
                                    <img id="profile_preview" src="" alt="Profile preview" class="upload-preview d-none">
                                @endif
                                <div class="w-100">
                                    <input type="file" name="profile_image" id="profile_image" class="form-control @error('profile_image') is-invalid @enderror" accept="image/*">
                                    <div class="field-hint">Optional. JPG, PNG, WEBP, or GIF up to 2MB.</div>
                                    @error('profile_image')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Contract E-signature</label>
                            <input type="file" name="contract_signature_image" class="form-control @error('contract_signature_image') is-invalid @enderror" accept=".jpg,.jpeg,.png,.webp">
                            <div class="field-hint">Optional. Upload an image of your signature for digital rental contracts (JPG, PNG up to 2MB).</div>
                            @error('contract_signature_image')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            @if($hasContractSignature)
                                <a href="{{ file_download_url(optional($landlordProfile)->contract_signature_path) }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary rounded-pill mt-2">
                                    <i class="bi bi-pen me-1"></i>View current e-signature
                                </a>
                            @endif
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

                <!-- PANEL 3: Accreditation Documents -->
                <section class="question-panel" data-question-panel="2">
                    <div class="question-kicker">Accreditation documents</div>
                    <h2 class="question-title">Upload your accreditation permits</h2>
                    <p class="question-subtitle">Upload your permits to become an accredited boarding house with the University.</p>

                    @if(($setupSnapshot['permit_status'] ?? '') === 'rejected' && filled($setupSnapshot['permit_rejection_reason'] ?? ''))
                        <div class="inline-note is-danger">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i>
                            <strong>Business permit rejected:</strong> {{ $setupSnapshot['permit_rejection_reason'] }}
                        </div>
                    @elseif(($setupSnapshot['permit_status'] ?? '') === 'pending')
                        <div class="inline-note">
                            <i class="bi bi-clock-history me-1"></i>
                            Business permit submitted. University accreditation review is currently pending.
                        </div>
                    @endif

                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Business Permit</label>
                            <input type="file" name="business_permit" class="form-control @error('business_permit') is-invalid @enderror" accept=".pdf,.jpg,.jpeg,.png,.webp">
                            <div class="field-hint">Accepted: PDF, JPG, PNG, WEBP up to 2MB. Can be uploaded now or updated later.</div>
                            @error('business_permit')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            @if($hasBusinessPermit)
                                <a href="{{ file_download_url(optional($landlordProfile)->business_permit_path) }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary rounded-pill mt-2">
                                    <i class="bi bi-file-earmark-text me-1"></i>View current business permit
                                </a>
                            @endif
                        </div>

                        <div class="col-12">
                            <label class="form-label">Safety Certificate (BFP Inspection / Barangay Clearance)</label>
                            <input type="file" name="safety_certificate" class="form-control @error('safety_certificate') is-invalid @enderror" accept=".pdf,.jpg,.jpeg,.png,.webp">
                            <div class="field-hint">Accepted: PDF, JPG, PNG, WEBP up to 2MB.</div>
                            @error('safety_certificate')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            @if($hasSafetyCertificate)
                                <a href="{{ file_download_url(optional($landlordProfile)->safety_certificate_path) }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary rounded-pill mt-2">
                                    <i class="bi bi-shield-check me-1"></i>View current safety certificate
                                </a>
                            @endif
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

                <!-- PANEL 4: Payment Preferences & Billing -->
                <section class="question-panel" data-question-panel="3">
                    <div class="question-kicker">Payment preferences</div>
                    <h2 class="question-title">How would you like to receive rental payments?</h2>
                    <p class="question-subtitle">Select your accepted payment channels and enter receiving account information.</p>

                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Preferred Payment Method(s) *</label>
                            <div class="method-grid">
                                <label class="method-option" for="pm_bank">
                                    <input class="form-check-input payment-method-toggle" type="checkbox" name="preferred_payment_methods[]" value="bank" id="pm_bank" @checked(in_array('bank', $selectedMethods, true))>
                                    <div class="method-copy">
                                        <div class="method-title">Bank</div>
                                        <p class="method-sub">Accept bank transfers directly to your bank account.</p>
                                    </div>
                                </label>

                                <label class="method-option" for="pm_gcash">
                                    <input class="form-check-input payment-method-toggle" type="checkbox" name="preferred_payment_methods[]" value="gcash" id="pm_gcash" @checked(in_array('gcash', $selectedMethods, true))>
                                    <div class="method-copy">
                                        <div class="method-title">GCash</div>
                                        <p class="method-sub">Accept direct wallet transfers and optional QR code scans.</p>
                                    </div>
                                </label>

                                <label class="method-option" for="pm_cash">
                                    <input class="form-check-input payment-method-toggle" type="checkbox" name="preferred_payment_methods[]" value="cash" id="pm_cash" @checked(in_array('cash', $selectedMethods, true))>
                                    <div class="method-copy">
                                        <div class="method-title">Cash</div>
                                        <p class="method-sub">Allow in-person cash payments without extra account details.</p>
                                    </div>
                                </label>
                            </div>
                            @error('preferred_payment_methods')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            @error('preferred_payment_methods.*')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>

                        <!-- Bank Account Conditional Fields -->
                        <div class="col-12 conditional-group d-none" id="bankFields" data-payment-section="bank">
                            <div class="fw-bold small text-uppercase text-muted border-bottom pb-1 mb-3">Bank Account Information</div>
                            <div class="row g-3">
                                <div class="col-12 col-md-6">
                                    <label class="form-label">Bank Name *</label>
                                    <input type="text" name="payment_bank_name" class="form-control @error('payment_bank_name') is-invalid @enderror" value="{{ old('payment_bank_name', optional($landlordProfile)->payment_bank_name) }}" placeholder="e.g., BDO, BPI, Landbank">
                                    @error('payment_bank_name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label">Account Holder Name *</label>
                                    <input type="text" name="payment_account_name" class="form-control @error('payment_account_name') is-invalid @enderror" value="{{ old('payment_account_name', optional($landlordProfile)->payment_account_name) }}" placeholder="e.g., Maria Santos">
                                    @error('payment_account_name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Account Number</label>
                                    <input type="text" name="payment_account_number" class="form-control @error('payment_account_number') is-invalid @enderror" value="{{ old('payment_account_number', optional($landlordProfile)->payment_account_number) }}" placeholder="e.g., 1234-5678-9012">
                                    @error('payment_account_number')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>

                        <!-- GCash Conditional Fields -->
                        <div class="col-12 conditional-group d-none" id="gcashFields" data-payment-section="gcash">
                            <div class="fw-bold small text-uppercase text-muted border-bottom pb-1 mb-3">GCash Account Information</div>
                            <div class="row g-3">
                                <div class="col-12 col-md-6">
                                    <label class="form-label">GCash Registered Name *</label>
                                    <input type="text" name="payment_gcash_name" class="form-control @error('payment_gcash_name') is-invalid @enderror" value="{{ old('payment_gcash_name', optional($landlordProfile)->payment_gcash_name) }}" placeholder="e.g., Maria S.">
                                    @error('payment_gcash_name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label">GCash Mobile Number *</label>
                                    <input type="text" name="payment_gcash_number" class="form-control ph-number @error('payment_gcash_number') is-invalid @enderror" value="{{ old('payment_gcash_number', optional($landlordProfile)->payment_gcash_number) }}" inputmode="numeric" pattern="^09\d{9}$" maxlength="11" placeholder="09XXXXXXXXX" title="Use 11-digit PH mobile format: 09XXXXXXXXX">
                                    @error('payment_gcash_number')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-12">
                                    <label class="form-label">GCash QR Code</label>
                                    <input type="file" name="payment_gcash_qr" class="form-control @error('payment_gcash_qr') is-invalid @enderror" accept="image/*">
                                    <div class="field-hint">Optional. Upload a JPG, PNG, or WEBP QR image up to 2MB.</div>
                                    @error('payment_gcash_qr')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                    @if(!empty(optional($landlordProfile)->payment_gcash_qr_path))
                                        <a href="{{ file_download_url(optional($landlordProfile)->payment_gcash_qr_path) }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary rounded-pill mt-2">
                                            <i class="bi bi-qr-code me-1"></i>View current QR code
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Payment Instructions</label>
                            <textarea name="payment_instructions" rows="2" class="form-control @error('payment_instructions') is-invalid @enderror" placeholder="Optional instructions for students when paying rent (e.g., Send screenshot of receipt via chat)...">{{ old('payment_instructions', optional($landlordProfile)->payment_instructions) }}</textarea>
                            @error('payment_instructions')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="question-actions">
                        <button type="button" class="btn btn-prev btn-prev-question" data-target="2">
                            <i class="bi bi-arrow-left"></i>
                            <span>Previous</span>
                        </button>
                        <button type="submit" id="submitLandlordBtn" class="btn btn-brand">
                            <span>Complete Landlord Setup</span>
                            <i class="bi bi-check2-circle"></i>
                        </button>
                    </div>
                </section>
            </form>
        </main>
    </div>

    <script>
        (function () {
            const shell = document.querySelector('.setup-shell');
            const form = document.getElementById('landlordSetupForm');
            const submitBtn = document.getElementById('submitLandlordBtn');
            const questionPanels = Array.from(document.querySelectorAll('[data-question-panel]'));
            let currentPanel = Number(shell ? shell.getAttribute('data-initial-step') : 0);

            const updateActivePanel = function () {
                questionPanels.forEach((panel, index) => {
                    panel.classList.toggle('active', index === currentPanel);
                });
                window.scrollTo({ top: 0, behavior: 'smooth' });
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
                    const maxMb = 2;
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
                if (field.classList.contains('ph-number')) {
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

            const moveToPanel = function (targetIndex) {
                currentPanel = Math.max(0, Math.min(questionPanels.length - 1, targetIndex));
                updateActivePanel();
            };

            document.querySelectorAll('.btn-next-question').forEach((button) => {
                button.addEventListener('click', function (e) {
                    e.preventDefault();
                    if (!validatePanel(currentPanel)) return;
                    moveToPanel(Number(button.getAttribute('data-target')));
                });
            });

            document.querySelectorAll('.btn-prev-question').forEach((button) => {
                button.addEventListener('click', function (e) {
                    e.preventDefault();
                    moveToPanel(Number(button.getAttribute('data-target')));
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
                        submitBtn.innerHTML = '<span>Saving setup...</span><span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';
                    }
                });
            }

            // Image Preview Handlers
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

            // Mobile Phone Normalization & Validation
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

            Array.from(document.querySelectorAll('.ph-number')).forEach(function (input) {
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

            // PSGC Location Suggestions for Landlord Address
            const landlordAddressInput = document.getElementById('landlordAddressInput');
            const landlordAddressSuggestMenu = document.getElementById('landlordAddressSuggestMenu');
            const landlordAddressSuggestStatus = document.getElementById('landlordAddressSuggestStatus');

            if (landlordAddressInput && landlordAddressSuggestMenu && landlordAddressSuggestStatus) {
                const psgcBaseUrl = 'https://psgc.gitlab.io/api';
                let indexedBarangays = [];
                let datasetsPromise = null;
                let suggestionTimer = null;

                const normalizeCode = function (value) {
                    return typeof value === 'string' ? value : '';
                };

                const setAddressStatus = function (message, isError) {
                    landlordAddressSuggestStatus.textContent = message || '';
                    landlordAddressSuggestStatus.classList.toggle('text-danger', Boolean(isError));
                    landlordAddressSuggestStatus.classList.toggle('text-muted', !isError);
                };

                const closeSuggestionMenu = function () {
                    landlordAddressSuggestMenu.classList.remove('is-open');
                    landlordAddressSuggestMenu.innerHTML = '';
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

                    const cityMap = new Map();
                    cities.forEach(function (cityItem) {
                        const cityCode = normalizeCode(cityItem?.code);
                        if (cityCode) cityMap.set(cityCode, cityItem);
                    });

                    const provinceMap = new Map();
                    provinces.forEach(function (provinceItem) {
                        const provinceCode = normalizeCode(provinceItem?.code);
                        if (provinceCode) provinceMap.set(provinceCode, provinceItem);
                    });

                    indexedBarangays = barangays
                        .map(function (barangayItem) {
                            const barangayName = String(barangayItem?.name || '').trim();
                            if (!barangayName) return null;

                            const cityCode = normalizeCode(barangayItem?.cityCode) || normalizeCode(barangayItem?.municipalityCode) || normalizeCode(barangayItem?.subMunicipalityCode);
                            const cityRecord = cityMap.get(cityCode);
                            const cityName = String(cityRecord?.name || '').trim();
                            const provinceCode = normalizeCode(barangayItem?.provinceCode) || normalizeCode(cityRecord?.provinceCode);
                            const provinceName = String(provinceMap.get(provinceCode)?.name || '').trim();
                            const fullAddress = [barangayName, cityName, provinceName].filter(Boolean).join(', ');

                            return [barangayName, fullAddress];
                        })
                        .filter(Boolean)
                        .map(function (item) {
                            return {
                                barangayLower: item[0].toLowerCase(),
                                addressLower: item[1].toLowerCase(),
                                fullAddress: item[1],
                            };
                        });
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
                        suggestions.push(item.fullAddress);
                        if (suggestions.length >= 8) break;
                    }
                    return suggestions;
                };

                const renderSuggestions = function (items) {
                    landlordAddressSuggestMenu.innerHTML = '';
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
                            landlordAddressInput.value = item;
                            closeSuggestionMenu();
                            setAddressStatus('Suggested address applied.', false);
                        });
                        landlordAddressSuggestMenu.appendChild(button);
                    });

                    landlordAddressSuggestMenu.classList.add('is-open');
                };

                const runSuggestionSearch = function () {
                    const term = String(landlordAddressInput.value || '').trim();
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

                landlordAddressInput.addEventListener('focus', function () {
                    loadDatasets().catch(function () {});
                    runSuggestionSearch();
                });

                landlordAddressInput.addEventListener('input', function () {
                    clearTimeout(suggestionTimer);
                    suggestionTimer = setTimeout(runSuggestionSearch, 220);
                });

                landlordAddressInput.addEventListener('keydown', function (event) {
                    if (event.key === 'Escape') closeSuggestionMenu();
                });

                document.addEventListener('click', function (event) {
                    if (landlordAddressSuggestMenu.contains(event.target) || event.target === landlordAddressInput) return;
                    closeSuggestionMenu();
                });
            }

            // Payment Methods Toggle & Validation
            const paymentMethodToggles = Array.from(document.querySelectorAll('.payment-method-toggle'));
            const bankFields = document.getElementById('bankFields');
            const gcashFields = document.getElementById('gcashFields');

            const setRequiredInSection = function (section, requiredFieldNames, enabled) {
                if (!section) return;
                requiredFieldNames.forEach(function (name) {
                    const input = section.querySelector('[name="' + name + '"]');
                    if (!input) return;
                    input.required = enabled;
                    if (!enabled) input.setCustomValidity('');
                });
            };

            const syncPaymentFields = function () {
                const selected = paymentMethodToggles
                    .filter((toggle) => toggle.checked)
                    .map((toggle) => toggle.value);

                const bankEnabled = selected.includes('bank');
                const gcashEnabled = selected.includes('gcash');

                if (bankFields) bankFields.classList.toggle('d-none', !bankEnabled);
                if (gcashFields) gcashFields.classList.toggle('d-none', !gcashEnabled);

                setRequiredInSection(bankFields, ['payment_bank_name', 'payment_account_name'], bankEnabled);
                setRequiredInSection(gcashFields, ['payment_gcash_name', 'payment_gcash_number'], gcashEnabled);
            };

            paymentMethodToggles.forEach(function (toggle) {
                toggle.addEventListener('change', syncPaymentFields);
            });

            // Server-side invalid field automatic panel activation
            const invalidServerField = document.querySelector('#landlordSetupForm .is-invalid');
            if (invalidServerField) {
                const invalidPanelIndex = questionPanels.findIndex((panel) => panel.contains(invalidServerField));
                if (invalidPanelIndex >= 0) {
                    currentPanel = invalidPanelIndex;
                }
            }

            syncPaymentFields();
            updateActivePanel();
        })();
    </script>
</body>
</html>
