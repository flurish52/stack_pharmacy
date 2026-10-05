<script setup>
/**
 * "Why Obudu Trusts Us": three boxes, client's exact copy.
 * The emoji are part of the client's text, so they stay as the box icons.
 */
import { ref, onMounted, onUnmounted } from 'vue'

defineProps({
    heading: { type: String, default: 'Why Obudu Trusts Us' },
    items: {
        type: Array,
        default: () => [
            {
                icon: '💊',
                title: '100% Genuine Drugs',
                text: 'We source only from licensed and reputable pharmaceutical companies. Zero fake drugs.',
            },
            {
                icon: '💰',
                title: 'Affordable For Everybody',
                text: 'Quality medicines at prices that Obudu families can afford.',
            },
            {
                icon: '🤝',
                title: 'Professional Care',
                text: 'Friendly, expert advice and drug counseling from caring professionals.',
            },
        ],
    },
})

const root = ref(null)
const visible = ref(false)
let observer = null

onMounted(() => {
    const reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches
    if (reduced || !('IntersectionObserver' in window)) {
        visible.value = true
        return
    }

    observer = new IntersectionObserver(
        ([entry]) => {
            if (entry.isIntersecting) {
                visible.value = true
                observer.disconnect()
            }
        },
        { threshold: 0.2 }
    )
    if (root.value) observer.observe(root.value)
})

onUnmounted(() => observer?.disconnect())
</script>

<template>
    <section ref="root" class="relative">
        <div class="mx-auto max-w-6xl px-4 sm:px-6">
            <h2 class="mb-6 text-center font-heading text-xl font-semibold tracking-tight text-neutral-text sm:text-2xl">
                {{ heading }}
            </h2>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <div
                    v-for="(item, index) in items"
                    :key="item.title"
                    class="trust-item flex items-start gap-3.5 rounded-xl border border-neutral-text/10 bg-white p-5 shadow-[0_8px_24px_-10px_rgba(15,23,22,0.14)] transition-shadow duration-200 hover:shadow-[0_12px_30px_-8px_rgba(15,23,22,0.2)]"
                    :class="{ 'trust-item--visible': visible }"
                    :style="{ transitionDelay: `${index * 90}ms` }"
                >
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-primary-light text-xl" aria-hidden="true">
                        {{ item.icon }}
                    </span>
                    <div class="min-w-0">
                        <h3 class="text-sm font-semibold leading-tight text-neutral-text">{{ item.title }}</h3>
                        <p class="mt-1 text-sm leading-snug text-neutral-text/65">{{ item.text }}</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

<style scoped>
.trust-item {
    opacity: 0;
    transform: translateY(10px);
    transition: opacity 0.5s ease-out, transform 0.5s ease-out, box-shadow 0.2s;
}
.trust-item--visible {
    opacity: 1;
    transform: translateY(0);
}
@media (prefers-reduced-motion: reduce) {
    .trust-item { opacity: 1; transform: none; transition: none; }
}
</style>
