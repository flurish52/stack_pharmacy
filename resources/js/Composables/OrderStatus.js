export const naira = new Intl.NumberFormat('en-NG', {
    style: 'currency',
    currency: 'NGN',
    maximumFractionDigits: 0,
})

export const formatDate = (iso) =>
    iso
        ? new Date(iso).toLocaleDateString('en-NG', {
            day: 'numeric',
            month: 'short',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
        })
        : '-'

export const statusLabel = (status) => (status ?? '').replaceAll('_', ' ')

const statusStyles = {
    pending: 'bg-gray-100 text-gray-700',
    paid: 'bg-blue-50 text-blue-700',
    processing: 'bg-amber-50 text-amber-700',
    out_for_delivery: 'bg-indigo-50 text-indigo-700',
    ready_for_pickup: 'bg-indigo-50 text-indigo-700',
    completed: 'bg-emerald-50 text-emerald-700',
    received: 'bg-emerald-50 text-emerald-700',
    cancelled: 'bg-red-50 text-red-700',
}

export const statusBadge = (status) => statusStyles[status] ?? 'bg-gray-100 text-gray-700'

export const customerName = (order) =>
    order.full_name || order.user?.name || 'Guest'
