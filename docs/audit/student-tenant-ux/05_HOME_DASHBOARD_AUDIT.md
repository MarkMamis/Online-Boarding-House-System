# Student Home Experience Audit: From Admin Control Panel to Context-Aware StayHub

**Audit Date:** October 18, 2026  
**Scope:** In-Depth Inspection of `student/dashboard.blade.php`, `student/tenant-dashboard.blade.php`, and `layouts/student_dashboard.blade.php`  

---

## 1. The Current Design Problem

The Student Home experience currently models an **administrative operational console** rather than a consumer accommodation discovery and living platform.

```
+--------------------------------------------------------------------------+
| Top Navbar: [MINSU LOGO] Student Portal        Workspace: Student Operations [🔔 13] [⚙] |
+--------------------------------------------------------------------------+
| Welcome, Juan Dela Cruz!                                                 |
| [13 unread messages]  [1 pending requests]  [0 report responses]         |
+--------------------------------------------------------------------------+
| [ Available Rooms: 14 ] [ Pending Requests: 1 ] [ Active Boardings: 0 ] [ Messages: 3 ] |
+--------------------------------------------------------------------------+
| Map Preview:                                                             |
| [ Mini Leaflet Map with "TBD" pins ]    | Visible Listings: Approved nearby |
|                                         | Coverage: 1+ active properties   |
|                                         | Tip: Tap a price pin...          |
+--------------------------------------------------------------------------+
| Next Action: Browse Rooms                                                |
| Explore accredited houses near MinSU.                      [Browse Rooms]|
+--------------------------------------------------------------------------+
| Recent Activity                         | Recommended Rooms              |
| • Booking requested (2h ago)            | [Room 101 - Green Haven]       |
| • Message received (1d ago)             | Capacity: 4 • ₱1,800/mo        |
|                                         | Inclusions: WiFi, Water        |
|                                         | [View More]                    |
+--------------------------------------------------------------------------+
| (Hidden below or tabbed): Browse Rooms, Map, Requests, Messages,         |
| Onboarding, Roommates, Reports, Notifications, Profile                   |
+--------------------------------------------------------------------------+
```

---

## 2. Element-by-Element Decomposition & Audit

### A. The Top Navbar Meta Label
- **Current Code:**
  ```html
  <div class="top-meta d-none d-lg-block text-end">
      <div>Workspace</div>
      <div class="fw-semibold">Student Operations</div>
  </div>
  ```
- **Audit:**
  - *"Student Operations"* is an administrative governance term copied directly from ERP systems or property management dashboards.
  - A student does not operate an enterprise.
- **Recommendation:** Remove or replace with user identity and campus affiliate badge: *"MinSU Calapan • Student Resident"*.

### B. The 4 KPI Metric Tiles
- **Current Elements:**
  1. `Available Rooms` (e.g. `14`): Aggregated count of all vacant rooms across all approved boarding houses.
  2. `Pending Requests` (e.g. `1`): Count of student's own pending reservations.
  3. `Active Boarding Request(s)` (e.g. `0` or `1`): Count of non-ended approved bookings.
  4. `Messages` (e.g. `3`): Combined sent + received message count.
- **Audit:**
  - **Available Rooms count is useless to a consumer:** Knowing there are "14 available rooms in the entire city" does not help a female agriculture student who needs a ₱1,500 bed-spacer within 500m of Victoria campus.
  - **Combining dissimilar metrics:** Putting a macro marketplace stat (`Available Rooms: 14`) right beside a personal micro task (`Pending Requests: 1`) confuses system analytics with personal action items.
  - **Messages total count is vanity:** A student needs to know if they have *unread* messages, not that their lifetime message count is 47.
- **Recommendation:** **Eliminate the 4 KPI metric grid entirely.** Replace with a dynamic status bar tailored to the student's active lifecycle.

### C. Map Preview on Home
- **Current Code:** An embedded mini-map (`#propertiesMapMini`) loaded via AJAX (`/student/properties/map-data`) with three side cards: *"Visible Listings"*, *"Coverage"*, *"Tip"*.
- **Audit:**
  - Code inspection shows markers call `compactPriceLabel(p.price_min, p.price_max)`. When price bounds are not set on the property model, it prints `"TBD"` or `"Price TBD"`.
  - The side text *"Coverage: 1+ active properties"* sounds like a telecommunications coverage report or an internal admin readiness metric.
  - The map preview competes directly with the full *"Property Map"* page (`/student/properties/map`), duplicating initialization code and tile requests.
