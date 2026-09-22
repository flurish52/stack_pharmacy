<script setup>
// resources/js/Components/Admin/FlashMessages.vue
import { computed, onBeforeUnmount, reactive, watch } from 'vue'

const props = defineProps({
    flash: { type: Object, default: () => ({}) },
})

const dismissed = reactive({ success: false, error: false })
let timer = null

// A new flash message always shows again. Success messages fade out on their own.
watch(
    () => [props.flash?.success, props.flash?.error],
    () => {
        dismissed.success = false
        dismissed.error = false
        clearTimeout(timer)

        if (props.flash?.success && typeof window !== 'undefined') {
            timer = setTimeout(() => (dismissed.success = true), 6000)
        }
    },
    { immediate: true },
)

onBeforeUnmount(() => clearTimeout(timer))

const messages = computed(() =>
    ['success', 'error']
        .filter((type) => props.flash?.[type] && !dismissed[type])
        .map((type) => ({ type, text: props.flash[type] })),
)

const styles = {
    success: 'border-primary/20 bg-primary-light text-primary-dark',
    error: 'border-accent-light/30 bg-accent-light/10 text-neutral-text',
}
const iconStyles = {
    success: 'bg-white text-primary-dark',
    error: 'bg-white text-accent',
}
const iconPaths = {
    success: 'm6 10.5 2.75 2.75L14 7.5',
    error: 'M10 6v4.5M10 13.5h.01',
}
</script>

<template>
    <TransitionGroup
        tag="div"
        name="flash"
        class="px-4 sm:px-6"
        :class="messages.length ? 'space-y-2 pt-4' : ''"
        aria-live="polite"
    >
        <div
            v-for="message in messages"
            :key="message.type"
            class="flex items-start gap-3 rounded-xl border px-4 py-3 text-sm"
            :class="styles[message.type]"
            :role="message.type === 'error' ? 'alert' : 'status'"
        >
            <span class="mt-px flex h-5 w-5 shrink-0 items-center justify-center rounded-full" :class="iconStyles[message.type]">
                <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path :d="iconPaths[message.type]" />
                </svg>
            </span>

            <p class="min-w-0 flex-1 leading-5">{{ message.text }}</p>

            <button
                type="button"
                class="-mr-1 -mt-0.5 rounded-md p-1 opacity-60 transition hover:bg-white/60 hover:opacity-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/40"
                aria-label="Dismiss message"
                @click="dismissed[message.type] = true"
            >
                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
                    <path d="M5.5 5.5l9 9M14.5 5.5l-9 9" />
                </svg>
            </button>
        </div>
    </TransitionGroup>
</template>

<style scoped>
.flash-enter-active,
.flash-leave-active {
    transition:
        opacity 0.2s ease,
        transform 0.2s ease;
}

.flash-enter-from,
.flash-leave-to {
    opacity: 0;
    transform: translateY(-4px);
}

@media (prefers-reduced-motion: reduce) {
    .flash-enter-active,
    .flash-leave-active {
        transition: none;
    }
}
</style>
