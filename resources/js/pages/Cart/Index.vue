<script setup>
import { computed, reactive, watch } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import { formatNaira } from '@/Composables/Currency.js'
import { useCart } from '@/composables/useCart'

const props = defineProps({
    items: { type: Array, default: () => [] },
    total: { type: Number, default: 0 },
})

const { setQuantity, remove, isPending } = useCart()

// Local mutable mirror of props.items so the stepper responds instantly.
// The server still has final say — any fresh props (from this update or
// any other visit) overwrite the mirror via the watcher below.
const localItems = reactive(props.items.map(i => ({ ...i })))

watch(
    () => props.items,
    (fresh) => localItems.splice(0, localItems.length, ...fresh.map(i => ({ ...i }))),
    { deep: true }
)

const itemCount = computed(() => localItems.reduce((sum, item) => sum + item.quantity, 0))
const localTotal = computed(() => localItems.reduce((sum, item) => sum + item.subtotal, 0))

const atStockLimit = (item) => item.quantity >= item.stock_quantity
const unitPrice = (item) => (item.quantity > 0 ? item.subtotal / item.quantity : 0)

const changeQuantity = (item, nextQty) => {
    const prevQty = item.quantity
    const unit = item.subtotal / prevQty // derive price without needing a separate field

    // Optimistic write
    item.quantity = nextQty
    item.subtotal = unit * nextQty

    setQuantity(item.variant_id, nextQty, {
        inertia: {
            onError: () => {
                // Roll back if the server rejects it (stock changed, etc.)
                item.quantity = prevQty
                item.subtotal = unit * prevQty
            },
        },
    })
}

const decrement = (item) => {
    if (item.quantity > 1) changeQuantity(item, item.quantity - 1)
}
const increment = (item) => {
    if (!atStockLimit(item)) changeQuantity(item, item.quantity + 1)
}
</script>

