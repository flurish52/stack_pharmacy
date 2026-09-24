<script setup>
/**
 * Home page. Composition only — every section owns its own markup, so this
 * file stays readable and sections can be reordered or reused elsewhere.
 */
import { Head } from '@inertiajs/vue3'
import HeroSection from '@/Components/Welcome/HeroSection.vue'
import TrustStrip from '@/Components/Welcome/TrustStrip.vue'
import ServicesSection from '@/Components/Welcome/ServicesSection.vue'
import CategoryStrip from '@/Components/Welcome/CategoryStrip.vue'
import FeaturedProducts from '@/Components/Welcome/FeaturedProducts.vue'
import TrainingTeaser from '@/Components/Welcome/TrainingTeaser.vue'
import WhatsappBanner from '@/Components/Welcome/WhatsappBanner.vue'
import AboutTeaser from '@/Components/Welcome/AboutTeaser.vue'

defineProps({
    services: { type: Array, default: () => [] },
    categories: { type: Array, default: () => [] },
    featuredProducts: { type: Array, default: () => [] },
    training: { type: Object, default: null },
    aboutPage: { type: Object, required: true },
})
</script>

<template>
    <Head title="Stack Pharmacy" />

    <div>
        <HeroSection
            :shop-href="route('shop.index')"
            :whatsapp-href="`https://wa.me/${$page.props.pharmacyWhatsapp}`"
            background-image-src="/storage/assets/stack_pharmacy.png"
        />

        <TrustStrip class="relative z-10 -mt-10 md:-mt-14" />

        <AboutTeaser :about="aboutPage" />

        <ServicesSection :services="services" />

        <CategoryStrip :categories="categories" />

        <FeaturedProducts :products="featuredProducts" />

        <TrainingTeaser :training="training" />

        <WhatsappBanner />
    </div>
</template>
