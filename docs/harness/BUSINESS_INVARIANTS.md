# Core Business Logic Invariants

This document establishes the inviolable business rules, state machines, and domain invariants of the Online Boarding House System (OBHS). Any code change violating these invariants is considered a breaking regression.

---

## 1. Booking & Room Availability Invariants

1. **Room Capacity & Availability:**
   - A room's `slots_available` can NEVER be negative (`slots_available >= 0`).
   - `slots_available` + `occupied_count` must NEVER exceed total room `capacity`.
   - When a booking transitions to `approved`, available slots must decrement atomically.
   - When an approved booking is cancelled or reaches move-out completion, available slots must increment atomically.
2. **Booking Eligibility:**
   - Only students with verified emails and completed student setup can initiate a room booking.
   - A student cannot place a booking on a room belonging to an unapproved property.
   - A student cannot double-book overlapping active tenancies for the same room.
3. **Approval Authority:**
   - Only the landlord who owns the underlying property (or a system administrator) can approve or reject a booking.

---

## 2. Tenancy & Onboarding State Machine

The tenant onboarding lifecycle adheres to a strict linear progression:

```
[Booking Approved]
       │
       ▼
1. Document Submission (Student uploads school credentials/ID)
       │
       ▼
2. Document Review (Landlord reviews & approves uploaded documents)
       │
       ▼
3. Student Signature (Student digitally signs lease agreement)
       │
       ▼
4. Landlord Signature (Landlord countersigns lease agreement)
       │
       ▼
5. Deposit / Advance Payment (Student submits move-in payment proof)
       │
       ▼
6. Tenancy Activated (Landlord confirms deposit; tenant enters active occupancy)
```

- **Invariants:**
  - A student cannot sign the contract until the landlord approves their submitted documents.
  - A landlord cannot sign the contract until the student has applied their digital signature.
  - An onboarding record cannot reach `completed` status until move-in deposit requirements are settled.

---

## 3. Financial & Payment Invariants

1. **Server-Side Computation:**
   - Monthly rent dues, deposit amounts, and total billing are ALWAYS calculated server-side from `bookings.monthly_rent_amount` or `rooms.price`.
   - Client-submitted payment amounts are strictly ignored or validated against expected server calculations.
2. **Duplicate Payment Prevention:**
   - A student cannot submit more than one active payment submission for the same billing month/cycle.
3. **Ledger Immutability:**
   - Payment records in `tenant_payments` maintain an audit trail (`submitted_at`, `reviewed_at`, `reviewed_by`, `review_notes`).
   - Approved payment records cannot be deleted; adjustments must use status transition or offsetting records.

---

## 4. Landlord Document & Compliance Invariants

1. **Verification State Transitions:**
   - When a landlord uploads a new or replacement document (Business Permit, Mayor's Permit, Fire Safety Inspection Certificate), its status immediately becomes `pending`.
   - Replacing an approved document resets status to `pending` until an Administrator re-verifies it.
2. **Date Consistency:**
   - Document `expiration_date` must never be earlier than `date_issued`.
   - Documents expiring within 60 days enter the `expiring_soon` warning state; documents past their expiration date enter `expired` state.
3. **Property Visibility Dependency:**
   - Properties are only eligible for public audience visibility when the property itself is `approved` and the owning landlord has completed required profile setup.

---

## 5. Incident Reporting Invariants

1. **Filing Eligibility:**
   - Only students with an approved, active, or historical tenancy at a property can file an incident report against that property or landlord.
2. **Resolution Lifecycle:**
   - Reports transition through `pending` -> `in_progress` -> `resolved` / `dismissed`.
   - Admin responses update `response_read = 0` until acknowledged by the filing student.

---

## 6. Messaging & Privacy Invariants

1. **Participant Authorization:**
   - Only the sender, receiver, or a system administrator can view a direct message thread.
2. **Read Status Integrity:**
   - Only the designated `receiver_id` can mark a message as read.
