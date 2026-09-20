<script setup>
import { ref } from 'vue'
import { Head, router, useForm } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import AdminModal from '@/Components/Admin/AdminModal.vue'

defineProps({
    pickupPoints: { type: Array, default: () => [] },
})

const showModal = ref(false)
const editing = ref(null)

const form = useForm({ name: '', address: '', is_active: true })

const load = (values) => {
    form.defaults(values)
    form.reset()
    form.clearErrors()
}

const openCreate = () => {
    editing.value = null
    load({ name: '', address: '', is_active: true })
    showModal.value = true
}

const openEdit = (point) => {
    editing.value = point
    load({ name: point.name, address: point.address, is_active: point.is_active })
    showModal.value = true
}

const close = () => (showModal.value = false)

const submit = () => {
    const options = { preserveScroll: true, onSuccess: close }

    if (editing.value) {
        form.patch(`/admin/pickup-points/${editing.value.id}`, options)
    } else {
        form.post('/admin/pickup-points', options)
    }
}

// Deactivating removes it from the checkout dropdown without touching past orders.
const toggleActive = (point) =>
    router.patch(
        `/admin/pickup-points/${point.id}`,
        { name: point.name, address: point.address, is_active: !point.is_active },
        { preserveScroll: true },
    )

const remove = (point) => {
    if (confirm(`Delete "${point.name}"?`)) {
        router.delete(`/admin/pickup-points/${point.id}`, { preserveScroll: true })
    }
}

const input =
    'w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500'
</script>

<template>
    <Head title="Pickup points" />

    <AdminLayout title="Pickup points">
        <template #actions>
            <button
                type="button"
                class="rounded-md bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700"
                @click="openCreate"
            >
                Add pickup point
            </button>
        </template>

        <div class="rounded-lg border border-gray-200 bg-white">
            <p v-if="pickupPoints.length === 0" class="px-5 py-10 text-center text-sm text-gray-500">
                No pickup points yet. Customers need at least one active point to choose pickup at checkout.
            </p>

            <div v-else class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-gray-200 text-xs text-gray-500">
                    <tr>
                        <th class="px-5 py-3 font-medium">Name</th>
                        <th class="px-5 py-3 font-medium">Address</th>
                        <th class="px-5 py-3 font-medium">Status</th>
                        <th class="px-5 py-3 font-medium">Orders</th>
                        <th class="px-5 py-3 text-right font-medium">Actions</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                    <tr v-for="point in pickupPoints" :key="point.id" class="hover:bg-gray-50">
                        <td class="px-5 py-3 font-medium">{{ point.name }}</td>
                        <td class="max-w-xs px-5 py-3 text-gray-600">{{ point.address }}</td>
                        <td class="px-5 py-3">
                                <span
                                    class="rounded-full px-2.5 py-1 text-xs font-medium"
                                    :class="point.is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-600'"
                                >
                                    {{ point.is_active ? 'Active' : 'Inactive' }}
                                </span>
                        </td>
                        <td class="px-5 py-3">{{ point.orders_count }}</td>
                        <td class="px-5 py-3 text-right">
                            <button type="button" class="mr-3 text-emerald-700 hover:underline" @click="openEdit(point)">
                                Edit
                            </button>
                            <button type="button" class="mr-3 text-gray-600 hover:underline" @click="toggleActive(point)">
                                {{ point.is_active ? 'Deactivate' : 'Activate' }}
                            </button>
                            <button
                                v-if="point.orders_count === 0"
                                type="button"
                                class="text-red-600 hover:underline"
                                @click="remove(point)"
                            >
                                Delete
                            </button>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <AdminModal :show="showModal" :title="editing ? 'Edit pickup point' : 'Add pickup point'" @close="close">
            <form class="space-y-4" @submit.prevent="submit">
                <div>
                    <label class="mb-1 block text-sm font-medium">Name</label>
                    <input v-model="form.name" type="text" placeholder="Utuhu Branch" :class="input" />
                    <p v-if="form.errors.name" class="mt-1 text-xs text-red-600">{{ form.errors.name }}</p>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium">Address</label>
                    <textarea v-model="form.address" rows="3" :class="input" />
                    <p v-if="form.errors.address" class="mt-1 text-xs text-red-600">{{ form.errors.address }}</p>
                </div>

                <label class="flex cursor-pointer items-center gap-2 text-sm">
                    <input
                        v-model="form.is_active"
                        type="checkbox"
                        class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500"
                    />
                    Available at checkout
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
                        {{ form.processing ? 'Saving...' : editing ? 'Save changes' : 'Add pickup point' }}
                    </button>
                </div>
            </form>
        </AdminModal>
    </AdminLayout>
</template>
