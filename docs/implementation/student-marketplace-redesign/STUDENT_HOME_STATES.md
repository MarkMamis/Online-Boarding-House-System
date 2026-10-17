# OBHS Student Home — Lifecycle States Specification

**Date:** October 18, 2026  
**Route:** `/student/dashboard` (`student.dashboard`)  
**Controller:** `AuthController@studentDashboard`  

---

## 1. Overview

Rather than displaying a static, administrative dashboard with empty metric counts, the new **Home** adapts dynamically to the student's exact lifecycle state in the boarding accommodation journey.

```
       ┌────────────────────────┐
       │   Student Opens Home   │
       └───────────┬────────────┘
                   │
         [Active Tenant?] ─── YES ───► Redirect to /student/tenant-dashboard (Stay Hub)
                   │ NO
         [Approved Booking?] ── YES ──► Display "Continue your move-in" (Onboarding checklist)
                   │ NO
         [Pending Request?] ─── YES ──► Display "Your room request is under review"
                   │ NO
         [Completed Stay?] ──── YES ──► Display "How was your stay?" (Review prompt)
                   │ NO
                   ▼
         [Searching Student] ─────────► Hero search, categories, featured rooms, campus map
```

---

## 2. State Definitions & Interface Contracts

### State A: Searching Student (Default)
- **Condition:** No active approved bookings, no pending booking requests, no completed stay eligible for review.
- **Hero Presentation:**
  - Headline: *"Find your next place near MinSU."*
  - Supporting text: *"Explore accredited boarding houses and rooms that fit your budget, lifestyle, and classes."*
  - Search bar: Submits GET request to `student.rooms.index` with keyword parameter `q`.
- **Secondary Surfaces:**
  - Quick Category Chips: All Stays, Under ₱2,000, Solo Rooms, Bed-Spacer (Shared), Near MinSU Gates.
  - Featured Accommodations Grid (8 curated rooms, eager-loaded).
  - Campus Map Explorer Banner (direct link to `student.properties.map_view`).
  - Accreditation & Safety Notice.

### State B: Booking Pending
- **Condition:** Student has at least one pending booking request (`status = 'pending'`).
- **Primary Section:** Compact alert card at the top of Home:
  - Header: *"Your reservation request for [Property Name]"*
  - Status badge: `Under Review`
  - Details: Room number, submission timestamp (`diffForHumans()`).
  - Landlord guidance: Landlord reviews requests promptly; notification will be sent upon decision.
  - CTA: *"View Booking Details"* &rarr; `route('student.bookings.index')`.
- **Discovery Behavior:** Normal room discovery remains fully accessible below the banner so the student can evaluate backup options if desired.

### State C: Move-in Pending (Booking Approved)
- **Condition:** Student has an approved booking (`status = 'approved'`) where tenant onboarding is not completed (`status != 'completed'`).
- **Primary Section:** High-priority action banner at the top of Home:
  - Header: *"Continue your move-in to [Property Name]"*
  - Status badge: `Booking Approved`
  - Guidance: *"Please complete your digital contract and move-in documents to finalize your stay."*
  - Primary CTA: *"Continue Move-in"* &rarr; `route('student.onboarding.show', $booking->tenantOnboarding->id)`.

### State D: Active Resident / Tenant
- **Condition:** Student has an approved booking where onboarding is completed (`status = 'completed'`) and check-out is in the future.
- **Handling:** `AuthController@studentDashboard` automatically redirects the resident to `student.tenant.dashboard` (`/student/tenant-dashboard`), ensuring residents arrive directly at their stay hub with room details, payment schedules, and landlord contact tools.

### State E: Completed Stay (Review Eligible)
- **Condition:** Student has completed an approved stay in the past (`check_out <= today`) and has not yet submitted feedback for the room.
- **Primary Section:** Compact review invitation banner:
  - Header: *"How was your stay at [Property Name]?"*
  - Supporting text: *"Your feedback helps fellow MinSU students find safe, comfortable accommodation."*
  - CTA: *"Leave a Review"* &rarr; `route('student.rooms.feedback_page', $reminder->room_id)`.
