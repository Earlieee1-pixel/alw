<script setup>
import { ref, computed } from 'vue'
import { useAuth } from '@/composables/useAuth'
import { useForm, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import {
    UsersIcon,
    UserGroupIcon,
    AcademicCapIcon,
    VideoCameraIcon,
    LinkIcon,
    ClipboardDocumentIcon,
    CheckIcon,
    ArrowRightIcon,
    UserPlusIcon,
    ShieldCheckIcon,
    ExclamationTriangleIcon,
    ChartBarIcon,
    ArrowPathIcon,
} from '@heroicons/vue/24/outline'
import { CheckCircleIcon } from '@heroicons/vue/24/solid'

// Props gikan sa DashboardController
const props = defineProps({
    stats:         { type: Object,  required: true },
    recentMembers: { type: Array,   required: true },
    inviteLink:    { type: String,  required: true },
})

const { isAdmin, isLeader, user } = useAuth()

// Invite link copy state
const copied = ref(false)

function copyInviteLink() {
    navigator.clipboard.writeText(props.inviteLink)
    copied.value = true
    setTimeout(() => { copied.value = false }, 2000)
}

import { useConfirm } from '@/composables/useConfirm'

const { confirm } = useConfirm()

// Regenerate invite code
const regenerating   = ref(false)
const regenerateForm = useForm({})
async function regenerateInviteCode() {
    const ok = await confirm(
        'This will invalidate your current invite link. Anyone with the old link will no longer be able to register.',
        { title: 'Regenerate Invite Link?', confirmLabel: 'Yes, Regenerate', danger: true }
    )
    if (! ok) return
    regenerating.value = true
    regenerateForm.post(route('profile.regenerate-invite'), {
        preserveScroll: true,
        onFinish: () => { regenerating.value = false },
    })
}

// Admin stat cards — makita lang sa admin
const adminStatCards = computed(() => [
    {
        label:   'Total Members',
        value:   props.stats.totalMembers ?? 0,
        icon:    UsersIcon,
        color:   'teal',
        change:  `+${props.stats.newMembersMonth ?? 0} this month`,
    },
    {
        label:   'Active Members',
        value:   props.stats.activeMembers ?? 0,
        icon:    CheckCircleIcon,
        color:   'green',
        change:  null,
    },
    {
        label:   'New This Month',
        value:   props.stats.newMembersMonth ?? 0,
        icon:    UserPlusIcon,
        color:   'teal',
        change:  null,
    },
    {
        label:   'Suspended',
        value:   props.stats.suspendedMembers ?? 0,
        icon:    ExclamationTriangleIcon,
        color:   'red',
        change:  null,
    },
])

// Member stat cards — makita sa tanan
const memberStatCards = computed(() => [
    {
        label:  'Direct Referrals',
        value:  props.stats.directDownlines ?? 0,
        icon:   UserGroupIcon,
        color:  'teal',
    },
    {
        label:  'Total Network',
        value:  props.stats.totalDownlines ?? 0,
        icon:   ChartBarIcon,
        color:  'teal',
    },
])

// Status badge styling
function statusClass(status) {
    return {
        active:    'bg-green-100 text-green-700',
        inactive:  'bg-gray-100 text-gray-500',
        suspended: 'bg-red-100 text-red-600',
    }[status] ?? 'bg-gray-100 text-gray-500'
}
</script>

<template>
    <AppLayout>
        <!-- Page title slot -->
        <template #header>
            <h1 class="text-base font-semibold text-teal-800">Dashboard</h1>
        </template>

        <div class="space-y-6 max-w-7xl mx-auto">

            <!-- ===================== WELCOME BANNER ===================== -->
            <div class="rounded-2xl bg-teal-800 px-6 py-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <ShieldCheckIcon v-if="isAdmin" class="w-4 h-4 text-teal-400" />
                        <span class="text-xs font-semibold text-teal-400 uppercase tracking-widest">
                            {{ isAdmin ? 'Admin' : isLeader ? 'Leader' : 'Member' }}
                        </span>
                    </div>
                    <h2 class="text-xl font-bold text-cream-200">
                        Welcome back, {{ user?.name?.split(' ')[0] }}.
                    </h2>
                    <p class="text-sm text-teal-300 mt-0.5">
                        {{ isAdmin
                            ? 'You have full access to the ALW platform.'
                            : 'Here\'s a look at your network activity.' }}
                    </p>
                </div>

                <!-- Quick action buttons -->
                <div class="flex items-center gap-2 shrink-0">
                    <a
                        :href="route('calls.index')"
                        class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-teal-500 text-cream-200 text-xs font-semibold hover:bg-teal-400 transition-colors"
                    >
                        <VideoCameraIcon class="w-3.5 h-3.5" />
                        Video Calls
                    </a>
                    <a
                        :href="route('videos.index')"
                        class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-teal-700 text-cream-200 text-xs font-semibold hover:bg-teal-600 transition-colors"
                    >
                        <AcademicCapIcon class="w-3.5 h-3.5" />
                        Training
                    </a>
                </div>
            </div>

            <!-- ===================== ADMIN STAT CARDS ===================== -->
            <div v-if="isAdmin" class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div
                    v-for="card in adminStatCards"
                    :key="card.label"
                    class="bg-cream-200 rounded-2xl p-5 border border-teal-100 hover:border-teal-200 transition-colors"
                >
                    <div class="flex items-start justify-between mb-3">
                        <div
                            class="w-9 h-9 rounded-xl flex items-center justify-center"
                            :class="{
                                'bg-teal-500/10': card.color === 'teal',
                                'bg-green-100':   card.color === 'green',
                                'bg-red-100':     card.color === 'red',
                            }"
                        >
                            <component
                                :is="card.icon"
                                class="w-4 h-4"
                                :class="{
                                    'text-teal-500': card.color === 'teal',
                                    'text-green-600': card.color === 'green',
                                    'text-red-500':  card.color === 'red',
                                }"
                            />
                        </div>
                    </div>
                    <div class="text-2xl font-bold text-teal-800">{{ card.value.toLocaleString() }}</div>
                    <div class="text-xs text-teal-500 font-medium mt-0.5">{{ card.label }}</div>
                    <div v-if="card.change" class="text-xs text-teal-400 mt-1">{{ card.change }}</div>
                </div>
            </div>

            <!-- ===================== MEMBER STAT CARDS ===================== -->
            <div class="grid grid-cols-2 gap-4" :class="isAdmin ? 'lg:grid-cols-2' : 'lg:grid-cols-4'">
                <div
                    v-for="card in memberStatCards"
                    :key="card.label"
                    class="bg-cream-200 rounded-2xl p-5 border border-teal-100 hover:border-teal-200 transition-colors"
                >
                    <div class="w-9 h-9 rounded-xl bg-teal-500/10 flex items-center justify-center mb-3">
                        <component :is="card.icon" class="w-4 h-4 text-teal-500" />
                    </div>
                    <div class="text-2xl font-bold text-teal-800">{{ card.value.toLocaleString() }}</div>
                    <div class="text-xs text-teal-500 font-medium mt-0.5">{{ card.label }}</div>
                </div>

                <!-- Invite link card -->
                <div class="col-span-2 bg-teal-500/8 rounded-2xl p-5 border border-teal-200">
                    <div class="flex items-center gap-2 mb-3">
                        <LinkIcon class="w-4 h-4 text-teal-500" />
                        <span class="text-xs font-semibold text-teal-600 uppercase tracking-wide">Your Invite Link</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="flex-1 px-3 py-2 rounded-xl bg-white border border-teal-200 text-xs text-teal-600 font-mono truncate">
                            {{ inviteLink }}
                        </div>
                        <button
                            class="shrink-0 flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold transition-colors"
                            :class="copied
                                ? 'bg-green-500 text-white'
                                : 'bg-teal-500 text-cream-200 hover:bg-teal-600'"
                            @click="copyInviteLink"
                        >
                            <CheckIcon v-if="copied" class="w-3.5 h-3.5" />
                            <ClipboardDocumentIcon v-else class="w-3.5 h-3.5" />
                            {{ copied ? 'Copied!' : 'Copy' }}
                        </button>
                    </div>
                    <p class="mt-2 text-xs text-teal-500">
                        Share this link to invite people to your network.
                    </p>
                    <button
                        class="mt-2 flex items-center gap-1.5 text-xs text-red-400 hover:text-red-600 transition-colors font-medium"
                        :disabled="regenerating"
                        @click="regenerateInviteCode"
                    >
                        <ArrowPathIcon class="w-3 h-3" :class="regenerating ? 'animate-spin' : ''" />
                        {{ regenerating ? 'Regenerating…' : 'Regenerate link' }}
                    </button>
                </div>
            </div>

            <!-- ===================== RECENT MEMBERS TABLE ===================== -->
            <div class="bg-cream-200 rounded-2xl border border-teal-100">

                <!-- Table header -->
                <div class="flex items-center justify-between px-5 py-4 border-b border-teal-100">
                    <div>
                        <h3 class="text-sm font-semibold text-teal-800">
                            {{ isAdmin ? 'Recent Members' : 'My Recent Referrals' }}
                        </h3>
                        <p class="text-xs text-teal-500 mt-0.5">
                            {{ isAdmin ? 'Latest registrations across the platform' : 'Members you personally invited' }}
                        </p>
                    </div>
                    <a
                        v-if="isAdmin"
                        :href="route('admin.members.index')"
                        class="inline-flex items-center gap-1 text-xs font-semibold text-teal-500 hover:text-teal-700 transition-colors"
                    >
                        View all
                        <ArrowRightIcon class="w-3 h-3" />
                    </a>
                </div>

                <!-- Table -->
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-teal-50">
                                <th class="text-left px-5 py-3 text-xs font-semibold text-teal-500 uppercase tracking-wide">Member</th>
                                <th class="text-left px-5 py-3 text-xs font-semibold text-teal-500 uppercase tracking-wide hidden sm:table-cell">Upline</th>
                                <th class="text-left px-5 py-3 text-xs font-semibold text-teal-500 uppercase tracking-wide hidden md:table-cell">Role</th>
                                <th class="text-left px-5 py-3 text-xs font-semibold text-teal-500 uppercase tracking-wide">Status</th>
                                <th class="text-left px-5 py-3 text-xs font-semibold text-teal-500 uppercase tracking-wide hidden lg:table-cell">Joined</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-teal-50">
                            <tr
                                v-for="member in recentMembers"
                                :key="member.id"
                                class="hover:bg-teal-50/50 transition-colors"
                            >
                                <!-- Name + email -->
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center gap-3">
                                        <div class="w-7 h-7 rounded-full bg-teal-500 flex items-center justify-center text-xs font-bold text-cream-200 shrink-0">
                                            {{ member.name.charAt(0).toUpperCase() }}
                                        </div>
                                        <div>
                                            <div class="font-medium text-teal-800 text-xs">{{ member.name }}</div>
                                            <div class="text-teal-400 text-xs">{{ member.email }}</div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Upline -->
                                <td class="px-5 py-3.5 text-xs text-teal-600 hidden sm:table-cell">
                                    {{ member.upline }}
                                </td>

                                <!-- Role badge -->
                                <td class="px-5 py-3.5 hidden md:table-cell">
                                    <span class="inline-flex items-center gap-1 text-xs px-2 py-0.5 rounded-full bg-teal-100 text-teal-700 font-medium capitalize">
                                        <ShieldCheckIcon v-if="member.roles.includes('admin')" class="w-2.5 h-2.5" />
                                        {{ member.roles[0] ?? 'member' }}
                                    </span>
                                </td>

                                <!-- Status badge -->
                                <td class="px-5 py-3.5">
                                    <span
                                        class="inline-flex text-xs px-2 py-0.5 rounded-full font-medium capitalize"
                                        :class="statusClass(member.status)"
                                    >
                                        {{ member.status }}
                                    </span>
                                </td>

                                <!-- Joined -->
                                <td class="px-5 py-3.5 text-xs text-teal-400 hidden lg:table-cell" :title="member.joined_date">
                                    {{ member.joined }}
                                </td>
                            </tr>

                            <!-- Empty state -->
                            <tr v-if="recentMembers.length === 0">
                                <td colspan="5" class="px-5 py-10 text-center text-sm text-teal-400">
                                    No members yet. Share your invite link to get started.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </AppLayout>
</template>
