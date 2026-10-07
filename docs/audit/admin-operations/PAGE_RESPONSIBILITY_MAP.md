# OBHS Admin Operations — Page & Responsibility Map

This document defines the existing implementation, security boundaries, queries, connected workflows, and recommended responsibilities for all seven administrative pages.

---

## Page 1: Boarding Houses (`/admin/properties`)

- **Route:** `GET /admin/properties` (`admin.properties`)
- **Middleware:** `auth`, `role:admin`
- **Controller & Method:** `AuthController@adminProperties`
- **Blade View:** `resources/views/admin/properties.blade.php`
- **Primary Admin Question Answered:**
  - *"What boarding houses exist, which ones are operating, what is their capacity/occupancy, and which new properties need review?"*
- **Data Source & Query:**
  - `Property::with(['landlord', 'rooms'])->paginate(15)` with dynamic query filters (`status`, `availability`, `location`, `search`).
  - Aggregates: Total properties, Approved properties, Pending review, Total Capacity (`rooms->sum('capacity')`), Available Capacity (`sum('available_capacity')`).
- **Available Actions:**
  - Search properties (name, address, landlord name/email).
  - Filter by Approval Status (`all`, `approved`, `pending`, `rejected`, `inactive`).
  - Filter by Availability (`all`, `available`, `full`).
  - Filter by Location / Barangay.
  - Switch between List View and Interactive Map View.
  - Review Modal / Action: Inspect property (`PropertyController@adminInspect`), Approve property (`PropertyController@adminApprove`), Reject property (`PropertyController@adminReject`).
- **Connected Workflows:**
  - Landlord Property Registration -> Admin Property Review -> Student Listing Discovery -> Student Booking.
- **Related Pages:**
  - `Landlord Approvals` (`/admin/approvals/landlords`)
  - `Document Verification` (`/admin/documents/verification`)
  - `Document Monitoring` (`/admin/documents/monitoring`)
- **Security Boundary:**
  - Admin-only access. Server-side CSRF and POST validation on `adminApprove` and `adminReject`.
- **Current Responsibility:**
  - Dual: Acts as both a property inventory monitor AND an approval decision queue.
- **Recommended Responsibility:**
  - **Operational Property Monitoring**: Focus on boarding house health, capacity, resident student counts, map visualization, and compliance status. Move direct approval action queues to dedicated Review/Approvals workflows or keep seamless non-conflicting links.

---

## Page 2: Boarded Students (`/admin/boarding-monitoring/students`)

- **Route:** `GET /admin/boarding-monitoring/students` (`admin.boarding-monitoring.students`)
- **Middleware:** `auth`, `role:admin`
- **Controller & Method:** `AdminBookingController@boardedStudents`
- **Blade View:** `resources/views/admin/boarding-monitoring/students.blade.php`
- **Primary Admin Question Answered:**
  - *"Which MinSU students are currently living in off-campus boarding houses, where are they staying, what is the historical boarding track record, and what are university-wide demographic/geographic trends?"*
- **Data Source & Query:**
  - Tab 1 (`current`): `Booking::whereIn('status', ['confirmed', 'active'])->whereDate('check_in', '<=', now())->where(fn($q) => $q->whereNull('check_out')->orWhereDate('check_out', '>=', now()))->with(['student', 'property.landlord', 'room'])`.
  - Tab 2 (`history`): `Booking::whereIn('status', ['completed', 'cancelled', 'rejected'])->orWhereDate('check_out', '<', now())->with(['student', 'property.landlord', 'room'])`.
  - Tab 3 (`analytics`): `AdminBoardingAnalyticsService` calculating unique boarders, college distribution, program breakdown, gender representation, stay longevity, and boarding house concentration.
- **Available Actions:**
  - Tab navigation (`current`, `history`, `analytics`).
  - Search by student name, student ID, boarding house, room, landlord.
  - Filter by Boarding House, College, Academic Program, Year Level, Date Range.
  - Print / Export official university boarding roster (`/admin/boarding-monitoring/students/print`).
- **Connected Workflows:**
  - Completed Onboarding -> Active Stay / Tenancy -> Move-out / Completion -> Historical Roster & Institutional Safety Auditing.
- **Related Pages:**
  - `Onboardings` (`/admin/onboardings`)
  - `Student Approvals` (`/admin/approvals/students`)
- **Security Boundary:**
  - Admin-only. All student records scoped university-wide. Export sanitized of unnecessary PII while preserving safety audit data.
- **Current Responsibility:**
  - Pure Monitoring and Reporting: Authoritative university-level census of off-campus student housing.
- **Recommended Responsibility:**
  - Retain current pure-monitoring model. Keep as the primary institutional student safety and residency directory.

---

## Page 3: Onboardings (`/admin/onboardings`)

