<script setup>
import { Link } from '@inertiajs/vue3'
import AccountLayout from '@/Layouts/AccountLayout.vue'

defineOptions({ layout: AccountLayout })

const props = defineProps({
    user: { type: Object, required: true },
    recentOrder: { type: Object, default: null },
    defaultAddress: { type: Object, default: null },
    ordersCount: { type: Number, default: 0 },
})

function formatNaira(value) {
    return new Intl.NumberFormat('en-NG', { style: 'currency', currency: 'NGN', maximumFractionDigits: 0 }).format(value)
}
</script>

<template>
    <div>
        <h2 class="font-heading text-lg font-semibold text-neutral-text">Welcome back, {{ user.name }}</h2>

        <!-- Continue shopping — the primary action on this page -->
        <div class="mt-4 flex flex-col items-start justify-between gap-4 rounded-xl bg-primary-light p-5 sm:flex-row sm:items-center">
            <div>
                <p class="font-medium text-primary-dark">Need to reorder or pick up something new?</p>
                <p class="mt-0.5 text-sm text-primary-dark/70">Browse the shop and check out in a couple of taps.</p>
            </div>
            <Link
                :href="route('shop.index')"
                class="shrink-0 rounded-md bg-primary-dark px-4 py-2 text-sm font-medium text-white transition-colors hover:opacity-90"
            >
                Continue shopping
            </Link>
        </div>

        <div class="mt-6 grid gap-4 sm:grid-cols-2">
            <!-- Most recent order -->
            <div class="rounded-xl border border-neutral-text/10 p-4">
                <p class="text-sm font-medium text-neutral-text/80">Most recent order</p>

                <template v-if="recentOrder">
                    <p class="mt-1.5 text-neutral-text">
                        #{{ recentOrder.id }} — {{ formatNaira(recentOrder.total_amount) }}
                        <span class="ml-1 text-sm capitalize text-neutral-text/60">({{ recentOrder.status }})</span>
                    </p>
                    <Link :href="route('account.orders.show', recentOrder.id)" class="mt-2 inline-block text-sm font-medium text-primary-dark hover:underline">
                        View order
                    </Link>
                </template>
                <p v-else class="mt-1.5 text-sm text-neutral-text/60">No orders yet.</p>

                <Link :href="route('account.orders.index')" class="mt-3 block text-sm text-neutral-text/60 hover:text-neutral-text">
                    View all orders ({{ ordersCount }}) →
                </Link>
            </div>

            <!-- Default address -->
            <div class="rounded-xl border border-neutral-text/10 p-4">
                <p class="text-sm font-medium text-neutral-text/80">Default address</p>

                <template v-if="defaultAddress">
                    <p class="mt-1.5 text-sm text-neutral-text/70">{{ defaultAddress.address }}</p>
                    <p class="text-sm text-neutral-text/70">{{ defaultAddress.phone }}</p>
                </template>
                <p v-else class="mt-1.5 text-sm text-neutral-text/60">You haven't added an address yet.</p>

                <Link :href="route('account.addresses.index')" class="mt-3 block text-sm font-medium text-primary-dark hover:underline">
                    Manage addresses →
                </Link>
            </div>
        </div>
    </div>
</template>
