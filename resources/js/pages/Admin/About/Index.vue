<script setup>
import { ref } from 'vue'
import { Head, useForm } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import ImageUploadField from '@/Components/Admin/ImageField.vue'

const props = defineProps({
    about: { type: Object, required: true },
    team: { type: Array, default: () => [] },
})

/* ---------- Story / Mission / Vision form ---------- */

const aboutForm = useForm({
    story: props.about.story ?? '',
    mission: props.about.mission ?? '',
    vision: props.about.vision ?? '',
    image: null,
    remove_image: false,
})

const saveAbout = () => {
    aboutForm.transform((data) => ({ ...data, _method: 'put' })).post(route('admin.about.update'), {
        preserveScroll: true,
        onSuccess: () => {
            aboutForm.image = null
            aboutForm.remove_himage = false
        },
    })
}

/* ---------- Team members: inline add + expand-to-edit ---------- */

const showAddForm = ref(false)
const expandedId = ref(null)

const addForm = useForm({
    name: '',
    role: '',
    bio: '',
    image: null,
    sort_order: props.team.length,
})

const submitAdd = () => {
    addForm.post(route('admin.about.team.store'), {
        preserveScroll: true,
        onSuccess: () => {
            addForm.reset()
            addForm.sort_order = props.team.length + 1
            showAddForm.value = false
        },
    })
}

const editForms = ref({})

const toggleExpand = (member) => {
    if (expandedId.value === member.id) {
        expandedId.value = null
        return
    }

    // Build the edit form lazily, the first time a row is expanded.
    if (!editForms.value[member.id]) {
        editForms.value[member.id] = useForm({
            name: member.name,
            role: member.role,
            bio: member.bio ?? '',
            image: null,
            remove_image: false,
            sort_order: member.sort_order,
        })
    }

    expandedId.value = member.id
}

const submitEdit = (member) => {
    const form = editForms.value[member.id]
    form.transform((data) => ({ ...data, _method: 'patch' })).post(
        route('admin.about.team.update', member.id),
        {
            preserveScroll: true,
            onSuccess: () => {
                form.image = null
                form.remove_image = false
                expandedId.value = null
            },
        },
    )
}

const removeMember = (member) => {
    if (!confirm(`Remove ${member.name} from the team?`)) return

    useForm({}).delete(route('admin.about.team.destroy', member.id), {
        preserveScroll: true,
    })
}
</script>

