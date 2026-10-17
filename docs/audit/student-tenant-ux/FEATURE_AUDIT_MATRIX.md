# Student & Tenant Experience: Complete Feature Audit Matrix

**Audit Date:** October 18, 2026  
**Scope:** Evaluation of All Student/Tenant Modules against 11 Experience Dimensions  
**Rating Legend:**
- **Strong:** High quality, intuitive, well-implemented.
- **Adequate:** Functional and usable, but contains visual or ergonomics friction.
- **Needs Improvement:** Significant UX defects, administrative bloat, or usability barriers.
- **Unverified:** Requires live interactive browser inspection with active production dataset.

---

## Complete Module Scorecard

| Module | Customer Experience | Purpose Clarity | Discoverability | Mobile Usability | Visual Consistency | Navigation | Content Hierarchy | State Feedback | Accessibility | Performance | Workflow Continuity | Concise Rationale |
|---|---|---|---|---|---|---|---|---|---|---|---|---|
| **Student Home (`student.dashboard`)** | Needs Improvement | Adequate | Strong | Needs Improvement | Adequate | Needs Improvement | Needs Improvement | Adequate | Adequate | Needs Improvement | Needs Improvement | Monolithic 2,262-line blade loading 10 sub-panels; 23+ queries; macro KPI tiles confuse students. |
| **Tenant Home (`student.tenant.dashboard`)** | Adequate | Strong | Strong | Needs Improvement | Adequate | Adequate | Needs Improvement | Adequate | Adequate | Adequate | Adequate | Shows giant 66% Google Map iframe; sentiment NLP metrics exposed; payments separate. |
| **Browse Rooms (`student.rooms.index`)** | Adequate | Strong | Strong | Needs Improvement | Adequate | Adequate | Adequate | Adequate | Adequate | Adequate | Adequate | Rich search & carousel, but listing cards have badge clutter and mobile filter panel covers entire screen. |
| **Room Details (`student.rooms.show`)** | Strong | Strong | Strong | Adequate | Strong | Strong | Adequate | Strong | Adequate | Strong | Adequate | 16:6 hero banner crops smartphone photos; inquiry composer is clean; sticky mobile CTA missing. |
| **Property Details (`student.properties.show`)** | Adequate | Strong | Adequate | Adequate | Adequate | Adequate | Needs Improvement | Adequate | Adequate | Strong | Adequate | Minimal landlord credibility signals; room list is unstyled; missing campus walking anchor. |
| **Property Map (`student.properties.map_view`)** | Adequate | Strong | Strong | Adequate | Strong | Strong | Strong | Adequate | Adequate | Adequate | Adequate | Interactive Leaflet map works well, but marker pins display "TBD" when price bounds are null. |
| **Booking Request (`bookings.create`)** | Adequate | Strong | Strong | Needs Improvement | Strong | Strong | Adequate | Adequate | Adequate | Strong | Needs Improvement | Requires canvas e-signature at initial inquiry (redundant with lease signing); post-submit guidance vague. |
| **My Requests (`student.bookings.index`)** | Adequate | Strong | Strong | Adequate | Adequate | Adequate | Needs Improvement | Adequate | Adequate | Strong | Needs Improvement | Flat list mixes past/current; approved booking lacks direct link to start Tenant Onboarding. |
| **Tenant Onboarding (`student.onboarding.show`)** | Strong | Strong | Adequate | Adequate | Strong | Adequate | Strong | Adequate | Adequate | Strong | Strong | Linear 4-step state machine (Docs -> Lease -> Deposit -> Done) is robust; language is bureaucratic. |
| **My Payments (`student.payments.index`)** | Strong | Strong | Adequate | Adequate | Strong | Adequate | Strong | Strong | Adequate | Strong | Adequate | Multi-channel GCash/Bank upload works well; separated from Tenant Dashboard quick actions. |
| **Leave Requests (`student.leave_requests.*`)** | Adequate | Strong | Needs Improvement | Needs Improvement | Adequate | Adequate | Needs Improvement | Adequate | Adequate | Strong | Adequate | Form and table buried at bottom of tenant dashboard; desktop table clips on narrow mobile screens. |
| **Messaging (`messages.index`)** | Adequate | Strong | Strong | Needs Improvement | Strong | Strong | Adequate | Adequate | Adequate | Strong | Adequate | Desktop 2-column view works well; on mobile, conversation list stacks vertically on top of chat. |
| **Notifications (`notifications.index`)** | Adequate | Strong | Strong | Adequate | Strong | Strong | Adequate | Needs Improvement | Adequate | Strong | Adequate | Functional unread filtering; prone to notification spam (13+ unread count in screenshot); duplicates Home. |
| **Room Feedback (`student.rooms.feedback_page`)** | Strong | Strong | Adequate | Strong | Strong | Strong | Strong | Strong | Strong | Strong | Adequate | Verified tenant constraint enforced; banner on Home jumps to details rather than direct review form. |
| **Incident Reports (`student.reports.index`)** | Needs Improvement | Adequate | Adequate | Needs Improvement | Adequate | Adequate | Adequate | Adequate | Adequate | Strong | Needs Improvement | **P0 Hazard:** Lacks emergency PNP/security disclaimers; prospective students barred from reporting. |
| **Profile Management (`student.profile.show`)** | Strong | Strong | Strong | Adequate | Strong | Strong | Strong | Strong | Adequate | Strong | Strong | Clear academic credentials, verified ID status badge, change password form. |
| **Verification Setup (`student.setup.show`)** | Strong | Strong | Strong | Strong | Strong | Strong | Strong | Strong | Adequate | Strong | Strong | Approved 6-panel guided onboarding with duplicate ID dispute modal and review screen. |
