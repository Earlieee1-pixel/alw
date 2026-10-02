<script setup>
import { ref, computed } from 'vue'
import { Link, useForm, usePage, router } from '@inertiajs/vue3'
import ConfirmModal from '@/components/ConfirmModal.vue'
import ToastStack from '@/components/ToastStack.vue'
import {
    HomeIcon,
    UsersIcon,
    UserGroupIcon,
    AcademicCapIcon,
    VideoCameraIcon,
    Cog6ToothIcon,
    Bars3Icon,
    XMarkIcon,
    GlobeAltIcon,
    ChevronDownIcon,
    ArrowRightStartOnRectangleIcon,
    ShieldCheckIcon,
    BellIcon,
    LinkIcon,
    CheckIcon,
    ChatBubbleLeftRightIcon,
} from '@heroicons/vue/24/outline'
import { ShieldCheckIcon as ShieldSolid } from '@heroicons/vue/24/solid'

// Makuha ang shared props gikan sa HandleInertiaRequests
const page       = usePage()
const user       = computed(() => page.props.auth?.user)
const roles      = computed(() => user.value?.roles ?? [])
const notifCount = computed(() => page.props.notifCount ?? 0)

// Role checks — gamiton sa template para i-filter ang nav items
const isAdmin  = computed(() => roles.value.includes('admin'))
const isLeader = computed(() => roles.value.includes('leader') || isAdmin.value)

// Mobile sidebar toggle
const sidebarOpen = ref(false)

// User dropdown toggle
const userMenuOpen = ref(false)

// Notification bell dropdown
const notifOpen        = ref(false)
const recentNotifs     = ref([])
const notifLoaded      = ref(false)

async function openNotifDropdown() {
    notifOpen.value = ! notifOpen.value
    if (notifOpen.value && ! notifLoaded.value) {
        // I-fetch ang recent notifications via JSON
        try {
            const res  = await fetch(route('notifications.recent'))
            const data = await res.json()
            recentNotifs.value = data.notifications
            notifLoaded.value  = true
        } catch {
            recentNotifs.value = []
        }
    }
}

function markRead(id) {
    router.post(route('notifications.read', id), {}, {
        preserveScroll: true,
        onSuccess: () => {
            const n = recentNotifs.value.find(n => n.id === id)
            if (n) n.read = true
            notifLoaded.value = false // I-refresh next time
        },
    })
}

function markAllRead() {
    router.post(route('notifications.read-all'), {}, {
        preserveScroll: true,
        onSuccess: () => {
            recentNotifs.value.forEach(n => n.read = true)
            notifLoaded.value = false
            notifOpen.value   = false
        },
    })
}

function notifTypeColor(type) {
    return {
        new_downline:    'bg-teal-500',
        account_status:  'bg-green-500',
        new_video:       'bg-purple-500',
        new_room:        'bg-blue-500',
        tree_name_set:   'bg-orange-500',
    }[type] ?? 'bg-gray-400'
}

// Logout form
const logoutForm = useForm({})
function logout() {
    logoutForm.post(route('logout'))
}

