<script setup>
import { computed, onMounted, ref, watch } from 'vue'
import { Link } from '@inertiajs/vue3'

const props = defineProps({
    title: { type: String, required: true },
    value: { type: [String, Number], required: true },
    hint: { type: String, default: '' },
    href: { type: String, default: null },
    // Flat solid fills only, never a gradient. Pick the token that matches meaning:
    // 'brand' teal (on track), 'accent' orange (needs attention), 'danger' coral (urgent/negative),
    // 'mint' secondary (in progress), 'dark' primary-dark (headline metric like revenue).
    tone: { type: String, default: 'mint' },
    pulse: { type: Boolean, default: false },
    // Optional formatter for numeric values (e.g. a currency formatter) so they can still count up.
    format: { type: Function, default: null },
})

/* ------------------------------------------------------------------ */
/* Look                                                                */
/* ------------------------------------------------------------------ */

const chipTones = {
    brand: 'bg-primary/10 text-primary-dark',
    accent: 'bg-accent/10 text-accent',
    danger: 'bg-accent-light/15 text-accent',
    mint: 'bg-secondary text-primary-dark',
    dark: 'bg-white/15 text-white',
}

const isHero = computed(() => props.tone === 'dark')
const chip = computed(() => chipTones[props.tone] ?? chipTones.mint)
const tag = computed(() => (props.href ? Link : 'div'))

/* ------------------------------------------------------------------ */
/* Count-up                                                            */
/* ------------------------------------------------------------------ */

// Plain numbers count up. A pre-formatted string (currency, etc.) is shown as-is so its
// formatting is never mangled. To count up a formatted number, pass a number plus `format`.
const isNumeric = computed(() => typeof props.value === 'number')
const counter = ref(0)
let token = 0

const prefersReducedMotion = () =>
    typeof window !== 'undefined' && !!window.matchMedia?.('(prefers-reduced-motion: reduce)').matches

const animateTo = (target) => {
    const current = ++token

    if (prefersReducedMotion()) {
        counter.value = target
        return
    }

    const from = counter.value
    const start = performance.now()
    const duration = 600

    const step = (now) => {
        if (current !== token) return // a newer animation took over
        const progress = Math.min((now - start) / duration, 1)
        counter.value = from + (target - from) * (1 - Math.pow(1 - progress, 3))
        if (progress < 1) requestAnimationFrame(step)
    }
    requestAnimationFrame(step)
}

onMounted(() => {
    if (isNumeric.value) animateTo(props.value)
})

watch(
    () => props.value,
    (next) => {
        if (typeof next === 'number') animateTo(next)
    },
)

const shown = computed(() => {
    if (!isNumeric.value) return props.value
    return props.format ? props.format(counter.value) : Math.round(counter.value)
})
</script>

<template>
    <component
        :is="tag"
        :href="href ?? undefined"
        class="group relative flex min-h-[8.5rem] flex-col overflow-hidden rounded-xl border p-5 transition duration-200 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/40 focus-visible:ring-offset-2 focus-visible:ring-offset-neutral-bg"
        :class="[
            isHero ? 'border-primary-dark bg-primary-dark text-white' : 'border-neutral-text/10 bg-white',
            href ? 'hover:-translate-y-0.5 hover:shadow-sm ' + (isHero ? 'hover:bg-primary-dark/95' : 'hover:border-primary/40') : '',
        ]"
    >
        <div class="flex items-start justify-between gap-3">
            <div class="flex items-center gap-2 pt-1.5">
                <p class="text-sm font-medium leading-none" :class="isHero ? 'text-white/75' : 'text-neutral-text/65'">
                    {{ title }}
                </p>
                <span v-if="pulse" class="relative flex h-2 w-2 shrink-0" aria-hidden="true">
                    <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-accent-light opacity-75 motion-reduce:animate-none" />
                    <span class="relative inline-flex h-2 w-2 rounded-full bg-accent-light" />
                </span>
            </div>

            <span
                class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl transition-transform duration-200 ease-out motion-reduce:transition-none"
                :class="[chip, href ? 'group-hover:scale-105' : '']"
            >
                <slot name="icon" />
            </span>
        </div>

        <p
            class="mt-4 font-heading text-[1.75rem] font-semibold leading-none tracking-tight tabular-nums"
            :class="isHero ? 'text-white' : 'text-neutral-text'"
        >
            {{ shown }}
        </p>

        <div class="mt-auto flex items-end justify-between gap-3 pt-3">
            <p v-if="hint" class="text-xs leading-snug" :class="isHero ? 'text-white/70' : 'text-neutral-text/55'">
                {{ hint }}
            </p>
            <span v-else />

            <!-- Shows that the whole card is a link -->
            <span
                v-if="href"
                class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full transition duration-200 group-hover:translate-x-0.5 motion-reduce:transition-none"
                :class="
                    isHero
                        ? 'bg-white/15 text-white group-hover:bg-white/25'
                        : 'bg-neutral-bg text-neutral-text/50 group-hover:bg-secondary group-hover:text-primary-dark'
                "
                aria-hidden="true"
            >
                <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m8 5 5 5-5 5" />
                </svg>
            </span>
        </div>
    </component>
</template>
