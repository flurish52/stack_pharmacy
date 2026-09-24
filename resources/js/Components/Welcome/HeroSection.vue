<script setup>
/**
 * Home page hero.
 *
 * Split layout: copy and calls to action on the left, a layered image panel on the right.
 * The panel stacks `backgroundImageSrc` (the scene) under `heroImageSrc` (the subject) and has
 * floating highlight chips. Every image degrades gracefully: if a source is empty or 404s,
 * the panel falls back to a flat palette tile, so the hero never looks broken.
 *
 * The bottom padding is deliberate: <TrustStrip> overlaps the hero with a negative margin
 * on the home page, and this leaves room for that overlap.
 */
import { computed, ref } from 'vue'
import { Link } from '@inertiajs/vue3'

const props = defineProps({
    headline: {
        type: String,
        default: 'The pharmacy on your street, open in your pocket.',
    },
    // A phrase inside the headline to emphasise. Ignored if it isn't found in the headline.
    headlineHighlight: { type: String, default: 'open in your pocket' },
    subcopy: {
        type: String,
        default:
            'Order medication and everyday health essentials, book same-day delivery, or talk to a pharmacist on WhatsApp before you buy.',
    },
    eyebrow: { type: String, default: 'Licensed online pharmacy' },
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
        default: "A delivery rider on the way with a customer's medicine order",
    },
    heroImagePosition: { type: String, default: 'center bottom' },
    backgroundImageSrc: { type: String, default: '' },
    backgroundImagePosition: { type: String, default: 'center' },
    // Two floating chips over the image. `icon` is 'truck' | 'whatsapp' | 'shield'.
    highlights: {
        type: Array,
        default: () => [
            { icon: 'truck', title: 'Same-day delivery', text: 'Right to your door' },
            { icon: 'whatsapp', title: 'Pharmacist on WhatsApp', text: 'Ask before you buy' },
        ],
    },
})

const heroFailed = ref(false)
const backgroundFailed = ref(false)

const showHero = computed(() => !!props.heroImageSrc && !heroFailed.value)
const showBackground = computed(() => !!props.backgroundImageSrc && !backgroundFailed.value)

// Split the headline around the highlighted phrase so it can be styled on its own.
const headlineParts = computed(() => {
    const phrase = props.headlineHighlight
    const index = phrase ? props.headline.indexOf(phrase) : -1

    if (index === -1) return [{ text: props.headline, highlight: false }]

    return [
        { text: props.headline.slice(0, index), highlight: false },
        { text: phrase, highlight: true },
        { text: props.headline.slice(index + phrase.length), highlight: false },
    ].filter((part) => part.text)
})

const chipPositions = ['-left-2 top-10 sm:-left-8 sm:top-10', '-right-2 bottom-8 sm:-right-6 sm:bottom-12']
const chipTones = {
    truck: 'bg-secondary text-primary-dark',
    shield: 'bg-secondary text-primary-dark',
    whatsapp: 'bg-whatsapp text-white',
}
const chipIcons = {
    truck: '<rect x="1.5" y="7" width="13" height="9" rx="1"/><path d="M14.5 10.5h4l3 3V16h-7z"/><circle cx="6" cy="18" r="1.7"/><circle cx="16.5" cy="18" r="1.7"/>',
    shield: '<path d="M12 3l7.5 3v5.5c0 4.5-3.2 8-7.5 9.5-4.3-1.5-7.5-5-7.5-9.5V6L12 3z"/><path d="m8.5 12 2.5 2.5 4.5-5"/>',
}
</script>

