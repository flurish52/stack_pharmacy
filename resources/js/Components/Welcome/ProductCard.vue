<script setup>
/**
 * Product tile for the shop grid and the home page.
 *
 * The card links to the product page; the cart action sits outside the
 * <Link> entirely (next to the price, not overlaid on the image), so there's
 * no nested-interactive-element a11y issue and no click.stop trickery needed.
 *
 * Add to cart targets a product *variant*, since that's what the session
 * cart is keyed by. `product.variant_id` should be the default (cheapest or
 * first) variant. When a product has several variants, set
 * `product.variant_count > 1` and the action becomes a "choose an option"
 * link to the product page instead of guessing which variant to add.
 */
import { computed, ref } from 'vue'
import { Link } from '@inertiajs/vue3'
import { useCart } from '@/composables/useCart'
import { formatNaira } from '@/composables/Currency.js'

const props = defineProps({
    product: { type: Object, required: true },
})

const { add, quantityOf, isInCart, isPending } = useCart()

const variantId = computed(() => props.product.variant_id ?? null)
const inCartQty = computed(() => (variantId.value ? quantityOf(variantId.value) : 0))
const busy = computed(() => (variantId.value ? isPending(variantId.value) : false))

const hasChoices = computed(() => (props.product.variant_count ?? 1) > 1)
const soldOut = computed(() => props.product.in_stock === false)
const canAdd = ref(true)

const discount = computed(() => {
    const was = Number(props.product.compare_at_price ?? 0)
    const now = Number(props.product.price ?? 0)
    if (!was || !now || was <= now) return null
    return Math.round(((was - now) / was) * 100)
})

const onAdd = (event) => {
    event.preventDefault()
    event.stopPropagation()
    if (canAdd.value && !busy.value) add(variantId.value, 1)
}
</script>

<template>
    <article class="group flex flex-col border border-neutral-text/10 bg-white transition-colors hover:border-primary-dark/30">
        <Link :href="route('shop.show', product.slug)" class="block">
            <div class="relative aspect-[4/3] overflow-hidden bg-primary-light/40 p-3 sm:p-4">
                <img
                    v-if="product.image_url"
                    :src="product.image_url"
                    :alt="product.name"
                    loading="lazy"
                    class="h-full w-full object-contain transition-transform duration-500 group-hover:scale-[1.04]"
                />
                <span v-else class="flex h-full w-full items-center justify-center text-primary-dark/20" aria-hidden="true">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 7l8-4 8 4-8 4-8-4zM4 7v10l8 4 8-4V7" />
                    </svg>
                </span>

                <span
                    v-if="discount"
                    class="absolute left-2.5 top-2.5 bg-primary-dark px-2 py-1 text-[11px] font-medium leading-none text-white tabular-nums"
                >
                    {{ discount }}% off
                </span>
                <span
                    v-else-if="soldOut"
                    class="absolute left-2.5 top-2.5 bg-white/95 px-2 py-1 text-[11px] font-medium leading-none text-neutral-text/70"
                >
                    Out of stock
                </span>
            </div>

            <div class="px-3 pt-3 sm:px-4 sm:pt-4">
                <h3 class="line-clamp-2 text-sm font-medium leading-snug text-neutral-text">{{ product.name }}</h3>
                <p v-if="product.subtitle" class="mt-1 line-clamp-1 text-xs leading-snug text-neutral-text/55">
                    {{ product.subtitle }}
                </p>

                <div v-if="product.rating" class="mt-1.5 flex items-center gap-1.5 text-xs text-neutral-text/60">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor" class="text-amber-500" aria-hidden="true">
                        <path d="M12 2l3 6.5 7 .9-5 4.8 1.2 7L12 17.8 5.8 21.2 7 14.2 2 9.4l7-.9z" />
                    </svg>
                    <span class="tabular-nums">{{ product.rating }}</span>
                    <span v-if="product.rating_count" class="text-neutral-text/40">({{ product.rating_count }})</span>
                </div>
            <div class="flex min-w-0 items-baseline gap-1.5">
                <span class="truncate text-sm font-semibold text-primary-dark tabular-nums my-1 p-1 rounded-md bg-primary-light">
                    {{ formatNaira(product.starting_price ?? product.price ) }}
                </span>
                <span
                    v-if="discount"
                    class="truncate text-xs text-neutral-text/40 line-through tabular-nums"
                >
                    {{ formatNaira(product.compare_at_price) }}
                </span>
            </div>
            </div>

        </Link>

        <div class="mt-auto flex items-center justify-between  px-3 pb-3 pt-2.5 sm:px-4 sm:pb-4">

            <Link
                v-if="hasChoices && !soldOut"
                :href="route('shop.show', product.slug)"
                :aria-label="`Choose an option for ${product.name}`"
                class="flex h-9 w-9 shrink-0 items-center justify-center gap-2 rounded-full border border-primary-dark text-primary-dark transition-colors hover:bg-primary-light/60 sm:h-auto sm:w-full sm:rounded-sm sm:px-4 sm:py-2.5"
            >
                <span class="hidden text-xs font-medium sm:inline">Choose an option</span>
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M9 6l6 6-6 6" />
                </svg>
            </Link>

            <button
                v-else
                type="button"
                :disabled="soldOut || !canAdd || busy"
                :aria-label="soldOut
                    ? `${product.name} is out of stock`
                    : inCartQty
                        ? `${inCartQty} in cart — add another ${product.name}`
                        : `Add ${product.name} to cart`"
                class="relative flex h-9 w-10 shrink-0 items-center justify-center gap-2 rounded-full transition-colors disabled:cursor-not-allowed sm:h-auto sm:w-full sm:rounded-sm sm:px-4 sm:py-2.5"
                :class="soldOut
                    ? 'bg-neutral-text/10 text-neutral-text/40'
                    : 'bg-primary-dark text-white hover:opacity-90'"
                @click="onAdd"
            >
                <svg
                    v-if="busy"
                    class="h-4 w-4 animate-spin"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    aria-hidden="true"
                >
                    <circle cx="12" cy="12" r="9" stroke-opacity="0.25" />
                    <path d="M21 12a9 9 0 0 0-9-9" stroke-linecap="round" />
                </svg>
                <svg v-else width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <circle cx="9" cy="21" r="1" />
                    <circle cx="20" cy="21" r="1" />
                    <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6" />
                </svg>

                <span class="hidden text-sm font-medium sm:inline">
                    <template v-if="soldOut">Out of stock</template>
                    <template v-else-if="busy">Adding…</template>
                    <template v-else-if="busy">Adding…</template>
                    <template v-else-if="inCartQty">In cart ({{ inCartQty }})</template>
                    <template v-else>Add to cart</template>
                </span>

                <span
                    v-if="inCartQty && !busy"
                    class="absolute -right-1 -top-1 flex h-4 w-4 items-center justify-center rounded-full bg-accent text-[10px] font-semibold text-white tabular-nums sm:hidden"
                >
                    {{ inCartQty }}
                </span>
            </button>
        </div>
    </article>
</template>
