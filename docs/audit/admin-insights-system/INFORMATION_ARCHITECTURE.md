# Information Architecture Evaluation

## 1. Current Information Architecture
- **OVERVIEW:** Dashboard
- **MONITORING:** Boarding Houses, Active Boarders, Onboarding Pipeline, Compliance Monitor
- **MANAGEMENT:** Property Reviews, Student Verification, Landlord Accreditation, Document Review Queue
- **INSIGHTS:** Reports, Analytics
- **SYSTEM:** User Accounts, Notifications

---

## 2. Evaluation & Recommendations
1. **Reports Placement:**
   - *Current:* Insights -> Reports
   - *Observation:* The reports page is an incident resolution queue where admins reply to student complaints and update operational status.
   - *Recommendation:* Keep route as-is for backward compatibility, but in future updates, represent this under **Management -> Incident Reports**, leaving **Insights** for aggregate institutional data.
2. **Notifications Placement:**
   - *Current:* System -> Notifications
   - *Observation:* Notifications is a global user utility accessed via the top navbar bell icon for all user roles (Student, Landlord, Admin).
   - *Recommendation:* Keep the global navbar entry point while maintaining the sidebar link under System for administrative convenience.
