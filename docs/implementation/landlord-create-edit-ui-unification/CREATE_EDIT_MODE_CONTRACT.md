# Create vs. Edit Mode Contract

## 1. Parameters & Configuration Contract
| Parameter | Create Mode | Edit Mode |
|-----------|-------------|-----------|
| `mode` | `'create'` | `'edit'` |
| `isEdit` | `false` | `true` |
| Form Action | `route('...store')` (POST) | `route('...update', $id)` (PUT) |
| Stepper | Completely removed | Completely removed |
| 3-Card Header Stack | None | None |
| Header Context | "Tell us about..." / "Adding Room To" | "Editing Room" / Property Title & Address |
| Auto-save (Drafts) | Active (Properties) | Strictly Disabled |
| Submit Button | "Submit for Review" / "Add Room" | "Save Changes" |
| Double Submit Guard | Displays "Saving..." spinner | Displays "Saving..." spinner |
| Photos Requirement | Required or initial draft preview | Existing preview kept; upload optional |

## 2. Precedence Protocol
All input controls evaluate values in strict priority order:
1. `old('field')` (reflashed validation errors)
2. `$model->field` (existing database record)
3. Default value (`'available'`, `1`, etc.)
