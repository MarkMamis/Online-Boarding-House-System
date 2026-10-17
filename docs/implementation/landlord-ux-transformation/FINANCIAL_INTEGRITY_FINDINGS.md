# Financial Integrity Findings & Policy Guardrails

**Document Reference:** `docs/implementation/landlord-ux-transformation/FINANCIAL_INTEGRITY_FINDINGS.md`  
**Modified Files:** `app/Http/Controllers/PaymentController.php`, `app/Http/Controllers/AnalyticsController.php`

---

## 1. Resolved Issues

### Issue 1: Cash Collection Ledger Omission
- **Root Cause:** When landlords confirmed cash collections, the application updated the booking record status but failed to insert a corresponding `TenantPayment` row.
- **Resolution:** `PaymentController@markAsPaid` now systematically verifies if a `TenantPayment` exists for the current cycle. If none exists, an approved record (`payment_method = 'cash'`, `reviewed_by = Auth::id()`) is instantiated and saved.
- **Verification:** Verified via `LandlordTransformationTest::test_mark_as_paid_creates_tenant_payment_record_if_none_exists`.

### Issue 2: Cross-Landlord Financial Leaks
- **Guardrail:** All financial queries strictly enforce tenant/booking ownership through `booking.room.property.landlord_id = Auth::id()`.
- **Verification:** Verified via `PaymentAuthorizationTest::test_landlord_cannot_mark_as_paid_another_landlords_booking`.

---

## 2. Policy Guardrail: Advance Rent Deduction

### Decision: No Speculative Automatic Crediting
During the audit, the question arose whether onboarding deposit payments (advance rent) should be automatically deducted against the tenant's Month 1 or Month 2 billings.

**Architectural Policy Enforced:**
1. In MinSU boarding house agreements, security deposits and advance rents serve varied operational purposes (e.g. security against damage vs last month's rent).
2. Implementing automatic deduction algorithms without explicit landlord-tenant contract terms risks overwriting intentional agreements or creating negative balance bugs.
3. Therefore, speculative auto-deduction was **strictly avoided**. Advance deposits remain explicitly tracked under move-in collections, and recurring rent remains tracked under monthly billings.
