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

// Whole row opens the editor; real buttons inside keep their own behaviour.
const onRowClick = (event, point) => {
    if (event.target.closest('a, button')) return
    openEdit(point)
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
    <Head title="Pickup points" />

    <AdminLayout title="Pickup points">
        <template #actions>
            <button
                type="button"
                class="inline-flex items-center gap-1.5 rounded-lg bg-primary px-4 py-2 text-sm font-medium text-white transition hover:bg-primary-dark focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/40 focus-visible:ring-offset-2 active:scale-[0.98]"
                @click="openCreate"
            >
                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
                    <path d="M10 4v12M4 10h12" />
                </svg>
                Add pickup point
            </button>
        </template>

        <div class="overflow-hidden rounded-xl border border-neutral-text/10 bg-white">
            <div v-if="pickupPoints.length === 0" class="px-5 py-14 text-center">
                <span class="mx-auto mb-3 flex h-11 w-11 items-center justify-center rounded-xl bg-secondary text-primary-dark">
                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M10 17s5-4.35 5-8.5a5 5 0 0 0-10 0C5 12.65 10 17 10 17Z" /><circle cx="10" cy="8.5" r="1.75" />
                    </svg>
                </span>
                <p class="font-heading font-medium text-neutral-text">No pickup points yet</p>
                <p class="mx-auto mt-1 max-w-sm text-sm text-neutral-text/55">
                    Customers need at least one active point to choose pickup at checkout.
                </p>
                <button
                    type="button"
                    class="mt-4 rounded-lg bg-primary px-4 py-2 text-sm font-medium text-white transition hover:bg-primary-dark focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/40 focus-visible:ring-offset-2 active:scale-[0.98]"
                    @click="openCreate"
                >
                    Add pickup point
                </button>
            </div>

            <div v-else class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-neutral-text/10 bg-neutral-bg text-xs text-neutral-text/55">
                    <tr>
                        <th class="px-5 py-3 font-medium">Name</th>
                        <th class="px-5 py-3 font-medium">Address</th>
                        <th class="px-5 py-3 font-medium">Available at checkout</th>
                        <th class="px-5 py-3 text-right font-medium">Orders</th>
                        <th class="px-5 py-3 text-right font-medium">Actions</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-text/[0.07]">
                    <tr
                        v-for="point in pickupPoints"
                        :key="point.id"
                        class="group cursor-pointer transition-colors duration-150 hover:bg-primary-light"
                        @click="onRowClick($event, point)"
                    >
                        <td class="px-5 py-3.5 font-medium text-neutral-text">{{ point.name }}</td>
                        <td class="max-w-xs px-5 py-3.5 text-neutral-text/65">{{ point.address }}</td>
                        <td class="px-5 py-3.5">
                            <!-- One-tap switch replaces the separate Activate / Deactivate link -->
                            <div class="flex items-center gap-2.5">
                                <button
                                    type="button"
                                    role="switch"
                                    :aria-checked="point.is_active"
                                    :aria-label="point.is_active ? `Deactivate ${point.name}` : `Activate ${point.name}`"
                                    class="relative inline-flex h-5 w-9 shrink-0 items-center rounded-full transition-colors duration-200 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/40 focus-visible:ring-offset-2"
                                    :class="point.is_active ? 'bg-primary' : 'bg-neutral-text/20'"
                                    @click="toggleActive(point)"
                                >
                                        <span
                                            class="inline-block h-4 w-4 rounded-full bg-white shadow-sm transition-transform duration-200"
                                            :class="point.is_active ? 'translate-x-[18px]' : 'translate-x-0.5'"
                                        />
                                </button>
                                <span
                                    class="text-xs font-medium"
                                    :class="point.is_active ? 'text-primary-dark' : 'text-neutral-text/55'"
                                >
                                        {{ point.is_active ? 'Active' : 'Inactive' }}
                                    </span>
                            </div>
                        </td>
                        <td class="px-5 py-3.5 text-right tabular-nums text-neutral-text/70">{{ point.orders_count }}</td>
                        <td class="px-5 py-3.5">
                            <div class="flex items-center justify-end gap-2">
                                <button
                                    type="button"
                                    :class="[ghostBtn, 'border-neutral-text/15 bg-white text-neutral-text group-hover:border-primary group-hover:bg-primary group-hover:text-white focus-visible:ring-primary/40']"
                                    @click="openEdit(point)"
                                >
                                    Edit
                                </button>
                                <button
                                    v-if="point.orders_count === 0"
                                    type="button"
                                    :class="[ghostBtn, 'border-transparent text-neutral-text/55 hover:border-accent-light/60 hover:bg-accent-light/10 hover:text-accent focus-visible:ring-accent-light/40']"
                                    @click="remove(point)"
                                >
                                    Delete
                                </button>
                            </div>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <AdminModal :show="showModal" :title="editing ? 'Edit pickup point' : 'Add pickup point'" @close="close">
            <form class="space-y-5" @submit.prevent="submit">
                <div>
                    <label for="pp-name" class="mb-1.5 block text-sm font-medium text-neutral-text">Name</label>
                    <input id="pp-name" v-model="form.name" type="text" placeholder="Utuhu Branch" :class="inputClass(form.errors.name)" />
                    <p v-if="form.errors.name" class="mt-1.5 text-xs font-medium text-accent">{{ form.errors.name }}</p>
                </div>

                <div>
                    <label for="pp-address" class="mb-1.5 block text-sm font-medium text-neutral-text">Address</label>
                    <textarea id="pp-address" v-model="form.address" rows="3" :class="inputClass(form.errors.address)" />
                    <p v-if="form.errors.address" class="mt-1.5 text-xs font-medium text-accent">{{ form.errors.address }}</p>
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
                        <span class="block font-medium text-neutral-text">Available at checkout</span>
                        <span class="block text-xs text-neutral-text/55">
                            Turn off to hide it from customers without affecting past orders.
                        </span>
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
                        {{ form.processing ? 'Saving...' : editing ? 'Save changes' : 'Add pickup point' }}
                    </button>
                </div>
            </form>
        </AdminModal>
    </AdminLayout>
</template>
