# Student and Tenant Personas & Lifecycle Analysis

**Audit Date:** October 18, 2026  
**Scope:** Evaluation of the 8 Core Personas across MinSU Accommodation Lifecycle  

---

## Overview

Accommodation is not a static state. An accommodation platform must adapt to where the student stands in their collegiate journey. Below is the audit of how OBHS currently treats each persona versus how a customer-facing platform should respond.

---

### Persona A: New Verified Student Looking for a Boarding House
- **Profile:** 1st year or transferee student, verified by university admin, needs to find a boarding house within walking distance or one jeepney ride of MinSU Calapan / Victoria campus.
- **Primary Goal:** Discover an accredited, safe room matching their budget (e.g. ₱1,500 - ₱2,500/mo), check available bed slots, view photos, verify inclusions (water/WiFi), and submit a request.
- **Current Experience:**
  - Lands on `student.dashboard`.
  - Sees 4 KPI cards: *"Available Rooms: 14"*, *"Pending Requests: 0"*, *"Active Boarding Request(s): 0"*, *"Messages: 0"*.
  - Sees a Map Preview where price pins say `"TBD"` because property bounds are unset.
  - Sees "Next Action: Browse Rooms".
  - Must scroll past Recent Activity to see 3 Recommended Rooms or click a button to go to `/student/rooms`.
- **Friction & Points of Confusion:**
  - KPI tiles provide no actionable value.
  - "TBD" map pins make the platform look broken or abandoned.
  - Must click 2–3 times just to reach a functional room search list.
- **Recommended Treatment:**
  - Welcome banner with prominent Search & Filter bar (Budget, Gender/Room Type, Walking Distance).
  - High-quality visual listing cards with clear photos, price per month, and live bed slot counts.
  - No administrative metrics or zero-count KPI cards.

---

### Persona B: Student with a Pending Booking Request
- **Profile:** Student who submitted a booking request for Room 2B at Green Haven Boarding House and is awaiting landlord approval.
- **Primary Goal:** Check whether the landlord approved or rejected the request, know how long to wait, and have the ability to cancel if needed.
- **Current Experience:**
  - Lands on `student.dashboard`.
  - Metric tile shows *"Pending Requests: 1"*.
  - Activity section shows *"Booking requested • 2 hours ago"*.
  - If they click "Requests" in the sidebar, they go to `/student/requests` which displays the request in a table with a cancel form.
- **Friction & Points of Confusion:**
  - Does not know landlord response time expectations (e.g., "Landlord usually responds within 24 hours").
  - Cannot easily message the landlord directly from the pending card unless they find the messaging tab.
  - `BookingController` restricts them from requesting any other room (`You already have an active boarding request`), but the UI doesn't clearly explain this restriction upfront on other room pages.
- **Recommended Treatment:**
  - Top prominent banner on Home: *"Your request for Green Haven - Room 2B is pending landlord review"*.
  - Include landlord contact shortcut, estimated response time, and one-click cancel action.
  - Clearly communicate that only one active request can be pending at a time.

---

### Persona C: Student with an Approved Booking Awaiting Move-in
- **Profile:** Landlord approved the reservation, but the student has not completed document submission, contract signing, or deposit payment.
- **Primary Goal:** Understand next steps to finalize the lease and secure the room before check-in date.
- **Current Experience:**
  - On `student.dashboard`, a modal may pop up (`showApprovedBookingModal`).
  - Home shows an *"Onboarding Progress"* card with 4 steps: Documents, Contract, Deposit, Complete.
  - **Critical Navigation Glitch:** In `components/student-sidebar.blade.php`, `$hideBrowse` evaluates to `true` when `$hasCurrentApprovedBooking` is truthy. As a result, the "Browse Rooms" link is completely removed from their sidebar! If the landlord suddenly cancels or the student needs to inspect other listings, they are trapped.
  - On `/student/requests`, the status says *"Approved - Your boarding request is confirmed. Check-in on Nov 01"*, but has **no link** to the Tenant Onboarding screen.
- **Friction & Points of Confusion:**
  - Inconsistent prompts: some screens say "Confirmed", but the room is not physically secured until documents and deposit are finished.
  - Lack of a continuous move-in checklist.
- **Recommended Treatment:**
  - Home automatically transforms into a *"Move-in Preparation Hub"*.
  - Clear 4-step actionable checklist (1. Upload Documents -> 2. Review & Sign Lease -> 3. Pay Deposit -> 4. Move-in Key Handover).
  - Keep room browsing accessible in case backup is needed.

---

