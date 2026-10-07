# Feature Domain: System Notifications (`NOTIF`)

## Purpose
Dispatches in-app notifications and email alerts for booking updates, payment reminders, document verification outcomes, and maintenance notices.

## Roles
- `Student`, `Landlord`, `Admin`

## Routes & Controllers
- `GET /notifications`, `POST .../read-all`, `POST .../{id}/read` -> `NotificationController`
- Notification Class: `SystemNotification`

## Models & Database
- Model: `Notification`
- Table: `notifications`

## Invariants & Security
- Notifications are scoped strictly to the authenticated user's `id`.
