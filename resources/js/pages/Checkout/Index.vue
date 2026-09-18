<script setup>
/**
 * Checkout/Index.
 *
 * `user` is null for a guest, or the authenticated user's { name, email }
 * — when present, the "Continue as" toggle is skipped entirely since
 * there's nothing to choose. `pickupPoints` only matters when fulfillment
 * is "pickup". `addresses` is the signed-in user's saved address book
 * (empty array for guests) — the default address is pre-selected.
 *
 * Two separate forms, because they're two separate actions:
 *  - `loginForm` posts to the existing login route. On success your auth
 *    controller should redirect back to `checkout.index` (pass `?redirect=`
 *    through, or just redirect intended()) so the guest picks up where
 *    they left off, now signed in.
 *  - `checkoutForm` posts to `checkout.store` (your current CartController
 *    logic, renamed — see the earlier backend notes) and carries the
 *    fulfillment + contact fields plus guest email/phone when relevant.
 *    For delivery, `delivery_address` is always plain text — picking a
 *    saved address just fills it in from here, there's no id sent and
 *    no lookup on the backend.
 */
import { computed, ref } from 'vue'
import { Head, useForm } from '@inertiajs/vue3'
// import CheckoutFlowLayout from '@/Layouts/CheckoutFlowLayout.vue'
import { formatNaira } from '@/Composables/Currency.js'

const props = defineProps({
    total: { type: Number, default: 0 },
    user: { type: Object, default: null },
    addresses: { type: Array, default: () => [] },
    pickupPoints: { type: Array, default: () => [] },
})

// defineOptions({ layout: CheckoutFlowLayout })

/* --- Continue as: guest vs sign in (skipped when already authenticated) --- */
const continueAs = ref('guest')

const loginForm = useForm({ email: '', password: '' })
const submitLogin = () => {
    loginForm.post(route('login'), { preserveScroll: true })
}

/* --- Saved address selection (signed-in users only) ---
 * `selectedId` is purely local UI state for highlighting the picked card —
 * it is never sent to the server. Picking a card just copies that
 * address's text into `checkoutForm.delivery_address`. */
const defaultAddress = props.addresses.find((a) => a.is_default) ?? props.addresses[0] ?? null
const selectedId = ref(defaultAddress?.id ?? null)

/* --- Checkout details --- */
const checkoutForm = useForm({
    fulfillment_type: 'delivery',
    delivery_address: defaultAddress?.address ?? '',
    pickup_point_id: '',
    full_name: '',
    phone: '',
    email: '',
})

const isPickup = computed(() => checkoutForm.fulfillment_type === 'pickup')
const deliveryNote = computed(() =>
    isPickup.value ? 'Free — pay in store when you collect.' : 'Delivery fee is paid on arrival.'
)

// A saved address already carries its own phone number, so once one is
// picked for delivery, there's nothing left to ask the signed-in user for.
const usingSavedAddress = computed(() => !!props.user && !isPickup.value && !!selectedId.value)

function selectAddress(addr) {
    selectedId.value = addr.id
    checkoutForm.delivery_address = addr.address
}

function editManually() {
    selectedId.value = null
}

const submitCheckout = () => {
    checkoutForm.post(route('checkout.store'), { preserveScroll: true })
}
</script>

