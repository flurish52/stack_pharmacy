<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'

const props = defineProps({
    order: { type: Object, required: true },
    payment: { type: Object, required: true },
})

defineOptions({ name: 'Callback' })

const page = usePage()
const pharmacyWhatsapp = computed(() => page.props.pharmacyWhatsapp)

// Normalize whatever status strings your backend uses into three buckets.
const SUCCESS_STATUSES = ['paid', 'success', 'completed', 'confirmed']
const FAILED_STATUSES = ['failed', 'cancelled', 'canceled', 'declined']

// Driven by the payment attempt's own status, not the order rollup —
// this page is about "did this specific payment go through."
const state = computed(() => {
    const s = (props.payment.status || '').toLowerCase()
    if (SUCCESS_STATUSES.includes(s)) return 'success'
    if (FAILED_STATUSES.includes(s)) return 'failed'
    return 'pending'
})

const statusLabel = computed(() => {
    return props.payment.status
        ? props.payment.status.charAt(0).toUpperCase() + props.payment.status.slice(1)
        : 'Pending'
})

// Quiet background poll while pending — stops itself once resolved or after
// a reasonable ceiling, so it never runs forever if a webhook is genuinely stuck.
const POLL_INTERVAL_MS = 4000
const MAX_POLLS = 20
let pollCount = 0
let pollTimer = null

const isPolling = ref(false)

const poll = () => {
    if (state.value !== 'pending' || pollCount >= MAX_POLLS) {
        stopPolling()
        return
    }
    pollCount += 1
    isPolling.value = true
    router.reload({
        only: ['order', 'payment'],
        onFinish: () => {
            isPolling.value = false
            if (state.value === 'pending') {
                pollTimer = setTimeout(poll, POLL_INTERVAL_MS)
            }
        },
    })
}

const stopPolling = () => {
    if (pollTimer) clearTimeout(pollTimer)
    pollTimer = null
}

onMounted(() => {
    if (state.value === 'pending') {
        pollTimer = setTimeout(poll, POLL_INTERVAL_MS)
    }
})

onBeforeUnmount(stopPolling)

const copied = ref(false)
const copyReference = async () => {
    try {
        await navigator.clipboard.writeText(props.payment.reference)
        copied.value = true
        setTimeout(() => (copied.value = false), 1800)
    } catch {
        /* clipboard unavailable — silently ignore */
    }
}

const whatsappHref = computed(() => {
    const msg = encodeURIComponent(
        `Hi, I need help with order #${props.order.id} (ref: ${props.payment.reference}).`
    )
    return `https://wa.me/${pharmacyWhatsapp.value}?text=${msg}`
})

const isGuest = computed(() => !props.order.user_id && !props.order.user)
</script>

