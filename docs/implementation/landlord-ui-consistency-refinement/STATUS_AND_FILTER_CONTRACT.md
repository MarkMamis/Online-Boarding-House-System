# Status Presentation & Filter Contract

## 1. Status Mappings & Canonical Values
To maintain domain integrity without exposing raw database strings or causing UI contradictions, status values are mapped to human-readable labels and color tokens.

### A. Tenancy & Residency
| Internal State | Presentation Label | Badge Styling | Meaning |
|---|---|---|---|
| Active Booking + Completed Onboarding | `Active Resident` | `bg-success-subtle text-success` | Currently living in the boarding house |
| Active Booking + Incomplete Onboarding | `Move-in In Progress` | `bg-warning-subtle text-warning-emphasis` | Approved booking undergoing onboarding |
| Past Checkout Date | `Past Stay` | `bg-secondary-subtle text-secondary` | Concluded stay |

### B. Payment Status
| Internal State | Presentation Label | Badge Styling | Meaning |
|---|---|---|---|
| `paid` | `Paid` | `bg-success-subtle text-success` | Rent is settled up to date |
| `pending` | `Pending` | `bg-warning-subtle text-warning-emphasis` | Awaiting landlord verification |
| `overdue` | `Overdue` | `bg-danger-subtle text-danger` | Due date has elapsed |

### C. Room Availability & Multi-Bed Occupancy
- Statuses: `Available` (`text-success`), `Occupied` (`text-secondary`), `Maintenance` (`text-warning-emphasis`).
- Multi-bed occupancy display:
  - If occupied beds $> 0$: `X of Y beds occupied`
  - If no occupants: `No residents yet (0 of Y beds occupied)`
  - Hydrates all active occupants from approved bookings covering the current date.

### D. Accreditation & Document Status Consistency
- **Accreditation State:** Institutional approval is governed by Admin verification of the official business permit (`business_permit_status`).
- **Safety Certificate Status:** Specific operational certificates (Fire Safety, Sanitary Permit) are monitored independently in the Required Documents Checklist.
- Having a missing safety certificate does not invalidate institutional accreditation unless revoked by the MinSU Admin. The UI accurately reflects both states independently without showing contradictory warnings.

---

## 2. Client-Side Filter Contract
All refined management pages implement zero-latency client-side filtering via `data-*` attributes:
- `data-search`: Lowercase normalized string composed of student name, property, room number, email, and contact info.
- `data-property`: Property ID string or `all`.
- `data-status` / `data-residency` / `data-payment`: Canonical lowercase status string or `all`.
- Dynamically hides `.d-none` and toggles `.landlord-empty-state` with no page reload.
