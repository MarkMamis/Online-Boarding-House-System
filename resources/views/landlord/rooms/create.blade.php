@extends('layouts.landlord')

@section('content')
    @php
        $errorKeys = $errors->keys();
        $initialPanel = 0;

        if (collect($errorKeys)->contains(fn ($key) => str_starts_with((string) $key, 'detail_images') || (string) $key === 'image')) {
            $initialPanel = 3;
        } elseif (collect($errorKeys)->contains(fn ($key) => in_array((string) $key, ['inclusions'], true))) {
            $initialPanel = 2;
        } elseif (collect($errorKeys)->contains(fn ($key) => in_array((string) $key, ['pricing_model', 'price_per_room', 'price_per_bed', 'requires_advance_payment'], true))) {
            $initialPanel = 1;
        }

        $initialInclusions = collect(explode(',', (string) old('inclusions')))
            ->map(fn ($item) => trim($item))
            ->filter()
            ->values();

        $quickInclusionOptions = [
            'Wifi',
            'Water Bill',
            'Electricity Bill',
            'Electric Fan',
            'Aircon',
            'Comfort Room',
            'Kitchen',
            'Balcony',
            'Study Table',
            'Kitchen Table',
            'Tambayan',
        ];
    @endphp

    <div class="guided-setup-wrapper">
        <div class="guided-setup-card">
            <!-- Setup Header: Clean Context -->
            <div class="setup-header mb-4 d-flex justify-content-between align-items-start">
                <div>
                    <div class="setup-kicker-context">Adding Room To</div>
                    <h1 class="setup-property-title mb-0">{{ $property->name }}</h1>
                    <div class="text-muted small mt-1"><i class="bi bi-geo-alt me-1"></i>{{ $property->address }}</div>
                </div>
                <a href="{{ route('landlord.properties.rooms.index', $property->id) }}" class="setup-back-link" title="Cancel and go back">
                    <i class="bi bi-x-lg fs-5"></i>
                </a>
            </div>

            @if($errors->any())
                <div class="alert alert-danger rounded-3 mb-4">
                    <div class="fw-bold mb-1"><i class="bi bi-exclamation-triangle-fill me-1"></i> Please check the following:</div>
                    <ul class="mb-0 ps-3">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form id="createRoomForm" method="POST" enctype="multipart/form-data" action="{{ route('landlord.properties.rooms.store', $property->id) }}" novalidate>
                @csrf

                <!-- ========================================== -->
                <!-- PANEL 0: ROOM DETAILS                      -->
                <!-- ========================================== -->
                <div class="question-panel {{ $initialPanel === 0 ? 'active' : '' }}" data-panel="0">
                    <div class="question-kicker">Room Details</div>
                    <h2 class="question-title">Tell us about this room.</h2>
                    <p class="question-subtitle">Enter the room identifier and maximum tenant capacity.</p>

                    <div class="mb-3">
                        <label class="form-label" for="roomNumber">Room Name or Number <span class="text-danger">*</span></label>
                        <input
                            type="text"
                            id="roomNumber"
                            name="room_number"
                            value="{{ old('room_number') }}"
                            class="form-control @error('room_number') is-invalid @enderror"
                            required
                            placeholder="e.g. Room 101, Room A, Bedspace 1">
                        @error('room_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="field-hint">How students and you will identify this room.</div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-sm-6">
                            <label class="form-label" for="roomCapacity">Capacity (Beds/Tenants) <span class="text-danger">*</span></label>
                            <input
                                type="number"
                                id="roomCapacity"
                                name="capacity"
                                min="1"
                                max="50"
                                value="{{ old('capacity', 1) }}"
                                class="form-control @error('capacity') is-invalid @enderror"
                                required>
                            @error('capacity')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="field-hint">Maximum number of students who can stay here.</div>
                        </div>

                        <div class="col-sm-6">
                            <label class="form-label" for="roomStatus">Initial Status <span class="text-danger">*</span></label>
                            <select id="roomStatus" name="status" class="form-select @error('status') is-invalid @enderror" required>
                                <option value="available" @selected(old('status', 'available') === 'available')>Available (Ready for bookings)</option>
                                <option value="maintenance" @selected(old('status') === 'maintenance')>Under Maintenance (Hidden)</option>
                                <option value="occupied" @selected(old('status') === 'occupied')>Occupied (Not bookable)</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="field-hint">Default is Available so students can book immediately.</div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="question-actions">
                        <a href="{{ route('landlord.properties.rooms.index', $property->id) }}" class="btn-prev">Cancel</a>
                        <div class="actions-right">
                            <button type="button" class="btn-save-return-review d-none" data-save-panel="0">
                                <i class="bi bi-check-lg me-1"></i> Save &amp; Return to Review
                            </button>
                            <button type="button" class="btn-continue btn-primary-green" data-target="1">
                                Continue <i class="bi bi-arrow-right ms-1"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- ========================================== -->
                <!-- PANEL 1: RENT & PRICING                    -->
                <!-- ========================================== -->
                <div class="question-panel {{ $initialPanel === 1 ? 'active' : '' }}" data-panel="1">
                    <div class="question-kicker">Rent &amp; Billing</div>
                    <h2 class="question-title">Set the monthly rent.</h2>
                    <p class="question-subtitle">Choose whether rent is billed per student or for the entire room.</p>

                    <div class="mb-3">
                        <label class="form-label">Pricing Model <span class="text-danger">*</span></label>
                        <div class="pricing-model-options">
                            <label class="pricing-card-option" for="pricingPerBed">
                                <input
                                    type="radio"
                                    id="pricingPerBed"
                                    name="pricing_model"
                                    value="per_bed"
                                    class="pricing-radio"
                                    @checked(old('pricing_model', 'per_bed') === 'per_bed')>
                                <div class="pricing-card-content">
                                    <div class="pricing-card-title"><i class="bi bi-person me-1"></i> Per Bed / Student</div>
                                    <div class="pricing-card-desc">Each student pays individually for their assigned bed slot.</div>
                                </div>
                            </label>

                            <label class="pricing-card-option" for="pricingPerRoom">
                                <input
                                    type="radio"
                                    id="pricingPerRoom"
                                    name="pricing_model"
                                    value="per_room"
                                    class="pricing-radio"
                                    @checked(old('pricing_model') === 'per_room')>
                                <div class="pricing-card-content">
                                    <div class="pricing-card-title"><i class="bi bi-door-closed me-1"></i> Entire Room</div>
                                    <div class="pricing-card-desc">One fixed monthly rate for renting the entire private room.</div>
                                </div>
                            </label>

                            <label class="pricing-card-option" for="pricingHybrid">
                                <input
                                    type="radio"
                                    id="pricingHybrid"
                                    name="pricing_model"
                                    value="hybrid"
                                    class="pricing-radio"
                                    @checked(old('pricing_model') === 'hybrid')>
                                <div class="pricing-card-content">
                                    <div class="pricing-card-title"><i class="bi bi-grid-fill me-1"></i> Hybrid (Flexible)</div>
                                    <div class="pricing-card-desc">Supports both individual bed bookings and whole-room leases.</div>
                                </div>
                            </label>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-sm-6" id="fieldPricePerBed">
                            <label class="form-label" for="pricePerBed">Price Per Bed (₱ / Month) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">₱</span>
                                <input
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    id="pricePerBed"
                                    name="price_per_bed"
                                    value="{{ old('price_per_bed') }}"
                                    class="form-control @error('price_per_bed') is-invalid @enderror"
                                    placeholder="e.g. 2500.00">
                            </div>
                            @error('price_per_bed')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                            <div class="field-hint">Monthly charge per student occupant.</div>
                        </div>

                        <div class="col-sm-6" id="fieldPricePerRoom">
                            <label class="form-label" for="pricePerRoom">Price Per Room (₱ / Month) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">₱</span>
                                <input
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    id="pricePerRoom"
                                    name="price_per_room"
                                    value="{{ old('price_per_room') }}"
                                    class="form-control @error('price_per_room') is-invalid @enderror"
                                    placeholder="e.g. 5000.00">
                            </div>
                            @error('price_per_room')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                            <div class="field-hint">Monthly rate if leasing the whole room.</div>
                        </div>
                    </div>

                    @if($supportsAdvanceRequirement)
                        <div class="form-check form-switch mb-3 p-3 bg-light rounded-3">
                            <input
                                class="form-check-input ms-0 me-2"
                                type="checkbox"
                                role="switch"
                                id="requiresAdvance"
                                name="requires_advance_payment"
                                value="1"
                                @checked(old('requires_advance_payment'))>
                            <label class="form-check-label fw-semibold" for="requiresAdvance">Require Advance Deposit Upon Move-In</label>
                            <div class="field-hint ps-0">Students will be prompted to deposit 1 month advance payment during onboarding.</div>
                        </div>
                    @endif

                    <!-- Actions -->
                    <div class="question-actions">
                        <button type="button" class="btn-prev" data-target="0">
                            <i class="bi bi-arrow-left me-1"></i> Previous
                        </button>
                        <div class="actions-right">
                            <button type="button" class="btn-save-return-review d-none" data-save-panel="1">
                                <i class="bi bi-check-lg me-1"></i> Save &amp; Return to Review
                            </button>
                            <button type="button" class="btn-continue btn-primary-green" data-target="2">
                                Continue <i class="bi bi-arrow-right ms-1"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- ========================================== -->
                <!-- PANEL 2: ROOM INCLUSIONS                   -->
                <!-- ========================================== -->
                <div class="question-panel {{ $initialPanel === 2 ? 'active' : '' }}" data-panel="2">
                    <div class="question-kicker">Room Inclusions</div>
                    <h2 class="question-title">What's included in the rent?</h2>
                    <p class="question-subtitle">Select utilities, furnishings, and amenities included with this room.</p>

                    <input type="hidden" id="inclusionsInput" name="inclusions" value="{{ old('inclusions') }}">

                    <div class="mb-4">
                        <label class="form-label mb-2">Common Utilities &amp; Features</label>
                        <div class="inclusions-chips-wrap">
                            @foreach($quickInclusionOptions as $option)
                                <button
                                    type="button"
                                    class="inclusion-chip-btn {{ in_array($option, $initialInclusions->all(), true) ? 'selected' : '' }}"
                                    data-value="{{ $option }}">
                                    <i class="bi bi-check2 check-icon"></i>
                                    <span>{{ $option }}</span>
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label" for="customInclusionText">Add Custom Inclusion</label>
                        <div class="input-group">
                            <input
                                type="text"
                                id="customInclusionText"
                                class="form-control"
                                placeholder="e.g. Foam Mattress, Personal Cabinet, Study Lamp">
                            <button type="button" class="btn btn-outline-success px-3" id="btnAddCustomInclusion">
                                <i class="bi bi-plus-lg me-1"></i> Add
                            </button>
                        </div>
                        <div id="customInclusionsList" class="d-flex flex-wrap gap-2 mt-2"></div>
                    </div>

                    <!-- Actions -->
                    <div class="question-actions">
                        <button type="button" class="btn-prev" data-target="1">
                            <i class="bi bi-arrow-left me-1"></i> Previous
                        </button>
                        <div class="actions-right">
                            <button type="button" class="btn-save-return-review d-none" data-save-panel="2">
                                <i class="bi bi-check-lg me-1"></i> Save &amp; Return to Review
                            </button>
                            <button type="button" class="btn-continue btn-primary-green" data-target="3">
                                Continue <i class="bi bi-arrow-right ms-1"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- ========================================== -->
                <!-- PANEL 3: ROOM PHOTOS                       -->
                <!-- ========================================== -->
                <div class="question-panel {{ $initialPanel === 3 ? 'active' : '' }}" data-panel="3">
                    <div class="question-kicker">Room Photos</div>
                    <h2 class="question-title">Upload room photos.</h2>
                    <p class="question-subtitle">Clear photos help students choose this room faster.</p>

                    <div class="mb-4">
                        <label class="form-label">Cover Photo</label>
                        <div class="photo-upload-zone" id="roomCoverZone">
                            <input
                                type="file"
                                id="roomImageInput"
                                name="image"
                                class="file-input-hidden"
                                accept="image/jpeg,image/png,image/webp">
                            <div class="upload-idle" id="roomUploadIdle">
                                <div class="upload-icon-circle">
                                    <i class="bi bi-camera"></i>
                                </div>
                                <div class="upload-prompt">
                                    <span class="upload-action-text">Click to choose room cover photo</span> or drag here
                                </div>
                                <div class="upload-help-text">JPG, PNG, or WebP up to 2MB</div>
                            </div>
                            <div class="upload-preview-card d-none" id="roomUploadPreview">
                                <img id="roomImagePreviewThumb" src="" alt="Room Preview" class="upload-preview-thumb">
                                <div class="upload-file-info">
                                    <div class="upload-file-name text-truncate" id="roomFileName">room.jpg</div>
                                    <div class="upload-file-size" id="roomFileSize">0 KB</div>
                                </div>
                                <button type="button" class="btn-remove-photo" id="btnRemoveRoomPhoto" title="Remove photo">
                                    <i class="bi bi-trash3"></i>
                                </button>
                            </div>
                        </div>
                        @error('image')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Additional Photos -->
                    <div class="mb-4">
                        <label class="form-label">Additional Photos (Optional)</label>
                        <input
                            type="file"
                            id="detailImagesInput"
                            name="detail_images[]"
                            class="form-control"
                            accept="image/jpeg,image/png,image/webp"
                            multiple>
                        <div class="field-hint">Upload bathroom, bed, or window views (up to 4MB each).</div>
                        <div id="detailImagesPreviewGrid" class="d-flex flex-wrap gap-2 mt-2"></div>
                    </div>

                    <!-- Actions -->
                    <div class="question-actions">
                        <button type="button" class="btn-prev" data-target="2">
                            <i class="bi bi-arrow-left me-1"></i> Previous
                        </button>
                        <div class="actions-right">
                            <button type="button" class="btn-save-return-review d-none" data-save-panel="3">
                                <i class="bi bi-check-lg me-1"></i> Save &amp; Return to Review
                            </button>
                            <button type="button" class="btn-continue btn-primary-green" data-target="4">
                                Review Room <i class="bi bi-arrow-right ms-1"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- ========================================== -->
                <!-- PANEL 4: REVIEW & SUBMIT                   -->
                <!-- ========================================== -->
                <div class="question-panel {{ $initialPanel === 4 ? 'active' : '' }}" data-panel="4">
                    <div class="question-kicker">Review &amp; Confirmation</div>
                    <h2 class="question-title">Review your room details.</h2>
                    <p class="question-subtitle">Confirm the configuration below before adding this room to your property.</p>

                    <div class="review-sections-wrap mb-4">
                        <!-- 1. Room Basics -->
                        <div class="review-card">
                            <div class="review-header">
                                <h3 class="review-section-title"><i class="bi bi-door-open me-1"></i> Room Details</h3>
                                <button type="button" class="btn-review-edit" data-edit-panel="0">
                                    <i class="bi bi-pencil-square me-1"></i>Edit
                                </button>
                            </div>
                            <div class="review-grid">
                                <div class="review-item">
                                    <span class="review-label">Room Identifier</span>
                                    <span class="review-val" id="review_room_number">—</span>
                                </div>
                                <div class="review-item">
                                    <span class="review-label">Capacity</span>
                                    <span class="review-val" id="review_capacity">—</span>
                                </div>
                                <div class="review-item">
                                    <span class="review-label">Initial Status</span>
                                    <span class="review-val" id="review_status">Available</span>
                                </div>
                            </div>
                        </div>

                        <!-- 2. Rent & Pricing -->
                        <div class="review-card">
                            <div class="review-header">
                                <h3 class="review-section-title"><i class="bi bi-cash-coin me-1"></i> Rent &amp; Pricing</h3>
                                <button type="button" class="btn-review-edit" data-edit-panel="1">
                                    <i class="bi bi-pencil-square me-1"></i>Edit
                                </button>
                            </div>
                            <div class="review-grid">
                                <div class="review-item">
                                    <span class="review-label">Pricing Model</span>
                                    <span class="review-val" id="review_pricing_model">Per Bed</span>
                                </div>
                                <div class="review-item">
                                    <span class="review-label">Monthly Rent</span>
                                    <span class="review-val text-success fw-bold" id="review_price_amount">₱0.00</span>
                                </div>
                            </div>
                        </div>

                        <!-- 3. Inclusions -->
                        <div class="review-card">
                            <div class="review-header">
                                <h3 class="review-section-title"><i class="bi bi-check2-all me-1"></i> Inclusions</h3>
                                <button type="button" class="btn-review-edit" data-edit-panel="2">
                                    <i class="bi bi-pencil-square me-1"></i>Edit
                                </button>
                            </div>
                            <div class="review-grid">
                                <div class="review-item col-12">
                                    <span class="review-label">Included in Rent</span>
                                    <span class="review-val" id="review_inclusions">None specified</span>
                                </div>
                            </div>
                        </div>

                        <!-- 4. Photos -->
                        <div class="review-card">
                            <div class="review-header">
                                <h3 class="review-section-title"><i class="bi bi-images me-1"></i> Photos</h3>
                                <button type="button" class="btn-review-edit" data-edit-panel="3">
                                    <i class="bi bi-pencil-square me-1"></i>Edit
                                </button>
                            </div>
                            <div class="review-grid">
                                <div class="review-item col-12">
                                    <span class="review-label">Cover Photo</span>
                                    <div class="d-flex align-items-center gap-2 mt-1">
                                        <img id="review_room_photo_thumb" src="" alt="Thumbnail" class="upload-preview d-none" style="width: 50px; height: 50px; object-fit: cover; border-radius: .5rem;">
                                        <span class="review-val" id="review_cover_photo">No photo uploaded</span>
                                    </div>
                                </div>
                                <div class="review-item col-12">
                                    <span class="review-label">Additional Photos</span>
                                    <span class="review-val" id="review_detail_photos_count">0 photos</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="question-actions">
                        <button type="button" class="btn-prev" data-target="3">
                            <i class="bi bi-arrow-left me-1"></i> Previous
                        </button>
                        <button type="submit" class="btn-submit btn-primary-green" id="submitRoomBtn">
                            <i class="bi bi-plus-circle me-1"></i> Add Room
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('styles')
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

    .guided-setup-wrapper {
        width: 100%;
        display: flex;
        justify-content: center;
        padding: 0.5rem 0.5rem 2.5rem;
    }

    .guided-setup-card {
        width: min(680px, 100%);
        background: #ffffff;
        border: 1px solid rgba(226, 232, 240, 0.9);
        border-radius: 1.5rem;
        box-shadow: 0 20px 45px -12px rgba(15, 23, 42, 0.08);
        padding: 2.25rem 2rem;
    }

    @media (max-width: 576px) {
        .guided-setup-card {
            padding: 1.35rem 1.1rem;
            border-radius: 1.15rem;
        }
    }

    .setup-kicker-context {
        font-size: 0.74rem;
        font-weight: 800;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        color: var(--premium-emerald-deep);
        margin-bottom: 0.2rem;
    }

    .setup-property-title {
        font-size: 1.35rem;
        font-weight: 800;
        color: var(--ink);
    }

    .setup-back-link {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 36px;
        height: 36px;
        border-radius: 50%;
        color: var(--muted);
        background: #f1f5f9;
        text-decoration: none;
        transition: all 0.15s ease;
    }

    .setup-back-link:hover {
        color: var(--brand);
        background: #e2e8f0;
    }

    /* Question Panels */
    .question-panel {
        display: none;
    }

    .question-panel.active {
        display: block;
        animation: questionFadeIn 0.22s cubic-bezier(0.16, 1, 0.3, 1);
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
        font-size: 0.75rem;
        font-weight: 800;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        color: var(--premium-emerald-deep);
        margin-bottom: 0.45rem;
    }

    .question-title {
        font-size: clamp(1.4rem, 2.8vw, 1.75rem);
        font-weight: 800;
        letter-spacing: -0.025em;
        line-height: 1.25;
        color: var(--ink);
        margin-bottom: 0.35rem;
    }

    .question-subtitle {
        font-size: 0.92rem;
        color: var(--muted);
        margin-bottom: 1.75rem;
        line-height: 1.5;
    }

    /* Form Inputs */
    .form-label {
        font-size: 0.86rem;
        font-weight: 700;
        color: #334155;
        margin-bottom: 0.4rem;
    }

    .form-control, .form-select {
        border: 1.5px solid var(--line);
        border-radius: 0.75rem;
        padding: 0.65rem 0.95rem;
        font-size: 0.95rem;
        color: var(--ink);
        transition: border-color 0.18s ease, box-shadow 0.18s ease;
    }

    .form-control:focus, .form-select:focus {
        border-color: var(--premium-emerald);
        box-shadow: 0 0 0 3.5px rgba(16, 185, 129, 0.15);
        outline: 0;
    }

    .field-hint {
        font-size: 0.78rem;
        color: var(--muted);
        margin-top: 0.35rem;
        line-height: 1.4;
    }

    /* Pricing Model Selection Cards */
    .pricing-model-options {
        display: flex;
        flex-direction: column;
        gap: 0.65rem;
    }

    .pricing-card-option {
        display: flex;
        align-items: flex-start;
        gap: 0.85rem;
        border: 1.5px solid var(--line);
        border-radius: 0.85rem;
        padding: 0.85rem 1rem;
        background: #ffffff;
        cursor: pointer;
        transition: all 0.15s ease;
        margin: 0;
    }

    .pricing-card-option:hover {
        border-color: #cbd5e1;
        background: #f8fafc;
    }

    .pricing-radio {
        margin-top: 0.25rem;
        accent-color: var(--premium-emerald);
    }

    .pricing-card-option:has(.pricing-radio:checked) {
        border-color: var(--premium-emerald);
        background: rgba(16, 185, 129, 0.05);
    }

    .pricing-card-title {
        font-size: 0.92rem;
        font-weight: 700;
        color: var(--ink);
    }

    .pricing-card-desc {
        font-size: 0.78rem;
        color: var(--muted);
        margin-top: 0.15rem;
    }

    /* Inclusions Chips */
    .inclusions-chips-wrap {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
    }

    .inclusion-chip-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.45rem 0.85rem;
        border: 1.5px solid var(--line);
        border-radius: 999px;
        background: #ffffff;
        font-size: 0.86rem;
        font-weight: 600;
        color: #334155;
        cursor: pointer;
        transition: all 0.15s ease;
    }

    .inclusion-chip-btn .check-icon {
        display: none;
        font-size: 0.95rem;
    }

    .inclusion-chip-btn.selected {
        border-color: var(--premium-emerald);
        background: rgba(16, 185, 129, 0.1);
        color: var(--premium-emerald-deep);
        font-weight: 700;
    }

    .inclusion-chip-btn.selected .check-icon {
        display: inline-block;
    }

    .custom-chip-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.35rem 0.75rem;
        border-radius: 999px;
        background: #f1f5f9;
        border: 1px solid #cbd5e1;
        font-size: 0.84rem;
        font-weight: 600;
        color: #334155;
    }

    .custom-chip-remove {
        background: none;
        border: none;
        padding: 0;
        color: #ef4444;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
    }

    /* Photo Upload Zone */
    .photo-upload-zone {
        border: 2px dashed #cbd5e1;
        border-radius: 1rem;
        padding: 1.5rem 1rem;
        text-align: center;
        background: #f8fafc;
        cursor: pointer;
        transition: all 0.2s ease;
        position: relative;
    }

    .photo-upload-zone:hover {
        border-color: var(--premium-emerald);
        background: rgba(16, 185, 129, 0.03);
    }

    .file-input-hidden {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        opacity: 0;
        cursor: pointer;
        z-index: 2;
    }

    .upload-icon-circle {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        background: rgba(16, 185, 129, 0.12);
        color: var(--premium-emerald-deep);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.4rem;
        margin-bottom: 0.6rem;
    }

    .upload-prompt {
        font-size: 0.92rem;
        font-weight: 600;
        color: #334155;
    }

    .upload-action-text {
        color: var(--premium-emerald-deep);
        text-decoration: underline;
    }

    .upload-help-text {
        font-size: 0.78rem;
        color: var(--muted);
        margin-top: 0.25rem;
    }

    .upload-preview-card {
        display: flex;
        align-items: center;
        gap: 0.85rem;
        background: #ffffff;
        border: 1px solid var(--line);
        border-radius: 0.75rem;
        padding: 0.65rem 0.85rem;
        position: relative;
        z-index: 3;
    }

    .upload-preview-thumb {
        width: 56px;
        height: 56px;
        object-fit: cover;
        border-radius: 0.5rem;
        border: 1px solid #e2e8f0;
        flex-shrink: 0;
    }

    .upload-file-info {
        flex: 1;
        min-width: 0;
        text-align: left;
    }

    .upload-file-name {
        font-size: 0.88rem;
        font-weight: 700;
        color: #1e293b;
    }

    .upload-file-size {
        font-size: 0.76rem;
        color: var(--muted);
    }

    .btn-remove-photo {
        background: transparent;
        border: none;
        color: #ef4444;
        font-size: 1.1rem;
        padding: 0.35rem;
        border-radius: 0.4rem;
        cursor: pointer;
        transition: background-color 0.15s ease;
    }

    .btn-remove-photo:hover {
        background-color: rgba(239, 68, 68, 0.1);
    }

    /* Actions Footer */
    .question-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 0.75rem;
        margin-top: 2rem;
        padding-top: 1.25rem;
        border-top: 1px solid var(--line);
        flex-wrap: wrap;
    }

    .actions-right {
        display: flex;
        align-items: center;
        gap: 0.6rem;
        flex-wrap: wrap;
    }

    .btn-primary-green {
        background: linear-gradient(135deg, #10b981 0%, #047857 100%);
        color: #ffffff;
        border: none;
        border-radius: 999px;
        padding: 0.7rem 1.6rem;
        font-weight: 700;
        font-size: 0.92rem;
        box-shadow: 0 4px 14px rgba(4, 120, 87, 0.2);
        display: inline-flex;
        align-items: center;
        transition: all 0.18s ease;
        cursor: pointer;
    }

    .btn-primary-green:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 18px rgba(4, 120, 87, 0.28);
        color: #ffffff;
    }

    .btn-prev {
        background: transparent;
        color: var(--muted);
        border: 1.5px solid var(--line);
        border-radius: 999px;
        padding: 0.68rem 1.25rem;
        font-weight: 700;
        font-size: 0.9rem;
        display: inline-flex;
        align-items: center;
        text-decoration: none;
        transition: all 0.18s ease;
        cursor: pointer;
    }

    .btn-prev:hover {
        background: #f1f5f9;
        color: #334155;
    }

    .btn-save-return-review {
        background: rgba(16, 185, 129, 0.1);
        color: var(--premium-emerald-deep);
        border: 1.5px solid var(--premium-emerald-deep);
        border-radius: 999px;
        padding: 0.68rem 1.25rem;
        font-weight: 700;
        font-size: 0.9rem;
        display: inline-flex;
        align-items: center;
        cursor: pointer;
        transition: all 0.18s ease;
    }

    .btn-save-return-review:hover {
        background: var(--premium-emerald-deep);
        color: #ffffff;
    }

    /* Review Screen Cards */
    .review-sections-wrap {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .review-card {
        border: 1px solid var(--line);
        border-radius: 1rem;
        background: #ffffff;
        padding: 1.15rem 1.35rem;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }

    .review-card:hover {
        border-color: #cbd5e1;
        box-shadow: 0 4px 12px rgba(15, 23, 42, 0.04);
    }

    .review-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding-bottom: 0.55rem;
        margin-bottom: 0.75rem;
        border-bottom: 1px solid #f1f5f9;
    }

    .review-section-title {
        font-size: 0.8rem;
        font-weight: 800;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: var(--premium-emerald-deep);
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.4rem;
    }

    .btn-review-edit {
        font-size: 0.82rem;
        font-weight: 700;
        color: var(--brand-dark);
        border-radius: 0.5rem;
        padding: 0.25rem 0.6rem;
        background: rgba(20, 83, 45, 0.06);
        border: none;
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        cursor: pointer;
        transition: all 0.15s ease;
    }

    .btn-review-edit:hover {
        background: rgba(20, 83, 45, 0.14);
        color: var(--brand);
    }

    .review-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 0.75rem 1rem;
    }

    .review-item {
        display: flex;
        flex-direction: column;
        gap: 0.15rem;
    }

    .review-label {
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        color: var(--muted);
    }

    .review-val {
        font-size: 0.92rem;
        font-weight: 600;
        color: var(--ink);
    }
