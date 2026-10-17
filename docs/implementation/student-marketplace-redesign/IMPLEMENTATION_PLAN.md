# OBHS Student & Tenant Marketplace Redesign — Implementation Plan

**Date:** October 18, 2026  
**Status:** In Progress  
**Objective:** Transform OBHS Student Portal from an administrative control panel into a MinSU-aligned, consumer-friendly accommodation marketplace inspired by modern hospitality UX.  

---

## 1. Architectural Strategy

1. **Retire the Monolithic Multi-Panel Dashboard:**
   - Refactor `AuthController@studentDashboard` to stop querying 23+ datasets (loading hundreds of messages and all database rooms into memory).
   - Make `/student/dashboard` a true, context-aware **Home** that serves the student's exact stage:
     - `searching`: Hero search, category filters, curated available rooms, campus map anchor.
     - `booking_pending`: Reservation status card with landlord response guidance, cancel option, and backup browsing.
     - `move_in_pending`: 4-step move-in checklist linking directly to Tenant Onboarding.
     - `active_tenant`: Resident stay hub (room details, rent due date, pay rent CTA, roommates, leave request notice).
     - `completed_stay`: Compact review prompt + room recommendations.
2. **Standardize Navigation (`<x-student-sidebar />`):**
   - Rename to customer terms: Home, Explore Rooms, Campus Map, My Bookings & Stays, Messages, Notifications, Profile, Help & Safety.
   - Remove the `$hideBrowse` bug that trapped approved students.
   - Ensure Help Center is open to all students.
3. **Marketplace Listing Card Component (`<x-room-card />`):**
   - High-quality 16:10 photography, clean typography, unambiguous price unit (`₱X / month per bed` or `whole room`), live available spaces, and maximum 2–3 key inclusions.
4. **Consolidated Stays View (`/student/requests`):**
   - Re-organize into 3 clear tabs: Active Stay, Pending & Upcoming, Past Stays.
   - Direct link from approved reservations to Tenant Onboarding.
5. **Mobile Chat & Messaging (`/messages`):**
   - Implement master-detail view on mobile to eliminate vertical stacking of threads over chat box.
6. **Fix Map "TBD" Bug:**
   - Resolve property price bounds accurately or display clean non-price fallback markers.
7. **Testing & Parity:**
   - Write comprehensive feature tests for all lifecycle states.
   - Maintain strict compatibility with Hostinger Shared Hosting (no WebSockets/Supervisor).
