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
        <Link href="/admin/products" class="mb-4 inline-block text-sm text-gray-500 hover:text-gray-900">
            Back to products
        </Link>

        <ProductForm :form="form" :categories="categories" auto-slug submit-label="Create product" @submit="submit">
            <template #cancel>
                <Link href="/admin/products" class="rounded-md px-4 py-2 text-sm text-gray-600 hover:bg-gray-100">
                    Cancel
                </Link>
            </template>
        </ProductForm>
    </AdminLayout>
</template>
