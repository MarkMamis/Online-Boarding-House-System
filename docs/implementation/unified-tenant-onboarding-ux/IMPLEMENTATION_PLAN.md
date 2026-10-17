# Implementation Plan: Unified Tenant Onboarding & Move-in Transformation

## Executive Summary
This document outlines the architectural and visual transformation of the Tenant Onboarding (Move-in) workflow across the three authorized roles in the Online Boarding House System (OBHS): **Student**, **Landlord**, and **Admin**.

Tenant Onboarding occurs after a student's booking request is approved by a landlord. This project redesigns the move-in experience to reuse the approved OBHS profile onboarding design system (`.setup-shell`, `.question-kicker`, `.question-title`, `.btn-brand`, and clean dropzones/signature pad) without numbered steppers or 4-box KPI clutter.

## Core Principle
**"One Design Language. Role-Specific Experiences."**
- **Student:** Guided submission answering *"What do I need to submit?"*
- **Landlord:** Focused review workspace answering *"What do I need to review?"*
- **Admin:** Authorized verification and inspection answering *"What am I authorized to verify?"*

## Phased Execution
1. **Pass 1 — Audit & State Machine Mapping:**
   - Traced `TenantOnboardingController` state machine: `pending` $\rightarrow$ `documents_uploaded` $\rightarrow$ `contract_signed` $\rightarrow$ `deposit_paid` $\rightarrow$ `completed`.
   - Identified reusable component primitives from `student/setup/index.blade.php`, `<x-onboarding.contract-document>`, and `<x-signature-pad>`.
2. **Pass 2 — Student Guided Move-in:**
   - Rebuilt `student/onboarding/show.blade.php` and `index.blade.php`.
   - Stripped 1,000+ lines of old stepper CSS, 4-box KPI widgets, and redundant card layers.
   - Dynamic stage rendering: Stage 1 Requirements Upload, Stage 2 Contract Review & E-signing, Stage 3 Move-in Payment, Stage 4 Payment Under Review, Stage 5 Digital Tenant ID Pass.
3. **Pass 3 — Landlord Move-in Workspace:**
   - Rebuilt `landlord/onboarding/review.blade.php` and simplified `index.blade.php`.
   - Replaced multi-tab interface with a single contextual workspace prioritizing actionable decisions at the top.
   - Integrated document approval/rejection, landlord contract signing, and move-in payment verification.
4. **Pass 4 — Admin Read-Only Verification Alignment:**
   - Updated `admin/onboardings/show.blade.php` to display full contract and payment details while strictly preserving read-only monitoring boundaries.
5. **Pass 5 — Verification & Automated Testing:**
   - Created `tests/Feature/TenantMoveInTransformationTest.php` with 10 feature tests covering all state transitions, role authorizations, and review actions.
   - Verified 44 regression tests passing.
