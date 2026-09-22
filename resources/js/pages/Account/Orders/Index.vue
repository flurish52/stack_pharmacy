<script setup>
/**
 * Account > Orders
 *
 * Corrections from the last pass:
 * - `defineProps()` result wasn't assigned to a variable, so `props.orders` was undefined.
 *   Now assigned to `props` and used consistently.
 * - `v-for="order in orders.data.length"` iterated a number, not the array. Fixed to
 *   `orders.data`, with a `?? []` fallback so a missing/malformed paginator object
 *   can't crash the page — it just shows the empty state instead.
 * - `orders` is a Laravel paginator ({ data, links, total, ... }), not a plain array —
 *   every reference below goes through `orders.data` / `orders.links`.
 * - Layout applied via `defineOptions({ layout: AccountLayout })` to match your existing
 *   page convention, instead of importing and wrapping in the template.
 * - Status colors are back to palette-only tokens (no amber-100/blue-100/green-100/red-100).
 * - Field names follow your real backend shape (order.total_amount, order.items_count,
 *   order.fulfillment_type), with graceful fallbacks (`order_number ?? id`, `total_amount ?? total ?? 0`)
 *   in case a given order object is missing a field.
 */
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import AccountLayout from '@/Layouts/AccountLayout.vue'

defineOptions({ layout: AccountLayout })

const props = defineProps({
    orders: { type: Object, required: true }, // Laravel paginator: { data, links, total, ... }
})

const STATUS_CONFIG = {
    pending: { label: 'Pending', classes: 'bg-neutral-text/8 text-neutral-text/70' },
    paid: { label: 'Paid', classes: 'bg-primary-light text-primary-dark' },
    processing: { label: 'Processing', classes: 'bg-accent-light/20 text-accent' },
    out_for_delivery: { label: 'Out for delivery', classes: 'bg-primary-light text-primary-dark' },
    ready_for_pickup: { label: 'Ready for pickup', classes: 'bg-primary-light text-primary-dark' },
    completed: { label: 'Completed', classes: 'bg-primary-dark text-white' },
    cancelled: { label: 'Cancelled', classes: 'bg-neutral-text/8 text-neutral-text/40 line-through decoration-neutral-text/30' },
}
const statusOf = (status) => STATUS_CONFIG[status] ?? STATUS_CONFIG.pending

// Guard against a malformed/empty paginator payload rather than throwing.
const rows = computed(() => props.orders?.data ?? [])
const hasOrders = computed(() => rows.value.length > 0)
const links = computed(() => props.orders?.links ?? [])

function formatNaira(value) {
    return new Intl.NumberFormat('en-NG', { style: 'currency', currency: 'NGN', maximumFractionDigits: 0 }).format(value ?? 0)
}
function formatDate(value) {
    if (!value) return '—'
    return new Date(value).toLocaleDateString('en-NG', { day: 'numeric', month: 'short', year: 'numeric' })
}
</script>

<template>
    <div>
        <div class="flex items-baseline justify-between gap-3">
            <h2 class="font-heading text-lg font-semibold tracking-tight text-neutral-text sm:text-xl">
                My orders
            </h2>
            <span v-if="hasOrders" class="text-xs text-neutral-text/50">
                {{ orders.total ?? rows.length }} {{ (orders.total ?? rows.length) === 1 ? 'order' : 'orders' }}
            </span>
        </div>

        <!-- List: one bordered container, divide-y lines between rows -->
        <div
            v-if="hasOrders"
            class="mt-5 overflow-hidden rounded-xl border border-neutral-text/10 divide-y divide-neutral-text/10"
        >
            <Link
                v-for="order in rows"
                :key="order.id"
                :href="route('account.orders.show', order.id)"
                class="group flex items-center gap-4 px-5 py-4 transition-colors duration-200 hover:bg-neutral-bg focus:bg-neutral-bg focus:outline-none sm:px-6"
            >
                <!-- Left: order identity -->
                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                        <span class="font-heading text-sm font-semibold text-neutral-text">
                            Order #{{ order.order_number ?? order.id }}
                        </span>
                        <span
                            class="inline-flex items-center rounded-md px-2 py-0.5 text-[11px] font-medium capitalize"
                            :class="statusOf(order.status).classes"
                        >
                            {{ statusOf(order.status).label }}
                        </span>
                    </div>
                    <p class="mt-1 text-xs text-neutral-text/50">
                        {{ formatDate(order.created_at) }}
                        <span v-if="order.items_count"> · {{ order.items_count }} item{{ order.items_count === 1 ? '' : 's' }}</span>
                        <span v-if="order.fulfillment_type"> · {{ order.fulfillment_type === 'pickup' ? 'Pickup' : 'Delivery' }}</span>
                    </p>
                </div>

                <!-- Right: total + explicit "view" affordance -->
                <div class="flex shrink-0 items-center gap-3">
                    <span class="text-sm font-semibold text-neutral-text">
                        {{ formatNaira(order.total_amount ?? order.total) }}
                    </span>
                    <span
                        class="flex h-7 w-7 items-center justify-center rounded-full bg-neutral-text/5 text-neutral-text/40 transition-all duration-200 group-hover:bg-primary-light group-hover:text-primary-dark"
                    >
                        <svg
                            width="14" height="14" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            class="transition-transform duration-200 group-hover:translate-x-0.5"
                            aria-hidden="true"
                        >
                            <path d="M9 6l6 6-6 6" />
                        </svg>
                    </span>
                </div>
            </Link>
        </div>

        <!-- Empty state -->
        <div
            v-else
            class="mt-5 flex flex-col items-center gap-3 rounded-xl border border-dashed border-neutral-text/15 px-6 py-14 text-center"
        >
            <span class="flex h-11 w-11 items-center justify-center rounded-full bg-neutral-text/5 text-neutral-text/30">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M3 3h2l2.4 12.4a2 2 0 0 0 2 1.6h7.2a2 2 0 0 0 2-1.6L21 8H6" />
                </svg>
            </span>
            <p class="text-sm font-medium text-neutral-text">You haven't placed any orders yet</p>
            <p class="max-w-xs text-xs text-neutral-text/50">
                Orders you place will show up here so you can track them anytime.
            </p>
            <Link
                :href= "route('shop.index')"
                class="mt-1 inline-flex items-center rounded-md bg-primary-dark px-4 py-2 text-xs font-medium text-white transition-colors duration-200 hover:bg-primary-dark/90"
            >
                Start shopping
            </Link>
        </div>

        <!-- Pagination -->
        <div v-if="links.length > 3" class="mt-6 flex flex-wrap gap-1.5">
            <Link
                v-for="(link, i) in links"
                :key="i"
                :href="link.url || '#'"
                v-html="link.label"
                class="rounded-md px-3 py-1.5 text-sm font-medium transition-colors duration-200"
                :class="[
                    link.active ? 'bg-primary-dark text-white' : 'text-neutral-text/65 hover:bg-neutral-bg',
                    !link.url ? 'pointer-events-none opacity-40' : '',
                ]"
            />
        </div>
    </div>
</template>
