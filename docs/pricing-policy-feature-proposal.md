# OBHS — Boarding House Pricing Policy Feature Proposal
**Date:** March 1, 2026  
**Status:** For Review — Not Yet Implemented  
**Prepared by:** GitHub Copilot  

---

## Overview

This document outlines suggested improvements to the OBHS (Online Boarding House System) payment and pricing system, based on standard boarding house practices in the Philippines. The goal is to help landlords configure realistic pricing policies and give students clear, transparent information before booking.

---

## 1. Philippine Boarding House Pricing — Background

### Legal Basis
- **Republic Act No. 877 (Boarding House Act of 1952)**, amended by RA 913
- Governs operation and sanitation standards of boarding houses
- **Does NOT regulate pricing, deposit amounts, or payment schedules** — these are left to the contract between landlord and tenant
- Boarding houses must be licensed by the LGU (barangay/city permit)

### Standard Move-in Payment in the Philippines

| Payment | Amount | Purpose |
|---------|--------|---------|
| Advance | 1 month's rent | Pays the first month of stay |
| Security Deposit | 1–2 months' rent | Held by landlord; returned on move-out minus deductions |
| **Total Move-in Cost** | **2–3× monthly rent** | Paid before keys are handed |

**Key rules (by convention):**
- Advance is NOT refundable — it is consumed as your first month's rent
- Deposit must be returned within ~30 days of move-out
- Landlord may deduct from deposit: unpaid rent, unpaid utilities, physical damages exceeding normal wear
- Landlord may NOT deduct for: repainting, normal floor wear, minor scuffs

---

## 2. Bed Spacer Pricing Models

The current system uses a single `price` field per room (whole room price). In practice, Philippine boarding houses operate under three distinct models:

---

### Model A — Fixed Per-Bed Rate *(Most Common)*

> The landlord sets a price **per bed slot**. Each tenant pays that fixed amount regardless of how many beds are occupied.

**Example:**
- Room capacity: 5 beds
- Per-bed price: ₱1,000/month
- Whether 1 or 5 beds are taken, each tenant pays ₱1,000

**Pros:**
- Simplest to manage
- Student always knows their fixed monthly cost
- Landlord absorbs vacancy risk

**Cons:**
- Landlord loses money on empty beds

---

### Model B — Dynamic Split (Cost-Sharing)

> The landlord sets a **fixed room price**. Active tenants split it equally based on current occupancy.

**Example:**
- Room price: ₱5,000/month
- Month 1: 5 tenants → ₱1,000 each
- Month 2: 3 tenants → ₱1,667 each
- Month 3: 1 tenant → ₱5,000 (all by themselves)

**Pros:**
- Landlord always collects the full room price
- Cheap for students when the room is full

**Cons:**
- Student's monthly cost is unpredictable
- Possible conflict when a roommate leaves
- Difficult to collect — if a tenant refuses to pay the higher share, who covers it?

---

### Model C — Guaranteed Minimum + Capped Split *(Hybrid)*

> Per-bed rate has a **floor price**; when under full capacity the cost is split but capped at a maximum per head.

**Example:**
- Room price: ₱5,000 | Capacity: 5
- Normal (full): ₱1,000/bed
- 3 tenants: ₱5,000 ÷ 3 = ₱1,667 → **capped at ₱1,400**
- Landlord absorbs: ₱5,000 − (₱1,400 × 3) = ₱800 gap

**Pros:**
- Protects students from extreme price spikes
- Landlord still collects more than the flat per-bed rate during low occupancy

**Cons:**
- More complex to compute
- Requires tracking current occupant count

---

## 3. Other Common PH Boarding House Policies

| Policy | Typical Value | Notes |
|--------|--------------|-------|
| Payment due date | 1st–5th of the month | Some landlords allow any date matching move-in date |
| Grace period | 3–7 days | No penalty within grace period |
| Late payment fee | ₱50–₱200/day | Charged after grace period |
| Lock-in period | 1–6 months | If tenant leaves early, forfeits deposit |
| Notice to vacate | 30 days written notice | Standard in both directions |
| Curfew | 10PM–11PM | Very common in student BHs |
| Visitor policy | Day visitors only | Overnight guests usually require extra charge |
| Appliance surcharge | ₱150–₱500/month | For air conditioners or personal refrigerators |

