# Landlord Portal — Test Coverage & Edge Case Audit

**Test Suite:** PHPUnit / Laravel Feature & Unit Tests  
**Total Tests in Repository:** 229 passed (1,139 assertions)  
**Audit Scope:** Landlord test coverage matrix, critical missing tests, edge-case scenarios  
**Date:** 2026-10-08  

---

## 1. Landlord Feature Test Coverage Matrix

| Feature / Domain | Existing Test File | Coverage Level | Tested Scenarios |
| :--- | :--- | :--- | :--- |
| **Authentication & Role Access** | `AuthRoleAccessTest.php` | **HIGH** | Role isolation, guest redirects, middleware gating. |
| **Document Verification & Sync** | `LandlordDocumentTest.php`, `LandlordDocumentConsistencyTest.php` | **HIGH** | Upload, resubmit, approval, expiration, admin sync, history. |
| **Booking & Tenancy Auth** | `BookingTenancyAuthorizationTest.php` | **HIGH** | Student booking cancel, landlord booking approve/reject, IDOR checks. |
| **Payment Authorization** | `PaymentAuthorizationTest.php` | **MEDIUM** | Student payment submit, duplicate block, landlord mark paid isolation. |
| **Private File Security** | `FileSecurityAuthorizationTest.php` | **HIGH** | Unauthorized downloads, private file isolation, permit access. |
| **Property Management** | Partial in `AdminPropertiesRefinementTest.php` | **LOW** | Landlord CRUD, geocoding, and destructive cascades lack dedicated suite. |
| **Room Directory & Capacity** | Implicit in `RoomOccupancyRules` | **MEDIUM** | Pricing models and solo/shared occupancy tested indirectly; missing capacity race test. |
| **Tenant Onboarding** | Implicit in `BookingTenancyAuthorizationTest.php` | **MEDIUM** | Document upload, signing, deposit steps tested in happy paths; edge cases missing. |
| **Leave Requests** | `AdminBoardingMonitoringTest.php` | **LOW** | Early leave cancellation tested from monitoring perspective; landlord approval status transitions missing unit coverage. |
| **Maintenance** | None | **NONE** | No automated tests cover `MaintenanceController`. |
| **Feedback & Sentiment** | None | **NONE** | No automated tests cover `LandlordFeedbackController` or Hugging Face integration. |
| **Analytics** | `AdminAnalyticsTest.php` (Admin only) | **NONE** | Landlord `AnalyticsController` has **zero** test coverage (explains how the 30x math bug went unnoticed). |

---

## 2. Edge Case Scenario Matrix

| Edge Case Scenario | Expected Behavior | Current Implementation | Risk Level | Existing Test Coverage |
| :--- | :--- | :--- | :--- | :--- |
| **Landlord has 0 properties** | Shows welcoming onboarding empty state with "Create First Property" CTA. | Redirects `/landlord/bookings` to property create; dashboard shows empty state. | Low | Partial |
| **Property has 0 rooms** | Clear prompt to add rooms before property can receive bookings. | Handled in dashboard and property show; property cannot receive bookings. | Low | Partial |
| **Two students request the last bed slot concurrently** | Only one request approved; second request blocked by concurrency lock or auto-rejected. | Handled via `DB::transaction` and `Room::lockForUpdate()` in `BookingController@approve`. | Low | Not Tested Under Concurrency |
| **Student cancels booking after approval** | Only pending bookings can be cancelled; approved bookings require formal leave request. | Enforced in `BookingController@cancel:349–351`. | Low | Covered in `BookingTenancyAuthorizationTest` |
| **Tenant approved for future move-out date** | Stay remains active until move-out date; then transitions to completed. | Status is immediately flipped to `cancelled`, prematurely freeing slot today. | **High** | Missing |
| **Landlord deletes property with active tenants** | Deletion should be blocked with error if active stays exist. | Deletion proceeds and cascades across MySQL, wiping out active stay records. | **High** | Missing |
| **Document approved but expired** | Status stays approved historically, but flagged as expired in compliance service. | Correctly handled by `LandlordDocumentStatusService`. | Low | Covered in `LandlordDocumentConsistencyTest` |
| **Room put under maintenance with active occupants** | Maintenance locks future slots; does not corrupt existing active tenant records. | `RoomOccupancyRules` forces `slots_available = 0`; existing bookings stay intact. | Low | Missing Unit Test |
