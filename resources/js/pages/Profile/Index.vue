<script setup>
import { ref } from 'vue'
import { useForm, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import {
    UserIcon,
    EnvelopeIcon,
    LockClosedIcon,
    LinkIcon,
    ClipboardDocumentIcon,
    CheckIcon,
    ShieldCheckIcon,
    EyeIcon,
    EyeSlashIcon,
    CalendarDaysIcon,
    UserGroupIcon,
    ArrowUpIcon,
    ArrowPathIcon,
} from '@heroicons/vue/24/outline'

const props = defineProps({
    profile: Object,
})

// Copy invite link
const copied = ref(false)
function copyInviteLink() {
    navigator.clipboard.writeText(props.profile.invite_link)
    copied.value = true
    setTimeout(() => { copied.value = false }, 2000)
}

import { useConfirm } from '@/composables/useConfirm'

const { confirm } = useConfirm()

// Regenerate invite code
const regenerating = ref(false)
const regenerateForm = useForm({})
async function regenerateInviteCode() {
    const ok = await confirm(
        'This will invalidate your current invite link. Anyone with the old link will no longer be able to register. Use this only if your link was shared with the wrong person.',
        { title: 'Regenerate Invite Link?', confirmLabel: 'Yes, Regenerate', danger: true }
    )
    if (! ok) return
    regenerating.value = true
    regenerateForm.post(route('profile.regenerate-invite'), {
        preserveScroll: true,
        onFinish: () => { regenerating.value = false },
    })
}

// Show/hide password fields
const showCurrent  = ref(false)
const showNew      = ref(false)
const showConfirm  = ref(false)

// Update info form
const infoForm = useForm({
    name:  props.profile.name,
    email: props.profile.email,
})

function updateInfo() {
    infoForm.post(route('profile.update-info'), {
        preserveScroll: true,
    })
}

// Update password form
const passwordForm = useForm({
    current_password: '',
    password:         '',
    password_confirmation: '',
})

function updatePassword() {
    passwordForm.post(route('profile.update-password'), {
        preserveScroll: true,
        onSuccess: () => passwordForm.reset(),
    })
}

// Role badge color
function roleClass(role) {
    return {
        admin:  'bg-teal-500 text-cream-200',
        leader: 'bg-purple-100 text-purple-700',
        member: 'bg-gray-100 text-gray-600',
    }[role] ?? 'bg-gray-100 text-gray-600'
}
</script>

<template>
    <AppLayout>
        <template #header>
            <h1 class="text-base font-semibold text-teal-800">My Profile</h1>
        </template>

        <div class="max-w-4xl mx-auto space-y-5">

            <!-- ===================== PROFILE HEADER CARD ===================== -->
            <div class="bg-teal-800 rounded-2xl p-6">
                <div class="flex flex-col sm:flex-row items-center sm:items-start gap-5">

                    <!-- Avatar -->
                    <div class="w-20 h-20 rounded-2xl bg-teal-500 flex items-center justify-center shrink-0 shadow-lg shadow-teal-900/40">
                        <span class="text-3xl font-black text-cream-200">
                            {{ profile.name.charAt(0).toUpperCase() }}
                        </span>
                    </div>

                    <!-- Info -->
                    <div class="flex-1 text-center sm:text-left">
                        <h2 class="text-xl font-bold text-cream-200">{{ profile.name }}</h2>
                        <p class="text-sm text-teal-300 mt-0.5">{{ profile.email }}</p>

                        <!-- Roles -->
                        <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2 mt-3">
                            <span
                                v-for="role in profile.roles"
                                :key="role"
                                class="inline-flex items-center gap-1 text-xs px-2.5 py-1 rounded-full font-semibold capitalize"
                                :class="roleClass(role)"
                            >
                                <ShieldCheckIcon class="w-3 h-3" />
                                {{ role }}
                            </span>
                            <span
                                class="inline-flex text-xs px-2.5 py-1 rounded-full font-medium capitalize"
                                :class="profile.status === 'active' ? 'bg-green-500/20 text-green-300' : 'bg-red-500/20 text-red-300'"
                            >
                                {{ profile.status }}
                            </span>
                        </div>
                    </div>

                    <!-- Quick stats -->
                    <div class="flex sm:flex-col gap-4 sm:gap-3 shrink-0">
                        <div class="text-center">
                            <div class="text-2xl font-bold text-cream-200">{{ profile.downlines }}</div>
                            <div class="text-xs text-teal-400">Downlines</div>
                        </div>
                        <div class="text-center">
                            <div class="text-sm font-semibold text-teal-300">{{ profile.joined }}</div>
                            <div class="text-xs text-teal-400">Joined</div>
                        </div>
                    </div>
                </div>

                <!-- Meta row -->
                <div class="mt-5 pt-5 border-t border-teal-700/60 grid grid-cols-2 sm:grid-cols-3 gap-4 text-xs">
                    <div class="flex items-center gap-2 text-teal-300">
                        <ArrowUpIcon class="w-3.5 h-3.5 text-teal-500 shrink-0" />
                        <span>Upline: <span class="font-semibold text-cream-200">{{ profile.upline }}</span></span>
                    </div>
                    <div class="flex items-center gap-2 text-teal-300">
                        <UserGroupIcon class="w-3.5 h-3.5 text-teal-500 shrink-0" />
                        <span>Network: <span class="font-semibold text-cream-200">{{ profile.downlines }} members</span></span>
                    </div>
                    <div class="flex items-center gap-2 text-teal-300">
                        <CalendarDaysIcon class="w-3.5 h-3.5 text-teal-500 shrink-0" />
                        <span>Since: <span class="font-semibold text-cream-200">{{ profile.joined }}</span></span>
                    </div>
                </div>
            </div>

            <!-- ===================== INVITE LINK CARD ===================== -->
            <div class="bg-cream-200 rounded-2xl border border-teal-100 p-5">
                <div class="flex items-center gap-2 mb-3">
                    <LinkIcon class="w-4 h-4 text-teal-500" />
                    <h3 class="text-sm font-bold text-teal-800">Your Invite Link</h3>
                </div>
                <p class="text-xs text-teal-500 mb-3">
                    Share this link to invite people to join ALW under your network.
                    Anyone who registers with this link becomes your direct downline.
                </p>
                <div class="flex items-center gap-2">
                    <div class="flex-1 px-3 py-2.5 rounded-xl bg-teal-50 border border-teal-200 text-xs text-teal-600 font-mono truncate">
                        {{ profile.invite_link }}
                    </div>
                    <button
                        class="shrink-0 flex items-center gap-1.5 px-4 py-2.5 rounded-xl text-xs font-semibold transition-colors"
                        :class="copied ? 'bg-green-500 text-white' : 'bg-teal-500 text-cream-200 hover:bg-teal-600'"
                        @click="copyInviteLink"
                    >
                        <CheckIcon v-if="copied" class="w-3.5 h-3.5" />
                        <ClipboardDocumentIcon v-else class="w-3.5 h-3.5" />
                        {{ copied ? 'Copied!' : 'Copy Link' }}
                    </button>
                </div>

                <!-- Invite code pill -->
                <div class="mt-3 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="text-xs text-teal-400">Invite code:</span>
                        <span class="text-xs font-black text-teal-700 tracking-widest font-mono bg-teal-100 px-2.5 py-0.5 rounded-lg">
                            {{ profile.invite_code }}
                        </span>
                    </div>
                    <!-- Regenerate button -->
                    <button
                        class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-red-200 text-red-500 text-xs font-semibold hover:bg-red-50 transition-colors"
                        :class="regenerating ? 'opacity-60 cursor-not-allowed' : ''"
                        :disabled="regenerating"
                        @click="regenerateInviteCode"
                    >
                        <ArrowPathIcon class="w-3.5 h-3.5" :class="regenerating ? 'animate-spin' : ''" />
                        {{ regenerating ? 'Regenerating…' : 'Regenerate' }}
                    </button>
                </div>
                <p class="mt-2 text-xs text-red-400">
                    Regenerating will invalidate your current link. Use only if your link was shared with the wrong person.
                </p>
            </div>

            <!-- ===================== BOTTOM GRID ===================== -->
            <div class="grid sm:grid-cols-2 gap-5">

                <!-- Update info form -->
                <div class="bg-cream-200 rounded-2xl border border-teal-100 p-5">
                    <div class="flex items-center gap-2 mb-4">
                        <UserIcon class="w-4 h-4 text-teal-500" />
                        <h3 class="text-sm font-bold text-teal-800">Account Information</h3>
                    </div>

                    <form @submit.prevent="updateInfo" class="space-y-4">

                        <!-- Name -->
                        <div class="space-y-1.5">
                            <label class="block text-xs font-semibold text-teal-700 uppercase tracking-wide">Full Name</label>
                            <div class="relative">
                                <UserIcon class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-teal-400 pointer-events-none" />
                                <input
                                    v-model="infoForm.name"
                                    type="text"
                                    class="w-full pl-9 pr-4 py-2.5 rounded-xl border text-sm text-teal-800 bg-white outline-none transition-colors"
                                    :class="infoForm.errors.name
                                        ? 'border-red-300 focus:border-red-400 focus:ring-2 focus:ring-red-100'
                                        : 'border-teal-200 focus:border-teal-400 focus:ring-2 focus:ring-teal-100'"
                                />
                            </div>
                            <p v-if="infoForm.errors.name" class="text-xs text-red-500">{{ infoForm.errors.name }}</p>
                        </div>

                        <!-- Email -->
                        <div class="space-y-1.5">
                            <label class="block text-xs font-semibold text-teal-700 uppercase tracking-wide">Email Address</label>
                            <div class="relative">
                                <EnvelopeIcon class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-teal-400 pointer-events-none" />
                                <input
                                    v-model="infoForm.email"
                                    type="email"
                                    class="w-full pl-9 pr-4 py-2.5 rounded-xl border text-sm text-teal-800 bg-white outline-none transition-colors"
                                    :class="infoForm.errors.email
                                        ? 'border-red-300 focus:border-red-400 focus:ring-2 focus:ring-red-100'
                                        : 'border-teal-200 focus:border-teal-400 focus:ring-2 focus:ring-teal-100'"
                                />
                            </div>
                            <p v-if="infoForm.errors.email" class="text-xs text-red-500">{{ infoForm.errors.email }}</p>
                        </div>

                        <button
                            type="submit"
                            :disabled="infoForm.processing"
                            class="w-full py-2.5 rounded-xl bg-teal-500 text-cream-200 text-sm font-semibold hover:bg-teal-600 transition-colors disabled:opacity-60"
                        >
                            {{ infoForm.processing ? 'Saving…' : 'Save Changes' }}
                        </button>
                    </form>
                </div>

                <!-- Update password form -->
                <div class="bg-cream-200 rounded-2xl border border-teal-100 p-5">
                    <div class="flex items-center gap-2 mb-4">
                        <LockClosedIcon class="w-4 h-4 text-teal-500" />
                        <h3 class="text-sm font-bold text-teal-800">Change Password</h3>
                    </div>

                    <form @submit.prevent="updatePassword" class="space-y-4">

                        <!-- Current password -->
                        <div class="space-y-1.5">
                            <label class="block text-xs font-semibold text-teal-700 uppercase tracking-wide">Current Password</label>
                            <div class="relative">
                                <LockClosedIcon class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-teal-400 pointer-events-none" />
                                <input
                                    v-model="passwordForm.current_password"
                                    :type="showCurrent ? 'text' : 'password'"
                                    placeholder="••••••••"
                                    class="w-full pl-9 pr-10 py-2.5 rounded-xl border text-sm text-teal-800 bg-white outline-none transition-colors"
                                    :class="passwordForm.errors.current_password
                                        ? 'border-red-300 focus:border-red-400 focus:ring-2 focus:ring-red-100'
                                        : 'border-teal-200 focus:border-teal-400 focus:ring-2 focus:ring-teal-100'"
                                />
                                <button type="button" class="absolute right-3 top-1/2 -translate-y-1/2 text-teal-400 hover:text-teal-600" @click="showCurrent = !showCurrent">
                                    <EyeSlashIcon v-if="showCurrent" class="w-4 h-4" />
                                    <EyeIcon v-else class="w-4 h-4" />
                                </button>
                            </div>
                            <p v-if="passwordForm.errors.current_password" class="text-xs text-red-500">{{ passwordForm.errors.current_password }}</p>
                        </div>

                        <!-- New password -->
                        <div class="space-y-1.5">
                            <label class="block text-xs font-semibold text-teal-700 uppercase tracking-wide">New Password</label>
                            <div class="relative">
                                <LockClosedIcon class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-teal-400 pointer-events-none" />
                                <input
                                    v-model="passwordForm.password"
                                    :type="showNew ? 'text' : 'password'"
                                    placeholder="Min. 8 characters"
                                    class="w-full pl-9 pr-10 py-2.5 rounded-xl border text-sm text-teal-800 bg-white outline-none transition-colors"
                                    :class="passwordForm.errors.password
                                        ? 'border-red-300 focus:border-red-400 focus:ring-2 focus:ring-red-100'
                                        : 'border-teal-200 focus:border-teal-400 focus:ring-2 focus:ring-teal-100'"
                                />
                                <button type="button" class="absolute right-3 top-1/2 -translate-y-1/2 text-teal-400 hover:text-teal-600" @click="showNew = !showNew">
                                    <EyeSlashIcon v-if="showNew" class="w-4 h-4" />
                                    <EyeIcon v-else class="w-4 h-4" />
                                </button>
                            </div>
                            <p v-if="passwordForm.errors.password" class="text-xs text-red-500">{{ passwordForm.errors.password }}</p>
                        </div>

                        <!-- Confirm password -->
                        <div class="space-y-1.5">
                            <label class="block text-xs font-semibold text-teal-700 uppercase tracking-wide">Confirm New Password</label>
                            <div class="relative">
                                <LockClosedIcon class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-teal-400 pointer-events-none" />
                                <input
                                    v-model="passwordForm.password_confirmation"
                                    :type="showConfirm ? 'text' : 'password'"
                                    placeholder="Repeat new password"
                                    class="w-full pl-9 pr-10 py-2.5 rounded-xl border text-sm text-teal-800 bg-white outline-none transition-colors border-teal-200 focus:border-teal-400 focus:ring-2 focus:ring-teal-100"
                                />
                                <button type="button" class="absolute right-3 top-1/2 -translate-y-1/2 text-teal-400 hover:text-teal-600" @click="showConfirm = !showConfirm">
                                    <EyeSlashIcon v-if="showConfirm" class="w-4 h-4" />
                                    <EyeIcon v-else class="w-4 h-4" />
                                </button>
                            </div>
                        </div>

                        <button
                            type="submit"
                            :disabled="passwordForm.processing"
                            class="w-full py-2.5 rounded-xl bg-teal-500 text-cream-200 text-sm font-semibold hover:bg-teal-600 transition-colors disabled:opacity-60"
                        >
                            {{ passwordForm.processing ? 'Updating…' : 'Update Password' }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
