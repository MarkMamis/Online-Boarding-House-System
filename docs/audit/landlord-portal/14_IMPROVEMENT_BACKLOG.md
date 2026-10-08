# Landlord Portal — Improvement & Refinement Backlog

**Scope:** Prioritized Technical, Architectural, UI/UX, and Business Logic Enhancements  
**Date:** 2026-10-08  

---

## 1. Prioritized Defect & Improvement Log

| Item ID | Category | Title & Summary | Severity | Complexity | Proposed Target |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **LOG-01** | **Financial** | **Fix Revenue Multiplier in Landlord Analytics**<br>Replace `$booking->room->price * $booking->getDurationInDays()` with actual monthly rent aggregation or sum of approved `tenant_payments`. | **P0** | Small | `AnalyticsController.php` |
| **LOG-02** | **Tenancy** | **Fix Premature Booking Cancellation on Leave Approval**<br>Maintain booking `status = approved` with updated `check_out` date when leave request is approved for a future date. Transition to `completed` only when current date reaches check-out date. | **P1** | Small | `LandlordLeaveRequestController.php` |
| **LOG-03** | **Data Safety** | **Guard Property & Room Deletion against Active Stays**<br>Prevent destructive cascade deletion of properties and rooms when active approved bookings, pending payments, or active onboardings exist. | **P1** | Medium | `PropertyController.php`, `RoomController.php` |
| **LOG-04** | **UI/UX** | **Multi-Tenant Room Display Hydration**<br>Display all active occupants in multi-capacity shared rooms instead of only picking the first tenant. | **P2** | Small | `PropertyController.php`, `RoomController.php`, Blade views |
| **LOG-05** | **Payments** | **Auto-Create Cash Payment Record on Quick "Mark as Paid"**<br>Allow landlords to immediately record received cash payments without requiring an advance digital submission from the student. | **P2** | Small | `PaymentController.php` |
| **LOG-06** | **Maintenance** | **Sync Availability Snapshot on Maintenance Completion**<br>Invoke `$room->syncAvailabilitySnapshot()` in `MaintenanceController@completeMaintenance` to prevent stale room capacity. | **P2** | Small | `MaintenanceController.php` |
| **LOG-07** | **Productivity** | **Landlord Dashboard "Needs Attention" Action Center**<br>Introduce a focused daily action queue (pending requests, pending onboarding approvals, overdue payments, leave requests, expiring permits). | **P2** | Medium | `AuthController.php`, `dashboard.blade.php` |
| **LOG-08** | **Navigation** | **Streamline Landlord Sidebar Information Architecture**<br>Consolidate 14 top-level items into 5 coherent operational domains (Overview, Portfolio, Operations, Finance, Account) and remove redundant room-level accordion trees. | **P2** | Medium | `layouts/landlord.blade.php` |
| **LOG-09** | **Mobile** | **Responsive Card Layout for Mobile Tables**<br>Provide card-based mobile summaries for payments, tenants, and properties on narrow viewports (< 768px). | **P3** | Medium | Blade views |
| **LOG-10** | **Testing** | **Comprehensive Landlord Feature Test Suite**<br>Add dedicated feature tests covering `LandlordAnalyticsTest`, `LandlordMaintenanceTest`, `LandlordPropertyCrudTest`, and `LandlordLeaveApprovalTest`. | **P2** | Medium | `tests/Feature/` |
