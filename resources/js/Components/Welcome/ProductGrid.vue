<script setup>
/**
 * Any list of products on the site: "Best selling", "On offer", a category
 * page, search results. Takes the heading and the collection; the card does
 * the rest.
 */
import {Link} from '@inertiajs/vue3'
import ProductCard from './ProductCard.vue'

defineProps({
    title: {type: String, required: true},
    lead: {type: String, default: ''},
    products: {type: Array, default: () => []},
    viewAllHref: {type: String, default: ''},
    viewAllLabel: {type: String, default: 'View all products'},
    /** 4 works for a home page row, 3 for a narrower column. */
    columns: {type: Number, default: 4},
})

const columnClass = {
    3: 'sm:grid-cols-2 lg:grid-cols-3',
    4: 'sm:grid-cols-2 lg:grid-cols-4',
    6: 'grid-cols-2 sm:grid-cols-3 lg:grid-cols-6',
}
</script>

<template>
    <section class="mx-auto max-w-6xl px-6 py-3 md:py-6">

        <div v-if="products.length"
             :class="columnClass[columns] ?? columnClass[4]"
             class="mt-6 grid grid-cols-2 gap-3 sm:grid-cols-3 sm:gap-4 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6">

            <ProductCard
                v-for="product in products"
                :key="product.id"
                :product="product"
            />
        </div>


        <div v-else class="mt-10 border border-dashed border-neutral-text/20 px-6 py-14 text-center">
            <p class="text-sm text-neutral-text/70">We're still stocking this shelf.</p>
            <Link
                v-if="viewAllHref"
                :href="viewAllHref"
                class="mt-4 inline-block text-sm font-medium text-primary-dark underline-offset-4 hover:underline"
            >
                Browse the full catalogue
            </Link>
        </div>
    </section>
</template>
