<script setup>
import { Head } from '@inertiajs/vue3'

const props = defineProps({
    about: { type: Object, required: true },
    team: { type: Array, default: () => [] },
})

const businessName = 'Stack Pharmacy'
const address = 'Stack Pharmacy, 9 Calabar Road, Obudu 552106, Cross River'
const mapEmbedSrc = 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d990.7075002334345!2d9.164380771769137!3d6.667977153387201!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x105bdfde8d9e617b%3A0xa22e6b4697943f11!2sSTACK%20PHARMACY!5e0!3m2!1sen!2sng!4v1790244087531!5m2!1sen!2sng'
const directionsHref = `https://www.google.com/maps/dir/?api=1&destination=${encodeURIComponent(address)}`
</script>
<template>
    <Head title="About Us" />

    <!-- Hero -->
    <section class="relative flex h-72 items-end bg-gray-900 sm:h-96">
        <img
            v-if="about.image_url"
            :src="about.image_url"
            alt=""
            class="absolute inset-0 h-full w-full object-cover opacity-60"
        />
        <div class="relative z-10 mx-auto w-full max-w-6xl px-6 pb-10">
            <h1 class="text-3xl font-bold text-white sm:text-4xl">About Us</h1>
        </div>
    </section>

    <!-- Story -->
    <section class="bg-white py-16" aria-labelledby="our-story-heading">
        <div class="mx-auto max-w-3xl px-6 text-center">
            <h2 id="our-story-heading" class="text-sm font-semibold uppercase tracking-wide text-emerald-600">Our Story</h2>
            <p class="mt-4 whitespace-pre-line text-lg leading-relaxed text-gray-700">{{ about.story }}</p>
        </div>
    </section>

    <!-- Mission / Vision -->
    <section class="bg-emerald-50 py-16" aria-label="Our mission and vision">
        <div class="mx-auto grid max-w-5xl gap-8 px-6 sm:grid-cols-2">
            <div class="rounded-xl bg-white p-8 shadow-sm">
                <h3 class="text-lg font-semibold text-gray-900">Our Vision</h3>
                <p class="mt-3 text-gray-600">{{ about.vision }}</p>
            </div>
            <div class="rounded-xl bg-white p-8 shadow-sm">
                <h3 class="text-lg font-semibold text-gray-900">Our Mission</h3>
                <p class="mt-3 text-gray-600">{{ about.mission }}</p>
            </div>
        </div>
    </section>

    <!-- Visit Us -->
    <section class="bg-white py-16" aria-labelledby="visit-us-heading">
        <div class="mx-auto max-w-5xl px-6">
            <div class="mb-10 text-center">
                <h2 id="visit-us-heading" class="text-2xl font-bold text-gray-900">Visit Us</h2>
                <p class="mt-2 text-gray-600">Stop by our store — we're easy to find and happy to help in person.</p>
            </div>

            <div class="grid gap-8 md:grid-cols-5 md:items-start">
                <!-- Address + directions -->
                <div class="md:col-span-2">
                    <address class="not-italic">
                        <p class="text-sm font-semibold uppercase tracking-wide text-emerald-600">Our Location</p>
                        <p class="mt-2 text-lg font-semibold text-gray-900">{{ businessName }}</p>
                        <p class="text-lg text-gray-900">9 Calabar Road</p>
                        <p class="text-lg text-gray-900">Obudu 552106, Cross River</p>
                    </address>


                    <a :href="directionsHref"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="mt-6 inline-flex items-center gap-2 rounded-md bg-emerald-600 px-5 py-2.5 text-sm font-medium text-white hover:bg-emerald-700"
                    >
                    Get Directions
                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                    </a>
                </div>

                <!-- Map -->
                <div class="overflow-hidden rounded-lg border border-gray-200 md:col-span-3">
                    <iframe
                        :src="mapEmbedSrc"
                        width="100%"
                        height="320"
                        style="border: 0"
                        allowfullscreen
                        loading="lazy"
                        referrerpolicy="strict-origin-when-cross-origin"
                        :title="`Map showing ${businessName} at 9 Calabar Road, Obudu`"
                    />
                </div>
            </div>
        </div>
    </section>

    <!-- Team -->
    <section v-if="team.length" class="bg-emerald-50 py-16" aria-labelledby="team-heading">
        <div class="mx-auto max-w-5xl px-6">
            <h2 id="team-heading" class="mb-10 text-center text-2xl font-bold text-gray-900">Meet Our Team</h2>

            <div class="flex flex-wrap justify-between gap-8 md:justify-center">
                <div
                    v-for="member in team"
                    :key="member.id"
                    class="w-32 text-center sm:w-36 md:w-40"
                >
                    <img
                        v-if="member.image_url"
                        :src="member.image_url"
                        :alt="member.name"
                        class="mx-auto h-24 w-24 rounded-full object-cover"
                        loading="lazy"
                    />
                    <div v-else class="mx-auto h-24 w-24 rounded-full bg-gray-100" />

                    <p class="mt-3 font-semibold text-gray-900">{{ member.name }}</p>
                    <p class="text-sm text-emerald-600">{{ member.role }}</p>
                    <p v-if="member.bio" class="mt-1 text-xs text-gray-500">{{ member.bio }}</p>
                </div>
            </div>
        </div>
    </section>
</template>
