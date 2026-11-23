# Handoff: Student Move-in Requirements & Lease Contract PDF Repair

**Date:** November 16, 2026  
**Feature IDs:** `TEN-001`, `TEN-002`, `LND-005`, `FILE-001`  
**Domain Agents:** `obhs-booking-tenancy`, `obhs-documents-storage`  

---

## 1. Problem Summary & Root Causes

1. **Undefined `$toDataUri` in Contract PDF (`TEN-002`):**
   - In `resources/views/onboarding/pdf/contract_page.blade.php`, `$toDataUri` was called on lines 399-400 for primary and secondary logos but was never defined or passed by `TenantOnboardingController`.
   - Any attempt to preview or download the contract PDF by student, landlord, or admin resulted in an `Undefined variable $toDataUri` fatal exception.

2. **Move-in Requirements Mismatch & Lack of Profile Document Reuse (`TEN-001`, `STU-001`):**
   - `BookingController` hardcoded `required_documents = ['student_id', 'proof_of_income', 'emergency_contact']` upon booking approval.
   - `uploadDocuments()` converted this into demanding a minimum of 3 newly uploaded files via generic dropzone validation.
   - Initial student setup already captured COR/COE (`enrollment_proof_path`), optional School ID (`school_id_path`), and emergency contact details. Students had no way to reuse their existing documents and were erroneously asked for proof of income.

3. **Onboarding State Invariants & Approval Gaps (`TEN-001`, `LND-005`):**
   - Uploading documents immediately set `status = 'documents_uploaded'`, allowing students to proceed to contract signing before landlord review.
   - Landlord "Approve Documents" action simply updated `status = 'documents_uploaded'`, leaving document review status indistinguishable and keeping the "Action Required" banner active.
   - Resubmitting documents could overwrite signed/completed onboarding records.
   - Payment approval did not enforce that both the student signature and landlord countersignature were present.

---

## 2. Key Changes Implemented

### A. Contract PDF Safe Data-URI Logo Conversion (`resources/views/onboarding/pdf/contract_page.blade.php`)
- Defined `$toDataUri` directly in `contract_page.blade.php` with robust error handling (`is_file`, `is_readable`, `file_get_contents`, MIME detection for png, jpg, jpeg, webp, svg).
- Gracefully returns `null` on missing/unreadable files; template renders existing fallback badges without breaking.
- Shared preview/download routes (`student.onboarding.contract_pdf`, `landlord.onboarding.contract_pdf`, `admin.onboardings.contract_pdf`) now generate valid PDF streams cleanly.

### B. Safe Multi-Extension Object Copying (`app/Services/FileStorageService.php`)
- Enhanced `FileStorageService::copyToDirectory()` with `$allowedExtensions` parameter defaulting to `['png', 'jpg', 'jpeg', 'webp', 'gif', 'pdf']`.
- Ensures PDF documents (e.g. COR/COE) preserve their `.pdf` extension and raw binary contents when copied to `tenant-onboardings/{id}/documents`.

### C. Physical Document Reuse & Server-Side Security (`app/Http/Controllers/TenantOnboardingController.php`)
- Server-side resolution: Source paths are resolved strictly from `Auth::user()` (`$student->enrollment_proof_path`, `$student->school_id_path`). Client cannot forge file paths or user IDs.
- Copied into isolated onboarding directory: `tenant-onboardings/{id}/documents/`. Profile document replacements later will not delete move-in copies.
- Duplicate prevention: Repeated submissions inspect existing documents by prefix (`cor-coe`, `school-id`) and avoid redundant file copies.
- Graceful error handling: Missing/unreadable files abort with clear guidance without advancing onboarding state.

### D. Requirements Alignment & Validation Repair (`app/Http/Controllers/BookingController.php` & `TenantOnboardingController.php`)
- New onboardings created with `['enrollment_proof', 'emergency_contact']`.
- Emergency contact verified directly from student profile (`emergency_contact_name` / `parent_contact_name` + phone).
- Proof of income ignored; legacy records with hardcoded defaults are handled without demanding 3 files or income proof.
- Reused documents and newly uploaded files are counted together. Specific missing requirement messages returned instead of generic min-file count errors.

### E. Document Approval & State Machine Enforcement (`app/Models/TenantOnboarding.php`, `TenantOnboardingController.php`, Views)
- Added `areDocumentsApproved()` and `areDocumentsSubmitted()` on `TenantOnboarding`.
- Landlord `approve` action records approval metadata (`_documents_approved = true`, `_documents_approved_at`).
- Student contract signing is blocked until `areDocumentsApproved()` is true.
- Student UI shows "Documents Under Review" during pending landlord review.
- Landlord UI distinguishes unapproved documents (showing review banner and buttons) from approved documents (showing approved confirmation badge).
- Document upload is strictly blocked if contract has already been signed or onboarding is completed.
- Completion (`approve_payment` and `completeOnboarding`) strictly requires student signature (`contract_signed`), landlord countersignature (`landlord_contract_signed`), and payment confirmation (`deposit_paid`).
- Preserved landlord profile signature application ("Apply Saved Profile Signature").

---

## 3. Files Modified & Added

- `app/Http/Controllers/BookingController.php` (Line 477: Updated default required documents)
- `app/Http/Controllers/TenantOnboardingController.php` (uploadDocuments, approveDocuments, signContract, completeOnboarding)
- `app/Models/TenantOnboarding.php` (Added `areDocumentsApproved()`, `areDocumentsSubmitted()`)
- `app/Services/FileStorageService.php` (Enhanced `copyToDirectory` to preserve extensions including PDF)
- `resources/views/onboarding/pdf/contract_page.blade.php` (Safe local logo data-URI conversion)
- `resources/views/student/onboarding/show.blade.php` (Document reuse controls, emergency contact summary, Stage 1.5 review state)
- `resources/views/landlord/onboarding/review.blade.php` (Differentiated document approval states and badges)
- `tests/Feature/TenantMoveInTransformationTest.php` (Updated test fixture helper to match strict invariants)
- `tests/Feature/TenantMoveInRequirementsTest.php` (**New**: 11 targeted regression tests)

---

## 4. Verification Evidence

- Executed `php artisan test tests/Feature/TenantMoveInRequirementsTest.php`:
  **11 passed (53 assertions)**
- Executed `php artisan test tests/Feature/TenantMoveInTransformationTest.php`:
  **10 passed (40 assertions)**
- Combined test run:
  **21 tests passed (93 assertions), 0 failures.**

---

## 5. Hostinger Production Deployment Steps

1. Pull latest commit on main branch:
   ```bash
   git pull origin main
   ```
2. No database migrations required (no schema changes).
3. Clear and rebuild application caches:
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```
4. Build front-end assets if needed:
   ```bash
   npm run build
   ```
