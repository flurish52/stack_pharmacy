<script setup>
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'

const props = defineProps({
    services: { type: Array, default: () => [] },
})

const page = usePage()

const whatsappHref = (service) => {
    const number = page.props.pharmacyWhatsapp
    const message = service.whatsapp_message ?? `Hi, I would like to know more about ${service.name}.`
    return `https://wa.me/${number}?text=${encodeURIComponent(message)}`
}

const activeServices = computed(() => props.services.filter((s) => s.is_active !== false))
</script>

<template>
    <div class="mx-auto max-w-6xl px-4 py-10 sm:px-6 md:py-12">
        <div>
            <h1 class="font-heading text-xl font-semibold tracking-tight text-neutral-text sm:text-2xl">
                Services
            </h1>
            <p class="mt-1 max-w-lg text-sm text-neutral-text/60">
                Some questions a product page can't answer. Bring them to a pharmacist instead.
            </p>
        </div>

        <div v-if="activeServices.length" class="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            <article
                v-for="service in activeServices"
                :key="service.id"
                class="group flex flex-col overflow-hidden rounded-xl border border-neutral-text/10 bg-white transition-all duration-300 ease-out hover:-translate-y-0.5 hover:border-primary-dark/25 hover:shadow-[0_12px_32px_-12px_rgba(15,23,22,0.18)]"
            >
                <!-- Image slot: shows the real photo once image_url exists, otherwise a
                     clearly-intentional "no preview" placeholder — never a broken <img>. -->
                <div class="relative aspect-[16/10] w-full overflow-hidden bg-neutral-bg">
                    <img
                        v-if="service.image_url"
                        :src="service.image_url"
                        :alt="service.name"
                        class="h-full w-full object-cover transition-transform duration-500 ease-out group-hover:scale-105"
                        loading="lazy"
                    />
                    <div v-else class="flex h-full w-full flex-col items-center justify-center gap-1.5 text-neutral-text/35">
                        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <rect x="3" y="3" width="18" height="18" rx="2" />
                            <circle cx="8.5" cy="8.5" r="1.5" />
                            <path d="m21 15-5-5L5 21" />
                        </svg>
                        <span class="text-xs font-medium">No preview available</span>
                    </div>
                </div>

                <div class="flex flex-1 flex-col p-5">
                    <h2 class="font-heading text-[0.95rem] font-semibold tracking-tight text-neutral-text">
                        {{ service.name }}
                    </h2>
                    <p class="mt-1.5 flex-1 text-[0.8rem] leading-relaxed text-neutral-text/60">
                        {{ service.description }}
                    </p>

                    <div class="mt-4 border-t border-neutral-text/10 pt-4">

                        <a :href="whatsappHref(service)"
                        target="_blank"
                        rel="noopener"
                        class="inline-flex items-center gap-1.5 rounded-md border border-primary-dark/25 px-3.5 py-2 text-[0.8rem] font-medium text-primary-dark transition-colors duration-200 hover:border-primary-dark hover:bg-primary-dark hover:text-white"
                        >
                        Chat with us
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" class="transition-transform duration-200 group-hover:translate-x-1">
                            <path d="M5 12h14M13 6l6 6-6 6" />
                        </svg>
                        </a>
                    </div>
                </div>
            </article>
        </div>

        <div v-else class="mt-8 rounded-xl border border-dashed border-neutral-text/20 px-6 py-16 text-center">
            <p class="text-sm text-neutral-text/70">No services available right now.</p>
        </div>
    </div>
</template>
