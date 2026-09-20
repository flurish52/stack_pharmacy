<script setup>
import { reactive, watch } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { naira, formatDate, statusLabel, statusBadge, customerName } from '@/Composables/OrderStatus.js'

const props = defineProps({
    orders: { type: Object, required: true },
    filters: { type: Object, required: true },
    statuses: { type: Array, default: () => [] },
})

const form = reactive({
    search: props.filters.search,
    status: props.filters.status,
    fulfillment_type: props.filters.fulfillment_type,
})

let timer = null

const apply = () => {
    router.get('/admin/orders', form, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    })
}

// Selects apply immediately; the search box waits until typing pauses.
watch(() => [form.status, form.fulfillment_type], apply)
watch(
    () => form.search,
    () => {
        clearTimeout(timer)
        timer = setTimeout(apply, 350)
    },
)

const hasFilters = () => form.search || form.status || form.fulfillment_type

const reset = () => {
    form.search = ''
    form.status = ''
    form.fulfillment_type = ''
}
</script>

<template>
    <Head title="Orders" />

    <AdminLayout title="Orders">
        <!-- Filters -->
        <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center">
            <input
                v-model="form.search"
                type="search"
                placeholder="Search by order number, name, email or phone"
                class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500 sm:max-w-sm"
            />

            <select
                v-model="form.status"
                class="rounded-md border border-gray-300 px-3 py-2 text-sm capitalize focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
            >
                <option value="">All statuses</option>
                <option v-for="s in statuses" :key="s" :value="s">{{ statusLabel(s) }}</option>
            </select>

            <select
                v-model="form.fulfillment_type"
                class="rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
            >
                <option value="">Pickup and delivery</option>
                <option value="pickup">Pickup</option>
                <option value="delivery">Delivery</option>
            </select>

            <button
                v-if="hasFilters()"
                type="button"
                class="text-sm text-gray-500 hover:text-gray-900"
                @click="reset"
            >
                Clear filters
            </button>
        </div>

        <!-- Table -->
        <div class="rounded-lg border border-gray-200 bg-white">
            <p v-if="orders.data.length === 0" class="px-5 py-10 text-center text-sm text-gray-500">
                No orders match these filters.
            </p>

            <div v-else class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-gray-200 text-xs text-gray-500">
                    <tr>
                        <th class="px-5 py-3 font-medium">Order</th>
                        <th class="px-5 py-3 font-medium">Customer</th>
                        <th class="px-5 py-3 font-medium">Fulfillment</th>
                        <th class="px-5 py-3 font-medium">Status</th>
                        <th class="px-5 py-3 text-right font-medium">Total</th>
                        <th class="px-5 py-3 font-medium">Placed</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                    <tr v-for="order in orders.data" :key="order.id" class="hover:bg-gray-50">
                        <td class="px-5 py-3">
                            <Link
                                :href="`/admin/orders/${order.id}`"
                                class="font-medium text-emerald-700 hover:underline"
                            >
                                #{{ order.id }}
                            </Link>
                        </td>
                        <td class="px-5 py-3">
                            <p>{{ customerName(order) }}</p>
                            <p class="text-xs text-gray-400">
                                {{ order.user?.email ?? order.guest_email }}
                            </p>
                        </td>
                        <td class="px-5 py-3">
                            <p class="capitalize">{{ order.fulfillment_type }}</p>
                            <p v-if="order.pickup_point" class="text-xs text-gray-400">
                                {{ order.pickup_point.name }}
                            </p>
                        </td>
                        <td class="px-5 py-3">
                                <span
                                    class="rounded-full px-2.5 py-1 text-xs font-medium capitalize"
                                    :class="statusBadge(order.status)"
                                >
                                    {{ statusLabel(order.status) }}
                                </span>
                        </td>
                        <td class="px-5 py-3 text-right">{{ naira.format(order.total_amount) }}</td>
                        <td class="px-5 py-3 text-gray-500">{{ formatDate(order.created_at) }}</td>
                    </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div
                v-if="orders.last_page > 1"
                class="flex flex-wrap items-center justify-between gap-3 border-t border-gray-200 px-5 py-3"
            >
                <p class="text-xs text-gray-500">
                    Showing {{ orders.from }} to {{ orders.to }} of {{ orders.total }} orders
                </p>
                <div class="flex flex-wrap gap-1">
                    <template v-for="link in orders.links" :key="link.label">
                        <Link
                            v-if="link.url"
                            :href="link.url"
                            preserve-scroll
                            class="rounded-md px-3 py-1.5 text-sm"
                            :class="link.active
                                ? 'bg-emerald-600 text-white'
                                : 'text-gray-600 hover:bg-gray-100'"
                            v-html="link.label"
                        />
                        <span
                            v-else
                            class="rounded-md px-3 py-1.5 text-sm text-gray-300"
                            v-html="link.label"
                        />
                    </template>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
