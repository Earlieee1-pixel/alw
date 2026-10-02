<script setup>
import { useConfirm } from '@/composables/useConfirm'
import { ExclamationTriangleIcon, XMarkIcon } from '@heroicons/vue/24/outline'

// I-connect sa global confirm state
const { isOpen, title, message, confirmLabel, cancelLabel, isDanger, onConfirm, onCancel } = useConfirm()
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transition duration-150 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition duration-100 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="isOpen"
                class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-teal-900/50 backdrop-blur-sm"
                @click.self="onCancel"
            >
                <Transition
                    enter-active-class="transition duration-150 ease-out"
                    enter-from-class="opacity-0 scale-95"
                    enter-to-class="opacity-100 scale-100"
                >
                    <div
                        v-if="isOpen"
                        class="bg-cream-200 rounded-2xl shadow-xl w-full max-w-sm border border-teal-100"
                    >
                        <!-- Header -->
                        <div class="flex items-start justify-between px-6 pt-5 pb-4">
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0"
                                    :class="isDanger ? 'bg-red-100' : 'bg-teal-100'"
                                >
                                    <ExclamationTriangleIcon
                                        class="w-5 h-5"
                                        :class="isDanger ? 'text-red-500' : 'text-teal-500'"
                                    />
                                </div>
                                <h3 class="text-sm font-bold text-teal-800">{{ title }}</h3>
                            </div>
                            <button
                                class="p-1 rounded-lg text-teal-400 hover:bg-teal-100 transition-colors"
                                @click="onCancel"
                            >
                                <XMarkIcon class="w-4 h-4" />
                            </button>
                        </div>

                        <!-- Body -->
                        <div class="px-6 pb-5">
                            <p class="text-sm text-teal-600 leading-relaxed">{{ message }}</p>
                        </div>

                        <!-- Actions -->
                        <div class="flex gap-3 px-6 pb-6">
                            <button
                                class="flex-1 py-2.5 rounded-xl border border-teal-200 text-sm text-teal-600 font-semibold hover:bg-teal-50 transition-colors"
                                @click="onCancel"
                            >
                                {{ cancelLabel }}
                            </button>
                            <button
                                class="flex-1 py-2.5 rounded-xl text-sm font-semibold text-white transition-colors"
                                :class="isDanger
                                    ? 'bg-red-500 hover:bg-red-600'
                                    : 'bg-teal-500 hover:bg-teal-600'"
                                @click="onConfirm"
                            >
                                {{ confirmLabel }}
                            </button>
                        </div>
                    </div>
                </Transition>
            </div>
        </Transition>
    </Teleport>
</template>
