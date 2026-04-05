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
            background:
                radial-gradient(540px 220px at 8% -10%, rgba(22, 163, 74, .18), transparent 62%),
                radial-gradient(620px 260px at 110% -20%, rgba(14, 116, 144, .11), transparent 60%),
                var(--shell);
            color: var(--ink);
            min-height: 100vh;
        }

        .setup-wrap {
            max-width: 1040px;
            margin: 0 auto;
            padding: 1.1rem;
        }

        .setup-shell {
            border: 1px solid var(--line);
            border-radius: 1.2rem;
            background: rgba(255, 255, 255, .96);
            box-shadow: 0 14px 34px rgba(2, 8, 20, .08);
            overflow: hidden;
        }

        .setup-head {
            border-bottom: 1px solid var(--line);
            padding: 1.05rem 1.1rem;
            background: linear-gradient(180deg, rgba(240, 253, 244, .78), rgba(255, 255, 255, .96));
        }

        .setup-chip {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            border: 1px solid rgba(20, 83, 45, .2);
            border-radius: 999px;
            background: rgba(167, 243, 208, .28);
            color: #14532d;
            padding: .25rem .6rem;
            font-size: .75rem;
            font-weight: 700;
        }

        .setup-grid {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 330px;
            gap: 1rem;
        }

        .setup-main {
            padding: 1rem;
        }

        .setup-side {
            border-left: 1px solid var(--line);
            background: #fbfdfc;
            padding: 1rem;
        }

        .stepper-shell {
            border: 1px solid var(--line);
            border-radius: .95rem;
            background: linear-gradient(180deg, #ffffff, #f8fafc);
            padding: .8rem .9rem;
            margin-bottom: .9rem;
        }

        .stepper-track {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: .5rem;
            align-items: center;
        }

        .step-chip {
            position: relative;
            border: 1px solid rgba(2, 8, 20, .1);
            border-radius: .85rem;
            background: #fff;
            padding: .55rem .6rem;
            display: flex;
            align-items: center;
            gap: .5rem;
            transition: border-color .2s ease, background-color .2s ease, box-shadow .2s ease;
        }

        .step-chip::after {
            content: '';
            position: absolute;
            top: 50%;
            right: -.42rem;
            width: .42rem;
            height: 2px;
            background: var(--step-idle);
            transform: translateY(-50%);
        }

        .step-chip:last-child::after {
            display: none;
        }

        .step-badge {
            width: 26px;
            height: 26px;
            border-radius: 50%;
            border: 2px solid var(--step-idle);
            color: #64748b;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: .76rem;
            font-weight: 700;
            background: #fff;
            flex: 0 0 auto;
        }

        .step-label {
            font-size: .73rem;
            text-transform: uppercase;
            letter-spacing: .05em;
            color: #64748b;
            margin-bottom: .08rem;
            line-height: 1.2;
            font-weight: 700;
        }

        .step-title {
            font-size: .82rem;
            color: #0f172a;
            line-height: 1.2;
            font-weight: 600;
        }

        .step-chip.active {
            border-color: rgba(20, 83, 45, .3);
            background: rgba(240, 253, 244, .8);
            box-shadow: 0 0 0 .2rem rgba(20, 83, 45, .08);
        }

        .step-chip.active .step-badge {
            border-color: var(--step-active);
            color: var(--step-active);
        }

        .step-chip.active .step-label {
            color: #14532d;
        }

        .step-chip.done {
            border-color: rgba(22, 163, 74, .32);
            background: rgba(220, 252, 231, .68);
        }

        .step-chip.done .step-badge {
            border-color: var(--step-done);
            color: #fff;
            background: var(--step-done);
        }

        .step-chip.done::after {
            background: var(--step-done);
        }

        .step-panel {
            display: none;
        }

        .step-panel.active {
            display: block;
        }

        .section-card {
            border: 1px solid var(--line);
            border-radius: .9rem;
            background: #fff;
            padding: .85rem;
            margin-bottom: .8rem;
        }

        .section-title {
            font-size: .86rem;
            font-weight: 700;
            margin-bottom: .2rem;
        }

        .section-sub {
            font-size: .78rem;
            color: #64748b;
            margin-bottom: .65rem;
        }

        .form-label {
            font-weight: 600;
            font-size: .84rem;
            color: #334155;
            margin-bottom: .35rem;
        }

        .form-control,
        .form-select {
            border-color: var(--line);
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #14532d;
            box-shadow: 0 0 0 .2rem rgba(20, 83, 45, .12);
        }

        .upload-preview {
            width: 78px;
            height: 78px;
            border-radius: .75rem;
            border: 1px solid rgba(2, 8, 20, .1);
            object-fit: cover;
            background: #f8fafc;
        }

        .step-actions {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .6rem;
            margin-top: .35rem;
        }

        .step-actions-right {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
        }

        .btn-brand {
            background: var(--brand);
            border-color: var(--brand);
            color: #fff;
        }

        .btn-brand:hover {
            background: var(--brand-dark);
            border-color: var(--brand-dark);
            color: #fff;
        }

        .btn-next,
        .btn-prev {
            border-radius: 999px;
            min-width: 116px;
        }

        .check-item {
            border: 1px solid var(--line);
            border-radius: .85rem;
            background: #fff;
            padding: .7rem .75rem;
        }

        .check-title {
            font-size: .86rem;
            font-weight: 700;
            margin-bottom: .1rem;
        }

        .check-copy {
            font-size: .78rem;
            color: #64748b;
            margin-bottom: 0;
        }

        .status-box {
            border: 1px solid rgba(2, 8, 20, .1);
            border-radius: .8rem;
            background: #f8fafc;
            padding: .7rem;
        }

        .payment-box {
            border: 1px solid rgba(2, 8, 20, .1);
            border-radius: .8rem;
            background: #f8fafc;
            padding: .7rem;
        }

        @media (max-width: 991.98px) {
            .setup-grid {
                grid-template-columns: 1fr;
            }

            .setup-side {
                border-left: 0;
                border-top: 1px solid var(--line);
            }

            .stepper-track {
                grid-template-columns: 1fr;
            }

            .step-chip::after {
                display: none;
            }
        }
    </style>
</head>
<body>
    @php
        $landlordProfile = $user->landlordProfile;
        $selectedMethods = old('preferred_payment_methods', optional($landlordProfile)->preferred_payment_methods ?? []);
        $selectedMethods = is_array($selectedMethods) ? $selectedMethods : [];
        $permitStatus = (string) ($setupSnapshot['permit_status'] ?? 'not_submitted');
    @endphp

    <div class="setup-wrap">
        <div class="d-flex align-items-center justify-content-between mb-3 px-1">
            <div class="setup-chip"><i class="bi bi-shield-check"></i>Landlord Setup</div>
            <form method="POST" action="{{ route('logout') }}" class="m-0">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-secondary rounded-pill px-3">Logout</button>
            </form>
        </div>

        <div class="setup-shell" data-initial-step="{{ (int) ($initialStep ?? 0) }}">
            <div class="setup-head">
                <h1 class="h4 mb-1">Complete your landlord setup</h1>
                <div class="text-muted small">Flow: account creation -> email verification -> landlord setup -> admin permit review -> full operations unlock.</div>
            </div>

            <div class="setup-grid">
                <div class="setup-main">
                    @if(session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger">
                            <div class="fw-semibold mb-1">Please fix the following:</div>
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="stepper-shell">
                        <div class="stepper-track" id="setupStepper">
                            <div class="step-chip" data-step-nav="0">
                                <span class="step-badge">1</span>
                                <div>
                                    <div class="step-label">Step 1</div>
                                    <div class="step-title">Profile</div>
                                </div>
                            </div>
                            <div class="step-chip" data-step-nav="1">
                                <span class="step-badge">2</span>
                                <div>
                                    <div class="step-label">Step 2</div>
                                    <div class="step-title">Permit</div>
                                </div>
                            </div>
                            <div class="step-chip" data-step-nav="2">
                                <span class="step-badge">3</span>
                                <div>
                                    <div class="step-label">Step 3</div>
                                    <div class="step-title">Billing</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('landlord.setup.update') }}" enctype="multipart/form-data" id="landlordSetupForm">
                        @csrf
                        @method('PUT')

                        <section class="step-panel" data-step="0">
                            <div class="section-card">
                                <div class="section-title">Landlord Profile</div>
                                <div class="section-sub">Provide your identity and boarding house information.</div>

                                <div class="row g-3">
                                    <div class="col-12 col-md-6">
                                        <label class="form-label">Full Name</label>
                                        <input type="text" name="full_name" class="form-control @error('full_name') is-invalid @enderror" value="{{ old('full_name', $user->full_name) }}" required>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <label class="form-label">Email</label>
                                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <label class="form-label">Contact Number</label>
                                        <input type="text" name="contact_number" class="form-control ph-number @error('contact_number') is-invalid @enderror" value="{{ old('contact_number', $user->contact_number) }}" inputmode="numeric" pattern="^09\d{9}$" maxlength="11" placeholder="09XXXXXXXXX" title="Use 11-digit PH mobile format: 09XXXXXXXXX" required>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <label class="form-label">Boarding House Name</label>
                                        <input type="text" name="boarding_house_name" class="form-control @error('boarding_house_name') is-invalid @enderror" value="{{ old('boarding_house_name', $user->boarding_house_name ?? optional($landlordProfile)->boarding_house_name) }}" required>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">About Your Boarding House</label>
                                        <textarea name="about" rows="3" class="form-control @error('about') is-invalid @enderror" required>{{ old('about', optional($landlordProfile)->about) }}</textarea>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Profile Photo (Optional)</label>
                                        <div class="d-flex align-items-center gap-3">
                                            @if(!empty($user->profile_image_path))
                                                <img id="profile_preview" src="{{ asset('storage/' . $user->profile_image_path) }}" alt="Profile" class="upload-preview">
                                            @else
                                                <img id="profile_preview" src="" alt="Profile" class="upload-preview d-none">
                                            @endif
                                            <div class="w-100">
                                                <input type="file" name="profile_image" id="profile_image" class="form-control @error('profile_image') is-invalid @enderror" accept="image/*">
                                                <div class="form-text">JPG, PNG, WEBP, GIF up to 2MB.</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="step-actions">
                                    <div></div>
                                    <div class="step-actions-right">
                                        <button type="button" class="btn btn-brand btn-next" data-next-step="1">Next Step <i class="bi bi-arrow-right ms-1"></i></button>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <section class="step-panel" data-step="1">
                            <div class="section-card">
                                <div class="section-title">Business Verification</div>
                                <div class="section-sub">Upload your permit so admin can validate your landlord account.</div>

                                <div class="status-box mb-3">
                                    <div class="d-flex justify-content-between align-items-center gap-2">
                                        <div>
                                            <div class="small text-muted">Permit Status</div>
                                            <div class="fw-semibold text-capitalize">{{ str_replace('_', ' ', $permitStatus) }}</div>
                                        </div>
                                        <span class="badge {{ $permitStatus === 'approved' ? 'text-bg-success' : ($permitStatus === 'rejected' ? 'text-bg-danger' : ($permitStatus === 'pending' ? 'text-bg-warning' : 'text-bg-secondary')) }}">{{ str_replace('_', ' ', $permitStatus) }}</span>
                                    </div>
                                    @if($permitStatus === 'rejected' && filled($setupSnapshot['permit_rejection_reason'] ?? ''))
                                        <div class="small text-danger mt-2">Reason: {{ $setupSnapshot['permit_rejection_reason'] }}</div>
                                    @elseif($permitStatus === 'pending')
                                        <div class="small text-muted mt-2">Your permit was submitted and is waiting for admin approval.</div>
                                    @endif
                                </div>

                                <div class="row g-3">
                                    <div class="col-12">
                                        <label class="form-label">Business Permit</label>
                                        <input type="file" name="business_permit" class="form-control @error('business_permit') is-invalid @enderror" accept=".pdf,.jpg,.jpeg,.png" {{ empty(optional($landlordProfile)->business_permit_path) ? 'required' : '' }}>
                                        <div class="form-text">Required. Accepted: PDF, JPG, JPEG, PNG (max 2MB).</div>
                                        @if(!empty(optional($landlordProfile)->business_permit_path))
                                            <div class="mt-2">
                                                <a href="{{ asset('storage/' . optional($landlordProfile)->business_permit_path) }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary rounded-pill">
                                                    <i class="bi bi-file-earmark-text me-1"></i>View Current Permit
                                                </a>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <div class="step-actions">
                                    <div>
                                        <button type="button" class="btn btn-outline-secondary btn-prev" data-prev-step="0"><i class="bi bi-arrow-left me-1"></i>Back</button>
                                    </div>
                                    <div class="step-actions-right">
                                        <button type="button" class="btn btn-brand btn-next" data-next-step="2">Next Step <i class="bi bi-arrow-right ms-1"></i></button>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <section class="step-panel" data-step="2">
                            <div class="section-card">
                                <div class="section-title">Billing Setup</div>
                                <div class="section-sub">Select payment methods and fill required account details.</div>

                                <div class="payment-box mb-3">
                                    <label class="form-label mb-2">Preferred Payment Method(s)</label>
                                    <div class="d-flex flex-wrap gap-3">
                                        <div class="form-check">
                                            <input class="form-check-input payment-method-toggle" type="checkbox" name="preferred_payment_methods[]" value="bank" id="pm_bank" @checked(in_array('bank', $selectedMethods, true))>
                                            <label class="form-check-label" for="pm_bank">Bank</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input payment-method-toggle" type="checkbox" name="preferred_payment_methods[]" value="gcash" id="pm_gcash" @checked(in_array('gcash', $selectedMethods, true))>
                                            <label class="form-check-label" for="pm_gcash">GCash</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input payment-method-toggle" type="checkbox" name="preferred_payment_methods[]" value="cash" id="pm_cash" @checked(in_array('cash', $selectedMethods, true))>
                                            <label class="form-check-label" for="pm_cash">Cash</label>
                                        </div>
                                    </div>
                                </div>

                                <div class="row g-3" id="bankFields" data-payment-section="bank">
                                    <div class="col-12 col-md-6">
                                        <label class="form-label">Bank Name</label>
                                        <input type="text" name="payment_bank_name" class="form-control @error('payment_bank_name') is-invalid @enderror" value="{{ old('payment_bank_name', optional($landlordProfile)->payment_bank_name) }}">
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <label class="form-label">Account Name</label>
                                        <input type="text" name="payment_account_name" class="form-control @error('payment_account_name') is-invalid @enderror" value="{{ old('payment_account_name', optional($landlordProfile)->payment_account_name) }}">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Account Number (Optional)</label>
                                        <input type="text" name="payment_account_number" class="form-control @error('payment_account_number') is-invalid @enderror" value="{{ old('payment_account_number', optional($landlordProfile)->payment_account_number) }}">
                                    </div>
                                </div>

                                <div class="row g-3 mt-1" id="gcashFields" data-payment-section="gcash">
                                    <div class="col-12 col-md-6">
                                        <label class="form-label">GCash Name</label>
                                        <input type="text" name="payment_gcash_name" class="form-control @error('payment_gcash_name') is-invalid @enderror" value="{{ old('payment_gcash_name', optional($landlordProfile)->payment_gcash_name) }}">
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <label class="form-label">GCash Number</label>
                                        <input type="text" name="payment_gcash_number" class="form-control ph-number @error('payment_gcash_number') is-invalid @enderror" value="{{ old('payment_gcash_number', optional($landlordProfile)->payment_gcash_number) }}" inputmode="numeric" pattern="^09\d{9}$" maxlength="11" placeholder="09XXXXXXXXX" title="Use 11-digit PH mobile format: 09XXXXXXXXX">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">GCash QR (Optional)</label>
                                        <input type="file" name="payment_gcash_qr" class="form-control @error('payment_gcash_qr') is-invalid @enderror" accept="image/*">
                                        @if(!empty(optional($landlordProfile)->payment_gcash_qr_path))
                                            <div class="mt-2">
                                                <a href="{{ asset('storage/' . optional($landlordProfile)->payment_gcash_qr_path) }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary rounded-pill">View Current QR</a>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <div class="row g-3 mt-1">
                                    <div class="col-12">
                                        <label class="form-label">Payment Instructions (Optional)</label>
                                        <textarea name="payment_instructions" rows="3" class="form-control @error('payment_instructions') is-invalid @enderror">{{ old('payment_instructions', optional($landlordProfile)->payment_instructions) }}</textarea>
                                    </div>
                                </div>

                                <div class="step-actions">
                                    <div>
                                        <button type="button" class="btn btn-outline-secondary btn-prev" data-prev-step="1"><i class="bi bi-arrow-left me-1"></i>Back</button>
                                    </div>
                                    <div class="step-actions-right">
                                        <button type="submit" class="btn btn-brand rounded-pill px-4">Complete Landlord Setup</button>
                                    </div>
                                </div>
                            </div>
                        </section>
                    </form>
                </div>

                <aside class="setup-side">
                    @php
                        $progress = (int) round(($completedCount / max(1, $totalCount)) * 100);
                    @endphp
                    <div class="small text-muted text-uppercase fw-semibold mb-2">Setup Progress</div>
                    <div class="d-flex justify-content-between small mb-1">
                        <span class="text-muted">Completed</span>
                        <span class="fw-semibold">{{ $completedCount }}/{{ $totalCount }}</span>
                    </div>
                    <div class="progress mb-3" style="height:.56rem;border-radius:999px;">
                        <div class="progress-bar" role="progressbar" style="width: {{ $progress }}%;" aria-valuenow="{{ $progress }}" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>

                    <div class="vstack gap-2 mb-3">
                        @foreach($checklist as $item)
                            <div class="check-item">
                                <div class="d-flex align-items-start justify-content-between gap-2">
                                    <div>
                                        <div class="check-title">{{ $item['title'] }}</div>
                                        <p class="check-copy">{{ $item['description'] }}</p>
                                    </div>
                                    <span class="badge {{ $item['completed'] ? 'text-bg-success' : 'text-bg-warning' }}">{{ $item['completed'] ? 'Done' : 'Pending' }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="alert alert-info py-2 px-3 mb-0">
                        <div class="small fw-semibold mb-1">Reminder</div>
                        <div class="small mb-0">After permit upload, admin approval is still required before full landlord operations are unlocked.</div>
                    </div>
                </aside>
            </div>
        </div>
    </div>

    <script>
        (function () {
            const shell = document.querySelector('.setup-shell');
            const form = document.getElementById('landlordSetupForm');
            const stepPanels = Array.from(document.querySelectorAll('[data-step]'));
            const stepNavs = Array.from(document.querySelectorAll('[data-step-nav]'));
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
