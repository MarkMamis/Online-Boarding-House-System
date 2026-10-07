# Canonical Role & Access Control Matrix

This document provides the authoritative endpoint access matrix across all user roles, setup states, and ownership boundaries in the Online Boarding House System (OBHS).

---

## 1. Role Classifications

- **`GUEST`**: Unauthenticated visitor.
- **`PENDING`**: Authenticated user with `users.role = 'pending'`.
- **`STUDENT_INC`**: Authenticated student who has not completed student ID & college verification setup.
- **`STUDENT_COM`**: Authenticated student with verified profile and completed student setup (`onboarding_complete = 1`).
- **`LANDLORD_INC`**: Authenticated landlord who has not completed profile setup.
- **`LANDLORD_COM`**: Authenticated landlord with completed profile setup.
- **`ADMIN`**: Authenticated system administrator (`users.role = 'admin'`).

---

## 2. Endpoint Access Control Matrix

| Route Name | Method | URI Pattern | Middleware / Policy | Classification | Allowed Roles | Ownership / Context Check | Unauthorized Behavior |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| `landing` | `GET` | `/` | None | `PUBLIC` | All | None | 200 OK (or dashboard redirect if auth) |
| `rooms.public.show` | `GET` | `/rooms/{room}` | None | `PUBLIC` | All | Property must be approved | 200 OK / 404 |
| `public.properties.map` | `GET` | `/browse-map` | None | `PUBLIC` | All | Approved properties only | 200 OK |
| `public.properties.map_data` | `GET` | `/browse-map/data` | None | `PUBLIC` | All | Approved properties only | 200 JSON |
| `login` | `GET` | `/login` | `guest` | `PUBLIC` | Guest | None | Redirects to role dashboard if auth |
| `login.submit` | `POST` | `/login` | `guest` | `PUBLIC` | Guest | Rate-limited by IP/user | 422 / Redirect |
| `register` | `GET` | `/register` | `guest` | `PUBLIC` | Guest | None | Redirects if auth |
| `register.student` | `GET` | `/register/student` | `guest` | `PUBLIC` | Guest | None | Redirects if auth |
| `register.landlord` | `GET` | `/register/landlord` | `guest` | `PUBLIC` | Guest | None | Redirects if auth |
| `register.submit` | `POST` | `/register` | `guest` | `PUBLIC` | Guest | Form validation | 422 |
| `register.admin` | `GET/POST` | `/register/admin` | `role:admin` (or config) | `ADMIN_ONLY` | Admin | Only active if enabled in config | 403 Forbidden |
| `logout` | `POST` | `/logout` | `auth` | `AUTHENTICATED` | All Authenticated | Session invalidated | 302 Redirect to `/login` |
| `verification.notice` | `GET` | `/email/verify` | `auth` | `AUTHENTICATED` | Unverified Auth | None | 200 OK |
| `verification.verify` | `GET` | `/email/verify/{id}/{hash}` | `signed`, `throttle:6,1` | `AUTHENTICATED` | Unverified Auth | SHA-1 hash matching user email | 403 Forbidden |
| `onboarding.role.show` | `GET` | `/onboarding/role` | `auth`, `verified` | `AUTHENTICATED` | Pending | Must be role `pending` | Redirects to portal if assigned |
| `onboarding.role.store` | `POST` | `/onboarding/role` | `auth`, `verified` | `AUTHENTICATED` | Pending | Sets role to `student` or `landlord` | 302 Redirect |
| `files.show` | `GET` | `/files/{path}` | `auth`, `verified` | `OWNER_RESTRICTED` | All Authenticated | Evaluated by `FileController@authorizePath` | 403 Forbidden |
| `chatbot.history` | `GET` | `/chatbot/history` | `auth`, `verified`, `student.setup` | `AUTHENTICATED` | Student_Com, Landlord_Com, Admin | Binds strictly to `auth()->id()` | 403 / Redirect |
| `chatbot.message` | `POST` | `/chatbot/message` | `auth`, `verified`, `student.setup`, `throttle:chatbot` | `AUTHENTICATED` | Student_Com, Landlord_Com, Admin | Tools authorized per role | 403 / 429 |
| `messages.index` | `GET` | `/messages` | `auth`, `verified`, `student.setup` | `PARTICIPANT_ONLY` | Student_Com, Landlord_Com, Admin | User must be sender or receiver | 403 / Redirect |
| `messages.store` | `POST` | `/messages` | `auth`, `verified`, `student.setup` | `PARTICIPANT_ONLY` | Student_Com, Landlord_Com, Admin | Receiver must exist | 422 / 302 |
| `notifications.index` | `GET` | `/notifications` | `auth`, `verified`, `student.setup` | `AUTHENTICATED` | Student_Com, Landlord_Com, Admin | Binds strictly to `auth()->user()->notifications` | 403 / Redirect |
| `student.setup.show` | `GET` | `/student/setup` | `auth`, `verified`, `role:student` | `STUDENT_ONLY` | Student_Inc, Student_Com | Must be student role | 403 Forbidden |
| `student.setup.update` | `PUT` | `/student/setup` | `auth`, `verified`, `role:student` | `STUDENT_ONLY` | Student_Inc, Student_Com | Updates `auth()->user()` | 403 / 422 |
| `student.dashboard` | `GET` | `/student/dashboard` | `auth`, `verified`, `role:student`, `student.setup` | `STUDENT_ONLY` | Student_Com | Must have completed student setup | Redirects to `/student/setup` |
| `student.rooms.index` | `GET` | `/student/rooms` | `auth`, `verified`, `role:student`, `student.setup` | `STUDENT_ONLY` | Student_Com | Only approved properties/rooms | Redirects to setup / 403 |
| `bookings.create` | `GET` | `/student/rooms/{room}/book` | `auth`, `verified`, `role:student`, `student.setup` | `STUDENT_ONLY` | Student_Com | `BookingPolicy@create` | 403 Forbidden |
| `bookings.store` | `POST` | `/student/rooms/{room}/book` | `auth`, `verified`, `role:student`, `student.setup` | `STUDENT_ONLY` | Student_Com | `BookingPolicy@create`, room availability | 403 Forbidden |
| `student.bookings.cancel` | `POST` | `/student/requests/{booking}/cancel` | `auth`, `verified`, `role:student`, `student.setup` | `OWNER_ONLY` | Student_Com | `booking.student_id === auth()->id()` | 403 Forbidden |
| `student.onboarding.show` | `GET` | `/student/onboarding/{onboarding}` | `auth`, `verified`, `role:student`, `student.setup` | `OWNER_ONLY` | Student_Com | `TenantOnboardingPolicy@view` (`student_id`) | 403 Forbidden |
| `student.payments.index` | `GET` | `/student/payments` | `auth`, `verified`, `role:student`, `student.setup` | `STUDENT_ONLY` | Student_Com | Must have approved booking | 403 Forbidden |
| `student.payments.store` | `POST` | `/student/payments` | `auth`, `verified`, `role:student`, `student.setup` | `OWNER_ONLY` | Student_Com | `booking.student_id === auth()->id()` | 403 Forbidden |
| `landlord.setup.show` | `GET` | `/landlord/setup` | `auth`, `verified`, `role:landlord` | `LANDLORD_ONLY` | Landlord_Inc, Landlord_Com | Must be landlord role | 403 Forbidden |
| `landlord.dashboard` | `GET` | `/landlord/dashboard` | `auth`, `verified`, `role:landlord` | `LANDLORD_ONLY` | Landlord_Inc, Landlord_Com | Incomplete setup redirected | Redirects to `/landlord/setup` |
| `landlord.properties.index` | `GET` | `/landlord/properties` | `auth`, `verified`, `role:landlord`, `landlord.setup` | `LANDLORD_ONLY` | Landlord_Com | `landlord_id === auth()->id()` | Redirects to setup / 403 |
| `landlord.properties.store` | `POST` | `/landlord/properties` | `auth`, `verified`, `role:landlord`, `landlord.setup` | `LANDLORD_ONLY` | Landlord_Com | `PropertyPolicy@create` | 403 / 422 |
| `landlord.properties.update` | `PUT` | `/landlord/properties/{property}` | `auth`, `verified`, `role:landlord`, `landlord.setup` | `OWNER_ONLY` | Landlord_Com | `PropertyPolicy@update` (`landlord_id`) | 403 Forbidden |
| `landlord.bookings.approve` | `POST` | `/landlord/bookings/{booking}/approve` | `auth`, `verified`, `role:landlord`, `landlord.setup` | `OWNER_ONLY` | Landlord_Com | `BookingPolicy@approve` (property owner) | 403 Forbidden |
| `landlord.payments.index` | `GET` | `/landlord/payments` | `auth`, `verified`, `role:landlord`, `landlord.setup` | `LANDLORD_ONLY` | Landlord_Com | Scoped to owned properties | 403 Forbidden |
| `landlord.payments.mark_paid` | `POST` | `/landlord/payments/{booking}/paid` | `auth`, `verified`, `role:landlord`, `landlord.setup` | `OWNER_ONLY` | Landlord_Com | `booking.room.property.landlord_id === auth()->id()` | 403 Forbidden |
| `admin.dashboard` | `GET` | `/admin/dashboard` | `role:admin` | `ADMIN_ONLY` | Admin | `users.role === 'admin'` | 403 Forbidden |
| `admin.properties.index` | `GET` | `/admin/properties` | `role:admin` | `ADMIN_ONLY` | Admin | `users.role === 'admin'` | 403 Forbidden |
| `admin.properties.approve` | `POST` | `/admin/properties/{property}/approve` | `role:admin` | `ADMIN_ONLY` | Admin | `users.role === 'admin'` | 403 Forbidden |
| `admin.documents.approve` | `POST` | `/admin/documents/{document}/approve` | `role:admin` | `ADMIN_ONLY` | Admin | `users.role === 'admin'` | 403 Forbidden |
| `admin.boarding_monitoring.students` | `GET` | `/admin/boarding-monitoring/students` | `role:admin` | `ADMIN_ONLY` | Admin | `users.role === 'admin'` | 403 Forbidden |

---

## 3. IDOR Prevention Standards

1. **Never trust client-supplied owner identifiers:** Routes must never accept `student_id`, `landlord_id`, or `user_id` as request parameters to select records for mutation.
2. **Explicit Eloquent Scopes on Mutations:** When mutating resources, always qualify queries by the authenticated user's ID:
   - For student operations: `Booking::where('id', $id)->where('student_id', Auth::id())->firstOrFail();`
   - For landlord operations: `Booking::where('id', $id)->whereHas('room.property', fn($q) => $q->where('landlord_id', Auth::id()))->firstOrFail();`
3. **Double Barrier Rule:** Combine Laravel Policy checks (`$this->authorize(...)`) with server-side scoped Eloquent queries for all state-changing endpoints.
