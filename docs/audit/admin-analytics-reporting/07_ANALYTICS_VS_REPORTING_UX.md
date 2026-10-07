# 07 — Analytics vs. Reporting Information Architecture & UX Audit

## Executive Summary
This document analyzes the user experience (UX) and Information Architecture (IA) collision on `/admin/analytics`, where general system-wide exploratory dashboards and specific institutional reporting tools are combined on a single page.

---

## 1. The Core UX Conflict: Dual User Mental Models

An administrator accessing `/admin/analytics` typically arrives with one of two fundamentally distinct intents:

| Dimension | Mental Model A: High-Level Analytics & Monitoring | Mental Model B: Institutional Report Generation |
|---|---|---|
| **User Objective** | "How is the boarding house ecosystem performing this semester? What are the occupancy trends, booking lifecycles, and college distributions?" | "I need to generate and print the official MinSU 'Active Boarded Students Report' for property X to submit to the Office of Student Affairs." |
| **Primary Interaction** | Scanning KPI cards, toggling trend years, inspecting doughnut charts, filtering aggregate demographics. | Selecting a report preset, setting strict reporting period boundaries, previewing the tabular roster, downloading CSV, and printing an official document. |
| **Expected Output** | Interactive visual dashboards and analytical charts. | Isolated, clean, print-ready document or tabular export with official institutional headers. |
| **Current Reality** | Forced to scroll past report preset pills and large tabular previews. | Forced to view, scroll past, and accidentally print 3 screen-lengths of unrelated dashboard charts and KPI cards. |

---

## 2. Why Switching Presets Confuses Administrators

When an administrator clicks an institutional report pill (e.g., *Active Boarded Students Report*):
1. The URL updates (`?report_preset=active_boarders`).
2. The page reloads.
3. **The top 70% of the viewport (KPI cards, Trend line chart, Booking doughnut, Demographics bar charts, Gender breakdown) remains 100% identical.**
4. The administrator must scroll ~800 pixels down to see that only the tabular preview card changed.
5. Clicking "Print Report" in the top header prints the entire page rather than the isolated report.

---

## 3. Evaluation of Architectural Options

### Option A: Status Quo (Combined Page with Improved Separation)
- **Concept:** Keep both on `/admin/analytics`, but move Institutional Reports into a distinct lower section or collapsible card with localized export/print controls.
- **Pros:** Zero new routes; minimal code movement.
- **Cons:** High cognitive load; print isolation requires complex CSS hiding rules; page weight remains high (all queries execute on every view).

### Option B: Tabbed Interface on `/admin/analytics` (Recommended Short-to-Mid Term)
- **Concept:** A top-level tab switcher on `/admin/analytics`:
  - Tab 1: **Executive Analytics & Trends** (KPIs, Charts, Demographics, Compliance).
  - Tab 2: **Institutional Reports Generator** (Preset selector, Filter bar, Dedicated Table Preview, CSV Export, Dedicated Print View trigger).
- **Pros:** Clean separation of concerns; maintains existing route `/admin/analytics`; prevents visual clutter; allows conditional backend query execution based on active tab.
- **Cons:** Requires minor Blade tab state management.

### Option C: Dedicated Portal Routes (Recommended Long Term)
- **Concept:** Separate routes under Admin Navigation:
  - `Insights → Analytics Dashboard` (`/admin/analytics`)
  - `Insights → Institutional Reports` (`/admin/reports/institutional`)
- **Pros:** Maximum architectural purity and role access clarity; independent controllers and cache lifecycles.
- **Cons:** Introduces new route and navigation item in Admin Portal sidebar.

---

## 4. Final UX Recommendation
Adopt **Option B (Tabbed Interface)** or **Option C (Separate Routes)** in combination with a **Dedicated Printable Blade Template** (`/admin/analytics/print?report_preset=...`).
