<script setup>
import { onBeforeUnmount, onMounted } from 'vue'

const props = defineProps({
    show: { type: Boolean, default: false },
    title: { type: String, default: '' },
})

const emit = defineEmits(['close'])

const onKey = (event) => {
    if (event.key === 'Escape' && props.show) emit('close')
}

onMounted(() => window.addEventListener('keydown', onKey))
onBeforeUnmount(() => window.removeEventListener('keydown', onKey))
</script>

<template>
    <div
        v-if="show"
        class="fixed inset-0 z-50 flex items-end justify-center bg-black/40 p-4 sm:items-center"
        @click.self="emit('close')"
    >
        <div
            class="max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-lg bg-white shadow-xl"
            role="dialog"
            aria-modal="true"
        >
            <div class="flex items-center justify-between border-b border-gray-200 px-5 py-4">
                <h2 class="font-semibold">{{ title }}</h2>
                <button type="button" class="text-gray-400 hover:text-gray-700" aria-label="Close" @click="emit('close')">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" d="M6 6l12 12M18 6L6 18" />
                    </svg>
                </button>
            </div>
            <div class="p-5">
                <slot />
            </div>
        </div>
    </div>
</template>
