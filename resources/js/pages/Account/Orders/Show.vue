<script setup>
import { Link } from '@inertiajs/vue3'
import AccountLayout from '@/Layouts/AccountLayout.vue'
import OrderHeader from '@/Components/Orders/OrderHeader.vue'
import OrderActions from '@/Components/Orders/OrderActions.vue'
import OrderItems from '@/Components/Orders/OrderItems.vue'

defineOptions({ layout: AccountLayout })

defineProps({
    order: { type: Object, required: true },
})

function formatNaira(value) {
    return new Intl.NumberFormat('en-NG', { style: 'currency', currency: 'NGN', maximumFractionDigits: 0 }).format(value)
}
</script>

<template>
    <div>
        <Link :href="route('account.orders.index')" class="text-sm font-medium text-primary-dark hover:underline">
            ← Back to orders
        </Link>

        <div class="mt-3">
            <OrderHeader :order="order" />
        </div>

        <div class="mt-4">
            <OrderActions :order="order" />
        </div>

        <div class="mt-6">
            <OrderItems :items="order.items" />
        </div>

        <!-- Fulfillment -->
        <div class="mt-6 grid gap-4 sm:grid-cols-2">
            <div class="rounded-xl border border-neutral-text/10 p-4">
                <p class="text-sm font-medium text-neutral-text/80">
                    {{ order.fulfillment_type === 'pickup' ? 'Pickup location' : 'Delivery address' }}
                </p>
                <p v-if="order.fulfillment_type === 'pickup'" class="mt-1.5 text-sm text-neutral-text/70">
                    {{ order.pickup_point?.name }}<br />
                    {{ order.pickup_point?.address }}
                </p>
                <p v-else class="mt-1.5 text-sm text-neutral-text/70">
                    {{ order.delivery_address }}
                </p>
            </div>

            <div class="rounded-xl border border-neutral-text/10 p-4">
                <p class="text-sm font-medium text-neutral-text/80">Total</p>
                <p class="mt-1.5 text-lg font-semibold text-neutral-text">{{ formatNaira(order.total_amount) }}</p>
            </div>
        </div>
    </div>
</template>
