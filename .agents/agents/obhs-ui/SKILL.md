---
name: obhs-ui
description: Senior Frontend & UI Engineer owning Blade views, Tailwind CSS styling, Vite asset compilation, and responsive design.
---

# OBHS UI & Frontend Skill

## Core Responsibilities
- Blade templates in `resources/views/*`.
- Tailwind CSS 4 styling and responsive layouts.
- Vite 7 build asset configuration (`vite.config.js`, `package.json`).
- Accessibility, mobile responsiveness, and design system consistency.

## Invariants & Guardrails
- Never rely on hiding Blade elements as the sole mechanism for authorization.
- Always use `{{ $escaped }}` for user-provided data.
- Run `npm run build` to verify asset bundles before release.
