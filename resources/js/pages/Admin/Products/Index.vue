<script setup>
import { reactive, watch } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import ProductStatusBadge from '@/Components/Admin/ProductStatusBadge.vue'
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

const hasFilters = () => form.search || form.status || form.category

const reset = () => {
    form.search = ''
    form.status = ''
    form.category = ''
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

// Whole row opens the editor (active tab only); real links/buttons keep their own behaviour.
const openProduct = (event, product) => {
    if (isTrashed() || event.target.closest('a, button')) return
    router.visit(`/admin/products/${product.id}/edit`)
}

const field =
    'rounded-lg border border-neutral-text/15 bg-white px-3 py-2 text-sm text-neutral-text transition placeholder:text-neutral-text/40 hover:border-neutral-text/30 focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20'
const ghostBtn =
    'inline-flex items-center gap-1 rounded-lg border px-3 py-1.5 text-xs font-medium transition focus-visible:outline-none focus-visible:ring-2 active:scale-[0.98]'
</script>

<template>
    <Head title="Products" />

    <AdminLayout title="Products">
        <template #actions>
            <Link
                href="/admin/products/create"
                class="inline-flex items-center gap-1.5 rounded-lg bg-primary px-4 py-2 text-sm font-medium text-white transition hover:bg-primary-dark focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/40 focus-visible:ring-offset-2 active:scale-[0.98]"
            >
                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
                    <path d="M10 4v12M4 10h12" />
                </svg>
                Add product
            </Link>
        </template>

        <!-- Active / Trashed -->
        <div class="mb-4 inline-flex rounded-xl border border-neutral-text/10 bg-white p-1">
            <button
                type="button"
                class="rounded-lg px-4 py-1.5 text-sm font-medium transition"
                :class="!isTrashed() ? 'bg-primary text-white' : 'text-neutral-text/60 hover:bg-primary-light hover:text-primary-dark'"
                @click="form.trashed = ''"
            >
                Products
            </button>
            <button
                type="button"
                class="rounded-lg px-4 py-1.5 text-sm font-medium transition"
                :class="isTrashed() ? 'bg-primary text-white' : 'text-neutral-text/60 hover:bg-primary-light hover:text-primary-dark'"
                @click="form.trashed = '1'"
            >
                Trashed
            </button>
        </div>

        <!-- Filters -->
        <div class="mb-4 flex flex-col gap-3 rounded-xl border border-neutral-text/10 bg-white p-3 sm:flex-row sm:items-center">
            <div class="relative w-full sm:max-w-sm">
                <svg
                    class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-neutral-text/40"
                    viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" aria-hidden="true"
                >
                    <circle cx="9" cy="9" r="5.5" /><path d="m13.5 13.5 3 3" />
                </svg>
                <input v-model="form.search" type="search" placeholder="Search name, slug or SKU" :class="[field, 'w-full pl-9']" />
            </div>

            <select v-model="form.status" :class="[field, 'capitalize']">
                <option value="">All statuses</option>
                <option v-for="s in statuses" :key="s" :value="s">{{ s }}</option>
            </select>

            <select v-model="form.category" :class="field">
                <option value="">All categories</option>
                <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
            </select>

            <button
                v-if="hasFilters()"
                type="button"
                class="rounded-lg px-3 py-2 text-sm font-medium text-primary-dark transition hover:bg-primary-light sm:ml-auto"
                @click="reset"
            >
                Clear filters
            </button>
        </div>

        <div class="overflow-hidden rounded-xl border border-neutral-text/10 bg-white">
            <div v-if="products.data.length === 0" class="px-5 py-14 text-center">
                <p class="font-heading font-medium text-neutral-text">
                    {{ isTrashed() ? 'Trash is empty' : 'No products found' }}
                </p>
                <p class="mt-1 text-sm text-neutral-text/55">
                    {{ isTrashed() ? 'Removed products will show up here.' : 'Try a different search or clear the filters.' }}
                </p>
            </div>

            <div v-else class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-neutral-text/10 bg-neutral-bg text-xs text-neutral-text/55">
                    <tr>
                        <th class="px-5 py-3 font-medium">Product</th>
                        <th class="px-5 py-3 font-medium">Categories</th>
                        <th class="px-5 py-3 font-medium">Status</th>
                        <th class="px-5 py-3 text-right font-medium">Variants</th>
                        <th class="px-5 py-3 font-medium">Stock</th>
                        <th class="px-5 py-3 text-right font-medium">From</th>
                        <th class="px-5 py-3 text-right font-medium">Actions</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-text/[0.07]">
                    <tr
                        v-for="product in products.data"
                        :key="product.id"
                        class="group transition-colors duration-150 hover:bg-primary-light"
                        :class="isTrashed() ? '' : 'cursor-pointer'"
                        @click="openProduct($event, product)"
                    >
                        <td class="px-5 py-3.5">
                            <div class="flex items-center gap-3">
                                <img
                                    v-if="product.primary_image_url"
                                    :src="product.primary_image_url"
                                    alt=""
                                    class="h-11 w-11 shrink-0 rounded-lg border border-neutral-text/10 bg-neutral-bg object-cover"
                                    loading="lazy"
                                />
                                <div v-else class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg border border-neutral-text/10 bg-neutral-bg text-neutral-text/30">
                                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <rect x="3" y="4" width="14" height="12" rx="2" /><circle cx="7.5" cy="8.5" r="1.25" /><path d="m17 13-4-4-7 7" />
                                    </svg>
                                </div>
                                <div class="min-w-0">
                                    <p class="truncate font-medium text-neutral-text">{{ product.name }}</p>
                                    <p class="truncate text-xs text-neutral-text/50">{{ product.slug }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-3.5">
                            <div v-if="product.categories.length" class="flex flex-wrap gap-1">
                                    <span
                                        v-for="c in product.categories"
                                        :key="c.id"
                                        class="rounded-md bg-neutral-text/[0.06] px-2 py-0.5 text-xs text-neutral-text/70"
                                    >
                                        {{ c.name }}
                                    </span>
                            </div>
                            <span v-else class="text-neutral-text/35">-</span>
                        </td>
                        <td class="px-5 py-3.5"><ProductStatusBadge :status="product.status" /></td>
                        <td class="px-5 py-3.5 text-right tabular-nums text-neutral-text/70">{{ product.variants.length }}</td>
                        <td class="px-5 py-3.5">
                            <p class="font-medium tabular-nums text-neutral-text">{{ totalStock(product) }}</p>
                            <p v-if="outOfStock(product)" class="text-xs font-medium text-accent">
                                {{ outOfStock(product) }} out of stock
                            </p>
                            <p v-if="lowStock(product)" class="text-xs text-accent">
                                {{ lowStock(product) }} running low
                            </p>
                        </td>
                        <td class="whitespace-nowrap px-5 py-3.5 text-right font-medium tabular-nums text-neutral-text">
                            {{ product.starting_price ? naira.format(product.starting_price) : '-' }}
                        </td>
                        <td class="px-5 py-3.5">
                            <div class="flex items-center justify-end gap-2">
                                <template v-if="!isTrashed()">
                                    <Link
                                        :href="`/admin/products/${product.id}/edit`"
                                        :class="[ghostBtn, 'border-neutral-text/15 bg-white text-neutral-text group-hover:border-primary group-hover:bg-primary group-hover:text-white focus-visible:ring-primary/40']"
                                    >
                                        Edit
                                    </Link>
                                    <button
                                        type="button"
                                        :class="[ghostBtn, 'border-transparent text-neutral-text/55 hover:border-accent-light/60 hover:bg-accent-light/10 hover:text-accent focus-visible:ring-accent-light/40']"
                                        @click="remove(product)"
                                    >
                                        Remove
                                    </button>
                                </template>
                                <template v-else>
                                    <button
                                        type="button"
                                        :class="[ghostBtn, 'border-primary/40 bg-white text-primary-dark hover:bg-primary-light focus-visible:ring-primary/40']"
                                        @click="restore(product)"
                                    >
                                        Restore
                                    </button>
                                    <button
                                        v-if="canForceDelete"
                                        type="button"
                                        :class="[ghostBtn, 'border-accent-light/60 bg-white text-accent hover:bg-accent-light/10 focus-visible:ring-accent-light/40']"
                                        @click="forceDelete(product)"
                                    >
                                        Delete permanently
                                    </button>
                                </template>
                            </div>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>

            <div
                v-if="products.last_page > 1"
                class="flex flex-wrap items-center justify-between gap-3 border-t border-neutral-text/10 bg-neutral-bg px-5 py-3"
            >
                <p class="text-xs text-neutral-text/55">
                    Showing {{ products.from }} to {{ products.to }} of {{ products.total }} products
                </p>
                <div class="flex flex-wrap gap-1">
                    <template v-for="link in products.links" :key="link.label">
                        <Link
                            v-if="link.url"
                            :href="link.url"
                            preserve-scroll
                            class="rounded-lg px-3 py-1.5 text-sm transition"
                            :class="link.active ? 'bg-primary font-medium text-white' : 'text-neutral-text/70 hover:bg-secondary hover:text-primary-dark'"
                            v-html="link.label"
                        />
                        <span v-else class="rounded-lg px-3 py-1.5 text-sm text-neutral-text/25" v-html="link.label" />
                    </template>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
