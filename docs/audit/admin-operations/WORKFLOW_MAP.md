# OBHS Admin Operations — Cross-Page Workflow Map

This document traces the four core lifecycle workflows across the system and maps them directly to the corresponding administrative oversight pages.

---

## 1. Complete Workflow Diagrams

### FLOW A — Landlord Accreditation & Property Lifecycle

```mermaid
flowchart TD
    A[Landlord Registers & Sets Up Profile] --> B[Uploads Business Permit & Safety Certificate]
    B --> C[Queued in Landlord Approvals / Document Verification]
    C --> D{Admin Reviews Documents}
    D -- Rejected --> E[Landlord Receives Reason & Re-uploads]
    E --> C
    D -- Approved --> F[Landlord Account Verified]
    F --> G[Landlord Adds Boarding House & Rooms]
    G --> H[Property Status: Pending Review]
    H --> I[Admin Reviews Property in Boarding Houses Page]
    I -- Approved --> J[Property Listed Publicly for MinSU Students]
    J --> K[Ongoing Monitoring in Boarding Houses & Document Monitoring]
    K --> L{Permit Nearing Expiration <= 30 Days}
    L --> M[Compliance Alert Triggered in Document Monitoring]
    M --> N[Landlord Uploads Renewal]
    N --> C
```

**Admin Page Responsibilities:**
1. Initial Credential Review: `Landlord Approvals` (`/admin/approvals/landlords`) & `Document Verification` (`/admin/documents/verification`).
2. Physical Property Review: `Boarding Houses` (`/admin/properties`).
3. Ongoing Operational Monitoring: `Boarding Houses` (`/admin/properties`).
4. Annual Compliance Expiry & Risk Monitoring: `Document Monitoring` (`/admin/documents/monitoring`).

---

### FLOW B — Student Verification & Housing Tenancy Lifecycle

```mermaid
flowchart TD
    A[Student Registers with @minsu.edu.ph or Valid Email] --> B[Submits School ID / COR Document]
    B --> C[Queued in Student Approvals]
    C --> D{Admin Verifies MinSU Student}
    D -- Rejected --> E[Student Re-uploads Valid Enrollment Proof]
    E --> C
    D -- Approved --> F[Student Verified & Granted Booking Access]
    F --> G[Student Searches & Books Verified Boarding House]
    G --> H[Landlord Approves Booking Request]
    H --> I[Tenant Onboarding Initialized]
    I --> J[Student Submits Emergency Contact & Info]
    J --> K[Digital Tenancy Contract Signed]
    K --> L[Security Deposit / Payment Verified]
    L --> M[Move-In Confirmed: Status Active]
    M --> N[Recorded as Active Boarder in Boarded Students]
    N --> O{Tenancy Ends / Student Moves Out}
    O --> P[Archived into Stay History & Reports]
```

**Admin Page Responsibilities:**
1. Enrollment Verification: `Student Approvals` (`/admin/approvals/students`).
2. Tenancy Pipeline Tracking: `Onboardings` (`/admin/onboardings`).
3. Real-Time Student Residency & Safety Roster: `Boarded Students - Current Boarders` (`/admin/boarding-monitoring/students?tab=current`).
4. Historical Auditing & Institutional Analytics: `Boarded Students - History & Analytics` (`/admin/boarding-monitoring/students?tab=history` / `tab=analytics`).

---

### FLOW C — Document Compliance Lifecycle

```mermaid
flowchart TD
    A[Landlord Uploads Document: Permit/Fire Safety/Sanitary] --> B[Status: Pending Verification]
    B --> C[Appears in Document Review Queue / Verification]
    C --> D{Admin Inspects File}
    D -- Reject with Reason --> E[Status: Rejected -> Landlord Notified]
    E --> F[Landlord Re-submits Corrected File]
    F --> B
    D -- Approve --> G[Status: Approved & Verified]
    G --> H[Document Active in Document Monitoring]
    H --> I{Days to Expiration}
    I -- > 30 Days --> J[Status: Valid / Compliant]
    I -- <= 30 Days --> K[Status: Expiring Soon -> Flagged in Monitoring]
    I -- Past Expiration Date --> L[Status: Expired -> Risk Alert Triggered]
    L --> M[Landlord Re-uploads Renewal]
    M --> B
```

**Admin Page Responsibilities:**
1. Review & Verification Queue: `Document Verification` (`/admin/documents/verification`).
2. Compliance & Expiry Monitoring: `Document Monitoring` (`/admin/documents/monitoring`).

---

### FLOW D — Physical Property & Room Occupancy Lifecycle

```mermaid
flowchart TD
    A[Property Created with Room Capacities] --> B[Status: Pending Approval]
    B --> C[Admin Inspects Location, Photos, Room Count]
    C --> D{Admin Decision}
    D -- Reject --> E[Landlord Edits Listing]
    E --> B
    D -- Approve --> F[Status: Approved & Active]
    F --> G[Public Listing Visible on Map & Search]
    G --> H[Students Move In via Completed Onboardings]
    H --> I[Occupancy Rate Automatically Increments]
    I --> J{Capacity Reached?}
    J -- Slots Available --> K[Status: Available (Green)]
    J -- 100% Full --> L[Status: Full / No Vacancy (Red)]
```

**Admin Page Responsibilities:**
1. Property Approval & Oversight: `Boarding Houses` (`/admin/properties`).
2. Bed Capacity & Spatial Mapping: `Boarding Houses` (`/admin/properties`).
3. Resident Occupancy Reconciliation: `Boarded Students` (`/admin/boarding-monitoring/students`).
