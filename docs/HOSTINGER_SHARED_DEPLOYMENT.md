# OBHS Hostinger Shared-Hosting Deployment & Maintenance Guide

This document is the authoritative operational guide for deploying and maintaining the **Online Boarding House System (OBHS)** on **Hostinger Shared Web Hosting** (Single, Premium, or Business Web Hosting) without requiring a Virtual Private Server (VPS).

Target Production Domain: **`https://mcc-boardinghouse.online`**

---

## 1. Hybrid Architecture Overview

The OBHS codebase is structured as a **single hybrid codebase** that runs in two distinct environments through configuration rather than separate code branches:

```
ONE CODEBASE
├── Local Development
│   ├── Operating System: Windows
│   ├── Dev Server: php artisan serve (http://127.0.0.1:8000)
│   ├── Environment: .env (APP_ENV=local, APP_DEBUG=true)
│   ├── Database: Local MySQL (root@127.0.0.1:3306)
│   ├── Assets: npm run dev or local npm run build
│   └── Dev Tools: PHPUnit, Tinker, Pail, Faker active
│
└── Hostinger Shared Production
    ├── Server Type: Hostinger Shared Web Hosting (Apache / LiteSpeed)
    ├── NO VPS Required (No root, no Supervisor, no systemd, no Redis daemon)
    ├── Document Root: public_html (containing the full Laravel tree)
    ├── Routing: Root .htaccess safely routes dynamic requests to public/index.php
    ├── Security: Root .htaccess blocks direct HTTP downloads of .env, artisan, storage, etc.
    ├── Environment: .env (APP_ENV=production, APP_DEBUG=false, APP_URL=https://mcc-boardinghouse.online)
    ├── Database: Hostinger Cloud MySQL (localhost:3306) with strict SQL compatibility
    ├── Storage: Local public symlink (or optional Cloudflare R2 / Supabase S3)
    ├── Queues: QUEUE_CONNECTION=sync (synchronous execution without background daemons)
    └── Scheduler: Hostinger hPanel Cron Job calling 'php artisan schedule:run'
```

---

## 2. Server Requirements & Hostinger PHP Configuration

Before deploying, ensure your domain is configured in Hostinger hPanel:

1. **PHP Version**: In Hostinger hPanel ➡️ **Advanced** ➡️ **PHP Configuration**, select **PHP 8.2** or **PHP 8.3**.
2. **Required PHP Extensions** (Default on Hostinger, ensure they are checked in PHP Extensions tab):
   - `pdo_mysql` (MySQL database connection)
   - `mbstring` (Multibyte string processing)
   - `openssl` (Encryption and HTTPS)
   - `tokenizer` (PHP parsing)
   - `xml` (XML & DOM parsing)
   - `ctype` (Character typing)
   - `json` (JSON parsing)
   - `bcmath` (Precision mathematics)
   - `fileinfo` (MIME-type detection for uploads)
   - `curl` (External HTTP requests)
   - `gd` (Image handling and QR codes)
3. **Directory Permissions**:
   - `storage/` ➡️ `755` or `775` (Writable)
   - `bootstrap/cache/` ➡️ `755` or `775` (Writable)

---

## 3. First Deployment Procedure (Step-by-Step)

Follow this procedure when launching the project for the very first time on Hostinger.

### Step 1: Run Local Preflight Checks
In your local terminal inside the project directory, run:
```powershell
php artisan obhs:deployment-check
```
Ensure there are no fatal errors.

### Step 2: Build the Production Deployment Package
Run the automated packaging utility:
```powershell
powershell -ExecutionPolicy Bypass -File tools/package-hostinger.ps1
```
This script will:
- Run `npm run build` to compile production CSS & JavaScript into `public/build/`.
- Create a clean staging directory `dist/hostinger/`.
- Run `composer install --no-dev --prefer-dist --optimize-autoloader` to bundle production PHP dependencies without touching your local dev environment.
- Exclude `.env`, `.git`, `node_modules`, test caches, and Windows symlinks.
- Produce **`dist/obhs-hostinger.zip`**.

### Step 3: Create MySQL Database in Hostinger
1. Log in to **Hostinger hPanel** (`hpanel.hostinger.com`).
2. Navigate to **Databases** ➡️ **MySQL Databases**.
3. Create a new database:
   - **Database Name**: e.g., `obhs_db` (Hostinger will format as `u123456789_obhs_db`)
   - **Username**: e.g., `obhs_admin` (Hostinger will format as `u123456789_obhs_admin`)
   - **Password**: Generate a strong password.
4. Note down the exact Database Name, Username, and Password. (Database host is `localhost`).

### Step 4: Upload and Extract Files
1. In Hostinger hPanel, open **File Manager** for `mcc-boardinghouse.online`.
2. Open the **`public_html`** directory.
3. Upload `dist/obhs-hostinger.zip`.
4. Right-click `obhs-hostinger.zip` and select **Extract**:
   - Extract directly into `public_html` so that files like `app/`, `bootstrap/`, `public/`, `vendor/`, and `.htaccess` sit in `public_html`.
5. Delete `obhs-hostinger.zip` from File Manager once extracted.

