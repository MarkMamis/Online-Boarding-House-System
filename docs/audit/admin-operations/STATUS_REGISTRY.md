# OBHS Admin Operations — Status & State Registry

This registry documents all status fields, allowable values, transitions, and semantic meanings across models involved in the seven admin operations pages.

---

## 1. Status Registry Table

| Model | Status Field | Allowed Values | Semantic Meaning | Mutated By | Admin Visibility / Page | Valid Next States |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| `User` | `school_id_verification_status` | `pending`, `verified`, `rejected`, `null` | MinSU student enrollment credential review state | Student upload, Admin decision | `Student Approvals` (`/admin/approvals/students`) | `pending` -> `verified` or `rejected`; `rejected` -> `pending` (upon re-upload) |
| `LandlordProfile` | `business_permit_status` | `pending`, `verified`, `rejected`, `not_submitted` | Mayor's / Business permit validity | Landlord upload, Admin decision | `Landlord Approvals` (`/admin/approvals/landlords`) | `pending` -> `verified` or `rejected`; `rejected` -> `pending` |
| `LandlordProfile` | `safety_certificate_status` | `pending`, `verified`, `rejected`, `not_submitted` | Fire Safety Inspection Certificate state | Landlord upload, Admin decision | `Landlord Approvals` (`/admin/approvals/landlords`) | `pending` -> `verified` or `rejected`; `rejected` -> `pending` |
| `LandlordProfile` | `verification_status` | `unverified`, `pending`, `verified`, `rejected` | Overall landlord accreditation state (derived or set) | Admin action / Auto-evaluation | `Landlord Approvals`, `Boarding Houses` | `unverified` -> `pending` -> `verified` (both permits valid) |
| `LandlordDocument` | `verification_status` | `pending`, `verified`, `rejected` | Individual compliance document review state | Landlord upload, Admin decision | `Document Verification` (`/admin/documents/verification`), `Document Monitoring` | `pending` -> `verified` or `rejected`; `rejected` -> `pending` |
| `LandlordDocument` | *Computed State* (`lifecycle_status`) | `valid`, `expiring_soon`, `expired`, `missing` | Dynamic temporal compliance status based on `expiry_date` vs `now()` | Computed in Service/Controller | `Document Monitoring` (`/admin/documents/monitoring`) | Computed based on current date |
| `Property` | `approval_status` | `pending`, `approved`, `rejected` | Administrative approval to list boarding house | Landlord creation, Admin decision | `Boarding Houses` (`/admin/properties`) | `pending` -> `approved` or `rejected`; `rejected` -> `pending` |
| `Property` | `status` | `active`, `inactive`, `maintenance` | Operational listing visibility toggle | Landlord toggle, Admin control | `Boarding Houses` (`/admin/properties`) | `active` <-> `inactive` |
| `Booking` | `status` | `pending`, `confirmed`, `active`, `completed`, `cancelled`, `rejected` | Reservation and residency state of a student in a room | Student, Landlord, System | `Boarded Students` (`/admin/boarding-monitoring/students`) | `pending` -> `confirmed` -> `active` -> `completed` (or `cancelled`/`rejected`) |
| `TenantOnboarding` | `status` | `pending_documents`, `pending_review`, `contract_pending`, `contract_signed`, `deposit_pending`, `deposit_verified`, `move_in_ready`, `completed`, `cancelled` | Exact phase in digital lease and move-in workflow | Student, Landlord, System | `Onboardings` (`/admin/onboardings`) | Linear forward pipeline until `move_in_ready` / `completed` |

---

## 2. Status Consistency Findings

1. **Dual Document Status Storage (Landlord Profile vs Landlord Document):**
   - In earlier versions, landlord permits were stored strictly as columns on `landlord_profiles` (`business_permit`, `business_permit_status`, `safety_certificate`, `safety_certificate_status`).
   - The system later introduced the flexible `landlord_documents` table to support arbitrary compliance types (`fire_safety_inspection`, `sanitary_permit`, etc.).
   - *Status Alignment:* `AuthController` and `AdminLandlordDocumentController` now synchronize status mutations between both tables. Both tables maintain `pending`, `verified`, and `rejected`.
2. **Student Verification State Field:**
   - Field is `users.school_id_verification_status`.
   - Legacy code references `users.school_id_status` in some older migrations, but the active schema consistently uses `school_id_verification_status`.
3. **Active Boarder Calculation:**
   - An active boarder is strictly defined in `AdminBookingController` as:
     `Booking` where `status IN ('confirmed', 'active')`, `check_in <= now()`, and `(check_out IS NULL OR check_out >= now())`.
   - This ensures students who cancelled or finished their stay are never erroneously counted as current residents.
