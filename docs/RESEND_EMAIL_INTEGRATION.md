# Resend Transactional Email Integration — Technical Specification & Operational Guide

**System:** Online Boarding House System (OBHS) — Mindoro State University (MinSU)  
**Host Environment:** Hostinger Shared Web Hosting (Apache, PHP 8.2+, MySQL 8.x)  
**Provider:** Resend (`resend/resend-php`)  
**Laravel Version:** Laravel 12 Native Transport  

---

## 1. Overview of the Integration

The Online Boarding House System (OBHS) integrates **Resend** as its production transactional email service provider. This integration connects directly with Laravel 12's native Resend mail transport (`Illuminate\Mail\Transport\ResendTransport`), using the official `resend/resend-php` SDK.

Transactional emails are dispatched automatically alongside existing in-app database notifications during critical lifecycle state transitions (such as booking requests, approvals, payment confirmations, compliance evaluations, and account security notices).

### Separation of Concerns:
- **In-App Database Notifications:** Preserved completely in the `notifications` table; powering the UI badge count, notifications dropdown, and `/notifications` management interface.
- **Transactional Emails:** Delivered synchronously through Resend directly to the recipient's verified email address using mobile-responsive, MinSU-branded HTML and plain-text templates.
- **Internal Direct Chat:** Completely isolated in `messages` table via HTTP POST/polling. Chat messages do not trigger email spam.

---

## 2. Reused Existing Components

To avoid system disruption and maintain architectural integrity, existing OBHS patterns were reused:
1. **Laravel 12 Framework Transport:** Utilizes native `createResendTransport()` in `Illuminate\Mail\MailManager`.
2. **`App\Notifications\SystemNotification`:** Enhanced with dual-channel capabilities (`database` and `mail`) instead of introducing separate mailable layers or duplicate dispatch systems.
3. **Database Schema:** Zero database schema modifications or migrations required. The `notifications` table structure remains 100% unchanged.
4. **Authentication Protocols:** Reused Laravel's built-in `MustVerifyEmail` trait and `PasswordResetController` / `CanResetPassword` notifications.
5. **Existing Routes & URL Helpers:** Action buttons in emails use signed or authorized project named routes (`student.onboarding.index`, `landlord.bookings.index`, etc.).
6. **Error Resiliency:** All notification and mail dispatches are guarded with `try / catch` blocks to ensure an external network or email API timeout never corrupts or rolls back business database transactions.

---

## 3. Files Created and Modified

### Dependencies
- [`composer.json`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/composer.json) & `composer.lock`: Added `resend/resend-php: ^1.17`.

### Configuration & Environment
- [`.env.example`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/.env.example): Added Resend environment template (`MAIL_MAILER=resend`, `RESEND_API_KEY=`, `MAIL_FROM_ADDRESS=`).
- [`config/mail.php`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/config/mail.php): Maintained native `'resend' => ['transport' => 'resend']`.
- [`config/services.php`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/config/services.php): Maintained `'resend' => ['key' => env('RESEND_API_KEY')]`.

### Core Application
- [`app/Notifications/SystemNotification.php`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/app/Notifications/SystemNotification.php): Added transactional email channel resolution, branded mail rendering, metadata handling, and action buttons.
- [`app/Console/Commands/SendTestEmail.php`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/app/Console/Commands/SendTestEmail.php): Safe administrative CLI testing tool (`obhs:mail-test`).

### Email Templates
- [`resources/views/emails/transactional.blade.php`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/resources/views/emails/transactional.blade.php): Branded, mobile-responsive HTML layout with MinSU green header bar (`#14532d` / `#166534`), typography, action button, and footer.
- [`resources/views/emails/transactional-text.blade.php`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/resources/views/emails/transactional-text.blade.php): Plain-text alternative.
- [`resources/views/emails/test.blade.php`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/resources/views/emails/test.blade.php) & [`test-text.blade.php`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/resources/views/emails/test-text.blade.php): Verification test templates.

