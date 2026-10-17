# Implementation Plan: Student Identity Protection & Secure Account Recovery

## 1. Overview & Business Objective
The Online Boarding House System (OBHS) onboarding workflow previously deferred Student ID validation until the Academic Information panel (after extensive personal details and demographic questionnaires were already filled). If the Student ID collided with an existing account, the user encountered a dead-end error message ("The student id has already been taken.") without any actionable recovery path. Consequently, legitimate students who forgot their prior accounts registered new accounts under different email addresses, causing identity duplication and data fragmentation.

This implementation establishes an authoritative student identity model:
1. **Early Student ID Question (Panel 0):** As soon as an authenticated user selects the Student role, they are prompted for their school-issued MinSU Student ID before entering personal data or uploading documents.
2. **Exact Equality Contract:** Validation requires full-value, exact canonical matching without wildcards, substring searches, or fuzzy matching.
3. **Recovery-Oriented Feedback:** Dead-end rejection is replaced with non-enumerating recovery guidance ("We couldn't continue with this Student ID. If you've used OBHS before, you can recover your account. If you believe this is a mistake, request assistance.").
4. **Self-Service Credential Recovery:** Direct pathway to secure password reset via Laravel's password broker for students who know their registered email.
5. **Assistance Request Intake:** Controlled submission form for students who lost their registered email address, collecting minimal necessary details and optional university credential proofs.
6. **Admin Identity Review Workspace:** Dedicated review dashboard under Admin Student Verification (`/admin/identity-recoveries`) allowing campus administrators to audit claims, inspect verified accounts, and make informed approval/rejection decisions with notes and an audit trail.

---

## 2. Architecture & Domain Separation
- **User Account:** `users` table handles credentials (email, hashed password), authentication state, email verification, and session management.
- **Student Identity:** `users.student_id` acts as the authoritative identifier. A student identity is single, canonical, and persistent.
- **Student Verification:** Tracks enrollment documents (`school_id_path`, `enrollment_proof_path`, `school_id_verification_status`). Validating Student ID availability is strictly an identity protection checkpoint and does not bypass academic verification.
- **Identity Recovery:** Managed by `identity_recovery_requests` table to record claims without modifying the primary `users.student_id` attribute.

---

## 3. Implementation Phases
- **Phase 0:** Audit existing data model, uniqueness constraints, password broker configuration, and onboarding flows.
- **Phase 1:** Early Student ID validation panel at Panel 0 in `/student/setup`.
- **Phase 2:** Self-service password recovery views (`/forgot-password`, `/reset-password/{token}`) with generic response handling.
- **Phase 3:** Dedicated `IdentityRecoveryRequest` data model, migration, and authenticated request submission endpoint.
- **Phase 4:** Admin Identity Recovery review interface under Admin Student Verification.
- **Phase 5:** Comprehensive feature tests, regression verification across all 288 tests, and documentation.
