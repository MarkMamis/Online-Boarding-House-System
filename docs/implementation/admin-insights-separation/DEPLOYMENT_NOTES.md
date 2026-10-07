# Deployment Notes — Admin Insights & Reporting Separation

## 1. Production Context
- **Target Host:** Hostinger Shared Web Hosting (Apache / MySQL 8.x / PHP 8.2+).
- **Invariants:** No root server access, no Supervisor/systemd, no Redis, standard dual development parity (Local + Hostinger).

---

## 2. Database & Schema Invariants
- **Zero Database Schema Migrations:** No schema modifications or table alterations were introduced. All data resolutions reuse existing indexed relations (`bookings`, `rooms`, `properties`, `users`, `landlord_documents`, `incident_reports`).
- **Strict SQL Preservation:** `ONLY_FULL_GROUP_BY` and MySQL strict mode remain 100% active and respected in all analytical queries.

---

## 3. Post-Deployment Steps
After uploading the release package to Hostinger:
1. `php artisan route:clear`
2. `php artisan route:cache`
3. `php artisan view:clear`
4. `php artisan view:cache`
5. `php artisan config:cache`
