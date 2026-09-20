<script setup>
import { computed, ref } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import PushToggle from '@/Components/Admin/PushToggle.vue'

defineProps({
    title: { type: String, default: '' },
})

const page = usePage()
const sidebarOpen = ref(false)

const permissions = computed(() => page.props.auth.permissions ?? [])
const roles = computed(() => page.props.auth.roles ?? [])
const can = (permission) => !permission || permissions.value.includes(permission)
const hasRole = (role) => roles.value.includes(role)

/**
 * One place that decides what the sidebar shows.
 * `ready: false` hides screens that aren't built yet - flip to true as you build them.
 */
const sections = [
    {
        label: 'Overview',
        items: [
            { name: 'Dashboard', href: '/admin/dashboard', match: '/admin/dashboard', ready: true },
        ],
    },
    {
        label: 'Sales',
        items: [
            { name: 'Orders', href: '/admin/orders', match: '/admin/orders', permission: 'view-orders', ready: true },
        ],
    },
    {
        label: 'Catalog',
        items: [
            { name: 'Products', href: '/admin/products', match: '/admin/products', permission: 'manage-products', ready: true },
            { name: 'Categories', href: '/admin/categories', match: '/admin/categories', permission: 'manage-categories', ready: true },
            { name: 'Pickup Points', href: '/admin/pickup-points', match: '/admin/pickup-points', permission: 'manage-pickup-points', ready: true },
        ],
    },
    {
        label: 'Content',
        items: [
            { name: 'Services', href: '/admin/services', match: '/admin/services', permission: 'manage-services', ready: true },
            { name: 'Training', href: '/admin/training', match: '/admin/training', permission: 'manage-training', ready: true },
            { name: 'Contact Channels', href: '/admin/contact-channels', match: '/admin/contact-channels', permission: 'manage-contact', ready: true },
        ],
    },
    {
        label: 'Administration',
        items: [
            { name: 'Staff', href: '/admin/staff', match: '/admin/staff', permission: 'manage-staff', ready: true },
            { name: 'Reports', href: '/admin/reports', match: '/admin/reports', permission: 'view-reports', ready: true },
            { name: 'Activity Log', href: '/admin/activity-log', match: '/admin/activity-log', permission: 'view-activity-log', ready: true },
            { name: 'Settings', href: '/admin/settings', match: '/admin/settings', role: 'super_admin', ready: false },
        ],
    },
]

const visibleSections = computed(() =>
    sections
        .map((section) => ({
            ...section,
            items: section.items.filter(
                (item) => item.ready && can(item.permission) && (!item.role || hasRole(item.role)),
            ),
        }))
        .filter((section) => section.items.length > 0),
)

const isActive = (item) => page.url.startsWith(item.match)

const roleLabel = computed(() => (roles.value[0] ?? '').replace('_', ' '))
</script>

<template>
    <div class="min-h-screen bg-gray-50 text-gray-900">
        <!-- Mobile overlay -->
        <div
            v-if="sidebarOpen"
            class="fixed inset-0 z-30 bg-black/40 lg:hidden"
            @click="sidebarOpen = false"
        />

        <!-- Sidebar -->
        <aside
            class="fixed inset-y-0 left-0 z-40 flex w-64 flex-col border-r border-gray-200 bg-white transition-transform lg:translate-x-0"
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
        >
            <div class="flex h-16 items-center border-b border-gray-200 px-6">
                <Link href="/admin/dashboard" class="text-lg font-semibold">Stack Pharmacy</Link>
            </div>

            <nav class="flex-1 space-y-6 overflow-y-auto px-4 py-6">
                <div v-for="section in visibleSections" :key="section.label">
                    <p class="mb-2 px-2 text-xs font-semibold uppercase tracking-wide text-gray-400">
                        {{ section.label }}
                    </p>
                    <Link
                        v-for="item in section.items"
                        :key="item.href"
                        :href="item.href"
                        class="block rounded-md px-3 py-2 text-sm font-medium"
                        :class="isActive(item)
              ? 'bg-emerald-50 text-emerald-700'
              : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900'"
                        @click="sidebarOpen = false"
                    >
                        {{ item.name }}
                    </Link>
                </div>
            </nav>

            <div class="border-t border-gray-200 p-4 text-sm">
                <PushToggle />
                <p class="truncate font-medium">{{ page.props.auth.user?.name }}</p>
                <p class="mb-3 text-xs capitalize text-gray-500">{{ roleLabel }}</p>
                <div class="flex gap-4">
                    <Link href="/" class="text-gray-500 hover:text-gray-900">View shop</Link>
                    <Link href="/logout" method="post" as="button" class="text-gray-500 hover:text-gray-900">
                        Log out
                    </Link>
                </div>
            </div>
        </aside>

        <!-- Main -->
        <div class="lg:pl-64">
            <header class="sticky top-0 z-20 flex h-16 items-center gap-3 border-b border-gray-200 bg-white px-4 sm:px-6">
                <button
                    type="button"
                    class="rounded-md p-2 text-gray-600 hover:bg-gray-100 lg:hidden"
                    aria-label="Open menu"
                    @click="sidebarOpen = true"
                >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
                <h1 class="text-lg font-semibold">{{ title }}</h1>
                <div class="ml-auto"><slot name="actions" /></div>
            </header>

            <!-- Flash messages -->
            <div v-if="page.props.flash?.success || page.props.flash?.error" class="px-4 pt-4 sm:px-6">
                <div
                    v-if="page.props.flash.success"
                    class="rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800"
                >
                    {{ page.props.flash.success }}
                </div>
                <div
                    v-if="page.props.flash.error"
                    class="rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800"
                >
                    {{ page.props.flash.error }}
                </div>
            </div>

            <main class="p-4 sm:p-6">
                <slot />
            </main>
        </div>
    </div>
</template>
