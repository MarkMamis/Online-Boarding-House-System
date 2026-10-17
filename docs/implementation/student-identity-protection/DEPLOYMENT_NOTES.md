# Hostinger Shared Hosting Deployment Manifest & Notes

## 1. Hosting Context & Operating Invariants
- **Host:** Hostinger Shared Web Hosting
- **Server Environment:** Apache / PHP 8.2+ / MySQL 8.x
- **Invariants:**
  - No root shell access.
  - No Redis or in-memory queues (database or sync queues used).
  - No Supervisor or persistent Node.js daemons.
  - Dual development parity (local development matches Hostinger production).
  - No `.env` secrets or production database credentials committed to version control.

---

## 2. Deployment Manifest

### Modified Files:
1. `routes/web.php` — Added routes for password recovery (`/forgot-password`, `/reset-password`), recovery assistance intake, and admin identity recovery reviews.
2. `app/Http/Controllers/StudentSetupController.php` — Integrated early student ID availability checking with recovery guidance and assistance request intake.
3. `resources/views/student/setup/index.blade.php` — Added early Student ID question (Panel 0), recovery guidance card, inline assistance request form, re-indexed panels, and updated JS navigation/validation.
4. `resources/views/auth/login.blade.php` — Updated "Forgot password?" link to point to `route('password.request')`.
5. `resources/views/admin/approvals/students.blade.php` — Added sub-navigation tab linking to Identity Recovery Requests.
6. `tests/Feature/UnifiedOnboardingExperienceTest.php` — Aligned test assertions with updated recovery-oriented copy.

### New Files:
1. `app/Http/Controllers/Auth/PasswordResetController.php` — Self-service password reset controller with generic responses and rate limiting.
2. `app/Http/Controllers/AdminIdentityRecoveryController.php` — Admin identity recovery review workspace controller.
3. `app/Models/IdentityRecoveryRequest.php` — Eloquent model for identity recovery requests with user/reviewer relationships.
4. `database/migrations/2026_10_17_200000_create_identity_recovery_requests_table.php` — Additive schema migration creating `identity_recovery_requests` table.
5. `resources/views/auth/forgot-password.blade.php` — Modern branded password recovery request view.
6. `resources/views/auth/reset-password.blade.php` — Modern branded password reset confirmation view.
7. `resources/views/admin/approvals/identity_recoveries.blade.php` — Admin workspace view for reviewing and processing recovery claims.
8. `tests/Feature/StudentIdentityProtectionTest.php` — 14 comprehensive feature tests for student identity protection and recovery.

---

## 3. Hostinger Production Execution Steps

### Step 1: Backup Production Database
Before running migrations, take a snapshot of the production MySQL database via Hostinger phpMyAdmin or CLI:
```bash
mysqldump -u <db_user> -p <db_name> > backup_pre_identity_recovery_$(date +%F).sql
```

### Step 2: Deploy Codebase
Deploy the updated files via Git or Hostinger Git deployment webhook.

### Step 3: Run Additive Migration
Execute the migration to create the `identity_recovery_requests` table:
```bash
php artisan migrate --force
```
*Note: This migration is completely additive and does not drop, alter, or lock existing tables.*

### Step 4: Clear & Re-cache Optimized Application State
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Step 5: Verification Check
1. Visit `https://mcc-boardinghouse.online/forgot-password` to confirm the self-service recovery page renders cleanly.
2. Log in as an administrator and navigate to `https://mcc-boardinghouse.online/admin/identity-recoveries` to verify the identity review workspace.
