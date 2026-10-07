# Protocol: Migration Deployment & Execution

This protocol guides how migrations are developed, tested, and executed in production.

---

## 1. Migration Development Lifecycle

1. **Generate Migration:**
   ```bash
   php artisan make:migration add_new_fields_to_table --table=table_name
   ```
2. **Implement `up()` and `down()`:** Ensure the rollback method cleans up created columns or indexes cleanly.
3. **Local Testing:** Test execution using `php artisan test` and SQLite in-memory test runner.
4. **Hostinger Execution:** Production migrations run via Hostinger SSH/terminal (`php artisan migrate --force`) or via secured setup wizard.
