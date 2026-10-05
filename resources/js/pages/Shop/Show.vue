<script setup>
import { computed, ref } from 'vue'
import { Link } from '@inertiajs/vue3'
import ProductCard from '@/Components/Welcome/ProductCard.vue'
import { useCart } from '@/composables/useCart' // adjust the path if yours differs

const props = defineProps({
    product: { type: Object, required: true },
    relatedProducts: { type: Array, default: () => [] },
})

const { add, isPending, quantityOf } = useCart()

// --- Variant selection ---------------------------------------------------

const firstAvailable = props.product.variants.find((v) => v.stock_quantity > 0)
const selectedVariantId = ref(firstAvailable?.id ?? props.product.variants[0]?.id ?? null)

const selectedVariant = computed(() =>
    props.product.variants.find((v) => v.id === selectedVariantId.value) ?? null
)

const selectedVariantOutOfStock = computed(
    () => !selectedVariant.value || selectedVariant.value.stock_quantity <= 0
)

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

// How many of the currently-selected variant are already in the cart —
// same idea as ProductCard's inCartQty, so the button can say so.
const inCartQty = computed(() => (selectedVariantId.value ? quantityOf(selectedVariantId.value) : 0))

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

const primaryCategory = computed(() => props.product.categories?.[0] ?? null)
</script>

