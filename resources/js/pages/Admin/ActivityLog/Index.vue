<script setup>
import { computed, reactive, ref, watch } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { formatDate } from '@/Composables/OrderStatus.js'

const props = defineProps({
    activities: { type: Object, required: true }, // paginator
    filters: { type: [Object, Array], default: () => ({}) },
    users: { type: Array, default: () => [] },
    types: { type: Array, default: () => [] },
    events: { type: Array, default: () => [] },
})

/* ------------------------------------------------------------------ */
/* Filters                                                             */
/* ------------------------------------------------------------------ */

// Normalise null/missing values to '' so every control has a stable model.
const form = reactive(
    Object.fromEntries(
        Object.entries({ user: '', type: '', event: '', from: '', to: '', ...props.filters }).map(([key, value]) => [
            key,
            value ?? '',
        ]),
    ),
)

const loading = ref(false)

const apply = () => {
    // Keep the URL clean: only send filters that have a value.
    const params = Object.fromEntries(Object.entries(form).filter(([, value]) => value !== ''))

    router.get('/admin/activity-log', params, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
        onStart: () => (loading.value = true),
        onFinish: () => (loading.value = false),
    })
}

watch(() => [form.user, form.type, form.event, form.from, form.to], apply)

const hasFilters = () => Object.values(form).some(Boolean)
const reset = () => Object.keys(form).forEach((key) => (form[key] = ''))

const actionChips = computed(() => [{ value: '', label: 'All' }, ...props.events.map((e) => ({ value: e, label: e }))])

/* ------------------------------------------------------------------ */
/* Expandable rows                                                     */
/* ------------------------------------------------------------------ */

const open = reactive({})
const isOpen = (id) => !!open[id]
const toggle = (id) => (open[id] = !open[id])
const hasChanges = (activity) => !!activity.changes?.length

const expandable = computed(() => props.activities.data.filter(hasChanges))
const allOpen = computed(() => expandable.value.length > 0 && expandable.value.every((a) => open[a.id]))
const toggleAll = () => {
    const next = !allOpen.value
    expandable.value.forEach((a) => (open[a.id] = next))
}

const changeLabel = (count) => `${count} ${count === 1 ? 'change' : 'changes'}`

/* ------------------------------------------------------------------ */
/* Display helpers (palette-only)                                      */
/* ------------------------------------------------------------------ */

// Small badge on the avatar corner
const badgeStyles = {
    created: 'bg-primary text-white',
    updated: 'bg-secondary text-primary-dark',
    deleted: 'bg-accent text-white',
    restored: 'bg-primary-dark text-white',
}
const fallbackBadge = 'bg-neutral-text/15 text-neutral-text/70'

// Dot inside the action chips
const dotStyles = {
    created: 'bg-primary',
    updated: 'bg-primary-dark/40',
    deleted: 'bg-accent',
    restored: 'bg-primary-dark',
}

// Stroke paths (20x20 viewBox)
const eventIcons = {
    created: 'M10 5v10M5 10h10',
    updated: 'M13.5 4.5l2 2L7 15l-3 1 1-3 8.5-8.5z',
    deleted: 'M5 6h10M8 6V4h4v2M6.5 6l.7 10h5.6l.7-10',
    restored: 'M4 9h8a4 4 0 010 8H8M4 9l3-3M4 9l3 3',
}
const fallbackIcon = 'M10 6v4l2.5 2.5'
const systemIcon = 'M11 3L5 11h4l-1 6 6-8h-4l1-6z'

const avatarTones = [
    'bg-secondary text-primary-dark',
    'bg-primary-dark text-white',
    'bg-white text-primary-dark ring-1 ring-primary/40',
]
const systemTone = 'bg-neutral-bg text-neutral-text/60 ring-1 ring-neutral-text/15'

const isSystem = (name) => !name || /^system$/i.test(name)

const initials = (name) =>
    (name ?? '')
        .split(/\s+/)
        .filter(Boolean)
        .slice(0, 2)
        .map((part) => part[0])
        .join('')
        .toUpperCase()

