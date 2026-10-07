# Hostinger Production Baseline & Environment Contract

This document defines the production runtime contract, server configuration, deployment procedures, and environment constraints for Hostinger Shared Web Hosting.

---

## 1. Hosting Architecture Specification

- **Hosting Provider:** Hostinger Shared Hosting (Linux / Apache).
- **Control Panel:** Hostinger hPanel.
- **PHP Version:** PHP 8.2+ (CLI and Web Engine).
- **Database Engine:** MySQL 8.0+ running on `127.0.0.1:3306`.
- **Web Server:** Apache 2.4+ with `mod_rewrite` enabled.
- **Web Root:** `public_html/` on Hostinger shared storage.

---

## 2. Infrastructure Fallbacks & Shared-Hosting Invariants

Because the production application runs on shared hosting without root/daemon access, the following infrastructural constraints are permanently binding:

1. **Queue Driver:** `sync` (default) or database queue processed via scheduled cron (`php artisan queue:work --stop-when-empty`). Do NOT introduce daemon-only supervisor dependencies.
2. **Session Driver:** `file` or `database`. (No Redis / Memcached daemon).
3. **Cache Store:** `file` or `database`.
4. **Task Scheduler:** Hostinger Cron Job invoking `php artisan schedule:run` every minute (`* * * * *`).
5. **Path Resolution:** Always use Laravel path helpers (`base_path()`, `app_path()`, `storage_path()`, `public_path()`). NEVER hardcode absolute local Windows paths (`C:\Users\...`) or absolute Linux paths (`/home/uXXXXXXX/...`).

---

## 3. Deployment Packaging Workflow

Production updates must follow the automated packaging workflow:

```powershell
# In PowerShell at project root:
.\tools\package-hostinger.ps1
```

### Packager Output & Deployment Steps:
1. Generates `dist/obhs-hostinger.zip` with clean production assets.
2. Upload `obhs-hostinger.zip` to Hostinger `public_html/` via File Manager.
3. Extract archive into `public_html/`.
4. On First Deployment: Copy `.env.hostinger.example` to `.env` and fill database credentials.
5. On Subsequent Updates: Never overwrite the existing live `.env`.
6. Run database migrations and link storage:
   ```bash
   php artisan migrate --force
   php artisan storage:link
   php artisan optimize:clear
   php artisan optimize
   ```

---

## 4. Production Preflight & Verification

Before and after deployment, execute the preflight check:
```bash
php artisan deploy:check
```

The checker validates:
- PHP version compatibility (8.2+)
- Active database connection & pending migration detection
- Writable permissions on `storage/` and `bootstrap/cache/`
- Cloudflare R2 / S3 storage connectivity
- Production security flags (`APP_DEBUG=false`, `APP_ENV=production`)
