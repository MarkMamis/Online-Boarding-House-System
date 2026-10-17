# OBHS Landlord Portal: Prioritized Implementation Roadmap

**Document ID:** `15_IMPLEMENTATION_ROADMAP.md`  
**Date:** October 18, 2026  
**Auditor:** Senior Laravel Architect, QA Lead & Product Strategist  
**Strategy:** Staged, Regression-Proof, Low-Risk Progressive Enhancement.

---

## Roadmap Overview & Dependency Chain

To ensure zero downtime on Hostinger Shared Hosting, modifications must follow a disciplined 7-phase sequence. No phase breaks existing database schemas or introduces risky dependencies.

```
Phase 1: Nav & Home ──► Phase 2: Property/Rooms ──► Phase 3: Onboarding
                                                            │
Phase 6: Analytics  ◄── Phase 5: Operations    ◄── Phase 4: Payments
         │
         ▼
Phase 7: Mobile Polish & Accessibility QA
```

---

### Phase 1: Navigation Streamlining & Action-Oriented Home
- **Goal:** Eliminate sidebar visual bloat, remove redundant module cards, and transform the dashboard into an actionable daily queue.
- **Problems Addressed:** 14 root links, dynamic room accordion inflation, 8 duplicate cards on dashboard, lack of operational priority.
- **Pages Affected:** `layouts/landlord.blade.php`, `landlord/dashboard.blade.php`.
- **Files Likely Affected:**
  - `resources/views/layouts/landlord.blade.php`
  - `resources/views/landlord/dashboard.blade.php`
  - `app/Http/Controllers/AuthController.php` (`landlordDashboard`)
- **UX Changes:**
  - Collapse sidebar into 5 primary groups; remove dynamic room loop from global nav.
  - Replace 60KB dashboard clutter with "Needs Your Attention Today" priority cards.
  - Prune redundant "Quick Modules" grid and "Recommended Rooms" section.
- **Backend Changes:** Aggregate real pending counts for the action queue; remove unnecessary dashboard modal logic.
- **Data Migration Risk:** None (zero schema change).
- **Complexity:** Low | **Risk:** Very Low | **Expected Benefit:** Immediate 80% reduction in cognitive fatigue.

---

### Phase 2: Property & Room Setup Streamlining
- **Goal:** Drastically lower the barrier to registering a boarding house and adding rooms.
- **Problems Addressed:** 4-step property wizard with mandatory first room, loss of progress on exit (no drafts), repetitive data entry for 10 similar rooms.
- **Pages Affected:** `/landlord/properties/create`, `/landlord/properties/{id}`, `/landlord/rooms/create`.
- **Files Likely Affected:**
  - `app/Http/Controllers/PropertyController.php`
  - `app/Http/Controllers/RoomController.php`
  - `resources/views/landlord/properties/create.blade.php`
  - `resources/views/landlord/properties/show.blade.php`
  - `resources/views/landlord/rooms/create.blade.php`
- **UX Changes:**
  - Decouple room creation from property registration.
  - Add 1-click `[ ⧉ Duplicate Room ]` action on room cards.
  - Add session-based draft restoration on property create form.
- **Backend Changes:**
  - Relax `Rule::requiredIf` on initial room fields in `PropertyController@store`.
  - Implement `duplicate(Property $property, Room $room)` method in `RoomController`.
- **Data Migration Risk:** None.
- **Complexity:** Moderate | **Risk:** Low | **Expected Benefit:** Multi-room setup time reduced from 25 mins to 2 mins.

---

### Phase 3: Unified Move-In Checklist & Tenant Separation
- **Goal:** Make student onboarding transparent, sequential, and separated from active boarders.
- **Problems Addressed:** Approved applicants showing up prematurely as active tenants; 5-page bouncing to complete one onboarding.
- **Pages Affected:** `/landlord/onboarding`, `/landlord/onboarding/{id}/review`, `/landlord/tenants`.
- **Files Likely Affected:**
  - `app/Http/Controllers/BookingController.php` (`landlordTenants`)
  - `app/Http/Controllers/TenantOnboardingController.php`
  - `resources/views/landlord/onboarding/index.blade.php`
  - `resources/views/landlord/onboarding/review.blade.php`
  - `resources/views/landlord/tenants/index.blade.php`
