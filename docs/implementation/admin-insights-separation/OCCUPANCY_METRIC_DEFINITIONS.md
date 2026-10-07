# Boarding Occupancy & Tenancy Metric Definitions

This document defines the authoritative semantics and calculation formulas for room occupancy, bed capacity, and active student boarders in OBHS.

---

## 1. Domain Definitions

| Metric | Source Entity | Condition / Formula | Operational Meaning |
| :--- | :--- | :--- | :--- |
| **Active Boarders** | `Booking` table | `status = 'approved'` AND `check_in <= date` AND (`check_out IS NULL` OR `check_out >= date`) | Distinct MinSU students currently physically residing in off-campus housing. |
| **Total Rooms** | `Room` table | `COUNT(rooms.id)` where property is approved | Total room inventory across accredited boarding houses. |
| **Occupied Rooms** | `Room` table | `status = 'occupied'` OR (`capacity - slots_available > 0`) | Rooms currently housing at least one resident student. |
| **Available Rooms** | `Room` table | `status = 'available'` AND `slots_available > 0` | Rooms with immediate vacancy for student bookings. |
| **Total Bed Capacity** | `Room` table | `SUM(rooms.capacity)` | Total licensed student bed slots across all rooms. |
| **Occupied Bed Slots** | `Room` table | `SUM(rooms.capacity - rooms.slots_available)` | Bed slots currently occupied by resident students. |
| **Available Bed Slots** | `Room` table | `SUM(rooms.slots_available)` | Available bed vacancies open for new bookings. |
| **Room Occupancy Rate** | Derived | `(Occupied Rooms / Total Rooms) * 100` | Percentage of rooms with active occupancy. |
| **Bed Occupancy Rate** | Derived | `(Occupied Bed Slots / Total Bed Capacity) * 100` | Percentage of total student capacity currently filled. |

---

## 2. Invariants & Reconciliation Rules
- **No Direct Mutation on Status Flags:** Physical tenancy queries rely on confirmed `Booking` date intervals (`check_in <= now() <= check_out`).
- **Inventory Synchronization:** When a student onboarding is completed and move-in occurs, the corresponding room's `slots_available` is decremented. If `slots_available == 0`, room status is updated to `occupied`.
- **Dual Reporting Parity:** Both the `AnalyticsService` and `AdminReportingService` calculate capacity rates using consistent room aggregation, preventing discrepancies between dashboard KPIs and occupancy CSV exports.
