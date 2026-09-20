<script setup>
import { computed } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

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

const label = (status) => status.replaceAll('_', ' ')

const statusStyles = {
    paid: 'bg-blue-50 text-blue-700',
    processing: 'bg-amber-50 text-amber-700',
    out_for_delivery: 'bg-indigo-50 text-indigo-700',
    ready_for_pickup: 'bg-indigo-50 text-indigo-700',
    completed: 'bg-emerald-50 text-emerald-700',
    received: 'bg-emerald-50 text-emerald-700',
    cancelled: 'bg-red-50 text-red-700',
}
const badge = (status) => statusStyles[status] ?? 'bg-gray-100 text-gray-700'

// The three states that need staff attention right now
const needsAction = computed(() => [
    { key: 'paid', title: 'New (paid)', hint: 'Waiting to be processed' },
    { key: 'processing', title: 'Processing', hint: 'Being prepared' },
    { key: 'ready_for_pickup', title: 'Ready for pickup', hint: 'Waiting for customer' },
    { key: 'out_for_delivery', title: 'Out for delivery', hint: 'With rider' },
])
</script>

<template>
    <Head title="Dashboard" />

    <AdminLayout title="Dashboard">
        <!-- Top cards -->
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <Link
                v-for="card in needsAction"
                :key="card.key"
                :href="`/admin/orders?status=${card.key}`"
                class="rounded-lg border border-gray-200 bg-white p-5 transition hover:border-emerald-300"
            >
                <p class="text-sm text-gray-500">{{ card.title }}</p>
                <p class="mt-2 text-3xl font-semibold">{{ statusCounts[card.key] ?? 0 }}</p>
                <p class="mt-1 text-xs text-gray-400">{{ card.hint }}</p>
            </Link>
        </div>

        <div class="mt-4 grid gap-4 sm:grid-cols-2">
            <div
                class="rounded-lg border p-5"
                :class="lowStock > 0 ? 'border-amber-200 bg-amber-50' : 'border-gray-200 bg-white'"
            >
                <p class="text-sm text-gray-500">Low-stock variants</p>
                <p class="mt-2 text-3xl font-semibold">{{ lowStock }}</p>
                <p class="mt-1 text-xs text-gray-400">At or below the low-stock threshold</p>
            </div>

            <!-- Only rendered when the server sent a revenue figure (view-reports) -->
            <div v-if="revenue !== null" class="rounded-lg border border-gray-200 bg-white p-5">
                <p class="text-sm text-gray-500">Revenue this month</p>
                <p class="mt-2 text-3xl font-semibold">{{ naira.format(revenue) }}</p>
                <p class="mt-1 text-xs text-gray-400">Excludes cancelled and unpaid orders</p>
            </div>
        </div>

        <!-- Recent orders -->
        <div class="mt-8 rounded-lg border border-gray-200 bg-white">
            <div class="flex items-center justify-between border-b border-gray-200 px-5 py-4">
                <h2 class="font-semibold">Recent orders</h2>
                <Link href="/admin/orders" class="text-sm text-emerald-700 hover:underline">View all</Link>
            </div>

            <p v-if="recentOrders.length === 0" class="px-5 py-8 text-center text-sm text-gray-500">
                No orders yet.
            </p>

            <div v-else class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="text-xs uppercase text-gray-400">
                    <tr>
                        <th class="px-5 py-3 font-medium">Order</th>
                        <th class="px-5 py-3 font-medium">Customer</th>
                        <th class="px-5 py-3 font-medium">Type</th>
                        <th class="px-5 py-3 font-medium">Status</th>
                        <th class="px-5 py-3 text-right font-medium">Total</th>
                        <th class="px-5 py-3 font-medium">Placed</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                    <tr v-for="order in recentOrders" :key="order.id" class="hover:bg-gray-50">
                        <td class="px-5 py-3">
                            <Link :href="`/admin/orders/${order.id}`" class="font-medium text-emerald-700 hover:underline">
                                #{{ order.id }}
                            </Link>
                        </td>
                        <td class="px-5 py-3">{{ order.customer }}</td>
                        <td class="px-5 py-3 capitalize">{{ order.fulfillment_type }}</td>
                        <td class="px-5 py-3">
                <span class="rounded-full px-2.5 py-1 text-xs font-medium capitalize" :class="badge(order.status)">
                  {{ label(order.status) }}
                </span>
                        </td>
                        <td class="px-5 py-3 text-right">{{ naira.format(order.total_amount) }}</td>
                        <td class="px-5 py-3 text-gray-500">{{ formatDate(order.created_at) }}</td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AdminLayout>
</template>
