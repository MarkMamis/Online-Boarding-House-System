# Feature Domain: Room Bookings & Capacity (`BKG`)

## Purpose
Manages student reservation requests, slot availability calculations, landlord approvals/rejections, and cancellations.

## Roles
- `Student`, `Landlord`, `Admin`

## Routes & Controllers
- `GET /student/rooms/{room}/book`, `POST .../book` -> `BookingController`
- `GET /student/requests`, `POST .../{booking}/cancel` -> `BookingController`
- `GET /landlord/bookings`, `POST .../{booking}/approve`, `POST .../{booking}/reject` -> `BookingController`
- `GET /admin/bookings` -> `AdminBookingController`

## Models & Database
- Models: `Booking`, `Room`, `Property`, `TenantOnboarding`
- Tables: `bookings`, `rooms`, `properties`, `tenant_onboardings`

## Invariants & Security
- `BookingPolicy` enforces creation by verified students and approvals strictly by owning landlords.
- Room slot decrement must be handled atomically upon approval.
