<script setup>
import { useForm } from '@inertiajs/vue3'
import { Link } from '@inertiajs/vue3'
import {
    EnvelopeIcon,
    LockClosedIcon,
    GlobeAltIcon,
    EyeIcon,
    EyeSlashIcon,
    ArrowRightIcon,
} from '@heroicons/vue/24/outline'
import { ref } from 'vue'

// Ipakita/itago ang password
const showPassword = ref(false)

// Inertia form helper — nag-handle sa loading state, errors, ug submission
const form = useForm({
    email:    '',
    password: '',
    remember: false,
})

// I-submit ang login form
function submit() {
    form.post(route('login.store'), {
        onFinish: () => form.reset('password'),
    })
}
</script>

<template>
    <div class="min-h-screen bg-cream-200 flex">

        <!-- Left panel — branding -->
        <div class="hidden lg:flex lg:w-1/2 bg-teal-800 flex-col justify-between p-12 relative overflow-hidden">

            <!-- Background orbs -->
            <div class="absolute top-0 left-0 w-[500px] h-[500px] rounded-full bg-teal-700/40 -translate-x-1/2 -translate-y-1/2 blur-3xl pointer-events-none" />
            <div class="absolute bottom-0 right-0 w-[400px] h-[400px] rounded-full bg-teal-600/30 translate-x-1/3 translate-y-1/3 blur-3xl pointer-events-none" />

            <!-- Logo -->
            <div class="relative flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-teal-500 flex items-center justify-center shadow-md">
                    <GlobeAltIcon class="w-5 h-5 text-cream-200" />
                </div>
                <div>
                    <div class="text-lg font-bold text-cream-200 leading-none">ALW</div>
                    <div class="text-xs text-teal-400 tracking-widest uppercase">Atomic Legendary Warriors</div>
                </div>
            </div>

            <!-- Center quote -->
            <div class="relative space-y-6">
                <h2 class="text-3xl font-bold text-cream-200 leading-tight">
                    Welcome back.<br />Your network<br />is waiting.
                </h2>
                <p class="text-teal-300 text-sm leading-relaxed max-w-xs">
                    Log in to access your dashboard, training resources, and connect with your team across the globe.
                </p>

                <!-- Stat pills -->
                <div class="flex flex-wrap gap-3 pt-2">
                    <div
                        v-for="stat in [
                            { value: '10,000+', label: 'Members' },
                            { value: '30+',     label: 'Countries' },
                            { value: '500+',    label: 'Videos' },
                        ]"
                        :key="stat.label"
                        class="px-4 py-2 rounded-xl bg-teal-700/50 border border-teal-600/40 text-center"
                    >
                        <div class="text-sm font-bold text-cream-200">{{ stat.value }}</div>
                        <div class="text-xs text-teal-400">{{ stat.label }}</div>
                    </div>
                </div>
            </div>

            <!-- Footer note -->
            <p class="relative text-xs text-teal-500">
                &copy; {{ new Date().getFullYear() }} Atomic Legendary Warriors
            </p>
        </div>

        <!-- Right panel — login form -->
        <div class="w-full lg:w-1/2 flex items-center justify-center px-6 py-12">
            <div class="w-full max-w-sm">

                <!-- Mobile logo -->
                <div class="flex items-center gap-2.5 mb-10 lg:hidden">
                    <div class="w-8 h-8 rounded-lg bg-teal-500 flex items-center justify-center">
                        <GlobeAltIcon class="w-4 h-4 text-cream-200" />
                    </div>
                    <span class="text-sm font-bold text-teal-700 tracking-tight">ALW</span>
                </div>

                <div class="mb-8">
                    <h1 class="text-2xl font-bold text-teal-800">Sign in to your account</h1>
                    <p class="mt-1.5 text-sm text-teal-600/80">
                        Don't have an account? Contact your upline for an invite link.
                    </p>
                </div>

                <!-- Global error (e.g. account suspended) -->
                <div
                    v-if="form.errors.email && !form.errors.password"
                    class="mb-6 flex items-start gap-3 p-4 rounded-xl bg-red-50 border border-red-200 text-sm text-red-700"
                >
                    <LockClosedIcon class="w-4 h-4 shrink-0 mt-0.5" />
                    <span>{{ form.errors.email }}</span>
                </div>

                <form @submit.prevent="submit" class="space-y-5" novalidate>

                    <!-- Email -->
                    <div class="space-y-1.5">
                        <label for="email" class="block text-sm font-medium text-teal-800">
                            Email address
                        </label>
                        <div class="relative">
                            <EnvelopeIcon class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-teal-400 pointer-events-none" />
                            <input
                                id="email"
                                v-model="form.email"
                                type="email"
                                autocomplete="email"
                                placeholder="you@example.com"
                                class="w-full pl-10 pr-4 py-2.5 rounded-xl border text-sm text-teal-800 placeholder-teal-300 bg-white outline-none transition-colors"
                                :class="form.errors.email
                                    ? 'border-red-300 focus:border-red-400 focus:ring-2 focus:ring-red-100'
                                    : 'border-teal-200 focus:border-teal-400 focus:ring-2 focus:ring-teal-100'"
                            />
                        </div>
                        <p v-if="form.errors.email" class="text-xs text-red-500">{{ form.errors.email }}</p>
                    </div>

                    <!-- Password -->
                    <div class="space-y-1.5">
                        <label for="password" class="block text-sm font-medium text-teal-800">
                            Password
                        </label>
                        <div class="relative">
                            <LockClosedIcon class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-teal-400 pointer-events-none" />
                            <input
                                id="password"
                                v-model="form.password"
                                :type="showPassword ? 'text' : 'password'"
                                autocomplete="current-password"
                                placeholder="••••••••"
                                class="w-full pl-10 pr-10 py-2.5 rounded-xl border text-sm text-teal-800 placeholder-teal-300 bg-white outline-none transition-colors"
                                :class="form.errors.password
                                    ? 'border-red-300 focus:border-red-400 focus:ring-2 focus:ring-red-100'
                                    : 'border-teal-200 focus:border-teal-400 focus:ring-2 focus:ring-teal-100'"
                            />
                            <!-- Show/hide password toggle -->
                            <button
                                type="button"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-teal-400 hover:text-teal-600 transition-colors"
                                @click="showPassword = !showPassword"
                                :aria-label="showPassword ? 'Hide password' : 'Show password'"
                            >
                                <EyeSlashIcon v-if="showPassword" class="w-4 h-4" />
                                <EyeIcon v-else class="w-4 h-4" />
                            </button>
                        </div>
                        <p v-if="form.errors.password" class="text-xs text-red-500">{{ form.errors.password }}</p>
                    </div>

                    <!-- Remember me -->
                    <div class="flex items-center gap-2.5">
                        <input
                            id="remember"
                            v-model="form.remember"
                            type="checkbox"
                            class="w-4 h-4 rounded border-teal-300 text-teal-500 accent-teal-500 cursor-pointer"
                        />
                        <label for="remember" class="text-sm text-teal-600 cursor-pointer select-none">
                            Keep me signed in
                        </label>
                    </div>

                    <!-- Submit button -->
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full flex items-center justify-center gap-2 px-4 py-3 rounded-xl bg-teal-500 text-cream-200 text-sm font-semibold hover:bg-teal-600 focus:outline-none focus:ring-2 focus:ring-teal-400 focus:ring-offset-2 transition-colors disabled:opacity-60 disabled:cursor-not-allowed shadow-sm shadow-teal-500/20"
                    >
                        <span>{{ form.processing ? 'Signing in…' : 'Sign In' }}</span>
                        <ArrowRightIcon v-if="!form.processing" class="w-4 h-4" />
                    </button>
                </form>

                <!-- Back to home -->
                <p class="mt-8 text-center text-xs text-teal-500">
                    <Link :href="route('home')" class="hover:text-teal-700 transition-colors">
                        ← Back to ALW homepage
                    </Link>
                </p>
            </div>
        </div>
    </div>
</template>
