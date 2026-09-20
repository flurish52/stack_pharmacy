<script setup>
import { ref } from 'vue'
import { Head, router, useForm } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import AdminModal from '@/Components/Admin/AdminModal.vue'
import ImageField from '@/Components/Admin/ImageField.vue'

defineProps({
    services: { type: Array, default: () => [] },
})

const showModal = ref(false)
const editing = ref(null) // null = creating

const form = useForm({
    name: '',
    description: '',
    whatsapp_message: '',
    is_active: true,
    image: null,
    remove_image: false,
})

const load = (values) => {
    form.defaults(values)
    form.reset()
    form.clearErrors()
}

const openCreate = () => {
    editing.value = null
    load({ name: '', description: '', whatsapp_message: '', is_active: true, image: null, remove_image: false })
    showModal.value = true
}

const openEdit = (service) => {
    editing.value = service
    load({
        name: service.name,
        description: service.description ?? '',
        whatsapp_message: service.whatsapp_message,
        is_active: service.is_active,
        image: null,
        remove_image: false,
    })
    showModal.value = true
}

const close = () => (showModal.value = false)

// PHP can't read files sent with PATCH, so updates are POST + _method spoofing.
const submit = () => {
    const options = { forceFormData: true, preserveScroll: true, onSuccess: close }

    if (editing.value) {
        form.transform((data) => ({ ...data, _method: 'patch' })).post(`/admin/services/${editing.value.id}`, options)
    } else {
        form.transform((data) => data).post('/admin/services', options)
    }
}

const remove = (service) => {
    if (confirm(`Remove "${service.name}"? This also deletes its image.`)) {
        router.delete(`/admin/services/${service.id}`, { preserveScroll: true })
    }
}

const input =
    'w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500'
</script>

<template>
    <Head title="Services" />

    <AdminLayout title="Services">
        <template #actions>
            <button
                type="button"
                class="rounded-md bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700"
                @click="openCreate"
            >
                Add service
            </button>
        </template>

        <div class="rounded-lg border border-gray-200 bg-white">
            <p v-if="services.length === 0" class="px-5 py-10 text-center text-sm text-gray-500">
                No services yet. Add the consultations and treatments you offer.
            </p>

            <div v-else class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-gray-200 text-xs text-gray-500">
                    <tr>
                        <th class="px-5 py-3 font-medium">Service</th>
                        <th class="px-5 py-3 font-medium">WhatsApp message</th>
                        <th class="px-5 py-3 font-medium">Status</th>
                        <th class="px-5 py-3 text-right font-medium">Actions</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                    <tr v-for="service in services" :key="service.id" class="hover:bg-gray-50">
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-3">
                                <img
                                    v-if="service.image_url"
                                    :src="service.image_url"
                                    alt=""
                                    class="h-12 w-12 shrink-0 rounded-md bg-gray-100 object-cover"
                                    loading="lazy"
                                />
                                <div v-else class="h-12 w-12 shrink-0 rounded-md bg-gray-100" />
                                <div>
                                    <p class="font-medium">{{ service.name }}</p>
                                    <p v-if="service.description" class="line-clamp-1 max-w-xs text-xs text-gray-400">
                                        {{ service.description }}
                                    </p>
                                </div>
                            </div>
                        </td>
                        <td class="max-w-xs px-5 py-3 text-gray-600">
                            <p class="line-clamp-2">{{ service.whatsapp_message }}</p>
                        </td>
                        <td class="px-5 py-3">
                                <span
                                    class="rounded-full px-2.5 py-1 text-xs font-medium"
                                    :class="service.is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-600'"
                                >
                                    {{ service.is_active ? 'Visible' : 'Hidden' }}
                                </span>
                        </td>
                        <td class="px-5 py-3 text-right">
                            <button type="button" class="mr-3 text-emerald-700 hover:underline" @click="openEdit(service)">
                                Edit
                            </button>
                            <button type="button" class="text-red-600 hover:underline" @click="remove(service)">
                                Remove
                            </button>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <AdminModal :show="showModal" :title="editing ? 'Edit service' : 'Add service'" @close="close">
            <form class="space-y-4" @submit.prevent="submit">
                <ImageField :form="form" :current-url="editing?.image_url ?? null" />

                <div>
                    <label class="mb-1 block text-sm font-medium">Name</label>
                    <input v-model="form.name" type="text" :class="input" />
                    <p v-if="form.errors.name" class="mt-1 text-xs text-red-600">{{ form.errors.name }}</p>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium">Description</label>
                    <textarea v-model="form.description" rows="3" :class="input" />
                    <p v-if="form.errors.description" class="mt-1 text-xs text-red-600">{{ form.errors.description }}</p>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium">WhatsApp message</label>
                    <input v-model="form.whatsapp_message" type="text" :class="input" />
                    <p class="mt-1 text-xs text-gray-400">
                        The message pre-filled when a customer taps this service and opens WhatsApp.
                    </p>
                    <p v-if="form.errors.whatsapp_message" class="mt-1 text-xs text-red-600">
                        {{ form.errors.whatsapp_message }}
                    </p>
                </div>

                <label class="flex cursor-pointer items-center gap-2 text-sm">
                    <input
                        v-model="form.is_active"
                        type="checkbox"
                        class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500"
                    />
                    Show on the public Services page
                </label>

                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" class="rounded-md px-4 py-2 text-sm text-gray-600 hover:bg-gray-100" @click="close">
                        Cancel
                    </button>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="rounded-md bg-emerald-600 px-5 py-2 text-sm font-medium text-white hover:bg-emerald-700 disabled:opacity-50"
                    >
                        {{ form.processing ? 'Saving...' : editing ? 'Save changes' : 'Add service' }}
                    </button>
                </div>
            </form>
        </AdminModal>
    </AdminLayout>
</template>
