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
</script>

<template>
    <div class="mx-auto max-w-7xl px-6 py-10">
        <nav class="flex items-center gap-1.5 text-xs text-neutral-text/45" aria-label="Breadcrumb">
            <Link :href="route('shop.index')" class="hover:text-primary-dark">shop</Link>
            <template v-if="activeCategory">
                <span>›</span>
                <span class="text-neutral-text/70">{{ activeCategory.name }}</span>
            </template>
        </nav>

        <div class="mt-8 flex flex-col gap-10 lg:flex-row lg:items-start">
            <ShopFilters :categories="categories" :filters="filters" />

            <div class="min-w-0 flex-1">
                <div class="flex flex-col gap-3 border-b border-neutral-text/10 pb-4 sm:flex-row sm:items-center sm:justify-between sm:gap-4">
                    <p class="shrink-0 text-sm text-neutral-text/60">
                        {{ products.total }} product{{ products.total === 1 ? '' : 's' }}
                    </p>

                    <ShopSearch v-model="search" />

                    <label class="flex shrink-0 items-center gap-2 text-xs text-neutral-text/50">
                        Sort by
                        <select
                            v-model="sort"
                            class="rounded-md border border-neutral-text/15 bg-white py-1 pl-2 pr-6 text-xs text-neutral-text focus:border-primary-dark focus:outline-none focus:ring-1 focus:ring-primary-dark/30"
                        >
                            <option v-for="option in sortOptions" :key="option.value" :value="option.value">
                                {{ option.label }}
                            </option>
                        </select>
                    </label>
                </div>

                <div v-if="products.data.length" class="mt-6 grid grid-cols-2 gap-3 sm:grid-cols-3 sm:gap-4 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6">
                    <ProductCard
                        v-for="product in products.data"
                        :key="product.id"
                        :product="product"
                    />
                </div>

                <div v-else class="mt-6 rounded-md border border-dashed border-neutral-text/20 px-6 py-14 text-center">
                    <p class="text-sm text-neutral-text/70">
                        {{ search ? `Nothing matches "${search}".` : 'Nothing matches those filters yet.' }}
                    </p>
                    <Link
                        :href="route('shop.index')"
                        class="mt-4 inline-block text-sm font-medium text-primary-dark underline-offset-4 hover:underline"
                    >
                        Clear filters and browse everything
                    </Link>
                </div>

                <Pagination :links="products.links" />
            </div>
        </div>
    </div>
</template>
