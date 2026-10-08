# Landlord Information Architecture
## Monitoring & Management Operational Separation

**Repository:** Online Boarding House System (OBHS)  
**Date:** 2026-10-08  
**Status:** Implemented & Verified  

---

## 1. Architectural Philosophy

The Landlord Portal is structured around the daily cognitive and operational tasks of boarding house owners in Calapan City:

```
┌────────────────────────────────────────────────────────┐
│                   LANDLORD PORTAL                      │
├────────────────────────────────────────────────────────┤
│ 1. OVERVIEW       → "What is my operational status?"   │
│ 2. MONITORING     → "What is happening right now?"     │
│ 3. MANAGEMENT     → "What decisions do I need to make?"│
│ 4. COMMUNICATION  → "Who needs my response?"           │
│ 5. INSIGHTS       → "How is my business performing?"   │
│ 6. ACCOUNT        → "How is my account configured?"    │
└────────────────────────────────────────────────────────┘
```

### Distinction Between Monitoring and Management

- **Monitoring:** Read-only inspection, search, filter, and comparison of portfolio conditions. It is calm, observational, and links contextually to Management workflows rather than cluttering views with mutation forms.
- **Management:** Action-oriented workspaces where landlords perform operational decisions (approving bookings, reviewing onboarding, verifying payments, updating room state, uploading document renewals).

---

## 2. Navigation Taxonomy

### 1. OVERVIEW
- **Dashboard** (`landlord.dashboard` -> `/landlord/dashboard`):
  - High-level portfolio summary (Total Properties, Total Rooms, Pending Requests, Overdue Payments).
  - Quick action bar (Add Property, Add Room, Requests modal shortcuts).
  - Compliance banner with document status badges.

### 2. MONITORING
- **Property & Room Status** (`landlord.monitoring.properties` -> `/landlord/monitoring/properties`):
  - Read-only portfolio status (approved properties, occupied rooms, available bed slots, maintenance rooms, overall occupancy %).
  - Property breakdown with capacity progress bars, monthly rates, status badges, and active occupant chips.
  - Contextual action links: `Manage Property →` and `Room Directory →`.
- **Tenants & Stays** (`landlord.tenants.index` -> `/landlord/tenants`):
  - Searchable, filterable list of active boarders, lease periods, emergency contacts, parent details, and payment statuses.
  - Deep links to Payments, Leave Requests, and Onboarding.
- **Compliance Status** (`landlord.monitoring.compliance` -> `/landlord/monitoring/compliance`):
  - Accreditation status (Accredited, Pending, Rejected).
  - Real-time document verification and calculated expiration tracking (Valid, Expiring Soon, Expired).
  - Deep link to `Document Renewals & Uploads →`.

### 3. MANAGEMENT
- **Properties** (`landlord.properties.index` -> `/landlord/properties`): Property creation, editing, amenities, photo uploads, deletion protection.
- **Room Directory** (`landlord.rooms.index` -> `/landlord/rooms`): Room creation, capacity configuration, pricing models (solo, shared, hybrid), inclusions, and deletion protection.
- **Boarding Requests** (`landlord.bookings.index` -> `/landlord/bookings`): Review, approve, reject student booking requests with slot validation.
- **Tenant Onboarding** (`landlord.onboarding.index` -> `/landlord/onboarding`): Document review, landlord digital contract signing, move-in confirmation.
- **Rent & Payments** (`landlord.payments.index` -> `/landlord/payments`): Payment records, GCash reference verification, mark-as-paid, payment reminders.
- **Leave Requests** (`landlord.leave_requests.index` -> `/landlord/leave-requests`): Review tenant move-out requests, approve future departures without premature room release, reject with reason.
- **Room Maintenance** (`landlord.maintenance.index` -> `/landlord/maintenance`): Toggle rooms into/out of maintenance status.
- **Document Renewals** (`landlord.documents.index` -> `/landlord/documents`): Upload business permits, fire safety certificates, and resubmit rejected documents.

### 4. COMMUNICATION
- **Messages** (`landlord.messages.index` -> `/landlord/messages`): Direct landlord-student messaging threads.

### 5. INSIGHTS
- **Business Analytics** (`landlord.analytics.index` -> `/landlord/analytics`): 30-day verified collected revenue, expected monthly rent roll, 7-day booking trends, top-performing properties.
- **Feedback & Ratings** (`landlord.feedback.index` -> `/landlord/feedback`): Tenant reviews, ratings, and landlord public responses.

### 6. ACCOUNT
- **Notifications** (`notifications.index` -> `/notifications`): Real-time bell notifications and action alerts.
- **Profile** (`landlord.profile.edit` -> `/landlord/profile`): Landlord contact details, profile photo, boarding house metadata.
- **Logout** (`logout` -> `POST /logout`): Secure session termination.
