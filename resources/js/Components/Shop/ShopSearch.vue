<script setup>
const model = defineModel({ type: String, default: '' })

defineProps({
    placeholder: { type: String, default: 'Search products' },
})

const clear = () => {
    model.value = ''
}
</script>

<template>
    <div class="relative w-full sm:max-w-xs">
        <svg
            width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
            stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"
            class="pointer-events-none absolute left-2.5 top-1/2 -translate-y-1/2 text-neutral-text/40"
        >
            <circle cx="11" cy="11" r="8" />
            <path d="m21 21-4.3-4.3" />
        </svg>
        <input
            v-model="model"
            type="text"
            :placeholder="placeholder"
            class="w-full rounded-md border border-neutral-text/15 bg-white py-1.5 pl-8 pr-8 text-xs text-neutral-text placeholder:text-neutral-text/40 focus:border-primary-dark focus:outline-none focus:ring-1 focus:ring-primary-dark/30"
        />
        <button
            v-if="model"
            type="button"
            @click="clear"
            class="absolute right-2.5 top-1/2 -translate-y-1/2 text-neutral-text/40 hover:text-neutral-text"
        >
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M18 6 6 18M6 6l12 12" />
            </svg>
        </button>
    </div>
</template>
