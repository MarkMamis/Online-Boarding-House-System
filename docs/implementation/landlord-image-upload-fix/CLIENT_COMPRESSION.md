# Client-Side Image Compression Architecture

## Overview
The `OBHSImageOptimizer` module (`public/js/image-optimizer.js` & `resources/js/image-optimizer.js`) automatically resizes and compresses high-resolution mobile camera photos in the landlord's browser/webview prior to payload transmission.

---

## Workflow Algorithm

```
User selects image in file input
       │
       ▼
Is file <= 5 MB? ───────── YES ─────────► Retain original file, update preview, proceed
       │
       NO
       ▼
Is file GIF? ───────────── YES ─────────► Reject with friendly message if > 5MB
       │
       NO
       ▼
Is file HEIC/HEIF? ─────── YES ─────────► Test canvas/createImageBitmap decode:
       │                                  If fails -> "HEIC/HEIF photos are not directly supported..."
       NO
       ▼
Show inline "Preparing photo..." state
       │
       ▼
Load image into memory & compute aspect-ratio preserving dimensions:
Max longest dimension: 2000px (no upscaling)
       │
       ▼
Draw to off-screen HTML5 Canvas (strips unnecessary EXIF location metadata)
       │
       ▼
Canvas to Blob (image/jpeg, initial quality 0.82)
       │
Is Blob still > 5 MB? ──── YES ─────────► Pass 2 re-compression with quality 0.72
       │
       NO
       ▼
Convert Blob to File object preserving name with .jpg/.webp extension
       │
       ▼
Inject into input.files using DataTransfer API (or fallback reference)
       │
       ▼
Hide "Preparing photo...", render optimized thumbnail preview, and trigger auto-save if in Draft mode
```

---

## Mobile Performance Safeguards
1. **Low-End Android Optimization:** Decodes images sequentially (`for ... of` loop) when multiple photos are selected in `detail_images[]`.
2. **Memory Leak Prevention:** Explicitly calls `URL.revokeObjectURL(url)` immediately upon image load.
3. **Canvas Teardown:** Explicitly resets `canvas.width = 0` and `canvas.height = 0` to release GPU texture buffers.
4. **Messenger WebView Compatibility:** Uses standard Canvas 2D context and `DataTransfer` supported across Android System WebView (Chrome 62+) and mobile Safari.
