<script setup>
import { computed, onBeforeUnmount, ref } from 'vue'

/**
 * Works with an Inertia useForm() that has:  image: null, remove_image: false
 * Pass the record's current image_url so it can be previewed / removed.
 */
const props = defineProps({
    form: { type: Object, required: true },
    currentUrl: { type: String, default: null },
    label: { type: String, default: 'Image' },
})

const preview = ref(null)
const fileInput = ref(null)

const shown = computed(() => preview.value ?? (props.form.remove_image ? null : props.currentUrl))

const revoke = () => {
    if (preview.value) URL.revokeObjectURL(preview.value)
    preview.value = null
}

const onPick = (event) => {
    const file = event.target.files[0]
    if (!file) return

    revoke()
    props.form.image = file
    props.form.remove_image = false
    preview.value = URL.createObjectURL(file)
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
        <label class="mb-1 block text-sm font-medium">{{ label }}</label>

        <div class="flex items-center gap-4">
            <div class="h-24 w-24 shrink-0 overflow-hidden rounded-md border border-gray-200 bg-gray-100">
                <img v-if="shown" :src="shown" alt="" class="h-full w-full object-cover" />
                <div v-else class="flex h-full w-full items-center justify-center text-xs text-gray-400">
                    No image
                </div>
            </div>

            <div class="space-y-2 text-sm">
                <label
                    class="inline-flex cursor-pointer items-center rounded-md border border-gray-300 px-3 py-1.5 font-medium text-gray-700 hover:bg-gray-50"
                >
                    {{ shown ? 'Change image' : 'Choose image' }}
                    <input
                        ref="fileInput"
                        type="file"
                        accept="image/jpeg,image/png,image/webp"
                        class="sr-only"
                        @change="onPick"
                    />
                </label>
                <button v-if="shown" type="button" class="ml-3 text-red-600 hover:underline" @click="clear">
                    Remove
                </button>
                <p class="text-xs text-gray-400">JPG, PNG or WebP, up to 5 MB.</p>
            </div>
        </div>

        <p v-if="form.errors.image" class="mt-1 text-xs text-red-600">{{ form.errors.image }}</p>
    </div>
</template>
