<script setup>
import { computed, ref } from 'vue'
import { Link } from '@inertiajs/vue3'
import ProductCard from '@/Components/Welcome/ProductCard.vue'
import { useCart } from '@/composables/useCart' // adjust the path if yours differs

const props = defineProps({
    product: { type: Object, required: true },
    relatedProducts: { type: Array, default: () => [] },
})

const { add, isPending } = useCart()

// --- Variant selection ---------------------------------------------------

const firstAvailable = props.product.variants.find((v) => v.stock_quantity > 0)
const selectedVariantId = ref(firstAvailable?.id ?? props.product.variants[0]?.id ?? null)

const selectedVariant = computed(() =>
    props.product.variants.find((v) => v.id === selectedVariantId.value) ?? null
)

const selectedVariantOutOfStock = computed(
    () => !selectedVariant.value || selectedVariant.value.stock_quantity <= 0
)

// Low-stock nudge — common e-commerce pattern that adds urgency without a fake gimmick
const lowStock = computed(() =>
    selectedVariant.value && selectedVariant.value.stock_quantity > 0 && selectedVariant.value.stock_quantity <= 5
)

// --- Gallery ---------------------------------------------------------------

// Images tied to the selected variant come first, then shared/product-level images
const galleryImages = computed(() => {
    const all = props.product.images ?? []
    const variantImages = all.filter((img) => img.product_variant_id === selectedVariantId.value)
    const sharedImages = all.filter((img) => img.product_variant_id === null)

    const ordered = [...variantImages, ...sharedImages]
    return ordered.length ? ordered : all
})

const activeImage = ref(null)

const currentImage = computed(() => {
    if (activeImage.value) return activeImage.value
    return galleryImages.value.find((img) => img.is_primary) ?? galleryImages.value[0] ?? null
})

function selectImage(image) {
    activeImage.value = image
}

// Reset the manually-picked image when the variant (and thus gallery) changes
function selectVariant(id) {
    selectedVariantId.value = id
    activeImage.value = null
    quantity.value = 1
}

// --- Quantity ----------------------------------------------------------

const quantity = ref(1)
const maxQuantity = computed(() => selectedVariant.value?.stock_quantity ?? 1)

function decrementQuantity() {
    if (quantity.value > 1) quantity.value--
}
function incrementQuantity() {
    if (quantity.value < maxQuantity.value) quantity.value++
}

// --- Price formatting ----------------------------------------------------

const currency = new Intl.NumberFormat('en-NG', {
    style: 'currency',
    currency: 'NGN',
    maximumFractionDigits: 0,
})

function formatPrice(value) {
    return currency.format(value ?? 0)
}

// --- Cart ------------------------------------------------------------------

function addToCart() {
    if (selectedVariantOutOfStock.value || !selectedVariant.value) return
    add(selectedVariant.value.id, quantity.value)
}

// --- Related products -------------------------------------------------

// Normalizes the key mismatch: relatedProducts sends `primary_image_url`,
// ProductCard expects `image_url` (per the Shop/Index transform).
const normalizedRelated = computed(() =>
    props.relatedProducts.map((p) => ({
        id: p.id,
        name: p.name,
        slug: p.slug,
        starting_price: p.starting_price,
        image_url: p.primary_image_url,
        in_stock: true, // controller already filters to variants with stock
        categories: [],
    }))
)
</script>

