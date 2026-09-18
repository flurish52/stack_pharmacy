<script setup>
import { computed, ref } from 'vue'
import { Link, usePage, router } from '@inertiajs/vue3'

const page = usePage()

const authUser = computed(() => page.props.auth?.user ?? null)
const permissions = computed(() => page.props.auth?.permissions ?? [])
const can = (permission) => !permission || permissions.value.includes(permission)

const sidebarOpen = ref(false)

// Each item's `permission` gates visibility per the RoleSeeder matrix.
// null = visible to anyone who reached the admin area at all.
const navSections = [
    {
        label: 'Overview',
        items: [
            { label: 'Dashboard', route: 'admin.dashboard', icon: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 0 0 1 1h3m10-11l2 2m-2-2v10a1 1 0 0 1-1 1h-3m-6 0a1 1 0 0 0 1-1v-4a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v4a1 1 0 0 0 1 1m-6 0h6', permission: null },
        ],
    },
    {
        label: 'Catalog',
        items: [
            { label: 'Products', route: 'admin.products.index', icon: 'M20.59 13.41 12 22 2 12l.01-.01L11.99 2l8.6 8.6a2 2 0 0 1 0 2.82zM7 7.01l.01-.01', permission: 'manage-products' },
            { label: 'Categories', route: 'admin.categories.index', icon: 'M4 6h16M4 12h16M4 18h7', permission: 'manage-categories' },
            { label: 'Services', route: 'admin.services.index', icon: 'M21 12a8 8 0 0 1-8 8H8l-5 3 1.5-4.5A8 8 0 1 1 21 12z', permission: 'manage-services' },
            { label: 'Training', route: 'admin.training.edit', icon: 'M4 6.5 12 3l8 3.5-8 3.5-8-3.5zM7 10v5.5c0 1.5 2.2 3 5 3s5-1.5 5-3V10', permission: 'manage-training' },
        ],
    },
    {
        label: 'Orders',
        items: [
            { label: 'All orders', route: 'admin.orders.index', icon: 'M3 3h2l2.4 12.4a2 2 0 0 0 2 1.6h7.2a2 2 0 0 0 2-1.6L21 8H6', permission: 'view-orders' },
        ],
    },
    {
        label: 'Operations',
        items: [
            { label: 'Pickup points', route: 'admin.pickup-points.index', icon: 'M12 22s8-7.58 8-13a8 8 0 1 0-16 0c0 5.42 8 13 8 13zM12 12a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5z', permission: 'manage-pickup-points' },
            { label: 'Contact channels', route: 'admin.contact-channels.index', icon: 'M3 5a2 2 0 0 1 2-2h3.28a1 1 0 0 1 .95.68l1.5 4.5a1 1 0 0 1-.5 1.21l-2.1 1.05a11 11 0 0 0 5.4 5.4l1.05-2.1a1 1 0 0 1 1.21-.5l4.5 1.5a1 1 0 0 1 .68.95V19a2 2 0 0 1-2 2h-1C9.7 21 3 14.3 3 6z', permission: 'manage-contact' },
        ],
    },
    {
        label: 'Team',
        items: [
            { label: 'Staff', route: 'admin.staff.index', icon: 'M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8zM23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75', permission: 'manage-staff' },
            { label: 'Activity log', route: 'admin.activity-log.index', icon: 'M12 8v4l3 3M12 2a10 10 0 1 0 10 10A10 10 0 0 0 12 2z', permission: 'view-activity-log' },
        ],
    },
    {
        label: 'Insights',
        items: [
            { label: 'Reports', route: 'admin.reports.index', icon: 'M18 20V10M12 20V4M6 20v-6', permission: 'view-reports' },
        ],
    },
    {
        label: 'System',
        items: [
            { label: 'Settings', route: 'admin.settings.index', icon: 'M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6z M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z', permission: 'manage-system-settings' },
        ],
    },
]

// Sections collapse entirely if none of their items are visible to this user.
// staff (view-orders + update-order-status only) -> lands with just Overview + Orders.
// admin -> Catalog + Orders + Operations (no Team/Insights/System).
// owner -> everything except System (settings is super_admin-only).
// super_admin -> everything.
const visibleSections = computed(() =>
    navSections
        .map((section) => ({
            ...section,
            items: section.items.filter((item) => can(item.permission)),
        }))
        .filter((section) => section.items.length > 0)
)

const isActive = (name) => {
    try {
        return route().current(name) || route().current(`${name.split('.').slice(0, -1).join('.')}.*`)
    } catch {
        return false
    }
}

const roleLabel = computed(() => {
    const role = authUser.value?.roles?.[0]?.name ?? authUser.value?.role
    if (!role) return ''
    return role.replace(/_/g, ' ').replace(/\b\w/g, (c) => c.toUpperCase())
})

const logout = () => router.post(route('logout'))
</script>

<template>
    <div class="flex min-h-screen bg-neutral-bg text-neutral-text">
        <!-- Sidebar (desktop) -->
        <aside class="hidden w-64 shrink-0 flex-col border-r border-neutral-text/10 bg-white lg:flex">
            <div class="flex h-16 items-center gap-2 border-b border-neutral-text/10 px-5">
                <span class="flex h-8 w-8 items-center justify-center rounded-md bg-primary-dark text-white">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M10.5 20.5 3.5 13.5a5 5 0 1 1 7-7l1 1 1-1a5 5 0 1 1 7 7l-7 7Z" />
                        <path d="M9 12h6M12 9v6" />
                    </svg>
                </span>
                <span class="font-heading text-sm font-semibold tracking-tight text-neutral-text">Stack Pharmacy</span>
            </div>

            <nav class="flex-1 overflow-y-auto px-3 py-4">
                <div v-for="section in visibleSections" :key="section.label" class="mb-5 last:mb-0">
                    <p class="px-2.5 text-[0.65rem] font-semibold uppercase tracking-wide text-neutral-text/40">
                        {{ section.label }}
                    </p>
                    <div class="mt-1.5 space-y-0.5">
                        <Link
                            v-for="item in section.items"
                            :key="item.route"
                            :href="route(item.route)"
                            class="flex items-center gap-2.5 rounded-md px-2.5 py-2 text-sm font-medium transition-colors"
                            :class="isActive(item.route)
                                ? 'bg-primary-light text-primary-dark'
                                : 'text-neutral-text/70 hover:bg-neutral-bg'"
                        >
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="shrink-0" aria-hidden="true">
                                <path :d="item.icon" />
                            </svg>
                            {{ item.label }}
                        </Link>
                    </div>
                </div>
            </nav>

            <div class="border-t border-neutral-text/10 p-3">
                <div class="flex items-center gap-2.5 rounded-md px-2.5 py-2">
                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-primary-light text-sm font-semibold text-primary-dark">
                        {{ authUser?.name?.charAt(0) ?? '?' }}
                    </span>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-medium text-neutral-text">{{ authUser?.name }}</p>
                        <p class="text-xs text-neutral-text/50">{{ roleLabel }}</p>
                    </div>
                </div>
                <button
                    class="mt-1 flex w-full items-center gap-2.5 rounded-md px-2.5 py-2 text-sm text-neutral-text/60 transition-colors hover:bg-neutral-bg hover:text-red-600"
                    @click="logout"
                >
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9" />
                    </svg>
                    Log out
                </button>
            </div>
        </aside>

        <!-- Mobile drawer -->
        <Teleport to="body">
            <Transition
                enter-active-class="transition-opacity duration-200"
                enter-from-class="opacity-0"
                leave-active-class="transition-opacity duration-200"
                leave-to-class="opacity-0"
            >
                <div v-if="sidebarOpen" class="fixed inset-0 z-50 bg-neutral-text/40 lg:hidden" @click="sidebarOpen = false" />
            </Transition>

            <Transition
                enter-active-class="transition-transform duration-300 ease-out"
                enter-from-class="-translate-x-full"
                leave-active-class="transition-transform duration-200 ease-in"
                leave-to-class="-translate-x-full"
            >
                <aside v-if="sidebarOpen" class="fixed inset-y-0 left-0 z-50 flex w-72 max-w-[85vw] flex-col bg-white shadow-xl lg:hidden">
                    <div class="flex h-16 items-center justify-between border-b border-neutral-text/10 px-4">
                        <span class="font-heading text-sm font-semibold text-neutral-text">Menu</span>
                        <button class="flex h-9 w-9 items-center justify-center rounded-md text-neutral-text/60 hover:bg-neutral-bg" @click="sidebarOpen = false">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <line x1="18" y1="6" x2="6" y2="18" />
                                <line x1="6" y1="6" x2="18" y2="18" />
                            </svg>
                        </button>
                    </div>
                    <nav class="flex-1 overflow-y-auto px-3 py-4">
                        <div v-for="section in visibleSections" :key="section.label" class="mb-5 last:mb-0">
                            <p class="px-2.5 text-[0.65rem] font-semibold uppercase tracking-wide text-neutral-text/40">{{ section.label }}</p>
                            <div class="mt-1.5 space-y-0.5">
                                <Link
                                    v-for="item in section.items"
                                    :key="item.route"
                                    :href="route(item.route)"
                                    class="flex items-center gap-2.5 rounded-md px-2.5 py-2 text-sm font-medium"
                                    :class="isActive(item.route) ? 'bg-primary-light text-primary-dark' : 'text-neutral-text/70'"
                                    @click="sidebarOpen = false"
                                >
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="shrink-0" aria-hidden="true">
                                        <path :d="item.icon" />
                                    </svg>
                                    {{ item.label }}
                                </Link>
                            </div>
                        </div>
                    </nav>
                </aside>
            </Transition>
        </Teleport>

        <!-- Main -->
        <div class="flex min-w-0 flex-1 flex-col">
            <header class="flex h-16 items-center justify-between border-b border-neutral-text/10 bg-white px-4 sm:px-6">
                <button class="flex h-9 w-9 items-center justify-center rounded-md text-neutral-text/60 hover:bg-neutral-bg lg:hidden" @click="sidebarOpen = true">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <line x1="3" y1="6" x2="21" y2="6" />
                        <line x1="3" y1="12" x2="21" y2="12" />
                        <line x1="3" y1="18" x2="21" y2="18" />
                    </svg>
                </button>

                <slot name="header">
                    <h1 class="font-heading text-base font-semibold text-neutral-text">
                        <slot name="title">Dashboard</slot>
                    </h1>
                </slot>

                <div class="flex items-center gap-3 lg:hidden">
                    <span class="flex h-8 w-8 items-center justify-center rounded-full bg-primary-light text-sm font-semibold text-primary-dark">
                        {{ authUser?.name?.charAt(0) ?? '?' }}
                    </span>
                </div>
            </header>

            <main class="flex-1 overflow-y-auto p-4 sm:p-6">
                <slot />
            </main>
        </div>
    </div>
</template>
