# OBHS Admin Operations — Information Architecture Audit

This document examines the existing navigation hierarchy, analyzes overlaps and conceptual ambiguities, and establishes the recommended Information Architecture (IA) tailored for a non-technical school administrator.

---

## 1. Current Navigation Hierarchy

Currently, the admin sidebar groups operations into two primary clusters:

```text
ADMIN CONSOLE
├── DASHBOARD (/admin/dashboard)
├── MONITORING
│   ├── Boarding Houses (/admin/properties)
│   ├── Boarded Students (/admin/boarding-monitoring/students)
│   └── Onboardings (/admin/onboardings)
├── APPROVALS & COMPLIANCE
│   ├── Student Approvals (/admin/approvals/students)
│   ├── Landlord Approvals (/admin/approvals/landlords)
│   ├── Document Verification (/admin/documents/verification)
│   └── Document Monitoring (/admin/documents/monitoring)
├── OPERATIONS
│   ├── Bookings (/admin/bookings)
│   ├── Reports / Incidents (/admin/reports)
│   └── Chatbot / Analytics (/admin/analytics)
```

---

## 2. Analysis of Current Ambiguities & Overlaps

### A. "Approval" vs "Verification" vs "Monitoring"
- **Landlord Approvals vs Document Verification:**
  - In `Landlord Approvals` (`/admin/approvals/landlords`), the administrator reviews and approves/rejects a landlord's Business Permit and Safety Certificate.
  - In `Document Verification` (`/admin/documents/verification`), the administrator reviews and approves/rejects the exact same documents (plus additional compliance types like Fire Safety, BIR, Sanitary Permit).
  - *Risk:* A school administrator does not know whether to look at Landlord Approvals or Document Verification to approve a permit.
- **Document Verification vs Document Monitoring:**
  - `Document Verification` is an **Inbox/Action Queue** ("What documents are waiting for my approval right now?").
  - `Document Monitoring` is a **Lifecycle/Compliance Census** ("What is the overall permit health across all boarding houses? Which permits expire next month?").
  - *Observation:* These two pages serve distinct purposes, but their names (`documents/verification` vs `documents/monitoring`) under the same header cause initial hesitation for non-technical users.
- **Boarded Students vs Onboardings:**
  - `Boarded Students` answers: *"Who is currently living in off-campus housing today?"* (Active Tenancy).
  - `Onboardings` answers: *"Who is currently in the contract/deposit/move-in pipeline?"* (Pre-Tenancy Transition).
  - *Observation:* Both are categorized under "MONITORING", which is logical, but the distinction between "Pipeline" and "Active Residents" needs clearer framing.
- **Boarding Houses (Properties) vs Landlord Approvals:**
  - `/admin/properties` displays property inventory, capacity, and maps, but also includes an "Approve / Reject Property" action modal.
  - Landlord account eligibility and Property listing eligibility are closely tied in real life, but currently split across different screens without cross-linking.

---

## 3. Recommended Information Architecture

To ensure clarity for school personnel, administrative workflows should be structured around three clear mental models:

1. **MONITORING & SAFETY (Oversight / Census / Health)** — "How is the ecosystem doing right now?"
2. **APPROVAL QUEUES (Action Inboxes / Eligibility)** — "What applications or entities need my decision today?"
3. **COMPLIANCE & PERMITS (Regulatory / Expirations)** — "Are boarding houses legally and physically compliant with university/LGU standards?"

### Recommended Hierarchy:

```text
ADMIN CONSOLE
├── OVERVIEW
│   └── Dashboard (/admin/dashboard)
│
├── MONITORING & CENSUS
│   ├── Boarding Houses (/admin/properties)
│   │   └── Subtitle: Operational inventory, capacity, and geographic distribution
│   ├── Active Boarders (/admin/boarding-monitoring/students)
│   │   └── Subtitle: University student housing census, roster, and demographics
│   └── Tenancy Pipeline (/admin/onboardings)
│       └── Subtitle: Contract, deposit, and move-in transition tracker
│
├── APPROVAL QUEUES (Action Inboxes)
│   ├── Student Verification (/admin/approvals/students)
│   │   └── Subtitle: Student MinSU ID, enrollment status, and account eligibility
│   └── Landlord Accreditation (/admin/approvals/landlords)
│       └── Subtitle: Landlord onboarding, business legitimacy, and initial accreditation
│
├── COMPLIANCE & SAFETY
│   ├── Document Review Queue (/admin/documents/verification)
│   │   └── Subtitle: Action queue for pending annual permits and certificate renewals
│   └── Compliance Lifecycle (/admin/documents/monitoring)
│       └── Subtitle: Permit expiration tracker, compliance audit, and risk flags
```

---

## 4. Why This Solves the Administrator's Cognitive Burden

| Question the Admin Asks | Page to Open Under Recommended IA | Why It Is Unambiguous |
| :--- | :--- | :--- |
| "A student just registered and says their account is locked." | **Student Verification** (`/admin/approvals/students`) | Dedicated queue for verifying MinSU student status. |
| "A landlord submitted their initial application to join OBHS." | **Landlord Accreditation** (`/admin/approvals/landlords`) | Initial boarding house operator vetting. |
| "A landlord uploaded their renewed 2026 Fire Safety Inspection." | **Document Review Queue** (`/admin/documents/verification`) | Action inbox for all incoming document submissions. |
| "Are any boarding houses operating with expired permits?" | **Compliance Lifecycle** (`/admin/documents/monitoring`) | Proactive safety and regulatory risk monitoring. |
| "Where is student Juan Dela Cruz currently boarding?" | **Active Boarders** (`/admin/boarding-monitoring/students`) | Authoritative student residence directory. |
| "How many total bed slots are available in Barangay Masipit?" | **Boarding Houses** (`/admin/properties`) | Physical capacity and geographic mapping. |
| "Did a student who booked a room finish signing their lease?" | **Tenancy Pipeline** (`/admin/onboardings`) | Pre-occupancy contract tracking. |
