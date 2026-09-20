<script setup>
import { computed, reactive } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { naira, statusLabel } from '@/Composables/OrderStatus.js'

const props = defineProps({
    period: { type: String, required: true },
    from: { type: String, required: true },
    to: { type: String, required: true },
    summary: { type: Object, required: true },
    series: { type: Array, default: () => [] },
    topProducts: { type: Array, default: () => [] },
    fulfillment: { type: Array, default: () => [] },
    statusBreakdown: { type: Array, default: () => [] },
})

const presets = [
    { value: 'today', label: 'Today' },
    { value: '7d', label: 'Last 7 days' },
    { value: '30d', label: 'Last 30 days' },
    { value: 'this_month', label: 'This month' },
    { value: 'last_month', label: 'Last month' },
]

const custom = reactive({ from: props.from, to: props.to })

const go = (params) => router.get('/admin/reports', params, { preserveState: true, preserveScroll: true })

const applyCustom = () => {
    if (custom.from && custom.to) go({ period: 'custom', from: custom.from, to: custom.to })
}

// --- revenue chart (plain SVG, no chart library) ---
const W = 600
const H = 160

const maxRevenue = computed(() => Math.max(1, ...props.series.map((d) => d.revenue)))
const barWidth = computed(() => W / Math.max(1, props.series.length))
const barHeight = (day) => (day.revenue / maxRevenue.value) * (H - 10)

const shortDate = (iso) =>
    new Date(`${iso}T00:00:00`).toLocaleDateString('en-NG', { day: 'numeric', month: 'short' })

const change = (value) => {
    if (value === null || value === undefined) return null
    return { text: `${value > 0 ? '+' : ''}${value}% vs previous period`, up: value >= 0 }
}

const totalUnits = computed(() => props.topProducts.reduce((sum, p) => sum + Number(p.units), 0))
</script>

