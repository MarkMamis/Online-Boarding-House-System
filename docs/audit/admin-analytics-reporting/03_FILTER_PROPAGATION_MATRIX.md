# 03 — Filter Propagation Matrix

## Overview
This document traces how filter query parameters propagate from HTTP requests through controllers, services, database queries, and view components across the Admin Analytics and Institutional Reporting module.

---

## Filter Propagation Matrix

| Filter Name | Request Parameter | Validation / Type Casting | Affects Analytics KPIs | Affects Dashboard Charts | Affects Preset Preview Table | Affects CSV Export | Affects Print Output | Expected Behavior | Actual Behavior | Discrepancy / Gap |
|---|---|---|---|---|---|---|---|---|---|---|
| **Reporting Period Preset** | `period_preset` (`all`, `this_month`, `last_month`, `custom`) | String match; resolved via `resolveReportingPeriod` | **YES** | **PARTIAL** (Affects Booking Lifecycle & Demographics; Trend uses `year` only) | **PARTIAL** (Only affects `academic_distribution`) | **NO** (Export method ignores `period_preset`) | **SAME AS VIEW** (Prints entire dashboard) | All presets and exports should respect period boundaries | CSV export and some presets (`active_boarders`, `property_distribution`, `compliance`, `incident`) bypass the period filter |
| **Custom Date Range** | `date_from`, `date_to` | Date strings parsed with Carbon | **YES** | **PARTIAL** (Demographics & Lifecycle) | **PARTIAL** (Only `academic_distribution`) | **NO** (Not passed to export) | **SAME AS VIEW** | Custom range applies across all time-bound presets | CSV ignores custom dates |
| **Boarding House (Property)** | `property_id` | `(int)` cast via `filled('property_id')` | **YES** (Filtered base query) | **YES** (Trend, Demographics, Occupancy) | **YES** (Applies to `active_boarders`, `property_distribution`, `academic_distribution`, `occupancy_availability`) | **YES** (Passed in export route) | **SAME AS VIEW** | Property filter isolates data to selected property | Consistently applied across presets supporting property scoping |
| **College** | `college` | String string/trimmed | **YES** (Filtered base query) | **YES** (Demographics) | **YES** (Applies to `active_boarders`, `academic_distribution`) | **YES** (Passed in export route) | **SAME AS VIEW** | College filter isolates student-centric datasets | Not applicable to property/document presets (as expected) |
| **Program** | `program` | String trimmed | **YES** (Filtered base query) | **YES** (Demographics) | **YES** (Applies to `academic_distribution`) | **NO** (Not explicitly passed to CSV route link) | **SAME AS VIEW** | Program filter applies to academic preset CSV | Missing `program` query parameter in Blade export URL |
| **Report Preset** | `report_preset` | String key, fallback to `occupancy_availability` | **NO** | **NO** | **YES** (Switches table dataset) | **YES** (Export exports selected preset) | **NO** (Prints entire dashboard) | Selecting preset updates the report context | Does not alter dashboard KPIs or charts; print output contains entire page |
| **Reset Action** | Route link without query params | N/A | **YES** (Resets to defaults) | **YES** (Resets to defaults) | **YES** (Resets to default preset) | **YES** (Exports default preset) | **YES** | Returns page to pristine state | Working as intended |

---

## Key Filter Inconsistencies Discovered

1. **CSV Export Parameter Dropping:**
   - In [`AdminAnalyticsController.php:101-109`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/app/Http/Controllers/AdminAnalyticsController.php#L101-L109), `exportCsv` only reads `property_id`, `college`, and `report_preset`. It completely ignores `period_preset`, `date_from`, `date_to`, and `program`.
   - In [`index.blade.php:21-23`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/resources/views/admin/analytics/index.blade.php#L21-L23), the CSV export URL only appends `report_preset`, `property_id`, and `college`.
2. **`active_boarders` Preset Date Hardcoding:**
   - In [`AdminReportingService.php:90-100`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/app/Services/AdminReportingService.php#L90-L100), `getActiveBoardersData()` hardcodes `$today = now()->toDateString();`. It does not accept or apply the user's selected `date_from`/`date_to` period filters.
