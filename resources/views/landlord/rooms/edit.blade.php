@extends('layouts.landlord')

@section('content')
@php
    $errorKeys = $errors->keys();
    $initialStep = 1;

    if (collect($errorKeys)->contains(fn ($key) => str_starts_with((string) $key, 'detail_images')
        || str_starts_with((string) $key, 'detail_labels')
        || str_starts_with((string) $key, 'existing_labels')
        || str_starts_with((string) $key, 'delete_detail_images')
        || (string) $key === 'image')) {
        $initialStep = 2;
    } elseif (collect($errorKeys)->contains(fn ($key) => in_array((string) $key, ['pricing_model', 'price_per_room', 'price_per_bed', 'requires_advance_payment'], true))) {
        $initialStep = 3;
    }

    $currentPricingModel = method_exists($room, 'resolvePricingModel') ? $room->resolvePricingModel() : 'hybrid';
    $currentPricePerRoom = method_exists($room, 'effectivePricePerRoom') ? $room->effectivePricePerRoom() : (float) $room->price;
    $currentPricePerBed = method_exists($room, 'effectivePricePerBed') ? $room->effectivePricePerBed() : ((float) $room->price / max(1, (int) $room->capacity));

    $initialInclusions = collect(explode(',', (string) old('inclusions', $room->inclusions)))
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

