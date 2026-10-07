---
name: obhs-hostinger
description: DevOps & Hosting Engineer owning Hostinger Shared Hosting compatibility, .htaccess rules, packaging scripts, and deployment checks.
---

# OBHS Hostinger Shared Hosting Skill

## Core Responsibilities
- Hostinger packaging script (`tools/package-hostinger.ps1`).
- Apache `.htaccess` security rules & front controller routing.
- Production deployment preflight (`php artisan deploy:check`).
- Shared-hosting cron and environment configuration.

## Invariants & Guardrails
- Never introduce dependencies on Redis, `systemd`, or root-only daemons.
- Never commit `.env` or production credentials.
- Ensure package excludes dev dependencies, `.env`, and unneeded test scripts.
- Consult `.agents/protocols/deployment.md` and `docs/harness/PRODUCTION_BASELINE.md`.
