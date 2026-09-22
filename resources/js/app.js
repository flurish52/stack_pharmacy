import '../css/app.css'

import { createApp, h } from 'vue'
import { createInertiaApp } from '@inertiajs/vue3'
import { ZiggyVue } from '../../vendor/tightenco/ziggy'
import ShopLayout from '@/Layouts/ShopLayout.vue'
import GuestLayout from "@/Layouts/GuestLayout.vue";
import AccountLayout from "@/Layouts/AccountLayout.vue";

const appName = import.meta.env.VITE_APP_NAME || 'Stack Pharmacy'
// const pages = import.meta.glob('./pages/**/*.vue')
const pages = import.meta.glob('./pages/**/*.vue', { eager: true })

if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js').catch((err) => {
            console.error('SW registration failed:', err)
        })
    })
}

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),

    resolve: (name) => {
        const page = pages[`./pages/${name}.vue`]

        if (!page) {
            throw new Error(`Page not found: ./pages/${name}.vue`)
        }

        page.default.layout = page.default.layout || (() => {
            switch (true) {
                case name === 'Welcome':
                    return ShopLayout
                case name.startsWith('Auth/'):
                    return GuestLayout
                case name.startsWith('Admin/'):
                    return null
                case name.startsWith('Shop/'):
                    return ShopLayout
                case name.startsWith('Cart/'):
                    return ShopLayout
                case name.startsWith('Checkout/'):
                    return ShopLayout
                case name.startsWith('Services/'):
                    return ShopLayout
                case name.startsWith('Training/'):
                    return ShopLayout
                case name.startsWith('ContactChannel/'):
                    return ShopLayout
                case name.startsWith('Dashboard'):
                    return AccountLayout
                case name.startsWith('Account/'):
                    return AccountLayout
                default:
                    return ShopLayout
            }
        })()

        return page
    },

    setup({ el, App, props, plugin }) {
        createApp({
            render: () => h(App, props),
        })
            .use(plugin)
            .use(ZiggyVue)
            .mount(el)
    },

    progress: {
        color: '#0F766E',
    },
})
