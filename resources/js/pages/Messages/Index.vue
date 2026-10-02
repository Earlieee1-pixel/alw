<script setup>
import { ref, computed, watch, nextTick, onMounted, onUnmounted } from 'vue'
import { useForm, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import {
    PaperAirplaneIcon,
    MagnifyingGlassIcon,
    PlusIcon,
    XMarkIcon,
    ChatBubbleLeftRightIcon,
} from '@heroicons/vue/24/outline'

const props = defineProps({
    conversations: Array,
    members:       Array,
    activeId:      Number,
    messages:      Array,
    otherUser:     Object,
})

// =========================================================
// CONVERSATION LIST + SEARCH
// =========================================================

const search       = ref('')
const filteredConvs = computed(() =>
    props.conversations.filter(c =>
        c.other_name.toLowerCase().includes(search.value.toLowerCase())
    )
)

// =========================================================
// NEW MESSAGE MODAL
// =========================================================

const showNewModal   = ref(false)
const newMemberSearch = ref('')
const filteredMembers = computed(() =>
    props.members.filter(m =>
        m.name.toLowerCase().includes(newMemberSearch.value.toLowerCase())
    )
)

function startConversation(userId) {
    router.post(route('messages.start'), { user_id: userId }, {
        onSuccess: () => {
            showNewModal.value   = false
            newMemberSearch.value = ''
        },
    })
}

// =========================================================
// MESSAGE THREAD + SEND
// =========================================================

const localMessages = ref([...props.messages])
const messageBody   = ref('')
const threadRef     = ref(null)
const sending       = ref(false)

// I-scroll sa pinaka-ubos sa thread
function scrollToBottom() {
    nextTick(() => {
        if (threadRef.value) {
            threadRef.value.scrollTop = threadRef.value.scrollHeight
        }
    })
}

// I-send ang message
async function sendMessage() {
    if (! messageBody.value.trim() || ! props.activeId || sending.value) return

    sending.value = true
    const body    = messageBody.value.trim()
    messageBody.value = ''

    router.post(route('messages.send', props.activeId), { body }, {
        preserveScroll: true,
        onSuccess: () => {
            sending.value = false
            pollMessages()
        },
        onError: () => {
            sending.value     = false
            messageBody.value = body
        },
    })
}

// =========================================================
// POLLING — i-check ang bag-ong messages every 3 seconds
// =========================================================

let pollInterval = null

async function pollMessages() {
    if (! props.activeId) return
    try {
        const res  = await fetch(route('messages.poll', props.activeId))
        const data = await res.json()
        localMessages.value = data.messages
        scrollToBottom()
    } catch {
        // Silent fail — mag-retry sa next poll
    }
}

onMounted(() => {
    scrollToBottom()
    if (props.activeId) {
        // I-start ang polling kung naa active conversation
        pollInterval = setInterval(pollMessages, 3000)
    }
})

onUnmounted(() => {
    if (pollInterval) clearInterval(pollInterval)
})

// I-restart ang polling kung mag-change ang active conversation
watch(() => props.activeId, (newId) => {
    if (pollInterval) clearInterval(pollInterval)
    localMessages.value = [...props.messages]
    scrollToBottom()
    if (newId) {
        pollInterval = setInterval(pollMessages, 3000)
    }
})

// Group messages by date para sa date dividers
const groupedMessages = computed(() => {
    const groups = {}
    localMessages.value.forEach(msg => {
        if (! groups[msg.date]) groups[msg.date] = []
        groups[msg.date].push(msg)
    })
    return groups
})
</script>

<template>
    <AppLayout>
        <template #header>
            <h1 class="text-base font-semibold text-teal-800">Messages</h1>
        </template>

        <div class="max-w-6xl mx-auto h-[calc(100vh-8rem)] flex gap-4">

            <!-- ===================== CONVERSATIONS SIDEBAR ===================== -->
            <div class="w-72 shrink-0 bg-cream-200 rounded-2xl border border-teal-100 flex flex-col overflow-hidden">

                <!-- Header -->
                <div class="px-4 pt-4 pb-3 border-b border-teal-100 space-y-3">
                    <div class="flex items-center justify-between">
                        <h2 class="text-sm font-bold text-teal-800">Chats</h2>
                        <button
                            class="p-1.5 rounded-lg bg-teal-500 text-cream-200 hover:bg-teal-600 transition-colors"
                            title="New message"
                            @click="showNewModal = true"
                        >
                            <PlusIcon class="w-3.5 h-3.5" />
                        </button>
                    </div>

                    <!-- Search -->
                    <div class="relative">
                        <MagnifyingGlassIcon class="absolute left-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-teal-400 pointer-events-none" />
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Search..."
                            class="w-full pl-8 pr-3 py-2 rounded-xl border border-teal-200 text-xs text-teal-800 placeholder-teal-300 bg-white outline-none focus:border-teal-400 transition-colors"
                        />
                    </div>
                </div>

                <!-- Conversation list -->
                <div class="flex-1 overflow-y-auto divide-y divide-teal-50">

                    <div v-if="filteredConvs.length === 0" class="flex flex-col items-center justify-center py-12 gap-2">
                        <ChatBubbleLeftRightIcon class="w-8 h-8 text-teal-300" />
                        <p class="text-xs text-teal-400 text-center px-4">
                            No conversations yet. Start one by clicking +
                        </p>
                    </div>

                    <button
                        v-for="conv in filteredConvs"
                        :key="conv.id"
                        class="w-full flex items-center gap-3 px-4 py-3 transition-colors text-left hover:bg-teal-50"
                        :class="conv.id === activeId ? 'bg-teal-50 border-l-2 border-teal-500' : ''"
                        @click="router.visit(route('messages.show', conv.id))"
                    >
                        <!-- Avatar -->
                        <div class="w-9 h-9 rounded-full bg-teal-500 flex items-center justify-center text-xs font-bold text-cream-200 shrink-0">
                            {{ conv.other_avatar }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-semibold text-teal-800 truncate">{{ conv.other_name }}</span>
                                <span class="text-[10px] text-teal-400 shrink-0 ml-1">{{ conv.last_at }}</span>
                            </div>
                            <div class="flex items-center justify-between mt-0.5">
                                <p class="text-xs text-teal-400 truncate">
                                    <span v-if="conv.is_mine" class="text-teal-500">You: </span>
                                    {{ conv.last_message }}
                                </p>
                                <span
                                    v-if="conv.unread > 0"
                                    class="ml-1 shrink-0 w-4 h-4 rounded-full bg-teal-500 text-cream-200 text-[9px] font-bold flex items-center justify-center"
                                >
                                    {{ conv.unread }}
                                </span>
                            </div>
                        </div>
                    </button>
                </div>
            </div>

            <!-- ===================== MESSAGE THREAD ===================== -->
            <div class="flex-1 bg-cream-200 rounded-2xl border border-teal-100 flex flex-col overflow-hidden">

                <!-- Empty state — no conversation selected -->
                <div v-if="! activeId" class="flex-1 flex flex-col items-center justify-center gap-3 text-center px-6">
                    <ChatBubbleLeftRightIcon class="w-12 h-12 text-teal-300" />
                    <h3 class="text-sm font-semibold text-teal-600">Select a conversation</h3>
                    <p class="text-xs text-teal-400 max-w-xs">
                        Choose from your existing chats on the left, or start a new conversation.
                    </p>
                    <button
                        class="mt-2 flex items-center gap-2 px-4 py-2.5 rounded-xl bg-teal-500 text-cream-200 text-sm font-semibold hover:bg-teal-600 transition-colors"
                        @click="showNewModal = true"
                    >
                        <PlusIcon class="w-4 h-4" />
                        New Message
                    </button>
                </div>

                <template v-else>
                    <!-- Thread header -->
                    <div class="px-5 py-3.5 border-b border-teal-100 flex items-center gap-3 shrink-0">
                        <div class="w-8 h-8 rounded-full bg-teal-500 flex items-center justify-center text-xs font-bold text-cream-200 shrink-0">
                            {{ otherUser?.avatar }}
                        </div>
                        <div>
                            <div class="text-sm font-bold text-teal-800">{{ otherUser?.name }}</div>
                            <div class="text-xs text-teal-400">Member</div>
                        </div>
                    </div>

                    <!-- Messages -->
                    <div
                        ref="threadRef"
                        class="flex-1 overflow-y-auto px-5 py-4 space-y-4"
                    >
                        <template v-for="(msgs, date) in groupedMessages" :key="date">
                            <!-- Date divider -->
                            <div class="flex items-center gap-3 my-2">
                                <div class="flex-1 h-px bg-teal-100" />
                                <span class="text-[10px] text-teal-400 font-medium shrink-0">{{ date }}</span>
                                <div class="flex-1 h-px bg-teal-100" />
                            </div>

                            <!-- Messages in this date group -->
                            <div
                                v-for="msg in msgs"
                                :key="msg.id"
                                class="flex"
                                :class="msg.is_mine ? 'justify-end' : 'justify-start'"
                            >
                                <!-- Other person's avatar -->
                                <div v-if="! msg.is_mine" class="w-6 h-6 rounded-full bg-teal-400 flex items-center justify-center text-[10px] font-bold text-cream-200 shrink-0 mr-2 mt-1">
                                    {{ otherUser?.avatar }}
                                </div>

                                <div class="max-w-xs space-y-0.5">
                                    <div
                                        class="px-4 py-2.5 rounded-2xl text-sm leading-relaxed"
                                        :class="msg.is_mine
                                            ? 'bg-teal-500 text-cream-200 rounded-br-sm'
                                            : 'bg-white text-teal-800 border border-teal-100 rounded-bl-sm'"
                                    >
                                        {{ msg.body }}
                                    </div>
                                    <div
                                        class="text-[10px] text-teal-400 px-1"
                                        :class="msg.is_mine ? 'text-right' : 'text-left'"
                                    >
                                        {{ msg.created_at }}
                                    </div>
                                </div>
                            </div>
                        </template>

                        <!-- Empty thread -->
                        <div v-if="localMessages.length === 0" class="flex flex-col items-center justify-center h-full gap-2 py-12">
                            <p class="text-xs text-teal-400">No messages yet. Say hello!</p>
                        </div>
                    </div>

                    <!-- Message input -->
                    <div class="px-4 py-3 border-t border-teal-100 shrink-0">
                        <div class="flex items-end gap-2">
                            <textarea
                                v-model="messageBody"
                                rows="1"
                                placeholder="Type a message..."
                                class="flex-1 px-4 py-2.5 rounded-2xl border border-teal-200 text-sm text-teal-800 placeholder-teal-300 bg-white outline-none focus:border-teal-400 focus:ring-2 focus:ring-teal-100 transition-colors resize-none"
                                style="max-height: 120px;"
                                @keydown.enter.exact.prevent="sendMessage"
                                @input="$event.target.style.height = 'auto'; $event.target.style.height = $event.target.scrollHeight + 'px'"
                            />
                            <button
                                class="p-2.5 rounded-2xl bg-teal-500 text-cream-200 hover:bg-teal-600 transition-colors shrink-0 disabled:opacity-50"
                                :disabled="! messageBody.trim() || sending"
                                @click="sendMessage"
                            >
                                <PaperAirplaneIcon class="w-4 h-4" />
                            </button>
                        </div>
                        <p class="text-[10px] text-teal-400 mt-1.5 ml-1">Press Enter to send</p>
                    </div>
                </template>
            </div>
        </div>

        <!-- ===================== NEW MESSAGE MODAL ===================== -->
        <Teleport to="body">
            <div
                v-if="showNewModal"
                class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-teal-900/50 backdrop-blur-sm"
                @click.self="showNewModal = false"
            >
                <div class="bg-cream-200 rounded-2xl shadow-xl w-full max-w-sm border border-teal-100">
                    <div class="flex items-center justify-between px-6 py-4 border-b border-teal-100">
                        <h2 class="text-sm font-bold text-teal-800">New Message</h2>
                        <button
                            class="p-1.5 rounded-lg text-teal-400 hover:bg-teal-100 transition-colors"
                            @click="showNewModal = false"
                        >
                            <XMarkIcon class="w-4 h-4" />
                        </button>
                    </div>
                    <div class="p-5 space-y-3">
                        <!-- Search members -->
                        <div class="relative">
                            <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-teal-400 pointer-events-none" />
                            <input
                                v-model="newMemberSearch"
                                type="text"
                                placeholder="Search member..."
                                class="w-full pl-9 pr-4 py-2.5 rounded-xl border border-teal-200 text-sm text-teal-800 placeholder-teal-300 bg-white outline-none focus:border-teal-400 focus:ring-2 focus:ring-teal-100 transition-colors"
                                autofocus
                            />
                        </div>

                        <!-- Member list -->
                        <div class="max-h-60 overflow-y-auto space-y-1 rounded-xl border border-teal-100 divide-y divide-teal-50">
                            <div v-if="filteredMembers.length === 0" class="py-6 text-center text-xs text-teal-400">
                                No members found.
                            </div>
                            <button
                                v-for="member in filteredMembers"
                                :key="member.id"
                                class="w-full flex items-center gap-3 px-4 py-3 hover:bg-teal-50 transition-colors text-left"
                                @click="startConversation(member.id)"
                            >
                                <div class="w-8 h-8 rounded-full bg-teal-500 flex items-center justify-center text-xs font-bold text-cream-200 shrink-0">
                                    {{ member.name[0].toUpperCase() }}
                                </div>
                                <span class="text-sm text-teal-800 font-medium">{{ member.name }}</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </Teleport>
    </AppLayout>
</template>
