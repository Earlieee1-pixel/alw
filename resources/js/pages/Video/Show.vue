<script setup>
import { useForm, Link, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { useConfirm } from '@/composables/useConfirm'
import {
    ChevronLeftIcon,
    TrashIcon,
    PlayCircleIcon,
    AcademicCapIcon,
} from '@heroicons/vue/24/outline'

const props = defineProps({
    video:     Object,
    related:   Array,
    canPost:   Boolean,
    canDelete: Boolean,
})

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

const { confirm } = useConfirm()

// Delete form
const deleteForm = useForm({})
async function deleteVideo() {
    const ok = await confirm('This video will be permanently deleted and cannot be recovered.', {
        title: 'Delete Video?',
        confirmLabel: 'Yes, Delete',
        danger: true,
    })
    if (! ok) return
    deleteForm.delete(route('videos.destroy', props.video.id), {
        onSuccess: () => router.visit(route('videos.index')),
    })
}
</script>

<template>
    <AppLayout>
        <template #header>
            <div class="flex items-center gap-2">
                <Link
                    :href="route('videos.index')"
                    class="p-1 rounded-lg text-teal-400 hover:text-teal-600 hover:bg-teal-100 transition-colors"
                >
                    <ChevronLeftIcon class="w-4 h-4" />
                </Link>
                <h1 class="text-base font-semibold text-teal-800 truncate">{{ video.title }}</h1>
            </div>
        </template>

        <div class="max-w-6xl mx-auto grid lg:grid-cols-3 gap-6">

            <!-- ===================== MAIN VIDEO AREA ===================== -->
            <div class="lg:col-span-2 space-y-5">

                <!-- Video embed -->
                <div class="rounded-2xl overflow-hidden bg-teal-900 aspect-video">
                    <iframe
                        :src="video.embed_url"
                        :title="video.title"
                        class="w-full h-full"
                        frameborder="0"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                        allowfullscreen
                    />
                </div>

                <!-- Video details -->
                <div class="bg-cream-200 rounded-2xl border border-teal-100 p-5">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex-1 min-w-0">
                            <h2 class="text-lg font-bold text-teal-800">{{ video.title }}</h2>
                            <div class="flex items-center gap-3 mt-2">
                                <span
                                    class="text-xs px-2.5 py-1 rounded-full font-medium capitalize"
                                    :class="categoryClass(video.category)"
                                >
                                    {{ video.category }}
                                </span>
                                <span class="text-xs text-teal-400">Posted by {{ video.posted_by }}</span>
                                <span class="text-xs text-teal-400">{{ video.posted_at }}</span>
                            </div>
                        </div>

                        <!-- Delete button — admin only -->
                        <button
                            v-if="canDelete"
                            class="shrink-0 flex items-center gap-1.5 px-3 py-2 rounded-xl bg-red-50 text-red-500 text-xs font-semibold hover:bg-red-100 transition-colors"
                            @click="deleteVideo"
                        >
                            <TrashIcon class="w-3.5 h-3.5" />
                            Delete
                        </button>
                    </div>

                    <p v-if="video.description" class="mt-4 text-sm text-teal-600/80 leading-relaxed">
                        {{ video.description }}
                    </p>
                </div>
            </div>

            <!-- ===================== RELATED VIDEOS ===================== -->
            <div class="space-y-4">
                <h3 class="text-sm font-semibold text-teal-700 uppercase tracking-wide">
                    Related Videos
                </h3>

                <div v-if="related.length > 0" class="space-y-3">
                    <Link
                        v-for="rel in related"
                        :key="rel.id"
                        :href="route('videos.show', rel.id)"
                        class="flex gap-3 p-3 rounded-xl bg-cream-200 border border-teal-100 hover:border-teal-300 hover:shadow-sm transition-all group"
                    >
                        <!-- Thumbnail -->
                        <div class="w-20 h-14 rounded-lg bg-teal-800 overflow-hidden shrink-0">
                            <img
                                v-if="rel.thumbnail"
                                :src="rel.thumbnail"
                                :alt="rel.title"
                                class="w-full h-full object-cover group-hover:scale-105 transition-transform"
                            />
                            <div v-else class="w-full h-full flex items-center justify-center">
                                <PlayCircleIcon class="w-5 h-5 text-teal-500" />
                            </div>
                        </div>

                        <!-- Info -->
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-semibold text-teal-800 line-clamp-2 leading-snug">
                                {{ rel.title }}
                            </p>
                            <div class="flex items-center gap-2 mt-1.5">
                                <span
                                    class="text-[10px] px-1.5 py-0.5 rounded-full font-medium capitalize"
                                    :class="categoryClass(rel.category)"
                                >
                                    {{ rel.category }}
                                </span>
                                <span class="text-[10px] text-teal-400">{{ rel.posted_at }}</span>
                            </div>
                        </div>
                    </Link>
                </div>

                <div v-else class="flex flex-col items-center justify-center py-10 bg-cream-200 rounded-2xl border border-teal-100">
                    <AcademicCapIcon class="w-8 h-8 text-teal-300 mb-2" />
                    <p class="text-xs text-teal-400">No related videos yet.</p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
