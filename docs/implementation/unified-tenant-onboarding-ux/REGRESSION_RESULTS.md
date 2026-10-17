# Regression Testing Results (`REGRESSION_RESULTS.md`)

## Automated Test Suites Executed

### 1. `Tests\Feature\TenantMoveInTransformationTest` (10 tests, 40 assertions) — PASS (16.24s)
- `✓ student move in stage1 pending renders document submission ui`
- `✓ student move in stage2 documents uploaded renders contract and signature ui`
- `✓ student move in stage3 contract signed renders payment submission ui`
- `✓ student move in stage4 deposit paid renders waiting for landlord ui`
- `✓ student move in stage5 completed renders digital tenant id pass`
- `✓ student cannot access another students move in`
- `✓ landlord can view own incoming tenant review workspace`
- `✓ landlord cannot view review workspace for unowned tenant`
- `✓ landlord can approve payment and finalize move in`
- `✓ admin can monitor onboarding details in read only mode`

### 2. Full Regression Suite (44 tests, 204 assertions) — PASS (18.06s)
- `Tests\Feature\BookingTenancyAuthorizationTest`: 6 passed
- `Tests\Feature\OnboardingFinalReviewTest`: 7 passed
- `Tests\Feature\TenantMoveInTransformationTest`: 10 passed
- `Tests\Feature\UnifiedOnboardingExperienceTest`: 21 passed

### 3. Build & Template Validation
- `php artisan view:clear && php artisan view:cache`: 0 errors
- `npm run build`: built in 2.51s, 0 errors
