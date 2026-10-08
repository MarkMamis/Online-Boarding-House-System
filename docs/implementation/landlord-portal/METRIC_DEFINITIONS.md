# Landlord Financial and Operational Metric Definitions

**Repository:** Online Boarding House System (OBHS)  
**Date:** 2026-10-08  

---

## 1. Financial Metrics

### 1.1 Monthly Collected Revenue (30-Day Actual)
- **Definition:** Total actual payments received and approved by the landlord within the preceding 30 days.
- **Source of Truth:** `TenantPayment` model.
- **Filter / Scope:** `status = 'approved' AND (reviewed_at >= now() - 30d OR (reviewed_at IS NULL AND created_at >= now() - 30d)) AND booking.room.property.landlord_id = Auth::id()`.
- **Constraint:** Does not include pending payment submissions, unverified proofs, or uncollected receivables.

### 1.2 Expected Monthly Revenue (Rent Roll)
- **Definition:** Total monthly contractual rent owed for the current active billing cycle across all active boarders.
- **Source of Truth:** Sum of `room.price` for active approved `Booking` records.
- **Filter / Scope:** `status = 'approved' AND check_in <= today < check_out AND room.property.landlord_id = Auth::id()`.
- **Constraint:** Strictly calculated on a monthly cycle basis. Room rate is NOT multiplied by stay duration in days.

### 1.3 Outstanding / Overdue Payments
- **Definition:** Boarders whose monthly payment due date has passed without an approved payment record.
- **Source of Truth:** `Booking::derivedPaymentStatus()` returning `'overdue'`.

---

## 2. Occupancy and Room Metrics

### 2.1 Total Rooms
- **Definition:** Total rooms registered under the landlord's properties.
- **Source of Truth:** `Room::whereHas('property', fn($q) => $q->where('landlord_id', Auth::id()))->count()`.

### 2.2 Occupied Rooms
- **Definition:** Rooms where `status = 'occupied'` or active approved bookings consume full capacity.
- **Source of Truth:** `RoomOccupancyRules::snapshot()`.

### 2.3 Available Bed Slots
- **Definition:** Total unassigned bed capacity across all available rooms.
- **Source of Truth:** Sum of `Room.slots_available`.

### 2.4 Maintenance Rooms
- **Definition:** Rooms temporarily taken offline by the landlord (`status = 'maintenance'`).

### 2.5 Occupancy Rate (%)
- **Definition:** `(Occupied Rooms / Total Rooms) * 100`. Returns `0%` if total rooms is 0.

---

## 3. Compliance Metrics

### 3.1 Document Verification Status
- **Approved:** Validated by MinSU Admin.
- **Pending:** Submitted by landlord and awaiting administrator review.
- **Rejected:** Document invalid or unreadable; landlord must resubmit.
- **Not Submitted:** Required document type has no active upload.

### 3.2 Expiration Condition
- **Valid:** Document expiration date is greater than the warning threshold (e.g. > 60 days).
- **Expiring Soon:** Expiration date is within 60 days of today.
- **Expired:** Expiration date is today or in the past (`daysUntilExpiration <= 0`).
