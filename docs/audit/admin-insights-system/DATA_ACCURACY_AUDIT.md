# Data Accuracy & Strict MySQL Audit

## 1. Metric Accuracy & De-Duplication
- **Student Boarding Counts:** The analytics and monitoring queries use `COUNT(DISTINCT bookings.student_id)` to prevent counting the same student multiple times across overlapping or historical bookings.
- **Occupancy Calculations:**
  - *Room Basis:* Calculated as `occupied_rooms / total_rooms`.
  - *Bed Basis:* Calculated as `(total_capacity - slots_available) / total_capacity`.
  - Both definitions are explicitly separated in UI labels to prevent confusion between room units and individual student slots.

---

## 2. Strict MySQL (`ONLY_FULL_GROUP_BY`) Compliance
- **Audit Findings:**
  1. Gender distribution aggregations in `AnalyticsService::getStudentDemographics` use strict derived subqueries (`fromSub`) where grouped columns match exactly the selected non-aggregate columns.
  2. Academic distributions in `BoardingMonitoringService::getCollegeDistribution` group by exact select keys (`college_code`, `program_name`).
  3. Booking lifecycle counts in `AnalyticsService::getBookingStatusBreakdown` group solely by `status`.
  4. Document compliance queries in `AnalyticsService::getDocumentComplianceBreakdown` use `COUNT(DISTINCT document_type) = 2` within a `HAVING` clause grouped by `landlord_id`.
- **Verdict:** All reporting queries are 100% compliant with MySQL 8.x `ONLY_FULL_GROUP_BY` strict mode.
