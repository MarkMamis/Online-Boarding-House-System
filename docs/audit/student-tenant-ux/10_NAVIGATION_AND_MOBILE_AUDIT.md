# Navigation, Sidebar & Mobile-First Usability Audit

**Audit Date:** October 18, 2026  
**Scope:** Evaluation of `<x-student-sidebar />`, Responsive Breakpoints (320px–430px), Offcanvas Drawer, and Touch Ergonomics  

---

## 1. Sidebar Navigation Audit (`resources/views/components/student-sidebar.blade.php`)

The Student Menu currently lives in a desktop fixed sidebar (`--sidebar-w: 264px`) and a mobile offcanvas slide-over drawer (`#studentSidebar`).

### Current Menu Hierarchy:

```
[Student Profile Photo + Full Name + "Student"]
-----------------------------------------------
MAIN
  [Dashboard]                (/student/dashboard or /student/tenant-dashboard)
EXPLORE
  [My Room]                  (Tenant only)
  [Browse Rooms]             (/student/rooms) - *Hidden if approved booking!*
  [Property Map]             (/student/properties/map)
SHORTCUTS
  [Notifications (13)]       (/notifications)
MY ACTIVITY
  [Requests (1)]             (/student/requests)
  [Messages]                 (/messages)
  [Feedback]                 (Tenant only)
  [Payments]                 (Tenant only)
  [Tenant Onboarding]        (/student/onboarding) - *Hidden if no approved booking!*
  [Roommates]                (#roommates hash jump on dashboard)
ACCOUNT
  [Profile]                  (/student/profile)
HELP
  [Help Center]              (/student/reports) - *Hidden if no approved booking!*
SESSION
  [Logout]                   (POST /logout)
```

---

## 2. Navigation Anomalies & Inconsistencies

1. **The Disappearing "Browse Rooms" Glitch:**
   - Lines 28–29 in `student-sidebar.blade.php`:
     ```php
     $hideBrowse = $tenantMode || !empty($hasCurrentApprovedBooking);
     ```
   - When a student has an approved booking, "Browse Rooms" disappears from their sidebar.
   - **Why this breaks UX:** If an approved reservation falls through, if the landlord becomes unresponsive, or if the student simply wants to check pricing trends or assist a classmate, they are blocked from navigating to room listings from the primary menu.
2. **Hidden Help Center for Prospective Students:**
   - Line 152: `@if(!$showLimitedNavigation)` hides "Help Center" unless the student is an active tenant or has an approved booking.
   - If an unbooked student encounters an application bug or needs assistance with academic verification, they cannot access the Help Center via the sidebar.
3. **Internal Hash Anchor Jumps:**
   - Line 136: `href="{{ route('student.dashboard') }}#roommates"` navigates to a hash anchor on the monolithic dashboard. If the student is on `/student/rooms`, clicking Roommates takes them back to the dashboard and relies on JavaScript tab switching.
4. **Administrative Section Grouping:**
   - Labels like *"Main"*, *"My Activity"*, *"Tenant Onboarding"*, and *"Workspace: Student Operations"* feel like an internal workflow management tool rather than a student lifestyle portal.

---

## 3. Mobile Usability Audit Across Breakpoints

| Viewport Width | Typical Devices | Current Behavior & Deficiencies | Usability Rating |
|---|---|---|---|
| **320px** | iPhone SE (1st gen), older Android | Severe card overflow, horizontal button clipping in tables, metric tiles wrap awkwardly into 1 column | Needs Improvement |
| **360px** | Budget Android (Samsung Galaxy A series, Xiaomi) | Heavy vertical scrolling; the home page requires ~8 full vertical swipes to reach room recommendations | Needs Improvement |
| **390px** | iPhone 12 / 13 / 14 / 15 / 16 | Layout is stable, but touch targets on table action buttons are smaller than 36px | Adequate |
| **430px** | iPhone Pro Max, Galaxy Plus / Ultra | Ample screen width, but single-column stacking leaves vast unused whitespace | Adequate |
| **768px** | Tablets / iPad Portrait | Offcanvas menu triggers, but content stretches into wide single-column cards | Adequate |
| **>= 992px** | Laptops & Desktops | Fixed 264px sidebar cleanly docks; content occupies main container | Strong |

---

## 4. Touch Target Ergonomics & Mobile Navigation Recommendation

### A. Mobile Navigation Drawer (`offcanvas-lg`)
- The hamburger button in the top navbar (`.mobile-menu-btn`) is sized at 36x36px. Recommended WCAG AAA touch target is at least **44x44px**.
- The close button (`#studentSidebarClose`) inside the drawer is responsive, but lacks an accessible `aria-expanded` state tied to the toggle button.

### B. The Case for a Mobile Bottom Navigation Bar
On modern consumer accommodation apps (Airbnb, Booking.com), a **sticky bottom navigation bar** provides 1-tap thumb access to the 4 essential destinations:

```
PROPOSED MOBILE BOTTOM APP BAR (for < 768px):
┌────────────────────────────────────────────────────────┐
│   [ 🔍 Explore ]   [ 🗺️ Map ]   [ 🛏️ My Stay ]   [ 💬 Chat ]  [ 👤 Profile ]   │
└────────────────────────────────────────────────────────┘
```

- This completely eliminates hamburger drawer friction for the 95% most common daily student tasks.
- The top header remains clean, housing only the MinSU OBHS brand mark and the notification bell.