- **UX Changes:**
  - Single Move-In Progress card clearly displaying: Complete vs Missing vs Next Action.
  - Filter `/landlord/tenants` to only display boarders whose onboarding is 100% completed.
- **Backend Changes:** Update `BookingController@landlordTenants` query to check onboarding completion.
- **Data Migration Risk:** None.
- **Complexity:** Moderate | **Risk:** Low | **Expected Benefit:** Zero confusion over who is actually living in the house.

---

### Phase 4: Unified Payments Center & Ledger Synchronization
- **Goal:** Consolidate Move-In deposits and monthly rent confirmation into one consistent financial command center.
- **Problems Addressed:** Double-counting of advance rent, missing `tenant_payments` rows on manual mark-paid, disjoint confirmation screens.
- **Pages Affected:** `/landlord/payments`, `/landlord/payments/{booking}/manage`.
- **Files Likely Affected:**
  - `app/Http/Controllers/PaymentController.php`
  - `resources/views/landlord/payments/index.blade.php`
  - `resources/views/landlord/payments/manage_monthly.blade.php`
- **UX Changes:**
  - Single Payments Center with 3 tabs: Rent Confirmations, Move-In Dues, Tenant Billing Roster.
  - Shared payment proof inspection modal with zoomable receipt viewer.
- **Backend Changes:**
  - Ensure `PaymentController@markAsPaid` atomically writes a verified `tenant_payments` row.
  - Ensure advance rent collected during onboarding credits Month 1 rent without double-counting.
- **Data Migration Risk:** None.
- **Complexity:** High | **Risk:** Moderate | **Expected Benefit:** Guaranteed ledger accuracy and seamless payment approvals.

---

### Phase 5: Operations & Communication Polish
- **Goal:** Clarify room maintenance toggling, student leave requests, and mobile chat.
- **Problems Addressed:** Misleading "Track repairs" label on room maintenance toggle; awkward message reply forms on dashboard.
- **Pages Affected:** `/landlord/maintenance`, `/landlord/leave-requests`, `/landlord/messages`.
- **Files Likely Affected:**
  - `resources/views/landlord/maintenance/index.blade.php`
  - `resources/views/landlord/leave_requests/index.blade.php`
  - `resources/views/landlord/messages/index.blade.php`
- **UX Changes:**
  - Relabel Maintenance to "Room Availability & Maintenance Mode".
  - Clear explanation on Leave Request approval (future departure vs immediate release).
- **Backend Changes:** Minor microcopy and route link updates.
- **Data Migration Risk:** None.
- **Complexity:** Low | **Risk:** Very Low | **Expected Benefit:** Clear expectations around resident departures and room status.

---

### Phase 6: Analytics & Monitoring Precision
- **Goal:** Correct revenue and occupancy formulas; remove empty or misleading graphs.
- **Problems Addressed:** Revenue ignoring onboarding payments, expected rent multiplying flat room price, bedspace occupancy undercounted.
- **Pages Affected:** `/landlord/analytics`.
- **Files Likely Affected:**
  - `app/Http/Controllers/AnalyticsController.php`
  - `resources/views/landlord/analytics/index.blade.php`
- **UX Changes:** Clean 4-KPI summary (Average Occupancy, Total Cash Collected, Payment Rate, Tenant Rating) + 6-month cash collection bar chart.
- **Backend Changes:** Align analytics aggregation queries with strict MySQL standards and actual booking rates.
- **Data Migration Risk:** None.
- **Complexity:** Moderate | **Risk:** Low | **Expected Benefit:** 100% trustworthy business numbers for boarding house owners.

---

### Phase 7: Responsive Polish, Accessibility & Final QA
- **Goal:** Ensure flawless mobile experience at 320px–430px and 100% test suite regression protection.
- **Problems Addressed:** Table horizontal overflows, small touch targets, modal clipping.
- **Files Likely Affected:**
  - `resources/css/` or `layouts/landlord.blade.php` (CSS media queries)
  - `tests/Feature/LandlordPortalRefinementTest.php`
- **Actions:**
  - Full automated feature regression tests covering all 56 landlord endpoints.
  - Verification of asset compilation (`npm run build`) and view caching (`php artisan view:cache`).
- **Complexity:** Low | **Risk:** Very Low | **Expected Benefit:** Production-ready quality on Hostinger Shared Hosting.
