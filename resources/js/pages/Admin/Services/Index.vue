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

// Whole row opens the editor; real buttons inside keep their own behaviour.
const onRowClick = (event, service) => {
    if (event.target.closest('a, button')) return
    openEdit(service)
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
    <Head title="Services" />

    <AdminLayout title="Services">
        <template #actions>
            <button
                type="button"
                class="inline-flex items-center gap-1.5 rounded-lg bg-primary px-4 py-2 text-sm font-medium text-white transition hover:bg-primary-dark focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/40 focus-visible:ring-offset-2 active:scale-[0.98]"
                @click="openCreate"
            >
                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
                    <path d="M10 4v12M4 10h12" />
                </svg>
                Add service
            </button>
        </template>

        <div class="overflow-hidden rounded-xl border border-neutral-text/10 bg-white">
            <div v-if="services.length === 0" class="px-5 py-14 text-center">
                <p class="font-heading font-medium text-neutral-text">No services yet</p>
                <p class="mx-auto mt-1 max-w-sm text-sm text-neutral-text/55">
                    Add the consultations and treatments you offer.
                </p>
                <button
                    type="button"
                    class="mt-4 rounded-lg bg-primary px-4 py-2 text-sm font-medium text-white transition hover:bg-primary-dark focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/40 focus-visible:ring-offset-2 active:scale-[0.98]"
                    @click="openCreate"
                >
                    Add service
                </button>
            </div>

            <div v-else class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-neutral-text/10 bg-neutral-bg text-xs text-neutral-text/55">
                    <tr>
                        <th class="px-5 py-3 font-medium">Service</th>
                        <th class="px-5 py-3 font-medium">WhatsApp message</th>
                        <th class="px-5 py-3 font-medium">Status</th>
                        <th class="px-5 py-3 text-right font-medium">Actions</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-text/[0.07]">
                    <tr
                        v-for="service in services"
                        :key="service.id"
                        class="group cursor-pointer transition-colors duration-150 hover:bg-primary-light"
                        @click="onRowClick($event, service)"
                    >
                        <td class="px-5 py-3.5">
                            <div class="flex items-center gap-3">
                                <img
                                    v-if="service.image_url"
                                    :src="service.image_url"
                                    alt=""
                                    class="h-12 w-12 shrink-0 rounded-lg border border-neutral-text/10 bg-neutral-bg object-cover"
                                    loading="lazy"
                                />
                                <div v-else class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg border border-neutral-text/10 bg-neutral-bg text-neutral-text/30">
                                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <rect x="3" y="4" width="14" height="12" rx="2" /><circle cx="7.5" cy="8.5" r="1.25" /><path d="m17 13-4-4-7 7" />
                                    </svg>
                                </div>
                                <div class="min-w-0">
                                    <p class="font-medium text-neutral-text">{{ service.name }}</p>
                                    <p v-if="service.description" class="line-clamp-1 max-w-xs text-xs text-neutral-text/50">
                                        {{ service.description }}
                                    </p>
                                </div>
                            </div>
                        </td>
                        <td class="max-w-xs px-5 py-3.5">
                            <!-- Styled like a chat bubble: it is the pre-filled WhatsApp text -->
                            <p class="line-clamp-2 inline-block rounded-xl rounded-tl-sm bg-secondary/60 px-3 py-1.5 text-xs leading-relaxed text-neutral-text/80">
                                {{ service.whatsapp_message }}
                            </p>
                        </td>
                        <td class="px-5 py-3.5">
                                <span
                                    class="inline-flex items-center gap-1.5 whitespace-nowrap rounded-full px-2.5 py-1 text-xs font-medium"
                                    :class="service.is_active ? 'bg-primary/10 text-primary-dark' : 'bg-neutral-text/10 text-neutral-text/70'"
                                >
                                    <span class="h-1.5 w-1.5 rounded-full" :class="service.is_active ? 'bg-primary' : 'bg-neutral-text/40'" />
                                    {{ service.is_active ? 'Visible' : 'Hidden' }}
                                </span>
                        </td>
                        <td class="px-5 py-3.5">
                            <div class="flex items-center justify-end gap-2">
                                <button
                                    type="button"
                                    :class="[ghostBtn, 'border-neutral-text/15 bg-white text-neutral-text group-hover:border-primary group-hover:bg-primary group-hover:text-white focus-visible:ring-primary/40']"
                                    @click="openEdit(service)"
                                >
                                    Edit
                                </button>
                                <button
                                    type="button"
                                    :class="[ghostBtn, 'border-transparent text-neutral-text/55 hover:border-accent-light/60 hover:bg-accent-light/10 hover:text-accent focus-visible:ring-accent-light/40']"
                                    @click="remove(service)"
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

        <AdminModal :show="showModal" :title="editing ? 'Edit service' : 'Add service'" @close="close">
            <form class="space-y-5" @submit.prevent="submit">
                <ImageField :form="form" :current-url="editing?.image_url ?? null" />

                <div>
                    <label for="svc-name" class="mb-1.5 block text-sm font-medium text-neutral-text">Name</label>
                    <input id="svc-name" v-model="form.name" type="text" :class="inputClass(form.errors.name)" />
                    <p v-if="form.errors.name" class="mt-1.5 text-xs font-medium text-accent">{{ form.errors.name }}</p>
                </div>

                <div>
                    <label for="svc-desc" class="mb-1.5 block text-sm font-medium text-neutral-text">Description</label>
                    <textarea id="svc-desc" v-model="form.description" rows="3" :class="inputClass(form.errors.description)" />
                    <p v-if="form.errors.description" class="mt-1.5 text-xs font-medium text-accent">{{ form.errors.description }}</p>
                </div>

                <div>
                    <label for="svc-wa" class="mb-1.5 block text-sm font-medium text-neutral-text">WhatsApp message</label>
                    <input id="svc-wa" v-model="form.whatsapp_message" type="text" :class="inputClass(form.errors.whatsapp_message)" />
                    <p class="mt-1.5 text-xs text-neutral-text/50">
                        The message pre-filled when a customer taps this service and opens WhatsApp.
                    </p>
                    <p v-if="form.errors.whatsapp_message" class="mt-1.5 text-xs font-medium text-accent">
                        {{ form.errors.whatsapp_message }}
                    </p>
                </div>

                <label
                    class="flex cursor-pointer items-start gap-3 rounded-xl border border-neutral-text/10 bg-neutral-bg p-3 text-sm transition hover:border-primary/40 hover:bg-primary-light"
                >
                    <input
                        v-model="form.is_active"
                        type="checkbox"
                        class="mt-0.5 h-4 w-4 rounded border-neutral-text/30 text-primary focus:ring-primary/30"
                    />
                    <span>
                        <span class="block font-medium text-neutral-text">Show on the public Services page</span>
                        <span class="block text-xs text-neutral-text/55">Turn off to hide it without deleting it.</span>
                    </span>
                </label>

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
                        {{ form.processing ? 'Saving...' : editing ? 'Save changes' : 'Add service' }}
                    </button>
                </div>
            </form>
        </AdminModal>
    </AdminLayout>
</template>
