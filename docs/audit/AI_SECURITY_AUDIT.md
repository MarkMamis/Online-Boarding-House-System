# AI Assistant Security & Guardrails Audit

This document reviews the security architecture of the OBHS AI-orchestrated chatbot, including prompt isolation, role-based tool authorization, data boundary enforcement, and external inference safety.

---

## 1. AI Assistant Subsystem Architecture

```
User Message -> ChatbotController@message (throttle:chatbot)
                     │
                     ▼
          AiConversationService
          (User Conversation Isolation)
                     │
                     ▼
             AiPromptBuilder
  (Injects System Guardrails, Local DateTime, Role Identity)
                     │
                     ▼
           AiAssistantService
  (Multi-Turn Reasoning + Tool Invocation Loop)
                     │
          ┌──────────┴──────────┐
          │                     │
          ▼                     ▼
   AiToolRegistry        AiToolExecutor
 (Role Authorization)  (Output Sanitization)
          │                     │
          └──────────┬──────────┘
                     │
                     ▼
        Hugging Face Inference API
                     │
                     ▼
          JSON Response with Actions
```

---

## 2. Key Security Properties & Guardrails

### A. Non-Bypass Rule: Server-Side Tool Authorization
- **Fundamental Rule:** The LLM is NEVER trusted to determine whether a user is authorized to execute an action or read sensitive data.
- **Implementation:**
  1. Every tool in [`AiToolRegistry.php`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/app/Services/AI/AiToolRegistry.php) defines an explicit `allowed_roles` list (e.g. `['admin']`, `['student']`, `['landlord', 'admin']`).
  2. [`AiToolExecutor@execute`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/app/Services/AI/AiToolExecutor.php#L20-L36) validates the requesting user's actual `Auth::user()->role` against the registry before invoking any handler.
  3. If an unauthorized role attempts to invoke an admin or landlord tool via prompt injection, the executor halts immediately and logs a security warning:
     ```
     AI Chatbot Tool Authorization Violation: User #X (student) attempted to invoke tool 'get_landlord_document_statistics'.
     ```

### B. Conversation Isolation
- **Evaluation:** Pass.
- **Evidence:**
  `ChatbotController` fetches conversation via `AiConversationService::getOrCreateConversation($user)` where query binds strictly `where('user_id', $user->id)`. A user cannot view or continue another user's conversation thread.

### C. Output Sanitization & Credential Protection
- **Implementation:**
  [`AiToolExecutor@sanitizeOutput`](file:///c:/Users/JC%20Zamora/Downloads/Mark-Capstone/obhs/app/Services/AI/AiToolExecutor.php#L69-L83) recursively strips any data keys matching `password`, `remember_token`, `token`, `secret`, `hash`, or storage credentials before feeding context back to the LLM.

### D. Rate Limiting & Denial of Service Protection
- **Implementation:**
  `/chatbot/message` is wrapped with `throttle:chatbot` middleware, preventing abuse and excessive API cost.

### E. Topic Isolation & Hallucination Resistance
- **Prompt Guardrails:**
  `AiPromptBuilder` includes explicit boundary directives:
  - Role-specific system instructions.
  - Strict date anchoring with current server timestamp.
  - Action buttons format validation (internal route verification).
- **Test Evidence:**
  Verified comprehensively by:
  - `Tests\Feature\ChatbotAiAssistantTest`
  - `Tests\Feature\ChatbotTopicIsolationTest`
  - `Tests\Feature\ChatbotLandlordDocumentTest`
  - `Tests\Feature\ChatbotStudentAnalyticsTest`
  - `Tests\Feature\ChatbotReportDateFilterTest`

---

## 3. Key Findings

### AI-SEC-001: External API Resilience
- **Observation:** `AiAssistantService` integrates retry logic with exponential backoff on transient 5xx HTTP errors from the inference provider, and fails gracefully to an informative user message on terminal errors without exposing stack traces.
- **Status:** Verified and secure.
