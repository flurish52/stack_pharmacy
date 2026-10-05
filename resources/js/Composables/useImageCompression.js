/**
 * Compress an image file client-side using canvas, iterating quality
 * downward until it's under maxSizeMB (or we hit minQuality).
 */
export async function compressImage(file, { maxSizeMB = 1, maxDimension = 1920, minQuality = 0.5 } = {}) {
    // Skip non-images and anything already small enough
    if (!file.type.startsWith('image/') || file.size <= maxSizeMB * 1024 * 1024) {
        return file
    }

    const img = await loadImage(file)
    const canvas = document.createElement('canvas')

    let { width, height } = img
    if (width > maxDimension || height > maxDimension) {
        const scale = maxDimension / Math.max(width, height)
        width = Math.round(width * scale)
        height = Math.round(height * scale)
    }
    canvas.width = width
    canvas.height = height

    const ctx = canvas.getContext('2d')
    ctx.drawImage(img, 0, 0, width, height)
    URL.revokeObjectURL(img.src)

    // PNGs with transparency should stay PNG; everything else -> JPEG for better compression
    const outputType = file.type === 'image/png' && hasTransparency(ctx, width, height) ? 'image/png' : 'image/jpeg'

    let quality = 0.9
    let blob = await canvasToBlob(canvas, outputType, quality)

    while (blob.size > maxSizeMB * 1024 * 1024 && quality > minQuality) {
        quality -= 0.1
        blob = await canvasToBlob(canvas, outputType, quality)
    }

    // If PNG still too big even at max quality reduction (PNG quality arg is ignored by most browsers),
    // fall back to JPEG as a last resort.
    if (outputType === 'image/png' && blob.size > maxSizeMB * 1024 * 1024) {
        blob = await canvasToBlob(canvas, 'image/jpeg', 0.8)
    }

    return new File([blob], renameExt(file.name, blob.type), { type: blob.type })
}

function loadImage(file) {
    return new Promise((resolve, reject) => {
        const img = new Image()
        img.onload = () => resolve(img)
        img.onerror = reject
        img.src = URL.createObjectURL(file)
    })
}

function canvasToBlob(canvas, type, quality) {
    return new Promise((resolve) => canvas.toBlob(resolve, type, quality))
}

function hasTransparency(ctx, width, height) {
    const { data } = ctx.getImageData(0, 0, width, height)
    for (let i = 3; i < data.length; i += 4) {
        if (data[i] < 255) return true
    }
    return false
}

function renameExt(name, mimeType) {
    const ext = mimeType === 'image/png' ? 'png' : 'jpg'
    return name.replace(/\.[^.]+$/, '') + '.' + ext
}