const avatarTone = (name) => {
    if (isSystem(name)) return systemTone
    const hash = [...name].reduce((sum, char) => sum + char.charCodeAt(0), 0)
    return avatarTones[hash % avatarTones.length]
}

const titleCase = (text) => (text ?? '').replaceAll('_', ' ')

const fmt = (value) => {
    if (value === null || value === undefined || value === '') return 'empty'
    if (value === true) return 'yes'
    if (value === false) return 'no'
    if (typeof value === 'object') return JSON.stringify(value)
    return String(value)
}

// One-line teaser shown while a row is collapsed
const preview = (activity) => {
    const [first] = activity.changes
    const extra = activity.changes.length - 1
    const line = `${titleCase(first.field)}: ${fmt(first.from)} → ${fmt(first.to)}`
    return extra > 0 ? `${line}  +${extra} more` : line
}

// Pagination: on small screens keep only Previous / Next / current page.
const pageClass = (link, index, total) =>
    index === 0 || index === total - 1 || link.active ? 'inline-flex' : 'hidden sm:inline-flex'

const field =
    'w-full rounded-lg border border-neutral-text/15 bg-white px-3 py-2 text-sm text-neutral-text transition hover:border-neutral-text/30 focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 lg:w-auto'
const label = 'mb-1 block text-xs font-medium text-neutral-text/55'
</script>