### Step 5: Configure Production `.env`
1. Inside `public_html`, find **`.env.hostinger.example`**.
2. Rename it to **`.env`** (or create `.env` if none exists).
3. Open `.env` in the File Manager editor and update:
   ```env
   APP_NAME="OBHS"
   APP_ENV=production
   APP_KEY=base64:... (paste your generated key or generate via artisan)
   APP_DEBUG=false
   APP_URL=https://mcc-boardinghouse.online

   DB_CONNECTION=mysql
   DB_HOST=localhost
   DB_PORT=3306
   DB_DATABASE=u123456789_obhs_db
   DB_USERNAME=u123456789_obhs_admin
   DB_PASSWORD=YOUR_DB_PASSWORD

   SESSION_DRIVER=database
   SESSION_SECURE_COOKIE=true
   CACHE_STORE=file
   QUEUE_CONNECTION=sync
   FILESYSTEM_DISK=public
   ```
4. Save and close `.env`.

### Step 6: Initialize Database and Storage
#### Option A: Via Hostinger SSH Terminal (Recommended if available)
In Hostinger hPanel ➡️ **Advanced** ➡️ **SSH Access**, enable SSH and connect:
```bash
cd public_html
php artisan key:generate --force
php artisan migrate --force
php artisan db:seed --force      # Optional: seeds initial admin and catalog
php artisan storage:link
php artisan optimize
```

#### Option B: Via Browser Setup Helper (`public/hostinger-setup.php`)
If SSH is not available on your Hostinger plan:
1. In `.env`, ensure you have set: `SETUP_SECRET=YourSecretKey2026`.
2. Open your browser: `https://mcc-boardinghouse.online/hostinger-setup.php?key=YourSecretKey2026`.
3. Click:
   - **Link Storage (Symlink)**: Creates Linux symlink `public/storage` ➡️ `../storage/app/public`.
   - **Run Migrations**: Runs `php artisan migrate --force`.
   - **Cache Config & Routes**: Runs `php artisan optimize`.
4. Click **Delete hostinger-setup.php Now** to permanently remove the helper script after setup.

---

## 4. Application Update Procedure (Subsequent Deployments)

When updating the application with new features or bug fixes, **NEVER** overwrite your production `.env` or delete user uploads!

```
REPEATABLE UPDATE WORKFLOW:
1. Local git commits & testing passed.
2. Run tools/package-hostinger.ps1 locally -> creates new dist/obhs-hostinger.zip.
3. Upload and extract zip into public_html in File Manager.
   (The zip does NOT contain .env, so production secrets are preserved!)
4. In Hostinger SSH or File Manager:
   php artisan optimize:clear
   php artisan migrate --force    (Only applies new migrations safely)
   php artisan optimize           (Rebuilds production config/route caches)
5. Verify site at https://mcc-boardinghouse.online.
```

---

## 5. Hostinger Scheduled Tasks (Cron Job)

OBHS schedules daily automated overdue payment notifications (`payments:notify-overdue` at 08:00 AM).

To activate the scheduler in Hostinger Shared Hosting:
1. Go to Hostinger hPanel ➡️ **Advanced** ➡️ **Cron Jobs**.
2. Select **Custom**:
   - Minute: `*`
   - Hour: `*`
   - Day: `*`
   - Month: `*`
   - Weekday: `*`
   *(Runs every minute: `* * * * *`)*
3. Set the Command:
   ```bash
   /usr/bin/php /home/u123456789/domains/mcc-boardinghouse.online/public_html/artisan schedule:run >> /dev/null 2>&1
   ```
   *(Replace `/home/u123456789/domains/...` with your actual hosting home path visible in File Manager)*
4. Click **Save**.

---

## 6. HTTP Route Smoke-Test Checklist

After deployment, verify that the application returns the expected HTTP status codes:

| Route | Method | Expected Status | Description |
| :--- | :--- | :--- | :--- |
| `/` | GET | `200 OK` | Public Homepage & Property Discovery |
| `/login` | GET | `200 OK` | Authentication Login Page |
| `/register` | GET | `200 OK` | Registration Page |
| `/build/manifest.json` | GET | `200 OK` | Frontend Asset Manifest |
| `/.env` | GET | `403 Forbidden` | Blocked by root `.htaccess` (Security Test) |
| `/artisan` | GET | `403 Forbidden` | Blocked by root `.htaccess` (Security Test) |
| `/up` | GET | `200 OK` | Laravel Application Health Check |
| `/admin/dashboard` | GET | `302 Found` ➡️ `/login` | Protected route redirects unauthenticated users |
| `/landlord/dashboard` | GET | `302 Found` ➡️ `/login` | Protected landlord dashboard |
| `/student/dashboard` | GET | `302 Found` ➡️ `/login` | Protected student dashboard |

---

## 7. Shared-Hosting Architecture Limitations

The basic OBHS system is fully operational on Hostinger Shared Hosting. However, the following features would require a VPS or external managed cloud services if needed in the future:

1. **Persistent WebSocket Servers (Laravel Reverb / Pusher)**:
   - Shared hosting kills long-running processes. For real-time messaging, use an external cloud provider like Pusher or Pusher-compatible HTTP webhooks, or rely on AJAX polling.
2. **Background Queue Daemons (Supervisor / queue:work --daemon)**:
   - Shared hosting does not allow running daemons. `QUEUE_CONNECTION=sync` executes jobs in the HTTP request. If deferred processing is desired, a cron job running `php artisan queue:work --stop-when-empty` every 5 minutes is the shared-hosting alternative.
3. **Redis / Memcached In-Memory Caching**:
   - Single/shared plans do not offer Redis server instances. OBHS uses `CACHE_STORE=file` or `CACHE_STORE=database`, which are fast and reliable for shared hosting workloads.
4. **Root Shell & Custom Nginx Configuration**:
   - Hostinger shared hosting uses Apache / LiteSpeed with `.htaccess`. All rewrite rules and headers are handled via root and public `.htaccess` files.
