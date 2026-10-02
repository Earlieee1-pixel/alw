import { defineConfig } from 'vite'
import laravel from 'laravel-vite-plugin'
import vue from '@vitejs/plugin-vue'
import tailwindcss from '@tailwindcss/vite'

export default defineConfig({
    plugins: [
        // Laravel Vite plugin — nag-handle sa hot reload ug asset versioning
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        // Vue single-file components (.vue files)
        vue(),
        // Tailwind CSS v4 — walay config file, integrated sa Vite
        tailwindcss(),
    ],
    server: {
        watch: {
            // Dili i-watch ang compiled blade views para dili mag-loop
            ignored: ['**/storage/framework/views/**'],
        },
    },
})
