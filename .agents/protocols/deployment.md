# Protocol: Deployment & Hostinger Packaging

This protocol defines the release and deployment procedure for Hostinger Shared Hosting.

---

## 1. Release Packaging Procedure

1. **Clean Working Tree:** Ensure Git status is clean and all tests pass (`php artisan test`).
2. **Execute Packaging Script:**
   ```powershell
   .\tools\package-hostinger.ps1
   ```
3. **Verify Archive:** Confirm `dist/obhs-hostinger.zip` is generated without `.env` or `node_modules`.
4. **Deploy to Hostinger:** Upload and extract in `public_html/`.
5. **Run Post-Deployment Commands:**
   ```bash
   php artisan migrate --force
   php artisan storage:link
   php artisan optimize:clear
   php artisan optimize
   php artisan deploy:check
   ```
