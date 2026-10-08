# Regression Testing Results & Test Evidence

**Repository:** Online Boarding House System (OBHS)  
**Date:** 2026-10-08  
**Total Tests:** 241 Passed (1,220 Assertions)  
**Execution Duration:** ~61.46s  
**Status:** 100% Green  

---

## 1. Targeted Feature Test Suites

### `Tests\Feature\LandlordIntegrityFixesTest` (7 Passed, 38 Assertions)
| Test Case | Description | Result |
| :--- | :--- | :---: |
| `test_financial_analytics_calculates_actual_collected_and_monthly_expected_revenue` | Verifies monthly revenue equals approved `TenantPayment` amounts and expected revenue equals active monthly rent roll. | PASS |
| `test_future_dated_leave_approval_preserves_active_stay_and_room_occupancy` | Verifies future leave approval retains `approved` status, sets `check_out` to future date, and preserves room occupancy. | PASS |
| `test_immediate_leave_approval_concludes_stay_and_frees_room_slot` | Verifies immediate move-out marks booking `cancelled` and releases bed slot. | PASS |
| `test_property_with_active_or_pending_bookings_cannot_be_deleted` | Enforces deletion blocker on properties with active boarders or pending requests. | PASS |
| `test_room_with_active_or_pending_bookings_cannot_be_deleted` | Enforces deletion blocker on rooms with active boarders or pending requests. | PASS |
| `test_empty_property_and_room_can_be_safely_deleted` | Verifies deletion is permitted when no operational dependencies exist. | PASS |
| `test_shared_room_hydrates_and_displays_all_active_occupants` | Verifies all occupants in multi-capacity shared rooms are hydrated and rendered. | PASS |

### `Tests\Feature\LandlordMonitoringManagementTest` (5 Passed, 43 Assertions)
| Test Case | Description | Result |
| :--- | :--- | :---: |
| `test_landlord_can_view_own_property_and_room_monitoring` | Landlord views read-only property and room status with deep links to management. | PASS |
| `test_landlord_can_view_own_compliance_monitoring` | Landlord inspects accreditation, permit verification, and expiration status. | PASS |
| `test_student_and_guest_cannot_access_landlord_monitoring` | Unauthorized students and unauthenticated guests are blocked with HTTP 403. | PASS |
| `test_landlord_navigation_sidebar_renders_all_sections` | Sidebar correctly renders 6 standard sections: Overview, Monitoring, Management, Communication, Insights, Account. | PASS |
| `test_preserved_management_routes_remain_functional` | All preserved management routes remain accessible and correctly wired. | PASS |

---

## 2. Cross-System Regression Suites

- `Tests\Feature\BookingTenancyAuthorizationTest`: 6 passed
- `Tests\Feature\PaymentAuthorizationTest`: 4 passed
- `Tests\Feature\LandlordDocumentTest`: 16 passed
- `Tests\Feature\LandlordDocumentConsistencyTest`: 9 passed
- `Tests\Feature\AdminMonitoringManagementSeparationTest`: 6 passed
- `Tests\Feature\AdminActionCenterTest`: 2 passed
- `Tests\Feature\AdminAnalyticsTest`: 12 passed
- `Tests\Feature\AdminBoardingMonitoringTest`: 19 passed
- `Tests\Feature\AdminBoardingMonitoringRefinementTest`: 16 passed
- `Tests\Feature\AdminPropertiesRefinementTest`: 18 passed
- `Tests\Feature\AdminDetailPagesRefinementTest`: 6 passed
- `Tests\Feature\AdminDocumentVerificationFixesTest`: 6 passed
- `Tests\Feature\AdminIncidentReportsTest`: 2 passed
- `Tests\Feature\AdminInstitutionalReportingTest`: 6 passed
- `Tests\Feature\AdminSecurityFoundationsTest`: 7 passed
- `Tests\Feature\AuthRoleAccessTest`: 10 passed
- `Tests\Feature\ChatbotAiAssistantTest`: 19 passed
- `Tests\Feature\ChatbotLandlordDocumentTest`: 11 passed
- `Tests\Feature\ChatbotReportDateFilterTest`: 14 passed
- `Tests\Feature\ChatbotStudentAnalyticsTest`: 10 passed
- `Tests\Feature\ChatbotTopicIsolationTest`: 11 passed
- `Tests\Feature\FileSecurityAuthorizationTest`: 6 passed
- `Tests\Unit\FileStorageManagerTest`: 25 passed
- `Tests\Unit\DocumentExpirationServiceTest`: 15 passed

**Total: 241 passed (1,220 assertions)**
