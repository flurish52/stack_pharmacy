<script setup>
/**
 * Design A — full-bleed photo with copy overlaid directly on it.
 * Corrections from the previous pass: stronger/dual-direction scrim,
 * a drop-shadow on the headline, and higher-opacity text so copy stays
 * readable regardless of what's happening in the photo underneath.
 * Same graceful fallback as before if heroImageSrc is empty or 404s.
 */
import { ref } from 'vue'

defineProps({
    headline: {
        type: String,
        default: 'The pharmacy on your street, open in your pocket.',
    },
    subcopy: {
        type: String,
        default:
            'Order medication and everyday health essentials, book same-day delivery, or talk to a pharmacist on WhatsApp before you buy.',
    },
    trustLine: {
        type: String,
        default: 'Every order is checked by a licensed pharmacist before it ships.',
    },
    shopHref: { type: String, default: '/shop' },
    shopLabel: { type: String, default: 'Shop medication' },
    whatsappHref: { type: String, default: 'https://wa.me/' },
    whatsappLabel: { type: String, default: 'Talk to a pharmacist' },
    heroImageSrc: { type: String, default: '' },
    heroImageAlt: {
        type: String,
        default: 'A delivery rider on the way with a customer\'s medicine order',
    },
    heroImagePosition: { type: String, default: 'center' },
})

const imageFailed = ref(false)
</script>

<template>
    <section class="relative isolate overflow-hidden">
        <img
            v-if="heroImageSrc && !imageFailed"
            :src="heroImageSrc"
            :alt="heroImageAlt"
            :style="{ objectPosition: heroImagePosition }"
            class="absolute inset-0 h-full w-full object-cover"
            @error="imageFailed = true"
        />
        <div
            v-else
            class="absolute inset-0 bg-gradient-to-br from-primary-dark via-primary to-primary-light"
            role="img"
            :aria-label="heroImageAlt"
        >
            <div class="absolute inset-0 flex items-center justify-center">
                <div class="relative h-16 w-16 opacity-20">
                    <span class="absolute left-1/2 top-0 h-full w-3 -translate-x-1/2 rounded-full bg-white"></span>
                    <span class="absolute left-0 top-1/2 h-3 w-full -translate-y-1/2 rounded-full bg-white"></span>
                </div>
            </div>
        </div>

        <!-- Scrim, split by breakpoint on purpose: on mobile the text spans
             nearly the full width, so a left-to-right fade leaves the right
             side of every line sitting on bare photo. Mobile gets a flat,
             uniformly strong overlay instead; the directional gradient only
             kicks in once there's a real text column with image visible
             beside it. -->
        <div class="absolute inset-0 bg-black/65 md:hidden"></div>
        <div class="absolute inset-0 hidden bg-gradient-to-r from-black/85 via-black/55 to-black/15 md:block"></div>
        <div class="absolute inset-0 bg-gradient-to-t from-black/45 via-transparent to-black/10"></div>

        <div class="relative mx-auto flex min-h-[560px] max-w-6xl flex-col justify-center px-6 py-24  ">
            <h1
                class="hero-in hero-in--1 max-w-lg font-heading text-4xl font-semibold leading-tight text-white drop-shadow-md md:text-5xl lg:text-[3.25rem]"
            >
                {{ headline }}
            </h1>
            <p class="hero-in hero-in--2 mt-5 max-w-md text-white/90 drop-shadow-sm">{{ subcopy }}</p>

            <div class="hero-in hero-in--3 mt-8 flex flex-wrap gap-3">
                <a
                    :href="shopHref"
                    class="rounded bg-accent px-6 py-3 text-sm font-medium text-white transition hover:opacity-90"
                >
                    {{ shopLabel }}
                </a>
                <a
                    :href="whatsappHref"
                    class="inline-flex items-center gap-2 rounded border border-white/50 px-6 py-3 text-sm font-medium text-white backdrop-blur-sm transition hover:border-white hover:bg-white/10"
                >
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path d="M12 2a10 10 0 0 0-8.6 15.1L2 22l5-1.3A10 10 0 1 0 12 2Zm0 18a8 8 0 0 1-4.1-1.1l-.3-.2-3 .8.8-2.9-.2-.3A8 8 0 1 1 12 20Zm4.4-6c-.2-.1-1.4-.7-1.6-.8-.2-.1-.4-.1-.6.1-.2.3-.6.8-.8 1-.1.1-.3.2-.5.1a6.5 6.5 0 0 1-3.3-2.9c-.2-.4.2-.4.5-1.3.1-.1.1-.3 0-.4-.1-.1-.6-1.4-.8-1.9-.2-.5-.4-.4-.6-.4h-.5c-.2 0-.5.1-.7.3-.2.3-.9.9-.9 2.2s1 2.5 1.1 2.7c.1.1 2 3 4.8 4.3.7.3 1.2.5 1.6.6.7.2 1.3.2 1.8.1.5-.1 1.4-.6 1.6-1.1.2-.5.2-1 .1-1.1-.1-.1-.2-.2-.5-.3Z" />
                    </svg>
                    {{ whatsappLabel }}
                </a>
            </div>

            <p class="hero-in hero-in--4 mt-6 text-sm text-white/80 drop-shadow-sm">{{ trustLine }}</p>
        </div>
    </section>
</template>

<style scoped>
/* One orchestrated entrance on load: fade + small rise, staggered.
   Cheap (opacity/transform only, GPU-composited), no layout shift since
   elements keep their box — only opacity/transform change. */
.hero-in {
    opacity: 0;
    transform: translateY(14px);
    animation: hero-fade-up 0.6s ease-out forwards;
}
.hero-in--1 { animation-delay: 0.05s; }
.hero-in--2 { animation-delay: 0.18s; }
.hero-in--3 { animation-delay: 0.3s; }
.hero-in--4 { animation-delay: 0.42s; }

@keyframes hero-fade-up {
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@media (prefers-reduced-motion: reduce) {
    .hero-in {
        opacity: 1;
        transform: none;
        animation: none;
    }
}
</style>
