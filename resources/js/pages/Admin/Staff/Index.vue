<script setup>
import { ref } from 'vue'
import { Head, router, useForm } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import AdminModal from '@/Components/Admin/AdminModal.vue'

const props = defineProps({
    staff: { type: Array, default: () => [] },
    assignableRoles: { type: Array, default: () => [] },
})

const roleLabels = { super_admin: 'Super admin', owner: 'Owner', admin: 'Admin', staff: 'Staff' }
const roleStyles = {
    super_admin: 'bg-purple-50 text-purple-700',
    owner: 'bg-indigo-50 text-indigo-700',
    admin: 'bg-emerald-50 text-emerald-700',
    staff: 'bg-gray-100 text-gray-700',
}
const roleLabel = (role) => roleLabels[role] ?? role

const roleHelp = {
    staff: 'Can view orders and move them through processing and delivery. Cannot cancel orders or edit the catalog.',
    admin: 'Can manage products, categories, services, training, contact channels and pickup points, and cancel orders.',
    owner: 'Full business access, including staff, reports and the activity log.',
}

const showModal = ref(false)
const editing = ref(null) // null = inviting someone new

const form = useForm({ name: '', email: '', role: 'staff' })

const load = (values) => {
    form.defaults(values)
    form.reset()
    form.clearErrors()
}

const openCreate = () => {
    editing.value = null
    load({ name: '', email: '', role: props.assignableRoles.includes('staff') ? 'staff' : props.assignableRoles[0] })
    showModal.value = true
}

const openEdit = (member) => {
    editing.value = member
    load({ name: member.name, email: member.email, role: member.role })
    showModal.value = true
}

const close = () => (showModal.value = false)

const submit = () => {
    const options = { preserveScroll: true, onSuccess: close }

    if (editing.value) {
        form.patch(`/admin/staff/${editing.value.id}`, options)
    } else {
        form.post('/admin/staff', options)
    }
}

const resendInvite = (member) =>
    router.post(`/admin/staff/${member.id}/invite`, {}, { preserveScroll: true })

const revoke = (member) => {
    if (confirm(`Remove ${member.name}'s admin access? They keep a customer account but can no longer use the admin area.`)) {
        router.delete(`/admin/staff/${member.id}`, { preserveScroll: true })
    }
}

const input =
    'w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500 disabled:bg-gray-50 disabled:text-gray-500'
</script>

<template>
    <Head title="Staff" />

    <AdminLayout title="Staff">
        <template #actions>
            <button
                type="button"
                class="rounded-md bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700"
                @click="openCreate"
            >
                Add staff member
            </button>
        </template>

        <p class="mb-4 text-sm text-gray-500">
            New staff get an email with a link to set their own password. Everything they do is recorded in the activity log.
        </p>

        <div class="rounded-lg border border-gray-200 bg-white">
            <p v-if="staff.length === 0" class="px-5 py-10 text-center text-sm text-gray-500">No staff accounts yet.</p>

            <div v-else class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-gray-200 text-xs text-gray-500">
                    <tr>
                        <th class="px-5 py-3 font-medium">Name</th>
                        <th class="px-5 py-3 font-medium">Email</th>
                        <th class="px-5 py-3 font-medium">Role</th>
                        <th class="px-5 py-3 text-right font-medium">Actions</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                    <tr v-for="member in staff" :key="member.id" class="hover:bg-gray-50">
                        <td class="px-5 py-3 font-medium">
                            {{ member.name }}
                            <span v-if="member.is_me" class="ml-1 text-xs font-normal text-gray-400">(you)</span>
                        </td>
                        <td class="px-5 py-3 text-gray-600">{{ member.email }}</td>
                        <td class="px-5 py-3">
                                <span
                                    class="rounded-full px-2.5 py-1 text-xs font-medium"
                                    :class="roleStyles[member.role] ?? 'bg-gray-100 text-gray-700'"
                                >
                                    {{ roleLabel(member.role) }}
                                </span>
                        </td>
                        <td class="px-5 py-3 text-right">
                            <template v-if="member.can_manage">
                                <button type="button" class="mr-3 text-emerald-700 hover:underline" @click="openEdit(member)">
                                    Edit
                                </button>
                                <button type="button" class="mr-3 text-gray-600 hover:underline" @click="resendInvite(member)">
                                    Resend invite
                                </button>
                                <button type="button" class="text-red-600 hover:underline" @click="revoke(member)">
                                    Remove access
                                </button>
                            </template>
                            <span v-else class="text-xs text-gray-300">-</span>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <AdminModal :show="showModal" :title="editing ? 'Edit staff member' : 'Add staff member'" @close="close">
            <form class="space-y-4" @submit.prevent="submit">
                <div>
                    <label class="mb-1 block text-sm font-medium">Full name</label>
                    <input v-model="form.name" type="text" :class="input" />
                    <p v-if="form.errors.name" class="mt-1 text-xs text-red-600">{{ form.errors.name }}</p>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium">Email</label>
                    <input v-model="form.email" type="email" :disabled="!!editing" :class="input" />
                    <p v-if="editing" class="mt-1 text-xs text-gray-400">The email address cannot be changed.</p>
                    <p v-if="form.errors.email" class="mt-1 text-xs text-red-600">{{ form.errors.email }}</p>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium">Role</label>
                    <select v-model="form.role" :class="input">
                        <option v-for="role in assignableRoles" :key="role" :value="role">{{ roleLabel(role) }}</option>
                    </select>
                    <p v-if="roleHelp[form.role]" class="mt-1 text-xs text-gray-400">{{ roleHelp[form.role] }}</p>
                    <p v-if="form.errors.role" class="mt-1 text-xs text-red-600">{{ form.errors.role }}</p>
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
                        {{ form.processing ? 'Saving...' : editing ? 'Save changes' : 'Create and send invite' }}
                    </button>
                </div>
            </form>
        </AdminModal>
    </AdminLayout>
</template>