### Persona D: Student Currently Living in a Boarding House (Active Resident Tenant)
- **Profile:** Tenant whose onboarding is completed, booking is approved, and checkout date is in the future.
- **Primary Goal:** Pay monthly rent, see payment due dates, view roommates, submit leave requests for weekend home visits, review house rules, and message the landlord.
- **Current Experience:**
  - Redirected by `AuthController` to `student.tenant.dashboard`.
  - Displays: Monthly rent, Next due date, Room occupancy, Unread updates, Embedded Google Map iframe, Roommates list, House Rules & Inclusions, Feedback Overview, Reports Overview, and Leave Requests form.
- **Friction & Points of Confusion:**
  - The page feels like an administrative dashboard of cards.
  - "Feedback Overview" shows metrics on "Positive sentiment" and "Submissions" rather than an easy way to write a review.
  - "Reports Overview" shows "Total", "Pending", "In progress", "Resolved" ticket counters.
  - Paying monthly rent requires clicking away to `/student/payments`.
- **Recommended Treatment:**
  - A warm, resident-focused *"My Stay"* Home:
    - Boarding house name, room number, landlord phone/chat button.
    - Prominent "Rent Status: Due in 5 days (₱2,000)" with a direct "Pay Rent" button.
    - Clean tabs for: Roommates & Rules, Payments, Leave Requests, and Support.

---

### Persona E: Student with an Upcoming Scheduled Departure
- **Profile:** Tenant whose checkout date is within 14–30 days.
- **Primary Goal:** Understand move-out procedures, settle final utility/rent dues, coordinate key return, and initiate search for next semester's accommodation.
- **Current Experience:**
  - No special departure alerts or move-out checklist exists.
  - When checkout date arrives, the system flips them to completed with no transitional guidance.
- **Friction & Points of Confusion:**
  - Student is unsure how deposit refunds or damage inspections are handled in-app.
- **Recommended Treatment:**
  - Contextual reminder card 15 days before checkout: *"Your stay ends on Dec 15. Review move-out guidelines & settle final dues."*
  - Easy button to re-book or browse new listings for the upcoming academic term.

---

### Persona F: Student Whose Boarding Stay Has Ended (Alumni / Former Tenant)
- **Profile:** Student whose lease period has elapsed (`check_out <= today`).
- **Current Experience:**
  - Redirected back to `student.dashboard`.
  - Shows persistent yellow warning banner: *"Rate your completed stay — You have completed booking(s) that still need a star rating."*
  - The rest of the dashboard displays the generic searching student dashboard.
- **Friction & Points of Confusion:**
  - The review banner is persistent and can be annoying if closed or if the student does not wish to rate immediately.
  - Past contracts and payment receipts are buried in past onboarding records.
- **Recommended Treatment:**
  - Clean "Rate your experience at [Property Name]" card that can be dismissed or answered in 30 seconds.
  - Dedicated "Stay History" tab with downloadable PDF contracts and payment receipts for tax or scholarship reimbursement.

---

### Persona G: Student Whose Academic Verification is Pending or Needs Info
- **Profile:** Student who just registered or whose School ID was flagged by university administrators as blurry or needing resubmission.
- **Primary Goal:** Know what document is required, upload the correct file, and understand what portal features are unlocked in the meantime.
- **Current Experience:**
  - Shows an amber alert card on `student.dashboard`: *"Additional Information Required — Administrator Instructions: [Text]"* with inline form to upload ID photo or Student ID.
  - If pending: displays an info banner.
  - `BookingController` blocks booking with an error flash message if they attempt to book a room.
- **Friction & Points of Confusion:**
  - Does not know they can still browse rooms while waiting for verification.
  - The warning card pushes all other dashboard content down significantly on mobile.
- **Recommended Treatment:**
  - Clear banner: *"Verification in Progress: You can browse and save rooms. Room booking unlocks once approved."*
  - Dedicated resubmission modal or compact alert with specific document guidelines.

---

### Persona H: Student with No Booking History
- **Profile:** Freshly approved student opening the app for the very first time.
- **Primary Goal:** Understand what OBHS is, how room booking works, and find their first boarding house.
- **Current Experience:**
  - Confronted with 4 empty stat cards: `0`, `0`, `0`, `0`, empty activity feed, and a map with "TBD" pins.
- **Friction & Points of Confusion:**
  - Feels like an empty database table rather than a welcoming campus service.
- **Recommended Treatment:**
  - Curated onboarding empty state: *"Welcome to MinSU OBHS! Browse verified boarding houses near your campus."*
  - Featured properties, top-rated houses, and popular price categories.
