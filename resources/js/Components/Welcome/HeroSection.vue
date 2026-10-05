<script setup>
/**
 * Home page hero. Copy is the client's exact text.
 *
 * Split layout: copy + two buttons on the left, the client's flyer on the right
 * with the round logo as a badge on the panel's corner. If an image is empty or
 * 404s, the panel falls back to a flat palette tile.
 *
 */
import { computed, ref } from 'vue'
import { Link } from '@inertiajs/vue3'

const props = defineProps({
    // The client's headline is one line with a dash between the two halves.
    // Shown as two lines instead, so every word stays and no dash is needed.
    name: { type: String, default: 'Stack Pharmacy, Ltd' },
    tagline: { type: String, default: 'Genuine Drugs for Everybody' },
    eyebrow: { type: String, default: 'Your Trusted Community Pharmacy at Cyclist Park, Obudu' },
    subcopy: {
        type: String,
        default:
            'Your health is our priority. We provide 100% genuine, affordable medicines for everybody: children, adults, and the elderly.',
    },
    shopHref: { type: String, default: '/shop' },
    shopLabel: { type: String, default: 'Shop Now' },
    whatsappHref: {
        type: String,
        default:
            'https://wa.me/2348034620196?text=Hello%20Stack%20Pharmacy%2C%20I%20was%20on%20your%20website%20and%20I%20would%20like%20to%20make%20an%20enquiry.',
    },
    whatsappLabel: { type: String, default: 'Chat on WhatsApp' },
    // Put the client's files in /public/images (or change these paths).
    flyerSrc: { type: String, default: '/images/flyer.jpg' },
    flyerAlt: { type: String, default: 'Stack Pharmacy, Ltd flyer' },
    logoSrc: { type: String, default: '/images/logo-round.png' },
})

const flyerFailed = ref(false)
const logoFailed = ref(false)
const showFlyer = computed(() => !!props.flyerSrc && !flyerFailed.value)
const showLogo = computed(() => !!props.logoSrc && !logoFailed.value)
</script>

