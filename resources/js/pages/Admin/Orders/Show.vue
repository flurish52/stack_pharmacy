<script setup>
import { computed, ref } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import StatusBadge from '@/Components/StatusBadge.vue'
import { naira, formatDate, statusLabel, customerName } from '@/Composables/OrderStatus.js'

const props = defineProps({
    order: { type: Object, required: true },
    can: { type: Object, required: true },
})

const busy = ref(false)

// Button text for each status staff can move an order to.
const nextLabels = {
    processing: 'Start processing',
    out_for_delivery: 'Send out for delivery',
    ready_for_pickup: 'Mark ready for pickup',
    completed: 'Mark completed',
}

const nextStatuses = computed(() => (props.can.updateStatus ? props.order.allowed_next_statuses ?? [] : []))

const run = (method, url, data = {}) => {
    busy.value = true
    router[method](url, data, {
        preserveScroll: true,
        onFinish: () => (busy.value = false),
    })
}

const moveTo = (status) => run('patch', `/admin/orders/${props.order.id}/status`, { status })

const markReceived = () => {
    if (confirm('Confirm that the customer has received this order?')) {
        run('patch', `/admin/orders/${props.order.id}/received`)
    }
}

const cancelOrder = () => {
    if (confirm(`Cancel order #${props.order.id}? This cannot be undone.`)) {
        run('patch', `/admin/orders/${props.order.id}/cancel`)
    }
}

const lineTotal = (item) => Number(item.price) * item.quantity

const needsRefund = computed(() => props.order.status === 'cancelled' && !!props.order.successful_payment)

const hasActions = computed(() => nextStatuses.value.length > 0 || props.can.markReceived || props.can.cancel)

const itemCount = computed(() => (props.order.items ?? []).reduce((sum, i) => sum + i.quantity, 0))

const card = 'rounded-xl border border-neutral-text/10 bg-white'
const cardHead = 'flex items-center justify-between border-b border-neutral-text/10 bg-neutral-bg px-5 py-3.5'
const cardTitle = 'font-heading text-sm font-semibold text-neutral-text'
</script>

