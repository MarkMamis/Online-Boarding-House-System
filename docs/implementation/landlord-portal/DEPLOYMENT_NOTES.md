# Landlord Portal Deployment Notes & Production Invariants

**Repository:** Online Boarding House System (OBHS)  
**Host Environment:** Hostinger Shared Hosting (Apache, PHP 8.2+, MySQL 8.x)  
**Date:** 2026-10-08  

---

## 1. Production Invariants Check

- **Database Migrations:** None required. All integrity fixes and monitoring additions utilize existing database tables (`properties`, `rooms`, `bookings`, `leave_requests`, `tenant_payments`, `landlord_documents`).
- **Strict MySQL Mode:** Fully compliant. Zero non-standard SQL functions (replaced `FIELD()` with portable ANSI `CASE WHEN`). `ONLY_FULL_GROUP_BY` remains strictly enforced.
- **Queues & Background Daemons:** No Redis, Supervisor, or worker daemons introduced.
- **File Uploads & Storage:** Preserves existing `FileStorageService` dual-disk hierarchy and secure signed download URLs.
- **Asset Compilation:** Built via Vite (`npm run build`). No CDN external framework dependencies added.

---

## 2. Standard Hostinger Deployment Sequence

1. Pull latest commit from repository (`git pull origin main`).
2. Clear and optimize caches:
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```
3. Verify directory permissions on storage and bootstrap cache:
   ```bash
   chmod -R 775 storage bootstrap/cache
   ```
4. Verify web endpoint health:
   - `GET /landlord/dashboard` (200 OK)
   - `GET /landlord/monitoring/properties` (200 OK)
   - `GET /landlord/monitoring/compliance` (200 OK)
   - `GET /landlord/tenants` (200 OK)
