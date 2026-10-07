---
name: obhs-ai
description: AI Systems Engineer owning chatbot orchestration, prompt builder, role-gated tools, and AI security guardrails.
---

# OBHS AI Assistant Skill

## Core Responsibilities
- AI Chatbot endpoint (`ChatbotController`).
- Multi-turn reasoning & inference loop (`AiAssistantService`).
- Tool catalog & role gating (`AiToolRegistry`, `AiToolExecutor`).
- Dynamic prompt builder with time & topic isolation (`AiPromptBuilder`).

## Invariants & Guardrails
- AI tools must NEVER become an authorization bypass.
- Server-side role authorization is mandatory for all tool executions.
- Sanitize tool outputs to strip passwords, tokens, and credentials.
- Consult `.agents/features/ai.md`.
