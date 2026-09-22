<script setup>
/**
 * Product tile for the shop grid and the home page.
 *
 * The whole card is clickable through a stretched link on the product name
 * (an ::after overlay), while the cart action sits above that overlay as its own
 * sibling element. That keeps the a11y tree clean (no interactive element inside
 * a link) and needs no click.stop trickery.
 *
 * Add to cart targets a product *variant*, since that's what the session cart is
 * keyed by. `product.variant_id` should be the default (cheapest or first) variant.
 * When a product has several variants (`product.variant_count > 1`), or no default
 * variant is known, the action becomes a "choose an option" link to the product page
 * instead of guessing which variant to add.
 *
 * Layout: on phones the price and a round icon button share one row; from `sm` up
 * the price sits above a full-width button with a label.
 */
import {computed, ref, watch} from 'vue'
import {Link} from '@inertiajs/vue3'
import {useCart} from '@/composables/useCart'
import {formatNaira} from '@/composables/Currency.js'

const props = defineProps({
    product: {type: Object, required: true},
})

const {add, quantityOf, isPending} = useCart()

const variantId = computed(() => props.product.variant_id ?? null)
const inCartQty = computed(() => (variantId.value ? quantityOf(variantId.value) : 0))
const busy = computed(() => (variantId.value ? isPending(variantId.value) : false))

const soldOut = computed(() => props.product.in_stock === false)
const needsChoice = computed(() => (props.product.variant_count ?? 1) > 1 || !variantId.value)

const discount = computed(() => {
    const was = Number(props.product.compare_at_price ?? 0)
    const now = Number(props.product.price ?? 0)
    if (!was || !now || was <= now) return null
    return Math.round(((was - now) / was) * 100)
})

// "From ₦x" when the product has several variants and a starting price.
const showFrom = computed(() => needsChoice.value && props.product.starting_price != null)
const displayPrice = computed(() => props.product.starting_price ?? props.product.price)

const onAdd = () => {
    if (!soldOut.value && !busy.value && variantId.value) add(variantId.value, 1)
}

// Small confirmation pop whenever the cart quantity goes up.
const bump = ref(false)
let bumpTimer = null

watch(inCartQty, (next, previous) => {
    if (next > previous) {
        bump.value = true
        clearTimeout(bumpTimer)
        bumpTimer = setTimeout(() => (bump.value = false), 400)
    }
})

const addLabel = computed(() => {
    if (soldOut.value) return `${props.product.name} is out of stock`
    if (inCartQty.value) return `${inCartQty.value} in cart. Add another ${props.product.name}`
    return `Add ${props.product.name} to cart`
})
</script>

