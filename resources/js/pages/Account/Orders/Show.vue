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
        <Link
            :href="route('account.orders.index')"
            class="group -ml-2 inline-flex items-center gap-1.5 rounded-md px-2 py-1 text-sm font-medium text-neutral-text/70 transition-colors hover:bg-primary-light/40 hover:text-primary-dark focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/40"
        >
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="transition-transform group-hover:-translate-x-0.5">
                <path d="M19 12H5M12 19l-7-7 7-7" />
            </svg>
            Back to orders
        </Link>

        <Transition appear name="fade-in">
            <div>
                <div class="mt-4">
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
                    <div class="rounded-xl border border-neutral-text/10 p-5">
                        <p class="text-xs font-medium text-neutral-text/45">
                            {{ order.fulfillment_type === 'pickup' ? 'Pickup location' : 'Delivery address' }}
                        </p>
                        <p v-if="order.fulfillment_type === 'pickup'" class="mt-2 text-sm leading-relaxed text-neutral-text/80">
                            {{ order.pickup_point?.name }}<br />
                            {{ order.pickup_point?.address }}
                        </p>
                        <p v-else class="mt-2 text-sm leading-relaxed text-neutral-text/80">
                            {{ order.delivery_address }}
                        </p>
                    </div>

                    <div class="rounded-xl border border-neutral-text/10 bg-primary-light/20 p-5">
                        <p class="text-xs font-medium text-neutral-text/45">Total</p>
                        <p class="mt-2 font-heading text-lg font-semibold text-neutral-text">{{ formatNaira(order.total_amount) }}</p>
                    </div>
                </div>
            </div>
        </Transition>
    </div>
</template>

<style scoped>
.fade-in-enter-active {
    transition: opacity 0.4s ease, transform 0.4s ease;
}
.fade-in-enter-from {
    opacity: 0;
    transform: translateY(8px);
}

@media (prefers-reduced-motion: reduce) {
    .fade-in-enter-active {
        transition-duration: 0.01ms !important;
    }
    .fade-in-enter-from {
        transform: none !important;
    }
}
</style>
