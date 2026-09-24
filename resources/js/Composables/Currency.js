const naira = new Intl.NumberFormat('en-NG', {
    style: 'currency',
    currency: 'NGN',
    maximumFractionDigits: 0,
})

/**
 * Format a price for display. Accepts a number (naira) or a
 * pre-formatted string from the backend, which is passed through
 * untouched so nothing double-formats.
 */
export function formatNaira(value) {
    if (value === null || value === undefined || value === '') return ''

    const number = typeof value === 'number' ? value : Number(String(value).replace(/[^\d.-]/g, ''))

    return Number.isFinite(number) ? naira.format(number) : String(value)
}
