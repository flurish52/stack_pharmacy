<script setup>
import { computed, ref } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { naira, formatDate, statusLabel, statusBadge, customerName } from '@/Composables/OrderStatus.js'

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
</script>

<template>
    <Head :title="`Order #${order.id}`" />

    <AdminLayout :title="`Order #${order.id}`">
        <template #actions>
            <span
                class="rounded-full px-3 py-1 text-xs font-medium capitalize"
                :class="statusBadge(order.status)"
            >
                {{ statusLabel(order.status) }}
            </span>
        </template>

        <Link href="/admin/orders" class="mb-4 inline-block text-sm text-gray-500 hover:text-gray-900">
            Back to orders
        </Link>

        <!-- Refund reminder: refunds are manual, so cancelled + paid must be visible -->
        <div
            v-if="needsRefund"
            class="mb-4 rounded-md border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900"
        >
            This order was paid online and is now cancelled. Refunds are handled manually outside the app.
            <span v-if="order.successful_payment.reference">
                Payment reference: <span class="font-medium">{{ order.successful_payment.reference }}</span>.
            </span>
        </div>

        <!-- Actions -->
        <div v-if="hasActions" class="mb-6 flex flex-wrap items-center gap-3 rounded-lg border border-gray-200 bg-white p-4">
            <button
                v-for="status in nextStatuses"
                :key="status"
                type="button"
                :disabled="busy"
                class="rounded-md bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700 disabled:opacity-50"
                @click="moveTo(status)"
            >
                {{ nextLabels[status] ?? statusLabel(status) }}
            </button>

            <button
                v-if="can.markReceived"
                type="button"
                :disabled="busy"
                class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50"
                @click="markReceived"
            >
                Mark received
            </button>

            <button
                v-if="can.cancel"
                type="button"
                :disabled="busy"
                class="ml-auto rounded-md border border-red-200 px-4 py-2 text-sm font-medium text-red-700 hover:bg-red-50 disabled:opacity-50"
                @click="cancelOrder"
            >
                Cancel order
            </button>
        </div>

        <div class="grid gap-6 lg:grid-cols-3">
            <!-- Left: items + history -->
            <div class="space-y-6 lg:col-span-2">
                <div class="rounded-lg border border-gray-200 bg-white">
                    <h2 class="border-b border-gray-200 px-5 py-4 font-semibold">Items</h2>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="text-xs text-gray-500">
                            <tr>
                                <th class="px-5 py-3 font-medium">Product</th>
                                <th class="px-5 py-3 text-right font-medium">Price</th>
                                <th class="px-5 py-3 text-right font-medium">Qty</th>
                                <th class="px-5 py-3 text-right font-medium">Subtotal</th>
                            </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                            <tr v-for="item in order.items" :key="item.id">
                                <td class="px-5 py-3">
                                    <p class="font-medium">{{ item.variant?.product?.name ?? 'Removed product' }}</p>
                                    <p v-if="item.variant?.name" class="text-xs text-gray-400">
                                        {{ item.variant.name }}
                                    </p>
                                </td>
                                <td class="px-5 py-3 text-right">{{ naira.format(item.price) }}</td>
                                <td class="px-5 py-3 text-right">{{ item.quantity }}</td>
                                <td class="px-5 py-3 text-right">{{ naira.format(lineTotal(item)) }}</td>
                            </tr>
                            </tbody>
                            <tfoot>
                            <tr class="border-t border-gray-200">
                                <td colspan="3" class="px-5 py-3 text-right font-medium">Total</td>
                                <td class="px-5 py-3 text-right font-semibold">
                                    {{ naira.format(order.total_amount) }}
                                </td>
                            </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <div class="rounded-lg border border-gray-200 bg-white">
                    <h2 class="border-b border-gray-200 px-5 py-4 font-semibold">Status history</h2>
                    <p v-if="!order.status_history?.length" class="px-5 py-6 text-sm text-gray-500">
                        No status changes recorded yet.
                    </p>
                    <ol v-else class="divide-y divide-gray-100">
                        <li
                            v-for="entry in order.status_history"
                            :key="entry.id"
                            class="flex items-center justify-between gap-4 px-5 py-3 text-sm"
                        >
                            <div class="flex items-center gap-3">
                                <span
                                    class="rounded-full px-2.5 py-1 text-xs font-medium capitalize"
                                    :class="statusBadge(entry.status)"
                                >
                                    {{ statusLabel(entry.status) }}
                                </span>
                                <span class="text-gray-500">
                                    {{ entry.changed_by?.name ? `by ${entry.changed_by.name}` : 'by the system' }}
                                </span>
                            </div>
                            <span class="text-xs text-gray-400">{{ formatDate(entry.created_at) }}</span>
                        </li>
                    </ol>
                </div>
            </div>

            <!-- Right: customer + fulfillment -->
            <div class="space-y-6">
                <div class="rounded-lg border border-gray-200 bg-white p-5 text-sm">
                    <h2 class="mb-3 font-semibold">Customer</h2>
                    <p class="font-medium">{{ customerName(order) }}</p>
                    <p class="text-gray-500">{{ order.user?.email ?? order.guest_email }}</p>
                    <p v-if="order.guest_phone" class="text-gray-500">{{ order.guest_phone }}</p>
                    <p v-if="!order.user_id" class="mt-2 text-xs text-gray-400">Guest checkout</p>
                </div>

                <div class="rounded-lg border border-gray-200 bg-white p-5 text-sm">
                    <h2 class="mb-3 font-semibold capitalize">{{ order.fulfillment_type }}</h2>

                    <template v-if="order.fulfillment_type === 'pickup'">
                        <p class="font-medium">{{ order.pickup_point?.name ?? 'Pickup point removed' }}</p>
                        <p class="text-gray-500">{{ order.pickup_point?.address }}</p>
                    </template>

                    <template v-else>
                        <p class="whitespace-pre-line text-gray-700">{{ order.delivery_address }}</p>
                    </template>

                    <p v-if="order.received_at" class="mt-4 border-t border-gray-100 pt-3 text-xs text-gray-500">
                        Received {{ formatDate(order.received_at) }}
                        <span v-if="order.received_by?.name">, confirmed by {{ order.received_by.name }}</span>
                    </p>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
