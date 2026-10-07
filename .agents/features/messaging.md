# Feature Domain: Direct Messaging & Inquiries (`MSG`)

## Purpose
Provides authenticated direct messaging between students, landlords, and administrators, including room inquiry threads.

## Roles
- `Student`, `Landlord`, `Admin`

## Routes & Controllers
- `GET /messages`, `POST /messages`, `POST /messages/{message}/read` -> `MessageController`
- `POST /student/rooms/{room}/inquire` -> `MessageController@storeInquiry`

## Models & Database
- Model: `Message`, `User`, `Room`
- Table: `messages`

## Invariants & Security
- `MessagePolicy` ensures only the sender, receiver, or admin can read messages.
- Only the intended receiver can mark a message as read.
