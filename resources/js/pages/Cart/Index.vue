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

const changeQuantity = (item, nextQty) => {
    const prevQty = item.quantity
    const unitPrice = item.subtotal / prevQty // derive price without needing a separate field

    // Optimistic write
    item.quantity = nextQty
    item.subtotal = unitPrice * nextQty

    setQuantity(item.variant_id, nextQty, {
        inertia: {
            onError: () => {
                // Roll back if the server rejects it (stock changed, etc.)
                item.quantity = prevQty
                item.subtotal = unitPrice * prevQty
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

    <div class="mx-auto max-w-5xl px-4 py-10 sm:px-6 md:py-12">
        <h1 class="font-heading text-xl font-semibold tracking-tight text-neutral-text sm:text-2xl">Your cart</h1>

        <!-- Empty state -->
        <div
            v-if="!items.length"
            class="mt-6 flex flex-col items-center rounded-xl border border-dashed border-neutral-text/20 bg-white px-6 py-16 text-center"
        >
            <span class="flex h-12 w-12 items-center justify-center rounded-full bg-primary-light text-primary-dark">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M3 3h2l2.4 12.4a2 2 0 0 0 2 1.6h7.2a2 2 0 0 0 2-1.6L21 8H6" />
                    <circle cx="9" cy="20" r="1" />
                    <circle cx="18" cy="20" r="1" />
                </svg>
            </span>
            <p class="mt-4 text-sm text-neutral-text/70">Your cart is empty.</p>
            <Link
                :href="route('shop.index')"
                class="mt-5 inline-flex items-center rounded-md bg-primary-dark px-5 py-2.5 text-sm font-medium text-white transition-colors duration-200 hover:bg-primary-dark/90"
            >
                Start shopping
            </Link>
        </div>

        <div v-else class="mt-6 grid gap-6 lg:grid-cols-[1fr_320px] lg:items-start">
            <div class="overflow-hidden rounded-xl border border-neutral-text/10 bg-white">
                <div class="flex items-center justify-between border-b border-neutral-text/10 px-5 py-3.5">
                    <h2 class="text-sm font-semibold text-neutral-text">Items</h2>
                    <span class="text-xs text-neutral-text/45">{{ itemCount }} {{ itemCount === 1 ? 'item' : 'items' }}</span>
                </div>

                <ul class="divide-y divide-neutral-text/10">
                    <li
                        v-for="item in items"
                        :key="item.variant_id"
                        class="flex items-center gap-4 px-5 py-4 transition-opacity"
                        :class="{ 'opacity-50': isPending(item.variant_id) }"
                    >
                        <div class="h-14 w-14 shrink-0 overflow-hidden rounded-md border border-neutral-text/10 bg-primary-light">
                            <img
                                v-if="item.image_url"
                                :src="item.image_url"
                                :alt="item.product_name"
                                class="h-full w-full object-contain p-2"
                            />
                        </div>

                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-medium text-neutral-text">{{ item.product_name }}</p>
                            <p v-if="item.variant_name" class="mt-0.5 text-xs text-neutral-text/50">{{ item.variant_name }}</p>
                            <p v-if="atStockLimit(item)" class="mt-0.5 text-xs text-accent">Max available quantity</p>
                        </div>

                        <div class="flex shrink-0 items-center gap-1 rounded-md border border-neutral-text/15 px-1 py-1">
                            <button
                                type="button"
                                class="flex h-7 w-7 items-center justify-center rounded-sm text-neutral-text/60 transition-colors hover:bg-neutral-bg hover:text-primary-dark disabled:opacity-30"
                                :disabled="isPending(item.variant_id) || item.quantity <= 1"
                                aria-label="Decrease quantity"
                                @click="decrement(item)"
                            >
                                −
                            </button>
                            <span class="w-6 text-center text-xs font-medium tabular-nums text-neutral-text">{{ item.quantity }}</span>
                            <button
                                type="button"
                                class="flex h-7 w-7 items-center justify-center rounded-sm text-neutral-text/60 transition-colors hover:bg-neutral-bg hover:text-primary-dark disabled:opacity-30"
                                :disabled="isPending(item.variant_id) || atStockLimit(item)"
                                aria-label="Increase quantity"
                                @click="increment(item)"
                            >
                                +
                            </button>
                        </div>

                        <span class="w-24 shrink-0 text-right text-sm font-semibold tabular-nums text-neutral-text">
                            {{ formatNaira(item.subtotal) }}
                        </span>

                        <button
                            type="button"
                            class="flex h-8 w-8 shrink-0 items-center justify-center rounded-md text-neutral-text/35 transition-colors hover:bg-neutral-bg hover:text-red-500"
                            :disabled="isPending(item.variant_id)"
                            :aria-label="`Remove ${item.product_name}`"
                            @click="remove(item.variant_id)"
                        >
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                                <line x1="18" y1="6" x2="6" y2="18" />
                                <line x1="6" y1="6" x2="18" y2="18" />
                            </svg>
                        </button>
                    </li>
                </ul>

                <div class="border-t border-neutral-text/10 px-5 py-3.5">
                    <Link :href="route('shop.index')" class="inline-flex items-center gap-1.5 text-xs font-medium text-primary-dark hover:underline">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M19 12H5M12 19l-7-7 7-7" />
                        </svg>
                        Continue shopping
                    </Link>
                </div>
            </div>

            <aside class="rounded-xl border border-neutral-text/10 bg-white p-5 lg:sticky lg:top-8">
                <h2 class="text-sm font-semibold text-neutral-text">Order summary</h2>

                <dl class="mt-4 space-y-2.5 text-sm">
                    <div class="flex items-center justify-between">
                        <dt class="text-neutral-text/55">Subtotal</dt>
                        <dd class="tabular-nums text-neutral-text">{{ formatNaira(total) }}</dd>
                    </div>
                    <div class="flex items-center justify-between">
                        <dt class="text-neutral-text/55">Delivery</dt>
                        <dd class="text-xs text-neutral-text/55">Paid on delivery</dd>
                    </div>
                </dl>

                <div class="mt-4 flex items-center justify-between border-t border-neutral-text/10 pt-4">
                    <span class="text-sm font-semibold text-neutral-text">Total</span>
                    <span class="font-heading text-base font-semibold tabular-nums text-primary-dark">{{ formatNaira(total) }}</span>
                </div>

                <Link
                    :href="route('checkout.index')"
                    class="mt-5 flex items-center justify-center gap-2 rounded-md bg-accent px-6 py-3 text-sm font-medium text-white transition-colors duration-200 hover:bg-accent/90"
                >
                    Checkout
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M5 12h14M13 6l6 6-6 6" />
                    </svg>
                </Link>
            </aside>
        </div>
    </div>
</template>
