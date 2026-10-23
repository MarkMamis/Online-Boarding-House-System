# Hostinger Deployment Manifest & Synchronization Guide

This application is deployed to Hostinger Shared Web Hosting via FTP / Hostinger File Manager.

> [!IMPORTANT]
> The following files MUST be synchronized together to ensure frontend assets, middleware, and controllers remain strictly aligned.

---

## 1. Exact Changed Files to Upload

### Backend Controllers & Middleware
1. `app/Http/Middleware/EnsureRoleSelected.php`
   - *Fix*: Returns 403 JSON for AJAX; auto-heals `$user->onboarding_complete = true` when profile details exist.
2. `app/Http/Middleware/EnsureStudentSetupComplete.php`
   - *Fix*: Returns 403 JSON for AJAX requests instead of an unexpected 302 HTML redirect.
3. `app/Http/Controllers/ChatbotController.php`
   - *Fix*: Safe try/catch around AI assistant delegation; sanitized logging without credentials or raw messages; returns 500 JSON upon provider failures.
4. `app/Http/Controllers/LandlordSetupController.php`
   - *Fix*: Auto-heals `$user->onboarding_complete = true` in `show()` before redirecting to dashboard; safe fallback for `payment_gcash_number`.
5. `app/Http/Controllers/RoomController.php`
   - *Fix*: Enforces `max:5120` (5 MB) in `store`, `update`, and `quickStore` with friendly custom message: `"This photo is too large. Please choose one under 5 MB."`.
6. `app/Http/Controllers/PropertyController.php`
   - *Fix*: Enforces `max:5120` in `store`, `update`, and `autoSave` with friendly custom message: `"This photo is too large. Please choose one under 5 MB."`.

### Routes
7. `routes/web.php`
   - *Fix*: Removed `student.setup` from the global authenticated chatbot route group.

### Frontend Blade Components & Client Scripts
8. `resources/views/components/chatbot.blade.php`
   - *Fix*: Added `Accept: application/json` & `X-Requested-With: XMLHttpRequest` to `loadHistory()`; handles 401, 403, 429, and redirect states cleanly; preserves user input on failures.
9. `public/js/image-optimizer.js`
   - *Fix*: Added `compressThresholdBytes = 1.8 * 1024 * 1024` (1.8 MB). Auto-compresses photos > 1.8 MB to max 2000px and 0.82 JPEG quality (~450 KB - 900 KB).
10. `resources/js/image-optimizer.js`
    - *Fix*: Vite ESM module updated identically with `compressThresholdBytes: 1.8 MB`.

### Compiled Vite Production Assets
11. `public/build/manifest.json`
12. `public/build/assets/app-BxQyoG-E.js`
13. `public/build/assets/app-Ddjl58YA.css`

---

## 2. Post-Deployment Cache Clearing Steps

After uploading files via Hostinger File Manager or FTP:

### Option A: If SSH access is available
Run:
```bash
php artisan optimize:clear
php artisan view:cache
php artisan route:cache
```

### Option B: If SSH is unavailable
1. Navigate to Hostinger File Manager.
2. In the `bootstrap/cache/` directory, delete:
   - `routes-v7.php`
   - `config.php`
   - `services.php`
   - `packages.php`
3. In `storage/framework/views/`, delete all compiled `.php` files (do NOT delete the directory itself or `.gitignore`).
4. In Hostinger hPanel, toggle PHP OPcache off and on, or click "Restart PHP" to ensure PHP-FPM loads the updated PHP files immediately.

> [!CAUTION]
> Do NOT create public web routes that execute arbitrary shell or Artisan commands.
