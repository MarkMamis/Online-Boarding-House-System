# Feature Domain: Landlord Operations (`LND`)

## Purpose
Provides landlords with boarding house property management, room creation, slot management, maintenance toggling, tenant management, and operational analytics.

## Roles
- `Landlord` (Setup Incomplete, Setup Complete, Verified)

## Routes & Controllers
- `GET /landlord/setup`, `PUT /landlord/setup` -> `LandlordSetupController`
- `GET /landlord/dashboard` -> `AuthController`
- `GET /landlord/properties`, `POST ...`, `GET .../{property}/edit`, `PUT ...`, `DELETE ...` -> `PropertyController`
- `GET /landlord/properties/{property}/rooms`, `POST .../rooms`, `PUT .../{room}`, `DELETE ...` -> `RoomController`
- `GET /landlord/maintenance`, `POST .../set`, `POST .../{room}/complete` -> `MaintenanceController`
- `GET /landlord/analytics` -> `AnalyticsController`
- `GET /landlord/leave-requests`, `POST .../approve`, `POST .../reject` -> `LandlordLeaveRequestController`

## Models & Database
- Models: `User`, `LandlordProfile`, `Property`, `Room`, `RoomImage`, `LeaveRequest`
- Tables: `users`, `landlord_profiles`, `properties`, `rooms`, `room_images`, `leave_requests`

## Invariants & Security
- `PropertyPolicy` and `RoomPolicy` enforce that landlords can only modify properties where `landlord_id === Auth::id()`.
- Room slots available cannot exceed capacity.
