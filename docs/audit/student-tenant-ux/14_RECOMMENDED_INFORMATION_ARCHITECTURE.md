# Recommended Information Architecture & Customer Navigation

**Audit Date:** October 18, 2026  
**Scope:** Proposed Customer-First Sitemap, Navigation Hierarchy, and Route Compatibility  

---

## 1. Core Architectural Shift

The current system forces students through an administrative paradigm:
`Dashboard (Monolith) -> Requests -> Tenant Onboarding -> Payments -> Help Center`.

The proposed architecture organizes the student experience around the **Accommodation Lifecycle**:

```
STUDENT ACCOMMODATION LIFECYCLE
┌─────────────┐     ┌─────────────┐     ┌─────────────┐     ┌─────────────┐     ┌─────────────┐
│ 1. EXPLORE  │ ──> │ 2. BOOK     │ ──> │ 3. MOVE-IN  │ ──> │ 4. RESIDE   │ ──> │ 5. DEPART   │
│ Search, Map,│     │ Request,    │     │ Documents,  │     │ Rent, Room, │     │ Review,     │
│ Details     │     │ Await Host  │     │ Lease, Dep. │     │ Leave, Chat │     │ New Search  │
└─────────────┘     └─────────────┘     └─────────────┘     └─────────────┘     └─────────────┘
```

---

## 2. Proposed Sitemap & Navigation Hierarchy

### Primary Destinations (Customer-Centric):

1. **Home (`/student/dashboard`):**
   - Context-aware entry point that renders the exact view needed for the student's active lifecycle stage.
   - Searching student -> Discover rooms & campus search.
   - Pending student -> Track reservation status banner + continue browsing.
   - Approved student -> Move-in preparation progress bar (Documents -> Lease -> Deposit).
   - Resident tenant -> My Stay overview (Rent due, landlord contact, roommates, quick pay).

2. **Explore Rooms (`/student/rooms`):**
   - The consumer accommodation marketplace.
   - Search bar (location, boarding house name).
   - Filter chips: Price range, Whole Room vs Bed-spacer, Gender/Room Type, Amenities.
   - High-quality visual listing cards.
   - Quick toggle to interactive Campus Map view.

3. **Campus Map (`/student/properties/map`):**
   - Full interactive map with real property price pins (e.g. `₱1.8k`, never `"TBD"`).
   - Side listing drawer linking directly to matching rooms.
   - MinSU campus landmarks (Gates, Colleges, Main Road).

4. **My Bookings & Stays (`/student/bookings` or `/student/stays`):**
   - Consolidates the currently fragmented sections into 3 clear tabs:
     - **Active / Current Stay:** Room details, roommates, landlord contact, house rules, signed lease download.
     - **Pending & Upcoming:** Active reservation requests, approval status, and move-in checklists.
     - **Past Stays:** Completed stay records, official receipts, and star review submission.

5. **Messages (`/messages`):**
   - Responsive master-detail chat interface.
   - Real-time conversation threads with landlords.

6. **Notifications (`/notifications`):**
   - Alert center categorized into: System, Booking Updates, Billing Due, and Verification.

7. **Profile (`/student/profile`):**
   - Personal credentials, verified MinSU ID badge, change password, and notification preferences.

8. **Help & Safety (`/student/reports`):**
   - Private support tickets for administrative concerns, paired with prominent **MinSU Emergency Security Hotline numbers**.

---

## 3. Sidebar Navigation: Before vs. After

```
CURRENT SIDEBAR (Admin-Heavy)              PROPOSED SIDEBAR (Customer-First)
┌──────────────────────────────┐          ┌──────────────────────────────┐
│ MAIN                         │          │ MinSU StayHub                │
│ • Dashboard                  │          ├──────────────────────────────┤
│                              │          │ • Home                       │
│ EXPLORE                      │          │ • Explore Rooms              │
│ • Browse Rooms (Hidden if app)│         │ • Campus Map                 │
│ • Property Map               │          ├──────────────────────────────┤
│                              │          │ • My Stays & Bookings        │
│ SHORTCUTS                    │          │ • Messages [Badge]           │
│ • Notifications (13)         │          │ • Notifications [Badge]      │
│                              │          ├──────────────────────────────┤
│ MY ACTIVITY                  │          │ • Profile & Verification     │
│ • Requests (1)               │          │ • Help & Safety              │
│ • Messages                   │          ├──────────────────────────────┤
│ • Payments (Tenant only)     │          │ • Logout                     │
│ • Tenant Onboarding (Hidden) │          └──────────────────────────────┘
│ • Roommates (Hash anchor)    │
│                              │
│ ACCOUNT                      │
│ • Profile                    │
│                              │
│ HELP                         │
│ • Help Center                │
└──────────────────────────────┘
```

---

## 4. Route Backward Compatibility

All existing routes and controller actions are preserved without breaking changes:
- Existing route names (`student.dashboard`, `student.rooms.index`, `student.bookings.index`, `student.onboarding.show`, `messages.index`) remain intact.
- Legacy URLs bookmarked by students or linked from email notifications continue to resolve seamlessly.
