import '../css/app.css'

import { createApp, h } from 'vue'
import { createInertiaApp } from '@inertiajs/vue3'
import { ZiggyVue } from '../../vendor/tightenco/ziggy'
import ShopLayout from '@/Layouts/ShopLayout.vue'
import AuthLayout from '@/Layouts/AuthLayout.vue'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const appName = import.meta.env.VITE_APP_NAME || 'Stack Pharmacy'
const pages = import.meta.glob('./pages/**/*.vue')

if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js').catch((err) => {
            console.error('SW registration failed:', err)
        })
    })
}

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),

    resolve: async (name) => {
        const page = pages[`./pages/${name}.vue`]

        if (!page) {
            throw new Error(`Page not found: ./pages/${name}.vue`)
        }

        const module = await page()

        module.default.layout = module.default.layout || (() => {
            switch (true) {
                case name === 'Welcome':
                    return ShopLayout
                case name.startsWith('Auth/'):
                    return AuthLayout
                case name.startsWith('Admin/'):
                    return AdminLayout
                case name.startsWith('Shop/'):
                    return ShopLayout
                case name.startsWith('Cart'):
                    return ShopLayout
                case name.startsWith('Checkout'):
                    return ShopLayout
                case name.startsWith('Services/'):
                    return ShopLayout
                case name.startsWith('Training'):
                    return ShopLayout
                case name.startsWith('Contact'):
                    return ShopLayout
                default:
                    return ShopLayout
            }
        })()

        return module
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
