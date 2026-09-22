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
import { Head, Link, useForm } from '@inertiajs/vue3'
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

/* --- Shared field styling: an error swaps the border to the accent tone --- */
const inputClass = (hasError) => [
    'w-full rounded-md border bg-neutral-bg px-3.5 py-2.5 text-sm text-neutral-text transition-colors placeholder:text-neutral-text/40 focus:outline-none focus:ring-2',
    hasError
        ? 'border-accent-light focus:border-accent-light focus:ring-accent-light/30'
        : 'border-neutral-text/15 hover:border-neutral-text/30 focus:border-primary-dark focus:ring-primary-dark/20',
]

const segmentClass = (active) => [
    'flex-1 rounded-md px-4 py-2 text-sm font-medium transition duration-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary motion-reduce:transition-none',
    active
        ? 'bg-neutral-bg text-primary-dark shadow-sm'
        : 'text-neutral-text/60 hover:text-neutral-text',
]

const optionCardClass = (active) => [
    'flex items-start gap-3 rounded-lg border p-4 text-left transition duration-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary motion-reduce:transition-none',
    active
        ? 'border-primary-dark bg-primary-light shadow-sm'
        : 'border-neutral-text/15 hover:border-primary-dark/40 hover:bg-primary-light/50',
]
</script>

