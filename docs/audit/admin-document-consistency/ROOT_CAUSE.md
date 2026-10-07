# Root Cause Analysis: Landlord Document Verification & Accreditation Discrepancy

## Summary of Findings

During cross-module inspection of the Online Boarding House System (OBHS) Admin interface, an inconsistency was identified where the **same landlord profile and safety certificate** displayed as `Pending` on the **Landlord Accreditation** (`/admin/approvals/landlords?tab=permits`) page, but displayed as `Approved` on both the **Landlord Profile** (`/admin/users/landlords/{id}`) and **Document Review Queue** (`/admin/documents/verification`) pages.

---

## Traced Root Causes

### 1. Landlord Accreditation Blade Property Check Defect (`resources/views/admin/approvals/landlords.blade.php:83-87`)
- **Root Cause**:
  ```php
  $safetyStatus = !filled(optional($profile)->safety_certificate_path)
      ? 'missing'
      : ((property_exists($profile ?? new stdClass(), 'safety_certificate_status') && filled(optional($profile)->safety_certificate_status))
          ? $profile->safety_certificate_status
          : 'pending');
  ```
- **Technical Breakdown**: `$profile` is an instance of `App\Models\LandlordProfile` (an Eloquent model). Eloquent attributes are stored dynamically in the protected `$attributes` array and accessed via PHP magic `__get()`. Calling `property_exists($profile, 'safety_certificate_status')` checks declared object properties on the class, which **always returns `false`** for dynamic model attributes in PHP.
- **Impact**: The ternary operator was guaranteed to fall back to `'pending'` on `/admin/approvals/landlords` regardless of whether the certificate was approved or rejected in the database.

### 2. Expiration Condition Omission in Landlord Profile (`resources/views/admin/users/landlords/show.blade.php:840-842`)
- **Root Cause**: The profile documents tab computed expiration solely as:
  ```php
  $expirationDate->lte(\Illuminate\Support\Carbon::today()) ? 'Expired' : 'Valid'
  ```
- **Technical Breakdown**: This omitted the configured 60-day warning threshold handled by `DocumentExpirationService`. Documents that were approved and expiring soon were labeled `Valid` in Landlord Profile, while being classified as `Expiring Soon` in Document Monitoring.

### 3. Action Center Conflicting Query Scopes (`app/Http/Controllers/AdminActionCenterController.php:82-85`)
- **Root Cause**:
  ```php
  LandlordDocument::current()->valid()->expiringSoon()->count();
  ```
- **Technical Breakdown**: `scopeValid` generates `WHERE expiration_date > today + 60 days`, while `scopeExpiringSoon` generates `WHERE expiration_date <= today + 60 days`. Chaining both scopes produced contradictory SQL criteria returning `0`.

### 4. Direct Upload Synchronization Gap (`app/Http/Controllers/LandlordDocumentController.php`)
- **Root Cause**: Submitting new or replacement documents directly via `LandlordDocumentController` persisted records into `landlord_documents` but did not synchronize the legacy `landlord_profiles` columns (`business_permit_path`, `safety_certificate_path`, etc.).

---

## Remediation Strategy

1. **Centralized Service Authority**: Delegate all document status evaluation across Admin modules to `App\Services\LandlordDocumentStatusService::resolveDocumentStatus()`.
2. **Unified Expiration Resolution**: Integrate `App\Services\DocumentExpirationService::expirationInfo()` across Landlord Profile and Compliance Monitor.
3. **Dual-Model Synchronization**: Maintain consistency between `LandlordDocument` (versioned) and `LandlordProfile` (legacy) during store, resubmit, approve, and reject operations.
4. **Action Center Scope Correction**: Query approved active documents within the warning threshold without scope contradiction.
