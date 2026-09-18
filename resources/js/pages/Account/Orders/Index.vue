<script setup>
import { Link } from '@inertiajs/vue3'
import AccountLayout from '@/Layouts/AccountLayout.vue'

defineOptions({ layout: AccountLayout })

defineProps({
    orders: { type: Object, required: true }, // Laravel paginator
})

const statusStyles = {
    paid: 'bg-primary-light text-primary-dark',
    pending: 'bg-amber-100 text-amber-700',
    processing: 'bg-blue-100 text-blue-700',
    completed: 'bg-green-100 text-green-700',
    cancelled: 'bg-red-100 text-red-700',
}

function statusClass(status) {
    return statusStyles[status] || 'bg-neutral-bg text-neutral-text/70'
}

function formatNaira(value) {
    return new Intl.NumberFormat('en-NG', { style: 'currency', currency: 'NGN', maximumFractionDigits: 0 }).format(value)
}

function formatDate(value) {
    if (!value) return '—'
    return new Date(value).toLocaleDateString('en-NG', { day: 'numeric', month: 'short', year: 'numeric' })
}
</script>

<template>
    <div>
        <h2 class="font-heading text-lg font-semibold text-neutral-text">Orders</h2>

        <div v-if="orders.data.length" class="mt-5 divide-y divide-neutral-text/10 overflow-hidden rounded-xl border border-neutral-text/10">
            <Link
                v-for="order in orders.data"
                :key="order.id"
                :href="route('account.orders.show', order.id)"
                class="flex flex-col gap-2 p-4 transition-colors hover:bg-neutral-bg/60 sm:flex-row sm:items-center sm:justify-between sm:p-5"
            >
                <div>
                    <p class="font-medium text-neutral-text">Order #{{ order.id }}</p>
                    <p class="mt-0.5 text-sm text-neutral-text/60">
                        {{ formatDate(order.created_at) }} · {{ order.items_count }} item{{ order.items_count === 1 ? '' : 's' }} ·
                        {{ order.fulfillment_type === 'pickup' ? 'Pickup' : 'Delivery' }}
                    </p>
                </div>

                <div class="flex items-center gap-4 sm:flex-col sm:items-end sm:gap-1.5">
                    <span class="text-sm font-semibold text-neutral-text">{{ formatNaira(order.total_amount) }}</span>
                    <span class="rounded-full px-2.5 py-0.5 text-xs font-medium capitalize" :class="statusClass(order.status)">
                        {{ order.status }}
                    </span>
                </div>
            </Link>
        </div>

        <div v-else class="mt-6 rounded-xl border border-dashed border-neutral-text/15 p-8 text-center">
            <p class="text-sm text-neutral-text/60">You haven't placed any orders yet.</p>
            <Link
                :href="route('shop.index')"
                class="mt-3 inline-block rounded-md bg-primary-dark px-4 py-2 text-sm font-medium text-white transition-colors hover:opacity-90"
            >
                Start shopping
            </Link>
        </div>

        <!-- Pagination -->
        <div v-if="orders.links.length > 3" class="mt-6 flex flex-wrap gap-1.5">
            <Link
                v-for="(link, i) in orders.links"
                :key="i"
                :href="link.url || '#'"
                v-html="link.label"
                class="rounded-md px-3 py-1.5 text-sm"
                :class="[
                    link.active ? 'bg-primary-dark text-white' : 'text-neutral-text/70 hover:bg-neutral-bg',
                    !link.url ? 'pointer-events-none opacity-40' : '',
                ]"
            />
        </div>
    </div>
</template>
