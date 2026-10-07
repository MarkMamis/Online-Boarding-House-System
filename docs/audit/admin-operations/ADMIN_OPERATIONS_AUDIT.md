# OBHS Admin Operations — Comprehensive Audit Report

**System:** Online Boarding House System (OBHS) — Mindoro State University (MinSU)  
**Environment:** Live Production (Hostinger Shared Web Hosting / MySQL 8.x / PHP 8.2+)  
**Audit Scope:** 7 Core Admin Operations Pages (Monitoring, Approvals, Compliance)  
**Auditor Roles:** Principal Product Architect, Senior Laravel Engineer, Senior Product Designer, Application Security Engineer, Data/Analytics Engineer, and QA Lead  
**Date:** October 8, 2026  

---

## 1. Executive Summary

An exhaustive, non-destructive architectural, logical, security, and UI/UX audit was conducted across all seven primary administrative operations pages of the OBHS platform:
1. **Boarding Houses** (`/admin/properties`)
2. **Boarded Students** (`/admin/boarding-monitoring/students`)
3. **Onboardings** (`/admin/onboardings`)
4. **Student Approvals** (`/admin/approvals/students`)
5. **Landlord Approvals** (`/admin/approvals/landlords`)
6. **Document Verification** (`/admin/documents/verification`)
7. **Document Monitoring** (`/admin/documents/monitoring`)

### Overall System Assessment
- **Admin Operations Health:** `GOOD WITH GAPS` (Solid business foundations, highly capable analytics engine, robust security boundaries, but suffering from operational overlap in approvals and legacy UI density).
- **Security & Authorization Risk:** `LOW` (All admin endpoints strictly enforce server-side role checks `role:admin`; private files are gated behind authorized streaming controllers; no IDOR vulnerabilities discovered).
- **Workflow Clarity:** `MOSTLY CLEAR` (Core student housing lifecycles are functionally complete; minor ambiguity between landlord approval and document verification queues).
- **UI/UX Consistency:** `MODERATE` (Modernized visual tokens exist in analytics and dashboard; however, table layouts, filters, and modal dialogues across older approval pages retain legacy Bootstrap-like density).

---

## 2. Page-by-Page Findings

### Page 1: Boarding Houses (`/admin/properties`)
- **Current Role:** Displays all registered boarding houses, bed capacity statistics, occupancy rates, and interactive Leaflet map markers. It also doubles as a property approval queue with inspect/approve/reject modals.
- **Key Findings:**
  1. *Capacity vs Slots:* Correctly sums `Room::capacity` across properties. It differentiates total rooms from total bed capacity.
  2. *Occupancy Calculation:* Occupancy is accurately derived from active confirmed bookings (`check_in <= now() <= check_out`).
  3. *Approval Context:* The page handles property approval (`Property::approval_status`), which is distinct from landlord account accreditation. A landlord can be verified, but their newly added 2nd property remains `pending` until approved.
  4. *Privacy / PII:* List view displays landlord name, email, and property address. Personal phone numbers and financial details are appropriately excluded from the main table.
  5. *Map Parity:* The interactive map dynamically renders markers based on the exact same filtered query parameters as the table view.
- **Recommended Evolution:** Transition this page into a pure **Property Operational Monitoring & Safety Console**. Relegate direct approval actions to clean secondary links or dedicated review drawers.

---

### Page 2: Boarded Students (`/admin/boarding-monitoring/students`)
- **Current Role:** University-level census of all MinSU students living off-campus. Spans 3 functional tabs: `Current Boarders` (active residency), `Stay History & Reports` (historical records), and `Analytics` (comprehensive demographic/institutional graphs and drill-down tables).
- **Key Findings:**
  1. *Definition of Boarded Student:* Strictly governed by `Booking::status IN ('confirmed', 'active')` with active temporal overlap (`check_in <= now() <= check_out`). This is authoritative and prevents premature bookings or departed students from inflating active numbers.
  2. *Analytics Engine:* Backed by `AdminBoardingAnalyticsService` utilizing strict SQL aggregations and distinct student counting (`COUNT(DISTINCT bookings.user_id)`).
  3. *PII Balance:* The table provides necessary student identification (Student ID, Full Name, College, Program, Emergency Contact) while hiding sensitive passwords and raw billing data.
  4. *Print & Export:* The dedicated print route (`/admin/boarding-monitoring/students/print`) generates clean institutional housing rosters for MinSU safety audits.
- **Recommended Evolution:** Retain as the gold-standard student safety directory. Maintain the current clean visualization hierarchy (Filters -> KPI Summary -> Trend Charts -> Breakdown Tables).

---

