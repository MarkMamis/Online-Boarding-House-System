# Property Status Lifecycle & Invariants

## 1. Authoritative State Lifecycle

The OBHS property model enforces four canonical lifecycle states stored in the `approval_status` column:

```
                  +--------------------------------+
                  |             Draft              |
                  |  (Unsubmitted, Saved listing)  |
                  +---------------+----------------+
                                  |
                                  | Explicit landlord submission
                                  | (via Details CTA, Edit Panel 3,
                                  |  or Initial Create)
                                  v
                  +--------------------------------+
                  |         Pending Review         |
                  |  (Awaiting Admin Evaluation)   |
                  +---------------+----------------+
                                  |
                     +------------+------------+
                     |                         |
                     v                         v
       +---------------------------+   +-------------------------------+
       |         Approved          |   |           Rejected            |
       | (Institutional Compliance)|   | (Specific Rejection Reason)   |
       +---------------------------+   +-------------------------------+
```

---

## 2. State Definitions & Invariants

### 1. `draft`
- **Definition:** The property record is created and editable by the owning landlord, but has NOT been submitted for administrative compliance review.
- **`submitted_at`:** `NULL`.
- **Marketplace Visibility:** Strictly hidden from student search, maps, and discovery (`scopeVisibleToAudience` excludes drafts).
- **Admin Review Queue:** Excluded from `pending` review queues and excluded from "Total Submissions". Visible only in administrative monitoring views and a dedicated "Drafts" filter tab.
- **Admin Actions:** CANNOT be approved or rejected via admin review actions (server-side rejects with HTTP 422).

### 2. `pending`
- **Definition:** The property has been explicitly validated and submitted by the authorized landlord. It is queued for administrative evaluation.
- **`submitted_at`:** Set to `now()` upon submission transition.
- **Marketplace Visibility:** Hidden from student search and public discovery.
- **Auto-Save:** Auto-save is strictly disabled (server-side returns HTTP 422 `Cannot auto-save a property that has already been submitted for review.`).
- **Landlord Actions:** Displays "Waiting for Admin review" notice; duplicate submissions are idempotent.

### 3. `approved`
- **Definition:** The property has received explicit institutional approval from an authorized administrator.
- **`approved_at`:** Recorded upon approval.
- **Marketplace Visibility:** Eligible for student discovery if landlord accreditation invariants are met (`billing_completed === true` and `business_permit_status === 'approved'`).
- **Admin Decisions:** Preserved historically; cannot be overwritten by stale auto-saves.

### 4. `rejected`
- **Definition:** The property was reviewed and rejected by an administrator.
- **`rejected_at` & `rejection_reason`:** Recorded on rejection.
- **Marketplace Visibility:** Strictly hidden from student discovery.
- **Resubmission:** If resubmission is initiated, requires explicit landlord action.

---

## 3. Migration Parity for Hostinger Shared Hosting
- Migration: `2026_10_23_000001_add_submitted_at_to_properties_table.php`
- Adds nullable timestamp `submitted_at` to `properties`.
- Backfill: Existing properties with `approval_status IN ('approved', 'rejected', 'pending')` are safely populated with `COALESCE(approved_at, created_at)`.
- Existing `draft` properties retain `submitted_at = NULL`.
