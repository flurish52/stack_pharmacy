<script setup>
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import AppFooter from "@/Components/AppFooter.vue";
import TopBar from "@/Components/TopBar.vue";

const page = usePage()

const mobileMenuOpen = ref(false)
const scrolled = ref(false)

const navLinks = [
    { label: 'Home', route: 'pharm.home' },
    { label: 'Shop', route: 'shop.index' },
    { label: 'Services', route: 'services.index' },
    { label: 'Training', route: 'training.index' },
    { label: 'Contact us', route: 'contact.index' },
]

/* --- Auth --- */
const authUser = computed(() => page.props.auth?.user ?? null)
const firstName = computed(() => authUser.value?.name?.split(' ')[0] ?? '')

/* --- Session cart, shared from Laravel via Inertia middleware --- */
const cart = computed(() => page.props.cart ?? {})
const cartCount = computed(() => Number(cart.value.count ?? 0))
const cartTotal = computed(() => Number(cart.value.total ?? 0))

const itemLabel = computed(() => `${cartCount.value} ${cartCount.value === 1 ? 'item' : 'items'}`)

const money = new Intl.NumberFormat('en-NG', {
    style: 'currency',
    currency: 'NGN',
    maximumFractionDigits: 0,
})
const totalLabel = computed(() => money.format(cartTotal.value))

const cartAria = computed(() =>
    cartCount.value === 0
        ? 'Cart, empty'
        : `Cart, ${itemLabel.value}, ${totalLabel.value}`
)

const isActive = (name) => {
    try {
        return route().current(name)
    } catch {
        return false
    }
}

/* --- Sticky header gets a hairline + blur once you leave the top --- */
const onScroll = () => {
    scrolled.value = window.scrollY > 8
}

const onKeydown = (e) => {
    if (e.key === 'Escape') mobileMenuOpen.value = false
}

onMounted(() => {
    onScroll()
    window.addEventListener('scroll', onScroll, { passive: true })
    window.addEventListener('keydown', onKeydown)
})

onBeforeUnmount(() => {
    window.removeEventListener('scroll', onScroll)
    window.removeEventListener('keydown', onKeydown)
    document.body.style.overflow = ''
})

/* Drawer floats above the page, so freeze the page behind it */
watch(mobileMenuOpen, (open) => {
    document.body.style.overflow = open ? 'hidden' : ''
})

/* Close the drawer after any Inertia visit */
watch(() => page.url, () => {
    mobileMenuOpen.value = false
})
</script>

