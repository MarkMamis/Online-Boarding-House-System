# Feature Domain: AI Conversational Assistant (`AI`)

## Purpose
Orchestrates an intelligent AI chatbot for students, landlords, and administrators to query room recommendations, document compliance status, report statistics, and platform guidance.

## Roles
- `Student`, `Landlord`, `Admin`

## Routes & Controllers
- `GET /chatbot/history`, `POST /chatbot/message` -> `ChatbotController`

## Services
- `AiAssistantService`, `AiPromptBuilder`, `AiToolRegistry`, `AiToolExecutor`, `AiConversationService`

## Invariants & Security
- Every tool in `AiToolRegistry` defines strict `allowed_roles`.
- `AiToolExecutor` enforces server-side authorization before calling tool handlers.
- Output sanitization strips passwords, tokens, and storage credentials.
