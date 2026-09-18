<script setup>
import { Link } from '@inertiajs/vue3'
import ShopLayout from "@/Layouts/ShopLayout.vue";

defineProps({
    title: { type: String, default: '' },
    subtitle: { type: String, default: '' },
})

const trustPoints = [
    { icon: 'M12 2l8 4v6c0 5-3.5 8.5-8 10-4.5-1.5-8-5-8-10V6l8-4zM9 12l2 2 4-4', label: 'Genuine, pharmacist-verified medicines' },
    { icon: 'M3 7h11v9H3zM14 10h4l3 3v3h-7zM6.5 20a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3zM17.5 20a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3z', label: 'Fast delivery on OTC & prescriptions' },
    { icon: 'M21 12a8 8 0 0 1-8 8H8l-5 3 1.5-4.5A8 8 0 1 1 21 12z', label: 'Talk to a pharmacist anytime' },
]
</script>

<template>
    <ShopLayout>


    <div class="flex  bg-neutral-bg text-neutral-text">
        <!-- Brand panel — hidden on mobile, form takes the full screen there -->
        <div class="relative hidden w-[42%] flex-col justify-between overflow-hidden bg-primary-dark px-10 py-10 text-white lg:flex xl:w-[38%]">
            <div class="pointer-events-none absolute -right-16 -top-16 h-56 w-56 rounded-full bg-white/5" />
            <div class="pointer-events-none absolute -bottom-24 -left-10 h-64 w-64 rounded-full bg-white/5" />

            <div class="relative">
                <h2 class="font-heading text-2xl font-semibold leading-tight tracking-tight">
                    Your neighborhood pharmacy, online.
                </h2>
                <p class="mt-2.5 max-w-xs text-sm leading-relaxed text-white/70">
                    OTC medicines, prescriptions, and pharmacist-led services — all in one account.
                </p>

                <ul class="mt-8 space-y-3.5">
                    <li v-for="point in trustPoints" :key="point.label" class="flex items-center gap-3 text-sm text-white/85">
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-md bg-white/10">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path :d="point.icon" />
                            </svg>
                        </span>
                        {{ point.label }}
                    </li>
                </ul>
            </div>

            <p class="relative text-xs text-white/40">© {{ new Date().getFullYear() }} Stack Pharmacy</p>
        </div>

        <!-- Form panel -->
        <div class="flex flex-1 flex-col justify-center px-6 py-10 sm:px-6 lg:px-16 xl:px-24">
            <div class="mx-auto w-full max-w-sm">
                <div v-if="title" class="mb-7">
                    <h1 class="font-heading text-xl font-semibold tracking-tight text-neutral-text sm:text-2xl">{{ title }}</h1>
                    <p v-if="subtitle" class="mt-1.5 text-sm text-neutral-text/60">{{ subtitle }}</p>
                </div>

                <slot />
            </div>
        </div>
    </div>
    </ShopLayout>
</template>
