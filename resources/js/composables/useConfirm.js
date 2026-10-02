import { ref } from 'vue'

// Global confirm modal state — shared across tanan nga components
const isOpen   = ref(false)
const title    = ref('')
const message  = ref('')
const confirmLabel = ref('Confirm')
const cancelLabel  = ref('Cancel')
const isDanger = ref(true)

let resolveFn = null

/**
 * useConfirm — replacement sa browser confirm() dialog.
 * Returns a Promise nga mag-resolve sa true (confirm) o false (cancel).
 *
 * Example:
 *   const confirmed = await confirm('Delete this?', { title: 'Are you sure?' })
 *   if (confirmed) { ... }
 */
export function useConfirm() {
    function confirm(msg, options = {}) {
        message.value      = msg
        title.value        = options.title        ?? 'Are you sure?'
        confirmLabel.value = options.confirmLabel ?? 'Confirm'
        cancelLabel.value  = options.cancelLabel  ?? 'Cancel'
        isDanger.value     = options.danger       ?? true
        isOpen.value       = true

        return new Promise((resolve) => {
            resolveFn = resolve
        })
    }

    function onConfirm() {
        isOpen.value = false
        resolveFn?.(true)
    }

    function onCancel() {
        isOpen.value = false
        resolveFn?.(false)
    }

    return {
        // State para sa ConfirmModal component
        isOpen,
        title,
        message,
        confirmLabel,
        cancelLabel,
        isDanger,
        // Functions
        confirm,
        onConfirm,
        onCancel,
    }
}
