<script setup>
/**
 * Featured products. Gains a "See all" link on the heading line and a real
 * empty state that offers a way forward instead of a shrug.
 */
import { Link } from '@inertiajs/vue3'
import SectionHeading from './SectionHeading.vue'
import ProductGrid from "@/Components/Welcome/ProductGrid.vue";
defineProps({
    products: { type: Array, default: () => [] },
})
</script>

<template>
    <section class="mx-auto max-w-6xl px-4  sm:px-6 md:py-14">
        <div class="flex items-end justify-between gap-4 border-b border-neutral-text/10 pb-4">
            <SectionHeading
                title="Popular right now"
                lead="What people around you are picking up this week."
                class="[&_h2]:font-heading [&_h2]:text-xl [&_h2]:font-semibold [&_h2]:tracking-tight [&_h2]:text-neutral-text sm:[&_h2]:text-2xl [&_p]:mt-1 [&_p]:text-sm [&_p]:text-neutral-text/60"
            />

            <Link
                :href="route('shop.index')"
                class="hidden shrink-0 items-center gap-1.5 rounded-md border border-primary-dark px-3.5 py-2 text-sm font-medium text-primary-dark transition-all duration-200 hover:gap-2 hover:border-primary-dark hover:bg-primary-dark hover:text-white sm:flex"
            >
                See all
            </Link>
        </div>

        <ProductGrid
            title="Best selling products"
            :products="products"
            :view-all-href="route('shop.index')"
            class="mt-6"
        />

        <Link
            :href="route('shop.index')"
            class="mt-6 flex items-center justify-center gap-1.5 my-3 rounded-md border border-primary-dark px-3.5 py-2 text-sm font-medium text-primary-dark transition-all duration-200 hover:gap-2 hover:border-primary-dark hover:bg-primary-dark hover:text-white sm:flex sm:hidden">
            See all products
            <span aria-hidden="true">→</span>
        </Link>
    </section>
</template>
