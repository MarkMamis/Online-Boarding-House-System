# Monitoring & Management Module Responsibility Matrix

**Repository:** Online Boarding House System (OBHS)  
**Date:** 2026-10-08  

---

| Module | Category | Primary Responsibility | Primary Data Displayed | Primary Actions | Authoritative Model / Service | Preserved Route | New Route |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| **Dashboard** | Overview | Operational summary & quick actions | Portfolio counts, pending alerts, permit status | Add property/room modal shortcuts | `Property`, `Booking`, `LandlordProfile` | `/landlord/dashboard` | — |
| **Property & Room Status** | Monitoring | Read-only inspection of properties & rooms | Properties, room occupancy, slot capacity, occupants | Deep links: Manage Property, Manage Rooms | `Property`, `Room`, `RoomOccupancyRules` | — | `/landlord/monitoring/properties` |
| **Tenants & Stays** | Monitoring | Read-only inspection of active boarders | Student names, emergency contacts, lease dates, payment status | Search, filter, deep links: Payments, Messages | `Booking`, `User` | `/landlord/tenants` | — |
| **Compliance Status** | Monitoring | Read-only accreditation & permit tracking | Verification state, expiration condition, validity days | Deep link: Renew Document | `LandlordDocument`, `DocumentExpirationService` | — | `/landlord/monitoring/compliance` |
| **Properties** | Management | Property registration & details CRUD | Property listings, addresses, amenities, photos | Create, edit, delete (with safeguard) | `Property` | `/landlord/properties` | — |
| **Room Directory** | Management | Room configuration & slot setup | Room numbers, pricing models, inclusions | Create, edit, set slots, delete (with safeguard) | `Room` | `/landlord/rooms` | — |
| **Boarding Requests** | Management | Student booking request processing | Pending booking applications, dates, student details | Approve (with capacity check), Reject | `Booking`, `RoomOccupancyRules` | `/landlord/bookings` | — |
| **Tenant Onboarding** | Management | Contract signing & document review | Onboarding dossiers, contracts, IDs, COR proofs | Sign contract, approve onboarding | `TenantOnboarding` | `/landlord/onboarding` | — |
| **Rent & Payments** | Management | Monthly billing and payment verification | Payment submissions, GCash refs, overdue flags | Verify payment, mark as paid, send reminder | `TenantPayment`, `Booking` | `/landlord/payments` | — |
| **Leave Requests** | Management | Move-out request adjudication | Departure requests, dates, student reasons | Approve (preserves active stay until move-out), Reject | `LeaveRequest`, `Booking` | `/landlord/leave-requests` | — |
| **Room Maintenance** | Management | Room operational state control | Rooms under maintenance vs active | Set room to maintenance, complete maintenance | `Room`, `RoomOccupancyRules` | `/landlord/maintenance` | — |
| **Document Renewals** | Management | Document submission and resubmission | Upload forms, file inputs, expiration date inputs | Upload document, resubmit rejected | `LandlordDocument`, `FileStorageService` | `/landlord/documents` | — |
| **Messages** | Communication | Direct messaging with students | Conversation threads, unread counters | Send message, view thread | `Message`, `User` | `/landlord/messages` | — |
| **Business Analytics** | Insights | Portfolio performance metrics | 30d collected revenue, expected rent roll, trends | Filter by date/property | `TenantPayment`, `Booking`, `Property` | `/landlord/analytics` | — |
| **Feedback & Ratings** | Insights | Student reviews and reputation | Ratings, comments, room feedback | Respond to review, analyze sentiment | `RoomFeedback` | `/landlord/feedback` | — |
| **Profile** | Account | Landlord account configuration | Personal info, contact number, avatar, address | Update profile, change password | `User`, `LandlordProfile` | `/landlord/profile` | — |
| **Notifications** | Account | Notification alerts and history | System notices, booking alerts, payment alerts | Mark as read, view detail | `DatabaseNotification` | `/notifications` | — |
