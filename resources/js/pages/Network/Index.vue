<script setup>
import { ref, computed } from 'vue'
import { router, useForm, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { useConfirm } from '@/composables/useConfirm'
import {
    HomeIcon,
    ChevronRightIcon,
    ArrowPathIcon,
    PencilIcon,
    ArrowsPointingOutIcon,
    XMarkIcon,
    CheckIcon,
    TrashIcon,
} from '@heroicons/vue/24/outline'
import { useAuth } from '@/composables/useAuth'

const props = defineProps({
    nodes:      Array,
    rootKey:    String,
    breadcrumb: Array,
})

const { isAdmin } = useAuth()
const { confirm } = useConfirm()

// =========================================================
// TREE LAYOUT — SVG binary tree positioning
// =========================================================

const LEVELS    = 5
const R         = 24      // circle radius
const H_SPACING = 62      // horizontal space per leaf node
const V_SPACING = 80      // vertical space between levels

/**
 * I-build ang position lookup gikan sa flat nodes array.
 * Key = position_key, value = node object.
 */
const nodeMap = computed(() => {
    const map = {}
    props.nodes.forEach(n => { map[n.position_key] = n })
    return map
})

/**
 * I-compute ang x,y coordinates sa matag node
 * gamit ang recursive centering — parent is centered over its children.
 */
const positions = computed(() => {
    const pos   = {}
    const root  = props.nodes.find(n => n.position_key === props.rootKey)
    if (! root) return pos

    // I-count ang total leaf nodes (level 5) para ma-compute ang total width
    const leafCount = Math.pow(2, LEVELS - 1) // 16 leaves

    // I-assign positions sa leaves first (level 4, 0-indexed)
    let leafIndex = 0

    function assignPos(key, depth) {
        const node = nodeMap.value[key]
        if (! node) return

        if (depth === LEVELS - 1) {
            // Leaf node — assign sequential x position
            pos[key] = {
                x: leafIndex * H_SPACING + R,
                y: depth * V_SPACING + R + 20,
            }
            leafIndex++
        } else {
            // I-assign ang children first
            const leftKey  = key + '-L'
            const rightKey = key + '-R'
            assignPos(leftKey,  depth + 1)
            assignPos(rightKey, depth + 1)

            // Center parent over children
            const leftPos  = pos[leftKey]
            const rightPos = pos[rightKey]

            if (leftPos && rightPos) {
                pos[key] = {
                    x: (leftPos.x + rightPos.x) / 2,
                    y: depth * V_SPACING + R + 20,
                }
            } else if (leftPos) {
                pos[key] = { x: leftPos.x, y: depth * V_SPACING + R + 20 }
            } else {
                pos[key] = { x: leafIndex * H_SPACING + R, y: depth * V_SPACING + R + 20 }
            }
        }
    }

    assignPos(props.rootKey, 0)
    return pos
})

// Total SVG canvas dimensions
const svgWidth  = computed(() => {
    const vals = Object.values(positions.value).map(p => p.x)
    return vals.length ? Math.max(...vals) + R + 20 : 800
})
const svgHeight = computed(() => (LEVELS * V_SPACING) + R + 40)

// I-collect ang all edges (parent → child lines)
const edges = computed(() => {
    const lines = []
    props.nodes.forEach(node => {
        if (node.parent_key && positions.value[node.position_key] && positions.value[node.parent_key]) {
            lines.push({
                x1: positions.value[node.parent_key].x,
                y1: positions.value[node.parent_key].y + R,
                x2: positions.value[node.position_key].x,
                y2: positions.value[node.position_key].y - R,
            })
        }
    })
    return lines
})

// =========================================================
// HOVER POPUP
// =========================================================

const hoveredNode   = ref(null)
const popupPosition = ref({ x: 0, y: 0 })

function onNodeMouseEnter(node, event) {
    hoveredNode.value   = node
    const pos           = positions.value[node.position_key]
    popupPosition.value = { x: pos.x, y: pos.y }
}

function onNodeMouseLeave() {
    // Small delay para dili ma-close agad kung mag-hover sa popup
    setTimeout(() => {
        if (! popupHovered.value) hoveredNode.value = null
    }, 150)
}

const popupHovered = ref(false)

// =========================================================
// SET NAME MODAL
// =========================================================

const showModal    = ref(false)
const editingNode  = ref(null)

const nameForm = useForm({ position_key: '', name: '' })

function openSetName(node) {
    editingNode.value      = node
    nameForm.position_key  = node.position_key
    nameForm.name          = node.name ?? ''
    showModal.value        = true
    hoveredNode.value      = null
}

function saveName() {
    nameForm.post(route('tree.set-name'), {
        preserveScroll: true,
        onSuccess: () => {
            showModal.value   = false
            editingNode.value = null
            nameForm.reset()
        },
    })
}

// Clear name — admin only
const clearForm = useForm({ position_key: '' })
function clearName(node) {
    if (confirm(`Clear the name "${node.name}" from position ${node.display_number}?`)) {
        clearForm.position_key = node.position_key
        clearForm.post(route('tree.clear-name'), { preserveScroll: true })
    }
    hoveredNode.value = null
}

// =========================================================
// NAVIGATION
// =========================================================

function drillInto(node) {
    // Only drill into filled nodes
    if (! node.is_filled && ! isAdmin.value) return
    router.get(route('network.tree'), { root: node.position_key }, {
        preserveScroll: false,
    })
    hoveredNode.value = null
}

function navigateTo(key) {
    router.get(route('network.tree'), { root: key }, { preserveScroll: false })
}

function resetTree() {
    router.get(route('network.tree'), {}, { preserveScroll: false })
}

// =========================================================
// NODE STYLE HELPERS
// =========================================================

function nodeStyle(node) {
    if (! node.is_filled) {
        return { fill: '#f0fafa', stroke: '#a3dcdc', textColor: '#6ec4c3' }
    }
    if (node.position_key === props.rootKey) {
        return { fill: '#288783', stroke: '#1f6e6b', textColor: '#ffebd0' }
    }
    return { fill: '#ffffff', stroke: '#288783', textColor: '#103c3b' }
}

function displayName(node) {
    if (! node.is_filled) return String(node.display_number)
    // Show first name only if too long
    const parts = node.name.trim().split(' ')
    if (parts[0].length <= 8) return parts[0]
    return parts[0].substring(0, 7) + '.'
}

function displaySub(node) {
    if (! node.is_filled) return null
    const parts = node.name.trim().split(' ')
    if (parts.length < 2) return null
    const sub = parts.slice(1).join(' ')
    return sub.length > 8 ? sub.substring(0, 7) + '.' : sub
}
</script>

<template>
    <AppLayout>
        <template #header>
            <h1 class="text-base font-semibold text-teal-800">Network Tree</h1>
        </template>

        <div class="space-y-4">

            <!-- ===================== BREADCRUMB ===================== -->
            <div class="flex items-center gap-1 flex-wrap bg-cream-200 rounded-2xl border border-teal-100 px-4 py-3">
                <button
                    class="p-1.5 rounded-lg text-teal-400 hover:text-teal-600 hover:bg-teal-100 transition-colors"
                    title="Back to root"
                    @click="resetTree"
                >
                    <HomeIcon class="w-4 h-4" />
                </button>

                <template v-for="(crumb, i) in breadcrumb" :key="crumb.key">
                    <ChevronRightIcon class="w-3 h-3 text-teal-300 shrink-0" />
                    <button
                        class="text-xs font-semibold px-2.5 py-1 rounded-lg transition-colors truncate max-w-[120px]"
                        :class="i === breadcrumb.length - 1
                            ? 'text-teal-800 bg-teal-100 cursor-default'
                            : 'text-teal-500 hover:text-teal-700 hover:bg-teal-50'"
                        @click="i < breadcrumb.length - 1 && navigateTo(crumb.key)"
                    >
                        {{ crumb.name }}
                    </button>
                </template>

                <button
                    v-if="breadcrumb.length > 1"
                    class="ml-auto flex items-center gap-1 text-xs text-teal-400 hover:text-teal-600 px-2 py-1 rounded-lg hover:bg-teal-50 transition-colors shrink-0"
                    @click="resetTree"
                >
                    <ArrowPathIcon class="w-3.5 h-3.5" />
                    Reset
                </button>
            </div>

            <!-- ===================== LEGEND ===================== -->
            <div class="flex items-center gap-5 px-1 flex-wrap">
                <div class="flex items-center gap-1.5">
                    <div class="w-4 h-4 rounded-full bg-teal-500 border-2 border-teal-600" />
                    <span class="text-xs text-teal-500">Current Root</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <div class="w-4 h-4 rounded-full bg-white border-2 border-teal-500" />
                    <span class="text-xs text-teal-500">Filled</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <div class="w-4 h-4 rounded-full bg-teal-50 border-2 border-teal-200" />
                    <span class="text-xs text-teal-400">Empty slot (shows number)</span>
                </div>
                <span class="text-xs text-teal-400 border-l border-teal-100 pl-4">
                    Hover any circle for options
                </span>
            </div>

            <!-- ===================== SVG TREE CANVAS ===================== -->
            <div class="bg-cream-200 rounded-2xl border border-teal-100 overflow-auto relative">
                <div class="p-4" style="min-width: fit-content;">
                    <svg
                        :width="svgWidth"
                        :height="svgHeight"
                        class="overflow-visible block mx-auto"
                    >
                        <!-- Drop shadow filter -->
                        <defs>
                            <filter id="node-shadow" x="-20%" y="-20%" width="140%" height="140%">
                                <feDropShadow dx="0" dy="2" stdDeviation="3" flood-color="#288783" flood-opacity="0.15" />
                            </filter>
                        </defs>

                        <!-- ===== CONNECTOR LINES ===== -->
                        <g class="edges">
                            <line
                                v-for="(edge, i) in edges"
                                :key="'e' + i"
                                :x1="edge.x1"
                                :y1="edge.y1"
                                :x2="edge.x2"
                                :y2="edge.y2"
                                stroke="#a3dcdc"
                                stroke-width="1.5"
                                stroke-linecap="round"
                            />
                        </g>

                        <!-- ===== NODES ===== -->
                        <g
                            v-for="node in nodes"
                            :key="node.position_key"
                            class="node-group"
                            style="cursor: pointer;"
                            :transform="positions[node.position_key]
                                ? `translate(${positions[node.position_key].x - R}, ${positions[node.position_key].y - R})`
                                : 'translate(0,0)'"
                            @mouseenter="onNodeMouseEnter(node, $event)"
                            @mouseleave="onNodeMouseLeave"
                        >
                            <!-- Root pulse ring -->
                            <circle
                                v-if="node.position_key === rootKey"
                                :cx="R"
                                :cy="R"
                                :r="R + 6"
                                fill="none"
                                stroke="#288783"
                                stroke-width="2"
                                stroke-dasharray="5 3"
                                opacity="0.4"
                            />

                            <!-- Main circle -->
                            <circle
                                :cx="R"
                                :cy="R"
                                :r="R"
                                :fill="nodeStyle(node).fill"
                                :stroke="nodeStyle(node).stroke"
                                stroke-width="2"
                                filter="url(#node-shadow)"
                            />

                            <!-- Position number badge (bottom-right) for filled nodes -->
                            <g v-if="node.is_filled">
                                <circle
                                    :cx="R * 1.65"
                                    :cy="R * 1.65"
                                    r="8"
                                    fill="#288783"
                                    stroke="#ffffff"
                                    stroke-width="1.5"
                                />
                                <text
                                    :x="R * 1.65"
                                    :y="R * 1.65 + 1"
                                    text-anchor="middle"
                                    dominant-baseline="middle"
                                    fill="#ffebd0"
                                    font-size="7"
                                    font-weight="700"
                                    font-family="system-ui, sans-serif"
                                    class="select-none pointer-events-none"
                                >
                                    {{ node.display_number }}
                                </text>
                            </g>

                            <!-- Primary text (name or number) -->
                            <text
                                :x="R"
                                :y="node.is_filled && displaySub(node) ? R - 4 : R + 1"
                                text-anchor="middle"
                                dominant-baseline="middle"
                                :fill="nodeStyle(node).textColor"
                                :font-size="node.is_filled ? 8 : 11"
                                :font-weight="node.is_filled ? '600' : '700'"
                                font-family="system-ui, sans-serif"
                                class="select-none pointer-events-none"
                            >
                                {{ displayName(node) }}
                            </text>

                            <!-- Secondary text (last name) -->
                            <text
                                v-if="node.is_filled && displaySub(node)"
                                :x="R"
                                :y="R + 7"
                                text-anchor="middle"
                                dominant-baseline="middle"
                                :fill="nodeStyle(node).textColor"
                                font-size="7"
                                font-family="system-ui, sans-serif"
                                opacity="0.75"
                                class="select-none pointer-events-none"
                            >
                                {{ displaySub(node) }}
                            </text>

                        </g>

                        <!-- ===== HOVER POPUP (SVG foreignObject) ===== -->
                        <foreignObject
                            v-if="hoveredNode && positions[hoveredNode.position_key]"
                            :x="positions[hoveredNode.position_key].x - 70"
                            :y="positions[hoveredNode.position_key].y - R - 90"
                            width="140"
                            height="80"
                            style="overflow: visible;"
                            @mouseenter="popupHovered = true"
                            @mouseleave="popupHovered = false; hoveredNode = null"
                        >
                            <div
                                xmlns="http://www.w3.org/1999/xhtml"
                                class="bg-teal-800 rounded-xl shadow-xl border border-teal-600/40 p-2 space-y-1.5"
                            >
                                <!-- Node label -->
                                <div class="text-center text-xs font-semibold text-cream-200 truncate px-1">
                                    {{ hoveredNode.is_filled ? hoveredNode.name : 'Slot ' + hoveredNode.display_number }}
                                </div>

                                <div class="flex gap-1.5">
                                    <!-- Set / Edit name -->
                                    <button
                                        class="flex-1 flex items-center justify-center gap-1 py-1.5 rounded-lg bg-teal-500 hover:bg-teal-400 text-cream-200 text-[10px] font-semibold transition-colors"
                                        @click="openSetName(hoveredNode)"
                                    >
                                        <PencilIcon class="w-3 h-3" />
                                        {{ hoveredNode.is_filled ? 'Edit' : 'Set Name' }}
                                    </button>

                                    <!-- Drill into / View tree -->
                                    <button
                                        class="flex-1 flex items-center justify-center gap-1 py-1.5 rounded-lg bg-teal-700 hover:bg-teal-600 text-cream-200 text-[10px] font-semibold transition-colors border border-teal-600"
                                        @click="drillInto(hoveredNode)"
                                    >
                                        <ArrowsPointingOutIcon class="w-3 h-3" />
                                        View Tree
                                    </button>
                                </div>

                                <!-- Clear — admin only, filled nodes only -->
                                <button
                                    v-if="isAdmin && hoveredNode.is_filled"
                                    class="w-full flex items-center justify-center gap-1 py-1 rounded-lg bg-red-500/20 hover:bg-red-500/40 text-red-300 text-[10px] font-semibold transition-colors"
                                    @click="clearName(hoveredNode)"
                                >
                                    <TrashIcon class="w-3 h-3" />
                                    Clear Slot
                                </button>
                            </div>
                        </foreignObject>

                    </svg>
                </div>
            </div>

            <!-- ===================== NAME LIST BELOW TREE ===================== -->
            <div class="bg-cream-200 rounded-2xl border border-teal-100 p-5">
                <h3 class="text-xs font-bold text-teal-600 uppercase tracking-widest mb-4">
                    All Slots in Current View
                </h3>

                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-2">
                    <div
                        v-for="node in nodes"
                        :key="node.position_key"
                        class="flex items-center gap-2 px-3 py-2 rounded-xl border text-xs transition-colors cursor-pointer"
                        :class="node.is_filled
                            ? 'bg-white border-teal-200 text-teal-800 hover:border-teal-400'
                            : 'bg-teal-50/50 border-dashed border-teal-200 text-teal-400 hover:border-teal-300'"
                        @click="openSetName(node)"
                    >
                        <!-- Position number badge -->
                        <div
                            class="w-5 h-5 rounded-full flex items-center justify-center text-[9px] font-bold shrink-0"
                            :class="node.is_filled ? 'bg-teal-500 text-cream-200' : 'bg-teal-100 text-teal-400'"
                        >
                            {{ node.display_number }}
                        </div>
                        <span class="truncate font-medium">
                            {{ node.is_filled ? node.name : 'Empty' }}
                        </span>
                    </div>
                </div>
            </div>

        </div>

        <!-- ===================== SET NAME MODAL ===================== -->
        <Teleport to="body">
            <div
                v-if="showModal"
                class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-teal-900/50 backdrop-blur-sm"
                @click.self="showModal = false"
            >
                <div class="bg-cream-200 rounded-2xl shadow-xl w-full max-w-sm border border-teal-100">

                    <!-- Header -->
                    <div class="flex items-center justify-between px-6 py-4 border-b border-teal-100">
                        <div>
                            <h2 class="text-sm font-bold text-teal-800">
                                {{ editingNode?.is_filled ? 'Edit Name' : 'Set Name' }}
                            </h2>
                            <p class="text-xs text-teal-400 mt-0.5">
                                Position {{ editingNode?.display_number }}
                            </p>
                        </div>
                        <button
                            class="p-1.5 rounded-lg text-teal-400 hover:bg-teal-100 transition-colors"
                            @click="showModal = false"
                        >
                            <XMarkIcon class="w-4 h-4" />
                        </button>
                    </div>

                    <!-- Body -->
                    <div class="p-6 space-y-4">
                        <div class="space-y-1.5">
                            <label class="block text-xs font-semibold text-teal-700 uppercase tracking-wide">
                                Full Name
                            </label>
                            <input
                                v-model="nameForm.name"
                                type="text"
                                placeholder="e.g. Juan dela Cruz"
                                autofocus
                                class="w-full px-3 py-3 rounded-xl border border-teal-200 text-sm text-teal-800 placeholder-teal-300 bg-white outline-none focus:border-teal-400 focus:ring-2 focus:ring-teal-100 transition-colors"
                                @keyup.enter="saveName"
                            />
                            <p v-if="nameForm.errors.name" class="text-xs text-red-500">
                                {{ nameForm.errors.name }}
                            </p>
                        </div>

                        <p class="text-xs text-teal-400">
                            This name will be visible to everyone on the network tree.
                        </p>

                        <div class="flex gap-3 pt-1">
                            <button
                                type="button"
                                class="flex-1 py-2.5 rounded-xl border border-teal-200 text-sm text-teal-600 hover:bg-teal-50 transition-colors"
                                @click="showModal = false"
                            >
                                Cancel
                            </button>
                            <button
                                type="button"
                                :disabled="nameForm.processing || !nameForm.name.trim()"
                                class="flex-1 flex items-center justify-center gap-2 py-2.5 rounded-xl bg-teal-500 text-cream-200 text-sm font-semibold hover:bg-teal-600 transition-colors disabled:opacity-60"
                                @click="saveName"
                            >
                                <CheckIcon class="w-4 h-4" />
                                {{ nameForm.processing ? 'Saving…' : 'Save Name' }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </Teleport>

    </AppLayout>
</template>

<style scoped>
/* Smooth hover on nodes */
.node-group:hover circle:first-of-type {
    filter: brightness(0.95) url(#node-shadow);
}
</style>