<template>
    <div class="relative mx-auto flex min-h-[70vh] max-w-lg flex-col items-center justify-center px-6 py-20">
        <div class="pointer-events-none absolute inset-x-0 top-1/4 -z-10 flex justify-center">
            <div
                class="h-64 w-[28rem] rounded-full blur-3xl transition-colors duration-500"
                :class="{
                    'bg-primary-light/50': state === 'success',
                    'bg-primary-light/35': state === 'pending',
                    'bg-red-100/60': state === 'failed',
                }"
            />
        </div>

        <div class="w-full overflow-hidden rounded-2xl border border-neutral-text/10 bg-white shadow-[0_20px_45px_-15px_rgba(15,23,22,0.12)]">
            <span
                class="block h-[3px] w-full"
                :class="{
                    'bg-gradient-to-r from-primary-dark to-primary-light': state === 'success',
                    'bg-gradient-to-r from-primary-light to-primary-dark/60': state === 'pending',
                    'bg-red-400': state === 'failed',
                }"
            />

            <div class="flex flex-col items-center px-8 py-12 text-center">
                <span
                    class="flex h-16 w-16 items-center justify-center rounded-full ring-1"
                    :class="{
                        'bg-primary-light text-primary-dark ring-primary-dark/10': state === 'success',
                        'bg-primary-light/60 text-primary-dark ring-primary-dark/10': state === 'pending',
                        'bg-red-50 text-red-500 ring-red-200': state === 'failed',
                    }"
                >
                    <svg v-if="state === 'success'" width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 6 9 17l-5-5" />
                    </svg>
                    <svg v-else-if="state === 'pending'" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" class="animate-spin">
                        <path d="M12 3a9 9 0 1 0 9 9" />
                    </svg>
                    <svg v-else width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M18 6 6 18M6 6l12 12" />
                    </svg>
                </span>

                <h1 class="mt-6 font-heading text-xl font-semibold tracking-tight text-neutral-text">
                    <template v-if="state === 'success'">Payment confirmed</template>
                    <template v-else-if="state === 'pending'">Confirming your payment&hellip;</template>
                    <template v-else>Payment didn&rsquo;t go through</template>
                </h1>

                <p class="mt-2.5 max-w-sm text-sm leading-relaxed text-neutral-text/60">
                    <template v-if="state === 'success'">
                        Your order has been placed and we&rsquo;re getting it ready.
                    </template>
                    <template v-else-if="state === 'pending'">
                        This usually takes a few seconds. We&rsquo;ll update this page automatically — no need to refresh.
                    </template>
                    <template v-else>
                        Your card wasn&rsquo;t charged, or the transaction was declined. You can try again or reach out if this looks wrong.
                    </template>
                </p>

                <div class="mt-8 w-full rounded-xl border border-neutral-text/10 bg-primary-light/20 px-5 py-4 text-left">
                    <div class="flex items-center justify-between py-1.5">
                        <span class="text-xs font-medium uppercase tracking-wide text-neutral-text/45">Order</span>
                        <span class="font-heading text-sm font-semibold text-neutral-text">#{{ order.id }}</span>
                    </div>
                    <div class="flex items-center justify-between border-t border-neutral-text/10 py-1.5 pt-3">
                        <span class="text-xs font-medium uppercase tracking-wide text-neutral-text/45">Status</span>
                        <span
                            class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-medium"
                            :class="{
                                'bg-primary-light text-primary-dark': state === 'success',
                                'bg-primary-light/70 text-primary-dark/80': state === 'pending',
                                'bg-red-50 text-red-500': state === 'failed',
                            }"
                        >
                            <span
                                class="h-1.5 w-1.5 rounded-full"
                                :class="{
                                    'bg-primary-dark': state === 'success',
                                    'bg-primary-dark/60 animate-pulse': state === 'pending',
                                    'bg-red-500': state === 'failed',
                                }"
                            />
                            {{ statusLabel }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between border-t border-neutral-text/10 py-1.5 pt-3">
                        <span class="text-xs font-medium uppercase tracking-wide text-neutral-text/45">Reference</span>
                        <button
                            type="button"
                            class="group inline-flex items-center gap-1.5 font-mono text-xs text-neutral-text/70 transition-colors hover:text-primary-dark"
                            @click="copyReference"
                        >
                            {{ payment.reference }}
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="opacity-50 transition-opacity group-hover:opacity-100">
                                <rect x="9" y="9" width="12" height="12" rx="2" />
                                <path d="M5 15V5a2 2 0 0 1 2-2h10" />
                            </svg>
                        </button>
                    </div>

                    <div v-if="isGuest" class="mt-3 flex items-start gap-2 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2.5">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mt-0.5 shrink-0 text-amber-600">
                            <path d="M12 9v4M12 17h.01M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z" />
                        </svg>
                        <p class="text-xs leading-relaxed text-amber-800">
                            <span class="font-semibold">Save this reference number.</span> Since you checked out as a guest, it's the only way to track or look up this order later — copy it or take a screenshot now.
                        </p>
                    </div>
                    <p v-if="isGuest && copied" class="mt-2 text-xs font-medium text-primary-dark">Copied — keep it somewhere safe</p>                </div>
                <p v-if="copied" class="mt-2 text-xs font-medium text-primary-dark">Copied</p>
                <p v-if="copied && !isGuest" class="mt-2 text-xs font-medium text-primary-dark">Copied</p>


                <div class="mt-8 flex w-full flex-col gap-3">
                    <template v-if="state === 'success'">
<!--                        <Link-->
<!--                            :href="route('orders.show', order.id)"-->
<!--                            class="inline-flex items-center justify-center gap-2 rounded-lg bg-primary-dark px-5 py-3 text-sm font-medium text-white transition-colors hover:bg-primary-dark/90"-->
<!--                        >-->
<!--                            View order-->
<!--                        </Link>-->
                        <Link
                            :href="route('shop.index')"
                            class="inline-flex items-center justify-center gap-2 rounded-lg border border-neutral-text/15 px-5 py-3 text-sm font-medium text-neutral-text/80 transition-colors hover:bg-neutral-text/5"
                        >
                            Continue shopping
                        </Link>
                    </template>

                    <template v-else-if="state === 'pending'">
                        <div class="inline-flex items-center justify-center gap-2 rounded-lg border border-neutral-text/10 bg-neutral-text/[0.03] px-5 py-3 text-sm font-medium text-neutral-text/50">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" class="animate-spin">
                                <path d="M12 3a9 9 0 1 0 9 9" />
                            </svg>
                            Checking status&hellip;
                        </div>


                       <a :href="whatsappHref"
                        target="_blank"
                        rel="noopener"
                        class="inline-flex items-center justify-center gap-2 text-sm font-medium text-primary-dark transition-colors hover:text-whatsapp"
                        >
                        Taking too long? Chat with us
                        </a>
                    </template>

                    <template v-else>
                        <Link
                            :href="route('checkout.index')"
                            class="inline-flex items-center justify-center gap-2 rounded-lg bg-primary-dark px-5 py-3 text-sm font-medium text-white transition-colors hover:bg-primary-dark/90"
                        >
                            Try again
                        </Link>


                        <a :href="whatsappHref"
                        target="_blank"
                        rel="noopener"
                        class="inline-flex items-center justify-center gap-2 rounded-lg border border-neutral-text/15 px-5 py-3 text-sm font-medium text-neutral-text/80 transition-colors hover:bg-neutral-text/5"
                        >
                        Chat with us
                        </a>
                    </template>
                </div>
            </div>
        </div>
    </div>
</template>
