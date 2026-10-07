# Authentication & Authorization Security Audit

This document details the security review of the authentication, role-based access control, ownership verification, and authorization layers of the OBHS application.

---

## 1. Audit Scope & Methodology

- **Target Files:**
  - `routes/web.php`
  - `config/auth.php`
  - `bootstrap/app.php`
  - `app/Http/Middleware/*` (`RoleMiddleware`, `EnsureRoleSelected`, `EnsureStudentSetupComplete`, `EnsureLandlordSetupStep`)
  - `app/Policies/*` (`BookingPolicy`, `MessagePolicy`, `PropertyPolicy`, `ReportPolicy`, `RoomPolicy`, `TenantOnboardingPolicy`)
  - `app/Http/Controllers/*` (`AuthController`, `FileController`, `BookingController`, `PaymentController`, `StudentPaymentController`, `TenantOnboardingController`, `LandlordDocumentController`, `AdminLandlordDocumentController`, `PublicPropertyMapController`, `ChatbotController`)
  - `public/hostinger-setup.php`

---

## 2. Findings Summary Table

| Finding ID | Severity | Category | Title | Safe to Patch Now? |
| :--- | :--- | :--- | :--- | :--- |
| **AUTH-SEC-001** | **CRITICAL** | Setup / Deployment | Hardcoded `obhs2026` Key in Public Deployment Helper | Yes (Document & Guard) |
| **AUTH-SEC-002** | **HIGH** | IDOR / Query Scope | Legacy File Access Queries Use `->first()` Across Global Records | Yes |
| **AUTH-SEC-003** | **HIGH** | Role Gating | Admin Registration Route Conditionally Public via Env Flag | Informational / Guarded |
| **AUTH-SEC-004** | **MEDIUM** | Authorization | Several Controller Actions Rely on Query Filters Rather Than Explicit Policy Calls | Yes |
| **AUTH-SEC-005** | **MEDIUM** | Middleware Scope | Shared `/messages` & `/notifications` Rely on `student.setup` Middleware Filter | Yes |
| **AUTH-SEC-006** | **LOW** | Session Security | Email Verification Link Logout Clears Session But Leaves User to Re-login | Intended Workflow |

---

## 3. Detailed Security Findings

