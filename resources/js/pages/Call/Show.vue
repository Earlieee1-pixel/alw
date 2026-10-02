<script setup>
import { ref } from 'vue'
import { Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import {
    ChevronLeftIcon,
    ClipboardDocumentIcon,
    CheckIcon,
    ArrowTopRightOnSquareIcon,
    UserGroupIcon,
    LockClosedIcon,
    VideoCameraIcon,
    ClockIcon,
    UserIcon,
} from '@heroicons/vue/24/outline'

const props = defineProps({
    room:      Object,
    isAdmin:   Boolean,
    canCreate: Boolean,
    userName:  String,
})

// Copy room code
const copied = ref(false)
function copyCode() {
    navigator.clipboard.writeText(props.room.room_code)
    copied.value = true
    setTimeout(() => { copied.value = false }, 2000)
}

// Build Jitsi URL with display name pre-filled — opens in new tab
const jitsiUrl = `${props.room.jitsi_url}#userInfo.displayName="${encodeURIComponent(props.userName)}"&config.prejoinPageEnabled=false`

function joinCall() {
    window.open(jitsiUrl, '_blank', 'noopener,noreferrer')
}
</script>

<template>
    <AppLayout>
        <template #header>
            <div class="flex items-center gap-2">
                <Link
                    :href="route('calls.index')"
                    class="p-1 rounded-lg text-teal-400 hover:text-teal-600 hover:bg-teal-100 transition-colors"
                >
                    <ChevronLeftIcon class="w-4 h-4" />
                </Link>
                <h1 class="text-base font-semibold text-teal-800 truncate">{{ room.title }}</h1>
                <div class="flex items-center gap-1.5 ml-2">
                    <div class="w-1.5 h-1.5 rounded-full bg-green-400 animate-pulse" />
                    <span class="text-xs font-medium text-green-600">Live</span>
                </div>
            </div>
        </template>

        <div class="max-w-4xl mx-auto space-y-5">

            <!-- ===================== JOIN CALL CARD ===================== -->
            <div class="bg-teal-800 rounded-2xl overflow-hidden">

                <!-- Top decorative band -->
                <div class="h-2 bg-gradient-to-r from-teal-500 via-teal-400 to-teal-600" />

                <div class="p-8 flex flex-col items-center text-center gap-6">

                    <!-- Icon -->
                    <div class="w-20 h-20 rounded-2xl bg-teal-500/20 border border-teal-500/30 flex items-center justify-center">
                        <VideoCameraIcon class="w-10 h-10 text-teal-400" />
                    </div>

                    <!-- Title -->
                    <div class="space-y-2">
                        <h2 class="text-2xl font-bold text-cream-200">{{ room.title }}</h2>
                        <p v-if="room.description" class="text-sm text-teal-300 max-w-md">
                            {{ room.description }}
                        </p>
                    </div>

                    <!-- Meta row -->
                    <div class="flex flex-wrap items-center justify-center gap-4 text-xs text-teal-400">
                        <span class="flex items-center gap-1.5">
                            <UserIcon class="w-3.5 h-3.5" />
                            Created by {{ room.created_by }}
                        </span>
                        <span v-if="room.scheduled_at" class="flex items-center gap-1.5">
                            <ClockIcon class="w-3.5 h-3.5" />
                            {{ room.scheduled_at }}
                        </span>
                        <span class="flex items-center gap-1.5">
                            <div class="w-1.5 h-1.5 rounded-full bg-green-400 animate-pulse" />
                            <span class="text-green-400 font-medium">Room is active</span>
                        </span>
                    </div>

                    <!-- Join button -->
                    <button
                        class="inline-flex items-center gap-3 px-8 py-4 rounded-xl bg-teal-500 text-cream-200 font-bold text-base hover:bg-teal-400 transition-colors shadow-lg shadow-teal-900/40 group"
                        @click="joinCall"
                    >
                        <VideoCameraIcon class="w-5 h-5" />
                        Join Call Now
                        <ArrowTopRightOnSquareIcon class="w-4 h-4 opacity-70 group-hover:opacity-100 transition-opacity" />
                    </button>

                    <p class="text-xs text-teal-500 max-w-sm">
                        The call opens in a new tab via Jitsi Meet. Allow camera and microphone access when prompted.
                        No time limits, no downloads required.
                    </p>
                </div>
            </div>

            <!-- ===================== BOTTOM ROW ===================== -->
            <div class="grid sm:grid-cols-2 gap-4">

                <!-- Room code card — admin/leader only -->
                <div v-if="canCreate" class="bg-cream-200 rounded-2xl border border-teal-100 p-5 space-y-3">
                    <div class="flex items-center gap-2">
                        <LockClosedIcon class="w-4 h-4 text-teal-500" />
                        <h3 class="text-sm font-bold text-teal-800">Room Code</h3>
                    </div>
                    <p class="text-xs text-teal-500">
                        Share this code with members so they can join from the Video Calls page.
                    </p>
                    <div class="flex items-center gap-2">
                        <div class="flex-1 px-3 py-3 rounded-xl bg-teal-50 border border-teal-200 text-center">
                            <span class="text-xl font-black text-teal-700 tracking-[0.3em] font-mono">
                                {{ room.room_code }}
                            </span>
                        </div>
                        <button
                            class="p-3 rounded-xl transition-colors shrink-0"
                            :class="copied ? 'bg-green-500' : 'bg-teal-500 hover:bg-teal-600'"
                            @click="copyCode"
                            :aria-label="copied ? 'Copied' : 'Copy room code'"
                        >
                            <CheckIcon v-if="copied" class="w-4 h-4 text-white" />
                            <ClipboardDocumentIcon v-else class="w-4 h-4 text-cream-200" />
                        </button>
                    </div>
                </div>

                <!-- Tips card -->
                <div class="bg-cream-200 rounded-2xl border border-teal-100 p-5">
                    <div class="flex items-center gap-2 mb-3">
                        <UserGroupIcon class="w-4 h-4 text-teal-500" />
                        <h3 class="text-xs font-bold text-teal-700 uppercase tracking-wide">Before You Join</h3>
                    </div>
                    <ul class="space-y-2 text-xs text-teal-600">
                        <li class="flex items-start gap-2">
                            <span class="text-teal-400 font-bold shrink-0">01</span>
                            Allow camera and microphone when the browser asks
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-teal-400 font-bold shrink-0">02</span>
                            Use headphones to avoid echo on the call
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-teal-400 font-bold shrink-0">03</span>
                            Mute yourself when not speaking
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-teal-400 font-bold shrink-0">04</span>
                            Your name ({{ userName }}) will appear automatically
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
