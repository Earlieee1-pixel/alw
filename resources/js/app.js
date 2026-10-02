import { createApp, h } from 'vue'
import { createInertiaApp } from '@inertiajs/vue3'
import { createPinia } from 'pinia'
import { ZiggyVue } from '../../vendor/tightenco/ziggy'
import { resolvePageComponent } from './utils/resolvePageComponent'
import '../css/app.css'

// I-boot ang Inertia app — kini ang entry point sa tanan nga pages
createInertiaApp({
    // Titulo sa browser tab — format: "Page | ALW"
    title: (title) => `${title} | ALW`,

    // I-resolve ang tamang Vue component base sa Inertia page name
    resolve: (name) =>
        resolvePageComponent(name, import.meta.glob('./pages/**/*.vue')),

    setup({ el, App, props, plugin }) {
        const pinia = createPinia()

        createApp({ render: () => h(App, props) })
            .use(plugin)    // Inertia plugin
            .use(pinia)     // Pinia state management
            .use(ZiggyVue)  // Ziggy — route() helper sa tanan nga components
            .mount(el)
    },

    // Progress bar sa taas sa screen kung nag-navigate
    progress: {
        color: '#288783',
    },
})
