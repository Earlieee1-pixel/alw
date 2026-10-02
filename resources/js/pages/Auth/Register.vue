<script setup>
import { useForm, Link } from '@inertiajs/vue3'
import {
    UserIcon,
    EnvelopeIcon,
    LockClosedIcon,
    GlobeAltIcon,
    EyeIcon,
    EyeSlashIcon,
    ArrowRightIcon,
    TicketIcon,
    CheckCircleIcon,
} from '@heroicons/vue/24/outline'
import { ref } from 'vue'

// Props gikan sa RegisterController — refCode ug inviter name
const props = defineProps({
    refCode:     { type: String, required: true },
    inviterName: { type: String, required: true },
})

// Ipakita/itago ang password fields
const showPassword        = ref(false)
const showPasswordConfirm = ref(false)

// Inertia form — pre-filled ang invite_code gikan sa URL
const form = useForm({
    name:                  '',
    email:                 '',
    password:              '',
    password_confirmation: '',
    invite_code:           props.refCode,
})

// I-submit ang registration form
function submit() {
    form.post(route('register.store'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    })
}
</script>

<template>
    <div class="min-h-screen bg-cream-200 flex">

        <!-- Left panel — branding -->
        <div class="hidden lg:flex lg:w-1/2 bg-teal-800 flex-col justify-between p-12 relative overflow-hidden">

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

            <!-- Invite info -->
            <div class="relative space-y-6">
                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-teal-700/60 border border-teal-600/40">
                    <TicketIcon class="w-3.5 h-3.5 text-teal-400" />
                    <span class="text-xs font-semibold text-teal-300 uppercase tracking-wide">Invite Only</span>
                </div>

                <h2 class="text-3xl font-bold text-cream-200 leading-tight">
                    You've been<br />invited to join<br />the network.
                </h2>

                <!-- Inviter card -->
                <div class="flex items-center gap-3 p-4 rounded-xl bg-teal-700/40 border border-teal-600/30">
                    <div class="w-10 h-10 rounded-full bg-teal-500/30 flex items-center justify-center shrink-0">
                        <UserIcon class="w-5 h-5 text-teal-300" />
                    </div>
                    <div>
                        <div class="text-xs text-teal-400">Invited by</div>
                        <div class="text-sm font-semibold text-cream-200">{{ inviterName }}</div>
                    </div>
                </div>

                <!-- What you get -->
                <ul class="space-y-2.5">
                    <li
                        v-for="perk in [
                            'Access to all training videos',
                            'Join live video calls with your team',
                            'Your own invite code to grow your network',
                            'Real-time downline tracking',
                        ]"
                        :key="perk"
                        class="flex items-center gap-2.5 text-sm text-teal-300"
                    >
                        <CheckCircleIcon class="w-4 h-4 text-teal-400 shrink-0" />
                        {{ perk }}
                    </li>
                </ul>
            </div>

            <p class="relative text-xs text-teal-500">
                &copy; {{ new Date().getFullYear() }} Atomic Legendary Warriors
            </p>
        </div>

        <!-- Right panel — register form -->
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
                    <h1 class="text-2xl font-bold text-teal-800">Create your account</h1>
                    <p class="mt-1.5 text-sm text-teal-600/80">
                        Invited by <span class="font-semibold text-teal-600">{{ inviterName }}</span>.
                        Fill in your details to get started.
                    </p>
                </div>

                <!-- Invite code badge — locked, read-only -->
                <div class="flex items-center gap-3 px-4 py-3 rounded-xl bg-teal-50 border border-teal-200 mb-6">
                    <TicketIcon class="w-4 h-4 text-teal-500 shrink-0" />
                    <div class="flex-1 min-w-0">
                        <div class="text-xs text-teal-500 font-medium">Invite Code</div>
                        <div class="text-sm font-bold text-teal-700 font-mono tracking-widest">{{ refCode }}</div>
                    </div>
                    <CheckCircleIcon class="w-4 h-4 text-green-500 shrink-0" />
                </div>

                <form @submit.prevent="submit" class="space-y-4" novalidate>

                    <!-- Full name -->
                    <div class="space-y-1.5">
                        <label for="name" class="block text-sm font-medium text-teal-800">Full Name</label>
                        <div class="relative">
                            <UserIcon class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-teal-400 pointer-events-none" />
                            <input
                                id="name"
                                v-model="form.name"
                                type="text"
                                autocomplete="name"
                                placeholder="Juan dela Cruz"
                                class="w-full pl-10 pr-4 py-2.5 rounded-xl border text-sm text-teal-800 placeholder-teal-300 bg-white outline-none transition-colors"
                                :class="form.errors.name
                                    ? 'border-red-300 focus:border-red-400 focus:ring-2 focus:ring-red-100'
                                    : 'border-teal-200 focus:border-teal-400 focus:ring-2 focus:ring-teal-100'"
                            />
                        </div>
                        <p v-if="form.errors.name" class="text-xs text-red-500">{{ form.errors.name }}</p>
                    </div>

                    <!-- Email -->
                    <div class="space-y-1.5">
                        <label for="email" class="block text-sm font-medium text-teal-800">Email Address</label>
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
                        <label for="password" class="block text-sm font-medium text-teal-800">Password</label>
                        <div class="relative">
                            <LockClosedIcon class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-teal-400 pointer-events-none" />
                            <input
                                id="password"
                                v-model="form.password"
                                :type="showPassword ? 'text' : 'password'"
                                autocomplete="new-password"
                                placeholder="Min. 8 characters"
                                class="w-full pl-10 pr-10 py-2.5 rounded-xl border text-sm text-teal-800 placeholder-teal-300 bg-white outline-none transition-colors"
                                :class="form.errors.password
                                    ? 'border-red-300 focus:border-red-400 focus:ring-2 focus:ring-red-100'
                                    : 'border-teal-200 focus:border-teal-400 focus:ring-2 focus:ring-teal-100'"
                            />
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

                    <!-- Confirm password -->
                    <div class="space-y-1.5">
                        <label for="password_confirmation" class="block text-sm font-medium text-teal-800">Confirm Password</label>
                        <div class="relative">
                            <LockClosedIcon class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-teal-400 pointer-events-none" />
                            <input
                                id="password_confirmation"
                                v-model="form.password_confirmation"
                                :type="showPasswordConfirm ? 'text' : 'password'"
                                autocomplete="new-password"
                                placeholder="Repeat your password"
                                class="w-full pl-10 pr-10 py-2.5 rounded-xl border text-sm text-teal-800 placeholder-teal-300 bg-white outline-none transition-colors"
                                :class="form.errors.password_confirmation
                                    ? 'border-red-300 focus:border-red-400 focus:ring-2 focus:ring-red-100'
                                    : 'border-teal-200 focus:border-teal-400 focus:ring-2 focus:ring-teal-100'"
                            />
                            <button
                                type="button"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-teal-400 hover:text-teal-600 transition-colors"
                                @click="showPasswordConfirm = !showPasswordConfirm"
                                :aria-label="showPasswordConfirm ? 'Hide password' : 'Show password'"
                            >
                                <EyeSlashIcon v-if="showPasswordConfirm" class="w-4 h-4" />
                                <EyeIcon v-else class="w-4 h-4" />
                            </button>
                        </div>
                        <p v-if="form.errors.password_confirmation" class="text-xs text-red-500">{{ form.errors.password_confirmation }}</p>
                    </div>

                    <!-- Submit -->
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full flex items-center justify-center gap-2 px-4 py-3 rounded-xl bg-teal-500 text-cream-200 text-sm font-semibold hover:bg-teal-600 focus:outline-none focus:ring-2 focus:ring-teal-400 focus:ring-offset-2 transition-colors disabled:opacity-60 disabled:cursor-not-allowed shadow-sm shadow-teal-500/20 mt-2"
                    >
                        <span>{{ form.processing ? 'Creating account…' : 'Create Account' }}</span>
                        <ArrowRightIcon v-if="!form.processing" class="w-4 h-4" />
                    </button>
                </form>

                <!-- Already have an account -->
                <p class="mt-6 text-center text-sm text-teal-600">
                    Already have an account?
                    <Link :href="route('login')" class="font-semibold text-teal-500 hover:text-teal-700 transition-colors">
                        Sign in
                    </Link>
                </p>

                <p class="mt-4 text-center text-xs text-teal-500">
                    <Link :href="route('home')" class="hover:text-teal-700 transition-colors">
                        ← Back to ALW homepage
                    </Link>
                </p>
            </div>
        </div>
    </div>
</template>
