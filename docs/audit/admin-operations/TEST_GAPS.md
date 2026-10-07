# OBHS Admin Operations — Test Coverage & Gaps Analysis

This document audits the test suite protecting the seven administrative operations pages and identifies existing coverage as well as remaining gaps.

---

## 1. Existing Test Coverage Map

| Target Feature / Page | Test File | Key Test Cases Implemented | Status |
| :--- | :--- | :--- | :---: |
| **Boarded Students (Census & Roster)** | `tests/Feature/AdminBoardingMonitoringTest.php` | • Admin can access active students list<br>• Active boarder temporal filtering (`check_in <= now() <= check_out`)<br>• Student and Landlord authorization denials | PASS |
| **Boarded Students (Analytics & Visuals)** | `tests/Feature/AdminAnalyticsTest.php`<br>`tests/Feature/AdminBoardingMonitoringRefinementTest.php` | • Grouped stats by college, program, gender<br>• MySQL `ONLY_FULL_GROUP_BY` compliance<br>• Distinct student counts (`COUNT(DISTINCT)`) | PASS |
| **Document Verification & Security** | `tests/Feature/AdminDocumentVerificationFixesTest.php`<br>`tests/Feature/FileSecurityAuthorizationTest.php` | • Admin preview returns `200` inline<br>• Admin download returns attachment disposition<br>• Unauthorized user forbidden from viewing private files<br>• Multiple document queue rendering | PASS |
| **Landlord Compliance & Expiry** | `tests/Feature/LandlordDocumentTest.php`<br>`tests/Feature/AdminDashboardGenderAndDocumentsTest.php` | • Admin can approve document<br>• Admin can reject document with reason<br>• Expired / Expiring soon temporal logic<br>• Replacement documents reset to pending | PASS |
| **Tenancy & Onboarding Oversight** | `tests/Feature/BookingTenancyAuthorizationTest.php`<br>`tests/Feature/TenantOnboardingTest.php` | • Student cannot view other contracts<br>• Landlord cannot view other onboardings<br>• Admin can view onboarding progress | PASS |

---

## 2. Identified Test Gaps & Recommendations

### Gap 1: Zero-Data & Filter Reset States
- **Area:** All 7 Pages.
- **Description:** Tests currently verify seeded records. Edge-case tests should explicitly assert that filtering down to zero results renders friendly empty states rather than empty unformatted tables or Javascript errors.
- **Priority:** P2.

### Gap 2: Concurrent Multi-Document Status Synchronization
- **Area:** `Landlord Approvals` vs `Document Verification`.
- **Description:** Add automated test confirming that approving a `safety_certificate` in `AdminLandlordDocumentController` immediately reflects as approved in `LandlordProfile` and vice-versa.
- **Priority:** P1.

### Gap 3: Property Capacity & Bed Slot Aggregation Under Edge Scenarios
- **Area:** `Boarding Houses` (`/admin/properties`).
- **Description:** Add test coverage verifying that a property with 0 rooms, or rooms with 0 capacity, displays gracefully with 0% occupancy without division-by-zero errors.
- **Priority:** P2.

### Gap 4: Export & Print Route Parameter Preservation
- **Area:** `Boarded Students Print` (`/admin/boarding-monitoring/students/print`).
- **Description:** Verify that active query filters (`college`, `program`, `property_id`) passed on the main page are preserved when accessing the print route.
- **Priority:** P2.
