<script setup>
import { ref } from 'vue'
import { useForm, Link, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { useConfirm } from '@/composables/useConfirm'
import {
    VideoCameraIcon,
    PlusIcon,
    XMarkIcon,
    LockClosedIcon,
    ArrowRightIcon,
    ClockIcon,
    CheckCircleIcon,
} from '@heroicons/vue/24/outline'
import { SignalIcon } from '@heroicons/vue/24/solid'

const props = defineProps({
    rooms:     Array,
    canCreate: Boolean,
    isAdmin:   Boolean,
})

// Create room modal
const showCreateModal = ref(false)
const createForm = useForm({
    title:        '',
    description:  '',
    scheduled_at: '',
})

function createRoom() {
    createForm.post(route('calls.store'), {
        onSuccess: () => {
            showCreateModal.value = false
            createForm.reset()
        },
    })
}

// Join room with code
const showJoinModal  = ref(false)
const selectedRoom   = ref(null)
const joinCode       = ref('')
const joinError      = ref('')

function openJoinModal(room) {
    selectedRoom.value = room
    joinCode.value     = ''
    joinError.value    = ''
    showJoinModal.value = true
}

function joinRoom() {
    if (! joinCode.value.trim()) {
        joinError.value = 'Please enter the room code.'
        return
    }
    // I-navigate sa show page with code as query param
    router.get(route('calls.show', selectedRoom.value.id), {
        code: joinCode.value.trim(),
    })
}

const { confirm } = useConfirm()

// Close room — admin only
async function closeRoom(room) {
    const ok = await confirm(`Members will no longer be able to join "${room.title}" once it is closed.`, {
        title: 'Close Room?',
        confirmLabel: 'Yes, Close',
        danger: true,
    })
    if (! ok) return
    router.post(route('calls.close', room.id), {}, { preserveScroll: true })
}

function statusClass(s) {
    return s === 'active'
        ? 'bg-green-100 text-green-700'
        : 'bg-gray-100 text-gray-500'
}
</script>

<template>
    <AppLayout>
        <template #header>
            <h1 class="text-base font-semibold text-teal-800">Video Calls</h1>
        </template>

        <div class="space-y-5 max-w-5xl mx-auto">

            <!-- Toolbar -->
            <div class="flex items-center justify-between">
                <p class="text-sm text-teal-500">
                    {{ rooms.filter(r => r.status === 'active').length }} active room{{ rooms.filter(r => r.status === 'active').length !== 1 ? 's' : '' }}
                </p>
                <button
                    v-if="canCreate"
                    class="flex items-center gap-2 px-4 py-2.5 rounded-xl bg-teal-500 text-cream-200 text-sm font-semibold hover:bg-teal-600 transition-colors"
                    @click="showCreateModal = true"
                >
                    <PlusIcon class="w-4 h-4" />
                    Create Room
                </button>
            </div>

            <!-- Rooms grid -->
            <div v-if="rooms.length > 0" class="grid sm:grid-cols-2 gap-4">
                <div
                    v-for="room in rooms"
                    :key="room.id"
                    class="bg-cream-200 rounded-2xl border transition-all"
                    :class="room.status === 'active'
                        ? 'border-teal-200 hover:border-teal-400 hover:shadow-md hover:shadow-teal-500/5'
                        : 'border-gray-200 opacity-60'"
                >
                    <div class="p-5">
                        <!-- Header row -->
                        <div class="flex items-start justify-between gap-3 mb-3">
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0"
                                    :class="room.status === 'active' ? 'bg-teal-500' : 'bg-gray-300'"
                                >
                                    <VideoCameraIcon class="w-5 h-5 text-cream-200" />
                                </div>
                                <div>
                                    <h3 class="text-sm font-bold text-teal-800">{{ room.title }}</h3>
                                    <p class="text-xs text-teal-400">by {{ room.created_by }}</p>
                                </div>
                            </div>

                            <!-- Status badge -->
                            <div class="flex items-center gap-1.5 shrink-0">
                                <div
                                    v-if="room.status === 'active'"
                                    class="w-1.5 h-1.5 rounded-full bg-green-400 animate-pulse"
                                />
                                <span
                                    class="text-xs px-2 py-0.5 rounded-full font-medium capitalize"
                                    :class="statusClass(room.status)"
                                >
                                    {{ room.status }}
                                </span>
                            </div>
                        </div>

                        <!-- Description -->
                        <p v-if="room.description" class="text-xs text-teal-600/80 mb-3 line-clamp-2">
                            {{ room.description }}
                        </p>

                        <!-- Meta -->
                        <div class="flex items-center gap-3 text-xs text-teal-400 mb-4">
                            <span v-if="room.scheduled_at" class="flex items-center gap-1">
                                <ClockIcon class="w-3.5 h-3.5" />
                                {{ room.scheduled_at }}
                            </span>
                            <span>{{ room.created_at }}</span>
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center gap-2">
                            <!-- Admin/leader can enter directly -->
                            <Link
                                v-if="canCreate"
                                :href="route('calls.show', room.id)"
                                class="flex-1 flex items-center justify-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold transition-colors"
                                :class="room.status === 'active'
                                    ? 'bg-teal-500 text-cream-200 hover:bg-teal-600'
                                    : 'bg-gray-200 text-gray-400 pointer-events-none'"
                            >
                                <SignalIcon class="w-3.5 h-3.5" />
                                Enter Room
                            </Link>

                            <!-- Members need code to join -->
                            <button
                                v-else
                                class="flex-1 flex items-center justify-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold transition-colors"
                                :class="room.status === 'active'
                                    ? 'bg-teal-500 text-cream-200 hover:bg-teal-600'
                                    : 'bg-gray-200 text-gray-400 cursor-not-allowed'"
                                :disabled="room.status !== 'active'"
                                @click="openJoinModal(room)"
                            >
                                <LockClosedIcon class="w-3.5 h-3.5" />
                                Join with Code
                            </button>

                            <!-- Close room — admin only -->
                            <button
                                v-if="isAdmin && room.status === 'active'"
                                class="px-3 py-2 rounded-xl bg-red-50 text-red-500 text-xs font-semibold hover:bg-red-100 transition-colors"
                                @click="closeRoom(room)"
                            >
                                Close
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty state -->
            <div v-else class="flex flex-col items-center justify-center h-64 bg-cream-200 rounded-2xl border border-teal-100">
                <VideoCameraIcon class="w-10 h-10 text-teal-300 mb-3" />
                <p class="text-sm text-teal-500 font-medium">No rooms yet.</p>
                <p v-if="canCreate" class="text-xs text-teal-400 mt-1">Create a room and share the code with your team.</p>
            </div>
        </div>

        <!-- ===================== CREATE ROOM MODAL ===================== -->
        <Teleport to="body">
            <div
                v-if="showCreateModal"
                class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-teal-900/50 backdrop-blur-sm"
                @click.self="showCreateModal = false"
            >
                <div class="bg-cream-200 rounded-2xl shadow-xl w-full max-w-md border border-teal-100">
                    <div class="flex items-center justify-between px-6 py-4 border-b border-teal-100">
                        <h2 class="text-sm font-bold text-teal-800">Create Video Call Room</h2>
                        <button
                            class="p-1.5 rounded-lg text-teal-400 hover:bg-teal-100 transition-colors"
                            @click="showCreateModal = false"
                        >
                            <XMarkIcon class="w-4 h-4" />
                        </button>
                    </div>

                    <form @submit.prevent="createRoom" class="p-6 space-y-4">
                        <div class="space-y-1.5">
                            <label class="block text-xs font-semibold text-teal-700 uppercase tracking-wide">Room Title</label>
                            <input
                                v-model="createForm.title"
                                type="text"
                                placeholder="e.g. ALW Weekly Briefing"
                                class="w-full px-3 py-2.5 rounded-xl border border-teal-200 text-sm text-teal-800 placeholder-teal-300 bg-white outline-none focus:border-teal-400 focus:ring-2 focus:ring-teal-100"
                            />
                            <p v-if="createForm.errors.title" class="text-xs text-red-500">{{ createForm.errors.title }}</p>
                        </div>

                        <div class="space-y-1.5">
                            <label class="block text-xs font-semibold text-teal-700 uppercase tracking-wide">
                                Schedule <span class="text-teal-400 font-normal">(optional)</span>
                            </label>
                            <input
                                v-model="createForm.scheduled_at"
                                type="datetime-local"
                                class="w-full px-3 py-2.5 rounded-xl border border-teal-200 text-sm text-teal-700 bg-white outline-none focus:border-teal-400 focus:ring-2 focus:ring-teal-100"
                            />
                        </div>

                        <div class="space-y-1.5">
                            <label class="block text-xs font-semibold text-teal-700 uppercase tracking-wide">
                                Description <span class="text-teal-400 font-normal">(optional)</span>
                            </label>
                            <textarea
                                v-model="createForm.description"
                                rows="2"
                                placeholder="What's this call about?"
                                class="w-full px-3 py-2.5 rounded-xl border border-teal-200 text-sm text-teal-800 placeholder-teal-300 bg-white outline-none focus:border-teal-400 focus:ring-2 focus:ring-teal-100 resize-none"
                            />
                        </div>

                        <div class="p-3 rounded-xl bg-teal-50 border border-teal-100 text-xs text-teal-600">
                            A unique room code will be auto-generated. Share it with your members so they can join.
                        </div>

                        <div class="flex justify-end gap-3 pt-1">
                            <button
                                type="button"
                                class="px-4 py-2.5 rounded-xl border border-teal-200 text-sm text-teal-600 hover:bg-teal-50 transition-colors"
                                @click="showCreateModal = false"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                :disabled="createForm.processing"
                                class="px-5 py-2.5 rounded-xl bg-teal-500 text-cream-200 text-sm font-semibold hover:bg-teal-600 transition-colors disabled:opacity-60"
                            >
                                {{ createForm.processing ? 'Creating…' : 'Create Room' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Teleport>

        <!-- ===================== JOIN WITH CODE MODAL ===================== -->
        <Teleport to="body">
            <div
                v-if="showJoinModal"
                class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-teal-900/50 backdrop-blur-sm"
                @click.self="showJoinModal = false"
            >
                <div class="bg-cream-200 rounded-2xl shadow-xl w-full max-w-sm border border-teal-100">
                    <div class="flex items-center justify-between px-6 py-4 border-b border-teal-100">
                        <h2 class="text-sm font-bold text-teal-800">Enter Room Code</h2>
                        <button
                            class="p-1.5 rounded-lg text-teal-400 hover:bg-teal-100 transition-colors"
                            @click="showJoinModal = false"
                        >
                            <XMarkIcon class="w-4 h-4" />
                        </button>
                    </div>
                    <div class="p-6 space-y-4">
                        <div>
                            <p class="text-sm text-teal-600 mb-1">Joining: <span class="font-semibold text-teal-800">{{ selectedRoom?.title }}</span></p>
                            <p class="text-xs text-teal-400">Enter the room code shared by your upline or admin.</p>
                        </div>
                        <div class="space-y-1.5">
                            <input
                                v-model="joinCode"
                                type="text"
                                placeholder="e.g. ABCD1234"
                                maxlength="12"
                                class="w-full px-3 py-3 rounded-xl border border-teal-200 text-center text-lg font-bold text-teal-800 tracking-widest bg-white outline-none focus:border-teal-400 focus:ring-2 focus:ring-teal-100 uppercase"
                                @keyup.enter="joinRoom"
                            />
                            <p v-if="joinError" class="text-xs text-red-500 text-center">{{ joinError }}</p>
                        </div>
                        <button
                            class="w-full flex items-center justify-center gap-2 px-4 py-3 rounded-xl bg-teal-500 text-cream-200 text-sm font-semibold hover:bg-teal-600 transition-colors"
                            @click="joinRoom"
                        >
                            <ArrowRightIcon class="w-4 h-4" />
                            Join Room
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>
    </AppLayout>
</template>
