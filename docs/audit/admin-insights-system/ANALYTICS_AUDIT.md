# Analytics Module Audit (`/admin/analytics`)

## 1. Metrics Inventory & Data Definitions

| Metric Name | Calculation / Query | Source Table(s) | Date / Filter Rule | Strict SQL Compliant |
|---|---|---|---|---|
| **Active Boarders** | Canonical distinct student counting (`COUNT(DISTINCT bookings.student_id)`) | `bookings`, `users` | Overlaps active period, `check_in <= date_to`, `check_out > date_from`, `status = 'approved'` | Yes |
| **Registered Students** | `User::where('role', 'student')->count()` | `users` | System-wide total | Yes |
| **Room Occupancy Rate** | `(occupied_rooms / total_rooms) * 100` | `rooms` | Aggregated across approved properties | Yes |
| **Bed Occupancy Rate** | `(occupied_beds / total_capacity) * 100` where `occupied_beds = total_capacity - slots_available` | `rooms` | Aggregated across approved properties | Yes |
| **Monthly Boarding Trend** | 12-month series comparing ongoing active boarders vs newly created bookings | `bookings`, `rooms`, `properties` | Grouped by month for selected year | Yes |
| **Booking Lifecycle** | Breakdown by status (`approved`, `pending`, `rejected`, `cancelled`) | `bookings` | Filtered by `created_at` in selected period | Yes |
| **Demographics: Colleges** | De-duplicated active students grouped by MinSU college code | `bookings`, `users` | Filtered by canonical base query | Yes |
| **Demographics: Top Programs** | De-duplicated active students grouped by academic program name | `bookings`, `users` | Filtered by canonical base query, top 10 | Yes |
| **Gender Distribution** | Active boarders vs registered students breakdown (`male`, `female`, `unspecified`) | `bookings`, `users` | Subquery aggregation with `COALESCE` and `LOWER(TRIM)` | Yes |
| **Property Occupancy Table** | Per-property room and bed occupancy breakdown with landlord details | `properties`, `rooms`, `users` | Filtered by property dropdown if selected | Yes |
| **Document Compliance** | Valid, Expiring Soon, Expired, Pending review counts for Business Permits & Safety Certificates | `landlord_documents` | Current active documents (`is_current = true`) | Yes |

---

## 2. Analytics Overlap & Distinct Module Roles
We evaluated the three places where analytics and statistics currently appear:

1. **Admin Dashboard (`/admin/dashboard`):**
   - *Role:* High-level operational snapshot (Pending approvals, active quick counts, system status).
   - *Classification:* **JUSTIFIED**.
2. **Active Boarders -> Analytics (`/admin/boarding-monitoring/students?tab=analytics`):**
   - *Role:* Student-stay-specific distribution (Boarding trends, property distribution, academic breakdown).
   - *Classification:* **JUSTIFIED** as an operational monitoring tab.
3. **Insights -> Analytics (`/admin/analytics`):**
   - *Role:* Comprehensive institutional analytics suite, combining boarding metrics, room/bed occupancy, booking lifecycles, demographic distributions, and landlord compliance breakdowns.
   - *Classification:* **JUSTIFIED** as the primary cross-system intelligence center.

---

## 3. Visualization & Filter Assessment
- **Charts:** Implemented using Chart.js v4.4.1 (Trend Line, Booking Status Doughnut, College Bar, Program Bar, Gender Doughnut).
- **Date Presets:** All Time, This Month, Last Month, Custom Date Range.
- **Entity Filters:** Filter by specific Boarding House or MinSU College.
- **Strict MySQL Verification:** All queries use canonical subquery groupings and strict aggregations without raw non-aggregate column selections, fully compliant with `ONLY_FULL_GROUP_BY`.
