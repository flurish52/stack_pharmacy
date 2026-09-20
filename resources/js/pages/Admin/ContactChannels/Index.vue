<script setup>
import { ref } from 'vue'
import { Head, router, useForm } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import AdminModal from '@/Components/Admin/AdminModal.vue'

const props = defineProps({
    channels: { type: Array, default: () => [] }, // already sorted by display_order
})

const platforms = [
    { value: 'whatsapp', label: 'WhatsApp', handleHint: '0801 234 5678' },
    { value: 'phone', label: 'Phone', handleHint: '08012345678' },
    { value: 'email', label: 'Email', handleHint: 'hello@stackpharmacy.com.ng' },
    { value: 'instagram', label: 'Instagram', handleHint: '@stackpharmacy' },
    { value: 'facebook', label: 'Facebook', handleHint: 'Stack Pharmacy' },
    { value: 'tiktok', label: 'TikTok', handleHint: '@stackpharmacy' },
]

const platformLabel = (value) => platforms.find((p) => p.value === value)?.label ?? value

const showModal = ref(false)
const editing = ref(null)

const form = useForm({ platform: 'whatsapp', handle: '', url: '', display_order: 1 })

const nextOrder = () => Math.max(0, ...props.channels.map((c) => Number(c.display_order))) + 1

const load = (values) => {
    form.defaults(values)
    form.reset()
    form.clearErrors()
}

const openCreate = () => {
    editing.value = null
    load({ platform: 'whatsapp', handle: '', url: '', display_order: nextOrder() })
    showModal.value = true
}

const openEdit = (channel) => {
    editing.value = channel
    load({
        platform: channel.platform,
        handle: channel.handle,
        url: channel.url ?? '',
        display_order: channel.display_order,
    })
    showModal.value = true
}

const close = () => (showModal.value = false)

const submit = () => {
    const options = { preserveScroll: true, onSuccess: close }

    if (editing.value) {
        form.patch(`/admin/contact-channels/${editing.value.id}`, options)
    } else {
        form.post('/admin/contact-channels', options)
    }
}

const remove = (channel) => {
    if (confirm(`Remove the ${platformLabel(channel.platform)} channel "${channel.handle}"?`)) {
        router.delete(`/admin/contact-channels/${channel.id}`, { preserveScroll: true })
    }
}

const input =
    'w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500'
</script>

<template>
    <Head title="Contact channels" />

    <AdminLayout title="Contact channels">
        <template #actions>
            <button
                type="button"
                class="rounded-md bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700"
                @click="openCreate"
            >
                Add channel
            </button>
        </template>

        <p class="mb-4 text-sm text-gray-500">
            These appear on the public Contact page and in the storefront footer, lowest order number first.
        </p>

        <div class="rounded-lg border border-gray-200 bg-white">
            <p v-if="channels.length === 0" class="px-5 py-10 text-center text-sm text-gray-500">
                No contact channels yet. Add the ways customers can reach the pharmacy.
            </p>

            <div v-else class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-gray-200 text-xs text-gray-500">
                    <tr>
                        <th class="px-5 py-3 font-medium">Order</th>
                        <th class="px-5 py-3 font-medium">Platform</th>
                        <th class="px-5 py-3 font-medium">Handle</th>
                        <th class="px-5 py-3 font-medium">Link</th>
                        <th class="px-5 py-3 text-right font-medium">Actions</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                    <tr v-for="channel in channels" :key="channel.id" class="hover:bg-gray-50">
                        <td class="px-5 py-3 text-gray-500">{{ channel.display_order }}</td>
                        <td class="px-5 py-3 font-medium">{{ platformLabel(channel.platform) }}</td>
                        <td class="px-5 py-3">{{ channel.handle }}</td>
                        <td class="max-w-xs px-5 py-3 text-gray-500">
                            <a
                                v-if="channel.url"
                                :href="channel.url"
                                target="_blank"
                                rel="noopener"
                                class="block truncate text-emerald-700 hover:underline"
                            >
                                {{ channel.url }}
                            </a>
                            <span v-else class="text-gray-300">-</span>
                        </td>
                        <td class="px-5 py-3 text-right">
                            <button type="button" class="mr-3 text-emerald-700 hover:underline" @click="openEdit(channel)">
                                Edit
                            </button>
                            <button type="button" class="text-red-600 hover:underline" @click="remove(channel)">
                                Remove
                            </button>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <AdminModal :show="showModal" :title="editing ? 'Edit channel' : 'Add channel'" @close="close">
            <form class="space-y-4" @submit.prevent="submit">
                <div>
                    <label class="mb-1 block text-sm font-medium">Platform</label>
                    <select v-model="form.platform" :class="input">
                        <option v-for="p in platforms" :key="p.value" :value="p.value">{{ p.label }}</option>
                    </select>
                    <p v-if="form.errors.platform" class="mt-1 text-xs text-red-600">{{ form.errors.platform }}</p>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium">Handle</label>
                    <input
                        v-model="form.handle"
                        type="text"
                        :placeholder="platforms.find((p) => p.value === form.platform)?.handleHint"
                        :class="input"
                    />
                    <p class="mt-1 text-xs text-gray-400">What customers see: a number, email address or @username.</p>
                    <p v-if="form.errors.handle" class="mt-1 text-xs text-red-600">{{ form.errors.handle }}</p>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium">Link (optional)</label>
                    <input v-model="form.url" type="url" placeholder="https://" :class="input" />
                    <p class="mt-1 text-xs text-gray-400">
                        The full page link for social platforms. Leave blank for phone and email.
                    </p>
                    <p v-if="form.errors.url" class="mt-1 text-xs text-red-600">{{ form.errors.url }}</p>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium">Display order</label>
                    <input v-model="form.display_order" type="number" min="0" step="1" :class="input" />
                    <p class="mt-1 text-xs text-gray-400">Lowest number shows first.</p>
                    <p v-if="form.errors.display_order" class="mt-1 text-xs text-red-600">
                        {{ form.errors.display_order }}
                    </p>
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
                        {{ form.processing ? 'Saving...' : editing ? 'Save changes' : 'Add channel' }}
                    </button>
                </div>
            </form>
        </AdminModal>
    </AdminLayout>
</template>
