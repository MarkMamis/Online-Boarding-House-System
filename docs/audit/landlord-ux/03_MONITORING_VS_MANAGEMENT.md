# OBHS Landlord Portal: Monitoring vs Management vs Insights

**Document ID:** `03_MONITORING_VS_MANAGEMENT.md`  
**Date:** October 18, 2026  
**Auditor:** Principal Product Designer & Business Process Analyst  
**Focus:** Architectural Taxonomy, Role Clarity, and Behavioral Boundaries.

---

## 1. The Core Conceptual Framework

In a property management workspace for non-technical owners, mixing observation (monitoring) with execution (management) and historical analysis (insights) creates cognitive fatigue. Owners struggle to answer the fundamental daily question: *"Do I need to do something, or am I just looking at records?"*

The OBHS Landlord Portal must strictly separate these three operational modes:

| Category | Primary Question Answered | Interaction Model | Record Mutability | Examples in OBHS |
| :--- | :--- | :--- | :--- | :--- |
| **MONITORING** | *"What is happening right now?"* | Read-only inspection, real-time counters, status lists, filterable directories | **Zero state mutation.** No submit buttons, no status toggles, no destructive actions. | Room vacancy count, current active boarders, move-in progress indicators, compliance permit validity. |
| **MANAGEMENT** | *"What do I need to do?"* | Task-oriented queues, decision cards, approval forms, multi-step wizards | **Mutates records.** Updates status enums, writes ledger rows, uploads documents. | Approve/reject booking, verify tenant payment proof, sign lease agreement, add a room, toggle maintenance. |
| **INSIGHTS** | *"How is my property performing over time?"* | Aggregate charts, trend curves, historical comparisons, rate calculations | **Historical aggregations.** Read-only calculations across date ranges. | 6-month occupancy curve, monthly rental collection rate, student review sentiment distribution. |

---

## 2. Current State Classification Audit

We inspected all 15 functional modules in the current landlord codebase against this taxonomy:

| Current Page & Route | Current Sidebar Placement | Observed Functionality & Controls | Violation / Misclassification | Recommended Category |
| :--- | :--- | :--- | :--- | :--- |
| `landlord.dashboard` (`/landlord/dashboard`) | `Overview` | Combines real-time metrics, quick module links, 2 Chart.js analytics charts, a properties table, recommended rooms, and inline message replies! | **Severe Confusion:** Blends Monitoring (occupancy), Insights (trend charts), and Management (inline message reply forms, embedded add-property modal). | **Home / Action Center** (Clean "Needs Attention" queue + summary) |
| `landlord.monitoring.properties` (`/landlord/monitoring/properties`) | `Monitoring` | Read-only table of properties, room count, occupancy rate, and active boarders. | **Clean Monitoring:** Properly restricted to read-only status. | **Monitoring** |
| `landlord.monitoring.compliance` (`/landlord/monitoring/compliance`) | `Monitoring` | Shows document verification and expiration status (`LandlordDocument`). | **Clean Monitoring**, but duplicates `landlord.documents.index` which also shows document statuses. | **Monitoring** (Consolidate view with Documents) |
| `landlord.tenants.index` (`/landlord/tenants`) | `Monitoring` | Displays approved bookings with student names, room numbers, and check-in dates. | **Clean Monitoring**, but titled "Tenants & Stays" and lists tenants who have not finished onboarding. | **Monitoring** (Active Residents Directory) |
| `landlord.properties.index` (`/landlord/properties`) | `Management` | List of properties with "Add Property", "Edit", "Delete", and links to rooms. | **Management:** Contains full CRUD actions. | **Management** |
| `landlord.rooms.index` (`/landlord/rooms`) | `Management` | Flat list of all rooms across all properties with edit/delete buttons. | **Management:** Contains room management controls. | **Management** |
| `landlord.bookings.index` (`/landlord/bookings`) | `Management` | Table of pending and past requests with "Approve" and "Reject" buttons. | **Pure Management:** Direct state transition workflow. | **Management** |
| `landlord.onboarding.index` (`/landlord/onboarding`) | `Management` | List of approved applicants in move-in pipeline with review links. | **Management:** Step-by-step document review and lease execution. | **Management** |
| `landlord.payments.index` (`/landlord/payments`) | `Management` | List of bookings with monthly dues, overdue alerts, and "Manage" links. | **Blended:** Top cards are Monitoring (Total Expected, Paid, Pending), while table actions are Management. | **Finance** (Subdivided into Monitoring overview & Management actions) |
| `landlord.documents.index` (`/landlord/documents`) | `Management` | Form to upload/replace business permits and safety certificates. | **Management:** Uploads files and creates records. | **Management / Compliance** |
| `landlord.maintenance.index` (`/landlord/maintenance`) | `Management` | Table of rooms under maintenance with "Complete" button, plus form to set room. | **Management:** Updates room status. | **Management** |
| `landlord.leave_requests.index` (`/landlord/leave_requests`) | `Management` | Table of student move-out notices with "Approve" and "Reject" buttons. | **Management:** Updates booking check-out dates. | **Management** |
| `landlord.analytics.index` (`/landlord/analytics`) | `Insights` | Monthly revenue, expected rent, weekly booking charts, and occupancy cards. | **Pure Insights**, but occupancy metrics duplicate the Monitoring page. | **Insights** |
| `landlord.feedback.index` (`/landlord/feedback`) | `Insights` | Student ratings and reviews with AI sentiment analysis button. | **Insights:** Historical reputation metrics. | **Insights** |

