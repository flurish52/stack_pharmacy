<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import ProductForm from '@/Components/Admin/ProductForm.vue'

defineProps({
    categories: { type: Array, default: () => [] },
})

const form = useForm({
    name: '',
    slug: '',
    description: '',
    status: 'active',
    category_ids: [],
    primary_category_id: null,
    variants: [{ id: null, variant_name: '', sku: '', price: '', stock_quantity: 0 }],
})

const submit = () => form.post('/admin/products')
</script>

<template>
    <Head title="Add product" />

    <AdminLayout title="Add product">
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

            <ProductForm :form="form" :categories="categories" auto-slug submit-label="Create product" @submit="submit">
                <template #cancel>
                    <Link
                        href="/admin/products"
                        class="rounded-lg border border-neutral-text/15 bg-white px-4 py-2 text-sm font-medium text-neutral-text transition hover:bg-neutral-bg focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/40"
                    >
                        Cancel
                    </Link>
                </template>
            </ProductForm>
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
