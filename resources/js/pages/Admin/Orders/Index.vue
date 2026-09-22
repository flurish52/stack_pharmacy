<script setup>
import { reactive, watch } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import StatusBadge from '@/Components/StatusBadge.vue'
import { naira, formatDate, statusLabel, customerName } from '@/Composables/OrderStatus.js'

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

// Whole row/card is clickable; real links/buttons inside keep their own behaviour.
const openOrder = (event, order) => {
    if (event.target.closest('a, button')) return
    router.visit(`/admin/orders/${order.id}`)
}

const field =
    'rounded-lg border border-neutral-text/15 bg-white px-3 py-2 text-sm text-neutral-text transition placeholder:text-neutral-text/40 hover:border-neutral-text/30 focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20'

const email = (order) => order.user?.email ?? order.guest_email
</script>

<template>
    <Head title="Orders" />

    <AdminLayout title="Orders">
        <!-- Filters -->
        <div class="mb-4 flex flex-col gap-3 rounded-xl border border-neutral-text/10 bg-white p-3 md:flex-row md:items-center">
            <div class="relative w-full md:max-w-sm">
                <svg
                    class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-neutral-text/40"
                    viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" aria-hidden="true"
                >
                    <circle cx="9" cy="9" r="5.5" /><path d="m13.5 13.5 3 3" />
                </svg>
                <input
                    v-model="form.search"
                    type="search"
                    placeholder="Search order, name, email or phone"
                    :class="[field, 'w-full pl-9']"
                />
            </div>

            <div class="grid grid-cols-2 gap-2 md:contents">
                <select v-model="form.status" :class="[field, 'w-full md:w-auto']">
                    <option value="">All statuses</option>
                    <option v-for="s in statuses" :key="s" :value="s">{{ statusLabel(s) }}</option>
                </select>

                <select v-model="form.fulfillment_type" :class="[field, 'w-full md:w-auto']">
                    <option value="">Pickup and delivery</option>
                    <option value="pickup">Pickup</option>
                    <option value="delivery">Delivery</option>
                </select>
            </div>

            <button
                v-if="hasFilters()"
                type="button"
                class="rounded-lg px-3 py-2 text-left text-sm font-medium text-primary-dark transition hover:bg-primary-light md:ml-auto md:text-center"
                @click="reset"
            >
                Clear filters
            </button>
        </div>

        <!-- Empty state -->
        <div v-if="orders.data.length === 0" class="rounded-xl border border-neutral-text/10 bg-white px-5 py-14 text-center">
            <p class="font-heading font-medium text-neutral-text">No orders found</p>
            <p class="mt-1 text-sm text-neutral-text/55">Try a different search or clear the filters.</p>
        </div>

        <template v-else>
            <!-- Mobile: card list (below md) -->
            <ul class="space-y-3 md:hidden">
                <li
                    v-for="order in orders.data"
                    :key="order.id"
                    class="cursor-pointer rounded-xl border border-neutral-text/10 bg-white p-4 transition-colors active:bg-primary-light/60"
                    @click="openOrder($event, order)"
                >
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <Link :href="`/admin/orders/${order.id}`" class="font-heading font-semibold text-primary-dark">
                                #{{ order.id }}
                            </Link>
                            <p class="mt-0.5 truncate font-medium text-neutral-text">{{ customerName(order) }}</p>
                            <p class="truncate text-xs text-neutral-text/50">{{ email(order) }}</p>
                        </div>
                        <StatusBadge :status="order.status" class="shrink-0" />
                    </div>

                    <div class="mt-3 flex items-end justify-between gap-3 border-t border-neutral-text/[0.07] pt-3">
                        <div class="text-xs text-neutral-text/55">
                            <p class="capitalize text-neutral-text/70">
                                {{ order.fulfillment_type }}
                                <span v-if="order.pickup_point">· {{ order.pickup_point.name }}</span>
                            </p>
                            <p class="mt-0.5">{{ formatDate(order.created_at) }}</p>
                        </div>
                        <p class="font-heading font-semibold tabular-nums text-neutral-text">
                            {{ naira.format(order.total_amount) }}
                        </p>
                    </div>
                </li>
            </ul>

            <!-- Desktop: table (md and up) -->
            <div class="hidden overflow-hidden rounded-xl border border-neutral-text/10 bg-white md:block">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="border-b border-neutral-text/10 bg-neutral-bg text-xs text-neutral-text/55">
                        <tr>
                            <th class="px-5 py-3 font-medium">Order</th>
                            <th class="px-5 py-3 font-medium">Customer</th>
                            <th class="px-5 py-3 font-medium">Fulfillment</th>
                            <th class="px-5 py-3 font-medium">Status</th>
                            <th class="px-5 py-3 text-right font-medium">Total</th>
                            <th class="px-5 py-3 font-medium">Placed</th>
                            <th class="px-5 py-3"><span class="sr-only">Actions</span></th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-text/[0.07]">
                        <tr
                            v-for="order in orders.data"
                            :key="order.id"
                            class="group cursor-pointer transition-colors duration-150 hover:bg-primary-light"
                            @click="openOrder($event, order)"
                        >
                            <td class="px-5 py-3.5">
                                <Link :href="`/admin/orders/${order.id}`" class="font-heading font-semibold text-primary-dark hover:underline">
                                    #{{ order.id }}
                                </Link>
                            </td>
                            <td class="px-5 py-3.5">
                                <p class="font-medium text-neutral-text">{{ customerName(order) }}</p>
                                <p class="text-xs text-neutral-text/50">{{ email(order) }}</p>
                            </td>
                            <td class="px-5 py-3.5">
                                <p class="capitalize text-neutral-text">{{ order.fulfillment_type }}</p>
                                <p v-if="order.pickup_point" class="text-xs text-neutral-text/50">{{ order.pickup_point.name }}</p>
                            </td>
                            <td class="px-5 py-3.5"><StatusBadge :status="order.status" /></td>
                            <td class="px-5 py-3.5 text-right font-medium tabular-nums text-neutral-text">
                                {{ naira.format(order.total_amount) }}
                            </td>
                            <td class="whitespace-nowrap px-5 py-3.5 text-neutral-text/60">{{ formatDate(order.created_at) }}</td>
                            <td class="px-5 py-3.5 text-right">
                                <Link
                                    :href="`/admin/orders/${order.id}`"
                                    class="inline-flex items-center gap-1 rounded-lg border border-neutral-text/15 bg-white px-3 py-1.5 text-xs font-medium text-neutral-text transition group-hover:border-primary group-hover:bg-primary group-hover:text-white focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/40"
                                >
                                    View
                                    <svg class="h-3.5 w-3.5 transition-transform duration-150 group-hover:translate-x-0.5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <path d="M4 10h11M11 5l5 5-5 5" />
                                    </svg>
                                </Link>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </template>

        <!-- Pagination: shared by both layouts -->
        <div
            v-if="orders.last_page > 1"
            class="mt-3 flex flex-wrap items-center justify-between gap-3 rounded-xl border border-neutral-text/10 bg-white px-5 py-3 md:mt-0 md:rounded-none md:rounded-b-xl md:border-t-0"
        >
            <p class="text-xs text-neutral-text/55">Showing {{ orders.from }} to {{ orders.to }} of {{ orders.total }} orders</p>
            <div class="flex flex-wrap gap-1">
                <template v-for="link in orders.links" :key="link.label">
                    <Link
                        v-if="link.url"
                        :href="link.url"
                        preserve-scroll
                        class="rounded-lg px-3 py-1.5 text-sm transition"
                        :class="link.active ? 'bg-primary font-medium text-white' : 'text-neutral-text/70 hover:bg-secondary hover:text-primary-dark'"
                        v-html="link.label"
                    />
                    <span v-else class="rounded-lg px-3 py-1.5 text-sm text-neutral-text/25" v-html="link.label" />
                </template>
            </div>
        </div>
    </AdminLayout>
</template>
