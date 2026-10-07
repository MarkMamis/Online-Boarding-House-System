# OBHS Admin Portal — Information Architecture & Responsibility Model

This document establishes the official Information Architecture (IA) and domain responsibility separation for the Admin Portal of the Online Boarding House System (OBHS) at Mindoro State University (MinSU).

---

## 1. Core Product Philosophy

The Admin Portal separates concerns strictly into two operational modes:

| Operational Mode | Core Purpose | Administrator Intent | Interaction Style |
| :--- | :--- | :--- | :--- |
| **MONITORING** | "What is happening?" | Observational oversight, university census, physical capacity tracking, lifecycle compliance health. | Calm, data-driven, graphs, maps, statistics, history, filters, read-only/low-risk interactions. |
| **MANAGEMENT** | "What requires my action?" | Active review queues, eligibility vetting, accreditation, document verification, approval/rejection decisions. | Action-oriented, review queues, verification drawers, structured decision forms, mandatory reasons, audit trails. |

---

## 2. Navigation Structure

```text
ADMIN PORTAL
│
├── OVERVIEW
│   └── Dashboard (/admin/dashboard)
│
├── MONITORING
│   ├── Boarding Houses (/admin/properties)
│   ├── Active Boarders (/admin/boarding-monitoring/students)
│   ├── Onboarding Pipeline (/admin/onboardings)
│   └── Compliance Monitor (/admin/documents/monitoring)
│
├── MANAGEMENT
│   ├── Property Reviews (/admin/property-reviews)
│   ├── Student Verification (/admin/approvals/students)
│   ├── Landlord Accreditation (/admin/approvals/landlords)
│   └── Document Review Queue (/admin/documents/verification)
│
├── INSIGHTS
│   ├── Analytics (/admin/analytics)
│   └── Institutional Reports (/admin/institutional-reports)
│
└── SYSTEM
    ├── User Accounts (/admin/users)
    └── Notifications (/notifications)
```

---

## 3. Detailed Page Responsibility Specifications

### OVERVIEW

#### Dashboard
- **Route:** `GET /admin/dashboard` (`admin.dashboard`)
- **Purpose:** Central high-level executive pulse of the boarding ecosystem.
- **Primary Question:** *"What is the state of off-campus housing today and what items require attention?"*
- **Primary Data:** Total properties, active resident count, overall bed occupancy, attention summary queue badges.
- **Primary Actions:** High-level navigation and deep links into specific Monitoring and Management destinations.

---

### MONITORING

#### 1. Boarding Houses
- **Route:** `GET /admin/properties` (`admin.properties.index`)
- **Purpose:** Operational property inventory and spatial capacity monitoring.
- **Primary Question:** *"Which boarding houses are registered, where are they located, and what is their room/slot availability?"*
- **Primary Data:** Total properties, approved properties, bed capacity vs available slots, occupancy rates, interactive map markers.
- **Primary Actions:** Search, filter by location/availability, switch List/Map view, inspect property details.
- **Actions Explicitly NOT Owned:** Approve/Reject property (owned by *Property Reviews* in Management).

#### 2. Active Boarders
- **Route:** `GET /admin/boarding-monitoring/students` (`admin.boarding_monitoring.students`)
- **Purpose:** Real-time student residency census and institutional safety roster.
- **Primary Question:** *"Which MinSU students are currently living in off-campus housing and what are the demographic trends?"*
- **Primary Data:** Active confirmed stays (`check_in <= now() <= check_out`), room assignments, landlord contacts, college/program distributions, trend analytics.
- **Primary Actions:** Filter by college/program/house, date-basis filtering, print official institutional housing roster.
- **Actions Explicitly NOT Owned:** Student ID verification (owned by *Student Verification* in Management).

#### 3. Onboarding Pipeline
- **Route:** `GET /admin/onboardings` (`admin.onboardings.index`)
- **Purpose:** Pre-move-in tenancy transition and pipeline monitoring.
- **Primary Question:** *"Are approved student bookings moving smoothly through digital lease signing and deposit verification?"*
- **Primary Data:** Onboarding stage breakdown (`pending_documents`, `contract_pending`, `deposit_pending`, `move_in_ready`, `completed`), emergency contacts, contract signing status.
- **Primary Actions:** Search by student/house, view signed contract preview, download official tenancy contract PDF.
- **Actions Explicitly NOT Owned:** Landlord-tenant contract countersignature or deposit disputes.

