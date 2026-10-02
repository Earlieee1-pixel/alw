/**
 * I-resolve ang Vue page component base sa Inertia page name.
 * Gihulat ang lazy-loaded component before i-render.
 *
 * @param {string} name - Inertia page name (e.g. 'Landing', 'Auth/Login')
 * @param {Object} pages - Glob import sa tanan nga pages
 * @returns {Promise} Vue component
 */
export function resolvePageComponent(name, pages) {
    // Pretend ang name nga naay slash path (e.g. Auth/Login -> ./pages/Auth/Login.vue)
    const path = `./pages/${name}.vue`
    const page = pages[path]

    if (!page) {
        throw new Error(`Wala makit-an ang page component: ${path}`)
    }

    // I-unwrap ang default export kung naa
    return typeof page === 'function' ? page() : page
}
