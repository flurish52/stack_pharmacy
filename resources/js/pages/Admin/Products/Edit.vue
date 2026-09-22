<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import ProductForm from '@/Components/Admin/ProductForm.vue'
import ProductImages from '@/Components/Admin/ProductImages.vue'
import ProductStatusBadge from '@/Components/Admin/ProductStatusBadge.vue'

const props = defineProps({
    product: { type: Object, required: true },
    primaryCategoryId: { type: Number, default: null },
    categories: { type: Array, default: () => [] },
})

const form = useForm({
    name: props.product.name,
    slug: props.product.slug,
    description: props.product.description ?? '',
    status: props.product.status,
    category_ids: props.product.categories.map((c) => c.id),
    primary_category_id: props.primaryCategoryId,
    variants: props.product.variants.map((v) => ({
        id: v.id,
        variant_name: v.variant_name,
        sku: v.sku,
        price: v.price,
        stock_quantity: v.stock_quantity,
    })),
})

// After saving, the server sends fresh variants (new ones now have ids).
const submit = () =>
    form.put(`/admin/products/${props.product.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            form.variants = props.product.variants.map((v) => ({
                id: v.id,
                variant_name: v.variant_name,
                sku: v.sku,
                price: v.price,
                stock_quantity: v.stock_quantity,
            }))
        },
    })
</script>

<template>
    <Head :title="`Edit ${product.name}`" />

    <AdminLayout :title="`Edit ${product.name}`">
        <template #actions>
            <ProductStatusBadge :status="product.status" />
        </template>

        <div class="page-in">
            <Link
                href="/admin/products"
                class="group mb-4 inline-flex items-center gap-1.5 rounded-lg border border-neutral-text/15 bg-white px-3 py-1.5 text-sm font-medium text-neutral-text transition hover:border-primary/40 hover:bg-primary-light hover:text-primary-dark focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/40"
            >
                <svg class="h-4 w-4 transition-transform duration-150 group-hover:-translate-x-0.5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M16 10H5M9 5l-5 5 5 5" />
                </svg>
                All products
            </Link>

            <div class="space-y-6">
                <ProductImages :product="product" />

                <ProductForm :form="form" :categories="categories" submit-label="Save changes">
                    <template #cancel>
                        <Link
                            href="/admin/products"
                            class="rounded-lg border border-neutral-text/15 bg-white px-4 py-2 text-sm font-medium text-neutral-text transition hover:bg-neutral-bg focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/40"
                        >
                            Back
                        </Link>
                    </template>
                </ProductForm>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
.page-in {
    animation: page-in 0.3s ease-out both;
}

@keyframes page-in {
    from {
        opacity: 0;
        transform: translateY(6px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@media (prefers-reduced-motion: reduce) {
    .page-in {
        animation: none;
    }
}
</style>
