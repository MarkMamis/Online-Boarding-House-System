# Feature Domain: Student Experience (`STU`)

## Purpose
Enables students to complete verification setup, maintain profile credentials, browse verified boarding houses and rooms, view interactive map vacancy data, and access tenant dashboards.

## Roles
- `Student` (Setup Incomplete, Setup Complete, Active Tenant)

## Routes & Controllers
- `GET /student/setup`, `PUT /student/setup` -> `StudentSetupController`
- `GET /student/profile`, `PUT /student/profile`, `PUT .../change-password` -> `StudentProfileController`
- `GET /student/dashboard`, `GET /student/tenant-dashboard` -> `AuthController`
- `GET /student/rooms`, `GET /student/rooms/{room}` -> `RoomController`
- `GET /student/properties/map`, `GET .../map-data`, `GET .../{property}` -> `StudentPropertyController`
- `POST /student/leave-requests`, `POST .../cancel` -> `StudentLeaveRequestController`
- `GET /student/reports`, `POST /student/reports`, `POST .../mark-read` -> `ReportController`

## Models & Database
- Models: `User`, `Property`, `Room`, `LeaveRequest`, `Report`, `RoomFeedback`
- Tables: `users`, `properties`, `rooms`, `leave_requests`, `reports`, `room_feedbacks`

## Invariants & Security
- `EnsureStudentSetupComplete` redirects students with incomplete setup to `/student/setup`.
- Student profile updates only affect the authenticated user (`Auth::id()`).
