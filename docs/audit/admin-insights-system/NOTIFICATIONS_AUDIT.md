# Notifications Module Audit (`/notifications`)

## 1. Scope & Architecture
- **Route:** `GET /notifications` (and `POST /notifications/read-all`, `POST /notifications/{id}/read`).
- **Nature of Route:** Shared across all three portals (Student, Landlord, Admin).
- **Layout Adaptability:** The view dynamically renders the parent layout based on `$user->role`:
  - `layouts.admin` for administrators.
  - `layouts.landlord` for landlords.
  - `layouts.student_dashboard` for students.

---

## 2. Notification Ownership & Security Scoping
- **Recipient Isolation:** All notification queries explicitly scope to the authenticated user:
  ```php
  DatabaseNotification::query()
      ->where('notifiable_type', get_class($user))
      ->where('notifiable_id', $user->id)
  ```
- **Read & Action Endpoint Security:**
  - `POST /notifications/{id}/read` uses `where('notifiable_id', $user->id)->firstOrFail()`. If User A attempts to mark User B's notification as read, it results in an immediate 404/403.
  - Action URLs (`data.url`) are checked for string presence and redirected safely. Destination routes enforce their own server-side role and policy middleware.

---

## 3. Supported Notification Events
- **Permit & Document Actions:** `permit_approved`, `permit_rejected`, `safety_certificate_approved`, `safety_certificate_rejected`.
- **Student Verification:** `student_verification_approved`, `student_verification_rejected`.
- **Booking & Tenancy:** Booking approvals, cancellations, deposit confirmations, onboarding contract signing.
- **Delivery Channel:** Standard database notifications stored in MySQL `notifications` table, making it 100% compatible with Hostinger shared hosting without requiring background queue workers.
