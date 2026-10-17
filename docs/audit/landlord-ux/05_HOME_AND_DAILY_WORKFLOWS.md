# OBHS Landlord Portal: Landlord Home & Daily Workflows

**Document ID:** `05_HOME_AND_DAILY_WORKFLOWS.md`  
**Date:** October 18, 2026  
**Auditor:** Principal Product Designer & Senior UX Architect  
**Objective:** Transforming the 60KB ERP Dashboard into an Action-Oriented Daily Workspace.

---

## 1. Deconstruction of the Current Dashboard (`dashboard.blade.php`)

At 1,005 lines of Blade and 60.5 KB in file size, the current Landlord Dashboard violates the core mental model of a small boarding-house owner. When a landlord logs into OBHS, they are typically on a smartphone looking to accomplish one of three things:
1. Did a student book a room?
2. Did a tenant pay their rent or move-in deposit?
3. Did someone send an inquiry message?

Instead of answering these questions, the current dashboard presents:
- **Level 1 (Top):** Greeting, profile completion badges, 3 header action buttons ("Add Property", "Add Room", "Requests"), and an overdue payment warning alert.
- **Level 2:** 4 generic metric tiles ("Properties", "Total Rooms", "Pending Requests", "Overdue Payments").
- **Level 3:** 4 document compliance cards with multiple badge layers ("Pending Verification", "Valid", "Expiring Soon").
- **Level 4:** An 8-card "Quick Modules" grid repeating links to sidebar pages.
- **Level 5:** A "Next Actions" list repeating the exact same links as Level 4.
- **Level 6:** Two full Chart.js canvases ("Monthly Tenant Trend" and "Real-time Occupancy by Property").
- **Level 7:** A 5-column "Your Properties" table.
- **Level 8:** A "Recommended Rooms" card list (cheapest available rooms).
- **Level 9:** A 2-column "Messages Overview" featuring embedded accordion forms for typing and sending direct message replies!
- **Level 10:** 3 heavy modal definitions embedded into the template (`#landlordSetupReminderModal`, `#addPropertyModal`, `#addRoomModal`).

### The Cognitive Burden
This creates an overwhelming visual scroll depth exceeding 4,000 pixels on mobile devices. A non-technical landlord cannot distinguish between urgent tasks and static reference charts.

---

## 2. Redesign Principle: The "Needs Your Attention Today" Home

The revamped Landlord Home must prioritize real operational tasks derived from authoritative database records. If no actions are required today, the screen should clearly communicate: *"Everything is up to date."*

### Proposed 5-Tier Content Hierarchy:

```
┌────────────────────────────────────────────────────────────────────────┐
│                        PROPOSED LANDLORD HOME                          │
├────────────────────────────────────────────────────────────────────────┤
│ 1. SIMPLE WELCOME & PORTFOLIO SNAPSHOT                                 │
│    "Good morning, Maria. Here is what is happening at your properties."│
│    [ 12 of 14 Beds Occupied (86%) ] • [ 2 Vacant Slots ]              │
├────────────────────────────────────────────────────────────────────────┤
│ 2. NEEDS YOUR ATTENTION TODAY (Dynamic Priority Queue)                 │
│    Ordered strictly by operational urgency:                           │
│    🔴 P0: 2 Payments Need Confirmation (₱7,500 total pending proof)    │
│           [Review Payments ->]                                         │
│    🟠 P1: 1 Student Submitted Move-In Requirements (John Doe, Rm 102) │
│           [Review Move-in ->]                                          │
│    🟡 P2: 2 New Boarding Requests Waiting for Review                   │
│           [Review Requests ->]                                         │
│    🔵 P3: Business Permit Expires in 14 Days                           │
│           [Submit Renewal ->]                                          │
├────────────────────────────────────────────────────────────────────────┤
│ 3. COMPACT PROPERTY STATUS CARDS                                       │
│    Horizontal card per property:                                       │
│    • Sunshine Dorm (Masipit): 8/8 Occupied [Full]                      │
│    • Green Villa (San Vicente): 4/6 Occupied [2 Beds Available]        │
│    [+ Add Room] [Manage Property]                                      │
├────────────────────────────────────────────────────────────────────────┤
│ 4. RECENT INCOMING INQUIRIES (Compact Drawer/Card)                     │
│    Last 2 unread student messages with 1-click "Open Inbox".           │
│    (No heavy embedded reply forms in Home).                            │
├────────────────────────────────────────────────────────────────────────┤
│ 5. MONTHLY RENT SUMMARY (Financial Snapshot)                           │
│    Expected this month: ₱35,000 | Collected: ₱28,000 | Pending: ₱7,000 │
│    [Open Payments Center ->]                                           │
└────────────────────────────────────────────────────────────────────────┘
```

---

## 3. Strict Rules for Action Queue Items

1. **Direct Deep Links Only:** Clicking an action card must never redirect to a generic index table where the landlord has to search for the record again.
   - Example: *"Review Payment Proof from John Doe"* must deep-link directly to `route('landlord.payments.manage', $booking->id)` or open the review drawer.
2. **Authoritative State Scoping:** Action cards must be backed by real database records:
   - Boarding Requests: `Booking::where('status', 'pending')->whereHas('room.property', fn($q) => $q->where('landlord_id', Auth::id()))`.
   - Onboarding Review: `TenantOnboarding::whereIn('status', ['documents_uploaded', 'deposit_paid'])->whereHas('booking.room.property', fn($q) => $q->where('landlord_id', Auth::id()))`.
   - Payment Proofs: `TenantPayment::where('status', 'submitted')->whereHas('booking.room.property', fn($q) => $q->where('landlord_id', Auth::id()))`.
   - Permit Expiration: `LandlordDocument::where('landlord_id', Auth::id())->where('expiration_date', '<=', now()->addDays(30))`.
3. **No Fake Urgency:** If there are zero overdue payments and zero pending requests, display an encouraging clean state:
   - *"All clear! No tasks require your action right now."* with a quick shortcut to view active tenants or room listings.
4. **Pruning Heavy Modals:** Move the `#addPropertyModal` and `#addRoomModal` out of the dashboard. Point "Add Property" to `/landlord/properties/create` and "Add Room" to `/landlord/properties/{property}/rooms/create` to ensure consistent validation and zero code duplication.
