# Landlord Portal — Recommended Implementation Roadmap

**Execution Philosophy:** Incremental, production-safe, non-breaking stages adhering to strict Hostinger shared hosting invariants.  
**Date:** 2026-10-08  

---

## 1. Phased Implementation Overview

```mermaid
graph LR
    P1[Phase 1: Critical Financial & Tenancy Integrity] --> P2[Phase 2: Operational Workflow Reliability]
    P2 --> P3[Phase 3: Landlord Dashboard Action Center]
    P3 --> P4[Phase 4: Navigation & Mobile UX Streamlining]
    P4 --> P5[Phase 5: Financial Reporting & Polish]
```

---

## 2. Detailed Phase Specifications

### Phase 1: Critical Financial & Tenancy Integrity
- **Primary Objectives:**
  1. Fix the `AnalyticsController` 30x revenue multiplication formula bug.
  2. Fix `LandlordLeaveRequestController` so approved future leave requests do not prematurely terminate active stays today.
  3. Guard `PropertyController@destroy` and `RoomController@destroy` against cascading deletions when active stays or uncompleted onboardings exist.
  4. Hydrate all active tenants in shared room views instead of only the first tenant.
- **Affected Files:**
  - `app/Http/Controllers/AnalyticsController.php`
  - `app/Http/Controllers/LandlordLeaveRequestController.php`
  - `app/Http/Controllers/PropertyController.php`
  - `app/Http/Controllers/RoomController.php`
  - `resources/views/landlord/properties/show.blade.php`
  - `resources/views/landlord/rooms/landlord_index.blade.php`
- **Estimated Complexity:** Small to Medium  
- **Production Risk:** Low (Strictly fixes known mathematical and status bugs)  
- **Verification:** Dedicated PHPUnit test cases in `tests/Feature/LandlordIntegrityFixesTest.php`.

---

### Phase 2: Operational Workflow Reliability
- **Primary Objectives:**
  1. Streamline manual payment recording: auto-create an approved cash payment row when landlord clicks "Mark as Paid".
  2. Invoke `syncAvailabilitySnapshot()` upon maintenance completion.
  3. Introduce unified property filtering across Bookings, Tenants, Onboarding, and Payments.
- **Affected Files:**
  - `app/Http/Controllers/PaymentController.php`
  - `app/Http/Controllers/MaintenanceController.php`
  - `resources/views/landlord/payments/index.blade.php`
  - `resources/views/landlord/tenants/index.blade.php`
  - `resources/views/landlord/bookings/index.blade.php`
- **Estimated Complexity:** Medium  
- **Production Risk:** Low  
- **Verification:** Regression tests for payment reconciliation.

---

### Phase 3: Landlord Dashboard "Needs Attention" Action Center
- **Primary Objectives:**
  1. Add a prioritized daily action card at the top of `/landlord/dashboard`:
     - Pending boarding requests
     - Onboarding documents / payments awaiting landlord review
     - Overdue tenant payments
     - Pending leave requests
     - Expiring compliance documents
  2. Give landlords one single place to resolve daily operational tasks without navigating 5 separate tabs.
- **Affected Files:**
  - `app/Http/Controllers/AuthController.php` (landlordDashboard)
  - `resources/views/landlord/dashboard.blade.php`
- **Estimated Complexity:** Medium  
- **Production Risk:** Low  
- **Verification:** Dashboard metric and deep-link assertion tests.

---

### Phase 4: Navigation & Mobile UX Streamlining
- **Primary Objectives:**
  1. Streamline the 14 sidebar navigation links into 5 intuitive clusters: Overview, Portfolio, Operations, Finance, Account.
  2. Remove excessive nested room accordions in the sidebar to prevent vertical bloating.
  3. Implement responsive card layouts for payments, tenants, and properties on mobile (< 768px).
- **Affected Files:**
  - `resources/views/layouts/landlord.blade.php`
  - `resources/views/landlord/payments/index.blade.php`
  - `resources/views/landlord/tenants/index.blade.php`
  - `resources/views/landlord/properties/index.blade.php`
- **Estimated Complexity:** Medium  
- **Production Risk:** Low  
- **Verification:** `php artisan view:cache` & visual review.

---

### Phase 5: Business Reporting & Analytics Refinement
- **Primary Objectives:**
  1. Refine `/landlord/analytics` into an insightful business performance workspace:
     - Real occupancy rate by bed-slot and room
     - Monthly revenue history based on verified ledger transactions
     - Exportable CSV payment report for tax and bookkeeping records
- **Affected Files:**
  - `app/Http/Controllers/AnalyticsController.php`
  - `resources/views/landlord/analytics/index.blade.php`
- **Estimated Complexity:** Medium  
- **Production Risk:** Low  
- **Verification:** Automated CSV export and calculation tests.
