<script setup>
import { computed, ref, watch } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import ProductCard from '@/Components/Welcome/ProductCard.vue'
import ShopFilters from '@/Components/Shop/ShopFilters.vue'
import ShopSearch from '@/Components/Shop/ShopSearch.vue'
import Pagination from '@/Components/Shop/Pagination.vue'

const props = defineProps({
    products: { type: Object, required: true },
    categories: { type: Array, required: true },
    filters: { type: Object, required: true },
})

const activeCategory = computed(() =>
    props.categories.find((c) => c.slug === props.filters.category)
)

const sort = computed({
    get: () => props.filters.sort ?? 'popular',
    set: (value) => {
        router.get(route('shop.index'), { ...props.filters, sort: value }, {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        })
    },
})

const sortOptions = [
    { value: 'popular', label: 'Popular' },
    { value: 'price_asc', label: 'Price: low to high' },
    { value: 'price_desc', label: 'Price: high to low' },
    { value: 'newest', label: 'Newest' },
]

const search = ref(props.filters.search ?? '')
let searchTimeout = null

watch(search, (value) => {
    clearTimeout(searchTimeout)
    searchTimeout = setTimeout(() => {
        router.get(route('shop.index'), { ...props.filters, search: value || undefined, page: undefined }, {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        })
    }, 400)
})

/* Active filter chips: show what is applied and let people drop it in one click */
const hasActiveFilters = computed(() => Boolean(props.filters.category || props.filters.search))

function clearCategory() {
    router.get(route('shop.index'), { ...props.filters, category: undefined, page: undefined }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    })
}

function clearSearch() {
    // The search watcher above issues the request
    search.value = ''
}
</script>

<template>
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 md:py-10">
        <!-- Breadcrumb + page heading -->
        <nav class="flex items-center gap-1.5 text-xs text-neutral-text/55" aria-label="Breadcrumb">
            <Link :href="route('pharm.home')" class="transition-colors hover:text-primary-dark">Home</Link>
            <span aria-hidden="true">›</span>
            <Link
                v-if="activeCategory"
                :href="route('shop.index')"
                class="transition-colors hover:text-primary-dark"
            >
                Shop
            </Link>
            <span v-else class="text-neutral-text/80" aria-current="page">Shop</span>
            <template v-if="activeCategory">
                <span aria-hidden="true">›</span>
                <span class="text-neutral-text/80" aria-current="page">{{ activeCategory.name }}</span>
            </template>
        </nav>

        <h1 class="mt-3 font-heading text-2xl font-semibold tracking-tight text-neutral-text sm:text-3xl">
            {{ activeCategory?.name ?? 'Shop' }}
        </h1>

        <div class="mt-8 flex flex-col gap-8 lg:flex-row lg:items-start lg:gap-10">
            <ShopFilters :categories="categories" :filters="filters" />

            <div class="min-w-0 flex-1">
                <!-- Toolbar -->
                <div class="flex flex-col gap-3 border-b border-neutral-text/10 pb-4 sm:flex-row sm:items-center sm:justify-between sm:gap-4">
                    <p class="shrink-0 text-sm text-neutral-text/60">
                        <span class="font-medium text-neutral-text">{{ products.total }}</span>
                        product{{ products.total === 1 ? '' : 's' }}
                    </p>

                    <ShopSearch v-model="search" />

                    <label class="flex shrink-0 items-center gap-2 text-xs text-neutral-text/60">
                        Sort by
                        <select
                            v-model="sort"
                            class="rounded-md border border-neutral-text/15 bg-neutral-bg py-1.5 pl-2.5 pr-7 text-xs text-neutral-text transition-colors hover:border-primary-dark/40 focus:border-primary-dark focus:outline-none focus:ring-1 focus:ring-primary-dark/30"
                        >
                            <option v-for="option in sortOptions" :key="option.value" :value="option.value">
                                {{ option.label }}
                            </option>
                        </select>
                    </label>
                </div>

                <!-- Applied filters -->
                <div v-if="hasActiveFilters" class="mt-4 flex flex-wrap items-center gap-2">
                    <span class="text-xs text-neutral-text/55">Showing:</span>

                    <button
                        v-if="activeCategory"
                        type="button"
                        class="group inline-flex items-center gap-1.5 rounded-md bg-primary-light px-2.5 py-1 text-xs font-medium text-primary-dark transition-colors hover:bg-secondary focus:outline-none focus-visible:ring-2 focus-visible:ring-primary"
                        @click="clearCategory"
                    >
                        {{ activeCategory.name }}
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true">
                            <path d="M18 6 6 18M6 6l12 12" />
                        </svg>
                        <span class="sr-only">Remove category filter</span>
                    </button>

                    <button
                        v-if="filters.search"
                        type="button"
                        class="group inline-flex items-center gap-1.5 rounded-md bg-primary-light px-2.5 py-1 text-xs font-medium text-primary-dark transition-colors hover:bg-secondary focus:outline-none focus-visible:ring-2 focus-visible:ring-primary"
                        @click="clearSearch"
                    >
                        "{{ filters.search }}"
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true">
                            <path d="M18 6 6 18M6 6l12 12" />
                        </svg>
                        <span class="sr-only">Remove search</span>
                    </button>

                    <Link
                        :href="route('shop.index')"
                        class="ml-1 text-xs font-medium text-primary-dark underline-offset-4 hover:underline"
                    >
                        Clear all
                    </Link>
                </div>

                <!-- Grid -->
                <div v-if="products.data.length" class="mt-6 grid grid-cols-2 gap-3 sm:grid-cols-3 sm:gap-4 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6">
                    <ProductCard
                        v-for="product in products.data"
                        :key="product.id"
                        :product="product"
                    />
                </div>

                <!-- Empty state -->
                <div v-else class="mt-6 rounded-md border border-dashed border-primary-dark/25 bg-primary-light/50 px-6 py-14 text-center">
                    <p class="text-sm text-neutral-text/75">
                        {{ search ? `Nothing matches "${search}".` : 'Nothing matches those filters yet.' }}
                    </p>
                    <Link
                        :href="route('shop.index')"
                        class="mt-4 inline-flex items-center gap-1.5 rounded-md bg-primary-dark px-4 py-2 text-sm font-medium text-neutral-bg transition-colors duration-200 hover:bg-primary-dark/90 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2 focus-visible:ring-offset-neutral-bg"
                    >
                        Clear filters and browse everything
                    </Link>
                </div>

                <div class="mt-8 border-t border-neutral-text/10 pt-6">
                    <Pagination :links="products.links" />
                </div>
            </div>
        </div>
    </div>
</template>
