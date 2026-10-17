# Monitoring & Analytics Alignment

**Document Reference:** `docs/implementation/landlord-ux-transformation/MONITORING_AND_ANALYTICS.md`  
**Modified Files:** `app/Http/Controllers/AnalyticsController.php`, `resources/views/landlord/monitoring/properties.blade.php`, `resources/views/landlord/analytics/index.blade.php`

---

## 1. Monitoring Hub vs Management Separation

In accordance with `docs/audit/landlord-ux/03_MONITORING_VS_MANAGEMENT.md`, OBHS maintains a strict separation between operational management and read-only monitoring:

- **Monitoring Hub (`landlord.monitoring.properties`):**
  - Read-only real-time portfolio overview.
  - Aggregates room statuses: Total Rooms, Occupied Rooms, Available Bed Slots, Available Rooms, Maintenance Rooms, and Overall Occupancy Rate.
  - Displays property approval state with clear badges: `Approved`, `Draft`, `Pending Review`, and `Rejected`.
- **Property Management (`landlord.properties.*`):**
  - Interactive CRUD operations (edit details, upload photos, manage room pricing, duplicate rooms).

---

## 2. Analytics KPIs & Mathematical Invariants

- **Monthly Collected Revenue (30d):** Sum of approved `TenantPayment` records within the last 30 days belonging to the landlord's properties.
- **Expected Monthly Revenue:** Sum of monthly prices of active approved bookings whose checkout is in the future.
- **Occupancy Rate:** Calculated accurately as `(occupied_rooms / total_rooms) * 100`.
- **Strict SQL Compliance:** All database aggregations respect MySQL 8 `ONLY_FULL_GROUP_BY` strict standards with zero raw group by omissions.