#### 4. Compliance Monitor
- **Route:** `GET /admin/documents/monitoring` (`admin.documents.monitoring`)
- **Purpose:** Legal and physical safety compliance lifecycle tracking.
- **Primary Question:** *"Which boarding houses have valid, expiring (<= 30 days), expired, or missing annual permits?"*
- **Primary Data:** Mayor's / Business permit validity, Fire Safety Inspection Certificate status, Sanitary Permits, BIR documents, expiration timelines.
- **Primary Actions:** Filter by compliance status (`valid`, `expiring_soon`, `expired`, `missing`), search by landlord/house, link to *Document Review Queue*.
- **Actions Explicitly NOT Owned:** Document approval/rejection (owned by *Document Review Queue* in Management).

---

### MANAGEMENT

#### 1. Property Reviews
- **Route:** `GET /admin/property-reviews` (`admin.property_reviews.index`)
- **Purpose:** Review boarding house listing submissions requiring administrative approval.
- **Primary Question:** *"Which newly submitted boarding houses need inspection and approval before they are published to students?"*
- **Primary Data:** Pending property list, photos, room breakdown, amenities, landlord accreditation status, submission date.
- **Primary Actions:** Inspect property details, Approve property (`POST /admin/properties/{property}/approve`), Reject property (`POST /admin/properties/{property}/reject` with mandatory reason).
- **Monitoring Counterpart:** *Boarding Houses* (`/admin/properties`).

#### 2. Student Verification
- **Route:** `GET /admin/approvals/students` (`admin.approvals.students`)
- **Purpose:** Verify student identity and MinSU enrollment credentials.
- **Primary Question:** *"Which student applicants have submitted valid School IDs or CORs to unlock booking privileges?"*
- **Primary Data:** Student Name, Student ID Number, College, Degree Program, School ID image/PDF preview.
- **Primary Actions:** Secure private document preview, Approve student verification, Reject student verification with reason.
- **Monitoring Counterpart:** *Active Boarders* (`/admin/boarding-monitoring/students`).

#### 3. Landlord Accreditation
- **Route:** `GET /admin/approvals/landlords` (`admin.approvals.landlords`)
- **Purpose:** Verify landlord legitimacy and initial business eligibility.
- **Primary Question:** *"Which boarding house operators are applying for accreditation to list properties on OBHS?"*
- **Primary Data:** Landlord contact details, business permit preview, safety certificate preview, submission history.
- **Primary Actions:** Approve/Reject Business Permit, Approve/Reject Safety Certificate.
- **Monitoring Counterpart:** *Compliance Monitor* (`/admin/documents/monitoring`).

#### 4. Document Review Queue
- **Route:** `GET /admin/documents/verification` (`admin.documents.verification`)
- **Purpose:** Action inbox for pending compliance documents and annual renewals.
- **Primary Question:** *"What submitted compliance documents are waiting for an administrative decision right now?"*
- **Primary Data:** Pending document submissions across all types (Mayor's Permit, Fire Safety, Sanitary, Clearances), landlord metadata, expiration dates.
- **Primary Actions:** Secure private inline preview, Approve document, Reject document with mandatory explanation.
- **Monitoring Counterpart:** *Compliance Monitor* (`/admin/documents/monitoring`).

---

## 4. Route Preservation & Backward Compatibility

All legacy routes remain functional:
- `/admin/properties` -> Boarding Houses (Monitoring)
- `/admin/properties/approval` & `admin.properties.pending` -> Redirects to `/admin/property-reviews` (Management)
- `/admin/permits` -> Redirects to `/admin/approvals/landlords` (Management)
- `/admin/student-verifications` -> Redirects to `/admin/approvals/students` (Management)
- `/admin/boarded-students` -> Redirects to `/admin/boarding-monitoring/students` (Monitoring)
- `/admin/analytics?report_preset=...` -> Redirects to `/admin/institutional-reports?report_preset=...` (Insights)
- `/admin/boarding-monitoring/students?tab=analytics` -> Redirects to `/admin/analytics` (Insights)
- `/admin/analytics/export` -> Streams CSV via `AdminReportingService` with full preset support
