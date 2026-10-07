# OBHS Security Baseline & Guardrails

This document establishes the mandatory security standards and defensive engineering requirements for the Online Boarding House System (OBHS).

---

## 1. Authentication & Session Security

- **Password Hashing:** Passwords must be hashed using Laravel's default Bcrypt/Argon2 implementation with proper cost factors.
- **Session Regeneration:** Sessions must regenerate on login (`$request->session()->regenerate()`) and invalidate on logout to prevent session fixation attacks.
- **Email Verification:** Student and landlord accounts must verify their email address before accessing portal routes or executing state changes.
- **Remember-Me Tokens:** Stored securely and invalidated upon password updates.

---

## 2. Server-Side Authorization & IDOR Defense

- **No Client-Trusted IDs:** Form requests and JSON payloads must never be trusted for `student_id`, `landlord_id`, `owner_id`, or `user_id`. Always derive the acting user's ID from `Auth::id()`.
- **Policy Enforcement:** Use Laravel Policies (`app/Policies/*`) or explicit scoped Eloquent queries on all resource endpoints.
- **No Blade-Only Authorization:** Hiding a button or modal in Blade does NOT constitute authorization. Backend controllers must reject unauthorized requests with HTTP 403.
- **Role Isolation:** Every route must be protected by `RoleMiddleware` or specific role gate middlewares (`role:admin`, `role:landlord`, `role:student`).

---

## 3. Data & Input Validation

- **Mass Assignment Defense:** All Eloquent models must explicitly define `$fillable` attributes. Sensitive administrative attributes (e.g. `role`, `is_active`, `school_id_verification_status`, `business_permit_status`) must never be mass-assigned from raw request inputs.
- **Strict SQL Injection Prevention:** Always use parameterized query bindings (`?`) with `selectRaw`, `whereRaw`, and `DB::raw`. Never interpolate unescaped string variables into raw SQL expressions.
- **XSS Prevention:** Blade templates must use `{{ $escaped }}` syntax for all user-supplied strings. Raw `{!! $unescaped !!}` is strictly forbidden for user inputs.

---

## 4. File Upload & Document Privacy

- **MIME & Extension Validation:** Every file upload endpoint must enforce explicit MIME types (e.g. `mimes:jpg,jpeg,png,pdf`) and maximum file size limits (e.g. `max:5120` for 5MB).
- **Private Storage:** Student IDs, enrollment records, business permits, fire safety certificates, and lease contracts must be stored on private storage disks (Cloudflare R2, private Supabase, or unexposed local storage).
- **Authenticated Streaming:** All sensitive file access must flow through `GET /files/{path}` (`FileController@show`), which checks user role and resource ownership.

---

## 5. AI Assistant & Chatbot Guardrails

- **Server-Side Tool Authorization:** The LLM is never trusted to make authorization decisions. Every AI tool must be role-gated in `AiToolRegistry` and validated in `AiToolExecutor`.
- **Output Sanitization:** Sensitive fields (`password`, `remember_token`, `token`, `secret`, `hash`, storage keys) must be stripped from all tool results before they are returned to the model or user.
- **Rate Limiting:** Chatbot endpoints must remain protected by dedicated rate limiting (`throttle:chatbot`).

---

## 6. Secrets & Production Governance

- **Never Commit Secrets:** `.env`, private storage credentials, database passwords, SMTP credentials, and API keys must NEVER be committed to Git or bundled into deployment archives.
- **No `APP_DEBUG=true` in Production:** Debug mode must remain disabled in production to prevent stack traces and database schemas from being exposed to end users.
- **Continuous Audit:** Security is an ongoing engineering property. Any new route, model, or controller must undergo security verification before deployment.
