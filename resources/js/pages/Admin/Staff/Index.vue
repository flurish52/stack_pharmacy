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

// Palette-only role tones, darkest = most access.
const roleStyles = {
    super_admin: { pill: 'bg-primary-dark text-white', dot: 'bg-white' },
    owner: { pill: 'bg-primary text-white', dot: 'bg-white' },
    admin: { pill: 'bg-secondary text-primary-dark', dot: 'bg-primary-dark' },
    staff: { pill: 'bg-neutral-text/10 text-neutral-text/70', dot: 'bg-neutral-text/40' },
}
const fallbackRole = { pill: 'bg-neutral-text/10 text-neutral-text/70', dot: 'bg-neutral-text/40' }
const roleLabel = (role) => roleLabels[role] ?? role

const roleHelp = {
    staff: 'Can view orders and move them through processing and delivery. Cannot cancel orders or edit the catalog.',
    admin: 'Can manage products, categories, services, training, contact channels and pickup points, and cancel orders.',
    owner: 'Full business access, including staff, reports and the activity log.',
}

const initials = (name) =>
    (name ?? '')
        .split(' ')
        .filter(Boolean)
        .slice(0, 2)
        .map((part) => part[0].toUpperCase())
        .join('')

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

// Whole row opens the editor when you're allowed to manage that person.
const onRowClick = (event, member) => {
    if (!member.can_manage || event.target.closest('a, button')) return
    openEdit(member)
}

const inputBase =
    'w-full rounded-lg border bg-white px-3 py-2 text-sm text-neutral-text transition placeholder:text-neutral-text/40 focus:outline-none focus:ring-2 disabled:cursor-not-allowed disabled:bg-neutral-bg disabled:text-neutral-text/50'
const inputClass = (error) =>
    error
        ? `${inputBase} border-accent-light focus:border-accent-light focus:ring-accent-light/20`
        : `${inputBase} border-neutral-text/15 hover:border-neutral-text/30 focus:border-primary focus:ring-primary/20`

const ghostBtn =
    'inline-flex items-center gap-1 whitespace-nowrap rounded-lg border px-3 py-1.5 text-xs font-medium transition focus-visible:outline-none focus-visible:ring-2 active:scale-[0.98]'
</script>