<template>
    <Head title="Checkout" />

    <div class="mx-auto max-w-5xl px-4 py-10 sm:px-6 md:py-12">
        <h1 class="font-heading text-xl font-semibold tracking-tight text-neutral-text sm:text-2xl">Checkout</h1>

        <div class="mt-6 grid gap-6 lg:grid-cols-[1fr_320px] lg:items-start">
            <div class="space-y-5">
                <!-- Continue as -->
                <section v-if="!user" class="rounded-xl border border-neutral-text/10 bg-white p-5">
                    <h2 class="text-sm font-semibold text-neutral-text">Continue as</h2>

                    <div class="mt-4 grid grid-cols-2 gap-3">
                        <button
                            type="button"
                            class="rounded-md border px-4 py-2.5 text-sm font-medium transition-colors"
                            :class="continueAs === 'guest'
                                ? 'border-primary-dark bg-primary-dark/5 text-primary-dark'
                                : 'border-neutral-text/15 text-neutral-text/60 hover:border-neutral-text/30'"
                            @click="continueAs = 'guest'"
                        >
                            Guest
                        </button>
                        <button
                            type="button"
                            class="rounded-md border px-4 py-2.5 text-sm font-medium transition-colors"
                            :class="continueAs === 'signin'
                                ? 'border-primary-dark bg-primary-dark/5 text-primary-dark'
                                : 'border-neutral-text/15 text-neutral-text/60 hover:border-neutral-text/30'"
                            @click="continueAs = 'signin'"
                        >
                            Sign in
                        </button>
                    </div>

                    <form v-if="continueAs === 'signin'" class="mt-5 space-y-3" @submit.prevent="submitLogin">
                        <div>
                            <input
                                v-model="loginForm.email"
                                type="email"
                                placeholder="Email address"
                                autocomplete="email"
                                class="w-full rounded-md border border-neutral-text/15 px-3.5 py-2.5 text-sm placeholder:text-neutral-text/40 focus:border-primary-dark focus:outline-none focus:ring-1 focus:ring-primary-dark/30"
                            />
                            <p v-if="loginForm.errors.email" class="mt-1 text-xs text-red-600">{{ loginForm.errors.email }}</p>
                        </div>
                        <div>
                            <input
                                v-model="loginForm.password"
                                type="password"
                                placeholder="Password"
                                autocomplete="current-password"
                                class="w-full rounded-md border border-neutral-text/15 px-3.5 py-2.5 text-sm placeholder:text-neutral-text/40 focus:border-primary-dark focus:outline-none focus:ring-1 focus:ring-primary-dark/30"
                            />
                            <p v-if="loginForm.errors.password" class="mt-1 text-xs text-red-600">{{ loginForm.errors.password }}</p>
                        </div>
                        <button
                            type="submit"
                            :disabled="loginForm.processing"
                            class="w-full rounded-md bg-primary-dark px-4 py-2.5 text-sm font-medium text-white transition-colors duration-200 hover:bg-primary-dark/90 disabled:opacity-60"
                        >
                            {{ loginForm.processing ? 'Signing in…' : 'Sign in' }}
                        </button>
                    </form>
                </section>

                <div v-else class="flex items-center gap-2.5 rounded-xl border border-neutral-text/10 bg-white px-5 py-3.5">
                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-primary-light text-primary-dark">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M20 6 9 17l-5-5" />
                        </svg>
                    </span>
                    <p class="text-sm text-neutral-text/70">
                        Signed in as <span class="font-medium text-neutral-text">{{ user.name }}</span>
                    </p>
                </div>

                <!-- Fulfillment -->
                <section class="rounded-xl border border-neutral-text/10 bg-white p-5">
                    <h2 class="text-sm font-semibold text-neutral-text">How should we get this to you?</h2>

                    <div class="mt-4 grid grid-cols-2 gap-3">
                        <button
                            type="button"
                            class="rounded-md border px-4 py-2.5 text-sm font-medium transition-colors"
                            :class="checkoutForm.fulfillment_type === 'delivery'
                                ? 'border-primary-dark bg-primary-dark/5 text-primary-dark'
                                : 'border-neutral-text/15 text-neutral-text/60 hover:border-neutral-text/30'"
                            @click="checkoutForm.fulfillment_type = 'delivery'"
                        >
                            Delivery
                        </button>
                        <button
                            type="button"
                            class="rounded-md border px-4 py-2.5 text-sm font-medium transition-colors"
                            :class="isPickup
                                ? 'border-primary-dark bg-primary-dark/5 text-primary-dark'
                                : 'border-neutral-text/15 text-neutral-text/60 hover:border-neutral-text/30'"
                            @click="checkoutForm.fulfillment_type = 'pickup'"
                        >
                            Pickup
                        </button>
                    </div>

                    <div class="mt-4">
                        <select
                            v-if="isPickup"
                            v-model="checkoutForm.pickup_point_id"
                            class="w-full rounded-md border border-neutral-text/15 bg-white px-3.5 py-2.5 text-sm focus:border-primary-dark focus:outline-none focus:ring-1 focus:ring-primary-dark/30"
                        >
                            <option value="" disabled>Select a pickup point</option>
                            <option v-for="point in pickupPoints" :key="point.id" :value="point.id">
                                {{ point.name }}
                            </option>
                        </select>

                        <!-- Delivery: saved address picker for signed-in users, free text otherwise -->
                        <template v-else>
                            <div v-if="user && addresses.length" class="mb-3 space-y-2">
                                <label
                                    v-for="addr in addresses"
                                    :key="addr.id"
                                    class="flex cursor-pointer items-start gap-3 rounded-md border px-3.5 py-2.5 text-sm transition-colors"
                                    :class="selectedId === addr.id
                                        ? 'border-primary-dark bg-primary-dark/5'
                                        : 'border-neutral-text/15 hover:border-neutral-text/30'"
                                >
                                    <input
                                        type="radio"
                                        class="mt-0.5"
                                        :checked="selectedId === addr.id"
                                        @change="selectAddress(addr)"
                                    />
                                    <span>
                                        <span class="block font-medium text-neutral-text">{{ addr.label || 'Address' }}</span>
                                        <span class="block text-neutral-text/60">{{ addr.address }}</span>
                                    </span>
                                </label>
                            </div>

                            <textarea
                                v-model="checkoutForm.delivery_address"
                                rows="2"
                                placeholder="Delivery address"
                                @input="editManually"
                                class="w-full resize-none rounded-md border border-neutral-text/15 px-3.5 py-2.5 text-sm placeholder:text-neutral-text/40 focus:border-primary-dark focus:outline-none focus:ring-1 focus:ring-primary-dark/30"
                            ></textarea>
                            <p v-if="user && addresses.length" class="mt-1.5 text-xs text-neutral-text/45">
                                Edit the text above to use a different address instead.
                            </p>
                        </template>

                        <p
                            v-if="checkoutForm.errors.delivery_address || checkoutForm.errors.pickup_point_id"
                            class="mt-1 text-xs text-red-600"
                        >
                            {{ checkoutForm.errors.delivery_address || checkoutForm.errors.pickup_point_id }}
                        </p>
                    </div>

                    <p class="mt-3 flex items-center gap-1.5 text-xs" :class="isPickup ? 'text-neutral-text/50' : 'text-amber-600'">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <circle cx="12" cy="12" r="10" />
                            <path d="M12 8v4M12 16h.01" />
                        </svg>
                        {{ deliveryNote }}
                    </p>
                </section>

                <!-- Contact details — only what we don't already know -->
                <section v-if="!user || !usingSavedAddress" class="rounded-xl border border-neutral-text/10 bg-white p-5">
                    <h2 class="text-sm font-semibold text-neutral-text">Contact details</h2>

                    <div class="mt-4 space-y-3">
                        <div v-if="!user">
                            <input
                                v-model="checkoutForm.full_name"
                                type="text"
                                placeholder="Full name"
                                autocomplete="name"
                                class="w-full rounded-md border border-neutral-text/15 px-3.5 py-2.5 text-sm placeholder:text-neutral-text/40 focus:border-primary-dark focus:outline-none focus:ring-1 focus:ring-primary-dark/30"
                            />
                            <p v-if="checkoutForm.errors.full_name" class="mt-1 text-xs text-red-600">{{ checkoutForm.errors.full_name }}</p>
                        </div>
                        <div v-if="!usingSavedAddress">
                            <input
                                v-model="checkoutForm.phone"
                                type="tel"
                                placeholder="Phone number"
                                autocomplete="tel"
                                class="w-full rounded-md border border-neutral-text/15 px-3.5 py-2.5 text-sm placeholder:text-neutral-text/40 focus:border-primary-dark focus:outline-none focus:ring-1 focus:ring-primary-dark/30"
                            />
                            <p v-if="checkoutForm.errors.phone" class="mt-1 text-xs text-red-600">{{ checkoutForm.errors.phone }}</p>
                        </div>
                        <div v-if="!user">
                            <input
                                v-model="checkoutForm.email"
                                type="email"
                                placeholder="Email address"
                                autocomplete="email"
                                class="w-full rounded-md border border-neutral-text/15 px-3.5 py-2.5 text-sm placeholder:text-neutral-text/40 focus:border-primary-dark focus:outline-none focus:ring-1 focus:ring-primary-dark/30"
                            />
                            <p v-if="checkoutForm.errors.email" class="mt-1 text-xs text-red-600">{{ checkoutForm.errors.email }}</p>
                        </div>
                    </div>
                </section>
            </div>

            <aside class="rounded-xl border border-neutral-text/10 bg-white p-5 lg:sticky lg:top-8">
                <h2 class="text-sm font-semibold text-neutral-text">Order summary</h2>

                <dl class="mt-4 space-y-2.5 text-sm">
                    <div class="flex items-center justify-between">
                        <dt class="text-neutral-text/55">Subtotal</dt>
                        <dd class="tabular-nums text-neutral-text">{{ formatNaira(total) }}</dd>
                    </div>
                    <div class="flex items-center justify-between">
                        <dt class="text-neutral-text/55">Delivery</dt>
                        <dd class="text-xs text-neutral-text/55">{{ isPickup ? 'Free' : 'On arrival' }}</dd>
                    </div>
                </dl>

                <div class="mt-4 flex items-center justify-between border-t border-neutral-text/10 pt-4">
                    <span class="text-sm font-semibold text-neutral-text">Total</span>
                    <span class="font-heading text-base font-semibold tabular-nums text-primary-dark">{{ formatNaira(total) }}</span>
                </div>

                <button
                    type="button"
                    :disabled="checkoutForm.processing"
                    class="mt-5 flex w-full items-center justify-center gap-2 rounded-md bg-primary-dark px-6 py-3 text-sm font-medium text-white transition-colors duration-200 hover:bg-primary-dark/90 disabled:cursor-not-allowed disabled:opacity-60"
                    @click="submitCheckout"
                >
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <rect x="3" y="10" width="18" height="10" rx="1.5" />
                        <path d="M7 10V7a5 5 0 0 1 10 0v3" />
                    </svg>
                    {{ checkoutForm.processing ? 'Redirecting…' : 'Pay with Paystack' }}
                </button>

                <p class="mt-3 flex items-center justify-center gap-1.5 text-center text-xs text-neutral-text/45">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <rect x="4" y="10" width="16" height="9" rx="1.5" />
                        <path d="M7 10V7a5 5 0 0 1 10 0v3" />
                    </svg>
                    Secured by Paystack
                </p>
            </aside>
        </div>
    </div>
</template>
