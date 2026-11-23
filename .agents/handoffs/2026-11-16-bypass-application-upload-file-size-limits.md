# Handoff: Temporary Bypass of Application-Level Upload File-Size Limits

**Date:** 2026-11-16  
**Scope:** Student and Landlord Upload Flows (Onboarding documents & payments, student setup & profile, landlord setup, profile & documents, room & property photos).  
**Directives Followed:** `AGENTS.md`, `docs/harness/BUSINESS_INVARIANTS.md`, `docs/harness/ROLE_ACCESS_MATRIX.md`.

---

## 1. Summary of Changes

### Root Cause Analysis of "File Limit" Errors
1. **Laravel Validation Rules (`max:<kilobytes>`)**:
   - `TenantOnboardingController::uploadDocuments`: `max:5120` (5 MB).
   - `TenantOnboardingController::payDeposit`: `max:5120` (5 MB).
   - `TenantOnboardingController::storeContractSignatureDataUrl`: `strlen($binary) > 5 * 1024 * 1024` buffer cap.
   - `StudentPaymentController::store`: `max:5120` (5 MB).
   - `StudentSetupController`: `id_document` (`max:3072`), `profile_image` (`max:3072`), `school_id_photo` (`max:3072`), `enrollment_proof_file` (`max:4096`), `parent_contact_photo` (`max:3072`).
   - `StudentProfileController`: `profile_image` (`max:2048`), `school_id_photo` (`max:3072`), `enrollment_proof_file` (`max:4096`), `parent_contact_photo` (`max:3072`).
   - `LandlordDocumentController`: `'max:' . (int) config('landlord_documents.max_size_kb', 2048)` (2 MB).
   - `LandlordSetupController`: `profile_image`, `contract_signature_image`, `business_permit`, `safety_certificate`, `payment_gcash_qr` (`max:2048`).
   - `AuthController::updateLandlordProfile`: `profile_image`, `business_permit`, `payment_gcash_qr`, `contract_signature_image` (`max:2048`).
   - `PropertyController` & `RoomController`: `image`, `detail_images.*` (`max:5120`).
2. **Frontend JavaScript Pre-Checks**:
   - `resources/views/student/setup/index.blade.php`: blocked uploads exceeding 3MB/4MB via `file.size > maxMb * 1024 * 1024`.
   - `resources/views/landlord/setup/index.blade.php`: blocked uploads exceeding 2MB via `file.size > maxMb * 1024 * 1024`.
   - `image-optimizer.js`: hardcoded `maxSizeBytes: 5 * 1024 * 1024`.
3. **Outdated UI Labels**:
   - Hints in student onboarding, deposit payment, profile, landlord setup, landlord profile, and landlord documents mentioned "Max 5MB", "max 2MB", "up to 3MB", "max 4MB".

---

## 2. Changes Made

1. **Application-Level Limits Removed**:
   - Removed all `max:<kilobytes>` file validation rules and custom error messages in the affected student and landlord controllers.
   - Preserved file format validation (`mimes:pdf,jpg,jpeg,png` or `image|mimes:...`).
   - Maintained all authorization checks, private storage destinations via `FileStorageService`, and state transitions.
   - Added explicit `$file->isValid()` checks with `getErrorMessage()` so that PHP-level upload errors (e.g. `UPLOAD_ERR_INI_SIZE`) are caught with explicit feedback rather than silent crashes.
2. **Frontend Interceptors & Outdated Labels Cleaned**:
   - Removed client-side `file.size > maxMb * 1024 * 1024` rejections in `student/setup/index.blade.php` and `landlord/setup/index.blade.php`, retaining file type (`accept`) checking.
   - Updated `resources/js/image-optimizer.js` and `public/js/image-optimizer.js` `maxSizeBytes` from 5MB to 50MB and updated message to "Please choose a smaller photo".
   - Removed outdated "(Max 5MB)", "(max 2MB)", "(up to 3MB)", and "(max 4MB)" hints across all affected Blade views.
3. **Testing & Regressions**:
   - Added automated tests verifying files >5MB (e.g. 8MB PDF, 7MB JPG) are accepted and stored.
   - Verified that invalid MIME types (`.exe`, `.txt`) are strictly rejected with HTTP 422.
   - Verified unauthorized uploads remain blocked with HTTP 403.
   - Verified 15/15 tests in `TenantMoveInRequirementsTest` and 10/10 tests in `TenantMoveInTransformationTest` pass.

---

## 3. Hostinger Shared Hosting Server-Side Configuration

Laravel cannot bypass PHP or web-server request limits. On Hostinger Shared Hosting (Apache + PHP 8.2+), PHP defaults may restrict uploads if files exceed Hostinger's PHP settings.

### Required Hostinger Settings
To allow uploads up to 64MB:

#### Option A: Hostinger hPanel (Recommended)
1. Log into **Hostinger hPanel**.
2. Navigate to **Advanced** > **PHP Configuration** > **PHP Options**.
3. Adjust the following directives:
   - `upload_max_filesize`: set to `64M`
   - `post_max_size`: set to `64M`
   - `memory_limit`: set to `256M`
   - `max_execution_time`: set to `120`
   - `max_input_time`: set to `120`
4. Click **Save**.

#### Option B: `.htaccess` (Apache `mod_php`)
Add the following directives to `public/.htaccess`:
```apache
<IfModule mod_php.c>
    php_value upload_max_filesize 64M
    php_value post_max_size 64M
    php_value memory_limit 256M
    php_value max_execution_time 120
    php_value max_input_time 120
</IfModule>
```

#### Option C: `.user.ini` (PHP-FPM)
In the root directory or `public/` directory, create/edit `.user.ini`:
```ini
upload_max_filesize = 64M
post_max_size = 64M
memory_limit = 256M
max_execution_time = 120
```
