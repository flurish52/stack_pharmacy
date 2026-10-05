<script setup>
/**
 * Closing contact band. The full-bleed WhatsApp green read as an ad; the
 * band now sits in the brand's deep teal and lets the green live only in
 * the button, where it's actually a signal. The href is wired to the
 * shared pharmacy number instead of a dead "#".
 */
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'

const props = defineProps({
    message: { type: String, default: 'Hello Stack Pharmacy, I need some help.' },
})

const page = usePage()

const whatsappHref = computed(() => {
    const number = page.props.pharmacyWhatsapp
    return `https://wa.me/${number}?text=${encodeURIComponent(props.message)}`
})
</script>

<template>
    <section class="relative overflow-hidden bg-primary-dark">
        <div class="pointer-events-none absolute -right-16 -top-16 h-56 w-56 rounded-full bg-white/5" />
        <div class="pointer-events-none absolute -bottom-20 left-1/4 h-48 w-48 rounded-full bg-white/5" />

        <div class="relative mx-auto flex max-w-6xl flex-col items-start gap-5 px-4 py-10 text-white sm:px-6 md:flex-row md:items-center md:justify-between md:py-12">
            <div>
                <span class="inline-flex items-center rounded-md bg-white/10 px-2.5 py-1 text-xs font-medium text-white/80">
                    Talk to a pharmacist
                </span>
                <h2 class="mt-3 font-heading text-xl font-semibold leading-tight tracking-tight sm:text-2xl">
                    Not sure what you need? Just ask.
                </h2>
                <p class="mt-1.5 max-w-md text-sm leading-relaxed text-white/70">
                    A pharmacist replies during opening hours, Monday to Saturday.
                </p>
            </div>


            <a :href="whatsappHref"
            target="_blank"
            rel="noopener"
               class="inline-flex shrink-0 items-center gap-2 rounded-md bg-white px-4 py-2.5 text-sm font-medium text-primary-dark shadow-sm transition-colors duration-200 hover:bg-white/90"            >
            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M21 12a8 8 0 0 1-8 8H8l-5 3 1.5-4.5A8 8 0 1 1 21 12z" />
            </svg>
            Message us on WhatsApp
            </a>
        </div>
    </section>
</template>