---

## 4. Utility Billing Models

| Model | How It Works | Common In |
|-------|-------------|-----------|
| **All-in / Inclusive** | Fixed monthly rate covers electric + water | Budget BHs, bed spacers |
| **Shared meter** | Total building/floor bill ÷ number of rooms | Mid-range BHs |
| **Sub-metered** | Each room has its own meter | Higher-end BHs |
| **Per-head metered** | Total bill ÷ number of occupants | Student BH / bed spacers |

---

## 5. Proposed System Features

The following three features are recommended in order of priority and implementation effort.

---

### Feature 1 — Pricing Type & Move-in Breakdown
**Priority:** High | **Impact:** High | **Effort:** Medium

#### What it adds
- Landlord can choose **pricing type** when creating/editing a room
- Landlord sets **deposit months** (1 or 2) and **advance months** (1 or 2)
- On the student room detail page and booking confirmation, a **move-in cost breakdown** is shown
- Browse cards show **"₱X,XXX/bed"** instead of whole room price for bed spacer rooms

#### Database Changes — New migration: `add_pricing_fields_to_rooms_table`

```php
$table->enum('pricing_type', ['whole_room', 'per_bed_fixed', 'per_bed_dynamic'])
      ->default('whole_room');
$table->unsignedTinyInteger('deposit_months')->default(1);   // 1 or 2
$table->unsignedTinyInteger('advance_months')->default(1);   // 1 or 2
$table->unsignedTinyInteger('lock_in_months')->default(0);   // 0–6
```

#### UI Changes

**Landlord room form** — new "Pricing Policy" section:
```
Pricing Type: [ Whole Room ▼ ]    (options: Whole Room / Per-Bed Fixed / Per-Bed Dynamic)
Deposit:      [ 1 month  ▼ ]
Advance:      [ 1 month  ▼ ]
Lock-in:      [ 0 months ▼ ]
```

**Student room detail page** — booking card shows:
```
₱3,500 / month

─────────────────────────────
Move-in Cost Breakdown
─────────────────────────────
1 Month Advance:     ₱3,500
1 Month Deposit:     ₱3,500
─────────────────────────────
Total Due Move-in:   ₱7,000
Monthly After:       ₱3,500
```

**Student browse cards** — shows per-bed price if `per_bed_fixed`:
```
₱1,000/bed  (5-bed room)
```

#### Files to Modify
- `database/migrations/` — new migration file
- `app/Models/Room.php` — add fields to `$fillable`, accessor for `per_head_price`
- `resources/views/landlord/rooms/create.blade.php` — add pricing section
- `resources/views/landlord/rooms/edit.blade.php` — add pricing section
- `resources/views/student/rooms/show.blade.php` — add breakdown to book card
- `resources/views/student/dashboard.blade.php` — update browse card price display

---

### Feature 2 — Payment Due Date & Late Fee Policy
**Priority:** Medium | **Impact:** Medium | **Effort:** Low-Medium

#### What it adds
- Landlord sets a **monthly due date** (day of month, e.g. 5)
- Landlord sets **grace period** (days, e.g. 5)
- Landlord sets **late fee per day** (e.g. ₱100)
- These display on the student room detail page as a "Payment Policy" info block
- (Optional later) System can auto-flag overdue payments based on due date

#### Database Changes — New migration: `add_payment_policy_to_properties_table`

```php
$table->unsignedTinyInteger('payment_due_day')->default(5);     // day of month
$table->unsignedTinyInteger('grace_period_days')->default(5);   // days after due
$table->decimal('late_fee_per_day', 8, 2)->default(0);          // PHP per day
```

