<script setup>
import { ref } from 'vue'

const props = defineProps({
    order: { type: Object, required: true },
})

const statusStyles = {
    paid: 'bg-primary-light text-primary-dark',
    pending: 'bg-amber-100 text-amber-700',
    processing: 'bg-blue-100 text-blue-700',
    ready_for_pickup: 'bg-blue-100 text-blue-700',
    out_for_delivery: 'bg-blue-100 text-blue-700',
    completed: 'bg-green-100 text-green-700',
    cancelled: 'bg-red-100 text-red-700',
    payment_failed: 'bg-red-100 text-red-700',
}

const statusLabels = {
    ready_for_pickup: 'Ready for pickup',
    out_for_delivery: 'Out for delivery',
    payment_failed: 'Payment failed',
}

function statusClass(status) {
    return statusStyles[status] || 'bg-neutral-bg text-neutral-text/70'
}

function statusLabel(status) {
    return statusLabels[status] || status
}

function formatDate(value) {
    if (!value) return '—'
    return new Date(value).toLocaleDateString('en-NG', { day: 'numeric', month: 'short', year: 'numeric' })
}

const latestReference = props.order.payments?.at(-1)?.reference ?? null

const copiedField = ref(null)
async function copy(value, field) {
    try {
        await navigator.clipboard.writeText(value)
        copiedField.value = field
        setTimeout(() => {
            if (copiedField.value === field) copiedField.value = null
        }, 1500)
    } catch {
        // Clipboard API unavailable — silently skip, nothing to fall back to safely.
    }
}
</script>

<template>
    <div>
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-2">
                <h2 class="font-heading text-lg font-semibold text-neutral-text">Order #{{ order.id }}</h2>
                <button
                    type="button"
                    title="Copy order number"
                    class="rounded p-1 text-neutral-text/40 transition-colors hover:bg-neutral-bg hover:text-neutral-text/70"
                    @click="copy(String(order.id), 'order')"
                >
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <rect x="9" y="9" width="13" height="13" rx="2" />
                        <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1" />
                    </svg>
                </button>
                <span v-if="copiedField === 'order'" class="text-xs text-primary-dark">Copied</span>
            </div>

            <span class="rounded-full px-2.5 py-0.5 text-xs font-medium capitalize" :class="statusClass(order.status)">
                {{ statusLabel(order.status) }}
            </span>
        </div>

        <p class="mt-1 text-sm text-neutral-text/60">Placed {{ formatDate(order.created_at) }}</p>

        <div v-if="latestReference" class="mt-1.5 flex items-center gap-1.5">
            <span class="text-xs text-neutral-text/50">Payment ref: {{ latestReference }}</span>
            <button
                type="button"
                title="Copy payment reference"
                class="rounded p-0.5 text-neutral-text/40 transition-colors hover:bg-neutral-bg hover:text-neutral-text/70"
                @click="copy(latestReference, 'reference')"
            >
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <rect x="9" y="9" width="13" height="13" rx="2" />
                    <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1" />
                </svg>
            </button>
            <span v-if="copiedField === 'reference'" class="text-xs text-primary-dark">Copied</span>
        </div>
    </div>
</template>
