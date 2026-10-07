# Document Version & Expiration Life Cycle Rules

## 1. Versioning Semantics

The OBHS architecture supports versioned document storage in the `landlord_documents` table:

1. **Latest Submission (`is_current = true`, `verification_status = pending`)**:
   - Newly uploaded or renewed document.
   - Requires administrator review in the Document Review Queue.
2. **Current Approved Document (`is_current = true`, `verification_status = approved`)**:
   - The active document accepted for institutional compliance.
3. **Pending Renewal**:
   - When a landlord uploads a newer submission, the previous version transitions to `is_current = false` and `superseded_at = now()`, while the new record starts with `is_current = true` and `verification_status = pending`.
4. **Historical Document (`is_current = false`)**:
   - Archived record preserved indefinitely for regulatory and compliance audits.
   - Historical versions cannot be approved or rejected (endpoint returns `404 Not Found`).

---

## 2. Decoupled Lifecycle Dimensions

Document verification and expiration are independent dimensions:

| Dimension | Domain | Possible States | Authoritative Determination |
|---|---|---|---|
| **A. Verification** | Administrative Review | `pending`, `approved`, `rejected`, `missing` | Administrative review decision on active submission |
| **B. Expiration** | Regulatory Validity | `valid`, `expiring_soon`, `expired`, `no_expiration` | Calculated dynamically from `expiration_date` relative to `today` |
| **C. Landlord Eligibility** | Institutional Accreditation | `Active Account`, `Compliant`, `Pending Review`, `Action Required` | Evaluated from verification of all required documents + property approvals |

### Invariant Rules
- A document that is `Approved` and `Expiring Soon` is valid for operation but flagged in monitoring.
- An `Expired` document retains its historical verification status of `Approved` (it is never rewritten to `Rejected`).
- A landlord account can be `Active` while required documents are in `Action Required` or `Pending Review`.
