<script setup>
import { computed } from 'vue'
import { statusLabel } from '@/Composables/OrderStatus.js'

const props = defineProps({
    status: { type: String, required: true },
})

// Palette-only tones. Adjust the keys if your status slugs differ.
const tones = {
    pending: { pill: 'bg-accent/10 text-accent', dot: 'bg-accent' },
    paid: { pill: 'bg-secondary text-primary-dark', dot: 'bg-primary-dark' },
    processing: { pill: 'bg-secondary text-primary-dark', dot: 'bg-primary-dark' },
    out_for_delivery: { pill: 'bg-primary/10 text-primary-dark', dot: 'bg-primary' },
    ready_for_pickup: { pill: 'bg-primary/10 text-primary-dark', dot: 'bg-primary' },
    completed: { pill: 'bg-primary text-white', dot: 'bg-white' },
    cancelled: { pill: 'bg-neutral-text/10 text-neutral-text/70', dot: 'bg-neutral-text/40' },
    failed: { pill: 'bg-accent-light/15 text-accent', dot: 'bg-accent-light' },
}

const tone = computed(() => tones[props.status] ?? { pill: 'bg-neutral-text/10 text-neutral-text/70', dot: 'bg-neutral-text/40' })
</script>

<template>
    <span
        class="inline-flex items-center gap-1.5 whitespace-nowrap rounded-full px-2.5 py-1 text-xs font-medium"
        :class="tone.pill"
    >
        <span class="h-1.5 w-1.5 rounded-full" :class="tone.dot" />
        {{ statusLabel(status) }}
    </span>
</template>