<template>
    <section class="relative isolate overflow-hidden bg-neutral-bg">
        <div class="pointer-events-none absolute -right-40 -top-4 -z-10 h-[32rem] w-[32rem] rounded-full bg-primary-light" aria-hidden="true" />
        <div class="pointer-events-none absolute -bottom-32 -left-32 -z-10 h-80 w-80 rounded-full bg-secondary/40" aria-hidden="true" />

        <div class="mx-auto flex max-w-6xl flex-col-reverse items-center gap-12 px-6 pb-16 pt-10 md:pt-14 lg:grid lg:grid-cols-[1.05fr_0.95fr] lg:gap-8 lg:pb-20 lg:pt-16">
            <!-- Copy -->
            <div class="mt-[-70px]">
                <p class="hero-in hero-in--1 inline-flex items-center gap-2 rounded-full border border-primary/20 bg-white px-3 py-1 text-xs font-medium text-primary-dark shadow-sm">
                    <span class="h-2 w-2 shrink-0 rounded-full bg-primary" aria-hidden="true" />
                    {{ eyebrow }}
                </p>

                <h1 class="hero-in hero-in--2 mt-5 max-w-xl font-heading text-[2.25rem] font-semibold leading-[1.1] tracking-tight text-neutral-text sm:text-5xl">
                    <span class="block">{{ name }}</span>
                    <span class="block text-primary-dark">{{ tagline }}</span>
                </h1>

                <p class="hero-in hero-in--3 mt-5 max-w-lg text-base leading-relaxed text-neutral-text/70 sm:text-lg">
                    {{ subcopy }}
                </p>

                <div class="hero-in hero-in--4 mt-8 flex flex-col gap-3 sm:flex-row">
                    <Link
                        :href="shopHref"
                        class="inline-flex items-center justify-center rounded-xl bg-primary-dark px-6 py-3.5 text-sm font-semibold text-white shadow-sm transition duration-150 hover:bg-primary-dark/90 hover:shadow-md focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/50 focus-visible:ring-offset-2 focus-visible:ring-offset-neutral-bg active:scale-[0.98]"
                    >
                        {{ shopLabel }}
                    </Link>

                    <a
                        :href="whatsappHref"
                        target="_blank"
                        rel="noopener"
                        class="inline-flex items-center justify-center gap-2.5 rounded-xl border border-neutral-text/15 bg-white px-5 py-3.5 text-sm font-semibold text-neutral-text transition duration-150 hover:border-whatsapp hover:bg-whatsapp/10 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-whatsapp/60 focus-visible:ring-offset-2 focus-visible:ring-offset-neutral-bg active:scale-[0.98]"
                    >
                        <span class="flex h-6 w-6 items-center justify-center rounded-full bg-whatsapp text-white">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                <path d="M12 2a10 10 0 0 0-8.6 15.1L2 22l5-1.3A10 10 0 1 0 12 2Zm0 18a8 8 0 0 1-4.1-1.1l-.3-.2-3 .8.8-2.9-.2-.3A8 8 0 1 1 12 20Zm4.4-6c-.2-.1-1.4-.7-1.6-.8-.2-.1-.4-.1-.6.1-.2.3-.6.8-.8 1-.1.1-.3.2-.5.1a6.5 6.5 0 0 1-3.3-2.9c-.2-.4.2-.4.5-1.3.1-.1.1-.3 0-.4-.1-.1-.6-1.4-.8-1.9-.2-.5-.4-.4-.6-.4h-.5c-.2 0-.5.1-.7.3-.2.3-.9.9-.9 2.2s1 2.5 1.1 2.7c.1.1 2 3 4.8 4.3.7.3 1.2.5 1.6.6.7.2 1.3.2 1.8.1.5-.1 1.4-.6 1.6-1.1.2-.5.2-1 .1-1.1-.1-.1-.2-.2-.5-.3Z" />
                            </svg>
                        </span>
                        {{ whatsappLabel }}
                    </a>
                </div>
            </div>

            <!-- Visual: flyer panel + round logo badge -->
            <div class="hero-in hero-in--visual relative mx-auto aspect-[4/5] w-full max-w-sm sm:max-w-md lg:max-w-none lg:h-[34rem] lg:aspect-auto">
                <div class="absolute inset-0 overflow-hidden rounded-[2rem] bg-secondary shadow-sm ring-1 ring-primary-dark/10">
                    <img
                        v-if="showFlyer"
                        :src="flyerSrc"
                        :alt="flyerAlt"
                        class="absolute inset-0 h-full w-full object-cover object-top"
                        fetchpriority="high"
                        decoding="async"
                        @error="flyerFailed = true"
                    />
                    <div v-else class="absolute inset-0 flex items-center justify-center" role="img" :aria-label="flyerAlt">
                        <div class="relative h-24 w-24 text-primary-dark/20">
                            <span class="absolute left-1/2 top-0 h-full w-5 -translate-x-1/2 rounded-full bg-current" />
                            <span class="absolute left-0 top-1/2 h-5 w-full -translate-y-1/2 rounded-full bg-current" />
                        </div>
                    </div>
                </div>

                <img
                    v-if="showLogo"
                    :src="logoSrc"
                    alt="Stack Pharmacy, Ltd logo"
                    class="absolute -bottom-5 -left-3 z-10 h-24 w-24 rounded-full bg-white object-cover shadow-lg ring-4 ring-white sm:-left-6 sm:h-28 sm:w-28"
                    decoding="async"
                    @error="logoFailed = true"
                />
            </div>
        </div>
    </section>
</template>

<style scoped>
/* One entrance on load: fade + small rise, staggered. Only opacity/transform change. */
.hero-in {
    opacity: 0;
    transform: translateY(14px);
    animation: hero-fade-up 0.6s ease-out forwards;
}
.hero-in--1 { animation-delay: 0.05s; }
.hero-in--2 { animation-delay: 0.15s; }
.hero-in--3 { animation-delay: 0.27s; }
.hero-in--4 { animation-delay: 0.38s; }
.hero-in--visual { animation-delay: 0.2s; animation-duration: 0.8s; }

@keyframes hero-fade-up {
    to { opacity: 1; transform: translateY(0); }
}

@media (prefers-reduced-motion: reduce) {
    .hero-in { opacity: 1; transform: none; animation: none; }
}
</style>