- **Route:** `GET /admin/onboardings` (`admin.onboardings.index`)
- **Middleware:** `auth`, `role:admin`
- **Controller & Method:** `TenantOnboardingController@adminIndex`
- **Blade View:** `resources/views/admin/onboardings/index.blade.php` (and `show.blade.php`)
- **Primary Admin Question Answered:**
  - *"Are approved student bookings successfully transitioning through digital contracts, safety agreements, and deposits into active tenancies, or are students getting stuck?"*
- **Data Source & Query:**
  - `TenantOnboarding::with(['booking.student', 'booking.property.landlord', 'booking.room'])->latest()->paginate(15)`.
  - Status counts: `pending` (`pending_documents`, `pending_review`, `contract_pending`), `active` (`move_in_ready`), `completed` (`completed`), `cancelled` (`cancelled`).
- **Available Actions:**
  - Filter by stage tabs (`All`, `Pending Review`, `Active Onboardings`, `Completed`).
  - Filter by Property, Landlord, Search term.
  - View Onboarding Details Modal/Page (`TenantOnboardingController@adminShow`).
  - View Signed Tenancy Contract (`TenantOnboardingController@viewContract`).
  - Download Official Contract PDF (`TenantOnboardingController@downloadContractPdf`).
- **Connected Workflows:**
  - Landlord Approves Booking -> TenantOnboarding Initialized -> Student Submits Emergency Contacts -> Contract Signed -> Deposit Verified -> Admin Oversight -> Active Stay.
- **Related Pages:**
  - `Boarded Students` (`/admin/boarding-monitoring/students`)
  - `Boarding Houses` (`/admin/properties`)
- **Security Boundary:**
  - Admin has read-only oversight; cannot forge signatures or tamper with landlord-student financial negotiations. Private contract PDFs served with strict authorization checks.
- **Current Responsibility:**
  - Tenancy Transition Monitoring & Dispute Oversight: Provides visibility into the pre-move-in pipeline.
- **Recommended Responsibility:**
  - Maintain as transition monitoring. Add bottleneck indicators (e.g., "Awaiting Landlord Signature > 5 days") to help admin identify stalled tenancies without manually executing steps.

---

## Page 4: Student Approvals (`/admin/approvals/students`)

- **Route:** `GET /admin/approvals/students` (`admin.approvals.students`)
- **Middleware:** `auth`, `role:admin`
- **Controller & Method:** `AuthController@adminStudentApprovals`
- **Blade View:** `resources/views/admin/approvals/students.blade.php`
- **Primary Admin Question Answered:**
  - *"Which newly registered students are waiting for institutional identity and enrollment verification before they can book off-campus housing?"*
- **Data Source & Query:**
  - `User::where('role', 'student')->where('school_id_verification_status', 'pending')->with('studentProfile')->paginate(15)`.
  - Status aggregates for pending, verified, rejected students.
- **Available Actions:**
  - View School ID Document / Certificate of Registration (via secure private viewer).
  - Search by Student Name, MinSU Student ID Number, Email, College, Program.
  - Approve Student Verification (`POST /admin/approvals/students/{id}/approve`).
  - Reject Student Verification (`POST /admin/approvals/students/{id}/reject` with rejection reason).
- **Connected Workflows:**
  - Student Registration -> Student Profile Setup & ID Upload -> Admin Approval -> Student Unlocked to Submit Bookings.
- **Related Pages:**
  - `Boarded Students` (`/admin/boarding-monitoring/students`)
  - `Document Verification` (`/admin/documents/verification`)
- **Security Boundary:**
  - Admin-only execution. Server-side validation, rejection reason requirement, state mutation on `users.school_id_verification_status`, and audit logging timestamp/reviewer ID.
- **Current Responsibility:**
  - Student Account & Academic Identity Verification Queue.
- **Recommended Responsibility:**
  - Student Eligibility Queue: Explicitly verify student identity, MinSU enrollment status, and student credentials.

---

## Page 5: Landlord Approvals (`/admin/approvals/landlords`)

- **Route:** `GET /admin/approvals/landlords` (`admin.approvals.landlords`)
- **Middleware:** `auth`, `role:admin`
- **Controller & Method:** `AuthController@adminLandlordApprovals`
- **Blade View:** `resources/views/admin/approvals/landlords.blade.php`
- **Primary Admin Question Answered:**
  - *"Which boarding house owners/landlords have submitted their core business permits and safety certificates to become verified operators?"*
- **Data Source & Query:**
  - `LandlordProfile::with('user')->paginate(15)` with filter tabs (`pending`, `verified`, `rejected`, `all`).
- **Available Actions:**
  - Inspect Mayor's / Business Permit image/PDF.
  - Inspect Fire Safety / Sanitary Certificate image/PDF.
  - Approve Business Permit (`POST /admin/approvals/landlords/{id}/approve-permit`).
  - Reject Business Permit (`POST /admin/approvals/landlords/{id}/reject-permit`).
  - Approve Safety Certificate (`POST /admin/approvals/landlords/{id}/approve-safety`).
  - Reject Safety Certificate (`POST /admin/approvals/landlords/{id}/reject-safety`).
