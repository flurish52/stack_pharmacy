<script setup>
/**
 * Account home ("Dashboard" in your route list / "Home" tab in AccountLayout).
 *
 * This replaces the Breeze placeholder ("You're logged in!") with something that
 * actually earns the "Home" tab: a greeting, two quick-stat tiles that link into
 * Orders / Addresses, and a short recent-orders preview reusing the same row
 * pattern as the Orders index (so the two pages feel like one product, not two).
 *
 * Swapped AppLayout -> AccountLayout since this page lives inside the account
 * nav (Home/Orders/Addresses) you already built — AppLayout is the public-site
 * shell with the header/cart/footer, which doesn't apply here.
 *
 * Assumptions (adjust to your real props/backend):
 * - auth.user: { name }  (via Inertia shared props, standard Breeze setup)
 * - recentOrders: [{ id, order_number, status, total, created_at }]  (send top 3)
 * - orderCount: number — total orders ever placed
 * - addressCount: number — saved addresses
 * - route('account.orders.index'), route('account.orders.show', id),
 *   route('account.addresses.index') exist
 */
import { computed } from 'vue'
import { Head, Link, usePage } from '@inertiajs/vue3'
import AccountLayout from '@/Layouts/AccountLayout.vue'

const props = defineProps({
    recentOrders: { type: Array, default: () => [] },
    orderCount: { type: Number, default: 0 },
    addressCount: { type: Number, default: 0 },
})

const page = usePage()
const firstName = computed(() => (page.props.auth?.user?.name ?? '').split(' ')[0] || 'there')

const STATUS_CONFIG = {
    paid: { label: 'Paid', classes: 'bg-neutral-text/8 text-neutral-text/70' },
    processing: { label: 'Processing', classes: 'bg-accent-light/20 text-accent' },
    out_for_delivery: { label: 'Out for delivery', classes: 'bg-primary-light text-primary-dark' },
    ready_for_pickup: { label: 'Ready for pickup', classes: 'bg-primary-light text-primary-dark' },
    completed: { label: 'Completed', classes: 'bg-primary-dark text-white' },
    cancelled: { label: 'Cancelled', classes: 'bg-neutral-text/8 text-neutral-text/40 line-through decoration-neutral-text/30' },
}
const statusOf = (status) => STATUS_CONFIG[status] ?? STATUS_CONFIG.paid

function formatCurrency(value) {
    return new Intl.NumberFormat('en-NG', { style: 'currency', currency: 'NGN', maximumFractionDigits: 0 }).format(value ?? 0)
}
function formatDate(value) {
    if (!value) return ''
    return new Date(value).toLocaleDateString('en-GB', { day: 'numeric', month: 'short' })
}
</script>

<template>
    <Head title="Dashboard" />

    <AccountLayout>
        <h2 class="font-heading text-lg font-semibold tracking-tight text-neutral-text sm:text-xl">
            Welcome back, {{ firstName }}
        </h2>
        <p class="mt-1 text-sm text-neutral-text/50">
            Here's a quick look at your account.
        </p>

        <!-- Quick stats -->
        <div class="mt-6 grid grid-cols-2 gap-3 sm:gap-4">
            <Link
                :href="route('account.orders.index')"
                class="group flex items-center gap-3 rounded-xl border border-neutral-text/10 px-4 py-4 transition-colors duration-200 hover:bg-neutral-bg sm:px-5"
            >
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-primary-light text-primary-dark transition-colors duration-200 group-hover:bg-primary-dark group-hover:text-white">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M3 3h2l2.4 12.4a2 2 0 0 0 2 1.6h7.2a2 2 0 0 0 2-1.6L21 8H6" />
                    </svg>
                </span>
                <div class="min-w-0">
                    <p class="font-heading text-lg font-semibold leading-none text-neutral-text">{{ orderCount }}</p>
                    <p class="mt-1 text-xs text-neutral-text/50">{{ orderCount === 1 ? 'Order' : 'Orders' }}</p>
                </div>
            </Link>

            <Link
                :href="route('account.addresses.index')"
                class="group flex items-center gap-3 rounded-xl border border-neutral-text/10 px-4 py-4 transition-colors duration-200 hover:bg-neutral-bg sm:px-5"
            >
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-primary-light text-primary-dark transition-colors duration-200 group-hover:bg-primary-dark group-hover:text-white">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M12 22s8-7.58 8-13a8 8 0 1 0-16 0c0 5.42 8 13 8 13zM12 12a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5z" />
                    </svg>
                </span>
                <div class="min-w-0">
                    <p class="font-heading text-lg font-semibold leading-none text-neutral-text">{{ addressCount }}</p>
                    <p class="mt-1 text-xs text-neutral-text/50">Saved {{ addressCount === 1 ? 'address' : 'addresses' }}</p>
                </div>
            </Link>
        </div>

        <!-- Recent orders -->
        <div class="mt-8 flex items-baseline justify-between gap-3">
            <h3 class="text-sm font-semibold text-neutral-text">Recent orders</h3>
            <Link
                v-if="recentOrders.length"
                :href="route('account.orders.index')"
                class="text-xs font-medium text-primary-dark transition-colors duration-200 hover:text-primary"
            >
                View all
            </Link>
        </div>

        <div
            v-if="recentOrders.length"
            class="mt-3 overflow-hidden rounded-xl border border-neutral-text/10 divide-y divide-neutral-text/10"
        >
            <Link
                v-for="order in recentOrders"
                :key="order.id"
                :href="route('account.orders.show', order.id)"
                class="group flex items-center gap-4 px-5 py-3.5 transition-colors duration-200 hover:bg-neutral-bg sm:px-6"
            >
                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                        <span class="text-sm font-medium text-neutral-text">#{{ order.order_number ?? order.id }}</span>
                        <span
                            class="inline-flex items-center rounded-md px-2 py-0.5 text-[11px] font-medium"
                            :class="statusOf(order.status).classes"
                        >
                            {{ statusOf(order.status).label }}
                        </span>
                    </div>
                    <p class="mt-0.5 text-xs text-neutral-text/45">{{ formatDate(order.created_at) }}</p>
                </div>

                <div class="flex shrink-0 items-center gap-2.5">
                    <span class="text-sm font-semibold text-neutral-text">{{ formatCurrency(order.total) }}</span>
                    <svg
                        width="14" height="14" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        class="text-neutral-text/30 transition-all duration-200 group-hover:translate-x-0.5 group-hover:text-primary-dark"
                        aria-hidden="true"
                    >
                        <path d="M9 6l6 6-6 6" />
                    </svg>
                </div>
            </Link>
        </div>

        <div
            v-else
            class="mt-3 flex flex-col items-center gap-2 rounded-xl border border-dashed border-neutral-text/15 px-6 py-10 text-center"
        >
            <p class="text-sm font-medium text-neutral-text">No orders yet</p>
            <p class="max-w-xs text-xs text-neutral-text/50">Your recent orders will appear here once you place one.</p>
            <Link
                :href="route('shop.index')"
                class="mt-1 inline-flex items-center rounded-md bg-primary-dark px-4 py-2 text-xs font-medium text-white transition-colors duration-200 hover:bg-primary-dark/90"
            >
                Start shopping
            </Link>
        </div>
    </AccountLayout>
</template>