### Workflow Controllers Updated
- [`app/Http/Controllers/BookingController.php`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/app/Http/Controllers/BookingController.php) (Bookings: new request, approve, reject).
- [`app/Http/Controllers/PropertyController.php`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/app/Http/Controllers/PropertyController.php) (Properties: admin approve, reject).
- [`app/Http/Controllers/PaymentController.php`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/app/Http/Controllers/PaymentController.php) (Payments: reminder, mark paid, review record).
- [`app/Console/Commands/NotifyOverduePayments.php`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/app/Console/Commands/NotifyOverduePayments.php) (Overdue rent payment alert).
- [`app/Http/Controllers/TenantOnboardingController.php`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/app/Http/Controllers/TenantOnboardingController.php) (Onboarding lifecycle steps).
- [`app/Http/Controllers/AuthController.php`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/app/Http/Controllers/AuthController.php) (Landlord permits, safety certs, student verifications).
- [`app/Http/Controllers/StudentPaymentController.php`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/app/Http/Controllers/StudentPaymentController.php) (Student monthly payment submission).
- [`app/Http/Controllers/StudentLeaveRequestController.php`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/app/Http/Controllers/StudentLeaveRequestController.php) (Tenancy leave submission).
- [`app/Http/Controllers/LandlordLeaveRequestController.php`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/app/Http/Controllers/LandlordLeaveRequestController.php) (Tenancy leave review).
- [`app/Http/Controllers/AdminIdentityRecoveryController.php`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/app/Http/Controllers/AdminIdentityRecoveryController.php) (Account identity recovery).

### Tests
- [`tests/Feature/ResendTransactionalEmailTest.php`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/tests/Feature/ResendTransactionalEmailTest.php): 13 automated tests.

---

## 4. Required Production Environment Variables

Add the following to the production `.env` file on Hostinger:

```env
# Mail Driver
MAIL_MAILER=resend

# Resend API Credentials
RESEND_API_KEY=re_your_verified_resend_api_key_here

# Sender Details (Must match verified domain in Resend)
MAIL_FROM_ADDRESS=notifications@mcc-boardinghouse.online
MAIL_FROM_NAME="Online Boarding House System"
```

> [!IMPORTANT]
> The `MAIL_FROM_ADDRESS` must use the exact domain or subdomain verified on the Resend dashboard. Never hardcode credentials in code or commit production `.env`.

---

## 5. Notification Event Matrix

| Event / Trigger | Triggering Class / Method | Recipient | DB Notif? | Transactional Email? | Action Button Target |
|---|---|---|---|---|---|
| User Email Verification | `MustVerifyEmail` / Auth | Student / Landlord | No | Yes (`VerifyEmail`) | Signed verify URL |
| Password Reset Link | `PasswordResetController` | User | No | Yes (`ResetPassword`) | Password reset token URL |
| New Booking Request | `BookingController@store` | Landlord | Yes | Yes (`SystemNotification`) | `landlord.bookings.index` |
| Booking Approved | `BookingController@approve` | Student | Yes | Yes (`SystemNotification`) | `student.onboarding.index` |
| Booking Rejected | `BookingController@reject` | Student | Yes | Yes (`SystemNotification`) | `student.bookings.index` |
| Property Approved | `PropertyController@adminApprove` | Landlord | Yes | Yes (`SystemNotification`) | `landlord.properties.index` |
| Property Rejected | `PropertyController@adminReject` | Landlord | Yes | Yes (`SystemNotification`) | `landlord.properties.index` |
| Onboarding Doc Submitted | `TenantOnboardingController` | Landlord | Yes | Yes (`SystemNotification`) | `landlord.onboarding.review` |
| Move-in Contract Signed | `TenantOnboardingController` | Landlord | Yes | Yes (`SystemNotification`) | `landlord.onboarding.review` |
| Deposit Proof Submitted | `TenantOnboardingController` | Landlord | Yes | Yes (`SystemNotification`) | `landlord.onboarding.review` |
| Onboarding Status Updated | `TenantOnboardingController` | Student | Yes | Yes (`SystemNotification`) | `student.onboarding.show` |
| Monthly Payment Submitted | `StudentPaymentController` | Landlord | Yes | Yes (`SystemNotification`) | `landlord.payments.index` |
| Rent Payment Confirmed | `PaymentController@markAsPaid` | Student | Yes | Yes (`SystemNotification`) | `student.bookings.index` |
| Payment Reminder Sent | `PaymentController@sendReminder` | Student | Yes | Yes (`SystemNotification`) | `student.bookings.index` |
| Overdue Payment Detected | `NotifyOverduePayments` command | Student & Landlord | Yes | Yes (`SystemNotification`) | Respective portal |
| Academic Verification Approved | `AuthController@adminApproveStudentVerification` | Student | Yes | Yes (`SystemNotification`) | `student.dashboard` |
| Academic Verification Rejected | `AuthController@adminRejectStudentVerification` | Student | Yes | Yes (`SystemNotification`) | `student.setup.show` |
| Additional Info Requested | `AuthController@adminRequestStudentVerificationInfo` | Student | Yes | Yes (`SystemNotification`) | `student.dashboard` |
| Business Permit Approved | `AuthController@adminApproveLandlordPermit` | Landlord | Yes | Yes (`SystemNotification`) | `landlord.dashboard` |
| Business Permit Rejected | `AuthController@adminRejectLandlordPermit` | Landlord | Yes | Yes (`SystemNotification`) | `landlord.setup.show` |
| Safety Certificate Approved | `AuthController@adminApproveLandlordSafetyCertificate` | Landlord | Yes | Yes (`SystemNotification`) | `landlord.dashboard` |
| Safety Certificate Rejected | `AuthController@adminRejectLandlordSafetyCertificate` | Landlord | Yes | Yes (`SystemNotification`) | `landlord.setup.show` |
| Tenancy Leave Request | `StudentLeaveRequestController@store` | Landlord | Yes | Yes (`SystemNotification`) | `landlord.leave_requests.index` |
| Tenancy Leave Approved | `LandlordLeaveRequestController@approve` | Student | Yes | Yes (`SystemNotification`) | `student.dashboard#onboarding` |
| Tenancy Leave Rejected | `LandlordLeaveRequestController@reject` | Student | Yes | Yes (`SystemNotification`) | `student.dashboard#onboarding` |
| Identity Recovery Outcome | `AdminIdentityRecoveryController` | Student | Yes | Yes (`SystemNotification`) | `student.setup.show` |

