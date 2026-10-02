import { ref } from 'vue'

// Global toast list — shared across all components
const toasts = ref([])
let nextId   = 0

/**
 * useToast — in-app toast notifications.
 * Auto-dismiss after 4 seconds.
 *
 * Example:
 *   const { toast } = useToast()
 *   toast.success('Saved!')
 *   toast.error('Something went wrong.')
 */
export function useToast() {
    function add(message, type = 'success', duration = 4000) {
        const id = ++nextId
        toasts.value.push({ id, message, type })

        // Auto-remove human ang toast after duration
        setTimeout(() => remove(id), duration)
    }

    function remove(id) {
        toasts.value = toasts.value.filter(t => t.id !== id)
    }

    return {
        toasts,
        remove,
        toast: {
            success: (msg) => add(msg, 'success'),
            error:   (msg) => add(msg, 'error'),
            info:    (msg) => add(msg, 'info'),
            warning: (msg) => add(msg, 'warning'),
        },
    }
}
