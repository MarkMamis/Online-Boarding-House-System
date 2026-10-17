# End-to-End User Journey Friction Analysis

**Audit Date:** October 18, 2026  
**Scope:** Step-by-step tracing of 13 primary student journeys through actual routes and views  

---

## Journey A: New Student Discovers a Room

1. **Entry Point:** Student logs in, lands on `/student/dashboard`.
2. **Path:** `/student/dashboard` → Scroll past KPI widgets & map preview → Click "Browse Rooms" button (or click sidebar "Browse Rooms") → `/student/rooms` → Apply filter (e.g. Max ₱2,000) → Click room card → `/student/rooms/{room}`.
3. **Clicks/Taps:** 3–4 clicks.
4. **Friction & Dead Ends:**
   - On `/student/dashboard`, the "Browse Rooms" CTA is halfway down the mobile screen.
   - On `/student/rooms`, filter inputs are hidden inside a collapsable panel that requires an extra tap to reveal.
   - Room card images are small 4:3 thumbnails with lots of stacked badges.
5. **Verdict:** Functional, but discovery should be immediately front-and-center on Home rather than buried under dashboard analytics.

---

## Journey B: Student Compares Two Boarding Houses

1. **Entry Point:** `/student/rooms`.
2. **Path:** Student scrolls through room cards. Must open Room 1 in a new tab or click in, inspect amenities, hit browser back button, then click Room 2.
3. **Clicks/Taps:** 6+ clicks back and forth.
4. **Friction & Dead Ends:**
   - No comparison matrix or side-by-side view.
   - When hitting the browser back button from `/student/rooms/{room}`, filters and scroll position may reset depending on browser session cache.
   - Inclusions use inconsistent terminology (e.g. "free water" vs "water inclusion" vs "water" chip).
5. **Verdict:** High cognitive load. Student must rely on manual memory or pen and paper to compare inclusions and rent.

---

## Journey C: Student Requests a Room

1. **Entry Point:** `/student/rooms/{room}`.
2. **Path:** Click "Request Room" button → Redirects to `/student/rooms/{room}/book` (or opens modal on dashboard) → Pick check-in date & check-out date → Select occupancy mode (Solo vs Shared) → Review House Rules → Click "Draw Signature" → Draw on HTML5 canvas modal → Click "Apply Signature" → Click "Submit Request" → Modal confirmation → POST `/student/rooms/{room}/book` → Redirects to `/student/requests` with success alert.
3. **Clicks/Taps:** 7–9 distinct taps/interactions.
4. **Friction & Dead Ends:**
   - Requiring a drawn e-signature during the initial booking request creates early drop-off friction. The formal digital lease agreement is executed during Tenant Onboarding anyway; signing at initial request creates double-signature fatigue.
   - If the student already has an active request, `BookingController` throws an error only *after* submission or redirects with a flash message: *"You already have an active boarding request."* The button on `/student/rooms/{room}` should clearly state this disabled state upfront.
5. **Verdict:** Works robustly, but signature step at request time is redundant with onboarding lease execution.

---

## Journey D: Student Checks a Pending Request

1. **Entry Point:** Student logs in to check if landlord approved Room 2B.
2. **Path:** Lands on `/student/dashboard` → Sees "Pending Requests: 1" → Clicks "Requests" in sidebar → Lands on `/student/requests` → Views card showing "Pending" badge.
3. **Clicks/Taps:** 2 clicks.
4. **Friction & Dead Ends:**
   - The card shows "Requested: 2 days ago", but gives no indication of whether the landlord has seen it, how long landlords typically take, or how to contact the landlord to follow up.
   - The only action available is "Cancel Request" with an optional text field.
5. **Verdict:** Adequate, but lacks reassurance and direct messaging shortcuts.

---

## Journey E: Student Receives Approval

1. **Entry Point:** Landlord approves request. System generates in-app notification and email. Student opens portal.
2. **Path:** Student visits `/student/dashboard`. Modal pops up (`showApprovedBookingModal`) prompting them to start onboarding.
3. **Clicks/Taps:** 1 click from modal.
4. **Friction & Dead Ends:**
   - If the student closes the modal or navigates to `/student/requests`, the request card says *"Your boarding request is confirmed. Check-in on [Date]"*. It has **no action button** to start onboarding!
   - The student must notice "Tenant Onboarding" in the sidebar with a yellow "Requires Action" badge.
   - In the sidebar, "Browse Rooms" is suddenly hidden because `$hideBrowse` is active.
5. **Verdict:** Missing continuity between "Requests" and "Tenant Onboarding".

---

## Journey F: Student Completes Required Onboarding

1. **Entry Point:** `/student/onboarding/{onboarding}`.
2. **Path:**
   - Step 1: Upload Documents (COR/COE, Emergency Contact, ID) → Submit → Wait for landlord review (status: `documents_uploaded`).
   - Step 2: Once reviewed, sign contract → Draw e-signature on canvas → Submit (status: `contract_signed`).
   - Step 3: View landlord payment details (GCash/Bank) → Transfer money outside app → Upload deposit slip/screenshot + reference number → Submit (status: `deposit_paid`).
   - Step 4: Landlord verifies deposit → Status changes to `completed`.
3. **Clicks/Taps:** ~12–15 interactions across multiple sessions.
4. **Friction & Dead Ends:**
   - The workflow requires multi-day asynchronous coordination with the landlord.
   - If the landlord takes 3 days to verify documents, the student is locked on Step 1 with no status explanation other than "Pending Review".
5. **Verdict:** Well-structured state machine, but requires clearer status feedback when waiting for landlord action.

---

