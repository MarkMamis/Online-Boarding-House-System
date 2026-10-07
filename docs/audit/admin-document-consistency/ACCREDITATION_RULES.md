# Landlord Accreditation & Prerequisite Rules

## Overview

Landlord accreditation in OBHS ensures that student accommodations meet safety and regulatory standards established by Mindoro State University (MinSU).

---

## 1. Prerequisite Hierarchy

1. **Account Registration**:
   - Landlord registers account; requires email verification and phone setup.
2. **Regulatory Document Submission**:
   - **Business Permit**: Mandatory municipal business registration permit.
   - **Safety Certificate**: Mandatory fire safety and building inspection certification.
3. **Document Approval**:
   - Both documents must achieve `Approved` verification status.
   - `LandlordDocumentStatusService::resolveDocumentStatus()` determines compliance state.
4. **Property Review & Approval**:
   - Individual property submissions require separate physical / visual inspection and admin approval.
5. **Full Compliance State**:
   - `is_compliant = true` requires:
     - `business_permit === 'approved'`
     - `safety_certificate === 'approved'`

---

## 2. Invariants & Security Directives

- **Independent Decisions**: Approving a single document does not automatically approve the landlord's overall account or unapproved properties.
- **Server-Side Authorization**: All review actions are gated by `RoleMiddleware` (`role:admin`) and CSRF tokens.
- **Atomic Persistence**: Approving or rejecting a document synchronizes both the versioned `landlord_documents` record and the `landlord_profiles` table atomically.
- **Audit Trails**: Records track `approved_by`, `approved_at`, `rejected_at`, and `rejection_reason`.
