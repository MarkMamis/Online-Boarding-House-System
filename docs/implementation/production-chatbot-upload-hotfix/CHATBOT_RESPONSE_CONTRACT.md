# Chatbot Request & Response Contract

## Endpoint 1: Conversation History
- **Route**: `GET /chatbot/history`
- **Middleware**: `web`, `auth`, `verified`, `role.selected`
- **Request Headers**:
  - `Accept`: `application/json`
  - `X-Requested-With`: `XMLHttpRequest`
- **Credentials**: `same-origin`

### Success Response (HTTP 200 OK)
```json
{
  "conversation_id": 142,
  "messages": [
    {
      "id": 1024,
      "role": "assistant",
      "content": "Kumusta! Paano kita matutulungan ngayon?",
      "meta": {
        "actions": []
      },
      "created_at": "2026-10-24T01:00:00.000000Z"
    }
  ]
}
```

---

## Endpoint 2: Send Message
- **Route**: `POST /chatbot/message`
- **Middleware**: `web`, `auth`, `verified`, `role.selected`, `throttle:chatbot`
- **Request Headers**:
  - `Content-Type`: `application/json`
  - `Accept`: `application/json`
  - `X-Requested-With`: `XMLHttpRequest`
  - `X-CSRF-TOKEN`: `<csrf_token>`
- **Request Payload**:
```json
{
  "content": "Show me available boarding houses near campus",
  "lat": 13.4115,
  "lng": 121.1803
}
```
*Note: `lat` and `lng` are optional numbers between -90..90 and -180..180.*

### Success Response (HTTP 200 OK)
```json
{
  "reply": "Narito ang mga bakanteng boarding house na malapit sa MinSU Calapan Campus.",
  "message": "Narito ang mga bakanteng boarding house na malapit sa MinSU Calapan Campus.",
  "actions": [
    {
      "type": "internal_route",
      "label": "Browse Rooms",
      "route": "/student/rooms",
      "url": "/student/rooms"
    }
  ],
  "meta": {
    "actions": [
      {
        "type": "internal_route",
        "label": "Browse Rooms",
        "route": "/student/rooms",
        "url": "/student/rooms"
      }
    ]
  }
}
```

---

## Error Response Specifications

### 1. Unauthenticated (HTTP 401)
Triggered when session expires or user is unauthenticated.
```json
{
  "message": "Unauthenticated."
}
```
*Frontend Action: Displays "Your session has expired. Please sign in again."*

### 2. Incomplete Setup (HTTP 403)
Triggered when user has not completed required account onboarding.
```json
{
  "message": "Complete your landlord setup before accessing this feature.",
  "redirect": "https://mcc-boardinghouse.online/landlord/setup"
}
```

### 3. Rate Limited (HTTP 429)
Triggered when `throttle:chatbot` is exceeded.
```json
{
  "message": "Too Many Attempts."
}
```
*Frontend Action: Displays Filipino friendly guidance to wait before retrying.*

### 4. Validation Failure (HTTP 422)
Triggered when `content` is missing, exceeds 2000 chars, or coords are malformed.
```json
{
  "message": "The content field is required.",
  "errors": {
    "content": ["The content field is required."]
  }
}
```

### 5. AI Service Failure / Provider Outage (HTTP 500)
Triggered when external LLM or orchestration encounters an exception.
```json
{
  "message": "Unable to process your message right now. Please try again shortly.",
  "reply": "Paumanhin, nagkaroon ng pansamantalang aberya sa sistema. Maaari kang sumubok muli mamaya."
}
```
*Security note: Detailed exception message is sanitized and written to Laravel log; no provider stack traces or API keys are exposed to the client.*
