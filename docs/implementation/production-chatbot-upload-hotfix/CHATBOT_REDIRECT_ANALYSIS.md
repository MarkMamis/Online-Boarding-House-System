# Chatbot Redirect Chain Analysis

## 1. Network Evidence & Destination Tracing

The observed network sequence in production:
```text
1. POST /chatbot/message -> 302 Found (Location: /landlord/setup)
2. GET  /chatbot/history -> 302 Found (Location: /landlord/setup)
3. GET  /landlord/setup  -> 302 Found (Location: /landlord/dashboard)
4. GET  /landlord/dashboard -> 200 OK (text/html)
```

### Destination Interpretation
| Step | URL | Status | Layer Triggering Redirect | Rationale |
|------|-----|--------|---------------------------|-----------|
| 1 & 2 | `/chatbot/*` | 302 | `EnsureRoleSelected` Middleware | `$user->role === 'landlord' && !($user->onboarding_complete ?? false)` redirected to `landlord.setup.show`. |
| 3 | `/landlord/setup` | 302 | `LandlordSetupController@show` | Controller observed `$setupSnapshot['setup_submitted'] == true` and bounced the user to `landlord.dashboard`. |
| 4 | `/landlord/dashboard` | 200 | `AuthController@landlordDashboard` | Dashboard route is not guarded by `role.selected`, rendering dashboard HTML normally. |

---

## 2. Why Did the Redirect Loop Occur?

1. **State Disconnect**:
   - `LandlordProfile` table held profile information (`contact_number`, `boarding_house_name`, `about`, payment details).
   - `users.onboarding_complete` was `0`/`false` because earlier onboarding forms did not update the `users` table directly upon completion.
   - When the landlord visited `/landlord/setup`, the controller redirected to `/landlord/dashboard` **without updating the `users.onboarding_complete` flag**.
   - As a result, every subsequent request through `role.selected` middleware (such as chatbot endpoints) saw `onboarding_complete === false` and triggered a 302 redirect back to `/landlord/setup`.

2. **Accept Header Handling in AJAX**:
   - Standard browser `fetch` without explicit `Accept: application/json` defaults to `text/html`.
   - Laravel checks `$request->expectsJson()` by inspecting the `Accept` header. Without `application/json`, it defaults to browser redirection rather than returning a 401/403 JSON error.

---

## 3. Implemented Fixes

### A. Middleware Self-Healing (`EnsureRoleSelected.php`)
Before redirecting, `EnsureRoleSelected` now evaluates whether the landlord has already submitted their setup data:
```php
$profileComplete = filled($user->contact_number)
    && filled($user->boarding_house_name)
    && filled(optional($landlordProfile)->about);

if ($profileComplete) {
    $user->forceFill(['onboarding_complete' => true])->save();
    return $next($request);
}
```
If the profile is complete, it immediately persists `onboarding_complete = true` and continues to the target route without redirecting.

### B. ExpectsJson Handling
If setup is genuinely incomplete:
```php
if ($request->expectsJson()) {
    return response()->json([
        'message' => 'Complete your landlord setup before accessing this feature.',
        'redirect' => route('landlord.setup.show'),
    ], 403);
}
```
For AJAX/JSON requests, HTTP 403 JSON is returned instead of an HTTP 302 redirect.

### C. Controller Persistence Sync (`LandlordSetupController.php`)
In `show()`:
```php
if ($setupSnapshot['setup_submitted']) {
    if (! ($user->onboarding_complete ?? false)) {
        $user->forceFill(['onboarding_complete' => true])->save();
    }
    if (! $request->has('step')) {
        return redirect()->route('landlord.dashboard');
    }
}
```
Whenever `/landlord/setup` recognizes that setup is complete, it guarantees `onboarding_complete` is set to `true` in the database.

### D. Route Definition Cleanup (`routes/web.php`)
Removed `student.setup` from the global chatbot route group:
```php
Route::middleware(['auth', 'verified', 'role.selected'])->group(function () {
    Route::get('/chatbot/history', [ChatbotController::class, 'history'])->name('chatbot.history');
    Route::post('/chatbot/message', [ChatbotController::class, 'message'])->name('chatbot.message')->middleware('throttle:chatbot');
});
```
This ensures student setup checks do not evaluate on global authenticated features.
