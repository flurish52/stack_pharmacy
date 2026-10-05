<script setup>
import { ref } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'

const props = defineProps({
    order: { type: Object, required: true },
})

const receivedForm = useForm({})
function markReceived() {
    receivedForm.patch(route('account.orders.received', props.order.id), { preserveScroll: true })
}

/* Cancelling is deliberately not a single click: it's a plain text link,
 * not a button, and clicking it only reveals a confirmation — it doesn't
 * cancel anything by itself. An order already being paid/prepared has
 * real cost to unwind, so this shouldn't be as easy as "mark as received". */
const confirmingCancel = ref(false)
const cancelForm = useForm({})
function cancelOrder() {
    cancelForm.patch(route('account.orders.cancel', props.order.id), { preserveScroll: true })
}
</script>

<template>
    <div class="space-y-3">
        <!-- Pay now — only while payment is still outstanding -->
        <div v-if="order.status === 'pending'" class="flex items-center justify-between gap-4 rounded-xl border border-amber-200 bg-amber-50 p-4">
            <p class="text-sm text-amber-800">This order hasn't been paid for yet.</p>
            <Link
                :href="route('checkout.pay', order.id)"
                class="shrink-0 rounded-md bg-primary-dark px-4 py-2 text-sm font-medium text-white transition-colors hover:opacity-90"
            >
                Pay now
            </Link>
        </div>

        <!-- Mark as received — the encouraged, easy action -->
        <div v-if="order.is_receivable" class="flex flex-wrap items-center justify-between gap-4 rounded-xl border border-neutral-text/10 p-4">
            <p class="text-sm text-neutral-text/70">Got your order? Let us know.</p>
            <button
                type="button"
                :disabled="receivedForm.processing"
                @click="markReceived"
                class="shrink-0 rounded-md bg-primary-dark px-4 py-2 text-sm font-medium text-white transition-colors hover:opacity-90 disabled:opacity-60"
            >
                Mark as received
            </button>
        </div>

        <!-- Cancel — quiet, tucked away, needs a second step -->
        <div v-if="order.is_cancellable" class="pt-1 text-right">
            <button
                v-if="!confirmingCancel"
                type="button"
                class="text-xs text-neutral-text/40 underline decoration-dotted hover:text-neutral-text/60"
                @click="confirmingCancel = true"
            >
                Need to cancel this order?
            </button>

            <div v-else class="inline-flex items-center gap-3 rounded-md border border-neutral-text/10 bg-neutral-bg/60 px-3 py-2 text-xs">
                <span class="text-neutral-text/70">Cancel order #{{ order.id }}? This can't be undone.</span>
                <button
                    type="button"
                    :disabled="cancelForm.processing"
                    class="font-medium text-red-600 hover:underline disabled:opacity-60"
                    @click="cancelOrder"
                >
                    Yes, cancel it
                </button>
                <button type="button" class="text-neutral-text/50 hover:underline" @click="confirmingCancel = false">
                    Never mind
                </button>
            </div>
        </div>
    </div>
</template>
