# Shared Components Reference (`SHARED_COMPONENTS.md`)

## Reused Primitives & Components

1. **Setup Shell (`.setup-shell`):**
   - Derived from `resources/views/student/setup/index.blade.php`.
   - Off-white card (`#ffffff`), subtle line border (`rgba(226, 232, 240, 0.9)`), gentle shadow (`0 20px 45px -12px rgba(15, 23, 42, 0.08)`), rounded border radius (`1.5rem`).
2. **Typography Tokens:**
   - Kicker (`.question-kicker`): 0.75rem, font-weight 800, letter-spacing 0.12em, uppercase, emerald (`#047857`).
   - Title (`.question-title`): Font family 'Bricolage Grotesque', 'Manrope', font-weight 800, clamp(1.4rem, 2.6vw, 1.8rem), color `#0f172a`.
   - Subtitle (`.question-subtitle`): Font size 0.92rem, color `#64748b`, line-height 1.5.
3. **Primary Action Button (`.btn-brand`):**
   - Forest-green to emerald gradient (`linear-gradient(125deg, #10b981 0%, #047857 100%)`).
   - Fully curved pill radius (`999px`), glowing shadow, hover translation (`translateY(-2px)`).
4. **Contract Document Component (`<x-onboarding.contract-document :onboarding="$onboarding" />`):**
   - Full residential lease agreement rendering with boarding request snapshot, parties, terms, payment obligations, house rules, and signature placeholders.
5. **Dropzone & Upload Control:**
   - Interactive dashed dropzone supporting drag-and-drop file additions, automatic file tag badges displaying file name and size in megabytes.
6. **Canvas E-Signature Component:**
   - Integrated touch/mouse HTML5 canvas with real-time status display and clear/redraw controls.