<template>
    <section class="relative isolate overflow-hidden bg-neutral-bg">
        <!-- Background shapes: flat, quiet, behind everything -->
        <div class="pointer-events-none absolute -right-40 -top-40 -z-10 h-[32rem] w-[32rem] rounded-full bg-primary-light" aria-hidden="true" />
        <div class="pointer-events-none absolute -bottom-32 -left-32 -z-10 h-80 w-80 rounded-full bg-secondary/40" aria-hidden="true" />
        <svg class="pointer-events-none absolute left-6 top-24 -z-10 hidden text-primary/25 lg:block" width="96" height="96" aria-hidden="true">
            <defs>
                <pattern id="hero-dots" width="16" height="16" patternUnits="userSpaceOnUse">
                    <circle cx="2" cy="2" r="1.6" fill="currentColor" />
                </pattern>
            </defs>
            <rect width="96" height="96" fill="url(#hero-dots)" />
        </svg>

        <div class="mx-auto flex flex-col-reverse md:grid px-6 max-w-6xl items-center gap-12 px-6 pb-24 pt-10 md:pt-14 lg:grid-cols-[1.05fr_0.95fr] lg:gap-8 lg:pb-32 lg:pt-16">
            <!-- Copy -->
            <div>
                <p
                    class="hero-in hero-in--1 inline-flex items-center gap-2 rounded-full border border-primary/20 bg-white px-3 py-1 text-xs font-medium text-primary-dark shadow-sm"
                >
                    <span class="relative flex h-2 w-2" aria-hidden="true">
                        <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-primary opacity-60 motion-reduce:animate-none" />
                        <span class="relative inline-flex h-2 w-2 rounded-full bg-primary" />
                    </span>
                    {{ eyebrow }}
                </p>

                <h1
                    class="hero-in hero-in--2 mt-5 max-w-xl font-heading text-[2.5rem] font-semibold leading-[1.1] tracking-tight text-neutral-text sm:text-5xl lg:text-[3.5rem]"
                >
                    <template v-for="(part, index) in headlineParts" :key="index">
                        <span v-if="part.highlight" class="relative isolate whitespace-nowrap text-primary-dark">
                            <span class="absolute -inset-x-1 bottom-[0.08em] -z-10 h-[0.4em] rounded-sm bg-secondary" aria-hidden="true" />
                            {{ part.text }}
                        </span>
                        <template v-else>{{ part.text }}</template>
                    </template>
                </h1>

                <p class="hero-in hero-in--3 mt-5 max-w-lg text-base leading-relaxed text-neutral-text/70 sm:text-lg">
                    {{ subcopy }}
                </p>

                <div class="hero-in hero-in--4 mt-8 flex flex-col gap-3 sm:flex-row">
                    <Link
                        :href="shopHref"
                        class="group inline-flex items-center justify-center gap-2 rounded-xl bg-primary-dark px-6 py-3.5 text-sm font-semibold text-white shadow-sm transition duration-150 hover:bg-primary-dark/90 hover:shadow-md focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/50 focus-visible:ring-offset-2 focus-visible:ring-offset-neutral-bg active:scale-[0.98]"
                    >
                        {{ shopLabel }}
                        <svg class="h-4 w-4 transition-transform duration-200 group-hover:translate-x-0.5 motion-reduce:transition-none" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M4 10h11m-4-4 4 4-4 4" />
                        </svg>
                    </Link>

                    <a
                        :href="whatsappHref"
                        target="_blank"
                        rel="noopener"
                        class="group inline-flex items-center justify-center gap-2.5 rounded-xl border border-neutral-text/15 bg-white px-5 py-3.5 text-sm font-semibold text-neutral-text transition duration-150 hover:border-whatsapp hover:bg-whatsapp/10 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-whatsapp/60 focus-visible:ring-offset-2 focus-visible:ring-offset-neutral-bg active:scale-[0.98]"
                    >
                        <span class="flex h-6 w-6 items-center justify-center rounded-full bg-whatsapp text-white">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                <path d="M12 2a10 10 0 0 0-8.6 15.1L2 22l5-1.3A10 10 0 1 0 12 2Zm0 18a8 8 0 0 1-4.1-1.1l-.3-.2-3 .8.8-2.9-.2-.3A8 8 0 1 1 12 20Zm4.4-6c-.2-.1-1.4-.7-1.6-.8-.2-.1-.4-.1-.6.1-.2.3-.6.8-.8 1-.1.1-.3.2-.5.1a6.5 6.5 0 0 1-3.3-2.9c-.2-.4.2-.4.5-1.3.1-.1.1-.3 0-.4-.1-.1-.6-1.4-.8-1.9-.2-.5-.4-.4-.6-.4h-.5c-.2 0-.5.1-.7.3-.2.3-.9.9-.9 2.2s1 2.5 1.1 2.7c.1.1 2 3 4.8 4.3.7.3 1.2.5 1.6.6.7.2 1.3.2 1.8.1.5-.1 1.4-.6 1.6-1.1.2-.5.2-1 .1-1.1-.1-.1-.2-.2-.5-.3Z" />
                            </svg>
                        </span>
                        {{ whatsappLabel }}
                    </a>
                </div>

                <p class="hero-in hero-in--5 mt-6 flex items-start gap-2 text-sm text-neutral-text/65">
                    <svg class="mt-0.5 h-4 w-4 shrink-0 text-primary-dark" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M12 3l7.5 3v5.5c0 4.5-3.2 8-7.5 9.5-4.3-1.5-7.5-5-7.5-9.5V6L12 3z" />
                        <path d="m8.5 12 2.5 2.5 4.5-5" />
                    </svg>
                    {{ trustLine }}
                </p>
            </div>

            <!-- Visual -->
            <div class="hero-in hero-in--visual relative mx-auto aspect-[5/4] w-full max-w-md sm:aspect-[4/3] sm:max-w-xl lg:aspect-auto lg:h-[32rem] lg:max-w-none">
                <!-- Panel (clips the images, chips sit outside so they can overlap its edge) -->
                <div class="absolute inset-0 overflow-hidden rounded-[2rem] bg-secondary shadow-sm ring-1 ring-primary-dark/10">
                    <img
                        v-if="showBackground"
                        :src="backgroundImageSrc"
                        alt=""
                        :style="{ objectPosition: backgroundImagePosition }"
                        class="absolute inset-0 h-full w-full object-cover"
                        decoding="async"
                        @error="backgroundFailed = true"
                    />
                    <div v-if="showBackground" class="absolute inset-0 bg-primary-dark/10" aria-hidden="true" />

                    <!-- Fallback mark when there are no images at all -->
                    <div v-if="!showBackground && !showHero" class="absolute inset-0 flex items-center justify-center" role="img" :aria-label="heroImageAlt">
                        <div class="relative h-24 w-24 text-primary-dark/20">
                            <span class="absolute left-1/2 top-0 h-full w-5 -translate-x-1/2 rounded-full bg-current" />
                            <span class="absolute left-0 top-1/2 h-5 w-full -translate-y-1/2 rounded-full bg-current" />
                        </div>
                    </div>

                    <img
                        v-if="showHero"
                        :src="heroImageSrc"
                        :alt="heroImageAlt"
                        :style="{ objectPosition: heroImagePosition }"
                        class="absolute inset-0 h-full w-full object-contain px-4 pt-8 drop-shadow-xl sm:px-6"
                        fetchpriority="high"
                        decoding="async"
                        @error="heroFailed = true"
                    />
                </div>

                <!-- Floating highlights -->
                <div
                    v-for="(item, index) in highlights.slice(0, 2)"
                    :key="item.title"
                    class="hero-float absolute z-10 flex max-w-[14.5rem] items-center gap-3 rounded-2xl bg-white p-3 pr-4 shadow-lg ring-1 ring-neutral-text/5"
                    :class="chipPositions[index]"
                    :style="{ animationDelay: `${index * 1.4}s` }"
                >
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl" :class="chipTones[item.icon] ?? chipTones.shield">
                        <svg v-if="item.icon === 'whatsapp'" width="20" height="20" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                            <path d="M12 2a10 10 0 0 0-8.6 15.1L2 22l5-1.3A10 10 0 1 0 12 2Zm0 18a8 8 0 0 1-4.1-1.1l-.3-.2-3 .8.8-2.9-.2-.3A8 8 0 1 1 12 20Zm4.4-6c-.2-.1-1.4-.7-1.6-.8-.2-.1-.4-.1-.6.1-.2.3-.6.8-.8 1-.1.1-.3.2-.5.1a6.5 6.5 0 0 1-3.3-2.9c-.2-.4.2-.4.5-1.3.1-.1.1-.3 0-.4-.1-.1-.6-1.4-.8-1.9-.2-.5-.4-.4-.6-.4h-.5c-.2 0-.5.1-.7.3-.2.3-.9.9-.9 2.2s1 2.5 1.1 2.7c.1.1 2 3 4.8 4.3.7.3 1.2.5 1.6.6.7.2 1.3.2 1.8.1.5-.1 1.4-.6 1.6-1.1.2-.5.2-1 .1-1.1-.1-.1-.2-.2-.5-.3Z" />
                        </svg>
                        <svg v-else width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" v-html="chipIcons[item.icon] ?? chipIcons.shield" />
                    </span>
                    <span class="min-w-0">
                        <span class="block text-sm font-semibold leading-tight text-neutral-text">{{ item.title }}</span>
                        <span class="mt-0.5 block text-xs leading-tight text-neutral-text/55">{{ item.text }}</span>
                    </span>
                </div>
            </div>
        </div>
    </section>
</template>

<style scoped>
/* One orchestrated entrance on load: fade + small rise, staggered.
   Only opacity and transform change, so nothing shifts layout. */
.hero-in {
    opacity: 0;
    transform: translateY(14px);
    animation: hero-fade-up 0.6s ease-out forwards;
}
.hero-in--1 { animation-delay: 0.05s; }
.hero-in--2 { animation-delay: 0.15s; }
.hero-in--3 { animation-delay: 0.27s; }
.hero-in--4 { animation-delay: 0.38s; }
.hero-in--5 { animation-delay: 0.48s; }
.hero-in--visual {
    animation-delay: 0.2s;
    animation-duration: 0.8s;
}

@keyframes hero-fade-up {
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* The highlight chips drift gently once the page has settled. */
.hero-float {
    animation: hero-float 6s ease-in-out infinite;
}

@keyframes hero-float {
    0%,
    100% {
        transform: translateY(0);
    }
    50% {
        transform: translateY(-6px);
    }
}

@media (prefers-reduced-motion: reduce) {
    .hero-in {
        opacity: 1;
        transform: none;
        animation: none;
    }
    .hero-float {
        animation: none;
    }
}
</style>
