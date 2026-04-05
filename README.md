# OBHS Tutorial

OBHS is a Laravel 12-based boarding house management system with modules for tenant onboarding, booking and billing workflows, landlord payment management, and admin operations.

This guide covers:

1. Installation
2. Running locally
3. Database setup
4. Custom Artisan commands (including export and import)

## 1. Prerequisites

Install the following first:

1. PHP 8.2+
2. Composer 2+
3. Node.js 18+ and npm
4. MySQL (Laragon or XAMPP is fine)
5. Git

## 2. Project Setup

From the project root:

```bash
composer install
npm install
```

Create environment file:

```bash
cp .env.example .env
```

On Windows PowerShell:

```powershell
Copy-Item .env.example .env
```

Generate app key:

```bash
php artisan key:generate
```

## 3. Configure Database (.env)

Update these values in .env:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=boardinghouse_db
DB_USERNAME=root
DB_PASSWORD=
```

Important:

1. php artisan migrate does not create the database itself.
2. Create DB_DATABASE manually in MySQL first, then run migrations.

## 4. Run Migrations

```bash
php artisan migrate
```

Optional, if you have seeders you want to run:

```bash
php artisan db:seed
```

## 5. Run the Application

### Option A: Standard local run (multiple terminals)

Terminal 1:

```bash
php artisan serve
```

Terminal 2:

```bash
php artisan queue:listen --tries=1
```

Terminal 3:

```bash
npm run dev
```

### Option B: Composer helper scripts

Initial setup script:

```bash
composer run setup
```

Development script (server + queue + logs + vite):

```bash
composer run dev
```

Run tests:

```bash
composer run test
```

## 6. Custom Artisan Commands

### A. Export Database

Command:

```bash
php artisan export
```

Useful options:

1. --stack=auto|laragon|xampp
2. --schema-only
3. --gzip
4. --file=your_dump.sql
5. --path=storage/app/exports
6. --binary="full/path/to/mysqldump(.exe)"

Examples:

```bash
php artisan export --stack=laragon
php artisan export --stack=laragon --schema-only --file=schema_only.sql
php artisan export --gzip --file=full_backup.sql.gz
```

### B. Import Database

Command:

```bash
php artisan import
```

Behavior:

1. If no file is given, it imports the latest .sql / .gz from storage/app/exports.
2. By default, it asks for confirmation.

Useful options:

1. --stack=auto|laragon|xampp
2. --binary="full/path/to/mysql(.exe)"
3. --database=your_db_name
4. --force (skip confirmation)

Examples:

```bash
php artisan import --stack=laragon
php artisan import storage/app/exports/boardinghouse_db_20260405_164113.sql --stack=laragon --force
php artisan import storage/app/exports/backup.sql.gz --stack=laragon --database=boardinghouse_db --force
```

### C. Other Project Commands

1. php artisan payments:notify-overdue - sends overdue payment alerts
2. php artisan admin:reset - resets/creates admin account
3. php artisan properties:geocode --force - geocodes property coordinates

## 7. Dual SQL Stack Note (Laragon + XAMPP)

If both are installed, client mismatch can happen (example: caching_sha2_password plugin errors).

Use stack preference to avoid wrong binaries:

```bash
php artisan export --stack=laragon
php artisan import --stack=laragon --force
```

If needed, pin exact binaries:

```bash
php artisan export --binary="C:\\laragon\\bin\\mysql\\mysql-8.4.3-winx64\\bin\\mysqldump.exe"
php artisan import --binary="C:\\laragon\\bin\\mysql\\mysql-8.4.3-winx64\\bin\\mysql.exe" --force
```

## 8. Mobile-First UI Standard

For OBHS UI work, follow mobile-first implementation:

1. Design first for 320px to 420px screens.
2. Keep primary actions thumb-friendly.
3. Prefer card-based layout over wide tables on mobile.
4. Validate common breakpoints before finishing UI work:
   1. 360x800
   2. 390x844
   3. 768x1024
   4. Desktop

## 9. Troubleshooting

### mysqldump is not recognized

1. Use stack flag (--stack=laragon or --stack=xampp).
2. Or pass full --binary path.

### Import/Export fails with MySQL authentication plugin errors

1. Use a MySQL 8+ client matching your running server.
2. Prefer Laragon binaries if your DB server is from Laragon.

### Migration fails because database does not exist

1. Create the database manually first.
2. Re-run php artisan migrate.

---

For team onboarding, start at Section 2 and continue in order.