<template>
    <article
        class="group relative flex h-full flex-col overflow-hidden rounded-2xl border border-neutral-text/10 bg-white p-2.5 transition duration-200 hover:-translate-y-0.5 hover:border-primary/40 hover:shadow-sm motion-reduce:transition-none motion-reduce:hover:translate-y-0 sm:p-3"
    >
        <!-- Image -->
        <div class="relative aspect-square overflow-hidden rounded-xl bg-primary-light">
            <img
                v-if="product.image_url"
                :src="product.image_url"
                alt=""
                loading="lazy"
                class="h-full w-full object-contain p-4 mix-blend-multiply transition-transform duration-500 ease-out group-hover:scale-[1.05] motion-reduce:transition-none sm:p-5"
                :class="soldOut ? 'opacity-60' : ''"
            />
            <span v-else class="flex h-full w-full items-center justify-center text-primary-dark/25" aria-hidden="true">
                <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2"
                     stroke-linecap="round" stroke-linejoin="round">
                    <path d="M4 7l8-4 8 4-8 4-8-4zM4 7v10l8 4 8-4V7"/>
                </svg>
            </span>

            <span
                v-if="discount && !soldOut"
                class="absolute left-2 top-2 rounded-full bg-accent px-2 py-1 text-[11px] font-semibold leading-none text-white tabular-nums sm:left-2.5 sm:top-2.5"
            >
                {{ discount }}% off
            </span>
            <span
                v-if="soldOut"
                class="absolute left-2 top-2 rounded-full bg-white px-2 py-1 text-[11px] font-medium leading-none text-neutral-text/70 shadow-sm sm:left-2.5 sm:top-2.5"
            >
                Out of stock
            </span>
        </div>

        <!-- Details -->
        <div class="px-1 pt-3">
            <h3 class="line-clamp-2 min-h-[2.5rem] text-sm font-medium leading-snug text-neutral-text sm:text-[0.9375rem]">
                <!-- Stretched link: the ::after overlay makes the whole card clickable -->
                <Link
                    :href="route('shop.show', product.slug)"
                    class="after:absolute after:inset-0 after:rounded-2xl focus-visible:outline-none focus-visible:after:ring-2 focus-visible:after:ring-primary/50"
                >
                    {{ product.name }}
                </Link>
            </h3>

            <p v-if="product.subtitle" class="mt-0.5 line-clamp-1 text-xs leading-snug text-neutral-text/55">
                {{ product.subtitle }}
            </p>

            <div v-if="product.rating" class="mt-1.5 flex items-center gap-1 text-xs text-neutral-text/65">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor" class="text-accent"
                     aria-hidden="true">
                    <path d="M12 2l3 6.5 7 .9-5 4.8 1.2 7L12 17.8 5.8 21.2 7 14.2 2 9.4l7-.9z"/>
                </svg>
                <span class="font-medium tabular-nums">{{ product.rating }}</span>
                <span v-if="product.rating_count" class="tabular-nums text-neutral-text/40">({{
                        product.rating_count
                    }})</span>
            </div>
        </div>

        <!-- Price + action -->
        <div class="mt-auto flex items-end justify-between gap-2 px-1 pt-3 sm:flex-col sm:items-stretch sm:gap-3">
            <div class="flex min-w-0 flex-col sm:flex-row sm:flex-wrap sm:items-baseline sm:gap-x-2">
                <!--                <p v-if="showFrom" class="text-[11px] leading-none text-neutral-text/50 sm:mb-0.5 sm:w-full">From</p>-->
                <p class="flex min-w-0 flex-col md:flex-row md:items-baseline md:gap-1.5">
    <span
        class="shrink-0 font-sans text-[11px] font-medium leading-none text-neutral-text/50 md:text-xs"
        :class="showFrom ? '' : 'hidden md:inline'"
    >
        {{ showFrom ? 'From' : 'Price' }}
    </span>
                    <span
                        class="truncate font-heading text-base font-semibold leading-tight tracking-tight text-primary-dark tabular-nums sm:text-lg"
                    >
        {{ formatNaira(displayPrice) }}
    </span>
                </p>
                <p v-if="discount"
                   class="truncate text-xs leading-tight text-neutral-text/40 line-through tabular-nums">
                    {{ formatNaira(product.compare_at_price) }}
                </p>
            </div>

            <!-- Several variants (or no default): send them to the product page -->
            <Link
                v-if="needsChoice && !soldOut"
                :href="route('shop.show', product.slug)"
                :aria-label="`Choose an option for ${product.name}`"
                class="relative z-10 flex h-10 w-10 shrink-0 items-center justify-center gap-1.5 rounded-full border border-primary-dark text-primary-dark transition duration-150 hover:bg-primary-light focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/50 active:scale-95 sm:w-full sm:rounded-xl sm:px-4"
            >
                <span class="hidden text-sm font-medium sm:inline">Choose option</span>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                     stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M9 6l6 6-6 6"/>
                </svg>
            </Link>

            <button
                v-else
                type="button"
                :disabled="soldOut || busy"
                :aria-label="addLabel"
                class="relative z-10 flex h-10 w-10 shrink-0 items-center justify-center gap-2 rounded-full transition duration-150 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/50 focus-visible:ring-offset-2 disabled:cursor-not-allowed active:scale-95 sm:w-full sm:rounded-xl sm:px-4"
                :class="[
                    soldOut
                        ? 'bg-neutral-text/10 text-neutral-text/40'
                        : inCartQty
                          ? 'bg-secondary text-primary-dark hover:bg-primary/20'
                          : 'bg-primary-dark text-white hover:bg-primary-dark/90',
                    bump ? 'cart-bump' : '',
                ]"
                @click="onAdd"
            >
                <svg v-if="busy" class="h-4 w-4 animate-spin motion-reduce:animate-none" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <circle cx="12" cy="12" r="9" stroke-opacity="0.25"/>
                    <path d="M21 12a9 9 0 0 0-9-9" stroke-linecap="round"/>
                </svg>
                <svg v-else-if="inCartQty && !soldOut" width="16" height="16" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round"
                     aria-hidden="true">
                    <path d="M5 12.5l4.5 4.5L19 7.5"/>
                </svg>
                <svg v-else width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                     stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <circle cx="9" cy="21" r="1"/>
                    <circle cx="20" cy="21" r="1"/>
                    <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
                </svg>

                <span class="hidden text-sm font-medium sm:inline">
                    <template v-if="soldOut">Out of stock</template>
                    <template v-else-if="busy">Adding…</template>
                    <template v-else-if="inCartQty">In cart ({{ inCartQty }})</template>
                    <template v-else>Add to cart</template>
                </span>

                <!-- Phones have no label, so the quantity rides on the button -->
                <span
                    v-if="inCartQty && !busy && !soldOut"
                    class="absolute -right-1 -top-1 flex h-4 min-w-4 items-center justify-center rounded-full bg-accent px-1 text-[10px] font-semibold leading-none text-white tabular-nums sm:hidden"
                >
                    {{ inCartQty }}
                </span>
            </button>
        </div>
    </article>
</template>

<style scoped>
.cart-bump {
    animation: cart-bump 0.4s ease-out;
}

@keyframes cart-bump {
    0% {
        transform: scale(1);
    }
    40% {
        transform: scale(1.12);
    }
    100% {
        transform: scale(1);
    }
}

@media (prefers-reduced-motion: reduce) {
    .cart-bump {
        animation: none;
    }
}
</style>
