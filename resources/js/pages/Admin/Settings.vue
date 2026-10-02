<script setup>
import AppLayout from '@/layouts/AppLayout.vue'
import {
    UsersIcon,
    VideoCameraIcon,
    AcademicCapIcon,
    ShieldCheckIcon,
    GlobeAltIcon,
    ServerStackIcon,
    CheckCircleIcon,
    ExclamationTriangleIcon,
    UserGroupIcon,
    ChartBarIcon,
} from '@heroicons/vue/24/outline'

const props = defineProps({
    stats:     Object,
    app:       Object,
    roleStats: Object,
})

// Compute role percentage para sa bar display
const totalForRoles = props.roleStats.admins + props.roleStats.leaders + props.roleStats.members || 1
function rolePct(count) {
    return Math.round((count / totalForRoles) * 100)
}
</script>

<template>
    <AppLayout>
        <template #header>
            <h1 class="text-base font-semibold text-teal-800">Settings</h1>
        </template>

        <div class="max-w-5xl mx-auto space-y-5">

            <!-- ===================== PLATFORM OVERVIEW ===================== -->
            <div class="bg-teal-800 rounded-2xl p-6">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-9 h-9 rounded-xl bg-teal-500 flex items-center justify-center">
                        <GlobeAltIcon class="w-5 h-5 text-cream-200" />
                    </div>
                    <div>
                        <h2 class="text-sm font-bold text-cream-200">Platform Overview</h2>
                        <p class="text-xs text-teal-400">Live snapshot of the ALW network</p>
                    </div>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
                    <div
                        v-for="card in [
                            { label: 'Total Members',    value: stats.total_members,  icon: UsersIcon,        color: 'teal'   },
                            { label: 'Active Members',   value: stats.active_members, icon: CheckCircleIcon,  color: 'green'  },
                            { label: 'New This Month',   value: stats.new_this_month, icon: UserGroupIcon,    color: 'teal'   },
                            { label: 'Suspended',        value: stats.suspended,      icon: ExclamationTriangleIcon, color: 'red' },
                            { label: 'Training Videos',  value: stats.total_videos,   icon: AcademicCapIcon,  color: 'teal'   },
                            { label: 'Active Rooms',     value: stats.active_rooms,   icon: VideoCameraIcon,  color: 'green'  },
                            { label: 'Total Rooms',      value: stats.total_rooms,    icon: VideoCameraIcon,  color: 'teal'   },
                        ]"
                        :key="card.label"
                        class="bg-teal-700/40 border border-teal-600/30 rounded-xl p-4"
                    >
                        <component
                            :is="card.icon"
                            class="w-4 h-4 mb-2"
                            :class="{
                                'text-teal-400':  card.color === 'teal',
                                'text-green-400': card.color === 'green',
                                'text-red-400':   card.color === 'red',
                            }"
                        />
                        <div
                            class="text-2xl font-bold"
                            :class="{
                                'text-cream-200': card.color === 'teal',
                                'text-green-300': card.color === 'green',
                                'text-red-300':   card.color === 'red',
                            }"
                        >
                            {{ card.value.toLocaleString() }}
                        </div>
                        <div class="text-xs text-teal-400 mt-0.5">{{ card.label }}</div>
                    </div>
                </div>
            </div>

            <!-- ===================== ROLE DISTRIBUTION ===================== -->
            <div class="bg-cream-200 rounded-2xl border border-teal-100 p-5">
                <div class="flex items-center gap-2 mb-5">
                    <ChartBarIcon class="w-4 h-4 text-teal-500" />
                    <h3 class="text-sm font-bold text-teal-800">Role Distribution</h3>
                </div>

                <div class="space-y-4">
                    <div
                        v-for="item in [
                            { role: 'Admin',   count: roleStats.admins,  color: 'bg-teal-500'    },
                            { role: 'Leader',  count: roleStats.leaders, color: 'bg-purple-400'  },
                            { role: 'Member',  count: roleStats.members, color: 'bg-teal-300'    },
                        ]"
                        :key="item.role"
                    >
                        <div class="flex justify-between text-xs mb-1.5">
                            <span class="font-semibold text-teal-700">{{ item.role }}</span>
                            <span class="text-teal-400">{{ item.count.toLocaleString() }} ({{ rolePct(item.count) }}%)</span>
                        </div>
                        <div class="h-2 bg-teal-100 rounded-full overflow-hidden">
                            <div
                                class="h-full rounded-full transition-all duration-500"
                                :class="item.color"
                                :style="{ width: rolePct(item.count) + '%' }"
                            />
                        </div>
                    </div>
                </div>
            </div>

            <!-- ===================== APP INFO ===================== -->
            <div class="bg-cream-200 rounded-2xl border border-teal-100 p-5">
                <div class="flex items-center gap-2 mb-5">
                    <ServerStackIcon class="w-4 h-4 text-teal-500" />
                    <h3 class="text-sm font-bold text-teal-800">Application Info</h3>
                </div>

                <div class="grid sm:grid-cols-2 gap-3">
                    <div
                        v-for="item in [
                            { label: 'App Name',       value: app.name    },
                            { label: 'App URL',        value: app.url     },
                            { label: 'Environment',    value: app.env     },
                            { label: 'Laravel Version', value: app.version },
                        ]"
                        :key="item.label"
                        class="flex items-start justify-between px-4 py-3 rounded-xl bg-teal-50 border border-teal-100"
                    >
                        <span class="text-xs font-semibold text-teal-500 uppercase tracking-wide">{{ item.label }}</span>
                        <span class="text-xs font-mono font-bold text-teal-800">{{ item.value }}</span>
                    </div>
                </div>
            </div>

            <!-- ===================== ROLES LEGEND ===================== -->
            <div class="bg-cream-200 rounded-2xl border border-teal-100 p-5">
                <div class="flex items-center gap-2 mb-5">
                    <ShieldCheckIcon class="w-4 h-4 text-teal-500" />
                    <h3 class="text-sm font-bold text-teal-800">Role Permissions</h3>
                </div>

                <div class="grid sm:grid-cols-3 gap-4">
                    <div
                        v-for="role in [
                            {
                                name: 'Member',
                                color: 'border-teal-200',
                                badge: 'bg-gray-100 text-gray-600',
                                perms: [
                                    'View dashboard',
                                    'Watch training videos',
                                    'Join video calls (with code)',
                                    'View own downline',
                                    'Share invite link',
                                ],
                            },
                            {
                                name: 'Leader',
                                color: 'border-purple-200',
                                badge: 'bg-purple-100 text-purple-700',
                                perms: [
                                    'All Member permissions',
                                    'Post training videos',
                                    'Create video call rooms',
                                    'Enter rooms without code',
                                    'View room codes',
                                ],
                            },
                            {
                                name: 'Admin',
                                color: 'border-teal-400',
                                badge: 'bg-teal-500 text-cream-200',
                                perms: [
                                    'All Leader permissions',
                                    'Manage all members',
                                    'Activate / suspend accounts',
                                    'Change member roles',
                                    'View platform settings',
                                ],
                            },
                        ]"
                        :key="role.name"
                        class="rounded-xl border p-4 space-y-3"
                        :class="role.color"
                    >
                        <span
                            class="inline-flex text-xs px-2.5 py-1 rounded-full font-semibold"
                            :class="role.badge"
                        >
                            {{ role.name }}
                        </span>
                        <ul class="space-y-1.5">
                            <li
                                v-for="perm in role.perms"
                                :key="perm"
                                class="flex items-start gap-2 text-xs text-teal-600"
                            >
                                <CheckCircleIcon class="w-3.5 h-3.5 text-teal-400 shrink-0 mt-0.5" />
                                {{ perm }}
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

        </div>
    </AppLayout>
</template>