<template>
    <div class="mx-auto max-w-6xl px-4 py-8 sm:px-6 md:py-10">
        <nav class="flex items-center gap-1.5 text-xs text-neutral-text/45" aria-label="Breadcrumb">
            <Link :href="route('shop.index')" class="hover:text-primary-dark">Shop</Link>
            <template v-if="product.categories?.length">
                <span>›</span>
                <Link
                    :href="route('shop.index', { category: product.categories[0].slug })"
                    class="hover:text-primary-dark"
                >
                    {{ product.categories[0].name }}
                </Link>
            </template>
            <span>›</span>
            <span class="truncate text-neutral-text/70">{{ product.name }}</span>
        </nav>

        <div class="mt-6 grid grid-cols-1 gap-10 lg:grid-cols-2 lg:gap-12">
            <!-- Gallery -->
            <div>
                <div class="relative aspect-square w-full overflow-hidden rounded-xl border border-neutral-text/10 bg-neutral-bg">
                    <img
                        v-if="currentImage"
                        :src="currentImage.url"
                        :alt="product.name"
                        class="h-full w-full object-cover"
                    />
                    <div v-else class="flex h-full w-full items-center justify-center text-xs text-neutral-text/40">
                        No image available
                    </div>

                    <span
                        v-if="product.is_out_of_stock"
                        class="absolute left-3 top-3 rounded-md bg-neutral-text/80 px-2 py-1 text-xs font-medium text-white backdrop-blur-sm"
                    >
                        Out of stock
                    </span>
                </div>

                <div v-if="galleryImages.length > 1" class="mt-3 flex gap-2">
                    <button
                        v-for="image in galleryImages"
                        :key="image.id"
                        type="button"
                        class="h-16 w-16 shrink-0 overflow-hidden rounded-md border-2 transition"
                        :class="currentImage?.id === image.id
                            ? 'border-primary-dark'
                            : 'border-transparent ring-1 ring-neutral-text/10 hover:ring-neutral-text/25'"
                        @click="selectImage(image)"
                    >
                        <img :src="image.url" :alt="product.name" class="h-full w-full object-cover" />
                    </button>
                </div>
            </div>

            <!-- Details -->
            <div>
                <div v-if="product.categories?.length" class="flex flex-wrap gap-1.5">
                    <span
                        v-for="category in product.categories"
                        :key="category.slug"
                        class="rounded-md bg-primary-light px-2 py-0.5 text-xs font-medium text-primary-dark"
                    >
                        {{ category.name }}
                    </span>
                </div>

                <h1 class="mt-3 font-heading text-2xl font-semibold tracking-tight text-neutral-text">
                    {{ product.name }}
                </h1>

                <div class="mt-2 flex items-center gap-2.5">
                    <p class="font-heading text-xl font-semibold text-primary-dark">
                        {{ formatPrice(selectedVariant?.price) }}
                    </p>
                    <span
                        v-if="lowStock"
                        class="rounded-md bg-accent/10 px-2 py-0.5 text-xs font-medium text-accent"
                    >
                        Only {{ selectedVariant.stock_quantity }} left
                    </span>
                </div>

                <!-- Variant picker -->
                <div v-if="product.variants.length > 1" class="mt-6">
                    <p class="text-xs font-medium text-neutral-text/60">Options</p>
                    <div class="mt-2 flex flex-wrap gap-2">
                        <button
                            v-for="variant in product.variants"
                            :key="variant.id"
                            type="button"
                            :disabled="variant.stock_quantity <= 0"
                            class="rounded-md border px-3 py-1.5 text-sm font-medium transition"
                            :class="[
                                selectedVariantId === variant.id
                                    ? 'border-primary-dark bg-primary-dark text-white'
                                    : 'border-neutral-text/15 text-neutral-text hover:border-primary-dark/40',
                                variant.stock_quantity <= 0 && 'cursor-not-allowed opacity-40 hover:border-neutral-text/15',
                            ]"
                            @click="selectVariant(variant.id)"
                        >
                            {{ variant.variant_name }}
                        </button>
                    </div>
                </div>

                <!-- Quantity -->
                <div class="mt-6 flex items-center gap-3">
                    <p class="text-xs font-medium text-neutral-text/60">Quantity</p>
                    <div class="flex items-center rounded-md border border-neutral-text/15">
                        <button
                            type="button"
                            class="flex h-9 w-9 items-center justify-center text-sm text-neutral-text/60 transition-colors hover:text-primary-dark disabled:opacity-30 disabled:hover:text-neutral-text/60"
                            :disabled="quantity <= 1"
                            @click="decrementQuantity"
                        >
                            −
                        </button>
                        <span class="w-9 text-center text-sm font-medium text-neutral-text">{{ quantity }}</span>
                        <button
                            type="button"
                            class="flex h-9 w-9 items-center justify-center text-sm text-neutral-text/60 transition-colors hover:text-primary-dark disabled:opacity-30 disabled:hover:text-neutral-text/60"
                            :disabled="quantity >= maxQuantity"
                            @click="incrementQuantity"
                        >
                            +
                        </button>
                    </div>
                </div>

                <!-- Add to cart -->
                <button
                    type="button"
                    class="mt-6 flex w-full items-center justify-center gap-2 rounded-md bg-primary-dark py-3 text-sm font-medium text-white transition-colors duration-200 hover:bg-primary-dark/90 disabled:cursor-not-allowed disabled:bg-neutral-text/20 disabled:text-neutral-text/50"
                    :disabled="selectedVariantOutOfStock || isPending(selectedVariantId)"
                    @click="addToCart"
                >
                    <svg
                        v-if="!isPending(selectedVariantId) && !selectedVariantOutOfStock"
                        width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"
                    >
                        <path d="M3 3h2l2.4 12.4a2 2 0 0 0 2 1.6h7.2a2 2 0 0 0 2-1.6L21 8H6" />
                        <circle cx="9" cy="20" r="1" />
                        <circle cx="18" cy="20" r="1" />
                    </svg>
                    {{ isPending(selectedVariantId) ? 'Adding…' : selectedVariantOutOfStock ? 'Out of stock' : 'Add to cart' }}
                </button>

                <div v-if="product.description" class="mt-8 border-t border-neutral-text/10 pt-6">
                    <p class="text-xs font-medium uppercase tracking-wide text-neutral-text/50">Description</p>
                    <p class="mt-2 text-sm leading-relaxed text-neutral-text/70">
                        {{ product.description }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Related products -->
        <div v-if="normalizedRelated.length" class="mt-14 border-t border-neutral-text/10 pt-8">
            <h2 class="font-heading text-lg font-semibold tracking-tight text-neutral-text">You may also like</h2>
            <div class="mt-5 grid grid-cols-2 gap-3 sm:grid-cols-3 sm:gap-6 xl:grid-cols-6">
                <ProductCard
                    v-for="related in normalizedRelated"
                    :key="related.id"
                    :product="related"
                />
            </div>
        </div>
    </div>
</template>
