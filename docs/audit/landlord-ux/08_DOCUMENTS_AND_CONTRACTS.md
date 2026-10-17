# OBHS Landlord Portal: Documents, Contracts & Compliance

**Document ID:** `08_DOCUMENTS_AND_CONTRACTS.md`  
**Date:** October 18, 2026  
**Auditor:** Accessibility Specialist & Security Architecture Reviewer  
**Focus:** Document Taxonomy, Lease Execution Integrity, Privacy Boundaries, and Expiration State Contracts.

---

## 1. Document Domain Classification

The OBHS codebase handles four distinct document classifications. These must never be merged into a single generic "documents" table:

```
┌────────────────────────────────────────────────────────────────────────┐
│                        OBHS DOCUMENT TAXONOMY                          │
├────────────────────────────┬───────────────────────────────────────────┤
│ 1. REGULATORY DOCUMENTS    │ Landlord compliance permits reviewed by   │
│    (Business Permit, etc.) │ university administrators.                │
├────────────────────────────┼───────────────────────────────────────────┤
│ 2. TENANT VERIFICATION     │ Move-in prerequisites uploaded by         │
│    DOCUMENTS               │ students (Emergency contacts, income).    │
├────────────────────────────┼───────────────────────────────────────────┤
│ 3. RENTAL LEASE CONTRACTS  │ Bilateral legal agreements digitally      │
│    (PDFs & E-Signatures)   │ signed by landlord and student.           │
├────────────────────────────┼───────────────────────────────────────────┤
│ 4. PAYMENT ATTACHMENTS     │ Transaction proofs (GCash screenshots,    │
│    (Receipts & Slips)      │ bank transfer slips).                     │
└────────────────────────────┴───────────────────────────────────────────┘
```

---

## 2. Deep Dive: Landlord Regulatory Compliance (`LandlordDocument`)

Managed by `LandlordDocumentController` and `DocumentExpirationService`:
- **Document Types:**
  - `business_permit`: Municipal Mayor's / Business Permit.
  - `safety_certificate`: BFP Fire Safety Inspection Certificate (FSIC) or City Engineering Certificate.
- **Verification States:**
  - `pending`: Uploaded by landlord; awaiting Admin review.
  - `approved`: Admin verified and active.
  - `rejected`: Admin rejected with specific reason.
- **Expiration States (`DocumentExpirationService:25-45`):**
  - `valid`: Expiration date > 30 days in future.
  - `expiring_soon`: Expiration date within next 30 days (triggers warning banner).
  - `expired`: Past expiration date.
- **Critical Distinction:** A document can have a valid expiration date but still be `pending` admin review. The UI must clearly display: *"Submitted — Waiting for MinSU Admin Review"*, rather than conflating expiration with verification.

---

## 3. Deep Dive: Rental Contract & E-Signature Architecture

Governed by `TenantOnboardingController` and `HandlesSignatureDataUrl`:

### The E-Signature Workflow:
1. **Saved Profile Signature vs Contract Execution:**
   - In `LandlordSetupController@saveSignature`, landlords can draw and save a reusable digital signature.
   - However, a saved signature does **not** automatically execute a lease. Signing a lease requires an explicit, intentional action on that specific tenancy agreement (`POST landlord/onboarding/{id}/sign-contract`).
2. **Contract Generation:**
   - `TenantOnboardingController@generateContract` renders standard terms, room number, rental rate, curfew rules, and dates into the agreement text.
3. **DomPDF Contract Streaming:**
   - `downloadContractPdf` streams a legal-sized PDF (`legal`, `portrait`) embedding both parties' signature PNGs, agreement date, and digital tenant ID (`TID-XXXXXXXX`).
4. **Audit Trail Integrity:**
   - Both `student_contract_signed_at` and `landlord_contract_signed_at` timestamps are immutably stored in `tenant_onboardings`. Once signed by both parties, the contract cannot be re-signed or edited.

---

## 4. Privacy & Authorization Boundaries

To comply with data privacy policies and MinSU guidelines:
- **Landlords CANNOT access:**
  - Student grades, academic transcripts, or unredacted academic records.
  - Admin incident moderation notes.
  - Other landlords' properties, permits, or contracts.
- **Storage Streaming Security (`FILE-001`):**
  - All contracts and student verification files are stored privately on Cloudflare R2 / local storage with signed streaming URLs (`/documents/{onboarding}/{filename}`). Raw disk paths are never exposed.

---

## 5. Recommended Document UX Improvements

1. **Unify Document Viewing and Uploading:**
   - Deprecate `/landlord/monitoring/compliance` as an isolated page.
   - Elevate `/landlord/documents` into a full **Compliance & Permits Hub** featuring clear status cards, expiration badges, and 1-click renewal buttons.
2. **Clear Document Status Chips:**
   - Replace technical uppercase strings (`STATUS_PENDING`) with warm, understandable badges:
     - 🟢 `Verified by University`
     - 🟡 `Under Admin Review`
     - 🔴 `Needs Resubmission (Reason: Image Blurry)`
     - 🟠 `Expires in 12 Days (Click to Renew)`
3. **1-Click Lease Signing:**
   - If the landlord already has a saved signature on profile, provide a 1-click action: `[ Apply Saved Signature & Execute Lease ]` with a quick modal preview.
