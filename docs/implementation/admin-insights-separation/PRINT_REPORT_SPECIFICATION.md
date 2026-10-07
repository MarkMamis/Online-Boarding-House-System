# Institutional Report Dedicated Print Specification

This specification describes the dedicated print engine implemented for formal administrative and institutional reporting at Mindoro State University.

---

## 1. Objectives & Architecture
- **Problem Solved:** Legacy print triggered `window.print()` directly on the dashboard, printing sidebars, toolbars, unrelated charts, and multi-page application clutter.
- **Solution:** A standalone route `GET /admin/institutional-reports/print` rendering `resources/views/admin/institutional_reports/print.blade.php`.

---

## 2. Layout & Print Rules

### 2.1 Excluded Elements
The print layout completely isolates the report document:
- ❌ No Admin Sidebar or Topbar
- ❌ No User Avatar / Profile Menu
- ❌ No Chatbot / Floating Widgets
- ❌ No Preset Selectors or Form Controls
- ❌ No Unrelated Charts or Graphs

### 2.2 Included Institutional Elements
- ✅ **Institutional Header:** Mindoro State University · Online Boarding House System (MinSU OBHS), official report title, and document subtitle.
- ✅ **Metadata Grid:** 4-column summary documenting Reporting Period, Filtered Boarding House, Academic College, and Generation Timestamp.
- ✅ **Key Metrics Summary Strip:** Preset-specific summary numbers (e.g., Total Active Boarders, Unique Students).
- ✅ **Clean Data Table:** A4-optimized borders, uppercase column headers, compact padding, and automatic `table-header-group` repeating on multi-page printouts.
- ✅ **Signatory & Endorsement Block:** Official "Generated & Prepared By" (Admin name & title) and "Certified Correct / Received By" (OSA / Unit Coordinator) blocks.

### 2.3 Paper Formatting & CSS
- Default `@page` sizing: `A4 landscape` with `12mm` margins.
- `page-break-inside: avoid` on table rows and signatory block.
- `@media print` rule hiding the top floating Print/Close action toolbar during physical printing or "Save as PDF".
- Auto-print invocation: Javascript `window.print()` after initial render (350ms delay).
