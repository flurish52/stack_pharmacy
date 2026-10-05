<script setup>
import { computed, ref } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import { compressImage } from '@/composables/useImageCompression'

const props = defineProps({
    product: { type: Object, required: true }, // needs id, images, variants
})

const page = usePage()
const uploading = ref(false)
const variantId = ref('')

// Primary first, then oldest first.
const images = computed(() =>
    [...(props.product.images ?? [])].sort(
        (a, b) => Number(b.is_primary) - Number(a.is_primary) || a.id - b.id,
    ),
)

const variantName = (image) =>
    props.product.variants?.find((v) => v.id === image.product_variant_id)?.variant_name

// Upload one file at a time; the endpoint takes a single image.
const upload = async (event) => {
    const files = [...event.target.files]
    event.target.value = ''
    if (!files.length) return

    uploading.value = true
    for (const file of files) {
        const compressed = await compressImage(file, { maxSizeMB: 1 })
        await new Promise((resolve) =>
            router.post(
                `/admin/products/${props.product.id}/images`,
                { image: compressed, product_variant_id: variantId.value || null },
                { forceFormData: true, preserveScroll: true, onFinish: resolve },
            ),
        )
    }
    uploading.value = false
}

const makePrimary = (image) =>
    router.patch(`/admin/product-images/${image.id}/primary`, {}, { preserveScroll: true })

const remove = (image) => {
    if (confirm('Remove this image? It will also be deleted from Cloudinary.')) {
        router.delete(`/admin/product-images/${image.id}`, { preserveScroll: true })
    }
}
</script>

<template>
    <section class="rounded-lg border border-gray-200 bg-white p-5">
        <h2 class="mb-1 font-semibold">Images</h2>
        <p class="mb-4 text-sm text-gray-500">JPG, PNG or WebP, up to 5 MB each. The first image becomes the primary one.</p>

        <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center">
            <select
                v-model="variantId"
                class="rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
            >
                <option value="">Applies to the whole product</option>
                <option v-for="variant in product.variants" :key="variant.id" :value="variant.id">
                    {{ variant.variant_name }} only
                </option>
            </select>

            <label
                class="inline-flex cursor-pointer items-center justify-center rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                :class="{ 'pointer-events-none opacity-50': uploading }"
            >
                {{ uploading ? 'Uploading...' : 'Upload images' }}
                <input
                    type="file"
                    accept="image/jpeg,image/png,image/webp"
                    multiple
                    class="sr-only"
                    :disabled="uploading"
                    @change="upload"
                />
            </label>
        </div>

        <p v-if="page.props.errors?.image" class="mb-3 text-sm text-red-600">{{ page.props.errors.image }}</p>
        <p v-if="page.props.errors?.product_variant_id" class="mb-3 text-sm text-red-600">
            {{ page.props.errors.product_variant_id }}
        </p>

        <p v-if="images.length === 0" class="rounded-md border border-dashed border-gray-300 px-4 py-8 text-center text-sm text-gray-500">
            No images yet. Upload at least one so the product looks right in the shop.
        </p>

        <div v-else class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
            <div v-for="image in images" :key="image.id" class="overflow-hidden rounded-md border border-gray-200">
                <div class="relative aspect-square bg-gray-100">
                    <img :src="image.url" alt="" class="h-full w-full object-cover" loading="lazy" />
                    <span
                        v-if="image.is_primary"
                        class="absolute left-2 top-2 rounded-full bg-emerald-600 px-2 py-0.5 text-xs font-medium text-white"
                    >
                        Primary
                    </span>
                </div>
                <div class="space-y-1 p-2 text-xs">
                    <p v-if="variantName(image)" class="truncate text-gray-500">{{ variantName(image) }}</p>
                    <div class="flex items-center justify-between">
                        <button
                            v-if="!image.is_primary"
                            type="button"
                            class="text-emerald-700 hover:underline"
                            @click="makePrimary(image)"
                        >
                            Make primary
                        </button>
                        <span v-else />
                        <button type="button" class="text-red-600 hover:underline" @click="remove(image)">Remove</button>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
