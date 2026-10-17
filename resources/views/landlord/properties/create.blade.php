@extends('layouts.landlord')

@section('content')
    @php
        $errorKeys = $errors->keys();
        $initialPanel = 0;

        if (collect($errorKeys)->contains(fn ($key) => in_array((string) $key, ['latitude', 'longitude'], true))) {
            $initialPanel = 2;
        } elseif (collect($errorKeys)->contains(fn ($key) => str_starts_with((string) $key, 'house_rules') || str_starts_with((string) $key, 'building_inclusions') || str_starts_with((string) $key, 'building_inclusion_custom'))) {
            $initialPanel = 1;
        }

        $amenityCategories = (array) config('property_amenities.categories', []);
        $selectedAmenities = collect(old('building_inclusions', []))
            ->map(fn ($value) => (string) $value)
            ->all();

        $houseRuleCategories = (array) config('property_house_rules.categories', []);
    @endphp

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

    <div class="guided-setup-wrapper">
        <div class="guided-setup-card">
            <!-- Setup Header -->
            <div class="setup-header mb-3">
                <a href="{{ route('landlord.properties.index') }}" class="setup-back-link">
                    <i class="bi bi-arrow-left me-1"></i> Back to Properties
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

            <form id="createPropertyForm" method="POST" enctype="multipart/form-data" action="{{ route('landlord.properties.store') }}" novalidate>
                @csrf

                <!-- ========================================== -->
                <!-- PANEL 0: PROPERTY BASICS                   -->
                <!-- ========================================== -->
                <div class="question-panel {{ $initialPanel === 0 ? 'active' : '' }}" data-panel="0">
                    <div class="question-kicker">Property Basics</div>
                    <h2 class="question-title">Tell us about your boarding house.</h2>
                    <p class="question-subtitle">Start with the core details that students will see first.</p>

                    <div class="mb-3">
                        <label class="form-label" for="propertyName">Boarding House Name <span class="text-danger">*</span></label>
                        <input
                            type="text"
                            id="propertyName"
                            name="name"
                            value="{{ old('name') }}"
                            class="form-control @error('name') is-invalid @enderror"
                            required
                            data-label="Boarding house name"
                            placeholder="e.g. Sunshine Dormitory, Villa Elena">
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="field-hint">The primary commercial or house name of your property.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="propertyAddressInput">Address <span class="text-danger">*</span></label>
                        <div class="address-suggest-wrap position-relative">
                            <input
                                type="text"
                                id="propertyAddressInput"
                                name="address"
                                value="{{ old('address') }}"
                                class="form-control @error('address') is-invalid @enderror"
                                required
                                data-label="Property address"
                                autocomplete="off"
                                placeholder="Start typing barangay, municipality...">
                            <div id="addressSuggestMenu" class="address-suggestion-menu" role="listbox" aria-label="Address suggestions"></div>
                        </div>
                        @error('address')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                        <div class="field-hint">Format: Barangay, City/Municipality, Province (e.g., Masipit, Calapan City, Oriental Mindoro).</div>
                        <div id="addressSuggestStatus" class="small text-muted mt-1"></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="propertyDescription">Description</label>
                        <textarea
                            id="propertyDescription"
                            name="description"
                            rows="3"
                            class="form-control @error('description') is-invalid @enderror"
                            placeholder="Describe your property, walking distance to MinSU campus, nearby landmarks, or special features...">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="field-hint">Optional. Give students a friendly overview of what living here is like.</div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Property Cover Photo</label>
                        <div class="photo-upload-zone" id="photoUploadZone">
                            <input
                                type="file"
                                id="propertyImageInput"
                                name="image"
                                class="file-input-hidden"
                                accept="image/jpeg,image/png,image/webp">
                            <div class="upload-idle" id="uploadIdleState">
                                <div class="upload-icon-circle">
                                    <i class="bi bi-cloud-arrow-up"></i>
                                </div>
                                <div class="upload-prompt">
                                    <span class="upload-action-text">Click to choose photo</span> or drag here
                                </div>
                                <div class="upload-help-text">JPG, PNG, or WebP up to 5MB</div>
                            </div>
                            <div class="upload-preview-card d-none" id="uploadPreviewCard">
                                <img id="propertyImagePreview" src="" alt="Property Preview" class="upload-preview-thumb">
                                <div class="upload-file-info">
                                    <div class="upload-file-name text-truncate" id="uploadFileName">photo.jpg</div>
                                    <div class="upload-file-size" id="uploadFileSize">0 KB</div>
                                </div>
                                <button type="button" class="btn-remove-photo" id="btnRemovePhoto" title="Remove photo">
                                    <i class="bi bi-trash3"></i>
                                </button>
                            </div>
                        </div>
                        @error('image')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Actions -->
                    <div class="question-actions">
                        <a href="{{ route('landlord.properties.index') }}" class="btn-prev">Cancel</a>
                        <div class="actions-right">
                            <button type="button" class="btn-save-return-review d-none" data-save-panel="0">
                                <i class="bi bi-check-lg me-1"></i> Save &amp; Return to Review
                            </button>
                            <button type="submit" name="action" value="draft" class="btn-save-draft">
                                <i class="bi bi-save me-1"></i> Save Draft
                            </button>
                            <button type="button" class="btn-continue btn-primary-green" data-target="1">
                                Continue <i class="bi bi-arrow-right ms-1"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- ========================================== -->
                <!-- PANEL 1: PROPERTY DETAILS (AMENITIES & RULES)-->
                <!-- ========================================== -->
                <div class="question-panel {{ $initialPanel === 1 ? 'active' : '' }}" data-panel="1">
                    <div class="question-kicker">Property Details</div>
                    <h2 class="question-title">Amenities and house rules.</h2>
                    <p class="question-subtitle">Specify what your boarding house offers and your stay policies.</p>

                    <!-- Amenities -->
                    <div class="mb-4">
                        <label class="form-label mb-2">Building Amenities</label>
                        <div class="amenities-container">
                            @foreach($amenityCategories as $category => $items)
                                <div class="amenity-category-block mb-3">
                                    <div class="amenity-category-label">{{ $category }}</div>
                                    <div class="amenities-chips-grid">
                                        @foreach($items as $amenityKey => $amenityLabel)
                                            <label class="amenity-chip-label" for="amenity_{{ $amenityKey }}">
                                                <input
                                                    type="checkbox"
                                                    class="amenity-chip-input"
                                                    id="amenity_{{ $amenityKey }}"
                                                    name="building_inclusions[]"
                                                    value="{{ $amenityKey }}"
                                                    data-label="{{ $amenityLabel }}"
                                                    @checked(in_array((string) $amenityKey, $selectedAmenities, true))>
                                                <span class="amenity-chip-box">
                                                    <i class="bi bi-check2 check-icon"></i>
                                                    <span>{{ $amenityLabel }}</span>
                                                </span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- House Rules -->
                    <div class="mb-4">
                        <label class="form-label mb-2">House Rules</label>
                        <div class="house-rules-accordion">
                            @foreach($houseRuleCategories as $categoryKey => $categoryConfig)
                                @php
                                    $categoryLabel = (string) ($categoryConfig['label'] ?? $categoryKey);
                                    $ruleText = old('house_rules.' . $categoryKey, '');
                                    $ruleSuggestions = (array) ($categoryConfig['rules'] ?? []);
                                @endphp
                                <div class="rule-block mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <label class="small fw-bold text-secondary text-uppercase" for="rules_{{ $categoryKey }}">{{ $categoryLabel }}</label>
                                    </div>
                                    <textarea
                                        class="form-control"
                                        id="rules_{{ $categoryKey }}"
                                        name="house_rules[{{ $categoryKey }}]"
                                        rows="2"
                                        placeholder="e.g. Observe curfew at 10 PM...">{{ $ruleText }}</textarea>
                                    @if(!empty($ruleSuggestions))
                                        <div class="rule-suggestions mt-1 d-flex flex-wrap gap-1">
                                            @foreach($ruleSuggestions as $suggestion)
                                                <button type="button" class="btn-suggestion-pill" data-target="rules_{{ $categoryKey }}" data-text="{{ $suggestion }}">
                                                    + {{ Str::limit($suggestion, 35) }}
                                                </button>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="question-actions">
                        <button type="button" class="btn-prev" data-target="0">
                            <i class="bi bi-arrow-left me-1"></i> Previous
                        </button>
                        <div class="actions-right">
                            <button type="button" class="btn-save-return-review d-none" data-save-panel="1">
                                <i class="bi bi-check-lg me-1"></i> Save &amp; Return to Review
                            </button>
                            <button type="submit" name="action" value="draft" class="btn-save-draft">
                                <i class="bi bi-save me-1"></i> Save Draft
                            </button>
                            <button type="button" class="btn-continue btn-primary-green" data-target="2">
                                Continue <i class="bi bi-arrow-right ms-1"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- ========================================== -->
                <!-- PANEL 2: PROPERTY LOCATION                 -->
                <!-- ========================================== -->
                <div class="question-panel {{ $initialPanel === 2 ? 'active' : '' }}" data-panel="2">
                    <div class="question-kicker">Property Location</div>
                    <h2 class="question-title">Pin your location on the map.</h2>
                    <p class="question-subtitle">Click anywhere on the map or use your current location to help students find you.</p>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label" for="latitude">Latitude</label>
                            <input
                                type="number"
                                step="0.000001"
                                id="latitude"
                                name="latitude"
                                value="{{ old('latitude') }}"
                                class="form-control"
                                placeholder="e.g. 13.4115">
                        </div>
                        <div class="col-6">
                            <label class="form-label" for="longitude">Longitude</label>
                            <input
                                type="number"
                                step="0.000001"
                                id="longitude"
                                name="longitude"
                                value="{{ old('longitude') }}"
                                class="form-control"
                                placeholder="e.g. 121.1803">
                        </div>
                    </div>

                    <div class="map-card mb-4">
                        <div id="property-map" class="map-frame" style="height: 300px; width: 100%; border-radius: .75rem 0.75rem 0 0;"></div>
                        <div class="map-footer d-flex justify-content-between align-items-center flex-wrap gap-2 p-2 bg-light border-top rounded-bottom">
                            <button type="button" id="useCurrentLocationBtn" class="btn btn-sm btn-outline-success rounded-pill px-3">
                                <i class="bi bi-geo-alt me-1"></i> Use My Location
                            </button>
                            <span id="locationStatus" class="small text-muted">Click map to set pin</span>
                        </div>
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
                            <button type="submit" name="action" value="draft" class="btn-save-draft">
                                <i class="bi bi-save me-1"></i> Save Draft
                            </button>
                            <button type="button" class="btn-continue btn-primary-green" data-target="3">
                                Review Details <i class="bi bi-arrow-right ms-1"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- ========================================== -->
                <!-- PANEL 3: REVIEW & SUBMIT                   -->
                <!-- ========================================== -->
                <div class="question-panel" data-panel="3">
                    <div class="question-kicker">Review &amp; Confirmation</div>
                    <h2 class="question-title">Review your boarding house.</h2>
                    <p class="question-subtitle">Check the details below before submitting for approval or saving as draft.</p>

                    <div class="review-sections-wrap mb-4">
                        <!-- 1. Basics -->
                        <div class="review-card">
                            <div class="review-header">
                                <h3 class="review-section-title"><i class="bi bi-house me-1"></i> Property Basics</h3>
                                <button type="button" class="btn-review-edit" data-edit-panel="0">
                                    <i class="bi bi-pencil-square me-1"></i>Edit
                                </button>
                            </div>
                            <div class="review-grid">
                                <div class="review-item">
                                    <span class="review-label">Boarding House Name</span>
                                    <span class="review-val" id="review_name">—</span>
                                </div>
                                <div class="review-item">
                                    <span class="review-label">Address</span>
                                    <span class="review-val" id="review_address">—</span>
                                </div>
                                <div class="review-item col-12">
                                    <span class="review-label">Description</span>
                                    <span class="review-val text-wrap" id="review_description">Not provided</span>
                                </div>
                                <div class="review-item col-12">
                                    <span class="review-label">Cover Photo</span>
                                    <div class="d-flex align-items-center gap-2 mt-1">
                                        <img id="review_photo_thumb" src="" alt="Thumbnail" class="upload-preview d-none" style="width: 50px; height: 50px; object-fit: cover; border-radius: .5rem;">
                                        <span class="review-val" id="review_photo_name">No photo uploaded</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 2. Amenities & Rules -->
                        <div class="review-card">
                            <div class="review-header">
                                <h3 class="review-section-title"><i class="bi bi-stars me-1"></i> Amenities &amp; Rules</h3>
                                <button type="button" class="btn-review-edit" data-edit-panel="1">
                                    <i class="bi bi-pencil-square me-1"></i>Edit
                                </button>
                            </div>
                            <div class="review-grid">
                                <div class="review-item col-12">
                                    <span class="review-label">Selected Amenities</span>
                                    <span class="review-val" id="review_amenities">None selected</span>
                                </div>
                                <div class="review-item col-12">
                                    <span class="review-label">House Rules</span>
                                    <span class="review-val text-wrap" id="review_rules">No rules specified</span>
                                </div>
                            </div>
                        </div>

                        <!-- 3. Location -->
                        <div class="review-card">
                            <div class="review-header">
                                <h3 class="review-section-title"><i class="bi bi-geo-alt me-1"></i> Map Location</h3>
                                <button type="button" class="btn-review-edit" data-edit-panel="2">
                                    <i class="bi bi-pencil-square me-1"></i>Edit
                                </button>
                            </div>
                            <div class="review-grid">
                                <div class="review-item">
                                    <span class="review-label">Latitude</span>
                                    <span class="review-val" id="review_latitude">Not set</span>
                                </div>
                                <div class="review-item">
                                    <span class="review-label">Longitude</span>
                                    <span class="review-val" id="review_longitude">Not set</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-info border-0 rounded-3 bg-light d-flex align-items-center gap-2 mb-4 p-3 text-secondary">
                        <i class="bi bi-info-circle text-primary fs-5 flex-shrink-0"></i>
                        <div class="small">
                            <strong>Next Step:</strong> After saving your property, you will be guided directly to add individual rooms, set capacities, and configure monthly pricing.
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="question-actions">
                        <button type="button" class="btn-prev" data-target="2">
                            <i class="bi bi-arrow-left me-1"></i> Previous
                        </button>
                        <div class="actions-right">
                            <button type="submit" name="action" value="draft" class="btn-save-draft">
                                <i class="bi bi-save me-1"></i> Save Draft
                            </button>
                            <button type="submit" name="action" value="submit" class="btn-submit btn-primary-green" id="submitPropertyBtn">
                                Submit for Review <i class="bi bi-send-check ms-1"></i>
                            </button>
                        </div>
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

    .setup-back-link {
        display: inline-flex;
        align-items: center;
        color: var(--muted);
        font-size: 0.88rem;
        font-weight: 600;
        text-decoration: none;
        transition: color 0.15s ease;
    }

    .setup-back-link:hover {
        color: var(--brand);
        text-decoration: underline;
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

    /* Amenities Chips */
    .amenity-category-label {
        font-size: 0.75rem;
        text-transform: uppercase;
        font-weight: 800;
        letter-spacing: 0.08em;
        color: var(--muted);
        margin-bottom: 0.4rem;
    }

    .amenities-chips-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
    }

    .amenity-chip-label {
        margin: 0;
        cursor: pointer;
    }

    .amenity-chip-input {
        display: none;
    }

    .amenity-chip-box {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.45rem 0.85rem;
        border: 1.5px solid var(--line);
        border-radius: 999px;
        background: #ffffff;
        font-size: 0.86rem;
        font-weight: 600;
        color: #334155;
        transition: all 0.15s ease;
    }

    .amenity-chip-box .check-icon {
        display: none;
        font-size: 0.95rem;
    }

    .amenity-chip-input:checked + .amenity-chip-box {
        border-color: var(--premium-emerald);
        background: rgba(16, 185, 129, 0.1);
        color: var(--premium-emerald-deep);
        font-weight: 700;
    }

    .amenity-chip-input:checked + .amenity-chip-box .check-icon {
        display: inline-block;
    }

    /* House Rules & Suggestions */
    .btn-suggestion-pill {
        border: 1px solid #cbd5e1;
        background: #f8fafc;
        border-radius: 999px;
        padding: 0.15rem 0.55rem;
        font-size: 0.75rem;
        font-weight: 600;
        color: #475569;
        cursor: pointer;
        transition: all 0.15s ease;
    }

    .btn-suggestion-pill:hover {
        border-color: var(--premium-emerald);
        background: rgba(16, 185, 129, 0.08);
        color: var(--premium-emerald-deep);
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

    .btn-save-draft {
        background: #ffffff;
        color: var(--premium-emerald-deep);
        border: 1.5px solid var(--premium-emerald-deep);
        border-radius: 999px;
        padding: 0.68rem 1.25rem;
        font-weight: 700;
        font-size: 0.9rem;
        display: inline-flex;
        align-items: center;
        transition: all 0.18s ease;
        cursor: pointer;
    }

    .btn-save-draft:hover {
        background: rgba(16, 185, 129, 0.08);
        color: var(--premium-emerald-deep);
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
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
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

    /* PSGC Address Suggestions */
    .address-suggestion-menu {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: #ffffff;
        border: 1px solid #cbd5e1;
        border-radius: 0.65rem;
        box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.15);
        max-height: 220px;
        overflow-y: auto;
        z-index: 1050;
        display: none;
        margin-top: 0.25rem;
    }

    .address-suggestion-menu.is-open {
        display: block;
    }

    .address-suggestion-item {
        width: 100%;
        text-align: left;
        background: none;
        border: none;
        padding: 0.55rem 0.85rem;
        font-size: 0.86rem;
        color: #334155;
        border-bottom: 1px solid #f1f5f9;
        cursor: pointer;
        transition: background-color 0.12s ease;
    }

    .address-suggestion-item:hover {
        background-color: #f8fafc;
        color: var(--brand);
    }
</style>
@endpush

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    (function () {
        'use strict';

        const form = document.getElementById('createPropertyForm');
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

            // If entering review panel (panel 3), sync review data
            if (index === 3) {
                syncReviewData();
                editingFromReview = false;
                saveReturnButtons.forEach((btn) => btn.classList.add('d-none'));
            }

            // Invalidate Leaflet map size when location panel is displayed
            if (index === 2 && map) {
                setTimeout(function () {
                    map.invalidateSize();
                }, 120);
            }

            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        // --- Validation per Panel ---
        function validatePanel(panelIndex) {
            let isValid = true;
            const panel = panels[panelIndex];
            if (!panel) return true;

            if (panelIndex === 0) {
                const nameInput = document.getElementById('propertyName');
                const addressInput = document.getElementById('propertyAddressInput');

                if (nameInput && !nameInput.value.trim()) {
                    nameInput.classList.add('is-invalid');
                    isValid = false;
                } else if (nameInput) {
                    nameInput.classList.remove('is-invalid');
                }

                if (addressInput && !addressInput.value.trim()) {
                    addressInput.classList.add('is-invalid');
                    isValid = false;
                } else if (addressInput) {
                    addressInput.classList.remove('is-invalid');
                }

                if (!isValid) {
                    const firstInvalid = panel.querySelector('.is-invalid');
                    if (firstInvalid) firstInvalid.focus();
                }
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
                    showPanel(3); // Jump back to Review panel
                }
            });
        });

        // --- Real-Time Review Data Synchronization ---
        function syncReviewData() {
            const name = document.getElementById('propertyName')?.value.trim() || '—';
            const address = document.getElementById('propertyAddressInput')?.value.trim() || '—';
            const desc = document.getElementById('propertyDescription')?.value.trim() || 'Not provided';
            const lat = document.getElementById('latitude')?.value.trim() || 'Not set';
            const lng = document.getElementById('longitude')?.value.trim() || 'Not set';

            document.getElementById('review_name').textContent = name;
            document.getElementById('review_address').textContent = address;
            document.getElementById('review_description').textContent = desc;
            document.getElementById('review_latitude').textContent = lat;
            document.getElementById('review_longitude').textContent = lng;

            // Amenities
            const checkedAmenities = Array.from(document.querySelectorAll('.amenity-chip-input:checked'))
                .map((cb) => cb.getAttribute('data-label') || cb.value);
            document.getElementById('review_amenities').textContent = checkedAmenities.length > 0
                ? checkedAmenities.join(', ')
                : 'None selected';

            // Rules
            const rulesText = Array.from(document.querySelectorAll('textarea[name^="house_rules"]'))
                .map((t) => t.value.trim())
                .filter(Boolean)
                .join('; ');
            document.getElementById('review_rules').textContent = rulesText || 'No rules specified';

            // Photo
            const imageInput = document.getElementById('propertyImageInput');
            const reviewPhotoName = document.getElementById('review_photo_name');
            const reviewPhotoThumb = document.getElementById('review_photo_thumb');
            if (imageInput && imageInput.files && imageInput.files[0]) {
                reviewPhotoName.textContent = imageInput.files[0].name;
                reviewPhotoThumb.src = URL.createObjectURL(imageInput.files[0]);
                reviewPhotoThumb.classList.remove('d-none');
            } else {
                reviewPhotoName.textContent = 'No photo uploaded';
                reviewPhotoThumb.classList.add('d-none');
            }
        }

        // --- Image Upload Handlers ---
        const imageInput = document.getElementById('propertyImageInput');
        const uploadIdleState = document.getElementById('uploadIdleState');
        const uploadPreviewCard = document.getElementById('uploadPreviewCard');
        const imagePreview = document.getElementById('propertyImagePreview');
        const fileNameLabel = document.getElementById('uploadFileName');
        const fileSizeLabel = document.getElementById('uploadFileSize');
        const btnRemovePhoto = document.getElementById('btnRemovePhoto');

        if (imageInput) {
            imageInput.addEventListener('change', function () {
                const file = imageInput.files && imageInput.files[0];
                if (!file) return;

                fileNameLabel.textContent = file.name;
                fileSizeLabel.textContent = (file.size / 1024 / 1024).toFixed(2) + ' MB';
                imagePreview.src = URL.createObjectURL(file);

                uploadIdleState.classList.add('d-none');
                uploadPreviewCard.classList.remove('d-none');
            });
        }

        if (btnRemovePhoto) {
            btnRemovePhoto.addEventListener('click', function (e) {
                e.stopPropagation();
                imageInput.value = '';
                imagePreview.src = '';
                uploadPreviewCard.classList.add('d-none');
                uploadIdleState.classList.remove('d-none');
            });
        }

        // --- House Rule Suggestion Pills ---
        document.querySelectorAll('.btn-suggestion-pill').forEach((btn) => {
            btn.addEventListener('click', function () {
                const targetId = btn.getAttribute('data-target');
                const text = btn.getAttribute('data-text');
                const textarea = document.getElementById(targetId);
                if (textarea) {
                    const currentVal = textarea.value.trim();
                    textarea.value = currentVal ? currentVal + '\n' + text : text;
                    textarea.focus();
                }
            });
        });

        // --- Leaflet Map Setup ---
        let map = null;
        let marker = null;
        const latInput = document.getElementById('latitude');
        const lngInput = document.getElementById('longitude');
        const locationStatus = document.getElementById('locationStatus');
        const useLocationBtn = document.getElementById('useCurrentLocationBtn');

        const initialLat = parseFloat(latInput?.value) || 13.4115;
        const initialLng = parseFloat(lngInput?.value) || 121.1803;

        try {
            map = L.map('property-map').setView([initialLat, initialLng], 14);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors',
                maxZoom: 19
            }).addTo(map);

            if (latInput?.value && lngInput?.value) {
                marker = L.marker([initialLat, initialLng]).addTo(map);
                locationStatus.textContent = 'Location pinned';
            }

            map.on('click', function (e) {
                const lat = e.latlng.lat.toFixed(6);
                const lng = e.latlng.lng.toFixed(6);

                if (latInput) latInput.value = lat;
                if (lngInput) lngInput.value = lng;

                if (marker) {
                    marker.setLatLng(e.latlng);
                } else {
                    marker = L.marker(e.latlng).addTo(map);
                }
                locationStatus.textContent = `Pinned: ${lat}, ${lng}`;
            });

            if (useLocationBtn && navigator.geolocation) {
                useLocationBtn.addEventListener('click', function () {
                    locationStatus.textContent = 'Detecting location...';
                    navigator.geolocation.getCurrentPosition(
                        function (pos) {
                            const lat = pos.coords.latitude.toFixed(6);
                            const lng = pos.coords.longitude.toFixed(6);

                            if (latInput) latInput.value = lat;
                            if (lngInput) lngInput.value = lng;

                            map.setView([lat, lng], 16);
                            if (marker) {
                                marker.setLatLng([lat, lng]);
                            } else {
                                marker = L.marker([lat, lng]).addTo(map);
                            }
                            locationStatus.textContent = `Location applied: ${lat}, ${lng}`;
                        },
                        function () {
                            locationStatus.textContent = 'Geolocation unavailable. Please click on the map.';
                        }
                    );
                });
            }
        } catch (err) {
            console.warn('Map initialization failed:', err);
        }

        // --- PSGC Location Suggestions ---
        const addressInput = document.getElementById('propertyAddressInput');
        const suggestMenu = document.getElementById('addressSuggestMenu');
        const suggestStatus = document.getElementById('addressSuggestStatus');

        if (addressInput && suggestMenu && suggestStatus) {
            const psgcBase = 'https://psgc.gitlab.io/api';
            let datasetsPromise = null;
            let indexedItems = [];
            let timer = null;

            function loadPsgc() {
                if (indexedItems.length > 0) return Promise.resolve();
                if (datasetsPromise) return datasetsPromise;

                suggestStatus.textContent = 'Loading location suggestions...';
                datasetsPromise = Promise.all([
                    fetch(psgcBase + '/barangays.json').then((r) => r.json()),
                    fetch(psgcBase + '/cities-municipalities.json').then((r) => r.json()),
                    fetch(psgcBase + '/provinces.json').then((r) => r.json())
                ]).then(([barangays, cities, provinces]) => {
                    const cityMap = new Map();
                    cities.forEach((c) => cityMap.set(c.code, { name: c.name, prov: c.provinceCode }));
                    const provMap = new Map();
                    provinces.forEach((p) => provMap.set(p.code, p.name));

                    indexedItems = barangays.map((b) => {
                        const c = cityMap.get(b.cityCode || b.municipalityCode);
                        const pName = c ? provMap.get(c.prov) : '';
                        if (!c || !pName) return null;
                        const full = `${b.name}, ${c.name}, ${pName}`;
                        return { full, lower: full.toLowerCase() };
                    }).filter(Boolean);

                    suggestStatus.textContent = '';
                }).catch(() => {
                    suggestStatus.textContent = 'Unable to load PSGC suggestions. You can enter manually.';
                });

                return datasetsPromise;
            }

            addressInput.addEventListener('input', function () {
                clearTimeout(timer);
                const term = addressInput.value.trim().toLowerCase();
                if (term.length < 2) {
                    suggestMenu.classList.remove('is-open');
                    return;
                }

                timer = setTimeout(function () {
                    loadPsgc().then(() => {
                        const matches = indexedItems.filter((i) => i.lower.includes(term)).slice(0, 8);
                        suggestMenu.innerHTML = '';
                        if (!matches.length) {
                            suggestMenu.classList.remove('is-open');
                            return;
                        }
                        matches.forEach((m) => {
                            const btn = document.createElement('button');
                            btn.type = 'button';
                            btn.className = 'address-suggestion-item';
                            btn.textContent = m.full;
                            btn.addEventListener('click', function () {
                                addressInput.value = m.full;
                                addressInput.classList.remove('is-invalid');
                                suggestMenu.classList.remove('is-open');
                            });
                            suggestMenu.appendChild(btn);
                        });
                        suggestMenu.classList.add('is-open');
                    });
                }, 200);
            });

            document.addEventListener('click', function (e) {
                if (!suggestMenu.contains(e.target) && e.target !== addressInput) {
                    suggestMenu.classList.remove('is-open');
                }
            });
        }

        // Initialize active panel
        showPanel(currentPanel);
    })();
</script>
@endpush
