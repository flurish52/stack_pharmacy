<script setup>
import { reactive, watch } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { naira, statusLabel, formatDate } from '@/Composables/OrderStatus.js'

const props = defineProps({
    activities: { type: Object, required: true }, // paginator
    filters: { type: Object, required: true },
    users: { type: Array, default: () => [] },
    types: { type: Array, default: () => [] },
    events: { type: Array, default: () => [] },
})

const form = reactive({ ...props.filters })

const apply = () => router.get('/admin/activity-log', form, { preserveState: true, preserveScroll: true, replace: true })

watch(() => [form.user, form.type, form.event, form.from, form.to], apply)

const hasFilters = () => Object.values(form).some(Boolean)

const reset = () => Object.keys(form).forEach((key) => (form[key] = ''))

// Which rows have their details open
const open = reactive({})
const toggle = (id) => (open[id] = !open[id])

const eventStyles = {
    created: 'bg-emerald-50 text-emerald-700',
    updated: 'bg-blue-50 text-blue-700',
    deleted: 'bg-red-50 text-red-700',
    restored: 'bg-amber-50 text-amber-700',
}

const titleCase = (text) => (text ?? '').replaceAll('_', ' ')

const sentence = (a) => {
    const target = [a.type, a.subject ? `"${a.subject}"` : null].filter(Boolean).join(' ')

    // Model events have an event name; manual logs carry their own wording.
    return a.event ? `${a.event} ${target}` : `${a.description}${target ? ` (${target})` : ''}`
}

const fmt = (value) => {
    if (value === null || value === undefined || value === '') return 'empty'
    if (value === true) return 'yes'
    if (value === false) return 'no'
    return String(value)
}

const select =
    'rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500'
</script>

<template>
    <Head title="Activity log" />

    <AdminLayout title="Activity log">
        <!-- Filters -->
        <div class="mb-4 flex flex-col gap-3 lg:flex-row lg:flex-wrap lg:items-end">
            <div>
                <label class="mb-1 block text-xs text-gray-500">Who</label>
                <select v-model="form.user" :class="select">
                    <option value="">Everyone</option>
                    <option value="system">System (no user)</option>
                    <option v-for="user in users" :key="user.id" :value="user.id">{{ user.name }}</option>
                </select>
            </div>
            <div>
                <label class="mb-1 block text-xs text-gray-500">What</label>
                <select v-model="form.type" :class="[select, 'capitalize']">
                    <option value="">Everything</option>
                    <option v-for="type in types" :key="type" :value="type">{{ titleCase(type) }}</option>
                </select>
            </div>
            <div>
                <label class="mb-1 block text-xs text-gray-500">Action</label>
                <select v-model="form.event" :class="[select, 'capitalize']">
                    <option value="">Any action</option>
                    <option v-for="event in events" :key="event" :value="event">{{ event }}</option>
                </select>
            </div>
            <div>
                <label class="mb-1 block text-xs text-gray-500">From</label>
                <input v-model="form.from" type="date" :class="select" />
            </div>
            <div>
                <label class="mb-1 block text-xs text-gray-500">To</label>
                <input v-model="form.to" type="date" :class="select" />
            </div>
            <button v-if="hasFilters()" type="button" class="pb-2 text-sm text-gray-500 hover:text-gray-900" @click="reset">
                Clear filters
            </button>
        </div>

        <div class="rounded-lg border border-gray-200 bg-white">
            <p v-if="activities.data.length === 0" class="px-5 py-10 text-center text-sm text-gray-500">
                No activity matches these filters.
            </p>

            <ul v-else class="divide-y divide-gray-100">
                <li v-for="activity in activities.data" :key="activity.id" class="px-5 py-3">
                    <div class="flex flex-wrap items-start justify-between gap-2">
                        <div class="flex items-start gap-3">
                            <span
                                class="mt-0.5 shrink-0 rounded-full px-2.5 py-1 text-xs font-medium capitalize"
                                :class="eventStyles[activity.event] ?? 'bg-gray-100 text-gray-700'"
                            >
                                {{ activity.event ?? titleCase(activity.log_name) }}
                            </span>
                            <p class="text-sm">
                                <span class="font-medium">{{ activity.actor }}</span>
                                <span class="text-gray-600"> {{ sentence(activity) }}</span>
                            </p>
                        </div>
                        <div class="flex items-center gap-3 text-xs text-gray-400">
                            <span>{{ formatDate(activity.when) }}</span>
                            <button
                                v-if="activity.changes.length"
                                type="button"
                                class="text-emerald-700 hover:underline"
                                @click="toggle(activity.id)"
                            >
                                {{ open[activity.id] ? 'Hide details' : 'Details' }}
                            </button>
                        </div>
                    </div>

                    <table v-if="open[activity.id]" class="mt-3 w-full max-w-2xl text-left text-xs">
                        <thead class="text-gray-400">
                        <tr>
                            <th class="py-1 pr-4 font-medium">Field</th>
                            <th class="py-1 pr-4 font-medium">Before</th>
                            <th class="py-1 font-medium">After</th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                        <tr v-for="change in activity.changes" :key="change.field">
                            <td class="py-1.5 pr-4 font-medium capitalize">{{ titleCase(change.field) }}</td>
                            <td class="py-1.5 pr-4 text-gray-500">{{ fmt(change.from) }}</td>
                            <td class="py-1.5">{{ fmt(change.to) }}</td>
                        </tr>
                        </tbody>
                    </table>
                </li>
            </ul>

            <div
                v-if="activities.last_page > 1"
                class="flex flex-wrap items-center justify-between gap-3 border-t border-gray-200 px-5 py-3"
            >
                <p class="text-xs text-gray-500">
                    Showing {{ activities.from }} to {{ activities.to }} of {{ activities.total }} entries
                </p>
                <div class="flex flex-wrap gap-1">
                    <template v-for="link in activities.links" :key="link.label">
                        <Link
                            v-if="link.url"
                            :href="link.url"
                            preserve-scroll
                            class="rounded-md px-3 py-1.5 text-sm"
                            :class="link.active ? 'bg-emerald-600 text-white' : 'text-gray-600 hover:bg-gray-100'"
                            v-html="link.label"
                        />
                        <span v-else class="rounded-md px-3 py-1.5 text-sm text-gray-300" v-html="link.label" />
                    </template>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
