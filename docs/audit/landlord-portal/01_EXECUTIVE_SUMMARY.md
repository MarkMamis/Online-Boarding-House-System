# OBHS Landlord Portal — Executive Summary

**Project:** Online Boarding House System (OBHS)  
**Host Context:** Hostinger Shared Hosting (Dual Parity with Local Development)  
**Production Status:** LIVE  
**Audit Date:** 2026-10-08  
**Scope:** Full End-to-End Landlord Portal Architecture, Workflows, Financial Integrity, Security, UI/UX, and Performance  

---

## 1. Executive Assessment

The **Online Boarding House System (OBHS)** Landlord Portal provides a functional suite for boarding house operators to list boarding houses, manage room inventories, review student booking requests, onboard tenants with digital contracts, record monthly payments, upload compliance documents, toggle room maintenance, and review student feedback.

However, a comprehensive architectural and operational audit reveals that while individual modules are functional, they operate as loosely coordinated subsystems rather than a cohesive, frictionless operating system. Critical data inconsistencies, financial math anomalies, lifecycle desynchronizations, and usability bottlenecks exist that create operational friction for boarding-house owners.

---

## 2. High-Level Health Ratings

| Domain | Rating | Summary |
| :--- | :--- | :--- |
| **Overall Landlord Portal Health** | **NEEDS ATTENTION** | Strong foundational features, but requires workflow unification and mathematical fixes. |
| **Property & Room Management** | **GOOD WITH GAPS** | Solid multi-property and pricing model support, but cascading deletions and single-tenant assumptions in views need remedy. |
| **Booking & Tenancy Lifecycle** | **NEEDS IMPROVEMENT** | Race-condition mitigation exists in booking approval, but leave request approval prematurely terminates active stays before actual move-out. |
| **Payment & Financial Integrity** | **HIGH RISK** | Critical formula flaw in `AnalyticsController` (multiplying monthly rate by total days), and dual manual/tenant ledger reconciliation gaps. |
| **Documents & Compliance** | **CONSISTENT** | Unified on centralized `LandlordDocumentStatusService` with versioning, expiration tracking, and legacy fallback parity. |
| **Role Isolation & Security** | **STRONG** | Solid policy coverage, route middleware, server-side scoping on landlord properties/bookings/onboardings, and private disk storage authorization. |
| **UI/UX & Design System** | **MODERATE** | Dense navigation with 14 top-level items; desktop layouts functional but mobile experience uses a slide-out offcanvas drawer with small touch targets. |
| **Mobile Usability** | **NEEDS REFINEMENT** | Drawer navigation functional, but dense forms and tables lack optimized mobile-first workflows. |
| **Performance & Scalability** | **GOOD (Shared Hosting Ready)** | No Redis/Supervisor dependencies, standard MySQL pagination, indexed foreign keys, lightweight asset footprint. |
| **Test Coverage** | **PARTIAL** | 229 passing tests overall, but landlord analytics, maintenance state machine, and edge-case capacity races lack dedicated feature test suites. |
| **Production Risk** | **MODERATE** | Existing live operations stable for standard paths, but financial calculations in analytics and leave approval status flips present operational risks. |

---

## 3. Top Critical Findings (P0 / P1)

### 1. [P0 — Financial Calculation Error in Analytics]
- **File:** `app/Http/Controllers/AnalyticsController.php` (Lines 60–62, 72–74)
- **Defect:** Expected and monthly revenue formulas calculate `$booking->room->price * $booking->getDurationInDays()`. Because `price` is a monthly rate (e.g. ₱2,500/month) and `getDurationInDays()` returns total days (e.g. 180 days for 6 months), a 6-month stay is calculated as `₱2,500 * 180 = ₱450,000` instead of `₱2,500 * 6 = ₱15,000` (a 30x calculation error).

### 2. [P1 — Premature Active Stay Termination upon Leave Request Approval]
- **File:** `app/Http/Controllers/LandlordLeaveRequestController.php` (Lines 83–86)
- **Defect:** When a landlord approves a future-dated leave request (e.g., student moving out in 3 weeks), the booking status is immediately set to `cancelled`. This causes `RoomOccupancyRules` and `AuthController@landlordDashboard` to treat the student as gone today, immediately freeing up the slot prematurely and removing the tenant from current boarders before physical move-out.

### 3. [P1 — Destructive Cascade on Property & Room Deletion]
- **Files:** `app/Http/Controllers/PropertyController.php` (Line 755), `RoomController.php` (Line 454)
- **Defect:** Property and Room deletion uses straightforward `$property->delete()` / `$room->delete()` without validating whether active approved bookings, unpaid balances, or active onboardings exist. Foreign keys cascade, permanently deleting historical financial records and active stays.

