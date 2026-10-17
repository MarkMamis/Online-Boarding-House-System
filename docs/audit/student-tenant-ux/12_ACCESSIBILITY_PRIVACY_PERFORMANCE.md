# Accessibility, Privacy & Performance Audit

**Audit Date:** October 18, 2026  
**Scope:** Evaluation of Accessibility (WCAG 2.1 AA), Data Privacy, Incident Confidentiality, and Hostinger Shared Hosting Performance  

---

## 1. Accessibility Audit (WCAG 2.1 AA Compliance)

### A. Color Contrast Ratios
- **MinSU Brand Green (`#14532d`) on White (`#ffffff`):** Contrast ratio is **9.74:1** (Passes WCAG AAA).
- **Secondary Muted Text (`#64748b`) on White:** Contrast ratio is **4.59:1** (Passes WCAG AA for normal text).
- **Light Status Badges (Warning Yellow):**
  - Text-bg-warning (`#fef3c7` background with `#92400e` text) has a contrast ratio of **4.8:1** (Passes AA).
  - However, in several views, yellow badge text is rendered as plain dark grey on yellow, causing occasional contrast dips.
- **Form Placeholder Text:** Light grey placeholders (`#cbd5e1` on white) have a contrast ratio of **1.8:1**, failing minimum contrast. Placeholders should be supplemented with explicit `<label>` elements.

### B. Keyboard Navigation & Focus Indicators
- **Issue:** Several interactive cards (e.g. Recommended Rooms cards, Activity buttons) rely on `div` or `button` elements with removed default outline (`outline: none` or custom focus without `:focus-visible` ring).
- **Fix:** Ensure a visible 2px emerald focus ring (`outline: 2px solid var(--brand); outline-offset: 2px;`) is applied to all interactive elements.

### C. Touch Target Sizes
- On desktop, buttons are well-proportioned.
- On mobile devices, several table action buttons (e.g. "Cancel", "Mark read", "Open") have physical hit areas of only **28px to 32px tall**, violating the recommended 44x44px minimum.

---

## 2. Security & Privacy Audit

### A. Student Confidential Verification Documents
- During verification setup, students upload sensitive documents (MinSU Certificate of Registration / Enrollment, Government ID, School ID).
- **Code Inspection:** Verified that document paths are stored in protected storage or retrieved via `FileController@show` / `TenantOnboardingController@viewDocument`, enforcing role and ownership authorization.
- **Privacy Rule:** Academic verification documents must NEVER be rendered in public listing previews or shared with other students or unauthorized landlords.

### B. Incident Reporting vs. Public Property Reviews
- **Critical Architectural Invariant:**
  - Public Room Feedback (`RoomFeedback`) is visible on the marketplace to help other students assess noise, cleanliness, and landlord responsiveness.
  - Incident Reports (`Report`) contain sensitive personal grievances, harassment complaints, or security disputes.
  - **Audit Finding:** In `student/dashboard.blade.php`, the "Recent Activity" feed maps report submissions (`report_submitted`) alongside public events. While scoped to the logged-in student, showing detailed report titles on an open laptop screen in a campus library poses privacy risks.
  - **Recommendation:** Keep Incident Reports strictly inside the private Help Center / Support view.

### C. Roommate Directory Privacy
- In both `student/dashboard.blade.php` and `student/tenant-dashboard.blade.php`, the Roommates panel lists other students residing in the room.
- Currently, it displays: Full Name, Academic Program, and Check-in Date.
- It appropriately omits personal phone numbers, email addresses, and guardian contact details, preventing doxxing while still fostering room safety.

---

## 3. Performance & Shared Hosting Compatibility

### Invariants:
- The production environment is **Hostinger Shared Web Hosting (Apache / MySQL 8.x / PHP 8.2+)**.
- PHP memory limits are typically 128MB or 256MB.
- Execution time limits are 30–60 seconds.

### The Monolithic Query Bottleneck in `AuthController@studentDashboard`:
When a student visits `/student/dashboard`, the controller currently executes:
1. `TenantOnboarding::where('status', 'completed')...exists()`
2. `student->bookings()->where('status', 'approved')...first()`
3. `activeBookingsCount` query
4. `pendingBookingsCount` query
5. `messagesCount` query
6. `unreadMessagesCount` query
7. `notificationsCount` query
8. `totalReports` query
9. `reportsWithResponses` query
10. `unreadResponsesCount` query
11. `canSubmitReport` query
12. `ratingReminders` query (with joins across rooms, properties, feedbacks)
13. `recentBookings` query
14. `recentMessages` query
15. `Message::with(...)->where(...)->latest()->limit(200)->get()` (loads up to 200 message models into memory!)
16. `latestOnboarding` query
17. `allOnboardings` query
18. `leaveRequests` query
19. `roommates` query
20. `messageProperties` query
21. `recommendedRooms` query (loads all rooms and filters via PHP closure)
22. `allRooms` query
23. `allProperties` query

**Total: Over 23 distinct SQL queries on a single page load!**
Loading 200 messages and all system rooms on every home dashboard visit wastes CPU cycles and database connection pools on shared hosting.

### Optimization Roadmap:
- Transition to the **Context-Aware Home**: Only query what the student's current lifecycle state actually needs (e.g. searching students do NOT query leave requests or roommates; resident tenants do NOT load 200 marketplace rooms into PHP memory).
- Implement standard pagination (`paginate(12)`) on room catalogs instead of `get()->filter()->take(6)`.
- Use lazy loading (`loading="lazy"`) for all listing and property images.