---

## 3. The Problem with the Current "Monitoring" Implementation

In commit `2d96303` (`feat: separate landlord monitoring and management`), two dedicated routes were created:
1. `GET /landlord/monitoring/properties` (`LandlordMonitoringController@properties`)
2. `GET /landlord/monitoring/compliance` (`LandlordMonitoringController@compliance`)

While well-intentioned, this implementation introduced UX friction:
- **Redundant Duplication of Data:** The landlord now has to visit `/landlord/monitoring/properties` to see vacancy, then visit `/landlord/properties` to edit a property, then visit `/landlord/rooms` to see pricing! Three separate pages exist to inspect the same physical house.
- **Compliance Status Split:** `/landlord/monitoring/compliance` shows whether a permit is approved or expired, but has **no upload button**. To fix an expired permit, the landlord must navigate to `/landlord/documents` ("Document Renewals").
- **Recommendation:** Do not create separate read-only duplicate pages that force landlords to bounce between a "viewing" page and an "editing" page. Instead, provide **clear Monitoring headers/badges within canonical pages**, and reserve dedicated Monitoring for high-level operational dashboards.

---

## 4. Clear Interaction Contracts per Category

To prevent regression during future development, every Landlord UI screen must declare its primary mode:

### Monitoring Contract:
- **Visual Marker:** Soft background pill badges, read-only data cards, census numbers.
- **Allowed Controls:** Date filters, property dropdown filter, search input, export/print.
- **Forbidden Controls:** Submit buttons, POST forms, destructive modals, inline editors.

### Management Contract:
- **Visual Marker:** Actionable buttons (MinSU green `#14532d` for primary actions), status pills with clear verbs ("Review", "Confirm", "Resolve").
- **Allowed Controls:** Form inputs, file dropzones, signature pads, approval/rejection modals.
- **Focus Rule:** One primary call-to-action (CTA) per card or drawer. Never present 4 competing primary buttons.

### Insights Contract:
- **Visual Marker:** Chart canvases, percentage deltas, comparison bars.
- **Allowed Controls:** Preset date range toggles (Last 30 Days, Last 6 Months, This Academic Term).
- **Integrity Rule:** Metrics must directly derive from authoritative database tables (`tenant_payments`, `bookings`, `rooms`) without speculative multipliers.
