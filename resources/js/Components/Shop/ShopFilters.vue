<script setup>
/**
 * Filter panel for the shop grid.
 * Owns navigation itself — filtering is a server-side concern handled by
 * ShopController, so picking a filter just re-requests the page with an
 * updated query string.
 *
 * On mobile this renders as a slide-in drawer (triggered by the "Filters"
 * button); on desktop (lg+) the same <aside> sits static in the layout.
 * Price range has been removed for now — a two-way slider will replace it
 * later rather than the old min/max number boxes.
 */
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
    categories: { type: Array, required: true },
    filters: { type: Object, required: true },
})

const open = ref(false)

const hasActiveFilters = computed(() => Boolean(props.filters.category))
const activeCount = computed(() => (props.filters.category ? 1 : 0))

function go(params) {
    router.get(route('shop.index'), { ...props.filters, ...params }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    })
}

function toggleCategory(slug) {
    go({ category: props.filters.category === slug ? undefined : slug })
    open.value = false
}

function clearAll() {
    router.get(route('shop.index'), {}, { preserveScroll: true })
    open.value = false
}

// Lock background scroll only while the mobile drawer is actually open —
// on desktop `open` never becomes true via the (hidden) trigger, so this
// never fires there.
watch(open, (isOpen) => {
    document.body.style.overflow = isOpen ? 'hidden' : ''
})

function onKeydown(event) {
    if (event.key === 'Escape') open.value = false
}

onMounted(() => window.addEventListener('keydown', onKeydown))
onBeforeUnmount(() => {
    window.removeEventListener('keydown', onKeydown)
    document.body.style.overflow = ''
})
</script>

<template>
    <!-- Mobile trigger — sticky bar so it stays reachable while the grid scrolls.
         The -mx-6/px-6 pair matches this app's page gutter (see Cart/Checkout,
         both `max-w-5xl px-6`) so the bar reaches full-bleed; adjust both
         numbers together if your shop page uses a different container padding. -->
    <div class="sticky top-0 z-30 -mx-6 mb-4 bg-neutral-bg/95 px-6 py-3 backdrop-blur-sm lg:hidden">
        <button
            type="button"
            class="flex items-center gap-2 rounded-sm border border-neutral-text/15 bg-white px-4 py-2.5 text-sm font-medium text-neutral-text transition-colors hover:border-neutral-text/30 hover:bg-primary-light/25"
            @click="open = true"
        >
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true">
                <line x1="4" y1="6" x2="20" y2="6" />
                <line x1="4" y1="12" x2="14" y2="12" />
                <line x1="4" y1="18" x2="10" y2="18" />
                <circle cx="17" cy="12" r="1.6" fill="currentColor" stroke="none" />
                <circle cx="13" cy="18" r="1.6" fill="currentColor" stroke="none" />
            </svg>
            Filters
            <span
                v-if="activeCount"
                class="ml-0.5 flex h-5 w-5 items-center justify-center rounded-full bg-primary-dark text-[11px] font-semibold text-white"
            >
                {{ activeCount }}
            </span>
        </button>
    </div>

    <!-- Backdrop (mobile only) -->
    <Transition name="fade">
        <div v-if="open" class="fixed inset-0 z-40 bg-neutral-text/30 lg:hidden" @click="open = false" />
    </Transition>

    <aside
        class="fixed inset-y-0 left-0 z-50 w-80 max-w-[85vw] transform overflow-y-auto bg-white p-6 shadow-[12px_0_32px_-16px_rgba(0,0,0,0.2)] transition-transform duration-300 ease-out lg:sticky lg:top-6 lg:z-auto lg:w-56 lg:max-w-none lg:max-h-[calc(100vh-3rem)] lg:translate-x-0 lg:self-start lg:overflow-y-auto lg:rounded-sm lg:border lg:border-neutral-text/10 lg:bg-white lg:p-5 lg:shadow-none lg:transition-none"
        :class="open ? 'translate-x-0' : '-translate-x-full'"
    >
        <div class="mb-5 flex items-center justify-between lg:hidden">
            <h2 class="font-heading text-base font-semibold text-neutral-text">Filters</h2>
            <button
                type="button"
                class="p-1 text-neutral-text/40 transition-colors hover:text-neutral-text"
                aria-label="Close filters"
                @click="open = false"
            >
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                    <line x1="18" y1="6" x2="6" y2="18" />
                    <line x1="6" y1="6" x2="18" y2="18" />
                </svg>
            </button>
        </div>

        <div class="flex items-center justify-between">
            <h3 class="text-xs font-medium text-neutral-text/45">Categories</h3>
            <button
                v-if="hasActiveFilters"
                type="button"
                class="text-xs text-neutral-text/45 underline-offset-2 transition-colors hover:text-primary-dark hover:underline"
                @click="clearAll"
            >
                Clear
            </button>
        </div>

        <ul class="mt-3 space-y-0.5">
            <li v-for="category in categories" :key="category.id">
                <button
                    type="button"
                    class="group -mx-2 flex w-full items-center gap-3 rounded-sm px-2 py-2 text-left text-sm transition-colors"
                    :class="filters.category === category.slug
                        ? 'font-medium text-primary-dark'
                        : 'text-neutral-text/65 hover:bg-primary-dark hover:text-white hover:text-neutral-text'"
                    @click="toggleCategory(category.slug)"
                >
                    <span
                        class="h-3.5 w-px shrink-0 bg-current transition-opacity"
                        :class="filters.category === category.slug ? 'opacity-100' : 'opacity-0 group-hover:opacity-70'"
                    />
                    {{ category.name }}
                </button>
            </li>
        </ul>
    </aside>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.2s ease;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
