<script setup>
import { ref, watch } from 'vue'
import { router, useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { useConfirm } from '@/composables/useConfirm'
import {
    MagnifyingGlassIcon,
    FunnelIcon,
    CheckCircleIcon,
    NoSymbolIcon,
    ShieldCheckIcon,
    UserIcon,
    ChevronLeftIcon,
    ChevronRightIcon,
    ArrowPathIcon,
} from '@heroicons/vue/24/outline'
import { CheckCircleIcon as CheckSolid } from '@heroicons/vue/24/solid'

const props = defineProps({
    members: Object,
    filters: Object,
    totals:  Object,
})

// Search ug filter state
const search = ref(props.filters.search ?? '')
const status = ref(props.filters.status ?? '')
const role   = ref(props.filters.role ?? '')

// I-debounce ang search — dili mag-request sa matag keystroke
let searchTimeout = null
watch(search, (val) => {
    clearTimeout(searchTimeout)
    searchTimeout = setTimeout(() => applyFilters(), 400)
})

watch([status, role], () => applyFilters())

function applyFilters() {
    router.get(route('admin.members.index'), {
        search: search.value || undefined,
        status: status.value || undefined,
        role:   role.value   || undefined,
    }, { preserveState: true, preserveScroll: true })
}

function clearFilters() {
    search.value = ''
    status.value = ''
    role.value   = ''
    applyFilters()
}

const { confirm } = useConfirm()

// Activate/suspend actions
function activate(member) {
    router.post(route('admin.members.activate', member.id), {}, {
        preserveScroll: true,
    })
}

async function suspend(member) {
    const ok = await confirm(`${member.name}'s account will be suspended and they will lose access to the platform.`, {
        title: 'Suspend Account?',
        confirmLabel: 'Yes, Suspend',
        danger: true,
    })
    if (! ok) return
    router.post(route('admin.members.suspend', member.id), {}, {
        preserveScroll: true,
    })
}

// Update role
const roleForm = useForm({ role: '' })
function updateRole(member, newRole) {
    roleForm.role = newRole
    roleForm.post(route('admin.members.role', member.id), {
        preserveScroll: true,
        onSuccess: () => roleForm.reset(),
    })
}

// Status badge styling
function statusClass(s) {
    return {
        active:    'bg-green-100 text-green-700',
        inactive:  'bg-gray-100 text-gray-500',
        suspended: 'bg-red-100 text-red-600',
    }[s] ?? 'bg-gray-100 text-gray-500'
}

const hasFilters = ref(
    !!(props.filters.search || props.filters.status || props.filters.role)
)
</script>

<template>
    <AppLayout>
        <template #header>
            <h1 class="text-base font-semibold text-teal-800">All Members</h1>
        </template>

        <div class="space-y-5 max-w-7xl mx-auto">

            <!-- ===================== TOTALS ROW ===================== -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div
                    v-for="card in [
                        { label: 'Total',     value: totals.all,       color: 'teal'  },
                        { label: 'Active',    value: totals.active,    color: 'green' },
                        { label: 'Suspended', value: totals.suspended, color: 'red'   },
                        { label: 'Inactive',  value: totals.inactive,  color: 'gray'  },
                    ]"
                    :key="card.label"
                    class="bg-cream-200 rounded-2xl p-4 border border-teal-100"
                >
                    <div
                        class="text-2xl font-bold"
                        :class="{
                            'text-teal-600':  card.color === 'teal',
                            'text-green-600': card.color === 'green',
                            'text-red-500':   card.color === 'red',
                            'text-gray-500':  card.color === 'gray',
                        }"
                    >
                        {{ card.value.toLocaleString() }}
                    </div>
                    <div class="text-xs text-teal-500 font-medium mt-0.5">{{ card.label }}</div>
                </div>
            </div>

            <!-- ===================== SEARCH + FILTERS ===================== -->
            <div class="bg-cream-200 rounded-2xl border border-teal-100 p-4">
                <div class="flex flex-col sm:flex-row gap-3">

                    <!-- Search input -->
                    <div class="relative flex-1">
                        <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-teal-400 pointer-events-none" />
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Search by name or email..."
                            class="w-full pl-9 pr-4 py-2.5 rounded-xl border border-teal-200 text-sm text-teal-800 placeholder-teal-300 bg-white outline-none focus:border-teal-400 focus:ring-2 focus:ring-teal-100 transition-colors"
                        />
                    </div>

                    <!-- Status filter -->
                    <div class="relative">
                        <FunnelIcon class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-teal-400 pointer-events-none" />
                        <select
                            v-model="status"
                            class="pl-9 pr-8 py-2.5 rounded-xl border border-teal-200 text-sm text-teal-700 bg-white outline-none focus:border-teal-400 focus:ring-2 focus:ring-teal-100 transition-colors appearance-none cursor-pointer"
                        >
                            <option value="">All Statuses</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="suspended">Suspended</option>
                        </select>
                    </div>

                    <!-- Role filter -->
                    <div class="relative">
                        <ShieldCheckIcon class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-teal-400 pointer-events-none" />
                        <select
                            v-model="role"
                            class="pl-9 pr-8 py-2.5 rounded-xl border border-teal-200 text-sm text-teal-700 bg-white outline-none focus:border-teal-400 focus:ring-2 focus:ring-teal-100 transition-colors appearance-none cursor-pointer"
                        >
                            <option value="">All Roles</option>
                            <option value="admin">Admin</option>
                            <option value="leader">Leader</option>
                            <option value="member">Member</option>
                        </select>
                    </div>

                    <!-- Clear filters -->
                    <button
                        v-if="search || status || role"
                        class="flex items-center gap-1.5 px-4 py-2.5 rounded-xl border border-teal-200 text-sm text-teal-500 hover:bg-teal-50 transition-colors"
                        @click="clearFilters"
                    >
                        <ArrowPathIcon class="w-3.5 h-3.5" />
                        Clear
                    </button>
                </div>
            </div>

            <!-- ===================== MEMBERS TABLE ===================== -->
            <div class="bg-cream-200 rounded-2xl border border-teal-100">

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-teal-100">
                                <th class="text-left px-5 py-3.5 text-xs font-semibold text-teal-500 uppercase tracking-wide">Member</th>
                                <th class="text-left px-5 py-3.5 text-xs font-semibold text-teal-500 uppercase tracking-wide hidden sm:table-cell">Upline</th>
                                <th class="text-left px-5 py-3.5 text-xs font-semibold text-teal-500 uppercase tracking-wide hidden md:table-cell">Downlines</th>
                                <th class="text-left px-5 py-3.5 text-xs font-semibold text-teal-500 uppercase tracking-wide">Role</th>
                                <th class="text-left px-5 py-3.5 text-xs font-semibold text-teal-500 uppercase tracking-wide">Status</th>
                                <th class="text-left px-5 py-3.5 text-xs font-semibold text-teal-500 uppercase tracking-wide hidden lg:table-cell">Joined</th>
                                <th class="text-right px-5 py-3.5 text-xs font-semibold text-teal-500 uppercase tracking-wide">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-teal-50">
                            <tr
                                v-for="member in members.data"
                                :key="member.id"
                                class="hover:bg-teal-50/40 transition-colors"
                            >
                                <!-- Member name + email -->
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-teal-500 flex items-center justify-center text-xs font-bold text-cream-200 shrink-0">
                                            {{ member.name.charAt(0).toUpperCase() }}
                                        </div>
                                        <div>
                                            <div class="font-semibold text-teal-800 text-sm">{{ member.name }}</div>
                                            <div class="text-xs text-teal-400">{{ member.email }}</div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Upline -->
                                <td class="px-5 py-4 text-sm text-teal-600 hidden sm:table-cell">
                                    {{ member.upline }}
                                </td>

                                <!-- Downline count -->
                                <td class="px-5 py-4 hidden md:table-cell">
                                    <span class="inline-flex items-center gap-1 text-xs text-teal-600 font-medium">
                                        <UserIcon class="w-3.5 h-3.5 text-teal-400" />
                                        {{ member.downlines }}
                                    </span>
                                </td>

                                <!-- Role — clickable to change -->
                                <td class="px-5 py-4">
                                    <select
                                        :value="member.roles[0] ?? 'member'"
                                        class="text-xs px-2 py-1 rounded-lg border border-teal-200 bg-teal-50 text-teal-700 font-medium cursor-pointer outline-none focus:border-teal-400 capitalize"
                                        @change="updateRole(member, $event.target.value)"
                                    >
                                        <option value="admin">Admin</option>
                                        <option value="leader">Leader</option>
                                        <option value="member">Member</option>
                                    </select>
                                </td>

                                <!-- Status badge -->
                                <td class="px-5 py-4">
                                    <span
                                        class="inline-flex text-xs px-2 py-0.5 rounded-full font-medium capitalize"
                                        :class="statusClass(member.status)"
                                    >
                                        {{ member.status }}
                                    </span>
                                </td>

                                <!-- Joined -->
                                <td class="px-5 py-4 text-xs text-teal-400 hidden lg:table-cell" :title="member.joined">
                                    {{ member.joined_ago }}
                                </td>

                                <!-- Action buttons -->
                                <td class="px-5 py-4">
                                    <div class="flex items-center justify-end gap-2">
                                        <button
                                            v-if="member.status !== 'active'"
                                            class="flex items-center gap-1 px-3 py-1.5 rounded-lg bg-green-100 text-green-700 text-xs font-semibold hover:bg-green-200 transition-colors"
                                            @click="activate(member)"
                                        >
                                            <CheckSolid class="w-3.5 h-3.5" />
                                            Activate
                                        </button>
                                        <button
                                            v-if="member.status !== 'suspended'"
                                            class="flex items-center gap-1 px-3 py-1.5 rounded-lg bg-red-100 text-red-600 text-xs font-semibold hover:bg-red-200 transition-colors"
                                            @click="suspend(member)"
                                        >
                                            <NoSymbolIcon class="w-3.5 h-3.5" />
                                            Suspend
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <!-- Empty state -->
                            <tr v-if="members.data.length === 0">
                                <td colspan="7" class="px-5 py-12 text-center text-sm text-teal-400">
                                    No members found matching your filters.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div
                    v-if="members.last_page > 1"
                    class="flex items-center justify-between px-5 py-4 border-t border-teal-100"
                >
                    <p class="text-xs text-teal-500">
                        Showing {{ members.from }}–{{ members.to }} of {{ members.total }} members
                    </p>
                    <div class="flex items-center gap-1">
                        <a
                            v-if="members.prev_page_url"
                            :href="members.prev_page_url"
                            class="p-1.5 rounded-lg border border-teal-200 text-teal-500 hover:bg-teal-50 transition-colors"
                        >
                            <ChevronLeftIcon class="w-4 h-4" />
                        </a>
                        <span class="px-3 py-1.5 text-xs font-semibold text-teal-700">
                            {{ members.current_page }} / {{ members.last_page }}
                        </span>
                        <a
                            v-if="members.next_page_url"
                            :href="members.next_page_url"
                            class="p-1.5 rounded-lg border border-teal-200 text-teal-500 hover:bg-teal-50 transition-colors"
                        >
                            <ChevronRightIcon class="w-4 h-4" />
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
