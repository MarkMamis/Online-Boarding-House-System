# Hostinger Shared Hosting Configuration & Limits

## Recommended PHP Settings for Production (`mcc-boardinghouse.online`)

To ensure smooth processing of 5 MB images and multi-image uploads (cover photo + up to 10 detail photos) without encountering HTTP 413 or request truncation:

### 1. PHP Invariants in Hostinger hPanel

Navigate to: **hPanel** $\rightarrow$ **Advanced** $\rightarrow$ **PHP Configuration** $\rightarrow$ **PHP Options**

| Directive | Recommended Value | Minimum Allowed | Rationale |
|---|---|---|---|
| `upload_max_filesize` | **16M** | **10M** | Allows single photos up to 5 MB with overhead. |
| `post_max_size` | **64M** | **32M** | Accommodates batch upload of multiple room photos + form fields. |
| `memory_limit` | **256M** | **128M** | Provides adequate memory buffer for image streaming. |
| `max_execution_time` | **120** | **60** | Prevents timeouts on slower mobile 3G/4G cellular uploads. |
| `max_file_uploads` | **20** | **20** | Accommodates room cover + up to 10 detail photos in single request. |

---

### 2. `.htaccess` Fallback Directives (Apache / Shared Hosting)

If Hostinger's environment allows PHP flag overrides via `.htaccess`, ensure the following directives exist in public `.htaccess`:

```apache
<IfModule mod_php.c>
    php_value upload_max_filesize 16M
    php_value post_max_size 64M
    php_value memory_limit 256M
    php_value max_execution_time 120
    php_value max_file_uploads 20
</IfModule>
```

> **Note:** If Hostinger runs via PHP-FPM (standard for modern shared plans), configure via **hPanel PHP Options** or `php.ini` in the user home directory instead of `.htaccess`.
