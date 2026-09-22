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

// Whole row opens the editor; real buttons inside keep their own behaviour.
const onRowClick = (event, category) => {
    if (event.target.closest('a, button')) return
    openEdit(category)
}

const inputBase =
    'w-full rounded-lg border bg-white px-3 py-2 text-sm text-neutral-text transition placeholder:text-neutral-text/40 focus:outline-none focus:ring-2'
const inputClass = (error) =>
    error
        ? `${inputBase} border-accent-light focus:border-accent-light focus:ring-accent-light/20`
        : `${inputBase} border-neutral-text/15 hover:border-neutral-text/30 focus:border-primary focus:ring-primary/20`

const ghostBtn =
    'inline-flex items-center gap-1 rounded-lg border px-3 py-1.5 text-xs font-medium transition focus-visible:outline-none focus-visible:ring-2 active:scale-[0.98]'
</script>

<template>
    <Head title="Categories" />

    <AdminLayout title="Categories">
        <template #actions>
            <button
                type="button"
                class="inline-flex items-center gap-1.5 rounded-lg bg-primary px-4 py-2 text-sm font-medium text-white transition hover:bg-primary-dark focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/40 focus-visible:ring-offset-2 active:scale-[0.98]"
                @click="openCreate"
            >
                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
                    <path d="M10 4v12M4 10h12" />
                </svg>
                Add category
            </button>
        </template>

        <!-- Search -->
        <div class="mb-4 rounded-xl border border-neutral-text/10 bg-white p-3">
            <div class="relative w-full sm:max-w-sm">
                <svg
                    class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-neutral-text/40"
                    viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" aria-hidden="true"
                >
                    <circle cx="9" cy="9" r="5.5" /><path d="m13.5 13.5 3 3" />
                </svg>
                <input v-model="search.search" type="search" placeholder="Search categories" :class="[inputClass(false), 'pl-9']" />
            </div>
        </div>

        <div class="overflow-hidden rounded-xl border border-neutral-text/10 bg-white">
            <div v-if="categories.data.length === 0" class="px-5 py-14 text-center">
                <p class="font-heading font-medium text-neutral-text">No categories found</p>
                <p class="mt-1 text-sm text-neutral-text/55">Try a different search, or add a new category.</p>
            </div>

            <div v-else class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-neutral-text/10 bg-neutral-bg text-xs text-neutral-text/55">
                    <tr>
                        <th class="px-5 py-3 font-medium">Category</th>
                        <th class="px-5 py-3 font-medium">Slug</th>
                        <th class="px-5 py-3 text-right font-medium">Products</th>
                        <th class="px-5 py-3 text-right font-medium">Actions</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-text/[0.07]">
                    <tr
                        v-for="category in categories.data"
                        :key="category.id"
                        class="group cursor-pointer transition-colors duration-150 hover:bg-primary-light"
                        @click="onRowClick($event, category)"
                    >
                        <td class="px-5 py-3.5">
                            <div class="flex items-center gap-3">
                                <img
                                    v-if="category.image_url"
                                    :src="category.image_url"
                                    alt=""
                                    class="h-11 w-11 shrink-0 rounded-lg border border-neutral-text/10 bg-neutral-bg object-cover"
                                    loading="lazy"
                                />
                                <div v-else class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg border border-neutral-text/10 bg-neutral-bg text-neutral-text/30">
                                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <rect x="3" y="4" width="14" height="12" rx="2" /><circle cx="7.5" cy="8.5" r="1.25" /><path d="m17 13-4-4-7 7" />
                                    </svg>
                                </div>
                                <span class="font-medium text-neutral-text">{{ category.name }}</span>
                            </div>
                        </td>
                        <td class="px-5 py-3.5">
                                <span class="rounded-md bg-neutral-text/[0.06] px-2 py-0.5 font-mono text-xs text-neutral-text/65">
                                    {{ category.slug }}
                                </span>
                        </td>
                        <td class="px-5 py-3.5 text-right tabular-nums text-neutral-text/70">{{ category.products_count }}</td>
                        <td class="px-5 py-3.5">
                            <div class="flex items-center justify-end gap-2">
                                <button
                                    type="button"
                                    :class="[ghostBtn, 'border-neutral-text/15 bg-white text-neutral-text group-hover:border-primary group-hover:bg-primary group-hover:text-white focus-visible:ring-primary/40']"
                                    @click="openEdit(category)"
                                >
                                    Edit
                                </button>
                                <button
                                    v-if="category.products_count === 0"
                                    type="button"
                                    :class="[ghostBtn, 'border-transparent text-neutral-text/55 hover:border-accent-light/60 hover:bg-accent-light/10 hover:text-accent focus-visible:ring-accent-light/40']"
                                    @click="remove(category)"
                                >
                                    Remove
                                </button>
                                <span
                                    v-else
                                    class="rounded-full bg-secondary px-2.5 py-1 text-xs font-medium text-primary-dark"
                                    title="Reassign its products first"
                                >
                                        In use
                                    </span>
                            </div>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>

            <div
                v-if="categories.last_page > 1"
                class="flex flex-wrap items-center justify-between gap-3 border-t border-neutral-text/10 bg-neutral-bg px-5 py-3"
            >
                <p class="text-xs text-neutral-text/55">
                    Showing {{ categories.from }} to {{ categories.to }} of {{ categories.total }} categories
                </p>
                <div class="flex flex-wrap gap-1">
                    <template v-for="link in categories.links" :key="link.label">
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

        <AdminModal :show="showModal" :title="editing ? 'Edit category' : 'Add category'" @close="close">
            <form class="space-y-5" @submit.prevent="submit">
                <ImageField :form="form" :current-url="editing?.image_url ?? null" />

                <div>
                    <label for="category-name" class="mb-1.5 block text-sm font-medium text-neutral-text">Name</label>
                    <input id="category-name" v-model="form.name" type="text" :class="inputClass(form.errors.name)" />
                    <p v-if="form.errors.name" class="mt-1.5 text-xs font-medium text-accent">{{ form.errors.name }}</p>
                </div>

                <div>
                    <label for="category-slug" class="mb-1.5 block text-sm font-medium text-neutral-text">Slug</label>
                    <input
                        id="category-slug"
                        v-model="form.slug"
                        type="text"
                        :class="inputClass(form.errors.slug)"
                        @input="slugEdited = true"
                    />
                    <p class="mt-1.5 text-xs text-neutral-text/50">
                        Used in shop links. Changing it on a live category breaks old links to it.
                    </p>
                    <p v-if="form.errors.slug" class="mt-1.5 text-xs font-medium text-accent">{{ form.errors.slug }}</p>
                </div>

                <div class="-mx-5 -mb-5 flex justify-end gap-3 border-t border-neutral-text/10 bg-neutral-bg px-5 py-4">
                    <button
                        type="button"
                        class="rounded-lg border border-neutral-text/15 bg-white px-4 py-2 text-sm font-medium text-neutral-text transition hover:bg-neutral-text/[0.04] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/40"
                        @click="close"
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="rounded-lg bg-primary px-5 py-2 text-sm font-medium text-white transition hover:bg-primary-dark focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/40 focus-visible:ring-offset-2 active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        {{ form.processing ? 'Saving...' : editing ? 'Save changes' : 'Add category' }}
                    </button>
                </div>
            </form>
        </AdminModal>
    </AdminLayout>
</template>