- **Connected Workflows:**
  - Landlord Registration -> Profile & Document Upload -> Admin Verification -> Landlord Verified -> Property Listing Enabled.
- **Related Pages:**
  - `Document Verification` (`/admin/documents/verification`)
  - `Document Monitoring` (`/admin/documents/monitoring`)
  - `Boarding Houses` (`/admin/properties`)
- **Security Boundary:**
  - Admin-only execution. State changes update both legacy `landlord_profiles` status fields and corresponding `landlord_documents` records.
- **Current Responsibility:**
  - Landlord Eligibility & Primary Credential Review.
- **Recommended Responsibility:**
  - Landlord Eligibility Verification: Confirm landlord identity, business legitimacy, and initial accreditation.

---

## Page 6: Document Verification (`/admin/documents/verification`)

- **Route:** `GET /admin/documents/verification` (`admin.documents.verification`)
- **Middleware:** `auth`, `role:admin`
- **Controller & Method:** `AdminLandlordDocumentController@verification`
- **Blade View:** `resources/views/admin/documents/verification.blade.php`
- **Primary Admin Question Answered:**
  - *"What individual compliance documents, annual permits, renewals, and certificates are currently queued for inspection across all landlords?"*
- **Data Source & Query:**
  - `LandlordDocument::where('verification_status', 'pending')->with(['landlordProfile.user'])->latest()->paginate(15)`.
  - Also incorporates pending documents from `landlord_profiles` via reconciliation logic.
- **Available Actions:**
  - Filter by Document Type (`business_permit`, `safety_certificate`, `bir_registration`, `fire_safety_inspection`, `sanitary_permit`, `occupancy_permit`, `mayors_permit`, `barangay_clearance`, `police_clearance`).
  - Filter by Landlord / Boarding House.
  - Secure Preview document inline in modal (`/files/private/{path}`).
  - Secure Download document (`/files/private/{path}?download=1`).
  - Approve Document (`POST /admin/documents/{id}/approve`).
  - Reject Document (`POST /admin/documents/{id}/reject` with mandatory reason).
- **Connected Workflows:**
  - Landlord Uploads Annual Compliance / Replacement Document -> Queued in Document Verification -> Admin Verifies -> Document Marked Valid -> Compliance Monitoring Updated.
- **Related Pages:**
  - `Landlord Approvals` (`/admin/approvals/landlords`)
  - `Document Monitoring` (`/admin/documents/monitoring`)
- **Security Boundary:**
  - Strict admin authentication. Reject requires non-empty reason. File viewing protected by `FileViewerController`.
- **Current Responsibility:**
  - Action-Oriented Document Review Queue (Document-Centric).
- **Recommended Responsibility:**
  - **Compliance Review Queue**: The single inbox for all pending compliance submissions and renewals.

---

## Page 7: Document Monitoring (`/admin/documents/monitoring`)

- **Route:** `GET /admin/documents/monitoring` (`admin.documents.monitoring`)
- **Middleware:** `auth`, `role:admin`
- **Controller & Method:** `AdminLandlordDocumentController@monitoring`
- **Blade View:** `resources/views/admin/documents/monitoring.blade.php`
- **Primary Admin Question Answered:**
  - *"What is the current compliance health of all boarding houses? Which permits are expiring within 30/60/90 days, which are already expired, and which landlords have missing compliance documents?"*
- **Data Source & Query:**
  - `LandlordDocument::with(['landlordProfile.user'])->paginate(20)`.
  - Lifecycle states computed: `expiring_soon` (`<= 30 days`), `expired` (`< now()`), `valid` (`> 30 days`), `missing` (mandatory documents not yet uploaded by active landlords).
  - Summary KPI cards: Total Uploaded, Valid & Compliant, Expiring Soon, Expired, Missing.
- **Available Actions:**
  - Filter by Compliance Health (`all`, `valid`, `expiring_soon`, `expired`, `missing`).
  - Filter by Document Type, Landlord, Date Range.
  - Search by Landlord Name, Business Name, Document Title.
  - Secure View / Download file.
  - Send renewal notification / compliance alert to landlord.
- **Connected Workflows:**
  - Approved Document -> Normal Operations -> 30-Day Warning Period -> Expiration -> Notification to Landlord -> Re-submission into Verification Queue.
- **Related Pages:**
  - `Document Verification` (`/admin/documents/verification`)
  - `Boarding Houses` (`/admin/properties`)
- **Security Boundary:**
  - Read-only monitoring with notification trigger capability.
- **Current Responsibility:**
  - Lifecycle Compliance & Expiry Monitoring.
- **Recommended Responsibility:**
  - **Compliance Lifecycle & Expiry Oversight**: Retain and strengthen proactive risk flags (e.g. "Expired Fire Safety on Property with 24 Students").
