<script setup>
/**
 * Training teaser. Sits on the soft secondary tone and uses a two-column
 * split so the copy has room to breathe; optional highlights render as a
 * short fact list when the backend sends them, and fall back to a fixed
 * set so the right column never collapses into empty space.
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
    <section class="bg-secondary">
        <div class="mx-auto max-w-6xl px-4 py-12 sm:px-6 md:py-16">
            <div class="grid gap-8 md:grid-cols-12 md:items-start">
                <div class="md:col-span-7">
                    <span class="inline-flex items-center rounded-md bg-primary-dark/10 px-2.5 py-1 text-xs font-medium text-primary-dark">
                        Training
                    </span>

                    <h2 class="mt-3 font-heading text-xl font-semibold leading-tight tracking-tight text-neutral-text sm:text-2xl">
                        {{ training?.title ?? 'Learn from our pharmacists' }}
                    </h2>
                    <p class="mt-2 max-w-lg text-sm leading-relaxed text-neutral-text/70">
                        {{ training?.description ?? 'Short, practical sessions on medication safety, first aid and everyday care — taught by the same people behind the counter.' }}
                    </p>

                    <Link
                        :href="route('training.index')"
                        class="mt-6 inline-flex items-center gap-1.5 rounded-md bg-primary-dark px-4 py-2.5 text-sm font-medium text-white transition-colors duration-200 hover:bg-primary-dark/90"
                    >
                        See training programmes
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M5 12h14M13 6l6 6-6 6" />
                        </svg>
                    </Link>
                </div>

                <dl class="grid gap-px overflow-hidden rounded-md border border-neutral-text/10 bg-neutral-text/10 sm:grid-cols-2 md:col-span-5 md:grid-cols-1">
                    <div
                        v-for="highlight in highlights"
                        :key="highlight.label"
                        class="bg-secondary px-4 py-3.5"
                    >
                        <dt class="text-xs text-neutral-text/55">{{ highlight.label }}</dt>
                        <dd class="mt-0.5 text-sm font-medium text-neutral-text">{{ highlight.value }}</dd>
                    </div>
                </dl>
            </div>
        </div>
    </section>
</template>
