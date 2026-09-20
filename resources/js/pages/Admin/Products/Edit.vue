<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import ProductForm from '@/Components/Admin/ProductForm.vue'
import ProductImages from '@/Components/Admin/ProductImages.vue'

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
        <Link href="/admin/products" class="mb-4 inline-block text-sm text-gray-500 hover:text-gray-900">
            Back to products
        </Link>

        <div class="space-y-6">
            <ProductImages :product="product" />

            <ProductForm :form="form" :categories="categories" submit-label="Save changes">
                <template #cancel>
                    <Link href="/admin/products" class="rounded-md px-4 py-2 text-sm text-gray-600 hover:bg-gray-100">
                        Back
                    </Link>
                </template>
            </ProductForm>
        </div>
    </AdminLayout>
</template>
