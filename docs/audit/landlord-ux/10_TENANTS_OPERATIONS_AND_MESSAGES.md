# OBHS Landlord Portal: Tenants, Operations & Messaging

**Document ID:** `10_TENANTS_OPERATIONS_AND_MESSAGES.md`  
**Date:** October 18, 2026  
**Auditor:** Property Management Specialist & Senior UX Architect  
**Focus:** Tenant Records, Move-Out Lifecycle, Room Maintenance Realities, and Communication Channels.

---

## 1. Tenants Directory Audit (`landlord.tenants.index`)

The Tenants page (`/landlord/tenants`) serves as the roster of who is living in the landlord's boarding house.
- **Current Query (`BookingController@landlordTenants:393-400`):**
  ```php
  $tenants = Booking::with(['room.property', 'student'])
      ->where('status', 'approved')
      ->whereHas('room.property', fn($q) => $q->where('landlord_id', Auth::id()))
      ->orderBy('check_in')
      ->get();
  ```
- **Major Finding:** It does not filter out students whose stay has ended (`check_out <= now()`) and does not exclude applicants who are still onboarding.
- **Recommended Redesign:**
  - Structure into 2 clear tabs:
    1. **Active Residents:** `Booking::where('status', 'approved')->where('check_out', '>', today)->whereHas('tenantOnboarding', fn($q) => $q->where('status', 'completed'))`.
    2. **Past Residents / History:** Stays completed or cancelled.
  - Display vital small-business information:
    - Student Full Name, College/Course, Contact Number, Emergency Contact.
    - Assigned Room Number and Bed Mode.
    - Move-in Date, Lease Expiration Date.
    - Monthly Rent Amount and Payment Status badge.
    - 1-Click "Send Message" action.

---

## 2. Leave Requests & Move-Out Lifecycle (`LandlordLeaveRequestController`)

When a student decides to vacate their room before the original check-out date:
- **Workflow:**
  1. Student files `LeaveRequest` with requested `leave_date` and reason.
  2. Landlord reviews request in `/landlord/leave-requests`.
  3. Landlord clicks Approve or Reject with optional response message.
- **The Fixed Premature Cancellation Bug (`LandlordLeaveRequestController:83-90`):**
  - Previously, approving a request immediately set `booking.status = 'cancelled'`, prematurely evicting the boarder from the system.
  - The current code now properly checks:
    ```php
    $isImmediate = $leaveDate <= $today;
    $leaveRequest->booking->update([
        'check_out' => $leaveDate,
        'status' => $isImmediate ? 'cancelled' : 'approved',
    ]);
    ```
- **UX Improvement:** In the UI, clearly explain the consequence of approval:
  - If leave date is in the future: *"Juan will remain an active resident until November 15, 2026. The room slot will automatically become available on that date."*
  - If leave date is today: *"Juan's stay will conclude immediately, and the room slot will open for new bookings."*

---

## 3. Maintenance Reality vs Mental Model (`MaintenanceController`)

- **Current Implementation:**
  - `MaintenanceController` simply toggles `rooms.status` between `'available'` and `'maintenance'`.
  - Setting a room to maintenance immediately prevents new student bookings for that room (`slots_available` logic).
- **The Misleading Terminology:**
  - In `dashboard.blade.php`, this module is labeled: *"Track issues and repairs"*.
  - This leads landlords to expect an issue-ticketing system where students submit repair requests (e.g. "Broken faucet in Room 2").
  - In reality, student incident reports (`ReportController`) are sent to **Admins**, not landlords!
- **Recommended Microcopy Correction:**
  - Relabel module: **"Room Availability & Maintenance Mode"**.
  - Plain language description: *"Temporarily take rooms offline for cleaning, painting, or repairs so students cannot book them."*
  - This eliminates confusion without requiring an entire new ticketing backend.

---

## 4. Communication & Messaging (`MessageController`)

- **Current Capabilities:**
  - Real-time 1-on-1 direct messages between Student and Landlord.
  - Contextual inquiries attached to a specific `room_id` or `property_id`.
  - Unread message counters in the top navbar and sidebar.
- **Usability Flaw: Inline Reply Accordions on Dashboard:**
  - As noted in the Home audit, embedding 5 complete messaging forms inside accordion cards directly on the dashboard page clutters the UI.
- **Recommendation:**
  - Keep Home clean: Display only a compact notification card (*"You have 2 unread messages from prospective students"*) with a button linking directly to `/landlord/messages`.
  - In `/landlord/messages`, implement a modern chat interface with sticky composer, clear sender timestamps, and room context headers.
