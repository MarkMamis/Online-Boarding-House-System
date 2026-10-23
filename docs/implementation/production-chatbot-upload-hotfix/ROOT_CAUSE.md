# Production Hotfix: Root Cause Analysis

## Executive Summary
This production hotfix investigates and resolves two issues reported on the live production domain (`https://mcc-boardinghouse.online` on Hostinger Shared Hosting):
1. **Chatbot 302 Redirect Chain**: Requests to `/chatbot/message` and `/chatbot/history` returned `302 Found`, redirecting to `/landlord/setup` and then `/landlord/dashboard`, disrupting AJAX calls and breaking JSON parsing.
2. **Landlord Photo Upload Rejection (`max:2048`)**: Landlords uploading room photos from mobile devices (specifically Android via Messenger in-app browser) received `"The image field must not be greater than 2048 kilobytes."`.

---

## 1. Chatbot 302 Redirect Chain Root Cause

### Observed Sequence in Production Network
```text
POST /chatbot/message -> 302 Found (Location: /landlord/setup)
GET  /chatbot/history -> 302 Found (Location: /landlord/setup)
GET  /landlord/setup  -> 302 Found (Location: /landlord/dashboard)
GET  /landlord/dashboard -> 200 OK
```

### Confirmed Application Mechanics
1. **Unconditional HTML Redirect in Middleware**:
   `app/Http/Middleware/EnsureRoleSelected.php` evaluated:
   ```php
   if ($user->role === 'landlord' && !($user->onboarding_complete ?? false)) {
       return redirect()->route('landlord.setup.show');
   }
   ```
   This middleware did NOT inspect `$request->expectsJson()`. When an AJAX request arrived without an explicit JSON expectation, it returned an HTTP 302 HTML redirect.

2. **Database State Inconsistency for Landlords**:
   In `app/Http/Controllers/LandlordSetupController.php`, when a landlord visited `/landlord/setup` and their setup details had been completed, `show()` executed:
   ```php
   if ($setupSnapshot['setup_submitted'] && ! $request->has('step')) {
       return redirect()->route('landlord.dashboard');
   }
   ```
   It redirected the landlord to `/landlord/dashboard` **without updating `$user->onboarding_complete = true` in the `users` table**. Thus, `$user->onboarding_complete` remained `false` (or `0`) in database storage even though the landlord's onboarding snapshot was complete.

3. **Missing Headers in Frontend History Fetch**:
   In `resources/views/components/chatbot.blade.php`:
   ```javascript
   const res = await fetch('/chatbot/history');
   ```
   The `fetch` call did not include `Accept: application/json` or `X-Requested-With: XMLHttpRequest`. The browser default `Accept: text/html,...` caused Laravel to treat the request as standard page navigation.

4. **JSON Parser Crash on Redirected HTML**:
   The browser followed the redirect chain to `/landlord/dashboard` (which returned HTML with `200 OK`). The script called `res.json()`, which threw `SyntaxError: Unexpected token '<'` trying to parse HTML as JSON, leaving the chatbot blank or unresponsive.

5. **Route Definition Middleware Redundancy**:
   `routes/web.php` lines 234-238 wrapped global chatbot routes with `student.setup` middleware (`EnsureStudentSetupComplete`), which was intended solely for student verification flows rather than multi-role portal assistance.

---

## 2. Landlord Photo Upload 2048 KB Rejection Root Cause

### Observed Production Error
```text
"The image field must not be greater than 2048 kilobytes."
```

### Confirmed Mechanics
1. **Unsynced Backend Deployment**:
   Local commit `5751654` previously raised `RoomController` rules from `max:2048` to `max:5120`. However, this change had not been synchronized via FTP/File Manager to the live Hostinger production server. The production server's `app/Http/Controllers/RoomController.php` was still running the old `max:2048` validation rule.

2. **Client-Side Optimization Skipped for 2-5 MB Photos**:
   In `public/js/image-optimizer.js` and `resources/js/image-optimizer.js`:
   ```javascript
   if (file.size <= opts.maxSizeBytes) { // maxSizeBytes = 5 * 1024 * 1024
       return { file, optimized: false };
   }
   ```
   When a landlord took a 2.5 MB - 4.5 MB photo on their Android camera, `file.size <= 5MB` was true. The optimizer returned `{ file, optimized: false }` without compressing it. The raw 3 MB file was sent directly to the server, immediately tripping the un-updated `max:2048` validator.

3. **Mobile WebView DataTransfer Fallback**:
   In Android WebViews (such as Messenger's in-app browser), manipulating `HTMLInputElement.files` via `DataTransfer` can fail or be restricted. When compression was skipped, the full multi-megabyte payload reached the server unoptimized.

---

## Resolution Strategy
1. **Chatbot**: Return 403/401 JSON for AJAX, auto-heal landlord `onboarding_complete` flag when profile data exists, remove `student.setup` from global chatbot routes, send explicit JSON headers, detect unexpected redirects, and handle errors gracefully.
2. **Photo Upload**: Set client-side compression threshold to `1.8 MB` (`1.8 * 1024 * 1024 bytes`). All mobile camera photos exceeding 1.8 MB are automatically scaled to max 2000px and 0.82 JPEG quality (~400 KB - 900 KB), ensuring compatibility with both 2048 KB and 5120 KB server limits. Ensure backend consistently accepts `max:5120` with custom 5 MB error messages.
