@extends('layouts.landlord')

@section('content')
@php
    $errorKeys = $errors->keys();
    $initialStep = 1;

    if (collect($errorKeys)->contains(fn ($key) => str_starts_with((string) $key, 'detail_images') || str_starts_with((string) $key, 'detail_labels') || (string) $key === 'image')) {
        $initialStep = 2;
    } elseif (collect($errorKeys)->contains(fn ($key) => in_array((string) $key, ['pricing_model', 'price_per_room', 'price_per_bed', 'requires_advance_payment'], true))) {
        $initialStep = 3;
    }

    $initialInclusions = collect(explode(',', (string) old('inclusions')))
        ->map(fn ($item) => trim($item))
        ->filter()
        ->values();

    $quickInclusionOptions = [
        'Wifi',
        'Water Bill',
        'Electricity Bill',
        'Electic Fan',
        'Aircon',
        'Comfort Room',
        'Kitchen',
        'Balcony',
        'Study Table',
        'Kitchen Table',
        'Tambayan',
    ];
@endphp

<div class="room-create-shell" data-initial-step="{{ $initialStep }}" data-draft-key="room-create-{{ $property->id }}">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <div class="text-uppercase small text-muted fw-semibold">Room Setup</div>
            <h1 class="h3 mb-1">Add Room - {{ $property->name }}</h1>
            <div class="text-muted small">{{ $property->address }}</div>
        </div>
        <a href="{{ route('landlord.properties.rooms.index', $property->id) }}" class="btn btn-outline-secondary rounded-pill px-3">Back</a>
    </div>

    <div class="create-summary mb-4">
        <div class="create-summary-item">
            <div class="create-summary-label">Property</div>
            <div class="create-summary-value text-truncate">{{ $property->name }}</div>
        </div>
        <div class="create-summary-item">
            <div class="create-summary-label">Default Status</div>
            <div class="create-summary-value">Available</div>
        </div>
        <div class="create-summary-item">
            <div class="create-summary-label">Address</div>
            <div class="create-summary-value text-truncate">{{ $property->address }}</div>
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

    <form id="createRoomForm" method="POST" enctype="multipart/form-data" action="{{ route('landlord.properties.rooms.store', $property->id) }}" class="card shadow-sm room-form-card" autocomplete="off">
        @csrf

        <div class="stepper-head border-bottom">
            <div class="room-stepper" id="roomStepper">
                <button type="button" class="stepper-node" data-step-target="1">
                    <span class="step-track" aria-hidden="true">
                        <span class="step-line step-line-left"></span>
                        <span class="step-count">
                            <i class="bi bi-check2 step-check"></i>
                            <span class="step-number">1</span>
                        </span>
                        <span class="step-line step-line-right"></span>
                    </span>
                    <span class="step-kicker">Step 1</span>
                    <span class="step-label">Room Details</span>
                    <span class="step-status">Pending</span>
                </button>
                <button type="button" class="stepper-node" data-step-target="2">
                    <span class="step-track" aria-hidden="true">
                        <span class="step-line step-line-left"></span>
                        <span class="step-count">
                            <i class="bi bi-check2 step-check"></i>
                            <span class="step-number">2</span>
                        </span>
                        <span class="step-line step-line-right"></span>
                    </span>
                    <span class="step-kicker">Step 2</span>
                    <span class="step-label">Room Photos</span>
                    <span class="step-status">Pending</span>
                </button>
                <button type="button" class="stepper-node" data-step-target="3">
                    <span class="step-track" aria-hidden="true">
                        <span class="step-line step-line-left"></span>
                        <span class="step-count">
                            <i class="bi bi-check2 step-check"></i>
                            <span class="step-number">3</span>
                        </span>
                        <span class="step-line step-line-right"></span>
                    </span>
                    <span class="step-kicker">Step 3</span>
                    <span class="step-label">Room Price</span>
                    <span class="step-status">Pending</span>
                </button>
                <button type="button" class="stepper-node" data-step-target="4">
                    <span class="step-track" aria-hidden="true">
                        <span class="step-line step-line-left"></span>
                        <span class="step-count">
                            <i class="bi bi-check2 step-check"></i>
                            <span class="step-number">4</span>
                        </span>
                        <span class="step-line step-line-right"></span>
                    </span>
                    <span class="step-kicker">Step 4</span>
                    <span class="step-label">Review</span>
                    <span class="step-status">Pending</span>
                </button>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="step-pane" data-step="1">
                <div class="pane-wrap">
                    <div class="section-kicker">Step 1</div>
                    <h5 class="fw-semibold mb-1">Room Details</h5>
                    <p class="text-muted small mb-3">Set core room attributes and rent inclusions.</p>

                    <div class="row g-3">
                        <div class="col-md-6 col-lg-3">
                            <label class="form-label">Room Name/Number</label>
                            <input type="text" name="room_number" class="form-control" value="{{ old('room_number') }}" data-required="1" data-label="Room name or number" id="room_number_input" autocomplete="off" spellcheck="false" placeholder="Room 101">
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <label class="form-label">Capacity</label>
                            <input type="number" min="1" name="capacity" class="form-control" value="{{ old('capacity', 1) }}" data-required="1" data-label="Capacity" id="capacity_input">
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select" data-required="1" data-label="Status" id="status_input">
                                <option value="available" @selected(old('status')==='available')>Available</option>
                                <option value="occupied" @selected(old('status')==='occupied')>Occupied</option>
                                <option value="maintenance" @selected(old('status')==='maintenance')>Maintenance</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Included in Rent (optional)</label>
                            <input type="hidden" name="inclusions" id="inclusionsSerialized" value="{{ old('inclusions') }}">

                            <div class="quick-inclusion-wrap mb-2">
                                <div class="small text-muted fw-semibold mb-1">Quick add:</div>
                                <div class="d-flex flex-wrap gap-2">
                                    @foreach($quickInclusionOptions as $quickOption)
                                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill quick-inclusion-btn" data-inclusion="{{ $quickOption }}">{{ $quickOption }}</button>
                                    @endforeach
                                </div>
                            </div>

                            <div id="inclusionsList" class="vstack gap-2">
                                @forelse($initialInclusions as $item)
                                    <div class="input-group inclusion-row">
                                        <input type="text" class="form-control inclusion-input" value="{{ $item }}" placeholder="e.g., Electric fan">
                                        <button class="btn btn-outline-danger remove-inclusion" type="button" aria-label="Remove inclusion">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                    </div>
                                @empty
                                    <div class="input-group inclusion-row">
                                        <input type="text" class="form-control inclusion-input" placeholder="e.g., Electric fan">
                                        <button class="btn btn-outline-danger remove-inclusion" type="button" aria-label="Remove inclusion">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                    </div>
                                @endforelse
                            </div>

                            <button id="addInclusionBtn" type="button" class="btn btn-sm btn-outline-brand mt-2 rounded-pill px-3">
                                <i class="bi bi-plus-lg me-1"></i>Add another
                            </button>
                            <div class="form-text">Add one item per field. These will be saved as comma-separated text.</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="step-pane" data-step="2">
                <div class="pane-wrap">
                    <div class="section-kicker">Step 2</div>
                    <h5 class="fw-semibold mb-1">Room Photos</h5>
                    <p class="text-muted small mb-3">Upload a cover and optional detail photos to improve listing quality.</p>

                    <div class="row g-4 room-split-grid">
                        <div class="col-xl-5">
                            <div class="edit-panel cover-panel h-100">
                                <div class="section-kicker mb-3">Room Cover Photo</div>
                                <p class="text-muted small mb-3">Upload a clear main photo for room listings and search results.</p>
                                <label class="form-label">Room Photo <span class="text-muted">(optional)</span></label>
                                <div class="custom-file-box">
                                    <input type="file" id="roomImageInput" name="image" class="visually-hidden" accept="image/*">
                                    <div class="custom-file-actions">
                                        <button type="button" class="btn btn-outline-brand btn-sm rounded-pill px-3" id="roomImageTrigger">
                                            <i class="bi bi-upload me-1"></i>Choose cover photo
                                        </button>
                                        <div class="custom-file-name" id="roomImageName">No file selected</div>
                                    </div>
                                </div>
                                <div class="form-text">JPG/PNG/WebP/GIF, up to 2MB.</div>
                                <div class="mt-2" id="roomImagePreviewWrap" style="display:none;">
                                    <img id="roomImagePreview" alt="Room photo preview" class="img-thumbnail w-100 room-cover-preview" style="max-height: 220px; object-fit: cover;">
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-7">
                            <div class="detail-photos-panel">
                                <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
                                    <div>
                                        <div class="section-kicker">Detail Photos</div>
                                        <div class="text-muted small">Optional photos for bed, kitchen, comfort room, and other room highlights.</div>
                                    </div>
                                </div>

                                <div id="newDetailSlots" class="detail-upload-stack">
                                    <div class="new-detail-slot" data-index="0">
                                        <div class="new-slot-head">
                                            <div class="slot-index">Photo 1</div>
                                            <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3 remove-slot-btn d-none">
                                                <i class="bi bi-x-lg me-1"></i>Remove
                                            </button>
                                        </div>
                                        <div class="detail-slot-grid">
                                            <div>
                                                <label class="form-label small fw-semibold mb-1">Select Photo</label>
                                                <input type="file" name="detail_images[]" class="visually-hidden detail-file-input" accept="image/*">
                                                <div class="custom-file-actions custom-file-actions--tight">
                                                    <button type="button" class="btn btn-outline-brand btn-sm rounded-pill px-3 detail-file-trigger">
                                                        <i class="bi bi-upload me-1"></i>Choose file
                                                    </button>
                                                    <div class="custom-file-name detail-file-name">No file selected</div>
                                                </div>
                                                <div class="detail-preview-wrap mt-2">
                                                    <img src="" alt="Preview" class="detail-preview-img d-none">
                                                    <div class="detail-preview-empty">No photo selected yet</div>
                                                </div>
                                            </div>
                                            <div>
                                                <label class="form-label small fw-semibold mb-1">Label <span class="text-danger">*</span></label>
                                                <input type="text" name="detail_labels[]" class="form-control detail-label-input" value="{{ old('detail_labels.0') }}" placeholder="e.g. Bed, Kitchen, Comfort Room">
                                                <div class="form-text mt-2">Use short labels, like "Bed", "Kitchen", or "CR".</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <button type="button" id="addDetailSlotBtn" class="btn btn-sm btn-outline-brand mt-2 rounded-pill px-3">
                                    <i class="bi bi-plus-lg me-1"></i>Add Another Photo
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="step-pane" data-step="3">
                <div class="pane-wrap">
                    <div class="section-kicker">Step 3</div>
                    <h5 class="fw-semibold mb-1">Room Price</h5>
                    <p class="text-muted small mb-3">Set your pricing model and monthly rates for room and bed occupancy.</p>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Pricing Model</label>
                            <div class="pricing-model-grid" id="pricingModelGroup">
                                <label class="pricing-model-option">
                                    <input type="radio" name="pricing_model" value="per_room" @checked(old('pricing_model', 'hybrid')==='per_room')>
                                    <span class="pricing-model-copy">
                                        <span class="pricing-model-title">Per room</span>
                                        <span class="pricing-model-note">Exclusive room / solo occupancy</span>
                                    </span>
                                </label>
                                <label class="pricing-model-option">
                                    <input type="radio" name="pricing_model" value="per_bed" @checked(old('pricing_model', 'hybrid')==='per_bed')>
                                    <span class="pricing-model-copy">
                                        <span class="pricing-model-title">Per bed</span>
                                        <span class="pricing-model-note">Bedspacer setup</span>
                                    </span>
                                </label>
                                <label class="pricing-model-option">
                                    <input type="radio" name="pricing_model" value="hybrid" @checked(old('pricing_model', 'hybrid')==='hybrid')>
                                    <span class="pricing-model-copy">
                                        <span class="pricing-model-title">Hybrid</span>
                                        <span class="pricing-model-note">Bedspacer and exclusive room</span>
                                    </span>
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="alert alert-light border h-100 mb-0 d-flex align-items-center">
                                <div class="small text-muted mb-0">
                                    <strong class="text-dark">Pricing Guide:</strong> use <em>Per bed</em> for bedspacer rates, <em>Per room</em> for exclusive room/solo occupancy, or <em>Hybrid</em> to offer both.
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6" id="pricePerRoomGroup">
                            <label class="form-label">Per-Room Monthly Price (P)</label>
                            <input type="number" step="0.01" min="0" name="price_per_room" class="form-control" value="{{ old('price_per_room', old('price')) }}" data-required="1" data-label="Per-room monthly price" id="price_per_room_input">
                            <div class="form-text">Used for exclusive room or solo occupancy rates.</div>
                        </div>
                        <div class="col-md-6" id="pricePerBedGroup">
                            <label class="form-label">Per-Bed Monthly Price (P)</label>
                            <input type="number" step="0.01" min="0" name="price_per_bed" class="form-control" value="{{ old('price_per_bed') }}" data-required="1" data-label="Per-bed monthly price" id="price_per_bed_input">
                            <div class="form-text">Used for bedspacer rate per tenant.</div>
                        </div>

                        @if(!empty($supportsAdvanceRequirement))
                        <div class="col-12">
                            <label class="advance-cta" for="requiresAdvancePayment">
                                <span class="advance-cta-body">
                                    <input
                                        class="form-check-input advance-cta-input"
                                        type="checkbox"
                                        value="1"
                                        id="requiresAdvancePayment"
                                        name="requires_advance_payment"
                                        @checked(old('requires_advance_payment') == '1')
                                    >
                                    <span class="advance-cta-icon">
                                        <i class="bi bi-shield-check"></i>
                                    </span>
                                    <span>
                                        <span class="advance-cta-title">Require 1 month advance payment for this room</span>
                                        <span class="advance-cta-note">If enabled, students cannot turn off advance payment during booking.</span>
                                    </span>
                                </span>
                            </label>
                        </div>
                        <div class="col-12 mt-n1">
                            <div class="form-text">Recommended for high-demand rooms to reduce no-show bookings.</div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="step-pane" data-step="4">
                <div class="pane-wrap">
                    <div class="section-kicker">Step 4</div>
                    <h5 class="fw-semibold mb-1">Review and Submit</h5>
                    <p class="text-muted small mb-3">Review room details, pricing, and uploaded photos before saving.</p>

                    <div class="review-grid">
                        <div class="review-card">
                            <div class="review-label">Room</div>
                            <div class="review-value" id="reviewRoom">-</div>
                        </div>
                        <div class="review-card">
                            <div class="review-label">Capacity</div>
                            <div class="review-value" id="reviewCapacity">1 pax</div>
                        </div>
                        <div class="review-card">
                            <div class="review-label">Price Summary</div>
                            <div class="review-value" id="reviewPrice">P0.00</div>
                        </div>
                        <div class="review-card">
                            <div class="review-label">Status</div>
                            <div class="review-value" id="reviewStatus">Available</div>
                        </div>
                        <div class="review-card">
                            <div class="review-label">Inclusions</div>
                            <div class="review-value" id="reviewInclusionCount">0 item(s)</div>
                        </div>
                        <div class="review-card">
                            <div class="review-label">Detail Photos</div>
                            <div class="review-value" id="reviewDetailCount">0 photo(s)</div>
                        </div>
                    </div>

                    <div class="review-media-grid mt-3">
                        <div class="review-media-card">
                            <div class="review-label mb-2">Cover Photo</div>
                            <div class="review-cover-wrap" id="reviewCoverWrap">
                                <img src="" alt="Cover review" id="reviewCoverImage" class="review-cover-image d-none">
                                <div class="review-empty" id="reviewCoverEmpty">No cover photo uploaded.</div>
                            </div>
                        </div>
                        <div class="review-media-card">
                            <div class="review-label mb-2">Detail Photo Preview</div>
                            <div class="review-detail-gallery" id="reviewDetailGallery">
                                <div class="review-empty">No detail photos uploaded.</div>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-light border mt-3 mb-0">
                        <strong>Before saving</strong>
                        <div class="small text-muted">Make sure room number, pricing, and photos are correct. You can still edit and add more photos after saving.</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-footer bg-light d-flex justify-content-between align-items-center flex-wrap gap-2 action-bar">
            <button type="button" class="btn btn-outline-secondary rounded-pill px-3" id="prevStepBtn">Previous</button>
            <div class="d-flex gap-2">
                <a href="{{ route('landlord.properties.rooms.index', $property->id) }}" class="btn btn-outline-secondary rounded-pill px-3">Cancel</a>
                <button type="button" class="btn btn-brand rounded-pill px-4" id="nextStepBtn">Next</button>
                <button type="submit" class="btn btn-brand rounded-pill px-4" id="saveRoomBtn">Save Room</button>
            </div>
        </div>
    </form>