- **Recommendation:** Remove the mini-map from Home. Provide an elegant search bar with a *"View on Campus Map"* button that directs to the dedicated, interactive property map.

### D. Next Action Card
- **Current Code:** A wide card: *"Next action: Browse rooms — Explore accredited houses near MinSU."* with a green *"Browse Rooms"* pill button.
- **Audit:**
  - While intended as a guide, it sits redundantly between the map preview and the recommended rooms list. If the student is already looking at recommended rooms, telling them in a separate card to "Browse Rooms" is repetitive noise.
- **Recommendation:** Fold the next action into the hero header (e.g. a personalized contextual card).

### E. Recent Activity vs. Notifications
- **Current Code:** A 6-column card listing recent events from bookings, messages, onboarding, and reports, using custom icon pills.
- **Audit:**
  - This 100% duplicates `/notifications`!
  - Clicking items triggers JavaScript hash changes (`data-panel-jump="requests"`) that fight with normal page navigation.
- **Recommendation:** Remove the redundant activity feed from Home. The notification bell in the top navbar provides direct, standard access to recent alerts.

---

## 3. Redundancy Matrix: What Stays, Moves, or Disappears

| Home Element | Current Behavior | Verdict | Target Destination |
|---|---|---|---|
| **KPI: Available Rooms** | Static count of total system rooms | **Remove** | Belongs in Admin Analytics, not Student Portal |
| **KPI: Pending Requests** | Number of pending requests | **Contextualize** | Becomes an active banner only when requests exist |
| **KPI: Active Boardings** | Number of approved stays | **Contextualize** | Becomes "My Stay" card when residing |
| **KPI: Messages Total** | Lifetime message count | **Simplify** | Replaced by unread badge on top navbar bell/chat |
| **Map Preview Mini** | Small Leaflet map with "TBD" pins | **Relocate** | Move to dedicated Explore Map (`/student/properties/map`) |
| **Coverage & Tip Cards** | "1+ active properties" text | **Remove** | Administrative placeholder copy |
| **Next Action Banner** | Generic browse CTA | **Contextualize** | Integrate into dynamic Hero greeting |
| **Recent Activity List** | List of recent events | **Remove** | Redundant with Notifications Center |
| **Recommended Rooms** | 3 vertically stacked room cards | **Elevate & Expand** | Becomes primary discovery grid on Home for searching students |
| **Completed Stay Banner**| Warning box to rate past stay | **Streamline** | Compact review card in Stay History or dismissible Home alert |

---

## 4. The Recommended Solution: A Single Context-Aware Home

Instead of maintaining two separate dashboards (`dashboard.blade.php` and `tenant-dashboard.blade.php`), the Student Home should be **one unified, context-aware template** that dynamically switches its primary viewport based on the student's authoritative backend state:

```
┌────────────────────────────────────────────────────────────────────────┐
│ State 1: SEARCHING (No active request, no current stay)                │
│ -> Hero: Search by location, budget, or campus walking distance        │
│ -> Curated Categories: "Near MinSU Gate", "Under ₱1,800", "Solo Rooms" │
│ -> Recommended & Available Room Grid                                   │
├────────────────────────────────────────────────────────────────────────┤
│ State 2: PENDING APPROVAL (Request submitted, awaiting landlord)       │
│ -> Prominent Status Banner: "Request for Room 2B at Green Haven"       │
│ -> Landlord Response Timer & Contact Landlord Shortcut                 │
│ -> Secondary: "Keep browsing alternative rooms"                        │
├────────────────────────────────────────────────────────────────────────┤
│ State 3: PREPARING MOVE-IN (Booking approved, onboarding in progress)  │
│ -> Actionable 4-Step Checklist: Documents -> Lease -> Deposit -> Move  │
│ -> Landlord Contact Card & Move-in Date Countdown                      │
├────────────────────────────────────────────────────────────────────────┤
│ State 4: ACTIVE RESIDENT (Onboarding complete, living in room)         │
│ -> Residence Card: Boarding House Name, Room Number, Landlord Phone    │
│ -> Monthly Rent Status: "Due on Nov 05 (₱2,000)" with "Pay Now" CTA    │
│ -> Quick Resident Actions: Roommates, House Rules, Submit Leave, Chat  │
├────────────────────────────────────────────────────────────────────────┤
│ State 5: COMPLETED STAY (Move-out complete)                            │
│ -> "How was your stay at [House Name]?" Compact 1-click star rating    │
│ -> "Find your next room for next semester" discovery section           │
└────────────────────────────────────────────────────────────────────────┘
```
