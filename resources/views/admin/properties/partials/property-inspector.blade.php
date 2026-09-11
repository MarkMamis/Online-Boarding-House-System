{{-- Property Inspector Slide-Over Drawer Partial --}}
<div id="propertyInspectorBackdrop" class="property-inspector-backdrop" onclick="closePropertyInspector()"></div>

<aside id="propertyInspectorDrawer" class="property-inspector-drawer" aria-labelledby="inspectorPropertyTitle" role="dialog" aria-modal="true" aria-hidden="true">
    {{-- Top Bar / Header --}}
    <div class="inspector-header">
        <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
            <div class="min-w-0">
                <div class="inspector-landlord-meta text-truncate">
                    <i class="bi bi-person-circle me-1"></i>
                    <span id="inspectorLandlordName">Landlord</span>
                    <span id="inspectorLandlordContact" class="text-muted ms-1 small"></span>
                </div>
                <h3 id="inspectorPropertyTitle" class="inspector-title text-truncate mb-0">Property Name</h3>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="#" id="inspectorFullDetailsLink" class="btn btn-sm btn-outline-secondary rounded-pill d-none d-sm-inline-flex align-items-center gap-1" title="Open full property details page in new tab" target="_blank">
                    <i class="bi bi-box-arrow-up-right"></i>
                    <span class="small">Full Details</span>
                </a>
                <button type="button" class="btn-close inspector-close-btn" onclick="closePropertyInspector()" aria-label="Close inspector"></button>
            </div>
        </div>

        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div id="inspectorStatusBadgeWrap">
                <span class="badge rounded-pill bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-1" id="inspectorStatusBadge">
                    Pending Review
                </span>
            </div>

            {{-- Queue Navigation for Pending Review Batching --}}
            <div id="inspectorQueueNav" class="inspector-queue-nav d-none">
                <button type="button" id="inspectorPrevBtn" class="btn btn-sm btn-light border px-2 py-1" onclick="navigateInspectorQueue(-1)" title="Previous property in queue">
                    <i class="bi bi-chevron-left"></i>
                </button>
                <span id="inspectorQueuePos" class="inspector-queue-pos small text-muted px-1 fw-semibold">1 of 1</span>
                <button type="button" id="inspectorNextBtn" class="btn btn-sm btn-light border px-2 py-1" onclick="navigateInspectorQueue(1)" title="Next property in queue">
                    <i class="bi bi-chevron-right"></i>
                </button>
            </div>
        </div>
    </div>

    {{-- Tabs Segmented Control --}}
    <div class="inspector-tabs-bar">
        <div class="inspector-tabs" role="tablist">
            <button type="button" class="inspector-tab active" data-tab="overview" onclick="switchInspectorTab('overview')">
                <i class="bi bi-info-circle me-1"></i>Overview
            </button>
            <button type="button" class="inspector-tab" data-tab="rooms" onclick="switchInspectorTab('rooms')">
                <i class="bi bi-door-open me-1"></i>Rooms
                <span class="badge rounded-pill bg-secondary text-white ms-1" id="inspectorTabRoomsCount">0</span>
            </button>
            <button type="button" class="inspector-tab" data-tab="compliance" onclick="switchInspectorTab('compliance')">
                <i class="bi bi-shield-check me-1"></i>Compliance
                <span class="badge rounded-pill bg-danger-subtle text-danger ms-1 d-none" id="inspectorComplianceAlert">!</span>
            </button>
            <button type="button" class="inspector-tab" data-tab="location" onclick="switchInspectorTab('location')">
                <i class="bi bi-geo-alt me-1"></i>Location
            </button>
        </div>
    </div>

    {{-- Body Scroll Area --}}
    <div class="inspector-body" id="inspectorBody">
        {{-- Loading Skeleton State --}}
        <div id="inspectorLoadingState" class="p-4 text-center">
            <div class="spinner-border text-success mb-3" role="status" style="width: 2.5rem; height: 2.5rem;">
                <span class="visually-hidden">Loading...</span>
            </div>
            <div class="fw-semibold text-dark">Loading property details...</div>
            <div class="small text-muted">Retrieving room inventory and landlord compliance.</div>
        </div>

        {{-- Error State --}}
        <div id="inspectorErrorState" class="p-4 text-center d-none">
            <i class="bi bi-exclamation-triangle-fill text-danger fs-1 mb-2"></i>
            <h5 class="fw-bold">Unable to load property</h5>
            <p class="small text-muted mb-3" id="inspectorErrorMessage">Could not fetch property details. Please try again.</p>
            <button type="button" class="btn btn-sm btn-outline-success rounded-pill px-3" onclick="retryInspectorLoad()">
                <i class="bi bi-arrow-clockwise me-1"></i>Retry
            </button>
        </div>

        {{-- Main Loaded Content --}}
        <div id="inspectorContent" class="d-none">
            {{-- TAB 1: OVERVIEW --}}
            <div id="tabContentOverview" class="inspector-tab-pane">
                {{-- Cover Image --}}
                <div class="mb-3">
                    <div id="inspectorCoverWrap" class="inspector-cover-wrap">
                        <img id="inspectorCoverImg" src="" alt="Property cover" class="inspector-cover-img d-none">
                        <div id="inspectorCoverEmpty" class="inspector-cover-empty d-none">
                            <i class="bi bi-buildings fs-1 mb-1"></i>
                            <span class="small">No property image uploaded</span>
                        </div>
                    </div>
                </div>

                {{-- Property Quick Check --}}
                <div class="inspector-card mb-3">
                    <div class="inspector-card-header">
                        <i class="bi bi-clipboard-check me-1 text-success"></i>
                        <span>Property Quick Check</span>
                    </div>
                    <div class="inspector-card-body p-2">
                        <div class="row g-2">
                            <div class="col-6">
                                <div class="quick-check-pill" id="qcImage">
                                    <span class="qc-indicator"><i class="bi bi-dot"></i></span>
                                    <span class="qc-title">Facade Image</span>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="quick-check-pill" id="qcDescription">
                                    <span class="qc-indicator"><i class="bi bi-dot"></i></span>
                                    <span class="qc-title">Description</span>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="quick-check-pill" id="qcCoordinates">
                                    <span class="qc-indicator"><i class="bi bi-dot"></i></span>
                                    <span class="qc-title">Map Coordinates</span>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="quick-check-pill" id="qcRooms">
                                    <span class="qc-indicator"><i class="bi bi-dot"></i></span>
                                    <span class="qc-title">Rooms Added</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Basic Details --}}
                <div class="inspector-card mb-3">
                    <div class="inspector-card-body p-3">
                        <div class="mb-2">
                            <div class="small text-muted fw-semibold text-uppercase">Address</div>
                            <div class="fw-medium text-dark" id="inspectorAddress">Address not set</div>
                        </div>
                        <div class="mb-2">
                            <div class="small text-muted fw-semibold text-uppercase">Description</div>
                            <div class="small text-secondary" id="inspectorDescription">No description provided by landlord.</div>
                        </div>
                        <div class="d-flex justify-content-between pt-1 border-top small text-muted">
                            <span>Submitted:</span>
                            <span class="fw-semibold text-dark" id="inspectorSubmittedAt">N/A</span>
                        </div>
                    </div>
                </div>

                {{-- Building Inclusions / Amenities --}}
                <div class="inspector-card mb-2">
                    <div class="inspector-card-header d-flex justify-content-between align-items-center">
                        <div><i class="bi bi-stars me-1 text-success"></i>Building Inclusions</div>
                        <span class="badge bg-light text-dark border" id="inspectorInclusionsCount">0</span>
                    </div>
                    <div class="inspector-card-body p-3" id="inspectorInclusionsWrap">
                        <div class="text-muted small">No inclusions listed yet.</div>
                    </div>
                </div>
            </div>

            {{-- TAB 2: ROOMS --}}
            <div id="tabContentRooms" class="inspector-tab-pane d-none">
                {{-- Room Metrics Grid --}}
                <div class="row g-2 mb-3">
                    <div class="col-6 col-sm-4">
                        <div class="inspector-stat-chip">
                            <div class="stat-label">Total Rooms</div>
                            <div class="stat-val" id="inspectorTotalRooms">0</div>
                        </div>
                    </div>
                    <div class="col-6 col-sm-4">
                        <div class="inspector-stat-chip">
                            <div class="stat-label">Occupancy</div>
                            <div class="stat-val" id="inspectorOccupancyRate">0%</div>
                            <div class="stat-sub" id="inspectorOccupiedRooms">0 occupied</div>
                        </div>
                    </div>
                    <div class="col-12 col-sm-4">
                        <div class="inspector-stat-chip">
                            <div class="stat-label">Price Range</div>
                            <div class="stat-val fs-6" id="inspectorPriceRange">PHP 0</div>
                        </div>
                    </div>
                </div>

                {{-- Rooms List --}}
                <div id="inspectorRoomsList" class="d-flex flex-column gap-2">
                    {{-- Dynamically populated --}}
                </div>
                <div id="inspectorRoomsEmpty" class="inspector-card p-4 text-center text-muted small d-none">
                    <i class="bi bi-door-closed fs-2 d-block mb-1"></i>
                    No rooms have been added yet for this property.
                </div>
            </div>

            {{-- TAB 3: COMPLIANCE --}}
            <div id="tabContentCompliance" class="inspector-tab-pane d-none">
                <div class="inspector-card mb-3">
                    <div class="inspector-card-header d-flex justify-content-between align-items-center">
                        <div><i class="bi bi-file-earmark-check me-1 text-success"></i>Landlord Compliance Summary</div>
                        <span class="badge rounded-pill" id="inspectorComplianceOverallBadge">Incomplete</span>
                    </div>
                    <div class="inspector-card-body p-3">
                        <div class="d-flex align-items-center justify-content-between py-2 border-bottom">
                            <div>
                                <div class="fw-semibold text-dark">Business Permit</div>
                                <div class="small text-muted">Official Mayor's / LGU permit</div>
                            </div>
                            <span class="badge rounded-pill" id="inspectorBpStatusBadge">Missing</span>
                        </div>
                        <div class="d-flex align-items-center justify-content-between py-2">
                            <div>
                                <div class="fw-semibold text-dark">Safety Certificate</div>
                                <div class="small text-muted">Fire & safety inspection certification</div>
                            </div>
                            <span class="badge rounded-pill" id="inspectorScStatusBadge">Missing</span>
                        </div>
                    </div>
                </div>

                <div class="alert alert-info border-0 rounded-3 small mb-3">
                    <div class="d-flex gap-2">
                        <i class="bi bi-info-circle-fill fs-5 text-info"></i>
                        <div>
                            <strong>Decision Context:</strong> Landlord compliance status is shown to support administrative review. Properties can be verified alongside permit approvals.
                        </div>
                    </div>
                </div>

                <div class="text-end">
                    <a href="#" id="inspectorReviewPermitsLink" class="btn btn-sm btn-outline-success rounded-pill px-3" target="_blank">
                        <i class="bi bi-box-arrow-up-right me-1"></i>Review Landlord Profile & Permits
                    </a>
                </div>
            </div>

            {{-- TAB 4: LOCATION --}}
            <div id="tabContentLocation" class="inspector-tab-pane d-none">
                <div class="inspector-card mb-3">
                    <div class="inspector-card-header">
                        <i class="bi bi-geo-alt-fill me-1 text-success"></i>Map Pin Location
                    </div>
                    <div class="inspector-card-body p-2">
                        <div id="inspectorMapContainer" class="inspector-map-wrap rounded"></div>
                        <div id="inspectorMapEmpty" class="inspector-cover-empty d-none" style="height: 220px;">
                            <i class="bi bi-geo-alt-slash fs-2 mb-1"></i>
                            <span class="small">No coordinates set for this property.</span>
                        </div>
                    </div>
                    <div class="inspector-card-footer p-2 bg-light border-top small text-muted text-truncate" id="inspectorLocationFooter">
                        Camilmil, Calapan City
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Bottom Decision Footer --}}
    <div class="inspector-footer">
        <div id="inspectorFooterPending" class="d-flex align-items-center justify-content-between gap-2 w-100">
            <button type="button" class="btn btn-sm btn-outline-danger fw-semibold rounded-pill px-3 py-2 flex-fill" onclick="openInspectorRejectModal()">
                <i class="bi bi-x-circle me-1"></i>Reject
            </button>
            <button type="button" class="btn btn-sm btn-success fw-bold rounded-pill px-3 py-2 flex-fill" onclick="openInspectorApproveModal()">
                <i class="bi bi-check-circle-fill me-1"></i>Approve Property
            </button>
        </div>

        <div id="inspectorFooterGeneral" class="d-flex align-items-center justify-content-between gap-2 w-100 d-none">
            <span class="small text-muted" id="inspectorGeneralStatusMsg">Property is active.</span>
            <a href="#" id="inspectorGeneralFullDetailsBtn" class="btn btn-sm btn-outline-secondary rounded-pill px-3" target="_blank">
                <i class="bi bi-box-arrow-up-right me-1"></i>Open Full Details
            </a>
        </div>
    </div>