### Page 3: Onboardings (`/admin/onboardings`)
- **Current Role:** Pre-move-in tenancy transition monitor. Tracks students who have approved bookings through document submission, digital contract signing, deposit verification, and move-in readiness.
- **Key Findings:**
  1. *Admin Boundary:* Admin has read-only supervisory visibility (`adminIndex`, `adminShow`, contract PDF downloads) and cannot tamper with landlord-student agreements.
  2. *State Progression:* Clear linear pipeline: `pending_documents` -> `pending_review` -> `contract_pending` -> `contract_signed` -> `deposit_pending` -> `deposit_verified` -> `move_in_ready` -> `completed`.
  3. *Bottleneck Visibility:* Identifies where a student is stuck in the pre-arrival pipeline.
- **Recommended Evolution:** Add proactive SLA alert badges (e.g., *"Contract unsigned > 5 days"*, *"Deposit unverified > 48h"*) to help administrators follow up with landlords without manual calculation.

---

### Page 4: Student Approvals (`/admin/approvals/students`)
- **Current Role:** Queue for verifying MinSU student enrollment credentials (School ID image and Certificate of Registration).
- **Key Findings:**
  1. *Semantics:* Verifies student identity and MinSU student status (`users.school_id_verification_status`).
  2. *Actions & Rejection:* POST endpoints require non-empty rejection reasons. Rejection notifies the student and allows re-uploading without account lockouts.
  3. *Audit Trail:* Stores `school_id_reviewed_at` and `school_id_reviewed_by`.
  4. *Document Inspection:* Uses secure inline private document streaming (`FileViewerController`).
- **Recommended Evolution:** Keep as the dedicated student eligibility inbox. Update the UI from legacy card tables to a modern, calm review queue.

---

### Page 5: Landlord Approvals (`/admin/approvals/landlords`)
- **Current Role:** Initial accreditation queue for boarding house operators. Verifies Mayor's / Business Permit and Fire Safety Inspection Certificate.
- **Key Findings:**
  1. *Dual Action Endpoints:* Offers separate approve/reject endpoints for Business Permit (`approve-permit`, `reject-permit`) and Safety Certificate (`approve-safety`, `reject-safety`).
  2. *Status Synchronization:* Automatically updates `landlord_profiles` and synchronizes corresponding records in `landlord_documents`.
  3. *Overlap with Document Verification:* Because business permits are also listed in `Document Verification`, there is conceptual duplication.
- **Recommended Evolution:** Position this page strictly as **Landlord Initial Accreditation** (onboarding review). Cross-link renewals to the Document Review Queue.

---

### Page 6: Document Verification (`/admin/documents/verification`)
- **Current Role:** Action-oriented review queue for all compliance documents uploaded by landlords (`business_permit`, `safety_certificate`, `bir_registration`, `fire_safety_inspection`, `sanitary_permit`, `occupancy_permit`, `mayors_permit`, `barangay_clearance`, `police_clearance`).
- **Key Findings:**
  1. *Action Inbox:* Lists only documents with `verification_status = 'pending'`.
  2. *Security:* Modal previews use `/files/private/{path}`. Direct file downloads enforce attachment content disposition.
  3. *Rejection Flow:* Rejection requires a reason and moves status to `rejected`, allowing landlord resubmission.
- **Recommended Evolution:** Standardize as the single universal **Compliance Review Queue** for all annual permits, renewals, and certificates.

---

### Page 7: Document Monitoring (`/admin/documents/monitoring`)
- **Current Role:** Lifecycle compliance monitor. Categorizes all approved landlord documents into temporal states: `Valid`, `Expiring Soon` (<= 30 days), `Expired`, and `Missing`.
- **Key Findings:**
  1. *Proactive Risk Management:* Accurately identifies boarding houses operating with expired fire safety or business permits.
  2. *Reconciliation Logic:* Correctly computes missing mandatory documents for active properties.
  3. *Clear Distinction:* Unlike Document Verification (an inbox for pending items), Document Monitoring is a strategic census of legal and physical safety compliance.
- **Recommended Evolution:** Enhance with one-click "Send Expiration Notice" batch triggers and link directly to property risk badges.

---

## 3. Cross-Page Workflow Integrity

| Workflow Stage | Initiating Entity | Admin Oversight Page | State Mutation / Action |
| :--- | :--- | :--- | :--- |
| **Student Registration** | Student | `Student Approvals` (`/admin/approvals/students`) | Verify School ID -> Unlocks booking access |
| **Landlord Registration** | Landlord | `Landlord Approvals` (`/admin/approvals/landlords`) | Verify Mayor's Permit & Safety Cert -> Accredit landlord |
| **Property Submission** | Landlord | `Boarding Houses` (`/admin/properties`) | Inspect photos, rooms, capacity -> Approve listing |
| **Lease & Contract** | Student & Landlord | `Onboardings` (`/admin/onboardings`) | Monitor signed contract, deposit, move-in status |
| **Active Residency** | Student Move-In | `Boarded Students` (`/admin/boarding-monitoring/students`) | Real-time student safety directory and census |
| **Annual Permit Renewal**| Landlord | `Document Verification` (`/admin/documents/verification`) | Review and approve renewed certificates |
| **Safety Oversight** | System / Admin | `Document Monitoring` (`/admin/documents/monitoring`) | Flag expired permits & notify operators |

