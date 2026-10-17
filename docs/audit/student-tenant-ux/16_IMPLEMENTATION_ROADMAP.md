# Recommended Implementation Roadmap & Development Stages

**Audit Date:** October 18, 2026  
**Scope:** Phased, Low-Risk Implementation Plan with Technical Dependencies and Regression Controls  

---

## Roadmap Overview

To safely transition the OBHS Student Portal from an administrative dashboard to a modern customer accommodation platform without breaking production invariants or disrupting live MinSU tenants, development is structured into **six discrete, verifiable phases**.

---

## Phase 1: Context-Aware Student Home & Navigation Clean-Up

- **Objective:** Replace the monolithic 2,262-line dashboard and administrative KPI tiles with a lifecycle-adaptive Home view.
- **Existing Issues Addressed:**
  - 138 KB monolithic `student/dashboard.blade.php` loading 10 sub-panels.
  - 23+ database queries per dashboard page load on Hostinger Shared Hosting.
  - "Student Operations" admin header label.
  - Irrelevant macro KPI metric cards ("Available Rooms", "Messages Total").
  - Map preview showing "TBD" price pins.
- **Affected Files:**
  - `app/Http/Controllers/AuthController.php` (`studentDashboard` method).
  - `resources/views/layouts/student_dashboard.blade.php` (header & top-meta cleanup).
  - `resources/views/components/student-sidebar.blade.php` (sidebar menu cleanup, unhiding Browse Rooms).
  - `resources/views/student/dashboard.blade.php` (refactored into clean context-aware container).
- **Reusable Components:**
  - Existing `x-toast`, `x-chatbot`, `x-student-sidebar`.
- **Backend Changes Needed:**
  - Streamline `AuthController@studentDashboard` to query only data relevant to the student's active lifecycle stage.
- **Test Requirements:**
  - Feature test: Verify Home renders Searching state for unbooked student.
  - Feature test: Verify Home renders Pending banner for applicant.
  - Feature test: Verify Home renders Move-In checklist for approved applicant.
  - Feature test: Verify Home renders My Stay overview for active tenant.
- **Relative Complexity:** Medium (3 days).
- **Risk:** Low (Non-destructive template and query refactoring; all booking and RBAC invariants preserved).

---

## Phase 2: Room Discovery, Listing Card Redesign & Property Details Polish

- **Objective:** Upgrade room browsing and property detail pages into an accommodation marketplace.
- **Existing Issues Addressed:**
  - Cluttered listing cards with up to 8 competing badges.
  - Inconsistent pricing displays for hybrid rooms.
  - Hidden mobile filter panel that covers the screen.
  - 16:6 letterbox hero banner cropping room photos.
  - Lack of landlord trust signals.
- **Affected Files:**
  - `resources/views/student/rooms/index.blade.php`
  - `resources/views/student/rooms/show.blade.php`
  - `resources/views/student/properties/show.blade.php`
  - `app/Http/Controllers/RoomController.php`
- **Reusable Components:**
  - Standardized listing card partial (`resources/views/components/room-card.blade.php`).
- **Backend Changes Needed:**
  - Add standard pagination (`paginate(12)`) on `RoomController@studentIndex`.
- **Test Requirements:**
  - Filter regression tests (price slider, capacity, amenities).
  - Pagination links testing.
- **Relative Complexity:** Medium (3 days).
- **Risk:** Low.

---

## Phase 3: Consolidated "My Stays & Bookings" (Unifying Requests, Onboarding & Tenancy)

- **Objective:** Unify the currently fragmented student accommodation records into a clear 3-tab hub: Active Stay, Pending & Upcoming, Past Stays.
- **Existing Issues Addressed:**
  - Approved booking has no direct CTA to start Tenant Onboarding.
  - Contracts and monthly payments live in completely separate portal sections.
  - Confusion between security deposit receipts and monthly rent receipts.
- **Affected Files:**
  - `resources/views/student/bookings/index.blade.php`
  - `resources/views/student/onboarding/show.blade.php`
  - `resources/views/student/payments/index.blade.php`
  - `app/Http/Controllers/BookingController.php`
  - `app/Http/Controllers/TenantOnboardingController.php`
- **Reusable Components:**
  - Onboarding 4-step progress bar, DomPDF lease viewer.
- **Backend Changes Needed:**
  - Provide a single composite view model for student stays (`activeStay`, `pendingBookings`, `pastBookings`).
- **Test Requirements:**
  - Onboarding flow integrity tests (Documents -> Signature -> Deposit -> Completed).
  - Access control tests ensuring students can only view their own records.
- **Relative Complexity:** High (4 days).
- **Risk:** Medium (Requires careful preservation of linear onboarding state transitions).

---

## Phase 4: Communication, Notifications & Review UX

- **Objective:** Fix mobile chat layouts, eliminate notification spam, and make review prompts dismissible.
- **Existing Issues Addressed:**
  - Mobile split-pane bug forcing students to scroll past all threads to see chat.
  - Persistent 13+ unread notification counters.
  - Intrusive "Rate your completed stay" yellow banner.
- **Affected Files:**
  - `resources/views/messages/index.blade.php`
  - `resources/views/notifications/index.blade.php`
  - `resources/views/student/rooms/feedback.blade.php`
  - `app/Http/Controllers/MessageController.php`
  - `app/Http/Controllers/NotificationController.php`
- **Reusable Components:**
  - Mobile master-detail JavaScript toggle.
- **Backend Changes Needed:**
  - Add notification thread grouping and auto-dismiss on page view.
- **Test Requirements:**
  - Messaging authorization tests (students can only message relevant landlords).
  - Completed-stay feedback validation tests.
- **Relative Complexity:** Medium (2 days).
- **Risk:** Low.

---

## Phase 5: Mobile Ergonomics, Accessibility & Performance Optimization

- **Objective:** Ensure effortless smartphone usability across 320px–430px viewports, implement mobile bottom app bar, and optimize shared hosting queries.
- **Existing Issues Addressed:**
  - 28px touch targets on mobile tables.
  - Excessive vertical scrolling fatigue.
  - Missing image lazy-loading.
  - Lack of emergency dispatch numbers in Help Center.
- **Affected Files:**
  - `resources/views/layouts/student_dashboard.blade.php`
  - `resources/views/student/reports/index.blade.php`
  - Global CSS tokens (`public/css` or inline Blade push styles).
- **Test Requirements:**
  - Responsive viewport rendering checks.
  - Keyboard focus and WCAG contrast automated audit.
- **Relative Complexity:** Medium (3 days).
- **Risk:** Low.

---

## Phase 6: Optional Future Consumer Enhancements (Post-Baseline)

- **Objective:** Add delightful consumer marketplace features once core operational parity is solid.
- **Candidate Features:**
  - Student Wishlists / Saved Listings.
  - MinSU Campus Walking Distance calculator (exact meters from Gate 1 / Gate 2).
  - Roommate matching preferences (quiet hours, study habits).
- **Relative Complexity:** High (Future backlog).
