# Admin Verification & Monitoring Experience (`ADMIN_VERIFICATION_UX.md`)

## Key Design Principles
1. **Preserved Administrative Boundary:**
   - Admin routes (`admin.onboardings.index`, `admin.onboardings.show`) are strictly dedicated to monitoring, compliance inspection, and verification audit trails.
   - Admin does NOT have approval or rejection buttons for private tenant contracts or move-in payments; those decisions belong strictly to landlords.
2. **Design System Alignment:**
   - Unified with the OBHS Admin Portal design system (`admin-page-shell`, `admin-table-card`, `x-admin.page-header`, `x-admin.status-badge`).
3. **Comprehensive Read-Only Inspection:**
   - Fixed broken route name references (`admin.onboardings.index`).
   - Added full Contract & Payment Information block displaying:
     - Contract signed timestamp
     - Move-in payment status (Confirmed, Under Review, Pending)
     - Payment details (Method, reference number, amount)
     - Inline links to View Contract, Download PDF, and View Payment Proof receipt.
