# Hostinger Shared Hosting Deployment Manifest

This document provides the definitive file manifest and step-by-step instructions for deploying the Student Marketplace Rebuild to the live Hostinger Shared Hosting environment.

---

## 1. Hosting Environment Invariants

- **Host:** Hostinger Shared Web Hosting (cPanel / hPanel)
- **Runtime:** PHP 8.2+ with Apache (mod_rewrite enabled) and MySQL 8.x
- **Invariants:**
  - No root server access, no Supervisor or systemd daemons, no Redis, no background node processes.
  - Assets must be built locally before synchronization (`npm run build`).
  - Production `.env` and production database must NEVER be overwritten or replaced.

---

## 2. File Change Manifest

### A. Controllers & Backend Logic
```
app/Http/Controllers/AuthController.php
app/Http/Controllers/BookingController.php
app/Http/Controllers/StudentPropertyController.php
```

### B. Blade Views & Components
```
resources/views/layouts/student_dashboard.blade.php
resources/views/components/student-sidebar.blade.php
resources/views/components/room-card.blade.php
resources/views/student/dashboard.blade.php
resources/views/student/rooms/index.blade.php
resources/views/student/rooms/show.blade.php
resources/views/student/bookings/index.blade.php
resources/views/student/bookings/partials/booking-card.blade.php
resources/views/student/properties/map.blade.php
resources/views/student/reports/index.blade.php
resources/views/messages/index.blade.php
```

### C. Compiled Assets (Vite Production Distribution)
```
public/build/manifest.json
public/build/assets/app-*.css
public/build/assets/app-*.js
```

### D. Automated Tests & Harness
```
tests/Feature/StudentMarketplaceExperienceTest.php
```

---

## 3. Deployment Steps (SFTP / Git / CI Sync)

1. **Pre-Deployment Local Verification:**
   - Run tests: `php artisan test --filter=StudentMarketplaceExperienceTest`
   - Compile frontend: `npm run build`
   - Cache check: `php artisan view:clear && php artisan view:cache`

2. **File Transfer to Hostinger:**
   - Synchronize modified files listed in Section 2 to `public_html/` (or repository directory on Hostinger).
   - Transfer `public/build/` directory completely.

3. **Post-Deployment Server Commands (via SSH or hPanel Terminal):**
   ```bash
   php artisan route:clear
   php artisan config:clear
   php artisan view:clear
   php artisan view:cache
   php artisan optimize
   ```

4. **Rollback Contingency:**
   - In case of unexpected issues, revert the files listed in Section 2 to their previous git commit baseline (`HEAD~1`).
   - Run `php artisan view:clear` to flush compiled templates.
