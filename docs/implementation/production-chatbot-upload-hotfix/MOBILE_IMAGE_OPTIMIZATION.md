# Mobile Image Optimization & Android WebView Compatibility

## Module Structure
- **Global UMD Script**: `public/js/image-optimizer.js` (loaded directly on Blade upload pages).
- **Vite ESM Module**: `resources/js/image-optimizer.js` (bundled via `resources/js/app.js`).

---

## Configuration & Automatic Trigger Threshold
```javascript
const CONFIG = {
    maxSizeBytes: 5 * 1024 * 1024,             // 5 MB backend upper ceiling
    compressThresholdBytes: 1.8 * 1024 * 1024, // 1.8 MB trigger for automatic client compression
    maxDimension: 2000,                         // Longest edge bounded to 2000 pixels
    initialQuality: 0.82,                       // High photographic quality
    fallbackQuality: 0.72,                      // Secondary pass if still above ceiling
    supportedMimeTypes: ['image/jpeg', 'image/png', 'image/webp']
};
```

### Compression Logic
1. **Under 1.8 MB**:
   Photos under 1.8 MB are preserved exactly as captured without modification. This prevents re-compressing small images and speeds up form interaction.
2. **Above 1.8 MB (Camera Photos)**:
   Modern Android / iOS photos typically range from 2.5 MB to 8 MB at 4000x3000px resolution.
   - The photo is loaded into memory via `createObjectURL`.
   - Dimensions are scaled to fit within a `2000x2000` bounding box preserving aspect ratio.
   - Renders to an offscreen HTML5 `canvas`.
   - Strips sensitive location EXIF tags while maintaining visual fidelity.
   - Converts to JPEG (or WebP if input was WebP) at `0.82` quality.
   - Average output file size: **450 KB – 950 KB** (a ~70-85% size reduction).
   - If still over 5 MB, second pass runs at `0.72` quality.
   - Canvas is immediately cleared (`canvas.width = 0; canvas.height = 0;`) to reclaim GPU/RAM on memory-constrained mobile devices.

---

## Android WebView & Messenger Compatibility

1. **In-App Browser Sandbox Limitations**:
   Facebook Messenger's in-app browser runs within the Android System WebView.
   - **`DataTransfer` Support**: Supported in modern Chromium WebViews (Chrome 53+). `OBHSImageOptimizer.setInputFiles(input, files)` checks `typeof DataTransfer !== 'undefined'` and verifies `input.files` was mutated.
   - **Graceful Fallback**: If `DataTransfer` is restricted by a customized WebView, the original file is submitted, and our updated backend validator (`max:5120`) accepts images up to 5 MB directly.
   - **HEIC / HEIF Handling**: If an iPhone user accesses via an Android bridge or uploads an unsupported HEIC format without browser decoding, a friendly error is displayed:
     `"HEIC/HEIF photos are not directly supported by this mobile browser. Please choose a JPG, PNG, or WebP photo."`
   - **Animated GIF Preservation**: Animated GIFs are never fed through canvas (which would destroy animation frames). They are validated against the 5 MB ceiling directly.

---

## Guided Onboarding Form Preservation
1. **Form Navigation Independence**:
   Selected `File` objects are retained in memory during transitions between questions (Details -> Pricing -> Amenities -> Photos -> Review).
2. **Live Image Preview**:
   Review screens display the optimized photo preview using `URL.createObjectURL(file)` before final submission.
3. **Draft Compatibility**:
   When auto-save runs, the optimized file or its existing database path is safely referenced without clearing user input.