<template>
    <Head :title="`Order #${order.id}`" />

    <AdminLayout :title="`Order #${order.id}`">
        <template #actions>
            <StatusBadge :status="order.status" />
        </template>

        <div class="show-in">
            <Link
                href="/admin/orders"
                class="group mb-4 inline-flex items-center gap-1.5 rounded-lg border border-neutral-text/15 bg-white px-3 py-1.5 text-sm font-medium text-neutral-text transition hover:border-primary/40 hover:bg-primary-light hover:text-primary-dark focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/40"
            >
                <svg class="h-4 w-4 transition-transform duration-150 group-hover:-translate-x-0.5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M16 10H5M9 5l-5 5 5 5" />
                </svg>
                All orders
            </Link>

            <!-- Refund reminder: refunds are manual, so cancelled + paid must be visible -->
            <div
                v-if="needsRefund"
                class="mb-4 flex gap-3 rounded-xl border border-accent/30 bg-accent/[0.06] px-4 py-3 text-sm text-neutral-text"
                role="alert"
            >
                <svg class="mt-0.5 h-4 w-4 shrink-0 text-accent" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <circle cx="10" cy="10" r="7.5" /><path d="M10 6.5v4M10 13.5h.01" />
                </svg>
                <p>
                    This order was paid online and is now cancelled. Refunds are handled manually outside the app.
                    <span v-if="order.successful_payment.reference">
                        Payment reference:
                        <span class="rounded bg-white px-1.5 py-0.5 font-mono text-xs font-medium">{{ order.successful_payment.reference }}</span>
                    </span>
                </p>
            </div>

            <!-- Actions -->
            <div v-if="hasActions" :class="[card, 'mb-6 flex flex-wrap items-center gap-3 p-4']">
                <button
                    v-for="status in nextStatuses"
                    :key="status"
                    type="button"
                    :disabled="busy"
                    class="rounded-lg bg-primary px-4 py-2 text-sm font-medium text-white transition hover:bg-primary-dark focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/40 focus-visible:ring-offset-2 active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-50"
                    @click="moveTo(status)"
                >
                    {{ nextLabels[status] ?? statusLabel(status) }}
                </button>

                <button
                    v-if="can.markReceived"
                    type="button"
                    :disabled="busy"
                    class="rounded-lg border border-primary/40 bg-white px-4 py-2 text-sm font-medium text-primary-dark transition hover:bg-primary-light focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/40 active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-50"
                    @click="markReceived"
                >
                    Mark received
                </button>

                <button
                    v-if="can.cancel"
                    type="button"
                    :disabled="busy"
                    class="rounded-lg border border-accent-light/60 bg-white px-4 py-2 text-sm font-medium text-accent transition hover:bg-accent-light/10 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-accent-light/40 active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-50 sm:ml-auto"
                    @click="cancelOrder"
                >
                    Cancel order
                </button>
            </div>

            <div class="grid gap-6 lg:grid-cols-3">
                <!-- Left: items + history -->
                <div class="space-y-6 lg:col-span-2">
                    <section :class="[card, 'overflow-hidden']">
                        <header :class="cardHead">
                            <h2 :class="cardTitle">Items</h2>
                            <span class="text-xs text-neutral-text/55">{{ itemCount }} {{ itemCount === 1 ? 'item' : 'items' }}</span>
                        </header>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-sm">
                                <thead class="border-b border-neutral-text/10 text-xs text-neutral-text/55">
                                <tr>
                                    <th class="px-5 py-3 font-medium">Product</th>
                                    <th class="px-5 py-3 text-right font-medium">Price</th>
                                    <th class="px-5 py-3 text-right font-medium">Qty</th>
                                    <th class="px-5 py-3 text-right font-medium">Subtotal</th>
                                </tr>
                                </thead>
                                <tbody class="divide-y divide-neutral-text/[0.07]">
                                <tr v-for="item in order.items" :key="item.id" class="transition-colors hover:bg-primary-light/60">
                                    <td class="px-5 py-3.5">
                                        <p class="font-medium text-neutral-text">{{ item.variant?.product?.name ?? 'Removed product' }}</p>
                                        <p v-if="item.variant?.name" class="text-xs text-neutral-text/50">{{ item.variant.name }}</p>
                                    </td>
                                    <td class="px-5 py-3.5 text-right tabular-nums text-neutral-text/70">{{ naira.format(item.price) }}</td>
                                    <td class="px-5 py-3.5 text-right tabular-nums text-neutral-text/70">{{ item.quantity }}</td>
                                    <td class="px-5 py-3.5 text-right font-medium tabular-nums text-neutral-text">{{ naira.format(lineTotal(item)) }}</td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="flex items-center justify-between border-t border-neutral-text/10 bg-secondary/40 px-5 py-4">
                            <span class="text-sm font-medium text-neutral-text">Order total</span>
                            <span class="font-heading text-lg font-semibold tabular-nums text-primary-dark">{{ naira.format(order.total_amount) }}</span>
                        </div>
                    </section>

                    <section :class="[card, 'overflow-hidden']">
                        <header :class="cardHead">
                            <h2 :class="cardTitle">Status history</h2>
                        </header>
                        <p v-if="!order.status_history?.length" class="px-5 py-8 text-center text-sm text-neutral-text/55">
                            No status changes recorded yet.
                        </p>
                        <ol v-else class="px-5 py-5">
                            <li
                                v-for="(entry, i) in order.status_history"
                                :key="entry.id"
                                class="relative flex gap-4 pb-6 last:pb-0"
                            >
                                <!-- connector line -->
                                <span
                                    v-if="i !== order.status_history.length - 1"
                                    class="absolute left-[7px] top-4 h-full w-px bg-neutral-text/10"
                                    aria-hidden="true"
                                />
                                <span class="relative mt-1 flex h-4 w-4 shrink-0 items-center justify-center" aria-hidden="true">
                                    <span v-if="i === 0" class="absolute inline-flex h-full w-full animate-ping rounded-full bg-primary opacity-30" />
                                    <span
                                        class="relative h-3 w-3 rounded-full border-2 bg-white"
                                        :class="i === 0 ? 'border-primary bg-primary' : 'border-neutral-text/25'"
                                    />
                                </span>
                                <div class="flex min-w-0 flex-1 flex-wrap items-center justify-between gap-x-4 gap-y-1">
                                    <div class="flex flex-wrap items-center gap-2.5">
                                        <StatusBadge :status="entry.status" />
                                        <span class="text-sm text-neutral-text/60">
                                            {{ entry.changed_by?.name ? `by ${entry.changed_by.name}` : 'by the system' }}
                                        </span>
                                    </div>
                                    <span class="text-xs text-neutral-text/50">{{ formatDate(entry.created_at) }}</span>
                                </div>
                            </li>
                        </ol>
                    </section>
                </div>

                <!-- Right: customer + fulfillment -->
                <div class="space-y-6">
                    <section :class="[card, 'overflow-hidden text-sm']">
                        <header :class="cardHead"><h2 :class="cardTitle">Customer</h2></header>
                        <div class="p-5">
                            <p class="font-medium text-neutral-text">{{ customerName(order) }}</p>
                            <p class="mt-0.5 break-all text-neutral-text/60">{{ order.user?.email ?? order.guest_email }}</p>
                            <p v-if="order.guest_phone" class="text-neutral-text/60">{{ order.guest_phone }}</p>
                            <p
                                v-if="!order.user_id"
                                class="mt-3 inline-block rounded-full bg-neutral-text/[0.07] px-2.5 py-1 text-xs font-medium text-neutral-text/65"
                            >
                                Guest checkout
                            </p>
                        </div>
                    </section>

                    <section :class="[card, 'overflow-hidden text-sm']">
                        <header :class="cardHead">
                            <h2 :class="[cardTitle, 'capitalize']">{{ order.fulfillment_type }}</h2>
                        </header>
                        <div class="p-5">
                            <template v-if="order.fulfillment_type === 'pickup'">
                                <p class="font-medium text-neutral-text">{{ order.pickup_point?.name ?? 'Pickup point removed' }}</p>
                                <p class="mt-0.5 text-neutral-text/60">{{ order.pickup_point?.address }}</p>
                            </template>
                            <p v-else class="whitespace-pre-line text-neutral-text/80">{{ order.delivery_address }}</p>

                            <p v-if="order.received_at" class="mt-4 border-t border-neutral-text/10 pt-3 text-xs text-neutral-text/55">
                                Received {{ formatDate(order.received_at) }}
                                <span v-if="order.received_by?.name">, confirmed by {{ order.received_by.name }}</span>
                            </p>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
/* One gentle entrance for the page, nothing else moves on its own. */
.show-in {
    animation: show-in 0.3s ease-out both;
}

@keyframes show-in {
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
    .show-in {
        animation: none;
    }
}
</style>
