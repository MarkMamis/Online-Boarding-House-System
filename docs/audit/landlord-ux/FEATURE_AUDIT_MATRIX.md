# OBHS Landlord Portal: Feature Audit Matrix

**Document ID:** `FEATURE_AUDIT_MATRIX.md`  
**Date:** October 18, 2026  
**Auditor:** QA Lead, Principal Product Designer & Laravel Architect  
**Scope:** Comprehensive Audit Evaluation across all 15 Landlord Functional Modules.

---

## Complete Module Audit Matrix

| Module / Domain | Canonical Route(s) | Controller & Method | UX Grade | Critical Issues & Code Findings | Redundant Controls | Recommended Action | Priority |
| :--- | :--- | :--- | :---: | :--- | :--- | :--- | :---: |
| **Landlord Dashboard** | `GET /landlord/dashboard` | `AuthController@landlordDashboard` | **D-** | 60KB file, 10 levels of nested sections, 8 duplicate module cards, 2 Chart.js graphs, inline message reply forms, 3 embedded modals. | Duplicates entire sidebar; duplicates Analytics; duplicates Properties list. | Rebuild as clean "Needs Your Attention" Home screen with dynamic priority queue. | **P1** |
| **Property Setup** | `GET /landlord/properties/create`, `POST /landlord/properties` | `PropertyController@create, store` | **D** | 4-step wizard (50KB), 35+ fields, mandatory first room creation, zero draft persistence (loss of data on exit). | `#addPropertyModal` in dashboard has conflicting validation rules with full page. | Decouple room creation; 3 simple cards; session draft restoration. | **P1** |
| **Property Management** | `GET /landlord/properties`, `GET .../{id}` | `PropertyController@index, show` | **C+** | Separate `/landlord/monitoring/properties` page duplicates this list without action buttons. | Duplicate monitoring table. | Merge occupancy health badges directly onto property cards. | **P2** |
| **Room Creation** | `GET .../rooms/create`, `POST .../rooms` | `RoomController@create, store` | **D+** | 4-step wizard (74KB, 1,903 lines), zero room duplication capability. Repetitive entry for 10 identical rooms. | `#addRoomModal` in dashboard conflicts with full page. | 1-step clean modal + 1-click "Duplicate Room" action. | **P1** |
| **Room Directory** | `GET /landlord/rooms`, `GET .../{property}/rooms` | `RoomController@landlordIndex, index` | **C** | Dynamic room loop in sidebar inflates DOM with 30+ room edit sub-links. | Dynamic sidebar room tree. | Prune room tree from sidebar; manage rooms inside Property Details. | **P2** |
| **Boarding Requests** | `GET /landlord/bookings`, `POST .../approve, reject` | `BookingController@landlordIndex, approve, reject` | **B-** | Functional and secure. Lacks clear applicant background context. Approving immediately marks booking approved before onboarding. | Header "Requests" button duplicates sidebar badge. | Enhance applicant cards; clarify that approval starts move-in onboarding. | **P2** |
| **Tenant Onboarding** | `GET /landlord/onboarding`, `GET .../review` | `TenantOnboardingController@landlordIndex, reviewDocuments` | **C-** | Landlords must jump across 5 separate pages to complete one onboarding. Unclear who is responsible at each step. | Duplicates document approval and payment verification in multiple views. | Unified Move-In Progress card with step-by-step responsibility checklist. | **P1** |
| **Rental Contracts** | `GET .../contract`, `POST .../sign-contract`, `GET .../contract-pdf` | `TenantOnboardingController@viewContract, signContractLandlord, downloadContractPdf` | **B** | Solid DomPDF generation and digital signature capture. Landlords confuse saved profile signature with lease execution. | Duplicate sign controls across review and contract views. | 1-click "Apply Saved Signature & Execute Lease" with clear modal preview. | **P2** |
| **Payments: Monthly** | `GET /landlord/payments`, `POST .../paid` | `PaymentController@index, markAsPaid` | **D+** | Manual "Mark as Paid" advances due date without writing `tenant_payments` row, causing revenue reporting desync. | Separate from Onboarding payments. | Ensure atomic ledger row creation on all payment state changes. | **P0** |
| **Payments: Move-In** | `POST /landlord/onboarding/{id}/approve-documents` | `TenantOnboardingController@approveDocuments` | **C-** | Handled in Onboarding controller completely separate from Payments Center. Advance rent double-counting risk. | Disjoint from `landlord.payments.index`. | Consolidate Move-in Dues into Unified Payments Center tab. | **P1** |
| **Active Tenants** | `GET /landlord/tenants` | `BookingController@landlordTenants` | **C** | Approved applicants who have not submitted documents or paid move-in dues appear as active tenants. | Lacks past resident filter. | Filter strictly to completed onboardings; separate into Active vs Past tabs. | **P1** |
| **Permits & Documents** | `GET /landlord/documents`, `POST /landlord/documents` | `LandlordDocumentController@index, store` | **B-** | Functional expiration tracking. Split between `/landlord/monitoring/compliance` and `/landlord/documents`. | Split view vs upload page. | Single Compliance Hub combining status badges with upload forms. | **P2** |
| **Room Maintenance** | `GET /landlord/maintenance`, `POST .../set, complete` | `MaintenanceController@index, setMaintenance, completeMaintenance` | **C+** | Only toggles room status; misleadingly labeled "Track issues and repairs". | Labeled as issue ticketing. | Relabel to "Room Availability & Maintenance Mode". | **P2** |
| **Leave Requests** | `GET /landlord/leave-requests`, `POST .../approve, reject` | `LandlordLeaveRequestController@index, approve, reject` | **B** | Premature stay cancellation bug fixed in commit `237acee`. Lacks clear microcopy on departure effects. | None. | Add clear explanatory text for future-dated vs immediate move-out. | **P3** |
| **Business Analytics** | `GET /landlord/analytics` | `AnalyticsController@index` | **C-** | Omits onboarding payments from collected revenue; expected rent multiplies flat room price; undercounts multi-bed occupancy. | Duplicates dashboard graphs. | Fix calculation formulas; clean 4-KPI cards + 6-month cash collection trend. | **P1** |
| **Messaging** | `GET /landlord/messages` | `MessageController@index, store` | **B** | Solid messaging backend. Dashboard inline reply accordions add visual noise. | Dashboard inline message forms. | Keep messaging inside dedicated Inbox with clean chat interface. | **P2** |
| **Landlord Profile** | `GET /landlord/profile`, `PUT ...` | `AuthController@landlordProfile, updateLandlordProfile` | **B+** | Clean profile editor with signature pad and GCash payout setup. | Intrusive setup popup on dashboard. | Non-blocking alert banner on Home screen for incomplete profiles. | **P3** |

---

## Priority Legend
- **P0:** Critical payment integrity, financial ledger synchronization, or security issue.
- **P1:** Broken workflow, missing draft persistence, severe navigation clutter, or major usability blocker.
- **P2:** Substantial efficiency improvement, redundancy removal, or terminology clarification.
- **P3:** Visual refinement, microcopy polish, and non-blocking layout enhancement.
