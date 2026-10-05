<script setup>
import {computed, onBeforeUnmount, onMounted, ref, watch} from 'vue'
import {Link, usePage} from '@inertiajs/vue3'
import PushToggle from '@/Components/Admin/PushToggle.vue'
import ApplicationLogo from "@/Components/ApplicationLogo.vue";

defineProps({
    title: {type: String, default: ''},
})

const page = usePage()
const sidebarOpen = ref(false)
const collapsed = ref(localStorage.getItem('admin-sidebar-collapsed') === '1')

watch(collapsed, (value) => {
    localStorage.setItem('admin-sidebar-collapsed', value ? '1' : '0')
})

// "collapsed" is a desktop-only preference. Without this, a sidebar collapsed on
// desktop would also open collapsed (icon-only, no labels) on mobile, where there's
// no lg: breakpoint to scope it to — that was the root of the mobile layout breaking.
const isDesktop = ref(false)
let media

onMounted(() => {
    media = window.matchMedia('(min-width: 1024px)')
    isDesktop.value = media.matches
    media.addEventListener('change', updateIsDesktop)
})
onBeforeUnmount(() => media?.removeEventListener('change', updateIsDesktop))

const updateIsDesktop = (event) => (isDesktop.value = event.matches)

const effectiveCollapsed = computed(() => collapsed.value && isDesktop.value)

// Lock page scroll behind the mobile drawer, and let Escape close it.
watch(sidebarOpen, (open) => {
    document.body.style.overflow = open ? 'hidden' : ''
})
onBeforeUnmount(() => (document.body.style.overflow = ''))

const onKeydown = (event) => {
    if (event.key === 'Escape') sidebarOpen.value = false
}
onMounted(() => window.addEventListener('keydown', onKeydown))
onBeforeUnmount(() => window.removeEventListener('keydown', onKeydown))

const permissions = computed(() => page.props.auth.permissions ?? [])
const roles = computed(() => page.props.auth.roles ?? [])
const can = (permission) => !permission || permissions.value.includes(permission)
const hasRole = (role) => roles.value.includes(role)

const ICONS = {
    grid: '<rect x="3.5" y="3.5" width="7" height="7" rx="1.5"/><rect x="13.5" y="3.5" width="7" height="7" rx="1.5"/><rect x="3.5" y="13.5" width="7" height="7" rx="1.5"/><rect x="13.5" y="13.5" width="7" height="7" rx="1.5"/>',
    list: '<rect x="3.5" y="4.5" width="17" height="3.6" rx="1"/><rect x="3.5" y="10.2" width="17" height="3.6" rx="1"/><rect x="3.5" y="15.9" width="11" height="3.6" rx="1"/>',
    box: '<path d="M3.5 8l8.5-4.6L20.5 8 12 12.6 3.5 8z"/><path d="M3.5 8v8l8.5 4.6 8.5-4.6V8"/><path d="M12 12.6V21"/>',
    tag: '<path d="M12 3.5h5.5a2 2 0 012 2V11L11 20.5 3.5 13 12 3.5z"/><circle cx="15.8" cy="7.2" r="1.3" fill="currentColor" stroke="none"/>',
    pin: '<path d="M12 21s6.5-6 6.5-11.2a6.5 6.5 0 10-13 0C5.5 15 12 21 12 21z"/><circle cx="12" cy="9.5" r="2.2"/>',
    wrench: '<path d="M14.9 6.3a3.8 3.8 0 10-5 5L4.5 16.7l2.8 2.8 5.4-5.4a3.8 3.8 0 005-5l-2.5 2.5-1.9-1.9 2.6-2.4z"/>',
    cap: '<path d="M2.5 9L12 4.2 21.5 9 12 13.8 2.5 9z"/><path d="M6.3 10.9V16c0 1.4 2.6 2.8 5.7 2.8s5.7-1.4 5.7-2.8v-5.1"/>',
    chat: '<path d="M4.5 5.5h15a1 1 0 011 1V15a1 1 0 01-1 1H9.5l-4 3.5V16h-1a1 1 0 01-1-1V6.5a1 1 0 011-1z"/>',
    users: '<circle cx="9" cy="8.2" r="3"/><path d="M3 18.8c.5-3.2 2.8-5 6-5s5.5 1.8 6 5"/><circle cx="17.3" cy="9.2" r="2.4"/><path d="M15.6 13.6c2.4.4 4 2.1 4.4 5"/>',
    bars: '<rect x="4" y="12.5" width="3.4" height="7" rx="0.8"/><rect x="10.3" y="7.5" width="3.4" height="12" rx="0.8"/><rect x="16.6" y="4" width="3.4" height="15.5" rx="0.8"/>',
    history: '<path d="M4 12a8 8 0 118 8"/><path d="M4 12V7"/><path d="M4 12H9"/><path d="M12 8v4l3 2"/>',
    gear: '<circle cx="12" cy="12" r="3"/><path d="M12 3v3.2M12 17.8V21M21 12h-3.2M6.2 12H3M17.9 6.1l-2.3 2.3M8.4 15.6l-2.3 2.3M17.9 17.9l-2.3-2.3M8.4 8.4L6.1 6.1"/>',
}