</style>
@endpush

@push('scripts')
<script>
    (function () {
        'use strict';

        const form = document.getElementById('createRoomForm');
        const panels = Array.from(document.querySelectorAll('.question-panel'));
        const reviewEditButtons = document.querySelectorAll('.btn-review-edit');
        const saveReturnButtons = document.querySelectorAll('.btn-save-return-review');
        let currentPanel = {{ $initialPanel }};
        let editingFromReview = false;

        // --- Panel Navigation ---
        function showPanel(index) {
            if (index < 0 || index >= panels.length) return;
            panels.forEach((p, idx) => {
                p.classList.toggle('active', idx === index);
            });
            currentPanel = index;

            // Handle Save & Return visibility
            saveReturnButtons.forEach((btn) => {
                btn.classList.toggle('d-none', !editingFromReview);
            });

            // If entering review panel (panel 4), sync review data
            if (index === 4) {
                syncReviewData();
                editingFromReview = false;
                saveReturnButtons.forEach((btn) => btn.classList.add('d-none'));
            }

            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        // --- Pricing Model Visibility Control ---
        const pricingRadios = document.querySelectorAll('input[name="pricing_model"]');
        const fieldPricePerBed = document.getElementById('fieldPricePerBed');
        const fieldPricePerRoom = document.getElementById('fieldPricePerRoom');
        const inputPricePerBed = document.getElementById('pricePerBed');
        const inputPricePerRoom = document.getElementById('pricePerRoom');

        function updatePricingFields() {
            const selectedModel = document.querySelector('input[name="pricing_model"]:checked')?.value || 'per_bed';
            if (selectedModel === 'per_bed') {
                fieldPricePerBed.style.display = 'block';
                fieldPricePerRoom.style.display = 'none';
            } else if (selectedModel === 'per_room') {
                fieldPricePerBed.style.display = 'none';
                fieldPricePerRoom.style.display = 'block';
            } else {
                fieldPricePerBed.style.display = 'block';
                fieldPricePerRoom.style.display = 'block';
            }
        }

        pricingRadios.forEach((r) => r.addEventListener('change', updatePricingFields));
        updatePricingFields();

        // --- Validation per Panel ---
        function validatePanel(panelIndex) {
            let isValid = true;
            const panel = panels[panelIndex];
            if (!panel) return true;

            if (panelIndex === 0) {
                const roomNum = document.getElementById('roomNumber');
                const cap = document.getElementById('roomCapacity');

                if (roomNum && !roomNum.value.trim()) {
                    roomNum.classList.add('is-invalid');
                    isValid = false;
                } else if (roomNum) {
                    roomNum.classList.remove('is-invalid');
                }

                if (cap && (isNaN(Number(cap.value)) || Number(cap.value) < 1)) {
                    cap.classList.add('is-invalid');
                    isValid = false;
                } else if (cap) {
                    cap.classList.remove('is-invalid');
                }
            } else if (panelIndex === 1) {
                const model = document.querySelector('input[name="pricing_model"]:checked')?.value || 'per_bed';
                if (model === 'per_bed' || model === 'hybrid') {
                    if (!inputPricePerBed.value || Number(inputPricePerBed.value) <= 0) {
                        inputPricePerBed.classList.add('is-invalid');
                        isValid = false;
                    } else {
                        inputPricePerBed.classList.remove('is-invalid');
                    }
                }
                if (model === 'per_room' || model === 'hybrid') {
                    if (!inputPricePerRoom.value || Number(inputPricePerRoom.value) <= 0) {
                        inputPricePerRoom.classList.add('is-invalid');
                        isValid = false;
                    } else {
                        inputPricePerRoom.classList.remove('is-invalid');
                    }
                }
            }

            if (!isValid) {
                const firstInvalid = panel.querySelector('.is-invalid');
                if (firstInvalid) firstInvalid.focus();
            }

            return isValid;
        }

        // --- Event Listeners: Continue Buttons ---
        document.querySelectorAll('.btn-continue').forEach((btn) => {
            btn.addEventListener('click', function () {
                const target = Number(btn.getAttribute('data-target'));
                if (validatePanel(currentPanel)) {
                    showPanel(target);
                }
            });
        });

        // --- Event Listeners: Previous Buttons ---
        document.querySelectorAll('.btn-prev').forEach((btn) => {
            btn.addEventListener('click', function () {
                const target = btn.getAttribute('data-target');
                if (target !== null) {
                    showPanel(Number(target));
                }
            });
        });

        // --- Event Listeners: Edit from Review ---
        reviewEditButtons.forEach((btn) => {
            btn.addEventListener('click', function () {
                const targetPanel = Number(btn.getAttribute('data-edit-panel'));
                editingFromReview = true;
                showPanel(targetPanel);
            });
        });

        // --- Event Listeners: Save & Return to Review ---
        saveReturnButtons.forEach((btn) => {
            btn.addEventListener('click', function () {
                if (validatePanel(currentPanel)) {
                    showPanel(4); // Jump back to Review panel
                }
            });
        });

        // --- Inclusions Management ---
        const inclusionsInput = document.getElementById('inclusionsInput');
        const inclusionBtns = document.querySelectorAll('.inclusion-chip-btn');
        const customText = document.getElementById('customInclusionText');
        const btnAddCustom = document.getElementById('btnAddCustomInclusion');
        const customList = document.getElementById('customInclusionsList');
        let selectedInclusions = new Set(
            (inclusionsInput?.value || '')
                .split(',')
                .map((s) => s.trim())
                .filter(Boolean)
        );

        function syncInclusionsInput() {
            if (inclusionsInput) {
                inclusionsInput.value = Array.from(selectedInclusions).join(', ');
            }
        }

        function renderCustomInclusions() {
            customList.innerHTML = '';
            selectedInclusions.forEach((item) => {
                // If it's not in the quick buttons
                const isQuick = Array.from(inclusionBtns).some((b) => b.getAttribute('data-value') === item);
                if (!isQuick) {
                    const badge = document.createElement('span');
                    badge.className = 'custom-chip-badge';
                    badge.innerHTML = `<span>${item}</span><button type="button" class="custom-chip-remove" title="Remove"><i class="bi bi-x"></i></button>`;
                    badge.querySelector('.custom-chip-remove').addEventListener('click', function () {
                        selectedInclusions.delete(item);
                        syncInclusionsInput();
                        renderCustomInclusions();
                    });
                    customList.appendChild(badge);
                }
            });
        }

        inclusionBtns.forEach((btn) => {
            btn.addEventListener('click', function () {
                const val = btn.getAttribute('data-value');
                if (selectedInclusions.has(val)) {
                    selectedInclusions.delete(val);
                    btn.classList.remove('selected');
                } else {
                    selectedInclusions.add(val);
                    btn.classList.add('selected');
                }
                syncInclusionsInput();
            });
        });

        if (btnAddCustom && customText) {
            btnAddCustom.addEventListener('click', function () {
                const val = customText.value.trim();
                if (val && !selectedInclusions.has(val)) {
                    selectedInclusions.add(val);
                    customText.value = '';
                    syncInclusionsInput();
                    renderCustomInclusions();
                }
            });

            customText.addEventListener('keydown', function (e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    btnAddCustom.click();
                }
            });
        }

        renderCustomInclusions();

        // --- Room Cover Photo Upload Handlers ---
        const roomImageInput = document.getElementById('roomImageInput');
        const roomUploadIdle = document.getElementById('roomUploadIdle');
        const roomUploadPreview = document.getElementById('roomUploadPreview');
        const roomImagePreviewThumb = document.getElementById('roomImagePreviewThumb');
        const roomFileName = document.getElementById('roomFileName');
        const roomFileSize = document.getElementById('roomFileSize');
        const btnRemoveRoomPhoto = document.getElementById('btnRemoveRoomPhoto');

        if (roomImageInput) {
            roomImageInput.addEventListener('change', function () {
                const file = roomImageInput.files && roomImageInput.files[0];
                if (!file) return;

                roomFileName.textContent = file.name;
                roomFileSize.textContent = (file.size / 1024 / 1024).toFixed(2) + ' MB';
                roomImagePreviewThumb.src = URL.createObjectURL(file);

                roomUploadIdle.classList.add('d-none');
                roomUploadPreview.classList.remove('d-none');
            });
        }

        if (btnRemoveRoomPhoto) {
            btnRemoveRoomPhoto.addEventListener('click', function (e) {
                e.stopPropagation();
                roomImageInput.value = '';
                roomImagePreviewThumb.src = '';
                roomUploadPreview.classList.add('d-none');
                roomUploadIdle.classList.remove('d-none');
            });
        }

        // --- Detail Images (Additional) Count Preview ---
        const detailImagesInput = document.getElementById('detailImagesInput');
        const detailImagesPreviewGrid = document.getElementById('detailImagesPreviewGrid');
        if (detailImagesInput) {
            detailImagesInput.addEventListener('change', function () {
                detailImagesPreviewGrid.innerHTML = '';
                const files = Array.from(detailImagesInput.files || []);
                files.forEach((file) => {
                    const thumb = document.createElement('img');
                    thumb.src = URL.createObjectURL(file);
                    thumb.style.width = '48px';
                    thumb.style.height = '48px';
                    thumb.style.objectFit = 'cover';
                    thumb.style.borderRadius = '0.4rem';
                    thumb.style.border = '1px solid #cbd5e1';
                    detailImagesPreviewGrid.appendChild(thumb);
                });
            });
        }

        // --- Real-Time Review Data Synchronization ---
        function syncReviewData() {
            const roomNum = document.getElementById('roomNumber')?.value.trim() || '—';
            const capacity = document.getElementById('roomCapacity')?.value.trim() || '1';
            const statusSelect = document.getElementById('roomStatus');
            const statusText = statusSelect?.options[statusSelect.selectedIndex]?.text || 'Available';

            document.getElementById('review_room_number').textContent = roomNum;
            document.getElementById('review_capacity').textContent = capacity + (Number(capacity) === 1 ? ' Bed / Student' : ' Beds / Students');
            document.getElementById('review_status').textContent = statusText;

            // Pricing
            const model = document.querySelector('input[name="pricing_model"]:checked')?.value || 'per_bed';
            const modelNames = { per_bed: 'Per Bed / Student', per_room: 'Entire Room', hybrid: 'Hybrid (Per Bed & Per Room)' };
            document.getElementById('review_pricing_model').textContent = modelNames[model] || model;

            let priceDisplay = '₱0.00';
            if (model === 'per_bed') {
                const p = parseFloat(inputPricePerBed?.value) || 0;
                priceDisplay = `₱${p.toLocaleString('en-US', { minimumFractionDigits: 2 })} / bed / month`;
            } else if (model === 'per_room') {
                const p = parseFloat(inputPricePerRoom?.value) || 0;
                priceDisplay = `₱${p.toLocaleString('en-US', { minimumFractionDigits: 2 })} / room / month`;
            } else {
                const pb = parseFloat(inputPricePerBed?.value) || 0;
                const pr = parseFloat(inputPricePerRoom?.value) || 0;
                priceDisplay = `₱${pb.toLocaleString('en-US', { minimumFractionDigits: 2 })} / bed or ₱${pr.toLocaleString('en-US', { minimumFractionDigits: 2 })} / room`;
            }
            document.getElementById('review_price_amount').textContent = priceDisplay;

            // Inclusions
            const incList = Array.from(selectedInclusions);
            document.getElementById('review_inclusions').textContent = incList.length > 0
                ? incList.join(', ')
                : 'None specified';

            // Photos
            const coverInput = document.getElementById('roomImageInput');
            const coverLabel = document.getElementById('review_cover_photo');
            const coverThumb = document.getElementById('review_room_photo_thumb');
            if (coverInput && coverInput.files && coverInput.files[0]) {
                coverLabel.textContent = coverInput.files[0].name;
                coverThumb.src = URL.createObjectURL(coverInput.files[0]);
                coverThumb.classList.remove('d-none');
            } else {
                coverLabel.textContent = 'No photo uploaded';
                coverThumb.classList.add('d-none');
            }

            const additionalFiles = detailImagesInput?.files ? detailImagesInput.files.length : 0;
            document.getElementById('review_detail_photos_count').textContent = additionalFiles > 0
                ? `${additionalFiles} additional photo(s)`
                : 'None';
        }

        // Initialize active panel
        showPanel(currentPanel);
    })();
</script>
@endpush