Why on `properties` not `rooms`? — Most landlords apply the same policy to all rooms in a property. Can be moved to `rooms` if variable policy is needed.

#### UI Changes

**Property edit page** — new "Payment Policy" section:
```
Payment due on the: [ 5th ▼ ] of each month
Grace period:       [ 5   ] days
Late fee:           ₱ [ 100 ] per day after grace
```

**Student room detail page** — new info block:
```
📅 Payment Policy
  Due every 5th of the month
  5-day grace period
  ₱100/day late fee after grace period
```

#### Files to Modify
- `database/migrations/` — new migration
- `app/Models/Property.php` — add fields to `$fillable`
- `resources/views/landlord/properties/edit.blade.php` — add payment policy section
- `resources/views/student/rooms/show.blade.php` — add policy block

---

### Feature 3 — Utility Inclusion Flag
**Priority:** Low-Medium | **Impact:** Medium | **Effort:** Low

#### What it adds
- Landlord marks whether utilities are included in the room price
- If not included, landlord can add a short note (e.g. "Electric billed separately ~₱300/mo per head")
- Shown as a badge on room browse cards and room detail page

#### Database Changes — New migration: `add_utilities_to_rooms_table`

```php
$table->boolean('utilities_included')->default(false);
$table->string('utility_note')->nullable();   // e.g. "Elec billed at ~₱300/head"
```

#### UI Changes

**Browse card** — badge:
```
[ ✓ All-in ]    or    [ Utilities Extra ]
```

**Room detail page** — inside Inclusions section:
```
⚡ Electricity included
💧 Water included
```
or
```
⚡ Utilities NOT included — Electric billed separately (~₱300/mo per head)
```

#### Files to Modify
- `database/migrations/` — new migration
- `app/Models/Room.php` — add fields to `$fillable`
- `resources/views/landlord/rooms/create.blade.php` — add utility toggle
- `resources/views/landlord/rooms/edit.blade.php` — add utility toggle
- `resources/views/student/dashboard.blade.php` — add utility badge on browse card
- `resources/views/student/rooms/show.blade.php` — add utility info to inclusions block

---

## 6. What Is Already In The System

For reference, here is the current state and what each proposed feature builds on:

| Area | Current State |
|------|--------------|
| `rooms.price` | Single price field — whole room price |
| `rooms.capacity` | Exists — number of pax |
| `rooms.status` | `available / occupied / maintenance` |
| `bookings.payment_status` | `pending / paid` — manual toggle by landlord |
| `bookings.payment_date` | Timestamp when marked paid |
| Landlord payments page | `/landlord/payments` — lists approved bookings, mark paid/pending |
| No pricing type | No `per_bed_fixed` / `per_bed_dynamic` differentiation |
| No deposit/advance fields | Move-in breakdown not shown anywhere |
| No utility flag | Utilities shown only via `inclusions` text array |
| No late fee policy | No due date, no grace period, no late fee stored |

---

## 7. Implementation Order Recommendation

If the decision is to implement all three features, recommended order:

```
Phase 1:  Feature 3 (Utility flag)         — 1–2 hours, low risk, visible improvement
Phase 2:  Feature 2 (Payment policy)       — 2–3 hours, adds credibility to listings
Phase 3:  Feature 1 (Pricing type/move-in) — 4–6 hours, highest value for students
```

Each phase is independent — they can be done one at a time without breaking anything already working.

---

## 8. Out of Scope (Not Recommended For Now)

The following are common in PH boarding houses but would add significant complexity and are not recommended for the current capstone scope:

| Feature | Reason to Skip |
|---------|---------------|
| Dynamic split billing (Model B) — auto compute | Requires real-time occupancy tracking + monthly billing generation |
| Online payment gateway (GCash, PayMaya) | Requires merchant account, API keys, and webhook handling |
| Receipt / invoice PDF generation | Separate library needed (DomPDF), out of scope |
| Recurring monthly payment schedule | Full billing engine — major feature |
| Partial payment tracking | Complex ledger logic |

---

*End of document. Review and decide which features to implement.*
