@extends('layouts.landlord')

@section('content')
    @php
        $errorKeys = $errors->keys();
        $initialStep = 1;

        if (collect($errorKeys)->contains(fn ($key) => in_array((string) $key, ['latitude', 'longitude'], true))) {
            $initialStep = 3;
        } elseif (collect($errorKeys)->contains(fn ($key) => str_starts_with((string) $key, 'house_rules') || str_starts_with((string) $key, 'building_inclusions') || str_starts_with((string) $key, 'building_inclusion_custom'))) {
            $initialStep = 2;
        }

        $amenityCategories = (array) config('property_amenities.categories', []);
        $allowedAmenityKeys = collect((array) config('property_amenities.flat', []))
            ->keys()
            ->map(fn ($value) => (string) $value)
            ->all();

        $selectedAmenities = collect(old('building_inclusions', $property->building_inclusions ?? []))
            ->map(fn ($value) => (string) $value)
            ->all();

        $existingCustomAmenities = collect((array) ($property->building_inclusions ?? []))
            ->map(fn ($value) => trim((string) $value))
            ->filter(fn ($value) => $value !== '' && !in_array($value, $allowedAmenityKeys, true))
            ->unique(fn ($value) => strtolower((string) $value))
            ->values()
            ->all();

        $oldCustomAmenitiesByCategory = collect((array) old('building_inclusion_custom', []))
            ->mapWithKeys(function ($values, $category) {
                $cleanValues = collect((array) $values)
                    ->map(fn ($value) => trim((string) $value))
                    ->filter()
                    ->unique(fn ($value) => strtolower((string) $value))
                    ->values()
                    ->all();

                return [(string) $category => $cleanValues];
            })
            ->all();

        if (empty($oldCustomAmenitiesByCategory) && !empty($existingCustomAmenities)) {
            $defaultCategory = array_key_first($amenityCategories);
            if ($defaultCategory !== null) {
                $oldCustomAmenitiesByCategory[(string) $defaultCategory] = $existingCustomAmenities;
            }
        }

        $houseRuleCategories = (array) config('property_house_rules.categories', []);
        $currentHouseRules = (array) ($property->house_rules ?? []);
    @endphp

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

    <div class="property-edit-shell" data-initial-step="{{ $initialStep }}">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
            <div>
                <div class="text-uppercase small text-muted fw-semibold">Property Settings</div>
                <h1 class="h3 mb-1">Edit Property</h1>
                <div class="text-muted small">Update property details, inclusions, and location using guided steps.</div>
            </div>
            <a href="{{ route('landlord.properties.show', $property->id) }}" class="btn btn-outline-secondary rounded-pill px-3">Back to Overview</a>
        </div>

        <div class="edit-summary mb-4">
            <div class="edit-summary-item">
                <div class="edit-summary-label">Property</div>
                <div class="edit-summary-value">{{ $property->name }}</div>
            </div>
            <div class="edit-summary-item">
                <div class="edit-summary-label">Address</div>
                <div class="edit-summary-value text-truncate">{{ $property->address }}</div>
            </div>
            <div class="edit-summary-item">
                <div class="edit-summary-label">Price Range</div>
                <div class="edit-summary-value">
                    @if($property->price_min !== null || $property->price_max !== null)
                        ₱{{ number_format($property->price_min, 2) }} - ₱{{ number_format($property->price_max, 2) }}
                    @else
                        Not available yet
                    @endif
                </div>
            </div>
        </div>

        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif

        <div class="card shadow-sm border-0 rounded-4 overflow-hidden property-form-card">
            <form id="updatePropertyForm" method="POST" enctype="multipart/form-data" action="{{ route('landlord.properties.update', $property->id) }}">
                @csrf
                @method('PUT')
                <input type="hidden" name="building_inclusion_custom_present" value="1">

                <div class="stepper-head border-bottom">
                    <div class="property-stepper" id="propertyEditStepper">
                        <button type="button" class="stepper-node" data-step-target="1">
                            <span class="step-count">1</span>
                            <span class="step-label">Basics</span>
                        </button>
                        <button type="button" class="stepper-node" data-step-target="2">
                            <span class="step-count">2</span>
                            <span class="step-label">Inclusions</span>
                        </button>
                        <button type="button" class="stepper-node" data-step-target="3">
                            <span class="step-count">3</span>
                            <span class="step-label">Location</span>
                        </button>
                    </div>
                </div>

                <div class="card-body p-0">
                    <div class="step-pane" data-step="1">
                        <div class="pane-wrap">
                            <div class="section-kicker">Step 1</div>
                            <h5 class="fw-semibold mb-1">Basic Details</h5>
                            <p class="text-muted small mb-3">Update the key property profile information first.</p>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Name</label>
                                    <input type="text" name="name" value="{{ old('name', $property->name) }}" class="form-control" data-required="1" data-label="Property name">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Address</label>
                                    <input type="text" name="address" value="{{ old('address', $property->address) }}" class="form-control" data-required="1" data-label="Property address">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Property Photo (Optional)</label>
                                    <input type="file" id="propertyImageInput" name="image" class="form-control" accept="image/*">
                                    <div class="form-text">JPG/PNG/WebP up to 5MB.</div>

                                    @if(!empty($property->image_path))
                                        <div class="mt-3">
                                            <div class="text-muted small mb-1">Current photo</div>
                                            <img src="{{ asset('storage/' . $property->image_path) }}" alt="Property photo" class="img-fluid rounded-3 border" style="max-height: 260px; object-fit: cover;">
                                        </div>
                                    @endif

                                    <div id="propertyImagePreviewWrap" class="mt-3" style="display:none;">
                                        <div class="text-muted small mb-1">New photo preview</div>
                                        <img id="propertyImagePreview" alt="Property preview" class="img-fluid rounded-3 border" style="max-height: 260px; object-fit: cover;">
                                    </div>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Description</label>
                                    <textarea name="description" rows="4" class="form-control">{{ old('description', $property->description) }}</textarea>
                                </div>
                            </div>

                            <div class="alert alert-light border mb-0 mt-3">
                                <strong>Price Range</strong>
                                <div class="small text-muted">Auto-generated from room prices. Current range:
                                    @if($property->price_min !== null || $property->price_max !== null)
                                        <span class="fw-semibold">₱{{ number_format($property->price_min, 2) }} - ₱{{ number_format($property->price_max, 2) }}</span>
                                    @else
                                        <span class="fw-semibold">Not available yet</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="step-pane" data-step="2">
                        <div class="pane-wrap">
                            <div class="section-kicker">Step 2</div>
                            <h5 class="fw-semibold mb-1">Inclusions and House Rules</h5>
                            <p class="text-muted small mb-3">Manage amenities and policies shown to tenants.</p>

                            <div class="mb-4">
                                <label class="form-label">Building/Boarding House Inclusions</label>
                                <div class="border rounded-3 p-3 bg-light-subtle">
                                    <div class="row g-3">
                                        @foreach($amenityCategories as $category => $items)
                                            @php
                                                $oldCustomAmenities = (array) ($oldCustomAmenitiesByCategory[$category] ?? []);
                                            @endphp
                                            <div class="col-12 col-md-4">
                                                <div class="small text-uppercase text-muted fw-semibold mb-2">{{ $category }}</div>
                                                @foreach($items as $amenityKey => $amenityLabel)
                                                    <div class="form-check mb-1">
                                                        <input
                                                            class="form-check-input"
                                                            type="checkbox"
                                                            id="amenity_{{ $amenityKey }}"
                                                            name="building_inclusions[]"
                                                            value="{{ $amenityKey }}"
                                                            @checked(in_array($amenityKey, $selectedAmenities, true))
                                                        >
                                                        <label class="form-check-label" for="amenity_{{ $amenityKey }}">{{ $amenityLabel }}</label>
                                                    </div>
                                                @endforeach

                                                <div class="custom-inclusion-tools mt-3" data-custom-category="{{ $category }}">
                                                    <label class="small text-muted fw-semibold mb-1 d-block">Add item available in this category</label>
                                                    <div class="input-group input-group-sm">
                                                        <input
                                                            type="text"
                                                            class="form-control custom-inclusion-input"
                                                            data-custom-input
                                                            placeholder="Type item and press Enter"
                                                            maxlength="100"
                                                        >
                                                        <button type="button" class="btn btn-outline-secondary" data-custom-add-btn>Add</button>
                                                    </div>
                                                    <div class="custom-inclusion-list mt-2" data-custom-list>
                                                        @foreach($oldCustomAmenities as $customAmenity)
                                                            <span class="custom-inclusion-chip">
                                                                <span class="custom-inclusion-label">{{ $customAmenity }}</span>
                                                                <button type="button" class="custom-chip-remove" aria-label="Remove custom inclusion">&times;</button>
                                                                <input type="hidden" name="building_inclusion_custom[{{ $category }}][]" value="{{ $customAmenity }}">
                                                            </span>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                @if(!empty($existingCustomAmenities))
                                    <div class="form-text mt-2">Previously saved custom inclusions are shown under the first category. You can move or remove them.</div>
                                @endif
                                @error('building_inclusions')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                                @error('building_inclusions.*')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                                @if($errors->has('building_inclusion_custom.*.*'))
                                    <div class="text-danger small mt-1">{{ $errors->first('building_inclusion_custom.*.*') }}</div>
                                @endif
                            </div>

                            <div>
                                <label class="form-label">Property House Rules</label>
                                <div class="border rounded-3 p-3 bg-light-subtle">
                                    <div class="row g-3">
                                        @foreach($houseRuleCategories as $categoryKey => $categoryConfig)
                                            @php
                                                $categoryLabel = (string) ($categoryConfig['label'] ?? $categoryKey);
                                                $currentCategoryRules = (array) ($currentHouseRules[$categoryKey] ?? []);
                                                $ruleText = old('house_rules.' . $categoryKey, implode(PHP_EOL, $currentCategoryRules));
                                                $ruleSuggestions = (array) ($categoryConfig['rules'] ?? []);
                                            @endphp
                                            <div class="col-12 col-md-4">
                                                <label class="small text-uppercase text-muted fw-semibold mb-2" for="house_rules_{{ $categoryKey }}">{{ $categoryLabel }}</label>
                                                <textarea
                                                    class="form-control"
                                                    id="house_rules_{{ $categoryKey }}"
                                                    name="house_rules[{{ $categoryKey }}]"
                                                    rows="6"
                                                    placeholder="Write one rule per line..."
                                                >{{ $ruleText }}</textarea>
                                                @if(!empty($ruleSuggestions))
                                                    <div class="rule-helper mt-2">
                                                        <div class="rule-helper-title">Sample ideas (optional):</div>
                                                        <ul class="rule-helper-list mb-0">
                                                            @foreach($ruleSuggestions as $suggestion)
                                                                <li>{{ $suggestion }}</li>
                                                            @endforeach
                                                        </ul>
                                                    </div>
                                                @endif
                                                @error('house_rules.' . $categoryKey)
                                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="form-text">Use one rule per line. These rules will appear in the booking flow.</div>
                            </div>
                        </div>
                    </div>

                    <div class="step-pane" data-step="3">
                        <div class="pane-wrap location-panel">
                            <div class="section-kicker">Step 3</div>
                            <h5 class="fw-semibold mb-1">Location and Map</h5>
                            <p class="text-muted small mb-3">Set an accurate pin by clicking the map or typing exact coordinates.</p>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Latitude</label>
                                    <input type="number" step="0.000001" name="latitude" id="latitude" value="{{ old('latitude', $property->latitude) }}" class="form-control" placeholder="e.g. 14.599512">
                                    <div class="form-text">Map click will auto-fill this field.</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Longitude</label>
                                    <input type="number" step="0.000001" name="longitude" id="longitude" value="{{ old('longitude', $property->longitude) }}" class="form-control" placeholder="e.g. 120.984222">
                                    <div class="form-text">Map click will auto-fill this field.</div>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Property Location Map</label>
                                    <div id="property-map" class="map-box" style="height: 360px; width: 100%;"></div>
                                    <div class="form-text">Click anywhere on the map to update your property location.</div>
                                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mt-2">
                                        <button type="button" id="useCurrentLocationBtn" class="btn btn-sm btn-outline-primary rounded-pill">
                                            <i class="fas fa-location-crosshairs me-1"></i>Use My Location
                                        </button>
                                        <span id="locationStatus" class="small text-muted"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

            <div class="card-footer bg-light d-flex justify-content-between align-items-center flex-wrap gap-2 action-bar">
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-3" id="prevStepBtn">Previous</button>
                    <form id="deletePropertyForm" method="POST" action="{{ route('landlord.properties.destroy', $property->id) }}" onsubmit="return confirm('Delete this property? All rooms and bookings under it will be removed.');">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-outline-danger rounded-pill px-3">Delete</button>
                    </form>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('landlord.properties.show', $property->id) }}" class="btn btn-outline-secondary rounded-pill px-3">Cancel</a>
                    <button type="button" class="btn btn-brand rounded-pill px-4" id="nextStepBtn">Next</button>
                    <button type="submit" form="updatePropertyForm" class="btn btn-brand rounded-pill px-4" id="saveChangesBtn">Save Changes</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
    .property-edit-shell {
        background: linear-gradient(180deg, #ffffff 0%, #fbfdfc 100%);
        border: 1px solid rgba(2,8,20,.08);
        border-radius: 1.25rem;
        box-shadow: 0 10px 26px rgba(2,8,20,.06);
        padding: 1.25rem;
    }

    .edit-summary {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: .75rem;
    }

    .edit-summary-item {
        border: 1px solid rgba(20,83,45,.16);
        background: linear-gradient(180deg, rgba(167,243,208,.18), rgba(255,255,255,.85));
        border-radius: .9rem;
        padding: .7rem .8rem;
        min-width: 0;
    }

    .edit-summary-label {
        font-size: .72rem;
        text-transform: uppercase;
        letter-spacing: .05em;
        color: rgba(2,8,20,.55);
        font-weight: 700;
        margin-bottom: .2rem;
    }

    .edit-summary-value {
        font-size: .94rem;
        font-weight: 700;
        color: #14532d;
    }

    .property-form-card {
        border: 1px solid rgba(2,8,20,.08) !important;
        box-shadow: 0 14px 30px rgba(2,8,20,.08) !important;
    }

    .stepper-head {
        background: linear-gradient(180deg, #f8fafc 0%, #ffffff 100%);
        padding: 1rem 1.25rem;
    }

    .property-stepper {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: .65rem;
    }

    .stepper-node {
        border: 1px solid rgba(2,8,20,.12);
        background: #ffffff;
        border-radius: .85rem;
        padding: .55rem .6rem;
        display: flex;
        align-items: center;
        gap: .55rem;
        text-align: left;
        transition: all .18s ease;
        cursor: pointer;
    }

    .stepper-node .step-count {
        width: 24px;
        height: 24px;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: .78rem;
        font-weight: 700;
        border: 1px solid rgba(2,8,20,.18);
        color: #475569;
        background: #f8fafc;
        flex: 0 0 auto;
    }

    .stepper-node .step-label {
        font-size: .84rem;
        font-weight: 600;
        color: #334155;
    }

    .stepper-node.is-active {
        border-color: rgba(20,83,45,.45);
        background: rgba(167,243,208,.18);
    }

    .stepper-node.is-active .step-count {
        border-color: rgba(20,83,45,.65);
        color: #14532d;
        background: #ffffff;
    }

    .stepper-node.is-done {
        border-color: rgba(20,83,45,.3);
        background: rgba(167,243,208,.12);
    }

    .step-pane {
        display: none;
        padding: 1.2rem 1.25rem;
    }

    .step-pane.is-visible {
        display: block;
    }

    .pane-wrap {
        border: 1px solid rgba(2,8,20,.09);
        border-radius: 1rem;
        background: linear-gradient(180deg, #ffffff 0%, #fcfffd 100%);
        padding: 1rem;
        box-shadow: 0 8px 20px rgba(2,8,20,.05);
    }

    .location-panel {
        background: linear-gradient(180deg, #f8fbff 0%, #ffffff 100%);
    }

    .section-kicker {
        font-size: .78rem;
        letter-spacing: .05em;
        text-transform: uppercase;
        color: rgba(2,8,20,.6);
        font-weight: 700;
    }

    .property-form-card .form-label {
        font-weight: 600;
        color: #0f172a;
    }

    .property-form-card .form-control,
    .property-form-card .form-select,
    .property-form-card textarea {
        border-color: rgba(2,8,20,.14);
        background: #ffffff;
    }

    .property-form-card textarea::placeholder {
        color: #94a3b8;
        font-style: italic;
    }

    .custom-inclusion-tools .input-group .form-control {
        border-right: 0;
    }

    .custom-inclusion-list {
        display: flex;
        flex-wrap: wrap;
        gap: .35rem;
        min-height: 1.4rem;
    }

    .custom-inclusion-chip {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        padding: .2rem .45rem;
        border: 1px solid rgba(20,83,45,.2);
        border-radius: 999px;
        background: rgba(167,243,208,.18);
        color: #14532d;
        font-size: .76rem;
        font-weight: 600;
    }

    .custom-chip-remove {
        border: 0;
        background: transparent;
        color: #14532d;
        line-height: 1;
        padding: 0;
        font-size: .92rem;
        width: 14px;
        cursor: pointer;
    }

    .rule-helper {
        border: 1px dashed rgba(2,8,20,.18);
        border-radius: .6rem;
        padding: .55rem .65rem;
        background: #f8fafc;
    }

    .rule-helper-title {
        font-size: .72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .04em;
        color: #475569;
        margin-bottom: .25rem;
    }

    .rule-helper-list {
        padding-left: 1rem;
        margin: 0;
    }

    .rule-helper-list li {
        font-size: .78rem;
        color: #64748b;
        line-height: 1.35;
        margin-bottom: .2rem;
    }

    .rule-helper-list li:last-child {
        margin-bottom: 0;
    }

    .map-box {
        border: 1px solid #dee2e6;
        border-radius: .8rem;
        overflow: hidden;
        box-shadow: inset 0 0 0 1px rgba(255,255,255,.6), 0 8px 20px rgba(2,8,20,.06);
    }

    .action-bar {
        position: sticky;
        bottom: 0;
        z-index: 5;
        border-top: 1px solid rgba(2,8,20,.08);
    }

    @media (max-width: 991.98px) {
        .edit-summary {
            grid-template-columns: 1fr;
        }

        .property-stepper {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const shell = document.querySelector('.property-edit-shell');
    const nodes = Array.from(document.querySelectorAll('.stepper-node'));
    const panes = Array.from(document.querySelectorAll('.step-pane'));
    const prevBtn = document.getElementById('prevStepBtn');
    const nextBtn = document.getElementById('nextStepBtn');
    const saveBtn = document.getElementById('saveChangesBtn');
    const totalSteps = panes.length;
    let currentStep = Number(shell?.dataset.initialStep || 1);

    function setStep(step) {
        currentStep = Math.max(1, Math.min(totalSteps, step));

        panes.forEach((pane) => {
            pane.classList.toggle('is-visible', Number(pane.dataset.step) === currentStep);
        });

        nodes.forEach((node) => {
            const targetStep = Number(node.dataset.stepTarget);
            node.classList.toggle('is-active', targetStep === currentStep);
            node.classList.toggle('is-done', targetStep < currentStep);
        });

        prevBtn.style.visibility = currentStep === 1 ? 'hidden' : 'visible';
        nextBtn.style.display = currentStep === totalSteps ? 'none' : 'inline-flex';
        saveBtn.style.display = currentStep === totalSteps ? 'inline-flex' : 'none';

        if (currentStep === 3 && typeof map !== 'undefined') {
            setTimeout(() => map.invalidateSize(), 80);
        }
    }

    function validateStep(step) {
        const activePane = panes.find((pane) => Number(pane.dataset.step) === step);
        if (!activePane) {
            return true;
        }

        const requiredFields = activePane.querySelectorAll('[data-required="1"]');
        for (const field of requiredFields) {
            const label = field.dataset.label || 'This field';
            const value = (field.value || '').trim();

            field.setCustomValidity('');
            if (value === '') {
                field.setCustomValidity(label + ' is required.');
                field.reportValidity();
                return false;
            }
        }

        return true;
    }

    document.querySelectorAll('[data-required="1"]').forEach((field) => {
        field.addEventListener('input', function () {
            field.setCustomValidity('');
        });
    });

    prevBtn.addEventListener('click', function () {
        setStep(currentStep - 1);
    });

    nextBtn.addEventListener('click', function () {
        if (validateStep(currentStep)) {
            setStep(currentStep + 1);
        }
    });

    nodes.forEach((node) => {
        node.addEventListener('click', function () {
            const targetStep = Number(node.dataset.stepTarget);
            if (targetStep < currentStep) {
                setStep(targetStep);
                return;
            }

            if (validateStep(currentStep)) {
                setStep(targetStep);
            }
        });
    });

    const latitudeInput = document.getElementById('latitude');
    const longitudeInput = document.getElementById('longitude');
    const useCurrentLocationBtn = document.getElementById('useCurrentLocationBtn');
    const locationStatus = document.getElementById('locationStatus');
    const fallbackLat = 14.5995;
    const fallbackLng = 120.9842;

    const parsedLat = parseFloat(latitudeInput.value);
    const parsedLng = parseFloat(longitudeInput.value);
    const hasCoords = !isNaN(parsedLat) && !isNaN(parsedLng);

    const map = L.map('property-map').setView(
        [hasCoords ? parsedLat : fallbackLat, hasCoords ? parsedLng : fallbackLng],
        hasCoords ? 15 : 10
    );

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxNativeZoom: 19,
        maxZoom: 22,
        attribution: 'OpenStreetMap contributors'
    }).addTo(map);

    let marker = null;

    function setLocationStatus(message, isError) {
        if (!locationStatus) return;
        locationStatus.textContent = message;
        locationStatus.classList.toggle('text-danger', !!isError);
        locationStatus.classList.toggle('text-muted', !isError);
    }

    function setLocationButtonLoading(isLoading) {
        if (!useCurrentLocationBtn) return;
        useCurrentLocationBtn.disabled = isLoading;
        useCurrentLocationBtn.innerHTML = isLoading
            ? '<i class="fas fa-spinner fa-spin me-1"></i>Locating...'
            : '<i class="fas fa-location-crosshairs me-1"></i>Use My Location';
    }

    function updateMarker(lat, lng) {
        if (marker) {
            map.removeLayer(marker);
        }
        marker = L.marker([lat, lng]).addTo(map);
        latitudeInput.value = Number(lat).toFixed(6);
        longitudeInput.value = Number(lng).toFixed(6);
    }

    function useCurrentLocation() {
        if (!navigator.geolocation) {
            setLocationStatus('Geolocation is not supported on this browser.', true);
            return;
        }

        setLocationButtonLoading(true);

        navigator.geolocation.getCurrentPosition(function (position) {
            const lat = position.coords.latitude;
            const lng = position.coords.longitude;
            updateMarker(lat, lng);
            map.setView([lat, lng], 15);
            setLocationStatus('Current location captured.', false);
            setLocationButtonLoading(false);
        }, function (error) {
            const messageByCode = {
                1: 'Location permission denied.',
                2: 'Location unavailable.',
                3: 'Location request timed out.',
            };
            setLocationStatus(messageByCode[error.code] || 'Unable to get current location.', true);
            setLocationButtonLoading(false);
        }, {
            enableHighAccuracy: true,
            timeout: 10000,
        });
    }

    if (hasCoords) {
        updateMarker(parsedLat, parsedLng);
    }

    map.on('click', function (event) {
        updateMarker(event.latlng.lat, event.latlng.lng);
    });

    latitudeInput.addEventListener('input', function () {
        const lat = parseFloat(latitudeInput.value);
        const lng = parseFloat(longitudeInput.value);
        if (!isNaN(lat) && !isNaN(lng) && lat >= -90 && lat <= 90 && lng >= -180 && lng <= 180) {
            updateMarker(lat, lng);
            map.setView([lat, lng], 15);
        }
    });

    longitudeInput.addEventListener('input', function () {
        const lat = parseFloat(latitudeInput.value);
        const lng = parseFloat(longitudeInput.value);
        if (!isNaN(lat) && !isNaN(lng) && lat >= -90 && lat <= 90 && lng >= -180 && lng <= 180) {
            updateMarker(lat, lng);
            map.setView([lat, lng], 15);
        }
    });

    if (useCurrentLocationBtn) {
        useCurrentLocationBtn.addEventListener('click', useCurrentLocation);
    }

    const imageInput = document.getElementById('propertyImageInput');
    const imageWrap = document.getElementById('propertyImagePreviewWrap');
    const imagePreview = document.getElementById('propertyImagePreview');

    if (imageInput && imageWrap && imagePreview) {
        imageInput.addEventListener('change', function () {
            const file = imageInput.files && imageInput.files[0];
            if (!file) {
                imageWrap.style.display = 'none';
                imagePreview.src = '';
                return;
            }

            imagePreview.src = URL.createObjectURL(file);
            imageWrap.style.display = 'block';
        });
    }

    function normalizeCustomInclusion(value) {
        return (value || '').replace(/\s+/g, ' ').trim();
    }

    function buildCustomInclusionChip(categoryName, itemLabel) {
        const chip = document.createElement('span');
        chip.className = 'custom-inclusion-chip';

        const label = document.createElement('span');
        label.className = 'custom-inclusion-label';
        label.textContent = itemLabel;

        const removeBtn = document.createElement('button');
        removeBtn.type = 'button';
        removeBtn.className = 'custom-chip-remove';
        removeBtn.setAttribute('aria-label', 'Remove custom inclusion');
        removeBtn.textContent = '×';

        const hiddenInput = document.createElement('input');
        hiddenInput.type = 'hidden';
        hiddenInput.name = 'building_inclusion_custom[' + categoryName + '][]';
        hiddenInput.value = itemLabel;

        chip.appendChild(label);
        chip.appendChild(removeBtn);
        chip.appendChild(hiddenInput);
        return chip;
    }

    const inclusionTools = Array.from(document.querySelectorAll('.custom-inclusion-tools'));

    inclusionTools.forEach((tool) => {
        const categoryName = tool.dataset.customCategory || '';
        const input = tool.querySelector('[data-custom-input]');
        const addButton = tool.querySelector('[data-custom-add-btn]');
        const list = tool.querySelector('[data-custom-list]');

        if (!categoryName || !input || !addButton || !list) {
            return;
        }

        function addItem() {
            const normalizedValue = normalizeCustomInclusion(input.value);
            if (!normalizedValue) {
                input.value = '';
                return;
            }

            if (normalizedValue.length > 100) {
                input.setCustomValidity('Please keep the item within 100 characters.');
                input.reportValidity();
                return;
            }

            const existingValues = Array.from(list.querySelectorAll('input[type="hidden"]'))
                .map((hiddenInput) => normalizeCustomInclusion(hiddenInput.value).toLowerCase());

            if (existingValues.includes(normalizedValue.toLowerCase())) {
                input.value = '';
                input.setCustomValidity('');
                return;
            }

            input.setCustomValidity('');
            list.appendChild(buildCustomInclusionChip(categoryName, normalizedValue));
            input.value = '';
            input.focus();
        }

        addButton.addEventListener('click', addItem);

        input.addEventListener('keydown', function (event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                addItem();
            }
        });

        input.addEventListener('input', function () {
            input.setCustomValidity('');
        });

        list.addEventListener('click', function (event) {
            const removeButton = event.target.closest('.custom-chip-remove');
            if (!removeButton) {
                return;
            }

            event.preventDefault();
            removeButton.closest('.custom-inclusion-chip')?.remove();
        });
    });

    setStep(currentStep);
});
</script>
@endpush
