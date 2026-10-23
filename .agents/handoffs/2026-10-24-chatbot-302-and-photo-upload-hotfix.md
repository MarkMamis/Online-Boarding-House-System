# OBHS Handoff: Chatbot 302 Redirect & Mobile Photo Upload Hotfix

- **Date**: 2026-10-24
- **Author**: Antigravity Assistant
- **Corpus**: `MarkMamis/Online-Boarding-House-System`
- **Domain**: Chatbot (AI Assistant) & Landlord Room/Property Photo Uploads

---

## Changes Implemented

### 1. Chatbot 302 Redirect Fix
- Updated `app/Http/Middleware/EnsureRoleSelected.php`:
  - Returns HTTP 403 JSON (`message`, `redirect`) when `$request->expectsJson()` is true instead of an HTTP 302 HTML redirect.
  - Automatically heals completed landlords: if a landlord's profile data (`contact_number`, `boarding_house_name`, `about`) exists, it immediately sets `$user->forceFill(['onboarding_complete' => true])->save()` and proceeds to the target route.
- Updated `app/Http/Middleware/EnsureStudentSetupComplete.php`:
  - Returns HTTP 403 JSON for JSON requests when student setup is incomplete.
- Updated `app/Http/Controllers/LandlordSetupController.php`:
  - In `show()`, ensures `$user->onboarding_complete = true` is persisted to the database before redirecting to `/landlord/dashboard`.
  - Added safe fallback for `payment_gcash_number` to `$user->contact_number`.
- Updated `app/Http/Controllers/ChatbotController.php`:
  - Wrapped AI delegation in a safe `try/catch` with sanitized logging (no raw messages or credentials logged). Returns 500 JSON upon provider failures.
- Updated `routes/web.php`:
  - Removed `student.setup` from the global authenticated chatbot route group (`['auth', 'verified', 'role.selected']`).
- Updated `resources/views/components/chatbot.blade.php`:
  - Added `Accept: application/json` and `X-Requested-With: XMLHttpRequest` to `loadHistory()`.
  - Added `X-Requested-With: XMLHttpRequest` and `credentials: 'same-origin'` to `sendMessage()`.
  - Handled 401 (session expired), 403 (setup required), 422 (validation), 429 (rate limit), and unexpected redirects without parsing HTML as JSON. User input is preserved on error for easy retry.

### 2. Landlord Mobile Photo Uploads (5 MB Ceiling & 1.8 MB Auto-Compression)
- Updated `public/js/image-optimizer.js` and `resources/js/image-optimizer.js`:
  - Added `compressThresholdBytes: 1.8 * 1024 * 1024` (1.8 MB). Any camera photo > 1.8 MB triggers client-side canvas scaling (max 2000px, 0.82 JPEG quality), reducing photo sizes to ~450 KB - 900 KB.
  - Standardized friendly error messages:
    - `"This photo is too large. Please choose one under 5 MB."`
    - `"We couldn't prepare this photo. Please try another image."`
- Updated `app/Http/Controllers/RoomController.php` & `PropertyController.php`:
  - Verified and standardized `max:5120` backend validation with custom 5 MB error messages across `store`, `update`, `quickStore`, and `autoSave`.

---

## Verification Performed
1. `php artisan test --filter=ChatbotRedirectAndPhotoUploadHotfixTest`: **14/14 PASS** (44 assertions).
2. `php artisan test --filter=LandlordPhotoUploadOptimizationTest`: **24/24 PASS** (82 assertions).
3. `php artisan route:list --path=chatbot -v`: Verified clean middleware stack (`web`, `auth`, `verified`, `role.selected`).
4. `php artisan view:cache`: **Blade templates cached successfully**.
5. `npm run build`: **Vite v7.3.1 build succeeded** (`app-BxQyoG-E.js`, `app-Ddjl58YA.css`).
6. `php vendor/bin/pint`: Formatted modified files cleanly.
