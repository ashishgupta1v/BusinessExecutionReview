import { defineConfig } from 'vite'
import laravel from 'laravel-vite-plugin'
import vue from '@vitejs/plugin-vue'
import { VitePWA } from 'vite-plugin-pwa'

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        vue({
            template: { transformAssetUrls: { base: null, includeAbsolute: false } },
        }),
        VitePWA({
            registerType: 'autoUpdate',
            injectRegister: 'auto',
            // Generated SW handles precache/offline; our push logic is imported in.
            workbox: {
                importScripts: ['push-sw.js'],
                navigateFallbackDenylist: [/^\/auth/, /^\/push/, /^\/dcr$/],
                globPatterns: ['**/*.{js,css,ico,png,svg,woff2}'],
            },
            includeAssets: ['favicon.ico', 'icons/icon-192.png', 'icons/icon-512.png'],
            manifest: {
                name: 'Execution Cockpit',
                short_name: 'Cockpit',
                description: 'Daily execution discipline — DCR, KPIs, reviews, streaks.',
                theme_color: '#4f46e5',
                background_color: '#0f172a',
                display: 'standalone',
                orientation: 'portrait',
                start_url: '/dcr',
                scope: '/',
                icons: [
                    { src: '/icons/icon-192.png', sizes: '192x192', type: 'image/png' },
                    { src: '/icons/icon-512.png', sizes: '512x512', type: 'image/png' },
                    { src: '/icons/icon-512-maskable.png', sizes: '512x512', type: 'image/png', purpose: 'maskable' },
                ],
            },
        }),
    ],
})
