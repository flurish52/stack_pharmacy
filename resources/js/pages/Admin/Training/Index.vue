<script setup>
import { ref } from 'vue'
import { Head, Link, router, useForm } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import AdminModal from '@/Components/Admin/AdminModal.vue'
import ImageField from '@/Components/Admin/ImageField.vue'

defineProps({
    trainings: { type: Array, default: () => [] },
})

const showModal = ref(false)
const editing = ref(null) // null = creating

const form = useForm({ title: '', description: '', image: null, remove_image: false })

const load = (values) => {
    form.defaults(values)
    form.reset()
    form.clearErrors()
}

const openCreate = () => {
    editing.value = null
    load({ title: '', description: '', image: null, remove_image: false })
    showModal.value = true
}

const openEdit = (training) => {
    editing.value = training
    load({ title: training.title, description: training.description ?? '', image: null, remove_image: false })
    showModal.value = true
}

const close = () => (showModal.value = false)

// PHP can't read files sent with PATCH, so updates are POST + _method spoofing.
const submit = () => {
    const options = { forceFormData: true, preserveScroll: true, onSuccess: close }

    if (editing.value) {
        form.transform((data) => ({ ...data, _method: 'patch' })).post(`/admin/training/${editing.value.id}`, options)
    } else {
        form.transform((data) => data).post('/admin/training', options)
    }
}

const remove = (training) => {
    if (confirm(`Remove "${training.title}"? This also deletes its image.`)) {
        router.delete(`/admin/training/${training.id}`, { preserveScroll: true })
    }
}

// Whole row opens the editor; real buttons inside keep their own behaviour.
const onRowClick = (event, training) => {
    if (event.target.closest('a, button')) return
    openEdit(training)
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
    <Head title="Training" />

    <AdminLayout title="Training">
        <template #actions>
            <div class="flex items-center gap-2">
                <button
                    type="button"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-primary px-4 py-2 text-sm font-medium text-white transition hover:bg-primary-dark focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/40 focus-visible:ring-offset-2 active:scale-[0.98]"
                    @click="openCreate"
                >
                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
                        <path d="M10 4v12M4 10h12" />
                    </svg>
                    Add training
                </button>
            </div>
        </template>

        <!-- Scope note -->
        <div class="mb-4 flex gap-3 rounded-xl border border-neutral-text/10 bg-secondary/50 px-4 py-3 text-sm text-neutral-text/75">
            <svg class="mt-0.5 h-4 w-4 shrink-0 text-primary-dark" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <circle cx="10" cy="10" r="7.5" /><path d="M10 9.25v4M10 6.5h.01" />
            </svg>
            <p>
                This manages the content shown on the public Training page only. Enrollment, payment and hosting are not part of it.
            </p>
        </div>

        <div class="overflow-hidden rounded-xl border border-neutral-text/10 bg-white">
            <div v-if="trainings.length === 0" class="px-5 py-14 text-center">
                <p class="font-heading font-medium text-neutral-text">No training entries yet</p>
                <p class="mt-1 text-sm text-neutral-text/55">Add your first one to show it on the public page.</p>
                <button
                    type="button"
                    class="mt-4 rounded-lg bg-primary px-4 py-2 text-sm font-medium text-white transition hover:bg-primary-dark focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/40 focus-visible:ring-offset-2 active:scale-[0.98]"
                    @click="openCreate"
                >
                    Add training
                </button>
            </div>

            <div v-else class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-neutral-text/10 bg-neutral-bg text-xs text-neutral-text/55">
                    <tr>
                        <th class="px-5 py-3 font-medium">Training</th>
                        <th class="px-5 py-3 font-medium">Description</th>
                        <th class="px-5 py-3 text-right font-medium">Actions</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-text/[0.07]">
                    <tr
                        v-for="training in trainings"
                        :key="training.id"
                        class="group cursor-pointer transition-colors duration-150 hover:bg-primary-light"
                        @click="onRowClick($event, training)"
                    >
                        <td class="px-5 py-3.5">
                            <div class="flex items-center gap-3">
                                <img
                                    v-if="training.image_url"
                                    :src="training.image_url"
                                    alt=""
                                    class="h-12 w-12 shrink-0 rounded-lg border border-neutral-text/10 bg-neutral-bg object-cover"
                                    loading="lazy"
                                />
                                <div v-else class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg border border-neutral-text/10 bg-neutral-bg text-neutral-text/30">
                                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <rect x="3" y="4" width="14" height="12" rx="2" /><circle cx="7.5" cy="8.5" r="1.25" /><path d="m17 13-4-4-7 7" />
                                    </svg>
                                </div>
                                <span class="font-medium text-neutral-text">{{ training.title }}</span>
                            </div>
                        </td>
                        <td class="max-w-md px-5 py-3.5 text-neutral-text/65">
                            <p class="line-clamp-2">{{ training.description || '-' }}</p>
                        </td>
                        <td class="px-5 py-3.5">
                            <div class="flex items-center justify-end gap-2">
                                <button
                                    type="button"
                                    :class="[ghostBtn, 'border-neutral-text/15 bg-white text-neutral-text group-hover:border-primary group-hover:bg-primary group-hover:text-white focus-visible:ring-primary/40']"
                                    @click="openEdit(training)"
                                >
                                    Edit
                                </button>
                                <button
                                    type="button"
                                    :class="[ghostBtn, 'border-transparent text-neutral-text/55 hover:border-accent-light/60 hover:bg-accent-light/10 hover:text-accent focus-visible:ring-accent-light/40']"
                                    @click="remove(training)"
                                >
                                    Remove
                                </button>
                            </div>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <AdminModal :show="showModal" :title="editing ? 'Edit training' : 'Add training'" @close="close">
            <form class="space-y-5" @submit.prevent="submit">
                <ImageField :form="form" :current-url="editing?.image_url ?? null" />

                <div>
                    <label for="tr-title" class="mb-1.5 block text-sm font-medium text-neutral-text">Title</label>
                    <input id="tr-title" v-model="form.title" type="text" :class="inputClass(form.errors.title)" />
                    <p v-if="form.errors.title" class="mt-1.5 text-xs font-medium text-accent">{{ form.errors.title }}</p>
                </div>

                <div>
                    <label for="tr-desc" class="mb-1.5 block text-sm font-medium text-neutral-text">Description</label>
                    <textarea
                        id="tr-desc"
                        v-model="form.description"
                        rows="6"
                        :class="[inputClass(form.errors.description), 'leading-relaxed']"
                    />
                    <p v-if="form.errors.description" class="mt-1.5 text-xs font-medium text-accent">{{ form.errors.description }}</p>
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
                        {{ form.processing ? 'Saving...' : editing ? 'Save changes' : 'Add training' }}
                    </button>
                </div>
            </form>
        </AdminModal>
    </AdminLayout>
</template>