</aside>

{{-- APPROVE CONFIRMATION MODAL --}}
<div class="modal fade" id="inspectorApproveModal" tabindex="-1" aria-labelledby="inspectorApproveModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="inspectorApproveModalLabel">Approve Property</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-3">
                <p class="text-secondary mb-2">
                    You are about to approve <strong id="approveModalPropName" class="text-dark"></strong>, owned by <strong id="approveModalLandlordName" class="text-dark"></strong>.
                </p>
                <div class="alert alert-success-subtle border border-success-subtle rounded-3 small mb-0">
                    <i class="bi bi-check2-circle me-1"></i>
                    This will publish the boarding house listing and make its rooms available for student bookings.
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                <button type="button" id="confirmApproveBtn" class="btn btn-success fw-bold rounded-pill px-4" onclick="submitInspectorApproval()">
                    Confirm Approval
                </button>
            </div>
        </div>
    </div>
</div>

{{-- REJECT CONFIRMATION MODAL --}}
<div class="modal fade" id="inspectorRejectModal" tabindex="-1" aria-labelledby="inspectorRejectModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-danger" id="inspectorRejectModalLabel">Reject Property Submission</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-3">
                <p class="text-secondary small mb-3">
                    Please provide a clear reason why <strong id="rejectModalPropName" class="text-dark"></strong> is being rejected. This will be sent to the landlord.
                </p>
                <div class="mb-2">
                    <label for="rejectReasonInput" class="form-label small fw-bold">Rejection Reason <span class="text-danger">*</span></label>
                    <textarea id="rejectReasonInput" class="form-control" rows="3" placeholder="e.g. Missing photos, misleading address, non-compliant safety inclusions..." required></textarea>
                    <div id="rejectReasonError" class="invalid-feedback d-none">Please provide a valid rejection reason.</div>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                <button type="button" id="confirmRejectBtn" class="btn btn-danger fw-bold rounded-pill px-4" onclick="submitInspectorRejection()">
                    Confirm Rejection
                </button>
            </div>
        </div>
    </div>
</div>

{{-- IN-PAGE TOAST NOTIFICATION --}}
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1090">
    <div id="inspectorToast" class="toast align-items-center text-white bg-success border-0 shadow-lg rounded-3" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body fw-semibold" id="inspectorToastMsg">
                Action completed successfully.
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>
