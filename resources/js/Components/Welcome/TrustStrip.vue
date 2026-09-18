<script setup>
/**
 * Trust strip, v3 — four reassurances, each as its own card. Same grid
 * gap/edge padding as the rest of the page so it doesn't spread wider
 * or narrower than the sections above/below it.
 */
import { ref, onMounted, onUnmounted } from 'vue'

const iconPaths = {
    box: 'M3 7l9-4 9 4-9 4-9-4zM3 7v10l9 4 9-4V7M12 11v10',
    refresh: 'M20 12a8 8 0 1 1-2.34-5.66M20 4v5h-5',
    shieldCheck: 'M12 2l8 4v6c0 5-3.5 8.5-8 10-4.5-1.5-8-5-8-10V6l8-4zM9 12l2 2 4-4',
    chat: 'M21 12a8 8 0 0 1-8 8H8l-5 3 1.5-4.5A8 8 0 1 1 21 12z',
}

defineProps({
    items: {
        type: Array,
        default: () => [
            { icon: 'box', title: 'Same-day delivery', subtitle: 'On orders placed before 3pm' },
            { icon: 'refresh', title: 'Easy returns', subtitle: '7-day return on unopened items' },
            { icon: 'shieldCheck', title: 'Genuine products', subtitle: 'Pharmacist-verified, always' },
            { icon: 'chat', title: 'Here when you need us', subtitle: 'Talk to a pharmacist on WhatsApp' },
        ],
    },
})

const root = ref(null)
const visible = ref(false)

onMounted(() => {
    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches
    if (prefersReducedMotion || !('IntersectionObserver' in window)) {
        visible.value = true
        return
    }

    const observer = new IntersectionObserver(
        ([entry]) => {
            if (entry.isIntersecting) {
                visible.value = true
                observer.disconnect()
            }
        },
        { threshold: 0.2 }
    )
    if (root.value) observer.observe(root.value)

    onUnmounted(() => observer.disconnect())
})
</script>

<template>
    <section ref="root" class="relative">
        <div class="mx-auto max-w-6xl px-4 sm:px-6">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-4">
                <div
                    v-for="(item, index) in items"
                    :key="item.title"
                    class="trust-item flex items-start gap-3.5 rounded-xl border border-neutral-text/10 bg-white p-5 shadow-[0_8px_24px_-10px_rgba(15,23,22,0.14)] transition-shadow duration-200 hover:shadow-[0_12px_30px_-8px_rgba(15,23,22,0.2)]"
                    :class="{ 'trust-item--visible': visible }"
                    :style="{ transitionDelay: `${index * 90}ms` }"
                >
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-md bg-primary-light text-primary-dark">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                            <path :d="iconPaths[item.icon]" />
                        </svg>
                    </span>
                    <div class="min-w-0">
                        <p class="text-sm font-medium leading-tight text-neutral-text">{{ item.title }}</p>
                        <p class="mt-0.5 text-xs leading-snug text-neutral-text/60">{{ item.subtitle }}</p>
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
    transition: opacity 0.5s ease-out, transform 0.5s ease-out;
}
.trust-item--visible {
    opacity: 1;
    transform: translateY(0);
}
</style>
