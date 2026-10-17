# Active Tenant Experience: Resident Living, Payments & Tenancy Lifecycle

**Audit Date:** October 18, 2026  
**Scope:** Evaluation of Resident Experience (`student/tenant-dashboard.blade.php`), Monthly Payments (`student/payments/index.blade.php`), and Leave Requests  

---

## 1. The Current Active Resident Portal

When a student finishes onboarding for an approved booking and is currently residing (`check_out > today`), `AuthController@studentDashboard` detects `$activeCompletedOnboarding` and automatically redirects them to:

`GET /student/tenant-dashboard` (`resources/views/student/tenant-dashboard.blade.php`)

This is one of the most functional sections in OBHS, but its visual hierarchy is severely unbalanced.

```
CURRENT TENANT DASHBOARD STRUCTURE (367 lines):
┌────────────────────────────────────────────────────────────────────────┐
│ Header: Tenant Hub • Tenant Dashboard                                  │
│ Property Name • Room Number • Dates (M d, Y to M d, Y)                 │
│ Status Badge: "Payment up to date" • Landlord Name & Contact           │
├────────────────────────────────────────────────────────────────────────┤
│ 4 Metric Tiles:                                                        │
│ [ Monthly Rent: ₱2,000 ] [ Next Due: Nov 05 ] [ Roommates: 2/4 ] [ Unread: 0 ] │
├────────────────────────────────────────────────────────────────────────┤
│ [ LEFT (8 cols): Giant Google Map Iframe ] | [ RIGHT (4 cols): Quick Actions ] │
│ Embedded 16:9 Google Map with address      | • Open My Room                   │
│ and "Open in Google Maps" button           | • Messages                       │
│                                            | • Help Center                    │
│                                            | • Room Feedback                  │
│                                            | • View Onboarding Record         │
├────────────────────────────────────────────────────────────────────────┤
│ [ Roommates Card (6 cols) ]                | [ House Rules & Inclusions (6 cols) ] │
│ List of active roommates in same room      | Rules list and Building amenities │
├────────────────────────────────────────────────────────────────────────┤
│ [ Feedback Overview (6 cols) ]             | [ Reports Overview (6 cols) ]    │
│ Submissions, Avg Rating, Positive Sentiment| Total, Pending, In Progress,     │
│ and recent feedback comments list          | Resolved tickets + Unread alert  │
├────────────────────────────────────────────────────────────────────────┤
│ [ Leave Requests Panel (Full width table) ]                            │
│ Form: Date, Reason, Submit + Table of past leave requests with Cancel  │
└────────────────────────────────────────────────────────────────────────┘
```

---

## 2. Critical Flaws in the Current Resident Dashboard

### A. The Giant Map Iframe (Waste of Above-the-Fold Space)
- Taking up **two-thirds of the desktop screen width** (and the very first item beneath the metric tiles on mobile) is an embedded Google Map iframe showing where the boarding house is located.
- **The resident already lives there.** They sleep in the building every night. Displaying an interactive map iframe in prime real estate wastes space that should be devoted to immediate resident needs: rent balance, maintenance updates, landlord contact, and house announcements.

### B. Feedback NLP Sentiment Metrics
- The "Feedback Overview" section shows cards for: *"Your submissions"*, *"Average rating"*, and *"Positive sentiment: 2"*.
- "Positive sentiment" is an internal administrative NLP classification metric used by university compliance staff. Exposing sentiment counters to a college student who just wants to review their room is confusing and unnatural.

### C. Fragmentation of Payments
- The dashboard displays *"Monthly rent: ₱2,000"* and *"Next due date: Nov 05"*, but clicking to actually make a payment is not among the Quick Actions buttons!
- To submit rent, the student must find "Payments" in the sidebar (`/student/payments`), which opens a separate view (`student/payments/index.blade.php`).
- Payment and tenancy should be directly accessible with a single click: *"Pay Rent (₱2,000)"*.

### D. Table-Heavy Mobile Layout
- Both the **Leave Requests** and **Reports** sections rely on desktop HTML `<table>` elements with multiple columns (Date, Status, Reason, Action). On viewports under 400px, these tables cause horizontal scrollbars or cramped, clipped text.

---

## 3. What an Active Tenant Actually Needs Daily

Through user research and student resident personas, an active tenant's priorities rank as follows:

1. **Immediate Rent & Due Date Status:**
   Am I paid up? When is my next payment due? Where do I upload my GCash screenshot?
2. **Direct Landlord Contact:**
   Tap-to-call or tap-to-SMS the landlord, plus direct in-app messaging.
3. **Roommate Awareness & Safety:**
   Who is currently assigned to my room? What program/year are they in?
4. **House Rules Reference:**
   What are the gate curfew hours? Can I bring visitors on weekends? Can I cook in the kitchen?
5. **Weekend Leave Requests:**
   Quick 2-tap leave submission so the landlord knows the student is safely home for the weekend.
6. **Lease Document Access:**
   Immediate PDF download of their signed lease agreement for university scholarship stipend verification.

---

## 4. Proposed Redesign: The "My Stay" Resident Experience

```
┌────────────────────────────────────────────────────────────────────────┐
│ 🏠 MY CURRENT STAY                                                     │
│ Villa Milagrosa Boarding House • Room 102 (Bed #2)                     │
│ Active Lease: Aug 15, 2026 – May 30, 2027                              │
│                                                                        │
│ Landlord: Mrs. Milagros Santos • 📞 Call Landlord  💬 Message          │
├────────────────────────────────────────────────────────────────────────┤
│ 💳 RENT & BILLING SUMMARY                                              │
│ [ Status: Payment Due in 4 Days ]                                      │
│ Amount: ₱1,800.00 • Due Date: Nov 05, 2026                             │
│ Preferred Channel: GCash (0917-XXX-XXXX)                               │
│ [ ⚡ Submit Monthly Rent Payment ]   [ 📄 View Payment History ]        │
├────────────────────────────────────────────────────────────────────────┤
│ 👥 ROOMMATES IN ROOM 102                                               │
│ • Juan Dela Cruz (You) — BS Information Technology (2nd Year)          │
│ • Mark Santos — BS Agriculture (3rd Year)                              │
│ (2 of 2 beds occupied)                                                 │
├────────────────────────────────────────────────────────────────────────┤
│ 📋 RESIDENT ACTIONS (Tabs or Clean Cards)                              │
│ [ 🚗 Submit Leave Request ]   [ 📜 View Signed Lease (PDF) ]           │
│ [ 🛡️ House Rules & Curfew ]   [ ⭐ Room Review & Feedback ]            │
└────────────────────────────────────────────────────────────────────────┘
```
