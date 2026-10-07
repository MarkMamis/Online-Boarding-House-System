# Analytics Consolidation Matrix

This matrix documents the consolidation of redundant visualizations and analytics across the OBHS Admin Portal.

| Visualization / Feature | Monitoring Source (`/admin/boarding-monitoring/students`) | Insights Source (`/admin/analytics`) | Metric Parity | Duplicate Status | Resolution / Owner |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **Student Boarding Trend** | `chartBoardingTrend` (Line Chart) | `chartTrend` (Monthly active boarders vs new bookings) | Same overlap formula | Redundant presentation | Consolidated into **Insights → Analytics**. Monitoring tab redirects with filters. |
| **Boarding House Distribution** | `chartBhDistribution` (Bar Chart & Ranked List) | `occupancy` table and capacity charts | Parity | Redundant presentation | Consolidated into **Insights → Analytics** with deep links to property records. |
| **College Distribution** | `chartCollegeDistribution` (Bar Chart) | `demographics.colleges` | Exact parity | Duplicate | Owned authoritatively by **Insights → Analytics**. |
| **Program Distribution** | `chartProgramDistribution` (Bar Chart) | Dynamic catalog & academic drilldowns | Parity | Duplicate | Consolidated into **Insights → Analytics**. |
| **Gender Demographics** | `chartGenderDonut` (Doughnut Chart) | `demographics.boarder_gender` | Exact parity | Duplicate | Owned authoritatively by **Insights → Analytics**. |
| **Operational KPIs (Active Boarders, Properties, Occupied Rooms)** | Monitoring KPI strip | Analytics KPI Cards | Authoritative query | Essential Operational Context | Retained in **Monitoring** as instant status; visualized in **Analytics** with contextual breakdowns. |
| **Institutional Report Generator** | N/A (previously embedded in Analytics) | Dedicated Workspace (`/admin/institutional-reports`) | 6 presets | Cluttered Analytics | Separated into dedicated **Institutional Reports** workspace. |