---

## 6. Sending Behavior and Delivery Reliability

1. **Shared Hosting Optimization:**
   - On Hostinger Shared Hosting, there are no persistent background daemons (`systemd`, `supervisord`, or Redis).
   - `SystemNotification` dispatches synchronously within the web request cycle.
   - This prevents emails from becoming permanently stranded in an unprocessed database queue table.
2. **Transaction Independence:**
   - Database operations (such as booking creation, status updates, or document reviews) commit to MySQL **before** external email dispatch.
   - If Resend experiences a momentary network timeout, the application logs a warning (`Log::warning(...)`) and returns cleanly to the user. The business transaction is preserved.
3. **Idempotency & Duplicate Prevention:**
   - Previous duplicate sends caused by concurrent `SystemNotification` AND `Mail::raw(...)` calls were eliminated.
   - Status updates are idempotent; re-submitting unchanged forms does not trigger duplicate events.

---

## 7. Testing Procedures

### Automated Test Suite
To run the automated transactional email test suite (100% mocked; does not make real network API requests):
```bash
php artisan test --filter=ResendTransactionalEmailTest
```

### Safe Manual CLI Testing
To safely test real outbound email delivery locally or on the production server without contacting students or landlords:
```bash
php artisan obhs:mail-test your-email@example.com
```

To test against the log driver:
```bash
php artisan obhs:mail-test test@example.com --mailer=log
```

---

## 8. Hostinger Deployment Instructions

1. **Upload Updated Files:**
   - Transfer modified files (`app/`, `resources/views/emails/`, `composer.json`, `composer.lock`).
2. **Install Vendor Dependencies:**
   - Run via SSH on Hostinger:
     ```bash
     composer install --no-dev --optimize-autoloader
     ```
3. **Configure Environment:**
   - Update `.env` with `MAIL_MAILER=resend`, `RESEND_API_KEY=...`, and `MAIL_FROM_ADDRESS=...`.
4. **Clear & Rebuild Cache:**
   ```bash
   php artisan config:clear
   php artisan config:cache
   php artisan view:clear
   php artisan view:cache
   ```
5. **Run Verification Command:**
   ```bash
   php artisan obhs:mail-test admin@yourdomain.com
   ```

### Rollback Procedure:
If email delivery needs to be immediately paused without code changes:
Change in `.env`:
```env
MAIL_MAILER=log
```
Run `php artisan config:clear && php artisan config:cache`. All emails will be safely written to `storage/logs/laravel.log` without user interruption.
