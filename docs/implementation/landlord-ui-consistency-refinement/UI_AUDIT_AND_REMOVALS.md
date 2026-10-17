# Landlord UI Audit & Redundancy Removals Matrix

## 1. Overview
This audit catalogued all non-essential metrics, repetitive KPI cards, redundant navigation shortcuts, and nested card shells across the 9 Landlord Portal management and monitoring views.

The fundamental principle applied: **"Not every page needs a dashboard."** Landlords navigating to operational pages need to execute tasks and inspect records directly without scrolling past giant zero counters or redundant shortcuts.

---

## 2. Redundancy Removal Matrix

| Page | Control / Element Removed | Original Purpose | Why Removed / Streamlined | Alternative Access |
|---|---|---|---|---|
| **Current Tenants** | "View Boarding Requests" header button | Quick link to requests | Redundant with primary sidebar navigation | Left sidebar "Boarding Requests" |
| **Current Tenants** | 5-card metric stack (`tenant-summary`: Total, Paid, Pending, Overdue, Active) | Display count metrics | Giant cards pushed tenant records below fold; stacked into 5 full-width cards on mobile | Compact `.landlord-summary-strip` displaying resident count and pending/overdue notice |
| **Current Tenants** | Heavy `.tenants-shell` gradient wrapper | Visual container | Extra nested padding reduced usable mobile width | Replaced with clean `.landlord-page-shell` and `.landlord-card` |
| **Property & Room Monitoring** | "Manage Properties" header button | Shortcut to properties | Redundant with sidebar | Sidebar "Portfolio > Properties" |
| **Property & Room Monitoring** | "Room Directory" header button | Shortcut to rooms | Redundant with sidebar | Sidebar "Portfolio > Room Directory" |
| **Property & Room Monitoring** | 6-card metric grid (Properties, Total Rooms, Available Slots, Available Rooms, Maintenance, Occupancy) | Metric overview | Monopolized screen height above actual monitoring tables | Unified occupancy summary strip (`80% Occupancy · X slots · Y maintenance`) |
| **Property & Room Monitoring** | Horizontally overflowing desktop `<table>` on mobile | Show room details | Table forced horizontal scroll on 320px–430px viewports | Responsive layout: compact desktop table + responsive mobile card rows |
| **Compliance Monitoring** | "Document Renewals & Uploads" header button | Shortcut to document center | Redundant with sidebar | Sidebar "Account > Documents" |
| **Compliance Monitoring** | "View Submission Queue" banner button | Shortcut to documents | Duplicate link within same view | Single clear "Manage Documents" action |
| **Compliance Monitoring** | 6-card metric grid (Required, Approved, Expiring, Expired, Review, Missing) | Document counts | Overwhelmed read-only monitoring view | Clean status strip in page header (`Accredited · X of Y Verified · Z expiring soon`) |
| **Properties** | 4-card metric stack (Properties, Total Rooms, Vacant Rooms, Mapped) | Portfolio count | Delayed access to property cards | Header summary strip (`X Properties · Y Rooms`) |
| **Properties** | Dominating 280px default map | Display map coordinates | Pushed boarding house cards below viewport | Map made secondary and collapsible via "View Map" toggle; initializes cleanly on demand |
| **Properties** | 4 competing card buttons (Overview, Manage Rooms, Add Room, More) | Actions on property | Button sprawl cluttered card footers | Single primary `Manage Property` button + clean dropdown for Add Room, Edit, Delete |
| **Room Directory** | "Manage Properties" header button | Shortcut | Redundant with sidebar | Sidebar "Portfolio > Properties" |
| **Room Directory** | 3-card metric tiles (Available, Occupied, Maintenance) | Room status metrics | Pushed directory cards down | Integrated header summary strip |
| **Room Directory** | Repeated "View Property" on every card | Property link | Property is already identified in card subtitle | Property subtitle link / property dropdown |
| **Boarding Requests** | 4-card summary stack (Pending, Approved, Rejected, Cancelled) | Display counters | Showed four large zero boxes when queue was empty | Integrated header summary strip (`X Requests · Y Action Required`); clean empty state |
| **Move-in Management** | "View Tenants" header button | Shortcut | Redundant with sidebar | Sidebar "Management > Current Tenants" |
| **Move-in Management** | 4-counter summary boxes | Onboarding counts | Staggered clutter above active move-in queue | Header summary strip with actionable count |
| **Departure Notices** | "View Active Tenants" header button | Shortcut | Redundant with sidebar | Sidebar "Management > Current Tenants" |
| **Departure Notices** | 4-counter summary boxes | Leave counts | Cluttered view when 0 requests existed | Header summary strip with active notice count |
| **Departure Notices** | Large always-visible policy box | Explain departures | Took up permanent screen real estate | Collapsible "How scheduled departures work" helper drawer |
| **Room Maintenance** | "View All Rooms" header button | Shortcut | Redundant with sidebar | Sidebar "Portfolio > Room Directory" |
| **Room Maintenance** | 4-card metric stack | Room status counts | Pushed maintenance controls down | Compact header summary strip |
| **Room Maintenance** | Large always-visible policy box | Notice on marketplace | Cluttered maintenance workflow | Collapsible "How maintenance mode works" policy helper |
