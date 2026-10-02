<script setup>
import { usePage } from '@inertiajs/vue3'
import { useToast } from '@/composables/useToast'
import { watch } from 'vue'
import {
    CheckCircleIcon,
    ExclamationCircleIcon,
    InformationCircleIcon,
    ExclamationTriangleIcon,
    XMarkIcon,
} from '@heroicons/vue/24/outline'

const { toasts, remove, toast } = useToast()
const page = usePage()

// I-watch ang flash messages gikan sa Laravel — auto-show as toast
watch(
    () => page.props.flash,
    (flash) => {
        if (flash?.success) toast.success(flash.success)
        if (flash?.error)   toast.error(flash.error)
    },
    { deep: true, immediate: true }
)

// Toast icon ug color base sa type
function toastStyle(type) {
    return {
        success: { icon: CheckCircleIcon,          bg: 'bg-green-50',   border: 'border-green-200', text: 'text-green-800', icon_color: 'text-green-500' },
        error:   { icon: ExclamationCircleIcon,    bg: 'bg-red-50',     border: 'border-red-200',   text: 'text-red-800',   icon_color: 'text-red-500'   },
        info:    { icon: InformationCircleIcon,    bg: 'bg-teal-50',    border: 'border-teal-200',  text: 'text-teal-800',  icon_color: 'text-teal-500'  },
        warning: { icon: ExclamationTriangleIcon,  bg: 'bg-amber-50',   border: 'border-amber-200', text: 'text-amber-800', icon_color: 'text-amber-500' },
    }[type] ?? { icon: InformationCircleIcon, bg: 'bg-teal-50', border: 'border-teal-200', text: 'text-teal-800', icon_color: 'text-teal-500' }
}
</script>

<template>
    <!-- Fixed top-right toast stack -->
    <Teleport to="body">
        <div class="fixed top-4 right-4 z-[200] flex flex-col gap-2 w-80 pointer-events-none">
            <TransitionGroup
                enter-active-class="transition duration-300 ease-out"
                enter-from-class="opacity-0 translate-x-4"
                enter-to-class="opacity-100 translate-x-0"
                leave-active-class="transition duration-200 ease-in"
                leave-from-class="opacity-100 translate-x-0"
                leave-to-class="opacity-0 translate-x-4"
            >
                <div
                    v-for="t in toasts"
                    :key="t.id"
                    class="pointer-events-auto flex items-start gap-3 px-4 py-3 rounded-2xl border shadow-lg"
                    :class="[toastStyle(t.type).bg, toastStyle(t.type).border]"
                >
                    <component
                        :is="toastStyle(t.type).icon"
                        class="w-5 h-5 shrink-0 mt-0.5"
                        :class="toastStyle(t.type).icon_color"
                    />
                    <p
                        class="flex-1 text-sm font-medium leading-snug"
                        :class="toastStyle(t.type).text"
                    >
                        {{ t.message }}
                    </p>
                    <button
                        class="shrink-0 p-0.5 rounded-lg opacity-60 hover:opacity-100 transition-opacity"
                        :class="toastStyle(t.type).text"
                        @click="remove(t.id)"
                    >
                        <XMarkIcon class="w-3.5 h-3.5" />
                    </button>
                </div>
            </TransitionGroup>
        </div>
    </Teleport>
</template>
