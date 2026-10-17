# Redundancy, Feature Responsibility & Cross-Portal Boundary Separation

**Audit Date:** October 18, 2026  
**Scope:** Demarcation of System Responsibilities across Admin, Landlord, and Student/Tenant Portals  

---

## 1. The Core Principle of Three Portals

In a multi-stakeholder higher-education housing platform, each portal has an unambiguous, distinct mission:

| Portal | Primary Audience | Core Mission | Mental Model |
|---|---|---|---|
| **Admin Portal** | University Housing Board / Student Affairs Staff | Institutional compliance, boarding house accreditation, governance, and dispute resolution | "Oversight & Compliance" |
| **Landlord Portal** | Boarding House Owners & Property Managers | Property listing, vacancy tracking, booking approval, contract management, and rent verification | "Property Business Operations" |
| **Student / Tenant Portal** | MinSU Students & Active Boarders | Room discovery, booking reservation, move-in preparation, resident living, and stay management | "Customer Experience & Resident Living" |

---

## 2. Cross-Portal UI Leakage Identified in Student Portal

During the audit, multiple administrative and landlord patterns were identified as having "leaked" into the Student Portal:

### Leak 1: Macro Operational KPIs on Student Home
- **Found in:** `student/dashboard.blade.php` (KPI tiles: *"Available Rooms: 14"*, *"Active Boarding Requests"*).
- **Origin:** Copied from `admin/dashboard.blade.php` and `landlord/dashboard.blade.php`.
- **Why it is wrong:** Students are consumers. Showing an aggregate metric of all vacant rooms across the city serves an administrative auditor, not a college student seeking a bed near their campus.

### Leak 2: Operational Data Tables for Simple Tasks
- **Found in:** Leave requests, payment history, and booking requests views.
- **Origin:** Copied from Landlord operational management tables.
- **Why it is wrong:** Landlords need dense, multi-column tabular data to process 50 tenants. A student only has ONE active stay and 1–3 past requests. Forcing students into cramped desktop data tables on smartphones destroys mobile readability.

### Leak 3: Internal NLP Sentiment Labels
- **Found in:** `student/tenant-dashboard.blade.php` (*"Positive sentiment: 2"*).
- **Origin:** University Admin Accreditation Analytics.
- **Why it is wrong:** A resident should never see algorithmic classification tags of their own feedback.

---

## 3. Clear Responsibility Demarcation Matrix

| Feature / Domain | Student / Tenant Responsibility | Landlord Responsibility | Admin Responsibility |
|---|---|---|---|
| **Room Discovery** | Search, filter, view photos, compare prices | Manage photos, update vacancies & prices | Accredit & approve property for public listing |
| **Booking Reservation** | Select dates, choose mode (Solo/Shared), submit request | Approve or reject reservation request | Audit booking volume and student placement |
| **Tenant Onboarding** | Upload COR/ID, sign lease, upload deposit slip | Review docs, counter-sign lease, verify deposit | Monitor lease completion & dispute resolution |
| **Active Residence** | View roommates, access house rules, submit leave | Manage maintenance, enforce house rules, log leave | Campus welfare monitoring & inspection |
| **Rent & Billing** | View due dates, upload monthly proof of payment | Approve rent payments, manage billing channels | Financial compliance & accreditation audits |
| **Communication** | Inquire with landlord, chat regarding room/stay | Answer student inquiries, send house announcements | Platform messaging oversight (if flagged) |
| **Feedback & Reviews** | Submit star ratings & comments upon stay completion | View feedback to improve accommodation quality | Review sentiment & accreditation ratings |
| **Incidents & Safety** | Submit confidential reports regarding misconduct or safety | Respond to tenant grievances internally | Investigate safety violations, inspect property |
