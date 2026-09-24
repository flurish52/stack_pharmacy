<script setup>
import { Link } from '@inertiajs/vue3'

defineProps({
    about: { type: Object, required: true },
})
</script>

<template>
    <section class="bg-white py-10">
        <div class="mx-auto grid max-w-6xl gap-10 px-6 md:grid-cols-2 md:items-center">
            <div>
                <h3 class="text-sm font-semibold uppercase tracking-wide text-emerald-600">About Us</h3>
                <h2 class="mt-2 text-3xl font-bold text-gray-900">Who we are</h2>
                <p class="mt-4 line-clamp-4 text-gray-600">{{ about.story }}</p>
                <Link
                    :href="route('about-us')"
                    class="mt-6 inline-flex items-center rounded-md bg-emerald-600 px-5 py-2.5 text-sm font-medium text-white hover:bg-emerald-700"
                >
                    Learn more about us
                </Link>
            </div>

            <img
                v-if="about.image_url"
                :src="about.image_url"
                alt="About us"
                class="aspect-[4/3] w-full rounded-xl object-cover"
                loading="lazy"
            />
        </div>
    </section>
</template>
