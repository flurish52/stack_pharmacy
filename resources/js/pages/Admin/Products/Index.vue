<script setup>
import { reactive, watch } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { naira } from '@/Composables/OrderStatus.js'

const props = defineProps({
    products: { type: Object, required: true },
    filters: { type: Object, required: true },
    categories: { type: Array, default: () => [] },
    statuses: { type: Array, default: () => [] },
    lowStockThreshold: { type: Number, default: 5 },
    canForceDelete: { type: Boolean, default: false },
})

const form = reactive({
    search: props.filters.search,
    status: props.filters.status,
    category: props.filters.category,
    trashed: props.filters.trashed,
})

let timer = null

const apply = () =>
    router.get('/admin/products', form, { preserveState: true, preserveScroll: true, replace: true })

watch(() => [form.status, form.category, form.trashed], apply)
watch(
    () => form.search,
    () => {
        clearTimeout(timer)
        timer = setTimeout(apply, 350)
    },
)

const isTrashed = () => form.trashed === '1'

const statusStyles = {
    active: 'bg-emerald-50 text-emerald-700',
    draft: 'bg-gray-100 text-gray-700',
    archived: 'bg-amber-50 text-amber-700',
}

const totalStock = (product) => product.variants.reduce((sum, v) => sum + Number(v.stock_quantity), 0)
const outOfStock = (product) => product.variants.filter((v) => Number(v.stock_quantity) <= 0).length
const lowStock = (product) =>
    product.variants.filter((v) => Number(v.stock_quantity) > 0 && Number(v.stock_quantity) <= props.lowStockThreshold).length

const remove = (product) => {
    if (confirm(`Remove "${product.name}"? It disappears from the shop but can be restored.`)) {
        router.delete(`/admin/products/${product.id}`, { preserveScroll: true })
    }
}

const restore = (product) => router.patch(`/admin/products/${product.id}/restore`, {}, { preserveScroll: true })

const forceDelete = (product) => {
    if (confirm(`Permanently delete "${product.name}" and its images? This cannot be undone.`)) {
        router.delete(`/admin/products/${product.id}/force`, { preserveScroll: true })
    }
}
</script>

<template>
    <Head title="Products" />

    <AdminLayout title="Products">
        <template #actions>
            <Link
                href="/admin/products/create"
                class="rounded-md bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700"
            >
                Add product
            </Link>
        </template>

        <!-- Active / Trashed -->
        <div class="mb-4 flex gap-1 border-b border-gray-200">
            <button
                type="button"
                class="-mb-px border-b-2 px-4 py-2 text-sm font-medium"
                :class="!isTrashed() ? 'border-emerald-600 text-emerald-700' : 'border-transparent text-gray-500 hover:text-gray-900'"
                @click="form.trashed = ''"
            >
                Products
            </button>
            <button
                type="button"
                class="-mb-px border-b-2 px-4 py-2 text-sm font-medium"
                :class="isTrashed() ? 'border-emerald-600 text-emerald-700' : 'border-transparent text-gray-500 hover:text-gray-900'"
                @click="form.trashed = '1'"
            >
                Trashed
            </button>
        </div>

        <!-- Filters -->
        <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center">
            <input
                v-model="form.search"
                type="search"
                placeholder="Search by name, slug or SKU"
                class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500 sm:max-w-sm"
            />
            <select
                v-model="form.status"
                class="rounded-md border border-gray-300 px-3 py-2 text-sm capitalize focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
            >
                <option value="">All statuses</option>
                <option v-for="s in statuses" :key="s" :value="s">{{ s }}</option>
            </select>
            <select
                v-model="form.category"
                class="rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
            >
                <option value="">All categories</option>
                <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
            </select>
        </div>

        <div class="rounded-lg border border-gray-200 bg-white">
            <p v-if="products.data.length === 0" class="px-5 py-10 text-center text-sm text-gray-500">
                {{ isTrashed() ? 'Nothing in the trash.' : 'No products match these filters.' }}
            </p>

            <div v-else class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-gray-200 text-xs text-gray-500">
                    <tr>
                        <th class="px-5 py-3 font-medium">Product</th>
                        <th class="px-5 py-3 font-medium">Categories</th>
                        <th class="px-5 py-3 font-medium">Status</th>
                        <th class="px-5 py-3 font-medium">Variants</th>
                        <th class="px-5 py-3 font-medium">Stock</th>
                        <th class="px-5 py-3 text-right font-medium">From</th>
                        <th class="px-5 py-3 text-right font-medium">Actions</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                    <tr v-for="product in products.data" :key="product.id" class="hover:bg-gray-50">
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-3">
                                <img
                                    v-if="product.primary_image_url"
                                    :src="product.primary_image_url"
                                    alt=""
                                    class="h-10 w-10 rounded-md bg-gray-100 object-cover"
                                    loading="lazy"
                                />
                                <div v-else class="h-10 w-10 rounded-md bg-gray-100" />
                                <div>
                                    <p class="font-medium">{{ product.name }}</p>
                                    <p class="text-xs text-gray-400">{{ product.slug }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-3 text-gray-600">
                            {{ product.categories.map((c) => c.name).join(', ') || '-' }}
                        </td>
                        <td class="px-5 py-3">
                                <span
                                    class="rounded-full px-2.5 py-1 text-xs font-medium capitalize"
                                    :class="statusStyles[product.status] ?? 'bg-gray-100 text-gray-700'"
                                >
                                    {{ product.status }}
                                </span>
                        </td>
                        <td class="px-5 py-3">{{ product.variants.length }}</td>
                        <td class="px-5 py-3">
                            <p>{{ totalStock(product) }}</p>
                            <p v-if="outOfStock(product)" class="text-xs text-red-600">
                                {{ outOfStock(product) }} out of stock
                            </p>
                            <p v-if="lowStock(product)" class="text-xs text-amber-600">
                                {{ lowStock(product) }} running low
                            </p>
                        </td>
                        <td class="px-5 py-3 text-right">
                            {{ product.starting_price ? naira.format(product.starting_price) : '-' }}
                        </td>
                        <td class="px-5 py-3 text-right">
                            <template v-if="!isTrashed()">
                                <Link
                                    :href="`/admin/products/${product.id}/edit`"
                                    class="mr-3 text-emerald-700 hover:underline"
                                >
                                    Edit
                                </Link>
                                <button type="button" class="text-red-600 hover:underline" @click="remove(product)">
                                    Remove
                                </button>
                            </template>
                            <template v-else>
                                <button type="button" class="mr-3 text-emerald-700 hover:underline" @click="restore(product)">
                                    Restore
                                </button>
                                <button
                                    v-if="canForceDelete"
                                    type="button"
                                    class="text-red-600 hover:underline"
                                    @click="forceDelete(product)"
                                >
                                    Delete permanently
                                </button>
                            </template>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>

            <div
                v-if="products.last_page > 1"
                class="flex flex-wrap items-center justify-between gap-3 border-t border-gray-200 px-5 py-3"
            >
                <p class="text-xs text-gray-500">
                    Showing {{ products.from }} to {{ products.to }} of {{ products.total }} products
                </p>
                <div class="flex flex-wrap gap-1">
                    <template v-for="link in products.links" :key="link.label">
                        <Link
                            v-if="link.url"
                            :href="link.url"
                            preserve-scroll
                            class="rounded-md px-3 py-1.5 text-sm"
                            :class="link.active ? 'bg-emerald-600 text-white' : 'text-gray-600 hover:bg-gray-100'"
                            v-html="link.label"
                        />
                        <span v-else class="rounded-md px-3 py-1.5 text-sm text-gray-300" v-html="link.label" />
                    </template>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
