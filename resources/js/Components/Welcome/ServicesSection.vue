<script setup>
/**
 * Services. Image-first: a service with a real photo (image_url) shows it
 * in the tile; anything without one falls back to the line-icon so the
 * grid never looks broken mid-rollout of photography. Cards match height
 * in a row and the "Chat with us" link sits pinned to the bottom via
 * mt-auto, so a short description doesn't leave the link floating mid-card.
 */
import SectionHeading from './SectionHeading.vue'

defineProps({
    services: { type: Array, default: () => [] },
})

const icons = {
    consultation: 'M8 10h8M8 14h5M21 12a9 9 0 1 1-4.06-7.5L21 3l-.94 4.06A8.96 8.96 0 0 1 21 12z',
    delivery: 'M3 7h11v9H3zM14 10h4l3 3v3h-7zM6.5 20a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3zM17.5 20a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3z',
    prescription: 'M6 3h9l3 3v15H6zM15 3v3h3M9 12h6M9 15.5h6M9 8.5h3',
    'health-check': 'M4 12h4l2-7 4 14 2-7h4',
    training: 'M4 6.5 12 3l8 3.5-8 3.5-8-3.5zM7 10v5.5c0 1.5 2.2 3 5 3s5-1.5 5-3V10',
    default: 'M12 2l8 4v6c0 5-3.5 8.5-8 10-4.5-1.5-8-5-8-10V6l8-4zM9 12l2 2 4-4',
}

const iconFor = (service) => icons[service.icon] ?? icons.default
</script>

<template>
    <section v-if="services.length" class="relative mx-auto max-w-6xl px-4 py-12 sm:px-6 md:py-16">
        <div class="pointer-events-none absolute inset-x-0 top-0 -z-10 flex justify-center">
            <div class="h-48 w-[30rem] rounded-full bg-primary-light/50 blur-3xl" />
        </div>

        <SectionHeading
            title="Need more than a product?"
            lead="Some questions a product page can't answer. Bring them to a pharmacist instead."
            class="[&_h2]:font-heading [&_h2]:text-xl [&_h2]:font-semibold [&_h2]:tracking-tight [&_h2]:text-neutral-text sm:[&_h2]:text-2xl [&_p]:mt-1 [&_p]:text-sm [&_p]:text-neutral-text/60"
        />

        <div class="mt-8 grid gap-5 md:grid-cols-3">
            <article
                v-for="(service, index) in services"
                :key="service.id"
                class="group relative flex flex-col overflow-hidden rounded-xl border border-neutral-text/10 bg-white transition-all duration-300 ease-out hover:-translate-y-0.5 hover:border-primary-dark/25 hover:shadow-[0_12px_32px_-12px_rgba(15,23,22,0.18)]"
            >
                <span
                    class="absolute inset-x-0 top-0 z-10 h-[2px] origin-left scale-x-0 bg-gradient-to-r from-primary-dark to-primary-light transition-transform duration-300 ease-out group-hover:scale-x-100"
                />

                <!-- Image header when available; otherwise the icon tile sits directly in the padded body -->
                <div v-if="service.image_url" class="relative aspect-[16/9] w-full overflow-hidden bg-primary-light">
                    <img
                        :src="service.image_url"
                        :alt="service.name"
                        class="h-full w-full object-cover transition-transform duration-500 ease-out group-hover:scale-105"
                        loading="lazy"
                    />
                </div>

                <div :class="['flex flex-1 flex-col', service.image_url ? 'p-5' : 'p-6']">
                    <div class="flex items-start justify-between">
                        <span
                            v-if="!service.image_url"
                            class="flex h-10 w-10 items-center justify-center rounded-lg bg-primary-light text-primary-dark ring-1 ring-primary-dark/10 transition-transform duration-300 ease-out group-hover:scale-105 group-hover:bg-primary-dark group-hover:text-white group-hover:ring-primary-dark/0"
                        >
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path :d="iconFor(service)" />
                            </svg>
                        </span>
                        <span v-else class="h-10 w-10" aria-hidden="true" />

                        <span class="font-heading text-xs font-medium tabular-nums text-neutral-text/25">
                            {{ String(index + 1).padStart(2, '0') }}
                        </span>
                    </div>

                    <h3 class="mt-3 font-heading text-[0.95rem] font-semibold tracking-tight text-neutral-text">
                        {{ service.name }}
                    </h3>
                    <p class="mt-1.5 flex-1 text-[0.8rem] leading-relaxed text-neutral-text/60">
                        {{ service.description }}
                    </p>
                    <div class="mt-4 border-t border-neutral-text/10 pt-4">

                        <a :href="service.whatsapp_url"
                           target="_blank"
                           rel="noopener"
                           class="inline-flex items-center gap-1.5 rounded-md bg-primary-dark px-3.5 py-2 text-[0.8rem] font-medium text-white shadow-sm shadow-whatsapp/25 transition-all duration-200 hover:bg-primary hover:shadow-md hover:shadow-whatsapp/30"
                        >
                            Chat with us
                            <svg
                                width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"
                                class="transition-transform duration-200 group-hover:translate-x-1"
                            >
                                <path d="M5 12h14M13 6l6 6-6 6" />
                            </svg>
                        </a>
                    </div>
                </div>
            </article>
        </div>
    </section>
</template>
