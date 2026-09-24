<script setup>
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'

const props = defineProps({
    channels: { type: Array, default: () => [] },
})

const page = usePage()

const platformMeta = {
    whatsapp: {
        label: 'WhatsApp',
        icon: 'M21 12a8 8 0 0 1-8 8H8l-5 3 1.5-4.5A8 8 0 1 1 21 12z',
        href: (handle) => `https://wa.me/${handle.replace(/\D/g, '')}`,
        external: true,
    },
    phone: {
        label: 'Phone',
        icon: 'M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z',
        href: (handle) => `tel:${handle.replace(/\s/g, '')}`,
        external: false,
    },
    email: {
        label: 'Email',
        icon: 'M4 4h16v16H4zM4 6l8 7 8-7',
        href: (handle) => `mailto:${handle}`,
        external: false,
    },
    instagram: {
        label: 'Instagram',
        icon: 'M12 2c2.7 0 3.06.01 4.12.06 1.06.05 1.79.22 2.43.47a4.92 4.92 0 0 1 1.78 1.16 4.92 4.92 0 0 1 1.16 1.78c.25.64.42 1.37.47 2.43.05 1.06.06 1.42.06 4.12s-.01 3.06-.06 4.12c-.05 1.06-.22 1.79-.47 2.43a4.92 4.92 0 0 1-1.16 1.78 4.92 4.92 0 0 1-1.78 1.16c-.64.25-1.37.42-2.43.47-1.06.05-1.42.06-4.12.06s-3.06-.01-4.12-.06c-1.06-.05-1.79-.22-2.43-.47a4.92 4.92 0 0 1-1.78-1.16 4.92 4.92 0 0 1-1.16-1.78c-.25-.64-.42-1.37-.47-2.43C2.01 15.06 2 14.7 2 12s.01-3.06.06-4.12c.05-1.06.22-1.79.47-2.43a4.92 4.92 0 0 1 1.16-1.78A4.92 4.92 0 0 1 5.47 2.51c.64-.25 1.37-.42 2.43-.47C8.94 2.01 9.3 2 12 2zm0 5a5 5 0 1 0 0 10 5 5 0 0 0 0-10zm0 8.2a3.2 3.2 0 1 1 0-6.4 3.2 3.2 0 0 1 0 6.4zM17.4 6.6a1.17 1.17 0 1 1-2.34 0 1.17 1.17 0 0 1 2.34 0z',
        href: null,
        external: true,
    },
}

const defaultMeta = {
    label: 'Contact',
    icon: 'M12 2l8 4v6c0 5-3.5 8.5-8 10-4.5-1.5-8-5-8-10V6l8-4zM9 12l2 2 4-4',
    href: null,
    external: true,
}

const metaFor = (channel) => platformMeta[channel.platform] ?? defaultMeta

const hrefFor = (channel) => {
    const meta = metaFor(channel)
    if (channel.url) return channel.url
    return meta.href ? meta.href(channel.handle) : null
}

const sortedChannels = computed(() =>
    [...props.channels].sort((a, b) => a.display_order - b.display_order)
)
</script>

<template>
    <div class="mx-auto max-w-6xl px-4 py-10 sm:px-6 md:py-12">
        <div>
            <h1 class="font-heading text-xl font-semibold tracking-tight text-neutral-text sm:text-2xl">
                Get in touch
            </h1>
            <p class="mt-1 max-w-lg text-sm text-neutral-text/60">
                However you'd rather reach us, we're here — pick whatever's easiest.
            </p>
        </div>

        <div v-if="sortedChannels.length" class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">

           <a v-for="channel in sortedChannels"
            :key="channel.id"
            :href="hrefFor(channel)"
            :target="metaFor(channel).external ? '_blank' : undefined"
            :rel="metaFor(channel).external ? 'noopener' : undefined"
            class="group flex items-center gap-3.5 rounded-xl border border-neutral-text/10 bg-white p-5 transition-all duration-200 hover:-translate-y-0.5 hover:border-primary-dark/25 hover:shadow-[0_12px_32px_-12px_rgba(15,23,22,0.16)]"
            >
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-primary-light text-primary-dark">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path :d="metaFor(channel).icon" />
                    </svg>
                </span>
            <div class="min-w-0 flex-1">
                <p class="text-sm font-medium text-neutral-text">{{ metaFor(channel).label }}</p>
                <p class="mt-0.5 truncate text-sm text-neutral-text/60">{{ channel.handle }}</p>
            </div>
            <svg
                width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"
                class="ml-auto shrink-0 text-neutral-text/25 transition-transform duration-200 group-hover:translate-x-0.5 group-hover:text-primary-dark"
            >
                <path d="M5 12h14M13 6l6 6-6 6" />
            </svg>
            </a>
        </div>

        <div v-else class="mt-8 rounded-xl border border-dashed border-neutral-text/20 px-6 py-16 text-center">
            <p class="text-sm text-neutral-text/70">No contact channels set up yet.</p>
        </div>
    </div>
</template>
