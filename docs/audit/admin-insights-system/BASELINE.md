# OBHS Admin Insights & System Modules Audit — Baseline Evidence

## 1. Repository State
- **Audit Date:** 2026-10-08
- **Branch:** `main`
- **Starting Commit:** `80492cecd141b4b870580871025372205eb91a8f`
- **Worktree Status:** Clean

---

## 2. In-Scope Feature Inventory

### Insights
1. **Reports (`/admin/reports`):**
   - Routes: `GET /admin/reports`, `GET /admin/reports/{report}`, `PUT /admin/reports/{report}`
   - Controller: `App\Http\Controllers\ReportController`
   - Policy: `App\Policies\ReportPolicy` (`create`, `update`, `markResponseRead`)
   - Model: `App\Models\Report` (columns: `id`, `user_id`, `title`, `description`, `priority`, `status`, `admin_response`, `response_read`, `resolved_at`, `created_at`, `updated_at`)
   - Services: `App\Services\ReportPriorityClassifier` (keyword/heuristic AI priority assignment)

2. **Analytics (`/admin/analytics`):**
   - Route: `GET /admin/analytics`
   - Controller: `App\Http\Controllers\AdminAnalyticsController`
   - Services: `App\Services\AnalyticsService`, `App\Services\BoardingMonitoringService`, `App\Services\DocumentExpirationService`
   - Models: `App\Models\Booking`, `App\Models\Property`, `App\Models\Room`, `App\Models\User`, `App\Models\LandlordDocument`
   - Data Layers: Canonical period resolver (`resolveReportingPeriod`), cross-system aggregations, de-duplicated distinct student/booking counts.

### System
3. **User Accounts — All Users (`/admin/users`):**
   - Route: `GET /admin/users`
   - Controller: `App\Http\Controllers\AuthController::adminUsers`
   - Models: `App\Models\User`, `App\Models\LandlordProfile`

4. **User Accounts — Students (`/admin/users/students`):**
   - Routes: `GET /admin/users/students`, `GET /admin/users/students/{user}`, `GET /admin/users/students/{user}/edit`, `PUT /admin/users/students/{user}`
   - Controller: `App\Http\Controllers\AuthController` (`adminUsersByRole`, `adminStudentDetails`, `adminEditStudent`, `adminUpdateStudent`)
   - Services: `App\Services\AcademicCatalogService`

5. **User Accounts — Landlords (`/admin/users/landlords`):**
   - Routes: `GET /admin/users/landlords`, `GET /admin/users/landlords/{user}`, `PUT /admin/users/landlords/{user}`, `POST /admin/users/landlords/{user}/status`
   - Controller: `App\Http\Controllers\AuthController` (`adminUsersByRole`, `adminLandlordDetails`, `adminUpdateLandlord`, `adminToggleLandlordStatus`)
   - Services: `App\Services\LandlordDocumentStatusService`

6. **Notifications (`/notifications`):**
   - Routes: `GET /notifications`, `POST /notifications/read-all`, `POST /notifications/{id}/read`
   - Controller: `App\Http\Controllers\NotificationController`
   - Models: `Illuminate\Notifications\DatabaseNotification`, `App\Models\User`
   - Notifications: `App\Notifications\SystemNotification`

---

## 3. Verification Commands Executed
- `php artisan route:list` -> Verified all active endpoints
- `php artisan test` -> 201 passed (956 assertions)
- `npm run build` -> Vite build cleanly generated assets in 2.78s
