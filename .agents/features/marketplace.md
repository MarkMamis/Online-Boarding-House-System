# Feature Domain: Public Marketplace & Discovery (`MKT`)

## Purpose
Public discovery layer for prospective students, parents, and visitors to browse verified MinSU boarding houses, view room details, and explore interactive maps.

## Roles
- `Guest`, `Student`, `Landlord`, `Admin`

## Routes & Controllers
- `GET /` -> `landing` (Landing view with featured listings and top-rated houses)
- `GET /rooms/{room}` -> `RoomController@publicShow`
- `GET /browse-map`, `GET .../data`, `GET .../suggestions`, `GET .../rooms` -> `PublicPropertyMapController`

## Models & Database
- Models: `Property`, `Room`, `RoomFeedback`, `User`, `LandlordProfile`
- Tables: `properties`, `rooms`, `room_feedbacks`, `users`, `landlord_profiles`

## Invariants & Future Boundary Rules
- Public queries MUST scope to `visibleToAudience()` or approved properties only.
- Planned Airbnb-style enhancements (MinSU distance, wishlists, comparisons) will reside in this module.