const sections = [
    {
        label: 'Overview',
        items: [{name: 'Dashboard', href: '/admin/dashboard', match: '/admin/dashboard', ready: true, icon: 'grid'}],
    },
    {
        label: 'Sales',
        items: [
            {
                name: 'Orders',
                href: '/admin/orders',
                match: '/admin/orders',
                permission: 'view-orders',
                ready: true,
                icon: 'list'
            },
        ],
    },
    {
        label: 'Catalog',
        items: [
            {
                name: 'Products',
                href: '/admin/products',
                match: '/admin/products',
                permission: 'manage-products',
                ready: true,
                icon: 'box'
            },
            {
                name: 'Categories',
                href: '/admin/categories',
                match: '/admin/categories',
                permission: 'manage-categories',
                ready: true,
                icon: 'tag'
            },
            {
                name: 'Pickup Points',
                href: '/admin/pickup-points',
                match: '/admin/pickup-points',
                permission: 'manage-pickup-points',
                ready: true,
                icon: 'pin'
            },
        ],
    },
    {
        label: 'Content',
        items: [
            {
                name: 'Services',
                href: '/admin/services',
                match: '/admin/services',
                permission: 'manage-services',
                ready: true,
                icon: 'wrench'
            },
            {
                name: 'Training',
                href: '/admin/training',
                match: '/admin/training',
                permission: 'manage-training',
                ready: true,
                icon: 'cap'
            },
            {
                name: 'About Us',
                href: '/admin/about',
                match: '/admin/about',
                permission: 'manage-about',
                ready: true,
                icon: 'users'
            },
            {
                name: 'Contact Channels',
                href: '/admin/contact-channels',
                match: '/admin/contact-channels',
                permission: 'manage-contact',
                ready: true,
                icon: 'chat'
            },
        ],
    },
    {
        label: 'Administration',
        items: [
            {
                name: 'Staff',
                href: '/admin/staff',
                match: '/admin/staff',
                permission: 'manage-staff',
                ready: true,
                icon: 'users'
            },
            {
                name: 'Reports',
                href: '/admin/reports',
                match: '/admin/reports',
                permission: 'view-reports',
                ready: true,
                icon: 'bars'
            },
            {
                name: 'Activity Log',
                href: '/admin/activity-log',
                match: '/admin/activity-log',
                permission: 'view-activity-log',
                ready: true,
                icon: 'history'
            },
            {
                name: 'Settings',
                href: '/admin/settings',
                match: '/admin/settings',
                role: 'super_admin',
                ready: false,
                icon: 'gear'
            },
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
const initials = computed(() => {
    const name = page.props.auth.user?.name ?? ''
    return name.split(' ').slice(0, 2).map((part) => part[0]).join('').toUpperCase() || 'U'
})
</script>

<template>
    <div class="min-h-screen overflow-x-hidden bg-neutral-bg font-sans text-neutral-text">
        <!-- Mobile overlay: fixed to the viewport, so it always covers the screen regardless of scroll -->
        <Transition name="fade">
            <div
                v-if="sidebarOpen"
                class="fixed inset-0 z-30 bg-neutral-text/40 lg:hidden"
                @click="sidebarOpen = false"
            />
        </Transition>

        <!-- Sidebar: solid dark-teal surface, flat fill, no gradient.
             Width and label visibility use `effectiveCollapsed` (desktop-only), so a
             collapsed desktop sidebar never carries over into the mobile drawer. -->
        <aside
            class="scrollBar fixed inset-y-0 left-0 z-40 flex w-64 max-w-[85vw] flex-col bg-primary-dark transition-[width,transform] duration-200 ease-out lg:translate-x-0"
            :class="[sidebarOpen ? 'translate-x-0 shadow-2xl lg:shadow-none' : '-translate-x-full', effectiveCollapsed ? 'lg:w-[76px]' : '']"
        >
            <!-- Brand + collapse toggle -->
            <div class="flex h-16 shrink-0 items-center gap-2 border-b border-white/10 px-4">
                <Link href="/admin/dashboard" class="flex min-w-0 items-center gap-2.5" @click="sidebarOpen = false">
                   <span
                       class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full  text-white shadow-sm transition-transform duration-200 ease-out group-hover:scale-105 group-active:scale-95 motion-reduce:transition-none sm:h-10 sm:w-10"
                   >
        <ApplicationLogo :size="20"/>
    </span>
                    <span class="truncate font-heading text-[15px] font-semibold tracking-tight text-white"
                          :class="effectiveCollapsed ? 'lg:hidden' : ''">
                        Stack Pharmacy
                    </span>
                </Link>

                <button
                    type="button"
                    class="ml-auto hidden h-7 w-7 shrink-0 items-center justify-center rounded-md text-white/40 transition hover:bg-white/10 hover:text-white lg:flex"
                    :aria-label="collapsed ? 'Expand sidebar' : 'Collapse sidebar'"
                    @click="collapsed = !collapsed"
                >
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                         stroke-linecap="round" class="transition-transform duration-200"
                         :class="effectiveCollapsed ? 'rotate-180' : ''">
                        <path d="M15 5l-7 7 7 7"/>
                    </svg>
                </button>

                <!-- Mobile close -->
                <button
                    type="button"
                    class="ml-auto rounded-md p-1.5 text-white/60 transition hover:bg-white/10 hover:text-white lg:hidden"
                    aria-label="Close menu"
                    @click="sidebarOpen = false"
                >
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                         stroke-linecap="round" aria-hidden="true">
                        <path d="M6 6l12 12M18 6L6 18"/>
                    </svg>
                </button>
            </div>

            <!-- Nav -->
            <nav class="flex-1 space-y-5 overflow-y-auto overflow-x-hidden px-3 py-5">
                <div v-for="section in visibleSections" :key="section.label">
                    <p class="mb-1.5 px-2.5 text-[11px] font-medium text-white/35"
                       :class="effectiveCollapsed ? 'lg:hidden' : ''">
                        {{ section.label }}
                    </p>
                    <div class="space-y-0.5">
                        <Link
                            v-for="item in section.items"
                            :key="item.href"
                            :href="item.href"
                            class="group relative flex items-center gap-3 rounded-lg px-2.5 py-2 text-sm transition-colors"
                            :class="[
                                effectiveCollapsed ? 'lg:justify-center lg:px-0' : '',
                                isActive(item) ? 'bg-white font-medium text-primary-dark' : 'text-white/70 hover:bg-white/10 hover:text-white',
                            ]"
                            @click="sidebarOpen = false"
                        >
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                 stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" class="shrink-0"
                                 v-html="ICONS[item.icon]"/>
                            <span class="truncate" :class="effectiveCollapsed ? 'lg:hidden' : ''">{{ item.name }}</span>

                            <!-- Tooltip: desktop-collapsed only, never shown in the mobile drawer -->
                            <span
                                v-if="effectiveCollapsed"
                                class="pointer-events-none absolute left-full top-1/2 z-10 ml-3 hidden -translate-y-1/2 whitespace-nowrap rounded-md bg-primary-dark px-2.5 py-1.5 text-xs font-medium text-white opacity-0 shadow-sm transition-opacity duration-150 group-hover:opacity-100 lg:block"
                            >
                                {{ item.name }}
                            </span>
                        </Link>
                    </div>
                </div>
            </nav>

            <!-- Footer / account -->
            <div class="shrink-0 border-t border-white/10 p-3">
                <div class="mb-2" :class="effectiveCollapsed ? 'lg:flex lg:justify-center' : ''">
                    <PushToggle/>
                </div>
                <div class="flex items-center gap-2.5 rounded-lg px-1.5 py-1.5"
                     :class="effectiveCollapsed ? 'lg:justify-center' : ''">
                    <span
                        class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-secondary text-xs font-semibold text-primary-dark">
                        {{ initials }}
                    </span>
                    <div class="min-w-0" :class="effectiveCollapsed ? 'lg:hidden' : ''">
                        <p class="truncate text-sm font-medium text-white">{{ page.props.auth.user?.name }}</p>
                        <span
                            class="inline-block rounded-full bg-white/10 px-1.5 py-0.5 text-[11px] capitalize leading-none text-white/70">
                            {{ roleLabel }}
                        </span>
                    </div>
                </div>
                <div class="mt-2 flex gap-1" :class="effectiveCollapsed ? 'lg:flex-col lg:items-center' : 'px-1.5'">
                    <Link href="/"
                          class="rounded-md px-1.5 py-1 text-xs text-white/45 transition hover:bg-white/10 hover:text-white">
                        View shop
                    </Link>
                    <Link href="/logout" method="post" as="button"
                          class="rounded-md px-1.5 py-1 text-xs text-white/45 transition hover:bg-white/10 hover:text-white">
                        Log out
                    </Link>
                </div>
            </div>
        </aside>

        <!-- Header: truly fixed to the viewport (not sticky), so it can never scroll away.
             It sits outside the padded content column and carries its own left offset,
             matching the sidebar's current width so it never overlaps it. -->
        <header
            class="fixed inset-x-0 top-0 z-20 flex h-16 items-center gap-3 border-b border-neutral-text/10 bg-white/90 px-4 backdrop-blur transition-[left] duration-200 ease-out sm:px-6"
            :class="effectiveCollapsed ? 'lg:left-[76px]' : 'lg:left-64'"
        >
            <button
                type="button"
                class="-ml-1 rounded-md p-2 text-neutral-text/60 hover:bg-neutral-bg lg:hidden"
                aria-label="Open menu"
                @click="sidebarOpen = true"
            >
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>
            <h1 class="min-w-0 truncate font-heading text-lg font-semibold tracking-tight">{{ title }}</h1>
            <div class="ml-auto">
                <slot name="actions"/>
            </div>
        </header>

        <!-- Main: offset by the header's height and, on large screens, the sidebar's width -->
        <div class="pt-16 transition-[padding] duration-200 ease-out"
             :class="effectiveCollapsed ? 'lg:pl-[76px]' : 'lg:pl-64'">
            <!-- Flash messages -->
            <div v-if="page.props.flash?.success || page.props.flash?.error" class="px-4 pt-4 sm:px-6">
                <div
                    v-if="page.props.flash.success"
                    class="rounded-md border border-primary/20 bg-primary-light px-4 py-3 text-sm text-primary-dark"
                >
                    {{ page.props.flash.success }}
                </div>
                <div
                    v-if="page.props.flash.error"
                    class="rounded-md border border-accent-light/30 bg-accent-light/10 px-4 py-3 text-sm text-accent-light"
                >
                    {{ page.props.flash.error }}
                </div>
            </div>

            <main class="p-4 sm:p-6">
                <slot/>
            </main>
        </div>
    </div>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

.scrollBar {
    scrollbar-width: thin; /* Firefox */
    scrollbar-color: rgba(70, 120, 130, 1) transparent; /* Firefox: thumb, track */
}

.scrollBar::-webkit-scrollbar {
    width: 6px;
    height: 6px;
    background: rgba(70, 120, 130, 1);
}

.scrollBar::-webkit-scrollbar-track {
    background: transparent;
}

.scrollBar::-webkit-scrollbar-thumb {
    background-color: rgba(20, 184, 166, 0.35); /* gray-teal, leaning teal */
    border-radius: 9999px;
    border: 2px solid transparent;
    background-clip: padding-box; /* gives the thumb a little breathing room, looks "floating" */
}

.scrollBar::-webkit-scrollbar-thumb:hover {
    background-color: rgba(20, 184, 166, 0.55);
}

.scrollBar::-webkit-scrollbar-button {
    display: none;
    width: 0;
    height: 0;
}

@media (prefers-reduced-motion: reduce) {
    .fade-enter-active,
    .fade-leave-active {
        transition: none;
    }
}
</style>
