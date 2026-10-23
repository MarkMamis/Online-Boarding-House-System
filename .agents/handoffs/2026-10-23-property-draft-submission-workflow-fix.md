# Handoff: Property Draft Submission Workflow & Admin Inconsistency Fix

## Summary
Resolved production defect where existing properties saved as `draft` could not be submitted for administrative review from the Property Details workspace (`/landlord/properties/{property}`) or the Edit Property workflow (`/landlord/properties/{property}/edit`). Also resolved admin review inconsistencies where unsubmitted drafts were mislabeled as "Submitted for review" and included in total submission metrics.

## Changes Made
1. **Database Schema:** Added nullable `submitted_at` timestamp column to `properties` table (`2026_10_23_000001_add_submitted_at_to_properties_table.php`).
2. **Model (`Property.php`):** Added `submitted_at` to `$fillable` and `$casts`. Added `getMissingSubmissionRequirements()`, `isCompleteForSubmission()`, and status query helpers (`isDraft()`, `isPending()`, `isApproved()`, `isRejected()`).
3. **Dedicated Submission Route (`routes/web.php`):** Added `POST /landlord/properties/{property}/submit` (`landlord.properties.submit`).
4. **PropertyController:**
   - `submit()`: Dedicated submission endpoint with ownership authorization, validation checklist, and admin notification.
   - `update()`: Supports `action === 'submit'` for drafts, validating completeness and transitioning to `pending`.
   - `autoSave()`: Strict non-draft rejection with HTTP 422 JSON containing `approval_status`.
   - `adminPending()`: Added `draft` tab; `all` filter exclusively counts submitted properties; sorts by `COALESCE(submitted_at, created_at) DESC`.
   - `adminApprove()` / `adminReject()`: Added backend check rejecting draft properties with HTTP 422.
5. **Landlord Views:**
   - `show.blade.php`: Added warning banner for draft listings with `"Review & Submit"` CTA and `"Waiting for Admin review"` notice for pending listings.
   - `index.blade.php`: Added `"Draft / Not submitted"` badge and dynamic `"Review & Submit"` vs `"Continue Setup"` primary action.
   - `guided-form.blade.php`: Supports deep linking (`?panel=3`), renders validation checklist with anchor links, and provides both `"Save Changes"` and `"Submit for Review"` buttons in edit mode.
6. **Admin Views:**
   - `pending.blade.php`: Separated drafts from pending queue; excluded drafts from "Total Submissions"; added Drafts tab; displays accurate dates and labels ("Not yet submitted" vs "Submitted for review"); strips decision buttons for drafts.
   - `index.blade.php`: Added Draft badge, Draft workspace navigation tab, and Draft option in status filter dropdown.

## Verification Performed
- `php artisan test --filter=PropertyDraftSubmissionWorkflowTest` (16 tests, 80 assertions - PASS)
- `php artisan test --filter=LandlordAutosaveAndPaymentsTest` (12 tests, 47 assertions - PASS)
- `php artisan test --filter=AdminPropertiesRefinementTest` (15 tests, 109 assertions - PASS)
- `php artisan view:cache` (PASS)
- `npm run build` (PASS)
- `php vendor/bin/pint` (PASS)

## Invariants Preserved
- No automatic approval or publication.
- Shared hosting constraints honored (no supervisor/redis, dual dev parity).
- MySQL strict mode & `ONLY_FULL_GROUP_BY` preserved.
