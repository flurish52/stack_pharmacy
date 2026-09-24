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

// Simple line icons so each platform is recognisable at a glance (path data for a 20x20 viewBox).
const icons = {
    whatsapp: 'M4 15.5 5 12a6 6 0 1 1 2.5 2.5L4 15.5Z',
    phone: 'M5 3.5h2.5l1.25 3-1.75 1.25a9 9 0 0 0 4.25 4.25L12.5 10.25l3 1.25V14a1.5 1.5 0 0 1-1.5 1.5A10.5 10.5 0 0 1 3.5 5 1.5 1.5 0 0 1 5 3.5Z',
    email: 'M3.5 5.5h13v9h-13v-9Zm0 .5 6.5 5 6.5-5',
}
const iconPath = (platform) => icons[platform] ?? 'M8 12l4-4M7 9.5 5.75 10.75a2.5 2.5 0 0 0 3.5 3.5L10.5 13M13 10.5l1.25-1.25a2.5 2.5 0 0 0-3.5-3.5L9.5 7'

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

// Whole row opens the editor; real links/buttons inside keep their own behaviour.
const onRowClick = (event, channel) => {
    if (event.target.closest('a, button')) return
    openEdit(channel)
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
    <Head title="Contact channels" />

    <AdminLayout title="Contact channels">
        <template #actions>
            <button
                type="button"
                class="inline-flex items-center gap-1.5 rounded-lg bg-primary px-4 py-2 text-sm font-medium text-white transition hover:bg-primary-dark focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/40 focus-visible:ring-offset-2 active:scale-[0.98]"
                @click="openCreate"
            >
                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
                    <path d="M10 4v12M4 10h12" />
                </svg>
                Add channel
            </button>
        </template>

        <!-- Info note -->
        <div class="mb-4 flex gap-3 rounded-xl border border-neutral-text/10 bg-secondary/50 px-4 py-3 text-sm text-neutral-text/75">
            <svg class="mt-0.5 h-4 w-4 shrink-0 text-primary-dark" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <circle cx="10" cy="10" r="7.5" /><path d="M10 9.25v4M10 6.5h.01" />
            </svg>
            <p>These appear on the public Contact page and in the storefront footer, lowest order number first.</p>
        </div>

        <div class="overflow-hidden rounded-xl border border-neutral-text/10 bg-white">
            <div v-if="channels.length === 0" class="px-5 py-14 text-center">
                <p class="font-heading font-medium text-neutral-text">No contact channels yet</p>
                <p class="mx-auto mt-1 max-w-sm text-sm text-neutral-text/55">
                    Add the ways customers can reach the pharmacy.
                </p>
                <button
                    type="button"
                    class="mt-4 rounded-lg bg-primary px-4 py-2 text-sm font-medium text-white transition hover:bg-primary-dark focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/40 focus-visible:ring-offset-2 active:scale-[0.98]"
                    @click="openCreate"
                >
                    Add channel
                </button>
            </div>

            <div v-else class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-neutral-text/10 bg-neutral-bg text-xs text-neutral-text/55">
                    <tr>
                        <th class="w-20 px-5 py-3 font-medium">Order</th>
                        <th class="px-5 py-3 font-medium">Platform</th>
                        <th class="px-5 py-3 font-medium">Handle</th>
                        <th class="px-5 py-3 font-medium">Link</th>
                        <th class="px-5 py-3 text-right font-medium">Actions</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-text/[0.07]">
                    <tr
                        v-for="channel in channels"
                        :key="channel.id"
                        class="group cursor-pointer transition-colors duration-150 hover:bg-primary-light"
                        @click="onRowClick($event, channel)"
                    >
                        <td class="px-5 py-3.5">
                                <span class="inline-flex h-7 min-w-7 items-center justify-center rounded-full bg-neutral-text/[0.07] px-2 text-xs font-medium tabular-nums text-neutral-text/70">
                                    {{ channel.display_order }}
                                </span>
                        </td>
                        <td class="px-5 py-3.5">
                            <div class="flex items-center gap-3">
                                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-secondary text-primary-dark">
                                        <svg class="h-[18px] w-[18px]" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <path :d="iconPath(channel.platform)" />
                                        </svg>
                                    </span>
                                <span class="font-medium text-neutral-text">{{ platformLabel(channel.platform) }}</span>
                            </div>
                        </td>
                        <td class="px-5 py-3.5 text-neutral-text">{{ channel.handle }}</td>
                        <td class="max-w-xs px-5 py-3.5">
                            <a
                                v-if="channel.url"
                                :href="channel.url"
                                target="_blank"
                                rel="noopener"
                                class="block truncate text-primary-dark hover:underline"
                            >
                                {{ channel.url }}
                            </a>
                            <span v-else class="text-neutral-text/30">-</span>
                        </td>
                        <td class="px-5 py-3.5">
                            <div class="flex items-center justify-end gap-2">
                                <button
                                    type="button"
                                    :class="[ghostBtn, 'border-neutral-text/15 bg-white text-neutral-text group-hover:border-primary group-hover:bg-primary group-hover:text-white focus-visible:ring-primary/40']"
                                    @click="openEdit(channel)"
                                >
                                    Edit
                                </button>
                                <button
                                    type="button"
                                    :class="[ghostBtn, 'border-transparent text-neutral-text/55 hover:border-accent-light/60 hover:bg-accent-light/10 hover:text-accent focus-visible:ring-accent-light/40']"
                                    @click="remove(channel)"
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

        <AdminModal :show="showModal" :title="editing ? 'Edit channel' : 'Add channel'" @close="close">
            <form class="space-y-5" @submit.prevent="submit">
                <div>
                    <label for="cc-platform" class="mb-1.5 block text-sm font-medium text-neutral-text">Platform</label>
                    <select id="cc-platform" v-model="form.platform" :class="inputClass(form.errors.platform)">
                        <option v-for="p in platforms" :key="p.value" :value="p.value">{{ p.label }}</option>
                    </select>
                    <p v-if="form.errors.platform" class="mt-1.5 text-xs font-medium text-accent">{{ form.errors.platform }}</p>
                </div>

                <div>
                    <label for="cc-handle" class="mb-1.5 block text-sm font-medium text-neutral-text">Handle</label>
                    <input
                        id="cc-handle"
                        v-model="form.handle"
                        type="text"
                        :placeholder="platforms.find((p) => p.value === form.platform)?.handleHint"
                        :class="inputClass(form.errors.handle)"
                    />
                    <p class="mt-1.5 text-xs text-neutral-text/50">What customers see: a number, email address or @username.</p>
                    <p v-if="form.errors.handle" class="mt-1.5 text-xs font-medium text-accent">{{ form.errors.handle }}</p>
                </div>

                <div>
                    <label for="cc-url" class="mb-1.5 block text-sm font-medium text-neutral-text">
                        Link <span class="font-normal text-neutral-text/50">(optional)</span>
                    </label>
                    <input id="cc-url" v-model="form.url" type="url" placeholder="https://" :class="inputClass(form.errors.url)" />
                    <p class="mt-1.5 text-xs text-neutral-text/50">
                        The full page link for social platforms. Leave blank for phone and email.
                    </p>
                    <p v-if="form.errors.url" class="mt-1.5 text-xs font-medium text-accent">{{ form.errors.url }}</p>
                </div>

                <div>
                    <label for="cc-order" class="mb-1.5 block text-sm font-medium text-neutral-text">Display order</label>
                    <input
                        id="cc-order"
                        v-model="form.display_order"
                        type="number"
                        min="0"
                        step="1"
                        :class="[inputClass(form.errors.display_order), 'sm:max-w-[8rem]']"
                    />
                    <p class="mt-1.5 text-xs text-neutral-text/50">Lowest number shows first.</p>
                    <p v-if="form.errors.display_order" class="mt-1.5 text-xs font-medium text-accent">
                        {{ form.errors.display_order }}
                    </p>
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
                        {{ form.processing ? 'Saving...' : editing ? 'Save changes' : 'Add channel' }}
                    </button>
                </div>
            </form>
        </AdminModal>
    </AdminLayout>
</template>