<div class="room-create-shell" data-initial-step="{{ $initialStep }}" data-room-number="{{ $room->room_number }}">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <div class="text-uppercase small text-muted fw-semibold">Room Setup</div>
            <h1 class="h3 mb-1">Edit Room - {{ $property->name }}</h1>
            <div class="text-muted small">{{ $property->address }}</div>
        </div>
        <a href="{{ route('landlord.properties.show', $property->id) }}" class="btn btn-outline-secondary rounded-pill px-3">Back to Property</a>
    </div>

    <div class="create-summary mb-4">
        <div class="create-summary-item">
            <div class="create-summary-label">Room</div>
            <div class="create-summary-value text-truncate">{{ $room->room_number }}</div>
        </div>
        <div class="create-summary-item">
            <div class="create-summary-label">Current Pricing</div>
            <div class="create-summary-value text-truncate">{{ ucfirst(str_replace('_', ' ', $currentPricingModel)) }}</div>
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

    <form id="updateRoomForm" method="POST" enctype="multipart/form-data" action="{{ route('landlord.properties.rooms.update', [$property->id, $room->id]) }}" class="card shadow-sm room-form-card">
        @csrf
        @method('PUT')

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
                    <span class="step-status">Completed</span>
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
                    <span class="step-status">Completed</span>
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
                    <span class="step-status">Completed</span>
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
                    <span class="step-status">Completed</span>
                </button>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="step-pane" data-step="1">
                <div class="pane-wrap">
                    <div class="section-kicker">Step 1</div>
                    <h5 class="fw-semibold mb-1">Room Details</h5>
                    <p class="text-muted small mb-3">Update room attributes and rent inclusions.</p>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Room Name/Number</label>
                            <input type="text" name="room_number" class="form-control" value="{{ old('room_number', $room->room_number) }}" data-required="1" data-label="Room name or number" id="room_number_input">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Capacity</label>
                            <input type="number" min="1" name="capacity" class="form-control" value="{{ old('capacity', $room->capacity) }}" data-required="1" data-label="Capacity" id="capacity_input">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select" data-required="1" data-label="Status" id="status_input">
                                <option value="available" @selected(old('status', $room->status)==='available')>Available</option>
                                <option value="occupied" @selected(old('status', $room->status)==='occupied')>Occupied</option>
                                <option value="maintenance" @selected(old('status', $room->status)==='maintenance')>Maintenance</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Included in Rent (optional)</label>
                            <input type="hidden" name="inclusions" id="inclusionsSerialized" value="{{ old('inclusions', $room->inclusions) }}">

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

                    <div class="d-flex justify-content-end mt-3">
                        <button type="submit" class="btn btn-brand rounded-pill px-4">
                            <i class="bi bi-check2-circle me-1"></i>Save Changes
                        </button>
                    </div>
                </div>
            </div>

            <div class="step-pane" data-step="2">
                <div class="pane-wrap">
                    <div class="section-kicker">Step 2</div>
                    <h5 class="fw-semibold mb-1">Room Photos</h5>
                    <p class="text-muted small mb-3">Manage cover and detail photos.</p>

                    <div class="row g-4 room-split-grid">
                        <div class="col-xl-5">
                            <div class="edit-panel cover-panel h-100">
                                <div class="section-kicker mb-3">Room Cover Photo</div>
                                <p class="text-muted small mb-3">Upload a clear main photo for room listings and search results.</p>

                                @if(!empty($room->image_path))
                                    <div class="mb-3">
                                        <div class="position-relative d-inline-block w-100">
                                            <img src="{{ asset('storage/'.$room->image_path) }}" alt="Current cover" id="currentCoverImage" class="img-thumbnail w-100 room-cover-preview" style="max-height:220px; object-fit:cover;">
                                            <span class="badge bg-success position-absolute top-0 start-0 m-2">Current Cover</span>
                                        </div>
                                    </div>
                                @else
                                    <img src="" alt="Current cover" id="currentCoverImage" class="d-none">
                                @endif

                                <label class="form-label">{{ !empty($room->image_path) ? 'Replace Cover Photo' : 'Upload Cover Photo' }} <span class="text-muted">(optional)</span></label>
                                <div class="custom-file-box">
                                    <input type="file" id="roomImageInput" name="image" class="visually-hidden" accept="image/*">
                                    <div class="custom-file-actions">
                                        <button type="button" class="btn btn-outline-brand btn-sm rounded-pill px-3" id="roomImageTrigger">
                                            <i class="bi bi-upload me-1"></i>Choose cover photo
                                        </button>
                                        <div class="custom-file-name" id="roomImageName">No file selected</div>
                                    </div>
                                </div>
                                <div class="form-text">Leave blank to keep current cover photo. JPG/PNG/WebP/GIF up to 4MB.</div>
                                <div class="mt-2" id="roomImagePreviewWrap" style="display:none;">
                                    <img id="roomImagePreview" alt="Room cover preview" class="img-thumbnail w-100 room-cover-preview" style="max-height: 220px; object-fit: cover;">
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-7">
                            <div class="detail-photos-panel">
                                <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
                                    <div>
                                        <div class="section-kicker">Detail Photos</div>
                                        <div class="text-muted small">Existing photos can be relabeled or removed. Add new photos below.</div>
                                    </div>
                                </div>

                                @if($roomImages->isNotEmpty())
                                    <div class="row g-3 mb-3" id="existingDetailImages">
                                        @foreach($roomImages as $img)
                                            @php
                                                $existingLabel = old('existing_labels.' . $img->id, $img->label);
                                            @endphp
                                            <div class="col-sm-6" id="detail-img-{{ $img->id }}">
                                                <div class="card h-100 border detail-photo-card existing-detail-card" data-image-src="{{ asset('storage/'.$img->image_path) }}">
                                                    <img src="{{ asset('storage/'.$img->image_path) }}" alt="{{ $existingLabel ?: 'Detail photo' }}" class="card-img-top" style="height:160px; object-fit:cover;">
                                                    <div class="card-body p-2">
                                                        <label class="form-label small fw-semibold mb-1">Label</label>
                                                        <input
                                                            type="text"
                                                            name="existing_labels[{{ $img->id }}]"
                                                            class="form-control form-control-sm existing-detail-label"
                                                            value="{{ $existingLabel }}"
                                                            placeholder="e.g. Bed, Kitchen"
                                                        >
                                                    </div>
                                                    <div class="card-footer p-2 bg-transparent border-top-0">
                                                        <div class="form-check">
                                                            <input class="form-check-input border-danger existing-detail-delete" type="checkbox" name="delete_detail_images[]" value="{{ $img->id }}" id="del_{{ $img->id }}">
                                                            <label class="form-check-label text-danger small" for="del_{{ $img->id }}">Remove this photo</label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif

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

                    <div class="d-flex justify-content-end mt-3">
                        <button type="submit" class="btn btn-brand rounded-pill px-4">
                            <i class="bi bi-check2-circle me-1"></i>Save Changes
                        </button>
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
                                    <input type="radio" name="pricing_model" value="per_room" @checked(old('pricing_model', $currentPricingModel)==='per_room')>
                                    <span class="pricing-model-copy">
                                        <span class="pricing-model-title">Per room</span>
                                        <span class="pricing-model-note">Exclusive room / solo occupancy</span>
                                    </span>
                                </label>
                                <label class="pricing-model-option">
                                    <input type="radio" name="pricing_model" value="per_bed" @checked(old('pricing_model', $currentPricingModel)==='per_bed')>
                                    <span class="pricing-model-copy">
                                        <span class="pricing-model-title">Per bed</span>
                                        <span class="pricing-model-note">Bedspacer setup</span>
                                    </span>
                                </label>
                                <label class="pricing-model-option">
                                    <input type="radio" name="pricing_model" value="hybrid" @checked(old('pricing_model', $currentPricingModel)==='hybrid')>
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
                            <input type="number" step="0.01" min="0" name="price_per_room" class="form-control" value="{{ old('price_per_room', $currentPricePerRoom) }}" data-required="1" data-label="Per-room monthly price" id="price_per_room_input">
                            <div class="form-text">Used for exclusive room or solo occupancy rates.</div>
                        </div>
                        <div class="col-md-6" id="pricePerBedGroup">
                            <label class="form-label">Per-Bed Monthly Price (P)</label>
                            <input type="number" step="0.01" min="0" name="price_per_bed" class="form-control" value="{{ old('price_per_bed', $currentPricePerBed) }}" data-required="1" data-label="Per-bed monthly price" id="price_per_bed_input">
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
                                            @checked(old('requires_advance_payment', $room->requires_advance_payment ? '1' : '0') == '1')
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

                    <div class="d-flex justify-content-end mt-3">
                        <button type="submit" class="btn btn-brand rounded-pill px-4">
                            <i class="bi bi-check2-circle me-1"></i>Save Changes
                        </button>
                    </div>
                </div>
            </div>

            <div class="step-pane" data-step="4">
                <div class="pane-wrap">
                    <div class="section-kicker">Step 4</div>
                    <h5 class="fw-semibold mb-1">Review and Submit</h5>
                    <p class="text-muted small mb-3">Review updated room details, pricing, and photos before saving.</p>

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
                                <div class="review-empty">No detail photos available.</div>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-light border mt-3 mb-0">
                        <strong>Before saving</strong>
                        <div class="small text-muted">Make sure room details, pricing, and photos are correct before saving changes.</div>
                    </div>

                    <div class="d-flex justify-content-end mt-3">
                        <button type="submit" class="btn btn-brand rounded-pill px-4">
                            <i class="bi bi-check2-circle me-1"></i>Save Changes
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-footer bg-light d-flex justify-content-between align-items-center flex-wrap gap-2 action-bar">
            <button type="button" class="btn btn-outline-secondary rounded-pill px-3" id="prevStepBtn">Previous</button>
            <div class="d-flex gap-2">
                <a href="{{ route('landlord.properties.show', $property->id) }}" class="btn btn-outline-secondary rounded-pill px-3">Cancel</a>
                <button type="button" class="btn btn-brand rounded-pill px-4" id="nextStepBtn">Next</button>
                <button type="submit" class="btn btn-brand rounded-pill px-4" id="saveRoomBtn">Save Changes</button>
            </div>
        </div>
    </form>

    <div class="card border-danger mt-3 danger-zone-card">
        <div class="card-header bg-danger text-white fw-semibold">Danger Zone</div>
        <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <div class="fw-semibold">Delete this room</div>
                <div class="text-muted small">This permanently deletes {{ $room->room_number }} and related room data.</div>
            </div>
            <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteRoomModal">
                Delete Room
            </button>
        </div>
    </div>
