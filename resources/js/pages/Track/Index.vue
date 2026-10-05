<script setup>
import { computed, ref } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'

defineOptions({ name: 'TrackIndex' })

const props = defineProps({
    reference: { type: String, default: '' },
    notFound: { type: Boolean, default: false },
    order: { type: Object, default: null },
})

const page = usePage()
const authUser = computed(() => page.props.auth?.user ?? null)

const input = ref(props.reference || '')
const submitting = ref(false)

const search = () => {
    const value = input.value.trim()
    if (!value) return
    submitting.value = true
    router.get(route('track.index'), { reference: value }, {
        preserveState: true,
        preserveScroll: true,
        onFinish: () => (submitting.value = false),
    })
}

const claiming = ref(false)
const claimOrder = () => {
    claiming.value = true
    router.post(route('track.claim', props.reference), {}, {
        onFinish: () => (claiming.value = false),
    })
}

// Group the raw lifecycle values into three visual buckets so the page
// doesn't need a color/icon for every individual status string.
const STATUS_LABELS = {
    pending: 'Payment pending',
    paid: 'Paid — preparing your order',
    processing: 'Processing',
    ready_for_pickup: 'Ready for pickup',
    out_for_delivery: 'Out for delivery',
    received: 'Received',
    completed: 'Completed',
    cancelled: 'Cancelled',
}

const statusLabel = computed(() => {
    const s = props.order?.status
    return STATUS_LABELS[s] ?? (s ? s.replace(/_/g, ' ') : '')
})

const statusTone = computed(() => {
    const s = props.order?.status
    if (s === 'completed' || s === 'received') return 'success'
    if (s === 'cancelled') return 'failed'
    return 'progress'
})

function formatNaira(value) {
    return new Intl.NumberFormat('en-NG', { style: 'currency', currency: 'NGN', maximumFractionDigits: 0 }).format(value)
}

function formatDate(value) {
    if (!value) return ''
    return new Intl.DateTimeFormat('en-NG', { day: 'numeric', month: 'short', year: 'numeric' }).format(new Date(value))
}
</script>

<template>
    <div class="mx-auto min-h-[70vh] max-w-lg px-6 py-16">
        <div class="text-center">
            <h1 class="font-heading text-xl font-semibold tracking-tight text-neutral-text">Track your order</h1>
            <p class="mt-2 text-sm leading-relaxed text-neutral-text/60">
                Enter the reference from your order confirmation to check its status.
            </p>
        </div>

        <form class="mt-6 flex gap-2" @submit.prevent="search">
            <input
                v-model="input"
                type="text"
                placeholder="STACK_ORD-XXXXXXXXXXXX"
                class="min-w-0 flex-1 rounded-lg border border-neutral-text/15 bg-white px-3.5 py-2.5 font-mono text-sm text-neutral-text placeholder:text-neutral-text/30 focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/30"
            />
            <button
                type="submit"
                :disabled="submitting || !input.trim()"
                class="shrink-0 rounded-lg bg-primary-dark px-4 py-2.5 text-sm font-medium text-white transition-colors hover:bg-primary-dark/90 disabled:cursor-not-allowed disabled:opacity-50"
            >
                {{ submitting ? 'Searching…' : 'Track' }}
            </button>
        </form>

        <!-- Not found -->
        <div v-if="notFound" class="mt-8 rounded-xl border border-neutral-text/10 bg-primary-light/10 px-5 py-6 text-center">
            <p class="text-sm font-medium text-neutral-text">We couldn't find an order matching that reference.</p>
            <p class="mt-1.5 text-xs leading-relaxed text-neutral-text/60">
                Double-check it against your order confirmation, or reach out if you think this is wrong.
            </p>
        </div>

        <!-- Result -->
        <div v-else-if="order" class="mt-8 overflow-hidden rounded-2xl border border-neutral-text/10 bg-white shadow-[0_20px_45px_-15px_rgba(15,23,22,0.12)]">
            <span
                class="block h-[3px] w-full"
                :class="{
                    'bg-gradient-to-r from-primary-dark to-primary-light': statusTone === 'success',
                    'bg-gradient-to-r from-primary-light to-primary-dark/60': statusTone === 'progress',
                    'bg-accent-light': statusTone === 'failed',
                }"
            />

            <div class="px-6 py-6">
                <div class="flex items-center justify-between">
                    <span class="font-mono text-xs text-neutral-text/45">{{ order.reference }}</span>
                    <span
                        class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-medium"
                        :class="{
                            'bg-primary-light text-primary-dark': statusTone === 'success',
                            'bg-primary-light/70 text-primary-dark/80': statusTone === 'progress',
                            'bg-accent-light/15 text-accent-light': statusTone === 'failed',
                        }"
                    >
                        {{ statusLabel }}
                    </span>
                </div>

                <p v-if="order.created_at" class="mt-1 text-xs text-neutral-text/45">Placed {{ formatDate(order.created_at) }}</p>

                <!-- Items -->
                <div class="mt-5 divide-y divide-neutral-text/10 border-y border-neutral-text/10">
                    <div v-for="(item, i) in order.items" :key="i" class="flex items-center justify-between py-2.5 text-sm">
                        <span class="text-neutral-text/80">
                            {{ item.product_name }}
                            <span class="text-neutral-text/45">— {{ item.variant_name }}</span>
                        </span>
                        <span class="text-neutral-text/60">×{{ item.quantity }}</span>
                    </div>
                </div>

                <div class="mt-4 flex items-center justify-between text-sm">
                    <span class="text-neutral-text/60">Total</span>
                    <span class="font-heading font-semibold text-neutral-text">{{ formatNaira(order.total_amount) }}</span>
                </div>

                <p class="mt-1 text-xs text-neutral-text/50">
                    {{ order.fulfillment_type === 'pickup' ? order.pickup_point?.name : order.delivery_address }}
                </p>

                <!-- Claim: logged-in user, email matches, order still unclaimed -->
                <div v-if="order.claimable" class="mt-5 rounded-r-md border-l-2 border-primary bg-primary-light/25 px-3.5 py-3">
                    <p class="text-xs leading-relaxed text-neutral-text/70">
                        This order matches your account email. Add it to your orders for easier tracking next time.
                    </p>
                    <button
                        type="button"
                        :disabled="claiming"
                        class="mt-2.5 inline-flex items-center gap-1.5 rounded-lg bg-primary-dark px-3.5 py-2 text-xs font-medium text-white transition-colors hover:bg-primary-dark/90 disabled:opacity-60"
                        @click="claimOrder"
                    >
                        {{ claiming ? 'Adding…' : 'Add to my account' }}
                    </button>
                </div>

                <!-- Not logged in, order still unclaimed: soft nudge, no reference leaked in the redirect -->
                <div v-else-if="!authUser && !order.is_claimed" class="mt-5 text-center text-xs text-neutral-text/50">
                    <Link :href="route('login')" class="font-medium text-primary-dark hover:underline">Log in</Link>
                    with the email you used to check out to save this order to your account.
                </div>
            </div>
        </div>
    </div>
</template>
