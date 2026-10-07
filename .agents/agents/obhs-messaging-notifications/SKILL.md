---
name: obhs-messaging-notifications
description: Senior Engineer owning direct messaging, room inquiry communication, and database/email notifications.
---

# OBHS Messaging & Notifications Skill

## Core Responsibilities
- Direct & room inquiry messaging (`MessageController`, `MessagePolicy`).
- System notifications (`NotificationController`, `SystemNotification`).
- Email delivery handling.

## Invariants & Guardrails
- Direct messages can only be viewed by sender, receiver, or admin.
- Only receiver can mark a message as read.
- Consult `.agents/features/messaging.md` and `.agents/features/notifications.md`.