<template>
    <Head title="Your cart" />

    <div class="mx-auto max-w-5xl px-4 py-8 sm:px-6 md:py-12">
        <!-- Page header -->
        <div class="flex flex-wrap items-end justify-between gap-x-6 gap-y-2">
            <div>
                <h1 class="font-heading text-2xl font-semibold tracking-tight text-neutral-text sm:text-3xl">Your cart</h1>
                <p v-if="localItems.length" class="mt-1 text-sm text-neutral-text/60">
                    {{ itemCount }} {{ itemCount === 1 ? 'item' : 'items' }} ready for checkout
                </p>
            </div>
            <Link
                v-if="localItems.length"
                :href="route('shop.index')"
                class="group inline-flex items-center gap-1.5 text-sm font-medium text-primary-dark focus:outline-none focus-visible:ring-2 focus-visible:ring-primary"
            >
                <svg
                    class="transition-transform duration-200 group-hover:-translate-x-0.5 motion-reduce:transition-none"
                    width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"
                >
                    <path d="M19 12H5M12 19l-7-7 7-7" />
                </svg>
                <span class="underline-offset-4 group-hover:underline">Continue shopping</span>
            </Link>
        </div>

        <!-- Empty state -->
        <div
            v-if="!localItems.length"
            class="mt-8 flex flex-col items-center rounded-xl border border-dashed border-primary-dark/25 bg-primary-light/50 px-6 py-16 text-center"
        >
            <span class="flex h-14 w-14 items-center justify-center rounded-full bg-secondary text-primary-dark">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M3 3h2l2.4 12.4a2 2 0 0 0 2 1.6h7.2a2 2 0 0 0 2-1.6L21 8H6" />
                    <circle cx="9" cy="20" r="1" />
                    <circle cx="18" cy="20" r="1" />
                </svg>
            </span>
            <h2 class="mt-5 font-heading text-lg font-semibold text-neutral-text">Your cart is empty</h2>
            <p class="mt-1.5 max-w-xs text-sm text-neutral-text/60">Add a few products and they will show up here.</p>
            <Link
                :href="route('shop.index')"
                class="mt-6 inline-flex items-center rounded-md bg-primary-dark px-5 py-2.5 text-sm font-medium text-neutral-bg shadow-sm transition duration-200 hover:bg-primary-dark/90 hover:shadow focus:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2 focus-visible:ring-offset-neutral-bg active:scale-[0.98] motion-reduce:transition-none"
            >
                Start shopping
            </Link>
        </div>

        <div v-else class="mt-8 grid gap-6 lg:grid-cols-[1fr_340px] lg:items-start lg:gap-8">
            <!-- Items -->
            <div class="overflow-hidden rounded-xl border border-neutral-text/10 bg-neutral-bg shadow-sm">
                <TransitionGroup
                    tag="ul"
                    class="divide-y divide-neutral-text/10"
                    leave-active-class="transition-opacity duration-200 motion-reduce:transition-none"
                    leave-to-class="opacity-0"
                >
                    <li
                        v-for="item in localItems"
                        :key="item.variant_id"
                        class="flex gap-4 px-4 py-5 transition-opacity duration-200 sm:gap-5 sm:px-6"
                        :class="{ 'opacity-50': isPending(item.variant_id) }"
                    >
                        <div class="h-20 w-20 shrink-0 overflow-hidden rounded-lg border border-neutral-text/10 bg-primary-light sm:h-24 sm:w-24">
                            <img
                                v-if="item.image_url"
                                :src="item.image_url"
                                :alt="item.product_name"
                                class="h-full w-full object-contain p-2.5"
                            />
                        </div>

                        <div class="flex min-w-0 flex-1 flex-col">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-medium text-neutral-text sm:text-base">{{ item.product_name }}</p>
                                    <p v-if="item.variant_name" class="mt-0.5 text-xs text-neutral-text/55 sm:text-sm">{{ item.variant_name }}</p>
                                    <p class="mt-1 text-xs tabular-nums text-neutral-text/55">{{ formatNaira(unitPrice(item)) }} each</p>
                                </div>

                                <button
                                    type="button"
                                    class="-mr-1.5 -mt-1 inline-flex shrink-0 items-center gap-1 rounded-md px-2 py-1.5 text-xs font-medium text-neutral-text/55 transition-colors hover:bg-accent/10 hover:text-accent focus:outline-none focus-visible:ring-2 focus-visible:ring-primary disabled:opacity-40"
                                    :disabled="isPending(item.variant_id)"
                                    :aria-label="`Remove ${item.product_name}`"
                                    @click="remove(item.variant_id)"
                                >
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <path d="M3 6h18M8 6V4h8v2M6 6l1 14h10l1-14" />
                                    </svg>
                                    <span class="hidden sm:inline">Remove</span>
                                </button>
                            </div>

                            <div class="mt-auto flex items-end justify-between gap-3 pt-3">
                                <div>
                                    <div class="inline-flex items-center rounded-md border border-neutral-text/15">
                                        <button
                                            type="button"
                                            class="flex h-9 w-9 items-center justify-center text-base text-neutral-text/60 transition-colors hover:bg-primary-light hover:text-primary-dark focus:outline-none focus-visible:ring-2 focus-visible:ring-primary disabled:opacity-30 disabled:hover:bg-transparent"
                                            :disabled="isPending(item.variant_id) || item.quantity <= 1"
                                            aria-label="Decrease quantity"
                                            @click="decrement(item)"
                                        >
                                            −
                                        </button>
                                        <span class="w-9 text-center text-sm font-medium tabular-nums text-neutral-text" aria-live="polite">{{ item.quantity }}</span>
                                        <button
                                            type="button"
                                            class="flex h-9 w-9 items-center justify-center text-base text-neutral-text/60 transition-colors hover:bg-primary-light hover:text-primary-dark focus:outline-none focus-visible:ring-2 focus-visible:ring-primary disabled:opacity-30 disabled:hover:bg-transparent"
                                            :disabled="isPending(item.variant_id) || atStockLimit(item)"
                                            aria-label="Increase quantity"
                                            @click="increment(item)"
                                        >
                                            +
                                        </button>
                                    </div>
                                    <p v-if="atStockLimit(item)" class="mt-1.5 text-xs font-medium text-accent">Max available quantity</p>
                                </div>

                                <span class="text-base font-semibold tabular-nums text-neutral-text sm:text-lg">
                                    {{ formatNaira(item.subtotal) }}
                                </span>
                            </div>
                        </div>
                    </li>
                </TransitionGroup>
            </div>

            <!-- Summary -->
            <aside class="rounded-xl border border-primary-dark/15 bg-primary-light/60 p-5 shadow-sm sm:p-6 lg:sticky lg:top-24">
                <h2 class="font-heading text-base font-semibold text-neutral-text">Order summary</h2>

                <dl class="mt-5 space-y-3 text-sm">
                    <div class="flex items-center justify-between">
                        <dt class="text-neutral-text/60">Subtotal ({{ itemCount }} {{ itemCount === 1 ? 'item' : 'items' }})</dt>
                        <dd class="tabular-nums text-neutral-text">{{ formatNaira(localTotal) }}</dd>
                    </div>
                    <div class="flex items-center justify-between">
                        <dt class="text-neutral-text/60">Delivery</dt>
                        <dd class="text-xs text-neutral-text/60">Paid on delivery</dd>
                    </div>
                </dl>

                <div class="mt-5 flex items-baseline justify-between border-t border-primary-dark/15 pt-5">
                    <span class="text-sm font-semibold text-neutral-text">Total</span>
                    <span class="font-heading text-2xl font-semibold tabular-nums text-primary-dark">{{ formatNaira(localTotal) }}</span>
                </div>

                <Link
                    :href="route('checkout.index')"
                    class="group mt-6 flex items-center justify-center gap-2 rounded-md bg-accent px-6 py-3.5 text-sm font-semibold text-neutral-bg shadow-sm transition duration-200 hover:bg-accent/90 hover:shadow focus:outline-none focus-visible:ring-2 focus-visible:ring-accent focus-visible:ring-offset-2 focus-visible:ring-offset-primary-light active:scale-[0.98] motion-reduce:transition-none motion-reduce:active:scale-100"
                >
                    Checkout
                    <svg
                        class="transition-transform duration-200 group-hover:translate-x-1 motion-reduce:transition-none"
                        width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"
                    >
                        <path d="M5 12h14M13 6l6 6-6 6" />
                    </svg>
                </Link>

                <p class="mt-3 flex items-center justify-center gap-1.5 text-xs text-neutral-text/55">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <rect x="4" y="10" width="16" height="9" rx="1.5" />
                        <path d="M7 10V7a5 5 0 0 1 10 0v3" />
                    </svg>
                    Secure payment with Paystack
                </p>
            </aside>
        </div>
    </div>
</template>
