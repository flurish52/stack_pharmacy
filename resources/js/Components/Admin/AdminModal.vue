<script setup>
import { onBeforeUnmount, onMounted, watch } from 'vue'

const props = defineProps({
    show: { type: Boolean, default: false },
    title: { type: String, default: '' },
})

const emit = defineEmits(['close'])

const onKey = (event) => {
    if (event.key === 'Escape' && props.show) emit('close')
}

// Stop the page behind the modal from scrolling while it is open.
watch(
    () => props.show,
    (open) => {
        document.body.style.overflow = open ? 'hidden' : ''
    },
)

onMounted(() => window.addEventListener('keydown', onKey))
onBeforeUnmount(() => {
    window.removeEventListener('keydown', onKey)
    document.body.style.overflow = ''
})
</script>

<template>
    <Transition name="modal">
        <div
            v-if="show"
            class="fixed inset-0 z-50 flex items-end justify-center bg-neutral-text/50 p-4 backdrop-blur-[2px] sm:items-center"
            @click.self="emit('close')"
        >
            <div
                class="modal-panel max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-2xl border border-neutral-text/10 bg-white shadow-[0_20px_50px_-12px_rgba(30,41,59,0.35)]"
                role="dialog"
                aria-modal="true"
                :aria-label="title"
            >
                <div class="sticky top-0 z-10 flex items-center justify-between border-b border-neutral-text/10 bg-neutral-bg px-5 py-4">
                    <h2 class="font-heading text-base font-semibold text-neutral-text">{{ title }}</h2>
                    <button
                        type="button"
                        class="rounded-lg p-1.5 text-neutral-text/50 transition hover:bg-neutral-text/[0.07] hover:text-neutral-text focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/40"
                        aria-label="Close"
                        @click="emit('close')"
                    >
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" d="M6 6l12 12M18 6L6 18" />
                        </svg>
                    </button>
                </div>
                <div class="p-5">
                    <slot />
                </div>
            </div>
        </div>
    </Transition>
</template>

<style scoped>
.modal-enter-active,
.modal-leave-active {
    transition: opacity 0.18s ease;
}
.modal-enter-active .modal-panel,
.modal-leave-active .modal-panel {
    transition: transform 0.18s ease, opacity 0.18s ease;
}
.modal-enter-from,
.modal-leave-to {
    opacity: 0;
}
.modal-enter-from .modal-panel,
.modal-leave-to .modal-panel {
    opacity: 0;
    transform: translateY(8px) scale(0.98);
}

@media (prefers-reduced-motion: reduce) {
    .modal-enter-active,
    .modal-leave-active,
    .modal-enter-active .modal-panel,
    .modal-leave-active .modal-panel {
        transition: none;
    }
}
</style>
