<script setup>
/**
 * Training teaser. Soft secondary band, two-column split.
 * Left: what it is + one obvious action. Right: a divided fact panel.
 * Highlights come from the backend when present, otherwise fall back to a
 * fixed set so the right column never collapses.
 */
import { Link } from '@inertiajs/vue3'

const props = defineProps({
    training: { type: Object, default: null },
})

const fallbackHighlights = [
    { label: 'Format', value: 'In-person & online' },
    { label: 'Duration', value: '2–3 hours per session' },
    { label: 'Led by', value: 'Licensed pharmacists' },
    { label: 'Next intake', value: 'Rolling enrolment' },
]

const highlights = props.training?.highlights?.length ? props.training.highlights : fallbackHighlights
</script>

<template>
    <section class="border-y border-primary-dark/10 bg-secondary" aria-labelledby="training-heading">
        <div class="mx-auto max-w-6xl px-4 py-12 sm:px-6 md:py-16">
            <div class="grid gap-8 md:grid-cols-12 md:items-center md:gap-10">
                <!-- Copy + action -->
                <div class="md:col-span-7">
                    <span class="inline-flex items-center gap-2 rounded-md bg-primary-dark/10 px-2.5 py-1 text-xs font-medium text-primary-dark">
                        <span class="h-1.5 w-1.5 rounded-full bg-primary" aria-hidden="true"></span>
                        Training
                    </span>

                    <h2
                        id="training-heading"
                        class="mt-4 font-heading text-2xl font-semibold leading-tight tracking-tight text-neutral-text sm:text-3xl"
                    >
                        {{ training?.title ?? 'Learn from our pharmacists' }}
                    </h2>
                    <p class="mt-3 max-w-lg text-sm leading-relaxed text-neutral-text/70 sm:text-base">
                        {{ training?.description ?? 'Short, practical sessions on medication safety, first aid and everyday care, taught by the same people behind the counter.' }}
                    </p>

                    <div class="mt-7 flex flex-wrap items-center gap-x-5 gap-y-3">
                        <Link
                            :href="route('training.index')"
                            class="group inline-flex items-center gap-2 rounded-md bg-primary-dark px-5 py-3 text-sm font-medium text-neutral-bg shadow-sm transition duration-200 hover:bg-primary-dark/90 hover:shadow focus:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2 focus-visible:ring-offset-secondary active:scale-[0.98] motion-reduce:transition-none motion-reduce:active:scale-100"
                        >
                            Browse training programmes
                            <svg
                                class="transition-transform duration-200 group-hover:translate-x-1 motion-reduce:transition-none motion-reduce:group-hover:translate-x-0"
                                width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"
                            >
                                <path d="M5 12h14M13 6l6 6-6 6" />
                            </svg>
                        </Link>

                        <span class="text-sm text-neutral-text/60">
                            Book a session in a few minutes
                        </span>
                    </div>
                </div>

                <!-- Fact panel: rows separated by lines, accent rail on the left -->
                <dl
                    class="relative divide-y divide-primary-dark/10 overflow-hidden rounded-md border border-primary-dark/15 bg-neutral-bg shadow-sm md:col-span-5"
                >
                    <span class="absolute inset-y-0 left-0 w-1 bg-primary" aria-hidden="true"></span>

                    <div
                        v-for="highlight in highlights"
                        :key="highlight.label"
                        class="flex items-baseline justify-between gap-4 py-3.5 pl-5 pr-4 transition-colors duration-200 hover:bg-primary-light motion-reduce:transition-none"
                    >
                        <dt class="text-xs text-neutral-text/60 sm:text-sm">{{ highlight.label }}</dt>
                        <dd class="text-right text-sm font-medium text-neutral-text">{{ highlight.value }}</dd>
                    </div>
                </dl>
            </div>
        </div>
    </section>
</template>
