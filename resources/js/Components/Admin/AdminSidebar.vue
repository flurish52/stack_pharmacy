<script setup>
// resources/js/Components/Admin/AdminSidebar.vue
import { Link } from '@inertiajs/vue3'
import PushToggle from '@/Components/Admin/PushToggle.vue'
import SidebarLink from '@/Components/Admin/SidebarLink.vue'
import { ICONS } from '@/Composables/adminNav.js'
import { useAdminNav } from '@/Composables/useAdminNav.js'

defineProps({
    open: { type: Boolean, default: false }, // mobile drawer
    collapsed: { type: Boolean, default: false }, // desktop icon-only mode
})

defineEmits(['close', 'toggle'])

const { sections, isActive, user, roleLabel, initials } = useAdminNav()
</script>

<template>
    <aside
        class="fixed inset-y-0 left-0 z-40 flex w-64 flex-col border-r border-neutral-text/10 bg-white transition-[width,transform] duration-200 ease-out motion-reduce:transition-none lg:translate-x-0"
        :class="[open ? 'translate-x-0 shadow-xl lg:shadow-none' : '-translate-x-full', collapsed ? 'lg:w-[76px]' : '']"
        aria-label="Admin navigation"
    >
        <!-- Desktop collapse toggle, sitting on the sidebar's edge -->
        <button
            type="button"
            class="absolute -right-3 top-[4.5rem] z-10 hidden h-6 w-6 items-center justify-center rounded-full border border-neutral-text/15 bg-white text-neutral-text/50 shadow-sm transition hover:border-primary/40 hover:text-primary-dark focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/40 lg:flex"
            :aria-label="collapsed ? 'Expand sidebar' : 'Collapse sidebar'"
            @click="$emit('toggle')"
        >
            <svg
                width="12"
                height="12"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2.5"
                stroke-linecap="round"
                stroke-linejoin="round"
                class="transition-transform duration-200"
                :class="collapsed ? 'rotate-180' : ''"
                aria-hidden="true"
            >
                <path d="M15 5l-7 7 7 7" />
            </svg>
        </button>

        <!-- Brand -->
        <div class="flex h-16 shrink-0 items-center gap-2 border-b border-neutral-text/10 px-4" :class="collapsed ? 'lg:justify-center lg:px-0' : ''">
            <Link href="/admin/dashboard" class="flex min-w-0 items-center gap-3 rounded-lg focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/40">
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-primary-dark font-heading text-sm font-semibold text-white">
                    SP
                </span>
                <span class="min-w-0" :class="collapsed ? 'lg:hidden' : ''">
                    <span class="block truncate font-heading text-[15px] font-semibold leading-tight tracking-tight text-neutral-text">
                        Stack Pharmacy
                    </span>
                    <span class="block text-xs leading-tight text-neutral-text/50">Admin</span>
                </span>
            </Link>

            <!-- Mobile close -->
            <button
                type="button"
                class="ml-auto rounded-lg p-1.5 text-neutral-text/50 transition hover:bg-neutral-bg hover:text-neutral-text focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/40 lg:hidden"
                aria-label="Close menu"
                @click="$emit('close')"
            >
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
                    <path d="M6 6l12 12M18 6L6 18" />
                </svg>
            </button>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 space-y-5 overflow-y-auto overflow-x-hidden px-3 py-4">
            <div v-for="section in sections" :key="section.label">
                <p class="mb-1.5 px-2.5 text-xs font-medium text-neutral-text/45" :class="collapsed ? 'lg:hidden' : ''">
                    {{ section.label }}
                </p>
                <div class="space-y-0.5">
                    <SidebarLink
                        v-for="item in section.items"
                        :key="item.href"
                        :href="item.href"
                        :label="item.name"
                        :icon="ICONS[item.icon]"
                        :active="isActive(item)"
                        :collapsed="collapsed"
                    />
                </div>
            </div>
        </nav>

        <!-- Footer -->
        <div class="shrink-0 space-y-3 border-t border-neutral-text/10 p-3">
            <!-- PushToggle keeps its light-on-dark styling inside this tile -->
            <div class="rounded-xl bg-primary-dark p-1" :class="collapsed ? 'lg:flex lg:justify-center' : ''">
                <PushToggle />
            </div>

            <div
                class="flex items-center gap-2.5 rounded-xl bg-neutral-bg p-2"
                :class="collapsed ? 'lg:justify-center lg:bg-transparent lg:p-0' : ''"
            >
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-primary-dark text-xs font-semibold text-white">
                    {{ initials }}
                </span>
                <div class="min-w-0" :class="collapsed ? 'lg:hidden' : ''">
                    <p class="truncate text-sm font-medium leading-tight text-neutral-text">{{ user?.name }}</p>
                    <span class="mt-1 inline-block rounded-full bg-secondary px-2 py-0.5 text-[11px] font-medium capitalize leading-none text-primary-dark">
                        {{ roleLabel }}
                    </span>
                </div>
            </div>

            <div class="space-y-0.5">
                <SidebarLink href="/" label="View shop" :icon="ICONS.store" :collapsed="collapsed" />
                <SidebarLink href="/logout" label="Log out" :icon="ICONS.logout" :collapsed="collapsed" method="post" as="button" danger />
            </div>
        </div>
    </aside>
</template>
