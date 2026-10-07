# Test Coverage & Regression Protection Audit

This document inventories existing automated tests, identifies test coverage gaps across critical user roles and workflows, and defines high-value test suites needed for robust regression protection.

---

## 1. Current Test Suite Inventory

As of baseline snapshot (Commit `a733a1440090c3ba9a228435a2c2b18cacac5aad`), the test suite contains **19 test files** with **162 passing tests**:

### A. Admin & Institutional Reporting (8 Suites)
- `Tests\Feature\AdminAnalyticsTest` (7 tests): KPI calculations, strict SQL compatibility, date filtering.
- `Tests\Feature\AdminBoardingMonitoringTest` (14 tests): Student boarding monitoring, college breakdown, print views.
- `Tests\Feature\AdminBoardingMonitoringRefinementTest` (11 tests): Filters, search queries, pagination.
- `Tests\Feature\AdminDashboardGenderAndDocumentsTest` (8 tests): Demographics metrics, document status badges.
- `Tests\Feature\AdminDetailPagesRefinementTest` (6 tests): Landlord & property inspection views.
- `Tests\Feature\AdminDocumentVerificationFixesTest` (7 tests): Approval and rejection workflows.
- `Tests\Feature\AdminPhase5Phase6Test` (7 tests): Permit & safety certificate reviews.
- `Tests\Feature\AdminPropertiesRefinementTest` (9 tests): Pending property approval workflow.

### B. Landlord Subsystem & Compliance (3 Suites)
- `Tests\Feature\LandlordDocumentTest` (16 tests): Document upload, pending status, resubmission, admin review.
- `Tests\Feature\BackfillLandlordDocumentsCommandTest` (3 tests): Database backfill command verification.
- `Tests\Unit\DocumentExpirationServiceTest` (8 tests): Expiring soon and expired status calculations.

### C. AI Assistant & Chatbot (5 Suites)
- `Tests\Feature\ChatbotAiAssistantTest` (18 tests): Rate limiting, tool invocation, conversation memory, structured actions.
- `Tests\Feature\ChatbotLandlordDocumentTest` (9 tests): Landlord document statistics & missing documents tools.
- `Tests\Feature\ChatbotReportDateFilterTest` (11 tests): Report statistics and date range resolver.
- `Tests\Feature\ChatbotStudentAnalyticsTest` (10 tests): Grouped student statistics and trend tools.
- `Tests\Feature\ChatbotTopicIsolationTest` (11 tests): Prompt guardrails, cross-user isolation, error recovery.

### D. Storage & Infrastructure (2 Suites)
- `Tests\Unit\FileStorageServiceTest` (9 tests): Multi-driver fallback (R2 -> Supabase -> Local), credential safety.
- `Tests\Feature\ExampleTest` / `Tests\Unit\ExampleTest` (2 tests): Basic sanity.

---

## 2. Identified Test Coverage Gaps

While the Admin, AI, and Storage layers have strong coverage, several core user workflows lack explicit regression test suites:

### GAP 1: Authentication & Role Matrix Enforcement
- **Missing Coverage:**
  - Student attempting to access Landlord portal (`/landlord/*`) returns 403.
  - Landlord attempting to access Student portal (`/student/*`) returns 403.
  - User with role `pending` redirected to `/onboarding/role`.
  - Student with incomplete setup redirected to `/student/setup`.
  - Landlord with incomplete profile redirected to `/landlord/setup`.
  - Unverified email user redirected to `/email/verify`.

### GAP 2: Booking & Tenancy Ownership (IDOR Protection)
- **Missing Coverage:**
  - Student cannot cancel another student's booking.
  - Landlord cannot approve/reject bookings belonging to another landlord's property.
  - Student cannot view another student's onboarding contract.
  - Room slots decrement atomically on booking approval.

### GAP 3: Payment Tracking & Financial Authorization
- **Missing Coverage:**
  - Student cannot submit monthly payments for another student's booking.
  - Landlord cannot modify payment status on bookings owned by another landlord.
  - Monthly payment amount cannot be spoofed by client payload.

### GAP 4: Sensitive File Authorization
- **Missing Coverage:**
  - Unauthenticated visitor cannot download sensitive documents from `/files/{path}`.
  - Student cannot access another student's ID or enrollment proof.
  - Landlord cannot access another landlord's private permit or GCash QR.

---

## 3. High-Value Test Suites Added in This Harness

To eliminate these gaps, we are implementing targeted feature tests:
1. `tests/Feature/AuthRoleAccessTest.php`: Complete matrix validation across Guest, Pending, Student, Landlord, Admin.
2. `tests/Feature/BookingTenancyAuthorizationTest.php`: Strict ownership checks on bookings, onboarding contracts, and capacity.
3. `tests/Feature/PaymentAuthorizationTest.php`: Cross-role and cross-user payment isolation.
4. `tests/Feature/FileSecurityAuthorizationTest.php`: Multi-role file access protection.
