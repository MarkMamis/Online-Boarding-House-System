# Landlord Move-in Review Workspace (`LANDLORD_REVIEW_UX.md`)

## Key Design Improvements
1. **Single Contextual Workspace:**
   - Replaced the previous 4-tab interface (Overview, Documents, Contract, Deposit) that forced landlords to click through multiple tabs to review a single student.
   - Organized into a single unified page with clean sections: Tenant & Lease Summary, Actionable Section (prominently placed at top), Rental Agreement & Signatures, Submitted Documents, and Move-in Completion status.
2. **Actionable Step Prioritization:**
   - When payment is under review (`deposit_paid`), the Move-in Payment Verification card appears prominently with green action buttons (`Approve Payment & Finalize Move-in`) and red rejection buttons.
   - When documents are submitted (`documents_uploaded`), the Document Review card is highlighted with `Approve Documents` and `Request Re-upload` controls.
   - When student has signed contract, the Landlord E-Signature card provides an explicit one-click button to apply the landlord's saved profile signature.
   - When status is `pending`, a calm notice informs the landlord that no action is required until the student uploads documents.
3. **Move-in Progress Directory:**
   - Page title simplified to "Move-in Progress" in accordance with user directive.
   - Top summary strip shows total move-ins, action-required count, and completed count.
   - Each incoming student card clearly identifies who needs to act next (e.g. "Documents submitted · Ready for your review", "Waiting for student to upload").
4. **Idempotency & Accounting Safety:**
   - Approval of move-in payment calls the canonical `$this->completeOnboarding()` service method, setting booking status to `paid`, assigning `digital_id`, and generating the signed lease record without mutating ledger rules.
