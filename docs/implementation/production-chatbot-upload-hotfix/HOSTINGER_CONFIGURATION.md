# Hostinger Shared Hosting Configuration & Environment Alignment

## 1. Required PHP Directive Limits
On Hostinger Shared Hosting, multi-photo mobile uploads require appropriate PHP limits. Recommended starting values in Hostinger **hPanel -> Advanced -> PHP Configuration**:

| Directive | Minimum Recommended | Default Typical | Purpose |
|-----------|---------------------|-----------------|---------|
| `upload_max_filesize` | **10M** | 2M / 10M | Maximum allowed size for a single uploaded file. |
| `post_max_size` | **32M** | 8M / 16M | Maximum total POST request size (must exceed `upload_max_filesize * max_file_uploads`). |
| `memory_limit` | **256M** | 128M / 256M | PHP memory for GD/Imagick image resizing operations. |
| `max_file_uploads` | **20** | 20 | Maximum number of files uploaded simultaneously. |
| `max_execution_time` | **60s** | 30s | Allows sufficient execution time on cellular connections. |
| `fileinfo` | **Enabled** | Enabled | Required for Laravel MIME type inspection. |

---

## 2. Secure Verification in Hostinger hPanel

> [!CAUTION]
> Never upload a public `phpinfo.php` file to production. It exposes server paths, PHP build details, and environment variables.

### How to Verify Settings via hPanel:
1. Log into [Hostinger hPanel](https://hpanel.hostinger.com).
2. Navigate to **Websites** -> select `mcc-boardinghouse.online` -> **Manage**.
3. Under the **Advanced** section in the left sidebar, click **PHP Configuration**.
4. Select the **PHP Options** tab.
5. Verify and adjust:
   - `upload_max_filesize`: set to `10M` (or `16M`).
   - `post_max_size`: set to `32M`.
   - `memory_limit`: set to `256M` or `512M`.
6. Click **Save** at the bottom of the page.

---

## 3. Web Server & `.htaccess` Rules
In Hostinger Apache environments, `.htaccess` in the document root (`public/`) can enforce runtime values if allowed by the PHP handler:
```apache
<IfModule mod_php.c>
    php_value upload_max_filesize 10M
    php_value post_max_size 32M
    php_value memory_limit 256M
</IfModule>
```
*Note: On modern Hostinger PHP-FPM setups, `.user.ini` located in the application root or public directory is the preferred mechanism:*
```ini
upload_max_filesize = 10M
post_max_size = 32M
memory_limit = 256M
```
