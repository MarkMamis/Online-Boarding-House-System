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
        $selectedAmenities = collect(old('building_inclusions', $draftProperty->building_inclusions ?? []))
            ->map(fn ($value) => (string) $value)
            ->all();

        $houseRuleCategories = (array) config('property_house_rules.categories', []);
        $draftHouseRules = $draftProperty->house_rules ?? [];

        $hasExistingDraftImage = !empty($draftProperty?->image_path);
        $existingDraftImageUrl = $hasExistingDraftImage ? file_url($draftProperty->image_path) : null;
        $existingDraftImageName = $hasExistingDraftImage ? basename($draftProperty->image_path) : null;
    @endphp

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

    <div class="guided-setup-wrapper">
        <div class="guided-setup-card">
            <!-- Setup Header with Auto-Save Badge -->
            <div class="setup-header d-flex justify-content-between align-items-center mb-3">
                <a href="{{ route('landlord.properties.index') }}" class="setup-back-link">
                    <i class="bi bi-arrow-left me-1"></i> Back to Properties
                </a>
                <div id="autoSaveIndicator" class="autosave-badge idle" aria-live="polite">
                    <i class="bi bi-cloud-check autosave-icon me-1"></i>
                    <span class="autosave-text">All changes saved</span>
                </div>
            </div>

            @if(isset($draftProperty) && $draftProperty)
                <div class="alert alert-info border-0 rounded-3 py-2 px-3 small d-flex align-items-center justify-content-between mb-3 bg-light text-secondary">
                    <span><i class="bi bi-info-circle text-primary me-1"></i> Resumed draft: <strong>{{ $draftProperty->name ?: 'Untitled Property' }}</strong></span>
                    <a href="{{ route('landlord.properties.create', ['new' => 1]) }}" class="text-decoration-none small text-danger fw-semibold ms-2">Start Fresh</a>
                </div>
            @endif

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
                <input type="hidden" name="property_id" id="draftPropertyId" value="{{ old('property_id', $draftProperty->id ?? '') }}">
                <input type="hidden" name="draft_version" id="draftVersion" value="{{ $draftProperty?->updated_at?->timestamp ?? 0 }}">

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
                            value="{{ old('name', $draftProperty->name ?? '') }}"
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
                                value="{{ old('address', $draftProperty->address ?? '') }}"
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
                            placeholder="Describe your property, walking distance to MinSU campus, nearby landmarks, or special features...">{{ old('description', $draftProperty->description ?? '') }}</textarea>
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
                            <div class="upload-idle {{ $hasExistingDraftImage ? 'd-none' : '' }}" id="uploadIdleState">
                                <div class="upload-icon-circle">
                                    <i class="bi bi-cloud-arrow-up"></i>
                                </div>
                                <div class="upload-prompt">
                                    <span class="upload-action-text">Click to choose photo</span> or drag here
                                </div>
                                <div class="upload-help-text">JPG, PNG, or WebP up to 5MB</div>
                            </div>
                            <div class="upload-preview-card {{ $hasExistingDraftImage ? '' : 'd-none' }}" id="uploadPreviewCard">
                                <img id="propertyImagePreview" src="{{ $existingDraftImageUrl ?: '' }}" alt="Property Preview" class="upload-preview-thumb">
                                <div class="upload-file-info">
                                    <div class="upload-file-name text-truncate" id="uploadFileName">{{ $existingDraftImageName ?: 'photo.jpg' }}</div>
                                    <div class="upload-file-size" id="uploadFileSize">{{ $hasExistingDraftImage ? 'Saved in draft' : '0 KB' }}</div>
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

                    <!-- Actions (No manual Save Draft) -->
                    <div class="question-actions">
                        <a href="{{ route('landlord.properties.index') }}" class="btn-prev">Cancel</a>
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

                    <!-- House Rules with Collapsible Suggestions -->
                    <div class="mb-4">
                        <label class="form-label mb-2">House Rules</label>
                        <div class="house-rules-accordion">
                            @foreach($houseRuleCategories as $categoryKey => $categoryConfig)
                                @php
                                    $categoryLabel = (string) ($categoryConfig['label'] ?? $categoryKey);
                                    $categorySavedRules = $draftHouseRules[$categoryKey] ?? null;
                                    $defaultRuleText = is_array($categorySavedRules)
                                        ? implode("\n", $categorySavedRules)
                                        : (string) ($categorySavedRules ?? '');
                                    $ruleText = old('house_rules.' . $categoryKey, $defaultRuleText);
                                    $ruleSuggestions = (array) ($categoryConfig['rules'] ?? []);
                                @endphp
                                <div class="rule-block mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-1 flex-wrap gap-1">
                                        <label class="small fw-bold text-secondary text-uppercase mb-0" for="rules_{{ $categoryKey }}">{{ $categoryLabel }}</label>
                                        @if(!empty($ruleSuggestions))
                                            <button type="button" class="btn-toggle-suggestions text-decoration-none border-0 bg-transparent text-success small fw-semibold" data-target="suggestions_{{ $categoryKey }}">
                                                <i class="bi bi-lightbulb me-1"></i> <span class="toggle-text">Add common rules</span>
                                            </button>
                                        @endif
                                    </div>
                                    <textarea
                                        class="form-control"
                                        id="rules_{{ $categoryKey }}"
                                        name="house_rules[{{ $categoryKey }}]"
                                        rows="2"
                                        placeholder="e.g. Observe curfew at 10 PM...">{{ $ruleText }}</textarea>
                                    @if(!empty($ruleSuggestions))
                                        <div id="suggestions_{{ $categoryKey }}" class="rule-suggestions mt-2 d-none flex-wrap gap-1 p-2 bg-light rounded-3">
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

                    <!-- Actions (No manual Save Draft) -->
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
                                value="{{ old('latitude', $draftProperty->latitude ?? '') }}"
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
                                value="{{ old('longitude', $draftProperty->longitude ?? '') }}"
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

                    <!-- Actions (No manual Save Draft) -->
                    <div class="question-actions">
                        <button type="button" class="btn-prev" data-target="1">
                            <i class="bi bi-arrow-left me-1"></i> Previous
                        </button>
                        <div class="actions-right">
                            <button type="button" class="btn-save-return-review d-none" data-save-panel="2">
                                <i class="bi bi-check-lg me-1"></i> Save &amp; Return to Review
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
                    <p class="question-subtitle">Check the details below before submitting for approval.</p>

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
                                    <span class="review-val" id="review_name">{{ $draftProperty?->name ?? '—' }}</span>
                                </div>
                                <div class="review-item">
                                    <span class="review-label">Address</span>
                                    <span class="review-val" id="review_address">{{ $draftProperty?->address ?? '—' }}</span>
                                </div>
                                <div class="review-item col-12">
                                    <span class="review-label">Description</span>
                                    <span class="review-val text-wrap" id="review_description">{{ $draftProperty?->description ?: 'Not provided' }}</span>
                                </div>
                                <div class="review-item col-12">
                                    <span class="review-label">Cover Photo</span>
                                    <div class="d-flex align-items-center gap-2 mt-1">
                                        <img id="review_photo_thumb" src="{{ $existingDraftImageUrl ?: '' }}" alt="Thumbnail" class="upload-preview {{ $hasExistingDraftImage ? '' : 'd-none' }}" style="width: 50px; height: 50px; object-fit: cover; border-radius: .5rem;">
                                        <span class="review-val" id="review_photo_name">{{ $existingDraftImageName ?: 'No photo uploaded' }}</span>
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
                                    <span class="review-val" id="review_latitude">{{ $draftProperty?->latitude ?? 'Not set' }}</span>
                                </div>
                                <div class="review-item">
                                    <span class="review-label">Longitude</span>
                                    <span class="review-val" id="review_longitude">{{ $draftProperty?->longitude ?? 'Not set' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-info border-0 rounded-3 bg-light d-flex align-items-center gap-2 mb-4 p-3 text-secondary">
                        <i class="bi bi-info-circle text-primary fs-5 flex-shrink-0"></i>
                        <div class="small">
                            <strong>Next Step:</strong> After submitting your property for review, you can add individual rooms, configure bed capacities, and set rental pricing.
                        </div>
                    </div>

                    <!-- Actions (Previous and Submit for Review only) -->
                    <div class="question-actions">
                        <button type="button" class="btn-prev" data-target="2">
                            <i class="bi bi-arrow-left me-1"></i> Previous
                        </button>
                        <div class="actions-right">
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
            border-radius: 1.15rem;
            padding: 1.35rem 1rem;
        }
    }

    .setup-back-link {
        color: var(--muted);
        text-decoration: none;
        font-size: 0.88rem;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        transition: color 0.15s ease;
    }

    .setup-back-link:hover {
        color: var(--ink);
    }

    /* Auto-save Status Badge */
    .autosave-badge {
        font-size: 0.8rem;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        padding: 0.25rem 0.65rem;
        border-radius: 999px;
        transition: all 0.2s ease;
    }

    .autosave-badge.idle {
        color: var(--muted);
        background: #f1f5f9;
    }

    .autosave-badge.saving {
        color: #0284c7;
        background: #e0f2fe;
    }

    .autosave-badge.saved {
        color: var(--premium-emerald-deep);
        background: #ecfdf5;
    }

    .autosave-badge.error {
        color: #dc2626;
        background: #fee2e2;
    }

    .spin {
        animation: spin 1s linear infinite;
        display: inline-block;
    }

    @keyframes spin {
        100% { transform: rotate(360deg); }
    }

    .question-panel {
        display: none;
    }

    .question-panel.active {
        display: block;
        animation: fadeIn 0.2s ease-in-out;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(6px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .question-kicker {
        font-size: 0.72rem;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        font-weight: 700;
        color: var(--premium-emerald-deep);
        margin-bottom: 0.35rem;
    }

    .question-title {
        font-size: 1.45rem;
        font-weight: 700;
        color: var(--ink);
        letter-spacing: -0.02em;
        margin-bottom: 0.3rem;
    }

    .question-subtitle {
        color: var(--muted);
        font-size: 0.88rem;
        margin-bottom: 1.5rem;
    }

    .field-hint {
        font-size: 0.76rem;
        color: var(--muted);
        margin-top: 0.35rem;
    }

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
        background: #f0fdf4;
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
        background: #e2e8f0;
        color: #475569;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.35rem;
        margin-bottom: 0.5rem;
    }

    .upload-action-text {
        color: var(--premium-emerald-deep);
        font-weight: 600;
    }

    .upload-help-text {
        font-size: 0.74rem;
        color: #94a3b8;
        margin-top: 0.2rem;
    }

    .upload-preview-card {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        background: #ffffff;
        border: 1px solid var(--line);
        border-radius: 0.75rem;
        padding: 0.5rem 0.75rem;
        position: relative;
        z-index: 3;
    }

    .upload-preview-thumb {
        width: 52px;
        height: 52px;
        object-fit: cover;
        border-radius: 0.5rem;
        border: 1px solid var(--line);
    }

    .upload-file-info {
        flex: 1;
        min-width: 0;
        text-align: left;
    }

    .upload-file-name {
        font-size: 0.85rem;
        font-weight: 600;
        color: var(--ink);
    }

    .upload-file-size {
        font-size: 0.72rem;
        color: var(--muted);
    }

    .btn-remove-photo {
        background: none;
        border: none;
        color: #ef4444;
        font-size: 1rem;
        padding: 0.35rem;
        border-radius: 0.4rem;
        cursor: pointer;
        transition: background 0.15s ease;
    }

    .btn-remove-photo:hover {
        background: #fee2e2;
    }

    /* Amenities & Chips */
    .amenity-category-block {
        border-bottom: 1px solid #f1f5f9;
        padding-bottom: 0.75rem;
    }

    .amenity-category-label {
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        color: #64748b;
        letter-spacing: 0.05em;
        margin-bottom: 0.5rem;
    }

    .amenities-chips-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 0.45rem;
    }

    .amenity-chip-label {
        margin: 0;
        cursor: pointer;
    }

    .amenity-chip-input {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }

    .amenity-chip-box {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.4rem 0.75rem;
        border-radius: 999px;
        font-size: 0.8rem;
        font-weight: 500;
        background: #f8fafc;
        border: 1px solid #cbd5e1;
        color: #334155;
        transition: all 0.15s ease;
    }

    .amenity-chip-box .check-icon {
        display: none;
        font-size: 0.85rem;
    }

    .amenity-chip-input:checked + .amenity-chip-box {
        background: #ecfdf5;
        border-color: var(--premium-emerald);
        color: var(--premium-emerald-deep);
        font-weight: 600;
    }

    .amenity-chip-input:checked + .amenity-chip-box .check-icon {
        display: inline-block;
    }

    /* Suggestion Pills */
    .btn-suggestion-pill {
        border: 1px solid #cbd5e1;
        background: #ffffff;
        color: #475569;
        border-radius: 999px;
        font-size: 0.74rem;
        padding: 0.2rem 0.55rem;
        cursor: pointer;
        transition: all 0.15s ease;
        text-align: left;
    }

    .btn-suggestion-pill:hover {
        background: #ecfdf5;
        border-color: var(--premium-emerald);
        color: var(--premium-emerald-deep);
    }

    /* Question Actions */
    .question-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 2rem;
        padding-top: 1.25rem;
        border-top: 1px solid var(--line);
        flex-wrap: wrap;
        gap: 0.75rem;
    }

    .actions-right {
        display: flex;
        align-items: center;
        gap: 0.5rem;
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
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.85rem;
        padding-bottom: 0.5rem;
        border-bottom: 1px solid #f1f5f9;
    }

    .review-section-title {
        font-size: 0.95rem;
        font-weight: 700;
        color: var(--ink);
        margin: 0;
    }

    .btn-review-edit {
        background: none;
        border: none;
        color: var(--premium-emerald-deep);
        font-size: 0.82rem;
        font-weight: 600;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        padding: 0.2rem 0.5rem;
        border-radius: 0.4rem;
        transition: background 0.15s ease;
    }

    .btn-review-edit:hover {
        background: #ecfdf5;
    }

    .review-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 0.75rem 1rem;
    }

    @media (max-width: 576px) {
        .review-grid {
            grid-template-columns: 1fr;
        }
    }

    .review-item {
        display: flex;
        flex-direction: column;
    }

    .review-label {
        font-size: 0.72rem;
        text-transform: uppercase;
        color: var(--muted);
        font-weight: 600;
        letter-spacing: 0.04em;
        margin-bottom: 0.15rem;
    }

    .review-val {
        font-size: 0.88rem;
        color: var(--ink);
        font-weight: 500;
    }

    /* PSGC Autocomplete Menu */
    .address-suggestion-menu {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: #ffffff;
        border: 1px solid #cbd5e1;
        border-radius: 0.75rem;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
        max-height: 220px;
        overflow-y: auto;
        z-index: 1050;
        display: none;
    }

    .address-suggestion-menu.is-open {
        display: block;
    }

    .address-suggestion-item {
        width: 100%;
        padding: 0.6rem 0.85rem;
        text-align: left;
        border: none;
        background: none;
        font-size: 0.84rem;
        color: var(--ink);
        cursor: pointer;
        border-bottom: 1px solid #f1f5f9;
        display: block;
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
        const autoSaveIndicator = document.getElementById('autoSaveIndicator');
        const draftPropertyIdInput = document.getElementById('draftPropertyId');
        const draftVersionInput = document.getElementById('draftVersion');
        const autoSaveUrl = "{{ route('landlord.properties.autosave') }}";
        const csrfToken = document.querySelector('input[name="_token"]')?.value || '';

        let currentPanel = {{ $initialPanel }};
        let editingFromReview = false;
        let autoSaveTimer = null;
        let isSaving = false;
        let hasPendingSave = false;

        // --- Save-State Indicator Updates ---
        function setSaveStatus(state, message) {
            if (!autoSaveIndicator) return;
            autoSaveIndicator.className = 'autosave-badge ' + state;
            const textEl = autoSaveIndicator.querySelector('.autosave-text');
            const iconEl = autoSaveIndicator.querySelector('.autosave-icon');

            if (state === 'saving') {
                if (iconEl) iconEl.className = 'bi bi-arrow-repeat spin autosave-icon me-1';
                if (textEl) textEl.textContent = message || 'Saving...';
            } else if (state === 'saved') {
                if (iconEl) iconEl.className = 'bi bi-cloud-check autosave-icon me-1';
                if (textEl) textEl.textContent = message || 'All changes saved';
            } else if (state === 'error') {
                if (iconEl) iconEl.className = 'bi bi-exclamation-circle text-danger autosave-icon me-1';
                if (textEl) {
                    textEl.innerHTML = 'Couldn\'t save. <button type="button" class="btn-retry-save text-primary border-0 bg-transparent p-0 small fw-bold">Retry</button>';
                    const retryBtn = autoSaveIndicator.querySelector('.btn-retry-save');
                    if (retryBtn) {
                        retryBtn.addEventListener('click', function (e) {
                            e.stopPropagation();
                            triggerAutoSaveNow();
                        });
                    }
                }
            } else {
                if (iconEl) iconEl.className = 'bi bi-cloud-check autosave-icon me-1';
                if (textEl) textEl.textContent = message || 'All changes saved';
            }
        }

        // --- Auto-Save Dispatcher ---
        function triggerAutoSaveNow() {
            clearTimeout(autoSaveTimer);
            if (isSaving) {
                hasPendingSave = true;
                return Promise.resolve();
            }

            const nameVal = document.getElementById('propertyName')?.value.trim() || '';
            const addressVal = document.getElementById('propertyAddressInput')?.value.trim() || '';
            const currentPropId = draftPropertyIdInput?.value;

            // Don't auto-create initial draft if there is no name or address yet
            if (!currentPropId && nameVal.length < 2 && addressVal.length < 2) {
                return Promise.resolve();
            }

            isSaving = true;
            setSaveStatus('saving', 'Saving...');

            const formData = new FormData(form);
            // Append explicit draft version
            if (draftVersionInput?.value) {
                formData.set('draft_version', draftVersionInput.value);
            }

            return fetch(autoSaveUrl, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(function (res) {
                if (!res.ok) {
                    throw new Error('Save failed with status ' + res.status);
                }
                return res.json();
            })
            .then(function (data) {
                isSaving = false;
                if (data.success) {
                    if (data.property_id && draftPropertyIdInput) {
                        draftPropertyIdInput.value = data.property_id;
                    }
                    if (data.draft_version && draftVersionInput) {
                        draftVersionInput.value = data.draft_version;
                    }
                    if (data.image_url) {
                        const reviewThumb = document.getElementById('review_photo_thumb');
                        const reviewName = document.getElementById('review_photo_name');
                        if (reviewThumb) {
                            reviewThumb.src = data.image_url;
                            reviewThumb.classList.remove('d-none');
                        }
                        if (reviewName) {
                            reviewName.textContent = data.image_name || 'Cover photo';
                        }
                    }
                    setSaveStatus('saved', 'All changes saved');
                } else {
                    setSaveStatus('error', data.message || 'Couldn\'t save. Retry');
                }

                if (hasPendingSave) {
                    hasPendingSave = false;
                    triggerAutoSaveNow();
                }
            })
            .catch(function (err) {
                isSaving = false;
                console.warn('Auto-save encountered error:', err);
                setSaveStatus('error', 'Couldn\'t save. Retry');
            });
        }

        function scheduleAutoSave(delayMs = 1200) {
            clearTimeout(autoSaveTimer);
            autoSaveTimer = setTimeout(triggerAutoSaveNow, delayMs);
        }

        // Attach auto-save triggers to text inputs (debounced 1000ms)
        const autoSaveInputs = form.querySelectorAll('input[type="text"], input[type="number"], textarea');
        autoSaveInputs.forEach((el) => {
            el.addEventListener('input', function () {
                scheduleAutoSave(1000);
            });
        });

        // Attach immediate auto-save to checkboxes
        const autoSaveCheckboxes = form.querySelectorAll('input[type="checkbox"]');
        autoSaveCheckboxes.forEach((cb) => {
            cb.addEventListener('change', function () {
                scheduleAutoSave(300);
            });
        });

        // --- Panel Navigation ---
        function showPanel(index) {
            if (index < 0 || index >= panels.length) return;
            panels.forEach((p, idx) => {
                p.classList.toggle('active', idx === index);
            });
            currentPanel = index;

            saveReturnButtons.forEach((btn) => {
                btn.classList.toggle('d-none', !editingFromReview);
            });

            if (index === 3) {
                syncReviewData();
                editingFromReview = false;
                saveReturnButtons.forEach((btn) => btn.classList.add('d-none'));
            }

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
                    triggerAutoSaveNow();
                    showPanel(target);
                }
            });
        });

        // --- Event Listeners: Previous Buttons ---
        document.querySelectorAll('.btn-prev').forEach((btn) => {
            btn.addEventListener('click', function () {
                const target = btn.getAttribute('data-target');
                if (target !== null) {
                    triggerAutoSaveNow();
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
                    triggerAutoSaveNow();
                    showPanel(3);
                }
            });
        });

        // --- Toggle House Rules Suggestions ---
        document.querySelectorAll('.btn-toggle-suggestions').forEach((btn) => {
            btn.addEventListener('click', function () {
                const targetId = btn.getAttribute('data-target');
                const container = document.getElementById(targetId);
                const textSpan = btn.querySelector('.toggle-text');
                if (container) {
                    const isHidden = container.classList.contains('d-none');
                    container.classList.toggle('d-none', !isHidden);
                    container.classList.toggle('d-flex', isHidden);
                    if (textSpan) {
                        textSpan.textContent = isHidden ? 'Hide common rules' : 'Add common rules';
                    }
                }
            });
        });

        // --- House Rule Suggestion Pills ---
        document.querySelectorAll('.btn-suggestion-pill').forEach((btn) => {
            btn.addEventListener('click', function () {
                const targetId = btn.getAttribute('data-target');
                const text = btn.getAttribute('data-text');
                const textarea = document.getElementById(targetId);
                if (textarea) {
                    const currentVal = textarea.value.trim();
                    // Avoid duplicate rule insertion
                    if (currentVal.indexOf(text) === -1) {
                        textarea.value = currentVal ? currentVal + '\n' + text : text;
                    }
                    textarea.focus();
                    scheduleAutoSave(400);
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
            } else if (!reviewPhotoThumb.getAttribute('src')) {
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

                // Auto-upload image to draft immediately
                triggerAutoSaveNow();
            });
        }

        if (btnRemovePhoto) {
            btnRemovePhoto.addEventListener('click', function (e) {
                e.stopPropagation();
                imageInput.value = '';
                imagePreview.src = '';
                uploadPreviewCard.classList.add('d-none');
                uploadIdleState.classList.remove('d-none');
                triggerAutoSaveNow();
            });
        }

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
                scheduleAutoSave(400);
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
                            scheduleAutoSave(400);
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
                                scheduleAutoSave(300);
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

        // Beforeunload safety warning if save is pending
        window.addEventListener('beforeunload', function (e) {
            if (isSaving || hasPendingSave) {
                e.preventDefault();
                e.returnValue = 'You have unsaved changes. Are you sure you want to leave?';
            }
        });

        // Initialize active panel
        showPanel(currentPanel);
    })();
</script>
@endpush
