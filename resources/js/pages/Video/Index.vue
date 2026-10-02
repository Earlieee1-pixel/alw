<script setup>
import { ref, watch } from 'vue'
import { router, useForm, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import {
    MagnifyingGlassIcon,
    PlusIcon,
    PlayCircleIcon,
    XMarkIcon,
    AcademicCapIcon,
    ChevronLeftIcon,
    ChevronRightIcon,
} from '@heroicons/vue/24/outline'

const props = defineProps({
    videos:     Object,
    filters:    Object,
    categories: Array,
    canPost:    Boolean,
})

// Search ug category filter
const search   = ref(props.filters.search   ?? '')
const category = ref(props.filters.category ?? '')

let searchTimeout = null
watch(search, () => {
    clearTimeout(searchTimeout)
    searchTimeout = setTimeout(() => applyFilters(), 400)
})
watch(category, () => applyFilters())

function applyFilters() {
    router.get(route('videos.index'), {
        search:   search.value   || undefined,
        category: category.value || undefined,
    }, { preserveState: true, preserveScroll: true })
}

// Post video modal
const showModal = ref(false)
const form = useForm({
    title:       '',
    description: '',
    url:         '',
    source:      'youtube',
    category:    'general',
})

function submitVideo() {
    form.post(route('videos.store'), {
        onSuccess: () => {
            showModal.value = false
            form.reset()
        },
    })
}

// Category label formatting
function categoryLabel(cat) {
    return cat.charAt(0).toUpperCase() + cat.slice(1)
}

// Category badge color
function categoryClass(cat) {
    const map = {
        onboarding: 'bg-blue-100 text-blue-700',
        leadership: 'bg-purple-100 text-purple-700',
        network:    'bg-teal-100 text-teal-700',
        compliance: 'bg-orange-100 text-orange-700',
        product:    'bg-pink-100 text-pink-700',
        general:    'bg-gray-100 text-gray-600',
    }
    return map[cat] ?? 'bg-gray-100 text-gray-600'
}
</script>

<template>
    <AppLayout>
        <template #header>
            <h1 class="text-base font-semibold text-teal-800">Training Videos</h1>
        </template>

        <div class="space-y-5 max-w-7xl mx-auto">

            <!-- ===================== TOOLBAR ===================== -->
            <div class="flex flex-col sm:flex-row gap-3 items-start sm:items-center justify-between">
                <div class="flex gap-3 flex-1 w-full sm:w-auto">

                    <!-- Search -->
                    <div class="relative flex-1 max-w-xs">
                        <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-teal-400 pointer-events-none" />
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Search videos..."
                            class="w-full pl-9 pr-4 py-2.5 rounded-xl border border-teal-200 text-sm text-teal-800 placeholder-teal-300 bg-white outline-none focus:border-teal-400 focus:ring-2 focus:ring-teal-100 transition-colors"
                        />
                    </div>

                    <!-- Category filter -->
                    <select
                        v-model="category"
                        class="px-3 py-2.5 rounded-xl border border-teal-200 text-sm text-teal-700 bg-white outline-none focus:border-teal-400 focus:ring-2 focus:ring-teal-100 transition-colors"
                    >
                        <option value="">All Categories</option>
                        <option v-for="cat in categories" :key="cat" :value="cat">
                            {{ categoryLabel(cat) }}
                        </option>
                    </select>
                </div>

                <!-- Post video button — admin/leader only -->
                <button
                    v-if="canPost"
                    class="flex items-center gap-2 px-4 py-2.5 rounded-xl bg-teal-500 text-cream-200 text-sm font-semibold hover:bg-teal-600 transition-colors shrink-0"
                    @click="showModal = true"
                >
                    <PlusIcon class="w-4 h-4" />
                    Post Video
                </button>
            </div>

            <!-- ===================== VIDEO GRID ===================== -->
            <div v-if="videos.data.length > 0" class="grid sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
                <Link
                    v-for="video in videos.data"
                    :key="video.id"
                    :href="route('videos.show', video.id)"
                    class="group bg-cream-200 rounded-2xl border border-teal-100 hover:border-teal-300 hover:shadow-md hover:shadow-teal-500/5 transition-all overflow-hidden"
                >
                    <!-- Thumbnail -->
                    <div class="relative aspect-video bg-teal-800 overflow-hidden">
                        <img
                            v-if="video.thumbnail"
                            :src="video.thumbnail"
                            :alt="video.title"
                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                        />
                        <div v-else class="w-full h-full flex items-center justify-center">
                            <AcademicCapIcon class="w-10 h-10 text-teal-500" />
                        </div>
                        <!-- Play overlay -->
                        <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity bg-teal-900/40">
                            <div class="w-12 h-12 rounded-full bg-cream-200/90 flex items-center justify-center shadow-lg">
                                <PlayCircleIcon class="w-6 h-6 text-teal-600" />
                            </div>
                        </div>
                    </div>

                    <!-- Info -->
                    <div class="p-4">
                        <div class="flex items-start justify-between gap-2 mb-2">
                            <h3 class="text-sm font-semibold text-teal-800 leading-snug line-clamp-2 flex-1">
                                {{ video.title }}
                            </h3>
                        </div>
                        <div class="flex items-center justify-between">
                            <span
                                class="text-xs px-2 py-0.5 rounded-full font-medium capitalize"
                                :class="categoryClass(video.category)"
                            >
                                {{ video.category }}
                            </span>
                            <span class="text-xs text-teal-400">{{ video.posted_at }}</span>
                        </div>
                        <p class="mt-1.5 text-xs text-teal-400">by {{ video.posted_by }}</p>
                    </div>
                </Link>
            </div>

            <!-- Empty state -->
            <div v-else class="flex flex-col items-center justify-center h-64 bg-cream-200 rounded-2xl border border-teal-100">
                <AcademicCapIcon class="w-10 h-10 text-teal-300 mb-3" />
                <p class="text-sm text-teal-500 font-medium">No videos found.</p>
                <p v-if="canPost" class="text-xs text-teal-400 mt-1">Post the first training video for your team.</p>
            </div>

            <!-- Pagination -->
            <div v-if="videos.last_page > 1" class="flex items-center justify-between">
                <p class="text-xs text-teal-500">
                    Showing {{ videos.from }}–{{ videos.to }} of {{ videos.total }} videos
                </p>
                <div class="flex items-center gap-1">
                    <a
                        v-if="videos.prev_page_url"
                        :href="videos.prev_page_url"
                        class="p-1.5 rounded-lg border border-teal-200 text-teal-500 hover:bg-teal-50 transition-colors"
                    >
                        <ChevronLeftIcon class="w-4 h-4" />
                    </a>
                    <span class="px-3 py-1.5 text-xs font-semibold text-teal-700">
                        {{ videos.current_page }} / {{ videos.last_page }}
                    </span>
                    <a
                        v-if="videos.next_page_url"
                        :href="videos.next_page_url"
                        class="p-1.5 rounded-lg border border-teal-200 text-teal-500 hover:bg-teal-50 transition-colors"
                    >
                        <ChevronRightIcon class="w-4 h-4" />
                    </a>
                </div>
            </div>
        </div>

        <!-- ===================== POST VIDEO MODAL ===================== -->
        <Teleport to="body">
            <div
                v-if="showModal"
                class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-teal-900/50 backdrop-blur-sm"
                @click.self="showModal = false"
            >
                <div class="bg-cream-200 rounded-2xl shadow-xl w-full max-w-lg border border-teal-100">

                    <!-- Modal header -->
                    <div class="flex items-center justify-between px-6 py-4 border-b border-teal-100">
                        <h2 class="text-sm font-bold text-teal-800">Post Training Video</h2>
                        <button
                            class="p-1.5 rounded-lg text-teal-400 hover:bg-teal-100 transition-colors"
                            @click="showModal = false"
                        >
                            <XMarkIcon class="w-4 h-4" />
                        </button>
                    </div>

                    <!-- Modal body -->
                    <form @submit.prevent="submitVideo" class="p-6 space-y-4">

                        <!-- Title -->
                        <div class="space-y-1.5">
                            <label class="block text-xs font-semibold text-teal-700 uppercase tracking-wide">Title</label>
                            <input
                                v-model="form.title"
                                type="text"
                                placeholder="e.g. Getting Started with ALW"
                                class="w-full px-3 py-2.5 rounded-xl border border-teal-200 text-sm text-teal-800 placeholder-teal-300 bg-white outline-none focus:border-teal-400 focus:ring-2 focus:ring-teal-100"
                            />
                            <p v-if="form.errors.title" class="text-xs text-red-500">{{ form.errors.title }}</p>
                        </div>

                        <!-- Video URL -->
                        <div class="space-y-1.5">
                            <label class="block text-xs font-semibold text-teal-700 uppercase tracking-wide">Video URL</label>
                            <input
                                v-model="form.url"
                                type="url"
                                placeholder="https://www.youtube.com/watch?v=..."
                                class="w-full px-3 py-2.5 rounded-xl border border-teal-200 text-sm text-teal-800 placeholder-teal-300 bg-white outline-none focus:border-teal-400 focus:ring-2 focus:ring-teal-100"
                            />
                            <p v-if="form.errors.url" class="text-xs text-red-500">{{ form.errors.url }}</p>
                        </div>

                        <!-- Source + Category row -->
                        <div class="grid grid-cols-2 gap-3">
                            <div class="space-y-1.5">
                                <label class="block text-xs font-semibold text-teal-700 uppercase tracking-wide">Source</label>
                                <select
                                    v-model="form.source"
                                    class="w-full px-3 py-2.5 rounded-xl border border-teal-200 text-sm text-teal-700 bg-white outline-none focus:border-teal-400 focus:ring-2 focus:ring-teal-100"
                                >
                                    <option value="youtube">YouTube</option>
                                    <option value="vimeo">Vimeo</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            <div class="space-y-1.5">
                                <label class="block text-xs font-semibold text-teal-700 uppercase tracking-wide">Category</label>
                                <select
                                    v-model="form.category"
                                    class="w-full px-3 py-2.5 rounded-xl border border-teal-200 text-sm text-teal-700 bg-white outline-none focus:border-teal-400 focus:ring-2 focus:ring-teal-100"
                                >
                                    <option v-for="cat in categories" :key="cat" :value="cat">
                                        {{ categoryLabel(cat) }}
                                    </option>
                                </select>
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="space-y-1.5">
                            <label class="block text-xs font-semibold text-teal-700 uppercase tracking-wide">Description <span class="text-teal-400 font-normal">(optional)</span></label>
                            <textarea
                                v-model="form.description"
                                rows="3"
                                placeholder="Brief description of what this video covers..."
                                class="w-full px-3 py-2.5 rounded-xl border border-teal-200 text-sm text-teal-800 placeholder-teal-300 bg-white outline-none focus:border-teal-400 focus:ring-2 focus:ring-teal-100 resize-none"
                            />
                        </div>

                        <!-- Actions -->
                        <div class="flex justify-end gap-3 pt-2">
                            <button
                                type="button"
                                class="px-4 py-2.5 rounded-xl border border-teal-200 text-sm text-teal-600 hover:bg-teal-50 transition-colors"
                                @click="showModal = false"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="px-5 py-2.5 rounded-xl bg-teal-500 text-cream-200 text-sm font-semibold hover:bg-teal-600 transition-colors disabled:opacity-60"
                            >
                                {{ form.processing ? 'Posting…' : 'Post Video' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Teleport>
    </AppLayout>
</template>
