# File & Document Security Audit

This document reviews the file upload, storage, access control, and privacy protections across all document and image handling subsystems in OBHS.

---

## 1. Document Classification Matrix

| Category | Description / Examples | Access Classification | Authorization Requirement |
| :--- | :--- | :--- | :--- |
| **Public Assets** | Property cover photos, room gallery images | `PUBLIC` / `AUTHENTICATED` | Public or any authenticated student/landlord/admin |
| **Student Credentials** | Student ID photo (`school_id_path`), Certificate of Registration/Enrollment (`enrollment_proof_path`), Parent contact photo | `OWNER_RESTRICTED` + `ADMIN_RESTRICTED` | Owning student or verified Administrator only |
| **Landlord Compliance Documents** | Business Permit, Mayor's Permit, Fire Safety Inspection Certificate, DTI/SEC | `OWNER_RESTRICTED` + `ADMIN_RESTRICTED` | Owning landlord or verified Administrator only |
| **Tenant Onboarding Files** | Signed lease contracts, tenant uploaded verification documents | `PARTICIPANT_RESTRICTED` | Owning student, assigned landlord, or Administrator |
| **Payment Proofs** | GCash transaction receipts, bank deposit slips, QR code images | `PARTICIPANT_RESTRICTED` | Submitting student, recipient landlord, or Administrator |

---

## 2. Storage Architecture & Privacy Controls

### A. Storage Pipeline (`FileStorageService`)
1. **Cloudflare R2 Storage (S3-Compatible):** Primary private cloud bucket.
2. **Supabase S3 Storage:** Secondary fallback cloud bucket.
3. **Local Filesystem Disk:** Development and legacy fallback (`storage/app/public` or private).
4. **Access Model:**
   - Sensitive user files are NEVER exposed via direct, unauthenticated URLs.
   - All access routes through `GET /files/{path}` (`FileController@show`), which checks user role and resource ownership.
   - Content streaming sets proper MIME headers (`Content-Type`) and `Content-Disposition: inline` (view) or `attachment` (download).

### B. Standardized Path Prefixes
Modern uploads use scoped paths that encode resource ownership:
- `students/{student_id}/verification/*`
- `landlords/{landlord_id}/documents/{type}/*`
- `tenant-onboardings/{onboarding_id}/*`
- `payments/{payment_id}/*`
- `properties/{property_id}/*`
- `rooms/{room_id}/*`

---

## 3. Upload Validation Controls

| Upload Type | Controller / Action | Allowed Formats / MIME | Max Size | Validation Location |
| :--- | :--- | :--- | :--- | :--- |
| **Student ID** | `StudentSetupController@update` | `mimes:jpg,jpeg,png,pdf` | `5120 KB` (5MB) | Validator in Controller |
| **Enrollment Proof** | `StudentSetupController@update` | `mimes:jpg,jpeg,png,pdf` | `5120 KB` (5MB) | Validator in Controller |
| **Parent Photo** | `StudentSetupController@update` | `mimes:jpg,jpeg,png` | `5120 KB` (5MB) | Validator in Controller |
| **Landlord Permit** | `LandlordDocumentController@store` | `mimes:jpg,jpeg,png,pdf` | `10240 KB` (10MB) | Validator in Controller |
| **Safety Certificate**| `LandlordDocumentController@store` | `mimes:jpg,jpeg,png,pdf` | `10240 KB` (10MB) | Validator in Controller |
| **Contract Signature**| `TenantOnboardingController` | `string` (Base64 PNG canvas data) | `2048 KB` | Controller base64 decoder |
| **Payment Proof** | `StudentPaymentController@store` | `mimes:jpg,jpeg,png,pdf` | `5120 KB` (5MB) | Validator in Controller |
| **Room Photos** | `RoomController@store` / `update` | `mimes:jpg,jpeg,png,webp` | `5120 KB` (5MB) | Validator in Controller |

---

## 4. Key Findings & Recommendations

### FILE-SEC-001: Legacy Path Matching in `FileController`
- **Finding:** Legacy records without prefix encoding use `FileController@authorizeLegacyPath`. As noted in `AUTH_SECURITY_AUDIT.md`, `TenantOnboarding::where(...)->first()` lacks proper grouping.
- **Remediation:** When querying legacy paths, always chain owner constraints:
  ```php
  TenantOnboarding::where(function ($q) use ($path) {
      $q->where('uploaded_documents', 'like', "%{$path}%")
        ->orWhere('contract_signature_path', $path)
        ->orWhere('landlord_contract_signature_path', $path)
        ->orWhere('payment_proof_path', $path);
  })->whereHas('booking', fn ($q) => $q->where('student_id', $user->id))->exists();
  ```

### FILE-SEC-002: Direct Public Storage Enumeration Prevention
- **Finding:** Apache `.htaccess` blocks direct directory indexing (`Options -Indexes`) and blocks direct access to root `storage/` and hidden files.
- **Verification:** Requests to non-existent private files return 403 or 404 without leaking server path trees.

### FILE-SEC-003: Storage Credentials Exposure Prevention
- **Finding:** Generated URLs and file responses do not contain AWS/R2 credentials.
- **Evidence:** Verified by `Tests\Unit\FileStorageServiceTest::generated_paths_and_urls_do_not_contain_storage_credentials`.