// Nav items — filtered base sa role
const navItems = computed(() => {
    const items = [
        {
            label: 'Dashboard',
            route: 'dashboard',
            icon:  HomeIcon,
            roles: ['admin', 'leader', 'member'],
        },
        {
            label: 'My Network',
            route: 'network.tree',
            icon:  UserGroupIcon,
            roles: ['admin', 'leader', 'member'],
        },
        {
            label: 'Training Videos',
            route: 'videos.index',
            icon:  AcademicCapIcon,
            roles: ['admin', 'leader', 'member'],
        },
        {
            label: 'Video Calls',
            route: 'calls.index',
            icon:  VideoCameraIcon,
            roles: ['admin', 'leader', 'member'],
        },
        {
            label: 'Messages',
            route: 'messages.index',
            icon:  ChatBubbleLeftRightIcon,
            roles: ['admin', 'leader', 'member'],
        },
        {
            label: 'Notifications',
            route: 'notifications.index',
            icon:  BellIcon,
            roles: ['admin', 'leader', 'member'],
        },
    ]

    // Admin-only nav items
    const adminItems = [
        {
            label: 'All Members',
            route: 'admin.members.index',
            icon:  UsersIcon,
            roles: ['admin'],
        },
        {
            label: 'Settings',
            route: 'admin.settings',
            icon:  Cog6ToothIcon,
            roles: ['admin'],
        },
    ]

    // I-combine base sa role sa user
    const allItems = isAdmin.value ? [...items, ...adminItems] : items

    // I-filter — ipakita lang ang items nga allowed sa current role
    return allItems.filter(item =>
        item.roles.some(r => roles.value.includes(r))
    )
})

// Check kung active ang current route
function isActive(routeName) {
    try {
        return route().current(routeName) || route().current(routeName + '.*')
    } catch {
        return false
    }
}
</script>