### AUTH-SEC-001: Hardcoded Default Secret Key in Public Deployment Helper
- **Severity:** `CRITICAL`
- **Affected File:** [`public/hostinger-setup.php`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/public/hostinger-setup.php#L29-L31)
- **Description:**
  In `public/hostinger-setup.php`:
  ```php
  $envSecret = config('app.setup_secret') ?: env('SETUP_SECRET', 'obhs2026');
  $isAuthorized = ($providedKey !== '' && ($providedKey === $envSecret || $providedKey === 'obhs2026'));
  ```
  Even when `SETUP_SECRET` is configured with a secure random key in production `.env`, the boolean condition `|| $providedKey === 'obhs2026'` allows anyone passing `?key=obhs2026` to unlock database migrations, `storage:link`, `optimize:clear`, and file deletion.
- **Attack Scenario:** An attacker requests `https://production-domain/hostinger-setup.php?key=obhs2026` and can trigger destructive actions or probe database schema state.
- **Recommendation:**
  1. Remove `hostinger-setup.php` immediately after initial deployment.
  2. In `tools/package-hostinger.ps1`, ensure `hostinger-setup.php` is only packaged when explicitly needed, or sanitize the default password check.
  3. Fix condition to require strict match against non-default `SETUP_SECRET`.

---

### AUTH-SEC-002: Legacy Path Authorization Uses `->first()` Across Global Table Records
- **Severity:** `HIGH`
- **Affected File:** [`app/Http/Controllers/FileController.php`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/app/Http/Controllers/FileController.php#L134-L143)
- **Description:**
  When evaluating legacy un-prefixed file paths, `FileController@authorizeLegacyPath` executes:
  ```php
  $onboarding = TenantOnboarding::with(['booking.student'])
      ->where('uploaded_documents', 'like', '%' . $path . '%')
      ->orWhere('contract_signature_path', $path)
      ->orWhere('landlord_contract_signature_path', $path)
      ->orWhere('payment_proof_path', $path)
      ->first();

  if ($onboarding && (int) optional($onboarding->booking)->student_id === (int) $user->id) {
      return true;
  }
  ```
  Because `->first()` evaluates without grouping user scope into the SQL `WHERE` clause, if document path matching returns another user's onboarding row first, the authorization check fails for the legitimate owner. Additionally, the `orWhere` conditions lack parentheses encapsulation.
- **Attack / Failure Scenario:**
  A legitimate student or landlord attempting to view their legacy document is denied if another record shares a substring, or an edge condition creates unintended record matching.
- **Recommendation:**
  Group queries by `where(function($query) use ($path) { ... })` and include `whereHas('booking', fn($q) => $q->where('student_id', $user->id))` inside the query constraint before calling `->exists()`.

---

### AUTH-SEC-003: Public Admin Registration Controlled by Configuration Flag
- **Severity:** `HIGH` (if enabled in production) / `INFORMATIONAL` (when disabled)
- **Affected File:** [`routes/web.php`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/routes/web.php#L191-L199)
- **Description:**
  `routes/web.php` exposes:
  ```php
  if (config('auth.public_admin_registration', false)) {
      Route::get('/register/admin', [AuthController::class, 'showRegisterAdminForm'])->name('register.admin');
      Route::post('/register/admin', [AuthController::class, 'registerAdmin'])->name('register.admin.submit');
  } else {
      Route::middleware(['role:admin'])->group(function () {
          Route::get('/register/admin', [AuthController::class, 'showRegisterAdminForm'])->name('register.admin');
          Route::post('/register/admin', [AuthController::class, 'registerAdmin'])->name('register.admin.submit');
      });
  }
  ```
- **Current Protection:** Default config is `false`, falling back to `role:admin` middleware requirement.
- **Risk:** If an administrator accidentally sets `PUBLIC_ADMIN_REGISTRATION=true` in `.env`, arbitrary visitors can register admin accounts without invitation or verification.
- **Recommendation:** Add an explicit deployment check in `DeploymentCheck.php` to fail preflight if `auth.public_admin_registration` is `true` in production.

---

### AUTH-SEC-004: Inconsistent Policy Usage in Payment and Leave Request Controllers
- **Severity:** `MEDIUM`
- **Affected Files:** [`PaymentController.php`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/app/Http/Controllers/PaymentController.php), [`StudentLeaveRequestController.php`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/app/Http/Controllers/StudentLeaveRequestController.php)
- **Description:**
  While controllers properly enforce scoping using Eloquent queries (e.g. `whereHas('room.property', fn($q) => $q->where('landlord_id', $landlordId))`), they do not consistently call `$this->authorize('ability', $model)` policies.
- **Recommendation:** Maintain explicit Eloquent query scoping as the primary barrier and register formal policies where future API endpoints might bind models directly.

---

### AUTH-SEC-005: `student.setup` Middleware Interaction on Shared Routes
- **Severity:** `MEDIUM`
- **Affected File:** [`app/Http/Middleware/EnsureStudentSetupComplete.php`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/app/Http/Middleware/EnsureStudentSetupComplete.php#L21-L23)
- **Description:**
  `EnsureStudentSetupComplete` checks `if ($user->role !== 'student') return $next($request);`.
  When applied to shared route groups (such as `/messages`, `/notifications`, and `/chatbot`), landlord and admin users pass through cleanly, but students with incomplete setup are redirected to `/student/setup`.
- **Status:** Working as intended, but must be documented so future agents do not inadvertently remove `student.setup` from shared groups.

---

## 4. Strengths Identified

1. **Strict Server-Side Role Enforcement:** `RoleMiddleware` uses variable argument unpacking and strict array comparisons, rejecting unauthenticated users with 401/403.
2. **Setup Isolation:** Users with role `pending` cannot access student or landlord areas; students cannot access landlord properties; landlords cannot access student dashboards.
3. **No Client-Trusted IDs:** IDs (`user_id`, `student_id`, `landlord_id`) for writes are derived from `Auth::id()` or validated parent relations.
4. **AI Role Guardrails:** AI assistant tools check `AiToolRegistry::isAuthorized($toolName, $user->role)` before execution.
