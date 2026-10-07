# Strict MySQL & Database Query Audit

This document reviews database queries, aggregations, groupings, joins, transactions, and compatibility with MySQL 8.x `ONLY_FULL_GROUP_BY` and strict SQL modes.

---

## 1. Audit Scope & Query Patterns

We audited all instances of:
- `selectRaw()`
- `groupBy()`, `groupByRaw()`
- `having()`, `havingRaw()`
- `DB::raw()`
- Joins and Subqueries
- Database transactions

### Target Components Inspected:
- [`AnalyticsService.php`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/app/Services/AnalyticsService.php)
- [`BoardingMonitoringService.php`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/app/Services/BoardingMonitoringService.php)
- [`AuthController.php`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/app/Http/Controllers/AuthController.php)
- [`PublicPropertyMapController.php`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/app/Http/Controllers/PublicPropertyMapController.php)
- [`StudentTools.php`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/app/Services/AI/Tools/StudentTools.php)
- [`Property.php`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/app/Models/Property.php)
- [`RoomOccupancyRules.php`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/app/Services/RoomOccupancyRules.php)

---

## 2. Key Findings & Strict SQL Evaluation

### A. ONLY_FULL_GROUP_BY Compliance
- **Evaluation:** Pass with strict adherence.
- **Analysis:**
  1. In `BoardingMonitoringService.php` (lines 552, 589, 632):
     ```php
     ->groupBy('prop_properties.id', 'prop_properties.name', 'prop_properties.address')
     ```
     Selected non-aggregate columns are explicitly enumerated in `groupBy()` or wrapped in expressions like `groupByRaw($collegeExpr)`.
  2. In `AnalyticsService.php` (lines 185, 207, 300, 364):
     Calculated columns (such as `CASE WHEN ... THEN ... END AS normalized_gender`) are grouped directly by their alias or explicit expression.
  3. In `AuthController.php` (lines 308, 524, 547, 623):
     Daily aggregation queries use `->groupBy(DB::raw('DATE(created_at)'))` matching the exact `DATE(created_at)` in the `SELECT` projection.
  4. In `StudentTools.php` (lines 131, 197):
     AI analytics tools group strictly by `label` or `period_key`.

### B. Prevention of Duplicate Rows from Joins
- **Analysis:**
  In `BoardingMonitoringService.php`, distinct counts and distinct queries (e.g. `selectRaw('DISTINCT YEAR(check_in) as yr')`) ensure that joining `properties` -> `rooms` -> `bookings` does not inflate capacity or student counts.
- **Recommendation:** When expanding marketplace queries or building complex multi-room filters, avoid joining 1-to-many relationships without subquery aggregation or `distinct()`.

### C. Atomic Transactions Usage
- **Current Transaction Points:**
  - `StudentPaymentController@store`: wraps payment creation and booking state checks.
  - `TenantOnboardingController@approveDocuments`, `signContract`, `payDeposit`: updates state transitions atomically.
  - `BookingController@approve` and `cancel`: room slot adjustments are executed during status updates.
- **Rule:** Do NOT remove existing `DB::transaction()` blocks. Any workflow involving simultaneous updates to (1) booking status, (2) room capacity/slots, or (3) tenant onboarding state MUST execute inside a transaction.

### D. SQL Injection & Raw SQL Parameters
- **Audit Result:** No string-interpolated user inputs were found in raw SQL.
- **Evidence:** All `selectRaw` and `whereRaw` calls with user parameters use parameter binding (`?`) or predefined enum allowlists (e.g., `$columnMap[$groupByInput]`).

---

## 3. Invariants for Future Work

1. **NEVER disable `ONLY_FULL_GROUP_BY`** in Laravel's `config/database.php` or on Hostinger MySQL to "fix" an aggregation query.
2. **Always group by primary keys or exact projected expressions.**
3. **Always use parameterized bindings (`?`) with `selectRaw()` and `whereRaw()`.**
4. **Always use database transactions when mutating multiple interrelated tables (e.g., Booking + Room slots + Notification).**
