<script setup>
import { computed } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import StatCard from '@/Components/Admin/StatCard.vue'
import StatusBadge from '@/Components/StatusBadge.vue'

const props = defineProps({
    statusCounts: { type: Object, required: true },
    recentOrders: { type: Array, default: () => [] },
    lowStock: { type: Number, default: 0 },
    revenue: { type: Number, default: null }, // null = user isn't allowed to see revenue
})

const naira = new Intl.NumberFormat('en-NG', {
    style: 'currency',
    currency: 'NGN',
    maximumFractionDigits: 0,
})

const formatDate = (iso) =>
    new Date(iso).toLocaleDateString('en-NG', { day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' })

const openOrder = (id) => router.visit(`/admin/orders/${id}`)

/* ------------------------------------------------------------------ */
/* Needs attention                                                     */
/* ------------------------------------------------------------------ */

const waiting = computed(() => Number(props.statusCounts.paid ?? 0))

// The four states that need staff attention right now
const needsAction = [
    {
        key: 'paid',
        title: 'New (paid)',
        hint: 'Waiting to be processed',
        tone: 'brand',
        icon: '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3.5 2"/>',
    },
    {
        key: 'processing',
        title: 'Processing',
        hint: 'Being prepared',
        tone: 'accent',
        icon: '<circle cx="12" cy="12" r="3"/><path d="M12 3v3.2M12 17.8V21M21 12h-3.2M6.2 12H3M17.9 6.1l-2.3 2.3M8.4 15.6l-2.3 2.3M17.9 17.9l-2.3-2.3M8.4 8.4L6.1 6.1"/>',
    },
    {
        key: 'ready_for_pickup',
        title: 'Ready for pickup',
        hint: 'Waiting for customer',
        tone: 'mint',
        icon: '<path d="M3.5 8l8.5-4.6L20.5 8 12 12.6 3.5 8z"/><path d="M3.5 8v8l8.5 4.6 8.5-4.6V8"/><path d="M12 12.6V21"/>',
    },
    {
        key: 'out_for_delivery',
        title: 'Out for delivery',
        hint: 'With rider',
        tone: 'mint',
        icon: '<rect x="1.5" y="7" width="13" height="9" rx="1"/><path d="M14.5 10.5h4l3 3V16h-7z"/><circle cx="6" cy="18" r="1.7"/><circle cx="16.5" cy="18" r="1.7"/>',
    },
]

const card = 'rounded-xl border border-neutral-text/10 bg-white'
const cardHead = 'flex items-center justify-between gap-3 border-b border-neutral-text/10 bg-neutral-bg px-5 py-3.5'
const cardTitle = 'font-heading text-sm font-semibold text-neutral-text'
</script>

<template>
    <Head title="Dashboard" />

    <AdminLayout title="Dashboard">
        <div class="page-in">
            <!-- Attention strip -->
            <div
                class="mb-6 flex flex-wrap items-center justify-between gap-3 rounded-xl border border-primary/20 bg-primary-light px-4 py-3"
            >
                <p class="flex items-center gap-2.5 text-sm text-neutral-text">
                    <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-white text-primary-dark">
                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path v-if="waiting > 0" d="M10 5.5V10l2.5 2.5M10 2.5a7.5 7.5 0 1 0 0 15 7.5 7.5 0 0 0 0-15Z" />
                            <path v-else d="m6 10.5 2.75 2.75L14 7.5" />
                        </svg>
                    </span>
                    <span v-if="waiting > 0">
                        <span class="font-semibold tabular-nums">{{ waiting }}</span>
                        new {{ waiting === 1 ? 'order is' : 'orders are' }} waiting to be processed.
                    </span>
                    <span v-else>You're all caught up. No new orders are waiting.</span>
                </p>

                <Link
                    v-if="waiting > 0"
                    href="/admin/orders?status=paid"
                    class="inline-flex items-center gap-1 rounded-lg bg-white px-3 py-1.5 text-sm font-medium text-primary-dark shadow-sm transition hover:bg-secondary focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/40 active:scale-[0.98]"
                >
                    Review orders
                    <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="m8 5 5 5-5 5" />
                    </svg>
                </Link>
            </div>

            <!-- Order status cards -->
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <StatCard
                    v-for="item in needsAction"
                    :key="item.key"
                    :href="`/admin/orders?status=${item.key}`"
                    :title="item.title"
                    :value="Number(statusCounts[item.key] ?? 0)"
                    :hint="item.hint"
                    :tone="item.tone"
                >
                    <template #icon>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" v-html="item.icon" />
                    </template>
                </StatCard>
            </div>

            <div class="mt-6 grid gap-6 lg:grid-cols-3">
                <!-- Recent orders -->
                <section :class="[card, 'overflow-hidden lg:col-span-2']">
                    <header :class="cardHead">
                        <h2 :class="cardTitle">Recent orders</h2>
                        <Link
                            href="/admin/orders"
                            class="inline-flex items-center gap-1 rounded-lg px-2.5 py-1 text-xs font-medium text-primary-dark transition hover:bg-primary-light focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/40"
                        >
                            View all
                            <svg class="h-3 w-3" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="m8 5 5 5-5 5" />
                            </svg>
                        </Link>
                    </header>

                    <div v-if="recentOrders.length === 0" class="px-5 py-14 text-center">
                        <p class="font-heading font-medium text-neutral-text">No orders yet</p>
                        <p class="mt-1 text-sm text-neutral-text/55">New orders will show up here as customers check out.</p>
                    </div>

                    <div v-else class="overflow-x-auto">
                        <table class="w-full min-w-[40rem] text-left text-sm">
                            <thead class="border-b border-neutral-text/10 text-xs text-neutral-text/55">
                            <tr>
                                <th class="px-5 py-3 font-medium">Order</th>
                                <th class="px-5 py-3 font-medium">Customer</th>
                                <th class="px-5 py-3 font-medium">Type</th>
                                <th class="px-5 py-3 font-medium">Status</th>
                                <th class="px-5 py-3 text-right font-medium">Total</th>
                                <th class="px-5 py-3 font-medium">Placed</th>
                                <th class="w-10 py-3 pr-4"><span class="sr-only">Open</span></th>
                            </tr>
                            </thead>
                            <tbody class="divide-y divide-neutral-text/[0.07]">
                            <tr
                                v-for="order in recentOrders"
                                :key="order.id"
                                class="group cursor-pointer transition-colors hover:bg-primary-light/60"
                                @click="openOrder(order.id)"
                            >
                                <td class="px-5 py-3.5">
                                    <Link
                                        :href="`/admin/orders/${order.id}`"
                                        class="rounded-md bg-primary/10 px-2 py-0.5 font-medium tabular-nums text-primary-dark transition-colors group-hover:bg-secondary focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/40"
                                        @click.stop
                                    >
                                        #{{ order.id }}
                                    </Link>
                                </td>
                                <td class="px-5 py-3.5 font-medium text-neutral-text">{{ order.customer }}</td>
                                <td class="px-5 py-3.5 capitalize text-neutral-text/70">{{ order.fulfillment_type }}</td>
                                <td class="px-5 py-3.5"><StatusBadge :status="order.status" /></td>
                                <td class="px-5 py-3.5 text-right font-medium tabular-nums text-neutral-text">
                                    {{ naira.format(order.total_amount) }}
                                </td>
                                <td class="whitespace-nowrap px-5 py-3.5 tabular-nums text-neutral-text/55">
                                    {{ formatDate(order.created_at) }}
                                </td>
                                <td class="py-3.5 pr-4 text-right">
                                    <svg
                                        class="ml-auto h-4 w-4 text-neutral-text/25 transition duration-200 group-hover:translate-x-0.5 group-hover:text-primary-dark motion-reduce:transition-none"
                                        viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"
                                    >
                                        <path d="m8 5 5 5-5 5" />
                                    </svg>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </section>

                <!-- Right column: headline metric + stock alert (first on small screens) -->
                <div class="order-first space-y-4 lg:order-none">
                    <!-- Only rendered when the server sent a revenue figure (view-reports) -->
                    <StatCard
                        v-if="revenue !== null"
                        href="/admin/reports"
                        title="Revenue this month"
                        :value="revenue"
                        :format="naira.format"
                        hint="Excludes cancelled and unpaid orders"
                        tone="dark"
                    >
                        <template #icon>
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <rect x="2.5" y="6" width="19" height="13" rx="2" />
                                <path d="M2.5 10.5h19" />
                                <path d="M6.5 15h3" />
                            </svg>
                        </template>
                    </StatCard>

                    <StatCard
                        title="Low-stock variants"
                        :value="lowStock"
                        hint="At or below the low-stock threshold"
                        :tone="lowStock > 0 ? 'danger' : 'mint'"
                        :pulse="lowStock > 0"
                    >
                        <template #icon>
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M12 3.5L21.5 20h-19L12 3.5z" />
                                <path d="M12 10v4.2" />
                                <circle cx="12" cy="17.2" r="0.9" fill="currentColor" stroke="none" />
                            </svg>
                        </template>
                    </StatCard>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
/* One gentle entrance for the page, nothing else moves on its own. */
.page-in {
    animation: page-in 0.3s ease-out both;
}

@keyframes page-in {
    from {
        opacity: 0;
        transform: translateY(6px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@media (prefers-reduced-motion: reduce) {
    .page-in {
        animation: none;
    }
}
</style>