<template>
    <div class="min-h-screen bg-cream-50 flex">

        <!-- ===================== SIDEBAR ===================== -->

        <!-- Mobile overlay -->
        <div
            v-if="sidebarOpen"
            class="fixed inset-0 z-20 bg-teal-900/50 lg:hidden"
            @click="sidebarOpen = false"
        />

        <!-- Sidebar -->
        <aside
            class="fixed inset-y-0 left-0 z-30 w-64 bg-teal-800 flex flex-col transition-transform duration-200 lg:translate-x-0 lg:static lg:z-auto"
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
        >
            <!-- Logo -->
            <div class="flex items-center gap-3 px-5 h-16 border-b border-teal-700/60 shrink-0">
                <div class="w-8 h-8 rounded-lg bg-teal-500 flex items-center justify-center shadow-sm">
                    <GlobeAltIcon class="w-4 h-4 text-cream-200" />
                </div>
                <div>
                    <div class="text-sm font-bold text-cream-200 leading-none">ALW</div>
                    <div class="text-[10px] text-teal-400 tracking-widest uppercase leading-tight">
                        Atomic Legendary Warriors
                    </div>
                </div>
                <!-- Close button on mobile -->
                <button
                    class="ml-auto lg:hidden text-teal-400 hover:text-cream-200 transition-colors"
                    @click="sidebarOpen = false"
                    aria-label="Close menu"
                >
                    <XMarkIcon class="w-5 h-5" />
                </button>
            </div>

            <!-- Admin badge -->
            <div v-if="isAdmin" class="mx-4 mt-4 flex items-center gap-2 px-3 py-2 rounded-lg bg-teal-700/50 border border-teal-600/30">
                <ShieldSolid class="w-3.5 h-3.5 text-teal-400 shrink-0" />
                <span class="text-xs font-semibold text-teal-300 uppercase tracking-wide">Admin Access</span>
            </div>

            <!-- Nav links -->
            <nav class="flex-1 px-3 py-4 space-y-0.5 overflow-y-auto">

                <!-- Divider label for admin section -->
                <template v-for="(item, index) in navItems" :key="item.route">

                    <!-- Insert divider before admin-only items -->
                    <div
                        v-if="isAdmin && item.route === 'admin.members.index' && index > 0"
                        class="pt-3 pb-1 px-2"
                    >
                        <span class="text-[10px] font-semibold text-teal-500 uppercase tracking-widest">
                            Admin
                        </span>
                    </div>

                    <Link
                        :href="route(item.route)"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-colors group"
                        :class="isActive(item.route)
                            ? 'bg-teal-500 text-cream-200 shadow-sm'
                            : 'text-teal-300 hover:bg-teal-700/60 hover:text-cream-200'"
                        @click="sidebarOpen = false"
                    >
                        <component
                            :is="item.icon"
                            class="w-4 h-4 shrink-0 transition-colors"
                            :class="isActive(item.route) ? 'text-cream-200' : 'text-teal-400 group-hover:text-cream-200'"
                        />
                        {{ item.label }}
                    </Link>
                </template>
            </nav>

            <!-- User section at bottom of sidebar -->
            <div class="px-3 pb-4 pt-2 border-t border-teal-700/60 space-y-1 shrink-0">

                <!-- Invite link shortcut -->
                <Link
                    :href="route('profile.index')"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-teal-300 hover:bg-teal-700/60 hover:text-cream-200 transition-colors group"
                >
                    <LinkIcon class="w-4 h-4 text-teal-400 group-hover:text-cream-200 shrink-0" />
                    My Profile
                </Link>

                <!-- Logout -->
                <button
                    class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-teal-300 hover:bg-red-500/20 hover:text-red-300 transition-colors group"
                    @click="logout"
                >
                    <ArrowRightStartOnRectangleIcon class="w-4 h-4 text-teal-400 group-hover:text-red-300 shrink-0" />
                    Sign Out
                </button>
            </div>
        </aside>

        <!-- ===================== MAIN AREA ===================== -->
        <div class="flex-1 flex flex-col min-w-0">

            <!-- Top header -->
            <header class="h-16 bg-cream-200 border-b border-teal-100 flex items-center px-4 lg:px-6 gap-4 shrink-0 sticky top-0 z-10">

                <!-- Mobile menu toggle -->
                <button
                    class="lg:hidden p-2 rounded-lg text-teal-600 hover:bg-teal-100 transition-colors"
                    @click="sidebarOpen = true"
                    aria-label="Open menu"
                >
                    <Bars3Icon class="w-5 h-5" />
                </button>

                <!-- Page title slot -->
                <div class="flex-1 min-w-0">
                    <slot name="header">
                        <h1 class="text-base font-semibold text-teal-800 truncate">Dashboard</h1>
                    </slot>
                </div>

                <!-- Right side — notifications + user -->
                <div class="flex items-center gap-2">

                    <!-- Notification bell with dropdown -->
                    <div class="relative">
                        <button
                            class="relative p-2 rounded-lg text-teal-500 hover:bg-teal-100 transition-colors"
                            @click="openNotifDropdown"
                            aria-label="Notifications"
                        >
                            <BellIcon class="w-5 h-5" />
                            <!-- Unread badge -->
                            <span
                                v-if="notifCount > 0"
                                class="absolute top-1 right-1 w-4 h-4 rounded-full bg-red-500 text-white text-[9px] font-bold flex items-center justify-center"
                            >
                                {{ notifCount > 9 ? '9+' : notifCount }}
                            </span>
                        </button>

                        <!-- Notification dropdown -->
                        <div
                            v-if="notifOpen"
                            class="absolute right-0 top-full mt-1 w-80 bg-white rounded-2xl shadow-xl border border-teal-100 z-30 overflow-hidden"
                            @mouseleave="notifOpen = false"
                        >
                            <!-- Header -->
                            <div class="flex items-center justify-between px-4 py-3 border-b border-teal-50">
                                <span class="text-sm font-bold text-teal-800">Notifications</span>
                                <button
                                    v-if="notifCount > 0"
                                    class="text-xs text-teal-500 hover:text-teal-700 font-medium transition-colors"
                                    @click="markAllRead"
                                >
                                    Mark all read
                                </button>
                            </div>

                            <!-- List -->
                            <div class="max-h-80 overflow-y-auto divide-y divide-teal-50">
                                <div v-if="! notifLoaded" class="flex items-center justify-center py-8">
                                    <div class="w-5 h-5 border-2 border-teal-400 border-t-transparent rounded-full animate-spin" />
                                </div>

                                <div v-else-if="recentNotifs.length === 0" class="py-8 text-center text-sm text-teal-400">
                                    No notifications yet.
                                </div>

                                <div
                                    v-else
                                    v-for="notif in recentNotifs"
                                    :key="notif.id"
                                    class="flex items-start gap-3 px-4 py-3 cursor-pointer transition-colors"
                                    :class="notif.read ? 'bg-white hover:bg-teal-50/50' : 'bg-teal-50 hover:bg-teal-100/50'"
                                    @click="markRead(notif.id); notifOpen = false; $router?.visit(notif.url)"
                                >
                                    <!-- Avatar dot -->
                                    <div
                                        class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold text-white shrink-0 mt-0.5"
                                        :class="notifTypeColor(notif.type)"
                                    >
                                        {{ notif.avatar }}
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-start justify-between gap-2">
                                            <p class="text-xs font-semibold text-teal-800 leading-snug">{{ notif.title }}</p>
                                            <div v-if="! notif.read" class="w-2 h-2 rounded-full bg-teal-500 shrink-0 mt-1" />
                                        </div>
                                        <p class="text-xs text-teal-500 mt-0.5 line-clamp-2">{{ notif.message }}</p>
                                        <p class="text-[10px] text-teal-400 mt-1">{{ notif.created_at }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Footer -->
                            <div class="border-t border-teal-50 px-4 py-2.5">
                                <Link
                                    :href="route('notifications.index')"
                                    class="text-xs text-teal-500 hover:text-teal-700 font-medium transition-colors"
                                    @click="notifOpen = false"
                                >
                                    View all notifications →
                                </Link>
                            </div>
                        </div>
                    </div>

                    <!-- User menu -->
                    <div class="relative">
                        <button
                            class="flex items-center gap-2.5 px-3 py-1.5 rounded-xl hover:bg-teal-100 transition-colors"
                            @click="userMenuOpen = !userMenuOpen"
                        >
                            <!-- Avatar initials -->
                            <div class="w-7 h-7 rounded-full bg-teal-500 flex items-center justify-center text-xs font-bold text-cream-200 shrink-0">
                                {{ user?.name?.charAt(0)?.toUpperCase() }}
                            </div>
                            <div class="hidden sm:block text-left">
                                <div class="text-xs font-semibold text-teal-800 leading-none">{{ user?.name }}</div>
                                <div class="text-[10px] text-teal-500 mt-0.5 flex items-center gap-1">
                                    <ShieldCheckIcon v-if="isAdmin" class="w-2.5 h-2.5" />
                                    <span class="capitalize">{{ roles[0] }}</span>
                                </div>
                            </div>
                            <ChevronDownIcon class="w-3.5 h-3.5 text-teal-400 hidden sm:block" />
                        </button>

                        <!-- Dropdown -->
                        <div
                            v-if="userMenuOpen"
                            class="absolute right-0 top-full mt-1 w-44 bg-white rounded-xl shadow-lg border border-teal-100 py-1 z-20"
                            @mouseleave="userMenuOpen = false"
                        >
                            <Link
                                :href="route('profile.index')"
                                class="flex items-center gap-2.5 px-3 py-2 text-sm text-teal-700 hover:bg-teal-50 transition-colors"
                                @click="userMenuOpen = false"
                            >
                                <GlobeAltIcon class="w-4 h-4 text-teal-400" />
                                My Profile
                            </Link>
                            <div class="border-t border-teal-100 my-1" />
                            <button
                                class="w-full flex items-center gap-2.5 px-3 py-2 text-sm text-red-500 hover:bg-red-50 transition-colors"
                                @click="logout"
                            >
                                <ArrowRightStartOnRectangleIcon class="w-4 h-4" />
                                Sign Out
                            </button>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page content -->
            <main class="flex-1 p-4 lg:p-6 overflow-auto">
                <slot />
            </main>
        </div>
    </div>

    <!-- Global modals + toasts — available sa tanan nga pages -->
    <ConfirmModal />
    <ToastStack />
</template>
