# Admin Identity Recovery Review & Verification Workspace

## 1. Domain Separation
OBHS strictly separates two distinct administrative responsibilities under **Management → Student Verification**:
1. **Academic Enrollment Verification (`/admin/approvals/students`):** Evaluates whether an enrolled student's uploaded COR/COE and school ID satisfy university residency requirements.
2. **Identity Recovery Review (`/admin/identity-recoveries`):** Evaluates an account recovery claim where a claimant seeks access to a Student ID currently held by an existing account.

---

## 2. Review Workspace Capabilities
Accessible only to authenticated users with `role: admin`.

### Metric Summary:
- **Pending Review:** Requests awaiting administrator audit.
- **Approved Claims:** Legitimate claims verified against registrar records.
- **Rejected Claims:** Ineligible, unverified, or fraudulent attempts.
- **Total Requests:** Complete audit history.

### Request Audit Card:
For each claim, administrators can inspect:
- **Claimant User:** Name, current login email, registration timestamp.
- **Claimed Student ID:** Canonical MinSU identifier.
- **Safe Contact Info:** Applicant's provided contact email and contact number.
- **Recovery Justification:** Detailed explanation submitted by the student.
- **Attached Proof of Identity:** Secure viewer for uploaded credential documents (served with authorized disposition checks).
- **Existing Account Snapshot:** Non-destructive summary of the account currently holding the Student ID:
  - Account ID & Name
  - Masked email (e.g. `j***@minsu.edu.ph`) for privacy preservation
  - Academic verification status of the existing account
  - Account registration date

---

## 3. Decision Procedures & State Transitions

### A. Approval Procedure:
1. Administrator validates the claimant's uploaded identity proof against campus registrar records.
2. Administrator inputs mandatory or optional review notes (e.g., *"Registrar confirmed claimant identity via 2026 student list"*).
3. System transitions `IdentityRecoveryRequest` to `status: approved`.
4. Sets `reviewed_by` to the authenticated admin ID and `reviewed_at` to the current timestamp.
5. In-app system notification is dispatched to the claimant.
6. **Security Invariant:** Approval alone **does not** automatically reassign the Student ID or overwrite user credentials without verified handoff.

### B. Rejection Procedure:
1. If the applicant provided invalid credentials, an unverified document, or appears to be an unauthorized claimant.
2. Administrator provides rejection notes detailing the deficiency.
3. System transitions `IdentityRecoveryRequest` to `status: rejected`.
4. In-app notification alerts the claimant of the decision and next steps.
5. Audit trail is permanently recorded for dispute resolution.
