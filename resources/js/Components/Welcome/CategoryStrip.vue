<script setup>
/**
 * Category browsing.
 *
 * Image-first: a category with a real photo (image_url) shows it; anything
 * without one falls back to the line-icon tile so the grid never looks
 * broken mid-rollout of photography. Because a photo needs room to read,
 * desktop moved from a divided icon row to an image-card grid — a rail of
 * tiny cropped thumbnails would have made good photos look like lint.
 *
 * Below md it's still a scroll rail (right pattern for touch); at md and
 * up it's a grid that fills the container evenly.
 *
 * Capped at 5 categories + a "View all" tile (6 total) rather than 6 + a
 * button — 6 tiles fill xl:grid-cols-6 as one exact row and md:grid-cols-3
 * as two exact rows; 7 tiles (6 cats + button) leaves an orphan on its own
 * row at xl. If `categories` is already trimmed to 5 server-side, `slice`
 * here is a no-op safety net, not extra work.
 */
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import SectionHeading from "@/Components/Welcome/SectionHeading.vue";

const props = defineProps({
    categories: { type: Array, default: () => [] },
})

const visibleCategories = computed(() => props.categories.slice(0, 5))

const icons = {
    'pain-relief': 'M4.5 12.5 12.5 4.5a4 4 0 0 1 5.66 5.66l-8 8a4 4 0 0 1-5.66-5.66zM8 8l8 8',
    'first-aid': 'M4 7h16v12H4zM9 7V5h6v2M12 11v4M10 13h4',
    vitamins: 'M12 3v4M12 21a6 6 0 0 1-6-6V9h12v6a6 6 0 0 1-6 6zM6 12h12',
    'baby-care': 'M12 3a9 9 0 1 0 0 18 9 9 0 0 0 0-18zM9 11h.01M15 11h.01M9 15c1.5 1.2 4.5 1.2 6 0',
    skincare: 'M9 3h6v3H9zM8 6h8l1 13a2 2 0 0 1-2 2H9a2 2 0 0 1-2-2L8 6zM8 13h8',
    devices: 'M3 6h18v10H3zM8 20h8M12 16v4M6 11h3l1.5-3 2 6 1.5-3h4',
    supplements: 'M12 3v4M12 21a6 6 0 0 1-6-6V9h12v6a6 6 0 0 1-6 6zM6 12h12',
    default: 'M4 7l8-4 8 4-8 4-8-4zM4 7v10l8 4 8-4V7',
}

const iconFor = (slug) => icons[slug] ?? icons.default
</script>

