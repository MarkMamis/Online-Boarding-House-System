# Online Boarding House System - Documentation

## Overview
The Online Boarding House System (OBHS) is a role-based platform that connects students with landlords, supports booking and tenant onboarding, and gives administrators oversight for compliance and reporting.

### Core Modules
- Authentication and role-based access
- Property and room listings
- Booking requests and approvals
- Tenant onboarding workflow
- Messaging and notifications
- Reports and feedback
- Maintenance and payments (landlord)
- Analytics (landlord/admin)
- Chatbot assistant (role-scoped)

## Roles and Features

### Student
- Browse rooms and view details
- Property map with filters
- Booking requests and status tracking
- Direct messaging with landlords
- Tenant onboarding steps (documents, contract, deposit)
- Leave requests
- Room feedback and ratings
- Reports to admins
- Profile management
- Role-scoped chatbot with route guidance and limited DB insights

### Landlord
- Property management (create, edit, approve-ready)
- Room management (create, edit, availability)
- Booking request review (approve/reject)
- Tenant list and onboarding review
- Leave request handling
- Maintenance tracking
- Payments and billing
- Analytics dashboard
- Tenant feedback review and sentiment analysis
- Messaging with students
- Notifications

### Admin
- User management and review by role
- Property approval workflow
- Bookings oversight
- Onboarding audit (pending/active/completed)
- Reports review and response
- Notifications
- Platform overview dashboard and analytics

## User Flows

### Student Flow
1. Register and verify account.
2. Browse rooms or use the property map.
3. View room details and message landlords.
4. Submit booking request.
5. If approved, complete onboarding steps:
   - Upload documents
   - Review and sign contract
   - Pay deposit
6. Move in and submit feedback.
7. Submit reports or leave requests if needed.

### Landlord Flow
1. Register and complete profile.
2. Create property and rooms.
3. Review booking requests and approve tenants.
4. Review onboarding documents and approve.
5. Track tenants, maintenance, and payments.
6. Review feedback and respond to issues.

### Admin Flow
1. Review property approvals.
2. Monitor bookings and onboardings.
3. Manage users and resolve reports.
4. Track platform performance and activity.

## System Gaps and Opportunities

### Functional Gaps
- No global search or ranking for rooms beyond filters.
- No automated payment gateway integration.
- No landlord response workflow for feedback.
- Limited admin controls for tenant disputes.
- Chatbot has limited intent coverage and no knowledge base.

### Data and Quality Gaps
- Room availability relies on active bookings and onboarding; edge cases may exist for cancelled or expired records.
- Location-based results require property latitude/longitude consistency.
- Feedback averages are visible but not normalized for low sample sizes.

### UX Gaps
- Some pages use different layouts (legacy student layout vs dashboard layout).
- No unified help or FAQ area for all roles.
- Chatbot button placement may conflict with mobile layouts.

### Security and Compliance Gaps
- No audit log for critical actions (booking approvals, onboarding decisions).
- No rate limiting for chatbot or messaging endpoints.

## Suggested Next Steps
- Add a consistent FAQ/help section per role.
- Add payment gateway integration.
- Improve search and ranking for room discovery.
- Add admin audit logs and moderation tools.
- Expand chatbot intents and add a small knowledge base.
