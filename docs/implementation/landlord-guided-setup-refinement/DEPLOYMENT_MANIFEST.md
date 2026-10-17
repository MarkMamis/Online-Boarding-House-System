# Deployment Manifest & Production Readiness

## 1. Target Environment
- **Host:** Hostinger Shared Web Hosting
- **PHP Version:** PHP 8.2+
- **Database:** MySQL 8.x
- **Web Server:** Apache with mod_rewrite
- **Node/NPM:** Vite asset bundling

## 2. Inventory of Modified Files
- `resources/views/landlord/properties/create.blade.php`: Refactored to guided onboarding card format with no visible stepper, decoupled first-room, Leaflet integration, and editable review.
- `resources/views/landlord/rooms/create.blade.php`: Refactored to guided onboarding card format with contextual header, simplified inclusion chips, dynamic pricing cards, and editable review.
- `tests/Feature/LandlordTransformationTest.php`: Added regression assertions verifying stepper absence, guided UI elements, and payload submission.

## 3. Database & Migration Invariants
- **No Schema Changes:** This refactoring is strictly UI/UX. No database migrations were added or modified.
- **No Foreign Key Alterations:** All property, room, booking, and user constraints remain untouched.
- **No Seeders Required:** Operates cleanly on existing tables.

## 4. Production Constraints Adhered To
- **No Redis Dependency:** Session and cache use database/file drivers as standard for Hostinger shared environments.
- **No Supervisor / Horizon:** All request handling remains synchronous.
- **Strict SQL Preservation:** Strict mode and `ONLY_FULL_GROUP_BY` were not touched or bypassed.
- **No Production Secrets Exposed:** No `.env` credentials touched or committed.
- **Dual Local & Hostinger Parity:** Compatible with both local `php artisan serve` and Apache `.htaccess` document roots.