<template>
    <Head title="Activity log" />

    <AdminLayout title="Activity log">
        <!-- Filters -->
        <div class="mb-4 rounded-2xl border border-neutral-text/10 bg-white p-4">
            <!-- Action chips -->
            <div
                class="-mx-1 flex gap-1.5 overflow-x-auto px-1 pb-1 sm:flex-wrap sm:overflow-visible sm:pb-0"
                role="group"
                aria-label="Filter by action"
            >
                <button
                    v-for="chip in actionChips"
                    :key="chip.value"
                    type="button"
                    :aria-pressed="form.event === chip.value"
                    class="inline-flex shrink-0 items-center gap-2 rounded-full px-3.5 py-1.5 text-sm font-medium capitalize transition-all duration-200 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/40 active:scale-95"
                    :class="
                        form.event === chip.value
                            ? 'bg-primary-dark text-white shadow-sm'
                            : 'bg-neutral-bg text-neutral-text/70 hover:bg-secondary hover:text-primary-dark'
                    "
                    @click="form.event = chip.value"
                >
                    <span
                        v-if="chip.value"
                        class="h-1.5 w-1.5 rounded-full"
                        :class="form.event === chip.value ? 'bg-white' : (dotStyles[chip.value] ?? 'bg-neutral-text/30')"
                        aria-hidden="true"
                    />
                    {{ chip.label }}
                </button>
            </div>

            <div class="mt-3 grid grid-cols-2 gap-3 border-t border-neutral-text/[0.07] pt-3 lg:flex lg:flex-wrap lg:items-end">
                <div class="col-span-2 sm:col-span-1 lg:col-auto">
                    <label for="al-user" :class="label">Who</label>
                    <select id="al-user" v-model="form.user" :class="field">
                        <option value="">Everyone</option>
                        <option value="system">System (no user)</option>
                        <option v-for="user in users" :key="user.id" :value="user.id">{{ user.name }}</option>
                    </select>
                </div>
                <div class="col-span-2 sm:col-span-1 lg:col-auto">
                    <label for="al-type" :class="label">What</label>
                    <select id="al-type" v-model="form.type" :class="[field, 'capitalize']">
                        <option value="">Everything</option>
                        <option v-for="type in types" :key="type" :value="type">{{ titleCase(type) }}</option>
                    </select>
                </div>
                <div>
                    <label for="al-from" :class="label">From</label>
                    <input id="al-from" v-model="form.from" type="date" :class="field" />
                </div>
                <div>
                    <label for="al-to" :class="label">To</label>
                    <input id="al-to" v-model="form.to" type="date" :class="field" />
                </div>
                <button
                    v-if="hasFilters()"
                    type="button"
                    class="col-span-2 rounded-lg px-3 py-2 text-sm font-medium text-primary-dark transition hover:bg-primary-light focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/40 lg:ml-auto"
                    @click="reset"
                >
                    Clear filters
                </button>
            </div>
        </div>

        <div class="overflow-hidden rounded-2xl border border-neutral-text/10 bg-white">
            <!-- Empty state -->
            <div v-if="activities.data.length === 0" class="px-5 py-16 text-center">
                <div class="mx-auto mb-3 flex h-11 w-11 items-center justify-center rounded-full bg-secondary text-primary-dark">
                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M4 5h12M4 10h12M4 15h7" />
                    </svg>
                </div>
                <template v-if="hasFilters()">
                    <p class="font-heading font-medium text-neutral-text">No activity found</p>
                    <p class="mt-1 text-sm text-neutral-text/55">Try a different date range or clear the filters.</p>
                    <button
                        type="button"
                        class="mt-4 rounded-full border border-primary/40 px-4 py-1.5 text-sm font-medium text-primary-dark transition hover:bg-primary-light focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/40"
                        @click="reset"
                    >
                        Clear filters
                    </button>
                </template>
                <template v-else>
                    <p class="font-heading font-medium text-neutral-text">Nothing logged yet</p>
                    <p class="mt-1 text-sm text-neutral-text/55">Changes made by your team will show up here.</p>
                </template>
            </div>

            <template v-else>
                <!-- Toolbar -->
                <div class="flex items-center justify-between gap-3 border-b border-neutral-text/10 bg-neutral-bg px-5 py-2.5">
                    <p class="text-xs text-neutral-text/55">
                        {{ activities.total }} {{ activities.total === 1 ? 'entry' : 'entries' }}
                    </p>
                    <button
                        v-if="expandable.length"
                        type="button"
                        class="rounded-full px-3 py-1 text-xs font-medium text-primary-dark transition hover:bg-secondary focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/40"
                        @click="toggleAll"
                    >
                        {{ allOpen ? 'Collapse all' : 'Expand all' }}
                    </button>
                </div>

                <TransitionGroup
                    tag="ul"
                    name="row"
                    appear
                    class="transition-opacity duration-200"
                    :class="loading ? 'opacity-60' : 'opacity-100'"
                    :aria-busy="loading"
                >
                    <li
                        v-for="(activity, index) in activities.data"
                        :key="activity.id"
                        class="relative transition-colors duration-200"
                        :class="isOpen(activity.id) ? 'bg-primary-light/50' : ''"
                        :style="{ '--i': Math.min(index, 10) }"
                    >
                        <!-- Timeline line, running through the avatars -->
                        <span
                            v-if="activities.data.length > 1"
                            class="absolute left-[2.375rem] w-0.5 -translate-x-1/2 bg-secondary"
                            :class="
                                index === 0
                                    ? 'top-[2.125rem] bottom-0'
                                    : index === activities.data.length - 1
                                      ? 'top-0 h-[2.125rem]'
                                      : 'inset-y-0'
                            "
                            aria-hidden="true"
                        />
                        <!-- Divider, inset so it starts at the text -->
                        <span
                            v-if="index < activities.data.length - 1"
                            class="absolute bottom-0 left-[4.375rem] right-0 h-px bg-neutral-text/[0.07]"
                            aria-hidden="true"
                        />

                        <!-- Row header: the whole row toggles when there are changes -->
                        <component
                            :is="hasChanges(activity) ? 'button' : 'div'"
                            :type="hasChanges(activity) ? 'button' : undefined"
                            :aria-expanded="hasChanges(activity) ? isOpen(activity.id) : undefined"
                            :aria-controls="hasChanges(activity) ? `al-changes-${activity.id}` : undefined"
                            class="group relative flex w-full items-start gap-3.5 px-5 py-4 text-left"
                            :class="
                                hasChanges(activity)
                                    ? 'cursor-pointer transition-colors duration-150 hover:bg-primary-light/70 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-primary/40'
                                    : ''
                            "
                            @click="hasChanges(activity) && toggle(activity.id)"
                        >
                            <!-- Avatar + action badge -->
                            <span class="relative shrink-0">
                                <span
                                    class="flex h-9 w-9 items-center justify-center rounded-full text-xs font-semibold transition-transform duration-200 group-hover:scale-105 motion-reduce:transition-none"
                                    :class="avatarTone(activity.actor)"
                                >
                                    <svg
                                        v-if="isSystem(activity.actor)"
                                        class="h-4 w-4"
                                        viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"
                                    >
                                        <path :d="systemIcon" />
                                    </svg>
                                    <template v-else>{{ initials(activity.actor) }}</template>
                                </span>
                                <span
                                    class="absolute -bottom-0.5 -right-0.5 flex h-4 w-4 items-center justify-center rounded-full ring-2 ring-white"
                                    :class="badgeStyles[activity.event] ?? fallbackBadge"
                                    :title="activity.event ?? titleCase(activity.log_name)"
                                >
                                    <svg class="h-2.5 w-2.5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <path :d="eventIcons[activity.event] ?? fallbackIcon" />
                                    </svg>
                                </span>
                            </span>

                            <!-- Sentence, time, teaser -->
                            <span class="min-w-0 flex-1">
                                <span class="block text-sm leading-5 text-neutral-text/65">
                                    <span class="font-semibold text-neutral-text">{{ activity.actor }}</span>
                                    <template v-if="activity.event">
                                        <span> {{ activity.event }} </span>
                                        <span v-if="activity.type">{{ titleCase(activity.type) }} </span>
                                    </template>
                                    <template v-else>
                                        <span> {{ activity.description }} </span>
                                    </template>
                                    <span
                                        v-if="activity.subject"
                                        class="inline-block max-w-full truncate rounded-md border border-neutral-text/10 bg-neutral-bg px-1.5 align-bottom text-[13px] font-medium leading-5 text-neutral-text"
                                    >
                                        {{ activity.subject }}
                                    </span>
                                    <span v-if="!activity.event && activity.type"> ({{ titleCase(activity.type) }})</span>
                                </span>

                                <span class="mt-0.5 block text-xs text-neutral-text/50">{{ formatDate(activity.when) }}</span>

                                <span
                                    v-if="hasChanges(activity) && !isOpen(activity.id)"
                                    class="mt-1.5 block truncate text-xs text-neutral-text/55"
                                >
                                    {{ preview(activity) }}
                                </span>
                            </span>

                            <!-- Toggle -->
                            <span
                                v-if="hasChanges(activity)"
                                class="mt-0.5 inline-flex shrink-0 items-center gap-1.5 rounded-full border px-2.5 py-1 text-xs font-medium transition-colors duration-150"
                                :class="
                                    isOpen(activity.id)
                                        ? 'border-primary-dark bg-primary-dark text-white'
                                        : 'border-primary/40 bg-white text-primary-dark group-hover:border-primary group-hover:bg-secondary'
                                "
                            >
                                <span>{{ activity.changes.length }}</span>
                                <span class="hidden sm:inline">{{ activity.changes.length === 1 ? 'change' : 'changes' }}</span>
                                <span class="sr-only">{{ changeLabel(activity.changes.length) }},</span>
                                <svg
                                    class="h-3.5 w-3.5 transition-transform duration-200 motion-reduce:transition-none"
                                    :class="isOpen(activity.id) ? 'rotate-180' : ''"
                                    viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"
                                >
                                    <path d="m5 8 5 5 5-5" />
                                </svg>
                            </span>
                        </component>

                        <!-- Before / after (animated height) -->
                        <div
                            v-if="hasChanges(activity)"
                            :id="`al-changes-${activity.id}`"
                            class="grid transition-[grid-template-rows] duration-300 ease-out motion-reduce:transition-none"
                            :class="isOpen(activity.id) ? 'grid-rows-[1fr]' : 'grid-rows-[0fr]'"
                            :inert="isOpen(activity.id) ? undefined : ''"
                        >
                            <div class="overflow-hidden">
                                <!-- 3.125rem = avatar (2.25rem) + gap (0.875rem): lines up with the sentence -->
                                <div class="px-5 pb-4">
                                    <ul class="ml-[3.125rem] divide-y divide-neutral-text/[0.07] overflow-hidden rounded-xl border border-neutral-text/10 bg-white">
                                        <li
                                            v-for="(change, i) in activity.changes"
                                            :key="change.field"
                                            class="flex flex-wrap items-center gap-x-3 gap-y-1.5 px-3.5 py-2.5 transition duration-300 motion-reduce:transition-none"
                                            :class="isOpen(activity.id) ? 'translate-y-0 opacity-100' : '-translate-y-1 opacity-0'"
                                            :style="{ transitionDelay: isOpen(activity.id) ? `${80 + Math.min(i, 8) * 40}ms` : '0ms' }"
                                        >
                                            <span class="w-full text-xs font-medium capitalize text-neutral-text sm:w-32 sm:shrink-0">
                                                {{ titleCase(change.field) }}
                                            </span>
                                            <span class="flex min-w-0 flex-1 flex-wrap items-center gap-2">
                                                <span class="max-w-full break-words rounded-md bg-neutral-bg px-2 py-0.5 text-xs text-neutral-text/55 line-through decoration-neutral-text/30">
                                                    {{ fmt(change.from) }}
                                                </span>
                                                <svg class="h-3.5 w-3.5 shrink-0 text-neutral-text/30" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                    <path d="M4 10h12m-4-4 4 4-4 4" />
                                                </svg>
                                                <span class="max-w-full break-words rounded-md bg-primary/10 px-2 py-0.5 text-xs font-medium text-primary-dark">
                                                    {{ fmt(change.to) }}
                                                </span>
                                            </span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </li>
                </TransitionGroup>
            </template>

            <!-- Pagination -->
            <div
                v-if="activities.last_page > 1"
                class="flex flex-wrap items-center justify-between gap-3 border-t border-neutral-text/10 bg-neutral-bg px-5 py-3"
            >
                <p class="text-xs text-neutral-text/55">
                    Showing {{ activities.from }} to {{ activities.to }} of {{ activities.total }} entries
                </p>
                <nav class="flex flex-wrap gap-1" aria-label="Pagination">
                    <template v-for="(link, index) in activities.links" :key="link.label">
                        <Link
                            v-if="link.url"
                            :href="link.url"
                            preserve-scroll
                            :aria-current="link.active ? 'page' : undefined"
                            class="min-w-[2rem] items-center justify-center rounded-full px-3 py-1.5 text-sm transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/40"
                            :class="[
                                pageClass(link, index, activities.links.length),
                                link.active ? 'bg-primary-dark font-medium text-white' : 'text-neutral-text/70 hover:bg-secondary hover:text-primary-dark',
                            ]"
                            v-html="link.label"
                        />
                        <span
                            v-else
                            class="min-w-[2rem] items-center justify-center rounded-full px-3 py-1.5 text-sm text-neutral-text/25"
                            :class="pageClass(link, index, activities.links.length)"
                            v-html="link.label"
                        />
                    </template>
                </nav>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
/* One orchestrated moment: rows glide in with a short stagger whenever the list loads or changes. */
.row-enter-active {
    animation: row-in 0.35s ease-out both;
    animation-delay: calc(var(--i, 0) * 35ms);
}

@keyframes row-in {
    from {
        opacity: 0;
        transform: translateY(6px);
    }
    to {
        opacity: 1;
        transform: none;
    }
}

@media (prefers-reduced-motion: reduce) {
    .row-enter-active {
        animation: none;
    }
}
</style>
