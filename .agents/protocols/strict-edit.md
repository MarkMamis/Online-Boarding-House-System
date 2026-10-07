# Protocol: Strict Edit Rules

All future code modifications in OBHS must adhere strictly to these 15 rules.

---

### Rule 1: No Random Edits
Identify the Feature ID, role, owner agent, affected files, business invariants, and regression tests before modifying any file.

### Rule 2: Minimum Change Surface
Make the smallest justified change. Do not refactor unrelated classes, reformat untouched files, or rename working methods.

### Rule 3: Route Changes Require Auth Review
Any new or modified route must specify authentication requirements, role middleware, setup prerequisites, ownership checks, and rate limits.

### Rule 4: No Blade-Only Authorization
Hiding UI elements or buttons does not secure an endpoint. All permissions must be enforced server-side in controllers and policies.

### Rule 5: No Client-Trusted Ownership
Never accept `student_id`, `landlord_id`, `owner_id`, or `user_id` from user input when it should be derived from `auth()->id()`.

### Rule 6: Policies for Resource Ownership
Use or extend Laravel Policies (`app/Policies/*`) for all resource operations and ownership checks.

### Rule 7: Database Safety & Strict SQL
Never disable `ONLY_FULL_GROUP_BY` or strict MySQL mode. Never edit production tables manually as the primary solution. Never silently drop columns or truncate data. Always create new migrations for schema changes.

### Rule 8: Transactions for Atomic Workflows
Use `DB::transaction()` for multi-step workflows where partial execution would corrupt state (e.g. booking approval + room capacity decrements, onboarding state transitions).

### Rule 9: Financial Integrity
Preserve exact 2-decimal scale (`decimal(10,2)`). Never use floating-point equality. Never trust client-submitted monetary totals if the server can calculate them.

### Rule 10: Private File Security
Never create public direct URLs for sensitive user documents (IDs, permits, contracts). Route all private access through authenticated streaming endpoints (`/files/{path}`).

### Rule 11: Production Credentials Protection
Never commit `.env`, production database passwords, SMTP credentials, Cloudflare R2 secrets, or API tokens.

### Rule 12: No `APP_DEBUG=true` in Production
Never solve production bugs by permanently enabling debug mode.

### Rule 13: Hostinger Shared Hosting Compatibility
Do not introduce tools requiring Redis, `systemd` daemons, long-running background workers, or root access without an approved shared-hosting fallback.

### Rule 14: Local Development Parity
Hardening production must never break local development on standard developer machines.

### Rule 15: Test Before Claiming Fixed
Do not mark a task complete or claim a bug is fixed without actual test execution results.
