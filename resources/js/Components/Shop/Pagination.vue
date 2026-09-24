<script setup>
/**
 * Renders a Laravel paginator's `links` array (prev / numbered pages / next).
 * Generic enough to sit under any paginated Inertia index page, not just shop.
 */
import { router } from '@inertiajs/vue3'

defineProps({
    links: { type: Array, required: true },
})

const visit = (url) => {
    if (!url) return
    router.get(url, {}, { preserveScroll: true, preserveState: true })
}
</script>

<template>
    <nav v-if="links.length > 3" class="mt-12 flex items-center justify-center gap-1" aria-label="Pagination">
        <button
            v-for="(link, i) in links"
            :key="i"
            type="button"
            :disabled="!link.url"
            class="flex h-8 min-w-8 items-center justify-center rounded-sm px-2 text-sm transition-colors disabled:cursor-not-allowed disabled:opacity-30"
            :class="link.active
                ? 'bg-primary-dark text-white'
                : 'text-neutral-text/60 hover:bg-primary-light/40'"
            @click="visit(link.url)"
        >
            <svg v-if="link.label.includes('Previous')" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M15 18l-6-6 6-6" />
            </svg>
            <svg v-else-if="link.label.includes('Next')" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M9 18l6-6-6-6" />
            </svg>
            <span v-else>{{ link.label }}</span>
        </button>
    </nav>
</template>
