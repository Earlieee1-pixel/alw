<script setup>
import { router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import {
    BellIcon,
    CheckCircleIcon,
    ChevronLeftIcon,
    ChevronRightIcon,
} from '@heroicons/vue/24/outline'
import { CheckCircleIcon as CheckSolid } from '@heroicons/vue/24/solid'

const props = defineProps({
    notifications: Object,
})

function notifTypeColor(type) {
    return {
        new_downline:   'bg-teal-500',
        account_status: 'bg-green-500',
        new_video:      'bg-purple-500',
        new_room:       'bg-blue-500',
        tree_name_set:  'bg-orange-500',
    }[type] ?? 'bg-gray-400'
}

function markRead(id) {
    router.post(route('notifications.read', id), {}, { preserveScroll: true })
}

function markAllRead() {
    router.post(route('notifications.read-all'), {}, { preserveScroll: true })
}

function visitNotif(notif) {
    if (! notif.read) markRead(notif.id)
    router.visit(notif.url)
}
</script>

<template>
    <AppLayout>
        <template #header>
            <h1 class="text-base font-semibold text-teal-800">Notifications</h1>
        </template>

        <div class="max-w-3xl mx-auto space-y-4">

            <!-- Toolbar -->
            <div class="flex items-center justify-between">
                <p class="text-sm text-teal-500">
                    {{ notifications.total }} notification{{ notifications.total !== 1 ? 's' : '' }}
                </p>
                <button
                    class="flex items-center gap-1.5 text-xs font-semibold text-teal-500 hover:text-teal-700 px-3 py-2 rounded-xl hover:bg-teal-50 transition-colors border border-teal-200"
                    @click="markAllRead"
                >
                    <CheckSolid class="w-3.5 h-3.5" />
                    Mark all as read
                </button>
            </div>

            <!-- Notifications list -->
            <div class="bg-cream-200 rounded-2xl border border-teal-100 overflow-hidden divide-y divide-teal-50">

                <div v-if="notifications.data.length === 0" class="flex flex-col items-center justify-center py-16 gap-3">
                    <BellIcon class="w-10 h-10 text-teal-300" />
                    <p class="text-sm text-teal-400">You're all caught up. No notifications.</p>
                </div>

                <div
                    v-for="notif in notifications.data"
                    :key="notif.id"
                    class="flex items-start gap-4 px-5 py-4 cursor-pointer transition-colors"
                    :class="notif.read ? 'bg-white hover:bg-teal-50/30' : 'bg-teal-50 hover:bg-teal-100/40'"
                    @click="visitNotif(notif)"
                >
                    <!-- Color avatar -->
                    <div
                        class="w-9 h-9 rounded-full flex items-center justify-center text-xs font-bold text-white shrink-0 mt-0.5"
                        :class="notifTypeColor(notif.type)"
                    >
                        {{ notif.avatar }}
                    </div>

                    <div class="flex-1 min-w-0">
                        <div class="flex items-start justify-between gap-3">
                            <p class="text-sm font-semibold text-teal-800">{{ notif.title }}</p>
                            <div class="flex items-center gap-2 shrink-0">
                                <div v-if="! notif.read" class="w-2 h-2 rounded-full bg-teal-500" />
                                <span class="text-xs text-teal-400">{{ notif.created_at }}</span>
                            </div>
                        </div>
                        <p class="text-sm text-teal-500 mt-0.5">{{ notif.message }}</p>
                    </div>

                    <!-- Mark read button -->
                    <button
                        v-if="! notif.read"
                        class="shrink-0 p-1.5 rounded-lg text-teal-400 hover:text-teal-600 hover:bg-teal-100 transition-colors"
                        title="Mark as read"
                        @click.stop="markRead(notif.id)"
                    >
                        <CheckCircleIcon class="w-4 h-4" />
                    </button>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="notifications.last_page > 1" class="flex items-center justify-between">
                <p class="text-xs text-teal-500">
                    Showing {{ notifications.from }}–{{ notifications.to }} of {{ notifications.total }}
                </p>
                <div class="flex items-center gap-1">
                    <a
                        v-if="notifications.prev_page_url"
                        :href="notifications.prev_page_url"
                        class="p-1.5 rounded-lg border border-teal-200 text-teal-500 hover:bg-teal-50"
                    >
                        <ChevronLeftIcon class="w-4 h-4" />
                    </a>
                    <span class="px-3 py-1.5 text-xs font-semibold text-teal-700">
                        {{ notifications.current_page }} / {{ notifications.last_page }}
                    </span>
                    <a
                        v-if="notifications.next_page_url"
                        :href="notifications.next_page_url"
                        class="p-1.5 rounded-lg border border-teal-200 text-teal-500 hover:bg-teal-50"
                    >
                        <ChevronRightIcon class="w-4 h-4" />
                    </a>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