</div>

@push('styles')
<style>
    .room-create-shell {
        background: linear-gradient(180deg, #ffffff 0%, #fbfdfc 100%);
        border: 1px solid rgba(2,8,20,.08);
        border-radius: 1.25rem;
        box-shadow: 0 10px 26px rgba(2,8,20,.06);
        padding: 1.25rem;
    }

    .create-summary {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: .75rem;
    }

    .create-summary-item {
        border: 1px solid rgba(20,83,45,.16);
        background: linear-gradient(180deg, rgba(167,243,208,.18), rgba(255,255,255,.85));
        border-radius: .9rem;
        padding: .7rem .8rem;
        min-width: 0;
    }

    .create-summary-label {
        font-size: .72rem;
        text-transform: uppercase;
        letter-spacing: .05em;
        color: rgba(2,8,20,.55);
        font-weight: 700;
        margin-bottom: .2rem;
    }

    .create-summary-value {
        font-size: .94rem;
        font-weight: 700;
        color: #14532d;
    }

    .room-form-card {
        border: 1px solid rgba(2,8,20,.08) !important;
        box-shadow: 0 14px 30px rgba(2,8,20,.08) !important;
        border-radius: 1rem;
        overflow: hidden;
    }

    .stepper-head {
        background: #ffffff;
        padding: 1rem 1.15rem;
    }

    .room-stepper {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: .85rem;
        border: 1px solid rgba(2,8,20,.08);
        border-radius: 1rem;
        background: linear-gradient(180deg, #f8fafc 0%, #ffffff 100%);
        padding: .75rem;
    }

    .stepper-node {
        border: none;
        background: transparent;
        border-radius: .8rem;
        padding: .3rem .35rem .45rem;
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        gap: .35rem;
        text-align: left;
        transition: transform .16s ease, opacity .16s ease;
        cursor: pointer;
        min-width: 0;
    }

    .stepper-node:hover {
        transform: translateY(-1px);
    }

    .step-track {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto minmax(0, 1fr);
        align-items: center;
        gap: .42rem;
        width: 100%;
    }

    .step-line {
        height: 4px;
        border-radius: 999px;
        background: #d1d5db;
        transition: background .2s ease;
        position: relative;
        overflow: hidden;
    }

    .stepper-node:first-child .step-line-left,
    .stepper-node:last-child .step-line-right {
        visibility: hidden;
    }

    .stepper-node .step-count {
        width: 38px;
        height: 38px;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: .95rem;
        font-weight: 700;
        border: 2px solid #cbd5e1;
        color: #475569;
        background: #e2e8f0;
        flex: 0 0 auto;
        transition: all .2s ease;
    }

    .stepper-node .step-check {
        display: none;
        line-height: 1;
    }

    .stepper-node .step-number {
        line-height: 1;
    }

    .stepper-node .step-kicker {
        font-size: .66rem;
        text-transform: uppercase;
        letter-spacing: .08em;
        color: #64748b;
        font-weight: 700;
        line-height: 1;
    }

    .stepper-node .step-label {
        font-size: .9rem;
        font-weight: 700;
        color: #334155;
        line-height: 1.2;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .stepper-node .step-status {
        align-self: flex-start;
        border: 1px solid #cbd5e1;
        border-radius: 999px;
        background: #f1f5f9;
        color: #475569;
        font-size: .66rem;
        font-weight: 700;
        letter-spacing: .03em;
        line-height: 1;
        padding: .27rem .56rem;
    }

    .stepper-node.is-active {
        opacity: 1;
    }

    .stepper-node.is-active .step-count {
        border-color: #1d4ed8;
        color: #ffffff;
        background: #2563eb;
        box-shadow: 0 0 0 .18rem rgba(37,99,235,.18);
    }

    .stepper-node.is-active .step-line-left {
        background: #22c55e;
    }

    .stepper-node.is-active .step-line-right {
        background: #d1d5db;
    }

    .stepper-node.is-active .step-line-right::after {
        content: '';
        position: absolute;
        inset: 0 auto 0 0;
        width: 42%;
        border-radius: inherit;
        background: #2563eb;
        animation: stepperPulse 1.2s ease-in-out infinite alternate;
    }

    .stepper-node.is-active .step-kicker,
    .stepper-node.is-active .step-label {
        color: #1e3a8a;
    }

    .stepper-node.is-active .step-status {
        border-color: #93c5fd;
        background: #dbeafe;
        color: #1d4ed8;
    }

    .stepper-node.is-done {
        opacity: 1;
    }

    .stepper-node.is-done .step-count {
        border-color: #16a34a;
        color: #ffffff;
        background: #22c55e;
        box-shadow: 0 0 0 .16rem rgba(34,197,94,.14);
    }

    .stepper-node.is-done .step-check {
        display: inline-flex;
    }

    .stepper-node.is-done .step-number {
        display: none;
    }

    .stepper-node.is-done .step-line-left,
    .stepper-node.is-done .step-line-right {
        background: #22c55e;
    }

    .stepper-node.is-done .step-kicker,
    .stepper-node.is-done .step-label {
        color: #14532d;
    }

    .stepper-node.is-done .step-status {
        border-color: #86efac;
        background: #dcfce7;
        color: #166534;
    }

    @keyframes stepperPulse {
        from {
            width: 36%;
        }
        to {
            width: 52%;
        }
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

    .pricing-model-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: .55rem;
    }

    .pricing-model-option {
        border: 1px solid rgba(2,8,20,.16);
        border-radius: .8rem;
        padding: .55rem .65rem;
        background: #ffffff;
        display: flex;
        align-items: center;
        gap: .55rem;
        cursor: pointer;
        transition: all .2s ease;
        min-width: 0;
    }

    .pricing-model-option input[type="radio"] {
        accent-color: #2563eb;
        cursor: pointer;
        margin-top: 0;
        flex: 0 0 auto;
    }

    .pricing-model-copy {
        min-width: 0;
        display: grid;
        gap: .08rem;
    }

    .pricing-model-title {
        font-size: .84rem;
        font-weight: 700;
        color: #0f172a;
        line-height: 1.2;
    }

    .pricing-model-note {
        font-size: .74rem;
        color: #64748b;
        line-height: 1.2;
    }

    .pricing-model-option:has(input[type="radio"]:checked) {
        border-color: rgba(37,99,235,.44);
        background: linear-gradient(180deg, rgba(219,234,254,.5), #ffffff 84%);
        box-shadow: 0 0 0 .14rem rgba(37,99,235,.12);
    }

    .pricing-model-option.is-selected {
        border-color: rgba(37,99,235,.44);
        background: linear-gradient(180deg, rgba(219,234,254,.5), #ffffff 84%);
        box-shadow: 0 0 0 .14rem rgba(37,99,235,.12);
    }

    .pricing-model-option:has(input[type="radio"]:checked) .pricing-model-title {
        color: #1e3a8a;
    }

    .pricing-model-option.is-selected .pricing-model-title {
        color: #1e3a8a;
    }

    .advance-cta {
        cursor: pointer;
        display: block;
        margin: 0;
    }

    .advance-cta-input {
        position: relative;
        width: 1.15rem;
        height: 1.15rem;
        margin: .2rem 0 0;
        accent-color: #166534;
        cursor: pointer;
        flex: 0 0 auto;
    }

    .advance-cta-body {
        display: flex;
        align-items: flex-start;
        gap: .75rem;
        border: 1px solid rgba(2,8,20,.15);
        border-radius: .85rem;
        background: #ffffff;
        padding: .75rem .8rem;
        transition: all .2s ease;
    }

    .advance-cta-icon {
        width: 34px;
        height: 34px;
        border-radius: 999px;
        border: 1px solid rgba(20,83,45,.28);
        background: rgba(167,243,208,.2);
        color: #14532d;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 auto;
        font-size: 1rem;
    }

    .advance-cta-title {
        display: block;
        font-size: .95rem;
        font-weight: 700;
        color: #166534;
        line-height: 1.25;
    }

    .advance-cta-note {
        display: block;
        margin-top: .2rem;
        font-size: .82rem;
        color: #0f766e;
        line-height: 1.35;
    }

    .advance-cta.is-checked .advance-cta-body {
        border-color: rgba(20,83,45,.55);
        background: linear-gradient(180deg, rgba(167,243,208,.2), #ffffff 85%);
        box-shadow: 0 0 0 .14rem rgba(22,163,74,.14);
    }

    .advance-cta.is-checked .advance-cta-title {
        color: #14532d;
    }

    .advance-cta.is-checked .advance-cta-note {
        color: #065f46;
    }

    .review-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: .6rem;
    }

    .review-card {
        border: 1px solid rgba(2,8,20,.1);
        border-radius: .75rem;
        padding: .55rem .65rem;
        background: #fff;
    }

    .review-label {
        font-size: .68rem;
        letter-spacing: .05em;
        text-transform: uppercase;
        color: #64748b;
        font-weight: 700;
    }

    .review-value {
        font-size: .92rem;
        font-weight: 700;
        color: #0f172a;
    }

    .review-media-grid {
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(0, 2fr);
        gap: .75rem;
    }

    .review-media-card {
        border: 1px solid rgba(2,8,20,.1);
        border-radius: .75rem;
        padding: .65rem;
        background: #fff;
    }

    .review-cover-wrap {
        border: 1px dashed rgba(2,8,20,.2);
        border-radius: .7rem;
        min-height: 180px;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        background: #f8fafc;
    }

    .review-cover-image {
        width: 100%;
        max-height: 220px;
        object-fit: cover;
    }

    .review-detail-gallery {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: .55rem;
    }

    .review-photo-card {
        border: 1px solid rgba(2,8,20,.12);
        border-radius: .65rem;
        overflow: hidden;
        background: #ffffff;
    }

    .review-photo-card img {
        width: 100%;
        height: 100px;
        object-fit: cover;
        display: block;
    }

    .review-photo-caption {
        font-size: .75rem;
        font-weight: 600;
        color: #334155;
        padding: .38rem .45rem;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .review-empty {
        font-size: .82rem;
        color: #64748b;
        font-weight: 600;
    }

    .room-split-grid {
        align-items: stretch;
    }

    .edit-panel {
        border: 1px solid rgba(2,8,20,.09);
        border-radius: 1rem;
        background: linear-gradient(180deg, #ffffff 0%, #fcfffd 100%);
        padding: 1rem;
        box-shadow: 0 8px 20px rgba(2,8,20,.05);
    }

    .cover-panel {
        background: linear-gradient(180deg, #f8fbff 0%, #ffffff 100%);
    }

    .section-kicker {
        font-size: .78rem;
        letter-spacing: .05em;
        text-transform: uppercase;
        color: rgba(2,8,20,.6);
        font-weight: 700;
    }

    .room-form-card .form-label {
        font-weight: 600;
        color: #0f172a;
    }

    .room-form-card .form-control,
    .room-form-card .form-select {
        border-color: rgba(2,8,20,.14);
        background: #ffffff;
    }

    .quick-inclusion-wrap {
        border: 1px dashed rgba(2,8,20,.16);
        border-radius: .75rem;
        background: #f8fafc;
        padding: .55rem .65rem;
    }

    .quick-inclusion-btn {
        transition: all .2s ease;
    }

    .quick-inclusion-btn.is-selected,
    .quick-inclusion-btn.is-selected:disabled {
        border-color: #86efac;
        background: linear-gradient(180deg, #dcfce7 0%, #f0fdf4 100%);
        color: #166534;
        font-weight: 700;
        box-shadow: inset 0 0 0 1px rgba(22,163,74,.24);
        cursor: not-allowed;
    }

    .quick-inclusion-btn:disabled {
        opacity: 1;
    }

    .custom-file-box {
        border: 1px dashed rgba(2,8,20,.24);
        border-radius: .75rem;
        padding: .7rem;
        background: #ffffff;
        margin-bottom: .35rem;
    }

    .custom-file-actions {
        display: flex;
        align-items: center;
        gap: .55rem;
        flex-wrap: wrap;
    }

    .custom-file-actions--tight {
        align-items: flex-start;
    }

    .custom-file-name {
        font-size: .82rem;
        color: #475569;
        border: 1px solid rgba(2,8,20,.12);
        border-radius: 999px;
        padding: .22rem .65rem;
        background: #f8fafc;
        max-width: 100%;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .room-cover-preview {
        border-radius: .75rem;
        border: 1px solid rgba(2,8,20,.14);
    }

    .detail-photos-panel {
        border: 1px solid rgba(2,8,20,.1);
        border-radius: 1rem;
        background: #ffffff;
        padding: 1rem;
        box-shadow: 0 8px 20px rgba(2,8,20,.05);
    }

    .detail-upload-stack {
        display: grid;
        gap: .75rem;
    }

    .new-detail-slot {
        border: 1px solid rgba(2,8,20,.12);
        border-radius: .85rem;
        background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
        padding: .8rem;
    }

    .new-slot-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: .6rem;
        margin-bottom: .65rem;
    }

    .slot-index {
        font-size: .76rem;
        letter-spacing: .05em;
        text-transform: uppercase;
        color: rgba(2,8,20,.58);
        font-weight: 700;
    }

    .detail-slot-grid {
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
        gap: .7rem;
    }

    .detail-preview-wrap {
        border: 1px dashed rgba(2,8,20,.22);
        border-radius: .7rem;
        min-height: 140px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #ffffff;
        overflow: hidden;
    }

    .detail-preview-img {
        width: 100%;
        height: 140px;
        object-fit: cover;
    }

    .detail-preview-empty {
        font-size: .8rem;
        color: rgba(2,8,20,.5);
        font-weight: 600;
    }

    .action-bar {
        border-top: 1px solid rgba(2,8,20,.08);
    }

    @media (max-width: 991.98px) {
        .create-summary,
        .review-grid {
            grid-template-columns: 1fr;
        }

        .pricing-model-grid {
            grid-template-columns: 1fr;
        }

        .room-stepper {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: .65rem;
            padding: .65rem;
        }

        .stepper-node {
            padding: .4rem .28rem .5rem;
        }

        .step-track {
            grid-template-columns: auto;
            justify-content: flex-start;
        }

        .stepper-node .step-line {
            display: none;
        }

        .stepper-node .step-label {
            font-size: .86rem;
        }

        .detail-slot-grid {
            grid-template-columns: 1fr;
        }

        .review-detail-gallery {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .room-create-shell {
            padding: .95rem;
        }

        .cover-panel {
            background: #ffffff;
        }
    }

    @media (max-width: 575.98px) {
        .room-stepper {
            grid-template-columns: 1fr;
            padding: .58rem;
        }

        .stepper-node {
            padding: .35rem .2rem .45rem;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const shell = document.querySelector('.room-create-shell');
        const form = document.getElementById('createRoomForm');
        const nodes = Array.from(document.querySelectorAll('.stepper-node'));
        const panes = Array.from(document.querySelectorAll('.step-pane'));
        const prevBtn = document.getElementById('prevStepBtn');
        const nextBtn = document.getElementById('nextStepBtn');
        const saveBtn = document.getElementById('saveRoomBtn');
        const totalSteps = panes.length;
        let currentStep = Number(shell?.dataset.initialStep || 1);

        const roomNumberInput = document.getElementById('room_number_input');
        const capacityInput = document.getElementById('capacity_input');
        const pricingModelRadios = Array.from(document.querySelectorAll('input[name="pricing_model"]'));
        const pricePerRoomInput = document.getElementById('price_per_room_input');
        const pricePerBedInput = document.getElementById('price_per_bed_input');
        const pricePerRoomGroup = document.getElementById('pricePerRoomGroup');
        const pricePerBedGroup = document.getElementById('pricePerBedGroup');
        const statusInput = document.getElementById('status_input');
        const roomImageInput = document.getElementById('roomImageInput');
        const roomImageTrigger = document.getElementById('roomImageTrigger');
        const roomImageName = document.getElementById('roomImageName');
        const roomImageWrap = document.getElementById('roomImagePreviewWrap');
        const roomImagePreview = document.getElementById('roomImagePreview');

        const reviewRoom = document.getElementById('reviewRoom');
        const reviewCapacity = document.getElementById('reviewCapacity');
        const reviewPrice = document.getElementById('reviewPrice');
        const reviewStatus = document.getElementById('reviewStatus');
        const reviewInclusionCount = document.getElementById('reviewInclusionCount');
        const reviewDetailCount = document.getElementById('reviewDetailCount');
        const reviewCoverImage = document.getElementById('reviewCoverImage');
        const reviewCoverEmpty = document.getElementById('reviewCoverEmpty');
        const reviewDetailGallery = document.getElementById('reviewDetailGallery');

        const inclusionsList = document.getElementById('inclusionsList');
        const addInclusionBtn = document.getElementById('addInclusionBtn');
        const inclusionsSerialized = document.getElementById('inclusionsSerialized');
        const quickInclusionBtns = Array.from(document.querySelectorAll('.quick-inclusion-btn'));
        const addDetailSlotBtn = document.getElementById('addDetailSlotBtn');
        const newDetailSlots = document.getElementById('newDetailSlots');
        const advancePaymentCheckbox = document.getElementById('requiresAdvancePayment');
        const advanceCta = advancePaymentCheckbox ? advancePaymentCheckbox.closest('.advance-cta') : null;
        const draftPrefix = shell?.dataset.draftKey || 'room-create-draft';
        const coverDraftKey = draftPrefix + ':cover';
        const detailDraftKey = draftPrefix + ':details';
        const hasServerValidationErrors = @json($errors->any());

        function getStorageValue(key, fallback = null) {
            try {
                const raw = localStorage.getItem(key);
                return raw ? JSON.parse(raw) : fallback;
            } catch (error) {
                return fallback;
            }
        }

        function setStorageValue(key, value) {
            try {
                localStorage.setItem(key, JSON.stringify(value));
            } catch (error) {
                // Ignore storage limits and continue with in-memory preview.
            }
        }

        function syncAdvanceCtaState() {
            if (!advancePaymentCheckbox || !advanceCta) return;
            advanceCta.classList.toggle('is-checked', !!advancePaymentCheckbox.checked);
        }

        function removeStorageValue(key) {
            try {
                localStorage.removeItem(key);
            } catch (error) {
                // No-op on storage access issues.
            }
        }

        function formatPrice(value) {
            const numeric = Number(value || 0);
            if (Number.isNaN(numeric)) return 'P0.00';
            return 'P' + numeric.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        function pricingModelLabel(value) {
            if (value === 'per_room') return 'Per room';
            if (value === 'per_bed') return 'Per bed';
            return 'Hybrid';
        }

        function selectedPricingModel() {
            const selected = pricingModelRadios.find((radio) => radio.checked);
            return selected ? selected.value : 'hybrid';
        }

        function syncPricingModelOptionState() {
            pricingModelRadios.forEach((radio) => {
                radio.closest('.pricing-model-option')?.classList.toggle('is-selected', radio.checked);
            });
        }

        function updatePricingFieldState() {
            const pricingModel = selectedPricingModel();
            const needsPerRoom = pricingModel === 'per_room' || pricingModel === 'hybrid';
            const needsPerBed = pricingModel === 'per_bed' || pricingModel === 'hybrid';

            syncPricingModelOptionState();

            if (pricePerRoomGroup) {
                pricePerRoomGroup.classList.toggle('d-none', !needsPerRoom);
            }

            if (pricePerBedGroup) {
                pricePerBedGroup.classList.toggle('d-none', !needsPerBed);
            }

            if (pricePerRoomInput) {
                pricePerRoomInput.disabled = !needsPerRoom;
                if (!needsPerRoom) {
                    pricePerRoomInput.setCustomValidity('');
                }
            }

            if (pricePerBedInput) {
                pricePerBedInput.disabled = !needsPerBed;
                if (!needsPerBed) {
                    pricePerBedInput.setCustomValidity('');
                }
            }
        }

        function filename(value) {
            if (!value) return 'No file selected';
            const parts = String(value).split(/[\\/]/).filter(Boolean);
            return parts[parts.length - 1] || 'No file selected';
        }

        function fileToDataUrl(file) {
            return new Promise((resolve) => {
                if (!file) {
                    resolve('');
                    return;
                }

                const reader = new FileReader();
                reader.onload = () => {
                    const rawDataUrl = String(reader.result || '');
                    if (!rawDataUrl) {
                        resolve('');
                        return;
                    }

                    const image = new Image();
                    image.onload = () => {
                        const maxDimension = 1200;
                        let targetWidth = image.width;
                        let targetHeight = image.height;

                        if (targetWidth > maxDimension || targetHeight > maxDimension) {
                            const scale = Math.min(maxDimension / targetWidth, maxDimension / targetHeight);
                            targetWidth = Math.max(1, Math.round(targetWidth * scale));
                            targetHeight = Math.max(1, Math.round(targetHeight * scale));
                        }

                        const canvas = document.createElement('canvas');
                        canvas.width = targetWidth;
                        canvas.height = targetHeight;
                        const ctx = canvas.getContext('2d');
                        if (!ctx) {
                            resolve(rawDataUrl);
                            return;
                        }

                        ctx.drawImage(image, 0, 0, targetWidth, targetHeight);
                        resolve(canvas.toDataURL('image/jpeg', 0.84));
                    };
                    image.onerror = () => resolve(rawDataUrl);
                    image.src = rawDataUrl;
                };
                reader.onerror = () => resolve('');
                reader.readAsDataURL(file);
            });
        }

        function persistDetailDraft() {
            const payload = Array.from(newDetailSlots.querySelectorAll('.new-detail-slot'))
                .map((slot) => {
                    const previewImg = slot.querySelector('.detail-preview-img');
                    const src = previewImg ? (previewImg.getAttribute('src') || '').trim() : '';
                    if (!src) return null;

                    const labelInput = slot.querySelector('input[name="detail_labels[]"]');
                    return {
                        src,
                        label: (labelInput?.value || '').trim(),
                    };
                })
                .filter(Boolean);

            if (payload.length === 0) {
                removeStorageValue(detailDraftKey);
                return;
            }

            setStorageValue(detailDraftKey, payload);
        }

        function hydrateDetailSlot(slot, draft) {
            const previewImg = slot.querySelector('.detail-preview-img');
            const previewEmpty = slot.querySelector('.detail-preview-empty');
            const labelInput = slot.querySelector('.detail-label-input');
            const fileName = slot.querySelector('.detail-file-name');

            if (previewImg) {
                previewImg.src = draft.src || '';
                previewImg.classList.toggle('d-none', !draft.src);
            }

            if (previewEmpty) {
                previewEmpty.classList.toggle('d-none', !!draft.src);
            }

            if (labelInput) {
                labelInput.value = draft.label || '';
            }

            if (fileName && draft.src) {
                fileName.textContent = 'Draft preview loaded';
            }
        }

        function restoreImageDrafts() {
            const storedCover = getStorageValue(coverDraftKey, '');
            if (typeof storedCover === 'string' && storedCover.trim() !== '') {
                roomImagePreview.src = storedCover;
                roomImageWrap.style.display = '';
                roomImageName.textContent = 'Draft preview loaded';
            }

            const storedDetails = getStorageValue(detailDraftKey, []);
            if (!Array.isArray(storedDetails) || storedDetails.length === 0) {
                return;
            }

            while (newDetailSlots.querySelectorAll('.new-detail-slot').length < storedDetails.length) {
                createNewDetailSlot();
            }

            const slots = Array.from(newDetailSlots.querySelectorAll('.new-detail-slot'));
            storedDetails.forEach((draft, index) => {
                if (!slots[index] || !draft || typeof draft !== 'object') return;
                hydrateDetailSlot(slots[index], draft);
            });

            updateDetailSlotState();
        }

        function clearImageDraftState() {
            removeStorageValue(coverDraftKey);
            removeStorageValue(detailDraftKey);
        }

        function setStep(step) {
            currentStep = Math.max(1, Math.min(totalSteps, step));

            panes.forEach((pane) => {
                pane.classList.toggle('is-visible', Number(pane.dataset.step) === currentStep);
            });

            nodes.forEach((node) => {
                const targetStep = Number(node.dataset.stepTarget);
                const isActive = targetStep === currentStep;
                const isDone = targetStep < currentStep;
                const statusEl = node.querySelector('.step-status');

                node.classList.toggle('is-active', isActive);
                node.classList.toggle('is-done', isDone);
                node.dataset.stepState = isDone ? 'completed' : (isActive ? 'in-progress' : 'pending');
                node.setAttribute('aria-current', isActive ? 'step' : 'false');

                if (statusEl) {
                    statusEl.textContent = isDone
                        ? 'Completed'
                        : (isActive ? 'In Progress' : 'Pending');
                }
            });

            prevBtn.style.visibility = currentStep === 1 ? 'hidden' : 'visible';
            nextBtn.style.display = currentStep === totalSteps ? 'none' : 'inline-flex';
            saveBtn.style.display = currentStep === totalSteps ? 'inline-flex' : 'none';

            if (currentStep === totalSteps) {
                updateReviewSummary();
            }
        }

        function validateStep(step) {
            const activePane = panes.find((pane) => Number(pane.dataset.step) === step);
            if (!activePane) return true;

            const requiredFields = activePane.querySelectorAll('[data-required="1"]');
            for (const field of requiredFields) {
                const label = field.dataset.label || 'This field';
                const value = (field.value || '').trim();
                const pricingModel = selectedPricingModel();
                const skipForPricing =
                    (field.name === 'price_per_room' && pricingModel === 'per_bed') ||
                    (field.name === 'price_per_bed' && pricingModel === 'per_room');

                field.setCustomValidity('');
                if (skipForPricing) {
                    continue;
                }

                if (value === '') {
                    field.setCustomValidity(label + ' is required.');
                    field.reportValidity();
                    return false;
                }

                if (field.name === 'capacity' && Number(value) < 1) {
                    field.setCustomValidity('Capacity must be at least 1.');
                    field.reportValidity();
                    return false;
                }

                if ((field.name === 'price_per_room' || field.name === 'price_per_bed') && Number(value) <= 0) {
                    field.setCustomValidity('Price must be greater than zero.');
                    field.reportValidity();
                    return false;
                }
            }

            if (step === 3) {
                pricingModelRadios.forEach((radio) => radio.setCustomValidity(''));
                const selectedModel = pricingModelRadios.find((radio) => radio.checked);
                if (!selectedModel) {
                    const firstRadio = pricingModelRadios[0];
                    if (firstRadio) {
                        firstRadio.setCustomValidity('Pricing model is required.');
                        firstRadio.reportValidity();
                    }
                    return false;
                }
            }

            if (step === 2) {
                const detailSlots = Array.from(activePane.querySelectorAll('.new-detail-slot'));
                for (const slot of detailSlots) {
                    const fileInput = slot.querySelector('.detail-file-input');
                    const labelInput = slot.querySelector('.detail-label-input');
                    const hasFile = !!(fileInput?.files && fileInput.files.length > 0);
                    const labelValue = (labelInput?.value || '').trim();

                    if (labelInput) {
                        labelInput.setCustomValidity('');
                    }

                    if (hasFile && !labelValue) {
                        if (labelInput) {
                            labelInput.setCustomValidity('Label is required when a detail photo is selected.');
                            labelInput.reportValidity();
                        }
                        return false;
                    }
                }
            }

            return true;
        }

        function collectInclusions() {
            return Array.from(inclusionsList.querySelectorAll('.inclusion-input'))
                .map((el) => el.value.trim())
                .filter((value) => value.length > 0);
        }

        function collectDetailPhotoRows() {
            const slots = Array.from(newDetailSlots.querySelectorAll('.new-detail-slot'));
            return slots
                .map((slot) => {
                    const previewImg = slot.querySelector('.detail-preview-img');
                    const previewSrc = previewImg ? (previewImg.getAttribute('src') || '').trim() : '';
                    const isVisible = previewImg && !previewImg.classList.contains('d-none') && previewSrc !== '';
                    const labelInput = slot.querySelector('input[name="detail_labels[]"]');
                    if (!isVisible) return null;
                    return {
                        src: previewSrc,
                        label: (labelInput?.value || '').trim() || 'Detail photo',
                    };
                })
                .filter(Boolean);
        }

        function updateReviewSummary() {
            const roomText = (roomNumberInput?.value || '').trim();
            const capVal = Math.max(1, Number(capacityInput?.value || 1));
            const pricingModel = selectedPricingModel();
            const perRoomVal = pricePerRoomInput?.value || 0;
            const perBedVal = pricePerBedInput?.value || 0;
            const statusVal = statusInput?.value || 'available';
            const statusLabel = statusVal.charAt(0).toUpperCase() + statusVal.slice(1);
            const inclusionItems = collectInclusions();
            const detailRows = collectDetailPhotoRows();

            let pricingSummary = '';
            if (pricingModel === 'per_room') {
                pricingSummary = pricingModelLabel(pricingModel) + ': ' + formatPrice(perRoomVal);
            } else if (pricingModel === 'per_bed') {
                pricingSummary = pricingModelLabel(pricingModel) + ': ' + formatPrice(perBedVal);
            } else {
                pricingSummary = pricingModelLabel(pricingModel)
                    + ': Room ' + formatPrice(perRoomVal)
                    + ' | Bed ' + formatPrice(perBedVal);
            }

            if (reviewRoom) reviewRoom.textContent = roomText || '-';
            if (reviewCapacity) reviewCapacity.textContent = capVal + ' pax';
            if (reviewPrice) reviewPrice.textContent = pricingSummary;
            if (reviewStatus) reviewStatus.textContent = statusLabel;
            if (reviewInclusionCount) reviewInclusionCount.textContent = inclusionItems.length + ' item(s)';
            if (reviewDetailCount) reviewDetailCount.textContent = detailRows.length + ' photo(s)';

            const coverSrc = roomImagePreview ? (roomImagePreview.getAttribute('src') || '').trim() : '';

            if (coverSrc !== '') {
                reviewCoverImage.src = coverSrc;
                reviewCoverImage.classList.remove('d-none');
                reviewCoverEmpty.classList.add('d-none');
            } else {
                reviewCoverImage.src = '';
                reviewCoverImage.classList.add('d-none');
                reviewCoverEmpty.classList.remove('d-none');
            }

            if (detailRows.length === 0) {
                reviewDetailGallery.innerHTML = '<div class="review-empty">No detail photos uploaded.</div>';
                return;
            }

            reviewDetailGallery.innerHTML = detailRows
                .map((row) => {
                    const safeLabel = row.label
                        .replace(/&/g, '&amp;')
                        .replace(/</g, '&lt;')
                        .replace(/>/g, '&gt;')
                        .replace(/"/g, '&quot;')
                        .replace(/'/g, '&#39;');
                    return '<div class="review-photo-card">'
                        + '<img src="' + row.src + '" alt="' + safeLabel + '">'
                        + '<div class="review-photo-caption" title="' + safeLabel + '">' + safeLabel + '</div>'
                        + '</div>';
                })
                .join('');
        }

        function createInclusionRow(value = '') {
            const row = document.createElement('div');
            row.className = 'input-group inclusion-row';
            row.innerHTML = ''
                + '<input type="text" class="form-control inclusion-input" placeholder="e.g., Electric fan">'
                + '<button class="btn btn-outline-danger remove-inclusion" type="button" aria-label="Remove inclusion">'
                + '<i class="bi bi-x-lg"></i>'
                + '</button>';
            row.querySelector('.inclusion-input').value = value;
            return row;
        }

        function serializeInclusions() {
            inclusionsSerialized.value = collectInclusions().join(', ');
        }

        function normalizeInclusionValue(value) {
            return (value || '').trim().toLowerCase();
        }

        function hasInclusionValue(value) {
            const normalized = normalizeInclusionValue(value);
            if (!normalized) return false;
            return Array.from(inclusionsList.querySelectorAll('.inclusion-input'))
                .some((el) => normalizeInclusionValue(el.value) === normalized);
        }

        function syncQuickInclusionButtons() {
            quickInclusionBtns.forEach((btn) => {
                const inclusionValue = btn.dataset.inclusion || '';
                const isSelected = hasInclusionValue(inclusionValue);
                btn.classList.toggle('is-selected', isSelected);
                btn.disabled = isSelected;
                btn.setAttribute('aria-disabled', isSelected ? 'true' : 'false');
            });
        }

        function addInclusionValue(value) {
            const normalized = (value || '').trim();
            if (!normalized || hasInclusionValue(normalized)) return;

            const rows = inclusionsList.querySelectorAll('.inclusion-row');
            const firstInput = rows[0]?.querySelector('.inclusion-input');
            if (rows.length === 1 && firstInput && firstInput.value.trim() === '') {
                firstInput.value = normalized;
            } else {
                inclusionsList.appendChild(createInclusionRow(normalized));
            }

            serializeInclusions();
            syncQuickInclusionButtons();
            updateReviewSummary();
        }

        function wireDetailSlot(slot) {
            const fileInput = slot.querySelector('.detail-file-input');
            const fileTrigger = slot.querySelector('.detail-file-trigger');
            const fileName = slot.querySelector('.detail-file-name');
            const previewImg = slot.querySelector('.detail-preview-img');
            const previewEmpty = slot.querySelector('.detail-preview-empty');
            const labelInput = slot.querySelector('.detail-label-input');

            if (fileTrigger && fileInput) {
                fileTrigger.addEventListener('click', () => fileInput.click());
            }

            if (fileInput) {
                fileInput.addEventListener('change', () => {
                    if (fileName) fileName.textContent = filename(fileInput.value);

                    const file = fileInput.files && fileInput.files[0];
                    if (!file) {
                        if (previewImg) {
                            previewImg.src = '';
                            previewImg.classList.add('d-none');
                        }
                        if (previewEmpty) previewEmpty.classList.remove('d-none');
                        persistDetailDraft();
                        updateReviewSummary();
                        return;
                    }

                    fileToDataUrl(file).then((dataUrl) => {
                        if (!dataUrl) {
                            persistDetailDraft();
                            updateReviewSummary();
                            return;
                        }

                        if (previewImg) {
                            previewImg.src = dataUrl;
                            previewImg.classList.remove('d-none');
                        }
                        if (previewEmpty) previewEmpty.classList.add('d-none');
                        persistDetailDraft();
                        updateReviewSummary();
                    });
                });
            }

            if (labelInput) {
                labelInput.addEventListener('input', () => {
                    persistDetailDraft();
                    updateReviewSummary();
                });
                labelInput.addEventListener('change', () => {
                    persistDetailDraft();
                    updateReviewSummary();
                });
            }
        }

        function updateDetailSlotState() {
            const slots = Array.from(newDetailSlots.querySelectorAll('.new-detail-slot'));
            slots.forEach((slot, index) => {
                const indexLabel = slot.querySelector('.slot-index');
                const removeBtn = slot.querySelector('.remove-slot-btn');
                if (indexLabel) indexLabel.textContent = 'Photo ' + (index + 1);
                if (removeBtn) removeBtn.classList.toggle('d-none', slots.length === 1);
            });
        }

        function createNewDetailSlot() {
            const first = newDetailSlots.querySelector('.new-detail-slot');
            if (!first) return;

            const clone = first.cloneNode(true);
            const fileInput = clone.querySelector('.detail-file-input');
            const fileName = clone.querySelector('.detail-file-name');
            const labelInput = clone.querySelector('.detail-label-input');
            const previewImg = clone.querySelector('.detail-preview-img');
            const previewEmpty = clone.querySelector('.detail-preview-empty');

            if (fileInput) fileInput.value = '';
            if (fileName) fileName.textContent = 'No file selected';
            if (labelInput) labelInput.value = '';
            if (previewImg) {
                previewImg.src = '';
                previewImg.classList.add('d-none');
            }
            if (previewEmpty) previewEmpty.classList.remove('d-none');

            newDetailSlots.appendChild(clone);
            wireDetailSlot(clone);
            updateDetailSlotState();
        }

        if (roomImageTrigger && roomImageInput) {
            roomImageTrigger.addEventListener('click', () => roomImageInput.click());
        }

        if (roomImageInput && roomImageWrap && roomImagePreview && roomImageName) {
            roomImageInput.addEventListener('change', () => {
                roomImageName.textContent = filename(roomImageInput.value);
                const file = roomImageInput.files && roomImageInput.files[0];

                if (!file) {
                    roomImageWrap.style.display = 'none';
                    roomImagePreview.src = '';
                    removeStorageValue(coverDraftKey);
                    updateReviewSummary();
                    return;
                }

                fileToDataUrl(file).then((dataUrl) => {
                    if (!dataUrl) {
                        removeStorageValue(coverDraftKey);
                        updateReviewSummary();
                        return;
                    }

                    roomImagePreview.src = dataUrl;
                    roomImageWrap.style.display = '';
                    setStorageValue(coverDraftKey, dataUrl);
                    updateReviewSummary();
                });
            });
        }

        document.querySelectorAll('[data-required="1"]').forEach((field) => {
            field.addEventListener('input', () => field.setCustomValidity(''));
            field.addEventListener('change', () => field.setCustomValidity(''));
        });

        prevBtn.addEventListener('click', () => setStep(currentStep - 1));
        nextBtn.addEventListener('click', () => {
            if (validateStep(currentStep)) setStep(currentStep + 1);
        });

        nodes.forEach((node) => {
            node.addEventListener('click', () => {
                const targetStep = Number(node.dataset.stepTarget);
                if (targetStep < currentStep) {
                    setStep(targetStep);
                    return;
                }
                if (validateStep(currentStep)) setStep(targetStep);
            });
        });

        addInclusionBtn.addEventListener('click', () => {
            inclusionsList.appendChild(createInclusionRow());
            serializeInclusions();
            syncQuickInclusionButtons();
        });

        quickInclusionBtns.forEach((btn) => {
            btn.addEventListener('click', () => addInclusionValue(btn.dataset.inclusion || ''));
        });

        inclusionsList.addEventListener('click', (event) => {
            const button = event.target.closest('.remove-inclusion');
            if (!button) return;

            const rows = inclusionsList.querySelectorAll('.inclusion-row');
            if (rows.length <= 1) {
                const inputEl = rows[0]?.querySelector('.inclusion-input');
                if (inputEl) inputEl.value = '';
            } else {
                button.closest('.inclusion-row')?.remove();
            }

            serializeInclusions();
            syncQuickInclusionButtons();
            updateReviewSummary();
        });

        inclusionsList.addEventListener('input', (event) => {
            if (!event.target.classList.contains('inclusion-input')) return;
            serializeInclusions();
            syncQuickInclusionButtons();
            updateReviewSummary();
        });

        if (addDetailSlotBtn && newDetailSlots) {
            addDetailSlotBtn.addEventListener('click', createNewDetailSlot);

            newDetailSlots.addEventListener('click', (event) => {
                const removeBtn = event.target.closest('.remove-slot-btn');
                if (!removeBtn) return;
                const slot = removeBtn.closest('.new-detail-slot');
                if (!slot) return;

                slot.remove();
                updateDetailSlotState();
                persistDetailDraft();
                updateReviewSummary();
            });

            newDetailSlots.querySelectorAll('.new-detail-slot').forEach(wireDetailSlot);
            updateDetailSlotState();
        }

        [roomNumberInput, capacityInput, pricePerRoomInput, pricePerBedInput, statusInput].forEach((el) => {
            if (!el) return;
            el.addEventListener('input', () => {
                updateReviewSummary();
            });
            el.addEventListener('change', () => {
                updateReviewSummary();
            });
        });

        pricingModelRadios.forEach((radio) => {
            radio.addEventListener('input', () => {
                radio.setCustomValidity('');
                updatePricingFieldState();
                updateReviewSummary();
            });
            radio.addEventListener('change', () => {
                radio.setCustomValidity('');
                updatePricingFieldState();
                updateReviewSummary();
            });
        });

        form.addEventListener('submit', (event) => {
            serializeInclusions();
            for (let step = 1; step <= totalSteps; step += 1) {
                if (!validateStep(step)) {
                    event.preventDefault();
                    setStep(step);
                    return;
                }
            }
        });

        if (advancePaymentCheckbox) {
            advancePaymentCheckbox.addEventListener('change', syncAdvanceCtaState);
            syncAdvanceCtaState();
        }

        serializeInclusions();
        syncQuickInclusionButtons();
        if (hasServerValidationErrors) {
            restoreImageDrafts();
        } else {
            clearImageDraftState();
        }
        updatePricingFieldState();
        updateReviewSummary();
        setStep(currentStep);
    });
</script>
@endpush
@endsection
