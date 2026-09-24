<script setup>
import { computed, onBeforeUnmount, ref } from 'vue'
import { compressImage } from '@/composables/useImageCompression'

/**
 * Works with an Inertia useForm() that has:  image: null, remove_image: false
 * Pass the record's current image_url so it can be previewed / removed.
 */
const props = defineProps({
    form: { type: Object, required: true },
    currentUrl: { type: String, default: null },
    label: { type: String, default: 'Image' },
    fileKey: { type: String, default: 'image' },
    removeKey: { type: String, default: 'remove_image' },
})

const preview = ref(null)
const fileInput = ref(null)
const compressing = ref(false)


const shown = computed(() => preview.value ?? (props.form.remove_image ? null : props.currentUrl))

const revoke = () => {
    if (preview.value) URL.revokeObjectURL(preview.value)
    preview.value = null
}

const onPick = async (event) => {
    const file = event.target.files[0]
    if (!file) return

    compressing.value = true
    const compressed = await compressImage(file, { maxSizeMB: 1 })
    compressing.value = false

    revoke()
    props.form.image = compressed
    props.form.remove_image = false
    preview.value = URL.createObjectURL(compressed)
}

const clear = () => {
    revoke()
    props.form.image = null
    if (fileInput.value) fileInput.value.value = ''
    // Only tell the server to delete something if there was something saved.
    if (props.currentUrl) props.form.remove_image = true
}

onBeforeUnmount(revoke)
</script>

<template>
    <div>
        <label class="mb-1.5 block text-sm font-medium text-neutral-text">{{ label }}</label>

        <div class="flex items-center gap-4 rounded-xl border border-neutral-text/10 bg-neutral-bg p-3">
            <div
                class="h-24 w-24 shrink-0 overflow-hidden rounded-xl bg-white"
                :class="shown ? 'border border-neutral-text/10' : 'border border-dashed border-neutral-text/25'"
            >
                <img v-if="shown" :src="shown" alt="" class="h-full w-full object-cover" />
                <div v-else class="flex h-full w-full flex-col items-center justify-center gap-1 text-neutral-text/40">
                    <svg class="h-6 w-6" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <rect x="3" y="4" width="14" height="12" rx="2" /><circle cx="7.5" cy="8.5" r="1.25" /><path d="m17 13-4-4-7 7" />
                    </svg>
                    <span class="text-xs">No image</span>
                </div>
            </div>

            <div class="min-w-0 text-sm">
                <div class="flex flex-wrap items-center gap-2">
                    <label
                        class="inline-flex cursor-pointer items-center rounded-lg border border-primary/40 bg-white px-3 py-1.5 text-sm font-medium text-primary-dark transition focus-within:ring-2 focus-within:ring-primary/40 hover:bg-primary-light active:scale-[0.98]"
                        :class="{ 'pointer-events-none opacity-50': compressing }"
                    >
                        {{ compressing ? 'Compressing...' : shown ? 'Change image' : 'Choose image' }}
                        <input
                            ref="fileInput"
                            type="file"
                            accept="image/jpeg,image/png,image/webp"
                            class="sr-only"
                            :disabled="compressing"
                            @change="onPick"
                        />
                    </label>
                    <button
                        v-if="shown"
                        type="button"
                        class="rounded-lg px-3 py-1.5 text-sm font-medium text-neutral-text/55 transition hover:bg-accent-light/10 hover:text-accent focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-accent-light/40"
                        @click="clear"
                    >
                        Remove
                    </button>
                </div>
                <p class="mt-2 text-xs text-neutral-text/50">JPG, PNG or WebP, up to 5 MB.</p>
            </div>
        </div>

        <p v-if="form.errors.image" class="mt-1.5 text-xs font-medium text-accent">{{ form.errors.image }}</p>
    </div>
</template>
