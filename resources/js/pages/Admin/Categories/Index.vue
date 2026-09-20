<script setup>
import { reactive, ref, watch } from 'vue'
import { Head, Link, router, useForm } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import AdminModal from '@/Components/Admin/AdminModal.vue'
import ImageField from '@/Components/Admin/ImageField.vue'

const props = defineProps({
    categories: { type: Object, required: true }, // paginator
    filters: { type: Object, required: true },
})

// Search
const search = reactive({ search: props.filters.search })
let timer = null
watch(
    () => search.search,
    () => {
        clearTimeout(timer)
        timer = setTimeout(
            () => router.get('/admin/categories', search, { preserveState: true, preserveScroll: true, replace: true }),
            350,
        )
    },
)

// Create / edit modal
const showModal = ref(false)
const editing = ref(null)
const slugEdited = ref(false)

const form = useForm({ name: '', slug: '', image: null, remove_image: false })

const slugify = (text) => text.toLowerCase().trim().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '')

// While creating, the slug follows the name until it is edited by hand.
watch(
    () => form.name,
    (name) => {
        if (!editing.value && !slugEdited.value) form.slug = slugify(name ?? '')
    },
)

const load = (values) => {
    form.defaults(values)
    form.reset()
    form.clearErrors()
}

const openCreate = () => {
    editing.value = null
    slugEdited.value = false
    load({ name: '', slug: '', image: null, remove_image: false })
    showModal.value = true
}

const openEdit = (category) => {
    editing.value = category
    load({ name: category.name, slug: category.slug, image: null, remove_image: false })
    showModal.value = true
}

const close = () => (showModal.value = false)

// PHP can't read files sent with PATCH, so updates are POST + _method spoofing.
const submit = () => {
    const options = { forceFormData: true, preserveScroll: true, onSuccess: close }

    if (editing.value) {
        form.transform((data) => ({ ...data, _method: 'patch' })).post(`/admin/categories/${editing.value.id}`, options)
    } else {
        form.transform((data) => data).post('/admin/categories', options)
    }
}

const remove = (category) => {
    if (confirm(`Remove the "${category.name}" category?`)) {
        router.delete(`/admin/categories/${category.id}`, { preserveScroll: true })
    }
}

const input =
    'w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500'
</script>

<template>
    <Head title="Categories" />

    <AdminLayout title="Categories">
        <template #actions>
            <button
                type="button"
                class="rounded-md bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700"
                @click="openCreate"
            >
                Add category
            </button>
        </template>

        <input
            v-model="search.search"
            type="search"
            placeholder="Search categories"
            class="mb-4 w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500 sm:max-w-sm"
        />

        <div class="rounded-lg border border-gray-200 bg-white">
            <p v-if="categories.data.length === 0" class="px-5 py-10 text-center text-sm text-gray-500">
                No categories found.
            </p>

            <div v-else class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-gray-200 text-xs text-gray-500">
                    <tr>
                        <th class="px-5 py-3 font-medium">Category</th>
                        <th class="px-5 py-3 font-medium">Slug</th>
                        <th class="px-5 py-3 font-medium">Products</th>
                        <th class="px-5 py-3 text-right font-medium">Actions</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                    <tr v-for="category in categories.data" :key="category.id" class="hover:bg-gray-50">
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-3">
                                <img
                                    v-if="category.image_url"
                                    :src="category.image_url"
                                    alt=""
                                    class="h-10 w-10 shrink-0 rounded-md bg-gray-100 object-cover"
                                    loading="lazy"
                                />
                                <div v-else class="h-10 w-10 shrink-0 rounded-md bg-gray-100" />
                                <span class="font-medium">{{ category.name }}</span>
                            </div>
                        </td>
                        <td class="px-5 py-3 text-gray-500">{{ category.slug }}</td>
                        <td class="px-5 py-3">{{ category.products_count }}</td>
                        <td class="px-5 py-3 text-right">
                            <button type="button" class="mr-3 text-emerald-700 hover:underline" @click="openEdit(category)">
                                Edit
                            </button>
                            <button
                                v-if="category.products_count === 0"
                                type="button"
                                class="text-red-600 hover:underline"
                                @click="remove(category)"
                            >
                                Remove
                            </button>
                            <span v-else class="text-xs text-gray-400" title="Reassign its products first">In use</span>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>

            <div
                v-if="categories.last_page > 1"
                class="flex flex-wrap items-center justify-between gap-3 border-t border-gray-200 px-5 py-3"
            >
                <p class="text-xs text-gray-500">
                    Showing {{ categories.from }} to {{ categories.to }} of {{ categories.total }} categories
                </p>
                <div class="flex flex-wrap gap-1">
                    <template v-for="link in categories.links" :key="link.label">
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

        <AdminModal :show="showModal" :title="editing ? 'Edit category' : 'Add category'" @close="close">
            <form class="space-y-4" @submit.prevent="submit">
                <ImageField :form="form" :current-url="editing?.image_url ?? null" />

                <div>
                    <label class="mb-1 block text-sm font-medium">Name</label>
                    <input v-model="form.name" type="text" :class="input" />
                    <p v-if="form.errors.name" class="mt-1 text-xs text-red-600">{{ form.errors.name }}</p>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium">Slug</label>
                    <input v-model="form.slug" type="text" :class="input" @input="slugEdited = true" />
                    <p class="mt-1 text-xs text-gray-400">
                        Used in shop links. Changing it on a live category breaks old links to it.
                    </p>
                    <p v-if="form.errors.slug" class="mt-1 text-xs text-red-600">{{ form.errors.slug }}</p>
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" class="rounded-md px-4 py-2 text-sm text-gray-600 hover:bg-gray-100" @click="close">
                        Cancel
                    </button>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="rounded-md bg-emerald-600 px-5 py-2 text-sm font-medium text-white hover:bg-emerald-700 disabled:opacity-50"
                    >
                        {{ form.processing ? 'Saving...' : editing ? 'Save changes' : 'Add category' }}
                    </button>
                </div>
            </form>
        </AdminModal>
    </AdminLayout>
</template>
