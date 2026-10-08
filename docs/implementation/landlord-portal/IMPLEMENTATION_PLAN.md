# Landlord Portal Implementation Plan
## Critical Integrity Fixes & Monitoring / Management Separation

**Repository:** Online Boarding House System (OBHS)  
**Date:** 2026-10-08  
**Baseline Commit:** `67abb6e142c2520655021d593c4426aa3b120104`  
**Status:** In Progress  

---

## 1. Executive Summary & Objective

The Landlord Portal is the operational backbone for boarding house owners managing properties, rooms, tenant onboarding, bookings, rent payments, and compliance. This plan executes a two-phase structural and operational improvement:

- **Phase 1: Critical Data Integrity Fixes**
  1. Correct financial revenue analytics calculations in `AnalyticsController.php`.
  2. Fix future-dated leave request approval in `LandlordLeaveRequestController.php` so room occupancy and active stays are preserved until scheduled departure.
  3. Enforce property and room deletion safeguards in `PropertyController.php` and `RoomController.php` to prevent cascading damage to active tenants and bookings.
  4. Fix shared-room tenant visibility across room listings and property detail views so multi-bed occupants are fully visible.

- **Phase 2: Landlord Information Architecture (Monitoring & Management Separation)**
  Reorganize landlord navigation and workflows into 6 distinct functional categories:
  - **Overview:** Dashboard with a "Needs Attention" queue.
  - **Monitoring:** Property & Room Status (read-only), Tenants & Stays (read-only lifecycle visibility), Compliance Status (read-only accreditation & permit tracking).
  - **Management:** Properties, Room Directory, Boarding Requests, Tenant Onboarding, Rent & Payments, Leave Requests, Room Maintenance, Document Renewals.
  - **Communication:** Messages.
  - **Insights:** Business Analytics, Feedback & Ratings.
  - **Account:** Profile, Logout.

---

## 2. Architecture & File Ownership Map

| Module / Area | Controllers / Services | Primary Views | Domain / Protocol Reference |
| :--- | :--- | :--- | :--- |
| **Financial Analytics** | `app/Http/Controllers/AnalyticsController.php` | `resources/views/landlord/analytics/index.blade.php` | `docs/audit/landlord-portal/06_PAYMENTS_AND_FINANCIAL_INTEGRITY.md` |
| **Leave Requests** | `app/Http/Controllers/LandlordLeaveRequestController.php` | `resources/views/landlord/leave_requests/index.blade.php` | `docs/audit/landlord-portal/05_TENANT_ONBOARDING_LIFECYCLE.md` |
| **Properties & Rooms (Safeguards & Visibility)** | `app/Http/Controllers/PropertyController.php`, `app/Http/Controllers/RoomController.php` | `resources/views/landlord/properties/show.blade.php`, `resources/views/landlord/rooms/landlord_index.blade.php`, `resources/views/landlord/rooms/index.blade.php` | `docs/audit/landlord-portal/04_PROPERTY_ROOM_BOOKING_AUDIT.md` |
| **Monitoring Hub** | `app/Http/Controllers/LandlordMonitoringController.php` | `resources/views/landlord/monitoring/properties.blade.php`, `resources/views/landlord/monitoring/compliance.blade.php` | `docs/audit/landlord-portal/03_FEATURE_RESPONSIBILITY_MATRIX.md` |
| **Navigation & Layout** | `resources/views/layouts/landlord.blade.php` | Sidebar, Offcanvas, Mobile Drawer | `docs/audit/landlord-portal/11_UI_UX_AND_MOBILE_AUDIT.md` |
| **Routes** | `routes/web.php` | All `/landlord/*` routes | `docs/harness/ROLE_ACCESS_MATRIX.md` |

---

## 3. Implementation Phases & Milestones

### Phase 1: Critical Integrity Fixes
- [x] Fix revenue formula in `AnalyticsController.php` (Monthly Collected Revenue = verified approved `TenantPayment` records in 30d; Expected Revenue = active approved monthly rents).
- [x] Fix future-dated leave approvals in `LandlordLeaveRequestController.php` (adjust `check_out` date, keep `approved` until move-out date; only complete/cancel if departure is today or past).
- [x] Add deletion blocks in `PropertyController@destroy` and `RoomController@destroy` for active approved stays or pending requests.
- [x] Hydrate `$room->active_tenants` collection in `PropertyController@show` and `RoomController` methods, rendering all occupants in Blade views.
- [x] Write `tests/Feature/LandlordIntegrityFixesTest.php` to verify all 4 areas.
- [x] Document in `docs/implementation/landlord-portal/INTEGRITY_FIXES.md` and create local commit `fix: harden landlord financial and tenancy workflows`.

### Phase 2: Monitoring & Management Separation
- [x] Create `LandlordMonitoringController.php` with `properties()` and `compliance()` actions.
- [x] Register routes:
  - `GET /landlord/monitoring/properties` (`landlord.monitoring.properties`)
  - `GET /landlord/monitoring/compliance` (`landlord.monitoring.compliance`)
  - Keep `/landlord/tenants` as the primary Tenants & Stays monitoring entrypoint (`landlord.tenants.index`).
- [x] Build blade views `resources/views/landlord/monitoring/properties.blade.php` and `resources/views/landlord/monitoring/compliance.blade.php`.
- [x] Reorganize `resources/views/layouts/landlord.blade.php` into the 6 standard sections (Overview, Monitoring, Management, Communication, Insights, Account).
- [x] Refine `resources/views/landlord/dashboard.blade.php` with a compact "Needs Attention" queue.
- [x] Write `tests/Feature/LandlordMonitoringManagementTest.php`.
- [x] Document matrix, metric definitions, and route compatibility.
- [x] Create local commit `feat: separate landlord monitoring and management`.

---

## 4. Verification & Validation Strategy

1. **Unit & Feature Regression Tests:** `php artisan test`
2. **Route Registry Verification:** `php artisan route:list`
3. **Blade Compilation Check:** `php artisan view:cache`
4. **Vite Production Asset Build:** `npm run build`
5. **Security & Authorization Review:** Verify strict server-side scoping on all landlord data (`where('landlord_id', Auth::id())`).
