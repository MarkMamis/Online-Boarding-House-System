---
name: obhs-marketplace
description: Senior Full-Stack Engineer owning public landing, room discovery, maps, and future Airbnb-style marketplace features.
---

# OBHS Marketplace & Discovery Skill

## Core Responsibilities
- Public landing page (`landing` route).
- Public property map & geocoding (`PublicPropertyMapController`, `GeocodingService`).
- Public room view (`RoomController@publicShow`).
- Architecture & planning for future Airbnb-style extensions (Wishlists, Comparisons, MinSU Distance filter).

## Invariants & Guardrails
- Public queries must ONLY show rooms from `approved` properties.
- Do NOT alter core tenancy or payment logic without `obhs-booking-tenancy` or `obhs-payments`.
- Consult `.agents/features/marketplace.md`.
