# Student Verification Conditional Requirements & Onboarding Consistency

## 1. Overview & Business Context
This implementation aligns the **Student Setup Onboarding (`/student/setup`)**, **Admin Student Verification (`/admin/approvals/students`)**, and **Student Portal Verification Status (`/student/dashboard`)** workflows.
It removes artificial and misleading requirements during initial onboarding while giving administrators authoritative control to request missing credentials (Student ID number, School ID photo, or both) if and when confirmed by institutional records.

---

## 2. Current Behavior vs. Corrected Behavior

### A. Initial Student Onboarding (`/student/setup`)
* **Student ID Label & Indicators:**
  * *Previous:* Rendered `Student ID (Optional)` with duplicate skip button `Don't have an official Student ID yet? Continue without ID`.
  * *Corrected:* Rendered cleanly as `Student ID` with authoritative helper: `"If you haven't received your Student ID yet, you may leave this blank. Format example: MCC2026-00000."`. Redundant skip link removed.
* **Single Canonical Input:**
  * *Previous:* Rendered on both Student Identity (Panel 0) and Academic Information (Panel 3), causing duplicate inputs and potential desynchronization.
  * *Corrected:* Panel 3 editable field removed completely. Student ID is collected strictly on Panel 0 as a canonical value. Users can navigate back using "Previous" to update it if desired.
* **School ID Upload:**
  * *Previous:* Rendered as `School ID Upload *` with contradictory helper `"Required for 2nd Year and above"`.
  * *Corrected:* Label is `School ID Upload` (no asterisk, optional initially for all year levels). Helper: `"Upload a clear photo of your school ID if available."`. Decoupled from year level.
* **Enrollment Proof (COR / COE):**
  * *Previous:* Contradictory helper `"Optional for 2nd Year and above"`.
  * *Corrected:* COR/COE is strictly required across all year levels as primary enrollment evidence at initial submission.

---

## 3. Admin-Requested Verification Workflow

### Admin Interface (`/admin/approvals/students`)
* For pending students, a dedicated action **"Request Additional Requirements"** is available in the table.
* Opens an accessible modal with:
  * Checkbox: `Student ID number`
  * Checkbox: `School ID photo`
  * Textarea: `Reason / Instructions` (required)
* Submitting sends a `POST` request to `admin.student_verifications.request_information`.
* Cancels any prior unresolved requests, records the new request, sets student status to `action_required`, and notifies the student.

---

## 4. Student Portal Resubmission Workflow

### Student Portal (`/student/dashboard`)
* When an active request exists with `status = 'pending'`:
  * Displays an **"Additional Information Required"** card.
  * Displays administrator instructions.
  * Dynamically renders **only** the requested fields as required:
    * If Student ID requested: Required Student ID input with exact format & uniqueness validation.
    * If School ID photo requested: Required School ID image upload with MIME & size validation.
  * Allows submission without forcing the student to repeat any prior onboarding panels.
* Upon submission (`student.verification_request.submit`):
  * Request is marked `resubmitted` with timestamp.
  * Student status returns to `pending`.
  * System notifications are sent to administrators.
* When `status = 'resubmitted'`:
  * Displays an informational banner that the submission is awaiting review.

---

## 5. Verification Safeguards & Security Decisions
1. **Unresolved Requirements Lock:**
   * Administrators cannot finalize approval (`admin.student_verifications.approve`) if requested items remain unsubmitted by the student.
2. **Exact Identity Protection:**
   * Student ID format (`/^[A-Za-z0-9\-]{3,50}$/`) and database uniqueness are enforced on resubmission.
   * If a duplicate ID is submitted, the error does not disclose the existing owner's personal details.
3. **No Account Recreation or Id Mutation:**
   * Resubmission updates the existing user record without changing the internal database user ID, foreign keys, bookings, or history.
4. **File Storage Isolation:**
   * Uploaded school ID photos are stored securely via `FileStorageService` with private permissions.

---

## 6. Database Changes
* **Migration:** `2026_10_17_210000_create_student_verification_requests_table.php`
  * Table: `student_verification_requests`
  * Columns:
    * `id` (bigint unsigned, primary key)
    * `user_id` (foreign key -> users.id, cascade on delete)
    * `request_student_id` (boolean, default false)
    * `request_school_id_photo` (boolean, default false)
    * `instructions` (text)
    * `status` (enum: `pending`, `resubmitted`, `resolved`, `cancelled`)
    * `requested_by` (foreign key -> users.id, set null on delete)
    * `requested_at` (timestamp)
    * `resubmitted_at` (timestamp, nullable)
    * `resolved_by` (foreign key -> users.id, set null on delete)
    * `resolved_at` (timestamp, nullable)
    * `admin_notes` (text, nullable)
    * `created_at`, `updated_at`
  * Index: `['user_id', 'status']`

---

## 7. Regression Tests
Implemented in `tests/Feature/StudentVerificationConditionalRequirementsTest.php`:
* **A. Initial Setup:**
  * First-year setup without ID or photo passes and sets `pending`.
  * Higher-year setup without ID or photo passes.
  * Setup with valid ID passes and saves exact value.
  * Duplicate ID is rejected with session errors.
  * School ID upload is validated and stored.
  * COR/COE enrollment proof strictly required.
  * Profile photo independently required.
* **B. Admin Requests:**
  * Request Student ID only.
  * Request School ID photo only.
  * Request both.
  * Empty request rejected.
  * Unauthorized reviewer rejected (403).
  * Subsequent request safely supersedes prior request.
* **C. Student Resubmission:**
  * Dynamic validation enforces only requested fields.
  * Missing requested item blocks submission.
  * Duplicate Student ID blocks submission.
  * Valid submission updates status to `resubmitted` and notifies admins.
* **D. Verification Decisions:**
  * Final approval blocked while requested items remain missing.
  * Final approval succeeds once requested items are provided.
* **E. Data Consistency:**
  * Internal database user ID and relations preserved.

All 19 feature tests pass (76 assertions).
Full student test suite passes with 93 tests (391 assertions).

---

## 8. Hostinger Shared Hosting Deployment Manifest
1. **Pull Code:**
   ```bash
   git pull origin main
   ```
2. **Execute Additive Migration:**
   ```bash
   php artisan migrate --force
   ```
   *Note: This migration is strictly additive. No destructive changes or existing table drops are performed.*
3. **Clear & Cache Configuration / Routes / Views:**
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```
4. **Vite Build Assets:**
   ```bash
   npm run build
   ```
