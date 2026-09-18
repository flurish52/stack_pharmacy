<script setup>
import { ref } from 'vue'
import { useForm, router } from '@inertiajs/vue3'
import AccountLayout from '@/Layouts/AccountLayout.vue'

defineOptions({ layout: AccountLayout })

const props = defineProps({
    addresses: { type: Array, default: () => [] },
})

const showForm = ref(false)
const editingId = ref(null)

const form = useForm({
    label: '',
    address: '',
    phone: '',
    is_default: false,
})

function openCreate() {
    editingId.value = null
    form.reset()
    form.clearErrors()
    showForm.value = true
}

function openEdit(addr) {
    editingId.value = addr.id
    form.label = addr.label ?? ''
    form.address = addr.address
    form.phone = addr.phone
    form.is_default = !!addr.is_default
    form.clearErrors()
    showForm.value = true
}

function closeForm() {
    showForm.value = false
    editingId.value = null
    form.reset()
    form.clearErrors()
}

function submit() {
    if (editingId.value) {
        form.put(route('account.addresses.update', editingId.value), {
            preserveScroll: true,
            onSuccess: closeForm,
        })
    } else {
        form.post(route('account.addresses.store'), {
            preserveScroll: true,
            onSuccess: closeForm,
        })
    }
}

function destroy(addr) {
    if (!confirm(`Remove the "${addr.label || 'saved'}" address?`)) return
    router.delete(route('account.addresses.destroy', addr.id), { preserveScroll: true })
}
</script>

<template>
    <div>
        <div class="flex items-center justify-between gap-4">
            <h2 class="font-heading text-lg font-semibold text-neutral-text">Addresses</h2>
            <button
                v-if="!showForm"
                type="button"
                @click="openCreate"
                class="rounded-md bg-primary-dark px-3.5 py-2 text-sm font-medium text-white transition-colors hover:opacity-90"
            >
                Add address
            </button>
        </div>

        <!-- Add / edit form -->
        <form v-if="showForm" @submit.prevent="submit" class="mt-5 rounded-xl border border-neutral-text/10 bg-neutral-bg/50 p-4 sm:p-5">
            <div class="grid gap-4 sm:grid-cols-2">
                <div class="sm:col-span-1">
                    <label class="text-sm font-medium text-neutral-text/80">Label (optional)</label>
                    <input
                        v-model="form.label"
                        type="text"
                        placeholder="Home, Office…"
                        class="mt-1.5 w-full rounded-md border border-neutral-text/15 px-3 py-2 text-sm text-neutral-text focus:border-primary-dark focus:outline-none focus:ring-1 focus:ring-primary-dark"
                    />
                    <p v-if="form.errors.label" class="mt-1 text-xs text-red-600">{{ form.errors.label }}</p>
                </div>

                <div class="sm:col-span-1">
                    <label class="text-sm font-medium text-neutral-text/80">Phone number</label>
                    <input
                        v-model="form.phone"
                        type="text"
                        placeholder="+234…"
                        class="mt-1.5 w-full rounded-md border border-neutral-text/15 px-3 py-2 text-sm text-neutral-text focus:border-primary-dark focus:outline-none focus:ring-1 focus:ring-primary-dark"
                    />
                    <p v-if="form.errors.phone" class="mt-1 text-xs text-red-600">{{ form.errors.phone }}</p>
                </div>

                <div class="sm:col-span-2">
                    <label class="text-sm font-medium text-neutral-text/80">Address</label>
                    <textarea
                        v-model="form.address"
                        rows="3"
                        placeholder="Street, area, city, state"
                        class="mt-1.5 w-full rounded-md border border-neutral-text/15 px-3 py-2 text-sm text-neutral-text focus:border-primary-dark focus:outline-none focus:ring-1 focus:ring-primary-dark"
                    ></textarea>
                    <p v-if="form.errors.address" class="mt-1 text-xs text-red-600">{{ form.errors.address }}</p>
                </div>

                <div class="sm:col-span-2">
                    <label class="flex items-center gap-2 text-sm text-neutral-text/80">
                        <input v-model="form.is_default" type="checkbox" class="rounded border-neutral-text/30 text-primary-dark focus:ring-primary-dark" />
                        Set as default address
                    </label>
                </div>
            </div>

            <div class="mt-4 flex items-center gap-2">
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="rounded-md bg-primary-dark px-3.5 py-2 text-sm font-medium text-white transition-colors hover:opacity-90 disabled:opacity-60"
                >
                    {{ editingId ? 'Save changes' : 'Save address' }}
                </button>
                <button
                    type="button"
                    @click="closeForm"
                    class="rounded-md px-3.5 py-2 text-sm font-medium text-neutral-text/70 hover:bg-neutral-bg"
                >
                    Cancel
                </button>
            </div>
        </form>

        <!-- List -->
        <div v-if="addresses.length" class="mt-6 grid gap-3 sm:grid-cols-2">
            <div
                v-for="addr in addresses"
                :key="addr.id"
                class="relative rounded-xl border border-neutral-text/10 p-4"
            >
                <span
                    v-if="addr.is_default"
                    class="absolute right-4 top-4 rounded-full bg-primary-light px-2 py-0.5 text-xs font-medium text-primary-dark"
                >
                    Default
                </span>

                <p class="pr-16 font-medium text-neutral-text">{{ addr.label || 'Address' }}</p>
                <p class="mt-1 text-sm text-neutral-text/70">{{ addr.address }}</p>
                <p class="mt-1 text-sm text-neutral-text/70">{{ addr.phone }}</p>

                <div class="mt-3 flex gap-3 text-sm">
                    <button type="button" @click="openEdit(addr)" class="font-medium text-primary-dark hover:underline">
                        Edit
                    </button>
                    <button type="button" @click="destroy(addr)" class="font-medium text-red-600 hover:underline">
                        Remove
                    </button>
                </div>
            </div>
        </div>

        <div v-else-if="!showForm" class="mt-6 rounded-xl border border-dashed border-neutral-text/15 p-8 text-center">
            <p class="text-sm text-neutral-text/60">You haven't saved any addresses yet.</p>
        </div>
    </div>
</template>