<template>
    <section v-if="visibleCategories.length"
             class="border-y border-neutral-text/10 bg-white">
        <div class="mx-auto max-w-6xl px-6 py-14">
            <SectionHeading
                title="Browse by category"
                lead=""
                class="[&_h2]:font-heading [&_h2]:text-xl [&_h2]:font-semibold [&_h2]:tracking-tight [&_h2]:text-neutral-text sm:[&_h2]:text-2xl [&_p]:mt-1 [&_p]:text-sm [&_p]:text-neutral-text/60"
            />
            <p class="text-sm font-medium text-neutral-text"></p>

            <!-- Mobile: scroll rail, "View all" as the last swipeable tile -->
            <div class="relative mt-7 md:hidden">
                <div class="category-rail flex gap-3 overflow-x-auto pb-1">
                    <Link
                        v-for="category in visibleCategories"
                        :key="category.id"
                        :href="route('shop.index', { category: category.slug })"
                        :title="category.name"
                        class="group flex w-28 shrink-0 flex-col items-center gap-2.5 py-1"
                    >
                        <span class="relative h-16 w-16 overflow-hidden rounded-xl bg-primary-light ring-1 ring-neutral-text/10 transition-transform duration-200 group-hover:scale-[1.03] group-active:scale-95">
                            <img
                                v-if="category.image_url"
                                :src="category.image_url"
                                :alt="category.name"
                                class="h-full w-full object-cover"
                                loading="lazy"
                            />
                            <span v-else class="flex h-full w-full items-center justify-center text-primary-dark">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path :d="iconFor(category.slug)" />
                                </svg>
                            </span>
                        </span>
                        <span class="text-center text-xs leading-snug text-neutral-text/75">
                            {{ category.name }}
                        </span>
                    </Link>

                    <Link
                        :href="route('shop.index')"
                        title="View all categories"
                        class="group flex w-28 shrink-0 flex-col items-center gap-2.5 py-1"
                    >
                        <span class="flex h-16 w-16 items-center justify-center rounded-xl bg-primary-light ring-1 ring-primary-dark/20 transition-transform duration-200 group-hover:scale-[1.03] group-active:scale-95">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="text-primary-dark" aria-hidden="true">
                                <rect x="3" y="3" width="7" height="7" rx="1.5" />
                                <rect x="14" y="3" width="7" height="7" rx="1.5" />
                                <rect x="3" y="14" width="7" height="7" rx="1.5" />
                                <rect x="14" y="14" width="7" height="7" rx="1.5" />
                            </svg>
                        </span>
                        <span class="text-center text-xs font-medium leading-snug text-primary-dark">
                            View all
                        </span>
                    </Link>
                </div>
                <div class="pointer-events-none absolute inset-y-0 right-0 w-10 bg-gradient-to-l from-white to-transparent" aria-hidden="true"></div>
            </div>

            <!-- Desktop: image-card grid. 5 categories + View all = 6 tiles,
                 which fills xl:grid-cols-6 as one exact row. -->
            <div class="mt-8 hidden grid-cols-3 gap-4 md:grid lg:grid-cols-4 xl:grid-cols-6">
                <Link
                    v-for="category in visibleCategories"
                    :key="category.id"
                    :href="route('shop.index', { category: category.slug })"
                    :title="category.name"
                    class="group overflow-hidden rounded-xl border border-neutral-text/10 bg-white transition-all duration-300 ease-out hover:-translate-y-0.5 hover:border-primary-dark/25 hover:shadow-[0_16px_36px_-14px_rgba(15,23,22,0.18)]"
                >
                    <div class="relative aspect-square w-full overflow-hidden bg-primary-light">
                        <img
                            v-if="category.image_url"
                            :src="category.image_url"
                            :alt="category.name"
                            class="h-full w-full object-cover transition-transform duration-500 ease-out group-hover:scale-105"
                            loading="lazy"
                        />
                        <span v-else class="flex h-full w-full items-center justify-center text-primary-dark">
                            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path :d="iconFor(category.slug)" />
                            </svg>
                        </span>
                        <!-- soft bottom scrim so the label below reads as one composed card, not photo+label glued together -->
                        <span class="pointer-events-none absolute inset-x-0 bottom-0 h-10 bg-gradient-to-t from-black/10 to-transparent opacity-0 transition-opacity duration-300 group-hover:opacity-100" />
                    </div>
                    <div class="px-3.5 py-3">
                        <span class="block truncate text-sm font-medium leading-snug text-neutral-text/85">
                            {{ category.name }}
                        </span>
                    </div>
                </Link>

                <Link
                    :href="route('shop.index')"
                    title="View all categories"
                    class="group flex flex-col overflow-hidden rounded-xl border border-primary-dark/20 bg-primary-light/40 transition-all duration-300 ease-out hover:-translate-y-0.5 hover:border-primary-dark/40 hover:shadow-[0_16px_36px_-14px_rgba(15,23,22,0.18)]"
                >
                    <div class="flex aspect-square w-full items-center justify-center bg-primary-light/60">
                        <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="text-primary-dark transition-transform duration-300 group-hover:scale-105" aria-hidden="true">
                            <rect x="3" y="3" width="7" height="7" rx="1.5" />
                            <rect x="14" y="3" width="7" height="7" rx="1.5" />
                            <rect x="3" y="14" width="7" height="7" rx="1.5" />
                            <rect x="14" y="14" width="7" height="7" rx="1.5" />
                        </svg>
                    </div>
                    <div class="px-3.5 py-3">
                        <span class="block truncate text-sm font-medium leading-snug text-primary-dark">
                            View all categories
                        </span>
                    </div>
                </Link>
            </div>
        </div>
    </section>
</template>

<style scoped>
.category-rail {
    scrollbar-width: none;
}
.category-rail::-webkit-scrollbar {
    display: none;
}
</style>