<template>
    <Head title="Staff" />

    <AdminLayout title="Staff">
        <template #actions>
            <button
                type="button"
                class="inline-flex items-center gap-1.5 rounded-lg bg-primary px-4 py-2 text-sm font-medium text-white transition hover:bg-primary-dark focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/40 focus-visible:ring-offset-2 active:scale-[0.98]"
                @click="openCreate"
            >
                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
                    <path d="M10 4v12M4 10h12" />
                </svg>
                Add staff member
            </button>
        </template>

        <!-- Info note -->
        <div class="mb-4 flex gap-3 rounded-xl border border-neutral-text/10 bg-secondary/50 px-4 py-3 text-sm text-neutral-text/75">
            <svg class="mt-0.5 h-4 w-4 shrink-0 text-primary-dark" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <circle cx="10" cy="10" r="7.5" /><path d="M10 9.25v4M10 6.5h.01" />
            </svg>
            <p>
                New staff get an email with a link to set their own password. Everything they do is recorded in the activity log.
            </p>
        </div>

        <div class="overflow-hidden rounded-xl border border-neutral-text/10 bg-white">
            <div v-if="staff.length === 0" class="px-5 py-14 text-center">
                <p class="font-heading font-medium text-neutral-text">No staff accounts yet</p>
                <p class="mt-1 text-sm text-neutral-text/55">Add someone and they will get an invite by email.</p>
                <button
                    type="button"
                    class="mt-4 rounded-lg bg-primary px-4 py-2 text-sm font-medium text-white transition hover:bg-primary-dark focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/40 focus-visible:ring-offset-2 active:scale-[0.98]"
                    @click="openCreate"
                >
                    Add staff member
                </button>
            </div>

            <div v-else class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-neutral-text/10 bg-neutral-bg text-xs text-neutral-text/55">
                    <tr>
                        <th class="px-5 py-3 font-medium">Name</th>
                        <th class="px-5 py-3 font-medium">Role</th>
                        <th class="px-5 py-3 text-right font-medium">Actions</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-text/[0.07]">
                    <tr
                        v-for="member in staff"
                        :key="member.id"
                        class="group transition-colors duration-150 hover:bg-primary-light"
                        :class="member.can_manage ? 'cursor-pointer' : ''"
                        @click="onRowClick($event, member)"
                    >
                        <td class="px-5 py-3.5">
                            <div class="flex items-center gap-3">
                                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-secondary font-heading text-xs font-semibold text-primary-dark">
                                        {{ initials(member.name) }}
                                    </span>
                                <div class="min-w-0">
                                    <p class="font-medium text-neutral-text">
                                        {{ member.name }}
                                        <span
                                            v-if="member.is_me"
                                            class="ml-1.5 rounded-full bg-neutral-text/[0.07] px-2 py-0.5 text-xs font-medium text-neutral-text/60"
                                        >
                                                You
                                            </span>
                                    </p>
                                    <p class="truncate text-xs text-neutral-text/50">{{ member.email }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-3.5">
                                <span
                                    class="inline-flex items-center gap-1.5 whitespace-nowrap rounded-full px-2.5 py-1 text-xs font-medium"
                                    :class="(roleStyles[member.role] ?? fallbackRole).pill"
                                >
                                    <span class="h-1.5 w-1.5 rounded-full" :class="(roleStyles[member.role] ?? fallbackRole).dot" />
                                    {{ roleLabel(member.role) }}
                                </span>
                        </td>
                        <td class="px-5 py-3.5">
                            <div v-if="member.can_manage" class="flex items-center justify-end gap-2">
                                <button
                                    type="button"
                                    :class="[ghostBtn, 'border-neutral-text/15 bg-white text-neutral-text group-hover:border-primary group-hover:bg-primary group-hover:text-white focus-visible:ring-primary/40']"
                                    @click="openEdit(member)"
                                >
                                    Edit
                                </button>
                                <button
                                    type="button"
                                    :class="[ghostBtn, 'border-primary/40 bg-white text-primary-dark hover:bg-primary-light focus-visible:ring-primary/40']"
                                    @click="resendInvite(member)"
                                >
                                    Resend invite
                                </button>
                                <button
                                    type="button"
                                    :class="[ghostBtn, 'border-transparent text-neutral-text/55 hover:border-accent-light/60 hover:bg-accent-light/10 hover:text-accent focus-visible:ring-accent-light/40']"
                                    @click="revoke(member)"
                                >
                                    Remove access
                                </button>
                            </div>
                            <p v-else class="text-right text-xs text-neutral-text/35">No actions available</p>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <AdminModal :show="showModal" :title="editing ? 'Edit staff member' : 'Add staff member'" @close="close">
            <form class="space-y-5" @submit.prevent="submit">
                <div>
                    <label for="staff-name" class="mb-1.5 block text-sm font-medium text-neutral-text">Full name</label>
                    <input id="staff-name" v-model="form.name" type="text" :class="inputClass(form.errors.name)" />
                    <p v-if="form.errors.name" class="mt-1.5 text-xs font-medium text-accent">{{ form.errors.name }}</p>
                </div>

                <div>
                    <label for="staff-email" class="mb-1.5 block text-sm font-medium text-neutral-text">Email</label>
                    <input
                        id="staff-email"
                        v-model="form.email"
                        type="email"
                        :disabled="!!editing"
                        :class="inputClass(form.errors.email)"
                    />
                    <p v-if="editing" class="mt-1.5 text-xs text-neutral-text/50">The email address cannot be changed.</p>
                    <p v-if="form.errors.email" class="mt-1.5 text-xs font-medium text-accent">{{ form.errors.email }}</p>
                </div>

                <div>
                    <label for="staff-role" class="mb-1.5 block text-sm font-medium text-neutral-text">Role</label>
                    <select id="staff-role" v-model="form.role" :class="inputClass(form.errors.role)">
                        <option v-for="role in assignableRoles" :key="role" :value="role">{{ roleLabel(role) }}</option>
                    </select>
                    <!-- What this role can do, shown as a panel so it is read before inviting -->
                    <p
                        v-if="roleHelp[form.role]"
                        class="mt-2 rounded-lg border border-neutral-text/10 bg-secondary/50 px-3 py-2 text-xs leading-relaxed text-neutral-text/75"
                    >
                        {{ roleHelp[form.role] }}
                    </p>
                    <p v-if="form.errors.role" class="mt-1.5 text-xs font-medium text-accent">{{ form.errors.role }}</p>
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
                        {{ form.processing ? 'Saving...' : editing ? 'Save changes' : 'Create and send invite' }}
                    </button>
                </div>
            </form>
        </AdminModal>
    </AdminLayout>
</template>