<template>
    <div class="flex min-h-screen flex-col bg-neutral-bg text-neutral-text">
        <TopBar />
        <header
            class="sticky top-0 z-40 border-b border-neutral-text/10 transition-shadow duration-200"
            :class="scrolled
        ? 'bg-neutral-bg/85 shadow-sm backdrop-blur-md supports-[backdrop-filter]:bg-neutral-bg/70'
        : 'bg-neutral-bg'"
        >
            <div class="mx-auto flex max-w-6xl items-center justify-between gap-4 px-4 py-3.5 sm:px-6">
                <Link
                    :href="route('pharm.home')"
                    class="flex items-center gap-2 font-heading text-lg font-semibold tracking-tight text-primary-dark sm:text-xl"
                >
                    <span class="flex h-8 w-8 items-center justify-center rounded-md bg-primary-dark text-white">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M10.5 20.5 3.5 13.5a5 5 0 1 1 7-7l1 1 1-1a5 5 0 1 1 7 7l-7 7Z" />
                            <path d="M9 12h6M12 9v6" />
                        </svg>
                    </span>
                    Stack Pharmacy
                </Link>

                <nav class="hidden items-center gap-7 md:flex">
                    <Link
                        v-for="link in navLinks"
                        :key="link.route"
                        :href="route(link.route)"
                        class="relative py-1 text-sm font-medium transition-colors hover:text-primary-dark"
                        :class="isActive(link.route)
                            ? 'text-primary-dark after:absolute after:inset-x-0 after:-bottom-0.5 after:h-px after:bg-primary-dark'
                            : 'text-neutral-text/65'"
                    >
                        {{ link.label }}
                    </Link>
                </nav>

                <div class="flex items-center gap-2">
                    <!-- Auth: single entry point, swaps to the user's name once signed in -->
                    <Link
                        :href="authUser ? route('dashboard') : route('login')"
                        class="hidden items-center gap-2 rounded-md px-2.5 py-2 text-sm font-medium text-neutral-text/70 transition-colors hover:bg-white hover:text-primary-dark md:flex"
                    >
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M20 21a8 8 0 0 0-16 0" />
                            <circle cx="12" cy="7" r="4" />
                        </svg>
                        {{ authUser ? firstName : 'Sign in' }}
                    </Link>

                    <!-- Desktop cart: icon + what's actually in it -->
                    <Link
                        :href="route('cart.index')"
                        :aria-label="cartAria"
                        class="hidden items-center gap-3 rounded-md border border-neutral-text/15 py-2 pl-3.5 pr-4 transition-colors hover:border-primary-dark/30 hover:bg-white md:flex"
                    >
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="shrink-0 text-primary-dark">
                            <circle cx="9" cy="21" r="1" />
                            <circle cx="20" cy="21" r="1" />
                            <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6" />
                        </svg>

                        <span v-if="cartCount > 0" class="flex items-baseline gap-2 text-sm leading-none">
                            <span class="text-neutral-text/65">{{ itemLabel }}</span>
                            <span class="h-3 w-px bg-neutral-text/20" aria-hidden="true"></span>
                            <span class="font-semibold text-primary-dark tabular-nums">{{ totalLabel }}</span>
                        </span>
                        <span v-else class="text-sm leading-none text-neutral-text/55">Cart is empty</span>
                    </Link>

                    <!-- Mobile cart: basket only -->
                    <Link
                        :href="route('cart.index')"
                        :aria-label="cartAria"
                        class="relative flex h-10 w-10 items-center justify-center rounded-md transition-colors hover:bg-white md:hidden"
                    >
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <circle cx="9" cy="21" r="1" />
                            <circle cx="20" cy="21" r="1" />
                            <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6" />
                        </svg>
                        <span
                            v-if="cartCount > 0"
                            class="absolute right-0.5 top-0.5 flex h-4 min-w-4 items-center justify-center rounded-full bg-accent px-1 text-[10px] font-medium leading-none text-white tabular-nums"
                        >
                            {{ cartCount > 99 ? '99+' : cartCount }}
                        </span>
                    </Link>

                    <button
                        class="flex h-10 w-10 items-center justify-center rounded-md transition-colors hover:bg-white md:hidden"
                        aria-label="Open menu"
                        :aria-expanded="mobileMenuOpen"
                        aria-controls="mobile-drawer"
                        @click="mobileMenuOpen = true"
                    >
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <line x1="3" y1="6" x2="21" y2="6" />
                            <line x1="3" y1="12" x2="21" y2="12" />
                            <line x1="3" y1="18" x2="21" y2="18" />
                        </svg>
                    </button>
                </div>
            </div>
        </header>

        <!-- Mobile drawer: floats over the page, slides in from the right -->
        <Teleport to="body">
            <Transition
                enter-active-class="transition-opacity duration-200"
                enter-from-class="opacity-0"
                leave-active-class="transition-opacity duration-200"
                leave-to-class="opacity-0"
            >
                <div
                    v-if="mobileMenuOpen"
                    class="fixed inset-0 z-50 bg-neutral-text/40 md:hidden"
                    @click="mobileMenuOpen = false"
                ></div>
            </Transition>

            <Transition
                enter-active-class="transition-transform duration-300 ease-out"
                enter-from-class="translate-x-full"
                leave-active-class="transition-transform duration-200 ease-in"
                leave-to-class="translate-x-full"
            >
                <aside
                    v-if="mobileMenuOpen"
                    id="mobile-drawer"
                    class="fixed inset-y-0 right-0 z-50 flex w-72 max-w-[85vw] flex-col bg-white shadow-xl md:hidden"
                    role="dialog"
                    aria-modal="true"
                    aria-label="Menu"
                >
                    <div class="flex items-center justify-between border-b border-neutral-text/10 px-4 py-3.5">
                        <span class="font-heading text-base font-semibold text-primary-dark">Menu</span>
                        <button
                            class="flex h-9 w-9 items-center justify-center rounded-md text-neutral-text/60 transition-colors hover:bg-neutral-bg"
                            aria-label="Close menu"
                            @click="mobileMenuOpen = false"
                        >
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <line x1="18" y1="6" x2="6" y2="18" />
                                <line x1="6" y1="6" x2="18" y2="18" />
                            </svg>
                        </button>
                    </div>

                    <!-- Auth entry point, mirrors the desktop link -->
                    <Link
                        :href="authUser ? route('dashboard') : route('login')"
                        class="mx-3 mt-3 flex items-center gap-2.5 rounded-md border border-neutral-text/15 px-3.5 py-2.5 text-sm font-medium text-neutral-text/80"
                        @click="mobileMenuOpen = false"
                    >
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M20 21a8 8 0 0 0-16 0" />
                            <circle cx="12" cy="7" r="4" />
                        </svg>
                        {{ authUser ? `Signed in as ${firstName}` : 'Sign in' }}
                    </Link>

                    <nav class="flex flex-1 flex-col gap-0.5 px-3 py-3">
                        <Link
                            v-for="link in navLinks"
                            :key="link.route"
                            :href="route(link.route)"
                            class="rounded-md px-3 py-2.5 text-sm font-medium transition-colors"
                            :class="isActive(link.route)
                                ? 'bg-primary-light text-primary-dark'
                                : 'text-neutral-text/75 hover:bg-neutral-bg'"
                            @click="mobileMenuOpen = false"
                        >
                            {{ link.label }}
                        </Link>
                    </nav>

                    <div class="border-t border-neutral-text/10 px-4 py-4">
                        <Link
                            :href="route('cart.index')"
                            class="flex items-center justify-between rounded-md border border-neutral-text/15 px-3.5 py-2.5 text-sm"
                            @click="mobileMenuOpen = false"
                        >
                            <span class="text-neutral-text/65">{{ cartCount > 0 ? itemLabel : 'Cart is empty' }}</span>
                            <span v-if="cartCount > 0" class="font-semibold text-primary-dark tabular-nums">{{ totalLabel }}</span>
                        </Link>

                        <a :href="`https://wa.me/${page.props.pharmacyWhatsapp}`"
                           target="_blank"
                           rel="noopener"
                           class="mt-2.5 flex items-center justify-center gap-2 rounded-md bg-whatsapp py-2.5 text-sm font-medium text-white transition-colors duration-200 hover:bg-whatsapp/90"
                        >
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M21 12a8 8 0 0 1-8 8H8l-5 3 1.5-4.5A8 8 0 1 1 21 12z" />
                            </svg>
                            Chat on WhatsApp
                        </a>
                    </div>
                </aside>
            </Transition>
        </Teleport>

        <main class="flex-1">
            <slot />
        </main>

        <AppFooter />
    </div>
</template>