## Journey G: Current Tenant Views Their Room/Stay

1. **Entry Point:** Log in as active tenant (`onboarding_complete = 1`, `check_out > today`).
2. **Path:** Automatically redirected by `AuthController` to `/student/tenant-dashboard`.
3. **Clicks/Taps:** 0 clicks (automatic redirect).
4. **Friction & Dead Ends:**
   - The tenant sees 4 metric cards: Monthly rent, Next due date, Room occupancy, Unread updates.
   - An iframe of Google Maps takes up 66% of the screen width on desktop. A resident already knows where their boarding house is located—displaying a giant map iframe above the fold wastes prime real estate.
   - Key resident actions (e.g. paying rent, viewing contract) are split across multiple cards.
5. **Verdict:** Informative, but poorly prioritized layout.

---

## Journey H: Tenant Contacts Landlord

1. **Entry Point:** Tenant needs to report a leaking pipe or ask about gate curfew.
2. **Path:** From `/student/tenant-dashboard`, landlord phone number is visible as plain text. Or click "Messages" in sidebar → `/messages` → Find thread with Landlord → Type reply → Send.
3. **Clicks/Taps:** 2–3 clicks.
4. **Friction & Dead Ends:**
   - No quick "Call / SMS" tap-to-dial button on mobile (`tel:` URI).
   - In `/messages`, student cannot attach photos (e.g., photo of the broken pipe or faucet).
5. **Verdict:** Basic text messaging works, but lacks mobile calling links and media attachments.

---

## Journey I: Student Checks Payment & Contract Details

1. **Entry Point:** Tenant wants to view monthly rent receipts and lease contract.
2. **Path:**
   - Contract: Must navigate to `/student/onboarding` → Click into onboarding record → Click "View Contract" → `/student/onboarding/{id}/contract` or `/student/onboarding/{id}/contract-pdf`.
   - Payments: Click "Payments" in sidebar → `/student/payments`.
3. **Clicks/Taps:** 3–5 clicks across disparate sections.
4. **Friction & Dead Ends:**
   - Contract and monthly payments are separated into two completely different architectural modules (Tenant Onboarding vs. Student Payments).
   - Students frequently get confused about whether their security deposit receipt is in "Payments" (it is not; it lives in Onboarding).
5. **Verdict:** Severe fragmentation of financial and legal tenancy records.

---

## Journey J: Student Requests Leave

1. **Entry Point:** Tenant going home to their province for a long weekend.
2. **Path:**
   - Can submit via the inline form at the bottom of `/student/tenant-dashboard` (under `#tenant-dashboard-leave`), or via `/student/onboarding`.
3. **Clicks/Taps:** 3 taps (Date picker, reason, submit).
4. **Friction & Dead Ends:**
   - Leave requests are buried at the absolute bottom of the tenant dashboard beneath feedback and support ticket tables.
   - Status updates (Approved / Rejected by Landlord) are shown in a desktop-style data table that truncates awkwardly on mobile viewports.
5. **Verdict:** Functional, but hidden below the fold.

---

## Journey K: Student Rates a Completed Stay

1. **Entry Point:** Stay has elapsed. Student visits `/student/dashboard`.
2. **Path:** Sees yellow warning banner: *"Rate your completed stay"* → Clicks room button → Lands on `/student/rooms/{room}` → Must scroll to bottom or click "Feedback" in sidebar → Lands on `/student/rooms/{room}/feedback` → Select star rating (1–5) → Enter comment → Submit.
3. **Clicks/Taps:** 4 clicks.
4. **Friction & Dead Ends:**
   - The banner button on Home links to `/student/rooms/{room}` (room details) rather than directly to `/student/rooms/{room}/feedback` (the rating form).
   - Sentiment analysis is shown to the student as "Positive sentiment" on the tenant dashboard, which is confusing internal NLP terminology.
5. **Verdict:** Journey has an unnecessary intermediate page redirect.

---

## Journey L: Student Reports a Safety Concern / Incident

1. **Entry Point:** Student encounters harassment, theft, or an emergency safety hazard.
2. **Path:** Clicks "Help Center" in sidebar → `/student/reports` → Sees "Student Support" with tickets summary → Clicks "Create report" → Fill title, category, priority, description → Submit → Ticket saved with status `pending`.
3. **Clicks/Taps:** 4 clicks.
4. **Friction & Dead Ends:**
   - **Critical P0 Issue:** The UI is labeled "Help Center" and "Student Support", mixing ordinary support inquiries with serious incident reports.
   - There is no disclaimer stating that OBHS is not an emergency dispatch service. A student in immediate danger might submit a "High Priority" ticket and wait for an admin response instead of calling MinSU Campus Security or the local PNP.
   - Only students with approved bookings can submit reports (`canSubmitReport` check). If a prospective student experiences harassment during a property ocular visit, they are barred from submitting a report!
5. **Verdict:** Usability and safety hazard. Requires emergency disclaimers and immediate security hotline numbers.

---

## Journey M: Student Receives Admin Verification Request

1. **Entry Point:** Admin flags student's ID as illegible. Student logs in.
2. **Path:** Lands on `/student/dashboard` → Large amber card at top: *"Additional Information Required"* → Reads admin instructions → Uploads new file → Submits → Info banner: *"Verification Resubmitted"*.
3. **Clicks/Taps:** 2–3 clicks.
4. **Friction & Dead Ends:**
   - Well implemented inline form (`student.verification_request.submit`).
   - However, it visually dominates the dashboard and hides all room browsing capabilities below it on mobile.
5. **Verdict:** Functionally sound, but needs a more compact collapsible alert design.
