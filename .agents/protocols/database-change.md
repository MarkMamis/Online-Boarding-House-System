# Protocol: Database Schema Changes & Queries

This protocol governs database changes, indexing, strict SQL compliance, and query performance.

---

## 1. Schema Change Rules

1. **New Migrations Only:** Never edit previously committed migrations that have been deployed to production. Create a new timestamped migration via `php artisan make:migration`.
2. **Nullable / Defaults for Backward Compatibility:** When adding columns to active tables (`users`, `properties`, `rooms`, `bookings`), ensure columns are `nullable()` or specify a sensible `default()`.
3. **Foreign Key Integrity:** Define `constrained()->cascadeOnDelete()` or `nullOnDelete()` explicitly.
4. **Strict MySQL Compatibility:** All queries must function without errors under `ONLY_FULL_GROUP_BY` and `STRICT_TRANS_TABLES`.
5. **No Destructive Commands:** NEVER run `php artisan migrate:fresh`, `db:wipe`, or drop tables in production or without explicit instructions.
