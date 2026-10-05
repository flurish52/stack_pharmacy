import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    plugins: [
        laravel({
            input: 'resources/js/app.js',
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],

    server: {
        host: '0.0.0.0', // still binds to all interfaces, fine for Laragon
        port: 5173,
        cors: true,
        hmr: {
            host: 'localhost', // <- was the hardcoded IP; now stable regardless of network
        },
    },
});
