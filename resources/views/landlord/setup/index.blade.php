<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Landlord Setup</title>
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

        .setup-link:hover,
        .change-role-link:hover {
            color: #14532d;
            text-decoration: underline;
        }

        .change-role-link {
            font-size: .84rem;
            color: #14532d;
            text-decoration: none;
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

        .step-label {
            font-size: .72rem;
            color: #64748b;
            margin-bottom: 0;
            line-height: 1.2;
            font-weight: 600;
            text-align: left;
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
        .form-select:focus,
        .form-check-input:focus {
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
            z-index: 25;
            display: none;
            max-height: 230px;
            overflow-y: auto;
            border: 1px solid rgba(15, 23, 42, .08);
            border-radius: 1rem;
            background: rgba(255, 255, 255, .98);
            box-shadow: 0 20px 34px rgba(15, 23, 42, .12);
            padding: .3rem;
        }

        .address-suggestion-menu.is-open {
            display: block;
        }

        .address-suggestion-item {
            width: 100%;
            border: 0;
            background: transparent;
            text-align: left;
            padding: .72rem .8rem;
            border-radius: .8rem;
            color: #0f172a;
            font-size: .88rem;
        }

        .address-suggestion-item:hover,
        .address-suggestion-item:focus {
            background: rgba(20, 83, 45, .08);
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
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            align-items: end;
            gap: .6rem;
            margin-top: .5rem;
            padding-top: .85rem;
            border-top: 1px solid rgba(15, 23, 42, .05);
        }

        .step-actions.step-actions-single {
            grid-template-columns: 1fr 1fr;
        }

        .step-actions-left,
        .step-actions-right {
            width: 100%;
        }

        .step-actions-right {
            display: flex;
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

        .method-grid {
            display: grid;
            gap: .75rem;
        }

        .method-option {
            display: flex;
            align-items: center;
            gap: .65rem;
            border: 1px solid rgba(15, 23, 42, .08);
            border-radius: 1rem;
            padding: .78rem .85rem;
            background: #fff;
        }

        .method-option .form-check-input {
            margin-top: 0;
        }

        .method-copy {
            min-width: 0;
        }

        .method-title {
            font-weight: 700;
            color: #0f172a;
        }

        .method-sub {
            font-size: .78rem;
            color: #64748b;
            margin: 0;
        }

        .conditional-group {
            display: grid;
            gap: .75rem;
            margin-top: .85rem;
            padding-top: .2rem;
        }

        .inline-note {
            border-radius: .9rem;
            border: 1px solid rgba(15, 23, 42, .06);
            background: rgba(248, 250, 252, .92);
            padding: .8rem .9rem;
            font-size: .82rem;
            color: #475569;
            margin-bottom: .85rem;
        }

        .inline-note.is-danger {
            border-color: rgba(220, 38, 38, .18);
            background: rgba(254, 242, 242, .95);
            color: #991b1b;
        }

        .inline-note.is-success {
            border-color: rgba(22, 163, 74, .18);
            background: rgba(240, 253, 244, .92);
            color: #166534;
        }

        @media (max-width: 575.98px) {
            .setup-wrap {
                padding: .8rem .7rem 1.25rem;
            }

            .setup-topbar,
            .setup-topbar-actions {
                align-items: flex-start;
                flex-direction: column;
            }

            .setup-topbar-actions {
                gap: .08rem;
            }

            .setup-main {
                padding: .88rem;
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

            .section-title {
                font-size: 1.35rem;
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
        <div class="setup-topbar px-1">
            <p class="setup-topbar-title">Landlord Verification Setup</p>
            <div class="setup-topbar-actions">
                @if(!$setupSnapshot['setup_submitted'])
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

        <div class="setup-shell" data-initial-step="{{ (int) ($initialStep ?? 0) }}">
            <div class="setup-main">
                @if(session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                <div class="stepper-shell">
                    <div class="stepper-track" id="setupStepper">
                        <div class="step-chip" data-step-nav="0">
                            <div class="step-label">Profile</div>
                        </div>
                        <div class="step-chip" data-step-nav="1">
                            <div class="step-label">Permit</div>
                        </div>
                        <div class="step-chip" data-step-nav="2">
                            <div class="step-label">Billing</div>
                        </div>
                    </div>
                    <span class="step-status-text" id="stepStatusText">Step 1 of 3</span>
                </div>

                <form method="POST" action="{{ route('landlord.setup.update') }}" enctype="multipart/form-data" id="landlordSetupForm">
                    @csrf
                    @method('PUT')

                    <section class="step-panel" data-step="0">
                        <div class="section-card step-panel-enter">
                            <div class="section-title">Profile</div>
                            <div class="section-sub">Add the identity and boarding house details needed to verify your landlord account.</div>

                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label">Full Name *</label>
                                    <input type="text" name="full_name" class="form-control @error('full_name') is-invalid @enderror" value="{{ old('full_name', $user->full_name ?: $user->name) }}" required>
                                    @error('full_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Email *</label>
                                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required>
                                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Contact Number *</label>
                                    <input type="text" name="contact_number" class="form-control ph-number @error('contact_number') is-invalid @enderror" value="{{ old('contact_number', $user->contact_number) }}" inputmode="numeric" pattern="^09\d{9}$" maxlength="11" placeholder="09XXXXXXXXX" title="Use 11-digit PH mobile format: 09XXXXXXXXX" required>
                                    @error('contact_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Address *</label>
                                    <div class="address-suggest-wrap">
                                        <textarea id="landlordAddressInput" name="address" rows="2" class="form-control @error('address') is-invalid @enderror" placeholder="Start typing barangay..." required>{{ old('address', $user->address) }}</textarea>
                                        <div id="landlordAddressSuggestMenu" class="address-suggestion-menu" role="listbox" aria-label="Landlord address suggestions"></div>
                                    </div>
                                    <div class="field-hint">Format: Barangay, City/Municipality, Province (e.g., Masipit, Calapan City, Oriental Mindoro).</div>
                                    <div id="landlordAddressSuggestStatus" class="small text-muted mt-1"></div>
                                    @error('address')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Boarding House Name *</label>
                                    <input type="text" name="boarding_house_name" class="form-control @error('boarding_house_name') is-invalid @enderror" value="{{ old('boarding_house_name', $user->boarding_house_name ?? optional($landlordProfile)->boarding_house_name) }}" required>
                                    @error('boarding_house_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-12">
                                    <label class="form-label">About Your Boarding House / Description *</label>
                                    <textarea name="about" rows="4" class="form-control @error('about') is-invalid @enderror" required>{{ old('about', optional($landlordProfile)->about) }}</textarea>
                                    @error('about')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Profile Photo</label>
                                    <div class="d-flex align-items-center gap-3">
                                        @if($hasProfilePhoto)
                                            <img id="profile_preview" src="{{ asset('storage/' . $user->profile_image_path) }}" alt="Profile" class="upload-preview">
                                        @else
                                            <img id="profile_preview" src="" alt="Profile" class="upload-preview d-none">
                                        @endif
                                        <div class="w-100">
                                            <input type="file" name="profile_image" id="profile_image" class="form-control @error('profile_image') is-invalid @enderror" accept="image/*">
                                            <div class="field-hint">Optional. JPG, PNG, WEBP, GIF up to 2MB.</div>
                                            @error('profile_image')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Contract E-sign</label>
                                    <input type="file" name="contract_signature_image" class="form-control @error('contract_signature_image') is-invalid @enderror" accept=".jpg,.jpeg,.png,.webp">
                                    <div class="field-hint">Optional. JPG, JPEG, PNG, WEBP up to 2MB.</div>
                                    @error('contract_signature_image')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                    @if($hasContractSignature)
                                        <a href="{{ asset('storage/' . optional($landlordProfile)->contract_signature_path) }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary rounded-pill mt-2">
                                            <i class="bi bi-pen me-1"></i>View current e-sign
                                        </a>
                                    @endif
                                </div>
                            </div>

                            <div class="step-actions step-actions-single">
                                <div class="step-actions-left">
                                    <span class="d-block" aria-hidden="true"></span>
                                </div>
                                <div class="step-actions-right">
                                    <button type="button" class="btn btn-brand btn-next" data-next-step="1">Next Step</button>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="step-panel" data-step="1">
                            <div class="section-card step-panel-enter">
                                <div class="section-title">Permit</div>
                                <div class="section-sub">Upload permit files if you already have them available for review.</div>

                            @if(($setupSnapshot['permit_status'] ?? '') === 'rejected' && filled($setupSnapshot['permit_rejection_reason'] ?? ''))
                                <div class="inline-note is-danger">Business permit rejected: {{ $setupSnapshot['permit_rejection_reason'] }}</div>
                            @elseif(($setupSnapshot['permit_status'] ?? '') === 'pending')
                                <div class="inline-note">Business permit submitted. Admin review is still pending.</div>
                            @endif

                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label">Business Permit</label>
                                    <input type="file" name="business_permit" class="form-control @error('business_permit') is-invalid @enderror" accept=".pdf,.jpg,.jpeg,.png,.webp">
                                    <div class="field-hint">Optional. Accepted: PDF, JPG, PNG, WEBP up to 2MB.</div>
                                    @error('business_permit')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                    @if($hasBusinessPermit)
                                        <a href="{{ asset('storage/' . optional($landlordProfile)->business_permit_path) }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary rounded-pill mt-2">
                                            <i class="bi bi-file-earmark-text me-1"></i>View current permit
                                        </a>
                                    @endif
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Safety Certificate</label>
                                    <input type="file" name="safety_certificate" class="form-control @error('safety_certificate') is-invalid @enderror" accept=".pdf,.jpg,.jpeg,.png,.webp">
                                    <div class="field-hint">Optional. Accepted: PDF, JPG, PNG, WEBP up to 2MB.</div>
                                    @error('safety_certificate')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                    @if($hasSafetyCertificate)
                                        <a href="{{ asset('storage/' . optional($landlordProfile)->safety_certificate_path) }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary rounded-pill mt-2">
                                            <i class="bi bi-shield-check me-1"></i>View current safety certificate
                                        </a>
                                    @endif
                                </div>
                            </div>

                            <div class="step-actions">
                                <div class="step-actions-left">
                                    <button type="button" class="btn btn-outline-secondary btn-prev" data-prev-step="0">Back</button>
                                </div>
                                <div class="step-actions-right">
                                    <button type="button" class="btn btn-brand btn-next" data-next-step="2">Next Step</button>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="step-panel" data-step="2">
                        <div class="section-card step-panel-enter">
                            <div class="section-title">Billing</div>
                            <div class="section-sub">Choose how tenants can pay you and fill in the matching account details.</div>

                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label">Preferred Payment Method(s) *</label>
                                    <div class="method-grid">
                                        <label class="method-option">
                                            <input class="form-check-input payment-method-toggle" type="checkbox" name="preferred_payment_methods[]" value="bank" id="pm_bank" @checked(in_array('bank', $selectedMethods, true))>
                                            <div class="method-copy">
                                                <div class="method-title">Bank</div>
                                                <p class="method-sub">Collect payments through a bank account.</p>
                                            </div>
                                        </label>
                                        <label class="method-option">
                                            <input class="form-check-input payment-method-toggle" type="checkbox" name="preferred_payment_methods[]" value="gcash" id="pm_gcash" @checked(in_array('gcash', $selectedMethods, true))>
                                            <div class="method-copy">
                                                <div class="method-title">GCash</div>
                                                <p class="method-sub">Accept direct wallet transfers and optional QR scans.</p>
                                            </div>
                                        </label>
                                        <label class="method-option">
                                            <input class="form-check-input payment-method-toggle" type="checkbox" name="preferred_payment_methods[]" value="cash" id="pm_cash" @checked(in_array('cash', $selectedMethods, true))>
                                            <div class="method-copy">
                                                <div class="method-title">Cash</div>
                                                <p class="method-sub">Allow cash payments without extra account fields.</p>
                                            </div>
                                        </label>
                                    </div>
                                    @error('preferred_payment_methods')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                    @error('preferred_payment_methods.*')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                            </div>

                            <div class="conditional-group d-none" id="bankFields" data-payment-section="bank">
                                <div class="col-12">
                                    <label class="form-label">Bank Name *</label>
                                    <input type="text" name="payment_bank_name" class="form-control @error('payment_bank_name') is-invalid @enderror" value="{{ old('payment_bank_name', optional($landlordProfile)->payment_bank_name) }}">
                                    @error('payment_bank_name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Account Name *</label>
                                    <input type="text" name="payment_account_name" class="form-control @error('payment_account_name') is-invalid @enderror" value="{{ old('payment_account_name', optional($landlordProfile)->payment_account_name) }}">
                                    @error('payment_account_name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Account Number</label>
                                    <input type="text" name="payment_account_number" class="form-control @error('payment_account_number') is-invalid @enderror" value="{{ old('payment_account_number', optional($landlordProfile)->payment_account_number) }}">
                                    @error('payment_account_number')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                            </div>

                            <div class="conditional-group d-none" id="gcashFields" data-payment-section="gcash">
                                <div class="col-12">
                                    <label class="form-label">GCash Name *</label>
                                    <input type="text" name="payment_gcash_name" class="form-control @error('payment_gcash_name') is-invalid @enderror" value="{{ old('payment_gcash_name', optional($landlordProfile)->payment_gcash_name) }}">
                                    @error('payment_gcash_name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-12">
                                    <label class="form-label">GCash Number *</label>
                                    <input type="text" name="payment_gcash_number" class="form-control ph-number @error('payment_gcash_number') is-invalid @enderror" value="{{ old('payment_gcash_number', optional($landlordProfile)->payment_gcash_number) }}" inputmode="numeric" pattern="^09\d{9}$" maxlength="11" placeholder="09XXXXXXXXX" title="Use 11-digit PH mobile format: 09XXXXXXXXX">
                                    @error('payment_gcash_number')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-12">
                                    <label class="form-label">GCash QR</label>
                                    <input type="file" name="payment_gcash_qr" class="form-control @error('payment_gcash_qr') is-invalid @enderror" accept="image/*">
                                    <div class="field-hint">Optional. Upload a JPG, PNG, or WEBP QR image up to 2MB.</div>
                                    @error('payment_gcash_qr')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                    @if(!empty(optional($landlordProfile)->payment_gcash_qr_path))
                                        <a href="{{ asset('storage/' . optional($landlordProfile)->payment_gcash_qr_path) }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary rounded-pill mt-2">
                                            View current QR
                                        </a>
                                    @endif
                                </div>
                            </div>

                            <div class="step-actions">
                                <div class="step-actions-left">
                                    <button type="button" class="btn btn-outline-secondary btn-prev" data-prev-step="1">Back</button>
                                </div>
                                <div class="step-actions-right">
                                    <button type="submit" class="btn btn-brand btn-next">Complete Landlord Setup</button>
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
            const shell = document.querySelector('.setup-shell');
            const form = document.getElementById('landlordSetupForm');
            const stepPanels = Array.from(document.querySelectorAll('[data-step]'));
            const stepNavs = Array.from(document.querySelectorAll('[data-step-nav]'));
            const stepStatusText = document.getElementById('stepStatusText');
            let currentStep = Number(shell ? shell.getAttribute('data-initial-step') : 0);

            const updateStepper = function () {
                stepPanels.forEach((panel, index) => {
                    panel.classList.toggle('active', index === currentStep);
                });

                stepNavs.forEach((nav, index) => {
                    nav.classList.remove('active', 'done');
                    if (index < currentStep) nav.classList.add('done');
                    if (index === currentStep) nav.classList.add('active');
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
                    if (field.offsetParent === null) continue;
                    if (!field.checkValidity()) {
                        return field;
                    }
                }

                return null;
            };

            const validateStep = function (stepIndex) {
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
                    moveToStep(Number(button.getAttribute('data-next-step')));
                });
            });

            document.querySelectorAll('[data-prev-step]').forEach((button) => {
                button.addEventListener('click', function () {
                    moveToStep(Number(button.getAttribute('data-prev-step')));
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

                    const cityMap = new Map();
                    cities.forEach(function (cityItem) {
                        const cityCode = normalizeCode(cityItem?.code);
                        if (cityCode) {
                            cityMap.set(cityCode, cityItem);
                        }
                    });

                    const provinceMap = new Map();
                    provinces.forEach(function (provinceItem) {
                        const provinceCode = normalizeCode(provinceItem?.code);
                        if (provinceCode) {
                            provinceMap.set(provinceCode, provinceItem);
                        }
                    });

                    indexedBarangays = barangays
                        .map(function (barangayItem) {
                            const barangayName = String(barangayItem?.name || '').trim();
                            if (!barangayName) {
                                return null;
                            }

                            const cityCode = normalizeCode(barangayItem?.cityCode) || normalizeCode(barangayItem?.municipalityCode) || normalizeCode(barangayItem?.subMunicipalityCode);
                            const cityRecord = cityMap.get(cityCode);
                            const cityName = String(cityRecord?.name || '').trim();
                            const provinceCode = normalizeCode(barangayItem?.provinceCode) || normalizeCode(cityRecord?.provinceCode);
                            const provinceName = String(provinceMap.get(provinceCode)?.name || '').trim();
                            const fullAddress = [barangayName, cityName, provinceName].filter(Boolean).join(', ');

                            return [
                                barangayName,
                                fullAddress,
                            ];
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
                        suggestions.push(item.fullAddress);

                        if (suggestions.length >= 8) {
                            break;
                        }
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
                    if (event.key === 'Escape') {
                        closeSuggestionMenu();
                    }
                });

                document.addEventListener('click', function (event) {
                    if (landlordAddressSuggestMenu.contains(event.target) || event.target === landlordAddressInput) {
                        return;
                    }

                    closeSuggestionMenu();
                });
            }

            const paymentMethodToggles = Array.from(document.querySelectorAll('.payment-method-toggle'));
            const bankFields = document.getElementById('bankFields');
            const gcashFields = document.getElementById('gcashFields');

            const setRequiredInSection = function (section, requiredFieldNames, enabled) {
                if (!section) return;

                requiredFieldNames.forEach(function (name) {
                    const input = section.querySelector('[name="' + name + '"]');
                    if (!input) return;
                    input.required = enabled;
                    if (!enabled) {
                        input.setCustomValidity('');
                    }
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

            const invalidServerField = document.querySelector('#landlordSetupForm .is-invalid');
            if (invalidServerField) {
                const invalidStep = stepPanels.findIndex((panel) => panel.contains(invalidServerField));
                if (invalidStep >= 0) {
                    currentStep = invalidStep;
                }
            }

            syncPaymentFields();
            updateStepper();
        })();
    </script>
</body>
</html>
