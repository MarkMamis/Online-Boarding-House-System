@extends('layouts.landlord')

@section('content')
<div class="profile-edit-shell">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <div class="text-uppercase small text-muted fw-semibold">Account Settings</div>
                    <h1 class="h3 mb-1">
                        <i class="fas fa-user-edit me-2"></i>Edit Profile
                    </h1>
                    <p class="text-muted mb-0">Update your account information and preferences</p>
                </div>
                <div>
                    <!-- <a href="{{ route('landlord.dashboard') }}" class="btn btn-outline-secondary rounded-pill px-3">
                        <i class="fas fa-arrow-left me-1"></i>Back to Dashboard
                    </a> -->
                </div>
            </div>

            <div class="profile-summary mb-4">
                <div class="profile-summary-item">
                    <div class="profile-summary-label">Account Type</div>
                    <div class="profile-summary-value">Landlord</div>
                </div>
                <div class="profile-summary-item">
                    <div class="profile-summary-label">Member Since</div>
                    <div class="profile-summary-value">{{ $user->created_at->format('F d, Y') }}</div>
                </div>
                <div class="profile-summary-item">
                    <div class="profile-summary-label">Status</div>
                    <div class="profile-summary-value">Active</div>
                </div>
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

            @if(session('success'))
                <div class="alert alert-success">
                    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                </div>
            @endif

            <div class="row">
                <div class="col-lg-8">
                    <div class="card shadow-sm profile-main-card">
                        <div class="card-header bg-transparent border-0 pt-3 pb-0 px-3">
                            <h5 class="mb-0">
                                <i class="fas fa-sliders-h me-2"></i>Profile Details
                            </h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('landlord.profile.update') }}" enctype="multipart/form-data">
                                @csrf
                                @method('PUT')

                                <div class="row g-4">
                                    <div class="col-12">
                                        <div class="profile-form-section section-focus-scope" data-focus-scope="personal-info">
                                            <h6 class="fw-semibold mb-3 section-focus-target" id="personal-info">
                                                <i class="fas fa-user me-2"></i>Personal Information
                                            </h6>

                                            <div class="row g-3">
                                                <div class="col-12">
                                                    <label class="form-label">Profile Photo</label>
                                                    <div class="d-flex align-items-center gap-3 profile-photo-panel">
                                                        @if(!empty($user->profile_image_path))
                                                            <img id="profile_image_preview" src="{{ asset('storage/' . $user->profile_image_path) }}" alt="Profile photo" class="rounded-circle border" style="width: 84px; height: 84px; object-fit: cover;">
                                                        @else
                                                            <img id="profile_image_preview" src="" alt="Profile photo" class="rounded-circle border d-none" style="width: 84px; height: 84px; object-fit: cover;">
                                                            <div id="profile_image_placeholder" class="rounded-circle border bg-light d-flex align-items-center justify-content-center" style="width: 84px; height: 84px;">
                                                                <i class="fas fa-user text-muted"></i>
                                                            </div>
                                                        @endif
                                                        <div class="grow">
                                                            <input id="profile_image_input" type="file" name="profile_image" class="form-control @error('profile_image') is-invalid @enderror" accept="image/*">
                                                            @error('profile_image')
                                                                <div class="invalid-feedback">{{ $message }}</div>
                                                            @enderror
                                                            <div class="form-text">JPG, PNG, WEBP, GIF (max 2MB)</div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <label class="form-label">Full Name <span class="text-danger">*</span></label>
                                                    <input type="text" name="full_name" value="{{ old('full_name', $user->full_name) }}"
                                                           class="form-control @error('full_name') is-invalid @enderror" required>
                                                    @error('full_name')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>

                                                <div class="col-md-6">
                                                    <label class="form-label">Email Address <span class="text-danger">*</span></label>
                                                    <input type="email" name="email" value="{{ old('email', $user->email) }}"
                                                           class="form-control @error('email') is-invalid @enderror" required>
                                                    @error('email')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>

                                                <div class="col-md-6">
                                                    <label class="form-label">Contact Number <span class="text-danger">*</span></label>
                                                    <input type="text" name="contact_number" value="{{ old('contact_number', $user->contact_number) }}"
                                                           class="form-control @error('contact_number') is-invalid @enderror"
                                                           placeholder="e.g. +63 912 345 6789" required>
                                                    @error('contact_number')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>

                                                <div class="col-md-6">
                                                    <label class="form-label">Boarding House Name <span class="text-danger">*</span></label>
                                                    <input type="text" name="boarding_house_name" value="{{ old('boarding_house_name', $user->boarding_house_name ?? optional($user->landlordProfile)->boarding_house_name) }}"
                                                           class="form-control @error('boarding_house_name') is-invalid @enderror"
                                                           placeholder="e.g. Mindoro Way Boarding House" required>
                                                    @error('boarding_house_name')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>

                                                <div class="col-12">
                                                    <label class="form-label">About Your Boarding House <span class="text-danger">*</span></label>
                                                    <textarea name="about" rows="3" class="form-control @error('about') is-invalid @enderror" placeholder="Briefly describe your property, amenities, and house rules." required>{{ old('about', optional($user->landlordProfile)->about) }}</textarea>
                                                    @error('about')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                    <div class="form-text">Required to unlock landlord operations.</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <div class="profile-form-section section-focus-scope" data-focus-scope="business-verification">
                                            <h6 class="fw-semibold mb-3 section-focus-target" id="business-verification">
                                                <i class="fas fa-file-shield me-2"></i>Business Verification
                                            </h6>

                                            <div class="row g-3">
                                                <div class="col-12">
                                                    @php
                                                        $permitStatus = $setupSnapshot['permit_status'] ?? (optional($user->landlordProfile)->business_permit_status ?? 'not_submitted');
                                                    @endphp
                                                    <div class="border rounded-3 p-3 bg-light-subtle">
                                                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                                                            <div>
                                                                <div class="small text-muted">Permit Approval Status</div>
                                                                <div class="fw-semibold text-capitalize">{{ str_replace('_', ' ', $permitStatus) }}</div>
                                                            </div>
                                                            <span class="badge rounded-pill {{ $permitStatus === 'approved' ? 'text-bg-success' : ($permitStatus === 'rejected' ? 'text-bg-danger' : 'text-bg-warning') }}">
                                                                {{ str_replace('_', ' ', $permitStatus) }}
                                                            </span>
                                                        </div>
                                                        @if($permitStatus === 'pending')
                                                            <div class="small text-muted mt-2">Your permit has been uploaded and is waiting for admin approval.</div>
                                                        @elseif($permitStatus === 'rejected' && !empty(optional($user->landlordProfile)->business_permit_rejection_reason))
                                                            <div class="small text-danger mt-2">Reason: {{ optional($user->landlordProfile)->business_permit_rejection_reason }}</div>
                                                        @elseif($permitStatus === 'approved')
                                                            <div class="small text-success mt-2">Permit approved. Full landlord operations are now enabled.</div>
                                                        @endif
                                                    </div>
                                                </div>

                                                <div class="col-12">
                                                    <label class="form-label">Business Permit</label>
                                                    <input type="file" name="business_permit" class="form-control @error('business_permit') is-invalid @enderror" accept=".pdf,.jpg,.jpeg,.png">
                                                    @error('business_permit')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                    <div class="form-text">Accepted: PDF, JPG, JPEG, PNG. Max size: 2MB.</div>
                                                    @if(!empty(optional($user->landlordProfile)->business_permit_path))
                                                        <div class="mt-2">
                                                            <a href="{{ asset('storage/' . optional($user->landlordProfile)->business_permit_path) }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary rounded-pill">
                                                                <i class="fas fa-file-alt me-1"></i>View Uploaded Permit
                                                            </a>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        @php
                                            $existingLandlordSignaturePath = (string) (optional($user->landlordProfile)->contract_signature_path ?? '');
                                            $existingLandlordSignatureUrl = $existingLandlordSignaturePath !== '' ? asset('storage/' . $existingLandlordSignaturePath) : '';
                                        @endphp
                                        <div class="profile-form-section section-focus-scope" data-focus-scope="contract-signature">
                                            <h6 class="fw-semibold mb-3 section-focus-target" id="contract-signature">
                                                <i class="fas fa-signature me-2"></i>Contract E-signature
                                            </h6>
                                            <p class="text-muted small mb-3">Choose one method: upload an image or draw directly in the signature canvas.</p>

                                            <div class="signature-grid mb-3">
                                                <div>
                                                    <div class="signature-card">
                                                        @if($existingLandlordSignatureUrl !== '')
                                                            <img id="landlordSignaturePreviewImage" class="signature-preview" src="{{ $existingLandlordSignatureUrl }}" alt="Saved landlord signature" />
                                                            <div id="landlordSignaturePreviewPlaceholder" class="signature-preview-placeholder d-none">No signature selected</div>
                                                        @else
                                                            <img id="landlordSignaturePreviewImage" class="signature-preview d-none" alt="Landlord signature preview" />
                                                            <div id="landlordSignaturePreviewPlaceholder" class="signature-preview-placeholder">No signature selected</div>
                                                        @endif
                                                    </div>
                                                    @if($existingLandlordSignatureUrl !== '')
                                                        <div class="mt-2">
                                                            <a href="{{ $existingLandlordSignatureUrl }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary rounded-pill">
                                                                <i class="fas fa-signature me-1"></i>Open Current Signature
                                                            </a>
                                                        </div>
                                                    @endif
                                                </div>

                                                <div>
                                                    <label class="form-label">Option 1: Upload Signature Image</label>
                                                    <input type="file" id="contractSignatureImageInput" name="contract_signature_image" class="form-control @error('contract_signature_image') is-invalid @enderror" accept=".jpg,.jpeg,.png,.webp">
                                                    @error('contract_signature_image')
                                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                                    @enderror
                                                    <div class="form-text">Accepted: JPG, JPEG, PNG, WEBP (max 2MB).</div>

                                                    <div class="signature-actions mt-3">
                                                        <button type="button" class="btn btn-brand btn-sm rounded-pill px-3" id="openLandlordSignatureModalBtn" data-bs-toggle="modal" data-bs-target="#landlordSignatureCanvasModal">
                                                            <i class="bi bi-pen me-1"></i>Option 2: Sign Here
                                                        </button>
                                                        <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3" id="clearLandlordSignatureBtn">
                                                            <i class="bi bi-eraser me-1"></i>Clear Selection
                                                        </button>
                                                    </div>

                                                    <div class="signature-hint mt-2">Draw in the canvas modal or upload an image. Saving profile will update your contract signature.</div>
                                                    <div id="landlordSignatureStatusText" class="small text-muted mt-2">No new signature selected yet.</div>
                                                </div>
                                            </div>

                                            <input type="hidden" name="contract_signature_data" id="contractSignatureData" value="{{ old('contract_signature_data', '') }}">
                                            @error('contract_signature_data')
                                                <div class="text-danger small">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <div class="profile-form-section section-focus-scope" data-focus-scope="payment-details">
                                            <h6 class="fw-semibold mb-3 section-focus-target" id="payment-details">
                                                <i class="fas fa-wallet me-2"></i>Payment Details
                                            </h6>
                                            <p class="text-muted small mb-3">These details can be shown to tenants during booking and payment steps.</p>

                                            <div class="row g-3">
                                                <div class="col-12">
                                                    <label class="form-label">Preferred Payment Method(s)</label>
                                                    @php
                                                        $preferredPaymentMethods = old('preferred_payment_methods', optional($user->landlordProfile)->preferred_payment_methods ?? []);
                                                        $preferredPaymentMethods = is_array($preferredPaymentMethods) ? $preferredPaymentMethods : [];
                                                    @endphp
                                                    <div class="d-flex flex-wrap gap-3">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="preferred_payment_methods[]" value="bank" id="pref_payment_bank" @checked(in_array('bank', $preferredPaymentMethods, true))>
                                                            <label class="form-check-label" for="pref_payment_bank">Bank</label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="preferred_payment_methods[]" value="gcash" id="pref_payment_gcash" @checked(in_array('gcash', $preferredPaymentMethods, true))>
                                                            <label class="form-check-label" for="pref_payment_gcash">GCash</label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="preferred_payment_methods[]" value="cash" id="pref_payment_cash" @checked(in_array('cash', $preferredPaymentMethods, true))>
                                                            <label class="form-check-label" for="pref_payment_cash">Cash</label>
                                                        </div>
                                                    </div>
                                                    @error('preferred_payment_methods')
                                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                                    @enderror
                                                    @error('preferred_payment_methods.*')
                                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                                    @enderror
                                                    <div class="form-text">You can select one or multiple methods.</div>
                                                </div>

                                                <div class="col-md-6" id="bank_field_bank_name">
                                                    <label class="form-label">Bank Name</label>
                                                    <input type="text" name="payment_bank_name" value="{{ old('payment_bank_name', optional($user->landlordProfile)->payment_bank_name) }}" class="form-control @error('payment_bank_name') is-invalid @enderror" placeholder="e.g. BDO, BPI">
                                                    @error('payment_bank_name')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>

                                                <div class="col-md-6" id="bank_field_account_name">
                                                    <label class="form-label">Account Name</label>
                                                    <input type="text" name="payment_account_name" value="{{ old('payment_account_name', optional($user->landlordProfile)->payment_account_name) }}" class="form-control @error('payment_account_name') is-invalid @enderror" placeholder="e.g. Juan Dela Cruz">
                                                    @error('payment_account_name')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>

                                                <div class="col-md-6" id="bank_field_account_number">
                                                    <label class="form-label">Account Number</label>
                                                    <input type="text" name="payment_account_number" value="{{ old('payment_account_number', optional($user->landlordProfile)->payment_account_number) }}" class="form-control @error('payment_account_number') is-invalid @enderror" placeholder="e.g. 1234-5678-9012">
                                                    @error('payment_account_number')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>

                                                <div class="col-md-6" id="gcash_field_name">
                                                    <label class="form-label">GCash Name</label>
                                                    <input type="text" name="payment_gcash_name" value="{{ old('payment_gcash_name', optional($user->landlordProfile)->payment_gcash_name) }}" class="form-control @error('payment_gcash_name') is-invalid @enderror" placeholder="e.g. Juan Dela Cruz">
                                                    @error('payment_gcash_name')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>

                                                <div class="col-md-6" id="gcash_field_number">
                                                    <label class="form-label">GCash Number</label>
                                                    <input type="text" name="payment_gcash_number" value="{{ old('payment_gcash_number', optional($user->landlordProfile)->payment_gcash_number) }}" class="form-control @error('payment_gcash_number') is-invalid @enderror" placeholder="e.g. 09XX-XXX-XXXX">
                                                    @error('payment_gcash_number')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>

                                                <div class="col-md-6" id="gcash_field_qr">
                                                    <label class="form-label">GCash QR Code <span class="text-muted">(Optional)</span></label>
                                                    <input type="file" name="payment_gcash_qr" class="form-control @error('payment_gcash_qr') is-invalid @enderror" accept=".jpg,.jpeg,.png,.webp" data-has-existing="{{ !empty(optional($user->landlordProfile)->payment_gcash_qr_path) ? '1' : '0' }}">
                                                    @error('payment_gcash_qr')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                    <div class="form-text">Optional upload. Accepted: JPG, JPEG, PNG, WEBP. Max size: 2MB.</div>
                                                    @if(!empty(optional($user->landlordProfile)->payment_gcash_qr_path))
                                                        <div class="mt-2 d-flex align-items-center gap-2 flex-wrap">
                                                            <a href="{{ asset('storage/' . optional($user->landlordProfile)->payment_gcash_qr_path) }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary rounded-pill">
                                                                <i class="fas fa-qrcode me-1"></i>View Current QR
                                                            </a>
                                                        </div>
                                                    @endif
                                                </div>

                                                <div class="col-12">
                                                    <label class="form-label">Payment Instructions</label>
                                                    <textarea name="payment_instructions" rows="3" class="form-control @error('payment_instructions') is-invalid @enderror" placeholder="Example: Send payment proof via chat after transfer.">{{ old('payment_instructions', optional($user->landlordProfile)->payment_instructions) }}</textarea>
                                                    @error('payment_instructions')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <div class="profile-form-section section-focus-scope" data-focus-scope="change-password">
                                            <h6 class="fw-semibold mb-3 section-focus-target" id="change-password">
                                                <i class="fas fa-lock me-2"></i>Change Password (Optional)
                                            </h6>
                                            <p class="text-muted small mb-3">Leave the new password fields blank if you don't want to change it. Current password is required when setting a new password.</p>

                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <label class="form-label">Current Password</label>
                                                    <input type="password" name="current_password" class="form-control @error('current_password') is-invalid @enderror" placeholder="Enter current password">
                                                    @error('current_password')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                    <div class="form-text">Required when changing your password.</div>
                                                </div>

                                                <div class="col-md-6">
                                                    <label class="form-label">New Password</label>
                                                    <input type="password" name="new_password" class="form-control @error('new_password') is-invalid @enderror" minlength="8">
                                                    @error('new_password')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                    <div class="form-text">Minimum 8 characters</div>
                                                </div>

                                                <div class="col-md-6">
                                                    <label class="form-label">Confirm New Password</label>
                                                    <input type="password" name="new_password_confirmation" class="form-control">
                                                    <div class="form-text">Re-enter your new password</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-end mt-4 action-bar">
                                    <button type="submit" class="btn btn-brand rounded-pill px-4">
                                        <i class="fas fa-save me-2"></i>Update Profile
                                    </button>
                                </div>
                            </form>

                            <div class="modal fade" id="landlordSignatureCanvasModal" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-fullscreen">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Draw Contract E-signature</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="signature-canvas-wrap">
                                                <canvas id="landlordSignatureCanvas"></canvas>
                                            </div>
                                            <div class="small text-muted mt-2">Use mouse or finger/stylus to draw your signature.</div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-outline-secondary rounded-pill" id="clearLandlordSignatureCanvasBtn">Clear Canvas</button>
                                            <button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">Cancel</button>
                                            <button type="button" class="btn btn-brand rounded-pill px-4" id="saveLandlordSignatureBtn">Save Signature</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    @php
                        $setupProgress = (int) round((($setupCompletedCount ?? 0) / max(1, (int) ($setupTotalCount ?? 1))) * 100);
                        $profilePermitStatus = (string) ($setupSnapshot['permit_status'] ?? (optional($user->landlordProfile)->business_permit_status ?? 'not_submitted'));
                        $profilePermitApproved = (bool) ($setupSnapshot['permit_approved'] ?? ($profilePermitStatus === 'approved'));
                    @endphp

                    <div class="card shadow-sm profile-side-card mb-3">
                        <div class="card-header bg-transparent border-0 pb-0">
                            <h5 class="mb-0">
                                <i class="fas fa-clipboard-check me-2"></i>Setup Checklist
                            </h5>
                        </div>
                        <div class="card-body profile-side-body">
                            <div class="d-flex justify-content-between align-items-center small">
                                <span class="text-muted">Progress</span>
                                <span class="fw-semibold">{{ $setupCompletedCount ?? 0 }}/{{ $setupTotalCount ?? 0 }}</span>
                            </div>
                            <div class="progress" style="height: 7px;">
                                <div class="progress-bar" role="progressbar" style="width: {{ $setupProgress }}%; background: var(--brand);"></div>
                            </div>

                            <div class="d-grid gap-2 mt-2">
                                @foreach(($setupChecklist ?? []) as $item)
                                    @php
                                        $itemTitle = (string) ($item['title'] ?? '');
                                        $isPropertyOrRoomStep = in_array($itemTitle, ['Add property location', 'Set room availability'], true);
                                        $isLockedByPermit = $isPropertyOrRoomStep && !$profilePermitApproved;
                                        $permitLockText = $profilePermitStatus === 'pending'
                                            ? 'Pending permit approval'
                                            : ($profilePermitStatus === 'rejected'
                                                ? 'Permit rejected'
                                                : 'Permit not approved');
                                    @endphp
                                    <div class="checklist-item">
                                        <div class="d-flex align-items-start gap-2">
                                            <span class="checklist-icon {{ !empty($item['completed']) ? 'is-done' : '' }}">
                                                <i class="fas {{ !empty($item['completed']) ? 'fa-check-circle' : 'fa-circle' }}"></i>
                                            </span>
                                            <div class="grow min-w-0">
                                                <div class="small fw-semibold">{{ $item['title'] ?? 'Setup Item' }}</div>
                                                @if(empty($item['completed']) && $isLockedByPermit)
                                                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill mt-2" disabled>{{ $permitLockText }}</button>
                                                @elseif(empty($item['completed']))
                                                    <a href="{{ $item['action_url'] ?? '#' }}" class="btn btn-sm btn-outline-secondary rounded-pill mt-2">{{ $item['action_label'] ?? 'Open' }}</a>
                                                @else
                                                    <span class="badge text-bg-success rounded-pill mt-2">Done</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="card shadow-sm mt-3 profile-side-card">
                        <div class="card-header bg-transparent border-0 pb-0">
                            <h5 class="mb-0">
                                <i class="fas fa-shield-alt me-2"></i>Security Tips
                            </h5>
                        </div>
                        <div class="card-body profile-side-body">
                            <div class="security-tip-item">
                                <span class="security-tip-icon"><i class="fas fa-key"></i></span>
                                <span class="security-tip-text">Use a strong password with at least 8 characters</span>
                            </div>
                            <div class="security-tip-item">
                                <span class="security-tip-icon"><i class="fas fa-phone-alt"></i></span>
                                <span class="security-tip-text">Keep your contact information up to date</span>
                            </div>
                            <div class="security-tip-item mb-0">
                                <span class="security-tip-icon"><i class="fas fa-home"></i></span>
                                <span class="security-tip-text">Regularly update your boarding house information</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .profile-edit-shell {
        background: linear-gradient(180deg, #ffffff 0%, #fbfdfc 100%);
        border: 1px solid rgba(2,8,20,.08);
        border-radius: 1.25rem;
        box-shadow: 0 10px 26px rgba(2,8,20,.06);
        padding: 1.25rem;
    }
    .profile-summary {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: .75rem;
    }
    .profile-summary-item {
        border: 1px solid rgba(20,83,45,.16);
        background: linear-gradient(180deg, rgba(167,243,208,.18), rgba(255,255,255,.85));
        border-radius: .9rem;
        padding: .7rem .8rem;
        min-width: 0;
    }
    .profile-summary-label {
        font-size: .72rem;
        text-transform: uppercase;
        letter-spacing: .05em;
        color: rgba(2,8,20,.55);
        font-weight: 700;
        margin-bottom: .2rem;
    }
    .profile-summary-value {
        font-size: .94rem;
        font-weight: 700;
        color: #14532d;
    }
    .profile-main-card,
    .profile-side-card {
        border: 1px solid rgba(2,8,20,.08);
        border-radius: 1rem;
        box-shadow: 0 14px 30px rgba(2,8,20,.07);
    }
    .profile-main-card .card-body,
    .profile-side-card .card-body {
        padding: 1rem;
    }
    .profile-side-body {
        display: grid;
        gap: .6rem;
    }
    .profile-main-card .form-label {
        font-weight: 600;
        color: #0f172a;
    }
    .profile-main-card .form-control {
        border-color: rgba(2,8,20,.14);
    }
    .profile-photo-panel {
        border: 1px solid rgba(2,8,20,.08);
        border-radius: .85rem;
        background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
        padding: .7rem;
    }
    .profile-form-section {
        border: 1px solid rgba(2,8,20,.1);
        border-radius: .9rem;
        background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
        padding: .9rem;
    }
    .action-bar {
        position: sticky;
        bottom: .4rem;
        z-index: 3;
        padding: .55rem;
        background: rgba(248,250,252,.88);
        border: 1px solid rgba(2,8,20,.08);
        border-radius: .85rem;
    }
    .side-info-item {
        border: 1px solid rgba(2,8,20,.1);
        border-radius: .75rem;
        background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
        padding: .55rem .65rem;
    }
    .side-info-label {
        font-size: .76rem;
        text-transform: uppercase;
        letter-spacing: .05em;
        color: rgba(2,8,20,.56);
        font-weight: 700;
        margin-bottom: .15rem;
    }
    .side-info-value {
        font-weight: 600;
        color: #0f172a;
        line-height: 1.3;
    }
    .security-tip-item {
        display: grid;
        grid-template-columns: 28px minmax(0, 1fr);
        align-items: start;
        gap: .55rem;
        border: 1px solid rgba(22,101,52,.14);
        border-radius: .75rem;
        background: linear-gradient(180deg, rgba(167,243,208,.16), #ffffff);
        padding: .55rem .6rem;
    }
    .security-tip-icon {
        width: 28px;
        height: 28px;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #166534;
        background: rgba(22,101,52,.12);
        font-size: .76rem;
    }
    .security-tip-text {
        font-size: .86rem;
        color: #0f172a;
        font-weight: 500;
        line-height: 1.3;
    }
    .signature-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: .9rem;
    }
    .signature-card {
        border: 1px dashed rgba(2,8,20,.2);
        border-radius: .75rem;
        min-height: 176px;
        background: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: .75rem;
    }
    .signature-preview {
        width: 100%;
        max-height: 160px;
        object-fit: contain;
    }
    .signature-preview-placeholder {
        font-size: .86rem;
        color: #64748b;
        text-align: center;
    }
    .signature-actions {
        display: inline-flex;
        flex-wrap: wrap;
        gap: .45rem;
    }
    .signature-hint {
        font-size: .8rem;
        color: #64748b;
    }
    .signature-canvas-wrap {
        border: 1px dashed rgba(2,8,20,.22);
        border-radius: .8rem;
        background: #fff;
        height: calc(100vh - 230px);
        min-height: 320px;
        padding: .4rem;
    }
    #landlordSignatureCanvas {
        width: 100%;
        height: 100%;
        border-radius: .55rem;
        touch-action: none;
        cursor: crosshair;
        background: #fff;
    }
    .checklist-item {
        border: 1px solid rgba(2,8,20,.08);
        border-radius: .75rem;
        background: #fff;
        padding: .6rem;
    }
    .checklist-icon {
        color: #f59e0b;
        line-height: 1;
        margin-top: .15rem;
    }
    .checklist-icon.is-done {
        color: #16a34a;
    }
    .section-focus-target {
        scroll-margin-top: 90px;
        transition: background-color .25s ease, box-shadow .25s ease, border-color .25s ease;
        border-radius: .5rem;
        padding: .2rem .35rem;
    }
    .section-focus-target.section-highlight,
    .section-focus-scope.section-highlight {
        background: rgba(22, 163, 74, .12);
        box-shadow: 0 0 0 3px rgba(22, 163, 74, .22) inset, 0 0 0 1px rgba(22, 163, 74, .35);
        border-color: rgba(22, 163, 74, .55) !important;
    }
    .section-focus-scope.section-highlight {
        animation: sectionPulse 1.1s ease 1;
    }
    @keyframes sectionPulse {
        0% { box-shadow: 0 0 0 0 rgba(22, 163, 74, .0); }
        35% { box-shadow: 0 0 0 6px rgba(22, 163, 74, .14); }
        100% { box-shadow: 0 0 0 3px rgba(22, 163, 74, .22) inset, 0 0 0 1px rgba(22, 163, 74, .35); }
    }
    @media (max-width: 991.98px) {
        .profile-summary {
            grid-template-columns: 1fr;
        }
        .profile-edit-shell {
            padding: .95rem;
        }
        .signature-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    (function () {
        const input = document.getElementById('profile_image_input');
        const preview = document.getElementById('profile_image_preview');
        const placeholder = document.getElementById('profile_image_placeholder');
        if (!input || !preview) return;

        input.addEventListener('change', function () {
            const file = input.files && input.files[0];
            if (!file) return;
            if (!file.type || !file.type.startsWith('image/')) return;

            const url = URL.createObjectURL(file);
            preview.src = url;
            preview.classList.remove('d-none');
            if (placeholder) placeholder.classList.add('d-none');
        });
    })();

    (function () {
        let activeHighlight = null;

        const highlightHashTarget = () => {
            const hash = window.location.hash;
            if (!hash) return;

            const target = document.querySelector(hash);
            if (!target) return;

            const scopeName = hash.startsWith('#') ? hash.slice(1) : hash;
            const scopedTarget = document.querySelector(`[data-focus-scope="${scopeName}"]`);
            const highlightTarget = scopedTarget || target.closest('.section-focus-scope') || target;

            if (activeHighlight && activeHighlight !== highlightTarget) {
                activeHighlight.classList.remove('section-highlight');
            }

            target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            highlightTarget.classList.add('section-highlight');
            activeHighlight = highlightTarget;
            window.setTimeout(() => {
                highlightTarget.classList.remove('section-highlight');
                if (activeHighlight === highlightTarget) {
                    activeHighlight = null;
                }
            }, 4200);
        };

        highlightHashTarget();
        window.addEventListener('hashchange', highlightHashTarget);
    })();

    (function () {
        const bankToggle = document.getElementById('pref_payment_bank');
        const gcashToggle = document.getElementById('pref_payment_gcash');

        const bankFieldIds = ['bank_field_bank_name', 'bank_field_account_name', 'bank_field_account_number'];
        const gcashFieldIds = ['gcash_field_name', 'gcash_field_number', 'gcash_field_qr'];

        const bankNameInput = document.querySelector('input[name="payment_bank_name"]');
        const bankAccountNameInput = document.querySelector('input[name="payment_account_name"]');
        const gcashNameInput = document.querySelector('input[name="payment_gcash_name"]');
        const gcashNumberInput = document.querySelector('input[name="payment_gcash_number"]');

        if (!bankToggle || !gcashToggle) return;

        const setGroupVisibility = (ids, visible) => {
            ids.forEach((id) => {
                const el = document.getElementById(id);
                if (!el) return;
                el.classList.toggle('d-none', !visible);
                el.querySelectorAll('input, select, textarea').forEach((field) => {
                    field.disabled = !visible;
                });
            });
        };

        const syncPaymentMethodFields = () => {
            const showBank = !!bankToggle.checked;
            const showGcash = !!gcashToggle.checked;

            setGroupVisibility(bankFieldIds, showBank);
            setGroupVisibility(gcashFieldIds, showGcash);

            if (bankNameInput) bankNameInput.required = showBank;
            if (bankAccountNameInput) bankAccountNameInput.required = showBank;
            if (gcashNameInput) gcashNameInput.required = showGcash;
            if (gcashNumberInput) gcashNumberInput.required = showGcash;
        };

        bankToggle.addEventListener('change', syncPaymentMethodFields);
        gcashToggle.addEventListener('change', syncPaymentMethodFields);
        syncPaymentMethodFields();
    })();

    (function () {
        const signatureDataInput = document.getElementById('contractSignatureData');
        const signatureFileInput = document.getElementById('contractSignatureImageInput');
        const signaturePreviewImage = document.getElementById('landlordSignaturePreviewImage');
        const signaturePreviewPlaceholder = document.getElementById('landlordSignaturePreviewPlaceholder');
        const statusText = document.getElementById('landlordSignatureStatusText');
        const clearSelectionBtn = document.getElementById('clearLandlordSignatureBtn');
        const modalEl = document.getElementById('landlordSignatureCanvasModal');
        const canvas = document.getElementById('landlordSignatureCanvas');
        const clearCanvasBtn = document.getElementById('clearLandlordSignatureCanvasBtn');
        const saveSignatureBtn = document.getElementById('saveLandlordSignatureBtn');
        const modal = (window.bootstrap && modalEl) ? window.bootstrap.Modal.getOrCreateInstance(modalEl) : null;

        if (!signatureDataInput || !signatureFileInput || !signaturePreviewImage || !signaturePreviewPlaceholder || !canvas) {
            return;
        }

        const existingSrc = signaturePreviewImage.getAttribute('src') || '';
        const hasExisting = existingSrc !== '';

        let ctx = null;
        let isDrawing = false;
        let hasStroke = false;

        const updateStatus = function (message, isSuccess) {
            if (!statusText) return;
            statusText.textContent = message;
            statusText.classList.toggle('text-success', !!isSuccess);
        };

        const showPreview = function (src) {
            if (!src) {
                signaturePreviewImage.classList.add('d-none');
                signaturePreviewImage.removeAttribute('src');
                signaturePreviewPlaceholder.classList.remove('d-none');
                return;
            }

            signaturePreviewImage.src = src;
            signaturePreviewImage.classList.remove('d-none');
            signaturePreviewPlaceholder.classList.add('d-none');
        };

        const resetToExisting = function () {
            signatureDataInput.value = '';
            signatureFileInput.value = '';
            showPreview(hasExisting ? existingSrc : '');
            updateStatus('No new signature selected yet.', false);
        };

        const getPoint = function (event) {
            const rect = canvas.getBoundingClientRect();
            return {
                x: event.clientX - rect.left,
                y: event.clientY - rect.top,
            };
        };

        const drawGuide = function (width, height) {
            if (!ctx) return;
            const baselineY = Math.max(40, Math.floor(height - 52));
            const startX = Math.max(16, Math.floor(width * 0.18));
            const endX = Math.min(width - 16, Math.floor(width * 0.82));

            ctx.lineWidth = 1;
            ctx.strokeStyle = 'rgba(71, 85, 105, 0.45)';
            ctx.beginPath();
            ctx.moveTo(startX, baselineY);
            ctx.lineTo(endX, baselineY);
            ctx.stroke();

            ctx.textAlign = 'center';
            ctx.textBaseline = 'top';
            ctx.fillStyle = 'rgba(100, 116, 139, 0.92)';
            ctx.font = '12px "Segoe UI", sans-serif';
            ctx.fillText('Signature over printed name', Math.floor(width / 2), baselineY + 6);
        };

        const resizeCanvas = function () {
            const ratio = Math.max(window.devicePixelRatio || 1, 1);
            const rect = canvas.getBoundingClientRect();
            canvas.width = Math.floor(rect.width * ratio);
            canvas.height = Math.floor(rect.height * ratio);
            ctx = canvas.getContext('2d');
            ctx.setTransform(ratio, 0, 0, ratio, 0, 0);
            ctx.fillStyle = '#ffffff';
            ctx.fillRect(0, 0, rect.width, rect.height);
            drawGuide(rect.width, rect.height);
            ctx.lineWidth = 2;
            ctx.lineCap = 'round';
            ctx.lineJoin = 'round';
            ctx.strokeStyle = '#0f172a';
            hasStroke = false;
        };

        signatureFileInput.addEventListener('change', function () {
            const file = signatureFileInput.files && signatureFileInput.files[0] ? signatureFileInput.files[0] : null;
            if (!file) {
                resetToExisting();
                return;
            }

            if (!/^image\//.test(file.type || '')) {
                alert('Please select an image file for your signature.');
                signatureFileInput.value = '';
                return;
            }

            signatureDataInput.value = '';
            showPreview(URL.createObjectURL(file));
            updateStatus('Upload selected. Save profile to apply this signature.', true);
        });

        if (clearSelectionBtn) {
            clearSelectionBtn.addEventListener('click', function () {
                resetToExisting();
            });
        }

        if (modalEl) {
            modalEl.addEventListener('shown.bs.modal', function () {
                resizeCanvas();
            });
        }

        canvas.addEventListener('pointerdown', function (event) {
            if (!ctx) return;
            isDrawing = true;
            hasStroke = true;
            const point = getPoint(event);
            ctx.beginPath();
            ctx.moveTo(point.x, point.y);
        });

        canvas.addEventListener('pointermove', function (event) {
            if (!isDrawing || !ctx) return;
            const point = getPoint(event);
            ctx.lineTo(point.x, point.y);
            ctx.stroke();
        });

        const stopDrawing = function () {
            isDrawing = false;
        };

        canvas.addEventListener('pointerup', stopDrawing);
        canvas.addEventListener('pointerleave', stopDrawing);

        if (clearCanvasBtn) {
            clearCanvasBtn.addEventListener('click', function () {
                resizeCanvas();
            });
        }

        if (saveSignatureBtn) {
            saveSignatureBtn.addEventListener('click', function () {
                if (!hasStroke) {
                    alert('Please draw your signature first.');
                    return;
                }

                const dataUrl = canvas.toDataURL('image/png');
                signatureDataInput.value = dataUrl;
                signatureFileInput.value = '';
                showPreview(dataUrl);
                updateStatus('Canvas signature saved. Save profile to apply this signature.', true);
                if (modal) {
                    modal.hide();
                }
            });
        }

        if (signatureDataInput.value) {
            showPreview(signatureDataInput.value);
            updateStatus('Canvas signature ready. Save profile to apply this signature.', true);
        }
    })();
</script>
@endpush