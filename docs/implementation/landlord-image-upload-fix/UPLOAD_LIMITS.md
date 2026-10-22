# Unified Upload Limits & Policy

## Image Size & Format Standards for Landlords

To ensure seamless mobile camera uploads while protecting server resources and keeping loading times fast for students, the following standards are now enforced across the application:

### Policy Matrix

| Scope | Field Name | Maximum Size | Allowed Formats / MIME | Count Limit | Friendly Error Message |
|---|---|---|---|---|---|
| Property Cover Photo | `image` | **5 MiB (5120 KiB)** | `jpg, jpeg, png, gif, webp` | 1 | "Your photo is too large. Please choose an image under 5 MB." |
| Room Cover Photo | `image` | **5 MiB (5120 KiB)** | `jpg, jpeg, png, webp, gif` | 1 | "Your photo is too large. Please choose an image under 5 MB." |
| Room Additional Photos | `detail_images.*` | **5 MiB (5120 KiB)** | `jpg, jpeg, png, webp, gif` | Max 10 | "One or more additional photos are too large. Please choose images under 5 MB." |
| Property AutoSave Draft | `image` | **5 MiB (5120 KiB)** | `jpg, jpeg, png, gif, webp` | 1 | "Your photo is too large. Please choose an image under 5 MB." |

### Boundary Rules
- **Exact Threshold:** Any image `<= 5120 KB` is accepted. Any image `>= 5121 KB` is rejected.
- **Array Protections:** `detail_images` enforces `max:10` to prevent oversized batch POSTs.
- **Isolated Scope:** Student ID verification, tenant documents, legal contracts, and payment receipt limits remain strictly unmodified.
