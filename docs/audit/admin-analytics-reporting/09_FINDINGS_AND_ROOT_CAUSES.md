# 09 — Findings and Root Causes

## Comprehensive Issue Registry

---

### Finding ID: `FIND-ANL-001`
- **Issue:** Report presets appear to leave most of the page unchanged.
- **Observed Behavior:** Selecting any of the 6 institutional report presets reloads the page with identical KPI cards, trend charts, booking lifecycle doughnuts, demographic charts, and compliance summaries.
- **Code Behavior:** `AdminAnalyticsController::index()` always calculates and passes all dashboard datasets regardless of `report_preset`. `resources/views/admin/analytics/index.blade.php` renders all dashboard visualizers above the table.
- **Affected Route:** `GET /admin/analytics` (`admin.analytics.index`)
- **Affected Files & Lines:**
  - [`AdminAnalyticsController.php:58-92`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/app/Http/Controllers/AdminAnalyticsController.php#L58-L92)
  - [`index.blade.php:116-278`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/resources/views/admin/analytics/index.blade.php#L116-L278)
- **Root Cause:** Architectural coupling of high-level analytical dashboards with institutional report generation on a single monolithic view. Preset selection only swaps `$presetData` in the lower preview table.
- **Impact:** High administrator confusion; false impression that presets are non-functional or broken.
- **Severity:** Moderate (UX / Information Architecture).
- **Recommended Fix:** Implement tabbed separation (Option B) or dedicated routes (Option C), isolating the Institutional Reports Generator and rendering dedicated summary headers per preset.

---

### Finding ID: `FIND-ANL-002`
- **Issue:** Print Report button prints the entire application UI spanning 4+ pages.
- **Observed Behavior:** Clicking "Print Report" opens a multi-page browser print dialog containing top navbar, sidebar, filter forms, preset buttons, charts, and table.
- **Code Behavior:** Button triggers raw `window.print()`. No print stylesheet (`@media print`) or `@page` CSS exists on the page.
- **Affected Route:** `GET /admin/analytics`
- **Affected Files & Lines:**
  - [`index.blade.php:24-26`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/resources/views/admin/analytics/index.blade.php#L24-L26)
  - [`layouts/admin.blade.php:1-120`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/resources/views/layouts/admin.blade.php#L1-L120)
- **Root Cause:** Absence of a dedicated printable report view or print-specific CSS rules.
- **Impact:** High. Impossible for administrators to generate clean, professional institutional reports for university submission.
- **Severity:** High (Functional / Usability).
- **Recommended Fix:** Create a dedicated printable endpoint (`/admin/analytics/print`) rendering a clean, A4-formatted institutional report template with official MinSU letterhead, filter summaries, and signature blocks.

---

### Finding ID: `FIND-ANL-003`
- **Issue:** Discrepancy between Active Boarders (1) and Room/Bed Occupancy (0%).
- **Observed Behavior:** Dashboard shows 1 active accommodated student, but room and bed occupancy rates remain 0%.
- **Code Behavior:** `active_boarders` is computed dynamically from `Booking` stay overlaps, whereas `room_occupancy_rate` and `bed_occupancy_rate` in `AnalyticsService::getKpis()` query static inventory columns (`rooms.status = 'occupied'` and `rooms.slots_available`).
- **Affected Files & Lines:**
  - [`AnalyticsService.php:52-77`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/app/Services/AnalyticsService.php#L52-L77)
- **Root Cause:** Decoupled data models: booking stay state vs. static room inventory state.
- **Impact:** Data perceived as inaccurate or contradictory by university stakeholders.
- **Severity:** Moderate (Data Integrity / Business Logic).
- **Recommended Fix:** Ensure room inventory flags update consistently upon booking approval, or compute occupancy dynamically from active booking records.

---

### Finding ID: `FIND-ANL-004`
- **Issue:** CSV Export drops reporting period, date range, and program filters.
- **Observed Behavior:** Exporting CSV after applying custom date ranges or academic program filters exports unfiltered or default period datasets.
- **Code Behavior:** `AdminAnalyticsController@exportCsv` only passes `property_id` and `college` into `$filters`. The export URL in Blade does not append date bounds.
- **Affected Files & Lines:**
  - [`AdminAnalyticsController.php:101-109`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/app/Http/Controllers/AdminAnalyticsController.php#L101-L109)
  - [`index.blade.php:21-23`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/resources/views/admin/analytics/index.blade.php#L21-L23)
- **Root Cause:** Incomplete filter propagation in the export controller and Blade action URL.
- **Impact:** Inaccurate exports when custom date filters are applied.
- **Severity:** Moderate (Functional Bug).
- **Recommended Fix:** Pass all resolved period and entity filters into `exportCsv` and `AdminReportingService`.

---

### Finding ID: `FIND-ANL-005`
- **Issue:** N+1 Query loop in Monthly Boarding Trend calculation.
- **Observed Behavior:** Loading Analytics executes 24 queries in a 12-month loop for the trend line.
- **Code Behavior:** `AnalyticsService::getBoardingTrend` loops through months 1–12 and executes 2 separate database queries per iteration.
- **Affected Files & Lines:**
  - [`AnalyticsService.php:122-147`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/app/Services/AnalyticsService.php#L122-L147)
- **Root Cause:** Procedural PHP loop over months instead of single grouped SQL aggregation.
- **Impact:** Page latency increase on shared MySQL hosting.
- **Severity:** Low (Performance Optimization).
- **Recommended Fix:** Refactor trend calculation into single aggregation query grouped by month.
