# Deployment Manifest (`DEPLOYMENT_MANIFEST.md`)

## Environment Target
- **Production Host:** Hostinger Shared Web Hosting
- **Server OS / Stack:** Linux / Apache / MySQL 8.x (`ONLY_FULL_GROUP_BY` strict mode) / PHP 8.2+
- **Invariants:** No root server access, no Supervisor/systemd, dual development parity.

## Files to Deploy
1. **Blade Views:**
   - `resources/views/student/onboarding/show.blade.php` (Complete redesign of student move-in guided view)
   - `resources/views/student/onboarding/index.blade.php` (Modernized move-in records list)
   - `resources/views/landlord/onboarding/review.blade.php` (Single focused landlord review workspace)
   - `resources/views/landlord/onboarding/index.blade.php` (Simplified move-in pipeline directory)
   - `resources/views/admin/onboardings/show.blade.php` (Aligned read-only verification view)
2. **Automated Feature Test:**
   - `tests/Feature/TenantMoveInTransformationTest.php`
3. **Compiled Assets:**
   - Run `npm run build` locally and upload `public/build/` to production.

## Database Considerations
- **No Schema Changes / No Migrations Required:**
  - All modifications are presentation layer and UI/UX refinements.
  - Zero database schema migrations introduced.
  - Existing `tenant_onboardings` records and contracts remain 100% backward-compatible.

## Post-Deployment Commands
```bash
php artisan view:clear
php artisan view:cache
php artisan route:clear
php artisan route:cache
php artisan config:cache
```
