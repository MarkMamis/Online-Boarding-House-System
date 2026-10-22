/**
 * OBHS Landlord Mobile Image Optimizer (ESM Module)
 */
export const CONFIG = {
    maxSizeBytes: 5 * 1024 * 1024, // 5 MB
    maxDimension: 2000,
    initialQuality: 0.82,
    fallbackQuality: 0.72,
    supportedMimeTypes: ['image/jpeg', 'image/png', 'image/webp']
};

export function isGif(file) {
    return (file.type && file.type === 'image/gif') || /\.gif$/i.test(file.name || '');
}

export function isHeic(file) {
    return (file.type && (file.type === 'image/heic' || file.type === 'image/heif')) ||
        /\.(heic|heif)$/i.test(file.name || '');
}

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

export async function optimizeFile(file, options) {
    const opts = Object.assign({}, CONFIG, options);

    if (!file || !(file instanceof File || file instanceof Blob)) {
        return { file, optimized: false, error: 'Invalid file object' };
    }

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

    if (file.size <= opts.maxSizeBytes) {
        return { file, optimized: false };
    }

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

        ctx.drawImage(img, 0, 0, width, height);

        let targetMime = 'image/jpeg';
        let extension = '.jpg';
        if (file.type === 'image/webp') {
            targetMime = 'image/webp';
            extension = '.webp';
        }

        let blob = await canvasToBlob(canvas, targetMime, opts.initialQuality);

        if (blob.size > opts.maxSizeBytes) {
            blob = await canvasToBlob(canvas, targetMime, opts.fallbackQuality);
        }

        canvas.width = 0;
        canvas.height = 0;

        if (blob.size > opts.maxSizeBytes) {
            return {
                file,
                optimized: false,
                error: 'Your photo is too large. Please choose an image under 5 MB.'
            };
        }

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

export function setInputFiles(input, files) {
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

const OBHSImageOptimizer = {
    optimizeFile,
    setInputFiles,
    isGif,
    isHeic,
    CONFIG
};

if (typeof window !== 'undefined') {
    window.OBHSImageOptimizer = window.OBHSImageOptimizer || OBHSImageOptimizer;
}

export default OBHSImageOptimizer;