---

## 4. Security & Authorization Analysis

1. **Role Enforcement:** All routes pass through `['auth', 'role:admin']`. Tests verify that unauthenticated guests and authenticated students/landlords receive `403 Forbidden` or redirects.
2. **File Authorization:** Document and ID images are stored in private storage (`storage/app/private/` and `storage/app/documents/`). Access is gated through `FileViewerController@showPrivateFile` verifying admin role or resource ownership.
3. **HTTP Methods & CSRF:** All state changes (approvals, rejections, status toggles) strictly utilize `POST` or `PATCH` with `@csrf` token validation. No destructive `GET` routes exist.
4. **Data Minimization:** Admin index tables hide unnecessary PII (passwords, complete billing logs, raw hashes) while retaining essential safety contact information.

---

## 5. Strict MySQL & Data Accuracy

1. **Group By & Aggregates:** All analytical and grouping queries in `AdminBoardingAnalyticsService` and `AdminBookingController` strictly conform to MySQL 8.x `ONLY_FULL_GROUP_BY` standards. No non-aggregated columns are selected in grouped queries.
2. **Distinct Counts:** Boarding queries utilize `COUNT(DISTINCT bookings.user_id)` and `COUNT(DISTINCT properties.id)` to prevent duplicate row counts caused by room and landlord table joins.
3. **Zero-Data State Handling:** Controllers gracefully handle zero-data conditions (e.g. empty periods, brand new colleges) using `COALESCE` and default collection fallback arrays.

---

## 6. UI/UX Analysis for Non-Technical Administrators

- **Strengths:**
  - Modernized visual hierarchy in `Boarded Students (Analytics)` and `Admin Dashboard`.
  - Intuitive color-coded status badges (`green` = active/valid, `yellow` = pending/expiring, `red` = rejected/expired).
  - Clear filtering controls with search term retention across pagination links.
- **Weaknesses / Areas for Improvement:**
  - `Boarding Houses` and `Landlord Approvals` retain older Bootstrap-style modal dialogs and dense action columns.
  - Absence of empty-state illustrations or helpful guidance text on zero-result filter queries.
  - Action buttons on tables are occasionally crowded (e.g. Inspect, Approve, Reject in a single row).

---

## 7. Duplication Findings & Page Merging Evaluation

### 1. Landlord Approvals vs Document Verification
- *Duplication Type:* Confusing Functional Duplication.
- *Analysis:* Both pages review business permits and safety certificates.
- *Recommendation:* Keep `Landlord Approvals` focused on initial operator accreditation (who is the landlord, are they registered?), while `Document Verification` handles the continuous operational stream of annual renewals and compliance certificates.

### 2. Boarded Students vs Onboardings
- *Duplication Type:* Good Contextual Differentiation.
- *Analysis:* `Onboardings` monitors the *pre-residency transition pipeline*, while `Boarded Students` monitors *active university residents*. They answer different questions and should remain separate.

### 3. Document Verification vs Document Monitoring
- *Duplication Type:* Good Functional Separation.
- *Analysis:* Verification is an **Action Queue** (Pending items); Monitoring is a **Census / Audit Lifecycle** (Valid, Expiring, Expired, Missing). Both are essential.

---

## 8. Summary Audit Scores

| Page | Purpose Clarity | Data Accuracy | Security | Workflow Integrity | UX Score (1-5) | Performance | Admin Monitoring Value | Overall Risk |
| :--- | :---: | :---: | :---: | :---: | :---: | :---: | :---: | :---: |
| **Boarding Houses** | High | Excellent | High | Strong | 3.5 / 5 | Fast | Critical | Low |
| **Boarded Students** | High | Excellent | High | Strong | 4.5 / 5 | Fast | Critical | Low |
| **Onboardings** | High | Excellent | High | Strong | 3.8 / 5 | Fast | High | Low |
| **Student Approvals** | High | Excellent | High | Strong | 3.6 / 5 | Fast | High | Low |
| **Landlord Approvals** | Medium | Good | High | Moderate | 3.2 / 5 | Fast | High | Medium |
| **Document Verification**| High | Excellent | High | Strong | 3.7 / 5 | Fast | High | Low |
| **Document Monitoring**| High | Excellent | High | Strong | 4.0 / 5 | Fast | Critical | Low |
