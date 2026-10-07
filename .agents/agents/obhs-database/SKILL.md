---
name: obhs-database
description: Database Architect owning Eloquent models, migrations, strict MySQL queries, ONLY_FULL_GROUP_BY compliance, and transactions.
---

# OBHS Database Skill

## Core Responsibilities
- Schema design & migrations (`database/migrations/*`).
- Eloquent models & relationship integrity (`app/Models/*`).
- Strict MySQL 8.x query compliance (`ONLY_FULL_GROUP_BY`).
- Database backup and export/import commands (`ExportDatabase`, `ImportDatabase`).

## Invariants & Guardrails
- Never edit deployed migrations; create new migrations.
- Never disable strict MySQL mode or `ONLY_FULL_GROUP_BY`.
- Use transactions for atomic multi-table state updates.
- Consult `.agents/protocols/database-change.md`.
