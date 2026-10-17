# Hostinger Shared Hosting Deployment Manifest
**Second Pass Refinement — OBHS Student & Tenant Marketplace**

This document specifies the exact file changes and synchronization checklist for deploying the second-pass refinements to Hostinger Shared Hosting.

---

### 1. Modified Files to Deploy

#### A. Backend Controllers
```
app/Http/Controllers/RoomController.php
```

#### B. Blade Templates & Shared Components
```
resources/views/layouts/student_dashboard.blade.php
resources/views/components/room-card.blade.php
resources/views/student/dashboard.blade.php
resources/views/student/rooms/index.blade.php
resources/views/student/rooms/show.blade.php
resources/views/student/bookings/index.blade.php
resources/views/student/tenant-dashboard.blade.php
```

#### C. Production Frontend Build Assets
```
public/build/manifest.json
public/build/assets/app-*.css
public/build/assets/app-*.js
```

#### D. Automated Tests
```
tests/Feature/StudentMarketplaceExperienceTest.php
```

---

### 2. Deployment Protocol & Cache Clearing

After transferring the files above via SFTP or Git:

```bash
# Clear compiled views and re-cache
php artisan view:clear
php artisan view:cache

# Optimize application
php artisan optimize
```

---

### 3. Verification Steps
1. Navigate to `/student/dashboard`: Confirm clean category chips, search bar, and featured rooms without redundant map/support banners.
2. Navigate to `/student/rooms`: Test keyword search (e.g. by boarding house name), price bounds filtering, and verify no clamping bugs occur.
3. Navigate to `/student/requests`: Verify 2 clean tabs (*Active & Upcoming*, *Past Stays & History*) and no duplicate header actions.
4. Navigate to `/student/tenant-dashboard`: Verify clean resident stay presentation with roommates, rules, and working leave request form.
