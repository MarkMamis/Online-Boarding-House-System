# OBHS Student Portal — Information Architecture

**Date:** October 18, 2026  
**System:** Online Boarding House System (OBHS) — Mindoro State University (MinSU)  
**Audience:** Student Accommodation Consumers & Active Tenants  

---

## 1. Executive Overview

Prior to this redesign, the Student Portal reflected an administrative monitoring paradigm inherited from the administrative dashboard. Students navigating the platform encountered statistical counters (`Available Rooms: 0`, `Pending Requests: 0`), administrative operation banners (`Workspace: Student Operations`), and multiple duplicate sub-panels embedded in a single monolithic Blade template.

The new Information Architecture (IA) re-centers the student journey around consumer marketplace hospitality principles:
1. **Discover:** Intuitive search and comparison of accredited student boarding houses.
2. **Explore:** Image-first listing catalog with transparent per-bed / per-room pricing.
3. **Map:** Campus vicinity orientation showing boarding house proximity to MinSU gates.
4. **Request & Onboard:** Clear status tracking and frictionless digital lease completion.
5. **Live & Support:** Dedicated resident stay hub and confidential safety incident reporting.

---

## 2. Navigation Architecture

### Primary Navigation Surface (`<x-student-sidebar />`)

| Original Administrative Label | Modern Consumer Marketplace Label | Route Target | Role & Visibility |
| :--- | :--- | :--- | :--- |
| **Dashboard** | **Home** | `student.dashboard` / `student.tenant.dashboard` | Universal entrance adapted to student lifecycle |
| **Browse Rooms** | **Explore Rooms** | `student.rooms.index` | Universal catalog access (never locked out) |
| **Property Map** | **Campus Map** | `student.properties.map_view` | Interactive Leaflet map near MinSU |
| **Notifications** | **Notifications** | `notifications.index` | System & tenancy notifications |
| **Requests** | **My Bookings & Stays** | `student.bookings.index` | Unified reservations and past stays |
| **Messages** | **Messages** | `messages.index` | Host-inquiry & landlord messaging |
| *(Hidden for non-tenants)* | **Help & Safety** | `student.reports.index` | Universal confidential support & incident reporting |
| **Profile** | **Profile** | `student.profile.show` | Account credentials, academic info |

### Secondary / Contextual Resident Navigation

For students with active, completed tenant onboarding:
- **My Room:** Direct link to assigned accommodation room specifications (`student.rooms.show`).
- **Payments:** Payment history, receipts, and advance rent tracking (`student.payments.index`).
- **Tenant Feedback:** Post-stay and active stay review form (`student.rooms.feedback_page`).

---

## 3. URL Compatibility & Route Preservation

All canonical routes defined in `routes/web.php` remain completely preserved:
- `/student/dashboard` &rarr; `student.dashboard`
- `/student/tenant-dashboard` &rarr; `student.tenant.dashboard`
- `/student/rooms` &rarr; `student.rooms.index`
- `/student/rooms/{room}` &rarr; `student.rooms.show`
- `/student/requests` &rarr; `student.bookings.index`
- `/student/properties/map` &rarr; `student.properties.map_view`
- `/student/properties/map-data` &rarr; `student.properties.map`
- `/messages` &rarr; `messages.index`
- `/student/reports` &rarr; `student.reports.index`
- `/student/onboarding` &rarr; `student.onboarding.index`
