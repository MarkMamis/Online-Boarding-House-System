# OBHS Full Codebase Audit Summary

- **Audit Date:** 2026-10-08
- **Project:** Online Boarding House System (OBHS)
- **Framework:** Laravel 12.39.0 / PHP 8.2.12
- **Environment Context:** Dual Support (Local Development + Hostinger Shared Hosting Production)
- **Baseline Git Commit:** `a733a1440090c3ba9a228435a2c2b18cacac5aad`

---

## 1. Executive Summary

A comprehensive architectural, security, database, and operational audit was performed across the entire OBHS codebase. The application is a live, feature-rich student boarding house management and discovery platform for Mindoro State University (MinSU).

The system features robust role isolation, strict server-side permissions for modern entities, comprehensive AI assistant guardrails, multi-driver cloud storage with Cloudflare R2 and Supabase fallback, and clean strict-SQL query designs.

The audit identified critical deployment governance requirements (specifically surrounding the public `hostinger-setup.php` helper script), subtle edge cases in legacy un-prefixed file authorization queries, and architectural technical debt in oversized controllers (`AuthController.php`).

---

## 2. Architecture & Roles Summary

### Architecture Overview
- **Backend:** Laravel 12 MVC + dedicated Domain Services layer (`AI/`, `AnalyticsService`, `BoardingMonitoringService`, `FileStorageService`, `DocumentExpirationService`, `LandlordDocumentStatusService`).
- **Frontend:** Blade templates styled with TailwindCSS 4 and bundled via Vite 7.
- **Routing:** 171 registered routes across Public, Auth, Student, Landlord, Admin, AI, and File handling.
- **Storage:** Private multi-driver cloud storage with authenticated streaming endpoint `/files/{path}`.

### Discovered Roles & Workflow States
1. **`pending`:** User registered but has not selected Student or Landlord role.
2. **`student`:**
   - Unverified Email -> Verified -> Setup Incomplete (Profile & Student ID) -> Setup Complete (Active Student) -> Booking Inquirer / Pending -> Onboarding Tenant (Document Upload, Contract Sign, Deposit) -> Active Tenant -> Move-Out / Leave Request -> Former Tenant.
3. **`landlord`:**
   - Unverified Email -> Setup Incomplete (Contact & House Name) -> Profile Complete -> Documents Pending (Business Permit, Mayor's Permit, Fire Safety) -> Verified / Compliant Landlord.
4. **`admin`:** System administrator with complete verification authority, boarding monitoring, incident moderation, and operational analytics.

---

## 3. What Is Already Strong

1. **AI Assistant Security:** Strict role-gated tool execution in `AiToolRegistry` and `AiToolExecutor`, output sanitization, rate limiting, and prompt guardrails.
2. **Strict SQL Compatibility:** All aggregate queries, daily metrics, and group-bys strictly adhere to MySQL `ONLY_FULL_GROUP_BY` standards without disabling strict mode.
3. **Storage Abstraction:** Resilient multi-tier fallback (Cloudflare R2 -> Supabase S3 -> Local disk) with zero credential leakage in URLs.
4. **Institutional Monitoring:** Rich administrative analytics and Mindoro State University boarding monitoring by college/program.
5. **Server-Side Financial Derivation:** Student monthly rent submissions calculate dues on the server from approved booking records, preventing client-side amount spoofing.

---

## 4. Key Findings by Severity

### Critical Findings
- **AUTH-SEC-001 / HOSTINGER-SEC-001:** `public/hostinger-setup.php` contains a fallback secret `'obhs2026'` that remains valid even when `SETUP_SECRET` is defined in `.env`.
  - *Impact:* Potential unauthorized execution of database migrations, cache flushes, or storage linking on production.
  - *Resolution:* Exclude from standard deployment packages or mandate immediate deletion after initial setup via the built-in self-delete action.

### High Findings
- **AUTH-SEC-002 / FILE-SEC-001:** `FileController@authorizeLegacyPath` executes un-grouped `TenantOnboarding::where(...)->orWhere(...)->first()` checks which could mis-evaluate ownership for legacy file paths.
  - *Resolution:* Refactor legacy queries to encapsulate `OR` conditions and chain explicit student/landlord relation scopes before calling `->exists()`.
- **AUTH-SEC-003:** `config('auth.public_admin_registration')` conditionally exposes public `/register/admin`.
  - *Impact:* Configuration risk if accidentally enabled in production `.env`.
  - *Resolution:* Add deployment check ensuring this remains `false` in production.

### Medium Findings
- **AUTH-SEC-004:** Inconsistent use of formal Laravel Policies in certain controller actions (e.g. `PaymentController` relies purely on query-level scoping).
- **TECH-DEBT-001:** `AuthController.php` is 127 KB (~1,200 lines), containing authentication, landlord approval, student verification, and admin metrics.

---

## 5. Domain-Specific Audit Results

| Subsystem | Audit Status | Key Invariant / Protection |
| :--- | :--- | :--- |
| **Authentication** | Passed with warnings | Strict role middleware; email verification gating; onboarding role redirects. |
| **Authorization** | Passed | IDOR prevention on properties, rooms, bookings, onboardings, and payments. |
| **Database / SQL** | Passed | `ONLY_FULL_GROUP_BY` compliant across all analytics and monitoring queries. |
| **File Storage** | Passed | Private file streaming via `/files/{path}`; role and ownership verification. |
| **Payments** | Passed | Server-side amount computation; duplicate payment blocking; 2-decimal scale. |
| **AI Assistant** | Passed | Server-side role gating on all tools; no client/LLM authorization trust. |
| **Deployment** | Passed with warnings | Automated packaging via `package-hostinger.ps1`; .htaccess layered defense. |

---

## 6. Recommended Next Actions

1. **Deploy Development Harness:** Implement `.agents/` directory, domain agent skills, and operational protocols.
2. **Execute Regression Test Suites:** Add dedicated feature test suites for role isolation, booking ownership, payment tracking, and file security.
3. **Lock Down Production Setup Helper:** Ensure `public/hostinger-setup.php` is deleted or strictly secured on live Hostinger instances.
4. **Prepare Marketplace Modules:** Establish canonical feature registry and planned Airbnb-style extension boundaries.
