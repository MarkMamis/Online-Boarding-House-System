# Feature Domain: Deployment & Infrastructure (`DEP`)

## Purpose
Governs Hostinger Shared Hosting packaging, `.htaccess` security rules, production environment configuration, and deployment preflight validation.

## Roles
- `Ops` / `Developer`

## Tooling & Scripts
- Packaging Script: `tools/package-hostinger.ps1`
- Preflight Command: `php artisan deploy:check`
- Root Server Rules: `.htaccess`, `public/.htaccess`
- Template: `.env.hostinger.example`

## Invariants & Security
- Never commit `.env` or production credentials.
- Packaging scripts must exclude `.env`, `.git`, and dev artifacts.
- Preserve 100% parity between local development and Hostinger production.