</div>

<div class="modal fade" id="deleteRoomModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Delete {{ $room->room_number }}?</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Type <strong>{{ $room->room_number }}</strong> below to confirm deletion.</p>
                <input type="text" id="deleteConfirmInput" class="form-control" placeholder="Type room number to confirm">
                <div class="text-danger small mt-1" id="deleteConfirmError" style="display:none">Room number does not match.</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form method="POST" action="{{ route('landlord.properties.rooms.destroy', [$property->id, $room->id]) }}" id="deleteRoomForm">
                    @csrf
                    @method('DELETE')
                    <button type="button" class="btn btn-danger" onclick="confirmDeleteRoom()">Yes, Delete Room</button>
                </form>
            </div>
        </div>
    </div>
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

    .section-kicker {
        font-size: .72rem;
        text-transform: uppercase;
        letter-spacing: .05em;
        color: rgba(2,8,20,.55);
        font-weight: 700;
        margin-bottom: .2rem;
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

    .pricing-model-option.is-selected {
        border-color: rgba(37,99,235,.44);
        background: linear-gradient(180deg, rgba(219,234,254,.5), #ffffff 84%);
        box-shadow: 0 0 0 .14rem rgba(37,99,235,.12);
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
        border: 1px solid rgba(2,8,20,.12);
        border-radius: .75rem;
        padding: .62rem .68rem;
        background: #ffffff;
    }

    .review-label {
        font-size: .72rem;
        text-transform: uppercase;
        letter-spacing: .05em;
        color: rgba(2,8,20,.55);
        font-weight: 700;
        margin-bottom: .2rem;
    }

    .review-value {
        font-size: .92rem;
        font-weight: 700;
        color: #0f172a;
        word-break: break-word;
    }

    .review-media-grid {
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
        gap: .7rem;
    }

    .review-media-card {
        border: 1px solid rgba(2,8,20,.12);
        border-radius: .85rem;
        background: #ffffff;
        padding: .65rem;
    }

    .review-cover-wrap {
        border: 1px dashed rgba(2,8,20,.2);
        border-radius: .7rem;
        min-height: 170px;
        background: #f8fafc;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .review-cover-image {
        width: 100%;
        min-height: 170px;
        max-height: 230px;
        object-fit: cover;
    }

    .review-empty {
        font-size: .82rem;
        color: rgba(2,8,20,.52);
        text-align: center;
        padding: .7rem;
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
        height: 88px;
        object-fit: cover;
        display: block;
    }

    .review-photo-caption {
        padding: .3rem .4rem;
        font-size: .72rem;
        color: #334155;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
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

    .detail-photo-card {
        border-color: rgba(2,8,20,.14) !important;
        border-radius: .8rem;
        overflow: hidden;
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

    .danger-zone-card {
        border-radius: .9rem;
        overflow: hidden;
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

        .detail-slot-grid,
        .review-media-grid {
            grid-template-columns: 1fr;
        }

        .review-detail-gallery {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .room-create-shell {
            padding: .95rem;
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
    function confirmDeleteRoom() {
        const shell = document.querySelector('.room-create-shell');
        const expected = (shell?.dataset.roomNumber || '').trim();
        const input = (document.getElementById('deleteConfirmInput')?.value || '').trim();
        const error = document.getElementById('deleteConfirmError');
        if (input !== expected) {
            if (error) error.style.display = 'block';
            return;
        }

        const form = document.getElementById('deleteRoomForm');
        if (form) form.submit();
    }

    document.addEventListener('DOMContentLoaded', () => {
        const shell = document.querySelector('.room-create-shell');
        const form = document.getElementById('updateRoomForm');
        const nodes = Array.from(document.querySelectorAll('.stepper-node'));
        const panes = Array.from(document.querySelectorAll('.step-pane'));
        const prevBtn = document.getElementById('prevStepBtn');
        const nextBtn = document.getElementById('nextStepBtn');
        const saveBtn = document.getElementById('saveRoomBtn');
        const isStaticCompletedStepper = true;
        const totalSteps = panes.length;
        let currentStep = Number(shell?.dataset.initialStep || 1);

        const roomNumberInput = document.getElementById('room_number_input');
        const capacityInput = document.getElementById('capacity_input');
        const statusInput = document.getElementById('status_input');
        const pricingModelRadios = Array.from(document.querySelectorAll('input[name="pricing_model"]'));
        const pricePerRoomInput = document.getElementById('price_per_room_input');
        const pricePerBedInput = document.getElementById('price_per_bed_input');
        const pricePerRoomGroup = document.getElementById('pricePerRoomGroup');
        const pricePerBedGroup = document.getElementById('pricePerBedGroup');
        const roomImageInput = document.getElementById('roomImageInput');
        const roomImageTrigger = document.getElementById('roomImageTrigger');
        const roomImageName = document.getElementById('roomImageName');
        const roomImageWrap = document.getElementById('roomImagePreviewWrap');
        const roomImagePreview = document.getElementById('roomImagePreview');
        const currentCoverImage = document.getElementById('currentCoverImage');

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

        const existingDetailImages = document.getElementById('existingDetailImages');
        const addDetailSlotBtn = document.getElementById('addDetailSlotBtn');
        const newDetailSlots = document.getElementById('newDetailSlots');

        const advancePaymentCheckbox = document.getElementById('requiresAdvancePayment');
        const advanceCta = advancePaymentCheckbox ? advancePaymentCheckbox.closest('.advance-cta') : null;

        function syncAdvanceCtaState() {
            if (!advancePaymentCheckbox || !advanceCta) return;
            advanceCta.classList.toggle('is-checked', !!advancePaymentCheckbox.checked);
        }

        function filename(value) {
            if (!value) return 'No file selected';
            const parts = String(value).split(/[\\/]/).filter(Boolean);
            return parts[parts.length - 1] || 'No file selected';
        }

        function escapeHtml(value) {
            return String(value || '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');
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
                if (!needsPerRoom) pricePerRoomInput.setCustomValidity('');
            }
            if (pricePerBedInput) {
                pricePerBedInput.disabled = !needsPerBed;
                if (!needsPerBed) pricePerBedInput.setCustomValidity('');
            }
        }

        function setStep(step) {
            currentStep = Math.max(1, Math.min(totalSteps, step));

            panes.forEach((pane) => {
                pane.classList.toggle('is-visible', Number(pane.dataset.step) === currentStep);
            });

            nodes.forEach((node) => {
                const targetStep = Number(node.dataset.stepTarget);
                const isActive = !isStaticCompletedStepper && targetStep === currentStep;
                const isDone = isStaticCompletedStepper ? true : (targetStep < currentStep);
                const statusEl = node.querySelector('.step-status');

                node.classList.toggle('is-active', isActive);
                node.classList.toggle('is-done', isDone);
                node.dataset.stepState = isDone ? 'completed' : (isActive ? 'in-progress' : 'pending');
                node.setAttribute('aria-current', isActive ? 'step' : 'false');

                if (statusEl) {
                    statusEl.textContent = isStaticCompletedStepper ? 'Completed' : (isDone ? 'Completed' : (isActive ? 'In Progress' : 'Pending'));
                }
            });

            if (prevBtn) prevBtn.style.visibility = currentStep === 1 ? 'hidden' : 'visible';
            if (nextBtn) nextBtn.style.display = currentStep === totalSteps ? 'none' : 'inline-flex';
            if (saveBtn) saveBtn.style.display = currentStep === totalSteps ? 'inline-flex' : 'none';

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
                if (skipForPricing) continue;

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

            if (step === 2) {
                const detailSlots = Array.from(activePane.querySelectorAll('.new-detail-slot'));
                for (const slot of detailSlots) {
                    const fileInput = slot.querySelector('.detail-file-input');
                    const labelInput = slot.querySelector('.detail-label-input');
                    const hasFile = !!(fileInput?.files && fileInput.files.length > 0);
                    const labelValue = (labelInput?.value || '').trim();

                    if (labelInput) labelInput.setCustomValidity('');
                    if (hasFile && !labelValue) {
                        if (labelInput) {
                            labelInput.setCustomValidity('Label is required when a detail photo is selected.');
                            labelInput.reportValidity();
                        }
                        return false;
                    }
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

            return true;
        }

        function collectInclusions() {
            return Array.from(inclusionsList.querySelectorAll('.inclusion-input'))
                .map((el) => el.value.trim())
                .filter((value) => value.length > 0);
        }

        function serializeInclusions() {
            if (!inclusionsSerialized) return;
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

        function collectDetailPhotoRows() {
            const rows = [];

            const existingCards = Array.from(document.querySelectorAll('.existing-detail-card'));
            existingCards.forEach((card) => {
                const deleteInput = card.querySelector('.existing-detail-delete');
                if (deleteInput?.checked) return;

                const src = card.dataset.imageSrc || '';
                const labelInput = card.querySelector('.existing-detail-label');
                const label = (labelInput?.value || '').trim() || 'Detail photo';
                if (!src) return;

                rows.push({ src, label });
            });

            const newSlots = Array.from(newDetailSlots.querySelectorAll('.new-detail-slot'));
            newSlots.forEach((slot) => {
                const previewImg = slot.querySelector('.detail-preview-img');
                const previewSrc = previewImg ? (previewImg.getAttribute('src') || '').trim() : '';
                const isVisible = previewImg && !previewImg.classList.contains('d-none') && previewSrc !== '';
                if (!isVisible) return;

                const labelInput = slot.querySelector('.detail-label-input');
                rows.push({
                    src: previewSrc,
                    label: (labelInput?.value || '').trim() || 'Detail photo',
                });
            });

            return rows;
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

            const newCoverSrc = roomImagePreview ? (roomImagePreview.getAttribute('src') || '').trim() : '';
            const currentCoverSrc = currentCoverImage ? (currentCoverImage.getAttribute('src') || '').trim() : '';
            const coverSrc = newCoverSrc || currentCoverSrc;

            if (coverSrc !== '') {
                if (reviewCoverImage) {
                    reviewCoverImage.src = coverSrc;
                    reviewCoverImage.classList.remove('d-none');
                }
                if (reviewCoverEmpty) reviewCoverEmpty.classList.add('d-none');
            } else {
                if (reviewCoverImage) {
                    reviewCoverImage.src = '';
                    reviewCoverImage.classList.add('d-none');
                }
                if (reviewCoverEmpty) reviewCoverEmpty.classList.remove('d-none');
            }

            if (!reviewDetailGallery) return;

            if (detailRows.length === 0) {
                reviewDetailGallery.innerHTML = '<div class="review-empty">No detail photos available.</div>';
                return;
            }

            reviewDetailGallery.innerHTML = detailRows
                .map((row) => {
                    const safeLabel = escapeHtml(row.label);
                    return '<div class="review-photo-card">'
                        + '<img src="' + row.src + '" alt="' + safeLabel + '">'
                        + '<div class="review-photo-caption" title="' + safeLabel + '">' + safeLabel + '</div>'
                        + '</div>';
                })
                .join('');
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
                        if (labelInput) labelInput.setCustomValidity('');
                        updateReviewSummary();
                        return;
                    }

                    const objectUrl = URL.createObjectURL(file);
                    if (previewImg) {
                        previewImg.src = objectUrl;
                        previewImg.classList.remove('d-none');
                    }
                    if (previewEmpty) previewEmpty.classList.add('d-none');
                    updateReviewSummary();
                });
            }

            if (labelInput) {
                labelInput.addEventListener('input', () => {
                    labelInput.setCustomValidity('');
                    updateReviewSummary();
                });
            }
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
            if (labelInput) {
                labelInput.value = '';
                labelInput.setCustomValidity('');
            }
            if (previewImg) {
                previewImg.src = '';
                previewImg.classList.add('d-none');
            }
            if (previewEmpty) previewEmpty.classList.remove('d-none');

            newDetailSlots.appendChild(clone);
            wireDetailSlot(clone);
            updateDetailSlotState();
            updateReviewSummary();
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
                    updateReviewSummary();
                    return;
                }

                roomImagePreview.src = URL.createObjectURL(file);
                roomImageWrap.style.display = '';
                updateReviewSummary();
            });
        }

        if (existingDetailImages) {
            existingDetailImages.addEventListener('change', (event) => {
                const deleteInput = event.target.closest('.existing-detail-delete');
                const labelInput = event.target.closest('.existing-detail-label');

                if (deleteInput) {
                    const card = deleteInput.closest('.detail-photo-card');
                    if (card) {
                        card.style.opacity = deleteInput.checked ? '0.45' : '1';
                        card.style.outline = deleteInput.checked ? '2px solid #ef4444' : '';
                    }
                    updateReviewSummary();
                }

                if (labelInput) {
                    updateReviewSummary();
                }
            });

            existingDetailImages.addEventListener('input', (event) => {
                if (!event.target.classList.contains('existing-detail-label')) return;
                updateReviewSummary();
            });
        }

        nodes.forEach((node) => {
            node.addEventListener('click', () => {
                const targetStep = Number(node.dataset.stepTarget);
                if (isStaticCompletedStepper) {
                    setStep(targetStep);
                    return;
                }
                if (targetStep < currentStep) {
                    setStep(targetStep);
                    return;
                }
                if (validateStep(currentStep)) setStep(targetStep);
            });
        });

        if (prevBtn) {
            prevBtn.addEventListener('click', () => setStep(currentStep - 1));
        }

        if (nextBtn) {
            nextBtn.addEventListener('click', () => {
                if (validateStep(currentStep)) setStep(currentStep + 1);
            });
        }

        if (addInclusionBtn) {
            addInclusionBtn.addEventListener('click', () => {
                inclusionsList.appendChild(createInclusionRow());
                serializeInclusions();
                syncQuickInclusionButtons();
            });
        }

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

        [roomNumberInput, capacityInput, statusInput, pricePerRoomInput, pricePerBedInput].forEach((el) => {
            if (!el) return;
            el.addEventListener('input', () => updateReviewSummary());
            el.addEventListener('change', () => updateReviewSummary());
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
                updateReviewSummary();
            });

            newDetailSlots.querySelectorAll('.new-detail-slot').forEach(wireDetailSlot);
            updateDetailSlotState();
        }

        if (advancePaymentCheckbox) {
            advancePaymentCheckbox.addEventListener('change', syncAdvanceCtaState);
            syncAdvanceCtaState();
        }

        if (form) {
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
        }

        serializeInclusions();
        syncQuickInclusionButtons();
        updatePricingFieldState();
        updateReviewSummary();
        setStep(currentStep);
    });
</script>
@endpush
@endsection

