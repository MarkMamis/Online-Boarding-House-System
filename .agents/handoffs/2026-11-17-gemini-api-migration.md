# Handoff: Google Gemini API Migration

**Date:** 2026-11-17  
**Feature ID:** AI-MIGRATE-GEMINI-01  
**Area:** AI Assistant / Chatbot, Room Feedback Sentiment Analysis, Report Severity Classification  
**Status:** Complete & Verified  

---

## 1. Summary of Changes

Migrated the OBHS application from the Hugging Face Inference API to Google Gemini API:

1. **Gemini Configuration (`config/services.php`, `.env.example`, `.env.hostinger.example`)**:
   - Added `services.gemini` block with `api_key`, `chat_model` (default: `gemini-3.8-flash`), `classification_model` (default: `gemini-3.5-flash-lite`), `base_url` (`https://generativelanguage.googleapis.com/v1beta`), and `timeout` (30s).
   - Removed obsolete Hugging Face configuration and environment templates.
   - Updated deployment health check (`app/Console/Commands/DeploymentCheck.php`) to inspect `services.gemini.api_key`.

2. **Unified Gemini Service (`app/Services/GeminiService.php`)**:
   - Implemented `GeminiService` using Laravel's `Http` client.
   - Headers: uses `x-goog-api-key` header to authenticate securely without leaking keys in query strings or logs.
   - Structured Output: uses `responseMimeType: 'application/json'` and `responseSchema` with enum constraints for reliable single-label classification.
   - Transient Error Resilience: bounded backoff on 429 (rate limits) and 5xx (max 1 retry), without retrying 400/401/403/404 client errors or silently switching models.
   - Schema converter: `convertOpenAiToolsToGemini()` converts `AiToolRegistry` JSON schemas to Gemini `functionDeclarations`.

3. **Sentiment Analysis Migration (`RoomFeedbackController` & `LandlordFeedbackController`)**:
   - Replaced Hugging Face sentiment endpoint with `GeminiService::classify()` restricted to `positive`, `neutral`, and `negative`.
   - Blank comments are skipped without making external API calls.
   - If sentiment analysis fails, feedback records are safely created with `sentiment_label = null` and `sentiment_score = null`.
   - `LandlordFeedbackController::analyzePending()` re-analyzes pending records in batches of 25, leaving `sentiment_score = null` (no fabricated probability scores).

4. **Report Priority Classification Migration (`ReportPriorityClassifier`)**:
   - Replaced Hugging Face classifier with `GeminiService::classify()` restricted to `low`, `medium`, and `high`.
   - Preserved keyword-based heuristic fallback if Gemini is unconfigured, rate-limited (HTTP 429), or unavailable.

5. **Chatbot Complete Migration (`AiAssistantService`)**:
   - Preserved conversation history, system prompt (`systemInstruction`), and role-filtered tool declarations (`tools`).
   - Server-side authorization maintained via `AiToolRegistry` and `AiToolExecutor`.
   - Function calling loop: parses candidate `functionCall` parts, executes tools, passes responses back with `role: "function"` and `parts: [['functionResponse' => ...]]`, and gets the final conversational response.
   - Graceful fallback: handles 429 quota exhaustion or provider errors by returning user-friendly messages without exposing provider exceptions or API keys.

---

## 2. Important Files Changed

- `config/services.php`
- `.env.example`
- `.env.hostinger.example`
- `app/Console/Commands/DeploymentCheck.php`
- `app/Services/GeminiService.php` (new shared service)
- `app/Services/ReportPriorityClassifier.php`
- `app/Http/Controllers/RoomFeedbackController.php`
- `app/Http/Controllers/LandlordFeedbackController.php`
- `app/Services/AI/AiAssistantService.php`
- `tests/Feature/GeminiMigrationTest.php` (new test suite)
- `tests/Feature/ChatbotAiAssistantTest.php`

---

## 3. Verification & Evidence

- `tests/Feature/GeminiMigrationTest.php`: 10/10 tests passed (50 assertions).
- `tests/Feature/Chatbot*`: 54/54 tests passed.
- No database migrations or schema alterations required.
