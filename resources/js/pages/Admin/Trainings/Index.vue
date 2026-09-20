<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import ImageField from '@/Components/Admin/ImageField.vue'

const props = defineProps({
    training: { type: Object, required: true }, // empty object until first save
})

const form = useForm({
    title: props.training.title ?? '',
    description: props.training.description ?? '',
    image: null,
    remove_image: false,
})

// PHP can't read files sent with PATCH, so this is POST + _method spoofing.
const submit = () =>
    form
        .transform((data) => ({ ...data, _method: 'patch' }))
        .post('/admin/training', {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: () => {
                form.image = null
                form.remove_image = false
            },
        })

const input =
    'w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500'
</script>

<template>
    <Head title="Training page" />

    <AdminLayout title="Training page">
        <template #actions>
            <Link href="/training" class="text-sm text-emerald-700 hover:underline">View public page</Link>
        </template>

        <form class="max-w-2xl space-y-5 rounded-lg border border-gray-200 bg-white p-5" @submit.prevent="submit">
            <p class="text-sm text-gray-500">
                This edits the content of the public Training page only. Enrollment, payment and hosting are not part of this screen.
            </p>

            <!-- key remounts the field after a save so the preview matches what is stored -->
            <ImageField :key="training.image_url ?? 'none'" :form="form" :current-url="training.image_url ?? null" />

            <div>
                <label class="mb-1 block text-sm font-medium">Title</label>
                <input v-model="form.title" type="text" :class="input" />
                <p v-if="form.errors.title" class="mt-1 text-xs text-red-600">{{ form.errors.title }}</p>
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium">Description</label>
                <textarea v-model="form.description" rows="8" :class="input" />
                <p v-if="form.errors.description" class="mt-1 text-xs text-red-600">{{ form.errors.description }}</p>
            </div>

            <div class="flex justify-end">
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="rounded-md bg-emerald-600 px-5 py-2 text-sm font-medium text-white hover:bg-emerald-700 disabled:opacity-50"
                >
                    {{ form.processing ? 'Saving...' : 'Save changes' }}
                </button>
            </div>
        </form>
    </AdminLayout>
</template>