<template>
    <Head title="About Us — Admin" />

    <AdminLayout>
        <div class="mx-auto max-w-4xl space-y-10 p-6">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">About Us</h1>
                <p class="mt-1 text-sm text-gray-500">Edit the story, mission, vision and team shown on the public About page.</p>
            </div>

            <!-- Story / Mission / Vision -->
            <section class="rounded-lg border border-gray-200 bg-white p-6">
                <h2 class="mb-4 font-semibold text-gray-900">Brand Story</h2>

                <form class="space-y-5" @submit.prevent="saveAbout">
                    <ImageUploadField
                        :form="aboutForm"
                        :current-url="about.image_url"
                        label="Hero image"
                        file-key="image"
                        remove-key="remove_image"
                    />

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700">Our Story</label>
                        <textarea
                            v-model="aboutForm.story"
                            rows="5"
                            class="w-full rounded-md border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500"
                            placeholder="Tell the story of the pharmacy — how it started, what it stands for..."
                        />
                        <p v-if="aboutForm.errors.story" class="mt-1 text-xs text-red-600">{{ aboutForm.errors.story }}</p>
                    </div>

                    <div class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700">Mission</label>
                            <textarea
                                v-model="aboutForm.mission"
                                rows="3"
                                class="w-full rounded-md border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500"
                            />
                            <p v-if="aboutForm.errors.mission" class="mt-1 text-xs text-red-600">{{ aboutForm.errors.mission }}</p>
                        </div>

                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700">Vision</label>
                            <textarea
                                v-model="aboutForm.vision"
                                rows="3"
                                class="w-full rounded-md border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500"
                            />
                            <p v-if="aboutForm.errors.vision" class="mt-1 text-xs text-red-600">{{ aboutForm.errors.vision }}</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <button
                            type="submit"
                            :disabled="aboutForm.processing"
                            class="rounded-md bg-emerald-600 px-5 py-2 text-sm font-medium text-white hover:bg-emerald-700 disabled:opacity-50"
                        >
                            {{ aboutForm.processing ? 'Saving...' : 'Save changes' }}
                        </button>
                        <span v-if="aboutForm.recentlySuccessful" class="text-sm text-emerald-600">Saved.</span>
                    </div>
                </form>
            </section>

            <!-- Team -->
            <section class="rounded-lg border border-gray-200 bg-white p-6">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="font-semibold text-gray-900">Team Members</h2>
                    <button
                        type="button"
                        class="rounded-md border border-emerald-600 px-3 py-1.5 text-sm font-medium text-emerald-700 hover:bg-emerald-50"
                        @click="showAddForm = !showAddForm"
                    >
                        {{ showAddForm ? 'Cancel' : '+ Add member' }}
                    </button>
                </div>

                <!-- Add form -->
                <form v-if="showAddForm" class="mb-6 space-y-4 rounded-md border border-dashed border-gray-300 p-4" @submit.prevent="submitAdd">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">Name</label>
                            <input v-model="addForm.name" type="text" class="w-full rounded-md border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500" />
                            <p v-if="addForm.errors.name" class="mt-1 text-xs text-red-600">{{ addForm.errors.name }}</p>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">Role</label>
                            <input v-model="addForm.role" type="text" class="w-full rounded-md border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500" placeholder="e.g. Finance & Admin" />
                            <p v-if="addForm.errors.role" class="mt-1 text-xs text-red-600">{{ addForm.errors.role }}</p>
                        </div>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">Bio (optional)</label>
                        <textarea v-model="addForm.bio" rows="2" class="w-full rounded-md border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500" />
                    </div>

                    <ImageUploadField
                        :form="addForm"
                        label="Image"
                        file-key="image"
                    />

                    <button
                        type="submit"
                        :disabled="addForm.processing"
                        class="rounded-md bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700 disabled:opacity-50"
                    >
                        {{ addForm.processing ? 'Adding...' : 'Add member' }}
                    </button>
                </form>

                <p v-if="team.length === 0" class="rounded-md border border-dashed border-gray-300 px-4 py-8 text-center text-sm text-gray-500">
                    No team members yet.
                </p>

                <ul v-else class="divide-y divide-gray-100">
                    <li v-for="member in team" :key="member.id" class="py-3">
                        <div class="flex items-center gap-3">
                            <img
                                v-if="member.image_url"
                                :src="member.image_url"
                                alt=""
                                class="h-10 w-10 shrink-0 rounded-full object-cover"
                            />
                            <div v-else class="h-10 w-10 shrink-0 rounded-full bg-gray-100" />

                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-medium text-gray-900">{{ member.name }}</p>
                                <p class="truncate text-xs text-gray-500">{{ member.role }}</p>
                            </div>

                            <button type="button" class="text-sm text-emerald-700 hover:underline" @click="toggleExpand(member)">
                                {{ expandedId === member.id ? 'Close' : 'Edit' }}
                            </button>
                            <button type="button" class="text-sm text-red-600 hover:underline" @click="removeMember(member)">
                                Remove
                            </button>
                        </div>

                        <!-- Inline edit form -->
                        <form
                            v-if="expandedId === member.id"
                            class="mt-4 space-y-4 rounded-md bg-gray-50 p-4"
                            @submit.prevent="submitEdit(member)"
                        >
                            <div class="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <label class="mb-1 block text-sm font-medium text-gray-700">Name</label>
                                    <input v-model="editForms[member.id].name" type="text" class="w-full rounded-md border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500" />
                                </div>
                                <div>
                                    <label class="mb-1 block text-sm font-medium text-gray-700">Role</label>
                                    <input v-model="editForms[member.id].role" type="text" class="w-full rounded-md border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500" />
                                </div>
                            </div>

                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700">Bio</label>
                                <textarea v-model="editForms[member.id].bio" rows="2" class="w-full rounded-md border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500" />
                            </div>

                            <ImageUploadField
                                :form="editForms[member.id]"
                                :current-url="member.image_url"
                                label="Image"
                                file-key="image"
                                remove-key="remove_image"
                            />

                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700">Sort order</label>
                                <input v-model.number="editForms[member.id].sort_order" type="number" min="0" class="w-24 rounded-md border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500" />
                            </div>

                            <button
                                type="submit"
                                :disabled="editForms[member.id].processing"
                                class="rounded-md bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700 disabled:opacity-50"
                            >
                                Save
                            </button>
                        </form>
                    </li>
                </ul>
            </section>
        </div>
    </AdminLayout>
</template>
