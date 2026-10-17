# Bookings & Stays Lifecycle Mapping

This document details how database booking statuses, active tenancies, and tenant onboarding workflows are consolidated under the single, customer-facing **"My Bookings & Stays"** experience (`/student/requests` -> `student.requests.index`).

---

## 1. Information Architecture Consolidation

Previously, student accommodation commitments were fragmented across four disconnected views:
- `/student/requests` (Administrative request table)
- `/student/onboarding` (Separate onboarding gate)
- `/student/tenant-dashboard` (Administrative dashboard full of metrics)
- `/student/payments` (Stand-alone payment list)

### Consolidated View Architecture
`resources/views/student/bookings/index.blade.php` organizes all bookings into three intuitive, customer-facing tabs:
1. **Active & Upcoming (`#tab-active`):** Current verified leases, approved move-ins, and pending applications.
2. **Past Stays & History (`#tab-history`):** Completed tenancies, rejected requests, and cancelled bookings.
3. **All Requests (`#tab-all`):** Comprehensive chronological timeline with direct search/filtering.

---

## 2. Status Mapping Table

| Database Booking Status | UI Category | Visual Badge Style | Primary Call to Action | Direct Target Route |
| :--- | :--- | :--- | :--- | :--- |
| `pending` | Active & Upcoming | Amber (`bg-warning-subtle text-warning-emphasis`) | "View Details" | Modal / show request |
| `approved` (Onboarding Incomplete) | Active & Upcoming | Emerald (`bg-success-subtle text-success-emphasis`) | "Complete Move-in Requirements" | `student.onboarding.show` (Deep Link) |
| `approved` (Onboarding Completed) | Active & Upcoming | Emerald (`bg-success-subtle text-success-emphasis`) | "View Move-in Instructions" | `student.onboarding.show` |
| `active` (Current Tenant) | Active & Upcoming | Primary Green (`bg-success text-white`) | "View Your Stay & Room" | `student.tenant.dashboard` |
| `completed` | Past Stays | Slate (`bg-secondary-subtle text-secondary-emphasis`) | "Leave a Review" / "View History" | `student.reviews.create` |
| `rejected` | Past Stays | Rose (`bg-danger-subtle text-danger-emphasis`) | "View Reason" | Request modal |
| `cancelled` | Past Stays | Muted (`bg-light text-muted border`) | "Archived" | Request modal |

---

## 3. Transition Rules & Invariants

1. **No Phantom Occupancy:** An `approved` booking does NOT grant immediate occupancy status. The student remains in move-in pending status until formal landlord lease confirmation and onboarding completion.
2. **Single Active Stay Policy:** A student may have multiple past historical stays, but only one active occupancy lease. Active stay cards are hoisted to the top of `#tab-active`.
3. **Review Eligibility Gate:** A student is only prompted to review a boarding house if their booking status is `completed` and no existing feedback record exists for that room from the student.
4. **Preserved Endpoints:** The route name `student.requests.index` remains unchanged to preserve backward compatibility with existing notifications, emails, and landlord-side redirects.
