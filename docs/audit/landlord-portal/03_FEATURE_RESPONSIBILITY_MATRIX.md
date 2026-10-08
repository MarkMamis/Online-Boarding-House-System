# Landlord Portal — Feature Responsibility & Module Matrix

**Project:** OBHS Landlord Portal  
**Audit Scope:** Module Ownership, Duplications, Overlaps, and Boundaries  
**Date:** 2026-10-08  

---

## 1. Module Responsibility Breakdown

| Module / Feature | Primary Purpose | Authoritative Source of Truth | Connected Student Perspective | Connected Admin Perspective | Classification |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **Dashboard** | Daily operational pulse & attention checklist. | Aggregated from `properties`, `bookings`, `rooms`, `landlord_documents`, `messages`. | Student Dashboard (browse/book). | Admin Dashboard (institutional oversight). | **JUSTIFIED** |
| **Documents & Requirements** | Submit & track regulatory permits (Business Permit, Safety Cert). | `landlord_documents` table (with legacy `landlord_profiles` sync). | N/A | Admin Landlord Accreditation & Document Review Queue. | **JUSTIFIED** |
| **Properties** | Register, edit, geocode, and view boarding houses. | `properties` table. | Marketplace property card & details. | Admin Boarding Houses & Property Reviews. | **JUSTIFIED** |
| **Room Directory** | Inventory of rooms, capacity, pricing models, inclusions. | `rooms` & `room_images` tables. | Room browsing & booking modal. | Admin Property detail & Room Inspector. | **JUSTIFIED** |
| **Boarding Requests** | Review student booking requests (approve/reject). | `bookings` table (`status: pending`). | Student "My Requests" tab. | Admin Bookings Monitoring Queue. | **JUSTIFIED** |
| **Messages** | Direct communication with prospective & active tenants. | `messages` table. | Student Messages tab. | Admin Direct Messaging. | **JUSTIFIED** |
| **Feedback** | View verified ratings and AI sentiment from stayers. | `room_feedbacks` table. | Student Room feedback submission. | Public property rating aggregates. | **JUSTIFIED** |
| **Tenants** | List of all approved boarders currently or historically assigned. | `bookings` (`status: approved`). | Student Active Tenancy dashboard. | Admin Boarding Monitoring (Boarded Students). | **JUSTIFIED** |
| **Onboarding** | Digital contract signing & initial deposit verification. | `tenant_onboardings` table. | Student Tenant Onboarding wizard. | Admin Tenant Onboardings pipeline. | **JUSTIFIED** |
| **Leave Requests** | Review student early departure requests. | `leave_requests` table. | Student Leave Request form. | Boarding Monitoring Stay History. | **JUSTIFIED** |
| **Maintenance** | Mark rooms as under maintenance/available. | `rooms.status` (`maintenance` / `available`). | N/A (Students see room unavailable). | Property Review status checks. | **REDUNDANT / CONFUSING** |
| **Payments** | Track monthly rent, send GCash reminders, log manual pay. | `tenant_payments` table & `bookings` billing columns. | Student Monthly Payments tab. | Institutional Reports (Financials). | **JUSTIFIED** |
| **Analytics** | Historical booking trends, revenue & occupancy. | `bookings`, `rooms`, `properties`. | N/A | Admin Analytics & Institutional Reports. | **JUSTIFIED (Needs Math Fix)** |
| **Notifications** | Alerts for new bookings, payments, permits, messages. | `notifications` table (`notifiable_id = landlord_id`). | Student notifications. | Admin notifications. | **JUSTIFIED** |
| **Profile** | Landlord contact, about, e-signature, billing settings. | `users` & `landlord_profiles` tables. | Landlord badge on listing. | Admin Landlord profile details. | **JUSTIFIED** |

---

## 2. Analysis of Overlapping Responsibilities

### A. Dashboard vs. Analytics
- **Finding:** The Dashboard displays active tenants, recent check-ins, occupancy breakdown per property, payment watchlist, and a 6-month tenant trend graph. The separate `Analytics` page shows a redundant 30-day revenue figure (with broken math), total occupancy %, and a 7-day booking count.
- **Classification:** **CONFUSING / REDUNDANT**
- **Recommendation:** Keep Dashboard focused strictly on **"What needs my attention today"** (pending approvals, overdue rents, active tenancies). Keep `Analytics` focused on **historical business performance and financial trend reporting** once formulas are corrected.

### B. Boarding Requests vs. Onboarding vs. Tenants
- **Finding:** 
  - A student submits a booking -> appears in `Boarding Requests` (`pending`).
  - Landlord approves -> creates a `tenant_onboardings` record and moves booking to `approved`.
  - The booking immediately appears in `Tenants` even if onboarding has not started!
  - Meanwhile, the onboarding steps (contract signing, deposit payment) must be tracked in `Onboarding`.
- **Classification:** **CONFUSING WORKFLOW BOUNDARY**
- **Recommendation:** Distinctly tag tenants in the `Tenants` view with their onboarding lifecycle badge (`Onboarding Pending`, `Contract Signed`, `Deposit Paid`, `Active Tenant`).

### C. Maintenance vs. Incident Reports
- **Finding:**
  - Landlord `Maintenance` is only a manual toggle on room status.
  - Students cannot submit maintenance requests to their landlord; they can only submit `Incident Reports` which go directly to the MinSU School Admin.
- **Classification:** **INCONSISTENT / GAPPED WORKFLOW**
- **Recommendation:** Future roadmap should allow tenants to file routine maintenance issues directly to landlords without escalating to university administration as an incident report.
