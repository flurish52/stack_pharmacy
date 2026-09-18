<script setup>
import { Link } from '@inertiajs/vue3'

const tabs = [
    { label: 'Home', route: 'dashboard', icon: 'M3 10.5 12 3l9 7.5M5 9v11a1 1 0 0 0 1 1h4v-6h4v6h4a1 1 0 0 0 1-1V9' },
    { label: 'Orders', route: 'account.orders.index', icon: 'M3 3h2l2.4 12.4a2 2 0 0 0 2 1.6h7.2a2 2 0 0 0 2-1.6L21 8H6' },
    { label: 'Addresses', route: 'account.addresses.index', icon: 'M12 22s8-7.58 8-13a8 8 0 1 0-16 0c0 5.42 8 13 8 13zM12 12a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5z' },
    // { label: 'Profile', route: 'account.profile.edit', icon: 'M20 21a8 8 0 0 0-16 0M12 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8z' },
]

const isActive = (name) => {
    try {
        return route().current(name) || route().current(`${name.split('.').slice(0, -1).join('.')}.*`)
    } catch {
        return false
    }
}
</script>

<template>
    <div class="mx-auto max-w-5xl px-4 py-10 pb-24 sm:px-6 md:py-12 lg:pb-12">
        <h1 class="font-heading text-xl font-semibold tracking-tight text-neutral-text sm:text-2xl">
            My account
        </h1>

        <div class="mt-6 grid gap-6 lg:grid-cols-[200px_1fr] lg:items-start">
            <!-- Desktop: sticky sidebar -->
            <nav class="hidden lg:sticky lg:top-8 lg:flex lg:flex-col lg:gap-0.5">
                <Link
                    v-for="tab in tabs"
                    :key="tab.route"
                    :href="route(tab.route)"
                    class="flex w-full items-center gap-2.5 whitespace-nowrap rounded-md px-3 py-2 text-sm font-medium transition-colors"
                    :class="isActive(tab.route)
                        ? 'bg-primary-light text-primary-dark'
                        : 'text-neutral-text/65 hover:bg-neutral-bg'"
                >
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="shrink-0" aria-hidden="true">
                        <path :d="tab.icon" />
                    </svg>
                    {{ tab.label }}
                </Link>

                <Link
                    href="/logout"
                    method="post"
                    as="button"
                    class="text-red-500 font-medium py-2 px-4 rounded transition duration-150 ease-in-out"
                >
                    Logout
                </Link>
            </nav>

            <div class="min-w-0 rounded-xl border border-neutral-text/10 bg-white p-5 sm:p-6">
                <slot />
            </div>
        </div>

        <!-- Mobile: fixed bottom nav -->
        <nav
            class="fixed inset-x-0 bottom-0 z-40 flex items-stretch justify-around border-t border-neutral-text/10 bg-white pb-[env(safe-area-inset-bottom)] lg:hidden"
        >
            <Link
                v-for="tab in tabs"
                :key="tab.route"
                :href="route(tab.route)"
                class="flex flex-1 flex-col items-center gap-1 py-2.5 text-xs font-medium transition-colors"
                :class="isActive(tab.route) ? 'text-primary-dark' : 'text-neutral-text/50'"
            >
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="shrink-0" aria-hidden="true">
                    <path :d="tab.icon" />
                </svg>
                {{ tab.label }}
            </Link>
        <Link
            href="/logout"
            method="post"
            as="button"
            class="text-red-500 font-medium py-2 px-4 rounded transition duration-150 ease-in-out"
        >
            Logout
        </Link>
        </nav>




    </div>
</template>
