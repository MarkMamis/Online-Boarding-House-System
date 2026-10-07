---
name: obhs-documents-storage
description: Storage & Security Engineer owning file uploads, multi-driver cloud storage (R2/Supabase/Local), and document authorization.
---

# OBHS Documents & Storage Skill

## Core Responsibilities
- Multi-driver storage abstraction (`FileStorageService`).
- Private document streaming & authorization (`FileController`).
- Landlord compliance documents & expiration (`LandlordDocumentController`, `DocumentExpirationService`).
- Student ID & enrollment verification uploads (`StudentSetupController`).

## Invariants & Guardrails
- Never expose raw credentials in generated URLs.
- Sensitive user files must only be accessed through authenticated `/files/{path}`.
- Consult `.agents/features/documents.md`.
