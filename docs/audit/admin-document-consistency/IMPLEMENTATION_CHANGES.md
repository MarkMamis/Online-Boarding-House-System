# Implementation Changes Summary

## Files Modified

1. **`app/Services/LandlordDocumentStatusService.php`**
   - Updated `resolveDocumentStatus(User|LandlordProfile|null $subject, string $documentType): string` to accept `User` or `LandlordProfile`.
   - Checks active `LandlordDocument::current()` record first before falling back to `LandlordProfile` columns.
   - Provides canonical resolution across `business_permit` and `safety_certificate`.

2. **`resources/views/admin/approvals/landlords.blade.php`**
   - Replaced flawed `property_exists($profile ?? new stdClass(), 'safety_certificate_status')` check with `LandlordDocumentStatusService::resolveDocumentStatus()`.
   - Restored exact parity between Landlord Accreditation badges and actual database status.

3. **`resources/views/admin/users/landlords/show.blade.php`**
   - Overview Tab: Standardized compliance resolution via `LandlordDocumentStatusService`.
   - Documents Tab (Tab 4):
     - Integrated `DocumentExpirationService::expirationInfo()` for lifecycle status (`Expiring in X days`, `Expired X days ago`, `Valid for X days`).
     - Displayed `Approve` and `Reject` buttons on `pending` submissions.
     - Provided `View`, `Download`, and `Re-approve` options on processed documents without exposing raw rejection forms on approved documents.

4. **`app/Http/Controllers/AdminActionCenterController.php`**
   - Corrected `$expiringComplianceCount` query: removed conflicting `->valid()->expiringSoon()` scope chaining and replaced with `LandlordDocument::current()->where('verification_status', LandlordDocument::STATUS_APPROVED)->expiringSoon()->count()`.

5. **`app/Http/Controllers/LandlordDocumentController.php`**
   - Added `syncProfileColumns()` helper to update `LandlordProfile` during `store()` and `resubmit()` uploads, ensuring legacy and versioned records stay synchronized.

6. **`tests/Feature/LandlordDocumentConsistencyTest.php`**
   - Added comprehensive cross-module test suite covering 9 scenarios:
     - Scenario A: Pending document consistency across views.
     - Scenario B: Approved document consistency across views (verifying the `property_exists` fix).
     - Scenario C: Rejected document with reason.
     - Scenario D: Approved but Expiring Soon (warning threshold).
     - Scenario E: Approved but Expired (retention of approved history).
     - Scenario F: Pending renewal and version isolation.
     - Scenario H: Action Center counts and metrics.
     - Scenario I: Role isolation and security guards.
     - Scenario J: Rejection of mutations on superseded historical records.
