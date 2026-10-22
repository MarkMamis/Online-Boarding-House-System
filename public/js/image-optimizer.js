/**
 * OBHS Landlord Mobile Image Optimizer
 * 
 * Automatically compresses large mobile camera photos before upload
 * to ensure seamless compliance with 5 MB limits without manual compression.
 */
(function (root, factory) {
    if (typeof define === 'function' && define.amd) {
        define([], factory);
    } else if (typeof module === 'object' && module.exports) {
        module.exports = factory();
    } else {
        root.OBHSImageOptimizer = factory();
    }
}(typeof self !== 'undefined' ? self : this, function () {
    'use strict';

    const CONFIG = {
        maxSizeBytes: 5 * 1024 * 1024, // 5 MB (5120 KiB)
        maxDimension: 2000,             // Max longest edge in pixels
        initialQuality: 0.82,           // Standard photo JPEG quality
        fallbackQuality: 0.72,          // If still over 5MB on first pass
        supportedMimeTypes: ['image/jpeg', 'image/png', 'image/webp']
    };

    /**
     * Check if a file is an animated GIF
     */
    function isGif(file) {
        return (file.type && file.type === 'image/gif') || /\.gif$/i.test(file.name || '');
    }

    /**
     * Check if a file is HEIC/HEIF
     */
    function isHeic(file) {
        return (file.type && (file.type === 'image/heic' || file.type === 'image/heif')) ||
            /\.(heic|heif)$/i.test(file.name || '');
    }

    /**
     * Load image from file/blob using Image element with EXIF orientation support
     */
    function loadImage(file) {
        return new Promise((resolve, reject) => {
            const url = URL.createObjectURL(file);
            const img = new Image();
            img.onload = () => {
                URL.revokeObjectURL(url);
                resolve(img);
            };
            img.onerror = (err) => {
                URL.revokeObjectURL(url);
                reject(err);
            };
            img.src = url;
        });
    }

    /**
     * Calculate scaled dimensions preserving aspect ratio
     */
    function calculateDimensions(width, height, maxDim) {
        if (width <= maxDim && height <= maxDim) {
            return { width, height };
        }
        if (width > height) {
            return {
                width: maxDim,
                height: Math.round((height * maxDim) / width)
            };
        } else {
            return {
                width: Math.round((width * maxDim) / height),
                height: maxDim
            };
        }
    }

    /**
     * Render canvas to Blob
     */
    function canvasToBlob(canvas, mimeType, quality) {
        return new Promise((resolve, reject) => {
            if (canvas.toBlob) {
                canvas.toBlob((blob) => {
                    if (blob) resolve(blob);
                    else reject(new Error('Canvas toBlob failed'));
                }, mimeType, quality);
            } else {
                try {
                    const dataUrl = canvas.toDataURL(mimeType, quality);
                    const binStr = atob(dataUrl.split(',')[1]);
                    const len = binStr.length;
                    const arr = new Uint8Array(len);
                    for (let i = 0; i < len; i++) {
                        arr[i] = binStr.charCodeAt(i);
                    }
                    resolve(new Blob([arr], { type: mimeType }));
                } catch (e) {
                    reject(e);
                }
            }
        });
    }

    /**
     * Optimize a single File object
     * @param {File} file
     * @param {Object} options
     * @returns {Promise<{file: File, optimized: boolean, error?: string}>}
     */
    async function optimizeFile(file, options) {
        const opts = Object.assign({}, CONFIG, options);

        if (!file || !(file instanceof File || file instanceof Blob)) {
            return { file, optimized: false, error: 'Invalid file object' };
        }

        // If it's a GIF
        if (isGif(file)) {
            if (file.size > opts.maxSizeBytes) {
                return {
                    file,
                    optimized: false,
                    error: 'Your photo is too large. Please choose an image under 5 MB.'
                };
            }
            return { file, optimized: false };
        }

        // If it's HEIC/HEIF check if decodable
        if (isHeic(file)) {
            let canDecode = false;
            try {
                if (typeof createImageBitmap === 'function') {
                    const bmp = await createImageBitmap(file);
                    bmp.close();
                    canDecode = true;
                }
            } catch (e) {
                canDecode = false;
            }

            if (!canDecode) {
                return {
                    file,
                    optimized: false,
                    error: 'HEIC/HEIF photos are not directly supported by this mobile browser. Please choose a JPG, PNG, or WebP photo.'
                };
            }
        }

        // If already within 5MB and reasonable size, keep original
        if (file.size <= opts.maxSizeBytes) {
            return { file, optimized: false };
        }

        // Needs optimization
        try {
            const img = await loadImage(file);
            const { width, height } = calculateDimensions(img.width, img.height, opts.maxDimension);

            const canvas = document.createElement('canvas');
            canvas.width = width;
            canvas.height = height;
            const ctx = canvas.getContext('2d');
            if (!ctx) {
                return { file, optimized: false, error: "We couldn't prepare this photo. Please try another image." };
            }

            // Draw to canvas (strips sensitive EXIF location metadata while preserving image)
            ctx.drawImage(img, 0, 0, width, height);

            // Determine target format
            let targetMime = 'image/jpeg';
            let extension = '.jpg';
            if (file.type === 'image/webp') {
                targetMime = 'image/webp';
                extension = '.webp';
            }

            // Pass 1: standard quality
            let blob = await canvasToBlob(canvas, targetMime, opts.initialQuality);

            // Pass 2: if still over 5MB, reduce quality
            if (blob.size > opts.maxSizeBytes) {
                blob = await canvasToBlob(canvas, targetMime, opts.fallbackQuality);
            }

            // Free canvas memory
            canvas.width = 0;
            canvas.height = 0;

            if (blob.size > opts.maxSizeBytes) {
                return {
                    file,
                    optimized: false,
                    error: 'Your photo is too large. Please choose an image under 5 MB.'
                };
            }

            // Generate clean filename
            let newName = file.name || ('photo_' + Date.now() + extension);
            if (!newName.toLowerCase().endsWith(extension)) {
                newName = newName.replace(/\.[^/.]+$/, '') + extension;
            }

            const optimizedFile = new File([blob], newName, {
                type: targetMime,
                lastModified: Date.now()
            });

            return {
                file: optimizedFile,
                optimized: true,
                originalSize: file.size,
                optimizedSize: optimizedFile.size
            };
        } catch (err) {
            console.warn('OBHS Image optimization error:', err);
            // If original is <= 5MB return original, otherwise friendly error
            if (file.size <= opts.maxSizeBytes) {
                return { file, optimized: false };
            }
            return {
                file,
                optimized: false,
                error: "We couldn't prepare this photo. Please try another image."
            };
        }
    }

    /**
     * Set files on HTMLInputElement safely using DataTransfer
     */
    function setInputFiles(input, files) {
        try {
            if (typeof DataTransfer !== 'undefined') {
                const dt = new DataTransfer();
                const fileList = Array.isArray(files) ? files : [files];
                fileList.forEach(f => {
                    if (f) dt.items.add(f);
                });
                input.files = dt.files;
                return true;
            }
        } catch (e) {
            console.warn('DataTransfer not available or failed:', e);
        }
        return false;
    }

    return {
        optimizeFile,
        setInputFiles,
        isGif,
        isHeic,
        CONFIG
    };
}));
