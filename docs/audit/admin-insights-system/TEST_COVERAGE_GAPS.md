# Test Coverage & Gaps Analysis

## 1. Existing Test Coverage
- `Tests\Feature\AdminAnalyticsTest.php`: Tests access restrictions, parameter resolution, metric calculations, and JSON payload consistency.
- `Tests\Feature\AdminRoleAccessTest.php`: Tests admin boundary security across reports, users, student profiles, and landlord details.
- `Tests\Feature\ChatbotReportDateFilterTest.php`: Tests report statistics queries and date-bound filtering.
- `Tests\Feature\FileSecurityAuthorizationTest.php`: Tests student/landlord file and document isolation.

---

## 2. Identified Test Gaps (Future Roadmap)
1. **Notification Ownership Tests:** Add explicit unit/feature tests asserting that User A cannot call `POST /notifications/{id}/read` on User B's notification ID.
2. **Report State Transition Tests:** Add feature tests verifying that `ReportController::update` sets `resolved_at` upon marking `status = 'resolved'` and sets `response_read = false` when an admin response is provided.
3. **Admin User Update Tests:** Add feature tests ensuring that `adminUpdateStudent` and `adminUpdateLandlord` cannot be abused to perform mass-assignment privilege escalation.