<template>
    <Head title="Reports" />

    <AdminLayout title="Reports">
        <!-- Period -->
        <div class="mb-6 flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
            <div class="flex flex-wrap gap-1">
                <button
                    v-for="preset in presets"
                    :key="preset.value"
                    type="button"
                    class="rounded-md px-3 py-1.5 text-sm"
                    :class="period === preset.value ? 'bg-emerald-600 text-white' : 'border border-gray-300 text-gray-600 hover:bg-gray-50'"
                    @click="go({ period: preset.value })"
                >
                    {{ preset.label }}
                </button>
            </div>

            <div class="flex items-end gap-2">
                <div>
                    <label class="mb-1 block text-xs text-gray-500">From</label>
                    <input v-model="custom.from" type="date" class="rounded-md border border-gray-300 px-3 py-1.5 text-sm" />
                </div>
                <div>
                    <label class="mb-1 block text-xs text-gray-500">To</label>
                    <input v-model="custom.to" type="date" class="rounded-md border border-gray-300 px-3 py-1.5 text-sm" />
                </div>
                <button
                    type="button"
                    class="rounded-md border border-gray-300 px-3 py-1.5 text-sm text-gray-700 hover:bg-gray-50"
                    @click="applyCustom"
                >
                    Apply
                </button>
            </div>
        </div>

        <!-- Summary -->
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-lg border border-gray-200 bg-white p-5">
                <p class="text-sm text-gray-500">Revenue</p>
                <p class="mt-2 text-3xl font-semibold">{{ naira.format(summary.revenue) }}</p>
                <p
                    v-if="change(summary.revenue_change)"
                    class="mt-1 text-xs"
                    :class="change(summary.revenue_change).up ? 'text-emerald-600' : 'text-red-600'"
                >
                    {{ change(summary.revenue_change).text }}
                </p>
            </div>
            <div class="rounded-lg border border-gray-200 bg-white p-5">
                <p class="text-sm text-gray-500">Orders</p>
                <p class="mt-2 text-3xl font-semibold">{{ summary.orders }}</p>
                <p
                    v-if="change(summary.orders_change)"
                    class="mt-1 text-xs"
                    :class="change(summary.orders_change).up ? 'text-emerald-600' : 'text-red-600'"
                >
                    {{ change(summary.orders_change).text }}
                </p>
            </div>
            <div class="rounded-lg border border-gray-200 bg-white p-5">
                <p class="text-sm text-gray-500">Average order</p>
                <p class="mt-2 text-3xl font-semibold">{{ naira.format(summary.average) }}</p>
            </div>
            <div class="rounded-lg border border-gray-200 bg-white p-5">
                <p class="text-sm text-gray-500">Cancelled orders</p>
                <p class="mt-2 text-3xl font-semibold">{{ summary.cancelled }}</p>
            </div>
        </div>
        <p class="mt-2 text-xs text-gray-400">
            Revenue and orders count paid orders only. Unpaid and cancelled orders are left out.
        </p>

        <!-- Chart -->
        <div class="mt-6 rounded-lg border border-gray-200 bg-white p-5">
            <div class="mb-3 flex items-center justify-between">
                <h2 class="font-semibold">Revenue by day</h2>
                <p class="text-xs text-gray-400">Highest day: {{ naira.format(maxRevenue > 1 ? maxRevenue : 0) }}</p>
            </div>
            <div class="overflow-x-auto">
                <svg :viewBox="`0 0 ${W} ${H}`" class="h-40 w-full min-w-[420px]" role="img" aria-label="Revenue by day">
                    <line x1="0" :y1="H - 0.5" :x2="W" :y2="H - 0.5" class="stroke-gray-200" />
                    <rect
                        v-for="(day, index) in series"
                        :key="day.date"
                        :x="index * barWidth + 1"
                        :y="H - barHeight(day)"
                        :width="Math.max(1, barWidth - 2)"
                        :height="barHeight(day)"
                        rx="1.5"
                        class="fill-emerald-500 hover:fill-emerald-700"
                    >
                        <title>{{ shortDate(day.date) }}: {{ naira.format(day.revenue) }} from {{ day.orders }} orders</title>
                    </rect>
                </svg>
            </div>
            <div class="mt-1 flex justify-between text-xs text-gray-400">
                <span>{{ shortDate(series[0]?.date ?? from) }}</span>
                <span>{{ shortDate(series[series.length - 1]?.date ?? to) }}</span>
            </div>
        </div>

        <div class="mt-6 grid gap-6 lg:grid-cols-3">
            <!-- Top products -->
            <div class="rounded-lg border border-gray-200 bg-white lg:col-span-2">
                <h2 class="border-b border-gray-200 px-5 py-4 font-semibold">Best sellers</h2>
                <p v-if="topProducts.length === 0" class="px-5 py-8 text-center text-sm text-gray-500">
                    No sales in this period.
                </p>
                <div v-else class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="text-xs text-gray-500">
                        <tr>
                            <th class="px-5 py-3 font-medium">Product</th>
                            <th class="px-5 py-3 text-right font-medium">Units sold</th>
                            <th class="px-5 py-3 text-right font-medium">Revenue</th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                        <tr v-for="(product, index) in topProducts" :key="product.id">
                            <td class="px-5 py-3">
                                <span class="mr-2 text-xs text-gray-400">{{ index + 1 }}</span>
                                <span class="font-medium">{{ product.name }}</span>
                            </td>
                            <td class="px-5 py-3 text-right">{{ product.units }}</td>
                            <td class="px-5 py-3 text-right">{{ naira.format(product.revenue) }}</td>
                        </tr>
                        </tbody>
                        <tfoot>
                        <tr class="border-t border-gray-200 text-xs text-gray-500">
                            <td class="px-5 py-3">Top {{ topProducts.length }} total</td>
                            <td class="px-5 py-3 text-right">{{ totalUnits }}</td>
                            <td />
                        </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <div class="space-y-6">
                <!-- Pickup vs delivery -->
                <div class="rounded-lg border border-gray-200 bg-white p-5 text-sm">
                    <h2 class="mb-3 font-semibold">Pickup vs delivery</h2>
                    <p v-if="fulfillment.length === 0" class="text-gray-500">No orders in this period.</p>
                    <div v-for="row in fulfillment" :key="row.fulfillment_type" class="flex justify-between py-1.5">
                        <span class="capitalize">{{ row.fulfillment_type }}</span>
                        <span class="text-gray-600">{{ row.orders }} orders, {{ naira.format(row.revenue) }}</span>
                    </div>
                </div>

                <!-- Status breakdown -->
                <div class="rounded-lg border border-gray-200 bg-white p-5 text-sm">
                    <h2 class="mb-3 font-semibold">Orders by status</h2>
                    <p v-if="statusBreakdown.length === 0" class="text-gray-500">No orders in this period.</p>
                    <div v-for="row in statusBreakdown" :key="row.status" class="flex justify-between py-1.5">
                        <span class="capitalize">{{ statusLabel(row.status) }}</span>
                        <span class="text-gray-600">{{ row.orders }}</span>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
