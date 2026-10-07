# Protocol: Production Incident Response

This protocol outlines the procedure for resolving production issues on Hostinger Shared Hosting.

---

## 1. Incident Handling Steps

1. **Do NOT Enable `APP_DEBUG=true` in Production:** Inspect error logs via Hostinger File Manager in `storage/logs/laravel.log`.
2. **Reproduce Locally:** Create a failing regression test in `tests/Feature/` reproducing the exact failure scenario.
3. **Smallest Safe Fix:** Apply the minimal justified fix.
4. **Verify Locally:** Confirm the regression test and broader suite pass (`php artisan test`).
5. **Deploy Fix:** Package via `tools/package-hostinger.ps1` and upload to Hostinger.
6. **Post-Mortem & Handoff:** Record root cause and remediation in `.agents/handoffs/`.
