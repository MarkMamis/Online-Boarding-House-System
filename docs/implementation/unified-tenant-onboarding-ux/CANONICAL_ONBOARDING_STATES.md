# Canonical Onboarding States & State Machine

## Authoritative Lifecycle
The Tenant Onboarding state machine is governed by `App\Models\TenantOnboarding` and orchestrated by `TenantOnboardingController`.

```
[Booking Approved]
        │
        ▼
   1. PENDING (Requirements Upload)
        │ Student uploads documents[]
        ▼
   2. DOCUMENTS_UPLOADED (Lease Agreement Review & Student Signing)
        │ Landlord can approve/reject documents
        │ Student draws e-signature and signs contract
        ▼
   3. CONTRACT_SIGNED (Move-in Payment Submission)
        │ Landlord can sign contract using profile signature
        │ Student submits move-in payment (Bank, GCash, or Cash)
        ▼
   4. DEPOSIT_PAID (Landlord Payment Review)
        │ Landlord verifies payment proof and approves payment
        │ (or rejects payment, reverting status to CONTRACT_SIGNED)
        ▼
   5. COMPLETED (Digital Tenant ID & Active Tenancy)
        │ Generates digital_id (TID-XXXXXX)
        │ Activates tenant access and schedules next payment due date
```

## State Definitions & Labels

| Canonical Status | Student UI Label | Landlord UI Label | Admin UI Label | Action Required By |
|---|---|---|---|---|
| `pending` | Requirements Pending | Waiting for Student Upload | Pending Upload | Student |
| `documents_uploaded` | Contract Ready to Sign | Review Documents / Landlord Sign | Documents Uploaded | Student & Landlord |
| `contract_signed` | Payment Due | Landlord Sign / Awaiting Payment | Contract Signed | Student (and Landlord) |
| `deposit_paid` | Payment Under Review | Payment Awaiting Confirmation | Payment Under Review | Landlord |
| `completed` | Active Stay / Move-in Complete | Move-in Complete | Completed | None (Tenancy Active) |

## Financial & Legal Invariants
1. **No Auto-Tenancy:** TENANT ONBOARDING NEVER activates a tenancy upon document upload or payment proof submission alone; explicit landlord payment confirmation is required.
2. **Contract Immutability:** Contracts once signed cannot be retroactively modified without explicit business rejection flows.
3. **Ledger Parity:** Move-in payments (security deposit + advance rent) are recorded strictly through canonical routes.