<template>
    <div class="mx-auto max-w-6xl px-4 py-8 sm:px-6 md:py-10">
        <!-- Breadcrumb: every step is a real link back up the tree -->
        <nav class="flex items-center gap-1.5 text-xs text-neutral-text/55" aria-label="Breadcrumb">
            <Link :href="route('shop.index')" class="inline-flex items-center gap-1 font-medium text-primary-dark transition-colors hover:underline hover:underline-offset-4">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="m15 6-6 6 6 6" />
                </svg>
                Shop
            </Link>
            <template v-if="primaryCategory">
                <span aria-hidden="true">›</span>
                <Link
                    :href="route('shop.index', { category: primaryCategory.slug })"
                    class="transition-colors hover:text-primary-dark"
                >
                    {{ primaryCategory.name }}
                </Link>
            </template>
            <span aria-hidden="true">›</span>
            <span class="truncate text-neutral-text/80" aria-current="page">{{ product.name }}</span>
        </nav>

        <div class="mt-6 grid grid-cols-1 gap-8 lg:grid-cols-2 lg:gap-14">
            <!-- Gallery -->
            <div>
                <div class="relative aspect-square w-full overflow-hidden rounded-lg border border-neutral-text/10 bg-secondary/40">
                    <Transition
                        mode="out-in"
                        enter-active-class="transition-opacity duration-200 motion-reduce:transition-none"
                        enter-from-class="opacity-0"
                        leave-active-class="transition-opacity duration-150 motion-reduce:transition-none"
                        leave-to-class="opacity-0"
                    >
                        <img
                            v-if="currentImage"
                            :key="currentImage.id"
                            :src="currentImage.url"
                            :alt="product.name"
                            class="h-full w-full object-cover"
                        />
                        <div v-else class="flex h-full w-full items-center justify-center text-xs text-neutral-text/40">
                            No image available
                        </div>
                    </Transition>

                    <span
                        v-if="product.is_out_of_stock"
                        class="absolute left-3 top-3 rounded-md bg-neutral-text/85 px-2 py-1 text-xs font-medium text-neutral-bg backdrop-blur-sm"
                    >
                        Out of stock
                    </span>
                </div>

                <div v-if="galleryImages.length > 1" class="mt-3 flex gap-2 overflow-x-auto pb-1">
                    <button
                        v-for="image in galleryImages"
                        :key="image.id"
                        type="button"
                        :aria-label="`Show image ${galleryImages.indexOf(image) + 1}`"
                        :aria-pressed="currentImage?.id === image.id"
                        class="h-16 w-16 shrink-0 overflow-hidden rounded-md border-2 transition focus:outline-none focus-visible:ring-2 focus-visible:ring-primary motion-reduce:transition-none"
                        :class="currentImage?.id === image.id
                            ? 'border-primary-dark'
                            : 'border-transparent opacity-70 ring-1 ring-neutral-text/10 hover:opacity-100 hover:ring-neutral-text/25'"
                        @click="selectImage(image)"
                    >
                        <img :src="image.url" alt="" class="h-full w-full object-cover" />
                    </button>
                </div>
            </div>

            <!-- Details: sticks while the description scrolls on desktop -->
            <div class="lg:sticky lg:top-24 lg:self-start">
                <div v-if="product.categories?.length" class="flex flex-wrap gap-1.5">
                    <Link
                        v-for="category in product.categories"
                        :key="category.slug"
                        :href="route('shop.index', { category: category.slug })"
                        class="rounded-md bg-primary-light px-2 py-0.5 text-xs font-medium text-primary-dark transition-colors hover:bg-secondary focus:outline-none focus-visible:ring-2 focus-visible:ring-primary"
                    >
                        {{ category.name }}
                    </Link>
                </div>

                <h1 class="mt-3 font-heading text-2xl font-semibold leading-tight tracking-tight text-neutral-text sm:text-3xl">
                    {{ product.name }}
                </h1>

                <div class="mt-3 flex flex-wrap items-center gap-x-3 gap-y-2">
                    <p class="font-heading text-2xl font-semibold text-primary-dark tabular-nums">
                        {{ formatPrice(selectedVariant?.price) }}
                    </p>

                    <span
                        v-if="selectedVariantOutOfStock"
                        class="inline-flex items-center gap-1.5 text-xs font-medium text-neutral-text/60"
                    >
                        <span class="h-1.5 w-1.5 rounded-full bg-neutral-text/40" aria-hidden="true"></span>
                        Out of stock
                    </span>
                    <span
                        v-else-if="lowStock"
                        class="rounded-md bg-accent/10 px-2 py-0.5 text-xs font-medium text-accent"
                    >
                        Only {{ selectedVariant.stock_quantity }} left
                    </span>
                    <span v-else class="inline-flex items-center gap-1.5 text-xs font-medium text-primary-dark">
                        <span class="h-1.5 w-1.5 rounded-full bg-primary" aria-hidden="true"></span>
                        In stock
                    </span>
                </div>

                <!-- Purchase panel: sections separated by hairlines -->
                <div class="mt-6 divide-y divide-neutral-text/10 border-y border-neutral-text/10">
                    <!-- Variant picker -->
                    <div v-if="product.variants.length > 1" class="py-5">
                        <p class="text-sm font-medium text-neutral-text">Options</p>
                        <div class="mt-3 flex flex-wrap gap-2">
                            <button
                                v-for="variant in product.variants"
                                :key="variant.id"
                                type="button"
                                :disabled="variant.stock_quantity <= 0"
                                :aria-pressed="selectedVariantId === variant.id"
                                class="rounded-md border px-3.5 py-2 text-sm font-medium transition-colors duration-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2 focus-visible:ring-offset-neutral-bg motion-reduce:transition-none"
                                :class="[
                                    selectedVariantId === variant.id
                                        ? 'border-primary-dark bg-primary-dark text-neutral-bg'
                                        : 'border-neutral-text/15 text-neutral-text hover:border-primary-dark/50 hover:bg-primary-light',
                                    variant.stock_quantity <= 0 && 'cursor-not-allowed line-through opacity-40 hover:border-neutral-text/15 hover:bg-transparent',
                                ]"
                                @click="selectVariant(variant.id)"
                            >
                                {{ variant.variant_name }}
                            </button>
                        </div>
                    </div>

                    <!-- Quantity + add to cart on one row -->
                    <div class="flex flex-col gap-3 py-5 sm:flex-row sm:items-center">
                        <div class="flex items-center justify-between gap-3 sm:justify-start">
                            <p class="text-sm font-medium text-neutral-text sm:sr-only">Quantity</p>
                            <div class="flex items-center rounded-md border border-neutral-text/15">
                                <button
                                    type="button"
                                    aria-label="Decrease quantity"
                                    class="flex h-11 w-11 items-center justify-center text-base text-neutral-text/60 transition-colors hover:text-primary-dark focus:outline-none focus-visible:ring-2 focus-visible:ring-primary disabled:opacity-30 disabled:hover:text-neutral-text/60"
                                    :disabled="quantity <= 1"
                                    @click="decrementQuantity"
                                >
                                    −
                                </button>
                                <span class="w-10 text-center text-sm font-medium tabular-nums text-neutral-text" aria-live="polite">{{ quantity }}</span>
                                <button
                                    type="button"
                                    aria-label="Increase quantity"
                                    class="flex h-11 w-11 items-center justify-center text-base text-neutral-text/60 transition-colors hover:text-primary-dark focus:outline-none focus-visible:ring-2 focus-visible:ring-primary disabled:opacity-30 disabled:hover:text-neutral-text/60"
                                    :disabled="quantity >= maxQuantity"
                                    @click="incrementQuantity"
                                >
                                    +
                                </button>
                            </div>
                        </div>

                        <button
                            type="button"
                            class="flex h-11 flex-1 items-center justify-center gap-2 rounded-md p-5 text-sm font-medium shadow-sm transition duration-200 hover:shadow focus:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2 focus-visible:ring-offset-neutral-bg active:scale-[0.98] disabled:cursor-not-allowed disabled:bg-neutral-text/20 disabled:text-neutral-text/50 disabled:shadow-none disabled:active:scale-100 motion-reduce:transition-none motion-reduce:active:scale-100"
                            :class="inCartQty && !selectedVariantOutOfStock
                                ? 'bg-secondary text-primary-dark hover:bg-primary/20'
                                : 'bg-primary-dark text-neutral-bg hover:bg-primary-dark/90'"
                            :disabled="selectedVariantOutOfStock || isPending(selectedVariantId)"
                            @click="addToCart"
                        >
                            <svg
                                v-if="isPending(selectedVariantId)"
                                class="h-4 w-4 animate-spin motion-reduce:animate-none"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"
                            >
                                <circle cx="12" cy="12" r="9" stroke-opacity="0.25" />
                                <path d="M21 12a9 9 0 0 0-9-9" stroke-linecap="round" />
                            </svg>
                            <svg
                                v-else-if="inCartQty && !selectedVariantOutOfStock"
                                width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.25"
                                stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"
                            >
                                <path d="M5 12.5l4.5 4.5L19 7.5" />
                            </svg>
                            <svg
                                v-else-if="!selectedVariantOutOfStock"
                                width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"
                            >
                                <path d="M3 3h2l2.4 12.4a2 2 0 0 0 2 1.6h7.2a2 2 0 0 0 2-1.6L21 8H6" />
                                <circle cx="9" cy="20" r="1" />
                                <circle cx="18" cy="20" r="1" />
                            </svg>
                            {{
                                isPending(selectedVariantId)
                                    ? 'Adding…'
                                    : selectedVariantOutOfStock
                                        ? 'Out of stock'
                                        : inCartQty
                                            ? `In cart (${inCartQty})`
                                            : 'Add to cart'
                            }}
                        </button>
                    </div>

                    <!-- Description -->
                    <div v-if="product.description" class="py-5">
                        <p class="text-sm font-medium text-neutral-text">About this product</p>
                        <p class="mt-2 max-w-prose text-sm leading-relaxed text-neutral-text/70">
                            {{ product.description }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Related products -->
        <div v-if="normalizedRelated.length" class="mt-14 border-t border-neutral-text/10 pt-8">
            <div class="flex items-end justify-between gap-4">
                <h2 class="font-heading text-lg font-semibold tracking-tight text-neutral-text">You may also like</h2>
                <Link
                    :href="primaryCategory ? route('shop.index', { category: primaryCategory.slug }) : route('shop.index')"
                    class="text-sm font-medium text-primary-dark underline-offset-4 hover:underline"
                >
                    {{ primaryCategory ? `More in ${primaryCategory.name}` : 'View all' }}
                </Link>
            </div>
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
