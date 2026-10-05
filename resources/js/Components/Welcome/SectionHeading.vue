<script setup>
/**
 * One heading treatment for every home section, so the page reads as a
 * single document rather than four unrelated blocks. Optional trailing
 * slot holds a link that sits on the heading's baseline on desktop.
 */
defineProps({
    title: { type: String, required: true },
    lead: { type: String, default: '' },
})
</script>

<template>
    <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
        <div class="max-w-xl">
            <h2 class="font-heading text-[1.75rem] font-semibold leading-tight tracking-tight text-neutral-text">
                {{ title }}
            </h2>
            <p v-if="lead" class="mt-3 text-[0.9375rem] leading-relaxed text-neutral-text/65">
                {{ lead }}
            </p>
        </div>

        <div v-if="$slots.action" class="shrink-0">
            <slot name="action" />
        </div>
    </div>
</template>