<template>
    <Head title="Checkout" />

    <div class="mx-auto max-w-5xl px-4 py-8 sm:px-6 md:py-12">
        <div class="flex flex-wrap items-end justify-between gap-x-6 gap-y-2">
            <h1 class="font-heading text-2xl font-semibold tracking-tight text-neutral-text sm:text-3xl">Checkout</h1>
            <Link
                :href="route('cart.index')"
                class="group inline-flex items-center gap-1.5 text-sm font-medium text-primary-dark focus:outline-none focus-visible:ring-2 focus-visible:ring-primary"
            >
                <svg
                    class="transition-transform duration-200 group-hover:-translate-x-0.5 motion-reduce:transition-none"
                    width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"
                >
                    <path d="M19 12H5M12 19l-7-7 7-7" />
                </svg>
                <span class="underline-offset-4 group-hover:underline">Back to cart</span>
            </Link>
        </div>

        <div class="mt-8 grid gap-6 lg:grid-cols-[1fr_340px] lg:items-start lg:gap-8">
            <div class="space-y-5">
                <!-- Continue as -->
                <section v-if="!user" class="rounded-xl border border-neutral-text/10 bg-neutral-bg p-5 shadow-sm sm:p-6">
                    <h2 class="font-heading text-base font-semibold text-neutral-text">Who is checking out?</h2>
                    <p class="mt-1 text-sm text-neutral-text/60">Sign in to use your saved addresses, or continue as a guest.</p>

                    <div class="mt-5 flex gap-1 rounded-lg bg-primary-light p-1" role="group" aria-label="Continue as">
                        <button type="button" :class="segmentClass(continueAs === 'guest')" :aria-pressed="continueAs === 'guest'" @click="continueAs = 'guest'">
                            Guest
                        </button>
                        <button type="button" :class="segmentClass(continueAs === 'signin')" :aria-pressed="continueAs === 'signin'" @click="continueAs = 'signin'">
                            Sign in
                        </button>
                    </div>

                    <Transition
                        enter-active-class="transition duration-200 motion-reduce:transition-none"
                        enter-from-class="opacity-0 -translate-y-1"
                    >
                        <form v-if="continueAs === 'signin'" class="mt-5 space-y-4" @submit.prevent="submitLogin">
                            <div>
                                <label for="login-email" class="text-sm font-medium text-neutral-text">Email address</label>
                                <input
                                    id="login-email"
                                    v-model="loginForm.email"
                                    type="email"
                                    autocomplete="email"
                                    class="mt-1.5"
                                    :class="inputClass(loginForm.errors.email)"
                                />
                                <p v-if="loginForm.errors.email" class="mt-1.5 flex items-center gap-1.5 text-xs text-neutral-text">
                                    <span class="h-1.5 w-1.5 shrink-0 rounded-full bg-accent" aria-hidden="true"></span>
                                    {{ loginForm.errors.email }}
                                </p>
                            </div>
                            <div>
                                <label for="login-password" class="text-sm font-medium text-neutral-text">Password</label>
                                <input
                                    id="login-password"
                                    v-model="loginForm.password"
                                    type="password"
                                    autocomplete="current-password"
                                    class="mt-1.5"
                                    :class="inputClass(loginForm.errors.password)"
                                />
                                <p v-if="loginForm.errors.password" class="mt-1.5 flex items-center gap-1.5 text-xs text-neutral-text">
                                    <span class="h-1.5 w-1.5 shrink-0 rounded-full bg-accent" aria-hidden="true"></span>
                                    {{ loginForm.errors.password }}
                                </p>
                            </div>
                            <button
                                type="submit"
                                :disabled="loginForm.processing"
                                class="w-full rounded-md bg-primary-dark px-4 py-3 text-sm font-medium text-neutral-bg shadow-sm transition duration-200 hover:bg-primary-dark/90 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2 focus-visible:ring-offset-neutral-bg active:scale-[0.98] disabled:opacity-60 motion-reduce:transition-none"
                            >
                                {{ loginForm.processing ? 'Signing in…' : 'Sign in and continue' }}
                            </button>
                        </form>
                    </Transition>
                </section>

                <div v-else class="flex items-center gap-3 rounded-xl border border-primary-dark/15 bg-primary-light px-5 py-4">
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-secondary text-primary-dark">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M20 6 9 17l-5-5" />
                        </svg>
                    </span>
                    <p class="text-sm text-neutral-text/70">
                        Signed in as <span class="font-medium text-neutral-text">{{ user.name }}</span>
                    </p>
                </div>

                <!-- Fulfillment -->
                <section class="rounded-xl border border-neutral-text/10 bg-neutral-bg p-5 shadow-sm sm:p-6">
                    <h2 class="font-heading text-base font-semibold text-neutral-text">How should we get this to you?</h2>

                    <div class="mt-5 grid gap-3 sm:grid-cols-2" role="radiogroup" aria-label="Fulfillment method">
                        <button
                            type="button"
                            role="radio"
                            :aria-checked="!isPickup"
                            :class="optionCardClass(!isPickup)"
                            @click="checkoutForm.fulfillment_type = 'delivery'"
                        >
                            <span class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-md" :class="!isPickup ? 'bg-secondary text-primary-dark' : 'bg-primary-light text-primary-dark/70'">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M3 7h11v9H3zM14 10h4l3 3v3h-7" />
                                    <circle cx="7" cy="18" r="1.6" />
                                    <circle cx="17" cy="18" r="1.6" />
                                </svg>
                            </span>
                            <span>
                                <span class="block text-sm font-semibold text-neutral-text">Delivery</span>
                                <span class="mt-0.5 block text-xs text-neutral-text/60">Brought to your address</span>
                            </span>
                        </button>

                        <button
                            type="button"
                            role="radio"
                            :aria-checked="isPickup"
                            :class="optionCardClass(isPickup)"
                            @click="checkoutForm.fulfillment_type = 'pickup'"
                        >
                            <span class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-md" :class="isPickup ? 'bg-secondary text-primary-dark' : 'bg-primary-light text-primary-dark/70'">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M12 21s-7-6.2-7-11a7 7 0 0 1 14 0c0 4.8-7 11-7 11Z" />
                                    <circle cx="12" cy="10" r="2.5" />
                                </svg>
                            </span>
                            <span>
                                <span class="block text-sm font-semibold text-neutral-text">Pickup</span>
                                <span class="mt-0.5 block text-xs text-neutral-text/60">Collect from a pickup point</span>
                            </span>
                        </button>
                    </div>

                    <div class="mt-5 border-t border-neutral-text/10 pt-5">
                        <div v-if="isPickup">
                            <label for="pickup-point" class="text-sm font-medium text-neutral-text">Pickup point</label>
                            <select
                                id="pickup-point"
                                v-model="checkoutForm.pickup_point_id"
                                class="mt-1.5"
                                :class="inputClass(checkoutForm.errors.pickup_point_id)"
                            >
                                <option value="" disabled>Select a pickup point</option>
                                <option v-for="point in pickupPoints" :key="point.id" :value="point.id">
                                    {{ point.name }}
                                </option>
                            </select>
                        </div>

                        <!-- Delivery: saved address picker for signed-in users, free text otherwise -->
                        <div v-else>
                            <div v-if="user && addresses.length" class="mb-4">
                                <p class="text-sm font-medium text-neutral-text">Saved addresses</p>
                                <div class="mt-2 space-y-2" role="radiogroup" aria-label="Saved addresses">
                                    <label
                                        v-for="addr in addresses"
                                        :key="addr.id"
                                        class="flex cursor-pointer items-start gap-3 rounded-lg border p-3.5 text-sm transition duration-200 focus-within:ring-2 focus-within:ring-primary motion-reduce:transition-none"
                                        :class="selectedId === addr.id
                                            ? 'border-primary-dark bg-primary-light'
                                            : 'border-neutral-text/15 hover:border-primary-dark/40 hover:bg-primary-light/50'"
                                    >
                                        <input
                                            type="radio"
                                            name="saved-address"
                                            class="sr-only"
                                            :checked="selectedId === addr.id"
                                            @change="selectAddress(addr)"
                                        />
                                        <span
                                            class="mt-0.5 flex h-4 w-4 shrink-0 items-center justify-center rounded-full border-2 transition-colors"
                                            :class="selectedId === addr.id ? 'border-primary-dark' : 'border-neutral-text/30'"
                                            aria-hidden="true"
                                        >
                                            <span
                                                class="h-2 w-2 rounded-full bg-primary-dark transition-transform duration-200 motion-reduce:transition-none"
                                                :class="selectedId === addr.id ? 'scale-100' : 'scale-0'"
                                            ></span>
                                        </span>
                                        <span class="min-w-0">
                                            <span class="block font-medium text-neutral-text">{{ addr.label || 'Address' }}</span>
                                            <span class="mt-0.5 block text-neutral-text/60">{{ addr.address }}</span>
                                        </span>
                                    </label>
                                </div>
                            </div>

                            <label for="delivery-address" class="text-sm font-medium text-neutral-text">
                                {{ user && addresses.length ? 'Or use a different address' : 'Delivery address' }}
                            </label>
                            <textarea
                                id="delivery-address"
                                v-model="checkoutForm.delivery_address"
                                rows="3"
                                placeholder="House number, street, area"
                                class="mt-1.5 resize-none"
                                :class="inputClass(checkoutForm.errors.delivery_address)"
                                @input="editManually"
                            ></textarea>
                        </div>

                        <p
                            v-if="checkoutForm.errors.delivery_address || checkoutForm.errors.pickup_point_id"
                            class="mt-1.5 flex items-center gap-1.5 text-xs text-neutral-text"
                        >
                            <span class="h-1.5 w-1.5 shrink-0 rounded-full bg-accent" aria-hidden="true"></span>
                            {{ checkoutForm.errors.delivery_address || checkoutForm.errors.pickup_point_id }}
                        </p>

                        <p class="mt-4 flex items-center gap-2 rounded-md px-3 py-2 text-xs" :class="isPickup ? 'bg-primary-light text-primary-dark' : 'bg-accent/10 text-neutral-text'">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="shrink-0" :class="isPickup ? '' : 'text-accent'" aria-hidden="true">
                                <circle cx="12" cy="12" r="10" />
                                <path d="M12 8v4M12 16h.01" />
                            </svg>
                            {{ deliveryNote }}
                        </p>
                    </div>
                </section>

                <!-- Contact details — only what we don't already know -->
                <section v-if="!user || !usingSavedAddress" class="rounded-xl border border-neutral-text/10 bg-neutral-bg p-5 shadow-sm sm:p-6">
                    <h2 class="font-heading text-base font-semibold text-neutral-text">Contact details</h2>
                    <p class="mt-1 text-sm text-neutral-text/60">We use these only to reach you about this order.</p>

                    <div class="mt-5 grid gap-4 sm:grid-cols-2">
                        <div v-if="!user" class="sm:col-span-2">
                            <label for="full-name" class="text-sm font-medium text-neutral-text">Full name</label>
                            <input
                                id="full-name"
                                v-model="checkoutForm.full_name"
                                type="text"
                                autocomplete="name"
                                class="mt-1.5"
                                :class="inputClass(checkoutForm.errors.full_name)"
                            />
                            <p v-if="checkoutForm.errors.full_name" class="mt-1.5 flex items-center gap-1.5 text-xs text-neutral-text">
                                <span class="h-1.5 w-1.5 shrink-0 rounded-full bg-accent" aria-hidden="true"></span>
                                {{ checkoutForm.errors.full_name }}
                            </p>
                        </div>
                        <div v-if="!usingSavedAddress">
                            <label for="phone" class="text-sm font-medium text-neutral-text">Phone number</label>
                            <input
                                id="phone"
                                v-model="checkoutForm.phone"
                                type="tel"
                                autocomplete="tel"
                                class="mt-1.5"
                                :class="inputClass(checkoutForm.errors.phone)"
                            />
                            <p v-if="checkoutForm.errors.phone" class="mt-1.5 flex items-center gap-1.5 text-xs text-neutral-text">
                                <span class="h-1.5 w-1.5 shrink-0 rounded-full bg-accent" aria-hidden="true"></span>
                                {{ checkoutForm.errors.phone }}
                            </p>
                        </div>
                        <div v-if="!user">
                            <label for="email" class="text-sm font-medium text-neutral-text">Email address</label>
                            <input
                                id="email"
                                v-model="checkoutForm.email"
                                type="email"
                                autocomplete="email"
                                class="mt-1.5"
                                :class="inputClass(checkoutForm.errors.email)"
                            />
                            <p v-if="checkoutForm.errors.email" class="mt-1.5 flex items-center gap-1.5 text-xs text-neutral-text">
                                <span class="h-1.5 w-1.5 shrink-0 rounded-full bg-accent" aria-hidden="true"></span>
                                {{ checkoutForm.errors.email }}
                            </p>
                        </div>
                    </div>
                </section>
            </div>

            <!-- Summary -->
            <aside class="rounded-xl border border-primary-dark/15 bg-primary-light/60 p-5 shadow-sm sm:p-6 lg:sticky lg:top-24">
                <h2 class="font-heading text-base font-semibold text-neutral-text">Order summary</h2>

                <dl class="mt-5 space-y-3 text-sm">
                    <div class="flex items-center justify-between">
                        <dt class="text-neutral-text/60">Subtotal</dt>
                        <dd class="tabular-nums text-neutral-text">{{ formatNaira(total) }}</dd>
                    </div>
                    <div class="flex items-center justify-between">
                        <dt class="text-neutral-text/60">{{ isPickup ? 'Pickup' : 'Delivery' }}</dt>
                        <dd class="text-xs text-neutral-text/60">{{ isPickup ? 'Free' : 'On arrival' }}</dd>
                    </div>
                </dl>

                <div class="mt-5 flex items-baseline justify-between border-t border-primary-dark/15 pt-5">
                    <span class="text-sm font-semibold text-neutral-text">Total</span>
                    <span class="font-heading text-2xl font-semibold tabular-nums text-primary-dark">{{ formatNaira(total) }}</span>
                </div>

                <button
                    type="button"
                    :disabled="checkoutForm.processing"
                    class="mt-6 flex w-full items-center justify-center gap-2 rounded-md bg-primary-dark px-6 py-3.5 text-sm font-semibold text-neutral-bg shadow-sm transition duration-200 hover:bg-primary-dark/90 hover:shadow focus:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2 focus-visible:ring-offset-primary-light active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-60 disabled:active:scale-100 motion-reduce:transition-none"
                    @click="submitCheckout"
                >
                    <svg v-if="checkoutForm.processing" class="animate-spin motion-reduce:animate-none" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
                        <path d="M12 3a9 9 0 1 0 9 9" />
                    </svg>
                    <svg v-else width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <rect x="3" y="10" width="18" height="10" rx="1.5" />
                        <path d="M7 10V7a5 5 0 0 1 10 0v3" />
                    </svg>
                    {{ checkoutForm.processing ? 'Redirecting…' : 'Pay with Paystack' }}
                </button>

                <p class="mt-3 flex items-center justify-center gap-1.5 text-center text-xs text-neutral-text/55">
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
