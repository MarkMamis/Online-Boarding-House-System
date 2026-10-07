# Hostinger Production & Shared Hosting Audit

This document reviews the production deployment configuration, packaging scripts, web server rules, shared-hosting constraints, and runtime protections for Hostinger Shared Hosting.

---

## 1. Hosting Architecture & Constraints

- **Platform:** Hostinger Shared Web Hosting (cPanel / hPanel).
- **Web Server:** Apache with `mod_rewrite` enabled.
- **PHP Version:** PHP 8.2+ with standard extensions (`pdo_mysql`, `mbstring`, `openssl`, `fileinfo`, `gd`/`imagick`, `curl`).
- **Database:** MySQL 8.0+ running on localhost.
- **Constraints:**
  1. No SSH root access; no `systemd` or supervisor daemon for long-running processes.
  2. No Redis / Memcached daemon (uses database/file cache and session storage).
  3. No background daemon queue workers (runs via `sync` driver or scheduled cron-based batches).
  4. Web document root typically points to `public_html/` or has application files at root with `public/` subfolder.

---

## 2. Web Server Routing & `.htaccess` Protection

### Root `.htaccess` Rules
[`./.htaccess`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/.htaccess) implements layered defensive rules:
1. **Directory Browsing Disabled:** `Options -Indexes`
2. **Hidden Files Blocked:** RewriteRule blocking `(^|/)\.` (e.g. `.env`, `.git`)
3. **Sensitive Application Directories Blocked:**
   `RewriteRule ^(app|bootstrap|config|database|resources|routes|storage|tests|vendor|node_modules)(/.*)?$ - [F,L]`
4. **Sensitive Root Files Blocked:**
   `RewriteRule ^(artisan|composer\.(json|lock)|package(-lock)?\.json|phpunit\.xml|vite\.config\.(js|ts)|Dockerfile|\.env.*)$ - [F,L]`
5. **Direct Pass-through & Front Controller:**
   Serves existing assets directly from `public/$1` or routes to `public/index.php`.

### `public/.htaccess` Rules
[`public/.htaccess`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/public/.htaccess) handles standard Laravel front controller forwarding, `Authorization` and `X-XSRF-Token` headers.

---

## 3. Production Deployment Tooling Audit

### A. Packaging Script: `tools/package-hostinger.ps1`
- **Function:** Automates production build without contaminating local development files.
- **Strengths:**
  - Runs `npm run build` locally to produce `public/build/manifest.json`.
  - Runs clean `composer install --no-dev --prefer-dist --optimize-autoloader` in staging.
  - Generates full required directory tree for `storage/framework/{cache,sessions,testing,views}` and `storage/logs`.
  - Explicitly strips `.env`, `.git`, `.github`, `.vscode`, `node_modules`.
  - Outputs `dist/obhs-hostinger.zip`.

### B. Deployment Checker: `php artisan deploy:check`
- **Location:** [`app/Console/Commands/DeploymentCheck.php`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/app/Console/Commands/DeploymentCheck.php)
- **Checks Performed:**
  - PHP version and extension requirements
  - Database connectivity and pending migrations
  - Storage symlink and folder permissions (`storage/`, `bootstrap/cache/`)
  - Cloudflare R2 / Supabase S3 storage driver configuration
  - `APP_KEY` generation and `APP_DEBUG` production status

---

## 4. Key Security & Operational Findings

### HOSTINGER-SEC-001: `public/hostinger-setup.php` Packaging & Default Secret
- **Risk:** High.
- **Details:** `public/hostinger-setup.php` contains a browser-based migration runner with fallback password `obhs2026`.
- **Recommendation:**
  1. Never leave `hostinger-setup.php` in production after initial database setup.
  2. Use the built-in "Delete hostinger-setup.php" button or remove via File Manager immediately after setup.
  3. Remove the hardcoded `'obhs2026'` fallback check from line 30 so only a strictly configured `.env` key is accepted if the file is run.

### HOSTINGER-OPS-002: Cron Job Configuration for Shared Hosting
- **Requirement:** Hostinger hPanel -> Advanced -> Cron Jobs:
  - Command: `cd /home/uXXXXXXX/public_html && php artisan schedule:run >> /dev/null 2>&1`
  - Frequency: Once per minute (`* * * * *`)
  - Runs `payments:notify-overdue` daily at 08:00.

### HOSTINGER-OPS-003: Dual Environment Parity
- **Rule:** Never introduce Docker-only or Redis-only features that break Hostinger shared hosting, and never hardcode Hostinger absolute Linux paths (`/home/uXXXXXXX/`) into application code. Use `base_path()`, `storage_path()`, and `public_path()` dynamically.
