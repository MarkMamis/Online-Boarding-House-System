# OBHS Admin Insights & Reporting Separation — Implementation Plan

## 1. Overview & Objective
This implementation establishes a strict, clean Information Architecture (IA) separating three critical administrative activities in OBHS:
1. **Monitoring** ("What is happening right now?") — Live operational records, student residency census, room occupancies, and compliance monitor.
2. **Analytics** ("What patterns and trends can we learn?") — Unified cross-system telemetry, 12-month boarding trends, demographic and college distributions, and property performance metrics.
3. **Institutional Reports** ("What documented information should we generate, print, or export?") — Preset-driven institutional report generation with complete filter normalization, table previews, CSV exports, and a dedicated, isolated A4 print view.

---

## 2. Core Architectural Changes

### 2.1 Route & Controller Architecture
- **Institutional Reports Workspace:** Created `AdminInstitutionalReportController` (`/admin/institutional-reports`) handling preview, CSV export (`/admin/institutional-reports/export`), and isolated print layout (`/admin/institutional-reports/print`).
- **Unified Analytics Dashboard:** Cleaned `AdminAnalyticsController` (`/admin/analytics`) to load only analytics datasets. When legacy `report_preset` queries are received, it cleanly redirects to `/admin/institutional-reports` preserving all query parameters.
- **Monitoring Analytics Consolidation:** Updated `AdminBookingController::boardedStudents` to redirect legacy `tab=analytics` queries to `/admin/analytics` preserving filters, removing duplicate Chart.js logic from the operational monitoring view.

### 2.2 Shared Reporting Service
- Normalized filter propagation in `AdminReportingService`.
- Extended `getActiveBoardersData` to support `periodStart`, `periodEnd`, and `program` filters, ensuring exact parity between UI previews, CSV streaming, and print views.

### 2.3 Dedicated Print Engine
- Created `resources/views/admin/institutional_reports/print.blade.php`.
- Zero application chrome (no sidebar, no navbar, no buttons, no unrelated charts).
- Official Mindoro State University header, metadata grid, table records, and dual-signatory endorsement block.
- A4 landscape print CSS with `@media print` rules for automated page breaks and clean browser "Save as PDF".

---

## 3. Backward Compatibility Matrix

| Legacy / Requested URL | Destination | Behavior |
| :--- | :--- | :--- |
| `/admin/analytics` | `admin.analytics.index` | Direct 200 OK — Renders full analytics dashboard |
| `/admin/analytics?report_preset=active_boarders` | `admin.institutional_reports.index` | 302 Redirect — Preserves preset & filters |
| `/admin/analytics/export?report_preset=...` | `admin.institutional_reports.export` | 200 OK — Streams requested preset CSV |
| `/admin/boarding-monitoring/students?tab=analytics` | `admin.analytics.index` | 302 Redirect — Preserves property & academic filters |
| `/admin/institutional-reports` | `admin.institutional_reports.index` | Direct 200 OK — Preset-driven report workspace |
| `/admin/institutional-reports/print` | `admin.institutional_reports.print` | Direct 200 OK — Standalone print view |
