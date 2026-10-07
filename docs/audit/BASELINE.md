# OBHS Baseline Snapshot

- **Date / Timestamp:** 2026-10-08 03:03:10 UTC+8
- **Git Branch:** `main`
- **HEAD Commit:** `a733a1440090c3ba9a228435a2c2b18cacac5aad`
- **Application Name:** Online Boarding House System (OBHS)
- **Primary Purpose:** Verified student boarding-house marketplace and management platform for Mindoro State University (MinSU) students around Calapan City, Oriental Mindoro.

---

## 1. Runtime & Framework Environment

| Component | Version / Specification | Notes |
| :--- | :--- | :--- |
| **PHP Version** | `8.2.12 (cli)` (Constraint: `^8.2`) | Local Windows CLI + Hostinger Shared Hosting compatible |
| **Laravel Framework** | `12.39.0` (Constraint: `^12.0`) | Laravel 12 application structure (`bootstrap/app.php`) |
| **Frontend Assets** | Vite 7.0.7, TailwindCSS 4.0.0, Axios | Built via `npm run build` to `public/build/` |
| **Database Engine** | MySQL (Production) / SQLite `:memory:` (Testing) | `ONLY_FULL_GROUP_BY` strict SQL compliance mandatory |
| **Web Server** | Apache (Hostinger Shared) / `php artisan serve` (Local) | Dual `.htaccess` routing (root + `public/`) |
| **Storage Drivers** | Cloudflare R2 / S3 (primary/fallback) + local | Multi-driver fallback via `FileStorageService` |

---

## 2. Dependencies Inventory

### Composer Dependencies (Production)
- `php`: `^8.2`
- `laravel/framework`: `^12.0`
- `barryvdh/laravel-dompdf`: `*` (Lease agreement PDF generation)
- `league/flysystem-aws-s3-v3`: `^3.0` (Cloudflare R2 & Supabase S3 storage)
- `toin0u/geocoder-laravel`: `^5.0` (Property geolocation)
- `laravel/tinker`: `^2.10.1`

### Composer Dev Dependencies
- `phpunit/phpunit`: `^11.5.3`
- `fakerphp/faker`: `^1.23`
- `mockery/mockery`: `^1.6`
- `laravel/pint`: `^1.24`
- `laravel/pail`: `^1.2.2`
- `laravel/sail`: `^1.41`
- `nunomaduro/collision`: `^8.6`

### NPM Dev Dependencies
- `vite`: `^7.0.7`
- `laravel-vite-plugin`: `^2.0.0`
- `tailwindcss`: `^4.0.0`
- `@tailwindcss/vite`: `^4.0.0`
- `axios`: `^1.11.0`
- `concurrently`: `^9.0.1`

---

## 3. Architecture & Directory Layout

```
obhs/
├── app/
│   ├── Console/Commands/        # 9 artisan commands (DB export/import, deployment check, backfills, overdue alerts)
│   ├── Http/
│   │   ├── Controllers/         # 29 controllers covering Auth, Student, Landlord, Admin, AI, Payments, Files
│   │   └── Middleware/          # RoleMiddleware, EnsureRoleSelected, EnsureStudentSetupComplete, EnsureLandlordSetupStep
│   ├── Models/                  # 15 Eloquent models (User, Property, Room, Booking, TenantOnboarding, TenantPayment, etc.)
│   ├── Notifications/           # SystemNotification (database + email channels)
│   ├── Policies/                # 6 authorization policies (Booking, Message, Property, Report, Room, TenantOnboarding)
│   ├── Providers/               # AppServiceProvider
│   ├── Services/                # Domain services: AI/, Analytics, BoardingMonitoring, FileStorage, DocumentExpiration, etc.
│   └── Support/                 # helpers.php
├── bootstrap/
│   ├── app.php                  # Laravel 12 application configure & middleware aliases
│   └── providers.php
├── config/                      # app, auth, database, filesystems, mail, logging, session, etc.
├── database/
│   ├── factories/               # Model factories
│   ├── migrations/              # 73 database migrations (initial -> documents v2)
│   └── seeders/                 # Database seeders
├── docs/                        # Architecture, audit, and deployment documentation
├── public/                      # Front controller (index.php), .htaccess, build assets, hostinger-setup.php
├── resources/
│   ├── css/                     # app.css
│   ├── js/                      # app.js, bootstrap.js
│   └── views/                   # Blade views (admin, auth, errors, landlord, student, layouts, landing, emails)
├── routes/
│   ├── web.php                  # 171 registered application routes
│   └── console.php              # Scheduled tasks & artisan commands
├── tests/
│   ├── Feature/                 # 16 feature test suites
│   ├── Unit/                    # 3 unit test suites
│   └── TestCase.php
├── tools/
│   └── package-hostinger.ps1    # Automated production packaging script
├── .htaccess                    # Root Apache rewrite & file protection rules
└── .env.hostinger.example       # Production environment template
```

---

## 4. Role & State Inventory

### Authenticated Roles (Stored in `users.role`)
1. **`pending`**: Registered user awaiting initial role selection (student vs landlord).
2. **`student`**: Student seeking housing or active boarding tenant.
   - States: `registered` -> `email unverified` -> `email verified` -> `student setup incomplete` -> `student setup complete (active student)` -> `prospective tenant` -> `booking pending` -> `booking approved` -> `onboarding` -> `active tenant` -> `leaving / move-out` -> `former tenant`.
3. **`landlord`**: Property owner/manager listing rooms and managing tenants.
   - States: `registered` -> `email unverified` -> `setup incomplete` -> `profile complete` -> `documents pending (permit/safety)` -> `verified/compliant` -> `document expired/rejected`.
4. **`admin`**: System administrator with oversight, verification authority, and institutional reporting.

---

## 5. Deployment Constraints (Hostinger Shared Hosting)

- **Environment:** Shared cPanel/hPanel Linux environment with Apache and MySQL 8.x.
- **Root / Daemon Access:** No root access, no `systemd` supervisor, no Docker runtime, no Redis.
- **Deployment Flow:** Packaging via `tools/package-hostinger.ps1` -> Upload `dist/obhs-hostinger.zip` -> Extract in `public_html/` -> Execute migrations and link storage.
- **Queue & Scheduler:** Queue runs via `sync` driver (or shared cron triggering `php artisan queue:work --stop-when-empty`); scheduler runs via Hostinger Cron Job invoking `php artisan schedule:run`.
- **Dual Environment Requirement:** The entire codebase must maintain 100% parity between local Windows development (`php artisan serve` + Vite dev server) and Hostinger production (`public/index.php` + compiled Vite bundle).

---

## 6. Initial Test Suite Baseline

- **Total Test Files:** 19 (16 Feature + 3 Unit)
- **Total Tests:** 162 passed (870 assertions)
- **Initial Duration:** ~45.5 seconds on local SQLite in-memory runner
