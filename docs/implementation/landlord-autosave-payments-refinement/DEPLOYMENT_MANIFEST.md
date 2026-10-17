# Deployment Manifest & Production Invariants

## 1. Target Environment
- **Host:** Hostinger Shared Web Hosting
- **Server:** Apache (mod_rewrite enabled, `.htaccess` standard)
- **PHP:** 8.2+
- **Database:** MySQL 8.x (Strict SQL / `ONLY_FULL_GROUP_BY` compliant)

## 2. Inventory of Modified Files
- `routes/web.php`: Added `POST /landlord/properties/autosave` (`landlord.properties.autosave`).
- `app/Http/Controllers/PropertyController.php`: Added `autoSave()` endpoint, updated `create()` to load and restore active draft, updated `store()` to update existing draft and preserve cover images.
- `app/Http/Controllers/PaymentController.php`: Added pending review count calculations (`totalPendingReviewCount`, `onboardingPendingReviewCount`, `monthlyPendingReviewCount`) in `index()`.
- `resources/views/landlord/properties/create.blade.php`: Integrated auto-save status indicator, removed manual Save Draft buttons, added collapsible house rules suggestions, and made review fields null-safe.
- `resources/views/landlord/payments/index.blade.php`: Streamlined UI with priority review banner, segmented type selector, clean collection summaries, single confirmation modal, and responsive styling.
- `tests/Feature/LandlordAutosaveAndPaymentsTest.php`: Comprehensive feature test suite with 12 tests covering autosave, authorization, draft reuse, and payments UI.

## 3. Database Invariants
- **No Schema Migrations Required:** Operates 100% on existing database schema and columns (`approval_status`, `building_inclusions`, `house_rules`).
- **No Production Secrets Touched:** `.env` and production credentials untouched.
- **No Redis / Supervisor Required:** Fully compatible with Hostinger shared hosting environment.
