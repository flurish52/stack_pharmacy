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

const input =
    'w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500'
</script>

<template>
    <Head title="Training" />

    <AdminLayout title="Training">
        <template #actions>
            <Link href="/training" class="mr-4 text-sm text-emerald-700 hover:underline">View public page</Link>
            <button
                type="button"
                class="rounded-md bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700"
                @click="openCreate"
            >
                Add training
            </button>
        </template>

        <p class="mb-4 text-sm text-gray-500">
            This manages the content shown on the public Training page only. Enrollment, payment and hosting are not part of it.
        </p>

        <div class="rounded-lg border border-gray-200 bg-white">
            <p v-if="trainings.length === 0" class="px-5 py-10 text-center text-sm text-gray-500">
                No training entries yet. Add your first one.
            </p>

            <div v-else class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-gray-200 text-xs text-gray-500">
                    <tr>
                        <th class="px-5 py-3 font-medium">Training</th>
                        <th class="px-5 py-3 font-medium">Description</th>
                        <th class="px-5 py-3 text-right font-medium">Actions</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                    <tr v-for="training in trainings" :key="training.id" class="hover:bg-gray-50">
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-3">
                                <img
                                    v-if="training.image_url"
                                    :src="training.image_url"
                                    alt=""
                                    class="h-12 w-12 shrink-0 rounded-md bg-gray-100 object-cover"
                                    loading="lazy"
                                />
                                <div v-else class="h-12 w-12 shrink-0 rounded-md bg-gray-100" />
                                <span class="font-medium">{{ training.title }}</span>
                            </div>
                        </td>
                        <td class="max-w-md px-5 py-3 text-gray-600">
                            <p class="line-clamp-2">{{ training.description || '-' }}</p>
                        </td>
                        <td class="px-5 py-3 text-right">
                            <button type="button" class="mr-3 text-emerald-700 hover:underline" @click="openEdit(training)">
                                Edit
                            </button>
                            <button type="button" class="text-red-600 hover:underline" @click="remove(training)">
                                Remove
                            </button>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <AdminModal :show="showModal" :title="editing ? 'Edit training' : 'Add training'" @close="close">
            <form class="space-y-4" @submit.prevent="submit">
                <ImageField :form="form" :current-url="editing?.image_url ?? null" />

                <div>
                    <label class="mb-1 block text-sm font-medium">Title</label>
                    <input v-model="form.title" type="text" :class="input" />
                    <p v-if="form.errors.title" class="mt-1 text-xs text-red-600">{{ form.errors.title }}</p>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium">Description</label>
                    <textarea v-model="form.description" rows="6" :class="input" />
                    <p v-if="form.errors.description" class="mt-1 text-xs text-red-600">{{ form.errors.description }}</p>
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
                        {{ form.processing ? 'Saving...' : editing ? 'Save changes' : 'Add training' }}
                    </button>
                </div>
            </form>
        </AdminModal>
    </AdminLayout>
</template>
