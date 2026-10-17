# Communication, Alerts & Feedback: Messaging, Notifications & Reviews

**Audit Date:** October 18, 2026  
**Scope:** Evaluation of Messaging (`messages/index.blade.php`), Notifications (`notifications/index.blade.php`), and Star Ratings (`student/rooms/feedback.blade.php`)  

---

## 1. Messaging Experience (`resources/views/messages/index.blade.php`)

Messaging in OBHS allows direct communication between Students, Landlords, and Administrators.

### Architectural Invariants & Data Structure:
- Messages are grouped into threads using a composite synthetic key: `{$otherUserId}_{$propertyId}`.
- Every message belongs to a specific property context (`property_id`), which ensures conversations stay anchored to the specific boarding house inquiry or stay.
- **Production Hostinger Invariant:** OBHS runs on Hostinger Shared Hosting with Apache and MySQL. No root server access, Supervisor, or Redis are available. As such, real-time WebSocket daemons (Pusher/Reverb) are NOT assumed. Communication relies on reliable HTTP POST submission and page reload or asynchronous AJAX polling.

### Current Strengths:
- Clean 2-column layout on desktop: Left panel shows conversation list with avatar, landlord name, property title, unread count badge, and message snippet; Right panel shows chat history with green outbound bubbles and grey inbound bubbles.
- Direct inquiry entry points on room detail pages (`POST /student/rooms/{room}/inquire`).

### Critical Deficiencies:
1. **Mobile Responsiveness Failure (Split-Pane on Mobile):**
   - On screens `< 992px`, both the conversation list and the active chat view are rendered in stacked vertical cards (`col-12 col-lg-4` and `col-12 col-lg-8`).
   - On a phone, the user must scroll past all conversation buttons to reach the message chat box!
   - Modern mobile chat interfaces use a **master-detail toggle**: tapping a conversation hides the conversation list and shows the full chat view, with a back arrow to return to the list.
2. **No Image / Attachment Support:**
   - Students cannot send photos of receipts, maintenance leaks, or room conditions through the chat box.
3. **No Active Polling or Auto-Scroll:**
   - The thread chat box (`.thread-msg-list`) does not automatically scroll to the newest message at the bottom upon loading.

---

## 2. Notification Center (`resources/views/notifications/index.blade.php`)

Notifications track booking updates, admin decisions, document verification, and rent reminders.

### Current Strengths:
- Uses Laravel's native database notification system (`notifications` table).
- Provides tabs: *"All"*, *"Unread"*, *"Needs Action"*.
- Notification badge on top navbar icon (`.icon-btn-badge`) shows unread count.

### Critical Deficiencies:
1. **Notification Clutter & Badge Dominance:**
   - The supplied screenshots show a prominent count of **13 unread notifications**.
   - Notifications accumulate because standard system events (e.g. "Booking request received", "Payment record created") fire redundant entries without auto-clearing upon viewing the affected page.
   - There is no grouping of related notifications (e.g. 3 updates regarding the same booking should collapse into a single thread).
2. **Redundancy with Home Dashboard Activity:**
   - As audited in Document 05, the monolithic dashboard renders a "Recent Activity" card that duplicates 90% of the notifications list, creating dual maintenance and confusing user attention.

---

## 3. Ratings, Star Reviews & Room Feedback

Feedback is gathered through `RoomFeedbackController@store` and `resources/views/student/rooms/feedback.blade.php`.

### Invariants & Rules:
- Only students who have completed a stay in a room (`status = approved`, `check_out <= today`) can rate a room.
- Prevents fake reviews from random accounts who never stayed at the boarding house.
- Sentiment analysis pipeline categorizes feedback comment as `positive`, `neutral`, or `negative`.

### Current Strengths:
- Authentic, verified student feedback.
- Ratings calculate average score (`withAvg('feedbacks', 'rating')`) and count (`withCount('feedbacks')`) displayed on listing cards.

### Critical Deficiencies:
1. **Review Prompt Annoyance:**
   - When a stay finishes, the Home dashboard displays a persistent yellow warning banner: *"Rate your completed stay"*. If the student doesn't have time right then, there is no "Remind me later" or dismiss button.
2. **Intermediate Navigation Hop:**
   - Clicking the prompt button opens the room details page rather than directly opening the review submission modal/form.
3. **Public Reviews vs. Private Reports Confusion:**
   - Some students use the public review form to complain about urgent safety issues (e.g. broken locks or theft), not realizing it will be displayed publicly on the room card rather than dispatched to university administrators.
