# OBHS Admin Operations — Improvement Backlog

This backlog categorizes actionable enhancements, refactoring targets, and UI refinements identified during the full administrative operations audit.

---

## 1. Improvement Items by Domain

### Domain: Boarding Houses (`/admin/properties`)

- **ID:** `ADM-PROP-001`
- **Page:** Boarding Houses
- **Type:** UI/UX & Information Architecture
- **Severity:** Medium
- **Priority:** P2
- **Problem:** Direct property approval modals inside the operational monitoring table add visual clutter and create cognitive confusion between daily inventory monitoring and pending review decisions.
- **Why It Matters:** School administrators reviewing active housing capacity do not want action modals crowding the operational census table.
- **Recommended Change:** Separate pending property reviews into an intuitive review drawer or dedicated pending queue banner while keeping the main page focused on capacity, occupancy, and spatial map analytics.
- **Affected Files:** `resources/views/admin/properties.blade.php`, `app/Http/Controllers/AuthController.php`, `app/Http/Controllers/PropertyController.php`
- **Risk:** Low
- **Required Agents:** `obhs-admin`, `obhs-ui`
- **Required Tests:** `tests/Feature/AdminPropertiesTest.php`

---

### Domain: Boarded Students (`/admin/boarding-monitoring/students`)

- **ID:** `ADM-BOARD-001`
- **Page:** Boarded Students
- **Type:** Export & Reporting
- **Severity:** Low
- **Priority:** P2
- **Problem:** Print template (`/admin/boarding-monitoring/students/print`) generates basic HTML tables without official university headers or digital audit metadata.
- **Why It Matters:** MinSU safety officers and Student Affairs administrators need formal, branded institutional documentation during official campus housing audits.
- **Recommended Change:** Enhance the print/PDF view with university seal placeholder, semester/academic year filters, signing authority block, and generated-at timestamp.
- **Affected Files:** `resources/views/admin/boarding-monitoring/print-students.blade.php`, `app/Http/Controllers/AdminBookingController.php`
- **Risk:** Low
- **Required Agents:** `obhs-admin`, `obhs-ui`
- **Required Tests:** `tests/Feature/AdminBoardingMonitoringTest.php`

---

### Domain: Onboardings (`/admin/onboardings`)

- **ID:** `ADM-ONB-001`
- **Page:** Onboardings
- **Type:** Monitoring & Alerting
- **Severity:** Medium
- **Priority:** P2
- **Problem:** The page lists onboardings chronologically but does not flag stalled cases (e.g., student waiting on landlord lease countersignature for > 7 days).
- **Why It Matters:** The admin needs to quickly spot bottlenecks in student move-ins before the academic semester begins.
- **Recommended Change:** Add visual SLA urgency badges ("Stalled > 5 Days", "Deposit Pending Confirmation", "Ready for Move-In") and quick landlord reminder triggers.
- **Affected Files:** `resources/views/admin/onboardings/index.blade.php`, `app/Http/Controllers/TenantOnboardingController.php`
- **Risk:** Low
- **Required Agents:** `obhs-admin`, `obhs-booking-tenancy`, `obhs-ui`
- **Required Tests:** `tests/Feature/TenantOnboardingTest.php`

---

### Domain: Student Approvals (`/admin/approvals/students`)

- **ID:** `ADM-STUAPP-001`
- **Page:** Student Approvals
- **Type:** UI/UX Refinement
- **Severity:** Low
- **Priority:** P2
- **Problem:** The student approval table uses legacy card containers and basic form buttons that do not align with the modern OBHS design system.
- **Why It Matters:** Modernizing the review card interface accelerates administrative throughput when processing hundreds of student ID uploads at the start of the semester.
- **Recommended Change:** Redesign into a sleek review queue with inline zoomable document inspection modal, batch approval options, and clear rejection preset reasons.
- **Affected Files:** `resources/views/admin/approvals/students.blade.php`, `app/Http/Controllers/AuthController.php`
- **Risk:** Low
- **Required Agents:** `obhs-admin`, `obhs-ui`, `obhs-student`
- **Required Tests:** `tests/Feature/StudentVerificationTest.php`

---

### Domain: Landlord Approvals (`/admin/approvals/landlords`)

- **ID:** `ADM-LNDAPP-001`
- **Page:** Landlord Approvals
- **Type:** Workflow Clarity & Information Architecture
- **Severity:** High
- **Priority:** P1
- **Problem:** Dual review pathways exist between `Landlord Approvals` (updating `landlord_profiles`) and `Document Verification` (updating `landlord_documents`), creating confusion on where a new landlord's credentials should be verified.
- **Why It Matters:** Administrators may verify a document on one page while missing related property inspection requirements on another.
- **Recommended Change:** Reframe `Landlord Approvals` explicitly as **Landlord Initial Accreditation**, presenting a unified view of landlord identity, contact vetting, and core permits before granting listing rights.
- **Affected Files:** `resources/views/admin/approvals/landlords.blade.php`, `app/Http/Controllers/AuthController.php`, `app/Http/Controllers/AdminLandlordDocumentController.php`
- **Risk:** Medium
- **Required Agents:** `obhs-admin`, `obhs-landlord`, `obhs-ui`
- **Required Tests:** `tests/Feature/LandlordDocumentTest.php`

---

### Domain: Document Verification (`/admin/documents/verification`)

- **ID:** `ADM-DOCVER-001`
- **Page:** Document Verification
- **Type:** Usability & Efficiency
- **Severity:** Medium
- **Priority:** P1
- **Problem:** The verification queue lacks fast-filtering by submission date range and batch inspection features.
- **Why It Matters:** During peak compliance renewal season (e.g. January business permit renewal), administrators process dozens of documents daily.
- **Recommended Change:** Introduce clear category groupings (Annual Permits, Safety Inspections, Clearances), quick rejection reason presets, and unified preview sidebars.
- **Affected Files:** `resources/views/admin/documents/verification.blade.php`, `app/Http/Controllers/AdminLandlordDocumentController.php`
- **Risk:** Low
- **Required Agents:** `obhs-admin`, `obhs-documents-storage`, `obhs-ui`
- **Required Tests:** `tests/Feature/AdminDocumentVerificationFixesTest.php`

---

### Domain: Document Monitoring (`/admin/documents/monitoring`)

- **ID:** `ADM-DOCMON-001`
- **Page:** Document Monitoring
- **Type:** Proactive Compliance & Alerting
- **Severity:** Medium
- **Priority:** P2
- **Problem:** Expired or missing document states require the admin to manually copy landlord emails rather than triggering built-in automated compliance alerts.
- **Why It Matters:** Timely notifications ensure boarding houses renew fire safety and sanitary permits without administrative overhead.
- **Recommended Change:** Add a "Send Compliance Reminder" action button next to expiring/expired records and summarize compliance status directly on the property details view.
- **Affected Files:** `resources/views/admin/documents/monitoring.blade.php`, `app/Http/Controllers/AdminLandlordDocumentController.php`
- **Risk:** Low
- **Required Agents:** `obhs-admin`, `obhs-documents-storage`, `obhs-ui`
- **Required Tests:** `tests/Feature/LandlordDocumentTest.php`
