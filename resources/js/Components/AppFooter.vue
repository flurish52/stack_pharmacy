<script setup>
import {Link, usePage} from '@inertiajs/vue3'
import ApplicationLogo from "@/Components/ApplicationLogo.vue";

const page = usePage()
</script>

<template>
    <footer class="bg-primary-dark">
        <div class="mx-auto grid max-w-6xl gap-8 px-4 py-10 sm:px-6 md:grid-cols-4 md:py-12">
            <div>
                <Link :href="route('pharm.home')"
                      class="flex items-center gap-2 font-heading text-base font-semibold text-white">
 <span
     class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full  text-white shadow-sm transition-transform duration-200 ease-out group-hover:scale-105 group-active:scale-95 motion-reduce:transition-none sm:h-10 sm:w-10"
 >
        <ApplicationLogo :size="20"/>
    </span>
                    Stack Pharmacy
                </Link>
                <p class="mt-2.5 max-w-[22ch] text-sm leading-relaxed text-white/60">
                    Your neighborhood pharmacy, online.
                </p>
            </div>

            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-white/45">Shop</p>
                <div class="mt-3 flex flex-col gap-2 text-sm text-white/65">
                    <Link :href="route('shop.index')" class="w-fit transition-colors hover:text-white">All products
                    </Link>
                    <Link :href="route('services.index')" class="w-fit transition-colors hover:text-white">Services
                    </Link>
                    <Link :href="route('training.index')" class="w-fit transition-colors hover:text-white">Training
                    </Link>
                    <Link :href="route('track.index')" class="w-fit transition-colors hover:text-white">Track order
                    </Link>
                </div>
            </div>

            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-white/45">Get in touch</p>
                <div class="mt-3 flex flex-col gap-2 text-sm text-white/65">
                    <Link :href="route('contact.index')" class="w-fit transition-colors hover:text-white">Contact
                        channels
                    </Link>


                    <a :href="`https://wa.me/${page.props.pharmacyWhatsapp}`"
                       target="_blank"
                       rel="noopener"
                       class="inline-flex w-fit items-center gap-1.5 text-whatsapp transition-colors hover:text-whatsapp/80"
                    >
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                             stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M21 12a8 8 0 0 1-8 8H8l-5 3 1.5-4.5A8 8 0 1 1 21 12z"/>
                        </svg>
                        Chat on WhatsApp
                    </a>
                </div>
            </div>

            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-white/45">Hours</p>
                <p class="mt-3 text-sm text-white/65">Mon–Sat, 8am–8pm</p>
            </div>
        </div>

        <div class="border-t border-white/10 px-4 py-4 text-center text-xs text-white/45 sm:px-6">
            © {{ new Date().getFullYear() }} Stack Pharmacy. All rights reserved.
        </div>
    </footer>
</template>
