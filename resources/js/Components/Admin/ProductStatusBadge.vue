<script setup>
import { computed } from 'vue'

const props = defineProps({
    status: { type: String, required: true },
})

const tones = {
    active: { pill: 'bg-primary/10 text-primary-dark', dot: 'bg-primary' },
    draft: { pill: 'bg-neutral-text/10 text-neutral-text/70', dot: 'bg-neutral-text/40' },
    archived: { pill: 'bg-accent/10 text-accent', dot: 'bg-accent' },
}

const tone = computed(() => tones[props.status] ?? tones.draft)
</script>

<template>
    <span
        class="inline-flex items-center gap-1.5 whitespace-nowrap rounded-full px-2.5 py-1 text-xs font-medium capitalize"
        :class="tone.pill"
    >
        <span class="h-1.5 w-1.5 rounded-full" :class="tone.dot" />
        {{ status }}
    </span>
</template>
