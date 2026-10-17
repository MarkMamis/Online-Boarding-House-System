# Handoff: Conditional Student ID & School ID Requirements

**Date:** October 17, 2026
**Target Areas:**
- `/student/setup` (Guided Onboarding)
- `/admin/approvals/students` (Admin Student Verification)
- `/student/dashboard` (Student Portal Verification Workflow)

---

## Changes Implemented

1. **Onboarding UI Consistency (`resources/views/student/setup/index.blade.php`):**
   - Changed label to `Student ID` (removed `(Optional)`, no asterisk).
   - Authoritative helper text: `"If you haven't received your Student ID yet, you may leave this blank. Format example: MCC2026-00000."`
   - Removed redundant `"Don't have an official Student ID yet? Continue without ID"`.
   - Removed duplicate editable Student ID field on Academic Information (Panel 3).
   - Removed duplicate JavaScript listeners, handlers, and feedback elements.
   - Decoupled `School ID Upload` from year level: label is `School ID Upload` (no asterisk, non-mandatory initially).
   - Maintained strict enrollment proof (COR/COE) requirement for all students.

2. **Persistence & Models:**
   - Additive migration `database/migrations/2026_10_17_210000_create_student_verification_requests_table.php`.
   - Created model `App\Models\StudentVerificationRequest`.
   - Updated `User` model with `studentVerificationRequests()` and `activeStudentVerificationRequest()` relationships.
   - Removed `school_id_path` from `User::missingStudentSetupFields()`.

3. **Admin Request Workflow (`AuthController.php` & `admin/approvals/students.blade.php`):**
   - Added `POST /admin/student-verifications/{user}/request-information` (`admin.student_verifications.request_information`).
   - Admin can independently request Student ID, School ID photo, or both with custom instructions.
   - Status badge and action modal added in Admin Student Verification table.
   - `adminApproveStudentVerification` blocks approval if requested items are unsubmitted.

4. **Student Resubmission Workflow (`StudentSetupController.php` & `student/dashboard.blade.php`):**
   - Added `POST /student/verification-request/submit` (`student.verification_request.submit`).
   - Dynamic server-side validation enforcing only the requested fields.
   - Student dashboard renders actionable request card when information is required, and informational badge when resubmitted.
   - Admins notified upon resubmission.

5. **Verification & Testing:**
   - 19 automated feature tests in `tests/Feature/StudentVerificationConditionalRequirementsTest.php` (all passing).
   - 20 automated tests in `tests/Feature/StudentIdentityProtectionTest.php` (all passing).
   - Total student test suite: 93 passing tests (391 assertions).
   - View cache & Vite build pass cleanly.

---

## Production Deployment Checklist (Hostinger)
1. `git pull origin main`
2. `php artisan migrate --force`
3. `php artisan config:cache && php artisan route:cache && php artisan view:cache`
4. `npm run build`