### 4. [P1 — Single Tenant Assumption in Multi-Capacity Room Views]
- **Files:** `app/Http/Controllers/PropertyController.php` (Lines 781–783), `RoomController.php` (Lines 190–194, 213–216)
- **Defect:** Controllers populate `$room->current_tenant = $room->bookings->first()?->student`. In shared/bed-space rooms with capacity > 1 (e.g., 4 occupants), only the first tenant is surfaced in property and room index tables.

### 5. [P2 — Disconnect between Landlord Maintenance and Student Incidents]
- **File:** `app/Http/Controllers/MaintenanceController.php`
- **Defect:** Maintenance is purely an administrative room status toggle. There is no tenant-facing maintenance ticketing workflow; students must file administrative incident reports instead.

---

## 4. Key Recommendations Summary

1. **Phase 1 (Critical Security & Financial Fixes):**
   - Correct the monthly revenue formula in `AnalyticsController` to use `monthly_rent_amount * months` or aggregate actual `TenantPayment` approved amounts.
   - Fix leave request lifecycle to maintain `approved` status with an upcoming scheduled move-out date, only transitioning to `completed`/`cancelled` on or after the actual leave date.
   - Prevent property/room deletion when active bookings or pending balances exist.
   - Multi-tenant hydration in property/room views.

2. **Phase 2 (Workflow & Operating System Unification):**
   - Introduce a consolidated **Needs Attention** operational action center on the Landlord Dashboard (pending requests, pending onboarding docs, overdue payments, pending leave requests, expiring permits).
   - Unified property filtering across Bookings, Tenants, Onboarding, and Payments.

3. **Phase 3 (Mobile & UI/UX Streamlining):**
   - Refactor mobile navigation and streamline the 14 sidebar items into 5 coherent operational domains: **Overview, Portfolio, Operations, Finance, Account**.

---

## 5. Audit Deliverables Index
- [`02_ROUTE_AND_ARCHITECTURE_MAP.md`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/docs/audit/landlord-portal/02_ROUTE_AND_ARCHITECTURE_MAP.md)
- [`03_FEATURE_RESPONSIBILITY_MATRIX.md`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/docs/audit/landlord-portal/03_FEATURE_RESPONSIBILITY_MATRIX.md)
- [`04_PROPERTY_ROOM_BOOKING_AUDIT.md`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/docs/audit/landlord-portal/04_PROPERTY_ROOM_BOOKING_AUDIT.md)
- [`05_TENANT_ONBOARDING_LIFECYCLE.md`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/docs/audit/landlord-portal/05_TENANT_ONBOARDING_LIFECYCLE.md)
- [`06_PAYMENTS_AND_FINANCIAL_INTEGRITY.md`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/docs/audit/landlord-portal/06_PAYMENTS_AND_FINANCIAL_INTEGRITY.md)
- [`07_MESSAGING_FEEDBACK_MAINTENANCE.md`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/docs/audit/landlord-portal/07_MESSAGING_FEEDBACK_MAINTENANCE.md)
- [`08_DOCUMENTS_COMPLIANCE_NOTIFICATIONS.md`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/docs/audit/landlord-portal/08_DOCUMENTS_COMPLIANCE_NOTIFICATIONS.md)
- [`09_SECURITY_AND_ROLE_ISOLATION.md`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/docs/audit/landlord-portal/09_SECURITY_AND_ROLE_ISOLATION.md)
- [`10_DATABASE_DATA_CONSISTENCY.md`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/docs/audit/landlord-portal/10_DATABASE_DATA_CONSISTENCY.md)
- [`11_UI_UX_AND_MOBILE_AUDIT.md`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/docs/audit/landlord-portal/11_UI_UX_AND_MOBILE_AUDIT.md)
- [`12_PERFORMANCE_AND_SCALABILITY.md`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/docs/audit/landlord-portal/12_PERFORMANCE_AND_SCALABILITY.md)
- [`13_TEST_COVERAGE_AND_EDGE_CASES.md`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/docs/audit/landlord-portal/13_TEST_COVERAGE_AND_EDGE_CASES.md)
- [`14_IMPROVEMENT_BACKLOG.md`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/docs/audit/landlord-portal/14_IMPROVEMENT_BACKLOG.md)
- [`15_RECOMMENDED_IMPLEMENTATION_ROADMAP.md`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/docs/audit/landlord-portal/15_RECOMMENDED_IMPLEMENTATION_ROADMAP.md)
- [`FEATURE_AUDIT_MATRIX.md`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/docs/audit/landlord-portal/FEATURE_AUDIT_MATRIX.md)
- [`END_TO_END_WORKFLOW_MAP.md`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/docs/audit/landlord-portal/END_TO_END_WORKFLOW_MAP.md)
